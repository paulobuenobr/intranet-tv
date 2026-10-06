<?

class relatorios_comissoes {

  var $bancodedados;
  var $permissoes;

  function __construct($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }

  #######################
  ### Funções - Geral ###
  #######################

  function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
    if (!$valor && !$exibir_nulo) return false;
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }

  ################
  ### Consulta ###
  ################

  function buscaConsulta($agrupamento) {
    $consulta = "
      SELECT
        tab_vendas.ven_id AS venda_id,
        tab_vendas.cor_id AS contrato_id,
        [campos_retorno]
      FROM
        tab_vendas
        INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
        INNER JOIN tab_contratos ON (tab_contratos.cor_id = tab_vendas.cor_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_vendas_comissoes ON (tab_vendas_comissoes.ven_id = tab_vendas.ven_id)
        LEFT OUTER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes_tipos.vct_id = tab_vendas_comissoes.vct_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
      WHERE
        tab_contratos.crs_id >= 15".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")." AND
        [campos_filtro]
      ORDER BY".($agrupamento ? "
        tab_usuarios.usu_nome,
        tab_vendas_comissoes_tipos.vct_nome" : "
        tab_vendas.ven_datahora");
    return $consulta;
  }

  ##############
  ### Campos ###
  ##############

  function buscaCampos() {
    $campos[] = array ("categoria" => "Dados Gerais", "nome" => "Comissionado", "campo" => "comissionado", "tabela" => "tab_usuarios.usu_nome AS comissionado, ");
    $campos[] = array ("categoria" => "Dados Gerais", "nome" => "Tipo", "campo" => "comissao_tipo", "tabela" => "tab_vendas_comissoes_tipos.vct_nome AS comissao_tipo, ");
    $campos[] = array ("categoria" => "Dados Gerais", "nome" => "Valor", "campo" => "comissao_valor", "tabela" => "tab_vendas_comissoes.vec_valor AS comissao_valor, ");
    $campos[] = array ("categoria" => "Dados Gerais", "nome" => "Geração", "campo" => "geracao", "tabela" => "DATE_FORMAT(tab_vendas.ven_datahora, '%d/%m/%Y') AS geracao, ");
    $campos[] = array ("categoria" => "Dados Gerais", "nome" => "Primeiro Pagto", "campo" => "primeiro_pagamento", "tabela" => "DATE_FORMAT(tab_vendas.ven_primeiro_pagamento, '%d/%m/%Y') AS primeiro_pagamento, ");

    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "ID Cliente", "campo" => "cliente_id", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Cliente", "campo" => "cliente", "tabela" => "tab_clientes.cli_nome AS cliente, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Condomínio", "campo" => "condominio", "tabela" => "tab_condominios.con_nome AS condominio, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Contrato", "campo" => "contrato", "tabela" => "tab_contratos.cor_codigo AS contrato, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Status do Contrato", "campo" => "contrato_status", "tabela" => "tab_contratos.crs_id AS contrato_status, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Serviço / Plano", "campo" => "servico_plano", "tabela" => "tab_assinaturas.pla_id AS servico_plano, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Vendedor", "campo" => "vendedor", "tabela" => "tab_vendas.usu_id AS vendedor, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Fechamento", "campo" => "fechamento", "tabela" => "DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%d/%m/%Y') AS fechamento, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Ativação", "campo" => "ativacao", "tabela" => "DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS ativacao, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Pontuação", "campo" => "pontuacao", "tabela" => "tab_vendas.ven_pontuacao AS pontuacao, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Valor Venda", "campo" => "valor_venda", "tabela" => "tab_assinaturas.ass_id AS assinatura_id, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Valor Mensalidade", "campo" => "valor_mensalidade", "tabela" => "tab_assinaturas.ass_id AS assinatura_id, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Valor Mensalidade (Final)", "campo" => "valor_mensalidade_final", "tabela" => "tab_assinaturas.ass_id AS assinatura_id, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Valor Instalação", "campo" => "valor_instalacao", "tabela" => "tab_assinaturas.ass_id AS assinatura_id, ");
    $campos[] = array ("categoria" => "Dados da Venda", "nome" => "Valor Instalação (Final)", "campo" => "valor_instalacao_final", "tabela" => "tab_assinaturas.ass_id AS assinatura_id, ");

    $campos[] = array ("categoria" => "Comissionado Agrupado Por", "nome" => "Tipo", "campo" => "agrupamento_tipo", "onclick" => "mudaAgrupamento(this)");
    $campos[] = array ("categoria" => "Comissionado Agrupado Por", "nome" => "Serviço / Plano", "campo" => "agrupamento_servico_plano", "onclick" => "mudaAgrupamento(this)");

    for ($i = 0; $i < sizeof($campos); $i++) {
      $retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"], "onclick" => $campos[$i]["onclick"]);
    }
    return $retorno;
  }

  ###############
  ### Filtros ###
  ###############

  function buscaFiltros() {
    if ($this->permissoes["relatorios"]["comissoes_plus"]) {
      $filtros[] = array ("tipo" => "select", "nome" => "Comissionado", "name" => "comissionado", "funcao" => "buscaComissionados()", "where" => "tab_usuarios.usu_id");
    } else {
      $filtros[] = array ("tipo" => "extra", "nome" => "Comissionado", "name" => "comissionado", "funcao" => "buscaComissionados()", "where" => "tab_usuarios.usu_id =");
    }
    $filtros[] = array ("tipo" => "select", "nome" => "Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_clientes.con_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Serviço / Plano", "name" => "servico_plano", "funcao" => "buscaServicosPlanos()", "where" => "tab_assinaturas.pla_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Status do Contrato", "name" => "contrato_status", "funcao" => "buscaContratoStatus()", "where" => "tab_contratos.crs_id");
    return $filtros;
  }

  ################
  ### Periodos ###
  ################

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data de Fechamento", "name1" => "data_fechamento_inicio", "name2" => "data_fechamento_termino", "where" => "tab_assinaturas.ass_data_fechamento");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data de Ativação", "name1" => "data_ativacao_inicio", "name2" => "data_ativacao_termino", "where" => "tab_assinaturas.ass_data_ativacao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Data de Primeiro Pagamento", "name1" => "data_primeiro_pagamento_inicio", "name2" => "data_primeiro_pagamento_termino", "where" => "tab_vendas.ven_primeiro_pagamento");
    return $periodos;
  }

  ##################
  ### Intervalos ###
  ##################

  function buscaIntervalos() {
  }

  ######################
  ### Funções Gerais ###
  ######################

  function agrupaItensComissoes($itens, $agrupamento) {
    $vendas_computadas = array();
    foreach ($itens as $item) {
      $itens_agrupados_total["Total Geral"][$item[$agrupamento]] += intval(preg_replace("/[^0-9]/", "", $item["comissao_valor"]));
      if (!in_array($item["venda_id"], $vendas_computadas)) {
        $itens_agrupados_total["Total Geral"]["Pontuação"] += $item["pontuacao"];
      }
      $itens_agrupados_total["Total Geral"]["Total Geral"] += intval(preg_replace("/[^0-9]/", "", $item["comissao_valor"]));
      $itens_agrupados[$item["comissionado"]][$item[$agrupamento]] += intval(preg_replace("/[^0-9]/", "", $item["comissao_valor"]));
      if (!in_array($item["venda_id"], $vendas_computadas)) {
        $itens_agrupados[$item["comissionado"]]["Pontuação"] += $item["pontuacao"];
      }
      $itens_agrupados[$item["comissionado"]]["Total Geral"] += intval(preg_replace("/[^0-9]/", "", $item["comissao_valor"]));
      $agrupamentos[] = $item[$agrupamento];
      $vendas_computadas[] = $item["venda_id"];
    }

    $itens_agrupados = funcoes::mescla_arrays($itens_agrupados, $itens_agrupados_total);

    $agrupamentos = array_unique($agrupamentos);
    $agrupamentos[] = "Pontuação";
    $agrupamentos[] = "Total Geral";
    $itens_tratados = array();
    foreach ($itens_agrupados as $comissionado => $item_agrupado) {
      $item_tratado["contador"] = sizeof($itens_tratados) + 1;
      $item_tratado["comissionado"] = ($comissionado == "Total Geral") ? "<b>$comissionado</b>" : $comissionado;
      foreach ($agrupamentos as $agrupamento) {
        if ($agrupamento != "Pontuação") {
          $item_tratado[$agrupamento] = $this->formataDinheiro($item_agrupado[$agrupamento]);
        } else {
          $item_tratado[$agrupamento] = $item_agrupado[$agrupamento];
        }
        if ($agrupamento == "Total Geral" || $comissionado == "Total Geral") {
          $item_tratado[$agrupamento] = "<b>".$item_tratado[$agrupamento]."</b>";
        }
      }
      $itens_tratados[] = $item_tratado;
    }
    $campos["contador"] = "#";
    $campos["comissionado"] = "Comissionado";
    foreach ($agrupamentos as $agrupamento) {
      $campos[$agrupamento] = $agrupamento;
    }
    return array("itens" => $itens_tratados, "campos" => $campos);
  }

  ###################
  ### Busca Dados ###
  ###################

  function buscaCondominios() {
    $query = "
      SELECT
        con_id AS id,
        CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, '/', tab_bairros.bai_nome, ')') AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      WHERE
        cos_id IS NOT NULL
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }

  function buscaComissionados() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
		uss_id = 1 AND
      	(SELECT COUNT(*) FROM tab_vendas WHERE tab_usuarios.usu_id = tab_vendas.usu_id) > 0".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $comissionados[$dados["id"]] = $dados["nome"];
    }
    return $comissionados;
  }

  function buscaServicosPlanos() {
    $query = "
      SELECT DISTINCT
        tab_planos.pla_id AS id,
        CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_planos.pla_nome) AS nome
      FROM
        tab_planos
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servicos_planos[$dados["id"]] = $dados["nome"];
    }
    return $servicos_planos;
  }

  function buscaContratoStatus() {
    $query = "
      SELECT DISTINCT
        crs_id AS id,
        crs_nome AS nome
      FROM
        tab_contratos_status
      WHERE
        crs_id >= 11
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    $status["-1"] = "Ativo";
    $status["-2"] = "Em Aberto";
    $status["-3"] = "Inativo";
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaInicio($campo, $inicio) {
    if ($inicio) {
      $inicio = substr($inicio, 6, 4).substr($inicio, 3, 2).substr($inicio, 0, 2);
      $inicio = "$campo >= $inicio";
    } else {
      $inicio = 1;
    }
    return $inicio;
  }

  function buscaTermino($campo, $termino) {
    if ($termino) {
      $termino = substr($termino, 6, 4).substr($termino, 3, 2).substr($termino, 0, 2);
      $termino = "$campo <= $termino";
    } else {
      $termino = 1;
    }
    return $termino;
  }

  function buscaUsuarioNome($usuario_id) {
    $query = "
      SELECT
        usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

  function buscaServicoPlano($plano_id) {
    $query = "
      SELECT DISTINCT
        CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_planos.pla_nome) AS nome
      FROM
        tab_planos
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_planos.pla_id = $plano_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

  function buscaContratoStatusNome($contrato_status_id) {
    $query = "
      SELECT
        crs_nome AS nome
      FROM
        tab_contratos_status
      WHERE
        crs_id = $contrato_status_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }
  
  function buscaPrimeiroPagamento($assinatura_id, $contrato_id) {
  	$query = "
  	  SELECT
  	    DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS pagamento
  	  FROM
  	    tab_ajustes_financeiros
  	    INNER JOIN tab_cobrancas_detalhes ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes.ajf_id)
  	    INNER JOIN tab_cobrancas ON (tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id)
  	  WHERE
  	    tab_ajustes_financeiros.cor_id = $contrato_id AND
  	    tab_ajustes_financeiros.afc_id = 19 AND
  	    tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)
  	  ORDER BY
  	    tab_cobrancas.coa_data_pagamento
  	  LIMIT 
  	  	0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["pagamento"];
    }
  	$query = "
  	  SELECT
  	    DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS pagamento
  	  FROM
  	    tab_cobrancas_detalhes
  	    INNER JOIN tab_cobrancas ON (tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id)
  	  WHERE
  	    tab_cobrancas_detalhes.ass_id = $assinatura_id AND
  	    tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)
  	  ORDER BY
  	    tab_cobrancas.coa_data_pagamento
  	  LIMIT 
  	  	0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["pagamento"];
    }
  }
  
  function buscaValorVenda($assinatura_id, $tipo = "mensalidade", $venda = false, $migracao = false, $final = false) {
    $query = "
      SELECT
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ass_migrada AS assinatura_migrada_id,
        tab_assinaturas.ass_$tipo AS $tipo
      FROM
        tab_assinaturas
     WHERE
  	    tab_assinaturas.ass_id = $assinatura_id
  	 GROUP BY
  	    tab_assinaturas.ass_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($final) {
        $dados[$tipo] -= $this->buscaDescontos($dados["assinatura_id"], $dados[$tipo], $tipo);
      }
      if ($venda && $tipo == "mensalidade" && $dados["assinatura_migrada_id"] && !$migracao) {
      	$dados[$tipo] -= $this->buscaValorVenda($dados["assinatura_migrada_id"], $tipo, true, true);
      }
      if ($migracao) {
	      return $dados[$tipo];
      } else {
	      return $this->formataDinheiro($dados[$tipo], true);
      }
    }
  }

  function buscaDescontos($assinatura_id, $mensalidade, $tipo) {
    $query = "
      SELECT
        SUM(IF(dec_$tipo <= 1, dec_$tipo * $mensalidade, dec_$tipo)) AS $tipo
      FROM
        tab_descontos_concessoes
      WHERE
        ass_id = $assinatura_id AND
        dec_$tipo > 0 AND
        dec_validade IS NULL";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, $tipo);
  }

}

?>
