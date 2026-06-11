<?php

namespace App\Filament\Resources\PhotoVerificationResource\Pages;

use App\Filament\Resources\PhotoVerificationResource;
use Filament\Resources\Pages\EditRecord;

class EditPhotoVerification extends EditRecord
{
    protected static string $resource = PhotoVerificationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['reviewed_by'] = auth()->id();
        $data['reviewed_at'] = now();

        return $data;
    }
}
