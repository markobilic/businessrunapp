<?php

namespace App\PL\Test;

class PrivredniSubjektiPromenePoDatumu
{
    /**
     * @var \DateTime
     */
    protected $datumOd = null;

    /**
     * @var \DateTime
     */
    protected $datumDo = null;

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
     * @return PrivredniSubjektiPromenePoDatumu
     */
    public function setDatumDo(\DateTime $datumDo)
    {
        $this->datumDo = $datumDo->format(\DateTime::ATOM);

        return $this;
    }
}
