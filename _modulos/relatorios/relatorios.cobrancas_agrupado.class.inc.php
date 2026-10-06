<?

class relatorios_cobrancas_agrupado {

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
      SELECT
        tab_cobrancas.coa_id AS id,
        tab_cobrancas.coa_valor_original AS valor_original,
        tab_cobrancas.coa_valor_juro AS valor_juro,
        tab_cobrancas.coa_valor_desconto AS valor_desconto,
        tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo,
        tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo,
        tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
        tab_cobrancas.coa_valor_total AS valor_total,
        tab_cobrancas.cot_id AS status_id,
        tab_cobrancas.cli_id AS cliente_id,
        IF(tab_cobrancas.coa_data_vencimento > tab_cobrancas.coa_data_pagamento, 1, 0) AS atrasada
      FROM
        tab_cobrancas
		INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
        [joins_filtro]
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    return $consulta;
  }

  function buscaCampos() {
  }

  function buscaFiltros() {
    $filtros[] = array ("tipo" => "select", "nome" => "Lote", "name" => "lote", "funcao" => "buscaCobrancasLotes()", "where" => "tab_cobrancas.col_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Formato", "name" => "formato", "funcao" => "buscaCobrancasFormatos()", "where" => "tab_cobrancas.cof_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Formato » Tipo", "name" => "formato_tipo", "funcao" => "buscaCobrancasFormatosTipos()", "joins" => array("INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)"));
    $filtros[] = array ("tipo" => "select", "nome" => "Tipo", "name" => "tipo", "funcao" => "buscaCobrancasTipos()");
    $filtros[] = array ("tipo" => "select", "nome" => "Status", "name" => "status", "funcao" => "buscaCobrancasStatus()");
    $filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_clientes.con_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Origem", "name" => "origem", "funcao" => "buscaOrigens()", "where" => "tab_clientes.clo_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Status", "name" => "cliente_status", "funcao" => "buscaClientesStatus()", "where" => "tab_clientes.cls_id");
    $filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "col_id", "funcao" => "", "where" => "");
    $filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "coa_pai", "funcao" => "", "where" => "tab_cobrancas.coa_pai");
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodoPorMes", "nome" => "Competência", "name1" => "competencia_inicio", "name2" => "competencia_termino", "where" => "tab_cobrancas_detalhes.cod_competencia_inicio", "joins" => array("INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)"));
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
    $cobrancas_formatos_tipos["2, 3"] = "Boletos";
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

  function buscaOrigens() {
    $query = "SELECT clo_id AS id, clo_nome AS nome FROM tab_clientes_origens WHERE 1".($_SESSION["origem"] ? " AND
				clo_id = {$_SESSION["origem"]}" : "")." ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[$dados["id"]] = $dados["nome"];
    }
    return $origens;
  }
  
  ###################
  ### Busca Itens ###
  ###################
  
  function buscaItens($query) {
    $retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, $this->permissoes, false);
    return $retornos->buscaRegistrosAgrupados(null, $query);
  }
  
  function buscaCamposListagem() {
    $campos["descricao"] = "Descrição";
    $campos["recebido_valor"] = "Valor";
    return $campos;
  }

}

?>
