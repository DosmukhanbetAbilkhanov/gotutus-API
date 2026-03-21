<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\HangoutRequestStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCities();
        $this->seedUserTypes();
        $this->seedActivityTypes();
        $this->seedPlaces();
        $this->seedPlaceDiscounts();
        $this->seedPlaceWorkingHours();
        $this->seedUsers();
        $this->seedHangoutRequests();
    }

    private function seedUserTypes(): void
    {
        $now = now();

        $types = [
            ['slug' => 'client', 'name' => 'Client'],
            ['slug' => 'admin', 'name' => 'Admin'],
            ['slug' => 'city_manager', 'name' => 'City Manager'],
        ];

        foreach ($types as $type) {
            DB::table('user_types')->insertOrIgnore([
                'slug' => $type['slug'],
                'name' => $type['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedCities(): void
    {
        $now = now();

        $cities = [
            ['en' => 'Almaty', 'ru' => 'Алматы', 'kz' => 'Алматы'],
            ['en' => 'Astana', 'ru' => 'Астана', 'kz' => 'Астана'],
            ['en' => 'Aktobe', 'ru' => 'Актобе', 'kz' => 'Ақтөбе'],
            ['en' => 'Shymkent', 'ru' => 'Шымкент', 'kz' => 'Шымкент'],
            ['en' => 'Karaganda', 'ru' => 'Караганда', 'kz' => 'Қарағанды'],
            ['en' => 'Aktau', 'ru' => 'Актау', 'kz' => 'Ақтау'],
            ['en' => 'Atyrau', 'ru' => 'Атырау', 'kz' => 'Атырау'],
            ['en' => 'Pavlodar', 'ru' => 'Павлодар', 'kz' => 'Павлодар'],
            ['en' => 'Semey', 'ru' => 'Семей', 'kz' => 'Семей'],
            ['en' => 'Kostanay', 'ru' => 'Костанай', 'kz' => 'Қостанай'],
            ['en' => 'Taraz', 'ru' => 'Тараз', 'kz' => 'Тараз'],
            ['en' => 'Uralsk', 'ru' => 'Уральск', 'kz' => 'Орал'],
            ['en' => 'Petropavlovsk', 'ru' => 'Петропавловск', 'kz' => 'Петропавл'],
            ['en' => 'Turkestan', 'ru' => 'Туркестан', 'kz' => 'Түркістан'],
            ['en' => 'Kokshetau', 'ru' => 'Кокшетау', 'kz' => 'Көкшетау'],
            ['en' => 'Taldykorgan', 'ru' => 'Талдыкорган', 'kz' => 'Талдықорған'],
            ['en' => 'Ekibastuz', 'ru' => 'Экибастуз', 'kz' => 'Екібастұз'],
            ['en' => 'Rudny', 'ru' => 'Рудный', 'kz' => 'Рудный'],
            ['en' => 'Temirtau', 'ru' => 'Темиртау', 'kz' => 'Теміртау'],
            ['en' => 'Zhezkazgan', 'ru' => 'Жезказган', 'kz' => 'Жезқазған'],
        ];

        foreach ($cities as $city) {
            $cityId = DB::table('cities')->insertGetId([
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('city_translations')->insert([
                ['city_id' => $cityId, 'language_code' => 'ru', 'name' => $city['ru']],
                ['city_id' => $cityId, 'language_code' => 'kz', 'name' => $city['kz']],
                ['city_id' => $cityId, 'language_code' => 'en', 'name' => $city['en']],
            ]);
        }
    }

    private function seedActivityTypes(): void
    {
        $now = now();

        $activityTypes = [
            ['slug' => 'beer', 'icon' => "\u{1F37A}", 'ru' => 'Пиво', 'kz' => 'Сыра', 'en' => 'Beer'],
            ['slug' => 'coffee', 'icon' => "\u{2615}", 'ru' => 'Кофе', 'kz' => 'Кофе', 'en' => 'Coffee'],
            ['slug' => 'sushi', 'icon' => "\u{2615}", 'ru' => ' Суши', 'kz' => ' Суши', 'en' => 'Sushi'],
            ['slug' => 'fast_food', 'icon' => "\u{2615}", 'ru' => 'Фаст-Фуд', 'kz' => 'Фаст-Фуд', 'en' => 'Fast Food'],
            ['slug' => 'bathhouse', 'icon' => "\u{1F9D6}", 'ru' => 'Баня', 'kz' => 'Монша', 'en' => 'Bathhouse'],
            ['slug' => 'walk', 'icon' => "\u{1F6B6}", 'ru' => 'Прогулка', 'kz' => 'Серуен', 'en' => 'Walk'],
            ['slug' => 'concert', 'icon' => "\u{1F6B6}", 'ru' => 'Концерт', 'kz' => 'Концерт', 'en' => 'Concert'],
            ['slug' => 'bowling', 'icon' => "\u{1F3B3}", 'ru' => 'Боулинг', 'kz' => 'Боулинг', 'en' => 'Bowling'],
            ['slug' => 'billiards', 'icon' => "\u{1F3B1}", 'ru' => 'Бильярд', 'kz' => 'Бильярд', 'en' => 'Billiards'],
            ['slug' => 'hookah', 'icon' => "\u{1F4A8}", 'ru' => 'Кальян', 'kz' => 'Кальян', 'en' => 'Hookah'],
            ['slug' => 'karaoke', 'icon' => "\u{1F3A4}", 'ru' => 'Караоке', 'kz' => 'Караоке', 'en' => 'Karaoke'],
            ['slug' => 'restaurant', 'icon' => "\u{1F37D}\u{FE0F}", 'ru' => 'Ресторан', 'kz' => 'Мейрамхана', 'en' => 'Restaurant'],
            ['slug' => 'kvest', 'icon' => "\u{26BD}", 'ru' => 'Квест', 'kz' => 'Квест', 'en' => 'Kvest'],
            ['slug' => 'paintball', 'icon' => "\u{26BD}", 'ru' => 'Пейнтбол', 'kz' => 'Пейнтбол', 'en' => 'Paintball'],
            ['slug' => 'pc_club', 'icon' => "\u{26BD}", 'ru' => 'Компьютерный Клуб', 'kz' => 'Компьютерный Клуб', 'en' => 'PC Club'],
            ['slug' => 'quiz', 'icon' => "\u{26BD}", 'ru' => 'Квиз', 'kz' => 'Квиз', 'en' => 'Quiz'],
            ['slug' => 'tennis', 'icon' => "\u{26BD}", 'ru' => 'Теннис', 'kz' => 'Теннис', 'en' => 'Tennis'],
            ['slug' => 'football', 'icon' => "\u{26BD}", 'ru' => 'Футбол', 'kz' => 'Футбол', 'en' => 'Football'],
            ['slug' => 'cinema', 'icon' => "\u{1F3AC}", 'ru' => 'Кино', 'kz' => 'Кино', 'en' => 'Cinema'],
            ['slug' => 'board-games', 'icon' => "\u{1F3B2}", 'ru' => 'Настолки', 'kz' => 'Үстел ойындары', 'en' => 'Board Games'],
        ];

        foreach ($activityTypes as $type) {
            $typeId = DB::table('activity_types')->insertGetId([
                'slug' => $type['slug'],
                'bg_photo' => null,
                'icon' => $type['icon'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('activity_type_translations')->insert([
                ['activity_type_id' => $typeId, 'language_code' => 'ru', 'name' => $type['ru']],
                ['activity_type_id' => $typeId, 'language_code' => 'kz', 'name' => $type['kz']],
                ['activity_type_id' => $typeId, 'language_code' => 'en', 'name' => $type['en']],
            ]);
        }
    }

    private function seedPlaces(): void
    {
        $now = now();

        $activitySlugs = DB::table('activity_types')->pluck('id', 'slug');
        $cityNames = DB::table('city_translations')
            ->where('language_code', 'en')
            ->pluck('city_id', 'name');

        $almatyId = $cityNames['Almaty'];
        $astanaId = $cityNames['Astana'];
        $aktobeId = $cityNames['Aktobe'];
        $shymkentId = $cityNames['Shymkent'];
        $karagandaId = $cityNames['Karaganda'];
        $aktauId = $cityNames['Aktau'];
        $atyrauId = $cityNames['Atyrau'];
        $pavlodarId = $cityNames['Pavlodar'];
        $semeyId = $cityNames['Semey'];
        $kostanayId = $cityNames['Kostanay'];
        $tarazId = $cityNames['Taraz'];
        $uralskId = $cityNames['Uralsk'];
        $petropavlovskId = $cityNames['Petropavlovsk'];
        $turkestanId = $cityNames['Turkestan'];
        $kokshetauId = $cityNames['Kokshetau'];
        $taldykorganId = $cityNames['Taldykorgan'];
        $ekibastuzId = $cityNames['Ekibastuz'];
        $rudnyId = $cityNames['Rudny'];
        $temirtauId = $cityNames['Temirtau'];
        $zhezkazganId = $cityNames['Zhezkazgan'];

        $beerId = $activitySlugs['beer'];
        $coffeeId = $activitySlugs['coffee'];
        $sushiId = $activitySlugs['sushi'];
        $fastFoodId = $activitySlugs['fast_food'];
        $bathhouseId = $activitySlugs['bathhouse'];
        $walkId = $activitySlugs['walk'];
        $concertId = $activitySlugs['concert'];
        $bowlingId = $activitySlugs['bowling'];
        $billiardsId = $activitySlugs['billiards'];
        $hookahId = $activitySlugs['hookah'];
        $karaokeId = $activitySlugs['karaoke'];
        $restaurantId = $activitySlugs['restaurant'];
        $kvestId = $activitySlugs['kvest'];
        $paintballId = $activitySlugs['paintball'];
        $pcClubId = $activitySlugs['pc_club'];
        $quizId = $activitySlugs['quiz'];
        $tennisId = $activitySlugs['tennis'];
        $footballId = $activitySlugs['football'];
        $cinemaId = $activitySlugs['cinema'];
        $boardGamesId = $activitySlugs['board-games'];

        // =====================================================================
        // ALMATY PLACES
        // =====================================================================
        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Бочонок', 'address' => 'ул. Жибек Жолы, 50'],
            'kz' => ['name' => 'Бочонок', 'address' => 'Жібек Жолы көш., 50'],
            'en' => ['name' => 'Bochonok', 'address' => '50 Zhibek Zholy St.'],
        ], [$beerId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Guinness Pub', 'address' => 'ул. Панфилова, 100'],
            'kz' => ['name' => 'Guinness Pub', 'address' => 'Панфилов көш., 100'],
            'en' => ['name' => 'Guinness Pub', 'address' => '100 Panfilov St.'],
        ], [$beerId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Пивная №1', 'address' => 'пр. Абая, 68'],
            'kz' => ['name' => 'Пивная №1', 'address' => 'Абай даң., 68'],
            'en' => ['name' => 'Pivnaya No. 1', 'address' => '68 Abay Ave.'],
        ], [$beerId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'The Beerhouse', 'address' => 'ул. Кабанбай Батыра, 115'],
            'kz' => ['name' => 'The Beerhouse', 'address' => 'Қабанбай Батыр көш., 115'],
            'en' => ['name' => 'The Beerhouse', 'address' => '115 Kabanbay Batyr St.'],
        ], [$beerId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Coffee Boom', 'address' => 'пр. Достык, 36'],
            'kz' => ['name' => 'Coffee Boom', 'address' => 'Достық даң., 36'],
            'en' => ['name' => 'Coffee Boom', 'address' => '36 Dostyk Ave.'],
        ], [$coffeeId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Starbucks', 'address' => 'пр. Аль-Фараби, 77/8'],
            'kz' => ['name' => 'Starbucks', 'address' => 'Әл-Фараби даң., 77/8'],
            'en' => ['name' => 'Starbucks', 'address' => '77/8 Al-Farabi Ave.'],
        ], [$coffeeId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Рахат', 'address' => 'ул. Гоголя, 2'],
            'kz' => ['name' => 'Рахат', 'address' => 'Гоголь көш., 2'],
            'en' => ['name' => 'Rakhat', 'address' => '2 Gogol St.'],
        ], [$coffeeId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Кофейня Даблби', 'address' => 'ул. Тулебаева, 38'],
            'kz' => ['name' => 'Даблби кофеханасы', 'address' => 'Төлебаев көш., 38'],
            'en' => ['name' => 'Dablbi Coffee', 'address' => '38 Tulebaev St.'],
        ], [$coffeeId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Арасан', 'address' => 'ул. Тулебаева, 78'],
            'kz' => ['name' => 'Арасан', 'address' => 'Төлебаев көш., 78'],
            'en' => ['name' => 'Arasan Baths', 'address' => '78 Tulebaev St.'],
        ], [$bathhouseId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Баня на Навои', 'address' => 'ул. Навои, 120'],
            'kz' => ['name' => 'Науаи моншағы', 'address' => 'Науаи көш., 120'],
            'en' => ['name' => 'Navoi Bathhouse', 'address' => '120 Navoi St.'],
        ], [$bathhouseId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Сандуны Алматы', 'address' => 'ул. Розыбакиева, 247А'],
            'kz' => ['name' => 'Сандуны Алматы', 'address' => 'Розыбақиев көш., 247А'],
            'en' => ['name' => 'Sanduny Almaty', 'address' => '247A Rozybakiev St.'],
        ], [$bathhouseId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Центральный парк', 'address' => 'ул. Гоголя, 1'],
            'kz' => ['name' => 'Орталық саябақ', 'address' => 'Гоголь көш., 1'],
            'en' => ['name' => 'Central Park', 'address' => '1 Gogol St.'],
        ], [$walkId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Парк 28 Панфиловцев', 'address' => 'ул. Гоголя / ул. Зенкова'],
            'kz' => ['name' => '28 Панфилов саябағы', 'address' => 'Гоголь көш. / Зенков көш.'],
            'en' => ['name' => '28 Panfilov Park', 'address' => 'Gogol St. / Zenkov St.'],
        ], [$walkId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Кок-Тобе', 'address' => 'гора Кок-Тобе'],
            'kz' => ['name' => 'Көк-Төбе', 'address' => 'Көк-Төбе тауы'],
            'en' => ['name' => 'Kok-Tobe', 'address' => 'Kok-Tobe Hill'],
        ], [$walkId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Медеу', 'address' => 'урочище Медеу'],
            'kz' => ['name' => 'Медеу', 'address' => 'Медеу шатқалы'],
            'en' => ['name' => 'Medeu', 'address' => 'Medeu Gorge'],
        ], [$walkId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Mega Bowling', 'address' => 'пр. Розыбакиева, 263'],
            'kz' => ['name' => 'Mega Bowling', 'address' => 'Розыбақиев даң., 263'],
            'en' => ['name' => 'Mega Bowling', 'address' => '263 Rozybakiev Ave.'],
        ], [$bowlingId, $billiardsId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Страйк Боулинг', 'address' => 'ул. Жандосова, 98'],
            'kz' => ['name' => 'Страйк Боулинг', 'address' => 'Жандосов көш., 98'],
            'en' => ['name' => 'Strike Bowling', 'address' => '98 Zhandosov St.'],
        ], [$bowlingId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Бильярдный клуб Корона', 'address' => 'ул. Сатпаева, 90/21'],
            'kz' => ['name' => 'Корона бильярд клубы', 'address' => 'Сәтбаев көш., 90/21'],
            'en' => ['name' => 'Korona Billiard Club', 'address' => '90/21 Satpayev St.'],
        ], [$billiardsId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Маракеш Lounge', 'address' => 'ул. Шевченко, 85'],
            'kz' => ['name' => 'Маракеш Lounge', 'address' => 'Шевченко көш., 85'],
            'en' => ['name' => 'Marrakesh Lounge', 'address' => '85 Shevchenko St.'],
        ], [$hookahId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Облако Lounge', 'address' => 'пр. Достык, 128'],
            'kz' => ['name' => 'Облако Lounge', 'address' => 'Достық даң., 128'],
            'en' => ['name' => 'Oblako Lounge', 'address' => '128 Dostyk Ave.'],
        ], [$hookahId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Do Star Karaoke', 'address' => 'ул. Курмангазы, 61Б'],
            'kz' => ['name' => 'Do Star Karaoke', 'address' => 'Құрманғазы көш., 61Б'],
            'en' => ['name' => 'Do Star Karaoke', 'address' => '61B Kurmangazy St.'],
        ], [$karaokeId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Voice Karaoke', 'address' => 'ул. Маметовой, 54'],
            'kz' => ['name' => 'Voice Karaoke', 'address' => 'Мәметова көш., 54'],
            'en' => ['name' => 'Voice Karaoke', 'address' => '54 Mametova St.'],
        ], [$karaokeId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Гости', 'address' => 'ул. Панфилова, 110'],
            'kz' => ['name' => 'Гости', 'address' => 'Панфилов көш., 110'],
            'en' => ['name' => 'Gosti Restaurant', 'address' => '110 Panfilov St.'],
        ], [$restaurantId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Алаша', 'address' => 'ул. Оспанова, 60'],
            'kz' => ['name' => 'Алаша', 'address' => 'Оспанов көш., 60'],
            'en' => ['name' => 'Alasha', 'address' => '60 Ospanov St.'],
        ], [$restaurantId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Дель Папа', 'address' => 'пр. Достык, 85'],
            'kz' => ['name' => 'Дель Папа', 'address' => 'Достық даң., 85'],
            'en' => ['name' => 'Del Papa', 'address' => '85 Dostyk Ave.'],
        ], [$restaurantId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Kinopark 11 IMAX', 'address' => 'пр. Абая, 109'],
            'kz' => ['name' => 'Kinopark 11 IMAX', 'address' => 'Абай даң., 109'],
            'en' => ['name' => 'Kinopark 11 IMAX', 'address' => '109 Abay Ave.'],
        ], [$cinemaId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Chaplin Cinemas', 'address' => 'пр. Достык, 111'],
            'kz' => ['name' => 'Chaplin Cinemas', 'address' => 'Достық даң., 111'],
            'en' => ['name' => 'Chaplin Cinemas', 'address' => '111 Dostyk Ave.'],
        ], [$cinemaId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Hobby Games Almaty', 'address' => 'ул. Толе Би, 59'],
            'kz' => ['name' => 'Hobby Games Almaty', 'address' => 'Төле Би көш., 59'],
            'en' => ['name' => 'Hobby Games Almaty', 'address' => '59 Tole Bi St.'],
        ], [$boardGamesId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Мосигра', 'address' => 'ул. Жибек Жолы, 135'],
            'kz' => ['name' => 'Мосигра', 'address' => 'Жібек Жолы көш., 135'],
            'en' => ['name' => 'Mosigra', 'address' => '135 Zhibek Zholy St.'],
        ], [$boardGamesId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Теннисный центр Достык', 'address' => 'пр. Достык, 104'],
            'kz' => ['name' => 'Достық теннис орталығы', 'address' => 'Достық даң., 104'],
            'en' => ['name' => 'Dostyk Tennis Center', 'address' => '104 Dostyk Ave.'],
        ], [$tennisId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Almaty Tennis Club', 'address' => 'ул. Сатпаева, 29А'],
            'kz' => ['name' => 'Almaty Tennis Club', 'address' => 'Сәтбаев көш., 29А'],
            'en' => ['name' => 'Almaty Tennis Club', 'address' => '29A Satpayev St.'],
        ], [$tennisId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Центральный стадион Алматы', 'address' => 'ул. Сатпаева, 29/3'],
            'kz' => ['name' => 'Алматы орталық стадионы', 'address' => 'Сәтбаев көш., 29/3'],
            'en' => ['name' => 'Almaty Central Stadium', 'address' => '29/3 Satpayev St.'],
        ], [$footballId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Футбольное поле Goal', 'address' => 'ул. Жандосова, 60'],
            'kz' => ['name' => 'Goal футбол алаңы', 'address' => 'Жандосов көш., 60'],
            'en' => ['name' => 'Goal Football Field', 'address' => '60 Zhandosov St.'],
        ], [$footballId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Sushi Master', 'address' => 'пр. Достык, 44'],
            'kz' => ['name' => 'Sushi Master', 'address' => 'Достық даң., 44'],
            'en' => ['name' => 'Sushi Master', 'address' => '44 Dostyk Ave.'],
        ], [$sushiId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Сакура Суши', 'address' => 'ул. Панфилова, 80'],
            'kz' => ['name' => 'Сакура Суши', 'address' => 'Панфилов көш., 80'],
            'en' => ['name' => 'Sakura Sushi', 'address' => '80 Panfilov St.'],
        ], [$sushiId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'KFC', 'address' => 'пр. Абая, 44/1'],
            'kz' => ['name' => 'KFC', 'address' => 'Абай даң., 44/1'],
            'en' => ['name' => 'KFC', 'address' => '44/1 Abay Ave.'],
        ], [$fastFoodId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Burger King', 'address' => 'пр. Достык, 50'],
            'kz' => ['name' => 'Burger King', 'address' => 'Достық даң., 50'],
            'en' => ['name' => 'Burger King', 'address' => '50 Dostyk Ave.'],
        ], [$fastFoodId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Дворец Республики', 'address' => 'пр. Достык, 56'],
            'kz' => ['name' => 'Республика сарайы', 'address' => 'Достық даң., 56'],
            'en' => ['name' => 'Republic Palace', 'address' => '56 Dostyk Ave.'],
        ], [$concertId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Алматы Арена', 'address' => 'пр. Аль-Фараби, 50'],
            'kz' => ['name' => 'Алматы Арена', 'address' => 'Әл-Фараби даң., 50'],
            'en' => ['name' => 'Almaty Arena', 'address' => '50 Al-Farabi Ave.'],
        ], [$concertId, $footballId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Квест-рум Escape', 'address' => 'ул. Курмангазы, 100'],
            'kz' => ['name' => 'Escape квест бөлмесі', 'address' => 'Құрманғазы көш., 100'],
            'en' => ['name' => 'Escape Quest Room', 'address' => '100 Kurmangazy St.'],
        ], [$kvestId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Пейнтбол Арена Алматы', 'address' => 'ул. Рыскулова, 200'],
            'kz' => ['name' => 'Алматы пейнтбол аренасы', 'address' => 'Рысқұлов көш., 200'],
            'en' => ['name' => 'Paintball Arena Almaty', 'address' => '200 Ryskulov St.'],
        ], [$paintballId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'CyberArena', 'address' => 'ул. Жандосова, 42'],
            'kz' => ['name' => 'CyberArena', 'address' => 'Жандосов көш., 42'],
            'en' => ['name' => 'CyberArena', 'address' => '42 Zhandosov St.'],
        ], [$pcClubId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'GameZone PC Club', 'address' => 'ул. Толе Би, 85'],
            'kz' => ['name' => 'GameZone PC Club', 'address' => 'Төле Би көш., 85'],
            'en' => ['name' => 'GameZone PC Club', 'address' => '85 Tole Bi St.'],
        ], [$pcClubId]);

        $this->createPlace($almatyId, $now, [
            'ru' => ['name' => 'Brain Quest Almaty', 'address' => 'ул. Шевченко, 45'],
            'kz' => ['name' => 'Brain Quest Almaty', 'address' => 'Шевченко көш., 45'],
            'en' => ['name' => 'Brain Quest Almaty', 'address' => '45 Shevchenko St.'],
        ], [$quizId]);

        // =====================================================================
        // ASTANA PLACES
        // =====================================================================
        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Пивной Дворик', 'address' => 'ул. Кенесары, 40'],
            'kz' => ['name' => 'Пивной Дворик', 'address' => 'Кенесары көш., 40'],
            'en' => ['name' => 'Pivnoy Dvorik', 'address' => '40 Kenesary St.'],
        ], [$beerId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Paulaner Bräuhaus', 'address' => 'пр. Кабанбай Батыра, 21'],
            'kz' => ['name' => 'Paulaner Bräuhaus', 'address' => 'Қабанбай Батыр даң., 21'],
            'en' => ['name' => 'Paulaner Bräuhaus', 'address' => '21 Kabanbay Batyr Ave.'],
        ], [$beerId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Craft Beer Pub', 'address' => 'ул. Сарайшык, 15'],
            'kz' => ['name' => 'Craft Beer Pub', 'address' => 'Сарайшық көш., 15'],
            'en' => ['name' => 'Craft Beer Pub', 'address' => '15 Saraishyk St.'],
        ], [$beerId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Шоколадница', 'address' => 'ул. Достык, 5/1'],
            'kz' => ['name' => 'Шоколадница', 'address' => 'Достық көш., 5/1'],
            'en' => ['name' => 'Shokoladnitsa', 'address' => '5/1 Dostyk St.'],
        ], [$coffeeId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Starbucks Хан Шатыр', 'address' => 'пр. Туран, 37'],
            'kz' => ['name' => 'Starbucks Хан Шатыр', 'address' => 'Тұран даң., 37'],
            'en' => ['name' => 'Starbucks Khan Shatyr', 'address' => '37 Turan Ave.'],
        ], [$coffeeId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Кофейня Brasilero', 'address' => 'ул. Кенесары, 52'],
            'kz' => ['name' => 'Brasilero кофеханасы', 'address' => 'Кенесары көш., 52'],
            'en' => ['name' => 'Brasilero Coffee', 'address' => '52 Kenesary St.'],
        ], [$coffeeId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Термальный комплекс Баня №1', 'address' => 'ул. Сыганак, 60'],
            'kz' => ['name' => 'Баня №1 термалды кешені', 'address' => 'Сығанақ көш., 60'],
            'en' => ['name' => 'Banya No. 1 Thermal Complex', 'address' => '60 Syganak St.'],
        ], [$bathhouseId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Русская баня Астана', 'address' => 'ул. Акмешит, 12'],
            'kz' => ['name' => 'Орыс моншағы Астана', 'address' => 'Ақмешіт көш., 12'],
            'en' => ['name' => 'Russian Banya Astana', 'address' => '12 Akmeshit St.'],
        ], [$bathhouseId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Набережная Есиля', 'address' => 'набережная реки Есиль'],
            'kz' => ['name' => 'Есіл жағалауы', 'address' => 'Есіл өзені жағалауы'],
            'en' => ['name' => 'Yesil River Embankment', 'address' => 'Yesil River Embankment'],
        ], [$walkId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Парк Любви', 'address' => 'ул. Сарайшык / ул. Кунаева'],
            'kz' => ['name' => 'Махаббат саябағы', 'address' => 'Сарайшық көш. / Қонаев көш.'],
            'en' => ['name' => 'Park of Love', 'address' => 'Saraishyk St. / Kunayev St.'],
        ], [$walkId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Байтерек', 'address' => 'пр. Нұржол, 14'],
            'kz' => ['name' => 'Бәйтерек', 'address' => 'Нұржол даң., 14'],
            'en' => ['name' => 'Bayterek Tower', 'address' => '14 Nurzhol Ave.'],
        ], [$walkId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Bowling City', 'address' => 'пр. Кабанбай Батыра, 21'],
            'kz' => ['name' => 'Bowling City', 'address' => 'Қабанбай Батыр даң., 21'],
            'en' => ['name' => 'Bowling City', 'address' => '21 Kabanbay Batyr Ave.'],
        ], [$bowlingId, $billiardsId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Brunswick Боулинг', 'address' => 'пр. Республики, 2'],
            'kz' => ['name' => 'Brunswick Боулинг', 'address' => 'Республика даң., 2'],
            'en' => ['name' => 'Brunswick Bowling', 'address' => '2 Republic Ave.'],
        ], [$bowlingId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Hookah Place', 'address' => 'ул. Мангилик Ел, 28'],
            'kz' => ['name' => 'Hookah Place', 'address' => 'Мәңгілік Ел көш., 28'],
            'en' => ['name' => 'Hookah Place', 'address' => '28 Mangilik El St.'],
        ], [$hookahId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Мята Lounge Астана', 'address' => 'ул. Сарайшык, 34'],
            'kz' => ['name' => 'Мята Lounge Астана', 'address' => 'Сарайшық көш., 34'],
            'en' => ['name' => 'Myata Lounge Astana', 'address' => '34 Saraishyk St.'],
        ], [$hookahId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Astana Karaoke', 'address' => 'ул. Достык, 13'],
            'kz' => ['name' => 'Astana Karaoke', 'address' => 'Достық көш., 13'],
            'en' => ['name' => 'Astana Karaoke', 'address' => '13 Dostyk St.'],
        ], [$karaokeId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'SingStar Karaoke', 'address' => 'ул. Кенесары, 70'],
            'kz' => ['name' => 'SingStar Karaoke', 'address' => 'Кенесары көш., 70'],
            'en' => ['name' => 'SingStar Karaoke', 'address' => '70 Kenesary St.'],
        ], [$karaokeId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Ресторан Turandot', 'address' => 'пр. Кабанбай Батыра, 15/1'],
            'kz' => ['name' => 'Turandot мейрамханасы', 'address' => 'Қабанбай Батыр даң., 15/1'],
            'en' => ['name' => 'Turandot Restaurant', 'address' => '15/1 Kabanbay Batyr Ave.'],
        ], [$restaurantId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Барсук', 'address' => 'ул. Сыганак, 54'],
            'kz' => ['name' => 'Барсук', 'address' => 'Сығанақ көш., 54'],
            'en' => ['name' => 'Barsuk', 'address' => '54 Syganak St.'],
        ], [$restaurantId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Линкер', 'address' => 'ул. Мангилик Ел, 48'],
            'kz' => ['name' => 'Линкер', 'address' => 'Мәңгілік Ел көш., 48'],
            'en' => ['name' => 'Linker', 'address' => '48 Mangilik El St.'],
        ], [$restaurantId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Астана Арена', 'address' => 'пр. Туран, 57'],
            'kz' => ['name' => 'Астана Арена', 'address' => 'Тұран даң., 57'],
            'en' => ['name' => 'Astana Arena', 'address' => '57 Turan Ave.'],
        ], [$footballId, $concertId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Теннисный центр Астана', 'address' => 'ул. Сыганак, 18'],
            'kz' => ['name' => 'Астана теннис орталығы', 'address' => 'Сығанақ көш., 18'],
            'en' => ['name' => 'Astana Tennis Center', 'address' => '18 Syganak St.'],
        ], [$tennisId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Футбольный манеж Астана', 'address' => 'ул. Мангилик Ел, 54'],
            'kz' => ['name' => 'Астана футбол манежі', 'address' => 'Мәңгілік Ел көш., 54'],
            'en' => ['name' => 'Astana Football Arena', 'address' => '54 Mangilik El St.'],
        ], [$footballId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Sushi Bar Tanuki', 'address' => 'ул. Достык, 9'],
            'kz' => ['name' => 'Sushi Bar Tanuki', 'address' => 'Достық көш., 9'],
            'en' => ['name' => 'Sushi Bar Tanuki', 'address' => '9 Dostyk St.'],
        ], [$sushiId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'KFC Хан Шатыр', 'address' => 'пр. Туран, 37'],
            'kz' => ['name' => 'KFC Хан Шатыр', 'address' => 'Тұран даң., 37'],
            'en' => ['name' => 'KFC Khan Shatyr', 'address' => '37 Turan Ave.'],
        ], [$fastFoodId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Барыс Арена', 'address' => 'пр. Туран, 55'],
            'kz' => ['name' => 'Барыс Арена', 'address' => 'Тұран даң., 55'],
            'en' => ['name' => 'Barys Arena', 'address' => '55 Turan Ave.'],
        ], [$concertId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Квест-рум Lost', 'address' => 'ул. Сарайшык, 40'],
            'kz' => ['name' => 'Lost квест бөлмесі', 'address' => 'Сарайшық көш., 40'],
            'en' => ['name' => 'Lost Quest Room', 'address' => '40 Saraishyk St.'],
        ], [$kvestId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Пейнтбол Астана', 'address' => 'ул. Кабанбай Батыра, 80'],
            'kz' => ['name' => 'Астана пейнтболы', 'address' => 'Қабанбай Батыр көш., 80'],
            'en' => ['name' => 'Paintball Astana', 'address' => '80 Kabanbay Batyr St.'],
        ], [$paintballId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'CyberX PC Club', 'address' => 'ул. Кенесары, 80'],
            'kz' => ['name' => 'CyberX PC Club', 'address' => 'Кенесары көш., 80'],
            'en' => ['name' => 'CyberX PC Club', 'address' => '80 Kenesary St.'],
        ], [$pcClubId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Quiz Night Astana', 'address' => 'ул. Достык, 20'],
            'kz' => ['name' => 'Quiz Night Astana', 'address' => 'Достық көш., 20'],
            'en' => ['name' => 'Quiz Night Astana', 'address' => '20 Dostyk St.'],
        ], [$quizId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Kinopark 7 Keruencity', 'address' => 'пр. Кабанбай Батыра, 21'],
            'kz' => ['name' => 'Kinopark 7 Keruencity', 'address' => 'Қабанбай Батыр даң., 21'],
            'en' => ['name' => 'Kinopark 7 Keruencity', 'address' => '21 Kabanbay Batyr Ave.'],
        ], [$cinemaId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Chaplin Cinemas Astana', 'address' => 'пр. Туран, 24'],
            'kz' => ['name' => 'Chaplin Cinemas Astana', 'address' => 'Тұран даң., 24'],
            'en' => ['name' => 'Chaplin Cinemas Astana', 'address' => '24 Turan Ave.'],
        ], [$cinemaId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Игротека Астана', 'address' => 'ул. Кенесары, 48'],
            'kz' => ['name' => 'Игротека Астана', 'address' => 'Кенесары көш., 48'],
            'en' => ['name' => 'Igroteka Astana', 'address' => '48 Kenesary St.'],
        ], [$boardGamesId]);

        $this->createPlace($astanaId, $now, [
            'ru' => ['name' => 'Бильярдный клуб Триумф', 'address' => 'ул. Иманова, 19'],
            'kz' => ['name' => 'Триумф бильярд клубы', 'address' => 'Иманов көш., 19'],
            'en' => ['name' => 'Triumph Billiard Club', 'address' => '19 Imanov St.'],
        ], [$billiardsId]);

        // =====================================================================
        // AKTOBE PLACES
        // =====================================================================
        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Beer Garden', 'address' => 'пр. Абилкайыр хана, 64'],
            'kz' => ['name' => 'Beer Garden', 'address' => 'Абылқайыр хан даң., 64'],
            'en' => ['name' => 'Beer Garden', 'address' => '64 Abilkaiyr Khan Ave.'],
        ], [$beerId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Хмельной Двор', 'address' => 'ул. Маресьева, 88'],
            'kz' => ['name' => 'Хмельной Двор', 'address' => 'Маресьев көш., 88'],
            'en' => ['name' => 'Khmelnoy Dvor', 'address' => '88 Maresyev St.'],
        ], [$beerId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Coffee House Актобе', 'address' => 'ул. Санкибай Батыра, 17'],
            'kz' => ['name' => 'Coffee House Ақтөбе', 'address' => 'Сәнкібай Батыр көш., 17'],
            'en' => ['name' => 'Coffee House Aktobe', 'address' => '17 Sankibay Batyr St.'],
        ], [$coffeeId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Кофемания', 'address' => 'ул. Алтынсарина, 22'],
            'kz' => ['name' => 'Кофемания', 'address' => 'Алтынсарин көш., 22'],
            'en' => ['name' => 'Coffemania', 'address' => '22 Altynsarin St.'],
        ], [$coffeeId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Баня Люкс', 'address' => 'ул. Есет Батыра, 105'],
            'kz' => ['name' => 'Люкс моншағы', 'address' => 'Есет Батыр көш., 105'],
            'en' => ['name' => 'Lux Bathhouse', 'address' => '105 Eset Batyr St.'],
        ], [$bathhouseId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Парковая аллея', 'address' => 'Парк им. Первого Президента'],
            'kz' => ['name' => 'Саябақ аллеясы', 'address' => 'Тұңғыш Президент саябағы'],
            'en' => ['name' => 'Park Alley', 'address' => 'First President Park'],
        ], [$walkId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Набережная реки Илек', 'address' => 'набережная р. Илек'],
            'kz' => ['name' => 'Ілек өзені жағалауы', 'address' => 'Ілек өзені жағалауы'],
            'en' => ['name' => 'Ilek River Embankment', 'address' => 'Ilek River Embankment'],
        ], [$walkId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Боулинг Сити Актобе', 'address' => 'пр. Абилкайыр хана, 44'],
            'kz' => ['name' => 'Боулинг Сити Ақтөбе', 'address' => 'Абылқайыр хан даң., 44'],
            'en' => ['name' => 'Bowling City Aktobe', 'address' => '44 Abilkaiyr Khan Ave.'],
        ], [$bowlingId, $billiardsId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Дымок Lounge', 'address' => 'ул. Маресьева, 70'],
            'kz' => ['name' => 'Дымок Lounge', 'address' => 'Маресьев көш., 70'],
            'en' => ['name' => 'Dymok Lounge', 'address' => '70 Maresyev St.'],
        ], [$hookahId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Star Karaoke Актобе', 'address' => 'ул. Санкибай Батыра, 50'],
            'kz' => ['name' => 'Star Karaoke Ақтөбе', 'address' => 'Сәнкібай Батыр көш., 50'],
            'en' => ['name' => 'Star Karaoke Aktobe', 'address' => '50 Sankibay Batyr St.'],
        ], [$karaokeId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Ресторан Нұр', 'address' => 'пр. Абилкайыр хана, 32'],
            'kz' => ['name' => 'Нұр мейрамханасы', 'address' => 'Абылқайыр хан даң., 32'],
            'en' => ['name' => 'Nur Restaurant', 'address' => '32 Abilkaiyr Khan Ave.'],
        ], [$restaurantId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Ресторан Ақ Орда', 'address' => 'ул. Алтынсарина, 4'],
            'kz' => ['name' => 'Ақ Орда мейрамханасы', 'address' => 'Алтынсарин көш., 4'],
            'en' => ['name' => 'Ak Orda Restaurant', 'address' => '4 Altynsarin St.'],
        ], [$restaurantId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Спорткомплекс Актобе', 'address' => 'ул. Есет Батыра, 56'],
            'kz' => ['name' => 'Ақтөбе спорт кешені', 'address' => 'Есет Батыр көш., 56'],
            'en' => ['name' => 'Aktobe Sports Complex', 'address' => '56 Eset Batyr St.'],
        ], [$footballId, $tennisId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'Кинотеатр Актобе', 'address' => 'пр. Абилкайыр хана, 72'],
            'kz' => ['name' => 'Ақтөбе кинотеатры', 'address' => 'Абылқайыр хан даң., 72'],
            'en' => ['name' => 'Aktobe Cinema', 'address' => '72 Abilkaiyr Khan Ave.'],
        ], [$cinemaId]);

        $this->createPlace($aktobeId, $now, [
            'ru' => ['name' => 'GameZone Актобе', 'address' => 'ул. Маресьева, 55'],
            'kz' => ['name' => 'GameZone Ақтөбе', 'address' => 'Маресьев көш., 55'],
            'en' => ['name' => 'GameZone Aktobe', 'address' => '55 Maresyev St.'],
        ], [$boardGamesId]);

        // =====================================================================
        // SHYMKENT PLACES
        // =====================================================================
        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Пивоварня Шымкент', 'address' => 'ул. Тауке хана, 45'],
            'kz' => ['name' => 'Шымкент сыра қайнатқышы', 'address' => 'Тәуке хан көш., 45'],
            'en' => ['name' => 'Shymkent Brewery', 'address' => '45 Tauke Khan St.'],
        ], [$beerId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Draft Pub', 'address' => 'пр. Республики, 10'],
            'kz' => ['name' => 'Draft Pub', 'address' => 'Республика даң., 10'],
            'en' => ['name' => 'Draft Pub', 'address' => '10 Republic Ave.'],
        ], [$beerId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Coffee Art', 'address' => 'ул. Байтурсынова, 12'],
            'kz' => ['name' => 'Coffee Art', 'address' => 'Байтұрсынов көш., 12'],
            'en' => ['name' => 'Coffee Art', 'address' => '12 Baitursynov St.'],
        ], [$coffeeId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Кофейня Мокко', 'address' => 'ул. Казыбек Би, 28'],
            'kz' => ['name' => 'Мокко кофеханасы', 'address' => 'Қазыбек Би көш., 28'],
            'en' => ['name' => 'Mokko Coffee', 'address' => '28 Kazybek Bi St.'],
        ], [$coffeeId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Шымкент Баня', 'address' => 'ул. Жангельдина, 76'],
            'kz' => ['name' => 'Шымкент моншағы', 'address' => 'Жангелдин көш., 76'],
            'en' => ['name' => 'Shymkent Bathhouse', 'address' => '76 Zhangeldin St.'],
        ], [$bathhouseId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Дендропарк', 'address' => 'Дендропарк, центр города'],
            'kz' => ['name' => 'Дендросаябақ', 'address' => 'Дендросаябақ, қала орталығы'],
            'en' => ['name' => 'Dendropark', 'address' => 'Dendropark, City Center'],
        ], [$walkId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Площадь Ордабасы', 'address' => 'площадь Ордабасы'],
            'kz' => ['name' => 'Ордабасы алаңы', 'address' => 'Ордабасы алаңы'],
            'en' => ['name' => 'Ordabasy Square', 'address' => 'Ordabasy Square'],
        ], [$walkId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'FunCity Боулинг', 'address' => 'пр. Республики, 38'],
            'kz' => ['name' => 'FunCity Боулинг', 'address' => 'Республика даң., 38'],
            'en' => ['name' => 'FunCity Bowling', 'address' => '38 Republic Ave.'],
        ], [$bowlingId, $billiardsId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Smoke Lab Lounge', 'address' => 'ул. Тауке хана, 22'],
            'kz' => ['name' => 'Smoke Lab Lounge', 'address' => 'Тәуке хан көш., 22'],
            'en' => ['name' => 'Smoke Lab Lounge', 'address' => '22 Tauke Khan St.'],
        ], [$hookahId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Boom Karaoke', 'address' => 'ул. Байтурсынова, 35'],
            'kz' => ['name' => 'Boom Karaoke', 'address' => 'Байтұрсынов көш., 35'],
            'en' => ['name' => 'Boom Karaoke', 'address' => '35 Baitursynov St.'],
        ], [$karaokeId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Ресторан Самарканд', 'address' => 'ул. Казыбек Би, 55'],
            'kz' => ['name' => 'Самарқанд мейрамханасы', 'address' => 'Қазыбек Би көш., 55'],
            'en' => ['name' => 'Samarkand Restaurant', 'address' => '55 Kazybek Bi St.'],
        ], [$restaurantId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Kinopark Shymkent', 'address' => 'ТРЦ Мега Шымкент'],
            'kz' => ['name' => 'Kinopark Shymkent', 'address' => 'Мега Шымкент СОО'],
            'en' => ['name' => 'Kinopark Shymkent', 'address' => 'Mega Shymkent Mall'],
        ], [$cinemaId]);

        $this->createPlace($shymkentId, $now, [
            'ru' => ['name' => 'Стадион Шымкент', 'address' => 'ул. Жангельдина, 22'],
            'kz' => ['name' => 'Шымкент стадионы', 'address' => 'Жангелдин көш., 22'],
            'en' => ['name' => 'Shymkent Stadium', 'address' => '22 Zhangeldin St.'],
        ], [$footballId]);

        // =====================================================================
        // KARAGANDA PLACES
        // =====================================================================
        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Пивной Бар Караганда', 'address' => 'пр. Бухар Жырау, 52'],
            'kz' => ['name' => 'Қарағанды сыра бары', 'address' => 'Бұхар Жырау даң., 52'],
            'en' => ['name' => 'Karaganda Beer Bar', 'address' => '52 Bukhar Zhyrau Ave.'],
        ], [$beerId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Hop & Malt', 'address' => 'ул. Ержанова, 18'],
            'kz' => ['name' => 'Hop & Malt', 'address' => 'Ержанов көш., 18'],
            'en' => ['name' => 'Hop & Malt', 'address' => '18 Erzhanov St.'],
        ], [$beerId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'La Brasserie', 'address' => 'пр. Нуркена Абдирова, 7'],
            'kz' => ['name' => 'La Brasserie', 'address' => 'Нүркен Әбдіров даң., 7'],
            'en' => ['name' => 'La Brasserie', 'address' => '7 Nurken Abdirov Ave.'],
        ], [$coffeeId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Шахтёр Кофе', 'address' => 'ул. Гоголя, 34'],
            'kz' => ['name' => 'Шахтёр Кофе', 'address' => 'Гоголь көш., 34'],
            'en' => ['name' => 'Shakhtar Coffee', 'address' => '34 Gogol St.'],
        ], [$coffeeId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Баня Караганды', 'address' => 'ул. Мичурина, 8'],
            'kz' => ['name' => 'Қарағанды моншағы', 'address' => 'Мичурин көш., 8'],
            'en' => ['name' => 'Karaganda Bathhouse', 'address' => '8 Michurin St.'],
        ], [$bathhouseId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Центральный парк Караганды', 'address' => 'пр. Бухар Жырау'],
            'kz' => ['name' => 'Қарағанды орталық саябағы', 'address' => 'Бұхар Жырау даң.'],
            'en' => ['name' => 'Karaganda Central Park', 'address' => 'Bukhar Zhyrau Ave.'],
        ], [$walkId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Fun Park Боулинг', 'address' => 'ул. Ержанова, 25'],
            'kz' => ['name' => 'Fun Park Боулинг', 'address' => 'Ержанов көш., 25'],
            'en' => ['name' => 'Fun Park Bowling', 'address' => '25 Erzhanov St.'],
        ], [$bowlingId, $billiardsId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Мелодия Караоке', 'address' => 'пр. Бухар Жырау, 30'],
            'kz' => ['name' => 'Мелодия Караоке', 'address' => 'Бұхар Жырау даң., 30'],
            'en' => ['name' => 'Melodiya Karaoke', 'address' => '30 Bukhar Zhyrau Ave.'],
        ], [$karaokeId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Ресторан Абай', 'address' => 'ул. Абая, 12'],
            'kz' => ['name' => 'Абай мейрамханасы', 'address' => 'Абай көш., 12'],
            'en' => ['name' => 'Abay Restaurant', 'address' => '12 Abay St.'],
        ], [$restaurantId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Кинотеатр Арман', 'address' => 'пр. Бухар Жырау, 44'],
            'kz' => ['name' => 'Арман кинотеатры', 'address' => 'Бұхар Жырау даң., 44'],
            'en' => ['name' => 'Arman Cinema', 'address' => '44 Bukhar Zhyrau Ave.'],
        ], [$cinemaId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Шахтёр Арена', 'address' => 'ул. Ленина, 58'],
            'kz' => ['name' => 'Шахтёр Арена', 'address' => 'Ленин көш., 58'],
            'en' => ['name' => 'Shakhtar Arena', 'address' => '58 Lenin St.'],
        ], [$footballId]);

        $this->createPlace($karagandaId, $now, [
            'ru' => ['name' => 'Cloud Lounge', 'address' => 'ул. Гоголя, 48'],
            'kz' => ['name' => 'Cloud Lounge', 'address' => 'Гоголь көш., 48'],
            'en' => ['name' => 'Cloud Lounge', 'address' => '48 Gogol St.'],
        ], [$hookahId]);

        // =====================================================================
        // AKTAU PLACES
        // =====================================================================
        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Пивной Причал', 'address' => '14 мкр., 1'],
            'kz' => ['name' => 'Пивной Причал', 'address' => '14 шағын аудан, 1'],
            'en' => ['name' => 'Pivnoy Prichal', 'address' => '14th Microdistrict, 1'],
        ], [$beerId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Кофейня Каспий', 'address' => '4 мкр., 60'],
            'kz' => ['name' => 'Каспий кофеханасы', 'address' => '4 шағын аудан, 60'],
            'en' => ['name' => 'Caspian Coffee', 'address' => '4th Microdistrict, 60'],
        ], [$coffeeId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Баня на берегу', 'address' => '15 мкр., 45'],
            'kz' => ['name' => 'Жағадағы моншақ', 'address' => '15 шағын аудан, 45'],
            'en' => ['name' => 'Seaside Bathhouse', 'address' => '15th Microdistrict, 45'],
        ], [$bathhouseId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Набережная Актау', 'address' => 'набережная Каспийского моря'],
            'kz' => ['name' => 'Ақтау жағалауы', 'address' => 'Каспий теңізі жағалауы'],
            'en' => ['name' => 'Aktau Waterfront', 'address' => 'Caspian Sea Waterfront'],
        ], [$walkId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Ресторан Парус', 'address' => '1 мкр., 1А'],
            'kz' => ['name' => 'Парус мейрамханасы', 'address' => '1 шағын аудан, 1А'],
            'en' => ['name' => 'Parus Restaurant', 'address' => '1st Microdistrict, 1A'],
        ], [$restaurantId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Актау Боулинг', 'address' => '11 мкр., 50'],
            'kz' => ['name' => 'Ақтау Боулинг', 'address' => '11 шағын аудан, 50'],
            'en' => ['name' => 'Aktau Bowling', 'address' => '11th Microdistrict, 50'],
        ], [$bowlingId, $billiardsId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Море Караоке', 'address' => '5 мкр., 22'],
            'kz' => ['name' => 'Море Караоке', 'address' => '5 шағын аудан, 22'],
            'en' => ['name' => 'More Karaoke', 'address' => '5th Microdistrict, 22'],
        ], [$karaokeId]);

        $this->createPlace($aktauId, $now, [
            'ru' => ['name' => 'Кинотеатр Каспий', 'address' => '3 мкр., 40'],
            'kz' => ['name' => 'Каспий кинотеатры', 'address' => '3 шағын аудан, 40'],
            'en' => ['name' => 'Caspian Cinema', 'address' => '3rd Microdistrict, 40'],
        ], [$cinemaId]);

        // =====================================================================
        // ATYRAU PLACES
        // =====================================================================
        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Пивная Бочка', 'address' => 'ул. Сатпаева, 15А'],
            'kz' => ['name' => 'Пивная Бочка', 'address' => 'Сәтбаев көш., 15А'],
            'en' => ['name' => 'Pivnaya Bochka', 'address' => '15A Satpayev St.'],
        ], [$beerId]);

        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Кофейня Атырау', 'address' => 'ул. Азаттык, 34'],
            'kz' => ['name' => 'Атырау кофеханасы', 'address' => 'Азаттық көш., 34'],
            'en' => ['name' => 'Atyrau Coffee', 'address' => '34 Azattyk St.'],
        ], [$coffeeId]);

        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Набережная Урала', 'address' => 'набережная реки Урал'],
            'kz' => ['name' => 'Жайық жағалауы', 'address' => 'Жайық өзені жағалауы'],
            'en' => ['name' => 'Ural River Embankment', 'address' => 'Ural River Embankment'],
        ], [$walkId]);

        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Ресторан Атырау', 'address' => 'ул. Сатпаева, 20'],
            'kz' => ['name' => 'Атырау мейрамханасы', 'address' => 'Сәтбаев көш., 20'],
            'en' => ['name' => 'Atyrau Restaurant', 'address' => '20 Satpayev St.'],
        ], [$restaurantId]);

        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Баня Жайык', 'address' => 'ул. Махамбета, 110'],
            'kz' => ['name' => 'Жайық моншағы', 'address' => 'Махамбет көш., 110'],
            'en' => ['name' => 'Zhaiyk Bathhouse', 'address' => '110 Makhambet St.'],
        ], [$bathhouseId]);

        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Атырау Боулинг', 'address' => 'ул. Азаттык, 66'],
            'kz' => ['name' => 'Атырау Боулинг', 'address' => 'Азаттық көш., 66'],
            'en' => ['name' => 'Atyrau Bowling', 'address' => '66 Azattyk St.'],
        ], [$bowlingId]);

        $this->createPlace($atyrauId, $now, [
            'ru' => ['name' => 'Спорткомплекс Мунайши', 'address' => 'ул. Баймуханова, 78'],
            'kz' => ['name' => 'Мұнайшы спорт кешені', 'address' => 'Баймұханов көш., 78'],
            'en' => ['name' => 'Munaishy Sports Complex', 'address' => '78 Baimukhanov St.'],
        ], [$footballId, $tennisId]);

        // =====================================================================
        // PAVLODAR PLACES
        // =====================================================================
        $this->createPlace($pavlodarId, $now, [
            'ru' => ['name' => 'Пивоварня Павлодар', 'address' => 'ул. Торайгырова, 44'],
            'kz' => ['name' => 'Павлодар сыра қайнатқышы', 'address' => 'Торайғыров көш., 44'],
            'en' => ['name' => 'Pavlodar Brewery', 'address' => '44 Toraighyrov St.'],
        ], [$beerId]);

        $this->createPlace($pavlodarId, $now, [
            'ru' => ['name' => 'Кофейня Иртыш', 'address' => 'ул. Кутузова, 20'],
            'kz' => ['name' => 'Ертіс кофеханасы', 'address' => 'Кутузов көш., 20'],
            'en' => ['name' => 'Irtysh Coffee', 'address' => '20 Kutuzov St.'],
        ], [$coffeeId]);

        $this->createPlace($pavlodarId, $now, [
            'ru' => ['name' => 'Набережная Иртыша', 'address' => 'набережная реки Иртыш'],
            'kz' => ['name' => 'Ертіс жағалауы', 'address' => 'Ертіс өзені жағалауы'],
            'en' => ['name' => 'Irtysh Embankment', 'address' => 'Irtysh River Embankment'],
        ], [$walkId]);

        $this->createPlace($pavlodarId, $now, [
            'ru' => ['name' => 'Ресторан Ертіс', 'address' => 'ул. Академика Сатпаева, 72'],
            'kz' => ['name' => 'Ертіс мейрамханасы', 'address' => 'Академик Сәтбаев көш., 72'],
            'en' => ['name' => 'Ertis Restaurant', 'address' => '72 Akademik Satpayev St.'],
        ], [$restaurantId]);

        $this->createPlace($pavlodarId, $now, [
            'ru' => ['name' => 'Баня Павлодар', 'address' => 'ул. Лермонтова, 62'],
            'kz' => ['name' => 'Павлодар моншағы', 'address' => 'Лермонтов көш., 62'],
            'en' => ['name' => 'Pavlodar Bathhouse', 'address' => '62 Lermontov St.'],
        ], [$bathhouseId]);

        $this->createPlace($pavlodarId, $now, [
            'ru' => ['name' => 'Кинотеатр Павлодар', 'address' => 'ул. Торайгырова, 60'],
            'kz' => ['name' => 'Павлодар кинотеатры', 'address' => 'Торайғыров көш., 60'],
            'en' => ['name' => 'Pavlodar Cinema', 'address' => '60 Toraighyrov St.'],
        ], [$cinemaId]);

        // =====================================================================
        // SEMEY PLACES
        // =====================================================================
        $this->createPlace($semeyId, $now, [
            'ru' => ['name' => 'Пивной Двор Семей', 'address' => 'ул. Абая, 100'],
            'kz' => ['name' => 'Семей сыра алаңы', 'address' => 'Абай көш., 100'],
            'en' => ['name' => 'Semey Beer Yard', 'address' => '100 Abay St.'],
        ], [$beerId]);

        $this->createPlace($semeyId, $now, [
            'ru' => ['name' => 'Кофейня Достоевского', 'address' => 'ул. Достоевского, 88'],
            'kz' => ['name' => 'Достоевский кофеханасы', 'address' => 'Достоевский көш., 88'],
            'en' => ['name' => 'Dostoevsky Coffee', 'address' => '88 Dostoevsky St.'],
        ], [$coffeeId]);

        $this->createPlace($semeyId, $now, [
            'ru' => ['name' => 'Набережная Иртыша Семей', 'address' => 'набережная реки Иртыш'],
            'kz' => ['name' => 'Ертіс жағалауы Семей', 'address' => 'Ертіс өзені жағалауы'],
            'en' => ['name' => 'Irtysh Embankment Semey', 'address' => 'Irtysh River Embankment'],
        ], [$walkId]);

        $this->createPlace($semeyId, $now, [
            'ru' => ['name' => 'Парк Жастар', 'address' => 'ул. Кабанбай Батыра, 50'],
            'kz' => ['name' => 'Жастар саябағы', 'address' => 'Қабанбай Батыр көш., 50'],
            'en' => ['name' => 'Zhastar Park', 'address' => '50 Kabanbay Batyr St.'],
        ], [$walkId]);

        $this->createPlace($semeyId, $now, [
            'ru' => ['name' => 'Ресторан Абай Семей', 'address' => 'ул. Абая, 150'],
            'kz' => ['name' => 'Абай мейрамханасы Семей', 'address' => 'Абай көш., 150'],
            'en' => ['name' => 'Abay Restaurant Semey', 'address' => '150 Abay St.'],
        ], [$restaurantId]);

        $this->createPlace($semeyId, $now, [
            'ru' => ['name' => 'Семей Боулинг', 'address' => 'ул. Интернациональная, 40'],
            'kz' => ['name' => 'Семей Боулинг', 'address' => 'Интернациональная көш., 40'],
            'en' => ['name' => 'Semey Bowling', 'address' => '40 Internatsionalnaya St.'],
        ], [$bowlingId, $billiardsId]);

        // =====================================================================
        // KOSTANAY PLACES
        // =====================================================================
        $this->createPlace($kostanayId, $now, [
            'ru' => ['name' => 'Пивной бар Костанай', 'address' => 'пр. Аль-Фараби, 36'],
            'kz' => ['name' => 'Қостанай сыра бары', 'address' => 'Әл-Фараби даң., 36'],
            'en' => ['name' => 'Kostanay Beer Bar', 'address' => '36 Al-Farabi Ave.'],
        ], [$beerId]);

        $this->createPlace($kostanayId, $now, [
            'ru' => ['name' => 'Кофейня Тобол', 'address' => 'ул. Байтурсынова, 80'],
            'kz' => ['name' => 'Тобыл кофеханасы', 'address' => 'Байтұрсынов көш., 80'],
            'en' => ['name' => 'Tobol Coffee', 'address' => '80 Baitursynov St.'],
        ], [$coffeeId]);

        $this->createPlace($kostanayId, $now, [
            'ru' => ['name' => 'Парк Победы', 'address' => 'ул. Победы'],
            'kz' => ['name' => 'Жеңіс саябағы', 'address' => 'Жеңіс көш.'],
            'en' => ['name' => 'Victory Park', 'address' => 'Pobedy St.'],
        ], [$walkId]);

        $this->createPlace($kostanayId, $now, [
            'ru' => ['name' => 'Ресторан Степь', 'address' => 'пр. Аль-Фараби, 55'],
            'kz' => ['name' => 'Дала мейрамханасы', 'address' => 'Әл-Фараби даң., 55'],
            'en' => ['name' => 'Steppe Restaurant', 'address' => '55 Al-Farabi Ave.'],
        ], [$restaurantId]);

        $this->createPlace($kostanayId, $now, [
            'ru' => ['name' => 'Баня Костанай', 'address' => 'ул. Гоголя, 44'],
            'kz' => ['name' => 'Қостанай моншағы', 'address' => 'Гоголь көш., 44'],
            'en' => ['name' => 'Kostanay Bathhouse', 'address' => '44 Gogol St.'],
        ], [$bathhouseId]);

        $this->createPlace($kostanayId, $now, [
            'ru' => ['name' => 'Костанай Боулинг', 'address' => 'ул. Байтурсынова, 115'],
            'kz' => ['name' => 'Қостанай Боулинг', 'address' => 'Байтұрсынов көш., 115'],
            'en' => ['name' => 'Kostanay Bowling', 'address' => '115 Baitursynov St.'],
        ], [$bowlingId]);

        // =====================================================================
        // TARAZ PLACES
        // =====================================================================
        $this->createPlace($tarazId, $now, [
            'ru' => ['name' => 'Пивная Тараз', 'address' => 'ул. Толе Би, 60'],
            'kz' => ['name' => 'Тараз сыра бары', 'address' => 'Төле Би көш., 60'],
            'en' => ['name' => 'Taraz Beer Bar', 'address' => '60 Tole Bi St.'],
        ], [$beerId]);

        $this->createPlace($tarazId, $now, [
            'ru' => ['name' => 'Кофейня Жамбыл', 'address' => 'ул. Абая, 22'],
            'kz' => ['name' => 'Жамбыл кофеханасы', 'address' => 'Абай көш., 22'],
            'en' => ['name' => 'Zhambyl Coffee', 'address' => '22 Abay St.'],
        ], [$coffeeId]);

        $this->createPlace($tarazId, $now, [
            'ru' => ['name' => 'Парк Тараз', 'address' => 'Центральный парк'],
            'kz' => ['name' => 'Тараз саябағы', 'address' => 'Орталық саябақ'],
            'en' => ['name' => 'Taraz Park', 'address' => 'Central Park'],
        ], [$walkId]);

        $this->createPlace($tarazId, $now, [
            'ru' => ['name' => 'Ресторан Тараз', 'address' => 'ул. Толе Би, 90'],
            'kz' => ['name' => 'Тараз мейрамханасы', 'address' => 'Төле Би көш., 90'],
            'en' => ['name' => 'Taraz Restaurant', 'address' => '90 Tole Bi St.'],
        ], [$restaurantId]);

        $this->createPlace($tarazId, $now, [
            'ru' => ['name' => 'Баня Древний Тараз', 'address' => 'ул. Казыбек Би, 32'],
            'kz' => ['name' => 'Ежелгі Тараз моншағы', 'address' => 'Қазыбек Би көш., 32'],
            'en' => ['name' => 'Ancient Taraz Bathhouse', 'address' => '32 Kazybek Bi St.'],
        ], [$bathhouseId]);

        // =====================================================================
        // URALSK PLACES
        // =====================================================================
        $this->createPlace($uralskId, $now, [
            'ru' => ['name' => 'Пивной Край', 'address' => 'пр. Достык, 202'],
            'kz' => ['name' => 'Сыра Өлкесі', 'address' => 'Достық даң., 202'],
            'en' => ['name' => 'Pivnoy Kray', 'address' => '202 Dostyk Ave.'],
        ], [$beerId]);

        $this->createPlace($uralskId, $now, [
            'ru' => ['name' => 'Кофейня Орал', 'address' => 'ул. Жангир хана, 18'],
            'kz' => ['name' => 'Орал кофеханасы', 'address' => 'Жәңгір хан көш., 18'],
            'en' => ['name' => 'Oral Coffee', 'address' => '18 Zhangir Khan St.'],
        ], [$coffeeId]);

        $this->createPlace($uralskId, $now, [
            'ru' => ['name' => 'Набережная Урала', 'address' => 'набережная реки Урал'],
            'kz' => ['name' => 'Жайық жағалауы', 'address' => 'Жайық өзені жағалауы'],
            'en' => ['name' => 'Ural River Waterfront', 'address' => 'Ural River Waterfront'],
        ], [$walkId]);

        $this->createPlace($uralskId, $now, [
            'ru' => ['name' => 'Ресторан Жайык', 'address' => 'пр. Достык, 185'],
            'kz' => ['name' => 'Жайық мейрамханасы', 'address' => 'Достық даң., 185'],
            'en' => ['name' => 'Zhaiyk Restaurant', 'address' => '185 Dostyk Ave.'],
        ], [$restaurantId]);

        $this->createPlace($uralskId, $now, [
            'ru' => ['name' => 'Баня Уральск', 'address' => 'ул. Мухита, 96'],
            'kz' => ['name' => 'Орал моншағы', 'address' => 'Мұхит көш., 96'],
            'en' => ['name' => 'Uralsk Bathhouse', 'address' => '96 Mukhit St.'],
        ], [$bathhouseId]);

        $this->createPlace($uralskId, $now, [
            'ru' => ['name' => 'Уральск Боулинг', 'address' => 'ул. Жангир хана, 60'],
            'kz' => ['name' => 'Орал Боулинг', 'address' => 'Жәңгір хан көш., 60'],
            'en' => ['name' => 'Uralsk Bowling', 'address' => '60 Zhangir Khan St.'],
        ], [$bowlingId, $billiardsId]);

        // =====================================================================
        // PETROPAVLOVSK PLACES
        // =====================================================================
        $this->createPlace($petropavlovskId, $now, [
            'ru' => ['name' => 'Пивной Дом', 'address' => 'ул. Конституции, 38'],
            'kz' => ['name' => 'Сыра Үйі', 'address' => 'Конституция көш., 38'],
            'en' => ['name' => 'Beer House', 'address' => '38 Constitution St.'],
        ], [$beerId]);

        $this->createPlace($petropavlovskId, $now, [
            'ru' => ['name' => 'Кофейня Петропавл', 'address' => 'ул. Абая, 56'],
            'kz' => ['name' => 'Петропавл кофеханасы', 'address' => 'Абай көш., 56'],
            'en' => ['name' => 'Petropavl Coffee', 'address' => '56 Abay St.'],
        ], [$coffeeId]);

        $this->createPlace($petropavlovskId, $now, [
            'ru' => ['name' => 'Парк культуры', 'address' => 'ул. Интернациональная'],
            'kz' => ['name' => 'Мәдениет саябағы', 'address' => 'Интернациональная көш.'],
            'en' => ['name' => 'Culture Park', 'address' => 'Internatsionalnaya St.'],
        ], [$walkId]);

        $this->createPlace($petropavlovskId, $now, [
            'ru' => ['name' => 'Ресторан Север', 'address' => 'ул. Конституции, 70'],
            'kz' => ['name' => 'Солтүстік мейрамханасы', 'address' => 'Конституция көш., 70'],
            'en' => ['name' => 'Sever Restaurant', 'address' => '70 Constitution St.'],
        ], [$restaurantId]);

        $this->createPlace($petropavlovskId, $now, [
            'ru' => ['name' => 'Баня Петропавловск', 'address' => 'ул. Пушкина, 14'],
            'kz' => ['name' => 'Петропавл моншағы', 'address' => 'Пушкин көш., 14'],
            'en' => ['name' => 'Petropavlovsk Bathhouse', 'address' => '14 Pushkin St.'],
        ], [$bathhouseId]);

        // =====================================================================
        // TURKESTAN PLACES
        // =====================================================================
        $this->createPlace($turkestanId, $now, [
            'ru' => ['name' => 'Кофейня Туркестан', 'address' => 'ул. Тауке хана, 10'],
            'kz' => ['name' => 'Түркістан кофеханасы', 'address' => 'Тәуке хан көш., 10'],
            'en' => ['name' => 'Turkestan Coffee', 'address' => '10 Tauke Khan St.'],
        ], [$coffeeId]);

        $this->createPlace($turkestanId, $now, [
            'ru' => ['name' => 'Мавзолей Ходжи Ахмеда Ясави', 'address' => 'площадь Ясави'],
            'kz' => ['name' => 'Қожа Ахмет Ясауи кесенесі', 'address' => 'Ясауи алаңы'],
            'en' => ['name' => 'Khoja Ahmed Yasawi Mausoleum', 'address' => 'Yasawi Square'],
        ], [$walkId]);

        $this->createPlace($turkestanId, $now, [
            'ru' => ['name' => 'Караван-сарай', 'address' => 'ул. Тауке хана, 30'],
            'kz' => ['name' => 'Керуен-сарай', 'address' => 'Тәуке хан көш., 30'],
            'en' => ['name' => 'Caravanserai', 'address' => '30 Tauke Khan St.'],
        ], [$restaurantId]);

        $this->createPlace($turkestanId, $now, [
            'ru' => ['name' => 'Хаммам Туркестан', 'address' => 'ул. Ясави, 15'],
            'kz' => ['name' => 'Түркістан хаммамы', 'address' => 'Ясауи көш., 15'],
            'en' => ['name' => 'Turkestan Hammam', 'address' => '15 Yasawi St.'],
        ], [$bathhouseId]);

        // =====================================================================
        // KOKSHETAU PLACES
        // =====================================================================
        $this->createPlace($kokshetauId, $now, [
            'ru' => ['name' => 'Пивной бар Кокшетау', 'address' => 'ул. Абая, 120'],
            'kz' => ['name' => 'Көкшетау сыра бары', 'address' => 'Абай көш., 120'],
            'en' => ['name' => 'Kokshetau Beer Bar', 'address' => '120 Abay St.'],
        ], [$beerId]);

        $this->createPlace($kokshetauId, $now, [
            'ru' => ['name' => 'Кофейня Бурабай', 'address' => 'ул. Ауельбекова, 55'],
            'kz' => ['name' => 'Бурабай кофеханасы', 'address' => 'Әуелбеков көш., 55'],
            'en' => ['name' => 'Burabay Coffee', 'address' => '55 Auelbekov St.'],
        ], [$coffeeId]);

        $this->createPlace($kokshetauId, $now, [
            'ru' => ['name' => 'Озеро Копа', 'address' => 'набережная озера Копа'],
            'kz' => ['name' => 'Қопа көлі', 'address' => 'Қопа көлі жағалауы'],
            'en' => ['name' => 'Lake Kopa', 'address' => 'Lake Kopa Waterfront'],
        ], [$walkId]);

        $this->createPlace($kokshetauId, $now, [
            'ru' => ['name' => 'Ресторан Кокшетау', 'address' => 'ул. Абая, 140'],
            'kz' => ['name' => 'Көкшетау мейрамханасы', 'address' => 'Абай көш., 140'],
            'en' => ['name' => 'Kokshetau Restaurant', 'address' => '140 Abay St.'],
        ], [$restaurantId]);

        $this->createPlace($kokshetauId, $now, [
            'ru' => ['name' => 'Баня Кокшетау', 'address' => 'ул. Горького, 20'],
            'kz' => ['name' => 'Көкшетау моншағы', 'address' => 'Горький көш., 20'],
            'en' => ['name' => 'Kokshetau Bathhouse', 'address' => '20 Gorky St.'],
        ], [$bathhouseId]);

        // =====================================================================
        // TALDYKORGAN PLACES
        // =====================================================================
        $this->createPlace($taldykorganId, $now, [
            'ru' => ['name' => 'Пивная Жетысу', 'address' => 'ул. Жансугурова, 112'],
            'kz' => ['name' => 'Жетісу сыра бары', 'address' => 'Жансүгіров көш., 112'],
            'en' => ['name' => 'Zhetysu Beer Bar', 'address' => '112 Zhansugurov St.'],
        ], [$beerId]);

        $this->createPlace($taldykorganId, $now, [
            'ru' => ['name' => 'Кофейня Каратал', 'address' => 'ул. Абая, 245'],
            'kz' => ['name' => 'Қаратал кофеханасы', 'address' => 'Абай көш., 245'],
            'en' => ['name' => 'Karatal Coffee', 'address' => '245 Abay St.'],
        ], [$coffeeId]);

        $this->createPlace($taldykorganId, $now, [
            'ru' => ['name' => 'Парк Жастар', 'address' => 'ул. Жансугурова'],
            'kz' => ['name' => 'Жастар саябағы', 'address' => 'Жансүгіров көш.'],
            'en' => ['name' => 'Zhastar Park', 'address' => 'Zhansugurov St.'],
        ], [$walkId]);

        $this->createPlace($taldykorganId, $now, [
            'ru' => ['name' => 'Ресторан Жетысу', 'address' => 'ул. Абая, 260'],
            'kz' => ['name' => 'Жетісу мейрамханасы', 'address' => 'Абай көш., 260'],
            'en' => ['name' => 'Zhetysu Restaurant', 'address' => '260 Abay St.'],
        ], [$restaurantId]);

        // =====================================================================
        // EKIBASTUZ PLACES
        // =====================================================================
        $this->createPlace($ekibastuzId, $now, [
            'ru' => ['name' => 'Пивная Экибастуз', 'address' => 'ул. Ауэзова, 15'],
            'kz' => ['name' => 'Екібастұз сыра бары', 'address' => 'Әуезов көш., 15'],
            'en' => ['name' => 'Ekibastuz Beer Bar', 'address' => '15 Auezov St.'],
        ], [$beerId]);

        $this->createPlace($ekibastuzId, $now, [
            'ru' => ['name' => 'Кофейня Энергетик', 'address' => 'ул. Машхур Жусупа, 50'],
            'kz' => ['name' => 'Энергетик кофеханасы', 'address' => 'Мәшһүр Жүсіп көш., 50'],
            'en' => ['name' => 'Energetik Coffee', 'address' => '50 Mashkhur Zhusup St.'],
        ], [$coffeeId]);

        $this->createPlace($ekibastuzId, $now, [
            'ru' => ['name' => 'Парк Шахтёров', 'address' => 'ул. Ленина'],
            'kz' => ['name' => 'Шахтёрлар саябағы', 'address' => 'Ленин көш.'],
            'en' => ['name' => 'Miners Park', 'address' => 'Lenin St.'],
        ], [$walkId]);

        $this->createPlace($ekibastuzId, $now, [
            'ru' => ['name' => 'Ресторан Экибастуз', 'address' => 'ул. Ауэзова, 30'],
            'kz' => ['name' => 'Екібастұз мейрамханасы', 'address' => 'Әуезов көш., 30'],
            'en' => ['name' => 'Ekibastuz Restaurant', 'address' => '30 Auezov St.'],
        ], [$restaurantId]);

        // =====================================================================
        // RUDNY PLACES
        // =====================================================================
        $this->createPlace($rudnyId, $now, [
            'ru' => ['name' => 'Пивная Горняк', 'address' => 'ул. Ленина, 40'],
            'kz' => ['name' => 'Горняк сыра бары', 'address' => 'Ленин көш., 40'],
            'en' => ['name' => 'Gornyak Beer Bar', 'address' => '40 Lenin St.'],
        ], [$beerId]);

        $this->createPlace($rudnyId, $now, [
            'ru' => ['name' => 'Кофейня Рудный', 'address' => 'пр. Космонавтов, 12'],
            'kz' => ['name' => 'Рудный кофеханасы', 'address' => 'Ғарышкерлер даң., 12'],
            'en' => ['name' => 'Rudny Coffee', 'address' => '12 Kosmonavtov Ave.'],
        ], [$coffeeId]);

        $this->createPlace($rudnyId, $now, [
            'ru' => ['name' => 'Парк Горняков', 'address' => 'ул. Горняков'],
            'kz' => ['name' => 'Кеншілер саябағы', 'address' => 'Кеншілер көш.'],
            'en' => ['name' => 'Miners Park', 'address' => 'Gornyakov St.'],
        ], [$walkId]);

        $this->createPlace($rudnyId, $now, [
            'ru' => ['name' => 'Ресторан Рудный', 'address' => 'ул. Ленина, 60'],
            'kz' => ['name' => 'Рудный мейрамханасы', 'address' => 'Ленин көш., 60'],
            'en' => ['name' => 'Rudny Restaurant', 'address' => '60 Lenin St.'],
        ], [$restaurantId]);

        // =====================================================================
        // TEMIRTAU PLACES
        // =====================================================================
        $this->createPlace($temirtauId, $now, [
            'ru' => ['name' => 'Пивная Металлург', 'address' => 'пр. Металлургов, 22'],
            'kz' => ['name' => 'Металлург сыра бары', 'address' => 'Металлургтар даң., 22'],
            'en' => ['name' => 'Metallurg Beer Bar', 'address' => '22 Metallurgov Ave.'],
        ], [$beerId]);

        $this->createPlace($temirtauId, $now, [
            'ru' => ['name' => 'Кофейня Темиртау', 'address' => 'пр. Республики, 15'],
            'kz' => ['name' => 'Теміртау кофеханасы', 'address' => 'Республика даң., 15'],
            'en' => ['name' => 'Temirtau Coffee', 'address' => '15 Republic Ave.'],
        ], [$coffeeId]);

        $this->createPlace($temirtauId, $now, [
            'ru' => ['name' => 'Набережная Самаркандского водохранилища', 'address' => 'Самаркандское водохранилище'],
            'kz' => ['name' => 'Самарқанд су қоймасы жағалауы', 'address' => 'Самарқанд су қоймасы'],
            'en' => ['name' => 'Samarkand Reservoir Embankment', 'address' => 'Samarkand Reservoir'],
        ], [$walkId]);

        $this->createPlace($temirtauId, $now, [
            'ru' => ['name' => 'Ресторан Темиртау', 'address' => 'пр. Металлургов, 40'],
            'kz' => ['name' => 'Теміртау мейрамханасы', 'address' => 'Металлургтар даң., 40'],
            'en' => ['name' => 'Temirtau Restaurant', 'address' => '40 Metallurgov Ave.'],
        ], [$restaurantId]);

        $this->createPlace($temirtauId, $now, [
            'ru' => ['name' => 'Баня Металлург', 'address' => 'ул. Ленина, 28'],
            'kz' => ['name' => 'Металлург моншағы', 'address' => 'Ленин көш., 28'],
            'en' => ['name' => 'Metallurg Bathhouse', 'address' => '28 Lenin St.'],
        ], [$bathhouseId]);

        // =====================================================================
        // ZHEZKAZGAN PLACES
        // =====================================================================
        $this->createPlace($zhezkazganId, $now, [
            'ru' => ['name' => 'Пивная Жезказган', 'address' => 'пр. Мира, 10'],
            'kz' => ['name' => 'Жезқазған сыра бары', 'address' => 'Бейбітшілік даң., 10'],
            'en' => ['name' => 'Zhezkazgan Beer Bar', 'address' => '10 Mira Ave.'],
        ], [$beerId]);

        $this->createPlace($zhezkazganId, $now, [
            'ru' => ['name' => 'Кофейня Сарыарка', 'address' => 'ул. Алтынсарина, 18'],
            'kz' => ['name' => 'Сарыарқа кофеханасы', 'address' => 'Алтынсарин көш., 18'],
            'en' => ['name' => 'Saryarka Coffee', 'address' => '18 Altynsarin St.'],
        ], [$coffeeId]);

        $this->createPlace($zhezkazganId, $now, [
            'ru' => ['name' => 'Парк Жезказган', 'address' => 'Центральный парк'],
            'kz' => ['name' => 'Жезқазған саябағы', 'address' => 'Орталық саябақ'],
            'en' => ['name' => 'Zhezkazgan Park', 'address' => 'Central Park'],
        ], [$walkId]);

        $this->createPlace($zhezkazganId, $now, [
            'ru' => ['name' => 'Ресторан Жезказган', 'address' => 'пр. Мира, 30'],
            'kz' => ['name' => 'Жезқазған мейрамханасы', 'address' => 'Бейбітшілік даң., 30'],
            'en' => ['name' => 'Zhezkazgan Restaurant', 'address' => '30 Mira Ave.'],
        ], [$restaurantId]);

        $this->createPlace($zhezkazganId, $now, [
            'ru' => ['name' => 'Баня Жезказган', 'address' => 'ул. Горняков, 5'],
            'kz' => ['name' => 'Жезқазған моншағы', 'address' => 'Кеншілер көш., 5'],
            'en' => ['name' => 'Zhezkazgan Bathhouse', 'address' => '5 Gornyakov St.'],
        ], [$bathhouseId]);
    }

    /**
     * @param  array<string, array{name: string, address: string}>  $translations
     * @param  list<int>  $activityTypeIds
     */
    private function createPlace(int $cityId, \Illuminate\Support\Carbon $now, array $translations, array $activityTypeIds): void
    {
        $placeId = DB::table('places')->insertGetId([
            'city_id' => $cityId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $translationRows = [];
        foreach ($translations as $languageCode => $data) {
            $translationRows[] = [
                'place_id' => $placeId,
                'language_code' => $languageCode,
                'name' => $data['name'],
                'address' => $data['address'],
            ];
        }
        DB::table('place_translations')->insert($translationRows);

        $pivotRows = [];
        foreach ($activityTypeIds as $activityTypeId) {
            $pivotRows[] = [
                'activity_type_id' => $activityTypeId,
                'place_id' => $placeId,
            ];
        }
        DB::table('activity_type_place')->insert($pivotRows);
    }

    private function seedPlaceDiscounts(): void
    {
        $now = now();

        // Get a few place IDs to assign discounts to (first 5 places)
        $placeIds = DB::table('places')->orderBy('id')->limit(5)->pluck('id');

        $discounts = [
            ['place_id' => $placeIds[0], 'discount_percent' => 10, 'is_active' => true, 'starts_at' => null, 'ends_at' => null],
            ['place_id' => $placeIds[1], 'discount_percent' => 15, 'is_active' => true, 'starts_at' => null, 'ends_at' => null],
            ['place_id' => $placeIds[2], 'discount_percent' => 20, 'is_active' => true, 'starts_at' => null, 'ends_at' => null],
            ['place_id' => $placeIds[3], 'discount_percent' => 10, 'is_active' => true, 'starts_at' => $now->copy()->subDays(7), 'ends_at' => $now->copy()->addDays(30)],
            ['place_id' => $placeIds[4], 'discount_percent' => 25, 'is_active' => true, 'starts_at' => null, 'ends_at' => $now->copy()->addDays(60)],
        ];

        foreach ($discounts as $discount) {
            DB::table('place_discounts')->insert([
                'place_id' => $discount['place_id'],
                'discount_percent' => $discount['discount_percent'],
                'is_active' => $discount['is_active'],
                'starts_at' => $discount['starts_at'],
                'ends_at' => $discount['ends_at'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedPlaceWorkingHours(): void
    {
        $now = now();
        $placeIds = DB::table('places')->orderBy('id')->pluck('id');

        $schedules = [
            // Standard restaurant hours
            ['open' => '10:00', 'close' => '22:00'],
            // Early morning cafe
            ['open' => '08:00', 'close' => '20:00'],
            // Late night bar
            ['open' => '12:00', 'close' => '23:00'],
            // Standard business hours
            ['open' => '09:00', 'close' => '21:00'],
        ];

        foreach ($placeIds as $index => $placeId) {
            $schedule = $schedules[$index % count($schedules)];

            foreach (range(0, 6) as $day) {
                // Sunday (6) closed for some places
                $isClosed = $day === 6 && $index % 3 === 0;

                DB::table('place_working_hours')->insert([
                    'place_id' => $placeId,
                    'day_of_week' => $day,
                    'open_time' => $isClosed ? null : $schedule['open'],
                    'close_time' => $isClosed ? null : $schedule['close'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function seedUsers(): void
    {
        $now = now();
        $password = Hash::make('password');

        $cityNames = DB::table('city_translations')
            ->where('language_code', 'en')
            ->pluck('city_id', 'name');

        $userTypes = DB::table('user_types')->pluck('id', 'slug');
        $clientTypeId = $userTypes['client'];
        $adminTypeId = $userTypes['admin'];
        $cityManagerTypeId = $userTypes['city_manager'];

        // Seed admin user
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@companion.test',
            'phone' => '+77000000000',
            'age' => 30,
            'gender' => Gender::Male->value,
            'password' => $password,
            'city_id' => $cityNames['Almaty'],
            'status' => 'active',
            'user_type_id' => $adminTypeId,
            'phone_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Seed city manager user (for Almaty)
        DB::table('users')->insert([
            'name' => 'Almaty Manager',
            'email' => 'manager.almaty@companion.test',
            'phone' => '+77000099999',
            'age' => 28,
            'gender' => Gender::Male->value,
            'password' => $password,
            'city_id' => $cityNames['Almaty'],
            'status' => 'active',
            'user_type_id' => $cityManagerTypeId,
            'phone_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $namesByCities = [
            $cityNames['Almaty'] => [
                ['name' => 'Arman Bekmuratov', 'gender' => Gender::Male],
                ['name' => 'Daulet Nurzhanov', 'gender' => Gender::Male],
                ['name' => 'Aibek Suleimenov', 'gender' => Gender::Male],
                ['name' => 'Timur Kairbekov', 'gender' => Gender::Male],
                ['name' => 'Ruslan Omarov', 'gender' => Gender::Male],
                ['name' => 'Asel Zhunusova', 'gender' => Gender::Female],
                ['name' => 'Dinara Muratova', 'gender' => Gender::Female],
                ['name' => 'Madina Tulegenova', 'gender' => Gender::Female],
                ['name' => 'Kamila Aitbayeva', 'gender' => Gender::Female],
                ['name' => 'Zhanna Sarsenbayeva', 'gender' => Gender::Female],
            ],
            $cityNames['Astana'] => [
                ['name' => 'Yerbol Tastanov', 'gender' => Gender::Male],
                ['name' => 'Nursultan Ibragimov', 'gender' => Gender::Male],
                ['name' => 'Marat Zhumabekov', 'gender' => Gender::Male],
                ['name' => 'Samat Kozhakhmetov', 'gender' => Gender::Male],
                ['name' => 'Dias Moldabekov', 'gender' => Gender::Male],
                ['name' => 'Aigul Nurgaliyeva', 'gender' => Gender::Female],
                ['name' => 'Saltanat Baizhanova', 'gender' => Gender::Female],
                ['name' => 'Gulnaz Ospanova', 'gender' => Gender::Female],
                ['name' => 'Dana Serikova', 'gender' => Gender::Female],
                ['name' => 'Aliya Tastanova', 'gender' => Gender::Female],
            ],
            $cityNames['Shymkent'] => [
                ['name' => 'Askar Tursynbekov', 'gender' => Gender::Male],
                ['name' => 'Bekzat Abdullaev', 'gender' => Gender::Male],
                ['name' => 'Azamat Kurmangaliev', 'gender' => Gender::Male],
                ['name' => 'Nurzhan Karimov', 'gender' => Gender::Male],
                ['name' => 'Serik Dosmagambetov', 'gender' => Gender::Male],
                ['name' => 'Zarina Bektursynova', 'gender' => Gender::Female],
                ['name' => 'Aidana Sultanova', 'gender' => Gender::Female],
                ['name' => 'Togzhan Yessenova', 'gender' => Gender::Female],
                ['name' => 'Moldir Baitasova', 'gender' => Gender::Female],
                ['name' => 'Nazerke Kaliyeva', 'gender' => Gender::Female],
            ],
        ];

        $phoneCounter = 1;

        foreach ($namesByCities as $cityId => $names) {
            foreach ($names as $person) {
                DB::table('users')->insert([
                    'name' => $person['name'],
                    'email' => 'user'.str_pad((string) $phoneCounter, 3, '0', STR_PAD_LEFT).'@companion.test',
                    'phone' => '+770000'.str_pad((string) $phoneCounter, 5, '0', STR_PAD_LEFT),
                    'age' => rand(20, 40),
                    'gender' => $person['gender']->value,
                    'password' => $password,
                    'city_id' => $cityId,
                    'status' => 'active',
                    'user_type_id' => $clientTypeId,
                    'phone_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $phoneCounter++;
            }
        }
    }

    private function seedHangoutRequests(): void
    {
        $now = now();
        $activityTypeIds = DB::table('activity_types')->pluck('id')->toArray();

        $cityNames = DB::table('city_translations')
            ->where('language_code', 'en')
            ->pluck('city_id', 'name');

        $cities = [
            $cityNames['Almaty'],
            $cityNames['Astana'],
            $cityNames['Shymkent'],
        ];

        $notes = [
            'Looking for company!',
            'First time, would be fun together',
            'Anyone free today?',
            'Let\'s hang out!',
            'Relaxed vibes only',
            null,
            null,
        ];

        // Build a lookup of places by city_id and activity_type_id
        $placesWithActivities = DB::table('places')
            ->join('activity_type_place', 'places.id', '=', 'activity_type_place.place_id')
            ->get(['places.id as place_id', 'places.city_id', 'activity_type_place.activity_type_id']);

        $placesByCityAndActivity = [];
        foreach ($placesWithActivities as $row) {
            $key = $row->city_id . '_' . $row->activity_type_id;
            $placesByCityAndActivity[$key][] = $row->place_id;
        }

        $users = DB::table('users')
            ->whereIn('city_id', $cities)
            ->get(['id', 'city_id']);

        $counter = 0;
        foreach ($users as $user) {
            $selectedActivities = collect($activityTypeIds)->shuffle()->take(rand(1, 3));

            foreach ($selectedActivities as $activityTypeId) {
                // Assign a place to ~50% of hangout requests (matching city + activity type)
                $placeId = null;
                if ($counter % 2 === 0) {
                    $key = $user->city_id . '_' . $activityTypeId;
                    if (! empty($placesByCityAndActivity[$key])) {
                        $candidates = $placesByCityAndActivity[$key];
                        $placeId = $candidates[array_rand($candidates)];
                    }
                }
                $counter++;

                DB::table('hangout_requests')->insert([
                    'user_id' => $user->id,
                    'city_id' => $user->city_id,
                    'activity_type_id' => $activityTypeId,
                    'place_id' => $placeId,
                    'date' => $now->copy()->addDays(rand(0, 10))->format('Y-m-d'),
                    'time' => sprintf('%02d:00', rand(10, 22)),
                    'status' => HangoutRequestStatus::Open->value,
                    'notes' => $notes[array_rand($notes)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
