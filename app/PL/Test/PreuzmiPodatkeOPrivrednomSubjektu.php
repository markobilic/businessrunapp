<?php

namespace App\PL\Test;

class PreuzmiPodatkeOPrivrednomSubjektu
{

    /**
     * @var PrivredniSubjektiUlazniPodaci $privredniSubjektiUlazniPodaci
     */
    protected $privredniSubjektiUlazniPodaci = null;

    /**
     * @param PrivredniSubjektiUlazniPodaci $privredniSubjektiUlazniPodaci
     */
    public function __construct($privredniSubjektiUlazniPodaci)
    {
      $this->privredniSubjektiUlazniPodaci = $privredniSubjektiUlazniPodaci;
    }

    /**
     * @return PrivredniSubjektiUlazniPodaci
     */
    public function getPrivredniSubjektiUlazniPodaci()
    {
      return $this->privredniSubjektiUlazniPodaci;
    }

    /**
     * @param PrivredniSubjektiUlazniPodaci $privredniSubjektiUlazniPodaci
     * @return PreuzmiPodatkeOPrivrednomSubjektu
     */
    public function setPrivredniSubjektiUlazniPodaci($privredniSubjektiUlazniPodaci)
    {
      $this->privredniSubjektiUlazniPodaci = $privredniSubjektiUlazniPodaci;
      return $this;
    }

}
