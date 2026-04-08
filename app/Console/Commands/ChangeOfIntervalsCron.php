<?php

namespace App\Console\Commands;

use App\Models\Captain;
use App\Models\Race;
use App\Models\Reservation;
use App\Services\ExportService;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChangeOfIntervalsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changeOfIntervals:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public MailService $mailService;
    public ExportService $exportService;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MailService $mailService, ExportService $exportService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->exportService = $exportService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
      ///ZAVRŠAVA ZA 3DANA

    public function handle()
    {
        $today = Carbon::now();

        $races = Race::where('application_end', '>=', $today)
            ->with(['inventories' => function ($q) {
                $q->whereHas('inventoryType', function ($typeQuery) {
                    $typeQuery->where('inventory_type_name', 'Akontacija');
                })
                ->with('inventoryIntervals');
            }])
            ->get();

        foreach ($races as $race) 
        {
            foreach ($race->inventories as $inventory) 
            {
                $sortedInventory = $inventory->inventoryIntervals->sortBy('start_date');
                $inThreeDays = Carbon::parse($today)->addDays(3)->toDateString();

                foreach ($sortedInventory as $sorted) 
                {
                    if (Carbon::parse($sorted->end_date)->toDateString() == $inThreeDays) 
                    {
                        $diffInDays = 3;
                        $unpaidReservations = Reservation::where([
                            ['race_id', $race->id],
                            ['payment_status', 0],
                            ['locked', 0],
                        ])
                            ->get();

                        foreach ($unpaidReservations as $unpaidReservation) 
                        {
                            $captain = Captain::where('id', $unpaidReservation->captain_id)->first();
                            $this->mailService->sendIntervalEndingEmail($captain, $diffInDays, $race->name);
                        }
                    }
                }
            }
        }
    }
}
