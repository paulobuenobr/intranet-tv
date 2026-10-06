<?

class operacoes {

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
        tab_operacoes.ope_id AS id,
        DATE_FORMAT(tab_operacoes.ope_inicio, '%d/%m/%Y %H:%i:%s') AS inicio,
        DATE_FORMAT(tab_operacoes.ope_termino, '%d/%m/%Y %H:%i:%s') AS termino,
        tab_operacoes_lotes.opl_nome AS nome,
        tab_operacoes_lotes_sistemas.oli_id AS sistema_id,
        tab_operacoes_lotes_sistemas.oli_nome AS sistema_nome,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_operacoes
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)
        INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_lotes.oli_id = tab_operacoes_lotes_sistemas.oli_id)
        INNER JOIN tab_usuarios ON (tab_operacoes.usu_id = tab_usuarios.usu_id)
      WHERE
        ope_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaPeriodos() {
    $query = "
      SELECT
        olp_id AS id,
        olp_nome AS nome
      FROM
        tab_operacoes_lotes_periodos
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $periodos[$dados["id"]] = $dados["nome"];
    }
    return $periodos;
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
        tab_usuarios.usu_id IN (SELECT DISTINCT usu_id FROM tab_operacoes)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }

  function buscaLotes() {
    $query = "
      SELECT DISTINCT
        tab_operacoes_lotes.opl_id AS id,
        tab_operacoes_lotes.opl_nome AS nome
      FROM
        tab_operacoes
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lotes[$dados["id"]] = $dados["nome"];
    }
    return $lotes;
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

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("operacoes.ver.inc.php");
      // Dados
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[sistema]', $this->dados_item["sistema_nome"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[operacoes]', ($this->dados_item["sistema_id"] == 1) ? $this->geraListagemOperacoesProspeccao($this->dados_item["id"]) : $this->geraListagemOperacoesRecuperacao($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel"], $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao,'id='.$this->dados_item["id"]);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  function geraListagemOperacoesProspeccao($id) {
    $itens[] = $this->buscaQtdeContatosProspeccao($id);
    $cabecalho = array ("na" => "<center><span onMouseOver=\"hintShow('Não Atende', 80);\" onMouseOut=\"hintHide();\">NA</span></center>", "oc" => "<span onMouseOver=\"hintShow('Ocupado', 80);\" onMouseOut=\"hintHide();\">OC</span>", "si" => "<span onMouseOver=\"hintShow('Sem Interesse', 80);\" onMouseOut=\"hintHide();\">SI</span>", "ci" => "<span onMouseOver=\"hintShow('Com Interesse', 80);\" onMouseOut=\"hintHide();\">CI</span>", "jcp" => "<span onMouseOver=\"hintShow('Já é Cliente e/ou Prospect', 160);\" onMouseOut=\"hintHide();\">JCP</span>", "er" => "<span onMouseOver=\"hintShow('Erro', 80);\" onMouseOut=\"hintHide();\">ER</span>", "total" => "<span onMouseOver=\"hintShow('Total', 80);\" onMouseOut=\"hintHide();\">TT</span>");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function geraListagemOperacoesRecuperacao($id) {
    $itens[] = $this->buscaQtdeContatosRecuperacao($id);
    $cabecalho = array ("ca" => "<center><span onMouseOver=\"hintShow('Cliente Atendeu', 160);\" onMouseOut=\"hintHide();\">CA</span></center>", "cna" => "<span onMouseOver=\"hintShow('Cliente não Atendeu', 160);\" onMouseOut=\"hintHide();\">CNA</span>", "tne" => "<span onMouseOver=\"hintShow('Telefone não Existe', 160);\" onMouseOut=\"hintHide();\">TNE</span>", "outro_contato" => "<span onMouseOver=\"hintShow('Outro Contato', 80);\" onMouseOut=\"hintHide();\">OC</span>", "outro" => "<span onMouseOver=\"hintShow('Outro', 80);\" onMouseOut=\"hintHide();\">OU</span>", "total" => "<span onMouseOver=\"hintShow('Total', 80);\" onMouseOut=\"hintHide();\">TT</span>");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function buscaQtdeContatosProspeccao($operacao_id) {
    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND (occ_id >= 19 AND occ_id <= 21)";
    $query = $this->bancodedados->query($query);
    $estatisticas["na"] = "<center>".intval($this->bancodedados->resultado($query, 0, "contatos"))."</center>";
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND (occ_id >= 22 AND occ_id <= 24)";
    $query = $this->bancodedados->query($query);
    $estatisticas["oc"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND (occ_id >= 25 AND occ_id <= 29)";
    $query = $this->bancodedados->query($query);
    $estatisticas["si"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 32";
    $query = $this->bancodedados->query($query);
    $estatisticas["ci"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 33";
    $query = $this->bancodedados->query($query);
    $estatisticas["jcp"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id IN (30, 31)";
    $query = $this->bancodedados->query($query);
    $estatisticas["er"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));
    return $estatisticas;
  }

  function buscaQtdeContatosRecuperacao($operacao_id) {
    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 1";
    $query = $this->bancodedados->query($query);
    $estatisticas["ca"] = "<center>".intval($this->bancodedados->resultado($query,0,"contatos"))."</center>";
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 2";
    $query = $this->bancodedados->query($query);
    $estatisticas["cna"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 3";
    $query = $this->bancodedados->query($query);
    $estatisticas["tne"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 4";
    $query = $this->bancodedados->query($query);
    $estatisticas["outro"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 5";
    $query = $this->bancodedados->query($query);
    $estatisticas["outro_contato"] = intval($this->bancodedados->resultado($query, 0, "contatos"));
    $estatisticas["total"] += intval($this->bancodedados->resultado($query, 0, "contatos"));
    return $estatisticas;
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
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
    // Lotes
    $lotes = $this->buscaLotes();
    foreach ($lotes as $id => $nome) {
      $select_lotes[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "lote", "descricao" => "Lote", "valor" => $select_lotes, "where" => "(tab_operacoes.opl_id = [valor])");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio", "descricao" => "Data &raquo; Início", "valor" => "", "where" => "(tab_operacoes.ope_inicio >= ".$this->formataData($valores["data_inicio"]).")");
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_termino", "descricao" => "Data &raquo; Término", "valor" => "", "where" => "(tab_operacoes.ope_termino <= DATE_SUB(DATE_ADD(".$this->formataData($valores["data_termino"]).", INTERVAL 1 DAY), INTERVAL 1 SECOND))");
    // Responsável
    $responsaveis = $this->buscaResponsaveis($_SESSION["usuario"]);
    foreach ($responsaveis as $id => $nome) {
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "responsavel", "descricao" => "Responsável", "valor" => $select_responsaveis, "where" => "(tab_operacoes.usu_id = [valor])", "disabled" => ($this->permissoes[$this->dados_modulo["nome"]]["listar_todos"])? false : true);
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
        tab_operacoes.ope_id AS id,
        DATE_FORMAT(tab_operacoes.ope_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
        SEC_TO_TIME(UNIX_TIMESTAMP(tab_operacoes.ope_termino) - UNIX_TIMESTAMP(tab_operacoes.ope_inicio)) AS duracao,
        tab_operacoes_lotes.opl_nome AS lote,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_operacoes
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)
        INNER JOIN tab_operacoes_lotes_periodos ON (DATE_FORMAT(tab_operacoes.ope_inicio,'%H%i') >= tab_operacoes_lotes_periodos.olp_inicio AND DATE_FORMAT(tab_operacoes.ope_inicio,'%H%i') < tab_operacoes_lotes_periodos.olp_termino)
        INNER JOIN tab_usuarios ON (tab_operacoes.usu_id = tab_usuarios.usu_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        tab_operacoes.ope_inicio ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["contatos"] = $this->geraOperacaoEstatisticas($dados["id"]);
      if ($dados["contatos"]) {
        if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
          $dados["link"] = "javascript:verOperacao('{$dados["id"]}');";
        }
        $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
        $itens[] = $dados;
      } else {
        $linhas--;
      }
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraOperacaoEstatisticas($operacao) {
    $query = "
      SELECT
        COUNT(*) AS contatos
      FROM
        tab_operacoes_contatos
      WHERE
        ope_id = $operacao";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "contatos");
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "lote" => "Lote", "data_hora" => "Data/Hora", "duracao" => "Duração", "contatos" => "Contatos", "responsavel" => "Responsável");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

}

?>
