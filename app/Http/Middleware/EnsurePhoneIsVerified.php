<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->phone_verified_at) {
            return response()->json([
                'message' => __('auth.phone_not_verified'),
                'error_code' => 'PHONE_NOT_VERIFIED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
