<?php

namespace App\Console\Commands;

use App\Models\Captain;
use App\Models\Race;
use App\Models\Reservation;
use App\Services\ExportService;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Services\ReservationService_v2;

class FinalBillCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finalBill:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        /*
        $yesterday = Carbon::yesterday();

        $races = Race::whereDate('starting_date', $yesterday)
            ->where('organizer_id', 2)
            ->with(['reservations'])
            ->get();

        $reservationService_v2 = new ReservationService_v2();

        foreach ($races as $race) 
        {
            foreach($race->reservations as $reservation)
            {
                $reservationService_v2->sendFinalBillToSwagger($reservation);
            }
        }
        */
    }
}
