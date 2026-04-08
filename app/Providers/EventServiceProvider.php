<?php

namespace App\Providers;

use App\Events\NewReservation;
use App\Events\DeleteReservation;
use App\Events\UpdateReservation;
use App\Events\NewCompany;
use App\Events\UpdateCompany;
use App\Events\UpdateCompanyCaptain;
use App\Listeners\SendWebhookNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewReservation::class => [
            SendWebhookNotification::class,
        ],
        DeleteReservation::class => [
            SendWebhookNotification::class,
        ],
        UpdateReservation::class => [
            SendWebhookNotification::class,
        ],
        NewCompany::class => [
            SendWebhookNotification::class,
        ],
        UpdateCompany::class => [
            SendWebhookNotification::class,
        ],
        UpdateCompanyCaptain::class => [
            SendWebhookNotification::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
