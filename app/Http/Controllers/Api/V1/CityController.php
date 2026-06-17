<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\City\ResolveCityRequest;
use App\Http\Resources\Api\V1\CityResource;
use App\Models\City;
use App\Services\CityResolver;
use Illuminate\Http\JsonResponse;
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

    /**
     * Resolve device coordinates to a supported city.
     *
     * Used during onboarding/registration to determine (server-side) which
     * city a user is physically in. Public — runs before authentication.
     */
    public function resolve(ResolveCityRequest $request, CityResolver $resolver): JsonResponse
    {
        $city = $resolver->resolve(
            (float) $request->validated('latitude'),
            (float) $request->validated('longitude'),
        );

        if ($city === null) {
            return response()->json(['supported' => false]);
        }

        return response()->json([
            'supported' => true,
            'city' => new CityResource($city),
        ]);
    }
}
