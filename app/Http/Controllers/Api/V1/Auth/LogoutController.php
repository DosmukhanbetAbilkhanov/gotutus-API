<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        // Handle real tokens (from API) vs transient tokens (from actingAs in tests)
        if (method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }
}
