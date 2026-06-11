<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use App\Services\NotificationService;

class UserPhotoObserver
{
    public function updated(UserPhoto $photo): void
    {
        if (! $photo->wasChanged('status') || $photo->status === PhotoStatus::Pending) {
            return;
        }

        // Use the app's NotificationService (custom notifications table + FCM +
        // WebSocket). The native ['database'] channel is NOT compatible with the
        // custom table — it writes the class name into type(50) and omits title/body.
        $title = match ($photo->status) {
            PhotoStatus::Approved => __('notifications.photo_approved_title'),
            PhotoStatus::Rejected => __('notifications.photo_rejected_title'),
            default => __('notifications.photo_reviewed_title'),
        };

        $body = match ($photo->status) {
            PhotoStatus::Approved => __('notifications.photo_approved_body'),
            PhotoStatus::Rejected => __('notifications.photo_rejected_body'),
            default => __('notifications.photo_reviewed_body'),
        };

        app(NotificationService::class)->send(
            $photo->user,
            'photo_reviewed',
            $title,
            $body,
            [
                'photo_id' => $photo->id,
                'status' => $photo->status->value,
                'rejection_reason' => $photo->rejection_reason,
            ],
        );
    }
}
