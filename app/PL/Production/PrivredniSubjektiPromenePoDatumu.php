<?php
namespace App\PL\Production;


class PrivredniSubjektiPromenePoDatumu
{

    /**
     * @var \DateTime $datumOd
     */
    protected $datumOd = null;

    /**
     * @var \DateTime $datumDo
     */
    protected $datumDo = null;

    /**
     * @param \DateTime $datumOd
     * @param \DateTime $datumDo
     */
    public function __construct(\DateTime $datumOd, \DateTime $datumDo)
    {
      $this->datumOd = $datumOd->format(\DateTime::ATOM);
      $this->datumDo = $datumDo->format(\DateTime::ATOM);
    }

    /**
     * @return \DateTime
     */
    public function getDatumOd()
    {
      if ($this->datumOd == null) {
        return null;
      } else {
        try {
          return new \DateTime($this->datumOd);
        } catch (\Exception $e) {
          return false;
        }
      }
    }

    /**
     * @param \DateTime $datumOd
     * @return PrivredniSubjektiPromenePoDatumu
     */
    public function setDatumOd(\DateTime $datumOd)
    {
      $this->datumOd = $datumOd->format(\DateTime::ATOM);
      return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatumDo()
    {
      if ($this->datumDo == null) {
        return null;
      } else {
        try {
          return new \DateTime($this->datumDo);
        } catch (\Exception $e) {
          return false;
        }
      }
    }

    /**
     * @param \DateTime $datumDo
     * @return PrivredniSubjektiPromenePoDatumu
     */
    public function setDatumDo(\DateTime $datumDo)
    {
      $this->datumDo = $datumDo->format(\DateTime::ATOM);
      return $this;
    }

}
