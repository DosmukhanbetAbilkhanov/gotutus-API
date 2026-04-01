<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Production-safe, idempotent seeder for tanys.app.
 *
 * Seeds: user_types, activity_types + translations, interests + translations,
 *        and test users for Aktobe.
 *
 * Test users are identifiable by:
 *   - Email domain: @companion.test
 *   - Name prefix: [TEST]
 *
 * Run: php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    /** Email domain used to identify test users. */
    public const TEST_EMAIL_DOMAIN = '@companion.test';
    public function run(): void
    {
        $this->command->info('Starting ProductionSeeder...');

        $this->seedUserTypes();
        $this->seedCities();
        $this->seedActivityTypes();
        $this->seedInterests();
        $this->seedAktobeUsers();

        $this->command->info('ProductionSeeder complete!');
    }

    // =========================================================================
    // USER TYPES
    // =========================================================================

    private function seedUserTypes(): void
    {
        $now = now();

        $types = [
            ['slug' => 'client', 'name' => 'Client'],
            ['slug' => 'admin', 'name' => 'Admin'],
            ['slug' => 'city_manager', 'name' => 'City Manager'],
        ];

        $created = 0;
        foreach ($types as $type) {
            $exists = DB::table('user_types')->where('slug', $type['slug'])->exists();
            if ($exists) {
                continue;
            }

            DB::table('user_types')->insert([
                'slug' => $type['slug'],
                'name' => $type['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $created++;
        }

        $this->command->info("  User types: {$created} created, ".(count($types) - $created).' already existed.');
    }

    // =========================================================================
    // CITIES
    // =========================================================================

    private function seedCities(): void
    {
        $now = now();

        $cities = [
            ['en' => 'Almaty', 'ru' => 'Алматы', 'kk' => 'Алматы'],
            ['en' => 'Astana', 'ru' => 'Астана', 'kk' => 'Астана'],
            ['en' => 'Aktobe', 'ru' => 'Актобе', 'kk' => 'Ақтөбе'],
            ['en' => 'Aktau', 'ru' => 'Актау', 'kk' => 'Ақтау'],
        ];

        $created = 0;
        foreach ($cities as $city) {
            // Check if city already exists by looking for its EN translation
            $existingCityId = DB::table('city_translations')
                ->where('language_code', 'en')
                ->where('name', $city['en'])
                ->value('city_id');

            if ($existingCityId) {
                // Ensure kk translation exists (old seeder used 'kz' code)
                $hasKk = DB::table('city_translations')
                    ->where('city_id', $existingCityId)
                    ->where('language_code', 'kk')
                    ->exists();

                if (! $hasKk) {
                    DB::table('city_translations')->insert([
                        'city_id' => $existingCityId,
                        'language_code' => 'kk',
                        'name' => $city['kk'],
                    ]);
                }

                continue;
            }

            $cityId = DB::table('cities')->insertGetId([
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('city_translations')->insert([
                ['city_id' => $cityId, 'language_code' => 'en', 'name' => $city['en']],
                ['city_id' => $cityId, 'language_code' => 'ru', 'name' => $city['ru']],
                ['city_id' => $cityId, 'language_code' => 'kk', 'name' => $city['kk']],
            ]);
            $created++;
        }

        $this->command->info("  Cities: {$created} created, ".(count($cities) - $created).' already existed.');
    }

    // =========================================================================
    // ACTIVITY TYPES
    // =========================================================================

    private function seedActivityTypes(): void
    {
        $now = now();

        $activityTypes = [
            ['slug' => 'beer', 'icon' => "\u{1F37A}", 'en' => 'Beer', 'ru' => 'Пиво', 'kk' => 'Сыра'],
            ['slug' => 'coffee', 'icon' => "\u{2615}", 'en' => 'Coffee', 'ru' => 'Кофе', 'kk' => 'Кофе'],
            ['slug' => 'sushi', 'icon' => "\u{1F363}", 'en' => 'Sushi', 'ru' => 'Суши', 'kk' => 'Суши'],
            ['slug' => 'fast_food', 'icon' => "\u{1F354}", 'en' => 'Fast Food', 'ru' => 'Фаст-Фуд', 'kk' => 'Фаст-Фуд'],
            ['slug' => 'bathhouse', 'icon' => "\u{1F9D6}", 'en' => 'Bathhouse', 'ru' => 'Баня', 'kk' => 'Монша'],
            ['slug' => 'kumys', 'icon' => "\u{1F95B}", 'en' => 'Kumys', 'ru' => 'Кумыс', 'kk' => 'Кумыс'],
            ['slug' => 'walk', 'icon' => "\u{1F6B6}", 'en' => 'Walk', 'ru' => 'Прогулка', 'kk' => 'Серуен'],
            ['slug' => 'concert', 'icon' => "\u{1F3B5}", 'en' => 'Concert', 'ru' => 'Концерт', 'kk' => 'Концерт'],
            ['slug' => 'standup', 'icon' => "\u{1F3A4}", 'en' => 'Standup', 'ru' => 'Стендап', 'kk' => 'Стендап'],
            ['slug' => 'bowling', 'icon' => "\u{1F3B3}", 'en' => 'Bowling', 'ru' => 'Боулинг', 'kk' => 'Боулинг'],
            ['slug' => 'billiards', 'icon' => "\u{1F3B1}", 'en' => 'Billiards', 'ru' => 'Бильярд', 'kk' => 'Бильярд'],
            ['slug' => 'hookah', 'icon' => "\u{1F4A8}", 'en' => 'Hookah', 'ru' => 'Кальян', 'kk' => 'Кальян'],
            ['slug' => 'karaoke', 'icon' => "\u{1F3A4}", 'en' => 'Karaoke', 'ru' => 'Караоке', 'kk' => 'Караоке'],
            ['slug' => 'restaurant', 'icon' => "\u{1F37D}\u{FE0F}", 'en' => 'Restaurant', 'ru' => 'Ресторан', 'kk' => 'Мейрамхана'],
            ['slug' => 'kvest', 'icon' => "\u{1F50D}", 'en' => 'Quest', 'ru' => 'Квест', 'kk' => 'Квест'],
            ['slug' => 'paintball', 'icon' => "\u{1F3AF}", 'en' => 'Paintball', 'ru' => 'Пейнтбол', 'kk' => 'Пейнтбол'],
            ['slug' => 'pc_club', 'icon' => "\u{1F4BB}", 'en' => 'PC Club', 'ru' => 'Компьютерный Клуб', 'kk' => 'Компьютерлік Клуб'],
            ['slug' => 'quiz', 'icon' => "\u{2753}", 'en' => 'Quiz', 'ru' => 'Квиз', 'kk' => 'Квиз'],
            ['slug' => 'tennis', 'icon' => "\u{1F3BE}", 'en' => 'Tennis', 'ru' => 'Теннис', 'kk' => 'Теннис'],
            ['slug' => 'football', 'icon' => "\u{26BD}", 'en' => 'Football', 'ru' => 'Футбол', 'kk' => 'Футбол'],
            ['slug' => 'cinema', 'icon' => "\u{1F3AC}", 'en' => 'Cinema', 'ru' => 'Кино', 'kk' => 'Кино'],
            ['slug' => 'board-games', 'icon' => "\u{1F3B2}", 'en' => 'Board Games', 'ru' => 'Настолки', 'kk' => 'Үстел ойындары'],
        ];

        $created = 0;
        $updated = 0;
        foreach ($activityTypes as $type) {
            $existing = DB::table('activity_types')->where('slug', $type['slug'])->first();

            if ($existing) {
                // Ensure all 3 translations exist (old seeder used 'kz', we also add 'kk')
                foreach (['en' => $type['en'], 'ru' => $type['ru'], 'kk' => $type['kk']] as $lang => $name) {
                    $hasTranslation = DB::table('activity_type_translations')
                        ->where('activity_type_id', $existing->id)
                        ->where('language_code', $lang)
                        ->exists();

                    if (! $hasTranslation) {
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

        $this->command->info("  Activity types: {$created} created, {$updated} translations added.");
    }

    // =========================================================================
    // INTERESTS
    // =========================================================================

    private function seedInterests(): void
    {
        $now = now();

        $interests = [
            ['slug' => 'beer', 'icon' => "\u{1F37A}", 'en' => 'Beer', 'ru' => 'Пиво', 'kk' => 'Сыра'],
            ['slug' => 'coffee', 'icon' => "\u{2615}", 'en' => 'Coffee', 'ru' => 'Кофе', 'kk' => 'Кофе'],
            ['slug' => 'sports', 'icon' => "\u{26BD}", 'en' => 'Sports', 'ru' => 'Спорт', 'kk' => 'Спорт'],
            ['slug' => 'fitness', 'icon' => "\u{1F4AA}", 'en' => 'Fitness', 'ru' => 'Фитнес', 'kk' => 'Фитнес'],
            ['slug' => 'music', 'icon' => "\u{1F3B5}", 'en' => 'Music', 'ru' => 'Музыка', 'kk' => 'Музыка'],
            ['slug' => 'cinema', 'icon' => "\u{1F3AC}", 'en' => 'Cinema', 'ru' => 'Кино', 'kk' => 'Кино'],
            ['slug' => 'travel', 'icon' => "\u{2708}\u{FE0F}", 'en' => 'Travel', 'ru' => 'Путешествия', 'kk' => 'Саяхат'],
            ['slug' => 'cooking', 'icon' => "\u{1F373}", 'en' => 'Cooking', 'ru' => 'Кулинария', 'kk' => 'Аспаздық'],
            ['slug' => 'gaming', 'icon' => "\u{1F3AE}", 'en' => 'Gaming', 'ru' => 'Видеоигры', 'kk' => 'Ойындар'],
            ['slug' => 'board-games', 'icon' => "\u{1F3B2}", 'en' => 'Board Games', 'ru' => 'Настолки', 'kk' => 'Үстел ойындары'],
            ['slug' => 'books', 'icon' => "\u{1F4DA}", 'en' => 'Books', 'ru' => 'Книги', 'kk' => 'Кітаптар'],
            ['slug' => 'art', 'icon' => "\u{1F3A8}", 'en' => 'Art', 'ru' => 'Искусство', 'kk' => 'Өнер'],
            ['slug' => 'photography', 'icon' => "\u{1F4F7}", 'en' => 'Photography', 'ru' => 'Фотография', 'kk' => 'Фотография'],
            ['slug' => 'nature', 'icon' => "\u{1F333}", 'en' => 'Nature', 'ru' => 'Природа', 'kk' => 'Табиғат'],
            ['slug' => 'technology', 'icon' => "\u{1F4BB}", 'en' => 'Technology', 'ru' => 'Технологии', 'kk' => 'Технологиялар'],
            ['slug' => 'politics', 'icon' => "\u{1F3DB}\u{FE0F}", 'en' => 'Politics', 'ru' => 'Политика', 'kk' => 'Саясат'],
            ['slug' => 'hookah', 'icon' => "\u{1F4A8}", 'en' => 'Hookah', 'ru' => 'Кальян', 'kk' => 'Кальян'],
            ['slug' => 'karaoke', 'icon' => "\u{1F3A4}", 'en' => 'Karaoke', 'ru' => 'Караоке', 'kk' => 'Караоке'],
            ['slug' => 'bowling', 'icon' => "\u{1F3B3}", 'en' => 'Bowling', 'ru' => 'Боулинг', 'kk' => 'Боулинг'],
            ['slug' => 'dancing', 'icon' => "\u{1F483}", 'en' => 'Dancing', 'ru' => 'Танцы', 'kk' => 'Би'],
            ['slug' => 'yoga', 'icon' => "\u{1F9D8}", 'en' => 'Yoga', 'ru' => 'Йога', 'kk' => 'Йога'],
            ['slug' => 'cars', 'icon' => "\u{1F697}", 'en' => 'Cars', 'ru' => 'Автомобили', 'kk' => 'Автокөліктер'],
            ['slug' => 'fashion', 'icon' => "\u{1F457}", 'en' => 'Fashion', 'ru' => 'Мода', 'kk' => 'Сән'],
            ['slug' => 'languages', 'icon' => "\u{1F30D}", 'en' => 'Languages', 'ru' => 'Языки', 'kk' => 'Тілдер'],
            ['slug' => 'startups', 'icon' => "\u{1F680}", 'en' => 'Startups', 'ru' => 'Стартапы', 'kk' => 'Стартаптар'],
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

                    if (! $hasTranslation) {
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

        $this->command->info("  Interests: {$created} created, {$updated} translations added.");
    }

    // =========================================================================
    // AKTOBE TEST USERS
    // =========================================================================

    private function seedAktobeUsers(): void
    {
        $now = now();
        $password = Hash::make('password');

        // Find Aktobe city ID
        $aktobeId = DB::table('city_translations')
            ->where('language_code', 'en')
            ->where('name', 'Aktobe')
            ->value('city_id');

        if (! $aktobeId) {
            $this->command->warn('  Aktobe city not found! Skipping user creation.');

            return;
        }

        $clientTypeId = DB::table('user_types')->where('slug', 'client')->value('id');

        if (! $clientTypeId) {
            $this->command->warn('  Client user type not found! Skipping user creation.');

            return;
        }

        // City manager for Aktobe
        $cityManagerTypeId = DB::table('user_types')->where('slug', 'city_manager')->value('id');

        $aktobeManager = [
            'name' => '[TEST] Aktobe Manager',
            'email' => 'manager.aktobe@companion.test',
            'phone' => '+77010000001',
        ];

        if (! DB::table('users')->where('email', $aktobeManager['email'])->exists()) {
            DB::table('users')->insert([
                'name' => $aktobeManager['name'],
                'email' => $aktobeManager['email'],
                'phone' => $aktobeManager['phone'],
                'age' => 30,
                'gender' => Gender::Male->value,
                'bio' => 'Менеджер города Актобе',
                'password' => $password,
                'city_id' => $aktobeId,
                'status' => 'active',
                'user_type_id' => $cityManagerTypeId,
                'phone_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->command->info('  Created Aktobe city manager.');
        }

        // Test users for Aktobe
        $aktobeUsers = [
            ['name' => '[TEST] Bolat Zhangeldinov', 'gender' => Gender::Male, 'age' => 25, 'bio' => 'Люблю спорт и кофе'],
            ['name' => '[TEST] Yerlan Turganov', 'gender' => Gender::Male, 'age' => 28, 'bio' => 'Фанат настолок и квизов'],
            ['name' => '[TEST] Kairat Mukhambetov', 'gender' => Gender::Male, 'age' => 32, 'bio' => 'Походы, природа, фотография'],
            ['name' => '[TEST] Miras Nurpeisov', 'gender' => Gender::Male, 'age' => 22, 'bio' => 'Геймер и программист'],
            ['name' => '[TEST] Temirlan Akhmetov', 'gender' => Gender::Male, 'age' => 27, 'bio' => 'Музыкант, ищу компанию на концерты'],
            ['name' => '[TEST] Aizhan Bektasova', 'gender' => Gender::Female, 'age' => 24, 'bio' => 'Йога, танцы, путешествия'],
            ['name' => '[TEST] Symbat Tulegenova', 'gender' => Gender::Female, 'age' => 26, 'bio' => 'Кулинария и искусство'],
            ['name' => '[TEST] Zhanar Ospanova', 'gender' => Gender::Female, 'age' => 29, 'bio' => 'Книги, кофе и кино'],
            ['name' => '[TEST] Karlygash Smagulova', 'gender' => Gender::Female, 'age' => 23, 'bio' => 'Фитнес и здоровый образ жизни'],
            ['name' => '[TEST] Ainur Zhumagaliyeva', 'gender' => Gender::Female, 'age' => 30, 'bio' => 'Фотография и мода'],
        ];

        $created = 0;
        $phoneCounter = 100;

        // Collect interest IDs for assigning to users
        $interestIds = DB::table('interests')->where('is_active', true)->pluck('id')->toArray();

        foreach ($aktobeUsers as $person) {
            $email = 'aktobe.user'.str_pad((string) $phoneCounter, 3, '0', STR_PAD_LEFT).'@companion.test';

            if (DB::table('users')->where('email', $email)->exists()) {
                $phoneCounter++;

                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'name' => $person['name'],
                'email' => $email,
                'phone' => '+77010'.str_pad((string) $phoneCounter, 6, '0', STR_PAD_LEFT),
                'age' => $person['age'],
                'gender' => $person['gender']->value,
                'bio' => $person['bio'],
                'password' => $password,
                'city_id' => $aktobeId,
                'status' => 'active',
                'user_type_id' => $clientTypeId,
                'phone_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Assign 3-6 random interests to each user
            if (! empty($interestIds)) {
                $count = rand(3, min(6, count($interestIds)));
                $selectedInterests = array_rand(array_flip($interestIds), $count);
                if (! is_array($selectedInterests)) {
                    $selectedInterests = [$selectedInterests];
                }

                foreach ($selectedInterests as $interestId) {
                    DB::table('user_interests')->insertOrIgnore([
                        'user_id' => $userId,
                        'interest_id' => $interestId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $created++;
            $phoneCounter++;
        }

        $this->command->info("  Aktobe users: {$created} created, ".(count($aktobeUsers) - $created).' already existed.');
    }
}
