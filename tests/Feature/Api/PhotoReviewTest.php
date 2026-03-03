<?php

use App\Enums\PhotoStatus;
use App\Models\City;
use App\Models\User;
use App\Models\UserPhoto;
use App\Notifications\PhotoReviewedNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->city = City::factory()->create();
    $this->admin = User::factory()->create(['city_id' => $this->city->id]);
    $this->user = User::factory()->create(['city_id' => $this->city->id]);
});

describe('Photo Review', function () {
    it('can approve a pending photo', function () {
        Notification::fake();

        $photo = UserPhoto::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/photos/{$photo->id}/review", [
                'status' => 'approved',
            ]);

        $response->assertOk();

        $photo->refresh();
        expect($photo->status)->toBe(PhotoStatus::Approved);
        expect($photo->is_approved)->toBeTrue();
        expect($photo->rejection_reason)->toBeNull();

        Notification::assertSentTo($this->user, PhotoReviewedNotification::class);
    });

    it('can reject a pending photo with a reason', function () {
        Notification::fake();

        $photo = UserPhoto::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/photos/{$photo->id}/review", [
                'status' => 'rejected',
                'rejection_reason' => 'Photo does not meet guidelines',
            ]);

        $response->assertOk();

        $photo->refresh();
        expect($photo->status)->toBe(PhotoStatus::Rejected);
        expect($photo->is_approved)->toBeFalse();
        expect($photo->rejection_reason)->toBe('Photo does not meet guidelines');

        Notification::assertSentTo($this->user, PhotoReviewedNotification::class);
    });

    it('requires rejection_reason when rejecting', function () {
        $photo = UserPhoto::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/photos/{$photo->id}/review", [
                'status' => 'rejected',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('rejection_reason');
    });

    it('does not allow invalid status', function () {
        $photo = UserPhoto::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/photos/{$photo->id}/review", [
                'status' => 'pending',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('status');
    });

    it('clears rejection_reason when approving', function () {
        Notification::fake();

        $photo = UserPhoto::factory()->rejected('Old reason')->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/photos/{$photo->id}/review", [
                'status' => 'approved',
            ]);

        $response->assertOk();

        $photo->refresh();
        expect($photo->status)->toBe(PhotoStatus::Approved);
        expect($photo->rejection_reason)->toBeNull();
    });

    it('returns status in user photo index', function () {
        UserPhoto::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);
        UserPhoto::factory()->create([
            'user_id' => $this->user->id,
        ]);
        UserPhoto::factory()->rejected('Bad photo')->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/user/photos');

        $response->assertOk();

        $photos = $response->json('data');
        expect($photos)->toHaveCount(3);

        $statuses = collect($photos)->pluck('status')->sort()->values()->toArray();
        expect($statuses)->toBe(['approved', 'pending', 'rejected']);

        // Verify rejection_reason is included for rejected photos
        $rejected = collect($photos)->firstWhere('status', 'rejected');
        expect($rejected['rejection_reason'])->toBe('Bad photo');

        // Verify rejection_reason is null for non-rejected photos
        $approved = collect($photos)->firstWhere('status', 'approved');
        expect($approved['rejection_reason'])->toBeNull();
    });

    it('does not send notification when status stays pending', function () {
        Notification::fake();

        $photo = UserPhoto::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        // Update something other than status
        $photo->update(['photo_url' => 'user-photos/new.jpg']);

        Notification::assertNothingSent();
    });
});
