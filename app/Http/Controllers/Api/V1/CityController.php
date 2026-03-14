<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CityResource;
use App\Models\City;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cities = Cache::remember('cities:active', 3600, function () {
            return City::active()->with('translations')->get();
        });

        return CityResource::collection($cities);
    }
}
