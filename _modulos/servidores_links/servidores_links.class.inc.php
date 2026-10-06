<?

require_once ("_global/modulo.class.inc.php");

class servidores_links extends modulo {
	
    function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"])
				$_GET["a"] = "editar";
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}
	
	function buscaUnidades() {
		$unidade["1"] = "Kbps";
		$unidade["2"] = "Mbps";
		$unidade["3"] = "Gbps";
		$unidade["4"] = "Tbps";
		return $unidade;
	}
	
	####################################
	######### Busca Dados Item #########
	####################################
	

	function buscaItens($filtro, $pagina) {
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
  			tab_servidores_links.sel_id AS id,
  			tab_servidores_links.sel_nome AS nome,
  			tab_servidores_links.sei_id AS sei_id,
  			tab_servidores_links.sel_endereco_ip AS endereco_ip,
  			tab_servidores_links.sel_download AS download,
  			tab_servidores_links.sel_upload AS upload,
  			tab_servidores_links_status.sls_id AS status_id,
  			tab_servidores_links_status.sls_nome AS status_nome,
  			CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS servidor_nome
			FROM
  			tab_servidores_links,
  			tab_servidores_links_status,
  			tab_servidores_interfaces,
  			tab_servidores
			WHERE
  			(tab_servidores_links.sls_id = tab_servidores_links_status.sls_id) AND
  			(tab_servidores_links.sei_id = tab_servidores_interfaces.sei_id) AND
  			(tab_servidores.ser_id = tab_servidores_interfaces.ser_id) AND
  			$filtro->parametros_sql
			ORDER BY
  			nome ASC
			LIMIT
  			$limit_inicio,
  			$limit_fim";
		$query = $this->bancodedados->query($query);
		$linhas = $this->bancodedados->linhasTotal($query);
		$unidades = $this->buscaUnidades();
		$itens = array();
		while (($item = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$item_tratado["link"] = '?m=' . $this->dados_modulo["nome"] . '&a=ver&i=' . $item["id"];
			}
			$unidade_download = floor(log($item["download"], 1024));
			if (!$unidade_download)
				$unidade_download = 1;
			$item["velocidade"] = round($item["download"] / pow(1024, $unidade_download), 2) . " / " . round($item["upload"] / pow(1024, $unidade_download), 2) . " " . $unidades[$unidade_download];
			$item_tratado["link"] = '?m=' . $this->dados_modulo["nome"] . '&a=ver&i=' . $item["id"];
			$item_tratado["nome"] = $item["nome"];
			$item_tratado["servidor_nome"] = $item["servidor_nome"];
			$item_tratado["velocidade"] = $item["velocidade"];
			$item_tratado["status_id"] = $item["status_id"];
			$item_tratado["status_nome"] = $item["status_nome"];
			$item_tratado["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$itens[] = $item_tratado;
			unset($item_tratado);
		}
		return array("linhas" => $linhas, "itens" => $itens);
	}
	
	###############################
	######### Busca Itens #########
	###############################
	

	function buscaDadosItem($id) {
		$query = "
			SELECT
  			tab_servidores_links.sel_id AS id,
  			tab_servidores_links.sel_nome AS nome,
  			tab_servidores_links.sei_id AS sei_id,
  			tab_servidores_links.sel_endereco_ip AS endereco_ip,
  			tab_servidores_links.sel_download AS download,
  			tab_servidores_links.sel_upload AS upload,
  			tab_servidores_links_status.sls_id AS status_id,
  			tab_servidores_links_status.sls_nome AS status_nome,
  			CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS servidor_nome
			FROM
  			tab_servidores_links,
  			tab_servidores_links_status,
  			tab_servidores_interfaces,
  			tab_servidores
			WHERE
  			tab_servidores_links.sls_id = tab_servidores_links_status.sls_id AND
  			tab_servidores_links.sei_id = tab_servidores_interfaces.sei_id AND
  			tab_servidores.ser_id = tab_servidores_interfaces.ser_id AND
  			tab_servidores_links.sel_id = $id";
		$query = $this->bancodedados->query($query);
		$this->dados_item = $this->bancodedados->resultadoArray($query);
	}
	
	#########################
	######### Geral #########
	#########################
	

	function buscaInterfaces($link_id = 0) {
		$servidorInterfaces = array();
		$linksExistentes = array();
		$query = "
    	SELECT
    		tab_servidores_interfaces.sei_id AS id,
  			CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS nome
    	FROM
        tab_servidores_interfaces,
        tab_servidores
      WHERE
        (tab_servidores.ser_id = tab_servidores_interfaces.ser_id) AND
        (tab_servidores_interfaces.sit_id = 2)
      ORDER BY nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servidor_interfaces[$dados["id"]] = $dados["nome"];
		}
		$query = "
   		SELECT
    		tab_servidores_interfaces.sei_id AS id,
 				CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS nome
   		FROM
        tab_servidores_interfaces,
        tab_servidores_links,
       	tab_servidores
     	WHERE
        (tab_servidores.ser_id = tab_servidores_interfaces.ser_id) AND
        (tab_servidores_links.sei_id = tab_servidores_interfaces.sei_id) AND
       	(tab_servidores_links.sel_id != $link_id)
     	ORDER BY nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$links_existentes[$dados["id"]] = $dados["nome"];
		}
		return array_diff_assoc($servidor_interfaces, $links_existentes);
	}
	
	#############################
	######### Adicionar #########
	#############################
	

	function adicionar() {
		if (!$_POST) {
			$this->conteudo .= $this->carregaArquivo("servidores_links.adicionar.inc.php");
			$this->conteudo = str_replace('[servidor_interface]', $this->geraSelect($this->buscaInterfaces(), null, true, "servidor_interface", "280px"), $this->conteudo);
			$this->conteudo = str_replace('[unidade_download]', $this->geraSelect($this->buscaUnidades(), null, false, "unidade_download"), $this->conteudo);
			$this->conteudo = str_replace('[unidade_upload]', $this->geraSelect($this->buscaUnidades(), null, false, "unidade_upload"), $this->conteudo);
			// Botões
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=adicionar', $this->conteudo);
		} else {
			$_POST["download"] = $_POST["download"] * pow(1024, $_POST["unidade_download"]);
			$_POST["upload"] = $_POST["upload"] * pow(1024, $_POST["unidade_upload"]);
			$query = "
				INSERT INTO
					tab_servidores_links (sel_nome, sel_download, sel_upload, sei_id, sls_id)
				VALUES
					('{$_POST["nome"]}', '{$_POST["download"]}', '{$_POST["upload"]}', '{$_POST["servidor_interface"]}', 2)";
			$this->bancodedados->query($query);
			$id = $this->bancodedados->ultimoIdInserido();
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$id}");
		}
	}
	
	#######################
	######### Ver #########
	#######################
	

	function ver() {
		$this->buscaDadosItem($_GET["i"]);
		if (!empty($this->dados_item)) {
			$unidades = $this->buscaUnidades();
			$unidade_download = floor(log($this->dados_item["download"], 1024));
			$download = round($this->dados_item["download"] / pow(1024, $unidade_download), 2) . " " . $unidades[$unidade_download];
			$upload = round($this->dados_item["upload"] / pow(1024, $unidade_download), 2) . " " . $unidades[$unidade_download];
			$interface = $this->buscaInterfaces($this->dados_item[id]);
			$this->conteudo .= $this->carregaArquivo("servidores_links.ver.inc.php");
			$this->conteudo = str_replace('[id]', $this->dados_item[id], $this->conteudo);
			$this->conteudo = str_replace('[nome]', $this->dados_item[nome], $this->conteudo);
			$this->conteudo = str_replace('[servidor_interface]', $interface[$this->dados_item[sei_id]], $this->conteudo);
			$this->conteudo = str_replace('[download]', $download, $this->conteudo);
			$this->conteudo = str_replace('[upload]', $upload, $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item[status_nome], $this->conteudo);
			// Botões
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dc"])
				$botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dc&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
	}
	
	##########################
	######### Editar #########
	##########################
	

	function editar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$unidade_download = floor(log($this->dados_item["download"], 1024));
				$unidade_upload = floor(log($this->dados_item["upload"], 1024));
				$download = round($this->dados_item["download"] / pow(1024, $unidade_download), 2);
				$upload = round($this->dados_item["upload"] / pow(1024, $unidade_upload), 2);
				$this->conteudo .= $this->carregaArquivo("servidores_links.editar.inc.php");
				$this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace('[servidor_interface]', $this->geraSelect($this->buscaInterfaces($_GET["i"]), $this->dados_item["sei_id"], true, "servidor_interface", "280px"), $this->conteudo);
				$this->conteudo = str_replace('[download]', $download, $this->conteudo);
				$this->conteudo = str_replace('[upload]', $upload, $this->conteudo);
				$this->conteudo = str_replace('[unidade_download]', $this->geraSelect($this->buscaUnidades(), $unidade_download, false, "unidade_download"), $this->conteudo);
				$this->conteudo = str_replace('[unidade_upload]', $this->geraSelect($this->buscaUnidades(), $unidade_upload, false, "unidade_upload"), $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				// Botões
				if ($this->dados_item["status_id"] != 1 && $this->permissoes[$this->dados_modulo["nome"]]["ativar"])
					$botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->dados_item["status_id"] == 1 && $this->permissoes[$this->dados_modulo["nome"]]["desativar"])
					$botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"])
					$botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
				$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"], $this->conteudo);
			} else {
				$_POST["download"] = $_POST["download"] * pow(1024, $_POST["unidade_download"]);
				$_POST["upload"] = $_POST["upload"] * pow(1024, $_POST["unidade_upload"]);
				$query = "
					UPDATE
						tab_servidores_links
					SET
						sel_nome = '{$_POST["nome"]}',
						sel_download = {$_POST["download"]},
						sel_upload = {$_POST["upload"]},
						sei_id = '{$_POST["servidor_interface"]}'
					WHERE
						sel_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
	}
	
	##########################
	######### Listar #########
	##########################
	

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
	
	function inicializaFiltro($valores = "") {
		$itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_servidores_links.sel_nome LIKE '%[valor]%')");
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		return $filtro;
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		return $paginacao;
	}
	
	function geraListagem($itens) {
		$cabecalho = array("contador" => "#", "nome" => "Nome", "servidor_nome" => "Interface", "velocidade" => "Velocidade");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
		return $listagem;
	}
	
	##########################
	######### Ativar #########
	##########################
	

	function ativar() {
		if ($_GET["i"]) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("servidores_links.ativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $_GET["i"], $this->conteudo);
			} else {
				$query = "UPDATE tab_servidores_links SET sls_id = 1 WHERE sel_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}
	
	##########################
	######## Desativar #######
	##########################
	

	function desativar() {
		if ($_GET["i"]) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("servidores_links.desativar.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $_GET["i"], $this->conteudo);
			} else {
				$query = "UPDATE tab_servidores_links SET sls_id = 2 WHERE sel_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}
	
	###########################
	######### Remover #########
	###########################
	

	function remover() {
		$this->buscaDadosItem($_GET["i"]);
		if (!empty($this->dados_item)) {
			if (empty($_POST["confirmar"])) {
				$this->conteudo .= $this->carregaArquivo("servidores_links.remover.inc.php");
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '&cliente_id=' . $this->dados_item["cliente_id"], $this->conteudo);
			} else {
				$query = "DELETE FROM tab_servidores_links WHERE sel_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
		}
	}

}

?>
