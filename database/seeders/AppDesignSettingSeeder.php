<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppDesignSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class AppDesignSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = AppDesignSetting::defaults();

        // Authoritative: refresh the active settings to the current defaults
        // (Fresh Meet) so re-running applies the latest design on any environment.
        $setting = AppDesignSetting::where('is_active', true)->first();

        if ($setting) {
            $setting->update($defaults);
        } else {
            AppDesignSetting::create(array_merge($defaults, ['is_active' => true]));
        }

        // Bust the cached design payload (AppDesignSettingController caches
        // `app_design_settings:active` for 1h) so clients fetch the new version.
        Cache::forget('app_design_settings:active');
    }
}
