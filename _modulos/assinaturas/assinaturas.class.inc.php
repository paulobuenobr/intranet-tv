<?

require_once("_global/modulo.class.inc.php");

class assinaturas extends modulo {

	var $periodicidades = array(
		 "1" => "Mensal",
		 "2" => "Bimestral",
		 "3" => "Trimestral",
		 "6" => "Semestral",
		"12" => "Anual"
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
				con_id AS condominio_id,
				IF(con_id IS NULL, 'sc', 'cc') AS ccsc,
				IF(clt_id = 1, 'pf', 'pj') AS pfpj,
				clo_id AS origem_id
			FROM
				tab_clientes
			WHERE
				cli_id = $cliente_id");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_pai AS assinatura,
				tab_assinaturas.ass_instalacao AS instalacao,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.ass_migrada AS migrada,
				tab_assinaturas.ass_vigencia AS vigencia,
				tab_assinaturas.ass_multa AS multa,
				DATE_FORMAT(tab_assinaturas.ass_data_cadastro, '%d/%m/%Y') AS data_cadastro,
				DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%d/%m/%Y') AS data_fechamento,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%d/%m/%Y') AS data_cancelamento,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_planos.plt_id AS tipo_id,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome,
				tab_planos_sistemas.pli_id AS sistema_id,
				tab_planos_sistemas.pli_nome AS sistema_nome,
				tab_assinaturas_status.ast_id AS status_id,
				IF(tab_assinaturas.ass_migrada AND tab_assinaturas.ast_id = 1, CONCAT(tab_assinaturas_status.ast_nome, ' / ', 'Migração'), tab_assinaturas_status.ast_nome) AS status_nome,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				IF(tab_clientes.clt_id = 1, 1, 0) AS pfpj,
				IF(tab_clientes.cob_id IS NOT NULL, 1, 0) AS ccsc,
				tab_clientes.con_id AS condominio_id,
				tab_clientes.clo_id AS origem_id,
				tab_usuarios.usu_nome AS vendedor_nome,
				tab_vendas.ven_id AS venda_id,
				tab_vendas.ven_pontuacao AS vendedor_pontuacao,
				(SELECT COUNT(*) FROM tab_assinaturas filhas WHERE filhas.ass_pai = tab_assinaturas.ass_id) AS filhas
			FROM
				tab_clientes
				INNER JOIN tab_assinaturas ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas_status.ast_id = tab_assinaturas.ast_id)
				LEFT OUTER JOIN tab_vendas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
				LEFT OUTER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_vendas.usu_id)
				INNER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_assinaturas.pli_id)
			WHERE
				tab_assinaturas.ass_id = '$assinatura_id'".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["contrato"] = $this->buscaContratoAssinatura($assinatura_id);
			$dados["vinculos"] = $this->buscaAssinaturasVinculos($assinatura_id);
			$dados["vinculos_pai"] = $this->buscaAssinaturasVinculosPai($assinatura_id);
			$dados["assinatura"] = $this->buscaDadosAssinatura($dados["assinatura"]);

			return $dados;

		}

	}

	function buscaAssinaturasVinculos($assinatura_id) {

		$vinculos = array();

		$query = $this->bancodedados->query("
			SELECT
				asv_id AS id,
				ass_filha AS assinatura_id,
				pvt_id AS tipo_id
			FROM
				tab_assinaturas_vinculos
			WHERE
				ass_id = $assinatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["vinculos_pai"] = $this->buscaAssinaturasVinculosPai($dados["assinatura_id"]);
			$dados["contrato"] = $this->buscaContratoAssinatura($dados["assinatura_id"]);

			$vinculos[] = $dados;

		}

		return $vinculos;

	}

	function buscaAssinaturasVinculosPai($assinatura_id) {

		$vinculos = array();

		$query = $this->bancodedados->query("
			SELECT
				asv_id AS id,
				ass_id AS assinatura_id,
				pvt_id AS tipo_id
			FROM
				tab_assinaturas_vinculos
			WHERE
				ass_filha = $assinatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$vinculos[] = $dados;
		}

		return $vinculos;

	}

	function buscaAssinaturasFilho($assinatura_id) {

		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id
			FROM
				tab_assinaturas
			WHERE
				ass_pai = $assinatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $dados;
		}

		return $assinaturas;
	}

	function buscaContratoAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS id,
				tab_contratos.cor_codigo AS codigo,
				tab_contratos.crs_id AS status_id
			FROM
				rel_ass_cor_estacontido
				INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
			WHERE
				ass_id = $assinatura_id
			ORDER BY
				cor_pai
			LIMIT
				1");

		return $this->bancodedados->resultadoArray($query);

	}

	function planoTemDominio($plano_id) {

		$query = $this->bancodedados->query("
			SELECT
				pla_parametros
			FROM
				tab_planos
			WHERE
				pla_id = $plano_id AND
				pls_id = 11");

		if (($parametros = $this->bancodedados->resultado($query)) != false) {
			$parametros = unserialize($parametros);
			return $parametros["hospedagens_quantidade"];
		}

	}

	function buscaDadosPlano($plano_id) {

		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				pla_instalacao AS instalacao,
				pla_mensalidade AS mensalidade,
				pls_id AS servico_id,
				pla_parametros AS parametros,
				sac_id AS categoria_ajuste_id,
				plt_id AS tipo_id,
				pli_id AS sistema_id
			FROM
				tab_planos
			WHERE
				pla_id = $plano_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$parametros = unserialize($dados["parametros"]);

			$dados["desconto_condominio"] = $parametros["desconto_condominio"];
			$dados["vinculos"] = $this->buscaPlanosVinculos($plano_id);

			return $dados;

		}

	}

	function buscaDadosServico($servico_id) {

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS id,
				pls_nome AS nome,
				pls_parametros AS parametros
			FROM
				tab_planos_servicos
			WHERE
				pls_id = $servico_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

	}

	function buscaSistemas($cliente_id = null, $sistema_id = null) {

		if ($cliente_id) {

			$query = $this->bancodedados->query("
				SELECT
					tab_planos_sistemas.pli_id AS id,
					tab_planos_sistemas.pli_nome AS nome
				FROM
					tab_assinaturas
					INNER JOIN tab_planos_sistemas ON (tab_assinaturas.pli_id = tab_planos_sistemas.pli_id)
				WHERE
					tab_assinaturas.ast_id IN (1, 2, 5) AND
					tab_assinaturas.cli_id = $cliente_id
				ORDER BY
					pli_nome
				LIMIT
					0, 1");

			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				return array($dados["id"] => $dados["nome"]);
			}

		}

		$sistemas = array();

		$query = $this->bancodedados->query("
			SELECT
				pli_id AS id,
				pli_nome AS nome
			FROM
				tab_planos_sistemas".($sistema_id ? "
			WHERE
				pli_id = $sistema_id" : "")."
			ORDER BY
				pli_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$sistemas[$dados["id"]] = $dados["nome"];
		}

		return $sistemas;

	}

	function buscaPlanosVinculos($plano_id) {

		$vinculos = array();

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS servico_id,
				pla_filho AS plano_id,
				pva_id AS aplicacao_id,
				pvt_id AS tipo_id
			FROM
				tab_planos_vinculos
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_planos_vinculos.pla_filho)
			WHERE
				tab_planos_vinculos.pla_id = $plano_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$vinculos[] = $dados;
		}

		return $vinculos;

	}

	function buscaDataCancelamento($cliente_id, $servico_id) {

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(MAX(cor_data_cancelamento), '%Y%m%d')
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				pls_id = $servico_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaDadosItem($assinatura_id) {

		$this->dados_item = $this->buscaDadosAssinatura($assinatura_id);

	}

	function formataDescontoValor($valor) {

		if ($valor <= 1 && $valor > 0) {
			return intval($valor * 100)."%";
		} else {
			return $this->formataDinheiro(intval($valor), "R$", true);
		}

	}

	function formataDescontoValidade($validade, $data_termino) {

		if ($data_termino) {
			return $data_termino;
		} else {
			return $validade > 0 ? ($validade == 1 ? "$validade mês" : "$validade meses") : "Indeterminada";
		}

	}

	function possuiContrato() {

		return $this->dados_item["contrato"] && !in_array($this->dados_item["contrato"]["status_id"], array(11, 12, 13, 14));

	}

	function ehRemovivel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["remover"]) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		foreach ($this->dados_item["vinculos_pai"] as $vinculo) {
			if ($vinculo["tipo_id"] != 1) return false;
		}
		if (!$this->dados_item["contrato"]) return true;
		if (!in_array($this->dados_item["contrato"]["status_id"], array(11, 12, 13, 14))) return false;
		if (!$this->dados_item["assinatura"]) return false;
		if ($this->buscaQuantidadeAssinaturasProspectContrato($this->dados_item["contrato"]["id"]) == 1) return false;

		return true;

	}

	function ehEditavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["editar"]) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		if ($this->dados_item["vinculos"]) return false;
		foreach ($this->dados_item["vinculos_pai"] as $vinculo) {
			if ($vinculo["tipo_id"] != 1) return false;
		}
		if ($this->dados_item["status_id"] != 1) return false;
		if ($this->dados_item["migrada"]) return false;
		if ($this->possuiAssinaturasAdicionais($this->dados_item["id"])) return false;

		return true;

	}

	function buscaQuantidadeAssinaturasProspectContrato($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_assinaturas.ass_id
			FROM
				tab_assinaturas
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				tab_assinaturas.ast_id = 1 AND
				rel_ass_cor_estacontido.cor_id = $contrato_id");

		return $this->bancodedados->linhas($query);

	}

	function buscaEmailsAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_emails.cle_id AS id,
				tab_clientes_emails.cle_usuario AS login,
				tab_dominios.dom_dominio AS dominio
			FROM
				tab_clientes_emails
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
			WHERE
				tab_clientes_emails.ass_id = $assinatura_id");

		return $this->bancodedados->resultadoArrayTotal($query);

	}

	function buscaPlanos() {

		$planos = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_planos.pla_id AS id,
				CONCAT(tab_planos_servicos.pls_nome, ' » ', tab_planos.pla_nome) AS nome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[$dados["id"]] = $dados["nome"];
		}

		return $planos;

	}

	function buscaStatus() {

		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				ast_id AS id,
				ast_nome AS nome
			FROM
				tab_assinaturas_status
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}

		return $status;

	}

	function buscaDadosCategoriaAjuste($categoria_id) {

		$query = $this->bancodedados->query("
			SELECT
				sac_regras as regras,
				sac_editavel as editavel
			FROM
				tab_servicos_ajustes_categorias
			WHERE
				sac_id = $categoria_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["regras"] = unserialize($dados["regras"]);
			return $dados;
		}

	}

	function registraComentarioAjuste($ajuste_id, $conteudo, $responsavel_id = 'NULL') {

		$responsavel_id = $responsavel_id ? $responsavel_id : 'NULL';

		$this->bancodedados->query("
			INSERT INTO
				tab_servicos_ajustes_comentarios
			SET
				saj_id = $ajuste_id,
				sam_datahora = NOW(),
				sam_conteudo = '$conteudo',
				usu_id = $responsavel_id");

	}

	function buscaTotalDescontosCondominio($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				SUM(dec_mensalidade)
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id = $assinatura_id AND
				dec_validade IS NULL AND
				dec_data_termino IS NULL AND
				dec_mensalidade > 1 AND
				dec_pai IS NOT NULL");

		return $this->bancodedados->resultado($query);

	}

	function buscaDescontoMaiorValidade($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				dec_id AS id,
				des_parametros AS parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
				ass_id = $assinatura_id AND
				(dec_validade IS NOT NULL OR dec_data_termino IS NOT NULL) AND
				dec_mensalidade > 1 AND
				dec_pai IS NULL
			ORDER BY
				IF(dec_validade IS NOT NULL, DATE_ADD(CURDATE(), INTERVAL dec_validade MONTH), dec_data_termino) DESC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($dados["parametros"]["mensalidade_fixa"]) {
				return $dados["id"];
			}
		}

	}

	function restauraDescontosConcessoes($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				dec_id AS id,
				des_parametros AS parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
				ass_id = $assinatura_id AND
				dec_pai IS NULL
			ORDER BY
				dec_validade DESC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);

			if ($dados["parametros"]["mensalidade_fixa"] || $dados["parametros"]["instalacao_fixa"] || $dados["parametros"]["validade_fixa"]) {

				$mensalidade = $dados["parametros"]["mensalidade"] ? $dados["parametros"]["mensalidade"] : "NULL";
				$instalacao = $dados["parametros"]["instalacao"] ? $dados["parametros"]["instalacao"] : "NULL";

				if ($dados["parametros"]["validade_tipo"] && $dados["parametros"]["validade_tipo"] == 2) {
					$validade = "NULL";
					$data_termino = $dados["parametros"]["validade"] ? $this->formataData($dados["parametros"]["validade"]) : "NULL";
				} else {
					$validade = $dados["parametros"]["validade"] ? $dados["parametros"]["validade"] : "NULL";
					$data_termino = "NULL";
				}

				$this->bancodedados->query("
					UPDATE
						tab_descontos_concessoes
					SET
						dec_mensalidade = $mensalidade,
						dec_instalacao = $instalacao,
						dec_data_termino = $data_termino,
						dec_validade = $validade
					WHERE
						dec_id = {$dados["id"]}");

			}

		}

	}

	function recalculaDescontosConcessoes($assinatura_id) {

		if (($total_descontos = $this->buscaTotalDescontosCondominio($assinatura_id)) != false &&
				($desconto_maior_validade = $this->buscaDescontoMaiorValidade($assinatura_id)) != false) {

			$this->bancodedados->query("
				UPDATE
					tab_descontos_concessoes
				SET
					dec_mensalidade = dec_mensalidade - $total_descontos
				WHERE
					dec_id = $desconto_maior_validade");

		}
	}

	function possuiAssinaturasAdicionais($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(ass_id)
			FROM
				tab_assinaturas
			WHERE
				ass_pai = $assinatura_id");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaClientesAssinaturas($cliente_id, $lista_status) {

		$assinaturas = array();
		$valores = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_instalacao AS instalacao,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.ass_parametros AS parametros,
				tab_planos_servicos.pls_nome AS servico,
				tab_planos_sistemas.pli_id AS sistema_id,
				tab_planos_sistemas.pli_nome AS sistema_nome,
				tab_planos.pla_id AS plano_id,
				IF(tab_planos.plt_id = 1, tab_planos.pla_nome, CONCAT('&nbsp;&raquo; ', tab_planos.pla_nome)) AS plano_nome,
				tab_assinaturas_status.ast_id AS status_id,
				tab_assinaturas_status.ast_nome AS status_nome,
				IF(tab_assinaturas.ass_migrada AND tab_assinaturas.ast_id = 1, CONCAT(tab_assinaturas_status.ast_nome, ' / ', 'Migração'), tab_assinaturas_status.ast_nome) AS status_nome,
				IF(tab_assinaturas.ass_pai, tab_assinaturas.ass_pai, tab_assinaturas.ass_id) AS ordem
			FROM
				tab_assinaturas_status
				INNER JOIN tab_assinaturas ON (tab_assinaturas_status.ast_id = tab_assinaturas.ast_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_assinaturas.pli_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_assinaturas.ast_id IN ($lista_status)
			ORDER BY
				status_nome ASC,
				servico ASC,
				ordem DESC,
				tab_planos.plt_id ASC,
				plano_nome ASC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["sistema_nome"] .= "<br>(".$this->buscaPeriodicidade($dados["parametros"]["periodicidade"]).")";
			$dados["plano"] = '<div class="txtAzulEscuro11" onMouseOver="hintShow(\''.$this->buscaAssinaturaStatusCompleto($dados).'\')" onMouseOut="hintHide()">'.$dados["plano_nome"].'</div>';
			$dados["status_nome"] = str_replace(" (", "<br>(", $dados["status_nome"]);

			// Calcula Totais
			$status_id = (in_array($dados["status_id"], array(2, 5)) ? 2 : $dados["status_id"]);

			$valores[$status_id]["instalacao"] += $dados["instalacao"];
			$valores[$status_id]["mensalidade"] += $dados["mensalidade"];

			// Busca Descontos
			if (($descontos = $this->buscaDescontosAssinatura($dados)) != false) {
				$valores[$status_id]["desconto"]["instalacao_sem_validade"] += $descontos["instalacao_sem_validade"];
				$valores[$status_id]["desconto"]["instalacao_com_validade"] += $descontos["instalacao_com_validade"];
				$valores[$status_id]["desconto"]["mensalidade_sem_validade"] += $descontos["mensalidade_sem_validade"];
				$valores[$status_id]["desconto"]["mensalidade_com_validade"] += $descontos["mensalidade_com_validade"];
				$dados["instalacao"] = $this->formataDinheiro($dados["instalacao"], "R$", true).(($descontos["instalacao"]) ? "<br>(".$this->formataDinheiro($dados["instalacao"] - $descontos["instalacao"], "R$", true).")" : "");
				$dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"], "R$", true).(($descontos["mensalidade"]) ? "<br>(".$this->formataDinheiro($dados["mensalidade"] - $descontos["mensalidade"], "R$", true).")" : "");
				$dados["descontos"] = implode(", ", $descontos["texto_com_mouseover"]);
			} else {
				$dados["instalacao"] = $this->formataDinheiro($dados["instalacao"], "R$", true);
				$dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"], "R$", true);
				$dados["descontos"] = "";
			}

			if ($this->permissoes["assinaturas"]["ver"]) $dados["link"] = "javascript:verAssinatura({$dados["id"]})";
			if (in_array($dados["status_id"], array(2, 5))) $dados["cor_linha"] = "#D7D7D7";

			$assinaturas[] = $dados;

		}

		// Assinaturas Prospect
		if ($valores[1]) {

		    $valores_prospects = array();

			$valores_prospects["status"] = "<b>Prospects</b>";
			$valores_prospects["status_nome"] = "<b>Prospects</b>";

			$valores_prospects["instalacao"] = "<b>".$this->formataDinheiro($valores[1]["instalacao"], "R$", true).(($valores[1]["desconto"]["instalacao_sem_validade"] + $valores[1]["desconto"]["instalacao_com_validade"]) ? "<br>(".$this->formataDinheiro($valores[1]["instalacao"] - $valores[1]["desconto"]["instalacao_sem_validade"] - $valores[1]["desconto"]["instalacao_com_validade"], "R$", true).")" : "")."</b>";
			$valores_prospects_instalacao_mouseover = "Detalhamento da Adesão (Prospects):<br>";
			if ($valores[1]["instalacao"]) $valores_prospects_instalacao_mouseover .= "&nbsp;&raquo; Assinatura(s): ".$this->formataDinheiro($valores[1]["instalacao"], "R$", true)."<br>";
			if ($valores[1]["desconto"]["instalacao_sem_validade"]) $valores_prospects_instalacao_mouseover .= "&nbsp;&raquo; Desconto(s) sem validade: ".$this->formataDinheiro($valores[1]["desconto"]["instalacao_sem_validade"], "R$", true)."<br>";
			if ($valores[1]["desconto"]["instalacao_com_validade"]) $valores_prospects_instalacao_mouseover .= "&nbsp;&raquo; Desconto(s) com validade: ".$this->formataDinheiro($valores[1]["desconto"]["instalacao_com_validade"], "R$", true)."<br>";
			if ($valores[1]["desconto"]["instalacao_sem_validade"] - $valores[1]["desconto"]["instalacao_com_validade"]) $valores_prospects_instalacao_mouseover .= "&nbsp;&raquo; Total: ".$this->formataDinheiro($valores[1]["instalacao"] - $valores[1]["desconto"]["instalacao_sem_validade"] - $valores[1]["desconto"]["instalacao_com_validade"], "R$", true)."<br>";
			$valores_prospects["instalacao"] = '<div class="txtAzulEscuro11" style="cursor:pointer" onMouseOver="hintShow(\''.$valores_prospects_instalacao_mouseover.'\');" onMouseOut="hintHide();">'.$valores_prospects["instalacao"].'</div>';

			$valores_prospects["mensalidade"] = "<b>".$this->formataDinheiro($valores[1]["mensalidade"], "R$", true).(($valores[1]["desconto"]["mensalidade_sem_validade"] + $valores[1]["desconto"]["mensalidade_com_validade"]) ? "<br>(".$this->formataDinheiro($valores[1]["mensalidade"] - $valores[1]["desconto"]["mensalidade_sem_validade"] - $valores[1]["desconto"]["mensalidade_com_validade"], "R$", true).")" : "")."</b>";
			$valores_prospects_mensalidade_mouseover = "Detalhamento da Mensalidade (Prospects):<br>";
			if ($valores[1]["mensalidade"]) $valores_prospects_mensalidade_mouseover .= "&nbsp;&raquo; Assinatura(s): ".$this->formataDinheiro($valores[1]["mensalidade"], "R$", true)."<br>";
			if ($valores[1]["desconto"]["mensalidade_sem_validade"]) $valores_prospects_mensalidade_mouseover .= "&nbsp;&raquo; Desconto(s) sem validade: ".$this->formataDinheiro($valores[1]["desconto"]["mensalidade_sem_validade"], "R$", true)."<br>";
			if ($valores[1]["desconto"]["mensalidade_com_validade"]) $valores_prospects_mensalidade_mouseover .= "&nbsp;&raquo; Desconto(s) com validade: ".$this->formataDinheiro($valores[1]["desconto"]["mensalidade_com_validade"], "R$", true)."<br>";
			if ($valores[1]["mensalidade"] - $valores[1]["desconto"]["mensalidade_sem_validade"] - $valores[1]["desconto"]["mensalidade_com_validade"]) $valores_prospects_mensalidade_mouseover .= "&nbsp;&raquo; Total: ".$this->formataDinheiro($valores[1]["mensalidade"] - $valores[1]["desconto"]["mensalidade_sem_validade"] - $valores[1]["desconto"]["mensalidade_com_validade"], "R$", true)."<br>";
			$valores_prospects["mensalidade"] = '<div class="txtAzulEscuro11" style="cursor:pointer" onMouseOver="hintShow(\''.$valores_prospects_mensalidade_mouseover.'\');" onMouseOut="hintHide();">'.$valores_prospects["mensalidade"].'</div>';

			$valores_prospects["descontos"] = "";

			$assinaturas[] = $valores_prospects;

		}

		// Assinaturas Ativas
		if ($valores[2]) {

		    $valores_ativos = array();

			$valores[2]["ajuste_financeiro"] = $this->buscaAjustesFinanceirosCliente($cliente_id, $valores[2]["mensalidade"]);
			$valores_ativos["status"] = "<b>Ativas</b>";
			$valores_ativos["status_nome"] = "<b>Ativas</b>";

			$valor_ativo_instalacao = $valores[2]["instalacao"] - $valores[2]["desconto"]["instalacao_sem_validade"] - $valores[2]["desconto"]["instalacao_com_validade"];
			$valor_ativo_instalacao = ($valor_ativo_instalacao < 0) ? 0 : $valor_ativo_instalacao;
			$valores_ativos["instalacao"] = "<b>".$this->formataDinheiro($valores[2]["instalacao"], "R$", true).(($valores[2]["desconto"]["instalacao_sem_validade"] + $valores[2]["desconto"]["instalacao_com_validade"]) ? "<br>(".$this->formataDinheiro($valor_ativo_instalacao, "R$", true).")" : "")."</b>";
			$valores_ativos_instalacao_mouseover = "Detalhamento da Adesão (Ativos):<br>";
			if ($valores[2]["instalacao"]) $valores_ativos_instalacao_mouseover .= "&nbsp;&raquo; Assinatura(s): ".$this->formataDinheiro($valores[2]["instalacao"], "R$", true)."<br>";
			if ($valores[2]["desconto"]["instalacao_sem_validade"]) $valores_ativos_instalacao_mouseover .= "&nbsp;&raquo; Desconto(s) sem validade: ".$this->formataDinheiro($valores[2]["desconto"]["instalacao_sem_validade"], "R$", true)."<br>";
			if ($valores[2]["desconto"]["instalacao_com_validade"]) $valores_ativos_instalacao_mouseover .= "&nbsp;&raquo; Desconto(s) com validade: ".$this->formataDinheiro($valores[2]["desconto"]["instalacao_com_validade"], "R$", true)."<br>";
			if ($valores[2]["desconto"]["instalacao_sem_validade"] - $valores[2]["desconto"]["instalacao_com_validade"]) $valores_ativos_instalacao_mouseover .= "&nbsp;&raquo; Total: ".$this->formataDinheiro($valor_ativo_instalacao, "R$", true)."<br>";
			$valores_ativos["instalacao"] = '<div class="txtAzulEscuro11" style="cursor:pointer" onMouseOver="hintShow(\''.$valores_ativos_instalacao_mouseover.'\');" onMouseOut="hintHide();">'.$valores_ativos["instalacao"].'</div>';

			$valor_ativo_mensalidade = $valores[2]["mensalidade"] - $valores[2]["desconto"]["mensalidade_sem_validade"] - $valores[2]["desconto"]["mensalidade_com_validade"] + $valores[2]["ajuste_financeiro"]["sem_validade"] + $valores[2]["ajuste_financeiro"]["com_validade"];
			$valor_ativo_mensalidade = ($valor_ativo_mensalidade < 0) ? 0 : $valor_ativo_mensalidade;
			$valores_ativos["mensalidade"] = "<b>".$this->formataDinheiro($valores[2]["mensalidade"], "R$", true).(($valores[2]["desconto"]["mensalidade_sem_validade"] + $valores[2]["desconto"]["mensalidade_com_validade"] + $valores[2]["ajuste_financeiro"]["sem_validade"] + $valores[2]["ajuste_financeiro"]["com_validade"]) ? "<br>(".$this->formataDinheiro($valor_ativo_mensalidade, "R$", true).")" : "")."</b>";
			$valores_ativos_mensalidade_mouseover = "Detalhamento da Mensalidade (Ativos):<br>";
			if ($valores[2]["mensalidade"]) $valores_ativos_mensalidade_mouseover .= "&nbsp;&raquo; Assinatura(s): ".$this->formataDinheiro($valores[2]["mensalidade"], "R$", true)."<br>";
			if ($valores[2]["desconto"]["mensalidade_sem_validade"]) $valores_ativos_mensalidade_mouseover .= "&nbsp;&raquo; Desconto(s) sem validade: ".$this->formataDinheiro($valores[2]["desconto"]["mensalidade_sem_validade"], "R$", true)."<br>";
			if ($valores[2]["desconto"]["mensalidade_com_validade"]) $valores_ativos_mensalidade_mouseover .= "&nbsp;&raquo; Desconto(s) com validade: ".$this->formataDinheiro($valores[2]["desconto"]["mensalidade_com_validade"], "R$", true)."<br>";
			if ($valores[2]["ajuste_financeiro"]["com_validade"]) $valores_ativos_mensalidade_mouseover .= "&nbsp;&raquo; Ajuste(s) Financeiro(s) com validade: ".(($valores[2]["ajuste_financeiro"]["com_validade"]<0) ? "- " : "").$this->formataDinheiro($valores[2]["ajuste_financeiro"]["com_validade"], "R$", true)."<br>";
			if ($valores[2]["mensalidade"] - $valores[2]["desconto"]["mensalidade_sem_validade"] - $valores[2]["desconto"]["mensalidade_com_validade"] + $valores[2]["ajuste_financeiro"]["sem_validade"] + $valores[2]["ajuste_financeiro"]["com_validade"]) $valores_ativos_mensalidade_mouseover .= "&nbsp;&raquo; Total: ".$this->formataDinheiro($valor_ativo_mensalidade, "R$", true)."<br>";
			$valores_ativos["mensalidade"] = '<div class="txtAzulEscuro11" style="cursor:pointer" onMouseOver="hintShow(\''.$valores_ativos_mensalidade_mouseover.'\');" onMouseOut="hintHide();">'.$valores_ativos["mensalidade"].'</div>';

			$valores_ativos["descontos"] = "";

			$assinaturas[] = $valores_ativos;

		}

		// Total
		if ($valores[1] && $valores[2]) {

		    $valores_totais = array();

			$valores_totais["status"] = "<b>Total</b>";
			$valores_totais["status_nome"] = "<b>Total</b>";
			$valores_totais["instalacao"] = "<b>".$this->formataDinheiro($valores[1]["instalacao"] + $valores[2]["instalacao"], "R$", true).(($valores[1]["desconto"]["instalacao_sem_validade"] + $valores[1]["desconto"]["instalacao_com_validade"] + $valores[2]["desconto"]["instalacao_sem_validade"] + $valores[2]["desconto"]["instalacao_com_validade"]) ? "<br>(".$this->formataDinheiro($valores[1]["instalacao"] - $valores[1]["desconto"]["instalacao_sem_validade"] - $valores[1]["desconto"]["instalacao_com_validade"] + $valores[2]["instalacao"] - $valores[2]["desconto"]["instalacao_sem_validade"] - $valores[2]["desconto"]["instalacao_com_validade"], "R$", true).")" : "")."</b>";
			$valores_totais["mensalidade"] = "<b>".$this->formataDinheiro($valores[1]["mensalidade"] + $valores[2]["mensalidade"], "R$", true).(($valores[1]["desconto"]["mensalidade_sem_validade"] + $valores[1]["desconto"]["mensalidade_com_validade"] + $valores[1]["desconto"]["mensalidade_sem_validade"] + $valores[1]["desconto"]["mensalidade_com_validade"] + $valores[2]["ajuste_financeiro"]["sem_validade"] + $valores[2]["ajuste_financeiro"]["com_validade"]) ? "<br>(".$this->formataDinheiro($valores[1]["mensalidade"] - $valores[1]["desconto"]["mensalidade_sem_validade"] - $valores[1]["desconto"]["mensalidade_com_validade"] + $valores[2]["mensalidade"] - $valores[2]["desconto"]["mensalidade_sem_validade"] - $valores[2]["desconto"]["mensalidade_com_validade"] + $valores[2]["ajuste_financeiro"]["sem_validade"] + $valores[2]["ajuste_financeiro"]["com_validade"], "R$", true).")" : "")."</b>";
			$valores_totais["descontos"] = "";

			$assinaturas[] = $valores_totais;

		}

		return $assinaturas;

	}

	function buscaPeriodicidade($periodicidade) {

		if (array_key_exists($periodicidade, $this->periodicidades)) {
			return $this->periodicidades[$periodicidade];
		} else {
			return $this->periodicidades["1"];
		}

	}

	function buscaAssinaturaStatusCompleto($dados_assinatura) {

		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_codigo AS codigo,
				DATE_FORMAT(tab_assinaturas.ass_data_cadastro, '%d/%m/%Y') AS data_cadastro,
				DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%d/%m/%Y') AS data_fechamento,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%d/%m/%Y') AS data_cancelamento,
				DATE_FORMAT(tab_contratos.cor_data_anulacao, '%d/%m/%Y') AS data_anulacao
			FROM
				tab_assinaturas
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				LEFT OUTER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
			WHERE
				tab_assinaturas.ass_id = {$dados_assinatura["id"]}");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($dados["codigo"]) {
				$status[] = "{$dados_assinatura["status_nome"]} com contrato.";
				$status[] = " &raquo; Contrato: {$dados["codigo"]}";
			} else {
				$status[] = "{$dados_assinatura["status_nome"]} sem contrato gerado";
			}

			if ($dados["data_cadastro"]) $status[] = " &raquo; Data de Cadastro: {$dados["data_cadastro"]}";
			if ($dados["data_fechamento"]) $status[] = " &raquo; Data de Fechamento: {$dados["data_fechamento"]}";
			if ($dados["data_ativacao"]) $status[] = " &raquo; Data de Ativação: {$dados["data_ativacao"]}";
			if ($dados["data_anulacao"]) $status[] = " &raquo; Data de Anulação: {$dados["data_anulacao"]}";
			if ($dados["data_cancelamento"]) $status[] = " &raquo; Data de Cancelamento: {$dados["data_cancelamento"]}";

		}

		return implode("<br>", $status);

	}

	function buscaAjustesFinanceirosCliente($cliente_id, $mensalidade) {

		$ajustes_financeiros = array();

		$query = $this->bancodedados->query("
			SELECT
				ajf_competencia_termino AS termino,
				ajf_valor AS valor,
				ajf_valor_tipo AS valor_tipo,
				aft_id AS tipo
			FROM
				tab_ajustes_financeiros
			WHERE
				afs_id = 5 AND
				afc_id != 11 AND
				cli_id = $cliente_id AND
				ajf_competencia_inicio BETWEEN '".date("Y-m-01")."' AND '".date("Y-m-t")."' AND
				ajf_competencia_termino IS NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($dados["termino"]) {
				if ($dados["valor_tipo"] == 1) {
					if ($dados["tipo"] == 1) {
						$ajustes_financeiros["com_validade"] += round($dados["valor"]);
					} else {
						$ajustes_financeiros["com_validade"] -= round($dados["valor"]);
					}
				} else if ($dados["valor_tipo"] == 2) {
					if ($dados["tipo"] == 1) {
						$ajustes_financeiros["com_validade"] += round($mensalidade * $dados["valor"]);
					} else {
						$ajustes_financeiros["com_validade"] -= round($mensalidade * $dados["valor"]);
					}
				} else if ($dados["valor_tipo"] == 3) {
					if ($dados["tipo"] == 1) {
						$ajustes_financeiros["com_validade"] += round($mensalidade * ($dados["valor"] / date('t')));
					} else {
						$ajustes_financeiros["com_validade"] -= round($mensalidade * ($dados["valor"] / date('t')));
					}
				}
			} else {
				if ($dados["valor_tipo"] == 1) {
					if ($dados["tipo"] == 1) {
						$ajustes_financeiros["sem_validade"] += round($dados["valor"]);
					} else {
						$ajustes_financeiros["sem_validade"] -= round($dados["valor"]);
					}
				} else if ($dados["valor_tipo"] == 2) {
					if ($dados["tipo"] == 1) {
						$ajustes_financeiros["sem_validade"] += round($mensalidade * $dados["valor"]);
					} else {
						$ajustes_financeiros["sem_validade"] -= round($mensalidade * $dados["valor"]);
					}
				} else if ($dados["valor_tipo"] == 3) {
					if ($dados["tipo"] == 1) {
						$ajustes_financeiros["sem_validade"] += round($mensalidade * ($dados["valor"] / date('t')));
					} else {
						$ajustes_financeiros["sem_validade"] -= round($mensalidade * ($dados["valor"] / date('t')));
					}
				}
			}

		}

		return $ajustes_financeiros;

	}

	function geraListagemClientesAssinaturas($dados) {

		$cabecalho = array ("status_nome" => "Status", "servico" => "Serviço", "plano" => "Plano", "sistema_nome" => "Sistema", "instalacao" => "Adesão", "mensalidade" => "Mensalidade", "descontos" => "Desconto(s)");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 2, false, array(75, 90, 0, 50, 60, 70, 190), false, array("center", "center", "left", "center", "center", "center", "left"));

		return $listagem->conteudo;

	}

	function buscaPlanosServicos($cliente_id, $origem_id, $pfpj, $ccsc, $condominio_id) {

		$lista_servicos = array();
		$velocidade_maxima = $condominio_id ? $this->buscaVelocidadeMaxima($condominio_id) : 0;
		$planos_condominio = $condominio_id ? $this->buscaRestricoesCondominio($condominio_id) : null;

		$sql = "
			SELECT DISTINCT
				tab_planos_servicos.pls_id AS id,
				tab_planos.pla_id AS planoId,
				tab_planos.pla_nome AS planoNome,
				tab_planos_servicos.pls_nome AS nome,
				tab_planos_servicos.pls_parametros AS servicos_parametros,
				tab_planos.pla_parametros AS planos_parametros
			FROM
				tab_planos_servicos
				INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
			WHERE".($this->buscaTipoInstalacaoCondominio($condominio_id) == 1 ? "
				tab_planos.pls_id NOT IN (32, 1) AND" : "").($condominio_id ? "
				(tab_planos.con_id IS NULL OR tab_planos.con_id = $condominio_id) AND" : "
				tab_planos.con_id IS NULL AND")."
				tab_planos.plu_id = 1 AND".($planos_condominio["permitidos"] ? "
				tab_planos.pla_id IN (".implode(", ", $planos_condominio["permitidos"]).") AND" : "").($planos_condominio["nao_permitidos"] ? " AND
				tab_planos.pla_id NOT IN (".implode(", ", $planos_condominio["nao_permitidos"]).") AND" : "")."
				(SELECT COUNT(plv_id) AS vinculos FROM tab_planos_vinculos WHERE tab_planos_vinculos.pla_filho = tab_planos.pla_id AND tab_planos_vinculos.pvt_id = 3) = 0 
				AND (
					NOT EXISTS (
						SELECT 1
						FROM tab_planos_multiservice m
						INNER JOIN tab_assinaturas a ON a.pla_id = m.pla_id
						WHERE a.cli_id = {$cliente_id} AND a.ast_id in (1,2,5)
						LIMIT 1
					)
					OR NOT EXISTS (
						SELECT 1
						FROM tab_planos_multiservice m2
						WHERE m2.pla_id = tab_planos.pla_id
						LIMIT 1
					)
				)
			ORDER BY
				nome";
		$query = $this->bancodedados->query($sql);
		$cc = 0;
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cc++;

			//$possuiPlanoParceiroDisponivel = $this->possuiPlanoParceiroDisponivel($dados["planoId"], $origem_id);
			$possuiLimiteDisponivel = $this->possuiLimiteDisponivel($dados["planoId"], $origem_id);
			if(!$possuiLimiteDisponivel) {
				continue;
			}
			
			$planos_parametros = unserialize($dados["planos_parametros"]);

			if ((($planos_parametros["aplica_pessoa_fisica"] && $pfpj == "pf") ||
					($planos_parametros["aplica_pessoa_juridica"] && $pfpj == "pj")) &&
					(($planos_parametros["aplica_com_condominio"] && $ccsc == "cc") ||
					($planos_parametros["aplica_sem_condominio"] && $ccsc == "sc")) &&
					(!$planos_parametros["origens"] || in_array($origem_id, $planos_parametros["origens"])) &&
					(!$velocidade_maxima || !$planos_parametros["pontos_quantidade"] || $planos_parametros["pontos_velocidade_download"] <= $velocidade_maxima)) {

				$servicos_parametros = unserialize($dados["servicos_parametros"]);

				if (!$servicos_parametros["aceita_multiplos"]) {
					$query_planos = $this->bancodedados->query("
						SELECT
							COUNT(ass_id)
						FROM
							tab_assinaturas
							INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
						WHERE
							tab_assinaturas.cli_id = $cliente_id AND
							tab_assinaturas.ast_id NOT IN (3, 4, 6) AND
							tab_planos.pls_id = {$dados["id"]}");
					if ($this->bancodedados->resultado($query_planos) == 0) {
						$lista_servicos[$dados["id"]] = $dados["nome"];
					}
				} else {
					$lista_servicos[$dados["id"]] = $dados["nome"];
				}

			}

		}

		return $lista_servicos ? array_unique($lista_servicos) : null;

	}

	function buscaVelocidadeMaxima($condominio_id) {

		$query = $this->bancodedados->query("
			SELECT
				con_velocidade_maxima
			FROM
				tab_condominios
			WHERE
				con_id = $condominio_id");

		return (int) $this->bancodedados->resultado($query);

	}

	function buscaServicosPlanos($servico_id, $pfpj, $ccsc, $condominio_id, $origem_id) {

		$planos = array();
		$velocidade_maxima = $condominio_id ? $this->buscaVelocidadeMaxima($condominio_id) : 0;
		$planos_condominio = $condominio_id ? $this->buscaRestricoesCondominio($condominio_id) : null;

		$vod_pacotes = -1;

		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_id AS id,
				IF(tab_planos.pli_id IS NULL, tab_planos.pla_nome, CONCAT('[', tab_planos_sistemas.pli_nome, '] ', tab_planos.pla_nome)) AS nome,
				tab_planos.pli_id AS sistema_id,
				tab_planos.pla_parametros AS parametros
			FROM
				tab_planos
				LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos.pli_id = tab_planos_sistemas.pli_id)
			WHERE".($origem_id == 13 ? "
				tab_planos.pla_id IN (729) AND" : "").($condominio_id ? "
				(tab_planos.con_id IS NULL OR tab_planos.con_id = $condominio_id) AND" : "
				tab_planos.con_id IS NULL AND")."
				tab_planos.pls_id = $servico_id AND".($planos_condominio["permitidos"] ? "
				tab_planos.pla_id IN (".implode(", ", $planos_condominio["permitidos"]).") AND" : "").($planos_condominio["nao_permitidos"] ? " AND
				tab_planos.pla_id NOT IN (".implode(", ", $planos_condominio["nao_permitidos"]).") AND" : "")."
				tab_planos.plt_id = 1 AND
				tab_planos.pla_tipo_contrato_principal = 1 AND
				tab_planos.plu_id = 1
			ORDER BY
				nome");
		$cc = 0;
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cc++;
			$parametros = unserialize($dados["parametros"]);
		
			//$possuiPlanoParceiroDisponivel = $this->possuiPlanoParceiroDisponivel($dados["id"], $origem_id);
			$possuiLimiteDisponivel = $this->possuiLimiteDisponivel($dados["id"], $origem_id);
			if(!$possuiLimiteDisponivel) {
				continue;
			}

			if (
					(($parametros["aplica_pessoa_fisica"] && $pfpj == "pf") || ($parametros["aplica_pessoa_juridica"] && $pfpj == "pj")) &&
					(($parametros["aplica_com_condominio"] && $ccsc == "cc") || ($parametros["aplica_sem_condominio"] && $ccsc == "sc")) &&
					(!$parametros["origens"] || in_array($origem_id, $parametros["origens"])) &&
					($_SESSION["usuario"] == 39 || !$velocidade_maxima || !$parametros["pontos_quantidade"] || $parametros["pontos_velocidade_download"] <= $velocidade_maxima)) {
				if ($origem_id == 525) {
					if(!in_array($dados["id"], array(730, 731, 732))) {
						$planos[$dados["id"]] = $dados["nome"];
					}
				} else {
					$planos[$dados["id"]] = $dados["nome"];
				}
			}
			
		}

		return $planos;

	}

	function buscaRestricoesCondominio($condominio_id) {

		$restricoes = array(
			"permitidos" => array(),
			"nao_permitidos" => array()
		);

		$query = $this->bancodedados->query("
			SELECT
				pla_id AS plano_id,
				rcp_permissao AS permissao
			FROM
				rel_con_pla_tempermissao
			WHERE
				con_id = $condominio_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["permissao"] == 1) {
				$restricoes["permitidos"][] = $dados["plano_id"];
			} else {
				$restricoes["nao_permitidos"][] = $dados["plano_id"];
			}
		}

		return $restricoes;

	}

	function buscaServicos() {

		$servicos = array();

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS id,
				pls_nome AS nome
			FROM
				tab_planos_servicos
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}

		return $servicos;

	}

	function buscaDescontosAssinatura($dados_assinatura) {

		$validades = $this->buscaValidades();

		$dados = array(
			"listagem" => array(),
			"texto_sem_mouseover" => array(),
			"texto_com_mouseover" => array(),
			"instalacao_com_validade" => 0,
			"instalacao_sem_validade" => 0,
			"mensalidade_com_validade" => 0,
			"mensalidade_sem_validade" => 0
		);

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_inicio, '%d/%m/%Y') AS data_inicio,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_termino, '%d/%m/%Y') AS data_termino,
				tab_descontos.des_nome AS nome,
				tab_descontos_concessoes_status.dcs_id AS status_id,
				tab_descontos_concessoes_status.dcs_nome AS status_nome,
				tab_descontos_tipos.det_nome AS tipo
			FROM
				tab_descontos
				INNER JOIN tab_descontos_concessoes ON (tab_descontos.des_id = tab_descontos_concessoes.des_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_concessoes.det_id = tab_descontos_tipos.det_id)
			WHERE
				tab_descontos_concessoes.ass_id = {$dados_assinatura["id"]}
			ORDER BY
				tab_descontos.des_nome");

		while (($desconto = $this->bancodedados->resultadoArray($query)) != false) {

			if ($desconto["instalacao"] <= 1) {
				$desconto["instalacao"] = $desconto["instalacao"] * $dados_assinatura["instalacao"];
			}
			if ($desconto["mensalidade"] <= 1) {
				$desconto["mensalidade"] = $desconto["mensalidade"] * $dados_assinatura["mensalidade"];
			}
			if ($desconto["status_id"] == 2 || $desconto["status_id"] == 5) {
				if ($desconto["validade"]) {
					$dados["instalacao_com_validade"] += $desconto["instalacao"];
					$dados["mensalidade_com_validade"] += $desconto["mensalidade"];
				} else {
					$dados["instalacao_sem_validade"] += $desconto["instalacao"];
					$dados["mensalidade_sem_validade"] += $desconto["mensalidade"];
				}
			}
			$desconto["validade"] = !$desconto["validade"] ? ($desconto["data_termino"] ? $desconto["data_termino"] : "Indeterminada") : $validades[$desconto["validade"]];

			$dados["listagem"][] = $desconto;

		}

		$dados["instalacao"] = $dados["instalacao_com_validade"] + $dados["instalacao_sem_validade"];

		if ($dados["instalacao"] > $dados_assinatura["instalacao"]) {
			$diferenca_instalacao = $dados["instalacao"] - $dados_assinatura["instalacao"];
			$dados["instalacao"] -= $diferenca_instalacao;
			$dados["instalacao_com_validade"] -= $diferenca_instalacao;
			if ($dados["instalacao_com_validade"] < 0) {
				$dados["instalacao_sem_validade"] += $dados["instalacao_com_validade"];
				$dados["instalacao_com_validade"] = 0;
			}
		}

		$dados["mensalidade"] = $dados["mensalidade_com_validade"] + $dados["mensalidade_sem_validade"];

		if ($dados["mensalidade"] > $dados_assinatura["mensalidade"]) {
			$diferenca_mensalidade = $dados["mensalidade"] - $dados_assinatura["mensalidade"];
			$dados["mensalidade"] -= $diferenca_mensalidade;
			$dados["mensalidade_com_validade"] -= $diferenca_mensalidade;
			if ($dados["mensalidade_com_validade"] < 0) {
				$dados["mensalidade_sem_validade"] += $dados["mensalidade_com_validade"];
				$dados["mensalidade_com_validade"] = 0;
			}
		}

		// Gera texto descontos
		for ($i = 0; $i < sizeof($dados["listagem"]); $i++) {

			$dados_sem_mouseover = '<b><a href="javascript:verDescontoConcessao('.$dados["listagem"][$i]["id"].')" class="txtAzulEscuro11" style="text-decoration:underline">'.$dados["listagem"][$i]["nome"].'</a></b>:<br>';
			$dados_sem_mouseover .= "&nbsp;&raquo; Adesão: ".$this->formataDinheiro($dados["listagem"][$i]["instalacao"], "R$", true)."<br>";
			$dados_sem_mouseover .= "&nbsp;&raquo; Mensalidade: ".$this->formataDinheiro($dados["listagem"][$i]["mensalidade"], "R$", true)."<br>";
			if ($dados["listagem"][$i]["status_id"] <= 4) {
				$dados_sem_mouseover .= "&nbsp;&raquo; Validade: ".$dados["listagem"][$i]["validade"]."<br>";
			} else {
				$dados_sem_mouseover .= "&nbsp;&raquo; Início: ".$dados["listagem"][$i]["data_inicio"]."<br>";
				$dados_sem_mouseover .= "&nbsp;&raquo; Término: ".(($dados["listagem"][$i]["data_termino"]) ? $dados["listagem"][$i]["data_termino"] : "Indeterminado")."<br>";
			}
			$dados_sem_mouseover .= "&nbsp;&raquo; Tipo: ".$dados["listagem"][$i]["tipo"]."<br>";
			$dados_sem_mouseover .= "&nbsp;&raquo; Status: ".$dados["listagem"][$i]["status_nome"];

			$dados["texto_sem_mouseover"][] = $dados_sem_mouseover;

			$dados_com_mouseover = $dados["listagem"][$i]["nome"]."<br>";
			$dados_com_mouseover .= "&nbsp;&raquo; Adesão: ".$this->formataDinheiro($dados["listagem"][$i]["instalacao"], "R$", true)."<br>";
			$dados_com_mouseover .= "&nbsp;&raquo; Mensalidade: ".$this->formataDinheiro($dados["listagem"][$i]["mensalidade"], "R$", true)."<br>";
			if ($dados["listagem"][$i]["status_id"] <= 4) {
				$dados_com_mouseover .= "&nbsp;&raquo; Validade: ".$dados["listagem"][$i]["validade"]."<br>";
			} else {
				$dados_com_mouseover .= "&nbsp;&raquo; Início: ".$dados["listagem"][$i]["data_inicio"]."<br>";
				$dados_com_mouseover .= "&nbsp;&raquo; Término: ".(($dados["listagem"][$i]["data_termino"]) ? $dados["listagem"][$i]["data_termino"] : "Indeterminado")."<br>";
			}
			$dados_com_mouseover .= "&nbsp;&raquo; Tipo: ".$dados["listagem"][$i]["tipo"]."<br>";
			$dados_com_mouseover .= "&nbsp;&raquo; Status: ".$dados["listagem"][$i]["status_nome"];
			$dados_com_mouseover = '<a class="txtAzulEscuro11" onMouseOver="hintShow(\''.$dados_com_mouseover.'\', 170);" onMouseOut="hintHide();" onClick="javascript:verDescontoConcessao('.$dados["listagem"][$i]["id"].')">'.$dados["listagem"][$i]["nome"].' ('.$dados["listagem"][$i]["status_nome"].')</a>';

			$dados["texto_com_mouseover"][] = $dados_com_mouseover;

		}

		return $dados;

	}

	function buscaAssinaturasDescontos($assinatura_id) {

		$descontos = array(
			"instalacao" => 0,
			"mensalidade" => 0,
			"listagem" => array()
		);

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_termino, '%d/%m/%Y') AS data_termino,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos.des_nome AS desconto_nome,
				tab_descontos_tipos.det_nome AS desconto_tipo_nome,
				tab_descontos_concessoes_status.dcs_id AS status_id,
				tab_descontos_concessoes_status.dcs_nome AS status_nome,
				tab_assinaturas.ass_instalacao AS assinatura_instalacao,
				tab_assinaturas.ass_mensalidade AS assinatura_mensalidade
			FROM
				tab_assinaturas
				INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_concessoes.det_id = tab_descontos_tipos.det_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
			WHERE
				tab_descontos_concessoes.ass_id = $assinatura_id
			ORDER BY
				tab_descontos.des_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes["descontos_concessoes"]["ver"]) {
				$dados["link"] = "javascript:verDescontoConcessao({$dados["id"]})";
			}

			if ($dados["status_id"] <= 4) {
				$dados["validade"] = $this->formataDescontoValidade($dados["validade"], $dados["data_termino"]);
			} else {
				$dados["validade"] = ($dados["data_termino"]) ? $dados["data_termino"] : "Indeterminada";
			}

			$descontos["assinatura_instalacao"] = $dados["assinatura_instalacao"];
			$descontos["assinatura_mensalidade"] = $dados["assinatura_mensalidade"];

			$descontos["instalacao"] += $dados["instalacao"] > 1 ? $dados["instalacao"] : $dados["assinatura_instalacao"] * $dados["instalacao"];
			$descontos["mensalidade"] += $dados["mensalidade"] > 1 ? $dados["mensalidade"] : $dados["assinatura_mensalidade"] * $dados["mensalidade"];

			$dados["instalacao"] = $this->formataDescontoValor($dados["instalacao"]);
			$dados["mensalidade"] = $this->formataDescontoValor($dados["mensalidade"]);

			$dados["status_nome"] = str_replace(" (", "<br>(", $dados["status_nome"]);
			$dados["desconto_tipo_nome"] = str_replace(" [", "<br>[", $dados["desconto_tipo_nome"]);

			$descontos["listagem"][] = $dados;

		}

		$descontos["instalacao"] = $descontos["instalacao"] > $descontos["assinatura_instalacao"] ? $descontos["assinatura_instalacao"] : $descontos["instalacao"];
		$descontos["mensalidade"] = $descontos["mensalidade"] > $descontos["assinatura_mensalidade"] ? $descontos["assinatura_mensalidade"] : $descontos["mensalidade"];

		return $descontos;

	}

	function geraListagemAssinaturasDescontos($dados, $status = true) {

		$cabecalho = array(
			"desconto_nome" => "Nome",
			"instalacao" => "Adesão",
			"mensalidade" => "Mensalidade",
			"validade" => "Validade",
			"desconto_tipo_nome" => "Tipo"
		);

		if ($status) {
			$cabecalho["status_nome"] = "Status";
		}

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);

		return $listagem->conteudo;

	}

	function buscaDescontosAutomaticos($condominio_id, $cliente_id, $dados_plano, $sistema_id = null) {

		$descontos_aplicados = array();
		$validades = $this->buscaValidades();
		$data_cancelamento = $this->buscaDataCancelamento($cliente_id, $dados_plano["servico_id"]);
		$dados_cliente = $this->buscaDadosCliente($cliente_id);

		$descontos = array(
			"instalacao" => 0,
			"mensalidade" => 0,
			"listagem" => array()
		);

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos.des_id AS desconto_id,
				tab_descontos.des_nome AS desconto_nome,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_termino, '%d/%m/%Y') AS data_termino,
				tab_descontos.des_parametros AS parametros,
				tab_descontos_concessoes.con_id AS condominio_id,
				tab_descontos_concessoes.pla_id AS plano_id,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes_status.dcs_id AS status_id,
				tab_descontos_concessoes_status.dcs_nome AS status_nome,
				tab_descontos_tipos.det_nome AS desconto_tipo_nome
			FROM
				tab_descontos
				INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes_status.dcs_id = tab_descontos_concessoes.dcs_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_tipos.det_id = tab_descontos_concessoes.det_id)
			WHERE
				(".($condominio_id ? "tab_descontos_concessoes.con_id = $condominio_id OR " : "")."tab_descontos_concessoes.pla_id = {$dados_plano["id"]}) AND
				(tab_descontos_concessoes.dcs_id = 5) AND".($sistema_id ? "
				(tab_descontos.pli_id = $sistema_id OR tab_descontos.pli_id IS NULL) AND" : "
				(tab_descontos.pli_id IS NULL) AND")."
				(tab_descontos.pls_id = {$dados_plano["servico_id"]} OR tab_descontos.pls_id IS NULL)
			ORDER BY
				tab_descontos_concessoes.con_id DESC,
				tab_descontos_concessoes.dec_ordem ASC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$parametros = unserialize($dados["parametros"]);
			$parametros["carencia"] = $parametros["carencia"] ? $parametros["carencia"] : 0;

			$data_carencia_minima = date('Ymd', strtotime("{$parametros["carencia"]} days ago"));

			if ((!$parametros["carencia"] || $data_carencia_minima >= $data_cancelamento) &&
					(($parametros["aplica_plano_principal"] && $dados_plano["tipo_id"] == 1) ||
					($parametros["aplica_plano_adicional"] && $dados_plano["tipo_id"] == 2)) &&
					(!$parametros["origens"] || in_array($dados_cliente["origem_id"], $parametros["origens"])) &&
					(!$descontos_aplicados || count(array_intersect($parametros["descontos"], $descontos_aplicados)) == 0) &&
					(!$parametros["planos"] || in_array($dados_plano["id"], $parametros["planos"]))) {

				if ($parametros["validade_tipo"] == 2) {
					$dados["validade"] = $parametros["validade"] ? $parametros["validade"] : "Indeterminada";
				} else {
					$dados["validade"] = $dados["validade"] ? $validades[$dados["validade"]] : "Indeterminada";
				}

				$descontos["instalacao"] += $dados["instalacao"] > 1 ? $dados["instalacao"] : $dados_plano["instalacao"] * $dados["instalacao"];
				$descontos["mensalidade"] += $dados["mensalidade"] > 1 ? $dados["mensalidade"] : $dados_plano["mensalidade"] * $dados["mensalidade"];

				$dados["instalacao"] = $dados["instalacao"] > 0 ? $this->formataDescontoValor($dados["instalacao"]) : "-";
				$dados["mensalidade"] = $dados["mensalidade"] > 0 ? $this->formataDescontoValor($dados["mensalidade"]) : "-";

				$dados["desconto_tipo_nome"] = preg_replace('/\[[^\]]*\]/', "", $dados["desconto_tipo_nome"]);

				$descontos["listagem"][] = $dados;

				$descontos_aplicados[] = $dados["desconto_id"];

			}

		}

		$descontos["instalacao"] = $descontos["instalacao"] > $dados_plano["instalacao"] ? $dados_plano["instalacao"] : $descontos["instalacao"];
		$descontos["mensalidade"] = $descontos["mensalidade"] > $dados_plano["mensalidade"] ? $dados_plano["mensalidade"] : $descontos["mensalidade"];

		return $descontos;

	}

	function buscaValidades() {

		$validades = array();

		for ($i = 1; $i <= 12; $i++) {
			$validades[$i] = $i.($i > 1 ? " Meses" : " Mês");
		}

		return $validades;

	}

	function buscaTipoInstalacaoCondominio($condominio_id) {

		$query = $this->bancodedados->query("
			SELECT
				cit_id
			FROM
				tab_condominios
			WHERE
				con_id = '$condominio_id'");

		return (int) $this->bancodedados->resultado($query);

	}

	function buscaPacotesVodDisponivel($origem_id) {

		if ($origem_id == 1) {
			return PHP_INT_MAX;
		}

		$pacotes = 0;

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS assinatura_id,
				tab_assinaturas.ass_parametros AS parametros
			FROM
				tab_assinaturas
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.cli_cpf_cnpj = tab_clientes_origens.clo_cnpj)
			WHERE
				tab_assinaturas.ast_id IN (2, 5) AND
				tab_clientes_origens.cos_id = 2 AND
				tab_clientes_origens.clo_id = $origem_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
				
			$pacotes += $dados["parametros"]["vod_pacotes"];
			
		}
		
		if ($pacotes <= 0) {
			return 0;
		}

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_parametros AS parametros
			FROM
				tab_assinaturas
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_clientes.clo_id = $origem_id AND
				tab_assinaturas.ast_id IN (1, 2, 5)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			if ($dados["parametros"]["vod_telas"] > 0) {
				$pacotes -= 1; //$dados["parametros"]["vod_telas"];
			}
			
			if ($pacotes <= 0) {
				return 0;
			}
			
		}

		if ($pacotes <= 0) {
			return 0;
		}

		return $pacotes;

	}

	function possuiPlanoParceiroDisponivel($plano_id, $origem_id) {	
		
		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_parceiro_plano_id AS parceiro_plano_id		
			FROM
				tab_planos
			WHERE
				tab_planos.pla_id = {$plano_id} AND
				tab_planos.plu_id = 1");

		$dados_plano = $this->bancodedados->resultadoArray($query);

		if (!$dados_plano) {
			return false;
		}
		
		if (!$dados_plano["parceiro_plano_id"]) {
			return true;
		}
		
		if ($origem_id == 1) {
			return true;
		}
	
		$query = $this->bancodedados->query("
			SELECT
				SUM(tab_planos.pla_parceiro_max) AS parceiro_max,
				COUNT(*) AS planos
			FROM
				tab_assinaturas
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.cli_cpf_cnpj = tab_clientes_origens.clo_cnpj)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.pla_id = {$dados_plano["parceiro_plano_id"]} AND
				tab_assinaturas.ast_id IN (2, 5) AND
				tab_clientes_origens.cos_id = 2 AND
				tab_clientes_origens.clo_id = {$origem_id}");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($dados["planos"] == 0) {
				return false;
			} else if ($dados["parceiro_max"] == 0) {
				return true;
			}
			
			$query = $this->bancodedados->query("
				SELECT
					COUNT(*)
				FROM
					tab_assinaturas
					INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				WHERE
					tab_clientes.clo_id = {$origem_id} AND
					tab_assinaturas.pla_id = {$plano_id} AND
					tab_assinaturas.ast_id IN (1, 2, 5)");
					
			if ($dados["parceiro_max"] > $this->bancodedados->resultado($query)) {
				return true;
			} else {
				return false;
			}
			
		}
		
		return false;
		
	}

	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////

	// Verifica se o plano tem limite disponivel para a origem
	// @param int $plano_id - ID do plano
	// @param int $origem_id - ID da origem
	// @return bool
	function possuiLimiteDisponivel($plano_id, $origem_id, $retorno_proxy_id = false) {	
				
		if ($origem_id == 1) {
			return true;
		}

		$plano_limitado = false;

		/*
		 * Verifica limitação por quantidade
		 */
		
		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_parceiro_plano_id AS parceiro_plano_id		
			FROM
				tab_planos
			WHERE
				tab_planos.pla_id = {$plano_id} AND
				tab_planos.plu_id = 1");

		$dados_plano = $this->bancodedados->resultadoArray($query);

		if ($dados_plano["parceiro_plano_id"]) {
	
			$plano_limitado = true;

			$query = $this->bancodedados->query("
				SELECT
					SUM(tab_planos.pla_parceiro_max) AS parceiro_max,
					COUNT(*) AS planos
				FROM
					tab_assinaturas
					INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
					INNER JOIN tab_clientes_origens ON (tab_clientes.cli_cpf_cnpj = tab_clientes_origens.clo_cnpj)
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				WHERE
					tab_assinaturas.pla_id = {$dados_plano["parceiro_plano_id"]} AND
					tab_assinaturas.ast_id IN (2, 5) AND
					tab_clientes_origens.cos_id = 2 AND
					tab_clientes_origens.clo_id = {$origem_id}");

			$dados = $this->bancodedados->resultadoArray($query);
				
			if ($dados["planos"] > 0) {

				if ($dados["parceiro_max"] == 0) {
					return true;
				}
				
				$query = $this->bancodedados->query("
					SELECT
						COUNT(*)
					FROM
						tab_assinaturas
						INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
					WHERE
						tab_clientes.clo_id = {$origem_id} AND
						tab_assinaturas.pla_id = {$plano_id} AND
						tab_assinaturas.ast_id IN (1, 2, 5)");
						
				$totalDeAssinaturasContadas = $this->bancodedados->resultado($query);
				
				if ($dados["parceiro_max"] > $totalDeAssinaturasContadas) {
					return true;
				}

			}

		}

		/*
		 * Verifica limitação por valor (primeiro plano proxy com limite disponível)
		 */
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(pla_id) AS planos
			FROM
				tab_planos_limitacao
			WHERE
				tab_planos_limitacao.plano = {$plano_id}");
		
		$planos_com_limite_valor = $this->bancodedados->resultado($query);
		
		if ($planos_com_limite_valor > 0) {
			
			$plano_limitado = true;
		
			$query = $this->bancodedados->query("
				SELECT
					tab_planos_limitacao.pla_id AS proxy_plano_id,
					SUM(tab_planos.pla_valor_max) AS pla_valor_max
				FROM
					tab_clientes_origens
					INNER JOIN tab_clientes ON (tab_clientes.cli_cpf_cnpj = tab_clientes_origens.clo_cnpj)
					INNER JOIN tab_assinaturas ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
					INNER JOIN tab_planos_limitacao ON (tab_planos_limitacao.pla_id = tab_planos.pla_id)
				WHERE
					tab_clientes_origens.clo_id = {$origem_id} AND
					tab_assinaturas.ast_id IN (2, 5) AND
					tab_planos_limitacao.plano = {$plano_id}
				GROUP BY tab_planos_limitacao.pla_id");
				
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				$proxy_plano_id = $dados["proxy_plano_id"];
				$pla_valor_max = $dados["pla_valor_max"];

				$query_valor_inserir = $this->bancodedados->query("
					SELECT
						tab_planos_limitacao.valor AS valor
					FROM
						tab_planos_limitacao
					WHERE
						tab_planos_limitacao.pla_id = {$proxy_plano_id} AND
						tab_planos_limitacao.plano = {$plano_id}
					LIMIT 1");

				$valor_inserir = 0;

				if (($row_valor = $this->bancodedados->resultadoArray($query_valor_inserir)) != false) {
					$valor_inserir = $row_valor["valor"];
				}

				$query_usado = $this->bancodedados->query("
					SELECT
						COALESCE(SUM(pl.valor), 0) AS usado
					FROM
						tab_assinaturas_planos_proxy asp
						INNER JOIN tab_assinaturas a ON (a.ass_id = asp.ass_id)
						INNER JOIN tab_clientes c ON (c.cli_id = a.cli_id)
						INNER JOIN tab_planos_limitacao pl ON (pl.pla_id = asp.pla_proxy_id AND pl.plano = asp.pla_id)
					WHERE
						asp.pla_proxy_id = {$proxy_plano_id} AND
						c.clo_id = {$origem_id} AND
						a.ast_id IN (1, 2, 5)");

				$row_usado = $this->bancodedados->resultadoArray($query_usado);
				$usado = $row_usado ? $row_usado["usado"] : 0;

				if ($usado + $valor_inserir <= $pla_valor_max) {
					return $retorno_proxy_id ? $proxy_plano_id : true;
				}

			}
			
		}

		return !$plano_limitado;
		
	}
	
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////


	function buscaPlanosAdicionais($assinatura_id, $pfpj, $ccsc, $condominio_id, $origem_id) {

		$adicionais = array();
		
		$vod_pacotes = -1;

		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pls_id AS id,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_parametros AS parametros,
				tab_assinaturas.pli_id AS sistema_id,
				tab_assinaturas.cli_id AS cliente_id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id
				");

		if (($servico = $this->bancodedados->resultadoArray($query)) != false) {

			$servico["parametros"] = unserialize($servico["parametros"]);

			if ($condominio_id) {
				$velocidade_maxima = $this->buscaVelocidadeMaxima($condominio_id);
				$planos_condominio = $this->buscaRestricoesCondominio($condominio_id);
			} else {
				$velocidade_maxima = 0;
				$planos_condominio = null;
			}

			$query = $this->bancodedados->query("
				SELECT
					tab_planos.pla_id AS id,
					IF(tab_planos.pli_id IS NULL, tab_planos.pla_nome, CONCAT('[', tab_planos_sistemas.pli_nome, '] ', tab_planos.pla_nome)) AS nome,
					tab_planos.pli_id AS sistema_id,
					tab_planos.pla_parametros AS parametros,
					tab_planos.pla_parceiro_plano_id AS parceiro_plano_id,
					(SELECT COUNT(*) FROM tab_assinaturas WHERE tab_assinaturas.ass_pai = {$assinatura_id} AND tab_assinaturas.pla_id = tab_planos.pla_id) AS aditivos
				FROM
					tab_planos
					LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos.pli_id = tab_planos_sistemas.pli_id)
				WHERE".($condominio_id ? "
					(tab_planos.con_id IS NULL OR tab_planos.con_id = $condominio_id) AND" : "
					tab_planos.con_id IS NULL AND")."
					tab_planos.pls_id = {$servico["id"]} AND".($planos_condominio["permitidos"] ? "
					tab_planos.pla_id IN (".implode(", ", $planos_condominio["permitidos"]).") AND" : "").($planos_condominio["nao_permitidos"] ? " AND
					tab_planos.pla_id NOT IN (".implode(", ", $planos_condominio["nao_permitidos"]).") AND" : "")."
					tab_planos.plu_id = 1 AND
					tab_planos.plt_id = 2
					AND (
						NOT EXISTS (
							SELECT 1
							FROM tab_assinaturas a
							INNER JOIN tab_planos_multiservice m ON m.pla_id = a.pla_id
							WHERE a.cli_id = {$servico["cliente_id"]} AND a.ast_id in (1,2,5)
							LIMIT 1
						)
						OR NOT EXISTS (
							SELECT 1
							FROM tab_planos_multiservice m2
							WHERE m2.pla_id = tab_planos.pla_id
							LIMIT 1
						)
					)
				ORDER BY
					nome");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				$parametros = unserialize($dados["parametros"]);

				// Verifica se o plano tem limite disponivel para a origem
				$possuiPlanoParceiroDisponivel = $this->possuiPlanoParceiroDisponivel($dados["id"], $origem_id);
				$possuiLimiteDisponivel = $this->possuiLimiteDisponivel($dados["id"], $origem_id);
				if(!$possuiLimiteDisponivel) {
					continue;
				}

				// 36 - One Telecom - Novo Hamburgo
				// 38 - Sunway
				// 76 - Box Telecomunicações
				// 99 - Knet
				// 137 - Isuper
				// 370 - Net Vale MA
				// 408 - TH Internet
				// 498 - Ipop Telecom
				// 636 - Opção Telecom
				// 639 - SCM Evolutt - MS
				// 650 - SCM Evolutt - PR
				$limite = 3;
				$limiteMaisUm = 4;
				if(in_array($origem_id, array(36, 38, 76, 99, 498, 370, 137, 408, 654, 75, 636, 650, 639, 673))) {
					$limite = 4;
					$limiteMaisUm = 5;
				}
				if (in_array($servico["plano_id"], $parametros["aplicavel"]) &&
						($dados["id"] != 736 || ($dados["aditivos"] < $limite || (($dados["aditivos"] < $limiteMaisUm && $servico["plano_id"] == 730 && in_array($origem_id, array(36))) || ($dados["aditivos"] < $limiteMaisUm && in_array($origem_id, array(12222220))))) || $this->permissoes["assinaturas"]["adicionar_adicional_plus"]) &&
						(($parametros["aplica_pessoa_fisica"] && $pfpj == "pf" && ($servico["id"] != 1 || $parametros["pontos_velocidade_download"] <= $servico["parametros"]["pontos_velocidade_download"])) || ($parametros["aplica_pessoa_juridica"] && $pfpj == "pj")) &&
						(($parametros["aplica_com_condominio"] && $ccsc == "cc") || ($parametros["aplica_sem_condominio"] && $ccsc == "sc")) &&
						(!$parametros["origens"] || in_array($origem_id, $parametros["origens"])) &&
						(!$velocidade_maxima || !$parametros["pontos_quantidade"] || $parametros["pontos_velocidade_download"] <= $velocidade_maxima)) {
					$adicionais[$dados["id"]] = $dados["nome"];
				}

			}

		}

		return $adicionais;

	}

	function atualizaAssinaturaVigencia($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id,
				ass_instalacao AS instalacao,
				ass_mensalidade AS mensalidade,
				(SELECT pls_id FROM tab_planos WHERE tab_planos.pla_id = tab_assinaturas.pla_id) AS servico_id
			FROM
				tab_assinaturas
			WHERE
				ass_id = $assinatura_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$vigencia = $this->buscaVigenciaAssinatura($dados["id"], $dados["servico_id"], $dados["instalacao"], $dados["mensalidade"]);

			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
				SET
					ass_vigencia = {$vigencia["vigencia"]},
					ass_multa = {$vigencia["multa"]}
				WHERE
					ass_id = $assinatura_id");

		}

	}

	function buscaVigenciaAssinatura($assinatura_id, $servico_id, $instalacao, $mensalidade) {

		$vigencia = 0;
		$multa = 0;

		$query = $this->bancodedados->query("
			SELECT
				dec_mensalidade AS mensalidade,
				dec_instalacao AS instalacao,
				dec_validade AS validade,
				dec_fidelidade AS fidelidade
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id = $assinatura_id AND
				dcs_id IN (2, 5) AND
				det_id = 2");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["validade"] = $dados["validade"] ? $dados["validade"] : $dados["fidelidade"];

			if ($dados["mensalidade"]) {
				$multa += ($dados["mensalidade"] > 1 ? $dados["mensalidade"] : $dados["mensalidade"] * $mensalidade) * $dados["validade"];
			}

			if ($dados["instalacao"]) {
				$multa += $dados["instalacao"] > 1 ? $dados["instalacao"] : $dados["instalacao"] * $instalacao;
			}

			if ($vigencia < $dados["fidelidade"]) {
				$vigencia = $dados["fidelidade"];
			}

		}

		if ($multa && $vigencia) {

			$dados_servico = $this->buscaDadosServico($servico_id);

			if ($dados_servico["parametros"]["multa_maxima"] > 0 && $multa > $dados_servico["parametros"]["multa_maxima"]) {
				$multa = $dados_servico["parametros"]["multa_maxima"];
			}

			return array("vigencia" => $vigencia, "multa" => $multa);

		} else {

			return array("vigencia" => "NULL", "multa" => "NULL");

		}

	}

	function buscaFormasPagamento($plano_id) {

		$formas_pagamento = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_formas_pagamento.fop_id AS id,
				tab_formas_pagamento.fop_entrada AS entrada,
				tab_formas_pagamento.fop_parcelas AS parcelas,
				tab_formas_pagamento.fop_valor_parcela AS valor_parcela,
				tab_formas_pagamento.fop_entrada + (tab_formas_pagamento.fop_valor_parcela * tab_formas_pagamento.fop_parcelas) AS valor_total,
				tab_formas_pagamento.fop_multa AS multa,
				tab_formas_pagamento.fop_fidelidade AS fidelidade,
				tab_formas_pagamento_categorias.fpc_nome AS categoria,
				tab_formas_pagamento_formas.fpf_nome AS forma
			FROM
				tab_formas_pagamento
				INNER JOIN tab_formas_pagamento_categorias ON tab_formas_pagamento.fpc_id = tab_formas_pagamento_categorias.fpc_id
				LEFT OUTER JOIN tab_formas_pagamento_formas ON tab_formas_pagamento.fpf_id = tab_formas_pagamento_formas.fpf_id
			WHERE
				tab_formas_pagamento.pla_id = $plano_id AND
				tab_formas_pagamento.fpc_id IN (1, 2, 3, 4)
			ORDER BY
				tab_formas_pagamento.fpc_id,
				tab_formas_pagamento.fop_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["forma_pagamento"] = array();

			if ($dados["entrada"]) {
				$dados["forma_pagamento"][] = $this->formataDinheiro($dados["entrada"], "R$", true)." (Entrada)";
			}

			if ($dados["parcelas"] && $dados["valor_parcela"]) {
				$dados["forma_pagamento"][] = $dados["parcelas"]." X ".$this->formataDinheiro($dados["valor_parcela"], "R$", true)." ({$dados["forma"]})";
			}

			$dados["forma_pagamento"] = $dados["forma_pagamento"] ? implode(" + ", $dados["forma_pagamento"]) : "-";

			if ($dados["parcelas"]) {
				$dados["forma_pagamento"] .= " = ".$this->formataDinheiro($dados["valor_total"], "R$", true);
			}

			if ($dados["fidelidade"] && $dados["multa"]) {
				$dados["fidelidade"] = $dados["fidelidade"]." meses / ".$this->formataDinheiro($dados["multa"], "R$", true);
			} else {
				$dados["fidelidade"] = "-";
			}

			$formas_pagamento[] = $dados;

		}

		return $formas_pagamento;

	}

	function geraListagemFormasPagamento($dados) {

		$cabecalho = array(
			"categoria" => "Categoria",
			"forma_pagamento" => "Forma Pagamento",
			"fidelidade" => "Fidelidade"
		);

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 2, false, null, false, array("center", "center", "center"));

		return $listagem->conteudo;

	}

	##############
	### Fechar ###
	##############

	function fechar() {

		if ($this->dados_item) {
			$this->conteudo = '<script type="text/javascript">if (window.opener) window.opener.buscaAssinaturasCliente('.$this->dados_item["cliente_id"].');</script>';
			$this->conteudo .= '<script type="text/javascript">window.close();</script>';
		} else {
			$this->conteudo = '<script type="text/javascript">window.close();</script>';
		}

	}

	#################
	### Adicionar ###
	#################

	function adicionar() {

		if (($dados_cliente = $this->buscaDadosCliente($_GET["cliente_id"])) != false) {

			$dados_assinatura = intval($_GET["assinatura_id"]) ? $this->buscaDadosAssinatura($_GET["assinatura_id"]) : null;
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				if ($dados_assinatura) $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				if (!$dados_assinatura) {

					$this->conteudo .= $this->carregaArquivo("assinaturas.pla.adicionar.inc.php");

					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&cliente_id={$dados_cliente["id"]}&condominio_id={$dados_cliente["condominio_id"]}", $this->conteudo);

				} else {

					$this->conteudo .= $this->carregaArquivo("assinaturas.pld.adicionar.inc.php");

					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&cliente_id={$dados_cliente["id"]}&assinatura_id={$dados_assinatura["id"]}", $this->conteudo);
					$this->conteudo = str_replace("[planos_adicionais]", $this->geraSelect($this->buscaPlanosAdicionais($dados_assinatura["id"], $dados_assinatura["pfpj"] ? "pf" : "pj", $dados_assinatura["ccsc"] ? "cc" : "sc", $dados_cliente["condominio_id"], $dados_cliente["origem_id"]), $dados_assinatura["plano_id"], true, "plano", "480px", "mudaAssinaturaAdicional(this, {$dados_assinatura["sistema_id"]})"), $this->conteudo);
					$this->conteudo = str_replace("[assinatura_id]", $dados_assinatura["id"], $this->conteudo);
					$this->conteudo = str_replace("[assinatura_nome]", $dados_assinatura["servico_nome"].' &raquo; '.$dados_assinatura["plano_nome"], $this->conteudo);
					$this->conteudo = str_replace("[sistema]", $dados_assinatura["sistema_nome"], $this->conteudo);
					$this->conteudo = str_replace("[descontos]", $this->geraListagemAssinaturasDescontos(null), $this->conteudo);

				}

				$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[origem_id]", $dados_cliente["origem_id"] ? $dados_cliente["origem_id"] : $_SESSION["origem"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[condominio]", $dados_cliente["condominio_id"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			} else {

				$_POST["sistema"] = $dados_assinatura ? $dados_assinatura["sistema_id"] : $_POST["sistema"];

				$assinatura_id = $this->adicionarAssinatura($dados_cliente, $dados_cliente["condominio_id"], $_POST["plano"], $_POST["sistema"], $_GET["assinatura_id"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $assinatura_id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$assinatura_id}");

			}

		} else {
			$this->fechar();
		}

	}

	function adicionarAssinatura($dados_cliente, $condominio_id, $plano_id, $sistema_id, $assinatura_id = null, $vinculo = null) {

		$assinatura_id = $assinatura_id ? $assinatura_id : "NULL";
		$dados_plano = $this->buscaDadosPlano($plano_id);

		$dados_plano["sistema_id"] = $dados_plano["sistema_id"] ? $dados_plano["sistema_id"] : $sistema_id;

		$this->bancodedados->query("
			INSERT INTO
				tab_assinaturas
			SET
				cli_id = {$dados_cliente["id"]},
				pla_id = $plano_id,
				pli_id = {$dados_plano["sistema_id"]},
				ass_parametros = '{$dados_plano["parametros"]}',
				ass_instalacao = {$dados_plano["instalacao"]},
				ass_mensalidade = {$dados_plano["mensalidade"]},
				ass_data_cadastro = NOW(),
				ast_id = 1,
				ass_pai = $assinatura_id");

		$id = $this->bancodedados->ultimoIdInserido();

		// Adiciona Ajuste Programado
		if ($dados_plano["categoria_ajuste_id"]) {

			// Busca Dados Categoria
			$dados_categoria = $this->buscaDadosCategoriaAjuste($dados_plano["categoria_ajuste_id"]);

			// Insere Ajuste Programado
			$this->bancodedados->query("
				INSERT INTO
					tab_servicos_ajustes
				SET
					cli_id = {$dados_cliente["id"]},
					ass_id = $id,
					sac_id = {$dados_plano["categoria_ajuste_id"]},
					saj_inicio = NULL,
					saj_termino = NULL,
					sas_id = 1");

			$ajuste_id = $this->bancodedados->ultimoIdInserido();

			// Registra Regras
			$this->registraRegras($ajuste_id, $dados_categoria["regras"]);

			// Registra Comentário
			$this->registraComentarioAjuste($ajuste_id, "<b>Ação</b>: Adicionado");

			// Insere Link Assinatura
			$this->bancodedados->query("
				INSERT INTO
					rel_saj_ass_contem
				SET
					saj_id = $ajuste_id,
					ass_id = $id");

		}

		// Atualiza Status do Cliente
		$this->atualizaStatusCliente($dados_cliente["id"]);

		// Adiciona Domínio
		if (($dominios = $this->planoTemDominio($plano_id)) != false) {
			for ($i = 0; $i < $dominios; $i++) {
				$this->bancodedados->query("
					INSERT INTO
						tab_dominios
					SET
						ass_id = $id,
						dos_id = 1");
			}
		}

		// Descontos Automáticos
		$this->insereDescontosAutomaticos($id, $condominio_id, $dados_cliente["id"], $dados_plano, $sistema_id);

		// Atualiza Vigência
		$this->atualizaAssinaturaVigencia($id);

		// Gera Vínculo
		if ($vinculo) {
			$this->bancodedados->query("
				INSERT INTO
					tab_assinaturas_vinculos
				SET
					ass_id = {$vinculo["assinatura_id"]},
					ass_filha = $id,
					pvt_id = {$vinculo["tipo_id"]}");
		}
		

		// Adiciona Assinaturas Vinculadas
		foreach ($dados_plano["vinculos"] as $vinculo) {
			$dados_assinatura = $this->buscaAssinatura($dados_cliente["id"], $vinculo["plano_id"], "1, 2");
			if ($vinculo["aplicacao_id"] == 2 && $dados_assinatura) {
				// Cria Vínculo: Aplicável 1 Por Cliente e Assinatura Existente
				$this->bancodedados->query("
					INSERT INTO
						tab_assinaturas_vinculos
					SET
						ass_id = $id,
						ass_filha = {$dados_assinatura["id"]},
						pvt_id = {$vinculo["tipo_id"]}");
			} else {
				$assinatura_pai = $dados_plano["servico_id"] == $vinculo["servico_id"] ? $id : null;
				$vinculo["assinatura_id"] = $id;
				$this->adicionarAssinatura($dados_cliente, $condominio_id, $vinculo["plano_id"], $sistema_id, $assinatura_pai, $vinculo);
			}
		}

		$id_plano_proxy = $this->possuiLimiteDisponivel($plano_id, $dados_cliente["origem_id"], true);

		if (is_numeric($id_plano_proxy) && (int) $id_plano_proxy > 0) {

			$pla_proxy_id = (int) $id_plano_proxy;

			$this->bancodedados->query("
				INSERT INTO
					tab_assinaturas_planos_proxy
				SET
					pla_id = {$plano_id},
					ass_id = {$id},
					pla_proxy_id = {$pla_proxy_id}");
		}

		return $id;

	}

	function buscaAssinatura($cliente_id, $plano_id, $lista_status) {

		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id,
				ast_id AS status_id
			FROM
				tab_assinaturas
			WHERE
				cli_id = $cliente_id AND
				pla_id = $plano_id AND
				ast_id IN ($lista_status)
			ORDER BY
				ast_id
			LIMIT
				0, 1");

		return $this->bancodedados->resultadoArray($query);

	}

	function registraRegras($ajuste_id, $regras) {

		// Remove Regras Antigas
		$this->bancodedados->query("
			DELETE FROM
				tab_servicos_ajustes_regras
			WHERE
				saj_id = $ajuste_id");

		// Insere Regras Novas
		foreach ($regras as $regra) {

			$regra["servico"] = $regra["servico"] ? $regra["servico"] : "NULL";
			$regra["ajuste"] = $regra["ajuste_tipo"] == 1 ? $regra["ajuste_valor"] / 100 : $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
			$regra["dia_semana"] = $regra["dia_semana"] ? $regra["dia_semana"] : "NULL";
			$regra["hora_inicio"] = $regra["hora_inicio"] && $regra["hora_inicio"] != '00:00' ? "'{$regra["hora_inicio"]}'" : "NULL";
			$regra["hora_termino"] = $regra["hora_termino"] && $regra["hora_termino"] != '24:00' ? "'{$regra["hora_termino"]}'" : "NULL";

			$this->bancodedados->query("
				INSERT INTO
					tab_servicos_ajustes_regras
				SET
					saj_id = $ajuste_id,
					sat_id = {$regra["servico"]},
					sar_ajuste = {$regra["ajuste"]},
					sar_dia_semana = {$regra["dia_semana"]},
					sar_hora_inicio = {$regra["hora_inicio"]},
					sar_hora_termino = {$regra["hora_termino"]}");

		}

	}

	function buscaDescontosAssinaturaAplicados($assinatura_id) {

		$descontos = array();

		$query = $this->bancodedados->query("
			SELECT
				des_id AS id
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id = $assinatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[] = $dados["id"];
		}

		return $descontos;

	}

	function insereDescontosAutomaticos($assinatura_id, $condominio_id, $cliente_id, $dados_plano, $sistema_id) {

		$data_cancelamento = $this->buscaDataCancelamento($cliente_id, $dados_plano["servico_id"]);
		$descontos_aplicados = $this->buscaDescontosAssinaturaAplicados($assinatura_id);
		$dados_cliente = $this->buscaDadosCliente($cliente_id);

		$descontos = array();
		$descontos_aplicados = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos.des_parametros AS parametros,
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes.dec_fidelidade AS fidelidade,
				tab_descontos_concessoes.dec_data_inicio AS data_inicio,
				tab_descontos_concessoes.dec_data_termino AS data_termino,
				tab_descontos_concessoes.des_id AS desconto_id,
				tab_descontos_concessoes.det_id AS tipo_id,
				tab_descontos_concessoes.con_id AS condominio_id
			FROM
				tab_descontos
				INNER JOIN tab_descontos_concessoes ON (tab_descontos.des_id = tab_descontos_concessoes.des_id)
			WHERE
				(".($condominio_id ? "tab_descontos_concessoes.con_id = $condominio_id OR " : "")."tab_descontos_concessoes.pla_id = {$dados_plano["id"]}) AND
				(tab_descontos_concessoes.dcs_id = 5) AND
				(tab_descontos.pli_id = $sistema_id OR tab_descontos.pli_id IS NULL) AND
				(tab_descontos.pls_id = {$dados_plano["servico_id"]} OR tab_descontos.pls_id IS NULL)
			ORDER BY
				tab_descontos_concessoes.con_id DESC,
				tab_descontos_concessoes.dec_ordem ASC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$parametros = unserialize($dados["parametros"]);

			$parametros["carencia"] = $parametros["carencia"] ? $parametros["carencia"] : 0;

			$data_carencia_minima = date('Ymd', strtotime("{$parametros["carencia"]} days ago"));

			if ((!$parametros["carencia"] || $data_carencia_minima >= $data_cancelamento) &&
					(($parametros["aplica_plano_principal"] && $dados_plano["tipo_id"] == 1) ||
					($parametros["aplica_plano_adicional"] && $dados_plano["tipo_id"] == 2)) &&
					(!$parametros["origens"] || in_array($dados_cliente["origem_id"], $parametros["origens"])) &&
					(!$descontos_aplicados || count(array_intersect($parametros["descontos"], $descontos_aplicados)) == 0) &&
					(!$parametros["planos"] || in_array($dados_plano["id"], $parametros["planos"]))) {

				$dados["instalacao"] = $dados["instalacao"] ? $dados["instalacao"] : "NULL";
				$dados["mensalidade"] = $dados["mensalidade"] ? $dados["mensalidade"] : "NULL";

				if ($parametros["validade_tipo"] && $parametros["validade_tipo"] == 2) {
					$dados["validade"] = "NULL";
					$dados["data_termino"] = $parametros["validade"] ? $this->formataData($parametros["validade"]) : "NULL";
				} else {
					$dados["validade"] = $dados["validade"] ? $dados["validade"] : "NULL";
					$dados["data_termino"] = "NULL";
				}

				$this->bancodedados->query("
					INSERT INTO
						tab_descontos_concessoes
					SET
						dec_data_cadastro = CURDATE(),
						des_id = {$dados["desconto_id"]},
						det_id = {$dados["tipo_id"]},
						ass_id = $assinatura_id,
						dec_instalacao = {$dados["instalacao"]},
						dec_mensalidade = {$dados["mensalidade"]},
						dec_fidelidade = {$dados["fidelidade"]},
						dec_validade = {$dados["validade"]},
						dec_data_inicio = NULL,
						dec_data_termino = {$dados["data_termino"]},
						dec_pai = {$dados["id"]},
						dcs_id = 2");

				$id = $this->bancodedados->ultimoIdInserido();

				$this->registraComentarioDesconto($id, "<b>Adicionado</b>: Desconto herdado do ".($dados["condominio_id"] ? "condomínio" : "plano"));

				$descontos[] = $id;
				$descontos_aplicados[] = $dados["desconto_id"];

			}

		}

		return $descontos;

	}

	function formataData($data) {

		return "'".substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2)."'";

	}

	function registraComentarioDesconto($desconto_concessao_id, $conteudo, $responsavel_id = null) {

		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";

		// Descomentar a linha abaixo para registrar o comentário na tabela tab_descontos_concessoes_comentarios
		$this->bancodedados->query("
			INSERT INTO
				tab_descontos_concessoes_comentarios
			SET
				dcc_datahora = NOW(),
				dec_id = $desconto_concessao_id,
				dcc_conteudo = '$conteudo',
				usu_id = $responsavel_id");

	}

	function geraVigencia($dados_assinatura) {

		if ($dados_assinatura["vigencia"]) {
			return "{$dados_assinatura["vigencia"]} meses - Multa Rescisória: ".$this->formataDinheiro($dados_assinatura["multa"], "R$", true);
		} else {
			return "Plano sem fidelidade";
		}

	}

	###########
	### Ver ###
	###########

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$descontos = $this->buscaAssinaturasDescontos($this->dados_item["id"]);

			$botoes = array();
			if (($this->dados_item["status_id"] == 1 || $this->dados_item["status_id"] == 2) && !($this->dados_item["migrada"] && $this->dados_item["status_id"] == 1) && $this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) {
				$assinatura = $this->dados_item["tipo_id"] == "1" ? $this->dados_item["id"] : $this->dados_item["assinatura"]["id"];
				if ($this->buscaPlanosAdicionais($assinatura, $this->dados_item["pfpj"] ? "pf" : "pj", $this->dados_item["ccsc"] ? "cc" : "sc", $this->dados_item["condominio_id"], $this->dados_item["origem_id"])) {
					$botoes[] = '<input type="button" name="adicionar_adicional" class="buttonAzulEscuroBranco11" value="Adicionar Adicional" onClick="adicionarAssinaturaAdicional('.$this->dados_item["cliente_id"].','.$assinatura.')">';
				}
			}
			if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\'">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'">';
			if ($this->dados_item["tipo_id"] != "1") $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			if ($this->dados_item["tipo_id"] == "1") {
				// Principal
				$this->conteudo .= $this->carregaArquivo("assinaturas.pla.ver.inc.php");
				$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
				$this->conteudo = str_replace("[plano]", $this->dados_item["plano_nome"], $this->conteudo);
			} else {
				// Adicional
				$this->conteudo .= $this->carregaArquivo("assinaturas.pld.ver.inc.php");
				$this->conteudo = str_replace("[assinatura]", "<a href=\"?m=assinaturas&a=ver&i={$this->dados_item["assinatura"]["id"]}\" class=\"txtAzulEscuro11\" style=\"text-decoration:underline\">{$this->dados_item["assinatura"]["servico_nome"]} &raquo; {$this->dados_item["assinatura"]["plano_nome"]}</a>", $this->conteudo);
				$this->conteudo = str_replace("[adicional]", $this->dados_item["plano_nome"], $this->conteudo);
			}

			$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[condominio]", $this->dados_item["condominio_id"], $this->conteudo);
			$this->conteudo = str_replace("[sistema]", $this->dados_item["sistema_nome"], $this->conteudo);
			$this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
			$this->conteudo = str_replace("[instalacao]", $this->formataDinheiro($this->dados_item["instalacao"], "R$", true).($descontos && $descontos["assinatura_instalacao"] - $descontos["instalacao"] != $this->dados_item["instalacao"] ? " (".$this->formataDinheiro($descontos["assinatura_instalacao"] - $descontos["instalacao"], "R$", true).")" : ""), $this->conteudo);
			$this->conteudo = str_replace("[mensalidade]", $this->formataDinheiro($this->dados_item["mensalidade"], "R$", true).($descontos && $descontos["assinatura_mensalidade"] - $descontos["mensalidade"] != $this->dados_item["mensalidade"] ? " (".$this->formataDinheiro($descontos["assinatura_mensalidade"] - $descontos["mensalidade"], "R$", true).")" : ""), $this->conteudo);
			$this->conteudo = str_replace("[descontos]", $this->geraListagemAssinaturasDescontos($descontos["listagem"]), $this->conteudo);
			$this->conteudo = str_replace("[descontos_adicionar]", $this->dados_item["status_id"] == 1 && $this->permissoes["descontos_concessoes"]["adicionar"] ? '<input type="button" name="adicionar_desconto" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarDescontoConcessaoAssinatura('.$this->dados_item["id"] .');">' : '', $this->conteudo);
			$this->conteudo = str_replace("[vigencia]", $this->geraVigencia($this->dados_item), $this->conteudo);
			$this->conteudo = str_replace("[data_fechamento]", $this->dados_item["data_fechamento"], $this->conteudo);
			$this->conteudo = str_replace("[data_cadastro]", $this->dados_item["data_cadastro"], $this->conteudo);
			$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
			$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
			$this->conteudo = str_replace("[vendas]", $this->geraListagemVenda($this->buscaVendas($_GET["i"])), $this->conteudo);
			$this->conteudo = str_replace("[comissoes]", $this->geraListagemComissao($this->buscaComissoes($_GET["i"])), $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[dominio_id]", $_GET["dom"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

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

		if ($this->dados_item && $this->dados_item["status_id"] == 1) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$dados_plano = $this->buscaDadosPlano($this->dados_item["plano_id"]);
				$descontos_automaticos = $this->buscaDescontosAutomaticos($this->dados_item["condominio_id"], $this->dados_item["cliente_id"], $dados_plano, $this->dados_item["sistema_id"]);

				$instalacao = $this->formataDinheiro($this->dados_item["instalacao"], "R$", true);
				$mensalidade = $this->formataDinheiro($this->dados_item["mensalidade"], "R$", true);

				if ($descontos_automaticos) {
					$instalacao .= " (".$this->formataDinheiro($this->dados_item["instalacao"] - $descontos_automaticos["instalacao"], "R$", true).")";
					$mensalidade .= " (".$this->formataDinheiro($this->dados_item["mensalidade"] - $descontos_automaticos["mensalidade"], "R$", true).")";
				}

				if ($this->dados_item["tipo_id"] == "1") {
					// Principal
					$this->conteudo .= $this->carregaArquivo("assinaturas.pla.editar.inc.php");
					$this->conteudo = str_replace("[servico_id]", $this->dados_item["servico_id"], $this->conteudo);
					$this->conteudo = str_replace("[servico_nome]", $this->dados_item["servico_nome"], $this->conteudo);
					$this->conteudo = str_replace("[planos]", $this->geraSelect($this->buscaServicosPlanos($this->dados_item["servico_id"], $this->dados_item["pfpj"] ? "pf" : "pj", $this->dados_item["ccsc"] ? "cc" : "sc", $this->dados_item["condominio_id"], $this->dados_item["origem_id"]), $this->dados_item["plano_id"], true, "plano", "100%", "mudaAssinaturaPlano(this)"), $this->conteudo);
				} else {
					// Adicional
					$this->conteudo .= $this->carregaArquivo("assinaturas.pld.editar.inc.php");
					$this->conteudo = str_replace("[assinatura_id]", $this->dados_item["assinatura"]["id"], $this->conteudo);
					$this->conteudo = str_replace("[assinatura_nome]", "<a href=\"?m=assinaturas&a=ver&i=".$this->dados_item["assinatura"]["id"]."\" class=\"txtAzulEscuro11\" style=\"text-decoration:underline\">".$this->dados_item["assinatura"]["servico_nome"].' &raquo; '.$this->dados_item["assinatura"]["plano_nome"]."</a>", $this->conteudo);
					$this->conteudo = str_replace("[planos]", $this->geraSelect($this->buscaPlanosAdicionais($this->dados_item["id"], $this->dados_item["pfpj"] ? "pf" : "pj", $this->dados_item["ccsc"] ? "cc" : "sc", $this->dados_item["condominio_id"], $this->dados_item["origem_id"]), $this->dados_item["plano_id"], true, "adicional", "100%", "mudaAssinaturaAdicional(this)"), $this->conteudo);
				}

				$this->conteudo = str_replace("[sistemas]", $this->geraSelect($this->buscaSistemas($this->dados_item["cliente_id"], $dados_plano["sistema_id"]), $this->dados_item["sistema_id"], true, "sistema", "200px"), $this->conteudo);
				$this->conteudo = str_replace("[instalacao]", $instalacao, $this->conteudo);
				$this->conteudo = str_replace("[mensalidade]", $mensalidade, $this->conteudo);
				$this->conteudo = str_replace("[descontos]", $this->geraListagemAssinaturasDescontos($descontos_automaticos["listagem"]), $this->conteudo);
				$this->conteudo = str_replace("[pfpj]", $this->dados_item["pfpj"], $this->conteudo);
				$this->conteudo = str_replace("[ccsc]", $this->dados_item["ccsc"], $this->conteudo);
				$this->conteudo = str_replace("[action]", '?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace("[origem_id]", $this->dados_item["origem_id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[condominio]", $this->dados_item["condominio_id"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			} else {

				if ($this->dados_item["tipo_id"] == "1") {
					$plano = $_POST["plano"];
				} else {
					$plano = $_POST["adicional"];
				}

				// Restaura Descontos da Assinatura
				$this->restauraDescontosConcessoes($this->dados_item["id"]);

				// Atualiza Plano da Assinatura
				$dados_plano = $this->buscaDadosPlano($plano);

				$this->bancodedados->query("
					UPDATE
						tab_assinaturas
					SET
						pla_id = $plano,
						pli_id = {$_POST["sistema"]},
						ass_parametros = '{$dados_plano["parametros"]}',
						ass_instalacao = {$dados_plano["instalacao"]},
						ass_mensalidade = {$dados_plano["mensalidade"]}
					WHERE
						ass_id = {$_GET["i"]}");

				$assinatura_alterada = $this->bancodedados->linhasAfetadas();

				// Remove desconto condominio anterior
				$this->bancodedados->query("
					DELETE
						rel_dec_cor_estacontido
					FROM
						rel_dec_cor_estacontido
						INNER JOIN tab_descontos_concessoes ON (rel_dec_cor_estacontido.dec_id = tab_descontos_concessoes.dec_id)
					WHERE
						tab_descontos_concessoes.ass_id = {$_GET["i"]} AND
						tab_descontos_concessoes.dec_pai IS NOT NULL");

				$this->bancodedados->query("
					DELETE FROM
						tab_descontos_concessoes
					WHERE
						ass_id = {$_GET["i"]} AND
						dec_pai IS NOT NULL");

				// Re-insere desconto do condominio
				$descontos = $this->insereDescontosAutomaticos($_GET["i"], $this->dados_item["condominio_id"], $this->dados_item["cliente_id"], $dados_plano, $_POST["sistema"]);

				// Recalcula Descontos Assinatura
				$this->recalculaDescontosConcessoes($this->dados_item["id"]);

				// Reassocia descontos com o contrato / altera cor_sincronizado
				if ($this->dados_item["contrato"] && ($assinatura_alterada || $descontos)) {

					foreach ($descontos as $desconto) {
						$this->bancodedados->query("
							INSERT INTO
								rel_dec_cor_estacontido
							SET
								dec_id = $desconto,
								cor_id = {$this->dados_item["contrato"]["id"]}");
					}

					$this->bancodedados->query("
						UPDATE
							tab_contratos
						SET
							cor_sincronizado = 0
						WHERE
							cor_id = {$this->dados_item["contrato"]["id"]} AND
							crs_id IN (11, 12, 13, 14)");

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
	### Remover ###
	###############

	function remover() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehRemovivel()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

				$this->conteudo .= $this->carregaArquivo("assinaturas.remover.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->removerAssinatura($this->dados_item);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

				if ($this->dados_item["tipo_id"] == "1") {
					$this->fechar();
				} else {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["assinatura"]["id"]}");
				}

			}

		} else {
			$this->fechar();
		}

	}

	function removerAssinatura($dados_assinatura) {

		if ($dados_assinatura["contrato"]) {
			// Remove Link Assinatura/Contrato
			$this->bancodedados->query("DELETE FROM rel_ass_cor_estacontido WHERE ass_id = {$dados_assinatura["id"]}");
			// Remove Link Desconto Concessão/Contrato
			$this->bancodedados->query("DELETE rel_dec_cor_estacontido FROM rel_dec_cor_estacontido INNER JOIN tab_descontos_concessoes ON (rel_dec_cor_estacontido.dec_id = tab_descontos_concessoes.dec_id) WHERE tab_descontos_concessoes.ass_id = {$dados_assinatura["id"]}");
			// Define Contrato como Não Sincronizado
			$this->bancodedados->query("UPDATE tab_contratos SET cor_sincronizado = 0 WHERE cor_id = {$dados_assinatura["contrato"]["id"]} AND crs_id IN (11, 12, 13, 14)");
		}

		// Remove Link Ajuste PRogramado
		$this->bancodedados->query("DELETE FROM rel_saj_ass_contem WHERE ass_id = {$dados_assinatura["id"]}");

		// Remove Descontos Concessões
		$this->bancodedados->query("DELETE tab_descontos_concessoes_comentarios FROM tab_assinaturas INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id) INNER JOIN tab_descontos_concessoes_comentarios ON (tab_descontos_concessoes.dec_id = tab_descontos_concessoes_comentarios.dec_id) WHERE tab_assinaturas.ass_id = {$dados_assinatura["id"]}");
		$this->bancodedados->query("DELETE tab_descontos_concessoes_comentarios FROM tab_assinaturas INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id) INNER JOIN tab_descontos_concessoes_comentarios ON (tab_descontos_concessoes.dec_id = tab_descontos_concessoes_comentarios.dec_id) WHERE tab_assinaturas.ass_pai = {$dados_assinatura["id"]}");
		$this->bancodedados->query("DELETE tab_descontos_concessoes FROM tab_assinaturas INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id) WHERE tab_assinaturas.ass_id = {$dados_assinatura["id"]}");
		$this->bancodedados->query("DELETE tab_descontos_concessoes FROM tab_assinaturas INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id) WHERE tab_assinaturas.ass_pai = {$dados_assinatura["id"]}");

		// Remove Ajustes Programados - Regras/Comentários
		$this->bancodedados->query("
			DELETE
				tab_servicos_ajustes_comentarios,
				tab_servicos_ajustes_regras,
				rel_saj_ass_contem
			FROM
				tab_servicos_ajustes
				LEFT OUTER JOIN tab_servicos_ajustes_comentarios ON (tab_servicos_ajustes_comentarios.saj_id = tab_servicos_ajustes.saj_id)
				LEFT OUTER JOIN tab_servicos_ajustes_regras ON (tab_servicos_ajustes_regras.saj_id = tab_servicos_ajustes.saj_id)
				LEFT OUTER JOIN rel_saj_ass_contem ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id)
			WHERE
				tab_servicos_ajustes.ass_id = {$dados_assinatura["id"]}");

		// Remove Ajustes Programados
		$this->bancodedados->query("DELETE FROM tab_servicos_ajustes WHERE ass_id = {$dados_assinatura["id"]}");

		// Remove Dominios
		$this->bancodedados->query("DELETE FROM tab_dominios WHERE ass_id = {$dados_assinatura["id"]}");

		// Remove Comentarios Pontos
		$this->bancodedados->query("DELETE tab_clientes_pontos_comentarios FROM tab_clientes_pontos INNER JOIN tab_clientes_pontos_comentarios ON (tab_clientes_pontos.clp_id = tab_clientes_pontos_comentarios.clp_id) WHERE ass_id = {$dados_assinatura["id"]}");

		// Remove Pontos
		$this->bancodedados->query("DELETE FROM tab_clientes_pontos WHERE ass_id = {$dados_assinatura["id"]}");

		// Remove E-Mails
		$this->removeEmails($dados_assinatura["id"]);

		// Remove Venda
		if ($dados_assinatura["venda_id"]) {

			// Remove Comissoes Venda
			$this->bancodedados->query("DELETE tab_vendas_comissoes FROM tab_vendas INNER JOIN tab_vendas_comissoes ON (tab_vendas.ven_id = tab_vendas_comissoes.ven_id) WHERE ass_id = {$dados_assinatura["id"]}");

			// Remove Venda
			$this->bancodedados->query("DELETE FROM tab_vendas WHERE ass_id = {$dados_assinatura["id"]}");

		}

		// Remove Assinaturas Filho
		$assinaturas_filho = $this->buscaAssinaturasFilho($dados_assinatura["id"]);
		foreach ($assinaturas_filho as $assinatura_filho) {
			$this->removerAssinatura($this->buscaDadosAssinatura($assinatura_filho["id"]));
		}

		// Remove Vinculo
		$this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_id = {$dados_assinatura["id"]} OR ass_filha = {$dados_assinatura["id"]}");

		// Remove Assinaturas Vinculadas
		foreach ($dados_assinatura["vinculos"] as $vinculo) {
			if (!$vinculo["contrato"] && count($vinculo["vinculos_pai"]) == 1) {
				$this->removerAssinatura($this->buscaDadosAssinatura($vinculo["assinatura_id"]));
			}
		}

		// Remove Planos Proxy vinculados
		$this->bancodedados->query("DELETE FROM tab_assinaturas_planos_proxy WHERE ass_id = {$dados_assinatura["id"]}");

		// Remove Assinatura
		$this->bancodedados->query("
			DELETE FROM
				tab_assinaturas
			WHERE
				tab_assinaturas.ass_id = {$dados_assinatura["id"]}");

		// Atualiza Status Cliente
		$this->atualizaStatusCliente($dados_assinatura["cliente_id"]);

	}

	function atualizaStatusCliente($cliente_id) {

		$clientes = controle::instanciaObjeto("clientes", $this->bancodedados, $this->permissoes, false);
		$clientes->atualizaStatusCliente($cliente_id);

	}

	function removeEmails($id) {

		$emails = $this->buscaEmailsAssinatura($id);
		$icable = null;
		$wavesystem = null;

		foreach ($emails as $email) {
			if ($email["dominio"] == 'icablenet.com.br') {
				if (!$icable) $icable = controle::instanciaObjeto("emails_icable");
				$removido = $icable->removerEmail($email["dominio"], $email["login"]);
			} else {
				if (!$wavesystem) $wavesystem = controle::instanciaObjeto("emails_wavesystem");
				$removido = $wavesystem->removerEmail($email["login"]."@".$email["dominio"]);
			}
			if ($removido) {
				$this->bancodedados->query("DELETE FROM tab_clientes_emails_comentarios WHERE cle_id = {$email["id"]}");
				$this->bancodedados->query("DELETE FROM tab_clientes_emails WHERE cle_id = {$email["id"]}");
			}
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
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");

		// Cadastro Início
		$itens[] = array ("tipo" => "input", "nome" => "data_cadastro_inicio", "descricao" => "Cadastro &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_cadastro >= '".substr($valores["data_cadastro_inicio"], 6, 4)."-".substr($valores["data_cadastro_inicio"], 3, 2)."-".substr($valores["data_cadastro_inicio"], 0, 2)."')");

		// Cadastro Término
		$itens[] = array ("tipo" => "input", "nome" => "data_cadastro_termino", "descricao" => "Cadastro &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_cadastro <= '".substr($valores["data_cadastro_termino"], 6, 4)."-".substr($valores["data_cadastro_termino"], 3, 2)."-".substr($valores["data_cadastro_termino"], 0, 2)."')");

		// Condomínio
		$itens[] = array ("tipo" => "input", "nome" => "condominio", "descricao" => "Condomínio", "valor" => "", "where" => "(tab_condominios.con_nome LIKE '%[valor]%')");

		// Fechamento Início
		$itens[] = array ("tipo" => "input", "nome" => "data_fechamento_inicio", "descricao" => "Fechamento &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_fechamento >= '".substr($valores["data_fechamento_inicio"], 6, 4)."-".substr($valores["data_fechamento_inicio"], 3, 2)."-".substr($valores["data_fechamento_inicio"], 0, 2)."')");

		// Fechamento Término
		$itens[] = array ("tipo" => "input", "nome" => "data_fechamento_termino", "descricao" => "Fechamento &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_fechamento <= '".substr($valores["data_fechamento_termino"], 6, 4)."-".substr($valores["data_fechamento_termino"], 3, 2)."-".substr($valores["data_fechamento_termino"], 0, 2)."')");

		// Planos
		$servicos = $this->buscaServicos();
		$select_servico = array();
		foreach ($servicos as $id => $nome) {
			$select_servico[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "servico", "descricao" => "Serviço", "valor" => $select_servico, "where" => "(tab_planos.pls_id = [valor])");

		// Ativacao Início
		$itens[] = array ("tipo" => "input", "nome" => "data_ativacao_inicio", "descricao" => "Ativacão &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_ativacao >= '".substr($valores["data_ativacao_inicio"], 6, 4)."-".substr($valores["data_ativacao_inicio"], 3, 2)."-".substr($valores["data_ativacao_inicio"], 0, 2)."')");

		// Ativacao Término
		$itens[] = array ("tipo" => "input", "nome" => "data_ativacao_termino", "descricao" => "Ativacão &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_ativacao <= '".substr($valores["data_ativacao_termino"], 6, 4)."-".substr($valores["data_ativacao_termino"], 3, 2)."-".substr($valores["data_ativacao_termino"], 0, 2)."')");

		// Sistema
		$sistemas = $this->buscaSistemas();
		$select_sistemas = array();
		foreach ($sistemas as $id => $nome) {
			$select_sistemas[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema", "valor" => $select_sistemas, "where" => "(tab_assinaturas.pli_id = [valor])");

		// Cancelamento Início
		$itens[] = array ("tipo" => "input", "nome" => "data_cancelamento_inicio", "descricao" => "Cancelamento &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_cancelamento >= '".substr($valores["data_cancelamento_inicio"], 6, 4)."-".substr($valores["data_cancelamento_inicio"], 3, 2)."-".substr($valores["data_cancelamento_inicio"], 0, 2)."')");

		// Cancelamento Término
		$itens[] = array ("tipo" => "input", "nome" => "data_cancelamento_termino", "descricao" => "Cancelamento &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_cancelamento <= '".substr($valores["data_cancelamento_termino"], 6, 4)."-".substr($valores["data_cancelamento_termino"], 3, 2)."-".substr($valores["data_cancelamento_termino"], 0, 2)."')");

		// Status
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$select_status[] = array ("id" => "-1", "nome" => "Inativa", "where" => "(tab_assinaturas.ast_id IN (3, 4, 6))");
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_assinaturas.ast_id = [valor])");

		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);

	}

	function buscaItens($filtro, $pagina) {

		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.cli_id AS cliente_id,
				tab_assinaturas.ass_instalacao AS instalacao,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas_status.ast_nome AS status,
				DATE_FORMAT(tab_assinaturas.ass_data_cadastro, '%d/%m/%Y') AS data_cadastro,
				DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%d/%m/%Y') AS data_fechamento,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%d/%m/%Y') AS data_cancelamento,
				tab_clientes.cli_nome AS cliente,
				tab_condominios.con_nome AS condominio,
				tab_planos.pla_nome AS plano
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
			WHERE
				$filtro->parametros_sql AND
				tab_clientes.cli_data_cadastro IS NOT NULL".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				tab_clientes.cli_nome ASC,
				tab_assinaturas.ass_data_ativacao ASC
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes["clientes"]["ver_dm"]) {
				$dados["link"] = "?m=clientes&a=ver_dm&i={$dados["cliente_id"]}";
			}
			if (strlen($dados["cliente"]) > 30) {
				$dados["cliente"] = substr($dados["cliente"], 0, 27)."...";
			}
			$dados["instalacao"] = $this->formataDinheiro($dados["instalacao"], "R$", true);
			$dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"], "R$", true);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			$dados["datas"] = array();

			if ($dados["data_fechamento"]) {
				$dados["datas"][] = "F: {$dados["data_fechamento"]}";
			} else {
				$dados["datas"][] = "C: {$dados["data_cadastro"]}";
			}
			if ($dados["data_ativacao"]) {
				$dados["datas"][] = "A: {$dados["data_ativacao"]}";
			}
			if ($dados["data_cancelamento"]) {
				$dados["datas"][] = "C: {$dados["data_cancelamento"]}";
			}

			$dados["datas"] = implode("<br>", $dados["datas"]);

			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagem($itens) {

		$cabecalho = array("contador" => "#", "cliente" => "Cliente", "datas" => "Datas", "plano" => "Plano", "instalacao" => "Adesão", "mensalidade" => "Mensalidade", "status" => "Status");

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 0, 90), null, array("right", "left", "center", "center", "right", "right"));

	}

	function buscaComissoes($assinatura_id) {

		$itens = array();
		$total = 0;

		$query = $this->bancodedados->query("
			SELECT
				tab_vendas_comissoes.vec_id AS id,
				tab_vendas_comissoes.vec_valor AS valor,
				tab_vendas_comissoes_tipos.vct_nome AS tipo,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_assinaturas
				INNER JOIN tab_vendas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
				INNER JOIN tab_vendas_comissoes ON (tab_vendas.ven_id = tab_vendas_comissoes.ven_id)
				INNER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes.vct_id = tab_vendas_comissoes_tipos.vct_id)
				INNER JOIN tab_usuarios ON (tab_vendas_comissoes.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id
			ORDER BY
				vec_valor DESC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes["vendas_comissoes"]["ver"]) {
				$dados["link"] = "javascript:verVendasComissoes('{$dados["id"]}');";
			}
			$dados["valor"] = $this->formataDinheiro($dados["valor"], "R$", true);

			$total += $dados["valor"];

			$itens[] = $dados;

		}

		if ($total) {
			$itens[] = array("nome" => "<b>Total</b>", "valor" => "<b>".$this->formataDinheiro($total, "R$", true)."</b>");
		}

		return $itens;

	}

	function geraListagemComissao($itens) {

		$cabecalho = array(
			"nome" => "Nome",
			"tipo" => "Tipo"
		);

		if ($this->permissoes["vendas_comissoes"]["listar"]) {
			$cabecalho["valor"] = "Valor";
		}

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);

		return $listagem->conteudo;

	}

	function buscaVendas($assinatura_id) {

		$itens = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_vendas.ven_id AS id,
				tab_vendas.ven_pontuacao AS pontuacao,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_assinaturas
				INNER JOIN tab_vendas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
				INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes["vendas"]["ver"]) {
				$dados["link"] = "javascript:verVendas('{$dados["id"]}');";
			}

			$itens[] = $dados;

		}

		return $itens;

	}

	function geraListagemVenda($itens) {

		$cabecalho = array("nome" => "Nome", "pontuacao" => "Pontuação");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);

		return $listagem->conteudo;

	}

}

?>
