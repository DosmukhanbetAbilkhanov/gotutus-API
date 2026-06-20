<?php

use App\Models\ActivityType;
use App\Models\City;
use App\Models\HangoutRequest;
use App\Models\User;

describe('Association files', function () {
    it('serves assetlinks.json as JSON', function () {
        config(['deeplink.android.sha256_fingerprints' => ['AA:BB:CC']]);

        $this->get('/.well-known/assetlinks.json')
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('0.target.package_name', config('deeplink.android.package'))
            ->assertJsonPath('0.target.sha256_cert_fingerprints.0', 'AA:BB:CC');
    });

    it('serves apple-app-site-association as JSON', function () {
        config(['deeplink.ios.team_id' => 'ABCDE12345']);

        $this->get('/.well-known/apple-app-site-association')
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('applinks.details.0.appIDs.0', 'ABCDE12345.'.config('deeplink.ios.bundle_id'));
    });
});

describe('Hangout landing page', function () {
    beforeEach(function () {
        $this->city = City::factory()->create();
        $this->activityType = ActivityType::factory()->create();
        $this->owner = User::factory()->create(['city_id' => $this->city->id]);
    });

    it('renders a preview for an active hangout with OG tags', function () {
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->owner->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'status' => 'open',
        ]);

        $this->get('/h/'.$hangout->id)
            ->assertOk()
            ->assertSee('og:title', false)
            ->assertSee('Open in Tanys');
    });

    it('returns 404 for a missing hangout (graceful page)', function () {
        $this->get('/h/999999')
            ->assertStatus(404)
            ->assertSee('App Store');
    });

    it('shows unavailable for a cancelled hangout', function () {
        $hangout = HangoutRequest::factory()->create([
            'user_id' => $this->owner->id,
            'city_id' => $this->city->id,
            'activity_type_id' => $this->activityType->id,
            'status' => 'cancelled',
        ]);

        $this->get('/h/'.$hangout->id)->assertStatus(410);
    });
});
