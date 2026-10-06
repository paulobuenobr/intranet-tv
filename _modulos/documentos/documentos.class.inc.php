<?

class documentos {

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
  		$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
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

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_documentos.doc_id AS id,
        tab_documentos.doc_nome AS nome,
        tab_documentos.doc_arquivo_nome AS arquivo_nome,
        tab_documentos.doc_arquivo_conteudo AS arquivo_conteudo,
        DATE_FORMAT(tab_documentos.doc_data_registro, '%d/%m/%Y') AS data_registro,
        tab_documentos.doa_id AS categoria_id,
        tab_documentos_categorias.doa_nome AS categoria_nome,
        tab_documentos.dot_id AS tipo_id,
        tab_documentos_tipos.dot_nome AS tipo_nome,
        tab_documentos.dou_id AS status_id,
        tab_documentos_status.dou_nome AS status_nome
      FROM
        tab_documentos
        INNER JOIN tab_documentos_categorias ON (tab_documentos.doa_id = tab_documentos_categorias.doa_id)
        INNER JOIN tab_documentos_tipos ON (tab_documentos.dot_id = tab_documentos_tipos.dot_id)
        INNER JOIN tab_documentos_status ON (tab_documentos.dou_id = tab_documentos_status.dou_id)
      WHERE
        tab_documentos.doc_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosPergunta ($id) {
    $query = "SELECT doc_id AS documento, dof_conteudo AS conteudo FROM tab_documentos_faqs WHERE dof_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    return $dados;
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

  function corrigeNome($nome) {
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
    $nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
    $nome = trim($nome); // Remove excessos do início e do fim
    return $nome;
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaTipos() {
    $query = "SELECT dot_id AS id, dot_nome AS nome	FROM tab_documentos_tipos	ORDER BY dot_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaCategorias() {
    $query = "SELECT doa_id AS id, doa_nome AS nome FROM tab_documentos_categorias ORDER BY doa_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaStatus() {
    $query = "SELECT dou_id AS id, dou_nome AS nome	FROM tab_documentos_status ORDER BY	dou_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  ############
  ### FAQs ###
  ############

  function geraListagemFAQs($faqs) {
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $faqs);
    return $listagem->conteudo;
  }

  function buscaDadosFAQ($id) {
    $query = "
      SELECT
        tab_documentos_faqs.dof_id AS id,
        DATE_FORMAT(tab_documentos_faqs.dof_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_documentos_faqs.dof_conteudo AS conteudo,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_documentos_faqs
        INNER JOIN tab_usuarios ON (tab_documentos_faqs.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_documentos_faqs.doc_id = $id AND
        tab_documentos_faqs.dof_pai IS NULL";
    $query = $this->bancodedados->query($query);
    while (($pergunta = $this->bancodedados->resultadoArray($query)) != false) {
      $pergunta["detalhamento"] = "<b>".$pergunta["responsavel"]."</b> em <b>".$pergunta["data_hora"]."</b><br>";
      $pergunta["detalhamento"] .= "&raquo; Pergunta: ".preg_replace("/[\r\n\t ]+/"," ",$pergunta["conteudo"])."<br>";
      $pergunta["detalhamento"] .= $this->buscaDadosResposta($pergunta["id"]);
      $faqs[] = $pergunta;
    }
    return $faqs;
  }

  function buscaDadosResposta ($id) {
    $query = "
      SELECT
        tab_documentos_faqs.dof_id AS id,
        DATE_FORMAT(tab_documentos_faqs.dof_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_documentos_faqs.dof_conteudo AS conteudo,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_documentos_faqs
        INNER JOIN tab_usuarios ON (tab_documentos_faqs.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_documentos_faqs.dof_pai = $id";
    $query = $this->bancodedados->query($query);
    $resposta = $this->bancodedados->resultadoArray($query);
    if ($resposta) {
      $respostas = "<b>".$resposta["responsavel"]."</b> em <b>".$resposta["data_hora"]."</b><br>";
      $respostas .= "&raquo; Resposta: ".preg_replace("/[\r\n\t ]+/"," ",$resposta["conteudo"])."<br>";
    } else {
      $respostas = "&raquo; Resposta: Aguardando resposta";
      if ($this->permissoes[$this->dados_modulo["nome"]]["responder"]) $respostas .= " (<a href='?m=documentos&a=responder&i=$id' class='txtAzulEscuro11'>Responder</a>)";
    }
    return $respostas;
  }

  ###################
  ### Comentários ###
  ###################

  function registraComentario($id, $conteudo, $responsavel) {
    $conteudo = trim(addslashes($conteudo));
    $query = "INSERT INTO tab_documentos_comentarios (doe_datahora, doe_conteudo, doc_id, usu_id) VALUES (NOW(), '$conteudo', $id, $responsavel)";
    $this->bancodedados->query($query);
  }

  function geraListagemComentarios($comentarios) {
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  function buscaDadosComentario($id) {
    $query = "
      SELECT
        tab_documentos_comentarios.doe_id AS id,
        DATE_FORMAT(tab_documentos_comentarios.doe_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_documentos_comentarios.doe_conteudo AS conteudo,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_documentos_comentarios
        INNER JOIN tab_usuarios ON (tab_documentos_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_documentos_comentarios.doc_id = $id";
    $query = $this->bancodedados->query($query);
    while (($comentario = $this->bancodedados->resultadoArray($query)) != false) {
      $comentario["detalhamento"] = "<b>".$comentario["responsavel"]."</b> em <b>".$comentario["data_hora"]."</b><br>";
      $comentario["detalhamento"] .= "".preg_replace("/[\r\n\t ]+/"," ", $comentario["conteudo"]);
      $comentarios[] = $comentario;
    }
    return $comentarios;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("documentos.adicionar.inc.php");
      // Ações
      $acoes  = '<td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td>';
      $this->conteudo = str_replace("[acoes]", $acoes, $this->conteudo);
      // Conteúdo
      $this->conteudo = str_replace("[tipos]", $this->geraSelect($this->buscaTipos()), $this->conteudo);
      $this->conteudo = str_replace("[categorias]", $this->geraSelect($this->buscaCategorias()), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Action
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}", $this->conteudo);
    } else {
      $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
      if($_POST["tipo"] == '1') {
        $arquivo_nome = "'{$_FILES["arquivo"]["name"]}'";
        $arquivo_conteudo = "null";
      } else {
        $arquivo_nome = "null";
        $arquivo_conteudo = "'{$_POST["conteudo"]}'";
      }
      $query = "INSERT INTO tab_documentos (doc_nome, doc_arquivo_nome, doc_arquivo_conteudo, doc_data_registro, doa_id, dou_id, dot_id) VALUES ('{$_POST["nome"]}', $arquivo_nome, $arquivo_conteudo, NOW(), {$_POST["categoria"]}, 1, {$_POST["tipo"]})";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      if ($_FILES["arquivo"]) {
        move_uploaded_file($_FILES["arquivo"]["tmp_name"], "./biblioteca/$id");
      }

      // Registra Comentário
      $comentario = "<b>Ação</b>: Adicionado";
      if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> ".preg_replace("/[\r\n\t ]+/"," ",trim($_POST["comentario"]));
      $this->registraComentario($id, $comentario, $_SESSION["usuario"]);
      // Atividades
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$id);
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("documentos.ver.inc.php");

      // Conteudo
      if ($this->dados_item["tipo_id"] == 1) {
        $this->conteudo = str_replace("[mostra_conteudo]", "none", $this->conteudo);
        $this->conteudo = str_replace("[mostra_arquivo]", ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group"), $this->conteudo);
        $this->conteudo = str_replace("[arquivo]", $this->dados_item["arquivo_nome"], $this->conteudo);
      } else {
        $this->conteudo = str_replace("[mostra_conteudo]", ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group"), $this->conteudo);
        $this->conteudo = str_replace("[mostra_arquivo]", "none", $this->conteudo);
        $this->conteudo = str_replace("[conteudo]", nl2br($this->dados_item["arquivo_conteudo"]), $this->conteudo);
      }
      $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace("[data_registro]", $this->dados_item["data_registro"], $this->conteudo);
      $this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[categoria]", $this->dados_item["categoria_nome"], $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace("[faqs]", $this->geraListagemFAQs($this->buscaDadosFAQ($this->dados_item["id"])), $this->conteudo);
      $this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->buscaDadosComentario($this->dados_item["id"])), $this->conteudo);

      // Ações
      $acoes  = '<td bgcolor="#505050" width="50%"><b>&nbsp;Ver&nbsp;</b></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["pegar_arquivo"] && $this->dados_item["tipo_id"] == 1) $botoes[] = '<input type="button" name="download" class="buttonAzulEscuroBranco11" value="Download" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=pegar_arquivo&i=' . $this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["perguntar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="perguntar" class="buttonAzulEscuroBranco11" value="Perguntar" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=perguntar&i=' . $this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("documentos.editar.inc.php");
        // Conteudo
        if ($this->dados_item["tipo_id"] == 1) {
          $this->conteudo = str_replace("[mostra_conteudo]", "none", $this->conteudo);
          $this->conteudo = str_replace("[mostra_arquivo]", ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group"), $this->conteudo);
        } else {
          $this->conteudo = str_replace("[mostra_conteudo]", ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group"), $this->conteudo);
          $this->conteudo = str_replace("[mostra_arquivo]", "none", $this->conteudo);
        }
        $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace("[tipos]", $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"]), $this->conteudo);
        $this->conteudo = str_replace("[categorias]", $this->geraSelect($this->buscaCategorias(), $this->dados_item["categoria_id"]), $this->conteudo);
        $this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"]), $this->conteudo);
        if ($this->permissoes[$this->dados_modulo["nome"]]["editar_plus"]) {
          $this->conteudo = str_replace("[arquivo]", '<input name="arquivo" id="arquivo" type="file" value="[arquivo]" class="inputBrancoAzulEscuro11">', $this->conteudo);
          $this->conteudo = str_replace("[permissao_editar]", '', $this->conteudo);
        }
        else {
          $this->conteudo = str_replace("[arquivo]", $this->dados_item["arquivo_nome"], $this->conteudo);
          $this->conteudo = str_replace("[permissao_editar]", 'readonly', $this->conteudo);
        }
        $this->conteudo = str_replace("[conteudo]", $this->dados_item["arquivo_conteudo"], $this->conteudo);
        $this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->buscaDadosComentario($this->dados_item["id"])), $this->conteudo);

        // Ações
        $acoes  = '<td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td>';
        $this->conteudo = str_replace("[acoes]", $acoes, $this->conteudo);
        // Botões
        if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="submit" name="Salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"].'\';">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
      } else {
        if ($this->dados_item["tipo_id"] == '1') {
          $arquivo_nome = ($_FILES["arquivo"]["name"]) ? "'{$_FILES["arquivo"]["name"]}'" : "'{$this->dados_item["arquivo_nome"]}'";
          $arquivo_conteudo = "null";
        } else {
          $arquivo_nome = "null";
          $arquivo_conteudo = "'{$_POST["conteudo"]}'";
        }
        $query = "UPDATE tab_documentos SET doc_arquivo_nome = $arquivo_nome, doc_arquivo_conteudo = $arquivo_conteudo, doa_id = {$_POST["categoria"]}, dou_id = {$_POST["status"]} WHERE doc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $id = $this->dados_item["id"];
        if($_FILES["arquivo"]["name"]) {
          move_uploaded_file($_FILES["arquivo"]["tmp_name"], "./biblioteca/$id");
        }
        // Registra Comentário
        $comentario = "<b>Ação</b>: Editado";
        if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> ".preg_replace("/[\r\n\t ]+/"," ",trim($_POST["comentario"]));
        $this->registraComentario($id, $comentario, $_SESSION["usuario"]);
        // Registra Atividades
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$id);
      }
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("documentos.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" value="Fechar" class="buttonAzulEscuroBranco11" onclick="window.close()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'&cliente_id='.$this->dados_item["cliente_id"], $this->conteudo);
      } else {
        // Verifica se é arquivo e o Sistema Operacional do Servidor
        if ($this->dados_item["tipo_nome"] == "Arquivo") {
          if (strpos($_SERVER["SERVER_SOFTWARE"], "Win32") !== false) unlink("biblioteca\\{$_GET["i"]}");
          else unlink("biblioteca/{$_GET["i"]}");
        }
        $query = "DELETE FROM tab_documentos_faqs WHERE doc_id = {$_GET["i"]} AND dof_pai IS NOT NULL";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_documentos_comentarios WHERE doc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_documentos_faqs WHERE doc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_documentos WHERE doc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);

        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
      }
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
  }

  #################
  ### Perguntar ###
  #################

  function perguntar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("documentos.perguntar.inc.php");
      // Ações
      $acoes  = '<td bgcolor="#505050"><b>&nbsp;Perguntar&nbsp;</b></td>';
      $this->conteudo = str_replace("[acoes]", $acoes, $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Action
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
    } else {
      $_POST["pergunta"] = trim(addslashes($_POST["pergunta"]));
      $query = "INSERT INTO tab_documentos_faqs (doc_id, dof_datahora, dof_conteudo, usu_id) VALUES ({$_GET["i"]}, NOW(), '{$_POST["pergunta"]}', {$_SESSION["usuario"]})";
      $this->bancodedados->query($query);

      // Atividades
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_GET["i"]);
    }
  }

  #################
  ### Responder ###
  #################

  function responder() {
    if (!$_POST) {
      $dados = $this->buscaDadosPergunta($_GET["i"]);
      $this->conteudo .= $this->carregaArquivo("documentos.responder.inc.php");
      // Conteudo
      $this->conteudo = str_replace("[conteudo]", $dados["conteudo"], $this->conteudo);
      $this->conteudo = str_replace("[documento]", $dados["documento"], $this->conteudo);

      // Ações
      $acoes  = '<td bgcolor="#505050"><b>&nbsp;Responder&nbsp;</b></td>';
      $this->conteudo = str_replace("[acoes]", $acoes, $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Action
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
    } else {
      $_POST["resposta"] = trim(addslashes($_POST["resposta"]));
      $query = "INSERT INTO tab_documentos_faqs (doc_id, dof_datahora, dof_conteudo, usu_id, dof_pai) VALUES ({$_POST["documento"]}, NOW(), '{$_POST["resposta"]}', {$_SESSION["usuario"]}, {$_GET["i"]})";
      $this->bancodedados->query($query);

      // Atividades
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_POST["documento"]);
    }
  }

  #####################
  ### Pegar Arquivo ###
  #####################

  function pegar_arquivo() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $conteudo = $this->carregaArquivo("./biblioteca/".$this->dados_item["id"]);
      header("Content-Disposition: attachment; filename=".$this->dados_item["arquivo_nome"].";");
      print $conteudo;
      die();
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=listar");
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar \';">';
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
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "tab_documentos.doc_nome LIKE '%[valor]%'");
    // Anexos
    $itens[] = array ("tipo" => "input", "nome" => "anexo", "descricao" => "Anexo", "valor" => "", "where" => "tab_documentos.doc_arquivo_nome LIKE '%[valor]%'");
    // Data Registro
    $itens[] = array ("tipo" => "input", "nome" => "data_registro", "descricao" => "Data Registro", "valor" => "", "where" => "(DATE_FORMAT(tab_documentos.doc_data_registro, '%d/%m/%Y') LIKE '%[valor]')");
    // Categorias
    $categorias = $this->buscaCategorias();
    while (($categoria = @current($categorias)) != false) {
      $select_categorias[] = array ("id" => key($categorias), "nome" => $categoria);
      next($categorias);
    }
    $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categorias", "valor" => $select_categorias, "where" => "tab_documentos_categorias.doa_id LIKE '[valor]' OR tab_documentos_categorias.doa_pai LIKE '[valor]'");
    // Tipos
    $tipos = $this->buscaTipos();
    while (($tipo = @current($tipos)) != false) {
      $select_tipos[] = array ("id" => key($tipos), "nome" => $tipo);
      next($tipos);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipos", "valor" => $select_tipos, "where" => "tab_documentos.dot_id LIKE '[valor]'");
    // Status
    $statuss = $this->buscaStatus();
    while (($status = @current($statuss)) != false) {
      $select_statuss[] = array ("id" => key($statuss), "nome" => $status);
      next($statuss);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_statuss, "where" => "tab_documentos.dou_id LIKE '[valor]'");

    // Gera Filtro
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
      SELECT SQL_CALC_FOUND_ROWS
        tab_documentos.doc_id AS id,
        tab_documentos.doc_nome AS nome,
        tab_documentos.doc_arquivo_nome AS arquivo_nome,
        DATE_FORMAT(tab_documentos.doc_data_registro, '%d/%m/%Y') AS data_hora,
        tab_documentos_categorias.doa_nome AS categoria_nome,
        tab_documentos_tipos.dot_nome AS tipo_nome,
        tab_documentos_status.dou_nome AS status_nome
      FROM
        tab_documentos
        INNER JOIN tab_documentos_categorias ON (tab_documentos.doa_id = tab_documentos_categorias.doa_id)
        INNER JOIN tab_documentos_tipos ON (tab_documentos.dot_id = tab_documentos_tipos.dot_id)
        INNER JOIN tab_documentos_status ON (tab_documentos.dou_id = tab_documentos_status.dou_id)
       WHERE
        $filtro->parametros_sql
      ORDER BY
        nome
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes[$this->dados_modulo["nome"]]["pegar_arquivo"] && $dados["arquivo_nome"]) $dados["anexo"] = '<a href="?m='.$this->dados_modulo["nome"].'&a=pegar_arquivo&i='.$dados["id"].'" class="txtAzulEscuro11" style="text-decoration:underline;">'.$dados["arquivo_nome"].'</a>';
      if ($this->permissoes["documentos"]["ver"]) $dados["link"] = "?m=documentos&a=ver&i={$dados["id"]}";
      $dados["ar"] = $this->estaAguardandoResposta($dados["id"]) ? "Sim" : "Não";
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("nome" => "Nome", "anexo" => "Anexo", "data_hora" => "Data Registro", "ar" => "<span onMouseOver=\"hintShow('Aguardando Resposta', 160);\" onMouseOut=\"hintHide();\">AR</span>", "categoria_nome" => "Categoria", "tipo_nome" => "Tipo", "status_nome" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem;
  }

  function estaAguardandoResposta ($id) {
    $query = "
      SELECT
        COUNT(*) AS quantidade
      FROM
        tab_documentos_faqs
        LEFT OUTER JOIN tab_documentos_faqs resposta ON (tab_documentos_faqs.dof_id = resposta.dof_pai)
      WHERE
        resposta.dof_id IS NULL AND
        tab_documentos_faqs.doc_id = $id AND
        tab_documentos_faqs.dof_pai IS NULL";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->resultado($query,0,"quantidade")) ? true : false;
  }

}

?>
