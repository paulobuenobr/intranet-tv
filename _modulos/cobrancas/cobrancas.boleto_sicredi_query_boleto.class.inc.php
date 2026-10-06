<?php

require_once __DIR__ . '/vendor/autoload.php';

class cobrancas_boleto_sicredi_query_boleto {

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
     * @return type
     */
    function pesquisaNaoLiquidados($config, $tokenSalvo = null, $usarTokenSalvo = true, $homologacao = false) {

        $this->tokenSalvo = $tokenSalvo;
        $this->usarTokenSalvo = $usarTokenSalvo;
        $boletoClient = $this->sicrediAPIClient($config, $homologacao);
        if (!$boletoClient) {
            return false;
        }
        $query = $this->bancodedados->query("SELECT * from tab_cobrancas WHERE cof_id IN (SELECT cof_id FROM tab_cobrancas_formatos WHERE cof_parametros LIKE '%sicredi%') AND coa_aguardando_registro = 0");
        $retorno = [];
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            if ($dados['cot_id'] != 12) {
                $parametros = unserialize($dados["coa_parametros"]);
                $nosso_numero = preg_replace("/\D/", "", $parametros['parametros']['boleto']['nosso_numero']);
                try {
                    $retorno[] = $boletoClient->query($nosso_numero);
                } catch (Exception $ex) {
                    $retorno = false;
                }
            }
        }

        return $retorno;
    }

    /**
     * 
     * @param type $nosso_numero
     * @param type $config
     * @param type $homologacao
     * @return type
     */
    function buscaBoletoPeloNossoNumero($nosso_numero, $config, $tokenSalvo = null, $usarTokenSalvo = true, $homologacao = false) {

        $this->tokenSalvo = $tokenSalvo;
        $this->usarTokenSalvo = $usarTokenSalvo;
        $boletoClient = $this->sicrediAPIClient($config, $homologacao);
        try {
            $retorno = $boletoClient->boletoPeloNossoNumero($nosso_numero);

            return $retorno;
        } catch (Exception $ex) {

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

            $this->util->atualizaTokenNoBanco($token, $this->usarTokenSalvo);

            $retorno = $this->client->boleto();
        } catch (Exception $exc) {
            $retorno = false;
        }

        return $retorno;
    }
}
