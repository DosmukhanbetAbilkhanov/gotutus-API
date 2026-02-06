<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\HangoutRequestStatus;
use App\Models\HangoutRequest;
use App\Models\User;

class HangoutRequestPolicy
{
    /**
     * Users can view any hangout requests in their city.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Users can view hangout requests in their city.
     */
    public function view(User $user, HangoutRequest $hangoutRequest): bool
    {
        return $user->city_id === $hangoutRequest->city_id;
    }

    /**
     * Authenticated users can create hangout requests.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the owner can update their hangout request (if still open).
     */
    public function update(User $user, HangoutRequest $hangoutRequest): bool
    {
        return $user->id === $hangoutRequest->user_id
            && $hangoutRequest->status === HangoutRequestStatus::Open;
    }

    /**
     * Only the owner can delete/cancel their hangout request.
     */
    public function delete(User $user, HangoutRequest $hangoutRequest): bool
    {
        return $user->id === $hangoutRequest->user_id;
    }

    /**
     * Check if user can view join requests for this hangout.
     */
    public function viewJoinRequests(User $user, HangoutRequest $hangoutRequest): bool
    {
        return $user->id === $hangoutRequest->user_id;
    }

    /**
     * Check if user can join this hangout request.
     */
    public function join(User $user, HangoutRequest $hangoutRequest): bool
    {
        // Cannot join own request
        if ($user->id === $hangoutRequest->user_id) {
            return false;
        }

        // Must be in same city
        if ($user->city_id !== $hangoutRequest->city_id) {
            return false;
        }

        // Must be open
        if ($hangoutRequest->status !== HangoutRequestStatus::Open) {
            return false;
        }

        // Check if not blocked
        $isBlocked = $hangoutRequest->user->blockedUsers()
            ->where('blocked_user_id', $user->id)
            ->exists();

        if ($isBlocked) {
            return false;
        }

        // Check if user hasn't already sent a join request
        $alreadyRequested = $hangoutRequest->joinRequests()
            ->where('user_id', $user->id)
            ->exists();

        return ! $alreadyRequested;
    }
}
