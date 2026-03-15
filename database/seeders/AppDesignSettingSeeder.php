<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppDesignSetting;
use Illuminate\Database\Seeder;

class AppDesignSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (AppDesignSetting::exists()) {
            return;
        }

        AppDesignSetting::create(array_merge(
            AppDesignSetting::defaults(),
            ['is_active' => true],
        ));
    }
}
