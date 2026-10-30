<?php

namespace App\PL\Test;

class Sifarnik
{
    /**
     * @var Zapis[]
     */
    protected $zapis = null;

    public function __construct() {}

    /**
     * @return Zapis[]
     */
    public function getZapis()
    {
        return $this->zapis;
    }

    /**
     * @param  Zapis[]  $zapis
     * @return Sifarnik
     */
    public function setZapis(?array $zapis = null)
    {
        $this->zapis = $zapis;

        return $this;
    }
}
