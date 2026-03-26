<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create-admin', function () {

        $now = now();
        $password = Hash::make('password');
      // Seed admin user
        DB::table('users')->insert([
            'name' => 'Dos',
            'email' => 'administrator@tanys.app',
            'phone' => '+77078835953',
            'age' => 30,
            'gender' => 'male',
            'password' => $password,
            'city_id' => 1,
            'status' => 'active',
            'user_type_id' => 2,
            'phone_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

});

// Test notification routes — remove before production!
require __DIR__.'/test_notifications.php';
