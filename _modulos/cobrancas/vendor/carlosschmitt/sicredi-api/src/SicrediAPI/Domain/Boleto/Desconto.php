<?php

namespace SicrediAPI\Domain\Boleto;

use DateTime;

class Desconto {

    private $valor;
    private $data;

    public function __construct(float $valor, DateTime $date) {

        if ($valor <= 0) {
            throw new \InvalidArgumentException("O valor do desconto deve ser maior que 0");
        }

        if (empty($date)) {
            throw new \InvalidArgumentException("A data do desconto deve ser definida");
        }

        $this->valor = $valor;
        $this->data = $date;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getData() {
        return $this->data;
    }

    public static function criar(float $valor, DateTime $data) {
        return new Desconto($valor, $data);
    }
}
