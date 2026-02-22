<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\SendPasswordResetCodeRequest;
use App\Http\Requests\Api\V1\Auth\VerifyPasswordResetCodeRequest;
use App\Models\User;
use App\Services\MobizonSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    public function __construct(private MobizonSmsService $smsService) {}

    public function sendCode(SendPasswordResetCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("password_reset_code:{$phone}", $code, now()->addMinutes(5));

        $this->smsService->send($phone, "Your password reset code: {$code}");

        return response()->json([
            'message' => __('auth.code_sent'),
        ]);
    }

    public function verifyCode(VerifyPasswordResetCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');

        $cachedCode = Cache::get("password_reset_code:{$phone}");

        if (! $cachedCode || $cachedCode !== $code) {
            return response()->json([
                'message' => __('auth.invalid_code'),
                'error_code' => 'INVALID_CODE',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $resetToken = Str::uuid()->toString();

        Cache::put("password_reset_token:{$phone}", $resetToken, now()->addMinutes(10));
        Cache::forget("password_reset_code:{$phone}");

        return response()->json([
            'message' => __('auth.code_verified'),
            'data' => [
                'reset_token' => $resetToken,
            ],
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $resetToken = $request->validated('reset_token');

        $cachedToken = Cache::get("password_reset_token:{$phone}");

        if (! $cachedToken || $cachedToken !== $resetToken) {
            return response()->json([
                'message' => __('auth.invalid_token'),
                'error_code' => 'INVALID_TOKEN',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return response()->json([
                'message' => __('auth.user_not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        Cache::forget("password_reset_token:{$phone}");

        return response()->json([
            'message' => __('auth.password_reset_success'),
        ]);
    }
}
