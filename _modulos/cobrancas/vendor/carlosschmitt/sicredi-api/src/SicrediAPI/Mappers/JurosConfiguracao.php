<?php

namespace SicrediAPI\Mappers;

use SicrediAPI\Domain\Boleto\JurosConfiguracao as JurosConfiguracaoDomain;

class JurosConfiguracao
{
    private $juros;

    public function __construct(JurosConfiguracaoDomain $juros)
    {
        $this->juros = $juros;
    }

    private function getTipoDeJuros()
    {
        if(!empty($this->juros->getTipoDeJuros())) {
            return $this->juros->getTipoDeJuros() == JurosConfiguracaoDomain::PERCENTUAL ? 'PERCENTUAL' : 'VALOR';
        } else {
            return null;
        }
    }

    public function toArray()
    {

        $configuracao = [];
        if (!empty($this->juros->getTipoDeJuros())) {
            $configuracao['tipoJuros'] = $this->getTipoDeJuros();
        }

        if (!empty($this->juros->getJuros())) {
            $configuracao['juros'] = $this->juros->getJuros();
        }

        if (!empty($this->juros->getMulta())) {
            $configuracao['multa'] = $this->juros->getMulta();
        }

        if (!empty($this->juros->getDiasNegativacaoAuto())) {
            $configuracao['diasNegativacaoAuto'] = $this->juros->getDiasNegativacaoAuto();
        }

        if (!empty($this->juros->getDiasProtestoAuto())) {
            $configuracao['diasProtestoAuto'] = $this->juros->getDiasProtestoAuto();
        }

        if (!empty($this->juros->getValidadeAposVencimento())) {
            $configuracao['validadeAposVencimento'] = $this->juros->getValidadeAposVencimento();
        }

        return $configuracao;
    }
}
