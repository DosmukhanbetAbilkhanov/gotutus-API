<?php

namespace App\Filament\Resources\ActivityTypeResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\ActivityTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivityType extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = ActivityTypeResource::class;
}
