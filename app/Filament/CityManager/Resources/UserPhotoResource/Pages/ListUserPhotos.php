<?php

namespace App\Filament\CityManager\Resources\UserPhotoResource\Pages;

use App\Filament\CityManager\Resources\UserPhotoResource;
use Filament\Resources\Pages\ListRecords;

class ListUserPhotos extends ListRecords
{
    protected static string $resource = UserPhotoResource::class;
}
