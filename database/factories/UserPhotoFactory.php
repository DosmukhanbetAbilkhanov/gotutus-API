<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPhoto>
 */
class UserPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'photo_url' => 'user-photos/'.Str::uuid().'.jpg',
            'is_approved' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }
}
