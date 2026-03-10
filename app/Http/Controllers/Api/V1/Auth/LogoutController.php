<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(private TokenService $tokenService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->updateQuietly([
            'is_online' => false,
        ]);

        // Revoke current access token
        $this->tokenService->revokeCurrentAccessToken($user);

        // Revoke all refresh tokens for this user
        $this->tokenService->revokeAllRefreshTokens($user);

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }
}
