<?php

namespace App\Filament\Resources\PlaceDiscountResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\PlaceDiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlaceDiscount extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = PlaceDiscountResource::class;
}
