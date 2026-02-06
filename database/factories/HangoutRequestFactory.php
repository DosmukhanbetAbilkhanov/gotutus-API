<?php

namespace Database\Factories;

use App\Enums\HangoutRequestStatus;
use App\Models\ActivityType;
use App\Models\City;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HangoutRequest>
 */
class HangoutRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'city_id' => City::factory(),
            'activity_type_id' => ActivityType::factory(),
            'place_id' => null,
            'date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'time' => fake()->time('H:i'),
            'status' => HangoutRequestStatus::Open,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function withPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place_id' => Place::factory(),
        ]);
    }

    public function matched(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HangoutRequestStatus::Matched,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HangoutRequestStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HangoutRequestStatus::Cancelled,
        ]);
    }
}
