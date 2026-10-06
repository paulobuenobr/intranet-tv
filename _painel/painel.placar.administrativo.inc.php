<?

function geraPainelPlacarConteudoAdministrativo($dia, $mes, $ano) {
  $metas = buscaMetasAdministrativo($dia, $mes, $ano);
  if ($mes == date("m") && $ano == date("Y")) {
    // Pedidos de Cancelamento Dia -> Gerados naquele dia
    $itens["pedidos_cancelamento"]["dia"] = buscaPedidosCancelamentoAdministrativo($dia, $mes, $ano, "dia");
    $itens["pedidos_cancelamento"]["dia"] = geraLinkPorCancelamentoMotivos($itens["pedidos_cancelamento"]["dia"], $metas["pedidos_cancelamento"], "dia");
    // Pedidos de Cancelamento Semana -> Gerados naquela semana
    $itens["pedidos_cancelamento"]["semana"] = buscaPedidosCancelamentoAdministrativo($dia, $mes, $ano, "semana");
    $itens["pedidos_cancelamento"]["semana"] = geraLinkPorCancelamentoMotivos($itens["pedidos_cancelamento"]["semana"], $metas["pedidos_cancelamento"], "semana");
  }
  // Pedidos de Cancelamento -> Gerados naquele mês
  $itens["pedidos_cancelamento"]["mes"] = buscaPedidosCancelamentoAdministrativo($dia, $mes, $ano, "mes");
  $itens["pedidos_cancelamento"]["mes"] = geraLinkPorCancelamentoMotivos($itens["pedidos_cancelamento"]["mes"], $metas["pedidos_cancelamento"], "mes");
  if ($mes == date("m") && $ano == date("Y")) {
    // Cancelamentos Dia -> Gerados naquele dia
    $itens["cancelamentos"]["dia"] = buscaCancelamentosAdministrativo($dia, $mes, $ano, "dia");
    $itens["cancelamentos"]["dia"] = geraLinkPorCancelamentoMotivos($itens["cancelamentos"]["dia"], $metas["cancelamentos"], "dia");
    // Cancelamentos Semana -> Gerados naquela semana
    $itens["cancelamentos"]["semana"] = buscaCancelamentosAdministrativo($dia, $mes, $ano, "semana");
    $itens["cancelamentos"]["semana"] = geraLinkPorCancelamentoMotivos($itens["cancelamentos"]["semana"], $metas["cancelamentos"], "semana");
  }
  // Cancelamentos -> Gerados naquele mês
  $itens["cancelamentos"]["mes"] = buscaCancelamentosAdministrativo($dia, $mes, $ano, "mes");
  $itens["cancelamentos"]["mes"] = geraLinkPorCancelamentoMotivos($itens["cancelamentos"]["mes"], $metas["cancelamentos"], "mes");
  if ($mes == date("m") && $ano == date("Y")) {
    // Agendados Dia -> Agendados para aquele dia
    $itens["agendados"]["dia"] = buscaAgendadosAdministrativo($dia, $mes, $ano, "dia");
    $itens["agendados"]["dia"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["dia"], $metas["agendados"], "dia");
    // Agendados Semana -> Agendados para aquela semana
    $itens["agendados"]["semana"] = buscaAgendadosAdministrativo($dia, $mes, $ano, "semana");
    $itens["agendados"]["semana"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["semana"], $metas["agendados"], "semana");
  }
  // Agendados Mês -> Agendados para aquele mês
  $itens["agendados"]["mes"] = buscaAgendadosAdministrativo($dia, $mes, $ano, "mes");
  $itens["agendados"]["mes"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["mes"], $metas["agendados"], "mes");
  if ($mes == date("m") && $ano == date("Y")) {
    // Ativos Dia -> Ativos aquele dia
    $itens["ativos"]["dia"] = buscaAtivosAdministrativo($dia, $mes, $ano, "dia");
    $itens["ativos"]["dia"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["dia"], $metas["ativos"], "dia");
    // Ativos Semana -> Ativos aquela semana
    $itens["ativos"]["semana"] = buscaAtivosAdministrativo($dia, $mes, $ano, "semana");
    $itens["ativos"]["semana"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["semana"], $metas["ativos"], "semana");
  }
  // Ativos Mês -> Ativos aquele mês
  $itens["ativos"]["mes"] = buscaAtivosAdministrativo($dia, $mes, $ano, "mes");
  $itens["ativos"]["mes"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["mes"], $metas["ativos"], "mes");
  return geraPainelPlacarConteudoTabelaAdministrativo($itens, $mes, $ano);
}

function buscaMetasAdministrativo($dia, $mes, $ano) {
  $dias_uteis_mes = buscaDiasUteisMes(date("t", strtotime("{$ano}-{$mes}-{$dia}")), $mes, $ano);
  $dias_uteis_semana = buscaDiasUteisSemana($dia, $mes, $ano);
  $metas_mensais = buscaMetasMensais ("administrativo", $mes, $ano);
  $metas["pedidos_cancelamento"]["mes"] = $metas_mensais["pedidos_cancelamento"];
  $metas["pedidos_cancelamento"]["dia"] = $metas["pedidos_cancelamento"]["mes"] / $dias_uteis_mes;
  $metas["pedidos_cancelamento"]["semana"] = $metas["pedidos_cancelamento"]["dia"] * $dias_uteis_semana;
  $metas["pedidos_cancelamento"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["pedidos_cancelamento"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["pedidos_cancelamento"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);
  $metas["cancelamentos"]["mes"] = $metas_mensais["cancelamentos"];
  $metas["cancelamentos"]["dia"] = $metas["cancelamentos"]["mes"] / $dias_uteis_mes;
  $metas["cancelamentos"]["semana"] = $metas["cancelamentos"]["dia"] * $dias_uteis_semana;
  $metas["cancelamentos"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["cancelamentos"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["cancelamentos"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);
  $metas["agendados"]["mes"] = $metas_mensais["agendados"];
  $metas["agendados"]["dia"] = $metas["agendados"]["mes"] / $dias_uteis_mes;
  $metas["agendados"]["semana"] = $metas["agendados"]["dia"] * $dias_uteis_semana;
  $metas["agendados"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["agendados"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["agendados"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);
  $metas["ativos"]["mes"] = $metas_mensais["ativos"];
  $metas["ativos"]["dia"] = $metas["ativos"]["mes"] / $dias_uteis_mes;
  $metas["ativos"]["semana"] = $metas["ativos"]["dia"] * $dias_uteis_semana;
  $metas["ativos"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["ativos"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["ativos"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);
  return $metas;
}

function geraLinkPorCancelamentoMotivos($registros, $metas, $periodo) {
  if (sizeof($registros) > 0 ) {
  	$motivos = array();
    for ($i = 0; $i < sizeof($registros); $i++) {
      $motivos[$registros[$i]]++;
    }
    arsort($motivos);
    $nomes = array_keys($motivos);
    for ($i = 0; $i < sizeof($nomes); $i++) {
      $detalhes[] = "$nomes[$i]: ".formataNumero($motivos[$nomes[$i]]);
    }
    $detalhes = @implode("<br>", $detalhes);
  }
  $media = sizeof($registros) / $metas[$periodo];
  for ($i = 0; $i < sizeof($metas["niveis"]); $i++) {
    if ($media >= $metas["niveis"][$i]["minimo"] && $media < $metas["niveis"][$i]["maximo"]) {
      $cor = $metas["niveis"][$i]["cor"];
    }
  }
  $link = '<a onMouseOver="hintShow(\''.$detalhes.'\');" onMouseOut="hintHide();" href="#" class="txtBranco10" style="color: ' . $cor . '">' . formataNumero(sizeof($registros)) . ' / ' . formataNumero($metas[$periodo]) . '</a>';
  return $link;
}

function buscaPedidosCancelamentoAdministrativo($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_cancelamentos.can_inicio LIKE '$ano-$mes-$dia%'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"))." 23:59:59";
    $query_periodo = "tab_cancelamentos.can_inicio BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial))." 23:59:59";
    $query_periodo = "tab_cancelamentos.can_inicio BETWEEN '$data_inicial' AND '$data_final'";
  }
  $query = "
    SELECT DISTINCT
      tab_cancelamentos.cli_id AS cliente_id,
      IF(tab_cancelamentos.cam_id IS NOT NULL, tab_cancelamentos_motivos.cam_nome, 'Aguardando contato') AS motivo
    FROM
      tab_cancelamentos
      LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id)
    WHERE
      $query_periodo
    GROUP BY
      cliente_id
    ORDER BY
      motivo";
  $query = $bancodedados->query($query);
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $dados["motivo"] = trim(preg_replace("/\\([^\\)]*\\)/", "", $dados["motivo"]));
    $pedidos_cancelamento[] = $dados["motivo"];
  }
  return $pedidos_cancelamento;
}

function buscaCancelamentosAdministrativo($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_contratos.cor_data_cancelamento = '$ano-$mes-$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_contratos.cor_data_cancelamento BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_contratos.cor_data_cancelamento BETWEEN '$data_inicial' AND '$data_final'";
  }
  $query = "
    SELECT
      tab_contratos.cli_id AS id,
      IF(can_id, IF(cam_id, cam_id, 0), -1) AS motivo
    FROM
      tab_contratos
      LEFT OUTER JOIN tab_cancelamentos ON (tab_contratos.cor_id = tab_cancelamentos.cor_id AND tab_cancelamentos.cas_id = 4)
    WHERE
      tab_contratos.crs_id IN (5, 21, 22, 23) AND
      tab_contratos.pls_id = 1 AND
      $query_periodo
    GROUP BY
      id
    ORDER BY
      tab_cancelamentos.can_id";
  $query = $bancodedados->query($query);
  $motivos = buscaPedidosCancelamentoMotivos();
  while (($cancelamento_atual = $bancodedados->resultadoArray($query)) != false) {
    $cancelamentos[] = $motivos[$cancelamento_atual["motivo"]];
  }
  return $cancelamentos;
}

function buscaPedidosCancelamentoMotivos() {
  global $bancodedados;
  $query = "SELECT cam_id AS id, cam_nome AS nome FROM tab_cancelamentos_motivos ORDER BY nome";
  $query = $bancodedados->query($query);
  $motivos["-1"] = "Sem Solicitação de Cancelamento";
  $motivos["0"] = "Aguardando Contato";
  while (($motivo_atual = $bancodedados->resultadoArray($query)) != false) {
    $motivos[$motivo_atual["id"]] = trim(preg_replace("/\\([^\\)]*\\)/", "", $motivo_atual["nome"]));
  }
  return $motivos;
}

function buscaAgendadosAdministrativo($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_visitas.vis_data = '$ano-$mes-$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_visitas.vis_data BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_visitas.vis_data BETWEEN '$data_inicial' AND '$data_final'";
  }
  if ($ano.$mes < 200509) {
    $query = "SELECT tab_contratos.cor_id AS id FROM tab_contratos INNER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id) WHERE ($query_periodo)";
    $query = $bancodedados->query($query);
    while (($contrato_atual = $bancodedados->resultadoArray($query)) != false) {
      $contrato_atual["pontos"] = buscaContratoPontos($contrato_atual["id"], false);
      $contrato_atual["responsavel"] = buscaContratoResponsavel($contrato_atual["id"]);
      if ($contrato_atual["pontos"] > 0) {
        $contratos[] = $contrato_atual;
      }
    }
  } else {
    $query = "
    SELECT
      tab_usuarios.usu_nome AS responsavel,
      SUM(tab_vendas.ven_pontuacao) AS pontos
    FROM
      tab_visitas
      INNER JOIN tab_vendas ON (tab_visitas.cor_id = tab_vendas.cor_id)
      INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
    WHERE
      $query_periodo
    GROUP BY
      tab_vendas.usu_id";
    $query = $bancodedados->query($query);
    while (($contrato_atual = $bancodedados->resultadoArray($query)) != false) {
      $contratos[] = $contrato_atual;
    }
  }
  return $contratos;
}

function buscaAtivosAdministrativo($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_contratos.cor_data_ativacao = '$ano$mes$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_contratos.cor_data_ativacao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_contratos.cor_data_ativacao BETWEEN '$data_inicial' AND '$data_final'";
  }
  if ($ano.$mes < 200509) {
    $query = "SELECT tab_contratos.cor_id AS id FROM tab_contratos INNER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id) WHERE ($query_periodo)";
    $query = $bancodedados->query($query);
    while (($contrato_atual = $bancodedados->resultadoArray($query)) != false) {
      $contrato_atual["pontos"] = buscaContratoPontos($contrato_atual["id"]);
      $contrato_atual["responsavel"] = buscaContratoResponsavel($contrato_atual["id"]);
      if ($contrato_atual["pontos"] > 0) $contratos[] = $contrato_atual;
    }
  } else {
    $query = "
    SELECT
      tab_usuarios.usu_nome AS responsavel,
      SUM(tab_vendas.ven_pontuacao) AS pontos
    FROM
      tab_visitas
      INNER JOIN tab_contratos ON (tab_visitas.cor_id = tab_contratos.cor_id)
      INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)
      INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
    WHERE
      $query_periodo
    GROUP BY
      tab_vendas.usu_id";
    $query = $bancodedados->query($query);
    while (($contrato_atual = $bancodedados->resultadoArray($query)) != false) {
      $contratos[] = $contrato_atual;
    }
  }
  return $contratos;
}

function geraPainelPlacarConteudoTabelaAdministrativo($itens, $mes, $ano) {
  $tabela = '
    <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="3A5977">
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" width="30%">&nbsp;</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#505050" class="txtBranco10" width="23%">Dia</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#505050" class="txtBranco10" width="23%">Semana</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#505050" class="txtBranco10">Mês</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Pedidos de Canc.</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["pedidos_cancelamento"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["pedidos_cancelamento"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["pedidos_cancelamento"]["mes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Cancelamentos</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["cancelamentos"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["cancelamentos"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["cancelamentos"]["mes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Agendamentos</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["mes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Ativações</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["ativos"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["ativos"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["ativos"]["mes"].'</td>
      </tr>
    </table>';
  return $tabela;
}

?>
