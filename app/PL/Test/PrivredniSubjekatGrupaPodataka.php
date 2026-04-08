<?php
namespace App\PL\Test;


class PrivredniSubjekatGrupaPodataka
{

    /**
     * @var string $grupa
     */
    protected $grupa = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return string
     */
    public function getGrupa()
    {
      return $this->grupa;
    }

    /**
     * @param string $grupa
     * @return PrivredniSubjekatGrupaPodataka
     */
    public function setGrupa($grupa)
    {
      $this->grupa = $grupa;
      return $this;
    }

}
