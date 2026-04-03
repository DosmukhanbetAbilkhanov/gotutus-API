<?php

namespace App\Filament\Resources\LegalPageResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\LegalPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalPage extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = LegalPageResource::class;
}
