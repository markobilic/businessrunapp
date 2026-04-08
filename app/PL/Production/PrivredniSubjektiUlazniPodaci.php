<?php
namespace App\PL\Production;


class PrivredniSubjektiUlazniPodaci
{

    /**
     * @var PrivredniSubjekatMaticniBroj $privredniSubjekti
     */
    protected $privredniSubjekti = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return PrivredniSubjekatMaticniBroj
     */
    public function getPrivredniSubjekti()
    {
      return $this->privredniSubjekti;
    }

    /**
     * @param PrivredniSubjekatMaticniBroj $privredniSubjekti
     * @return PrivredniSubjektiUlazniPodaci
     */
    public function setPrivredniSubjekti($privredniSubjekti)
    {
      $this->privredniSubjekti = $privredniSubjekti;
      return $this;
    }

}
