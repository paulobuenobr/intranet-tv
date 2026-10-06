<?

require_once ("_global/modulo.class.inc.php");

class ajustes_financeiros extends modulo {
	
	private $valor_tipos = array(
		1 => "Dinheiro",
		2 => "Porcentagem",
		3 => "Dias",
		4 => "Horas"
	);
	
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
	
	function buscaDadosCliente($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cli_id AS id,
				cli_nome AS nome,
				cli_cpf_cnpj AS cpf_cnpj,
				cli_nascimento AS nascimento,
				cof_id AS cobranca_formato_id,
				cli_ccd_vencimento AS dia_vencimento,
				clt_id AS tipo,
				cls_id AS status,
				usu_id AS responsavel,
				tab_clientes.con_id AS condominio_id
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["emails"] = $this->buscaContatosEmails($dados["id"]);
			return $dados;
		}
		
	}
	
	function buscaContatosEmails($cliente_id) {
		
		$emails = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ccm_id AS id,
				CONCAT(ccm_usuario, '@', ccm_dominio) AS contato_email,
				tab_clientes_contatos_emails_tipos.cca_id AS contato_email_tipo_id,
				tab_clientes_contatos_emails_tipos.cca_nome AS contato_email_tipo
			FROM
				tab_clientes_contatos_emails
				INNER JOIN tab_clientes_contatos_emails_tipos ON (tab_clientes_contatos_emails.cca_id = tab_clientes_contatos_emails_tipos.cca_id)
			WHERE
				cli_id = {$cliente_id}
			ORDER BY
				contato_email_tipo,
				contato_email");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[] = $dados;
		}
		
		return $emails;
		
	}
	
	function buscaDadosCondominio($condominio_id) {
		
		if ($condominio_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					con_id AS id,
					con_nome AS nome
				FROM
					tab_condominios
				WHERE
					con_id = {$condominio_id}");
			
			return $this->bancodedados->resultadoArray($query);
			
		}
		
	}
	
	function buscaDadosCategoria($categoria_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				afc_id AS id,
				afc_nome AS nome,
				afc_acrescimo AS acrescimo,
				afc_decrescimo AS decrescimo,
				afc_disponivel AS disponivel,
				IFNULL(custom, 0) AS custom
			FROM
				tab_ajustes_financeiros_categorias
			WHERE
				afc_id = $categoria_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaDadosItem($ajuste_financeiro_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_ajustes_financeiros.ajf_id AS id,
				tab_ajustes_financeiros.cor_id AS contrato_id,
				tab_ajustes_financeiros.ajf_valor AS valor,
				tab_ajustes_financeiros.ajf_valor_tipo AS valor_tipo,
				tab_ajustes_financeiros.ajf_valor_total AS valor_total,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_inicio, '%m/%Y') AS competencia_inicio,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_termino, '%m/%Y') AS competencia_termino,
				tab_ajustes_financeiros.ajf_parcelas AS parcelas,
				tab_ajustes_financeiros.ajf_nf_numero AS nf_numero,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_nf_emissao, '%d/%m/%Y') AS nf_emissao,
				tab_ajustes_financeiros.ajf_nf_valor AS nf_valor,
				tab_ajustes_financeiros.ajf_descricao_custom AS descricao_custom_servico,
				tab_ajustes_financeiros.ajf_pai AS pai,
				tab_ajustes_financeiros_categorias.afc_id AS categoria_id,
				tab_ajustes_financeiros_categorias.afc_nome AS categoria_nome,
				IFNULL(tab_ajustes_financeiros_categorias.custom, 0) AS categoria_custom,
				tab_ajustes_financeiros_tipos.aft_id AS tipo_id,
				tab_ajustes_financeiros_tipos.aft_nome AS tipo_nome,
				tab_ajustes_financeiros_status.afs_id AS status_id,
				tab_ajustes_financeiros_status.afs_nome AS status_nome,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cof_id AS cobranca_formato_id,
				tab_clientes.cli_ccd_vencimento AS dia_vencimento,
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_assinaturas.ass_id AS assinatura_id,
		        CONCAT(tab_planos.pla_nome, ' &raquo; ', tab_planos_servicos.pls_nome) AS assinatura_nome

			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
				INNER JOIN tab_ajustes_financeiros_tipos ON (tab_ajustes_financeiros.aft_id = tab_ajustes_financeiros_tipos.aft_id)
				INNER JOIN tab_ajustes_financeiros_status ON (tab_ajustes_financeiros.afs_id = tab_ajustes_financeiros_status.afs_id)
				LEFT JOIN tab_clientes ON (tab_ajustes_financeiros.cli_id = tab_clientes.cli_id)
				LEFT JOIN tab_condominios ON (tab_ajustes_financeiros.con_id = tab_condominios.con_id)
		        LEFT JOIN tab_assinaturas ON (tab_ajustes_financeiros.ass_id = tab_assinaturas.ass_id)
		        LEFT JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		        LEFT JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				tab_ajustes_financeiros.ajf_id = {$ajuste_financeiro_id}".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));
		
		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->dados_item["valor"] = $this->formataValor($this->dados_item["valor"], $this->dados_item["valor_tipo"]);
			$this->dados_item["valor_total"] = $this->formataDinheiro($this->dados_item["valor_total"]);
			$this->dados_item["comentarios"] = $this->buscaComentarios($ajuste_financeiro_id);
			$this->dados_item["nf_valor"] = $this->formataDinheiro($this->dados_item["nf_valor"]);
			
		}
		
	}
	
	function buscaAjustesFilhos($ajuste_id) {
		
		$ajustes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ajf_id AS id
			FROM
				tab_ajustes_financeiros
			WHERE
				ajf_pai = {$ajuste_id}");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}
		
		return $ajustes;
		
	}
	
	function buscaClientesCondominio($condominio_id) {
		
		$clientes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cli_id AS id
			FROM
				tab_clientes
			WHERE
				con_id = {$condominio_id} AND
				cls_id IN (2, 3)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes[] = $dados["id"];
		}
		
		return $clientes;
		
	}
	
	function buscaDadosAssinatura($assinatura_id) {
		
		if ($assinatura_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					CONCAT(tab_planos.pla_nome, ' &raquo; ', tab_planos_servicos.pls_nome)
				FROM
					tab_assinaturas
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
					INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				WHERE
					tab_assinaturas.ass_id = {$assinatura_id}");
			
			return $this->bancodedados->resultado($query);
			
		}
		
	}
	
	###################
	### Busca Itens ###
	###################
	
	function buscaCategorias($somente_disponivel = false, $categoria_atual_id = null, $acrescimo = false, $decrescimo = false, $informacao_nf = null) {
		
		$categorias = array();
		$where = array("1");
		
		if ($somente_disponivel) $where[] = "afc_disponivel = '1'";
		if ($acrescimo) $where[] = "afc_acrescimo = '1'";
		if ($decrescimo) $where[] = "afc_decrescimo = '1'";
		
		$where = implode(" AND ", $where);
		
		if ($categoria_atual_id) {
			$where = "($where) OR (afc_id = $categoria_atual_id)";
		}
		
		$query = $this->bancodedados->query("
			SELECT" . ($informacao_nf ? "
				CONCAT(afc_id, ',', afc_solicitar_nf, ',', IFNULL(custom, 0))" : "
				afc_id") . " AS id,
				afc_nome AS nome
			FROM
				tab_ajustes_financeiros_categorias
			WHERE
				$where
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$categorias[$dados["id"]] = $dados["nome"];
		}
		
		return $categorias;
		
	}

	function chaveCategoriaSelect($categorias, $categoria_id) {

		$categoria_id = intval($categoria_id);

		foreach ($categorias as $id => $nome) {
			$partes = explode(",", $id);
			if (intval($partes[0]) === $categoria_id) {
				return $id;
			}
		}

		return $categoria_id;
	}

	function sanitizaDescricaoCustomServico($categoria_id) {

		$dados_categoria = $this->buscaDadosCategoria($categoria_id);

		if ($dados_categoria && intval($dados_categoria["custom"]) == 1) {
			$descricao_custom = trim(isset($_POST["descricao_custom_servico"]) ? $_POST["descricao_custom_servico"] : "");
			if ($descricao_custom === "") {
				return false;
			}
			return "'" . addslashes(substr($descricao_custom, 0, 255)) . "'";
		}

		return "NULL";
	}
	
	function buscaTipos($ocultar_valor_minimo = false, $tipo_id = null) {
		
		$tipos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				aft_id AS id,
				aft_nome AS nome
			FROM
				tab_ajustes_financeiros_tipos" . ($ocultar_valor_minimo ? "
			WHERE
				aft_id IN (1, 2)" . ($tipo_id ? " OR
				aft_id = $tipo_id" : "") : "") . "
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}
		
		return $tipos;
		
	}
	
	function buscaStatus() {
		
		$status = array();
		
		$query = $this->bancodedados->query("
			SELECT
				afs_id AS id,
				afs_nome AS nome
			FROM
				tab_ajustes_financeiros_status
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}
		
		return $status;
		
	}
	
	function buscaAssinaturas($cliente_id, $assinatura_ativa_id = null) {
		
		$assinaturas = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				CONCAT(tab_planos_servicos.pls_nome, ' &raquo; ', tab_planos.pla_nome, ' (', DATE_FORMAT(ass_data_ativacao, '%d/%m/%Y'), IF(ass_data_cancelamento IS NULL, '', CONCAT(' a ', DATE_FORMAT(ass_data_cancelamento, '%d/%m/%Y'))), ')') AS nome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id
			ORDER BY
				ass_data_ativacao");
	
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[$dados["id"]] = $dados["nome"];
		}
		
		return $assinaturas;
		
	}
	
	###################
	### Comentários ###
	###################
	
	function registraComentario($ajuste_id, $conteudo, $usuario_id = null) {
		
		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = trim(addslashes($conteudo));
		
		$this->bancodedados->query("
			INSERT INTO 
				tab_ajustes_financeiros_comentarios
			SET 
				afm_datahora = NOW(), 
				ajf_id = $ajuste_id, 
				afm_conteudo = '$conteudo', 
				usu_id = $usuario_id");
		
	}
	
	function buscaComentarios($ajuste_id) {
		
		$comentarios = array();
		
		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_ajustes_financeiros_comentarios.afm_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_ajustes_financeiros_comentarios.afm_conteudo AS conteudo
			FROM
				tab_ajustes_financeiros_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_ajustes_financeiros_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_ajustes_financeiros_comentarios.ajf_id = $ajuste_id
			ORDER BY
				tab_ajustes_financeiros_comentarios.afm_datahora ASC,
				tab_ajustes_financeiros_comentarios.afm_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
			$dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
			$dados["detalhamento"] .= substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Comentário:</b> {$dados["conteudo"]}";
		
			$comentarios[] = $dados;
		
		}
		
		return $comentarios;
		
	}
	
	function geraComentariosListagem($comentarios) {
		
		$listagem = controle::instanciaObjeto("listagem", array("detalhamento" => "Detalhamento"), $comentarios);
		
		return $listagem->conteudo;
		
	}
	
	function buscaFormatos($status_id, $avulsa = false) {
		
		$formatos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cof_id AS id,
				cof_nome AS nome
			FROM
				tab_cobrancas_formatos
			WHERE
				cfs_id = $status_id" . ($avulsa ? " AND
				cof_id IN (2, 8, 9, 11)" : "") . "
			ORDER BY
				nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formatos[$dados["id"]] = $dados["nome"];
		}
		
		return $formatos;
		
	}
	
	#############
	### Geral ###
	#############
	
	function fechar() {
		
		unset($this->conteudo);
		
		if ($this->dados_item) {
			if ($this->dados_item["condominio_id"]) {
				$this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscaAjustesCondominio) window.opener.buscaAjustesCondominio(' . $this->dados_item["condominio_id"] . ')</script>';
			} else {
				$this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscarAjustesFinanceiros) window.opener.buscarAjustesFinanceiros(' . $this->dados_item["cliente_id"] . ')</script>';
			}
		}
		
		$this->conteudo .= '<script type="text/javascript">window.close()</script>';
		
	}
	
	function formataValor($valor, $tipo) {
		
		if ($tipo == 1) {
			return $this->formataDinheiro($valor);
		} elseif ($tipo == 2) {
			return ($valor * 100) . "%";
		} else {
			return intval($valor);
		}
		
	}
	
	function formataValorListagem($valor, $tipo) {
		
		if ($tipo == 1) {
			return $this->formataDinheiro($valor);
		} elseif ($tipo == 2) {
			return ($valor * 100) . "%";
		} elseif ($tipo == 3) {
			return intval($valor) . " dia" . ($valor != 1 ? "s" : "");
		} else {
			return intval($valor) . " hora" . ($valor != 1 ? "s" : "");
		}
		
	}
	
	function ehAprovavel() {
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["aprovar"]) return false;
		if (in_array($this->dados_item["categoria_id"], array (8, 10)) && ! ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_administrativo"] || $this->permissoes[$this->dados_modulo["nome"]]["aprovar_todos"])) return false;
		if ($this->dados_item["categoria_id"] == 3 && ! ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_comercial"] || $this->permissoes[$this->dados_modulo["nome"]]["aprovar_todos"])) return false;
		if (in_array($this->dados_item["categoria_id"], array (6, 11, 12)) && ! ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_financeiro"] || $this->permissoes[$this->dados_modulo["nome"]]["aprovar_todos"])) return false;
		if ($this->dados_item["categoria_id"] == 5 && ! ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_tecnico"] || $this->permissoes[$this->dados_modulo["nome"]]["aprovar_todos"])) return false;
		if ($this->dados_item["categoria_id"] == 9 && ! ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_ti"] || $this->permissoes[$this->dados_modulo["nome"]]["aprovar_todos"])) return false;
		if ($this->dados_item["pai"]) return false;
		if (!in_array($this->dados_item["status_id"], array(1, 3))) return false;
		
		return true;
		
	}
	
	function ehReprovavel() {
		
		return false;
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["reprovar"]) return false;
		if (in_array($this->dados_item["categoria_id"], array (8, 10)) && ! ($this->permissoes[$this->dados_modulo["nome"]]["reprovar_administrativo"] || $this->permissoes[$this->dados_modulo["nome"]]["reprovar_todos"])) return false;
		if ($this->dados_item["categoria_id"] == 3 && ! ($this->permissoes[$this->dados_modulo["nome"]]["reprovar_comercial"] || $this->permissoes[$this->dados_modulo["nome"]]["reprovar_todos"])) return false;
		if (in_array($this->dados_item["categoria_id"], array (6, 11, 12)) && ! ($this->permissoes[$this->dados_modulo["nome"]]["reprovar_financeiro"] || $this->permissoes[$this->dados_modulo["nome"]]["reprovar_todos"])) return false;
		if ($this->dados_item["categoria_id"] == 5 && ! ($this->permissoes[$this->dados_modulo["nome"]]["reprovar_tecnico"] or $this->permissoes[$this->dados_modulo["nome"]]["reprovar_todos"])) return false;
		if ($this->dados_item["categoria_id"] == 9 && ! ($this->permissoes[$this->dados_modulo["nome"]]["reprovar_ti"] || $this->permissoes[$this->dados_modulo["nome"]]["reprovar_todos"])) return false;
		if ($this->dados_item["pai"]) return false;
		if (!in_array($this->dados_item["status_id"], array(1, 2))) return false;
		
		return true;
		
	}
	
	function ehCancelavel() {
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) return false;
		if (!in_array($this->dados_item["status_id"], array(5, 7))) return false;
		
		return true;
		
	}
	
	function ehDescancelavel() {
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["descancelar"]) return false;
		if (!in_array($this->dados_item["status_id"], array(6))) return false;
		
		return true;
		
	}
	
	function ehFinalizavel() {
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) return false;
		if ($this->dados_item["contrato_id"]) return false;
		if (!in_array($this->dados_item["status_id"], array(5))) return false;
		
		return true;
		
	}
	
	function ehAtivavel() {
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["ativar"]) return false;
		if ($this->dados_item["pai"]) return false;
		if (!($this->dados_item["status_id"] == 2 || $this->ehAprovavel())) return false;
		
		return true;
		
	}
	
	function ehEnviavelCobranca() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["enviar_cobranca"]) return false;
		if (!$this->buscaCobranca($this->dados_item["id"], true)) return false;
		
		return true;
		
	}
	
	function ehRemovivel() {
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["remover"]) return false;
		if ($this->dados_item["pai"]) return false;
		if ($this->buscaCobranca($this->dados_item["id"])) return false;
		
		return true;
		
	}
	
	function ehEditavel() {
		
		if ($this->dados_item["pai"]) return false;
		if (!in_array($this->dados_item["status_id"], array(1, 2, 3))) return false;
		
		return true;
		
	}
		
	function ehEditavelDadosNF() {
		
		if (!$this->permissoes[$this->dados_modulo["nome"]]["editar_dados_nf"]) return false;
		if ($this->dados_item["condominio_id"]) return false;
		if (!in_array($this->dados_item["tipo_id"], array(1))) return false;
		//if (!in_array($this->dados_item["status_id"], array(5, 7, 8))) return false;
		if (!in_array($this->dados_item["categoria_id"], array(19, 20, 23))) return false;
		
		return true;
		
	}

	function ehEditavelMinimo() {
		
		if (!in_array($this->dados_item["status_id"], array(4, 6, 7))) return false;

		return true;
		
	}
	
	function buscaErrosAtivacao($dados_ajuste) {
		
		$possui_assinatura_ativa = $this->possuiAssinaturaAtiva($dados_ajuste);
		$erro = array("ativavel" => true, "descricao" => array());
		
		if ($dados_ajuste["cliente_id"] && $dados_ajuste["valor_tipo"] != 1 && ! $possui_assinatura_ativa) {
			$erro["ativavel"] = $erro["ativavel"] && false;
			$erro["descricao"][] = "Ajuste em <b>" . ($dados_ajuste["valor_tipo"] == 2 ? "porcental" : ($dados_ajuste["valor_tipo"] == 3 ? "dias" : "horas")) . "</b> referente a período sem assinatura ativa.";
		}
		
		if ($erro["descricao"]) {
			$erro["descricao"] = "&nbsp; &raquo;&nbsp; " . implode("<br>&nbsp; &raquo;&nbsp; ", $erro["descricao"]);
			return $erro;
		}
		
	}
	
	function buscaVencimentoInicio($competencia_inicio) {
		
		$competencia_inicio = substr($competencia_inicio, 3, 4) . substr($competencia_inicio, 0, 2) . "01";
		
		return date("m/Y", strtotime("$competencia_inicio 1 MONTH"));
		
	}
	
	function buscaVencimentoTermino($dados_ajuste) {
		
		$filtro = array();
		
		if ($dados_ajuste["competencia_inicio"]) {
			$filtro[] = "DATE_FORMAT(DATE_SUB(col_vencimento, INTERVAL 1 MONTH), '%Y%m') >= " . substr($dados_ajuste["competencia_inicio"], 3, 4) . substr($dados_ajuste["competencia_inicio"], 0, 2);
		}
		if ($dados_ajuste["competencia_termino"]) {
			$filtro[] = "DATE_FORMAT(DATE_SUB(col_vencimento, INTERVAL 1 MONTH), '%Y%m') <= " . substr($dados_ajuste["competencia_termino"], 3, 4) . substr($dados_ajuste["competencia_termino"], 0, 2);
		}
		$filtro[] = "cla_id >= 4";
		
		$filtro = implode(" AND\n        ", $filtro);
		
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(col_vencimento, '%m/%Y') AS vencimento
			FROM
				tab_cobrancas_lotes
			WHERE
				$filtro
			ORDER BY
				col_vencimento DESC
			LIMIT
				1");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function existeLoteCobranca($dados_ajuste) {
		
		$filtro = array();
		
		if ($dados_ajuste["competencia_inicio"]) {
			$filtro[] = "DATE_FORMAT(DATE_SUB(col_vencimento, INTERVAL 1 MONTH), '%Y%m') >= " . substr($dados_ajuste["competencia_inicio"], 3, 4) . substr($dados_ajuste["competencia_inicio"], 0, 2);
		}
		if ($dados_ajuste["competencia_termino"]) {
			$filtro[] = "DATE_FORMAT(DATE_SUB(col_vencimento, INTERVAL 1 MONTH), '%Y%m') <= " . substr($dados_ajuste["competencia_termino"], 3, 4) . substr($dados_ajuste["competencia_termino"], 0, 2);
		}
		$filtro[] = "cla_id >= 4";
		
		$filtro = implode(" AND\n        ", $filtro);
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(col_id)
			FROM
				tab_cobrancas_lotes
			WHERE
				$filtro");
		
		return $this->bancodedados->resultadoArray($query) > 0;
		
	}
	
	function possuiAssinaturaAtiva($dados_ajuste) {
		
		$filtro = array();
		
		$filtro[] = "cli_id = {$dados_ajuste["cliente_id"]}";
		if ($dados_ajuste["competencia_inicio"]) {
			$filtro[] = "DATE_FORMAT(ass_data_ativacao, '%Y%m') <= " . substr($dados_ajuste["competencia_inicio"], 3, 4) . substr($dados_ajuste["competencia_inicio"], 0, 2);
		}
		if ($dados_ajuste["competencia_termino"]) {
			$filtro[] = "(ass_data_cancelamento IS NULL OR DATE_FORMAT(ass_data_cancelamento, '%Y%m') >= " . substr($dados_ajuste["competencia_termino"], 3, 4) . substr($dados_ajuste["competencia_termino"], 0, 2) . ")";
		}
		
		$filtro = implode(" AND\n        ", $filtro);
		
		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id
			FROM
				tab_assinaturas
			WHERE
				$filtro");
		
		return $this->bancodedados->resultadoArray($query) > 0;
		
	}
	
	function subtraiDatas($data_1, $data_2) {
		
		if (strlen($data_1) > 6) $data_1 = substr($data_1, 3, 4) . substr($data_1, 0, 2);
		if (strlen($data_2) > 6) $data_2 = substr($data_2, 3, 4) . substr($data_2, 0, 2);
		return intval($data_1 - $data_2);
		
	}
	
	#################
	### Adicionar ###
	#################
	
	function adicionar() {
		
		if ($_GET["cli"]) {
			$this->adicionarAjusteCliente();
		} elseif ($_GET["con"]) {
			$this->adicionarAjusteCondominio();
		} else {
			$this->fechar();
		}
		
	}
	
	function adicionarAjusteCliente() {
		
		if (($dados_cliente = $this->buscaDadosCliente($_GET["cli"])) != false) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.adicionar.inc.php");

				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&cli={$dados_cliente["id"]}", $this->conteudo);
				$this->conteudo = str_replace('[cliente]', $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace('[tipo]', $this->geraRadioButtons("tipo", $this->buscaTipos(true), 1, null, true, 2, "mudaTipo(this)"), $this->conteudo);
				$this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(true, null, true, false, true), null, true, "categoria", "100%", "mudaCategoria(this)"), $this->conteudo);
				$this->conteudo = str_replace('[assinatura]', $this->geraSelect($this->buscaAssinaturas($dados_cliente["id"]), null, true, "assinatura", "100%", null, false), $this->conteudo);
				$this->conteudo = str_replace('[valor_tipo]', $this->geraSelect($this->valor_tipos, 1, false, "valor_tipo", "135px", "mudaValorTipo(this)", false), $this->conteudo);
				$this->conteudo = str_replace('[nf_numero]', $this->geraInput("text", "nf_numero", "135px", null, false, 15, "return formataNumero(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[nf_emissao]', $this->geraInput("text", "nf_emissao", "135px", null, false, null, "return formataData(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[nf_valor]', $this->geraInput("text", "nf_valor", "135px", null, false, null, "return formataDinheiro(this, event)"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				
			} else {
				
				$categoria_partes = explode(",", $_POST["categoria"]);
				$_POST["categoria"] = intval($categoria_partes[0]);
				
				if ($_POST["tipo"] == 1) {
					$_POST["valor_tipo"] = 1;
				}
				
				$_POST["assinatura"] = ! $_POST["assinatura"] ? "NULL" : $_POST["assinatura"];
				
				$_POST["parcelas"] = $_POST["competencia_termino"] != "NULL" ? (substr($_POST["competencia_termino"], 3, 4) * 12 + substr($_POST["competencia_termino"], 0, 2)) - (substr($_POST["competencia_inicio"], 3, 4) * 12 + substr($_POST["competencia_inicio"], 0, 2)) + 1 : "NULL";
				
				$_POST["competencia_inicio"] = "'" . substr($_POST["competencia_inicio"], 3, 4) . "-" . substr($_POST["competencia_inicio"], 0, 2) . "-" . "01'";
				$_POST["competencia_termino"] = $_POST["competencia_termino"] ? "'" . substr($_POST["competencia_termino"], 3, 4) . "-" . substr($_POST["competencia_termino"], 0, 2) . "-" . "01'" : "NULL";
				
				if ($_POST["valor_tipo"] == 1 || $_POST["valor_tipo"] == 3 || $_POST["valor_tipo"] == 4) {
					// Dinheiro/Dias/Horas
					$_POST["valor_mensal"] = preg_replace("/[^0-9]/", "", $_POST["valor_mensal"]);
				} elseif ($_POST["valor_tipo"] == 2) {
					// Porcentagem
					$_POST["valor_mensal"] = preg_replace("/[^0-9]/", "", $_POST["valor_mensal"]) / 100;
				}
				
				$_POST["valor_total"] = $_POST["parcelas"] != "NULL" && $_POST["valor_tipo"] == 1 ? $_POST["valor_mensal"] * $_POST["parcelas"] : "NULL";
				
				$_POST["nf_numero"] = $_POST["nf_numero"] ? $_POST["nf_numero"] : "NULL";
				$_POST["nf_emissao"] = $_POST["nf_emissao"] ? "'" . substr($_POST["nf_emissao"], 6, 4) . "-" . substr($_POST["nf_emissao"], 3, 2) . "-" . substr($_POST["nf_emissao"], 0, 2) . "'" : "NULL";
				$_POST["nf_valor"] = $_POST["nf_valor"] ? (int) preg_replace("/[^0-9]/", "", $_POST["nf_valor"]) : "NULL";
				
				$dados_categoria = $this->buscaDadosCategoria($_POST["categoria"]);
				if ($dados_categoria && intval($dados_categoria["custom"]) == 1) {
					$descricao_custom = trim($_POST["descricao_custom_servico"]);
					if ($descricao_custom === "") {
						header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar&cli={$_GET["cli"]}");
						return;
					}
					$_POST["descricao_custom_servico"] = "'" . addslashes(substr($descricao_custom, 0, 255)) . "'";
				} else {
					$_POST["descricao_custom_servico"] = "NULL";
				}
				
				$this->bancodedados->query("
					INSERT INTO
						tab_ajustes_financeiros
					SET
 						ajf_datahora = NOW(),
						cli_id = {$_GET["cli"]},
						ass_id = {$_POST["assinatura"]},
						afc_id = {$_POST["categoria"]},
						aft_id = {$_POST["tipo"]},
						ajf_valor_tipo = {$_POST["valor_tipo"]},
						ajf_valor = {$_POST["valor_mensal"]},
						ajf_valor_total = {$_POST["valor_total"]},
						ajf_competencia_inicio = {$_POST["competencia_inicio"]},
						ajf_competencia_termino = {$_POST["competencia_termino"]},
						ajf_parcelas = {$_POST["parcelas"]},
						ajf_nf_numero = {$_POST["nf_numero"]},
						ajf_nf_emissao = {$_POST["nf_emissao"]},
						ajf_nf_valor = {$_POST["nf_valor"]},
						ajf_descricao_custom = {$_POST["descricao_custom_servico"]},
						afs_id = 1");
				
				$ajuste_id = $this->bancodedados->ultimoIdInserido();
				
				if ($_POST["comentarios"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentarios"]));
				$this->registraComentario($ajuste_id, "<b>Ação:</b> Adicionado" . $comentarios, $_SESSION["usuario"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $ajuste_id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$ajuste_id");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	function adicionarAjusteCondominio() {
		
		if (($dados_condominio = $this->buscaDadosCondominio($_GET["con"])) != false) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.condominio.adicionar.inc.php");

				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&con={$dados_condominio["id"]}", $this->conteudo);
				$this->conteudo = str_replace('[condominio]', $dados_condominio["nome"], $this->conteudo);
				$this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(true), null, true, "categoria", "100%"), $this->conteudo);
				$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(true), null, false, "tipo", "100%"), $this->conteudo);
				$this->conteudo = str_replace("[valor_tipo]", $this->geraSelect($this->valor_tipos, 1, false, "valor_tipo", "135px", "mudaValorTipo(this)"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
			} else {
				

				if ($_POST["valor_tipo"] == 1 || $_POST["valor_tipo"] == 3 || $_POST["valor_tipo"] == 4) {
					// Dinheiro/Dias/Horas
					$_POST["valor"] = preg_replace("/[^0-9]/", "", $_POST["valor"]);
				} elseif ($_POST["valor_tipo"] == 2) {
					// Porcentagem
					$_POST["valor"] = preg_replace("/[^0-9]/", "", $_POST["valor"]) / 100;
				}
				
				$_POST["competencia_inicio"] = $_POST["competencia_inicio"] ? "'" . substr($_POST["competencia_inicio"], 3, 4) . substr($_POST["competencia_inicio"], 0, 2) . "01'" : "NULL";
				$_POST["competencia_termino"] = $_POST["competencia_termino"] ? "'" . substr($_POST["competencia_termino"], 3, 4) . substr($_POST["competencia_termino"], 0, 2) . "01'" : "NULL";

				$this->bancodedados->query("
					INSERT INTO 
						tab_ajustes_financeiros
					SET 
						ajf_datahora = NOW(), 
						con_id = {$_GET["con"]}, 
						afc_id = {$_POST["categoria"]}, 
						aft_id = {$_POST["tipo"]}, 
						ajf_valor = {$_POST["valor"]}, 
						ajf_valor_tipo = {$_POST["valor_tipo"]}, 
						ajf_competencia_inicio = {$_POST["competencia_inicio"]}, 
						ajf_competencia_termino = {$_POST["competencia_termino"]}, 
						afs_id = 1");
				
				$ajuste_id = $this->bancodedados->ultimoIdInserido();
				
				$clientes_condominio = $this->buscaClientesCondominio($_GET["con"]);
				
				foreach ($clientes_condominio as $cliente_condominio) {
					
					$this->bancodedados->query("
						INSERT INTO 
							tab_ajustes_financeiros
						SET' 
							ajf_datahora = NOW(), 
							cli_id = {$cliente_condominio}, 
							afc_id = {$_POST["categoria"]}, 
							aft_id = {$_POST["tipo"]}, 
							ajf_valor = {$_POST["valor"]}, 
							ajf_valor_tipo = {$_POST["valor_tipo"]}, 
							ajf_competencia_inicio = {$_POST["competencia_inicio"]}, 
							ajf_competencia_termino = {$_POST["competencia_termino"]}, 
							afs_id = 1, 
							ajf_pai = $ajuste_id");
					
					$id_filho = $this->bancodedados->ultimoIdInserido();
					
					if ($_POST["comentarios"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentarios"]));
					$this->registraComentario($id_filho, "<b>Ação:</b> Herdado do condominio" . $comentarios, $_SESSION["usuario"]);
					
				}
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_parcelas = (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1)
					WHERE
						ajf_id = $ajuste_id");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_parcelas = (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1)
					WHERE
						ajf_pai = $ajuste_id");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_valor_total = IF(ajf_valor_tipo = 1 AND ajf_parcelas IS NOT NULL, ajf_valor * ajf_parcelas, NULL)
					WHERE
						ajf_id = $ajuste_id");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_valor_total = IF(ajf_valor_tipo = 1 AND ajf_parcelas IS NOT NULL, ajf_valor * ajf_parcelas, NULL)
					WHERE
						ajf_pai = $ajuste_id");
				
				if ($_POST["comentarios"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentarios"]));
				$this->registraComentario($ajuste_id, "<b>Ação:</b> Adicionado" . $comentarios, $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $ajuste_id);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$ajuste_id");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	###########
	### Ver ###
	###########
	
	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehEditavelDadosNF()) $botoes[] = '<input type="button" name="editar_dados_nf" class="buttonAzulEscuroBranco11" value="Editar Dados NF" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dados_nf&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehEnviavelCobranca()) $botoes[] = '<input type="button" name="enviar_cobranca" class="buttonAzulEscuroBranco11" value="Enviar Cobrança" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=enviar_cobranca&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" value="Reprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=finalizar&i=' . $this->dados_item["id"] . '\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			if ($this->dados_item["condominio_id"]) {
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.condominio.ver.inc.php");
				$this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
				$this->conteudo = str_replace('[condominio_nome]', $this->dados_item["condominio_nome"], $this->conteudo);
			} else {
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.ver.inc.php");
				$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[assinatura]', $this->dados_item["assinatura_nome"], $this->conteudo);
			}
			
			$this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace('[categoria]', $this->dados_item["categoria_nome"], $this->conteudo);
			$this->conteudo = str_replace('[descricao_custom_servico]', $this->dados_item["descricao_custom_servico"], $this->conteudo);
			$this->conteudo = str_replace('[competencia_inicio]', $this->dados_item["competencia_inicio"], $this->conteudo);
			$this->conteudo = str_replace('[competencia_termino]', $this->dados_item["competencia_termino"], $this->conteudo);
			$this->conteudo = str_replace('[parcelas]', $this->dados_item["parcelas"], $this->conteudo);
			$this->conteudo = str_replace('[valor_mensal]', $this->dados_item["valor"], $this->conteudo);
			$this->conteudo = str_replace('[valor_total]', $this->dados_item["valor_total"], $this->conteudo);
			$this->conteudo = str_replace('[nf_numero]', $this->dados_item["nf_numero"], $this->conteudo);
			$this->conteudo = str_replace('[nf_emissao]', $this->dados_item["nf_emissao"], $this->conteudo);
			$this->conteudo = str_replace('[nf_valor]', $this->dados_item["nf_valor"], $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
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
			
			if (! $_POST) {
				
				$eh_editavel = $this->ehEditavel();
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				if ($this->ehEditavelDadosNF()) $botoes[] = '<input type="button" name="editar_dados_nf" class="buttonAzulEscuroBranco11" value="Editar Dados NF" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar_dados_nf&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehEnviavelCobranca()) $botoes[] = '<input type="button" name="enviar_cobranca" class="buttonAzulEscuroBranco11" value="Enviar Cobrança" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=enviar_cobranca&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" value="Reprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=finalizar&i=' . $this->dados_item["id"] . '\';">';
				if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				if ($this->dados_item["condominio_id"]) {
					$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.condominio.editar.inc.php");
					$this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
					$this->conteudo = str_replace('[condominio_nome]', $this->dados_item["condominio_nome"], $this->conteudo);
				} else {
					$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.editar.inc.php");
					$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
					$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
					$this->conteudo = str_replace('[assinatura]', $this->geraSelect($this->buscaAssinaturas($this->dados_item["cliente_id"], $this->dados_item["assinatura_id"]), $this->dados_item["assinatura_id"], true, "assinatura", "100%", null, $eh_editavel && $this->dados_item["tipo_id"] == 2), $this->conteudo);
				}
				
				$categorias = $this->buscaCategorias(true, $this->dados_item["categoria_id"], false, false, true);
				$categoria_selecionada = $this->chaveCategoriaSelect($categorias, $this->dados_item["categoria_id"]);
				$descricao_custom_habilitada = $eh_editavel && intval($this->dados_item["categoria_custom"]) == 1;

				$this->conteudo = str_replace('[tipo]', $this->geraRadioButtons("tipo", $this->buscaTipos(true, $this->dados_item["tipo_id"]), $this->dados_item["tipo_id"], null, $eh_editavel, 3, "mudaTipo(this)"), $this->conteudo);
				$this->conteudo = str_replace('[categoria]', $this->geraSelect($categorias, $categoria_selecionada, true, "categoria", "100%", "mudaCategoria(this)", $eh_editavel), $this->conteudo);
				$this->conteudo = str_replace('[descricao_custom_servico]', htmlspecialchars(isset($this->dados_item["descricao_custom_servico"]) ? $this->dados_item["descricao_custom_servico"] : ""), $this->conteudo);
				$this->conteudo = str_replace('[descricao_custom_disabled]', $descricao_custom_habilitada ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[valor_mensal]', $this->dados_item["valor"], $this->conteudo);
				$this->conteudo = str_replace('[valor_mensal_disabled]', $eh_editavel ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[valor_tipo]', $this->geraSelect($this->valor_tipos, $this->dados_item["valor_tipo"], false, "valor_tipo", "135px", "mudaValorTipo(this)", $eh_editavel && $this->dados_item["tipo_id"] == 2), $this->conteudo);
				$this->conteudo = str_replace('[valor_total]', $this->dados_item["valor_total"], $this->conteudo);
				$this->conteudo = str_replace('[valor_total_disabled]', $eh_editavel && $this->dados_item["valor_tipo"] == 1 && $this->dados_item["parcelas"] ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[competencia_inicio]', $this->dados_item["competencia_inicio"], $this->conteudo);
				$this->conteudo = str_replace('[competencia_inicio_disabled]', $eh_editavel ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[competencia_termino]', $this->dados_item["competencia_termino"], $this->conteudo);
				$this->conteudo = str_replace('[competencia_termino_disabled]', $eh_editavel ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[parcelas]', $this->dados_item["parcelas"], $this->conteudo);
				$this->conteudo = str_replace('[parcelas_disabled]', $eh_editavel ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace('[nf_numero]', $this->geraInput("text", "nf_numero", "135px", $this->dados_item["nf_numero"], $eh_editavel, null, "return formataNumero(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[nf_emissao]', $this->geraInput("text", "nf_emissao", "135px", $this->dados_item["nf_emissao"], $eh_editavel, null, "return formataData(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[nf_valor]', $this->geraInput("text", "nf_valor", "135px", $this->dados_item["nf_valor"], $eh_editavel, null, "return formataDinheiro(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
				
			} else {
				
				if ($this->ehEditavel()) {
					
					if ($_POST["tipo"] == 1) {
						$_POST["valor_tipo"] = 1;
					}
					
					$categoria_partes = explode(",", $_POST["categoria"]);
					$_POST["categoria"] = intval($categoria_partes[0]);
					
					$_POST["assinatura"] = ! $_POST["assinatura"] ? "NULL" : $_POST["assinatura"];
					
					$_POST["parcelas"] = $_POST["competencia_termino"] != "NULL" ? (substr($_POST["competencia_termino"], 3, 4) * 12 + substr($_POST["competencia_termino"], 0, 2)) - (substr($_POST["competencia_inicio"], 3, 4) * 12 + substr($_POST["competencia_inicio"], 0, 2)) + 1 : "NULL";
					
					$_POST["competencia_inicio"] = "'" . substr($_POST["competencia_inicio"], 3, 4) . "-" . substr($_POST["competencia_inicio"], 0, 2) . "-" . "01'";
					$_POST["competencia_termino"] = $_POST["competencia_termino"] ? "'" . substr($_POST["competencia_termino"], 3, 4) . "-" . substr($_POST["competencia_termino"], 0, 2) . "-" . "01'" : "NULL";
					
					// Trata Dados
					if ($_POST["valor_tipo"] == 1 || $_POST["valor_tipo"] == 3 || $_POST["valor_tipo"] == 4) {
						// Dinheiro/Dias/Horas
						$_POST["valor_mensal"] = preg_replace("/[^0-9]/", "", $_POST["valor_mensal"]);
					} elseif ($_POST["valor_tipo"] == 2) {
						// Porcentagem
						$_POST["valor_mensal"] = preg_replace("/[^0-9]/", "", $_POST["valor_mensal"]) / 100;
					}
					
					$_POST["valor_total"] = $_POST["parcelas"] != "NULL" && $_POST["valor_tipo"] == 1 ? $_POST["valor_mensal"] * $_POST["parcelas"] : "NULL";
					
					$_POST["nf_numero"] = $_POST["nf_numero"] ? $_POST["nf_numero"] : "NULL";
					$_POST["nf_emissao"] = $_POST["nf_emissao"] ? "'" . substr($_POST["nf_emissao"], 6, 4) . "-" . substr($_POST["nf_emissao"], 3, 2) . "-" . substr($_POST["nf_emissao"], 0, 2) . "'" : "NULL";
					$_POST["nf_valor"] = $_POST["nf_valor"] ? (int) preg_replace("/[^0-9]/", "", $_POST["nf_valor"]) : "NULL";

					$_POST["descricao_custom_servico"] = $this->sanitizaDescricaoCustomServico($_POST["categoria"]);
					if ($_POST["descricao_custom_servico"] === false) {
						header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}");
						return;
					}
					
					$this->bancodedados->query("
						UPDATE
							tab_ajustes_financeiros
						SET
							aft_id = {$_POST["tipo"]},
							afc_id = {$_POST["categoria"]},
							ass_id = {$_POST["assinatura"]},
							ajf_competencia_inicio = {$_POST["competencia_inicio"]},
							ajf_competencia_termino = {$_POST["competencia_termino"]},
							ajf_parcelas = {$_POST["parcelas"]},
							ajf_valor_tipo = {$_POST["valor_tipo"]},
							ajf_valor = {$_POST["valor_mensal"]},
							ajf_valor_total = {$_POST["valor_total"]},
							ajf_nf_numero = {$_POST["nf_numero"]},
							ajf_nf_emissao = {$_POST["nf_emissao"]},
							ajf_nf_valor = {$_POST["nf_valor"]},
							ajf_descricao_custom = {$_POST["descricao_custom_servico"]},
							afs_id = 1
						WHERE
							ajf_id = {$_GET["i"]}");
					
					$this->bancodedados->query("
						UPDATE
							tab_ajustes_financeiros
						SET
							aft_id = {$_POST["tipo"]},
							afc_id = {$_POST["categoria"]},
							ass_id = {$_POST["assinatura"]},
							ajf_competencia_inicio = {$_POST["competencia_inicio"]},
							ajf_competencia_termino = {$_POST["competencia_termino"]},
							ajf_parcelas = {$_POST["parcelas"]},
							ajf_valor_tipo = {$_POST["valor_tipo"]},
							ajf_valor = {$_POST["valor_mensal"]},
							ajf_valor_total = {$_POST["valor_total"]},
							ajf_nf_numero = {$_POST["nf_numero"]},
							ajf_nf_emissao = {$_POST["nf_emissao"]},
							ajf_nf_valor = {$_POST["nf_valor"]},
							ajf_descricao_custom = {$_POST["descricao_custom_servico"]},
							afs_id = 1
						WHERE
							ajf_pai = {$_GET["i"]}");
					
				}
				
				if ($_POST["comentarios"]) {
					
					$_POST["comentarios"] = "<b>Comentario</b>: " . trim(addslashes($_POST["comentarios"]));
					
					$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
					
					$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);
					
					foreach ($ajustes_filhos as $ajuste_filho) {
						$this->registraComentario($ajuste_filho, $_POST["comentarios"], $_SESSION["usuario"]);
					}
					
				}
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_parcelas = (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1)
					WHERE
						ajf_id = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_parcelas = (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1)
					WHERE
						ajf_pai = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_valor_total = IF(ajf_valor_tipo = 1 AND ajf_parcelas IS NOT NULL, ajf_valor * ajf_parcelas, NULL)
					WHERE
						ajf_id = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_valor_total = IF(ajf_valor_tipo = 1 AND ajf_parcelas IS NOT NULL, ajf_valor * ajf_parcelas, NULL)
					WHERE
						ajf_pai = {$_GET["i"]}");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
		
	#######################
	### Editar Dados NF ###
	#######################

	function editar_dados_nf() {
		
		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehEditavelDadosNF()) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.editar_dados_nf.inc.php");
				
				$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[assinatura]', $this->dados_item["assinatura_nome"], $this->conteudo);
				$this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
				$this->conteudo = str_replace('[categoria]', $this->dados_item["categoria_nome"], $this->conteudo);
				$this->conteudo = str_replace('[competencia_inicio]', $this->dados_item["competencia_inicio"], $this->conteudo);
				$this->conteudo = str_replace('[competencia_termino]', $this->dados_item["competencia_termino"], $this->conteudo);
				$this->conteudo = str_replace('[parcelas]', $this->dados_item["parcelas"], $this->conteudo);
				$this->conteudo = str_replace('[valor_mensal]', $this->dados_item["valor"], $this->conteudo);
				$this->conteudo = str_replace('[valor_total]', $this->dados_item["valor_total"], $this->conteudo);
				$this->conteudo = str_replace('[nf_numero]', $this->geraInput("text", "nf_numero", "135px", $this->dados_item["nf_numero"], true, null, "return formataNumero(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[nf_emissao]', $this->geraInput("text", "nf_emissao", "135px", $this->dados_item["nf_emissao"], true, null, "return formataData(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[nf_valor]', $this->geraInput("text", "nf_valor", "135px", $this->dados_item["nf_valor"], true, null, "return formataDinheiro(this, event)"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_dados_nf&i={$this->dados_item["id"]}", $this->conteudo);
				
			} else {
				
				$_POST["nf_numero"] = $_POST["nf_numero"] ? $_POST["nf_numero"] : "NULL";
				$_POST["nf_emissao"] = $_POST["nf_emissao"] ? "'" . substr($_POST["nf_emissao"], 6, 4) . "-" . substr($_POST["nf_emissao"], 3, 2) . "-" . substr($_POST["nf_emissao"], 0, 2) . "'" : "NULL";
				$_POST["nf_valor"] = $_POST["nf_valor"] ? (int) preg_replace("/[^0-9]/", "", $_POST["nf_valor"]) : "NULL";
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_nf_numero = {$_POST["nf_numero"]},
						ajf_nf_emissao = {$_POST["nf_emissao"]},
						ajf_nf_valor = {$_POST["nf_valor"]}
					WHERE
						ajf_id = {$_GET["i"]}");
				
				if ($_POST["comentarios"]) {
					
					$_POST["comentarios"] = "<b>Comentario</b>: " . trim(addslashes($_POST["comentarios"]));
					
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
	
	###############
	### Aprovar ###
	###############
	
	function aprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehAprovavel()) {
			
			if (! $_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.aprovar.inc.php");
				
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=aprovar&i={$_GET["i"]}", $this->conteudo);
			
			} else {
				
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 2 WHERE ajf_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 2 WHERE ajf_pai = {$_GET["i"]}");
				
				if ($_POST["comentario"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentario"]));
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Aprovado" . $comentarios, $_SESSION["usuario"]);
				
				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);
				
				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->registraComentario($ajuste_filho, "<b>Ação:</b> Aprovado" . $comentarios, $_SESSION["usuario"]);
				}
				
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
		
		if ($this->dados_item && $this->ehReprovavel()) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.reprovar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $_GET["i"], $this->conteudo);
			
			} else {
				
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 3 WHERE ajf_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 3 WHERE ajf_pai = {$_GET["i"]}");
				
				if ($_POST["comentario"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentario"]));
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Reprovado" . $comentarios, $_SESSION["usuario"]);

				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);
				
				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->registraComentario($ajuste_filho, "<b>Ação:</b> Reprovado" . $comentarios, $_SESSION["usuario"]);
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	################
	### Cancelar ###
	################
	
	function cancelar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehCancelavel()) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.cancelar.inc.php");
				
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=cancelar&i={$_GET["i"]}", $this->conteudo);
				
			} else {

				$status = $this->dados_item["status_id"] >= 5 ? 6 : 4;
				
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = $status WHERE ajf_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = $status WHERE ajf_pai = {$_GET["i"]}");
				
				if ($_POST["comentario"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentario"]));
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Cancelado" . $comentarios, $_SESSION["usuario"]);
				
				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);

				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->registraComentario($ajuste_filho, "<b>Ação:</b> Cancelado" . $comentarios, $_SESSION["usuario"]);
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	###################
	### Descancelar ###
	###################
	
	function descancelar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehDescancelavel()) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.descancelar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=descancelar&i=' . $_GET["i"], $this->conteudo);
				
			} else {

				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 5 WHERE ajf_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 5 WHERE ajf_pai = {$_GET["i"]}");
				
				if ($_POST["comentario"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentario"]));
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Desancelado" . $comentarios, $_SESSION["usuario"]);

				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);
				
				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->registraComentario($ajuste_filho, "<b>Ação:</b> Descancelado" . $comentarios, $_SESSION["usuario"]);
				}
				
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
		
		if ($this->dados_item && $this->ehFinalizavel()) {
			
			if (! $_POST) {
			
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.finalizar.inc.php");
				
				$this->conteudo = str_replace("[competencia_termino]", date("m/Y"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$_GET["i"]}", $this->conteudo);
				
			} else {

				$_POST["competencia_termino"] = $_POST["competencia_termino"] ? "'" . substr($_POST["competencia_termino"], 3, 4) . substr($_POST["competencia_termino"], 0, 2) . "01'" : "'" . date("Y-m-01") . "'";
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						afs_id = 8,
						ajf_competencia_termino = {$_POST["competencia_termino"]}
					WHERE
						ajf_id = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						afs_id = 8,
						ajf_competencia_termino = {$_POST["competencia_termino"]}
					WHERE
						ajf_pai = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_parcelas = (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1)
					WHERE
						ajf_id = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_parcelas = (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1)
					WHERE
						ajf_pai = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_valor_total = IF(ajf_valor_tipo = 1 AND	ajf_parcelas IS NOT NULL, ajf_valor * ajf_parcelas, NULL)
					WHERE
						ajf_id = {$_GET["i"]}");
				
				$this->bancodedados->query("
					UPDATE
						tab_ajustes_financeiros
					SET
						ajf_valor_total = IF(ajf_valor_tipo = 1 AND	ajf_parcelas IS NOT NULL, ajf_valor * ajf_parcelas, NULL)
					WHERE
						ajf_pai = {$_GET["i"]}");
				
				if ($_POST["comentario"]) $comentarios = "<br><b>Comentário:</b> " . trim(addslashes($_POST["comentario"]));
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Finalizado" . $comentarios, $_SESSION["usuario"]);

				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);

				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->registraComentario($ajuste_filho, "<b>Ação:</b> Finalizado" . $comentarios, $_SESSION["usuario"]);
				}
				
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
		
		if ($this->dados_item && $this->ehAtivavel()) {
			
			$erros = $this->buscaErrosAtivacao($this->dados_item);
			
			if (! $_POST || ($erros && ! $erros["ativavel"])) {
				
				if ($erros) {
					
					$botoes = array();
					if ($erros["ativavel"]) $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
					
					$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.ativar.erros.inc.php");

					$this->conteudo = str_replace("[erros]", $erros["descricao"], $this->conteudo);
					$this->conteudo = str_replace("[continuar]", ($erros["ativavel"]) ? "<center>Deseja continuar com a ativação assim mesmo e gerar<br>cobrança avulsa referente ao período?</center><br>" : "", $this->conteudo);
					$this->conteudo = str_replace("[comentario]", $_POST["comentario"], $this->conteudo);
					if ($erros["ativavel"]) {
						$this->conteudo = str_replace("[formato]", $this->geraSelect($this->buscaFormatos("1", true), $this->dados_item["cobranca_formato_id"], true, "formato", "100%"), $this->conteudo);
						$this->conteudo = str_replace("[dia_vencimento]", $this->dados_item["dia_vencimento"], $this->conteudo);
						$this->conteudo = str_replace("[vencimento_maximo]", COBRANCA_AVULSA_VENCIMENTO_MAXIMO, $this->conteudo);
						$this->conteudo = str_replace("[vencimento_inicio]", $this->dados_item["competencia_inicio"], $this->conteudo);
					} else {
						$this->conteudo = preg_replace('/<tbody id="grupo_formato">[^$]*<\/tbody>\s+/', '', $this->conteudo);
					}
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
				
				} else {
					
					$botoes = array();
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
					
					$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.ativar.inc.php");

					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
				
				}
				
			} else {
				
				$inicio = "'" . substr($this->dados_item["competencia_inicio"], 3, 4) . "-" . substr($this->dados_item["competencia_inicio"], 0, 2) . "-" . "01'";
				$termino = ($this->dados_item["competencia_termino"]) ? "'" . substr($this->dados_item["competencia_termino"], 3, 4) . substr($this->dados_item["competencia_termino"], 0, 2) . "01'" : "NULL";
				
				if ($this->dados_item["condominio_id"]) {
					
					$this->bancodedados->query("
						UPDATE
							tab_cobrancas_lotes
							INNER JOIN tab_cobrancas ON (tab_cobrancas_lotes.col_id = tab_cobrancas.col_id)
							INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
						SET
							col_sincronizado = 0
						WHERE
							(DATE_SUB(tab_cobrancas_lotes.col_vencimento, INTERVAL 1 MONTH) >= $inicio AND ($termino IS NULL OR DATE_SUB(tab_cobrancas_lotes.col_vencimento, INTERVAL 1 MONTH) <= $termino)) AND
							tab_clientes.con_id = {$this->dados_item["condominio_id"]} AND
							tab_cobrancas_lotes.cla_id < 4");
		
				} else {
		
					$this->bancodedados->query("
						UPDATE
							tab_cobrancas
							INNER JOIN tab_cobrancas_lotes ON (tab_cobrancas.col_id = tab_cobrancas_lotes.col_id)
						SET
							col_sincronizado = 0
						WHERE
							(DATE_SUB(tab_cobrancas_lotes.col_vencimento, INTERVAL 1 MONTH) >= $inicio AND ($termino IS NULL OR DATE_SUB(tab_cobrancas_lotes.col_vencimento, INTERVAL 1 MONTH) <= $termino)) AND
							tab_cobrancas.cli_id = {$this->dados_item["cliente_id"]} AND
							tab_cobrancas_lotes.cla_id < 4");
					
				}
				
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 5 WHERE ajf_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 5 WHERE ajf_pai = {$_GET["i"]}");
				
				if ($this->dados_item["status_id"] == 1 || $this->dados_item["status_id"] == 3) {
					$this->registraComentario($_GET["i"], "<b>Ação:</b> Auto-aprovado");
				}
				
				$comentarios = ($_POST["comentario"]) ? "<br><b>Comentário</b>: " . trim(addslashes($_POST["comentario"])) : "";
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Ativado" . $comentarios, $_SESSION["usuario"]);
				
				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);
				
				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->registraComentario($ajuste_filho, "<b>Ação:</b> Ativado" . $comentarios, $_SESSION["usuario"]);
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				if ($erros) {
					
					$data_vencimento = substr($_POST["data_vencimento"], 6, 4) . "-" . substr($_POST["data_vencimento"], 3, 2) . "-" . substr($_POST["data_vencimento"], 0, 2);
					$vencimento_inicio = $this->buscaVencimentoInicio($this->dados_item["competencia_inicio"]);
					$vencimento_termino = $this->buscaVencimentoTermino($this->dados_item);
					
					$geracao = controle::instanciaObjeto("cobrancas_geracao", $this->bancodedados);
					
					$cobranca_id = $geracao->geraCobrancaAvulsa($this->dados_item["cliente_id"], $_POST["formato"], $vencimento_inicio, $vencimento_termino, $data_vencimento, $this->dados_item["dia_vencimento"]);
					
					if ($cobranca_id && ! in_array($cobranca_id, array ("erro_codigo"))) {
						header("Location: ./?m={$this->dados_modulo["nome"]}&a=enviar_cobranca&i={$_GET["i"]}");
						exit();
					}
					
				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	#######################
	### Enviar Cobrança ###
	#######################
	
	function enviar_cobranca() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		$cobranca_id = $this->buscaCobranca($this->dados_item["id"], true);
		
		if ($this->dados_item && $cobranca_id) {
			
			$dados_cobranca = $this->buscaDadosCobranca($cobranca_id);
			
			if (! $_POST) {
				
				$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);
				
				$lista_emails = array();
				foreach ($dados_cliente["emails"] as $email) {
					$lista_emails[] = $email["contato_email"];
				}
				$lista_emails = implode("; ", $lista_emails);

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.enviar_cobranca.inc.php");
				$this->conteudo = str_replace('[emails]', $lista_emails, $this->conteudo);
				$this->conteudo = str_replace('[cliente_id]', $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_nome]', $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace('[cobranca_codigo]', $dados_cobranca["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[cobranca_formato]', $dados_cobranca["formato"], $this->conteudo);
				$this->conteudo = str_replace('[cobranca_vencimento]', $dados_cobranca["vencimento"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=enviar_cobranca&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				sort($emails = array_unique(preg_split("/ *[ ,;] */", strtolower($_POST["emails"]))));
				
				$this->enviaMensagemCobranca($this->dados_item["cliente_id"], $dados_cobranca, $emails, $_POST["comentario"], 2, null, $_SESSION["usuario"]);

				$this->bancodedados->query("UPDATE tab_cobrancas SET coa_enviada = 1 WHERE coa_id = $cobranca_id");

				$_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário</b>: " . trim(addslashes($_POST["comentario"])) : "";
				
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Enviar Cobrança<br><b>Código da Cobrança</b>: {$dados_cobranca["codigo"]}<br><b>E-mail(s)</b>: " . implode(", ", $emails) . $_POST["comentario"], $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	function buscaCobranca($ajuste_id, $enviavel = false) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_parametros AS parametros
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
			WHERE
				ajf_id = $ajuste_id".($enviavel ? " AND
				coa_enviada = 0 AND
				col_id IS NULL AND
				cot_id = 1" : "")."
			GROUP BY
				id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$parametros = unserialize($dados["parametros"]);
			if ($parametros["boleto"]) {
				return $dados["id"];
			}
		}
		
	}
	
	function buscaDadosCobranca($cobranca_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id,
				coa_codigo AS codigo,
				DATE_FORMAT(coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				cof_nome AS formato,
				coa_valor_original AS valor_original,
				coa_valor_bonificacao AS valor_bonificacao,
				coa_parametros AS parametros,
				tab_clientes.clo_id AS origem_id
			FROM
				tab_clientes
				INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				coa_id = $cobranca_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
		
	}
	
	function enviaMensagemCobranca($cliente_id, $dados_cobranca, $emails = null, $comentario = null, $mensagem_lote_id = 2, $data_hora_envio = null, $usuario_id = null) {
		
		$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
		$cobrancas->enviaMensagemCobranca($cliente_id, $dados_cobranca, $emails, $comentario, $mensagem_lote_id, $data_hora_envio, $usuario_id);
		
	}
	
	###############
	### Remover ###
	###############
	
	function remover() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehRemovivel()) {
			
			if (! $_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("ajustes_financeiros.remover.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
			
			} else {
				
				$inicio = "'" . substr($this->dados_item["competencia_inicio"], 3, 4) . "-" . substr($this->dados_item["competencia_inicio"], 0, 2) . "-01'";
				$termino = ($this->dados_item["competencia_termino"]) ? "'" . substr($this->dados_item["competencia_termino"], 3, 4) . substr($this->dados_item["competencia_termino"], 0, 2) . "01'" : "NULL";
				
				if ($this->dados_item["condominio_id"]) {
					$this->bancodedados->query("UPDATE tab_cobrancas_lotes INNER JOIN tab_cobrancas ON (tab_cobrancas_lotes.col_id = tab_cobrancas.col_id) INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id) SET col_sincronizado = 0 WHERE tab_clientes.con_id = {$this->dados_item["condominio_id"]} AND (tab_cobrancas_lotes.col_vencimento >= $inicio AND ($termino IS NULL OR tab_cobrancas_lotes.col_vencimento <= $termino)) AND (tab_cobrancas_lotes.cla_id < 4)");
				} else {
					$this->bancodedados->query("UPDATE tab_cobrancas INNER JOIN tab_cobrancas_lotes ON (tab_cobrancas.col_id = tab_cobrancas_lotes.col_id) SET col_sincronizado = 0 WHERE tab_cobrancas.cli_id = {$this->dados_item["cliente_id"]} AND (tab_cobrancas_lotes.col_vencimento >= $inicio AND ($termino IS NULL OR tab_cobrancas_lotes.col_vencimento <= $termino)) AND (tab_cobrancas_lotes.cla_id < 4)");
				}
				
				$this->bancodedados->query("DELETE FROM tab_cobrancas_detalhes WHERE ajf_id = {$_GET["i"]}");
				
				$ajustes_filhos = $this->buscaAjustesFilhos($_GET["i"]);
				
				foreach ($ajustes_filhos as $ajuste_filho) {
					$this->bancodedados->query("DELETE FROM tab_ajustes_financeiros_comentarios WHERE ajf_id = $ajuste_filho");
					$this->bancodedados->query("DELETE FROM tab_ajustes_financeiros WHERE ajf_id = $ajuste_filho");
				}
				
				$this->bancodedados->query("DELETE FROM tab_ajustes_financeiros_comentarios WHERE ajf_id = {$_GET["i"]}");
				$this->bancodedados->query("DELETE FROM tab_ajustes_financeiros WHERE ajf_id = {$_GET["i"]}");
				
				$this->fechar();
				
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
		
		$itens = array();
		
		// Cliente
		$itens[] = array (
			"tipo" => "input",
			"nome" => "cliente",
			"descricao" => "Cliente",
			"valor" => "",
			"where" => "(tab_clientes.cli_nome LIKE '%[valor]%')"
		);
		
		// Categoria
		$categorias = $this->buscaCategorias();
		$select_categorias = array();
		foreach ($categorias as $id => $nome) {
			$select_categorias[] = array (
				"id" => $id,
				"nome" => $nome
			);
		}
		$itens[] = array (
			"tipo" => "select",
			"nome" => "categoria",
			"descricao" => "Categoria",
			"valor" => $select_categorias,
			"where" => "(tab_ajustes_financeiros.afc_id = [valor])"
		);
		
		// Tipo
		$tipos = $this->buscaTipos();
		$select_tipos = array();
		foreach ($tipos as $id => $nome) {
			$select_tipos[] = array (
				"id" => $id,
				"nome" => $nome
			);
		}
		$itens[] = array (
			"tipo" => "select",
			"nome" => "tipo",
			"descricao" => "Tipo",
			"valor" => $select_tipos,
			"where" => "(tab_ajustes_financeiros.aft_id = [valor])"
		);
		
		// Competência Início
		$itens[] = array (
			"tipo" => "input",
			"nome" => "competencia_inicio",
			"descricao" => "Competência Início",
			"valor" => "",
			"where" => "(tab_ajustes_financeiros.ajf_competencia_inicio = '" . substr($valores["competencia_inicio"], 3, 4) . "-" . substr($valores["competencia_inicio"], 0, 2) . "-01')"
		);
		
		// Competência Término
		$itens[] = array (
			"tipo" => "input",
			"nome" => "competencia_termino",
			"descricao" => "Competência Término",
			"valor" => "",
			"where" => "(tab_ajustes_financeiros.ajf_competencia_termino = '" . substr($valores["competencia_termino"], 3, 4) . "-" . substr($valores["competencia_termino"], 0, 2) . "-01')"
		);
		
		// Status
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array (
				"id" => $id,
				"nome" => $nome
			);
		}
		$select_status[] = array (
			"id" => "-1",
			"nome" => "Análise",
			"where" => "(tab_ajustes_financeiros.afs_id IN (1, 2, 3, 4))"
		);
		$select_status[] = array (
			"id" => "-2",
			"nome" => "Inativo",
			"where" => "(tab_ajustes_financeiros.afs_id IN (6, 7))"
		);
		$itens[] = array (
			"tipo" => "select",
			"nome" => "status",
			"descricao" => "Status",
			"valor" => $select_status,
			"where" => "(tab_ajustes_financeiros.afs_id = [valor])",
			"disabled" => ($this->permissoes[$this->dados_modulo["nome"]]["listar_todos"]) ? false : true
		);
		
		if (! $this->permissoes[$this->dados_modulo["nome"]]["listar_todos"]) $valores["status"] = - 1;
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}
	
	function buscaItens($filtro, $pagina) {
		
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_ajustes_financeiros.ajf_id AS id,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_inicio,'%m/%Y') AS competencia_inicio,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_termino,'%m/%Y') AS competencia_termino,
				tab_ajustes_financeiros.ajf_valor AS valor,
				tab_ajustes_financeiros.ajf_valor_tipo AS valor_tipo,
				tab_ajustes_financeiros_categorias.afc_nome AS categoria,
				tab_ajustes_financeiros_tipos.aft_nome AS tipo,
				tab_ajustes_financeiros_status.afs_nome AS status,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_clientes ON (tab_ajustes_financeiros.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
				INNER JOIN tab_ajustes_financeiros_tipos ON (tab_ajustes_financeiros.aft_id = tab_ajustes_financeiros_tipos.aft_id)
				INNER JOIN tab_ajustes_financeiros_status ON (tab_ajustes_financeiros.afs_id = tab_ajustes_financeiros_status.afs_id)
			WHERE
				$filtro->parametros_sql" . ($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "") . "
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);
		$itens = array ();
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes"]["ver_df"]) {
				$dados["link"] = "?m=clientes&a=ver_df&i={$dados["cliente_id"]}";
			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$dados["data"] = $dados["competencia_inicio"] . " / " . (($dados["competencia_termino"]) ? $dados["competencia_termino"] : "N/D");
			$dados["valor"] = $this->formataValorListagem($dados["valor"], $dados["valor_tipo"]);
			
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
			"cliente_nome" => "Cliente",
			"categoria" => "Categoria",
			"data" => "Competência",
			"tipo" => "Tipo",
			"valor" => "Valor",
			"status" => "Status"
		);
		
		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array ("20"), null, array ("right", "left"));
		
	}
	
	###########
	### RDS ###
	###########
	
	function buscaAjustesFinanceiros($cliente_id = null, $condominio_id = null) {
		
		$ajustes_financeiros = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_ajustes_financeiros.ajf_id AS id,
				tab_ajustes_financeiros_categorias.afc_nome AS categoria,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_inicio,'%m/%Y') AS competencia_inicio,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_termino,'%m/%Y') AS competencia_termino,
				tab_ajustes_financeiros_tipos.aft_nome AS tipo,
				tab_ajustes_financeiros.ajf_valor AS valor,
				tab_ajustes_financeiros.ajf_parcelas AS parcelas,
				tab_ajustes_financeiros.ajf_valor_total AS valor_total,
				tab_ajustes_financeiros.ajf_valor_tipo AS valor_tipo,
				tab_ajustes_financeiros_status.afs_nome AS status
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
				INNER JOIN tab_ajustes_financeiros_tipos ON (tab_ajustes_financeiros.aft_id = tab_ajustes_financeiros_tipos.aft_id)
				INNER JOIN tab_ajustes_financeiros_status ON (tab_ajustes_financeiros.afs_id = tab_ajustes_financeiros_status.afs_id)
			WHERE
				tab_ajustes_financeiros." . ($cliente_id ? "cli_id = $cliente_id" : "con_id = $condominio_id") . "
			ORDER BY
				ajf_competencia_inicio,
				categoria");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["ajustes_financeiros"]["ver"]) {
				$dados["link"] = "javascript:verAjusteFinanceiro({$dados["id"]});";
			}
			$dados["valor"] = $this->formataValorListagem($dados["valor"], $dados["valor_tipo"]);
			if ($dados["valor_total"]) {
				$dados["valor_total"] = $this->formataValorListagem($dados["valor_total"], $dados["valor_tipo"]);
			}
				
			$ajustes_financeiros[] = $dados;
			
		}
		
		return $ajustes_financeiros;
		
	}
	
	function geraListagemAjustesFinanceiros($itens) {
		
		$cabecalho = array (
			"categoria" => "Categoria",
			"tipo" => "Tipo",
			"competencia_inicio" => "Início",
			"competencia_termino" => "Término",
			"parcelas" => "Parcelas",
			"valor" => "Valor",
			"valor_total" => "Valor Total",
			"status" => "Status"
		);
		
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array ("left", "center", "center", "center", "right", "right", "right", "left"));
		
		return $listagem->conteudo;
		
	}
	
}
?>
