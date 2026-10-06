<?

class integracao_painel_planos_dia {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "listar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

	##############
	### Listar ###
	##############
	
	function listar() {
	  $conteudo["opcoes"] = $this->buscaOpcoes($_GET["usuario"]);
	  $conteudo["historico"] = $this->buscaHistorico($_GET["usuario"]);
    $this->conteudo = serialize($conteudo);
	}
	
	function buscaOpcoes($cliente_id) {
	  $query = "
	    SELECT
	      sac.sac_id AS id,
	      sac.sac_nome AS nome,
	      sac.sac_valor AS valor,
	      sac.sac_duracao AS duracao,
	      sac.sac_regras AS regras
	    FROM
	      tab_servicos_ajustes_categorias sac
	    WHERE
	      sac.sac_status = 1 AND
	      sac.sac_valor IS NOT NULL AND
	      sac.sac_duracao IS NOT NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	//if (in_array($cliente_id, array(66435, 61861, 78536, 46053, 73029, 53021, 84697, 62613, 61835, 68639, 77558, 60666, 62518, 63410, 86174, 60892, 80427, 67640, 76981, 52065, 60677))) {
    	//	$dados["valor"] = 0;
    	//}
      $dados["valor"] = $this->formataDinheiro($dados["valor"]);
      $dados["duracao"] = $dados["duracao"] ? "{$dados["duracao"]} dia".($dados["duracao"] > 1 ? "s" : "") : "";
      $dados["regras"] = unserialize($dados["regras"]);
      foreach ($dados["regras"] as $regra) {
        if ($regra["servico"] == 9 && $regra["ajuste_tipo"] == 2) {
          $dados["download"] = $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
        } elseif ($regra["servico"] == 1 && $regra["ajuste_tipo"] == 2) {
          $dados["upload"] = $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
        }
      }
      $opcoes[] = $dados;
    }
    return $opcoes;
	}
	
	function buscaHistorico($cliente_id) {
	  $query = "
	    SELECT
	      saj.saj_id AS id,
	      (SELECT sac_nome FROM tab_servicos_ajustes_categorias sac WHERE sac.sac_id = saj.sac_id) AS plano,
	      DATE_FORMAT(saj.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
	      DATE_FORMAT(saj.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
	      saj.saj_valor AS valor,
	      saj.sas_id AS status_id,
	      IF(saj.sas_id = 5 AND saj.saj_inicio > NOW(), 1, 0) AS agendado,
	      IF((SELECT COUNT(*) FROM tab_cobrancas_detalhes cod WHERE cod.ajf_id = saj.ajf_id) = 0, 1, 0) AS removivel,
        (SELECT DATE_FORMAT(coa.coa_data_vencimento, '%m/%Y') FROM tab_cobrancas coa WHERE coa.cli_id = saj.cli_id AND coa.cot_id NOT IN (6, 12) AND coa_pai IS NULL AND coa.coa_id IN (SELECT cod.coa_id FROM tab_cobrancas_detalhes cod WHERE cod.ajf_id = saj.ajf_id)) AS vencimento
	    FROM
	      tab_servicos_ajustes saj
	    WHERE
	      saj.cli_id = $cliente_id AND
	      saj.sas_id IN (5, 7, 8, 9) AND
	      saj.sac_id IN (SELECT sac.sac_id FROM tab_servicos_ajustes_categorias sac WHERE sac.sac_valor IS NOT NULL AND sac.sac_duracao IS NOT NULL)
	    ORDER BY
		    (SELECT coa.coa_data_vencimento FROM tab_cobrancas coa WHERE coa.cli_id = saj.cli_id AND coa.cot_id NOT IN (6, 12) AND coa_pai IS NULL AND coa.coa_id IN (SELECT cod.coa_id FROM tab_cobrancas_detalhes cod WHERE cod.ajf_id = saj.ajf_id)),
	      saj.saj_inicio DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["agendado"]) {
        $dados["status"] = "Agendado";
      } elseif ($dados["status_id"] == 5 && !$this->ajustePublicado($dados["id"])) {
        $dados["status"] = "Configurando...";
      } else {
        $dados["status"] = $this->buscaStatus($dados["status_id"]);
      }
      $dados["valor"] = $this->formataDinheiro($dados["valor"]);
      $dados["regras"] = $this->buscaRegras($dados["id"]);
      if ($dados["regras"]) {
        foreach ($dados["regras"] as $regra) {
          if ($regra["servico"] == 9 && $regra["ajuste_tipo"] == 2) {
            $dados["download"] = $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
          } elseif ($regra["servico"] == 1 && $regra["ajuste_tipo"] == 2) {
            $dados["upload"] = $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
          }
        }
      }
      $historico[] = $dados;
    }
    return $historico;
	}
	
  function buscaRegras($ajuste_id) {
    $query = "
      SELECT
        sat_id AS servico,
        sar_ajuste AS ajuste,
        sar_dia_semana AS dia_semana,
        DATE_FORMAT(sar_hora_inicio, '%H:%i') AS hora_inicio,
        DATE_FORMAT(sar_hora_termino, '%H:%i') AS hora_termino
      FROM
        tab_servicos_ajustes_regras
      WHERE
        saj_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["servico"] = $dados["servico"] ? $dados["servico"] : 0;
      $dados["ajuste_tipo"] = $dados["ajuste"] <= 1 ? 1 : 2;
      $dados["ajuste_unidade"] = $dados["ajuste_tipo"] == 2 ? floor(log($dados["ajuste"], 1024)) : "";
      $dados["ajuste_valor"] = $dados["ajuste"] <= 1 ? $dados["ajuste"] * 100 : floor($dados["ajuste"] / pow(1024, $dados["ajuste_unidade"]));
      $dados["dia_semana"] = $dados["dia_semana"] ? $dados["dia_semana"] : 0;
      $dados["hora_inicio"] = $dados["hora_inicio"] ? $dados["hora_inicio"] : "00:00";
      $dados["hora_termino"] = $dados["hora_termino"] ? $dados["hora_termino"] : "24:00";
      $regras[] = $dados;
    }
    return $regras;
  }
  
	function buscaDadosHistorico($ajuste_id) {
	  $query = "
	    SELECT
	      saj.saj_id AS id,
	      saj.cli_id AS cliente_id,
	      (SELECT sac_nome FROM tab_servicos_ajustes_categorias sac WHERE sac.sac_id = saj.sac_id) AS plano,
	      DATE_FORMAT(saj.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
	      DATE_FORMAT(saj.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
	      saj.saj_valor AS valor,
	      saj.sas_id AS status_id,
	      IF(saj.sas_id = 5 AND saj.saj_inicio > NOW(), 1, 0) AS agendado,
	      IF((SELECT COUNT(*) FROM tab_cobrancas_detalhes cod WHERE cod.ajf_id = saj.ajf_id) = 0, 1, 0) AS removivel,
	      (SELECT DATE_FORMAT(coa.coa_data_vencimento, '%m/%Y') FROM tab_cobrancas coa WHERE coa.cli_id = saj.cli_id AND coa.cot_id NOT IN (6, 12) AND coa.coa_id IN (SELECT cod.coa_id FROM tab_cobrancas_detalhes cod WHERE cod.ajf_id = saj.ajf_id)) AS vencimento,
	      (SELECT sar.sar_ajuste FROM tab_servicos_ajustes_regras sar WHERE sar.saj_id = $ajuste_id AND sar.sat_id = 9) AS download,
	      (SELECT sar.sar_ajuste FROM tab_servicos_ajustes_regras sar WHERE sar.saj_id = $ajuste_id AND sar.sat_id = 1) AS upload
	    FROM
	      tab_servicos_ajustes saj
	    WHERE
	      saj.saj_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["agendado"]) {
        $dados["status"] = "Agendado";
      } elseif ($dados["status_id"] == 5 && !$this->ajustePublicado($dados["id"])) {
        $dados["status"] = "Configurando...";
      } else {
        $dados["status"] = $this->buscaStatus($dados["status_id"]);
      }
      $dados["valor"] = $this->formataDinheiro($dados["valor"]);
      return $dados;
    }
	}
	
	function ajustePublicado($ajuste_id) {
	  $query = "
	    SELECT
	      cau.cau_ajuste_download AS download,
	      cau.cau_ajuste_upload AS upload,
	      cau.cau_publicado AS publicado,
	      (SELECT IF(sar.sar_ajuste >= 1, sar.sar_ajuste, sar.sar_ajuste * cau.cau_ajuste_download) FROM tab_servicos_ajustes_regras sar WHERE sar.saj_id = $ajuste_id AND sar.sat_id = 9) AS ajuste_download,
	      (SELECT IF(sar.sar_ajuste >= 1, sar.sar_ajuste, sar.sar_ajuste * cau.cau_ajuste_upload) FROM tab_servicos_ajustes_regras sar WHERE sar.saj_id = $ajuste_id AND sar.sat_id = 1) AS ajuste_upload
	    FROM
	      tab_clientes_autenticacoes cau
	    WHERE
	      cau.cau_id IN (SELECT cau_id FROM rel_saj_cau_contem WHERE saj_id = $ajuste_id)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["publicado"] && $dados["download"] == $dados["ajuste_download"] && $dados["upload"] == $dados["ajuste_upload"];
    }
	  $query = "
	    SELECT
	      clp.clp_parametros_ajuste AS parametros_ajuste,
	      clp.clp_publicado AS publicado,
	      (SELECT sar.sar_ajuste FROM tab_servicos_ajustes_regras sar WHERE sar.saj_id = $ajuste_id AND sar.sat_id = 9) AS ajuste_download,
	      (SELECT sar.sar_ajuste FROM tab_servicos_ajustes_regras sar WHERE sar.saj_id = $ajuste_id AND sar.sat_id = 1) AS ajuste_upload
	    FROM
	      tab_clientes_pontos clp
	    WHERE
	      clp.clp_id IN (SELECT clp_id FROM rel_saj_clp_contem WHERE saj_id = $ajuste_id)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros_ajuste"] = unserialize($dados["parametros_ajuste"]);
      $dados["download"] = $dados["parametros_ajuste"]["download"];
      $dados["upload"] = $dados["parametros_ajuste"]["upload"];
      $dados["ajuste_download"] = $dados["ajuste_download"] >= 1 ? $dados["ajuste_download"] : $dados["ajuste_download"] * $dados["download"];
      $dados["ajuste_upload"] = $dados["ajuste_upload"] >= 1 ? $dados["ajuste_upload"] : $dados["ajuste_upload"] * $dados["upload"];
      return $dados["publicado"] && $dados["download"] == $dados["ajuste_download"] && $dados["upload"] == $dados["ajuste_upload"];
    }
	}
	
	function buscaStatus($status_id) {
	  $status[1] = "Inativo";
	  $status[2] = "Inativo";
	  $status[3] = "Cancelado";
	  $status[4] = "Cancelado";
	  $status[5] = "Ativo";
	  $status[6] = "Cancelado";
	  $status[7] = "Expirado";
	  $status[8] = "Cancelado";
	  $status[9] = "Ativo";
	  return $status[$status_id];
	}
	
	#################
	### Contratar ###
	#################
	
	function contratar() {
	  $dados = array();
    $servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, "", false);
    $dados_categoria = $servicos_ajustes->buscaDadosCategoria($_GET["i"]);
	  if ($this->clienteBloqueado($_GET["usuario"])) {
	    $dados["erro"] = "Por favor, verifique sua situação com o setor\nfinanceiro antes de continuar.";
	  } elseif ($servicos_ajustes->buscaAjusteAtivo($_GET["usuario"], !$_POST["data"] ? date("Y-m-d H:i:s") : substr($_POST["data"], 6, 4)."-".substr($_POST["data"], 3, 2)."-".substr($_POST["data"], 0, 2)." ".substr($_POST["data"], 12, 2).":".substr($_POST["data"], 15, 2).":00", $dados_categoria["duracao"])) {
	    $dados["erro"] = "Você já contratou um Plano Dia para o período informado.";
	  } elseif (!$this->velocidadeValidada($dados_categoria["download"], $_POST["velocidade_minima"], $_POST["velocidade_maxima"])) {
	    $dados["erro"] = "O Plano Dia solicitado não está disponível no momento.\nEntre em contato com o Suporte Técnico.";
	  } else {
	  	$ajuste_id = $servicos_ajustes->insereAjusteCliente($_GET["usuario"], $_GET["i"], "<b>Olé Controle</b>: Contratado pelo cliente ({$_GET["ip_cliente"]})", true, $_POST["data"]);
      if ($ajuste_id) {
        $dados["plano"] = $this->buscaDadosHistorico($ajuste_id);
      } else {
	      $dados["erro"] = "Erro durante processamento da solicitação.\n\nAguarde alguns instantes e tente novamente ou\nentre em contato com o Suporte Técnico.";
      }
	  }
    $this->conteudo = serialize($dados);
	}
	
	function velocidadeValidada($velocidade, $velocidade_minima, $velocidade_maxima) {
	  return (!$velocidade_minima || $velocidade >= $velocidade_minima) && (!$velocidade_maxima || $velocidade <= $velocidade_maxima);
	}
	
	function clienteBloqueado($cliente_id) {
	  $query = "
	   SELECT 
	     COUNT(*) bloqueios
     FROM 
       tab_servicos_ajustes saj 
     WHERE 
       saj.cli_id = $cliente_id AND 
       saj.sas_id IN (5, 9) AND 
       saj.sac_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["bloqueios"] >= 1;
    }
    return false;
	}
	
	############################
	### Cancelar Agendamento ###
	############################
	
	function cancelar_agendamento() {
	  $dados_historico = $this->buscaDadosHistorico($_GET["i"]);
	  if ($dados_historico && (!$dados_historico["agendado"] || !$dados_historico["removivel"])) {
      $dados["erro"] = "Sem permissão para cancelar o Plano Dia selecionado.\nEntre em contato com o Suporte Técnico.";
	  } elseif (!$dados_historico || $dados_historico["cliente_id"] != $_GET["usuario"]) {
      $dados["erro"] = "Plano Dia não encontrado.\nEntre em contato com o Suporte Técnico.";
	  } else {
      $servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, "", false);
      if ($servicos_ajustes->cancelarAjusteAgendado($_GET["i"], "<b>Olé Controle</b>: Cancelado pelo cliente ({$_GET["ip_cliente"]})")) {
        $dados["ok"] = true;
      } else {
        $dados["erro"] = "Erro durante processamento da solicitação.\nEntre em contato com o Suporte Técnico.";
      }
	  }
    $this->conteudo = serialize($dados);
	}
	
	#############
	### Geral ###
	#############
	
  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = intval(preg_replace("/[^0-9]/", "", $valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }
	
}

?>
