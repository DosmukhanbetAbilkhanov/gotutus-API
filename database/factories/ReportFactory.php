<?php

namespace Database\Factories;

use App\Models\HangoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'hangout_request_id' => null,
            'reason' => fake()->paragraph(),
        ];
    }

    public function withHangout(): static
    {
        return $this->state(fn (array $attributes) => [
            'hangout_request_id' => HangoutRequest::factory(),
        ]);
    }
}
