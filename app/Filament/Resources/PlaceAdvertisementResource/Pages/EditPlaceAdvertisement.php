<?php

namespace App\Filament\Resources\PlaceAdvertisementResource\Pages;

use App\Filament\Resources\PlaceAdvertisementResource;
use Filament\Resources\Pages\EditRecord;

class EditPlaceAdvertisement extends EditRecord
{
    protected static string $resource = PlaceAdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
