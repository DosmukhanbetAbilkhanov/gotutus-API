<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ActivityTypeResource;
use App\Models\ActivityType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $activityTypes = ActivityType::active()->with('translations')->get();

        return ActivityTypeResource::collection($activityTypes);
    }
}
