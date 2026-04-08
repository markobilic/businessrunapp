<?php

namespace App\Models\PL;

use App\PL\Production\Grupa;

class PLGrupa {
    // PLPodatak[] $podatak
    public $podatak = [];
    public $id = null;

    public function __construct(Grupa $grupa) {
        if($grupa->getPodatak()) {
            $this->podatak = array_map(
                function($podatak) { return new PLPodatak($podatak); },
                $grupa->getPodatak()
            );
        }
        $this->id = $grupa->getId();
    }
}