<?

class cron_descontos {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  ##########################
  ### Atualiza Descontos ###
  ##########################

  function expiraDescontos() {
  	$descontos = $this->buscaDescontosAtivosExpirados();
    if ($descontos) {
      foreach ($descontos as $desconto) {
        $this->atualizaStatus($desconto, 7);
        $this->registraComentario($desconto, "<b>Ação</b>: Expirado");
      }
    }
  }

  ########################
  ### Adiciona Retorno ###
  ########################

  function adicionaRetorno() {
  	$descontos = $this->buscaDescontosAdicionarRetorno();
    if ($descontos) {
      foreach ($descontos as $desconto) {
  	    if (!$this->buscaRetornoDesconto($desconto["cliente_id"])) {
          $this->registraRetornoSupervisorComercial($desconto);
  	    }
      }
    }
  }

  #################################
  ### Adiciona Retorno Sindicos ###
  #################################

  function adicionaRetornoSindicos() {
  	$clientes = $this->buscaDescontosSindicosAExpirar();
    if ($clientes) {
      foreach ($clientes as $cliente_id) {
        //$this->registraRetornoSupervisorFinanceiro($cliente_id);
        $this->registraRetornoAuxiliarFinanceiro($cliente_id);
      }
    }
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaDescontosSindicosAExpirar() {
    $query = "
      SELECT DISTINCT
        cli_id AS id
      FROM
        tab_descontos_concessoes
        INNER JOIN tab_assinaturas ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
      WHERE
        dcs_id IN (5) AND
        des_id IN (29, 30, 67, 109) AND
        dec_data_termino = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
      UNION SELECT DISTINCT
        cli_id AS id
      FROM
        tab_ajustes_financeiros
      WHERE
        afs_id IN (5) AND
        afc_id IN (18) AND
        ajf_competencia_termino = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $descontos[] = $dados["id"];
    }
    return $descontos;
  }

  function buscaDescontosAtivosExpirados() {
    $query = "
			SELECT
			  dec_id AS id
			FROM
			  tab_descontos_concessoes
			WHERE
			 	dcs_id = 5 AND
				dec_data_termino < CURDATE()";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $descontos[] = $dados["id"];
    }
    return $descontos;
  }

  function buscaDescontosAdicionarRetorno() {
    $query = "
			SELECT
			  tab_descontos.des_nome AS nome,
			  tab_assinaturas.cli_id AS cliente_id
			FROM
			  tab_descontos
			  INNER JOIN tab_descontos_concessoes ON (tab_descontos.des_id = tab_descontos_concessoes.des_id)
			  INNER JOIN tab_assinaturas ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
			WHERE
			 	tab_descontos_concessoes.dcs_id = 5 AND
				tab_descontos_concessoes.dec_data_termino <= DATE_ADD(NOW(), INTERVAL ".DESCONTOS_ADICIONAR_RETORNO." DAY) AND
				tab_descontos.des_adicionar_retorno = 1
		  GROUP BY
		    cliente_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $descontos[] = $dados;
    }
    return $descontos;
  }

	function buscaRetornoDesconto($cliente_id) {
	  $query = "
      SELECT
        clr_id AS id
      FROM
        tab_clientes_retornos
      WHERE
        cli_id = $cliente_id AND
        clr_comentario = '%irá expirar em menos de%' AND
        ces_id = 1";
	  $query = $this->bancodedados->query($query);
	  if ($this->bancodedados->resultadoArray($query)) {
	  	return true;
	  }
	}

  ########################
  #### Atualiza Status ###
  ########################

  function atualizaStatus($id, $status) {
    $query = "UPDATE tab_descontos_concessoes SET dcs_id = $status WHERE dec_id = $id";
    $query = $this->bancodedados->query($query);
  }

  ########################
  ##### Comentários ######
  ########################

  function registraComentario($id, $comentario) {
    $query = "INSERT INTO tab_descontos_concessoes_comentarios (dcc_datahora, dec_id, dcc_conteudo) VALUES (NOW(), $id, '$comentario')";
    
    // Descomentar a linha abaixo para registrar o comentário na tabela tab_descontos_concessoes_comentarios
    $this->bancodedados->query($query);
  }

	#####################################################
	#### Registra Retorno Aos Supervisores Comerciais ###
	#####################################################

	function registraRetornoSupervisorComercial($desconto) {
	  $query = "
	    SELECT
	      tab_usuarios.usu_id AS id
	    FROM
	      tab_usuarios
	      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
	    WHERE
	      rel_usu_gru_fazparte.gru_id = 22 AND
	      tab_usuarios.uss_id = 1";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
  	  $comentario = "O desconto <b>{$dados["nome"]}</b> irá expirar em menos de ".DESCONTOS_ADICIONAR_RETORNO." dia(s)";
      $query_retorno = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ({$dados["cliente_id"]}, {$dados["id"]}, NOW(), CURDATE(), '$comentario', 2, 1)";
      $this->bancodedados->query($query_retorno);
	  }
	}

	#########################
	#### Registra Retorno ###
	#########################

	function registraRetornoSupervisorFinanceiro($cliente_id) {
	  $query = "
	    SELECT
	      tab_usuarios.usu_id AS id
	    FROM
	      tab_usuarios
	      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
	    WHERE
	      rel_usu_gru_fazparte.gru_id = 8 AND
	      tab_usuarios.uss_id = 1";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $query_retorno = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ($cliente_id, {$dados["id"]}, NOW(), CURDATE(), 'O benefício de síndico irá expirar em 1 mês.', 2, 1)";
      $this->bancodedados->query($query_retorno);
	  }
	}

	function registraRetornoAuxiliarFinanceiro($cliente_id) {
	  $query = "
	    SELECT DISTINCT
	      tab_usuarios.usu_id AS id
	    FROM
	      tab_usuarios
	      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
	      INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
	    WHERE
	      rel_usu_gru_fazparte.gru_id = 9 AND
	      rel_usu_orf_ors_exerce.ors_id = 3 AND
	      tab_usuarios.uss_id = 1";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $query_retorno = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ($cliente_id, {$dados["id"]}, NOW(), CURDATE(), 'O benefício de síndico irá expirar em 1 mês.', 2, 1)";
      $this->bancodedados->query($query_retorno);
	  }
	}

}

?>
