<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Check if user is a participant in the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $this->isParticipant($user, $conversation);
    }

    /**
     * Check if user can send messages in this conversation.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->isParticipant($user, $conversation);
    }

    /**
     * Determine if user is a participant (hangout owner or approved joiner).
     */
    private function isParticipant(User $user, Conversation $conversation): bool
    {
        $hangout = $conversation->hangoutRequest;

        // Owner of the hangout
        if ($hangout->user_id === $user->id) {
            return true;
        }

        // The specific joiner for this conversation
        return $conversation->join_request_id !== null
            && $conversation->joinRequest->user_id === $user->id;
    }
}
