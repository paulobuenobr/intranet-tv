<?

function buscaDiasUteisMes($dia,$mes,$ano) {
  $dias_uteis = 0;
  for ($i = 1; $i <= $dia; $i++) {
  	$semana = date("w", strtotime("{$ano}-{$mes}-{$i}"));
    if ($semana != '0' && $semana != '6') {
    	$dias_uteis++;
    }
  }
  return intval($dias_uteis);
}

function buscaDiasUteisSemana($dia,$mes,$ano) {
	$data = "{$ano}-{$mes}-{$dia}";
  $semana_do_ano = date("W", strtotime($data));
  $dias_uteis = 0;
  for ($i = 1; $i <= date("t", strtotime($data)); $i++) {
  	$semana = date("w", strtotime("{$ano}-{$mes}-{$i}"));
    if (date("W", strtotime("{$ano}-{$mes}-{$i}")) == $semana_do_ano && $semana != '0' && $semana != '6') {
    	$dias_uteis++;
    }
  }
  return $dias_uteis;
}

function formataNumero($numero) {
  return floatval(number_format($numero,1,".",""));
}

function formataDinheiro($valor,$moeda = 'R$') {
  $valor = preg_replace("/[^0-9]/", "", intval($valor));
  $valor = number_format($valor / 100, 2, ",", ".");
  if ($moeda) $valor = $moeda." ".$valor;
  return $valor;
}

function geraLinkPorPontosPorResponsavel($registros, $metas, $periodo) {
  if (sizeof($registros) > 0 ) {
    $responsaveis = array();
    $pontos = 0;
    for ($i = 0; $i < sizeof($registros); $i++) {
      $pontos += $registros[$i]["pontos"];
      $responsaveis[$registros[$i]["responsavel"]] += $registros[$i]["pontos"];
    }
    arsort($responsaveis);
    foreach ($responsaveis as $chave => $responsavel_atual) {
      $detalhes[] = $chave.": ".formataNumero($responsavel_atual);
    }
    $detalhes = @implode("<br>", $detalhes);
  }
  if ($metas) {
    $media = $pontos / $metas[$periodo];
    for ($i = 0; $i < sizeof($metas["niveis"]); $i++) {
      if ($media >= $metas["niveis"][$i]["minimo"] && $media < $metas["niveis"][$i]["maximo"]) {
        $cor = $metas["niveis"][$i]["cor"];
      }
    }
  } else {
    $cor = PAINEL_COR_RUIM;
  }
  $link = '<a onMouseOver="hintShow(\''.$detalhes.'\');" onMouseOut="hintHide();" href="#" class="txtBranco10" style="color: ' . $cor . '">'.formataNumero($pontos).($metas ? ' / '.formataNumero($metas[$periodo]) : '').'</a>';
  return $link;
}

function buscaContratoPontos($contrato, $com_corporativo = true) {
  global $bancodedados;
  $query = "
		SELECT
		  tab_assinaturas.ass_id AS id,
		  tab_assinaturas.pla_id AS plano,
		  tab_assinaturas.ass_parametros AS ass_parametros,
		  tab_planos.pls_id AS servico_id,
		  tab_planos.plt_id AS tipo_id,
		  tab_planos_servicos.pls_parametros AS pls_parametros
		FROM
		  rel_ass_cor_estacontido
		  INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
		  INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		  INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
		WHERE
		  rel_ass_cor_estacontido.cor_id = '$contrato'";
  $query = $bancodedados->query($query);
  $total_pontos = 0;
  while (($assinatura_atual = $bancodedados->resultadoArray($query)) != false) {
    $assinatura_valor_cobrado = assinaturaValorCobrado($assinatura_atual["id"]);
    $parametros = @unserialize($assinatura_atual["ass_parametros"]);
    $servicos_parametros = @unserialize($assinatura_atual["pls_parametros"]);
    if ($assinatura_atual["servico_id"] == 1 || $assinatura_atual["servico_id"] == 11) {
      if ($assinatura_valor_cobrado > 0) {
      	if ($assinatura_atual["tipo_id"] == 1) {
        	$pontos_principais = intval($parametros["pontos_quantidade"]);
        	$pontos_adicionais = 0;
      	} else {
        	$pontos_principais = 0;
        	$pontos_adicionais = intval($parametros["pontos_quantidade"]);
      	}
        if ($com_corporativo) {
        	$dominios = intval($servicos_parametros["tem_dominio"]);
        } else {
          $dominios = 0;
        }
        $total_pontos += $pontos_principais + ($pontos_adicionais / 3) + ($dominios / 2);
      }
    } else {
      if ($com_corporativo) $total_pontos += ($assinatura_valor_cobrado / 5000);
    }
  }
  return @round($total_pontos, 2);
}

function buscaAssinaturaPontos($assinatura, $com_corporativo = true) {
  global $bancodedados;
  $query = "
		SELECT
		  tab_assinaturas.ass_id AS id,
		  tab_assinaturas.pla_id AS plano,
		  tab_assinaturas.ass_parametros AS ass_parametros,
		  tab_planos.pls_id AS servico_id,
		  tab_planos.plt_id AS tipo_id,
		  tab_planos_servicos.pls_parametros AS pls_parametros
		FROM
		  tab_assinaturas
		  INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		  INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
		WHERE
		  tab_assinaturas.ass_id = '$assinatura'";
  $query = $bancodedados->query($query);
  $total_pontos = 0;
  $assinatura_atual = $bancodedados->resultadoArray($query);
  if ($assinatura_atual) {
    $assinatura_valor_cobrado = assinaturaValorCobrado($assinatura_atual["id"]);
    $parametros = @unserialize($assinatura_atual["ass_parametros"]);
    $servicos_parametros = @unserialize($assinatura_atual["pls_parametros"]);
    if ($assinatura_atual["servico_id"] == 1 || $assinatura_atual["servico_id"] == 11) {
      if ($assinatura_valor_cobrado > 0) {
      	if ($assinatura_atual["tipo_id"] == 1) {
        	$pontos_principais = intval($parametros["pontos_quantidade"]);
        	$pontos_adicionais = 0;
      	} else {
        	$pontos_principais = 0;
        	$pontos_adicionais = intval($parametros["pontos_quantidade"]);
      	}
        if ($com_corporativo) {
        	$dominios = intval($servicos_parametros["tem_dominio"]);
        } else {
          $dominios = 0;
        }
        $total_pontos += $pontos_principais + ($pontos_adicionais / 3) + ($dominios / 2);
      }
    } else {
      if ($com_corporativo) $total_pontos += ($assinatura_valor_cobrado / 5000);
    }
  }
  return @round($total_pontos, 2);
}

function buscaContratoResponsavel($contrato_id) {
  global $bancodedados;
  $query = "
		SELECT
		  tab_usuarios.usu_nome AS responsavel
		FROM
		  tab_vendas
		  INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
		WHERE
		  tab_vendas.cor_id = $contrato_id";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "responsavel");
}

function buscaAssinaturaResponsavel($assinatura_id) {
  global $bancodedados;
  $query = "
		SELECT
		  tab_usuarios.usu_nome AS responsavel
		FROM
		  tab_vendas
		  INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
		WHERE
		  tab_vendas.ass_id = $assinatura_id";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "responsavel");
}

function assinaturaValorCobrado($assinatura) {
  global $bancodedados;
  $query = "
		SELECT
		  tab_assinaturas.ass_id AS id,
		  tab_assinaturas.cli_id AS cliente,
		  tab_assinaturas.ass_mensalidade AS mensalidade,
		  tab_assinaturas.ass_parametros AS parametros,
		  tab_planos.pla_id AS plano_id,
		  tab_planos.pls_id AS plano_servico,
		  tab_planos.pla_id AS desconto_condominio
		FROM
		  tab_assinaturas
		  INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		WHERE
		  (tab_assinaturas.ass_id = '$assinatura')";
  $query = $bancodedados->query($query);
  $assinatura = $bancodedados->resultadoArray($query);
  $descontos_assinatura = buscaDescontosMensalidadeSemValidadeAssinatura($assinatura["id"], $assinatura["mensalidade"]);
  return ($assinatura["mensalidade"] - $descontos_assinatura);
}

function buscaDescontosMensalidadeSemValidadeAssinatura($assinatura, $mensalidade) {
  global $bancodedados;
  $query = "
		SELECT
		  dec_mensalidade AS mensalidade
		FROM
		  tab_descontos_concessoes
		WHERE
		  (dec_mensalidade > 0) AND
		  (dec_validade IS NULL) AND
		  (ass_id = '$assinatura')";
  $query = $bancodedados->query($query);
  $desconto_total = 0;
  while (($desconto_atual = $bancodedados->resultadoArray($query)) != false) {
    if ($desconto_atual["mensalidade"] <= 1) {
      $desconto_total += $mensalidade * $desconto_atual["mensalidade"];
    } else {
      $desconto_total += $desconto_atual["mensalidade"];
    }
  }
  return $desconto_total;
}

function buscaMetasMensais($setor,$mes,$ano) {
  if ($ano.$mes <= 200409) {
    $metas["tecnico"]["os_internas"] = 3000;
    $metas["tecnico"]["os_externas"] = 400;
    $metas["tecnico"]["os_sistemas"] = 1500;
    $metas["tecnico"]["os_estrutural"] = 400;

    $metas["comercial"]["fechados"] = 180;
    $metas["comercial"]["agendados"] = 160;
    $metas["comercial"]["ativos"] = 140;

    $metas["administrativo"]["pedidos_cancelamento"] = 50;
    $metas["administrativo"]["cancelamentos"] = 40;
    $metas["administrativo"]["agendados"] = 160;
    $metas["administrativo"]["ativos"] = 140;

    $metas["financeiro"]["inadimplentes_cobranca_atual"] = 0.15;
    $metas["financeiro"]["resgate_cobranca_anterior"] = 0.8;
    $metas["financeiro"]["resgate_cobrancas_restantes"] = 0.9;
    $metas["financeiro"]["crescimento"] = 0;
    $metas["financeiro"]["proxima_cobranca"] = 0;
  } elseif ($ano.$mes < 200702) {
    $metas["tecnico"]["os_internas"] = 3000;
    $metas["tecnico"]["os_externas"] = 400;
    $metas["tecnico"]["os_sistemas"] = 1500;
    $metas["tecnico"]["os_estrutural"] = 400;

    $metas["comercial"]["fechados"] = 230;
    $metas["comercial"]["agendados"] = $metas["comercial"]["fechados"] * 0.8;
    $metas["comercial"]["ativos"] = $metas["comercial"]["fechados"] * 0.7;

    $metas["administrativo"]["pedidos_cancelamento"] = 40;
    $metas["administrativo"]["cancelamentos"] = 35;
    $metas["administrativo"]["agendados"] = $metas["comercial"]["fechados"] * 0.9;
    $metas["administrativo"]["ativos"] = $metas["comercial"]["fechados"] * 0.8;

    $metas["financeiro"]["inadimplentes_cobranca_atual"] = 0.15;
    $metas["financeiro"]["resgate_cobranca_anterior"] = 0.8;
    $metas["financeiro"]["resgate_cobrancas_restantes"] = 0.9;
    $metas["financeiro"]["crescimento"] = 0.1;
    $metas["financeiro"]["proxima_cobranca"] = 0.1;
  } elseif ($ano.$mes < 200710) {
    $metas["tecnico"]["os_internas"] = 3000;
    $metas["tecnico"]["os_externas"] = 400;
    $metas["tecnico"]["os_sistemas"] = 1500;
    $metas["tecnico"]["os_estrutural"] = 400;

    $metas["comercial"]["fechados"] = 340;
    $metas["comercial"]["agendados"] = $metas["comercial"]["fechados"] * 0.9;
    $metas["comercial"]["ativos"] = $metas["comercial"]["fechados"] * 0.8;

    $metas["administrativo"]["pedidos_cancelamento"] = 40;
    $metas["administrativo"]["cancelamentos"] = 35;
    $metas["administrativo"]["agendados"] = $metas["comercial"]["fechados"] * 0.9;
    $metas["administrativo"]["ativos"] = 300;

    $metas["financeiro"]["inadimplentes_cobranca_atual"] = 0.15;
    $metas["financeiro"]["resgate_cobranca_anterior"] = 0.8;
    $metas["financeiro"]["resgate_cobrancas_restantes"] = 0.9;
    $metas["financeiro"]["crescimento"] = 0.1;
    $metas["financeiro"]["proxima_cobranca"] = 0.1;
  } else {
    $metas["tecnico"]["os_internas"] = 3000;
    $metas["tecnico"]["os_externas"] = 400;
    $metas["tecnico"]["os_sistemas"] = 1500;
    $metas["tecnico"]["os_estrutural"] = 400;

    $metas["comercial"]["fechados"] = 400;
    $metas["comercial"]["agendados"] = $metas["comercial"]["fechados"] * 0.9;
    $metas["comercial"]["ativos"] = $metas["comercial"]["fechados"] * 0.8;

    $metas["administrativo"]["pedidos_cancelamento"] = 40;
    $metas["administrativo"]["cancelamentos"] = 35;
    $metas["administrativo"]["agendados"] = $metas["comercial"]["fechados"] * 0.9;
    $metas["administrativo"]["ativos"] = $metas["comercial"]["fechados"] * 0.8;

    $metas["financeiro"]["inadimplentes_cobranca_atual"] = 0.15;
    $metas["financeiro"]["resgate_cobranca_anterior"] = 0.8;
    $metas["financeiro"]["resgate_cobrancas_restantes"] = 0.9;
    $metas["financeiro"]["crescimento"] = 0.1;
    $metas["financeiro"]["proxima_cobranca"] = 0.1;
  }

  return $metas[$setor];
}

?>
