<?php

use App\Enums\HangoutRequestStatus;
use App\Models\ActivityType;
use App\Models\City;
use App\Models\HangoutRequest;
use App\Models\Place;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->activityType = ActivityType::factory()->create();
    $this->user = User::factory()->create(['city_id' => $this->city->id]);
});

describe('Browse Hangout Requests', function () {
    it('lists open hangout requests in specified city', function () {
        $hangout = HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'status' => HangoutRequestStatus::Open,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        // Create hangout in different city
        $otherCity = City::factory()->create();
        HangoutRequest::factory()->create([
            'city_id' => $otherCity->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->getJson('/api/v1/hangout-requests?city_id='.$this->city->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $hangout->id);
    });

    it('allows unauthenticated access', function () {
        HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'status' => HangoutRequestStatus::Open,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/hangout-requests?city_id='.$this->city->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('requires city_id parameter', function () {
        $response = $this->getJson('/api/v1/hangout-requests');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city_id']);
    });

    it('excludes own hangout requests when authenticated', function () {
        HangoutRequest::factory()->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/hangout-requests?city_id='.$this->city->id);

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('filters by activity type', function () {
        $otherType = ActivityType::factory()->create();

        HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $otherType->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/hangout-requests?city_id='.$this->city->id.'&activity_type_id='.$this->activityType->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('filters by date', function () {
        $tomorrow = now()->addDay()->format('Y-m-d');
        $nextWeek = now()->addWeek()->format('Y-m-d');

        HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => $tomorrow,
        ]);

        HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'date' => $nextWeek,
        ]);

        $response = $this->getJson('/api/v1/hangout-requests?city_id='.$this->city->id.'&date='.$tomorrow);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('Create Hangout Request', function () {
    it('creates a hangout request', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/hangout-requests', [
                'activity_type_id' => $this->activityType->id,
                'date' => now()->addDays(1)->format('Y-m-d'),
                'time' => '18:00',
                'notes' => 'Looking for company',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'date', 'time', 'status', 'notes'],
            ]);

        $this->assertDatabaseHas('hangout_requests', [
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);
    });

    it('requires activity type', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/hangout-requests', [
                'date' => now()->addDays(1)->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['activity_type_id']);
    });

    it('requires future date', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/hangout-requests', [
                'activity_type_id' => $this->activityType->id,
                'date' => now()->subDay()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    });

    it('can include a place', function () {
        $place = Place::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/hangout-requests', [
                'activity_type_id' => $this->activityType->id,
                'date' => now()->addDays(1)->format('Y-m-d'),
                'place_id' => $place->id,
            ]);

        $response->assertStatus(201);

        // Verify place was saved
        $this->assertDatabaseHas('hangout_requests', [
            'place_id' => $place->id,
        ]);
    });
});

describe('Update Hangout Request', function () {
    it('owner can update their open hangout request', function () {
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/hangout-requests/'.$hangout->id, [
                'notes' => 'Updated notes',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('hangout_requests', [
            'id' => $hangout->id,
            'notes' => 'Updated notes',
        ]);
    });

    it('cannot update matched hangout request', function () {
        $hangout = HangoutRequest::factory()->matched()->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/hangout-requests/'.$hangout->id, [
                'notes' => 'Updated notes',
            ]);

        $response->assertStatus(403);
    });

    it('cannot update other users hangout request', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/hangout-requests/'.$hangout->id, [
                'notes' => 'Updated notes',
            ]);

        $response->assertStatus(403);
    });
});

describe('Delete Hangout Request', function () {
    it('owner can cancel their hangout request', function () {
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/hangout-requests/'.$hangout->id);

        $response->assertOk();

        $this->assertDatabaseHas('hangout_requests', [
            'id' => $hangout->id,
            'status' => HangoutRequestStatus::Cancelled->value,
        ]);
    });

    it('cannot cancel other users hangout request', function () {
        $otherUser = User::factory()->create(['city_id' => $this->city->id]);
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $otherUser->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/hangout-requests/'.$hangout->id);

        $response->assertStatus(403);
    });
});

describe('My Hangout Requests', function () {
    it('lists user own hangout requests', function () {
        HangoutRequest::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        // Other user's hangout
        HangoutRequest::factory()->create([
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/user/hangout-requests');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });
});
