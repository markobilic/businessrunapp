<?php
namespace App\PL\Production;


class Podatak
{

    /**
     * @var string $naziv
     */
    protected $naziv = null;

    /**
     * @var string $vrednost
     */
    protected $vrednost = null;

    /**
     * @var string $tip
     */
    protected $tip = null;

    /**
     * @param string $naziv
     * @param string $vrednost
     * @param string $tip
     */
    public function __construct($naziv, $vrednost, $tip)
    {
      $this->naziv = $naziv;
      $this->vrednost = $vrednost;
      $this->tip = $tip;
    }

    /**
     * @return string
     */
    public function getNaziv()
    {
      return $this->naziv;
    }

    /**
     * @param string $naziv
     * @return Podatak
     */
    public function setNaziv($naziv)
    {
      $this->naziv = $naziv;
      return $this;
    }

    /**
     * @return string
     */
    public function getVrednost()
    {
      return $this->vrednost;
    }

    /**
     * @param string $vrednost
     * @return Podatak
     */
    public function setVrednost($vrednost)
    {
      $this->vrednost = $vrednost;
      return $this;
    }

    /**
     * @return string
     */
    public function getTip()
    {
      return $this->tip;
    }

    /**
     * @param string $tip
     * @return Podatak
     */
    public function setTip($tip)
    {
      $this->tip = $tip;
      return $this;
    }

}
