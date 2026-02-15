<?php

namespace Database\Factories;

use App\Models\ActivityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityType>
 */
class ActivityTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'icon' => fake()->word().'.png',
            'bg_photo' => fake()->word().'-bg.jpg',
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ActivityType $activityType) {
            $name = fake()->word();
            foreach (['ru', 'kz', 'en'] as $locale) {
                $activityType->translations()->create([
                    'language_code' => $locale,
                    'name' => $name,
                ]);
            }
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
