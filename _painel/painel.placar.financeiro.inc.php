<?

function geraPainelPlacarConteudoFinanceiro($mes, $ano) {
  $metas = buscaMetasFinanceiro($mes, $ano);
  $itens["inadimplentes_cobranca_atual"] = buscaInadimplentesCobrancaAtual($mes, $ano, $metas["inadimplentes_cobranca_atual"]);
  $itens["resgate_cobranca_anterior"] = buscaResgateCobrancaAnterior($mes, $ano, $metas["resgate_cobranca_anterior"]);
  $itens["resgate_cobrancas_restantes"] = buscaResgateCobrancaRestantes($mes, $ano, $metas["resgate_cobrancas_restantes"]);
  $itens["crescimento"] = buscaValoresCrescimento($mes, $ano);
  $itens["proxima_cobranca"] = buscaValoresProximaCobranca($mes, $ano);
  return geraPainelPlacarConteudoTabelaFinanceiro($itens);
}

function buscaMetasFinanceiro($mes, $ano) {
  $metas_mensais = buscaMetasMensais("financeiro", $mes, $ano);

  $metas["inadimplentes_cobranca_atual"]["valor"] = $metas_mensais["inadimplentes_cobranca_atual"];
  $metas["inadimplentes_cobranca_atual"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["inadimplentes_cobranca_atual"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["inadimplentes_cobranca_atual"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);

  $metas["resgate_cobranca_anterior"]["valor"] = $metas_mensais["resgate_cobranca_anterior"];
  $metas["resgate_cobranca_anterior"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["resgate_cobranca_anterior"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["resgate_cobranca_anterior"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);

  $metas["resgate_cobrancas_restantes"]["valor"] = $metas_mensais["resgate_cobrancas_restantes"];
  $metas["resgate_cobrancas_restantes"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["resgate_cobrancas_restantes"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["resgate_cobrancas_restantes"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);

  $metas["crescimento"]["valor"] = $metas_mensais["crescimento"];
  $metas["crescimento"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["crescimento"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["crescimento"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);

  $metas["proxima_cobranca"]["valor"] = $metas_mensais["proxima_cobranca"];
  $metas["proxima_cobranca"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 0, "maximo" => 0.75);
  $metas["proxima_cobranca"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.75, "maximo" => 1);
  $metas["proxima_cobranca"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 1, "maximo" => 1000);

  return $metas;
}

function buscaInadimplentesCobrancaAtual($mes, $ano, $meta) {
  global $bancodedados;
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      coa_data_vencimento >= CURDATE() AND
      coa_pai IS NULL AND
      cot_id = 1";
  $query = $bancodedados->query($query);
  $valor_total = $bancodedados->resultado($query, 0, "valor");
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      coa_data_vencimento < CURDATE() AND
      coa_pai IS NULL AND
      cot_id = 1";
  $query = $bancodedados->query($query);
  $valor_inadimplencia = $bancodedados->resultado($query, 0, "valor");
  if ($valor_total) {
    $valor_proporcional = number_format($valor_inadimplencia / $valor_total, 2, ".", "");
  } else {
    $valor_proporcional = 0;
  }
  $nivel = $valor_proporcional/$meta["valor"];
  for ($i = 0; $i < sizeof($meta["niveis"]); $i++) {
    if ($nivel >= $meta["niveis"][$i]["minimo"] && $nivel < $meta["niveis"][$i]["maximo"]) {
      $cor = $meta["niveis"][$i]["cor"];
    }
  }
  $conteudo = '<span class="txtBranco10" style="color: '.$cor.'">'.formataDinheiro($valor_inadimplencia).' / '.formataDinheiro($valor_total).' ('.intval($valor_proporcional*100).'%)</span>';
  return $conteudo;
}

function buscaResgateCobrancaAnterior($mes, $ano, $meta) {
  global $bancodedados;
  $data = strtotime("{$ano}-{$mes}-01 -1 month");
  $mes_anterior = date("m", $data);
  $ano_anterior = date("Y", $data);
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento BETWEEN '$ano_anterior-$mes_anterior-01' AND '".date("Y-m-t", strtotime("$ano_anterior-$mes_anterior-01"))."' AND
      (coa_data_processamento BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' OR coa_data_processamento IS NULL) AND
      cof_id NOT IN (5, 6) AND
      coa_pai IS NULL";
  $query = $bancodedados->query($query);
  $valor_total = $bancodedados->resultado($query, 0, "valor");
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento BETWEEN '$ano_anterior-$mes_anterior-01' AND '".date("Y-m-t", strtotime("$ano_anterior-$mes_anterior-01"))."' AND
      coa_data_processamento BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      cof_id NOT IN (5, 6) AND
      coa_pai IS NULL";
  $query = $bancodedados->query($query);
  $valor_recuperado = $bancodedados->resultado($query, 0, "valor");
  if ($valor_total) {
    $valor_proporcional = number_format($valor_recuperado / $valor_total, 2, ".", "");
  } else {
    $valor_proporcional = 0;
  }
  $nivel = $valor_proporcional / $meta["valor"];
  for ($i = 0; $i < sizeof($meta["niveis"]); $i++) {
    if ($nivel >= $meta["niveis"][$i]["minimo"] && $nivel < $meta["niveis"][$i]["maximo"]) {
      $cor = $meta["niveis"][$i]["cor"];
    }
  }
  $conteudo = '<span class="txtBranco10" style="color: '.$cor.'">'.formataDinheiro($valor_recuperado).' / '.formataDinheiro($valor_total).' ('.intval($valor_proporcional * 100).'%)</span>';
  return $conteudo;
}

function buscaResgateCobrancaRestantes($mes, $ano, $meta) {
  global $bancodedados;
  $data = strtotime("{$ano}-{$mes}-01 -1 month");
  $mes_anterior = date("m", $data);
  $ano_anterior = date("Y", $data);
  // Busca Valor em Aberto
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento < '$ano_anterior-$mes_anterior-01' AND
      coa_pai IS NULL AND
      cot_id = 1";
  $query = $bancodedados->query($query);
  $valor_aberto = $bancodedados->resultado($query, 0, "valor");
  // Busca Valor Pago
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento < '$ano_anterior-$mes_anterior-01' AND
      coa_data_processamento BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)";
  $query = $bancodedados->query($query);
  $valor_recuperado = $bancodedados->resultado($query, 0, "valor");
  // Calcula Valores
  $valor_total = $valor_aberto + $valor_recuperado;
  $valor_proporcional = number_format($valor_recuperado / $valor_total, 2, ".", "");
  $nivel = $valor_proporcional / $meta["valor"];
  for ($i = 0; $i < sizeof($meta["niveis"]); $i++) {
    if ($nivel >= $meta["niveis"][$i]["minimo"] && $nivel < $meta["niveis"][$i]["maximo"]) {
      $cor = $meta["niveis"][$i]["cor"];
    }
  }
  $conteudo = '<span class="txtBranco10" style="color: '.$cor.'">'.formataDinheiro($valor_recuperado).' / '.formataDinheiro($valor_total).' ('.intval($valor_proporcional * 100).'%)</span>';
  return $conteudo;
}

function buscaValoresCrescimento($mes, $ano) {
  return "N/D";
  global $bancodedados;
  $query = "
    SELECT
      SUM(tab_assinaturas.ass_mensalidade) AS mensalidade,
      SUM(tab_assinaturas.ass_instalacao) AS instalacao,
      SUM(IF(descontos_determinados.dec_mensalidade <= 1, descontos_determinados.dec_mensalidade * tab_assinaturas.ass_mensalidade, descontos_determinados.dec_mensalidade)) AS descontos_determinados_mensalidade,
      SUM(IF(descontos_determinados.dec_instalacao <= 1, descontos_determinados.dec_instalacao * tab_assinaturas.ass_instalacao, descontos_determinados.dec_instalacao)) AS descontos_determinados_instalacao,
      SUM(IF(descontos_indeterminados.dec_mensalidade <= 1, descontos_indeterminados.dec_mensalidade * tab_assinaturas.ass_mensalidade, descontos_indeterminados.dec_mensalidade)) AS descontos_indeterminados_mensalidade,
      SUM(IF(descontos_indeterminados.dec_instalacao <= 1, descontos_indeterminados.dec_instalacao * tab_assinaturas.ass_instalacao, descontos_indeterminados.dec_instalacao)) AS descontos_indeterminados_instalacao
    FROM
      tab_assinaturas
      LEFT OUTER JOIN tab_descontos_concessoes descontos_determinados ON (descontos_determinados.ass_id = tab_assinaturas.ass_id) AND (descontos_determinados.dec_data_termino IS NOT NULL) AND (descontos_determinados.dcs_id IN (5, 7))
      LEFT OUTER JOIN tab_descontos_concessoes descontos_indeterminados ON (descontos_indeterminados.ass_id = tab_assinaturas.ass_id) AND (descontos_indeterminados.dec_data_termino IS NULL) AND (descontos_indeterminados.dcs_id IN (5, 7))
    WHERE
      ass_data_ativacao BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      ast_id = 2 AND
      ass_migrada IS NULL
    GROUP BY
      tab_assinaturas.ass_id";
  $query = $bancodedados->query($query);
  $contratos = $bancodedados->resultadoArrayTotal($query);
  foreach ($contratos as $contrato) {
    $instalacoes_valor += $contrato["instalacao"];
    $mensalidades_valor += $contrato["mensalidade"];
    $instalacoes_desconto_indeterminado += $contrato["descontos_indeterminados_instalacao"];
    $mensalidades_desconto_indeterminado += $contrato["descontos_indeterminados_mensalidade"];
    $instalacoes_desconto_determinado += $contrato["descontos_determinados_instalacao"];
    $mensalidades_desconto_determinado += $contrato["descontos_determinados_mensalidade"];
  }

  $detalhes_instalacao[] = "<b>Instalações:</b>";
  $detalhes_instalacao[] = "&raquo; Valor Bruto: ".formataDinheiro($instalacoes_valor);
  if ($instalacoes_desconto_indeterminado) $detalhes_instalacao[] = "&raquo; Desconto Indeterminado: - ".formataDinheiro($instalacoes_desconto_indeterminado);
  if ($instalacoes_desconto_determinado) $detalhes_instalacao[] = "&raquo; Desconto Determinado: - ".formataDinheiro($instalacoes_desconto_determinado);
  $detalhes_instalacao[] = "&raquo; Valor Liquído: ".formataDinheiro($instalacoes_valor - $instalacoes_desconto_indeterminado - $instalacoes_desconto_determinado);
  $detalhes_instalacao = implode("<br>", $detalhes_instalacao);

  $detalhes_mensalidade[] = "<b>Mensalidades:</b>";
  $detalhes_mensalidade[] = "&raquo; Valor Bruto: ".formataDinheiro($mensalidades_valor);
  if ($mensalidades_desconto_indeterminado) $detalhes_mensalidade[] = "&raquo; Desconto Indeterminado: - ".formataDinheiro($mensalidades_desconto_indeterminado);
  if ($mensalidades_desconto_determinado) $detalhes_mensalidade[] = "&raquo; Desconto Determinado: - ".formataDinheiro($mensalidades_desconto_determinado);
  $detalhes_mensalidade[] = "&raquo; Valor Liquído: ".formataDinheiro($mensalidades_valor - $mensalidades_desconto_indeterminado - $mensalidades_desconto_determinado);
  $detalhes_mensalidade = implode("<br>", $detalhes_mensalidade);

  $conteudo[] = '<a onMouseOver="hintShow(\''.$detalhes_instalacao.'\');" onMouseOut="hintHide();" href="#" class="txtBranco10">'.formataDinheiro($instalacoes_valor - $instalacoes_desconto_indeterminado - $instalacoes_desconto_determinado).'</a>';
  $conteudo[] = '<a onMouseOver="hintShow(\''.$detalhes_mensalidade.'\');" onMouseOut="hintHide();" href="#" class="txtBranco10">'.formataDinheiro($mensalidades_valor - $mensalidades_desconto_indeterminado - $mensalidades_desconto_determinado).'</a>';
  $conteudo = implode(" / ", $conteudo);
  return $conteudo;

}

function buscaValoresProximaCobranca($mes, $ano) {
  return "N/D";
  // Cobrança Anterior - Proporcional Anterior + Completo Anterior + Proporcional Atual
  global $bancodedados;
  $data = strtotime("{$ano}-{$mes}-01 -1 month");
  $mes_anterior = date("m", $data);
  $ano_anterior = date("Y", $data);
  // Cobrança Anterior
  $query = "
    SELECT
      SUM(coa_valor_original) AS valor
    FROM
      tab_cobrancas
    WHERE
      coa_data_vencimento BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      cof_id NOT IN (5, 6) AND
      coa_pai IS NULL";
  $query = $bancodedados->query($query);
  $cobranca_anterior = $bancodedados->resultado($query, 0, "valor");
  // Proporcional Anterior
  $proporcional_anterior = buscaValoresMensalidade($mes_anterior, $ano_anterior, true);
  // Completo Atual
  $completo_anterior = buscaValoresMensalidade($mes_anterior, $ano_anterior);
  // Proporcional Atual
  $proporcional_atual = buscaValoresMensalidade($mes, $ano, true);

  $detalhes[] = "Cobrança Anterior: ".formataDinheiro($cobranca_anterior);
  $detalhes[] = "Cres. Prop. (Mês Anterior): - ".formataDinheiro($proporcional_anterior);
  $detalhes[] = "Cres. Comp. (Mês Anterior): + ".formataDinheiro($completo_anterior);
  $detalhes[] = "Cres. Prop. (Mês Atual): + ".formataDinheiro($proporcional_atual);
  $detalhes[] = "Próxima Cobrança: ".formataDinheiro($cobranca_anterior - $proporcional_anterior + $completo_anterior + $proporcional_atual);
  $detalhes = implode("<br>", $detalhes);

  $conteudo = '<a onMouseOver="hintShow(\''.$detalhes.'\');" onMouseOut="hintHide();" href="#" class="txtBranco10">'.formataDinheiro($cobranca_anterior-$proporcional_anterior + $completo_anterior + $proporcional_atual).'</a>';
  return $conteudo;

}

function buscaValoresMensalidade($mes, $ano, $proporcional = false) {
  global $bancodedados;
  $dias_no_mes = date("t", strtotime("{$ano}-{$mes}-01"));
  $query = "
    SELECT
      DATE_FORMAT(cor_data_ativacao, '%d') AS ativacao,
      cor_parametros AS parametros
    FROM
      tab_contratos
    WHERE
      cor_data_ativacao BETWEEN '$ano-$mes-01' AND '".date("Y-m-t", strtotime("$ano-$mes-01"))."' AND
      crt_id IN (11, 12)";
  $query = $bancodedados->query($query);
  $contratos = $bancodedados->resultadoArrayTotal($query);
  for ($i=0;$i<sizeof($contratos);$i++) {
    $contrato_dados = @unserialize($contratos[$i]["parametros"]);
    if ($contrato_dados["contrato_versao"] == 5) {
      $contrato_ativacao = $contratos[$i]["ativacao"];
      $mensalidade_contrato = $contrato_dados["tabela_valor_original_mensalidade"];
      if ($contrato_dados["contrato_descontos"]) {
        foreach ($contrato_dados["contrato_descontos"] as $desconto) {
          $mensalidade_contrato -= $desconto["mensalidade"];
        }
      }
      if ($proporcional) $mensalidade_contrato = intval($mensalidade_contrato * ($dias_no_mes - $contrato_ativacao + 1) / $dias_no_mes);
      $valores_mensalidade += $mensalidade_contrato;
    } else {
      $contrato_ativacao = $contratos[$i]["ativacao"];
      $mensalidade_contrato = $contrato_dados["cobranca_mensalidade"];
      if ($contrato_dados["itens_descontos"]) {
        foreach ($contrato_dados["itens_descontos"] as $desconto) {
          $mensalidade_contrato -= $desconto["mensalidade"];
        }
      }
      if ($proporcional) $mensalidade_contrato = intval($mensalidade_contrato * ($dias_no_mes - $contrato_ativacao + 1) / $dias_no_mes);
      $valores_mensalidade += $mensalidade_contrato;
    }
  }
  return $valores_mensalidade;
}

function geraPainelPlacarConteudoTabelaFinanceiro($itens) {
  $tabela = '
    <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="3A5977">
        <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" align="left">&nbsp;Inadimplência (Atual)</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["inadimplentes_cobranca_atual"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" align="left">&nbsp;&raquo; Resgate (Anterior)</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["resgate_cobranca_anterior"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" align="left">&nbsp;&raquo; Resgate (Restantes)</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["resgate_cobrancas_restantes"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" align="left">&nbsp;Crescimento (Atual)</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["crescimento"].'</td>
      </tr>
      <tr>
        <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
      </tr>
      <tr align="center">
        <td bgcolor="#505050" class="txtBranco10" align="left">&nbsp;Próxima Cobrança</td>
        <td bgcolor="#FFFFFF" width="1"></td>
        <td bgcolor="#828282" class="txtBranco10">'.$itens["proxima_cobranca"].'</td>
      </tr>
    </table>';
  return $tabela;
}

?>
