<?php

namespace App\Filament\Resources\CityManagerResource\Pages;

use App\Filament\Resources\CityManagerResource;
use App\Models\UserType;
use Filament\Resources\Pages\CreateRecord;

class CreateCityManager extends CreateRecord
{
    protected static string $resource = CityManagerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_type_id'] = UserType::where('slug', UserType::SLUG_CITY_MANAGER)->value('id');
        $data['phone_verified_at'] = now();

        return $data;
    }
}
