<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CompleteRegistrationRequest;
use App\Http\Requests\Api\V1\Auth\SendRegistrationCodeRequest;
use App\Http\Requests\Api\V1\Auth\VerifyRegistrationCodeRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\MobizonSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(private MobizonSmsService $smsService) {}

    public function sendCode(SendRegistrationCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Cache::put("registration_code:{$phone}", $code, now()->addMinutes(5));

        $this->smsService->send($phone, "Your verification code: {$code}");

        return response()->json([
            'message' => __('auth.code_sent'),
        ]);
    }

    public function verifyCode(VerifyRegistrationCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');

        $cachedCode = Cache::get("registration_code:{$phone}");

        if (! $cachedCode || $cachedCode !== $code) {
            return response()->json([
                'message' => __('auth.invalid_code'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Cache::put("registration_verified:{$phone}", true, now()->addMinutes(10));

        return response()->json([
            'message' => __('auth.code_verified'),
        ]);
    }

    public function complete(CompleteRegistrationRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');

        if (! Cache::get("registration_verified:{$phone}")) {
            return response()->json([
                'message' => __('auth.phone_not_verified'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::create([
            ...$request->validated(),
            'phone_verified_at' => now(),
            'status' => \App\Enums\UserStatus::Active,
        ]);

        Cache::forget("registration_code:{$phone}");
        Cache::forget("registration_verified:{$phone}");

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => __('auth.registered'),
            'data' => [
                'user' => new UserResource($user->load('city.translations')),
                'token' => $token,
            ],
        ], Response::HTTP_CREATED);
    }
}
