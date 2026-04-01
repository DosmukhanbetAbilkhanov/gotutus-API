<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user()->load(['city.translations', 'photos', 'interests.translations']);

        return new UserResource($user);
    }

    public function profile(User $user): UserResource
    {
        $user->load([
            'city.translations',
            'photos' => fn ($q) => $q->approved(),
            'interests.translations',
        ]);

        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'message' => __('user.profile_updated'),
            'data' => new UserResource($user->fresh()->load(['city.translations', 'photos', 'interests.translations'])),
        ]);
    }
}
