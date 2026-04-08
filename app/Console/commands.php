<?php

use Illuminate\Foundation\Configuration\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
return Artisan::resolveCommands([
    App\Console\Commands\DemoCron::class,
    App\Console\Commands\FreeSpotsCron::class,
    App\Console\Commands\ChangeOfIntervalsCron::class,
    App\Console\Commands\AzuriranjePrivrednihSubjekataCron::class,
]);
*/

// 🕒 Schedule the commands:
//Schedule::command('endOfInterval:cron')->dailyAt('14:00');
//Schedule::command('changeOfIntervals:cron')->dailyAt('12:00');

// Optional/Commented (Uncomment if needed)
// Schedule::command('freeSpots:cron')->dailyAt('08:00');
// Schedule::command('azuriranjePrivrednihSubjekata:cron')->dailyAt('03:00');
