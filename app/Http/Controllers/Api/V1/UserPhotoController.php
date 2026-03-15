<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreUserPhotoRequest;
use App\Http\Resources\Api\V1\UserPhotoResource;
use App\Models\UserPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserPhotoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $photos = $request->user()->photos()->latest()->get();

        return UserPhotoResource::collection($photos);
    }

    public function store(StoreUserPhotoRequest $request): JsonResponse
    {
        $path = $request->file('photo')->store('user-photos', 'public');

        $photo = $request->user()->photos()->create([
            'photo_url' => $path,
        ]);

        return response()->json([
            'message' => __('photo.uploaded'),
            'data' => new UserPhotoResource($photo),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Request $request, UserPhoto $photo): JsonResponse
    {
        if ($photo->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        Storage::disk('public')->delete($photo->photo_url);
        $photo->delete();

        return response()->json([
            'message' => __('photo.deleted'),
        ]);
    }
}
