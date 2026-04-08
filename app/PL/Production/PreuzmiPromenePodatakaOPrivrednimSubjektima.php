<?php
namespace App\PL\Production;


class PreuzmiPromenePodatakaOPrivrednimSubjektima
{

    /**
     * @var PrivredniSubjektiPromenePoDatumu $privredniSubjektiPromenePoDatumuUlazniPodaci
     */
    protected $privredniSubjektiPromenePoDatumuUlazniPodaci = null;

    /**
     * @param PrivredniSubjektiPromenePoDatumu $privredniSubjektiPromenePoDatumuUlazniPodaci
     */
    public function __construct($privredniSubjektiPromenePoDatumuUlazniPodaci)
    {
      $this->privredniSubjektiPromenePoDatumuUlazniPodaci = $privredniSubjektiPromenePoDatumuUlazniPodaci;
    }

    /**
     * @return PrivredniSubjektiPromenePoDatumu
     */
    public function getPrivredniSubjektiPromenePoDatumuUlazniPodaci()
    {
      return $this->privredniSubjektiPromenePoDatumuUlazniPodaci;
    }

    /**
     * @param PrivredniSubjektiPromenePoDatumu $privredniSubjektiPromenePoDatumuUlazniPodaci
     * @return PreuzmiPromenePodatakaOPrivrednimSubjektima
     */
    public function setPrivredniSubjektiPromenePoDatumuUlazniPodaci($privredniSubjektiPromenePoDatumuUlazniPodaci)
    {
      $this->privredniSubjektiPromenePoDatumuUlazniPodaci = $privredniSubjektiPromenePoDatumuUlazniPodaci;
      return $this;
    }

}
