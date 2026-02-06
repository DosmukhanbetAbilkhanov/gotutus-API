<?php

use App\Models\ActivityType;
use App\Models\City;
use App\Models\Conversation;
use App\Models\HangoutRequest;
use App\Models\JoinRequest;
use App\Models\Message;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->activityType = ActivityType::factory()->create();
    $this->hangoutOwner = User::factory()->create(['city_id' => $this->city->id]);
    $this->joiner = User::factory()->create(['city_id' => $this->city->id]);

    $this->hangout = HangoutRequest::factory()->matched()->create([
        'user_id' => $this->hangoutOwner->id,
        'city_id' => $this->city->id,
        'activity_type_id' => $this->activityType->id,
    ]);

    JoinRequest::factory()->confirmed()->create([
        'hangout_request_id' => $this->hangout->id,
        'user_id' => $this->joiner->id,
    ]);

    $this->conversation = Conversation::factory()->create([
        'hangout_request_id' => $this->hangout->id,
    ]);
});

describe('List Conversations', function () {
    it('owner can see their conversations', function () {
        $response = $this->actingAs($this->hangoutOwner)
            ->getJson('/api/v1/conversations');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->conversation->id);
    });

    it('joiner can see their conversations', function () {
        $response = $this->actingAs($this->joiner)
            ->getJson('/api/v1/conversations');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('non-participant cannot see conversation', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($otherUser)
            ->getJson('/api/v1/conversations');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });
});

describe('View Conversation', function () {
    it('participant can view conversation', function () {
        $response = $this->actingAs($this->hangoutOwner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $this->conversation->id);
    });

    it('non-participant cannot view conversation', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($otherUser)
            ->getJson('/api/v1/conversations/'.$this->conversation->id);

        $response->assertStatus(403);
    });
});

describe('List Messages', function () {
    it('participant can view messages', function () {
        Message::factory()->count(5)->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    it('messages are ordered by newest first', function () {
        $oldMessage = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
        ]);

        // Wait a bit to ensure different timestamps
        sleep(1);

        $newMessage = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $newMessage->id)
            ->assertJsonPath('data.1.id', $oldMessage->id);
    });

    it('non-participant cannot view messages', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($otherUser)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertStatus(403);
    });
});

describe('Send Message', function () {
    it('participant can send message', function () {
        $response = $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/conversations/'.$this->conversation->id.'/messages', [
                'message' => 'Hello, looking forward to meeting!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'message', 'is_mine', 'created_at'],
            ])
            ->assertJsonPath('data.is_mine', true);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
            'message' => 'Hello, looking forward to meeting!',
        ]);
    });

    it('requires message content', function () {
        $response = $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/conversations/'.$this->conversation->id.'/messages', [
                'message' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('non-participant cannot send message', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($otherUser)
            ->postJson('/api/v1/conversations/'.$this->conversation->id.'/messages', [
                'message' => 'Hello!',
            ]);

        $response->assertStatus(403);
    });

    it('updates conversation timestamp when sending message', function () {
        $originalUpdatedAt = $this->conversation->updated_at;

        sleep(1);

        $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/conversations/'.$this->conversation->id.'/messages', [
                'message' => 'New message',
            ]);

        $this->conversation->refresh();
        expect($this->conversation->updated_at->gt($originalUpdatedAt))->toBeTrue();
    });
});
