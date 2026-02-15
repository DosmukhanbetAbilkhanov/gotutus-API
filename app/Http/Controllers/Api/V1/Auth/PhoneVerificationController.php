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
    public function __construct(private MobizonSmsService $smsService) {}

    public function sendCode(Request $request): JsonResponse
    {
        $phone = $request->user()->phone;
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Cache::put("phone_verification:{$phone}", $code, now()->addMinutes(5));

        $this->smsService->send($phone, "Your verification code: {$code}");

        return response()->json([
            'message' => __('auth.code_sent'),
        ]);
    }

    public function verify(VerifyPhoneRequest $request): JsonResponse
    {
        $phone = $request->user()->phone;
        $code = $request->validated('code');

        $cachedCode = Cache::get("phone_verification:{$phone}");

        if (! $cachedCode || $cachedCode !== $code) {
            return response()->json([
                'message' => __('auth.invalid_code'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->user()->update(['phone_verified_at' => now()]);

        Cache::forget("phone_verification:{$phone}");

        return response()->json([
            'message' => __('auth.phone_verified'),
        ]);
    }
}
