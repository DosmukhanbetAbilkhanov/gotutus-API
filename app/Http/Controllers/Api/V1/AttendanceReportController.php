<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Attendance\StoreAttendanceRequest;
use App\Models\AttendanceReport;
use App\Models\HangoutRequest;
use App\Services\TrustScoreService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AttendanceReportController extends Controller
{
    public function __construct(
        private readonly TrustScoreService $trustScoreService,
    ) {}

    public function index(HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = request()->user();

        if ($user->id !== $hangoutRequest->user_id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        $reports = $hangoutRequest->attendanceReports()
            ->with(['reportedUser'])
            ->get();

        return response()->json([
            'data' => $reports,
        ]);
    }

    public function store(StoreAttendanceRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        // Check if attendance already submitted
        $existing = $hangoutRequest->attendanceReports()
            ->where('reporter_user_id', $request->user()->id)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Attendance already reported for this hangout.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $affectedUserIds = [];

        foreach ($request->validated()['attendances'] as $entry) {
            AttendanceReport::create([
                'hangout_request_id' => $hangoutRequest->id,
                'reporter_user_id' => $request->user()->id,
                'reported_user_id' => $entry['user_id'],
                'showed_up' => $entry['showed_up'],
            ]);
            $affectedUserIds[] = $entry['user_id'];
        }

        // Recalculate trust scores for all affected users
        $this->trustScoreService->recalculateForUsers($affectedUserIds);

        return response()->json([
            'message' => 'Attendance reported successfully.',
        ], Response::HTTP_CREATED);
    }
}
