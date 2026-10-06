<?php

use Spatie\Async\Pool;

class NotaSJ {

    var $criarXMLRetorno;
    var $bancodedados;
    var $dadosNota;
    var $salvarNota;
    var $nota;
    var $fake;
    var $teste;
    var $config = [
        'cnpj' => '23911953000107',
        'ctom' => '8327',
        'ibge' => '4214805',
        'razao' => 'OTT AND IPTV LTDA',
        'usuario' => '23911953000107',
        'senha' => 'Ott2018!',
        'tpamb' => 1];

    /**
     * 
     * @param object $bd
     * @param bool $criarXMLRetorno
     * @param object $class
     * @param bool $fake
     * @param bool $teste
     */
    function __construct($bd, $criarXMLRetorno = false, $class = null, $fake = false, $teste = false) {
        $this->criarXMLRetorno = $criarXMLRetorno;
        $this->bancodedados = $bd;
        $this->nota = $class;
        $this->fake = $fake;
        $this->teste = $teste;

        $sql = "SELECT * FROM tab_notas_login WHERE status = '1' limit 1;";
        $query = $this->bancodedados->query($sql);
        $dados = $this->bancodedados->resultadoArray($query);
        if ($dados) {
            $this->config['usuario'] = $dados['login'];
            $this->config['senha'] = $dados['senha'];
        }
    }

    /**
     * 
     * @param int|null $cobrancaId
     * @param bool $geraEmAberto
     */
    function emissao($cobrancaId = null, $geraEmAberto = false) {

        $cobrancas = $this->nota->buscaCobrancasComFaturasGeradas($cobrancaId, $geraEmAberto);
		
        foreach ($cobrancas as $cobranca) {
            $this->dadosNota = null;
            $cobranca["parametros"] = unserialize($cobranca["coa_parametros"]);
            $cobranca['id'] = $cobranca['coa_id'];
            $itens[0] = $this->nota->buscaItensParaNotaPelaCobranca($cobranca);

            if ($itens[0]) {
                $this->executaGeracaoDeNotas($itens, $cobranca, false);
                
            } else {
                $this->nota->atualizaCobranca($cobranca['id']);
            }
            sleep(1);
        }
    }

    /**
     * 
     * @param bool $naoGeradasPorId
     * @param int|null $id
     * @return void
     * @throws Exception
     */
    function gerarNotaNaoGeradas($naoGeradasPorId = false, $id = null) {

        if ($naoGeradasPorId) {

            $cobrancas = $this->nota->buscaCobrancasComFaturasGeradas($id, true, true);
            
            $this->bancodedados->query("UPDATE tab_cobrancas SET gerado_nota = 0 WHERE coa_id = {$id}");
            $this->bancodedados->query("DELETE FROM tab_notas_log WHERE nota IN (SELECT id FROM tab_notas WHERE coa_id = {$id})");
            $this->bancodedados->query("DELETE FROM tab_notas WHERE coa_id = {$id}");

            foreach ($cobrancas as $cobranca) {
            
                $this->dadosNota = null;
                $cobranca["parametros"] = unserialize($cobranca["coa_parametros"]);
                $cobranca['id'] = $cobranca['coa_id'];
                $itens[0] = $this->nota->buscaItensParaNotaPelaCobranca($cobranca);
                
                if ($itens[0]) {
                    $this->executaGeracaoDeNotas($itens, $cobranca, false);
                    
                } else {
                    $this->nota->atualizaCobranca($cobranca['id']);
                }
            }
            
        } else {
            $notas = $this->nota->buscaNotasNaoGeradas("0,1", "2,3,5");
            $this->executaGeracaoDeNotas($notas, null, true);
        }

    }

    function gerarNaoCancelados() {

        $notas = $this->nota->buscaNotasNaoGeradas("0", "1");

        if ($notas) {
            foreach ($notas as $nota) {
                $this->gerarCancelamento($nota['id'], $nota['numero']);
            }
        }
    }

    /**
     * 
     * @param mixed $itens
     * @param array|null $cobranca
     * @param bool $notasNaoGeradas
     */
    function executaGeracaoDeNotas($itens, $cobranca = null, $notasNaoGeradas = false) {
        if (!$itens) {
            return;
        }
		
        foreach ($itens as $itemNota) {
            $this->processarGeracaoDeNotas($itemNota, $cobranca, $notasNaoGeradas);
        }            
    }

    /**
     * Processa um item de nota individual
     * 
     * @param mixed $itens
     * @param bool $notasNaoGeradas
     * @param array|null $cobranca
     * @throws Exception
     * @return void
     */
    function processarGeracaoDeNotas($itens, $cobranca = null, $notasNaoGeradas = false) {

        foreach ($itens as $item) {
            if ($notasNaoGeradas) {
                $this->montaDadosNotaNaoGerada($item);
                $retorno['idNota'] = $this->dadosNota->id;
            } else {
                $this->nota->atualizaCobranca($cobranca['id']);
                $this->montaDadosNota($cobranca, $item);
                $retorno['idNota'] = $this->insereDadosNota($cobranca['id']);
            }
            if ($item) {
                try {
                    $retorno['dados'] = $this->gerarNota($retorno['idNota']);
                    if ($retorno['dados'] == false) {
                        throw new Exception($retorno['idNota'], 2);
                    }
                    
                    $retorno['cobrancaId'] = $cobranca['id'];
                } catch (\Exception $e) {
                    throw new Exception($retorno['idNota'], 2);
                }
            } else {
                throw new Exception($retorno['idNota'], 2);
            }
            $nota = (object) $retorno;
            if ($nota->dados) {
                if (!empty($nota->dados->cod_verificador_autenticidade) || !empty($nota->dados->nf->cod_verificador_autenticidade)) {
                    $this->atualizaDadosNota($nota->idNota, $nota->dados);
                    $this->registrarNota($nota->idNota);
                } else {
                    if (empty($nota->dados['retorno'])) {
                        $this->atualizaDadosNota($nota->idNota, $nota->dados);
                        $this->insereLog($nota);
                    } else {
                        $this->atualizaDadosNota($nota->idNota , null, 5, 0);
                    }
                }
            } else {    
                $this->atualizaDadosNota($nota->idNota , null, 5, 0);
            }
        }
    }

    /**
     * 
     * @param array $cobranca
     */
    function enviaEmail($cobranca) {
    
    }

    /**
     * Summary of gerarNota
     * @param mixed $id
     * @return string
     */
    function gerarNota($id) {

        $xml = $this->xmlEmissao();
        $novoXML = $this->remontaXML($xml, $id);

        return $this->enviarXml($novoXML, "Emissao", "_" . $id, $this->criarXMLRetorno, $this->fake, 2);
    }
    
    /**
     * 
     * @return string
     */
    function xmlEmissao() {
        
        return "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
                <nfse>
                    [nfse_teste]
                    <nf>
                        <valor_total>[valor_total]</valor_total>
                        <valor_desconto>[valor_desconto]</valor_desconto>
                        <valor_ir>[valor_ir]</valor_ir>
                        <valor_inss>[valor_inss]</valor_inss>
                        <valor_contribuicao_social>[valor_contribuicao_social]</valor_contribuicao_social>
                        <valor_rps>[valor_rps]</valor_rps>
                        <valor_pis>[valor_pis]</valor_pis>
                        <valor_cofins>[valor_cofins]</valor_cofins>
                        <IBSCBS>
                            <pRedutor>[pRedutor]</pRedutor>
                            <cLocalidadeIncid>[cLocalidadeIncid]</cLocalidadeIncid>
                            <valores>
                                <vBC>[vBC]</vBC>
                                <uf>
                                    <pIBSUF>[pIBSUF]</pIBSUF>
                                    <pRedAliqUF>[pRedAliqUF]</pRedAliqUF>
                                    <pAliqEfetUF>[pAliqEfetUF]</pAliqEfetUF>
                                </uf>
                                <mun>
                                    <pIBSMun>[pIBSMun]</pIBSMun>
                                    <pRedAliqMun>[pRedAliqMun]</pRedAliqMun>
                                    <pAliqEfetMun>[pAliqEfetMun]</pAliqEfetMun>
                                </mun>
                                <fed>
                                    <pCBS>[pCBS]</pCBS>
                                    <pRedAliqCBS>[pRedAliqCBS]</pRedAliqCBS>
                                    <pAliqEfetCBS>[pAliqEfetCBS]</pAliqEfetCBS>
                                </fed>
                            </valores>
                            <totCIBS>
                                <vTotNF>[vTotNF]</vTotNF>
                                <gIBS>
                                    <vIBSTot>[vIBSTot]</vIBSTot>
                                    <gIBSUFTot>
                                        <vDifUF>[vDifUF]</vDifUF>
                                        <vIBSUF>0</vIBSUF>
                                    </gIBSUFTot>
                                    <gIBSMunTot>
                                        <vDifMun>[vDifMun]</vDifMun>
                                        <vIBSMun>0</vIBSMun>
                                    </gIBSMunTot>
                                </gIBS>
                                <gCBS>
                                    <vDifCBS>[vDifCBS]</vDifCBS>
                                    <vCBS>0</vCBS>
                                </gCBS>
                            </totCIBS>
                        </IBSCBS>
                    </nf>

                    <prestador>
                        <cpfcnpj>[prestador_cpfcnpj]</cpfcnpj>
                        <cidade>[prestador_cidade]</cidade>
                    </prestador>

                    <tomador>
                        <tipo>[tipo]</tipo>
                        <cpfcnpj>[cpfcnpj]</cpfcnpj>
                        [ie]
                        <nome_razao_social>[nome_razao_social]</nome_razao_social>
                        <sobrenome_nome_fantasia>[sobrenome_nome_fantasia]</sobrenome_nome_fantasia>
                        <logradouro>[logradouro]</logradouro>
                        <email>[email]</email>
                        <complemento>[complemento]</complemento>
                        <bairro>[bairro]</bairro>
                        <cidade>[cidade]</cidade>
                        <cep>[cep]</cep>
                        <ddd_fone_comercial>[ddd]</ddd_fone_comercial>
                        <fone_comercial>[fone]</fone_comercial>
                    </tomador>

                    <itens>
                        [lista_itens]
                    </itens>
                </nfse>";
    }

    /**
     * 
     * @param int|null $notaId
     * @param int|null $numero
     * @return object
     */
    function gerarCancelamento($notaId = null, $numero = null) {

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
                <nfse>
                    [nfse_teste]
                    <nf>
                        <numero>[nf_numero]</numero>
                        <situacao>C</situacao>
                        <serie_nfse>[serie_nfse]</serie_nfse>
                        <observacao>[nf_observacao]</observacao>
                    </nf>
                    <prestador>
                        <cpfcnpj>[prestador_cpfcnpj]</cpfcnpj>
                        <cidade>[prestador_cidade]</cidade>
                    </prestador>
                </nfse>";
        $novoXML = $this->remontaXmlCancelar($xml, $notaId, $numero);
        $retorno = $this->enviarXml($novoXML, "Cancelar", "_" . $notaId, $this->criarXMLRetorno, $this->fake, 4);

        if (!empty($retorno['erro']) && $retorno['erro'] == true) {
            $this->atualzaNotaCancelada($notaId, 0);
        } else {
            if ($retorno->situacao_codigo_nfse == 2) {
                $this->atualizaDadosNota($notaId, null, 1, 1);
            } else {
                $this->atualizaDadosNota($notaId, null, 1, 0);
            }
            if (!empty($retorno->mensagem->codigo)) {
                $nota = new stdClass();
                $nota->dados = new stdClass();
                $nota->dados = $retorno;
                $nota->idNota = $notaId;
                $this->insereLog($nota, false);
            }
        }

        return $retorno;
    }

    /**
     * 
     * @param int|null $numero
     * @param bool $porCodigo
     * @return object
     */
    function consultarNota($numero = null, $porCodigo = false) {

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
                <nfse>
                    <pesquisa>
                        <codigo_autenticidade>[codigo_autenticidade]</codigo_autenticidade>
                    </pesquisa>
                </nfse>";
        $novoXML = $this->remontaXmlConsulta($xml, $numero, $porCodigo);
        $retorno = $this->enviarXml($novoXML, "Consulta", "_" . $numero, $this->criarXMLRetorno, $this->fake, 2, false);

        if (empty($retorno->nf)) {
            $nota = new stdClass();
            $nota->dados = new stdClass();
            $nota->idNota = $numero;
            if (!empty($retorno['retorno']->retorno)) {
                $nota->erro = $retorno['retorno']->retorno->code . " - " . $retorno['retorno']->retorno->msg;
                $this->insereLog($nota, false, true);
            } else {
                $nota->dados = $retorno;
                if (empty($retorno)) {
                    $nota->erro = "0 - Erro desconhecido";
                    $this->insereLog($nota, false, true);
                } else {
                    $this->insereLog($nota, false);
                }
            }
            return false;
        } else {
            if ($retorno->nf->situacao_codigo_nfse == 2) {
                $this->atualizaDadosNota($numero, null, 1, 1);
            }
        }
        if ($this->fake) {
            $retorno->codigo_html = utf8_decode($retorno->codigo_html);
        }

        return $retorno;
    }

    /**
     * 
     * @param string $xml
     * @param int $id
     * @return string
     */
    function remontaXML($xml, $id) {

        $nfseTeste = $this->teste ? "<nfse_teste>1</nfse_teste>" : "";
        $xml = str_replace('[nfse_teste]', $nfseTeste, $xml);

        $itensIBSCDS = $this->calcularIBSCBS(
            $this->dadosNota->nf->valor_total_nao_formatado,
            0.1, // IBS UF
            0.0, // IBS Municipio
            0.9, // CBS
            $this->dadosNota->itens->lista_itens
        );

        $xml = str_replace('[lista_itens]',               $this->gerarLista($itensIBSCDS['itens'], $id), $xml);

        $xml = str_replace('[valor_total]',               $this->dadosNota->nf->valor_total, $xml);
        $xml = str_replace('[valor_desconto]',            $this->dadosNota->nf->valor_desconto, $xml);
        $xml = str_replace('[valor_ir]',                  $this->dadosNota->nf->valor_ir, $xml);
        $xml = str_replace('[valor_inss]',                $this->dadosNota->nf->valor_inss, $xml);
        $xml = str_replace('[valor_contribuicao_social]', $this->dadosNota->nf->valor_contribuicao_social, $xml);
        $xml = str_replace('[valor_rps]',                 $this->dadosNota->nf->valor_rps, $xml);
        $xml = str_replace('[valor_pis]',                 $this->dadosNota->nf->valor_pis, $xml);
        $xml = str_replace('[valor_cofins]',              $this->dadosNota->nf->valor_cofins, $xml);

        $xml = str_replace('[pRedutor]',            "0", $xml);
        $xml = str_replace('[cLocalidadeIncid]',    $this->config['ibge'], $xml);

        // IBSCBS
        
        $xml = str_replace('[vBC]',          $this->nota->formataMoeda($itensIBSCDS['vBC'] , ""), $xml);
        $xml = str_replace('[pIBSUF]',       $this->nota->formataMoeda($itensIBSCDS['pIBSUF'] , ""), $xml);
        $xml = str_replace('[pRedAliqUF]',   $this->nota->formataMoeda($itensIBSCDS['pRedAliqUF'] , ""), $xml);
        $xml = str_replace('[pIBSMun]',      $this->nota->formataMoeda($itensIBSCDS['pIBSMun'] , ""), $xml);
        $xml = str_replace('[pRedAliqMun]',  $this->nota->formataMoeda($itensIBSCDS['pRedAliqMun'] , ""), $xml);
        $xml = str_replace('[pCBS]',         $this->nota->formataMoeda($itensIBSCDS['pCBS'] , ""), $xml);
        $xml = str_replace('[pRedAliqCBS]',  $this->nota->formataMoeda($itensIBSCDS['pRedAliqCBS'] , ""), $xml);
        $xml = str_replace('[pAliqEfetUF]',  $this->nota->formataMoeda($itensIBSCDS['pAliqEfetUF'] , ""), $xml);
        $xml = str_replace('[pAliqEfetMun]', $this->nota->formataMoeda($itensIBSCDS['pAliqEfetMun'] , ""), $xml);
        $xml = str_replace('[pAliqEfetCBS]', $this->nota->formataMoeda($itensIBSCDS['pAliqEfetCBS'] , ""), $xml);

        $xml = str_replace('[vIBSTot]',      $this->nota->formataMoeda($itensIBSCDS['totCIBS']['vIBSTot'] , ""), $xml);
        $xml = str_replace('[vDifUF]',       $this->nota->formataMoeda($itensIBSCDS['totCIBS']['vDifUF'] , ""), $xml);
        $xml = str_replace('[vDifMun]',      $this->nota->formataMoeda($itensIBSCDS['totCIBS']['vDifMun'] , ""), $xml);
        $xml = str_replace('[vIBSTot]',      $this->nota->formataMoeda($itensIBSCDS['totCIBS']['vIBSTot'] , ""), $xml);
        $xml = str_replace('[vDifCBS]',      $this->nota->formataMoeda($itensIBSCDS['totCIBS']['vDifCBS'] , ""), $xml);
        $xml = str_replace('[vTotNF]',       $this->nota->formataMoeda($itensIBSCDS['totCIBS']['vTotNF'] , ""), $xml);
        // Fim IBSCBS

        $xml = str_replace('[prestador_cpfcnpj]',         $this->dadosNota->prestador->cpfcnpj, $xml);
        $xml = str_replace('[prestador_cidade]',          $this->dadosNota->prestador->cidade, $xml);

        $xml = str_replace('[tipo]',                      $this->dadosNota->tomador->tipo, $xml);
        $xml = str_replace('[cpfcnpj]',                   $this->dadosNota->tomador->cpfcnpj, $xml);

        if($this->dadosNota->tomador->ie) {
            $xml = str_replace('[ie]', "<ie>" .           $this->dadosNota->tomador->ie . "</ie>", $xml);
        } else {
			$xml = str_replace('[ie]', "", $xml);
        }
        $xml = str_replace('[nome_razao_social]',         $this->substituirCaracteres($this->dadosNota->tomador->nome_razao_social), $xml);
        $xml = str_replace('[sobrenome_nome_fantasia]',   $this->substituirCaracteres($this->dadosNota->tomador->sobrenome_nome_fantasia), $xml);
        $xml = str_replace('[logradouro]',                $this->substituirCaracteres($this->dadosNota->tomador->logradouro), $xml);
        $xml = str_replace('[email]',                     $this->dadosNota->tomador->email, $xml);
        $xml = str_replace('[complemento]',               $this->substituirCaracteres($this->dadosNota->tomador->complemento), $xml);
        $xml = str_replace('[bairro]',                    $this->substituirCaracteres($this->dadosNota->tomador->bairro), $xml);
        $xml = str_replace('[cidade]',                    $this->dadosNota->tomador->cidade, $xml);
        $xml = str_replace('[cep]',                       $this->dadosNota->tomador->cep, $xml);
        $xml = str_replace('[ddd]',                       $this->dadosNota->tomador->ddd_fone_comercial, $xml);
        $xml = str_replace('[fone]',                      $this->dadosNota->tomador->fone_comercial, $xml);

        return $xml;
    }
    
    /**
     * 
     * @param string $texto 
     * @return string
     */
    function substituirCaracteres($texto = "") {

        $texto = preg_replace('/[*.-\/,-\/*%$#@:;?<>!\'"|\\¨+=}\]\{\[]/', '', trim($texto));
        $texto = preg_replace('/&/', '&amp;', trim($texto));
        
        return $texto;
    }

    /**
     * Valida e retorna um email válido ou um email padrão
     * 
     * @param string $email Email a ser validado
     * @param string $emailPadrao Email padrão caso o email seja inválido
     * @return string
     */
    function validarEmail($email, $emailPadrao = 'financeiro@ole.net.br') {
        // Remove espaços em branco
        $email = trim($email);
        
        // Verifica se o email está vazio ou nulo
        if (empty($email)) {
            return $emailPadrao;
        }
        
        // Valida o formato do email
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return $emailPadrao;
    }

    /**
     * 
     * @param array | object $itens
     * @param int $id
     * @return string
     */
    function gerarLista($itens, $id) {

        $lista = "";
        $valor_final = 0;
        foreach ($itens as $item) {
            $item = is_object($item) ? (array) $item : $item;
            if (!is_array($item) || empty($item['dados'])) {
                continue;
            }

            $dados = is_object($item['dados']) ? $item['dados'] : (object) $item['dados'];
            $codigoServicos = isset($dados->codigo_servico) ? str_replace('.', '', $dados->codigo_servico) : '';
            $codigoServico = ltrim($codigoServicos, '0');
            if ($codigoServico === '') {
                // XSD exige xs:integer; não envia item sem código de serviço
                continue;
            }

            $aliquota = number_format(isset($dados->aliquota) ? $dados->aliquota : 0, 2, ',', '');
            $codigoNbs = !empty($dados->nbs) ? $dados->nbs : '';
            $descritivo = isset($item['items']) ? $item['items'] : '';
            
            $lista .= "<lista>";
            $lista .= "     <codigo_local_prestacao_servico>{$this->config['ctom']}</codigo_local_prestacao_servico>";
            $lista .= "     <codigo_item_lista_servico>{$codigoServico}</codigo_item_lista_servico>";
            if ($codigoNbs) {
                $lista .= "     <codigo_nbs>{$codigoNbs}</codigo_nbs>";
            }
            $lista .= "     <descritivo>{$descritivo}</descritivo>";
            $lista .= "     <aliquota_item_lista_servico>{$aliquota}</aliquota_item_lista_servico>";
            $lista .= "     <situacao_tributaria>00</situacao_tributaria>";
            $lista .= "     <valor_tributavel>{$this->nota->formataMoeda($this->nota->apenasNumeros($item['valor_bruto']), "")}</valor_tributavel>";
            $lista .= "     <valor_deducao>0,00</valor_deducao>";
            $lista .= "     <valor_issrf>0,00</valor_issrf>";
            $lista .= "     <tributa_municipio_prestador>S</tributa_municipio_prestador>";
            $lista .= "</lista>";

            $valor_final = $valor_final + $item['valor_bruto'];
        }

        $this->dadosNota->nf->valor_total = $this->nota->formataMoeda($this->nota->apenasNumeros($valor_final), "");

        $this->bancodedados->query("
                UPDATE tab_notas SET 
                    servico_valores_valorServicos = {$valor_final},
                    servico_valores_baseCalculo = {$valor_final}
                WHERE 
                    id = '{$id}'");

        return $lista;
    }

    /**
     * 
     * @param string $xml
     * @param int $notaId
     * @param int $numero
     * @return string
     */
    function remontaXmlCancelar($xml, $notaId, $numero) {

        $nota = (object) $this->buscaNota($notaId);
        $nfseTeste = $this->teste ? "<nfse_teste>1</nfse_teste>" : "";
        $xml = str_replace('[nfse_teste]', $nfseTeste, $xml);

        $xml = str_replace('[nf_numero]', $numero, $xml);
        $xml = str_replace('[nf_observacao]', "Cancelando uma NFS-e", $xml);

        $xml = str_replace('[serie_nfse]', $nota->serie_nfse, $xml);
        $xml = str_replace('[prestador_cpfcnpj]', $this->config['cnpj'], $xml);
        $xml = str_replace('[prestador_cidade]', $this->config['ctom'], $xml);

        return $xml;
    }

    /**
     * 
     * @param string $xml
     * @param int $numero
     * @param bool $porCodigo
     * @return string
     */
    function remontaXmlConsulta($xml, $numero, $porCodigo = false) {

        $nfseTeste = $this->teste ? "<nfse_teste>1</nfse_teste>" : "";
        $xml = str_replace('[nfse_teste]', $nfseTeste, $xml);

        if ($porCodigo) {
            $xml = str_replace('[codigo_autenticidade]', $numero, $xml);
        } else {
            $nota = (object) $this->buscaNota($numero);
            $xml = str_replace('[codigo_autenticidade]', $nota->codigoVerificacao, $xml);
        }

        return $xml;
    }

    /**
     * 
     * @param array $nota
     */
    function montaDadosNotaNaoGerada($nota) {

        $obj = (object) $nota;

        $this->dadosNota = new stdClass();
        $this->dadosNota->nf = new stdClass();
        $this->dadosNota->prestador = new stdClass();
        $this->dadosNota->tomador = new stdClass();
        $this->dadosNota->itens = new stdClass();
        $this->dadosNota->itens->lista_itens = new stdClass();

        $dddFone = explode(" ", $obj->tomserv_contato_telefone);
        $ddd = $this->nota->apenasNumeros($dddFone[0]);
        $fone = $this->nota->apenasNumeros($dddFone[1]);

        $this->dadosNota->id = $obj->id;
        $this->dadosNota->nf->valor_total = $obj->servico_valores_valorServicos;
        $this->dadosNota->nf->valor_total_nao_formatado = $obj->servico_valores_valorServicos;
        $this->dadosNota->nf->valor_desconto = "0,00";
        $this->dadosNota->nf->valor_ir = "0,00";
        $this->dadosNota->nf->valor_inss = "0,00";
        $this->dadosNota->nf->valor_contribuicao_social = "0,00";
        $this->dadosNota->nf->valor_rps = "0,00";
        $this->dadosNota->nf->valor_pis = "0,00";
        $this->dadosNota->nf->valor_cofins = "0,00";

        $this->dadosNota->prestador->cpfcnpj = $this->config['cnpj'];
        $this->dadosNota->prestador->cidade = $this->config['ctom'];

        if ($this->nota->ehCnpj($obj->tomserv_idetom_cpfCnpj_cnpj)) {
            $this->dadosNota->tomador->tipo = "J";
        } else {
            $this->dadosNota->tomador->tipo = "F";
        }
        $this->dadosNota->tomador->cpfcnpj = $obj->tomserv_idetom_cpfCnpj_cnpj;
        $this->dadosNota->tomador->ie = $obj->tomserv_inscricaoMunicipal;
        $this->dadosNota->tomador->nome_razao_social = $obj->tomserv_razaoSocial;
        $this->dadosNota->tomador->sobrenome_nome_fantasia = $obj->tomserv_razaoSocial;
        $this->dadosNota->tomador->logradouro = empty($obj->tomserv_end_endereco)? "Sem Rua" : $obj->tomserv_end_endereco;
        $this->dadosNota->tomador->email = $this->validarEmail($obj->tomserv_contato_email);
        $this->dadosNota->tomador->cidade = $obj->tomserv_end_codigoMunicipio;
        $this->dadosNota->tomador->ddd_fone_comercial = $ddd;
        $this->dadosNota->tomador->fone_comercial = $fone;

        $dadosCidade = $this->nota->buscaDadosCidade($obj->tomserv_end_cep);
        $this->dadosNota->tomador->logradouro = empty($dadosCidade[0]['logradouro'])? "Sem Rua" : $dadosCidade[0]['logradouro'];
        $this->dadosNota->tomador->bairro = empty($dadosCidade[0]['bairro'])? "Centro" : $dadosCidade[0]['bairro'];
        $this->dadosNota->tomador->complemento = $obj->tomserv_end_complemento;
        $this->dadosNota->tomador->cep = str_pad($this->nota->apenasNumeros($dadosCidade[0]['cep_exportacao']), 8, '0', STR_PAD_LEFT);

        $lista_itens = unserialize($obj->lista_itens);
        $this->dadosNota->itens->lista_itens = $this->normalizarItensNota($lista_itens);
    }

    /**
     * Summary of montaDadosNota
     * @param mixed $cobranca
     * @param mixed $nota
     * @return void
     */
    function montaDadosNota($cobranca, $nota) {

        $dadosCobranca = (object) $cobranca;
        $dadosCobranca->parametros = new stdClass();
        if(empty($cobranca['parametros']['endereco_cobranca'])) {
            $temEnderecoCobranca = false;
        } else {
            $temEnderecoCobranca = true;
            $dadosCobranca->parametros->endereco_cobranca = (object) $cobranca['parametros']['endereco_cobranca'];
        }
        $dadosCobranca->parametros->titular = (object) $cobranca['parametros']['titular'];
        $email = $this->nota->buscaContatosEmails($dadosCobranca->cli_id);
        if(count($email[0]) > 0) {
            $email = $this->validarEmail($email[0]);
        } else {
            $email = 'financeiro@ole.net.br';
        }

        $this->dadosNota = new stdClass();
        $this->dadosNota->nf = new stdClass();
        $this->dadosNota->prestador = new stdClass();
        $this->dadosNota->tomador = new stdClass();
        $this->dadosNota->itens = new stdClass();
        // Lista de itens da nota (array). Se vier um único item associativo, encapsula em array.
        $this->dadosNota->itens->lista_itens = $this->normalizarItensNota($nota);
        if (!$temEnderecoCobranca) {
            $dadosCidade = $this->nota->buscaDadosCidade($dadosCobranca->cep);
            $this->dadosNota->tomador->logradouro = empty($dadosCidade[0]['logradouro'])? "Sem Rua" : $dadosCidade[0]['logradouro'];
            $this->dadosNota->tomador->complemento = $dadosCobranca->complemento;
            $this->dadosNota->tomador->bairro = empty($dadosCidade[0]['bairro'])? "Centro" : $dadosCidade[0]['bairro'];
            $this->dadosNota->tomador->cep = str_pad($this->nota->apenasNumeros($dadosCidade[0]['cep_exportacao']), 8, '0', STR_PAD_LEFT);
        } else {
            $dadosCidade = $this->nota->buscaDadosCidade($dadosCobranca->parametros->endereco_cobranca->cep);

            $this->dadosNota->tomador->logradouro = empty($dadosCobranca->parametros->endereco_cobranca->logradouro)? "Sem Rua" : $dadosCobranca->parametros->endereco_cobranca->logradouro;
            $this->dadosNota->tomador->complemento = $dadosCobranca->parametros->endereco_cobranca->complemento;
            $this->dadosNota->tomador->bairro = empty($dadosCobranca->parametros->endereco_cobranca->bairro)? "Centro" : $dadosCobranca->parametros->endereco_cobranca->bairro;
            $this->dadosNota->tomador->cep = str_pad($this->nota->apenasNumeros($dadosCidade[0]['cep_exportacao']), 8, '0', STR_PAD_LEFT);
        }

        $telefone = "";
        if (isset($dadosCobranca->parametros->titular->tel_comercial)) {
            $telefone = $dadosCobranca->parametros->titular->tel_comercial;
        } else if (isset($dadosCobranca->parametros->titular->tel_residencial)) {
            $telefone = $dadosCobranca->parametros->titular->tel_residencial;
        } else if (isset($dadosCobranca->parametros->titular->tel_celular)) {
            $telefone = $dadosCobranca->parametros->titular->tel_celular;
        }

        
        $telefone = explode(",", $telefone);
        $tel = preg_replace('/(.+?)\(Ra[^.]*$|$/', '$1', $telefone[0]);
        $dddFone = explode(" ", $tel);
        $ddd = $this->nota->apenasNumeros($dddFone[0]);
        $fone = $this->nota->apenasNumeros($dddFone[1]);

        $this->dadosNota->nf->valor_total = $this->nota->formataMoeda($this->nota->apenasNumeros($nota['valor_final']), "");
        $this->dadosNota->nf->valor_total_nao_formatado = $nota['valor_final'];
        $this->dadosNota->nf->valor_desconto = "0,00";
        $this->dadosNota->nf->valor_ir = "0,00";
        $this->dadosNota->nf->valor_inss = "0,00";
        $this->dadosNota->nf->valor_contribuicao_social = "0,00";
        $this->dadosNota->nf->valor_rps = "0,00";
        $this->dadosNota->nf->valor_pis = "0,00";
        $this->dadosNota->nf->valor_cofins = "0,00";

        $this->dadosNota->prestador->cpfcnpj = $this->config['cnpj'];
        $this->dadosNota->prestador->cidade = $this->config['ctom'];

        if ($this->nota->ehCnpj($dadosCobranca->cliente_cpf_cnpj)) {
            $this->dadosNota->tomador->tipo = "J";
        } else {
            $this->dadosNota->tomador->tipo = "F";
        }
        $this->dadosNota->tomador->cpfcnpj = $this->nota->apenasNumeros($dadosCobranca->cliente_cpf_cnpj);
        $this->dadosNota->tomador->ie = $dadosCobranca->cliente_inscricao_estadual;
        $this->dadosNota->tomador->nome_razao_social = $dadosCobranca->cliente_nome;
        $this->dadosNota->tomador->sobrenome_nome_fantasia = $dadosCobranca->cliente_nome;
        $this->dadosNota->tomador->cidade = $dadosCidade[0]['cid_tom'];
        $this->dadosNota->tomador->email = $email;
        $this->dadosNota->tomador->ddd_fone_comercial = $ddd;
        $this->dadosNota->tomador->fone_comercial = $fone;
    }

    /**
     * Summary of enviarXml
     * @param mixed $xml
     * @param mixed $operation
     * @param mixed $cobranca
     * @param mixed $criarXMLRetorno
     * @param mixed $fake
     * @param mixed $tipo
     * @param mixed $decode
     * @return string
     */
    function enviarXml($xml, $operation, $cobranca, $criarXMLRetorno = false, $fake = false, $tipo = 1, $decode = true) {

        if ($fake) {
            return $this->sendFake($xml, $operation, 1, $cobranca, $criarXMLRetorno, $tipo, $decode);
        }
        return $this->send($xml, $operation, 1, $cobranca, $criarXMLRetorno);
    }

    /**
     * Send message to webservice
     * @param string $operation
     * @param string $tipoDeRetorno 0 - soap , 1 - array,  2 - json 
     * @param array $cobranca
     * @param bool $criarXMLRetorno 
     * @param string $xml
     * @param int $c : contador de tentativas
     * @return string|object|unset XML response from webservice
     */
    public function send($xml, $operation, $tipoDeRetorno = 1, $cobranca = "", $criarXMLRetorno = false, $c = 0) {

        $retorno = null;
        try {
            $retorno = $this->resend($xml, $operation, $tipoDeRetorno, $cobranca, $criarXMLRetorno, $c++);

            return $retorno;
        } catch (Exception $exc) {
            if ($exc->getMessage() == "ERROR_SESSAO") {

                sleep(5);
                $retorno = $this->send($xml, $operation, $tipoDeRetorno, $cobranca, $criarXMLRetorno, $c++);
            }
        }

        return $retorno;
    }

    /**
     * Send message to webservice
     * @param string $operation
     * @param string $tipoDeRetorno : 0 - soap
     *                                1 - array
     *                                2 - json 
     * @param array $cobranca
     * @param bool $criarXMLRetorno 
     * @param string $xml
     * @param int $c : contador de tentativas
     * @throws Exception
     * @return string XML response from webservice
     */
    public function resend($xml, $operation, $tipoDeRetorno = 1, $cobranca = "", $criarXMLRetorno = false, $c = 0) {

        $URL = 'https://nfse-saojose.atende.net/atende.php?pg=rest&service=WNERestServiceNFSe&cidade=padrao';
        $arquivo = $this->criarXMLDeRetorno($xml, $operation, $cobranca, "log/envio");
        $cfile = curl_file_create($arquivo, 'text/xml', 'arquivo');
        $xmldata = array('xml' => $cfile);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $URL);

        curl_setopt_array($curl, Array(
            CURLOPT_POST => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST'
        ));

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode($this->config['usuario'] . ':' . $this->config['senha']),
            'Content-Type: multipart/form-data'
        ));

        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);
        if (curl_errno($curl)) {
            $curlErrno = curl_errno($curl);
            $curlError = curl_error($curl);

            return false;
        } else {

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $reSend = false;

            if ($httpCode == 200) {
                $resp = explode('<?xml', $response);
                $newResponse = "<?xml" . $resp[1];
                $response = $newResponse;
            } else if ($httpCode == 403) {
                $j1 = explode("Content-Type: application/json", $response);
                $response = trim($j1[1]);
                $json = json_decode($response);
            } else if ($httpCode == 401) {
                if (preg_match("/error_container/i", $response)) {
                    $json = new stdClass();
                    $json->retorno = new stdClass();
                    $json->retorno->msg = "Sua sessão foi encerrada por inatividade, por favor realize login novamente";
                    $json->retorno->sis = "EST";
                    $json->retorno->code = 402;
                    $reSend = true;
                } else {
                    $j1 = explode("Content-Type: application/json", $response);
                    $json = json_decode(trim($j1[1]));
                }
            } else {
                $json = new stdClass();
                $json->retorno = new stdClass();
                $json->retorno->msg = "Parâmetros Inválidos";
                $json->retorno->sis = "EST";
                $json->retorno->code = 400;
                $reSend = true;
            }

            curl_close($curl);
            if ($criarXMLRetorno) {
                $this->criarXMLDeRetorno($response, "retorno_" . $operation, $cobranca, "log/retorno");
            } else {
                if (file_exists($arquivo)) {
                    unlink($arquivo);
                }
            }

            if ($reSend && $c < 2) {
                $retorno = "ERROR_SESSAO";
                throw new Exception($retorno, 2);
            } else {
                if (!empty($json->retorno->code) && $json->retorno->code != 200) {
                    $retorno['retorno'] = $json;
                    $retorno['erro'] = true;
                } else {
                    $retorno = $this->tipoDeRetorno($response, $tipoDeRetorno, false);
                }
            }

            return $retorno;
        }
    }

    /**
     * Send message to webservice
     * @param string $message
     * @param string $tipoDeRetorno : 0 - soap 
     *                                1 - array
     *                                2 - json 
     * @param bool $criarXMLRetorno 
     * @param int $tipo : 1 - envio , 
     *                    2 - retorno, 
     *                    3 - cancela nota,
     *                    4 - retorno cancelado,
     * @return string XML response from webservice
     */
    public function sendFake($message, $operation = "", $tipoDeRetorno = 1, $cobranca = "", $criarXMLRetorno = false, $tipo = 1, $decode = true) {

        switch ($tipo) {
            case 1:
                $caminhoXML = dirname(__DIR__, 1) . '\xml_retorno_fake\sjsc\emissao.xml';
                break;
            case 2:
                $caminhoXML = dirname(__DIR__, 1) . '\xml_retorno_fake\sjsc\retorno.xml';
                break;
            case 3:
                $caminhoXML = dirname(__DIR__, 1) . '\xml_retorno_fake\sjsc\cancelamento.xml';
                break;
            case 4:
                $caminhoXML = dirname(__DIR__, 1) . '\xml_retorno_fake\sjsc\retorno_cancelado.xml';
                break;
            default:
                $caminhoXML = dirname(__DIR__, 1) . '\xml_retorno_fake\sjsc\retorno.xml';
                break;
        }
        $xml = file_get_contents($caminhoXML);

        if ($criarXMLRetorno) {
            $this->criarXMLDeRetorno($message, $operation, $cobranca);
        }

        return $this->tipoDeRetorno($xml, $tipoDeRetorno, $decode);
    }

    /**
     * Summary of criarXMLDeRetorno
     * @param mixed $conteudo
     * @param mixed $nomeArquivo
     * @param mixed $cobranca
     * @param mixed $local
     * @return string
     */
    function criarXMLDeRetorno($conteudo, $nomeArquivo, $cobranca = "", $local = "xml_retorno_fake") {

        $dataAgora = new \DateTime();
        $dataHorario = $dataAgora->format('Y-m-d-H-i-s-u');
        $file = dirname(__DIR__, 1) . "/{$local}/{$nomeArquivo}_{$dataHorario}{$cobranca}.xml";
        $fp = fopen($file, 'w+b');
        fwrite($fp, $conteudo);
        fclose($fp);

        return $file;
    }

    /**
     * 
     * @param string $retorno
     * @param mixed $tipoDeRetorno : 0 - soap 
     *                              1 - xmlToArray
     *                              2 - xmlToJson
     *                              3 - jsonDecode
     * @return string, array, json
     */
    public function tipoDeRetorno($retorno, $tipoDeRetorno, $utf8Decode = false) {

        switch ($tipoDeRetorno) {

            case 0:
                return $retorno;

            case 1:
                return $this->retornoParaArray($retorno, $utf8Decode);

            case 2:
                return $this->retornoParaJson($retorno, $utf8Decode);

            case 3:
                return json_encode($retorno);

            default:
                return $retorno;
        }
    }

    /**
     * Converte response para json
     * @param string $response xml soap
     * @param bool $utf8Decode
     * @return mixed  json
     */
    function retornoParaJson($response, $utf8Decode) {

        return json_encode($this->retornoParaArray($response, $utf8Decode));
    }

    /**
     * Converte response para array
     * @param string $response xml soap
     * @param bool $utf8Decode
     * @return array
     */
    function retornoParaArray($response, $utf8Decode = false) {

        if ($response) {
            if ($utf8Decode) {
                return simplexml_load_string(utf8_decode($response));
            }
            return simplexml_load_string($response);
        }
        return false;
    }

    /**
     * 
     * @param int $coaId
     * @return int
     * @throws Exception
     */
    function insereDadosNota($coaId) {

        $dataAgora = new DateTime('now');
        $dataEmissao = $dataAgora->format('Y-m-d h:i:s');
        $telefone = $this->dadosNota->tomador->ddd_fone_comercial . " " . $this->dadosNota->tomador->fone_comercial;
        $lista_itens = addslashes(serialize($this->dadosNota->itens->lista_itens));
        $complemento = addslashes($this->dadosNota->tomador->complemento);
        $bairro = addslashes($this->dadosNota->tomador->bairro);
        $nome_razao_social = addslashes($this->dadosNota->tomador->nome_razao_social);
        $logradouro = addslashes($this->dadosNota->tomador->logradouro);

        $q = "INSERT INTO tab_notas (
                    dataEmissao , servico_valores_valorServicos , servico_valores_baseCalculo ,
                    tomserv_idetom_cpfCnpj_cnpj , tomserv_end_endereco ,
                    tomserv_inscricaoMunicipal , tomserv_razaoSocial ,
                    tomserv_end_bairro , tomserv_end_complemento ,
                    tomserv_end_cep ,tomserv_end_codigoMunicipio , tomserv_contato_telefone , 
                    tomserv_contato_email , nota_gerada , coa_id , status_id , lista_itens)
                VALUES(
                    '{$dataEmissao}' , '{$this->dadosNota->nf->valor_total}' , '{$this->dadosNota->nf->valor_total}' , 
                    '{$this->dadosNota->tomador->cpfcnpj}' , '{$logradouro}' , 
                    '{$this->dadosNota->tomador->ie}' , '{$nome_razao_social}' ,
                    '{$bairro}' , '{$complemento}' , 
                    '{$this->dadosNota->tomador->cep}' , '{$this->dadosNota->tomador->cidade}' , '{$telefone}' ,
                    '{$this->dadosNota->tomador->email}' , 1 , '{$coaId}' , 3 , '{$lista_itens}');";

        $this->bancodedados->query($q);

        return $this->bancodedados->ultimoIdInserido();
    }

    /**
     * 
     * @param array|object $nota
     * @param bool $atualizarNota
     * @param bool $unico - apenas um unico log
     * @return void
     */
    function insereLog($nota, $atualizarNota = true, $unico = false) {

        $notaId = $nota->idNota;
        if ($unico) {
            $this->inserindoLog($notaId, $nota->erro);
        } else {
            $codigos = $nota->dados->mensagem->codigo;
            if (!empty($codigos)) {
                foreach ($codigos as $codigo) {
                    $this->inserindoLog($notaId, $codigo);
                }
            }
        }

        if ($atualizarNota) {
            $this->atualizaDadosNota($notaId, null, 5);
        }
    }

    /**
     * 
     * @param int $notaId
     * @param string $codigo
     * @return void
     */
    function inserindoLog($notaId, $codigo) {

        $codigo = str_replace("'", "`", $codigo);
        $codText = explode("-", $codigo);
        $retorno = utf8_decode($codigo);
        $cod = $this->nota->apenasNumeros(utf8_decode(trim($codText[0])));
        $msg = !empty($codText[1]) ? utf8_decode(trim($codText[1])) : $cod;
        $date = date('Y-m-d H:i:s');
        $q = "INSERT INTO tab_notas_log(nota,retorno,cod, msg, data) VALUES ('$notaId', '$retorno', '$cod', '$msg', '$date')";
        $this->bancodedados->query($q);
    }

    /**
     * 
     * @param int $id
     * @param array|object|null $dados
     * @param int $status : 1 - Cancelada 
     *                       2 - Aguardando 
     *                       3 - Enviado 
     *                       4 - Emitida 
     *                       5 - Erro
     * @param int $notaGerada : 1 - gerado 
     *                           0 - não gerado
     */
    function atualizaDadosNota($id = 0, $dados = null, $status = 3, $notaGerada = 0) {

        if ($id > 0) {
            $dados = (object) $dados;
            if(!empty($dados->nf)) {
                $dados = $dados->nf;
            }
            if (empty($dados->cod_verificador_autenticidade)) {
                $q = "UPDATE tab_notas SET 
                        nota_gerada = $notaGerada, 
                        status_id = {$status} WHERE id = {$id}";
            } else {
                $q = "UPDATE tab_notas SET 
                        identificacaoRps_numero = {$id},
                        nota_gerada = 1, 
                        status_id = 4, 
                        serie_nfse = '{$dados->serie_nfse}',
                        link_nfse = '{$dados->link_nfse}',
                        data_nfse = '{$dados->data_nfse} {$dados->hora_nfse}', 
                        numero = '{$dados->numero_nfse}',
                        codigoVerificacao = '{$dados->cod_verificador_autenticidade}',
                        protocolo = '{$dados->cod_verificador_autenticidade}' WHERE id = '{$id}'";
            }

            $this->bancodedados->query($q);
        }
    }

    /**
     * 
     * @param int $notaId
     * @return array
     * @throws Exception
     */
    function buscaNota($notaId) {

        $nota = array();
        $q = "SELECT * FROM tab_notas WHERE id = '$notaId' OR numero = '$notaId'";
        $query = $this->bancodedados->query($q);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $nota[] = $dados;
        }

        return $nota[0];
    }

    /**
     * 
     * @param array $cobranca
     * @return array
     * @throws Exception
     */
    function buscaNotaPelaCobranca($cobranca) {

        $nota = array();
        $q = "SELECT * FROM tab_notas WHERE coa_id = '{$cobranca}'";
        $query = $this->bancodedados->query($q);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $nota[] = $dados;
        }

        return $nota[0];
    }

    /**
     * 
     * @param int $idNota
     * @param int $gerado : 1 - gerado 
     *                       0 - não gerado
     */
    function atualzaNotaCancelada($idNota, $gerado = 1) {

        $q = "UPDATE tab_notas SET nota_gerada = $gerado, status_id = 1 WHERE id = '$idNota'";
        $this->bancodedados->query($q);
    }

    /**
     * 
     * @param int $notaId
     * @return void
     * @throws Exception
     */
    function registrarNota($notaId) {

        $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
        $faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
        $nota = (object) $this->buscaNota($notaId);
        
        $comentario = "<b>Ação:</b> Ciclo aprovado";
        $faturas_lotes->geraNotaCobranca($nota->coa_id, 1, $comentario, true);
        $cobrancas->buscaDadosItem($nota->coa_id);
        $dados = $this->buscaNotaPelaCobranca($nota->coa_id);
        $dados["numero"] = preg_replace("/[^0-9]/", "", $dados["numero"]);
        $dados["emissao"] = $dados["dataEmissao"];
        $dados["valor"] = (int) preg_replace("/[^0-9]/", "", $dados["servico_valores_baseCalculo"]);
        $dados["tipo"] = 3;

        if ($cobrancas->dados_item["fatura"]) {
            $this->bancodedados->query("
                    UPDATE
                            tab_faturas
                    SET
                            fat_data_emissao = '{$dados["emissao"]}',
                            fat_numero = {$dados["numero"]},
                            fat_valor = {$dados["valor"]},
                            fap_id = {$dados["tipo"]}
                    WHERE
                            fat_id = {$cobrancas->dados_item["fatura"]["id"]} AND fap_id = 3
                    ");
            
        } else {
            if($this->ehRegistravelNota($cobrancas)) {
                $vencimento = substr($cobrancas->dados_item["data_vencimento"], 6, 4) . "-" . substr($cobrancas->dados_item["data_vencimento"], 3, 2) . "-" . substr($cobrancas->dados_item["data_vencimento"], 0, 2); ;

                $this->bancodedados->query("
                        INSERT INTO
                                tab_faturas
                        SET
                                fat_codigo = '{$cobrancas->dados_item["codigo"]}',
                                fat_data_emissao = '{$dados["emissao"]}',
                                fat_data_vencimento = '{$vencimento}',
                                fat_numero = {$dados["numero"]},
                                fat_valor = {$dados["valor"]},
                                fap_id = {$dados["tipo"]},
                                cli_id = {$cobrancas->dados_item["cliente_id"]},
                                coa_id = {$cobrancas->dados_item["id"]},
                                emp_id = 1");

                $fatura_id = $this->bancodedados->ultimoIdInserido();
                $this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_id = {$cobrancas->dados_item["id"]}");
                if ($cobrancas->dados_item["coa_pai"]) {
                    $this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_id = {$cobrancas->dados_item["coa_pai"]} AND fat_id IS NULL");
                    $this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_pai = {$cobrancas->dados_item["coa_pai"]} AND fat_id IS NULL");
                } else {
                    $this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_pai = {$cobrancas->dados_item["id"]} AND fat_id IS NULL");
                }
            }
        }
        
        $query = $this->bancodedados->query("SELECT * FROM tab_faturas_lotes WHERE fls_id = 1");
        while (($lotes = $this->bancodedados->resultadoArray($query)) != false) {
            $queryFaturaLotes = "
                    UPDATE 
                            tab_faturas_lotes 
                    SET 
                            fal_data_aprovacao = NOW(), 
                            fls_id = 3 
                    WHERE 
                            fal_id = {$lotes['fal_id']}";
            $this->bancodedados->query($queryFaturaLotes);
        }
        
        $q = "SELECT ajf_id id FROM tab_cobrancas_detalhes WHERE coa_id = {$cobrancas->dados_item["id"]} AND ajf_id IS NOT NULL";
        $query = $this->bancodedados->query($q);
        while (($ajuste = $this->bancodedados->resultadoArray($query)) != false) {
            $this->bancodedados->query("
                    UPDATE 
                        tab_ajustes_financeiros 
                    SET 
                        ajf_nf_numero = {$dados["numero"]},
                        ajf_nf_emissao = '{$dados["emissao"]}',
                        ajf_nf_valor = {$dados["valor"]}
                    WHERE 
                        ajf_id = {$ajuste['id']}");
        }
        
        $comentaio = "NFS-e registrada";
        $usuario = empty($_SESSION["usuario"]) ? NULL : $_SESSION["usuario"];
        $cobrancas->registraComentario($cobrancas->dados_item["id"], $comentaio , $usuario);
    }

    /** 
     * 
     * @param array|object $cobrancas
     * @return bool
     * @throws Exception
     * @return boolean
     */
    function ehRegistravelNota($cobrancas) {
		
        if ($cobrancas->dados_item["fatura_id"] && !in_array($cobrancas->dados_item["fatura"]["tipo_id"], array(3, 4))) return false;
		
        return true;

    }
    
    /**
     * Calcula IBS + CBS a partir do valor final pago pelo cliente
     *
     * @param float $valorFinal   Valor total pago pelo cliente
     * @param float $pIBSUF       Aliquota IBS UF (ex: 0.15)
     * @param float $pIBSMun      Aliquota IBS Municipio (ex: 0.03)
     * @param float $pCBS         Aliquota CBS (ex: 0.09)
     * @param array $itens        Itens com valor bruto (ex: [['descricao'=>'HBO','valor'=>40]])
     *
     * @return array
    */
    /**
     * Normaliza itens da nota para sempre retornar uma lista de itens válidos.
     * Evita o erro de expandir as propriedades de um único item (stdClass) como vários itens.
     *
     * @param mixed $itens
     * @return array
     */
    function normalizarItensNota($itens) {

        if (empty($itens) && $itens !== 0 && $itens !== '0') {
            return [];
        }

        if (is_object($itens)) {
            $asArray = (array) $itens;
            if ($this->ehItemNota($asArray)) {
                return [$asArray];
            }
            $itens = get_object_vars($itens);
        }

        if (!is_array($itens)) {
            return [];
        }

        if ($this->ehItemNota($itens)) {
            return [$itens];
        }

        $normalizados = [];
        foreach ($itens as $item) {
            $itemArray = is_object($item) ? (array) $item : $item;
            if (is_array($itemArray) && $this->ehItemNota($itemArray)) {
                $normalizados[] = $itemArray;
            }
        }

        return $normalizados;
    }

    /**
     * @param array $item
     * @return bool
     */
    function ehItemNota($item) {

        return is_array($item) && (
            array_key_exists('valor_final', $item) ||
            array_key_exists('dados', $item) ||
            array_key_exists('items', $item)
        );
    }

    function calcularIBSCBS(float $valorFinal, float $pIBSUF, float $pIBSMun, float $pCBS, $itens = []) {

        $itens = $this->normalizarItensNota($itens);

        // 1) Padroniza alíquotas (garante formato decimal, ex: 10% vira 0.10)
        $pIBSUFDec  = $pIBSUF > 1 ? $pIBSUF / 100 : $pIBSUF;
        $pIBSMunDec = $pIBSMun > 1 ? $pIBSMun / 100 : $pIBSMun;
        $pCBSDec    = $pCBS > 1 ? $pCBS / 100 : $pCBS;

        $aliqTotalDec = $pIBSUFDec + $pIBSMunDec + $pCBSDec;
        if ($aliqTotalDec <= -1) {
            $aliqTotalDec = 0;
        }

        // 2) Base de cálculo principal (vBC)
        // Formula "Por fora": vBC = Valor Final / (1 + Alíquota Total)
        $vBC = $valorFinal / (1 + $aliqTotalDec);

        // 3) Cálculo dos impostos
        $vDifUF  = $vBC * $pIBSUFDec;
        $vDifMun = $vBC * $pIBSMunDec;
        $vIBSTot = $vDifUF + $vDifMun;
        $vDifCBS = $vBC * $pCBSDec;

        // 4) Recálculo e rateio por item (se houver itens)
        $itensReCalculados = [];

        foreach ($itens as $itemArray) {
            $valorFinalItem = isset($itemArray['valor_final']) ? $itemArray['valor_final']
                : (isset($itemArray['valor_total']) ? $itemArray['valor_total']
                : (isset($itemArray['vItem']) ? $itemArray['vItem'] : 0));

            $valorBCItem = $valorFinalItem / (1 + $aliqTotalDec);

            $itemArray['valor_bruto']      = $valorFinalItem;
            $itemArray['valor_tributavel'] = round($valorBCItem, 2);
            $itemArray['vIBSUF']           = round($valorBCItem * $pIBSUFDec, 2);
            $itemArray['vIBSMun']          = round($valorBCItem * $pIBSMunDec, 2);
            $itemArray['vCBS']             = round($valorBCItem * $pCBSDec, 2);

            $itensReCalculados[] = $itemArray;
        }

        // 5) Retorno formatado
        return [
            'vBC'          => round($vBC, 2),
            'pIBSUF'       => $pIBSUFDec * 100,
            'pIBSMun'      => $pIBSMunDec * 100,
            'pCBS'         => $pCBSDec * 100,

            'pRedAliqUF'   => 0,
            'pRedAliqMun'  => 0,
            'pRedAliqCBS'  => 0,

            'pAliqEfetUF'  => $pIBSUFDec * 100,
            'pAliqEfetMun' => $pIBSMunDec * 100,
            'pAliqEfetCBS' => $pCBSDec * 100,

            'totCIBS' => [
                'vIBSTot' => round($vIBSTot, 2),
                'vDifUF'  => round($vDifUF, 2),
                'vDifMun' => round($vDifMun, 2),
                'vDifCBS' => round($vDifCBS, 2),
                'vTotNF'  => round($valorFinal, 2)
            ],
            'itens' => $itensReCalculados
        ];
    }

}
