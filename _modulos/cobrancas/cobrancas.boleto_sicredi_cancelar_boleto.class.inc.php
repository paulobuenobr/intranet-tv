<?php

require_once __DIR__ . '/vendor/autoload.php';

class cobrancas_boleto_sicredi_cancelar_boleto {

    var $bancodedados;
    var $homologacao;
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
     * @param type $cobranca_id
     * @param type $config
     * @param type $homologacao
     * @return type
     */
    function cancelar($cobranca_id, $config, $tokenSalvo = null, $usarTokenSalvo = true, $homologacao = false) {

        $this->homologacao = $homologacao;

        $this->tokenSalvo = $tokenSalvo;
        $this->usarTokenSalvo = $usarTokenSalvo;
        $boletoClient = $this->sicrediAPIClient($config, $homologacao);
        if (!$boletoClient) {
            return false;
        }

        $query = $this->bancodedados->query("SELECT coa_nosso_numero, coa_parametros parametros FROM tab_cobrancas 
                                             WHERE coa_nosso_numero IS NOT NULL AND coa_id = {$cobranca_id}
                                             AND cof_id IN (SELECT cof_id FROM tab_cobrancas_formatos WHERE cof_parametros LIKE '%sicredi%')");

        $retorno = [];
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $parametros = unserialize($dados["parametros"]);
            $num = $parametros['parametros']['boleto']['nosso_numero'] ? $parametros['parametros']['boleto']['nosso_numero'] : $parametros['boleto']['nosso_numero'];
            $nosso_numero = preg_replace('/[^0-9]/', '', $num);
			
            try {
                $retornoBoleto = $boletoClient->boletoPeloNossoNumero($nosso_numero);
            } catch (Exception $exc) {
                
            }
			
			if(!empty($retornoBoleto->error) && $retornoBoleto->error == "NOT_FOUND" ) {
				$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 0, coa_data_cancelamento = NOW() WHERE coa_id = {$cobranca_id}");
                return $retornoBoleto;
            } else if(!empty($retornoBoleto->situacao) && ($retornoBoleto->situacao == "BAIXADO POR SOLICITACAO")) {
				$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 0, coa_data_cancelamento = NOW() WHERE coa_id = {$cobranca_id}");
                return $retornoBoleto;
            }
            
			try {
                $retorno = $boletoClient->cancelarPorBaixa($nosso_numero);
            } catch (Exception $ex) {
                (object) $erro = null;
                $erro->error = "";
                $erro->code = "0";
                $erro->message = $ex;
                $erro->coa_id = $cobranca_id;
                $this->insereErroLogSicredi($erro);
                $retorno = false;
            }
        }

        if(is_object($retorno)) {
            if($retorno->erro) {
                $er = $retorno;
	            $er->coa_id = $cobranca_id;
	            $this->insereErroLogSicredi($er);
	
	            $retorno = $this->tipoErrosPelaMSG(utf8_decode($er->message));
                $codErro = preg_replace('/[^0-9]/', '', $er->message);
                if($codErro == 77) {
                    $this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 0, coa_data_cancelamento = NOW() WHERE coa_id = {$cobranca_id}");
                    return true;
                }
	        }
        }
        
        if (is_array($retorno)) {
            if(empty($retorno['tipoMensagem'])) {
                $json->erro = true;
                $json->code = "500";
                $json->message = "Instabilidade no servidor";
                $retorno = $this->tipoErrosPelaMSG(utf8_decode($er->message));
            }
        }

        if ($retorno) {
            $query = "UPDATE tab_cobrancas SET coa_aguardando_registro = 0, coa_data_cancelamento = NOW() WHERE coa_id = {$cobranca_id}";

            $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
            $cobrancas_retornos->registraComentarioCobranca($cobranca_id, "<b>Ação:</b> Boleto cancelado no banco");
        } else {
            $query = "UPDATE tab_cobrancas SET coa_aguardando_registro = 1, coa_data_cancelamento = null WHERE coa_id = {$cobranca_id}";
            $retorno = false;
        }

        $this->bancodedados->query($query);
        return $retorno;
    }

    function tipoErrosPelaMSG($msg) {

        switch ($this->removeAcentuacao($msg)) {
            case $this->removeAcentuacao("Operação não permitida: Título já baixado"):
            case $this->removeAcentuacao("Operação não permitida: Título rejeitado"):
            case $this->removeAcentuacao("Operação não permitida: Título já liquidado"):
            case $this->removeAcentuacao("Operação não permitida: Título em fluxo de negativação ou protesto"):
            case $this->removeAcentuacao("Sua solicitação anterior está em processamento, aguarde alguns instantes"):
                return true;
            default:
                return false;
        }
    }
    
    
    /**
     * 
     * @param type $str
     * @return type
     */
    function removeAcentuacao($str) {

        $limpa = preg_replace('/[^\p{L}\p{N}\s]/', '', $str);
        $texto = strtr(trim(trim($limpa, ","), "-"), 'àáâãäçèéêëìíîïñòóôõöùúûüıÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜİ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

        if (mb_detect_encoding($texto, 'ISO-8859-1', true)) {
            $texto = mb_convert_encoding($texto, "ISO-8859-1", "UTF-8");
        }
        $saida = strtolower(preg_replace('/[*.-\/,-\/*%$#@<>:;?!\'"|\\¨+=}\]\{\[]/', '', trim($texto)));;
        
        return $saida;
    }

    /**
     * 
     * @param type $er
     */
    function insereErroLogSicredi($er) {

        $query = "INSERT INTO tab_sicredi_log (timetamp, error, code, message, coa_id)
                    VALUES ( NOW() , '{$er->error}', '{$er->code}', '" . utf8_decode($er->message) . "', '{$er->coa_id}')";

        $this->bancodedados->query($query);
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
