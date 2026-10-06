<?

set_time_limit(0);
ignore_user_abort(1);

require_once("_global/modulo.class.inc.php");

class clientes_origens extends modulo {
	
	//TODO Ver como lidar com liberação de planos / descontos
	//TODO Ver como lidar com bloqueio e liberação de formas de cobrança
	//TODO Integrar com plataforma e criar Vendor e Lotes do Vendor

	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		$this->buscaDadosModulo($_GET["m"]);
		
		if ($executar) {
			$_GET["a"] = $_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"] ? "editar" : $_GET["a"];
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "buscar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
	
	##############
	### Buscar ###
	##############
	
	function buscar() {
		
		if (!$_GET["f"] && !$_GET["p"]) {
			$_GET["f"]["status"] = 1;
		}
		
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
		
		// Nome
		$itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", 
		"where" => "(tab_clientes_origens.clo_nome LIKE '%[valor]%' OR tab_clientes_origens.clo_razao_social LIKE '%[valor]%' OR 
					tab_clientes_origens.clo_email LIKE '%[valor]%' OR tab_clientes_origens.clo_login LIKE '%[valor]%')");
		
		// Estado
		$itens[] = array ("tipo" => "select", "nome" => "estado", "descricao" => "Estado", "valor" => $this->buscaEstados(), "where" => "(tab_clientes_origens.clo_id IN (SELECT clo_id FROM rel_clo_cid_contem WHERE cid_id IN (SELECT cid_id FROM tab_cidades WHERE est_id = [valor])))");

		// Cidade
		$itens[] = array ("tipo" => "input", "nome" => "cidade", "descricao" => "Cidade", "valor" => "", "where" => "(tab_clientes_origens.clo_id IN (SELECT clo_id FROM rel_clo_cid_contem WHERE cid_id IN (SELECT cid_id FROM tab_cidades WHERE cid_nome LIKE '%[valor]%')))");
		
		// Status
		$itens[] = array ("tipo" => "select", "nome" => "status_origem", "descricao" => "Status", "valor" => $this->buscaStatus(), "where" => "(tab_clientes_origens.cos_id IN ([valor]))");
		
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
				clo_id AS id,
				clo_nome AS nome,
				clo_vendor_id AS vendor_id,
				clo_vendor_lot_id AS vendor_lot_id,
				-- (SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 4) AS mailings,
				-- (SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 3) AS cancelados,
				-- (SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 1) AS prospects,
				-- (SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 2) AS ativos,
				tab_clientes_origens_status.cos_nome AS status
			FROM
				tab_clientes_origens
				INNER JOIN tab_clientes_origens_status ON (tab_clientes_origens.cos_id = tab_clientes_origens_status.cos_id)
			WHERE
				$filtro->parametros_sql AND
				clo_id > 0
			ORDER BY
				clo_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes_origens"]["ver"]) {
				$dados["link"] = "?m=clientes_origens&a=ver&i={$dados["id"]}";
			}
			
			//$dados["mailings"] = number_format($dados["mailings"], 0, ",", ".");
			//$dados["cancelados"] = number_format($dados["cancelados"], 0, ",", ".");
			//$dados["prospects"] = number_format($dados["prospects"], 0, ",", ".");
			//$dados["ativos"] = number_format($dados["ativos"], 0, ",", ".");
			
			$dados["estados"] = $this->buscaOrigemEstados($dados["id"]);
			$dados["cidades"] = $this->buscaOrigemCidades($dados["id"]);
			
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
			
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}
	
	function geraListagem($itens) {
		
		//$cabecalho = array("contador" => "#", "nome" => "Nome", "mailings" => "<span title=\"Mailings\">M</span>", "cancelados" => "<span title=\"Cancelados\">C</span>", "prospects" => "<span title=\"Prospects\">P</span>", "ativos" => "<span title=\"Ativos\">A</span>", "estados" => "Estados", "cidades" => "Cidades", "status" => "Status");
		$cabecalho = array("contador" => "#", "nome" => "Nome", "estados" => "Estados", "cidades" => "Cidades", "status" => "Status");
		
		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 100, 35, 35, 35, 35, 50, 0, 60), null, array("right", "left", "right", "right", "right", "right", "center", "left", "center"));
		
	}
	
	###########
	### Ver ###
	###########
	
	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->permissoes["clientes_origens"]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\'">';
			if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $_GET["i"] . '\';">';
			if ($this->ehBloqueavel()) $botoes[] = '<input type="button" name="bloquear" class="buttonAzulEscuroBranco11" value="Bloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=bloquear&i=' . $_GET["i"] . '\';">';
			if ($this->ehDesbloqueavel()) $botoes[] = '<input type="button" name="desbloquear" class="buttonAzulEscuroBranco11" value="Desbloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desbloquear&i=' . $_GET["i"] . '\';">';
			if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar&i=' . $_GET["i"] . '\';">';
			if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $_GET["i"] . '\';">';
			if ($this->ehCancelavelReajuste()) $botoes[] = '<input type="button" name="cancelar_reajuste" class="buttonAzulEscuroBranco11" value="Cancelar Reajuste" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar_reajuste&i=' . $_GET["i"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$aplicacoes = array();
			if ($this->dados_item["parametros"]["desconto_condominio"] == '1') $aplicacoes[] = "Herda Desconto Condomínio";
			if ($this->dados_item["parametros"]["aplica_pessoa_fisica"] == '1') $aplicacoes[] = "Pessoa Física";
			if ($this->dados_item["parametros"]["aplica_pessoa_juridica"] == '1') $aplicacoes[] = "Pessoa Jurídica";
			if ($this->dados_item["parametros"]["aplica_com_condominio"] == '1') $aplicacoes[] = "Com Condomínio";
			if ($this->dados_item["parametros"]["aplica_sem_condominio"] == '1') $aplicacoes[] = "Sem Condomínio";
			$aplicacoes = implode(" / ", $aplicacoes);
			
			$this->conteudo .= $this->carregaArquivo("clientes_origens.ver.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Cadastrais", "editar_dt" => "Dados T&eacute;cnicos")), $this->conteudo);
			
			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[razao_social]", $this->dados_item["razao_social"], $this->conteudo);
			$this->conteudo = str_replace("[cnpj]", $this->dados_item["cnpj"], $this->conteudo);
			$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
			$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
			$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
			$this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
			$this->conteudo = str_replace("[email]", $this->dados_item["email"], $this->conteudo);
			$this->conteudo = str_replace("[mailings]", $this->dados_item["mailings"], $this->conteudo);
			$this->conteudo = str_replace("[cancelados]", $this->dados_item["cancelados"], $this->conteudo);
			$this->conteudo = str_replace("[prospects]", $this->dados_item["prospects"], $this->conteudo);
			$this->conteudo = str_replace("[ativos]", $this->dados_item["ativos"], $this->conteudo);
			$this->conteudo = str_replace("[erp]", $this->dados_item["erp"], $this->conteudo);
			$this->conteudo = str_replace("[cidades_listagem]", $this->geraCidadesListagem($this->dados_item["cidades"], false), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[comentarios]", $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
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
				if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $_GET["i"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["sincronizar"]) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=sincronizar&i=' . $_GET["i"] . '\';">';
				if ($this->ehBloqueavel()) $botoes[] = '<input type="button" name="bloquear" class="buttonAzulEscuroBranco11" value="Bloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=bloquear&i=' . $_GET["i"] . '\';">';
				if ($this->ehDesbloqueavel()) $botoes[] = '<input type="button" name="desbloquear" class="buttonAzulEscuroBranco11" value="Desbloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desbloquear&i=' . $_GET["i"] . '\';">';
				if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar&i=' . $_GET["i"] . '\';">';
				if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $_GET["i"] . '\';">';
				if ($this->ehCancelavelReajuste()) $botoes[] = '<input type="button" name="cancelar_reajuste" class="buttonAzulEscuroBranco11" value="Cancelar Reajuste" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar_reajuste&i=' . $_GET["i"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"] . '\';">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.editar.inc.php");
				
				$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Cadastrais", "editar_dt" => "Dados T&eacute;cnicos")), $this->conteudo);
				
				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[razao_social]", $this->dados_item["razao_social"], $this->conteudo);
				$this->conteudo = str_replace("[cnpj]", $this->dados_item["cnpj"], $this->conteudo);
				$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
				$this->conteudo = str_replace("[pesquisar_cep]", !$this->permissoes["ceps"]["pesquisar"] ? "" : "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />", $this->conteudo);
				$this->conteudo = str_replace("[adicionar_cep]", !$this->permissoes["ceps"]["adicionar"] ? "" : "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />", $this->conteudo);
				$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
				$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
				$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
				$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
				$this->conteudo = str_replace("[telefone]", $this->dados_item["telefone"], $this->conteudo);
				$this->conteudo = str_replace("[email]", $this->dados_item["email"], $this->conteudo);
				$this->conteudo = str_replace("[mailings]", $this->dados_item["mailings"], $this->conteudo);
				$this->conteudo = str_replace("[cancelados]", $this->dados_item["cancelados"], $this->conteudo);
				$this->conteudo = str_replace("[prospects]", $this->dados_item["prospects"], $this->conteudo);
				$this->conteudo = str_replace("[ativos]", $this->dados_item["ativos"], $this->conteudo);
				$this->conteudo = str_replace("[erp]", $this->dados_item["erp"], $this->conteudo);
				$this->conteudo = str_replace("[cidades_listagem]", $this->geraCidadesListagem($this->dados_item["cidades"]), $this->conteudo);
				$this->conteudo = str_replace("[cidades_lista]", serialize($this->dados_item["cidades"]), $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
				$this->conteudo = str_replace("[comentarios]", $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);

				// alteração API
				$this->conteudo = str_replace("[apihash]", $this->dados_item["hashapi"], $this->conteudo);
				$this->conteudo = str_replace("[apikey]", ($this->dados_item["hashapi"]==''?'':sha1($this->dados_item["hashapi"])), $this->conteudo);
				$this->conteudo = str_replace("[gerar_key]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"gerar_key\" id=\"gerar_key\" value=\"Gerar\" onClick=\"newkeyapi()\" />", $this->conteudo);
				$this->conteudo = str_replace("[apilogin]", $this->dados_item["loginapi"], $this->conteudo);
				$this->conteudo = str_replace("[apipass]", '', $this->conteudo);
				$this->conteudo = str_replace("[logo_preview]", $this->geraLogoPreview($_GET["i"]), $this->conteudo);
				$this->conteudo = str_replace("[remover_logo_disabled]", $this->geraLogoPreview($_GET["i"]) ? "" : "display: none;", $this->conteudo);
			} else {
				
				$_POST["nome"] = addslashes(trim($_POST["nome"]));
				$_POST["erp"] = addslashes(trim($_POST["erp"]));
				$_POST["razao_social"] = addslashes(trim($_POST["razao_social"]));
				$_POST["cnpj"] = addslashes(trim($_POST["cnpj"]));
				$_POST["numero"] = addslashes(trim($_POST["numero"]));
				$_POST["complemento"] = addslashes(trim($_POST["complemento"]));
				$_POST["telefone"] = addslashes(trim($_POST["telefone"]));
				$_POST["email"] = mb_strtolower(addslashes(trim($_POST["email"])), 'iso-8859-1');
				$_POST["cep"] = preg_replace("/[^0-9]/", "", $_POST["cep"]);
				$_POST["cep"] = $_POST["cep"] ? $_POST["cep"] : "NULL";

				if ($_POST["remover_logo"] == "1") {
					$logo_sql = ", clo_logo = NULL";
				} else {
					$logo_sql = $this->processaUploadLogo();
				}
				
				$this->bancodedados->query("
					UPDATE
						tab_clientes_origens
					SET
						clo_nome = '{$_POST["nome"]}',
						clo_erp = '{$_POST["erp"]}',
						clo_razao_social = '{$_POST["razao_social"]}',
						clo_cnpj = '{$_POST["cnpj"]}',
						clo_numero = '{$_POST["numero"]}',
						clo_complemento = '{$_POST["complemento"]}',
						clo_telefone = '{$_POST["telefone"]}',
						clo_email = '{$_POST["email"]}',
						cep_id = {$_POST["cep"]},
						clo_hashapi = '{$_POST["apihash"]}',
						clo_login = '{$_POST["apilogin"]}'
						".(trim($_POST["apipass"])==""?"":",clo_pass = '".md5($_POST["apipass"])."'")."
						{$logo_sql}
					WHERE
						clo_id = {$_GET["i"]}");
				
				$this->bancodedados->query("
					DELETE FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_GET["i"]}");
				
				$cidades = unserialize(stripslashes($_POST["cidades_lista"]));
				
				foreach ($cidades as $cidade) {
					$this->bancodedados->query("
						INSERT INTO
							rel_clo_cid_contem
						SET
							clo_id = {$_GET["i"]},
							cid_id = {$cidade["cidade_id"]}");
				}
				
				if ($this->dados_item["status_id"] > 1) {
					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
					$publicacao->sincronizar($this->dados_item["id"]);
				}
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
	
	function logo() {
		
		$origem_id = (int)$_GET["i"];
		
		if (!$origem_id) {
			header("HTTP/1.0 404 Not Found");
			die();
		}
		
		$query = $this->bancodedados->query("
			SELECT
				clo_logo
			FROM
				tab_clientes_origens
			WHERE
				clo_id = {$origem_id} AND
				clo_logo IS NOT NULL");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false && $dados["clo_logo"]) {
			
			$info = @getimagesizefromstring($dados["clo_logo"]);
			$mime = $info ? $info["mime"] : "image/png";
			
			header("Content-Type: {$mime}");
			header("Cache-Control: private, max-age=3600");
			print $dados["clo_logo"];
			die();
			
		}
		
		header("HTTP/1.0 404 Not Found");
		die();
		
	}
	
	/**
	 * @param int $origem_id
	 * @return string|null
	 */
	function geraLogoPreview($origem_id) {
		
		$origem_id = (int)$origem_id;
		
		if (!$origem_id) {
			return "";
		}
		
		$query = $this->bancodedados->query("
			SELECT
				clo_logo logo
			FROM
				tab_clientes_origens
			WHERE
				clo_id = {$origem_id} AND
				clo_logo IS NOT NULL");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false && $dados["logo"] != null) {

			if (!$dados["logo"] || strlen($dados["logo"]) === 0) {
				return null;
			}
	
			$info = @getimagesizefromstring($dados["logo"]);
	
			if ($info === false) {
				return null;
			}
	
			$imagem = "data:{$info["mime"]};base64," . base64_encode($dados["logo"]);

			return '<div style="float: left;margin: 0 10px;" id="logo_preview">
						<span class="txtAzulEscuro11">Logo atual:</span><br>
						<img src="'.$imagem.'" alt="Logo atual" style="max-height:60px; margin-bottom:8px; display:block;">
					</div>';
		}
		
		return false;
		
	}
	
	function processaUploadLogo() {
		
		if (empty($_FILES["logo"]["tmp_name"]) || !is_uploaded_file($_FILES["logo"]["tmp_name"]) || $_FILES["logo"]["error"] !== UPLOAD_ERR_OK) {
			return "";
		}
		
		$logo_binario = file_get_contents($_FILES["logo"]["tmp_name"]);
		
		if ($logo_binario === false || strlen($logo_binario) === 0) {
			return "";
		}
		
		if (@getimagesizefromstring($logo_binario) === false) {
			return "";
		}
		
		return ", clo_logo = 0x".bin2hex($logo_binario);
		
	}
        
	function buscaCatalogosVods($compacto = true) {
		
		$catalogos = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				codigo AS id,
				nome AS nome
			FROM
				tab_catalogos_vods
			WHERE
				1 AND status IS TRUE
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($compacto) {
				$catalogos[$dados["id"]] = $dados["nome"];
			} else {
				$catalogos[] = $dados;
			}
		}
		
		return $catalogos;
		
	}
        
	###################
	### Comentários ###
	###################

	function registraComentario($origem_id, $conteudo, $responsavel_id = 'NULL') {
		
		$origem_id = preg_replace("/[^0-9]/", "", $origem_id);
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		if ($origem_id) {
			$query = "INSERT INTO tab_clientes_origens_comentarios (clo_id, datahora, conteudo, usu_id) VALUES ($origem_id, NOW(), '$conteudo', $responsavel_id)";
			$this->bancodedados->query($query);
		}
	}
        
	######################
	### Gera Listagens ###
	######################

	function geraComentariosListagem($comentarios) {

		$cabecalho = array ("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);

		return $listagem->conteudo;

	}
        
	function buscaComentarios($origem_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_nome IS NOT NULL, tab_usuarios.usu_nome, 'Sistema') AS responsavel,
				DATE_FORMAT(tab_clientes_origens_comentarios.datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_clientes_origens_comentarios.conteudo AS conteudo
			FROM
				tab_clientes_origens_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes_origens_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_clientes_origens_comentarios.clo_id = $origem_id
			ORDER BY
				tab_clientes_origens_comentarios.datahora ASC,
				tab_clientes_origens_comentarios.clo_id ASC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
			$dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
			$dados["detalhamento"] .= "{$dados["conteudo"]}";

			$comentarios[] = $dados;

		}

		return $comentarios;
	}
        
	function buscaCatalogosTVs($compacto = true) {
		
		$catalogos = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				codigo AS id,
				nome AS nome
			FROM
				tab_catalogos_tvs
			WHERE
				1 AND status IS TRUE
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($compacto) {
				$catalogos[$dados["id"]] = $dados["nome"];
			} else {
				$catalogos[] = $dados;
			}
		}
		
		return $catalogos;
		
	}
        	
	function opcoesSimNao() {

		$lista["0"] = "Não";
		$lista["1"] = "Sim";

		return $lista;
	}

	function opcoesVodStatus() {

		$lista["ATIVO"] = "Ativo";
		$lista["INATIVO"] = "Inativo";

		return $lista;
	}

	function opcoesVodExibirTitulos() {

		$lista["TODOS"] = "Todos";
		$lista["CONTRATADOS"] = "Contratados";

		return $lista;
	}
	
	#############################
	### Editar Dados Têcnicos ###
	#############################
	
	function desbloquearApi($id) {
		
		$id = preg_replace("/[^0-9]/", "", $id);
		
		if ($id) {
			$this->bancodedados->query("
				UPDATE
					tab_clientes_origens
				SET
					clo_errorapi = 0
				WHERE
					clo_id = $id");
		}
		
	}

	function editar_dt() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {

			$ehAdmin = $this->ehAdmin();
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["desbloquear_api"] && $this->dados_item["errorapi"] > 3) $botoes[] = '<input type="button" name="desbloquear_api" class="buttonAzulEscuroBranco11" value="Desbloquear API" onclick="desbloquearApi(' . $_GET["i"] . ')">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.editar_dt.inc.php");
				$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Cadastrais", "editar_dt" => "Dados T&eacute;cnicos")), $this->conteudo);
				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
                                
				$vodAtivo = $this->dados_item["vod_status"] === "ATIVO";

				$this->conteudo = str_replace("[vod_status]", $this->geraSelect($this->opcoesVodStatus(), $this->dados_item["vod_status"] ?: "INATIVO", false, 'vod_status', '250px', "atualizaCamposVod(this.value)"), $this->conteudo);
				$this->conteudo = str_replace("[vod_exibir_titulos]", $this->geraSelect($this->opcoesVodExibirTitulos(), $this->dados_item["vod_exibir_titulos"] ?: "TODOS", false, 'vod_exibir_titulos', '250px', null, $vodAtivo), $this->conteudo);
				$this->conteudo = str_replace("[vod_disabled]", $vodAtivo ? "" : "disabled", $this->conteudo);

				$dataDegustacao = empty($this->dados_item["inicio_degustacao"])? "" : $this->dados_item["inicio_degustacao"];
				$this->conteudo = str_replace("[tempo_degustacao]", $this->dados_item["degustacao"], $this->conteudo);
				$this->conteudo = str_replace("[data_inicio_degustacao]", $dataDegustacao, $this->conteudo);
				$this->conteudo = str_replace("[plano_vod]", $this->geraSelect($this->buscaCatalogosVods(), $this->dados_item["plano_vod"] , true, 'plano_vod', '250px', null, $vodAtivo), $this->conteudo);
				$disabledDataDegustaca = "disabled";
				if($this->podeEditarDataDegustacao()) {
					$disabledDataDegustaca = "";
				}
				$verDegustacao = "none";
				if($this->podeVerDegustacao()){
					$verDegustacao = "";
				}
				$this->conteudo = str_replace("[verDegustacao]", $verDegustacao, $this->conteudo);
				$this->conteudo = str_replace("[disabledDataDegustaca]", $disabledDataDegustaca, $this->conteudo);
				$this->conteudo = str_replace("[data_locked]", $this->podeEditarDataDegustacao() ? "" : "data-locked=\"1\"", $this->conteudo);
                                
				$dataDegustacao_tv = empty($this->dados_item["inicio_degustacao_tv"])? "" : $this->dados_item["inicio_degustacao_tv"];
				$this->conteudo = str_replace("[tempo_degustacao_tv]", $this->dados_item["degustacao_tv"], $this->conteudo);
				$this->conteudo = str_replace("[data_inicio_degustacao_tv]", $dataDegustacao_tv, $this->conteudo);
				$this->conteudo = str_replace("[plano_tv]", $this->geraSelect($this->buscaCatalogosTVs(), $this->dados_item["plano_tv"] , true, 'plano_tv', '250px'), $this->conteudo);
				$disabledDataDegustacaTv = "disabled";
				if($this->podeEditarDataDegustacaoTv()) {
					$disabledDataDegustacaTv = "";
				}    
				$verDegustacaoTv = "none";
				if($this->podeVerDegustacaoTv()){
					$verDegustacaoTv = "";
				}
				
				if($ehAdmin) {
					$ativoApenasCarterira = true;
				} else {
					$ativoApenasCarterira = false;
				}
				$this->conteudo = str_replace("[separar_pontos]", $this->geraSelect($this->opcoesSimNao(), $this->dados_item["separar_pontos"] , true, 'separar_pontos', '250px'), $this->conteudo);
				$this->conteudo = str_replace("[apenas_carteira]", $this->geraSelect($this->opcoesSimNao(), $this->dados_item["apenas_carteira"] , true, 'apenas_carteira','250px', null, $ativoApenasCarterira), $this->conteudo);
				
				$this->conteudo = str_replace("[verDegustacaoTv]", $verDegustacaoTv, $this->conteudo);
				$this->conteudo = str_replace("[disabledDataDegustaca_tv]", $disabledDataDegustacaTv, $this->conteudo);
                                
				$this->conteudo = str_replace("[vendor_id]", $this->dados_item["vendor_id"], $this->conteudo);
				$this->conteudo = str_replace("[vendor_lot_id]", $this->dados_item["vendor_lot_id"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_dt&i={$_GET["i"]}", $this->conteudo);
				
			} else {

				if(!$ehAdmin) {
					$_POST['apenas_carteira'] = $this->dados_item["apenas_carteira"];
				}

				if (!$this->podeEditarDataDegustacao()) {
					$_POST["data_inicio_degustacao"] = "'{$this->dados_item["inicio_degustacao"]}'";
				} else {
					$_POST["data_inicio_degustacao"] = $_POST["plano_vod"] && $_POST["data_inicio_degustacao"] ? "'{$_POST["data_inicio_degustacao"]}'" : "NULL";
				}
                                
				if (!$this->podeEditarDataDegustacaoTv()) {
					$_POST["data_inicio_degustacao_tv"] = "'{$this->dados_item["inicio_degustacao_tv"]}'";
				} else {
					$_POST["data_inicio_degustacao_tv"] = $_POST["plano_tv"] &&$_POST["data_inicio_degustacao_tv"] ? "'{$_POST["data_inicio_degustacao_tv"]}'" : "NULL";
				}
				
				$_POST["vendor_id"] = intval($_POST["vendor_id"], 10);
				$_POST["vendor_lot_id"] = intval($_POST["vendor_lot_id"], 10);
				$_POST["tempo_degustacao"] = intval($_POST["tempo_degustacao"], 10);
				$_POST["tempo_degustacao_tv"] = intval($_POST["tempo_degustacao_tv"], 10);
				
				$_POST["tempo_degustacao"] = $_POST["plano_vod"] && $_POST["tempo_degustacao"] > 0 ? $_POST["tempo_degustacao"] : "NULL";
				$_POST["tempo_degustacao_tv"] = $_POST["plano_tv"] && $_POST["tempo_degustacao_tv"] > 0 ? $_POST["tempo_degustacao_tv"] : "NULL";
				$_POST["vendor_id"] = $_POST["vendor_id"] > 0 ? $_POST["vendor_id"] : "NULL";
				$_POST["vendor_lot_id"] = $_POST["vendor_lot_id"] > 0 ? $_POST["vendor_lot_id"] : "NULL";

				$_POST["vod_status"] = in_array($_POST["vod_status"], array("ATIVO", "INATIVO"), true) ? $_POST["vod_status"] : "INATIVO";
				$_POST["vod_exibir_titulos"] = in_array($_POST["vod_exibir_titulos"], array("TODOS", "CONTRATADOS"), true) ? $_POST["vod_exibir_titulos"] : "TODOS";

				$_POST["plano_vod"] = $_POST["plano_vod"] ? "'{$_POST["plano_vod"]}'" : "NULL";
				$_POST["plano_tv"] = $_POST["plano_tv"] ? "'{$_POST["plano_tv"]}'" : "NULL";

				if ($_POST["vod_status"] === "INATIVO") {
					$_POST["plano_vod"] = "NULL";
					$_POST["tempo_degustacao"] = "NULL";
					$_POST["data_inicio_degustacao"] = "NULL";
				}
                                
				$this->bancodedados->query("
					UPDATE
						tab_clientes_origens
					SET
						clo_vod_status = '{$_POST["vod_status"]}',
						clo_vod_exibir_titulos = '{$_POST["vod_exibir_titulos"]}',

						clo_plano_vod = {$_POST["plano_vod"]},
						clo_degustacao = {$_POST["tempo_degustacao"]},
						clo_inicio_degustacao = {$_POST["data_inicio_degustacao"]},
                                                    
						clo_plano_tv = {$_POST["plano_tv"]},
						clo_degustacao_tv = {$_POST["tempo_degustacao_tv"]},
						clo_inicio_degustacao_tv = {$_POST["data_inicio_degustacao_tv"]},
                                                    
						clo_separar_pontos = '{$_POST["separar_pontos"]}',
						clo_cobranca_carteira = '{$_POST["apenas_carteira"]}',
                                                    
						clo_vendor_id = {$_POST["vendor_id"]},
						clo_vendor_lot_id = {$_POST["vendor_lot_id"]}
					WHERE
						clo_id = {$_GET["i"]}");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				if ($this->dados_item["status_id"] > 1) {
					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
					$publicacao->sincronizar($this->dados_item["id"]);	
				}

				if($_POST['apenas_carteira'] != $this->dados_item["apenas_carteira"]) {
					if($_POST['apenas_carteira'] == '1') {
						$this->bancodedados->query("DELETE FROM rel_cof_clo_disponivel WHERE clo_id = {$_GET["i"]} AND cof_id != 1");
						$this->bancodedados->query("UPDATE tab_clientes_origens SET clo_bloqueio_dias = '0' WHERE clo_id = '{$_GET["i"]}';");
						$this->bancodedados->query("UPDATE tab_clientes SET cli_bloqueio_dias = NULL, cof_id = 1 WHERE clo_id = '{$_GET["i"]}'");
						$this->bancodedados->query("UPDATE tab_cobrancas SET coa_data_bloqueio = NULL WHERE cof_id = 1 AND coa_data_bloqueio IS NOT NULL AND cot_id = 1 AND cli_id IN (SELECT cli_id FROM tab_clientes WHERE clo_id = '{$_GET["i"]}');");
					} else {
						$this->bancodedados->query("INSERT INTO rel_cof_clo_disponivel (cof_id, clo_id) VALUES (7, {$_GET["i"]})");
						$this->bancodedados->query("UPDATE tab_clientes_origens SET clo_bloqueio_dias = '15' WHERE clo_id = '{$_GET["i"]}';");
						$this->bancodedados->query("UPDATE tab_clientes SET cli_bloqueio_dias = 15, cof_id = 7 WHERE clo_id = '{$_GET["i"]}'");
					}
				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar_dt&i={$_GET["i"]}");
				
			}
			
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
	
	#################
	### Adicionar ###
	#################
	
	function adicionar() {
		
		if (!$_POST) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			
			$this->conteudo .= $this->carregaArquivo("clientes_origens.adicionar.inc.php");
			
			$this->conteudo = str_replace("[pesquisar_cep]", !$this->permissoes["ceps"]["pesquisar"] ? "" : "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />", $this->conteudo);
			$this->conteudo = str_replace("[adicionar_cep]", !$this->permissoes["ceps"]["adicionar"] ? "" : "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />", $this->conteudo);
			$this->conteudo = str_replace("[cidades_listagem]", $this->geraCidadesListagem(array()), $this->conteudo);
			$this->conteudo = str_replace("[cidades_lista]", serialize(array()), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(' ', $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
			
		} else {
			
			$_POST["nome"] = addslashes(trim($_POST["nome"]));
			$_POST["razao_social"] = addslashes(trim($_POST["razao_social"]));
			$_POST["cnpj"] = addslashes(trim($_POST["cnpj"]));
			$_POST["numero"] = addslashes(trim($_POST["numero"]));
			$_POST["complemento"] = addslashes(trim($_POST["complemento"]));
			$_POST["telefone"] = addslashes(trim($_POST["telefone"]));
			$_POST["email"] = addslashes(trim($_POST["email"]));
			$_POST["cep"] = preg_replace("/[^0-9]/", "", $_POST["cep"]);
			$_POST["cep"] = $_POST["cep"] ? $_POST["cep"] : "NULL";				
			
			$this->bancodedados->query("
				INSERT INTO
					tab_clientes_origens
				SET
					clo_nome = '{$_POST["nome"]}',
					clo_razao_social = '{$_POST["razao_social"]}',
					clo_cnpj = '{$_POST["cnpj"]}',
					clo_numero = '{$_POST["numero"]}',
					clo_complemento = '{$_POST["complemento"]}',
					clo_telefone = '{$_POST["telefone"]}',
					clo_email = '{$_POST["email"]}',
					cep_id = {$_POST["cep"]},
					clo_hashapi = '',
					clo_login = '',
					clo_pass = ''");
			
			$origem_id = $this->bancodedados->ultimoIdInserido();
			
			$cidades = unserialize(stripslashes($_POST["cidades_lista"]));
			
			foreach ($cidades as $cidade) {
				$this->bancodedados->query("
						INSERT INTO
							rel_clo_cid_contem
						SET
							clo_id = {$origem_id},
							cid_id = {$cidade["cidade_id"]}");
			}
			
			$this->bancodedados->query("
				INSERT INTO
					rel_cof_clo_disponivel (cof_id, clo_id) 
				VALUES
					(1, {$origem_id}),
					(7, {$origem_id})");
			
			$query = $this->bancodedados->query("SELECT des_id AS id, des_parametros AS parametros FROM tab_descontos WHERE des_id IN (473, 478)");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				
				$dados["parametros"] = unserialize($dados["parametros"]);
				
				$dados["parametros"]["origens"][] = "{$origem_id}";
				
				$dados["parametros"] = serialize($dados["parametros"]);
				
				$this->bancodedados->query("UPDATE tab_descontos SET des_parametros = '{$dados["parametros"]}' WHERE des_id = {$dados["id"]}");
				
			}
			
			$this->registraComentario($origem_id, "<b>Ação</b>: Adicionado Origem", $_SESSION["usuario"]);
                        
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $origem_id);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$origem_id");
			
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
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.remover.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("UPDATE tab_clientes SET clo_id = 0 WHERE clo_id = {$_GET["i"]}");
				//TODO Remove after removing clo_id from tab_cidades
				$this->bancodedados->query("UPDATE tab_cidades SET clo_id = NULL WHERE clo_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_usuarios SET clo_id = 0, uss_id = 2 WHERE clo_id = {$_GET["i"]}");
				
				$this->bancodedados->query("DELETE FROM rel_clo_cid_contem WHERE clo_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_cof_clo_disponivel WHERE clo_id = {$_GET["i"]}");
				
				$this->bancodedados->query("DELETE FROM tab_clientes_origens WHERE clo_id = {$_GET["i"]}");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
							
	##############
	### Ativar ###
	##############
	
	function ativar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehAtivavel()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.ativar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("UPDATE tab_clientes_origens SET cos_id = 2 WHERE clo_id = {$_GET["i"]}");
				$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Ativado Origem", $_SESSION["usuario"]);

				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				$publicacao->sincronizar($this->dados_item["id"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
        
        
        ###################
	### Sincronizar ###
	###################
	
	function sincronizar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.sincronizar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=sincronizar&i={$_GET["i"]}&debug={$_GET["debug"]}", $this->conteudo);
				
			} else {
				
				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				$publicacao->sincronizar($this->dados_item["id"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Sincronizado Origem", $_SESSION["usuario"]);
									
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
        
			
	################
	### Bloquear ###
	################
	
	function bloquear() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehBloqueavel()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.bloquear.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=bloquear&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				if ($this->dados_item["status_id"] != $_POST["status"]) {
				
					$this->bancodedados->query("UPDATE tab_clientes_origens SET cos_id = {$_POST["status"]} WHERE clo_id = {$_GET["i"]}");
					$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Bloqueado Origem", $_SESSION["usuario"]);

					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
					$publicacao->sincronizar($this->dados_item["id"]);
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					
				}
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
					
	###################
	### Desbloquear ###
	###################
	
	function desbloquear() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehDesbloqueavel()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.desbloquear.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=desbloquear&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("UPDATE tab_clientes_origens SET cos_id = 2 WHERE clo_id = {$_GET["i"]}");
				$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Desbloqueado Origem", $_SESSION["usuario"]);
			
				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				$publicacao->sincronizar($this->dados_item["id"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
	
	################
	### Cancelar ###
	################
	
	function cancelar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehCancelavel()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.cancelar.inc.php");

				$this->conteudo = str_replace("[data_cancelamento]", date("d/m/Y"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=cancelar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->bancodedados->query("UPDATE tab_clientes_origens SET cos_id = 4 WHERE clo_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_usuarios SET uss_id = 2 WHERE clo_id = {$_GET["i"]}");

				$modulo_contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
				
				$contratos = $this->buscaContratos($_GET["i"]);
				
				foreach ($contratos as $contrato_id) {
					$modulo_contratos->buscaDadosItem($contrato_id);
					if ($modulo_contratos->dados_item["status_id"] <= 18) {
						$modulo_contratos->anularContrato($modulo_contratos->dados_item, 15);
					} else {
						$modulo_contratos->acaoCancelarContrato($modulo_contratos->dados_item, $_POST["data_cancelamento"], null);
						$this->bancodedados->query("UPDATE tab_contratos SET cma_id = 15 WHERE cor_id = {$modulo_contratos->dados_item["id"]}");
					}
				}
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Cancelado Origem", $_SESSION["usuario"]);
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}
	
	################
	### Desancelar ###
	################
	
	function descancelar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehDescancelavel()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.descancelar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=descancelar&i={$_GET["i"]}", $this->conteudo);
				
			} else {

				$modulo_contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
				
				$contratos = $this->buscaContratosCancelados($_GET["i"]);
				
				foreach ($contratos as $contrato_id) {
					
					$modulo_contratos->buscaDadosItem($contrato_id);
					$modulo_contratos->acaoDescancelarContrato();
					
					$this->bancodedados->query("UPDATE tab_contratos SET cma_id = null WHERE cor_id = {$modulo_contratos->dados_item["id"]}");
					
				}
				
				$this->bancodedados->query("UPDATE tab_clientes_origens SET cos_id = 2 WHERE clo_id = {$_GET["i"]}");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Descancelado Origem", $_SESSION["usuario"]);
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}

	#########################
	### Cancelar Reajuste ###
	#########################
	
	function cancelar_reajuste() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehCancelavelReajuste()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$clientes_todos = count($this->buscaClientes($_GET["i"], true));
				$clientes_vigentes = count($this->buscaClientes($_GET["i"], false));
				
				$this->conteudo .= $this->carregaArquivo("clientes_origens.cancelar_reajuste.inc.php");

				$this->conteudo = str_replace("[clientes_todos]", $clientes_todos, $this->conteudo);
				$this->conteudo = str_replace("[clientes_vigentes]", $clientes_vigentes, $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ",$botoes),$this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=cancelar_reajuste&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$modulo_clientes = controle::instanciaObjeto("clientes", $this->bancodedados, $this->permissoes, false);
				
				$clientes = $this->buscaClientes($_GET["i"], $_POST["contratos"] == "todos");
				
				foreach ($clientes as $cliente_id) {
					$modulo_clientes->cancelaReajuste($cliente_id);
				}
                $this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Cancelado ajustes da Origem", $_SESSION["usuario"]);
					
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
		}
		
	}

	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($origem_id) {

		if ($origem_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					tab_clientes_origens.clo_id AS id,
					tab_clientes_origens.clo_nome AS nome,
					tab_clientes_origens.clo_razao_social AS razao_social,
					tab_clientes_origens.clo_cnpj AS cnpj,
					tab_clientes_origens.clo_numero AS numero,
					tab_clientes_origens.clo_complemento AS complemento,
					tab_clientes_origens.clo_telefone AS telefone,
					tab_clientes_origens.clo_email AS email,
					INSERT(LPAD(tab_clientes_origens.cep_id, 8, '0'), 6, 0, '-') AS cep,
					tab_logradouros.log_nome AS logradouro,
					tab_bairros.bai_nome AS bairro,
					tab_cidades.cid_nome AS cidade,
					tab_estados.est_sigla AS estado,
					tab_clientes_origens.clo_bloqueio_dias AS bloqueio_dias,
					tab_clientes_origens.clo_vendor_id AS vendor_id,
					tab_clientes_origens.clo_vendor_lot_id AS vendor_lot_id,
					tab_clientes_origens_status.cos_id AS status_id,
					tab_clientes_origens_status.cos_nome AS status_nome,

					tab_clientes_origens.clo_hashapi AS hashapi, 
					tab_clientes_origens.clo_login AS loginapi,
					tab_clientes_origens.clo_pass AS passapi,
					
					tab_clientes_origens.clo_cobranca_carteira AS apenas_carteira,
                                        
					tab_clientes_origens.clo_vod_status AS vod_status,
					tab_clientes_origens.clo_vod_exibir_titulos AS vod_exibir_titulos,

					tab_clientes_origens.clo_degustacao AS degustacao,
					tab_clientes_origens.clo_plano_vod AS plano_vod,
					tab_clientes_origens.clo_inicio_degustacao AS inicio_degustacao,
                                        
					tab_clientes_origens.clo_degustacao_tv AS degustacao_tv,
					tab_clientes_origens.clo_plano_tv AS plano_tv,
					tab_clientes_origens.clo_inicio_degustacao_tv AS inicio_degustacao_tv,
					
					tab_clientes_origens.clo_separar_pontos AS separar_pontos,
					tab_clientes_origens.clo_errorapi AS errorapi,
					tab_clientes_origens.clo_erp AS erp,
                                        
					(SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 4) AS mailings,
					(SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 3) AS cancelados,
					(SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 1) AS prospects,
					(SELECT COUNT(*) FROM tab_clientes WHERE tab_clientes.clo_id = tab_clientes_origens.clo_id AND tab_clientes.cls_id = 2) AS ativos
				FROM
					tab_clientes_origens
					INNER JOIN tab_clientes_origens_status ON (tab_clientes_origens.cos_id = tab_clientes_origens_status.cos_id)
					LEFT JOIN tab_ceps ON (tab_clientes_origens.cep_id = tab_ceps.cep_id)
					LEFT JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
					LEFT JOIN tab_bairros ON (tab_ceps.bai_id = tab_bairros.bai_id)
					LEFT JOIN tab_cidades ON (tab_ceps.cid_id = tab_cidades.cid_id)
					LEFT JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)
				WHERE
					tab_clientes_origens.clo_id = $origem_id");
			
			if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
				
				$this->dados_item["mailings"] = number_format($this->dados_item["mailings"], 0, ",", ".");
				$this->dados_item["cancelados"] = number_format($this->dados_item["cancelados"], 0, ",", ".");
				$this->dados_item["prospects"] = number_format($this->dados_item["prospects"], 0, ",", ".");
				$this->dados_item["ativos"] = number_format($this->dados_item["ativos"], 0, ",", ".");
				
				$this->dados_item["cidades"] = $this->buscaCidades($this->dados_item["id"]);
				
				$this->dados_item["comentarios"] = $this->buscaComentarios($origem_id);
			}
			
		}
		
	}
	
	#################
	### Consultas ###
	#################
	
	function buscaClientes($origem_id, $todos) {
		
		$clientes = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_clientes.cli_id AS id
			FROM
				tab_clientes
				INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)".($todos ? "" : "
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				INNER JOIN tab_contratos ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)")."
			WHERE
				tab_clientes.clo_id = {$origem_id} AND
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.ass_migrada IS NOT NULL AND
				tab_assinaturas.ass_data_ativacao = '2022-04-01'".($todos ? "" : " AND
				tab_contratos.cor_pai IS NULL AND
				tab_contratos.cor_data_ativacao > '2021-04-01'"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes[] = $dados["id"];
		}
		
		return $clientes;
		
	}
	
	function buscaContratos($origem_id) {
		
		$contratos = array();
						
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_contratos.cor_id AS id
			FROM
				tab_clientes
				INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
			WHERE
				tab_clientes.clo_id = {$origem_id} AND
				tab_contratos.cor_pai IS NULL AND
				tab_contratos.crs_id IN (11, 12, 13, 14, 15, 16, 17, 18, 19, 20)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}
		
		return $contratos;
		
	}
		
	function buscaContratosCancelados($origem_id) {
		
		$contratos = array();
						
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_contratos.cor_id AS id
			FROM
				tab_clientes
				INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
			WHERE
				tab_clientes.clo_id = {$origem_id} AND
				tab_contratos.cor_pai IS NULL AND
				tab_contratos.crs_id IN (22) AND
				tab_contratos.cma_id = 15");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}
		
		return $contratos;
		
	}

	function buscaEstados($compacto = false) {
		
		$estados = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				est_id AS id,
				est_nome AS nome
			FROM
				tab_estados
			WHERE
				1
			ORDER BY
				est_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($compacto) {
				$estados[$dados["id"]] = $dados["nome"];
			} else {
				$estados[] = $dados;
			}
		}
		
		return $estados;
		
	}
		
	function buscaStatus() {
		
		$status = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cos_id AS id,
				cos_nome AS nome
			FROM
				tab_clientes_origens_status
			WHERE
				1
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[] = $dados;
		}
		
		$status[] = array("id" => "3,5", "nome" => "Bloqueado");
		
		usort($status, function ($a, $b) {
			return strcmp($a["nome"], $b["nome"]);
		});
		
		return $status;
		
	}

	function buscaOrigemEstados($origem_id) {
		
		$estados = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				est_sigla AS sigla,
				est_nome AS nome
			FROM
				tab_cidades
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$origem_id}
				)
			ORDER BY
				est_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$estados[] = "<span title=\"{$dados["nome"]}\">{$dados["sigla"]}</span>";
		}
		
		return implode(", ", $estados);
		
	}
	
	function buscaOrigemCidades($origem_id) {
		
		$cidades = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				CONCAT(cid_nome, ' / ', est_sigla) AS cidade
			FROM
				tab_cidades
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$origem_id}
				)
			ORDER BY
				est_nome,
				cid_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cidades[] = "<span style=\"white-space: nowrap;\">{$dados["cidade"]}</span>";
		}
		
		return implode(", ", $cidades);
		
	}
	
	function buscaCidades($origem_id) {
		
		$cidades = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cidades.cid_id AS cidade_id,
				tab_cidades.cid_nome AS cidade_nome,
				tab_estados.est_id AS estado_id,
				tab_estados.est_nome AS estado_nome
			FROM
				tab_cidades
				INNER JOIN tab_estados ON (tab_estados.est_id = tab_cidades.est_id)
			WHERE
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$origem_id}
				)
			ORDER BY
				tab_estados.est_nome,
				tab_cidades.cid_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cidades[] = $dados;
		}
		
		return $cidades;
		
	}
	
	function buscaEstadoCidades($estado_id) {
		
		$cidades = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cid_id AS id,
				cid_nome AS nome
			FROM
				tab_cidades
			WHERE
				est_id = $estado_id
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cidades[] = "new Array(\"{$dados["id"]}\", \"{$dados["nome"]}\")";
		}
		
		return $cidades;
		
	}
	
	function getEstadoNome($estado_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				est_nome
			FROM
				tab_estados
			WHERE
				est_id = $estado_id");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function getCidadeNome($cidade_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cid_nome
			FROM
				tab_cidades
			WHERE
				cid_id = $cidade_id");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	###############
	### Funções ###
	###############
	
	function geraCidadesAdicionar() {
		
		$adicionar = array();
		
		$adicionar["estado_nome"] = $this->geraSelect($this->buscaEstados(true), null, true, "estado_lista", "100%", "mudaEstadoLista(this.value)");
-		$adicionar["cidade_nome"] = '<select name="cidade" id="cidade_lista" style="width:100%" class="inputBrancoAzulEscuro11" disabled></select>';
		$adicionar["botoes"] = '<input type="button" name="cidades_adicionar" id="cidades_adicionar" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarCidade()">';
		
		return $adicionar;
		
	}
	
	function geraCidadesListagem($dados = null, $editavel = true) {
		
		if ($dados) {
			
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerCidade(\''.($i + 1).'\')">';
				}
			}
			
		} else {
			$dados = array(array("mesclado" => '<table width="100%" height="20" cellpadding="0" cellspacing="0" border="0"><tr><td class="txtAzulEscuro11" align="center">Nenhum registro disponível</td></tr></table>'));
		}
		
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->geraCidadesAdicionar()), $dados);
		}
		
		$cabecalho = array("estado_nome" => "Estado", "cidade_nome" => "Cidade");
		
		if ($editavel) {
			$cabecalho["botoes"] = "";
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(150, 0, 25), null, array("left", "left", "center"));
		
		return $listagem->conteudo;
		
	}
	
	function ordena_multiarray(&$array, $campos) {
		
		if (is_array($array) && is_array($campos)) {
			usort($array, $this->geraOrdenador($campos));
		}
		
	}
	
	function geraOrdenador($campos)  {
		
		return function ($a, $b) use ($campos) {
			$cmp = 0;
			if (is_array($a) && is_array($b)) {
				foreach ($campos as $campo) {
					if (array_key_exists($campo, $a) && array_key_exists($campo, $b)) {
						$cmp = strcmp($a[$campo], $b[$campo]);
						if ($cmp != 0) {
							return $cmp;
						}
					}
				}
			}
			return $cmp;
		};
		
	}
	
	function ehAtivavel() {
		
		return $this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item && $this->dados_item["status_id"] == 1;
	}
	
	function podeEditarDataDegustacao() {
                
		if(empty($this->dados_item["inicio_degustacao"])) return true;
		if($this->dados_item["inicio_degustacao"] == '0000-00-00') return true;
                
		return $this->permissoes[$this->dados_modulo["nome"]]["editar_dt_degustacao"] ;
	}
        
	function podeEditarDataDegustacaoTv() {
                
		if(empty($this->dados_item["inicio_degustacao_tv"])) return true;
		if($this->dados_item["inicio_degustacao_tv"] == '0000-00-00') return true;
                
		return $this->permissoes[$this->dados_modulo["nome"]]["editar_dt_degustacao_tv"] ;
	}
        
	function podeVerDegustacao() {
                
		return $this->permissoes[$this->dados_modulo["nome"]]["ver_degustacao"] ;
	}
	
        
	function podeVerDegustacaoTv() {
                
		return $this->permissoes[$this->dados_modulo["nome"]]["ver_degustacao_tv"] ;
	}
	function ehBloqueavel() {
		
		return $this->permissoes[$this->dados_modulo["nome"]]["bloquear"] && $this->dados_item && 
			($this->dados_item["status_id"] == 2 || $this->dados_item["status_id"] == 3 || $this->dados_item["status_id"] == 5);
	}
	
	function ehDesbloqueavel() {
		
		return $this->permissoes[$this->dados_modulo["nome"]]["desbloquear"] && $this->dados_item &&
			($this->dados_item["status_id"] == 3 || $this->dados_item["status_id"] == 5);
	}
		
	function ehCancelavel() {
		
		return $this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item && $this->dados_item["status_id"] == 2;
	}		
	
	function ehDescancelavel() {
		
		return $this->permissoes[$this->dados_modulo["nome"]]["descancelar"] && $this->dados_item && $this->dados_item["status_id"] == 4;
	}
		
	function ehCancelavelReajuste() {
		
		return $this->permissoes[$this->dados_modulo["nome"]]["cancelar_reajuste"];
	}

}

?>
