<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


//Schedule::command('azuriranjePrivrednihSubjekata:cron')->dailyAt('03:00');
//Schedule::command('finalBill:cron')->dailyAt('10:00');
Schedule::command('freeSpots:cron')->dailyAt('08:00');
Schedule::command('changeOfIntervals:cron')->dailyAt('21:00');
Schedule::command('endOfInterval:cron')->dailyAt('22:00');

Schedule::command('queue:work --stop-when-empty')->everyMinute();
