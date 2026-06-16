<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('city.{cityId}', function ($user, $cityId) {
    return true; // Any authenticated user can listen (hangouts are public)
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::with(['hangoutRequest', 'joinRequest'])->find($conversationId);

    if (! $conversation) {
        return false;
    }

    // Group conversation: any confirmed participant (tracked in conversation_user).
    if ($conversation->isGroup()) {
        return $conversation->participants()->where('users.id', $user->id)->exists();
    }

    // Owner of the hangout request
    if ($conversation->hangoutRequest->user_id === $user->id) {
        return true;
    }

    // The specific joiner for this conversation
    return $conversation->join_request_id !== null
        && $conversation->joinRequest->user_id === $user->id;
});
