<?php

namespace App\Services;

use App\PL\Test\PlService;
use App\PL\Test\PreuzmiPodatkeOPrivrednomSubjektu;
use App\PL\Test\PrivredniSubjekatMaticniBroj;
use App\PL\Test\PrivredniSubjektiUlazniPodaci;
use App\Models\PL\PLPrivredniSubjekat;
use Illuminate\Support\Facades\Log;

class PLTestServiceClient
{

    private $plService;

    public function __construct(
        PlService $plService
    )
    {
        $this->plService = $plService;
    }

    public function PreuzmiPodatkeOPrivrednomSubjektu(
        string $mbr,
        string $maticniBrojTip    
    ) {
        $maticniBroj = new PrivredniSubjekatMaticniBroj($maticniBrojTip);
        $maticniBroj->setMaticniBroj($mbr);
        $inputData = new PrivredniSubjektiUlazniPodaci();
        $inputData->setPrivredniSubjekti($maticniBroj);
        $data = new PreuzmiPodatkeOPrivrednomSubjektu($inputData);
        $response = $this->plService->PreuzmiPodatkeOPrivrednomSubjektu($data);

        $privredniSubjekti = [];

        foreach($response->getPrivredniSubjektiPodaci()->getPrivredniSubjekat() as $p) {
            $privredniSubjekti[] = new PLPrivredniSubjekat($p);
        } 

        return collect($privredniSubjekti);
    }
}