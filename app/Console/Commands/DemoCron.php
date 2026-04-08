<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Captain;
use App\Models\Race;
use App\Models\Reservation;
use App\Models\Organizer;
use App\Services\ExportService;
use App\Services\MailService;
use Carbon\Carbon;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

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
    public function __construct( MailService $mailService, ExportService $exportService)
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
        $fiveDaysAgo = Carbon::now()->subDays(9)->toDateString();
        $today = Carbon::now()->toDateString();
    
        $racesFinished = Race::whereDate('starting_date','>=', $fiveDaysAgo)->whereDate('starting_date','<=', $today)->where('organizer_id', 2)->get();
       
        $raceFinishedIDs = collect($racesFinished)->map(function ($item) {
            return $item->id;
        });

        $reservationsForRace = Reservation::whereIn('race_id', $raceFinishedIDs)
          ->where('payment_status', 1)
          ->whereNull('sent_email')
          ->with('race')
          ->take(10)
          ->get(); 

        foreach($racesFinished as $race) 
        {
            foreach($reservationsForRace as $reservation) 
            {                 
                $organizerID = Captain::where('id', $reservation->captain_id)->first();
                $data['captain'] = $organizerID;
                $data['race'] = $reservation->race;
                $data['reservation'] = $reservation;
                $template =  $this->mailService->getRightTemplate($organizerID->organizer_id, 'send-bills');
                $encode = json_decode($template->content);
                
                $data['downloadBill'] = $encode->downloadBill;
                $data['thankYou'] = $encode->thankYou;
                $data['goodLuck'] = $encode->goodLuck;
                $data['respect'] = $encode->respect;
                $data['sbr'] = $encode->sbr;
                $data['title'] = $encode->title;
      
                $pdf =  $this->exportService->exportToPdf($reservation, 'finalBill');
               
                $reservation->sent_email = Carbon::now();
                $reservation->save();
                $this->mailService->sendEmail($data['captain']->email, $data, $data['title'].' '.$race->name, 'view', 'mail.send-bills', 'No Reply', $pdf['pdf']);
            }
        }       
    }
}
