<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;

class CityResolver
{
    /**
     * Resolve a set of GPS coordinates to a supported city.
     *
     * Returns the nearest active city whose center is within its configured
     * radius of the given point, or null when the point is outside every
     * supported city.
     */
    public function resolve(float $latitude, float $longitude): ?City
    {
        $cities = City::active()
            ->whereNotNull('center_latitude')
            ->whereNotNull('center_longitude')
            ->with('translations')
            ->get();

        $best = null;
        $bestDistance = null;

        foreach ($cities as $city) {
            $distanceKm = $this->haversineKm(
                $latitude,
                $longitude,
                (float) $city->center_latitude,
                (float) $city->center_longitude,
            );

            if ($distanceKm > $city->radius_km) {
                continue;
            }

            if ($bestDistance === null || $distanceKm < $bestDistance) {
                $best = $city;
                $bestDistance = $distanceKm;
            }
        }

        return $best;
    }

    /**
     * Great-circle distance between two points in kilometres.
     */
    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * asin(min(1.0, sqrt($a)));
    }
}
