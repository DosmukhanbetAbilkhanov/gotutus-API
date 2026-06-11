<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\PhotoStatus;
use App\Mail\VerificationSubmittedMail;
use App\Models\PhotoVerification;
use App\Models\User;
use App\Models\UserType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PhotoVerificationObserver
{
    /**
     * Email admins and the submitter's city managers when a new verification
     * selfie is submitted, so it can be reviewed promptly.
     */
    public function created(PhotoVerification $verification): void
    {
        try {
            $user = $verification->user;

            $admins = User::query()
                ->whereHas('userType', fn ($q) => $q->where('slug', UserType::SLUG_ADMIN))
                ->whereNotNull('email')
                ->get();

            $cityManagers = User::query()
                ->where('city_id', $user->city_id)
                ->whereHas('userType', fn ($q) => $q->where('slug', UserType::SLUG_CITY_MANAGER))
                ->whereNotNull('email')
                ->get();

            $recipients = $admins->merge($cityManagers)->unique('id');

            $cityName = $user->city?->name ?? '—';
            $poseLabel = $verification->pose?->label() ?? '—';

            foreach ($recipients as $recipient) {
                $path = $recipient->isAdmin()
                    ? '/admin/photo-verifications'
                    : '/city-manager/photo-verifications';

                Mail::to($recipient->email)->queue(new VerificationSubmittedMail(
                    userName: $user->name,
                    cityName: $cityName,
                    poseLabel: $poseLabel,
                    reviewUrl: url($path),
                ));
            }
        } catch (\Throwable $e) {
            // Never let a notification failure block the user's submission.
            Log::warning('Failed to email moderators about photo verification', [
                'verification_id' => $verification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updated(PhotoVerification $verification): void
    {
        if (! $verification->wasChanged('status') || $verification->status === PhotoStatus::Pending) {
            return;
        }

        // Flip the user's verified badge on approval. Rejections never un-verify
        // a user who was already verified through an earlier submission.
        if ($verification->status === PhotoStatus::Approved) {
            $verification->user->forceFill([
                'photo_verified_at' => now(),
            ])->saveQuietly();
        }

        // Use the app's NotificationService (custom notifications table + FCM +
        // WebSocket). The native ['database'] channel is NOT compatible with the
        // custom table — it writes the class name into type(50) and omits title/body.
        $title = match ($verification->status) {
            PhotoStatus::Approved => __('notifications.verification_approved_title'),
            PhotoStatus::Rejected => __('notifications.verification_rejected_title'),
            default => __('notifications.verification_reviewed_title'),
        };

        $body = match ($verification->status) {
            PhotoStatus::Approved => __('notifications.verification_approved_body'),
            PhotoStatus::Rejected => __('notifications.verification_rejected_body'),
            default => __('notifications.verification_reviewed_body'),
        };

        app(NotificationService::class)->send(
            $verification->user,
            'verification_reviewed',
            $title,
            $body,
            [
                'verification_id' => $verification->id,
                'status' => $verification->status->value,
                'rejection_reason' => $verification->rejection_reason,
            ],
        );
    }
}
