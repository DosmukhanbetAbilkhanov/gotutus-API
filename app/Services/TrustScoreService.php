<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AttendanceReport;
use App\Models\HangoutRating;
use App\Models\User;

class TrustScoreService
{
    public function recalculateForUser(User $user): void
    {
        $attendanceReports = AttendanceReport::where('reported_user_id', $user->id);
        $totalReports = $attendanceReports->count();
        $showedUpCount = (clone $attendanceReports)->where('showed_up', true)->count();

        $attendanceRate = $totalReports > 0
            ? ($showedUpCount / $totalReports) * 100
            : null;

        $ratingsQuery = HangoutRating::where('rated_user_id', $user->id);
        $ratingsCount = $ratingsQuery->count();
        $averageRating = $ratingsCount > 0
            ? (float) (clone $ratingsQuery)->avg('rating')
            : null;

        $trustScore = null;
        if ($averageRating !== null && $attendanceRate !== null) {
            $trustScore = ($averageRating * 0.6) + (($attendanceRate / 100) * 5 * 0.4);
        } elseif ($averageRating !== null) {
            $trustScore = $averageRating;
        } elseif ($attendanceRate !== null) {
            $trustScore = ($attendanceRate / 100) * 5;
        }

        $user->update([
            'trust_score' => $trustScore !== null ? round($trustScore, 2) : null,
            'ratings_count' => $ratingsCount,
            'average_rating' => $averageRating !== null ? round($averageRating, 2) : null,
            'attendance_rate' => $attendanceRate !== null ? round($attendanceRate, 2) : null,
        ]);
    }

    public function recalculateForUsers(array $userIds): void
    {
        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            $this->recalculateForUser($user);
        }
    }
}
