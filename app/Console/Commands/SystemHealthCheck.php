<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\SystemHealthAlertMail;
use App\Models\User;
use App\Services\GenericFcmNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check
        {--fcm-test= : Send a real test FCM push to the given user ID and report the result}';

    protected $description = 'Check Reverb, FCM and the queue; alert the admin when something is broken.';

    public function handle(): int
    {
        // On-demand end-to-end FCM test (does not run the full health check / alerts).
        if ($this->option('fcm-test') !== null) {
            return $this->runFcmTest((int) $this->option('fcm-test'));
        }

        $problems = [];

        // 1. Reverb websocket server reachable (server→Reverb publish path).
        $host = env('REVERB_BROADCASTING_HOST', '127.0.0.1');
        $port = (int) config('reverb.servers.reverb.port', 8080);
        $conn = @fsockopen($host, $port, $errno, $errstr, 3);
        if ($conn) {
            fclose($conn);
        } else {
            $problems[] = "Reverb is not reachable at {$host}:{$port} ({$errstr})";
        }

        // 2. Failed queue jobs.
        try {
            $failed = app('queue.failer')->count();
            if ($failed > 0) {
                $problems[] = "{$failed} failed queue job(s)";
            }
        } catch (\Throwable $e) {
            // ignore — failer driver may be unavailable
        }

        // 3. Queue backlog (worker stopped / overwhelmed).
        try {
            $size = Queue::size();
            if ($size > 100) {
                $problems[] = "Queue backlog: {$size} pending jobs (worker may be down)";
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 4. FCM credentials present and valid.
        $credPath = config('services.fcm.credentials');
        if (! is_string($credPath) || ! file_exists($credPath)) {
            $problems[] = 'FCM credentials file is missing ('.($credPath ?: 'unset').')';
        } else {
            $json = json_decode((string) file_get_contents($credPath), true);
            if (! is_array($json) || empty($json['private_key']) || empty($json['client_email'])) {
                $problems[] = "FCM credentials file is invalid ({$credPath})";
            }
        }

        // 5. Recent FCM send failures (tracked by NotificationService).
        $fcmFailures = (int) Cache::get('health:fcm_failures', 0);
        if ($fcmFailures > 0) {
            $problems[] = "{$fcmFailures} FCM push failure(s) in the last hour";
        }

        // 6. Low SMS balance (verification codes stop sending at zero).
        $balance = app(\App\Services\MobizonSmsService::class)->getBalance();
        $threshold = (float) config('services.mobizon.low_balance_threshold', 500);
        if ($balance !== null && $balance < $threshold) {
            $problems[] = "Low SMS balance: {$balance} (threshold {$threshold})";
        }

        if (empty($problems)) {
            Cache::forget('health:alerted');
            $this->info('Health check passed.');

            return self::SUCCESS;
        }

        Log::warning('System health check failed', ['problems' => $problems]);

        // Throttle alerts to once per 30 minutes while unhealthy. Send the mail
        // synchronously (not queued) so it still goes out if the queue is down.
        if (! Cache::has('health:alerted')) {
            Cache::put('health:alerted', true, now()->addMinutes(30));

            $email = config('services.health.alert_email');
            if ($email) {
                try {
                    Mail::to($email)->send(new SystemHealthAlertMail($problems));
                } catch (\Throwable $e) {
                    Log::error('Failed to send health alert email', ['error' => $e->getMessage()]);
                }
            }

            // Also post to Slack (when configured) so ops alerts land in-channel.
            if (config('logging.channels.slack.url')) {
                try {
                    Log::channel('slack')->error('System health check failed', ['problems' => $problems]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to post health alert to Slack', ['error' => $e->getMessage()]);
                }
            }
        }

        $this->error('Health check FAILED: '.implode('; ', $problems));

        return self::FAILURE;
    }

    /**
     * Send a real FCM push to a user's devices and report the outcome.
     */
    private function runFcmTest(int $userId): int
    {
        $user = User::find($userId);
        if (! $user) {
            $this->error("User {$userId} not found.");

            return self::FAILURE;
        }

        $tokens = $user->deviceTokens()->pluck('token');
        $this->info("User {$user->id} ({$user->name}) has {$tokens->count()} device token(s).");

        if ($tokens->isEmpty()) {
            $this->warn('No device tokens registered — the user must open the app (logged in) so a token is stored. Nothing to send.');

            return self::FAILURE;
        }

        try {
            // GenericFcmNotification is not queued, so it sends synchronously and
            // any failure (bad credentials, project mismatch, etc.) throws here.
            $user->notify(new GenericFcmNotification(
                title: 'Tanys test push',
                body: 'If you see this, FCM is working ✅',
                data: ['type' => 'health_test'],
            ));

            $this->info("✅ FCM push sent without error to {$tokens->count()} device(s). Check the phone and the Firebase console.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ FCM push failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
