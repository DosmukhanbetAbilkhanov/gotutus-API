<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Place $place) {
            foreach (['ru', 'kz', 'en'] as $locale) {
                $place->translations()->create([
                    'language_code' => $locale,
                    'name' => fake()->company(),
                    'address' => fake()->streetAddress(),
                ]);
            }
        });
    }
}
