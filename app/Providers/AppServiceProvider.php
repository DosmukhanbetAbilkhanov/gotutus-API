<?php

namespace App\Providers;

use App\Models\PhotoVerification;
use App\Models\PlaceDiscount;
use App\Models\UserPhoto;
use App\Observers\PhotoVerificationObserver;
use App\Observers\PlaceDiscountObserver;
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
        PhotoVerification::observe(PhotoVerificationObserver::class);
        PlaceDiscount::observe(PlaceDiscountObserver::class);
    }
}
