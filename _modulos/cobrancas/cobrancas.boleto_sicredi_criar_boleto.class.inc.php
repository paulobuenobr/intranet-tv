<?php

require_once __DIR__ . '/vendor/autoload.php';

use SicrediAPI\Domain\Boleto\BeneficiarioFinal;
use SicrediAPI\Domain\Boleto\Pagador;
use SicrediAPI\Domain\Boleto\Desconto;
use SicrediAPI\Domain\Boleto\DescontoConfiguracao;
use SicrediAPI\Domain\Boleto\JurosConfiguracao;

class cobrancas_boleto_sicredi_criar_boleto {

    /**
     * @var object - Objeto da classe bancodedados
     */
    var $bancodedados;
    /**
     * @var bool - Flag de homologação
     */
    var $homologacao;
    /**
     * @var object - Objeto da classe config
     */
    var $config;
    /**
     * @var string - ID da cobrança
     */
    var $cobranca_id;
    /**
     * @var string - ID do formato
     */
    var $formato_id;
    /**
     * @var string - NSA
     */
    var $nsa;
    /**
     * @var string - Conteúdo
     */
    var $conteudo;
    /**
     * @var string - Token salvo
     */
    var $tokenSalvo;
    /**
     * @var bool - Flag de uso do token salvo
     */
    var $usarTokenSalvo;
    /**
     * @var object - Objeto da classe util
     */
    var $util;

    /**
     * @var object - Objeto da classe client
     */
    var $client;

    /**
     * Construtor da classe
     * @param object $bd - Objeto da classe bancodedados
     */
    function __construct($bd) {

        $this->bancodedados = $bd;
        $this->util = controle::instanciaObjeto("cobrancas.boleto_sicredi_util", $this->bancodedados);
    }

    /**
     * 
     * @param string $formato_id
     * @param string $cobranca_id
     * @param object $config
     * @param string $tokenSalvo
     * @param bool $usarTokenSalvo
     * @param bool $homologacao
     * @return bool
     */
    function criar($formato_id, $cobranca_id, $config, $tokenSalvo = null, $usarTokenSalvo = true, $homologacao = false) {

        $this->homologacao = $homologacao;
        $this->cobranca_id = $cobranca_id;
        $this->formato_id = $formato_id;
        $this->tokenSalvo = $tokenSalvo;
        $this->usarTokenSalvo = $usarTokenSalvo;
        $boletoClient = $this->sicrediAPIClient($config, $homologacao);
        if (!$boletoClient) {
            return false;
        }

        $dados = $this->util->carregaDadosDaCobranca($this->cobranca_id);

        $valor = empty($dados['parametros']["boleto"]["valor"]) ? $dados["valor"] : $dados['parametros']["boleto"]["valor"];
        $titularCpfCnpj = empty($dados['parametros']["titular"]["cpf_cnpj"]) ? $dados["cpf_cnpj"] : $dados['parametros']["titular"]["cpf_cnpj"];
        $titularNome = $dados['parametros']["titular"]["nome"] ? $dados['parametros']["titular"]["nome"] : $titularCpfCnpj;
        $nosso_numero = preg_replace("/[^0-9]/", "", $dados['parametros']['boleto']['nosso_numero']);
        $data_vencimento = $dados['parametros']['data_vencimento'] ? $dados['parametros']['data_vencimento'] : $dados['data_vencimento'];
        $data_emissao = $dados['parametros']['data_emissao'] ? $dados['parametros']['data_emissao'] : $dados['data_emissao'];

        if ($dados["desconto"] > 0) {
            $descontos = array(new Desconto($this->formataValor($dados["desconto"]), new DateTime($this->dateEmMysql($data_vencimento))));
            $desconto = new DescontoConfiguracao($descontos, 'VALOR');
        } else {
            $desconto = null;
        }

        $valorFormatado = $this->formataValor($valor);
        if ($valorFormatado >= 30) {
            $valorJuros = $this->calculaJuros($valorFormatado, 1 / 30);
            $juros = new JurosConfiguracao("VALOR", $valorJuros, 2);
        } elseif ($valorFormatado < 0.5) {
            $juros = new JurosConfiguracao(null, null, null);
        } else {
            $juros = new JurosConfiguracao(null, null, 2);
        }

        $titularCpfCnpj = preg_replace('/[^0-9]/', '', $titularCpfCnpj);
        $tipoPagador = strlen($titularCpfCnpj) > 11 ? "PESSOA_JURIDICA" : "PESSOA_FISICA";
        $boleto = new \SicrediAPI\Domain\Boleto\Boleto(
//                (new BeneficiarioFinal('EMPRESA CATARINENSE DE TEC. EM TELECOM.', '08.519.692/0001-90', 'PESSOA_JURIDICA')),
                null,
                (new Pagador(
                        substr($this->removeAcentuacao($titularNome), 0, 40), $titularCpfCnpj, $tipoPagador,
                        $dados["cliente_id"], substr($this->removeAcentuacao($dados["logradouro"]), 0, 40), $this->removeAcentuacao($dados["cidade"]),
                        $dados['estado'], $dados["cep"])),
                $valorFormatado,
                $dados['especie_doc'],
                $config->SICREDI_BENEFICIARY,
                'OUTROS',
                $dados['id'],
                new DateTime($this->dateEmMysql($data_vencimento)),
                new DateTime($this->dateEmMysql($data_emissao)),
                $nosso_numero,
                $desconto,
                $juros
        );

        try {
            $retorno = $boletoClient->criar($boleto);

            $erro = $retorno->getErro();
            
            if (!$erro) {

                if (empty($retorno->getInformacaoDePagamento()) || 
                        empty($retorno->getInformacaoDePagamento()->getQrCode()) || 
                        strlen($retorno->getInformacaoDePagamento()->getQrCode()) < 10) {
                   
		            return false;
                } else {
		            $dados['parametros']['boleto']['qrcode'] = $retorno->getInformacaoDePagamento()->getQrCode();
		            $this->atualizaCobranca($dados);
		
		            return true;
                }
	        } else {
                
	            $erro->coa_id = $this->cobranca_id;
	            $this->insereErroLogSicredi($erro);

                if ($erro->code == 422) {

	                try {
	                    $retorno = $boletoClient->query($nosso_numero);
	                    if (!empty($retorno->erro)) {
	                        $retorno->coa_id = $this->cobranca_id;
	                        $this->insereErroLogSicredi($retorno);
	                    } else {
	                        if (!empty($retorno->getInformacaoDePagamento()) && 
	                                !empty($retorno->getInformacaoDePagamento()->getQrCode()) && 
	                                strlen($retorno->getInformacaoDePagamento()->getQrCode()) > 10) {
	                            
	                            $dados['parametros']['boleto']['qrcode'] = $retorno->getInformacaoDePagamento()->getQrCode();
	                            $this->atualizaCobranca($dados);
	                            
	                            return true;
	                        }
	                    }
	                } catch (Exception $exc) {
	
	                    $erro = (object) null;
	                    $erro->error = "";
	                    $erro->code = "0";
	                    $erro->message = $exc->getMessage();
	                    $erro->coa_id = $dados['id'];
	                    $this->insereErroLogSicredi($erro);
	                }
                }
                return false;
            }

        } catch (Exception $ex) {

            $erro = (object) null;
            $erro->error = "";
            $erro->code = "0";
            $erro->message = $ex->getMessage();
            $erro->coa_id = $dados['id'];
            $this->insereErroLogSicredi($erro);
            $retorno = false;
            
        }

    }

    /**
     * 
     * @param object $config
     * @param bool $homologacao
     * @return object
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

    /**
     * 
     * @param string $str
     * @return string
     */
    function removeAcentuacao($str) {

        $limpa = preg_replace('/[^\p{L}\p{N}\s]/', '', $str);
        $texto = strtr(trim(trim($limpa, ","), "-"), 'àáâãäçèéêëìíîïñòóôõöùúûüıÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜİ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

        if (mb_detect_encoding($texto, 'ISO-8859-1', true)) {
            $texto = mb_convert_encoding($texto, "ISO-8859-1", "UTF-8");
        }
        return preg_replace('/[*.-\/,-\/*%$#@<>:;?!\'"|\\¨+=}\]\{\[]/', '', trim($texto));
    }

    /**
     * 
     * @param array $info
     */
    function atualizaCobranca($info) {

        $parametros = addslashes(serialize($info['parametros']));
        $query = "UPDATE tab_cobrancas SET coa_parametros = '{$parametros}', coa_aguardando_registro = 0 , coa_data_registro = NOW() WHERE coa_id = {$this->cobranca_id}";
        $this->bancodedados->query($query);

        $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
        $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);

        $cobrancas->buscaDadosItem($this->cobranca_id);
        $cobrancas_retornos->registraComentarioCobranca($this->cobranca_id, "<b>Ação:</b> Boleto registrado no banco");
        if ($cobrancas->dados_item["geracao_manual"]) {
            $cobrancas->enviaBoletoAutomatico();
        }
    }

    /**
     * 
     * @param object $er
     */
    function insereErroLogSicredi($er) {

        $msg = addslashes(utf8_decode($er->message));
        $query = "INSERT INTO tab_sicredi_log (timetamp, error, code, message, coa_id)
                    VALUES ( NOW() , '{$er->error}', '{$er->code}', '" . $msg . "', '{$er->coa_id}')";

        $this->bancodedados->query($query);
    }

    /**
     * 
     * @param string $dateSql
     * @return string
     */
    function dateEmMysql($dateSql) {

        $ano = substr($dateSql, 6);
        $mes = substr($dateSql, 3, -5);
        $dia = substr($dateSql, 0, -8);

        return $ano . "-" . $mes . "-" . $dia;
    }

    /**
     * 
     * @param string $valor
     * @return string
     */
    function formataValor($valor) {

        $val1 = str_replace(".", "", $valor);
        $val2 = str_replace(",", "", $val1);
        $retorno = $val2 / 100;

        return number_format($retorno, 2, ".", "");
    }

    /**
     * 
     * @param string $valor
     * @param string $porcentagem
     * @return string
     */
    function calculaJuros($valor, $porcentagem) {

        $val = $valor * ( $porcentagem / 100);

        return number_format($val, 2, ".", "");
    }
}
