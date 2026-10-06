<?

require_once("_global/modulo.class.inc.php");

class clientes_telefones extends modulo {
	
	var $integracao;

	function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		$this->buscaDadosModulo(__CLASS__);
		if ($executar) {
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}
	
	function buscaDadosItem($id) {
		$query = "
			SELECT
			  tab_clientes_telefones.clt_id AS id,
				tab_clientes_telefones.clt_numero AS numero,
				tab_clientes_telefones_status.cts_id AS status_id,
				tab_clientes_telefones_status.cts_nome AS status_nome,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_planos.pla_nome AS assinatura
			FROM
				tab_clientes
				INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				INNER JOIN tab_planos	ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			  INNER JOIN tab_clientes_telefones ON (tab_assinaturas.ass_id = tab_clientes_telefones.ass_id)
			  INNER JOIN tab_clientes_telefones_status ON (tab_clientes_telefones.cts_id = tab_clientes_telefones_status.cts_id)
			WHERE
			  tab_clientes_telefones.clt_id = $id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		$this->dados_item = $dados;
		if ($this->dados_item) {
			$this->dados_item += $this->buscaInfoTelefone($this->dados_item["numero"]);
		}
	}
	
	function buscaInfoTelefone($numero) {
		if (!$this->integracao) {
			$this->integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
		}
		return $this->integracao->buscaInfoTelefone($numero);
	}
	
	function buscaTelefonesOnline() {
		//$integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
		//return $integracao->buscaTelefonesOnline();
	}
	
	##############
	### Fechar ###
	##############

	function fechar() {
		$this->conteudo = '<script type="text/javascript">window.close();</script>';
	}
	
	#################
	### Adicionar ###
	#################
	
	function adicionar() {
		if (!$_POST) {
			$assinaturas = $this->buscaAssinaturasCliente($_GET["i"]);
			if (count($assinaturas) == 1) {
				$dados_assinatura = $this->buscaDadosAssinatura(key($assinaturas));
			}
			$this->conteudo .= $this->carregaArquivo("clientes_telefones.adicionar.inc.php");
			$this->conteudo = str_replace('[assinaturas]', $this->geraSelect($assinaturas, count($assinaturas) == 1 ? key($assinaturas) : null, true, "assinatura", "100%", "mudaAssinatura(this.value)"), $this->conteudo);
			$this->conteudo = str_replace('[tabela_venda]', $dados_assinatura ? ($dados_assinatura["parametros"]["telefonia_tabela_venda_id"] ? $this->buscaTabelaVenda($dados_assinatura["parametros"]["telefonia_tabela_venda_id"]) : $this->geraSelect($this->buscaTabelasVenda(), null, true, "tabela_venda", "100%")) : "", $this->conteudo);
			$this->conteudo = str_replace('[tabela_roteamento]', $dados_assinatura ? ($dados_assinatura["parametros"]["telefonia_tabela_roteamento_id"] ? $this->buscaTabelaRoteamento($dados_assinatura["parametros"]["telefonia_tabela_roteamento_id"]) : $this->geraSelect($this->buscaTabelasRoteamento(), null, true, "tabela_roteamento", "100%")) : "", $this->conteudo);
			$this->conteudo = str_replace('[erro]', $_GET["erro"], $this->conteudo);
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" style="width:45px" value="Salvar">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:50px" value="Fechar" onclick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&i={$_GET["i"]}", $this->conteudo);	
		} else {
			$integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
			$retorno = $integracao->registrar($_POST["assinatura"], $_POST["tabela_venda"], $_POST["tabela_roteamento"]);
			if ($retorno && is_numeric($retorno)) {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$retorno");
			} else {
				$retorno["erro"] = $retorno["erro"] ? $retorno["erro"] : "Erro durante registro do usuário";
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar&i={$_GET["i"]}&erro={$retorno["erro"]}");
			}
		}
	}
	
	function buscaTabelasVenda() {
		if (!$this->integracao) {
			$this->integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
		}
		$tabelas_venda = $this->integracao->buscaTabelasVendas();
		$tabelas = array();
  	foreach ($tabelas_venda as $tabela) {
  		$tabelas[$tabela["id"]] = $tabela["name"];
  	}
  	return $tabelas;
	}
	  
  function buscaTabelaVenda($tabela_venda_id) {
		if (!$this->integracao) {
			$this->integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
		}
  	return $this->integracao->buscaTabelaVenda($tabela_venda_id);
  }
	
	function buscaTabelasRoteamento() {
		if (!$this->integracao) {
			$this->integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
		}
		$tabelas_roteamento = $this->integracao->buscaTabelasRoteamento();
		$tabelas = array();
  	foreach ($tabelas_roteamento as $tabela) {
  		$tabelas[$tabela["id"]] = $tabela["name"];
  	}
  	return $tabelas;
	}
	  
  function buscaTabelaRoteamento($tabela_roteamento_id) {
		if (!$this->integracao) {
			$this->integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
		}
  	return $this->integracao->buscaTabelaRoteamento($tabela_roteamento_id);
  }
	
	function buscaAssinaturasCliente($cliente_id) {
		$query = "
			SELECT DISTINCT
				tab_assinaturas.ass_id AS id,
				IF(tab_planos.plt_id = 1, tab_planos.pla_nome, CONCAT('&nbsp;&raquo; ', tab_planos.pla_nome)) AS assinatura,
				tab_assinaturas.ass_parametros AS parametros,
				(SELECT COUNT(*) FROM tab_clientes_telefones WHERE tab_assinaturas.ass_id = tab_clientes_telefones.ass_id) AS linhas
      FROM
      	tab_assinaturas 
      	INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
      	INNER JOIN tab_visitas ON (tab_assinaturas.cli_id = tab_visitas.cli_id)
		  WHERE
		  	tab_assinaturas.cli_id = $cliente_id AND
		  	tab_assinaturas.ast_id IN (1, 2)";
		$query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != null) {
    	$dados["parametros"] = unserialize($dados["parametros"]);
	   	if ($dados["parametros"]["telefonia_linhas"] > $dados["linhas"]) {
				$assinaturas[$dados["id"]] = $dados["assinatura"];			
	   	}
    }
		return $assinaturas;
	}
	
	function buscaDadosAssinatura($assinatura_id) {
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_parametros AS parametros
      FROM
      	tab_assinaturas 
		  WHERE
		  	tab_assinaturas.ass_id = $assinatura_id";
		$query = $this->bancodedados->query($query);
    if (($dados = $this->bancodedados->resultadoArray($query)) != null) {
    	$dados["parametros"] = unserialize($dados["parametros"]);
    	return $dados;
    }
	}
	
	###########
	### Ver ###
	###########
	
	function ver() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("clientes_telefones.ver.inc.php");
      $this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver" => "Ver", "recargas" => "Recargas")), $this->conteudo);
			$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[status_nome]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
			$this->conteudo = str_replace("[senha]", $this->dados_item["senha"], $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[tabela_venda]", $this->dados_item["tabela_venda_nome"], $this->conteudo);
			$this->conteudo = str_replace("[tabela_roteamento]", $this->dados_item["tabela_roteamento_nome"], $this->conteudo);
			// Info
			$this->conteudo = str_replace("[telefone_status]", $this->dados_item["status_telefone"], $this->conteudo);
			$this->conteudo = str_replace("[telefone_user_agent]", $this->dados_item["user_agent"], $this->conteudo);
			$this->conteudo = str_replace("[telefone_ip]", $this->dados_item["ip"], $this->conteudo);
			$this->conteudo = str_replace("[telefone_saldo]", $this->formataDinheiro($this->dados_item["saldo"], 'R$', true), $this->conteudo);
			$this->conteudo = str_replace("[telefone_extrato]", $this->formataDinheiro($this->dados_item["extrato"], 'R$', true), $this->conteudo);
			// Botoes
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["inserir_credito"]) $botoes[] = "<input type=\"button\" name=\"inserir_credito\" class=\"buttonAzulEscuroBranco11\" value=\"Inserir Crédito\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=inserir_credito&i={$_GET["i"]}'\">";
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}
		
	################
	### Recargas ###
	################
	
	function recargas() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("clientes_telefones.recargas.inc.php");
      $this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver" => "Ver", "recargas" => "Recargas")), $this->conteudo);
			$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
			$this->conteudo = str_replace("[recargas]", $this->buscaRecargas($this->dados_item["id"]), $this->conteudo);
			// Info
			$this->conteudo = str_replace("[telefone_status]", $this->dados_item["status_telefone"], $this->conteudo);
			$this->conteudo = str_replace("[telefone_user_agent]", $this->dados_item["user_agent"], $this->conteudo);
			$this->conteudo = str_replace("[telefone_ip]", $this->dados_item["ip"], $this->conteudo);
			$this->conteudo = str_replace("[telefone_saldo]", $this->formataDinheiro($this->dados_item["saldo"], 'R$', true), $this->conteudo);
			$this->conteudo = str_replace("[telefone_extrato]", $this->formataDinheiro($this->dados_item["extrato"], 'R$', true), $this->conteudo);
			// Botoes
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["inserir_credito"]) $botoes[] = "<input type=\"button\" name=\"inserir_credito\" class=\"buttonAzulEscuroBranco11\" value=\"Inserir Crédito\" onClick=\"window.location='?m={$this->dados_modulo["nome"]}&a=inserir_credito&i={$_GET["i"]}'\">";
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
		} else {
			$this->fechar();
		}
	}
	
	function buscaRecargas($cliente_telefone_id) {
		$query = "
			SELECT
				DATE_FORMAT(ctr_data_recarga, '%d/%m/%Y') AS data,
				DATE_FORMAT(ctr_data_vencimento, '%d/%m/%Y') AS vencimento,
				ctr_valor_recarga AS valor,
				ctr_observacao AS observacao,
				usu_nome AS usuario
			FROM
				tab_clientes_telefones_recargas
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes_telefones_recargas.usu_id = tab_usuarios.usu_id)
			WHERE
				clt_id = $cliente_telefone_id
			ORDER BY
			  ctr_data_recarga DESC";
		$query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != null) {
    	$dados["valor"] = $this->formataDinheiro($dados["valor"]);
    	$itens[] = $dados;
    }
		$cabecalho = array ("data" => "Data", "valor" => "Valor", "observacao" => "Observação", "usuario" => "Usuário");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(60, 70, 0, 150));
    return $listagem->conteudo;
	}
	
	#######################
	### Inserir Crédito ###
	#######################
	
	function inserir_credito() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("clientes_telefones.inserir_credito.inc.php");
				$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[assinatura]", $this->dados_item["assinatura"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
				$this->conteudo = str_replace("[telefone_saldo]", $this->formataDinheiro($this->dados_item["saldo"], 'R$', true), $this->conteudo);
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=inserir_credito&i={$_GET["i"]}", $this->conteudo);
			} else {
				if (!$this->integracao) {
					$this->integracao = controle::instanciaObjeto("clientes_telefones.integracao", $this->bancodedados);
				}
				$this->integracao->inserirCredito($this->dados_item["numero"], $_POST["valor"], $_POST["observacao"]);
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}");
			}
		} else {
			$this->fechar();
		}		
	}
		
	##############
	### Buscar ###
	##############
	
	function buscar() {
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=clientes_telefones"></script>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
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
		// Cliente
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
		// Número
		$itens[] = array ("tipo" => "input", "nome" => "numero", "descricao" => "Número", "valor" => "", "where" => "(tab_clientes_telefones.clt_numero LIKE '%[valor]%')");
		// Status
		$status = $this->buscaStatus();
//		$status[-1] = "On line";
//		$status[-2] = "Off line";
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		if ($valores["status"] == -1) {
			$online = implode(",", $this->buscaTelefonesOnline());
			$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_clientes_telefones.clt_numero IN ($online))");
		} elseif ($valores["status"] == -2) {
			$online = implode(",", $this->buscaTelefonesOnline());
			$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_clientes_telefones.clt_numero NOT IN ($online))");
		} else {
			$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_clientes_telefones.cts_id = [valor])");
		}
		// Filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		return $filtro;
	}

	function geraPaginacao($filtro, $total, $pagina) {
		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		return $paginacao;
	}

	function buscaItens($filtro, $pagina) {
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes_telefones.clt_id AS id,
				tab_clientes_telefones.clt_numero AS numero,
				tab_clientes_telefones_status.cts_id AS status_id,
				tab_clientes_telefones_status.cts_nome AS status_nome
			FROM
				tab_clientes_telefones
				INNER JOIN tab_clientes_telefones_status ON (tab_clientes_telefones.cts_id = tab_clientes_telefones_status.cts_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_telefones.ass_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
			WHERE
				$filtro->parametros_sql
			ORDER BY
				tab_clientes.cli_nome
			LIMIT
				$limit_inicio,
				$limit_fim";
		$query = $this->bancodedados->query($query);
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados += $this->buscaInfoTelefone($dados["numero"]);
			if (strlen($dados["cliente_nome"]) > 50) {
				$dados["cliente_nome"] = substr($dados["cliente_nome"], 0, 50)."...";
			}
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verTelefone('{$dados["id"]}');";
			}
			if ($dados["status_id"] != 4 && $dados["status_telefone"]) {
				$dados["status_nome"] .= " / {$dados["status_telefone"]}";
			}
			$dados["saldo"] = $this->formataDinheiro($dados["saldo"], 'R$', true);
			// Dados Cliente
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$itens[] = $dados;
		}
		return array("linhas" => $linhas, "itens" => $itens);
	}

	function geraListagem($itens) {
		$cabecalho = array ("contador" => "#", "cliente_id" => "ID", "cliente_nome" => "Cliente", "numero" => "Número", "senha" => "Senha", "saldo" => "Saldo", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "35", "", "", "80", "80", "120"), null, array("right", "center", "left", "center", "left", "right", "center"));
		return $listagem;
	}
	
	function buscaStatus() {
		$query = "SELECT cts_id AS id, cts_nome AS nome FROM tab_clientes_telefones_status ORDER BY nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		return $status;
	}
	
	##############################
	### buscaClientesTelefones ###
	##############################

	function buscaClientesTelefones($cliente_id) {
		$query = "
			SELECT
			  tab_clientes_telefones.clt_id AS id,
				tab_clientes_telefones.clt_numero AS numero,
				tab_clientes_telefones_status.cts_id AS status_id,
				tab_clientes_telefones_status.cts_nome AS status_nome,
				tab_planos.pla_nome AS assinatura
			FROM
				tab_assinaturas
				INNER JOIN tab_planos	ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			  INNER JOIN tab_clientes_telefones ON (tab_assinaturas.ass_id = tab_clientes_telefones.ass_id)
			  INNER JOIN tab_clientes_telefones_status ON (tab_clientes_telefones.cts_id = tab_clientes_telefones_status.cts_id)
			WHERE
			  tab_assinaturas.cli_id = $cliente_id
			ORDER BY
			  tab_clientes_telefones.cts_id,
			  tab_clientes_telefones.clt_numero";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados += $this->buscaInfoTelefone($dados["numero"]);
			if ($dados["status_id"] != 4 && $dados["status_telefone"]) {
				$dados["status_nome"] .= " / {$dados["status_telefone"]}";
			}
			if ($this->permissoes["clientes_telefones"]["ver"]) {
				$dados["link"] = "javascript:verTelefone({$dados["id"]})";
			}
			$telefones[] = $dados;
		}
		return $telefones;
	}

	function geraClientesTelefonesListagem($dados) {
		$cabecalho = array ("assinatura" => "Assinatura", "numero" => "Número", "senha" => "Senha", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("", "150", "150", "120"), null, array("center"));
		return $listagem->conteudo;
	}
	
}

?>
