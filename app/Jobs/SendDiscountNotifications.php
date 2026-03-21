<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PlaceDiscount;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDiscountNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public PlaceDiscount $discount,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $this->discount->loadMissing('place.translations');
        $place = $this->discount->place;

        if (!$place) {
            return;
        }

        $placeName = $place->translations
            ->firstWhere('language_code', 'en')?->name
            ?? "Place #{$place->id}";

        $title = __('notifications.discount_created_title');
        $body = __('notifications.discount_created_body', [
            'place' => $placeName,
            'percent' => $this->discount->discount_percent,
        ]);

        $data = [
            'place_id' => (string) $place->id,
            'discount_id' => (string) $this->discount->id,
            'discount_percent' => (string) $this->discount->discount_percent,
        ];

        User::active()
            ->inCity($place->city_id)
            ->select(['id'])
            ->chunk(100, function ($users) use ($notificationService, $title, $body, $data) {
                foreach ($users as $user) {
                    try {
                        $notificationService->send(
                            user: $user,
                            type: 'discount_created',
                            title: $title,
                            body: $body,
                            data: $data,
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Failed to send discount notification', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
