<?

class relatorios_cobrancas_detalhado {

  var $bancodedados;
  var $permissoes;
  var $filtro;

  function __construct($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }

  ######################
  ### Funções Gerais ###
  ######################

  function buscaCobrancasDetalhado() {
  	$this->geraFiltros();
	
	$this->filtro = implode(" AND\n        ", $this->filtro);
	
  	// Dados Cobrança
  	$query = "
  		SELECT
  			SUM(coa_valor_juro) AS juro,
  			SUM(coa_valor_desconto) + SUM(coa_valor_decrescimo) AS decrescimo,
  			SUM(coa_valor_acrescimo) AS acrescimo,
  			SUM(coa_valor_total) - (SUM(coa_valor_original) + SUM(coa_valor_juro) - SUM(coa_valor_desconto) + SUM(coa_valor_acrescimo) - SUM(coa_valor_decrescimo)) AS outros
  		FROM
  		  tab_cobrancas
		  INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
		  INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
  		WHERE
  			tab_cobrancas.cot_id IN (2, 3, 4, 5, 9)".($_SESSION["origem"] ? " AND
			tab_clientes.clo_id = {$_SESSION["origem"]}" : "").($this->filtro ? " AND
			{$this->filtro}" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	if ($dados["outros"] > 0) {
    		$dados["acrescimo"] += $dados["outros"];
    	} elseif ($dados["outros"] < 0) {
    		$dados["decrescimo"] -= $dados["outros"];
    	}
    	if ($dados["juro"]) {
	    	$itens["Outros (Juro)"]["descricao"] = "Outros (Juro)";
	    	$itens["Outros (Juro)"]["valor"] = $dados["juro"];
    	}
    	if ($dados["decrescimo"]) {
	    	$itens["Outros (Decréscimo)"]["descricao"] = "Outros (Decréscimo)";
	    	$itens["Outros (Decréscimo)"]["valor"] = -$dados["decrescimo"];
    	}
    	if ($dados["acrescimo"]) {
	    	$itens["Outros (Acréscimo)"]["descricao"] = "Outros (Acréscimo)";
	    	$itens["Outros (Acréscimo)"]["valor"] = $dados["acrescimo"];
    	}
    }
  	// Dados Baixa
    $query = "
      SELECT
        tab_cobrancas_detalhes.cod_id AS detalhe_id,
        tab_cobrancas_detalhes.ass_id AS assinatura_id,
        tab_cobrancas_detalhes.ajf_id AS ajuste_id,
        tab_cobrancas_detalhes.cod_descricao AS descricao,
        tab_cobrancas_detalhes.cod_valor_final AS valor_final
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
		INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
      	tab_cobrancas_detalhes.cod_pai IS NULL".($_SESSION["origem"] ? " AND
			tab_clientes.clo_id = {$_SESSION["origem"]}" : "").($this->filtro ? " AND
			{$this->filtro}" : "");
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["valor_final"] += $this->buscaDetalhesFilhos($dados["detalhe_id"]);
    	if (substr($dados["descricao"], 0, 4) == "Juro") {
    		$dados["descricao"] = "Juro";
    	}
    	if ($dados["assinatura_id"]) {
    		$dados["descricao"] = $this->buscaServico($dados["assinatura_id"]);
    	} elseif ($dados["ajuste_id"]) {
    		$dados_ajuste = $this->buscaCategoriaAjuste($dados["ajuste_id"]);
    		if ($dados_ajuste["categoria_id"] == 13) {
    			$dados["descricao"] = $this->buscaServico($dados_ajuste["assinatura_id"]);
    		} else {
    		  if ($dados_ajuste["contrato_id"]) {
    			  $dados["descricao"] = $dados["descricao"]." » ".$this->buscaServicoContrato($dados_ajuste["contrato_id"]);
    		  }
    			$dados["descricao"] = $dados["descricao"]." » ".$this->buscaAjusteTipo($dados["ajuste_id"]);
    		}
    	} elseif ($dados["descricao"] == "Acréscimo" || $dados["descricao"] == "Decréscimo" || $dados["descricao"] == "Juro" || $dados["descricao"] == "Tarifa Bancária") {
    		$dados["descricao"] = "Outros ({$dados["descricao"]})";
    	}
    	$itens[$dados["descricao"]]["descricao"] = $dados["descricao"];
    	$itens[$dados["descricao"]]["valor"] += $dados["valor_final"];
    }
    sort($itens);
    for ($i = 0; $i < count($itens); $i++) {
    	$itens[$i]["valor"] = $this->formataDinheiro($itens[$i]["valor"]);
    }
  	$itens_geral["itens"] = $itens;
  	$itens_geral["campos"] = $this->buscaCampos();
  	$itens_geral["larguras"] = array(0, 200);
  	$itens_geral["alinhamentos"] = array("left", "right");
  	$itens_geral["total"] = array("valor" => "formataDinheiro");
    return $itens_geral;
  }

  function buscaCampos() {
    $campos["descricao"] = "Descrição";
    $campos["valor"] = "Valor";
    return $campos;
  }

  function geraFiltros() {
  	$this->filtro = array();
		// Lote
  	if ($_POST["lote"]) {
      $this->filtro[] = "tab_cobrancas.col_id = {$_POST["lote"]}";
    }
		// Formato
    if ($_POST["formato"]) {
      $this->filtro[] = "tab_cobrancas.cof_id = {$_POST["formato"]}";
    }
		// Formato Tipo
    if ($_POST["formato_tipo"]) {
      $this->filtro[] = "tab_cobrancas_formatos.cft_id = {$_POST["formato_tipo"]}";
    }
		// Status
    if ($_POST["status"]) {
      if ($_POST["status"] == "-1") $_POST["status"] = "2, 3";
      if ($_POST["status"] == "-2") $_POST["status"] = "4, 5, 6, 7, 9";
      if ($_POST["status"] == "-3") $_POST["status"] = "4, 5, 9";
      if ($_POST["status"] == "-4") $_POST["status"] = "2, 3, 4, 5, 9";
      $this->filtro[] = "tab_cobrancas.cot_id IN ({$_POST["status"]})";
    } else {
    	$this->filtro[] = "(tab_cobrancas.coa_pai IS NULL OR tab_cobrancas.cot_id  IN (2, 3, 4, 5, 9))";
    }
		// Data Geração
    if ($_POST["data_geracao_inicio"] && $_POST["data_geracao_termino"]) {
	    $_POST["data_geracao_inicio"] = $this->formataData($_POST["data_geracao_inicio"]);
	    $_POST["data_geracao_termino"] = $this->formataData($_POST["data_geracao_termino"]);
    	if ($_POST["data_geracao_inicio"] == $_POST["data_geracao_termino"]) {
	      $this->filtro[] = "tab_cobrancas.coa_data_geracao = '{$_POST["data_geracao_inicio"]}'";
    	} else {
	      $this->filtro[] = "tab_cobrancas.coa_data_geracao BETWEEN '{$_POST["data_geracao_inicio"]}' AND '{$_POST["data_geracao_termino"]}'";
    	}
    } elseif ($_POST["data_geracao_inicio"]) {
	    $_POST["data_geracao_inicio"] = $this->formataData($_POST["data_geracao_inicio"]);
      $this->filtro[] = "tab_cobrancas.coa_data_geracao >= '{$_POST["data_geracao_inicio"]}'";
    } elseif ($_POST["data_geracao_termino"]) {
	    $_POST["data_geracao_termino"] = $this->formataData($_POST["data_geracao_termino"]);
      $this->filtro[] = "tab_cobrancas.coa_data_geracao <= '{$_POST["data_geracao_inicio"]}'";
    }
		// Data Vencimento
    if ($_POST["data_vencimento_inicio"] && $_POST["data_vencimento_termino"]) {
	    $_POST["data_vencimento_inicio"] = $this->formataData($_POST["data_vencimento_inicio"]);
	    $_POST["data_vencimento_termino"] = $this->formataData($_POST["data_vencimento_termino"]);
    	if ($_POST["data_vencimento_inicio"] == $_POST["data_vencimento_termino"]) {
	      $this->filtro[] = "tab_cobrancas.coa_data_vencimento = '{$_POST["data_vencimento_inicio"]}'";
    	} else {
	      $this->filtro[] = "tab_cobrancas.coa_data_vencimento BETWEEN '{$_POST["data_vencimento_inicio"]}' AND '{$_POST["data_vencimento_termino"]}'";
    	}
    } elseif ($_POST["data_vencimento_inicio"]) {
	    $_POST["data_vencimento_inicio"] = $this->formataData($_POST["data_vencimento_inicio"]);
      $this->filtro[] = "tab_cobrancas.coa_data_vencimento >= '{$_POST["data_vencimento_inicio"]}'";
    } elseif ($_POST["data_vencimento_termino"]) {
	    $_POST["data_vencimento_termino"] = $this->formataData($_POST["data_vencimento_termino"]);
      $this->filtro[] = "tab_cobrancas.coa_data_vencimento <= '{$_POST["data_vencimento_inicio"]}'";
    }
		// Data Processamento
    if ($_POST["data_processamento_inicio"] && $_POST["data_processamento_termino"]) {
	    $_POST["data_processamento_inicio"] = $this->formataData($_POST["data_processamento_inicio"]);
	    $_POST["data_processamento_termino"] = $this->formataData($_POST["data_processamento_termino"]);
    	if ($_POST["data_processamento_inicio"] == $_POST["data_processamento_termino"]) {
      	$this->filtro[] = "tab_cobrancas.coa_data_processamento = '{$_POST["data_processamento_inicio"]}'";
    	} else {
      	$this->filtro[] = "tab_cobrancas.coa_data_processamento BETWEEN '{$_POST["data_processamento_inicio"]}' AND '{$_POST["data_processamento_termino"]}'";
    	}
    } elseif ($_POST["data_processamento_inicio"]) {
	    $_POST["data_processamento_inicio"] = $this->formataData($_POST["data_processamento_inicio"]);
      $this->filtro[] = "tab_cobrancas.coa_data_processamento >= '{$_POST["data_processamento_inicio"]}'";
    } elseif ($_POST["data_processamento_termino"]) {
	    $_POST["data_processamento_termino"] = $this->formataData($_POST["data_processamento_termino"]);
      $this->filtro[] = "tab_cobrancas.coa_data_processamento <= '{$_POST["data_processamento_inicio"]}'";
    }
		// Data Pagamento
    if ($_POST["data_pagamento_inicio"] && $_POST["data_pagamento_termino"]) {
	    $_POST["data_pagamento_inicio"] = $this->formataData($_POST["data_pagamento_inicio"]);
	    $_POST["data_pagamento_termino"] = $this->formataData($_POST["data_pagamento_termino"]);
    	if ($_POST["data_pagamento_inicio"] == $_POST["data_pagamento_termino"]) {
      	$this->filtro[] = "tab_cobrancas.coa_data_pagamento = '{$_POST["data_pagamento_inicio"]}'";
    	} else {
      	$this->filtro[] = "tab_cobrancas.coa_data_pagamento BETWEEN '{$_POST["data_pagamento_inicio"]}' AND '{$_POST["data_pagamento_termino"]}'";
    	}
    } elseif ($_POST["data_pagamento_inicio"]) {
	    $_POST["data_pagamento_inicio"] = $this->formataData($_POST["data_pagamento_inicio"]);
      $this->filtro[] = "tab_cobrancas.coa_data_pagamento >= '{$_POST["data_pagamento_inicio"]}'";
    } elseif ($_POST["data_pagamento_termino"]) {
	    $_POST["data_pagamento_termino"] = $this->formataData($_POST["data_pagamento_termino"]);
      $this->filtro[] = "tab_cobrancas.coa_data_pagamento <= '{$_POST["data_pagamento_inicio"]}'";
    }
  }

  function formataData($data) {
    return substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2);
  }


  function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
    if (!$valor && !$exibir_nulo) return false;
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }

  function buscaFiltros() {
    $filtros[] = array ("tipo" => "select", "nome" => "Cobranças » Lote", "name" => "lote", "funcao" => "buscaCobrancasLotes()");
    $filtros[] = array ("tipo" => "select", "nome" => "Cobranças » Formato", "name" => "formato", "funcao" => "buscaCobrancasFormatos()");
    $filtros[] = array ("tipo" => "select", "nome" => "Cobranças » Formato » Tipo", "name" => "formato_tipo", "funcao" => "buscaCobrancasFormatosTipos()");
    $filtros[] = array ("tipo" => "select", "nome" => "Cobranças » Status", "name" => "status", "funcao" => "buscaCobrancasStatus()");
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data Geração", "name1" => "data_geracao_inicio", "name2" => "data_geracao_termino", "where" => "tab_cobrancas.coa_data_geracao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data Vencimento", "name1" => "data_vencimento_inicio", "name2" => "data_vencimento_termino", "where" => "tab_cobrancas.coa_data_vencimento");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data Processamento", "name1" => "data_processamento_inicio", "name2" => "data_processamento_termino", "where" => "tab_cobrancas.coa_data_processamento");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data Pagamento", "name1" => "data_pagamento_inicio", "name2" => "data_pagamento_termino", "where" => "tab_cobrancas.coa_data_pagamento");
    return $periodos;
  }

  ###################
  ### Busca Dados ###
  ###################

  function buscaAjusteTipo($ajuste_id) {
  	$query = "
  	  SELECT
  	    tab_ajustes_financeiros_tipos.aft_nome AS tipo
  	  FROM
  	  	tab_ajustes_financeiros
  	  	INNER JOIN tab_ajustes_financeiros_tipos ON (tab_ajustes_financeiros.aft_id = tab_ajustes_financeiros_tipos.aft_id)
  	  WHERE
  	  	tab_ajustes_financeiros.ajf_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "tipo");
  }

  function buscaDetalhesFilhos($detalhe_id) {
    $query = "
      SELECT
        SUM(cod_valor_final) AS valor_final
      FROM
        tab_cobrancas_detalhes
      WHERE
        cod_pai = $detalhe_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "valor_final");
  }

  function buscaServico($assinatura_id) {
    $query = "
      SELECT
        tab_planos_servicos.pls_nome AS servico
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_assinaturas.ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    return "Serviço (".$this->bancodedados->resultado($query, 0, "servico").")";
  }

  function buscaServicoContrato($contrato_id) {
    $query = "
      SELECT
        tab_planos_servicos.pls_nome AS servico
      FROM
        tab_contratos
        INNER JOIN tab_planos_servicos ON (tab_contratos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_contratos.cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "servico");
  }

  function buscaCategoriaAjuste($ajuste_id) {
    $query = "
      SELECT
        afc_id AS categoria_id,
        ass_id AS assinatura_id,
        cor_id AS contrato_id
      FROM
        tab_ajustes_financeiros
      WHERE
        ajf_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
		return $this->bancodedados->resultadoArray($query);
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

}

?>
