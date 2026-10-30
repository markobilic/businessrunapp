<?php

namespace App\PL\Test;

class SviPodaciOTipovimaClanaPrivrednogDrustvaResponse
{
    /**
     * @var Sifarnik
     */
    protected $sifarnik = null;

    /**
     * @param  Sifarnik  $sifarnik
     */
    public function __construct($sifarnik)
    {
        $this->sifarnik = $sifarnik;
    }

    /**
     * @return Sifarnik
     */
    public function getSifarnik()
    {
        return $this->sifarnik;
    }

    /**
     * @param  Sifarnik  $sifarnik
     * @return SviPodaciOTipovimaClanaPrivrednogDrustvaResponse
     */
    public function setSifarnik($sifarnik)
    {
        $this->sifarnik = $sifarnik;

        return $this;
    }
}
