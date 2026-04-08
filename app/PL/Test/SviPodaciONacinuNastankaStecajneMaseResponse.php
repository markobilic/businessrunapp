<?php
namespace App\PL\Test;


class SviPodaciONacinuNastankaStecajneMaseResponse
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
     * @return SviPodaciONacinuNastankaStecajneMaseResponse
     */
    public function setSifarnik($sifarnik)
    {
      $this->sifarnik = $sifarnik;
      return $this;
    }

}
