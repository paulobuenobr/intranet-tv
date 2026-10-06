<?php

require_once __DIR__ . '/vendor/autoload.php';

class cobrancas_boleto_sicredi_query_liquidacao {

    var $bancodedados;
    var $homologacao;
    var $client;
    var $tokenSalvo;
    var $usarTokenSalvo;
    var $util;

    /**
     * @param type $bd
     */
    function __construct($bd) {

        $this->bancodedados = $bd;
        $this->util = controle::instanciaObjeto("cobrancas.boleto_sicredi_util", $this->bancodedados);
    }

    /**
     * 
     * @param type $config
     * @param type $tokenSalvo
     * @param type $usarTokenSalvo
     * @param type $homologacao
     * @param type $ontem
     * @return type
     */
    function liquidadosPorDia($config, $tokenSalvo = null, $usarTokenSalvo = true, $homologacao = false, $ontem = true, $pagina = 0) {

        $this->tokenSalvo = $tokenSalvo;
        $this->usarTokenSalvo = $usarTokenSalvo;

        $boletoClient = $this->sicrediAPIClient($config, $homologacao);
        if (!$boletoClient) {
            return false;
        }

        try {
            $liquidados = $ontem ? $boletoClient->getLiquidacaoPorDia(date('d/m/Y', strtotime("-1 day")),$pagina) : $boletoClient->getLiquidacaoPorDia(date('d/m/Y'),$pagina);
        } catch (Exception $ex) {
            $liquidados = false;
        }

        $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
        $liq = $naoLiq = 0;
        foreach ($liquidados['items'] as $value) {
            $retorno = $this->jaPago($value);
            if ($retorno && $retorno != "true") {
                $registro = $this->montaRetornoParabaixa($value);
                $cobrancas_retornos->darBaixa($registro);
                $this->atualizandoCobranca($value);
                $liq++;
            } else {
                $naoLiq++;
            }
        }
        $tipo = $ontem ? "ONTEM" : "HOJE";
        $this->insereLiquidadosENaoLiq($liq, $naoLiq, $tipo);
        
        if($liquidados['hasNext'] == true) {
            $pagina = $pagina + 1;
            $this->liquidadosPorDia($config, $tokenSalvo, $usarTokenSalvo, $homologacao, $ontem, $pagina);
        }
        
        return $liquidados;
    }
    


    /**
     * 
     * @param type $executadoBaixa
     * @param type $jaBaixada
     * @param type $tipo
     */
    public function insereLiquidadosENaoLiq($executadoBaixa, $jaBaixada, $tipo) {

        $sql = "INSERT INTO tab_sicredi_log_liquidados(executado_baixa , ja_baixada, tipo) VALUES ($executadoBaixa , $jaBaixada, '$tipo')";
        $this->bancodedados->query($sql);
    }

    /**
     * 
     * @param type $dados
     */
    function liquidarPorMovimentacao($dados) {

        $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
        $retorno = $this->jaPago($dados);
        if ($retorno) {
            $cobrancas_retornos->darBaixa($dados);
            $this->atualizandoCobranca($dados);

            return true;
        } else {
            
            return false;
        }
    }

    /**
     * 
     * @param type $config
     * @param type $homologacao
     * @return type
     */
    function sicrediAPIClient($config, $homologacao = false) {

        $this->homologacao = $homologacao;
        $this->client = new \SicrediAPI\Client(
                $config->SICREDI_API_KEY,
                $config->SICREDI_COOPERATIVE,
                $config->SICREDI_POST,
                $config->SICREDI_BENEFICIARY,
                new \GuzzleHttp\Client(),
                $this->homologacao);

        try {
            $token = $this->client->authenticate(
                    $config->SICREDI_USERNAME,
                    $config->SICREDI_PASSWORD, $this->tokenSalvo, $this->usarTokenSalvo);

            if(empty($this->tokenSalvo["noUpdate"])) {
                $this->util->atualizaTokenNoBanco($token, $this->usarTokenSalvo);
            }

            $retorno = $this->client->boleto();
        } catch (Exception $exc) {
            $retorno = false;
        }

        return $retorno;
    }

    /**
     * 
     * @param type $item
     */
    function atualizandoCobranca($item) {

        $data = date('Y-m-d');
        $seuNumero = (int)$item['seuNumero'];
        $query = "UPDATE tab_cobrancas SET coa_aguardando_registro = 0 WHERE coa_id = '{$seuNumero}'";
        $this->bancodedados->query($query);
    }

    /**
     * 
     * @param type $pago
     * @return type
     */
    function montaRetornoParabaixa($pago) {

        $registro["data_processamento"] = $pago['dataPagamento'];
        $registro["data_credito"] = $pago['dataPagamento'];
        $registro["valor_juro"] = $pago['jurosLiquido'] * 100;
        $registro["valor_desconto"] = $pago['descontoLiquido'] * 100;
        $registro["valor_acrescimo"] = $pago['multaLiquida'] * 100;
        $registro["valor_decrescimo"] = $pago['abatimentoLiquido'] * 100;
        $registro["valor_total"] = $pago['valorLiquidado'] * 100;
        $registro["valor_iof"] = 0;
        $registro["id"] = (int)$pago['seuNumero'];
        $registro["nossoNumero"] = $pago['nossoNumero'];
        $registro["tipoLiquidacao"] = $pago['tipoLiquidacao'];
        $registro["valor_original"] = $pago['valor'] * 100;

        return $registro;
    }

    /**
     * @param type $info
     * @return type
     */
    function jaPago($info) {

        $seuNumero = substr((int) $info['seuNumero'], 0, 8);

        $q = "SELECT * FROM tab_cobrancas
              WHERE cot_id IN(1) AND (coa_data_pagamento IS NULL OR coa_data_pagamento = '0000-00-00' ) AND coa_id = '{$seuNumero}'";

        $cri = $this->bancodedados->resultadoArray($this->bancodedados->query($q));
        if ($cri) {
            return true;
        }
        return false;
    }
}
