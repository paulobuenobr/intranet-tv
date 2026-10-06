<?php

require_once("_global/modulo.class.inc.php");

class ajustes_financeiros_categorias extends modulo {

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

	function arraySimNao() {

		return array(
			"0" => "Não",
			"1" => "Sim",
		);
	}

	function arrayIssRetido() {

		return array(
			"1" => "ISS Retido",
			"2" => "ISS não Retido",
		);
	}

	function buscaCodigoServico() {

		$servicos = array();
		$query = $this->bancodedados->query("
			SELECT
				id,
				cod_serv AS nome,
				descricao
			FROM
				tab_notas_servicos
			ORDER BY
				cod_serv");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descricao = strlen($dados["descricao"]) > 150 ? substr($dados["descricao"], 0, 150) . "..." : $dados["descricao"];
			$servicos[$dados["id"]] = $dados["nome"] . " - " . $descricao;
		}

		return $servicos;
	}

	function buscaCodigoCnae() {

		$cnaes = array();
		$query = $this->bancodedados->query("
			SELECT
				id,
				cnae AS nome,
				descricao
			FROM
				tab_notas_cnae
			ORDER BY
				cnae");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descricao = strlen($dados["descricao"]) > 150 ? substr($dados["descricao"], 0, 150) . "..." : $dados["descricao"];
			$cnaes[$dados["id"]] = $dados["nome"] . " - " . $descricao;
		}

		return $cnaes;
	}

	function buscaCodigoNds() {

		$nds = array();
		$query = $this->bancodedados->query("
			SELECT
				nbs_limpo AS id,
				nbs,
				descricao
			FROM
				tab_notas_nbs
			ORDER BY
				nbs");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descricao = strlen($dados["descricao"]) > 150 ? substr($dados["descricao"], 0, 150) . "..." : $dados["descricao"];
			$nds[$dados["id"]] = $dados["nbs"] . " - " . $descricao;
		}

		return $nds;
	}

	function labelSimNao($valor) {

		return "{$valor}" === "1" ? "Sim" : "Não";
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
			"nome" => "afc_nome",
			"descricao" => "Nome",
			"valor" => "",
			"where" => "(tab_ajustes_financeiros_categorias.afc_nome LIKE '%[valor]%')"
		);

		$opcoes = array();
		foreach ($this->arraySimNao() as $id => $nome) {
			$opcoes[] = array("id" => $id, "nome" => $nome);
		}

		$itens[] = array(
			"tipo" => "select",
			"nome" => "afc_disponivel",
			"descricao" => "Disponível",
			"valor" => $opcoes,
			"where" => "(tab_ajustes_financeiros_categorias.afc_disponivel = '[valor]')"
		);

		$itens[] = array(
			"tipo" => "select",
			"nome" => "gerar_nfse",
			"descricao" => "Gerar NFS-e",
			"valor" => $opcoes,
			"where" => "(tab_ajustes_financeiros_categorias.gerar_nfse = '[valor]')"
		);

		$itens[] = array(
			"tipo" => "select",
			"nome" => "custom",
			"descricao" => "Tipo Custom",
			"valor" => $opcoes,
			"where" => "(tab_ajustes_financeiros_categorias.custom = '[valor]')"
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
				afc_id AS id,
				afc_nome AS nome,
				IF(afc_acrescimo = '1', 'Sim', 'Não') AS acrescimo,
				IF(afc_decrescimo = '1', 'Sim', 'Não') AS decrescimo,
				IF(afc_bonificacao = '1', 'Sim', 'Não') AS bonificacao,
				IF(afc_disponivel = '1', 'Sim', 'Não') AS disponivel,
				IF(IFNULL(custom, 0) = 1, 'Sim', 'Não') AS custom,
				IF(gerar_nfse = 1, 'Sim', 'Não') AS gerar_nfse
			FROM
				tab_ajustes_financeiros_categorias
			WHERE
				$filtro->parametros_sql
			ORDER BY
				afc_nome
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
			"nome" => "Nome",
			"acrescimo" => "Acréscimo",
			"decrescimo" => "Decréscimo",
			"bonificacao" => "Bonificação",
			"disponivel" => "Disponível",
			"custom" => "Custom",
			"gerar_nfse" => "NFS-e"
		);

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 40, 0, 70, 70, 80, 70, 60, 50), null, array("right", "center", "left", "center", "center", "center", "center", "center", "center"));
	}

	function buscaDadosItem($id) {

		$id = preg_replace("/[^0-9]/", "", $id);
		$query = $this->bancodedados->query("
			SELECT
				*
			FROM
				tab_ajustes_financeiros_categorias
			WHERE
				afc_id = '{$id}'
			LIMIT 1");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$this->dados_item = $dados;
			$this->dados_item["id"] = $dados["afc_id"];
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
			$servicos = $this->buscaCodigoServico();
			$cnaes = $this->buscaCodigoCnae();
			$listaNds = $this->buscaCodigoNds();
			$iss = $this->arrayIssRetido();

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) {
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\'">';
			}
			if ($this->ehAdmin()) {
				$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\'">';
			}
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=listar\'">';

			$this->conteudo .= $this->carregaArquivo("ajustes_financeiros_categorias.ver.inc.php");
			$this->conteudo = str_replace("[afc_id]", $this->dados_item["afc_id"], $this->conteudo);
			$this->conteudo = str_replace("[afc_nome]", htmlspecialchars($this->dados_item["afc_nome"]), $this->conteudo);
			$this->conteudo = str_replace("[afc_acrescimo]", $this->labelSimNao($this->dados_item["afc_acrescimo"]), $this->conteudo);
			$this->conteudo = str_replace("[afc_decrescimo]", $this->labelSimNao($this->dados_item["afc_decrescimo"]), $this->conteudo);
			$this->conteudo = str_replace("[afc_bonificacao]", $this->labelSimNao($this->dados_item["afc_bonificacao"]), $this->conteudo);
			$this->conteudo = str_replace("[afc_solicitar_nf]", $this->labelSimNao($this->dados_item["afc_solicitar_nf"]), $this->conteudo);
			$this->conteudo = str_replace("[afc_disponivel]", $this->labelSimNao($this->dados_item["afc_disponivel"]), $this->conteudo);
			$this->conteudo = str_replace("[custom]", $this->labelSimNao(isset($this->dados_item["custom"]) ? $this->dados_item["custom"] : 0), $this->conteudo);
			$this->conteudo = str_replace("[gerar_nfse]", $this->labelSimNao($this->dados_item["gerar_nfse"]), $this->conteudo);
			$this->conteudo = str_replace("[codigo_servico_id]", isset($servicos[$this->dados_item["codigo_servico_id"]]) ? $servicos[$this->dados_item["codigo_servico_id"]] : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[cnae_id]", isset($cnaes[$this->dados_item["cnae_id"]]) ? $cnaes[$this->dados_item["cnae_id"]] : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[aliquota]", $this->dados_item["aliquota"] !== null && $this->dados_item["aliquota"] !== "" ? $this->dados_item["aliquota"] : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[issRetido]", isset($iss[$this->dados_item["issRetido"]]) ? $iss[$this->dados_item["issRetido"]] : "&mdash;", $this->conteudo);
			$this->conteudo = str_replace("[nds]", isset($listaNds[$this->dados_item["nds"]]) ? $listaNds[$this->dados_item["nds"]] : ($this->dados_item["nds"] ? $this->dados_item["nds"] : "&mdash;"), $this->conteudo);
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

				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros_categorias.editar.inc.php");
				$this->preencheFormulario($this->dados_item);
				$this->conteudo = str_replace("[afc_id]", $this->dados_item["afc_id"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
			} else {
				$dados = $this->sanitizaPost();
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros_categorias
					SET
						afc_nome = '{$dados["afc_nome"]}',
						afc_acrescimo = '{$dados["afc_acrescimo"]}',
						afc_decrescimo = '{$dados["afc_decrescimo"]}',
						afc_bonificacao = '{$dados["afc_bonificacao"]}',
						afc_solicitar_nf = '{$dados["afc_solicitar_nf"]}',
						afc_disponivel = '{$dados["afc_disponivel"]}',
						custom = {$dados["custom"]},
						gerar_nfse = {$dados["gerar_nfse"]},
						codigo_servico_id = {$dados["codigo_servico_id"]},
						cnae_id = {$dados["cnae_id"]},
						aliquota = {$dados["aliquota"]},
						issRetido = {$dados["issRetido"]},
						nds = {$dados["nds"]}
					WHERE
						afc_id = '{$_GET["i"]}'");

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

			$this->conteudo .= $this->carregaArquivo("ajustes_financeiros_categorias.adicionar.inc.php");
			$this->preencheFormulario(array(
				"afc_nome" => "",
				"afc_acrescimo" => "0",
				"afc_decrescimo" => "0",
				"afc_bonificacao" => "0",
				"afc_solicitar_nf" => "0",
				"afc_disponivel" => "1",
				"custom" => "0",
				"gerar_nfse" => "0",
				"codigo_servico_id" => "",
				"cnae_id" => "",
				"aliquota" => "",
				"issRetido" => "2",
				"nds" => ""
			));
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
		} else {
			$dados = $this->sanitizaPost();
			$this->bancodedados->query("
				INSERT INTO
					tab_ajustes_financeiros_categorias
				SET
					afc_nome = '{$dados["afc_nome"]}',
					afc_acrescimo = '{$dados["afc_acrescimo"]}',
					afc_decrescimo = '{$dados["afc_decrescimo"]}',
					afc_bonificacao = '{$dados["afc_bonificacao"]}',
					afc_solicitar_nf = '{$dados["afc_solicitar_nf"]}',
					afc_disponivel = '{$dados["afc_disponivel"]}',
					custom = {$dados["custom"]},
					gerar_nfse = {$dados["gerar_nfse"]},
					codigo_servico_id = {$dados["codigo_servico_id"]},
					cnae_id = {$dados["cnae_id"]},
					aliquota = {$dados["aliquota"]},
					issRetido = {$dados["issRetido"]},
					nds = {$dados["nds"]}");

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

				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros_categorias.remover.inc.php");
				$this->conteudo = str_replace("[afc_nome]", htmlspecialchars($this->dados_item["afc_nome"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {
				$this->bancodedados->query("DELETE FROM tab_ajustes_financeiros_categorias WHERE afc_id = '{$_GET["i"]}'");
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}

	function preencheFormulario($dados) {
		
		$nds = !empty($dados["nds"]) ? $dados["nds"] : null;
		$nfse_habilitado = isset($dados["gerar_nfse"]) && "{$dados["gerar_nfse"]}" === "1";

		$this->conteudo = str_replace("[afc_nome]", htmlspecialchars(isset($dados["afc_nome"]) ? $dados["afc_nome"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[afc_acrescimo]", $this->geraSelect($this->arraySimNao(), isset($dados["afc_acrescimo"]) ? $dados["afc_acrescimo"] : "0", false, "afc_acrescimo", "150px"), $this->conteudo);
		$this->conteudo = str_replace("[afc_decrescimo]", $this->geraSelect($this->arraySimNao(), isset($dados["afc_decrescimo"]) ? $dados["afc_decrescimo"] : "0", false, "afc_decrescimo", "150px"), $this->conteudo);
		$this->conteudo = str_replace("[afc_bonificacao]", $this->geraSelect($this->arraySimNao(), isset($dados["afc_bonificacao"]) ? $dados["afc_bonificacao"] : "0", false, "afc_bonificacao", "150px"), $this->conteudo);
		$this->conteudo = str_replace("[afc_solicitar_nf]", $this->geraSelect($this->arraySimNao(), isset($dados["afc_solicitar_nf"]) ? $dados["afc_solicitar_nf"] : "0", false, "afc_solicitar_nf", "150px"), $this->conteudo);
		$this->conteudo = str_replace("[afc_disponivel]", $this->geraSelect($this->arraySimNao(), isset($dados["afc_disponivel"]) ? $dados["afc_disponivel"] : "1", false, "afc_disponivel", "150px"), $this->conteudo);
		$this->conteudo = str_replace("[custom]", $this->geraSelect($this->arraySimNao(), isset($dados["custom"]) ? $dados["custom"] : "0", false, "custom", "150px"), $this->conteudo);
		$this->conteudo = str_replace("[gerar_nfse]", $this->geraSelect($this->arraySimNao(), isset($dados["gerar_nfse"]) ? $dados["gerar_nfse"] : "0", false, "gerar_nfse", "150px", "mudaGerarNota(this.value)"), $this->conteudo);
		$this->conteudo = str_replace("[codigo_servico_id]", $this->geraSelect($this->buscaCodigoServico(), isset($dados["codigo_servico_id"]) ? $dados["codigo_servico_id"] : null, true, "codigo_servico_id", "450px", null, $nfse_habilitado), $this->conteudo);
		$this->conteudo = str_replace("[cnae_id]", $this->geraSelect($this->buscaCodigoCnae(), isset($dados["cnae_id"]) ? $dados["cnae_id"] : null, true, "cnae_id", "450px", null, $nfse_habilitado), $this->conteudo);
		$this->conteudo = str_replace("[aliquota]", htmlspecialchars(isset($dados["aliquota"]) ? $dados["aliquota"] : ""), $this->conteudo);
		$this->conteudo = str_replace("[issRetido]", $this->geraSelect($this->arrayIssRetido(), isset($dados["issRetido"]) ? $dados["issRetido"] : "2", true, "issRetido", "200px", null, $nfse_habilitado), $this->conteudo);
		$this->conteudo = str_replace("[nds]", $this->geraSelect($this->buscaCodigoNds(), $nds, true, "nds", "450px", null, $nfse_habilitado), $this->conteudo);
	}

	function sanitizaPost() {

		$afc_nome = addslashes(trim($_POST["afc_nome"]));
		$afc_acrescimo = isset($_POST["afc_acrescimo"]) && $_POST["afc_acrescimo"] == "1" ? "1" : "0";
		$afc_decrescimo = isset($_POST["afc_decrescimo"]) && $_POST["afc_decrescimo"] == "1" ? "1" : "0";
		$afc_bonificacao = isset($_POST["afc_bonificacao"]) && $_POST["afc_bonificacao"] == "1" ? "1" : "0";
		$afc_solicitar_nf = isset($_POST["afc_solicitar_nf"]) && $_POST["afc_solicitar_nf"] == "1" ? "1" : "0";
		$afc_disponivel = isset($_POST["afc_disponivel"]) && $_POST["afc_disponivel"] == "1" ? "1" : "0";
		$custom = isset($_POST["custom"]) && $_POST["custom"] == "1" ? 1 : 0;
		$gerar_nfse = isset($_POST["gerar_nfse"]) && $_POST["gerar_nfse"] == "1" ? 1 : 0;

		if ($gerar_nfse == 1) {
			$codigo_servico_id = preg_replace("/[^0-9]/", "", $_POST["codigo_servico_id"]);
			$codigo_servico_id = $codigo_servico_id !== "" ? intval($codigo_servico_id) : "NULL";
			$cnae_id = preg_replace("/[^0-9]/", "", $_POST["cnae_id"]);
			$cnae_id = $cnae_id !== "" ? intval($cnae_id) : "NULL";
			$aliquota = str_replace(",", ".", trim($_POST["aliquota"]));
			$aliquota = $aliquota !== "" && is_numeric($aliquota) ? "'" . addslashes($aliquota) . "'" : "NULL";
			$issRetido = preg_replace("/[^0-9]/", "", $_POST["issRetido"]);
			$issRetido = $issRetido !== "" ? intval($issRetido) : "NULL";
			$nds = preg_replace("/[^0-9]/", "", $_POST["nds"]);
			$nds = $nds !== "" ? "'" . addslashes($nds) . "'" : "NULL";
		} else {
			$codigo_servico_id = "NULL";
			$cnae_id = "NULL";
			$aliquota = "NULL";
			$issRetido = "NULL";
			$nds = "NULL";
		}

		return array(
			"afc_nome" => $afc_nome,
			"afc_acrescimo" => $afc_acrescimo,
			"afc_decrescimo" => $afc_decrescimo,
			"afc_bonificacao" => $afc_bonificacao,
			"afc_solicitar_nf" => $afc_solicitar_nf,
			"afc_disponivel" => $afc_disponivel,
			"custom" => $custom,
			"gerar_nfse" => $gerar_nfse,
			"codigo_servico_id" => $codigo_servico_id,
			"cnae_id" => $cnae_id,
			"aliquota" => $aliquota,
			"issRetido" => $issRetido,
			"nds" => $nds
		);
	}
}
?>
