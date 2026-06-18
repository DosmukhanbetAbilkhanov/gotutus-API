<?php

use App\Models\City;
use App\Models\User;
use App\Services\MobizonSmsService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->city = City::factory()->create();

    // SMS gateway isn't configured in tests — fake it so verification-code
    // sends succeed (send() now returns false when unconfigured).
    $this->mock(MobizonSmsService::class, function ($mock) {
        $mock->shouldReceive('send')->andReturn(true);
        $mock->shouldReceive('sendVerificationCode')->andReturn(true);
        $mock->shouldReceive('getBalance')->andReturn(1000.0);
    });
});

describe('Registration', function () {
    describe('Step 1: Send Code', function () {
        it('sends a verification code for a new phone', function () {
            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => '+77001234567',
            ]);

            $response->assertOk()
                ->assertJsonStructure(['message']);

            expect(Cache::has('registration_code:+77001234567'))->toBeTrue();
        });

        it('returns 503 and clears the code when SMS fails to send', function () {
            // Override the default (true) mock for this test.
            $this->mock(MobizonSmsService::class, function ($mock) {
                $mock->shouldReceive('send')->andReturn(false);
            });

            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => '+77001234599',
            ]);

            $response->assertStatus(503);
            expect(Cache::has('registration_code:+77001234599'))->toBeFalse();
        });

        it('rejects an already verified phone', function () {
            User::factory()->create([
                'city_id' => $this->city->id,
                'phone' => '+77001234567',
            ]);

            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => '+77001234567',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['phone']);
        });

        it('rejects an unverified phone that already exists', function () {
            User::factory()->unverified()->create([
                'city_id' => $this->city->id,
                'phone' => '+77001234567',
            ]);

            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => '+77001234567',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['phone']);
        });

        it('requires a valid phone format', function () {
            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => 'invalid-phone',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['phone']);
        });
    });

    describe('Step 2: Verify Code', function () {
        it('returns a verification token on correct code', function () {
            Cache::put('registration_code:+77001234567', '123456', now()->addMinutes(10));

            $response = $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '123456',
            ]);

            $response->assertOk()
                ->assertJsonStructure([
                    'message',
                    'data' => ['verification_token'],
                ]);

            $token = $response->json('data.verification_token');
            expect(Cache::has("registration_token:{$token}"))->toBeTrue();
        });

        it('rejects a wrong code', function () {
            Cache::put('registration_code:+77001234567', '123456', now()->addMinutes(10));

            $response = $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '999999',
            ]);

            $response->assertStatus(422);
        });

        it('rejects when no code was sent', function () {
            $response = $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '123456',
            ]);

            $response->assertStatus(422);
        });

        it('creates a registration token after successful verify', function () {
            Cache::put('registration_code:+77001234567', '123456', now()->addMinutes(10));

            $response = $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '123456',
            ])->assertOk();

            $token = $response->json('data.verification_token');
            expect(Cache::has("registration_token:{$token}"))->toBeTrue();
        });
    });

    describe('Step 3: Complete Registration', function () {
        beforeEach(function () {
            $this->verificationToken = fake()->uuid();
            Cache::put("registration_token:{$this->verificationToken}", '+77001234567', now()->addMinutes(30));
        });

        it('creates a verified user', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'phone_verified'],
                        'token',
                        'access_token',
                        'refresh_token',
                        'expires_in',
                        'token_type',
                    ],
                ]);

            $this->assertDatabaseHas('users', [
                'phone' => '+77001234567',
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
            ]);

            expect(User::where('phone', '+77001234567')->first()->isPhoneVerified())->toBeTrue();
        });

        it('accepts other gender', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'Alex',
                'email' => 'alex@example.com',
                'age' => 22,
                'gender' => 'other',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201);

            $this->assertDatabaseHas('users', [
                'phone' => '+77001234567',
                'gender' => 'other',
            ]);
        });

        it('accepts optional bio field', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201);

            $this->assertDatabaseHas('users', [
                'phone' => '+77001234567',
                'email' => 'john@example.com',
            ]);
        });

        it('rejects an invalid verification token', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => fake()->uuid(),
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422);
        });

        it('consumes the verification token after use', function () {
            $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertStatus(201);

            expect(Cache::has("registration_token:{$this->verificationToken}"))->toBeFalse();
        });

        it('requires coordinates', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['latitude', 'longitude']);
        });

        it('rejects coordinates outside any supported city', function () {
            // Far from the seeded Astana city → no supported city.
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 48.8566, // Paris
                'longitude' => 2.3522,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422);
            expect(User::where('email', 'john@example.com')->exists())->toBeFalse();
        });

        it('assigns the city resolved from coordinates', function () {
            $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'Geo User',
                'email' => 'geo@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertStatus(201);

            $this->assertDatabaseHas('users', [
                'email' => 'geo@example.com',
                'city_id' => $this->city->id,
            ]);
        });

        it('requires password confirmation', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'different',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        it('requires email field', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'public_offer_accepted' => true,
                'public_offer_version' => '1.0',
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
                'latitude' => 51.1605,
                'longitude' => 71.4704,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
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
                    'user' => ['id', 'name', 'phone_verified'],
                    'token',
                    'access_token',
                    'refresh_token',
                    'expires_in',
                    'token_type',
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
            ->assertJson(['message' => 'These credentials do not match our records.']);
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
            ->assertJson(['message' => __('auth.phone_not_verified')]);
    });

    it('allows verified users to access protected routes', function () {
        $user = User::factory()->create(['city_id' => $this->city->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user');

        $response->assertOk();
    });
});
