<?

require_once ("_global/modulo.class.inc.php");

class usuarios extends modulo {
	
    function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			
			if ($_GET["a"] == "ver_dc" && $this->permissoes[$_GET["m"]]["editar_dc"]) $_GET["a"] = "editar_dc";
			if ($_GET["a"] == "ver_da" && $this->permissoes[$_GET["m"]]["editar_da"]) $_GET["a"] = "editar_da";
			if ($_GET["a"] == "ver_dp" && $this->permissoes[$_GET["m"]]["editar_dp"]) $_GET["a"] = "editar_dp";
			
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			
			$this->conteudo = $this->geraTitulo();
			
			$this->{$this->acao}();
			
		}
		
	}
	
	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome,
				tab_usuarios.usu_cpf AS cpf,
				DATE_FORMAT(tab_usuarios.usu_nascimento,'%d/%m/%Y') AS nascimento,
				INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_nome AS estado,
				tab_usuarios.usu_numero AS numero,
				tab_usuarios.usu_complemento AS complemento,
				tab_usuarios.usu_tel_residencial AS tel_residencial,
				tab_usuarios.usu_tel_celular AS tel_celular,
				tab_usuarios.usu_ramal AS ramal,
				tab_usuarios.usu_agente_id AS agente_id,
				tab_usuarios.usu_agente_senha AS agente_senha,
				tab_usuarios.usu_senha_discagem AS senha_discagem,
				tab_usuarios.usu_email AS email,
				tab_usuarios.usu_login AS login,
				tab_usuarios.usu_senha AS senha,
				tab_usuarios.usu_comentarios AS comentarios,
				tab_usuarios.usu_administrador AS administrador,
				tab_usuarios_status.uss_id AS status_id,
				tab_usuarios_status.uss_nome AS status_nome,
				tab_clientes_origens.clo_id AS origem_id,
				tab_clientes_origens.clo_nome AS origem_nome
			FROM
				tab_usuarios
				INNER JOIN tab_clientes_origens ON (tab_usuarios.clo_id = tab_clientes_origens.clo_id)
				INNER JOIN tab_usuarios_status ON (tab_usuarios.uss_id = tab_usuarios_status.uss_id)
				LEFT OUTER JOIN tab_ceps ON (tab_usuarios.cep_id = tab_ceps.cep_id)
				LEFT OUTER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				LEFT OUTER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				LEFT OUTER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_usuarios.usu_id = $id");
		
		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
			$this->dados_item["grupos"] = $this->buscaItemGrupos($this->dados_item["id"]);
			$this->dados_item["setores_funcoes"] = $this->buscaItemSetoresFuncoes($this->dados_item["id"]);
		}
		
	}
	
	###################
	### Busca Itens ###
	###################
	
	function buscaStatus() {
		
		$status = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				uss_id AS id, 
				uss_nome AS nome 
			FROM 
				tab_usuarios_status 
			ORDER BY 
				nome");
		
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

	#############
	### Geral ###
	#############

	function corrigeNome($nome) {
		
		$minusculas = array("da", "de", "do", "das", "dos", "e");
		$maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
		
		$nome = str_replace(".", ". ", $nome);
		$nome = preg_replace("/[\r\n\t ]+/", " ", $nome);
		
		if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
			$nome = strtolower($nome);
			$nome = ucwords($nome);
		}
		
		for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
		for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
		
		return trim($nome);
		
	}
	
	#################################
	### Grupos, Setores e Funções ###
	#################################

	function buscaGrupos() {
		
		$grupos = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				gru_id AS id, 
				gru_nome AS nome 
			FROM 
				tab_grupos 
			ORDER BY 
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$grupos[$dados["id"]] = $dados["nome"];
		}
		
		return $grupos;
		
	}
	
	function buscaSetores($setor_pai = "") {
		
		$setores = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ors_id AS id,
				ors_nome AS nome,
				ors_pai AS pai
			FROM
				tab_organizacao_setores
			WHERE".(!$setor_pai ? "
				1" : "
				ors_id = $setor_pai"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($setor_pai) {
				return ($dados["pai"] ? $this->buscaSetores($dados["pai"]) . " » " : "").$dados["nome"];
			}
			$setores[$dados["id"]] = ($dados["pai"] ? $this->buscaSetores($dados["pai"])." » " : "").$dados["nome"];
		}
		
		asort($setores);
		
		return $setores;
		
	}
	
	function buscaFuncoes() {
		
		$funcoes = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				orf_id AS id, 
				orf_nome AS nome 
			FROM 
				tab_organizacao_funcoes 
			ORDER BY 
				id DESC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$funcoes[$dados["id"]] = $dados["nome"];
		}
		
		return $funcoes;
		
	}
	
	function buscaItemGrupos($usuario) {
		
		$grupos = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				gru_id AS grupo 
			FROM 
				rel_usu_gru_fazparte 
			WHERE 
				usu_id = '$usuario' 
			ORDER BY 
				grupo");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$grupos[$dados["grupo"]] = true;
		}
		
		return $grupos;
		
	}
	
	function buscaItemListagemGrupos($usuario) {
		
		$grupos = $this->buscaGrupos();
		$item_grupos = array_keys($this->buscaItemGrupos($usuario));
		$listagem_grupos = array();
		
		for ($i = 0; $i < sizeof($item_grupos); $i++) {
			$listagem_grupos[] = $grupos[$item_grupos[$i]];
		}
		
		return count($listagem_grupos) > 0 ? implode(",<br>", $listagem_grupos) : "Sem Grupo";
		
	}
	
	function buscaItemSetoresFuncoes($usuario) {
		
		$setores_funcoes = array();
		
		$query = $this->bancodedados->query("
			SELECT 
				ors_id AS setor, 
				orf_id AS funcao 
			FROM 
				rel_usu_orf_ors_exerce 
			WHERE 
				usu_id = '$usuario' 
			ORDER BY 
				setor, funcao");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$setores_funcoes[$dados["setor"]][$dados["funcao"]] = true;
		}
		
		return $setores_funcoes;
		
	}
	
	function buscaItemListagemSetores($usuario) {
		
		$setores = $this->buscaSetores();
		$item_setores = array_keys($this->buscaItemSetoresFuncoes($usuario));
		$listagem_setores = array();
		
		for ($i = 0; $i < sizeof($item_setores); $i++) {
			$listagem_setores[] = $setores[$item_setores[$i]];
		}
		
		return count($listagem_setores) > 0 ? implode(",<br>", $listagem_setores) : "Sem Setor";
		
	}
	
	function buscaGrupoPermissoes($grupo) {
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT 
				tab_modulos.mod_descricao AS modulo, 
				tab_acoes.aco_descricao AS acao 
			FROM 
				rel_gru_aco_tempermissao 
				INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id) 
				INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id) 
			WHERE 
				rel_gru_aco_tempermissao.gru_id = '$grupo'");
				
		if ($this->bancodedados->linhas($query)) {
			$modulos_acoes = array();
			while (($modulo_acao = $this->bancodedados->resultadoArray($query)) != false) {
				$modulos_acoes[$modulo_acao["modulo"]][] = $modulo_acao["acao"];
			}
			$permissoes = array();
			foreach ($modulos_acoes as $modulo => $acoes) {
				$permissoes[] = '<span onMouseOver="hintShow(\''.implode(", ", $acoes).'\');" onMouseOut="hintHide();">'.$modulo."</span>";
			}
			$permissoes = implode(", ", $permissoes);
		} else {
			$permissoes = "Sem Permissões";
		}
		
		return $permissoes;
		
	}
	
	function geraListagemGrupos($grupos_ativos, $editar = false) {
		
		$grupos = $this->buscaGrupos();
		$cabecalho = array();
		$itens = array();
		
		if ($editar) $cabecalho["opcao"] = "&nbsp;";
		$cabecalho["grupo"] = "Grupo";
		$cabecalho["permissoes"] = "Permissões";
		
		foreach ($grupos as $id => $nome) {
			if ($editar || $grupos_ativos[$id]) {
			    
			    $item = array();
			    if ($editar) $item["opcao"] = '<input type="checkbox" class="inputBrancoAzulEscuro11" id="grupos_' . $id . '" name="grupos[' . $id . ']" value="true" ' . (($grupos_ativos[$id]) ? ' checked' : '') . '>';
			    $item["grupo"] = "<label for=\"grupos_" . $id . "\">$nome</label>";
			    $item["permissoes"] = $this->buscaGrupoPermissoes($id);
			    
			    $itens[] = $item;
			    
			}
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
		
		return $listagem->conteudo;
		
	}
	
	function geraListagemSetoresFuncoes($setores_funcoes_ativos, $editar = false) {
		
		$setores = $this->buscaSetores();
		$funcoes = $this->buscaFuncoes();
		$cabecalho = array("setor" => "Setores / Funções");
		$larguras = array("0");
		$itens = array();
		
		foreach ($funcoes as $id => $nome) {
			$larguras[] = "90";
			$cabecalho["funcao_".$id] = $nome;
		}
		
		foreach ($setores as $setor_id => $setor_nome) {
			$item = array("setor" => $setor_nome);
			foreach ($funcoes as $funcao_id => $funcao_nome) {
				$item["funcao_" . $funcao_id] = ($editar) ? '<input type="checkbox" class="inputBrancoAzulEscuro11" name="setores_funcoes[' . $setor_id . '][' . $funcao_id . ']" value="true" ' . (($setores_funcoes_ativos[$setor_id][$funcao_id]) ? ' checked' : '') . '>' : (($setores_funcoes_ativos[$setor_id][$funcao_id]) ? 'Sim' : 'Não');
			}
			if ($editar || $setores_funcoes_ativos[$setor_id]) {
				$itens[] = $item;
			}
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, $larguras);
		
		return $listagem->conteudo;
		
	}
	
	function registraItemGrupos($usuario, $grupos) {
		
		$this->bancodedados->query("DELETE FROM rel_usu_gru_fazparte WHERE usu_id = '$usuario'");
		
		if ($grupos) {
			$grupos = array_keys($grupos);
			for ($i = 0; $i < sizeof($grupos); $i++) {
				$this->bancodedados->query("INSERT INTO rel_usu_gru_fazparte (usu_id, gru_id) VALUES ('$usuario', '$grupos[$i]')");
			}
		}
	}
	
	function registraItemSetoresFuncoes($usuario, $setores_funcoes) {
		
		$this->bancodedados->query("DELETE FROM rel_usu_orf_ors_exerce WHERE usu_id = '$usuario'");
		
		if ($setores_funcoes) {
			$setores = array_keys($setores_funcoes);
			for ($i = 0; $i < sizeof($setores); $i++) {
				$funcoes = array_keys($setores_funcoes[$setores[$i]]);
				for ($j = 0; $j < sizeof($funcoes); $j++) {
					$this->bancodedados->query("INSERT INTO rel_usu_orf_ors_exerce (usu_id, orf_id, ors_id) VALUES ('$usuario','$funcoes[$j]','$setores[$i]')");
				}
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
			
			$this->conteudo .= $this->carregaArquivo("usuarios.adicionar.inc.php");

			$this->conteudo = str_replace("[origem]", $this->geraSelect($this->buscaOrigens(), null, true, "origem", "250px"), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), null, false, "status", "250px"), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
			$this->conteudo = str_replace("[pesquisar_cep]", $this->permissoes["ceps"]["pesquisar"] ? "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />" : "", $this->conteudo);
			$this->conteudo = str_replace("[adicionar_cep]", $this->permissoes["ceps"]["adicionar"] ? "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />" : "", $this->conteudo);
			
		} else {
			
			$_POST["nome"] = addslashes($this->corrigeNome($_POST["nome"]));
			if (!empty($_POST["nascimento"])) {
				$_POST["nascimento"] = explode("/", $_POST["nascimento"]);
				$_POST["nascimento"] = "'".$_POST["nascimento"][2]."-".$_POST["nascimento"][1]."-".$_POST["nascimento"][0]."'";
			} else {
				$_POST["nascimento"] = "NULL";
			}
			if ($_POST["cep"]) {
				$_POST["cep"] = "'".preg_replace("/[^0-9]/", "", $_POST["cep"])."'";
				$_POST["numero"] = preg_replace("/[^0-9]/", "", $_POST["numero"]);
			} else {
				$_POST["cep"] = "NULL";
				$_POST["numero"] = 0;
			}
			$_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "NULL";
			$_POST["complemento"] = addslashes(trim($_POST["complemento"]));
			$_POST["ramal"] = trim($_POST["ramal"]);
			$_POST["login"] = $this->limpaLogin($_POST["login"]);
			$_POST["comentarios"] = addslashes(trim($_POST["comentarios"]));
			$_POST["administrador"] = intval($_POST["administrador"]);
			
			$this->bancodedados->query("
				INSERT INTO 
					tab_usuarios 
				SET
					usu_nome = '{$_POST["nome"]}', 
					usu_cpf = '{$_POST["cpf"]}', 
					usu_nascimento = {$_POST["nascimento"]}, 
					cep_id = {$_POST["cep"]}, 
					usu_numero = {$_POST["numero"]}, 
					usu_complemento = '{$_POST["complemento"]}', 
					usu_tel_residencial = '{$_POST["tel_residencial"]}', 
					usu_tel_celular = '{$_POST["tel_celular"]}', 
					usu_ramal = '{$_POST["ramal"]}', 
					usu_email = '{$_POST["email"]}', 
					usu_login = '{$_POST["login"]}', 
					usu_senha = md5('{$_POST["senha"]}'), 
					usu_comentarios = '{$_POST["comentarios"]}', 
					usu_administrador = '{$_POST["administrador"]}', 
					uss_id = '{$_POST["status"]}',
					clo_id = '{$_POST["origem"]}'");
			
			$id = $this->bancodedados->ultimoIdInserido();
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$id}");
			
		}
		
	}
		
	function limpaLogin($login) {
		
		$login = mb_strtolower($login);
		$login = preg_replace("/[ç]/", "c", $login);
		$login = preg_replace("/[ñ]/", "n", $login);
		$login = preg_replace("/[áàâãä]/", "a", $login);
		$login = preg_replace("/[éèêë]/", "e", $login);
		$login = preg_replace("/[íìîï]/", "i", $login);
		$login = preg_replace("/[óòôõö]/", "o", $login);
		$login = preg_replace("/[úùûü]/", "u", $login);
		
		return preg_replace("/[^a-z0-9]/", "", $login);
		
	}

	############################
	### Ver Dados Cadastrais ###
	############################

	function ver_dc() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dc"]) $botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dc&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

			$this->conteudo .= $this->carregaArquivo("usuarios.ver_dc.inc.php");
			
			$this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[cpf]", $this->dados_item["cpf"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento]", $this->dados_item["nascimento"], $this->conteudo);
			$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
			$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
			$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
			$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
			$this->conteudo = str_replace("[tel_residencial]", $this->dados_item["tel_residencial"], $this->conteudo);
			$this->conteudo = str_replace("[tel_celular]", $this->dados_item["tel_celular"], $this->conteudo);
			$this->conteudo = str_replace("[email]", $this->dados_item["email"], $this->conteudo);
			$this->conteudo = str_replace("[ramal]", $this->dados_item["ramal"], $this->conteudo);
			$this->conteudo = str_replace("[agente_id]", $this->dados_item["agente_id"], $this->conteudo);
			$this->conteudo = str_replace("[agente_senha]", $this->dados_item["agente_senha"], $this->conteudo);
			$this->conteudo = str_replace("[login]", $this->dados_item["login"], $this->conteudo);
			$this->conteudo = str_replace("[comentarios]", nl2br($this->dados_item["comentarios"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[administrador]", ($this->dados_item["administrador"] ? " (Administrador do sistema)" : ""), $this->conteudo);
			$this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_da" => "Dados Administrativos")), $this->conteudo);
			
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function ehAdmin($id = null) {

		$user = $id? $id : $_SESSION["usuario"];
		$query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
		$query = $this->bancodedados->query($query);
		$ehAdmin = $this->bancodedados->resultadoArray($query);

		return $ehAdmin['usu_administrador']== 0 ? false : true;
	}

	function opcoesSimNao() {

		$lista["0"] = "Não";
		$lista["1"] = "Sim";

		return $lista;
	}
	
	###############################
	### Editar Dados Cadastrais ###
	###############################

	function editar_dc() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {

			$ehAdmin = $this->ehAdmin();
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("usuarios.editar_dc.inc.php");
				
				$this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[cpf]", $this->dados_item["cpf"], $this->conteudo);
				$this->conteudo = str_replace("[nascimento]", $this->dados_item["nascimento"], $this->conteudo);
				$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
				$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
				$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
				$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
				$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
				$this->conteudo = str_replace("[tel_residencial]", $this->dados_item["tel_residencial"], $this->conteudo);
				$this->conteudo = str_replace("[tel_celular]", $this->dados_item["tel_celular"], $this->conteudo);
				$this->conteudo = str_replace("[email]", $this->dados_item["email"], $this->conteudo);
				$this->conteudo = str_replace("[ramal]", $this->dados_item["ramal"], $this->conteudo);
				$this->conteudo = str_replace("[agente_id]", $this->dados_item["agente_id"], $this->conteudo);
				$this->conteudo = str_replace("[agente_senha]", $this->dados_item["agente_senha"], $this->conteudo);
				$this->conteudo = str_replace("[senha_discagem]", $this->dados_item["senha_discagem"], $this->conteudo);
				$this->conteudo = str_replace("[login]", $this->dados_item["login"], $this->conteudo);
				$this->conteudo = str_replace("[comentarios]", $this->dados_item["comentarios"], $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"], false, "status", "250px"), $this->conteudo);
				$this->conteudo = str_replace("[origem]", $this->geraSelect($this->buscaOrigens(), $this->dados_item["origem_id"], true, "origem", "250px"), $this->conteudo);
				$this->conteudo = str_replace("[administrador]", ($this->dados_item["administrador"] ? " checked" : ""), $this->conteudo);

				if($ehAdmin) {
					$this->conteudo = str_replace("[verEhAdministrador]", "table-row;", $this->conteudo);
				} else {
					$this->conteudo = str_replace("[verEhAdministrador]", "none", $this->conteudo);
				}

				$this->conteudo = str_replace("[ehAdministrador]", $this->geraSelect($this->opcoesSimNao(), $this->dados_item["administrador"] , true, 'ehAdministrador', '250px'), $this->conteudo);

				$this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_da" => "Dados Administrativos")), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", '?m='.$this->dados_modulo["nome"].'&a=editar_dc&i='.$this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace("[pesquisar_cep]", $this->permissoes["ceps"]["pesquisar"] ? "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />" : "", $this->conteudo);
				$this->conteudo = str_replace("[adicionar_cep]", $this->permissoes["ceps"]["adicionar"] ? "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"adicionar_cep\" id=\"adicionar_cep\" value=\"Adicionar\" onClick=\"adicionarCEP()\" />" : "", $this->conteudo);
				
			} else {
				
				$_POST["nome"] = $this->corrigeNome($_POST["nome"]);
				
				if (!empty($_POST["nascimento"])) {
					$_POST["nascimento"] = explode("/", $_POST["nascimento"]);
					$_POST["nascimento"] = "'" . $_POST["nascimento"][2] . "-" . $_POST["nascimento"][1] . "-" . $_POST["nascimento"][0] . "'";
				} else {
					$_POST["nascimento"] = "NULL";
				}
				
				$_POST["numero"] = preg_replace("/[^0-9]/", "", $_POST["numero"]);
				
				if ($_POST["cep"]) {
					$_POST["cep"] = "'" . preg_replace("/[^0-9]/", "", $_POST["cep"]) . "'";
					$_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "NULL";
				} else {
					$_POST["cep"] = "NULL";
					$_POST["numero"] = "NULL";
				}
				
				$_POST["complemento"] = trim($_POST["complemento"]);
				$_POST["ramal"] = trim($_POST["ramal"]);
				$_POST["agente_id"] = trim($_POST["agente_id"]);
				$_POST["agente_senha"] = trim($_POST["agente_senha"]);
				$_POST["senha_discagem"] = trim($_POST["senha_discagem"]);
				$_POST["login"] = $this->limpaLogin($_POST["login"]);
				$_POST["comentarios"] = trim($_POST["comentarios"]);
				$_POST["administrador"] = intval($_POST["administrador"]);
				if ($_POST["senha"]) {
					if ($_SESSION["usuario"] == $_GET["i"]) $_SESSION["senha"] = md5($_POST["senha"]);
					$_POST["senha"] = "'" . md5($_POST["senha"]) . "'";
				} else {
					$_POST["senha"] = "usu_senha";
				}
				$_POST["origem"] = (int) $_POST["origem"];

				if($ehAdmin) {
					if($_SESSION["usuario"] == $_GET["i"] && $_POST["ehAdministrador"] == 0) {
						$_POST["ehAdministrador"] = 1;
					} else {
						$_POST["ehAdministrador"] = (int) $_POST["ehAdministrador"];
					}
				} else {
					$_POST["ehAdministrador"] = $this->dados_item["administrador"];
				}
				
				$this->bancodedados->query("
					UPDATE
						tab_usuarios
					SET
						usu_nome = '{$_POST["nome"]}', 
						usu_cpf = '{$_POST["cpf"]}', 
						usu_nascimento = {$_POST["nascimento"]}, 
						cep_id = {$_POST["cep"]}, 
						usu_numero = {$_POST["numero"]}, 
						usu_complemento = '{$_POST["complemento"]}', 
						usu_tel_residencial = '{$_POST["tel_residencial"]}', 
						usu_tel_celular = '{$_POST["tel_celular"]}', 
						usu_ramal = '{$_POST["ramal"]}', 
						usu_agente_id = '{$_POST["agente_id"]}', 
						usu_agente_senha = '{$_POST["agente_senha"]}', 
						usu_senha_discagem = '{$_POST["senha_discagem"]}', 
						usu_email = '{$_POST["email"]}', 
						usu_login = '{$_POST["login"]}', 
						usu_senha = {$_POST["senha"]}, 
						usu_comentarios = '{$_POST["comentarios"]}', 
						uss_id = {$_POST["status"]} ,
						usu_administrador = {$_POST["ehAdministrador"]} ,
						clo_id = {$_POST["origem"]}
					WHERE
						usu_id = {$_GET["i"]}");
						
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
			
			}
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	#################################
	### Ver Dados Administrativos ###
	#################################

	function ver_da() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {

			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar_da"]) $botoes[] = '<input type="button" name="editar_da" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_da&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("usuarios.ver_da.inc.php");
			
			$this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[grupos]", $this->geraListagemGrupos($this->dados_item["grupos"]), $this->conteudo);
			$this->conteudo = str_replace("[setores_funcoes]", $this->geraListagemSetoresFuncoes($this->dados_item["setores_funcoes"]), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[administrador]", ($this->dados_item["administrador"] ? " (Administrador do sistema)" : ""), $this->conteudo);
			$this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_da" => "Dados Administrativos")), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	###############################
	### Editar Dados Cadastrais ###
	###############################
	

	function editar_da() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("usuarios.editar_da.inc.php");

				$this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[grupos]", $this->geraListagemGrupos($this->dados_item["grupos"], true), $this->conteudo);
				$this->conteudo = str_replace("[setores_funcoes]", $this->geraListagemSetoresFuncoes($this->dados_item["setores_funcoes"], true), $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace("[administrador]", ($this->dados_item["administrador"] ? " (Administrador do sistema)" : ""), $this->conteudo);
				$this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_da" => "Dados Administrativos")), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_da&i={$this->dados_item["id"]}", $this->conteudo);
				
			} else {
				
				$this->registraItemGrupos($_GET["i"], $_POST["grupos"]);
				$this->registraItemSetoresFuncoes($_GET["i"], $_POST["setores_funcoes"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_da&i={$_GET["i"]}");
			
			}
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	##########################
	### Ver Dados Pessoais ###
	##########################

	function ver_dp() {
		
		$this->buscaDadosItem($_SESSION["usuario"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dp"]) $botoes[] = '<input type="button" name="editar_dp" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dp\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

			$this->conteudo .= $this->carregaArquivo("usuarios.ver_dp.inc.php");
			
			$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
			$this->conteudo = str_replace("[cpf]", $this->dados_item["cpf"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento]", $this->dados_item["nascimento"], $this->conteudo);
			$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
			$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
			$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
			$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
			$this->conteudo = str_replace("[tel_residencial]", $this->dados_item["tel_residencial"], $this->conteudo);
			$this->conteudo = str_replace("[tel_celular]", $this->dados_item["tel_celular"], $this->conteudo);
			$this->conteudo = str_replace("[dados_central_telefonica]", $this->buscaDadosCentralTelefonica(false), $this->conteudo);
			$this->conteudo = str_replace("[email]", $this->dados_item["email"], $this->conteudo);
			$this->conteudo = str_replace("[login]", $this->dados_item["login"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	###############################
	### Editar Dados Cadastrais ###
	###############################

	function editar_dp() {
		
		$this->buscaDadosItem($_SESSION["usuario"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("usuarios.editar_dp.inc.php");
				
				$this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
				$this->conteudo = str_replace("[cpf]", $this->dados_item["cpf"], $this->conteudo);
				$this->conteudo = str_replace("[nascimento]", $this->dados_item["nascimento"], $this->conteudo);
				$this->conteudo = str_replace("[cep]", $this->dados_item["cep"], $this->conteudo);
				$this->conteudo = str_replace("[estado]", $this->dados_item["estado"], $this->conteudo);
				$this->conteudo = str_replace("[cidade]", $this->dados_item["cidade"], $this->conteudo);
				$this->conteudo = str_replace("[bairro]", $this->dados_item["bairro"], $this->conteudo);
				$this->conteudo = str_replace("[logradouro]", $this->dados_item["logradouro"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $this->dados_item["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento]", $this->dados_item["complemento"], $this->conteudo);
				$this->conteudo = str_replace("[tel_residencial]", $this->dados_item["tel_residencial"], $this->conteudo);
				$this->conteudo = str_replace("[tel_celular]", $this->dados_item["tel_celular"], $this->conteudo);
				$this->conteudo = str_replace("[dados_central_telefonica]", $this->buscaDadosCentralTelefonica(), $this->conteudo);
				$this->conteudo = str_replace("[email]", $this->dados_item["email"], $this->conteudo);
				$this->conteudo = str_replace("[login]", $this->dados_item["login"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", '?m=' . $this->dados_modulo["nome"] . '&a=editar_dp', $this->conteudo);
				
			} else {
				
				$_POST["nome"] = $this->corrigeNome($_POST["nome"]);
				if (!empty($_POST["nascimento"])) {
					$_POST["nascimento"] = explode("/", $_POST["nascimento"]);
					$_POST["nascimento"] = "'" . $_POST["nascimento"][2] . "-" . $_POST["nascimento"][1] . "-" . $_POST["nascimento"][0] . "'";
				} else {
					$_POST["nascimento"] = "NULL";
				}
				
				$_POST["numero"] = preg_replace("/[^0-9]/", "", $_POST["numero"]);
				
				if ($_POST["cep"]) {
					$_POST["cep"] = "'" . preg_replace("/[^0-9]/", "", $_POST["cep"]) . "'";
					$_POST["numero"] = $_POST["numero"] ? $_POST["numero"] : "NULL";
				} else {
					$_POST["cep"] = "NULL";
					$_POST["numero"] = "NULL";
				}
				$_POST["complemento"] = trim($_POST["complemento"]);
				$_POST["ramal"] = trim($_POST["ramal"]);
				$_POST["ramal"] = $_POST["ramal"] ? "'{$_POST["ramal"]}'" : "NULL";
				if ($_POST["senha"]) {
					if ($_SESSION["usuario"] == $_GET["i"])
						$_SESSION["senha"] = md5($_POST["senha"]);
					$_POST["senha"] = "'" . md5($_POST["senha"]) . "'";
				} else {
					$_POST["senha"] = "usu_senha";
				}
				
				$this->bancodedados->query("
					UPDATE 
						tab_usuarios 
					SET 
						usu_nascimento = {$_POST["nascimento"]}, 
						cep_id = {$_POST["cep"]}, 
						usu_numero = {$_POST["numero"]}, 
						usu_complemento = '{$_POST["complemento"]}', 
						usu_tel_residencial = '{$_POST["tel_residencial"]}', 
						usu_tel_celular = '{$_POST["tel_celular"]}', 
						usu_ramal = {$_POST["ramal"]}, 
						usu_email = '{$_POST["email"]}', 
						usu_senha = {$_POST["senha"]} 
					WHERE 
						usu_id = '{$_SESSION["usuario"]}'");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id=' . $_SESSION["usuario"]);
				
				header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver_dp");
				
			}
			
		} else {
			header("Location: ./?m=" . $this->dados_modulo["nome"]);
		}
		
	}
	
	function buscaDadosCentralTelefonica($editavel = true) {
		
		if ($this->dados_item["agente_id"]) {
			return "
      <tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
        <td><strong>&nbsp;&raquo; Agente:</strong></td>
        <td>{$this->dados_item["agente_id"]}</td>
      </tr>
  		";
		} else {
			return "
      <tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
        <td><strong>&nbsp;&raquo; Ramal:</strong></td>
        <td>" . ($editavel ? "<input name=\"ramal\" id=\"ramal\" type=\"text\" class=\"inputBrancoAzulEscuro11\" size=\"50\" maxlength=\"10\" value=\"{$this->dados_item["ramal"]}\">" : $this->dados_item["ramal"]) . "</td>
      </tr>
  		";
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
		if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
			$this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
		}
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
		$itens[] = array("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_usuarios.usu_nome LIKE '%[valor]%')");
		
		// Setores
		$setores = $this->buscaSetores();
		$select_setores = array();
		foreach ($setores as $id => $nome) {
			$select_setores[] = array("id" => $id, "nome" => $nome);
		}
		if (!$valores["setores"]) {
			$setores_nulo = " OR rel_usu_orf_ors_exerce.ors_id IS NULL";
		}
		$itens[] = array("tipo" => "select", "nome" => "setores", "descricao" => "Setores", "valor" => $select_setores, "where" => "(rel_usu_orf_ors_exerce.ors_id = [valor]$setores_nulo)");
		
		// Funções
		$funcoes = $this->buscaFuncoes();
		$select_funcoes = array();
		foreach ($funcoes as $id => $nome) {
			$select_funcoes[] = array("id" => $id, "nome" => $nome);
		}
		if (!$valores["funcoes"]) {
			$funcoes_nulo = " OR rel_usu_orf_ors_exerce.orf_id IS NULL";
		}
		$itens[] = array("tipo" => "select", "nome" => "funcoes", "descricao" => "Funções", "valor" => $select_funcoes, "where" => "(rel_usu_orf_ors_exerce.orf_id = [valor]$funcoes_nulo)");
		
		// Grupos
		$grupos = $this->buscaGrupos();
		$select_grupos = array();
		foreach ($grupos as $id => $nome) {
			$select_grupos[] = array("id" => $id, "nome" => $nome);
		}
		if (!$valores["grupos"]) {
			$grupos_nulo = " OR rel_usu_gru_fazparte.gru_id IS NULL";
		}
		$itens[] = array("tipo" => "select", "nome" => "grupos", "descricao" => "Grupos", "valor" => $select_grupos, "where" => "(rel_usu_gru_fazparte.gru_id = [valor]$grupos_nulo)");
		
		// Status
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array("id" => $id, "nome" => $nome);
		}
		$itens[] = array("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_usuarios.uss_id = [valor])");
		
		// Origens
		$origens = $this->buscaOrigens();
		$select_origens = array();
		foreach ($origens as $id => $nome) {
			$select_origens[] = array("id" => $id, "nome" => $nome);
		}
		$itens[] = array("tipo" => "select", "nome" => "origem", "descricao" => "Origem", "valor" => $select_origens, "where" => "(tab_usuarios.clo_id = [valor])");
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}
	
	function buscaItens($filtro, $pagina) {
		
		$status = $this->buscaStatus();
		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome,
				tab_usuarios.usu_tel_residencial AS tel_residencial,
				tab_usuarios.usu_tel_celular AS tel_celular,
				tab_usuarios.usu_ramal AS ramal,
				tab_usuarios.usu_login AS login,
				tab_usuarios.usu_email AS email,
				tab_usuarios.uss_id AS status,
				tab_clientes_origens.clo_nome AS origem
			FROM
				tab_usuarios
				INNER JOIN tab_clientes_origens ON (tab_usuarios.clo_id = tab_clientes_origens.clo_id)
				LEFT OUTER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
				LEFT OUTER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
			WHERE
				$filtro->parametros_sql
			GROUP BY
				tab_usuarios.usu_id
			ORDER BY
				tab_usuarios.usu_nome ASC,
				tab_clientes_origens.clo_nome ASC
			LIMIT
				$limit_inicio,
				$limit_fim");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["telefone"] = array();
			if ($dados["tel_residencial"]) $dados["telefone"][] = $dados["tel_residencial"];
			if ($dados["tel_celular"]) $dados["telefone"][] = $dados["tel_celular"];
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"]) $dados["link"] = "?m=usuarios&a=ver_dc&i={$dados["id"]}";
			$dados["telefone"] = "<div style=\"width:80px\">" .implode("<br>", $dados["telefone"])."</div>";
			$dados["grupos"] = $this->buscaItemListagemGrupos($dados["id"]);
			$dados["setores"] = $this->buscaItemListagemSetores($dados["id"]);
			$dados["status"] = $status[$dados["status"]];
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
			
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}
	
	function geraListagem($itens) {
		
		$cabecalho = array("contador" => "#", "nome" => "Nome", "origem" => "Origem", "login" => "Login", "grupos" => "Grupos", "setores" => "Setores", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right", "left", "left", "left"));
		
		return $listagem;
		
	}

}

?>
