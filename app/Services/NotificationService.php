<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification record, send FCM push, and broadcast via WebSocket.
     */
    public function send(User $user, string $type, string $title, string $body, array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        // Broadcast via WebSocket (Reverb) for real-time in-app updates
        NotificationCreated::dispatch($user, $notification);

        // Send FCM push notification to all user devices
        $this->sendFcmPush($user, $type, $title, $body, $data);

        return $notification;
    }

    private function sendFcmPush(User $user, string $type, string $title, string $body, array $data): void
    {
        try {
            $user->notify(new GenericFcmNotification(
                title: $title,
                body: $body,
                data: array_merge($data, ['type' => $type]),
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM push failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            // Track recent failures so system:health-check can alert on FCM outages.
            \Illuminate\Support\Facades\Cache::put(
                'health:fcm_failures',
                (int) \Illuminate\Support\Facades\Cache::get('health:fcm_failures', 0) + 1,
                now()->addHour(),
            );

            // Real-time Slack alert. Throttled so a burst of failures posts once
            // per 15 minutes, and only when the Slack webhook is configured.
            if (config('logging.channels.slack.url')
                && \Illuminate\Support\Facades\Cache::add('slack:fcm_alert', true, now()->addMinutes(15))) {
                try {
                    \Illuminate\Support\Facades\Log::channel('slack')->error('FCM push failed', [
                        'user_id' => $user->id,
                        'type' => $type,
                        'error' => $e->getMessage(),
                    ]);
                } catch (\Throwable $slackError) {
                    \Illuminate\Support\Facades\Log::warning('Failed to post FCM failure to Slack', [
                        'error' => $slackError->getMessage(),
                    ]);
                }
            }
        }
    }
}
