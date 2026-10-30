<?php

namespace App\Console\Commands;

use App\Services\AzuriranjePrivrednihSubjekataService;
use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AzuriranjePrivrednihSubjekataService $azuriranjeService)
    {
        $this->azuriranjeService = $azuriranjeService;

        $this->azuriranjeService->update();
    }
}
