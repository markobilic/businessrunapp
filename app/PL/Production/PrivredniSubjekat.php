<?php
namespace App\PL\Production;


class PrivredniSubjekat
{

    /**
     * @var Grupa[] $grupa
     */
    protected $grupa = null;

    /**
     * @var PrivredniSubjekatMaticniBrojTip $tip
     */
    protected $tip = null;

    /**
     * @var string $maticniBroj
     */
    protected $maticniBroj = null;

    /**
     * @param PrivredniSubjekatMaticniBrojTip $tip
     * @param string $maticniBroj
     */
    public function __construct($tip, $maticniBroj)
    {
      $this->tip = $tip;
      $this->maticniBroj = $maticniBroj;
    }

    /**
     * @return Grupa[]
     */
    public function getGrupa()
    {
      return $this->grupa;
    }

    /**
     * @param Grupa[] $grupa
     * @return PrivredniSubjekat
     */
    public function setGrupa(array $grupa = null)
    {
      $this->grupa = $grupa;
      return $this;
    }

    /**
     * @return PrivredniSubjekatMaticniBrojTip
     */
    public function getTip()
    {
      return $this->tip;
    }

    /**
     * @param PrivredniSubjekatMaticniBrojTip $tip
     * @return PrivredniSubjekat
     */
    public function setTip($tip)
    {
      $this->tip = $tip;
      return $this;
    }

    /**
     * @return string
     */
    public function getMaticniBroj()
    {
      return $this->maticniBroj;
    }

    /**
     * @param string $maticniBroj
     * @return PrivredniSubjekat
     */
    public function setMaticniBroj($maticniBroj)
    {
      $this->maticniBroj = $maticniBroj;
      return $this;
    }

}
