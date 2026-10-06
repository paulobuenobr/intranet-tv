<?php

//header("Content-Type: text/plain");
//header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include dirname(__DIR__, 1) . '/vendor/autoload.php';

use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;
use Symfony\Component\Config\Definition\Exception\Exception;
use Spatie\Async\Pool;

class ConsultarLoteRps {

    var $configJson;
    var $cert;
    var $criarXMLRetorno;
    var $bancodedados;
    var $protocolo;
    var $fake;
    var $utf8 = true;

    /**
     * 
     * @param type $bd
     * @param type $configJson
     * @param type $cert
     * @param type $criarXMLRetorno
     * @param type $fake
     */
    function __construct($bd, $configJson, $cert, $criarXMLRetorno = false, $fake = false) {
        $this->configJson = $configJson;
        $this->cert = $cert;
        $this->criarXMLRetorno = $criarXMLRetorno;
        $this->bancodedados = $bd;
        $this->fake = $fake;
    }

    function consultarLoteRpsPorProtocolo() {

        $notas = $this->buscaNotasComProtocolos();
        $pool = Pool::create();

        foreach ($notas as $nota) {

            $pool->add(function () use ($nota) {

                $retorno['idNota'] = $nota['id'];
                if ($nota) {
                    try {
                        $this->protocolo = $nota['protocolo'];
                        $retorno['dados'] = $dadosNota = $this->retornaNota($nota['id']);

                        if(is_array($dadosNota->retorno) || is_object($dadosNota->retorno)) {

                            return $retorno;
                        } else {
                            if ($dadosNota->retorno === false || $dadosNota['retorno'] === false) {
                                $msg = $dadosNota->erro . "<:>" . $retorno['idNota'];
                                throw new Exception($msg, 3);
                            }

                            return $retorno;
                        }

                        return $retorno;
                    } catch (\Exception $e) {
                        if ($e->getCode() == 3) {
                            throw new Exception($e->getMessage(), 3);
                        } else {
                            throw new Exception($retorno['idNota'], 2);
                        }
                    }
                } else {
                    throw new Exception($retorno['idNota'], 2);
                }
            })->then(function ($dados) {

                $nota = (object) $dados;
                if ($nota->dados) {
                    if (!empty($nota->dados->retorno->ConsultarLoteRpsResult->ListaNfse)) {
                        $infoNota = $nota->dados->retorno->ConsultarLoteRpsResult->ListaNfse->CompNfse->tcCompNfse->Nfse->InfNfse;
                        $this->atualizaDadosNota($nota->idNota, $infoNota);
                    } else {
                        if (!empty($msg = $nota->dados->retorno->ConsultarLoteRpsResult->ListaMensagemRetorno->MensagemRetorno)) {
                            $this->atualizaNotaParaErro($nota->idNota);
                            $this->insereLogs($nota->idNota, $msg);
                        }
                    }
                } else {
                    $this->atualizaDadosNota($nota->idNota, null, true);
                }
            })->catch(function (Exception $e) {
                if ($e->getCode() == 3) {
                    $ar = explode("<:>", $e->getMessage());
                    $msg = $ar[0];
                    $idNota = $ar[1];
                    $this->insertTabLog($idNota, $msg);
                    $this->atualizaNotaParaErro($idNota);
                } else {
                    $idNota = $e->getMessage();
                    $this->atualizaDadosNota($idNota, null, true);
                }
            });
        }
        $pool->wait();
    }

    function retornaNota($notaId) {

        $soap = new SoapCurl();
        $soap->disableCertValidation(true);
        $tools = new Tools($this->configJson, $this->cert);
        $tools->loadSoapClass($soap);
        $caminhoXML = __DIR__ . '\xml\ConsultarLoteRps.xml';
        $xml = file_get_contents($caminhoXML);
        $novoXML = utf8_encode($this->remontaNotaXML($xml, $this->configJson));

        return $tools->consultarLoteRpsXml($novoXML, "_" . $notaId, $this->criarXMLRetorno, $this->fake, true);
    }

    function remontaNotaXML($xml, $configJson) {

        $this->configJson = $configJson;
        if (empty($configJson->cmun)) {
            $this->configJson = json_decode($configJson);
        }
        $xml = str_replace('[cnpj]', $this->configJson->cnpj, $xml);
        $xml = str_replace('[inscricaoMunicipal]', $this->configJson->im, $xml);
        $xml = str_replace('[protocolo]', $this->protocolo, $xml);

        return $xml;
    }

    /**
     * @param type $id
     * @param type $dados
     * @param type $erro
     * @param type $protocolo
     */
    function atualizaDadosNota($id = 0, $dados = null, $erro = false) {

        if ($id > 0) {
            if ($erro) {
                $q = "UPDATE tab_notas SET status_id =3 WHERE id = $id";
            } else {
                $q = "UPDATE tab_notas SET 
                      numero = $dados->Numero,
                      dataEmissao = '{$dados->DataEmissao}',
                      dataEmissaoRps = '{$dados->DataEmissao}',
                      codigoVerificacao = '{$dados->CodigoVerificacao}',
                      nfseSubstituida = '{$dados->NfseSubstituida}',
                      identificacaoRps_numero = $id,
                      servico_valores_valorPis = '{$dados->Servico->Valores->ValorPis}',
                      servico_valores_valorCofins = '{$dados->Servico->Valores->ValorCofins}',
                      servico_valores_valorIr = '{$dados->Servico->Valores->ValorIr}',
                      servico_valores_valorCsll = '{$dados->Servico->Valores->ValorCsll}',
                      servico_valores_issRetido = '{$dados->Servico->Valores->IssRetido}',
                      servico_valores_valorInss = '{$dados->Servico->Valores->ValorInss}',
                      servico_valores_valorIssRetido = '{$dados->Servico->Valores->ValorIssRetido}',
                      servico_valores_outrasRetencoes = '{$dados->Servico->Valores->OutrasRetencoes}',
                      servico_valores_baseCalculo = '{$dados->Servico->Valores->BaseCalculo}',
                      servico_valores_valorLiquidoNfse = '{$dados->Servico->Valores->ValorLiquidoNfse}',
                      servico_valores_descontoIncondicionado = '{$dados->Servico->Valores->DescontoIncondicionado}',
                      servico_valores_descontoCondicionado = '{$dados->Servico->Valores->DescontoCondicionado}',
                      servico_valores_valorDeducoes = '{$dados->Servico->Valores->ValorDeducoes}',
                      servico_valores_numeroDeducao = '{$dados->Servico->Valores->NumeroDeducao}',
                      valorCredito = '{$dados->ValorCredito}',
                      servico_codigoTributacaoMunicipio = '{$dados->Servico->CodigoTributacaoMunicipio}',
                      servico_codigoMunicipio = '{$dados->Servico->CodigoMunicipio}',
                      nota_gerada = 1, status_id = 4 WHERE id = $id";
            }
            $this->bancodedados->query($q);
        }
    }

    function buscaNotasComProtocolos() {

        $q = "SELECT * FROM tab_notas WHERE nota_gerada IN(0,1) AND numero IS NULL AND protocolo IS NOT NULL";
        $query = $this->bancodedados->query($q);

        return $this->bancodedados->resultadoArrayTotal($query);
    }
    
    /**
     * 
     * @param type $nota
     * @param type $atualizarNota
     */
    function insereLogs($notaId, $codigosErros) {

        foreach ($codigosErros as $cod) {
            $codigo = (object) $cod;
            $this->insertTabLog($notaId, $codigo, false);
        }
    }

    /**
     * 
     * @param type $notaId
     * @param type $codigo
     */
    function insertTabLog($notaId, $codigo, $retornoDeMsg = true) {

        if ($retornoDeMsg) {
            $codText = explode("-", $codigo);
            $retorno = $codigo;
            $cod = trim($codText[0]);
            $msg = trim($codText[1]);
            if (!empty(trim($codText[2]))) {
                $msg = trim($codText[1]) . '-' . trim($codText[2]);
            }
            if (!empty(trim($codText[3]))) {
                $msg = trim($codText[1]) . '-' . trim($codText[2]) . '-' . trim($codText[3]);
            }
        } else {
            $cod = $codigo->Codigo;
            $msg = $codigo->Mensagem;
            $retorno = $codigo->Correcao;
            if ($this->utf8) {
                $cod = $codigo->Codigo;
                $msg = utf8_decode($codigo->Mensagem);
                $retorno = utf8_decode($codigo->Correcao);
            }
        }

        $date = date('Y-m-d H:i:s');
        $q = "INSERT INTO tab_notas_log(nota,retorno,cod, msg, data) VALUES ('$notaId', '$retorno', '$cod', '$msg', '$date')";
        $this->bancodedados->query($q);
    }

    /**
     * 
     * @param type $id
     */
    function atualizaNotaParaErro($id) {

        $q = "UPDATE tab_notas SET nota_gerada = 0, status_id = '5' WHERE id = '{$id}'";
        $this->bancodedados->query($q);
    }
}
