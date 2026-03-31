<?php

namespace App\Filament\CityManager\Resources\PlacePromotionResource\Pages;

use App\Filament\CityManager\Resources\PlacePromotionResource;
use Filament\Resources\Pages\ListRecords;

class ListPlacePromotions extends ListRecords
{
    protected static string $resource = PlacePromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
