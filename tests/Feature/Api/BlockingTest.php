<?php

use App\Models\ActivityType;
use App\Models\BlockedUser;
use App\Models\City;
use App\Models\HangoutRequest;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->activityType = ActivityType::factory()->create();
    $this->user = User::factory()->create(['city_id' => $this->city->id]);
    $this->otherUser = User::factory()->create(['city_id' => $this->city->id]);
});

describe('Block User', function () {
    it('can block a user', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/blocked-users', [
                'blocked_user_id' => $this->otherUser->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'blocked_user', 'created_at'],
            ]);

        $this->assertDatabaseHas('blocked_users', [
            'user_id' => $this->user->id,
            'blocked_user_id' => $this->otherUser->id,
        ]);
    });

    it('cannot block yourself', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/blocked-users', [
                'blocked_user_id' => $this->user->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error_code' => 'CANNOT_BLOCK_SELF']);
    });

    it('cannot block same user twice', function () {
        BlockedUser::factory()->create([
            'user_id' => $this->user->id,
            'blocked_user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/blocked-users', [
                'blocked_user_id' => $this->otherUser->id,
            ]);

        $response->assertStatus(422)
            ->assertJson(['error_code' => 'ALREADY_BLOCKED']);
    });
});

describe('Unblock User', function () {
    it('can unblock a user', function () {
        $blockedUser = BlockedUser::factory()->create([
            'user_id' => $this->user->id,
            'blocked_user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/blocked-users/'.$blockedUser->id);

        $response->assertOk();

        $this->assertDatabaseMissing('blocked_users', [
            'id' => $blockedUser->id,
        ]);
    });

    it('cannot unblock other users blocked list', function () {
        $blockedUser = BlockedUser::factory()->create([
            'user_id' => $this->otherUser->id,
            'blocked_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/blocked-users/'.$blockedUser->id);

        $response->assertStatus(404);
    });
});

describe('List Blocked Users', function () {
    it('can list blocked users', function () {
        BlockedUser::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Other user's blocked list
        BlockedUser::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/blocked-users');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });
});

describe('Blocking Hides Hangouts', function () {
    it('blocked user hangouts are hidden', function () {
        // Other user creates a hangout
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->otherUser->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        // User blocks other user
        BlockedUser::factory()->create([
            'user_id' => $this->user->id,
            'blocked_user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/hangout-requests');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('hangouts are hidden from users who blocked you', function () {
        // User creates a hangout
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        // Other user blocks the hangout owner
        BlockedUser::factory()->create([
            'user_id' => $this->otherUser->id,
            'blocked_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->otherUser)
            ->getJson('/api/v1/hangout-requests');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('blocked user cannot join hangout', function () {
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        // User blocks other user
        BlockedUser::factory()->create([
            'user_id' => $this->user->id,
            'blocked_user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->otherUser)
            ->postJson('/api/v1/hangout-requests/'.$hangout->id.'/join');

        $response->assertStatus(403);
    });
});

describe('Report User', function () {
    it('can report a user', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/reports', [
                'reported_user_id' => $this->otherUser->id,
                'reason' => 'Inappropriate behavior',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $this->user->id,
            'reported_user_id' => $this->otherUser->id,
            'reason' => 'Inappropriate behavior',
        ]);
    });

    it('cannot report yourself', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/reports', [
                'reported_user_id' => $this->user->id,
                'reason' => 'Self report',
            ]);

        $response->assertStatus(422)
            ->assertJson(['error_code' => 'CANNOT_REPORT_SELF']);
    });

    it('can include related hangout in report', function () {
        $hangout = HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/reports', [
                'reported_user_id' => $this->otherUser->id,
                'reason' => 'Bad experience during hangout',
                'hangout_request_id' => $hangout->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $this->user->id,
            'hangout_request_id' => $hangout->id,
        ]);
    });
});
