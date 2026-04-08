<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use App\Events\DeleteReservation;
use App\Events\UpdateReservation;
use App\Events\NewCompany;
use App\Events\NewReservation;
use App\Events\UpdateCompany;
use App\Events\UpdateCompanyCaptain;
use App\Events\NewRunner;
use App\Events\NewRunnerReservation;

class SendWebhookNotification
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UpdateReservation|DeleteReservation|NewCompany|NewReservation|UpdateCompany|UpdateCompanyCaptain|NewRunner|NewRunnerReservation $event): void
    {
        $eventName = class_basename($event); 

        if ($eventName === 'NewReservation') 
        {
            $payload = [
                'event' => 'NewReservation',
                'data'  => [
                    'reservation_id' => (string)$event->reservation->id,
                    'company_key' => $event->reservation->captain->pin,
                    'race_id' => $event->reservation->race->bill_prefix,                    
                    'reserved_places' => $event->reservation->reserved_places,
                ],
            ];
        } 
        elseif ($eventName === 'DeleteReservation') 
        {
            $payload = [
                'event' => 'DeleteReservation',
                'data'  => [
                    'reservation_id' => (string)$event->reservation->id,
                ],
            ];
        } 
        elseif ($eventName === 'UpdateReservation') 
        {
            $payload = [
                'event' => 'UpdateReservation',
                'data'  => [
                    'reservation_id' => (string)$event->reservation->id,
                    'reserved_places' => $event->reservation->reserved_places,
                ],
            ];
        } 
        elseif ($eventName === 'NewCompany') 
        {
            $payload = [
                'event' => 'NewCompany',
                'data'  => [
                    'company_id' => (string)$event->captain->id,
                    'company_key' => $event->captain->pin,
                    'company_name' => $event->captain->company_name,
                    'company_address' => $event->captain->address,
                    'company_city' => $event->captain->city,
                    'company_postcode' => $event->captain->postcode,
					'company_phone' => $event->captain->phone,
					'company_jbkjs' => $event->captain->jbkjs,
					'company_mb' => $event->captain->identification_number,
					'captain_email' => $event->captain->email,					
                    'captain_first_name' => $event->captain->name,
                    'captain_last_name' => $event->captain->last_name,
					'team_name' => $event->captain->team_name,
                ],
            ];
        } 
        elseif ($eventName === 'UpdateCompany') 
        {
            $payload = [
                'event' => 'UpdateCompany',
                'data'  => [
					'company_key' => $event->captain->pin,   
                    'company_address' => $event->captain->address,
                    'company_city' => $event->captain->city,
                    'company_name' => $event->captain->company_name,                                    
                    'company_postcode' => $event->captain->postcode,
					'company_phone' => $event->captain->phone,
					'company_jbkjs' => $event->captain->jbkjs,
					'company_mb' => $event->captain->identification_number,
					'team_name' => $event->captain->team_name,
                ],
            ];
        } 
        elseif ($eventName === 'UpdateCompanyCaptain') 
        {
            $payload = [
                'event' => 'UpdateCompanyCaptain',
                'data'  => [
					'company_key' => $event->captain->pin,   
                    'captain_email' => $event->captain->email,
                    'captain_first_name' => $event->captain->name,
                    'captain_last_name' => $event->captain->last_name,                                     
                ],
            ];
        }
        elseif ($eventName === 'NewRunner') 
        {
            $payload = [
                'event' => 'NewRunner',
                'data'  => [
                    'runner_id' => (string)$event->runner->id,
                    'first_name' => $event->runner->name,
                    'last_name' => $event->runner->last_name,
                    'email' => $event->runner->email,
                    'phone' => $event->runner->phone,
                    'shirt_size' => optional($event->runner->shirtSize)->shirt_size_name ?? null,
                    'socks_size' => optional($event->runner->socksSize)->socks_size_name ?? null,
                    'date_of_birth' => $event->runner->date_of_birth,
                    'sex' => $event->runner->sex,
                    'work_position' => optional($event->runner->workPosition)->work_position_name ?? null,
                    'work_sector' => optional($event->runner->workSector)->work_sector_name ?? null,
                    'week_running' => optional($event->runner->weekRunning)->week_running_name ?? null,
                    'longest_race' => optional($event->runner->longestRace)->longest_race_name ?? null,
                    'company_id' => (string)$event->runner->captain_id,
                ],
            ];
        }       
        elseif ($eventName === 'NewRunnerReservation') 
        {
            $payload = [
                'event' => 'NewRunnerReservation',
                'data'  => [
                    'runner_id' => (string)$event->runnerReservation->id,                    
                    'reservation_id' => (string)$event->runnerReservation->reservation_id,
                    'race' => optional($event->runnerReservation->reservation->race)->bill_prefix ?? null,
                ],
            ];
        }   

        $url = config('services.webhook.external_url');

        Http::post($url, $payload);
    }
}
