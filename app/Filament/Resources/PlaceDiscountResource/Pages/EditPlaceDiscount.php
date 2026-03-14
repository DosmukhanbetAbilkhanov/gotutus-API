<?php

namespace App\Filament\Resources\PlaceDiscountResource\Pages;

use App\Filament\Resources\PlaceDiscountResource;
use Filament\Resources\Pages\EditRecord;

class EditPlaceDiscount extends EditRecord
{
    protected static string $resource = PlaceDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
