<?php

namespace SicrediAPI\Domain\Boleto;

use DateTime;
use SicrediAPI\Domain\Boleto\Desconto;

class DescontoConfiguracao {

    public const PERCENTUAL = 'PERCENTUAL';
    public const VALOR = 'VALOR';

    private $tipoDeDescontos;
    private $descontoParaPagamentoAntecipado;
    private $descontos;
    private $somaDeDescontos;

    public function __construct(array $descontos = [], string $tipoDeDescontos, float $descontoParaPagamentoAntecipado = null) {

        if ($tipoDeDescontos !== self::PERCENTUAL && $tipoDeDescontos !== self::VALOR) {
            throw new \InvalidArgumentException("O tipo de desconto deve ser \"PERCENTUAL\" ou \"VALOR\"");
        }

        $this->tipoDeDescontos = $tipoDeDescontos;

        if ($descontoParaPagamentoAntecipado) {
            $this->descontoParaPagamentoAntecipado = $descontoParaPagamentoAntecipado;

            if (empty($descontos)) {
                return;
            }

            throw new \InvalidArgumentException("Você não pode definir descontos e descontos por pagamento antecipado");
        }

        if (empty($descontos)) {
            throw new \InvalidArgumentException("Você deve definir pelo menos um desconto");
        }

        if (count($descontos) > 3) {
            throw new \InvalidArgumentException("A contagem de descontos deve ser inferior a 3");
        }

        $somaDeDescontos = 0;
        $dataAnterior = null;

        $this->descontos = array_map(function ($desconto) use (&$somaDeDescontos, &$dataAnterior) {
            if (!$desconto instanceof Desconto) {
                throw new \InvalidArgumentException("Os descontos devem ser uma matriz de objetos descontos");
            }

            if (isset($dataAnterior) && $desconto->getData() <= $dataAnterior) {
                throw new \InvalidArgumentException("As datas de desconto devem estar em ordem crescente");
            }

            if ($this->tipoDeDescontos == self::PERCENTUAL && $desconto->getValor() >= 100) {
                throw new \InvalidArgumentException("O valor do desconto deve ser inferior a 100%");
            }

            $dataAnterior = $desconto->getData();
            $somaDeDescontos += $desconto->getValor();

            return $desconto;
        }, $descontos);

        $this->somaDeDescontos = $somaDeDescontos;
    }

    public function validarDataVencimento(DateTime $dataVencimento) {
        foreach ($this->getDescontos() as $key => $item) {
            if ($item->getData() > $dataVencimento) {
                throw new \InvalidArgumentException("A data do desconto deve ser inferior à data de vencimento no Desconto" . $key + 1 . "}");
            }
        }

        return true;
    }

    public function validarValor(float $valor) {
        
        if ($this->getTipoDesconto() == DescontoConfiguracao::VALOR) {

            if ($this->getSomaDeDescontos() >= $valor) {
                throw new \InvalidArgumentException("A soma dos descontos deve ser menor que o valor do boleto");
            }

            if ($this->getDescontoParaPagamentoAntecipado() >= $valor) {
                throw new \InvalidArgumentException("O valor do desconto antecipado deve ser menor que o valor do boleto");
            }
        }

        return true;
    }

    public function getTipoDesconto() {
        return $this->tipoDeDescontos;
    }

    public function getDescontoParaPagamentoAntecipado() {
        return $this->descontoParaPagamentoAntecipado;
    }

    public function getDescontos() {
        return $this->descontos;
    }

    public function getSomaDeDescontos() {
        return $this->somaDeDescontos;
    }
}
