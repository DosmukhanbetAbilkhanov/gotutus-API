<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\JoinRequestStatus;
use App\Models\JoinRequest;
use App\Models\User;

class JoinRequestPolicy
{
    /**
     * View own join requests.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * View a join request if you're the requester or hangout owner.
     */
    public function view(User $user, JoinRequest $joinRequest): bool
    {
        return $user->id === $joinRequest->user_id
            || $user->id === $joinRequest->hangoutRequest->user_id;
    }

    /**
     * Only hangout owner can approve join requests.
     */
    public function approve(User $user, JoinRequest $joinRequest): bool
    {
        return $user->id === $joinRequest->hangoutRequest->user_id
            && $joinRequest->status === JoinRequestStatus::Pending;
    }

    /**
     * Only hangout owner can decline join requests.
     */
    public function decline(User $user, JoinRequest $joinRequest): bool
    {
        return $user->id === $joinRequest->hangoutRequest->user_id
            && $joinRequest->status === JoinRequestStatus::Pending;
    }

    /**
     * Only the join requester can confirm after approval.
     */
    public function confirm(User $user, JoinRequest $joinRequest): bool
    {
        return $user->id === $joinRequest->user_id
            && $joinRequest->status === JoinRequestStatus::Approved;
    }

    /**
     * Only the join requester can cancel their own request (if pending or approved).
     */
    public function cancel(User $user, JoinRequest $joinRequest): bool
    {
        return $user->id === $joinRequest->user_id
            && in_array($joinRequest->status, [JoinRequestStatus::Pending, JoinRequestStatus::Approved], true);
    }
}
