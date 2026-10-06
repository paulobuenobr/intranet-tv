<?

require_once("_global/modulo.class.inc.php");

class clientes_emails extends modulo {

	private $controle;
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		$this->buscaDadosModulo(__CLASS__);
    if ($executar) {
      if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
  		$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
  		$this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
	}

  #############
  ### Geral ###
  #############

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
    	$array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[$chave."_".key($array)] = key($array);
        }
        unset($chave);
        next($array);
      }
      ksort($array_ordenacao);
      while (is_int($array_id = current($array_ordenacao))) {
        $array_final[] = $array[$array_id];
        next($array_ordenacao);
      }
      return $array_final;
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

  function limpaNome($nome) {
    $nome = strtolower($nome);
    $nome = str_replace(array("ç", "ñ"), array("c", "n"), $nome);
    $nome = preg_replace('/[áàâãäªÁÀÂÃÄ]/', "a", $nome);
    $nome = preg_replace('/[éèêëÉÈÊË]/', "e", $nome);
    $nome = preg_replace('/[íìîïÍÌÎÏ]/', "i", $nome);
    $nome = preg_replace('/[óòôõö°ºÓÒÔÕÖ]/', "o", $nome);
    $nome = preg_replace('/[úùûüÚÙÛÜ]/', "u", $nome);
    $nome = preg_replace('/[^a-z0-9()\[\] ]/', " ", $nome);
    $nome = preg_replace('/[ ]+/', " ", $nome);
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "SA", "ME");
    $nome = ucwords($nome);
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
    $nome = trim($nome);
    return $nome;
  }

  function fechar() {
    unset($this->conteudo);
    if ($this->dados_item["cliente_id"]) $this->conteudo  .= '<script type="text/javascript">if (!window.opener.closed && window.opener.buscaClientesEmailsCliente) window.opener.buscaClientesEmailsCliente('.$this->dados_item["cliente_id"].')</script>';
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  #########################
  ### Busca Dados Itens ###
  #########################

  function buscaDadosItem($id) {
    $this->dados_item = $this->buscaDadosClienteEmail($id);
  }

  function buscaDadosClienteEmail($cliente_email_id) {
  	if ($cliente_email_id) {
	    $query = "
	      SELECT
	        tab_clientes_emails.cle_id AS id,
	        tab_clientes_emails.cle_usuario AS usuario,
	        tab_clientes_emails.cle_senha AS senha,
	        tab_dominios.dom_id AS dominio_id,
	        tab_dominios.dom_dominio AS dominio_nome,
	        tab_dominios.dom_integracao AS dominio_integracao,
	        tab_clientes_emails.cle_descricao AS descricao,
	        tab_clientes_emails.cle_capacidade AS capacidade,
	        tab_clientes_emails.cle_publicado AS publicado,
	        tab_clientes_emails.cle_bloqueado AS bloqueado,
	        tab_clientes_emails.cle_principal AS principal,
	        tab_assinaturas.ass_id AS assinatura_id,
	        tab_assinaturas.ast_id AS assinatura_status_id,
	        CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_planos.pla_nome) AS assinatura_nome,
	        tab_clientes.cli_id AS cliente_id,
	        tab_clientes.cli_nome AS cliente_nome,
	        tab_clientes_emails_tipos.cet_id AS tipo_id,
	        tab_clientes_emails_tipos.cet_nome AS tipo_nome,
	        tab_clientes_emails_status.ces_id AS status_id,
	        tab_clientes_emails_status.ces_nome AS status_nome
	      FROM
	        tab_clientes_emails
	        INNER JOIN tab_clientes_emails_tipos ON (tab_clientes_emails.cet_id = tab_clientes_emails_tipos.cet_id)
	        INNER JOIN tab_clientes_emails_status ON (tab_clientes_emails.ces_id = tab_clientes_emails_status.ces_id)
	        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
	        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
	        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
	        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
	        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
	      WHERE
	        tab_clientes_emails.cle_id = $cliente_email_id";
	    $query = $this->bancodedados->query($query);
	    $dados = $this->bancodedados->resultadoArray($query);
	    if ($dados) {
	      $dados["nome"] = $dados["usuario"]."@".$dados["dominio_nome"];
	      $dados["comentario"] = $this->limpaNome("[{$dados["cliente_id"]}] {$dados["cliente_nome"]}" . ($dados["descricao"] ? " ({$dados["descricao"]})" : ""));
				if ($dados["status_id"] == 1) {
					if ($dados["bloqueado"]) $dados["status_nome"] .= " (Bloqueado)";
					if (!$dados["bloqueado"] || !$dados["publicado"]) $dados["status_nome"] .= ($dados["publicado"]) ? " (Publicado)" : " (Não Publicado)";
				}
				if ($dados["status_id"] == 1 && $dados["publicado"] && !$dados["bloqueado"]) {
					$dados["status_nome"] = "<span style=\"color:green\">{$dados["status_nome"]}</span>";
				} else {
					$dados["status_nome"] = "<span style=\"color:red\">{$dados["status_nome"]}</span>";
				}
				$dados["principal_nome"] = $dados["principal"] ? "Sim" : "Não";
				$dados["comentarios"] = $this->buscaComentarios($cliente_email_id);
				$dados["ajustes_programados"] = $this->buscaAjustesProgramados($cliente_email_id);
	    	return $dados;
	    }
  	}
  }

  function buscaDadosClienteEmailPorUsuarioPorDominioId($usuario, $dominio, $cliente = null) {
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id
      FROM
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
      WHERE
        tab_clientes_emails.cle_usuario = '$usuario' AND
        tab_clientes_emails.dom_id = '$dominio' AND
        tab_clientes_emails.ces_id IN (1, 2, 3) ";
    $query = $this->bancodedados->query($query);
    $id = $this->bancodedados->resultado($query, 0, "id");
    if ($id) {
      return $this->buscaDadosClienteEmail($id);
    }
  }

  function buscaDadosClienteEmailPorUsuarioPorDominioNome($usuario, $dominio) {
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id
      FROM
        tab_clientes_emails
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_clientes_emails.cle_usuario = '$usuario' AND
        tab_dominios.dom_dominio = '$dominio'";
    $query = $this->bancodedados->query($query);
    $id = $this->bancodedados->resultado($query, 0, "id");
    if ($id) {
    	return $this->buscaDadosClienteEmail($id);
    }
  }

  function buscaDadosCliente($id) {
    $query = "
      SELECT
        tab_clientes.cli_id AS id,
        tab_clientes.cli_nome AS nome,
        tab_condominios.con_nome AS condominio,
        tab_condominios_blocos.cob_nome AS bloco,
        SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
      WHERE
        tab_clientes.cli_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados_tratados["id"] = $dados["id"];
      $dados_tratados["nome"] = $dados["nome"];
      $dados_tratados["condominio"] = $dados["condominio"];
      $dados_tratados["bloco"] = $dados["bloco"];
      $dados_tratados["complemento"] = $dados["complemento"];
      if ($dados_tratados["condominio"]) $dados_tratados["endereco"] = "{$dados_tratados["condominio"]} {$dados_tratados["complemento"]}/{$dados_tratados["bloco"]}";
    }
    return $dados_tratados;
  }

  function buscaDadosAssinatura($id) {
    $query = "
      SELECT
        tab_assinaturas.ass_id AS id,
        CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_planos.pla_nome) AS nome,
        tab_assinaturas.ass_parametros AS parametros,
        tab_assinaturas_status.ast_id AS status_id,
        tab_assinaturas_status.ast_nome AS status_nome
      FROM
        tab_assinaturas
        INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_assinaturas.ass_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados_tratados["id"] = $dados["id"];
      $dados_tratados["nome"] = $dados["nome"];
      $dados_tratados["parametros"] = @unserialize($dados["parametros"]);
      $dados_tratados["status_id"] = $dados["status_id"];
      $dados_tratados["status_nome"] = $dados["status_nome"];
    }
    return $dados_tratados;
  }

  function buscaDadosDominio($dominio_id) {
    $query = "
      SELECT
        tab_dominios.dom_id AS id,
        tab_dominios.ass_id AS assinatura,
        tab_dominios.dom_dominio AS dominio,
        tab_dominios.dom_integracao AS integracao,
        tab_dominios.dos_id AS status_id
      FROM
        tab_dominios
      WHERE
        tab_dominios.dom_id = $dominio_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaClientesEmailsPorCliente($cliente_id) {
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id,
        CONCAT(tab_clientes_emails.cle_usuario, '@', tab_dominios.dom_dominio) AS nome
      FROM
        tab_clientes_emails INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id
      ORDER BY
        ces_id,
        dom_dominio,
        cle_usuario";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $clientes_emails[$dados["id"]] = $dados["nome"];
    }
    return $clientes_emails;
  }

  function buscaClientesEmailsNaoPublicados($limit = 0) {
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id,
        CONCAT(tab_clientes_emails.cle_usuario, '@', tab_dominios.dom_dominio) AS email
      FROM
        tab_clientes_emails
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_clientes_emails.cle_publicado = 0 AND
        tab_dominios.dom_integracao = 'ldap'
      ORDER BY
        RAND()".($limit ? "
      LIMIT
        0, $limit" : "");
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[$dados["id"]] = $dados["email"];
    }
    return $emails;
  }

  function buscaTipos() {
    $query = "
      SELECT
        cet_id AS id,
        cet_nome AS nome
      FROM
        tab_clientes_emails_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaStatus() {
    $query = "
      SELECT
        ces_id AS id,
        ces_nome AS nome
      FROM
        tab_clientes_emails_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaClienteAssinaturas($cliente_id) {
    $query = "
			SELECT
        tab_assinaturas.ass_id AS id,
        CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' (Prospect)', '')) AS nome,
        tab_assinaturas.ass_parametros AS parametros,
        COUNT(tab_clientes_emails.cle_id) AS emails_existentes,
        SUM(tab_clientes_emails.cle_capacidade) AS emails_capacidade
			FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id AND tab_clientes_emails.ces_id IN (1, 2))
	  	WHERE
        tab_assinaturas.ast_id IN (1, 2, 5) AND
        tab_assinaturas.cli_id = $cliente_id
      GROUP BY
        tab_assinaturas.ass_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      $emails_disponiveis = $dados["parametros"]["emails_quantidade"] - $dados["emails_existentes"];
      $emails_capacidade = ($dados["parametros"]["emails_capacidade"] * $dados["parametros"]["emails_quantidade"]) - $dados["emails_capacidade"];
      if ($emails_disponiveis > 0 && $emails_capacidade > 0) {
        $assinaturas[$dados["id"]] = "{$dados["nome"]} ({$emails_disponiveis} " . ($emails_disponiveis > 1 ? "disponíveis" : "disponível").")";
      }
    }
    return $assinaturas;
  }

  function buscaDominiosPorAssinatura($assinatura_id = '%', $busca = false) {
  	if ($assinatura_id && $assinatura_id != '%') {
  		$servico_id = $this->buscaServicoAssinatura($assinatura_id);
  	}
    $query = "
			SELECT DISTINCT
        tab_dominios.dom_id AS id,
        tab_dominios.dom_dominio AS nome
			FROM
        tab_dominios
	  	WHERE".($busca ? "" : "
        (tab_dominios.dos_id IN (3, 4)) AND")."
        (tab_dominios.dom_dominio <> '-') AND
        (tab_dominios.dom_dominio <> '') AND
	  	  (tab_dominios.ass_id LIKE '$assinatura_id'".($servico_id != 11 ? " OR tab_dominios.dom_global = 1)" : ")")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dominios[$dados["id"]] = $dados["nome"];
    }
    return $dominios;
  }
  
  function buscaServicoAssinatura($assinatura_id) {
  	$query = "
			SELECT
			  tab_planos.pls_id AS id
			FROM
			  tab_assinaturas
  		  INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
  		WHERE
  			tab_assinaturas.ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["id"];
    }
  }
  
  function buscaAssinaturasComAjuste($assinatura) {
  	$query = "
			SELECT
			  rel_saj_ass_contem.saj_id AS id
			FROM
			  rel_saj_ass_contem
  		  INNER JOIN tab_servicos_ajustes ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id)
  		WHERE
  			rel_saj_ass_contem.ass_id = $assinatura";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$lista[] = $dados["id"];
    }
  	return $lista;
  }
  
  function buscaCapacidadeMaxima($assinatura_id) {
    $query = "
			SELECT
        tab_assinaturas.ass_id AS id,
        tab_assinaturas.ass_parametros AS parametros,
        SUM(IF(tab_clientes_emails.ces_id IN (1, 2), tab_clientes_emails.cle_capacidade, 0)) AS emails_capacidade
			FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
	  	WHERE
        tab_assinaturas.ast_id IN (1, 2, 5) AND
        tab_assinaturas.ass_id = $assinatura_id
      GROUP BY
        tab_assinaturas.ass_id
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      $capacidade_maxima += ($dados["parametros"]["emails_capacidade"] * $dados["parametros"]["emails_quantidade"]) - $dados["emails_capacidade"];
    }
    return $capacidade_maxima > 0 ? $capacidade_maxima : 0;
  }
  
  function buscaCapacidadePadrao($assinatura_id) {
    $query = "
			SELECT
        ass_id AS id,
        ass_parametros AS parametros
			FROM
        tab_assinaturas
	  	WHERE
        ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      return $dados["parametros"]["emails_capacidade"];
    } else {
      return 0;
    }
  }
  
  function buscaCapacidades($capacidade_maxima) {
   	$capacidade_maxima = $capacidade_maxima > 1073741824 ? 1073741824 :  $capacidade_maxima;
   for ($i = 10; $i <= $capacidade_maxima / (1024 * 1024); $i += 10) {
      $capacidades[$i * 1024 * 1024] = $this->formataCapacidade($i * 1024 * 1024);
      $maior_capacidade = $i * 1024 * 1024;
    }
    if ($maior_capacidade < $capacidade_maxima) {
      $capacidades[$maior_capacidade] = $this->formataCapacidade($maior_capacidade);
    }
    return $capacidades;
  }
  
  function insereContatoCliente($cliente_id, $usuario, $dominio, $tipo_id) {
  	$query = "
  		SELECT
  		  ccm_id AS id,
  			cca_id AS tipo_id
			FROM
				tab_clientes_contatos_emails
		  WHERE
		    ccm_usuario = '$usuario' AND
		    ccm_dominio = '$dominio' AND
		    cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["tipo_id"] != $tipo_id) {
        $query = "UPDATE tab_clientes_contatos_emails SET cca_id = $tipo_id WHERE ccm_id = {$dados["id"]}";
	      $this->bancodedados->query($query);
      }
    } else {
	  	$query = "
	  		INSERT INTO
	  			tab_clientes_contatos_emails (ccm_usuario, ccm_dominio, cli_id, cca_id) 
				VALUES ('$usuario', '$dominio', $cliente_id, $tipo_id)";
	    $this->bancodedados->query($query);
    }
  }
  
  function buscaContatosClienteQuantidade($cliente_id) {
  	$query = "
  	  SELECT
  	  	COUNT(*) AS quantidade
  	  FROM
  	    tab_clientes_contatos_emails
  	  WHERE
  	    cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["quantidade"];
    }
  }
  
  function removeContatoCliente($usuario, $dominio) {
  	$query = "
  		DELETE FROM 
  			tab_clientes_contatos_emails 
  		WHERE 
  			ccm_usuario = '$usuario' AND
  			ccm_dominio = '$dominio'";
    $this->bancodedados->query($query);
  }

  function atualizaContatoEmail($cliente_id, $usuario, $dominio, $tipo_id) {
    $query = "
      UPDATE 
        tab_clientes_contatos_emails
      SET
        cca_id = $tipo_id
      WHERE
        cli_id = $cliente_id AND
        ccm_usuario = '$usuario' AND
        ccm_dominio = '$dominio'";
    $query = $this->bancodedados->query($query);
  }

  ###########################
  ### Ajustes Programados ###
  ###########################

  function buscaAjustesProgramados($id) {
  	$ajustes_programados = array();
    $query = "
      SELECT DISTINCT
        tab_servicos_ajustes.saj_id AS id,
        DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tab_servicos_ajustes_categorias.sac_nome AS categoria,
        tab_servicos_ajustes_status.sas_id AS status_id,
        tab_servicos_ajustes_status.sas_nome AS status,
        DATE_FORMAT(tab_clientes_emails_ajustes.cea_inicio, '%d/%m/%Y (%H:%i)') AS aplicado_deste
      FROM
        tab_servicos_ajustes
        INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
        INNER JOIN rel_saj_cle_contem ON (rel_saj_cle_contem.saj_id = tab_servicos_ajustes.saj_id)
        INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
        LEFT OUTER JOIN tab_clientes_emails_ajustes ON (tab_clientes_emails_ajustes.saj_id = tab_servicos_ajustes.saj_id AND tab_clientes_emails_ajustes.cea_termino IS NULL)
      WHERE
        rel_saj_cle_contem.cle_id = $id AND
        tab_servicos_ajustes_status.sas_id IN (5, 9)
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["codigo"] = "<div align=\"center\">".sprintf("%06s", $dados["id"])."</div>";
      $dados["status"] .= $dados["aplicado_deste"] ? " / Aplicado desde {$dados["aplicado_deste"]}" : " / Não Aplicado";
      $dados["categoria"] = "<div align=\"left\">{$dados["categoria"]}</div>";
      if (!$dados["inicio"]) $dados["inicio"] = "-";
      if (!$dados["termino"]) $dados["termino"] = "-";
      $ajustes_programados[] = $dados;
    }
    return $ajustes_programados;
  }

  function geraAjustesProgramadosListagem($registros) {
    $cabecalho = array ("codigo" => "Código", "categoria" => "Categoria", "status" => "Status");
    $larguras = array("40", "", "220");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $registros, 0, false, $larguras);
    return $listagem->conteudo;
  }

  ###################
  ### Comentários ###
  ###################

  function buscaComentarios($id) {
    $query = "
      SELECT
			  IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
        DATE_FORMAT(tab_clientes_emails_comentarios.cec_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_clientes_emails_comentarios.cec_conteudo AS conteudo
      FROM
        tab_clientes_emails_comentarios
        LEFT OUTER JOIN tab_usuarios ON (tab_clientes_emails_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        cle_id = '$id'
      ORDER BY
        tab_clientes_emails_comentarios.cec_datahora ASC,
        tab_clientes_emails_comentarios.cec_id ASC";
    $query = $this->bancodedados->query($query);
    while(($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["detalhamento"][] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data_hora"]}</b>";
      if (substr($dados["conteudo"], 0, 3) != "<b>") {
        $dados["detalhamento"][] = "<b>Conteúdo:</b> {$dados["conteudo"]}";
      } else {
        $dados["detalhamento"][] = $dados["conteudo"];
      }
      $dados["detalhamento"] = implode("<br>", $dados["detalhamento"]);
      $comentarios[] = $dados;
    }
    return $comentarios;
  }

  function registraComentario($id, $conteudo, $responsavel = null) {
    $conteudo = addslashes(trim($conteudo));
    $responsavel = $responsavel ? $responsavel : "NULL";
    $query = "INSERT INTO tab_clientes_emails_comentarios (cec_datahora, cle_id, cec_conteudo, usu_id) VALUES (NOW(), $id, '$conteudo', $responsavel)";
    $this->bancodedados->query($query);
  }

  function geraComentariosListagem ($comentarios) {
    $cabecalho = array("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
  	$dados_cliente = $this->buscaDadosCliente($_GET["i"]);
    if ($dados_cliente) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.adicionar.inc.php");
        $this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
        $this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
        $this->conteudo = str_replace("[assinatura]", $this->geraSelect($assinaturas = $this->buscaClienteAssinaturas($dados_cliente["id"]), count($assinaturas) == 1 ? key($assinaturas) : null, true, "assinatura", "100%", "atualizaClienteEmailAssinatura(this)"), $this->conteudo);
        $this->conteudo = str_replace("[usuario]", $this->geraInput("text", "usuario", "100%", null, count($assinaturas) == 1, "32", null, null, "verificaClienteEmailUsuario(this)"), $this->conteudo);
        $this->conteudo = str_replace("[dominio]", $this->geraSelect($dominios = count($assinaturas) == 1 ? $this->buscaDominiosPorAssinatura(key($assinaturas)) : null, count($dominios) == 1 ? key($dominios) : null, true, "dominio", "100%", "verificaClienteEmailUsuario(this)"), $this->conteudo);
        $this->conteudo = str_replace("[capacidade]", $this->geraSelect($capacidades = count($assinaturas) == 1 ? $this->buscaCapacidades($this->buscaCapacidadeMaxima(key($assinaturas))) : null, count($capacidades) == 1 ? key($capacidades) : $this->buscaCapacidadePadrao(key($assinaturas)), true, "capacidade", "200px", null, count($assinaturas) == 1), $this->conteudo);
        $this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), 1, true, "tipo", "200px"), $this->conteudo);
        $this->conteudo = str_replace("[ole_controle]", $this->geraSelect(array("0" => "Não", "1" => "Sim"), null, true, "ole_controle", "200px"), $this->conteudo);
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
      } else {
      	$id = $this->insereEmail($dados_cliente["id"], $_POST["assinatura"], $_POST["usuario"], $_POST["dominio"], $_POST["senha"], $_POST["descricao"], $_POST["capacidade"], $_POST["tipo"], $_POST["comentario"], true);
		    // Publica
		    if (($erro = $this->publicaClientesEmails(array($id))) <= 0) {
		      $mensagem = urlencode("Publicação não concluída (ERRO: ".$this->buscaErro($erro).")!");
		    }
		    if ($_POST["ole_controle"]) {
		    	$this->ativarOleControle($dados_cliente["id"], $id);
		    }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $id, $this->acao, $id);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}&mensagem={$mensagem}");
      }
    } else {
      $this->fechar();
    }
  }
  
  function insereEmail($cliente_id, $assinatura_id, $usuario, $dominio_id, $senha, $descricao, $capacidade, $tipo_id, $comentario = "", $atualizar_contatos = false) {
    // Busca Dados Assinatura
    $assinatura = $this->buscaDadosAssinatura($assinatura_id);
    // Trata Dados
    $usuario = trim(strtolower($usuario));
    $senha = trim($senha);
    $descricao = trim($descricao);
    // Insere Registro
    $query = "
      INSERT INTO 
        tab_clientes_emails (ass_id, cle_usuario, cle_senha, dom_id, cle_descricao, cle_capacidade, cet_id, ces_id)
      VALUES 
        ($assinatura_id, '$usuario', '$senha', $dominio_id, '$descricao', '$capacidade', $tipo_id, 1)";
    $this->bancodedados->query($query);
    $id = $this->bancodedados->ultimoIdInserido();
    // Verifica se assinatura tem ajuste e adiciona ajuste ao ponto
    $ajuste = $this->buscaAssinaturasComAjuste($assinatura_id);
    if ($ajuste) {
      foreach ($ajuste as $id_ajuste) {
		  	$query = "INSERT INTO rel_saj_cle_contem (saj_id, cle_id) VALUES ($id_ajuste, $id)";
			 	$this->bancodedados->query($query);
      }
    }
    // Registra Comentário
    if ($comentario) {
      if (substr($comentario, 0, 3) != "<b>") {
        $comentario = "<b>Ação:</b> Adicionado e Ativado<br><b>Comentário</b>: ".trim(addslashes($comentario));
      }
    } else {
      $comentario = "<b>Ação:</b> Adicionado e Ativado";
    }
    $this->registraComentario($id, $comentario, $_SESSION["usuario"]);
    // Insere E-mail Contato
    if ($atualizar_contatos && !$this->buscaContatosClienteQuantidade($cliente_id)) {
	    $dados_dominio = $this->buscaDadosDominio($dominio_id);
	    $this->insereContatoCliente($cliente_id, $usuario, $dados_dominio["dominio"], $tipo_id);
    }
    return $id;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    if (!$this->dados_item) {
      $this->buscaDadosItem($_GET["i"]);
    }
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("clientes_emails.ver.inc.php");
      $this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
      $this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura_nome"], $this->conteudo);
      $this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
      $this->conteudo = str_replace("[dominio]", $this->dados_item["dominio_nome"], $this->conteudo);
      $this->conteudo = str_replace("[descricao]", $this->dados_item["descricao"], $this->conteudo);
      $this->conteudo = str_replace("[capacidade]", $this->formataCapacidade($this->dados_item["capacidade"]),$this->conteudo);
      $this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace("[ole_controle]", $this->dados_item["principal_nome"], $this->conteudo);
      $this->conteudo = str_replace("[ajustes_programados]", $this->geraAjustesProgramadosListagem($this->dados_item["ajustes_programados"]),$this->conteudo);
      $this->conteudo = str_replace("[comentarios]", $this->geraComentariosListagem($this->dados_item["comentarios"]),$this->conteudo);
      $this->conteudo = str_replace("[mensagem]", $_GET["mensagem"], $this->conteudo);
      if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehMigravel()) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehAtivavelOleControle()) $botoes[] = '<input type="button" name="ativar_ole_controle" class="buttonAzulEscuroBranco11" value="Ativar Olé Controle" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar_ole_controle&i=' . $this->dados_item["id"] . '\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Editar ###
  ##############

  function ehEditavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["editar"]) return false;
    if ($this->dados_item["status_id"] > 3) return false;
    return true;
  }

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehEditavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.editar.inc.php");
        $this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
        $this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura_nome"], $this->conteudo);
        $this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
        $this->conteudo = str_replace("[dominio]", $this->dados_item["dominio_nome"], $this->conteudo);
        $this->conteudo = str_replace("[descricao]", $this->dados_item["descricao"], $this->conteudo);
        $this->conteudo = str_replace("[capacidade]", $this->geraSelect($this->buscaCapacidades($this->buscaCapacidadeMaxima($this->dados_item["assinatura_id"]) + $this->dados_item["capacidade"]), $this->dados_item["capacidade"], true, "capacidade", "200px"), $this->conteudo);
        $this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(),$this->dados_item["tipo_id"], true, "tipo", "200px"), $this->conteudo);
        $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      	$this->conteudo = str_replace("[ole_controle]", $this->dados_item["principal_nome"], $this->conteudo);
        $this->conteudo = str_replace("[ajustes_programados]", $this->geraAjustesProgramadosListagem($this->dados_item["ajustes_programados"]), $this->conteudo);
        $this->conteudo = str_replace("[comentarios]", $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
        $this->conteudo = str_replace("[mensagem]", $_GET["mensagem"], $this->conteudo);
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehMigravel()) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" value="Migrar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=migrar&i=' . $this->dados_item["id"] . '\';">';
      	if ($this->ehAtivavelOleControle()) $botoes[] = '<input type="button" name="ativar_ole_controle" class="buttonAzulEscuroBranco11" value="Ativar Olé Controle" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar_ole_controle&i=' . $this->dados_item["id"] . '\';">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}",$this->conteudo);
      } else {
        // Trata Dados
        $capacidade_maxima = $this->buscaCapacidadeMaxima($this->dados_item["assinatura_id"]) + $this->dados_item["capacidade"];
        $_POST["descricao"] = trim($_POST["descricao"]);
        $_POST["senha"] = $_POST["senha"] ? "'".trim($_POST["senha"])."'" : "cle_senha";
        $_POST["capacidade"] = $_POST["capacidade"] > $capacidade_maxima ? $capacidade_maxima : $_POST["capacidade"];
        // Atualiza Registro
        $query = "UPDATE tab_clientes_emails SET cle_senha = {$_POST["senha"]}, cle_descricao = '{$_POST["descricao"]}', cle_capacidade = '{$_POST["capacidade"]}', cet_id = '{$_POST["tipo"]}' WHERE cle_id = '{$this->dados_item["id"]}'";
        $this->bancodedados->query($query);
        // Atualiza Contato
        $this->atualizaContatoEmail($this->dados_item["cliente_id"], $this->dados_item["usuario"], $this->dados_item["dominio_nome"], $_POST["tipo"]);
        // Registra Comentário
        $comentario = "<b>Ação:</b> Editado";
        if ($_POST["comentario"]) {
          $comentario .= "<br><b>Comentário</b>: ".trim(addslashes($_POST["comentario"]));
        }
        $this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
        // Publica
        if ($_POST["senha"] != "cle_senha" || $this->dados_item["capacidade"] != $_POST["capacidade"]) {
          $this->solicitaClienteEmailPublicacao($this->dados_item["id"]);
          if (($erro = $this->publicaClientesEmails(array($this->dados_item["id"]))) <= 0) {
            $mensagem = urlencode("Publicação não concluída (ERRO: ".$this->buscaErro($erro).")!");
          }
        }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}&mensagem=$mensagem");
      }
    } else {
      if ($this->dados_item) {
        $this->ver();
      } else {
        $this->fechar();
      }
    }
  }

  ###############
  ### Remover ###
  ###############

  function ehRemovivel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["remover"]) return false;
    return true;
  }

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehRemovivel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}",$this->conteudo);
      } else {
        // Remove do Servidor
        if ($this->dados_item["status_id"] <= 3) {
          if (!$this->controle[$this->dados_item["dominio_integracao"]]) {
            $this->controle[$this->dados_item["dominio_integracao"]] = controle::instanciaObjeto("clientes_emails.{$this->dados_item["dominio_integracao"]}");
          }
          if ($this->controle[$this->dados_item["dominio_integracao"]]->verificarEmail($this->dados_item["usuario"], $this->dados_item["dominio_nome"])) {
            $this->controle[$this->dados_item["dominio_integracao"]]->removerEmail($this->dados_item["usuario"], $this->dados_item["dominio_nome"]);
          }
          $this->removeContatoCliente($this->dados_item["usuario"], $this->dados_item["dominio_nome"]);
        }
        $query = "UPDATE tab_clientes_autenticacoes SET cle_id = NULL WHERE cle_id = '{$this->dados_item["id"]}'";
        $this->bancodedados->query($query);
        // Deleta Relacionamento c/ Ajustes
        $query = "DELETE FROM rel_saj_cle_contem WHERE cle_id = '{$this->dados_item["id"]}'";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_clientes_emails_ajustes WHERE cle_id = '{$this->dados_item["id"]}'";
        $this->bancodedados->query($query);
        // Deleta Comentários
        $query = "DELETE FROM tab_clientes_emails_comentarios WHERE cle_id = '{$this->dados_item["id"]}'";
        $this->bancodedados->query($query);
        // Deleta E-mail
        $query = "DELETE FROM tab_clientes_emails WHERE cle_id = '{$this->dados_item["id"]}'";
        $this->bancodedados->query($query);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Fecha Janela
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ################
  ### Publicar ###
  ################

  function ehPublicavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["publicar"]) return false;
    if (!$this->dados_item["dominio_integracao"]) return false;
    if ($this->dados_item["status_id"] > 3) return false;
    if ($this->dados_item["publicado"]) return false;
    return true;
  }

  function publicar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehPublicavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.publicar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes),$this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}",$this->conteudo);
      } else {
        if (($erro = $this->publicaClientesEmails(array($this->dados_item["id"]), $_SESSION["usuario"])) > 0) {
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				  $this->conteudo .= "<script type=\"text/javascript\">alert('Publicação concluída!')</script>";
				  $this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}'</script>";
        } else {
          $comentario = "<b>Ação:</b> Não Publicado (ERRO: ".$this->buscaErro($erro).")";
          if ($_POST["comentario"]) {
            $comentario .= "<br><b>Comentário</b>: ".trim(addslashes($_POST["comentario"]));
          }
          $this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
				  $this->conteudo .= "<script type=\"text/javascript\">alert('Publicação não concluída (ERRO: ".$this->buscaErro($erro).")!')</script>";
				  $this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}'</script>";
        }
      }
    } else {
      $this->fechar();
    }
  }

  function solicitaClienteEmailPublicacao($id) {
		$query = "UPDATE tab_clientes_emails SET cle_publicado = 0 WHERE cle_id = $id";
		$this->bancodedados->query($query);
  }

  function registraClienteEmailPublicacao($id) {
    $query = "UPDATE tab_clientes_emails SET cle_senha = '', cle_publicado = 1 WHERE cle_id = $id";
    $query = $this->bancodedados->query($query);
  }

  function publicaClientesEmails($clientes_emails, $usuario = null) {
    for ($i = 0; $i < sizeof($clientes_emails); $i++) {
      $clientes_emails[$i] = $this->buscaDadosClienteEmail($clientes_emails[$i]);
      if (!$this->controle || !array_key_exists($clientes_emails[$i]["dominio_integracao"], $this->controle)) {
        $this->controle[$clientes_emails[$i]["dominio_integracao"]] = controle::instanciaObjeto("clientes_emails.{$clientes_emails[$i]["dominio_integracao"]}");
      }
      if ($clientes_emails[$i]["status_id"] == 4) {
        if ($this->controle[$clientes_emails[$i]["dominio_integracao"]]->verificarEmail($clientes_emails[$i]["usuario"], $clientes_emails[$i]["dominio_nome"])) {
          $mensagem = $this->controle[$clientes_emails[$i]["dominio_integracao"]]->removerEmail($clientes_emails[$i]["usuario"], $clientes_emails[$i]["dominio_nome"]);
        }
        $this->removeContatoCliente($clientes_emails[$i]["usuario"], $clientes_emails[$i]["dominio_nome"]);
      } else {
        if ($this->controle[$clientes_emails[$i]["dominio_integracao"]]->verificarEmail($clientes_emails[$i]["usuario"], $clientes_emails[$i]["dominio_nome"])) {
          $mensagem = $this->controle[$clientes_emails[$i]["dominio_integracao"]]->editarEmail($clientes_emails[$i]["usuario"], $clientes_emails[$i]["dominio_nome"], $clientes_emails[$i]["senha"] ? $clientes_emails[$i]["senha"] : null, $clientes_emails[$i]["capacidade"], $clientes_emails[$i]["comentario"], $clientes_emails[$i]["bloqueado"] ? 2 : $clientes_emails[$i]["status_id"]);
        } else {
          $mensagem = $this->controle[$clientes_emails[$i]["dominio_integracao"]]->adicionarEmail($clientes_emails[$i]["usuario"], $clientes_emails[$i]["dominio_nome"], $clientes_emails[$i]["senha"] ? $clientes_emails[$i]["senha"] : null, $clientes_emails[$i]["capacidade"], $clientes_emails[$i]["comentario"], $clientes_emails[$i]["bloqueado"] ? 2 : $clientes_emails[$i]["status_id"]);
        }
      }
      if ($mensagem <= 0) {
        if (count($clientes_emails) == 1) {
          return $mensagem;
        }
      } else {
        // Define como publicado
        $this->registraClienteEmailPublicacao($clientes_emails[$i]["id"]);
        // Registra Comentário
        if ($clientes_emails[$i]["status_id"] < 4) {
          $this->registraComentario($clientes_emails[$i]["id"], "<b>Ação:</b> Publicado<br>", $usuario);
        } else {
          $this->registraComentario($clientes_emails[$i]["id"], "<b>Ação:</b> Conta removida do servidor<br>", $usuario);
        }
      }
    }
    return true;
  }

  ##################################################
  ### Mudar Status - Ativar, Desativar, Cancelar ###
  ##################################################

  function ehAtivavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["ativar"]) return false;
    if ($this->dados_item["status_id"] == 2) return true;
    return false;
  }

  function ehDesativavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["desativar"]) return false;
    if ($this->dados_item["status_id"] != 1) return false;
    if ($this->dados_item["assinatura_status_id"] != 2) return false;
    return true;
  }

  function ehCancelavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) return false;
    if ($this->dados_item["status_id"] < 3) return true;
    return false;
  }

  function ehDescancelavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["descancelar"]) return false;
    if ($this->dados_item["status_id"] != 3) return false;
    $contas = $this->contaClientesEmailsDisponiveisPorCliente($this->dados_item["cliente_id"]);
    if (!$contas["capacidade_maxima"] || !$contas["contas_disponiveis"]) return false;
    return true;
  }
  
  function ehAtivavelOleControle() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["ativar_ole_controle"]) return false;
    if ($this->dados_item["status_id"] > 2) return false;
    if ($this->dados_item["principal"]) return false;
    return true;
  }
  
  function ativar_ole_controle() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.ativar_ole_controle.inc.php");
        $this->conteudo = str_replace("[acao]", strtolower($this->dados_acao["descricao"]), $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
      	// Ativa Olé Controle
      	$this->ativarOleControle($this->dados_item["cliente_id"], $this->dados_item["id"]);
      	// Registra Comentário
        $comentario = "<b>Ação:</b> Olé Controle Ativado";
        if ($_POST["comentario"]) {
          $comentario .= "<br><b>Comentário</b>: ".trim(addslashes($_POST["comentario"]));
        }
      	$this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }
  
  function ativarOleControle($cliente_id, $email_id) {
  	$query = "
  	  UPDATE
  	  	tab_clientes_emails
  	  	INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
  	  SET
  	    cle_principal = IF(cle_id = $email_id, 1, 0)
  	  WHERE
  	    tab_assinaturas.cli_id = $cliente_id";
		$this->bancodedados->query($query);
  }
  
  function ativar() {
    $this->mudar_status(1, "ehAtivavel", "Ativado");
  }

  function desativar() {
    $this->mudar_status(2, "ehDesativavel", "Desativado");
  }

  function cancelar() {
    $this->mudar_status(3, "ehCancelavel", "Cancelado");
  }

  function descancelar() {
    $this->mudar_status(1, "ehDescancelavel", "Descancelado");
  }

  function mudar_status($status, $metodo_checagem, $comentario_acao) {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && (method_exists($this, $metodo_checagem) ? $this->{$metodo_checagem}() : true)) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.mudar_status.inc.php");
        $this->conteudo = str_replace("[acoes]", $this->dados_acao["descricao"], $this->conteudo);
        $this->conteudo = str_replace("[acao]", strtolower($this->dados_acao["descricao"]), $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        // Atualiza Status
        $cancelamento = $status == 3 ? "NOW()" : "NULL";
        $assinatura_id = $status == 1 ? $this->buscaAssinaturaDisponivel($this->dados_item["cliente_id"]) : $this->dados_item["assinatura_id"];
        $assinatura_id = $assinatura_id ? $assinatura_id : $this->dados_item["assinatura_id"];
        $query = "UPDATE tab_clientes_emails SET ass_id = $assinatura_id, ces_id = $status, cle_publicado = 0, cle_cancelamento = $cancelamento WHERE cle_id = {$this->dados_item["id"]}";
        $this->bancodedados->query($query);
        // Registra Comentário
        $comentario = "<b>Ação:</b> $comentario_acao";
        if ($_POST["comentario"]) {
          $comentario .= "<br><b>Comentário</b>: ".trim(addslashes($_POST["comentario"]));
        }
        $this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
        // Publica E-mail
        if (($erro = $this->publicaClientesEmails(array($this->dados_item["id"]))) <= 0) {
          $mensagem = urlencode("Publicação não concluída (ERRO: ".$this->buscaErro($erro).")!");
        }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}&mensagem=$mensagem");
      }
    } else {
      $this->fechar();
    }
  }

  function buscaErro($erro) {
    $erros[-1] = "Ação inexistente";
    $erros[-2] = "Ação não disponível";
    $erros[-3] = "Não foi possível conectar no servidor";
    $erros[-4] = "Versão incompatível";
    $erros[-5] = "Não foi possível autenticar no servidor";
    $erros[-6] = "Domínio inválido";
    $erros[-7] = "Usuário inválido";
    $erros[-8] = "Senha não informada";
    $erros[-9] = "Tamanho não informado";
    $erros[-10] = "E-mail já existe no servidor";
    $erros[-11] = "Impossível adicionar registro";
    $erros[-12] = "Domínio inválido";
    $erros[-13] = "Usuário inválido";
    $erros[-15] = "Registro não existe";
    $erros[-16] = "Impossível editar registro";
    $erros[-17] = "Domínio inválido";
    $erros[-18] = "Usuário inválido";
    $erros[-19] = "Registro não existe";
    $erros[-20] = "Impossível remover registro";
    $erros[-21] = "Impossível criar a pasta Maildir";
    $erros[-22] = "Impossível criar a pasta Lixeira";
    $erros[-23] = "Impossível criar a pasta Enviados";
    $erros[-24] = "Impossível criar a pasta Rascunhos";
    $erros[-25] = "Impossível criar a pasta Spam";
    $erros[-26] = "Impossível copiar os arquivos mailfilter e courierimapsubscribe";
    $erros[-27] = "Impossível alterar o grupo da pasta Maildir";
    $erros[-28] = "Impossível criar o registro no servidor";
    $erros[-29] = "Impossível atualizar o registro no servidor";
    $erros[-30] = "Impossível remover a pasta Maildir";
    $erros[-31] = "Maildir configurada incorretamente";
    $erros[-32] = "Impossível remover o registro no servidor";
    $erros[-33] = "Domínio inválido";
    $erros[-34] = "Usuário inválido";
    $erros[-35] = "Domínio inválido";
    return array_key_exists($erro, $erros) ? $erros[$erro] : "$erro (Desconhecido)";
  }

	##############
	### Listar ###
	##############

	function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
   	$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=clientes_emails" id="clientes_emails.js"></script>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    $this->conteudo .= $filtro->formulario;
    if ($_GET["f"] || $_GET["p"]) {
      $itens = $this->buscaItens($filtro, $pagina);
      $paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
      $listagem = $this->geraListagem($itens["itens"]);
      $this->conteudo .= '</td></tr></table>';
      $this->conteudo .= $paginacao->conteudo;
      $this->conteudo .= $listagem->conteudo;
      $this->conteudo .= $paginacao->conteudo;
    } else {
      $this->conteudo .= '</td></tr></table>';
    }
	}

  function inicializaFiltro($valores = "") {
    // Cliente
    $itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
    // Usuário
    $itens[] = array ("tipo" => "input", "nome" => "usuario", "descricao" => "Usuário", "valor" => "", "where" => "(tab_clientes_emails.cle_usuario LIKE '%[valor]%')");
    // Domínio
    $dominios = $this->buscaDominiosPorAssinatura("%", true);
    foreach ($dominios as $id => $nome) {
      $select_dominios[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "dominio", "descricao" => "Dominio", "valor" => $select_dominios, "where" => "(tab_dominios.dom_id = [valor])");
    // Descrição
    $itens[] = array ("tipo" => "input", "nome" => "descricao", "descricao" => "Descrição", "valor" => "", "where" => "(tab_clientes_emails.cle_descricao LIKE '%[valor]%')");
    // Tipo
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "(tab_clientes_emails.cet_id = [valor])");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
      next($status);
    }
    $select_status[] = array ("id" => "-1", "nome" => "Cancelado", "where" => "(tab_clientes_emails.ces_id IN (3,4))");
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_clientes_emails.ces_id = [valor])");
    // Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}");
    return $filtro;
  }

  function geraPaginacao($filtro, $total, $pagina) {
    $paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
    return $paginacao;
  }

  function buscaItens($filtro, $pagina) {
    $limit_inicio = ($pagina - 1) * PAG_ITENS;
    $limit_fim = PAG_ITENS;
    $query = "
      SELECT SQL_CALC_FOUND_ROWS
        tab_clientes_emails.cle_id AS id,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente,
        tab_clientes_emails.cle_usuario AS usuario,
        tab_dominios.dom_dominio AS dominio,
        tab_clientes_emails.cle_descricao AS descricao,
        tab_clientes_emails.cle_capacidade AS capacidade,
        tab_clientes_emails.cle_publicado AS publicado,
        tab_clientes_emails_tipos.cet_nome AS tipo,
        tab_clientes_emails_status.ces_id AS status_id,
        tab_clientes_emails_status.ces_nome AS status
      FROM
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
        INNER JOIN tab_clientes_emails_tipos ON (tab_clientes_emails.cet_id = tab_clientes_emails_tipos.cet_id)
        INNER JOIN tab_clientes_emails_status ON (tab_clientes_emails.ces_id = tab_clientes_emails_status.ces_id)
      WHERE
			  $filtro->parametros_sql
			ORDER BY
			  tab_clientes_emails.cle_usuario,
			  tab_dominios.dom_dominio
			LIMIT
			  $limit_inicio,
			  $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["status_id"] < 3) {
        $dados["status"] .= " (".($dados["publicado"] ? "Publicado" : "Não Publicado").")";
      }
		if ($this->permissoes["clientes"]["ver_dt"]) {
			$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
		}
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
        $dados["link"] = "javascript:verClienteEmail({$dados["id"]})";
      }
			$dados["capacidade"] = $this->formataCapacidade($dados["capacidade"]);
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "cliente_id" => "ID", "cliente" => "Cliente", "usuario" => "Usuário", "dominio" => "Domínio", "capacidade" => "Capacidade", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "35"), null, array("right"));
		return $listagem;
  }

  ###########
  ### RDS ###
  ###########

  function geraClientesEmailsClienteListagem($cliente_id) {
  	$clientes_emails_cliente = $this->buscaClientesEmailsPorCliente($cliente_id);
    if ($clientes_emails_cliente) {
    	$i = 0;
      foreach ($clientes_emails_cliente AS $id => $nome) {
        $clientes_emails_cliente[$id] = $this->buscaDadosClienteEmail($id);
        if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) $clientes_emails_cliente[$id]["link"] = "javascript:verClienteEmail($id)";
        $clientes_emails_cliente[$id]["dominio"] = $clientes_emails_cliente[$id]["dominio_nome"];
        $clientes_emails_cliente[$id]["capacidade"] = $this->formataCapacidade($clientes_emails_cliente[$id]["capacidade"]);
        $clientes_emails_cliente[$id]["tipo"] = $clientes_emails_cliente[$id]["tipo_nome"];
        $clientes_emails_cliente[$id]["status"] = str_replace(array(" (Publicado)", " (Não Publicado)"), array("<br>(Publicado)", "<br>(Não Publicado)"), $clientes_emails_cliente[$id]["status_nome"]);
        $clientes_emails_cliente[$id]["status"] = str_replace(" (", "<br>(", $clientes_emails_cliente[$id]["status_nome"]);
        $clientes_emails_cliente[$id]["count"] = ++$i;
      }
    }
    $cabecalho = array ("count" => "#", "usuario" => "Usuário", "dominio" => "Domínio", "descricao" => "Descrição", "capacidade" => "Capacidade", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $clientes_emails_cliente, 0, false, array("20", "120", "180", "", "70", "90"), null, array("right", "left", "left", "left"));
    return $listagem;
  }

  function geraClientesEmailsClienteAdicionar($cliente_id) {
    $contas_disponiveis = $this->contaClientesEmailsDisponiveisPorCliente($cliente_id);
    if ($contas_disponiveis["capacidade_maxima"] && $contas_disponiveis["contas_disponiveis"]) {
      if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
        $retorno = '<input type="button" class="buttonAzulEscuroBranco11" name="clientes_emails_adicionar" value="Adicionar" onClick="adicionarClienteEmail('.$cliente_id.')"> ';
      }
    }
    if ($contas_disponiveis["capacidade_maxima"] || $contas_disponiveis["contas_disponiveis"]) {
      $retorno .= $this->formataCapacidade($contas_disponiveis["capacidade_maxima"]). " disponíveis ({$contas_disponiveis["contas_disponiveis"]} ".($contas_disponiveis["contas_disponiveis"] == 1 ? ' conta restante)' : ' contas restantes)');
    } else {
      $retorno = 'Nenhuma conta disponível';
    }
    return $retorno;
  }

  function contaClientesEmailsDisponiveisPorCliente($cliente_id) {
    $contas["contas_disponiveis"] = 0;
    $contas["capacidade_maxima"] = 0;
    $query = "
			SELECT
        tab_assinaturas.ass_id AS id,
        tab_assinaturas.ass_parametros AS parametros,
        COUNT(tab_clientes_emails.cle_id) AS emails_existentes,
        SUM(tab_clientes_emails.cle_capacidade) AS emails_capacidade
			FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id AND tab_clientes_emails.ces_id IN (1, 2))
	  	WHERE
        tab_assinaturas.ast_id IN (1, 2, 5) AND
        tab_assinaturas.cli_id = $cliente_id
      GROUP BY
        tab_assinaturas.ass_id
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      $contas["contas_disponiveis"] += $dados["parametros"]["emails_quantidade"] - $dados["emails_existentes"];
      $contas["capacidade_maxima"] += ($dados["parametros"]["emails_capacidade"] * $dados["parametros"]["emails_quantidade"]) - $dados["emails_capacidade"];
    }
    $contas["contas_disponiveis"] = $contas["contas_disponiveis"] <= 0 ? 0 : $contas["contas_disponiveis"];
    $contas["capacidade_maxima"] = $contas["capacidade_maxima"] <= 0 ? 0 : $contas["capacidade_maxima"];
    return $contas;
  }

  function buscaAssinaturaDisponivel($cliente_id) {
    $query = "
			SELECT
        tab_assinaturas.ass_id AS id,
        tab_assinaturas.ass_parametros AS parametros,
        COUNT(tab_clientes_emails.cle_id) AS emails_existentes,
        SUM(tab_clientes_emails.cle_capacidade) AS emails_capacidade
			FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id AND tab_clientes_emails.ces_id IN (1, 2))
	  	WHERE
        tab_assinaturas.ast_id IN (1, 2, 5) AND
        tab_assinaturas.cli_id = $cliente_id
      GROUP BY
        tab_assinaturas.ass_id
      ORDER BY
        tab_assinaturas.ast_id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      $dados["contas_disponiveis"] += $dados["parametros"]["emails_quantidade"] - $dados["emails_existentes"];
      $dados["capacidade_maxima"] += ($dados["parametros"]["emails_capacidade"] * $dados["parametros"]["emails_quantidade"]) - $dados["emails_capacidade"];
      if ($dados["contas_disponiveis"] > 0) {
        return $dados["id"];
      }
    }
  }

  function geraClientesEmailsMensagemVerificacaoUsuario($usuario, $dominio, $cliente = "", $cliente_email = "") {
    $cliente_email = $this->buscaDadosClienteEmail($cliente_email, true);
    $cliente_email_usuario_dominio = $this->buscaDadosClienteEmailPorUsuarioPorDominioId($usuario, $dominio, $cliente);
    if ($cliente_email_usuario_dominio["id"] != $cliente_email["id"]) {
      return "Este e-mail está sendo utilizado pelo cliente \"{$cliente_email_usuario_dominio["cliente_nome"]}\"!";
    }
    if (!$this->voip_contas) $this->voip_contas = controle::instanciaObjeto("voip_contas", $this->bancodedados, $this->permissoes, false);
    $voip_conta = $this->voip_contas->buscaDadosVoipContaPorUsuario($usuario, true);
    if ($voip_conta && $dominio == 1001) {
      if ($voip_conta["credito"]["cliente_id"] != $cliente) {
        return "O este e-mail está bloqueado por uma Conta VoIP do cliente \"{$voip_conta["credito"]["cliente_nome"]}\"!";
      }
    }
    if ($usuario == "abuse" || $usuario == "postmaster") {
      return "Este é um nome protegido e não pode ser utilizado!";
    }
  }
  
  ##############
  ### Migrar ###
  ##############
  
  function ehMigravel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["migrar_plus"]) return true;
    if (!$this->permissoes[$this->dados_modulo["nome"]]["migrar"]) return false;
    if (!in_array($this->dados_item["cliente_id"], array(62291, 3000))) return false;
    return true;
  }

  function migrar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehMigravel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_emails.migrar.inc.php");
        $this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;'.$this->dados_acao["descricao"].'&nbsp;</b></td>', $this->conteudo);
        $this->conteudo = str_replace("[acao]", strtolower($this->dados_acao["descricao"]), $this->conteudo);
        $this->conteudo = str_replace("[email]", "{$this->dados_item["usuario"]}@{$this->dados_item["dominio_nome"]}", $this->conteudo);
        $this->conteudo = str_replace("[cliente_origem_id]", $this->dados_item["cliente_id"], $this->conteudo);
        $this->conteudo = str_replace("[cliente_origem_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace("[mensagem]", $_GET["mensagem"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
      	$assinatura_id = $this->buscaAssinaturaEmail($_POST["cliente_destino_id"]);
        if ($assinatura_id) {
          // Migra E-mail
          $query = "UPDATE tab_clientes_emails SET ass_id = $assinatura_id WHERE cle_id = {$this->dados_item["id"]}";
          $this->bancodedados->query($query);
          // Registra Comentário
          $comentario = "<b>Ação:</b> Migrado do cliente [{$this->dados_item["cliente_id"]}] {$this->dados_item["cliente_nome"]}";
          if ($_POST["comentario"]) $comentario .= "<br><b>Comentário:</b> {$_POST["comentario"]}";
          $this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
          // Registra Contato
	        $this->insereContatoCliente($_POST["cliente_destino_id"], $this->dados_item["usuario"], $this->dados_item["dominio_nome"], $this->dados_item["tipo_id"]);
          // Redireciona
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
        } else {
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=migrar&i={$_GET["i"]}&mensagem=Cliente não encontrado ou sem assinatura disponível!");
        }
      }
    } else {
      $this->fechar();
    }
  }
  
  function buscaAssinaturaEmail($cliente_id) {
    $query = "
      SELECT
        tab_assinaturas.ass_id AS id,
        tab_assinaturas.ass_parametros AS parametros,
        COUNT(DISTINCT IF(tab_clientes_emails.ces_id IN (1, 2), tab_clientes_emails.cle_id, NULL)) AS emails_existentes
      FROM
        tab_assinaturas
        LEFT OUTER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_assinaturas.ast_id IN (1, 2, 5)
      GROUP BY
        tab_assinaturas.ass_id
      ORDER BY
        IF(tab_assinaturas.ast_id = 2, 0, IF(tab_assinaturas.ast_id = 5, 1, 2)),
        tab_assinaturas.ass_pai";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      if ($dados["parametros"]["emails_quantidade"] - $dados["emails_existentes"] > 0) {
        return $dados["id"];
      }
    }
  }

}

?>
