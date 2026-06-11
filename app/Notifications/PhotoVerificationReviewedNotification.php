<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\PhotoStatus;
use App\Models\PhotoVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PhotoVerificationReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private PhotoVerification $verification,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'verification_reviewed',
            'verification_id' => $this->verification->id,
            'status' => $this->verification->status->value,
            'rejection_reason' => $this->verification->rejection_reason,
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $title = match ($this->verification->status) {
            PhotoStatus::Approved => __('notifications.verification_approved_title'),
            PhotoStatus::Rejected => __('notifications.verification_rejected_title'),
            default => __('notifications.verification_reviewed_title'),
        };

        $body = match ($this->verification->status) {
            PhotoStatus::Approved => __('notifications.verification_approved_body'),
            PhotoStatus::Rejected => __('notifications.verification_rejected_body'),
            default => __('notifications.verification_reviewed_body'),
        };

        return (new FcmMessage(notification: new FcmNotification(
            title: $title,
            body: $body,
        )))->data([
            'type' => 'verification_reviewed',
            'verification_id' => (string) $this->verification->id,
            'status' => $this->verification->status->value,
        ]);
    }
}
