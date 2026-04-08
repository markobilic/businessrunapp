<?php
namespace App\PL\Production;


class Sifarnik
{

    /**
     * @var Zapis[] $zapis
     */
    protected $zapis = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return Zapis[]
     */
    public function getZapis()
    {
      return $this->zapis;
    }

    /**
     * @param Zapis[] $zapis
     * @return Sifarnik
     */
    public function setZapis(array $zapis = null)
    {
      $this->zapis = $zapis;
      return $this;
    }

}
