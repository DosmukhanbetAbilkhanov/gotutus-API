<?php

namespace Database\Factories;

use App\Models\HangoutRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hangout_request_id' => HangoutRequest::factory()->matched(),
        ];
    }
}
