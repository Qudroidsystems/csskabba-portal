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

// Queued jobs (e.g. fee reminders) on hosts without a permanent queue worker.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping(5);
