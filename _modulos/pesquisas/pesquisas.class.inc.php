<?

require_once("_global/modulo.class.inc.php");

class pesquisas extends modulo {

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

  function buscaDadosItem($pesquisa_id) {
    $query = "
      SELECT
        tab_pesquisas.pes_id AS id,
        tab_pesquisas.pes_descricao AS descricao,
        tab_pesquisas.pes_texto_inicial AS texto_inicial,
        tab_pesquisas.pes_texto_final AS texto_final,
        DATE_FORMAT(tab_pesquisas.pes_data_inicio, '%d/%m/%Y') AS inicio,
        DATE_FORMAT(tab_pesquisas.pes_data_termino, '%d/%m/%Y') AS termino,
        tab_pesquisas_status.pet_id AS status_id,
        tab_pesquisas_status.pet_nome AS status_nome
      FROM
        tab_pesquisas
        INNER JOIN tab_pesquisas_status ON (tab_pesquisas.pet_id = tab_pesquisas_status.pet_id)
      WHERE
        tab_pesquisas.pes_id = $pesquisa_id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaQuestoes($pesquisa_id) {
    $questoes = controle::instanciaObjeto("pesquisas_questoes", $this->bancodedados, $this->permissoes, false);
    return $questoes->buscaQuestoes($pesquisa_id);
  }

  function buscaDadosAplicacoes($pesquisa_id) {
    $query = "
      SELECT
        COUNT(pea_id) AS aplicacoes_geradas,
        SUM(IF(pea_termino IS NULL, 0, 1)) AS aplicacoes_respondidas,
        DATE_FORMAT(MIN(pea_inicio), '%d/%m/%Y') AS inicio,
        DATE_FORMAT(MAX(pea_termino), '%d/%m/%Y') AS termino
      FROM
        tab_pesquisas_aplicacoes
      WHERE
        tab_pesquisas_aplicacoes.pes_id = $pesquisa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["aplicacoes_geradas"] = intval($dados["aplicacoes_geradas"], 10);
      $dados["aplicacoes_respondidas"] = intval($dados["aplicacoes_respondidas"], 10);
    	return $dados;
    }
  }

  function geraRelatorios($pesquisa_id, $dados_aplicacoes) {
    $pesquisas_questoes = controle::instanciaObjeto("pesquisas_questoes", $this->bancodedados, $this->permissoes, false);
    $questoes = $pesquisas_questoes->buscaQuestoes($pesquisa_id);
    for ($i = 0; $i < count($questoes); $i++) {
      $itens = array($questoes[$i], array("mesclado" => $this->geraGrafico($questoes[$i], $dados_aplicacoes)));
      $relatorios[] = $pesquisas_questoes->geraListagemQuestoes($itens, true);
    }
    return implode("<div style=\"width:100%;height:5px\"></div>", $relatorios);
  }

  function geraGrafico($dados_questao, $dados_aplicacoes) {
    if (!$dados_aplicacoes["aplicacoes_respondidas"]) {
      return "<center>Nenhum dado disponível</center>";
    }
    if ($dados_questao["tipo_id"] > 1) {
      $conteudo[] = "<center><table cellspacing=\"0\" cellpadding=\"0\" style=\"background-color:#FFFFFF;margin:10px;padding:10px;border:1px solid #828282\">";
      $alternativas = $this->buscaAlternativas($dados_questao["id"]);
      if ($alternativas) {
        foreach ($alternativas as $alternativa) {
          $conteudo[] = "<tr>";
          $conteudo[] = "<td class=\"txtAzulEscuro11\" align=\"right\" height=\"20\" style=\"padding-top:5px;padding-bottom:5px;border-right:1px solid #505050\">{$alternativa["conteudo"]}&nbsp;</td>";
          $conteudo[] = "<td class=\"txtAzulEscuro11\" style=\"padding-top:5px;padding-bottom:5px\"><div style=\"background-color:#828282;border-right: solid 1px #505050;border-top: solid 1px #505050;border-bottom: solid 1px #505050;width:".(intval(300 * $alternativa["respostas"] / $dados_aplicacoes["aplicacoes_respondidas"], 10))."px;height:15px;float:left\" title=\"{$alternativa["respostas"]}\"></div>&nbsp;".round(100 * $alternativa["respostas"] / $dados_aplicacoes["aplicacoes_respondidas"], 0)."%</td>";
          $conteudo[] = "</tr>";
        }
      }
      $conteudo[] = "</table></center>";
    } elseif ($dados_questao["tipo_id"] < 3 && $dados_questao["variavel_tipo_id"] <= 2) {
    	$respostas_abertas = $this->bucaQuantidadeRespostasAbertas($dados_questao["id"]);
      if ($respostas_abertas) {
        $conteudo[] = "<div id=\"respostas_{$dados_questao["id"]}\"><center><a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:exibirRespostas({$dados_questao["id"]})\">Exibir respostas ($respostas_abertas)</a></center></div>";
      }
    }
    return implode($conteudo);
  }

  function buscaAlternativas($questao_id) {
  	$query = "
  		SELECT
  		  tab_pesquisas_questoes_alternativas.pqa_id AS id,
  		  tab_pesquisas_questoes_alternativas.pqa_ordem AS ordem,
  		  tab_pesquisas_questoes_alternativas.pqa_conteudo AS conteudo,
  		  tab_pesquisas_questoes_alternativas.pqa_eliminar AS eliminar,
  		  COUNT(tab_pesquisas_aplicacoes_respostas.pqa_id) AS respostas
  		FROM
  		  tab_pesquisas_questoes_alternativas
  		  LEFT OUTER JOIN tab_pesquisas_aplicacoes_respostas ON (tab_pesquisas_questoes_alternativas.pqa_id = tab_pesquisas_aplicacoes_respostas.pqa_id)
  		  LEFT OUTER JOIN tab_pesquisas_aplicacoes ON (tab_pesquisas_aplicacoes_respostas.pea_id = tab_pesquisas_aplicacoes.pea_id)
  		WHERE
  		  tab_pesquisas_questoes_alternativas.peq_id = $questao_id AND
  		  (tab_pesquisas_aplicacoes_respostas.par_id IS NULL OR tab_pesquisas_aplicacoes.pea_termino IS NOT NULL)
  		GROUP BY
  		  tab_pesquisas_questoes_alternativas.pqa_id
  		ORDER BY
  		  tab_pesquisas_questoes_alternativas.pqa_ordem";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$alternativas[] = $dados;
    }
    return $alternativas;
  }

  function buscaRespostasAbertas($questao_id) {
  	$query = "
  		SELECT
  		  IF(par_valor_tabulado IS NULL, par_valor_qualitativo, par_valor_tabulado) AS conteudo,
  		  COUNT(par_id) AS ocorrencias
  		FROM
  		  tab_pesquisas_aplicacoes_respostas
  		WHERE
  		  peq_id = $questao_id AND
  		  pqa_id IS NULL AND
  		  par_valor_qualitativo IS NOT NULL
  		GROUP BY
  		  IF(par_valor_tabulado IS NULL, par_valor_qualitativo, par_valor_tabulado)
  		ORDER BY
  		  ocorrencias DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $total += $dados["ocorrencias"];
    	$respostas[] = $dados;
    }
    return array("total" => $total, "itens" => $respostas);
  }

  function bucaQuantidadeRespostasAbertas($questao_id) {
  	$query = "
  		SELECT
  		  COUNT(par_id) AS respostas
  		FROM
  		  tab_pesquisas_aplicacoes_respostas
  		WHERE
  		  peq_id = $questao_id AND
  		  pqa_id IS NULL AND
  		  par_valor_qualitativo IS NOT NULL";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "respostas"), 10);
  }

  #####################
  ### Gera Listagem ###
  #####################

  function geraListagemQuestoes($itens) {
    $questoes = controle::instanciaObjeto("pesquisas_questoes", $this->bancodedados, $this->permissoes, false);
    return $questoes->geraListagemQuestoes($itens);
  }

  function geraListagemRespostasAbertas($itens, $questao_id) {
    $cabecalho = array ("conteudo" => "Conteúdo <span style=\"cursor:pointer\" onclick=\"exibirRespostas($questao_id)\">[Atualizar]</span> ({$itens["total"]})", "ocorrencias" => "Ocorrências");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens["itens"], 0, false, array(0, 80), null, array("left", "center"));
    return $listagem->conteudo;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("pesquisas.adicionar.inc.php");
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}", $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
    	$_POST["descricao"] = trim($_POST["descricao"]);
    	$_POST["texto_inicial"] = trim($_POST["texto_inicial"]);
    	$_POST["texto_final"] = trim($_POST["texto_final"]);
    	$query = "INSERT INTO tab_pesquisas (pes_descricao, pes_texto_inicial, pes_texto_final, pet_id) VALUES ('{$_POST["descricao"]}', '{$_POST["texto_inicial"]}', '{$_POST["texto_final"]}', 1)";
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
      $this->conteudo .= $this->carregaArquivo("pesquisas.ver.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Pesquisa", "relatorios" => "Relatórios")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[questoes]', $this->geraListagemQuestoes($this->buscaQuestoes($this->dados_item["id"])), $this->conteudo);
      $this->conteudo = str_replace('[texto_inicial]', $this->dados_item["texto_inicial"] ? str_replace(array("\r\n", "\n"), "<br>", $this->dados_item["texto_inicial"]) : "Nenhuma instrução disponível", $this->conteudo);
      $this->conteudo = str_replace('[texto_final]', $this->dados_item["texto_final"] ? str_replace(array("\r\n", "\n"), "<br>", $this->dados_item["texto_final"]) : "Nenhuma instrução disponível", $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
    	if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##################
  ### Relatórios ###
  ##################

  function relatorios() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      // Busca Dados
      $dados_aplicacoes = $this->buscaDadosAplicacoes($this->dados_item["id"]);
      // Conteudo
      $this->conteudo .= $this->carregaArquivo("pesquisas.relatorios.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Pesquisa", "relatorios" => "Relatórios")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
      $this->conteudo = str_replace('[aplicacoes]', "{$dados_aplicacoes["aplicacoes_respondidas"]} / {$dados_aplicacoes["aplicacoes_geradas"]}", $this->conteudo);
      $this->conteudo = str_replace('[periodo_aplicacoes]', "{$dados_aplicacoes["inicio"]} - {$dados_aplicacoes["termino"]}", $this->conteudo);
      $this->conteudo = str_replace('[relatorios]', $this->geraRelatorios($this->dados_item["id"], $dados_aplicacoes), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
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
	      $this->conteudo .= $this->carregaArquivo("pesquisas.editar.inc.php");
	      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);
	      // Ações
        $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Pesquisa", "relatorios" => "Relatórios")), $this->conteudo);
	      // Dados
	      $this->conteudo = str_replace('[descricao]', $this->dados_item["descricao"], $this->conteudo);
	      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
	      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
	      $this->conteudo = str_replace('[questoes_adicionar]', '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarQuestao('.$_GET["i"].')">', $this->conteudo);
	      $this->conteudo = str_replace('[questoes]', $this->geraListagemQuestoes($this->buscaQuestoes($this->dados_item["id"])), $this->conteudo);
	      $this->conteudo = str_replace('[texto_inicial]', $this->dados_item["texto_inicial"], $this->conteudo);
	      $this->conteudo = str_replace('[texto_final]', $this->dados_item["texto_final"], $this->conteudo);
	      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
	      // Botões
      	if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      	if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
	      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
	      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
	      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
	      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
	    } else {
	    	$_POST["descricao"] = trim($_POST["descricao"]);
	    	$_POST["texto_inicial"] = trim($_POST["texto_inicial"]);
	    	$_POST["texto_final"] = trim($_POST["texto_final"]);
	    	// Atualiza
	    	$query = "UPDATE tab_pesquisas SET pes_descricao = '{$_POST["descricao"]}', pes_texto_inicial = '{$_POST["texto_inicial"]}', pes_texto_final = '{$_POST["texto_final"]}' WHERE pes_id = {$_GET["i"]}";
	    	$this->bancodedados->query($query);
				// Registra Atividade
	      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
	      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
	      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
	    }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##############
  ### Ativar ###
  ##############

  function ativar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("pesquisas.ativar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=ativar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_pesquisas SET pet_id = 2, pes_data_inicio = NOW() WHERE pes_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
    }
  }

  ##############
  ### Finalizar ###
  ##############

  function finalizar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("pesquisas.finalizar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_pesquisas SET pet_id = 3, pes_data_termino = NOW() WHERE pes_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
    }
  }

  ##############
  ### Buscar ###
  ##############

  function buscar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=operacoes_lotes" id="operacoes_lotes.js"></script>';
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
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "descricao", "descricao" => "Descrição", "valor" => "", "where" => "(tab_pesquisas.pes_descricao LIKE '%{$valores["descricao"]}%')");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio", "descricao" => "Data &raquo; Início", "valor" => "", "where" => "(tab_pesquisas.pes_data_inicio >= '".$this->formataData($valores["data_inicio"])."')");
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_termino", "descricao" => "Data &raquo; Término", "valor" => "", "where" => "(tab_pesquisas.pes_data_termino <= '".$this->formataData($valores["data_termino"])."')");
		// Inicializa Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
    return $filtro;
  }

  function formataData($data) {
  	return substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2);
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
        tab_pesquisas.pes_id AS id,
        tab_pesquisas.pes_descricao AS descricao,
        DATE_FORMAT(tab_pesquisas.pes_data_inicio, '%d/%m/%Y') AS inicio,
        DATE_FORMAT(tab_pesquisas.pes_data_termino, '%d/%m/%Y') AS termino,
        tab_pesquisas_status.pet_nome AS status
      FROM
        tab_pesquisas
        INNER JOIN tab_pesquisas_status ON (tab_pesquisas.pet_id = tab_pesquisas_status.pet_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        tab_pesquisas.pes_descricao ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["pesquisas"]["ver"]) $dados["link"] = "?m=pesquisas&a=ver&i={$dados["id"]}";
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      if ($this->permissoes["pesquisas"]["relatorios"]) {
        $dados["relatorios"] = "<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"?m=pesquisas&a=relatorios&i={$dados["id"]}\">Relatórios</a>";
      }
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    if ($this->permissoes["pesquisas"]["relatorios"]) {
      $cabecalho = array ("contador" => "#", "descricao" => "Descrição", "inicio" => "Início", "termino" => "Término", "status" => "Status", "relatorios" => "&nbsp;");
      $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 0, 80, 80, 70, 80), null, array("right", "left"));
    } else {
      $cabecalho = array ("contador" => "#", "descricao" => "Descrição", "inicio" => "Início", "termino" => "Término", "status" => "Status");
      $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 0, 80, 80, 70), null, array("right", "left"));
    }
    return $listagem;
  }
  
  #######################
  ### Insere Pesquisa ###
  #######################
  
  function inserePesquisa($cliente_id, $contrato_id, $pesquisa_id, $chamado_categoria_id) {
    $this->buscaDadosItem($pesquisa_id);
    $query = "
      INSERT INTO
        tab_pesquisas_aplicacoes (pea_cadastro, cli_id, cor_id, pes_id)
      VALUES (NOW(), $cliente_id, $contrato_id, $pesquisa_id)";
    $this->bancodedados->query($query);
    $aplicacao_id = $this->bancodedados->ultimoIdInserido();
    // Insere Chamado
    $conteudo = "[d]{$this->dados_item["descricao"]} [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verPesquisasAplicacoes($aplicacao_id)\">Aplicar</a>][/d]";
    $chamado_id = $this->inserirChamado($cliente_id, $chamado_categoria_id, $conteudo);
    // Atualiza Codigo Chamado Aplicaçao
    $query = "UPDATE tab_pesquisas_aplicacoes SET cha_id = $chamado_id WHERE pea_id = $aplicacao_id";
    $this->bancodedados->query($query);
  }

  function inserirChamado($cliente_id, $categoria_id, $conteudo) {
    // Gera Dados Chamado
    $dados_chamado["solicitante"] = "Sistema (Intranet)";
    $dados_chamado["categoria"] = $categoria_id;
    $dados_chamado["prioridade"] = 3;
    $dados_chamado["tipo"] = 5;
    $dados_chamado["conteudo"] = $conteudo;
    $dados_chamado["sistema"] = true;
    // Instancia Classe Chamados
    $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
    $chamado_id = $chamados->adicionarChamado($cliente_id, null, $dados_chamado, "NULL");
    // Retorna ID
    return $chamado_id;
  }

}

?>
