<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds ONLY the minimum essential data required for the app to function.
 *
 * This seeder ensures the app can:
 * - Authenticate users (user types)
 * - Create hangouts (cities, activity types)
 * - Complete user profiles (interests)
 *
 * Run this FIRST before any other seeders:
 * php artisan db:seed --class=MinimalDataSeeder
 */
class MinimalDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('╔═══════════════════════════════════════════════════════════╗');
        $this->command->info('║     SEEDING MINIMUM ESSENTIAL DATA                       ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $this->seedUserTypes();
        $this->seedCities();
        $this->seedActivityTypes();
        $this->seedInterests();

        $this->command->newLine();
        $this->command->info('╔═══════════════════════════════════════════════════════════╗');
        $this->command->info('║     ✓ MINIMUM DATA SEEDED - APP IS READY!               ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $this->showSummary();
    }

    private function seedUserTypes(): void
    {
        $this->command->info('👤 Seeding user types...');
        $now = now();

        $types = [
            ['slug' => 'client', 'name' => 'Client'],
            ['slug' => 'admin', 'name' => 'Admin'],
            ['slug' => 'city_manager', 'name' => 'City Manager'],
        ];

        $created = 0;
        foreach ($types as $type) {
            $exists = DB::table('user_types')->where('slug', $type['slug'])->exists();
            if (!$exists) {
                DB::table('user_types')->insert([
                    'slug' => $type['slug'],
                    'name' => $type['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $created++;
            }
        }

        $this->command->info("  ✓ User types: {$created} created, " . (count($types) - $created) . ' existed');
    }

    private function seedCities(): void
    {
        $this->command->info('🏙️  Seeding cities...');
        $now = now();

        $cities = [
            ['en' => 'Almaty', 'ru' => 'Алматы', 'kk' => 'Алматы'],
            ['en' => 'Astana', 'ru' => 'Астана', 'kk' => 'Астана'],
        ];

        $created = 0;
        foreach ($cities as $city) {
            $existingCityId = DB::table('city_translations')
                ->where('language_code', 'en')
                ->where('name', $city['en'])
                ->value('city_id');

            if (!$existingCityId) {
                $cityId = DB::table('cities')->insertGetId([
                    'is_active' => true,
                    'ad_frequency' => 5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('city_translations')->insert([
                    ['city_id' => $cityId, 'language_code' => 'en', 'name' => $city['en']],
                    ['city_id' => $cityId, 'language_code' => 'ru', 'name' => $city['ru']],
                    ['city_id' => $cityId, 'language_code' => 'kk', 'name' => $city['kk']],
                ]);
                $created++;
            }
        }

        $this->command->info("  ✓ Cities: {$created} created, " . (count($cities) - $created) . ' existed');
    }

    private function seedActivityTypes(): void
    {
        $this->command->info('🎯 Seeding activity types (REQUIRED for hangouts)...');
        $now = now();

        // Essential activity types for basic app functionality
        $activityTypes = [
            ['slug' => 'coffee', 'icon' => "☕", 'en' => 'Coffee', 'ru' => 'Кофе', 'kk' => 'Кофе'],
            ['slug' => 'beer', 'icon' => "🍺", 'en' => 'Beer', 'ru' => 'Пиво', 'kk' => 'Сыра'],
            ['slug' => 'restaurant', 'icon' => "🍽️", 'en' => 'Restaurant', 'ru' => 'Ресторан', 'kk' => 'Мейрамхана'],
            ['slug' => 'walk', 'icon' => "🚶", 'en' => 'Walk', 'ru' => 'Прогулка', 'kk' => 'Серуен'],
            ['slug' => 'cinema', 'icon' => "🎬", 'en' => 'Cinema', 'ru' => 'Кино', 'kk' => 'Кино'],
            ['slug' => 'bowling', 'icon' => "🎳", 'en' => 'Bowling', 'ru' => 'Боулинг', 'kk' => 'Боулинг'],
            ['slug' => 'concert', 'icon' => "🎵", 'en' => 'Concert', 'ru' => 'Концерт', 'kk' => 'Концерт'],
            ['slug' => 'sports', 'icon' => "⚽", 'en' => 'Sports', 'ru' => 'Спорт', 'kk' => 'Спорт'],
            ['slug' => 'board-games', 'icon' => "🎲", 'en' => 'Board Games', 'ru' => 'Настолки', 'kk' => 'Үстел ойындары'],
            ['slug' => 'karaoke', 'icon' => "🎤", 'en' => 'Karaoke', 'ru' => 'Караоке', 'kk' => 'Караоке'],
        ];

        $created = 0;
        $updated = 0;
        foreach ($activityTypes as $type) {
            $existing = DB::table('activity_types')->where('slug', $type['slug'])->first();

            if ($existing) {
                // Ensure all 3 translations exist
                foreach (['en' => $type['en'], 'ru' => $type['ru'], 'kk' => $type['kk']] as $lang => $name) {
                    $hasTranslation = DB::table('activity_type_translations')
                        ->where('activity_type_id', $existing->id)
                        ->where('language_code', $lang)
                        ->exists();

                    if (!$hasTranslation) {
                        DB::table('activity_type_translations')->insert([
                            'activity_type_id' => $existing->id,
                            'language_code' => $lang,
                            'name' => $name,
                        ]);
                        $updated++;
                    }
                }
                continue;
            }

            $typeId = DB::table('activity_types')->insertGetId([
                'slug' => $type['slug'],
                'bg_photo' => null,
                'icon' => $type['icon'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('activity_type_translations')->insert([
                ['activity_type_id' => $typeId, 'language_code' => 'en', 'name' => $type['en']],
                ['activity_type_id' => $typeId, 'language_code' => 'ru', 'name' => $type['ru']],
                ['activity_type_id' => $typeId, 'language_code' => 'kk', 'name' => $type['kk']],
            ]);
            $created++;
        }

        $this->command->info("  ✓ Activity types: {$created} created, {$updated} translations added");
    }

    private function seedInterests(): void
    {
        $this->command->info('❤️  Seeding interests (for user profiles)...');
        $now = now();

        // Essential interests for user profiles
        $interests = [
            ['slug' => 'coffee', 'icon' => "☕", 'en' => 'Coffee', 'ru' => 'Кофе', 'kk' => 'Кофе'],
            ['slug' => 'beer', 'icon' => "🍺", 'en' => 'Beer', 'ru' => 'Пиво', 'kk' => 'Сыра'],
            ['slug' => 'sports', 'icon' => "⚽", 'en' => 'Sports', 'ru' => 'Спорт', 'kk' => 'Спорт'],
            ['slug' => 'music', 'icon' => "🎵", 'en' => 'Music', 'ru' => 'Музыка', 'kk' => 'Музыка'],
            ['slug' => 'cinema', 'icon' => "🎬", 'en' => 'Cinema', 'ru' => 'Кино', 'kk' => 'Кино'],
            ['slug' => 'travel', 'icon' => "✈️", 'en' => 'Travel', 'ru' => 'Путешествия', 'kk' => 'Саяхат'],
            ['slug' => 'gaming', 'icon' => "🎮", 'en' => 'Gaming', 'ru' => 'Видеоигры', 'kk' => 'Ойындар'],
            ['slug' => 'books', 'icon' => "📚", 'en' => 'Books', 'ru' => 'Книги', 'kk' => 'Кітаптар'],
            ['slug' => 'fitness', 'icon' => "💪", 'en' => 'Fitness', 'ru' => 'Фитнес', 'kk' => 'Фитнес'],
            ['slug' => 'board-games', 'icon' => "🎲", 'en' => 'Board Games', 'ru' => 'Настолки', 'kk' => 'Үстел ойындары'],
        ];

        $created = 0;
        $updated = 0;
        foreach ($interests as $index => $interest) {
            $existing = DB::table('interests')->where('slug', $interest['slug'])->first();

            if ($existing) {
                // Ensure 'kk' translation exists
                foreach (['en' => $interest['en'], 'ru' => $interest['ru'], 'kk' => $interest['kk']] as $lang => $name) {
                    $hasTranslation = DB::table('interest_translations')
                        ->where('interest_id', $existing->id)
                        ->where('language_code', $lang)
                        ->exists();

                    if (!$hasTranslation) {
                        DB::table('interest_translations')->insert([
                            'interest_id' => $existing->id,
                            'language_code' => $lang,
                            'name' => $name,
                        ]);
                        $updated++;
                    }
                }
                continue;
            }

            $interestId = DB::table('interests')->insertGetId([
                'slug' => $interest['slug'],
                'icon' => $interest['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('interest_translations')->insert([
                ['interest_id' => $interestId, 'language_code' => 'en', 'name' => $interest['en']],
                ['interest_id' => $interestId, 'language_code' => 'ru', 'name' => $interest['ru']],
                ['interest_id' => $interestId, 'language_code' => 'kk', 'name' => $interest['kk']],
            ]);
            $created++;
        }

        $this->command->info("  ✓ Interests: {$created} created, {$updated} translations added");
    }

    private function showSummary(): void
    {
        $this->command->info('📊 Database Status:');
        $this->command->info('   User types: ' . DB::table('user_types')->count());
        $this->command->info('   Cities: ' . DB::table('cities')->count());
        $this->command->info('   Activity types: ' . DB::table('activity_types')->count());
        $this->command->info('   Interests: ' . DB::table('interests')->count());
        $this->command->newLine();

        $this->command->info('✅ The app can now:');
        $this->command->info('   • Register and login users');
        $this->command->info('   • Create hangouts (activity types available)');
        $this->command->info('   • Browse hangouts by city');
        $this->command->info('   • Set user interests');
        $this->command->newLine();

        $this->command->warn('⚠️  Next steps:');
        $this->command->warn('   1. Create admin user: php artisan db:seed --class=AdminUserSeeder');
        $this->command->warn('   2. Create test users: php artisan db:seed --class=TestUsersSeeder');
        $this->command->warn('   3. Add places: php artisan db:seed --class=PlacesSeeder');
    }
}
