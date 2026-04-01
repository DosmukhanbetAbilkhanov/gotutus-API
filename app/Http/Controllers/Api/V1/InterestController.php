<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\InterestResource;
use App\Models\Interest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class InterestController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $interests = Cache::remember('interests:active', 3600, function () {
            return Interest::active()
                ->with('translations')
                ->orderBy('sort_order')
                ->get();
        });

        return InterestResource::collection($interests);
    }
}
