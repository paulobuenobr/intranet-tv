<?

class operacoes_contatos {

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

  function buscaCategorias () {
    $query = "SELECT occ_id AS id, CONCAT(oli_nome, ' » ', occ_nome) AS nome FROM tab_operacoes_contatos_categorias INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_contatos_categorias.oli_id = tab_operacoes_lotes_sistemas.oli_id) ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$categorias[$dados["id"]] = $dados["nome"];
    }
    return $categorias;
  }

  function buscaResponsaveis($usuario_id) {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
        tab_usuarios.usu_id = $usuario_id OR
        tab_usuarios.usu_id IN (SELECT DISTINCT usu_id FROM tab_operacoes_contatos)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }
  
  function buscaLotes () {
    $query = "SELECT DISTINCT tab_operacoes_lotes.opl_id AS id, tab_operacoes_lotes.opl_nome AS nome FROM tab_operacoes INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$lotes[$dados["id"]] = $dados["nome"];
    }
    return $lotes;
  }

  #############
  ### Geral ###
  #############

  function geraSelect ($itens, $item_selecionado = "", $item_nulo = "") {
    if (sizeof($itens)>0) {
      if ($item_nulo) $select .= '<option></option>';
      do {
        $item = @current($itens);
        (@key($itens) == $item_selecionado) ? $complemento = " selected" : $complemento = "";
        $select .= '<option value="' . @key($itens) . '" ' . $complemento . '>' . $item . '</option>';
      } while (@next($itens));
    }
    return $select;
  }

  function formataData($data) {
    return "'".substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2)."'";
  }

  ##########################
  ######### Listar #########
  ##########################

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
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
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio", "descricao" => "Data &raquo; Início", "valor" => "", "where" => "(tab_operacoes_contatos.opc_inicio >= ".$this->formataData($valores["data_inicio"]).")");
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_termino", "descricao" => "Data &raquo; Término", "valor" => "", "where" => "(tab_operacoes_contatos.opc_termino <= DATE_SUB(DATE_ADD(".$this->formataData($valores["data_termino"]).", INTERVAL 1 DAY), INTERVAL 1 SECOND))");
    // Categoria
    $categorias = $this->buscaCategorias();
    foreach ($categorias as $id => $nome) {
      $select_categorias[] = array ("id" => $id, "nome" => $nome);
    }
    /*$select_categorias[] = array ("id" => "-1", "nome" => "Recuperação » Não Atende", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 1 AND 3)");
    $select_categorias[] = array ("id" => "-2", "nome" => "Recuperação » Ocupado", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 4 AND 6)");
    $select_categorias[] = array ("id" => "-3", "nome" => "Recuperação » Não Está", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 7 AND 9)");
    $select_categorias[] = array ("id" => "-4", "nome" => "Recuperação » Já Pagou", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 10 AND 12)");
    $select_categorias[] = array ("id" => "-5", "nome" => "Recuperação » Vai Pagar", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 13 AND 15)");*/
    $select_categorias[] = array ("id" => "-6", "nome" => "Prospecção » Não Atende", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 19 AND 21)");
    $select_categorias[] = array ("id" => "-7", "nome" => "Prospecção » Ocupado", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 22 AND 24)");
    $select_categorias[] = array ("id" => "-8", "nome" => "Prospecção » Sem Interesse", "where" => "(tab_operacoes_contatos.occ_id BETWEEN 25 AND 29)");
    $select_categorias[] = array ("id" => "-9", "nome" => "Prospecção » Erro", "where" => "(tab_operacoes_contatos.occ_id IN (30, 31))");
    $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "(tab_operacoes_contatos.occ_id = [valor])");
    // Lote
    $lotes = $this->buscaLotes();
    foreach ($lotes as $id => $nome) {
      $select_lotes[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "lote", "descricao" => "Lote", "valor" => $select_lotes, "where" => "(tab_operacoes.opl_id = [valor])");
    // Sistema
    $sistemas = $this->buscaSistemas();
    foreach ($sistemas as $id => $nome) {
      $select_sistemas[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema", "valor" => $select_sistemas, "where" => "(tab_operacoes_lotes.oli_id = [valor])");
    // Responsável
    $responsaveis = $this->buscaResponsaveis($_SESSION["usuario"]);
    foreach ($responsaveis as $id => $nome){
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "responsavel", "descricao" => "Responsável", "valor" => $select_responsaveis, "where" => "(tab_operacoes_contatos.usu_id = [valor])", "disabled" => ($this->permissoes[$this->dados_modulo["nome"]]["listar_todos"])? false : true);
    if (!$this->permissoes[$this->dados_modulo["nome"]]["listar_todos"]) {
    	$valores["responsavel"] = $_SESSION["usuario"];
    }
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
        tab_operacoes_contatos.opc_id AS id,
        tab_clientes.cli_nome AS nome,
        DATE_FORMAT(tab_operacoes_contatos.opc_inicio,'%d/%m/%Y (%H:%i)') AS data_hora,
        SEC_TO_TIME(UNIX_TIMESTAMP(tab_operacoes_contatos.opc_termino)-UNIX_TIMESTAMP(tab_operacoes_contatos.opc_inicio)) AS duracao,
        tab_operacoes_lotes.opl_nome AS lote,
        tab_operacoes_contatos_categorias.occ_nome AS categoria,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_clientes
        INNER JOIN tab_operacoes_contatos ON (tab_clientes.cli_id = tab_operacoes_contatos.cli_id)
        INNER JOIN tab_operacoes ON (tab_operacoes_contatos.ope_id = tab_operacoes.ope_id)
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)
        INNER JOIN tab_operacoes_contatos_categorias ON (tab_operacoes_contatos.occ_id = tab_operacoes_contatos_categorias.occ_id)
        INNER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_operacoes_contatos.usu_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        tab_operacoes_contatos.opc_inicio ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem ($itens) {
    $cabecalho = array ("contador" => "#", "data_hora" => "Data/Hora", "duracao" => "Duração", "nome" => "Nome", "categoria" => "Categoria", "lote" => "Lote", "responsavel" => "Responsável");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

}

?>
