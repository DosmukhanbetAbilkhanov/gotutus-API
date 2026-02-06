<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($place) {
            $name = fake()->company();
            $address = fake()->streetAddress();
            $place->translations()->createMany([
                ['locale' => 'ru', 'name' => $name, 'address' => $address],
                ['locale' => 'kz', 'name' => $name, 'address' => $address],
                ['locale' => 'en', 'name' => $name, 'address' => $address],
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
