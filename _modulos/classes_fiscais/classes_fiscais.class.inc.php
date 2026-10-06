<?php

require_once("_global/modulo.class.inc.php");

class classes_fiscais extends modulo {

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

	function ehAdmin() {
	
		return $_SESSION["administrador"];

	}

	function buscaPlanos() {

		$itens = array();
		$query = $this->bancodedados->query("
			SELECT
				pla_id,
				pla_nome
			FROM
				tab_planos
			ORDER BY
				pla_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$itens[$dados["pla_id"]] = $dados["pla_id"] . " - " . $dados["pla_nome"];
		}

		return $itens;
	}

	function buscaServicos() {

		$itens = array();
		$query = $this->bancodedados->query("
			SELECT
				pls_id,
				pls_nome
			FROM
				tab_planos_servicos
			ORDER BY
				pls_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$itens[$dados["pls_id"]] = $dados["pls_id"] . " - " . $dados["pls_nome"];
		}

		return $itens;
	}

	function buscaCategorias() {

		$itens = array();
		$query = $this->bancodedados->query("
			SELECT
				afc_id,
				afc_nome
			FROM
				tab_ajustes_financeiros_categorias
			ORDER BY
				afc_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$itens[$dados["afc_id"]] = $dados["afc_id"] . " - " . $dados["afc_nome"];
		}

		return $itens;
	}

	function buscaTiposFiscais() {

		$itens = array();
		$query = $this->bancodedados->query("
			SELECT
				cft_id,
				cft_sigla,
				cft_descricao,
				cft_tipo
			FROM
				tab_classes_fiscais_tipos
			ORDER BY
				cft_sigla");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$itens[$dados["cft_id"]] = $dados["cft_sigla"] . " - " . $dados["cft_descricao"] . " (" . $dados["cft_tipo"] . ")";
		}

		return $itens;
	}

	function labelOuTraco($lista, $id) {

		return isset($lista[$id]) ? htmlspecialchars($lista[$id]) : "&mdash;";
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

		$opcoesPlanos = array();
		foreach ($this->buscaPlanos() as $id => $nome) {
			$opcoesPlanos[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "pla_id",
			"descricao" => "Plano",
			"valor" => $opcoesPlanos,
			"where" => "(tab_classes_fiscais.pla_id = '[valor]')"
		);

		$opcoesServicos = array();
		foreach ($this->buscaServicos() as $id => $nome) {
			$opcoesServicos[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "pls_id",
			"descricao" => "Serviço",
			"valor" => $opcoesServicos,
			"where" => "(tab_classes_fiscais.pls_id = '[valor]')"
		);

		$opcoesCategorias = array();
		foreach ($this->buscaCategorias() as $id => $nome) {
			$opcoesCategorias[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "afc_id",
			"descricao" => "Categoria",
			"valor" => $opcoesCategorias,
			"where" => "(tab_classes_fiscais.afc_id = '[valor]')"
		);

		$opcoesTipos = array();
		foreach ($this->buscaTiposFiscais() as $id => $nome) {
			$opcoesTipos[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "cft_id",
			"descricao" => "Tipo Fiscal",
			"valor" => $opcoesTipos,
			"where" => "(tab_classes_fiscais.cft_id = '[valor]')"
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
				tab_classes_fiscais.clf_id AS id,
				tab_classes_fiscais.clf_taxa AS taxa,
				IFNULL(CONCAT(tab_planos.pla_id, ' - ', tab_planos.pla_nome), null) AS plano,
				IFNULL(CONCAT(tab_planos_servicos.pls_id, ' - ', tab_planos_servicos.pls_nome), null) AS servico,
				IFNULL(CONCAT(tab_ajustes_financeiros_categorias.afc_id, ' - ', tab_ajustes_financeiros_categorias.afc_nome), null) AS categoria,
				IFNULL(CONCAT(tab_classes_fiscais_tipos.cft_sigla, ' - ', tab_classes_fiscais_tipos.cft_descricao), null) AS tipo
			FROM
				tab_classes_fiscais
				LEFT JOIN tab_planos ON (tab_planos.pla_id = tab_classes_fiscais.pla_id)
				LEFT JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_classes_fiscais.pls_id)
				LEFT JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros_categorias.afc_id = tab_classes_fiscais.afc_id)
				LEFT JOIN tab_classes_fiscais_tipos ON (tab_classes_fiscais_tipos.cft_id = tab_classes_fiscais.cft_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_classes_fiscais.clf_id
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
			"taxa" => "Taxa",
			"plano" => "Plano",
			"servico" => "Serviço",
			"categoria" => "Categoria",
			"tipo" => "Tipo Fiscal"
		);

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 40, 60, 0, 0, 0, 0), null, array("right", "center", "right", "left", "left", "left", "left"));
	}

	function buscaDadosItem($id) {

		$id = preg_replace("/[^0-9]/", "", $id);
		$query = $this->bancodedados->query("
			SELECT
				*
			FROM
				tab_classes_fiscais
			WHERE
				clf_id = '{$id}'
			LIMIT 1");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$this->dados_item = $dados;
			$this->dados_item["id"] = $dados["clf_id"];
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
			$planos = $this->buscaPlanos();
			$servicos = $this->buscaServicos();
			$categorias = $this->buscaCategorias();
			$tipos = $this->buscaTiposFiscais();

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) {
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\'">';
			}
			if ($this->ehAdmin()) {
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\'">';
			}
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\'">';

			$this->conteudo .= $this->carregaArquivo("classes_fiscais.ver.inc.php");
			$this->conteudo = str_replace("[clf_id]", $this->dados_item["clf_id"], $this->conteudo);
			$this->conteudo = str_replace("[clf_taxa]", $this->dados_item["clf_taxa"], $this->conteudo);
			$this->conteudo = str_replace("[pla_id]", $this->labelOuTraco($planos, $this->dados_item["pla_id"]), $this->conteudo);
			$this->conteudo = str_replace("[pls_id]", $this->labelOuTraco($servicos, $this->dados_item["pls_id"]), $this->conteudo);
			$this->conteudo = str_replace("[afc_id]", $this->labelOuTraco($categorias, $this->dados_item["afc_id"]), $this->conteudo);
			$this->conteudo = str_replace("[cft_id]", $this->labelOuTraco($tipos, $this->dados_item["cft_id"]), $this->conteudo);
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

				$this->conteudo .= $this->carregaArquivo("classes_fiscais.editar.inc.php");
				$this->preencheFormulario($this->dados_item);
				$this->conteudo = str_replace("[clf_id]", $this->dados_item["clf_id"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
			} else {
				$dados = $this->sanitizaPost();

				if ($dados["cft_id"] === "NULL") {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}");
					return;
				}

				$this->bancodedados->query("
					UPDATE
						tab_classes_fiscais
					SET
						clf_taxa = {$dados["clf_taxa"]},
						pla_id = {$dados["pla_id"]},
						pls_id = {$dados["pls_id"]},
						afc_id = {$dados["afc_id"]},
						cft_id = {$dados["cft_id"]}
					WHERE
						clf_id = '{$_GET["i"]}'");

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

			$this->conteudo .= $this->carregaArquivo("classes_fiscais.adicionar.inc.php");
			$this->preencheFormulario(array(
				"clf_taxa" => "0.00",
				"pla_id" => "",
				"pls_id" => "",
				"afc_id" => "",
				"cft_id" => ""
			));
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
		} else {
			$dados = $this->sanitizaPost();

			if ($dados["cft_id"] === "NULL") {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar");
				return;
			}

			$this->bancodedados->query("
				INSERT INTO
					tab_classes_fiscais
				SET
					clf_taxa = {$dados["clf_taxa"]},
					pla_id = {$dados["pla_id"]},
					pls_id = {$dados["pls_id"]},
					afc_id = {$dados["afc_id"]},
					cft_id = {$dados["cft_id"]}");

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

				$this->conteudo .= $this->carregaArquivo("classes_fiscais.remover.inc.php");
				$this->conteudo = str_replace("[clf_id]", $this->dados_item["clf_id"], $this->conteudo);
				$this->conteudo = str_replace("[clf_taxa]", $this->dados_item["clf_taxa"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {
				$this->bancodedados->query("DELETE FROM tab_classes_fiscais WHERE clf_id = '{$_GET["i"]}'");
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}

	function preencheFormulario($dados) {

		$this->conteudo = str_replace("[clf_taxa]", htmlspecialchars(isset($dados["clf_taxa"]) ? $dados["clf_taxa"] : "0.00"), $this->conteudo);
		$this->conteudo = str_replace("[pla_id]", $this->geraSelect($this->buscaPlanos(), isset($dados["pla_id"]) ? $dados["pla_id"] : null, true, "pla_id", "450px"), $this->conteudo);
		$this->conteudo = str_replace("[pls_id]", $this->geraSelect($this->buscaServicos(), isset($dados["pls_id"]) ? $dados["pls_id"] : null, true, "pls_id", "450px"), $this->conteudo);
		$this->conteudo = str_replace("[afc_id]", $this->geraSelect($this->buscaCategorias(), isset($dados["afc_id"]) ? $dados["afc_id"] : null, true, "afc_id", "450px"), $this->conteudo);
		$this->conteudo = str_replace("[cft_id]", $this->geraSelect($this->buscaTiposFiscais(), isset($dados["cft_id"]) ? $dados["cft_id"] : null, true, "cft_id", "450px"), $this->conteudo);
	}

	function sanitizaPost() {

		$clf_taxa = str_replace(",", ".", trim($_POST["clf_taxa"]));
		$clf_taxa = is_numeric($clf_taxa) ? number_format((float) $clf_taxa, 2, ".", "") : "0.00";

		$pla_id = preg_replace("/[^0-9]/", "", isset($_POST["pla_id"]) ? $_POST["pla_id"] : "");
		$pla_id = $pla_id !== "" ? intval($pla_id) : "NULL";

		$pls_id = preg_replace("/[^0-9]/", "", isset($_POST["pls_id"]) ? $_POST["pls_id"] : "");
		$pls_id = $pls_id !== "" ? intval($pls_id) : "NULL";

		$afc_id = preg_replace("/[^0-9]/", "", isset($_POST["afc_id"]) ? $_POST["afc_id"] : "");
		$afc_id = $afc_id !== "" ? intval($afc_id) : "NULL";

		$cft_id = preg_replace("/[^0-9]/", "", isset($_POST["cft_id"]) ? $_POST["cft_id"] : "");
		$cft_id = $cft_id !== "" ? intval($cft_id) : "NULL";

		return array(
			"clf_taxa" => $clf_taxa,
			"pla_id" => $pla_id,
			"pls_id" => $pls_id,
			"afc_id" => $afc_id,
			"cft_id" => $cft_id
		);
	}
}
?>
