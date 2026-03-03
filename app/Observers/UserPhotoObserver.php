<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use App\Notifications\PhotoReviewedNotification;

class UserPhotoObserver
{
    public function updated(UserPhoto $photo): void
    {
        if ($photo->wasChanged('status') && $photo->status !== PhotoStatus::Pending) {
            $photo->user->notify(new PhotoReviewedNotification($photo));
        }
    }
}
