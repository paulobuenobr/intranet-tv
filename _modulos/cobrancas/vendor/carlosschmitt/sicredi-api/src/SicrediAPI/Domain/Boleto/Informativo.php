<?php

namespace SicrediAPI\Domain\Boleto;

class Informativo {

    private $maxLength = 80;
    private $maxMensagens = 5;
    private $mensagens = [];

    public function __construct(array $mensagens = []) {

        if (count($mensagens) > $this->maxMensagens) {
            throw new \InvalidArgumentException("A contagem de mensagens deve ser menor que {$this->maxMensagens}");
        }

        foreach ($mensagens as $message) {
            if (strlen($message) > $this->maxLength) {
                throw new \InvalidArgumentException("O comprimento da mensagem deve ser menor que {$this->maxLength} caracteres");
            }
        }

        $this->mensagens = $mensagens;
    }

    public function getLines() {
        return $this->mensagens;
    }
}
