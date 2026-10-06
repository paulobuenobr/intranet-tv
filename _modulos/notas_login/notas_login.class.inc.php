<?php

require_once("_global/modulo.class.inc.php");

class notas_login extends modulo {

	private $valor_status = array(
		1 => "Ativo",
		0 => "Inativo",
	);

	function __construct($bancodedados, $permissoes, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		$this->buscaDadosModulo($_GET["m"]);

		if ($executar) {
			$_GET["a"] = $_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"] ? "editar" : $_GET["a"];
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
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
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
			$this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
		}
		$this->conteudo .= $filtro->formulario;

		$itens = $this->buscaItens($filtro, $pagina);
		$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
		$listagem = $this->geraListagem($itens["itens"]);

		$this->conteudo .= '</td></tr></table>';
		$this->conteudo .= $paginacao->conteudo;
		$this->conteudo .= $listagem->conteudo;
		$this->conteudo .= $paginacao->conteudo;

	}

	function buscar() {
		$this->listar();
	}

	function inicializaFiltro($valores = "") {

		$itens = array();

		$itens[] = array(
			"tipo" => "input",
			"nome" => "login",
			"descricao" => "Login",
			"valor" => "",
			"where" => "(tab_notas_login.login LIKE '%[valor]%')"
		);

		$itens[] = array(
			"tipo" => "select",
			"nome" => "status",
			"descricao" => "Status",
			"valor" => $this->buscaOpcoesStatusFiltro(),
			"where" => "(tab_notas_login.status = '[valor]')"
		);

		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);

	}

	function buscaItens($filtro, $pagina) {

		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$itens = array();

		$where = trim($filtro->parametros_sql) !== "" ? $filtro->parametros_sql : "1=1";

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				id,
				login,
				status
			FROM
				tab_notas_login
			WHERE
				$where
			ORDER BY
				status DESC,
				login
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
			}

			$dados["status"] = $dados["status"] == 1 ? "Ativo" : "Inativo";
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagem($itens) {

		$cabecalho = array(
			"contador" => "#",
			"login" => "Login",
			"status" => "Status"
		);

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(40, 0, 80), null, array("right", "left", "center"));

	}

	###########
	### Ver ###
	###########

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) {
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\'">';
			}
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) {
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\'">';
			}
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\'">';

			$this->conteudo .= $this->carregaArquivo("notas_login.ver.inc.php");

			$this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace("[login]", htmlspecialchars($this->dados_item["login"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->valor_status[$this->dados_item["status"]], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}

	}

	##############
	### Editar ###
	##############

	function editar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) {
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\'">';
				}
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\'">';

				$this->conteudo .= $this->carregaArquivo("notas_login.editar.inc.php");

				$this->conteudo = str_replace("[login]", htmlspecialchars($this->dados_item["login"]), $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->geraSelect($this->valor_status, $this->dados_item["status"], false, "status", "250px"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);

			} else {

				$_POST["login"] = addslashes(trim($_POST["login"]));
				$_POST["status"] = (int) $_POST["status"];

				$set_senha = "";
				if (trim($_POST["senha"]) != "") {
					$_POST["senha"] = addslashes(trim($_POST["senha"]));
					$set_senha = ", senha = '{$_POST["senha"]}'";
				}

				$this->bancodedados->query("
					UPDATE
						tab_notas_login
					SET
						login = '{$_POST["login"]}',
						status = {$_POST["status"]}
						$set_senha
					WHERE
						id = {$_GET["i"]}");

				if ($_POST["status"] == 1) {
					$this->desativaOutrosLogins($_GET["i"]);
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}

	}

	#################
	### Adicionar ###
	#################

	function adicionar() {

		if (!$_POST) {

			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\'">';

			$this->conteudo .= $this->carregaArquivo("notas_login.adicionar.inc.php");

			$this->conteudo = str_replace("[status]", $this->geraSelect($this->valor_status, 1, false, "status", "250px"), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);

		} else {

			$_POST["login"] = addslashes(trim($_POST["login"]));
			$_POST["senha"] = addslashes(trim($_POST["senha"]));
			$_POST["status"] = (int) $_POST["status"];

			$this->bancodedados->query("
				INSERT INTO
					tab_notas_login
				SET
					login = '{$_POST["login"]}',
					senha = '{$_POST["senha"]}',
					status = {$_POST["status"]}");

			$id = $this->bancodedados->ultimoIdInserido();

			if ($_POST["status"] == 1) {
				$this->desativaOutrosLogins($id);
			}

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");

		}

	}

	###############
	### Remover ###
	###############

	function remover() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("notas_login.remover.inc.php");

				$this->conteudo = str_replace("[login]", htmlspecialchars($this->dados_item["login"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);

			} else {

				$this->bancodedados->query("DELETE FROM tab_notas_login WHERE id = {$_GET["i"]}");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");

			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}

	}

	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($id) {

		if ($id) {

			$query = $this->bancodedados->query("
				SELECT
					id,
					login,
					senha,
					status
				FROM
					tab_notas_login
				WHERE
					id = $id");

			$this->dados_item = $this->bancodedados->resultadoArray($query);

		}

	}

	/**
	 * Garante um único login ativo (NotaSJ usa WHERE status = 1 LIMIT 1).
	 */
	function desativaOutrosLogins($idAtivo) {

		$idAtivo = (int) $idAtivo;
		$this->bancodedados->query("UPDATE tab_notas_login SET status = 0 WHERE id <> $idAtivo AND status = 1");

	}

	#################
	### Consultas ###
	#################

	function buscaOpcoesStatusFiltro() {

		$opcoes = array();
		$opcoes[] = array("id" => "1", "nome" => "Ativo");
		$opcoes[] = array("id" => "0", "nome" => "Inativo");

		return $opcoes;

	}

}
