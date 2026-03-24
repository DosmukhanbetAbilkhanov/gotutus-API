<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('hangouts:close-expired')->dailyAt('00:05');
Schedule::command('users:mark-offline')->everyMinute();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('tokens:prune-expired-refresh')->daily();
Schedule::command('feedback:send-requests')->dailyAt('10:00');
Schedule::command('feedback:send-reminders')->dailyAt('14:00');
