<?php

namespace App\Filament\CityManager\Resources\ReportResource\Pages;

use App\Filament\CityManager\Resources\ReportResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['reviewed_by'] = Auth::id();
        $data['reviewed_at'] = now();
        return $data;
    }
}
