<?php

namespace App\Filament\Resources\CityManagerResource\Pages;

use App\Filament\Resources\CityManagerResource;
use Filament\Resources\Pages\ListRecords;

class ListCityManagers extends ListRecords
{
    protected static string $resource = CityManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
