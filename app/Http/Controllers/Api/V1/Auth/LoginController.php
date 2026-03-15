<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function __construct(private TokenService $tokenService) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->validated('phone'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => __('auth.failed'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->status === UserStatus::Banned) {
            return response()->json([
                'message' => __('auth.account_banned'),
                'error_code' => 'ACCOUNT_BANNED',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->status === UserStatus::Suspended) {
            return response()->json([
                'message' => __('auth.account_suspended'),
                'error_code' => 'ACCOUNT_SUSPENDED',
            ], Response::HTTP_FORBIDDEN);
        }

        $user->updateQuietly([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        $tokenData = $this->tokenService->createTokenPair($user);

        return response()->json([
            'message' => __('auth.login_success'),
            'data' => [
                'user' => new UserResource($user->load('city.translations')),
                'token' => $tokenData['access_token'], // backward compatibility
                ...$tokenData,
            ],
        ]);
    }
}
