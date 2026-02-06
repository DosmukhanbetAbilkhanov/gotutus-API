<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Conversation
 */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hangoutRequest = $this->hangoutRequest;
        $currentUserId = $request->user()?->id;

        // Determine the other participant
        $otherUser = null;
        if ($hangoutRequest->user_id === $currentUserId) {
            // Current user is the hangout owner, other user is the confirmed joiner
            $confirmedJoinRequest = $hangoutRequest->confirmedJoinRequest;
            $otherUser = $confirmedJoinRequest?->user;
        } else {
            // Current user is the joiner, other user is the hangout owner
            $otherUser = $hangoutRequest->user;
        }

        return [
            'id' => $this->id,
            'hangout_request' => new HangoutRequestResource($this->whenLoaded('hangoutRequest')),
            'other_user' => $otherUser ? new UserResource($otherUser) : null,
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
