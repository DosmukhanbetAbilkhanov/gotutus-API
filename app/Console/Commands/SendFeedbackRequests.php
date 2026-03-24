<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HangoutRequestStatus;
use App\Models\HangoutRequest;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendFeedbackRequests extends Command
{
    protected $signature = 'feedback:send-requests';

    protected $description = 'Send feedback notifications for hangouts completed 24+ hours ago';

    public function handle(NotificationService $notificationService): int
    {
        $hangouts = HangoutRequest::query()
            ->where('status', HangoutRequestStatus::Completed)
            ->whereNull('feedback_requested_at')
            ->where('updated_at', '<=', now()->subHours(24))
            ->with(['user', 'place.translations', 'joinRequests' => fn ($q) => $q->whereIn('status', ['approved', 'confirmed'])->with('user')])
            ->get();

        $count = 0;

        foreach ($hangouts as $hangout) {
            // Notify creator
            $hasPlace = $hangout->place_id !== null;
            $body = $hasPlace
                ? 'Report attendance, rate participants and the venue'
                : 'Report attendance and rate the experience';

            $notificationService->send(
                $hangout->user,
                'hangout_feedback_attendance',
                'How was your hangout?',
                $body,
                ['hangout_request_id' => $hangout->id],
            );

            // Notify each participant
            foreach ($hangout->joinRequests as $joinRequest) {
                if ($joinRequest->user) {
                    $notificationService->send(
                        $joinRequest->user,
                        'hangout_feedback_rating',
                        'Rate Your Hangout',
                        'How was your experience? Rate the participants!',
                        ['hangout_request_id' => $hangout->id],
                    );
                }
            }

            $hangout->update(['feedback_requested_at' => now()]);
            $count++;
        }

        $this->info("Sent feedback requests for {$count} hangouts.");

        return self::SUCCESS;
    }
}
