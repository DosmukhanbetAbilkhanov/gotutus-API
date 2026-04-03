<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\CityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCity extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = CityResource::class;
}
