<?php

namespace NFePHP\NFSeEGoverne;

/**
 * Class for comunications with NFSe webserver in Nacional Standard
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
use NFePHP\NFSeEGoverne\Common\Tools as BaseTools;
use NFePHP\Common\Certificate;
use NFePHP\Common\Validator;

class Tools extends BaseTools {

    const ERRO_EMISSAO = 1;
    const SERVICO_NAO_CONCLUIDO = 2;

    protected $xsdpath;

    public function __construct($config, Certificate $cert) {
        parent::__construct($config, $cert);
    }

    /**
     * Solicita o cancelamento de NFSe (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=CancelarNfse
     * @param string $id
     * @param integer $numero
     * @param integer $codigo
     * @return string
     */
    public function cancelarNfse($id, $numero, $codigo = self::ERRO_EMISSAO) {

        $operation = 'CancelarNfse';
        $pedido = $content = '';
        $pedido .= "<e:Pedido>";
        $pedido .= "<e:InfPedidoCancelamento>";
        $pedido .= "<e:IdentificacaoNfse>";
        $pedido .= "<e:Numero>{$numero}</e:Numero>";
        $pedido .= "<e:Cnpj>{$this->config->cnpj}</e:Cnpj>";
        $pedido .= "<e:InscricaoMunicipal>{$this->config->im}</e:InscricaoMunicipal>";
        $pedido .= "<e:CodigoMunicipio>{$this->config->cmun}</e:CodigoMunicipio>";
        $pedido .= "</e:IdentificacaoNfse>";
        $pedido .= "<e:CodigoCancelamento>{$codigo}</e:CodigoCancelamento>";
        $pedido .= "</e:InfPedidoCancelamento>";
        $pedido .= "</e:Pedido>";
        $content .= "<e:CancelarNfseEnvio>";
        $content .= $pedido;
        $content .= "</e:CancelarNfseEnvio>";

        return $this->send($content, $operation);
    }

    /**
     * Consulta Lote RPS (SINCRONO) após envio com recepcionarLoteRps() (ASSINCRONO)
     * complemento do processo de envio assincono.
     * Que deve ser usado quando temos mais de um RPS sendo enviado
     * por vez.
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarLoteRps
     * @param string $protocolo
     * @return string
     */
    public function consultarLoteRps($protocolo) {

        $operation = 'ConsultarLoteRps';
        $content = '';
        $content .= "<e:ConsultarLoteRpsEnvio>";
        $content .= $this->prestador;
        $content .= "<e:Protocolo>{$protocolo}</Protocolo>";
        $content .= "</e:ConsultarLoteRpsEnvio>";

        return $this->send($content, $operation);
    }

    /**
     * Consulta NFSe emitidas em um periodo e por tomador (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfse
     * @param string $dini
     * @param string $dfim
     * @param string $tomadorCnpj
     * @param string $tomadorCpf
     * @param string $tomadorIM
     * @return string
     */
    public function consultarNfse($dini, $dfim, $tomadorCnpj = null, $tomadorCpf = null, $tomadorIM = null, $numeroNFSe = null, $intermediario = null) {

        $operation = 'ConsultarNfse';
        $content = '';
        $content .= "<e:ConsultarNfse>";
        $content .= "<e:ConsultarNfseEnvio>";
        $content .= $this->prestador;
        $content .= "<e:NumeroNfse>{$numeroNFSe}</e:NumeroNfse>";
        $content .= "<e:PeriodoEmissao>";
        $content .= "<e:DataInicial>{$dini}</e:DataInicial>";
        $content .= "<e:DataFinal>{$dfim}</e:DataFinal>";
        $content .= "</e:PeriodoEmissao>";

        if ($tomadorCnpj !== null || $tomadorCpf !== null) {
            if (isset($tomadorCnpj)) {
                $tomadorDocumento = "<e:Cnpj>{$tomadorCnpj}</e:Cnpj>";
            } else {
                $tomadorDocumento = "<e:Cpf>{$tomadorCpf}</e:Cpf>";
            }

            $tomadorInscMun = '';
            if (isset($tomadorIM)) {
                $tomadorInscMun = "<e:InscricaoMunicipal>{$tomadorIM}</e:InscricaoMunicipal>";
            }

            $content .= "<e:Tomador>";
            $content .= "<e:CpfCnpj>";
            $content .= $tomadorDocumento;
            $content .= "</e:CpfCnpj>";
            $content .= $tomadorInscMun;
            $content .= "</e:Tomador>";
        }

        $content .= $intermediario;
        $content .= "</e:ConsultarNfseEnvio>";
        $content .= "</e:ConsultarNfse>";

        return $this->send($content, $operation);
    }

    /**
     * Consulta NFSe emitidas em um periodo e por tomador (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfse
     * @param string $tomadorCnpj
     * @param string $tomadorCpf
     * @param string $tomadorIM
     * @return string
     */
    public function consultarNfseBasico($numeroNFSe = null) {

        $operation = 'ConsultarNfse';
        $content = "<e:ConsultarNfse>
                        <e:ConsultarNfseEnvio>
                            <e:Prestador>
                                <e:Cnpj>" . $this->config->cnpj . "</e:Cnpj>
                                <e:InscricaoMunicipal>" . $this->config->im . "</e:InscricaoMunicipal>
                            </e:Prestador>
                            <e:NumeroNfse>{$numeroNFSe}</e:NumeroNfse>
                        </e:ConsultarNfseEnvio>
                    </e:ConsultarNfse>";

        return $this->send($content, $operation, 1);
    }

    /**
     * 
     * @param type $xml
     * @param type $cobranca
     * @param type $criarXMLRetorno
     * @return type
     */
    public function consultarNfseXml($xml, $cobranca = "", $criarXMLRetorno = false) {

        $operation = 'ConsultarNfse';

        return $this->send($xml, $operation, 1, $cobranca, $criarXMLRetorno);
    }

    /**
     * 
     * @param type $xml
     * @param type $cobranca
     * @param type $criarXMLRetorno
     * @param type $fake
     * @return type
     */
    public function cancelarLoteRpsXml($xml, $cobranca = "", $criarXMLRetorno = false, $fake = false) {

        $operation = 'CancelarLoteRps';

        if ($fake) {
            return $this->sendFake($xml, $operation, 1, $criarXMLRetorno, $cobranca, 4);
        }
        return $this->send($xml, $operation, 1, $cobranca, $criarXMLRetorno);
    }

    /**
     * 
     * @param type $xml
     * @param type $cobranca
     * @param type $criarXMLRetorno
     * @param type $fake
     * @param type $retornoErro
     * @return type
     */
    public function consultarLoteRpsXml($xml, $cobranca = "", $criarXMLRetorno = false, $fake = false, $retornoErro = false) {

        $operation = 'ConsultarLoteRps';

        if ($fake) {
            return $this->sendFake($xml, $operation, 1, $criarXMLRetorno, $cobranca, 5, $retornoErro);
        }
        return $this->send($xml, $operation, 1, $cobranca, $criarXMLRetorno, $retornoErro);
    }

    /**
     * @param string $xml
     * @param bool $criarXMLRetorno
     * @param bool $fake
     * @param bool $retornoErro
     * @return type
     */
    public function recepcionarLoteRpsXML($xml, $cobranca = "", $criarXMLRetorno = false, $fake = false, $retornoErro = false) {

        $operation = 'RecepcionarLoteRps';

        if ($fake) {
            return $this->sendFake($xml, $operation, 1, $criarXMLRetorno, $cobranca, 1, $retornoErro);
        }
        return $this->send($xml, $operation, 1, $cobranca, $criarXMLRetorno, $retornoErro);
    }

    /**
     * Consulta NFSe por RPS (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ConsultarNfsePorRps
     * @param integer $numero
     * @param string $serie
     * @param integer $tipo
     * @return string
     */
    public function consultarNfsePorRps($numero, $serie, $tipo) {

        $content = '';
        $operation = "ConsultarNfsePorRps";
        $content .= "<e:ConsultarNfseRpsEnvio>";
        $content .= "<e:IdentificacaoRps>";
        $content .= "<e:Numero>{$numero}</e:Numero>";
        $content .= "<e:Serie>{$serie}</e:Serie>";
        $content .= "<e:Tipo>{$tipo}</e:Tipo>";
        $content .= "</e:IdentificacaoRps>";
        $content .= $this->prestador;
        $content .= "</e:ConsultarNfseRpsEnvio>";

        return $this->send($content, $operation);
    }

    /**
     * Envia LOTE de RPS para emissão de NFSe (ASSINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=RecepcionarLoteRps
     * @param array $arps Array contendo de 1 a 50 RPS::class
     * @param string $lote Número do lote de envio
     * @return string
     * @throws \Exception
     */
    public function recepcionarLoteRps($arps, $lote) {

        $content = $listaRpsContent = '';
        $operation = 'RecepcionarLoteRps';
        $countRps = count($arps);
        if ($countRps > 50) {
            throw new \Exception('O limite é de 50 RPS por lote enviado.');
        }

        $content .= "<e:EnviarLoteRpsEnvio>";
        $content .= "<e:LoteRps>";
        $content .= "<e:NumeroLote>{$lote}</e:NumeroLote>";
        $content .= "<e:Cnpj>{$this->config->cnpj}</e:Cnpj>";
        $content .= "<e:InscricaoMunicipal>{$this->config->im}</e:InscricaoMunicipal>";
        $content .= "<e:QuantidadeRps>{$countRps}</e:QuantidadeRps>";
        $content .= "<e:ListaRps>";
        $content .= "</e:ListaRps>";
        $content .= "</e:LoteRps>";
        $content .= "</e:EnviarLoteRpsEnvio>";

        try {
            return $this->send($content, $operation);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Buscar Usuario (SINCRONO)
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=BuscarUsuario
     * @param string $cnpj
     * @param string $imu
     * @return string
     */
    public function buscarUsuario($cnpj, $imu) {

        $content = '';
        $operation = 'BuscarUsuario';
        $content .= "<e:BuscarUsuario>";
        $content .= "<e:imu>{$imu}</e:imu>";
        $content .= "<e:cnpj>{$cnpj}</e:cnpj>";
        $content .= "</e:BuscarUsuario>";

        return $this->send($content, $operation);
    }

    /**
     * Recepcionar Xml (SINCRONO)
     * Parâmentro (metodo) nome do metodo WS que será chamado.
     * Os valores podem ser : (RecepcionarLoteRps, ConsultarSituacaoLoteRps, ConsultarNfsePorRps,
     *                         ConsultarNfse, ConsultarLoteRps e CancelarNfse)
     * e o Parâmetro (xml) deve ser a mensagem xml a ser enviada.
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=RecepcionarXml
     * @param string $metodo
     * @param string $xml
     * @return string
     */
    public function recepcionarXml($metodo, $xml) {

        return $this->send($xml, $metodo);
    }

    /**
     * Validar Xml (SINCRONO)
     * Realiza a validação básica de um xml de acordo com o schema xsd
     * https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?op=ValidarXml
     * @param string $xml
     * @return string
     */
    public function validarXml($xml) {

        $content = '';
        $operation = 'ValidarXml';
        $content .= "<ValidarXml>";
        $content .= "<xml>$xml</xml>";
        $content .= "</ValidarXml>";

        return $this->send($content, $operation);
    }
}
