<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Console\Command;

class DeleteTestUsers extends Command
{
    protected $signature = 'users:delete-test {--force : Skip confirmation}';

    protected $description = 'Delete all test users (email ending with @companion.test)';

    public function handle(): void
    {
        $testUsers = User::where('email', 'like', '%'.ProductionSeeder::TEST_EMAIL_DOMAIN)->get();

        if ($testUsers->isEmpty()) {
            $this->info('No test users found.');

            return;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Phone', 'City ID'],
            $testUsers->map(fn (User $u) => [$u->id, $u->name, $u->email, $u->phone, $u->city_id])
        );

        if (! $this->option('force') && ! $this->confirm("Delete {$testUsers->count()} test users and all related data?")) {
            $this->info('Cancelled.');

            return;
        }

        $count = 0;
        foreach ($testUsers as $user) {
            $user->tokens()->delete();
            $user->delete();
            $count++;
        }

        $this->info("{$count} test users deleted.");
    }
}
