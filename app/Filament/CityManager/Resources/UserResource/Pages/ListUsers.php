<?php

namespace App\Filament\CityManager\Resources\UserResource\Pages;

use App\Filament\CityManager\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
}
