<?php

namespace Database\Factories;

use App\Enums\PhotoStatus;
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
            'status' => PhotoStatus::Approved,
            'is_approved' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhotoStatus::Pending,
            'is_approved' => false,
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhotoStatus::Rejected,
            'rejection_reason' => $reason ?? 'Does not meet community guidelines',
            'is_approved' => false,
        ]);
    }
}
