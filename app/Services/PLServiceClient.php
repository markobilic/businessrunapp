<?php

namespace App\Services;

use App\PL\Production\PlService;
use App\PL\Production\PreuzmiPodatkeOPrivrednomSubjektu;
use App\PL\Production\PrivredniSubjekatMaticniBroj;
use App\PL\Production\PrivredniSubjektiUlazniPodaci;
use App\Models\PL\PLPrivredniSubjekat;
use Illuminate\Support\Facades\Log;

class PLServiceClient
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