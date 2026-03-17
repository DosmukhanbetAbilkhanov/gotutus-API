<?php

namespace App\Filament\CityManager\Resources\HangoutRequestResource\Pages;

use App\Filament\CityManager\Resources\HangoutRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewHangoutRequest extends ViewRecord
{
    protected static string $resource = HangoutRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}
