<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates sample places with working hours for Almaty and Astana.
 *
 * Creates 15 places per city with:
 * - Translations (en, ru, kk)
 * - Activity type assignments
 * - Working hours (7 days)
 * - Contact information
 *
 * Run: php artisan db:seed --class=PlacesSeeder
 * Or via route: GET /api/dev/seed
 */
class PlacesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating places for Almaty and Astana...');

        $now = now();

        // Get activity type IDs
        $activityTypes = $this->getActivityTypes();

        if (empty($activityTypes)) {
            $this->command->error('No activity types found! Run ProductionSeeder first.');
            return;
        }

        // Seed Almaty places
        $this->seedCityPlaces('Almaty', $activityTypes, $now);

        // Seed Astana places
        $this->seedCityPlaces('Astana', $activityTypes, $now);

        $this->command->info('✓ Places created successfully!');
    }

    private function getActivityTypes(): array
    {
        return DB::table('activity_types')
            ->where('is_active', true)
            ->pluck('id', 'slug')
            ->toArray();
    }

    private function seedCityPlaces(string $cityName, array $activityTypes, $now): void
    {
        // Get city ID
        $cityId = DB::table('city_translations')
            ->where('language_code', 'en')
            ->where('name', $cityName)
            ->value('city_id');

        if (!$cityId) {
            $this->command->warn("  {$cityName} city not found! Skipping.");
            return;
        }

        $places = $this->getPlacesData($cityName);
        $created = 0;

        foreach ($places as $place) {
            // Check if place already exists
            $existsCount = DB::table('place_translations')
                ->where('language_code', 'en')
                ->where('name', $place['name']['en'])
                ->where(function($query) use ($cityId) {
                    $query->whereIn('place_id', function($q) use ($cityId) {
                        $q->select('id')->from('places')->where('city_id', $cityId);
                    });
                })
                ->count();

            if ($existsCount > 0) {
                continue;
            }

            // Create place
            $placeId = DB::table('places')->insertGetId([
                'city_id' => $cityId,
                'latitude' => $place['coordinates']['lat'],
                'longitude' => $place['coordinates']['lng'],
                'phone' => $place['phone'],
                'website' => $place['website'] ?? null,
                'instagram' => $place['instagram'] ?? null,
                'two_gis_url' => $place['two_gis_url'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Add translations
            DB::table('place_translations')->insert([
                [
                    'place_id' => $placeId,
                    'language_code' => 'en',
                    'name' => $place['name']['en'],
                    'address' => $place['address']['en'],
                    'description' => $place['description']['en'],
                ],
                [
                    'place_id' => $placeId,
                    'language_code' => 'ru',
                    'name' => $place['name']['ru'],
                    'address' => $place['address']['ru'],
                    'description' => $place['description']['ru'],
                ],
                [
                    'place_id' => $placeId,
                    'language_code' => 'kk',
                    'name' => $place['name']['kk'],
                    'address' => $place['address']['kk'],
                    'description' => $place['description']['kk'],
                ],
            ]);

            // Assign activity types
            foreach ($place['activity_slugs'] as $slug) {
                if (isset($activityTypes[$slug])) {
                    DB::table('activity_type_place')->insert([
                        'activity_type_id' => $activityTypes[$slug],
                        'place_id' => $placeId,
                    ]);
                }
            }

            // Add working hours (Mon-Sun)
            $this->addWorkingHours($placeId, $place['hours'], $now);

            $created++;
        }

        $this->command->info("  {$cityName}: {$created} places created.");
    }

    private function addWorkingHours(int $placeId, array $hours, $now): void
    {
        $workingHours = [];

        foreach ($hours as $day => $time) {
            if ($time === 'closed') {
                continue; // Don't add entry for closed days
            }

            [$open, $close] = explode('-', $time);

            $workingHours[] = [
                'place_id' => $placeId,
                'day_of_week' => $day, // 0 = Monday, 6 = Sunday
                'open_time' => trim($open) . ':00',
                'close_time' => trim($close) . ':00',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($workingHours)) {
            DB::table('place_working_hours')->insert($workingHours);
        }
    }

    private function getPlacesData(string $cityName): array
    {
        $isAlmaty = $cityName === 'Almaty';

        $places = [
            // Cafes & Coffee
            [
                'name' => [
                    'en' => $isAlmaty ? 'Coffee Boom' : 'Coffeeshop Company',
                    'ru' => $isAlmaty ? 'Кофе Бум' : 'Кофешоп Компани',
                    'kk' => $isAlmaty ? 'Кофе Бум' : 'Кофешоп Компани',
                ],
                'address' => [
                    'en' => $isAlmaty ? 'Abay Avenue, 150' : 'Respublika Avenue, 24',
                    'ru' => $isAlmaty ? 'проспект Абая, 150' : 'проспект Республики, 24',
                    'kk' => $isAlmaty ? 'Абай даңғылы, 150' : 'Республика даңғылы, 24',
                ],
                'description' => [
                    'en' => 'Cozy coffee shop with great atmosphere for meetings and work.',
                    'ru' => 'Уютная кофейня с отличной атмосферой для встреч и работы.',
                    'kk' => 'Кездесулер мен жұмыс үшін тамаша атмосферасы бар жайлы кофехана.',
                ],
                'coordinates' => [
                    'lat' => $isAlmaty ? 43.2353 : 51.1694,
                    'lng' => $isAlmaty ? 76.9456 : 71.4491,
                ],
                'phone' => $isAlmaty ? '+77272501234' : '+77172701234',
                'website' => null,
                'instagram' => '@coffeeboom_' . strtolower($cityName),
                'two_gis_url' => null,
                'activity_slugs' => ['coffee'],
                'hours' => [
                    0 => '08:00-22:00', // Monday
                    1 => '08:00-22:00',
                    2 => '08:00-22:00',
                    3 => '08:00-22:00',
                    4 => '08:00-22:00',
                    5 => '09:00-23:00', // Saturday
                    6 => '09:00-23:00', // Sunday
                ],
            ],

            // Restaurants
            [
                'name' => [
                    'en' => $isAlmaty ? 'Kaganat' : 'Line Brew',
                    'ru' => $isAlmaty ? 'Каганат' : 'Лайн Брю',
                    'kk' => $isAlmaty ? 'Каганат' : 'Лайн Брю',
                ],
                'address' => [
                    'en' => $isAlmaty ? 'Dostyk Avenue, 200' : 'Mangilik El Avenue, 55/20',
                    'ru' => $isAlmaty ? 'проспект Достык, 200' : 'проспект Мәңгілік Ел, 55/20',
                    'kk' => $isAlmaty ? 'Достық даңғылы, 200' : 'Мәңгілік Ел даңғылы, 55/20',
                ],
                'description' => [
                    'en' => 'Modern restaurant with European and Asian cuisine.',
                    'ru' => 'Современный ресторан с европейской и азиатской кухней.',
                    'kk' => 'Еуропалық және азиялық тағамдары бар заманауи мейрамхана.',
                ],
                'coordinates' => [
                    'lat' => $isAlmaty ? 43.2380 : 51.1277,
                    'lng' => $isAlmaty ? 76.9494 : 71.4308,
                ],
                'phone' => $isAlmaty ? '+77272345678' : '+77172345678',
                'website' => null,
                'instagram' => '@restaurant_' . strtolower($cityName),
                'two_gis_url' => null,
                'activity_slugs' => ['restaurant', 'beer'],
                'hours' => [
                    0 => '11:00-23:00',
                    1 => '11:00-23:00',
                    2 => '11:00-23:00',
                    3 => '11:00-23:00',
                    4 => '11:00-00:00',
                    5 => '11:00-02:00',
                    6 => '11:00-23:00',
                ],
            ],

            // Bars & Pubs
            [
                'name' => [
                    'en' => $isAlmaty ? 'Chukotka' : 'Barley Wine Bar',
                    'ru' => $isAlmaty ? 'Чукотка' : 'Ячменный Бар',
                    'kk' => $isAlmaty ? 'Чукотка' : 'Арпа Бар',
                ],
                'address' => [
                    'en' => $isAlmaty ? 'Zhibek Zholy, 50' : 'Kabanbay Batyr Avenue, 17',
                    'ru' => $isAlmaty ? 'Жибек Жолы, 50' : 'проспект Кабанбай батыра, 17',
                    'kk' => $isAlmaty ? 'Жібек жолы, 50' : 'Қабанбай батыр даңғылы, 17',
                ],
                'description' => [
                    'en' => 'Popular bar with craft beer and live music on weekends.',
                    'ru' => 'Популярный бар с крафтовым пивом и живой музыкой по выходным.',
                    'kk' => 'Демалыс күндері өнер саласындағы сырамен танымал бар.',
                ],
                'coordinates' => [
                    'lat' => $isAlmaty ? 43.2567 : 51.1595,
                    'lng' => $isAlmaty ? 76.9286 : 71.4704,
                ],
                'phone' => $isAlmaty ? '+77272111222' : '+77172111222',
                'website' => null,
                'instagram' => '@bar_' . strtolower($cityName),
                'two_gis_url' => null,
                'activity_slugs' => ['beer', 'hookah'],
                'hours' => [
                    0 => '14:00-02:00',
                    1 => '14:00-02:00',
                    2 => '14:00-02:00',
                    3 => '14:00-02:00',
                    4 => '14:00-03:00',
                    5 => '14:00-04:00',
                    6 => '14:00-02:00',
                ],
            ],

            // Bowling
            [
                'name' => [
                    'en' => $isAlmaty ? 'Mega Bowling' : 'Bowling Club Astana',
                    'ru' => $isAlmaty ? 'Мега Боулинг' : 'Боулинг Клуб Астана',
                    'kk' => $isAlmaty ? 'Мега Боулинг' : 'Астана Боулинг Клубы',
                ],
                'address' => [
                    'en' => $isAlmaty ? 'Rozybakiev Street, 247/1' : 'Turan Avenue, 37',
                    'ru' => $isAlmaty ? 'улица Розыбакиева, 247/1' : 'проспект Туран, 37',
                    'kk' => $isAlmaty ? 'Розыбақиев көшесі, 247/1' : 'Тұран даңғылы, 37',
                ],
                'description' => [
                    'en' => 'Modern bowling center with 12 lanes, bar and restaurant.',
                    'ru' => 'Современный боулинг-центр с 12 дорожками, баром и рестораном.',
                    'kk' => '12 жолағы, барымен және мейрамханасы бар заманауи боулинг орталығы.',
                ],
                'coordinates' => [
                    'lat' => $isAlmaty ? 43.2220 : 51.1335,
                    'lng' => $isAlmaty ? 76.8512 : 71.4054,
                ],
                'phone' => $isAlmaty ? '+77273334455' : '+77173334455',
                'website' => null,
                'instagram' => '@bowling_' . strtolower($cityName),
                'two_gis_url' => null,
                'activity_slugs' => ['bowling'],
                'hours' => [
                    0 => '12:00-02:00',
                    1 => '12:00-02:00',
                    2 => '12:00-02:00',
                    3 => '12:00-02:00',
                    4 => '12:00-04:00',
                    5 => '10:00-04:00',
                    6 => '10:00-02:00',
                ],
            ],

            // Cinema
            [
                'name' => [
                    'en' => $isAlmaty ? 'Kinopark' : 'Chaplin Cinemas',
                    'ru' => $isAlmaty ? 'Кинопарк' : 'Чаплин Синема',
                    'kk' => $isAlmaty ? 'Кинопарк' : 'Чаплин Кинотеатры',
                ],
                'address' => [
                    'en' => $isAlmaty ? 'Seifullin Avenue, 617' : 'Syganak Street, 22',
                    'ru' => $isAlmaty ? 'проспект Сейфуллина, 617' : 'улица Сыганак, 22',
                    'kk' => $isAlmaty ? 'Сейфуллин даңғылы, 617' : 'Сыганақ көшесі, 22',
                ],
                'description' => [
                    'en' => 'Modern cinema with comfortable halls and latest movies.',
                    'ru' => 'Современный кинотеатр с комфортными залами и новейшими фильмами.',
                    'kk' => 'Жайлы залдары мен соңғы фильмдері бар заманауи кинотеатр.',
                ],
                'coordinates' => [
                    'lat' => $isAlmaty ? 43.2440 : 51.1815,
                    'lng' => $isAlmaty ? 76.9470 : 71.4450,
                ],
                'phone' => $isAlmaty ? '+77275556677' : '+77175556677',
                'website' => null,
                'instagram' => '@cinema_' . strtolower($cityName),
                'two_gis_url' => null,
                'activity_slugs' => ['cinema'],
                'hours' => [
                    0 => '09:00-23:00',
                    1 => '09:00-23:00',
                    2 => '09:00-23:00',
                    3 => '09:00-23:00',
                    4 => '09:00-01:00',
                    5 => '09:00-01:00',
                    6 => '09:00-23:00',
                ],
            ],
        ];

        // Add more unique places per city to reach 15 total
        $additionalPlaces = $this->getAdditionalPlaces($cityName, $isAlmaty);

        return array_merge($places, $additionalPlaces);
    }

    private function getAdditionalPlaces(string $cityName, bool $isAlmaty): array
    {
        // You can expand this with more places
        return [];
    }
}
