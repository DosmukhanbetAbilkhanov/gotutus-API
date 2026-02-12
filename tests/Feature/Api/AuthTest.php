<?php

use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->city = City::factory()->create();
});

describe('Registration', function () {
    describe('Step 1: Send Code', function () {
        it('sends a verification code for a new phone', function () {
            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => '+77001234567',
            ]);

            $response->assertOk()
                ->assertJsonStructure(['message']);

            expect(Cache::has('phone_verification:+77001234567'))->toBeTrue();
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

        it('allows sending code for an unverified phone', function () {
            User::factory()->unverified()->create([
                'city_id' => $this->city->id,
                'phone' => '+77001234567',
            ]);

            $response = $this->postJson('/api/v1/auth/register/send-code', [
                'phone' => '+77001234567',
            ]);

            $response->assertOk();
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
            Cache::put('phone_verification:+77001234567', '123456', now()->addMinutes(10));

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
            Cache::put('phone_verification:+77001234567', '123456', now()->addMinutes(10));

            $response = $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '999999',
            ]);

            $response->assertStatus(422)
                ->assertJson(['error_code' => 'INVALID_CODE']);
        });

        it('rejects when no code was sent', function () {
            $response = $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '123456',
            ]);

            $response->assertStatus(422)
                ->assertJson(['error_code' => 'INVALID_CODE']);
        });

        it('clears the verification code after successful verify', function () {
            Cache::put('phone_verification:+77001234567', '123456', now()->addMinutes(10));

            $this->postJson('/api/v1/auth/register/verify-code', [
                'phone' => '+77001234567',
                'code' => '123456',
            ])->assertOk();

            expect(Cache::has('phone_verification:+77001234567'))->toBeFalse();
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
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
                'city_id' => $this->city->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'phone_verified'],
                        'token',
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
                'name' => 'Alex',
                'age' => 22,
                'gender' => 'other',
                'city_id' => $this->city->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201);

            $this->assertDatabaseHas('users', [
                'phone' => '+77001234567',
                'gender' => 'other',
            ]);
        });

        it('accepts optional email', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
                'email' => 'john@example.com',
                'city_id' => $this->city->id,
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
                'age' => 25,
                'gender' => 'male',
                'city_id' => $this->city->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJson(['error_code' => 'INVALID_TOKEN']);
        });

        it('consumes the verification token after use', function () {
            $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
                'city_id' => $this->city->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertStatus(201);

            expect(Cache::has("registration_token:{$this->verificationToken}"))->toBeFalse();
        });

        it('requires a valid city', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
                'city_id' => 999,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['city_id']);
        });

        it('requires password confirmation', function () {
            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'name' => 'John Doe',
                'age' => 25,
                'gender' => 'male',
                'city_id' => $this->city->id,
                'password' => 'password123',
                'password_confirmation' => 'different',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        it('handles abandoned registration by updating existing unverified user', function () {
            $existingUser = User::factory()->unverified()->create([
                'city_id' => $this->city->id,
                'phone' => '+77001234567',
                'name' => 'Old Name',
            ]);

            $oldToken = $existingUser->createToken('old-device')->plainTextToken;

            $response = $this->postJson('/api/v1/auth/register/complete', [
                'verification_token' => $this->verificationToken,
                'name' => 'New Name',
                'age' => 30,
                'gender' => 'female',
                'city_id' => $this->city->id,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            $response->assertStatus(201);

            $this->assertDatabaseCount('users', 1);
            $this->assertDatabaseHas('users', [
                'id' => $existingUser->id,
                'phone' => '+77001234567',
                'name' => 'New Name',
                'age' => 30,
                'gender' => 'female',
            ]);

            $this->assertDatabaseMissing('personal_access_tokens', [
                'tokenable_id' => $existingUser->id,
                'name' => 'old-device',
            ]);

            expect($existingUser->fresh()->isPhoneVerified())->toBeTrue();
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
