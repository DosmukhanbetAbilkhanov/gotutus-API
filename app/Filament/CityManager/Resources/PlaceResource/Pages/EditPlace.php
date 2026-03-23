<?php

namespace App\Filament\CityManager\Resources\PlaceResource\Pages;

use App\Filament\CityManager\Resources\PlaceResource;
use Filament\Resources\Pages\EditRecord;

class EditPlace extends EditRecord
{
    protected static string $resource = PlaceResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $place = $this->getRecord();

        if ($place->workingHours()->count() === 0) {
            for ($i = 0; $i <= 6; $i++) {
                $place->workingHours()->create([
                    'day_of_week' => $i,
                    'open_time' => null,
                    'close_time' => null,
                ]);
            }

            $this->fillForm();
        }
    }
}
