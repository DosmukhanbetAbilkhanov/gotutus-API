<?php

namespace App\Providers;

use App\Models\UserPhoto;
use App\Observers\UserPhotoObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        UserPhoto::observe(UserPhotoObserver::class);
    }
}
