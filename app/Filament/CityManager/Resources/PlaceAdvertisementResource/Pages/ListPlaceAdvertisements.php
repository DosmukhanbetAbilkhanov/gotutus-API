<?php

namespace App\Filament\CityManager\Resources\PlaceAdvertisementResource\Pages;

use App\Filament\CityManager\Resources\PlaceAdvertisementResource;
use Filament\Resources\Pages\ListRecords;

class ListPlaceAdvertisements extends ListRecords
{
    protected static string $resource = PlaceAdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
