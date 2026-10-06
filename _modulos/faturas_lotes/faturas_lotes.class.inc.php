<?
/**
 * 
    inserir item ponto adicional na tabela tab_classes_fiscais_tipos;
    inserir item na tabela tab_classes_fiscais para o plano 736;
 */

require_once ("_global/modulo.class.inc.php");

class faturas_lotes extends modulo {
	
	var $webservice = "http://financeiro.oletelecom.com.br/ott_webservices/start_invoice_cycle/start_invoice_cycle.asmx?WSDL";
	
	var $fatura_serie = 2;
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
	
	function buscaDadosItem($id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_faturas_lotes.fal_id AS id,
				tab_faturas_lotes.fal_ciclo AS ciclo,
				CONCAT(DATE_FORMAT(tab_faturas_lotes.fal_data_ciclo, '%d/%m/%Y'), ' - ', LPAD(tab_faturas_lotes.fal_ciclo_sequencia, 2, '0')) AS ciclo_formatado,
				DATE_FORMAT(tab_faturas_lotes.fal_data_geracao, '%d/%m/%Y (%H:%i)') AS data_geracao,
				DATE_FORMAT(tab_faturas_lotes.fal_data_processamento, '%d/%m/%Y (%H:%i)') AS data_processamento,
				DATE_FORMAT(tab_faturas_lotes.fal_data_aprovacao, '%d/%m/%Y (%H:%i)') AS data_aprovacao,
				tab_faturas_tipos.fap_id AS tipo_id,
				tab_faturas_tipos.fap_nome AS tipo_nome,
				tab_faturas_lotes_status.fls_id AS status_id,
				tab_faturas_lotes_status.fls_nome AS status_nome,
				(SELECT COUNT(tab_faturas_lotes_clientes.cli_id) FROM tab_faturas_lotes_clientes WHERE tab_faturas_lotes.fal_id = tab_faturas_lotes_clientes.fal_id) AS clientes,
				(SELECT COUNT(tab_faturas_lotes_cobrancas.fco_id) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas_lotes.fal_id = tab_faturas_lotes_cobrancas.fal_id) AS faturas,
				(SELECT SUM(tab_faturas_itens.fai_valor_total) FROM tab_faturas INNER JOIN tab_faturas_itens ON (tab_faturas.fat_id = tab_faturas_itens.fat_id) WHERE tab_faturas_lotes.fal_id = tab_faturas.fal_id) AS total,
				(SELECT SUM(tab_faturas_itens.fai_valor_icms) FROM tab_faturas INNER JOIN tab_faturas_itens ON (tab_faturas.fat_id = tab_faturas_itens.fat_id) WHERE tab_faturas_lotes.fal_id = tab_faturas.fal_id) AS total_icms
			FROM
				tab_faturas_lotes
				INNER JOIN tab_faturas_tipos ON (tab_faturas_lotes.fap_id = tab_faturas_tipos.fap_id)
				INNER JOIN tab_faturas_lotes_status ON (tab_faturas_lotes.fls_id = tab_faturas_lotes_status.fls_id)
			WHERE
				tab_faturas_lotes.fal_id = {$id}");
				
		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
			$this->dados_item["total"] = $this->formataDinheiro($this->dados_item["total"], "R$", true);
			$this->dados_item["total_icms"] = $this->formataDinheiro($this->dados_item["total_icms"], "R$", true);
		}
		
	}
	
	function buscaStatus() {
		
		$query = $this->bancodedados->query("
			SELECT 
				fls_id AS id, 
				fls_nome AS nome
			FROM 
				tab_faturas_lotes_status 
			ORDER BY 
				fls_nome");
		
		return $this->bancodedados->resultadoArrayTotal($query);
		
	}
		
	function buscaTipos() {
		
		$query = $this->bancodedados->query("
			SELECT 
				fap_id AS id, 
				fap_nome AS nome
			FROM 
				tab_faturas_tipos
			ORDER BY 
				fap_nome");
		
		return $this->bancodedados->resultadoArrayTotal($query);
		
	}
		
	function buscaListaTipos() {
		
		$tipos = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				fap_id AS id, 
				fap_nome AS nome
			FROM 
				tab_faturas_tipos
			ORDER BY 
				fap_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}

		return $tipos;
		
	}

	function buscaLotePorCiclo($ciclo, $tipo_id) {
		
		$query = $this->bancodedados->query("
			SELECT 
				fal_id 
			FROM 
				tab_faturas_lotes 
			WHERE 
				fal_ciclo = '{$ciclo}' AND
				fap_id = {$tipo_id}");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	##############
	### Listar ###
	##############

	function listar() {
		
		if (!$_GET["f"]) {
			$_GET["f"]["data_ciclo_inicio"] = date("01/m/Y");
			$_GET["f"]["data_ciclo_termino"] = date("t/m/Y");
		}
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$botoes = array();
		
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
			$botoes[] = '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
		}
		
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= implode(" ", $botoes);
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
		
		$itens[] = array("tipo" => "input", "nome" => "ciclo", "descricao" => "Ciclo", "valor" => "", "where" => "tab_faturas_lotes.fal_ciclo = '%[valor]%')");
		$itens[] = array("tipo" => "input", "nome" => "data_geracao_inicio", "descricao" => "Data Geração &raquo; Início", "valor" => "", "where" => "(tab_faturas_lotes.fal_data_geracao >= '" . (substr($valores["data_geracao_inicio"], 6, 4) . "-" . substr($valores["data_geracao_inicio"], 3, 2) . "-" . substr($valores["data_geracao_inicio"], 0, 2)) . "')");
		$itens[] = array("tipo" => "input", "nome" => "data_geracao_termino", "descricao" => "Data Geração &raquo; Término", "valor" => "", "where" => "(tab_faturas_lotes.fal_data_geracao <= '" . (substr($valores["data_geracao_termino"], 6, 4) . "-" . substr($valores["data_geracao_termino"], 3, 2) . "-" . substr($valores["data_geracao_termino"], 0, 2)) . "')");
		$itens[] = array("tipo" => "input", "nome" => "data_ciclo_inicio", "descricao" => "Data do Ciclo &raquo; Início", "valor" => "", "where" => "(tab_faturas_lotes.fal_data_ciclo >= '" . (substr($valores["data_ciclo_inicio"], 6, 4) . "-" . substr($valores["data_ciclo_inicio"], 3, 2) . "-" . substr($valores["data_ciclo_inicio"], 0, 2)) . "')");
		$itens[] = array("tipo" => "input", "nome" => "data_ciclo_termino", "descricao" => "Data do Ciclo &raquo; Término", "valor" => "", "where" => "(tab_faturas_lotes.fal_data_ciclo <= '" . (substr($valores["data_ciclo_termino"], 6, 4) . "-" . substr($valores["data_ciclo_termino"], 3, 2) . "-" . substr($valores["data_ciclo_termino"], 0, 2)) . "')");
		$itens[] = array("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $this->buscaTipos(), "where" => "(tab_faturas_lotes.fap_id = [valor])");
		$itens[] = array("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $this->buscaStatus(), "where" => "(tab_faturas_lotes.fls_id = [valor])");
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}
	
	function buscaItens($filtro, $pagina) {
		
		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_faturas_lotes.fal_id AS id,
				CONCAT(DATE_FORMAT(tab_faturas_lotes.fal_data_ciclo, '%d/%m/%Y'), ' - ', LPAD(tab_faturas_lotes.fal_ciclo_sequencia, 2, '0')) AS ciclo,
				DATE_FORMAT(tab_faturas_lotes.fal_data_geracao, '%d/%m/%Y') AS data_geracao,
				tab_faturas_tipos.fap_nome AS tipo,
				tab_faturas_lotes_status.fls_nome AS status,
				(SELECT COUNT(tab_faturas_lotes_clientes.cli_id) FROM tab_faturas_lotes_clientes WHERE tab_faturas_lotes.fal_id = tab_faturas_lotes_clientes.fal_id) AS clientes,
				(SELECT COUNT(tab_faturas_lotes_cobrancas.fco_id) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas_lotes.fal_id = tab_faturas_lotes_cobrancas.fal_id) AS faturas
			FROM
				tab_faturas_lotes
				INNER JOIN tab_faturas_tipos ON (tab_faturas_lotes.fap_id = tab_faturas_tipos.fap_id)
				INNER JOIN tab_faturas_lotes_status ON (tab_faturas_lotes.fls_id = tab_faturas_lotes_status.fls_id)
			WHERE
				{$filtro->parametros_sql}
			GROUP BY
				tab_faturas_lotes.fal_id
			ORDER BY
				tab_faturas_lotes.fal_ciclo DESC
			LIMIT
				{$limit_inicio},
				{$limit_fim}");
				
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["faturas_lotes"]["ver"]) {
				$dados["link"] = "?m=faturas_lotes&a=ver&i={$dados["id"]}";
			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
			
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}
	
	function geraListagem($itens) {
		
		$cabecalho = array("contador" => "#", "tipo" => "Tipo", "ciclo" => "Ciclo", "data_geracao" => "Geração", "clientes" => "Clientes", "faturas" => "Faturas", "status" => "Status");
		
		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, null, null, null, array("right", "center", "center", "center", "right", "right", "center"));

	}
	
	function geraFiltrosListagem($dados = null, $editavel = true) {
		
		if ($dados) {
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarFiltro(this, event, " . ($i + 1) . ")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerFiltro(\'' . ($i + 1) . '\')">';
				}
				$dados_tipo = $this->buscaDadosLotesFiltros($dados[$i]["campo"]);
				$dados[$i]["campo"] = $dados_tipo["nome"];
				$dados[$i]["comparacao"] = $dados_tipo["parametros"]["comparacao"][$dados[$i]["comparacao"]];
				if (is_array($dados_tipo["parametros"]["valor_conteudo"])) {
					$dados[$i]["valor"] = $dados_tipo["parametros"]["valor_conteudo"][$dados[$i]["valor"]];
				} else if ($dados_tipo["parametros"]["valor_conteudo"]) {
					$dados_valor = $this->buscaFiltroTipoConteudo($dados_tipo["parametros"]["valor_conteudo"]);
					$dados[$i]["valor"] = $dados_valor[$dados[$i]["valor"]];
				}
			}
		} else {
			$dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
		}
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaFiltrosAdicionar()), $dados);
			$cabecalho = array("campo" => "Campo", "comparacao" => "Comparação", "valor" => "Valor", "botoes" => "");
		} else {
			$cabecalho = array("campo" => "Campo", "comparacao" => "Comparação", "valor" => "Valor");
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
		
		return $listagem->conteudo;
		
	}
	
	function buscaFiltrosAdicionar() {
		
		$adicionar = array();
		
		$adicionar["campo"] = $this->geraSelect($this->buscaFaturasFiltros(), null, true, "campo", "260px", "mudaFiltroCampo(this.value)");
		$adicionar["comparacao"] = '<div id="div_comparacao"><select name="comparacao" id="comparacao" style="width:120px" class="inputBrancoAzulEscuro11" disabled></select></div>';
		$adicionar["valor"] = '<div id="div_valor"><select name="valor" id="valor" style="width:300px" class="inputBrancoAzulEscuro11" disabled></select></div>';
		$adicionar["botoes"] = '<input type="button" name="adicionar_filtro" id="adicionar_filtro" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarFiltro()">';
		
		return $adicionar;
		
	}
	
	function buscaFiltroTipoConteudo($query) {
		
		$lista = array();
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}
		
		return $lista;
		
	}
	
	function buscaDadosLotesFiltros($filtro_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				faf_nome AS nome,
				faf_parametros AS parametros
			FROM
				tab_faturas_filtros
			WHERE
				faf_id = {$filtro_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
		
	}
	
	function buscaFaturasFiltros() {
		
		$lista = array();
		
		$query = $this->bancodedados->query("
			SELECT
				faf_id AS id,
				faf_nome AS nome
			FROM
				tab_faturas_filtros
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}
		
		return $lista;
		
	}
	
	###################
	### Comentários ###
	###################

	function registraComentario($lote_id, $conteudo, $responsavel_id = "NULL") {
		
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_faturas_lotes_comentarios 
			SET
				flc_datahora = NOW(), 
				fal_id = {$lote_id}, 
				flc_conteudo = '{$conteudo}', 
				usu_id = {$responsavel_id}");
		
	}
	
	function geraComentariosListagem($comentarios) {
		
		$cabecalho = array("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
		
		return $listagem->conteudo;
		
	}
	
	function buscaComentarios($id) {
		
		$comentarios = array();
			  
		$query = $this->bancodedados->query("
			SELECT
			  IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
			  DATE_FORMAT(tab_faturas_lotes_comentarios.flc_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
			  tab_faturas_lotes_comentarios.flc_conteudo AS conteudo
			FROM
			  tab_faturas_lotes_comentarios
			  LEFT OUTER JOIN tab_usuarios ON (tab_faturas_lotes_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
			  tab_faturas_lotes_comentarios.fal_id = {$id}
			ORDER BY
			  tab_faturas_lotes_comentarios.flc_datahora ASC,
			  tab_faturas_lotes_comentarios.flc_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
			$dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
			$dados["detalhamento"] .= "{$dados["conteudo"]}<br>";
			
			$comentarios[] = $dados;
			
		}
		
		return $comentarios;
		
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
			
			$this->conteudo .= $this->carregaArquivo("faturas_lotes.adicionar.inc.php");
			
			$this->conteudo = str_replace('[data_ciclo]', date("d/m/Y"), $this->conteudo);
			$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaListaTipos(), null, true, "tipo"), $this->conteudo);
			$this->conteudo = str_replace('[filtros]', $this->geraFiltrosListagem(), $this->conteudo);
			$this->conteudo = str_replace('[botoes]', implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
			
		} else {
			
			set_time_limit(0);
			
			$data_ciclo = array();
			
			if (preg_match("/(?P<dia>[0-9]{2})\\/(?P<mes>[0-9]{2})\\/(?P<ano>[0-9]{4})/", $_POST["data_ciclo"], $data_ciclo)) {
				$data_ciclo = "{$data_ciclo["ano"]}-{$data_ciclo["mes"]}-{$data_ciclo["dia"]}";
			} else {
				$data_ciclo = date("Y-m-d");
			}
			
			$sequencia = $this->buscaSequenciaCiclo($data_ciclo, $_POST["tipo"]);
			$ciclo = date("Ymd", strtotime($data_ciclo)) . sprintf("%03s", $sequencia);
			
			$this->bancodedados->query("
				INSERT INTO 
					tab_faturas_lotes 
				SET
					fal_ciclo = '{$ciclo}',
					fal_ciclo_sequencia = {$sequencia}, 
					fal_data_ciclo = '{$data_ciclo}', 
					fal_data_geracao = NOW(), 
					fap_id = {$_POST["tipo"]},
					fls_id = 1");
			
			$id = $this->bancodedados->ultimoIdInserido();
			
			$filtros = unserialize(stripslashes($_POST["filtros_lista"]));
			
			foreach ($filtros as $filtro) {
				
				$this->bancodedados->query("
					INSERT INTO 
						tab_faturas_lotes_filtros 
					SET
						flf_comparacao = '{$filtro["comparacao"]}', 
						flf_valor = '{$filtro["valor"]}', 
						fal_id = {$id}, 
						faf_id = {$filtro["campo"]}");
						
			}
			
			// Gera Faturas
			$this->geraFaturas($id, $_POST["comentario"]);
			
			// Registra Atividade
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			
			// Redireciona
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
			
		}
		
	}
	
	function buscaSequenciaCiclo($data, $tipo_id) {
			
		$query = $this->bancodedados->query("
			SELECT
				MAX(fal_ciclo_sequencia)
			FROM
				tab_faturas_lotes
			WHERE
				fap_id = {$tipo_id} AND
				fal_data_ciclo LIKE '{$data}'");
				
		return ((int) $this->bancodedados->resultado($query)) + 1;
		
	}
	
	function geraFaturas($lote_id, $comentario_usuario = null) {
	    
	    $filtros_agrupados = array();
	    $filtros_tratados = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				fal_data_ciclo AS data_ciclo,
				fap_id AS tipo_id
			FROM
				tab_faturas_lotes
			WHERE
				fal_id = {$lote_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$data_ciclo = $dados["data_ciclo"];
			$tipo_id = $dados["tipo_id"];
		}				
			  
		$query = $this->bancodedados->query("
			SELECT
				tab_faturas_lotes_filtros.faf_id AS filtro_id,
				tab_faturas_lotes_filtros.flf_comparacao AS comparacao,
				tab_faturas_lotes_filtros.flf_valor AS valor,
				tab_faturas_filtros.faf_parametros AS parametros
			FROM
				tab_faturas_lotes_filtros
				INNER JOIN tab_faturas_filtros ON (tab_faturas_lotes_filtros.faf_id = tab_faturas_filtros.faf_id)
			WHERE
			  tab_faturas_lotes_filtros.fal_id = {$lote_id}");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			$filtros_agrupados[$dados["filtro_id"]][$dados["comparacao"]][] = $dados;
			
		}
		
		if (count($filtros_agrupados) == 0) {
			return;
		}
		
		foreach ($filtros_agrupados as $comparacoes) {
			foreach ($comparacoes as $comparacao => $filtro) {
				$valores = array();
				for ($i = 0; $i < count($filtro); $i++) {
				    $matches = array();
					if (preg_match('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $filtro[$i]["valor"], $matches)) {
						$filtro[$i]["valor"] = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
					}
					$valores[] = $filtro[$i]["valor"];
				}
				if ($comparacao == "=" && count($valores) > 1) {
					$filtros_tratados[] = array("parametros" => $filtro[0]["parametros"], "comparacao" => "IN", "valor" => "('".implode("', '", $valores)."')");
				} else if ($comparacao == "<>" && count($valores) > 1) {
					$filtros_tratados[] = array("parametros" => $filtro[0]["parametros"], "comparacao" => "NOT IN", "valor" => "('".implode("', '", $valores)."')");
				} else if (($comparacao == ">" || $comparacao == ">=") && count($valores) > 1) {
					$filtros_tratados[] = array("parametros" => $filtro[0]["parametros"], "comparacao" => $comparacao, "valor" => "'".max($valores)."'");
				} else if (($comparacao == "<" || $comparacao == "<=") && count($valores) > 1) {
					$filtros_tratados[] = array("parametros" => $filtro[0]["parametros"], "comparacao" => $comparacao, "valor" => "'".min($valores)."'");
				} else {
					foreach ($valores as $valor) {
						$filtros_tratados[] = array("parametros" => $filtro[0]["parametros"], "comparacao" => $comparacao, "valor" => "'$valor'");
					}
				}
			}
		}
		
		$joins = array();
		$where = array();
		$having = array();
		
		foreach ($filtros_tratados as $filtro) {
			if (array_key_exists("juncao", $filtro["parametros"]) && $filtro["parametros"]["juncao"]) {
				foreach ($filtro["parametros"]["juncao"] as $dados_juncao) {
					$joins[] = $dados_juncao;
				}
			}
			if (array_key_exists("where", $filtro["parametros"]["filtro"]) && $filtro["parametros"]["filtro"]["where"]) {
				foreach ($filtro["parametros"]["filtro"]["where"] as $dados_filtro) {
					$where[] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_filtro);
				}
			}
			if (array_key_exists("having", $filtro["parametros"]["filtro"]) && $filtro["parametros"]["filtro"]["having"]) {
				foreach ($filtro["parametros"]["filtro"]["having"] as $dados_filtro) {
					$having[] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_filtro);
				}
			}
		}
		
		$where[] = "tab_cobrancas.fco_id IS NULL";
		
		$mes_ciclo = date("Ym01", strtotime($data_ciclo));
		$mes_ciclo_formatado = date("Y-m-01", strtotime($data_ciclo));
		$data_ciclo = date("Ymd", strtotime($data_ciclo));
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.cli_id AS cliente_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_desconto AS valor_desconto,
				tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo,
				tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_juro AS valor_juro,
				tab_cobrancas.coa_data_geracao AS data_geracao,
				tab_cobrancas.coa_data_processamento AS data_processamento,
				tab_cobrancas.coa_data_vencimento AS data_vencimento,
				tab_cobrancas.cot_id AS status_id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.cop_id AS tipo_id,
				tab_cobrancas.cof_id AS formato_id
			FROM
				tab_cobrancas".(count($joins) > 0 ? "
				".implode("\n        ", $joins) : "")."
			WHERE
				".implode(" AND\n        ", $where)."
			GROUP BY
				tab_cobrancas.coa_id".(count($having) > 0 ? "
			HAVING
				".implode("\n          ", $having) : "")."
			ORDER BY
				tab_cobrancas.cli_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->geraCobrancaFatura($dados, $lote_id, $tipo_id, $data_ciclo, $mes_ciclo, $mes_ciclo_formatado);
		}
		
		// Registra Comentário
		$comentario = "<b>Ação:</b> Lote gerado";
		if (array_key_exists("comentario", $_POST) && trim($_POST["comentario"])) {
			$comentario .= "<br><b>Comentário:</b> ".preg_replace("/[\r\n\t ]+/", " ", trim($comentario_usuario));
		}
		
		$this->registraComentario($lote_id, $comentario, $_SESSION && array_key_exists("usuario", $_SESSION) && $_SESSION["usuario"] ? $_SESSION["usuario"] : null);

	}
	
	function geraCobrancaFatura($dados, $lote_id, $tipo_id, $data_ciclo, $mes_ciclo, $mes_ciclo_formatado) {
		
		$fatura_itens = $this->buscaFaturaItens($dados, $tipo_id, null, true);
		$data_geracao = date("Ymd", strtotime($dados["data_geracao"]));
		
		if ($data_geracao > $data_ciclo || $data_geracao < $mes_ciclo) {
			$dados["data_geracao"] = $mes_ciclo_formatado;
		}
		
		if ($dados["status_id"] == 1) {
			if (strtotime($dados["data_vencimento"]) < strtotime($dados["data_geracao"])) {
				$dados["data_vencimento"] = $dados["data_geracao"];
			}
		} else {
			if (strtotime($dados["data_vencimento"]) < strtotime($dados["data_geracao"])) {
				$dados["data_vencimento"] = $dados["data_processamento"];
			}
		}
		
		$this->geraRegistroFatura($dados, $lote_id, $tipo_id, $fatura_itens, 1);
		
	}
	
	public function geraRegistroFatura($dados, $lote_id, $tipo_id, $fatura_itens, $empresa_id = null) {
		
		if ($fatura_itens["valor_scm"] || $fatura_itens["valor_sva"]) {
						
			if (count($fatura_itens["itens"]) > 0) {
				
				$fatura_lote_cliente_id = $this->bancodedados->resultado($this->bancodedados->query("SELECT fcl_id FROM tab_faturas_lotes_clientes WHERE fal_id = $lote_id AND cli_id = {$dados["cliente_id"]}"));
				
				if (!$fatura_lote_cliente_id) {
					
					$dados_cliente = $this->buscaDadosCliente($dados["cliente_id"]);
					
					if ($dados_cliente["cpf_cnpj"]) {
						
						$dados_cliente["nome"] = addslashes(strlen($dados_cliente["nome"]) > 80 ? substr($dados_cliente["nome"], 0, 80) : $dados_cliente["nome"]);
						$dados_cliente["nome_fantasia"] = addslashes(strlen($dados_cliente["nome_fantasia"]) > 50 ? substr($dados_cliente["nome_fantasia"], 0, 50) : $dados_cliente["nome_fantasia"]);
						$dados_cliente["endereco_logradouro"] = addslashes(strlen($dados_cliente["endereco_logradouro"]) > 100 ? substr($dados_cliente["endereco_logradouro"], 0, 100) : $dados_cliente["endereco_logradouro"]);
						$dados_cliente["endereco_complemento"] = addslashes(strlen($dados_cliente["endereco_complemento"]) > 20 ? substr($dados_cliente["endereco_complemento"], 0, 20) : $dados_cliente["endereco_complemento"]);
						$dados_cliente["endereco_bairro"] = addslashes(strlen($dados_cliente["endereco_bairro"]) > 30 ? substr($dados_cliente["endereco_bairro"], 0, 30) : $dados_cliente["endereco_bairro"]);
						$dados_cliente["endereco_numero"] = $dados_cliente["endereco_numero"] ? $dados_cliente["endereco_numero"] : "NULL";
						$dados_cliente["endereco_bairro"] = addslashes($dados_cliente["endereco_bairro"]);
						
						$this->bancodedados->query("
							INSERT INTO 
								tab_faturas_lotes_clientes
							SET
								fcl_nome = '{$dados_cliente["nome"]}',
								fcl_nome_fantasia = '{$dados_cliente["nome_fantasia"]}',
								fcl_tipo_pessoa = '{$dados_cliente["tipo_pessoa"]}',
								fcl_cpf_cnpj = '{$dados_cliente["cpf_cnpj"]}',
								fcl_inscricao_municipal = '{$dados_cliente["inscricao_municipal"]}',
								fcl_inscricao_estadual = '{$dados_cliente["inscricao_estadual"]}',
								fcl_endereco_logradouro = '{$dados_cliente["endereco_logradouro"]}',
								fcl_endereco_numero = {$dados_cliente["endereco_numero"]},
								fcl_endereco_complemento = '{$dados_cliente["endereco_complemento"]}',
								fcl_endereco_bairro = '{$dados_cliente["endereco_bairro"]}',
								fcl_endereco_cep = {$dados_cliente["endereco_cep"]},
								fal_id = {$lote_id},
								cli_id = {$dados["cliente_id"]}");
						
						$fatura_lote_cliente_id = $this->bancodedados->ultimoIdInserido();
						
					} else {
						return;
					}
					
				}
				
			}
			
			$faturas = array();
			$faturas_valor = array();
			$faturas_existentes = array();
			
			$query = $this->bancodedados->query("
				SELECT
					emp_id AS empresa_id
				FROM
					tab_faturas
				WHERE
					fal_id = {$lote_id} AND
					coa_id = {$dados["id"]}");
			
			while (($dados_fatura = $this->bancodedados->resultadoArray($query)) != false) {
				$faturas_existentes[] = $dados_fatura["empresa_id"]; 
			}

			// Insere itens
			
			foreach ($fatura_itens["itens"] as $item) {
				
				$empresa_atual_id = $empresa_id ? $empresa_id : $item["empresa_id"];
				
				if (!in_array($empresa_atual_id, $faturas_existentes)) {
					
					if (!array_key_exists($empresa_atual_id, $faturas)) {
						
						$numero = $tipo_id == 1 ? $dados["id"] : "NULL";
						
						$this->bancodedados->query("
							INSERT INTO 
								tab_faturas 
							SET
								fat_serie = {$this->fatura_serie}, 
								fat_codigo = '{$dados["codigo"]}', 
								fat_numero = {$numero}, 
								fat_data_emissao = '{$dados["data_geracao"]}', 
								fat_data_vencimento = '{$dados["data_vencimento"]}', 
								fat_natureza = '{$fatura_itens["natureza"]}', 
								fal_id = {$lote_id}, 
								fcl_id = {$fatura_lote_cliente_id}, 
								cli_id = {$dados["cliente_id"]}, 
								coa_id = {$dados["id"]}, 
								emp_id = {$empresa_atual_id}, 
								fap_id = {$tipo_id}");
						
						$faturas[$empresa_atual_id] = $this->bancodedados->ultimoIdInserido();
						$faturas_valor[$empresa_atual_id] = 0;
						
					}
					
					if($item["valor_total"] > 0) {
						$this->bancodedados->query("
								INSERT INTO 
										tab_faturas_itens 
								SET
										fai_quantidade = 1, 
										fai_valor_unitario = {$item["valor_total"]}, 
										fai_valor_total = {$item["valor_total"]}, 
										fai_valor_icms = {$item["valor_icms"]}, 
										fai_tipo = '{$item["tipo"]}', 
										fai_natureza = '{$item["natureza"]}', 
										fat_id = {$faturas[$empresa_atual_id]}, 
										cft_id = {$item["tipo_id"]}");
					}
					
					$faturas_valor[$empresa_atual_id] += $item["valor_total"];
					
				}
				
			}
			
			foreach ($faturas as $empresa_atual_id => $fatura_atual_id) {
				$this->bancodedados->query("UPDATE tab_faturas SET fat_valor = {$faturas_valor[$empresa_atual_id]} WHERE fat_id = {$fatura_atual_id}");
			}
			
			$fatura_id = $faturas["1"] ? $faturas["1"] : "NULL";
			
			// Fatura Lote Cobrança

			$query = $this->bancodedados->query("
				SELECT 
					fco_id 
				FROM 
					tab_faturas_lotes_cobrancas 
				WHERE 
					fal_id = {$lote_id} AND
					coa_id = {$dados["id"]}");
			
			$fatura_lote_cobranca_id = $this->bancodedados->resultado($query);
			
			if (!$fatura_lote_cobranca_id) {
				
				$this->bancodedados->query("
					INSERT INTO 
						tab_faturas_lotes_cobrancas
					SET
						fco_valor_scm = {$fatura_itens["valor_scm"]}, 
						fco_valor_sva = {$fatura_itens["valor_sva"]}, 
						fco_valor_juro = {$fatura_itens["valor_juro"]}, 
						fco_valor_instalacao = {$fatura_itens["valor_instalacao"]}, 
						fco_valor_servicos = {$fatura_itens["valor_servicos"]}, 
						fco_valor_equipamentos = {$fatura_itens["valor_equipamentos"]}, 
						fco_valor_garantia = {$fatura_itens["valor_garantia"]}, 
						fal_id = {$lote_id}, 
						fat_id = {$fatura_id}, 
						coa_id = {$dados["id"]}");
				
				$fatura_lote_cobranca_id = $this->bancodedados->ultimoIdInserido();
				
			}
		
			// Seta fatura gerada na cobranca
			$this->bancodedados->query("
				UPDATE 
					tab_cobrancas 
				SET 
					fco_id = {$fatura_lote_cobranca_id}, 
					fat_id = {$fatura_id} 
				WHERE 
					coa_id = {$dados["id"]}");
			
			if ($dados["coa_pai"]) {
				
				// Seta fatura gerada na cobranca pai
				$this->bancodedados->query("
					UPDATE 
						tab_cobrancas 
					SET 
						fco_id = {$fatura_lote_cobranca_id}, 
						fat_id = {$fatura_id}
					WHERE 
						coa_id = {$dados["coa_pai"]} AND
						fco_id IS NULL");
				
				// Seta fatura gerada nas cobrancas irmãs
				$this->bancodedados->query("
					UPDATE 
						tab_cobrancas 
					SET 
						fco_id = {$fatura_lote_cobranca_id}, 
						fat_id = {$fatura_id} 
					WHERE 
						coa_pai = {$dados["coa_pai"]} AND 
						fco_id IS NULL");
				
			} else {
				
				// Seta fatura gerada nas cobrancas filhas
				$this->bancodedados->query("
					UPDATE 
						tab_cobrancas 
					SET 
						fco_id = {$fatura_lote_cobranca_id}, 
						fat_id = {$fatura_id}
					WHERE 
						coa_pai = {$dados["id"]} AND 
						fco_id IS NULL");
				
			}

		}
		
	}
	
	function buscaDadosCliente($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cli_nome AS nome,
				cli_nome_fantasia AS nome_fantasia,
				IF(clt_id = 1, 'R', 'B') AS tipo_pessoa,
				cli_cpf_cnpj AS cpf_cnpj,
				IF(cli_inscricao_estadual = '-1', '', cli_inscricao_estadual) AS inscricao_estadual
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		
			$dados["cpf_cnpj"] = preg_replace("/[^0-9]/", "", $dados["cpf_cnpj"]);
			$dados["inscricao_estadual"] = preg_replace("/[^0-9]/", "", $dados["inscricao_estadual"]);
			$dados["inscricao_estadual"] = $dados["inscricao_estadual"] ? $dados["inscricao_estadual"] : "ISENTO";
			$dados["inscricao_municipal"] = "";
			
			if (!$dados["nome_fantasia"]) {
				$dados["nome_fantasia"] = $dados["nome"];
			}
			
			$endereco = $dados["tipo_pessoa"] == 'R' ? $this->buscaEnderecoInstalacao($cliente_id) : $this->buscaEnderecoCobranca($cliente_id);
			
			$dados["endereco_logradouro"] = $endereco["logradouro"];
			$dados["endereco_numero"] = $endereco["numero"] ? $endereco["numero"] : "NULL";
			$dados["endereco_complemento"] = $endereco["complemento"];
			$dados["endereco_bairro"] = $endereco["bairro"];
			$dados["endereco_cep"] = $endereco["cep"];
			
			return $dados;
		
		}
		
	}
	
	function buscaEnderecoCobranca($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes_cobranca_dados.ccd_numero)) AS numero,
				tab_clientes_cobranca_dados.ccd_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				tab_ceps.cep_exportacao AS cep
			FROM
				tab_clientes_cobranca_dados
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes_cobranca_dados.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_ceps.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_ceps.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)
			WHERE
				tab_clientes_cobranca_dados.cli_id = {$cliente_id}");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
			
			return $dados;
			
		}

		return $this->buscaEnderecoInstalacao($cliente_id);
		
	}
	
	function buscaEnderecoInstalacao($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes.cli_numero)) AS numero,
				tab_clientes.cli_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				tab_ceps.cep_exportacao AS cep
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_ceps.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_ceps.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)
			WHERE
				tab_clientes.cli_id = {$cliente_id}");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
			
			return $dados;
			
		}
		
	}
	
	function corrigeComplementoCliente($complemento) {
		
	    $complementos_tipos = array(
	        "a" => "Apto ",
	        "l" => "Loja ",
	        "s" => "Sala ",
	        "c" => "Casa ",
	        "o" => ""
        );
		
		if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
			return $complementos_tipos[$complemento[0]].substr($complemento, 1, strlen($complemento));
		} else {
			return null;
		}
		
	}
	
	function buscaFaturaItens($dados_cobranca, $tipo_id, $empresa_id = null, $gerar_notas = false) {
		
		$natureza_tipo = $this->buscaNaturezaTipo($dados_cobranca["cliente_id"]);
		
		$itens = array();
		$cobrancas_detalhes = array();
		$valor_juro = (int) $dados_cobranca["valor_juro"];
		$valor_instalacao = 0;
		$valor_equipamentos = 0;
		$valor_servicos = 0;
		$valor_garantia = 0;
		
		$acrescimo = (int) $dados_cobranca["valor_acrescimo"];
		$decrescimo = (int) $dados_cobranca["valor_decrescimo"];
		$bonificacao = (int) $dados_cobranca["valor_desconto"];
		$valor_total = 0;
		
		if ($bonificacao > 0 && $acrescimo > 0) {
			if ($acrescimo >= $bonificacao) {
				$acrescimo -= $bonificacao;
				$bonificacao = 0;
			}
		}
		
		// Busca cobranças filhas
		if ($dados_cobranca["tipo_id"] == 3 || $dados_cobranca["tipo_id"] == 4) {
			$cobrancas_ids = $this->buscaCobrancasFilhas($dados_cobranca["coa_pai"] ? $dados_cobranca["coa_pai"] : $dados_cobranca["id"]);
		} else {
			$cobrancas_ids = $dados_cobranca["id"];
		}
		
		// Busca detalhamento
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_detalhes.ass_id AS assinatura_id,
				tab_cobrancas_detalhes.dec_id AS desconto_concessao_id,
				tab_cobrancas_detalhes.ajf_id AS ajuste_id,
				tab_cobrancas_detalhes.cod_descricao AS descricao, 
				tab_cobrancas_detalhes.cod_valor_final + IFNULL((SELECT SUM(tab_cobrancas_detalhes_2.cod_valor_final) FROM tab_cobrancas_detalhes tab_cobrancas_detalhes_2 WHERE tab_cobrancas_detalhes.cod_id = tab_cobrancas_detalhes_2.cod_pai), 0) AS valor_final
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id IN ({$cobrancas_ids}) AND
				tab_cobrancas_detalhes.cod_pai IS NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
                        $dados["plano_id"] = null;
			if ($dados["assinatura_id"]) {
				$dados_servico = $this->buscaServicoAssinatura($dados["assinatura_id"]);
				$dados["servico_id"] = $dados_servico["servico_id"];
				$dados["plano_id"] = $dados_servico["plano_id"];
			} elseif ($dados["ajuste_id"]) {
				$dados["ajuste_categoria_id"] = $this->buscaAjusteCategoria($dados["ajuste_id"]);
				if (!in_array($dados["ajuste_categoria_id"], array(19, 20, 23))) {
					$dados_servico = $this->buscaServicoAjuste($dados["ajuste_id"]);
					$dados["servico_id"] = $dados_servico["servico_id"];
					$dados["plano_id"] = $dados_servico["plano_id"];
				}
			}
			
			if ($bonificacao) {
				if ($dados["valor_final"] > $bonificacao) {
					$dados["valor_final"] -= $bonificacao;
					$bonificacao = 0;
				} elseif ($dados["valor_final"] < $bonificacao) {
					$bonificacao -= $dados["valor_final"];
					$dados["valor_final"] = 0;
				} else {
					$dados["valor_final"] = 0;
					$bonificacao = 0;
				}
			}
			
			if (array_key_exists("servico_id", $dados) && $dados["servico_id"]) {
					$dados["classes_fiscais"] = $this->buscaClassesFiscaisPlano($dados["plano_id"], $tipo_id, $empresa_id, $gerar_notas);
					if (!$dados["classes_fiscais"]) {
						$dados["classes_fiscais"] = $this->buscaClassesFiscaisServico($dados["servico_id"], $tipo_id, $empresa_id, $gerar_notas);
					}
			} elseif (array_key_exists("ajuste_categoria_id", $dados) && $dados["ajuste_categoria_id"]) {
				$dados["classes_fiscais"] = $this->buscaClassesFiscaisAjuste($dados["ajuste_categoria_id"], $tipo_id, $empresa_id, $gerar_notas);
			} else {
				$dados["classes_fiscais"] = null;
			}
			
			if ($dados["classes_fiscais"]) {
				$valor_total += $dados["valor_final"];
				$cobrancas_detalhes[] = $dados;
			} elseif ($dados["plano_id"] == 736) {
				$valor_equipamentos += $dados["valor_final"];
			} elseif (array_key_exists("ajuste_categoria_id", $dados) && $dados["ajuste_categoria_id"] == 19) {
				$valor_instalacao += $dados["valor_final"];
			} elseif (array_key_exists("ajuste_categoria_id", $dados) && $dados["ajuste_categoria_id"] == 20) {
				$valor_servicos += $dados["valor_final"];
			} elseif (array_key_exists("ajuste_categoria_id", $dados) && $dados["ajuste_categoria_id"] == 23) {
				$valor_equipamentos += $dados["valor_final"];
			} elseif ($dados["descricao"] == "Decréscimo") {
				$decrescimo -= $dados["valor_final"];
			} elseif ($dados["descricao"] == "Acréscimo") {
				$acrescimo += $dados["valor_final"];
			} elseif (substr($dados["descricao"], 0, 4) == "Juro") {
				$valor_juro += $dados["valor_final"];
			} elseif ($dados["valor_final"] != 0) {
				if ($dados["valor_final"] > 0) {
					$acrescimo += $dados["valor_final"];
				} else {
					$decrescimo -= $dados["valor_final"];
				}
			}
		
		}
		
		if ($valor_instalacao < 0) {
			$decrescimo -= $valor_instalacao;
			$valor_instalacao = 0;
		}
		if ($valor_equipamentos < 0) {
			$decrescimo -= $valor_equipamentos;
			$valor_equipamentos = 0;
		}
		if ($valor_servicos < 0) {
			$decrescimo -= $valor_servicos;
			$valor_servicos = 0;
		}
		if ($valor_garantia < 0) {
			$decrescimo -= $valor_garantia;
			$valor_garantia = 0;
		}
		
		if ($bonificacao) {
			$decrescimo += $bonificacao;
		}
		
		$valor_total_calculado = $valor_total + $valor_instalacao + $valor_servicos + $valor_equipamentos + $valor_garantia - $decrescimo + $acrescimo + $valor_juro;
		
		if ($dados_cobranca["status_id"] != 1 && $valor_total_calculado != $dados_cobranca["valor_total"]) {
			if ($valor_total_calculado > $dados_cobranca["valor_total"]) {
				$decrescimo += $valor_total_calculado - $dados_cobranca["valor_total"];
			} else {
				$acrescimo += $dados_cobranca["valor_total"] - $valor_total_calculado;
			}
		}
		
		if ($acrescimo > 0 && $decrescimo > 0) {
			if ($acrescimo < $decrescimo) {
				$decrescimo -= $acrescimo;
				$acrescimo = 0;
			} else {
				$acrescimo -= $decrescimo;
				$decrescimo = 0;
			}
		}
		
		if ($valor_juro > 0 && $decrescimo > 0) {
			if ($valor_juro <= $decrescimo) {
				$decrescimo -= $valor_juro;
				$valor_juro = 0;
			} else {
				$valor_juro -= $decrescimo;
				$decrescimo = 0;
			}
		}
		
		if ($acrescimo <= 400 && $valor_juro > 0) {
			$valor_juro += $acrescimo;
			$acrescimo = 0;
		}
		
		$diferenca_total = $sobra = $acrescimo - $decrescimo;
		
		if ($diferenca_total != 0) {
			
			$total_faturado = $valor_total + $valor_instalacao + $valor_servicos + $valor_equipamentos + $valor_garantia;
			
			if ($total_faturado + $diferenca_total > 0) {
				
				$valor_total_ajustado = $valor_total;
				
				if (count($cobrancas_detalhes)) {
					
					foreach ($cobrancas_detalhes as $i => $dados) {
						$diferenca = (int) ($dados["valor_final"] / $total_faturado * $diferenca_total);
						$sobra -= $diferenca;
						$cobrancas_detalhes[$i]["valor_final"] += $diferenca;
						$valor_total_ajustado += $diferenca;
					}
					
				}
				
				if ($sobra != 0 && ($valor_instalacao || $valor_servicos || $valor_equipamentos || $valor_garantia)) {
					
					if ($valor_instalacao) {
						$diferenca = (int) ($valor_instalacao / $total_faturado * $diferenca_total);
						$sobra -= $diferenca;
						$valor_instalacao += $diferenca;
					}
					if ($valor_servicos) {
						$diferenca = (int) ($valor_servicos / $total_faturado * $diferenca_total);
						$sobra -= $diferenca;
						$valor_servicos += $diferenca;
					} 
					if ($valor_equipamentos) {
						$diferenca = (int) ($valor_equipamentos / $total_faturado * $diferenca_total);
						$sobra -= $diferenca;
						$valor_equipamentos += $diferenca;
					}
					if ($valor_garantia) {
						$diferenca = (int) ($valor_garantia / $total_faturado * $diferenca_total);
						$sobra -= $diferenca;
						$valor_garantia += $diferenca;
					}
					
				}
				
				while ($sobra != 0 && $valor_total_ajustado > 0) {
					foreach ($cobrancas_detalhes as $i => $dados) {
						if ($sobra < 0) {
							if ($cobrancas_detalhes[$i]["valor_final"] > 0) {
								$cobrancas_detalhes[$i]["valor_final"]--;
								$valor_total_ajustado--;
								$sobra++;
							}
						} elseif ($sobra > 0) {
							$cobrancas_detalhes[$i]["valor_final"]++;
							$valor_total_ajustado++;
							$sobra--;
						}
					}
				}
					
				while ($sobra != 0 && ($valor_instalacao + $valor_servicos + $valor_equipamentos) > 0) {
					if ($valor_instalacao) {
						if ($sobra < 0) {
							$valor_instalacao--;
							$sobra++;
						} elseif ($sobra > 0) {
							$valor_instalacao++;
							$sobra--;
						}
					}
					if ($valor_servicos) {
						if ($sobra < 0) {
							$valor_servicos--;
							$sobra++;
						} elseif ($sobra > 0) {
							$valor_servicos++;
							$sobra--;
						}
					} 
					if ($valor_equipamentos) {
						if ($sobra < 0) {
							$valor_equipamentos--;
							$sobra++;
						} elseif ($sobra > 0) {
							$valor_equipamentos++;
							$sobra--;
						}
					}
					if ($valor_garantia) {
						if ($sobra < 0) {
							$valor_garantia--;
							$sobra++;
						} elseif ($sobra > 0) {
							$valor_garantia++;
							$sobra--;
						}
					}
				}				
				
			} else {
				
				$cobrancas_detalhes = array();
				$valor_instalacao = 0;
				$valor_servicos = 0;
				$valor_equipamentos = 0;
				$valor_garantia = 0;
				
			}
			
		}
		
		$natureza = null;
		
		foreach ($cobrancas_detalhes as $dados) {
			
			if (!$natureza || $dados["classes_fiscais"]["tributado"]) {
				$natureza = $dados["classes_fiscais"]["natureza"][$natureza_tipo];
			}
			
			$valor_total_item = 0;
			$total_taxas = 0;
			for ($i = 0; $i < count($dados["classes_fiscais"]["itens"]); $i++) {
				$valor_item = round($dados["classes_fiscais"]["itens"][$i]["taxa"] * $dados["valor_final"] / 100, 0);
				$valor_total_item += $valor_item;
				$total_taxas += $dados["classes_fiscais"]["itens"][$i]["taxa"];
				if (!array_key_exists($dados["classes_fiscais"]["itens"][$i]["tipo_id"], $itens)) {
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]] = array();
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["tipo"] = $dados["classes_fiscais"]["itens"][$i]["tipo"];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["tipo_id"] = $dados["classes_fiscais"]["itens"][$i]["tipo_id"];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["descricao"] = $dados["classes_fiscais"]["itens"][$i]["descricao"];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["valor_total"] = 0;
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["valor_icms"] = 0;
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["icms"] = floatval($dados["classes_fiscais"]["itens"][$i]["icms"]);
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["natureza"] = $dados["classes_fiscais"]["itens"][$i][$natureza_tipo];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["empresa_id"] = $dados["classes_fiscais"]["itens"][$i]["empresa_id"];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["empresa_nome"] = $dados["classes_fiscais"]["itens"][$i]["empresa_nome"];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["empresa_razao_social"] = $dados["classes_fiscais"]["itens"][$i]["empresa_razao_social"];
					$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["empresa_cnpj"] = $dados["classes_fiscais"]["itens"][$i]["empresa_cnpj"];
				}
				$itens[$dados["classes_fiscais"]["itens"][$i]["tipo_id"]]["valor_total"] += $valor_item;
			}
			
			// Corrige Arredondamento
			if ($total_taxas == 100) {
				$diferenca_arredondamento = $dados["valor_final"] - $valor_total_item;
				if ($diferenca_arredondamento != 0) {
					do {
						$classe = current($dados["classes_fiscais"]["itens"]);
						if ($diferenca_arredondamento > 0) {
							$itens[$classe["tipo_id"]]["valor_total"]++;
							$diferenca_arredondamento--;
						} else {
							$itens[$classe["tipo_id"]]["valor_total"]--;
							$diferenca_arredondamento++;
						}
					} while ($diferenca_arredondamento != 0 && next($dados["classes_fiscais"]["itens"]));
				}
			}
			
		}
		
		if (count($itens)) {
			
			$total_negativo = 0;
			$total_positivo = 0;
			
			foreach ($itens as $tipo_id => $item) {
				if ($item["valor_total"] <= 0) {
					$total_negativo -= $item["valor_total"];
				} else {
					$total_positivo += $item["valor_total"];
				}
			}
			
			if ($total_negativo > 0) {
				
				$total = $total_positivo + $valor_instalacao + $valor_equipamentos + $valor_servicos + $valor_garantia;
				
				foreach ($itens as $tipo_id => $item) {
					$diferenca = (int) ($item["valor_total"] / $total * $total_negativo);
					$total_negativo -= $diferenca;
					$itens[$tipo_id]["valor_total"] -= $diferenca;
				}
				
				if ($total_negativo) {
					if ($valor_instalacao) {
						$diferenca = (int) ($valor_instalacao / $total * $total_negativo);
						$total_negativo -= $diferenca;
						$valor_instalacao -= $diferenca;
					}
					if ($valor_servicos) {
						$diferenca = (int) ($valor_servicos / $total * $total_negativo);
						$total_negativo -= $diferenca;
						$valor_servicos -= $diferenca;
					} 
					if ($valor_equipamentos) {
						$diferenca = (int) ($valor_equipamentos / $total * $total_negativo);
						$total_negativo -= $diferenca;
						$valor_equipamentos -= $diferenca;
					}
					if ($valor_garantia) {
						$diferenca = (int) ($valor_garantia / $total * $total_negativo);
						$total_negativo -= $diferenca;
						$valor_garantia -= $diferenca;
					}
				}
				
				// Corrige arredondamento
				while ($total_negativo) {
					foreach ($itens as $tipo_id => $item) {
						if ($itens[$tipo_id]["valor_total"] && $total_negativo) {
							$itens[$tipo_id]["valor_total"]--;
							$total_negativo--;
						}
					}
					if ($valor_instalacao && $total_negativo) {
						$total_negativo--;
						$valor_instalacao--;
					}
					if ($valor_servicos && $total_negativo) {
						$total_negativo--;
						$valor_servicos--;
					} 
					if ($valor_equipamentos && $total_negativo) {
						$total_negativo--;
						$valor_equipamentos--;
					}
					if ($valor_garantia && $total_negativo) {
						$total_negativo--;
						$valor_garantia--;
					}
				}
				
			}
			
		}
		
		$valor_scm = 0;
		$valor_sva = 0;
		
		foreach ($itens as $tipo_id => $item) {
			if ($item["valor_total"] > 0) {
				if ($item["tipo"] == "SCM") {
					$valor_scm += $item["valor_total"];
				} else {
					$valor_sva += $item["valor_total"];
				}
				$itens[$tipo_id]["valor_icms"] += round($item["icms"] * $item["valor_total"] / 100, 0);
			}
		}
		
		return array("itens" => $itens, "valor_scm" => $valor_scm, "valor_sva" => $valor_sva, "valor_juro" => $valor_juro, "valor_instalacao" => $valor_instalacao, "valor_servicos" => $valor_servicos, "valor_equipamentos" => $valor_equipamentos, "valor_garantia" => $valor_garantia, "natureza" => $natureza);

	}
	
	function buscaCobrancasFilhas($cobranca_id) {
		
		$cobrancas_ids = array();
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id,
				cop_id AS tipo_id
			FROM
				tab_cobrancas
			WHERE
				coa_grupo_id = {$cobranca_id} AND
				coa_pai IS NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["tipo_id"] == 3) {
				$cobrancas_ids[] = $this->buscaCobrancasFilhas($dados["id"]);
			} else {
				$cobrancas_ids[] = $dados["id"];
			}
		}
		
		return implode(", ", $cobrancas_ids);
		
	}
	
	function buscaNaturezaTipo($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				IF(tab_clientes.clt_id = 1, 'PF', 'PJ') AS pessoa_tipo,
				est_sigla AS estado
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
				INNER JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)
			WHERE
				tab_clientes.cli_id = {$cliente_id}");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["pessoa_tipo"] == "PF") {
				if ($dados["estado"] == "SC") {
					return "natureza_pf_sc";
				} else {
					return "natureza_pf_outros";
				}
			} else {
				if ($dados["estado"] == "SC") {
					return "natureza_pj_sc";
				} else {
					return "natureza_pj_outros";
				}
			}
		}
		
	}
	
	function buscaClassesFiscaisPlano($plano_id, $tipo_id, $empresa_id = null, $gerar_notas = false) {
		
	    $classes = array(
	        "tributado" => false,
	        "itens" => array(),
	        "natureza" => null
        );
		
		$query = $this->bancodedados->query("
			SELECT
				tab_classes_fiscais.clf_taxa AS taxa,
				tab_classes_fiscais_tipos.cft_id AS tipo_id,
				tab_classes_fiscais_tipos.cft_tipo AS tipo,
				tab_classes_fiscais_tipos.cft_icms AS icms,
				tab_classes_fiscais_tipos.cft_descricao AS descricao,
				tab_classes_fiscais_tipos.cft_natureza_pf_sc AS natureza_pf_sc,
				tab_classes_fiscais_tipos.cft_natureza_pj_sc AS natureza_pj_sc,
				tab_classes_fiscais_tipos.cft_natureza_pf_outros AS natureza_pf_outros,
				tab_classes_fiscais_tipos.cft_natureza_pj_outros AS natureza_pj_outros,
				tab_empresas.emp_id AS empresa_id,
				tab_empresas.emp_nome AS empresa_nome,
				tab_empresas.emp_razao_social AS empresa_razao_social,
				tab_empresas.emp_cnpj AS empresa_cnpj
			FROM
				tab_classes_fiscais
				INNER JOIN tab_classes_fiscais_tipos ON (tab_classes_fiscais.cft_id = tab_classes_fiscais_tipos.cft_id)
				INNER JOIN tab_empresas ON (tab_classes_fiscais_tipos.emp_id = tab_empresas.emp_id)
			WHERE
				tab_classes_fiscais.pla_id = {$plano_id}".($gerar_notas ? " AND
				tab_classes_fiscais_tipos.cft_gerar_nota = 1" : "").(is_null($empresa_id) ? "" : " AND
				tab_empresas.emp_id = {$empresa_id}"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($tipo_id == 1) {
				
				$dados["icms"] = 0;
				$dados["tipo"] = "SVA";
				
				$dados["natureza_pf_sc"] = "";
				$dados["natureza_pj_sc"] = "";
				$dados["natureza_pf_outros"] = "";
				$dados["natureza_pj_outros"] = "";
				
			}
			
			if (!$classes["natureza"] || $dados["icms"] > 0) {
				$classes["natureza"] = array(
					"natureza_pf_sc" => $dados["natureza_pf_sc"],
					"natureza_pj_sc" => $dados["natureza_pj_sc"],
					"natureza_pf_outros" => $dados["natureza_pf_outros"],
					"natureza_pj_outros" => $dados["natureza_pj_outros"]
				);
			}
			
			if ($dados["icms"] > 0) {
				$classes["tributado"] = true;
			}
			
			$classes["itens"][] = $dados;
			
		}
		
		if (count($classes["itens"])) {
			return $classes;
		}
		
		return null;
		
	}
	
	function buscaClassesFiscaisServico($servico_id, $tipo_id, $empresa_id = null, $gerar_notas = false) {
		
	    $classes = array(
	        "tributado" => false,
	        "itens" => array(),
	        "natureza" => null
        );
		
		$query = $this->bancodedados->query("
			SELECT
				tab_classes_fiscais.clf_taxa AS taxa,
				tab_classes_fiscais_tipos.cft_id AS tipo_id,
				tab_classes_fiscais_tipos.cft_tipo AS tipo,
				tab_classes_fiscais_tipos.cft_icms AS icms,
				tab_classes_fiscais_tipos.cft_descricao AS descricao,
				tab_classes_fiscais_tipos.cft_natureza_pf_sc AS natureza_pf_sc,
				tab_classes_fiscais_tipos.cft_natureza_pj_sc AS natureza_pj_sc,
				tab_classes_fiscais_tipos.cft_natureza_pf_outros AS natureza_pf_outros,
				tab_classes_fiscais_tipos.cft_natureza_pj_outros AS natureza_pj_outros,
				tab_empresas.emp_id AS empresa_id,
				tab_empresas.emp_nome AS empresa_nome,
				tab_empresas.emp_razao_social AS empresa_razao_social,
				tab_empresas.emp_cnpj AS empresa_cnpj
			FROM
				tab_classes_fiscais
				INNER JOIN tab_classes_fiscais_tipos ON (tab_classes_fiscais.cft_id = tab_classes_fiscais_tipos.cft_id)
				INNER JOIN tab_empresas ON (tab_classes_fiscais_tipos.emp_id = tab_empresas.emp_id)
			WHERE
				tab_classes_fiscais.pls_id = {$servico_id}".($gerar_notas ? " AND
				tab_classes_fiscais_tipos.cft_gerar_nota = 1" : "").(is_null($empresa_id) ? "" : " AND
				tab_empresas.emp_id = {$empresa_id}"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
						
			if ($tipo_id == 1) {
				
				$dados["icms"] = 0;
				$dados["tipo"] = "SVA";
				
				$dados["natureza_pf_sc"] = "";
				$dados["natureza_pj_sc"] = "";
				$dados["natureza_pf_outros"] = "";
				$dados["natureza_pj_outros"] = "";
				
			}

			if (!$classes["natureza"] || $dados["icms"] > 0) {
				$classes["natureza"] = array(
					"natureza_pf_sc" => $dados["natureza_pf_sc"],
					"natureza_pj_sc" => $dados["natureza_pj_sc"],
					"natureza_pf_outros" => $dados["natureza_pf_outros"],
					"natureza_pj_outros" => $dados["natureza_pj_outros"]
				);
			}
			
			if ($dados["icms"] > 0) {
				$classes["tributado"] = true;
			}
			
			$classes["itens"][] = $dados;
			
		}
		
		if (count($classes["itens"])) {
			return $classes;
		}
		
		return null;
		
	}
	
	function buscaClassesFiscaisAjuste($ajuste_categoria_id, $tipo_id, $empresa_id = null, $gerar_notas = false) {
		
	    $classes = array(
	        "tributado" => false,
	        "itens" => array(),
	        "natureza" => null
	    );
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_classes_fiscais.clf_taxa AS taxa,
				tab_classes_fiscais_tipos.cft_id AS tipo_id,
				tab_classes_fiscais_tipos.cft_tipo AS tipo,
				tab_classes_fiscais_tipos.cft_icms AS icms,
				tab_classes_fiscais_tipos.cft_descricao AS descricao,
				tab_classes_fiscais_tipos.cft_natureza_pf_sc AS natureza_pf_sc,
				tab_classes_fiscais_tipos.cft_natureza_pj_sc AS natureza_pj_sc,
				tab_classes_fiscais_tipos.cft_natureza_pf_outros AS natureza_pf_outros,
				tab_classes_fiscais_tipos.cft_natureza_pj_outros AS natureza_pj_outros,
				tab_empresas.emp_id AS empresa_id,
				tab_empresas.emp_nome AS empresa_nome,
				tab_empresas.emp_razao_social AS empresa_razao_social,
				tab_empresas.emp_cnpj AS empresa_cnpj
			FROM
				tab_classes_fiscais
				INNER JOIN tab_classes_fiscais_tipos ON (tab_classes_fiscais.cft_id = tab_classes_fiscais_tipos.cft_id)
				INNER JOIN tab_empresas ON (tab_classes_fiscais_tipos.emp_id = tab_empresas.emp_id)
			WHERE
				tab_classes_fiscais.afc_id = {$ajuste_categoria_id}".($gerar_notas ? " AND
				tab_classes_fiscais_tipos.cft_gerar_nota = 1" : "").(is_null($empresa_id) ? "" : " AND
				tab_empresas.emp_id = {$empresa_id}"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
						
			if ($tipo_id == 1) {
				
				$dados["icms"] = 0;
				$dados["tipo"] = "SVA";
				
				$dados["natureza_pf_sc"] = "";
				$dados["natureza_pj_sc"] = "";
				$dados["natureza_pf_outros"] = "";
				$dados["natureza_pj_outros"] = "";
				
			}

			if (!$classes["natureza"] || $dados["icms"] > 0) {
				$classes["natureza"] = array(
					"natureza_pf_sc" => $dados["natureza_pf_sc"],
					"natureza_pj_sc" => $dados["natureza_pj_sc"],
					"natureza_pf_outros" => $dados["natureza_pf_outros"],
					"natureza_pj_outros" => $dados["natureza_pj_outros"]
				);
			}
			
			if ($dados["icms"] > 0) {
				$classes["tributado"] = true;
			}
			
			$classes["itens"][] = $dados;
			
		}
		
		if (count($classes["itens"])) {
			return $classes;
		}
		
		return null;
		
	}
	
	function buscaServicoAssinatura($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				pla_id AS plano_id,
				pls_id AS servico_id
			FROM
				tab_planos
			WHERE
				pla_id = (
					SELECT
						pla_id 
					FROM
						tab_assinaturas 
					WHERE
						ass_id = {$assinatura_id}
				)
			LIMIT 1");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaServicoContrato($contrato_id) {
				
		$query = $this->bancodedados->query("
			SELECT
				pla_id AS plano_id,
				pls_id AS servico_id
			FROM
				tab_planos
			WHERE
				pla_id = (
					SELECT
						tab_assinaturas.pla_id 
					FROM
						tab_assinaturas
					WHERE 
						tab_assinaturas.ass_pai IS NULL AND
						tab_assinaturas.ass_id IN (
							SELECT
								rel_ass_cor_estacontido.ass_id
							FROM
								rel_ass_cor_estacontido
							WHERE
								rel_ass_cor_estacontido.cor_id = {$contrato_id}
						)
				)
			LIMIT 1");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaServicoAjuste($ajuste_id) {
				
		$query = $this->bancodedados->query("
			SELECT
				ass_id AS assinatura_id,
				cor_id AS contrato_id
			FROM
			  tab_ajustes_financeiros
			WHERE
				ajf_id = {$ajuste_id}");
				
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados["assinatura_id"]) {
			return $this->buscaServicoAssinatura($dados["assinatura_id"]);
		} elseif ($dados["contrato_id"]) {
			return $this->buscaServicoContrato($dados["contrato_id"]);
		}
		
	}
	
	function buscaAjusteCategoria($ajuste_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				 afc_id
			FROM
				tab_ajustes_financeiros
			WHERE
				ajf_id = {$ajuste_id}");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function ehRegeravel() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["regerar"])	return false;
		if ($this->dados_item["status_id"] != 1) return false;
		
		return true;
		
	}
	
	function ehRemovivel() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["remover"]) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		
		return true;
		
	}
	
	function ehProcessavel() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["processar"]) return false;
		if ($this->dados_item["tipo_id"] == 1) return false;
		if ($this->dados_item["status_id"] > 2) return false;
		
		return true;
		
	}
	
	function ehAprovavel() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["aprovar"])	return false;
		
		if ($this->dados_item["tipo_id"] == 1) {
			return $this->dados_item["status_id"] == 1;
		} else {
			return $this->dados_item["status_id"] == 2;
		}
		
	}
	
	###########
	### Ver ###
	###########
	
	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:65" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehProcessavel()) $botoes[] = '<input type="button" name="processar" class="buttonAzulEscuroBranco11" style="width:65" value="Processar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=processar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehRegeravel()) $botoes[] = '<input type="button" name="regerar" class="buttonAzulEscuroBranco11" style="width:65" value="Regerar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=regerar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("faturas_lotes.ver.inc.php");

				$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "ver_faturas" => "Faturas")), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}", $this->conteudo);
				$this->conteudo = str_replace('[ciclo]', $this->dados_item["ciclo_formatado"], $this->conteudo);
				$this->conteudo = str_replace('[tipo_nome]', $this->dados_item["tipo_nome"], $this->conteudo);
				$this->conteudo = str_replace('[clientes]', $this->dados_item["clientes"], $this->conteudo);
				$this->conteudo = str_replace('[faturas]', $this->dados_item["faturas"], $this->conteudo);
				$this->conteudo = str_replace('[total_icms]', $this->dados_item["total_icms"], $this->conteudo);
				$this->conteudo = str_replace('[total]', $this->dados_item["total"], $this->conteudo);
				$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
				$this->conteudo = str_replace('[data_processamento]', $this->dados_item["data_processamento"], $this->conteudo);
				$this->conteudo = str_replace('[data_aprovacao]', $this->dados_item["data_aprovacao"], $this->conteudo);
				$this->conteudo = str_replace('[filtros]', $this->geraFiltrosListagem($this->buscaLotesFiltros($this->dados_item["id"]), false), $this->conteudo);
				$this->conteudo = str_replace('[status_nome]', $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace('[lista_comentarios]', $this->geraComentariosListagem($this->buscaComentarios($this->dados_item["id"])), $this->conteudo);
				$this->conteudo = str_replace('[botoes]', implode(' ', $botoes), $this->conteudo);
			
			} else {
				
				$_POST["comentarios"] = trim($_POST["comentarios"]);
				if ($_POST["comentarios"]) {
					$this->registraComentario($_GET["i"], "<b>Comentário:</b> " . preg_replace("/[\r\n\t ]+/", " ", $_POST["comentarios"]), $_SESSION["usuario"]);
				}
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function buscaLotesFiltros($lote_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				faf_id AS campo,
				flf_comparacao AS comparacao,
				flf_valor AS valor
			FROM
				tab_faturas_lotes_filtros
			WHERE
				fal_id = {$lote_id}
			ORDER BY
				faf_id, 
				flf_comparacao, 
				flf_valor");
		
		return $this->bancodedados->resultadoArrayTotal($query);
		
	}
	
	###################
	### Ver Faturas ###
	###################
	

	function ver_faturas() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:65" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehProcessavel()) $botoes[] = '<input type="button" name="processar" class="buttonAzulEscuroBranco11" style="width:65" value="Processar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=processar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehRegeravel()) $botoes[] = '<input type="button" name="regerar" class="buttonAzulEscuroBranco11" style="width:65" value="Regerar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=regerar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

			$this->conteudo .= $this->carregaArquivo("faturas_lotes.ver_faturas.inc.php");

			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "ver_faturas" => "Faturas")), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}", $this->conteudo);
			$this->conteudo = str_replace('[ciclo]', $this->dados_item["ciclo_formatado"], $this->conteudo);
			$this->conteudo = str_replace('[clientes]', $this->dados_item["clientes"], $this->conteudo);
			$this->conteudo = str_replace('[faturas]', $this->dados_item["faturas"], $this->conteudo);
			$this->conteudo = str_replace('[total_icms]', $this->dados_item["total_icms"], $this->conteudo);
			$this->conteudo = str_replace('[total]', $this->dados_item["total"], $this->conteudo);
			$this->conteudo = str_replace('[faturas_listagem]', $this->buscaFaturas($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
		
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function buscaFaturas($lote_id) {
		
		$faturas = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.cli_id AS cliente_id,
			 	tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.cof_id AS formato_id,
			 	DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_processamento, '%d/%m/%Y') AS processamento,
				tab_faturas_lotes_cobrancas.fco_valor_scm AS valor_scm,
				tab_faturas_lotes_cobrancas.fco_valor_sva AS valor_sva,
				tab_faturas_lotes_cobrancas.fco_valor_juro AS valor_juro,
				tab_faturas_lotes_cobrancas.fco_valor_instalacao AS valor_instalacao,
				tab_faturas_lotes_cobrancas.fco_valor_servicos AS valor_servicos,
				tab_faturas_lotes_cobrancas.fco_valor_equipamentos AS valor_equipamentos,
				tab_faturas_lotes_cobrancas.fco_valor_garantia AS valor_garantia,
				(SELECT SUM(fai_valor_icms) FROM tab_faturas_itens INNER JOIN tab_faturas ON (tab_faturas_itens.fat_id = tab_faturas.fat_id) WHERE tab_faturas_itens.fat_id = tab_faturas.fat_id AND tab_faturas.coa_id = tab_faturas_lotes_cobrancas.coa_id) AS total_icms
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
			  tab_faturas_lotes_cobrancas.fal_id = {$lote_id}");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes"]["ver_df"]) {
				$dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			if ($this->permissoes["cobrancas"]["ver_dg"]) {
				$dados["link"] = "javascript:verCobranca({$dados["cobranca_id"]})";
			}
			if (strlen($dados["cliente_nome"]) > 50) {
				$dados["cliente_nome"] = substr($dados["cliente_nome"], 0, 50) . "...";
			}
			$dados["formato"] = in_array($dados["formato_id"], array(23, 25, 27, 28)) ? "Olé" : "Firsti";
			
			$dados["valor_total"] = $dados["valor_scm"] + $dados["valor_sva"] + $dados["valor_equipamentos"] + $dados["valor_instalacao"] + $dados["valor_servicos"] + $dados["valor_juro"] + $dados["valor_garantia"];
			
			$dados["valor_scm"] = $this->formataDinheiro($dados["valor_scm"], 'R$', false);
			$dados["valor_sva"] = $this->formataDinheiro($dados["valor_sva"], 'R$', false);
			$dados["valor_equipamentos"] = $this->formataDinheiro($dados["valor_equipamentos"], 'R$', false);
			$dados["valor_instalacao"] = $this->formataDinheiro($dados["valor_instalacao"], 'R$', false);
			$dados["valor_servicos"] = $this->formataDinheiro($dados["valor_servicos"], 'R$', false);
			$dados["valor_juro"] = $this->formataDinheiro($dados["valor_juro"], 'R$', false);
			$dados["valor_garantia"] = $this->formataDinheiro($dados["valor_garantia"], 'R$', false);
			$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"], 'R$', false);
			
			$dados["total_icms"] = $this->formataDinheiro($dados["total_icms"], 'R$', false);
			
			$faturas[] = $dados;
			
		}
		
		$cabecalho = array("cliente_id" => "ID", "codigo" => "Código", "valor_scm" => "SCM", "valor_sva" => "SVA", "valor_equipamentos" => "Equip.", "valor_instalacao" => "Instalação", "valor_servicos" => "Serviços", "valor_garantia" => "Garantia", "valor_juro" => "Juros", "valor_total" => "Total", "total_icms" => "ICMS");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $faturas, 0, array("valor_scm" => "formataDinheiro", "valor_sva" => "formataDinheiro", "valor_instalacao" => "formataDinheiro", "valor_servicos" => "formataDinheiro", "valor_equipamentos" => "formataDinheiro", "valor_juro" => "formataDinheiro", "total_icms" => "formataDinheiro", "valor_total" => "formataDinheiro"), null, null, array("center", "center", "right", "right", "right", "right", "right", "right", "right", "right", "right"));
		
		return $listagem->conteudo;
		
	}
	
	######################
	### Processar Lote ###
	######################
	

	function processar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("faturas_lotes.processar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=processar&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$this->processarLote($this->dados_item, $_POST["comentario"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function processarLote($dados_lote, $comentario_usuario = null) {
		
		set_time_limit(0);
		ignore_user_abort(1);
		ini_set("default_socket_timeout", 3600);
		
		// Chama webservice
		$webservice = new SoapClient($this->webservice);
		$retorno = $webservice->start_invoice_cycle_string(array("Cycle" => $dados_lote["ciclo"]));
		$xml = simplexml_load_string($retorno->start_invoice_cycle_stringResult);
		$status_id = (string) $xml->status_id;
		$status = (string) $xml->status;
		
		if ($status_id == "100") {
			
			$this->bancodedados->query("
				UPDATE 
					tab_faturas_lotes 
				SET 
					fal_data_processamento = NOW(), 
					fls_id = 2 
				WHERE 
					fal_id = {$dados_lote["id"]}");
					
			$sucesso = true;
			
		} else {
			
			$this->bancodedados->query("
				UPDATE 
					tab_faturas_lotes 
				SET 
					fal_data_processamento = NOW(), 
					fls_id = 1 
				WHERE 
					fal_id = {$dados_lote["id"]}");
			
			$sucesso = false;
			
		}
		
		$status_codes =  array(
			"100" => "Processado com sucesso",
			"101" => "Ciclo já processado",
			"102" => "Não foi possível processar o ciclo",
			"103" => "Erro apagar as notas para processar novamente",
			"104" => "Erro ao atualizar a data do ciclo",
			"105" => "Erro ao buscar a sequencia do ciclo",
			"106" => "Erro ao verificar se o ciclo está aprovado",
			"107" => "Erro ao inserir o protocolo",
			"108" => "Erro ao gerar o protocolo",
			"109" => "Há ciclo que ainda não foi aprovado",
			"110" => "Ciclo processado, mas foram encontrados erros",
			"111" => "Erro de validação do xml",
			"112" => "Erro ao verificar se há algum ciclo em aberto"
		);
		
		// Registra Comentário
		$comentario = "<b>Ação:</b> Processar<br><b>Retorno:</b> " . $status_codes[$status_id] . ($status_id != "100" ? " ($status)" : "");
		if (trim($_POST["comentario"])) $comentario .= "<br><b>Comentário:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($comentario_usuario));
		$this->registraComentario($dados_lote["id"], $comentario, $_SESSION["usuario"]);
		
		// Retorna status
		return $sucesso;
		
	}
	
	####################
	### Aprovar Lote ###
	####################

	function aprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("faturas_lotes.aprovar.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=aprovar&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				if ($this->dados_item["tipo_id"] == 1) {
					
					$this->bancodedados->query("
						UPDATE 
							tab_faturas_lotes 
						SET 
							fal_data_aprovacao = NOW(), 
							fls_id = 3 
						WHERE 
							fal_id = {$this->dados_item["id"]}");
					
					$comentario = "<b>Ação:</b> Ciclo aprovado";
					if (trim($_POST["comentario"])) {
						$comentario .= "<br><b>Comentário:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
					}
					
					$this->registraComentario($this->dados_item["id"], $comentario, $_SESSION["usuario"]);
					
				} else {
					$this->aprovarLote($this->dados_item, $_POST["comentario"]);
				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function aprovarLote($dados_lote, $comentario_usuario = null) {
		
		set_time_limit(0);
		ignore_user_abort(1);
		ini_set("default_socket_timeout", 3600);
		
		$webservice = new SoapClient($this->webservice);
		$retorno = $webservice->aprovar_ciclo(array("Cycle" => $dados_lote["ciclo"]));
		
		if ($retorno->aprovar_cicloResult) {
			$comentario = "<b>Ação:</b> Aprovar<br><b>Retorno:</b> {$retorno->aprovar_cicloResult}";
			if (trim($comentario_usuario)) $comentario .= "<br><b>Comentário:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($comentario_usuario));
			$this->registraComentario($dados_lote["id"], $comentario, $_SESSION["usuario"]);
		} else {
			$this->cicloAprovado($dados_lote["ciclo"], $_POST["comentario"]);
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
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("faturas_lotes.remover.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				set_time_limit(0);
				ignore_user_abort(1);
				
				$this->removeLote($this->dados_item["id"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}");
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function removeLote($lote_id) {
		
		// Remove faturas
		$this->removeFaturas($lote_id);
		
		// Remove lote
		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_filtros WHERE tab_faturas_lotes_filtros.fal_id = {$lote_id}");
		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_comentarios WHERE tab_faturas_lotes_comentarios.fal_id = {$lote_id}");
		$this->bancodedados->query("DELETE FROM tab_faturas_lotes WHERE tab_faturas_lotes.fal_id = {$lote_id}");
		
	}
	
	function removeFaturas($lote_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				fco_id AS id
			FROM
				tab_faturas_lotes_cobrancas
			WHERE
				fal_id = {$lote_id}");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE fco_id = {$dados["id"]}");
		}
		
		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fal_id = {$lote_id}");
		$this->bancodedados->query("DELETE tab_faturas_itens FROM tab_faturas INNER JOIN tab_faturas_itens ON (tab_faturas.fat_id = tab_faturas_itens.fat_id) WHERE tab_faturas.fal_id = {$lote_id}");
		$this->bancodedados->query("DELETE FROM tab_faturas WHERE tab_faturas.fal_id = {$lote_id}");
		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_clientes WHERE fal_id = {$lote_id}");
		
	}
	
	###############
	### Regerar ###
	###############

	function regerar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("faturas_lotes.regerar.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=regerar&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				set_time_limit(0);
				ignore_user_abort(1);
				
				// Atualiza status
				$this->bancodedados->query("
					UPDATE 
						tab_faturas_lotes 
					SET 
						fal_data_geracao = NOW(), 
						fls_id = 1 
					WHERE 
						fal_id = {$this->dados_item["id"]}");
				
				// Remove faturas
				$this->removeFaturas($this->dados_item["id"]);
				
				// Gera faturas
				$this->geraFaturas($this->dados_item["id"], $_POST["comentario"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	####################
	### Ciclo Gerado ###
	####################

	function cicloAprovado($ciclo, $comentario_usuario = null) {
		
		set_time_limit(0);
		ignore_user_abort(1);
		ini_set("default_socket_timeout", 3600);
		
		$lote_id = $this->buscaLotePorCiclo($ciclo, 2);
		
		if ($lote_id) {
			
			$retorno = $this->pegaDadosCicloRemoto($ciclo);
			
			$xml = simplexml_load_string($retorno);
			
			for ($i = 0; $i < count($xml->customer); $i++) {
				if (is_array($xml->customer[$i]->invoice)) {
					for ($j = 0; $j < count($xml->customer[$i]->invoice); $j++) {
						$this->atualizaFatura($lote_id, $xml->customer[$i]->invoice[$j]->invoice_id, $xml->customer[$i]->invoice[$j]->invoice_number_id, $xml->customer[$i]->invoice[$j]->invoice_md5);
					}
				} else {
					$this->atualizaFatura($lote_id, $xml->customer[$i]->invoice->invoice_id, $xml->customer[$i]->invoice->invoice_number_id, $xml->customer[$i]->invoice->invoice_md5);
				}
			}
			
			$this->bancodedados->query("
				UPDATE 
					tab_faturas_lotes 
				SET 
					fal_data_aprovacao = NOW(), 
					fls_id = 3 
				WHERE 
					fal_id = {$lote_id}");
			
			$comentario = "<b>Ação:</b> Ciclo aprovado";
			if (trim($comentario_usuario)) {
				$comentario .= "<br><b>Comentário:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($comentario_usuario));
			}
			
			$this->registraComentario($lote_id, $comentario, $_SESSION["usuario"]);
			
		}
		
	}
	
	function atualizaFatura($lote_id, $cobranca_id, $numero, $md5) {
		
		$this->bancodedados->query("
			UPDATE 
				tab_faturas 
			SET 
				fat_numero = '{$numero}', 
				fat_md5 = '{$md5}' 
			WHERE 
				fal_id = {$lote_id} AND 
				coa_id = {$cobranca_id} AND 
				emp_id = 1");
		
	}
	
	function pegaDadosCicloRemoto($ciclo) {
		
		$webservice = new SoapClient($this->webservice);
		$retorno = $webservice->get_invoice_data_string(array("Cycle" => $ciclo));
		
		return $retorno->get_invoice_data_stringResult->any;
		
	}
	
	function pegaFaturaPdf($ciclo, $codigo_cobranca, $serie) {
		
		$webservice = new SoapClient($this->webservice);
		
		$retorno = $webservice->get_invoice_pdf_file_string(array("Cycle" => $ciclo, "invoice_number" => $codigo_cobranca, "serie" => $serie));

		return $retorno->get_invoice_pdf_file_stringResult;
		
	}
	
	function geraNotaCobranca($cobranca_id, $tipo_id, $comentario = null, $autoAprovar = false) {
		
		$data_ciclo = date("Y-m-d");
		$sequencia = $this->buscaSequenciaCiclo($data_ciclo, $tipo_id);
		$ciclo = date("Ymd", strtotime($data_ciclo)).sprintf("%03s", $sequencia);
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_faturas_lotes 
			SET
				fal_ciclo = '{$ciclo}', 
				fal_ciclo_sequencia = {$sequencia}, 
				fal_data_ciclo = '{$data_ciclo}', 
				fal_data_geracao = NOW(), 
				fap_id = {$tipo_id},
				fls_id = 1");
		
		$lote_id = $this->bancodedados->ultimoIdInserido();
		if ($lote_id) {
			
			// Insere Filtros
			$this->bancodedados->query("
				INSERT INTO 
					tab_faturas_lotes_filtros
				SET
					flf_comparacao = '=', 
					flf_valor = {$cobranca_id}, 
					faf_id = 5, 
					fal_id = {$lote_id}");
			
			// Gera Faturas
			$this->geraFaturas($lote_id);
			
			// Processa Lote
			$this->buscaDadosItem($lote_id);
			
			if ($tipo_id == 1) {
				if($comentario) {
					$this->cicloAprovado($this->dados_item["ciclo"], $comentario);
				} else {
					$this->cicloAprovado($this->dados_item["ciclo"], $_POST["comentario"]);
				}
				return true;
			} else {
				if ($this->processarLote($this->dados_item)) {
					$this->aprovarLote($this->dados_item);
					return true;
				}
			}
			
		}
                
                if($autoAprovar) {
                    $queryFaturaLotes = "
                    UPDATE 
                            tab_faturas_lotes 
                    SET 
                            fal_data_aprovacao = NOW(), 
                            fls_id = 3 
                    WHERE 
                            fal_id = {$lote_id}";
                    $this->bancodedados->query($queryFaturaLotes);
                }
		
		return false;
		
	}
	
	function cancelarFatura($fatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(tab_faturas_lotes.fal_data_ciclo, '%Y') AS ano,
				DATE_FORMAT(tab_faturas_lotes.fal_data_ciclo, '%m') AS mes,
				tab_faturas.fat_numero AS numero
			FROM
				tab_faturas
				INNER JOIN tab_faturas_lotes ON (tab_faturas.fal_id = tab_faturas_lotes.fal_id)
			WHERE
				tab_faturas.fat_id = {$fatura_id}");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			// 100 - Cancelada com sucesso
			// 200 - Nota fiscal não encontrada
			// 300 - Nota fiscal já está cancelada
			
			$webservice = new SoapClient($this->webservice);
			$retorno = $webservice->cancel_invoices(array("pAno" => $dados["ano"], "pMes" => $dados["mes"], "pCodNFS" => $dados["numero"]));
			
			return $retorno->cancel_invoicesResult;
			
		}
		
	}

}

?>