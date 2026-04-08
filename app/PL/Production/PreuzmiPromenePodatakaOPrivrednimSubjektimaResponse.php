<?php
namespace App\PL\Production;


class PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse
{

    /**
     * @var ArrayOfPrivredniSubjekat $privredniSubjektiPodaci
     */
    protected $privredniSubjektiPodaci = null;

    /**
     * @param ArrayOfPrivredniSubjekat $privredniSubjektiPodaci
     */
    public function __construct($privredniSubjektiPodaci)
    {
      $this->privredniSubjektiPodaci = $privredniSubjektiPodaci;
    }

    /**
     * @return ArrayOfPrivredniSubjekat
     */
    public function getPrivredniSubjektiPodaci()
    {
      return $this->privredniSubjektiPodaci;
    }

    /**
     * @param ArrayOfPrivredniSubjekat $privredniSubjektiPodaci
     * @return PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse
     */
    public function setPrivredniSubjektiPodaci($privredniSubjektiPodaci)
    {
      $this->privredniSubjektiPodaci = $privredniSubjektiPodaci;
      return $this;
    }

}
