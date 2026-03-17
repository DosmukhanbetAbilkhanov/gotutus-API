<?php

namespace App\Filament\CityManager\Resources\BlockedUserResource\Pages;

use App\Filament\CityManager\Resources\BlockedUserResource;
use Filament\Resources\Pages\ListRecords;

class ListBlockedUsers extends ListRecords
{
    protected static string $resource = BlockedUserResource::class;
}
