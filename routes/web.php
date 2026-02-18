<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test notification routes — remove before production!
require __DIR__.'/test_notifications.php';
