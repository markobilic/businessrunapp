<?php
namespace App\PL\Production;


class Zapis
{

    /**
     * @var Podatak[] $podatak
     */
    protected $podatak = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return Podatak[]
     */
    public function getPodatak()
    {
      return $this->podatak;
    }

    /**
     * @param Podatak[] $podatak
     * @return Zapis
     */
    public function setPodatak(array $podatak = null)
    {
      $this->podatak = $podatak;
      return $this;
    }

}
