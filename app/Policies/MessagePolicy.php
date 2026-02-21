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

        // Is the hangout owner
        if ($conversation->hangoutRequest->user_id === $user->id) {
            return true;
        }

        // Is the specific joiner for this conversation
        return $conversation->join_request_id !== null
            && $conversation->joinRequest->user_id === $user->id;
    }
}
