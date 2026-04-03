<?php

namespace App\Filament\Resources\PlacePromotionResource\Pages;

use App\Filament\Concerns\RedirectsToListOnCreate;
use App\Filament\Resources\PlacePromotionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlacePromotion extends CreateRecord
{
    use RedirectsToListOnCreate;

    protected static string $resource = PlacePromotionResource::class;
}
