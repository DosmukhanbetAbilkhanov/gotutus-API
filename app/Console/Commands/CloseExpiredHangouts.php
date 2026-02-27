<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HangoutRequestStatus;
use App\Models\HangoutRequest;
use Illuminate\Console\Command;

class CloseExpiredHangouts extends Command
{
    protected $signature = 'hangouts:close-expired';

    protected $description = 'Auto-close hangouts past their scheduled date';

    public function handle(): void
    {
        $count = HangoutRequest::query()
            ->where('status', HangoutRequestStatus::Open)
            ->where('date', '<', now()->toDateString())
            ->update(['status' => HangoutRequestStatus::Closed->value]);

        $this->info("Closed {$count} expired hangouts.");
    }
}
