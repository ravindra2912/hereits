<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Log::info("Run schedule at " . date('y-m-d-h-i-s'));

// Schedule::call(function () {
//     Log::info('This job runs every minute or more'); 
// })->everySecond()->onFailure(function () {
//     Log::error('This job failed to run at ' . now());
// });

// Schedule::command('app:appoinment-auto-cancel')->everySecond();
Schedule::command('app:appoinment-auto-cancel')->dailyAt('00:10');
Schedule::command('app:subscription-check-task')->dailyAt('00:01');
Schedule::command('queue:work --stop-when-empty')->everyMinute();
// Schedule::command('reverb:start --stop-when-empty')->withoutOverlapping();
