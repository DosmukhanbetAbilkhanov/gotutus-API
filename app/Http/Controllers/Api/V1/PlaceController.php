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
        $query = Place::query()
            ->with(['translations', 'city.translations', 'activityTypes.translations']);

        // Filter by city (default to user's city)
        $cityId = $request->input('city_id', $request->user()->city_id);
        $query->inCity($cityId);

        // Filter by activity type if provided
        if ($request->filled('activity_type_id')) {
            $query->forActivityType($request->input('activity_type_id'));
        }

        $places = $query->get();

        return PlaceResource::collection($places);
    }
}
