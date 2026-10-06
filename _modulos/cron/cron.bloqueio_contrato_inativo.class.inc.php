<?

class cron_bloqueio_contrato_inativo {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  ############################
  ### Ajusta Inadimplentes ###
  ############################

  function bloqueiaPontos() {
	if (!in_array(date("d-m-Y"), array("2019-04-18", "2019-04-19"))) {
		$assinaturas = $this->buscaAssinaturas();
		foreach ($assinaturas as $assinatura) {
		  if ($assinatura["intervalo"] >= 2 && !$this->buscaRetornoContratoInativo($assinatura["cli_id"])) {
			$this->geraRetornoContratoInativo($assinatura["cli_id"]);
		  }
		  if (!$assinatura["bloqueio_ativo"]) {
			  $maximo_dias = in_array($assinatura["cidade_id"], array(26)) ?  10 : 5;
			  if ($assinatura["intervalo"] >= $maximo_dias) {
				$this->geraBloqueio($assinatura);
			  }
		  }
		}
		// Mudança de Endereço
		$contratos = $this->buscaContratosInativosME();
		if ($contratos) {
		  $contratos_me = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
		  foreach ($contratos as $contrato) {
			$contratos_me->insereBloqueioContratoInativo($contrato["pai"]);
		  }
		}
	}
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaContratosInativosME() {
  	$query = "
  		SELECT
  		  tab_contratos.cor_id AS id,
  		  tab_contratos.cor_pai AS pai
  		FROM
  		  tab_contratos
  		  INNER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
				LEFT OUTER JOIN tab_servicos_ajustes ON (tab_contratos.cli_id = tab_servicos_ajustes.cli_id AND tab_servicos_ajustes.sac_id = 3 AND tab_servicos_ajustes.sas_id IN (5, 9))
  		WHERE
  		  tab_contratos.crt_id = 14 AND
  		  tab_contratos.crs_id BETWEEN 11 AND 19 AND
  		  tab_visitas.vis_data <= '".date("Y-m-d", strtotime("10 days ago"))."' AND
  		  tab_visitas.via_id = 2 AND
  		  tab_servicos_ajustes.saj_id IS NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $contratos[] = $dados;
    }
    return $contratos;
  }
  
  function buscaAssinaturas() {
  	// Pontos
  	$query = "
			SELECT
			  tab_assinaturas.ass_id AS ass_id,
			  tab_assinaturas.cli_id AS cli_id,
			  ROUND((UNIX_TIMESTAMP(CURDATE()) - UNIX_TIMESTAMP(MIN(tab_clientes_pontos_comentarios.cpc_datahora))) / 86400) AS intervalo,
			  COUNT(tab_servicos_ajustes.saj_id) AS bloqueio_ativo,
			  (SELECT cid_id FROM tab_ceps WHERE tab_ceps.cep_id = tab_clientes.cep_id) AS cidade_id
			FROM
			  tab_clientes_pontos_comentarios
			  INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos_comentarios.clp_id = tab_clientes_pontos.clp_id)
			  INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
			  INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
			  LEFT OUTER JOIN rel_saj_ass_contem ON (rel_saj_ass_contem.ass_id = tab_clientes_pontos.ass_id)
			  LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.saj_id = rel_saj_ass_contem.saj_id AND tab_servicos_ajustes.sac_id = 3 AND tab_servicos_ajustes.sas_id IN (5, 9))
			WHERE
			  tab_assinaturas.ast_id = 1 AND
			  (tab_clientes.cid_id IS NULL OR cid_id != 26) AND
			  tab_clientes_pontos.cps_id = 1 AND
			  cpc_conteudo LIKE '<_>Ação</_>:%Adicionado%'
			GROUP BY
			  ass_id
			HAVING
			  TO_DAYS(CURDATE()) - TO_DAYS(MIN(tab_clientes_pontos_comentarios.cpc_datahora)) >= 5";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados;
    }
  	// Autenticacoes
  	$query = "
			SELECT
			  tab_assinaturas.ass_id AS ass_id,
			  tab_assinaturas.cli_id AS cli_id,
			  ROUND((UNIX_TIMESTAMP(CURDATE()) - UNIX_TIMESTAMP(MIN(tab_clientes_autenticacoes_comentarios.cac_datahora))) / 86400) AS intervalo,
			  COUNT(tab_servicos_ajustes.saj_id) AS bloqueio_ativo
			FROM
			  tab_clientes_autenticacoes_comentarios
			  INNER JOIN tab_clientes_autenticacoes ON (tab_clientes_autenticacoes_comentarios.cau_id = tab_clientes_autenticacoes.cau_id)
			  INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
			  INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
			  LEFT OUTER JOIN rel_saj_ass_contem ON (tab_assinaturas.ass_id = rel_saj_ass_contem.ass_id)
			  LEFT OUTER JOIN tab_servicos_ajustes ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id AND tab_servicos_ajustes.sac_id = 3 AND tab_servicos_ajustes.sas_id IN (5, 9))
			WHERE
			  tab_assinaturas.ast_id = 1 AND
			  (tab_clientes.cid_id IS NULL OR cid_id != 26) AND
			  tab_clientes_autenticacoes.cas_id = 1 AND
			  tab_clientes_autenticacoes_comentarios.cac_conteudo LIKE '<_>Ação</_>:%Adicionado%'
			GROUP BY
			  ass_id
			HAVING
			  TO_DAYS(CURDATE()) - TO_DAYS(MIN(tab_clientes_autenticacoes_comentarios.cac_datahora)) >= 5";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados;
    }
    // Retorna Dados
    return $lista;
  }

  function buscaRetornoContratoInativo($cliente_id) {
    $query = "
      SELECT
        clr_id AS id
      FROM
        tab_clientes_retornos
      WHERE
        cli_id = $cliente_id AND
        clr_comentario = 'Contrato inativo com ponto de acesso instalado' AND
        ces_id = 1";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultadoArray($query)) {
      return true;
    }
  }

  function buscaPontos($assinatura_id) {
    $query = "
			SELECT
			  clp_id AS id
			FROM
			  tab_clientes_pontos
			WHERE
			  ass_id = $assinatura_id AND
			  cps_id <> 3";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados["id"];
    }
    return $lista;
  }

  function buscaAutenticacoes($assinatura_id) {
    $query = "
			SELECT
			  cau_id AS id
			FROM
			  tab_clientes_autenticacoes
			WHERE
			  ass_id = $assinatura_id AND
			  cas_id <> 3";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados["id"];
    }
    return $lista;
  }

  function buscaEmails($assinatura_id) {
    $query = "
			SELECT
			  cle_id AS id
			FROM
			  tab_clientes_emails
			WHERE
			  ass_id = $assinatura_id AND
			  ces_id <> 3";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[] = $dados["id"];
    }
    return $emails;
  }

  ####################
  ### Gera Retorno ###
  ####################

  function geraRetornoContratoInativo($cliente_id) {
    $query = "
	    SELECT
	      tab_usuarios.usu_id AS id
	    FROM
	      tab_usuarios
	      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
	    WHERE
	      rel_usu_gru_fazparte.gru_id = 13 AND
	      tab_usuarios.uss_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $comentarios = "Contrato inativo com ponto de acesso instalado";
      $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ($cliente_id, {$dados["id"]}, NOW(), CURDATE(), '$comentarios', 2, 1)";
      $this->bancodedados->query($query);
    }
  }

  ###########################
  ### Funções de Bloqueio ###
  ###########################

  function geraBloqueio($assinatura) {
    $ajuste_id = $this->adicionaBloqueio($assinatura["cli_id"]);
    $this->adicionaBloqueioAssinatura($ajuste_id, $assinatura["ass_id"]);
    $autenticacoes = $this->buscaAutenticacoes($assinatura["ass_id"]);
    if ($autenticacoes) {
      foreach ($autenticacoes as $autenticacao_id) {
        $this->adicionaBloqueioAutenticacao($ajuste_id, $autenticacao_id);
      }
    }
    $pontos = $this->buscaPontos($assinatura["ass_id"]);
    if ($pontos) {
      foreach ($pontos as $ponto_id) {
        $this->adicionaBloqueioPonto($ajuste_id, $ponto_id);
      }
    }
    $emails = $this->buscaEmails($assinatura["ass_id"]);
    if ($emails) {
      foreach ($emails as $email_id) {
        $this->adicionaBloqueioEmail($ajuste_id, $email_id);
      }
    }
  }

  function adicionaBloqueio($cliente_id) {
    // Insere Ajuste
    $query = "
    	INSERT INTO tab_servicos_ajustes
    		(cli_id, sac_id, saj_inicio, sas_id)
    	VALUES
    		($cliente_id, 3, NOW(), 5)";
    $this->bancodedados->query($query);
    $id = $this->bancodedados->ultimoIdInserido();
    // Insere Regra
    $dados_categoria = $this->buscaDadosCategoria(3);
    $this->registraRegras($id, $dados_categoria["regras"]);
    // Retorna id do ajuste
    return $id;
  }

  function registraRegras($id, $regras) {
    // insere regras
    $query = "DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = $id";
    $this->bancodedados->query($query);
    // Insere Regras
    foreach ($regras as $regra) {
      $regra["servico"] = $regra["servico"] ? $regra["servico"] : "NULL";
      $regra["ajuste"] = $regra["ajuste_tipo"] == 1 ? $regra["ajuste_valor"] / 100 : $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
      $regra["dia_semana"] = $regra["dia_semana"] ? $regra["dia_semana"] : "NULL";
      $regra["hora_inicio"] = $regra["hora_inicio"] ? "'{$regra["hora_inicio"]}'" : "NULL";
      $regra["hora_termino"] = $regra["hora_termino"] ? "'{$regra["hora_termino"]}'" : "NULL";
      $query = "
        INSERT INTO
          tab_servicos_ajustes_regras (saj_id, sat_id, sar_ajuste, sar_dia_semana, sar_hora_inicio, sar_hora_termino)
        VALUES
          ($id, {$regra["servico"]}, {$regra["ajuste"]}, {$regra["dia_semana"]}, {$regra["hora_inicio"]}, {$regra["hora_termino"]})";
      $this->bancodedados->query($query);
    }
  }

  function buscaDadosCategoria($categoria_id) {
    $query = "
      SELECT
        sac_regras as regras,
        sac_editavel as editavel
      FROM
        tab_servicos_ajustes_categorias
      WHERE
        sac_id = $categoria_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["regras"] = unserialize($dados["regras"]);
      return $dados;
    }
  }

  function adicionaBloqueioAssinatura($ajuste_id, $assinatura_id) {
    $query = "
	 		INSERT INTO
	 			rel_saj_ass_contem (saj_id, ass_id)
	 		VALUES
	 			($ajuste_id, $assinatura_id)";
    $this->bancodedados->query($query);
  }

  function adicionaBloqueioPonto($ajuste_id, $ponto_id) {
    $query = "
	 		INSERT INTO
	 			rel_saj_clp_contem (saj_id, clp_id)
	 		VALUES
	 			($ajuste_id, $ponto_id)";
    $this->bancodedados->query($query);
  }

  function adicionaBloqueioAutenticacao($ajuste_id, $autenticacao_id) {
    $query = "
	 		INSERT INTO
	 			rel_saj_cau_contem (saj_id, cau_id)
	 		VALUES
	 			($ajuste_id, $autenticacao_id)";
    $this->bancodedados->query($query);
  }

  function adicionaBloqueioEmail($ajuste_id, $email_id) {
    $query = "
	 		INSERT INTO
	 			rel_saj_cle_contem (saj_id, cle_id)
	 		VALUES
	 			($ajuste_id, $email_id)";
    $this->bancodedados->query($query);
  }

}

?>
