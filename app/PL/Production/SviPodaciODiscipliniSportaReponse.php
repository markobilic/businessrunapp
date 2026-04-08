<?php
namespace App\PL\Production;


class SviPodaciODiscipliniSportaReponse
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
     * @return SviPodaciODiscipliniSportaReponse
     */
    public function setSifarnik($sifarnik)
    {
      $this->sifarnik = $sifarnik;
      return $this;
    }

}
