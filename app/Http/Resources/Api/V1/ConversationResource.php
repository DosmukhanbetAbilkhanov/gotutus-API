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
        $user = $request->user();

        return [
            'id' => $this->id,
            'hangout_request' => new HangoutRequestResource($this->whenLoaded('hangoutRequest')),
            'other_user' => $this->when(
                $this->relationLoaded('hangoutRequest'),
                function () use ($user) {
                    $otherUser = $this->otherUserFor($user);

                    return $otherUser ? new UserResource($otherUser) : null;
                },
            ),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'unread_count' => $this->when(
                $this->relationLoaded('participants'),
                fn () => $this->unreadCountFor($user->id),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
