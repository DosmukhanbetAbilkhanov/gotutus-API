<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MarkInactiveUsersOffline extends Command
{
    protected $signature = 'users:mark-offline';

    protected $description = 'Mark users as offline if they have been inactive for more than 5 minutes';

    public function handle(): void
    {
        $count = User::where('is_online', true)
            ->where('last_seen_at', '<', now()->subMinutes(5))
            ->update(['is_online' => false]);

        $this->info("Marked {$count} users as offline.");
    }
}
