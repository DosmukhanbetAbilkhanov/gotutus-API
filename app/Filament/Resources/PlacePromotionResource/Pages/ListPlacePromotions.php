<?php

namespace App\Filament\Resources\PlacePromotionResource\Pages;

use App\Filament\Resources\PlacePromotionResource;
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
