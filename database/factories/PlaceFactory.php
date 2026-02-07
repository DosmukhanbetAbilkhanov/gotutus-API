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
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($place) {
            $name = fake()->company();
            $address = fake()->streetAddress();
            $place->translations()->createMany([
                ['language_code' => 'ru', 'name' => $name, 'address' => $address],
                ['language_code' => 'kz', 'name' => $name, 'address' => $address],
                ['language_code' => 'en', 'name' => $name, 'address' => $address],
            ]);
        });
    }
}
