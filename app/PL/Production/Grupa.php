<?php
namespace App\PL\Production;


class Grupa
{

    /**
     * @var Podatak[] $podatak
     */
    protected $podatak = null;

    /**
     * @var string $id
     */
    protected $id = null;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
      $this->id = $id;
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
     * @return Grupa
     */
    public function setPodatak(array $podatak = null)
    {
      $this->podatak = $podatak;
      return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
      return $this->id;
    }

    /**
     * @param string $id
     * @return Grupa
     */
    public function setId($id)
    {
      $this->id = $id;
      return $this;
    }

}
