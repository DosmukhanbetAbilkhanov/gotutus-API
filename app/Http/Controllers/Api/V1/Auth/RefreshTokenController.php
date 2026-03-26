<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenController extends Controller
{
    public function __construct(private TokenService $tokenService) {}

    public function __invoke(RefreshTokenRequest $request): JsonResponse
    {
        $plainRefreshToken = $request->validated('refresh_token');

        $user = $this->tokenService->validateRefreshToken($plainRefreshToken);

        if (! $user) {
            return response()->json([
                'message' => __('auth.refresh_token_invalid'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->status === UserStatus::Banned) {
            $this->tokenService->revokeRefreshToken($plainRefreshToken);
            $user->tokens()->delete();

            return response()->json([
                'message' => __('auth.account_banned'),
                'error_code' => 'ACCOUNT_BANNED',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->status === UserStatus::Suspended) {
            $this->tokenService->revokeRefreshToken($plainRefreshToken);
            $user->tokens()->delete();

            return response()->json([
                'message' => __('auth.account_suspended'),
                'error_code' => 'ACCOUNT_SUSPENDED',
            ], Response::HTTP_FORBIDDEN);
        }

        // Revoke the old refresh token (token rotation)
        $this->tokenService->revokeRefreshToken($plainRefreshToken);

        // Generate new token pair
        $tokenData = $this->tokenService->createTokenPair($user);

        return response()->json([
            'message' => __('auth.token_refreshed'),
            'data' => [
                'user' => new UserResource($user->load(['city.translations', 'photos'])),
                ...$tokenData,
            ],
        ]);
    }
}
