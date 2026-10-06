<?

function geraPainelPlacarConteudoComercial($dia, $mes, $ano) {
  $metas = buscaMetasComercial($dia, $mes, $ano);
  if ($mes == date("m") && $ano == date("Y")) {
    // Aguardando Dia -> Gerados naquele dia
    $itens["aguardando"]["dia"] = buscaAguardandoComercial($dia, $mes, $ano, "dia");
    $itens["aguardando"]["dia"] = geraLinkPorPontosPorResponsavel($itens["aguardando"]["dia"], $metas["aguardando"], "dia");
    // Aguardando Semana -> Gerados semana atual
    $itens["aguardando"]["semana"] = buscaAguardandoComercial($dia, $mes, $ano, "semana");
    $itens["aguardando"]["semana"] = geraLinkPorPontosPorResponsavel($itens["aguardando"]["semana"], $metas["aguardando"], "semana");
    // Aguardando Semana -> Gerados semana anterior
    $itens["aguardando"]["semana_anterior"] = buscaAguardandoComercial($dia, $mes, $ano, "semana_anterior");
    $itens["aguardando"]["semana_anterior"] = geraLinkPorPontosPorResponsavel($itens["aguardando"]["semana_anterior"], $metas["aguardando"], "semana_anterior");
  }
  // Aguardando Mês -> Gerados naquele mês
  $itens["aguardando"]["mes"] = buscaAguardandoComercial($dia, $mes, $ano, "mes");
  $itens["aguardando"]["mes"] = geraLinkPorPontosPorResponsavel($itens["aguardando"]["mes"], $metas["aguardando"], "mes");
  if ($mes == date("m") && $ano == date("Y")) {
    // Fechados Dia -> Gerados naquele dia
    $itens["fechados"]["dia"] = buscaFechadosComercial($dia, $mes, $ano, "dia");
    $itens["fechados"]["dia"] = geraLinkPorPontosPorResponsavel($itens["fechados"]["dia"], $metas["fechados"], "dia");
    // Aguardando Semana -> Gerados semana atual
    $itens["fechados"]["semana"] = buscaFechadosComercial($dia, $mes, $ano, "semana");
    $itens["fechados"]["semana"] = geraLinkPorPontosPorResponsavel($itens["fechados"]["semana"], $metas["fechados"], "semana");
    // Aguardando Semana -> Gerados semana anterior
    $itens["fechados"]["semana_anterior"] = buscaFechadosComercial($dia, $mes, $ano, "semana_anterior");
    $itens["fechados"]["semana_anterior"] = geraLinkPorPontosPorResponsavel($itens["fechados"]["semana_anterior"], $metas["fechados"], "semana_anterior");
  }
  // Fechados Mês -> Gerados naquele mês
  $itens["fechados"]["mes"] = buscaFechadosComercial($dia, $mes, $ano, "mes");
  $itens["fechados"]["mes"] = geraLinkPorPontosPorResponsavel($itens["fechados"]["mes"], $metas["fechados"], "mes");
  if ($mes == date("m") && $ano == date("Y")) {
    // Agendados Dia -> Agendados para aquele dia e que foram gerados este mês
    $itens["agendados"]["dia"] = buscaAgendadosComercial($dia, $mes, $ano, "dia");
    $itens["agendados"]["dia"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["dia"], $metas["agendados"], "dia");
    // Agendados Semana -> Agendados para aquela semana e que foram gerados este mês
    $itens["agendados"]["semana"] = buscaAgendadosComercial($dia, $mes, $ano, "semana");
    $itens["agendados"]["semana"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["semana"], $metas["agendados"], "semana");
    // Aguardando Semana -> Agendados semana anterior
    $itens["agendados"]["semana_anterior"] = buscaAgendadosComercial($dia, $mes, $ano, "semana_anterior");
    $itens["agendados"]["semana_anterior"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["semana_anterior"], $metas["agendados"], "semana_anterior");
  }
  // Agendados Mês -> Agendados para aquele mês e que foram gerados este mês
  $itens["agendados"]["mes"] = buscaAgendadosComercial($dia, $mes, $ano, "mes");
  $itens["agendados"]["mes"] = geraLinkPorPontosPorResponsavel($itens["agendados"]["mes"], $metas["agendados"], "mes");
  if ($mes == date("m") && $ano == date("Y")) {
    // Ativos Dia -> Ativos aquele dia e que foram gerados este mês
    $itens["ativos"]["dia"] = buscaAtivosComercial($dia, $mes, $ano, "dia");
    $itens["ativos"]["dia"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["dia"], $metas["ativos"], "dia");
    // Ativos Semana -> Ativos aquela semana e que foram gerados este mês
    $itens["ativos"]["semana"] = buscaAtivosComercial($dia, $mes, $ano, "semana");
    $itens["ativos"]["semana"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["semana"], $metas["ativos"], "semana");
    // Aguardando Semana -> Ativos semana anterior
    $itens["ativos"]["semana_anterior"] = buscaAtivosComercial($dia, $mes, $ano, "semana_anterior");
    $itens["ativos"]["semana_anterior"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["semana_anterior"], $metas["ativos"], "semana_anterior");
  }
  // Ativos Mês -> Ativos aquele mês e que foram gerados este mês
  $itens["ativos"]["mes"] = buscaAtivosComercial($dia, $mes, $ano, "mes");
  $itens["ativos"]["mes"] = geraLinkPorPontosPorResponsavel($itens["ativos"]["mes"], $metas["ativos"], "mes");
  return geraPainelPlacarConteudoTabelaComercial($itens, $mes, $ano);
}

function buscaMetasComercial($dia, $mes, $ano) {
  $dias_uteis_mes = buscaDiasUteisMes(date("t", strtotime("{$ano}-{$mes}-{$dia}")), $mes, $ano);
  $dias_uteis_semana = buscaDiasUteisSemana($dia, $mes, $ano);
  $metas_mensais = buscaMetasMensais ("comercial", $mes, $ano);
  $metas["fechados"]["mes"] = $metas_mensais["fechados"];
  $metas["fechados"]["dia"] = $metas["fechados"]["mes"] / $dias_uteis_mes;
  $metas["fechados"]["semana"] = $metas["fechados"]["dia"] * $dias_uteis_semana;
  $metas["fechados"]["semana_anterior"] = $metas["fechados"]["semana"];
  $metas["fechados"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["fechados"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["fechados"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);
  $metas["agendados"]["mes"] = $metas_mensais["agendados"];
  $metas["agendados"]["dia"] = $metas["agendados"]["mes"] / $dias_uteis_mes;
  $metas["agendados"]["semana"] = $metas["agendados"]["dia"] * $dias_uteis_semana;
  $metas["agendados"]["semana_anterior"] = $metas["agendados"]["semana"];
  $metas["agendados"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["agendados"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["agendados"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);
  $metas["ativos"]["mes"] = $metas_mensais["ativos"];
  $metas["ativos"]["dia"] = $metas["ativos"]["mes"] / $dias_uteis_mes;
  $metas["ativos"]["semana"] = $metas["ativos"]["dia"] * $dias_uteis_semana;
  $metas["ativos"]["semana_anterior"] = $metas["ativos"]["semana"];
  $metas["ativos"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["ativos"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["ativos"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);
  return $metas;
}

function buscaFechadosComercial($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_contratos.cor_data_geracao = '$ano-$mes-$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "semana_anterior") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY LAST WEEK"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'";
  }
  if ($ano.$mes < 200509) {
    $query = "SELECT tab_assinaturas.ass_id AS id FROM tab_assinaturas INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id) INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id) WHERE ($query_periodo) AND tab_assinaturas.ass_migrada IS NULL AND (tab_assinaturas.ass_id NOT BETWEEN 11163 AND 11315)"; // Clientes Compuline 11163 - 11315
    $query = $bancodedados->query($query);
    while (($assinatura = $bancodedados->resultadoArray($query)) != false) {
      $assinatura["pontos"] = buscaAssinaturaPontos($assinatura["id"]);
      if ($assinatura["pontos"] > 0) {
        $assinatura["responsavel"] = buscaAssinaturaResponsavel($assinatura["id"]);
        $assinaturas[] = $assinatura;
      }
    }
  } else {
    $query = "
      SELECT
        tab_usuarios.usu_nome AS responsavel,
        SUM(tab_vendas.ven_pontuacao) AS pontos
      FROM
        tab_contratos
        INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)
        INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
      WHERE
        crs_id >= 15 AND
        $query_periodo
      GROUP BY
        tab_vendas.usu_id";
    $query = $bancodedados->query($query);
    while (($assinatura = $bancodedados->resultadoArray($query)) != false) {
      $assinaturas[] = $assinatura;
    }
  }
  return $assinaturas;
}

function buscaAguardandoComercial($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_contratos.cor_data_geracao = '$ano-$mes-$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "semana_anterior") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY LAST WEEK"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'";
  }
  $query = "
    SELECT
      tab_usuarios.usu_nome AS responsavel,
      SUM(tab_vendas.ven_pontuacao) AS pontos
    FROM
      tab_contratos
      INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)
      INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
    WHERE
      crs_id <= 13 AND
      $query_periodo
    GROUP BY
      tab_vendas.usu_id";
  $query = $bancodedados->query($query);
  while (($assinatura = $bancodedados->resultadoArray($query)) != false) {
    $assinaturas[] = $assinatura;
  }
  return $assinaturas;
}

function buscaAgendadosComercial($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_visitas.vis_data = '$ano-$mes-$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_visitas.vis_data BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "semana_anterior") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY LAST WEEK"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_visitas.vis_data BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_visitas.vis_data BETWEEN '$data_inicial' AND '$data_final'";
  }
  if ($ano.$mes < 200509) {
    $query = "SELECT tab_contratos.cor_id AS id FROM tab_contratos INNER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id) WHERE ($query_periodo) AND (DATE_FORMAT(tab_contratos.cor_data_geracao, '%Y%m') = DATE_FORMAT('$ano$mes$dia', '%Y%m'))";
    $query = $bancodedados->query($query);
    while (($contrato_atual = $bancodedados->resultadoArray($query)) != false) {
      $contrato_atual["pontos"] = buscaContratoPontos($contrato_atual["id"], false);
      if ($contrato_atual["pontos"] > 0) {
        $contrato_atual["responsavel"] = buscaContratoResponsavel($contrato_atual["id"]);
        $contratos[] = $contrato_atual;
      }
    }
  } else {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
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
        $query_periodo AND
        tab_contratos.crs_id < 19 AND
        tab_contratos.cor_data_geracao BETWEEN '$data_inicial' AND '$data_final'
      GROUP BY
        tab_vendas.usu_id";
    $query = $bancodedados->query($query);
    while (($contrato_atual = $bancodedados->resultadoArray($query)) != false) {
      $contratos[] = $contrato_atual;
    }
  }
  return $contratos;
}

function buscaAtivosComercial($dia, $mes, $ano, $periodo) {
  global $bancodedados;
  if ($periodo == "dia") {
    $query_periodo = "tab_assinaturas.ass_data_ativacao = '$ano-$mes-$dia'";
  } elseif ($periodo == "semana") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_assinaturas.ass_data_ativacao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "semana_anterior") {
    $data_inicial = date("w", strtotime("$ano-$mes-$dia")) == 0 ? "$ano-$mes-$dia" : date("Y-m-d", strtotime("$ano-$mes-$dia LAST SUNDAY LAST WEEK"));
    $data_final = date("Y-m-d", strtotime("$data_inicial + 6 DAYS"));
    $query_periodo = "tab_assinaturas.ass_data_ativacao BETWEEN '$data_inicial' AND '$data_final'";
  } elseif ($periodo == "mes") {
    $data_inicial = "$ano-$mes-01";
    $data_final = date("Y-m-t", strtotime($data_inicial));
    $query_periodo = "tab_assinaturas.ass_data_ativacao BETWEEN '$data_inicial' AND '$data_final'";
  }
  $data_inicial = "$ano-$mes-01";
  $data_final = date("Y-m-t", strtotime($data_inicial));
  $query = "
    SELECT
      tab_vendas.ven_pontuacao AS pontos,
      tab_usuarios.usu_nome AS responsavel
    FROM
      tab_assinaturas
      INNER JOIN tab_vendas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
      INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
    WHERE
      $query_periodo AND
      tab_assinaturas.ass_data_fechamento BETWEEN '$data_inicial' AND '$data_final'";
  $query = $bancodedados->query($query);
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $ativos[] = $dados;
  }
  return $ativos;
}

function geraPainelPlacarConteudoTabelaComercial($itens, $mes, $ano) {
  $tabela = '
    <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="3A5977">
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" width="23%">&nbsp;</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#505050" class="txtBranco10" width="19%">Dia</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#505050" class="txtBranco10" width="19%">Sem. Ant.</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#505050" class="txtBranco10" width="19%">Sem. Atual</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#505050" class="txtBranco10">Mês</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="9"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Aguardando</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["aguardando"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["aguardando"]["semana_anterior"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["aguardando"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["aguardando"]["mes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="9"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Fechados</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["fechados"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["fechados"]["semana_anterior"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["fechados"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["fechados"]["mes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="9"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Agendados</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["semana_anterior"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["semana"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  }
  $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["agendados"]["mes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="9"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10">&nbsp;Ativos</td>
        <td bgcolor="#FFFFFF" width="1"></td>';
  if ($mes == date("m") && $ano == date("Y")) {
    $tabela .= '
        <td bgcolor="#828282" class="txtBranco10">'.$itens["ativos"]["dia"].'</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["ativos"]["semana_anterior"].'</td>
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
