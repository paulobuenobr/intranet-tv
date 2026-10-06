<?php

require_once("_global/modulo.class.inc.php");

class cobrancas_boleto_sicredi extends modulo {

    var $bancodedados;
    var $config;
    var $cert;
    var $homologacao = false /* true sera usado para homologar */;
    var $tokenSalvo;
    var $usarTokenSalvo;

    /**
     * 
     * @param type $bancodedados
     */
    function __construct($bancodedados) {

        $this->bancodedados = $bancodedados;
        (object) $this->config = null;
        
        $this->eHHomologacao(); 
        
        if ($this->homologacao) {
            $prod = $this->buscaDadosLoginSicredi(1);
        } else {
            $prod = $this->buscaDadosLoginSicredi(2);
        }
        
        $this->config->SICREDI_API_KEY = $prod['api_key'];
        $this->config->SICREDI_CLIENT_ID = $prod['client_id'];
        $this->config->SICREDI_CLIENT_SECRET = $prod['client_secret'];
        $this->config->SICREDI_PASSWORD = $prod['password'];
        $this->config->SICREDI_USERNAME = $prod['username'];
        $this->config->SICREDI_BENEFICIARY = $prod['beneficiary'];
        $this->config->SICREDI_COOPERATIVE = $prod['cooperative'];
        $this->config->SICREDI_POST = $prod['post'];
    }

    function eHHomologacao() {
		
        $sql = "SELECT * FROM tab_homologacao WHERE tipo LIKE 'sicredi' LIMIT 1";
        $query = $this->bancodedados->query($sql);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $this->homologacao =  false;
            if($dados['homologa']) {
                $this->homologacao =  true;
            }
        }
    }
    
    function buscaDadosLoginSicredi($id) {
        $sql = "SELECT * FROM tab_sicredi_login where id = $id;";
        $query = $this->bancodedados->query($sql);

        return $this->bancodedados->resultadoArray($query);
    }

    /**
     * 
     * @param type $formato_id
     * @param type $cobranca_id
     * @param type $dadosBoleto
     * @return type
     */
    function criarBoleto($formato_id, $cobranca_id) {

        $this->buscaToken();
        $criarBoleto = controle::instanciaObjeto("cobrancas.boleto_sicredi_criar_boleto", $this->bancodedados);
        $retorno = $criarBoleto->criar($formato_id, $cobranca_id, $this->config, $this->tokenSalvo, $this->usarTokenSalvo, $this->homologacao);

        return $retorno;
    }

    function pesquisaNaoLiquidados() {

        $this->buscaToken();
        $queryBoleto = controle::instanciaObjeto("cobrancas.boleto_sicredi_query_boleto", $this->bancodedados);
        $retorno = $queryBoleto->pesquisaNaoLiquidados($this->config, $this->tokenSalvo, $this->usarTokenSalvo, $this->homologacao);

        return $retorno;
    }

    /**
     * 
     * @param type $nosso_numero
     * @return type
     */
    function buscaBoletoPeloNossoNumero($nosso_numero) {

        $this->buscaToken();
        $queryBoleto = controle::instanciaObjeto("cobrancas.boleto_sicredi_query_boleto", $this->bancodedados);
        $retorno = $queryBoleto->buscaBoletoPeloNossoNumero($nosso_numero, $this->config, $this->tokenSalvo, $this->usarTokenSalvo, $this->homologacao);

        return $retorno;
    }

    function liquidadosPorDia() {

        $this->buscaToken();
        $queryLiquidacao = controle::instanciaObjeto("cobrancas.boleto_sicredi_query_liquidacao", $this->bancodedados);
        $retorno = $queryLiquidacao->liquidadosPorDia($this->config, $this->tokenSalvo, $this->usarTokenSalvo, $this->homologacao);

        return $retorno;
    }

    function liquidadosHoje() {

        $this->buscaToken();
        $queryLiquidacao = controle::instanciaObjeto("cobrancas.boleto_sicredi_query_liquidacao", $this->bancodedados);
        $retorno = $queryLiquidacao->liquidadosPorDia($this->config, $this->tokenSalvo, $this->usarTokenSalvo, $this->homologacao, false);

        return $retorno;
    }

    /**
     * 
     * @param type $dados
     * @return type
     */
    function liquidarPorMovimentacao($dados) {

        $queryLiquidacao = controle::instanciaObjeto("cobrancas.boleto_sicredi_query_liquidacao", $this->bancodedados);
        $retorno = $queryLiquidacao->liquidarPorMovimentacao($dados);

        return $retorno;
    }

    /**
     * 
     * @param type $cobranca_id
     * @return type
     */
    function cancelarBoleto($cobranca_id = null) {

        $this->buscaToken();
        $cancelarBoleto = controle::instanciaObjeto("cobrancas.boleto_sicredi_cancelar_boleto", $this->bancodedados);
        $retorno = $cancelarBoleto->cancelar($cobranca_id, $this->config, $this->tokenSalvo, $this->usarTokenSalvo, $this->homologacao);

        return $retorno;
    }

    function buscaToken() {

        $sql = "SELECT * FROM tab_sicredi_authenticate WHERE id = 1;";
        $query = $this->bancodedados->query($sql);
        $token = $this->bancodedados->resultadoArray($query);
        $token['not-before-policy'] = $token['not_before_policy'];

        $this->tokenSalvo = null;
        if ($token["access_token"] == null) {
            $this->usarTokenSalvo = false;
        } else {
            $agora = strtotime(date('Y-m-d H:i:s'));
            $expiraEM = $token['expires_in'] - 30;
            $expira = strtotime(date('Y-m-d H:i:s', strtotime("{$token['data']} {$expiraEM} second")));
            if ($agora > $expira) {
                $this->usarTokenSalvo = false;
            } else {
                $token["noUpdate"] = true;
                $this->tokenSalvo = $token;
                $this->usarTokenSalvo = true;
            }
        }
    }
}
