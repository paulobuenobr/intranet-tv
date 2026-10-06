<?

require_once("_global/modulo.class.inc.php");

class email_smtp extends modulo {

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
	### Buscar ###
	##############

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

		$itens = array();

		$itens[] = array("tipo" => "input", "nome" => "usuario", "descricao" => "Usu&aacute;rio", "valor" => "",
			"where" => "(tab_email_smtp.usuario LIKE '%[valor]%')");

		$itens[] = array("tipo" => "input", "nome" => "de", "descricao" => "De", "valor" => "",
			"where" => "(tab_email_smtp.de LIKE '%[valor]%')");

		$itens[] = array("tipo" => "select", "nome" => "ativo", "descricao" => "Ativo", "valor" => $this->buscaOpcoesSituacao(),
			"where" => "(tab_email_smtp.ativo = '[valor]')");

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
				id,
				usuario,
				host,
				porta,
				de,
				seguranca,
				tipo,
				IF(ativo = '1', 'Sim', 'N&atilde;o') AS ativo
			FROM
				tab_email_smtp
			WHERE
				$filtro->parametros_sql
			ORDER BY
				usuario
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
			"usuario" => "Usu&aacute;rio",
			"host" => "Host",
			"porta" => "Porta",
			"de" => "De",
			"seguranca" => "Seguran&ccedil;a",
			"tipo" => "Tipo",
			"ativo" => "Ativo"
		);

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 0, 120, 50, 120, 60, 80, 40), null, array("right", "left", "left", "center", "left", "center", "center", "center"));

	}

	###########
	### Ver ###
	###########

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\'">';
			if ($this->ehAdmin()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

			$this->conteudo .= $this->carregaArquivo("email_smtp.ver.inc.php");

			$this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
			$this->conteudo = str_replace("[host]", $this->dados_item["host"], $this->conteudo);
			$this->conteudo = str_replace("[porta]", $this->dados_item["porta"], $this->conteudo);
			$this->conteudo = str_replace("[de]", $this->dados_item["de"], $this->conteudo);
			$this->conteudo = str_replace("[seguranca]", $this->dados_item["seguranca"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo"], $this->conteudo);
			$this->conteudo = str_replace("[ativo]", $this->dados_item["ativo"] == '1' ? 'Sim' : 'N&atilde;o', $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}

	}

	function ehAdmin($id = null) {

		$user = $id? $id : $_SESSION["usuario"];
		$query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
		$query = $this->bancodedados->query($query);
		$ehAdmin = $this->bancodedados->resultadoArray($query);

		return $ehAdmin['usu_administrador']== 0 ? false : true;
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
				if ($this->ehAdmin()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

				$this->conteudo .= $this->carregaArquivo("email_smtp.editar.inc.php");

				$this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
				$this->conteudo = str_replace("[host]", $this->dados_item["host"], $this->conteudo);
				$this->conteudo = str_replace("[porta]", $this->dados_item["porta"], $this->conteudo);
				$this->conteudo = str_replace("[de]", $this->dados_item["de"], $this->conteudo);
				$this->conteudo = str_replace("[seguranca]", $this->geraSelect($this->buscaOpcoesSeguranca(), $this->dados_item["seguranca"], true, "seguranca", "250px"), $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo"], $this->conteudo);
				$this->conteudo = str_replace("[ativo]", $this->geraSelect($this->buscaOpcoesAtivo(), $this->dados_item["ativo"], false, "ativo", "250px"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);

			} else {

				$_POST["usuario"] = addslashes(trim($_POST["usuario"]));
				$_POST["host"] = addslashes(trim($_POST["host"]));
				$_POST["porta"] = addslashes(trim($_POST["porta"]));
				$_POST["de"] = addslashes(trim($_POST["de"]));
				$_POST["seguranca"] = addslashes(trim($_POST["seguranca"]));
				$_POST["tipo"] = addslashes(trim($_POST["tipo"]));
				$_POST["ativo"] = addslashes(trim($_POST["ativo"]));

				$set_senha = "";
				if (trim($_POST["senha"]) != "") {
					$_POST["senha"] = addslashes(trim($_POST["senha"]));
					$set_senha = ", senha = '{$_POST["senha"]}'";
				}

				$this->bancodedados->query("
					UPDATE
						tab_email_smtp
					SET
						usuario = '{$_POST["usuario"]}',
						host = '{$_POST["host"]}',
						porta = '{$_POST["porta"]}',
						de = '{$_POST["de"]}',
						seguranca = '{$_POST["seguranca"]}',
						tipo = '{$_POST["tipo"]}',
						ativo = '{$_POST["ativo"]}'
						$set_senha
					WHERE
						id = {$_GET["i"]}");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
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

			$this->conteudo .= $this->carregaArquivo("email_smtp.adicionar.inc.php");

			$this->conteudo = str_replace("[seguranca]", $this->geraSelect($this->buscaOpcoesSeguranca(), 'tls', true, "seguranca", "250px"), $this->conteudo);
			$this->conteudo = str_replace("[ativo]", $this->geraSelect($this->buscaOpcoesAtivo(), '1', false, "ativo", "250px"), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);

		} else {

			$_POST["usuario"] = addslashes(trim($_POST["usuario"]));
			$_POST["senha"] = addslashes(trim($_POST["senha"]));
			$_POST["host"] = addslashes(trim($_POST["host"]));
			$_POST["porta"] = addslashes(trim($_POST["porta"]));
			$_POST["de"] = addslashes(trim($_POST["de"]));
			$_POST["seguranca"] = addslashes(trim($_POST["seguranca"]));
			$_POST["tipo"] = addslashes(trim($_POST["tipo"]));
			$_POST["ativo"] = addslashes(trim($_POST["ativo"]));

			$this->bancodedados->query("
				INSERT INTO
					tab_email_smtp
				SET
					usuario = '{$_POST["usuario"]}',
					senha = '{$_POST["senha"]}',
					host = '{$_POST["host"]}',
					porta = '{$_POST["porta"]}',
					de = '{$_POST["de"]}',
					seguranca = '{$_POST["seguranca"]}',
					tipo = '{$_POST["tipo"]}',
					ativo = '{$_POST["ativo"]}'");

			$id = $this->bancodedados->ultimoIdInserido();

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

				$this->conteudo .= $this->carregaArquivo("email_smtp.remover.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);

			} else {

				$this->bancodedados->query("DELETE FROM tab_email_smtp WHERE id = {$_GET["i"]}");

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");

			}

		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
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
					usuario,
					senha,
					host,
					porta,
					de,
					seguranca,
					tipo,
					ativo
				FROM
					tab_email_smtp
				WHERE
					id = $id");

			$this->dados_item = $this->bancodedados->resultadoArray($query);

		}

	}

	#################
	### Consultas ###
	#################

	function buscaOpcoesSeguranca() {

		$opcoes = array();
		$opcoes["tls"] = "TLS";
		$opcoes["ssl"] = "SSL";

		return $opcoes;

	}

	function buscaOpcoesAtivo() {

		$opcoes = array();
		$opcoes["1"] = "Sim";
		$opcoes["0"] = "N&atilde;o";

		return $opcoes;

	}

	function buscaOpcoesSituacao() {

		$opcoes = array();
		$opcoes[] = array("id" => "1", "nome" => "Sim");
		$opcoes[] = array("id" => "0", "nome" => "N&atilde;o");

		return $opcoes;

	}

	function buscaEmailSmtp($tipo = null) {

		$where_tipo = $tipo ? "AND tipo = '$tipo'" : "";

		$query = $this->bancodedados->query("
			SELECT
				id,
				usuario,
				senha,
				host,
				porta,
				de,
				seguranca,
				tipo
			FROM
				tab_email_smtp
			WHERE
				ativo = '1'
				$where_tipo
			LIMIT 1");

		return $this->bancodedados->resultadoArray($query);

	}

}

?>
