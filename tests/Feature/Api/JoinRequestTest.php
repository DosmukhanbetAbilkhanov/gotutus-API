<?php

use App\Enums\HangoutRequestStatus;
use App\Enums\JoinRequestStatus;
use App\Models\ActivityType;
use App\Models\City;
use App\Models\HangoutRequest;
use App\Models\JoinRequest;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->activityType = ActivityType::factory()->create();
    $this->hangoutOwner = User::factory()->create(['city_id' => $this->city->id]);
    $this->joiner = User::factory()->create(['city_id' => $this->city->id]);
    $this->hangout = HangoutRequest::factory()->create([
        'user_id' => $this->hangoutOwner->id,
        'city_id' => $this->city->id,
        'activity_type_id' => $this->activityType->id,
        'date' => now()->addDays(1)->format('Y-m-d'),
    ]);
});

describe('Send Join Request', function () {
    it('can send a join request', function () {
        $response = $this->actingAs($this->joiner)
            ->postJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join', [
                'message' => 'I would love to join!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'status', 'message'],
            ]);

        $this->assertDatabaseHas('join_requests', [
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
            'status' => JoinRequestStatus::Pending->value,
        ]);
    });

    it('cannot join own hangout', function () {
        $response = $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join');

        $response->assertStatus(403);
    });

    it('cannot join twice', function () {
        JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->postJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join');

        $response->assertStatus(403);
    });

    it('cannot join hangout in different city', function () {
        $otherCity = City::factory()->create();
        $userInOtherCity = User::factory()->create(['city_id' => $otherCity->id]);

        $response = $this->actingAs($userInOtherCity)
            ->postJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join');

        $response->assertStatus(403);
    });

    it('cannot join matched hangout', function () {
        $this->hangout->update(['status' => HangoutRequestStatus::Matched]);

        $response = $this->actingAs($this->joiner)
            ->postJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join');

        $response->assertStatus(403);
    });
});

describe('View Join Requests', function () {
    it('owner can view join requests', function () {
        JoinRequest::factory()->count(3)->create([
            'hangout_request_id' => $this->hangout->id,
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->getJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join-requests');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('non-owner cannot view join requests', function () {
        $response = $this->actingAs($this->joiner)
            ->getJson('/api/v1/hangout-requests/'.$this->hangout->id.'/join-requests');

        $response->assertStatus(403);
    });
});

describe('Approve Join Request', function () {
    it('owner can approve join request', function () {
        $joinRequest = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/join-requests/'.$joinRequest->id.'/approve');

        $response->assertOk();

        $this->assertDatabaseHas('join_requests', [
            'id' => $joinRequest->id,
            'status' => JoinRequestStatus::Approved->value,
        ]);
    });

    it('non-owner cannot approve join request', function () {
        $joinRequest = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->postJson('/api/v1/join-requests/'.$joinRequest->id.'/approve');

        $response->assertStatus(403);
    });
});

describe('Decline Join Request', function () {
    it('owner can decline join request', function () {
        $joinRequest = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/join-requests/'.$joinRequest->id.'/decline');

        $response->assertOk();

        $this->assertDatabaseHas('join_requests', [
            'id' => $joinRequest->id,
            'status' => JoinRequestStatus::Declined->value,
        ]);
    });
});

describe('Confirm Participation', function () {
    it('joiner can confirm after approval', function () {
        $joinRequest = JoinRequest::factory()->approved()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->postJson('/api/v1/join-requests/'.$joinRequest->id.'/confirm');

        $response->assertOk();

        $this->assertDatabaseHas('join_requests', [
            'id' => $joinRequest->id,
            'status' => JoinRequestStatus::Confirmed->value,
        ]);

        $this->assertDatabaseHas('hangout_requests', [
            'id' => $this->hangout->id,
            'status' => HangoutRequestStatus::Matched->value,
        ]);

        // Conversation should be created
        $this->assertDatabaseHas('conversations', [
            'hangout_request_id' => $this->hangout->id,
        ]);
    });

    it('cannot confirm pending join request', function () {
        $joinRequest = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->postJson('/api/v1/join-requests/'.$joinRequest->id.'/confirm');

        $response->assertStatus(403);
    });

    it('owner cannot confirm join request', function () {
        $joinRequest = JoinRequest::factory()->approved()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->hangoutOwner)
            ->postJson('/api/v1/join-requests/'.$joinRequest->id.'/confirm');

        $response->assertStatus(403);
    });
});

describe('Cancel Join Request', function () {
    it('joiner can cancel pending join request', function () {
        $joinRequest = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->deleteJson('/api/v1/join-requests/'.$joinRequest->id);

        $response->assertOk();

        $this->assertDatabaseHas('join_requests', [
            'id' => $joinRequest->id,
            'status' => JoinRequestStatus::Cancelled->value,
        ]);
    });

    it('joiner can cancel approved join request', function () {
        $joinRequest = JoinRequest::factory()->approved()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $this->joiner->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->deleteJson('/api/v1/join-requests/'.$joinRequest->id);

        $response->assertOk();
    });

    it('cannot cancel other users join request', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);
        $joinRequest = JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->deleteJson('/api/v1/join-requests/'.$joinRequest->id);

        $response->assertStatus(403);
    });
});

describe('My Join Requests', function () {
    it('lists user sent join requests', function () {
        JoinRequest::factory()->count(3)->create([
            'user_id' => $this->joiner->id,
        ]);

        // Other user's join request
        JoinRequest::factory()->create([
            'hangout_request_id' => $this->hangout->id,
        ]);

        $response = $this->actingAs($this->joiner)
            ->getJson('/api/v1/user/join-requests');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });
});
