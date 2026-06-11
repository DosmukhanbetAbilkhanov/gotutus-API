<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\PhotoStatus;
use App\Enums\VerificationPose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StorePhotoVerificationRequest;
use App\Models\PhotoVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PhotoVerificationController extends Controller
{
    /**
     * Current verification status for the authenticated user.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $latest = $user->photoVerifications()->latest()->first();

        return response()->json([
            'data' => $this->statusPayload($user, $latest),
        ]);
    }

    /**
     * Server picks a randomized pose the user must mimic (anti-spoof).
     */
    public function pose(): JsonResponse
    {
        $pose = VerificationPose::random();

        return response()->json([
            'data' => [
                'pose' => $pose->value,
                'instruction' => $pose->label(),
            ],
        ]);
    }

    public function store(StorePhotoVerificationRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->photoVerifications()->pending()->exists()) {
            return response()->json([
                'message' => __('verification.already_pending'),
            ], Response::HTTP_CONFLICT);
        }

        $path = $request->file('selfie')->store('verifications', 'public');

        $verification = $user->photoVerifications()->create([
            'selfie_path' => $path,
            'pose' => $request->validated('pose'),
            'status' => PhotoStatus::Pending,
        ]);

        return response()->json([
            'message' => __('verification.submitted'),
            'data' => $this->statusPayload($user, $verification),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function statusPayload($user, ?PhotoVerification $latest): array
    {
        if ($user->isPhotoVerified()) {
            $status = 'approved';
        } elseif ($latest?->status === PhotoStatus::Pending) {
            $status = 'pending';
        } elseif ($latest?->status === PhotoStatus::Rejected) {
            $status = 'rejected';
        } else {
            $status = 'none';
        }

        return [
            'status' => $status,
            'pose' => $latest?->pose?->value,
            'rejection_reason' => $status === 'rejected' ? $latest?->rejection_reason : null,
            'can_resubmit' => $status !== 'pending' && $status !== 'approved',
        ];
    }
}
