<?php

use App\Models\AppDesignSetting;

describe('GET /app-design', function () {
    it('returns default design settings when none exist', function () {
        $response = $this->getJson('/api/v1/app-design');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'colors',
                    'typography',
                    'spacing',
                    'border_radius',
                    'version',
                ],
            ])
            ->assertJsonPath('data.colors.primary', '#6366F1');
    });

    it('returns active design settings', function () {
        $setting = AppDesignSetting::create(array_merge(
            AppDesignSetting::defaults(),
            [
                'is_active' => true,
                'colors' => array_merge(
                    AppDesignSetting::defaults()['colors'],
                    ['primary' => '#FF0000'],
                ),
            ],
        ));

        $response = $this->getJson('/api/v1/app-design');

        $response->assertOk()
            ->assertJsonPath('data.colors.primary', '#FF0000')
            ->assertJsonPath('data.version', $setting->fresh()->version);
    });

    it('returns 304 when If-None-Match matches version', function () {
        $setting = AppDesignSetting::create(array_merge(
            AppDesignSetting::defaults(),
            ['is_active' => true],
        ));

        $version = $setting->fresh()->version;

        $response = $this->getJson('/api/v1/app-design', [
            'If-None-Match' => $version,
        ]);

        $response->assertStatus(304);
    });

    it('returns 200 when If-None-Match does not match', function () {
        AppDesignSetting::create(array_merge(
            AppDesignSetting::defaults(),
            ['is_active' => true],
        ));

        $response = $this->getJson('/api/v1/app-design', [
            'If-None-Match' => 'wrong-version',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['colors', 'typography', 'spacing', 'border_radius', 'version'],
            ]);
    });

    it('includes ETag header in response', function () {
        $setting = AppDesignSetting::create(array_merge(
            AppDesignSetting::defaults(),
            ['is_active' => true],
        ));

        $response = $this->getJson('/api/v1/app-design');

        $response->assertOk()
            ->assertHeader('ETag', $setting->fresh()->version);
    });

    it('auto-computes version hash on save', function () {
        $setting = AppDesignSetting::create(array_merge(
            AppDesignSetting::defaults(),
            ['is_active' => true],
        ));

        expect($setting->fresh()->version)->not->toBeNull()
            ->and($setting->fresh()->version)->toHaveLength(32);
    });
});
