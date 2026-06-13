<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\SystemHealthAlertMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check';

    protected $description = 'Check Reverb, FCM and the queue; alert the admin when something is broken.';

    public function handle(): int
    {
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
        }

        $this->error('Health check FAILED: '.implode('; ', $problems));

        return self::FAILURE;
    }
}
