<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master seeder for server deployment.
 *
 * Seeds everything needed for production testing:
 * 1. User types, cities, activity types, interests (ProductionSeeder)
 * 2. Admin user
 * 3. Test users for Almaty and Astana
 * 4. Places with working hours
 *
 * Run: php artisan db:seed --class=ServerSeeder
 * Or via route: GET /api/dev/seed
 */
class ServerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('╔═══════════════════════════════════════════════════════════╗');
        $this->command->info('║         TANYS.APP SERVER SEEDER                          ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        // 1. Base data (user types, cities, activity types, interests)
        $this->command->info('📦 Step 1: Seeding base data...');
        $this->call(ProductionSeeder::class);
        $this->command->newLine();

        // 2. Admin user
        $this->command->info('👤 Step 2: Creating admin user...');
        $this->call(AdminUserSeeder::class);
        $this->command->newLine();

        // 3. Test users
        $this->command->info('👥 Step 3: Creating test users...');
        $this->call(TestUsersSeeder::class);
        $this->command->newLine();

        // 4. Places
        $this->command->info('🏢 Step 4: Creating places...');
        $this->call(PlacesSeeder::class);
        $this->command->newLine();

        $this->command->info('╔═══════════════════════════════════════════════════════════╗');
        $this->command->info('║         ✓ SERVER SEEDING COMPLETE!                       ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $this->showCredentials();
    }

    private function showCredentials(): void
    {
        $this->command->info('🔑 Admin Credentials:');
        $this->command->info('   Email: dosmukhanbet@gmail.com');
        $this->command->info('   Phone: +77078835953');
        $this->command->info('   Password: password');
        $this->command->warn('   ⚠️  CHANGE PASSWORD IMMEDIATELY!');
        $this->command->newLine();

        $this->command->info('👥 Test Users:');
        $this->command->info('   Almaty: test.user.almaty.01@tanys.app (through test.user.almaty.10@tanys.app)');
        $this->command->info('   Astana: test.user.astana.01@tanys.app (through test.user.astana.10@tanys.app)');
        $this->command->info('   Password: password (for all test users)');
        $this->command->newLine();

        $this->command->info('🏢 Cities seeded: Almaty, Astana, Aktobe, Aktau');
        $this->command->info('🎯 Activity types: 22 types seeded');
        $this->command->info('❤️  Interests: 25 interests seeded');
        $this->command->info('🏪 Places: Sample places created for Almaty and Astana');
        $this->command->newLine();
    }
}
