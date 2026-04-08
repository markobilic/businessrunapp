<?php

namespace App\PL\Test;

class PreuzmiPodatkeOPrivrednomSubjektuResponse
{

    /**
     * @var ArrayOfPrivredniSubjekat $PrivredniSubjektiPodaci
     */
    protected $PrivredniSubjektiPodaci = null;

    /**
     * @param ArrayOfPrivredniSubjekat $PrivredniSubjektiPodaci
     */
    public function __construct($PrivredniSubjektiPodaci)
    {
      $this->PrivredniSubjektiPodaci = $PrivredniSubjektiPodaci;
    }

    /**
     * @return ArrayOfPrivredniSubjekat
     */
    public function getPrivredniSubjektiPodaci()
    {
      return $this->PrivredniSubjektiPodaci;
    }

    /**
     * @param ArrayOfPrivredniSubjekat $PrivredniSubjektiPodaci
     * @return PreuzmiPodatkeOPrivrednomSubjektuResponse
     */
    public function setPrivredniSubjektiPodaci($PrivredniSubjektiPodaci)
    {
      $this->PrivredniSubjektiPodaci = $PrivredniSubjektiPodaci;
      return $this;
    }

}
