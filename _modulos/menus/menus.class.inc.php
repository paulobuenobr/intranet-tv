<?php

require_once("_global/modulo.class.inc.php");

class menus extends modulo {

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

	function ehAdmin($id = null) {

		$user = $id ? $id : $_SESSION["usuario"];
		$query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
		$query = $this->bancodedados->query($query);
		$ehAdmin = $this->bancodedados->resultadoArray($query);

		return $ehAdmin["usu_administrador"] == 0 ? false : true;
	}

	function buscaDadosItem($id) {

		$id = intval($id);
		$query = "
			SELECT
				m.men_id AS id,
				m.men_nome AS nome,
				m.men_modulo AS modulo,
				m.men_acao AS acao,
				m.men_url AS url,
				m.men_target AS target,
				m.men_ordem AS ordem,
				m.men_pai AS pai_id,
				CASE
					WHEN p.men_id IS NULL THEN NULL
					WHEN gp.men_id IS NULL THEN p.men_nome
					ELSE CONCAT(gp.men_nome, ' > ', p.men_nome)
				END AS pai_nome
			FROM
				tab_menus m
				LEFT JOIN tab_menus p ON (m.men_pai = p.men_id)
				LEFT JOIN tab_menus gp ON (p.men_pai = gp.men_id)
			WHERE
				m.men_id = $id";

		$query = $this->bancodedados->query($query);
		$this->dados_item = $this->bancodedados->resultadoArray($query);

		if (!$this->dados_item) {
			unset($this->dados_item);
		}
	}

	/**
	 * Menus que podem ser pai (níveis 1 e 2).
	 * Hierarquia máxima: Menu 1 > Menu 2 > Menu 3.
	 */
	function buscaMenusPai($excluir_id = null) {

		$menus = array();
		$excluir_id = $excluir_id ? intval($excluir_id) : null;
		$where_excluir = $excluir_id ? "AND m.men_id != {$excluir_id}" : "";

		$query = $this->bancodedados->query("
			SELECT
				m.men_id AS id,
				CASE
					WHEN p.men_id IS NULL THEN m.men_nome
					ELSE CONCAT(p.men_nome, ' > ', m.men_nome)
				END AS nome,
				IFNULL(p.men_ordem, m.men_ordem) AS ordem_raiz,
				CASE WHEN p.men_id IS NULL THEN 0 ELSE 1 END AS nivel_pai,
				m.men_ordem AS ordem
			FROM
				tab_menus m
				LEFT JOIN tab_menus p ON (m.men_pai = p.men_id)
			WHERE
				(m.men_pai IS NULL OR p.men_pai IS NULL)
				{$where_excluir}
			ORDER BY
				ordem_raiz,
				nivel_pai,
				ordem,
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($excluir_id && $this->ehDescendente($dados["id"], $excluir_id)) {
				continue;
			}
			$menus[$dados["id"]] = $dados["nome"];
		}

		return $menus;
	}

	function nivelMenu($id) {

		$id = intval($id);
		if (!$id) {
			return 0;
		}

		$nivel = 1;
		$atual = $id;

		while ($nivel < 10) {
			$query = $this->bancodedados->query("SELECT men_pai FROM tab_menus WHERE men_id = {$atual} LIMIT 1");
			$dados = $this->bancodedados->resultadoArray($query);
			if (!$dados || !$dados["men_pai"]) {
				return $nivel;
			}
			$atual = intval($dados["men_pai"]);
			$nivel++;
		}

		return $nivel;
	}

	function profundidadeSubmenus($id) {

		$id = intval($id);
		$max = 1;
		$query = $this->bancodedados->query("SELECT men_id FROM tab_menus WHERE men_pai = {$id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$profundidade = 1 + $this->profundidadeSubmenus($dados["men_id"]);
			if ($profundidade > $max) {
				$max = $profundidade;
			}
		}

		return $max;
	}

	function ehDescendente($id, $ancestral_id) {

		$id = intval($id);
		$ancestral_id = intval($ancestral_id);
		if (!$id || !$ancestral_id || $id == $ancestral_id) {
			return $id == $ancestral_id;
		}

		$atual = $id;
		while ($atual) {
			$query = $this->bancodedados->query("SELECT men_pai FROM tab_menus WHERE men_id = {$atual} LIMIT 1");
			$dados = $this->bancodedados->resultadoArray($query);
			if (!$dados || !$dados["men_pai"]) {
				return false;
			}
			if (intval($dados["men_pai"]) == $ancestral_id) {
				return true;
			}
			$atual = intval($dados["men_pai"]);
		}

		return false;
	}

	function paiValido($pai_id, $menu_id = null) {

		if ($pai_id === null || $pai_id === "" || $pai_id === "NULL") {
			return true;
		}

		$pai_id = intval($pai_id);
		$nivel_pai = $this->nivelMenu($pai_id);

		if ($nivel_pai < 1 || $nivel_pai > 2) {
			return false;
		}

		if ($menu_id) {
			$menu_id = intval($menu_id);
			if ($pai_id == $menu_id || $this->ehDescendente($pai_id, $menu_id)) {
				return false;
			}
			if (($nivel_pai + $this->profundidadeSubmenus($menu_id)) > 3) {
				return false;
			}
		} elseif ($nivel_pai >= 3) {
			return false;
		}

		return true;
	}

	##############
	### Listar ###
	##############
	function listar() {

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';

		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
			$this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
		}

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

		$itens = array();
		$itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(m.men_nome LIKE '%[valor]%')");

		$menus_pai = $this->buscaMenusPai();
		$select_pai = array();
		foreach ($menus_pai as $id => $nome) {
			$select_pai[] = array("id" => $id, "nome" => $nome);
		}
		$itens[] = array("tipo" => "select", "nome" => "pai", "descricao" => "Menu Pai", "valor" => $select_pai, "where" => "(m.men_pai = [valor])");

		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
	}

	function buscaItens($filtro, $pagina) {

		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;

		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				m.men_id AS id,
				CASE
					WHEN p.men_id IS NULL THEN m.men_nome
					WHEN gp.men_id IS NULL THEN CONCAT(p.men_nome, ' > ', m.men_nome)
					ELSE CONCAT(gp.men_nome, ' > ', p.men_nome, ' > ', m.men_nome)
				END AS nome,
				m.men_modulo AS modulo,
				m.men_acao AS acao,
				m.men_url AS url,
				m.men_target AS target,
				m.men_ordem AS ordem,
				CASE
					WHEN p.men_id IS NULL THEN '-'
					WHEN gp.men_id IS NULL THEN p.men_nome
					ELSE CONCAT(gp.men_nome, ' > ', p.men_nome)
				END AS pai_nome
			FROM
				tab_menus m
				LEFT JOIN tab_menus p ON (m.men_pai = p.men_id)
				LEFT JOIN tab_menus gp ON (p.men_pai = gp.men_id)
			WHERE
				{$filtro->parametros_sql}
			ORDER BY
				IFNULL(gp.men_ordem, IFNULL(p.men_ordem, m.men_ordem)),
				IFNULL(p.men_ordem, m.men_ordem),
				m.men_ordem
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
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, 0, ",", ".");
			$itens[] = $dados;
		}

		return array("linhas" => $linhas, "itens" => $itens);
	}

	function geraListagem($itens) {

		$cabecalho = array(
			"contador" => "#",
			"nome" => "Nome",
			"modulo" => "M&oacute;dulo",
			"acao" => "A&ccedil;&atilde;o",
			// "url" => "URL",
			// "ordem" => "Ordem",
			"pai_nome" => "Menu Pai"
		);

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right"));
	}

	#################
	### Adicionar ###
	#################
	function adicionar() {

		if (!$_POST) {
			$this->conteudo .= $this->carregaArquivo("menus.adicionar.inc.php");

			$menus_pai = $this->buscaMenusPai();
			$this->conteudo = str_replace("[pai]", $this->geraSelect($menus_pai, null, true, "pai", "250px"), $this->conteudo);

			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
		} else {

			$_POST["nome"] = addslashes(trim($_POST["nome"]));
			$_POST["modulo"] = addslashes(trim($_POST["modulo"]));
			$_POST["acao"] = addslashes(trim($_POST["acao"]));
			$_POST["url"] = addslashes(trim($_POST["url"]));
			$_POST["target"] = addslashes(trim($_POST["target"]));
			$_POST["ordem"] = intval($_POST["ordem"]);
			$_POST["pai"] = $_POST["pai"] !== "" ? intval($_POST["pai"]) : "NULL";

			if (!$this->paiValido($_POST["pai"])) {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar");
				return;
			}

			$modulo_val = $_POST["modulo"] ? "'{$_POST["modulo"]}'" : "NULL";
			$acao_val = $_POST["acao"] ? "'{$_POST["acao"]}'" : "NULL";
			$url_val = $_POST["url"] ? "'{$_POST["url"]}'" : "NULL";
			$target_val = $_POST["target"] ? "'{$_POST["target"]}'" : "NULL";

			$this->bancodedados->query("
				INSERT INTO
					tab_menus (men_nome, men_modulo, men_acao, men_url, men_target, men_ordem, men_pai)
				VALUES
					('{$_POST["nome"]}', $modulo_val, $acao_val, $url_val, $target_val, {$_POST["ordem"]}, {$_POST["pai"]})");

			$id = $this->bancodedados->ultimoIdInserido();

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
			$this->conteudo .= $this->carregaArquivo("menus.ver.inc.php");

			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[modulo]", $this->dados_item["modulo"] ? $this->dados_item["modulo"] : "-", $this->conteudo);
			$this->conteudo = str_replace("[acao]", $this->dados_item["acao"] ? $this->dados_item["acao"] : "-", $this->conteudo);
			$this->conteudo = str_replace("[url]", $this->dados_item["url"] ? $this->dados_item["url"] : "-", $this->conteudo);
			$this->conteudo = str_replace("[target]", $this->dados_item["target"] ? $this->dados_item["target"] : "-", $this->conteudo);
			$this->conteudo = str_replace("[ordem]", $this->dados_item["ordem"], $this->conteudo);
			$this->conteudo = str_replace("[pai]", $this->dados_item["pai_nome"] ? $this->dados_item["pai_nome"] : "-", $this->conteudo);

			$submenus = $this->buscaSubmenus($this->dados_item["id"]);
			$this->conteudo = str_replace("[submenus]", $submenus ? $submenus : "Nenhum submenu", $this->conteudo);

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && $this->ehAdmin()) {
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
			}
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\';">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
	}

	/**
	 * Searches for the submenus
	 * @param int $pai_id
	 * @return string
	 */
	function buscaSubmenus($pai_id) {

		$query = $this->bancodedados->query("
			SELECT
				men_id AS id,
				men_nome AS nome,
				men_ordem AS ordem
			FROM
				tab_menus
			WHERE
				men_pai = $pai_id
			ORDER BY
				men_ordem, men_nome");

		$submenus = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$link = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
			$submenus[] = "<a href=\"$link\" class=\"txtAzulEscuro11\">{$dados["nome"]}</a> (Ordem: {$dados["ordem"]})";
		}

		return count($submenus) > 0 ? implode("<br>", $submenus) : "";
	}

	##############
	### Editar ###
	##############
	function editar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("menus.editar.inc.php");

				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[modulo]", $this->dados_item["modulo"], $this->conteudo);
				$this->conteudo = str_replace("[acao]", $this->dados_item["acao"], $this->conteudo);
				$this->conteudo = str_replace("[url]", $this->dados_item["url"], $this->conteudo);
				$this->conteudo = str_replace("[target]", $this->dados_item["target"], $this->conteudo);
				$this->conteudo = str_replace("[ordem]", $this->dados_item["ordem"], $this->conteudo);

				$menus_pai = $this->buscaMenusPai($this->dados_item["id"]);
				$this->conteudo = str_replace("[pai]", $this->geraSelect($menus_pai, $this->dados_item["pai_id"], true, "pai", "250px"), $this->conteudo);

				$botoes = array();

				if ($this->ehAdmin()) {
					$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
				}

				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
			} else {

				$_POST["nome"] = addslashes(trim($_POST["nome"]));
				$_POST["modulo"] = addslashes(trim($_POST["modulo"]));
				$_POST["acao"] = addslashes(trim($_POST["acao"]));
				$_POST["url"] = addslashes(trim($_POST["url"]));
				$_POST["target"] = addslashes(trim($_POST["target"]));
				$_POST["ordem"] = intval($_POST["ordem"]);
				$_POST["pai"] = $_POST["pai"] !== "" ? intval($_POST["pai"]) : "NULL";

				if (!$this->paiValido($_POST["pai"], $_GET["i"])) {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}");
					return;
				}

				$modulo_val = $_POST["modulo"] ? "'{$_POST["modulo"]}'" : "NULL";
				$acao_val = $_POST["acao"] ? "'{$_POST["acao"]}'" : "NULL";
				$url_val = $_POST["url"] ? "'{$_POST["url"]}'" : "NULL";
				$target_val = $_POST["target"] ? "'{$_POST["target"]}'" : "NULL";

				$this->bancodedados->query("
					UPDATE
						tab_menus
					SET
						men_nome = '{$_POST["nome"]}',
						men_modulo = $modulo_val,
						men_acao = $acao_val,
						men_url = $url_val,
						men_target = $target_val,
						men_ordem = {$_POST["ordem"]},
						men_pai = {$_POST["pai"]}
					WHERE
						men_id = {$_GET["i"]}");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
	}

	###############
	### Remover ###
	###############
	function remover() {

		if (!$this->ehAdmin()) {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
			return;
		}

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
			if (!$_POST) {

				$tem_submenus = $this->temSubmenus($this->dados_item["id"]);

				$botoes = array();
				if (!$tem_submenus) {
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				}
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("menus.remover.inc.php");

				if ($tem_submenus) {
					$this->conteudo = str_replace("[mensagem]", "Este menu possui submenus vinculados e <b>n&atilde;o pode ser removido</b>.<br>Remova os submenus primeiro.", $this->conteudo);
				} else {
					$this->conteudo = str_replace("[mensagem]", "Voc&ecirc; deseja realmente <b>remover</b> o menu <b>{$this->dados_item["nome"]}</b>?", $this->conteudo);
				}

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {

				$this->bancodedados->query("DELETE FROM tab_menus WHERE men_id = '{$_GET["i"]}'");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}

	function temSubmenus($id) {

		$query = "SELECT COUNT(*) AS total FROM tab_menus WHERE men_pai = $id";
		$query = $this->bancodedados->query($query);
		$total = $this->bancodedados->resultado($query, 0, "total");

		return $total > 0;
	}
}


?>