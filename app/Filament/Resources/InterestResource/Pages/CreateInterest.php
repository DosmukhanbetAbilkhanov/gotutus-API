<?php

namespace App\Filament\Resources\InterestResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\InterestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInterest extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = InterestResource::class;
}
