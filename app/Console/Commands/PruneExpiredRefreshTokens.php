<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TokenService;
use Illuminate\Console\Command;

class PruneExpiredRefreshTokens extends Command
{
    protected $signature = 'tokens:prune-expired-refresh';

    protected $description = 'Delete expired refresh tokens from the database';

    public function handle(): void
    {
        $count = TokenService::pruneExpiredRefreshTokens();

        $this->info("Pruned {$count} expired refresh tokens.");
    }
}
