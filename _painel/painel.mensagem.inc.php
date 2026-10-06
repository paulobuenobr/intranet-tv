<?

function geraPainelMensagem($usuario) {
  $mensagem = "Olá ".buscaNomeUsuario($usuario);
  $mensagem .= buscaPendenciasUsuario($usuario);
  return $mensagem;
}

function buscaNomeUsuario($usuario) {
  global $bancodedados;
  $query = "
    SELECT
      usu_nome AS nome,
      IF(usu_nascimento = CURDATE(), 1, 0) AS aniversariante
    FROM
      tab_usuarios
    WHERE
      usu_id = $usuario";
  $query = $bancodedados->query($query);
  $dados = $bancodedados->resultadoArray($query);
  if ($dados) {
    $dados["nome"] = explode(" ", $dados["nome"]);
    return "<a href=\"?m=usuarios&a=ver_dp\" class=\"txtBranco11\"><b>{$dados["nome"][0]}</b></a>".($dados["aniversariante"] ? " <img src=\"imagens/bolo_aniversario.png\" width=\"13\" height=\"13\" border=\"0\" style=\"margin:0px\" title=\"Feliz Aniversário!\">" : "");
  }
}

function buscaPendenciasUsuario($usuario) {
	$ajustes = contaAjustesFinanceirosPendentesUsuario($usuario);
	$pendencias_descricao = array();
  if ($ajustes) {
    $pendencias_descricao[] = "Ajustes Financeiros: " . $ajustes;
  }
  $contratos = contaContratosPendentesUsuario($usuario);
  if ($contratos) {
    $pendencias_descricao[] = "Contratos: " . $contratos;
  }
  $descontos = contaDescontosPendentesUsuario($usuario);
  if ($descontos) {
    $pendencias_descricao[] = "Descontos: ".$descontos;
  }
  $documentos = contaDocumentosPendentesUsuario($usuario);
  if ($documentos) {
    $pendencias_descricao[] = "Documentos:";
    $pendencias_descricao[] = "&raquo; Aguardando Resposta: " . $documentos;
  }
  $dominios = contaDominiosPendentesUsuario($usuario);
  if ($dominios) {
    $pendencias_descricao[] = "Domínios: " . $dominios;
  }
  $renegociacoes = contaRenegociacoesPendentesUsuario($usuario);
  if ($renegociacoes) {
    $pendencias_descricao[] = "Re-negociações: " . $renegociacoes;
  }
  $responsabilidades_clientes = contaResponsabilidadesClientesPendentesUsuario($usuario);
  $responsabilidades_condominios = contaResponsabilidadesCondominiosPendentesUsuario($usuario);
  if ($responsabilidades_clientes || $responsabilidades_condominios) {
    $pendencias_descricao[] = "Responsabilidades:";
  }
  if ($responsabilidades_clientes) {
    $pendencias_descricao[] = "&raquo; Aguardando Aprovação (Clientes): " . $responsabilidades_clientes;
  }
  if ($responsabilidades_condominios) {
    $pendencias_descricao[] = "&raquo; Aguardando Aprovação (Condomínios): " . $responsabilidades_condominios;
  }
  $retornos = contaRetornosPendentesUsuario($usuario);
  if ($retornos) {
    $pendencias_descricao[] = "Retornos: " . $retornos;
  }
  $visitas = contaVisitasPendentesUsuario($usuario);
  if ($visitas) {
    $pendencias_descricao[] = "Visitas: " . $visitas;
  }
  $solicitacoes_de_cancelamento = contaSolicitacoesDeCancelamentosPendentesUsuario($usuario);
  if (count($solicitacoes_de_cancelamento)) {
    $pendencias_descricao[] = "Solicitações de Cancelamento:";
    if (array_key_exists("aguardando_contato", $solicitacoes_de_cancelamento)) $pendencias_descricao[] = "&raquo; Aguardando Contato: " . $solicitacoes_de_cancelamento["aguardando_contato"];
    if (array_key_exists("em_andamento", $solicitacoes_de_cancelamento)) $pendencias_descricao[] = "&raquo; Em Andamento: " . $solicitacoes_de_cancelamento["em_andamento"];
    if (array_key_exists("aguardando_ciencia", $solicitacoes_de_cancelamento)) $pendencias_descricao[] = "&raquo; Aguardando Tomada de Ciência: " . $solicitacoes_de_cancelamento["aguardando_ciencia"];
    if (array_key_exists("aguardando_aprovacao_desconto", $solicitacoes_de_cancelamento)) $pendencias_descricao[] = "&raquo; Aguardando Aprovação de Desconto: " . $solicitacoes_de_cancelamento["aguardando_aprovacao_desconto"];
  }
  $pendencias_contagem = intval($retornos + $responsabilidades_clientes + $responsabilidades_condominios + $ajustes + $descontos + $contratos + $dominios + $documentos + $visitas + @array_sum($solicitacoes_de_cancelamento) + $renegociacoes);
  if ($pendencias_contagem > 0) {
    $pendencias_mensagem = ', você possui <a href="#" onMouseOver="hintShow(\\\'' . implode("<br>",$pendencias_descricao) . '\\\');" onMouseOut="hintHide();"><b style="color: #FF1111">' . $pendencias_contagem . '</b></a> pendência';
	  if ($pendencias_contagem > 1) {
	    $pendencias_mensagem .= 's';
	  }
  	return $pendencias_mensagem;
  }
  return "";
}

function contaRetornosPendentesUsuario($usuario) {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(clr_id) AS retornos
    FROM
      tab_clientes_retornos
    WHERE
      usu_id = $usuario AND
      clr_data <= CURDATE() AND
      ces_id = 1";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "retornos");
}

function contaResponsabilidadesClientesPendentesUsuario($usuario) {
  global $bancodedados;
  if (verificaPermissaoResponsabilidade($usuario, "clientes_responsabilidades", "aprovar")) {
    $query = "
      SELECT
        COUNT(cln_id) AS responsabilidades
      FROM
        tab_clientes_responsabilidades
      WHERE
        cra_id = 1 AND
        NOW() BETWEEN cln_inicio AND cln_termino";
    $query = $bancodedados->query($query);
    return $bancodedados->resultado($query, 0, "responsabilidades");
  }
}

function contaResponsabilidadesCondominiosPendentesUsuario($usuario) {
  global $bancodedados;
  if (verificaPermissaoResponsabilidade($usuario, "condominios_responsabilidades", "aprovar")) {
    $query = "
      SELECT
        COUNT(cod_id) AS responsabilidades
      FROM
        tab_condominios_responsabilidades
      WHERE
        cru_id = 1 AND
        NOW() BETWEEN cod_inicio AND cod_termino";
    $query = $bancodedados->query($query);
    return $bancodedados->resultado($query, 0, "responsabilidades");
  }
}

function verificaPermissaoResponsabilidade($usuario, $modulo, $acao) {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(rel_gru_aco_tempermissao.aco_id) AS temPermissao
    FROM
      tab_usuarios
      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
      INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
      INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
      INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
    WHERE
      tab_modulos.mod_nome = '$modulo' AND
      tab_acoes.aco_nome = '$acao' AND
      tab_usuarios.usu_id = $usuario";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "temPermissao");
}

function contaAjustesFinanceirosPendentesUsuario($usuario) {
	$ajustes = 0;
  // Administrativo
  $temPermissao = verificaPermissaoAjustesFinanceiros($usuario, 359, 360);
  if ($temPermissao > 0) {
    $ajustes += contaAjustesFinanceirosPendetesTipo(8);
  }
  // Comercial
  $temPermissao = verificaPermissaoAjustesFinanceiros($usuario, 361, 362);
  if ($temPermissao > 0) {
    $ajustes += contaAjustesFinanceirosPendetesTipo(3);
  }
  // Fincanceiro
  $temPermissao = verificaPermissaoAjustesFinanceiros($usuario, 363, 364);
  if ($temPermissao > 0) {
    $ajustes += contaAjustesFinanceirosPendetesTipo(6);
  }
  // Técnico
  $temPermissao = verificaPermissaoAjustesFinanceiros($usuario, 365, 366);
  if ($temPermissao > 0) {
    $ajustes += contaAjustesFinanceirosPendetesTipo(5);
  }
  // T.I.
  $temPermissao = verificaPermissaoAjustesFinanceiros($usuario, 367, 368);
  if ($temPermissao > 0) {
    $ajustes += contaAjustesFinanceirosPendetesTipo(9);
  }
  return $ajustes;
}

function verificaPermissaoAjustesFinanceiros($usuario, $aprovar, $reprovar) {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(rel_gru_aco_tempermissao.aco_id) AS temPermissao
    FROM
      tab_usuarios
      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
      INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
    WHERE
      aco_id IN ($aprovar, $reprovar, 369, 370) AND
      tab_usuarios.usu_id = $usuario";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "temPermissao");
}

function contaAjustesFinanceirosPendetesTipo($tipo) {
  global $bancodedados;
  $query = "
  SELECT
    COUNT(ajf_id) AS ajustes
  FROM
    tab_ajustes_financeiros
  WHERE
    afs_id = 1 AND
    afc_id = $tipo";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "ajustes");
}

function contaRenegociacoesPendentesUsuario($usuario) {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(cli_id) AS renegociacoes
    FROM
      tab_clientes
    WHERE
      usu_id = $usuario AND
      clu_id = 4";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "renegociacoes");
}

function contaSolicitacoesDeCancelamentosPendentesUsuario($usuario) {
	$solicitacoes = array();
  if (gerenciaSolicitacoesDeCancelamentoUsuarioAcao($usuario, 146)) {
    $solicitacoes["aguardando_contato"] = contaSolicitacoesDeCancelamentoPendentesStatus(1);
    $solicitacoes["em_andamento"] = contaSolicitacoesDeCancelamentoPendentesStatus(2);
  }
  $solicitacoes["aguardando_ciencia"] = contaSolicitacoesDeCancelamentoPendentesUsuarioTipo($usuario,4);
  if (gerenciaSolicitacoesDeCancelamentoUsuarioAcao($usuario, 149)) {
    $solicitacoes["aguardando_ciencia"] += contaSolicitacoesDeCancelamentoPendentesComercial();
  }
  $solicitacoes["aguardando_aprovacao_desconto"] = contaSolicitacoesDeCancelamentoPendentesUsuarioTipo($usuario,6);
  return $solicitacoes;
}

function gerenciaSolicitacoesDeCancelamentoUsuarioAcao($usuario, $acao) {
  global $bancodedados;
  $query = "SELECT usu_id AS id FROM rel_usu_gru_fazparte INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id) WHERE rel_usu_gru_fazparte.usu_id = $usuario AND rel_gru_aco_tempermissao.aco_id = $acao";
  $query = $bancodedados->query($query);
  if ($bancodedados->linhas($query)) return true;
}

function contaSolicitacoesDeCancelamentoPendentesStatus($status) {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(DISTINCT can_id) AS contagem
    FROM
      tab_cancelamentos
    WHERE
      cas_id = $status";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "contagem");
}

function contaSolicitacoesDeCancelamentoPendentesUsuarioTipo($usuario, $tipo) {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(DISTINCT tab_cancelamentos.can_id) AS contagem
    FROM
      tab_cancelamentos
      INNER JOIN tab_cancelamentos_etapas ON (tab_cancelamentos.can_id = tab_cancelamentos_etapas.can_id)
    WHERE
      tab_cancelamentos.cas_id <> 3 AND
      tab_cancelamentos_etapas.cet_id = $tipo AND
      tab_cancelamentos_etapas.ces_id = 1 AND
      tab_cancelamentos_etapas.cae_parametros LIKE '%s:7:\"usuario\";i:$usuario%'";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "contagem");
}

function contaSolicitacoesDeCancelamentoPendentesComercial () {
  global $bancodedados;
  $query = "
    SELECT
      COUNT(DISTINCT tab_cancelamentos.can_id) AS contagem
    FROM
      tab_cancelamentos
      INNER JOIN tab_cancelamentos_etapas ON (tab_cancelamentos.can_id = tab_cancelamentos_etapas.can_id)
    WHERE
      tab_cancelamentos.cas_id <> 3 AND
      tab_cancelamentos_etapas.cet_id = 9 AND
      tab_cancelamentos_etapas.ces_id = 1";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "contagem");
}

function contaDescontosPendentesUsuario($usuario) {
  global $bancodedados;
  if (gerenciaDescontosUsuario($usuario, "2, 4, 22, 28")) {
    $query = "
      SELECT
        COUNT(dec_id) AS descontos
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
      WHERE
        tab_clientes.cli_nome != '' AND
        tab_descontos_concessoes.dcs_id = 1 AND
        tab_descontos_concessoes.det_id IN (1, 2, 4)";
  } elseif (gerenciaDescontosUsuario($usuario, "8")) {
    $query = "
      SELECT
        COUNT(dec_id) AS descontos
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
      WHERE
        tab_clientes.cli_nome != '' AND
        tab_descontos_concessoes.dcs_id = 1 AND
        tab_descontos_concessoes.det_id IN (3)";
  } else {
    $query = "
      SELECT
        DISTINCT COUNT(dec_id) AS descontos
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
      WHERE
        tab_clientes.cli_nome != '' AND
        tab_clientes.usu_id = $usuario AND
        tab_descontos_concessoes.dcs_id = 1";
  }
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "descontos");
}

function contaDocumentosPendentesUsuario($usuario) {
  global $bancodedados;
    $query = "
      SELECT
        DISTINCT COUNT(*) AS documentos
      FROM
        tab_documentos_faqs
        LEFT OUTER JOIN tab_documentos_faqs resposta ON (tab_documentos_faqs.dof_id = resposta.dof_pai)
      WHERE
        resposta.dof_id IS NULL AND
        tab_documentos_faqs.dof_pai IS NULL";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, "documentos");
}

function gerenciaDescontosUsuario($usuario, $grupos) {
  global $bancodedados;
  $query = "
    SELECT
      gru_id AS grupos
    FROM
      rel_usu_gru_fazparte
    WHERE
      usu_id = $usuario AND
      gru_id IN (2, 4, 22, 28)";
  $query = $bancodedados->query($query);
  if ($bancodedados->linhas($query)) {
    return true;
  }
}

function contaContratosPendentesUsuario($usuario) {
  if (gerenciaContratosUsuario($usuario)) {
    global $bancodedados;
    $query = "
      SELECT
        COUNT(cor_id) AS contratos
      FROM
        tab_contratos
      WHERE
        crs_id IN (4, 19)";
    $query = $bancodedados->query($query);
    return $bancodedados->resultado($query, 0, "contratos");
  }
}

function gerenciaContratosUsuario($usuario) {
  global $bancodedados;
  $query = "
    SELECT
      gru_id AS grupos
    FROM
      rel_usu_gru_fazparte
    WHERE
      usu_id = $usuario AND
      gru_id = 14";
  $query = $bancodedados->query($query);
  if ($bancodedados->linhas($query)) {
    return true;
  }
}

function contaDominiosPendentesUsuario($usuario) {
  if (gerenciaDominiosUsuario($usuario)) {
    global $bancodedados;
    $query = "
      SELECT
        COUNT(dom_id) AS dominios
      FROM
        tab_dominios
      WHERE
        dos_id = 2";
    $query = $bancodedados->query($query);
    return $bancodedados->resultado($query, 0, "dominios");
  }
}

function gerenciaDominiosUsuario($usuario) {
  global $bancodedados;
  $query = "
    SELECT
      gru_id AS grupos
    FROM
      rel_usu_gru_fazparte
    WHERE
      usu_id = $usuario AND
      gru_id = 7";
  $query = $bancodedados->query($query);
  if ($bancodedados->linhas($query)) {
    return true;
  }
}

function contaVisitasPendentesUsuario($usuario) {
  if (gerenciaVisitasUsuario($usuario)) {
    global $bancodedados;
    $query = "
      SELECT DISTINCT
        COUNT(tab_visitas.vis_id) AS visitas_aguardando_agendamento
      FROM
        tab_visitas
        INNER JOIN rel_ass_cor_estacontido ON (tab_visitas.cor_id = rel_ass_cor_estacontido.cor_id)
        INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
      WHERE
        tab_visitas.via_id = 1 AND
        tab_condominios.cos_id = 2";
    $query = $bancodedados->query($query);
    $visitas_aguardando_agendamento = $bancodedados->resultado($query, 0, "visitas_aguardando_agendamento");
    if ($visitas_aguardando_agendamento) {
    	$visitas_descricao[] = "A:" . $visitas_aguardando_agendamento;
    }
    $query = "
      SELECT DISTINCT
        COUNT(tab_visitas.vis_id) AS visitas_pendentes
      FROM
        tab_visitas
        INNER JOIN rel_ass_cor_estacontido ON (tab_visitas.cor_id = rel_ass_cor_estacontido.cor_id)
        INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
      WHERE
        tab_visitas.via_id = 1 AND
        tab_visitas.vis_data < CURDATE() AND
        tab_condominios.cos_id = 2";
    $query = $bancodedados->query($query);
    $visitas_pendentes = $bancodedados->resultado($query, 0, "visitas_pendentes");
    if ($visitas_pendentes) {
      $visitas_descricao[] = "P:" . $visitas_pendentes;
    }
    $visitas_contagem = intval($visitas_aguardando_agendamento + $visitas_pendentes);
    if ($visitas_contagem > 0) {
      return $visitas_contagem . " (" . implode(", ", $visitas_descricao) . ")";
    }
  }
}

function gerenciaVisitasUsuario($usuario) {
  global $bancodedados;
  $query = "
    SELECT
      gru_id AS grupos
    FROM
      rel_usu_gru_fazparte
    WHERE
      usu_id = $usuario AND
      gru_id IN (14, 15)";
  $query = $bancodedados->query($query);
  if ($bancodedados->linhas($query)) {
    return true;
  }
}

?>
