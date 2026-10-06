<?

require_once("_global/modulo.class.inc.php");

class cobrancas_remessas extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}

	}

	######################
	### Banco de Dados ###
	######################

	function buscaDadosItem($id) {

		if ($id) {

			$query = $this->bancodedados->query("
				SELECT
					tab_cobrancas_remessas.cbr_id AS id,
					tab_cobrancas_remessas.cbr_nsa AS nsa,
					tab_cobrancas_remessas.cbr_data_hora_geracao AS data_hora_geracao,
					tab_cobrancas_formatos.cof_nome AS formato,
					tab_cobrancas_formatos.cof_parametros AS parametros
				FROM
					tab_cobrancas_remessas
					INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_remessas.cof_id = tab_cobrancas_formatos.cof_id)
				WHERE
					tab_cobrancas_remessas.cbr_id = $id");
			
			$this->dados_item = $this->bancodedados->resultadoArray($query);
			$this->dados_item["parametros"] = unserialize($this->dados_item["parametros"]);

		} else {

			$this->dados_item = array();

		}

	}

	function buscaFormatos() {

		$formatos = array();

		$query = $this->bancodedados->query("
			SELECT
				cof_id AS id,
				cof_nome AS nome,
				cof_parametros AS parametros
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_remessa = cof_id
			ORDER BY
				cof_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formatos[] = $dados;
		}

		return $formatos;

	}

	##############
	### Listar ###
	##############

	function listar() {

		if (!$_GET["f"]) {
			$_GET["f"]["data_geracao_inicio"] = date("01/m/Y");
			$_GET["f"]["data_geracao_termino"] = date("t/m/Y");
		}

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"3\" width=\"100%\"><tr align=\"center\" class=\"txtBranco11\"><td bgcolor=\"#505050\"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>";
		$this->conteudo .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" width=\"100%\">";

		$cobrancas = $this->buscaCobrancasAguardandoRegistro();

		$this->conteudo .= "<tr><td bgcolor=\"#E8E8E8\" align=\"center\" class=\"txtBranco11\" style=\"height:50px\"><a href=\"?m=cobrancas_remessas&a=listar_cobrancas\" style=\"color:#FF0000\">".($cobrancas ? $cobrancas : "Nenhuma")." cobrança".($cobrancas > 0 ? "s" : "")." sem remessa.</a></td></tr>";

		if ($cobrancas && $this->permissoes[$this->dados_modulo["nome"]]["gerar"]) {
			$this->conteudo .= "<tr><td bgcolor=\"#E8E8E8\" align=\"center\" class=\"txtBranco11\"><input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"gerar\" value=\"Gerar\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=gerar'\"></td></tr>";
		}

		$this->conteudo .= "<tr><td bgcolor=\"#E8E8E8\" align=\"center\" class=\"txtBranco11\">";

		$this->conteudo .= $filtro->formulario;

		if ($_GET["f"] || $_GET["p"]) {

			$itens = $this->buscaItens($filtro, $pagina);
			$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
			$listagem = $this->geraListagem($itens["itens"]);

			$this->conteudo .= "</td></tr></table>";
			$this->conteudo .= $paginacao->conteudo;
			$this->conteudo .= $listagem->conteudo;
			$this->conteudo .= $paginacao->conteudo;

		} else {

			$this->conteudo .= "</td></tr></table>";

		}

	}

	function inicializaFiltro($valores = "") {

	    $itens = array();
	    
		// Geração Inicio
		$itens[] = array ("tipo" => "input", "nome" => "data_geracao_inicio", "descricao" => "Data de Geração &raquo; Início", "valor" => "", "where" => "(tab_cobrancas_remessas.cbr_data_hora_geracao >= '".substr($valores["data_geracao_inicio"], 6, 4)."-".substr($valores["data_geracao_inicio"], 3, 2)."-".substr($valores["data_geracao_inicio"], 0, 2)." 00:00:00')");
		
		// Geração Termino
		$itens[] = array ("tipo" => "input", "nome" => "data_geracao_termino", "descricao" => "Data de Geração &raquo; Término", "valor" => "", "where" => "(tab_cobrancas_remessas.cbr_data_hora_geracao <= '".substr($valores["data_geracao_termino"], 6, 4)."-".substr($valores["data_geracao_termino"], 3, 2)."-".substr($valores["data_geracao_termino"], 0, 2)." 23:59:59')");
		
		// Formatos
		$itens[] = array ("tipo" => "select", "nome" => "formato", "descricao" => "Formato", "valor" => $this->buscaFormatos(), "where" => "(tab_cobrancas_remessas.cof_id = [valor])");

		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);

	}

	function buscaItens($filtro, $pagina) {

		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_cobrancas_remessas.cbr_id AS id,
				tab_cobrancas_remessas.cbr_nsa AS nsa,
				DATE_FORMAT(tab_cobrancas_remessas.cbr_data_hora_geracao, '%d/%m/%Y (%H:%i)') AS data_hora_geracao,
				IF(DATE_ADD(tab_cobrancas_remessas.cbr_data_hora_geracao, INTERVAL 1 HOUR) > NOW(), 1, 0) AS recente,
				tab_cobrancas_formatos.cof_nome AS formato,
				(SELECT COUNT(coa_id) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cbr_id = tab_cobrancas_remessas.cbr_id AND tab_cobrancas_remessas_itens.cri_tipo = 'REGISTRO') AS registros,
				(SELECT COUNT(coa_id) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cbr_id = tab_cobrancas_remessas.cbr_id AND tab_cobrancas_remessas_itens.cri_tipo = 'BAIXA') AS baixas,
				(SELECT COUNT(coa_id) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cbr_id = tab_cobrancas_remessas.cbr_id AND tab_cobrancas_remessas_itens.cri_tipo = 'REGISTRO' AND tab_cobrancas_remessas_itens.cri_status = 'REGISTRADA') AS registradas,
				(SELECT COUNT(coa_id) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cbr_id = tab_cobrancas_remessas.cbr_id AND tab_cobrancas_remessas_itens.cri_tipo = 'BAIXA' AND tab_cobrancas_remessas_itens.cri_status = 'BAIXADA') AS baixadas
			FROM
				tab_cobrancas_remessas
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_remessas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				{$filtro->parametros_sql}
			GROUP BY
				id
			ORDER BY
				tab_cobrancas_remessas.cbr_data_hora_geracao DESC,
				tab_cobrancas_formatos.cof_nome,
				tab_cobrancas_remessas.cbr_nsa DESC
			LIMIT
				{$limit_inicio},
				{$limit_fim}");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["acoes"] = array();

			if ($this->permissoes[$this->dados_modulo["nome"]]["listar_cobrancas"]) {
				$dados["link"] = "javascript:window.location='?m={$this->dados_modulo["nome"]}&a=listar_cobrancas&i={$dados["id"]}';";
			}
			if ($this->permissoes[$this->dados_modulo["nome"]]["exportar"]) {
				$dados["acoes"][] = "<a class=\"txtAzulEscuro11\" href=\"?m={$this->dados_modulo["nome"]}&a=exportar&i={$dados["id"]}\">[Exportar]</a>";
			}
			$dados["acoes"] = implode("&nbsp;", $dados["acoes"]);
			$dados["registros"] = number_format($dados["registradas"], null, null, ".")." / ".number_format($dados["registros"], null, null, ".");
			$dados["baixas"] = number_format($dados["baixadas"], null, null, ".")." / ".number_format($dados["baixas"], null, null, ".");
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			if ($dados["recente"]) {
				$dados["cor_linha"] = "#FFC0C0";
			}

			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagem($itens) {

		$cabecalho = array ("contador" => "#", "formato" => "Formato", "nsa" => "Sequencia", "data_hora_geracao" => "Geração", "registros" => "Registros", "baixas" => "Baixas", "acoes" => "");

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, null, array(20, 0, 70, 110, 80, 80, 50), null, array("right", "left", "right", "center", "right", "right"));

	}

	##############
	### Listar ###
	##############

	function listar_cobrancas() {

		$this->buscaDadosItem($_GET["i"]);

		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= "<script type=\"text/javascript\" src=\"rds.js.php?m=cobrancas\"></script>";
		$this->conteudo .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"3\" width=\"100%\"><tr align=\"center\" class=\"txtBranco11\"><td bgcolor=\"#505050\"><strong>&nbsp;Cobranças&nbsp;</strong></td></tr></table>";
		$this->conteudo .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" width=\"100%\">";

		$cobrancas = $this->buscaCobrancasAguardandoRegistro();

		$this->conteudo .= "<tr><td bgcolor=\"#E8E8E8\" align=\"center\" class=\"txtBranco11\" style=\"height:50px\"><a href=\"?m=cobrancas_remessas&a=listar_cobrancas\" style=\"color:#FF0000\">".($cobrancas ? $cobrancas : "Nenhuma")." cobrança".($cobrancas > 0 ? "s" : "")." sem remessa.</a></td></tr>";

		if ($cobrancas && $this->permissoes[$this->dados_modulo["nome"]]["gerar"]) {
			$this->conteudo .= "<tr><td bgcolor=\"#E8E8E8\" align=\"center\" class=\"txtBranco11\"><input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"gerar\" value=\"Gerar\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=gerar'\"></td></tr>";
		}

		$this->conteudo .= "<tr><td bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">";

		if ($this->dados_item) {
			$this->conteudo .= "<b>&raquo; Remessa: </b> {$this->dados_item["formato"]} &raquo; {$this->dados_item["nsa"]}";
		} else {
			$this->conteudo .= "<b>&raquo; Cobranças sem remessa</b>";
		}

		$this->conteudo .= "</td></tr></table>";

		$itens = $this->buscaCobrancasRemessa($_GET["i"], $pagina);
		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $itens["linhas"], $pagina);

		$this->conteudo .= $paginacao->conteudo;
		$this->conteudo .= $this->geraListagemCobrancaSemRemessa($itens["itens"]);
		$this->conteudo .= $paginacao->conteudo;

	}

	function buscaCobrancasRemessa($remessa_id, $pagina) {

		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.cli_id AS cliente_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_parametros AS parametros,
				DATE_FORMAT(tab_cobrancas.coa_data_geracao, '%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas.coa_valor_original AS valor,
				tab_cobrancas_formatos.cof_nome AS formato,".($remessa_id ? "
				(SELECT tab_cobrancas_remessas_itens.cri_tipo FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cbr_id = $remessa_id AND tab_cobrancas_remessas_itens.coa_id = tab_cobrancas.coa_id) AS tipo,
				(SELECT tab_cobrancas_remessas_itens.cri_status FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cbr_id = $remessa_id AND tab_cobrancas_remessas_itens.coa_id = tab_cobrancas.coa_id) AS status" : "
				IF(tab_cobrancas.cot_id <> 1 OR tab_cobrancas.coa_data_geracao <= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), INTERVAL 15 DAY), 'BAIXA', 'REGISTRO') AS tipo
				")."
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE".($remessa_id ? "
				tab_cobrancas.coa_id IN (
					SELECT
						coa_id
					FROM
						tab_cobrancas_remessas_itens
					WHERE
						cbr_id = $remessa_id
				)" : "
				tab_cobrancas.coa_aguardando_registro = 1")."
			GROUP BY
				tab_cobrancas.coa_nosso_numero
			ORDER BY
				tab_cobrancas.coa_nosso_numero
			LIMIT
				{$limit_inicio},
				{$limit_fim}");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($this->permissoes["cobrancas"]["ver_dg"]) {
				$dados["link"] = "javascript:verCobranca({$dados["id"]})";
			}
			if ($this->permissoes["clientes"]["ver_df"]) {
				$dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			$dados["nosso_numero"] = $dados["parametros"]["boleto"]["nosso_numero"];
			$dados["valor"] = $this->formataDinheiro($dados["valor"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagemCobrancaSemRemessa($itens) {

		$cabecalho = array ("contador" => "#", "formato" => "Formato", "cliente_id" => "ID", "codigo" => "Código", "nosso_numero" => "Nosso Número", "data_geracao" => "Geração", "data_vencimento" => "Venc.", "valor" => "Valor", "tipo" => "Tipo", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array("valor" => "formataDinheiro"),  array(20, 0, 40, 80, 60, 60, 60, 70, 50, 70), null, array("right", "left", "center", "center", "center", "center", "center", "right", "left", "left"));

		return $listagem->conteudo;

	}

	#############
	### Gerar ###
	#############

	function gerar() {
	
		set_time_limit(0);
		ignore_user_abort(1);
			
		$query = $this->bancodedados->query("
			SELECT
				cof_remessa AS formato_remessa_id,
				GROUP_CONCAT(cof_id SEPARATOR ', ') AS formatos_cobranca
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_remessa IS NOT NULL AND
				cof_id = 5 AND
				-- cfs_id = 1 AND
				(
					SELECT
						COUNT(*)
					FROM
						tab_cobrancas
					WHERE
						tab_cobrancas.coa_aguardando_registro = 1 AND
						tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id
				) > 0
			GROUP BY
				formato_remessa_id");

		while (($formato = $this->bancodedados->resultadoArray($query)) != false) {
			do {
				$this->geraRemessa($formato["formato_remessa_id"], $formato["formatos_cobranca"]);
			} while ($this->buscaQuantidadeCobrancasSemRemessa($formato["formatos_cobranca"]) > 0);
		}

		header("Location: ./?m={$this->dados_modulo["nome"]}");

	}

	function buscaQuantidadeCobrancasSemRemessa($formatos_cobranca) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(DISTINCT tab_cobrancas.coa_nosso_numero)
			FROM
				tab_cobrancas
			WHERE
				coa_aguardando_registro = 1 AND
				cof_id IN ($formatos_cobranca)");

		return (int) $this->bancodedados->resultado($query);

	}

	function geraRemessa($formato_id, $formatos_cobranca) {

		$this->bancodedados->query("BEGIN");

		$nsa = $this->bancodedados->resultado($this->bancodedados->query("
			SELECT 
				MAX(cbr_nsa) 
			FROM 
				tab_cobrancas_remessas 
			WHERE 
				cof_id = $formato_id")) + 1;

		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_remessas
			SET
				cbr_data_hora_geracao = NOW(),
				cbr_nsa = $nsa,
				cof_id = $formato_id");

		$remessa_id = $this->bancodedados->ultimoIdInserido();

		$this->bancodedados->query("COMMIT");

		$query = $this->bancodedados->query("
			SELECT
				coa_id AS cobranca_id,
				coa_nosso_numero AS nosso_numero,
				IF(cot_id <> 1 OR coa_data_geracao <= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), INTERVAL 15 DAY), 'BAIXA', 'REGISTRO') AS tipo
			FROM
				tab_cobrancas
			WHERE
				coa_aguardando_registro = 1 AND
				cof_id IN ($formatos_cobranca)
			GROUP BY
				tab_cobrancas.coa_nosso_numero
			LIMIT
				3000");

		while (($cobranca = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("INSERT INTO tab_cobrancas_remessas_itens (cbr_id, cof_id, coa_id, cri_nosso_numero, cri_tipo) VALUES ($remessa_id, $formato_id, {$cobranca["cobranca_id"]}, '{$cobranca["nosso_numero"]}', '{$cobranca["tipo"]}')");
			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 0 WHERE coa_id = {$cobranca["cobranca_id"]}");
		}

	}

	function buscaCobrancasAguardandoRegistro() {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(DISTINCT tab_cobrancas.coa_nosso_numero)
			FROM
				tab_cobrancas
			WHERE
				coa_aguardando_registro = 1");

		return $this->bancodedados->resultado($query);

	}

	################
	### Exportar ###
	################

	function exportar() {

		$this->buscaDadosItem($_GET["i"]);

		$caminho = dirname(__FILE__)."/cobrancas_remessas.{$this->dados_item["parametros"]["remessa"]}.class.inc.php";

		if (file_exists($caminho)) {

			require_once $caminho;

			$classe = "cobrancas_remessas_{$this->dados_item["parametros"]["remessa"]}";

			$remessa = new $classe($this->bancodedados, $this->dados_item);

			$content = $remessa->gerar();
			
			$matches = array();

			preg_match("/\\((.*)\\)( \\[(.*)\\])?/", $this->dados_item["formato"], $matches);

			$nsa = sprintf("%05.5s", $this->dados_item["nsa"]);

			$filename = urlencode("Remessa.{$matches[1]}.{$this->limpaNome($matches[3])}.{$nsa}.txt");

			header("Content-Type: application/text;");
			header("Content-Length: ".strlen($content));
			header("Content-Disposition: attachment; filename={$filename};");

			print $content;
			die();

		} else {

			header("Location: ./?m={$this->dados_modulo["nome"]}");
			die();

		}

	}
   
	function limpaNome($nome) {
		
		$nome = trim(preg_replace("/[\r\n\t ]+/", " ", mb_strtolower($nome)));
		$nome = preg_replace("/[ç]/", "c", $nome);
		$nome = preg_replace("/[ñ]/", "n", $nome);
		$nome = preg_replace("/[áàâãäª]/", "a", $nome);
		$nome = preg_replace("/[éèêë]/", "e", $nome);
		$nome = preg_replace("/[íìîï]/", "i", $nome);
		$nome = preg_replace("/[óòôõö°º]/", "o", $nome);
		$nome = preg_replace("/[úùûü]/", "u", $nome);
		$nome = preg_replace("/[^a-zA-Z0-9,. \\-\\/]/", "", $nome);
		
		return strtoupper($nome);
		
	}	

}

?>
