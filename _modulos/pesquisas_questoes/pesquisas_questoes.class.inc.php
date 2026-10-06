<?

class pesquisas_questoes {

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
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
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

  function buscaDadosItem($questao_id) {
    $query = "
      SELECT
        tab_pesquisas_questoes.peq_id AS id,
        tab_pesquisas_questoes.peq_numero AS numero,
        tab_pesquisas_questoes.peq_conteudo AS conteudo,
        tab_pesquisas_questoes.peq_minimo AS minimo,
        tab_pesquisas_questoes.peq_maximo AS maximo,
        tab_pesquisas_questoes.peq_maximo_alternativas AS maximo_alternativas,
        tab_pesquisas_questoes.pes_id AS pesquisa_id,
        tab_pesquisas_variaveis_tipos.pvt_id AS variavel_tipo_id,
        tab_pesquisas_variaveis_tipos.pvt_nome AS variavel_tipo_nome,
        tab_pesquisas_questoes_tipos.pqt_id AS questao_tipo_id,
        tab_pesquisas_questoes_tipos.pqt_nome AS questao_tipo_nome
      FROM
        tab_pesquisas_questoes
        INNER JOIN tab_pesquisas_questoes_tipos ON (tab_pesquisas_questoes.pqt_id = tab_pesquisas_questoes_tipos.pqt_id)
        INNER JOIN tab_pesquisas_variaveis_tipos ON (tab_pesquisas_questoes.pvt_id = tab_pesquisas_variaveis_tipos.pvt_id)
      WHERE
        tab_pesquisas_questoes.peq_id = $questao_id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    $this->dados_item["alternativas"] = $this->buscaAlternativas($questao_id, $this->dados_item["variavel_tipo_id"]);
    	if ($this->dados_item["variavel_tipo_id"] == 3) {
    		$this->dados_item["minimo"] = intval($this->dados_item["minimo"], 10);
    		$this->dados_item["maximo"] = intval($this->dados_item["maximo"], 10);
    	} elseif ($this->dados_item["variavel_tipo_id"] == 4) {
    		$this->dados_item["minimo"] = number_format($this->dados_item["minimo"], 2, ",", ".");
    		$this->dados_item["maximo"] = number_format($this->dados_item["maximo"], 2, ",", ".");
    	} else {
    		$this->dados_item["minimo"] = "";
    		$this->dados_item["maximo"] = "";
    	}
  }

  function buscaQuestoesTipos() {
  	$query = "
  		SELECT
  		  tab_pesquisas_questoes_tipos.pqt_id AS id,
  		  tab_pesquisas_questoes_tipos.pqt_nome AS nome
  		FROM
  		  tab_pesquisas_questoes_tipos
  		ORDER BY
  		  tab_pesquisas_questoes_tipos.pqt_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaVariaveisTipos() {
  	$query = "
  		SELECT
  		  tab_pesquisas_variaveis_tipos.pvt_id AS id,
  		  tab_pesquisas_variaveis_tipos.pvt_nome AS nome
  		FROM
  		  tab_pesquisas_variaveis_tipos
  		ORDER BY
  		  tab_pesquisas_variaveis_tipos.pvt_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function renumerarQuestoes($pesquisa_id, $questao_id) {
  	$query = "
  		SELECT
  		  peq_id AS id
  		FROM
  		  tab_pesquisas_questoes
  		WHERE
  		  pes_id = $pesquisa_id
  		ORDER BY
  		  peq_numero".($questao_id ? ",
  		  IF(peq_id = $questao_id, 0, 1)" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArrayTotal($query);
    for ($i = 0; $i < count($dados); $i++) {
    	$query = "UPDATE tab_pesquisas_questoes SET peq_numero = $i + 1 WHERE peq_id = {$dados[$i]["id"]}";
    	$this->bancodedados->query($query);
    }
  }

  function buscaAlternativas($questao_id, $variavel_tipo_id) {
  	$query = "
  		SELECT
  		  pqa_id AS id,
  		  pqa_ordem AS ordem,
  		  pqa_conteudo AS conteudo,
  		  pqa_minimo AS minimo,
  		  pqa_maximo AS maximo,
  		  pqa_eliminar AS eliminar
  		FROM
  		  tab_pesquisas_questoes_alternativas
  		WHERE
  		  peq_id = $questao_id
  		ORDER BY
  		  pqa_ordem";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($variavel_tipo_id == 3) {
    		$dados["minimo"] = intval($dados["minimo"], 10);
    		$dados["maximo"] = intval($dados["maximo"], 10);
    	} elseif ($variavel_tipo_id == 4) {
    		$dados["minimo"] = number_format($dados["minimo"], 2, ",", ".");
    		$dados["maximo"] = number_format($dados["maximo"], 2, ",", ".");
    	}
    	$alternativas[] = $dados;
    }
    return $alternativas;
  }

	function ordena_multiarray($array, $campos) {
		if (is_array($array) && sizeof($array)) {
			$tamanho_chave = 0;
			reset($array);
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				$tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
				unset($chave);
				next($array);
			}
			reset($array);
			$array_ordenacao = array();
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
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

	function reordenaAlternativas($alternativas) {
		$alternativas = $this->ordena_multiarray($alternativas, array("ordem"));
		for ($i = 0; $i < count($alternativas); $i++) {
			$alternativas[$i]["ordem"] = $i + 1;
		}
		return $alternativas;
	}

  ###################
  ### Busca Itens ###
  ###################

  function buscaQuestoes($pesquisa_id) {
  	$query = "
  		SELECT
  		  tab_pesquisas_questoes.peq_id AS id,
  		  tab_pesquisas_questoes.peq_numero AS numero,
  		  tab_pesquisas_questoes.peq_conteudo AS conteudo,
  		  tab_pesquisas_questoes_tipos.pqt_id AS tipo_id,
  		  tab_pesquisas_questoes_tipos.pqt_nome AS tipo_nome,
  		  tab_pesquisas_variaveis_tipos.pvt_id AS variavel_tipo_id,
  		  tab_pesquisas_variaveis_tipos.pvt_nome AS variavel_tipo
  		FROM
  		  tab_pesquisas_questoes
  		  INNER JOIN tab_pesquisas_questoes_tipos ON (tab_pesquisas_questoes.pqt_id = tab_pesquisas_questoes_tipos.pqt_id)
  		  INNER JOIN tab_pesquisas_variaveis_tipos ON (tab_pesquisas_questoes.pvt_id = tab_pesquisas_variaveis_tipos.pvt_id)
  		WHERE
  		  tab_pesquisas_questoes.pes_id = $pesquisa_id
  		ORDER BY
  		  tab_pesquisas_questoes.peq_numero";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($this->permissoes["pesquisas_questoes"]["ver"]) {
        $dados["link"] = "javascript:verQuestao({$dados["id"]})";
    	}
    	$dados["tipo_nome"] = str_replace(" (", "<br>(", $dados["tipo_nome"]);
    	$dados["variavel_tipo"] = str_replace(" (", "<br>(", $dados["variavel_tipo"]);
    	$questoes[] = $dados;
    }
    return $questoes;
  }

  #####################
  ### Gera Listagem ###
  #####################

  function geraListagemQuestoes($dados, $somente_leitura = false) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        $dados[$i]["numero"] = sprintf("%0".strlen(count($dados))."s", $dados[$i]["numero"]);
        if ($somente_leitura) {
          $dados[$i]["link"] = null;
        }
      }
    }
    $cabecalho = array ("numero" => "#", "conteudo" => "Conteúdo", "tipo_nome" => "Tipo", "variavel_tipo" => "Variável");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(20, 0, 80, 80), null, array("right", "left"));
    return $listagem->conteudo;
  }

  function geraListagemAlternativas($tipo_id = null, $variavel_tipo_id = null, $dados = null, $editavel = true) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        if ($editavel && $tipo_id > 1) {
          $dados[$i]["link"] = "javascript:selecionarAlternativa(this, event, ".($i + 1).")";
          $dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerAlternativa('.($i + 1).')">';
        }
        $dados[$i]["ordem"] = sprintf("%02s", $dados[$i]["ordem"]);
        if ($variavel_tipo_id < 3) {
        	$dados[$i]["minimo"] = "-";
        	$dados[$i]["maximo"] = "-";
        }
      }
    } else {
      $dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    if ($editavel && $tipo_id > 1) {
      $dados = funcoes::mescla_arrays(array($this->buscaAlternativasAdicionar($variavel_tipo_id >= 3)), $dados);
      $cabecalho = array("ordem" => "#", "conteudo" => "Conteúdo", "minimo" => "Mínimo", "maximo" => "Máximo", "eliminar" => "Eliminar", "botoes" => "");
      $larguras = null;
    } else {
      $cabecalho = array("ordem" => "#", "conteudo" => "Conteúdo", "minimo" => "Mínimo", "maximo" => "Máximo", "eliminar" => "Eliminar");
      $larguras = array(40, 0, 100, 100);
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras, null, array("center", "left"));
    return $listagem->conteudo;
  }

  function buscaAlternativasAdicionar($com_limites = false) {
    $adicionar["ordem"] = '<input type="text" id="alternativa_ordem" class="inputBrancoAzulEscuro11" style="width:40px" onKeyPress="return formataNumero(this, event)" onblur="return formataNumero(this, event)">';
    $adicionar["conteudo"] = '<input type="text" id="alternativa_conteudo" class="inputBrancoAzulEscuro11" style="width:335px">';
    if ($com_limites) {
	    $adicionar["minimo"] = '<input type="text" id="alternativa_minimo" class="inputBrancoAzulEscuro11" style="width:50px" onKeyPress="return formataAlternativaLimite(this, event)" onblur="return formataAlternativaLimite(this, event)">';
	    $adicionar["maximo"] = '<input type="text" id="alternativa_maximo" class="inputBrancoAzulEscuro11" style="width:50px" onKeyPress="return formataAlternativaLimite(this, event)" onblur="return formataAlternativaLimite(this, event)">';
    } else {
	    $adicionar["minimo"] = '<div style="width:50px">-</div>';
	    $adicionar["maximo"] = '<div style="width:50px">-</div>';
    }
    $adicionar["eliminar"] = '<input type="text" id="alternativa_eliminar" class="inputBrancoAzulEscuro11" style="width:50px">';
    $adicionar["botoes"] = '<input type="button" id="adicionar_alternativa" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarAlternativa()">';
    return $adicionar;
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

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    unset($this->conteudo);
    $this->conteudo .= "<script type=\"text/javascript\">if (!window.opener.closed && window.opener.buscaQuestoes) window.opener.buscaQuestoes({$this->dados_item["pesquisa_id"]})</script>";
    $this->conteudo .= "<script type=\"text/javascript\">window.close()</script>";
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("pesquisas_questoes.adicionar.inc.php");
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);
      // Ações
      $acoes  = '<td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[tipos]', $this->geraSelect($this->buscaQuestoesTipos()), $this->conteudo);
      $this->conteudo = str_replace('[variavel]', $this->geraSelect($this->buscaVariaveisTipos()), $this->conteudo);
      $this->conteudo = str_replace('[listagem_alternativas]', $this->geraListagemAlternativas(), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
    	$_POST["numero"] = intval($_POST["numero"]);
    	$_POST["conteudo"] = trim($_POST["conteudo"]);
    	$_POST["conteudo"][0] = strtoupper($_POST["conteudo"][0]);
  		if ($_POST["variavel"] <= 2) {
  			$_POST["minimo"] = "NULL";
  			$_POST["maximo"] = "NULL";
  		} else {
  			$_POST["minimo"] = floatval(str_replace(",", ".", $_POST["minimo"]));
  			$_POST["maximo"] = floatval(str_replace(",", ".", $_POST["maximo"]));
  		}
  		if ($_POST["tipo"] == 1) {
  			$_POST["maximo_alternativas"] = "NULL";
  		} else {
    		$_POST["maximo_alternativas"] = intval($_POST["maximo_alternativas"]);
  		}
	    $query = "INSERT INTO tab_pesquisas_questoes (peq_numero, peq_conteudo, peq_minimo, peq_maximo, peq_maximo_alternativas, pes_id, pvt_id, pqt_id) VALUES ({$_POST["numero"]}, '{$_POST["conteudo"]}', {$_POST["minimo"]}, {$_POST["maximo"]}, {$_POST["maximo_alternativas"]}, {$_GET["i"]}, {$_POST["variavel"]}, {$_POST["tipo"]})";
    	$this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      // Renumera Questões
      $this->renumerarQuestoes($_GET["i"], $id);
      // Registra Alternativas
      $alternativas = unserialize(stripslashes($_POST["lista_alternativas"]));
      if ($alternativas) {
      	foreach ($alternativas as $alternativa) {
      		if ($_POST["variavel"] <= 2) {
      			$alternativa["minimo"] = "NULL";
      			$alternativa["maximo"] = "NULL";
      		} else {
      			$alternativa["minimo"] = str_replace(",", ".", $alternativa["minimo"]);
      			$alternativa["maximo"] = str_replace(",", ".", $alternativa["maximo"]);
      		}
      		$alternativa["eliminar"] = $alternativa["eliminar"] ? "'{$alternativa["eliminar"]}'" : "NULL";
    			$query = "INSERT INTO tab_pesquisas_questoes_alternativas (pqa_ordem, pqa_conteudo, pqa_minimo, pqa_maximo, pqa_eliminar, peq_id) VALUES ({$alternativa["ordem"]}, '{$alternativa["conteudo"]}', {$alternativa["minimo"]}, {$alternativa["maximo"]}, {$alternativa["eliminar"]}, $id)";
					$this->bancodedados->query($query);
      	}
      }
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
      $this->conteudo .= $this->carregaArquivo("pesquisas_questoes.ver.inc.php");
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}", $this->conteudo);
      // Ações
      $acoes  = '<td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
    	if (!$_POST) {
	      $this->conteudo .= $this->carregaArquivo("pesquisas_questoes.editar.inc.php");
	      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);
	      // Ações
	      $acoes  = '<td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td>';
	      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
	      // Dados
	      $this->conteudo = str_replace('[pesquisa_id]', $this->dados_item["pesquisa_id"], $this->conteudo);
	      $this->conteudo = str_replace('[numero]', $this->dados_item["numero"], $this->conteudo);
	      $this->conteudo = str_replace('[conteudo]', $this->dados_item["conteudo"], $this->conteudo);
	      $this->conteudo = str_replace('[minimo]', $this->dados_item["minimo"], $this->conteudo);
	      $this->conteudo = str_replace('[maximo]', $this->dados_item["maximo"], $this->conteudo);
	      $this->conteudo = str_replace('[tipos]', $this->geraSelect($this->buscaQuestoesTipos(), $this->dados_item["questao_tipo_id"]), $this->conteudo);
	      $this->conteudo = str_replace('[variavel]', $this->geraSelect($this->buscaVariaveisTipos(), $this->dados_item["variavel_tipo_id"]), $this->conteudo);
	      $this->conteudo = str_replace('[listagem_alternativas]', $this->geraListagemAlternativas($this->dados_item["questao_tipo_id"], $this->dados_item["variavel_tipo_id"], $this->dados_item["alternativas"], true), $this->conteudo);
	      $this->conteudo = str_replace('[lista_alternativas]', @serialize($this->dados_item["alternativas"]), $this->conteudo);
	      $this->conteudo = str_replace('[maximo_alternativas]', $this->dados_item["maximo_alternativas"], $this->conteudo);
	      // Botões
			 	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = "<input type=\"button\" name=\"remover\" class=\"buttonAzulEscuroBranco11\" value=\"Remover\" style=\"width:65\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}';\">";
	      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
	      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
	      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
	    } else {
	    	// Atualiza Dados
	    	$_POST["numero"] = intval($_POST["numero"]);
	    	$_POST["conteudo"] = trim($_POST["conteudo"]);
    		$_POST["conteudo"][0] = strtoupper($_POST["conteudo"][0]);
	    	$_POST["maximo_alternativas"] = intval($_POST["maximo_alternativas"]);
	  		if ($_POST["variavel"] <= 2) {
	  			$_POST["minimo"] = "NULL";
	  			$_POST["maximo"] = "NULL";
	  		} else {
	  			$_POST["minimo"] = str_replace(",", ".", $_POST["minimo"]);
	  			$_POST["maximo"] = str_replace(",", ".", $_POST["maximo"]);
	  		}
	    	$query = "UPDATE tab_pesquisas_questoes SET peq_numero = {$_POST["numero"]}, peq_conteudo = '{$_POST["conteudo"]}', peq_minimo = {$_POST["minimo"]}, peq_maximo = {$_POST["maximo"]}, peq_maximo_alternativas = {$_POST["maximo_alternativas"]}, pvt_id = {$_POST["variavel"]}, pqt_id = {$_POST["tipo"]} WHERE peq_id = {$_GET["i"]}";
	    	$this->bancodedados->query($query);
	      // Atualiza Alternativas
	      $alternativas = unserialize(stripslashes($_POST["lista_alternativas"]));
        if ($alternativas) {
        	foreach ($alternativas as $alternativa) {
	      		if ($_POST["variavel"] <= 2) {
	      			$alternativa["minimo"] = "NULL";
	      			$alternativa["maximo"] = "NULL";
	      		} else {
	      			$alternativa["minimo"] = str_replace(",", ".", $alternativa["minimo"]);
	      			$alternativa["maximo"] = str_replace(",", ".", $alternativa["maximo"]);
	      		}
	      		$alternativa["eliminar"] = $alternativa["eliminar"] ? "'{$alternativa["eliminar"]}'" : "NULL";
        		if ($alternativa["id"]) {
        			$alternativas_ids[] = $alternativa["id"];
        			$query = "UPDATE tab_pesquisas_questoes_alternativas SET pqa_ordem = {$alternativa["ordem"]}, pqa_conteudo = '{$alternativa["conteudo"]}', pqa_minimo = {$alternativa["minimo"]}, pqa_maximo = {$alternativa["maximo"]}, pqa_eliminar = {$alternativa["eliminar"]} WHERE pqa_id = {$alternativa["id"]}";
    					$this->bancodedados->query($query);
        		} else {
        			$query = "INSERT INTO tab_pesquisas_questoes_alternativas (pqa_ordem, pqa_conteudo, pqa_minimo, pqa_maximo, pqa_eliminar, peq_id) VALUES ({$alternativa["ordem"]}, '{$alternativa["conteudo"]}', {$alternativa["minimo"]}, {$alternativa["maximo"]}, {$alternativa["eliminar"]}, {$_GET["i"]})";
    					$this->bancodedados->query($query);
        		}
        	}
        	foreach ($this->dados_item["alternativas"] as $alternativa) {
        		if (!in_array($alternativa["id"], $alternativas_ids)) {
        			$query = "DELETE FROM tab_pesquisas_questoes_alternativas WHERE pqa_id = {$alternativa["id"]}";
    					$this->bancodedados->query($query);
        		}
        	}
        }
	      // Renumera Questões
	      if ($_POST["numero"] != $this->dados_item["numero"]) {
		      $query = "UPDATE tab_pesquisas_questoes SET peq_numero = {$this->dados_item["numero"]} WHERE peq_id != {$this->dados_item["id"]} AND pes_id = {$this->dados_item["pesquisa_id"]} AND peq_numero = {$_POST["numero"]}";
		    	$this->bancodedados->query($query);
		      $this->renumerarQuestoes($this->dados_item["pesquisa_id"], $this->dados_item["id"]);
	      }
				// Registra Atividade
	      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
	      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
	      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
	    }
    } else {
      $this->fechar();
    }
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
   $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("pesquisas_questoes.remover.inc.php");
  			$acoes  = '<td bgcolor="#505050"><strong>&nbsp;Remover&nbsp;</strong></td>';
      	$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
      	// Remove Alternativas
	      $query = "DELETE FROM tab_pesquisas_questoes_alternativas WHERE peq_id = {$_GET["i"]}";
	      $this->bancodedados->query($query);
      	// Remove Questão
	      $query = "DELETE FROM tab_pesquisas_questoes WHERE peq_id = {$_GET["i"]}";
	      $this->bancodedados->query($query);
	      // Renumera Questões
	      $this->renumerarQuestoes($this->dados_item["pesquisa_id"]);
	      // Fecha Janela
	      $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

}

?>
