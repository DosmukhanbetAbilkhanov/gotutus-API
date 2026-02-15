<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CityResource;
use App\Models\City;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cities = City::active()->with('translations')->get();

        return CityResource::collection($cities);
    }
}
