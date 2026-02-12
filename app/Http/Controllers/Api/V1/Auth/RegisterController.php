<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CompleteRegistrationRequest;
use App\Http\Requests\Api\V1\Auth\SendRegistrationCodeRequest;
use App\Http\Requests\Api\V1\Auth\VerifyRegistrationCodeRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\MobizonSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(
        private readonly MobizonSmsService $smsService
    ) {}

    public function sendCode(SendRegistrationCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');

        $code = $this->smsService->generateCode();
        Cache::put("phone_verification:{$phone}", $code, now()->addMinutes(10));
        $this->smsService->sendVerificationCode($phone, $code);

        return response()->json([
            'message' => __('auth.verification_code_sent'),
        ]);
    }

    public function verifyCode(VerifyRegistrationCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');

        $cachedCode = Cache::get("phone_verification:{$phone}");

        if (! $cachedCode || $cachedCode !== $code) {
            return response()->json([
                'message' => __('auth.invalid_verification_code'),
                'error_code' => 'INVALID_CODE',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Cache::forget("phone_verification:{$phone}");

        $verificationToken = Str::uuid()->toString();
        Cache::put("registration_token:{$verificationToken}", $phone, now()->addMinutes(30));

        return response()->json([
            'message' => __('auth.phone_verified'),
            'data' => [
                'verification_token' => $verificationToken,
            ],
        ]);
    }

    public function complete(CompleteRegistrationRequest $request): JsonResponse
    {
        $token = $request->validated('verification_token');
        $phone = Cache::get("registration_token:{$token}");

        if (! $phone) {
            return response()->json([
                'message' => __('auth.invalid_verification_token'),
                'error_code' => 'INVALID_TOKEN',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Double-check phone not taken by a verified user
        $verifiedExists = User::query()
            ->where('phone', $phone)
            ->whereNotNull('phone_verified_at')
            ->exists();

        if ($verifiedExists) {
            Cache::forget("registration_token:{$token}");

            return response()->json([
                'message' => __('validation.phone_taken'),
                'error_code' => 'PHONE_TAKEN',
            ], Response::HTTP_CONFLICT);
        }

        // Handle abandoned registration: update existing unverified user
        $existingUser = User::query()
            ->where('phone', $phone)
            ->whereNull('phone_verified_at')
            ->first();

        if ($existingUser) {
            $existingUser->tokens()->delete();
            $existingUser->update([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'age' => $request->validated('age'),
                'gender' => $request->validated('gender'),
                'password' => Hash::make($request->validated('password')),
                'city_id' => $request->validated('city_id'),
                'status' => UserStatus::Active,
                'phone_verified_at' => now(),
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $request->validated('name'),
                'phone' => $phone,
                'email' => $request->validated('email'),
                'age' => $request->validated('age'),
                'gender' => $request->validated('gender'),
                'password' => Hash::make($request->validated('password')),
                'city_id' => $request->validated('city_id'),
                'status' => UserStatus::Active,
                'phone_verified_at' => now(),
            ]);
        }

        // Consume the registration token
        Cache::forget("registration_token:{$token}");

        $authToken = $user->createToken($request->header('User-Agent', 'mobile-app'))->plainTextToken;

        return response()->json([
            'message' => __('auth.registered'),
            'data' => [
                'user' => new UserResource($user->load('city')),
                'token' => $authToken,
            ],
        ], Response::HTTP_CREATED);
    }
}
