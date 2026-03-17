<?php

namespace App\Filament\CityManager\Resources\PlaceResource\Pages;

use App\Filament\CityManager\Resources\PlaceResource;
use Filament\Resources\Pages\ListRecords;

class ListPlaces extends ListRecords
{
    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
