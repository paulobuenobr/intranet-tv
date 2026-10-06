<?php

require_once("_global/modulo.class.inc.php");

class classes_fiscais_tipos extends modulo {

	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_modulo;
	var $dados_acao;
	var $dados_item;

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

	function ehAdmin($id = null) {

		return $_SESSION["administrador"];

	}

	function arraySimNao() {

		return array(
			"0" => "Não",
			"1" => "Sim",
		);
	}

	function arrayTipo() {

		return array(
			"SCM" => "SCM",
			"SVA" => "SVA",
		);
	}

	function buscaEmpresas() {

		$empresas = array();
		$query = $this->bancodedados->query("
			SELECT
				emp_id,
				emp_nome
			FROM
				tab_empresas
			ORDER BY
				emp_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$empresas[$dados["emp_id"]] = $dados["emp_nome"];
		}

		return $empresas;
	}

	function labelSimNao($valor) {

		return "{$valor}" === "1" ? "Sim" : "Não";
	}

	function siglaExiste($sigla, $excetoId = null) {

		$sigla = addslashes($sigla);
		$exceto = $excetoId !== null && $excetoId !== "" ? " AND cft_id <> '" . preg_replace("/[^0-9]/", "", $excetoId) . "'" : "";
		$query = $this->bancodedados->query("
			SELECT
				cft_id
			FROM
				tab_classes_fiscais_tipos
			WHERE
				cft_sigla = '{$sigla}'
				{$exceto}
			LIMIT 1");

		return $this->bancodedados->resultadoArray($query) ? true : false;
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

	function inicializaFiltro($valores = "") {

		$itens = array();

		$itens[] = array(
			"tipo" => "input",
			"nome" => "cft_sigla",
			"descricao" => "Sigla",
			"valor" => "",
			"where" => "(tab_classes_fiscais_tipos.cft_sigla LIKE '%[valor]%')"
		);

		$itens[] = array(
			"tipo" => "input",
			"nome" => "cft_descricao",
			"descricao" => "Descrição",
			"valor" => "",
			"where" => "(tab_classes_fiscais_tipos.cft_descricao LIKE '%[valor]%')"
		);

		$opcoesTipo = array();
		foreach ($this->arrayTipo() as $id => $nome) {
			$opcoesTipo[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "cft_tipo",
			"descricao" => "Tipo",
			"valor" => $opcoesTipo,
			"where" => "(tab_classes_fiscais_tipos.cft_tipo = '[valor]')"
		);

		$opcoes = array();
		foreach ($this->arraySimNao() as $id => $nome) {
			$opcoes[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "cft_gerar_nota",
			"descricao" => "Gerar Nota",
			"valor" => $opcoes,
			"where" => "(tab_classes_fiscais_tipos.cft_gerar_nota = '[valor]')"
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

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_classes_fiscais_tipos.cft_id AS id,
				tab_classes_fiscais_tipos.cft_sigla AS sigla,
				tab_classes_fiscais_tipos.cft_descricao AS descricao,
				tab_classes_fiscais_tipos.cft_tipo AS tipo,
				tab_classes_fiscais_tipos.cft_icms AS icms,
				IF(tab_classes_fiscais_tipos.cft_gerar_nota = 1, 'Sim', 'Não') AS gerar_nota,
				IFNULL(tab_empresas.emp_nome, '?') AS empresa
			FROM
				tab_classes_fiscais_tipos
				LEFT JOIN tab_empresas ON (tab_empresas.emp_id = tab_classes_fiscais_tipos.emp_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_classes_fiscais_tipos.cft_sigla
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$itens[] = $dados;
		}

		return array("linhas" => $linhas, "itens" => $itens);
	}

	function geraListagem($itens) {

		$cabecalho = array(
			"contador" => "#",
			"id" => "ID",
			"sigla" => "Sigla",
			"descricao" => "Descrição",
			"tipo" => "Tipo",
			"icms" => "ICMS",
			"gerar_nota" => "Nota",
			"empresa" => "Empresa"
		);

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 40, 50, 0, 50, 60, 50, 120), null, array("right", "center", "center", "left", "center", "right", "center", "left"));
	}

	function buscaDadosItem($id) {

		$id = preg_replace("/[^0-9]/", "", $id);
		$query = $this->bancodedados->query("
			SELECT
				*
			FROM
				tab_classes_fiscais_tipos
			WHERE
				cft_id = '{$id}'
			LIMIT 1");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$this->dados_item = $dados;
			$this->dados_item["id"] = $dados["cft_id"];
		} else {
			unset($this->dados_item);
		}
	}

	###########
	### Ver ###
	###########

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
			$empresas = $this->buscaEmpresas();

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) {
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\'">';
			}
			if ($this->ehAdmin()) {
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\'">';
			}
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\'">';

			$this->conteudo .= $this->carregaArquivo("classes_fiscais_tipos.ver.inc.php");
			$this->conteudo = str_replace("[cft_id]", $this->dados_item["cft_id"], $this->conteudo);
			$this->conteudo = str_replace("[cft_sigla]", htmlspecialchars($this->dados_item["cft_sigla"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_descricao]", htmlspecialchars($this->dados_item["cft_descricao"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_tipo]", htmlspecialchars($this->dados_item["cft_tipo"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_tributacao]", $this->dados_item["cft_tributacao"], $this->conteudo);
			$this->conteudo = str_replace("[cft_icms]", $this->dados_item["cft_icms"], $this->conteudo);
			$this->conteudo = str_replace("[cft_codigo]", htmlspecialchars($this->dados_item["cft_codigo"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_natureza_pf_sc]", htmlspecialchars($this->dados_item["cft_natureza_pf_sc"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_natureza_pj_sc]", htmlspecialchars($this->dados_item["cft_natureza_pj_sc"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_natureza_pf_outros]", htmlspecialchars($this->dados_item["cft_natureza_pf_outros"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_natureza_pj_outros]", htmlspecialchars($this->dados_item["cft_natureza_pj_outros"]), $this->conteudo);
			$this->conteudo = str_replace("[emp_id]", isset($empresas[$this->dados_item["emp_id"]]) ? htmlspecialchars($empresas[$this->dados_item["emp_id"]]) : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[cft_gerar_nota]", $this->labelSimNao($this->dados_item["cft_gerar_nota"]), $this->conteudo);
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
				if ($this->ehAdmin()) {
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\'">';
				}
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

				$this->conteudo .= $this->carregaArquivo("classes_fiscais_tipos.editar.inc.php");
				$this->preencheFormulario($this->dados_item);
				$this->conteudo = str_replace("[cft_id]", $this->dados_item["cft_id"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
			} else {
				$dados = $this->sanitizaPost();

				if ($dados["cft_sigla"] === "" || $dados["cft_descricao"] === "") {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}");
					return;
				}

				if ($this->siglaExiste($dados["cft_sigla"], $_GET["i"])) {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}");
					return;
				}

				$this->bancodedados->query("
					UPDATE
						tab_classes_fiscais_tipos
					SET
						cft_sigla = '{$dados["cft_sigla"]}',
						cft_descricao = '{$dados["cft_descricao"]}',
						cft_tipo = '{$dados["cft_tipo"]}',
						cft_tributacao = {$dados["cft_tributacao"]},
						cft_icms = {$dados["cft_icms"]},
						cft_codigo = '{$dados["cft_codigo"]}',
						cft_natureza_pf_sc = '{$dados["cft_natureza_pf_sc"]}',
						cft_natureza_pj_sc = '{$dados["cft_natureza_pj_sc"]}',
						cft_natureza_pf_outros = '{$dados["cft_natureza_pf_outros"]}',
						cft_natureza_pj_outros = '{$dados["cft_natureza_pj_outros"]}',
						emp_id = {$dados["emp_id"]},
						cft_gerar_nota = {$dados["cft_gerar_nota"]}
					WHERE
						cft_id = '{$_GET["i"]}'");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
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
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

			$this->conteudo .= $this->carregaArquivo("classes_fiscais_tipos.adicionar.inc.php");
			$this->preencheFormulario(array(
				"cft_sigla" => "",
				"cft_descricao" => "",
				"cft_tipo" => "SCM",
				"cft_tributacao" => "1",
				"cft_icms" => "0.00",
				"cft_codigo" => "",
				"cft_natureza_pf_sc" => "",
				"cft_natureza_pj_sc" => "",
				"cft_natureza_pf_outros" => "",
				"cft_natureza_pj_outros" => "",
				"emp_id" => "",
				"cft_gerar_nota" => "1"
			));
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
		} else {
			$dados = $this->sanitizaPost();

			if ($dados["cft_sigla"] === "" || $dados["cft_descricao"] === "") {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar");
				return;
			}

			if ($this->siglaExiste($dados["cft_sigla"])) {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar");
				return;
			}

			$this->bancodedados->query("
				INSERT INTO
					tab_classes_fiscais_tipos
				SET
					cft_sigla = '{$dados["cft_sigla"]}',
					cft_descricao = '{$dados["cft_descricao"]}',
					cft_tipo = '{$dados["cft_tipo"]}',
					cft_tributacao = {$dados["cft_tributacao"]},
					cft_icms = {$dados["cft_icms"]},
					cft_codigo = '{$dados["cft_codigo"]}',
					cft_natureza_pf_sc = '{$dados["cft_natureza_pf_sc"]}',
					cft_natureza_pj_sc = '{$dados["cft_natureza_pj_sc"]}',
					cft_natureza_pf_outros = '{$dados["cft_natureza_pf_outros"]}',
					cft_natureza_pj_outros = '{$dados["cft_natureza_pj_outros"]}',
					emp_id = {$dados["emp_id"]},
					cft_gerar_nota = {$dados["cft_gerar_nota"]}");

			$id = $this->bancodedados->ultimoIdInserido();
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
		}
	}

	###############
	### Remover ###
	###############

	function remover() {

		if (!$this->ehAdmin()) {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			return;
		}

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
			if (!$_POST) {
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("classes_fiscais_tipos.remover.inc.php");
				$this->conteudo = str_replace("[cft_sigla]", htmlspecialchars($this->dados_item["cft_sigla"]), $this->conteudo);
				$this->conteudo = str_replace("[cft_descricao]", htmlspecialchars($this->dados_item["cft_descricao"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {
				$this->bancodedados->query("DELETE FROM tab_classes_fiscais_tipos WHERE cft_id = '{$_GET["i"]}'");
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}

	function preencheFormulario($dados) {

		$this->conteudo = str_replace("[cft_sigla]", htmlspecialchars(isset($dados["cft_sigla"]) ? $dados["cft_sigla"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[cft_descricao]", htmlspecialchars(isset($dados["cft_descricao"]) ? $dados["cft_descricao"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[cft_tipo]", $this->geraSelect($this->arrayTipo(), isset($dados["cft_tipo"]) ? $dados["cft_tipo"] : "SCM", false, "cft_tipo", "120px"), $this->conteudo);
		$this->conteudo = str_replace("[cft_tributacao]", htmlspecialchars(isset($dados["cft_tributacao"]) ? $dados["cft_tributacao"] : "1"), $this->conteudo);
		$this->conteudo = str_replace("[cft_icms]", htmlspecialchars(isset($dados["cft_icms"]) ? $dados["cft_icms"] : "0.00"), $this->conteudo);
		$this->conteudo = str_replace("[cft_codigo]", htmlspecialchars(isset($dados["cft_codigo"]) ? $dados["cft_codigo"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[cft_natureza_pf_sc]", htmlspecialchars(isset($dados["cft_natureza_pf_sc"]) ? $dados["cft_natureza_pf_sc"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[cft_natureza_pj_sc]", htmlspecialchars(isset($dados["cft_natureza_pj_sc"]) ? $dados["cft_natureza_pj_sc"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[cft_natureza_pf_outros]", htmlspecialchars(isset($dados["cft_natureza_pf_outros"]) ? $dados["cft_natureza_pf_outros"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[cft_natureza_pj_outros]", htmlspecialchars(isset($dados["cft_natureza_pj_outros"]) ? $dados["cft_natureza_pj_outros"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[emp_id]", $this->geraSelect($this->buscaEmpresas(), isset($dados["emp_id"]) ? $dados["emp_id"] : null, true, "emp_id", "350px"), $this->conteudo);
		$this->conteudo = str_replace("[cft_gerar_nota]", $this->geraSelect($this->arraySimNao(), isset($dados["cft_gerar_nota"]) ? $dados["cft_gerar_nota"] : "1", false, "cft_gerar_nota", "120px"), $this->conteudo);
	}

	function sanitizaPost() {

		$cft_sigla = strtoupper(substr(trim($_POST["cft_sigla"]), 0, 3));
		$cft_sigla = addslashes($cft_sigla);
		$cft_descricao = addslashes(substr(trim($_POST["cft_descricao"]), 0, 60));
		$cft_tipo = isset($_POST["cft_tipo"]) && $_POST["cft_tipo"] === "SVA" ? "SVA" : "SCM";
		$cft_tributacao = preg_replace("/[^0-9]/", "", $_POST["cft_tributacao"]);
		$cft_tributacao = $cft_tributacao !== "" ? intval($cft_tributacao) : 1;
		$cft_icms = str_replace(",", ".", trim($_POST["cft_icms"]));
		$cft_icms = is_numeric($cft_icms) ? number_format((float) $cft_icms, 2, ".", "") : "0.00";
		$cft_codigo = addslashes(substr(trim($_POST["cft_codigo"]), 0, 4));
		$cft_natureza_pf_sc = addslashes(substr(trim($_POST["cft_natureza_pf_sc"]), 0, 8));
		$cft_natureza_pj_sc = addslashes(substr(trim($_POST["cft_natureza_pj_sc"]), 0, 8));
		$cft_natureza_pf_outros = addslashes(substr(trim($_POST["cft_natureza_pf_outros"]), 0, 8));
		$cft_natureza_pj_outros = addslashes(substr(trim($_POST["cft_natureza_pj_outros"]), 0, 8));
		$emp_id = preg_replace("/[^0-9]/", "", isset($_POST["emp_id"]) ? $_POST["emp_id"] : "");
		$emp_id = $emp_id !== "" ? intval($emp_id) : "NULL";
		$cft_gerar_nota = isset($_POST["cft_gerar_nota"]) && $_POST["cft_gerar_nota"] == "1" ? 1 : 0;

		return array(
			"cft_sigla" => $cft_sigla,
			"cft_descricao" => $cft_descricao,
			"cft_tipo" => $cft_tipo,
			"cft_tributacao" => $cft_tributacao,
			"cft_icms" => $cft_icms,
			"cft_codigo" => $cft_codigo,
			"cft_natureza_pf_sc" => $cft_natureza_pf_sc,
			"cft_natureza_pj_sc" => $cft_natureza_pj_sc,
			"cft_natureza_pf_outros" => $cft_natureza_pf_outros,
			"cft_natureza_pj_outros" => $cft_natureza_pj_outros,
			"emp_id" => $emp_id,
			"cft_gerar_nota" => $cft_gerar_nota
		);
	}
}
?>
