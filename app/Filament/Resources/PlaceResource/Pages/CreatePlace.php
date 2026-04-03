<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\PlaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlace extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = PlaceResource::class;
}
