<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-expire pending bookings every 15 minutes
Schedule::command('bookings:expire-pending')->everyFifteenMinutes();

// Auto-complete bookings whose visit_date has passed (daily at 23:55)
Schedule::command('bookings:auto-complete')->dailyAt('23:55');
