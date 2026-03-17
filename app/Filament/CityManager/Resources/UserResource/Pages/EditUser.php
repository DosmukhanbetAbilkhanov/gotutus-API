<?php

namespace App\Filament\CityManager\Resources\UserResource\Pages;

use App\Filament\CityManager\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
        ];
    }
}
