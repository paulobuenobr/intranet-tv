<?php

namespace NFePHP\NFSeEGoverne\Common;

/**
 * Auxiar Tools Class for comunications with NFSe webserver in Nacional Standard
 *
 * @category  NFePHP
 * @package   NFePHP\NFSeEGoverne
 * @copyright NFePHP Copyright (c) 2008-2019
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse-egoverne for the canonical source repository
 */
use NFePHP\Common\Certificate;
use NFePHP\NFSeEGoverne\RpsInterface;
use NFePHP\Common\DOMImproved as Dom;
use NFePHP\NFSeEGoverne\Common\Signer;
use NFePHP\NFSeEGoverne\Common\Soap\SoapInterface;
use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;

class Tools {

    public $lastRequest;
    protected $config;
    protected $prestador;
    protected $certificate;
    protected $wsobj;
    protected $soap;
    protected $environment;
    protected $xmlRequest;
    protected $urls = [
        '4106902' => [
            'municipio' => 'Curitiba',
            'uf' => 'PR',
            'homologacao' => 'https://srv2-isscuritiba.curitiba.pr.gov.br/nfsews/nfsews.asmx',
            'producao' => 'https://srv2-isscuritiba.curitiba.pr.gov.br/nfsews/nfsews.asmx',
            'version' => '2.01',
            'msgns' => 'http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd',
            'soapns' => 'https://www.e-governeapps2.com.br'
        ]
    ];

    /**
     * Constructor
     * @param string $config
     * @param Certificate $cert
     */
    public function __construct($config, Certificate $cert) {

        $this->config = $config;
        if (empty($config->cmun)) {
            $this->config = json_decode($config);
        }
        $this->certificate = $cert;
        $this->buildPrestadorTag();
        $wsobj = $this->urls;
        $this->wsobj = json_decode(json_encode($this->urls[$this->config->cmun]));
        $this->environment = 'homologacao';
        if ($this->config->tpamb == 1) {
            $this->environment = 'producao';
        }
    }

    /**
     * SOAP communication dependency injection
     * @param SoapInterface $soap
     */
    public function loadSoapClass(SoapInterface $soap) {

        $this->soap = $soap;
    }

    /**
     * Build tag Prestador
     */
    protected function buildPrestadorTag() {

        $this->prestador = "<e:Prestador>
                                <e:Cnpj>" . $this->config->cnpj . "</e:Cnpj>
                                <e:InscricaoMunicipal>" . $this->config->im . "</e:InscricaoMunicipal>
                            </e:Prestador>";
    }

    /**
     * Sign XML passing in content
     * @param string $content
     * @param string $tagname
     * @param string $mark
     * @return string XML signed
     */
    public function sign($content, $tagname, $mark) {

        $xml = Signer::sign(
                        $this->certificate,
                        $content,
                        $tagname,
                        $mark
        );

        $dom = new Dom('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        return $dom->saveXML($dom->documentElement);
    }

    /**
     * Send message to webservice
     * @param string $message
     * @param string $operation
     * @param string $tipoDeRetorno 0 - soap , 1 - array,  2 - json 
     * @param string $cobranca
     * @param bool $criarXMLRetorno 
     * @param bool $retornoErro
     * @return string XML response from webservice
     */
    public function send($message, $operation, $tipoDeRetorno = 1, $cobranca = "", $criarXMLRetorno = false, $retornoErro = false) {

        $param = $this->parametrosWsdlSend($message, $operation);
        if (empty($this->soap)) {
            $this->soap = new SoapCurl($this->certificate);
        }

        $response = (string) $this->soap->send($operation, $param['url'], $param['action'], $param['request'], $param['parameters'], $this->certificate);
        if ($criarXMLRetorno) {
            $this->criarXMLDeRetorno($response, "retorno_" . $operation, $cobranca, "log/retorno");
            $this->criarXMLDeRetorno($param['request'], $operation, $cobranca, "log/envio");
        }

        $retorno = $this->tipoDeRetorno($response, $tipoDeRetorno, false);
        if ($retornoErro) {
            $ret['erro'] = $response;
            $ret['retorno'] = $retorno;

            $retorno = (object) $ret;
        }

        return $retorno;
    }

    /**
     * Send message to webservice
     * @param string $message
     * @param string $operation
     * @param string $tipoDeRetorno 0 - soap , 1 - array,  2 - json 
     * @param bool $criarXMLRetorno 
     * @param int $tipo 1 - retorno envio lote , 
     *                  2 - retorno consulta lote, 
     *                  3 - retorno consulta nota,
     *                  4 - retorno cancela lote,
     *                  5 - retorno erro envio lote,
     *                  6 - retorno erros recepcionar Lote,
     *                  7 - retorno varios erros recepcionar Lote,
     * @return string XML response from webservice
     */
    public function sendFake($message, $operation, $tipoDeRetorno = 1, $criarXMLRetorno = false, $cobranca = "", $tipo = 1, $retornoErro = false) {

        $param = $this->parametrosWsdlSend($message, $operation);

        switch ($tipo) {
            case 1:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_envio_lote.xml';
                break;
            case 2:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_consulta_lote.xml';
                break;
            case 3:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno.xml';
                break;
            case 4:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_cancela_lote.xml';
                break;
            case 5:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_erro_envio_lote.xml';
                break;
            case 6:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_erro_recepcionarLote.xml';
                break;
            case 7:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_erro_recepcionarLoteMuitos.xml';
                break;
            default:
                $caminhoXML = dirname(__DIR__, 2) . '\xml_retorno_fake\ctb\retorno_envio_lote.xml';
                break;
        }
        $xml = file_get_contents($caminhoXML);

        if ($criarXMLRetorno) {
            $this->criarXMLDeRetorno($param['request'], $operation, $cobranca);
        }

        $retorno = $this->tipoDeRetorno($xml, $tipoDeRetorno, false);
        if ($retornoErro) {
            $ret['erro'] = true;
            $ret['retorno'] = $retorno;

            return $ret;
        }

        return $retorno;
    }

    /**
     * Send message to webservice
     * @param string $message
     * @param string $operation
     * @param string $tipoDeRetorno 0 - soap , 1 - array,  2 - json 
     * @param bool $criarXMLRetorno  
     * @return string XML response from webservice
     */
    public function wsdl($message, $operation, $tipoDeRetorno = 1, $criarXMLRetorno = false) {

        $param = $this->parametrosWsdlSend($message, $operation);
        if (empty($this->soap)) {
            $this->soap = new SoapCurl($this->certificate);
        }
        $response = (string) $this->soap->wsdl($param['url'], $param['request'], $param['parameters'], $this->certificate);

        if ($criarXMLRetorno) {
            $this->criarXMLDeRetorno($response, 0);
        }

        return $this->tipoDeRetorno($response, $tipoDeRetorno);
    }

    /**
     * 
     * @param type $conteudo
     * @param type $nomeArquivo
     * @param type $cobranca
     * @param type $local
     */
    function criarXMLDeRetorno($conteudo, $nomeArquivo, $cobranca = "", $local = "xml_retorno_fake") {

        $dataAgora = new \DateTime();
        $dataHorario = $dataAgora->format('Y-m-d-H-i-s-u');
        $file = dirname(__DIR__, 2) . "/{$local}/{$nomeArquivo}_{$dataHorario}_{$cobranca}.xml";
        $fp = fopen($file, 'w+b');
        fwrite($fp, $conteudo);
        fclose($fp);
    }

    public function parametrosWsdlSend($message, $operation) {

        $action = "{$this->wsobj->soapns}/$operation";
        $ret = [];
        $ret['url'] = $this->wsobj->homologacao;
        if ($this->environment == 'producao') {
            $ret['url'] = $this->wsobj->producao;
        }
        $request = $this->createSoapRequestNew($message, $operation);
        $this->lastRequest = $request;
        $this->xmlRequest = $message;

        $msgSize = strlen($request);
        $parameters = ["Content-Type: text/xml;charset=UTF-8", "SOAPAction: \"$action\"", "Content-length: $msgSize"];

        $ret['request'] = $request;
        $ret['parameters'] = $parameters;
        $ret['action'] = $action;

        return $ret;
    }

    /**
     * 
     * @param string $retorno
     * @param type $tipoDeRetorno 0 - soap , 1 - array,  2 - json 
     * @return string, array, json
     */
    public function tipoDeRetorno($retorno, $tipoDeRetorno, $utf8Decode = false) {

        switch ($tipoDeRetorno) {

            case 0:
                return $retorno;

            case 1:
                return $this->soapToArray($retorno, $utf8Decode);

            case 2:
                return $this->soapToJson($retorno, $utf8Decode);

            default:
                return $retorno;
        }
    }

    /**
     * Converte response para json
     * @param string $response xml soap
     * @return json
     */
    function soapToJson($response, $utf8Decode) {

        return json_encode($this->soapToArray($response, $utf8Decode));
    }

    /**
     * Converte response para array
     * @param string $response xml soap
     * @return array
     */
    function soapToArray($response, $utf8Decode = false) {

        $ar = explode('<soap:Body>', $response);
        if (count($ar) > 1) {
            list($trash, $response) = $ar;
            if ($response) {
                list($response, $trash) = explode('</soap:Body>', $response);
                unset($trash);
                $response = str_replace('xmlns="url"', '', $response);
                $response = str_replace('xsi:nil="true"', '', $response);
                if ($utf8Decode) {
                    return simplexml_load_string(utf8_decode($response));
                }
                return simplexml_load_string($response);
            }
            return false;
        }

        return false;
    }

    /**
     * Build SOAP request
     * @param string $message
     * @param string $operation
     * @return string XML SOAP request
     */
    protected function createSoapRequest($message, $operation) {

        $env = "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">
                    <soap:Body>
                        <{$operation} xmlns=\"{$this->wsobj->soapns}/\">
                            $message
                        </{$operation}>
                    </soap:Body>
                </soap:Envelope>";

        $dom = new Dom('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($env);

        return $dom->saveXML($dom->documentElement);
    }

    /**
     * Build SOAP request
     * @param string $message
     * @param string $operation
     * @return string XML SOAP request
     */
    protected function createSoapRequestNew($message) {

        $url = $this->wsobj->soapns . '/';

        return "<soap:Envelope xmlns:e=\"{$url}\" xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\">
                    <soap:Header/>
                    <soap:Body>
                        {$message}
                    </soap:Body>
                </soap:Envelope>";
    }

    /**
     * Create tag Prestador and insert into RPS xml
     * @param RpsInterface $rps
     * @return string RPS XML (not signed)
     */
    protected function putPrestadorInRps1(RpsInterface $rps) {

        $dom = new Dom('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($rps->render());
        $referenceNode = $dom->getElementsByTagName('Servico')->item(0);
        $node = $dom->createElement('Prestador');
        $dom->addChild($node, "Cnpj", $this->config->cnpj, true);
        $dom->addChild($node, "InscricaoMunicipal", $this->config->im, true);
        $dom->insertAfter($node, $referenceNode);

        return $dom->saveXML($dom->documentElement);
    }

    public function getXmlRequest() {

        return $this->xmlRequest;
    }
}
