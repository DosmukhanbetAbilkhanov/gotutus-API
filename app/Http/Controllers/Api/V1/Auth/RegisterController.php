<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\MobizonSmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(
        private readonly MobizonSmsService $smsService
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'age' => $request->validated('age'),
            'gender' => $request->validated('gender'),
            'password' => Hash::make($request->validated('password')),
            'city_id' => $request->validated('city_id'),
            'status' => UserStatus::Active,
        ]);

        // Generate and send verification code
        $code = $this->smsService->generateCode();
        Cache::put("phone_verification:{$user->phone}", $code, now()->addMinutes(10));
        $this->smsService->sendVerificationCode($user->phone, $code);

        $token = $user->createToken($request->header('User-Agent', 'mobile-app'))->plainTextToken;

        return response()->json([
            'message' => __('auth.registered'),
            'data' => [
                'user' => new UserResource($user->load('city')),
                'token' => $token,
                'phone_verified' => false,
            ],
        ], Response::HTTP_CREATED);
    }
}
