<?php

namespace App\Filament\Resources\CityManagerResource\Pages;

use App\Filament\Resources\CityManagerResource;
use Filament\Resources\Pages\EditRecord;

class EditCityManager extends EditRecord
{
    protected static string $resource = CityManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->before(function ($record) {
                    $record->tokens()->delete();
                }),
        ];
    }
}
