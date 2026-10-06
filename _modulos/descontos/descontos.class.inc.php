<?

class descontos {

	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;

	var $valor_tipos = array(
		"1" => "Dinheiro",
		"2" => "Percentual"
	);

	function __construct($bancodedados, $permissoes, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
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

		$query = $this->bancodedados->query("
			SELECT
				mod_id AS id,
				mod_nome AS nome,
				mod_descricao AS descricao
			FROM
				tab_modulos
			WHERE
				mod_nome = '$nome'");

		$this->dados_modulo = $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosAcao($nome) {

		$query = $this->bancodedados->query("
			SELECT
				aco_id AS id,
				aco_nome AS nome,
				aco_descricao AS descricao,
				act_id AS tipo
			FROM
				tab_acoes
			WHERE
				aco_nome = '$nome' AND
				mod_id = '{$this->dados_modulo["id"]}'");

		if (($this->dados_acao = $this->bancodedados->resultadoArray($query)) != false) {
			$this->acao = $this->dados_acao["nome"];
		}

	}

	function buscaDadosItem($id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos.des_id AS id,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros,
				tab_descontos.des_adicionar_retorno AS adicionar_retorno,
				tab_descontos.des_fidelidade AS fidelidade,
				tab_descontos_tipos.det_id AS tipo_id,
				tab_descontos_tipos.det_nome AS tipo_nome,
				tab_descontos_status.dea_id AS status_id,
				tab_descontos_status.dea_nome AS status_nome,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome,
				IF(tab_descontos.pli_id IS NOT NULL, tab_planos_sistemas.pli_id, '-1') AS sistema_id,
				IF(tab_descontos.pli_id IS NOT NULL, tab_planos_sistemas.pli_nome, 'Qualquer um') AS sistema_nome
			FROM
				tab_descontos
				INNER JOIN tab_descontos_tipos ON (tab_descontos_tipos.det_id = tab_descontos.det_id)
				INNER JOIN tab_descontos_status ON (tab_descontos_status.dea_id = tab_descontos.dea_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_descontos.pls_id)
				LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_descontos.pli_id)
			WHERE
				tab_descontos.des_id = $id");

		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
			$this->dados_item["parametros"] = unserialize($this->dados_item["parametros"]);
		}

	}

	function temDesconto($id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(dec_id)
			FROM
				tab_descontos_concessoes
			WHERE
				des_id = $id");

		return $this->bancodedados->resultado($query) > 0;

	}

	###################
	### Busca Itens ###
	###################

	function buscaServicos() {

		$servicos = array();

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS id,
				pls_nome AS nome
			FROM
				tab_planos_servicos
			ORDER BY
				pls_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}

		return $servicos;

	}

	function buscaPlanos($servico_id) {

		$planos = array();

		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				pla_nome AS nome
			FROM
				tab_planos
			WHERE
				pls_id = $servico_id
			ORDER BY
				pla_nome,
				pla_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[$dados["id"]] = $dados["nome"];
		}

		return $planos;

	}

	function buscaDescontos($servico_id) {

		$descontos = array();

		$query = $this->bancodedados->query("
			SELECT
				des_id AS id,
				des_nome AS nome
			FROM
				tab_descontos
			WHERE
				pls_id = $servico_id
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[$dados["id"]] = $dados["nome"];
		}

		return $descontos;

	}

	function buscaSistemas() {

		$sistemas = array("-1" => "Qualquer um");

		$query = $this->bancodedados->query("
			SELECT
				pli_id AS id,
				pli_nome AS nome
			FROM
				tab_planos_sistemas
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$sistemas[$dados["id"]] = $dados["nome"];
		}

		return $sistemas;

	}

	function buscaTipos() {

		$tipos = array();

		$query = $this->bancodedados->query("
			SELECT
				det_id AS id,
				det_nome AS nome
			FROM
				tab_descontos_tipos
			ORDER BY
				det_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}

		return $tipos;

	}

	function buscaStatus() {

		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				dea_id AS id,
				dea_nome AS nome
			FROM
				tab_descontos_status
			ORDER BY
				dea_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}

		return $status;

	}

	function buscaOrigens() {

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[$dados["id"]] = $dados["nome"];
		}

		return $origens;

	}

	function buscaValor($valor) {

		if (!$valor) {
			$valores["valor"] = "";
			$valores["tipo"] = "";
		} else if ($valor <= 1) {
			$valores["valor"] = intval($valor * 100) . "%";
			$valores["tipo"] = 2;
		} else {
			$valores["valor"] = $this->formataDinheiro($valor);
			$valores["tipo"] = 1;
		}

		return $valores;

	}

	function buscaValidades() {

		$validades = array("Indeterminada");

		for ($i = 1; $i <= 12; $i++) {
			$validades[$i] = $i.(($i > 1) ? " Meses" : " Mês");
		}

		return $validades;

	}

	function buscaTiposValidade() {

		return array(1 => "Meses", 2 => "Data");

	}

	#############
	### Geral ###
	#############

	function geraSelect($itens, $selecionado = null, $item_nulo = false) {

		$select = $item_nulo ? "<option></option>" : "";

		if ($itens) {
			foreach ($itens as $valor => $item) {
				$select .= "<option value=\"$valor\"".(isset($selecionado) && (is_array($selecionado) ? in_array($valor, $selecionado) : $valor == $selecionado) ? " selected" : "").">$item</option>";
			}
		}

		return $select;

	}

	function formataDinheiro($valor, $moeda = 'R$') {

		$valor = preg_replace("/[^0-9]/", "", intval($valor));
		$valor = number_format($valor / 100, 2, ",", ".");
		if ($moeda) $valor = $moeda." ".$valor;

		return $valor;

	}

	function formataValor($valor) {

		return $valor > 1 ? $this->formataDinheiro($valor) : ($valor > 0 ? ($valor * 100)."%" : "");

	}

	function trataParametros($parametros) {

		$parametros["instalacao"] = preg_replace("/[^0-9]/", "", $parametros["instalacao"]);
		$parametros["mensalidade"] = preg_replace("/[^0-9]/", "", $parametros["mensalidade"]);
		$parametros["parcelamento_maximo"] = preg_replace("/[^0-9]/", "", $parametros["parcelamento_maximo"]);

		if ($parametros["instalacao"]) {
			if ($parametros["instalacao_tipo"] == 2) {
				$parametros["instalacao"] = $parametros["instalacao"] / 100;
				$parametros["instalacao"] = substr($parametros["instalacao"], 0, 4);
			}
			$parametros["parcelamento_maximo"] = $parametros["parcelamento_maximo"] < 1 ? "1" : ($parametros["parcelamento_maximo"] > 12 ? "12" : $parametros["parcelamento_maximo"]);
		} else {
			$parametros["parcelamento_maximo"] = "";
		}

		if ($parametros["mensalidade"]) {
			if ($parametros["mensalidade_tipo"] == 2) {
				$parametros["mensalidade"] = $parametros["mensalidade"] / 100;
				$parametros["mensalidade"] = substr($parametros["mensalidade"], 0, 4);
			}
		}

		$parametros["aplica_plano_principal"] = $parametros["aplica_plano_principal"] ? 1 : 0;
		$parametros["aplica_plano_adicional"] = $parametros["aplica_plano_adicional"] ? 1 : 0;
		$parametros["aplica_condominio"] = $parametros["aplica_condominio"] ? 1 : 0;
		$parametros["aplica_plano"] = $parametros["aplica_plano"] ? 1 : 0;

		unset($parametros["fidelidade"], $parametros["nome"], $parametros["servico"], $parametros["tipo"], $parametros["adicionar_retorno"], $parametros["instalacao_tipo"], $parametros["mensalidade_tipo"], $parametros["status"], $parametros["salvar"]);

		return serialize($parametros);

	}

	function buscaDescontosParametros($descontos) {

		$retorno = array();

		$query = $this->bancodedados->query("
			SELECT
				des_id AS id,
				des_parametros AS parametros
			FROM
				tab_descontos
			WHERE
				des_id IN ($descontos)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$retorno[] = $dados;
		}

		return $retorno;

	}

	function ajustarDescontosExcludentes($desconto_id, $descontos_novos, $descontos_antigos = null) {

		$descontos_novos = array_diff($descontos_novos, array($desconto_id));

		if ($descontos_antigos) {
			$descontos_antigos = array_diff($descontos_antigos, array($desconto_id));
			$descontos_excluidos = array_diff($descontos_antigos, $descontos_novos);
		}

		$descontos_excluidos = array_filter($descontos_excluidos, function ($value) { return $value !== null; });
		$descontos_novos = array_filter($descontos_novos, function ($value) { return $value !== null; });

		// Atualiza Excluídos
		if (count($descontos_excluidos) && ($descontos = $this->buscaDescontosParametros(implode(", ", $descontos_excluidos)))) {
			foreach ($descontos as $desconto) {
				$desconto["parametros"]["descontos"] = $desconto["parametros"]["descontos"] ? $desconto["parametros"]["descontos"] : array();
				$desconto["parametros"]["descontos"] = array_diff($desconto["parametros"]["descontos"], array($desconto_id));
				$this->bancodedados->query("
					UPDATE
						tab_descontos
					SET
						des_parametros = '".serialize($desconto["parametros"])."'
					WHERE
						des_id = {$desconto["id"]}");
			}
		}

		// Atualiza Novos
		if (count($descontos_novos) && ($descontos = $this->buscaDescontosParametros(implode(", ", $descontos_novos)))) {
			foreach ($descontos as $desconto) {
				$desconto["parametros"]["descontos"] = $desconto["parametros"]["descontos"] ? $desconto["parametros"]["descontos"] : array();
				$desconto["parametros"]["descontos"] = array_unique(array_merge($desconto["parametros"]["descontos"], array($desconto_id)));
				$this->bancodedados->query("
					UPDATE
						tab_descontos
					SET
						des_parametros = '".serialize($desconto["parametros"])."'
					WHERE
						des_id = {$desconto["id"]}");
			}
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

			$this->conteudo .= $this->carregaArquivo("descontos.adicionar.inc.php");

			$this->conteudo = str_replace("[servico]", $this->geraSelect($this->buscaServicos(), null, true), $this->conteudo);
			$this->conteudo = str_replace("[origens]", $this->geraSelect($this->buscaOrigens()), $this->conteudo);
			$this->conteudo = str_replace("[sistema]", $this->geraSelect($this->buscaSistemas(), null, true), $this->conteudo);
			$this->conteudo = str_replace("[tipos]", $this->geraSelect($this->valor_tipos, null, true), $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), null, true), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), null, true), $this->conteudo);
			$this->conteudo = str_replace("[validade]", $this->geraSelect($this->buscaValidades(), "-1", true), $this->conteudo);
			$this->conteudo = str_replace("[validade_tipo]", $this->geraSelect($this->buscaTiposValidade(), null, true), $this->conteudo);
			$this->conteudo = str_replace("[fixas]", $this->geraSelect(array("0" => "Variável", "1" => "Fixa"), "-1", true), $this->conteudo);
			$this->conteudo = str_replace("[auto_aprovado]", $this->geraSelect(array("0" => "Não", "1" => "Sim"), "-1", true), $this->conteudo);
			$this->conteudo = str_replace("[adicionar_retorno]", $this->geraSelect(array("0" => "Não", "1" => "Sim"), $this->dados_item["adicionar_retorno"], true), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);

		} else {

			$this->ajustarDescontosExcludentes($_GET["i"], $_POST["descontos"]);

			$parametros = $this->trataParametros($_POST);

			$_POST["sistema"] = !$_POST["sistema"] || $_POST["sistema"] == "-1" ? "NULL" : $_POST["sistema"];
			$_POST["nome"] = trim($_POST["nome"]);
			$_POST["fidelidade"] = $_POST["tipo"] == 2 ? intval($_POST["fidelidade"], 10) : 0;

			$this->bancodedados->query("
				INSERT INTO
					tab_descontos
				SET
					des_nome = '{$_POST["nome"]}',
					det_id = {$_POST["tipo"]},
					pls_id = {$_POST["servico"]},
					pli_id = {$_POST["sistema"]},
					dea_id = {$_POST["status"]},
					des_adicionar_retorno = {$_POST["adicionar_retorno"]},
					des_parametros = '$parametros',
					des_fidelidade = {$_POST["fidelidade"]}");

			$id = $this->bancodedados->ultimoIdInserido();

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");

		}

	}

	###########
	### Ver ###
	###########

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$select_sim_nao = array ("0" => "Não", "1" => "Sim");
			$select_fixa_variavel = array ("0" => "Variável", "1" => "Fixa");
			$instalacao = $this->buscaValor($this->dados_item["parametros"]["instalacao"]);
			$mensalidade = $this->buscaValor($this->dados_item["parametros"]["mensalidade"]);
			$validade = $this->dados_item["parametros"]["validade"] ? $parametros["validade"] . " meses" : "Indeterminada";

			$aplicacoes = array();
			if ($this->dados_item["parametros"]["aplica_plano_principal"]) $aplicacoes[] = "Assinaturas (Plano Principal)";
			if ($this->dados_item["parametros"]["aplica_plano_adicional"]) $aplicacoes[] = "Assinaturas (Plano Adicional)";
			if ($this->dados_item["parametros"]["aplica_condominio"]) $aplicacoes[] = "Condomínios";
			if ($this->dados_item["parametros"]["aplica_plano"]) $aplicacoes[] = "Planos";

			$botoes = array();
			if (!$this->temDesconto($_GET["i"]) && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

			$aplicacao = implode(" / ", $aplicacoes);

			$this->conteudo .= $this->carregaArquivo("descontos.ver.inc.php");

			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			$this->conteudo = str_replace("[origens]", implode("<br>", $this->dados_item["parametros"]["origens"]), $this->conteudo);
			$this->conteudo = str_replace("[planos]", implode("<br>", $this->dados_item["parametros"]["planos"]), $this->conteudo);
			$this->conteudo = str_replace("[sistema]", $this->dados_item["sistema_nome"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"].($this->dados_item["fidelidade"] ? "{$this->dados_item["fidelidade"]} meses" : ""), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[carencia]", $this->dados_item["parametros"]["carencia"], $this->conteudo);
			$this->conteudo = str_replace("[cota_mensal]", $this->dados_item["parametros"]["cota_mensal"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao]", $instalacao["valor"].($this->dados_item["parametros"]["instalacao"] ? " ({$select_fixa_variavel[$this->dados_item["parametros"]["instalacao_fixa"]]})" : ""), $this->conteudo);
			$this->conteudo = str_replace("[mensalidade]", $mensalidade["valor"].($this->dados_item["parametros"]["mensalidade"] ? " ({$select_fixa_variavel[$this->dados_item["parametros"]["mensalidade_fixa"]]})" : ""), $this->conteudo);
			$this->conteudo = str_replace("[validade]", "$validade ({$select_fixa_variavel[$this->dados_item["parametros"]["validade_fixa"]]})", $this->conteudo);
			$this->conteudo = str_replace("[aplicacoes]", $aplicacao, $this->conteudo);
			$this->conteudo = str_replace("[auto_aprovado]", $select_sim_nao[$this->dados_item["parametros"]["auto_aprovado"]], $this->conteudo);
			$this->conteudo = str_replace("[adicionar_retorno]", $select_sim_nao[$this->dados_item["parametros"]["adicionar_retorno"]], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

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

				$instalacao = $this->buscaValor($this->dados_item["parametros"]["instalacao"]);
				$mensalidade = $this->buscaValor($this->dados_item["parametros"]["mensalidade"]);
				$select_fixa_variavel = array ("0" => "Variável", "1" => "Fixa");
				$select_sim_nao = array ("0" => "Não", "1" => "Sim");

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if (!$this->temDesconto($_GET["i"]) && $this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

				$this->conteudo .= $this->carregaArquivo("descontos.editar.inc.php");

				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[servico]", $this->geraSelect($this->buscaServicos(), $this->dados_item["servico_id"], true), $this->conteudo);
				$this->conteudo = str_replace("[origens]", $this->geraSelect($this->buscaOrigens(), $this->dados_item["parametros"]["origens"]), $this->conteudo);
				$this->conteudo = str_replace("[planos]", $this->geraSelect($this->buscaPlanos($this->dados_item["servico_id"]), $this->dados_item["parametros"]["planos"]), $this->conteudo);
				$this->conteudo = str_replace("[planos_disabled]", $this->dados_item["servico_id"] ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace("[descontos]", $this->geraSelect($this->buscaDescontos($this->dados_item["servico_id"]), $this->dados_item["parametros"]["descontos"]), $this->conteudo);
				$this->conteudo = str_replace("[descontos_disabled]", $this->dados_item["servico_id"] ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace("[sistema]", $this->geraSelect($this->buscaSistemas(), $this->dados_item["sistema_id"], true), $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true), $this->conteudo);
				$this->conteudo = str_replace("[fidelidade]", $this->dados_item["fidelidade"] ? $this->dados_item["fidelidade"] : "", $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"], true), $this->conteudo);
				$this->conteudo = str_replace("[carencia]", $this->dados_item["parametros"]["carencia"], $this->conteudo);
				$this->conteudo = str_replace("[cota_mensal]", $this->dados_item["parametros"]["cota_mensal"], $this->conteudo);
				$this->conteudo = str_replace("[instalacao]", $instalacao["valor"], $this->conteudo);
				$this->conteudo = str_replace("[parcelamento_maximo]", $this->dados_item["parametros"]["parcelamento_maximo"], $this->conteudo);
				$this->conteudo = str_replace("[instalacao_tipo]", $this->geraSelect($this->valor_tipos, $instalacao["tipo"], "n"), $this->conteudo);
				$this->conteudo = str_replace("[mensalidade]", $mensalidade["valor"], $this->conteudo);
				$this->conteudo = str_replace("[mensalidade_tipo]", $this->geraSelect($this->valor_tipos, $mensalidade["tipo"], "n"), $this->conteudo);
				$this->conteudo = str_replace("[instalacao_fixa]", $this->geraSelect($select_fixa_variavel, isset($this->dados_item["parametros"]["instalacao_fixa"]) ? $this->dados_item["parametros"]["instalacao_fixa"] : "-1", true), $this->conteudo);
				$this->conteudo = str_replace("[mensalidade_fixa]", $this->geraSelect($select_fixa_variavel, isset($this->dados_item["parametros"]["mensalidade_fixa"]) ? $this->dados_item["parametros"]["mensalidade_fixa"] : "-1", true), $this->conteudo);
				$this->conteudo = str_replace("[validade]", $this->dados_item["parametros"]["validade"], $this->conteudo);
				$this->conteudo = str_replace("[validade_tipo]", $this->geraSelect($this->buscaTiposValidade(), !$this->dados_item["parametros"]["validade_tipo"] && $this->dados_item["parametros"]["validade"] ? 1 : $this->dados_item["parametros"]["validade_tipo"], true), $this->conteudo);
				$this->conteudo = str_replace("[validade_fixa]", $this->geraSelect($select_fixa_variavel, isset($this->dados_item["parametros"]["validade_fixa"]) ? $this->dados_item["parametros"]["validade_fixa"] : "-1", true), $this->conteudo);
				$this->conteudo = str_replace("[aplica_plano_principal]", ($this->dados_item["parametros"]["aplica_plano_principal"] == '1') ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_plano_adicional]", ($this->dados_item["parametros"]["aplica_plano_adicional"] == '1')  ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_condominio]", ($this->dados_item["parametros"]["aplica_condominio"] == '1')  ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[aplica_plano]", ($this->dados_item["parametros"]["aplica_plano"] == '1')  ? "checked" : "", $this->conteudo);
				$this->conteudo = str_replace("[auto_aprovado]", $this->geraSelect($select_sim_nao, $this->dados_item["parametros"]["auto_aprovado"], true), $this->conteudo);
				$this->conteudo = str_replace("[adicionar_retorno]", $this->geraSelect($select_sim_nao, $this->dados_item["adicionar_retorno"], true), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);

			} else {

				$this->ajustarDescontosExcludentes($_GET["i"], $_POST["descontos"], $this->dados_item["parametros"]["descontos"]);

				$parametros = $this->trataParametros($_POST);

				$_POST["sistema"] = (!$_POST["sistema"] || $_POST["sistema"] == "-1") ? "NULL" : $_POST["sistema"];
				$_POST["nome"] = trim($_POST["nome"]);
				$_POST["fidelidade"] = $_POST["tipo"] == 2 ? intval($_POST["fidelidade"], 10) : 0;

				$this->bancodedados->query("
					UPDATE
						tab_descontos
					SET
						des_nome = '{$_POST["nome"]}',
						pls_id = {$_POST["servico"]},
						pli_id = {$_POST["sistema"]},
						det_id = {$_POST["tipo"]},
						dea_id = {$_POST["status"]},
						des_parametros = '$parametros',
						des_adicionar_retorno = {$_POST["adicionar_retorno"]},
						des_fidelidade = {$_POST["fidelidade"]}
					WHERE
						des_id = {$_GET["i"]}");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

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

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("descontos.remover.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);

			} else {

				if (!$this->temDesconto($_GET["i"])) {

					$this->bancodedados->query("
						DELETE FROM 
							tab_descontos 
						WHERE 
							des_id = '{$_GET["i"]}'");

					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");

			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}

	}

	##############
	### Listar ###
	##############

	function listar() {

		if (!$_GET["f"] && !$_GET["p"]) {
			$_GET["f"]["status"] = 1;
		}

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
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

		// Nome
		$itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_descontos.des_nome LIKE '%[valor]%')");

		// Serviços
		$servicos = $this->buscaServicos();
		$select_servicos = array();
		foreach ($servicos as $id => $nome) {
			$select_servicos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "servico", "descricao" => "Serviço", "valor" => $select_servicos, "where" => "tab_planos_servicos.pls_id = [valor]");

		// Sistema
		$sistemas = $this->buscaSistemas();
		$select_sistemas = array();
		foreach ($sistemas as $id => $nome) {
			$select_sistemas[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema", "valor" => $select_sistemas, "where" => "IF('[valor]' = '-1', tab_descontos.pli_id IS NULL, tab_descontos.pli_id = [valor])");

		// Tipos
		$tipos = $this->buscaTipos();
		$select_tipos = array();
		foreach ($tipos as $id => $nome) {
			$select_tipos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "tab_descontos_tipos.det_id = [valor]");

		// Status
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "tab_descontos_status.dea_id = [valor]");

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
				tab_descontos.des_id AS id,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros,
				tab_planos_servicos.pls_nome AS servico,
				IF(tab_descontos.pli_id IS NOT NULL, tab_planos_sistemas.pli_nome, 'Qualquer um') AS sistema,
				tab_descontos_tipos.det_nome AS tipo,
				tab_descontos_status.dea_nome AS status
			FROM
				tab_descontos
				INNER JOIN tab_descontos_tipos ON (tab_descontos_tipos.det_id = tab_descontos.det_id)
				INNER JOIN tab_descontos_status ON (tab_descontos_status.dea_id = tab_descontos.dea_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_descontos.pls_id)
				LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_descontos.pli_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_planos_servicos.pls_nome,
				tab_descontos.des_nome,
				tab_descontos.des_id
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$parametros = unserialize($dados["parametros"]);

			if ($this->permissoes["descontos"]["ver"]) $dados["link"] = "?m=descontos&a=ver&i={$dados["id"]}";
			$dados["instalacao"] = $this->formataValor($parametros["instalacao"]);
			$dados["mensalidade"] = $this->formataValor($parametros["mensalidade"]);
			$dados["tipo"] = str_replace(" [", "<br>[", $dados["tipo"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagem($itens) {

		$cabecalho = array("contador" => "#", "nome" => "Nome", "servico" => "Serviço", "tipo" => "Tipo", "instalacao" => "Instalação", "mensalidade" => "Mensalidade", "status" => "Status");

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right", "left", "center", "center", "right", "right"));

	}

}

?>
