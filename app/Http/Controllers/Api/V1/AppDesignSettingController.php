<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppDesignSettingResource;
use App\Models\AppDesignSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AppDesignSettingController extends Controller
{
    public function show(Request $request): AppDesignSettingResource|JsonResponse
    {
        $setting = Cache::remember('app_design_settings:active', 3600, function () {
            return AppDesignSetting::active();
        });

        // If no active settings, return defaults
        if (! $setting) {
            $setting = new AppDesignSetting(AppDesignSetting::defaults());
            $setting->version = md5(json_encode(AppDesignSetting::defaults()));
        }

        // ETag / If-None-Match support
        $clientVersion = $request->header('If-None-Match');
        if ($clientVersion && $clientVersion === $setting->version) {
            return response()->json(null, 304);
        }

        return (new AppDesignSettingResource($setting))
            ->response()
            ->header('ETag', $setting->version);
    }
}
