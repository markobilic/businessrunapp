<?php

namespace App\Models\PL;

use App\PL\Production\Podatak;

class PLPodatak {

    public $naziv = null;
    public $vrednost = null;
    public $tip = null;

    public function __construct(Podatak $podatak) {
        $this->naziv = $podatak->getNaziv();
        $this->vrednost = $podatak->getVrednost();
        $this->tip = $podatak->getTip();
    }
}