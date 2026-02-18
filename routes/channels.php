<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::with('hangoutRequest.joinRequests')->find($conversationId);

    if (! $conversation) {
        return false;
    }

    $hangout = $conversation->hangoutRequest;

    // Owner of the hangout request
    if ($hangout->user_id === $user->id) {
        return true;
    }

    // The confirmed join request sender
    return $hangout->joinRequests
        ->where('user_id', $user->id)
        ->where('status', 'confirmed')
        ->isNotEmpty();
});
