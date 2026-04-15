<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdminNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $cityId,
        public string $title,
        public string $body,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        User::active()
            ->inCity($this->cityId)
            ->select(['id'])
            ->chunk(100, function ($users) use ($notificationService) {
                foreach ($users as $user) {
                    try {
                        $notificationService->send(
                            user: $user,
                            type: 'system_announcement',
                            title: $this->title,
                            body: $this->body,
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Failed to send admin notification', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
