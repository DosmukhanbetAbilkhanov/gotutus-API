<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->status === UserStatus::Banned) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => __('auth.account_banned'),
                'error_code' => 'ACCOUNT_BANNED',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->status === UserStatus::Suspended) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => __('auth.account_suspended'),
                'error_code' => 'ACCOUNT_SUSPENDED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
