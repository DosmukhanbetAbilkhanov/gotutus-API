<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isPhoneVerified()) {
            return response()->json([
                'message' => 'Phone number is not verified.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
