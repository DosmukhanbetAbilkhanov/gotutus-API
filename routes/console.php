<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Email the admin if a scheduled command fails (non-zero exit / exception).
// Applied to infrequent jobs only; high-frequency jobs (mark-offline,
// health-check) would flood — their exceptions are still caught + throttled by
// the global exception reporter (App\Support\ExceptionAlerter).
$alertEmail = config('services.health.alert_email');

Schedule::command('hangouts:close-expired')->dailyAt('00:05')->emailOutputOnFailure($alertEmail);
Schedule::command('users:mark-offline')->everyMinute();
Schedule::command('sanctum:prune-expired --hours=24')->daily()->emailOutputOnFailure($alertEmail);
Schedule::command('tokens:prune-expired-refresh')->daily()->emailOutputOnFailure($alertEmail);
Schedule::command('feedback:send-requests')->dailyAt('10:00')->emailOutputOnFailure($alertEmail);
Schedule::command('feedback:send-reminders')->dailyAt('14:00')->emailOutputOnFailure($alertEmail);
Schedule::command('system:health-check')->everyFiveMinutes();
