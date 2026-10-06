<?php

//header("Content-Type: text/plain; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include dirname(__DIR__, 1) . '/vendor/autoload.php';

use NFePHP\NFSeEGoverne\Tools;
use NFePHP\NFSeEGoverne\Common\Soap\SoapCurl;
use Symfony\Component\Config\Definition\Exception\Exception;
use Spatie\Async\Pool;

class RecepcionarLoteRps {

    /**
     * @var string
     */
    var $configJson;

    /**
     * @var object
     */
    var $cert;

    /**
     * @var bool
     */
    var $criarXMLRetorno;

    /**
     * @var object
     */
    var $bancodedados;

    /**
     * @var object
     */
    var $dadosNota;

    /**
     * @var bool
     */
    var $fake;

    /**
     * 
     * @param object $bd
     * @param string $configJson
     * @param object $cert
     * @param bool $criarXMLRetorno
     * @param bool $fake
     * @return void
     * @throws Exception
     */
    function __construct($bd, $configJson, $cert, $criarXMLRetorno = false, $fake = false) {
        $this->configJson = $configJson;
        $this->cert = $cert;
        $this->criarXMLRetorno = $criarXMLRetorno;
        $this->bancodedados = $bd;
        $this->fake = $fake;
    }

    function recepcionandoLoteRps() {

        $itens = $this->buscaCobrancasGeradas();
        foreach ($itens as $item) {
            $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
            $this->dadosNota = null;
            $cobrancas->buscaDadosItem($item['coa_id']);
            $dados = $cobrancas->dados_item;
            $notas = $this->buscaItensParaNotaPelaCobranca($dados['id']);
            $this->executaGeracaoDeNotas($notas, $dados);
        }
    }

    function gerarNotaNaoGeradas() {

        $notas = $this->buscaNotasNaoGeradas('0', "2,5");
        $this->executaGeracaoDeNotas($notas, null, true);
    }

    /**
     * 
     * @param array $notas
     * @param array $dados
     * @param bool $notasNaoGeradas
     * @return void
     * @throws Exception
     */
    function executaGeracaoDeNotas($notas, $dados = null, $notasNaoGeradas = false) {
        $pool = Pool::create();
        if (!$notas) {
            return;
        }
        foreach ($notas as $nota) {
            $pool->add(function () use ($dados, $nota, $notasNaoGeradas) {
                if ($notasNaoGeradas) {
                    $atualiza = $this->atualizaAliquotaTipoServCnae($nota);
                    $this->montaDadosNotaNaoGerada($nota, $atualiza);
                    $retorno['idNota'] = $this->dadosNota->loteRps->numeroLote;
                } else {
                    $this->atualizaCobranca($dados['id']);
                    $this->montaDadosNota($dados, $nota);
                    $retorno['idNota'] = $this->insereDadosNota($dados['id']);
                }
                if ($nota) {
                    try {
                        $retorno['dados'] = $dadosNota = (object) $this->gerarNota();
                        if (empty($dadosNota->retorno)) {
                            throw new Exception("500 - Erro interno<:>" . $retorno['idNota'], 3);
                        } else {
                            if ($dadosNota->retorno == false) {
                                throw new Exception($dadosNota->erro . "<:>" . $retorno['idNota'], 3);
                            }
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
                    if (!empty($nota->dados->retorno->RecepcionarLoteRpsResult->ListaMensagemRetorno->MensagemRetorno)) {
                        $this->atualizaNotaParaErro($nota->idNota);
                        $this->insereLogs($nota->idNota, $nota->dados->retorno->RecepcionarLoteRpsResult->ListaMensagemRetorno->MensagemRetorno);
                    } else {
                        $this->atualizaDadosNota($nota->idNota, $nota->dados->retorno->RecepcionarLoteRpsResult->Protocolo);
                    }
                } else {
                    $this->atualizaDadosNota($nota->idNota);
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
                    $this->atualizaDadosNota($idNota);
                }
            });
        }
        $pool->wait();
    }

    /**
     * 
     * @param string $txt
     * @return string
     * @throws Exception
     * @return string
     */
    function ajusteCaracteresDB($txt) {

        return str_replace("'", "`", $txt);
    }

    /**
     * 
     * @param array $nota
     * @param array $atualizar
     * @return void
     * @throws Exception
     */
    function montaDadosNotaNaoGerada($nota, $atualizar) {

        $obj = (object) $nota;
        $atualiza = (object) $atualizar;

        $this->dadosNota = new stdClass();
        $this->dadosNota->loteRps = new stdClass();
        $this->dadosNota->titular = new stdClass();
        $this->dadosNota->servico = new stdClass();
        $this->dadosNota->tomador = new stdClass();
        $this->dadosNota->identificacaoRps = new stdClass();
        $this->dadosNota->id = $obj->id;
        $this->dadosNota->servico->issRetido = $obj->servico_valores_issRetido;
        $this->dadosNota->servico->valorIss = $obj->servico_valores_valorIss;
        $this->dadosNota->servico->valorServicos = $obj->servico_valores_valorServicos;
        $this->dadosNota->servico->discriminacao = $obj->servico_discriminacao;
        $this->dadosNota->loteRps->numeroLote = $obj->id;
        $this->dadosNota->identificacaoRps->numero = $obj->identificacaoRps_numero;
        $this->dadosNota->dataEmissao = $obj->dataEmissaoRps;
        $this->dadosNota->tomador->razaoSocial = $obj->tomserv_razaoSocial;
        $this->dadosNota->tomador->inscricaoMunicipal = $obj->tomserv_inscricaoMunicipal;
        $this->dadosNota->tomador->cnpj = $obj->tomserv_idetom_cpfCnpj_cnpj;
        $this->dadosNota->tomador->cpf = $obj->tomserv_idetom_cpfCnpj_cpf;
        $this->dadosNota->tomador->end_complemento = $obj->tomserv_end_complemento;
        $this->dadosNota->tomador->end_numero = $obj->tomserv_end_numero;
        $this->dadosNota->tomador->end_endereco = $obj->tomserv_end_endereco;
        $this->dadosNota->tomador->end_uf = $obj->tomserv_end_uf;
        $this->dadosNota->tomador->end_bairro = $obj->tomserv_end_bairro;
        $this->dadosNota->tomador->end_cep = $obj->tomserv_end_cep;
        $this->dadosNota->tomador->end_codigoMunicipio = $obj->tomserv_end_codigoMunicipio;
        $this->dadosNota->tomador->telefone = $this->apenasNumeros($obj->tomserv_contato_telefone);
        $this->dadosNota->tomador->email = $obj->tomserv_contato_email;

        $this->dadosNota->servico->aliquota = $atualiza->aliquota;
        $this->dadosNota->servico->itemListaServico = $atualiza->cod_ser;
        $this->dadosNota->servico->codigoCnae = $atualiza->cod_cnae;
    }

    function gerarNota() {

        $soap = new SoapCurl();
        $soap->disableCertValidation(true);
        $tools = new Tools($this->configJson, $this->cert);
        $tools->loadSoapClass($soap);
        $caminhoXML = __DIR__ . '\xml\RecepcionarLoteRps.xml';
        $xml = file_get_contents($caminhoXML);
        $novoXML = utf8_encode($this->remontaXML($xml, $this->configJson));

        return $tools->recepcionarLoteRpsXML($novoXML, "_" . $this->dadosNota->loteRps->numeroLote, $this->criarXMLRetorno, $this->fake, true);
    }

    /**
     * 
     * @param array $dados
     * @param array $nota
     * @return void
     * @throws Exception
     */
    function montaDadosNota($dados, $nota) {

        $obj = (object) $dados;
        $detalhes = (object) $nota;
        $this->dadosNota = new stdClass();
        $obj->parametros = new stdClass();
        $obj->parametros->endereco_cobranca = (object) $dados['parametros']['endereco_cobranca'];
        $obj->parametros->titular = (object) $dados['parametros']['titular'];
        $email = $this->buscaContatosEmails($obj->cliente_id);
        foreach ($email[0] as $key => $value) {
            $email = $value;
        }

        $this->dadosNota->tomador = new stdClass();
        $this->dadosNota->loteRps = new stdClass();
        $this->dadosNota->titular = new stdClass();
        $this->dadosNota->servico = new stdClass();
        $this->dadosNota->identificacaoRps = new stdClass();

        $this->dadosNota->afc = $this->dadosNota->plano = "";
        if (!empty($detalhes->dados->pla_id)) {
            $this->dadosNota->plano = $detalhes->dados->pla_id;
        }
        if (!empty($detalhes->dados->afc_id)) {
            $this->dadosNota->afc = $detalhes->dados->afc_id;
        }

        $dataAgora = new DateTime('now');
        $dataEmissao = $dataAgora->format('Y-m-d');
        $this->dadosNota->dataEmissao = $dataEmissao . "T00:00:00";

        $this->dadosNota->loteRps->numeroLote = $obj->id;
        $this->dadosNota->identificacaoRps->numero = $obj->id;
        $this->dadosNota->servico->valorServicos = $this->formataNumero($this->apenasNumeros($detalhes->cod_valor_final));
        $this->dadosNota->servico->valorIss = $this->formataNumero($this->calculaISS($detalhes->cod_valor_final));
        $this->dadosNota->servico->discriminacao = $detalhes->items;

        $this->dadosNota->servico->aliquota = $detalhes->dados->aliquota;
        $this->dadosNota->servico->issRetido = $detalhes->dados->issRetido;
        $this->dadosNota->servico->itemListaServico = $detalhes->dados->codigo_servico;
        $this->dadosNota->servico->codigoCnae = $detalhes->dados->cnae;
        $this->dadosNota->servico->nbs = $detalhes->dados->nbs;

        if ($this->ehCnpj($obj->cliente_cpf_cnpj)) {
            $this->dadosNota->tomador->cnpj = $this->apenasNumeros($obj->cliente_cpf_cnpj);
        } else {
            $this->dadosNota->tomador->cpf = $this->apenasNumeros($obj->cliente_cpf_cnpj);
        }
        $this->dadosNota->tomador->inscricaoMunicipal = $obj->cliente_inscricao_estadual;
        $this->dadosNota->tomador->razaoSocial = $obj->cliente_nome;
        $this->dadosNota->tomador->end_endereco = $obj->parametros->endereco_cobranca->logradouro;
        $this->dadosNota->tomador->end_numero = $obj->parametros->endereco_cobranca->numero;
        $this->dadosNota->tomador->end_complemento = $obj->parametros->endereco_cobranca->complemento;
        $this->dadosNota->tomador->end_bairro = $obj->parametros->endereco_cobranca->bairro;

        $dadosCidade = $this->buscaDadosCidade($obj->parametros->endereco_cobranca->cep);
        $this->dadosNota->tomador->end_codigoMunicipio = $dadosCidade[0]['cid_ibge'];

        $this->dadosNota->tomador->end_uf = $obj->parametros->endereco_cobranca->estado;
        $this->dadosNota->tomador->end_cep = $this->apenasNumeros($obj->parametros->endereco_cobranca->cep);

        $telefone = "";
        if (isset($obj->parametros->titular->tel_comercial)) {
            $telefone = $obj->parametros->titular->tel_comercial;
        } else if (isset($obj->parametros->titular->tel_residencial)) {
            $telefone = $obj->parametros->titular->tel_comercial;
        } else if (isset($obj->parametros->titular->tel_celular)) {
            $telefone = $obj->parametros->titular->tel_celular;
        }
        $telefone = explode(",", $telefone);
        $tel = preg_replace('/(.+?)\(Ra[^.]*$|$/', '$1', $telefone[0]);
        $this->dadosNota->tomador->telefone = $this->apenasNumeros($tel);
        $this->dadosNota->tomador->email = $email;
    }

    /**
     * 
     * @param string $cpfoucnpj
     * @return bool
     * @throws Exception
     */
    function ehCnpj($cpfoucnpj) {

        return (strlen($this->apenasNumeros($cpfoucnpj)) == 14);
    }

    /**
     * 
     * @param string $cpfoucnpj
     * @return bool
     * @throws Exception
     */
    function ehCpf($cpfoucnpj) {

        return (strlen($this->apenasNumeros($cpfoucnpj)) == 11);
    }

    /**
     * 
     * @param string $valorServicos
     * @return string
     * @throws Exception
     */
    function calculaISS($valorServicos) {

        return $valorServicos * 0.0342;
    }

    /**
     * 
     * @param string $valor
     * @return string
     * @throws Exception
     */
    function apenasNumeros($valor) {

        if (!$valor) {
            return 0;
        }
        return preg_replace('/[^0-9]/', '', $valor);
    }

    /**
     * 
     * @param string $valor
     * @return string
     * @throws Exception
     */
    function formataNumero($valor) {

        $numero = $valor / 100;

        return number_format($numero, 2, '.', '');
    }

    /**
     * 
     * @param string $valor
     * @param string $moeda
     * @return string
     * @throws Exception
     */
    function formataMoeda($valor, $moeda = "R$") {

        $numero = $valor / 100;

        return $moeda . number_format($numero, 2, ',', '.');
    }

    /**
     * 
     * @param string $xml
     * @param string $configJson
     * @return string
     * @throws Exception
     */
    function remontaXML($xml, $configJson) {

        $config = json_decode($configJson);

        $xml = str_replace('[loteRps_numeroLote]', $this->dadosNota->loteRps->numeroLote, $xml);
        $xml = str_replace('[loteRps_cnpj]', $config->cnpj, $xml);
        $xml = str_replace('[loteRps_inscricaoMunicipal]', $config->im, $xml);
        $xml = str_replace('[loteRps_quantidadeRps]', '1', $xml);
        $xml = str_replace('[rps_id]', "Rps_" . $this->dadosNota->identificacaoRps->numero . "UNICA", $xml);
        $xml = str_replace('[identificacaoRps_numero]', $this->dadosNota->identificacaoRps->numero, $xml);
        $xml = str_replace('[identificacaoRps_serie]', "UNICA", $xml);
        $xml = str_replace('[identificacaoRps_tipo]', '1', $xml);

        $xml = str_replace('[dataEmissao]', $this->dadosNota->dataEmissao, $xml);
        $xml = str_replace('[naturezaOperacao]', '1', $xml);
        $xml = str_replace('[regimeEspecialTributacao]', '1', $xml);
        $xml = str_replace('[optanteSimplesNacional]', '1', $xml);
        $xml = str_replace('[incentivadorCultural]', '2', $xml);
        $xml = str_replace('[status]', '1', $xml);

        $xml = str_replace('[servico_valorServicos]', $this->dadosNota->servico->valorServicos, $xml);
        $xml = str_replace('[servico_valorInss]', $this->dadosNota->servico->valorIss, $xml);
        $xml = str_replace('[servico_issRetido]', $this->dadosNota->servico->issRetido, $xml);
        $xml = str_replace('[servico_aliquota]', ($this->dadosNota->servico->aliquota / 100), $xml);
        $xml = str_replace('[servico_itemListaServico]', $this->dadosNota->servico->itemListaServico, $xml);
        $xml = str_replace('[servico_codigoCnae]', $this->dadosNota->servico->codigoCnae, $xml);

        $xml = str_replace('[servico_discriminacao]', $this->dadosNota->servico->discriminacao, $xml);

        $xml = str_replace('[prestador_cnpj]', $config->cnpj, $xml);
        $xml = str_replace('[prestador_inscricaoMunicipal]', $config->im, $xml);

        $cpf = $cnpj = "";
        if (isset($this->dadosNota->tomador->cnpj)) {
            $cnpj = "<e:Cnpj>{$this->dadosNota->tomador->cnpj}</e:Cnpj>";
        } else {
            $cpf = "<e:Cpf>{$this->dadosNota->tomador->cpf}</e:Cpf>";
        }
        $xml = str_replace('[tomador_cpf]', $cpf, $xml);
        $xml = str_replace('[tomador_cnpj]', $cnpj, $xml);
        $xml = str_replace('[tomador_inscricaoMunicipal]', $this->dadosNota->tomador->inscricaoMunicipal, $xml);
        if (!$this->dadosNota->tomador->inscricaoMunicipal) {
            $xml = str_replace('<e:InscricaoMunicipal></e:InscricaoMunicipal>', "", $xml);
        }
        $xml = str_replace('[tomador_razaoSocial]', $this->dadosNota->tomador->razaoSocial, $xml);
        $xml = str_replace('[tomador_end_endereco]', $this->dadosNota->tomador->end_endereco, $xml);
        $xml = str_replace('[tomador_end_numero]', $this->dadosNota->tomador->end_numero, $xml);
        $xml = str_replace('[tomador_end_complemento]', $this->dadosNota->tomador->end_complemento, $xml);
        $xml = str_replace('[tomador_end_bairro]', $this->dadosNota->tomador->end_bairro, $xml);
        $xml = str_replace('[tomador_end_codigoMunicipio]', $this->dadosNota->tomador->end_codigoMunicipio, $xml);
        $xml = str_replace('[tomador_end_uf]', $this->dadosNota->tomador->end_uf, $xml);
        $xml = str_replace('[tomador_end_cep]', $this->dadosNota->tomador->end_cep, $xml);
        $xml = str_replace('[tomador_telefone]', $this->apenasNumeros($this->apenasNumeros($this->dadosNota->tomador->telefone)), $xml);
        $xml = str_replace('[tomador_email]', $this->dadosNota->tomador->email, $xml);

        return $xml;
    }

    /**
     * Summary of atualizaCobranca
     * @param mixed $idCobranca
     * @return void
     */
    function atualizaCobranca($idCobranca) {

        $q = "UPDATE tab_cobrancas SET gerado_nota = 1 WHERE coa_id = $idCobranca";
        $this->bancodedados->query($q);
    }

    /**
     * @param int $id
     * @param string|null $protocolo
     * @return void
     * @throws Exception
     */
    function atualizaDadosNota($id = 0, $protocolo = null) {

        if ($id > 0) {
            if ($protocolo != null) {
                $q = "UPDATE tab_notas SET identificacaoRps_numero = $id, nota_gerada = 1, status_id = 3, protocolo = '$protocolo' WHERE id = $id";
            } else {
                $q = "UPDATE tab_notas SET identificacaoRps_numero = $id, nota_gerada = 0, status_id = 2 WHERE id = $id";
            }

            if ($protocolo == "E514") {
                $q = "DELETE FROM tab_notas_log WHERE nota = $id;";
                $this->bancodedados->query($q);

                $q = "UPDATE tab_notas SET id = (select max(id) from tab_notas) + 1, nota_gerada = 0, numero = NULL, status_id = 2 WHERE id = $id;";
            }

            $this->bancodedados->query($q);
        }
    }

    function buscaCobrancasGeradas() {

        $query = $this->bancodedados->query("SELECT * FROM tab_cobrancas WHERE gerado_nota = 0 AND coa_valor_total > 0 AND cot_id NOT IN(1,12)");

        return $this->bancodedados->resultadoArrayTotal($query);
    }

    /**
     * 
     * @param int|null $cobranca
     * @param bool $geraEmAberto
     * @return array
     * @throws Exception
     */
    function buscaCobrancasComFaturasGeradas($cobranca = null, $geraEmAberto = false , $jaGeradas = false) {

        $where = array();
        if ($cobranca) {
            $where[] = "c.coa_id = {$cobranca}";
        }
        if (!$geraEmAberto) {
            $where[] = "c.coa_valor_total > 0";
            $where[] = "c.cot_id NOT IN(1,6,7,8,12)";
        }

        if ($jaGeradas) {
            $where[] = "c.gerado_nota = 1";
        } else {
            $where[] = "c.gerado_nota = 0";
        }
        $sqlwhere = implode(" AND ", $where);
        $query = $this->bancodedados->query("SELECT 
                                                c.* , c.coa_valor_total valor_total,
                                                ci.cli_id AS cliente_id,
                                                ci.cli_nome AS cliente_nome,
                                                ci.cli_cpf_cnpj AS cliente_cpf_cnpj,
                                                ci.cli_inscricao_estadual AS cliente_inscricao_estadual,
                                                ci.clt_id AS cliente_tipo,
                                                ci.clo_id AS origem_id,
                                                ci.cli_bloqueio_dias AS bloqueio_dias,
                                                ci.cep_id AS cep, 
                                                ci.cli_complemento AS complemento,
                                                ci.con_id AS condominio_id,
                                                ci.cof_id AS cliente_formato_id, 
                                                if(c.coa_valor_original >= coa_valor_total , 1 , 0) AS eHBonificado
                                            FROM 
                                                tab_cobrancas c 
                                                INNER JOIN tab_clientes ci ON (c.cli_id = ci.cli_id)
                                            WHERE 
                                                {$sqlwhere} LIMIT 15");

        $cobranca = $this->bancodedados->resultadoArrayTotal($query);
        if(empty($cobranca[0]['cep']) && !empty($cobranca)) {
            $condominio_id = $cobranca[0]['condominio_id'];
            $query = $this->bancodedados->query("SELECT * FROM tab_condominios WHERE con_id = {$condominio_id}");
            $condominio = $this->bancodedados->resultadoArrayTotal($query);
            if(!empty($condominio[0]['cep_id'])) {
                $cobranca[0]['cep'] = $condominio[0]['cep_id'];
            } else {
                
            }
        }
        return $cobranca;
    }

    /**
     * 
     * @param string|Stringable|null $cep
     * @return array
     * @throws Exception
     */
    function buscaDadosCidade($cep) {

        $cep = $this->apenasNumeros($cep);
        $q = "
            SELECT
                tab_cidades.cid_ibge AS cid_ibge,
                tab_cidades.cid_tom AS cid_tom,
                tab_estados.est_nome AS estado,
                tab_estados.est_sigla AS estado_sigla,
                tab_cidades.cid_nome AS cidade,
                tab_bairros.bai_nome AS bairro,
                tab_logradouros.log_nome AS logradouro,
                tab_ceps.cep_exportacao AS cep_exportacao,
                IF(tab_cidades.cid_cep_geral, tab_cidades.cid_cep_geral, tab_ceps.cep_exportacao) AS cep_geral
            FROM
                tab_ceps
                INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
                INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
                INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
                INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
            WHERE
                tab_ceps.cep_id = " . $cep;
        $query = $this->bancodedados->query($q);

        return $this->bancodedados->resultadoArrayTotal($query);
    }

    /**
     * 
     * @param int $cliente_id
     * @return array
     * @throws Exception
     */
    function buscaContatosEmails($cliente_id) {

        $lista = array();
        $q = "SELECT CONCAT(ccm_usuario, '@', ccm_dominio) AS contato_email FROM tab_clientes_contatos_emails WHERE cli_id = $cliente_id LIMIT 1";
        $query = $this->bancodedados->query($q);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $lista[] = $dados;
        }
        
        if(empty($lista)) {
            $lista[] = "boleto@oletv.net.br";
        }

        return $lista;
    }

    /**
     * 
     * @param array $cobranca
     * @return array|false
     * @throws Exception
     */
    function buscaItensParaNotaPelaCobranca($cobranca) {

        $lista = array();
        $q = "
            SELECT 
                d.cod_valor_final valor_final_item, d.cod_descricao descricao, d.ajf_id, d.cod_id, d.ass_id, a.pla_id, f.afc_id, d.coa_id, d.cod_id, 
                DATE_FORMAT(d.cod_competencia_inicio, '%m/%Y') AS competencia,
                d.cod_valor_final + IFNULL((SELECT SUM(d2.cod_valor_final) FROM tab_cobrancas_detalhes d2 WHERE d.cod_id = d2.cod_pai AND d2.cod_valor_final < 0 AND ajf_id IS NOT NULL), 0) AS valor_final,
                0 + IFNULL((SELECT SUM(d2.cod_valor_final) FROM tab_cobrancas_detalhes d2 WHERE d.cod_id = d2.cod_pai AND d2.cod_valor_final < 0 AND ajf_id IS NOT NULL), 0) AS valor_ajuste,
                0 + IFNULL((SELECT SUM(d2.cod_valor_final) FROM tab_cobrancas_detalhes d2 WHERE d.cod_id = d2.cod_pai AND d2.cod_valor_final < 0 AND dec_id IS NOT NULL), 0) AS valor_desconto
            FROM 
                tab_cobrancas_detalhes d
                LEFT JOIN tab_assinaturas a ON d.ass_id = a.ass_id
                LEFT JOIN tab_ajustes_financeiros f ON f.ajf_id = d.ajf_id 
            WHERE 
                d.coa_id = {$cobranca['coa_id']} 
                AND d.cod_valor_final > 0
                AND (d.ajf_id IN (SELECT ajf_id FROM tab_ajustes_financeiros AS a LEFT JOIN tab_ajustes_financeiros_categorias AS c ON c.afc_id = a.afc_id WHERE c.gerar_nfse = 1)
                    OR d.ass_id IN (SELECT ass_id FROM tab_assinaturas AS a LEFT JOIN tab_planos AS p ON p.pla_id = a.pla_id WHERE p.gerar_nfse = 1))";

        $query = $this->bancodedados->query($q);
        $dados = $this->bancodedados->resultadoArrayTotal($query);
        if (!$dados) {
            return false;
        }

		$c = 0;
        foreach ($dados as $value) {
            if (!empty($value['coa_id']) || $value['coa_id'] > 0) {

                $valor_final = $this->bancodedados->resultadoArray($this->bancodedados->query("SELECT SUM(cod_valor_final) valor FROM tab_cobrancas_detalhes WHERE dec_id IS NOT NULL AND cod_pai = {$value['cod_id']}"));
                $value["valor_final"] = ($valor_final) ? $value['valor_final'] + ($valor_final['valor']) : $value['valor_final'];

                if($value["valor_final"] > 0) {
                    if ($value['ajf_id']) {
                        $qAFC = "SELECT 
                                    c.gerar_nfse, s.cod_serv AS codigo_servico, c.nds AS nbs,
                                    cn.cnae AS cnae, s.codigo AS codigoServicoNacional,
                                    c.aliquota, c.issRetido, c.afc_id, s.descricao AS descricao
                                FROM 
                                    tab_ajustes_financeiros_categorias AS c 
                                    LEFT JOIN tab_notas_cnae AS cn ON cn.id = c.cnae_id
                                    LEFT JOIN tab_notas_servicos AS s ON s.id = c.codigo_servico_id
                                WHERE 
                                    c.gerar_nfse = 1 AND c.afc_id = {$value['afc_id']}";

                        $query = $this->bancodedados->query($qAFC);
                        $dados = $this->bancodedados->resultadoArray($query);
                        $value["dados"] = (object) $dados;
                        $value["items"] = $value['descricao'] . " - Ref.:" . $value['competencia'];
                        $lista[] = $value;
                    } else {
                        $valorFinal = $value["valor_final"] - $cobranca['coa_valor_bonificacao'];
                        if($cobranca['eHBonificado'] && ($valorFinal  < $value["valor_final"]) && $c == 0) {
                            $value["valor_final"] = $valorFinal;
                            $c++;
                        }
                        $qAss = "SELECT 
                                    p.gerar_nfse, s.cod_serv AS codigo_servico, p.nbs AS nbs,
                                    cn.cnae AS cnae, s.codigo AS codigoServicoNacional,
                                    p.aliquota, p.issRetido, p.pla_id, s.descricao AS descricao
                                FROM 
                                    tab_planos AS p
                                    LEFT JOIN tab_notas_cnae AS cn ON cn.id = p.cnae_id
                                    LEFT JOIN tab_notas_servicos AS s ON s.id = p.codigo_servico_id
                                WHERE 
                                    p.gerar_nfse = 1 AND 
                                    p.pla_id = {$value['pla_id']}";

                        $queryAss = $this->bancodedados->query($qAss);
                        $dadosAss = $this->bancodedados->resultadoArray($queryAss);

                        $value["dados"] = (object) $dadosAss;
                        if ($dados) {
                            $value["items"] = $value['descricao'] . " - Ref.:" . $value['competencia'];
                        }
                        $lista[] = $value;
                    }
                } else {
                    $list = $lista;
                }
            } else {
                $lista = false;
            }
        }

        return $lista;
    }

    /**
     * 
     * @param string $notaGerada "0,1" "0"
     * @param string $status "2,3" "2" 
     * @return array
     * @throws Exception
     */
    function buscaNotasNaoGeradas($notaGerada = '0', $status = '2') {

        $q = "SELECT * FROM tab_notas WHERE nota_gerada IN ($notaGerada) AND status_id IN ($status)";
        $query = $this->bancodedados->query($q);

        return $this->bancodedados->resultadoArrayTotal($query);
    }

    /**
     * 
     * @param int $id
     * @param string $notaGerada "0,1" "0"
     * @param string $status "2,3" "2"
     * @return array
     * @throws Exception
     */
    function buscaNotasNaoGeradasPorId($id, $notaGerada = '0', $status = '2') {

        $q = "SELECT * FROM tab_notas WHERE nota_gerada IN ($notaGerada) AND status_id IN ($status) AND coa_id = $id";
        $query = $this->bancodedados->query($q);

        return $this->bancodedados->resultadoArrayTotal($query);
    }

    /**
     * 
     * @param int $coaId
     * @return int
     * @throws Exception
     */
    function insereDadosNota($coaId) {

        $valueCpfCnpj = $insertCpfCnpj = $valueComplemento = $insertComplemento = $insertInscricaoMunicipal = $valueInscricaoMunicipal = "";
        if (isset($this->dadosNota->tomador->cnpj)) {
            $valueCpfCnpj = $this->dadosNota->tomador->cnpj;
            $insertCpfCnpj = "tomserv_idetom_cpfCnpj_cnpj,";
        } else {
            $valueCpfCnpj = $this->dadosNota->tomador->cpf;
            $insertCpfCnpj = "tomserv_idetom_cpfCnpj_cpf,";
        }
        if (isset($this->dadosNota->tomador->inscricaoMunicipal)) {
            $valueInscricaoMunicipal = "'{$this->dadosNota->tomador->inscricaoMunicipal}',";
            $insertInscricaoMunicipal = "tomserv_inscricaoMunicipal,";
        }
        if (isset($this->dadosNota->tomador->end_complemento)) {
            $insertComplemento = "tomserv_end_complemento, ";
            $valueComplemento = "'" . $this->ajusteCaracteresDB($this->dadosNota->tomador->end_complemento) . "',";
        }

        $q = "INSERT INTO tab_notas (status_id, 
                loteRps_numeroLote, identificacaoRps_serie, identificacaoRps_tipo, 
                dataEmissaoRps, naturezaOperacao, regimeEspecialTributacao, optanteSimplesNacional, incentivadorCultural, 
                servico_valores_valorServicos, servico_valores_valorIss, servico_valores_aliquota, servico_valores_issRetido,
                servico_itemListaServico, servico_codigoCnae, servico_discriminacao, 
                $insertCpfCnpj $insertInscricaoMunicipal tomserv_razaoSocial, tomserv_end_endereco, tomserv_end_numero, tomserv_end_bairro, 
                $insertComplemento tomserv_end_codigoMunicipio, tomserv_end_uf, tomserv_end_cep, tomserv_contato_telefone, tomserv_contato_email, coa_id, nota_gerada, 
                plano_id, afc_id)

                VALUES (3 , '{$this->dadosNota->loteRps->numeroLote}', 'UNICA', 1, '{$this->dadosNota->dataEmissao}', 1, 1, 1, 2, 
                '{$this->dadosNota->servico->valorServicos}', '{$this->dadosNota->servico->valorIss}', '{$this->dadosNota->servico->aliquota}', '{$this->dadosNota->servico->issRetido}',
                '{$this->dadosNota->servico->itemListaServico}', '{$this->dadosNota->servico->codigoCnae}',
                '" . $this->ajusteCaracteresDB($this->dadosNota->servico->discriminacao) . "', '$valueCpfCnpj', $valueInscricaoMunicipal '" .
                $this->ajusteCaracteresDB($this->dadosNota->tomador->razaoSocial) . "', '" . $this->ajusteCaracteresDB($this->dadosNota->tomador->end_endereco) .
                "', '" . $this->ajusteCaracteresDB($this->dadosNota->tomador->end_numero) . "', '" . $this->ajusteCaracteresDB($this->dadosNota->tomador->end_bairro) .
                "', $valueComplemento '{$this->dadosNota->tomador->end_codigoMunicipio}', '{$this->dadosNota->tomador->end_uf}', '{$this->dadosNota->tomador->end_cep}',
                '{$this->apenasNumeros($this->dadosNota->tomador->telefone)}', '" . $this->ajusteCaracteresDB($this->dadosNota->tomador->email) . "', $coaId, 1,
                '{$this->dadosNota->plano}', '{$this->dadosNota->afc}')";

        $this->bancodedados->query($q);

        return $this->dadosNota->loteRps->numeroLote = $this->dadosNota->identificacaoRps->numero = $this->bancodedados->ultimoIdInserido();
    }

    /**
     * 
     * @param array $nota
     * @param bool $tipoCodServ - codigo - false , cod_serv - true
     * @return array|false
     * @throws Exception
     */
    function atualizaAliquotaTipoServCnae($nota, $tipoCodServ = false) {

        $obj = (object) $nota;

        if ($obj->plano_id > 0) {
            $q = "SELECT p.pla_id, p.aliquota, s.codigo, s.cod_serv, c.cnae, p.nbs, p.gerar_nfse
                    FROM tab_planos AS p 
                    LEFT JOIN tab_notas_cnae AS c ON c.id = p.cnae_id
                    LEFT JOIN tab_notas_servicos AS s ON s.id = p.codigo_servico_id
                    WHERE p.pla_id = " . $obj->plano_id;
        }

        if ($obj->afc_id > 0) {
            $q = "SELECT f.afc_id, f.aliquota , s.codigo, s.cod_serv, c.cnae, f.nbs, f.gerar_nfse
                    FROM tab_ajustes_financeiros_categorias AS f 
                    LEFT JOIN tab_notas_cnae AS c ON c.id = f.cnae_id
                    LEFT JOIN tab_notas_servicos AS s ON s.id = f.codigo_servico_id
                    WHERE f.afc_id = " . $obj->afc_id;
        }

        $query = $this->bancodedados->query($q);
        $dados = $this->bancodedados->resultadoArrayTotal($query);

        $cod = $tipoCodServ ? $dados[0]['cod_serv'] : $dados[0]['codigo'];
        $q = "  UPDATE tab_notas 
                SET 
                servico_itemListaServico = '{$cod}', 
                servico_valores_aliquota = '{$dados[0]['aliquota']}', 
                servico_codigoCnae = '{$dados[0]['cnae']}'
                WHERE id = '{$obj->id}'";

        $retorno = [];
        $retorno['cod_ser'] = $cod;
        $retorno['cod_cnae'] = $dados[0]['cnae'];
        $retorno['aliquota'] = $dados[0]['aliquota'];
        $this->bancodedados->query($q);

        if ($dados[0]['gerar_nfse']) {
            return $retorno;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param int $notaId
     * @param array $codigosErros
     * @return void
     * @throws Exception
     */
    function insereLogs($notaId, $codigosErros) {

        foreach ($codigosErros as $cod) {
            $codigo = (object) $cod;
            if ($codigo->Codigo == "E514") {
                $this->atualizaDadosNota($notaId, "E514");
                break;
            }
            $this->insertTabLog($notaId, $codigo, false);
        }
    }

    /**
     * 
     * @param int $notaId
    * @param array|object|string|null $codigo
     * @param bool $retornoDeMsg
     * @return void
     * @throws Exception
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
            $msg = $this->encodingText($codigo->Mensagem);
            $retorno = $this->encodingText($codigo->Correcao);
        }

        $date = date('Y-m-d H:i:s');
        $q = "INSERT INTO tab_notas_log(nota, retorno, cod, msg, data) VALUES ('$notaId', '$retorno', '$cod', '$msg', '$date')";
        $this->bancodedados->query($q);
    }

    /**
     * 
     * @param string $text
     * @return string
     * @throws Exception
     */
    function encodingText($text) {
        $cur_encoding = mb_detect_encoding($text);
        if ($cur_encoding == "UTF-8" && mb_check_encoding($text, "UTF-8")) {
            return utf8_decode($text);
        } else {
            return $text;
        }
    }

    /**
     * 
     * @param int $id
     * @throws Exception
     */
    function atualizaNotaParaErro($id) {

        $q = "UPDATE tab_notas SET nota_gerada = 0, status_id = '5' WHERE id = '{$id}'";
        $this->bancodedados->query($q);
    }
}
