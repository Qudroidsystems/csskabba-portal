<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Scheduler — needs ONE cPanel cron job running every minute:
|   * * * * * cd /path/to/portal && php artisan schedule:run >> /dev/null 2>&1
*/
// School notices: scheduled sends and automatic reminders.
Schedule::command('notices:dispatch')->everyMinute()->withoutOverlapping(15);

// Absence alerts, fee reminders, birthday wishes (each checks its own time).
Schedule::command('messages:auto')->everyFiveMinutes()->withoutOverlapping(30);

// Report cards to parents: generate + send in batches.
Schedule::command('results:send --max=40')->everyMinute()->withoutOverlapping(20);

// Queued jobs (e.g. fee reminders) on hosts without a permanent queue worker.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping(5);

// Government remittance reminders (PAYE, pension, NHF) — 8am daily
Schedule::command('payroll:remittance-reminders')->dailyAt('08:00')->withoutOverlapping(30);
