<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->validated('phone'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => __('auth.failed'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->updateQuietly([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => __('auth.login_success'),
            'data' => [
                'user' => new UserResource($user->load('city.translations')),
                'token' => $token,
            ],
        ]);
    }
}
