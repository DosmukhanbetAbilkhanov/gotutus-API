<?php

use App\Models\City;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
});

describe('Registration', function () {
    it('can register a new user', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'phone' => '+77001234567',
            'age' => 25,
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city_id' => $this->city->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'phone_verified'],
                    'token',
                    'phone_verified',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '+77001234567',
            'name' => 'John Doe',
            'age' => 25,
            'gender' => 'male',
        ]);
    });

    it('requires valid phone format', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'phone' => 'invalid-phone',
            'age' => 25,
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city_id' => $this->city->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    });

    it('requires password confirmation', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'phone' => '+77001234567',
            'age' => 25,
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'city_id' => $this->city->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('requires a valid city', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'phone' => '+77001234567',
            'age' => 25,
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city_id']);
    });
});

describe('Login', function () {
    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'city_id' => $this->city->id,
            'phone' => '+77001234567',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '+77001234567',
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name'],
                    'token',
                    'phone_verified',
                ],
            ]);
    });

    it('fails with invalid credentials', function () {
        User::factory()->create([
            'city_id' => $this->city->id,
            'phone' => '+77001234567',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '+77001234567',
            'password' => 'wrong-password',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error_code' => 'INVALID_CREDENTIALS']);
    });
});

describe('Logout', function () {
    it('can logout authenticated user', function () {
        $user = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJsonStructure(['message']);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    });
});

describe('Phone Verification', function () {
    it('blocks unverified users from protected routes', function () {
        $user = User::factory()->unverified()->create([
            'city_id' => $this->city->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user');

        $response->assertStatus(403)
            ->assertJson(['error_code' => 'PHONE_NOT_VERIFIED']);
    });

    it('allows verified users to access protected routes', function () {
        $user = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user');

        $response->assertOk();
    });
});
