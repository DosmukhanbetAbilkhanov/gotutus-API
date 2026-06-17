<?php

use App\Models\City;

describe('POST /cities/resolve', function () {
    it('returns the supported city for in-city coordinates', function () {
        $city = City::factory()->create([
            'center_latitude' => 51.1605,
            'center_longitude' => 71.4704,
            'radius_km' => 40,
        ]);

        $response = $this->postJson('/api/v1/cities/resolve', [
            'latitude' => 51.17,
            'longitude' => 71.45,
        ]);

        $response->assertOk()
            ->assertJsonPath('supported', true)
            ->assertJsonPath('city.id', $city->id);
    });

    it('returns supported:false outside every city', function () {
        City::factory()->create([
            'center_latitude' => 51.1605,
            'center_longitude' => 71.4704,
            'radius_km' => 40,
        ]);

        $response = $this->postJson('/api/v1/cities/resolve', [
            'latitude' => 48.8566, // Paris
            'longitude' => 2.3522,
        ]);

        $response->assertOk()->assertJsonPath('supported', false);
    });

    it('ignores inactive cities', function () {
        City::factory()->inactive()->create([
            'center_latitude' => 51.1605,
            'center_longitude' => 71.4704,
            'radius_km' => 40,
        ]);

        $response = $this->postJson('/api/v1/cities/resolve', [
            'latitude' => 51.1605,
            'longitude' => 71.4704,
        ]);

        $response->assertOk()->assertJsonPath('supported', false);
    });

    it('picks the nearest city when several are in range', function () {
        $astana = City::factory()->create([
            'center_latitude' => 51.1605,
            'center_longitude' => 71.4704,
            'radius_km' => 200,
        ]);
        City::factory()->create([
            'center_latitude' => 50.0,
            'center_longitude' => 71.4704,
            'radius_km' => 200,
        ]);

        $response = $this->postJson('/api/v1/cities/resolve', [
            'latitude' => 51.15,
            'longitude' => 71.47,
        ]);

        $response->assertOk()
            ->assertJsonPath('supported', true)
            ->assertJsonPath('city.id', $astana->id);
    });

    it('validates coordinates', function () {
        $this->postJson('/api/v1/cities/resolve', [
            'latitude' => 200,
            'longitude' => 'abc',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    });
});
