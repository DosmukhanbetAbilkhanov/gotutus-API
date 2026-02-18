<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\JoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class JoinRequestStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JoinRequest $joinRequest,
        public string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $ownerName = $this->joinRequest->hangoutRequest->user->name;

        $title = match ($this->action) {
            'approved' => __('notifications.join_request_approved_title'),
            'declined' => __('notifications.join_request_declined_title'),
            default => __('notifications.join_request_updated_title'),
        };

        $body = match ($this->action) {
            'approved' => __('notifications.join_request_approved_body', ['name' => $ownerName]),
            'declined' => __('notifications.join_request_declined_body', ['name' => $ownerName]),
            default => __('notifications.join_request_updated_body'),
        };

        return (new FcmMessage(notification: new FcmNotification(
            title: $title,
            body: $body,
        )))->data([
            'type' => 'join_request_'.$this->action,
            'join_request_id' => (string) $this->joinRequest->id,
            'hangout_request_id' => (string) $this->joinRequest->hangout_request_id,
        ]);
    }
}
