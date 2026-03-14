<?php

namespace App\Filament\Resources\HangoutRequestResource\Pages;

use App\Filament\Resources\HangoutRequestResource;
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
