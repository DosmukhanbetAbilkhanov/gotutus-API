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
use Symfony\Component\HttpFoundation\Response;

class BlockedUserController extends Controller
{
    /**
     * List blocked users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $blockedUsers = $request->user()
            ->blockedUsers()
            ->with(['blockedUser.photos' => fn ($q) => $q->approved()])
            ->latest()
            ->paginate(20);

        return BlockedUserResource::collection($blockedUsers);
    }

    /**
     * Block a user.
     */
    public function store(StoreBlockedUserRequest $request): JsonResponse
    {
        $user = $request->user();
        $blockedUserId = $request->validated('blocked_user_id');

        // Cannot block yourself
        if ($user->id === $blockedUserId) {
            return response()->json([
                'message' => __('blocked_user.cannot_block_self'),
                'error_code' => 'CANNOT_BLOCK_SELF',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if already blocked
        $exists = BlockedUser::where('user_id', $user->id)
            ->where('blocked_user_id', $blockedUserId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => __('blocked_user.already_blocked'),
                'error_code' => 'ALREADY_BLOCKED',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $blockedUser = BlockedUser::create([
            'user_id' => $user->id,
            'blocked_user_id' => $blockedUserId,
        ]);

        $blockedUser->load(['blockedUser']);

        return response()->json([
            'message' => __('blocked_user.blocked'),
            'data' => new BlockedUserResource($blockedUser),
        ], Response::HTTP_CREATED);
    }

    /**
     * Unblock a user.
     */
    public function destroy(Request $request, BlockedUser $blockedUser): JsonResponse
    {
        // Ensure user owns this block record
        if ($blockedUser->user_id !== $request->user()->id) {
            return response()->json([
                'message' => __('blocked_user.not_found'),
                'error_code' => 'NOT_FOUND',
            ], Response::HTTP_NOT_FOUND);
        }

        $blockedUser->delete();

        return response()->json([
            'message' => __('blocked_user.unblocked'),
        ]);
    }
}
