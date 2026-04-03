<?php

namespace App\Filament\Resources\PlaceAdvertisementResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\PlaceAdvertisementResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlaceAdvertisement extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = PlaceAdvertisementResource::class;
}
