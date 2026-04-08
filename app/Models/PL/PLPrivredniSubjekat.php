<?php

namespace App\Models\PL;

use App\Models\PL\PLGrupa;
use App\PL\Production\PrivredniSubjekat;

class PLPrivredniSubjekat {

    // PLGrupa[] $grupa
    public $grupa = [];
    public $tip = null;
    public $maticniBroj = null;

    public function __construct(PrivredniSubjekat $subjekat) {
        if($subjekat->getGrupa()) {
            $this->grupa = array_map(
                function($grupa) { return new PLGrupa($grupa); },
                $subjekat->getGrupa()
            );
        }
        $this->tip = $subjekat->getTip();
        $this->maticniBroj = $subjekat->getMaticniBroj();
    }
}