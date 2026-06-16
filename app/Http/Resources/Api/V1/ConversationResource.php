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

        $isGroup = $this->isGroup();

        return [
            'id' => $this->id,
            'type' => $isGroup ? 'group' : 'direct',
            'hangout_request' => new HangoutRequestResource($this->whenLoaded('hangoutRequest')),
            // Group rooms have many members; for direct chats this stays a single "other user".
            'participants' => $this->when(
                $isGroup && $this->relationLoaded('participants'),
                fn () => UserResource::collection($this->participants),
            ),
            'participant_count' => $this->when(
                $isGroup && $this->relationLoaded('participants'),
                fn () => $this->participants->count(),
            ),
            // other_user / presence only make sense for 1:1 conversations.
            'other_user' => $this->when(
                ! $isGroup && $this->relationLoaded('hangoutRequest'),
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
            'is_online' => $this->when(
                ! $isGroup && $this->relationLoaded('hangoutRequest'),
                function () use ($user) {
                    $otherUser = $this->otherUserFor($user);

                    return $otherUser->is_online ?? false;
                },
            ),
            'last_seen_at' => $this->when(
                ! $isGroup && $this->relationLoaded('hangoutRequest'),
                function () use ($user) {
                    $otherUser = $this->otherUserFor($user);

                    return $otherUser->last_seen_at?->toIso8601String();
                },
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
