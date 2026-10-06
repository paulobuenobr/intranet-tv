<?php

namespace SicrediAPI\Mappers;

use DateTime;
use SicrediAPI\Domain\Boleto\Boleto as BoletoDomain;
use SicrediAPI\Domain\Boleto\InformacaoDePagamento;
use SicrediAPI\Domain\Boleto\DescontoConfiguracao as DescontoConfiguracaoDomain;
use SicrediAPI\Domain\Boleto\JurosConfiguracao as JurosConfiguracaoDomain;
use SicrediAPI\Domain\Boleto\BeneficiarioFinal as BeneficiarioFinalDomain;
use SicrediAPI\Domain\Boleto\Pagador as PagadorDomain;
use SicrediAPI\Domain\Boleto\Liquidacao as LiquidacaoDomain;

class Boleto {

    public static function mapCriarBoleto(BoletoDomain $boleto) {
        $base = [
            'pagador' => (new Pagador($boleto->getPagador()))->toArray(),
            'nossoNumero' => $boleto->getNossoNumero(),
            'seuNumero' => $boleto->getSeuNumero(),
            'dataVencimento' => $boleto->getDataVencimento()->format('Y-m-d'),
            'valor' => $boleto->getValor(),
            'codigoBeneficiario' => $boleto->getBeneficiarioFinalCode(),
            'especieDocumento' => $boleto->getEspecieDocumento(),
            'informativo' => (new Mensagens($boleto->getInformativo()))->toArray(),
            'mensagem' => (new Mensagens($boleto->getMensagens()))->toArray(),
        ];

        if (!empty($boleto->getBeneficiarioFinal())) {
            $base['beneficiarioFinal'] = (new BeneficiarioFinal($boleto->getBeneficiarioFinal()))->toArray();
        }

        if (!empty($boleto->getDesconto())) {
            $desconto = new DescontoConfiguracao($boleto->getDesconto());
            $descontoArray = $desconto->toArray();
            $base = $base + $descontoArray;
        }

        if (!empty($boleto->getJuros())) {
            $juros = new JurosConfiguracao($boleto->getJuros());
            $jurosArray = $juros->toArray();
            $base = $base + $jurosArray;
        }

        if (!empty($boleto->getMensagens())) {
            $mensagem = new Mensagens($boleto->getMensagens());
            $msgArray = $mensagem->toArray();
            $base = $base + $msgArray;
        }

        if (!empty($boleto->getInformativo())) {
            $info = new Informativo($boleto->getInformativo());
            $infoArray = $info->toArray();
            $base = $base + $infoArray;
        }

        return array_filter($base, function ($value) {
            return !empty($value);
        });
    }

    public static function mapFromQuery($data): BoletoDomain {

        if(!empty($data['beneficiarioFinal']['nome'])) {
            $beneficiarioFinal = new BeneficiarioFinalDomain($data['beneficiarioFinal']['nome'], $data['beneficiarioFinal']['documento'], null, $data['beneficiarioFinal']['codigo']);
        } else {
            throw new \InvalidArgumentException("Boleto não cadastrado");
        }
        
        if(!empty($data['pagador']['nome'])) {
            $pagador = new PagadorDomain($data['pagador']['nome'], $data['pagador']['documento'], null, $data['pagador']['codigo']);
        } else {
            throw new \InvalidArgumentException("Boleto não cadastrado");
        }

        $dataEmissao = DateTime::createFromFormat('Y-m-d', $data['dataEmissao']);
        $dataVencimento = DateTime::createFromFormat('Y-m-d', $data['dataVencimento']);
        $valorNominal = $data['valorNominal'];
        $seuNumero = $data['seuNumero'];
        $nossoNumero = $data['nossoNumero'];

        $boleto = new BoletoDomain($beneficiarioFinal, $pagador, $valorNominal, null, null, null, $seuNumero, $dataVencimento, $dataEmissao, $nossoNumero);

        $boleto->setCarteira($data['carteira']);
        $boleto->setStatus($data['situacao']);

        if (!empty($data['descontos'])) {
            $descontos = [];

            usort($data['descontos'], function ($a, $b) {
                return $a['numeroOrdem'] <=> $b['numeroOrdem'];
            });

            $c = 0;
            foreach ($data['descontos'] as $desconto) {
                $descontos[$c++] = new \SicrediAPI\Domain\Boleto\Desconto($desconto['valorDesconto'], \DateTime::createFromFormat('Y-m-d', $desconto['dataLimite']));
            }

            $descontoConfiguracao = new \SicrediAPI\Domain\Boleto\DescontoConfiguracao($descontos, ($data['tipoDesconto'] == 'VALOR' || strpos($data['tipoDesconto'], 'VALOR') > 0) ? DescontoConfiguracaoDomain::VALOR : DescontoConfiguracaoDomain::PERCENTUAL, $data['descontoAntecipacao']);
            $boleto->setDesconto($descontoConfiguracao);
        }

        if (!empty($data['juros'])) {
            $juros = new JurosConfiguracaoDomain(($data['tipoJuros'] == 'VALOR' || strpos($data['tipoJuros'], 'VALOR') > 0) ? JurosConfiguracaoDomain::VALOR : JurosConfiguracaoDomain::PERCENTUAL, $data['juros'], $data['multa']);
            $boleto->setJuros($juros);
        }

        $InformacaoDePagamento = InformacaoDePagamento::fromArray($data);
        $boleto->setInformacaoDePagamento($InformacaoDePagamento);

        if (!empty($data['dadosLiquidacao'])) {
            $boleto->setLiquidacao(LiquidacaoDomain::fromArray($data['dadosLiquidacao']));
        }

        return $boleto;
    }

    /**
     * @param array $data
     * @return BoletoDomain
     */
    public static function mapFromQueryDailyLiquidations(array $data) {
        if (empty($data)) {
            return [];
        }

        $liquidacoes = [];

        foreach ($data as $item) {
            // dataPagamento = 2021-09-01 07:23:28.7
            $date = DateTime::createFromFormat('Y-m-d H:i:s.u', $item['dataPagamento']);
            $liquidacao = new LiquidacaoDomain(
                    $date,
                    (float) $item['valorLiquidado'],
                    (float) $item['multaLiquida'],
                    (float) $item['abatimentoLiquido'],
                    (float) $item['jurosLiquido'],
                    (float) $item['descontoLiquido'],
                    (string) $item['nossoNumero'],
                    (string) $item['seuNumero'],
                    (float) $item['valor'],
                    (string) $item['tipoLiquidacao']
            );

            $liquidacoes[] = $liquidacao;
        }

        return $liquidacoes;
    }
}
