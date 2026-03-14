<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ActivityTypeResource;
use App\Models\ActivityType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ActivityTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $activityTypes = Cache::remember('activity_types:active', 3600, function () {
            return ActivityType::active()->with('translations')->get();
        });

        return ActivityTypeResource::collection($activityTypes);
    }
}
