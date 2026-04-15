<?php

namespace App\Filament\Resources\AdminNotificationResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\AdminNotificationResource;
use App\Jobs\SendAdminNotification;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminNotification extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = AdminNotificationResource::class;

    protected ?string $heading = 'Send Notification';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sent_by'] = auth()->id();
        $data['sent_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        SendAdminNotification::dispatch(
            $this->record->city_id,
            $this->record->title,
            $this->record->body,
        );
    }
}
