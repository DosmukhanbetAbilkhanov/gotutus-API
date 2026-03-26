<?php

use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create-admin', function () {

        $now = now();
        $cities = [
            ['en' => 'Aktobe', 'ru' => 'Актобе', 'kz' => 'Ақтөбе'],
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



        DB::table('users')->insert([
            'name' => 'Dos',
            'email' => 'administrator@tanys.app',
            'phone' => '+77078835953',
            'age' => 30,
            'gender' => 'male',
            'password' => Hash::make('password'),
            'city_id' => $cityId ,
            'status' => 'active',
            'user_type_id' => 2,
            'phone_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

});


        

       
    

// Test notification routes — remove before production!
require __DIR__.'/test_notifications.php';
