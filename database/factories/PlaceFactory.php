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

            // Create working hours (Monday=0 .. Sunday=6)
            foreach (range(0, 6) as $day) {
                $isClosed = $day === 6 && fake()->boolean(30); // 30% chance Sunday is closed
                $place->workingHours()->create([
                    'day_of_week' => $day,
                    'open_time' => $isClosed ? null : fake()->randomElement(['08:00', '09:00', '10:00']),
                    'close_time' => $isClosed ? null : fake()->randomElement(['20:00', '21:00', '22:00', '23:00']),
                ]);
            }
        });
    }
}
