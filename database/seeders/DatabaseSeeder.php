<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\ActivityType;
use App\Models\City;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCities();
        $this->seedActivityTypes();
        $this->seedPlaces();
        $this->seedTestUsers();
    }

    private function seedCities(): void
    {
        $cities = [
            ['ru' => 'Алматы', 'kz' => 'Алматы', 'en' => 'Almaty'],
            ['ru' => 'Астана', 'kz' => 'Астана', 'en' => 'Astana'],
            ['ru' => 'Шымкент', 'kz' => 'Шымкент', 'en' => 'Shymkent'],
            ['ru' => 'Караганда', 'kz' => 'Қарағанды', 'en' => 'Karaganda'],
            ['ru' => 'Актобе', 'kz' => 'Ақтөбе', 'en' => 'Aktobe'],
            ['ru' => 'Тараз', 'kz' => 'Тараз', 'en' => 'Taraz'],
            ['ru' => 'Павлодар', 'kz' => 'Павлодар', 'en' => 'Pavlodar'],
            ['ru' => 'Усть-Каменогорск', 'kz' => 'Өскемен', 'en' => 'Ust-Kamenogorsk'],
            ['ru' => 'Семей', 'kz' => 'Семей', 'en' => 'Semey'],
            ['ru' => 'Атырау', 'kz' => 'Атырау', 'en' => 'Atyrau'],
        ];

        foreach ($cities as $cityData) {
            $city = City::create(['is_active' => true]);
            foreach (['ru', 'kz', 'en'] as $locale) {
                $city->translations()->create([
                    'language_code' => $locale,
                    'name' => $cityData[$locale],
                ]);
            }
        }
    }

    private function seedActivityTypes(): void
    {
        $activityTypes = [
            [
                'slug' => 'beer',
                'icon' => 'beer.png',
                'bg_photo' => 'beer-bg.jpg',
                'translations' => [
                    'ru' => 'Пиво',
                    'kz' => 'Сыра',
                    'en' => 'Grabbing Beer',
                ],
            ],
            [
                'slug' => 'coffee',
                'icon' => 'coffee.png',
                'bg_photo' => 'coffee-bg.jpg',
                'translations' => [
                    'ru' => 'Кофе',
                    'kz' => 'Кофе',
                    'en' => 'Coffee',
                ],
            ],
            [
                'slug' => 'food',
                'icon' => 'food.png',
                'bg_photo' => 'food-bg.jpg',
                'translations' => [
                    'ru' => 'Поесть',
                    'kz' => 'Тамақтану',
                    'en' => 'Grabbing Food',
                ],
            ],
            [
                'slug' => 'walk',
                'icon' => 'walk.png',
                'bg_photo' => 'walk-bg.jpg',
                'translations' => [
                    'ru' => 'Прогулка',
                    'kz' => 'Серуендеу',
                    'en' => 'Walk',
                ],
            ],
            [
                'slug' => 'cinema',
                'icon' => 'cinema.png',
                'bg_photo' => 'cinema-bg.jpg',
                'translations' => [
                    'ru' => 'Кино',
                    'kz' => 'Кино',
                    'en' => 'Cinema',
                ],
            ],
            [
                'slug' => 'sport',
                'icon' => 'sport.png',
                'bg_photo' => 'sport-bg.jpg',
                'translations' => [
                    'ru' => 'Спорт',
                    'kz' => 'Спорт',
                    'en' => 'Sports',
                ],
            ],
            [
                'slug' => 'games',
                'icon' => 'games.png',
                'bg_photo' => 'games-bg.jpg',
                'translations' => [
                    'ru' => 'Игры',
                    'kz' => 'Ойындар',
                    'en' => 'Board Games',
                ],
            ],
            [
                'slug' => 'hookah',
                'icon' => 'hookah.png',
                'bg_photo' => 'hookah-bg.jpg',
                'translations' => [
                    'ru' => 'Кальян',
                    'kz' => 'Қалиян',
                    'en' => 'Hookah',
                ],
            ],
            [
                'slug' => 'concert',
                'icon' => 'concert.png',
                'bg_photo' => 'concert-bg.jpg',
                'translations' => [
                    'ru' => 'Концерт',
                    'kz' => 'Концерт',
                    'en' => 'Concert',
                ],
            ],
            [
                'slug' => 'party',
                'icon' => 'party.png',
                'bg_photo' => 'party-bg.jpg',
                'translations' => [
                    'ru' => 'Вечеринка',
                    'kz' => 'Кеш',
                    'en' => 'Party',
                ],
            ],
        ];

        foreach ($activityTypes as $typeData) {
            $activityType = ActivityType::create([
                'slug' => $typeData['slug'],
                'icon' => $typeData['icon'],
                'bg_photo' => $typeData['bg_photo'],
                'is_active' => true,
            ]);

            foreach (['ru', 'kz', 'en'] as $locale) {
                $activityType->translations()->create([
                    'language_code' => $locale,
                    'name' => $typeData['translations'][$locale],
                ]);
            }
        }
    }

    private function seedPlaces(): void
    {
        $almaty = City::whereHas('translations', fn ($q) => $q->where('name', 'Алматы'))->first();
        $astana = City::whereHas('translations', fn ($q) => $q->where('name', 'Астана'))->first();

        $beer = ActivityType::where('slug', 'beer')->first();
        $coffee = ActivityType::where('slug', 'coffee')->first();
        $food = ActivityType::where('slug', 'food')->first();
        $hookah = ActivityType::where('slug', 'hookah')->first();

        $places = [
            // Almaty places
            [
                'city_id' => $almaty->id,
                'activity_types' => [$beer->id, $food->id],
                'translations' => [
                    'ru' => ['name' => 'Пивной бар "У друзей"', 'address' => 'ул. Абая 150'],
                    'kz' => ['name' => 'Сыра бар "Достар"', 'address' => 'Абай к-сі 150'],
                    'en' => ['name' => 'Friends Beer Bar', 'address' => 'Abay str. 150'],
                ],
            ],
            [
                'city_id' => $almaty->id,
                'activity_types' => [$coffee->id],
                'translations' => [
                    'ru' => ['name' => 'Кофейня Brazuka', 'address' => 'ул. Достык 89'],
                    'kz' => ['name' => 'Brazuka кофеханасы', 'address' => 'Достық к-сі 89'],
                    'en' => ['name' => 'Brazuka Coffee', 'address' => 'Dostyk str. 89'],
                ],
            ],
            [
                'city_id' => $almaty->id,
                'activity_types' => [$food->id],
                'translations' => [
                    'ru' => ['name' => 'Ресторан Navat', 'address' => 'пр. Аль-Фараби 77'],
                    'kz' => ['name' => 'Navat мейрамханасы', 'address' => 'Әл-Фараби д-ы 77'],
                    'en' => ['name' => 'Navat Restaurant', 'address' => 'Al-Farabi ave. 77'],
                ],
            ],
            [
                'city_id' => $almaty->id,
                'activity_types' => [$hookah->id],
                'translations' => [
                    'ru' => ['name' => 'Кальянная Smoke Lab', 'address' => 'ул. Жандосова 58'],
                    'kz' => ['name' => 'Smoke Lab қалиянханасы', 'address' => 'Жандосов к-сі 58'],
                    'en' => ['name' => 'Smoke Lab Lounge', 'address' => 'Zhandosova str. 58'],
                ],
            ],
            // Astana places
            [
                'city_id' => $astana->id,
                'activity_types' => [$beer->id],
                'translations' => [
                    'ru' => ['name' => 'Пивной ресторан Beerhouse', 'address' => 'ул. Кенесары 40'],
                    'kz' => ['name' => 'Beerhouse сыра мейрамханасы', 'address' => 'Кенесары к-сі 40'],
                    'en' => ['name' => 'Beerhouse Restaurant', 'address' => 'Kenesary str. 40'],
                ],
            ],
            [
                'city_id' => $astana->id,
                'activity_types' => [$coffee->id, $food->id],
                'translations' => [
                    'ru' => ['name' => 'Кофейня Story', 'address' => 'пр. Мангилик Ел 55'],
                    'kz' => ['name' => 'Story кофеханасы', 'address' => 'Мәңгілік Ел д-ы 55'],
                    'en' => ['name' => 'Story Coffee', 'address' => 'Mangilik El ave. 55'],
                ],
            ],
        ];

        foreach ($places as $placeData) {
            $place = Place::create([
                'city_id' => $placeData['city_id'],
            ]);

            foreach (['ru', 'kz', 'en'] as $locale) {
                $place->translations()->create([
                    'language_code' => $locale,
                    'name' => $placeData['translations'][$locale]['name'],
                    'address' => $placeData['translations'][$locale]['address'],
                ]);
            }

            $place->activityTypes()->attach($placeData['activity_types']);
        }
    }

    private function seedTestUsers(): void
    {
        $almaty = City::whereHas('translations', fn ($q) => $q->where('name', 'Алматы'))->first();
        $astana = City::whereHas('translations', fn ($q) => $q->where('name', 'Астана'))->first();

        // Create test users
        $users = [
            [
                'name' => 'Алексей',
                'phone' => '+77001111111',
                'email' => 'alex@test.com',
                'age' => 28,
                'gender' => Gender::Male,
                'city_id' => $almaty->id,
            ],
            [
                'name' => 'Мария',
                'phone' => '+77002222222',
                'email' => 'maria@test.com',
                'age' => 25,
                'gender' => Gender::Female,
                'city_id' => $almaty->id,
            ],
            [
                'name' => 'Дмитрий',
                'phone' => '+77003333333',
                'email' => 'dmitry@test.com',
                'age' => 32,
                'gender' => Gender::Male,
                'city_id' => $astana->id,
            ],
            [
                'name' => 'Айгуль',
                'phone' => '+77004444444',
                'email' => 'aigul@test.com',
                'age' => 24,
                'gender' => Gender::Female,
                'city_id' => $astana->id,
            ],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'phone' => $userData['phone'],
                'email' => $userData['email'],
                'age' => $userData['age'],
                'gender' => $userData['gender'],
                'password' => Hash::make('password'),
                'city_id' => $userData['city_id'],
                'status' => UserStatus::Active,
                'phone_verified_at' => now(),
            ]);
        }
    }
}
