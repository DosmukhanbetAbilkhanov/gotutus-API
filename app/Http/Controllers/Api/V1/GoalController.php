<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GoalResource;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GoalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $activityTypeId = $request->query('activity_type_id');

        $goals = Cache::remember(
            'goals:' . ($activityTypeId ?? 'all'),
            3600,
            function () use ($activityTypeId) {
                $query = Goal::active()->with('translations')->orderBy('sort_order');

                if ($activityTypeId) {
                    $query->whereHas('activityTypes', function ($q) use ($activityTypeId) {
                        $q->where('activity_types.id', (int) $activityTypeId);
                    });
                }

                return $query->get();
            }
        );

        return GoalResource::collection($goals);
    }
}
