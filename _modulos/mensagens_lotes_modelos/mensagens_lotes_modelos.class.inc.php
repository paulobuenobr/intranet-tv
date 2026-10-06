<?

require_once("_global/modulo.class.inc.php");

class mensagens_lotes_modelos extends modulo {

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

	function fechar() {
    $this->conteudo  = '<script type="text/javascript">if (window.opener && window.opener.atualizarListagem) window.opener.atualizarListagem();</script>';
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_mensagens_lotes_modelos.mlm_id AS id,
        tab_mensagens_lotes_modelos.mlm_nome AS nome,
        tab_mensagens_lotes_modelos.mlm_conteudo_html AS conteudo_html,
        tab_mensagens_lotes_modelos.mlm_conteudo_texto AS conteudo_texto,
        tab_mensagens_lotes_modelos.mlm_versao AS versao,
        tab_mensagens_lotes_modelos.mlt_id AS tipo_id,
        tab_mensagens_lotes_tipos.mlt_nome AS tipo_nome
      FROM
        tab_mensagens_lotes_modelos
        INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes_modelos.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
       WHERE
        tab_mensagens_lotes_modelos.mlm_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  ############
  ## BUSCAS ##
  ############

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

  function mensagemModeloEhUltilizado($id) {
    $query = "
      SELECT
        mel_id AS id
      FROM
        tab_mensagens
      WHERE
        mlm_id = $id
      LIMIT 0,1";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultadoArray($query)) {
      return true;
    } else {
      return false;
    }
  }

  function buscaUltimaVersao($modelo_id) {
    $query = "
      SELECT
        MAX(mlm_versao) AS versao
      FROM
        tab_mensagens_lotes_modelos
      WHERE
        mlm_id = $modelo_id OR
        mlm_pai = $modelo_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["versao"];
    }
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("mensagens_lotes_modelos.adicionar.inc.php");
      // Action
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}", $this->conteudo);
      // Conteúdo
      $this->conteudo = str_replace("[tipos]", $this->geraSelect($this->buscaTipos(), null, true, "tipo", "200px", "mudaTipo(this.value)"), $this->conteudo);
      $this->conteudo = str_replace("[versao]", "1", $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="fechar" value="Fechar" class="buttonAzulEscuroBranco11" onclick="window.close()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      $_POST["conteudo_html"] = ($_POST["tipo"] == 1) ? "'{$_POST["conteudo_html"]}'" : "NULL";
      $query = "INSERT INTO tab_mensagens_lotes_modelos (mlm_nome, mlm_conteudo_html, mlm_conteudo_texto, mlt_id, mlm_versao) VALUES ('{$_POST["nome"]}', {$_POST["conteudo_html"]}, '{$_POST["conteudo_texto"]}', {$_POST["tipo"]}, 1)";
      $this->bancodedados->query($query);
      // Atividades
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
      $id = $this->bancodedados->ultimoIdInserido();
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("mensagens_lotes_modelos.ver.inc.php");
      // Conteudo
      if ($this->dados_item["tipo_id"] == 1) {
        $this->conteudo = str_replace("[mostra_conteudo_html]", ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group"), $this->conteudo);
      } else {
        $this->conteudo = str_replace("[mostra_conteudo_html]", "none", $this->conteudo);
      }
      $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[versao]", $this->dados_item["versao"], $this->conteudo);
      $this->conteudo = str_replace("[conteudo_html]", $this->dados_item["conteudo_html"], $this->conteudo);
      $this->conteudo = str_replace("[conteudo_texto]", $this->dados_item["conteudo_texto"], $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\'">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && !$this->mensagemModeloEhUltilizado($_GET["i"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'">';
      $botoes[] = '<input type="button" name="fechar" value="Fechar" class="buttonAzulEscuroBranco11" onclick="window.close()">';
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
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes_modelos.editar.inc.php");
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
        if ($this->dados_item["tipo_id"] == 1) {
          $this->conteudo = str_replace("[mostra_conteudo_html]", ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group"), $this->conteudo);
        } else {
          $this->conteudo = str_replace("[mostra_conteudo_html]", "none", $this->conteudo);
        }
        $tipos = $this->buscaTipos();
        $this->conteudo = str_replace("[modelo_id]", $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace("[tipo]", $tipos[$this->dados_item["tipo_id"]], $this->conteudo);
        $this->conteudo = str_replace("[versao]", $this->dados_item["versao"], $this->conteudo);
        $this->conteudo = str_replace("[conteudo_html]", htmlspecialchars($this->dados_item["conteudo_html"]), $this->conteudo);
        $this->conteudo = str_replace("[conteudo_texto]", $this->dados_item["conteudo_texto"], $this->conteudo);
        // Botões
        if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="submit" name="Salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && !$this->mensagemModeloEhUltilizado($_GET["i"])) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"].'\';">';
        $botoes[] = '<input type="button" name="fechar" value="Fechar" class="buttonAzulEscuroBranco11" onclick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        $_POST["conteudo_html"] = $_POST["conteudo_html"] ? "'{$_POST["conteudo_html"]}'" : "NULL";
        if ($this->mensagemModeloEhUltilizado($_GET["i"])) {
          $versao = $this->buscaUltimaVersao($_GET["i"]) + 1;
          $query = "INSERT INTO tab_mensagens_lotes_modelos (mlm_nome, mlm_conteudo_html, mlm_conteudo_texto, mlt_id, mlm_versao) VALUES ('{$this->dados_item["nome"]}', {$_POST["conteudo_html"]}, '{$_POST["conteudo_texto"]}', {$this->dados_item["tipo_id"]}, $versao)";
          $this->bancodedados->query($query);
          $id = $this->bancodedados->ultimoIdInserido();
        } else {
          $query = "UPDATE tab_mensagens_lotes_modelos SET mlm_conteudo_html = {$_POST["conteudo_html"]} , mlm_conteudo_texto = '{$_POST["conteudo_texto"]}' WHERE mlm_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          $id = $this->dados_item["id"];
        }
        // Registra Atividades
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
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
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("mensagens_lotes_modelos.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" value="Fechar" class="buttonAzulEscuroBranco11" onclick="window.close()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}&cliente_id={$this->dados_item["cliente_id"]}", $this->conteudo);
      } else {
        $query = "DELETE FROM tab_mensagens_lotes_modelos WHERE mlm_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        $this->fechar();
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<script type="text/javascript">var dados_get = \''.@serialize($_GET).'\'</script>';
    $this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=mensagens_lotes_modelos" id="mensagens_lotes_modelos.js"></script>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onclick="abrir(\'mensagens_lotes_modelos\', \'?m=mensagens_lotes_modelos&a=adicionar\', 620, 520, 1)">';
    $this->conteudo .= $filtro->formulario;
    if ($_GET["f"] || $_GET["p"]) {
      $itens = $this->buscaItens($filtro, $pagina);
      $paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
      $listagem = $this->geraListagem($itens["itens"]);
      $this->conteudo .= '</td></tr></table>';
      $this->conteudo .= '<span id="paginacao_1">'.$paginacao->conteudo.'</span>';
      $this->conteudo .= '<span id="listagem">'.$listagem->conteudo.'</span>';
      $this->conteudo .= '<span id="paginacao_2">'.$paginacao->conteudo.'</span>';
    } else {
      $this->conteudo .= '</td></tr></table>';
    }
  }

  function inicializaFiltro($valores = "") {
    // Nome
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "tab_mensagens_lotes_modelos.mlm_nome LIKE '[valor]'");
    // Tipos
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_dados[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_dados, "where" => "tab_mensagens_lotes_modelos.mlt_id = [valor]");
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
        tab_mensagens_lotes_modelos.mlm_id AS id,
        tab_mensagens_lotes_modelos.mlm_nome AS nome,
        tab_mensagens_lotes_modelos.mlm_versao AS versao,
        tab_mensagens_lotes_modelos.mlt_id AS tipo_id,
        tab_mensagens_lotes_tipos.mlt_nome AS tipo_nome
      FROM
        tab_mensagens_lotes_modelos
        INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes_modelos.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        nome, versao
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["mensagens_lotes_modelos"]["ver"]) {
        $dados["link"] = 'javascript:abrir(\'mensagens_lotes_modelos\', \'?m=mensagens_lotes_modelos&a=ver&i='.$dados["id"].' \', 620, 520, 1)';
      }
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "nome" => "Nome", "tipo_nome" => "Tipo", "versao" => "Versão");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

}

?>
