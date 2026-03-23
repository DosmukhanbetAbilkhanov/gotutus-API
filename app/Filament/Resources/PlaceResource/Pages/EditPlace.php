<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use Filament\Resources\Pages\EditRecord;

class EditPlace extends EditRecord
{
    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $place = $this->getRecord();

        // Seed 7 working hour records if the place has none yet
        // (for places created before the working hours feature)
        if ($place->workingHours()->count() === 0) {
            for ($i = 0; $i <= 6; $i++) {
                $place->workingHours()->create([
                    'day_of_week' => $i,
                    'open_time' => null,
                    'close_time' => null,
                ]);
            }

            // Re-fill form so repeater picks up the new records
            $this->fillForm();
        }
    }
}
