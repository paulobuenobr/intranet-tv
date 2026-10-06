<?

class relatorios_recebimentos {

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
        IF(tab_cobrancas.cot_id IN (2, 3), IF(tab_cobrancas.cof_id IN (1, 8), 'Boleto Bancário (HSBC)', IF(tab_cobrancas.cof_id IN (7, 9), 'Boleto Bancário (Santander)', IF(tab_cobrancas.cof_id IN (10, 11), 'Boleto Bancário (Caixa)', IF(tab_cobrancas.cof_id IN (14, 15, 16), 'Boleto Bancário (Santander Tec Service)', cof_nome)))), IF(tab_cobrancas.cot_id = 4, 'Pagos na Empresa', IF(tab_cobrancas.cot_id = 5, 'Pagos via Depósito', IF(tab_cobrancas.cot_id = 9, 'Assistência Técnica', 'Outros')))) AS formato,
        DATE_FORMAT(IF(tab_cobrancas_original.coa_id IS NULL, tab_cobrancas.coa_data_vencimento, tab_cobrancas_original.coa_data_vencimento), '%Y%m') AS vencimento,
        DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%Y%m') AS pagamento,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS quantidade,
        SUM(tab_cobrancas.coa_valor_total) AS valor
      FROM
        tab_cobrancas
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
        LEFT OUTER JOIN tab_cobrancas tab_cobrancas_original ON (tab_cobrancas.coa_pai = tab_cobrancas_original.coa_id)
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
      	formato,
      	tab_cobrancas.coa_data_vencimento,
      	tab_cobrancas.coa_data_pagamento
      ORDER BY
        vencimento ASC,
        formato ASC";
    return $consulta;
  }

  function buscaCampos() {
  }

  function buscaFiltros() {
    $filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "status", "funcao" => "", "where" => "tab_cobrancas.cot_id");
    $filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "valor_total", "funcao" => "", "where" => "tab_cobrancas.coa_valor_total");
    $filtros[] = array ("tipo" => "select", "nome" => "Origem", "name" => "origem", "funcao" => "buscaOrigens()", "where" => "tab_clientes.clo_id");
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodoPorMes", "nome" => "Vencimento", "name1" => "venc_inicio", "name2" => "venc_termino", "where" => "tab_cobrancas.coa_data_vencimento");
    $periodos[] = array ("tipo" => "periodoPorMes", "nome" => "Processamento", "name1" => "proc_inicio", "name2" => "proc_termino", "where" => "tab_cobrancas.coa_data_processamento");
    $periodos[] = array ("tipo" => "periodoPorMes", "nome" => "Pagamento", "name1" => "pag_inicio", "name2" => "pag_termino", "where" => "tab_cobrancas.coa_data_pagamento");
    return $periodos;
  }

  ###################
  ### Busca Dados ###
  ###################

  function buscaFormatos () {
    $query = "SELECT cof_id AS id, cof_nome AS nome FROM tab_cobrancas_formatos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas[$dados["id"]] = $dados["nome"];
    }
    return $cobrancas;
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

  ######################
  ### Funções Gerais ###
  ######################

  function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
    if (!$valor && !$exibir_nulo) return "";
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }

  function agrupaItensRecebimentos($itens, $datas, $formatos) {
    $chaves_vencimento = $this->criaChaves($datas["venc_inicio"], $datas["venc_termino"]);
    $chaves_processamento = $this->criaChaves($datas["proc_inicio"], $datas["proc_termino"]);
    $campos = $this->CriaCampos($chaves_processamento);
    $i = 0;
    foreach ($itens as $chave_formato => $item) {
      foreach ($chaves_vencimento as $chave_venc => $venc) {
        $itens_tratados[$i]["contador"] = $i + 1;
        $itens_tratados[$i]["formato"] = "<div style=\"text-align:left\">$formatos[$chave_formato]</div>";
        $itens_tratados[$i]["vencimento"] = $venc;
        foreach ($chaves_processamento as $chave_proc => $proc) {
          $item_atual = $item[$chave_venc][$chave_proc];
          $itens_tratados[$i]["quantidade$chave_proc"] = "<div style=\"text-align:right\">".$item_atual["quantidade"]."</div>";
          $itens_tratados[$i]["valor$chave_proc"] = "<div style=\"text-align:right\">".$this->formataDinheiro($item_atual["valor"])."</div>";
          if ($item_atual["valor"]) {
            $total_linhas[$i]["valor"] += $item_atual["valor"];
            $total_colunas[$chave_proc]["valor"] += $item_atual["valor"];
          }
          if ($item_atual["quantidade"]) {
            $total_linhas[$i]["quantidade"] += $item_atual["quantidade"];
            $total_colunas[$chave_proc]["quantidade"] += $item_atual["quantidade"];
          }
        }
        if ($total_linhas[$i]) {
          $itens_tratados[$i]["quantidadeTotal"] = "<div style=\"text-align:right\"><b>".$total_linhas[$i]["quantidade"]."</b></div>";
          $itens_tratados[$i]["valorTotal"] = "<div style=\"text-align:right\"><b>".$this->formataDinheiro($total_linhas[$i]["valor"])."</b></div>";
          $i++;
        }
      }
    }

    // Gera última linha com os totais
    if ($total_colunas) {
      foreach ($total_colunas as $chave => $total_coluna) {
        $itens_tratados[$i]["contador"] = $i + 1;
        $itens_tratados[$i]["formato"] = "<div style=\"text-align:left\"><b>Total Geral</b></div>";
        $itens_tratados[$i]["quantidade$chave"] = "<div style=\"text-align:right\"><b>".$total_coluna["quantidade"]."</b></div>";
        $itens_tratados[$i]["valor$chave"] = "<div style=\"text-align:right\"><b>".$this->formataDinheiro($total_coluna["valor"])."</b></div>";
        $total_geral["quantidade"] += $total_coluna["quantidade"];
        $total_geral["valor"] += $total_coluna["valor"];
      }
    }
    $itens_tratados[$i]["quantidadeTotal"] = "<div style=\"text-align:right\"><b>{$total_geral["quantidade"]}</b></div>";
    $itens_tratados[$i]["valorTotal"] = "<div style=\"text-align:right\"><b>".$this->formataDinheiro($total_geral["valor"])."</b></div>";

    return array("itens" => $itens_tratados, "campos" => $campos);
  }

  function criaChaves($inicio, $termino) {
    $inicio = substr($inicio, 3, 4).substr($inicio, 0, 2);
    $termino = substr($termino, 3, 4).substr($termino, 0, 2);
    $igual = false;
    while (!$igual) {
      if ($inicio == $termino) $igual = true;
      $chaves[$inicio] = substr($inicio, 4, 2). "/" .substr($inicio, 0, 4);
      $inicio += 1;
      if (substr($inicio, 4, 2) > 12) $inicio += 88;
    }
    return $chaves;
  }

  function criaCampos($chaves) {
    $campos = array("contador" => "#", "formato" => "Formato", "vencimento" => "Vencimento");
    foreach ($chaves as $chave => $valor) {
      $campos["Paga em $valor"] = array("quantidade$chave" => "Qtde.", "valor$chave" => "Valor");
    }
    $campos["Total Geral"] = array("quantidadeTotal" => "Qtde.", "valorTotal" => "Valor");
    return $campos;
  }

}

?>
