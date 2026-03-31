<?php

namespace App\Filament\Resources\PlacePromotionResource\Pages;

use App\Filament\Resources\PlacePromotionResource;
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
