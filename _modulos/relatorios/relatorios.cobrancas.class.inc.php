<?

class relatorios_cobrancas {

  var $bancodedados;
  var $permissoes;

  function __construct($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }

  ######################
  ### Funções Gerais ###
  ######################

  function buscaConsulta() {
    $consulta = "
      SELECT DISTINCT
        tab_cobrancas.coa_id AS cobranca_id,
        tab_cobrancas.coa_pai AS cobranca_pai_id,
        tab_cobrancas.cli_id AS cliente_id,
        [campos_retorno]
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
        LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
        LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id)
        [joins_filtro]
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        tab_clientes_origens.clo_nome,
        tab_clientes.cli_nome,
      	tab_cobrancas.coa_codigo";
    return $consulta;
  }

	function buscaCampos() {
		
		$campos[] = array ("categoria" => "Cliente", "nome" => "ID", "campo" => "cliente_id", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Origem", "campo" => "origem_nome", "tabela" => "tab_clientes_origens.clo_nome AS origem_nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Nome", "campo" => "cliente_nome", "tabela" => "tab_clientes.cli_nome AS cliente_nome, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Nome Fantasia", "campo" => "cliente_nome_fantasia", "tabela" => "tab_clientes.cli_nome_fantasia AS cliente_nome_fantasia, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "CPF/CNPJ", "campo" => "cliente_cpf_cnpj", "tabela" => "tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Inscrição Estadual", "campo" => "cliente_inscricao_estadual", "tabela" => "IF(tab_clientes.cli_inscricao_estadual = -1, 'Isento', tab_clientes.cli_inscricao_estadual) AS cliente_inscricao_estadual, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Telefone(s)", "campo" => "cliente_telefone", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Telefone (Individual)", "campo" => "cliente_telefone_individual", "tabela" => "REPLACE(CONCAT(tab_clientes_contatos_telefones.ccl_ddd, tab_clientes_contatos_telefones.ccl_numero),'-', '') AS cliente_telefone_individual, ", "joins" => array("INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)"));
		$campos[] = array ("categoria" => "Cliente", "nome" => "E-mail(s)", "campo" => "cliente_email", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "E-mail (Individual)", "campo" => "cliente_email_individual", "tabela" => "CONCAT(tab_clientes_contatos_emails.ccm_usuario, '@', tab_clientes_contatos_emails.ccm_dominio) AS cliente_email_individual, ", "joins" => array("INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)"));
		$campos[] = array ("categoria" => "Cliente", "nome" => "MAC's", "campo" => "cliente_macs", "tabela" => "tab_clientes.cli_id AS cliente_id, ");

		$campos[] = array ("categoria" => "Cliente", "nome" => "Débito em Conta » Operação", "campo" => "debito_operacao", "tabela" => "tab_clientes.cli_ccd_debito_operacao AS debito_operacao, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Débito em Conta » Agência", "campo" => "debito_agencia", "tabela" => "tab_clientes.cli_ccd_debito_agencia AS debito_agencia, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Débito em Conta » Conta", "campo" => "debito_conta", "tabela" => "tab_clientes.cli_ccd_debito_conta AS debito_conta, ");

		$campos[] = array ("categoria" => "Cliente", "nome" => "Cidade", "campo" => "cliente_cidade", "tabela" => "tab_cidades.cid_nome AS cliente_cidade, ", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))", "LEFT OUTER JOIN tab_cidades ON (tab_ceps.cid_id = tab_cidades.cid_id)"));
		$campos[] = array ("categoria" => "Cliente", "nome" => "Estado", "campo" => "cliente_estado", "tabela" => "tab_estados.est_sigla AS cliente_estado, ", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))", "LEFT OUTER JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)"));

		$campos[] = array ("categoria" => "Cliente", "nome" => "Condomínio", "campo" => "cliente_condominio", "tabela" => "tab_clientes.con_id AS condominio_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Endereço", "campo" => "cliente_endereco", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Endereço Cobrança", "campo" => "cliente_endereco_cobranca", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Endereço Resumido", "campo" => "cliente_endereco_resumido", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
		$campos[] = array ("categoria" => "Cliente", "nome" => "Status", "campo" => "cliente_status", "tabela" => "tab_clientes_status.cls_nome AS cliente_status, ", "joins" => array("INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Consultor", "campo" => "consultor_nome", "tabela" => "tab_consultor.nome AS consultor_nome, ", "joins" => array("INNER JOIN tab_clientes cli_consultor ON (tab_cobrancas.cli_id = cli_consultor.cli_id)", "LEFT OUTER JOIN tab_consultor ON (tab_consultor.id = cli_consultor.consultor)"));

		$campos[] = array ("categoria" => "Cobrança", "nome" => "Lote", "campo" => "lote", "tabela" => "tab_cobrancas_lotes.col_id AS lote, tab_cobrancas.coa_pai AS coa_pai, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Formato", "campo" => "formato", "tabela" => "tab_cobrancas_formatos.cof_nome AS formato, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Competência(s)", "campo" => "competencia", "tabela" => "tab_cobrancas.coa_id AS id, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Código", "campo" => "codigo", "tabela" => "tab_cobrancas.coa_codigo AS codigo, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Data Geração", "campo" => "data_geracao", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_geracao,'%d/%m/%Y') AS data_geracao, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Data Vencimento", "campo" => "data_vencimento", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_vencimento,'%d/%m/%Y') AS data_vencimento, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Data Processamento", "campo" => "data_processamento", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_processamento,'%d/%m/%Y') AS data_processamento, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Data Pagamento", "campo" => "data_pagamento", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_pagamento,'%d/%m/%Y') AS data_pagamento, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Data Crédito", "campo" => "data_credito", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_credito,'%d/%m/%Y') AS data_credito, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Original", "campo" => "valor_original", "tabela" => "tab_cobrancas.coa_valor_original AS valor_original, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Original (Últ. Via)", "campo" => "valor_original_ultima_via", "tabela" => "tab_cobrancas.coa_id AS id, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Acréscimo", "campo" => "valor_acrescimo", "tabela" => "tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Decréscimo", "campo" => "valor_decrescimo", "tabela" => "tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Bonificação", "campo" => "valor_bonificacao", "tabela" => "tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Total", "campo" => "valor_total", "tabela" => "tab_cobrancas.coa_valor_total AS valor_total, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Comentário", "campo" => "comentario", "tabela" => "tab_cobrancas.coa_comentario AS comentario, ");
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Status", "campo" => "status", "tabela" => "tab_cobrancas_status.cot_nome AS status, ", "joins" => array("INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)"));
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Serviço", "campo" => "servico", "tabela" => "tab_cobrancas.coa_id AS id, ");
    
		$campos[] = array ("categoria" => "Cobrança", "nome" => "Plano", "campo" => "plano", "tabela" => "tab_planos2.pla_nome AS plano, ",
      "joins" => array("INNER JOIN tab_cobrancas_detalhes tab_cobrancas_detalhes2 ON (tab_cobrancas_detalhes2.coa_id = tab_cobrancas.coa_id)", "INNER JOIN tab_assinaturas tab_assinaturas2 ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas2.ass_id)", "INNER JOIN tab_planos tab_planos2 ON (tab_assinaturas2.pla_id = tab_planos2.pla_id)")); 

		$campos[] = array ("categoria" => "Nota Fiscal", "nome" => "Número", "campo" => "nota_fiscal_numero", "tabela" => "tab_faturas.fat_numero AS nota_fiscal_numero, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)"));
		$campos[] = array ("categoria" => "Nota Fiscal", "nome" => "Emissão", "campo" => "nota_fiscal_emissao", "tabela" => "DATE_FORMAT(tab_faturas.fat_data_emissao,'%d/%m/%Y') AS nota_fiscal_emissao, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)"));
		$campos[] = array ("categoria" => "Nota Fiscal", "nome" => "Valor", "campo" => "nota_fiscal_valor", "tabela" => "tab_faturas.fat_valor AS nota_fiscal_valor, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)"));
		$campos[] = array ("categoria" => "Nota Fiscal", "nome" => "Tipo", "campo" => "nota_fiscal_tipo", "tabela" => "tab_faturas_tipos.fap_nome AS nota_fiscal_tipo, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)", "LEFT OUTER JOIN tab_faturas_tipos ON (tab_faturas.fap_id = tab_faturas_tipos.fap_id)"));

		//$campos[] = array ("categoria" => "Nota Fatura", "nome" => "Número", "campo" => "nota_fatura_numero", "tabela" => "tab_faturas.fat_numero AS nota_fiscal_numero, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)"));
		//$campos[] = array ("categoria" => "Nota Fatura", "nome" => "Emissão", "campo" => "nota_fatura_emissao", "tabela" => "DATE_FORMAT(tab_faturas.fat_data_emissao,'%d/%m/%Y') AS nota_fiscal_emissao, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)"));
		//$campos[] = array ("categoria" => "Nota Fatura", "nome" => "Valor", "campo" => "nota_fatura_valor", "tabela" => "(SELECT SUM(fai_valor_total) FROM tab_faturas_itens WHERE tab_faturas_itens.fat_id = tab_faturas.fat_id) AS nota_fiscal_valor, ", "joins" => array("LEFT OUTER JOIN tab_faturas ON (tab_cobrancas.fat_id = tab_faturas.fat_id)"));

		for ($i = 0; $i < sizeof($campos); $i++) {
			$retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"], "joins" => $campos[$i]["joins"]);
		}

		return $retorno;
	
	}

	function buscaFiltros() {
		
		$filtros[] = array ("tipo" => "select", "nome" => "Lote", "name" => "lote", "funcao" => "buscaCobrancasLotes()", "where" => "tab_cobrancas_lotes.col_id");
		$filtros[] = array ("tipo" => "select", "nome" => "Formato", "name" => "formato", "funcao" => "buscaCobrancasFormatos()", "where" => "tab_cobrancas.cof_id");
		$filtros[] = array ("tipo" => "select", "nome" => "Formato » Tipo", "name" => "formato_tipo", "funcao" => "buscaCobrancasFormatosTipos()");
		$filtros[] = array ("tipo" => "select", "nome" => "Tipo", "name" => "tipo", "funcao" => "buscaCobrancasTipos()");
		$filtros[] = array ("tipo" => "select", "nome" => "Status", "name" => "status", "funcao" => "buscaCobrancasStatus()", "where" => "tab_cobrancas.cot_id");
		$filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_clientes.con_id");
		$filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Tipo", "name" => "cliente_tipo", "funcao" => "buscaClienteTipos()", "where" => "tab_clientes.clt_id");
		$filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Origem", "name" => "origem", "funcao" => "buscaOrigens()", "where" => "tab_clientes.clo_id");
		$filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Status", "name" => "cliente_status", "funcao" => "buscaClientesStatus()", "where" => "tab_clientes.cls_id");
		$filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "col_id", "funcao" => "", "where" => "");
		$filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "coa_pai", "funcao" => "", "where" => "tab_cobrancas.coa_pai");
    $filtros[] = array ("tipo" => "select", "nome" => "Consultor", "name" => "consultor", "funcao" => "buscaConsultores()", "where" => "tab_clientes.consultor");
    
    $filtros[] = array ("tipo" => "select", "nome" => "Serviço", "name" => "servico", "funcao" => "buscaContratoServicos()", "onchange" => "buscaPlanos(this.value)", "where" => "tab_planos2.pls_id", "joins" => array("INNER JOIN tab_cobrancas_detalhes tab_cobrancas_detalhes2 ON (tab_cobrancas_detalhes2.coa_id = tab_cobrancas.coa_id)", "INNER JOIN tab_assinaturas tab_assinaturas2 ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas2.ass_id)", "INNER JOIN tab_planos tab_planos2 ON (tab_assinaturas2.pla_id = tab_planos2.pla_id)")); 
        
    $filtros[] = array ("tipo" => "selectMultiple", "nome" => "Plano", "name" => "plano[]", "funcao" => "buscaPlanos()", "where" => "tab_assinaturas.pla_id", "joins" => array("INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_cobrancas_detalhes.ass_id)"));
    
		return $filtros;
		
	}


	function buscaPeriodos() {
		
		$periodos[] = array ("tipo" => "periodoPorMes", "nome" => "Competência", "name1" => "competencia_inicio", "name2" => "competencia_termino", "where" => "tab_cobrancas_detalhes.cod_competencia_inicio");
		$periodos[] = array ("tipo" => "periodo", "nome" => "Data Geração", "name1" => "data_geracao_inicio", "name2" => "data_geracao_termino", "where" => "tab_cobrancas.coa_data_geracao");
		$periodos[] = array ("tipo" => "periodo", "nome" => "Data Vencimento", "name1" => "data_vencimento_inicio", "name2" => "data_vencimento_termino", "where" => "tab_cobrancas.coa_data_vencimento");
		$periodos[] = array ("tipo" => "periodo", "nome" => "Data Processamento", "name1" => "data_processamento_inicio", "name2" => "data_processamento_termino", "where" => "tab_cobrancas.coa_data_processamento");
		$periodos[] = array ("tipo" => "periodo", "nome" => "Data Pagamento", "name1" => "data_pagamento_inicio", "name2" => "data_pagamento_termino", "where" => "tab_cobrancas.coa_data_pagamento");
		$periodos[] = array ("tipo" => "periodo", "nome" => "Data Crédito", "name1" => "data_credito_inicio", "name2" => "data_credito_termino", "where" => "tab_cobrancas.coa_data_credito");
		
		return $periodos;
		
	}

	function buscaIntervalos() {
		
		$intervalos[] = array ("tipo" => "dinheiro", "nome" => "Original", "name1" => "valor_original_inicio", "name2" => "valor_original_termino", "where" => "tab_cobrancas.coa_valor_original");
		$intervalos[] = array ("tipo" => "dinheiro", "nome" => "Acréscimo", "name1" => "valor_acrescimo_inicio", "name2" => "valor_acrescimo_termino", "where" => "tab_cobrancas.coa_valor_acrescimo");
		$intervalos[] = array ("tipo" => "dinheiro", "nome" => "Decréscimo", "name1" => "valor_decrescimo_inicio", "name2" => "valor_decrescimo_termino", "where" => "tab_cobrancas.coa_valor_decrescimo");
		$intervalos[] = array ("tipo" => "dinheiro", "nome" => "Total", "name1" => "valor_total_inicio", "name2" => "valor_total_termino", "where" => "tab_cobrancas.coa_valor_total");
		
		return $intervalos;
		
	}

  function buscaContratoServicos() {
    $query = "
      SELECT
        pls_id AS id,
        pls_nome AS nome
      FROM
        tab_planos_servicos";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $contrato_servicos[$dados["id"]] = $dados["nome"];
    }

    return $contrato_servicos;
  }

  function buscaPlanos($servico_id = null) {
    $servico_id = $_POST["servico"] ? $_POST["servico"] : $servico_id;
      if ($servico_id) {
        $query = "
          SELECT
          pla_id AS id,
          pla_nome AS nome
        FROM
          tab_planos
        WHERE
          pls_id = $servico_id";
        $query = $this->bancodedados->query($query);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
          $planos[$dados["id"]] = $dados["nome"];
        }
      
      return $planos;
		}
  }

  ###################
  ### Busca Dados ###
  ###################

  function buscaClientesStatus() {
    $query = "
      SELECT
        cls_id AS id,
        cls_nome AS nome
      FROM
        tab_clientes_status
      ORDER BY
        cls_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $statuss[$dados["id"]] = $dados["nome"];
    }
    return $statuss;
  }

  function buscaConsultores() {
    
    $query = "SELECT tab_consultor.* FROM tab_clientes INNER JOIN tab_consultor ON tab_clientes.consultor = tab_consultor.id";
    $query = $this->bancodedados->query($query);
    $consultores = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $consultores[$dados["id"]] = $dados["nome"];
      }
      
    return $consultores;
  }

  function buscaCobrancasLotes() {
    $cobrancas_lotes["-1"] = "Segundas Vias";
    $cobrancas_lotes["-2"] = "Cobranças Avulsas";
    $query = "SELECT col_id AS id, col_nome AS nome FROM tab_cobrancas_lotes ORDER BY col_vencimento";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas_lotes[$dados["id"]] = $dados["nome"];
    }
    return $cobrancas_lotes;
  }

  function buscaCobrancasFormatos() {
    $query = "SELECT cof_id AS id, cof_nome AS nome FROM tab_cobrancas_formatos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas_formatos[$dados["id"]] = $dados["nome"];
    }
    return $cobrancas_formatos;
  }

  function buscaCobrancasFormatosTipos() {
    $query = "SELECT cft_id AS id, cft_nome AS nome FROM tab_cobrancas_formatos_tipos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas_formatos_tipos[$dados["id"]] = $dados["nome"];
    }
    $cobrancas_formatos_tipos["2,3"] = "Boletos";
    asort($cobrancas_formatos_tipos);
    return $cobrancas_formatos_tipos;
  }

  function buscaCobrancasTipos() {
    $cobrancas_tipos["1"] = "Primeira Via";
    $cobrancas_tipos["2"] = "Segunda Via";
    return $cobrancas_tipos;
  }

  function buscaCobrancasStatus() {
    $query = "SELECT cot_id AS id, cot_nome AS nome FROM tab_cobrancas_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas_status[$dados["id"]] = $dados["nome"];
    }
    $cobrancas_status["-1"] = "Baixa Automática";
    $cobrancas_status["-2"] = "Baixa Manual";
    $cobrancas_status["-3"] = "Baixa Manual (Paga)";
    $cobrancas_status["-4"] = "Baixa (Paga)";
    asort($cobrancas_status);
    return $cobrancas_status;
  }

  function buscaCondominios() {
    $query = "SELECT con_id AS id, con_nome AS nome FROM tab_condominios WHERE cos_id IS NOT NULL ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }

  function buscaClienteTipos() {
    $query = "SELECT clt_id AS id, clt_nome AS nome FROM tab_clientes_tipos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaOrigens() {
    $query = "SELECT clo_id AS id, clo_nome AS nome FROM tab_clientes_origens WHERE 1".($_SESSION["origem"] ? " AND
				clo_id = {$_SESSION["origem"]}" : "")." ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[$dados["id"]] = $dados["nome"];
    }
    return $origens;
  }

  function buscaValorOriginalUltimaVia($cobranca_id) {
    $query = "
      SELECT
        coa_valor_original AS valor_original
      FROM
        tab_cobrancas
      WHERE
        coa_pai = $cobranca_id
      ORDER BY
        coa_id DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["valor_original"];
    } else {
      $query = "
        SELECT
          coa_valor_original AS valor_original
        FROM
          tab_cobrancas
        WHERE
          coa_id = $cobranca_id";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
        return $dados["valor_original"];
      }
    }
  }

  function buscaCondominioNome($condominio_id) {
  	$condominio_id = preg_replace("/[^0-9]/", "", $condominio_id);
  	if ($condominio_id) {
	    $query = "SELECT con_nome FROM tab_condominios WHERE con_id = $condominio_id";
	    $query = $this->bancodedados->query($query);
	    return $this->bancodedados->resultado($query);
  	}
  }

  function buscaMACs($cliente_id) {
    $query = "
      SELECT
        clc_mac_address AS mac
      FROM
        tab_clientes_comodatos
      WHERE
        tab_clientes_comodatos.cli_id = $cliente_id AND
        tab_clientes_comodatos.ccs_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $macs[] = $dados["mac"];
    }
    return @implode(", ", $macs);
  }

  function buscaEnderecoBloco($bloco_id) {
    $query = "
      SELECT
        tab_condominios.con_nome AS condominio,
        tab_condominios_blocos.cob_nome AS bloco,
        IF(tab_condominios_blocos.cep_id IS NULL, tab_condominios.cep_id, tab_condominios_blocos.cep_id) AS cep,
        IF(tab_condominios_blocos.cob_numero IS NULL, tab_condominios.con_numero, tab_condominios_blocos.cob_numero) AS numero
      FROM
        tab_condominios_blocos
        INNER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
      WHERE
        tab_condominios_blocos.cob_id = $bloco_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados = funcoes::mescla_arrays($dados, $this->buscaEnderecoCEP($dados["cep"]));
    }
    return $dados;
  }

  function buscaEnderecoCondominio($condominio_id) {
    $query = "
      SELECT
        con_nome AS condominio,
        cep_id AS cep,
        con_numero AS condominio
      FROM
        tab_condominios
      WHERE
        con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return funcoes::mescla_arrays($dados, $this->buscaEnderecoCEP($dados["cep"]));
    }
  }

  function buscaEnderecoCEP($cep) {
    $query = "
      SELECT
      	CONCAT(SUBSTRING(tab_ceps.cep_id, 1, 5), '-', SUBSTRING(tab_ceps.cep_id, 6, 3)) AS cep,
        tab_logradouros.log_nome AS logradouro,
        tab_bairros.bai_nome AS bairro,
        tab_cidades.cid_nome AS cidade,
        tab_estados.est_sigla AS estado
      FROM
        tab_ceps
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        tab_ceps.cep_id = $cep";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
  }

  function buscaClienteTelefones($id) {
    $query = "
      SELECT
        ccl_ramal AS ramal,
        CONCAT('(', ccl_ddd, ') ', ccl_numero) AS telefone
      FROM
        tab_clientes_contatos_telefones
      WHERE
      	cli_id = $id AND
		ccf_id > 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $telefone = $dados["telefone"];
      if ($dados["ramal"]) $telefone .= " (Ramal {$dados["ramal"]})";
      $lista[] = $telefone;
    }
    return implode(" / ", $lista);
  }

  function buscaClienteEmails($id) {
    $query = "
      SELECT
        CONCAT(ccm_usuario, '@', ccm_dominio) AS email
      FROM
        tab_clientes_contatos_emails
      WHERE
      	cli_id = $id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados["email"];
    }
    return implode(", ", $lista);
  }

  function corrigeComplementoCliente($complemento) {
    $complementos_tipos["a"] = "Apto ";
    $complementos_tipos["l"] = "Loja ";
    $complementos_tipos["s"] = "Sala ";
    $complementos_tipos["c"] = "Casa ";
    $complementos_tipos["o"] = "";
    return $complementos_tipos[$complemento[0]].trim(substr($complemento, 1, strlen($complemento)));
  }

  function buscaEnderecoCliente($cliente) {
    $query = "
      SELECT
        tab_clientes.cob_id AS bloco,
        tab_clientes.con_id AS condominio,
        tab_clientes.cep_id AS cep,
        tab_clientes.cli_numero AS numero,
        tab_clientes.cli_complemento AS complemento
      FROM
        tab_clientes
      WHERE
        tab_clientes.cli_id = $cliente";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      $endereco = $this->bancodedados->resultadoArray($query);
      if ($endereco["bloco"]) {
        $endereco_tratado = funcoes::mescla_arrays($endereco_tratado, $this->buscaEnderecoBloco($endereco["bloco"]));
      } elseif ($endereco["condominio"]) {
        $endereco_tratado = funcoes::mescla_arrays($endereco_tratado, $this->buscaEnderecoCondominio($endereco["condominio"]));
      } elseif ($endereco["cep"]) {
        $endereco_tratado = funcoes::mescla_arrays($endereco_tratado, $this->buscaEnderecoCEP($endereco["cep"]));
        $endereco_tratado["numero"] = $endereco["numero"];
      }
      $endereco_tratado["complemento"] = $this->corrigeComplementoCliente($endereco["complemento"]);
    }
    return $endereco_tratado;
  }

  function buscaEnderecoCobrancaCliente($cliente) {
    $query = "
      SELECT
        tab_clientes_cobranca_dados.cob_id AS bloco,
        tab_clientes_cobranca_dados.cep_id AS cep,
        tab_clientes_cobranca_dados.ccd_numero AS numero,
        tab_clientes_cobranca_dados.ccd_complemento AS complemento
      FROM
        tab_clientes_cobranca_dados
      WHERE
        tab_clientes_cobranca_dados.cli_id = $cliente";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      $dados_cobranca = $this->bancodedados->resultadoArray($query);
      if ($dados_cobranca["bloco"]) {
        $dados_cobranca_tratados = funcoes::mescla_arrays($dados_cobranca_tratados,$this->buscaEnderecoBloco($dados_cobranca["bloco"]));
        $dados_cobranca_tratados["complemento"] = $this->corrigeComplementoCliente($dados_cobranca["complemento"]);
      } elseif ($dados_cobranca["cep"]) {
        $dados_cobranca_tratados = funcoes::mescla_arrays($dados_cobranca_tratados,$this->buscaEnderecoCEP($dados_cobranca["cep"]));
        $dados_cobranca_tratados["numero"] = $dados_cobranca["numero"];
        $dados_cobranca_tratados["complemento"] = $this->corrigeComplementoCliente($dados_cobranca["complemento"]);
        $dados_cobranca_tratados["condominio"] = "";
        $dados_cobranca_tratados["bloco"] = "";
      } else {
        if ($dados_cobranca["complemento"]) $dados_cobranca_tratados["complemento"] = $this->corrigeComplementoCliente($dados_cobranca["complemento"]);
      }
    }
    $dados_cobranca_tratados = funcoes::mescla_arrays($this->buscaEnderecoCliente($cliente),$dados_cobranca_tratados);
    return $dados_cobranca_tratados;
  }

  function formataEndereco($endereco) {
    if ($endereco["logradouro"]) $endereco_formatado = $endereco["logradouro"];
    if ($endereco["numero"]) $endereco_formatado .= ", " . $endereco["numero"];
    if ($endereco["condominio"]) $endereco_formatado .= ", " . $endereco["condominio"];
    if ($endereco["complemento"]) {
      $endereco_formatado .= ", " . $endereco["complemento"];
      if ($endereco["bloco"] && $endereco["bloco"] != "Único") $endereco_formatado .= "/" . $endereco["bloco"];
    } else {
      if ($endereco["bloco"] && $endereco["bloco"] != "Único") $endereco_formatado .= $endereco["bloco"];
    }
    if ($endereco["bairro"]) $endereco_formatado .= ", " . $endereco["bairro"];
    if ($endereco["cidade"]) $endereco_formatado .= ", " . $endereco["cidade"];
    if ($endereco["estado"]) $endereco_formatado .= "/" . $endereco["estado"];
    if ($endereco["cep"]) $endereco_formatado .= ", " . $endereco["cep"];
    return $endereco_formatado;
  }

  function formataEnderecoResumido($endereco) {
    if ($endereco["condominio"]) {
      $endereco_formatado .= $endereco["condominio"];
      if ($endereco["complemento"]) {
        $endereco_formatado .= ", " . $endereco["complemento"];
        if ($endereco["bloco"] && $endereco["bloco"] != "Único") $endereco_formatado .= "/" . $endereco["bloco"];
      } else {
        if ($endereco["bloco"] && $endereco["bloco"] != "Único") $endereco_formatado .= $endereco["bloco"];
      }
      return $endereco_formatado;
    }
  }

  function buscaCobrancaCompetencia($cobranca_id) {
    $query = "
      SELECT DISTINCT
        DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
      FROM
        tab_cobrancas_detalhes
      WHERE
        coa_id = $cobranca_id AND
        ass_id IS NOT NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $competencias[] = $this->formataData($dados["competencia"]);
    }
    return @implode(", ", $competencias);
  }

  function formataData($vencimento) {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    $vencimento = @explode("/", $vencimento);
    return $meses[intval($vencimento[0])]."/".$vencimento[1];
  }

  ######################
  ### Busca Serviços ###
  ######################
  
  function buscaServicos($cobranca_id, $cliente_id) {
    $query = "
      SELECT
        tab_cobrancas_detalhes.cod_descricao AS descricao,
        tab_cobrancas_detalhes.cod_competencia_inicio AS competencia_inicio,
        tab_cobrancas_detalhes.	cod_competencia_termino AS competencia_termino,
        tab_planos.pls_id AS servico_id,
        tab_planos.pla_nome AS plano
      FROM
        tab_cobrancas_detalhes
        LEFT OUTER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
        LEFT OUTER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        LEFT OUTER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_cobrancas_detalhes.coa_id = $cobranca_id
		ORDER BY tab_planos_servicos.pls_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if (in_array($dados["servico_id"], array(1, 2, 3, 28, 32, 33, 34, 36))) {
	      $dados["plano"] = trim(preg_replace('/\([^\)]*\)/', '', $dados["plano"]));
	      $dados["plano"] = trim(preg_replace('/\[[^\]]*\]/', '', $dados["plano"]));
	      $dados["plano"] = trim(str_replace(array("Acesso Internet", "Condomínio", "Pós Venda", "Pos Venda", "Ponto Principal", "Corujão", "Olé Banda Larga", "Olé Total", "Ponto Adicional", "Super Condomínio"), "", $dados["plano"]));
	      if ($dados["plano"]) {
	      	$servicos[$dados["plano"]] = $dados["plano"];
	      }
    	} elseif (in_array($dados["servico_id"], array(23))) {
      	$servicos["ADSL"] = "ADSL";
    	} elseif (in_array($dados["servico_id"], array(20))) {
      	$servicos["E-mail"] = "E-mail";
    	} elseif (in_array($dados["servico_id"], array(10, 11, 30))) {
      	$servicos["Hospedagem"] = "Hospedagem";
    	} elseif (in_array($dados["servico_id"], array(25))) {
      	$servicos["Sonork"] = "Sonork";
    	} elseif (in_array($dados["servico_id"], array(9))) {
      	$servicos["Co-Location"] = "Co-Location";
    	} elseif (in_array($dados["servico_id"], array(14))) {
      	$servicos["VoIP"] = "VoIP";
    	} elseif (in_array($dados["servico_id"], array(5, 8))) {
      	$servicos["Olé Servicos Custom"] = "Olé Servicos Custom";
    	} elseif (in_array($dados["servico_id"], array(27))) {
      	$servicos["Contrato Personalizado"] = "Contrato Personalizado";
    	} elseif (($servico = $this->buscaServicoCliente($cliente_id, $dados["competencia_inicio"], $dados["competencia_termino"])) != false) {
      	$servicos[$servico] = $servico;
    	}
    }
    if ($servicos) {
    	return implode(", ", $servicos);
    } else {
    	return $this->buscaServico($cliente_id);
    }
  }
  
  function buscaServicoCliente($cliente_id, $competencia_inicio, $competencia_termino) {
  	$competencia_termino = date("Y-m-d", strtotime("$competencia_termino - 1 month"));
  	$competencia_inicio = date("Y-m-d", strtotime("$competencia_inicio + 1 month"));
    $query = "
      SELECT
        tab_planos.pla_nome AS plano,
        tab_planos.pls_id AS servico_id
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_assinaturas.ass_data_ativacao <= '$competencia_inicio' AND
        (tab_assinaturas.ass_data_cancelamento IS NULL OR tab_assinaturas.ass_data_cancelamento >= '$competencia_termino')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	if (in_array($dados["servico_id"], array(1, 2, 3, 28, 32, 33, 34, 36))) {
	      $dados["plano"] = trim(preg_replace("/\\([^\\)]*\\)/", "", $dados["plano"]));
	      $dados["plano"] = trim(preg_replace("/\\[[^\\]]*\\]/", "", $dados["plano"]));
	      $dados["plano"] = trim(str_replace(array("Condomínio", "Acesso Internet", "Pós Venda", "Pos Venda", "Ponto Principal", "Corujão", "Olé Banda Larga", "Olé Total", "Ponto Adicional", "Super Condomínio"), "", $dados["plano"]));
	      return $dados["plano"] == "Condomínio" ? "" : $dados["plano"];
    	} elseif (in_array($dados["servico_id"], array(23))) {
    		return "ADSL";
    	} elseif (in_array($dados["servico_id"], array(20))) {
    		return "E-mail";
    	} elseif (in_array($dados["servico_id"], array(10, 11, 30))) {
      	return "Hospedagem";
    	} elseif (in_array($dados["servico_id"], array(25))) {
      	return "Sonork";
    	} elseif (in_array($dados["servico_id"], array(9))) {
      	return "Co-Location";
    	} elseif (in_array($dados["servico_id"], array(14))) {
      	return "VoIP";
    	} elseif (in_array($dados["servico_id"], array(27))) {
      	return "Contrato Personalizado";
    	} elseif (in_array($dados["servico_id"], array(5, 8))) {
      	return "Olé Servicos Custom";
    	}
    }
  }
  
  function buscaServico($cliente_id) {
    $query = "
      SELECT
        tab_planos.pls_id AS servico_id,
        tab_planos.pla_nome AS plano
      FROM
        tab_assinaturas
        LEFT OUTER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if (in_array($dados["servico_id"], array(1, 2, 3, 28, 32, 33, 34, 36))) {
	      $dados["plano"] = trim(preg_replace('/\([^\)]*\)/', '', $dados["plano"]));
	      $dados["plano"] = trim(preg_replace('/\[[^\]]*\]/', '', $dados["plano"]));
	      $dados["plano"] = trim(str_replace(array("Condomínio", "Acesso Internet", "Pós Venda", "Pos Venda", "Ponto Principal", "Corujão", "Olé Banda Larga", "Olé Total", "Ponto Adicional", "Super Condomínio"), "", $dados["plano"]));
	      if ($dados["plano"]) {
      		$servicos[$dados["plano"]] = $dados["plano"];
	      }
    	} elseif (in_array($dados["servico_id"], array(23))) {
      	$servicos["ADSL"] = "ADSL";
    	} elseif (in_array($dados["servico_id"], array(20))) {
      	$servicos["E-mail"] = "E-mail";
    	} elseif (in_array($dados["servico_id"], array(10, 11, 30))) {
      	$servicos["Hospedagem"] = "Hospedagem";
    	} elseif (in_array($dados["servico_id"], array(25))) {
      	$servicos["Sonork"] = "Sonork";
    	} elseif (in_array($dados["servico_id"], array(9))) {
      	$servicos["Co-Location"] = "Co-Location";
    	} elseif (in_array($dados["servico_id"], array(14))) {
      	$servicos["VoIP"] = "VoIP";
    	} elseif (in_array($dados["servico_id"], array(5, 8))) {
      	$servicos["Olé Servicos Custom"] = "Olé Servicos Custom";
    	} elseif (in_array($dados["servico_id"], array(27))) {
      	$servicos["Contrato Personalizado"] = "Contrato Personalizado";
    	} elseif (($servico = $this->buscaServicoCliente($cliente_id, $dados["competencia_inicio"], $dados["competencia_termino"])) != false) {
      	$servicos[$servico] = $servico;
    	}
    }
    if ($servicos) {
    	return implode(", ", $servicos);
    }
  }
  
}

?>
