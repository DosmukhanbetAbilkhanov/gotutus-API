<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\Api\V1\JoinRequestResource;
use App\Models\JoinRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JoinRequestReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public JoinRequest $joinRequest,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->joinRequest->hangoutRequest->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'join-request.received';
    }

    public function broadcastWith(): array
    {
        $this->joinRequest->load(['user', 'place.translations', 'hangoutRequest']);

        return [
            'join_request' => (new JoinRequestResource($this->joinRequest))->resolve(),
        ];
    }
}
