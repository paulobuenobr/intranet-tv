<?

class cron_placar {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  #######################
  ### Atualiza Placar ###
  #######################

  function atualizaPlacar() {
    $referencia = date("Y-m-d");
    while (strtotime($referencia) > strtotime("2014-11-01")) {
      $this->geraPlacar($referencia);
      $referencia = date("Y-m-d", strtotime("$referencia yesterday"));
    }
  }

  function atualizaPlacarHoje() {
    $referencia = date("Y-m-d");
    while (strtotime($referencia) <= strtotime("today")) {
      $this->geraPlacar($referencia);
      $referencia = date("Y-m-d", strtotime("$referencia tomorrow"));
    }
  }

  function atualizaPlacarSemana() {
    $referencia = date("Y-m-d", strtotime("7 days ago"));
    while (strtotime($referencia) <= strtotime("today")) {
      $this->geraPlacar($referencia);
      $referencia = date("Y-m-d", strtotime("$referencia tomorrow"));
    }
  }

  function atualizaPlacarMes() {
    $referencia = date("Y-m-01", strtotime("1 month ago"));
    while (strtotime($referencia) <= strtotime("today")) {
      $this->geraPlacar($referencia);
      $referencia = date("Y-m-d", strtotime("$referencia tomorrow"));
    }
  }

  ###################
  ### Gera Placar ###
  ###################
  
  function geraPlacar($referencia) {
    $painel["clientes_ativos"] = $this->geraClientesAtivos($referencia, null, "Clientes Ativos");
    $painel["clientes_novos"] = $this->geraClientesAtivos($referencia, $referencia, "Clientes Novos (Ativações)");
    $painel["fechamentos_vendas"] = $this->geraFechamentos($referencia);
    $painel["fechamentos_migracoes"] = $this->geraFechamentos($referencia, null, false);
    $painel["clientes_cancelados"] = $this->geraClientesCancelados($referencia);
    $painel["pedidos_de_cancelamento"] = $this->geraPedidosCancelamento($referencia);
    $painel["clientes_retidos"] = $this->geraPedidosCancelamento($referencia, null, 3);
    $painel["clientes_perdidos"] = $this->geraPedidosCancelamento($referencia, null, 4);
    // Serializa Painel
    $painel = serialize($painel);
    // Registra Painel
    $query = "SELECT plc_id AS id FROM tab_placares WHERE plb_id = 5 AND plc_data = '$referencia'";
    $query = $this->bancodedados->query($query);
    $id = $this->bancodedados->resultado($query, 0, "id");
    if ($id) {
      $query = "UPDATE tab_placares SET plc_geracao = NOW(), plc_conteudo = '$painel' WHERE plc_id = $id";
      $this->bancodedados->query($query);
    } else {
      $query = "INSERT INTO tab_placares (plb_id, plc_data, plc_geracao, plc_conteudo) VALUES (5, '$referencia', NOW(), '$painel')";
      $this->bancodedados->query($query);
    }
  }

  ######################################
  ### Painel - Clientes Ativos/Novos ###
  ######################################

  function geraClientesAtivos($referencia, $referencia_final = null, $tipo = "Clientes Ativos") {
	$painel = array();
    if ($tipo == "Clientes Ativos") {
    	// Ativos
      //$where[] = "tab_planos.pls_id NOT IN (2)";
      $where[] = "tab_assinaturas.ass_data_ativacao <= '$referencia'";
      $where[] = "(tab_assinaturas.ass_data_cancelamento IS NULL OR tab_assinaturas.ass_data_cancelamento >= '$referencia')";
    } else {
    	// Novos
      //$where[] = "tab_planos.pls_id NOT IN (2, 21)";
      $where[] = "tab_assinaturas.ass_migrada IS NULL";
      $where[] = "tab_assinaturas.ass_data_ativacao BETWEEN '$referencia' AND '$referencia_final'";
    }
    $where = implode(" AND \n        ", $where);
    $query = "
      SELECT
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.cli_id AS cliente_id,
        tab_assinaturas.ass_mensalidade AS mensalidade,
        tab_planos.pla_parametros AS parametros,
		tab_planos.pla_id AS plano_id,
        tab_planos.pla_nome AS plano_nome,
        tab_planos_servicos.pls_id AS servico_id,
        tab_planos_servicos.pls_nome AS servico_nome
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        $where";
   $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      $velocidade = array_key_exists("autenticacoes_velocidade_download", $dados["parametros"]) && $dados["parametros"]["autenticacoes_velocidade_download"] ? $dados["parametros"]["autenticacoes_velocidade_download"] : $dados["parametros"]["pontos_velocidade_download"];
      if (in_array($dados["plano_id"], array(94, 159))) {
        $plano = "IP Válido";
        $agrupador = "IP Válido";
        $ordem = "10.1.";
      } elseif (in_array($dados["servico_id"], array(28, 33))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Residencial";
        $ordem = "01.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(1, 32))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Condomínios";
        $ordem = "03.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(5))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Residencial";
        $ordem = "01.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(3, 34, 35))) {
		if ($dados["plano_id"] == 526) {
			$plano = "Olé Wi-Fi Zone";
		} else {
			$plano = $this->formataVelocidade($velocidade);
		}
        $agrupador = "Olé Empresas";
        $ordem = "04.1.".sprintf("%010s", $velocidade);
//      } elseif (in_array($dados["servico_id"], array(14))) {
//        $plano = $dados["plano_nome"];
//        $agrupador = "Hospedagem";
//        $ordem = "05.1.";
      } elseif (in_array($dados["servico_id"], array(21))) {
        $plano = "Olé Garantia";
        $agrupador = "Olé Garantia";
        $ordem = "07.1.";
      } elseif (in_array($dados["servico_id"], array(11, 14))) {
        $plano = "Hospedagem";
        $agrupador = "Hospedagem";
        $ordem = "08.1.";
      } elseif (in_array($dados["servico_id"], array(20))) {
        $plano = "Contas de E-mail";
        $agrupador = "Contas de E-mail";
        $ordem = "09.1.";
      } elseif (in_array($dados["servico_id"], array(9))) {
        $plano = "Co-Location";
        $agrupador = "Co-Location";
        $ordem = "10.1.";
      } elseif (in_array($dados["servico_id"], array(23))) {
        $plano = "ADSL";
        $agrupador = "ADSL";
        $ordem = "11.1.";
      } elseif (in_array($dados["servico_id"], array(26))) {
        $plano = "Manutenção";
        $agrupador = "Manutenção";
        $ordem = "12.1.";
      } elseif (in_array($dados["servico_id"], array(25))) {
        $plano = "Sonork";
        $agrupador = "Sonork";
        $ordem = "13.1.";
      } elseif (in_array($dados["servico_id"], array(27))) {
        $plano = "Contrato Personalizado";
        $agrupador = "Contrato Personalizado";
        $ordem = "14.1.";
      } else {
        $plano = "{$dados["servico_nome"]}";
        $agrupador = "{$dados["servico_nome"]}";
        $ordem = "15.1.{$dados["servico_nome"]}";
      }
	  
      $dados["mensalidade"] = (int) $dados["mensalidade"] - $this->buscaDescontos($dados["assinatura_id"], $dados["mensalidade"]);
      $dados["mensalidade"] = $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
	  
	  if (!array_key_exists($agrupador.$plano, $painel)) {
		  $painel[$agrupador.$plano]["quantidade"] = 0;
		  $painel[$agrupador.$plano]["valor"] = 0;
	  }	  
      $painel[$agrupador.$plano]["descricao"] = $plano;
      $painel[$agrupador.$plano]["quantidade"] += 1;
      $painel[$agrupador.$plano]["valor"] += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
      $painel[$agrupador.$plano]["ordem"] = $ordem;
      // Agrupado
      if ($plano != $agrupador) {
        $painel[$agrupador]["descricao"] = $agrupador;
        $painel[$agrupador]["quantidade"] += 1;
        $painel[$agrupador]["valor"] += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
        $painel[$agrupador]["ordem"] = substr($painel[$agrupador.$plano]["ordem"], 0, 2).".0.";
        $painel[$agrupador]["subgrupo_agrupador"] = $this->formataNome($agrupador);
        $painel[$agrupador]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_item"] = $this->formataNome($agrupador);
      } else {
        $painel[$agrupador.$plano]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_agrupador"] = $this->formataNome($agrupador);
      }
      // Geral
      $painel[$tipo]["descricao"] = $tipo;
      $painel[$tipo]["quantidade"][$dados["cliente_id"]] = $dados["cliente_id"];
      $painel[$tipo]["valor"] += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
      $painel[$tipo]["ordem"] = "00.0.";
      $painel[$tipo]["cor_linha"] = "#828282";
      $painel[$tipo]["cor_texto"] = "#FFFFFF";
    }
    if ($painel) {
      $painel[$tipo]["quantidade"] = count($painel[$tipo]["quantidade"]);
      $painel[$tipo]["agrupador"] = $this->formataNome($tipo);
      return $painel;
    } else {
      return null;
    }
  }

  ############################
  ### Painel - Fechamentos ###
  ############################

  function geraFechamentos($referencia, $referencia_final = null, $vendas = true) {
    if ($vendas) {
      $tipo = "Fechamentos (Vendas)";
      $where[] = "tab_assinaturas.ass_migrada IS NULL";
    } else {
      $tipo = "Fechamentos (Migrações)";
      $where[] = "tab_assinaturas.ass_migrada IS NOT NULL";
    }
    if ($referencia_final) {
      $where[] = "tab_assinaturas.ass_data_confirmacao BETWEEN '$referencia' AND '$referencia_final'";
    } else {
      $where[] = "tab_assinaturas.ass_data_confirmacao = '$referencia'";
    }
    $where = implode(" AND \n        ", $where);
    $query = "
      SELECT
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ass_migrada AS assinatura_migrada_id,
        tab_assinaturas.cli_id AS cliente_id,
        tab_assinaturas.ass_mensalidade AS mensalidade,
        tab_planos.pla_parametros AS parametros,
		tab_planos.pla_id AS plano_id,
        tab_planos.pla_nome AS plano_nome,
        tab_planos_servicos.pls_id AS servico_id,
        tab_planos_servicos.pls_nome AS servico_nome,
        tab_usuarios.usu_id AS usuario_id,
        tab_usuarios.usu_nome AS usuario_nome
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
        INNER JOIN tab_vendas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
        INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
      WHERE
        $where";
   $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["mensalidade"] = (int) $dados["mensalidade"] - $this->buscaDescontos($dados["assinatura_id"], $dados["mensalidade"]);
      if ($dados["assinatura_migrada_id"]) {
        $dados["mensalidade"] -= $this->buscaValorAssinatura($dados["assinatura_migrada_id"]);
      }
      $dados["mensalidade"] = $dados["mensalidade"] >= 0 ? $dados["mensalidade"] : 0;
      // Vendedor
      $painel[$dados["usuario_nome"]]["usuario_id"] = $dados["usuario_id"];
      $painel[$dados["usuario_nome"]]["descricao"] = $dados["usuario_nome"];
      $painel[$dados["usuario_nome"]]["quantidade"] += 1;
      $painel[$dados["usuario_nome"]]["valor"] += $dados["mensalidade"];
      $painel[$dados["usuario_nome"]]["ordem"] = "00.0.{$dados["usuario_nome"]}";
      $painel[$dados["usuario_nome"]]["cor_linha"] = "#D8E3EF";
      $painel[$dados["usuario_nome"]]["subgrupo_agrupador"] = $this->formataNome($tipo);
      $painel[$dados["usuario_nome"]]["subgrupo_agrupador_id"] = "1";
      $painel[$dados["usuario_nome"]]["subgrupo_agrupador_nome"] = "Vendedor";
      // Plano Contratato
      $dados["parametros"] = unserialize($dados["parametros"]);
      $velocidade = array_key_exists("autenticacoes_velocidade_download", $dados["parametros"]) && $dados["parametros"]["autenticacoes_velocidade_download"] ? $dados["parametros"]["autenticacoes_velocidade_download"] : $dados["parametros"]["pontos_velocidade_download"];
      if (in_array($dados["plano_id"], array(94, 159))) {
        $plano = "IP Válido";
        $agrupador = "IP Válido";
        $ordem = "10.1.";
      } elseif (in_array($dados["servico_id"], array(28, 33))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Residencial";
        $ordem = "01.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(1, 32))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Condomínios";
        $ordem = "02.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(5))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Residencial";
        $ordem = "01.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(3, 34, 35))) {
		if ($dados["plano_id"] == 526) {
			$plano = "Olé Wi-Fi Zone";
		} else {
			$plano = $this->formataVelocidade($velocidade);
		}
        $agrupador = "Olé Empresas";
        $ordem = "04.1.".sprintf("%010s", $velocidade);
//      } elseif (in_array($dados["servico_id"], array(14))) {
//        $plano = $dados["plano_nome"];
//        $agrupador = "Hospedagem";
//        $ordem = "05.1.";
//      } elseif (in_array($dados["servico_id"], array(14))) {
//        $plano = $dados["plano_nome"];
//        $agrupador = "Hospedagem";
//        $ordem = "06.1.";
      } elseif (in_array($dados["servico_id"], array(21))) {
        $plano = "Olé Garantia";
        $agrupador = "Olé Garantia";
        $ordem = "07.1.";
      } elseif (in_array($dados["servico_id"], array(11, 14))) {
        $plano = "Hospedagem";
        $agrupador = "Hospedagem";
        $ordem = "08.1.";
      } elseif (in_array($dados["servico_id"], array(20))) {
        $plano = "Contas de E-mail";
        $agrupador = "Contas de E-mail";
        $ordem = "09.1.";
      } elseif (in_array($dados["servico_id"], array(9))) {
        $plano = "Co-Location";
        $agrupador = "Co-Location";
        $ordem = "10.1.";
      } elseif (in_array($dados["servico_id"], array(23))) {
        $plano = "ADSL";
        $agrupador = "ADSL";
        $ordem = "11.1.";
      } elseif (in_array($dados["servico_id"], array(26))) {
        $plano = "Manutenção";
        $agrupador = "Manutenção";
        $ordem = "12.1.";
      } elseif (in_array($dados["servico_id"], array(25))) {
        $plano = "Sonork";
        $agrupador = "Sonork";
        $ordem = "13.1.";
      } elseif (in_array($dados["servico_id"], array(27))) {
        $plano = "Contrato Personalizado";
        $agrupador = "Contrato Personalizado";
        $ordem = "14.1.";
      } else {
        $plano = "{$dados["servico_nome"]}";
        $agrupador = "{$dados["servico_nome"]}";
        $ordem = "15.1.{$dados["servico_nome"]}";
      }
      $painel[$agrupador.$plano]["descricao"] = $plano;
      $painel[$agrupador.$plano]["quantidade"] += 1;
      $painel[$agrupador.$plano]["valor"] += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
      $painel[$agrupador.$plano]["ordem"] = $ordem;
      // Agrupado
      if ($plano != $agrupador) {
        $painel[$agrupador]["descricao"] = $agrupador;
        $painel[$agrupador]["quantidade"] += 1;
        $painel[$agrupador]["valor"] += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
        $painel[$agrupador]["ordem"] = substr($painel[$agrupador.$plano]["ordem"], 0, 2).".0.";
        $painel[$agrupador]["subgrupo_agrupador"] = $this->formataNome($agrupador);
        $painel[$agrupador]["subgrupo_agrupador_id"] = "2";
        $painel[$agrupador]["subgrupo_agrupador_nome"] = "Plano Contratado";
        $painel[$agrupador]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_item"] = $this->formataNome($agrupador);
      } else {
        $painel[$agrupador.$plano]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_agrupador"] = $this->formataNome($agrupador);
      }
      $painel[$agrupador.$plano]["subgrupo_agrupador_id"] = "2";
      $painel[$agrupador.$plano]["subgrupo_agrupador_nome"] = "Plano Contratado";
      // Geral
      $painel[$tipo]["descricao"] = $tipo;
      $painel[$tipo]["quantidade"] += 1;
      $painel[$tipo]["valor"] += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
      $painel[$tipo]["ordem"] = "00.0.";
      $painel[$tipo]["cor_linha"] = "#828282";
      $painel[$tipo]["cor_texto"] = "#FFFFFF";
      $painel[$tipo]["agrupador"] = $this->formataNome($tipo);
      $painel[$tipo]["agrupador_subgrupo"] = true;
    }
    return $painel;
  }

  ##################################
  ### Painel - Clientes Perdidos ###
  ##################################

  function geraClientesCancelados($referencia, $referencia_final = null) {
    $where[] = "tab_contratos.crs_id IN (21, 22)";
    $where[] = "tab_contratos.pls_id NOT IN (21)";
    $where[] = "tab_contratos.cor_pai IS NULL";
    if ($referencia_final) {
      $where[] = "tab_contratos.cor_data_cancelamento BETWEEN '$referencia' AND '$referencia_final'";
    } else {
      $where[] = "tab_contratos.cor_data_cancelamento = '$referencia'";
    }
    $where = implode(" AND \n        ", $where);
    $query = "
      SELECT
        tab_contratos.cli_id AS cliente_id,
        tab_contratos.cor_id AS contrato_id,
        tab_cancelamentos_motivos.cam_nome AS motivo
      FROM
        tab_contratos
        LEFT OUTER JOIN tab_cancelamentos ON (tab_contratos.cor_id = tab_cancelamentos.cor_id AND tab_cancelamentos.cas_id = 4)
        LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id)
      WHERE
        $where
      GROUP BY
        cliente_id
      ORDER BY
        tab_cancelamentos.can_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $valor_contrato = $this->buscaValorCliente($dados["cliente_id"], $referencia, $referencia_final);
      // Dados Motivos de Cancelamento
      $dados["motivo"] = trim(preg_replace('/\(.*\)/', '', $dados["motivo"]));
      $dados["motivo"] = $dados["motivo"] ? $dados["motivo"] : "Sem solicitação";
      // Motivos de Cancelamento
      $painel[$dados["motivo"]]["descricao"] = $dados["motivo"];
      $painel[$dados["motivo"]]["quantidade"] += 1;
      $painel[$dados["motivo"]]["valor"] += $valor_contrato;
      $painel[$dados["motivo"]]["ordem"] = $dados["motivo"] == "Sem solicitação" ? "00.1." : "00.0.{$dados["motivo"]}";
      $painel[$dados["motivo"]]["cor_linha"] = "#D8E3EF";
      $painel[$dados["motivo"]]["subgrupo_agrupador"] = "clientes_cancelados";
      $painel[$dados["motivo"]]["subgrupo_agrupador_id"] = "1";
      $painel[$dados["motivo"]]["subgrupo_agrupador_nome"] = "Motivo de Cancelamento";
      // Dados Plano Principal
      $dados_contrato = $this->buscaDadosContrato($dados["contrato_id"]);
      $agrupador = $dados_contrato["agrupador"];
      $plano = $dados_contrato["plano"];
      $ordem = $dados_contrato["ordem"];
      // Plano Principal
      $painel[$agrupador.$plano]["descricao"] = $plano;
      $painel[$agrupador.$plano]["quantidade"] += 1;
      $painel[$agrupador.$plano]["valor"] += $valor_contrato;
      $painel[$agrupador.$plano]["ordem"] = $ordem;
      // Plano Principal Agrupado
      if ($plano != $agrupador) {
        $painel[$agrupador]["descricao"] = $agrupador;
        $painel[$agrupador]["quantidade"] += 1;
        $painel[$agrupador]["valor"] += $valor_contrato;
        $painel[$agrupador]["ordem"] = substr($ordem, 0, 2).".0.";
        $painel[$agrupador]["subgrupo_agrupador"] = $this->formataNome($agrupador);
        $painel[$agrupador]["subgrupo_agrupador_id"] = "2";
        $painel[$agrupador]["subgrupo_agrupador_nome"] = "Plano Contratado";
        $painel[$agrupador]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_item"] = $this->formataNome($agrupador);
      } else {
        $painel[$agrupador.$plano]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_agrupador"] = $this->formataNome($agrupador);
      }
      $painel[$agrupador.$plano]["subgrupo_agrupador_id"] = "2";
      $painel[$agrupador.$plano]["subgrupo_agrupador_nome"] = "Plano Contratado";
      // Geral
      $painel["clientes_cancelados"]["descricao"] = "Clientes Cancelados";
      $painel["clientes_cancelados"]["quantidade"] += 1;
      $painel["clientes_cancelados"]["ordem"] = "00.0.";
      $painel["clientes_cancelados"]["valor"] += $valor_contrato;
      $painel["clientes_cancelados"]["cor_linha"] = "#828282";
      $painel["clientes_cancelados"]["cor_texto"] = "#FFFFFF";
      $painel["clientes_cancelados"]["agrupador"] = "clientes_cancelados";
      $painel["clientes_cancelados"]["agrupador_subgrupo"] = true;
    }
    return $painel;
  }

  ########################################
  ### Painel - Pedidos de Cancelamento ###
  ########################################

  function geraPedidosCancelamento($referencia, $referencia_final = null, $status_id = null) {
    $where[] = "tab_contratos.pls_id NOT IN (21)";
    if ($status_id == 3) {
      $tipo = "Clientes Retidos";
      $where[] = "tab_cancelamentos.cas_id = 3";
    } elseif ($status_id == 4) {
      $tipo = "Clientes Perdidos";
      $where[] = "tab_cancelamentos.cas_id = 4";
    } else {
      $tipo = "Pedidos de Cancelamento";
    }
    $agrupador = $this->formataNome($tipo);
    if ($referencia_final) {
      $where[] = "tab_cancelamentos.can_inicio BETWEEN '$referencia 00:00:00' AND '$referencia_final 23:59:59'";
    } else {
      $where[] = "tab_cancelamentos.can_inicio LIKE '$referencia%'";
    }
    $where = implode(" AND \n        ", $where);
    $query = "
      SELECT
        tab_cancelamentos.cli_id AS cliente_id,
        tab_cancelamentos.cor_id AS contrato_id,
        tab_cancelamentos_motivos.cam_nome AS motivo
      FROM
        tab_cancelamentos
        INNER JOIN tab_contratos ON (tab_cancelamentos.cor_id = tab_contratos.cor_id)
        LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id)
      WHERE
        $where
      GROUP BY
        cliente_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      // Dados Motivos de Cancelamento
      $dados["motivo"] = trim(preg_replace('/\(.*\)/', '', $dados["motivo"]));
      $dados["motivo"] = $dados["motivo"] ? $dados["motivo"] : "Aguardando contato";
      $valor_contrato = $this->buscaValorContrato($dados["contrato_id"]);
      // Motivos de Cancelamento
      $painel[$dados["motivo"]]["descricao"] = $dados["motivo"];
      $painel[$dados["motivo"]]["quantidade"] += 1;
      $painel[$dados["motivo"]]["valor"] += $valor_contrato;
      $painel[$dados["motivo"]]["ordem"] = ($dados["motivo"] == "Aguardando contato" ? "00.0." : "00.1").$dados["motivo"];
      $painel[$dados["motivo"]]["cor_linha"] = "#D8E3EF";
      $painel[$dados["motivo"]]["subgrupo_agrupador"] = $this->formataNome($tipo."_".$agrupador);
      $painel[$dados["motivo"]]["subgrupo_agrupador_id"] = "1";
      $painel[$dados["motivo"]]["subgrupo_agrupador_nome"] = "Motivo de Cancelamento";
      // Dados Plano Principal
      $dados_contrato = $this->buscaDadosContrato($dados["contrato_id"]);
      $agrupador = $dados_contrato["agrupador"];
      $plano = $dados_contrato["plano"];
      $ordem = $dados_contrato["ordem"];
      // Plano Principal
      $painel[$agrupador.$plano]["descricao"] = $plano;
      $painel[$agrupador.$plano]["quantidade"] += 1;
      $painel[$agrupador.$plano]["valor"] += $valor_contrato;
      $painel[$agrupador.$plano]["ordem"] = $ordem;
      // Plano Principal Agrupado
      if ($plano != $agrupador) {
        $painel[$agrupador]["descricao"] = $agrupador;
        $painel[$agrupador]["quantidade"] += 1;
        $painel[$agrupador]["valor"] += $valor_contrato;
        $painel[$agrupador]["ordem"] = substr($ordem, 0, 2).".0.";
        $painel[$agrupador]["subgrupo_agrupador"] = $this->formataNome($tipo."_".$agrupador);
        $painel[$agrupador]["subgrupo_agrupador_id"] = "2";
        $painel[$agrupador]["subgrupo_agrupador_nome"] = "Plano Contratado";
        $painel[$agrupador]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_item"] = $this->formataNome($tipo."_".$agrupador);
      } else {
        $painel[$agrupador.$plano]["cor_linha"] = "#D8E3EF";
        $painel[$agrupador.$plano]["subgrupo_agrupador"] = $this->formataNome($tipo."_".$agrupador);
      }
      $painel[$agrupador.$plano]["subgrupo_agrupador_id"] = "2";
      $painel[$agrupador.$plano]["subgrupo_agrupador_nome"] = "Plano Contratado";
      // Geral
      $painel[$tipo]["descricao"] = $tipo;
      $painel[$tipo]["quantidade"] += 1;
      $painel[$tipo]["ordem"] = "00.0.";
      $painel[$tipo]["valor"] += $valor_contrato;
      $painel[$tipo]["cor_linha"] = "#828282";
      $painel[$tipo]["cor_texto"] = "#FFFFFF";
      $painel[$tipo]["agrupador"] = $this->formataNome($tipo."_".$agrupador);
      $painel[$tipo]["agrupador_subgrupo"] = true;
    }
    return $painel;
  }

  ####################
  ### Painel Merge ###
  ####################

  function agrupaPainel($painel_1, $painel_2, $sufixo) {
    $chaves = array_keys($painel_1);
    for ($h = 0; $h < count($chaves); $h++) {
      $tipo = $chaves[$h];
      for ($i = 0; $i < count($painel_1[$tipo]); $i++) {
        for ($j = 0; $j < count($painel_2[$tipo]); $j++) {
          if ($painel_1[$tipo][$i]["ordem"] == $painel_2[$tipo][$j]["ordem"]) {
            $painel_1[$tipo][$i]["quantidade_{$sufixo}"] = $painel_2[$tipo][$j]["quantidade"];
            $painel_1[$tipo][$i]["valor_{$sufixo}"] = $painel_2[$tipo][$j]["valor"];
            unset($painel_2[$tipo][$j]);
            sort($painel_2[$tipo]);
            break;
          }
        }
      }
      if ($painel_2[$tipo]) {
        for ($j = 0; $j < count($painel_2[$tipo]); $j++) {
          $id = count($painel_1[$tipo]);
          foreach ($painel_2[$tipo][$j] as $campo => $valor) {
            if ($campo == "quantidade" || $campo == "valor") {
              $campo = "{$campo}_{$sufixo}";
            }
            $painel_1[$tipo][$id][$campo] = $valor;
          }
        }
      }
    }
    return $painel_1;
  }

  ###########################
  ### Busca Dados - Geral ###
  ###########################

  function buscaDadosContrato($contrato_id) {
    $query = "
      SELECT
        tab_planos.pls_id AS servico_id,
        tab_planos.pla_id AS plano_id,
        tab_planos.pla_nome AS plano_nome,
        tab_planos.pla_parametros AS parametros,
        tab_planos_servicos.pls_nome AS servico_nome
      FROM
        rel_ass_cor_estacontido
        INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        rel_ass_cor_estacontido.cor_id = $contrato_id AND
        tab_assinaturas.ass_pai IS NULL";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      $velocidade = array_key_exists("autenticacoes_velocidade_download", $dados["parametros"]) && $dados["parametros"]["autenticacoes_velocidade_download"] ? $dados["parametros"]["autenticacoes_velocidade_download"] : $dados["parametros"]["pontos_velocidade_download"];
      if (in_array($dados["plano_id"], array(94, 159))) {
        $plano = "IP Válido";
        $agrupador = "IP Válido";
        $ordem = "10.1.";
      } elseif (in_array($dados["servico_id"], array(28, 33))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Residencial";
        $ordem = "01.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(1, 32))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Condomínios";
        $ordem = "02.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(5))) {
        $plano = $this->formataVelocidade($velocidade);
        $agrupador = "Olé Residencial";
        $ordem = "01.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(3, 34, 35))) {
		if ($dados["plano_id"] == 526) {
			$plano = "Olé Wi-Fi Zone";
		} else {
			$plano = $this->formataVelocidade($velocidade);
		}
        $agrupador = "Olé Empresas";
        $ordem = "04.1.".sprintf("%010s", $velocidade);
      } elseif (in_array($dados["servico_id"], array(14))) {
        $plano = $dados["plano_nome"];
        $agrupador = "Hospedagem";
        $ordem = "05.1.";
      } elseif (in_array($dados["servico_id"], array(21))) {
        $plano = "Olé Garantia";
        $agrupador = "Olé Garantia";
        $ordem = "06.1.";
      } elseif (in_array($dados["servico_id"], array(11))) {
        $plano = "Hospedagem";
        $agrupador = "Hospedagem";
        $ordem = "07.1.";
      } elseif (in_array($dados["servico_id"], array(20))) {
        $plano = "Contas de E-mail";
        $agrupador = "Contas de E-mail";
        $ordem = "08.1.";
      } elseif (in_array($dados["servico_id"], array(9))) {
        $plano = "Co-Location";
        $agrupador = "Co-Location";
        $ordem = "09.1.";
      } elseif (in_array($dados["servico_id"], array(23))) {
        $plano = "ADSL";
        $agrupador = "ADSL";
        $ordem = "20.1.";
      } elseif (in_array($dados["servico_id"], array(26))) {
        $plano = "Manutenção";
        $agrupador = "Manutenção";
        $ordem = "11.1.";
      } elseif (in_array($dados["servico_id"], array(25))) {
        $plano = "Sonork";
        $agrupador = "Sonork";
        $ordem = "12.1.";
      } elseif (in_array($dados["servico_id"], array(27))) {
        $plano = "Contrato Personalizado";
        $agrupador = "Contrato Personalizado";
        $ordem = "13.1.";
      } else {
        $plano = "{$dados["servico_nome"]} ({$dados["plano_nome"]})";
        $agrupador = "{$dados["servico_nome"]} ({$dados["plano_nome"]})";
        $ordem = "14.1.{$dados["servico_nome"]} ({$dados["plano_nome"]})";
      }
      return array("plano" => $plano, "agrupador" => $agrupador, "ordem" => $ordem);
    }
  }

  function buscaValorAssinatura($assinatura_id) {
    $query = "
      SELECT
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ass_mensalidade AS mensalidade
      FROM
        tab_assinaturas
      WHERE
        tab_assinaturas.ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["mensalidade"] = (int) $dados["mensalidade"] - $this->buscaDescontos($dados["assinatura_id"], $dados["mensalidade"]);
      return $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
    }
  }

  function buscaDescontos($assinatura_id, $mensalidade) {
    $query = "
      SELECT
        SUM(IF(dec_mensalidade <= 1, dec_mensalidade * $mensalidade, dec_mensalidade)) AS mensalidade
      FROM
        tab_descontos_concessoes
      WHERE
        ass_id = $assinatura_id AND
        dec_mensalidade > 0 AND
        dec_validade IS NULL";
    $query = $this->bancodedados->query($query);
    return (int) $this->bancodedados->resultado($query, 0, "mensalidade");
  }

  function buscaValorCliente($cliente_id, $referencia, $referencia_final = null) {
    $where[] = "tab_assinaturas.cli_id = $cliente_id";
    $where[] = "tab_assinaturas.ast_id IN (3, 5)";
    if ($referencia_final) {
      $where[] = "tab_assinaturas.ass_data_cancelamento BETWEEN '$referencia' AND '$referencia_final'";
    } else {
      $where[] = "tab_assinaturas.ass_data_cancelamento = '$referencia'";
    }
    $where = implode(" AND \n        ", $where);
    $query = "
      SELECT
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ass_mensalidade AS mensalidade
      FROM
        tab_assinaturas
      WHERE
        $where";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["mensalidade"] -= $this->buscaDescontos($dados["assinatura_id"], $dados["mensalidade"]);
      $valor_total += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
    }
    return $valor_total;
  }

  function buscaValorContrato($contrato_id) {
    $where[] = "rel_ass_cor_estacontido.cor_id = $contrato_id";
    $where = implode(" AND \n        ", $where);
    $query = "
      SELECT
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ass_mensalidade AS mensalidade
      FROM
        tab_assinaturas
        INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
      WHERE
        $where";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["mensalidade"] -= $this->buscaDescontos($dados["assinatura_id"], $dados["mensalidade"]);
      $valor_total += $dados["mensalidade"] > 0 ? $dados["mensalidade"] : 0;
    }
    return $valor_total;
  }

  ##################
  ### Formatação ###
  ##################

  function formataNome($nome) {
    $nome = strtolower($nome);
    $nome = str_replace(array("ç", "ñ"), array("c", "n"), $nome);
    $nome = preg_replace("/[áàâãäª]/", "a", $nome);
    $nome = preg_replace("/[éèêë]/", "e", $nome);
    $nome = preg_replace("/[íìîï]/", "i", $nome);
    $nome = preg_replace("/[óòôõö°º]/", "o", $nome);
    $nome = preg_replace("/[úùûü]/", "u", $nome);
    $nome = preg_replace("/[^a-z0-9]/", " ", $nome);
    $nome = preg_replace("/[\r\n\t _]+/", "_", trim($nome));
    return $nome;
  }

  function formataVelocidade($velocidade) {
 	  $indice = floor(log($velocidade, 1024));
 	  $indice = $indice < 1 ? 1 : $indice;
 	  return round($velocidade / pow(1024, $indice), 1)." ".$this->buscaUnidade("bps", $indice);

  }

	function buscaUnidade($unidade, $indice = null) {
  	$unidades["1"] = "K$unidade";
	  $unidades["2"] = "M$unidade";
	  $unidades["3"] = "G$unidade";
	  $unidades["4"] = "T$unidade";
		return $indice ? $unidades[$indice] : $unidades;
	}

}

?>
