<?php
namespace App\PL\Test;


class SviPodaciOTipoviimaZabeleskiUdruzenjaResponse
{

    /**
     * @var Sifarnik $sifarnik
     */
    protected $sifarnik = null;

    /**
     * @param Sifarnik $sifarnik
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
     * @param Sifarnik $sifarnik
     * @return SviPodaciOTipoviimaZabeleskiUdruzenjaResponse
     */
    public function setSifarnik($sifarnik)
    {
      $this->sifarnik = $sifarnik;
      return $this;
    }

}
