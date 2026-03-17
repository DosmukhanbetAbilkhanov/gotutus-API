<?php

namespace App\Filament\CityManager\Resources\HangoutRequestResource\Pages;

use App\Filament\CityManager\Resources\HangoutRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditHangoutRequest extends EditRecord
{
    protected static string $resource = HangoutRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
        ];
    }
}
