<?

class relatorios_faturamento_geral {

  var $bancodedados;
  var $permissoes;

  function __construct($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    set_time_limit(0);
  }

  ######################
  ### Funções Gerais ###
  ######################

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "MesAno", "nome" => "Vencimento", "name" => "vencimento", "where" => "DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%Y%m%d')");
    return $periodos;
  }

  function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
    if (!$valor && !$exibir_nulo) return "";
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }

  function formataVencimento($vencimento, $formato = "Y-m-d") {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    if ($formato == "Y-m-d") {
      $vencimento = @explode("-", $vencimento);
      return $meses[intval($vencimento[1])]."/".$vencimento[0];
    } elseif ($formato == "m/Y") {
      $vencimento = @explode("/", $vencimento);
      return $meses[intval($vencimento[0])]."/".$vencimento[1];
    }
  }

  function buscaCampos($vencimento) {
    $campos["descricao"] = "Descrição";
    $campos = array_merge($campos, $this->buscaOrigens());
    $campos = array_merge($campos, array("Total" => array("qtd_total" => "Qtd", "valor_total" => "Valor")));
    return $campos;
  }

  function formataData($vencimento) {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    $vencimento = @explode("/", $vencimento);
    return $meses[intval($vencimento[0])]."/".$vencimento[1];
  }

  function buscaOrigens() {
    $query = "
      SELECT
        clo_id AS id,
        clo_nome AS nome
      FROM
        tab_clientes_origens
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[$dados["nome"]]["qtd_{$dados["id"]}"] = "Qtd";
      $origens[$dados["nome"]]["valor_{$dados["id"]}"] = "Valor";
    }
    return $origens;
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaItens($vencimento) {
    $vencimento_inicial = date('Y-m-d', strtotime(substr($vencimento, -4)."-".substr($vencimento, 0, 2)."-"."01"));
    $vencimento_final = date('Y-m-t', strtotime(substr($vencimento, -4)."-".substr($vencimento, 0, 2)."-"."01"));
    // Valor Faturado
    $itens = $this->buscaValorFaturado($vencimento, $vencimento_inicial, $vencimento_final);
    // Valor Recebido
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorRecebido($vencimento_inicial, $vencimento_final));
    // Valor Recebido (Mês Atual)
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorRecebidoMesAtual($vencimento_inicial, $vencimento_final));
    // Valor Recebido (Mês Anterior)
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorRecebidoMesAnterior($vencimento_inicial, $vencimento_final));
    // Valor Recebido (Mês Posterior)
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorRecebidoMesPosterior($vencimento_inicial, $vencimento_final));
    // Em Aberto (Valor Original)
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorAbertoOriginal($vencimento_inicial, $vencimento_final));
    // Em Aberto (Valor Final)
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorAbertoFinal($vencimento_inicial, $vencimento_final));
    // Baixas Indevidas/Negociadas
    $itens = funcoes::mescla_arrays($itens, $this->buscaValorIndevido($vencimento_inicial, $vencimento_final));
    // Retorna Valor
    return $itens;
  }

  function buscaValorFaturado($vencimento, $vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')) AS descricao,
        SUM(tab_cobrancas.coa_valor_original) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Total Faturado (".$this->formataData($vencimento).")");
  }

        
  function buscaValorRecebido($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_segundas_vias.coa_id IS NULL, IF(tab_cobrancas.cot_id = 4, 'Pagos na Empresa', IF(tab_cobrancas.cot_id = 5, 'Pagos via Depósito', IF(tab_cobrancas.cot_id = 9, 'Assistência Técnica', IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta'))))), IF(tab_segundas_vias.cot_id = 4, 'Pagos na Empresa', IF(tab_segundas_vias.cot_id = 5, 'Pagos via Depósito', IF(tab_segundas_vias.cot_id = 9, 'Assistência Técnica', IF(formatos_segundas_vias.cft_id IN (1), 'Carteira', IF(formatos_segundas_vias.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')))))) AS descricao,
        SUM(IF(tab_segundas_vias.coa_id IS NULL, tab_cobrancas.coa_valor_total, tab_segundas_vias.coa_valor_total)) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        LEFT OUTER JOIN tab_cobrancas tab_segundas_vias ON (tab_cobrancas.coa_id = tab_segundas_vias.coa_pai AND tab_segundas_vias.cot_id IN (2, 3, 4, 5, 9))
        LEFT OUTER JOIN tab_cobrancas_formatos formatos_segundas_vias ON (tab_segundas_vias.cof_id = formatos_segundas_vias.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.cot_id IN (2, 3, 4, 5, 9, 11) AND
        tab_cobrancas.coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao,
        tab_segundas_vias.coa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Total Recebido");
  }

  function buscaValorRecebidoMesAtual($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_segundas_vias.coa_id IS NULL, IF(tab_cobrancas.cot_id = 4, 'Pagos na Empresa', IF(tab_cobrancas.cot_id = 5, 'Pagos via Depósito', IF(tab_cobrancas.cot_id = 9, 'Assistência Técnica', IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta'))))), IF(tab_segundas_vias.cot_id = 4, 'Pagos na Empresa', IF(tab_segundas_vias.cot_id = 5, 'Pagos via Depósito', IF(tab_segundas_vias.cot_id = 9, 'Assistência Técnica', IF(formatos_segundas_vias.cft_id IN (1), 'Carteira', IF(formatos_segundas_vias.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')))))) AS descricao,
        SUM(IF(tab_segundas_vias.coa_id IS NULL, tab_cobrancas.coa_valor_total, tab_segundas_vias.coa_valor_total)) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        LEFT OUTER JOIN tab_cobrancas tab_segundas_vias ON (tab_cobrancas.coa_id = tab_segundas_vias.coa_pai AND tab_segundas_vias.cot_id IN (2, 3, 4, 5, 9))
        LEFT OUTER JOIN tab_cobrancas_formatos formatos_segundas_vias ON (tab_segundas_vias.cof_id = formatos_segundas_vias.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.coa_data_pagamento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.cot_id IN (2, 3, 4, 5, 9, 11) AND
        tab_cobrancas.coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao,
        tab_segundas_vias.coa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Total Recebido (Mês Atual)");
  }

  function buscaValorRecebidoMesAnterior($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_segundas_vias.coa_id IS NULL, IF(tab_cobrancas.cot_id = 4, 'Pagos na Empresa', IF(tab_cobrancas.cot_id = 5, 'Pagos via Depósito', IF(tab_cobrancas.cot_id = 9, 'Assistência Técnica', IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta'))))), IF(tab_segundas_vias.cot_id = 4, 'Pagos na Empresa', IF(tab_segundas_vias.cot_id = 5, 'Pagos via Depósito', IF(tab_segundas_vias.cot_id = 9, 'Assistência Técnica', IF(formatos_segundas_vias.cft_id IN (1), 'Carteira', IF(formatos_segundas_vias.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')))))) AS descricao,
        SUM(IF(tab_segundas_vias.coa_id IS NULL, tab_cobrancas.coa_valor_total, tab_segundas_vias.coa_valor_total)) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        LEFT OUTER JOIN tab_cobrancas tab_segundas_vias ON (tab_cobrancas.coa_id = tab_segundas_vias.coa_pai AND tab_segundas_vias.cot_id IN (2, 3, 4, 5, 9))
        LEFT OUTER JOIN tab_cobrancas_formatos formatos_segundas_vias ON (tab_segundas_vias.cof_id = formatos_segundas_vias.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento < '$vencimento_inicial' AND
        tab_cobrancas.coa_data_pagamento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.cot_id IN (2, 3, 4, 5, 9, 11) AND
        tab_cobrancas.coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao,
        tab_segundas_vias.coa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Total Recebido (Meses Anteriores)");
  }

  function buscaValorRecebidoMesPosterior($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_segundas_vias.coa_id IS NULL, IF(tab_cobrancas.cot_id = 4, 'Pagos na Empresa', IF(tab_cobrancas.cot_id = 5, 'Pagos via Depósito', IF(tab_cobrancas.cot_id = 9, 'Assistência Técnica', IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta'))))), IF(tab_segundas_vias.cot_id = 4, 'Pagos na Empresa', IF(tab_segundas_vias.cot_id = 5, 'Pagos via Depósito', IF(tab_segundas_vias.cot_id = 9, 'Assistência Técnica', IF(formatos_segundas_vias.cft_id IN (1), 'Carteira', IF(formatos_segundas_vias.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')))))) AS descricao,
        SUM(IF(tab_segundas_vias.coa_id IS NULL, tab_cobrancas.coa_valor_total, tab_segundas_vias.coa_valor_total)) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        LEFT OUTER JOIN tab_cobrancas tab_segundas_vias ON (tab_cobrancas.coa_id = tab_segundas_vias.coa_pai AND tab_segundas_vias.cot_id IN (2, 3, 4, 5, 9))
        LEFT OUTER JOIN tab_cobrancas_formatos formatos_segundas_vias ON (tab_segundas_vias.cof_id = formatos_segundas_vias.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento > '$vencimento_final' AND
        tab_cobrancas.coa_data_pagamento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.cot_id IN (2, 3, 4, 5, 9, 11) AND
        tab_cobrancas.coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao,
        tab_segundas_vias.coa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Total Recebido (Meses Posteriores)");
  }

  function buscaValorAbertoFinal($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_segundas_vias.coa_id IS NULL, IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')), IF(formatos_segundas_vias.cft_id IN (1), 'Carteira', IF(formatos_segundas_vias.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta'))) AS descricao,
        SUM(IF(tab_segundas_vias.coa_id IS NULL, tab_cobrancas.coa_valor_original, tab_segundas_vias.coa_valor_original)) AS valor
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        LEFT OUTER JOIN tab_cobrancas tab_segundas_vias ON (tab_cobrancas.coa_id = tab_segundas_vias.coa_pai)
        LEFT OUTER JOIN tab_cobrancas_formatos formatos_segundas_vias ON (tab_segundas_vias.cof_id = formatos_segundas_vias.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.coa_pai IS NULL AND
        tab_cobrancas.cot_id = 1".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        tab_cobrancas.coa_id
      ORDER BY
        descricao,
        tab_segundas_vias.coa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]]["valor"] += $dados["valor"];
      $valor[$dados["descricao"]][$dados["origem_id"]]["qtd"] += 1;
    }
    return $this->agrupaValores($valor, "Em Aberto (Valor Final)");
  }

  function buscaValorAbertoOriginal($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')) AS descricao,
        SUM(tab_cobrancas.coa_valor_original) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.coa_pai IS NULL AND
        tab_cobrancas.cot_id = 1".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Em Aberto (Valor Original)");
  }

  function buscaValorIndevido($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_clientes.clo_id AS origem_id,
        IF(tab_cobrancas_formatos.cft_id IN (1), 'Carteira', IF(tab_cobrancas_formatos.cft_id IN (2, 3), 'Boleto Bancário', 'Débito em Conta')) AS descricao,
        SUM(tab_cobrancas.coa_valor_original) AS valor,
        COUNT(DISTINCT tab_cobrancas.coa_id) AS qtd
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas.coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.cot_id IN (6, 7, 12) AND
        tab_cobrancas.coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        origem_id,
      	descricao
      ORDER BY
        descricao";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor[$dados["descricao"]][$dados["origem_id"]] = $dados;
    }
    return $this->agrupaValores($valor, "Indevidas / Negociadas / Canceladas");
  }

  function agrupaValores($valores, $titulo_total) {
    foreach ($valores as $descricao => $origens) {
      $valor_agrupado["descricao"] = "» $descricao";
      ksort($origens);
      foreach ($origens as $origem_id => $valor) {
        $valor_agrupado["qtd_total"] += $valor["qtd"];
        $valor_agrupado["valor_total"] += $valor["valor"];
        $valor_agrupado["qtd_$origem_id"] = $valor["qtd"];
        $valor_agrupado["valor_$origem_id"] = $this->formataDinheiro($valor["valor"], $valor_agrupado["qtd_$origem_id"] > 0);
        $valor_total[$origem_id]["qtd"] += $valor["qtd"];
        $valor_total[$origem_id]["valor"] += $valor["valor"];
      }
      $valor_agrupado["valor_total"] = $this->formataDinheiro($valor_agrupado["valor_total"], $valor_agrupado["qtd_total"] > 0);
      $valores_agrupados[] = $valor_agrupado;
      unset($valor_agrupado);
    }
    $valor_agrupado["descricao"] = $titulo_total;
    $valor_agrupado["cor_linha"] = "#D7D7D7";
    foreach ($valor_total as $origem_id => $valor) {
      $valor_agrupado["qtd_total"] += $valor["qtd"];
      $valor_agrupado["valor_total"] += $valor["valor"];
      $valor_agrupado["qtd_$origem_id"] = $valor["qtd"];
      $valor_agrupado["valor_$origem_id"] = $this->formataDinheiro($valor["valor"], $valor_agrupado["qtd_$origem_id"] > 0);
    }
    $valor_agrupado["valor_total"] = $this->formataDinheiro($valor_agrupado["valor_total"], $valor_agrupado["qtd_total"] > 0);
    $valores_agrupados = array_merge(array($valor_agrupado), $valores_agrupados);
    return $valores_agrupados;
  }

}

?>
