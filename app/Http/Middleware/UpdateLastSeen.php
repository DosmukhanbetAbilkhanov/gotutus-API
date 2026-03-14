<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($user = $request->user()) {
            $cacheKey = "user:{$user->id}:last_seen";

            // Only write to DB once every 2 minutes per user
            if (! Cache::has($cacheKey)) {
                $user->updateQuietly([
                    'is_online' => true,
                    'last_seen_at' => now(),
                ]);

                Cache::put($cacheKey, true, 120); // 2 minutes
            }
        }

        return $response;
    }
}
