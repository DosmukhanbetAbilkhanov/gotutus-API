<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+7700'.fake()->unique()->numerify('#######'),
            'age' => fake()->numberBetween(18, 65),
            'gender' => fake()->randomElement(Gender::cases()),
            'password' => static::$password ??= Hash::make('password'),
            'city_id' => City::factory(),
            'status' => UserStatus::Active,
            'phone_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Suspended,
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Banned,
        ]);
    }
}
