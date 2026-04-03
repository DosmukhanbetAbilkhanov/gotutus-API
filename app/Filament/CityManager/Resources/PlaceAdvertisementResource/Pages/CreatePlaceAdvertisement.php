<?php

namespace App\Filament\CityManager\Resources\PlaceAdvertisementResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\CityManager\Resources\PlaceAdvertisementResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlaceAdvertisement extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = PlaceAdvertisementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['city_id'] = auth()->user()->city_id;

        return $data;
    }
}
