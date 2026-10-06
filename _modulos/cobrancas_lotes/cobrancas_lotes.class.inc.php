<?

require_once("_global/modulo.class.inc.php");

class cobrancas_lotes extends modulo {
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		$this->buscaDadosModulo("cobrancas_lotes");
		
		if ($executar) {
			$this->buscaDadosAcao($_GET["a"] ? ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"] ? "editar" : $_GET["a"]) : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
	
	function buscaDadosItem($cobranca_lote_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_lotes.col_id AS id,
				tab_cobrancas_lotes.col_nome AS nome,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m/%Y') AS vencimento,
				tab_cobrancas_lotes.col_parametros AS parametros,
				tab_cobrancas_lotes.col_sincronizado AS sincronizado,
				tab_cobrancas_lotes.col_gerado AS gerado,
				tab_cobrancas_lotes_status.cla_id AS status_id,
				tab_cobrancas_lotes_status.cla_nome AS status_nome,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cft_id AS formato_tipo_id,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				tab_cobrancas_formatos.cof_parametros AS formato_parametros
			FROM
				tab_cobrancas_formatos
				INNER JOIN tab_cobrancas_lotes ON (tab_cobrancas_formatos.cof_id = tab_cobrancas_lotes.cof_id)
				INNER JOIN tab_cobrancas_lotes_status ON (tab_cobrancas_lotes.cla_id = tab_cobrancas_lotes_status.cla_id)
			WHERE
				tab_cobrancas_lotes.col_id = $cobranca_lote_id");
		
		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->dados_item["parametros"] = unserialize($this->dados_item["parametros"]);
			$this->dados_item["formato_parametros"] = unserialize($this->dados_item["formato_parametros"]);
			
			if (! $this->dados_item["sincronizado"] || $this->dados_item["status_id"] < 4) {
				$this->dados_item["status_nome"] .= ($this->dados_item["sincronizado"]) ? " / Sincronizado" : " / <font color=\"red\">Não Sincronizado</font>";
			}
			
			if (! $this->dados_item["gerado"]) {
				$this->dados_item["status_nome"] .= " / <font color=\"red\">Gerando...</font>";
			}
			
			$this->dados_item["comentarios"] = $this->buscaComentarios($cobranca_lote_id);
		}
		
	}
	
	function buscaDadosCobrancaFormato($cobranca_formato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_formatos.cof_id AS id,
				tab_cobrancas_formatos.cof_nome AS nome,
				tab_cobrancas_formatos.cof_parametros AS parametros,
				tab_cobrancas_formatos_status.cfs_id AS status_id,
				tab_cobrancas_formatos_status.cfs_nome AS status_nome
			FROM
				tab_cobrancas_formatos
				INNER JOIN tab_cobrancas_formatos_status ON (tab_cobrancas_formatos.cfs_id = tab_cobrancas_formatos_status.cfs_id)
			WHERE
				tab_cobrancas_formatos.cof_id = $cobranca_formato_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
		
	}
	
	###################
	### Busca Itens ###
	###################
	
	function buscaFormatos($formato = '%') {
		
		$formatos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cof_id AS id,
				cof_nome AS nome,
				cof_parametros AS parametros
			FROM
				tab_cobrancas_formatos
			WHERE
				cfs_id LIKE '$formato'
			ORDER BY
				cof_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formatos[$dados["id"]] = $dados["nome"];
		}
		
		return $formatos;
		
	}
	
	function buscaFormatosAdicionaveis() {
		
		$formatos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_formatos.cof_id AS id,
				tab_cobrancas_formatos.cof_nome AS nome
			FROM
				tab_cobrancas_formatos
				INNER JOIN tab_clientes ON (tab_clientes.cof_id = tab_cobrancas_formatos.cof_id)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.cof_id = tab_cobrancas_formatos.cof_id AND tab_cobrancas_lotes.cla_id IN (1, 2))
			WHERE
				(tab_cobrancas_formatos.cfs_id = 1 OR tab_cobrancas_formatos.cof_id IN (18, 19))
			GROUP BY
				id
			ORDER BY
				cof_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formatos[$dados["id"]] = $dados["nome"];
		}
		
		return $formatos;
		
	}
	
	function buscaStatus() {
		
		$status = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cla_id AS id,
				cla_nome AS nome
			FROM
				tab_cobrancas_lotes_status
			ORDER BY
				cla_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		
		return $status;
		
	}
	
	function buscaErroAtivacao() {
		
		$vencimento = strtotime(substr($this->dados_item["vencimento"], 3, 4) . "-" . substr($this->dados_item["vencimento"], 0, 2) . "-01");
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(ajf_id)
			FROM
				tab_ajustes_financeiros
			WHERE
				afs_id IN (1, 2) AND
				('$vencimento' BETWEEN ajf_competencia_inicio AND ajf_competencia_termino OR
				(ajf_competencia_inicio <= '$vencimento' AND ajf_competencia_termino IS NULL))");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaCobrancasEnviaveis($cobranca_lote_id) {
		
		$cobrancas = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.cli_id AS cliente_id,
				tab_cobrancas.coa_codigo AS codigo,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas_formatos.cof_nome AS formato,
				tab_clientes.clo_id AS origem_id
			FROM
				tab_clientes
				INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)
				INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				col_id = $cobranca_lote_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$cobrancas[] = $dados;
		}
		
		return $cobrancas;
		
	}
	
	###################
	### Comentários ###
	###################
	
	function registraComentario($cobranca_lote_id, $conteudo, $responsavel) {
		
		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_lotes_comentarios
			SET
				clo_datahora = NOW(),
				col_id = '$cobranca_lote_id',
				clo_conteudo = '$conteudo',
				usu_id = '$responsavel'");
		
	}
	
	function geraComentariosListagem($comentarios) {
		
		$cabecalho = array (
			"detalhamento" => "Detalhamento"
		);
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
		
		return $listagem->conteudo;
		
	}
	
	function buscaComentarios($cobranca_lote_id) {
		
		$comentarios = array();
		
		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_cobrancas_lotes_comentarios.clo_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_cobrancas_lotes_comentarios.clo_conteudo AS conteudo
			FROM
				tab_cobrancas_lotes_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_cobrancas_lotes_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_cobrancas_lotes_comentarios.col_id = $cobranca_lote_id
			ORDER BY
				tab_cobrancas_lotes_comentarios.clo_datahora ASC,
				tab_cobrancas_lotes_comentarios.clo_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
			$dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
			$dados["detalhamento"] .= "{$dados["conteudo"]}<br>";
			
			$comentarios[] = $dados;
			
		}
		
		return $comentarios;
		
	}
	
	#############
	### Geral ###
	#############
	
	function geraParametrosListagem($parametros) {
		
		$parametros = $this->trataParametrosVisualizavel($parametros);
		$parametros_1 = array();
		$parametros_2 = array();
		
		if ($parametros) {
			$i = 1;
			$j = ceil(count($parametros) / 2);
			foreach ($parametros as $chave => $parametros) {
				if ($i <= $j) {
					$parametros_1[$chave] = $parametros;
				} else {
					$parametros_2[$chave] = $parametros;
				}
				$i++;
			}
		}
		
		// Gera Listagens
		$cabecalho = array (
			"descricao" => "Descrição",
			"valor_formatado" => "Valor"
		);
		
		$listagem_1 = controle::instanciaObjeto("listagem", $cabecalho, $parametros_1);
		$listagem_2 = controle::instanciaObjeto("listagem", $cabecalho, $parametros_2);
		
		return '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td valign="top" width="50%">' . $listagem_1->conteudo . '</td><td width="4">&nbsp;</td><td valign="top" width="50%">' . $listagem_2->conteudo . '</td></tr></table>';
		
	}
	
	function trataParametrosVisualizavel($parametros) {
		
		$parametros_tratados = array ();
		
		foreach ($parametros as $chave => $parametro) {
			if (is_array($parametro)) {
				$parametros_tratados[$chave] = $parametro;
			}
		}
		
		return $parametros_tratados;
		
	}
	
	function geraDetalhamentoListagem($detalhamentos) {
		
		$cabecalho = array (
			"vencimento" => "Vencimento",
			"quantidade" => "Quantidade",
			"valor_original" => "Valor Original",
			"valor_bonificacao" => "Valor Bonificação",
			"valor_final" => "Valor Final"
		);
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, ($detalhamentos) ? $detalhamentos : array (), 0, false, array (0, 100, 150, 150, 150), null, array("center", "right", "right", "right", "right"));
		
		return $listagem->conteudo;
		
	}
	
	function buscaCobrancaLoteExistente($formato_id, $vencimento) {
		
		$query = $this->bancodedados->query("
			SELECT
				col_id
			FROM
				tab_cobrancas_lotes
			WHERE
				cof_id = $formato_id AND
				DATE_FORMAT(col_vencimento, '%m/%Y') = '$vencimento'");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaDetalhamentoLote($lote_id) {
		
		$detalhamento = array();
		
		$quantidade_total = 0;
		$valor_original_total = 0;
		$valor_bonificacao_total = 0;
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id) AS quantidade,
				SUM(coa_valor_original) AS valor_original,
				SUM(coa_valor_bonificacao) AS valor_bonificacao,
				DATE_FORMAT(coa_data_vencimento, '%d/%m/%Y') AS vencimento
			FROM
				tab_cobrancas
			WHERE
				col_id = $lote_id
			GROUP BY
				vencimento");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$quantidade_total += $dados["quantidade"];
			$valor_bonificacao_total += $dados["valor_bonificacao"];
			$valor_original_total += $dados["valor_original"];
			
			$dados["quantidade"] = number_format($dados["quantidade"], 0, ",", ".");
			$dados["valor_final"] = $this->formataDinheiro($dados["valor_original"] - $dados["valor_bonificacao"], 'R$', true);
			$dados["valor_original"] = $this->formataDinheiro($dados["valor_original"], 'R$', true);
			$dados["valor_bonificacao"] = $this->formataDinheiro($dados["valor_bonificacao"]);
			
			if ($this->permissoes["cobrancas"]["buscar"]) {
				$dados["link"] = "?m=cobrancas&a=buscar&f[lote]=$lote_id&f[data_vencimento]={$dados["vencimento"]}";
			}
			
			$detalhamento[] = $dados;
			
		}
		
		$detalhamento[] = array (
			"vencimento" => "<b>Total</b>",
			"quantidade" => "<b>" . number_format($quantidade_total, 0, ",", ".") . "</b>",
			"valor_original" => "<b>" . $this->formataDinheiro($valor_original_total) . "</b>",
			"valor_bonificacao" => "<b>" . $this->formataDinheiro($valor_bonificacao_total) . "</b>",
			"valor_final" => "<b>" . $this->formataDinheiro($valor_original_total - $valor_bonificacao_total, 'R$', true) . "</b>"
		);
		
		return $detalhamento;
		
	}
	
	###########
	### Ver ###
	###########
	
	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["sincronizar"] && $this->dados_item["gerado"] && ! $this->dados_item["sincronizado"] && in_array($this->dados_item["status_id"], array (1, 2, 3))) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=sincronizar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["aprovar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && in_array($this->dados_item["status_id"], array (1, 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && in_array($this->dados_item["status_id"], array (1, 2))) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" value="Reprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["gerado"] && in_array($this->dados_item["status_id"], array (1, 2, 3))) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["exportar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && ($this->dados_item["formato_parametros"]["exportavel"] || $this->dados_item["formato_id"] == 5) && $this->dados_item["status_id"] == 4) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=exportar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["enviar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && $this->dados_item["formato_parametros"]["enviavel"] && $this->dados_item["status_id"] == 6) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=enviar&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.ver.inc.php");
			
			$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[parametros]', $this->geraParametrosListagem($this->dados_item["parametros"]), $this->conteudo);
			$this->conteudo = str_replace('[detalhamento]', $this->geraDetalhamentoListagem($this->buscaDetalhamentoLote($this->dados_item["id"])), $this->conteudo);
			$this->conteudo = str_replace('[vencimento]', $this->dados_item["vencimento"], $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
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
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="editar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["sincronizar"] && $this->dados_item["gerado"] && ! $this->dados_item["sincronizado"] && in_array($this->dados_item["status_id"], array (1, 2, 3))) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=sincronizar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["aprovar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && in_array($this->dados_item["status_id"], array (1, 3))) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && in_array($this->dados_item["status_id"], array (1, 2))) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" value="Reprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["gerado"] && in_array($this->dados_item["status_id"], array (1, 2, 3))) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["exportar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && ($this->dados_item["formato_parametros"]["exportavel"] || $this->dados_item["formato_id"] == 5)) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=exportar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["enviar"] && $this->dados_item["gerado"] && $this->dados_item["sincronizado"] && $this->dados_item["formato_parametros"]["enviavel"] && $this->dados_item["status_id"] == 6) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=enviar&i=' . $this->dados_item["id"] . '\';">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.editar.inc.php");
				
				$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
				$this->conteudo = str_replace('[parametros]', $this->geraParametrosListagem($this->dados_item["parametros"]), $this->conteudo);
				$this->conteudo = str_replace('[detalhamento]', $this->geraDetalhamentoListagem($this->buscaDetalhamentoLote($this->dados_item["id"])), $this->conteudo);
				$this->conteudo = str_replace('[vencimento]', $this->dados_item["vencimento"], $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$comentario = "<b>Ação:</b> Editado";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($_GET["i"], $comentario, $_SESSION["usuario"]);
				
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
		
		if (! $_POST) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.adicionar.inc.php");
			
			$this->conteudo = str_replace('[formato]', $this->geraSelect($this->buscaFormatosAdicionaveis(), null, true, "formato", "100%", "return mudaCobrancaFormato(this.form.formato.value, this.form.vencimento.value)"), $this->conteudo);
			$this->conteudo = str_replace('[botoes]', implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
			
		} else {
			
			$dados_formato = $this->buscaDadosCobrancaFormato($_POST["formato"]);
			
			unset($dados_formato["parametros"]["exportavel"]);
			unset($dados_formato["parametros"]["enviavel"]);
			
			$parametros = addslashes(serialize($dados_formato["parametros"]));
			
			$_POST["vencimento"] = substr($_POST["vencimento"], 3, 4) . "-" . substr($_POST["vencimento"], 0, 2) . "-" . "01";
			
			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_lotes
				SET
					col_nome = '{$_POST["nome"]}',
					col_parametros = '$parametros',
					col_vencimento = '{$_POST["vencimento"]}',
					col_data_geracao = NOW(),
					col_sincronizado = 1,
					cof_id = {$_POST["formato"]},
					cla_id = 1");
			
			$id = $this->bancodedados->ultimoIdInserido();
			
			$this->bancodedados->query("
				UPDATE
					tab_cron
				SET
					cro_ativo = 1,
					cro_ultima_execucao_inicio = NULL,
					cro_ultima_execucao_termino = NULL
				WHERE
					cro_classe = 'cobrancas_geracao' AND
					cro_acao = 'cobrancasGerar' AND
					cro_ativo = 0");
			
			$comentario = "<b>Ação:</b> Adicionado (Geração Agendada)";
			if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
			$this->registraComentario($id, $comentario, $_SESSION["usuario"]);
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
			
		}
		
	}
	
	#############
	### Gerar ###
	#############
	
	function gerar() {
		
		if (! $_POST) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.gerar.inc.php");
			
			$this->conteudo = str_replace('[formatos]', $this->geraCheckBoxes("formatos", $this->buscaFormatosAdicionaveis()), $this->conteudo);
			$this->conteudo = str_replace('[botoes]', implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=gerar", $this->conteudo);
			
		} else {
			
			$_POST["vencimento"] = substr($_POST["vencimento"], 3, 4) . "-" . substr($_POST["vencimento"], 0, 2) . "-" . "01";
			
			$meses = array (
				1 => "Janeiro",
				2 => "Fevereiro",
				3 => "Março",
				4 => "Abril",
				5 => "Maio",
				6 => "Junho",
				7 => "Julho",
				8 => "Agosto",
				9 => "Setembro",
				10 => "Outubro",
				11 => "Novembro",
				12 => "Dezembro"
			);
			
			$nome_data = $meses[intval(substr($_POST["vencimento"], 5, 2))] . "/" . substr($_POST["vencimento"], 0, 4);
			
			foreach ($_POST["formatos"] as $formato_id) {
				
				$dados_formato = $this->buscaDadosCobrancaFormato($formato_id);
				
				unset($dados_formato["parametros"]["exportavel"]);
				unset($dados_formato["parametros"]["enviavel"]);
				
				$parametros = addslashes(@serialize($dados_formato["parametros"]));
				
				$nome = $dados_formato["nome"] . " - " . $nome_data;
				
				$this->bancodedados->query("
					INSERT INTO
						tab_cobrancas_lotes
					SET
						col_nome = '$nome',
						col_parametros = '$parametros',
						col_vencimento = '{$_POST["vencimento"]}',
						col_data_geracao = NOW(),
						col_sincronizado = 1,
						cof_id = $formato_id,
						cla_id = 1");
				
				$id = $this->bancodedados->ultimoIdInserido();
				
				$comentario = "<b>Ação:</b> Adicionado (Geração Agendada)";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($id, $comentario, $_SESSION["usuario"]);
				
			}
			
			$this->bancodedados->query("
				UPDATE
					tab_cron
				SET
					cro_ativo = 1,
					cro_ultima_execucao_inicio = NULL,
					cro_ultima_execucao_termino = NULL
				WHERE
					cro_classe = 'cobrancas_geracao' AND
					cro_acao = 'cobrancasGerar' AND
					cro_ativo = 0");
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
			
		}
		
	}
	
	###############
	### Aprovar ###
	###############
	
	function aprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.aprovar.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=aprovar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("UPDATE tab_cobrancas_lotes SET cla_id = 2 WHERE col_id = {$_GET["i"]}");
				
				
				$comentario = "<b>Ação:</b> Aprovado";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($_GET['i'], $comentario, $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
	
	################
	### Reprovar ###
	################
	
	function reprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.reprovar.inc.php");
				
				$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=reprovar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("UPDATE tab_cobrancas_lotes SET cla_id = 3 WHERE col_id = {$_GET["i"]}");
				
				$comentario = "<b>Ação:</b> Reprovado";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($_GET['i'], $comentario, $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
	
	##############
	### Ativar ###
	##############
	
	function ativar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (! $this->buscaErroAtivacao()) {
				
				if (! $_POST) {
					
					$botoes = array();
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					
					$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.ativar.inc.php");
					
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
					
				} else {
					
					$status_id = $this->dados_item["formato_parametros"]["enviavel"] ? 6 : 4;
					
					$this->bancodedados->query("UPDATE tab_cobrancas_lotes SET cla_id = $status_id, col_data_ativacao = NOW() WHERE col_id = {$_GET["i"]}");
					
					if ($this->dados_item["formato_parametros"]["sequencial"]) {
						
						$this->dados_item["formato_parametros"]["sequencial"]["valor"] += 1;
						$this->dados_item["formato_parametros"]["sequencial"]["valor_formatado"] = sprintf("%06s", $this->dados_item["formato_parametros"]["sequencial"]["valor"]);
						
						$parametros_formato = serialize($this->dados_item["formato_parametros"]);
						
						$this->bancodedados->query("UPDATE tab_cobrancas_formatos SET cof_parametros = '$parametros_formato' WHERE cof_id = {$this->dados_item["formato_id"]}");
						
					}
					
					
					$comentario = "<b>Ação:</b> Ativar";
					if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
					$this->registraComentario($_GET['i'], $comentario, $_SESSION["usuario"]);
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id=' . $_GET["i"]);
					
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
					
				}
				
			} else {
				
				$botoes = array();
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.erro.inc.php");
				
				$this->conteudo = str_replace("[mensagem]", "Não é possivel ativar este lote pois existem Ajustes Financeiros desta competência aguardando ativação!", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}'&a=ativar&i={$_GET["i"]}", $this->conteudo);
				
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
		
		if ($this->dados_item && in_array($this->dados_item["status_id"], array (1, 2, 3))) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.remover.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("DELETE tab_cobrancas_detalhes FROM tab_cobrancas INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id) WHERE col_id = {$_GET["i"]} AND cod_pai IS NOT NULL");
				$this->bancodedados->query("DELETE tab_cobrancas_detalhes FROM tab_cobrancas INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id) WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE tab_cobrancas_remessas_itens FROM tab_cobrancas INNER JOIN tab_cobrancas_remessas_itens ON (tab_cobrancas.coa_id = tab_cobrancas_remessas_itens.coa_id) WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE tab_cobrancas_comentarios FROM tab_cobrancas INNER JOIN tab_cobrancas_comentarios ON (tab_cobrancas.coa_id = tab_cobrancas_comentarios.coa_id) WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_cobrancas WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_cobrancas_lotes_comentarios WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_cobrancas_lotes WHERE col_id = {$_GET["i"]}");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
	
	###################
	### Sincronizar ###
	###################
	
	function sincronizar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.sincronizar.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=sincronizar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("DELETE tab_cobrancas_detalhes FROM tab_cobrancas INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id) WHERE col_id = {$_GET["i"]} AND cod_pai IS NOT NULL");
				$this->bancodedados->query("DELETE tab_cobrancas_detalhes FROM tab_cobrancas INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id) WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_cobrancas WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_cobrancas_lotes SET col_gerado = 0 WHERE col_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_cron SET cro_ativo = 1, cro_ultima_execucao_inicio = NULL, cro_ultima_execucao_termino = NULL WHERE cro_classe = 'cobrancas_geracao' AND cro_acao = 'cobrancasGerar' AND cro_ativo = 0");
				
				$comentario = "<b>Ação:</b> Sincronizado (Geração Agendada)";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($_GET['i'], $comentario, $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
	
	##############
	### Enviar ###
	##############
	
	function enviar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->dados_item["formato_parametros"]["enviavel"]) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas_lotes.enviar.inc.php");
				
				$this->conteudo = str_replace("[data_hora_envio]", date("d/m/Y (H:i)"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=enviar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$cobrancas_lote = $this->buscaCobrancasEnviaveis($_GET["i"]);
				$data_hora_envio = ($_POST["data_hora_envio"]) ? substr($_POST["data_hora_envio"], 6, 4) . "-" . substr($_POST["data_hora_envio"], 3, 2) . "-" . substr($_POST["data_hora_envio"], 0, 2) . " " . substr($_POST["data_hora_envio"], 12, 2) . ":" . substr($_POST["data_hora_envio"], 15, 2) . ":00" : date("Y-m-d h:i:s");
				
				$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
				
				foreach ($cobrancas_lote as $dados_cobranca) {
					$cobrancas->enviaMensagemCobranca($dados_cobranca["cliente_id"], $dados_cobranca, null, null, 12, $data_hora_envio);
				}
				
				$this->bancodedados->query("UPDATE tab_cobrancas_lotes SET cla_id = 4 WHERE col_id = {$_GET["i"]}");
				
				$comentario = strtotime($data_hora_envio) <= time() ? "<b>Ação:</b> Enviado" : "<b>Ação:</b> Envio agendado para {$_POST["data_hora_envio"]}";
				if (trim($_POST["comentario"])) $comentario .= "<br><b>Conteúdo:</b> " . preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
				$this->registraComentario($_GET['i'], $comentario, $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
	
	################
	### Exportar ###
	################
	
	function exportar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			// $exportacao = controle::instanciaObjeto("cobrancas.exportacao", $this->bancodedados);
			// $exportacao->exportaLote($_GET["i"]);
			
			$boletos_pdf = controle::instanciaObjeto("cobrancas.boletos_pdf", $this->bancodedados);
			
			$boletos_pdf->gerarLote($_GET["i"]);
			$boletos_pdf->exportar();
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
		
	}
	
	##############
	### Listar ###
	##############
	
	function listar() {
		
		if (! $_GET["f"] && ! $_GET["p"]) {
			$_GET["f"]["vencimento"] = $this->buscaUltimoVencimento();
		}
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		
		$botoes = array();
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
		if ($this->permissoes[$this->dados_modulo["nome"]]["gerar"]) $botoes[] = '<input type="button" class="buttonAzulEscuroBranco11" name="gerar" value="Gerar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=gerar\'">';
		
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
	
	function buscaUltimoVencimento() {
		
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(MAX(col_vencimento), '%m/%Y')
			FROM
				tab_cobrancas_lotes");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function inicializaFiltro($valores = "") {
		
		$itens = array();
		
		// Nome
		$itens[] = array (
			"tipo" => "input",
			"nome" => "nome",
			"descricao" => "Nome",
			"valor" => "",
			"where" => "(col_nome LIKE '%[valor]%')"
		);
		
		// Competência
		$itens[] = array (
			"tipo" => "input",
			"nome" => "vencimento",
			"descricao" => "Vencimento",
			"valor" => "",
			"where" => "(tab_cobrancas_lotes.col_vencimento BETWEEN '" . (substr($valores["vencimento"], 3, 4) . "-" . substr($valores["vencimento"], 0, 2) . "-" . "01") . "' AND '" . date("Y-m-t", strtotime(substr($valores["vencimento"], 3, 4) . "-" . substr($valores["vencimento"], 0, 2) . "-" . "01")) . "')",
			"formatacao" => "formataCompetencia(this, event)"
		);
		
		// Formatos
		$formatos = $this->buscaFormatos();
		$select_formatos = array();
		foreach ($formatos as $id => $nome) {
			$select_formatos[] = array (
				"id" => $id,
				"nome" => $nome
			);
		}
		$itens[] = array (
			"tipo" => "select",
			"nome" => "formato",
			"descricao" => "Formato",
			"valor" => $select_formatos,
			"where" => "(tab_cobrancas_lotes.cof_id = [valor])"
		);
		
		// Sincronizado
		$select_sincronizados = array (
			array (
				"id" => "1",
				"nome" => "Não"
			),
			array (
				"id" => "2",
				"nome" => "Sim"
			)
		);
		$itens[] = array (
			"tipo" => "select",
			"nome" => "sincronizado",
			"descricao" => "Sincronizado",
			"valor" => $select_sincronizados,
			"where" => "(tab_cobrancas_lotes.col_sincronizado + 1 = [valor])"
		);
		
		// Formatos
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array (
				"id" => $id,
				"nome" => $nome
			);
		}
		$itens[] = array (
			"tipo" => "select",
			"nome" => "status",
			"descricao" => "Status",
			"valor" => $select_status,
			"where" => "(tab_cobrancas_lotes.cla_id = [valor])"
		);
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}
	
	function buscaItens($filtro, $pagina) {
		
		$itens = array ();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_cobrancas_lotes.col_id AS id,
				tab_cobrancas_lotes.col_nome AS lote_nome,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m/%Y') AS vencimento,
				IF(tab_cobrancas_lotes.col_sincronizado = 1, 'Sim', 'Não') AS sincronizado,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				IF(tab_cobrancas_lotes.col_gerado = 1, tab_cobrancas_lotes_status.cla_nome, CONCAT(tab_cobrancas_lotes_status.cla_nome, '<br><font color=\"red\">Gerando...</font>')) AS status_nome,
				COUNT(tab_cobrancas.coa_id) AS cobrancas_quantidade,
				SUM(tab_cobrancas.coa_valor_original) AS cobrancas_original,
				SUM(tab_cobrancas.coa_valor_bonificacao) AS cobrancas_bonificacao,
				SUM(tab_cobrancas.coa_valor_original - tab_cobrancas.coa_valor_bonificacao) AS cobrancas_final
			FROM
				tab_cobrancas_lotes
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_lotes.cof_id = tab_cobrancas_formatos.cof_id)
				INNER JOIN tab_cobrancas_lotes_status ON (tab_cobrancas_lotes.cla_id = tab_cobrancas_lotes_status.cla_id)
				LEFT OUTER JOIN tab_cobrancas ON (tab_cobrancas.col_id = tab_cobrancas_lotes.col_id)
			WHERE
				$filtro->parametros_sql
			GROUP BY
				tab_cobrancas_lotes.col_id
			ORDER BY
				tab_cobrancas_lotes.col_vencimento ASC,
				tab_cobrancas_lotes.col_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["cobrancas_lotes"]["ver"]) $dados["link"] = '?m=cobrancas_lotes&a=ver&i=' . $dados["id"];
			
			$dados["cobrancas_quantidade"] = number_format($dados["cobrancas_quantidade"], 0, ",", ".");
			$dados["cobrancas_original"] = $this->formataDinheiro($dados["cobrancas_original"], 'R$', true);
			$dados["cobrancas_bonificacao"] = $this->formataDinheiro($dados["cobrancas_bonificacao"], 'R$', true);
			$dados["cobrancas_final"] = $this->formataDinheiro($dados["cobrancas_final"], 'R$', true);
			$dados["lote_nome"] = str_replace(" - ", "<br>", $dados["lote_nome"]);
			$dados["lote_nome"] = str_replace(" (", "<br>(", $dados["lote_nome"]);
			$dados["formato_nome"] = str_replace(" (", "<br>(", $dados["formato_nome"]);
			$dados["formato_nome"] = str_replace(" [", "<br>[", $dados["formato_nome"]);
			$dados["status_nome"] = str_replace(" (", "<br>(", $dados["status_nome"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
			
		}
		
		return array (
			"linhas" => $linhas,
			"itens" => $itens
		);
		
	}
	
	function geraListagem($itens) {
		
		$cabecalho = array (
			"contador" => "#",
			"lote_nome" => "Nome",
			"vencimento" => "Vencimento",
			"formato_nome" => "Formato",
			"cobrancas_quantidade" => "Cobranças",
			"cobrancas_original" => "Original",
			"cobrancas_bonificacao" => "Bonificação",
			"cobrancas_final" => "Final",
			"status_nome" => "Status"
		);
		
		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array (
			"cobrancas_quantidade" => "formataNumero",
			"cobrancas_original" => "formataDinheiro",
			"cobrancas_bonificacao" => "formataDinheiro",
			"cobrancas_final" => "formataDinheiro"
		), null, null, array (
			"right",
			"left",
			"center",
			"center",
			"center",
			"right",
			"right",
			"right"
		));
		
	}
	
}

?>