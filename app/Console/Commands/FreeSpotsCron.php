<?php

namespace App\Console\Commands;

use App\Models\Captain;
use App\Models\Race;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreeSpotsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freeSpots:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public MailService $mailService;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(MailService $mailService)
    {
        $this->mailService = $mailService;

        $today = Carbon::now();
        $inThreeDays = Carbon::parse($today)->addDays(3)->format('Y-m-d');

        $races = Race::where('application_end', '=', $inThreeDays)
            ->with(['reservations' => function ($q) {
                $q->with(['runnerReservations' => function ($sub) {
                    $sub->whereNotNull('runner_id')->where('runner_id', '!=', 0);
                }]);
            }])
            ->get();

        foreach ($races as $race) {
            foreach ($race->reservations as $reservation) {
                $filledPlaces = count($reservation->runnerReservations);

                if ($reservation->reserved_places > $filledPlaces) {
                    $captain = Captain::where('id', $reservation->captain_id)->first();
                    $freeSpots = $reservation->reserved_places - $filledPlaces;
                    $date = Carbon::parse($race->application_end)->format('d-m-Y');
                    $this->mailService->sendFreeSpotsEmail($captain, $freeSpots, $date, $reservation->id, $race->name);
                }
            }
        }
    }
}
