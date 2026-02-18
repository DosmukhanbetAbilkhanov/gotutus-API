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

class JoinRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JoinRequest $joinRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $senderName = $this->joinRequest->user->name;

        return (new FcmMessage(notification: new FcmNotification(
            title: __('notifications.join_request_received_title'),
            body: __('notifications.join_request_received_body', ['name' => $senderName]),
        )))->data([
            'type' => 'join_request_received',
            'join_request_id' => (string) $this->joinRequest->id,
            'hangout_request_id' => (string) $this->joinRequest->hangout_request_id,
        ]);
    }
}
