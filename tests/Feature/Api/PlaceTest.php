<?php

use App\Models\ActivityType;
use App\Models\City;
use App\Models\Place;
use App\Models\PlaceDiscount;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->user = User::factory()->create(['city_id' => $this->city->id]);
});

describe('GET /places', function () {
    it('returns places in the user city', function () {
        $place = Place::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $place->id);
    });

    it('returns discount data for places with active discounts', function () {
        $place = Place::factory()->create(['city_id' => $this->city->id]);
        PlaceDiscount::create([
            'place_id' => $place->id,
            'discount_percent' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places');

        $response->assertOk()
            ->assertJsonPath('data.0.discount.percent', 15);
    });

    it('returns null discount for places without discounts', function () {
        Place::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places');

        $response->assertOk()
            ->assertJsonPath('data.0.discount', null);
    });

    it('excludes inactive discounts', function () {
        $place = Place::factory()->create(['city_id' => $this->city->id]);
        PlaceDiscount::create([
            'place_id' => $place->id,
            'discount_percent' => 10,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places');

        $response->assertOk()
            ->assertJsonPath('data.0.discount', null);
    });

    it('excludes expired discounts', function () {
        $place = Place::factory()->create(['city_id' => $this->city->id]);
        PlaceDiscount::create([
            'place_id' => $place->id,
            'discount_percent' => 20,
            'is_active' => true,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places');

        $response->assertOk()
            ->assertJsonPath('data.0.discount', null);
    });

    it('sorts places with discounts first', function () {
        $placeWithout = Place::factory()->create(['city_id' => $this->city->id]);
        $placeWith = Place::factory()->create(['city_id' => $this->city->id]);
        PlaceDiscount::create([
            'place_id' => $placeWith->id,
            'discount_percent' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $placeWith->id)
            ->assertJsonPath('data.1.id', $placeWithout->id);
    });

    it('filters by activity type', function () {
        $activityType = ActivityType::factory()->create();
        $place = Place::factory()->create(['city_id' => $this->city->id]);
        $place->activityTypes()->attach($activityType->id);

        $otherPlace = Place::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/places?activity_type_id=' . $activityType->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $place->id);
    });
});
