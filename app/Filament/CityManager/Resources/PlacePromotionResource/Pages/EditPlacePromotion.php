<?php

namespace App\Filament\CityManager\Resources\PlacePromotionResource\Pages;

use App\Filament\CityManager\Resources\PlacePromotionResource;
use Filament\Resources\Pages\EditRecord;

class EditPlacePromotion extends EditRecord
{
    protected static string $resource = PlacePromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
