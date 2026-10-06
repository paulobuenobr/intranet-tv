<?

require_once("_global/modulo.class.inc.php");

class ceps extends modulo {

	var $ultimo_status = null;
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			
			if ($_GET["a"] == "adicionar" && $_GET["tipo"] == "cadastro") {
				$this->dados_acao["tipo"] = 2;
			}
			
			$this->conteudo = $this->geraTitulo();
			
			$this->{$this->acao}();
			
		}
		
	}

	function adicionar() {

		$botoes = array();
		$botoes[] = '<input type="button" id="salvar" class="buttonAzulEscuroBranco11" value="Salvar" onclick="salvaCEP()">';
		if ($_GET["tipo"] == "cadastro") {
			$botoes[] = '<input type="button" id="preencher" class="buttonAzulEscuroBranco11" value="Preencher" onclick="preencherCEP()" disabled>';
		}
		
		$this->conteudo .= $this->carregaArquivo("ceps.adicionar.inc.php");
		
		$this->conteudo = str_replace("[estado]", $this->geraSelect($this->buscaEstados(), null, true, "estado", "250px", "mudaEstado(this)"), $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
	}
	
	function buscaEstados() {
		
		$estados = array();

		$query = $this->bancodedados->query("
			SELECT
				est_id AS id,
				est_nome AS nome
			FROM
				tab_estados
			WHERE".($_SESSION["origem"] ? "
				est_id IN (
					SELECT
						est_id
					FROM
						tab_cidades
					WHERE
						cid_id IN (
							SELECT
								cid_id
							FROM
								rel_clo_cid_contem
							WHERE
								clo_id = {$_SESSION["origem"]}
						)
				)" : "1")."
			ORDER BY
				est_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$estados[$dados["id"]] = $dados["nome"];
		}
		
		return $estados;
		
	}
	
	function buscaCidades($estado_id) {
		
		$cidades = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cid_id AS id,
				cid_nome AS nome
			FROM
				tab_cidades
			WHERE
				est_id = $estado_id".($_SESSION["origem"] ? " AND
				cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : "")."
			ORDER BY
				cid_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cidades[$dados["id"]] = $dados["nome"];
		}
		
		return $cidades;
		
	}
	
	function buscaDadosCidade($cidade_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cid_id AS id,
				CONCAT(cid_nome, '/', est_sigla) AS nome,
				cid_cep_geral AS cep_geral
			FROM
				tab_cidades
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				cid_id = $cidade_id".($_SESSION["origem"] ? " AND
				cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : ""));
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaBairros($cidade_id) {
		
		$bairros = array();
		
		$query = $this->bancodedados->query("
			SELECT
				bai_id AS id,
				bai_nome AS nome
			FROM
				tab_bairros
			WHERE
				cid_id = $cidade_id".($_SESSION["origem"] ? " AND
				cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : "")."
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$bairros[$dados["id"]] = $dados["nome"];
		}
		
		return $bairros;
		
	}

	function buscaDadosCEP($cep_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_ceps.cep_id AS cep_id,
				tab_cidades.est_id AS estado_id,
				tab_cidades.cid_id AS cidade_id,
				tab_bairros.bai_id AS bairro_id,
				tab_logradouros.log_id AS logradouro_id,
				tab_logradouros.log_nome AS logradouro_nome,
				tab_logradouros.log_observacao AS observacao
			FROM
				tab_ceps
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				cep_id = '{$cep_id}'".($_SESSION["origem"] ? " AND
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : ""));
		
		return $this->bancodedados->resultadoArray($query);
	
	}
	
	function adicionarCEP($estado_id, $cidade_id, $bairro_id, $novo_bairro, $logradouro, $observacao, $cep_id) {
				
		if (!$this->permissoes["ceps"]["adicionar"]) {
			$this->ultimo_status = "Sem permissão para inserir CEPs.";
			return false;
		}

		$cep_id = sprintf("%08.8s", preg_replace("/[^0-9]/", "", $cep_id));
		if (substr($cep_id, 5, 3) == "000") {
			$prefixo_cep = substr($cep_id, 0, 5);
		} else if (substr($cep_id, 6, 2) == "00") {
			$prefixo_cep = substr($cep_id, 0, 6);
		}
		
		if (($dados_regiao = $this->buscaDadosRegiao($cep_id)) != false) {

			$cep_id = $this->buscaCEPDisponivel($prefixo_cep);
			
			if (!$cep_id) {
				$this->ultimo_status = "Sem CEP disponível para a região.";
				return false;
			}
			
			$cep_exportacao = $dados_regiao["cep_geral"];
			$estado_id = $dados_regiao["estado_id"];
			$cidade_id = $dados_regiao["cidade_id"];
			$bairro_id = $dados_regiao["bairro_id"] ? $dados_regiao["bairro_id"] : $bairro_id;
			
		} else {
			
			if (!$this->permissoes["ceps"]["adicionar_plus"]) {
				$this->ultimo_status = "Sem permissão para inserir CEP nessa região.";
				return false;
			}

			$cep_exportacao = $cep_id;
			
		}

		if (!$bairro_id) {
			$bairro_id = $this->adicionarBairro($novo_bairro, $cidade_id);
		}
		
		$logradouro = $this->corrigeNome($logradouro);
		
		if (!preg_match("/^(Alameda|Aeroporto|Área|Avenida|Campo|Chácara|Colônia|Condomínio|Conjunto|Distrito|Esplanada|Estação|Estrada|Favela|Fazenda|Feira|Jardim|Ladeira|Lago|Lagoa|Largo|Loteamento|Morro|Núcleo|Parque|Passarela|Pátio|Praça|Quadra|Recanto|Residencial|Rua|Rodovia|Servidão|Setor|Sítio|Travessa|Trecho|Trevo|Vale|Vereda|Via|Viaduto|Viela|Vila) /", $logradouro)) {
			$this->ultimo_status = "Logradouro inválido (deve iniciar com: Alameda, Aeroporto, Área, Avenida, Campo, Chácara, Colônia, Condomínio, Conjunto, Distrito, Esplanada, Estação, Estrada, Favela, Fazenda, Feira, Jardim, Ladeira, Lago, Lagoa, Largo, Loteamento, Morro, Núcleo, Parque, Passarela, Pátio, Praça, Quadra, Recanto, Residencial, Rua, Rodovia, Servidão, Setor, Sítio, Travessa, Trecho, Trevo, Vale, Vereda, Via, Viaduto, Viela ou Vila).";
			return false;
		}

		if ($this->permissoes["ceps"]["inserir_observacao"]) {
			$observacao = trim($observacao);
		} else {
			$observacao = "";
		}
				
		if (($dados_cep = $this->buscaDadosCEP($cep_id)) != false) {
			return $this->atualizarCEP($dados_cep, $estado_id, $cidade_id, $bairro_id, $logradouro, $observacao);
		}
		
		if ($dados_regiao && ($cep_regiao = $this->buscaCEPRegiao($prefixo_cep, $estado_id, $cidade_id, $bairro_id, $logradouro, $observacao)) != false) {
			$this->ultimo_status = "CEP já cadastrado!";
			return substr(sprintf("%08.8s", $cep_regiao), 0, 5)."-".substr(sprintf("%08.8s", $cep_regiao), 5, 3);
		}
		
		$this->bancodedados->transacaoIniciar();
							
		$this->bancodedados->query("
			INSERT INTO 
				tab_logradouros
			SET
				log_nome = '$logradouro', 
				log_observacao = '$observacao', 
				bai_id = $bairro_id");
		
		$logradouro_id = $this->bancodedados->ultimoIdInserido();
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_ceps
			SET 
				cep_id = $cep_id, 
				log_id = $logradouro_id, 
				bai_id = $bairro_id, 
				cid_id = $cidade_id, 
				est_id = $estado_id, 
				cep_exportacao = $cep_exportacao");
		
		$cep_id = $this->bancodedados->ultimoIdInserido();
		
		$this->bancodedados->transacaoSalvar();
		
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], "adicionar", $cep_id);

		$this->ultimo_status = "CEP cadastrado com sucesso!";
		
		return substr(sprintf("%08.8s", $cep_id), 0, 5)."-".substr(sprintf("%08.8s", $cep_id), 5, 3);
		
	}

	function atualizarCEP($dados_cep, $estado_id, $cidade_id, $bairro_id, $logradouro, $observacao) {
		
		if ($dados_cep["estado_id"] == $estado_id &&
				$dados_cep["cidade_id"] == $cidade_id &&
				$dados_cep["bairro_id"] == $bairro_id &&
				$dados_cep["logradouro"] == $logradouro &&
				$dados_cep["observacao"] == $observacao) {
			$this->ultimo_status = "CEP já cadastrado!";
			return substr(sprintf("%08.8s", $dados_cep["cep_id"]), 0, 5)."-".substr(sprintf("%08.8s", $dados_cep["cep_id"]), 5, 3);
		}
		
		if (!$this->permissoes["ceps"]["editar"]) {
			$this->ultimo_status = "Sem permissão para editar CEPs.";
			return false;
		}
		
		$dados_cidade = $this->buscaDadosCidade($cidade_id);
		
		if (!$dados_cidade) {
			$this->ultimo_status = "Cidade não encontrada.";
			return false;
		}
		
		if ($dados_cidade["cep_geral"] && !$this->permissoes["ceps"]["editar_plus"]) {
			$this->ultimo_status = "Sem permissão para editar CEP de {$dados_cidade["nome"]}.";
			return false;
		}
		
		if (!$this->permissoes["ceps"]["inserir_observacao"]) {
			$observacao = $dados_cep["observacao"];
		}
			
		$this->bancodedados->query("
			UPDATE 
				tab_logradouros 
			SET 
				log_nome = '$logradouro', 
				log_observacao = '$observacao', 
				bai_id = $bairro_id 
			WHERE 
				log_id = {$dados_cep["logradouro_id"]}");
		
		if ($bairro_id != $dados_cep["bairro_id"]) {
		  	
			$this->bancodedados->query("
				UPDATE 
					tab_ceps 
				SET 
					bai_id = $bairro_id, 
					cid_id = $cidade_id, 
					est_id = $estado_id 
				WHERE 
					cep_id = {$dados_cep["cep_id"]}");
		  	
			if (!$this->bancodedados->linhasAfetadas()) return false;
		
		}
		
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], "editar", $dados_cep["cep_id"]);
		
		$this->ultimo_status = "CEP atualizado com sucesso!";
		
		return substr(sprintf("%08.8s", $dados_cep["cep_id"]), 0, 5)."-".substr(sprintf("%08.8s", $dados_cep["cep_id"]), 5, 3);
		
	}
	
	function adicionarBairro($bairro, $cidade_id) {
		
		$bairro = $this->corrigeNome($bairro);
		
		if ($bairro && $cidade_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					bai_id
				FROM
					tab_bairros
				WHERE
					bai_nome = '$bairro' AND
					cid_id = $cidade_id");
			
			if (($bairro_id = $this->bancodedados->resultado($query)) != false) {
				return $bairro_id;
			}
			
			$this->bancodedados->query("
				INSERT INTO
					tab_bairros
				SET
					bai_nome = '$bairro',
					cid_id = $cidade_id");
			
			return $this->bancodedados->ultimoIdInserido();
			
		}
		
	}
	
	function buscaDadosRegiao($cep_id) {
		
		//$cep_id = substr(sprintf("%08.8s", $cep_id), 0, 5)."000";
		
		$query = $this->bancodedados->query("
			SELECT
				cid_id AS cidade_id,
				est_id AS estado_id,
				cid_cep_geral AS cep_geral
			FROM
				tab_cidades
			WHERE
				cid_cep_geral = '$cep_id'");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $dados;
		}
				
		$query = $this->bancodedados->query("
			SELECT
				tab_cidades.cid_id AS cidade_id,
				tab_cidades.est_id AS estado_id,
				tab_bairros.bai_id AS bairro_id,
				tab_bairros.bai_cep_geral AS cep_geral
			FROM
				tab_bairros
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				tab_bairros.bai_cep_geral = '$cep_id'");
				
		return $this->bancodedados->resultadoArray($query);

	}
	
	function buscaCEPRegiao($prefixo_cep, $estado_id, $cidade_id, $bairro_id, $logradouro, $observacao) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_ceps.cep_id
			FROM
				tab_ceps
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
			WHERE
				tab_ceps.cep_id LIKE '{$prefixo_cep}%' AND
				tab_ceps.est_id = $estado_id AND
				tab_ceps.cid_id = $cidade_id AND
				tab_ceps.bai_id = $bairro_id AND
				tab_logradouros.log_nome LIKE '$logradouro' AND
				tab_logradouros.log_observacao LIKE '$observacao'
			ORDER BY
				tab_ceps.cep_id
			LIMIT 1");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaCEPDisponivel($prefixo_cep) {
		
		if (strlen($prefixo_cep) == 5) {
		
			$query = $this->bancodedados->query("
				SELECT
					MIN(cep_id)
				FROM
					tab_ceps
				WHERE
					cep_id LIKE '{$prefixo_cep}___' AND
					cep_id <> '{$prefixo_cep}999' AND
					cep_id + 1 NOT IN (
						SELECT
							cep_id
						FROM
							tab_ceps
						WHERE
							cep_id LIKE '{$prefixo_cep}%'
					)");
			
			$cep = $this->bancodedados->resultado($query);
			
			if (!$cep) {
				return "{$prefixo_cep}001";
			} elseif ($cep != "{$prefixo_cep}999") {
				return $cep + 1;
			}

		} elseif (strlen($prefixo_cep) == 6) {
		
			$query = $this->bancodedados->query("
				SELECT
					MIN(cep_id)
				FROM
					tab_ceps
				WHERE
					cep_id LIKE '{$prefixo_cep}__' AND
					cep_id <> '{$prefixo_cep}99' AND
					cep_id + 1 NOT IN (
						SELECT
							cep_id
						FROM
							tab_ceps
						WHERE
							cep_id LIKE '{$prefixo_cep}%'
					)");
			
			$cep = $this->bancodedados->resultado($query);
			
			if (!$cep) {
				return "{$prefixo_cep}01";
			} elseif ($cep != "{$prefixo_cep}99") {
				return $cep + 1;
			}

		}
		
	}

	function buscaCEPPorEndereco($estado_id, $cidade_id, $bairro_id, $logradouro) {
		
		$query = $this->bancodedados->query("
			SELECT
				INSERT(LPAD(cep_id, 8, '0'), 6, 0, '-')
			FROM
				tab_ceps
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				tab_cidades.est_id = $estado_id AND
				tab_cidades.cid_id = $cidade_id AND
				tab_bairros.bai_id = $bairro_id AND
				tab_logradouros.log_nome = '$logradouro'");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	#################
	### Pesquisar ###
	#################
	
	function pesquisar() {
		
		$this->buscar(true);
		
	}
		
	##############
	### Buscar ###
	##############
	
	function buscar($botao_preencher = false) {
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=ceps"></script>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= $filtro->formulario;
		
		if ($_GET["f"] || $_GET["p"]) {
			
			$itens = $this->buscaItens($filtro, $pagina, $botao_preencher);
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
	
	function inicializaFiltro($valores = null) {
		
		$itens = array();
		
		// CEP
		$itens[] = array ("tipo" => "input", "nome" => "cep", "descricao" => "CEP", "valor" => "", "where" => "tab_ceps.cep_id LIKE '%[valor]%'");
		
		// Estado
		$estados = $this->buscaEstados();
		$select_estados = array();
		foreach ($estados as $id => $nome) {
			$select_estados[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "estado", "descricao" => "Estado", "valor" => $select_estados, "where" => "tab_estados.est_id = '[valor]'");
		
		// Cidade
		if ($valores["cidade"]) {
			$cidade = $this->geraQueryTexto($valores["cidade"], "tab_cidades.cid_nome");
		} else {
			$cidade = "1";
		}
		$itens[] = array ("tipo" => "input", "nome" => "cidade", "descricao" => "Cidade", "valor" => "", "where" => $cidade);
		
		// Bairro
		if ($valores["bairro"]) {
			$bairro = $this->geraQueryTexto($valores["bairro"], "tab_bairros.bai_nome");
		} else {
			$bairro = "1";
		}
		$itens[] = array ("tipo" => "input", "nome" => "bairro", "descricao" => "Bairro", "valor" => "", "where" => $bairro);
		
		// Lograoduro
		if ($valores["logradouro"]) {
			$logradouro = $this->geraQueryTexto($valores["logradouro"], "tab_logradouros.log_nome");
		} else {
			$logradouro = "1";
		}
		$itens[] = array ("tipo" => "input", "nome" => "logradouro", "descricao" => "Logradouro", "valor" => "", "where" => $logradouro);
		
		// Filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
		return $filtro;
		
	}

	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}

	function buscaItens($filtro, $pagina, $botao_preencher) {
		
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		$itens = array();
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
				tab_estados.est_sigla AS estado,
				tab_cidades.cid_nome AS cidade,
				tab_bairros.bai_nome AS bairro,
				tab_logradouros.log_nome AS logradouro,
				tab_logradouros.log_observacao AS observacao
			FROM
				tab_ceps
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				$filtro->parametros_sql".($_SESSION["origem"] ? " AND
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : "")."
			ORDER BY
				tab_logradouros.log_nome
			LIMIT
				$limit_inicio,
				$limit_fim");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($botao_preencher) {
				$dados["cep"] = "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" value=\"{$dados["cep"]}\" onClick=\"preencherCEP('{$dados["cep"]}')\" />";
			}
			$itens[] = $dados;
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}

	function geraListagem($itens) {
		
		$cabecalho = array ("cep" => "CEP", "logradouro" => "Logradouro", "bairro" => "Bairro", "cidade" => "Cidade", "estado" => "UF", "observacao" => "Observa&ccedil;&atilde;o");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("60", "", "", "", "20"), null, array("center", "left", "left", "left", "left", "left"));
		
		return $listagem;
		
	}
	
	function geraQueryTexto($texto, $field) {
		
		$blacklist = array("o", "a", "e", "de", "do", "dos", "di", "da", "das");
		//$logradouros = array("rua", "estrada", "rodovia", "praça", "avenida", "alameda", "beco", "loteamento", "via", "vila", "travessa", "servidao", "passagem", "parque", "largo", "jardim", "passeio");
		$logradouros = array("alameda", "aeroporto", "área", "avenida", "campo", "chácara", "colônia", "condomínio", "conjunto", "distrito", "esplanada", "estação", "estrada", "favela", "fazenda", "feira", "jardim", "ladeira", "lago", "lagoa", "largo", "loteamento", "morro", "núcleo", "parque", "passarela", "pátio", "praça", "quadra", "recanto", "residencial", "rua", "rodovia", "servidão", "setor", "sítio", "travessa", "trecho", "trevo", "vale", "vereda", "via", "viaduto", "viela", "vila");

		$texto = mb_strtolower($texto, "iso-8859-1");
		$texto = preg_replace("/[ç]/", "c", $texto);
		$texto = preg_replace("/[ñ]/", "n", $texto);
		$texto = preg_replace("/[áàâãä]/", "a", $texto);
		$texto = preg_replace("/[éèêë]/", "e", $texto);
		$texto = preg_replace("/[íìîï]/", "i", $texto);
		$texto = preg_replace("/[óòôõö]/", "o", $texto);
		$texto = preg_replace("/[úùûü]/", "u", $texto);
		$texto = trim(preg_replace("/[^a-z0-9]+/", " ", $texto));
		$texto = explode(" ", $texto);
		
		
	
		
		$tokens = array();
		
		for ($i = 0; $i < count($texto); $i++) {
			if (!in_array($texto[$i], $blacklist) && ($i != 0 || !in_array($texto[$i], $logradouros))) {
				$tokens[] = "{$field} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}'";
			}
		}
		
		if ($tokens) {
			return implode(" AND ", $tokens);
		} else {
			return "1";
		}
		
	}
	
	function geraExpressao($token) {

		$origem  = array(
			"/(.)\\1+/",
			"/nh/",
			"/([^p])h/",
			"/s$/",
			"/(.)/",
			"/([aeiou]) l ([b-df-hj-np-tv-z])/",
			"/(g n)/",
			"/(d t|t d|t) $/",
			"/(f|p h) /",
			"/([vw] )+/",
			"/([mn] )+/",
			"/(([jg]|gu) )+/",
			"/(([cszxkq]|qu) )+/",
			"/a /",
			"/[eiy]+ /",
			"/[ou]+ /",
			"/([a-z0-9]) /",
			"/ +/",
			"/$/"
		);
		
		$destino = array(
			"$1",
			"gn",
			"$1",
			"",
			"$1 ",
			"$1 (h?[lu])+ $2",
			" (gn|nh) ",
			" (t+|dt|td) ",
			" (f+|ph) ",
			" (h?[vw])+ ",
			" (h?[mn])+ ",
			" (h?[jgu])+ ",
			" (h?[cszxkquç]h?)+ ",
			"(h?[aáàâãä])+ ",
			"(h?[eéèêëiíìîïy])+ ",
			"(h?[oóòôõöuúùûü])+ ",
			"(h?$1)+ ",
			"",
			"h?"
		);		

		return preg_replace($origem, $destino, $token);
		
	}
	
	function corrigeNome($nome) {
		
		$minusculas = array("da", "de", "do", "das", "dos", "e");
		$maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
		
		$nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
		$nome = preg_replace("/[ ]+/", " ", $nome); // Remove espaços seguidos
		$nome = preg_replace("/[']/", "`", $nome);
		$nome = preg_replace("/[^a-zA-Z0-9 áéíóúâêôãõñçÁÉÍÓÚÂÊÔÃÕÑÇ`.]/", "", $nome); // Remove caracteres estranhos
		
		$nome = mb_strtolower($nome, "iso-8859-1"); // Coloca todos os caracteres em minúsculo
		$nome = mb_convert_case($nome,  MB_CASE_TITLE, "iso-8859-1"); // Coloca as primeiras letras de cada nome em maiúsculo
		
		for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
		for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
		
		$nome = trim($nome);
		
		$nome = preg_replace_callback('/\b([LXIVCDM]+)\b/i', function($matches) {
			return mb_strtoupper($matches[1], "iso-8859-1");
		}, $nome);
			
		$nome = preg_replace_callback('/\b`([a-z]+)\b/i', function($matches) {
			return "`".mb_convert_case($matches[1],  MB_CASE_TITLE, "iso-8859-1");
		}, $nome);
				
		return $nome;
		
	}
	
}

?>
