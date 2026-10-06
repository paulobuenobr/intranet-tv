<?php

//header("Content-Type: text/plain");
header('Content-Type: text/html;charset=ISO-8859-1');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include dirname(__DIR__, 1) . '/vendor/autoload.php';

use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;

class CancelarLoteRps {

    var $protocolo;
    var $configJson;
    var $cert;
    var $criarXMLRetorno;
    var $fake;
    var $bancodedados;
    var $nota;

    /**
     * 
     * @param type $bd
     * @param type $protocolo
     * @param type $configJson
     * @param type $cert
     * @param type $criarXMLRetorno
     * @param type $fake
     */
    function __construct($bd, $protocolo, $configJson, $cert, $criarXMLRetorno = false, $fake = false, $class = null) {
        $this->protocolo = $protocolo;
        $this->configJson = $configJson;
        $this->cert = $cert;
        $this->criarXMLRetorno = $criarXMLRetorno;
        $this->bancodedados = $bd;
        $this->fake = $fake;
        $this->log = $class;
    }

    function cancelarLoteRpsPorNumero($notaId = null) {

        try {

            $soap = new SoapCurl();
            $soap->disableCertValidation(true);
            $tools = new Tools($this->configJson, $this->cert);
            $tools->loadSoapClass($soap);
            $caminhoXML = __DIR__ . '\xml\CancelarLoteRps.xml';
            $xml = file_get_contents($caminhoXML);
            $response = $tools->cancelarLoteRpsXml($this->remontaXML($xml), "_" . $this->protocolo, $this->criarXMLRetorno, $this->fake);

            if ($response) {
                if (!empty($response->CancelarLoteRpsResult->ListaMensagemRetorno->MensagemRetorno)) {
                    $this->log->insereLogs($notaId, $response->CancelarLoteRpsResult->ListaMensagemRetorno->MensagemRetorno);
                    if ($response->CancelarLoteRpsResult->ListaMensagemRetorno->MensagemRetorno->Codigo = "E86") {

                        return false;
                    }
                } else {
                    $this->atualzarNotaCancelada($this->protocolo);
                }
                return $response;
            }
            return false;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    function remontaXML($xml) {

        $this->configJson = json_decode($this->configJson);
        $xml = str_replace('[cnpj]', $this->configJson->cnpj, $xml);
        $xml = str_replace('[inscricaoMunicipal]', $this->configJson->im, $xml);
        $xml = str_replace('[protocolo]', $this->protocolo, $xml);

        return $xml;
    }

    function atualzarNotaCancelada($protocolo) {

        $q = "UPDATE tab_notas SET nota_gerada = 1, status_id = 1 WHERE protocolo = '$protocolo'";
        $this->bancodedados->query($q);
    }
}
