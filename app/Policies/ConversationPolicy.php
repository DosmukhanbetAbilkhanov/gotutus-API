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
     * Determine if user is a participant (hangout owner or confirmed joiner).
     */
    private function isParticipant(User $user, Conversation $conversation): bool
    {
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
