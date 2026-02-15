<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Report\StoreReportRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request): JsonResponse
    {
        $request->user()->reportsSent()->create($request->validated());

        return response()->json([
            'message' => __('report.submitted'),
        ], Response::HTTP_CREATED);
    }
}
