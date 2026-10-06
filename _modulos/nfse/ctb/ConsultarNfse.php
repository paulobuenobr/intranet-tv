<?php

header("Content-Type: text/plain");
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include dirname(__DIR__, 1) . '/vendor/autoload.php';

use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;

class ConsultarNfse {

    var $nota;
    var $configJson;
    var $cert;
    var $criarXMLRetorno;

    function __construct($nota, $configJson, $cert, $criarXMLRetorno = false) {
        $this->nota = $nota;
        $this->configJson = $configJson;
        $this->cert = $cert;
        $this->criarXMLRetorno = $criarXMLRetorno;
    }

    function ConsultarNfsePorNumero() {

        try {

            $soap = new SoapCurl();
            $soap->disableCertValidation(true);
            $tools = new Tools($this->configJson, $this->cert);
            $tools->loadSoapClass($soap);
            $caminhoXML = __DIR__ . '\xml\ConsultarNfse.xml';
            $xml = file_get_contents($caminhoXML);
            $response = $tools->consultarNfseXml($this->remontaXML($xml), $this->nota, $this->criarXMLRetorno);

            return $response;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    function remontaXML($xml) {

        $this->configJson = json_decode($this->configJson);
        $xml = str_replace('[cnpj]', $this->configJson->cnpj, $xml);
        $xml = str_replace('[inscricaoMunicipal]', $this->configJson->im, $xml);
        $xml = str_replace('[numeroNfse]', $this->nota, $xml);

        return $xml;
    }
}
