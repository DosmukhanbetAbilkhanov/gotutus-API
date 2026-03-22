<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlaceAdvertisementResource;
use App\Models\PlaceAdvertisement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlaceAdvertisementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $ads = PlaceAdvertisement::query()
            ->with([
                'place.translations',
                'place.activeDiscount',
                'place.activityTypes.translations',
                'activityType.translations',
            ])
            ->active()
            ->when($request->query('city_id'), fn ($q, $id) => $q->inCity((int) $id))
            ->when(
                $request->query('activity_type_id'),
                fn ($q, $id) => $q->where(function ($q) use ($id) {
                    $q->where('activity_type_id', (int) $id)
                      ->orWhereNull('activity_type_id');
                })
            )
            ->orderBy('sort_order')
            ->get();

        return PlaceAdvertisementResource::collection($ads);
    }
}
