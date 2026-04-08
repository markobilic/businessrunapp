<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Captain;
use Carbon\Carbon;
use App\Services\AzuriranjePrivrednihSubjekataService;

class AzuriranjePrivrednihSubjekataCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azuriranjePrivrednihSubjekata:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public AzuriranjePrivrednihSubjekataService $azuriranjeService;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AzuriranjePrivrednihSubjekataService $azuriranjeService)
    {
        parent::__construct();
        $this->azuriranjeService = $azuriranjeService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->azuriranjeService->update();
    }
}
