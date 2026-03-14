<?php

namespace App\Filament\Resources\PlaceDiscountResource\Pages;

use App\Filament\Resources\PlaceDiscountResource;
use Filament\Resources\Pages\ListRecords;

class ListPlaceDiscounts extends ListRecords
{
    protected static string $resource = PlaceDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
