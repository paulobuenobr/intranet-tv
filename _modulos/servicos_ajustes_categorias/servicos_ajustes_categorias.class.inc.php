<?

require_once("_global/modulo.class.inc.php");

class servicos_ajustes_categorias extends modulo {

    function __construct ($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

  function buscaDadosItem($id) {
  	$query = "
  		SELECT
  			sac_id AS id,
  			sac_nome AS nome,
  			sac_regras AS regras,
  			sac_duracao AS duracao,
  			sac_valor AS valor,
  			sac_editavel AS editavel,
  			sac_sistema AS sistema,
  			sac_status AS status
  		FROM
  			tab_servicos_ajustes_categorias
  		WHERE
  			sac_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
   	if ($dados) {
   		$dados["regras"] = unserialize($dados["regras"]);
   		$this->dados_item = $dados;
   	} else {
   		unset($this->dados_item);
   	}
  }

  function buscaServicos() {
  	$query = "
  		SELECT
  			sat_id as id,
  			sat_nome as nome
  		FROM
  			tab_servicos_ajustes_tipos
  		ORDER BY
  			nome";
    $query = $this->bancodedados->query($query);
 		$servicos[0] = "Todos";
   	while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	   	$servicos[$dados["id"]] = $dados["nome"];
   	}
   	return $servicos;
  }

  function buscaAjusteTipos() {
  	$dias[1] = "Porcentagem";
  	$dias[2] = "Valor";
  	return $dias;
  }

  function buscaDiasSemana() {
  	$dias[0] = "Todos";
  	$dias[7] = "Domingo";
  	$dias[1] = "Segunda-feira";
  	$dias[2] = "Terça-feira";
  	$dias[3] = "Quarta-feira";
  	$dias[4] = "Quinta-feira";
  	$dias[5] = "Sexta-feira";
  	$dias[6] = "Sábado";
  	return $dias;
  }

  function buscaEditavel($padrao = null) {
  	$itens[1] = "Sim";
  	$itens[0] = "Não";
  	return $padrao === null ? $itens : $itens[$padrao];
  }

  function buscaStatus($padrao = null) {
  	$itens[0] = "Inativo";
  	$itens[1] = "Ativo";
  	return $padrao === null ? $itens : $itens[$padrao];
  }

  function buscaAjustes() {
  	for ($i = 0; $i <= 300; $i += 25) {
  		$ajustes[$i] = $i."%";
  	}
  	return $ajustes;
  }

  function buscaHorarios() {
  	for ($h = 0; $h < 24; $h++) {
	  	for ($m = 0; $m < 60; $m += 15) {
	  		$horario = sprintf("%02s", $h).":".sprintf("%02s", $m);
	  		$horarios[$horario] = $horario;
  		}
  	}
  	$horarios["24:00"] = "24:00";
  	return $horarios;
  }

  ##############
  ### Regras ###
  ##############

  function buscaRegrasAdicionar() {
    $adicionar["servico"] = $this->geraSelect($this->buscaServicos(), null, true, "servico", "201px", "mudaServico(this.value)");
    $adicionar["ajuste_tipo"] = $this->geraSelect($this->buscaAjusteTipos(), null, true, "ajuste_tipo", "100px", "mudaAjusteTipo(this.value)", false);
    $adicionar["ajuste_valor"] = '<input type="text" name="ajuste_valor" id="ajuste_valor" class="inputBrancoAzulEscuro11" style="width: 60px" onKeyPress="return formataAjusteValor(this, event)" onblur="return formataAjusteValor(this, event)" disabled>';
    $adicionar["ajuste_unidade"] = '<select name="ajuste_unidade" id="ajuste_unidade" class="inputBrancoAzulEscuro11" style="width: 70px" disabled></select>';
    $adicionar["dia_semana"] = $this->geraSelect($this->buscaDiasSemana(), null, true, "dia_semana", "100px");
    $adicionar["hora_inicio"] = $this->geraSelect($this->buscaHorarios(), null, true, "hora_inicio", "60px");
    $adicionar["hora_termino"] = $this->geraSelect($this->buscaHorarios(), null, true, "hora_termino", "60px");
    $adicionar["botoes"] = '<input type="button" name="adicionar_regra" id="adicionar_regra" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarRegra()">';
    return $adicionar;
  }

  function geraRegrasListagem($dados = null, $editavel = true) {
    if ($dados) {
      $servicos = $this->buscaServicos();
      $ajuste_tipos = $this->buscaAjusteTipos();
      $dias_semana = $this->buscaDiasSemana();
      for ($i = 0; $i < count($dados); $i++) {
        if ($editavel) {
          $dados[$i]["link"] = "javascript:selecionarRegra(this, event, ".($i + 1).")";
          $dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerRegra('.($i + 1).')">';
        }
        if ($dados[$i]["ajuste_tipo"] == 2) {
          $dados[$i]["ajuste_unidade"] = $this->buscaAjusteUnidade($dados[$i]["servico"], $dados[$i]["ajuste_unidade"]);
          $dados[$i]["ajuste_valor"] .= (!$editavel) ? " ".$dados[$i]["ajuste_unidade"] : "";
        } else {
          $dados[$i]["ajuste_valor"] .= "%";
        }
        $dados[$i]["servico"] = $servicos[$dados[$i]["servico"]];
        $dados[$i]["ajuste_tipo"] = $ajuste_tipos[$dados[$i]["ajuste_tipo"]];
        $dados[$i]["dia_semana"] = $dias_semana[$dados[$i]["dia_semana"]];
      }
    } else {
      $dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    if ($editavel) {
      $dados = funcoes::mescla_arrays(array($this->buscaRegrasAdicionar()), $dados);
      $cabecalho = array("servico" => "Serviço", "ajuste_tipo" => "Tipo", "ajuste_valor" => "Valor", "ajuste_unidade" => "Unidade", "dia_semana" => "Dia da Semana", "hora_inicio" => "Início", "hora_termino" => "Término", "botoes" => "");
      $larguras = null;
    } else {
      $cabecalho = array("servico" => "Serviço", "ajuste_valor" => "Ajuste", "dia_semana" => "Dia da Semana", "hora_inicio" => "Início", "hora_termino" => "Término");
      $larguras = array(0, 120, 120, 70, 70);
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras);
    return $listagem->conteudo;
  }

  function buscaAjusteUnidade($servico_id, $unidade_id) {
    $multiplicadores = array(0 => "", 1 => "K", 2 => "M", 3 => "G");
    $unidades = array(1 => "bps", 2 => "B", 4 => "B", 5 => "B / mês", 7 => "B", 8 => "B / mês", 9 => "bps");
    return $multiplicadores[$unidade_id].$unidades[$servico_id];
  }

	#################
	### Adicionar ###
	#################

	function adicionar() {
    if (empty($_POST)) {
      $this->conteudo .= $this->carregaArquivo("servicos_ajustes_categorias.adicionar.inc.php");
      // Dados
      $this->conteudo = str_replace('[listagem_regras]', $this->geraRegrasListagem(), $this->conteudo);
      $this->conteudo = str_replace('[editavel]', $this->geraSelect($this->buscaEditavel(), "1", true, "editavel", "100px"), $this->conteudo);
  		$this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), null, true, "status", "100px"), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=adicionar', $this->conteudo);
    } else {
    	$nome = $_POST["descricao"];
      $duracao = intval(preg_replace("/[^0-9]/", "", $_POST["duracao"]), 10);
      $duracao = $duracao ? $duracao : "NULL";
      $valor = intval(preg_replace("/[^0-9]/", "", $_POST["valor"]), 10);
      $valor = $valor ? $valor : "NULL";
			$editavel = intval($_POST["editavel"], 10);
			$status = intval($_POST["status"], 10);
    	$regras = stripslashes($_POST["lista_regras"]);
    	$query = "
    		INSERT INTO
    			tab_servicos_ajustes_categorias (sac_nome, sac_regras, sac_duracao, sac_valor, sac_editavel, sac_status)
    		VALUES
    			('$nome', '$regras', $duracao, $valor, $editavel, $status)";
    	$this->bancodedados->query($query);
  		$id = $this->bancodedados->ultimoIdInserido();
  	  $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$id);
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=$id");
    }
	}

	###########
	### Ver ###
	###########

	function ver() {
		$this->buscaDadosItem($_GET["i"]);
	  if ($this->dados_item) {
	    $this->conteudo .= $this->carregaArquivo("servicos_ajustes_categorias.ver.inc.php");
	    // Dados
	    $editavel = $this->buscaEditavel();
   		$this->conteudo = str_replace('[descricao]', $this->dados_item["nome"], $this->conteudo);
   		$this->conteudo = str_replace('[editavel]', $editavel[$this->dados_item["editavel"]], $this->conteudo);
      $this->conteudo = str_replace('[listagem_regras]', $this->geraRegrasListagem($this->dados_item["regras"], false), $this->conteudo);
	    // Botões
		 	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && !$this->dados_item["sistema"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
   		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
	   	$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
			// Ação
	   	$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$_GET["i"], $this->conteudo);
    } else {
	    header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
	}

	##############
	### Editar ###
	##############

	function editar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item["sistema"]) {
			$this->ver();
			return;
		}
	  if ($this->dados_item) {
	    if (!$_POST) {
		    $this->conteudo .= $this->carregaArquivo("servicos_ajustes_categorias.editar.inc.php");
		    // Dados
    		$this->conteudo = str_replace('[descricao]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[listagem_regras]', $this->geraRegrasListagem($this->dados_item["regras"]), $this->conteudo);
        $this->conteudo = str_replace('[lista_regras]', @serialize($this->dados_item["regras"]), $this->conteudo);
    		$this->conteudo = str_replace('[duracao]', $this->dados_item["duracao"], $this->conteudo);
    		$this->conteudo = str_replace('[valor]', $this->formataDinheiro($this->dados_item["valor"]), $this->conteudo);
    		$this->conteudo = str_replace('[editavel]', $this->geraSelect($this->buscaEditavel(), $this->dados_item["editavel"], true, "editavel", "100px"), $this->conteudo);
    		$this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $this->dados_item["status"], true, "status", "100px"), $this->conteudo);
		    // Botões
 			 	if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
		 	  $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
		   	$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
     		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
		   	$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
				// Ação
		   	$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$_GET["i"], $this->conteudo);
	    } else {
  			$nome = $_POST["descricao"];
        $duracao = intval(preg_replace("/[^0-9]/", "", $_POST["duracao"]), 10);
        $duracao = $duracao ? $duracao : "NULL";
        $valor = intval(preg_replace("/[^0-9]/", "", $_POST["valor"]), 10);
        $valor = $valor ? $valor : "NULL";
  			$editavel = intval($_POST["editavel"], 10);
  			$status = intval($_POST["status"], 10);
        $regras = stripslashes($_POST["lista_regras"]);
		  	$query = "UPDATE tab_servicos_ajustes_categorias SET sac_nome = '$nome', sac_duracao = $duracao, sac_valor = $valor, sac_regras = '$regras', sac_editavel = $editavel, sac_status = $status WHERE sac_id = {$_GET["i"]}";
		  	$this->bancodedados->query($query);
			  $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		    $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
		    header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
    } else {
	    header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
	}

	##############
	### Listar ###
	##############

	function listar() {
    if (!$_GET["f"] && !$_GET["p"]) {
      $_GET["f"]["status"] = 1; // Ativo
    }
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar\'">';
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
    // Nome
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_servicos_ajustes_categorias.sac_nome LIKE '%[valor]%')");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_statuss[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_statuss, "where" => "tab_servicos_ajustes_categorias.sac_status = [valor]");
    // Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
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
  		SELECT
  			sac_id AS id,
  			sac_nome AS nome,
  			sac_duracao AS duracao,
  			sac_valor AS valor,
  			sac_editavel AS editavel,
  			sac_status AS status
  		FROM
  			tab_servicos_ajustes_categorias
  		WHERE
        $filtro->parametros_sql
      ORDER BY
        nome ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
       	$dados["link"] = "?m=servicos_ajustes_categorias&a=ver&i={$dados["id"]}";
    	}
   		$dados["duracao"] = $dados["duracao"] ? "{$dados["duracao"]} dia(s)" : "";
   		$dados["valor"] = $dados["valor"] ? $this->formataDinheiro($dados["valor"]) : "";
   		$dados["editavel"] = $this->buscaEditavel($dados["editavel"]);
   		$dados["status"] = $this->buscaStatus($dados["status"]);
  		$itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array("nome" => "Nome", "duracao" => "Duração", "valor" => "Valor", "editavel" => "Editável", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("", "70", "70", "70", "70"), null, array("left", "right", "right"));
		return $listagem;
  }

	###############
	### Remover ###
	###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
			if (empty($_POST["confirmar"])) {
	    	$this->conteudo .= $this->carregaArquivo("servicos_ajustes_categorias.remover.inc.php");
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'&servidor_id='.$this->dados_item["servidor_id"], $this->conteudo);
			} else {
				$query = "DELETE FROM tab_servicos_ajustes_categorias WHERE sac_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
      	header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
    } else {
	    header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
  }

}

?>
