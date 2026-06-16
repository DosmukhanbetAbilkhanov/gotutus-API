<?php

use App\Enums\JoinRequestStatus;
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

    $this->owner = User::factory()->create(['city_id' => $this->city->id]);
    $this->joinerA = User::factory()->create(['city_id' => $this->city->id]);
    $this->joinerB = User::factory()->create(['city_id' => $this->city->id]);

    // Unlimited participants so approvals don't auto-close the hangout.
    $this->hangout = HangoutRequest::factory()->matched()->create([
        'user_id' => $this->owner->id,
        'city_id' => $this->city->id,
        'activity_type_id' => $this->activityType->id,
        'max_participants' => null,
    ]);
});

/** Approve (as owner) then confirm (as joiner) a pending join request. */
function approveAndConfirm($test, JoinRequest $jr, User $joiner): void
{
    $test->actingAs($test->owner)
        ->postJson('/api/v1/join-requests/'.$jr->id.'/approve')
        ->assertOk();

    $test->actingAs($joiner)
        ->postJson('/api/v1/join-requests/'.$jr->id.'/confirm')
        ->assertOk();
}

describe('Group conversation lifecycle', function () {
    it('does not create a group room when only one participant has confirmed', function () {
        $jrA = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerA->id,
        ]);

        approveAndConfirm($this, $jrA, $this->joinerA);

        $group = Conversation::where('hangout_request_id', $this->hangout->id)
            ->whereNull('join_request_id')
            ->first();

        expect($group)->toBeNull();
    });

    it('creates a group room with all confirmed members once a 2nd participant confirms', function () {
        $jrA = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerA->id,
        ]);
        $jrB = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerB->id,
        ]);

        approveAndConfirm($this, $jrA, $this->joinerA);
        approveAndConfirm($this, $jrB, $this->joinerB);

        $group = Conversation::where('hangout_request_id', $this->hangout->id)
            ->whereNull('join_request_id')
            ->first();

        expect($group)->not->toBeNull();

        $memberIds = $group->participants()->pluck('users.id')->all();
        expect($memberIds)->toHaveCount(3)
            ->and($memberIds)->toContain($this->owner->id)
            ->and($memberIds)->toContain($this->joinerA->id)
            ->and($memberIds)->toContain($this->joinerB->id);
    });

    it('exposes group_conversation_id on the hangout detail only to confirmed participants', function () {
        $jrA = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerA->id,
        ]);
        $jrB = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerB->id,
        ]);

        approveAndConfirm($this, $jrA, $this->joinerA);
        approveAndConfirm($this, $jrB, $this->joinerB);

        $group = Conversation::where('hangout_request_id', $this->hangout->id)
            ->whereNull('join_request_id')
            ->first();

        // Member sees the id
        $this->actingAs($this->joinerA)
            ->getJson('/api/v1/hangout-requests/'.$this->hangout->id)
            ->assertOk()
            ->assertJsonPath('data.group_conversation_id', $group->id);

        // Non-participant does not
        $stranger = User::factory()->create(['city_id' => $this->city->id]);
        $this->actingAs($stranger)
            ->getJson('/api/v1/hangout-requests/'.$this->hangout->id)
            ->assertOk()
            ->assertJsonPath('data.group_conversation_id', null);
    });
});

describe('Group conversation access', function () {
    beforeEach(function () {
        $jrA = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerA->id,
        ]);
        $jrB = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerB->id,
        ]);

        approveAndConfirm($this, $jrA, $this->joinerA);
        approveAndConfirm($this, $jrB, $this->joinerB);

        $this->group = Conversation::where('hangout_request_id', $this->hangout->id)
            ->whereNull('join_request_id')
            ->firstOrFail();
    });

    it('marks the conversation as a group and lists its participants', function () {
        $this->actingAs($this->owner)
            ->getJson('/api/v1/conversations/'.$this->group->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'group')
            ->assertJsonPath('data.participant_count', 3)
            ->assertJsonCount(3, 'data.participants');
    });

    it('lists the group room for every member', function () {
        foreach ([$this->owner, $this->joinerA, $this->joinerB] as $member) {
            $this->actingAs($member)
                ->getJson('/api/v1/conversations')
                ->assertOk()
                ->assertJsonFragment(['id' => $this->group->id]);
        }
    });

    it('lets any member send a message to the group', function () {
        $this->actingAs($this->joinerB)
            ->postJson('/api/v1/conversations/'.$this->group->id.'/messages', [
                'message' => 'See you all at 7!',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.is_mine', true);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->group->id,
            'user_id' => $this->joinerB->id,
            'message' => 'See you all at 7!',
        ]);
    });

    it('forbids a non-member from viewing or messaging the group', function () {
        $stranger = User::factory()->create(['city_id' => $this->city->id]);

        $this->actingAs($stranger)
            ->getJson('/api/v1/conversations/'.$this->group->id)
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->postJson('/api/v1/conversations/'.$this->group->id.'/messages', [
                'message' => 'let me in',
            ])
            ->assertStatus(403);
    });
});

describe('Group membership sync', function () {
    it('removes a participant from the room when they leave but keeps their messages', function () {
        $jrA = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerA->id,
        ]);
        $jrB = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joinerB->id,
        ]);

        approveAndConfirm($this, $jrA, $this->joinerA);
        approveAndConfirm($this, $jrB, $this->joinerB);

        $group = Conversation::where('hangout_request_id', $this->hangout->id)
            ->whereNull('join_request_id')
            ->firstOrFail();

        // Joiner A posted a message before leaving.
        $message = Message::factory()->create([
            'conversation_id' => $group->id,
            'user_id' => $this->joinerA->id,
        ]);

        // Joiner A leaves (status change → re-sync membership).
        $jrA->update(['status' => JoinRequestStatus::Cancelled]);
        $this->hangout->refresh()->syncGroupConversation();

        $memberIds = $group->fresh()->participants()->pluck('users.id')->all();
        expect($memberIds)->not->toContain($this->joinerA->id)
            ->and($memberIds)->toContain($this->owner->id)
            ->and($memberIds)->toContain($this->joinerB->id);

        // History preserved.
        $this->assertDatabaseHas('messages', ['id' => $message->id]);
    });
});
