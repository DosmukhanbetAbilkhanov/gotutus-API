<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'is_active' => true,
            // Default geo so city-resolution (GPS → city) works in tests.
            'center_latitude' => 51.1605, // Astana
            'center_longitude' => 71.4704,
            'radius_km' => 40,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (City $city) {
            $name = fake()->city();
            foreach (['ru', 'kz', 'en'] as $locale) {
                $city->translations()->create([
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
