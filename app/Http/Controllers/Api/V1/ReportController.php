<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Report\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    /**
     * Submit a report about a user.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $user = $request->user();
        $reportedUserId = $request->validated('reported_user_id');

        // Cannot report yourself
        if ($user->id === $reportedUserId) {
            return response()->json([
                'message' => __('report.cannot_report_self'),
                'error_code' => 'CANNOT_REPORT_SELF',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Report::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $reportedUserId,
            'hangout_request_id' => $request->validated('hangout_request_id'),
            'reason' => $request->validated('reason'),
        ]);

        return response()->json([
            'message' => __('report.submitted'),
        ], Response::HTTP_CREATED);
    }
}
