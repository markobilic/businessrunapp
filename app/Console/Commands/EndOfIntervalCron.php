<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Captain;
use App\Models\Race;
use App\Models\Reservation;
use App\Services\MailService;
use Carbon\Carbon;
use App\Services\ExportService;

class EndOfIntervalCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'endOfInterval:cron';

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
    public function handle()
    {
        $today = Carbon::now();
        $yesterday = Carbon::parse($today)->subDays(1)->toDateString();

        $races = Race::where([
            ['application_end', '>=', $today],
        ])->get();

        foreach ($races as $race) 
        {
            $inventory = $race->inventories()
                ->whereHas('inventoryType', function ($query) {
                    $query->where('inventory_type_name', 'Akontacija');
                })
                ->with(['inventoryIntervals'])
                ->withMin('inventoryIntervals', 'start_date')
                ->orderBy('inventory_intervals_min_start_date', 'ASC')
                ->first();

            if($inventory)
            {
                foreach ($inventory->inventoryIntervals as $ii) 
                {
                    if (Carbon::parse($ii->end_date)->toDateString() == $yesterday) 
                    {                
                        $unpaidReservations = Reservation::where([
                            ['race_id', $race->id],
                            ['payment_status', 0],
                            ['locked', 0],
                        ])
                        ->get();
    
                        foreach ($unpaidReservations as $unpaidReservation) 
                        {
                            $captain = Captain::where('id', $unpaidReservation->captain_id)->first();
    
                            $template = $this->mailService->getRightTemplate($captain->organizer_id, 'send-invoice');
                            $encode = json_decode($template->content);
                            $data['title'] = $encode->title;
                            $data['invoice1'] = $encode->invoice1;
                            $data['invoice2'] = $encode->invoice2;
                            $data['invoice3'] = $encode->invoice3;
                            $data['downloadBill'] = $encode->downloadBill;
                            $data['race'] = $race->name;
                            $data['respect'] = $encode->respect;
                            $data['sbr'] = $encode->sbr;
                            $data['note'] = $encode->note;
                            $pdf = $this->exportService->exportToPdf($unpaidReservation, 'invoice', true);

                            $this->mailService->sendEmail($captain->email, $data, $data['title'], 'view', 'mail.send-invoice', 'No Reply', $pdf);
                        }
                    }
                }
            }
        }
    }
}
