<?php

require_once ("_global/modulo.class.inc.php");

class iptv_pacotes extends modulo {

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
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"])
				$_GET["a"] = "editar";
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

	// Mapa de tipos do enum ipt_tipo para rótulos legíveis.
	function buscaTipos() {
		return array(
			"PACOTE"   => "Pacote",
			"CATALOGO" => "Catálogo",
		);
	}

	function buscaDadosItem($id) {

		$id = (int) $id;
		$query = "
					SELECT
						ipt_id     AS id,
						ipt_nome   AS nome,
						ipt_codigo AS codigo,
						ipt_tipo   AS tipo
					FROM
						tab_iptv_pacotes
					WHERE
						ipt_id = $id";
		$query = $this->bancodedados->query($query);
		$this->dados_item = $this->bancodedados->resultadoArray($query);
		if (!$this->dados_item) {
			unset($this->dados_item);
		}
	}

	function adicionar() {

		if (!$_POST) {

			$this->conteudo .= $this->carregaArquivo("iptv_pacotes.adicionar.inc.php");
			$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), null, true, "tipo", "250px"), $this->conteudo);

			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
		} else {

			$nome   = addslashes(trim($_POST["nome"]));
			$codigo = addslashes(trim($_POST["codigo"]));
			$tipo   = array_key_exists($_POST["tipo"], $this->buscaTipos()) ? $_POST["tipo"] : "PACOTE";

			$query = "
						INSERT INTO tab_iptv_pacotes
							(ipt_nome, ipt_codigo, ipt_tipo)
						VALUES
							('$nome', '$codigo', '$tipo')";
			$this->bancodedados->query($query);

			$id = $this->bancodedados->ultimoIdInserido();

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
		}
	}

	function editar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) {
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
				}
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

				$this->conteudo .= $this->carregaArquivo("iptv_pacotes.editar.inc.php");
				$this->conteudo = str_replace("[nome]",   $this->dados_item["nome"],   $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]",   $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo"], false, "tipo", "250px"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
			} else {

				$id     = (int) $_GET["i"];
				$nome   = addslashes(trim($_POST["nome"]));
				$codigo = addslashes(trim($_POST["codigo"]));
				$tipo   = array_key_exists($_POST["tipo"], $this->buscaTipos()) ? $_POST["tipo"] : $this->dados_item["tipo"];

				$query = "
							UPDATE
								tab_iptv_pacotes
							SET
								ipt_nome   = '$nome',
								ipt_codigo = '$codigo',
								ipt_tipo   = '$tipo'
							WHERE
								ipt_id = $id";
				$this->bancodedados->query($query);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
	}

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if (!empty($this->dados_item)) {

			$tipos = $this->buscaTipos();

			$this->conteudo .= $this->carregaArquivo("iptv_pacotes.ver.inc.php");
			$this->conteudo = str_replace("[nome]",   $this->dados_item["nome"],   $this->conteudo);
			$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]",   isset($tipos[$this->dados_item["tipo"]]) ? $tipos[$this->dados_item["tipo"]] : $this->dados_item["tipo"], $this->conteudo);

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"])
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
	}

	function remover() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$tipos = $this->buscaTipos();

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("iptv_pacotes.remover.inc.php");
				$this->conteudo = str_replace("[nome]",   $this->dados_item["nome"],   $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]",   isset($tipos[$this->dados_item["tipo"]]) ? $tipos[$this->dados_item["tipo"]] : $this->dados_item["tipo"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {

				$id = (int) $_GET["i"];
				$this->bancodedados->query("DELETE FROM tab_iptv_pacotes WHERE ipt_id = $id");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}

	function listar() {

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		$itens     = $this->buscaItens($filtro, $pagina);
		$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
		$listagem  = $this->geraListagem($itens["itens"]);

		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#252525"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
		$this->conteudo .= $filtro->formulario;
		$this->conteudo .= '</td></tr></table>';
		$this->conteudo .= $paginacao->conteudo;
		$this->conteudo .= $listagem->conteudo;
		$this->conteudo .= $paginacao->conteudo;

	}

	function inicializaFiltro($valores = "") {

		$itens[] = array("tipo" => "input", "nome" => "nome",   "descricao" => "Nome",   "valor" => "", "where" => "(tab_iptv_pacotes.ipt_nome LIKE '%[valor]%')");
		$itens[] = array("tipo" => "input", "nome" => "codigo", "descricao" => "Código", "valor" => "", "where" => "(tab_iptv_pacotes.ipt_codigo LIKE '%[valor]%')");

		$select_tipos = array();
		foreach ($this->buscaTipos() as $id => $nome) {
			$select_tipos[] = array("id" => $id, "nome" => $nome);
		}
		$itens[] = array("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "(tab_iptv_pacotes.ipt_tipo = '[valor]')");

		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		return $filtro;
	}

	function geraPaginacao($filtro, $total, $pagina) {

		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		return $paginacao;
	}

	function buscaItens($filtro, $pagina) {

		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim    = PAG_ITENS;
		$query = "
					SELECT SQL_CALC_FOUND_ROWS
						tab_iptv_pacotes.ipt_id     AS id,
						tab_iptv_pacotes.ipt_nome   AS nome,
						tab_iptv_pacotes.ipt_codigo AS codigo,
						tab_iptv_pacotes.ipt_tipo   AS tipo
					FROM
						tab_iptv_pacotes
					WHERE
						$filtro->parametros_sql
					ORDER BY
						tab_iptv_pacotes.ipt_nome ASC
					LIMIT
						$limit_inicio,
						$limit_fim";

		$query = $this->bancodedados->query($query);
		$linhas = $this->bancodedados->linhasTotal($query);
		$tipos  = $this->buscaTipos();
		$itens  = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
			}
			$dados["tipo_nome"] = isset($tipos[$dados["tipo"]]) ? $tipos[$dados["tipo"]] : $dados["tipo"];
			$dados["contador"]  = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$itens[] = $dados;
		}
		return array("linhas" => $linhas, "itens" => $itens);
	}

	function geraListagem($itens) {
		$cabecalho = array("contador" => "#", "nome" => "Nome", "codigo" => "Código", "tipo_nome" => "Tipo");
		$listagem  = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right", "left"));
		return $listagem;
	}
}
