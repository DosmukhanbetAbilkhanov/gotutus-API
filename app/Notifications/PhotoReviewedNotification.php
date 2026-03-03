<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PhotoReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private UserPhoto $photo,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'photo_reviewed',
            'photo_id' => $this->photo->id,
            'status' => $this->photo->status->value,
            'rejection_reason' => $this->photo->rejection_reason,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $title = match ($this->photo->status) {
            PhotoStatus::Approved => __('notifications.photo_approved_title'),
            PhotoStatus::Rejected => __('notifications.photo_rejected_title'),
            default => __('notifications.photo_reviewed_title'),
        };

        $body = match ($this->photo->status) {
            PhotoStatus::Approved => __('notifications.photo_approved_body'),
            PhotoStatus::Rejected => __('notifications.photo_rejected_body'),
            default => __('notifications.photo_reviewed_body'),
        };

        return (new FcmMessage(notification: new FcmNotification(
            title: $title,
            body: $body,
        )))->data([
            'type' => 'photo_reviewed',
            'photo_id' => (string) $this->photo->id,
            'status' => $this->photo->status->value,
        ]);
    }
}
