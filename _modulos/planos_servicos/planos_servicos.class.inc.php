<?

require_once ("_global/modulo.class.inc.php");

class planos_servicos extends modulo {
	
	var $dados_modulo;
	
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
	
	function quantidadePlanos($servico_id) {
		$query = "SELECT COUNT(*) FROM tab_planos WHERE pls_id = $servico_id";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, 0);
	}
	
	function ehRemovivel() {
		return $this->quantidadePlanos($this->dados_item["id"]) == 0;
	}
	
	function remover() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item && $this->ehRemovivel()) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("planos_servicos.remover.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			} else {
				$query = "
          SELECT
            fop_id AS id,
            (SELECT COUNT(*) FROM rel_fop_cor_estacontido WHERE rel_fop_cor_estacontido.fop_id = tab_formas_pagamento.fop_id) AS utilizados
          FROM 
            tab_formas_pagamento 
          WHERE 
            pls_id = {$_GET["i"]}";
				$query = $this->bancodedados->query($query);
				while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
					if ($dados["utilizados"]) {
						$this->bancodedados->query("UPDATE tab_formas_pagamento SET pls_id = NULL WHERE fop_id = {$dados["id"]}");
					} else {
						$this->bancodedados->query("DELETE FROM tab_formas_pagamento WHERE fop_id = {$dados["id"]}");
					}
				}
				$query = "DELETE FROM tab_classes_fiscais WHERE pls_id = {$_GET["i"]}";
				$query = "DELETE FROM tab_planos_servicos WHERE pls_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
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
			$this->conteudo .= $this->carregaArquivo("planos_servicos.adicionar.inc.php");
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$this->conteudo = str_replace('[botoes]', implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
		} else {
			$parametros = $this->trataParametrosEntrada();
			$query = "
      	INSERT INTO
      		tab_planos_servicos (pls_nome, pls_parametros)
      	VALUES
      		('{$_POST["nome"]}', '$parametros')";
			$this->bancodedados->query($query);
			$id = $this->bancodedados->ultimoIdInserido();
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
		}
	}
	
	function inicializaFiltro($valores = "") {
		// Nome
		$itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_planos_servicos.pls_nome LIKE '%[valor]%')");
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		return $filtro;
	}
	
	##############
	### Listar ###
	##############
	

	function listar() {
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"])
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
	
	function buscaItens($filtro, $pagina) {
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$query = "
    	SELECT
    		pls_id AS id,
    		pls_nome AS nome,
    		pls_parametros as parametros
    	FROM
    		tab_planos_servicos
    	WHERE
    		$filtro->parametros_sql
    	ORDER BY pls_nome";
		$query = $this->bancodedados->query($query);
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes["planos_servicos"]["ver"]) {
				$dados["link"] = "?m=planos_servicos&a=ver&i={$dados["id"]}";
			}
			$dados["parametros"] = @unserialize($dados["parametros"]);
			$dados["aceita_multiplos"] = $dados["parametros"]["aceita_multiplos"] ? "Sim" : "Não";
			$dados["auto_aprovacao_comercial"] = $dados["parametros"]["auto_aprovacao_comercial"] ? "Sim" : "Não";
			$dados["auto_aprovacao_tecnico"] = $dados["parametros"]["auto_aprovacao_tecnico"] ? "Sim" : "Não";
			$itens[] = $dados;
		}
		return array("linhas" => $linhas, "itens" => $itens);
	}
	
	function geraListagem($itens) {
		$cabecalho = array("nome" => "Nome", "aceita_multiplos" => "Aceita múltiplos", "auto_aprovacao_comercial" => "Auto-aprovação comercial", "auto_aprovacao_tecnico" => "Auto-aprovação técnica");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(), null, array());
		return $listagem;
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		return $paginacao;
	}
	
	function ver() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("planos_servicos.ver.inc.php");
			$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[formas_pagamento_listagem]', $this->buscaFormasPagamentoListagem($this->dados_item["formas_pagamento"], false), $this->conteudo);
			$this->conteudo = str_replace('[classificacao_fiscal_listagem]', $this->buscaClassificacaoFiscalListagem($this->dados_item["classificacao_fiscal"], false), $this->conteudo);
			$this->conteudo = str_replace('[multa_maxima]', $this->dados_item["parametros"]["multa_maxima"], $this->conteudo);
			if ($this->dados_item["parametros"]["aceita_multiplos"] == '1') $aplicacoes[] = "Aceita múltiplos";
			if ($this->dados_item["parametros"]["auto_aprovacao_comercial"] == '1') $aplicacoes[] = "Auto-aprovação comercial";
			if ($this->dados_item["parametros"]["auto_aprovacao_tecnico"] == '1') $aplicacoes[] = "Auto-aprovação técnica";
			if ($this->dados_item["parametros"]["com_contrato"] == '1') $aplicacoes[] = "Com contrato";
			if ($this->dados_item["parametros"]["gera_visita"] == '1') $aplicacoes[] = "Gera visita";
			if ($this->dados_item["parametros"]["tem_dominio"] == '1') $aplicacoes[] = "Tem domínio";
			$this->conteudo = str_replace('[opcoes]', @implode("<br>", $aplicacoes), $this->conteudo);
			// Botões
			if ($this->permissoes["planos"]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\'">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
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
				$this->conteudo .= $this->carregaArquivo("planos_servicos.editar.inc.php");
				$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace('[multa_maxima]', $this->formataDinheiro($this->dados_item["parametros"]["multa_maxima"]), $this->conteudo);
				
				$this->conteudo = str_replace('[aceita_multiplos]', $this->geraInput("checkbox", "aceita_multiplos", null, 1, true, null, null, null, null, $this->dados_item["parametros"]["aceita_multiplos"]), $this->conteudo);
				$this->conteudo = str_replace('[auto_aprovacao_comercial]', $this->geraInput("checkbox", "auto_aprovacao_comercial", null, 1, true, null, null, null, null, $this->dados_item["parametros"]["auto_aprovacao_comercial"]), $this->conteudo);
				$this->conteudo = str_replace('[auto_aprovacao_tecnico]', $this->geraInput("checkbox", "auto_aprovacao_tecnico", null, 1, true, null, null, null, null, $this->dados_item["parametros"]["auto_aprovacao_tecnico"]), $this->conteudo);
				$this->conteudo = str_replace('[com_contrato]', $this->geraInput("checkbox", "com_contrato", null, 1, true, null, null, null, null, $this->dados_item["parametros"]["com_contrato"]), $this->conteudo);
				$this->conteudo = str_replace('[gera_visita]', $this->geraInput("checkbox", "gera_visita", null, 1, true, null, null, null, null, $this->dados_item["parametros"]["gera_visita"]), $this->conteudo);
				$this->conteudo = str_replace('[tem_dominio]', $this->geraInput("checkbox", "tem_dominio", null, 1, true, null, null, null, null, $this->dados_item["parametros"]["tem_dominio"]), $this->conteudo);
				
				$this->conteudo = str_replace('[formas_pagamento_listagem]', $this->buscaFormasPagamentoListagem($this->dados_item["formas_pagamento"], true), $this->conteudo);
				$this->conteudo = str_replace('[formas_pagamento_lista]', sizeof($this->dados_item["formas_pagamento"]) ? @serialize($this->dados_item["formas_pagamento"]) : "", $this->conteudo);

				$this->conteudo = str_replace('[classificacao_fiscal_listagem]', $this->buscaClassificacaoFiscalListagem($this->dados_item["classificacao_fiscal"], true), $this->conteudo);
				$this->conteudo = str_replace('[classificacao_fiscal_lista]', sizeof($this->dados_item["classificacao_fiscal"]) ? @serialize($this->dados_item["classificacao_fiscal"]) : "", $this->conteudo);
				
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->ehRemovivel())
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
			} else {
				// Atualiza Formas de Pagamento
				$formas_pagamento = unserialize(stripslashes($_POST["formas_pagamento_lista"]));
				if ($formas_pagamento) {
					foreach ($formas_pagamento as $forma_pagamento) {
						$forma_pagamento["entrada"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["entrada"]), 10);
						$forma_pagamento["parcelas"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["parcelas"]), 10);
						$forma_pagamento["valor_parcela"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["valor_parcela"]), 10);
						if (!$forma_pagamento["parcelas"] || !$forma_pagamento["valor_parcela"]) {
							$forma_pagamento["parcelas"] = 0;
							$forma_pagamento["valor_parcela"] = 0;
						}
						$forma_pagamento["forma"] = $forma_pagamento["parcelas"] ? $forma_pagamento["forma"] : "NULL";
						$forma_pagamento["multa"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["multa"]), 10);
						$forma_pagamento["fidelidade"] = intval(preg_replace("/[^0-9]/", "", $forma_pagamento["fidelidade"]), 10);
						if (!$forma_pagamento["multa"] || !$forma_pagamento["fidelidade"]) {
							$forma_pagamento["multa"] = 0;
							$forma_pagamento["fidelidade"] = 0;
						}
						if ($forma_pagamento["id"]) {
							$query = "UPDATE tab_formas_pagamento SET fop_entrada = {$forma_pagamento["entrada"]}, fop_parcelas = {$forma_pagamento["parcelas"]}, fop_valor_parcela = {$forma_pagamento["valor_parcela"]}, fpf_id = {$forma_pagamento["forma"]}, fop_multa = {$forma_pagamento["multa"]}, fop_fidelidade = {$forma_pagamento["fidelidade"]}, fpc_id = {$forma_pagamento["categoria"]} WHERE fop_id = {$forma_pagamento["id"]}";
							$this->bancodedados->query($query);
							$validos[] = $forma_pagamento["id"];
						} else {
							$query = "INSERT INTO tab_formas_pagamento (fop_entrada, fop_parcelas, fop_valor_parcela, fpf_id, fop_multa, fop_fidelidade, fpc_id, pls_id) VALUES ({$forma_pagamento["entrada"]}, {$forma_pagamento["parcelas"]}, {$forma_pagamento["valor_parcela"]}, {$forma_pagamento["forma"]}, {$forma_pagamento["multa"]}, {$forma_pagamento["fidelidade"]}, {$forma_pagamento["categoria"]}, {$_GET["i"]})";
							$this->bancodedados->query($query);
							$validos[] = $this->bancodedados->ultimoIdInserido();
						}
					}
				}
				$query = "
          SELECT
            fop_id AS id,
            (SELECT COUNT(*) FROM rel_fop_cor_estacontido WHERE rel_fop_cor_estacontido.fop_id = tab_formas_pagamento.fop_id) AS utilizados
          FROM 
            tab_formas_pagamento 
          WHERE 
            pls_id = {$_GET["i"]}" . ($validos ? " AND
            fop_id NOT IN (" . implode(",", $validos) . ")" : "");
				$query = $this->bancodedados->query($query);
				while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
					if ($dados["utilizados"]) {
						$this->bancodedados->query("UPDATE tab_formas_pagamento SET pls_id = NULL WHERE fop_id = {$dados["id"]}");
					} else {
						$this->bancodedados->query("DELETE FROM tab_formas_pagamento WHERE fop_id = {$dados["id"]}");
					}
				}
				// Atualiza Classificação Fiscal
				$classificacao_fiscal = unserialize(stripslashes($_POST["classificacao_fiscal_lista"]));
				$validos = array();
				if ($classificacao_fiscal) {
					foreach ($classificacao_fiscal as $dados) {
						$dados["taxa"] = floatval(str_replace(",", ".", preg_replace("/[^0-9,.]/", "", $dados["taxa"])));
						if ($dados["id"]) {
							$query = "UPDATE tab_classes_fiscais SET cft_id = {$dados["tipo_id"]}, clf_taxa = {$dados["taxa"]} WHERE clf_id = {$dados["id"]}";
							$this->bancodedados->query($query);
							$validos[] = $dados["id"];
						} else {
							$query = "INSERT INTO tab_classes_fiscais (cft_id, clf_taxa, pls_id) VALUES ('{$dados["tipo_id"]}', {$dados["taxa"]}, {$_GET["i"]})";
							$this->bancodedados->query($query);
							$validos[] = $this->bancodedados->ultimoIdInserido();
						}
					}
				}
				if (count($validos)) {
					$this->bancodedados->query("DELETE FROM tab_classes_fiscais WHERE pls_id = {$_GET["i"]} AND clf_id NOT IN (" . implode(",", $validos) . ")");
				} else {
					$this->bancodedados->query("DELETE FROM tab_classes_fiscais WHERE pls_id = {$_GET["i"]}");
				}
				// Atualiza Serviço
				$parametros = $this->trataParametrosEntrada();
				$query = "UPDATE tab_planos_servicos SET pls_nome = '{$_POST["nome"]}', pls_parametros = '$parametros' WHERE pls_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}
	
	function buscaDadosItem($id) {
		$query = "
			SELECT
				pls_id AS id,
				pls_nome AS nome,
				pls_parametros AS parametros
			FROM
				tab_planos_servicos
			WHERE
				pls_id = $id";
		$query = $this->bancodedados->query($query);
		$this->dados_item = $this->bancodedados->resultadoArray($query);
		if ($this->dados_item) {
			$this->dados_item["parametros"] = @unserialize($this->dados_item["parametros"]);
			$this->dados_item["formas_pagamento"] = $this->buscaFormasPagamento($id);
			$this->dados_item["classificacao_fiscal"] = $this->buscaClassificacaoFiscal($id);
		}
	}
	
	function buscaFormasPagamento($servico_id) {
		$query = "
      SELECT
        fop_id AS id,
        fop_entrada AS entrada,
        fop_parcelas AS parcelas,
        fop_valor_parcela AS valor_parcela,
        fop_multa AS multa,
        fop_fidelidade AS fidelidade,
        fpc_id AS categoria,
        fpf_id AS forma
      FROM
        tab_formas_pagamento
      WHERE
        pls_id = $servico_id
      ORDER BY
        fpc_id,
        fop_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formas_pagamento[] = $dados;
		}
		return $formas_pagamento;
	}
	
	
	function buscaDescricaoClasseFiscal(){
		$query = "
      SELECT
        cft_id AS id,
        CONCAT(tab_classes_fiscais_tipos.cft_sigla, ' - ' , tab_classes_fiscais_tipos.cft_descricao) AS nome
      FROM
				tab_classes_fiscais_tipos
			ORDER BY
			  tab_classes_fiscais_tipos.cft_sigla";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		return $tipos;
	}
	
	function buscaClassificacaoFiscal($servico_id) {
		$query = "
      SELECT
        CONCAT(tab_classes_fiscais_tipos.cft_sigla, ' - ' , tab_classes_fiscais_tipos.cft_descricao) AS descricao,
        tab_classes_fiscais.cft_id AS tipo_id,
        tab_classes_fiscais.clf_taxa AS taxa
      FROM
        tab_classes_fiscais
        INNER JOIN tab_classes_fiscais_tipos ON (tab_classes_fiscais.cft_id = tab_classes_fiscais_tipos.cft_id)
      WHERE
        tab_classes_fiscais.pls_id = $servico_id
      ORDER BY
        tab_classes_fiscais_tipos.cft_sigla";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$classes[] = $dados;
		}
		return $classes;
	}
	
	function buscaClasseFiscalTipo($tipo_id) {
		$query = "
      SELECT
        CONCAT(tab_classes_fiscais_tipos.cft_sigla, ' - ' , tab_classes_fiscais_tipos.cft_descricao) AS descricao
      FROM
        tab_classes_fiscais_tipos
      WHERE
        tab_classes_fiscais_tipos.cft_id = $tipo_id";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, 0);
	}
	
	function trataParametrosEntrada() {
		
		$parametros = array();
		
		$parametros["aceita_multiplos"] = $_POST["aceita_multiplos"] ? 1 : 0;
		$parametros["auto_aprovacao_comercial"] = $_POST["auto_aprovacao_comercial"] ? 1 : 0;
		$parametros["auto_aprovacao_tecnico"] = $_POST["auto_aprovacao_tecnico"] ? 1 : 0;
		$parametros["com_contrato"] = $_POST["com_contrato"] ? 1 : 0;
		$parametros["gera_visita"] = $_POST["gera_visita"] ? 1 : 0;
		$parametros["tem_dominio"] = $_POST["tem_dominio"] ? 1 : 0;
		$parametros["multa_maxima"] = intval($_POST["multa_maxima"], 10);
		
		return serialize($parametros);
	}
	
	############################
	### Formas de Pagamento ####
	############################
	
	function buscaFormasPagamentoListagem($dados = null, $editavel = true) {
		if ($dados) {
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarFormaPagamento(this, event, " . ($i + 1) . ")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerFormaPagamento(\'' . ($i + 1) . '\')">';
				}
				$dados[$i]["categoria"] = $this->buscaFormasPagamentosCategoria($dados[$i]["categoria"]);
				$dados[$i]["entrada"] = $dados[$i]["entrada"] > 0 ? $this->formataDinheiro($dados[$i]["entrada"]) : "";
				$dados[$i]["parcelas"] = $dados[$i]["parcelas"] > 0 ? $dados[$i]["parcelas"] : "";
				$dados[$i]["valor_parcela"] = $dados[$i]["valor_parcela"] > 0 ? $this->formataDinheiro($dados[$i]["valor_parcela"]) : "";
				$dados[$i]["forma"] = $this->buscaFormasPagamentosForma($dados[$i]["forma"]);
				$dados[$i]["multa"] = $dados[$i]["multa"] > 0 ? $this->formataDinheiro($dados[$i]["multa"]) : "";
				$dados[$i]["fidelidade"] = $dados[$i]["fidelidade"] > 0 ? $dados[$i]["fidelidade"] : "";
			}
		} else {
			$dados[] = array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponível</td></tr></table>');
		}
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaFormasPagamentoAdicionar()), $dados);
		}
		return $this->geraFormasPagamentoListagem($dados, $editavel);
	}
	
	function geraFormasPagamentoListagem($dados, $editavel = true) {
		if ($editavel) {
			$cabecalho = array("categoria" => "Categoria", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Valor Parcelas", "forma" => "Forma", "multa" => "Multa", "fidelidade" => "Fidelidade", "botoes" => "");
		} else {
			$cabecalho = array("categoria" => "Categoria", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Valor Parcelas", "forma" => "Forma", "multa" => "Multa", "fidelidade" => "Fidelidade");
		}
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, null, null, array("left", "right", "right", "right", "center", "right", "right"));
		return $listagem->conteudo;
	}
	
	function buscaFormasPagamentosCategoria($categoria_id) {
		$query = "
      SELECT
        fpc_nome AS nome
      FROM
        tab_formas_pagamento_categorias
      WHERE
        fpc_id = $categoria_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["nome"];
		}
	}
	
	function buscaFormasPagamentosForma($forma_id) {
		if (!$forma_id) {
			return "";
		}
		$query = "
      SELECT
        fpf_nome AS nome
      FROM
        tab_formas_pagamento_formas
      WHERE
        fpf_id = $forma_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["nome"];
		}
	}
	
	function buscaFormasPagamentoAdicionar() {
		$adicionar["categoria"] = $this->geraSelect($this->buscaFormasPagamentosCategorias(), null, true, "formas_pagamento_categoria", "160px");
		$adicionar["entrada"] = '<input name="formas_pagamento_entrada" id="formas_pagamento_entrada" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:90px;text-align:right">';
		$adicionar["parcelas"] = '<input name="formas_pagamento_parcelas" id="formas_pagamento_parcelas" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:60px;text-align:right">';
		$adicionar["valor_parcela"] = '<input name="formas_pagamento_valor_parcela" id="formas_pagamento_valor_parcela" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:90px;text-align:right">';
		$adicionar["forma"] = $this->geraSelect($this->buscaFormasPagamentosFormas(), null, true, "formas_pagamento_forma", "100px");
		$adicionar["multa"] = '<input name="formas_pagamento_multa" id="formas_pagamento_multa" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:90px;text-align:right">';
		$adicionar["fidelidade"] = '<input name="formas_pagamento_fidelidade" id="formas_pagamento_fidelidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:60px;text-align:right">';
		$adicionar["botoes"] = '<input type="button" name="formas_pagamento_adicionar" id="formas_pagamento_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="formaPagamentoAdicionar()">';
		return $adicionar;
	}
	
	function buscaFormasPagamentosFormas() {
		$query = "
      SELECT
        fpf_id AS id,
        fpf_nome AS nome
      FROM
        tab_formas_pagamento_formas
      ORDER BY
        nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		return $tipos;
	}
	
	function buscaFormasPagamentosCategorias() {
		$query = "
      SELECT
        fpc_id AS id,
        fpc_nome AS nome
      FROM
        tab_formas_pagamento_categorias
      ORDER BY
        nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		return $tipos;
	}
	
	#############################
	### Classificacao Fiscal ####
	#############################
	
	function buscaClassificacaoFiscalListagem($dados = null, $editavel = true) {
		if ($dados) {
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarClassificacaoFiscal(this, event, " . ($i + 1) . ")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerClassificacaoFiscal(\'' . ($i + 1) . '\')">';
				}
				$dados[$i]["taxa"] = number_format($dados[$i]["taxa"], 2, ",", ".");
				if (!$dados[$i]["descricao"]) {
					$dados[$i]["descricao"] = $this->buscaClasseFiscalTipo($dados[$i]["tipo_id"]);
				}
			}
		} else {
			$dados[] = array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponível</td></tr></table>');
		}
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaClassificacaoFiscalAdicionar()), $dados);
		}
		return $this->geraClassificacaoFiscalListagem($dados, $editavel);
	}
	
	function geraClassificacaoFiscalListagem($dados, $editavel = true) {
		$cabecalho = array( "descricao" => "Tipo", "taxa" => "Taxa (%)");
		if ($editavel) {
			$cabecalho["botoes"] = "";
		}
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, null, null, null, array("left", "right", "right", "center"));
		return $listagem->conteudo;
	}
	
	function buscaClassificacaoFiscalAdicionar() {
		$adicionar["descricao"] = $this->geraSelect($this->buscaDescricaoClasseFiscal(), null, true, "classificacao_fiscal_tipo", "593px");
		$adicionar["taxa"] = '<input name="classificacao_fiscal_taxa" id="classificacao_fiscal_taxa" type="text" class="inputBrancoAzulEscuro11" style="width:90px;text-align:right" onKeyPress="return formataNumeroDecimal(this, event)" onBlur="return formataNumeroDecimal(this, event)">';
		$adicionar["botoes"] = '<input type="button" name="classificacao_fiscal_adicionar" id="classificacao_fiscal_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="classificacaoFiscalAdicionar()">';
		return $adicionar;
	}
	
}

     
