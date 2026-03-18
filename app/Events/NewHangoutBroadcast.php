<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\Api\V1\HangoutRequestResource;
use App\Models\HangoutRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewHangoutBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HangoutRequest $hangout,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('city.'.$this->hangout->city_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'hangout.created';
    }

    public function broadcastWith(): array
    {
        $this->hangout->load([
            'user.photos' => fn ($q) => $q->where('status', 'approved'),
            'city.translations',
            'activityType.translations',
            'place.translations',
            'place.activeDiscount',
        ]);

        return [
            'hangout' => (new HangoutRequestResource($this->hangout))->resolve(),
        ];
    }
}
