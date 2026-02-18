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
        }
    }
}
