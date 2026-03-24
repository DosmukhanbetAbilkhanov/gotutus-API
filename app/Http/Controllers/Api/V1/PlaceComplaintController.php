<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Complaint\StorePlaceComplaintRequest;
use App\Models\HangoutRequest;
use App\Models\PlaceComplaint;
use App\Models\User;
use App\Models\UserType;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PlaceComplaintController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = request()->user();

        $complaints = $hangoutRequest->placeComplaints()
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'data' => $complaints->map(fn ($c) => [
                'id' => $c->id,
                'hangout_request_id' => $c->hangout_request_id,
                'place_id' => $c->place_id,
                'type' => $c->type->value,
                'description' => $c->description,
                'status' => $c->status->value,
                'created_at' => $c->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function store(StorePlaceComplaintRequest $request, HangoutRequest $hangoutRequest): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Check for duplicate complaint of same type
        $existing = $hangoutRequest->placeComplaints()
            ->where('user_id', $user->id)
            ->where('type', $validated['type'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already filed this type of complaint for this hangout.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $complaint = PlaceComplaint::create([
            'hangout_request_id' => $hangoutRequest->id,
            'user_id' => $user->id,
            'place_id' => $hangoutRequest->place_id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        // Notify city managers of the place's city
        $place = $hangoutRequest->place;
        if ($place) {
            $cityManagers = User::where('city_id', $place->city_id)
                ->whereHas('userType', fn ($q) => $q->where('slug', UserType::SLUG_CITY_MANAGER))
                ->get();

            foreach ($cityManagers as $manager) {
                $this->notificationService->send(
                    $manager,
                    'place_complaint_filed',
                    'New Place Complaint',
                    "A complaint has been filed about {$place->name}: {$validated['type']}",
                    [
                        'place_id' => $place->id,
                        'complaint_id' => $complaint->id,
                        'hangout_request_id' => $hangoutRequest->id,
                        'complaint_type' => $validated['type'],
                    ],
                );
            }
        }

        return response()->json([
            'message' => 'Complaint submitted successfully. The city manager will review it.',
            'data' => [
                'id' => $complaint->id,
                'hangout_request_id' => $complaint->hangout_request_id,
                'place_id' => $complaint->place_id,
                'type' => $complaint->type->value,
                'description' => $complaint->description,
                'status' => $complaint->status->value,
                'created_at' => $complaint->created_at?->toIso8601String(),
            ],
        ], Response::HTTP_CREATED);
    }
}
