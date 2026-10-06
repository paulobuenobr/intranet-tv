<?

class servicos_ajustes {

	var $bancodedados;
	var $permissoes;
	var $acao;
	var $conteudo;
	var $dados_item;
	var $dados_modulo;
	var $dados_acao;

	var $ajustes_tipos = array(
		1 => "Porcentagem",
		2 => "Valor"
	);
	
	var $unidades_capacidade = array(
		"1" => "Kbps",
		"2" => "Mbps",
		"3" => "Gbps",
		"4" => "Tbps"
	);
	
	var $dias_semana = array(
		0 => "Todos",
		7 => "Domingo",
		1 => "Segunda-feira",
		2 => "Terça-feira",
		3 => "Quarta-feira",
		4 => "Quinta-feira",
		5 => "Sexta-feira",
		6 => "Sábado"
	);

	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}

	function geraTitulo() {
		
		$titulo  = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';
		
		return $titulo;
		
	}

	function carregaArquivo($arquivo) {
		
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
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

	function geraSelect($itens, $selecionado = null, $item_nulo = false) {
		
		if ($item_nulo) $select = "<option></option>";
		
		foreach ($itens as $valor => $item) {
			$select .= "<option value=\"$valor\"".(isset($selecionado) && $valor == $selecionado ? " selected" : "").">$item</option>";
		}
		
		return $select;
		
	}

	function buscaDadosItem($ajuste_id) {
		
		$this->dados_item = $this->buscaDadosAjuste($ajuste_id);
		
	}
  
	function buscaDadosAjuste($ajuste_id) {
		
		$query = "
			SELECT
				tab_servicos_ajustes.saj_id AS id,
				tab_servicos_ajustes.ass_id AS assinatura_id,
				tab_servicos_ajustes.saj_manual AS manual,
				tab_servicos_ajustes.saj_valor AS valor,
				tab_servicos_ajustes.saj_duracao AS duracao,
				tab_servicos_ajustes.ajf_id AS ajuste_financeiro_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_servicos_ajustes_categorias.sac_id AS categoria_id,
				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				IF(tab_servicos_ajustes_categorias.sac_editavel AND tab_servicos_ajustes.ass_id IS NULL, 1, 0) AS editavel,
				tab_servicos_ajustes_categorias.sac_sistema AS sistema,
				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
				tab_servicos_ajustes_status.sas_id AS status_id,
				tab_servicos_ajustes_status.sas_nome AS status
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_servicos_ajustes.cli_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
			WHERE
				saj_id = $ajuste_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["codigo"] = sprintf("%06s", $dados["id"]);
			return $dados;
		}
		
	}

	function buscaSuspensoes($ajuste_id) {
		
		$ajustes = array();
		
		$query = "
			SELECT
				tab_servicos_ajustes.saj_id AS id,
				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_servicos_ajustes_status.sas_nome AS status
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
			WHERE
				tab_servicos_ajustes.saj_pai = $ajuste_id";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["codigo"] = "<div align=\"center\">".sprintf("%06s", $dados["id"])."</div>";
			$dados["categoria"] = "<div align=\"left\">{$dados["categoria"]}</div>";
			if (!$dados["inicio"]) $dados["inicio"] = "-";
			if (!$dados["termino"]) $dados["termino"] = "-";
			
			$ajustes[] = $dados;
		
		}
		
		$cabecalho = array ("codigo" => "Código", "categoria" => "Categoria", "inicio" => "Início", "termino" => "Término", "status" => "Status");
		$larguras = array("40", "", "95", "95", "80");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $ajustes, 0, false, $larguras);
		
		return $listagem->conteudo;
	}

	function buscaAjusteFilhoAtivo($ajuste_id) {
		
		$query = "
			SELECT
				tab_servicos_ajustes.saj_id
			FROM
				tab_servicos_ajustes
			WHERE
				tab_servicos_ajustes.saj_pai = $ajuste_id AND
				tab_servicos_ajustes.saj_inicio <= NOW() AND
				tab_servicos_ajustes.sas_id IN (5, 9)
			LIMIT
				0, 1";
			
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaCliente($cliente_id) {

		$query = "
			SELECT
				cli_nome
			FROM
				tab_clientes
			WHERE
				cli_id = $cliente_id";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);

	}

	function buscaServicos() {
		
		$servicos = array(0 => "Todos");
		
		$query = "
			SELECT
				sat_id as id,
				sat_nome as nome
			FROM
				tab_servicos_ajustes_tipos
			ORDER BY
				nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}
		
		return $servicos;
		
	}

	function buscaHorarios() {
		
		$horarios = array();
		
		for ($h = 0; $h < 24; $h++) {
			for ($m = 0; $m < 60; $m += 15) {
				$horario = sprintf("%02s", $h).":".sprintf("%02s", $m);
				$horarios[$horario] = $horario;
			}
		}
		$horarios["24:00"] = "24:00";
		
		return $horarios;
		
	}

	function buscaCategorias($categoria_id = null, $mostrar_todas = false) {
		
		$categorias = array();
		$filtro = array();

		if ($categoria_id) $filtro[] = "sac_id = $categoria_id";
		if (!$mostrar_todas) $filtro[] = "sac_status = 1";
		
		$filtro = implode(" OR\n        ", $filtro);
		
		$query = "
			SELECT
				sac_id as id,
				sac_nome as nome
			FROM
				tab_servicos_ajustes_categorias".($filtro ? "
			WHERE
				$filtro" : "")."
			ORDER BY
				nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$categorias[$dados["id"]] = $dados["nome"];
		}
		
		return $categorias;
		
	}

	function buscaCondominios() {

		$condominios = array();

		$query = "
			SELECT
				con_id AS id,
				CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, '/', tab_bairros.bai_nome, ')') AS nome
			FROM
				tab_condominios
				INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				cos_id IS NOT NULL
			ORDER BY
				nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$condominios[$dados["id"]] = $dados["nome"];
		}
		
		return $condominios;
		
	}

	function buscaOrigens() {

		$origens = array();

		$query = "
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			ORDER BY
				nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[$dados["id"]] = $dados["nome"];
		}
		
		return $origens;
		
	}

	function buscaDadosCategoria($categoria_id) {
		
		$query = "
			SELECT
				sac_id AS id,
				sac_regras as regras,
				sac_duracao AS duracao,
				sac_valor AS valor,
				sac_editavel as editavel
			FROM
				tab_servicos_ajustes_categorias
			WHERE
				sac_id = $categoria_id";
		
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["regras"] = unserialize($dados["regras"]);
			foreach ($dados["regras"] as $regra) {
				if ($regra["servico"] == 9 && $regra["ajuste_tipo"] == 2) {
					$dados["download"] = $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
				} elseif ($regra["servico"] == 1 && $regra["ajuste_tipo"] == 2) {
					$dados["upload"] = $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
				}
			}
			return $dados;
		}
		
	}

	function buscaAssinaturas($contrato_id) {
		
		$assinaturas = array();
		
		$query = "
			SELECT
				tab_assinaturas.ass_id AS assinatura_id,
				tab_planos.pla_nome AS plano_nome
			FROM
				rel_ass_cor_estacontido
				INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ast_id IN (1, 2, 3, 5) AND
				tab_assinaturas.ass_pai IS NULL AND
				rel_ass_cor_estacontido.cor_id = $contrato_id
			ORDER BY
				rel_ass_cor_estacontido.ass_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[$dados["assinatura_id"]] = $dados["plano_nome"];
		}
		
		return $assinaturas;
		
	}

	function buscaDadosAssinatura($assinatura_id) {
		
		$query = "
			SELECT
				ass_id AS id,
				pla_nome AS nome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				ass_id = $assinatura_id";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
	
	}

	function buscaContratos($cliente_id) {
		
		$contratos = array();
		
		$query = "
			SELECT DISTINCT
				tab_contratos.cor_id AS contrato_id,
				CONCAT(tab_contratos.cor_codigo, ' - ', tab_planos_servicos.pls_nome) AS contrato,
				tab_assinaturas.ast_id AS status_id
			FROM
				tab_contratos
				INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
				INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				tab_contratos.crs_id NOT IN (5, 22, 23, 24) AND
				tab_contratos.crt_id = 11 AND
				tab_assinaturas.cli_id = $cliente_id AND
				tab_assinaturas.ast_id IN (1, 2, 5)";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["status_id"] == 1) {
				$dados["contrato"] .= " (Prospect)";
			}
			$contratos[$dados["contrato_id"]] = $dados["contrato"];
		}
		
		return $contratos;
		
	}

	function buscaServicosIptv($assinatura_id, $ajuste_id = null) {
		
		$pontos = array();
		
		$query = "
			SELECT
				tab_clientes_iptv.cip_id AS id,
				tab_planos.pla_nome AS plano_nome
			FROM
				tab_clientes_iptv
				INNER JOIN tab_planos ON (tab_clientes_iptv.pla_id = tab_planos.pla_id)
			WHERE
				tab_clientes_iptv.cor_id IN (SELECT cor_id FROM rel_ass_cor_estacontido WHERE ass_id = $assinatura_id) AND
				(tab_clientes_iptv.cis_id IN (1, 3)".($ajuste_id ? " OR
				(SELECT COUNT(*) FROM rel_saj_cip_contem WHERE rel_saj_cip_contem.saj_id = $ajuste_id AND rel_saj_cip_contem.cip_id = tab_clientes_iptv.cip_id) > 0" : "").")";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$pontos[$dados["id"]] = "IPTV: {$dados["plano_nome"]}";
		
		}
		
		return $pontos;
	}

	function buscaServicosAutenticacoes($assinatura_id, $ajuste_id = null) {
		
		$autenticacoes = array();
		
		$query = "
			SELECT
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_velocidade_download AS download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS upload
			FROM
				tab_clientes_autenticacoes
			WHERE
				tab_clientes_autenticacoes.ass_id = $assinatura_id AND
				(tab_clientes_autenticacoes.cas_id IN (1, 2)".($ajuste_id ? " OR
				(SELECT COUNT(*) FROM rel_saj_cau_contem WHERE rel_saj_cau_contem.saj_id = $ajuste_id AND rel_saj_cau_contem.cau_id = tab_clientes_autenticacoes.cau_id) > 0" : "").")";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$unidade_download = floor(log($dados["download"], 1024));
			
			$velocidade = $dados["download"] / pow(1024, $unidade_download)." / ".round($dados["upload"] / pow(1024, $unidade_download), 1)." ".$this->unidades_capacidade[$unidade_download];
			
			$autenticacoes[$dados["id"]] = "Autenticação: $velocidade";
		
		}
		
		return $autenticacoes;
	}

	function buscaServicosPontos($assinatura_id, $ajuste_id = null) {
		
		$pontos = array();
		
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS ponto_id,
				tab_clientes_pontos.clp_endereco_mac AS mac,
				tab_clientes_pontos.clp_velocidade_download AS download,
				tab_clientes_pontos.clp_velocidade_upload AS upload
			FROM
				tab_clientes_pontos
			WHERE
				tab_clientes_pontos.ass_id = $assinatura_id AND
				(tab_clientes_pontos.cps_id IN (1, 2)".($ajuste_id ? " OR
				(SELECT COUNT(*) FROM rel_saj_clp_contem WHERE rel_saj_clp_contem.saj_id = $ajuste_id AND rel_saj_clp_contem.clp_id = tab_clientes_pontos.clp_id) > 0" : "").")";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$unidade_download = floor(log($dados["download"], 1024));
			$velocidade = $dados["download"] / pow(1024, $unidade_download)." / ".round($dados["upload"] / pow(1024, $unidade_download), 1)." ".$this->unidades_capacidade[$unidade_download];
			$pontos[$dados["ponto_id"]] = "Ponto: {$dados["mac"]} ($velocidade)";
		}
		
		return $pontos;
		
	}

	function buscaServicosEmails($assinatura_id, $ajuste_id = null) {
		
		$emails = array();
		
		$query = "
			SELECT
				tab_clientes_emails.cle_id AS email_id,
				tab_clientes_emails.cle_usuario AS login,
				tab_dominios.dom_dominio AS dominio
			FROM
				tab_clientes_emails
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
			WHERE
				tab_clientes_emails.ass_id = $assinatura_id AND
				(tab_clientes_emails.ces_id IN (1, 2)".($ajuste_id ? " OR
				(SELECT COUNT(*) FROM rel_saj_cle_contem WHERE rel_saj_cle_contem.saj_id = $ajuste_id AND rel_saj_cle_contem.cle_id = tab_clientes_emails.cle_id) > 0" : "").")";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[$dados["email_id"]] = "E-Mail: {$dados["login"]}@{$dados["dominio"]}";
		}
		
		return $emails;
		
	}

	function buscaAssinaturasComAjuste($contrato_id, $ajuste_id) {
		
		$assinaturas = array();
		
		$query = "
			SELECT
				rel_saj_ass_contem.ass_id AS id
			FROM
				rel_saj_ass_contem
				INNER JOIN rel_ass_cor_estacontido ON (rel_saj_ass_contem.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id AND
				rel_saj_ass_contem.saj_id = $ajuste_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[$dados["id"]] = $dados["id"];
		}
		
		return $assinaturas;
		
	}

	function buscaIptvsComAjuste($ajuste_id, $detalhes = false) {
		
		$iptvs = array();
		
		if ($ajuste_id) {
			
			$query = "
				SELECT
					cip_id AS id,
					cis_id AS status_id
				FROM
					tab_clientes_iptv
				WHERE
					cip_id IN (SELECT cip_id FROM rel_saj_cip_contem WHERE saj_id = $ajuste_id)";
					
			$query = $this->bancodedados->query($query);
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($detalhes) {
					$iptvs[] = $dados;
				} else {
					$iptvs[$dados["id"]] = $dados["id"];
				}
			}
			
		}
		
		return $iptvs;
		
	}

	function buscaAutenticacoesComAjuste($ajuste_id, $detalhes = false) {
		
		$autenticacoes = array();
		
		if ($ajuste_id) {

			$query = "
				SELECT
					cau_id AS id,
					cau_velocidade_download AS download,
					cau_velocidade_upload AS upload,
					cau_ajuste_download AS ajuste_download,
					cau_ajuste_upload AS ajuste_upload,
					cau_ajustado AS ajustado
				FROM
					tab_clientes_autenticacoes
				WHERE
					cau_id IN (SELECT cau_id FROM rel_saj_cau_contem WHERE saj_id = $ajuste_id)";
					
			$query = $this->bancodedados->query($query);
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($detalhes) {
					$autenticacoes[] = $dados;
				} else {
					$autenticacoes[$dados["id"]] = $dados["id"];
				}
			}
			
		}
		
		return $autenticacoes;
		
	}

	function buscaPontosComAjuste($ajuste_id, $detalhes = false) {

		$pontos = array();
	
		if ($ajuste_id) {
	
			$query = "
				SELECT
					clp_id AS id,
					clp_velocidade_download AS download,
					clp_velocidade_upload AS upload,
					clp_ajustado AS ajustado,
					clp_parametros_ajuste AS parametros_ajuste,
					sei_id AS interface_id
				FROM
					tab_clientes_pontos
				WHERE
					clp_id IN (SELECT clp_id FROM rel_saj_clp_contem WHERE saj_id = $ajuste_id)";
			
			$query = $this->bancodedados->query($query);
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($detalhes) {
					$pontos[] = $dados;
				} else {
					$pontos[$dados["id"]] = $dados["id"];
				}
			}
			
		}
		
		return $pontos;
		
	}

	function buscaEmailsComAjuste($ajuste_id, $detalhes = false) {
		
		$emails = array();

		if ($ajuste_id) {
			
			$query = "
				SELECT
					cle_id AS id,
					cle_bloqueado AS bloqueado
				FROM
					tab_clientes_emails
				WHERE
					cle_id IN (SELECT cle_id FROM rel_saj_cle_contem WHERE saj_id = $ajuste_id)";
			
			$query = $this->bancodedados->query($query);
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($detalhes) {
					$emails[] = $dados;
				} else {
					$emails[$dados["id"]] = $dados["id"];
				}
			}
		
		}
		
		return $emails;
	
	}

	function buscaServicosAjustes($cliente_id, $ajuste_id = null, $ativo = true) {
		
		$contratos = $this->buscaContratos($cliente_id);
		$servicos_i = 0;
		
		$servicos = '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		
		if ($this->dados_item["assinatura_id"]) {
			
			$assinatura = $this->buscaDadosAssinatura($this->dados_item["assinatura_id"]);
			
			$iptv = $this->buscaServicosIptv($this->dados_item["assinatura_id"], $ajuste_id);
			$autenticacoes = $this->buscaServicosAutenticacoes($this->dados_item["assinatura_id"], $ajuste_id);
			$pontos = $this->buscaServicosPontos($this->dados_item["assinatura_id"], $ajuste_id);
			$emails = $this->buscaServicosEmails($this->dados_item["assinatura_id"], $ajuste_id);
			
			$servicos .= '<tr><td class="txtBranco11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="contrato" disabled checked></td>';
			$servicos .= '<td class="txtAzulEscuro11" colspan="3" width="100%">&nbsp;<b>Assinatura com Ajuste Programado</b></td>';
			$servicos .= '</tr>';
			$servicos .= '<tr bgcolor="#E8E8E8">';
			$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join.gif" width="19" height="16"></td>';
			$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="assinatura" disabled checked></td>';
			$servicos .= '<td class="txtAzulEscuro11" width="100%" colspan="2">&nbsp;'.$assinatura["nome"].'</td>';
			$servicos .= '</tr>';
			
			// Lista Serviços
			
			foreach ($iptv as $iptv_id => $iptv) {
				if (next($iptv) || sizeof($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
					$ultimo_iptv = false;
				} else {
					prev($iptv);
					$ultimo_iptv = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_iptv) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="iptv['.$iptv_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$iptv.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}

			foreach ($autenticacoes as $autenticacao_id => $autenticacao) {
				if (next($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
					$ultimo_autenticacao = false;
				} else {
					prev($autenticacoes);
					$ultimo_autenticacao = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_autenticacao) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="autenticacao['.$autenticacao_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$autenticacao.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}

			foreach ($pontos as $ponto_id => $ponto) {
				if (next($pontos) || sizeof($emails)) {
					$ultimo_ponto = false;
				} else {
					prev($pontos);
					$ultimo_ponto = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_ponto) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="ponto['.$ponto_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$ponto.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}

			foreach ($emails as $email_id => $email) {
				if (next($emails)) {
					$ultimo_email = false;
				} else {
					prev($emails);
					$ultimo_email = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_email) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="email['.$email_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$email.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}
			
		} else {
			
			if (!$contratos) {
				$servicos .= '<tr bgcolor="#E8E8E8"><td colspan="3" class="txtAzulEscuro11">Nenhum serviço disponivel.</td></tr>';
			} else {
				
				foreach ($contratos as $contrato_id => $contrato) {
					
					$assinaturas = $this->buscaAssinaturas($contrato_id);
					$assinaturas_com_ajuste = $ajuste_id ? $this->buscaAssinaturasComAjuste($contrato_id, $ajuste_id) : array();
					
					$servicos .= '<tr><td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="contrato['.$contrato_id.']" id="contrato_'.$contrato_id.'" onclick="contratoClick(this, '.$contrato_id.')"'.(($ativo) ? "" : " disabled").(count($assinaturas) == count($assinaturas_com_ajuste) ? " checked" : "").'></td>';
					$servicos .= '<td class="txtAzulEscuro11" colspan="3" width="100%"><label for="contrato_'.$contrato_id.'">&nbsp;<b>'.$contrato.'</b></label></td>';
					$servicos .= '</tr>';
					
					$assinatura_i = 1;
					
					foreach ($assinaturas as $assinatura_id => $assinatura) {
													
						$iptvs = $this->buscaServicosIptv($assinatura_id, $ajuste_id);
						$autenticacoes = $this->buscaServicosAutenticacoes($assinatura_id, $ajuste_id);
						$pontos = $this->buscaServicosPontos($assinatura_id, $ajuste_id);
						$emails = $this->buscaServicosEmails($assinatura_id, $ajuste_id);

						if (next($assinaturas)) {
							$ultima_assinatura = false;
						} else {
							prev($assinaturas);
							$ultima_assinatura = true;
						}
						
						$checked = ($assinaturas_com_ajuste[$assinatura_id]) ? " checked" : "";
						
						$servicos .= '<tr bgcolor="#E8E8E8">';
						$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultima_assinatura) ? "bottom" : "").'.gif" width="19" height="16"></td>';
						$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="assinatura['.$assinatura_id.']" value = "1" id="assinatura_'.$contrato_id.'_'.$assinatura_i.'" onclick="assinaturaClick(this, '.$contrato_id.', '.$assinatura_i.')"'.(($ativo) ? "" : " disabled").$checked.'></td>';
						$servicos .= '<td class="txtAzulEscuro11" width="100%" colspan="2"><label for="assinatura_'.$contrato_id.'_'.$assinatura_i.'">&nbsp;'.$assinatura.'</label></td>';
						$servicos .= '</tr>';
						
						$servicos_i = 1;
						
						// Lista Serviços
												
						if ($iptvs) {
							$iptvs_com_ajuste = $this->buscaIptvsComAjuste($ajuste_id);
							foreach ($iptvs as $iptv_id => $iptv) {
								if (next($iptvs) || sizeof($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
									$ultimo_iptv = false;
								} else {
									prev($iptvs);
									$ultimo_iptv = true;
								}
								$checked = ($iptvs_com_ajuste[$iptv_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_iptv) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="iptv['.$iptv_id.']" value = "1" id="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'" onclick="servicoClick(this, '.$contrato_id.', '.$assinatura_i.')"'.$checked.(($ativo) ? "" : " disabled").'></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$iptv.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}

						if ($autenticacoes) {
							$autenticacoes_com_ajuste = $this->buscaAutenticacoesComAjuste($ajuste_id);
							foreach ($autenticacoes as $autenticacao_id => $autenticacao) {
								if (next($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
									$ultimo_autenticacao = false;
								} else {
									prev($autenticacoes);
									$ultimo_autenticacao = true;
								}
								$checked = ($autenticacoes_com_ajuste[$autenticacao_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_autenticacao) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="autenticacao['.$autenticacao_id.']" value = "1" id="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'" onclick="servicoClick(this, '.$contrato_id.', '.$assinatura_i.')"'.$checked.(($ativo) ? "" : " disabled").'></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$autenticacao.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}
						
						if ($pontos) {
							$pontos_com_ajuste = $this->buscaPontosComAjuste($ajuste_id);
							foreach ($pontos as $ponto_id => $ponto) {
								if (next($pontos) || sizeof($emails)) {
									$ultimo_ponto = false;
								} else {
									prev($pontos);
									$ultimo_ponto = true;
								}
								$checked = ($pontos_com_ajuste[$ponto_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_ponto) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="ponto['.$ponto_id.']" value = "1" id="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'" onclick="servicoClick(this, '.$contrato_id.', '.$assinatura_i.')"'.$checked.(($ativo) ? "" : " disabled").'></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$ponto.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}
						
						if ($emails) {
							$emails_com_ajuste = $this->buscaEmailsComAjuste($ajuste_id);
							foreach ($emails as $email_id => $email) {
								if (next($emails)) {
									$ultimo_email = false;
								} else {
									prev($emails);
									$ultimo_email = true;
								}
								$checked = ($emails_com_ajuste[$email_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_email) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="email['.$email_id.']" value = "1" id="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'" onclick="servicoClick(this, '.$contrato_id.', '.$assinatura_i.')"'.$checked.(($ativo) ? "" : " disabled").'></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$email.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}
						
						$assinatura_i++;
					
					}
				
				}
			
			}
			
		}
		
		$servicos .= '</table>';
		
		return $servicos;
		
	}

	function buscaServicosAjustesBloqueioTemporario($cliente_id, $ajuste_id = null, $ativo = true) {
		
		$contratos = $this->buscaContratos($cliente_id);
		$servicos_i = 0;

		$servicos = '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		
		if ($this->dados_item["assinatura_id"]) {
			
			$assinatura = $this->buscaDadosAssinatura($this->dados_item["assinatura_id"]);
			
			$iptvs = $this->buscaServicosIptv($this->dados_item["assinatura_id"], $ajuste_id);
			$autenticacoes = $this->buscaServicosAutenticacoes($this->dados_item["assinatura_id"], $ajuste_id);
			$pontos = $this->buscaServicosPontos($this->dados_item["assinatura_id"], $ajuste_id);
			$emails = $this->buscaServicosEmails($this->dados_item["assinatura_id"], $ajuste_id);
			
			$servicos .= '<tr><td class="txtBranco11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="contrato" disabled checked></td>';
			$servicos .= '<td class="txtAzulEscuro11" colspan="3" width="100%">&nbsp;<b>Assinatura com Ajuste Programado</b></td>';
			$servicos .= '</tr>';
			$servicos .= '<tr bgcolor="#E8E8E8">';
			$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join.gif" width="19" height="16"></td>';
			$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="assinatura" disabled checked></td>';
			$servicos .= '<td class="txtAzulEscuro11" width="100%" colspan="2">&nbsp;'.$assinatura["nome"].'</td>';
			$servicos .= '</tr>';
			
			// Lista Serviços
			
			foreach ($iptvs as $iptv_id => $iptv) {
				if (next($iptvs) || sizeof($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
					$ultimo_iptv = false;
				} else {
					prev($iptvs);
					$ultimo_iptv = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_iptv) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="iptv['.$iptv_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$iptv.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}
			
			foreach ($autenticacoes as $autenticacao_id => $autenticacao) {
				if (next($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
					$ultimo_autenticacao = false;
				} else {
					prev($autenticacoes);
					$ultimo_autenticacao = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_autenticacao) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="autenticacao['.$autenticacao_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$autenticacao.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}
			
			foreach ($pontos as $ponto_id => $ponto) {
				if (next($pontos) || sizeof($emails)) {
					$ultimo_ponto = false;
				} else {
					prev($pontos);
					$ultimo_ponto = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_ponto) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="ponto['.$ponto_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$ponto.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}

			foreach ($emails as $email_id => $email) {
				if (next($emails)) {
					$ultimo_email = false;
				} else {
					prev($emails);
					$ultimo_email = true;
				}
				$servicos .= '<tr bgcolor="#E8E8E8">';
				$servicos .= '<td class="txtAzulEscuro11" width="19"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_email) ? "bottom" : "").'.gif" width="19" height="16"></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" name="email['.$email_id.']" value = "1" checked disabled></td>';
				$servicos .= '<td class="txtAzulEscuro11" width="100%">&nbsp; '.$email.'</td>';
				$servicos .= '</tr>';
				$servicos_i++;
			}
			
		} else {
			
			if (!$contratos) {
				$servicos .= '<tr bgcolor="#E8E8E8"><td colspan="3" class="txtAzulEscuro11">Nenhum serviço disponivel.</td></tr>';
			} else {
				
				foreach ($contratos as $contrato_id => $contrato) {
					
					$assinaturas = $this->buscaAssinaturas($contrato_id);
					$assinaturas_com_ajuste = $ajuste_id ? $this->buscaAssinaturasComAjuste($contrato_id, $ajuste_id) : array();
					$assinatura_i = 1;
					
					$servicos .= '<tr><td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px"  id="contrato_'.$contrato_id.'" checked disabled ></td>';
					$servicos .= '<input type="hidden" name="contrato['.$contrato_id.']" value="1">';
					$servicos .= '<td class="txtAzulEscuro11" colspan="3" width="100%"><label for="contrato_'.$contrato_id.'">&nbsp;<b>'.$contrato.'</b></label></td>';
					$servicos .= '</tr>';
					
					foreach ($assinaturas as $assinatura_id => $assinatura) {
						
						$servicos_i = 1;
						$checked = ($assinaturas_com_ajuste[$assinatura_id]) ? " checked" : "";
						$iptvs = $this->buscaServicosIptv($assinatura_id, $ajuste_id);
						$autenticacoes = $this->buscaServicosAutenticacoes($assinatura_id, $ajuste_id);
						$pontos = $this->buscaServicosPontos($assinatura_id, $ajuste_id);
						$emails = $this->buscaServicosEmails($assinatura_id, $ajuste_id);
						
						if (next($assinaturas)) {
							$ultima_assinatura = false;
						} else {
							prev($assinaturas);
							$ultima_assinatura = true;
						}
						
						$servicos .= '<tr bgcolor="#E8E8E8">';
						$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultima_assinatura) ? "bottom" : "").'.gif" width="19" height="16"></td>';
						$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox" style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px"  value = "1" id="assinatura_'.$contrato_id.'_'.$assinatura_i.'" checked disabled ></td>';
						$servicos .= '<input type="hidden" name="assinatura['.$assinatura_id.']" value="1">';
						$servicos .= '<td class="txtAzulEscuro11" width="100%" colspan="2"><label for="assinatura_'.$contrato_id.'_'.$assinatura_i.'">&nbsp;'.$assinatura.'</label></td>';
						$servicos .= '</tr>';
						
						// Lista Serviços

						if ($iptvs) {
							$iptvs_com_ajuste = $this->buscaIptvsComAjuste($ajuste_id);
							foreach ($iptvs as $iptv_id => $iptv) {
								if (next($iptvs) || sizeof($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
									$ultimo_iptv = false;
								} else {
									prev($iptvs);
									$ultimo_iptv = true;
								}
								$checked = ($iptvs_com_ajuste[$iptv_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_iptv) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" value = "1" id="servico_'.$contrato_id.'" checked disabled ></td>';
								$servicos .= '<input type="hidden" name="iptv['.$iptv_id.']" value="1">';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$iptv.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}

						if ($autenticacoes) {
							$autenticacoes_com_ajuste = $this->buscaAutenticacoesComAjuste($ajuste_id);
							foreach ($autenticacoes as $autenticacao_id => $autenticacao) {
								if (next($autenticacoes) || sizeof($pontos) || sizeof($emails)) {
									$ultimo_autenticacao = false;
								} else {
									prev($autenticacoes);
									$ultimo_autenticacao = true;
								}
								$checked = ($autenticacoes_com_ajuste[$autenticacao_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_autenticacao) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" value = "1" id="servico_'.$contrato_id.'" checked disabled ></td>';
								$servicos .= '<input type="hidden" name="autenticacao['.$autenticacao_id.']" value="1">';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$autenticacao.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}
						
						if ($pontos) {
							$pontos_com_ajuste = $this->buscaPontosComAjuste($ajuste_id);
							foreach ($pontos as $ponto_id => $ponto) {
								if (next($pontos) || sizeof($emails)) {
									$ultimo_ponto = false;
								} else {
									prev($pontos);
									$ultimo_ponto = true;
								}
								$checked = ($pontos_com_ajuste[$ponto_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_ponto) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" value = "1" id="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'" checked disabled ></td>';
								$servicos .= '<input type="hidden" name="ponto['.$ponto_id.']" value="1">';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$ponto.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}
						
						if ($emails) {
							$emails_com_ajuste = $this->buscaEmailsComAjuste($ajuste_id);
							foreach ($emails as $email_id => $email) {
								if (next($emails)) {
									$ultimo_email = false;
								} else {
									prev($emails);
									$ultimo_email = true;
								}
								$checked = ($emails_com_ajuste[$email_id]) ? " checked" : "";
								$servicos .= '<tr bgcolor="#E8E8E8">';
								$servicos .= '<td class="txtAzulEscuro11" width="19">'.((!$ultima_assinatura) ? '<img src="./imagens/line.gif" width="19" height="16">' : '').'</td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><img src="./imagens/join'.((!$ultimo_email) ? "bottom" : "").'.gif" width="19" height="16"></td>';
								$servicos .= '<td class="txtAzulEscuro11" width="19"><input type="checkbox"  style="width:12px;height:12px;margin-top:0px;margin-bottom:0px;margin-left:2px;margin-right:2px" value = "1" id="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'" checked disabled ></td>';
								$servicos .= '<input type="hidden" name="email['.$email_id.']" value="1">';
								$servicos .= '<td class="txtAzulEscuro11" width="100%"><label for="servico_'.$contrato_id.'_'.$assinatura_i.'_'.$servicos_i.'">&nbsp; '.$email.'</label></td>';
								$servicos .= '</tr>';
								$servicos_i++;
							}
						}

						$assinatura_i++;
						
					}
					
				}
				
			}
			
		}
		
		$servicos .= '</table>';
		
		return $servicos;
	
	}

	function ehSuspendivel() {
		
		if ($this->dados_item["status_id"] != 5) return false;
		if ($this->buscaAjusteFilhoAtivo($_GET["i"])) return false;
		if ($this->dados_item["categoria_id"] != 1 && $this->dados_item["categoria_id"] != 3) return false;
		if ($this->dados_item["categoria_id"] == 3 && !$this->permissoes[$this->dados_modulo["nome"]]["suspensao_temporaria_contrato_inativo"]) return false;
		if ($this->permissoes[$this->dados_modulo["nome"]]["suspensao_temporaria_ilimitada"]) return true;
		if ($this->dados_item["manual"]) return false;
		
		$limite = $this->permissoes[$this->dados_modulo["nome"]]["suspensao_temporaria_plus"] ? 3 : ($this->permissoes[$this->dados_modulo["nome"]]["suspensao_temporaria"] ? 1 : 0);
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(saj_id)
			FROM
				tab_servicos_ajustes
			WHERE
				saj_pai = {$this->dados_item["id"]}");
		
		return $this->bancodedados->resultado($query) < $limite;
	
	}

	function ehFinalizavel() {

		if (!in_array($this->dados_item["status_id"], array(5, 9))) return false;
		if (in_array($this->dados_item["categoria_id"], array(23, 24, 25, 26, 27)) && $this->permissoes[$this->dados_modulo["nome"]]["finalizar_plano_dia"]) return true;
		if (in_array($this->dados_item["categoria_id"], array(10)) && $this->permissoes[$this->dados_modulo["nome"]]["finalizar_bloqueio_temporario"]) return true;
		if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar_plus"]) return true;
		
		return false;
		
	}

	##############
	### Fechar ###
	##############

	function fechar() {
		
		unset($this->conteudo);
		
		$this->conteudo .= "<script type=\"text/javascript\">if (!window.opener.closed && window.opener.buscaAjustesCliente) window.opener.buscaAjustesCliente({$_GET["cliente"]})</script>";
		$this->conteudo .= "<script type=\"text/javascript\">window.close()</script>";
		
	}

	#################
	### Adicionar ###
	#################

	function adicionar() {
		
		if (!$_POST) {
			
			$this->conteudo .= $this->carregaArquivo("servicos_ajustes.adicionar.inc.php");
			
			// Dados
			$this->conteudo = str_replace('[cliente]', $this->buscaCliente($_GET["cliente"]), $this->conteudo);
			$this->conteudo = str_replace('[cliente_id]', $_GET["cliente"], $this->conteudo);
			$this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(null, $this->permissoes["servicos_ajustes"]["adicionar_plus"]), "", "n"), $this->conteudo);
			$this->conteudo = str_replace('[servicos]', $this->geraSelect($this->buscaServicos()), $this->conteudo);
			$this->conteudo = str_replace('[dia_semana]', $this->geraSelect($this->dias_semana), $this->conteudo);
			$this->conteudo = str_replace('[hora_inicio]', $this->geraSelect($this->buscaHorarios(), "", "n"), $this->conteudo);
			$this->conteudo = str_replace('[hora_termino]', $this->geraSelect($this->buscaHorarios(), "", "n"), $this->conteudo);
			$this->conteudo = str_replace('[servicos_ajustes]', $this->buscaServicosAjustes($_GET["cliente"]), $this->conteudo);
			$this->conteudo = str_replace('[listagem_regras]', $this->geraRegrasListagem(null, false), $this->conteudo);
			
			// Botões
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&cliente={$_GET["cliente"]}", $this->conteudo);
			
		} else {
			
			$categoria = $_POST["categoria"];
			$dados_categoria = $this->buscaDadosCategoria($categoria);
			
			if ($dados_categoria["editavel"]) {
				$regras = unserialize(stripslashes($_POST["lista_regras"]));
			} else {
				$regras = $dados_categoria["regras"];
			}
			
			$_POST["inicio"] = !$_POST["inicio"] ? "NULL" : "'".substr($_POST["inicio"], 6, 4)."-".substr($_POST["inicio"], 3, 2)."-".substr($_POST["inicio"], 0, 2)." ".substr($_POST["inicio"], 12, 2).":".substr($_POST["inicio"], 15, 2).":00'";
			$_POST["termino"] = !$_POST["termino"] || $dados_categoria["duracao"] ? "NULL" : "'".substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)." ".substr($_POST["termino"], 12, 2).":".substr($_POST["termino"], 15, 2).":00'";
			$duracao = $dados_categoria["duracao"] ? $dados_categoria["duracao"] : "NULL";
			$valor = $dados_categoria["valor"] ? $dados_categoria["valor"] : "NULL";
			
			// insere novo ajuste
			$query = "
				INSERT INTO tab_servicos_ajustes
					(cli_id, sac_id, saj_inicio, saj_termino, saj_manual, saj_valor, saj_duracao, sas_id)
				VALUES
					({$_GET["cliente"]}, $categoria, {$_POST["inicio"]}, {$_POST["termino"]}, 1, $valor, $duracao, 1)";
			
			$this->bancodedados->query($query);
			
			$id = $this->bancodedados->ultimoIdInserido();
			
			// Insere Regras
			$this->registraRegras($id, $regras);
			
			// Adiciona iptv aos ajustes
			if ($_POST["iptv"]) {
				foreach ($_POST["iptv"] as $iptv => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_cip_contem (saj_id, cip_id)
						VALUES
							($id, $iptv)";
					$this->bancodedados->query($query);
				}
			}
			
			// Adiciona autenticacoes aos ajustes
			if ($_POST["autenticacao"]) {
				foreach ($_POST["autenticacao"] as $autenticacao => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_cau_contem (saj_id, cau_id)
						VALUES
							($id, $autenticacao)";
					$this->bancodedados->query($query);
				}
			}
			
			// Adiciona pontos aos ajustes
			if ($_POST["ponto"]) {
				foreach ($_POST["ponto"] as $ponto => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_clp_contem (saj_id, clp_id)
						VALUES
							($id, $ponto)";
					$this->bancodedados->query($query);
				}
			}
			
			// Adiciona emails aos ajustes
			if ($_POST["email"]) {
				foreach ($_POST["email"] as $email => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_cle_contem (saj_id, cle_id)
						VALUES
							($id, $email)";
					$this->bancodedados->query($query);
				}
			}
			
			// Adiciona assinaturas aos ajustes
			if ($_POST["assinatura"]) {
				foreach ($_POST["assinatura"] as $assinatura => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_ass_contem (saj_id, ass_id)
						VALUES
							($id, $assinatura)";
					$this->bancodedados->query($query);
				}
			}
			
			// Adicionar comentário
			$this->registraComentario($id, "<b>Ação</b>: Adicionado", $_SESSION["usuario"]);
			if ($_POST["comentarios"]) {
				$this->registraComentario($id, $_POST["comentarios"], $_SESSION["usuario"]);
			}
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
			
		}
		
	}

	######################################
	### Adicionar Bloqueio Temporrario ###
	######################################

	function adicionar_bloqueio_temporario() {

		if (!$_POST) {

			$this->conteudo .= $this->carregaArquivo("servicos_ajustes.adicionar_bloqueio_temporario.inc.php");

			// Dados
			$this->conteudo = str_replace("[cliente]", $this->buscaCliente($_GET["cliente"]), $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $_GET["cliente"], $this->conteudo);
			$this->conteudo = str_replace("[servicos_ajustes]", $this->buscaServicosAjustesBloqueioTemporario($_GET["cliente"]), $this->conteudo);

			// Botões
			$botoes = array();
			$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar_bloqueio_temporario&cliente={$_GET["cliente"]}", $this->conteudo);

		} else  {

			$converted_date_time_start	= date_create_from_format("d/m/Y H:i:s", $_POST["inicio"].date(" H:i:s")); // Para cálculo da diferença de anos
			$converted_date_time_end	= date_create_from_format("d/m/Y H:i:s", $_POST["termino"]." 23:59:59");   // Para cálculo da diferença de anos

			// Tratamento das datas
			$interval			= $converted_date_time_start->diff($converted_date_time_end);
			$intervaldays		= $interval->days;
			$startyear			= $converted_date_time_start->format("Y");	// Ano inicial
			$endyear			= $converted_date_time_end->format("Y");	// Ano final

			// Valida datas inicio não ser menor que a atual
			$this->verificaDataInicio($converted_date_time_start);

			// Verificação de final menor que início...
			$this->verificaPeriodoDatas($converted_date_time_start, $converted_date_time_end );

			// Verificação de sobreposição de datas...
			$this->verificaSobreposicaoDatas($converted_date_time_end, $converted_date_time_start, $_GET["cliente"]);

			// Verificação do limite de 120 dias por ano...
			$this->verificaLimiteDiasPorAno($startyear, $endyear, $intervaldays, $_GET["cliente"], 120);

			$categoria			= 10;// $_POST["categoria"];  		
			$dados_categoria	= $this->buscaDadosCategoria($categoria);

			if ($dados_categoria["editavel"]) {
				$regras			= unserialize(stripslashes($_POST["lista_regras"]));
			} else {
				$regras			= $dados_categoria["regras"];
			}

			$_POST["inicio"]	= $converted_date_time_start->format("Y-m-d H:i:s");
			$_POST["termino"]	= $converted_date_time_end->format("Y-m-d H:i:s");  		

			$duracao			= $dados_categoria["duracao"] ? $dados_categoria["duracao"] : "NULL";
			$valor				= $dados_categoria["valor"] ? $dados_categoria["valor"] : "NULL";

			// Insere novo ajuste
			$query = "
				INSERT INTO 
					tab_servicos_ajustes (cli_id, sac_id, saj_inicio, saj_termino, saj_manual, saj_valor, saj_duracao, sas_id)
				VALUES
					({$_GET["cliente"]}, $categoria, '{$_POST["inicio"]}', '{$_POST["termino"]}', 1, $valor, $duracao, 1)";
			
			$this->bancodedados->query($query);
			
			$id = $this->bancodedados->ultimoIdInserido();

			// Insere Regras
			$this->registraRegras($id, $regras);

			// Adiciona iptv aos ajustes
			if ($_POST["iptv"]) {
				foreach ($_POST["iptv"] as $iptv => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_cip_contem (saj_id, cip_id)
						VALUES
							($id, $iptv)";
					$this->bancodedados->query($query);
				}
			}

			// Adiciona autenticacao aos ajustes
			if ($_POST["autenticacao"]) {
				foreach ($_POST["autenticacao"] as $autenticacao => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_cau_contem (saj_id, cau_id)
						VALUES
							($id, $autenticacao)";
					$this->bancodedados->query($query);
				}
			}

			// Adiciona pontos aos ajustes
			if ($_POST["ponto"]) {
				foreach ($_POST["ponto"] as $ponto => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_clp_contem (saj_id, clp_id)
						VALUES
							($id, $ponto)";
					$this->bancodedados->query($query);
				}
			}

			// Adiciona emails aos ajustes
			if ($_POST["email"]) {
				foreach ($_POST["email"] as $email => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_cle_contem (saj_id, cle_id)
						VALUES
							($id, $email)";
					$this->bancodedados->query($query);
				}
			}

			// Adiciona assinaturas aos ajustes
			if ($_POST["assinatura"]) {
				foreach ($_POST["assinatura"] as $assinatura => $selecionado) {
					$query = "
						INSERT INTO
							rel_saj_ass_contem (saj_id, ass_id)
						VALUES
							($id, $assinatura)";
					$this->bancodedados->query($query);
				}
			}

			// Adicionar comentário
			$this->registraComentario($id, "<b>Ação</b>: Adicionado", $_SESSION["usuario"]);

			if ($_POST["comentarios"]) {
				$this->registraComentario($id, $_POST["comentarios"], $_SESSION["usuario"]);
			}

			$this->buscaDadosItem($id);
			$this->ativarServicoAjuste($this->dados_item, $this->dados_item["inicio"]);

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");

		}

	}

	###########
	### Ver ###
	###########

	function ver() {
			
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$categorias = $this->buscaCategorias();

			$this->conteudo .= $this->carregaArquivo("servicos_ajustes.ver.inc.php");
			
			// Dados
			$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace('[servicos]', $this->geraSelect($this->buscaServicos()), $this->conteudo);
			$this->conteudo = str_replace('[dia_semana]', $this->geraSelect($this->dias_semana), $this->conteudo);
			$this->conteudo = str_replace('[hora_inicio]', $this->geraSelect($this->buscaHorarios(), "", "n"), $this->conteudo);
			$this->conteudo = str_replace('[hora_termino]', $this->geraSelect($this->buscaHorarios(), "", "n"), $this->conteudo);
			$this->conteudo = str_replace('[listagem_regras]', $this->geraRegrasListagem($this->buscaListaRegras($_GET["i"]), false), $this->conteudo);
			$this->conteudo = str_replace('[categoria]', $categorias[$this->dados_item["categoria_id"]], $this->conteudo);
			$this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
			$this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
			$this->conteudo = str_replace('[servicos_ajustes]', $this->buscaServicosAjustes($this->dados_item["cliente_id"], $_GET["i"], false), $this->conteudo);
			$this->conteudo = str_replace('[lista_suspensoes]', $this->buscaSuspensoes($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace('[lista_comentarios]', $this->buscaComentarios($_GET["i"]), $this->conteudo);
			$this->conteudo = str_replace('[valor]', $this->formataDinheiro($this->dados_item["valor"]), $this->conteudo);
			$this->conteudo = str_replace('[duracao]', $this->dados_item["duracao"] ? "{$this->dados_item["duracao"]} dia(s)" : "", $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status"], $this->conteudo);
			
			// Botões
			$botoes = array();
			if ($this->ehSuspendivel()) $botoes[] = '<input type="button" name="suspensao_temporaria" class="buttonAzulEscuroBranco11" style="width:155" value="Suspensão Temporária" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=suspensao_temporaria&i='.$this->dados_item["id"].'\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["aprovar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:65" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:65" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] < 5 && !$this->dados_item["sistema"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:65" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" style="width:65" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["status_id"] <= 3) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
			// Ação
			$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
		
		} else {
			$this->fechar();
		}
		
	}

	##############
	### Editar ###
	##############

	function editar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if (($this->dados_item["status_id"] == 6) || ($this->dados_item["sistema"])) {
			$this->ver();
			return;
		}
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				if ($this->dados_item["status_id"] == 1 && !$this->dados_item["assinatura_id"]) {
					$this->conteudo .= $this->carregaArquivo("servicos_ajustes.editar.inc.php");
				} else {
					$this->conteudo .= $this->carregaArquivo("servicos_ajustes.editar_ativo.inc.php");
				}
				
				// Dados
				$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias($this->dados_item["categoria_id"]), $this->dados_item["categoria_id"], "n"), $this->conteudo);
				$this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
				$this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
				$this->conteudo = str_replace('[editar_data]', $this->dados_item["assinatura_id"] ? "disabled" : "", $this->conteudo);
				$this->conteudo = str_replace('[servicos_ajustes]', $this->buscaServicosAjustes($this->dados_item["cliente_id"], $_GET["i"], ($this->dados_item["status_id"] == 1 && !$this->dados_item["assinatura_id"])), $this->conteudo);
				$this->conteudo = str_replace('[listagem_regras]', $this->geraRegrasListagem($this->buscaListaRegras($_GET["i"]), $this->dados_item["editavel"] && $this->dados_item["status_id"] == 1 && !$this->dados_item["assinatura_id"]), $this->conteudo);
				$this->conteudo = str_replace('[lista_regras]', serialize($this->buscaListaRegras($_GET["i"])), $this->conteudo);
				$this->conteudo = str_replace('[valor]', $this->formataDinheiro($this->dados_item["valor"]), $this->conteudo);
				$this->conteudo = str_replace('[duracao]', $this->dados_item["duracao"] ? "{$this->dados_item["duracao"]} dia(s)" : "", $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status"], $this->conteudo);
				$this->conteudo = str_replace('[lista_comentarios]', $this->buscaComentarios($_GET["i"]), $this->conteudo);
				
				// Botões
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				if ($this->ehSuspendivel()) $botoes[] = '<input type="button" name="suspensao_temporaria" class="buttonAzulEscuroBranco11" style="width:155" value="Suspensão Temporária" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=suspensao_temporaria&i='.$this->dados_item["id"].'\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["aprovar"] && $this->dados_item["status_id"] == 1 && !$this->dados_item["assinatura_id"]) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" style="width:65" value="Aprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=aprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && $this->dados_item["status_id"] == 1 && !$this->dados_item["assinatura_id"]) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" style="width:65" value="Reprovar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] < 5 && !$this->dados_item["assinatura_id"]) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" style="width:65" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" style="width:65" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] == 2 && !$this->dados_item["assinatura_id"]) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" style="width:65" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
				if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["status_id"] <= 3) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" style="width:65" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				
				// Ação
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}&cliente={$_GET["cliente"]}", $this->conteudo);
			
			} else {
				
				if ($this->dados_item["status_id"] == 1 && !$this->dados_item["assinatura_id"]) {
					
					$categoria = $_POST["categoria"];
					
					$dados_categoria = $this->buscaDadosCategoria($categoria);
					
					if ($dados_categoria["editavel"]) {
						$regras = unserialize(stripslashes($_POST["lista_regras"]));
					} else {
						$regras = $dados_categoria["regras"];
					}
					
					$inicio = (!$_POST["inicio"]) ? "NULL" : "'".substr($_POST["inicio"], 6, 4).substr($_POST["inicio"], 3, 2).substr($_POST["inicio"], 0, 2).substr($_POST["inicio"], 12, 2).substr($_POST["inicio"], 15, 2)."00'";
					$termino = (!$_POST["termino"]) ? "NULL" : "'".substr($_POST["termino"], 6, 4).substr($_POST["termino"], 3, 2).substr($_POST["termino"], 0, 2).substr($_POST["termino"], 12, 2).substr($_POST["termino"], 15, 2)."00'";
					
					$query = "
					UPDATE
						tab_servicos_ajustes
					SET
						sac_id = '$categoria',
						saj_inicio = $inicio,
						saj_termino = $termino
					WHERE
						saj_id = {$_GET["i"]}";
					
					$this->bancodedados->query($query);
					
					// Registra regras
					$this->registraRegras($_GET["i"], $regras);
					
					// Remove serviços aos ajustes
					$query = "DELETE FROM rel_saj_cip_contem WHERE saj_id = {$_GET["i"]}";
					$this->bancodedados->query($query);
					$query = "DELETE FROM rel_saj_cau_contem WHERE saj_id = {$_GET["i"]}";
					$this->bancodedados->query($query);
					$query = "DELETE FROM rel_saj_clp_contem WHERE saj_id = {$_GET["i"]}";
					$this->bancodedados->query($query);
					$query = "DELETE FROM rel_saj_cle_contem WHERE saj_id = {$_GET["i"]}";
					$this->bancodedados->query($query);
					
					// Adiciona serviços aos ajustes

					if ($_POST["iptv"]) {
						foreach ($_POST["iptv"] as $iptv => $selecionado) {
							$query = "
								INSERT INTO
									rel_saj_cip_contem (saj_id, cip_id)
								VALUES
									({$_GET["i"]}, $iptv)";
							$this->bancodedados->query($query);
						}
					}

					if ($_POST["autenticacao"]) {
						foreach ($_POST["autenticacao"] as $autenticacao => $selecionado) {
							$query = "
								INSERT INTO
									rel_saj_cau_contem (saj_id, cau_id)
								VALUES
									({$_GET["i"]}, $autenticacao)";
							$this->bancodedados->query($query);
						}
					}
					
					if ($_POST["ponto"]) {
						foreach ($_POST["ponto"] as $ponto => $selecionado) {
							$query = "
								INSERT INTO
									rel_saj_clp_contem (saj_id, clp_id)
								VALUES
									({$_GET["i"]}, $ponto)";
							$this->bancodedados->query($query);
						}
					}
					
					if ($_POST["email"]) {
						foreach ($_POST["email"] as $email => $selecionado) {
							$query = "
								INSERT INTO
									rel_saj_cle_contem (saj_id, cle_id)
								VALUES
									({$_GET["i"]}, $email)";
							$this->bancodedados->query($query);
						}
					}
					
					// Remove assinaturas aos ajustes
					$query = "DELETE FROM rel_saj_ass_contem WHERE saj_id = {$_GET["i"]}";
					$this->bancodedados->query($query);

					// Adiciona assinaturas aos ajustes
					
					if ($_POST["assinatura"]) {
						foreach ($_POST["assinatura"] as $assinatura => $selecionado) {
							$query = "
								INSERT INTO
									rel_saj_ass_contem (saj_id, ass_id)
								VALUES
									({$_GET["i"]}, $assinatura)";
							$this->bancodedados->query($query);
						}
					}
					
				} elseif (!$this->dados_item["assinatura_id"]) {
					
					if ($_POST["termino"] != $this->dados_item["termino"] && !$this->dados_item["assinatura_id"]) {
						if ($this->dados_item["termino"]) {
							$this->registraComentario($_GET["i"], "<b>Alteração Término</b>: <b>{$this->dados_item["termino"]}</b> para <b>{$_POST["termino"]}</b>", $_SESSION["usuario"]);
						} else {
							$this->registraComentario($_GET["i"], "<b>Definição Término</b>: <b>{$_POST["termino"]}</b>", $_SESSION["usuario"]);
						}
						$termino = (!$_POST["termino"]) ? "NULL" : "'".substr($_POST["termino"], 6, 4).substr($_POST["termino"], 3, 2).substr($_POST["termino"], 0, 2).substr($_POST["termino"], 12, 2).substr($_POST["termino"], 15, 2)."00'";
						$query = "
							UPDATE
								tab_servicos_ajustes
							SET
								saj_termino = $termino
							WHERE
								saj_id = {$_GET["i"]}";
						$this->bancodedados->query($query);
					}
				
				}
				
				if ($_POST["comentarios"]) {
					$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
				}
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}

	function registraRegras($ajuste_id, $regras) {
		
		// Remove Regras Antigas
		$query = "DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = $ajuste_id";
		$this->bancodedados->query($query);
		
		// Insere Regras Novas
		foreach ($regras as $regra) {
			
			$regra["servico"] = $regra["servico"] ? $regra["servico"] : "NULL";
			$regra["ajuste_unidade"] = $regra["ajuste_unidade"] ? $regra["ajuste_unidade"] : 0;
			$regra["ajuste"] = $regra["ajuste_tipo"] == 1 ? $regra["ajuste_valor"] / 100 : $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
			$regra["dia_semana"] = $regra["dia_semana"] ? $regra["dia_semana"] : "NULL";
			$regra["hora_inicio"] = $regra["hora_inicio"] && $regra["hora_inicio"] != '00:00' ? "'{$regra["hora_inicio"]}'" : "NULL";
			$regra["hora_termino"] = $regra["hora_termino"] && $regra["hora_termino"] != '24:00' ? "'{$regra["hora_termino"]}'" : "NULL";
			
			$query = "
				INSERT INTO
					tab_servicos_ajustes_regras (saj_id, sat_id, sar_ajuste, sar_dia_semana, sar_hora_inicio, sar_hora_termino)
				VALUES
					($ajuste_id, {$regra["servico"]}, {$regra["ajuste"]}, {$regra["dia_semana"]}, {$regra["hora_inicio"]}, {$regra["hora_termino"]})";
			$this->bancodedados->query($query);
			
		}
	
	}

	function buscaListaRegras($id) {
		
		$regras = array();
		
		$query = "
			SELECT
				sat_id AS servico,
				sar_ajuste AS ajuste,
				sar_dia_semana AS dia_semana,
				DATE_FORMAT(sar_hora_inicio, '%H:%i') AS hora_inicio,
				DATE_FORMAT(sar_hora_termino, '%H:%i') AS hora_termino
			FROM
				tab_servicos_ajustes_regras
			WHERE
				saj_id = $id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["servico"] = $dados["servico"] ? $dados["servico"] : 0;
			$dados["ajuste_tipo"] = $dados["ajuste"] <= 1 ? 1 : 2;
			$dados["ajuste_unidade"] = $dados["ajuste_tipo"] == 2 ? floor(log($dados["ajuste"], 1024)) : "";
			$dados["ajuste_valor"] = $dados["ajuste"] <= 1 ? $dados["ajuste"] * 100 : floor($dados["ajuste"] / pow(1024, $dados["ajuste_unidade"]));
			$dados["dia_semana"] = $dados["dia_semana"] ? $dados["dia_semana"] : 0;
			$dados["hora_inicio"] = $dados["hora_inicio"] ? $dados["hora_inicio"] : "00:00";
			$dados["hora_termino"] = $dados["hora_termino"] ? $dados["hora_termino"] : "24:00";
			
			$regras[] = $dados;
			
		}
		
		return $regras;
	
	}

	##############
	### Listar ###
	##############

	function listar() {
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=servicos_ajustes" id="servicos_ajustes.js"></script>';
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
		
		$itens = array();
		
		// Código
		$itens[] = array ("tipo" => "input", "nome" => "codigo", "descricao" => "Código", "valor" => "", "where" => "(tab_servicos_ajustes.saj_id LIKE '[valor]')");
		// Cliente
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
		// Origem
		$origens = $this->buscaOrigens();
		foreach ($origens as $id => $nome) {
		$select_origens[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "origem", "descricao" => "Origem", "valor" => $select_origens, "where" => "(tab_clientes.clo_id = [valor])");

		// Condomínio
		$condominios = $this->buscaCondominios();
		foreach ($condominios as $id => $nome) {
		$select_condominios[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "condominio", "descricao" => "Condomínio", "valor" => $select_condominios, "where" => "(tab_clientes.con_id = [valor])");
		// Categoria
		$categorias = $this->buscaCategorias(null, true);
		foreach ($categorias as $id => $nome) {
		$select_categorias[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "(tab_servicos_ajustes.sac_id LIKE '[valor]')");
		// Status
		$status = $this->buscaStatus();
		foreach ($status as $id => $nome) {
		$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$select_status[] = array ("id" => "-1", "nome" => "Análise", "where" => "(tab_servicos_ajustes.sas_id IN (1, 2, 3, 4))");
		$select_status[] = array ("id" => "-2", "nome" => "Inativo", "where" => "(tab_servicos_ajustes.sas_id IN (6, 7, 8))");
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_servicos_ajustes.sas_id LIKE '[valor]')");
		
		// Inicializa filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
		return $filtro;
		
	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}

	function buscaItens($filtro, $pagina) {
		
		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				tab_servicos_ajustes.saj_id AS id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_servicos_ajustes_status.sas_nome AS status
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_servicos_ajustes.cli_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
			WHERE
				(saj_pai IS NULL) AND
				$filtro->parametros_sql".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				tab_servicos_ajustes.saj_inicio
			LIMIT
				$limit_inicio,
				$limit_fim";
		
		$query = $this->bancodedados->query($query);
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["link"] = "?m=clientes&a=ver_dt&i={$dados["cliente_id"]}";
			}
			$dados["cliente_nome"] = "<div align=\"left\">{$dados["cliente_nome"]}</div>";
			$dados["categoria"] = "<div align=\"left\">{$dados["categoria"]}</div>";
			$dados["inicio"] = ($dados["inicio"]) ? $dados["inicio"] : "-";
			$dados["termino"] = ($dados["termino"]) ? $dados["termino"] : "-";
			$dados["status"] = $dados["status"].($this->possuiSuspensao($dados["id"]) ? "<br>(Suspensão Temporária)" : "");
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
		
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}

	function possuiSuspensao($servico_ajuste_id) {
		
		$query = "
			SELECT
				COUNT(saj_id)
			FROM
				tab_servicos_ajustes
			WHERE
				saj_pai = $servico_ajuste_id AND
				sas_id IN (5, 9)";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query) > 0;
	
	}

	function geraListagem($itens) {
		
		$cabecalho = array("contador" => "#", "cliente_id" => "ID", "cliente_nome" => "Cliente", "categoria" => "Categoria", "inicio" => "Início", "termino" => "Término", "status" => "Status");
		$larguras = array("20", "40", "", "", "100", "100", "120");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, $larguras, null, array("right"));
		
		return $listagem;
		
	}

	function buscaStatus() {
		
		$lista = array();
		
		$query = "
			SELECT
				sas_id AS id,
				sas_nome AS nome
			FROM
				tab_servicos_ajustes_status
			ORDER BY
				nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}
		
		return $lista;
		
	}

	###############
	### Remover ###
	###############

	function remover() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST["confirmar"]) {
				
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.remover.inc.php");
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_cip_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_cau_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_clp_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_cle_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_clh_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_clb_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM rel_saj_ass_contem WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_comentarios WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_clientes_iptv_ajustes WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes_ajustes WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_clientes_pontos_ajustes WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_clientes_emails_ajustes WHERE saj_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_servicos_ajustes WHERE saj_id = {$_GET["i"]}");
				
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}&cliente={$this->dados_item["cliente_id"]}");
				
			}
		
		} else {
			$this->fechar();
		}
	
	}

	###############
	### Aprovar ###
	###############

	function aprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
			
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.aprovar.inc.php");
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=aprovar&i={$_GET["i"]}", $this->conteudo);
			
			} else {
			
				$query = "UPDATE tab_servicos_ajustes SET sas_id = 2 WHERE saj_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Aprovado", $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
		
		} else {
			$this->fechar();
		}
	
	}

	################
	### Reprovar ###
	################

	function reprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.reprovar.inc.php");
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=reprovar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$query = "UPDATE tab_servicos_ajustes SET sas_id = 3 WHERE saj_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Reprovado", $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
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
				
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.ativar.inc.php");
				$this->conteudo = str_replace("[inicio]", $this->dados_item["inicio"] ? $this->dados_item["inicio"] : date("d/m/Y (H:i)"), $this->conteudo);
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$this->ativarServicoAjuste($this->dados_item, $_POST["inicio"], $_POST["comentario"], $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
		
		} else {
			$this->fechar();
		}
		
	}

	function ativarServicoAjuste($dados_ajuste, $data_inicio = null, $comentario = null, $usuario_id = null) {
		
		// Trata Dados dd/mm/yyyy (hh:ii)
		$data_inicio = !$data_inicio ? date("Y-m-d H:i:s") : substr($data_inicio, 6, 4)."-".substr($data_inicio, 3, 2)."-".substr($data_inicio, 0, 2)." ".substr($data_inicio, 12, 2).":".substr($data_inicio, 15, 2).":00";
		$ajuste_ativo = $this->buscaAjusteAtivo($dados_ajuste["cliente_id"], $data_inicio, $dados_ajuste["duracao"]);
		
		if ($ajuste_ativo && strtotime($data_inicio) >= strtotime($ajuste_ativo["inicio"]) && strtotime($data_inicio) < strtotime($ajuste_ativo["termino"])) {
			$data_inicio = $ajuste_ativo["termino"];
		}
		
		$comentario = $comentario ? "<br><b>Comentário:</b> ".trim($comentario) : "";
		
		// Ativa Ajuste
		$query = "UPDATE tab_servicos_ajustes SET sas_id = 5 WHERE saj_id = {$dados_ajuste["id"]}";
		$this->bancodedados->query($query);
		
		// Aplica Ajuste
		$this->aplicarAjuste($dados_ajuste["id"]);
		
		// Atualiza Início/Término
		$query = "UPDATE tab_servicos_ajustes SET saj_inicio = '$data_inicio', saj_termino = IF(saj_duracao > 0, DATE_ADD('$data_inicio', INTERVAL saj_duracao DAY), saj_termino) WHERE saj_id = {$dados_ajuste["id"]}";
		$this->bancodedados->query($query);
		
		// Registra Comentário
		$this->registraComentario($dados_ajuste["id"], "<b>Ação</b>: Ativado{$comentario}", $usuario_id);
		
		// Gera Ajuste Cobrança
		if ($dados_ajuste["valor"]) {
			
			$competencia = date("Y-m-01");
			
			$query = "
				INSERT INTO
					tab_ajustes_financeiros (ajf_datahora, cli_id, cor_id, afc_id, aft_id, ajf_valor, ajf_valor_tipo, ajf_competencia_inicio, ajf_competencia_termino, afs_id)
				VALUES (NOW(), {$dados_ajuste["cliente_id"]}, NULL, 26, 1, {$dados_ajuste["valor"]}, 1, '$competencia', '$competencia', 5)";
			$this->bancodedados->query($query);
			
			$ajuste_id = $this->bancodedados->ultimoIdInserido();
			
			$this->registraComentarioAjusteFinanceiro($ajuste_id, "<b>{$dados_ajuste["categoria"]}</b>: Entre ".date("d/m/Y (H:i)", strtotime($data_inicio))." e ".date("d/m/Y (H:i)", strtotime("$data_inicio + {$dados_ajuste["duracao"]} days")));
		
			$this->bancodedados->query("UPDATE tab_servicos_ajustes SET ajf_id = $ajuste_id WHERE saj_id = {$dados_ajuste["id"]}");
		
		}
	
	}

	function buscaAjusteAtivo($cliente_id, $inicio, $duracao) {
		
		$termino = date("Y-m-d H:i:s", strtotime("$inicio + $duracao days"));
		
		$query = "
			SELECT
				saj_id AS id,
				saj_inicio AS inicio,
				saj_termino AS termino
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				saj_inicio < '$termino' AND
				saj_termino > '$inicio' AND
				saj_valor > 0 AND
				sas_id = 5
			ORDER BY
				saj_termino DESC";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
	
	}

	function registraComentarioAjusteFinanceiro($ajuste_id, $conteudo, $responsavel_id = null) {
		
		$ajuste_id = preg_replace("/[^0-9]/", "", $ajuste_id);
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		if ($ajuste_id) {
			$query = "INSERT INTO tab_ajustes_financeiros_comentarios (afm_datahora, ajf_id, afm_conteudo, usu_id) VALUES (NOW(), $ajuste_id, '$conteudo', $responsavel_id)";
			$this->bancodedados->query($query);
		}
	
	}

	################
	### Cancelar ###
	################

	function cancelar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.cancelar.inc.php");
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=cancelar&i={$_GET["i"]}", $this->conteudo);
				
			} else {
				
				$query = "UPDATE tab_servicos_ajustes SET sas_id = IF(sas_id = 1, 4, 6), saj_termino = IF(sas_id = 1, saj_termino, NOW()) WHERE saj_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Cancelado", $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
		
		} else {
			$this->fechar();
		}
	
	}

	#################
	### Finalizar ###
	#################

	function finalizar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.finalizar.inc.php");
				$this->conteudo = str_replace("[termino]", date("d/m/Y (H:i)"), $this->conteudo);
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$_GET["i"]}", $this->conteudo);
			
			} else {
				
				// Trata Dados
				$_POST["termino"] = !$_POST["termino"] ? date("Y-m-d H:i:s") : substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)." ".substr($_POST["termino"], 12, 2).":".substr($_POST["termino"], 15, 2).":00";
				$_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
				
				// Finaliza Ajuste
				$this->removeAjuste($_GET["i"], $_POST["termino"]);
				
				// Registra Comentário
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Finalizado".$_POST["comentario"], $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
		
		} else {
			$this->fechar();
		}
	
	}

	function removeAjuste($ajuste_id, $data_termino = null) {
		
		// Finaliza Ajustes Filhos
		$ajuste_filho = $this->buscaAjusteFilho($ajuste_id);
		if ($ajuste_filho) {
			$this->finalizaAjuste($ajuste_filho, $data_termino);
		}
		
		// Finaliza Ajuste
		$this->finalizaAjuste($ajuste_id, $data_termino);
				
		// Redefine Iptv
		$iptvs = $this->buscaIptvsAjustados($ajuste_id);
		foreach ($iptvs as $iptv) {
			$this->redefineIptv($iptv["id"]);
			if ($ajuste_filho) {
				$this->registraAjusteIptv($iptv["id"], $ajuste_filho);
			}
			$this->registraAjusteIptv($iptv["id"], $ajuste_id);
		}

		// Redefine Autenticações
		$autenticacoes = $this->buscaAutenticacoesAjustados($ajuste_id);
		foreach ($autenticacoes as $autenticacao) {
			$this->redefineAutenticacao($autenticacao["id"]);
			if ($ajuste_filho) {
				$this->registraAjusteAutenticacao($autenticacao["id"], $ajuste_filho);
			}
			$this->registraAjusteAutenticacao($autenticacao["id"], $ajuste_id);
		}
		
		// Redefine Pontos
		$pontos = $this->buscaPontosAjustados($ajuste_id);
		foreach ($pontos as $ponto) {
			$this->redefinePonto($ponto["id"]);
			if ($ajuste_filho) {
				$this->registraAjustePonto($ponto["id"], $ajuste_filho);
			}
			$this->registraAjustePonto($ponto["id"], $ajuste_id);
			$this->atualizaServidorServicos($ponto["interface_id"]);
		}
		
		// Redefine E-mails
		$emails = $this->buscaEmailsAjustados($ajuste_id);
		foreach ($emails as $email) {
			$this->redefineEmail($email["id"]);
			if ($ajuste_filho) {
				$this->registraAjusteEmail($email["id"], $ajuste_filho);
			}
			$this->registraAjusteEmail($email["id"], $ajuste_id);
		}
		
	}

	function buscaAjusteFilho($ajuste_id) {
		
		$query = "
			SELECT
				tab_servicos_ajustes.saj_id
			FROM
				tab_servicos_ajustes
			WHERE
				tab_servicos_ajustes.saj_pai = $ajuste_id AND
				tab_servicos_ajustes.sas_id IN (5, 9)";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
	
	}

	function finalizaAjuste($ajuste_id, $data_termino = null) {
		
		$query = "
			UPDATE
				tab_servicos_ajustes
			SET
				saj_termino = ".($data_termino ? "'$data_termino'" : "NOW()").",
				sas_id = 8
			WHERE
				saj_id = $ajuste_id";
		$this->bancodedados->query($query);
	
	}

	function buscaIptvsAjustados($ajuste_id) {
		
		$pontos = array();
			
		$query = "
			SELECT
				tab_clientes_iptv.cip_id AS id
			FROM
				rel_saj_cip_contem
				INNER JOIN tab_clientes_iptv ON (rel_saj_cip_contem.cip_id = tab_clientes_iptv.cip_id)
			WHERE
				rel_saj_cip_contem.saj_id = $ajuste_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$pontos[] = $dados;
		}
		
		return $pontos;
	
	}

	function buscaAutenticacoesAjustados($ajuste_id) {
		
		$pontos = array();
			
		$query = "
			SELECT
				tab_clientes_autenticacoes.cau_id AS id
			FROM
				rel_saj_cau_contem
				INNER JOIN tab_clientes_autenticacoes ON (rel_saj_cau_contem.cau_id = tab_clientes_autenticacoes.cau_id)
			WHERE
				rel_saj_cau_contem.saj_id = $ajuste_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$pontos[] = $dados;
		}
		
		return $pontos;
	
	}

	function buscaPontosAjustados($ajuste_id) {
		
		$pontos = array();
		
		$query = "
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.sei_id AS interface_id
			FROM
				rel_saj_clp_contem
				INNER JOIN tab_clientes_pontos ON (rel_saj_clp_contem.clp_id = tab_clientes_pontos.clp_id)
			WHERE
				rel_saj_clp_contem.saj_id = $ajuste_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$pontos[] = $dados;
		}
		
		return $pontos;
		
	}

	function buscaEmailsAjustados($ajuste_id) {
		
		$query = "
			SELECT
				tab_clientes_emails.cle_id AS id
			FROM
				rel_saj_cle_contem
				INNER JOIN tab_clientes_emails ON (rel_saj_cle_contem.cle_id = tab_clientes_emails.cle_id)
			WHERE
				rel_saj_cle_contem.saj_id = $ajuste_id";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[] = $dados;
		}
		
		return $emails;
	
	}

	#######################
	### Redefine Ajuste ###
	#######################

	function redefineIptv($iptv_id) {
		
		$query = "
			UPDATE
				tab_clientes_iptv
			SET
				cis_id = 1,
				cip_publicado = 0
			WHERE
				cip_id = $iptv_id AND
				cis_id = 3";
		$this->bancodedados->query($query);
		
						
		$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$publicacao->publicar($iptv_id);
	
	}

	function redefineAutenticacao($autenticacao_id) {
		
		$query = "
			UPDATE
				tab_clientes_autenticacoes
			SET
				cau_ajustado = 0,
				cau_ajuste_download = NULL,
				cau_ajuste_upload = NULL,
				cau_publicado = 0
			WHERE
				cau_id = $autenticacao_id";
		$this->bancodedados->query($query);
	
	}

	function redefinePonto($ponto_id) {
		
		$query = "
			UPDATE
				tab_clientes_pontos
				LEFT OUTER JOIN tab_servidores_servicos ON (tab_clientes_pontos.sei_id = tab_servidores_servicos.sei_id AND tab_servidores_servicos.sst_id IN (1, 2, 3, 8))
			SET
				tab_clientes_pontos.clp_ajustado = 0,
				tab_clientes_pontos.clp_parametros_ajuste = NULL,
				tab_clientes_pontos.clp_publicado = 0,
				tab_servidores_servicos.ses_data_alteracao = NOW()
			WHERE
				tab_clientes_pontos.clp_id = $ponto_id";
		$this->bancodedados->query($query);
	
	}

	function redefineEmail($email_id) {
		
		$query = "
			UPDATE
				tab_clientes_emails
			SET
				cle_bloqueado = 0,
				cle_publicado = 0
			WHERE
				cle_id = $email_id";
		$this->bancodedados->query($query);
	
	}

	#######################
	### Registra Ajuste ###
	#######################

	function registraAjusteIptv($iptv_id, $ajuste_id, $inicio = false) {
		
		$query = "
			UPDATE
				tab_clientes_iptv_ajustes
			SET
				cia_termino = NOW()
			WHERE
				cip_id = $iptv_id AND
				saj_id = $ajuste_id AND
				cia_termino IS NULL";
		$this->bancodedados->query($query);
		
		if ($inicio) {
			$query = "
				INSERT INTO
					tab_clientes_iptv_ajustes (cip_id, saj_id, cia_inicio)
				VALUES ($iptv_id, $ajuste_id, NOW())";
			$this->bancodedados->query($query);
		}
		
	}

	function registraAjusteAutenticacao($autenticacao_id, $ajuste_id, $inicio = false) {
		
		$query = "
			UPDATE
				tab_clientes_autenticacoes_ajustes
			SET
				caa_termino = NOW()
			WHERE
				cau_id = $autenticacao_id AND
				saj_id = $ajuste_id AND
				caa_termino IS NULL";
			$this->bancodedados->query($query);
		
		if ($inicio) {
			$query = "
				INSERT INTO
					tab_clientes_autenticacoes_ajustes (cau_id, saj_id, caa_inicio)
				VALUES ($autenticacao_id, $ajuste_id, NOW())";
			$this->bancodedados->query($query);
		}
	
	}

	function registraAjustePonto($ponto_id, $ajuste_id, $inicio = false) {
		
		$query = "
			UPDATE
				tab_clientes_pontos_ajustes
			SET
				cpa_termino = NOW()
			WHERE
				clp_id = $ponto_id AND
				saj_id = $ajuste_id AND
				cpa_termino IS NULL";
		$this->bancodedados->query($query);
		
		if ($inicio) {
			$query = "
				INSERT INTO
					tab_clientes_pontos_ajustes (clp_id, saj_id, cpa_inicio)
				VALUES ($ponto_id, $ajuste_id, NOW())";
			$this->bancodedados->query($query);
		}
	
	}

	function registraAjusteEmail($email_id, $ajuste_id, $inicio = false) {
		
		$query = "
			UPDATE
				tab_clientes_emails_ajustes
			SET
				cea_termino = NOW()
			WHERE
				cle_id = $email_id AND
				saj_id = $ajuste_id AND
				cea_termino IS NULL";
		$this->bancodedados->query($query);
		
		if ($inicio) {
			$query = "
				INSERT INTO
					tab_clientes_emails_ajustes (cle_id, saj_id, cea_inicio)
				VALUES ($email_id, $ajuste_id, NOW())";
			$this->bancodedados->query($query);
		}
		
	}

	function atualizaServidorServicos($interface_id) {
		
		$query = "
			UPDATE
				tab_servidores_servicos
			SET
				ses_data_alteracao = NOW()
			WHERE
				sst_id IN (1, 2, 3, 8) AND
				sei_id = $interface_id";
		$this->bancodedados->query($query);
		
	}

	############################
	### Suspensão Temporária ###
	############################

	function suspensao_temporaria() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$this->conteudo .= $this->carregaArquivo("servicos_ajustes.suspensao_temporaria.inc.php");
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=suspensao_temporaria&i={$_GET["i"]}", $this->conteudo);
			
			} else {

				$this->insereSuspensaoTemporaria();

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

				// Visualiza ajuste
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}
		
		} else {
			$this->fechar();
		}
	
	}

	
	function desbloqueioDaSuspensaoTemporaria($id_suspensao = null) {

		$this->buscaDadosItem($id_suspensao);
		$this->insereSuspensaoTemporaria();
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade(null, $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

	}

	function insereSuspensaoTemporaria() {

		// Insere Ajuste
		if (in_array(date("w", strtotime("+ 2 days")), array(0, 6)) || (date("w", strtotime("+ 2 days")) == 1 && date("H") <= 12) || (date("w", strtotime("+ 2 days")) == 5 && date("H") >= 18)) {
			$termino = date("Y-m-d", strtotime("Monday"))." 12:00";
		} else {
			$termino = date("Y-m-d H:i", strtotime("+ 2 days"));
		}
		
		$query = "
			INSERT INTO tab_servicos_ajustes
				(sac_id, saj_pai, cli_id, saj_inicio, saj_termino, sas_id)
			VALUES
				(2, {$this->dados_item["id"]}, {$this->dados_item["cliente_id"]}, NOW(), '$termino', 5)";
		$this->bancodedados->query($query);
		
		$id = $this->bancodedados->ultimoIdInserido();
		
		// Registra Regras
		$dados_categoria = $this->buscaDadosCategoria(2);
		$this->registraRegras($id, $dados_categoria["regras"]);
		
		// Insere relação bloqueio/assinatura
		$query = "SELECT ass_id AS id FROM rel_saj_ass_contem WHERE saj_id = {$this->dados_item["id"]}";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$query_insert = "INSERT INTO rel_saj_ass_contem (saj_id, ass_id) VALUES ($id, {$dados["id"]})";
			$this->bancodedados->query($query_insert);
		}
		
		// Insere relação bloqueio/iptv
		$query = "SELECT cip_id AS id FROM rel_saj_cip_contem WHERE saj_id = {$this->dados_item["id"]}";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$query_insert = "INSERT INTO rel_saj_cip_contem (saj_id, cip_id) VALUES ($id, {$dados["id"]})";
			$this->bancodedados->query($query_insert);
		}
		
		// Insere relação bloqueio/autenticacoes
		$query = "SELECT cau_id AS id FROM rel_saj_cau_contem WHERE saj_id = {$this->dados_item["id"]}";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$query_insert = "INSERT INTO rel_saj_cau_contem (saj_id, cau_id) VALUES ($id, {$dados["id"]})";
			$this->bancodedados->query($query_insert);
		}
		
		// Insere relação bloqueio/pontos
		$query = "SELECT clp_id AS id FROM rel_saj_clp_contem WHERE saj_id = {$this->dados_item["id"]}";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$query_insert = "INSERT INTO rel_saj_clp_contem (saj_id, clp_id) VALUES ($id, {$dados["id"]})";
			$this->bancodedados->query($query_insert);
		}
		
		// Insere relação bloqueio/emails
		$query = "SELECT cle_id AS id FROM rel_saj_cle_contem WHERE saj_id = {$this->dados_item["id"]}";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$query_insert = "INSERT INTO rel_saj_cle_contem (saj_id, cle_id) VALUES ($id, {$dados["id"]})";
			$this->bancodedados->query($query_insert);
		}
		
		// Aplica Suspensão
		$this->aplicarAjuste($this->dados_item["id"]);
		
		// Registra comentário
		$this->registraComentario($this->dados_item["id"], "<b>Ação</b>: Suspensão Temporária", $_SESSION["usuario"]);

	}

	###################
	### Comentários ###
	###################

	function registraComentario($ajuste_id, $conteudo, $responsavel_id = 'NULL') {
		
		$ajuste_id = preg_replace("/[^0-9]/", "", $ajuste_id);
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		if ($ajuste_id) {
			$query = "INSERT INTO tab_servicos_ajustes_comentarios (saj_id, sam_datahora, sam_conteudo, usu_id) VALUES ($ajuste_id, NOW(), '$conteudo', $responsavel_id)";
			$this->bancodedados->query($query);
		}
	
	}

	function buscaComentarios($id) {
		
		$comentarios = array();		
		
		$query = "
			SELECT
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_servicos_ajustes_comentarios.sam_datahora, '%d/%m/%Y (%H:%i)') AS data,
				tab_servicos_ajustes_comentarios.sam_conteudo AS conteudo
			FROM
				tab_servicos_ajustes_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_servicos_ajustes_comentarios.usu_id)
			WHERE
				tab_servicos_ajustes_comentarios.saj_id = $id
			ORDER BY
				tab_servicos_ajustes_comentarios.sam_datahora ASC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["responsavel"] = (!$dados["responsavel"]) ? "Sistema" : $dados["responsavel"];
			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data"]}</b><br>";
			$dados["detalhamento"] .= substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$comentarios[] = $dados;
		}
		
		$cabecalho = array("detalhamento" => "Detalhamento");
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
		
		return $listagem->conteudo;
	
	}

	##############
	### Regras ###
	##############

	function buscaRegrasAdicionar() {
		
		$adicionar = array();
		
		$adicionar["servico"] = '<select name="servico" id="servico" class="inputBrancoAzulEscuro11" style="width: 179px" onchange="mudaServico(this.value)">'.$this->geraSelect($this->buscaServicos(), null, true).'</select>';
		$adicionar["ajuste_tipo"] = '<select name="ajuste_tipo" id="ajuste_tipo" class="inputBrancoAzulEscuro11" style="width: 90px" onchange="mudaAjusteTipo(this.value)"'.($this->permissoes["servicos_ajustes"]["adicionar_plus"] ? "" : " disabled").'>'.$this->geraSelect($this->ajustes_tipos, null, true).'</select>';
		$adicionar["ajuste_valor"] = '<input type="text" name="ajuste_valor" id="ajuste_valor" class="inputBrancoAzulEscuro11" style="width: 40px" onKeyPress="return formataAjusteValor(this, event)" onblur="return formataAjusteValor(this, event)" disabled>';
		$adicionar["ajuste_unidade"] = '<select name="ajuste_unidade" id="ajuste_unidade" class="inputBrancoAzulEscuro11" style="width: 55px"'.($_SESSION["usuario"] == 39 ? '' : ' disabled').'></select>';
		$adicionar["dia_semana"] = '<select name="dia_semana" id="dia_semana" class="inputBrancoAzulEscuro11" style="width: 95px">'.$this->geraSelect($this->dias_semana, "todos", true).'</select>';
		$adicionar["hora_inicio"] = '<select name="hora_inicio" id="hora_inicio" class="inputBrancoAzulEscuro11" style="width: 55px">'.$this->geraSelect($this->buscaHorarios(), "00:00", true).'</select>';
		$adicionar["hora_termino"] = '<select name="hora_termino" id="hora_termino" class="inputBrancoAzulEscuro11" style="width: 55px">'.$this->geraSelect($this->buscaHorarios(), "24:00", true).'</select>';
		$adicionar["botoes"] = '<input type="button" name="adicionar_regra" id="adicionar_regra" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarRegra()">';
		
		return $adicionar;
		
	}

	function geraRegrasListagem($dados = null, $editavel = true) {
		
		if ($dados) {
			$servicos = $this->buscaServicos();
			for ($i = 0; $i < count($dados); $i++) {
				if ($editavel) {
					$dados[$i]["link"] = "javascript:selecionarRegra(this, event, ".($i + 1).")";
					$dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerRegra('.($i + 1).')">';
				}
				if ($dados[$i]["ajuste_tipo"] == 2) {
					$dados[$i]["ajuste_unidade"] = $this->buscaAjusteUnidade($dados[$i]["servico"], $dados[$i]["ajuste_unidade"]);
					$dados[$i]["ajuste_valor"] .= (!$editavel) ? " ".$dados[$i]["ajuste_unidade"] : "";
				} else {
					$dados[$i]["ajuste_valor"] .= "%";
				}
				$dados[$i]["servico"] = $servicos[$dados[$i]["servico"]];
				$dados[$i]["ajuste_tipo"] = $this->ajustes_tipos[$dados[$i]["ajuste_tipo"]];
				$dados[$i]["dia_semana"] = $this->dias_semana[$dados[$i]["dia_semana"]];
			}
		} else {
			$dados = array(array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>"));
		}
		
		if ($editavel) {
			$dados = funcoes::mescla_arrays(array($this->buscaRegrasAdicionar()), $dados);
			$cabecalho = array("servico" => "Serviço", "ajuste_tipo" => "Tipo", "ajuste_valor" => "Valor", "ajuste_unidade" => "Unidade", "dia_semana" => "Dia da Semana", "hora_inicio" => "Início", "hora_termino" => "Término", "botoes" => "");
			$larguras = null;
		} else {
			$cabecalho = array("servico" => "Serviço", "ajuste_valor" => "Ajuste", "dia_semana" => "Dia da Semana", "hora_inicio" => "Início", "hora_termino" => "Término");
			$larguras = array(0, 120, 120, 70, 70);
		}
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras);
		
		return $listagem->conteudo;
		
	}

	function buscaAjusteUnidade($servico_id, $unidade_id) {
		
		$multiplicadores = array(0 => "", 1 => "K", 2 => "M", 3 => "G");
		$unidades = array(1 => "bps", 2 => "B", 4 => "B", 5 => "B / mês", 7 => "B", 8 => "B / mês", 9 => "bps");
		
		return $multiplicadores[$unidade_id].$unidades[$servico_id];
		
	}

	##############################
	### Busca Clientes Ajustes ###
	##############################

	function buscaBotaoAdicionar($cliente_id) {
		
		if (!$this->permissoes["servicos_ajustes"]["adicionar"]) return "";
		if (!$this->possuiContratos($cliente_id, "=", "11", "", "")) return "";
		
		return '<input type="button" class="buttonAzulEscuroBranco11" name="ajustes_adicionar" value="Adicionar" onClick="adicionarAjusteCliente('.$cliente_id.')">';
	
	}

	function buscaBotaoAdicionarBloqueioTemporario($cliente_id)  {
		
		if (!$this->permissoes["servicos_ajustes"]["adicionar_bloqueio_temporario"]) return "";
		if (!$this->possuiContratos($cliente_id, "=", "11", "", "")) return "";
		
		return '<input type="button" class="buttonAzulEscuroBranco11" name="ajustes_adicionar_bloqueio_temporario" value="Adicionar Bloqueio Temporario" onClick="adicionarAjusteTemporarioCliente('.$cliente_id.')">';
	
	}

	function possuiContratos($cliente, $tipo_filtro, $tipo_id, $status_filtro, $status_id) {
		
		if ($tipo_filtro && $tipo_id) $tipo = " AND crt_id $tipo_filtro ($tipo_id)";
		if ($status_filtro && $status_id) $status = " AND crs_id $status_filtro ($status_id)";
		
		$query = "SELECT COUNT(cor_id) FROM tab_contratos WHERE cli_id = '$cliente' $tipo $status";
		
		return $this->bancodedados->resultado($this->bancodedados->query($query)) > 0;
	
	}

	function buscaClientesAjustes($cliente_id) {
		
		$ajustes = array();
		
		$query = "
			SELECT
				tab_servicos_ajustes.saj_id AS id,
				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
				tab_servicos_ajustes.saj_valor AS valor,
				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_servicos_ajustes_status.sas_nome AS status
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
			WHERE
				tab_servicos_ajustes.cli_id = $cliente_id AND
				tab_servicos_ajustes.saj_pai IS NULL";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["codigo"] = "<div align=\"center\">".sprintf("%06s", $dados["id"])."</div>";
			$dados["categoria"] = "<div align=\"left\">".$dados["categoria"]."</div>";
			$dados["valor"] = $this->formataDinheiro($dados["valor"]);
			if (!$dados["inicio"]) $dados["inicio"] = "-";
			if (!$dados["termino"]) $dados["termino"] = "-";
			if ($this->permissoes["servicos_ajustes"]["ver"]) $dados["link"] = "javascript:verAjusteCliente({$dados["id"]})";
			
			$ajustes[] = $dados;
			
		}
		
		return $ajustes;
		
	}

	function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
		
		if (!$valor && !$exibir_nulo) return false;
		$valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
		$valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
		if ($moeda) $valor_formatado = "$moeda $valor_formatado";
		if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
		
		return $valor_formatado;
	
	}

	function geraListagemClientesAjustes($dados) {
		
		$cabecalho = array ("categoria" => "Categoria", "valor" => "Valor", "inicio" => "Início", "termino" => "Término", "status" => "Status");
		$larguras = array("", "95", "110", "110", "130");
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, $larguras);
		
		return $listagem->conteudo;
	
	}

	######################
	### Aplicar Ajuste ###
	######################

	function aplicarAjuste($ajuste_id) {
		
		//$ajuste_bloqueio_ativo_id = $this->buscaAjusteBloqueioAtivo($ajuste_id);
		
		//if ((!$ajuste_bloqueio_ativo_id || $ajuste_bloqueio_ativo_id == $ajuste_id) && $this->ehAjusteAtivo($ajuste_id)) {

		if ($this->ehAjusteAtivo($ajuste_id)) {
			$this->aplicaAjusteIptvs($ajuste_id);
			$this->aplicaAjusteAutenticacoes($ajuste_id);
			$this->aplicaAjustePontos($ajuste_id);
			$this->aplicaAjusteEmails($ajuste_id);
		}
		
	}

	function buscaAjusteBloqueioAtivo($ajuste_id) {
		
		$query = "
			SELECT
				saj_id
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = (SELECT cli_id FROM tab_servicos_ajustes WHERE saj_id = $ajuste_id) AND
				sas_id IN (5, 9) AND
				sac_id = 1
			LIMIT
				0, 1";			
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
		
	}

	function ehAjusteAtivo($ajuste_id) {
		
		$query = "
			SELECT
				sas_id
			FROM
				tab_servicos_ajustes
			WHERE
				saj_id = $ajuste_id AND
				(saj_inicio IS NULL OR saj_inicio <= NOW()) AND
				saj_pai IS NULL";
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query) == 5;
	
	}

	function buscaRegraAjuste($ajuste_id, $tipo_id) {
		
		$query = "
			SELECT
				sar_ajuste
			FROM
				tab_servicos_ajustes_regras
			WHERE
				saj_id = $ajuste_id AND
				(sat_id IS NULL OR sat_id = $tipo_id) AND
				(sar_dia_semana IS NULL OR sar_dia_semana = WEEKDAY(NOW()) + 1) AND
				IF(sar_hora_inicio > sar_hora_termino,
				(sar_hora_inicio IS NULL OR sar_hora_inicio <= CURTIME()) OR
				(sar_hora_termino IS NULL OR sar_hora_termino >= CURTIME()),
				(sar_hora_inicio IS NULL OR sar_hora_inicio <= CURTIME()) AND
				(sar_hora_termino IS NULL OR sar_hora_termino >= CURTIME()))
			ORDER BY
				sar_id ASC
			LIMIT 0, 1";
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
	
	}

	##############################
	### Aplica Ajuste Serviços ###
	##############################

	function aplicaAjusteIptvs($ajuste_id, $ajuste_pai = false) {
		
		$ajuste_filho = $this->buscaAjusteFilhoAtivo($ajuste_id);
		
		if ($ajuste_filho) {
			$ajuste_id = $ajuste_filho;
		}
		
		$regra_ajuste = $this->buscaRegraAjuste($ajuste_id, 10);
		
		$servicos = $this->buscaIptvsComAjuste($ajuste_id, true);
		
		foreach ($servicos as $servico) {
			
			if ($regra_ajuste) {
				
				$atualizado = false;
				
				if ($regra_ajuste == 0 && $servico["status_id"] == 1) {
					
					$query = "
						UPDATE
							tab_clientes_iptv
						SET
							cis_id = 3,
							cip_publicado = 0
						WHERE
							cip_id = {$servico["id"]}";
					$this->bancodedados->query($query);
					
					$atualizado = $this->bancodedados->linhasAfetadas();
					
					if (!$ajuste_pai) {
						$this->registraAjusteIptv($servico["id"], $ajuste_id, true);
					}
				
				} elseif ($regra_ajuste != 0 && $servico["status_id"] == 3) {
					
					$query = "
						UPDATE
							tab_clientes_iptv
						SET
							cis_id = 1,
							cip_publicado = 0
						WHERE
							cip_id = {$servico["id"]}";
					$this->bancodedados->query($query);
					
					$atualizado = $this->bancodedados->linhasAfetadas();

					if (!$ajuste_pai) {
						$this->registraAjusteIptv($servico["id"], $ajuste_id, 1);
					}
				
				}
				
				if ($atualizado) {
					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
					$publicacao->publicar($servico["id"]);
				}
			
			} elseif ($email["bloqueado"]) {
				$this->finalizaAjusteIptvs($ajuste_id);
			}
			
		}
		
	}

	function aplicaAjusteAutenticacoes($ajuste_id, $ajuste_pai = false) {
		
		$ajuste_filho = $this->buscaAjusteFilhoAtivo($ajuste_id);
		
		if ($ajuste_filho) {
			$ajuste_id = $ajuste_filho;
		}
		
		$regra_ajuste_download = $this->buscaRegraAjuste($ajuste_id, 9);
		$regra_ajuste_upload = $this->buscaRegraAjuste($ajuste_id, 1);
		
		$servicos = $this->buscaAutenticacoesComAjuste($ajuste_id, true);
		
		foreach ($servicos as $servico) {
			
			if (isset($regra_ajuste_download) || isset($regra_ajuste_upload)) {
				
				$regra_ajuste_download = $regra_ajuste_download === null ? 1 : $regra_ajuste_download;
				$regra_ajuste_upload = $regra_ajuste_upload === null ? 1 : $regra_ajuste_upload;
				
				$regra = array();
				$regra["download"] = intval($regra_ajuste_download ? ($regra_ajuste_download <= 1 ? round($servico["download"] * $regra_ajuste_download) : $regra_ajuste_download) : 0, 10);
				$regra["upload"] = intval($regra_ajuste_upload ? ($regra_ajuste_upload <= 1 ? round($servico["upload"] * $regra_ajuste_upload) : $regra_ajuste_upload) : 0, 10);
				
				if (($regra["download"] != $servico["ajuste_download"]) || ($regra["upload"] != $servico["ajuste_upload"]) || !$servico["ajustado"]) {
				
					$query = "
						UPDATE
							tab_clientes_autenticacoes
						SET
							tab_clientes_autenticacoes.cau_ajustado = 1,
							tab_clientes_autenticacoes.cau_ajuste_download = {$regra["download"]},
							tab_clientes_autenticacoes.cau_ajuste_upload = {$regra["upload"]},
							tab_clientes_autenticacoes.cau_publicado = 0
						WHERE
							tab_clientes_autenticacoes.cau_id = {$servico["id"]}";
					$this->bancodedados->query($query);
					
					if (!$ajuste_pai) {
						$this->registraAjusteAutenticacao($servico["id"], $ajuste_id, true);
					}
					
				}
			
			} elseif ($servico["ajustado"]) {
				$this->finalizaAjusteAutenticacoes($ajuste_id);
			}
			
		}
		
	}

	function aplicaAjustePontos($ajuste_id, $ajuste_pai = false) {
		
		$ajuste_filho = $this->buscaAjusteFilhoAtivo($ajuste_id);
		
		if ($ajuste_filho) {
			$ajuste_id = $ajuste_filho;
		}
		
		$regra_ajuste_download = $this->buscaRegraAjuste($ajuste_id, 9);
		$regra_ajuste_upload = $this->buscaRegraAjuste($ajuste_id, 1);
		
		$servicos = $this->buscaPontosComAjuste($ajuste_id, true);
		
		foreach ($servicos as $servico) {
			
		if (isset($regra_ajuste_download) || isset($regra_ajuste_upload)) {
			
			$regra_ajuste_download = $regra_ajuste_download === null ? 1 : $regra_ajuste_download;
			$regra_ajuste_upload = $regra_ajuste_upload === null ? 1 : $regra_ajuste_upload;

			$regra = array();
			$regra["download"] = intval($regra_ajuste_download ? ($regra_ajuste_download <= 1 ? round($servico["download"] * $regra_ajuste_download) : $regra_ajuste_download) : 0, 10);
			$regra["upload"] = intval($regra_ajuste_upload ? ($regra_ajuste_upload <= 1 ? round($servico["upload"] * $regra_ajuste_upload) : $regra_ajuste_upload) : 0, 10);
			$regra = serialize($regra);
			
			if ($regra != $servico["parametros_ajuste"]) {
				
				$query = "
					UPDATE
						tab_clientes_pontos
						LEFT OUTER JOIN tab_servidores_servicos ON (tab_clientes_pontos.sei_id = tab_servidores_servicos.sei_id AND tab_servidores_servicos.sst_id IN (1, 2, 3, 8))
					SET
						tab_clientes_pontos.clp_ajustado = 1,
						tab_clientes_pontos.clp_parametros_ajuste = '$regra',
						tab_clientes_pontos.clp_publicado = 0,
						tab_servidores_servicos.ses_data_alteracao = NOW()
					WHERE
						tab_clientes_pontos.clp_id = {$servico["id"]}";
				$this->bancodedados->query($query);
				
				if (!$ajuste_pai) {
					$this->registraAjustePonto($servico["id"], $ajuste_id, true);
				}
				
			}
			
			} elseif ($servico["ajustado"]) {
				$this->finalizaAjustePontos($ajuste_id);
			}
		
		}
		
	}

	function aplicaAjusteEmails($ajuste_id, $ajuste_pai = false) {
		
		$ajuste_filho = $this->buscaAjusteFilhoAtivo($ajuste_id);
		
		if ($ajuste_filho) {
			$ajuste_id = $ajuste_filho;
		}
		
		$regra_ajuste = $this->buscaRegraAjuste($ajuste_id, 2);
		
		$emails = $this->buscaEmailsComAjuste($ajuste_id, true);
		
		foreach ($emails as $email) {
			
			if (isset($regra_ajuste)) {
				
				if ($regra_ajuste == 0 && !$email["bloqueado"]) {
					
					$query = "
						UPDATE
							tab_clientes_emails
						SET
							cle_bloqueado = 1,
							cle_publicado = 0
						WHERE
							cle_id = {$email["id"]}";
					$this->bancodedados->query($query);
					
					if (!$ajuste_pai) {
						$this->registraAjusteEmail($email["id"], $ajuste_id, true);
					}
				
				} elseif ($regra_ajuste != 0 && $email["bloqueado"]) {
					
					$query = "
						UPDATE
							tab_clientes_emails
						SET
							cle_bloqueado = 0,
							cle_publicado = 0
						WHERE
							cle_id = {$email["id"]}";
					$this->bancodedados->query($query);
					
					if (!$ajuste_pai) {
						$this->registraAjusteEmail($email["id"], $ajuste_id, 1);
					}
				
				}
			
			} elseif ($email["bloqueado"]) {
				$this->finalizaAjusteEmails($ajuste_id);
			}
			
		}
		
	}

	################################
	### Finaliza Ajuste Serviços ###
	################################

	function finalizaAjusteIptvs($ajuste_id) {
		
		$iptvs = $this->buscaIptvsComAjuste($ajuste_id);
		foreach ($iptvs as $iptv_id) {
			$this->redefineIptv($iptv_id);
			$this->registraAjusteIptv($iptv_id, $ajuste_id);
		}
	
	}

	function finalizaAjusteAutenticacoes($ajuste_id) {
		
		$autenticacoes = $this->buscaAutenticacoesComAjuste($ajuste_id);
		foreach ($autenticacoes as $autenticacao_id) {
			$this->redefineAutenticacao($autenticacao_id);
			$this->registraAjusteAutenticacao($autenticacao_id, $ajuste_id);
		}
	
	}

	function finalizaAjustePontos($ajuste_id) {
		
		$pontos = $this->buscaPontosComAjuste($ajuste_id);
		foreach ($pontos as $ponto_id) {
			$this->redefinePonto($ponto_id);
			$this->registraAjustePonto($ponto_id, $ajuste_id);
		}
	
	}

	function finalizaAjusteEmails($ajuste_id) {
		
		$emails = $this->buscaEmailsComAjuste($ajuste_id);
		foreach ($emails as $email_id) {
			$this->redefineEmail($email_id);
			$this->registraAjusteEmail($email_id, $ajuste_id);
		}
	
	}

	######################
	### Expirar Ajuste ###
	######################

	function expirarAjuste($ajuste) {
		
		// Finaliza ajustes dos serviços
		$this->finalizaAjusteIptvs($ajuste["id"]);
		$this->finalizaAjusteAutenticacoes($ajuste["id"]);
		$this->finalizaAjustePontos($ajuste["id"]);
		$this->finalizaAjusteEmails($ajuste["id"]);
		
		// Altera status do ajuste
		$this->defineAjusteComoExpirado($ajuste["id"]);
		
		// Reaplica ajuste pai, se existir e ativo
		if ($ajuste["ajuste_pai"]) {
			$this->aplicaAjusteIptvs($ajuste["ajuste_pai"], true);
			$this->aplicaAjusteAutenticacoes($ajuste["ajuste_pai"], true);
			$this->aplicaAjustePontos($ajuste["ajuste_pai"], true);
			$this->aplicaAjusteEmails($ajuste["ajuste_pai"], true);
		}
		
	}

	function defineAjusteComoExpirado($ajuste_id) {
		
		// Expira ajuste
		$query = "UPDATE tab_servicos_ajustes SET sas_id = 7 WHERE saj_id = $ajuste_id";
		$this->bancodedados->query($query);
		
		// Registra comentário
		$this->registraComentario($ajuste_id, "<u>Ação</u>: Expirado");
		
		// Finaliza ajustes filhos
		$query = "UPDATE tab_servicos_ajustes SET sas_id = 8, saj_termino = NOW() WHERE saj_pai = $ajuste_id AND sas_id = 5";
		$this->bancodedados->query($query);
		
	}

	##############################
	### Inserir Ajuste Cliente ###
	##############################

	function insereAjusteCliente($cliente_id, $categoria_id, $comentario = null, $ativar = false, $data_ativacao = null) {
		
		$servico = $this->buscaServicoCliente($cliente_id);
		
		if ($servico) {
				
			$dados_categoria = $this->buscaDadosCategoria($categoria_id);
			$ajuste_id = $this->geraAjuste($cliente_id, $dados_categoria);
			
			$this->registraRegras($ajuste_id, $dados_categoria["regras"]);
			$this->vinculaAjusteServico($ajuste_id, $servico);
			
			if ($comentario) {
				$this->registraComentario($ajuste_id, $comentario);
			}
			
			if ($ativar) {
				$dados_ajuste = $this->buscaDadosAjuste($ajuste_id);
				$this->ativarServicoAjuste($dados_ajuste, $data_ativacao);
			}
			
			return $ajuste_id;
		
		}
	
	}

	function geraAjuste($cliente_id, $dados_categoria) {
		
		$duracao = $dados_categoria["duracao"] ? $dados_categoria["duracao"] : "NULL";
		
		if (in_array($dados_categoria["id"], array(23, 24, 25, 26, 27)) && (in_array($cliente_id, array(66435, 61861, 78536, 46053, 73029, 53021, 84697, 62613, 61835, 68639, 77558, 60666, 62518, 63410, 86174, 60892, 80427, 67640, 76981, 52065, 60677)) || $this->ehPrimeiroPlanoDia($cliente_id))) {
			$valor = 0;
		} else {
			$valor = $dados_categoria["valor"] ? $dados_categoria["valor"] : "NULL";
		}
		
		$query = "
			INSERT INTO
				tab_servicos_ajustes (cli_id, sac_id, saj_duracao, saj_valor, sas_id)
			VALUES
				($cliente_id, {$dados_categoria["id"]}, $duracao, $valor, 1)";
		$this->bancodedados->query($query);
		
		return $this->bancodedados->ultimoIdInserido();

	}

	function ehPrimeiroPlanoDia($cliente_id) {
		
		$query = "
			SELECT
				COUNT(*)
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sac_id IN (23, 24, 25, 26, 27)";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query) == 0;
	
	}

	function vinculaAjusteServico($ajuste_id, $servico) {
		
		if ($servico["ponto_id"]) {
			$query = $this->bancodedados->query("INSERT INTO rel_saj_clp_contem (saj_id, clp_id) VALUES ($ajuste_id, {$servico["ponto_id"]})");
		} elseif ($servico["autenticacao_id"]) {
			$query = $this->bancodedados->query("INSERT INTO rel_saj_cau_contem (saj_id, cau_id) VALUES ($ajuste_id, {$servico["autenticacao_id"]})");
		}
	
	}

	function buscaServicoCliente($cliente_id) {
		
		$query = "
			SELECT
				clp_id AS ponto_id
			FROM
				tab_clientes_pontos
			WHERE
				ass_id IN (SELECT ass_id FROM tab_assinaturas WHERE cli_id = $cliente_id AND ast_id IN (2, 5) AND ass_pai IS NULL) AND
				cps_id = 1
			LIMIT
				0, 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			return $dados;
		}
		
		$query = "
			SELECT
				cau_id AS autenticacao_id
			FROM
				tab_clientes_autenticacoes
			WHERE
				ass_id IN (SELECT ass_id FROM tab_assinaturas WHERE cli_id = $cliente_id AND ast_id IN (2, 5) AND ass_pai IS NULL) AND
				cas_id = 1
			LIMIT
				0, 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		return $dados;
	
	}

	function cancelarAjusteAgendado($ajuste_id, $comentario = null) {
		
		$dados_ajuste = $this->buscaDadosAjuste($ajuste_id);
		
		if ($dados_ajuste) {
			// Cancela Ajuste
			$query = "UPDATE tab_servicos_ajustes SET sas_id = 6, ajf_id = NULL WHERE saj_id = {$dados_ajuste["id"]}";
			$query = $this->bancodedados->query($query);
			// Remove Comentário Ajuste
			$query = "DELETE FROM tab_ajustes_financeiros_comentarios WHERE ajf_id = {$dados_ajuste["ajuste_financeiro_id"]}";
			$query = $this->bancodedados->query($query);
			// Remove Ajuste
			$query = "DELETE FROM tab_ajustes_financeiros WHERE ajf_id = {$dados_ajuste["ajuste_financeiro_id"]}";
			$query = $this->bancodedados->query($query);
			// Registra Comentário
			if ($comentario) {
				$this->registraComentario($dados_ajuste["id"], $comentario);
			}
			return true;
		}
		
		return false;

	}

	function verificaDataInicio($converted_date_time_start) {

		$today = strtotime(date('Y-m-d 00:00:00'));

		if( $converted_date_time_start->getTimestamp() < $today) {

			print "
				<script type='text/javascript'>
				alert(\"Erro -> A data de início não pode ser anterior a hoje!\");
				window.history.back();
				</script>";

			die();

		}

	}

	function verificaPeriodoDatas($converted_date_time_start, $converted_date_time_end) {

		if ($converted_date_time_end->getTimestamp() <= $converted_date_time_start->getTimestamp()) {

		print "
			<script type='text/javascript'>
			alert(\"Erro -> A data de início deve ser menor que a data de término!\");
			window.history.back();
			</script>";

		die();

		}

	}

	function verificaSobreposicaoDatas($converted_date_time_end, $converted_date_time_start, $cliente) {

		$query = "
			SELECT 
				COUNT(*)
			FROM
				`tab_servicos_ajustes`
			WHERE
				`cli_id` = " . $cliente . " AND
				`saj_inicio` <= '" . $converted_date_time_end->format("Y-m-d H:i:s") . "' AND
				`saj_termino` >= '" . $converted_date_time_start->format("Y-m-d H:i:s") . "' AND
				`sac_id` = 10 AND
				`sas_id` >= 5";

		$query = $this->bancodedados->query($query);

		if ($this->bancodedados->resultado($query) > 0) {

			print "
				<script type='text/javascript'>
				alert(\"Erro -> Já existe um bloqueio neste período!\");
				window.history.back();
				</script>";

			die();

		}

	}		

	function verificaLimiteDiasPorAno($startyear, $endyear, $intervaldays, $client, $maxdaysperyear) {

		for ($year = $startyear; $year <= $endyear; $year++) {

			$querysumdatas = "
				SELECT
					SUM(DATEDIFF(IF(`saj_termino`>'$year-12-31 23:59:59', '$year-12-31 23:59:59',`saj_termino`) , IF(`saj_inicio`<'$year-01-01 00:00:00', '$year-01-01 00:00:00',`saj_inicio`))) AS days
				FROM
					`tab_servicos_ajustes`
				WHERE
					`cli_id` = " . $client . " AND
					`saj_inicio` <= '$year-12-31 23:59:59' AND
					`saj_termino`>= '$year-01-01 00:00:00' AND
					`sac_id` = 10 AND
					`sas_id` >= 5";

			$query = $this->bancodedados->query($querysumdatas);
			
			$difdays = $this->bancodedados->resultado($query);

			if ($difdays + $intervaldays > $maxdaysperyear) {

				print "
					<script type='text/javascript'>
					alert(\"Erro -> O número máximo de dias de bloqueio para o ano de " . $year . " foi ultrapassado  em " . (($difdays + $intervaldays) - 120) . " dias!\");
					window.history.back();
					</script>";

				die();

			}

		}

	}

}

?>
