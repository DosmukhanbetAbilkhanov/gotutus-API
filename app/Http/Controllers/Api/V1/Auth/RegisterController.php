<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CompleteRegistrationRequest;
use App\Http\Requests\Api\V1\Auth\SendRegistrationCodeRequest;
use App\Http\Requests\Api\V1\Auth\VerifyRegistrationCodeRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\LegalPage;
use App\Models\User;
use App\Services\MobizonSmsService;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(
        private MobizonSmsService $smsService,
        private TokenService $tokenService,
    ) {}

    public function sendCode(SendRegistrationCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("registration_code:{$phone}", $code, now()->addMinutes(5));

        try {
            $this->smsService->send($phone, "Your verification code: {$code}");
        } catch (\Throwable $e) {
            Log::error('SMS send failed during registration', ['phone' => $phone, 'error' => $e->getMessage()]);
            Cache::forget("registration_code:{$phone}");

            return response()->json([
                'message' => __('auth.sms_send_failed'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

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

        $verificationToken = Str::uuid()->toString();
        Cache::put("registration_token:{$verificationToken}", $phone, now()->addMinutes(10));

        return response()->json([
            'message' => __('auth.code_verified'),
            'data' => [
                'verification_token' => $verificationToken,
            ],
        ]);
    }

    public function complete(CompleteRegistrationRequest $request): JsonResponse
    {
        $verificationToken = $request->validated('verification_token');
        $phone = Cache::get("registration_token:{$verificationToken}");

        if (! $phone) {
            return response()->json([
                'message' => __('auth.phone_not_verified'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validate that the submitted offer version matches the currently active one
        $activePage = LegalPage::getActive(LegalPage::SLUG_PUBLIC_OFFER);
        if ($activePage && $activePage->version !== $request->validated('public_offer_version')) {
            return response()->json([
                'message' => __('legal.offer_version_mismatch'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        [$user, $tokenData] = DB::transaction(function () use ($request, $phone, $verificationToken) {
            $user = User::create([
                ...$request->safe()->except(['verification_token', 'password_confirmation', 'public_offer_accepted']),
                'phone' => $phone,
                'phone_verified_at' => now(),
                'status' => \App\Enums\UserStatus::Active,
                'public_offer_accepted_at' => now(),
            ]);

            $tokenData = $this->tokenService->createTokenPair($user);

            return [$user, $tokenData];
        });

        Cache::forget("registration_code:{$phone}");
        Cache::forget("registration_token:{$verificationToken}");

        return response()->json([
            'message' => __('auth.registered'),
            'data' => [
                'user' => new UserResource($user->load(['city.translations', 'photos'])),
                'token' => $tokenData['access_token'], // backward compatibility
                ...$tokenData,
            ],
        ], Response::HTTP_CREATED);
    }
}
