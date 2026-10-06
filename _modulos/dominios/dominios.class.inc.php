<?

class dominios {
	
	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;
	
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
	
	function geraTitulo() {
		$titulo = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';
		return $titulo;
	}
	
	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__) . "/" . $arquivo))
			$arquivo = dirname(__FILE__) . "/" . $arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}
	
	function buscaDadosModulo($nome) {
		$query = "SELECT mod_id AS id, mod_nome AS nome, mod_descricao AS descricao FROM tab_modulos WHERE mod_nome = '$nome'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_modulo = $this->bancodedados->resultadoArray($query);
		}
	}
	
	function buscaDadosAcao($nome) {
		$query = "SELECT aco_id AS id, aco_nome AS nome, aco_descricao AS descricao, act_id AS tipo FROM tab_acoes WHERE aco_nome = '$nome' and mod_id = '{$this->dados_modulo["id"]}'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			$this->dados_acao = $this->bancodedados->resultadoArray($query);
			$this->acao = $this->dados_acao["nome"];
		}
	}
	
	function buscaDadosCliente($id) {
		$query = "SELECT cli_id AS id, cli_nome AS nome FROM tab_clientes WHERE cli_id = '$id'";
		$query = $this->bancodedados->query($query);
		if ($this->bancodedados->linhas($query)) {
			return $this->bancodedados->resultadoArray($query);
		}
	}
	
	function buscaOrigens() {
		$query = "SELECT doo_id AS id, doo_nome AS nome FROM tab_dominios_origens ORDER BY doo_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[$dados["id"]] = $dados["nome"];
		}
		return $origens;
	}
	
	function geraSelectOrigens($origens, $selecionado = "") {
		do {
			$origem = current($origens);
			(key($origens) == $selecionado) ? $complemento = " selected" : $complemento = "";
			$select .= '<option value="' . key($origens) . '" ' . $complemento . '>' . $origem . '</option>';
		} while (next($origens));
		return $select;
	}
	
	function buscaStatus() {
		$query = "SELECT dos_id AS id, dos_nome AS nome FROM tab_dominios_status ORDER BY dos_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$statuss[$dados["id"]] = $dados["nome"];
		}
		return $statuss;
	}
	
	function buscaPlanos() {
		$query = "
      SELECT
        pla_id AS id,
        pla_nome AS nome
      FROM
        tab_planos
      WHERE
        pls_id = 11
      ORDER BY
        nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[] = $dados;
		}
		return $planos;
	}
	
	function buscaDadosItem($id) {
		$query = "
      SELECT
        tab_assinaturas.cli_id AS cliente,
        tab_planos.pla_nome AS plano,
        tab_assinaturas.ass_id AS assinatura_id,
        tab_dominios.doo_id AS origem,
        tab_dominios.dom_responsavel AS responsavel,
        tab_dominios.dom_fapesp AS fapesp,
        tab_dominios.dom_dominio AS dominio,
        tab_dominios.dom_emails AS emails,
        tab_dominios.dom_comentarios AS comentarios,
        tab_dominios.dos_id AS status
      FROM
        tab_dominios
        INNER JOIN tab_assinaturas ON (tab_dominios.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_dominios.dom_id = '$id'";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$this->dados_item["id"] = $id;
			$origens = $this->buscaOrigens();
			$this->dados_item["assinatura_id"] = $dados["assinatura_id"];
			$this->dados_item["plano"] = $dados["plano"];
			$this->dados_item["origem_id"] = $dados["origem"];
			$this->dados_item["origem_nome"] = $origens[$dados["origem"]];
			$this->dados_item["responsavel"] = $dados["responsavel"];
			$this->dados_item["fapesp"] = $dados["fapesp"];
			$this->dados_item["dominio"] = $dados["dominio"];
			$this->dados_item["emails"] = $dados["emails"];
			$this->dados_item["comentarios"] = $dados["comentarios"];
			$status = $this->buscaStatus();
			$this->dados_item["status_id"] = $dados["status"];
			$this->dados_item["status_nome"] = $status[$dados["status"]];
			if ($dados["status"] < 3 && !$this->buscaContratoDominio($dados["assinatura_id"])) {
				$this->dados_item["status_nome"] .= " <b><font color=\"red\">(Sem Contrato)</font></b>";
			}
			$cliente = $this->buscaDadosCliente($dados["cliente"]);
			$this->dados_item["cliente_id"] = $dados["cliente"];
			$this->dados_item["cliente_nome"] = $cliente["nome"];
		} else {
			unset($this->dados_item);
		}
	}
	
	function corrigeNome($nome) {
		$minusculas = array("da", "de", "do", "das", "dos", "e");
		$maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
		$nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
		$nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
		if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
			$nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
			$nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
		}
		for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
		for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
		$nome = trim($nome); // Remove excessos do início e do fim
		return $nome;
	}
	
	function ehContratoAtivavel() {
		$contrato_id = $this->buscaContratoDominio($this->dados_item["assinatura_id"]);
		if ($contrato_id) {
			$query = "
        SELECT
          tab_dominios.dom_id AS id
        FROM
          rel_ass_cor_estacontido
          INNER JOIN tab_dominios ON (tab_dominios.ass_id = rel_ass_cor_estacontido.ass_id)
        WHERE
          cor_id = $contrato_id AND
          dos_id = 1";
			$query = $this->bancodedados->query($query);
			$dados = $this->bancodedados->resultadoArray($query);
			if ($dados) {
				return false;
			} else {
				return true;
			}
		}
	}
	
	function buscaContratoDominio($assinatura_id) {
		$query = "
      SELECT
        tab_contratos.cor_id AS id
      FROM
        rel_ass_cor_estacontido
        INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
      WHERE
        rel_ass_cor_estacontido.ass_id = $assinatura_id AND
        tab_contratos.crs_id < 19
      ORDER BY
        tab_contratos.cor_pai
      LIMIT 0, 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			return $dados["id"];
		}
	}
	
	function buscaClientesDominios($cliente_id) {
		$query = "
      SELECT
        tab_dominios.dom_id AS id,
        tab_planos.pla_nome AS plano,
        tab_dominios.dom_dominio AS dominio,
        tab_dominios.dom_emails AS emails,
        tab_dominios.ass_id AS assinatura_id,
        tab_dominios_status.dos_id AS status_id,
        tab_dominios_status.dos_nome AS status
      FROM
        tab_planos_servicos
        INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
        INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
      	INNER JOIN tab_dominios ON (tab_assinaturas.ass_id = tab_dominios.ass_id)
      	INNER JOIN tab_dominios_status ON (tab_dominios.dos_id = tab_dominios_status.dos_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id
      ORDER BY
      	tab_dominios_status.dos_nome,
      	tab_dominios.dom_dominio";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["status_id"] < 3 && !$this->buscaContratoDominio($dados["assinatura_id"])) {
				$dados["status"] .= "<br><b><font color=\"red\">(Sem Contrato)</font></b>";
			}
			if ($this->permissoes["dominios"]["ver"]) {
				$dados["link"] = "javascript:verDominio({$dados["id"]})";
			}
			$dominios[] = $dados;
		}
		return $dominios;
	}
	
	function geraClientesDominiosListagem($dados) {
		$cabecalho = array("plano" => "Plano", "dominio" => "Domínio", "emails" => "E-mail(s)", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, null, null, array("left", "left"));
		return $listagem->conteudo;
	}
	
	##############
	### Fechar ###
	##############
	

	function fechar() {
		$this->conteudo = '<script type="text/javascript">window.close();</script>';
	}
	
	###########
	### Ver ###
	###########
	

	function ver() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			$this->conteudo .= $this->carregaArquivo("dominios.ver.inc.php");
			$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace('[plano]', $this->dados_item["plano"], $this->conteudo);
			$this->conteudo = str_replace('[origem]', ($this->dados_item["status_id"] != 1) ? $this->dados_item["origem_nome"] : $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[responsavel]', ($this->dados_item["status_id"] != 1) ? $this->dados_item["responsavel"] : $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[fapesp]', ($this->dados_item["status_id"] != 1) ? $this->dados_item["fapesp"] : $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[dominio]', ($this->dados_item["status_id"] != 1) ? $this->dados_item["dominio"] : $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[emails]', ($this->dados_item["status_id"] != 1) ? $this->dados_item["emails"] : $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', ($this->dados_item["status_id"] != 1) ? $this->dados_item["comentarios"] : $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && $this->dados_item["status_id"] != 5)
				$botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->ehContratoAtivavel())
				$botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["bloquear"] && $this->dados_item["status_id"] == 3)
				$botoes[] = '<input type="button" name="bloquear" class="buttonAzulEscuroBranco11" value="Bloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=bloquear&i=' . $this->dados_item["id"] . '\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["desbloquear"] && $this->dados_item["status_id"] == 4)
				$botoes[] = '<input type="button" name="desbloquear" class="buttonAzulEscuroBranco11" value="Desbloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desbloquear&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
		} else {
			$this->fechar();
		}
	}
	
	##############
	### Editar ###
	##############
	

	function editar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("dominios.editar.inc.php");
				$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[plano]', $this->dados_item["plano"], $this->conteudo);
				$this->conteudo = str_replace('[origem]', $this->geraSelectOrigens($this->buscaOrigens(), $this->dados_item["origem_id"]), $this->conteudo);
				$this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel"], $this->conteudo);
				$this->conteudo = str_replace('[fapesp]', $this->dados_item["fapesp"], $this->conteudo);
				$this->conteudo = str_replace('[dominio]', $this->dados_item["dominio"], $this->conteudo);
				$this->conteudo = str_replace('[bloqueio_ativo]', ($this->dados_item["status_id"] > 2 && $this->dados_item["dominio"]) ? "disabled" : "", $this->conteudo);
				$this->conteudo = str_replace('[emails]', $this->dados_item["emails"], $this->conteudo);
				$this->conteudo = str_replace('[comentarios]', $this->dados_item["comentarios"], $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->ehContratoAtivavel())
					$botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["bloquear"] && $this->dados_item["status_id"] == 3)
					$botoes[] = '<input type="button" name="bloquear" class="buttonAzulEscuroBranco11" value="Bloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=bloquear&i=' . $this->dados_item["id"] . '\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["desbloquear"] && $this->dados_item["status_id"] == 4)
					$botoes[] = '<input type="button" name="desbloquear" class="buttonAzulEscuroBranco11" value="Desbloquear" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desbloquear&i=' . $this->dados_item["id"] . '\';">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
			} else {
				$_POST["responsavel"] = $this->corrigeNome($_POST["responsavel"]);
				$_POST["dominio"] = preg_replace("/^www\\./", "", trim(strtolower($_POST["dominio"])));
				$_POST["origem"] = $this->dados_item["origem_id"] ? $this->dados_item["origem_id"] : $_POST["origem"];
				$_POST["dominio"] = $this->dados_item["dominio"] ? $this->dados_item["dominio"] : $_POST["dominio"];
				$status = $this->dados_item["status_id"] > 2 ? $this->dados_item["status_id"] : 2;
				$query = "UPDATE tab_dominios SET doo_id = {$_POST["origem"]}, dos_id = $status, dom_fapesp = '{$_POST["fapesp"]}', dom_responsavel = '{$_POST["responsavel"]}', dom_dominio = '{$_POST["dominio"]}', dom_emails = '{$_POST["emails"]}', dom_comentarios = '{$_POST["comentarios"]}' WHERE dom_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			$this->fechar();
		}
	}
	
	####################
	### Mudar Status ###
	####################
	

	function bloquear() {
		$this->mudaStatus("4", 0, 0);
	}
	
	function desbloquear() {
		$this->mudaStatus("3", 0, 0);
	}
	
	function mudaStatus($status, $ativar_contrato, $cancelar_contrato) {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("dominios.mudar_status.inc.php");
				$this->conteudo = str_replace('[nome]', $this->dados_item["dominio"], $this->conteudo);
				$this->conteudo = str_replace('[acao]', $this->acao, $this->conteudo);
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=' . $this->acao . '&i=' . $this->dados_item["id"], $this->conteudo);
			} else {
				$query = "UPDATE tab_dominios SET dos_id = '$status' WHERE dom_id = '{$_GET["i"]}'";
				$this->bancodedados->query($query);
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
			}
		} else {
			$this->fechar();
		}
	}
	
	##############
	### Ativar ###
	##############
	

	function ativar() {
		$this->buscaDadosItem($_GET["i"]);
		if ($this->dados_item) {
			if (!$_POST) {
				$this->conteudo .= $this->carregaArquivo("dominios.ativar.inc.php");
				$this->conteudo = str_replace("[nome]", $this->dados_item["dominio"], $this->conteudo);
				$this->conteudo = str_replace("[acao]", $this->acao, $this->conteudo);
				$this->conteudo = str_replace("[data_ativacao]", date("d/m/Y"), $this->conteudo);
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$this->dados_item["id"]}", $this->conteudo);
			} else {
				if ($_POST["data_ativacao"]) {
					$_POST["data_ativacao"] = substr($_POST["data_ativacao"], 6, 4) . "-" . substr($_POST["data_ativacao"], 3, 2) . "-" . substr($_POST["data_ativacao"], 0, 2);
				} else {
					$_POST["data_ativacao"] = date("Y-m-d");
				}
				$contrato_id = $this->buscaContratoDominio($this->dados_item["assinatura_id"]);
				if ($contrato_id) {
					$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
					$dados_contrato = $contratos->buscaDadosContrato($contrato_id);
					if ($contratos->dados_item["pai"]) {
						$contratos->ativar_contrato_aditivo_alteracao($dados_contrato, $_POST["data_ativacao"], 20);
					} else {
						$contratos->ativar_contrato_principal($dados_contrato, $_POST["data_ativacao"], 20);
					}
					// Registra Atividade
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
				}
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
		} else {
			$this->fechar();
		}
	}
	
	##############
	### Listar ###
	##############
	

	function listar() {
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
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
		$itens[] = array("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
		// Plano
		$planos = $this->buscaPlanos();
		$itens[] = array("tipo" => "select", "nome" => "planos", "descricao" => "Plano", "valor" => $planos, "where" => "(tab_planos.pla_id = [valor])");
		// Domínio
		$itens[] = array("tipo" => "input", "nome" => "dominio", "descricao" => "Domínio", "valor" => "", "where" => "(tab_dominios.dom_dominio LIKE '%[valor]%')");
		// Status
		$status = $this->buscaStatus();
		foreach ($status as $id => $nome) {
			$select_status[] = array("id" => $id, "nome" => $nome);
		}
		$itens[] = array("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_dominios_status.dos_id = [valor])");
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
			  tab_dominios.dom_id AS id,
			  tab_dominios.dom_dominio AS dominio,
			  tab_dominios_status.dos_nome AS status,
			  tab_planos.pla_nome AS plano,
			  tab_clientes.cli_id AS cliente_id,
			  tab_clientes.cli_nome AS cliente
			FROM
			  tab_dominios
			  INNER JOIN tab_dominios_status ON (tab_dominios.dos_id = tab_dominios_status.dos_id)
			  INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_dominios.ass_id)
			  INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			  INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
			WHERE
			  $filtro->parametros_sql
			ORDER BY
			  tab_dominios_status.dos_nome,
			  tab_dominios.dom_dominio,
			  tab_planos.pla_nome
			LIMIT
			  $limit_inicio,
			  $limit_fim";
		$query = $this->bancodedados->query($query);
		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array();
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}";
			}
			$dados["plano"] = "<div style=\"width:220px\">" . str_replace(" [", "<br>[", $dados["plano"]) . "</div>";
			$dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$itens[] = $dados;
		}
		return array("linhas" => $linhas, "itens" => $itens);
	}
	
	function geraListagem($itens) {
		$cabecalho = array("contador" => "#", "cliente" => "Cliente", "plano" => "Plano", "dominio" => "Domínio", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "265", "210", "176", "70"), null, array("right"));
		return $listagem;
	}

}

?>
