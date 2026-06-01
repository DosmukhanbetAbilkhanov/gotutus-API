<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates admin user for tanys.app production.
 *
 * Admin credentials:
 * - Email: dosmukhanbet@gmail.com
 * - Phone: +77078835953
 * - Password: password (CHANGE THIS!)
 *
 * Run: php artisan db:seed --class=AdminUserSeeder
 * Or via route: GET /api/dev/seed
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating admin user...');

        $now = now();

        // Ensure admin user type exists
        $adminTypeId = DB::table('user_types')->where('slug', 'admin')->value('id');

        if (!$adminTypeId) {
            $this->command->error('Admin user type not found! Run ProductionSeeder first.');
            return;
        }

        // Get Almaty city (default for admin)
        $almatyId = DB::table('city_translations')
            ->where('language_code', 'en')
            ->where('name', 'Almaty')
            ->value('city_id');

        if (!$almatyId) {
            $this->command->error('Almaty city not found! Run ProductionSeeder first.');
            return;
        }

        $adminEmail = 'dosmukhanbet@gmail.com';
        $adminPhone = '+77078835953';

        // Check if admin already exists
        if (DB::table('users')->where('email', $adminEmail)->exists()) {
            $this->command->warn("Admin user with email {$adminEmail} already exists.");
            return;
        }

        if (DB::table('users')->where('phone', $adminPhone)->exists()) {
            $this->command->warn("User with phone {$adminPhone} already exists.");
            return;
        }

        // Create admin user
        $userId = DB::table('users')->insertGetId([
            'name' => 'Dos Mukhanbet',
            'email' => $adminEmail,
            'phone' => $adminPhone,
            'age' => 30,
            'gender' => Gender::Male->value,
            'bio' => 'Администратор платформы Tanys',
            'password' => Hash::make('password'), // CHANGE THIS AFTER FIRST LOGIN!
            'city_id' => $almatyId,
            'status' => UserStatus::Active->value,
            'user_type_id' => $adminTypeId,
            'phone_verified_at' => $now,
            'public_offer_accepted_at' => $now,
            'public_offer_version' => 1,
            'trust_score' => 5.00,
            'ratings_count' => 0,
            'average_rating' => 0.00,
            'attendance_rate' => 100.00,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info("✓ Admin user created successfully!");
        $this->command->info("  Email: {$adminEmail}");
        $this->command->info("  Phone: {$adminPhone}");
        $this->command->info("  Password: password");
        $this->command->warn("  ⚠️  CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN!");
    }
}
