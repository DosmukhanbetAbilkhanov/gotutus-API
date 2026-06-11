<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PhotoStatus;
use App\Http\Controllers\Controller;
use App\Models\PhotoVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminVerificationController extends Controller
{
    public function review(Request $request, PhotoVerification $verification): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:500'],
        ]);

        $verification->update([
            'status' => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => 'Verification reviewed.']);
    }
}
