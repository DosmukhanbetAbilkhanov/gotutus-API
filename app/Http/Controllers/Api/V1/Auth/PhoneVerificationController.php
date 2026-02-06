<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\VerifyPhoneRequest;
use App\Services\MobizonSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PhoneVerificationController extends Controller
{
    public function __construct(
        private readonly MobizonSmsService $smsService
    ) {}

    public function sendCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isPhoneVerified()) {
            return response()->json([
                'message' => __('auth.phone_already_verified'),
            ]);
        }

        $code = $this->smsService->generateCode();
        Cache::put("phone_verification:{$user->phone}", $code, now()->addMinutes(10));
        $this->smsService->sendVerificationCode($user->phone, $code);

        return response()->json([
            'message' => __('auth.verification_code_sent'),
        ]);
    }

    public function verify(VerifyPhoneRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isPhoneVerified()) {
            return response()->json([
                'message' => __('auth.phone_already_verified'),
            ]);
        }

        $cachedCode = Cache::get("phone_verification:{$user->phone}");

        if (! $cachedCode || $cachedCode !== $request->validated('code')) {
            return response()->json([
                'message' => __('auth.invalid_verification_code'),
                'error_code' => 'INVALID_CODE',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['phone_verified_at' => now()]);
        Cache::forget("phone_verification:{$user->phone}");

        return response()->json([
            'message' => __('auth.phone_verified'),
        ]);
    }
}
