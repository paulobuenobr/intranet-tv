<?php

namespace SicrediAPI\Mappers;

use SicrediAPI\Domain\Boleto\DescontoConfiguracao as DescontoConfiguracaoDomain;

class DescontoConfiguracao
{
    private $desconto;

    public function __construct(DescontoConfiguracaoDomain $desconto)
    {
        $this->desconto = $desconto;
    }

    private function getTipoDesconto()
    {
        return $this->desconto->getTipoDesconto() == DescontoConfiguracaoDomain::PERCENTUAL ? 'PERCENTUAL' : 'VALOR';
    }

    public function toArray()
    {
        $configuracao = [
            'tipoDesconto' => $this->getTipoDesconto()
        ];

        if (!empty($this->desconto->getDescontoParaPagamentoAntecipado())) {
            $configuracao['descontoAntecipado'] = $this->desconto->getDescontoParaPagamentoAntecipado();
            return $configuracao;
        }

        if (!empty($this->desconto->getDescontos())) {
            foreach ($this->desconto->getDescontos() as $key => $item) {
                $descontoIndex = $key + 1;
                $configuracao['valorDesconto' . $descontoIndex] = $item->getValor();
                $configuracao['dataDesconto' . $descontoIndex] = $item->getData()->format('Y-m-d');
            }
        }

        return $configuracao;
    }
}
