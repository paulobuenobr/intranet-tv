<?

class mensagens_lotes {

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
			//if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			if ($_GET["a"] == "ver_dc" && $this->permissoes[$this->dados_modulo["nome"]]["editar_dc"]) $_GET["a"] = "editar_dc";
			if ($_GET["a"] == "ver_de" && $this->permissoes[$this->dados_modulo["nome"]]["editar_de"]) $_GET["a"] = "editar_de";
      $this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function geraTitulo() {
    $titulo  = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
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
        tab_mensagens_lotes.mel_id AS id,
        tab_mensagens_lotes.mel_sistema AS sistema,
        tab_mensagens_lotes.mls_id AS status_id,
        tab_mensagens_lotes_status.mls_nome AS status_nome,
        tab_mensagens_lotes.mlt_id AS tipo_id,
        tab_mensagens_lotes_tipos.mlt_nome AS tipo_nome,
        tab_mensagens_lotes.mlm_id AS modelo_id,
        CONCAT(tab_mensagens_lotes_modelos.mlm_nome, ' (v. ', tab_mensagens_lotes_modelos.mlm_versao, ')') AS modelo_nome,
        tab_mensagens_lotes_modelos.mlm_conteudo_html AS modelo_conteudo_html,
        tab_mensagens_lotes_modelos.mlm_conteudo_texto AS modelo_conteudo_texto,
        tab_mensagens_lotes.mel_nome AS nome,
        DATE_FORMAT(tab_mensagens_lotes.mel_data_inicio, '%d/%m/%Y') AS inicio,
        DATE_FORMAT(tab_mensagens_lotes.mel_data_termino, '%d/%m/%Y') AS termino,
        tab_mensagens_lotes.mel_parametros AS parametros
      FROM
        tab_mensagens_lotes
        INNER JOIN tab_mensagens_lotes_status ON (tab_mensagens_lotes.mls_id = tab_mensagens_lotes_status.mls_id)
        INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
        INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens_lotes.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
       WHERE
        tab_mensagens_lotes.mel_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["comentarios"] = $this->buscaDadosComentario($id);
      $this->dados_item["parametros"] = @unserialize($this->dados_item["parametros"]);
    }
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

  ###################
  ### Comentários ###
  ###################

  function registraComentario($id, $conteudo, $categoria, $responsavel) {
    if ($conteudo) $conteudo = trim(addslashes($conteudo));
    $query = "INSERT INTO tab_mensagens_lotes_comentarios (mlc_datahora, mel_id, mlc_conteudo, mlo_id, usu_id) VALUES (NOW(), $id, '$conteudo', $categoria, $responsavel)";
    $this->bancodedados->query($query);
  }

  function geraListagemComentarios($comentarios) {
    for ($i = 0; $i < sizeof($comentarios); $i++) {
      $comentarios[$i]["responsavel"] = ($comentarios[$i]["responsavel_id"]) ? $comentarios[$i]["responsavel_nome"] : "Sistema";
      $comentarios[$i]["detalhamento"][] = "<b>".$comentarios[$i]["responsavel"]."</b> em <b>".$comentarios[$i]["data_hora"]."</b>";
      if ($comentarios[$i]["conteudo"]) $comentarios[$i]["detalhamento"][] = "".preg_replace("/[\r\n\t ]+/"," ", $comentarios[$i]["conteudo"]);
      $comentarios[$i]["detalhamento"] = @implode('<br>',$comentarios[$i]["detalhamento"]);
    }
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  function buscaDadosComentario($id) {
    $query = "
      SELECT
        tab_mensagens_lotes_comentarios.mlc_id AS id,
        tab_mensagens_lotes_comentarios.mel_id AS mensagem,
        DATE_FORMAT(tab_mensagens_lotes_comentarios.mlc_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        CONCAT('<b>Ação</b>: ', tab_mensagens_lotes_comentarios_categorias.mlo_nome, IF(tab_mensagens_lotes_comentarios.mlc_conteudo IS NOT NULL AND tab_mensagens_lotes_comentarios.mlc_conteudo <> '', CONCAT('<br><b>Comentários</b>: ', tab_mensagens_lotes_comentarios.mlc_conteudo), '')) AS conteudo,
        tab_usuarios.usu_id AS responsavel_id,
        IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel_nome
      FROM
        tab_mensagens_lotes_comentarios
        INNER JOIN tab_mensagens_lotes_comentarios_categorias ON (tab_mensagens_lotes_comentarios.mlo_id = tab_mensagens_lotes_comentarios_categorias.mlo_id)
        LEFT OUTER JOIN tab_usuarios ON (tab_mensagens_lotes_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_mensagens_lotes_comentarios.mel_id = $id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $comentarios[] = $dados;
    }
    return $comentarios;
  }

  ############
  ## Buscas ##
  ############

  function buscaStatus() {
    $query = "
  		SELECT
  			mls_id AS id,
  			mls_nome AS nome
  		FROM
  			tab_mensagens_lotes_status
  		ORDER BY
  			mls_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaTipos() {
    $query = "
  		SELECT
  			mlt_id AS id,
  			mlt_nome AS nome
  		FROM
  			tab_mensagens_lotes_tipos
  		ORDER BY
  			mlt_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaTiposIntervalo() {
    $lista["1"] = "Minutos";
    $lista["2"] = "Horas";
    $lista["3"] = "Dias";
    return $lista;
  }

  function buscaDestinos() {
    $lista["0"] = "Todos os e-mails";
    $lista["1"] = "E-mails de contato";
    $lista["2"] = "E-mails Wave System";
    return $lista;
  }

  function buscaModelos() {
    $query = "
  		SELECT
  			mlm_id AS id,
  			CONCAT(mlm_nome, ' (v. ', mlm_versao, ')') AS nome
  		FROM
  			tab_mensagens_lotes_modelos
  		ORDER BY
  			mlm_nome, mlm_versao";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  #################
  ### Situações ###
  #################

  function ehAprovavel() {
    return $this->permissoes[$this->dados_modulo["nome"]]["aprovar"] && !$this->dados_item["sistema"] && in_array($this->dados_item["status_id"], array(1, 3));
  }

  function ehReprovavel() {
    return $this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && !$this->dados_item["sistema"] && in_array($this->dados_item["status_id"], array(1, 2));
  }

  function ehAtivavel() {
    return $this->permissoes[$this->dados_modulo["nome"]]["ativar"] && !$this->dados_item["sistema"] && $this->dados_item["status_id"] == 2;
  }

  function ehCancelavel() {
    return $this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && !$this->dados_item["sistema"] && in_array($this->dados_item["status_id"], array(1, 2, 3, 5));
  }

  function ehRemovivel() {
    return $this->permissoes[$this->dados_modulo["nome"]]["remover"] && !$this->dados_item["sistema"] && in_array($this->dados_item["status_id"], array(1, 3, 4));
  }

  ###############
  ### Filtros ###
  ###############

  function buscaFiltrosListagem($itens, $editavel=true) {
    if ($editavel) {
      $cabecalho = array("campo" => "Parametro", "comparacao" => "Comparação", "valor" => "Valor", "botoes" => "");
    } else {
      $cabecalho = array("campo" => "Parametro", "comparacao" => "Comparação", "valor" => "Valor");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function buscaLotesFiltros() {
    $query = "
  		SELECT
  			mli_id AS id,
  			mli_nome AS nome
  		FROM
  			tab_mensagens_lotes_filtros
  		ORDER BY
  			nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaDadosLotesFiltros($filtro_tipo_id) {
    $query = "
  		SELECT
  		  mli_nome AS nome,
  			mli_parametros AS parametros
  		FROM
  			tab_mensagens_lotes_filtros
  		WHERE
  			mli_id = $filtro_tipo_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      return $dados;
    }
  }

  function buscaFiltroTipoConteudo($conteudo) {
    if (is_array($conteudo)) {
      return $conteudo;
    } else {
      $query = $this->bancodedados->query($conteudo);
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $lista[$dados["id"]] = $dados["nome"];
      }
      return $lista;
    }
  }

  function buscaFiltros($filtros, $editavel = false) {
    if ($editavel) {
      $lista[] = $this->buscaFiltrosAdicionar();
    }
    if ($filtros) {
      foreach ($filtros as $filtro) {
        if ($editavel) {
          $filtro["link"] = "javascript:selecionarFiltro(this, event, {$filtro["id"]})";
          $filtro["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerFiltro(\''.$filtro["id"].'\')">';
        }
        $lista[] = $filtro;
      }
    } elseif ($editavel) {
      $lista[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    return $lista;
  }

  function buscaFiltrosAdicionar() {
    $adicionar["campo"] = '<select name="campo" id="campo" style="width:329px" class="inputBrancoAzulEscuro11" onchange="mudaFiltroCampo(this.value)">'.$this->geraSelect($this->buscaLotesFiltros(), null, true).'</select>';
    $adicionar["comparacao"] = '<div id="div_comparacao"><select name="comparacao" id="comparacao" style="width:120px" class="inputBrancoAzulEscuro11" disabled></select></div>';
    $adicionar["valor"] = '<div id="div_valor"><select name="valor" id="valor" style="width:230px" class="inputBrancoAzulEscuro11" disabled></select></div>';
    $adicionar["botoes"] = '<input type="button" name="adicionar_filtro" id="adicionar_filtro" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarFiltro()">';
    return $adicionar;
  }

  function geraFiltrosListagem($dados = null, $editavel = true) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        if ($editavel) {
          $dados[$i]["link"] = "javascript:selecionarFiltro(this, event, ".($i + 1).")";
          $dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerFiltro(\''.($i + 1).'\')">';
        }
        $dados_tipo = $this->buscaDadosLotesFiltros($dados[$i]["campo"]);
        $dados[$i]["campo"] = $dados_tipo["nome"];
        $dados[$i]["comparacao"] = $dados_tipo["parametros"]["comparacao"][$dados[$i]["comparacao"]];
        if ($dados_tipo["parametros"]["valor_conteudo"]) {
          $dados_valor = $this->buscaFiltroTipoConteudo($dados_tipo["parametros"]["valor_conteudo"]);
          $dados[$i]["valor"] = $dados_valor[$dados[$i]["valor"]];
        }
      }
      sort($dados);
    } else {
      $dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    if ($editavel) {
      $dados = funcoes::mescla_arrays(array($this->buscaFiltrosAdicionar()), $dados);
      $cabecalho = array("campo" => "Campo", "comparacao" => "Comparação", "valor" => "Valor", "botoes" => "");
    } else {
      $cabecalho = array("campo" => "Campo", "comparacao" => "Comparação", "valor" => "Valor");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("mensagens_lotes.adicionar.inc.php");
      // Action
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}", $this->conteudo);
      // Conteúdo
      $this->conteudo = str_replace("[tipos]", $this->geraSelect($this->buscaTipos()), $this->conteudo);
      $this->conteudo = str_replace("[modelos]", $this->geraSelect($this->buscaModelos(), null, true), $this->conteudo);
      if ($this->permissoes["mensagens_lotes_modelos"]["editar"]) {
        $this->conteudo = str_replace("[ver_modelo]", '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onclick="verModelo(this.form.modelo.value)">', $this->conteudo);
      } elseif ($this->permissoes["mensagens_lotes_modelos"]["editar"]) {
        $this->conteudo = str_replace("[ver_modelo]", '<input type="button" name="ver" class="buttonAzulEscuroBranco11" value="Ver" onclick="verModelo(this.form.modelo.value)">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[ver_modelo]", "", $this->conteudo);
      }
      $this->conteudo = str_replace("[destinos]", $this->geraSelect($this->buscaDestinos(), null, true), $this->conteudo);
      $this->conteudo = str_replace("[tipos_intervalo]", $this->geraSelect($this->buscaTiposIntervalo(), null, true), $this->conteudo);
      $this->conteudo = str_replace("[filtros]", $this->buscaFiltrosListagem($this->buscaFiltros(null, true)), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      $inicio = $_POST["inicio"] ? "'".substr($_POST["inicio"], 6, 4)."-".substr($_POST["inicio"], 3, 2)."-".substr($_POST["inicio"], 0, 2)."'" : "NULL";
      $termino = $_POST["termino"] ? "'".substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)."'" : "NULL";
      $parametros["envio"]["remetente_nome"] = $_POST["remetente_nome"];
      $parametros["envio"]["remetente_email"] = $_POST["remetente_email"];
      if ($_POST["tipo"] == 1) {
        if ($_POST["cc_email"]) {
          $parametros["envio"]["cc_nome"] = $_POST["cc_nome"];
          $parametros["envio"]["cc_email"] = $_POST["cc_email"];
        }
        if ($_POST["bcc_email"]) {
          $parametros["envio"]["bcc_nome"] = $_POST["bcc_nome"];
          $parametros["envio"]["bcc_email"] = $_POST["bcc_email"];
        }
        if ($_POST["reply_email"]) {
          $parametros["envio"]["reply_nome"] = $_POST["reply_nome"];
          $parametros["envio"]["reply_email"] = $_POST["reply_email"];
        }
        if ($_POST["assunto"]) {
          $parametros["envio"]["assunto"] = $_POST["assunto"];
        }
      }
      $parametros["envio"]["destino"] = $_POST["destino"];
      $parametros["envio"]["quantidade"] = $_POST["envio_quantidade"] + 0;
      $parametros["envio"]["intervalo"] = $parametros["envio"]["intervalo"] ? $_POST["envio_intervalo"] + 0 : null;
      $parametros["envio"]["intervalo_tipo"] = $parametros["envio"]["intervalo"] ? $_POST["envio_intervalo_tipo"] + 0 : null;
      $parametros["filtros"] = @unserialize(stripslashes($_POST["filtros_lista"]));
      $parametros = @serialize($parametros);
      $query = "
        INSERT INTO
          tab_mensagens_lotes (mel_nome, mel_parametros, mel_data_inicio, mel_data_termino, mel_sistema, mlt_id, mlm_id, mls_id)
        VALUES ('{$_POST["lote_nome"]}', '$parametros', $inicio, $termino, 0, {$_POST["tipo"]}, {$_POST["modelo"]}, 1)";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      // Registra Comentário
      $this->registraComentario($id, $_POST["comentarios"], 1 , $_SESSION["usuario"]);
      // Registra Atividade
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$id}");
    }
  }

  ############################
  ### Ver Dados Cadastrais ###
  ############################

  function ver_dc() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      $this->conteudo .= $this->carregaArquivo("mensagens_lotes.ver_dc.inc.php");
      $acoes  = '<td bgcolor="#505050" width="50%"><b>&nbsp;Dados Cadastrais&nbsp;</b></td>';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver_de"]) $acoes .= '<td bgcolor="#828282" width="50%"><b>&nbsp;<a href="?m='.$this->dados_modulo["nome"].'&a=ver_de&i='.$this->dados_item["id"].'" class="txtBranco11">Dados de Envio</a>&nbsp;</b></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace("[inicio]", $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace("[termino]", $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace("[filtros]", $this->geraFiltrosListagem($this->dados_item["parametros"]["filtros"], false), $this->conteudo);
      $this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" value="Aprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=aprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" value="Reprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" value="Cancelar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" value="Ativar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ##########################
  ### Ver Dados de Envio ###
  ##########################

  function ver_de() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("mensagens_lotes.ver_de.inc.php");
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"]) $acoes = '<td bgcolor="#828282" width="50%"><b>&nbsp;<a href="?m='.$this->dados_modulo["nome"].'&a=ver_dc&i='.$this->dados_item["id"].'" class="txtBranco11">Dados Cadastrais</a>&nbsp;</b></td>';
      $acoes .= '<td bgcolor="#505050" width="50%"><b>&nbsp;Dados de Envio&nbsp;</b></td>';
      // Busca Dados
      $intervalo_tipos = $this->buscaTiposIntervalo();
      $destino = $this->buscaDestinos();
      // Conteúdo
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[modelo_nome]", $this->dados_item["modelo_nome"], $this->conteudo);
      if ($this->permissoes["mensagens_lotes_modelos"]["editar"]) {
        $this->conteudo = str_replace("[ver_modelo]", '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onclick="verModelo('.$this->dados_item["modelo_id"].')">', $this->conteudo);
      } elseif ($this->permissoes["mensagens_lotes_modelos"]["ver"]) {
        $this->conteudo = str_replace("[ver_modelo]", '<input type="button" name="ver" class="buttonAzulEscuroBranco11" value="Ver" onclick="verModelo('.$this->dados_item["modelo_id"].')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[ver_modelo]", "", $this->conteudo);
      }
      $this->conteudo = str_replace("[mostra_conteudo_tipo_1]", ($this->dados_item["tipo_id"] == 1) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
      $this->conteudo = str_replace("[mostra_conteudo_tipo_2]", ($this->dados_item["tipo_id"] == 2) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
      $this->conteudo = str_replace("[destino]", $destino[$this->dados_item["parametros"]["envio"]["destino"]], $this->conteudo);
      $this->conteudo = str_replace("[remetente]", ($this->dados_item["parametros"]["envio"]["remetente_nome"]) ? $this->dados_item["parametros"]["envio"]["remetente_nome"]." / ".$this->dados_item["parametros"]["envio"]["remetente_email"] : $this->dados_item["parametros"]["envio"]["remetente_email"], $this->conteudo);
      $this->conteudo = str_replace("[cc]", ($this->dados_item["parametros"]["envio"]["cc_nome"]) ? $this->dados_item["parametros"]["envio"]["cc_nome"]." / ".$this->dados_item["parametros"]["envio"]["cc_email"] : $this->dados_item["parametros"]["envio"]["cc_email"], $this->conteudo);
      $this->conteudo = str_replace("[bcc]", ($this->dados_item["parametros"]["envio"]["bcc_nome"]) ? $this->dados_item["parametros"]["envio"]["bcc_nome"]." / ".$this->dados_item["parametros"]["envio"]["bcc_email"] : $this->dados_item["parametros"]["envio"]["bcc_email"], $this->conteudo);
      $this->conteudo = str_replace("[reply]", ($this->dados_item["parametros"]["envio"]["reply_nome"]) ? $this->dados_item["parametros"]["envio"]["reply_nome"]." / ".$this->dados_item["parametros"]["envio"]["reply_email"] : $this->dados_item["parametros"]["envio"]["reply_email"], $this->conteudo);
      $this->conteudo = str_replace("[assunto]", $this->dados_item["parametros"]["envio"]["assunto"], $this->conteudo);
      $this->conteudo = str_replace("[envio_quantidade]", $this->dados_item["parametros"]["envio"]["quantidade"], $this->conteudo);
      $this->conteudo = str_replace("[envio_intervalo]", $this->dados_item["parametros"]["envio"]["intervalo"], $this->conteudo);
      $this->conteudo = str_replace("[envio_intervalo_tipo]", $intervalo_tipos[$this->dados_item["parametros"]["envio"]["intervalo_tipo"]], $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" value="Aprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=aprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" value="Reprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" value="Cancelar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" value="Ativar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ###############################
  ### Editar Dados Cadastrais ###
  ###############################

  function editar_dc() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      if (empty($_POST)) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.editar_dc.inc.php");
        // Action
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_dc&i={$this->dados_item["id"]}", $this->conteudo);
        $acoes = '<td bgcolor="#505050" width="50%"><b>&nbsp;Dados Cadastrais&nbsp;</b></td>';
        if ($this->permissoes[$this->dados_modulo["nome"]]["ver_de"]) $acoes .= '<td bgcolor="#828282" width="50%"><b>&nbsp;<a href="?m='.$this->dados_modulo["nome"].'&a=ver_de&i='.$this->dados_item["id"].'" class="txtBranco11">Dados de Envio</a>&nbsp;</b></td>';
        // Conteúdo
        $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
        $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace("[inicio]", $this->dados_item["inicio"], $this->conteudo);
        $this->conteudo = str_replace("[termino]", $this->dados_item["termino"], $this->conteudo);
        $this->conteudo = str_replace("[datas_disabled]", in_array($this->dados_item["status_id"], array(4, 5, 6, 7)) ? "disabled" : "", $this->conteudo);
        if($this->dados_item["sistema"] == '1') {
          $this->conteudo = str_replace("[edita_nome]", "disabled", $this->conteudo);
          $this->conteudo = str_replace("[filtros]", $this->geraFiltrosListagem($this->dados_item["parametros"]["filtros"], false), $this->conteudo);
        } else {
          $this->conteudo = str_replace("[edita_nome]", "", $this->conteudo);
          $this->conteudo = str_replace("[filtros]", $this->geraFiltrosListagem($this->dados_item["parametros"]["filtros"], true), $this->conteudo);
        }
        $this->conteudo = str_replace("[filtros_lista]", @serialize($this->dados_item["parametros"]["filtros"]), $this->conteudo);
        $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
        $this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" value="Aprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=aprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" value="Reprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" value="Cancelar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" value="Ativar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        // Trata Dados
        if (in_array($this->dados_item["status_id"], array(4, 5, 6, 7))) {
          $_POST["inicio"] = $this->dados_item["inicio"];
          $_POST["termino"] = $this->dados_item["termino"];
        }
        $_POST["nome"] = $_POST["nome"] ? $_POST["nome"] : $this->dados_item["nome"];
        // Registra Comentário
        //if ($_POST["comentarios"] || $_POST["nome"] != $this->dados_item["nome"] || $_POST["inicio"] != $this->dados_item["inicio"] || $_POST["termino"] != $this->dados_item["termino"] || $this->dados_item["parametros"]["filtros"] != @unserialize(stripslashes($_POST["filtros_lista"]))) {
        if ($_POST["comentarios"]) {
          $this->registraComentario($_GET["i"], $_POST["comentarios"], 2 , $_SESSION["usuario"]);
        }
        $_POST["inicio"] = ($_POST["inicio"]) ? "'".substr($_POST["inicio"], 6, 4)."-".substr($_POST["inicio"], 3, 2)."-".substr($_POST["inicio"], 0, 2)."'" : "NULL";
        $_POST["termino"] = ($_POST["termino"]) ? "'".substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)."'" : "NULL";
        $parametros = $this->dados_item["parametros"];
        $parametros["filtros"] = @unserialize(stripslashes($_POST["filtros_lista"]));
        $parametros = @serialize($parametros);
        // Atualiza Registro
        $query = "UPDATE tab_mensagens_lotes SET mel_nome = '{$_POST["nome"]}', mel_data_inicio = {$_POST["inicio"]}, mel_data_termino = {$_POST["termino"]}, mel_parametros = '$parametros' WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  #############################
  ### Editar Dados de Envio ###
  #############################

  function editar_de() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      if (empty($_POST)) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.editar_de.inc.php");
        // Action
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_de&i={$this->dados_item["id"]}", $this->conteudo);
        if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"]) $acoes  = '<td bgcolor="#828282" width="50%"><b>&nbsp;<a href="?m='.$this->dados_modulo["nome"].'&a=ver_dc&i='.$this->dados_item["id"].'" class="txtBranco11">Dados Cadastrais</a>&nbsp;</b></td>';
        // Ações
        $acoes .= '<td bgcolor="#505050" width="50%"><b>&nbsp;Dados de Envio&nbsp;</b></td>';
        $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
        // Conteúdo
        $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"]), $this->conteudo);
        $this->conteudo = str_replace("[modelos]", $this->geraSelect($this->buscaModelos(), $this->dados_item["modelo_id"]), $this->conteudo);
        if ($this->permissoes["mensagens_lotes_modelos"]["editar"]) {
          $this->conteudo = str_replace("[ver_modelo]", '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onclick="verModelo(this.form.modelo.value)">', $this->conteudo);
        } elseif ($this->permissoes["mensagens_lotes_modelos"]["ver"]) {
          $this->conteudo = str_replace("[ver_modelo]", '<input type="button" name="ver" class="buttonAzulEscuroBranco11" value="Ver" onclick="verModelo(this.form.modelo.value)">', $this->conteudo);
        } else {
          $this->conteudo = str_replace("[ver_modelo]", "", $this->conteudo);
        }
        $this->conteudo = str_replace("[destinos]", $this->geraSelect($this->buscaDestinos(), $this->dados_item["parametros"]["envio"]["destino"], true), $this->conteudo);
        $this->conteudo = str_replace("[mostra_conteudo_tipo_1]", ($this->dados_item["tipo_id"] == 1) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
        $this->conteudo = str_replace("[mostra_conteudo_tipo_2]", ($this->dados_item["tipo_id"] == 2) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
        $this->conteudo = str_replace("[remetente]", $this->dados_item["parametros"]["envio"]["remetente"], $this->conteudo);
        $this->conteudo = str_replace("[remetente_nome]", $this->dados_item["parametros"]["envio"]["remetente_nome"], $this->conteudo);
        $this->conteudo = str_replace("[remetente_email]", $this->dados_item["parametros"]["envio"]["remetente_email"], $this->conteudo);
        $this->conteudo = str_replace("[cc_nome]", $this->dados_item["parametros"]["envio"]["cc_nome"], $this->conteudo);
        $this->conteudo = str_replace("[cc_email]", $this->dados_item["parametros"]["envio"]["cc_email"], $this->conteudo);
        $this->conteudo = str_replace("[bcc_nome]", $this->dados_item["parametros"]["envio"]["bcc_nome"], $this->conteudo);
        $this->conteudo = str_replace("[bcc_email]", $this->dados_item["parametros"]["envio"]["bcc_email"], $this->conteudo);
        $this->conteudo = str_replace("[reply_nome]", $this->dados_item["parametros"]["envio"]["reply_nome"], $this->conteudo);
        $this->conteudo = str_replace("[reply_email]", $this->dados_item["parametros"]["envio"]["reply_email"], $this->conteudo);
        $this->conteudo = str_replace("[assunto]", $this->dados_item["parametros"]["envio"]["assunto"], $this->conteudo);
        $this->conteudo = str_replace("[envio_quantidade]", $this->dados_item["parametros"]["envio"]["quantidade"], $this->conteudo);
        $this->conteudo = str_replace("[envio_intervalo]", $this->dados_item["parametros"]["envio"]["intervalo"], $this->conteudo);
        $this->conteudo = str_replace("[envio_intervalo_tipo]", $this->geraSelect($this->buscaTiposIntervalo(), $this->dados_item["parametros"]["envio"]["intervalo_tipo"], true), $this->conteudo);
        $this->conteudo = str_replace("[filtros_lista]", @serialize($this->dados_item["parametros"]["filtros"]), $this->conteudo);
        $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" value="Aprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=aprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" value="Reprovar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" value="Cancelar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" value="Ativar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'" class="buttonAzulEscuroBranco11">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        $parametros["envio"]["remetente_nome"] = $_POST["remetente_nome"];
        $parametros["envio"]["remetente_email"] = $_POST["remetente_email"];
        if ($_POST["tipo"] == 1) {
          if ($_POST["cc_email"]) {
            $parametros["envio"]["cc_nome"] = $_POST["cc_nome"];
            $parametros["envio"]["cc_email"] = $_POST["cc_email"];
          }
          if ($_POST["bcc_email"]) {
            $parametros["envio"]["bcc_nome"] = $_POST["bcc_nome"];
            $parametros["envio"]["bcc_email"] = $_POST["bcc_email"];
          }
          if ($_POST["reply_email"]) {
            $parametros["envio"]["reply_nome"] = $_POST["reply_nome"];
            $parametros["envio"]["reply_email"] = $_POST["reply_email"];
          }
          if ($_POST["assunto"]) {
            $parametros["envio"]["assunto"] = $_POST["assunto"];
          }
        } else {
          $parametros["envio"]["remetente"] = $_POST["remetente"];
        }
        $parametros["envio"]["destino"] = $_POST["destino"];
        $parametros["envio"]["quantidade"] = $_POST["envio_quantidade"] + 0;
        $parametros["envio"]["intervalo"] = $_POST["envio_intervalo"] ? $_POST["envio_intervalo"] + 0 : null;
        $parametros["envio"]["intervalo_tipo"] =  $_POST["envio_intervalo"] ? $_POST["envio_intervalo_tipo"] + 0 : null;
        $parametros["filtros"] = @unserialize(stripslashes($_POST["filtros_lista"]));
        $parametros = @serialize($parametros);
        $query = "UPDATE tab_mensagens_lotes SET mlt_id = {$_POST["tipo"]}, mel_parametros = '$parametros', mlm_id = {$_POST["modelo"]} WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Comentário
        if ($_POST["comentarios"]) {
          $this->registraComentario($_GET["i"], $_POST["comentarios"], 2 , $_SESSION["usuario"]);
        }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_de&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehRemovivel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.confirmacao.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[acao]', "remover", $this->conteudo);
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      } else {
        // Remove Comentários
        $query = "DELETE FROM tab_mensagens_lotes_comentarios WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Remove Lote
        $query = "DELETE FROM tab_mensagens_lotes WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ###############
  ### Aprovar ###
  ###############

  function aprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehAprovavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.confirmacao.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[acao]', "aprovar", $this->conteudo);
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      } else {
        // Atualiza Registro
        $query = "UPDATE tab_mensagens_lotes SET mls_id = 2 WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Comentário
        $this->registraComentario($_GET["i"], $_POST["comentario"], 3, $_SESSION["usuario"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ################
  ### Reprovar ###
  ################

  function reprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehReprovavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.confirmacao.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[acao]', "reprovar", $this->conteudo);
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      } else {
        // Atualiza Registro
        $query = "UPDATE tab_mensagens_lotes SET mls_id = 3 WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Comentário
        $this->registraComentario($_GET["i"], $_POST["comentario"], 4, $_SESSION["usuario"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ##############
  ### Ativar ###
  ##############

  function ativar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehAtivavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.confirmacao.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[acao]', "ativar", $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      } else {
        // Atualiza Registro
        $query = "UPDATE tab_mensagens_lotes SET mls_id = 5, mel_data_inicio = IF(mel_data_inicio IS NULL OR mel_data_inicio < CURDATE(), CURDATE(), mel_data_inicio) WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Comentário
        $this->registraComentario($_GET["i"], $_POST["comentario"], 5, $_SESSION["usuario"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ################
  ### Cancelar ###
  ################

  function cancelar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehCancelavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes.confirmacao.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[acao]', "cancelar", $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
      } else {
        // Atualiza Registro
        $query = "UPDATE tab_mensagens_lotes SET mls_id = IF(mls_id < 5, 4, 6), mel_data_termino = IF(mls_id < 5, mel_data_termino, CURDATE()) WHERE mel_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Comentário
        $this->registraComentario($_GET["i"], $_POST["comentario"], 6, $_SESSION["usuario"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    if (!$_GET["f"] && !$_GET["p"]) {
      $_GET["f"]["status"] = 5; // Ativo
    }
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
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "tab_mensagens_lotes.mel_nome LIKE '%[valor]%'");
    // Tipos
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "tab_mensagens_lotes.mlt_id = [valor]");
    // Modelos
    $modelos = $this->buscaModelos();
    foreach ($modelos as $id => $nome) {
      $select_modelos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "modelo", "descricao" => "Modelo", "valor" => $select_modelos, "where" => "tab_mensagens_lotes.mlm_id = [valor]");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "tab_mensagens_lotes.mls_id = [valor]");

    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}", 5);
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
        tab_mensagens_lotes.mel_id AS id,
        tab_mensagens_lotes.mel_nome AS nome,
        tab_mensagens_lotes_status.mls_nome AS status,
        tab_mensagens_lotes_tipos.mlt_nome AS tipo,
        tab_mensagens_lotes_modelos.mlm_nome AS modelo
      FROM
        tab_mensagens_lotes
        INNER JOIN tab_mensagens_lotes_status ON (tab_mensagens_lotes.mls_id = tab_mensagens_lotes_status.mls_id)
        INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
        INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens_lotes.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        tab_mensagens_lotes.mel_nome,
        tab_mensagens_lotes_tipos.mlt_nome,
        tab_mensagens_lotes_status.mls_nome
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["mensagens_lotes"]["ver_dc"]) {
        $dados["link"] = "?m=mensagens_lotes&a=ver_dc&i={$dados["id"]}";
      }
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "nome" => "Nome", "tipo" => "Tipo", "modelo" => "Modelo", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right", "left"));
    return $listagem;
  }

}

?>
