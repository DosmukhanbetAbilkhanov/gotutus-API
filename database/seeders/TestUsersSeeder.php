<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates test users for Almaty and Astana cities.
 *
 * Creates 10 test users per city (5 male, 5 female).
 * All users have:
 * - Password: password
 * - Verified phone
 * - Random interests assigned
 * - Email: test.user.{city}.{number}@tanys.app
 *
 * Run: php artisan db:seed --class=TestUsersSeeder
 * Or via route: GET /api/dev/seed
 */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating test users for Almaty and Astana...');

        $now = now();
        $password = Hash::make('password');

        // Get client user type
        $clientTypeId = DB::table('user_types')->where('slug', 'client')->value('id');

        if (!$clientTypeId) {
            $this->command->error('Client user type not found! Run ProductionSeeder first.');
            return;
        }

        // Get interest IDs for random assignment
        $interestIds = DB::table('interests')->where('is_active', true)->pluck('id')->toArray();

        // Almaty users
        $this->seedCityUsers('Almaty', $clientTypeId, $password, $interestIds, $now);

        // Astana users
        $this->seedCityUsers('Astana', $clientTypeId, $password, $interestIds, $now);

        $this->command->info('✓ Test users created successfully!');
    }

    private function seedCityUsers(
        string $cityName,
        int $clientTypeId,
        string $password,
        array $interestIds,
        $now
    ): void {
        // Get city ID
        $cityId = DB::table('city_translations')
            ->where('language_code', 'en')
            ->where('name', $cityName)
            ->value('city_id');

        if (!$cityId) {
            $this->command->warn("  {$cityName} city not found! Skipping.");
            return;
        }

        $citySlug = strtolower($cityName);

        // Test users data
        $users = [
            // Male users
            [
                'name' => 'Arman Suleimenov',
                'gender' => Gender::Male,
                'age' => 25,
                'bio' => 'Люблю кофе, спорт и настолки. Ищу компанию для походов и квизов.'
            ],
            [
                'name' => 'Daulet Zhankeldinov',
                'gender' => Gender::Male,
                'age' => 28,
                'bio' => 'Программист и геймер. Интересуют технологии, стартапы и киберспорт.'
            ],
            [
                'name' => 'Nurlan Bektasov',
                'gender' => Gender::Male,
                'age' => 30,
                'bio' => 'Фотограф-любитель. Увлекаюсь путешествиями и природой.'
            ],
            [
                'name' => 'Timur Akhmetov',
                'gender' => Gender::Male,
                'age' => 26,
                'bio' => 'Музыкант. Играю на гитаре, люблю концерты и джем-сейшны.'
            ],
            [
                'name' => 'Yerik Karimov',
                'gender' => Gender::Male,
                'age' => 32,
                'bio' => 'Бизнесмен. Интересуют нетворкинг, инвестиции и автомобили.'
            ],
            // Female users
            [
                'name' => 'Aigerim Nurgaliyeva',
                'gender' => Gender::Female,
                'age' => 24,
                'bio' => 'Дизайнер. Люблю искусство, моду и фотографию.'
            ],
            [
                'name' => 'Dana Ospanova',
                'gender' => Gender::Female,
                'age' => 27,
                'bio' => 'Маркетолог. Увлекаюсь йогой, книгами и путешествиями.'
            ],
            [
                'name' => 'Kamila Smagulova',
                'gender' => Gender::Female,
                'age' => 25,
                'bio' => 'Фитнес-тренер. Здоровый образ жизни, спорт и правильное питание.'
            ],
            [
                'name' => 'Madina Tulegenova',
                'gender' => Gender::Female,
                'age' => 29,
                'bio' => 'Журналистка. Интересуют политика, кино и литература.'
            ],
            [
                'name' => 'Saule Zhumagaliyeva',
                'gender' => Gender::Female,
                'age' => 26,
                'bio' => 'Учитель английского. Люблю языки, танцы и караоке.'
            ],
        ];

        $created = 0;

        foreach ($users as $index => $person) {
            $email = "test.user.{$citySlug}." . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) . '@tanys.app';
            $phone = '+7701' . $cityId . str_pad((string)($index + 1), 5, '0', STR_PAD_LEFT);

            // Check if user already exists
            if (DB::table('users')->where('email', $email)->exists()) {
                continue;
            }

            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $person['name'],
                'email' => $email,
                'phone' => $phone,
                'age' => $person['age'],
                'gender' => $person['gender']->value,
                'bio' => $person['bio'],
                'password' => $password,
                'city_id' => $cityId,
                'status' => UserStatus::Active->value,
                'user_type_id' => $clientTypeId,
                'phone_verified_at' => $now,
                'public_offer_accepted_at' => $now,
                'public_offer_version' => 1,
                'trust_score' => rand(35, 50) / 10, // 3.5 - 5.0
                'ratings_count' => rand(0, 10),
                'average_rating' => rand(35, 50) / 10,
                'attendance_rate' => rand(80, 100),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Assign 3-7 random interests
            if (!empty($interestIds)) {
                $count = rand(3, min(7, count($interestIds)));
                $selectedInterests = (array) array_rand(array_flip($interestIds), $count);

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
        }

        $this->command->info("  {$cityName}: {$created} users created.");
    }
}
