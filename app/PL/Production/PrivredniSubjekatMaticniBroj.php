<?php
namespace App\PL\Production;


class PrivredniSubjekatMaticniBroj
{

    /**
     * @var string $maticniBroj
     */
    protected $maticniBroj = null;

    /**
     * @var PrivredniSubjekatMaticniBrojTip $tip
     */
    protected $tip = null;

    /**
     * @var PrivredniSubjekatGrupaPodataka[] $gp
     */
    protected $gp = null;

    /**
     * @param PrivredniSubjekatMaticniBrojTip $tip
     */
    public function __construct($tip)
    {
      $this->tip = $tip;
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
     * @return PrivredniSubjekatMaticniBroj
     */
    public function setMaticniBroj($maticniBroj)
    {
      $this->maticniBroj = $maticniBroj;
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
     * @return PrivredniSubjekatMaticniBroj
     */
    public function setTip($tip)
    {
      $this->tip = $tip;
      return $this;
    }

    /**
     * @return PrivredniSubjekatGrupaPodataka[]
     */
    public function getGp()
    {
      return $this->gp;
    }

    /**
     * @param PrivredniSubjekatGrupaPodataka[] $gp
     * @return PrivredniSubjekatMaticniBroj
     */
    public function setGp(array $gp = null)
    {
      $this->gp = $gp;
      return $this;
    }

}
