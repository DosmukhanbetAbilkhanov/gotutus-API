<?php

namespace App\Filament\Resources\HangoutRequestResource\Pages;

use App\Filament\Resources\HangoutRequestResource;
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
