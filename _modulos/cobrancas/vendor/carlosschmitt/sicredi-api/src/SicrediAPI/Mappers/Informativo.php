<?php

namespace SicrediAPI\Mappers;

use SicrediAPI\Domain\Boleto\Informativo as InformativoDomain;

class Informativo
{
    private $informativo;

    public function __construct(InformativoDomain $informativo)
    {
        $this->informativo = $informativo;
    }

    public function toArray()
    {
        if (empty($this->informativo)) {
            return [];
        }

        return $this->informativo->getLines();
    }
}
