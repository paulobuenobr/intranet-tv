<?

class pesquisas_aplicacoes {

  var $bancodedados;
  var $permissoes;
  var $acao;
  var $conteudo;
  var $dados_item;
  var $dados_modulo;
  var $dados_acao;

  function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "buscar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

	function geraTitulo() {
		$titulo	= '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';
		return $titulo;
	}

	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}

	function buscaDadosModulo($nome) {
		$query = "SELECT mod_id AS id, mod_nome AS nome, mod_descricao AS descricao FROM tab_modulos WHERE mod_nome = '$nome'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_modulo = $this->bancodedados->resultadoArray($query);
		}
	}

	function buscaDadosAcao($nome) {
		$query = "SELECT aco_id AS id, aco_nome AS nome, aco_descricao AS descricao, act_id AS tipo FROM tab_acoes WHERE aco_nome = '$nome' and mod_id = '{$this->dados_modulo["id"]}'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_acao = $this->bancodedados->resultadoArray($query);
			$this->acao = $this->dados_acao["nome"];
		}
	}

  function buscaDadosItem($pesquisa_aplicacao_id) {
    $query = "
  	  SELECT
  	  	tab_pesquisas_aplicacoes.pea_id AS id,
  	    DATE_FORMAT(tab_pesquisas_aplicacoes.pea_cadastro, '%d/%m/%Y') AS cadastro,
  	    DATE_FORMAT(tab_pesquisas_aplicacoes.pea_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
  	    DATE_FORMAT(tab_pesquisas_aplicacoes.pea_termino, '%d/%m/%Y (%H:%i)') AS termino,
  	    COUNT(DISTINCT tab_pesquisas_aplicacoes_respostas.peq_id) respostas,
  	    COUNT(DISTINCT tab_pesquisas_questoes.peq_id) AS questoes,
  	    CONCAT(COUNT(DISTINCT tab_pesquisas_aplicacoes_respostas.peq_id), ' / ', COUNT(DISTINCT tab_pesquisas_questoes.peq_id)) AS andamento,
  	    tab_pesquisas_aplicacoes.cor_id AS contrato_id,
  	    tab_pesquisas_aplicacoes.cha_id AS chamado_id,
  	    tab_pesquisas.pes_id AS pesquisa_id,
  	    tab_pesquisas.pes_descricao AS descricao,
  	    tab_pesquisas.pes_texto_inicial AS texto_inicial,
  	    tab_pesquisas.pes_texto_final AS texto_final,
  	  	tab_clientes.cli_id AS cliente_id,
  	  	tab_clientes.cls_id AS cliente_status_id,
  	  	tab_clientes.cli_nome AS cliente_nome,
  	  	tab_usuarios.usu_id AS aplicador_id,
  	  	tab_usuarios.usu_nome AS aplicador_nome
  	  FROM
  	    tab_pesquisas_aplicacoes
  	    INNER JOIN tab_pesquisas ON (tab_pesquisas_aplicacoes.pes_id = tab_pesquisas.pes_id)
  	    LEFT OUTER JOIN tab_pesquisas_questoes ON (tab_pesquisas.pes_id = tab_pesquisas_questoes.pes_id)
  	    LEFT OUTER JOIN tab_pesquisas_aplicacoes_respostas ON (tab_pesquisas_aplicacoes.pea_id = tab_pesquisas_aplicacoes_respostas.pea_id)
  	    INNER JOIN tab_clientes ON (tab_pesquisas_aplicacoes.cli_id = tab_clientes.cli_id)
  	    LEFT OUTER JOIN tab_usuarios ON (tab_pesquisas_aplicacoes.usu_id = tab_usuarios.usu_id)
  	  WHERE
  	  	tab_pesquisas_aplicacoes.pea_id = $pesquisa_aplicacao_id
  	  GROUP BY
  	    tab_pesquisas_aplicacoes.pea_id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["cliente_primeiro_nome"] = $this->buscaPrimeiroNome($this->dados_item["cliente_nome"]);
      $this->dados_item["cliente_telefones"] = $this->buscaTelefones($this->dados_item["cliente_id"]);
      $this->dados_item["usuario_primeiro_nome"] = $this->buscaPrimeiroNome($this->buscaUsuarioNome());
      $this->dados_item["saudacao"] = $this->buscaSaudacao();
      foreach ($this->dados_item as $campo => $valor) {
      	$this->dados_item["texto_inicial"] = str_replace("[$campo]", $valor, $this->dados_item["texto_inicial"]);
      }
    }
  }

  function buscaUsuarioNome() {
    $query = "
      SELECT
        usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
        usu_id = {$_SESSION["usuario"]}";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

  function buscaSaudacao() {
  	$horario = intval(strtotime("H"), 10);
  	if ($horario <= 12) {
  		return "Bom dia";
  	} elseif ($horario <= 18) {
  		return "Boa tarde";
  	} else {
  		return "Boa noite";
  	}
  }

  function buscaPrimeiroNome($nome) {
  	$i = 0;
  	$primeiro_nome = "";
  	while ($i < strlen($nome) && $nome[$i] != " ") {
  	  $primeiro_nome .= $nome[$i];
  	  $i++;
  	}
  	return $primeiro_nome;
  }

  function buscaTelefones($cliente_id) {
    $query = "
      SELECT
        ccl_id AS id,
        ccl_ddd AS contato_ddd,
        ccl_numero AS contato_numero,
        ccl_ramal AS contato_ramal,
        tab_clientes_contatos_telefones_tipos.ccf_id AS contato_telefone_tipo_id,
        tab_clientes_contatos_telefones_tipos.ccf_nome AS contato_telefone_tipo
      FROM
        tab_clientes_contatos_telefones
        INNER JOIN tab_clientes_contatos_telefones_tipos ON (tab_clientes_contatos_telefones.ccf_id = tab_clientes_contatos_telefones_tipos.ccf_id)
      WHERE
        tab_clientes_contatos_telefones.cli_id = $cliente_id AND
		tab_clientes_contatos_telefones.ccf_id > 0
      ORDER BY
        contato_telefone_tipo,
        contato_ddd,
        contato_numero";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $telefone = "({$dados["contato_ddd"]}) {$dados["contato_numero"]}";
      if ($dados["contato_ramal"]) {
        $telefone .= " (Ramal {$dados["contato_ramal"]})";
      }
      $lista[] = $telefone;
    }
    return @implode(" / ", $lista);
  }

  function buscaQuestoes($pesquisa_id, $pesquisa_aplicacao_id, $numero_questoes) {
  	$query = "
  		SELECT
  			tab_pesquisas_questoes.peq_id AS id,
  			tab_pesquisas_questoes.peq_numero AS numero,
  			tab_pesquisas_questoes.peq_conteudo AS conteudo
  		FROM
  		  tab_pesquisas_questoes
  		WHERE
  			tab_pesquisas_questoes.pes_id = $pesquisa_id
  		ORDER BY
  			tab_pesquisas_questoes.peq_numero";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["numero"] = sprintf("%0".(strlen($numero_questoes) + 1)."s", $dados["numero"]);
    	$dados["conteudo"] = array("<table cellpadding=\"1\" cellspaing=\"1\" border=\"0\" width=\"100%\"><tr class=\"txtAzulEscuro11\"><td colspan=\"3\" bgcolor=\"#73a1c3\" style=\"color:#FFFFFF;padding:5px\"><b>{$dados["conteudo"]}</b></td></tr>");
    	$respostas = $this->buscaRespostas($pesquisa_aplicacao_id, $dados["id"]);
    	$alternativas = $this->buscaQuestaoAlternativas($dados["id"]);
    	if ($alternativas) {
    		foreach ($alternativas as $ordem => $dados_alternativa) {
    			$dados["conteudo"][] = "<tr class=\"txtAzulEscuro11\">";
    			$dados["conteudo"][] = "<td align=\"center\" width=\"15\" bgcolor=\"#c9d4e0\"><input type=\"checkbox\"".($respostas["fechada"] && in_array($ordem, $respostas["fechada"]) ? " checked" : "")." disabled></td>";
    			$dados["conteudo"][] = "<td align=\"center\" width=\"15\" bgcolor=\"#c9d4e0\">".$this->formataAlternativa($ordem).")</td>";
    			$dados["conteudo"][] = "<td bgcolor=\"#c9d4e0\">{$dados_alternativa["conteudo"]}</td>";
    			$dados["conteudo"][] = "</tr>";
    		}
    	}
    	if ($respostas["aberta"]) {
  			$dados["conteudo"][] = "<tr class=\"txtAzulEscuro11\">";
  			$dados["conteudo"][] = "<td bgcolor=\"#c9d4e0\" colspan=\"3\">{$respostas["aberta"]}</td>";
  			$dados["conteudo"][] = "</tr>";
    	}
    	$dados["conteudo"][] = "</table>";
    	$questao["numero"] = $dados["numero"];
    	$questao["conteudo"] = implode($dados["conteudo"]);
    	$questoes[] = $questao;
    }
    return $questoes;
  }

  function formataAlternativa($alternativa) {
  	return chr(ord("a") + $alternativa - 1);
  }

  function buscaQuestaoAlternativas($questao_id) {
  	$query = "
  		SELECT
  		  pqa_id AS id,
  			pqa_ordem AS ordem,
  			pqa_conteudo AS conteudo
  		FROM
  			tab_pesquisas_questoes_alternativas
  		WHERE
  			peq_id = $questao_id
  		ORDER BY
  			pqa_ordem";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$alternativas[$dados["ordem"]] = $dados;
    }
    return $alternativas;
  }

  function buscaDadosQuestao($pesquisa_id, $aplicacao_id, $numero = 1) {
  	if (!$_SESSION["respostas"][$aplicacao_id][$numero]) {
  		$_SESSION["respostas"][$aplicacao_id][$numero] = $this->buscaQuestao($pesquisa_id, $aplicacao_id, $numero);
  	}
  }

  function geraQuestao($pesquisa_id, $aplicacao_id, $numero = 1) {
  	return $_SESSION["respostas"][$aplicacao_id][$numero]["conteudo"];
  }

  function geraResposta($pesquisa_id, $aplicacao_id, $numero = 1) {
  	$resposta[] = "<table cellpadding=\"1\" cellspaing=\"1\" border=\"0\" width=\"100%\"><tr class=\"txtAzulEscuro11\">";
  	if ($_SESSION["respostas"][$aplicacao_id][$numero]["alternativas"]) {
  		foreach ($_SESSION["respostas"][$aplicacao_id][$numero]["alternativas"] as $ordem => $dados) {
  			$resposta[] = "<tr class=\"txtAzulEscuro11\">";
  			$resposta[] = "<td bgcolor=\"#c9d4e0\" align=\"center\" width=\"20\"><input type=\"checkbox\" id=\"resposta_{$ordem}\" onclick=\"clicaResposta({$ordem})\"".($_SESSION["respostas"][$aplicacao_id][$numero]["respostas"]["fechada"] && in_array($ordem, $_SESSION["respostas"][$aplicacao_id][$numero]["respostas"]["fechada"]) ? " checked" : "")."></td>";
  			$resposta[] = "<td align=\"center\" width=\"20\" bgcolor=\"#73a1c3\"><label style=\"color:#FFFFFF;font-size:16px;font-weight:bold\" for=\"resposta_{$ordem}\">".$this->formataAlternativa($ordem).")</label></td>";
  			$resposta[] = "<td bgcolor=\"#c9d4e0\" style=\"padding-left:3px;font-size:16px;font-weight:bold\"><label for=\"resposta_{$ordem}\">{$dados["conteudo"]}</label></td>";
  			$resposta[] = "</tr>";
  		}
  	}
  	if ($_SESSION["respostas"][$aplicacao_id][$numero]["tipo_id"] != 3) {
  		if ($_SESSION["respostas"][$aplicacao_id][$numero]["variavel_tipo_id"] == 1) {
  			// Nominal
  			$resposta[] = "<tr class=\"txtAzulEscuro11\">";
  			$resposta[] = "<td colspan=\"3\" bgcolor=\"#c9d4e0\" style=\"padding:4px\"><textarea class=\"textBrancoAzulEscuro11\" id=\"resposta_nominal\" wrap=\"virtual\" rows=\"3\" style=\"width:100%\">{$_SESSION["respostas"][$aplicacao_id][$numero]["respostas"]["aberta"]}</textarea></td>";
  			$resposta[] = "</tr>";
  		} elseif ($_SESSION["respostas"][$aplicacao_id][$numero]["variavel_tipo_id"] == 2) {
  			// Ordinal
  			$resposta[] = "<tr class=\"txtAzulEscuro11\">";
  			$resposta[] = "<td colspan=\"3\" bgcolor=\"#c9d4e0\" style=\"padding:4px\"><input type=\"text\" class=\"textBrancoAzulEscuro11\" id=\"resposta_ordinal\" style=\"width:100%\" value=\"{$_SESSION["respostas"][$aplicacao_id][$numero]["respostas"]["aberta"]}\"></td>";
  			$resposta[] = "</tr>";
  		} elseif ($_SESSION["respostas"][$aplicacao_id][$numero]["variavel_tipo_id"] == 3) {
  			// Discreta
  			$resposta[] = "<tr class=\"txtAzulEscuro11\">";
  			$resposta[] = "<td colspan=\"3\" bgcolor=\"#c9d4e0\" style=\"padding:4px\"><input type=\"text\" class=\"textBrancoAzulEscuro11\" id=\"resposta_discreta\" style=\"width:100%\" value=\"{$_SESSION["respostas"][$aplicacao_id][$numero]["respostas"]["aberta"]}\" onKeyPress=\"return formataNumero(this, event)\" onblur=\"return formataNumero(this, event)\"></td>";
  			$resposta[] = "</tr>";
  		} elseif ($_SESSION["respostas"][$aplicacao_id][$numero]["variavel_tipo_id"] == 4) {
  			// Contínua
  			$resposta[] = "<tr class=\"txtAzulEscuro11\">";
  			$resposta[] = "<td colspan=\"3\" bgcolor=\"#c9d4e0\" style=\"padding:4px\"><input type=\"text\" class=\"textBrancoAzulEscuro11\" id=\"resposta_continua\" style=\"width:100%\" value=\"{$_SESSION["respostas"][$aplicacao_id][$numero]["respostas"]["aberta"]}\" onKeyPress=\"return formataNumeroDecimal(this, event)\" onblur=\"return formataNumeroDecimal(this, event)\"></td>";
  			$resposta[] = "</tr>";
  		}
  	}
		$resposta[] = "</table>";
  	return implode($resposta);
  }

  function buscaQuestao($pesquisa_id, $aplicacao_id, $numero) {
  	$query = "
  	  SELECT
  	    tab_pesquisas_questoes.peq_id AS questao_id,
  	    tab_pesquisas_questoes.peq_conteudo AS conteudo,
  	    tab_pesquisas_questoes.peq_minimo AS minimo,
  	    tab_pesquisas_questoes.peq_maximo AS maximo,
  	    tab_pesquisas_questoes.peq_maximo_alternativas AS maximo_alternativas,
  	    tab_pesquisas_questoes.pqt_id AS tipo_id,
  	    tab_pesquisas_questoes.pvt_id AS variavel_tipo_id
  	  FROM
  	    tab_pesquisas_questoes
  	  WHERE
  	    tab_pesquisas_questoes.pes_id = $pesquisa_id AND
  	    tab_pesquisas_questoes.peq_numero = $numero";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$dados["minimo"] = intval($dados["minimo"], 10);
    	$dados["maximo"] = intval($dados["maximo"], 10);
    	$dados["maximo_alternativas"] = intval($dados["maximo_alternativas"], 10);
    	$dados["alternativas"] = $this->buscaQuestaoAlternativas($dados["questao_id"]);
    	$dados["respostas"] = $this->buscaRespostas($aplicacao_id, $dados["questao_id"]);
    	return $dados;
    }
  }

  function buscaRespostas($aplicacao_id, $questao_id) {
  	$query = "
  	  SELECT
  	  	par_valor_qualitativo AS valor_qualitativo,
  	  	par_valor_quantitativo AS valor_quantitativo,
				pqa_ordem AS ordem
  	  FROM
  	    tab_pesquisas_aplicacoes_respostas
  	    LEFT OUTER JOIN tab_pesquisas_questoes_alternativas ON (tab_pesquisas_aplicacoes_respostas.pqa_id = tab_pesquisas_questoes_alternativas.pqa_id)
  	  WHERE
  	    tab_pesquisas_aplicacoes_respostas.pea_id = $aplicacao_id AND
  	    tab_pesquisas_aplicacoes_respostas.peq_id = $questao_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($dados["ordem"]) {
    		$respostas["fechada"][] = $dados["ordem"];
    	} elseif ($dados["valor_qualitativo"]) {
    		$respostas["aberta"] = $dados["valor_qualitativo"];
    	} elseif ($dados["valor_quantitativo"]) {
    		$respostas["aberta"] = $dados["valor_quantitativo"];
    	}
    }
    return $respostas;
  }

  function concluirAplicacao($pesquisa_id, $aplicacao_id) {
  	foreach ($_SESSION["respostas"][$aplicacao_id] as $questao) {
  	  $this->buscaDadosItem($aplicacao_id);
  		// Remove Respostas Antigas
			$query = "DELETE FROM tab_pesquisas_aplicacoes_respostas WHERE pea_id = $aplicacao_id AND peq_id = {$questao["questao_id"]}";
			$this->bancodedados->query($query);
			// Registra Respostas Fechadas
  		if ($questao["tipo_id"] > 1 && $questao["respostas"]["fechada"]) {
  			foreach ($questao["respostas"]["fechada"] as $ordem) {
  				$alternativa_id = $questao["alternativas"][$ordem]["id"];
  				if ($alternativa_id) {
  					$query = "INSERT INTO tab_pesquisas_aplicacoes_respostas (pqa_id, pea_id, peq_id) VALUES ($alternativa_id, $aplicacao_id, {$questao["questao_id"]})";
    				$this->bancodedados->query($query);
    				// Trata Abertura Chamado Comercial
    				if ($questao["questao_id"] == 18 && $alternativa_id == 24 && !$this->dados_item["termino"]) {
              $codigo_chamado = $this->inserirChamado($this->dados_item["cliente_id"], 10314, "Entrar em contato com o cliente para sanar dúvidas referentes ao plano, valores ou serviço");
    				}
  				}
  			}
				// Registra Respostas
  			$respostas[$questao["questao_id"]] = $questao["questao_id"];
  		}
			// Registra Resposta Aberta
  		if ($questao["tipo_id"] < 3 && $questao["respostas"]["aberta"]) {
  			if ($questao["variavel_tipo_id"] <= 2) {
  				$valor_qualitativo = "'{$questao["respostas"]["aberta"]}'";
  				$valor_quantitativo = "NULL";
  			} elseif ($questao["variavel_tipo_id"] == 3) {
  				$valor_qualitativo = "NULL";
  				$valor_quantitativo = intval($questao["respostas"]["aberta"], 10);
  			} elseif ($questao["variavel_tipo_id"] == 4) {
  				$valor_qualitativo = "NULL";
  				$valor_quantitativo = str_replace(",", ".", $questao["respostas"]["aberta"]);
  			}
				$query = "INSERT INTO tab_pesquisas_aplicacoes_respostas (par_valor_qualitativo, par_valor_quantitativo, pea_id, peq_id) VALUES ($valor_qualitativo, $valor_quantitativo, $aplicacao_id, {$questao["questao_id"]})";
				$this->bancodedados->query($query);
				// Registra Respostas
  			$respostas[$questao["questao_id"]] = $questao["questao_id"];
  		}
  	}
  	if (count($respostas) > 0 && count($respostas) == count($_SESSION["respostas"][$aplicacao_id])) {
			$query = "UPDATE tab_pesquisas_aplicacoes SET pea_termino = NOW(), usu_id = {$_SESSION["usuario"]} WHERE pea_id = $aplicacao_id";
      $this->bancodedados->query($query);
      if ($this->dados_item["chamado_id"]) {
        $this->fecharChamado($this->dados_item["chamado_id"]);
        print "if (window.opener && !window.opener.closed && window.opener.chamado_atual == '{$this->dados_item["chamado_id"]}' && window.opener.navegacaoIr) navegacaoIr(0);";
      }
  	} else {
			$query = "UPDATE tab_pesquisas_aplicacoes SET pea_termino = NULL WHERE pea_id = $aplicacao_id";
      $this->bancodedados->query($query);
  	}
  	if ($codigo_chamado) {
      print "alert('Chamado <<$codigo_chamado>> encaminhado ao setor comercial!');";
  	}
  	if ($this->dados_item["texto_final"]) {
      print "alert('Instrução final:\\n\\n".str_replace(array("\r\n", "\n"), "\\n", $this->dados_item["texto_final"])."');";
  	}
    print "window.location = './?m=pesquisas_aplicacoes&a=ver&i={$aplicacao_id}';";
  }

  function inserirChamado($cliente_id, $categoria_id, $conteudo) {
    // Gera Dados Chamado
    $dados_chamado["solicitante"] = "Sistema (Intranet)";
    $dados_chamado["categoria"] = $categoria_id;
    $dados_chamado["prioridade"] = 3;
    $dados_chamado["tipo"] = 5;
    $dados_chamado["conteudo"] = $conteudo;
    // Instancia Classe Chamados
    $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
    $chamado_id = $chamados->adicionarChamado($cliente_id, null, $dados_chamado, "NULL");
    // Retorna Codigo
    return $chamados->buscaChamadoCodigo($chamado_id);
  }

  function fecharChamado($chamado_id) {
    $query = "
      SELECT
        cha_id AS chamado_id
      FROM
        tab_chamados
      WHERE
        cha_id = $chamado_id AND
        chs_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
      $dados_chamado = $chamados->buscaDadosChamado($dados["chamado_id"]);
      /// Registra Andamento = 100%
      if ($dados_chamado["andamento"] != 100) {
        $chamados->registraEtapa($dados_chamado, 15, array("andamento" => "100"), "NULL");
      }
      // Registra Comentário
      $chamados->registraEtapa($dados_chamado, 5, array("conteudo" => "Pesquisa aplicada"), "NULL");
      // Registra Etapa Fechamento
      $chamados->registraEtapa($dados_chamado, 6, null, "NULL");
      // Envia Acompanhamento
      $usuarios = $chamados->buscaUsuariosAcompanhamentos($dados_chamado["id"]);
      if ($usuarios) {
        $chamados->enviaAcompanhamentos($dados_chamado, array("Fechamento", "Comentário (Interno)"), $usuarios);
      }
    }
  }

  function buscaPesquisas() {
  	$query = "
  	  SELECT
  	    pes_id AS id,
  	    pes_descricao AS descricao,
  	    pes_texto_inicial AS texto_inicial,
  	    pes_texto_final AS texto_final,
  	    DATE_FORMAT(pes_data_inicio, '%d/%m/%Y') AS inicio
  	  FROM
  	    tab_pesquisas
  	  WHERE
  	    pet_id = 2
  	  ORDER BY
  	    pes_descricao";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$pesquisas[] = $dados;
    }
  	return $pesquisas;
  }

  #####################
  ### Gera Listagem ###
  #####################

  function geraListagemPesquisas($dados) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        $dados[$i]["radiobox"] = "<input type=\"radio\" id=\"pesquisa_{$dados[$i]["id"]}\" name=\"pesquisa\" value=\"{$dados[$i]["id"]}\"".(count($dados) == 1 ? " checked" : "").">";
        $dados[$i]["descricao"] = "<label for=\"pesquisa_{$dados[$i]["id"]}\">{$dados[$i]["descricao"]}</label>";
      }
    }
    $cabecalho = array ("radiobox" => "", "descricao" => "Descrição", "inicio" => "Início");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(20, 0, 70), null, array("center", "left", "center"));
    return $listagem->conteudo;
  }

  function geraListagemQuestoes($dados) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        $dados[$i]["numero"] = sprintf("%02s", $dados[$i]["numero"]);
      }
    }
    $cabecalho = array ("numero" => "#", "conteudo" => "Conteúdo");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(2), null, array("right", "left"));
    return $listagem->conteudo;
  }

  #############
  ### Geral ###
  #############

  function geraSelect($itens, $selecionado = null, $item_nulo = false) {
    if ($item_nulo) $select = "<option></option>";
    foreach ($itens as $valor => $item) {
      $select .= "<option value=\"$valor\"".(isset($selecionado) && $valor == $selecionado ? " selected" : "").">$item</option>";
    }
    return $select;
  }

  function formataData($data) {
    return "'".substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2)."'";
  }

  function ehCompletavelDados() {
  	return $this->permissoes["pesquisas_aplicacoes"]["completar_dados"];
  }

  function ehRemovivel() {
  	return $this->permissoes["pesquisas_aplicacoes"]["remover"];
  }

  function ehAplicavel() {
  	if (!$this->permissoes["pesquisas_aplicacoes"]["aplicar"]) return false;
  	if ($this->dados_item["respostas"] < $this->dados_item["questoes"]) return true;
  	if ($this->permissoes["pesquisas_aplicacoes"]["aplicar_plus"]) return true;
  	return false;
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    unset($this->conteudo);
    $this->conteudo .= "<script type=\"text/javascript\">if (!window.opener.closed && window.opener.buscaPesquisasAplicacoes) window.opener.buscaPesquisasAplicacoes({$this->dados_item["cliente_id"]})</script>";
    $this->conteudo .= "<script type=\"text/javascript\">window.close()</script>";
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("pesquisas_aplicacoes.adicionar.inc.php");
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);
      // Ações
      $acoes  = '<td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[listagem_pesquisas]', $this->geraListagemPesquisas($this->buscaPesquisas()), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
    	$query = "INSERT INTO tab_pesquisas_aplicacoes (pea_cadastro, cli_id, pes_id) VALUES (NOW(), {$_GET["i"]}, {$_POST["pesquisa"]})";
    	$this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
			// Registra Atividade
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("pesquisas_aplicacoes.ver.inc.php");
      // Ações
      $acoes[] = '<td bgcolor="#505050" width="50%"><strong>&nbsp;Dados Aplicação&nbsp;</strong></td>';
      if ($this->permissoes[$this->dados_modulo["nome"]]["questoes"]) $acoes[] = '<td bgcolor="#828282" width="50%"><strong>&nbsp;<a href="?m='.$this->dados_modulo["nome"].'&a=questoes&i='.$this->dados_item["id"].'" class="txtBranco11">Questões</a>&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', implode($acoes), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
      $this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace('[telefones]', $this->dados_item["cliente_telefones"], $this->conteudo);
      $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
      $this->conteudo = str_replace('[aplicador]', $this->dados_item["aplicador_nome"], $this->conteudo);
      $this->conteudo = str_replace('[texto_inicial]', $this->dados_item["texto_inicial"] ? str_replace("\n", "<br>", $this->dados_item["texto_inicial"]) : "Nenhuma instrução disponível", $this->conteudo);
      $this->conteudo = str_replace('[cadastro]', $this->dados_item["cadastro"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[andamento]', $this->dados_item["andamento"], $this->conteudo);
      // Botões
      if ($this->ehAplicavel()) {
      	if ($this->ehCompletavelDados()) {
      	  $botoes[] = "<input type=\"button\" name=\"completar_dados\" class=\"buttonAzulEscuroBranco11\" value=\"Iniciar\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=completar_dados&i={$this->dados_item["id"]}';\">";
      	} else {
      	  $botoes[] = "<input type=\"button\" name=\"aplicar\" class=\"buttonAzulEscuroBranco11\" value=\"Iniciar\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=aplicar&i={$this->dados_item["id"]}';\">";
      	}
      }
    	if ($this->ehRemovivel()) {
        $botoes[] = "<input type=\"button\" name=\"remover\" class=\"buttonAzulEscuroBranco11\" value=\"Remover\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}';\">";
    	}
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      $this->fechar();
    }
  }

  ###########
  ### Ver ###
  ###########

  function questoes() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("pesquisas_aplicacoes.questoes.inc.php");
      // Ações
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) $acoes[] = '<td bgcolor="#828282" width="50%"><strong>&nbsp;<a href="?m='.$this->dados_modulo["nome"].'&a=ver&i='.$this->dados_item["id"].'" class="txtBranco11">Dados Aplicação</a>&nbsp;</strong></td>';
      $acoes[] = '<td bgcolor="#505050" width="50%"><strong>&nbsp;Questões&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', implode($acoes), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace('[telefones]', $this->dados_item["cliente_telefones"], $this->conteudo);
      $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
      $this->conteudo = str_replace('[aplicador]', $this->dados_item["aplicador_nome"], $this->conteudo);
      $this->conteudo = str_replace('[questoes]', $this->geraListagemQuestoes($this->buscaQuestoes($this->dados_item["pesquisa_id"], $this->dados_item["id"], $this->dados_item["questoes"])), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      $this->fechar();
    }
  }

  #######################
  ### Completar Dados ###
  #######################

  function completar_dados() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $clientes_contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
      $clientes_emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, $this->permissoes, false);
      if (!$_POST) {
    		// Gera Conteúdo
        $this->conteudo .= $this->carregaArquivo("pesquisas_aplicacoes.completar_dados.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);
        // Ações
        $acoes  = '<td bgcolor="#505050"><strong>&nbsp;Completar/Confirmar Dados&nbsp;</strong></td>';
        $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
        // Dados
        $this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
        // Contatos
        $this->conteudo = str_replace("[listagem_telefones]", $clientes_contatos->geraContatosTelefonesListagem($clientes_contatos->buscaContatosTelefones($this->dados_item["cliente_id"]), true, $this->dados_item["cliente_status_id"] == 2), $this->conteudo);
        $this->conteudo = str_replace("[lista_telefones]", @serialize($clientes_contatos->buscaContatosTelefones($this->dados_item["cliente_id"])), $this->conteudo);
        $this->conteudo = str_replace("[listagem_emails]", $clientes_contatos->geraContatosEmailsListagem($clientes_contatos->buscaContatosEmails($this->dados_item["cliente_id"]), true, $this->dados_item["cliente_status_id"] == 2), $this->conteudo);
        $this->conteudo = str_replace("[lista_emails]", @serialize($clientes_contatos->buscaContatosEmails($this->dados_item["cliente_id"])), $this->conteudo);
        // Emails
        if (($botao_adicionar = $clientes_emails->geraClientesEmailsClienteAdicionar($this->dados_item["cliente_id"])) != "Nenhuma conta disponível") {
          $listagem = $clientes_emails->geraClientesEmailsClienteListagem($this->dados_item["cliente_id"]);
          $this->conteudo = str_replace("[emails_adicionar]", $botao_adicionar, $this->conteudo);
          $this->conteudo = str_replace("[emails_listagem]", $listagem->conteudo, $this->conteudo);
        } else {
          $this->conteudo = preg_replace('@\[clientes_emails\].*?\[/clientes_emails\]@si', "", $this->conteudo);
        }
        // Botões
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = preg_replace("/\\[[^\\]]*\\]/", "", $this->conteudo);
      } else {
        // Atualiza Contatos
        $clientes_contatos->atualizaContatoTelefones($this->dados_item["cliente_id"], @unserialize(stripslashes($_POST["lista_telefones"])));
        $clientes_contatos->atualizaContatoEmails($this->dados_item["cliente_id"], @unserialize(stripslashes($_POST["lista_emails"])));
        // Rediciona Aplicação Questionário
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=aplicar&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ###############
  ### Aplicar ###
  ###############

  function aplicar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehAplicavel()) {
  		$_SESSION["respostas"] = null;
  		// Atualiza Data Início
  		if (!$this->dados_item["inicio"]) {
  			$query = "UPDATE tab_pesquisas_aplicacoes SET pea_inicio = NOW() WHERE pea_id = {$this->dados_item["id"]}";
	      $this->bancodedados->query($query);
  		}
  		// Gera Conteúdo
      $this->conteudo .= $this->carregaArquivo("pesquisas_aplicacoes.aplicar.inc.php");
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);
      // Ações
      $acoes  = '<td bgcolor="#505050"><strong>&nbsp;Aplicar&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      // Dados
      $this->buscaDadosQuestao($this->dados_item["pesquisa_id"], $this->dados_item["id"]);
      $this->conteudo = str_replace('[pesquisa_id]', $this->dados_item["pesquisa_id"], $this->conteudo);
      $this->conteudo = str_replace('[pesquisa_aplicacao_id]', $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace('[telefones]', $this->dados_item["cliente_telefones"], $this->conteudo);
      $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
      $this->conteudo = str_replace('[andamento]', "1 / {$this->dados_item["questoes"]}", $this->conteudo);
      $this->conteudo = str_replace('[questao_total]', $this->dados_item["questoes"], $this->conteudo);
      $this->conteudo = str_replace('[maximo_alternativas]', $_SESSION["respostas"][$this->dados_item["id"]][1]["maximo_alternativas"], $this->conteudo);
      $this->conteudo = str_replace('[numero_alternativas]', count($_SESSION["respostas"][$this->dados_item["id"]][1]["alternativas"]), $this->conteudo);
      $this->conteudo = str_replace('[questao]', $this->geraQuestao($this->dados_item["pesquisa_id"], $this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[resposta]', $this->geraResposta($this->dados_item["pesquisa_id"], $this->dados_item["id"]), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:80px" value="Fechar" onClick="window.close()">';
      $botoes[] = '<input type="button" name="voltar" id="voltar" class="buttonAzulEscuroBranco11" style="width:80px" value="<< Voltar" onClick="aplicacaoVoltar()">';
      $botoes[] = '<input type="button" name="avancar" id="avancar" class="buttonAzulEscuroBranco11" style="width:80px" value="Avançar >>" onClick="aplicacaoAvancar()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      if ($this->dados_item) {
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      } else {
        $this->fechar();
      }
    }
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("pesquisas_aplicacoes.remover.inc.php");
        // Ações
        $acoes .= '<td bgcolor="#505050"><strong>&nbsp;Remover&nbsp;</strong></td>';
        $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
        // Conteúdo
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
      } else {
        // Remove Respostas
        $query = "DELETE FROM tab_pesquisas_aplicacoes_respostas WHERE pea_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Remove Aplicação
        $query = "DELETE FROM tab_pesquisas_aplicacoes WHERE pea_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Fecha Janela
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### RDS ###
  ###########

  function buscaPesquisasAplicacoes($cliente_id) {
  	$query = "
  	  SELECT
  	  	tab_pesquisas_aplicacoes.pea_id AS id,
  	    DATE_FORMAT(tab_pesquisas_aplicacoes.pea_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
  	    DATE_FORMAT(tab_pesquisas_aplicacoes.pea_termino, '%d/%m/%Y (%H:%i)') AS termino,
  	    CONCAT(COUNT(DISTINCT tab_pesquisas_aplicacoes_respostas.peq_id), ' / ', COUNT(DISTINCT tab_pesquisas_questoes.peq_id)) AS andamento,
  	    tab_pesquisas.pes_descricao AS descricao
  	  FROM
  	    tab_pesquisas_aplicacoes
  	    INNER JOIN tab_pesquisas ON (tab_pesquisas_aplicacoes.pes_id = tab_pesquisas.pes_id)
  	    LEFT OUTER JOIN tab_pesquisas_questoes ON (tab_pesquisas.pes_id = tab_pesquisas_questoes.pes_id)
  	    LEFT OUTER JOIN tab_pesquisas_aplicacoes_respostas ON (tab_pesquisas_aplicacoes.pea_id = tab_pesquisas_aplicacoes_respostas.pea_id)
  	  WHERE
  	  	tab_pesquisas_aplicacoes.cli_id = $cliente_id
  		GROUP BY
  		  tab_pesquisas_aplicacoes.pea_id
  		ORDER BY
  		  tab_pesquisas_aplicacoes.pea_inicio";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($this->permissoes["pesquisas_aplicacoes"]["ver"]) {
        $dados["link"] = "javascript:verPesquisasAplicacoes({$dados["id"]})";
    	}
    	if ($dados["inicio"]) $dados["aplicacao"][] = $dados["inicio"];
    	if ($dados["termino"]) $dados["aplicacao"][] = $dados["termino"];
    	$dados["aplicacao"] = $dados["aplicacao"] ? implode("<br>", $dados["aplicacao"]) : "";
    	$questoes[] = $dados;
    }
    return $questoes;
  }

  function geraListagemPesquisasAplicacoes($dados) {
    $cabecalho = array ("descricao" => "Descrição", "aplicacao" => "Aplicação", "andamento" => "Andamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("0", "100", "100"), null, array("left", "center", "center"));
    return $listagem->conteudo;
  }

}

?>
