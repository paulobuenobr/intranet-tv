<?

class relatorios_recuperacoes {

  var $bancodedados;
  var $permissoes;

  function __construct ($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }


  ####################################
  ########## Funções Gerais ##########
  ####################################

  function buscaConsulta() {
    $consulta = "
      SELECT
        tab_cobrancas.coa_id AS cobranca_id,
        tab_cobrancas.cot_id AS cobranca_status_id,
        [campos_retorno]
      FROM
        tab_clientes
        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
        INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
        INNER JOIN tab_operacoes_contatos ON (tab_clientes.cli_id = tab_operacoes_contatos.cli_id)
        INNER JOIN tab_operacoes ON (tab_operacoes_contatos.ope_id = tab_operacoes.ope_id)
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")." AND
        tab_cobrancas.coa_pai IS NULL AND
        tab_cobrancas.coa_data_vencimento < DATE_FORMAT(tab_operacoes_contatos.opc_inicio, '%Y-%m-%d') AND
        tab_cobrancas.coa_data_processamento >= DATE_FORMAT(tab_operacoes_contatos.opc_inicio, '%Y-%m-%d') AND
        -- tab_cobrancas.coa_data_processamento >= DATE_FORMAT(DATE_ADD(tab_cobrancas.coa_data_vencimento, INTERVAL 3 MONTH), '%Y-%m-01') AND
        tab_cobrancas.cot_id IN (2, 3, 4, 5, 11) AND
        -- tab_operacoes_lotes.opl_recuperacao_vencimento = tab_cobrancas.coa_data_vencimento AND
        tab_operacoes_contatos.occ_id IN (1, 5) AND
        tab_operacoes_contatos.usu_id = (SELECT `contatos`.usu_id FROM tab_operacoes_contatos `contatos` WHERE `contatos`.cli_id = tab_clientes.cli_id AND DATE_FORMAT(`contatos`.opc_inicio, '%Y-%m-%d') <= tab_cobrancas.coa_data_processamento AND DATE_FORMAT(`contatos`.opc_inicio, '%Y-%m-%d') > tab_cobrancas.coa_data_vencimento AND `contatos`.occ_id IN (1, 5) ORDER BY opc_inicio DESC LIMIT 1)
      GROUP BY
        tab_cobrancas.coa_id
      ORDER BY
        tab_cobrancas.coa_data_processamento,
        tab_clientes.cli_nome";
    return $consulta;
  }

  function buscaCampos() {
    $campos[] = array ("categoria" => "Cliente", "nome" => "ID", "campo" => "cliente_id", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Nome", "campo" => "cliente_nome", "tabela" => "tab_clientes.cli_nome AS cliente_nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Origem", "campo" => "cliente_origem", "tabela" => "tab_clientes_origens.clo_nome AS cliente_origem, ");
//    $campos[] = array ("categoria" => "Cliente", "nome" => "Status", "campo" => "cliente_status", "tabela" => "tab_clientes.cls_id AS cliente_status, ");

    $campos[] = array ("categoria" => "Cobrança", "nome" => "Código", "campo" => "codigo", "tabela" => "tab_cobrancas.coa_codigo AS codigo, ");
    $campos[] = array ("categoria" => "Cobrança", "nome" => "Data Vencimento", "campo" => "data_vencimento", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento, ");
    $campos[] = array ("categoria" => "Cobrança", "nome" => "Data Processamento", "campo" => "data_processamento", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_processamento, '%d/%m/%Y') AS data_processamento, ");
//    $campos[] = array ("categoria" => "Cobrança", "nome" => "Data Pagamento", "campo" => "data_pagamento", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento, ");
//    $campos[] = array ("categoria" => "Cobrança", "nome" => "Data Crédito", "campo" => "data_credito", "tabela" => "DATE_FORMAT(tab_cobrancas.coa_data_credito, '%d/%m/%Y') AS data_credito, ");
    $campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Original", "campo" => "valor_original", "tabela" => "tab_cobrancas.coa_valor_original AS valor_original, ");
//    $campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Juro", "campo" => "valor_juro", "tabela" => "tab_cobrancas.coa_valor_juro AS valor_juro, ");
//    $campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Desconto", "campo" => "valor_desconto", "tabela" => "tab_cobrancas.coa_valor_desconto AS valor_desconto, ");
//    $campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Acréscimo", "campo" => "valor_acrescimo", "tabela" => "tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo, ");
//    $campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Decréscimo", "campo" => "valor_decrescimo", "tabela" => "tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo, ");
    $campos[] = array ("categoria" => "Cobrança", "nome" => "Valor Total", "campo" => "valor_total", "tabela" => "tab_cobrancas.coa_valor_total AS valor_total, ");
    $campos[] = array ("categoria" => "Cobrança", "nome" => "Status", "campo" => "cobranca_status", "tabela" => "tab_cobrancas.cot_id AS cobranca_status, ");

    $campos[] = array ("categoria" => "Operação", "nome" => "Lote", "campo" => "lote", "tabela" => "tab_operacoes_lotes.opl_nome AS lote, ");
    $campos[] = array ("categoria" => "Operação", "nome" => "Data/Hora Contato", "campo" => "data_hora_contato", "tabela" => "DATE_FORMAT(tab_operacoes_contatos.opc_inicio, '%d/%m/%Y (%H:%i)') AS data_hora_contato, ");
    $campos[] = array ("categoria" => "Operação", "nome" => "Responsável", "campo" => "responsavel", "tabela" => "tab_operacoes_contatos.usu_id AS responsavel, ");

    for ($i = 0; $i < sizeof($campos); $i++) {
      $retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"]);
    }
    return $retorno;
  }

  function buscaFiltros() {
//    $filtros[] = array ("tipo" => "select", "nome" => "Lote", "name" => "lote", "funcao" => "buscaLotes()", "where" => "tab_operacoes_lotes.opl_id");
    if ($this->permissoes["relatorios"]["recuperacoes_plus"]) {
      $filtros[] = array ("tipo" => "select", "nome" => "Responsável", "name" => "responsavel", "funcao" => "buscaResponsaveis()", "where" => "tab_operacoes_contatos.usu_id");
    } else {
      $filtros[] = array ("tipo" => "extra", "nome" => "Responsável", "name" => "responsavel", "funcao" => "", "where" => "tab_operacoes_contatos.usu_id =");
    }
	if (!$_SESSION["origem"]) {
		$filtros[] = array ("tipo" => "select", "nome" => "Origem", "name" => "origem", "funcao" => "buscaOrigens()", "where" => "tab_clientes_origens.clo_id =");
	}
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Vencimento", "name1" => "vencimento_inicio", "name2" => "vencimento_termino", "where" => "tab_cobrancas.coa_data_vencimento");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Processamento", "name1" => "processamento_inicio", "name2" => "processamento_termino", "where" => "tab_cobrancas.coa_data_processamento");
    return $periodos;
  }

  ####################################
  ########### Busca Dados ############
  ####################################

  function buscaLotes() {
    $query = "SELECT opl_id AS id, opl_nome AS nome FROM tab_operacoes_lotes WHERE oli_id = 2 ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lotes[$dados["id"]] = $dados["nome"];
    }
    return $lotes;
  }

  function buscaResponsaveis() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN tab_operacoes_contatos ON (tab_usuarios.usu_id = tab_operacoes_contatos.usu_id)
        INNER JOIN tab_operacoes ON (tab_operacoes_contatos.ope_id = tab_operacoes.ope_id)
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)
      WHERE
        tab_operacoes_lotes.oli_id = 2".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }


  function buscaOrigens() {
	  $origens = array();
    $query = "
      SELECT DISTINCT
        clo_id AS id,
        clo_nome AS nome
      FROM
        tab_clientes_origens
      WHERE
        clo_id > 0
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[$dados["id"]] = $dados["nome"];
    }
    return $origens;
  }

  function buscaCobrancaOriginal($cobranca_id) {
    $query = "
      SELECT
        coa_valor_original AS valor_original,
        coa_valor_juro AS valor_juro,
        coa_valor_desconto AS valor_desconto,
        coa_valor_acrescimo AS valor_acrescimo,
        coa_valor_decrescimo AS valor_decrescimo,
        coa_valor_total AS valor_total
      FROM
        tab_cobrancas
      WHERE
        coa_pai = $cobranca_id AND
        cot_id IN (2, 3, 4, 5, 9)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaClientesStatus() {
    $query = "SELECT cls_id AS id, cls_nome AS nome FROM tab_clientes_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $clientes_status[$dados["id"]] = $dados["nome"];
    }
    return $clientes_status;
  }

  function buscaCobrancasStatus() {
    $query = "SELECT cot_id AS id, cot_nome AS nome FROM tab_cobrancas_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas_status[$dados["id"]] = $dados["nome"];
    }
    return $cobrancas_status;
  }

}

?>
