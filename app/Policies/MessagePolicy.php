<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Only the message sender can view their own message details.
     */
    public function view(User $user, Message $message): bool
    {
        $conversation = $message->conversation;
        $hangoutRequest = $conversation->hangoutRequest;

        // Is the hangout owner
        if ($hangoutRequest->user_id === $user->id) {
            return true;
        }

        // Is the confirmed joiner
        $confirmedJoinRequest = $hangoutRequest->confirmedJoinRequest;

        return $confirmedJoinRequest && $confirmedJoinRequest->user_id === $user->id;
    }
}
