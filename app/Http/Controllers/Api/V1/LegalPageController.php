<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LegalPageResource;
use App\Models\LegalPage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LegalPageController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $page = LegalPage::getActive($slug);

        if (! $page) {
            return response()->json([
                'message' => __('legal.page_not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new LegalPageResource($page),
        ]);
    }
}
