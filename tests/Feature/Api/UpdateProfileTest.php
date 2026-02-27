<?php

use App\Models\City;
use App\Models\User;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->user = User::factory()->create(['city_id' => $this->city->id]);
    $this->actingAs($this->user);
});

describe('Update Profile', function () {
    it('can update bio', function () {
        $response = $this->putJson('/api/v1/user', [
            'bio' => 'Hello, I love hiking and coffee.',
        ]);

        $response->assertOk();
        expect($this->user->fresh()->bio)->toBe('Hello, I love hiking and coffee.');
    });

    it('can clear bio by sending empty string', function () {
        $this->user->update(['bio' => 'Old bio']);

        $response = $this->putJson('/api/v1/user', [
            'bio' => '',
        ]);

        $response->assertOk();
        expect($this->user->fresh()->bio)->toBe('');
    });

    it('can send null bio', function () {
        $this->user->update(['bio' => 'Old bio']);

        $response = $this->putJson('/api/v1/user', [
            'bio' => null,
        ]);

        $response->assertOk();
    });

    it('rejects bio longer than 500 characters', function () {
        $response = $this->putJson('/api/v1/user', [
            'bio' => str_repeat('a', 501),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bio']);
    });

    it('can update age within valid range', function () {
        $response = $this->putJson('/api/v1/user', [
            'age' => 25,
        ]);

        $response->assertOk();
        expect($this->user->fresh()->age)->toBe(25);
    });

    it('rejects age below 18', function () {
        $response = $this->putJson('/api/v1/user', [
            'age' => 17,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age']);
    });

    it('rejects age above 100', function () {
        $response = $this->putJson('/api/v1/user', [
            'age' => 101,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age']);
    });

    it('can update email with valid unique email', function () {
        $response = $this->putJson('/api/v1/user', [
            'email' => 'newemail@example.com',
        ]);

        $response->assertOk();
        expect($this->user->fresh()->email)->toBe('newemail@example.com');
    });

    it('rejects duplicate email', function () {
        User::factory()->create(['email' => 'taken@example.com', 'city_id' => $this->city->id]);

        $response = $this->putJson('/api/v1/user', [
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('rejects invalid email format', function () {
        $response = $this->putJson('/api/v1/user', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('ignores gender field in update request', function () {
        $originalGender = $this->user->gender;

        $response = $this->putJson('/api/v1/user', [
            'gender' => 'other',
        ]);

        $response->assertOk();
        expect($this->user->fresh()->gender)->toBe($originalGender);
    });

    it('ignores phone field in update request', function () {
        $originalPhone = $this->user->phone;

        $response = $this->putJson('/api/v1/user', [
            'phone' => '+77009999999',
        ]);

        $response->assertOk();
        expect($this->user->fresh()->phone)->toBe($originalPhone);
    });

    it('can update name', function () {
        $response = $this->putJson('/api/v1/user', [
            'name' => 'New Name',
        ]);

        $response->assertOk();
        expect($this->user->fresh()->name)->toBe('New Name');
    });

    it('can update multiple fields at once', function () {
        $newCity = City::factory()->create();

        $response = $this->putJson('/api/v1/user', [
            'name' => 'Updated Name',
            'bio' => 'My new bio',
            'age' => 30,
            'email' => 'multi@example.com',
            'city_id' => $newCity->id,
        ]);

        $response->assertOk();

        $user = $this->user->fresh();
        expect($user->name)->toBe('Updated Name')
            ->and($user->bio)->toBe('My new bio')
            ->and($user->age)->toBe(30)
            ->and($user->email)->toBe('multi@example.com')
            ->and($user->city_id)->toBe($newCity->id);
    });

    it('returns bio in the response', function () {
        $this->user->update(['bio' => 'Test bio']);

        $response = $this->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJsonPath('data.bio', 'Test bio');
    });
});
