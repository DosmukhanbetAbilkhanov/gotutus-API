<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlaceResource;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlaceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $places = Place::query()
            ->with(['translations', 'activeDiscount'])
            ->inCity($request->user()->city_id)
            ->when($request->query('activity_type_id'), fn ($q, $id) => $q->forActivityType((int) $id))
            ->get()
            ->sortByDesc(fn ($place) => $place->activeDiscount !== null)
            ->values();

        return PlaceResource::collection($places);
    }
}
