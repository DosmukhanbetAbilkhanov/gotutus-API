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
            ->with(['blockedUser.photos', 'blockedUser.city.translations'])
            ->latest('created_at')
            ->get();

        return BlockedUserResource::collection($blockedUsers);
    }

    public function store(StoreBlockedUserRequest $request): JsonResponse
    {
        $user = $request->user();
        $blockedUserId = $request->validated()['blocked_user_id'];

        $user->blockedUsers()->create($request->validated());

        // Invalidate cached blocked user lists for both users
        cache()->forget("user.{$user->id}.blocked_ids");
        cache()->forget("user.{$blockedUserId}.blocked_by_ids");

        return response()->json([
            'message' => __('user.blocked'),
        ], 201);
    }

    public function destroy(Request $request, BlockedUser $blockedUser): JsonResponse
    {
        $user = $request->user();

        if ($blockedUser->user_id !== $user->id) {
            abort(403);
        }

        $blockedUserId = $blockedUser->blocked_user_id;
        $blockedUser->delete();

        // Invalidate cached blocked user lists for both users
        cache()->forget("user.{$user->id}.blocked_ids");
        cache()->forget("user.{$blockedUserId}.blocked_by_ids");

        return response()->json([
            'message' => __('user.unblocked'),
        ]);
    }
}
