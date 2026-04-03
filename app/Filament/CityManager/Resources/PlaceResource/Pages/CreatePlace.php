<?php

namespace App\Filament\CityManager\Resources\PlaceResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\CityManager\Resources\PlaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlace extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = PlaceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['city_id'] = auth()->user()->city_id;

        return $data;
    }
}
