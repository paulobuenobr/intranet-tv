<?php

//header('Content-Type: text/html; charset=UTF-8');
require_once("_global/modulo.class.inc.php");
//include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/ctb/ConsultarNfse.php';
include __DIR__ . '/ctb/RecepcionarLoteRps.php';
include __DIR__ . '/ctb/ConsultarLoteRps.php';
include __DIR__ . '/ctb/CancelarLoteRps.php';
include __DIR__ . '/sjsc/NotaSJ.php';

use NFePHP\Common\Certificate;

class nfse extends modulo {

    var $bancodedados;
    var $configJson;
    var $cert;
    var $fake = false;  // true - Usado 'true' para ser teste local fake; false usado para enviar para api 
    var $teste = false; // true - Usado 'true' para teste em envio para o servidor
    var $criarXMLRetorno = false; // Criar arquivo XML de envio e retorno;

    function __construct($bancodedados) {

        $this->bancodedados = $bancodedados;
        
        $this->eHHomologacao();

        $config = ['cnpj' => '17989371000140', 'im' => '010108063136', 'cmun' => '4106902', 'razao' => 'JK2 TELECOMUNICACOES LTDA', 'tpamb' => 1];
        $password = '12345678';
        $caminho = __DIR__ . '/ctb/certificados/certificado.pfx';
        $content = file_get_contents($caminho);

        $this->configJson = json_encode($config);
        $this->cert = Certificate::readPfx($content, $password);

        if (!empty($_GET['a'])) {
            if ($_GET['a'] == "consultarNota") {
                $this->consultarNotaCtb($_GET['i']);
            }

            if ($_GET['a'] == "consultarLoteRps") {
                $this->consultarLoteRpsCtb();
            }

            if ($_GET['a'] == "recepcionarLoteRps") {
                $this->recepcionarLoteRpsCtb();
            }

            if ($_GET['a'] == "notasNaoGeradas") {
                $this->recepcionarLoteRpsNaoGeradasCtb();
            }

            if ($_GET['a'] == "emiteNotaSJSC") {
                $this->emiteNotaSJSC();
            }

            if ($_GET['a'] == "emiteNotaNaoGeradasSJSC") {
                $this->emiteNotasNaoGeradasSJSC();
            }

            if ($_GET['a'] == "consultarNotaSJSC") {
                $this->consultarNotaSJSC($_GET['i'], $_GET['c']);
            }
        }
    }
    
    function eHHomologacao() {
		
        $sql = "SELECT * FROM tab_homologacao WHERE tipo LIKE 'nfse' LIMIT 1";
        $query = $this->bancodedados->query($sql);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $this->fake =  true;
            if($dados['homologa']) {
                $this->fake =  false;
            }
        }
    }


    function consultarNotaCtb($numeroDaNota = null) {

        $nota = new ConsultarNfse($numeroDaNota, $this->configJson, $this->cert, $this->criarXMLRetorno);
        $retorno = $nota->ConsultarNfsePorNumero();

        return $retorno;
    }

    function cancelarLoteRpsCtb($protocoloDaNota = null, $notaId = null) {

        $recepcionar = new RecepcionarLoteRps($this->bancodedados, null, null, false);
        $nota = new CancelarLoteRps($this->bancodedados, $protocoloDaNota, $this->configJson, $this->cert, $this->criarXMLRetorno, $this->fake, $recepcionar);
        $retorno = $nota->cancelarLoteRpsPorNumero($notaId);

        return $retorno;
    }

    function recepcionarLoteRpsCtb() {

        $consultarNfse = new RecepcionarLoteRps($this->bancodedados, $this->configJson, $this->cert, $this->criarXMLRetorno, $this->fake);
        $retorno = $consultarNfse->recepcionandoLoteRps();

        return $retorno;
    }

    function recepcionarLoteRpsNaoGeradasCtb() {

        $nota = new RecepcionarLoteRps($this->bancodedados, $this->configJson, $this->cert, $this->criarXMLRetorno, $this->fake);
        $retorno = $nota->gerarNotaNaoGeradas();

        return $retorno;
    }

    function consultarLoteRpsCtb() {

        $nota = new ConsultarLoteRps($this->bancodedados, $this->configJson, $this->cert, $this->criarXMLRetorno, $this->fake);
        $retorno = $nota->consultarLoteRpsPorProtocolo();

        return $retorno;
    }

    /**
     * 
     * @param array|null $cobranca
     * @param bool $geraEmAberto
     * @return object
     * @throws Exception
     */
    function emiteNotaSJSC($cobranca = null, $geraEmAberto = false) {

        $recepcionar = new RecepcionarLoteRps($this->bancodedados, null, null, false);
        $nota = new NotaSJ($this->bancodedados, $this->criarXMLRetorno, $recepcionar, $this->fake, $this->teste);
        $retorno = $nota->emissao($cobranca, $geraEmAberto);

        return $retorno;
    }

    /**
     * 
     * @param bool $naoGeradasPorId
     * @param int|null $id
     * @return object
     * @throws Exception
     */
    function emiteNotasNaoGeradasSJSC($naoGeradasPorId = false, $id = null) {

        $recepcionar = new RecepcionarLoteRps($this->bancodedados, null, null, true, false);
        $nota = new NotaSJ($this->bancodedados, $this->criarXMLRetorno, $recepcionar, $this->fake, $this->teste);
        $retorno = $nota->gerarNotaNaoGeradas($naoGeradasPorId, $id);

        return $retorno;
    }

    /**
     * 
     * @param int|null $notaId
     * @param int|null $numero
     * @return object
     * @throws Exception
     */
    function cancelarNotaSJSC($notaId = null, $numero = null) {

        $recepcionar = new RecepcionarLoteRps($this->bancodedados, null, null, true, false);
        $nota = new NotaSJ($this->bancodedados, $this->criarXMLRetorno, $recepcionar, $this->fake, $this->teste);
        $retorno = $nota->gerarCancelamento($notaId, $numero);

        return $retorno;
    }
    
    function gerarNaoCanceladosSJSC() {
        
        $recepcionar = new RecepcionarLoteRps($this->bancodedados, null, null, true, false);
        $nota = new NotaSJ($this->bancodedados, $this->criarXMLRetorno, $recepcionar, $this->fake, $this->teste);
        $retorno = $nota->gerarNaoCancelados();

        return $retorno;
    }

    /**
     * 
     * @param int|null $numero
     * @param bool $porCodigo
     * @return object
     * @throws Exception
     */
    function consultarNotaSJSC($numero = null, $porCodigo = false) {

        $recepcionar = new RecepcionarLoteRps($this->bancodedados, null, null, false);
        $nota = new NotaSJ($this->bancodedados, $this->criarXMLRetorno, $recepcionar, $this->fake);
        $retorno = $nota->consultarNota($numero, $porCodigo);

        return $retorno;
    }
}
