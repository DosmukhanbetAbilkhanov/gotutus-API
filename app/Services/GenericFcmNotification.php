<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class GenericFcmNotification extends Notification
{

    public function __construct(
        private string $title,
        private string $body,
        private array $data = [],
    ) {}

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        // Cast all data values to string (FCM requires string values)
        $stringData = [];
        foreach ($this->data as $key => $value) {
            $stringData[$key] = (string) $value;
        }

        return (new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body: $this->body,
        )))->data($stringData);
    }
}
