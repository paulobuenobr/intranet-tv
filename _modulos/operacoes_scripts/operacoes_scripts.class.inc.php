<?

require_once("_global/modulo.class.inc.php");

class operacoes_scripts extends modulo {

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

  function buscaDadosItem ($id) {
    $query = "
      SELECT
        tab_operacoes_scripts.ops_id AS id,
        tab_operacoes_scripts.ops_titulo AS titulo,
        tab_operacoes_scripts.ops_conteudo AS conteudo,
        tab_operacoes_lotes_sistemas.oli_id AS sistema_id,
        tab_operacoes_lotes_sistemas.oli_nome AS sistema_nome,
        tab_operacoes_scripts_tipos.ost_id AS tipo_id,
        tab_operacoes_scripts_tipos.ost_nome AS tipo_nome,
        tab_operacoes_scripts_formatos.osf_id AS formato_id,
        tab_operacoes_scripts_formatos.osf_nome AS formato_nome,
        tab_operacoes_scripts_status.osa_id AS status_id,
        tab_operacoes_scripts_status.osa_nome AS status_nome
      FROM
        tab_operacoes_scripts
        INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_scripts.oli_id = tab_operacoes_lotes_sistemas.oli_id)
        INNER JOIN tab_operacoes_scripts_tipos ON (tab_operacoes_scripts.ost_id = tab_operacoes_scripts_tipos.ost_id)
        INNER JOIN tab_operacoes_scripts_status ON (tab_operacoes_scripts.osa_id = tab_operacoes_scripts_status.osa_id)
        INNER JOIN tab_operacoes_scripts_formatos ON (tab_operacoes_scripts.osf_id = tab_operacoes_scripts_formatos.osf_id)
      WHERE
        tab_operacoes_scripts.ops_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaSistemas () {
    $query = "SELECT oli_id AS id, oli_nome AS nome FROM tab_operacoes_lotes_sistemas ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$sistemas[$dados["id"]] = $dados["nome"];
    }
    return $sistemas;
  }

  function buscaTipos () {
    $query = "SELECT ost_id AS id, ost_nome AS nome FROM tab_operacoes_scripts_tipos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaFormatos () {
    $query = "SELECT osf_id AS id, osf_nome AS nome FROM tab_operacoes_scripts_formatos ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$formatos[$dados["id"]] = $dados["nome"];
    }
    return $formatos;
  }

  function buscaStatus () {
    $query = "SELECT osa_id AS id, osa_nome AS nome FROM tab_operacoes_scripts_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$statuss[$dados["id"]] = $dados["nome"];
    }
    return $statuss;
  }

  #############
  ### Geral ###
  #############

  function limpaConteudoHTML ($conteudo) {
    // Limpa Tags
    $tags_permitidas = array ("b", "u", "i", "p", "br", "table", "tr", "td");
    preg_match_all('/<([\w]+)[^>]*>/', $conteudo, $tags_existentes);
    $tags_existentes = array_unique($tags_existentes[1]);
    sort($tags_existentes);
    for ($i = 0; $i < sizeof($tags_existentes); $i++) {
    	if (!in_array($tags_existentes[$i], $tags_permitidas)) {
	      $conteudo = preg_replace("/<{$tags_existentes[$i]} [^>]*>/", "", $conteudo);
	      $conteudo = str_replace("</{$tags_existentes[$i]}>", "", $conteudo);
    	}
    }
    // Limpa Parametros
    $parametros_permitidos = array("colspan", "rowspan", "border", "cellpadding", "cellspacing", "align");
    preg_match_all('/([\w]+)="[^"]+"/', $conteudo, $parametros_existentes);
    $parametros_existentes = @array_unique($parametros_existentes[1]);
    sort($parametros_existentes);
    for ($i = 0; $i < sizeof($parametros_existentes); $i++) {
    	if (!in_array($parametros_existentes[$i], $parametros_permitidos)) {
    		$conteudo = preg_replace("/{$parametros_existentes[$i]}=\"[^\"]+\"/", "", $conteudo);
    	}
    }
    // Adiciona Estilos
    for ($i = 0; $i < sizeof($tags_permitidas); $i++) {
    	$conteudo = str_replace("<{$tags_permitidas[$i]} ", "<{$tags_permitidas[$i]} class=\"txtAzulEscuro11\" ", $conteudo);
    }
    $conteudo = preg_replace("/ +/", " ", $conteudo);
    return $conteudo;
  }

  #############################
  ######### Adicionar #########
  #############################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("operacoes_scripts.adicionar.inc.php");
      // Dados
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
      $this->conteudo = str_replace('[sistema]', $this->geraSelect($this->buscaSistemas(), null, true, "sistema"), $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), null, true, "tipo"), $this->conteudo);
      $this->conteudo = str_replace('[formato]', $this->geraSelect($this->buscaFormatos(), null, true, "formato"), $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), null, true, "status"), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      $_POST["titulo"] = trim($_POST["titulo"]);
      $_POST["conteudo"] = ($_POST["formato"] == 2) ? $this->limpaConteudoHTML($_POST["conteudo"]) : trim($_POST["conteudo"]);
      $query = "INSERT INTO tab_operacoes_scripts (ops_titulo, ops_conteudo, oli_id, ost_id, osf_id, osa_id) VALUES ('{$_POST["titulo"]}', '{$_POST["conteudo"]}', {$_POST["sistema"]}, {$_POST["tipo"]}, {$_POST["formato"]}, {$_POST["status"]})";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
    }
  }

  #######################
  ######### Ver #########
  #######################

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("operacoes_scripts.ver.inc.php");
      // Dados;
      $this->conteudo = str_replace('[titulo]', $this->dados_item["titulo"], $this->conteudo);
      $this->conteudo = str_replace('[sistema]', $this->dados_item["sistema_nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[conteudo]', ($this->dados_item["formato_id"] == 1) ? nl2br(htmlentities($this->dados_item["conteudo"])) : $this->dados_item["conteudo"], $this->conteudo);
      $this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##########################
  ######### Editar #########
  ##########################

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("operacoes_scripts.editar.inc.php");
        // Dados
        $this->conteudo = str_replace('[titulo]', $this->dados_item["titulo"], $this->conteudo);
        $this->conteudo = str_replace('[sistema]', $this->geraSelect($this->buscaSistemas(), $this->dados_item["sistema_id"], true, "sistema"), $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true, "tipo"), $this->conteudo);
        $this->conteudo = str_replace('[conteudo]', $this->dados_item["conteudo"], $this->conteudo);
        $this->conteudo = str_replace('[formato]', $this->geraSelect($this->buscaFormatos(), $this->dados_item["formato_id"], true, "formato"), $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"], true, "status"), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        // Action
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $_POST["titulo"] = trim($_POST["titulo"]);
        $_POST["conteudo"] = ($_POST["formato"] == 2) ? $this->limpaConteudoHTML($_POST["conteudo"]) : trim($_POST["conteudo"]);
        $query = "UPDATE tab_operacoes_scripts SET ops_titulo = '{$_POST["titulo"]}', ops_conteudo = '{$_POST["conteudo"]}', oli_id = {$_POST["sistema"]}, ost_id = {$_POST["tipo"]}, osf_id = {$_POST["formato"]}, osa_id = {$_POST["status"]} WHERE ops_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ###########################
  ######### Remover #########
  ###########################

  function remover () {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("operacoes_scripts.remover.inc.php");
        $this->conteudo = str_replace('[titulo]', $this->dados_item["titulo"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "DELETE FROM tab_operacoes_scripts WHERE ops_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##########################
  ######### Listar #########
  ##########################

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
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
    // Título
    $itens[] = array ("tipo" => "input", "nome" => "titulo", "descricao" => "Título", "valor" => "", "where" => "(tab_operacoes_scripts.ops_titulo LIKE '%[valor]%')");
    // Sistemas
    $sistemas = $this->buscaSistemas();
    foreach ($sistemas as $id => $nome) {
      $select_sistemas[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema", "valor" => $select_sistemas, "where" => "(tab_operacoes_lotes_sistemas.oli_id LIKE '[valor]')");
    // Tipos
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "(tab_operacoes_scripts.ost_id LIKE '[valor]')");
    // Formatos
    $formatos = $this->buscaFormatos();
    foreach ($formatos as $id => $nome) {
      $select_formatos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "formato", "descricao" => "Formato", "valor" => $select_formatos, "where" => "(tab_operacoes_scripts.osf_id LIKE '[valor]')");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_operacoes_scripts.osa_id LIKE '[valor]')");
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
        tab_operacoes_scripts.ops_id AS id,
        tab_operacoes_scripts.ops_titulo AS titulo,
        tab_operacoes_lotes_sistemas.oli_nome AS sistema,
        tab_operacoes_scripts_tipos.ost_nome AS tipo,
        tab_operacoes_scripts_formatos.osf_nome AS formato,
        tab_operacoes_scripts_status.osa_nome AS status
      FROM
        tab_operacoes_scripts
        INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_scripts.oli_id = tab_operacoes_lotes_sistemas.oli_id)
        INNER JOIN tab_operacoes_scripts_tipos ON (tab_operacoes_scripts.ost_id = tab_operacoes_scripts_tipos.ost_id)
        INNER JOIN tab_operacoes_scripts_status ON (tab_operacoes_scripts.osa_id = tab_operacoes_scripts_status.osa_id)
        INNER JOIN tab_operacoes_scripts_formatos ON (tab_operacoes_scripts.osf_id = tab_operacoes_scripts_formatos.osf_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
       titulo
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
        $dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
      }
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return $itens;
  }

  function geraListagem ($itens) {
    $cabecalho = array ("contador" => "#", "titulo" => "Título", "sistema" => "Sistema", "tipo" => "Tipo", "formato" => "Formato", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

}

?>
