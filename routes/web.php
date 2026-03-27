<?php

use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create-admin', function () {


        // $now = now();

        // $activityTypes = [
        //     ['slug' => 'beer', 'icon' => "\u{1F37A}", 'ru' => 'Пиво', 'kz' => 'Сыра', 'en' => 'Beer'],
        //     ['slug' => 'coffee', 'icon' => "\u{2615}", 'ru' => 'Кофе', 'kz' => 'Кофе', 'en' => 'Coffee'],
        //     ['slug' => 'sushi', 'icon' => "\u{2615}", 'ru' => ' Суши', 'kz' => ' Суши', 'en' => 'Sushi'],
        //     ['slug' => 'fast_food', 'icon' => "\u{2615}", 'ru' => 'Фаст-Фуд', 'kz' => 'Фаст-Фуд', 'en' => 'Fast Food'],
        //     ['slug' => 'kumys', 'icon' => "\u{2615}", 'ru' => 'Кумыс', 'kz' => 'Кумыс', 'en' => 'Kumys'],
        //     ['slug' => 'bathhouse', 'icon' => "\u{1F9D6}", 'ru' => 'Баня', 'kz' => 'Монша', 'en' => 'Bathhouse'],
        //     ['slug' => 'walk', 'icon' => "\u{1F6B6}", 'ru' => 'Прогулка', 'kz' => 'Серуен', 'en' => 'Walk'],
        //     ['slug' => 'concert', 'icon' => "\u{1F6B6}", 'ru' => 'Концерт', 'kz' => 'Концерт', 'en' => 'Concert'],
        //     ['slug' => 'bowling', 'icon' => "\u{1F3B3}", 'ru' => 'Боулинг', 'kz' => 'Боулинг', 'en' => 'Bowling'],
        //     ['slug' => 'billiards', 'icon' => "\u{1F3B1}", 'ru' => 'Бильярд', 'kz' => 'Бильярд', 'en' => 'Billiards'],
        //     ['slug' => 'hookah', 'icon' => "\u{1F4A8}", 'ru' => 'Кальян', 'kz' => 'Кальян', 'en' => 'Hookah'],
        //     ['slug' => 'karaoke', 'icon' => "\u{1F3A4}", 'ru' => 'Караоке', 'kz' => 'Караоке', 'en' => 'Karaoke'],
        //     ['slug' => 'restaurant', 'icon' => "\u{1F37D}\u{FE0F}", 'ru' => 'Ресторан', 'kz' => 'Мейрамхана', 'en' => 'Restaurant'],
        //     ['slug' => 'kvest', 'icon' => "\u{26BD}", 'ru' => 'Квест', 'kz' => 'Квест', 'en' => 'Kvest'],
        //     ['slug' => 'paintball', 'icon' => "\u{26BD}", 'ru' => 'Пейнтбол', 'kz' => 'Пейнтбол', 'en' => 'Paintball'],
        //     ['slug' => 'pc_club', 'icon' => "\u{26BD}", 'ru' => 'Компьютерный Клуб', 'kz' => 'Компьютерный Клуб', 'en' => 'PC Club'],
        //     ['slug' => 'quiz', 'icon' => "\u{26BD}", 'ru' => 'Квиз', 'kz' => 'Квиз', 'en' => 'Quiz'],
        //     ['slug' => 'tennis', 'icon' => "\u{26BD}", 'ru' => 'Теннис', 'kz' => 'Теннис', 'en' => 'Tennis'],
        //     ['slug' => 'football', 'icon' => "\u{26BD}", 'ru' => 'Футбол', 'kz' => 'Футбол', 'en' => 'Football'],
        //     ['slug' => 'cinema', 'icon' => "\u{1F3AC}", 'ru' => 'Кино', 'kz' => 'Кино', 'en' => 'Cinema'],
        //     ['slug' => 'board-games', 'icon' => "\u{1F3B2}", 'ru' => 'Настолки', 'kz' => 'Үстел ойындары', 'en' => 'Board Games'],
        // ];

        // foreach ($activityTypes as $type) {
        //     $typeId = DB::table('activity_types')->insertGetId([
        //         'slug' => $type['slug'],
        //         'bg_photo' => null,
        //         'icon' => $type['icon'],
        //         'is_active' => true,
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ]);

        //     DB::table('activity_type_translations')->insert([
        //         ['activity_type_id' => $typeId, 'language_code' => 'ru', 'name' => $type['ru']],
        //         ['activity_type_id' => $typeId, 'language_code' => 'kz', 'name' => $type['kz']],
        //         ['activity_type_id' => $typeId, 'language_code' => 'en', 'name' => $type['en']],
        //     ]);
        // }

        // $cities = [
        //     ['en' => 'Aktobe', 'ru' => 'Актобе', 'kz' => 'Ақтөбе'],
        // ];

        // foreach ($cities as $city) {
        //     $cityId = DB::table('cities')->insertGetId([
        //         'is_active' => true,
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ]);

        //     DB::table('city_translations')->insert([
        //         ['city_id' => $cityId, 'language_code' => 'ru', 'name' => $city['ru']],
        //         ['city_id' => $cityId, 'language_code' => 'kz', 'name' => $city['kz']],
        //         ['city_id' => $cityId, 'language_code' => 'en', 'name' => $city['en']],
        //     ]);
        // }

        //  $types = [
        //     ['slug' => 'client', 'name' => 'Client'],
        //     ['slug' => 'admin', 'name' => 'Admin'],
        //     ['slug' => 'city_manager', 'name' => 'City Manager'],
        // ];

        // foreach ($types as $type) {
        //     DB::table('user_types')->insertOrIgnore([
        //         'slug' => $type['slug'],
        //         'name' => $type['name'],
        //         'created_at' => $now,
        //         'updated_at' => $now,
        //     ]);
        // }



        // DB::table('users')->insert([
        //     'name' => 'Dos',
        //     'email' => 'administrator@tanys.app',
        //     'phone' => '+77078835953',
        //     'age' => 30,
        //     'gender' => 'male',
        //     'password' => Hash::make('password'),
        //     'city_id' => $cityId ,
        //     'status' => 'active',
        //     'user_type_id' => 2,
        //     'phone_verified_at' => $now,
        //     'created_at' => $now,
        //     'updated_at' => $now,
        // ]);

});

       
    

// Test notification routes — remove before production!
require __DIR__.'/test_notifications.php';
