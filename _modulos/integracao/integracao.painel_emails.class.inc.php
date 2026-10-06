<?

class integracao_painel_emails {

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
    $retorno["assinaturas"] = $this->buscaAssinaturas($_GET["usuario"]);
	  $retorno["emails"] = $this->buscaEmails($_GET["i"] ? $_GET["i"] : $retorno["assinaturas"][0]["id"], $_GET["usuario"]);
    $this->conteudo = serialize($retorno);
	}
	
	function buscaEmails($assinatura_id = null, $cliente_id = null, $email_id = null) {
	  $filtro[] = "tab_assinaturas.ast_id IN (2, 5)";
	  $filtro[] = "tab_clientes_emails.ces_id IN (1, 2)";
	  if ($assinatura_id) {
	   $filtro[] = "tab_assinaturas.ass_id = $assinatura_id";
	  }
	  if ($cliente_id) {
	   $filtro[] = "tab_assinaturas.cli_id = $cliente_id";
	  }
	  if ($email_id) {
	   $filtro[] = "tab_clientes_emails.cle_id = $email_id";
	  }
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id,
        tab_clientes_emails.cle_usuario AS usuario,
        tab_dominios.dom_dominio AS dominio,
        tab_clientes_emails.cle_descricao AS descricao,
        tab_clientes_emails.cle_capacidade AS capacidade,
        tab_clientes_emails.cle_publicado AS publicado,
        tab_clientes_emails.cle_bloqueado AS bloqueado,
        tab_clientes_emails.ass_id AS assinatura_id,
        tab_clientes_emails.ces_id AS status_id
      FROM
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        ".implode(" AND\n        ", $filtro)."
      ORDER BY
        tab_clientes_emails.cle_usuario";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["email"] = "{$dados["usuario"]}@{$dados["dominio"]}";
      if ($dados["bloqueado"]) {
				$dados["status_nome"] = "Bloqueado";
      } elseif ($dados["status_id"] == 1) {
        $dados["status_nome"] = "Ativo";
      } else {
        $dados["status_nome"] = "Inativo";
      }
      if ($email_id) {
        return $dados;
      } else {
        $emails[] = $dados;
      }
    }
    return $emails;
	}
	
	function buscaAssinaturas($cliente_id) {
    $query = "
			SELECT
        tab_assinaturas.ass_id AS id,
        tab_planos.pla_nome AS nome,
        tab_assinaturas.ass_parametros AS parametros,
        COUNT(tab_clientes_emails.cle_id) AS emails_existentes,
        SUM(tab_clientes_emails.cle_capacidade) AS emails_capacidade
			FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id AND tab_clientes_emails.ces_id IN (1, 2))
	  	WHERE
        tab_assinaturas.ast_id IN (2, 5) AND
        tab_assinaturas.cli_id = $cliente_id
      GROUP BY
        tab_assinaturas.ass_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      if ($dados["parametros"]["emails_quantidade"] > 0) {
        $assinatura["id"] = $dados["id"];
        $assinatura["nome"] = $dados["nome"];
        $assinatura["emails_existentes"] = $dados["emails_existentes"];
        $assinatura["capacidade_existente"] = $dados["emails_capacidade"];
        $assinatura["emails_quantidade"] = $dados["parametros"]["emails_quantidade"];
        $assinatura["emails_capacidade"] = $dados["parametros"]["emails_capacidade"] * $dados["parametros"]["emails_quantidade"];
        $assinatura["quantidade_disponivel"] = $dados["parametros"]["emails_quantidade"] - $dados["emails_existentes"];
        $assinatura["capacidade_disponivel"] = $assinatura["emails_capacidade"] - $assinatura["capacidade_existente"];
        $assinaturas[] = $assinatura;
      }
    }
    return $assinaturas;
	}

	#################
	### Adicionar ###
	#################
	
	function adicionar() {
	  // Trata dados
    $_POST["capacidade"] = $_POST["capacidade"] * pow(1024, $_POST["unidade"]);
    $_POST["usuario"] = strtolower($_POST["usuario"]);
    // Busca Dados
	  $dados_assinatura = $this->buscaDadosAssinatura($_GET["i"]);
	  // Verifica Erros
	  if ($dados_assinatura && $dados_assinatura["cliente_id"] == $_GET["usuario"]) {
	    if ($dados_assinatura["quantidade_disponivel"] <= 0) {
  	   $conteudo["erro"] = "A quantidade máxima de e-mails contratada ({$dados_assinatura["emails_quantidade"]}) já foi atingida.";
	    } elseif ($dados_assinatura["capacidade_disponivel"] < $_POST["capacidade"]) {
  	   $conteudo["erro"] = "A capacidade do e-mail solicitada é superior à capacidade disponível (".$this->formataCapacidade($dados_assinatura["capacidade_disponivel"]).").";
	    } elseif (!in_array($_POST["dominio"], $dados_assinatura["dominios"])) {
  	   $conteudo["erro"] = "Domínio inválido ou inexistente.";
	    } elseif ($this->buscaUsuarioEmail($_POST["usuario"], $_POST["dominio"])) {
  	   $conteudo["erro"] = "O e-mail solicitado não está disponível.";
	    } else {
	      $tipo_id = 2;
	      $comentario = "<b>Olé Controle:</b> Adicionado pelo cliente ({$_GET["ip_cliente"]})";
	      $dominio_id = $this->buscaDominioId($_POST["dominio"]);
        $emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, null, false);
        $email_id = $emails->insereEmail($dados_assinatura["cliente_id"], $dados_assinatura["id"], $_POST["usuario"], $dominio_id, $_POST["senha"], $_POST["descricao"], $_POST["capacidade"], $tipo_id, $comentario);
      	if ($email_id) {
      	  $emails->publicaClientesEmails(array($email_id));
  	      $conteudo["dados"] = $this->buscaEmails($dados_assinatura["id"], $dados_assinatura["cliente_id"], $email_id);
  	      $conteudo["assinatura"] = $dados_assinatura;
          $conteudo["assinatura"]["emails_existentes"] += 1;
          $conteudo["assinatura"]["quantidade_disponivel"] -= 1;
          $conteudo["assinatura"]["capacidade_existente"] += $conteudo["dados"]["capacidade"];
      	}
	    }
	  } else {
  	  $conteudo["erro"] = "Assinatura inválida ou inexistente.";
	  }
    $this->conteudo = serialize($conteudo);
	}
	
	################
	### Detalhes ###
	################
	
	function detalhes() {
		$dados_email = $this->buscaDadosEmail($_GET["i"], $_GET["usuario"]);
	  if ($dados_email) {
	  	$dados["dados"] = $dados_email;
	  } else {
	  	$dados["erro"] = "E-mail inválido ou inexistente.";
	  }
	  $this->conteudo = serialize($dados);
	}
	
	function buscaDadosEmail($email_id, $cliente_id) {
		$query = "
		  SELECT
		    cle_id AS id,
		    cle_descricao AS descricao,
		    cle_capacidade AS capacidade,
		    cle_bloqueado AS bloqueado,
		    cle_principal AS principal,
		    ces_id AS status_id,
		    ass_id AS assinatura_id
		  FROM
		    tab_clientes_emails
		  WHERE
		    cle_id = $email_id AND
		    ces_id IN (1, 2) AND
		    ass_id IN (SELECT ass_id FROM tab_assinaturas WHERE cli_id = $cliente_id)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["unidade"] = !$dados["capacidade"] ? 2 : floor(log($dados["capacidade"], 1024));
      $dados["capacidade"] = intval(@round($dados["capacidade"] / pow(1024, $dados["unidade"]), 2), 10);
      if ($dados["bloqueado"]) {
        $dados["status_nome"] = "Bloqueado";
      } elseif ($dados["status_id"] == 1) {
        $dados["status_nome"] = "Ativo";
      } else {
        $dados["status_nome"] = "Inativo";
      }
      return $dados;
    }
	}
		
	##############
	### Editar ###
	##############
	
	function editar() {
		$dados = $this->buscaDadosEmail($_GET["i"], $_GET["usuario"]);
	  if ($dados) {
	  	$dados_assinatura = $this->buscaDadosAssinatura($dados["assinatura_id"]);
	  	if (($dados_assinatura["capacidade_disponivel"] + $dados["capacidade"] * pow(1024, $dados["unidade"])) < $_POST["capacidade"] * pow(1024, $_POST["unidade"])) {
  	  	$dados["erro"] = "A capacidade do e-mail solicitada é superior à capacidade disponível (".$this->formataCapacidade($dados_assinatura["capacidade_disponivel"]).").";
	    } else {
	      $emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, null, false);
		  	// Formata Dados
		  	$_POST["publicado"] = $_POST["senha"] || $_POST["status"] != $dados["status_id"] || ($_POST["capacidade"] != $dados["capacidade"] && $_POST["unidade"] != $dados["unidade"]) ? 0 : 1;
	      $_POST["descricao"] = "'".trim($_POST["descricao"])."'";
	      $_POST["capacidade"] = $_POST["capacidade"] * pow(1024, $_POST["unidade"]);
	      $_POST["senha"] = $_POST["senha"] ? "'{$_POST["senha"]}'" : "cle_senha";
	      $_POST["status"] = $_POST["status"] == 1 ? "1" : "2";
	      // Atualiza Registro
	      $query = "UPDATE tab_clientes_emails SET cle_senha = {$_POST["senha"]}, cle_descricao = {$_POST["descricao"]}, cle_capacidade = {$_POST["capacidade"]}, ces_id = {$_POST["status"]}, cle_publicado = {$_POST["publicado"]} WHERE cle_id = {$_GET["i"]}";
	      $this->bancodedados->query($query);
	      // Solicita publicação e-mail
	      if (!$_POST["publicado"]) {
	        $emails->solicitaClienteEmailPublicacao($_GET["i"]);
	      }
	      // Registra Comentário
	      $emails->registraComentario($_GET["i"], "<b>Olé Controle:</b> Editado pelo cliente ({$_GET["ip_cliente"]})");
	      // Retorna Dados E-mail
	      $dados["dados"] = $this->buscaDadosEmail($_GET["i"], $_GET["usuario"]);
	      $dados["assinatura"] = $this->buscaDadosAssinatura($dados["dados"]["assinatura_id"]);
	    }
	  } else {
      $dados["erro"] = "E-mail inválido ou inexistente.";
	  }
    $this->conteudo = serialize($dados);
	}
	
	################
	### Cancelar ###
	################
	
	function cancelar() {
		$dados = $this->buscaDadosEmail($_GET["i"], $_GET["usuario"]);
    if ($dados) {
    	if ($dados["principal"]) {
        $dados["erro"] = "Este e-mail é usado para autenticação do Olé Controle e não pode ser removido.";
    	} else {
	      $emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, null, false);
	    	// Cancela E-mail
	    	$query = "UPDATE tab_clientes_emails SET ces_id = 3, cle_cancelamento = NOW(), cle_publicado = 0, cle_bloqueado = 0 WHERE cle_id = {$_GET["i"]}";
	      $this->bancodedados->query($query);
	      // Publica Alteração
	      $emails->solicitaClienteEmailPublicacao($_GET["i"]);
	      // Registra Comentário
	      $emails->registraComentario($_GET["i"], "<b>Olé Controle:</b> Cancelado pelo cliente ({$_GET["ip_cliente"]})");
	      // Retorna OK
	      $dados["assinatura"] = $this->buscaDadosAssinatura($dados["assinatura_id"]);
	      $dados["ok"] = true;
    	}
    } else {
      $dados["erro"] = "E-mail inválido ou inexistente.";
    }
    $this->conteudo = serialize($dados);
  }

  #############
  ### Geral ###
  #############

  function buscaDadosAssinatura($assinatura_id) {
    $query = "
			SELECT
        tab_assinaturas.ass_id AS id,
        tab_assinaturas.cli_id AS cliente_id,
        tab_assinaturas.ass_parametros AS parametros,
        COUNT(tab_clientes_emails.cle_id) AS emails_existentes,
        SUM(tab_clientes_emails.cle_capacidade) AS emails_capacidade,
        (SELECT pls_id FROM tab_planos WHERE tab_planos.pla_id = tab_assinaturas.pla_id) AS servico_id
			FROM
        tab_assinaturas
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id AND tab_clientes_emails.ces_id IN (1, 2))
	  	WHERE
        tab_assinaturas.ass_id = $assinatura_id AND
        tab_assinaturas.ast_id IN (2, 5)
      GROUP BY
        tab_assinaturas.ass_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      if ($dados["parametros"]["emails_quantidade"] > 0) {
        $assinatura["id"] = $dados["id"];
        $assinatura["cliente_id"] = $dados["cliente_id"];
        $assinatura["emails_existentes"] = $dados["emails_existentes"];
        $assinatura["capacidade_existente"] = $dados["emails_capacidade"];
        $assinatura["emails_quantidade"] = $dados["parametros"]["emails_quantidade"];
        $assinatura["emails_capacidade"] = $dados["parametros"]["emails_capacidade"] * $dados["parametros"]["emails_quantidade"];
        $assinatura["quantidade_disponivel"] = $dados["parametros"]["emails_quantidade"] - $dados["emails_existentes"];
        $assinatura["capacidade_disponivel"] = $assinatura["emails_capacidade"] - $assinatura["capacidade_existente"];
        $assinatura["dominios"] = $this->buscaDominios($dados["id"], $dados["servico_id"]);
        return $assinatura;
      }
    }
  }
  
  function buscaDominios($assinatura_id, $servico_id) {
    if ($servico_id == 11) {
      $filtro[] = "tab_dominios.ass_id = $assinatura_id";
      $filtro[] = "tab_dominios.dom_dominio <> '-'";
      $filtro[] = "tab_dominios.dom_dominio <> ''";
    } else {
      $filtro[] = "tab_dominios.dom_global = 1";
    }
    $filtro[] = "tab_dominios.dos_id IN (3, 4)";
    $query = "
			SELECT
        tab_dominios.dom_id AS id,
        tab_dominios.dom_dominio AS nome
			FROM
        tab_dominios
	  	WHERE
        ".implode(" AND \n        ", $filtro)."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dominios[$dados["id"]] = $dados["nome"];
    }
    return $dominios;
  }
  
  function buscaDominioId($dominio) {
    $query = "
			SELECT
        tab_dominios.dom_id AS id
			FROM
        tab_dominios
	  	WHERE
        tab_dominios.dos_id IN (3, 4) AND
        tab_dominios.dom_dominio = '$dominio'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }
  
  function buscaUsuarioEmail($usuario, $dominio) {
    $query = "
      SELECT
        COUNT(cle_id) AS quantidade
      FROM
        tab_clientes_emails
      WHERE
        cle_usuario = '$usuario' AND
        ces_id != 4 AND
        dom_id IN (SELECT dom_id FROM tab_dominios WHERE dom_dominio = '$dominio')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["quantidade"];
    }
  }
  
  function formataCapacidade($capacidade) {
    if ($capacidade <= 0) return "0 MB";
    $unidades["1"] = "KB";
    $unidades["2"] = "MB";
    $unidades["3"] = "GB";
    $unidades["4"] = "TB";
    $unidade_capacidade = !$capacidade ? 2 : floor(log($capacidade, 1024));
    return @round($capacidade / pow(1024, $unidade_capacidade), 2)." ".$unidades[$unidade_capacidade];
  }

}

?>
