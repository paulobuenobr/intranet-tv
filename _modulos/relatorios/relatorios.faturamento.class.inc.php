<?

class relatorios_faturamento {

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
    $periodos[] = array ("tipo" => "periodoPorMes", "nome" => "Vencimento", "name1" => "venc_inicio", "name2" => "venc_termino", "where" => "DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%Y%m%d')");
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

  function buscaCampos($vencimento_inicial, $vencimento_final) {
    // Trata Vencimentos
    $vencimento = date('Y-m-d', strtotime(substr($vencimento_inicial, -4)."-".substr($vencimento_inicial, 0, 2)."-"."01"." 1 MONTH AGO"));
    $vencimento_final = substr($vencimento_final, -4)."-".substr($vencimento_final, 0, 2)."-"."01";
    // Gera Campos
    $campos["descricao"] = "Descrição";
    do {
      $vencimento = date('Y-m-d', strtotime("$vencimento 1 MONTH"));
      $campos[$this->formataVencimento($vencimento)] = array("quantidade[$vencimento]" => "Quantidade", "valor[$vencimento]" => "Valor");
    } while ($vencimento != $vencimento_final);
    return $campos;
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
      $tamanho_chave = 0;
      reset($array);
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = @implode(",", $chave);
        $tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
        unset($chave);
        next($array);
      }
      reset($array);
      $array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = @implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
        }
        unset($chave);
        next($array);
      }
      ksort($array_ordenacao);
      while (is_int($array_id = current($array_ordenacao))) {
        $array_final[] = $array[$array_id];
        next($array_ordenacao);
      }
      return $array_final;
    }
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaItens($vencimento_inicial, $vencimento_final) {
    // Trata Vencimentos
    $vencimento_inicial = date('Y-m-d', strtotime(substr($vencimento_inicial, -4)."-".substr($vencimento_inicial, 0, 2)."-"."01"." 1 MONTH AGO"));
    $vencimento_final = substr($vencimento_final, -4)."-".substr($vencimento_final, 0, 2)."-"."01";
    // Busca Detalhamento
    return $this->buscaDetalhamento($vencimento_inicial, $vencimento_final);
  }

  function buscaFaturamento($vencimento_fatura, $vencimento_atual, $descricao, $ordem) {
    $query = "
      SELECT
        COUNT(coa_id) AS quantidade,
        SUM(coa_valor_original) AS valor
      FROM
        tab_cobrancas
        INNER JOIN tab_cobrancas_lotes ON (tab_cobrancas.col_id = tab_cobrancas_lotes.col_id)
		INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_cobrancas.cli_id)
      WHERE
        col_vencimento = '$vencimento_fatura'".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["descricao"] = $descricao;
      $dados["quantidade[$vencimento_atual]"] = $dados["quantidade"];
      $dados["valor[$vencimento_atual]"] = $dados["valor"];
      $dados["ordem"] = $ordem;
      $dados["cor_linha"] = "#D7D7D7";
      return $dados;
    }
  }

  function buscaDetalhamento($vencimento_inicial, $vencimento_final) {
    $query = "
      SELECT
        tab_cobrancas_lotes.col_vencimento AS vencimento,
        tab_cobrancas_detalhes.cod_valor_final AS valor_final,
        tab_cobrancas_detalhes.ass_id AS assinatura_id,
        tab_cobrancas_detalhes.dec_id AS desconto_id,
        tab_cobrancas_detalhes.ajf_id AS ajuste_id,
        tab_cobrancas.cli_id AS cliente_id
      FROM
        tab_cobrancas_lotes
        INNER JOIN tab_cobrancas ON (tab_cobrancas_lotes.col_id = tab_cobrancas.col_id)
        INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
		INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_cobrancas_lotes.col_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        tab_cobrancas.coa_valor_original > 0".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    $detalhamentos = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["valor_final"] = intval($dados["valor_final"], 10);
      if ($dados["assinatura_id"]) {
        if ($detalhamentos["assinaturas"][$dados["vencimento"]][$dados["assinatura_id"]]) {
          $detalhamentos["assinaturas"][$dados["vencimento"]][$dados["assinatura_id"]]["valor_final"] += $dados["valor_final"];
        } else {
          $detalhamentos["assinaturas"][$dados["vencimento"]][$dados["assinatura_id"]] = $dados;
        }
      } elseif ($dados["desconto_id"]) {
        if ($detalhamentos["descontos"][$dados["vencimento"]][$dados["desconto_id"]]) {
          $detalhamentos["descontos"][$dados["vencimento"]][$dados["desconto_id"]]["valor_final"] += $dados["valor_final"];
        } else {
          $detalhamentos["descontos"][$dados["vencimento"]][$dados["desconto_id"]] = $dados;
        }
      } elseif ($dados["ajuste_id"]) {
        if ($detalhamentos["ajustes"][$dados["vencimento"]][$dados["ajuste_id"]]) {
          $detalhamentos["ajustes"][$dados["vencimento"]][$dados["ajuste_id"]]["valor_final"] += $dados["valor_final"];
        } else {
          $detalhamentos["ajustes"][$dados["vencimento"]][$dados["ajuste_id"]] = $dados;
        }
      } else {
        $detalhamentos["outros"][$dados["vencimento"]][$dados["cliente_id"]] = $dados;
      }
    }
    // Trata Detalhamentos
    do {
      $vencimento = date("Y-m-d", strtotime(($vencimento ? $vencimento : $vencimento_inicial)." 1 MONTH"));
      $detalhamentos_tratados = $this->array_merge_recursivo($detalhamentos_tratados, $this->trataDetalhamento($detalhamentos, $vencimento));
    } while ($vencimento != $vencimento_final);
    // Ordena Detalhamentos Tratados
    sort($detalhamentos_tratados);
    $detalhamentos_tratados = $this->ordena_multiarray($detalhamentos_tratados, array("ordem"));
    // Retorna Detalhamentos Tratados
    return $detalhamentos_tratados;
  }

  function array_merge_recursivo($array_1, $array_2) {
   if (!is_array($array_1) or !is_array($array_2)) {
     return $array_2;
   }
   foreach ($array_2 AS $chave_2 => $valor_2) {
     $array_1[$chave_2] = $this->array_merge_recursivo(@$array_1[$chave_2], $valor_2);
   }
   return $array_1;
  }

  function trataDetalhamento($detalhamentos, $vencimento) {
    // Trata Vencimentos
    $vencimento_inicial = date("Y-m-d", strtotime("$vencimento 1 MONTH AGO"));
    $vencimento_final = $vencimento;

    // Busca Faturamento Anterior
    $itens["[000]"] = $this->buscaFaturamento($vencimento_inicial, $vencimento_final, "Faturamento Anterior", "[000]");

    // Assinaturas - Vencimento Inicial
    foreach ($detalhamentos["assinaturas"][$vencimento_inicial] as $assinatura_id => $detalhamento) {
      if (abs($detalhamento["valor_final"]) < abs($detalhamentos["assinaturas"][$vencimento_final][$assinatura_id]["valor_final"])) {
        // Busca Assinatura Serviço
        $dados_assinatura = $this->buscaDadosAssinatura($assinatura_id);
        $acao = $dados_assinatura["migrada_id"] ? "Migrações" : "Ativações";
        $odem = $dados_assinatura["migrada_id"] ? "030" : "010";
        // Ativações/Migrações
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["descricao"] = "&nbsp; &raquo; Assinaturas &raquo; $acao &raquo; {$dados_assinatura["servico_nome"]}";
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["quantidade[$vencimento_final]"][] = $assinatura_id;
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["valor[$vencimento_final]"] += $detalhamentos["assinaturas"][$vencimento_final][$assinatura_id]["valor_final"] - $detalhamento["valor_final"];
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["ordem"] = "[$odem] {$dados_assinatura["servico_nome"]}";
      } elseif (abs($detalhamento["valor_final"]) > abs($detalhamentos["assinaturas"][$vencimento_final][$assinatura_id]["valor_final"])) {
        // Busca Assinatura Serviço
        $dados_assinatura = $this->buscaDadosAssinatura($assinatura_id);
        $acao = $dados_assinatura["status_id"] == 4 ? "Migrações" : "Cancelamentos";
        $odem = $dados_assinatura["status_id"] == 4 ? "030" : "020";
        // Cancelamentos/Migrações
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["descricao"] = "&nbsp; &raquo; Assinaturas &raquo; $acao &raquo; {$dados_assinatura["servico_nome"]}";
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["quantidade[$vencimento_final]"][] = $assinatura_id;
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["valor[$vencimento_final]"] += $detalhamentos["assinaturas"][$vencimento_final][$assinatura_id]["valor_final"] - $detalhamento["valor_final"];
        $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["ordem"] = "[$odem] {$dados_assinatura["servico_nome"]}";
      }
      // Limpa Assinaturas
      unset($detalhamentos["assinaturas"][$vencimento_inicial][$assinatura_id]);
      unset($detalhamentos["assinaturas"][$vencimento_final][$assinatura_id]);
    }
    // Assinaturas - Vencimento Final
    foreach ($detalhamentos["assinaturas"][$vencimento_final] as $assinatura_id => $detalhamento) {
      // Busca Assinatura Serviço
      $dados_assinatura = $this->buscaDadosAssinatura($assinatura_id);
      $acao = $dados_assinatura["migrada_id"] ? "Migrações" : "Ativações";
      $odem = $dados_assinatura["migrada_id"] ? "030" : "010";
      // Ativação
      $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["descricao"] = "&nbsp; &raquo; Assinaturas &raquo; $acao &raquo; {$dados_assinatura["servico_nome"]}";
      $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["quantidade[$vencimento_final]"][] = $assinatura_id;
      $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["valor[$vencimento_final]"] += $detalhamento["valor_final"];
      $itens["[$odem] {$dados_assinatura["servico_nome"]}"]["ordem"] = "[$odem] {$dados_assinatura["servico_nome"]}";
      // Limpa Assinaturas
      unset($detalhamentos["assinaturas"][$vencimento_inicial][$assinatura_id]);
      unset($detalhamentos["assinaturas"][$vencimento_final][$assinatura_id]);
    }
    // Sub-Total
    $itens = funcoes::mescla_arrays($itens, $this->buscaSubTotal($itens, $vencimento_final, array("[010]" => array("descricao" => "&nbsp; &raquo; Assinaturas &raquo; Ativações &raquo; Sub-Total", "ordem" => "[011]"), "[020]" => array("descricao" => "&nbsp; &raquo; Assinaturas &raquo; Cancelamentos &raquo; Sub-Total", "ordem" => "[021]"), "[030]" => array("descricao" => "&nbsp; &raquo; Assinaturas &raquo; Migrações &raquo; Sub-Total", "ordem" => "[031]"))));

    // Descontos - Vencimento Inicial
    foreach ($detalhamentos["descontos"][$vencimento_inicial] as $desconto_id => $detalhamento) {
      if (abs($detalhamento["valor_final"]) < abs($detalhamentos["descontos"][$vencimento_final][$desconto_id]["valor_final"])) {
        // Busca Desconto Tipo
        $desconto_tipo = $this->buscaDescontoTipo($desconto_id);
        // Início
        $itens["[040] $desconto_tipo"]["descricao"] = "&nbsp; &raquo; Descontos &raquo; Início &raquo; $desconto_tipo";
        $itens["[040] $desconto_tipo"]["quantidade[$vencimento_final]"][] = $desconto_id;
        $itens["[040] $desconto_tipo"]["valor[$vencimento_final]"] += $detalhamentos["descontos"][$vencimento_final][$desconto_id]["valor_final"] - $detalhamento["valor_final"];
        $itens["[040] $desconto_tipo"]["ordem"] = "[040] $desconto_tipo";
      } elseif (abs($detalhamento["valor_final"]) > abs($detalhamentos["descontos"][$vencimento_final][$desconto_id]["valor_final"])) {
        // Busca Desconto Tipo
        $desconto_tipo = $this->buscaDescontoTipo($desconto_id);
        // Término
        $itens["[050] $desconto_tipo"]["descricao"] = "&nbsp; &raquo; Descontos &raquo; Término &raquo; $desconto_tipo";
        $itens["[050] $desconto_tipo"]["quantidade[$vencimento_final]"][] = $desconto_id;
        $itens["[050] $desconto_tipo"]["valor[$vencimento_final]"] += $detalhamentos["descontos"][$vencimento_final][$desconto_id]["valor_final"] - $detalhamento["valor_final"];
        $itens["[050] $desconto_tipo"]["ordem"] = "[050] $desconto_tipo";
      }
      // Limpa Descontos
      unset($detalhamentos["descontos"][$vencimento_inicial][$desconto_id]);
      unset($detalhamentos["descontos"][$vencimento_final][$desconto_id]);
    }
    // Descontos - Vencimento Final
    foreach ($detalhamentos["descontos"][$vencimento_final] as $desconto_id => $detalhamento) {
      // Busca Desconto Tipo
      $desconto_tipo = $this->buscaDescontoTipo($desconto_id);
      // Início
      $itens["[040] $desconto_tipo"]["descricao"] = "&nbsp; &raquo; Descontos &raquo; Início &raquo; $desconto_tipo";
      $itens["[040] $desconto_tipo"]["quantidade[$vencimento_final]"][] = $desconto_id;
      $itens["[040] $desconto_tipo"]["valor[$vencimento_final]"] += $detalhamento["valor_final"];
      $itens["[040] $desconto_tipo"]["ordem"] = "[040] $desconto_tipo";
      // Limpa Descontos
      unset($detalhamentos["descontos"][$vencimento_inicial][$desconto_id]);
      unset($detalhamentos["descontos"][$vencimento_final][$desconto_id]);
    }
    // Sub-Total
    $itens = funcoes::mescla_arrays($itens, $this->buscaSubTotal($itens, $vencimento_final, array("[040]" => array("descricao" => "&nbsp; &raquo; Descontos &raquo; Início &raquo; Sub-Total", "ordem" => "[041]"), "[050]" => array("descricao" => "&nbsp; &raquo; Descontos &raquo; Término &raquo; Sub-Total", "ordem" => "[051]"))));

    // Ajustes Financeiros - Vencimento Inicial
    foreach ($detalhamentos["ajustes"][$vencimento_inicial] as $ajuste_id => $detalhamento) {
      if (abs($detalhamento["valor_final"]) < abs($detalhamentos["ajustes"][$vencimento_final][$ajuste_id]["valor_final"])) {
        // Busca Desconto Tipo
        $dados_ajuste = $this->buscaDadosAjuste($ajuste_id);
        if ($dados_ajuste["categoria_id"] == 11) {
          // Tarifa Bancária
          $detalhamentos["outros"][$vencimento_inicial][$detalhamento["cliente_id"]]["valor_final"] += $detalhamento["valor_final"];
        } else {
          // Início
          $itens["[060] {$dados_ajuste["tipo_nome"]}"]["descricao"] = "&nbsp; &raquo; Ajustes Financeiros &raquo; Início &raquo; {$dados_ajuste["tipo_nome"]}";
          $itens["[060] {$dados_ajuste["tipo_nome"]}"]["quantidade[$vencimento_final]"][] = $ajuste_id;
          $itens["[060] {$dados_ajuste["tipo_nome"]}"]["valor[$vencimento_final]"] += $detalhamentos["ajustes"][$vencimento_final][$ajuste_id]["valor_final"] - $detalhamento["valor_final"];
          $itens["[060] {$dados_ajuste["tipo_nome"]}"]["ordem"] = "[060] {$dados_ajuste["tipo_nome"]}";
        }
      } elseif (abs($detalhamento["valor_final"]) > abs($detalhamentos["ajustes"][$vencimento_final][$ajuste_id]["valor_final"])) {
        // Busca Desconto Tipo
        $dados_ajuste = $this->buscaDadosAjuste($ajuste_id);
        if ($dados_ajuste["categoria_id"] == 11) {
          // Tarifa Bancária
          $detalhamentos["outros"][$vencimento_inicial][$detalhamento["cliente_id"]]["valor_final"] += $detalhamento["valor_final"];
        } else {
          // Término
          $itens["[070] {$dados_ajuste["tipo_nome"]}"]["descricao"] = "&nbsp; &raquo; Ajustes Financeiros &raquo; Término &raquo; {$dados_ajuste["tipo_nome"]}";
          $itens["[070] {$dados_ajuste["tipo_nome"]}"]["quantidade[$vencimento_final]"][] = $ajuste_id;
          $itens["[070] {$dados_ajuste["tipo_nome"]}"]["valor[$vencimento_final]"] += $detalhamentos["ajustes"][$vencimento_final][$ajuste_id]["valor_final"] - $detalhamento["valor_final"];
          $itens["[070] {$dados_ajuste["tipo_nome"]}"]["ordem"] = "[070] {$dados_ajuste["tipo_nome"]}";
        }
      }
      // Limpa Ajustes Financeiros
      unset($detalhamentos["ajustes"][$vencimento_inicial][$ajuste_id]);
      unset($detalhamentos["ajustes"][$vencimento_final][$ajuste_id]);
    }
    // Ajustes Financeiros - Vencimento Final
    foreach ($detalhamentos["ajustes"][$vencimento_final] as $ajuste_id => $detalhamento) {
      // Busca Desconto Tipo
      $dados_ajuste = $this->buscaDadosAjuste($ajuste_id);
      if ($dados_ajuste["categoria_id"] == 11) {
        // Tarifa Bancária
        $detalhamentos["outros"][$vencimento_final][$detalhamento["cliente_id"]]["valor_final"] += $detalhamento["valor_final"];
      } else {
        // Início
        $itens["[060] {$dados_ajuste["tipo_nome"]}"]["descricao"] = "&nbsp; &raquo; Ajustes Financeiros &raquo; Início &raquo; {$dados_ajuste["tipo_nome"]}";
        $itens["[060] {$dados_ajuste["tipo_nome"]}"]["quantidade[$vencimento_final]"][] = $ajuste_id;
        $itens["[060] {$dados_ajuste["tipo_nome"]}"]["valor[$vencimento_final]"] += $detalhamento["valor_final"];
        $itens["[060] {$dados_ajuste["tipo_nome"]}"]["ordem"] = "[060] {$dados_ajuste["tipo_nome"]}";
      }
      // Limpa Ajustes Financeiros
      unset($detalhamentos["ajustes"][$vencimento_inicial][$ajuste_id]);
      unset($detalhamentos["ajustes"][$vencimento_final][$ajuste_id]);
    }
    // Sub-Total
    $itens = funcoes::mescla_arrays($itens, $this->buscaSubTotal($itens, $vencimento_final, array("[060]" => array("descricao" => "&nbsp; &raquo; Ajustes Financeiros &raquo; Sub-Total", "ordem" => "[071]"), "[070]" => array("descricao" => "&nbsp; &raquo; Ajustes Financeiros &raquo; Sub-Total", "ordem" => "[071]"))));

    // Tarifas Bancárias - Vencimento Inicial
    foreach ($detalhamentos["outros"][$vencimento_inicial] as $cliente_id => $detalhamento) {
      if (abs($detalhamento["valor_final"]) < abs($detalhamentos["outros"][$vencimento_final][$cliente_id]["valor_final"])) {
        // Tarifa Bancária
        $itens["[080]"]["descricao"] = "&nbsp; &raquo; Tarifa Bancária &raquo; Início";
        $itens["[080]"]["quantidade[$vencimento_final]"][] = $detalhamento["cliente_id"];
        $itens["[080]"]["valor[$vencimento_final]"] += $detalhamento["valor_final"];
        $itens["[080]"]["ordem"] = "[080]";
      } elseif (abs($detalhamento["valor_final"]) > abs($detalhamentos["outros"][$vencimento_final][$cliente_id]["valor_final"])) {
        $itens["[090]"]["descricao"] = "&nbsp; &raquo; Tarifa Bancária &raquo; Término";
        $itens["[090]"]["quantidade[$vencimento_final]"][] = $detalhamento["cliente_id"];
        $itens["[090]"]["valor[$vencimento_final]"] -= $detalhamento["valor_final"];
        $itens["[090]"]["ordem"] = "[090]";
      }
      unset($detalhamentos["outros"][$vencimento_inicial][$cliente_id]);
      unset($detalhamentos["outros"][$vencimento_final][$cliente_id]);
    }
    // Tarifas Bancárias - Vencimento Final
    foreach ($detalhamentos["outros"][$vencimento_final] as $cliente_id => $detalhamento) {
      if ($detalhamento["valor_final"]) {
        // Tarifa Bancária
        $itens["[080]"]["descricao"] = "&nbsp; &raquo; Tarifa Bancária &raquo; Início";
        $itens["[080]"]["quantidade[$vencimento_final]"][] = $detalhamento["cliente_id"];
        $itens["[080]"]["valor[$vencimento_final]"] += $detalhamento["valor_final"];
        $itens["[080]"]["ordem"] = "[080]";
      }
      unset($detalhamentos["outros"][$vencimento_inicial][$cliente_id]);
      unset($detalhamentos["outros"][$vencimento_final][$cliente_id]);
    }

    // Busca Faturamento Atual
    $itens["[100]"] = $this->buscaFaturamento($vencimento_final, $vencimento_final, "Faturamento Atual", "[100]");

    // Formata Valores
    foreach ($itens as $chave => $item) {
      $itens[$chave]["quantidade[$vencimento_final]"] = number_format(is_array($item["quantidade[$vencimento_final]"]) ? count(array_unique($item["quantidade[$vencimento_final]"])) : intval($item["quantidade[$vencimento_final]"], 10), 0, null, ".");
      $itens[$chave]["valor[$vencimento_final]"] = $this->formataDinheiro($item["valor[$vencimento_final]"]);
    }

    return $itens;
  }

  function buscaSubTotal($itens, $vencimento, $grupos) {
    foreach ($itens as $chave => $item) {
      foreach ($grupos as $item_sub_total => $dados) {
        if (strpos($chave, $item_sub_total) !== false) {
          $sub_total[$dados["ordem"]]["descricao"] = $dados["descricao"];
          $sub_total[$dados["ordem"]]["quantidade[$vencimento]"] = funcoes::mescla_arrays($sub_total[$dados["ordem"]]["quantidade[$vencimento]"], $item["quantidade[$vencimento]"]);
          $sub_total[$dados["ordem"]]["valor[$vencimento]"] += $item["valor[$vencimento]"];
          $sub_total[$dados["ordem"]]["ordem"] = $dados["ordem"];
          $sub_total[$dados["ordem"]]["cor_linha"] = "#D7D7D7";
        }
      }
    }
    return $sub_total;
  }

  function buscaDadosAssinatura($assinatura_id) {
    $query = "
      SELECT
        ast_id AS status_id,
        ass_migrada AS migrada_id,
        pls_nome AS servico_nome
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDescontoTipo($desconto_id) {
    $query = "
      SELECT
        det_nome AS tipo_nome
      FROM
        tab_descontos_concessoes
        INNER JOIN tab_descontos_tipos ON (tab_descontos_concessoes.det_id = tab_descontos_tipos.det_id)
      WHERE
        dec_id = $desconto_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return trim(preg_replace("/\\[[^\\]]*\\]/", "", $dados["tipo_nome"]));
    }
  }

  function buscaDadosAjuste($ajuste_id) {
    $query = "
      SELECT
        afc_id AS categoria_id,
        aft_nome AS tipo_nome
      FROM
        tab_ajustes_financeiros
        INNER JOIN tab_ajustes_financeiros_tipos ON (tab_ajustes_financeiros.aft_id = tab_ajustes_financeiros_tipos.aft_id)
      WHERE
        ajf_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaUltimoVencimentoLote() {
    $query = "
      SELECT
        DATE_FORMAT(MAX(col_vencimento), '%d/%m/%Y') AS vencimento
      FROM
        tab_cobrancas_lotes";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["vencimento"];
    }
  }

}

?>
