<?php

namespace SicrediAPI\Domain\Boleto;

class Liquidacao {

    private $dataLiquidacao;
    private $valor;
    private $multa;
    private $abatimento;
    private $juros;
    private $desconto;
    private $nossoNumero;
    private $seuNumero;
    private $tipoLiquidacao;
    private $valorOriginal;

    public function __construct(
            \DateTime $dataLiquidacao, float $valor, float $multa = null, float $abatimento = null, float $juros = null,
            float $desconto = null, string $nossoNumero = null, string $seuNumero = null, float $valorOriginal = null, string $tipoLiquidacao = null) {
        
        $this->dataLiquidacao = $dataLiquidacao;
        $this->valor = $valor;
        $this->multa = $multa;
        $this->abatimento = $abatimento;
        $this->juros = $juros;
        $this->desconto = $desconto;
        $this->nossoNumero = $nossoNumero;
        $this->seuNumero = $seuNumero;
        $this->valorOriginal = $valorOriginal;
        $this->tipoLiquidacao = $tipoLiquidacao;
    }

    public function getDataLiquidacao(): \DateTime {
        return $this->dataLiquidacao;
    }

    public function getValor(): float {
        return $this->valor;
    }

    public function getMulta(): ?float {
        return $this->multa;
    }

    public function getAbatimento(): ?float {
        return $this->abatimento;
    }

    public function getJuros(): ?float {
        return $this->juros;
    }

    public function getDesconto(): ?float {
        return $this->desconto;
    }

    public function getNossoNumero(): ?string {
        return $this->nossoNumero;
    }

    public function getSeuNumero(): ?string {
        return $this->seuNumero;
    }

    public function getTipoLiquidacao(): ?string {
        return $this->tipoLiquidacao;
    }

    public function getValorOriginal(): ?float {
        return $this->valorOriginal;
    }

    public static function fromArray(array $data) {
        return new self(
                \DateTime::createFromFormat('Y-m-d\TH:i:sP', $data['data']),
                $data['valor'],
                $data['multa'],
                $data['abatimento'],
                $data['juros'],
                $data['desconto']
        );
    }
}
