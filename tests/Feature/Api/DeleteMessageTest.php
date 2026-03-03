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

describe('Delete Message', function () {
    it('author can delete own message for everyone', function () {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
            'message' => 'Hello!',
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->deleteJson('/api/v1/conversations/'.$this->conversation->id.'/messages/'.$message->id, [
                'for_everyone' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Message deleted.');

        $message->refresh();
        expect($message->deleted_for_everyone)->toBeTrue();
        expect($message->deleted_at)->not->toBeNull();
    });

    it('participant can delete any message for self only', function () {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
            'message' => 'Hello!',
        ]);

        $response = $this->actingAs($this->joiner)
            ->deleteJson('/api/v1/conversations/'.$this->conversation->id.'/messages/'.$message->id);

        $response->assertOk();

        // Message should still exist and not be deleted for everyone
        $message->refresh();
        expect($message->deleted_for_everyone)->toBeFalse();
        expect($message->deleted_at)->toBeNull();

        // But should be in the pivot table for the joiner
        $this->assertDatabaseHas('message_deletions', [
            'message_id' => $message->id,
            'user_id' => $this->joiner->id,
        ]);
    });

    it('non-participant gets 403', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);

        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson('/api/v1/conversations/'.$this->conversation->id.'/messages/'.$message->id);

        $response->assertStatus(403);
    });

    it('cannot delete-for-everyone if not the author', function () {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
            'message' => 'Owner message',
        ]);

        $response = $this->actingAs($this->joiner)
            ->deleteJson('/api/v1/conversations/'.$this->conversation->id.'/messages/'.$message->id, [
                'for_everyone' => true,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You can only delete your own messages for everyone.');

        $message->refresh();
        expect($message->deleted_for_everyone)->toBeFalse();
    });

    it('deleted-for-everyone messages do not appear in index for anyone', function () {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
            'message' => 'Will be deleted',
        ]);

        // Delete for everyone
        $message->update([
            'deleted_at' => now(),
            'deleted_for_everyone' => true,
        ]);

        // Owner should not see it
        $response = $this->actingAs($this->hangoutOwner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertOk()
            ->assertJsonCount(0, 'data');

        // Joiner should not see it either
        $response = $this->actingAs($this->joiner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('deleted-for-me messages do not appear for that user but do for others', function () {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->hangoutOwner->id,
            'message' => 'Hidden for joiner only',
        ]);

        // Joiner deletes for self
        $message->deletedByUsers()->attach($this->joiner->id);

        // Joiner should not see it
        $response = $this->actingAs($this->joiner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertOk()
            ->assertJsonCount(0, 'data');

        // Owner should still see it
        $response = $this->actingAs($this->hangoutOwner)
            ->getJson('/api/v1/conversations/'.$this->conversation->id.'/messages');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $message->id);
    });

    it('returns 404 when message does not belong to conversation', function () {
        $otherConversation = Conversation::factory()->create([
            'hangout_request_id' => $this->hangout->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $otherConversation->id,
            'user_id' => $this->hangoutOwner->id,
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->deleteJson('/api/v1/conversations/'.$this->conversation->id.'/messages/'.$message->id);

        $response->assertStatus(404);
    });
});
