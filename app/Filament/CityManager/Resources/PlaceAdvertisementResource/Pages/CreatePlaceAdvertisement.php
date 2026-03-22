<?php

namespace App\Filament\CityManager\Resources\PlaceAdvertisementResource\Pages;

use App\Filament\CityManager\Resources\PlaceAdvertisementResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlaceAdvertisement extends CreateRecord
{
    protected static string $resource = PlaceAdvertisementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['city_id'] = auth()->user()->city_id;

        return $data;
    }
}
