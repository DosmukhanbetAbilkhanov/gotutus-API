<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HangoutRequestStatus;
use App\Models\HangoutRequest;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendFeedbackReminders extends Command
{
    protected $signature = 'feedback:send-reminders';

    protected $description = 'Send reminder notifications for hangouts that still need feedback (3-7 days after completion)';

    public function handle(NotificationService $notificationService): int
    {
        $hangouts = HangoutRequest::query()
            ->where('status', HangoutRequestStatus::Completed)
            ->whereNotNull('feedback_requested_at')
            ->whereBetween('feedback_requested_at', [now()->subDays(7), now()->subDays(3)])
            ->with(['user', 'joinRequests' => fn ($q) => $q->whereIn('status', ['approved', 'confirmed'])->with('user')])
            ->get();

        $count = 0;

        foreach ($hangouts as $hangout) {
            // Check if creator has submitted attendance
            $hasAttendance = $hangout->attendanceReports()
                ->where('reporter_user_id', $hangout->user_id)
                ->exists();

            if (! $hasAttendance) {
                $notificationService->send(
                    $hangout->user,
                    'hangout_feedback_reminder',
                    "Don't forget to rate!",
                    'You have hangouts waiting for your feedback',
                    ['hangout_request_id' => $hangout->id],
                );
                $count++;
            }

            // Check each participant for missing ratings
            foreach ($hangout->joinRequests as $joinRequest) {
                if (! $joinRequest->user) {
                    continue;
                }

                $hasRated = $hangout->ratings()
                    ->where('rater_user_id', $joinRequest->user_id)
                    ->exists();

                if (! $hasRated) {
                    $notificationService->send(
                        $joinRequest->user,
                        'hangout_feedback_reminder',
                        "Don't forget to rate!",
                        'You have hangouts waiting for your feedback',
                        ['hangout_request_id' => $hangout->id],
                    );
                    $count++;
                }
            }
        }

        $this->info("Sent {$count} feedback reminders.");

        return self::SUCCESS;
    }
}
