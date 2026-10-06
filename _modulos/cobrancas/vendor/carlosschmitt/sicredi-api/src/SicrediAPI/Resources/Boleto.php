<?php

namespace SicrediAPI\Resources;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use SicrediAPI\Domain\Boleto\Boleto as BoletoDomain;
use SicrediAPI\Domain\Boleto\Liquidacao;
use SicrediAPI\Domain\Boleto\InformacaoDePagamento;
use SicrediAPI\Mappers\Boleto as BoletoMapper;

class Boleto extends ResourceAbstract {

    public function criar(BoletoDomain $boleto): BoletoDomain {
        $payload = BoletoMapper::mapCriarBoleto($boleto);

        $response = $this->post('/cobranca/boleto/v1/boletos', [
            'json' => $payload,
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);

        $json = json_decode($response);
        if (!empty($json->code) || !empty($json)) {
            $json->erro = true;
            $boleto->setErro($json);
            
            return $boleto;
        } elseif(empty($response)) {
            $json->erro = true;
            $json->code = "0";
            $json->message = "Instabilidade no servidor";
            $boleto->setErro($json);
            
            return $boleto;
        } else {
            $boleto->setErro(false);
        }

        $InformacaoDePagamento = InformacaoDePagamento::fromArray($response);
        $boleto->setInformacaoDePagamento($InformacaoDePagamento);
        if ($boleto->getNossoNumero() === null) {
            $boleto->setNossoNumero($InformacaoDePagamento->getNossoNumero());
        }

        return $boleto;
    }

    public function cancelar($nossoNumero) {

        $response = $this->patch('/cobranca/boleto/v1/boletos/split/cancelar-split/', [
            'json' => [
                'codigoBeneficiario' => $this->apiClient->getCodigoDoBeneficiario(),
                'nossoNumero' => $nossoNumero
            ],
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);

        if (empty($response['parcelasCanceladas'])) {
            return false;
        } elseif(empty($response)) {
            return false;
        }

        return $response;
    }

    public function cancelarPorBaixa($nossoNumero) {

        $response = $this->patch('/cobranca/boleto/v1/boletos/' . $nossoNumero . '/baixa/', [
            'body' => '{}',
            'headers' => [
                'codigoBeneficiario' => $this->apiClient->getCodigoDoBeneficiario(),
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);
        
        $json = json_decode($response);
        if (!empty($json->code) || !empty($json)) {
            $json->erro = true;

            return $json;
        } elseif(empty($response)) {
            $json->erro = true;
            $json->code = "500";
            $json->message = "Instabilidade no servidor";
            
            return $json;
        } else {
            return $response;
        }

    }

    public function query($nossoNumero) {

        $response = $this->get('/cobranca/boleto/v1/boletos/', [
            'query' => [
                'codigoBeneficiario' => $this->apiClient->getCodigoDoBeneficiario(),
                'nossoNumero' => $nossoNumero,
            ],
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);

        $json = json_decode($response);
        if (!empty($json->code) || !empty($json)) {
            $json->erro = true;

            return $json;
        } elseif(empty($response)) {
            $json->erro = true;
            $json->code = "500";
            $json->message = "Instabilidade no servidor";
            
            return $json;
        } else {
            $boleto = BoletoMapper::mapFromQuery($response);
            return $boleto;
        }
    }

    /**
     * Returns the Boletos liquidated in a specific day.
     * This method returns an instance of Meta\Paginator, which is an iterable object.
     * Upon reaching the end of the page, the next page is automatically fetched.
     * Beware of the performance implications of this method. Memory usage will increase as more pages are fetched.
     *
     * @param DateTime $day
     * @return Meta\Paginator
     * @throws GuzzleException
     */
    public function queryDailyLiquidations(string $day) {
        $liquidations = new Meta\Paginator($this, function ($page) use ($day) {
                    return $this->getDailyLiquidationsByPage($page, $day);
                }, function ($items) {
                    return BoletoMapper::mapFromQueryDailyLiquidations($items);
                });

        return $liquidations;
    }

    /**
     * Returns the Boletos liquidated in a specific day
     * @param DateTime $day
     * @return Liquidacao[]
     * @throws GuzzleException
     */
    private function getDailyLiquidationsByPage(int $page = 1, string $day) {
        $response = $this->get('/cobranca/boleto/v1/boletos/liquidados/dia', [
            'query' => [
                'codigoBeneficiario' => $this->apiClient->getBeneficiarioFinalCode(),
                'dia' => "$day",
                'pagina' => $page,
            ],
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);

        return $response;
    }

    public function getLiquidacaoPorDia(string $day, $pagina = 0) {
        $cod_beni = $this->apiClient->getCodigoDoBeneficiario();
        $pagina = $pagina > 0 ? "&pagina=$pagina" : "";
        $response = $this->get('/cobranca/boleto/v1/boletos/liquidados/dia?codigoBeneficiario=' . $cod_beni . '&dia=' . $day . $pagina, [
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);

        return $response;
    }

    public function boletoPeloNossoNumero(string $nossoNumero) {
        $cod_beni = $this->apiClient->getCodigoDoBeneficiario();
        $response = $this->get('/cobranca/boleto/v1/boletos?codigoBeneficiario=' . $cod_beni . '&nossoNumero=' . $nossoNumero, [
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperativa(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);

        $json = json_decode($response);
        if (!empty($json->code) || !empty($json)) {
            if($json->code == 404) {
                $json->erro = true;
                $json->message= "Título não encontrado.";
                return $json;
            }
            return false;
        } elseif(empty($response)) {
            $json->message = "Instabilidade na API da Sicredi";
            
            return $json;
        } else {
            return $response;
        }

    }

    public function print(string $numericRepresentation) {
        $response = $this->get('/cobranca/boleto/v1/boletos/pdf', [
            'query' => [
                'linhaDigitavel' => $numericRepresentation
            ]
                ], true);

        return $response;
    }
}
