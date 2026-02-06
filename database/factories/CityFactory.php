<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($city) {
            $city->translations()->createMany([
                ['locale' => 'ru', 'name' => fake()->city()],
                ['locale' => 'kz', 'name' => fake()->city()],
                ['locale' => 'en', 'name' => fake()->city()],
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
