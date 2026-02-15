<?php

namespace Database\Factories;

use App\Enums\JoinRequestStatus;
use App\Models\HangoutRequest;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JoinRequest>
 */
class JoinRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hangout_request_id' => HangoutRequest::factory(),
            'user_id' => User::factory(),
            'place_id' => null,
            'status' => JoinRequestStatus::Pending,
            'message' => fake()->optional()->sentence(),
            'confirmed_at' => null,
        ];
    }

    public function withPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place_id' => Place::factory(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JoinRequestStatus::Approved,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JoinRequestStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JoinRequestStatus::Declined,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JoinRequestStatus::Cancelled,
        ]);
    }
}
