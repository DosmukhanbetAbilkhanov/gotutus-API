<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityType>
 */
class ActivityTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'icon' => 'icon-'.fake()->word().'.png',
            'bg_photo' => 'bg-'.fake()->word().'.jpg',
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($activityType) {
            $name = fake()->word();
            $activityType->translations()->createMany([
                ['language_code' => 'ru', 'name' => $name],
                ['language_code' => 'kz', 'name' => $name],
                ['language_code' => 'en', 'name' => $name],
            ]);
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
