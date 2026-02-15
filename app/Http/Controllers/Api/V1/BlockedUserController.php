<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BlockedUser\StoreBlockedUserRequest;
use App\Http\Resources\Api\V1\BlockedUserResource;
use App\Models\BlockedUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlockedUserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $blockedUsers = $request->user()
            ->blockedUsers()
            ->with('blockedUser')
            ->latest('created_at')
            ->get();

        return BlockedUserResource::collection($blockedUsers);
    }

    public function store(StoreBlockedUserRequest $request): JsonResponse
    {
        $request->user()->blockedUsers()->create($request->validated());

        return response()->json([
            'message' => __('user.blocked'),
        ], 201);
    }

    public function destroy(Request $request, BlockedUser $blockedUser): JsonResponse
    {
        if ($blockedUser->user_id !== $request->user()->id) {
            abort(403);
        }

        $blockedUser->delete();

        return response()->json([
            'message' => __('user.unblocked'),
        ]);
    }
}
