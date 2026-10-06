<?php


class boletosclass extends loadclass {
    
    public $returnClass = array();
    public $urlClass = array();
    public $idParceiro = null;
    
    function __construct($idParceiro='') {
        $this->returnClass['retorno_status'] = false;
        $this->idParceiro = $idParceiro;
        
        $this->urlClass = $this->getParametros();
        if ( trim( $this->urlClass[1]) == '') {
            $this->returnClass['error'] = 'Nenhum método definido!';
        }
        else {
            if ( method_exists(boletosclass, $this->urlClass[1]) ) { eval(" boletosclass::".$this->urlClass[1]."(); "); }
            else    { $this->returnClass['error'] = "Método '".$this->urlClass[1]."' inválido!"; }
        }
        return $this->returnClass;
    }
    
    
    
    function listar() {
        // Verifica valor da pesquisa
        $idCliente = intval(@$this->urlClass[2]);
        if ($idCliente === 0) {
            $this->returnClass['error'] = "ID do cliente não informado!";
            return;
        }
        $Format = new formats();
        
        // Busca o Cliente do ID
        $this->conectaDB();
        $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id={$idCliente} and clo_id = {$this->idParceiro} and (cls_id='1' OR cls_id='2')");
        $dadosCliente = $this->db->resultadoArray($queryCliente);
        
        if (!isset($dadosCliente['cli_id'])) {
            $this->returnClass['error'] = "ID do cliente não localizado!";
            return;
        }
        
        $this->returnClass['id_cliente'] = $dadosCliente['cli_id'];
        $this->returnClass['nome'] = $this->textCharsetOutput( $dadosCliente['cli_nome'] );
        $this->returnClass['cpf_cnpj'] = $dadosCliente['cli_cpf_cnpj'];
        

        // Monta listagem dos boletos
        
        $this->conectaDB();
        require_once '_modulos/cobrancas/cobrancas.class.inc.php';
        $intranetCobranca = new cobrancas($this->db, null, false);
        $dadosBoletos = $intranetCobranca->buscaClientesCobrancas( $idCliente );
        $cobrancas = array();
        foreach ($dadosBoletos AS $v) {
            $dados["id"] = ($v['coa_pai']!=null?$v['coa_pai']:$v["id"]);
            $dados["codigo"] = $v["codigo"];
            $dados["formato"] = $this->textCharsetOutput( $v["formato_nome"] );
            
            $dados["referente"] = $this->buscaCobrancaCompetencia($v["id"], $v["tipo_id"]);
            
            //$dados["datas"]["geracao"] = $v["data_geracao"];
            $dados["datas"]["vencimento"] = substr( strip_tags($v["data_vencimento"]), 0, 10);
            $dados["datas"]["pagamento"] = $v["data_pagamento"];
            
            $ExtraiValor = strip_tags($v["valor_original"]);
            $ExtraiValor = substr($ExtraiValor, 0, (strpos($ExtraiValor,'(')===false?strlen($ExtraiValor):strpos($ExtraiValor,'(') ) );
            
            
            $dados["valores"]["bonificacao"] = strip_tags($v["valor_bonificacao"]);
            $dados["valores"]["valor"] = trim($ExtraiValor);
            $dados["nosso_numero"] = $v["nosso_numero"];
            
            
            $parametroBoleto = $this->db->resultadoArray($this->db->query("SELECT coa_parametros as parametros FROM tab_cobrancas WHERE coa_id={$v["id"]}"));
            
            // Extrai a linha_digitavel dos parametros
            $i = strpos( $parametroBoleto['parametros'], 'linha_digitavel";s:54:"' );
            if ($i !== false) {
                $tmp = substr($parametroBoleto['parametros'], $i+23);
                $i = strpos( $tmp, '";' );
                $dados["linha_digitavel"] = substr($tmp, 0, $i);
            }
            else $dados["linha_digitavel"] = '';
            
            $dados["status"] = ($v['status_id']==1?'Em Aberto':'Pago');//  str_replace(" (", "<br>(", $v["status"]);
            
            $cobrancas[] = $dados;
            unset($dados);
        }
        
        if (count($cobrancas) > 0) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['boletos'] = $cobrancas;
        }
        else {
            unset( $this->returnClass);
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = 'Nenhum boleto do cliente foi encontrado!';
        }
    }
    
    
    function buscacpfcnpj() {
        // Verifica valor da pesquisa
        $CPF_CNPJ = trim(@$this->urlClass[2]);
        if (strlen($CPF_CNPJ) < 14) {
            $CPF_CNPJ .= '/'.trim(@$this->urlClass[3]);
            $Status = trim(@$this->urlClass[4]);
        }
        else {
            $Status = trim(@$this->urlClass[3]);
        }
        
        if ($CPF_CNPJ === '' || empty($CPF_CNPJ)) {
            $this->returnClass['error'] = "CPF/CNPJ não informado!";
            return;
        }
        
        //$Status = trim(@$this->urlClass[3]);
        if ($Status != '') {
            switch ($Status) {
                case 'Aberto':  break;
                case 'Pago':    break;
                default:
                    $this->returnClass['error'] = "Status informado é inválido!";
                    return;
                break;
            }
            
        }
        
        
        //$Format = new formats();
        
        // Busca o Cliente do ID
        $this->conectaDB();
        $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_cpf_cnpj='{$CPF_CNPJ}' and clo_id = {$this->idParceiro} and (cls_id='1' OR cls_id='2')");
        $dadosCliente = $this->db->resultadoArray($queryCliente);
        
        if (!isset($dadosCliente['cli_id'])) {
            $this->returnClass['error'] = "CPF/CNPJ não localizado!";
            return;
        }
        
        $this->returnClass['id_cliente'] = $dadosCliente['cli_id'];
        $this->returnClass['nome'] = $this->textCharsetOutput( $dadosCliente['cli_nome'] );
        $this->returnClass['cpf_cnpj'] = $dadosCliente['cli_cpf_cnpj'];
        

        // Monta listagem dos boletos
        
        $this->conectaDB();
        require_once '_modulos/cobrancas/cobrancas.class.inc.php';
        $intranetCobranca = new cobrancas($this->db, null, false);
        $dadosBoletos = $intranetCobranca->buscaClientesCobrancas( $dadosCliente['cli_id'] );
        $cobrancas = array();
        foreach ($dadosBoletos AS $v) {
            if (
                ($Status == 'Aberto' && $v['status_id'] == 1) ||
                ($Status == 'Pago' && $v['status_id'] != 1) ||
                ($Status == '')
                ) {
            
                $dados["id"] = ($v['coa_pai']!=null?$v['coa_pai']:$v["id"]);
                $dados["codigo"] = $v["codigo"];
                $dados["formato"] = $this->textCharsetOutput( $v["formato_nome"] );
                
                $dados["referente"] = $this->buscaCobrancaCompetencia($v["id"], $v["tipo_id"]);
                
                //$dados["datas"]["geracao"] = $v["data_geracao"];
                $dados["datas"]["vencimento"] = substr( strip_tags($v["data_vencimento"]), 0, 10);
                $dados["datas"]["pagamento"] = $v["data_pagamento"];
                
                $ExtraiValor = strip_tags($v["valor_original"]);
                $ExtraiValor = substr($ExtraiValor, 0, (strpos($ExtraiValor,'(')===false?strlen($ExtraiValor):strpos($ExtraiValor,'(') ) );
                
                
                $dados["valores"]["bonificacao"] = strip_tags($v["valor_bonificacao"]);
                $dados["valores"]["valor"] = trim($ExtraiValor);
                $dados["nosso_numero"] = $v["nosso_numero"];
                
                
                $parametroBoleto = $this->db->resultadoArray($this->db->query("SELECT coa_parametros as parametros FROM tab_cobrancas WHERE coa_id={$v["id"]}"));
                
                // Extrai a linha_digitavel dos parametros
                $i = strpos( $parametroBoleto['parametros'], 'linha_digitavel";s:54:"' );
                if ($i !== false) {
                    $tmp = substr($parametroBoleto['parametros'], $i+23);
                    $i = strpos( $tmp, '";' );
                    $dados["linha_digitavel"] = substr($tmp, 0, $i);
                }
                else $dados["linha_digitavel"] = '';
                
                $dados["status"] = ($v['status_id']==1?'Em Aberto':'Pago');//  str_replace(" (", "<br>(", $v["status"]);
                
                $cobrancas[] = $dados;
                unset($dados);
            }
        }
        
        if (count($cobrancas) > 0) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['boletos'] = $cobrancas;
        }
        else {
            unset( $this->returnClass);
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = 'Nenhum boleto do cliente foi encontrado!';
        }
    }
    
    function buscacontrato() {
        // Verifica valor da pesquisa
        
        $idContrato = intval( @$this->urlClass[2] );
        if ($idContrato == 0) {
            $this->returnClass['error'] = "ID do contrato nao informado ou invalido!";
            return;
        }
        
        define('DIR_RAIZ', '../../');
        define('DIR_GLOBAL', '../../');
        
        $this->conectaDB();
        require_once '../_modulos/contratos/contratos.class.inc.php';
        $intranetContratos = new contratos($this->db, null, false);
        
        $dadosFindCli = $intranetContratos->buscaDadosContrato($idContrato);
        if ($dadosFindCli['cliente'] > 0) {
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dadosFindCli['cliente']."' and clo_id = '".$this->idParceiro."' and (cls_id='1' OR cls_id='2' OR cls_id='3')");
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do contrato não pertence ao seu cliente!";
                return;
            }
        }
        else {
            $this->returnClass['error'] = "ID do contrato não localizado!";
            return;
        }   
        
        
         // Busca o Cliente do ID
        
        $this->returnClass['id_cliente'] = $dadosCliente['cli_id'];
        $this->returnClass['nome'] = $this->textCharsetOutput( $dadosCliente['cli_nome'] );
        $this->returnClass['cpf_cnpj'] = $dadosCliente['cli_cpf_cnpj'];
        
        
        // Monta listagem dos boletos
        
        $this->conectaDB();
        require_once '_modulos/cobrancas/cobrancas.class.inc.php';
        $intranetCobranca = new cobrancas($this->db, null, false);
        $dadosBoletos = $intranetCobranca->buscaClientesCobrancasContrato( $idContrato );
        
        //print_r($dadosBoletos);
        
       
        
        
        $cobrancas = array();
        foreach ($dadosBoletos AS $v) {
                $dados["id"] = ($v['coa_pai']!=null?$v['coa_pai']:$v["id"]);
                $dados["codigo"] = $v["codigo"];
                $dados["formato"] = $this->textCharsetOutput( $v["formato_nome"] );
                
                //$this->returnClass['error'] = "Testando5!";
                //return;
                
                
                $dados["referente"] = $this->buscaCobrancaCompetencia($v["id"], $v["tipo_id"]);
                
                $dados["datas"]["vencimento"] = substr( strip_tags($v["data_vencimento"]), 0, 10);
                $dados["datas"]["pagamento"] = $v["data_pagamento"];
                
                $ExtraiValor = strip_tags($v["valor_original"]);
                $ExtraiValor = substr($ExtraiValor, 0, (strpos($ExtraiValor,'(')===false?strlen($ExtraiValor):strpos($ExtraiValor,'(') ) );
                
                
                $dados["valores"]["bonificacao"] = strip_tags($v["valor_bonificacao"]);
                $dados["valores"]["valor"] = trim($ExtraiValor);
                $dados["nosso_numero"] = $v["nosso_numero"];
                
                
                $parametroBoleto = $this->db->resultadoArray($this->db->query("SELECT coa_parametros as parametros FROM tab_cobrancas WHERE coa_id={$v["id"]}"));
                
                // Extrai a linha_digitavel dos parametros
                $i = strpos( $parametroBoleto['parametros'], 'linha_digitavel";s:54:"' );
                if ($i !== false) {
                    $tmp = substr($parametroBoleto['parametros'], $i+23);
                    $i = strpos( $tmp, '";' );
                    $dados["linha_digitavel"] = substr($tmp, 0, $i);
                }
                else $dados["linha_digitavel"] = '';
                
                $dados["status"] = ($v['status_id']==1?'Em Aberto':'Pago');//  str_replace(" (", "<br>(", $v["status"]);
                
                $cobrancas[] = $dados;
                unset($dados);
        }
        
        if (count($cobrancas) > 0) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['boletos'] = $cobrancas;
        }
        else {
            unset( $this->returnClass);
            $this->returnClass['retorno_status'] = false;
            //$this->returnClass['error'] = 'Nenhum boleto do cliente foi encontrado!';
        }
    }
    
    function buscaCobrancaCompetencia($cobranca_id, $tipo_id) {
        $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
        if ($tipo_id == 3) { return "Semestral"; } elseif ($tipo_id == 4) { return "Anual"; }
        $Format = new formats();
        
        $this->conectaDB();
        $query = $this->db->query("
			SELECT DISTINCT
				DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia,
                cod_descricao,
                cod_valor_final
			FROM
				tab_cobrancas_detalhes
			WHERE
				coa_id = $cobranca_id AND
				cod_competencia_inicio IS NOT NULL
			ORDER BY
				cod_competencia_inicio");

        $competencias = array();
        $periodos = array();
        
        while (($v = $this->db->resultadoArray($query)) != false) {
            $tmp = explode('/', $v["competencia"]);
            $itens['descricao'] = $this->textCharsetOutput( $v['cod_descricao'] );
            $itens['valor'] = $Format->FormatFloatSQL2BR($v['cod_valor_final']);
            $periodos[$meses[intval($tmp[0])].'/'.$tmp[1]][] = $itens;
            unset($tmp);
        }
        
        $pos = 0;
        foreach ($periodos AS $k => $v) {
            $competencias[$pos]['periodo'] = $k;
            $competencias[$pos]['itens'] = $v;
            $pos++;
        }
        return $competencias;
    }
    
    
    function visualizar() {
        // Verifica ID do boleto
        if (intval(@$this->urlClass[2]) === 0 || empty($this->urlClass[2])) {
            $this->returnClass['error'] = "ID do boleto não informado!";
            return;
        }
        
        // Procura boleto
        $this->conectaDB();
        $queryBoleto = $this->db->query("
            SELECT 
                tab_cobrancas.coa_id,tab_cobrancas.coa_pai,tab_cobrancas.cot_id,tab_cobrancas.cli_id,tab_cobrancas.coa_data_vencimento,tab_cobrancas.coa_valor_original,
                tab_cobrancas_formatos.cof_nome AS formato_nome,
                tab_cobrancas_status.cot_id AS status_id,
                IF(tab_cobrancas_lotes.cla_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6), tab_cobrancas_status.cot_nome, CONCAT(tab_cobrancas_status.cot_nome, '<br>(Lote Inativo)')) AS status  
            FROM 
                tab_cobrancas 
                INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
                INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id) 
				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
            WHERE tab_cobrancas.coa_id='".intval($this->urlClass[2]) ."'");
        $dadosBoleto = $this->db->resultadoArray($queryBoleto);
        if (!isset($dadosBoleto['coa_id'])) {
            $this->returnClass['error'] = "ID do boleto não localizado!";
            return;
        }
        else {
            //Verifica se o boleto percente a um cliente do parceiro
            $queryCliente = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dadosBoleto['cli_id']."' and clo_id = '".$this->idParceiro."' and (cls_id='1' OR cls_id='2')");
            $dadosCliente = $this->db->resultadoArray($queryCliente);
            if (!isset($dadosCliente['cli_id'])) {
                $this->returnClass['error'] = "ID do boleto não localizado!";
                return;
            }
            
            $idFilho = array();
            if (count($idFilho) > 1) {
                $query = $this->db->query("
                    SELECT 
                        tab_cobrancas.coa_id,tab_cobrancas.coa_pai,tab_cobrancas.cot_id,tab_cobrancas.cli_id,tab_cobrancas.coa_data_vencimento,tab_cobrancas.coa_valor_original,
                        tab_cobrancas_formatos.cof_nome AS formato_nome,
                        tab_cobrancas_status.cot_id AS status_id,
                        IF(tab_cobrancas_lotes.cla_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6), tab_cobrancas_status.cot_nome, CONCAT(tab_cobrancas_status.cot_nome, '<br>(Lote Inativo)')) AS status 
                    FROM 
                        tab_cobrancas 
                        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
                        INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
        				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
		          		LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
                    WHERE tab_cobrancas.coa_id='".$idFilho[(count($idFilho)-1)] ."'");
                $dadosBoleto = $this->db->resultadoArray($query);
            }
            
            
            if ($dadosBoleto['status_id'] == 12) {
                $this->returnClass['error'] = "Visualização não disponível, boleto foi cancelado!";
                return;
            }
            
            if ($dadosBoleto['status_id'] != 1) {
                $this->returnClass['error'] = "Visualização não disponível, boleto já quitado!";
                return;
            }
            
            if ($dadosBoleto['formato_nome'] == 'Carteira') {
                $this->returnClass['error'] = "Boletos no formato Carteira não tem visualização!";
                return;
            }
            
            // require_once '../_modulos/cobrancas/cobrancas.boletos_pdf_api.class.inc.php';
            // $boletos_pdf = new cobrancas_boletos_pdf_api( $this->db );

            require_once '../_modulos/cobrancas/cobrancas.boletos_pdf.class.inc.php';
            $boletos_pdf = new cobrancas_boletos_pdf( $this->db );
            $boletos_pdf->gerarBoleto( $dadosBoleto['coa_id'] ); // 
            $boleto = $boletos_pdf->conteudo();//  exportar();

            //echo $boleto;
            
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['formato'] = 'application/pdf';
            //$this->returnClass['formato'] = 'application/pdf';
            $this->returnClass['conteudo'] = base64_encode($boleto);
            
            
        }
        
    }
    
    
    function baixa() {
        // Verifica ID do boleto
        $idBoleto = intval(@$this->urlClass[2]);
        if ($idBoleto === 0) {
            $this->returnClass['error'] = "ID do boleto não informado!";
            return;
        }
        
        $this->conectaDB();
        // Procura boleto
        $resultBoleto = $this->db->query("SELECT coa_id,coa_pai,cot_id,cli_id,coa_data_vencimento,coa_valor_original,coa_valor_bonificacao,coa_codigo,coa_grupo_id FROM tab_cobrancas WHERE coa_id={$idBoleto}");
        $dadosBoleto = $this->db->resultadoArray($resultBoleto);
        if (intval($dadosBoleto['coa_id']) == 0) {
            $this->returnClass['error'] = "ID do boleto não localizado!";
            return;
        }
        else {
            //Verifica se o boleto percente a um cliente do parceiro
            $result = $this->db->query("SELECT cli_id,cli_nome,cli_cpf_cnpj FROM tab_clientes WHERE cli_id='".$dadosBoleto['cli_id']."' and clo_id = '".$this->idParceiro."' and (cls_id='1' OR cls_id='2')");
            $dadosCliente = $this->db->resultadoArray($result);
            if ($dadosCliente===false) {
                $this->returnClass['error'] = "ID do boleto não localizado!";
                return;
            }
            
            // Verifica dados informados
            $Format = new formats();
            $erro = array();
            if ($Format->ValidDtBr($_POST['data_pagamento']) === false) $erro['data_pagamento'] = 'Data do pagamento é inválida!';
            else {
                //verifica se data é maior que data atual, oque não pode
                $tmpdtpagto = explode('/', $_POST['data_pagamento']);
                $dttime = strtotime($tmpdtpagto[2].'-'.$tmpdtpagto['1'].'-'.$tmpdtpagto[0]);
                if ($dttime > time()) {
                    $erro['data_pagamento'] = 'Data de pagamento é maior que a data atual!';
                }
                else {
                    $data_pagamento = $tmpdtpagto[2].'-'.$tmpdtpagto['1'].'-'.$tmpdtpagto[0];
                }
            }
            if ($Format->ValidMoneyBr($_POST['valor_pago']) === false) $erro['valor_pago'] = 'Valor do pagamento é inválido!';
            else {
                $valorPago = $Format->Float2Int($_POST['valor_pago']);
                if ($valorPago == 0) $erro['valor_pago'] = 'Valor do pagamento é inválido!';
            }
            
            if (count($erro) > 0) {
                $this->returnClass['error'] = $erro;
                return;
            }

            // Verifica Status do Boleto Cancelado
            if ($dadosBoleto['cot_id'] == 12) {
                $this->returnClass['error'] = "ID do boleto informado esta cancelado!";
                return;
            }
            
            // Verifica Status do Boleto Pago
            if ($dadosBoleto['cot_id'] > 1) {
                $this->returnClass['error'] = "ID do boleto informado consta como pago!";
                return;
            }
            
            // Calcula valores Juros, Acrescimos e Decrescimos
            $calculoJuros = $this->calculaJuros($dadosBoleto['coa_data_vencimento'], $data_pagamento, $dadosBoleto['coa_valor_original']);
            $valorPago -= $calculoJuros;
            $vlDesconto = 0;
            
            if ($calculoJuros == 0) {
                //Sem juros, pagou dentro do vencimento, verifica bonificação
                $vlDesconto = $dadosBoleto['coa_valor_bonificacao'];
            }
            
            if ($valorPago != $dadosBoleto['coa_valor_original']) {
                if ($valorPago > $dadosBoleto['coa_valor_original']) {
                    $vlAcrescimo = $valorPago - $dadosBoleto['coa_valor_original'];
                }
                else { $vlAcrescimo = 0; }
            }
            else { $vlAcrescimo = 0; }
            
            require_once '_modulos/cobrancas/cobrancas.class.inc.php';
            $intranetCobranca = new cobrancas($this->db, null, false);
            
            $totalPagamento = ($dadosBoleto['coa_valor_original'] - $vlDesconto) + $calculoJuros + $vlAcrescimo;
            if ($totalPagamento > $valorPago+$calculoJuros) {
                $tpStatusPagto = 15; // Pagamento parcial;
            }
            else {
                $tpStatusPagto = 13; // Pagamento integral
            }
            $dados_status = $intranetCobranca->buscaDadosStatus($tpStatusPagto);
            
            // Baixando boletos, copia da intranet
            // Atualiza cobranca
            $query = "UPDATE tab_cobrancas SET coa_data_processamento = CURDATE(), coa_data_pagamento = '{$data_pagamento}', coa_data_credito = '{$data_pagamento}', coa_valor_juro = {$calculoJuros}, coa_valor_desconto = {$vlDesconto}, coa_valor_acrescimo = {$vlAcrescimo}, coa_valor_decrescimo = 0, coa_valor_total = ".($valorPago+$calculoJuros).", cot_id = {$tpStatusPagto} WHERE coa_id = {$idBoleto}";
            $this->db->query($query);
            
            // Sinaliza remessa
            if ($intranetCobranca->ehRegistrada($idBoleto)) { 
                $query = "UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$idBoleto}";
                $query = $this->db->query($query);
            }
            // Descancela detalhes
            $query = "UPDATE tab_cobrancas_detalhes SET cod_cancelado = 0 WHERE coa_id = {$idBoleto}";
            $this->db->query($query);
            
            // Registra Comentários
            if ($_POST["comentario"]) $comentario = $this->textCharsetInput("<br><b>Comentário:</b> ").trim($_POST["comentario"]);
            $intranetCobranca->registraComentario($idBoleto, $this->textCharsetInput("<b>Ação:</b> Baixa via API").$comentario, $_SESSION["usuario"]);
            
            
            // Baixa Cobrança Pai
            if ($dadosBoleto["coa_pai"]) {
                $intranetCobranca->baixarPagaEmOutraVia($dadosBoleto["coa_pai"], $dadosBoleto["coa_codigo"], $comentario);
                // Baixa Cobranças Irmãs
                $cobrancas_filho = $intranetCobranca->buscaCobrancasFilho($dadosBoleto["coa_pai"]);
                foreach ($cobrancas_filho as $cobranca_filho_id) {
                    if ($cobranca_filho_id != $idBoleto) {
                        $intranetCobranca->baixarPagaEmOutraVia($cobranca_filho_id, $dadosBoleto["coa_codigo"], $comentario);
                    }
                }
                
            }
            
            // Baixa Cobranças Filhas
            $cobrancas_filho = $intranetCobranca->buscaCobrancasFilho($idBoleto);
            foreach ($cobrancas_filho as $cobranca_filho_id) {
                $intranetCobranca->baixarPagaEmOutraVia($cobranca_filho_id, $dadosBoleto["coa_codigo"], $comentario);
            }
            
            // Baixa Filhos do Grupo
            $intranetCobranca->baixarFilhosDoGrupo($idBoleto, $dadosBoleto["coa_codigo"], $comentario);
            
            // Cancela Grupo Pai
            if ($dadosBoleto["coa_grupo_id"]) {
                $intranetCobranca->cancelarGrupoPai($dadosBoleto["coa_grupo_id"], $comentario);
            }
            
            // Finaliza ajuste de bloqueio se n�o tiver outra cobran�a em atraso
            $intranetCobranca->finalizaBloqueio($dadosBoleto['cli_id']);
            
            if ($dados_status["recebido"]) {
                // Atualiza Comiss�o Primeiro Pagamento
                $intranetCobranca->atualizaComissaoPrimeiroPagamento($idBoleto);
                // Aprova Contrato
                $intranetCobranca->aprovaContrato($idBoleto);
            }
            
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['id_boleto'] = $idBoleto;
            $this->returnClass['data_vencimento'] = $Format->FormatDate( $dadosBoleto['coa_data_vencimento'] );
            $this->returnClass['valor_original'] = $Format->FormatFloatSQL($dadosBoleto['coa_valor_original']);
            $this->returnClass['pagamento']['data'] = $Format->FormatDate($data_pagamento);
            $this->returnClass['pagamento']['valores']['bonificacao'] = $Format->FormatFloatSQL($vlDesconto);
            $this->returnClass['pagamento']['valores']['juros'] = $Format->FormatFloatSQL($calculoJuros);
            $this->returnClass['pagamento']['valores']['acrescimo'] = $Format->FormatFloatSQL($vlAcrescimo);
            $this->returnClass['pagamento']['valores']['total'] = $Format->FormatFloatSQL($totalPagamento);
            $this->returnClass['pagamento']['valores']['pago'] = $Format->FormatFloatSQL($valorPago+$calculoJuros);
            $this->returnClass['pagamento']['status'] = $this->textCharsetOutput($dados_status['nome']);
            
            return;
        }
    }
    
    

    function calculaJuros($data_vencimento, $data_pagamento, $valor) {
        
        if (($dias = (int) ((strtotime($data_pagamento) - strtotime($this->buscaProximoDiaUtil($data_vencimento))) / 60 / 60 / 24)) > 0) {
            return (int) ($valor * 0.02) + (int) ($valor * 0.01 / 30 * $dias);
        } else {
            return 0;
        }
        
    }
    
    
    
    function buscaProximoDiaUtil($data) {
        $Format = new formats();
        
        $tmpdata = explode('-', $data); // y-m-d
        
        
        if ($Format->ehFeriado( $tmpdata[2], $tmpdata[1], $tmpdata[0] )) {
            $data = date("Y-m-d", strtotime("$data 1 days"));
        }
        
        $dia_semana = date("w", strtotime($data));
        
        if ($dia_semana == 6) {
            return date("Y-m-d", strtotime("$data 2 days"));
        } elseif ($dia_semana == 0) {
            return date("Y-m-d", strtotime("$data 1 days"));
        } else {
            return $data;
        }
        
    }
    
    
    
    
    
    function __destruct() {
        
    }
    
}