<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::macro('betweenDates', function ($start, $end) {
            return $this->toDateString() >= $start->toDateString() &&
                   $this->toDateString() <= $end->toDateString();
        });
    }
}
