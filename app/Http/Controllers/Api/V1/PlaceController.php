<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlaceResource;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class PlaceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $cityId = $request->user()->city_id;
        $activityTypeId = $request->query('activity_type_id');
        $cacheKey = "places:city:{$cityId}:at:{$activityTypeId}";

        $places = Cache::remember($cacheKey, 1800, function () use ($cityId, $activityTypeId) {
            return Place::query()
                ->with(['translations', 'activeDiscount', 'activityTypes.translations', 'workingHours'])
                ->inCity($cityId)
                ->when($activityTypeId, fn ($q, $id) => $q->forActivityType((int) $id))
                ->get()
                ->sortByDesc(fn ($place) => $place->activeDiscount !== null)
                ->values();
        });

        return PlaceResource::collection($places);
    }
}
