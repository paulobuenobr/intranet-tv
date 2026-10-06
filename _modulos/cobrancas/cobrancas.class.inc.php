<?php

require_once("_global/modulo.class.inc.php");

class cobrancas extends modulo {

    function __construct($bancodedados, $permissoes = null, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		$this->buscaDadosModulo("cobrancas");

		if ($executar) {
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "buscar");
            if($_GET['a'] == "pesquisa_boleto") {
                $this->dados_modulo["descricao"] = "Boletos";
            }
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}

	}

	function buscaDadosItem($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.gerado_nota AS gerado_nota,
				tab_cobrancas.coa_grupo_id AS grupo_id,
				IF(tab_cobrancas.col_id IS NULL, 1, 0) AS geracao_manual,
				tab_cobrancas_tipos.cop_id AS tipo_id,
				tab_cobrancas_tipos.cop_nome AS tipo_nome,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj,
				tab_clientes.cli_inscricao_estadual AS cliente_inscricao_estadual,
				tab_clientes.clt_id AS cliente_tipo,
				tab_clientes.clo_id AS origem_id,
				tab_clientes.cli_bloqueio_dias AS bloqueio_dias,
				tab_clientes.cof_id AS cliente_formato_id,
				tab_estados.est_sigla AS estado,
				tab_cobrancas_lotes.col_id AS lote_id,
				tab_cobrancas_lotes.col_nome AS lote_nome,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				tab_cobrancas_formatos.cft_id AS formato_tipo_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_nosso_numero AS nosso_numero,
				tab_cobrancas.coa_data_bloqueio AS data_bloqueio,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m/%Y') AS vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_geracao, '%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento,
				DATE_FORMAT(tab_cobrancas.coa_data_processamento, '%d/%m/%Y') AS data_processamento,
				DATE_FORMAT(tab_cobrancas.coa_data_credito, '%d/%m/%Y') AS data_credito,
				IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas.coa_data_vencimento < CURDATE(), 1, 0) AS em_atraso,
				IF(tab_cobrancas.coa_valor_bonificacao && tab_cobrancas.coa_data_vencimento >= CURDATE(), tab_cobrancas.coa_valor_original - tab_cobrancas.coa_valor_bonificacao, NULL) AS valor_final,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_juro AS valor_juro,
				tab_cobrancas.coa_valor_desconto AS valor_desconto,
				tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo,
				tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_cobrancas.fat_id AS fatura_id,
				(SELECT fap_id FROM tab_faturas WHERE tab_faturas.fat_id = tab_cobrancas.fat_id) AS fatura_tipo_id,
				(SELECT fap_nome FROM tab_faturas INNER JOIN tab_faturas_tipos ON (tab_faturas.fap_id = tab_faturas_tipos.fap_id) WHERE tab_faturas.fat_id = tab_cobrancas.fat_id) AS fatura_tipo_nome,
				tab_cobrancas.coa_enviada AS enviada,
				tab_cobrancas_status.cot_id AS status_id,
				tab_cobrancas_status.cot_recebido AS status_recebido,
				tab_cobrancas_status.cot_manual AS status_manual,
				tab_cobrancas_lotes.cla_id AS lote_status_id,
                IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas.coa_aguardando_registro = 1, 1, 0) AS coa_aguardando_registro, 
                tab_cobrancas_formatos.cof_parametros AS formatos_parametros,
				IF(tab_cobrancas_lotes.cla_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6), tab_cobrancas_status.cot_nome, CONCAT(tab_cobrancas_status.cot_nome, ' (Lote Inativo)')) AS status_nome,
				IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas_formatos.cof_remessa IS NOT NULL AND (SELECT COUNT(*) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cri_nosso_numero = tab_cobrancas.coa_nosso_numero AND tab_cobrancas_remessas_itens.cri_status IN ('REGISTRADA', 'REJEITADA') AND tab_cobrancas_remessas_itens.cof_id = tab_cobrancas_formatos.cof_remessa) = 0, 1, 0) AS aguardando_registro,
				IF((SELECT COUNT(*) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cri_nosso_numero = tab_cobrancas.coa_nosso_numero AND tab_cobrancas_remessas_itens.cof_id = tab_cobrancas_formatos.cof_remessa) = 1, 1, 0) AS com_remessa
			FROM
				tab_cobrancas
				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
				INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
				INNER JOIN tab_cobrancas_tipos ON (tab_cobrancas.cop_id = tab_cobrancas_tipos.cop_id)
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_ceps ON (tab_clientes.cep_id = tab_ceps.cep_id)
				INNER JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)
			WHERE
				tab_cobrancas.coa_id = $cobranca_id".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));
		
		if (($this->dados_item = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->dados_item["vencimento"]) $this->dados_item["formato_nome"] .= " - ".$this->formataData($this->dados_item["vencimento"]);
			if ($this->dados_item["tipo_id"] != 1) $this->dados_item["formato_nome"] .= " - {$this->dados_item["tipo_nome"]}";
			if (!$this->dados_item["lote_id"]) $this->dados_item["formato_nome"] .= ' - Cobrança Avulsa';
			if ($this->dados_item["coa_pai"]) $this->dados_item["formato_nome"] .= ' (Segunda Via)';
                        
            $this->dados_item["formatos_parametros"] = unserialize($this->dados_item["formatos_parametros"]);
            if(@$this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
                $this->dados_item["aguardando_registro"] = $this->dados_item["coa_aguardando_registro"];
                if ($this->dados_item["coa_aguardando_registro"]) $this->dados_item["status_nome"] .= ' (Aguardando registro)';
            } else {
				if ($this->dados_item["aguardando_registro"]) $this->dados_item["status_nome"] .= ' (Aguardando registro)';
            }

			$this->dados_item["parametros"] = unserialize($this->dados_item["parametros"]);

			$this->dados_item["competencia"] = str_replace("<br>", ", ", $this->buscaCobrancaCompetencia($cobranca_id, $this->dados_item["tipo_id"]));
			$this->dados_item["cliente_inscricao_estadual"] = $this->dados_item["cliente_inscricao_estadual"] == "-1" ? "" : $this->dados_item["cliente_inscricao_estadual"];

			if ($this->dados_item["fatura_id"]) {
				$this->dados_item["fatura"] = $this->buscaFatura($this->dados_item["fatura_id"]);
			}

		}

	}
	
	function ehRegistrada($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(*) 
			FROM 
				tab_cobrancas_remessas_itens 
			WHERE 
				coa_id = $cobranca_id AND 
				cri_status = 'REGISTRADA'");
				
		$registrada = (int) $this->bancodedados->resultado($query);

		$query = $this->bancodedados->query("
			SELECT
				COUNT(*) 
			FROM 
				tab_cobrancas_remessas_itens 
			WHERE 
				coa_id = $cobranca_id AND 
				cri_status = 'BAIXADA'");
		
		$baixada = (int) $this->bancodedados->resultado($query);
		
		return $registrada > 0 && $baixada == 0;
		
	}

	function buscaFatura($fatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_faturas.fat_id AS id,
				tab_faturas.fap_id AS tipo_id,
				tab_faturas.fat_codigo AS codigo,
				DATE_FORMAT(tab_faturas.fat_data_emissao, '%d/%m/%Y') AS data_emissao,
				DATE_FORMAT(tab_faturas.fat_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_faturas.fat_serie AS serie,
				tab_faturas.fat_natureza AS natureza,
				tab_faturas.fat_numero AS numero,
				tab_faturas.fat_valor AS valor,
				tab_faturas.fat_md5 AS md5,
				tab_faturas.fal_id AS lote_id,
				tab_faturas.fat_cancelada AS cancelada,
				tab_faturas.emp_id AS empresa_id,
				CONCAT(DATE_FORMAT(tab_faturas_lotes.fal_data_ciclo, '%d/%m/%Y'), ' - ', LPAD(tab_faturas_lotes.fal_ciclo_sequencia, 2, '0')) AS ciclo_formatado,
				tab_faturas_lotes.fal_ciclo AS ciclo
			FROM
				tab_faturas
				LEFT OUTER JOIN tab_faturas_lotes ON (tab_faturas.fal_id = tab_faturas_lotes.fal_id)
			WHERE
				tab_faturas.fat_id = $fatura_id");
		
		return $this->bancodedados->resultadoArray($query);

	}

	function buscaCobrancaCompetencia($cobranca_id, $tipo_id) {

		if ($tipo_id == 3) {
			return "Semestral";
		} elseif ($tipo_id == 4) {
			return "Anual";
		}

		$competencias = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
			FROM
				tab_cobrancas_detalhes
			WHERE
				coa_id = $cobranca_id AND
				cod_competencia_inicio IS NOT NULL
			ORDER BY
				cod_competencia_inicio");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$competencias[] = $this->formataData($dados["competencia"]);
		}

		return implode("<br>", $competencias);

	}

	function formataData($vencimento, $ano_completo = true) {

		$meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
		$vencimento = explode("/", $vencimento);
		
		if (!$ano_completo) {
			$vencimento[1] = substr($vencimento[1], -2);
		}
		
		return $meses[intval($vencimento[0])]."/".$vencimento[1];

	}

	function buscaDadosCliente($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cli_id AS id,
				cli_nome AS nome,
				clo_id AS cliente_origem_id,
				cli_cpf_cnpj AS cpf_cnpj,
				cof_id AS formato_id,
				LPAD(cli_ccd_vencimento, 2, '0') AS dia_vencimento,
				clo_id AS origem_id
			FROM
				tab_clientes
			WHERE
				cli_id = $cliente_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["emails"] = $this->buscaContatosEmails($dados["id"]);
			return $dados;
		}

	}

	function buscaDadosMensagensLotes($lote_id) {

		$query = $this->bancodedados->query("
			SELECT
				mlm_id AS modelo_id,
				mel_parametros AS parametros
			FROM
				tab_mensagens_lotes
			WHERE
				mel_id = $lote_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

	}

	function buscaContatosEmails($cliente_id) {

		$emails = array();

		$query = $this->bancodedados->query("
			SELECT
				CONCAT(ccm_usuario, '@', ccm_dominio) AS email
			FROM
				tab_clientes_contatos_emails
			WHERE
				cli_id = $cliente_id
			ORDER BY
				email");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[] = $dados["email"];
		}

		return $emails;

	}

	function buscaDadosContatoTipo($contato_tipo_id) {

		$query = $this->bancodedados->query("
			SELECT
				rct_id AS id,
				rct_nome AS nome
			FROM
				tab_recuperacao_contatos_tipos
			WHERE
				rct_id = $contato_tipo_id");
		
		return $this->bancodedados->resultadoArray($query);

	}

	function buscaCobrancasFilho($cobranca_id) {

		$cobrancas = array();

		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id
			FROM
				tab_cobrancas
			WHERE
				coa_pai = $cobranca_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cobrancas[] = $dados["id"];
		}

		return $cobrancas;

	}

	###################
	### Busca Itens ###
	###################

	function buscaFormatos($status = "%", $avulsa = false, $cliente_origem_id = null, $formato_atual_id = null) {

		$formatos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_formatos.cof_id AS id,
				tab_cobrancas_formatos.cof_nome AS nome
			FROM
				tab_cobrancas_formatos
				LEFT OUTER JOIN rel_cof_clo_disponivel ON (tab_cobrancas_formatos.cof_id = rel_cof_clo_disponivel.cof_id)
			WHERE
				(tab_cobrancas_formatos.cfs_id LIKE '$status'".($cliente_origem_id ? " AND
				 rel_cof_clo_disponivel.clo_id = $cliente_origem_id" : "").($avulsa ? " AND
				 tab_cobrancas_formatos.cft_id IN (1, 3)" : "").")".($formato_atual_id ? " OR
				tab_cobrancas_formatos.cof_id = $formato_atual_id" : "")."
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formatos[$dados["id"]] = $dados["nome"];
		}

		return $formatos;

	}

	function buscaLotes() {

		$lotes = array();

		$query = $this->bancodedados->query("
			SELECT
				col_id AS id,
				col_nome AS nome
			FROM
				tab_cobrancas_lotes
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lotes[$dados["id"]] = $dados["nome"];
		}

		return $lotes;

	}

	function buscaCondominios() {

		$condominios = array();

		$query = $this->bancodedados->query("
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
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$condominios[$dados["id"]] = $dados["nome"];
		}

		return $condominios;

	}

	function buscaStatus() {

		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				cot_id AS id,
				cot_nome AS nome
			FROM
				tab_cobrancas_status
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}

		return $status;

	}

	function buscaClientesOrigens() {

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			WHERE
				1".($_SESSION["origem"] ? " AND
				clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[] = $dados;
		}

		return $origens;

	}

	function buscaOrigens() {

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			WHERE
				1".($_SESSION["origem"] ? " AND
				clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[$dados["id"]] = $dados["nome"];
		}

		return $origens;

	}

	function buscaStatusManual() {

		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				cot_id AS id,
				cot_nome AS nome
			FROM
				tab_cobrancas_status
			WHERE
				cot_id IN (".($this->temPermissao("dar_baixa_plus") ? "SELECT cot_id FROM tab_cobrancas_status WHERE cot_manual = 1" : "4").")
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}

		return $status;

	}

	function buscaContatos($cobranca_id) {

		$contatos = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_usuarios.usu_nome AS responsavel,
				DATE_FORMAT(tab_recuperacao_contatos.rec_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_recuperacao_contatos.rec_comentario AS comentario,
				tab_recuperacao_contatos_tipos.rct_nome AS tipo
			FROM
				tab_recuperacao_contatos_tipos
				INNER JOIN tab_recuperacao_contatos ON (tab_recuperacao_contatos_tipos.rct_id = tab_recuperacao_contatos.rct_id)
				INNER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_recuperacao_contatos.usu_id)
			WHERE
				tab_recuperacao_contatos.coa_id = $cobranca_id AND
				tab_recuperacao_contatos.rec_termino IS NOT NULL
			UNION SELECT DISTINCT
				tab_usuarios.usu_nome AS responsavel,
				DATE_FORMAT(tab_recuperacao_contatos.rec_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_recuperacao_contatos.rec_comentario AS comentario,
				tab_recuperacao_contatos_tipos.rct_nome AS tipo
			FROM
				tab_recuperacao_contatos_tipos
				INNER JOIN tab_recuperacao_contatos ON (tab_recuperacao_contatos_tipos.rct_id = tab_recuperacao_contatos.rct_id)
				INNER JOIN tab_cobrancas ON (tab_recuperacao_contatos.rec_id = tab_cobrancas.rec_id)
				INNER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_recuperacao_contatos.usu_id)
			WHERE
				tab_cobrancas.coa_id = $cobranca_id AND
				tab_recuperacao_contatos.rec_termino IS NOT NULL AND
				(tab_cobrancas.coa_data_processamento IS NULL OR tab_cobrancas.coa_data_processamento < tab_recuperacao_contatos.rec_termino)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["comentario"] = $dados["comentario"] ? nl2br(addslashes($dados["comentario"])) : "Não Possui";
			$contatos[] = $dados;
		}

		return $contatos;

	}

	function buscaDadosFormato($formato_id) {

		$query = $this->bancodedados->query("
			SELECT
				cof_id AS formato_id,
				cof_nome AS nome,
				cof_parametros AS parametros,
				cof_remessa AS remessa
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id = $formato_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

	}

	function buscaFormatoBoleto($origem_id) {

		$query = $this->bancodedados->query("
			SELECT
				cof_id
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id iN (
					SELECT
						cof_id
					FROM
						rel_cof_clo_disponivel
					WHERE
						clo_id = $origem_id
				) AND
				cft_id = 3 AND
				cfs_id = 1
			LIMIT
				0, 1");

		return $this->bancodedados->resultado($query);

	}

	function buscaVencimentos($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(MIN(IF(tab_assinaturas.pli_id = 2, tab_cobrancas_detalhes.cod_competencia_inicio, DATE_ADD(tab_cobrancas_detalhes.cod_competencia_inicio, INTERVAL 1 MONTH))), '%m/%Y') AS inicio,
				DATE_FORMAT(MAX(IF(tab_assinaturas.pli_id = 2, tab_cobrancas_detalhes.cod_competencia_termino, DATE_ADD(tab_cobrancas_detalhes.cod_competencia_termino, INTERVAL 1 MONTH))), '%m/%Y') AS termino
			FROM
				tab_cobrancas_detalhes
				LEFT OUTER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_cobrancas_detalhes.cod_pai IS NULL AND
				tab_cobrancas_detalhes.coa_id = $cobranca_id");
		
		return $this->bancodedados->resultadoArray($query);

	}

	function buscaContatosTipos() {

		$contatos_tipos = array();

		$query = $this->bancodedados->query("
			SELECT
				rct_id AS id,
				rct_nome AS nome
			FROM
				tab_recuperacao_contatos_tipos
			ORDER BY
				id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contatos_tipos[$dados["id"]] = $dados["nome"];
		}

		return $contatos_tipos;

	}

	function buscaClientesOperacoes($cliente_id, $vencimento, $processamento) {

		$contatos = array();
		$contato_inicio = substr($vencimento, 6, 4)."-".substr($vencimento, 3, 2)."-".substr($vencimento, 0, 2)." 00:00:00";
		$cabecalho = array("detalhamento" => "Detalhamento");

		if ($processamento) {
			$contato_termino = substr($processamento, 6, 4)."-".substr($processamento, 3, 2)."-".substr($processamento, 0, 2)." 23:59:59";
			$filtro = "tab_operacoes_contatos.opc_inicio BETWEEN '$contato_inicio' AND '$contato_termino'";
		} else {
			$filtro = "tab_operacoes_contatos.opc_inicio >= '$contato_inicio'";
		}

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(tab_operacoes_contatos.opc_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
				SEC_TO_TIME(UNIX_TIMESTAMP(tab_operacoes_contatos.opc_termino) - UNIX_TIMESTAMP(tab_operacoes_contatos.opc_inicio)) AS duracao,
				tab_operacoes_contatos.opc_comentario AS comentario,
				tab_operacoes_contatos_categorias.occ_nome AS categoria,
				tab_usuarios.usu_nome AS responsavel
			FROM
				tab_operacoes_contatos
				INNER JOIN tab_operacoes_contatos_categorias ON (tab_operacoes_contatos.occ_id = tab_operacoes_contatos_categorias.occ_id)
				INNER JOIN tab_usuarios ON (tab_operacoes_contatos.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_operacoes_contatos.cli_id = $cliente_id AND
				tab_operacoes_contatos.opc_termino IS NOT NULL AND
				tab_operacoes_contatos_categorias.oli_id = 2 AND
				$filtro
			ORDER BY
				tab_operacoes_contatos.opc_termino");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["detalhamento"] = preg_replace("/[\r\n\t ]+/", " ", "
				<b>{$dados["responsavel"]}</b> em <b>{$dados["data_hora"]}</b><br>
				<b>Duração:</b> {$dados["duracao"]}<br>
				<b>Categoria:</b> {$dados["categoria"]}<br>
				<b>Conteúdo:</b> {$dados["comentario"]}<br>");
			
			$contatos[] = $dados;
			
		}

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $contatos);

		return $listagem->conteudo;

	}

	function quantidadeSegundasVias($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id)
			FROM
				tab_cobrancas
			WHERE
				coa_pai = $cobranca_id");
		
		return $this->bancodedados->resultado($query);

	}

	function buscaAjustesEstornoMigracaoCancelaveis($cobranca_id) {

		$ajustes = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_ajustes_financeiros.ajf_id AS id
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ass_id = tab_ajustes_financeiros.ass_id)
				LEFT OUTER JOIN tab_cobrancas_detalhes tab_cobrancas_detalhes_ajustes ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes_ajustes.ajf_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_ajustes_financeiros.afc_id = 13 AND
				tab_ajustes_financeiros.afs_id IN (5, 7) AND
				tab_ajustes_financeiros.ajf_competencia_inicio >= tab_cobrancas_detalhes.cod_competencia_inicio AND
				tab_cobrancas_detalhes_ajustes.cod_id IS NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}

		return $ajustes;

	}

	function buscaAjusteEstornoPendente($cobranca_id, $vencimento) {

		$vencimento_inicio = date("Y-m-d", strtotime(substr($vencimento, 6, 4)."-".substr($vencimento, 3, 2)."-01 1 MONTH AGO"));
		$vencimento_termino = date("Y-m-d", strtotime(substr($vencimento, 6, 4)."-".substr($vencimento, 3, 2)."-01"));

		$query = $this->bancodedados->query("
			SELECT
				tab_ajustes_financeiros.ajf_id
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ass_id = tab_ajustes_financeiros.ass_id)
				LEFT OUTER JOIN tab_cobrancas_detalhes tab_cobrancas_detalhes_ajustes ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes_ajustes.ajf_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_ajustes_financeiros.afc_id = 13 AND
				tab_ajustes_financeiros.afs_id IN (5, 7) AND
				tab_ajustes_financeiros.ajf_competencia_inicio BETWEEN '$vencimento_inicio' AND '$vencimento_termino' AND
				tab_cobrancas_detalhes_ajustes.cod_id IS NULL");

		return $this->bancodedados->resultado($query);

	}

	function cobrancaAssinaturaCancelada($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_assinaturas.ass_id)
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_assinaturas.ast_id IN (3, 4, 5) AND
				tab_assinaturas.ass_data_cancelamento < tab_cobrancas_detalhes.cod_competencia_termino");

		return $this->bancodedados->resultado($query);

	}

	function buscaAjusteEstornosPendentes($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_ajustes_financeiros.ajf_id
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ass_id = tab_ajustes_financeiros.ass_id)
				LEFT OUTER JOIN tab_cobrancas_detalhes tab_cobrancas_detalhes_ajustes ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes_ajustes.ajf_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_ajustes_financeiros.afc_id = 13 AND
				tab_ajustes_financeiros.afs_id IN (5, 7) AND
				tab_cobrancas_detalhes_ajustes.cod_id IS NULL");

		return $this->bancodedados->resultado($query);

	}

	function loteFaturaGerado($fatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_faturas_lotes.fls_id
			FROM
				tab_faturas_lotes
			WHERE
				tab_faturas_lotes.fal_id = (SELECT fal_id FROM tab_faturas WHERE fat_id = $fatura_id)");

		return $this->bancodedados->resultado($query) > 1;

	}

	##################
	### Permissões ###
	##################

	function ehRegistravelContato() {

		if (!$this->temPermissao("registrar_contato")) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		
		return true;

	}

	function ehBaixavel() {

		if (!$this->temPermissao("dar_baixa")) return false;

		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if (!in_array($this->dados_item["status_id"], array(1, 12))) return false;
		
		return true;

	}

    function podeVerificarLiquidacao() {
            
		if (!$this->temPermissao("verificar_liquidacao")) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if (!in_array($this->dados_item["status_id"], array(1))) return false;
		
		return true;
		
    }
     
    function podeVerInformacaoBoleto() {
            
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		
		return true;
		
    }

	function ehGeravelRecibo() {

		if (!$this->temPermissao("gerar_recibo")) return false;
		if (!$this->dados_item["status_recebido"]) return false;
		if (!$this->dados_item["status_manual"]) return false;
		
		return true;

	}

	function ehGeravelReciboVoip() {

		if (!$this->temPermissao("gerar_recibo_voip")) return false;
		if (!$this->dados_item["status_recebido"]) return false;
		if ($this->dados_item["coa_pai"]) return false;
		if (!$this->bancodedados->resultado($this->bancodedados->query("SELECT SUM(tab_cobrancas_detalhes.cod_valor_final) FROM tab_cobrancas_detalhes INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id) WHERE tab_ajustes_financeiros.afc_id = 29 AND tab_cobrancas_detalhes.coa_id = {$this->dados_item["id"]}"))) return false;
		
		return true;

	}

	function ehEditavelBaixa() {

		if (!$this->temPermissao("editar_baixa")) return false;
		if (!$this->dados_item["status_manual"]) return false;
		
		return true;

	}

	function ehRemovivelBaixa() {

		return false;

	}

	function ehGeravelSegundaVia() {

		if (!$this->temPermissao("gerar_segunda_via")) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		if ($this->temPermissao("gerar_segunda_via_plus_plus")) return true;
		if ($this->quantidadeSegundasVias($this->dados_item["coa_pai"] ? $this->dados_item["coa_pai"] : $this->dados_item["id"]) >= 5) return false;
		
		return true;

	}

	function ehGeravelNota() {

		if (!$this->temPermissao("gerar_nota")) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if ($this->dados_item["fatura_id"]) return false;
		if ($this->dados_item["status_id"] == 12) return false;
		if ($this->dados_item["origem_id"] == 6) return false;
		if ($this->bancodedados->resultado($this->bancodedados->query("SELECT COUNT(*) FROM tab_faturas_lotes WHERE fls_id <> 3"))) return false;
		//if ($this->dados_item["estado"] != "SC" && $_SESSION["usuario"] != 39) return false;
		
		return true;

	}

	function ehGeravelNotaFatura() {
		
		if (!$this->temPermissao("gerar_nota_fatura")) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if ($this->dados_item["fatura_id"]) return false;
		if ($this->dados_item["estado"] == "SC") return false;
		
		return true;

	}

	function ehCancelavelNota() {

		if (!$this->temPermissao("cancelar_nota")) return false;
		if (!$this->dados_item["fatura_id"]) return false;
		if ($this->dados_item["fatura"]["cancelada"]) return false;
		if (!$this->bancodedados->resultado($this->bancodedados->query("SELECT DATE_FORMAT(fal_data_ciclo, '%Y%m') = DATE_FORMAT(CURDATE(), '%Y%m') FROM tab_faturas INNER JOIN tab_faturas_lotes ON (tab_faturas.fal_id = tab_faturas_lotes.fal_id) WHERE tab_faturas.fat_id = {$this->dados_item["fatura_id"]} AND tab_faturas_lotes.fls_id = 3"))) return false;
		
		return true;

	}

	function ehRemovivel() {

		if (!$this->temPermissao("remover")) return false;
		if ($this->dados_item["fatura_id"]) return false;
		if ($this->buscaCobrancasFilho($this->dados_item["id"])) return false;
		if ($this->dados_item["enviada"]) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		if ($this->dados_item["com_remessa"]) return false;
		
		return true;

	}

	function ehRemovivelFatura() {

		if (!$this->temPermissao("remover_fatura")) return false;
		if (!$this->dados_item["fatura_id"]) return false;
		if (!in_array($this->dados_item["fatura_tipo_id"], array(2, 3, 4))) return false;
		if ($this->loteFaturaGerado($this->dados_item["fatura_id"])) return false;
		
		return true;

	}

	function ehExportavelFatura1() {

		if (!$this->temPermissao("exportar_fatura_1")) return false;
		if (!$this->dados_item["fatura_id"]) return false;
		if ($this->dados_item["fatura"]["cancelada"]) return false;
		if (!$this->loteFaturaGerado($this->dados_item["fatura_id"])) return false;
		
		return true;

	}

	function ehExportavelFatura2() {

		return false;

	}

	function ehEnviavelFatura1() {

		if (!$this->temPermissao("enviar_fatura_1")) return false;
		if (!$this->dados_item["fatura_id"]) return false;
		if ($this->dados_item["fatura"]["cancelada"]) return false;
		if (!$this->loteFaturaGerado($this->dados_item["fatura_id"])) return false;
		
		return true;

	}

	function ehEnviavelFatura2() {

		return false;

	}

	function ehCancelavel() {

		if (!$this->temPermissao("cancelar")) return false;
		//if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if ($this->dados_item["status_recebido"] && !$this->dados_item["status_manual"]) return false;
		if ($this->dados_item["status_id"] == 12) return false;
		
		return true;

	}

	function ehDescancelavel() {

		return false;

	}

	function ehExportavel() {

		if (!$this->temPermissao("exportar")) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		if ($this->dados_item["formato_id"] != 3) return false;
		if (!$this->dados_item["coa_pai"] && $this->dados_item["lote_id"]) return false;
		
		return true;

	}

	function ehImprimivel() {

		if (!$this->temPermissao("imprimir")) return false;
		if (!$this->dados_item["parametros"]["boleto"]) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if (in_array($_SESSION["usuario"], array(39, 62))) return true;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if (!$this->temPermissao("imprimir_plus") && $this->dados_item["status_id"] != 1) return false;
		
		return true;

	}

	function ehEnviavel() {

		if (!$this->temPermissao("enviar")) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if (!$this->dados_item["parametros"]["boleto"]) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		
		return true;

	}

	function ehRegeravel() {

		if (!$this->temPermissao("regerar")) return false;
		if ($this->dados_item["aguardando_registro"]) return false;
		if ($this->dados_item["lote_id"] && !in_array($this->dados_item["lote_status_id"], array(4, 6))) return false;
		if ($this->ehCobrancaCondominial($this->dados_item["id"])) return false;
		if ($this->dados_item["status_id"] != 1) return false;
		if ($this->dados_item["coa_pai"]) return false;
		if (in_array($_SESSION["usuario"], array(39, 62))) return true;
		if ($this->dados_item["fatura_id"]) return false;
		if (!$this->buscaAjusteEstornoPendente($this->dados_item["id"], $this->dados_item["data_vencimento"]) && !$this->cobrancaAssinaturaCancelada($this->dados_item["id"])) return false;
		
		return true;

	}
	
	function ehRegistravelNota() {
		
		if (!$this->temPermissao("registrar_nota")) return false;
		if ($this->dados_item["fatura_id"] && !in_array($this->dados_item["fatura"]["tipo_id"], array(3, 4))) return false;
		
		return true;

	}

	###################
	### Comentários ###
	###################

	function registraComentario($cobranca_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_cobrancas_comentarios 
			SET
				com_datahora = NOW(), 
				coa_id = $cobranca_id, 
				com_conteudo = '$conteudo', 
				usu_id = $usuario_id");

	}

	function buscaComentarios($cobranca_id) {

		$comentarios = array();

		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_nome IS NOT NULL, tab_usuarios.usu_nome, 'Sistema') AS responsavel,
				DATE_FORMAT(tab_cobrancas_comentarios.com_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_cobrancas_comentarios.com_conteudo AS conteudo
			FROM
				tab_cobrancas_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_cobrancas_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_cobrancas_comentarios.coa_id = $cobranca_id
			ORDER BY
				tab_cobrancas_comentarios.com_datahora ASC,
				tab_cobrancas_comentarios.coa_id ASC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
			$dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
			$dados["detalhamento"] .= "{$dados["conteudo"]}";

			$comentarios[] = $dados;

		}

		return $comentarios;

	}

	function geraComentariosListagem($comentarios) {

		$cabecalho = array ("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);

		return $listagem->conteudo;

	}

	#############
	### Geral ###
	#############

	function fechar() {

		$this->conteudo = "";
		if ($this->dados_item) $this->conteudo .= '<script type="text/javascript">if (window.opener != null && window.opener.document.getElementById("cobrancas")) window.opener.buscarCobrancas('.$this->dados_item["cliente_id"].')</script>';
		$this->conteudo .= '<script type="text/javascript">window.close()</script>';

	}

	#################################
	### Adicionar Cobrança Avulsa ###
	#################################

	function adicionar_cobranca_avulsa() {

		$dados_cliente = $this->buscaDadosCliente($_GET["cli"]);

		if ($dados_cliente) {

			if ($_GET["erro"]) {

				$botoes = array();
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.geracao_erros.inc.php");

				if ($_GET["erro"] == "erro_codigo") {
					$this->conteudo = str_replace("[erro]", "Limite máximo de cobranças geradas para este vencimento.", $this->conteudo);
				} else {
					$this->conteudo = str_replace("[erro]", "O cliente não possui cobranças para vencimento entre <b>[vencimento_inicio]</b> e <b>[vencimento_termino]</b>,<br>ou já possui cobrança gerada para esse período.", $this->conteudo);
				}
				$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[vencimento_inicio]", $_GET["vencimento_inicio"], $this->conteudo);
				$this->conteudo = str_replace("[vencimento_termino]", $_GET["vencimento_termino"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			} elseif (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.adicionar_cobranca_avulsa.inc.php");

				$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[formato]", $this->geraSelect($this->buscaFormatos("1", true, $dados_cliente["cliente_origem_id"]), $dados_cliente["formato_id"], true, "formato", "100%"), $this->conteudo);
				$this->conteudo = str_replace("[adicionar_plus]", ($this->temPermissao("adicionar_cobranca_avulsa_plus")) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
				$this->conteudo = str_replace("[dia_vencimento]", $dados_cliente["dia_vencimento"], $this->conteudo);
				$this->conteudo = str_replace("[vencimento_maximo]", COBRANCA_AVULSA_VENCIMENTO_MAXIMO, $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&cli={$_GET["cli"]}", $this->conteudo);

			} else {

				$data_vencimento = substr($_POST["data_vencimento"], 6, 4)."-".substr($_POST["data_vencimento"], 3, 2)."-".substr($_POST["data_vencimento"], 0, 2);
				if (strtotime($data_vencimento) < strtotime("today")) {
					$_POST["formato"] = 1;
				}
				$gerar_juros = $_POST["nao_gerar_juros"] ? false : true;
				
				$geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);

				$cobranca_id = $geracao->geraCobrancaAvulsa($_GET["cli"], $_POST["formato"], $_POST["vencimento_inicio"], $_POST["vencimento_termino"], $data_vencimento, $dados_cliente["dia_vencimento"], $gerar_juros, $_POST["valor_acrescimo"], $_POST["valor_decrescimo"], false);
				
				if ($cobranca_id && !in_array($cobranca_id, array("erro_codigo"))) {
					
					if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
					$this->registraComentario($cobranca_id, "<b>Ação:</b> Adicionar Cobrança Avulsa$comentario", $_SESSION["usuario"]);
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $cobranca_id);
                                        
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i=$cobranca_id");

				} else {

					$erro = $cobranca_id ? $cobranca_id : 1;

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=adicionar_cobranca_avulsa&erro=$erro&cli={$_GET["cli"]}&vencimento_inicio={$_POST["vencimento_inicio"]}&vencimento_termino={$_POST["vencimento_termino"]}");
					
				}
				
			}

		} else {
			$this->fechar();
		}

	}

	#############################
	### Adicionar Segunda Via ###
	#############################

	function gerar_segunda_via() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$tarifa_bancaria = $this->buscaTarifaBancaria($this->dados_item["id"]);
				$formatos = $this->buscaFormatos("1", true, $this->dados_item["origem_id"], $this->dados_item["cliente_formato_id"]);
				
				if ($this->temPermissao("gerar_segunda_via_plus")) {
					$data_vencimento_maximo = "";
				} else {
					$data_vencimento_maximo = substr($this->dados_item["data_vencimento"], 6, 4).substr($this->dados_item["data_vencimento"], 3, 2).substr($this->dados_item["data_vencimento"], 0, 2);
					$data_vencimento_maximo = floor((time() - strtotime($data_vencimento_maximo)) / 60 / 60 / 24);
					$data_vencimento_maximo = ($data_vencimento_maximo >= COBRANCA_SEGUNDAVIA_VENCIMENTO_MAXIMO - COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO) ? ((date("w", strtotime("+ ".COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO." days")) == 0 ? COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO + 1 : ((date("w", strtotime("+ ".COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO." days")) == 6) ? COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO + 2 : COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO))) : COBRANCA_SEGUNDAVIA_VENCIMENTO_MAXIMO - $data_vencimento_maximo;
					$data_vencimento_maximo = date("d/m/Y", strtotime("+ $data_vencimento_maximo days"));
				}
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.gerar_segunda_via.inc.php");

				$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[formato_1_via]", $this->dados_item["formato_nome"], $this->conteudo);
				$this->conteudo = str_replace("[codigo_1_via]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[data_vencimento_1_via]", $this->dados_item["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace("[valor_1_via]", $this->formataDinheiro($this->dados_item["valor_original"]).($this->dados_item["valor_final"] ? " (".$this->formataDinheiro($this->dados_item["valor_final"]).")" : ""), $this->conteudo);
				$this->conteudo = str_replace("[data_vencimento_maximo]", $data_vencimento_maximo, $this->conteudo);
				$this->conteudo = str_replace("[valor_desconto_maximo]", $this->temPermissao("gerar_segunda_via_plus") ? "" : "1000", $this->conteudo);
				$this->conteudo = str_replace("[formato]", $this->geraSelect($formatos, $this->dados_item["formato_id"], false, "formato", "100%"), $this->conteudo);
				$this->conteudo = str_replace("[valores]", $this->buscaTabelaValores($this->dados_item["id"]), $this->conteudo);
				$this->conteudo = str_replace("[valor_original]", $this->formataDinheiro($this->dados_item["valor_original"] - $tarifa_bancaria), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$data_vencimento = substr($_POST["data_vencimento"], 6, 4)."-".substr($_POST["data_vencimento"], 3, 2)."-".substr($_POST["data_vencimento"], 0, 2);

				if (strtotime($data_vencimento) < strtotime("today")) {
					$_POST["formato"] = 1;
				}
				
				$cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);

				$cobranca_id = $cobrancas_geracao->geraSegundaVia($this->dados_item, $_POST["formato"], $_POST["data_vencimento"], $_POST["valor_final_original"], $_POST["valor_juro"], $_POST["tipo_ajustes"], $_POST["valor_ajustes"], 0, $_POST["comentario"], $_SESSION["usuario"], $_POST["desbloquear"], (bool) $_POST["manter_bonificacao"]);

				if ($_POST["desbloquear"]) {
					$this->finalizaBloqueio($this->dados_item["cliente_id"]);
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $cobranca_id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i=$cobranca_id");

			}

		} else {
			$this->fechar();
		}

	}

	function buscaTabelaValores($cobranca_id) {

		$meses = array("01" => "Jan", "02" => "Fev", "03" => "Mar", "04" => "Abr", "05" => "Mai", "06" => "Jun", "07" => "Jul", "08" => "Ago", "09" => "Set", "10" => "Out", "11" => "Nov", "12" => "Dez");
		$valores = array();
		$total = 0;
		$i = 0;

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_detalhes.cod_id AS id,
				DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%m') AS competencia_mes,
				DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%Y') AS competencia_ano,
				tab_cobrancas_detalhes.cod_descricao AS descricao,
				tab_cobrancas_detalhes.cod_valor_final + IFNULL((SELECT SUM(tab_cobrancas_detalhes_2.cod_valor_final) FROM tab_cobrancas_detalhes tab_cobrancas_detalhes_2 WHERE tab_cobrancas_detalhes.cod_id = tab_cobrancas_detalhes_2.cod_pai), 0) AS valor,
				(SELECT afc_id FROM tab_ajustes_financeiros WHERE tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes.ajf_id) AS ajuste_categoria_id
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id IN ($cobranca_id) AND
				tab_cobrancas_detalhes.cod_pai IS NULL AND
				(tab_cobrancas_detalhes.ajf_id IS NOT NULL OR
				tab_cobrancas_detalhes.ass_id IS NOT NULL)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["descricao"] .= " &raquo; {$meses[$dados["competencia_mes"]]} / {$dados["competencia_ano"]}";

			$valores[] = array("valor_original" => "<div style=\"color:#232323;text-align:left;font-weight:bold\">&raquo; {$dados["descricao"]}</div>", "colspan" => array("valor_original" => 4), "cor_linha" => "#A3D1F3");

			if ($dados["valor"] < 0) {
				$dados["valor_original"] = "<input id=\"valor_original_{$i}\" style=\"width:116px;text-align:right;background-color:#fe8888;border-color:#9d2727;color:#9d2727\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"".strip_tags($this->formataDinheiro($dados["valor"]))."\" disabled \\>";
				$dados["tipo_ajuste"] = "<select class=\"inputBrancoAzulEscuro11\" id=\"tipo_ajuste_{$i}\" name=\"tipo_ajustes[{$dados["id"]}]\" style=\"width:104px;background-color:#E8E8E8\" disabled><option value=\"+\">Acréscimo</option><option value=\"-\">Decréscimo</option></select>";
				$dados["valor_ajuste"] = "<input id=\"valor_ajuste_{$i}\" name=\"valor_ajustes[{$dados["id"]}]\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"R$ 0,00\" disabled \\>";
				$dados["valor_final"] = "<input id=\"valor_final_{$i}\" style=\"width:116px;text-align:right;background-color:#fe8888;border-color:#9d2727;color:#9d2727\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"".strip_tags($this->formataDinheiro($dados["valor"]))."\" disabled \\>";
			} elseif ($this->temPermissao("gerar_segunda_via_plus_plus") || !in_array($dados["ajuste_categoria_id"], array(19, 20, 23))) {
				$dados["valor_original"] = "<input id=\"valor_original_{$i}\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"{$this->formataDinheiro($dados["valor"])}\" disabled \\>";
				$dados["tipo_ajuste"] = "<select class=\"inputBrancoAzulEscuro11\" id=\"tipo_ajuste_{$i}\" name=\"tipo_ajustes[{$dados["id"]}]\" style=\"width:104px\" onchange=\"return mudaTipoAjuste($i)\"><option value=\"+\">Acréscimo</option><option value=\"-\">Decréscimo</option></select>";
				$dados["valor_ajuste"] = "<input id=\"valor_ajuste_{$i}\" name=\"valor_ajustes[{$dados["id"]}]\" style=\"width:116px;text-align:right\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"R$ 0,00\" onkeypress=\"return mudaValorAjuste(this, event, $i)\" onblur=\"return mudaValorAjuste(this, event, $i)\" \\>";
				$dados["valor_final"] = "<input id=\"valor_final_{$i}\" style=\"width:116px;text-align:right\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"{$this->formataDinheiro($dados["valor"])}\" onkeypress=\"return mudaValorFinal(this, event, $i)\" onblur=\"return mudaValorFinal(this, event, $i)\" \\>";
			} else {
				$dados["valor_original"] = "<input id=\"valor_original_{$i}\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"{$this->formataDinheiro($dados["valor"])}\" disabled \\>";
				$dados["tipo_ajuste"] = "<select class=\"inputBrancoAzulEscuro11\" id=\"tipo_ajuste_{$i}\" name=\"tipo_ajustes[{$dados["id"]}]\" style=\"width:104px;background-color:#E8E8E8;\" disabled><option value=\"+\">Acréscimo</option><option value=\"-\">Decréscimo</option></select>";
				$dados["valor_ajuste"] = "<input id=\"valor_ajuste_{$i}\" name=\"valor_ajustes[{$dados["id"]}]\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"R$ 0,00\" disabled \\>";
				$dados["valor_final"] = "<input id=\"valor_final_{$i}\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"{$this->formataDinheiro($dados["valor"])}\" disabled \\>";
			}

			$total += $dados["valor"];
			$i++;

			$valores[] = $dados;

		}

		// Total Items
		$valores[] = array("valor_original" => "<div style=\"color:#232323;text-align:left;font-weight:bold\">&raquo; Total Itens</div>", "colspan" => array("valor_original" => 4), "cor_linha" => "#A3D1F3");
		$valores[] = array("valor_final" => "<input id=\"valor_final_original\" name=\"valor_final_original\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"{$this->formataDinheiro($total)}\" readonly \\>");

		// Juro
		$valores[] = array("valor_original" => "<div style=\"color:#232323;text-align:left;font-weight:bold\">&raquo; Juro</div>", "colspan" => array("valor_original" => 4), "cor_linha" => "#A3D1F3");
		$valores[] = array("valor_final" => "<input id=\"valor_juro\" name=\"valor_juro\" style=\"width:116px;text-align:right;background-color:#E8E8E8;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"R$ 0,00\" readonly \\>");

		// Total
		$valores[] = array("valor_original" => "<div style=\"color:#232323;text-align:left;font-weight:bold\">&raquo; Total</div>", "colspan" => array("valor_original" => 4), "cor_linha" => "#A3D1F3");
		$valores[] = array("valor_final" => "<input id=\"valor_final_total\" name=\"valor_final_total\" style=\"width:116px;text-align:right;background-color:#505050;color:#ffffff;border-color:#003364;\" type=\"text\" class=\"inputBrancoAzulEscuro11\" value=\"{$this->formataDinheiro($total)}\" readonly \\>");

		$cabecalho = array ("valor_original" => "Valor Original", "tipo_ajuste" => "Ajuste", "valor_ajuste" => "Valor Ajuste", "valor_final" => "Valor Final");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $valores, 0, false, array(), null, array("center"));

		return $listagem->conteudo;

	}

	function buscaTarifaBancaria($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				SUM(cod_valor_final)
			FROM
				tab_cobrancas_detalhes
			WHERE
				(coa_id = $cobranca_id) AND
				(ass_id IS NULL) AND
				(dec_id IS NULL) AND
				(cod_descricao LIKE '%Tarifa Bancária%' OR cod_descricao LIKE '%Taxa de emissão e envio de cobrança%') AND
				(cod_valor_final > 0)");

		return (int) $this->bancodedados->resultado($query);

	}

	function buscaTarifasBancarias($cliente_id, $formatos, $padrao) {

		$cliente_novo = $this->ehClienteNovo($cliente_id);
		$tarifas = array("tarifas_bancarias[0] = $padrao;");
		
		$query = $this->bancodedados->query("
			SELECT
				cof_id AS id,
				cof_parametros AS parametros
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id IN (".implode(", ", array_keys($formatos)).")
			ORDER BY
				cof_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($cliente_novo) {

				$tarifas[] = "tarifas_bancarias[{$dados["id"]}] = 0;";

			} else {

				$dados["parametros"] = unserialize($dados["parametros"]);

				$valor = $dados["parametros"]["valor_cobranca"]["valor"] + $this->buscaAjusteTarifaBancaria($cliente_id, $dados["parametros"]["valor_cobranca"]["valor"]);
				$valor = $valor > 0 ? $valor : 0;

				$tarifas[] = "tarifas_bancarias[{$dados["id"]}] = $valor;";

			}

		}

		return implode("\n  ", $tarifas);

	}

	function ehClienteNovo($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(ass_id)
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				cli_id = $cliente_id AND
				ass_data_ativacao >= '2008-01-01' AND
				ass_migrada IS NULL AND
				pls_id NOT IN (21) AND
				ass_pai IS NULL AND
				ast_id NOT IN (1, 6)");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaAjusteTarifaBancaria($cliente_id, $valor_total) {

		$valor_total_ajustes = 0;

		$query = $this->bancodedados->query("
			SELECT
				aft_id AS tipo,
				ajf_valor AS valor,
				ajf_valor_tipo AS valor_tipo
			FROM
				tab_ajustes_financeiros
			WHERE
				cli_id = $cliente_id AND
				afs_id IN (5, 7, 8) AND
				afc_id IN (11)
			ORDER BY
				ajf_competencia_inicio");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["valor"] = intval(($dados["valor_tipo"] == 1) ? $dados["valor"] : (($dados["valor_tipo"] == 2) ? $valor_total * $dados["valor"] : 0));
			$dados["valor"] *= ($dados["tipo"] == 1) ? 1 : -1;
			
			$valor_total_ajustes += $dados["valor"];
			
		}

		return $valor_total_ajustes;

	}

	##########################
	### Ver Dados Cobrança ###
	##########################

	function ver_dg() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$this->conteudo .= $this->carregaArquivo("cobrancas.ver_dg.inc.php");

			$acoes = array("ver_dg" => "Dados Cobrança", "ver_dd" => "Detalhamento");
			$notas = $this->buscaNotas($_GET["i"]);
			if (!in_array($this->dados_item["status_id"], array(1, 12)) || $notas) {
				$acoes["ver_nt"] = "NFS-e";
			}
			if ($this->dados_item["fatura_id"]) {
				$acoes["ver_df_1"] = "Fatura ({$this->dados_item["fatura_tipo_nome"]})";
			}

			$botoes = array();
                        
			if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
				$nosso_numero = preg_replace("/[^0-9]/", "", $this->dados_item['parametros']['boleto']['nosso_numero']);
				if ($this->podeVerInformacaoBoleto() && $nosso_numero) $acoes["ver_boleto"] = "Ver Boleto";
				if ($this->podeVerificarLiquidacao() && $nosso_numero) $botoes[] = '<input type="button" name="dar_baixa" class="buttonAzulEscuroBranco11" value="Verificar Liquidação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=verificar_liquidacao&n='. $nosso_numero . '&i='.$this->dados_item["id"].'\';">';
			}
                            
			if ($this->ehGeravelNota()) $botoes[] = '<input type="button" name="gerar_nota" class="buttonAzulEscuroBranco11" value="Gerar Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_nota&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRegistravelNota()) $botoes[] = '<input type="button" name="registrar_nota" class="buttonAzulEscuroBranco11" value="Registrar Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=registrar_nota&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRegistravelContato()) $botoes[] = '<input type="button" name="registrar_contato" class="buttonAzulEscuroBranco11" value="Registrar Contato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=registrar_contato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehBaixavel()) $botoes[] = '<input type="button" name="dar_baixa" class="buttonAzulEscuroBranco11" value="Dar Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=dar_baixa&i='.$this->dados_item["id"].'\';">';
			if ($this->ehGeravelRecibo()) $botoes[] = '<input type="button" name="gerar_recibo" class="buttonAzulEscuroBranco11" value="Gerar Recibo" onClick="gerarRecibo('.$this->dados_item["id"].')">';
			if ($this->ehGeravelReciboVoip()) $botoes[] = '<input type="button" name="gerar_recibo_voip" class="buttonAzulEscuroBranco11" value="Gerar Recibo VoIP" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_recibo_voip&i='.$this->dados_item["id"].'\';">';
			if ($this->ehEditavelBaixa()) $botoes[] = '<input type="button" name="editar_baixa" class="buttonAzulEscuroBranco11" value="Editar Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_baixa&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivelBaixa()) $botoes[] = '<input type="button" name="remover_baixa" class="buttonAzulEscuroBranco11" value="Remover Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_baixa&i='.$this->dados_item["id"].'\';">';
			if ($this->ehGeravelSegundaVia()) $botoes[] = '<input type="button" name="gerar_segunda_via" class="buttonAzulEscuroBranco11" value="Gerar Segunda Via" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_segunda_via&i='.($this->dados_item["coa_pai"] ? $this->dados_item["coa_pai"] : $this->dados_item["id"]).'\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=descancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRegeravel()) $botoes[] = '<input type="button" name="regerar" class="buttonAzulEscuroBranco11" value="Regerar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehExportavel()) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehImprimivel()) $botoes[] = '<input type="button" name="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=imprimir&i='.$this->dados_item["id"].'\';">';
			if ($this->ehEnviavel()) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar&i='.$this->dados_item["id"].'\';">';
			if(empty(array_filter($notas)) && $this->dados_item["status_id"] != 12 && $this->dados_item["gerado_nota"] == 0 ) {
				if ($this->temPermissao("gerar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="gerar_nfse" class="buttonAzulEscuroBranco11" value="Gerar NFS-e" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=gerar_nfse&c=0&i=' . $_GET["i"] . '\';">';
			}
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes($acoes), $this->conteudo);

			$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[competencia]', $this->dados_item["competencia"], $this->conteudo);
			$this->conteudo = str_replace('[valor_bonificacao]', $this->formataDinheiro($this->dados_item["valor_bonificacao"]), $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_vencimento]', $this->dados_item["data_vencimento"], $this->conteudo);
			$this->conteudo = str_replace('[data_processamento]', $this->dados_item["data_processamento"], $this->conteudo);
			$this->conteudo = str_replace('[data_pagamento]', $this->dados_item["data_pagamento"], $this->conteudo);
			$this->conteudo = str_replace('[data_credito]', $this->dados_item["data_credito"], $this->conteudo);
			$this->conteudo = str_replace('[valor_original]', $this->formataDinheiro($this->dados_item["valor_original"]).($this->dados_item["valor_final"] ? " (".$this->formataDinheiro($this->dados_item["valor_final"]).")" : ""), $this->conteudo);
			$this->conteudo = str_replace('[valor_juro]', $this->formataDinheiro($this->dados_item["valor_juro"]), $this->conteudo);
			$this->conteudo = str_replace('[valor_desconto]', $this->formataDinheiro($this->dados_item["valor_desconto"]), $this->conteudo);
			$this->conteudo = str_replace('[valor_acrescimo]', $this->formataDinheiro($this->dados_item["valor_acrescimo"]), $this->conteudo);
			$this->conteudo = str_replace('[valor_decrescimo]', $this->formataDinheiro($this->dados_item["valor_decrescimo"]), $this->conteudo);
			$this->conteudo = str_replace('[valor_total]', $this->formataDinheiro($this->dados_item["valor_total"]), $this->conteudo);
			$this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->buscaComentarios($this->dados_item["id"])), $this->conteudo);
			$this->conteudo = str_replace('[operacoes]', $this->buscaClientesOperacoes($this->dados_item["cliente_id"], $this->dados_item["data_vencimento"], $this->dados_item["data_processamento"]), $this->conteudo);
			$this->conteudo = str_replace('[contatos]', $this->geraContatosListagem($this->buscaContatos($this->dados_item["id"])), $this->conteudo);
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		} else {
			$this->fechar();
		}

	}

	function geraContatosListagem($contatos) {

		$cabecalho = array ("data_hora" => "Data/Hora", "tipo" => "Tipo", "comentario" => "Comentário", "responsavel" => "Responsável");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $contatos);

		return $listagem->conteudo;

	}

	########################
	### Ver Detalhamento ###
	########################

	function ver_dd() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$this->conteudo .= $this->carregaArquivo("cobrancas.ver_dd.inc.php");

			$acoes = array("ver_dg" => "Dados Cobrança", "ver_dd" => "Detalhamento");
			$notas = $this->buscaNotas($_GET["i"]);
			if (!in_array($this->dados_item["status_id"], array(1, 12)) || $notas) {
				$acoes["ver_nt"] = "NFS-e";
			}
                        
			if ($this->dados_item["fatura_id"]) {
				$acoes["ver_df_1"] = "Fatura ({$this->dados_item["fatura_tipo_nome"]})";
			}
			$botoes = array();
			if ($this->ehRegistravelContato()) $botoes[] = '<input type="button" name="registrar_contato" class="buttonAzulEscuroBranco11" value="Registrar Contato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=registrar_contato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehBaixavel()) $botoes[] = '<input type="button" name="dar_baixa" class="buttonAzulEscuroBranco11" value="Dar Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=dar_baixa&i='.$this->dados_item["id"].'\';">';
                        
			if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
				$nosso_numero = preg_replace("/[^0-9]/", "", $this->dados_item['parametros']['boleto']['nosso_numero']);
				if ($this->podeVerInformacaoBoleto() && $nosso_numero) $acoes["ver_boleto"] = "Ver Boleto";
				if ($this->podeVerificarLiquidacao() && $nosso_numero) $botoes[] = '<input type="button" name="dar_baixa" class="buttonAzulEscuroBranco11" value="Verificar Liquidação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=verificar_liquidacao&n='. $nosso_numero . '&i='.$this->dados_item["id"].'\';">';
			}
                        
			if ($this->ehGeravelRecibo()) $botoes[] = '<input type="button" name="gerar_recibo" class="buttonAzulEscuroBranco11" value="Gerar Recibo" onClick="gerarRecibo('.$this->dados_item["id"].')">';
			if ($this->ehGeravelReciboVoip()) $botoes[] = '<input type="button" name="gerar_recibo_voip" class="buttonAzulEscuroBranco11" value="Gerar Recibo VoIP" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_recibo_voip&i='.$this->dados_item["id"].'\';">';
			if ($this->ehEditavelBaixa()) $botoes[] = '<input type="button" name="editar_baixa" class="buttonAzulEscuroBranco11" value="Editar Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_baixa&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivelBaixa()) $botoes[] = '<input type="button" name="remover_baixa" class="buttonAzulEscuroBranco11" value="Remover Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_baixa&i='.$this->dados_item["id"].'\';">';
			if ($this->ehGeravelSegundaVia()) $botoes[] = '<input type="button" name="gerar_segunda_via" class="buttonAzulEscuroBranco11" value="Gerar Segunda Via" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_segunda_via&i='.($this->dados_item["coa_pai"] ? $this->dados_item["coa_pai"] : $this->dados_item["id"]).'\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=descancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRegeravel()) $botoes[] = '<input type="button" name="regerar" class="buttonAzulEscuroBranco11" value="Regerar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehExportavel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehImprimivel()) $botoes[] = '<input type="button" name="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=imprimir&i='.$this->dados_item["id"].'\';">';
			if ($this->ehEnviavel()) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes($acoes), $this->conteudo);

			$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
			$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[competencia]', $this->dados_item["competencia"], $this->conteudo);
			$this->conteudo = str_replace('[valor_bonificacao]', $this->formataDinheiro($this->dados_item["valor_bonificacao"]), $this->conteudo);
			if ($this->dados_item["detalhamento"]) {
				$this->conteudo = str_replace('[detalhamento]', $this->geraDetalhamentoListagemAntiga(unserialize($this->dados_item["detalhamento"])), $this->conteudo);
			} else {
				$this->conteudo = str_replace('[detalhamento]', $this->geraDetalhamentoListagem($this->buscaDetalhesCobranca($this->dados_item)), $this->conteudo);
			}
			$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

		} else {
			$this->fechar();
		}

	}

    function ver_boleto() {
        
        $this->buscaDadosItem($_GET["i"]);
        if ($this->dados_item) {
            
            $this->conteudo .= $this->carregaArquivo("cobrancas.ver_boleto.inc.php");
            
            $acoes = array("ver_dg" => "Dados Cobrança", "ver_dd" => "Detalhamento");
            $notas = $this->buscaNotas($_GET["i"]);
            if (!in_array($this->dados_item["status_id"], array(1, 12)) || $notas) {
				$acoes["ver_nt"] = "NFS-e";
            }
            if ($this->dados_item["fatura_id"]) {
                $acoes["ver_df_1"] = "Fatura ({$this->dados_item["fatura_tipo_nome"]})";
            }
            $acoes["ver_boleto"] = "Ver Boleto";

            $botoes = array();
            $nossoNumero = preg_replace("/[^0-9]/", "", $this->dados_item['parametros']['boleto']['nosso_numero']);
            $botoes[] = '<input type="button" name="dar_baixa" class="buttonAzulEscuroBranco11" value="Verificar Liquidação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=verificar_liquidacao&n='. $nosso_numero . '&i='.$this->dados_item["id"].'\';">';
            if ($this->ehRegistravelContato()) $botoes[] = '<input type="button" name="registrar_contato" class="buttonAzulEscuroBranco11" value="Registrar Contato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=registrar_contato&i='.$this->dados_item["id"].'\';">';
            if ($this->ehBaixavel()) $botoes[] = '<input type="button" name="dar_baixa" class="buttonAzulEscuroBranco11" value="Dar Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=dar_baixa&i='.$this->dados_item["id"].'\';">';
            if ($this->ehGeravelRecibo()) $botoes[] = '<input type="button" name="gerar_recibo" class="buttonAzulEscuroBranco11" value="Gerar Recibo" onClick="gerarRecibo('.$this->dados_item["id"].')">';
            if ($this->ehGeravelReciboVoip()) $botoes[] = '<input type="button" name="gerar_recibo_voip" class="buttonAzulEscuroBranco11" value="Gerar Recibo VoIP" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_recibo_voip&i='.$this->dados_item["id"].'\';">';
            if ($this->ehEditavelBaixa()) $botoes[] = '<input type="button" name="editar_baixa" class="buttonAzulEscuroBranco11" value="Editar Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_baixa&i='.$this->dados_item["id"].'\';">';
            if ($this->ehRemovivelBaixa()) $botoes[] = '<input type="button" name="remover_baixa" class="buttonAzulEscuroBranco11" value="Remover Baixa" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_baixa&i='.$this->dados_item["id"].'\';">';
            if ($this->ehGeravelSegundaVia()) $botoes[] = '<input type="button" name="gerar_segunda_via" class="buttonAzulEscuroBranco11" value="Gerar Segunda Via" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_segunda_via&i='.($this->dados_item["coa_pai"] ? $this->dados_item["coa_pai"] : $this->dados_item["id"]).'\';">';
            if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
            if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
            if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=descancelar&i='.$this->dados_item["id"].'\';">';
            if ($this->ehRegeravel()) $botoes[] = '<input type="button" name="regerar" class="buttonAzulEscuroBranco11" value="Regerar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar&i='.$this->dados_item["id"].'\';">';
            if ($this->ehExportavel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$this->dados_item["id"].'\';">';
            if ($this->ehImprimivel()) $botoes[] = '<input type="button" name="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=imprimir&i='.$this->dados_item["id"].'\';">';
            if ($this->ehEnviavel()) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar&i='.$this->dados_item["id"].'\';">';
            $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
		
            $this->conteudo = str_replace('[acoes]', $this->geraAcoes($acoes), $this->conteudo);
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
            
            $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
            if($nossoNumero) {
                $saida = "";
                if($boleto = $boletoSicredi->buscaBoletoPeloNossoNumero($nossoNumero)) {
                    foreach ($boleto as $key => $item) {
                        $key = ucfirst(preg_replace('/(?<!\ )[A-Z]/', ' $0', $key));
                        if(is_array($item)) {
                            $saida .= "<div style='padding: 5px 0 2px'><b>$key:</b></div>";
                            foreach ($item as $subKey => $subItem) {
                                $subKey = ucfirst(preg_replace('/(?<!\ )[A-Z]/', ' $0', $subKey));
                                if(is_array($subItem)) {
                                    $saida .= "<div style='padding: 2px 0'>>> <b>$subKey: </b></div>";
                                    foreach ($subItem as $subKey1 => $subItem1) {
                                        $subKey1 = ucfirst(preg_replace('/(?<!\ )[A-Z]/', ' $0', $subKey1));
                                        $saida .= "<div style='padding: 2px 0;word-break: break-all;'>>>>> <b>$subKey1: </b> :  $subItem1 </div>";
                                    }
                                } else {
                                    $saida .= "<div style='padding: 2px 0;word-break: break-all'><b>>> $subKey</b> :  $subItem </div>";
                                }
                            }
                        } else {
                            $saida .= "<div style='padding: 2px 0;word-break: break-all'><b>$key</b> :  $item </div>";
                        }
                    }
                } else {
                    $saida .= "<div>Sem informação para apresentar no momento</div>";
                }
            }
            
            $this->conteudo = str_replace("[dados_boleto]", $saida, $this->conteudo);
        }
    }

	##############################
        
	########################
	### Ver Notas ###
	########################
        function ver_nt() {
            $this->buscaDadosItem($_GET["i"]);

            $notas = $this->buscaNotas($_GET["i"]);
            $notasAguardando = $this->buscaNotas($_GET["i"], true);
			$notasCanceladas = $this->buscaNotas($_GET["i"], false, true);
			
            if ($this->dados_item) {

                $this->conteudo .= $this->carregaArquivo("cobrancas.ver_nt.inc.php");

                $acoes = array("ver_dg" => "Dados Cobrança", "ver_dd" => "Detalhamento");
                $acoes["ver_nt"] = "NFS-e";
                if ($this->dados_item["fatura_id"]) {
                    $acoes["ver_df_1"] = "Fatura ({$this->dados_item["fatura_tipo_nome"]})";
                }
                if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
                    $nosso_numero = preg_replace("/[^0-9]/", "", $this->dados_item['parametros']['boleto']['nosso_numero']);
                    if ($this->podeVerInformacaoBoleto() && $nosso_numero) $acoes["ver_boleto"] = "Ver Boleto";
                }

                $botoes = array();
                if(!$notas && $this->dados_item["gerado_nota"] == 0 ) {
					if ($this->temPermissao("gerar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="gerar_nfse" class="buttonAzulEscuroBranco11" value="Gerar NFS-e" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=gerar_nfse&c=0&i=' . $_GET["i"] . '\';">';
                }

				if(sizeof($notasCanceladas) == sizeof($notas) && $this->dados_item["gerado_nota"] == 0) {
					if ($this->temPermissao("gerar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="gerar_nfse" class="buttonAzulEscuroBranco11" value="Gerar NFS-e" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=gerar_nfse&c=0&i=' . $_GET["i"] . '\';">';
				}

                if($notasAguardando) {
                    if ($this->temPermissao("gerar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="gerar_nfse" class="buttonAzulEscuroBranco11" value="Gerar NFS-e não geradas" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&n=1&a=gerar_nfse&i=' . $_GET["i"] . '\';">';
                }
				
				if($notas && $this->dados_item["gerado_nota"] ) {
					if ($this->temPermissao("enviar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar NFS-e" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=enviar_nfse&c='. $_GET["i"] . '&i=0\';">';
				}


                $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

                $this->conteudo = str_replace('[acoes]', $this->geraAcoes($acoes), $this->conteudo);
                $this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
                $this->conteudo = str_replace('[cobranca]', $_GET["i"], $this->conteudo);
                $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
                $this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
                $this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
                $this->conteudo = str_replace('[competencia]', $this->dados_item["competencia"], $this->conteudo);
                $this->conteudo = str_replace('[valor_bonificacao]', $this->formataDinheiro($this->dados_item["valor_bonificacao"]), $this->conteudo);
				if($notas) {
					$this->conteudo = str_replace('[detalhamento]', $this->geraDetalhamentoDasNotas($notas), $this->conteudo);
				} else {
					$this->conteudo = str_replace('[detalhamento]', $this->geraDetalhamentoDasNotas(null), $this->conteudo);
				}
                $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);

            } else {
                $this->fechar();
            }
        }
        
        function gerar_nfse() {
            
            $id = $_GET['i'];
            $naoGeradasPorId = false;
            $geraEmAberto = false;
            if($_GET['c'] == 0) {
                $geraEmAberto = true;
            }
            if($_GET['n'] == 1) {
                $naoGeradasPorId = true;
            }
            
            $nfse = controle::instanciaObjeto("nfse", $this->bancodedados, null, false);
            if($naoGeradasPorId) {
                $nfse->emiteNotasNaoGeradasSJSC($naoGeradasPorId, $id);
            } else {
		
                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
                $nfse->emiteNotaSJSC($id, $geraEmAberto);
            }
             
            header("Location: ./?m=cobrancas&a=ver_nt&i=$id");
        }
        
        function ver_nota() {
            
            $dadosDaNota = $this->buscaNota($_GET["i"]);
            if($dadosDaNota['status_id'] == 5) {
                $this->conteudo .= $this->montaLog($_GET['i']);
                
            } else {
                $notaNfse = $this->buscaNotaNfsePorLocal($dadosDaNota);
                $this->conteudo .= $notaNfse["dados"];
                $botoes = array();
                if($notaNfse["log"] == false) {
                    $this->conteudo = str_replace('[cobranca]', $dadosDaNota['coa_id'], $this->conteudo);
                    
                    $podeCancelar = $notaNfse['podeCancelar'];
                    if ($dadosDaNota['status_id'] == 4 || $dadosDaNota['status_id'] == 1) {
                        if ($podeCancelar && $dadosDaNota['status_id'] == 4) {
                            if ($this->temPermissao("cancelar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=cancelar_nfse&c='.$dadosDaNota['coa_id'] . '&n=' . $dadosDaNota["numero"] . '&i=' . $dadosDaNota["id"] . '\';">';
                        }
                        if ($this->temPermissao("enviar_nfse", $this->dados_modulo["nome"])) $botoes[] = '<input type="button" name="enviar" class="buttonAzulEscuroBranco11" value="Enviar NFS-e" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=enviar_nfse&c='.$dadosDaNota['coa_id'] . '&i=' . $dadosDaNota["id"] . '\';">';
                    }
                    $botoes[] = '<input type="button" name="Imprimir"  class="buttonAzulEscuroBranco11" value="Imprimir" onClick="window.print()" />';
                }

            }
            $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        }
        
        function cancelar_nfse() {
            
            if (!$_POST) {
                $botoes = array();
                $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
                $this->conteudo .= $this->carregaArquivo("cobrancas.cancelar_nfse.inc.php");
                $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}&c={$_GET['c']}&n={$_GET['n']}", $this->conteudo);

            } else {
                $nfseID = $_GET['i'];
                $cobranca = $_GET['c'];
                $nota = $_GET['n'];
                $comentario = $_POST["comentario"];
                $comentario = $comentario ? "<br><b>Comentário:</b> ".trim($comentario) : "";
                
                $this->registraComentario($cobranca, "<b>Ação:</b> Cancelada NFS-e $nota $comentario", $_SESSION["usuario"]);
                $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $cobranca);
                
                $retorno = $this->cancelarNfsePorLocal($nfseID);
                
                $this->conteudo = "";
                $this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscarNotas) window.opener.buscarNotas(' . $cobranca . ')</script>';
                $this->conteudo .= '<script type="text/javascript">window.close()</script>';
            }
                       
        }
        
        function modalDaNotaNfsePorLocal() {
            
            $local = $this->buscaLocalDaNota();
            
            switch ($local['sigla']) {
                case "sjsc":
                    return ['l' => 820, 'a' => 720];
                    
                case "ctb":
                    return ['l' => 680, 'a' => 720];
                    
                default:
                    return ['l' => 680, 'a' => 720];
            }
        }
        
        function buscaNotaNfsePorLocal($nota) {
            
            $local = $this->buscaLocalDaNota();
            $retorno = [];
            switch ($local['sigla']) {
                case "sjsc":
                    $nfse = controle::instanciaObjeto("nfse", $this->bancodedados);
                    $date1 = date("Y-m-d 00:00:00", strtotime("{$nota['dataEmissao']}  +7 day"));
                    $date2 = date("Y-m-d 00:00:00");
                    
                    $detalhes = $nfse->ConsultarNotaSJSC($nota['id']);
                    if(!$detalhes) {
                        $retorno["dados"] = $this->montaLog($nota['id']);
                        $retorno["log"] = true;
                    } else {
                        $retorno["dados"] = $this->montaDetalhesNFSeSJSC($detalhes, $nota);
                        $retorno["log"] = false;
                    }   
                    $retorno["podeCancelar"] = $date1 >= $date2 ;
                    return $retorno;
                    
                case "ctb":
                    $date1 = date("Y-m-t 00:00:00", strtotime("{$nota['dataEmissao']}  +1 month"));
                    $date2 = date("Y-m-t 00:00:00");
                    $conteudo = $this->carregaArquivo("cobrancas.ver_nfse.inc.php");
                    $retorno = $this->buscaImagemDaNotaCtb($nota);
                    $retorno["dados"] =  str_replace('[nota]', $retorno['retorno'] , $conteudo);
                    $retorno["log"] = $retorno['erro'];
                    $retorno["podeCancelar"] = $date1 >= $date2 ;
                    
                    return $retorno;
                    
                default:
                    return null;
            }
        }
        
        function montaLog($id) {
            
            $conteudo = $this->carregaArquivo("cobrancas.ver_nfse.detalhesErros.inc.php");
            $log = $this->buscaLogDeErros($id);
            
            return str_replace('[log_erros]', $this->geraDetalhamentoDeLogDasNotas($log), $conteudo);
        }

        function montaDetalhesNFSeSJSC($nfse, $nota) {
            
            $conteudo = $this->carregaArquivo("cobrancas.ver_nfse.detalhes.inc.php");
            $conteudo = str_replace('[tributa_municipio_prestador]', $nfse->itens->lista->tributa_municipio_prestador, $conteudo);
            $conteudo = str_replace('[valor_desconto_incondicional]', $nfse->itens->lista->valor_desconto_incondicional, $conteudo);
            $conteudo = str_replace('[valor_issrf]', $nfse->itens->lista->valor_issrf, $conteudo);
            $conteudo = str_replace('[valor_deducao]', $nfse->itens->lista->valor_deducao, $conteudo);
            $conteudo = str_replace('[valor_tributavel]', $nfse->itens->lista->valor_tributavel, $conteudo);
            $conteudo = str_replace('[aliquota_item_lista_servico]', $nfse->itens->lista->aliquota_item_lista_servico, $conteudo);
            $conteudo = str_replace('[descritivo]', $nota['servico_discriminacao'], $conteudo);
            $conteudo = str_replace('[codigo_item_lista_servico]', $nfse->itens->lista->codigo_item_lista_servico, $conteudo);
            $conteudo = str_replace('[observacao]', utf8_decode($nfse->nf->observacao), $conteudo);
            $conteudo = str_replace('[valor_pis]', $nfse->nf->valor_pis, $conteudo);
            $conteudo = str_replace('[valor_cofins]', $nfse->nf->valor_cofins, $conteudo);
            $conteudo = str_replace('[valor_rps]', $nfse->nf->valor_rps, $conteudo);
            $conteudo = str_replace('[valor_contribuicao_social]', $nfse->nfvalor_contribuicao_social, $conteudo);
            $conteudo = str_replace('[valor_inss]', $nfse->nf->valor_inss, $conteudo);
            $conteudo = str_replace('[valor_ir]', $nfse->nf->valor_ir, $conteudo);
            $conteudo = str_replace('[valor_desconto]', $nfse->nf->valor_desconto, $conteudo);
            $conteudo = str_replace('[valor_total]', $nfse->nf->valor_total, $conteudo);
            $conteudo = str_replace('[cod_verificador_autenticidade]', $nfse->nf->cod_verificador_autenticidade, $conteudo);
            $conteudo = str_replace('[link_nfse]', $nfse->nf->link_nfse, $conteudo);
            $conteudo = str_replace('[situacao_descricao_nfse]', $nfse->nf->situacao_descricao_nfse, $conteudo);
            $conteudo = str_replace('[hora_nfse]', $nfse->nf->hora_nfse, $conteudo);
            $conteudo = str_replace('[data_nfse]', $nfse->nf->data_nfse, $conteudo);
            $conteudo = str_replace('[serie_nfse]', $nfse->nf->serie_nfse, $conteudo);
            $conteudo = str_replace('[numero_nfse]', $nfse->nf->numero_nfse, $conteudo);
            
            return $conteudo;
        }

        function cancelarNfsePorLocal($nota) {
            
            $local = $this->buscaLocalDaNota();
            $dadosDaNota = $this->buscaNota($nota);
            $nfse = controle::instanciaObjeto("nfse", $this->bancodedados);
            
            switch ($local['sigla']) {
                case "sjsc":
                    return $nfse->CancelarNotaSJSC($dadosDaNota["id"], $dadosDaNota["numero"]);
                    
                case "ctb":
                    return $nfse->CancelarLoteRpsCtb($dadosDaNota["protocolo"], $dadosDaNota["id"]);
                    
                default:
                    return null;
            }
        }
        
        function montaConteudoEmailNfsePorLocal($nota) {
            
            $body = "";

			$i = 1;
			foreach ($nota as $item) {

				$linkNfse = $item["link_nfse"];

				$links[$i] = "<a href='{$linkNfse}'>NFS-e {$i}</a>";
				$codigos[$i] = "<b>{$item['codigoVerificacao']}</b>";
				$numero[$i] = "<b>{$item["numero"]}</b>";
				$i++;
			}

			$body = $this->carregaArquivo("cobrancas.enviar.nfse.conteudo.sjsc.inc.php");
			$body = str_replace("[link]", "<br>" . implode("<br>", $links), $body);
			$body = str_replace("[codigo]", implode(", ", $codigos), $body);
			$body = str_replace("[data]", $nota[0]["data_nfse"], $body);
			$body = str_replace("[serie_nfse]", $nota[0]["serie_nfse"], $body);
			$body = str_replace("[tomador]", $nota[0]["tomserv_razaoSocial"], $body);
            $body = str_replace("[numero]", implode(", ", $numero), $body);

            return $body;
        }

        function buscaLocalDaNota() {
                    
            $nota = array();
            $q = "SELECT * FROM tab_notas_local_emissao WHERE padrao IS TRUE LIMIT 1";
            $query = $this->bancodedados->query($q);
            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                $nota[] = $dados;
            }

            return $nota[0];
        }
        
        function buscaLogDeErros($nfse) {
            
            $log = array();
            $q = "SELECT * FROM tab_notas_log WHERE nota = '$nfse'";
            $query = $this->bancodedados->query($q);
            
            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
                $log[] = $dados;
            }

            return $log;
        }

	##############
	### Enviar ###
	##############

	function enviar_nfse($cobrancaId = null) {

		$id = $_GET['i'];
		$cobranca = $_GET['c'];
		if($cobrancaId) {
			$id = 0;
			$cobranca = $cobrancaId;
		}
		if($id == 0) {
			$nota = $this->buscaNota($id, $cobranca , true);
		} else {
			$nota = $this->buscaNota($id, null , true);
		}

		$this->buscaDadosItem($cobranca);
		
		if (!$_POST) {
			$botoes = array();
			$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("cobrancas.enviar.nfse.inc.php");

			$emails = $this->buscaContatosEmails($this->dados_item["cliente_id"]);
			$this->conteudo = str_replace("[emails]", implode("; ", $emails), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$id}&c={$cobranca}", $this->conteudo);

		} else {
			$comentario = $_POST["comentario"];
			$emails = $_POST["emails"];
			$this->enviarNotaNFSe($cobrancaId, $emails, $comentario, $nota, $id, $cobranca);
		}
	}

	function enviarNfseAoGerar($cobrancaId = null) {

		$nota = $this->buscaNota(0, $cobrancaId , true);
		$this->buscaDadosItem($cobrancaId);

		$emails = $this->buscaContatosEmails($this->dados_item["cliente_id"]);
		
		$this->enviarNotaNFSe($cobrancaId, implode("; ", $emails), null, $nota, 0, null);
	}

	function enviarNotaNFSe($cobrancaId = null, $emails = null, $comentario = null, $nota = null, $id = null, $cobranca = null) {
		
		$comentario = $comentario ? "<br><b>Obs.: </b><br/>".trim($comentario) : "";
		sort($emails = array_unique(preg_split("/ *[ ,;] */", strtolower($emails))));

		if (count($emails) > 0 && $this->enviaNfse($emails, $comentario, $_SESSION["usuario"], $nota) ) {
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
				
			if($cobrancaId == null) {
				$this->conteudo = "";
				$this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscarNotas) window.opener.buscarNotas(' . $cobranca . ')</script>';
				$this->conteudo .= '<script type="text/javascript">window.close()</script>';
			}
		} else {
			if($cobrancaId == null) {
				print "<script type=\"text/javascript\">alert(\"Erro ao enviar e-mail!\\nFavor contactar o Administrador de Sistema.\"); window.location = \"./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}\";</script>";
			}
			die();
		}
	}

	function loginEmail($tipo = 'boleto') {
            
		$sql = "SELECT * FROM tab_email_smtp where ativo IS TRUE AND tipo LIKE '$tipo' LIMIT 1";
		$query = $this->bancodedados->query($sql);

		return $this->bancodedados->resultadoArray($query);
	}
        
	function enviaNfse($emails = null, $comentario = null, $usuario = null, $nota = null, $conta = 0) { 

            if (count($emails) > 0) {

                require_once "_global/PHPMailer/PHPMailerAutoload.php";

                $mail = new PHPMailer();
                $mail->isSMTP();
				
                $dadosEmail = $this->loginEmail();
                $mail->Host =  $dadosEmail['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $dadosEmail['usuario'];
                $mail->Password = $dadosEmail['senha'];
                $mail->SMTPSecure = $dadosEmail['seguranca'];
                $mail->Port = $dadosEmail['porta'];
                $mail->From = $dadosEmail['usuario'];
                $mail->FromName = $dadosEmail['de'];
                        
                $mail->SMTPOptions = array ('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));
                $mail->Subject = "NFS-e Online";
                $mail->isHTML(true);

				for ($i = 0; $i < count($emails); $i++) {
					$mail->addAddress($emails[$i]);
				}

                if ($comentario) {
                    $comentario = trim($comentario);
                }

				$cobrancaID = $nota[0]['coa_id'];
                
                $body = $this->montaConteudoEmailNfsePorLocal($nota);
                $body = str_replace("[comentario]", $comentario, $body);
                $mail->Body = $body; 

                if ($mail->send()) {
                    $this->registraComentario($cobrancaID, "<b>Ação:</b> Enviar NFS-e da cobrança {$cobrancaID}<br><b>E-mail(s):</b> ".implode(", ", $emails).($comentario ? "<br><b>Mensagem:</b> {$comentario}" : ""), $usuario);
                    return true;
                } else {

					if($conta < 3) {
						$this->enviaNfse($emails, $comentario, $usuario, $nota, $conta + 1);
					} else {
						$this->registraComentario($cobrancaID, "<b>Ação:</b> Enviar NFS-e da cobrança {$cobrancaID}<br><b>E-mail(s):</b> ".implode(", ", $emails).($comentario ? "<br><b>Mensagem:</b> {$comentario}" : "")."<br><b>Erro:</b> {$mail->ErrorInfo}", $usuario);
					}
                }
            }
            return false;
	}

        function buscaImagemDaNotaCtb($nota){
            
            $notaNumero = $nota["numero"];
            $codigo = $nota["codigoVerificacao"];
            

            $html_brand = "https://srv2-isscuritiba.curitiba.pr.gov.br/NotaCuritibana/NotaRPS/AutenticidadeNotaImagemNfse?LinkDireto=True";
            $ch = curl_init();

            $options = array(
                CURLOPT_URL            => $html_brand,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
                CURLOPT_POSTFIELDS     => "{'DocumentoPrestador':'17.989.371/0001-40','NumeroNotaFiscal':'{$notaNumero}','CodigoVerificacao':'{$codigo}'}",
            );
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch); 
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode != 200){
                $ret['erro'] = true;
                $ret['retorno'] = "Erro desconhecido. Procure o setor de desenvolvimento. <br/><br/> Ou tente novamente mais tarde.";
            } else {
                
                $ret['erro'] = false;
                $ret['retorno'] = $response;
                if(!empty($json = json_decode($response))) {
                    $ret['erro'] = true;
                    $ret['retorno'] = utf8_decode($json->mensagens[0]->mensagem) . "<br/>" . "NFS-e: " . $notaNumero . "<br/>" . "Cóodigo verificação: " . $codigo;
                }
            }
            $ret['code'] = $httpCode;
            curl_close($ch);
            return $ret;
        }

        function buscaNota($notaId, $cobranca = null, $tudo = false, $emitida= false) {

            $nota = array();
            $q = "SELECT * FROM tab_notas WHERE id = $notaId";
			if($cobranca) {
				$q = "SELECT * FROM tab_notas WHERE coa_id = $cobranca AND status_id = 4";
			}
            $query = $this->bancodedados->query($q);
            while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$nota[] = $dados;
            }

			if($tudo) {
				return $nota;
			}
			
            return $nota[0];
		}

        function buscaNotas($cobrancaId, $aguardando = false, $cancelado = false) {

            $notas = array();
            $where = "";
            if($aguardando) {
                $where = "AND n.status_id IN (2,5)";
            }
			if($cancelado) { 
				$where = "AND n.status_id IN (1)";
			}

            $q = "SELECT n.*, s.nome AS statusNome FROM tab_notas AS n LEFT JOIN tab_notas_status AS s ON s.id = n.status_id WHERE n.coa_id = $cobrancaId $where ORDER BY n.id";
			$query = $this->bancodedados->query($q);
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$notas[] = $dados;
			}
			
			if(count($notas) == 0 || empty($notas)) {
				$notas = false;
            }
			return $notas;
		}

		function buscaTotalNotas($cobrancaId) {

			$sql = "
					SELECT SUM(servico_valores_baseCalculo) valor, GROUP_CONCAT(numero SEPARATOR ',  ') numero
					FROM tab_notas
					WHERE numero IS NOT NULL AND coa_id = $cobrancaId AND status_id = 4";
			$query = $this->bancodedados->query($sql);
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$notas[] = $dados;
			}
			
			if(count($notas) == 0 || empty($notas)) {
				$notas = false;
            }
			return $notas[0];
		}
        
        function geraDetalhamentoDeLogDasNotas($log) {
            
            if(!$log) {
                return '';
            }
            $detalhamento = "<table width=\"100%\" cellpading=\"0\" cellspacing=\"0\" border=\"0\">";
            $cabecalho = array ("cod" => "Código", "msg" => "Discrição", "data"=> "Log gerado em");
            for ($i = 0; $i < count($log); $i++) {
                $date = new DateTime($log[$i]["data"]);
                $log[$i]["data"] = $date->format( 'd/m/Y H:i:s' );
            }
            @$listagem = controle::instanciaObjeto("listagem", $cabecalho, $log, 0, false);
            $detalhamento .= "<tr><td>".$listagem->conteudo."</td></tr>";
            $detalhamento .= "</table>";

            return $detalhamento;
        }

        function geraDetalhamentoDasNotas($notas) {
            
            $detalhamento = "<table width=\"100%\" cellpading=\"0\" cellspacing=\"0\" border=\"0\">";

            $dimesoes = $this->modalDaNotaNfsePorLocal();
            $altura = $dimesoes["a"];
            $largura = $dimesoes["l"];
            
            for ($i = 0; $i < count($notas); $i++) {
                $date = new DateTime($notas[$i]["dataEmissao"]);
                $notas[$i]["dataEmissao"] = $date->format( 'd/m/Y' );
                if($notas[$i]["status_id"] == 1 || $notas[$i]["status_id"] == 4 || $notas[$i]["status_id"] == 5 ) {
                    $notas[$i]["link"] = "javascript:verNota({$notas[$i]["id"]}, {$largura}, {$altura});";
                }
				$notas[$i]['valor'] = $this->formataDinheiro($notas[$i]['servico_valores_baseCalculo']);
            }
            $tamanho = array(40, 150, 100, 80);
            $cabecalho = array ("id" => "ID", "numero" => "Nota", "valor" => "Valor", "dataEmissao" => "Data emissão" , "statusNome" => "Status");
            $listagem = controle::instanciaObjeto("listagem", $cabecalho, $notas, 0, false, $tamanho);
            $detalhamento .= "<tr><td>".$listagem->conteudo."</td></tr>";

            $detalhamento .= "</table>";

            return $detalhamento;
	}


        ##############################
	### Ver Fatura (Modelo 21) ###
	##############################

	function ver_df_1() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if ($this->dados_item["fatura_id"]) {

				$this->conteudo .= $this->carregaArquivo("cobrancas.ver_df_1.inc.php");

				$acoes = array("ver_dg" => "Dados Cobrança", "ver_dd" => "Detalhamento");
				$notas = $this->buscaNotas($_GET["i"]);

				$totalNotas = $this->buscaTotalNotas($_GET["i"]);

				if (!in_array($this->dados_item["status_id"], array(1, 12)) || $notas) {
					$acoes["ver_nt"] = "NFS-e";
				}
				$acoes["ver_df_1"] = "Fatura ({$this->dados_item["fatura_tipo_nome"]})";
				
				if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
					$nosso_numero = preg_replace("/[^0-9]/", "", $this->dados_item['parametros']['boleto']['nosso_numero']);
					if ($this->podeVerInformacaoBoleto() && $nosso_numero) $acoes["ver_boleto"] = "Ver Boleto";
				}
                                
				$botoes = array();
				if ($this->ehCancelavelNota()) $botoes[] = '<input type="button" name="cancelar_nota" class="buttonAzulEscuroBranco11" value="Cancelar Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar_nota&i='.$this->dados_item["id"].'\';">';
				if ($this->ehRemovivelFatura()) $botoes[] = '<input type="button" name="remover_fatura" class="buttonAzulEscuroBranco11" value="Remover Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_fatura&i='.$this->dados_item["id"].'\';">';
				if ($this->ehExportavelFatura1()) $botoes[] = '<input type="button" name="exportar_fatura" class="buttonAzulEscuroBranco11" value="Exportar Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar_fatura_1&i='.$this->dados_item["id"].'\';">';
				if ($this->ehEnviavelFatura1()) $botoes[] = '<input type="button" name="enviar_fatura" class="buttonAzulEscuroBranco11" value="Enviar Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_fatura_1&i='.$this->dados_item["id"].'\';">';
				if ($this->ehRegistravelNota()) $botoes[] = '<input type="button" name="registrar_nota" class="buttonAzulEscuroBranco11" value="Registrar Nota" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=registrar_nota&i='.$this->dados_item["id"].'\';">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo = str_replace('[acoes]', $this->geraAcoes($acoes), $this->conteudo);

				$this->conteudo = str_replace('[nota_cancelada]', $this->dados_item["fatura"]["cancelada"] ? "<div style=\"background-color: rgb(232, 243, 255); text-align: center; padding: 10px; color: rgb(239, 3, 3); font-family: Arial,Helvetica,sans-serif; font-weight: bold;\">Nota Cancelada</div>" : "", $this->conteudo);
				$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[fatura_emissao]', $this->dados_item["fatura"]["data_emissao"], $this->conteudo);
				
				if($totalNotas) {
					$this->conteudo = str_replace('[fatura_valor]', $this->formataDinheiro($totalNotas["valor"]), $this->conteudo);
					$this->conteudo = str_replace('[fatura_numero]', $totalNotas["numero"], $this->conteudo); 
				} else {
					$this->conteudo = str_replace('[fatura_valor]', $this->formataDinheiro($this->dados_item["fatura"]["valor"]), $this->conteudo);
					$this->conteudo = str_replace('[fatura_numero]', $this->dados_item["fatura"]["numero"], $this->conteudo);
				}
				
				$this->conteudo = str_replace('[faturas]', $this->geraDadosFaturaListagem($this->buscaDadosFaturasItens($this->dados_item["fatura"]["id"])), $this->conteudo);
				$this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);

			} else {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");
			}

		} else {
			$this->fechar();
		}

	}

	###########################
	### Ver Fatura (Firsti) ###
	###########################

	function ver_df_2() {

	}

	###################################
	### Exportar Fatura (Modelo 21) ###
	###################################

	function exportar_fatura_1() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
			
			if ($this->dados_item["fatura_tipo_id"] == 1) {
				$boletos_fatura = controle::instanciaObjeto("cobrancas.fatura", $this->bancodedados);
				$boletos_fatura->gerarFatura($this->dados_item["fatura_id"]);
				$conteudo = $boletos_fatura->conteudo();
			} else {
				$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
				$conteudo = $faturas_lotes->pegaFaturaPdf($this->dados_item["fatura"]["ciclo"], $this->dados_item["fatura"]["codigo"], $this->dados_item["fatura"]["serie"]);
			}
			
			if ($conteudo) {

				header("Content-type: application/pdf;");
				header("Content-disposition: attachment; filename=Fatura-{$this->dados_item["codigo"]}.pdf;");
				header("Content-Length: ".strlen($conteudo));

				print $conteudo;
				die();

			}
			
		}
		
		header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df&i={$_GET["i"]}");

	}

	################################
	### Exportar Fatura (Firsti) ###
	################################

	function exportar_fatura_2() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$conteudo = $this->exportarFatura2();

			if ($conteudo) {

				header("Content-type: application/pdf;");
				header("Content-disposition: attachment; filename=Recibo-{$this->dados_item["codigo"]}.pdf;");
				header("Content-Length: ".strlen($conteudo));

				print $conteudo;
				die();

			}

		}

		header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df&i={$_GET["i"]}");

	}

	function exportarFatura2() {

		$valor = $this->formataDinheiro($this->bancodedados->resultado($this->bancodedados->query("SELECT SUM(tab_cobrancas_detalhes.cod_valor_final) FROM tab_cobrancas_detalhes INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id) WHERE tab_ajustes_financeiros.afc_id = 29 AND tab_cobrancas_detalhes.coa_id = {$this->dados_item["id"]}")), "R$", true);
		$endereco = $this->buscaEndereco($this->dados_item["cliente_id"]);
		$endereco["endereco"] = array();
		$endereco["endereco"][] = $endereco["logradouro"];
		$endereco["endereco"][] = $endereco["numero"] ? $endereco["numero"] : "s/n";
		if ($endereco["complemento"]) $endereco["endereco"][] = $endereco["complemento"];
		$endereco["endereco"] = implode(", ", $endereco["endereco"]);

		// Page Size
		$page_width = 595;
		$page_height = 842;

		// Start new pdf document
		$pdf = pdf_new();
		pdf_begin_document($pdf, "", "");
		pdf_set_info($pdf, "Author", "Olé Ineternet");
		pdf_set_info($pdf, "Title", "Recibo de Serviço de Valor Adicionado");
		pdf_begin_page_ext($pdf, $page_width, $page_height, "");

		// Box line width
		pdf_setlinewidth($pdf, 0.75);

		// Set fill color
		pdf_setcolor($pdf, "fill", "cmyk", 0.19, 0.11, 0.00, 0.00);
		pdf_rect($pdf, $page_width - 20 - 150, $page_height - 20 - 25, 150, 25);
		pdf_rect($pdf, 20, $page_height - 20 - 100 - 5 - 20, $page_width - 20 - 20, 20);
		pdf_rect($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 40, $page_width - 20 - 20, 40);
		pdf_rect($pdf, $page_width - 20 - 100, 20 + 80 + 5 + 20, 100, $page_height - 20 - 100 - 5 - 80 - 5 - 40 - 20 - 80 - 5 - 20);
		pdf_rect($pdf, 20, 20 + 80 + 5, $page_width - 20 - 20, 20);
		pdf_rect($pdf, 20, 20 + 60, $page_width - 20 - 20, 20);
		pdf_fill($pdf);

		// Set fill color
		pdf_setcolor($pdf, "fill", "cmyk", 1.00, 1.00, 1.00, 1.00);

		// Header box
		pdf_rect($pdf, 20, $page_height - 20 - 100, $page_width - 40, 100);

		// Logo vertical division
		pdf_moveto($pdf, 20 + 150, $page_height - 20);
		pdf_lineto($pdf, 20 + 150, $page_height - 20 - 100);

		// Header third column division
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20);
		pdf_lineto($pdf, $page_width - 20 - 150, $page_height - 20 - 100);

		// Header third column - 1st line
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20 - 25);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 25);

		// Header third column - 2nd line
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20 - 50);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 50);

		// Header third column - 3rd line
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20 - 75);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 75);

		// Customer box
		pdf_rect($pdf, 20, $page_height - 20 - 100 - 5 - 80, $page_width - 40, 80);

		// Customer box - 1st line
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 20);

		// Customer box - 2nd line
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 40);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 40);

		// Customer box - 3rd line
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 60);

		// Customer box - Veritcal - CPF/CNPJ
		pdf_moveto($pdf, $page_width - 20 - 200, $page_height - 20 - 100 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20 - 200, $page_height - 20 - 100 - 5 - 40);

		// Customer box - Veritcal - Inscrição Estadual
		pdf_moveto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 40);

		// Customer box - Veritcal - Cidade
		pdf_moveto($pdf, $page_width - 20 - 300, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20 - 300, $page_height - 20 - 100 - 5 - 80);

		// Customer box - Veritcal - CEP
		pdf_moveto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 80);

		// Customer box - Veritcal - UF
		pdf_moveto($pdf, $page_width - 20 - 25, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20 - 25, $page_height - 20 - 100 - 5 - 80);

		// Service box
		pdf_rect($pdf, 20, 20 + 80 + 5, $page_width - 40, $page_height - 20 - 100 - 5 - 80 - 5 - 5 - 80 - 20);

		// Service box - Title
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 80 - 5 - 20);

		// Service box - Header
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 40);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 80 - 5 - 40);

		// Service box - Footer
		pdf_moveto($pdf, 20, 20 + 80 + 5 + 20);
		pdf_lineto($pdf, $page_width - 20, 20 + 80 + 5 + 20);

		// Service box - Column Código
		pdf_moveto($pdf, 20 + 75, $page_height - 20 - 100 - 5 - 80 - 5 - 20);
		pdf_lineto($pdf, 20 + 75, 20 + 80 + 5 + 20);

		// Service box - Column Valor
		pdf_moveto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 80 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20 - 100, 20 + 80 + 5);

		// Information box
		pdf_rect($pdf, 20, 20, $page_width - 40, 80);

		// Information box - Title
		pdf_moveto($pdf, 20, 80);
		pdf_lineto($pdf, $page_width - 20, 80);

		// Draw box
		pdf_stroke($pdf);

		// Logo
		$logo = pdf_load_image($pdf, "png", DIR_RAIZ."imagens".DIRECTORY_SEPARATOR."imagens/logo_ole_internet_pequeno.png", "");
		pdf_fit_image($pdf, $logo, 20 + 5, $page_height - 20 - 87 - 4, "boxsize {140 87} fitmethod meet");
		pdf_close_image($pdf, $logo);

		// Font
		pdf_set_parameter($pdf, "FontOutline", "Arial=".DIR_RAIZ."imagens".DIRECTORY_SEPARATOR."fonts/arial.ttf");
		pdf_set_parameter($pdf, "FontOutline", "ArialB=".DIR_RAIZ."imagens".DIRECTORY_SEPARATOR."fonts/arialbd.ttf");
		$arial = pdf_findfont($pdf, "Arial", "host", 0);
		$arialb = pdf_findfont($pdf, "ArialB", "host", 0);

		// Company
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_boxed($pdf, "OLÉ INTERNET",  20 + 10 + 150, $page_height - 20 - 10 - 25, $page_width - 40 - 20 - 300, 23, "center", "");
		pdf_show_boxed($pdf, "Firsti Internet SVA LTDA ME",  20 + 10 + 150, $page_height - 20 - 10 - 25 - 12, $page_width - 40 - 20 - 300, 23, "center", "");

		// Address
		pdf_setfont($pdf, $arial, 10);
		pdf_set_value($pdf, "leading", 13);
		pdf_show_boxed($pdf, "CNPJ: 10.220.619/0001-65\nAv. Acioni de Souza Filho, 925, Praia Comprida\nSão José - Santa Catarina - 88103-790\nTelefone: (48) 3028-0000",  20 + 10 + 150, $page_height - 20 - 10 - 80, $page_width - 40 - 20 - 300, 55, "center", "");

		// Document title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_boxed($pdf, "RECIBO DE SERVIÇO\nDE VALOR ADICIONADO",  $page_width - 20 - 150, $page_height - 20 - 23 - 1, 150, 23, "center", "");

		// Header - Número
		pdf_setfont($pdf, $arialb, 9);
		pdf_show_xy($pdf, "Número:", $page_width - 20 - 150 + 2, $page_height - 20 - 25 - 10);
		pdf_setfont($pdf, $arial, 9);
		pdf_show_xy($pdf, $this->dados_item["codigo"], $page_width - 20 - 150 + 2, $page_height - 20 - 25 - 10 - 12);

		// Header - Emissão
		pdf_setfont($pdf, $arialb, 9);
		pdf_show_xy($pdf, "Emissão:", $page_width - 20 - 150 + 2, $page_height - 20 - 50 - 10);
		pdf_setfont($pdf, $arial, 9);
		pdf_show_xy($pdf, $this->dados_item["data_geracao"], $page_width - 20 - 150 + 2, $page_height - 20 - 50 - 10 - 12);

		// Header - Vencimento
		pdf_setfont($pdf, $arialb, 9);
		pdf_show_xy($pdf, "Vencimento:", $page_width - 20 - 150 + 2, $page_height - 20 - 75 - 10);
		pdf_setfont($pdf, $arial, 9);
		pdf_show_xy($pdf, $this->dados_item["data_vencimento"], $page_width - 20 - 150 + 2, $page_height - 20 - 75 - 10 - 12);

		// Customer title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_xy($pdf, "Dados do Cliente", 20 + 5, $page_height - 20 - 100 - 5 - 14);

		// Customer data - Nome / Razão Social
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Nome / Razão Social:", 20 + 3, $page_height - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $this->dados_item["cliente_nome"], 20 + 3, $page_height - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - CPF/CNPJ
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "CPF/CNPJ:", $page_width - 20  - 100 - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $this->dados_item["cliente_cpf_cnpj"], $page_width - 20  - 100 - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Inscrição Estadual
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Inscrição Estadual:", $page_width - 20  - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $this->dados_item["cliente_inscricao_estadual"], $page_width - 20 - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Endereço
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Endereço:", 20 + 3, $page_height - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["endereco"], 20 + 3, $page_height - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Bairro
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Bairro:", 20 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["bairro"], 20 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Cidade
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Cidade:", $page_width - 20 - 300 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["cidade"], $page_width - 20 - 300 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - CEP
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "CEP:", $page_width - 20 - 100 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["cep"], $page_width - 20 - 100 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - UF
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "UF:", $page_width - 20 - 25 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["estado"], $page_width - 20 - 25 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Service title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_xy($pdf, "Dados do Serviço", 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 14);

		// Service header - Código
		pdf_show_xy($pdf, "Código", 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 14);

		// Service header - Descrição do Serviço
		pdf_show_xy($pdf, "Descrição do Serviço", 20 + 75 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 14);

		// Service header - Valor
		pdf_show_xy($pdf, "Valor", $page_width - 20 - 100 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 14);

		// Service - Total Recebido
		pdf_show_xy($pdf, "Total Recebido", $page_width - 20 - 100 - pdf_stringwidth($pdf, "Total Recebido", $arialb, 10) - 5, 20 + 80 + 5 + 6);

		$itens = $this->buscaFaturasItens($this->dados_item["faturas"][2]["id"]);

		for ($i = 0; $i < count($itens); $i++) {
			$item = $itens[$i];
			$valor = $this->formataDinheiro($item["valor_total"]);
			$offset_y = $i * 15;
			pdf_setfont($pdf, $arial, 8);
			pdf_show_xy($pdf, $item["codigo"], 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 20 - 10 - 5 - $offset_y);
			pdf_show_xy($pdf, mb_strtoupper($item["descricao"], "iso-8859-1"), 20 + 75 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 20 - 10 - 5 - $offset_y);
			pdf_show_xy($pdf, $valor, $page_width - 20 - pdf_stringwidth($pdf, $valor, $arial, 8) - 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 20 - 10 - 5 - $offset_y);
			$total += $item["valor_total"];
		}

		// Total
		$total = $this->formataDinheiro($total);
		pdf_setfont($pdf, $arial, 10);
		pdf_show_xy($pdf, $total, $page_width - 20 - pdf_stringwidth($pdf, $total, $arial, 10) - 5, 20 + 80 + 5 + 6);

		// Information box - Title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_xy($pdf, "Informações Complementares", 20 + 5, 100 - 14);

		// Information box - Content
		pdf_setfont($pdf, $arial, 8);
		pdf_show_boxed($pdf, "Este só terá validade mediante o comprovante de pagamento do boleto bancário correspondente ou de depósito bancário na conta do emitente.",  20 + 5, 25, $page_width - 20 - 5, 50, "left", "");

		// End Page & Document
		pdf_end_page($pdf);
		pdf_end_document($pdf, "");

		// Return content
		return pdf_get_buffer($pdf);

	}

	#################################
	### Enviar Fatura (Modelo 21) ###
	#################################

	function enviar_fatura_1() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.enviar.inc.php");
				
				$this->conteudo = str_replace("[emails]", implode("; ", $dados_cliente["emails"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				error_reporting(E_ALL);
				ini_set('display_errors', '1');
				
				sort($emails = array_unique(preg_split("/[ ,;]+/", strtolower($_POST["emails"]))));
				
				if ($_POST["adicionar_emails"]) {
					$this->cadastrarEmailsCliente($this->dados_item["cliente_id"], $emails);
				}

				require_once "_global/PHPMailer/PHPMailerAutoload.php";

				$mail = new PHPMailer();
				$mail->isSMTP();
				$dadosEmail = $this->loginEmail();
				$mail->Host =  $dadosEmail['host'];
				$mail->SMTPAuth = true;
				$mail->Username = $dadosEmail['usuario'];
				$mail->Password = $dadosEmail['senha'];
				$mail->SMTPSecure = $dadosEmail['seguranca'];
				$mail->Port = $dadosEmail['porta'];
				$mail->FromName = $dadosEmail['de'];

				$mail->isHTML(true);
				//$mail->From = "financeiro@oletelecom.com.br";
				$mail->addReplyTo("financeiro@oletelecom.com.br", "Olé Telecom");
				$mail->Subject = "Fatura Olé Telecom";

				$mail->SMTPOptions = array (
					'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					)
				);


				for ($i = 0; $i < count($emails); $i++) {
					$mail->addAddress($emails[$i], $this->dados_item["cliente_nome"]);
				}

				$mail->Body = $this->getConteudoEmailFatura($this->dados_item);

				// Anexos
				
				if ($this->dados_item["fatura_tipo_id"] == 1) {
					$boletos_fatura = controle::instanciaObjeto("cobrancas.fatura", $this->bancodedados);
					$boletos_fatura->gerarFatura($this->dados_item["fatura_id"]);
					$conteudo = $boletos_fatura->conteudo();
				} else {
					$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
					$conteudo = $faturas_lotes->pegaFaturaPdf($this->dados_item["fatura"]["ciclo"], $this->dados_item["fatura"]["codigo"], $this->dados_item["fatura"]["serie"]);
				}

				$mail->AddStringAttachment($conteudo, "Fatura Olé Telecom {$this->dados_item["codigo"]}.pdf", "base64", "application/pdf");

				if ($mail->send()) {

					$_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
					$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Enviar Nota<br><b>E-mail(s):</b> ".implode(", ", $emails).$_POST["comentario"], $_SESSION["usuario"]);

					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

				} else {
					
					print "<script type=\"text/javascript\">alert(\"Erro ao enviar e-mail!\\nFavor contactar o Administrador de Sistema.\"); window.location = \"./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}\";</script>";
					die();
					
				}

			}

		} else {
			$this->fechar();
		}

	}

	##############################
	### Enviar Fatura (Firsti) ###
	##############################

	function enviar_fatura_2() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.enviar.inc.php");
				
				$this->conteudo = str_replace("[emails]", implode("; ", $dados_cliente["emails"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				sort($emails = array_unique(preg_split("/ *[ ,;] */", strtolower($_POST["emails"]))));

				if ($_POST["adicionar_emails"]) {
					$this->cadastrarEmailsCliente($this->dados_item["cliente_id"], $emails);
				}

				$mensagens_emails = controle::instanciaObjeto("mensagens.emails");

				$mensagens_emails->setRemetente("financeiro@oletelecom.com.br", "Olé Internet");
				$mensagens_emails->setAssunto("Fatura Olé Internet");
				for ($i = 0; $i < count($emails); $i++) {
					$mensagens_emails->adicionarDestinatario($emails[$i], $this->dados_item["clinte_nome"]);
				}
				$mensagens_emails->setConteudo($this->getConteudoEmailFatura($this->dados_item));

				$conteudo = $this->exportarFatura2();
				$mensagens_emails->adicionarAnexo($conteudo, "Fatura Olé Internet {$this->dados_item["codigo"]}.pdf", "base64", "application/pdf");

				if ($mensagens_emails->enviar()) {

					$_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
					$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Enviar Nota<br><b>E-mail(s):</b> ".implode(", ", $emails).$_POST["comentario"], $_SESSION["usuario"]);

					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

				} else {
					print "<script type=\"text/javascript\">alert(\"Erro ao enviar e-mail!\nFavor contactar o Administrador de Sistema.\"); window.location = \"./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}\";</script>";
					die();
				}

			}

		} else {
			$this->fechar();
		}

	}

	function getConteudoEmailFatura($dados_cobranca) {

	return "
		<html>
		<head>
		<style type=\"text/css\">
		p { font-family: Verdana, Arial, sans-serif; font-size: 12px; color: #444444; }
		</style>
		</head>
		<body>
		<p>Prezado cliente,</p>
		<p>Segue a sua fatura em anexo.</p>
		<p>Nome do Titular: <b>{$dados_cobranca["cliente_nome"]}</b></p>
		<p>Em caso de dúvidas, entre em contato conosco através <br />deste e-mail ou do telefone (48) 3028-0000.</p>
		<p>Olé Telecom</p>
		</body>
		</html>";

	}

	######################
	### Remover Fatura ###
	######################

	function remover_fatura() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->ehRemovivelFatura()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.remover_fatura.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$this->removerFatura($this->dados_item["fatura_id"]);

				$this->finalizaBloqueio($this->dados_item["cliente_id"]);
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	function removerFatura($fatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai
			FROM
				tab_cobrancas
				INNER JOIN tab_faturas ON (tab_faturas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_faturas.fat_id = $fatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			// Atualiza fatura gerada na cobrança
			$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_id = {$dados["cobranca_id"]}");
			if ($dados["cobranca_pai"]) {
				// Atualiza fatura gerada na cobrança pai
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_id = {$dados["cobranca_pai"]} AND (SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0");
				// Atualiza fatura gerada nas cobranças irmãs
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_pai = {$dados["cobranca_pai"]} AND (SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0");
			} else {
				// Atualiza fatura gerada nas cobranças filhas
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_pai = {$dados["cobranca_id"]} AND (SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0");
			}
		}

		$query = $this->bancodedados->query("
			SELECT
				fcl_id AS cliente_lote_id,
				(SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.fcl_id = tab_faturas_lotes_clientes.fcl_id) AS faturas
			FROM
				tab_faturas_lotes_clientes
			WHERE
				fcl_id = (SELECT fcl_id FROM tab_faturas WHERE fat_id = $fatura_id)");
		
		$dados_cliente_lote = $this->bancodedados->resultadoArray($query);

		$query = $this->bancodedados->query("
			SELECT
				fco_id AS cobranca_lote_id,
				(SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_faturas_lotes_cobrancas.coa_id) AS faturas
			FROM
				tab_faturas_lotes_cobrancas
			WHERE
				coa_id = (SELECT coa_id FROM tab_faturas WHERE fat_id = $fatura_id)");
		
		$dados_cobranca_lote = $this->bancodedados->resultadoArray($query);

		// Desassocia fatura
		$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET fat_id = NULL WHERE fat_id = $fatura_id");

		// Remove Fatura Itens
		$this->bancodedados->query("DELETE FROM tab_faturas_itens WHERE fat_id = $fatura_id");

		// Remove Fatura
		$this->bancodedados->query("DELETE FROM tab_faturas WHERE fat_id = $fatura_id");

		if ($dados_cobranca_lote["faturas"] == 1) {
			// Remove Fatura Cobrança
			$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fco_id = {$dados_cobranca_lote["cobranca_lote_id"]}");
		}

		if ($dados_cliente_lote["faturas"] == 1) {
			// Remove Fatura Cliente
			$this->bancodedados->query("DELETE FROM tab_faturas_lotes_clientes WHERE fcl_id = {$dados_cliente_lote["cliente_lote_id"]}");
		}

	}

	#####################
	### Cancelar Nota ###
	#####################

	function cancelar_nota() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->ehCancelavelNota()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.cancelar_nota.inc.php");
				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				if($this->dados_item["fatura"]['tipo_id'] == 1) {
					$retorno = "100";
				} else {
					$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
					$retorno = $faturas_lotes->cancelarFatura($this->dados_item["fatura"]["id"]);
				}

				if ($retorno == "100") {

					$this->cancelarFatura($this->dados_item["fatura"]["id"]);

					$comentario = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
					$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Nota Cancelada{$comentario}", $_SESSION["usuario"]);

					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	function cancelarFatura($fatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai
			FROM
				tab_cobrancas
				INNER JOIN tab_faturas ON (tab_faturas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_faturas.fat_id = $fatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["cobranca_pai"]) {
				// Atualiza fatura gerada na cobrança pai
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_id = {$dados["cobranca_pai"]} AND (SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0");
				// Atualiza fatura gerada nas cobranças irmãs
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL  WHERE coa_pai = {$dados["cobranca_pai"]} AND (SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0");
			} else {
				// Atualiza fatura gerada nas cobranças filhas
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL  WHERE coa_pai = {$dados["cobranca_id"]} AND (SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0");
			}
		}

		// Zera fatura lote cobrança
		$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET fco_valor_scm = 0, fco_valor_sva = 0, fco_valor_juro = 0, fco_valor_instalacao = 0, fco_valor_equipamentos = 0, fco_valor_servicos = 0, fco_valor_garantia = 0 WHERE fat_id = {$fatura_id}");
		// Cancela fatura
		$this->bancodedados->query("UPDATE tab_faturas SET fat_cancelada = 1 WHERE fat_id = {$fatura_id}");

	}

	################
	### Exportar ###
	################

	function exportar() {

		$exportacao = controle::instanciaObjeto("cobrancas.exportacao", $this->bancodedados);
		$exportacao->exportaCobranca($_GET["i"]);

	}

	################
	### Cancelar ###
	################

	function cancelar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->ehCancelavel()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.cancelar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$this->cancelarCobranca($_GET["i"], $_POST["comentario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	function cancelarFilhosDoGrupo($cobranca_id, $comentarios = null) {

		$query = $this->bancodedados->query("
			SELECT 
				coa_id AS cobranca_id 
			FROM 
				tab_cobrancas 
			WHERE 
				coa_grupo_id = $cobranca_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->cancelarCobranca($dados["cobranca_id"], $comentarios, true);
			$this->cancelarFilhosDoGrupo($dados["cobranca_id"], $comentarios);
		}

	}

	function cancelarCobranca($cobranca_id, $comentarios = null, $grupo = false) {

		if (!$this->dados_item) {
			$this->buscaDadosItem($cobranca_id);
		}
	
		if ($this->ehCancelavel()) {

			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_data_processamento = NULL, coa_data_pagamento = NULL, coa_data_credito = NULL, cot_id = 12 WHERE coa_id = $cobranca_id");
			$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = $cobranca_id");

			if ($this->ehRegistrada($cobranca_id)) {
				$query = "UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = $cobranca_id";
				$query = $this->bancodedados->query($query);
			}

			$comentario = $comentarios ? "<br><b>Comentário:</b> ".trim($comentarios) : "";
			$this->registraComentario($cobranca_id, "<b>Ação:</b> Cobrança Cancelada$comentario", $_SESSION["usuario"]);

			if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
				$boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
				if($this->ehSicredi($cobranca_id)) {
					$boletoSicredi->cancelarBoleto($cobranca_id);
				}
				if ($grupo) {
					$cobrancas_filho = $this->buscaCobrancasFilho($cobranca_id);
					foreach ($cobrancas_filho as $cobranca_filho_id) {
						$this->cancelarCobranca($cobranca_filho_id, $comentarios);
						if($this->ehSicredi($cobranca_filho_id)) {
							$boletoSicredi->cancelarBoleto($cobranca_filho_id);
						}
					}
				}
			} else {
				if ($grupo) {
					$cobrancas_filho = $this->buscaCobrancasFilho($cobranca_id);
					foreach ($cobrancas_filho as $cobranca_filho_id) {
						$this->cancelarCobranca($cobranca_filho_id, $comentarios);
					}
				}
			}

			$query = $this->bancodedados->query("SELECT cli_id FROM tab_cobrancas WHERE coa_id = $cobranca_id");
			
			$cliente_id = $this->bancodedados->resultado($query);
			
			$this->finalizaBloqueio($cliente_id);

		}

	}

	###################
	### Descancelar ###
	###################

	function descancelar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->ehDescancelavel()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.descancelar.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$this->bancodedados->query("UPDATE tab_cobrancas SET cot_id = 1 WHERE coa_id = {$_GET["i"]}");
				$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 0 WHERE coa_id = {$_GET["i"]}");

				if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Cobrança Descancelada$comentario", $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

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

		if ($this->ehRemovivel()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.remover.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi" && strlen($this->dados_item['parametros']['boleto']['qrcode'] > 0)) {
					$boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
                    if($this->ehSicredi($this->dados_item["id"])) {
                        $boletoSicredi->cancelarBoleto($this->dados_item["id"]);
					}
				}
                                
				$this->removerCobranca($this->dados_item["id"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				$this->fechar();

			}

		} else {
			$this->fechar();
		}

	}

	function removerCobranca($cobranca_id) {

		$query = "
			SELECT
				tab_cobrancas.coa_pai AS coa_pai,
				tab_clientes.cli_bloqueio_dias AS bloqueio_dias
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_cobrancas.coa_id = $cobranca_id";
		$query = $this->bancodedados->query($query);

		$dados_cobranca = $this->bancodedados->resultadoArray($query);

		if ($dados_cobranca["coa_pai"] && $dados_cobranca["bloqueio_dias"]) {
			$this->atualizaDataBloqueio($dados_cobranca["coa_pai"], $dados_cobranca["bloqueio_dias"]);
		}

		$this->bancodedados->query("DELETE FROM tab_cobrancas_detalhes WHERE coa_id = $cobranca_id AND cod_pai IS NOT NULL");
		$this->bancodedados->query("DELETE FROM tab_cobrancas_detalhes WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_cobrancas_comentarios WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("UPDATE tab_cobrancas SET rec_id = NULL WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_recuperacao_contatos WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_cobrancas_remessas_itens WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_cobrancas_retornos_registros WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_cobrancas_retornos_nao_encontradas WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_cobrancas_retornos_erros WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_sicredi_log WHERE coa_id = $cobranca_id");
		$this->bancodedados->query("DELETE FROM tab_cobrancas WHERE coa_id = $cobranca_id");
		
	}

	function atualizaDataBloqueio($cobranca_id, $bloqueio_dias) {

		$query = $this->bancodedados->query("
			SELECT
				coa_data_bloqueio AS bloqueio
			FROM
				tab_cobrancas
			WHERE
				coa_pai = $cobranca_id
			ORDER BY
				coa_data_bloqueio DESC
			LIMIT
				0, 1");

		if (($bloqueio = $this->bancodedados->resultado($query)) != false) {
		
			$this->bancodedados->query("
				UPDATE 
					tab_cobrancas 
				SET 
					coa_data_bloqueio = '$bloqueio' 
				WHERE 
					coa_id = $cobranca_id AND 
					DATE_ADD(coa_data_vencimento, INTERVAL $bloqueio_dias DAY) < '$bloqueio'");

		} else {
			
			$this->bancodedados->query("
				UPDATE 
					tab_cobrancas 
				SET 
					coa_data_bloqueio = DATE_ADD(coa_data_vencimento, INTERVAL $bloqueio_dias DAY) 
				WHERE 
					coa_id = $cobranca_id");
			
		}

	}

	function buscaSistemaCobranca($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				MAX(pli_id) AS sistema_id
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
			WHERE
				coa_id = $cobranca_id");

		return $this->bancodedados->resultado($query);

	}

	function atualizaComissaoPrimeiroPagamento($cobranca_id, $debug = false) {

		if ($debug) {
			print "$cobranca_id";
		}
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas_detalhes.ass_id AS assinatura_id,
				tab_vendas.ven_id AS venda_id
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_vendas ON (tab_cobrancas_detalhes.ass_id = tab_vendas.ass_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = {$cobranca_id} AND
				tab_vendas.ven_primeiro_pagamento IS NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$primeiro_pagamento = $this->buscaPrimeiroPagamentoAssinaturaCompleto($dados["assinatura_id"]);
			
			if ($primeiro_pagamento) {
				
				$this->bancodedados->query("UPDATE tab_vendas SET ven_primeiro_pagamento = '{$primeiro_pagamento}' WHERE ven_id = {$dados["venda_id"]}");
				
				if ($debug) {
					print "\t$primeiro_pagamento";
				}
				
			}
			
		}
		
		if ($debug) {
			print "\n";
			flush();
		}
	
		/*

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas_detalhes.ass_id AS assinatura_id
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_cobrancas_detalhes.ass_id IS NOT NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$data = $this->buscaPrimeiroPagamentoAssinatura($dados["assinatura_id"]);

			if ($data) {
				
				$this->bancodedados->query("
					UPDATE 
						tab_vendas 
					SET 
						ven_primeiro_pagamento = IF(ven_primeiro_pagamento IS NULL OR ven_primeiro_pagamento > '$data', '$data', ven_primeiro_pagamento) 
					WHERE 
					ass_id = {$dados["assinatura_id"]}");
					
			} else {
				
				$this->bancodedados->query("
					UPDATE 
						tab_vendas 
					SET 
						ven_primeiro_pagamento = NULL 
					WHERE 
						ass_id = {$dados["assinatura_id"]}");
						
			}

		}
		
		*/

	}

	function buscaPrimeiroPagamentoAssinaturaCompleto($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				MIN(tab_cobrancas.coa_data_processamento) AS processamento
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
			WHERE
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1) AND

				tab_cobrancas_detalhes.ass_id = {$assinatura_id} AND

				DAY(tab_cobrancas_detalhes.cod_competencia_inicio) = 1 AND
				MONTH(tab_cobrancas_detalhes.cod_competencia_inicio) = MONTH(tab_cobrancas_detalhes.cod_competencia_termino) AND
				LAST_DAY(tab_cobrancas_detalhes.cod_competencia_termino) = tab_cobrancas_detalhes.cod_competencia_termino");

		return $this->bancodedados->resultado($query);
		
	}

	function buscaPrimeiroPagamentoAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_data_processamento
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_cobrancas ON (tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_cobrancas_detalhes.ass_id = $assinatura_id AND
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)
			ORDER BY
				tab_cobrancas.coa_data_processamento
			LIMIT
				0, 1");

		return $this->bancodedados->resultado($query);

	}

	function aprovaContrato($cobranca_id) {

		$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_contratos.cor_id AS contrato_id
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
				INNER JOIN tab_contratos ON (tab_ajustes_financeiros.cor_id = tab_contratos.cor_id)
			WHERE
				tab_cobrancas.coa_id = $cobranca_id AND
				tab_ajustes_financeiros.afc_id = 19 AND
				tab_contratos.crs_id IN (11, 12)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos->financeiroAprovar($contratos->buscaDadosContrato($dados["contrato_id"]), "Recebimento de cobrança Pré-Pago detectado", null, true);
		}

	}

	function buscaVendasContrato($contrato_id) {

		$contratos = array();

		$query = $this->bancodedados->query("
			SELECT
				ven_id AS venda_id
			FROM
				tab_vendas
			WHERE
				cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["venda_id"];
		}

		return $contratos;

	}
	
	function buscaDadosStatus($status_id) {

		$query = $this->bancodedados->query("
			SELECT
				cot_id AS id,
				cot_nome AS nome,
				cot_recebido AS recebido,
				cot_manual AS manual
			FROM
				tab_cobrancas_status
			WHERE
				cot_id = $status_id");

		return $this->bancodedados->resultadoArray($query);
		
	}

	##########################
	### Validar Liquidação ###
	##########################
	
	function verificar_liquidacao() {
	
	    $this->buscaDadosItem($_GET["i"]);
	    
	    $nossoNumero = empty($_GET['n'])? $this->dados_item['parametros']['boleto']['nosso_numero'] : $_GET['n'] ;
	    
	    if ($this->dados_item && $this->podeVerificarLiquidacao()) {
	        if (!$_POST) {
	            
	            $botoes = array();
	            $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	            $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
	            $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
	            $this->conteudo .= $this->carregaArquivo("cobrancas.verificar_liquidacao.inc.php");
	            
	            $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
	            $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}&n={$nossoNumero}", $this->conteudo);
	            
	        } else {
	            
	            $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
	            $seuNumero = empty($_GET['i'])? (int) $this->dados_item["id"] : $_GET["i"];
	            if($nossoNumero) {
	                if($boleto = $boletoSicredi->buscaBoletoPeloNossoNumero($nossoNumero)) {
	                    $boletoDataEvento = date('Y-m-d');
	                    $liquidacao = $boleto['dadosLiquidacao'];
	                    if(!empty($liquidacao)) {
	                        $registro["data_processamento"] = $boletoDataEvento;
	                        $registro["data_credito"] = $boletoDataEvento;
	                        $registro["valor_juro"] = $liquidacao['juros'] * 100;
	                        $registro["valor_desconto"] = $liquidacao['desconto'] * 100;
	                        $registro["valor_acrescimo"] = $liquidacao['multa'] * 100;
	                        $registro["valor_decrescimo"] = $liquidacao['abatimento'] * 100;
	                        $registro["valor_total"] = $liquidacao['valor'] * 100;
	                        $registro["valor_iof"] = 0;
	                        $registro["id"] = $seuNumero;
	                        $registro["seuNumero"] = $seuNumero;
	                        $registro["nossoNumero"] = $nossoNumero;
	                        $registro["tipoLiquidacao"] = $boleto['situacao'];
	                        $registro["valor_original"] = $this->calculaValorOriginal($registro);
	
	                        $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
	                        $cobrancas_retornos->darBaixa($registro);
	                        
	                        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");
	                    } else if(!empty($boleto['dataBaixa'])) {
	                        print "<script type=\"text/javascript\">alert('Boleto foi cancelado.'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}';</script>";
	                    } else  {
	                        print "<script type=\"text/javascript\">alert('Cobrança ainda não foi paga.'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}';</script>";
	                    }
	                } else {
	                    print "<script type=\"text/javascript\">alert('Problema para fazer a verificação do pagamento ou boleto inexistente.'); window.location = './?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}';</script>";
	                }
	            }
	        }
	    }
	}
	
	function calculaValorOriginal($dados) {
	    $valor = $dados['valor_total'] + ($dados['valor_desconto'] + $dados['valor_decrescimo']) - ($dados['valor_juro'] + $dados['valor_acrescimo']);
	
	    return $valor;
	}

	#################
	### Dar Baixa ###
	#################

	function dar_baixa() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehBaixavel()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.dar_baixa.inc.php");

				$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
				$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
				$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaStatusManual(), null, true, "tipo", "100%", "atualizaCobrancaBaixa(this)"), $this->conteudo);
				$this->conteudo = str_replace('[data_vencimento]', $this->dados_item["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace('[data_pagamento]', date("d/m/Y"), $this->conteudo);
				$this->conteudo = str_replace('[valor_original]', $this->formataDinheiro($this->dados_item["valor_original"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_bonificacao]', $this->formataDinheiro($this->dados_item["valor_bonificacao"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$dados_status = $this->buscaDadosStatus($_POST["tipo"]);

				$_POST["data_pagamento"] = $dados_status["recebido"] && $_POST["data_pagamento"] && $_POST["data_pagamento"] != "00/00/0000" ? "'".substr($_POST["data_pagamento"], 6, 4)."-".substr($_POST["data_pagamento"], 3, 2)."-".substr($_POST["data_pagamento"], 0, 2)."'" : "NULL";
				$_POST["valor_juro"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_juro"]), 10);
				$_POST["valor_desconto"] =  intval(preg_replace("/[^0-9]/", "", $_POST["valor_desconto"]), 10);
				$_POST["valor_acrescimo"] =  intval(preg_replace("/[^0-9]/", "", $_POST["valor_acrescimo"]), 10);
				$_POST["valor_decrescimo"] =  intval(preg_replace("/[^0-9]/", "", $_POST["valor_decrescimo"]), 10);
				$_POST["valor_total"] =  intval(preg_replace("/[^0-9]/", "", $_POST["valor_total"]), 10);
				$_POST["comentario"] = addslashes(preg_replace("/[\r\n\t ]+/", " ", $_POST["comentario"]));

				$this->bancodedados->query("
					UPDATE 
						tab_cobrancas 
					SET 
						coa_data_processamento = CURDATE(), 
						coa_data_pagamento = {$_POST["data_pagamento"]}, 
						coa_data_credito = {$_POST["data_pagamento"]}, 
						coa_valor_juro = {$_POST["valor_juro"]}, 
						coa_valor_desconto = {$_POST["valor_desconto"]}, 
						coa_valor_acrescimo = {$_POST["valor_acrescimo"]}, 
						coa_valor_decrescimo = {$_POST["valor_decrescimo"]}, 
						coa_valor_total = {$_POST["valor_total"]}, 
						cot_id = {$_POST["tipo"]} 
					WHERE 
						coa_id = {$_GET["i"]}");

				if ($this->ehRegistrada($_GET["i"])) {
					$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$_GET["i"]}");
				}
				
				$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 0 WHERE coa_id = {$_GET["i"]}");

				if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Dar Baixa$comentario", $_SESSION["usuario"]);
				
				if ($this->dados_item["coa_pai"]) {

					if ($_POST["tipo"] == 6) {
						$this->baixarIndevidaOutraVia($this->dados_item["coa_pai"], $this->dados_item["codigo"], $comentario);
					} elseif ($_POST["tipo"] == 7) {
						$this->baixarNegociadaOutraVia($this->dados_item["coa_pai"], $this->dados_item["codigo"], $comentario);
					}  else {
						$this->baixarPagaEmOutraVia($this->dados_item["coa_pai"], $this->dados_item["codigo"], $comentario);
					}

					$cobrancas_filho = $this->buscaCobrancasFilho($this->dados_item["coa_pai"]);
					
					foreach ($cobrancas_filho as $cobranca_filho_id) {
						if ($cobranca_filho_id != $_GET["i"]) {
							if ($_POST["tipo"] == 6) {
								$this->baixarIndevidaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
							} elseif ($_POST["tipo"] == 7) {
								$this->baixarNegociadaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
							} else {
								$this->baixarPagaEmOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
							}
						}
					}

				}

				$cobrancas_filho = $this->buscaCobrancasFilho($_GET["i"]);
				
				foreach ($cobrancas_filho as $cobranca_filho_id) {
					if ($_POST["tipo"] == 6) {
						$this->baixarIndevidaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
					} elseif ($_POST["tipo"] == 7) {
						$this->baixarNegociadaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
					} else {
						$this->baixarPagaEmOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
					}
				}

				if ($_POST["tipo"] != 6) {
					$this->baixarFilhosDoGrupo($this->dados_item["id"], $this->dados_item["codigo"], $comentario);
				}

				if ($this->dados_item["grupo_id"]) {
					$this->cancelarGrupoPai($this->dados_item["grupo_id"], $comentario);
				}

				$this->finalizaBloqueio($this->dados_item["cliente_id"]);
				
				if ($dados_status["recebido"]) {
					$this->atualizaComissaoPrimeiroPagamento($_GET["i"]);
					$this->aprovaContrato($_GET["i"]);
				}

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

                if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
                     $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
                     if($this->ehSicredi($this->dados_item['id'])) {
                         $boletoSicredi->cancelarBoleto($this->dados_item['id']);
                     }
                }

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}
	
	function baixarCobranca($cobranca_id, $tipo_id, $data_pagamento, $valor_final, $comentario) {

		$this->buscaDadosItem($cobranca_id);

		$dados_status = $this->buscaDadosStatus($tipo_id);

		$vencimento = substr($this->dados_item["data_vencimento"], 6, 4)."-".substr($this->dados_item["data_vencimento"], 3, 2)."-".substr($this->dados_item["data_vencimento"], 0, 2);
		$pagamento = substr($data_pagamento, 6, 4)."-".substr($data_pagamento, 3, 2)."-".substr($data_pagamento, 0, 2);
		$dias_apos_vencimento = (strtotime($pagamento) - strtotime($vencimento)) / 60 / 60 / 24;

		$valor_original = intval(preg_replace("/[^0-9]/", "", $this->dados_item["valor_original"]), 10);
		$valor_total = intval(preg_replace("/[^0-9]/", "", $valor_final), 10);
		$valor_bonificacao = $dias_apos_vencimento > 0 ? 0 : intval(preg_replace("/[^0-9]/", "", $this->dados_item["valor_bonificacao"]), 10);
		$valor_juro = $dias_apos_vencimento > 0 ? $this->calculaJuro($valor_original, $vencimento, $pagamento) : 0;
		$valor_desconto = 0;
		$valor_sub_total = $valor_original - $valor_bonificacao + $valor_juro;
		$valor_acrescimo = $valor_total > $valor_sub_total ? $valor_total - $valor_sub_total : 0;
		$valor_decrescimo = $valor_total < $valor_sub_total ? $valor_sub_total - $valor_total : 0;

		$data_pagamento = $dados_status["recebido"] && $data_pagamento && $data_pagamento != "00/00/0000" ? "'".substr($data_pagamento, 6, 4)."-".substr($data_pagamento, 3, 2)."-".substr($data_pagamento, 0, 2)."'" : "NULL";
		$comentario = addslashes(preg_replace("/[\r\n\t ]+/", " ", $comentario));

		/**
		 * Atualiza cobranca
		 */

		$this->bancodedados->query("
			UPDATE 
				tab_cobrancas 
			SET 
				coa_data_processamento = CURDATE(), 
				coa_data_pagamento = {$data_pagamento}, 
				coa_data_credito = {$data_pagamento}, 
				coa_valor_juro = {$valor_juro}, 
				coa_valor_desconto = {$valor_desconto}, 
				coa_valor_acrescimo = {$valor_acrescimo}, 
				coa_valor_decrescimo = {$valor_decrescimo}, 
				coa_valor_total = {$valor_total}, 
				cot_id = {$tipo_id} 
			WHERE 
				coa_id = {$cobranca_id}");

		/**
		 * Sinaliza remessa
		 */
		
		if ($this->ehRegistrada($cobranca_id)) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$cobranca_id}");
		}
		
		/**
		 * Descancela detalhes
		 */
		
		$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 0 WHERE coa_id = {$cobranca_id}");

		/**
		 * Registra Comentários
		 */
		
		if ($comentario) {
			$comentario = "<br><b>Comentário:</b> ".trim($comentario);
		}
		
		$this->registraComentario($cobranca_id, "<b>Ação:</b> Dar Baixa$comentario", $_SESSION["usuario"]);

		/**
		 * Baixa Cobrança Pai
		 */
		
		if ($this->dados_item["coa_pai"]) {

			if ($tipo_id == 6) {
				$this->baixarIndevidaOutraVia($this->dados_item["coa_pai"], $this->dados_item["codigo"], $comentario);
			} elseif ($tipo_id == 7) {
				$this->baixarNegociadaOutraVia($this->dados_item["coa_pai"], $this->dados_item["codigo"], $comentario);
			}  else {
				$this->baixarPagaEmOutraVia($this->dados_item["coa_pai"], $this->dados_item["codigo"], $comentario);
			}

			/**
			 * Baixa Cobranças Irmãs
			 */
			
			$cobrancas_filho = $this->buscaCobrancasFilho($this->dados_item["coa_pai"]);
			
			foreach ($cobrancas_filho as $cobranca_filho_id) {
				if ($cobranca_filho_id != $cobranca_id) {
					if ($tipo_id == 6) {
						$this->baixarIndevidaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
					} elseif ($tipo_id == 7) {
						$this->baixarNegociadaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
					} else {
						$this->baixarPagaEmOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
					}
				}
			}

		}

		/**
		 * Baixa Cobranças Filhas
		 */
		
		$cobrancas_filho = $this->buscaCobrancasFilho($cobranca_id);
		
		foreach ($cobrancas_filho as $cobranca_filho_id) {
			if ($tipo_id == 6) {
				$this->baixarIndevidaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
			} elseif ($tipo_id == 7) {
				$this->baixarNegociadaOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
			} else {
				$this->baixarPagaEmOutraVia($cobranca_filho_id, $this->dados_item["codigo"], $comentario);
			}
		}

		/**
		 * Baixa Filhos do Grupo
		 */
		
		if ($tipo_id != 6) {
			$this->baixarFilhosDoGrupo($this->dados_item["id"], $this->dados_item["codigo"], $comentario);
		}

		/**
		 * Cancela Grupo Pai
		 */
		
		if ($this->dados_item["grupo_id"]) {
			$this->cancelarGrupoPai($this->dados_item["grupo_id"], $comentario);
		}

		/**
		 * Finaliza ajuste de bloqueio se não tiver outra cobrança em atraso
		 */
		
		$this->finalizaBloqueio($this->dados_item["cliente_id"]);
		
		/**
		 * Atualiza Comissão Primeiro Pagamento e Aprova Contrato
		 */
		
		if ($dados_status["recebido"]) {
			$this->atualizaComissaoPrimeiroPagamento($cobranca_id);
			$this->aprovaContrato($cobranca_id);
		}
	
        if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
             $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
             if($this->ehSicredi($this->dados_item['id'])) {
                 $boletoSicredi->cancelarBoleto($this->dados_item['id']);
             }
        }

	}

	function calculaJuro($valor_final, $data_vencimento_original, $data_vencimento_final) {

		if (($dias = (strtotime($data_vencimento_final) - strtotime($data_vencimento_original)) / 60 / 60 / 24) > 0) {
			return intval(round(($valor_final * 0.02) + ($valor_final * 0.01 * $dias / 30)));
		}
		return false;

	}

	function baixarFilhosDoGrupo($cobranca_id, $codigo, $comentarios) {

		$query = $this->bancodedados->query("
			SELECT
				coa_id
			FROM
				tab_cobrancas
			WHERE
				coa_grupo_id = $cobranca_id");

		while (($cobranca_id = $this->bancodedados->resultado($query)) != false) {
			$this->baixarPagaEmOutraVia($cobranca_id, $codigo, $comentarios);
			$this->baixarFilhosDoGrupo($cobranca_id, $codigo, $comentarios);
		}

	}

	function cancelarGrupoPai($cobranca_id, $comentarios) {

		$this->cancelarCobranca($cobranca_id, $comentarios, true);

		$query = $this->bancodedados->query("
			SELECT 
				coa_grupo_id 
			FROM 
				tab_cobrancas 
			WHERE 
				coa_id = $cobranca_id");
		
		if (($grupo_id = $this->bancodedados->resultado($query)) != false) {
			$this->cancelarCobranca($grupo_id, $comentarios, true);
		}

	}

    function darBaixaDoBoleto($cobrancaId) {

        if($this->ehSicredi($cobrancaId)) {
            $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
            $boletoSicredi->cancelarBoleto($cobrancaId);
        }
    }
    

    function ehSicredi($id) {
        
        $this->buscaDadosItem($id);
        
        return $this->dados_item["formatos_parametros"]["parametros"] == "sicredi";
    }

	function baixarPagaEmOutraVia($cobranca_id, $codigo, $comentarios = null) {

		$this->bancodedados->query("UPDATE tab_cobrancas SET cot_id = 11, coa_data_processamento = CURDATE() WHERE coa_id = $cobranca_id");

		if ($this->ehRegistrada($cobranca_id)) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$cobranca_id}");
		}

		$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = $cobranca_id");

		$this->registraComentario($cobranca_id, "<b>Ação:</b> Baixa na Cobrança Código: <b>$codigo</b>$comentarios", $_SESSION["usuario"]);

        if($this->ehSicredi($cobranca_id)) {
            $this->darBaixaDoBoleto($cobranca_id);
        }
    
	}

	function baixarIndevidaOutraVia($cobranca_id, $codigo, $comentarios = null) {

		$this->bancodedados->query("
			UPDATE 
				tab_cobrancas 
			SET 
				coa_valor_juro = 0, 
				coa_valor_desconto = 0, 
				coa_valor_acrescimo = 0, 
				coa_valor_decrescimo = coa_valor_original, 
				coa_valor_total = 0, 
				cot_id = 6, 
				coa_data_processamento = CURDATE() 
			WHERE 
				coa_id = $cobranca_id");

		if ($this->ehRegistrada($cobranca_id)) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$cobranca_id}");
		}

		$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = $cobranca_id");

		$this->registraComentario($cobranca_id, "<b>Ação:</b> Baixa Indevida da Cobrança Código: <b>$codigo</b>$comentarios", $_SESSION["usuario"]);

        if($this->ehSicredi($cobranca_id)) {
            $this->darBaixaDoBoleto($cobranca_id);
        }

	}

	function baixarNegociadaOutraVia($cobranca_id, $codigo, $comentarios = null) {

		$this->bancodedados->query("UPDATE tab_cobrancas SET cot_id = 7, coa_data_processamento = CURDATE() WHERE coa_id = $cobranca_id");

		if ($this->ehRegistrada($cobranca_id)) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$cobranca_id}");
		}

		$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = $cobranca_id");

		$this->registraComentario($cobranca_id, "<b>Ação:</b> Baixa Negociada da Cobrança Código: <b>$codigo</b>$comentarios", $_SESSION["usuario"]);

        if($this->ehSicredi($cobranca_id)) {
            $this->darBaixaDoBoleto($cobranca_id);
        }

	}

	####################
	### Editar Baixa ###
	####################

	function editar_baixa() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehEditavelBaixa()) {

			if (!$_POST["confirmar"]) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.editar_baixa.inc.php");

				$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
				$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[data_vencimento]', $this->dados_item["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace('[data_pagamento]', $this->dados_item["data_pagamento"], $this->conteudo);
				$this->conteudo = str_replace('[valor_original]', $this->formataDinheiro($this->dados_item["valor_original"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_juro]', $this->formataDinheiro($this->dados_item["valor_juro"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_desconto]', $this->formataDinheiro($this->dados_item["valor_desconto"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_acrescimo]', $this->formataDinheiro($this->dados_item["valor_acrescimo"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_decrescimo]', $this->formataDinheiro($this->dados_item["valor_decrescimo"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_bonificacao]', $this->formataDinheiro($this->dados_item["valor_bonificacao"]), $this->conteudo);
				$this->conteudo = str_replace('[valor_total]', $this->formataDinheiro($this->dados_item["valor_total"]), $this->conteudo);
				$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaStatusManual(), $this->dados_item["status_id"], true, "tipo", "100%", "atualizaCobrancaBaixa(this)"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {
				
				$dados_status = $this->buscaDadosStatus($_POST["tipo"]);

				$_POST["data_pagamento"] = $dados_status["recebido"] ? "'".substr($_POST["data_pagamento"], 6, 4).substr($_POST["data_pagamento"], 3, 2).substr($_POST["data_pagamento"], 0, 2)."'" : "NULL";
				$_POST["valor_juro"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_juro"]), 10);
				$_POST["valor_desconto"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_desconto"]), 10);
				$_POST["valor_acrescimo"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_acrescimo"]), 10);
				$_POST["valor_decrescimo"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_decrescimo"]), 10);
				$_POST["valor_total"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_total"]), 10);
				$_POST["comentario"] = addslashes(preg_replace("/[\r\n ]+/", " ", $_POST["comentario"]));

				$this->bancodedados->query("
					UPDATE 
						tab_cobrancas 
					SET 
						coa_data_pagamento = {$_POST["data_pagamento"]}, 
						coa_data_credito = {$_POST["data_pagamento"]}, 
						coa_valor_juro = {$_POST["valor_juro"]}, 
						coa_valor_desconto = {$_POST["valor_desconto"]}, 
						coa_valor_acrescimo = {$_POST["valor_acrescimo"]}, 
						coa_valor_decrescimo = {$_POST["valor_decrescimo"]}, 
						coa_valor_total = {$_POST["valor_total"]}, 
						cot_id = {$_POST["tipo"]} 
					WHERE 
						coa_id = {$_GET["i"]}");

				if (!empty($_POST["comentario"])) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Editar Baixa$comentario", $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	#####################
	### Remover Baixa ###
	#####################

	function remover_baixa() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehRemovivelBaixa()) {

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.remover_baixa.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$_POST["comentario"] = addslashes(preg_replace("/[\r\n\t ]+/", " ", $_POST["comentario"]));

				$this->bancodedados->query("
					UPDATE 
						tab_cobrancas 
					SET 
						coa_data_processamento = NULL, 
						coa_data_pagamento = NULL, 
						coa_valor_acrescimo = NULL, 
						coa_valor_desconto = NULL, 
						coa_valor_decrescimo = NULL, 
						coa_valor_total = NULL, 
						cot_id = 1 
					WHERE 
						coa_id = {$_GET["i"]}");

				if (!empty($_POST["comentario"])) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
				$this->registraComentario($_GET["i"], "<b>Ação:</b> Remover Baixa{$comentario}", $_SESSION["usuario"]);

				if ($this->dados_item["coa_pai"]) {
					
					$this->bancodedados->query("
						UPDATE 
							tab_cobrancas 
						SET 
							coa_data_processamento = NULL, 
							coa_data_pagamento = NULL, 
							coa_valor_acrescimo = NULL, 
							coa_valor_decrescimo = NULL, 
							coa_valor_total = NULL, 
							cot_id = 1 
						WHERE 
							coa_id = {$this->dados_item["coa_pai"]}");
					
					$this->registraComentario($this->dados_item["coa_pai"], "<b>Ação:</b> Remover Baixa$comentario", $_SESSION["usuario"]);

					$cobrancas_filho = $this->buscaCobrancasFilho($this->dados_item["coa_pai"]);
					
					foreach ($cobrancas_filho as $cobranca_filho_id) {
						if ($cobranca_filho_id != $_GET["i"]) {
							
							$this->bancodedados->query("
								UPDATE 
									tab_cobrancas 
								SET 
									coa_data_processamento = NULL, 
									coa_data_pagamento = NULL, 
									coa_data_credito = NULL, 
									coa_valor_acrescimo = NULL, 
									coa_valor_decrescimo = NULL, 
									coa_valor_total = NULL, 
									cot_id = 1 
								WHERE 
									coa_id = $cobranca_filho_id");
							
							$this->registraComentario($cobranca_filho_id, "<b>Ação:</b> Remover Baixa{$comentario}", $_SESSION["usuario"]);
							
						}
					}

				}

				$cobrancas_filho = $this->buscaCobrancasFilho($_GET["i"]);
				
				foreach ($cobrancas_filho as $cobranca_filho_id) {
					
					$this->bancodedados->query("
						UPDATE 
							tab_cobrancas 
						SET 
							coa_data_processamento = NULL, 
							coa_data_pagamento = NULL, 
							coa_valor_acrescimo = NULL, 
							coa_valor_decrescimo = NULL, 
							coa_valor_total = NULL, 
							cot_id = 1 
						WHERE 
							coa_id = $cobranca_filho_id");
					
					$this->registraComentario($cobranca_filho_id, "<b>Ação:</b> Remover Baixa{$comentario}", $_SESSION["usuario"]);
					
				}

				$this->atualizaComissaoPrimeiroPagamento($_GET["i"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	#########################
	### Registrar Contato ###
	#########################

	function registrar_contato() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehRegistravelContato()) {

			if (!$_POST) {

				$tipos = $this->buscaContatosTipos();
				for ($i = 0; $i < 10; $i++) unset($tipos[$i]);
				unset($tipos[17]);
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.registrar_contato.inc.php");

				$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
				$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[data_vencimento]', $this->dados_item["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace('[valor_original]', $this->formataDinheiro($this->dados_item["valor_original"]), $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->geraSelect($tipos, null, true, "tipo", "100%"), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$_POST["comentario"] = addslashes(trim($_POST["comentario"]));
				
				if ($_POST["contato_multiplo"]) {
					$cobrancas = $this->buscaCobrancasEmAberto($this->dados_item["cliente_id"]);
				} else {
					$cobrancas = array($_GET["i"]);
				}
				
				for ($i = 0; $i < sizeof($cobrancas); $i++) {
					
					$this->bancodedados->query("
						INSERT INTO 
							tab_recuperacao_contatos
						SET 
							rec_inicio = NOW(), 
							rec_termino = NOW(), 
							usu_id = '{$_SESSION["usuario"]}', 
							coa_id = '$cobrancas[$i]', 
							rct_id = '{$_POST["tipo"]}', 
							rec_comentario = '{$_POST["comentario"]}'");
					
					$id = $this->bancodedados->ultimoIdInserido();
					
					$this->bancodedados->query("UPDATE tab_cobrancas SET rec_id = $id WHERE coa_id = $cobrancas[$i]");
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $cobrancas[$i]);

				}
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	function buscaCobrancasEmAberto($cliente_id) {
		
		$cobrancas = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id
			FROM
				tab_cobrancas
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas.col_id = tab_cobrancas_lotes.col_id)
			WHERE
				tab_cobrancas.cli_id = $cliente_id AND
				tab_cobrancas.cot_id = 1 AND
				(tab_cobrancas_lotes.col_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6))
			ORDER BY
				id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cobrancas[] = $dados["id"];
		}

		return $cobrancas;

	}

	####################
	### Gerar Recibo ###
	####################

	function gerar_recibo() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehGeravelRecibo()) {

			$conteudo = $this->carregaArquivo("modelos/recibo_cobranca.html");
			$recibo_dados = $this->geraReciboDados();
			foreach ($recibo_dados as $chave => $valor) {
				$conteudo = str_replace("[$chave]", $valor, $conteudo);
			}
			$conteudo = preg_replace("/\\[[^\\]]*\\]/", "", $conteudo);
			print $conteudo;
			exit();

		} else {
			$this->fechar();
		}

	}

	function geraReciboDados() {

		$meses = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

		$recibo_dados = array();
		
		$recibo_dados["empresa_logo"] = "logo_ole_tv_retangular.png";
		$recibo_dados["empresa_nome"] = "OTT AND IPTV LTDA EPP";
		$recibo_dados["empresa_cnpj"] = "23.911.953/0001-07";
		$recibo_dados["empresa_fone"] = "(48) 4042-6262";
		
		$recibo_dados["cpf_cnpj"] = $this->dados_item["cliente_tipo"] == 1 ? "CPF" : "CNPJ";
		$recibo_dados["cliente_nome"] = $this->dados_item["cliente_nome"];
		$recibo_dados["cliente_cpf_cnpj"] = $this->dados_item["cliente_cpf_cnpj"];
		$recibo_dados["cliente_tipo"] = $this->dados_item["cliente_tipo"];
		$recibo_dados["valor_formatado"] = $this->formataDinheiro($this->dados_item["valor_total"]);
		$recibo_dados["valor_extenso"] = $this->escreveValorPorExtenso($this->dados_item["valor_total"] / 100, true);
		$recibo_dados["codigo"] = $this->dados_item["codigo"];
		$recibo_dados["data_vencimento"] = $this->dados_item["data_vencimento"];
		$recibo_dados["data_pagamento_dia"] = substr($this->dados_item["data_pagamento"], 0, 2);
		$recibo_dados["data_pagamento_mes"] = substr($this->dados_item["data_pagamento"], 3, 2);
		$recibo_dados["data_pagamento_mes_extenso"] = $meses[$recibo_dados["data_pagamento_mes"] - 1];
		$recibo_dados["data_pagamento_ano"] = substr($this->dados_item["data_pagamento"], 6, 4);

		if ($this->dados_item["competencia"]) {
			$recibo_dados["competencia"] = " (competência: {$this->dados_item["competencia"]}) ";
		} else {
			$recibo_dados["competencia"] = "";
		}

		return $recibo_dados;

	}

	function escreveValorPorExtenso($valor, $dinheiro=false, $maiusculas=false) {

		// verifica se tem virgula decimal
		if (strpos($valor, ", ") > 0) {
			// retira o ponto de milhar, se tiver
			$valor = str_replace(".", "", $valor);
			// troca a virgula decimal por ponto decimal
			$valor = str_replace(", ", ".", $valor);
		}

		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

		if (!$dinheiro) {
			$singular[0] = "";
			$plural[0] = "";
			$singular[1] = "";
			$plural[1] = "";
		}

		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
		$z = 0;
		$valor = number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);

		for($i=0;$i<count($inteiro);$i++) {
			for($ii=strlen($inteiro[$i]);$ii<3;$ii++) {
				$inteiro[$i] = "0".$inteiro[$i];
			}
		}

		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		$rt = "";

		for ($i=0;$i<count($inteiro);$i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
		}

		if (!$maiusculas) {
			return trim($rt ? $rt : "zero");
		} else {
			return trim(ucwords($rt) ? ucwords($rt) : "Zero");
		}

	}

	#########################
	### Gerar Recibo VoIP ###
	#########################

	function gerar_recibo_voip() {

		// Geta data
		$this->buscaDadosItem($_GET["i"]);

		$valor = $this->formataDinheiro($this->bancodedados->resultado($this->bancodedados->query("SELECT SUM(tab_cobrancas_detalhes.cod_valor_final) FROM tab_cobrancas_detalhes INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id) WHERE tab_ajustes_financeiros.afc_id = 29 AND tab_cobrancas_detalhes.coa_id = {$this->dados_item["id"]}")), "R$", true);
		$endereco = $this->buscaEndereco($this->dados_item["cliente_id"]);
		$endereco["endereco"] = array();
		$endereco["endereco"][] = $endereco["logradouro"];
		$endereco["endereco"][] = $endereco["numero"] ? $endereco["numero"] : "s/n";
		if ($endereco["complemento"]) $endereco["endereco"][] = $endereco["complemento"];
		$endereco["endereco"] = implode(", ", $endereco["endereco"]);

		// Page Size
		$page_width = 595;
		$page_height = 842;

		// Start new pdf document
		$pdf = pdf_new();

		pdf_begin_document($pdf, "", "");
		pdf_set_info($pdf, "Author", "Olé Telecom");
		pdf_set_info($pdf, "Title", "Recibo de Serviço de Valor Adicionado");
		pdf_begin_page_ext($pdf, $page_width, $page_height, "");

		// Box line width
		pdf_setlinewidth($pdf, 0.75);

		// Header box
		pdf_rect($pdf, 20, $page_height - 20 - 100, $page_width - 40, 100);

		// Logo vertical division
		pdf_moveto($pdf, 20 + 150, $page_height - 20);
		pdf_lineto($pdf, 20 + 150, $page_height - 20 - 100);

		// Header third column division
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20);
		pdf_lineto($pdf, $page_width - 20 - 150, $page_height - 20 - 100);

		// Header third column - 1st line
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20 - 25);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 25);

		// Header third column - 2nd line
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20 - 50);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 50);

		// Header third column - 3rd line
		pdf_moveto($pdf, $page_width - 20 - 150, $page_height - 20 - 75);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 75);

		// Customer box
		pdf_rect($pdf, 20, $page_height - 20 - 100 - 5 - 80, $page_width - 40, 80);

		// Customer box - 1st line
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 20);

		// Customer box - 2nd line
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 40);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 40);

		// Customer box - 3rd line
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 60);

		// Customer box - Veritcal - CPF/CNPJ
		pdf_moveto($pdf, $page_width - 20 - 200, $page_height - 20 - 100 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20 - 200, $page_height - 20 - 100 - 5 - 40);

		// Customer box - Veritcal - Inscrição Estadual
		pdf_moveto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 40);

		// Customer box - Veritcal - Cidade
		pdf_moveto($pdf, $page_width - 20 - 300, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20 - 300, $page_height - 20 - 100 - 5 - 80);

		// Customer box - Veritcal - CEP
		pdf_moveto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 80);

		// Customer box - Veritcal - UF
		pdf_moveto($pdf, $page_width - 20 - 25, $page_height - 20 - 100 - 5 - 60);
		pdf_lineto($pdf, $page_width - 20 - 25, $page_height - 20 - 100 - 5 - 80);

		// Service box
		pdf_rect($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 300, $page_width - 40, 300);

		// Service box - Title
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 80 - 5 - 20);

		// Service box - Header
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 40);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 80 - 5 - 40);

		// Service box - Footer
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 280);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 80 - 5 - 280);

		// Service box - Column Código
		pdf_moveto($pdf, 20 + 75, $page_height - 20 - 100 - 5 - 80 - 5 - 20);
		pdf_lineto($pdf, 20 + 75, $page_height - 20 - 100 - 5 - 80 - 5 - 280);

		// Service box - Column Valor
		pdf_moveto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 80 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20 - 100, $page_height - 20 - 100 - 5 - 80 - 5 - 300);

		// Information box
		pdf_rect($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 300 - 5 - 80, $page_width - 40, 80);

		// Information box - Title
		pdf_moveto($pdf, 20, $page_height - 20 - 100 - 5 - 80 - 5 - 300 - 5 - 20);
		pdf_lineto($pdf, $page_width - 20, $page_height - 20 - 100 - 5 - 80 - 5 - 300 - 5 - 20);

		// Draw box
		pdf_stroke($pdf);

		// Logo
		$logo = pdf_load_image($pdf, "png", DIR_RAIZ."imagens".DIRECTORY_SEPARATOR."logo_curvas_pequeno.png", "");
		pdf_fit_image($pdf, $logo, 20 + 5, $page_height - 20 - 87 - 4, "boxsize {140 87} fitmethod meet");
		pdf_close_image($pdf, $logo);

		// Font
		pdf_set_parameter($pdf, "FontOutline", "Arial=".DIR_RAIZ."fonts".DIRECTORY_SEPARATOR."arial.ttf");
		pdf_set_parameter($pdf, "FontOutline", "ArialB=".DIR_RAIZ."fonts".DIRECTORY_SEPARATOR."arialbd.ttf");
		$arial = pdf_findfont($pdf, "Arial", "host", 0);
		$arialb = pdf_findfont($pdf, "ArialB", "host", 0);

		// Company
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_boxed($pdf, "EMPRESA CATARINENSE DE TECNOLOGIA\nEM TELECOMUNICAÇÕES LTDA.",  20 + 10 + 150, $page_height - 20 - 10 - 25, $page_width - 40 - 20 - 300, 23, "center", "");

		// Address
		pdf_setfont($pdf, $arial, 10);
		pdf_set_value($pdf, "leading", 13);
		pdf_show_boxed($pdf, "CNPJ: 08.519.692/0001-90\nAv. Acioni Souza Filho, 925, Praia Comprida\nSão José - Santa Catarina - 88103-790\nTelefone: (48) 3028-0000",  20 + 10 + 150, $page_height - 20 - 10 - 80, $page_width - 40 - 20 - 300, 55, "center", "");

		// Document title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_boxed($pdf, "RECIBO DE SERVIÇO\nDE VALOR ADICIONADO",  $page_width - 20 - 150, $page_height - 20 - 23 - 1, 150, 23, "center", "");

		// Header - Número
		pdf_setfont($pdf, $arialb, 9);
		pdf_show_xy($pdf, "Número:", $page_width - 20 - 150 + 2, $page_height - 20 - 25 - 10);
		pdf_setfont($pdf, $arial, 9);
		pdf_show_xy($pdf, $this->dados_item["codigo"], $page_width - 20 - 150 + 2, $page_height - 20 - 25 - 10 - 12);

		// Header - Emissão
		pdf_setfont($pdf, $arialb, 9);
		pdf_show_xy($pdf, "Emissão:", $page_width - 20 - 150 + 2, $page_height - 20 - 50 - 10);
		pdf_setfont($pdf, $arial, 9);
		pdf_show_xy($pdf, $this->dados_item["data_geracao"], $page_width - 20 - 150 + 2, $page_height - 20 - 50 - 10 - 12);

		// Header - Vencimento
		pdf_setfont($pdf, $arialb, 9);
		pdf_show_xy($pdf, "Vencimento:", $page_width - 20 - 150 + 2, $page_height - 20 - 75 - 10);
		pdf_setfont($pdf, $arial, 9);
		pdf_show_xy($pdf, $this->dados_item["data_vencimento"], $page_width - 20 - 150 + 2, $page_height - 20 - 75 - 10 - 12);

		// Customer title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_xy($pdf, "Dados do Cliente", 20 + 5, $page_height - 20 - 100 - 5 - 14);

		// Customer data - Nome / Razão Social
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Nome / Razão Social:", 20 + 3, $page_height - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $this->dados_item["cliente_nome"], 20 + 3, $page_height - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - CPF/CNPJ
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "CPF/CNPJ:", $page_width - 20  - 100 - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $this->dados_item["cliente_cpf_cnpj"], $page_width - 20  - 100 - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Inscrição Estadual
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Inscrição Estadual:", $page_width - 20  - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $this->dados_item["cliente_inscricao_estadual"], $page_width - 20 - 100 + 3, $page_height - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Endereço
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Endereço:", 20 + 3, $page_height - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["endereco"], 20 + 3, $page_height - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Bairro
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Bairro:", 20 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["bairro"], 20 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - Cidade
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "Cidade:", $page_width - 20 - 300 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["cidade"], $page_width - 20 - 300 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - CEP
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "CEP:", $page_width - 20 - 100 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["cep"], $page_width - 20 - 100 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Customer data - UF
		pdf_setfont($pdf, $arialb, 8);
		pdf_show_xy($pdf, "UF:", $page_width - 20 - 25 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8.5);
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, $endereco["estado"], $page_width - 20 - 25 + 3, $page_height - 20 - 20 - 20 - 100 - 5 - 20 - 8 - 8.5);

		// Service title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_xy($pdf, "Dados do Serviço", 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 14);

		// Service header - Código
		pdf_show_xy($pdf, "Código", 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 14);

		// Service header - Descrição do Serviço
		pdf_show_xy($pdf, "Descrição do Serviço", 20 + 75 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 14);

		// Service header - Valor
		pdf_show_xy($pdf, "Valor", $page_width - 20 - 100 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 14);

		// Service - Total Recebido
		pdf_show_xy($pdf, "Total Recebido", $page_width - 20 - 100 - pdf_stringwidth($pdf, "Total Recebido", $arialb, 10) - 5, $page_height - 20 - 100 - 5 - 80 - 5 - 280 - 14);

		// Service description
		pdf_setfont($pdf, $arial, 8);
		pdf_show_xy($pdf, "SVA-210", 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 20 - 10);
		pdf_show_xy($pdf, "SERVIÇO DE VOZ SOBRE IP (VOIP)", 20 + 75 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 20 - 10);
		pdf_show_xy($pdf, $valor, $page_width - 20 - pdf_stringwidth($pdf, $valor, $arial, 8) - 5, $page_height - 20 - 100 - 5 - 80 - 5 - 20 - 20 - 10);
		pdf_setfont($pdf, $arial, 10);
		pdf_show_xy($pdf, $valor, $page_width - 20 - pdf_stringwidth($pdf, $valor, $arial, 10) - 5, $page_height - 20 - 100 - 5 - 80 - 5 - 280 - 14);

		// Information box - Title
		pdf_setfont($pdf, $arialb, 10);
		pdf_show_xy($pdf, "Informações Complementares", 20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 300 - 5 - 14);

		// Information box - Content
		pdf_setfont($pdf, $arial, 8);
		pdf_show_boxed($pdf, "Este só terá validade mediante o comprovante de pagamento do boleto bancário correspondente ou de depósito bancário na conta do emitente.",  20 + 5, $page_height - 20 - 100 - 5 - 80 - 5 - 300 - 5 - 20 - 5 - 70, $page_width - 20 - 5, 70, "left", "");

		// End Page & Document
		pdf_end_page($pdf);
		pdf_end_document($pdf, "");

		// Get Content
		$content = pdf_get_buffer($pdf);

		// HTTP Header
		header("Content-Type: application/pdf");
		header("Content-Length: ".strlen($content));
		header("Content-Disposition: attachment; filename=recibo-{$this->dados_item["codigo"]}.pdf");

		// Print content
		print $content;

	}

	##############
	### Buscar ###
	##############

	function buscar() {

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>';
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
		
		// Condominios
		$condominios = $this->buscaCondominios();
		$select_condominio = array();
		foreach ($condominios as $id => $nome) {
			$select_condominio[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "condominio", "descricao" => "Condomínio", "valor" => $select_condominio, "where" => "(tab_clientes.con_id = [valor])");

		// Código
		$itens[] = array ("tipo" => "input", "nome" => "codigo", "descricao" => "Código", "valor" => "", "where" => "(tab_cobrancas.coa_codigo LIKE '%[valor]%')");

		// Nosso número
		//$itens[] = array ("tipo" => "input", "nome" => " nosso_numero", "descricao" => "Nosso Número", "valor" => "", "where" => "(tab_cobrancas.coa_nosso_numero = LPAD([valor], 7, '0'))");
		$itens[] = array ("tipo" => "input", "nome" => " nosso_numero", "descricao" => "Nosso Número", "valor" => "", "where" => "(tab_cobrancas.coa_nosso_numero IN ('".preg_replace("/[^0-9]/", "", $valores[" nosso_numero"])."', LPAD([valor], 7, '0')))");
		
		// Lote
		$lotes = $this->buscaLotes();
		$select_lotes = array();
		foreach ($lotes as $id => $nome) {
			$select_lotes[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "lote", "descricao" => "Lote", "valor" => $select_lotes, "where" => "(tab_cobrancas.col_id = '[valor]')");
		
		// Formato
		$formatos = $this->buscaFormatos();
		$select_formatos = array();
		foreach ($formatos as $id => $nome) {
			$select_formatos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "formato", "descricao" => "Formato", "valor" => $select_formatos, "where" => "(tab_cobrancas.cof_id = [valor])");
		
		// Geração
		$itens[] = array ("tipo" => "input", "nome" => "data_geracao", "descricao" => "Data Geração", "valor" => "", "where" => "(tab_cobrancas.coa_data_geracao = '".(substr($valores["data_geracao"], 6, 4)."-".substr($valores["data_geracao"], 3, 2)."-".substr($valores["data_geracao"], 0, 2))."')");
		
		// Competência
		$itens[] = array ("tipo" => "input", "nome" => "competencia", "descricao" => "Competência", "valor" => "", "where" => $valores["competencia"] == "avulsa" ? "(tab_cobrancas_lotes.col_id IS NULL)" : "(tab_cobrancas_detalhes.cod_competencia_inicio BETWEEN '".(substr($valores["competencia"], 3, 4)."-".substr($valores["competencia"], 0, 2)."-"."01")."' AND '".date("Y-m-t", strtotime(substr($valores["competencia"], 3, 4)."-".substr($valores["competencia"], 0, 2)."-"."01"))."')");
		
		// Vencimento
		$itens[] = array ("tipo" => "input", "nome" => "data_vencimento", "descricao" => "Data Vencimento", "valor" => "", "where" => "(tab_cobrancas.coa_data_vencimento = '".(substr($valores["data_vencimento"], 6, 4)."-".substr($valores["data_vencimento"], 3, 2)."-".substr($valores["data_vencimento"], 0, 2))."')");
		
		// Pagamento
		$itens[] = array ("tipo" => "input", "nome" => "data_pagamento", "descricao" => "Data Pagamento", "valor" => "", "where" => "(tab_cobrancas.coa_data_pagamento = '".(substr($valores["data_pagamento"], 6, 4)."-".substr($valores["data_pagamento"], 3, 2)."-".substr($valores["data_pagamento"], 0, 2))."')");
		
		// Processamento
		$itens[] = array ("tipo" => "input", "nome" => "data_processamento", "descricao" => "Data Processamento", "valor" => "", "where" => "(tab_cobrancas.coa_data_processamento = '".(substr($valores["data_processamento"], 6, 4)."-".substr($valores["data_processamento"], 3, 2)."-".substr($valores["data_processamento"], 0, 2))."')");
		
		// Tipos
		$select_tipos = array();
		$select_tipos[] = array ("id" => "1", "nome" => "Primeira Via", "where" => "(tab_cobrancas.coa_pai IS NULL AND tab_cobrancas_lotes.col_id IS NOT NULL)");
		$select_tipos[] = array ("id" => "2", "nome" => "Segunda Via", "where" => "(tab_cobrancas.coa_pai IS NOT NULL)");
		$select_tipos[] = array ("id" => "3", "nome" => "Cobrança Avulsa", "where" => "(tab_cobrancas_lotes.col_id IS NULL)");
		$itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos);

		// Status
		$status = $this->buscaStatus();
		$select_status = array();
		foreach ($status as $id => $nome) {
			$select_status[] = array ("id" => $id, "nome" => $nome);
		}
		$select_status[] = array ("id" => "-1", "nome" => "Baixa Automática (Paga)", "where" => "(tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_manual = 0 AND cot_recebido = 1))");
		$select_status[] = array ("id" => "-2", "nome" => "Baixa Manual", "where" => "(tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_manual = 1))");
		$select_status[] = array ("id" => "-3", "nome" => "Baixa Manual (Paga)", "where" => "(tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_manual = 1 AND cot_recebido = 1))");
		$select_status[] = array ("id" => "-4", "nome" => "Aguardando Envio", "where" => "(tab_cobrancas.cot_id = 1 AND tab_cobrancas_formatos.cft_id IN (3) AND tab_cobrancas.col_id IS NULL AND tab_cobrancas.coa_enviada = 0)");
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_cobrancas.cot_id = [valor])");

		// Origem
		$origens = $this->buscaClientesOrigens();
		$itens[] = array ("tipo" => "select", "nome" => "origem", "descricao" => "Origem", "valor" => $origens, "where" => "(tab_clientes.clo_id = [valor])");

		// Inicializa Classe Filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

		return $filtro;

	}

	function geraPaginacao($filtro, $total, $pagina) {

		$paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		return $paginacao;

	}

	function buscaItens($filtro, $pagina , $linhaDigitavel = false) {

		$itens = array();
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_nosso_numero AS nosso_numero,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.cop_id AS tipo_id,
				tab_cobrancas.cot_id AS status_id,
				IF(tab_cobrancas_lotes.cla_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6), tab_cobrancas_status.cot_nome, CONCAT(tab_cobrancas_status.cot_nome, ' (Lote Inativo)')) AS status_nome,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m/%Y') AS vencimento,
				tab_cobrancas_lotes.col_id AS lote_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cls_id AS cliente_status,
                tab_cobrancas.coa_aguardando_registro AS coa_aguardando_registro, 
                tab_cobrancas_formatos.cof_parametros AS formatos_parametros,
				IF(tab_cobrancas_formatos.cof_remessa IS NOT NULL AND (SELECT COUNT(*) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cri_nosso_numero = tab_cobrancas.coa_nosso_numero AND tab_cobrancas_remessas_itens.cri_status IN ('REGISTRADA', 'REJEITADA')) = 0, 1, 0) AS aguardando_registro
			FROM
				tab_clientes
				INNER JOIN tab_cobrancas ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
				INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
				LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id AND tab_cobrancas_detalhes.ass_id IS NOT NULL)
			WHERE
				$filtro->parametros_sql".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			GROUP BY
				id
			ORDER BY
				tab_clientes.cli_nome,
				tab_cobrancas.coa_data_vencimento
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->temPermissao("ver_df", "clientes")) {
				$dados["cliente_nome"] = "[<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>]<br>{$dados["cliente_nome"]}";
			} else {
				$dados["cliente_nome"] = "[{$dados["cliente_id"]}]<br>{$dados["cliente_nome"]}";
			}

			if ($this->temPermissao("ver_dg")) {
				$dados["link"] = "javascript:verCobranca({$dados["id"]})";
			}

			if (!$dados["lote_id"]) $dados["formato_nome"] .= '<br>Cobrança Avulsa';
			if ($dados["coa_pai"]) $dados["formato_nome"] .= ' (Segunda Via)';

            $dados["formatos_parametros"] = unserialize($dados["formatos_parametros"]);
            if($dados["formatos_parametros"]["parametros"] == "sicredi") {
                if ($dados["status_id"] == 1 && $dados["coa_aguardando_registro"]) $dados["status_nome"] .= ' (Aguardando registro)';
            } else {
				if ($dados["aguardando_registro"]) $dados["status_nome"] .= ' (Aguardando registro)';
            }

			$dados["competencia"] = $this->buscaCobrancaCompetencia($dados["id"], $dados["tipo_id"]);
			$dados["valor_original"] = $this->formataDinheiro($dados["valor_original"], 'R$', true);
			$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"], 'R$', true);
			if ($dados["data_pagamento"]) $dados["data_vencimento"] .= "<br>({$dados["data_pagamento"]})";
			if ($dados["data_pagamento"]) $dados["valor_original"] .= "<br>({$dados["valor_total"]})";
			$dados["codigo"] = "<div NOWRAP>{$dados["codigo"]}</div>";
			$dados["data_vencimento"] = "<div NOWRAP>{$dados["data_vencimento"]}</div>";
			$dados["valor_original"] = "<div NOWRAP>{$dados["valor_original"]}</div>";
			$dados["formato_nome"] = str_replace(" (", "<br>(", $dados["formato_nome"]);
			$dados["formato_nome"] = str_replace(" [", "<br>[", $dados["formato_nome"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			$dados["status_nome"] = str_replace(" (", "<br>(", $dados["status_nome"]);

            if($linhaDigitavel) {
                
                $dados["parametros"] = unserialize($dados["parametros"]);
                $dados['linha_digitavel'] = $dados["parametros"]['boleto']['linha_digitavel'];
                $linha1 = preg_replace("/[^0-9]/", "",$_GET['f']['linha_digitavel']);
                $linha2 = preg_replace("/[^0-9]/", "", $dados['linha_digitavel']);
                
                $nossoNum1 = (int) preg_replace("/[^0-9]/", "",$_GET['f']['linha_digitavel']);
                $nossoNum2 = (int) preg_replace("/[^0-9]/", "", $dados['nosso_numero']);
                
                if(($linha1 == $linha2) || ($nossoNum1 == $nossoNum2) ) {
                    $dados['linhaDigitavelValidaText'] = "Linha Digitavel Válida";
                    $dados['linhaDigitavelValida'] = true;
                    $dados['linha_digitavel'] = "<b style='color: #000'><i>" . $dados['linha_digitavel'] . "</i></b>";
                    $dados['nosso_numero'] = "<b style='color: #000'><i>" . $dados['nosso_numero'] . "</i></b>";
                } else {
                    $dados['linhaDigitavelValidaText'] = "Linha Digitavel Inválida";
                    $dados['linhaDigitavelValida'] = false;
                    $dados["cor_linha"] = "#ffb7b2";
                }
            }
                    
			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagem($itens) {

		$cabecalho = array ("contador" => "#", "cliente_nome" => "Cliente", "codigo" => "Código", "formato_nome" => "Formato", "competencia" => "Compet.", "data_vencimento" => "Data (V/P)", "valor_original" => "Valor (O/T)", "status_nome" => "Status");

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 0, 90, 120, 60, 60, 80, 70), null, array("right"));

	}

	function buscaDetalhamento($cobranca_id) {
		
		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				cod_descricao AS descricao,
				cod_valor_original AS valor_original,
				cod_valor_final AS valor_final,
				DATE_FORMAT(cod_competencia_inicio, '%Y-%m-%d') AS competencia_inicio,
				DATE_FORMAT(cod_competencia_termino, '%Y-%m-%d') AS competencia_termino,
				ass_id AS ass_id,
				dec_id AS dec_id,
				ajf_id AS ajf_id,
				cod_pai AS cod_pai
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id
			ORDER BY
				tab_cobrancas_detalhes.cod_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}

		return $lista;

	}

	function buscaDetalhesCobranca($cobranca) {

		if ($this->ehCobrancaCondominial($cobranca["id"])) {
			return $this->buscaDetalhesCobrancaCondominial($cobranca);
		} else {
			return $this->buscaDetalhesCobrancaNormal($cobranca);
		}

	}

	function ehCobrancaCondominial($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(DISTINCT tab_assinaturas.cli_id)
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id");

		return $this->bancodedados->resultado($query) > 1;

	}

	function buscaDetalhesCobrancaCondominial($cobranca, $resumido = false) {
		
		$lista = array();
		
		if ($resumido) {
			$meses = array(1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
		} else {
			$meses = array(1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril", 5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto", 9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro");
		}

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.cli_id AS cobranca_cliente_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_condominios_blocos.cob_id AS bloco_id,
				IF(tab_condominios_blocos.cob_nome = 'Único', '', tab_condominios_blocos.cob_nome) AS bloco_nome,
				tab_cobrancas_detalhes.cod_id AS id,
				tab_cobrancas_detalhes.cod_descricao AS descricao,
				tab_cobrancas_detalhes.cod_valor_original AS valor_original,
				tab_cobrancas_detalhes.cod_valor_final AS valor_final,
				DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%m') AS competencia_mes,
				DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%Y') AS competencia_ano,
				DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%d/%m/%Y') AS competencia_inicio,
				DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_termino, '%d/%m/%Y') AS competencia_termino,
				tab_cobrancas_detalhes.cod_competencia_termino - tab_cobrancas_detalhes.cod_competencia_inicio + 1 AS competencia_dias,
				tab_cobrancas_detalhes.ass_id AS ass_id,
				tab_cobrancas_detalhes.dec_id AS dec_id,
				tab_cobrancas_detalhes.ajf_id AS ajf_id,
				tab_cobrancas_detalhes.cod_pai AS cod_pai
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				LEFT OUTER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
				LEFT OUTER JOIN tab_descontos_concessoes ON (tab_cobrancas_detalhes.dec_id = tab_descontos_concessoes.dec_id)
				LEFT OUTER JOIN tab_assinaturas tab_assinaturas_descontos ON (tab_descontos_concessoes.ass_id = tab_assinaturas_descontos.ass_id)
				LEFT OUTER JOIN tab_clientes ON (tab_clientes.cli_id = IF(tab_assinaturas.cli_id IS NOT NULL, tab_assinaturas.cli_id, IF(tab_ajustes_financeiros.cli_id IS NOT NULL, tab_ajustes_financeiros.cli_id, tab_assinaturas_descontos.cli_id)))
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
			WHERE
				tab_cobrancas.coa_id = {$cobranca["id"]}
			ORDER BY
				IF(tab_clientes.cli_id IS NULL || tab_cobrancas.cli_id = tab_clientes.cli_id, 'Z', DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%m')),
				tab_condominios_blocos.cob_nome,
				SUBSTRING(tab_clientes.cli_complemento, 2) + 0");

		while (($detalhes = $this->bancodedados->resultadoArray($query)) != false) {

			if ($detalhes["cliente_id"] && ($detalhes["cliente_id"] != $detalhes["cobranca_cliente_id"])) {

				$detalhes["descricao"] = $detalhes["cliente_nome"];
				if ($resumido) {
					$competencia = $detalhes["bloco_nome"] ? "{$detalhes["bloco_nome"]} (".$meses[intval($detalhes["competencia_mes"])]." / {$detalhes["competencia_ano"]})" : $meses[intval($detalhes["competencia_mes"])]." / ".$detalhes["competencia_ano"];
				} else {
					$competencia = "Competência &raquo; ".$meses[intval($detalhes["competencia_mes"])]." / ".$detalhes["competencia_ano"].($detalhes["bloco_nome"] ? " &raquo; ".$detalhes["bloco_nome"] : "");
				}

				$detalhes["valor_original"] = "<div align=\"right\">".$this->formataDinheiro($detalhes["valor_original"] + intval(preg_replace("/[^0-9\\-]/", "", strip_tags($lista[$competencia]["cliente_{$detalhes["cliente_id"]}"]["valor_original"])), 10), 'R$', true)."</div>";
				$detalhes["valor_final"] = "<div align=\"right\">".$this->formataDinheiro($detalhes["valor_final"] + intval(preg_replace("/[^0-9\\-]/", "", strip_tags($lista[$competencia]["cliente_{$detalhes["cliente_id"]}"]["valor_final"])), 10), 'R$', true)."</div>";

				$lista[$competencia]["cliente_{$detalhes["cliente_id"]}"] = $detalhes;

			} else {

				if ($detalhes["ass_id"]) {
					$detalhes["link"] = "javascript:verAssinatura({$detalhes["ass_id"]})";
				} elseif ($detalhes["dec_id"]) {
					$detalhes["link"] = "javascript:verDescontoConcessao({$detalhes["dec_id"]})";
				} elseif ($detalhes["ajf_id"]) {
					$detalhes["link"] = "javascript:verAjusteFinanceiro({$detalhes["ajf_id"]})";
				}

				if ($detalhes["ajf_id"]) {
					$competencia_ajuste = (substr($detalhes["competencia_inicio"], 3) == substr($detalhes["competencia_termino"], 3)) ? substr($detalhes["competencia_inicio"], 3) : substr($detalhes["competencia_inicio"], 3)." - ".substr($detalhes["competencia_termino"], 3);
					$hint = "Competência: $competencia_ajuste<br>";
				} else {
					$hint = "Competência: {$detalhes["competencia_inicio"]} - {$detalhes["competencia_termino"]} ({$detalhes["competencia_dias"]} dia".($detalhes["competencia_dias"] == 1 ? "" : "s").")<br>";
				}
				$hint .= "Valor Original: ".strip_tags($this->formataDinheiro($detalhes["valor_original"], 'R$', true))."<br>";
				$hint .= "Valor Final: ".strip_tags($this->formataDinheiro($detalhes["valor_final"], 'R$', true));

				if ($detalhes["ass_id"] || $detalhes["dec_id"] || $detalhes["ajf_id"]) {
					$detalhes["descricao"] = $resumido ? trim(preg_replace("/\\([^\\)]*\\)/", "", $detalhes["descricao"])) : ($detalhes["cod_pai"] ? "<div style=\"cursor:pointer;width:270px;height:14px;overflow:hidden;text-overflow:ellipsis\" onmouseover=\"hintShow('$hint')\" onmouseout=\"hintHide()\">&nbsp;&raquo;&nbsp;".str_replace(" ", "&nbsp;", $detalhes["descricao"])."</div>" : "<div style=\"cursor:pointer;width:270px;height:14px;overflow:hidden;text-overflow:ellipsis\" onmouseover=\"hintShow('$hint')\" onmouseout=\"hintHide()\"><b>".str_replace(" ", "&nbsp;", $detalhes["descricao"])."</b></div>");
				} else {
					$detalhes["descricao"] = "<b>{$detalhes["descricao"]}</b>";
				}

				if ($resumido) {
					$competencia = "Geral";
				} else {
					$competencia = $detalhes["competencia_mes"] && strpos($detalhes["descricao"], "Tarifa&nbsp;Bancária") === false ? "Competência &raquo; ".$meses[intval($detalhes["competencia_mes"])]." / ".$detalhes["competencia_ano"] : "Geral";
				}

				$detalhes["valor_original"] = "<div align=\"right\">".$this->formataDinheiro($detalhes["valor_original"], 'R$', true)."</div>";
				$detalhes["valor_final"] = "<div align=\"right\">".$this->formataDinheiro($detalhes["valor_final"], 'R$', true)."</div>";

				$lista[$competencia][] = $detalhes;

			}

		}

		$lista["Geral"][] = array("descricao" => "<b>Valor Final</b>", "valor_final" => "<div align=\"right\"><b>".$this->formataDinheiro($cobranca["valor_original"], 'R$', true)."</b></div>");

		return $lista;

	}

	function buscaDetalhesCobrancaNormal($cobranca) {
		
		$meses = array(1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril", 5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto", 9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro");
		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				cod_id AS id,
				cod_descricao AS descricao,
				cod_valor_original AS valor_original,
				cod_valor_final AS valor_final,
				DATE_FORMAT(cod_competencia_inicio, '%m') AS competencia_mes,
				DATE_FORMAT(cod_competencia_inicio, '%Y') AS competencia_ano,
				DATE_FORMAT(cod_competencia_inicio, '%d/%m/%Y') AS competencia_inicio,
				DATE_FORMAT(cod_competencia_termino, '%d/%m/%Y') AS competencia_termino,
				cod_competencia_termino - cod_competencia_inicio + 1 AS competencia_dias,
				ass_id AS ass_id,
				dec_id AS dec_id,
				ajf_id AS ajf_id,
				cod_pai AS cod_pai
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id = {$cobranca["id"]}
			ORDER BY
				IF(tab_cobrancas_detalhes.cod_competencia_inicio IS NOT NULL, -1/DATE_FORMAT(cod_competencia_inicio, '%Y%m'), 1),
				IF(cod_pai IS NULL, cod_id, cod_pai),
				ass_id DESC,
				dec_id DESC");

		while (($detalhes = $this->bancodedados->resultadoArray($query)) != false) {

			if ($detalhes["ass_id"]) {
				$detalhes["link"] = "javascript:verAssinatura({$detalhes["ass_id"]})";
			} elseif ($detalhes["dec_id"]) {
				$detalhes["link"] = "javascript:verDescontoConcessao({$detalhes["dec_id"]})";
			} elseif ($detalhes["ajf_id"]) {
				$detalhes["link"] = "javascript:verAjusteFinanceiro({$detalhes["ajf_id"]})";
			}

			if ($detalhes["ajf_id"]) {
			$competencia_ajuste = (substr($detalhes["competencia_inicio"], 3) == substr($detalhes["competencia_termino"], 3)) ? substr($detalhes["competencia_inicio"], 3) : substr($detalhes["competencia_inicio"], 3)." - ".substr($detalhes["competencia_termino"], 3);
			$hint = "Competência: $competencia_ajuste<br>";
			} else {
			$hint = "Competência: {$detalhes["competencia_inicio"]} - {$detalhes["competencia_termino"]} ({$detalhes["competencia_dias"]} dia".($detalhes["competencia_dias"] == 1 ? "" : "s").")<br>";
			}
			$hint .= "Valor Original: ".strip_tags($this->formataDinheiro($detalhes["valor_original"], 'R$', true))."<br>";
			$hint .= "Valor Final: ".strip_tags($this->formataDinheiro($detalhes["valor_final"], 'R$', true));

			if ($detalhes["ass_id"] || $detalhes["dec_id"] || $detalhes["ajf_id"]) {
				$detalhes["descricao"] = ($detalhes["cod_pai"]) ? "<div style=\"cursor:pointer;width:340px;height:14px;overflow:hidden;text-overflow:ellipsis\" onmouseover=\"hintShow('$hint')\" onmouseout=\"hintHide()\">&nbsp;&raquo;&nbsp;".str_replace(" ", "&nbsp;", $detalhes["descricao"])."</div>" : "<div style=\"cursor:pointer;width:290px;height:14px;overflow:hidden;text-overflow:ellipsis\" onmouseover=\"hintShow('$hint')\" onmouseout=\"hintHide()\"><b>".str_replace(" ", "&nbsp;", $detalhes["descricao"])."</b></div>";
			} else {
				if ($detalhes["cod_pai"]) {
					$detalhes["descricao"] = "<div style=\"width:340px;height:14px;overflow:hidden;text-overflow:ellipsis\">&nbsp;&raquo;&nbsp;{$detalhes["descricao"]}</div>";
				} else {
					$detalhes["descricao"] = "<b>{$detalhes["descricao"]}</b>";
				}
			}

			$detalhes["valor_original"] = "<div align=\"right\">".$this->formataDinheiro($detalhes["valor_original"], 'R$', true)."</div>";
			$detalhes["valor_final"] = "<div align=\"right\">".$this->formataDinheiro($detalhes["valor_final"], 'R$', true)."</div>";
			$competencia = $detalhes["competencia_mes"] && strpos($detalhes["descricao"], "Tarifa&nbsp;Bancária") === false ? "Competência &raquo; ".$meses[intval($detalhes["competencia_mes"])]." / ".$detalhes["competencia_ano"] : "Geral";

			$lista[$competencia][] = $detalhes;

		}

		$lista["Geral"][] = array("descricao" => "<b>Valor Final</b>", "valor_final" => "<div align=\"right\"><b>".$this->formataDinheiro($cobranca["valor_original"], 'R$', true)."</b></div>");

		return $lista;

	}

	function buscaCobrancaDetalhes($cobranca_id) {
		
		$cobranca_detalhes = array();

		$query = $this->bancodedados->query("
			SELECT
				cod_id AS id,
				cod_descricao AS descricao,
				cod_valor_original AS valor_original,
				cod_valor_final AS valor_final,
				DATE_FORMAT(cod_competencia_inicio, '%m') AS competencia_mes,
				DATE_FORMAT(cod_competencia_inicio, '%Y') AS competencia_ano,
				DATE_FORMAT(cod_competencia_inicio, '%d/%m/%Y') AS competencia_inicio,
				DATE_FORMAT(cod_competencia_termino, '%d/%m/%Y') AS competencia_termino,
				cod_competencia_termino - cod_competencia_inicio + 1 AS competencia_dias,
				ass_id AS ass_id,
				dec_id AS dec_id,
				ajf_id AS ajf_id,
				cod_pai AS cod_pai
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id
			ORDER BY
				tab_cobrancas_detalhes.cod_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cobranca_detalhes[] = $dados;
		}

		return $cobranca_detalhes;

	}

	function geraDetalhamentoListagem($itens) {

		if ($itens) {

			$itens = $this->calculaSubTotal($itens);
			$detalhamento = "<table width=\"100%\" cellpading=\"0\" cellspacing=\"0\" border=\"0\">";

			foreach ($itens as $competencia => $itens_competencia) {
				if ($itens_competencia[key($itens_competencia)]["complemento"]) {
					$cabecalho = array ("descricao" => "$competencia", "complemento" => "Complemento", "valor_final" => "Valor");
					$tamanho = array(0, 70, 70);
				} else {
					$cabecalho = array ("descricao" => "$competencia", "valor_final" => "Valor");
					$tamanho = array(0, 70);
				}
				$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens_competencia, 0, false, $tamanho);
				$detalhamento .= "<tr><td>".$listagem->conteudo."</td></tr>";
			}

			$detalhamento .= "</table>";

			return $detalhamento;

		}

	}

	function calculaSubTotal($itens) {

		foreach ($itens as $competencia => $itens_competencia) {
			if ($competencia != "Geral") {
				$novo_item = array(
					"valor_final" => 0
				);
				foreach ($itens_competencia as $item) {
					$novo_item["valor_final"] += intval(preg_replace('/[^0-9\-]/', "", strip_tags($item["valor_final"])));
				}
				$novo_item["descricao"] = "<b>Sub-Total</b>";
				$novo_item["valor_final"] = $novo_item["valor_final"];
				$novo_item["valor_final"] = "<div align=\"right\"><b>".$this->formataDinheiro($novo_item["valor_final"], 'R$', true)."</b></div>";
				$itens[$competencia][] = $novo_item;
			}
		}

		return $itens;

	}

	function geraDetalhamentoListagemAntiga($itens) {

		for ($i = 0; $i < count($itens); $i++) {
			$itens[$i]["valor"] = $this->formataDinheiro($itens[$i]["valor"], 'R$', true);
		}

		$cabecalho = array ("descricao" => "Descrição", "valor" => "Valor");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);

		return $listagem->conteudo;

	}

	function geraDadosFaturaListagem($itens) {

		$cabecalho = array ("quantidade" => "Qtd", "descricao" => "Descrição", "natureza" => "Natureza", "Valor" => array("valor_unitario" => "Unitário", "valor_total" => "Total", "valor_icms" => "ICMS"));
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 0, 55, 65, 65, 55), null, array("right", "left", "center", "right", "right", "right"));

		return $listagem->conteudo;

	}

	function buscaDadosFaturasItens($fatura_id) {

		$lista = array();
		$total = 0;
		$total_icms = 0;

		$query = $this->bancodedados->query("
			SELECT
				tab_faturas_itens.fai_quantidade AS quantidade,
				tab_faturas_itens.fai_valor_unitario AS valor_unitario,
				tab_faturas_itens.fai_valor_total AS valor_total,
				tab_faturas_itens.fai_valor_icms AS valor_icms,
				tab_faturas_itens.fai_natureza AS natureza,
				tab_classes_fiscais_tipos.cft_descricao AS descricao
			FROM
				tab_faturas_itens
				INNER JOIN tab_classes_fiscais_tipos ON (tab_faturas_itens.cft_id = tab_classes_fiscais_tipos.cft_id)
			WHERE
				tab_faturas_itens.fat_id = $fatura_id
			ORDER BY
				tab_classes_fiscais_tipos.cft_descricao");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$total += $dados["valor_total"];
			$total_icms =+ $dados["valor_icms"];

			$dados["valor_unitario"] = $this->formataDinheiro($dados["valor_unitario"], "R$" , true);
			$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"], "R$" , true);
			$dados["valor_icms"] = $this->formataDinheiro($dados["valor_icms"]);

			$lista[] = $dados;

		}

		$lista[] = array("descricao" => "<b>Total</b>", "valor_total" => $this->formataDinheiro($total), "valor_icms" => $this->formataDinheiro($total_icms), "cor_linha" => "#C8D3DF");

		return $lista;

	}

	function buscaFaturasItens($fatura_id) {

		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_faturas_itens.fai_quantidade AS quantidade,
				tab_faturas_itens.fai_valor_unitario AS valor_unitario,
				tab_faturas_itens.fai_valor_total AS valor_total,
				tab_faturas_itens.fai_valor_icms AS valor_icms,
				tab_faturas_itens.fai_natureza AS natureza,
				tab_classes_fiscais_tipos.cft_descricao AS descricao,
				tab_classes_fiscais_tipos.cft_codigo AS codigo
			FROM
				tab_faturas_itens
				INNER JOIN tab_classes_fiscais_tipos ON (tab_faturas_itens.cft_id = tab_classes_fiscais_tipos.cft_id)
			WHERE
				tab_faturas_itens.fat_id = $fatura_id
			ORDER BY
				tab_classes_fiscais_tipos.cft_descricao");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}

		return $lista;

	}

	#########################
	### Finaliza Bloqueio ###
	#########################

	function finalizaBloqueio($cliente_id) {

		$servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, $this->permissoes, false);

		if (!$this->possuiCobrancasEmAtraso($cliente_id)) {
			$ajustes = $this->buscaAjustesInadimplencia($cliente_id);
			if ($ajustes) {
				foreach ($ajustes as $ajuste_id) {
					$servicos_ajustes->removeAjuste($ajuste_id);
					$this->registraComentarioAjuste($ajuste_id, "<b>Ação</b>: Finalizado");
				}
				$ajustes_ativos = $this->buscaAjustesAtivos($cliente_id);
				if ($ajustes_ativos) {
					foreach ($ajustes_ativos as $ajuste_id) {
						$servicos_ajustes->aplicarAjuste($ajuste_id);
					}
				}
			}
			$this->cancelaBloqueiosAguardando($cliente_id);
		}

	}

	function buscaAjustesAtivos($cliente_id) {
		
		$ajustes = array();

		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sas_id = 5");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}

		return $ajustes;

	}

	function cancelaBloqueiosAguardando($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sas_id IN (1, 2, 3) AND
				sac_id = 1");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("UPDATE tab_servicos_ajustes SET sas_id = 4 WHERE saj_id = {$dados["id"]}");
			$this->registraComentarioAjuste($dados["id"], "<b>Ação</b>: Cancelado");
		}

	}

	function registraComentarioAjuste($ajuste_id, $conteudo, $usuario_id = null) {

		if (($ajuste_id = preg_replace("/[^0-9]/", "", $ajuste_id)) != false) {

			$usuario_id = $usuario_id ? $usuario_id : "NULL";
			$conteudo = addslashes(trim($conteudo));

			$this->bancodedados->query("INSERT INTO tab_servicos_ajustes_comentarios (saj_id, sam_datahora, sam_conteudo, usu_id) VALUES ($ajuste_id, NOW(), '$conteudo', $usuario_id)");

		}

	}

	function registraComentarioAjusteFinanceiro($ajuste_id, $conteudo, $usuario_id = null) {

		if (($ajuste_id = preg_replace("/[^0-9]/", "", $ajuste_id)) != false) {

			$usuario_id = $usuario_id ? $usuario_id : "NULL";
			$conteudo = addslashes(trim($conteudo));
			
			$this->bancodedados->query("INSERT INTO tab_ajustes_financeiros_comentarios (ajf_id, afm_datahora, afm_conteudo, usu_id) VALUES ($ajuste_id, NOW(), '$conteudo', $usuario_id)");
			
		}

	}

	function possuiPontoBloqueavel($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_clientes_pontos.clp_id) AS pontos
			FROM
				tab_clientes_pontos
				INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_clientes_pontos.cps_id IN (1, 2) AND
				tab_clientes_pontos.sei_id IS NOT NULL");

		if (($pontos = $this->bancodedados->resultado($query)) != false) {
			return $pontos;
		}

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_clientes_autenticacoes.cau_id) AS autenticacoes
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_clientes_autenticacoes.cas_id IN (1, 2)");

		if (($autenticacoes = $this->bancodedados->resultado($query)) != false) {
			return $autenticacoes;
		}

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_clientes_emails.cle_id) AS emails
			FROM
				tab_clientes_emails
				INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_clientes_emails.ces_id = 1 AND
				tab_planos.pls_id = 20");

		if (($emails = $this->bancodedados->resultado($query)) != false) {
			return $emails;
		}

	}

	###################################
	### Solicita Finalização Ajuste ###
	###################################

	function solicitaFinalizacaoAjuste($ajuste_id) {

		$this->bancodedados->query("
			UPDATE
				tab_servicos_ajustes
			SET
				sas_id = 9
			WHERE
				saj_id = $ajuste_id");

	}

	########################
	### Funções de Busca ###
	########################

	function possuiCobrancasEmAtraso($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id)
			FROM
				tab_cobrancas
			WHERE
				coa_data_bloqueio <= CURDATE() AND
				cli_id = $cliente_id AND
				coa_pai IS NULL AND
				cot_id = 1");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaAjustesInadimplencia($cliente_id) {

		$ajustes = array();

		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sas_id IN (5, 9) AND
				sac_id = 1");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}

		return $ajustes;

	}

	################
	### Imprimir ###
	################

	function imprimir() {

		$boletos_pdf = controle::instanciaObjeto("cobrancas.boletos_pdf", $this->bancodedados);
		$boletos_pdf->gerarBoleto($_GET["i"]);
		$boletos_pdf->exportar();

	}

	function buscaConteudoCobranca($dados_cobranca, $endereco_cobranca = true) {

		$conteudo = $this->carregaArquivo("modelos/cobrancas_boletos_{$dados_cobranca["parametros"]["boleto"]["versao"]}.html");

		if (!$dados_cobranca["parametros"]["boleto"]["competencia"]) {
			$dados_cobranca["parametros"]["boleto"]["competencia"] = $this->buscaCompetenciaBoleto($dados_cobranca["id"], $dados_cobranca["tipo_id"]);
		}

		$dados_cobranca["parametros"]["boleto"]["instrucoes"] = str_replace("<br>", " ", $dados_cobranca["parametros"]["boleto"]["instrucoes"]);

		if ($dados_cobranca["parametros"]["itens"]) {
			$dados_cobranca["parametros"]["boleto"]["instrucoes"] = implode("<br>", $dados_cobranca["parametros"]["itens"])."<br><br>".$dados_cobranca["parametros"]["boleto"]["instrucoes"];
		}

		foreach ($dados_cobranca["parametros"]["boleto"] as $chave => $valor) {
			$conteudo = str_replace("[$chave]", $valor, $conteudo);
		}

		$dados_cobranca["parametros"]["titular"]["nome"] = $dados_cobranca["cliente_nome"];

		if ($endereco_cobranca) {
			$conteudo = str_replace("[titulo]", $this->buscaOrigemNome($dados_cobranca["origem_id"]), $conteudo);
			$dados_cobranca["parametros"]["endereco_cobranca"] = $this->buscaEnderecoCobranca($dados_cobranca["cliente_id"]);
		}

		$conteudo = str_replace("[endereco_correspondencia]", $this->geraEnderecoBoleto($dados_cobranca["parametros"]), $conteudo);

		return preg_replace("/\\[[^\\]]*\\]/", "", $conteudo);

	}

	function buscaCompetenciaBoleto($cobranca_id, $tipo_id) {

		$estorno_migracao = false;
		$competencias = array();

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia,
				DATE_FORMAT(cod_competencia_inicio, '%Y') AS competencia_ano,
				DATE_FORMAT(cod_competencia_inicio, '%m') AS competencia_mes,
				tab_ajustes_financeiros.afc_id AS ajuste_categoria_id
			FROM
				tab_cobrancas_detalhes
				LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
			WHERE
				coa_id = $cobranca_id AND
				cod_competencia_inicio IS NOT NULL
			ORDER BY
				cod_competencia_inicio");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($tipo_id == 3) {
				if ($dados["competencia_mes"] == 1) {
					return "Jan - Jun / {$dados["competencia_ano"]}";
				} else {
					return "Jul - Ago / {$dados["competencia_ano"]}";
				}
			} elseif ($tipo_id == 4) {
				return "Anual / {$dados["competencia_ano"]}";
			}

			if (!in_array($dados["competencia"], $competencias)) {
				$competencias[] = $dados["competencia"];
			}

			if ($dados["ajuste_categoria_id"] == 13) {
				$estorno_migracao = true;
			}

		}

		if (count($competencias) == 2 && $estorno_migracao) {
			return $this->formataData($competencias[1]);
		} else {
			if (count($competencias) == 1) {
				return $this->formataData($competencias[0], false);
			} else {
				return $this->formataData($competencias[0], false)." - ".$this->formataData($competencias[count($competencias) - 1], false);
			}
		}

	}

	function buscaEnderecoCobranca($cliente_id) {

		$cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
		return $cobrancas_geracao->buscaEnderecoCobranca($cliente_id);

	}

	function buscaEndereco($cliente_id) {

		$cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
		return $cobrancas_geracao->buscaEndereco($cliente_id);

	}

	function geraEnderecoBoleto($parametros) {
		
		$endereco = array();
		$apartamento = array();

		$parametros["titular"]["nome"] = strtoupper($parametros["titular"]["nome"]);
		if ($parametros["endereco_cobranca"]["logradouro"]) $endereco[] = $parametros["endereco_cobranca"]["logradouro"];
		if ($parametros["endereco_cobranca"]["numero"]) $endereco[] = $parametros["endereco_cobranca"]["numero"];
		if ($parametros["endereco_cobranca"]["condominio"]) $endereco[] = $parametros["endereco_cobranca"]["condominio"];
		if ($parametros["endereco_cobranca"]["complemento"]) $apartamento[] = $parametros["endereco_cobranca"]["complemento"];
		if ($parametros["endereco_cobranca"]["bloco"] && $parametros["endereco_cobranca"]["bloco"] != "Único") $apartamento[] = $parametros["endereco_cobranca"]["bloco"];
		if ($apartamento) $endereco[] = implode(" / ", $apartamento);
		$endereco = implode(", ", $endereco);

		$conteudo = "
			<table class=\"endereco_cobranca\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td height=\"290\" align=\"center\" valign=\"bottom\">
			<table width=\"400\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
			<tr>
			<td class=\"endereco\">
			<b>{$parametros["titular"]["nome"]}</b><br>
			$endereco<br>
			{$parametros["endereco_cobranca"]["bairro"]} - {$parametros["endereco_cobranca"]["cidade"]} - {$parametros["endereco_cobranca"]["estado"]} - {$parametros["endereco_cobranca"]["cep"]}
			</td>
			</tr>
			</table>
			</td>
			</tr>
			</table>";

		return $conteudo;

	}

	##############
	### Enviar ###
	##############

	function enviar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);

			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.enviar.inc.php");

				$this->conteudo = str_replace("[emails]", implode("; ", $dados_cliente["emails"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				sort($emails = array_unique(preg_split("/[ ,;]+/", strtolower($_POST["emails"]))));

				if ($_POST["adicionar_emails"]) {
					$this->cadastrarEmailsCliente($this->dados_item["cliente_id"], $emails);
				}

				if (count($emails) > 0 && $this->enviaBoleto($emails, $_POST["comentario"], $_SESSION["usuario"])) {

					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

				} else {

					print "<script type=\"text/javascript\">alert(\"Erro ao enviar e-mail!\nFavor contactar o Administrador de Sistema.\"); window.location = \"./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}\";</script>";
					die();

				}

			}

		} else {
			$this->fechar();
		}

	}

	function enviaBoleto($emails = null, $comentario = null, $usuario = null) {

		if ($emails === null) {
			$emails = $this->buscaContatosEmails($this->dados_item["cliente_id"]);
		}

		if (count($emails) > 0) {

			require_once "_global/PHPMailer/PHPMailerAutoload.php";

			$mail = new PHPMailer();
			$mail->isSMTP();
			$dadosEmail = $this->loginEmail();
			$mail->Host =  $dadosEmail['host'];
			$mail->SMTPAuth = true;
			$mail->Username = $dadosEmail['usuario'];
			$mail->Password = $dadosEmail['senha'];
			$mail->SMTPSecure = $dadosEmail['seguranca'];
			$mail->Port = $dadosEmail['porta'];
			$mail->From = $dadosEmail['usuario'];
			$mail->FromName = $dadosEmail['de'];

			$mail->SMTPOptions = array (
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);

			$mail->Subject = "Boleto Online";
			$mail->isHTML(true);

			for ($i = 0; $i < count($emails); $i++) {
				$mail->addAddress($emails[$i], $this->dados_item["cliente_nome"]);
			}

			if ($comentario) {
				$comentario = trim($comentario);
			}

			$mail->Body = $this->getConteudoEmailBoleto($this->dados_item, $comentario);

			$boletos_pdf = controle::instanciaObjeto("cobrancas.boletos_pdf", $this->bancodedados);
			$boletos_pdf->gerarBoleto($this->dados_item["id"]);
			$conteudo = $boletos_pdf->conteudo();

			$mail->AddStringAttachment($conteudo, "Boleto {$this->dados_item["codigo"]}.pdf", "base64", "application/pdf");

			if ($mail->send()) {

				$this->bancodedados->query("UPDATE tab_cobrancas SET coa_enviada = 1 WHERE coa_id = {$this->dados_item["id"]}");

				$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Enviar boleto<br><b>E-mail(s):</b> ".implode(", ", $emails).($comentario ? "<br><b>Mensagem:</b> {$comentario}" : ""), $usuario);

				return true;

			} else {

				$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Enviar boleto<br><b>E-mail(s):</b> ".implode(", ", $emails).($comentario ? "<br><b>Mensagem:</b> {$comentario}" : "")."<br><b>Erro:</b> {$mail->ErrorInfo}", $usuario);

			}

		}

		return false;

	}

	function enviaBoletoAutomatico($emails = null) {

		if ($this->dados_item && $this->dados_item["status_id"] == 1 && $this->dados_item["parametros"]["boleto"] > 0 && ($this->dados_item["geracao_manual"] || in_array($this->dados_item["lote_status_id"], array(4, 6))) && !$this->dados_item["aguardando_registro"]) {

			if (!$emails) {
				$emails = $this->buscaContatosEmails($this->dados_item["cliente_id"]);
			}

			if ($emails) {

				$this->enviaBoleto($emails);

			} else {

				$auxiliares_financeiros = $this->buscaAxuliaresFinanceiros();

				foreach ($auxiliares_financeiros as $auxiliar_financeiro_id) {
					$this->bancodedados->query("
						INSERT INTO
							tab_clientes_retornos
						SET
							cli_id = {$this->dados_item["cliente_id"]},
							usu_id = {$auxiliar_financeiro_id},
							clr_abertura = NOW(),
							clr_data = CURDATE(),
							clr_comentario = 'Aguardando envio da cobrança <b>{$this->dados_item["codigo"]}</b>.',
							cet_id = 2,
							ces_id = 1");
				}

			}

		}

	}

	function buscaAxuliaresFinanceiros() {

		$auxiliares = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id
			FROM
				tab_usuarios
				INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
			WHERE
				tab_usuarios.uss_id = 1 AND
				rel_usu_gru_fazparte.gru_id = 35");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$auxiliares[] = $dados["id"];
		}

		return $auxiliares;

	}

	function getConteudoEmailBoleto($dados_cobranca, $comentario = null) {

		if ($comentario) {
			$comentario = preg_replace("/[\r\n]+/s", "<br>", strip_tags(trim($comentario)));
		}

		return "
			<html>
			<head>
			</head>
			<body style=\"font-family: Verdana, Arial, sans-serif; font-size: 12px; color: #444444;\">
			<div style=\"width: 480px\">
			<p>Prezado cliente,</p>
			<p>Segue anexo o seu boleto referente aos serviços mensais prestados.</p>".($comentario ? "
			<div style=\"background-color:#E8E8E8;color:#505050;padding:10px\">{$comentario}</div>" : "")."
			<p>Em caso de duvidas, entre em contato com nosso setor financeiro através do telefone <b>48 4042 6262</b>.</p>
			<p>Atenciosamente,</p>
			<p>Olé TV</p>
			</div>
			</body>
			</html>";

	}

	function cadastrarEmailsCliente($cliente_id, $emails) {

		foreach ($emails as $email) {

			$email = explode("@", $email);
			
			$query = $this->bancodedados->query("
				SELECT
					COUNT(ccm_id)
				FROM
					tab_clientes_contatos_emails
				WHERE
					cli_id = $cliente_id AND
					ccm_usuario = '$email[0]' AND
					ccm_dominio = '$email[1]'");
			
			if ($this->bancodedados->resultado($query) == 0) {
				
				$this->bancodedados->query("
					INSERT INTO 
						tab_clientes_contatos_emails
					SET 
						ccm_usuario = '$email[0]', 
						ccm_dominio = '$email[1]', 
						cli_id = $cliente_id, 
						cca_id = 1");
				
			}

		}

	}

	function enviaMensagemCobranca($cliente_id, $dados_cobranca, $emails = null, $comentario = null, $mensagem_lote_id = 2, $data_hora_envio = null, $usuario_id = null) {

		$dados_cliente = $this->buscaDadosCliente($cliente_id);
		$emails = $emails ? $emails : $dados_cliente["emails"];

		if ($emails) {

			$dados_lote = $this->buscaDadosMensagensLotes($mensagem_lote_id);
			$data_hora_envio = $data_hora_envio ? "'$data_hora_envio'" : "NULL";
			$link = md5("cobrancas"."imprimir".$dados_cobranca["id"]);

			if ($this->ehCobrancaCondominial($dados_cobranca["id"])) {
				$detalhamento = $this->geraCobrancaDetalhamentoCondominial($dados_cobranca);
			} else {
				$detalhamento = $this->geraCobrancaDetalhamento($dados_cobranca["id"], $dados_cobranca["valor_bonificacao"], $dados_cobranca["data_vencimento"]);
			}

			$parametros = $dados_lote["parametros"]["envio"];

			$parametros["dados_conteudo"]["id"] = $cliente_id;
			$parametros["dados_conteudo"]["cliente_nome"] = $dados_cliente["nome"];
			$parametros["dados_conteudo"]["empresa_nome"] = $this->buscaOrigemNome($dados_cliente["origem_id"]);
			$parametros["dados_conteudo"]["empresa_telefone"] = $this->buscaOrigemTelefone($dados_cliente["origem_id"]);
			$parametros["dados_conteudo"]["url"] = $this->buscaOrigemURL($dados_cliente["origem_id"]);
			$parametros["dados_conteudo"]["link_0"] = $link;
			$parametros["dados_conteudo"]["codigo"] = $dados_cobranca["codigo"];
			$parametros["dados_conteudo"]["competencia"] = $dados_cobranca["parametros"]["boleto"]["competencia"];
			$parametros["dados_conteudo"]["vencimento"] = $dados_cobranca["data_vencimento"];
			$parametros["dados_conteudo"]["valor"] = $this->formataDinheiro($dados_cobranca["valor_original"]);
			$parametros["dados_conteudo"]["linha_digitavel"] = $dados_cobranca["parametros"]["boleto"]["linha_digitavel"];
			$parametros["dados_conteudo"]["cpf_cnpj"] = $dados_cliente["cpf_cnpj"];
			$parametros["dados_conteudo"]["detalhamento_texto"] = $detalhamento["texto"];
			$parametros["dados_conteudo"]["detalhamento_html"] = $detalhamento["html"];
			$parametros["destinatarios"] = $emails;
			$parametros["remetente_nome"] = "Olé Telecom";
			$parametros["dados_conteudo"]["remetente_nome"] = "Olé Telecom";

			$parametros = addslashes(serialize($parametros));

			$this->bancodedados->query("
				INSERT INTO
					tab_mensagens (men_parametros, men_data_hora_geracao, men_data_hora_envio, cli_id, mes_id, mlm_id, mel_id)
				VALUES ('$parametros', NOW(), $data_hora_envio, $cliente_id, 1, {$dados_lote["modelo_id"]}, 2)");

			$mensagem_id = $this->bancodedados->ultimoIdInserido();

			$this->bancodedados->query("
				INSERT INTO
					tab_mensagens_links (mei_md5, mei_url_modulo, mei_url_acao, mei_url_id, men_id)
				VALUES ('$link', 'cobrancas', 'imprimir', '{$dados_cobranca["id"]}', $mensagem_id)");

			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_enviada = 1 WHERE coa_id = {$dados_cobranca["id"]}");

			$this->registraComentario($dados_cobranca["id"], "<b>Ação:</b> Enviar Cobrança<br><b>E-mail(s):</b> ".implode(", ", $emails).$comentario, $usuario_id);

		}

	}

	function buscaOrigemURL($origem_id) {

		return "http://www.oletelecom.com.br/cobrancas/";

	}

	function buscaOrigemTelefone($origem_id) {

		return "3028-0000";

	}

	function buscaOrigemNome($origem_id) {

		return "Olé Telecom";

	}

	function geraCobrancaDetalhamento($cobranca_id, $valor_bonificacao, $data_vencimento) {

		$detalhamento_valores = $this->buscaDetalhamentoValores($cobranca_id);
		$detalhamento_formatado = array();
		$listagem_valor_cobranca = array();
		$listagem_valor_total = array();
		$listagem_bonificacao = array();
		$detalhamento_descricoes = $this->buscaDetalhamentoDescricoes();
		$listagem = array();

		foreach ($detalhamento_descricoes as $detalhamento => $descricao) {
			if ($detalhamento_valores[$detalhamento]) {
				if ($detalhamento == "valor_cobranca") {
					$listagem_valor_cobranca[] = array("descricao" => $detalhamento_valores[$detalhamento] > 0 ? $descricao : "Descontos", "valor" => $this->formataDinheiro($detalhamento_valores[$detalhamento]));
				} elseif ($detalhamento == "valor_total") {
					$listagem_valor_total[] = array("descricao" => $detalhamento_valores[$detalhamento] > 0 ? $descricao : "Descontos", "valor" => $this->formataDinheiro($detalhamento_valores[$detalhamento]));
				} else {
					$listagem[] = array("descricao" => $detalhamento_valores[$detalhamento] > 0 ? $descricao : "Descontos", "valor" => $this->formataDinheiro($detalhamento_valores[$detalhamento]));
				}
			}
		}

		$listagem = funcoes::mescla_arrays($listagem, $listagem_valor_cobranca, $listagem_valor_total);
		$detalhamento_formatado["texto"] = $this->formataDetalhamentoTexto($listagem);
		$detalhamento_formatado["html"] = $this->formataDetalhamentoHtml($listagem);

		if ($valor_bonificacao) {
			// Listagem Bonificação
			$listagem_bonificacao[] = array("descricao" => "Valor da Cobrança", "valor" => $this->formataDinheiro($detalhamento_valores["valor_total"]));
			$listagem_bonificacao[] = array("descricao" => "Bonificação", "valor" => $this->formataDinheiro(-$valor_bonificacao));
			$listagem_bonificacao[] = array("descricao" => "Total", "valor" => $this->formataDinheiro($detalhamento_valores["valor_total"] - $valor_bonificacao));
			// Adiciona Listagem Bonificação
			$detalhamento_formatado["texto"] .= "\n\n - Para pagamento até $data_vencimento:\n\n".$this->formataDetalhamentoTexto($listagem_bonificacao);
			$detalhamento_formatado["html"] .= "<br>&nbsp; <b>» Para pagamento até $data_vencimento</b>:<br><br>".$this->formataDetalhamentoHtml($listagem_bonificacao);
		}

		return $detalhamento_formatado;

	}

	function geraCobrancaDetalhamentoCondominial($dados_cobranca) {

		$detalhes_cobranca = $this->buscaDetalhesCobrancaCondominial($dados_cobranca, true);
		$detalhes_cobranca = $this->calculaSubTotal($detalhes_cobranca);
		$listagem = array();
		$detalhamento_formatado = array();

		foreach ($detalhes_cobranca as $competencia => $detalhes) {
			$listagem[] = array("descricao" => str_replace(array("&raquo;", "&nbsp;"), array("»", " "), "<b>".strip_tags($competencia))."</b>", "valor" => "");
			foreach ($detalhes as $detalhe) {
				$listagem[] = array("descricao" => str_replace(array("&raquo;", "&nbsp;"), array("»", " "), strip_tags($detalhe["descricao"])), "complemento" => $detalhe["complemento"], "valor" => strip_tags($detalhe["valor_final"]));
			}
		}

		$detalhamento_formatado["texto"] = $this->formataDetalhamentoTexto($listagem, true);
		$detalhamento_formatado["html"] = $this->formataDetalhamentoHtml($listagem, true);

		return $detalhamento_formatado;

	}

	function formataDetalhamentoTexto($listagem, $condominial = false) {

		$detalhamento = array();

		foreach ($listagem as $detalhe) {
			$detalhe["descricao"] = strip_tags($detalhe["descricao"]);
			$detalhe["valor"] = strip_tags($detalhe["valor"]);
			if (!$detalhe["valor"]) {
				$detalhamento[] = "";
			}
			$detalhamento[] = "   ".$detalhe["descricao"].str_repeat(".", 40 - (strlen($detalhe["descricao"]) > 40 ? 40 : strlen($detalhe["descricao"]))).str_repeat(" ", 15 - (strlen($detalhe["valor"]) > 15 ? 15 : strlen($detalhe["valor"]))).$detalhe["valor"];
		}

		return implode("\n", $detalhamento);

	}

	function formataDetalhamentoHtml($listagem, $condominial = false) {

		$detalhamento = array();
		$detalhamento[] = '<table width="'.($condominial ? "90" : "80").'%" cellpadding="0" cellpasing="0" border="0">';

		foreach ($listagem as $chave => $detalhe) {
			if (in_array($detalhe["descricao"], array("Geral", "Total", "Sub-Total", "Valor Final"))) {
				$detalhe["descricao"] = "<b>{$detalhe["descricao"]}</b>";
				$detalhe["valor"] = "<b>{$detalhe["valor"]}</b>";
				$estilo_linha = ";border-top:1px solid #000000";
			} else {
				$estilo_linha = "";
			}
			if ((!$detalhe["valor"] || $detalhe["descricao"] == "<b>Valor Final</b>") && $chave != 0) {
				$detalhamento[] = "<tr><td colspan=\"2\">&nbsp;</td></tr>";
			}
			if ($condominial) {
				if ($detalhe["valor"]) {
					if ($detalhe["descricao"] == "<b>Sub-Total</b>" || $detalhe["descricao"] == "<b>Valor Final</b>") {
						$detalhamento[] = "<tr><td colspan=\"2\" style=\"width:80%$estilo_linha\">{$detalhe["descricao"]}</td><td style=\"width:20%;text-align:right$estilo_linha\">{$detalhe["valor"]}</td></tr>";
					} else {
						$detalhamento[] = "<tr><td style=\"width:70%$estilo_linha\">{$detalhe["descricao"]}</td><td style=\"width:10%;text-align:center$estilo_linha\">{$detalhe["complemento"]}</td><td style=\"width:20%;text-align:right$estilo_linha\">{$detalhe["valor"]}</td></tr>";
					}
				} else {
					$detalhamento[] = "<tr><td style=\"border-bottom:1px solid #000000\" colspan=\"3\">{$detalhe["descricao"]}</td></tr>";
				}
			} else {
				$detalhamento[] = "<tr><td colspan=\"width:80%\">&nbsp; &nbsp; &nbsp; &nbsp; {$detalhe["descricao"]}</td><td style=\"width:20%;text-align:right$estilo_linha\">{$detalhe["valor"]}</td></tr>";
			}
		}

		$detalhamento[] = '</table>';

		return implode("\n", $detalhamento);

	}

	function buscaDetalhamentoDescricoes() {

		$descricao = array();
		
		$descricao["valor_instalacao"] = "Adesão";
		$descricao["valor_assistencia"] = "Assistência Técnica";
		$descricao["valor_venda"] = "Venda de Produtos";
		$descricao["valor_acesso_internet"] = "Conexão à Internet";
		$descricao["valor_iptv"] = "TV por Assinatura";
		$descricao["valor_telefonia"] = "Telefonia";
		$descricao["valor_manutencao_equipamentos"] = "Manutenção de Equipamentos";
		$descricao["valor_dominio"] = "Manutenção de Domínio";
		$descricao["valor_garantia"] = "Olé Garantia";
		$descricao["valor_garantia_bonificacao"] = "Bonificação Olé Garantia";
		$descricao["valor_outros"] = "Outros";
		$descricao["valor_estorno_migracao"] = "Estorno Migração de Plano";
		$descricao["valor_mensalidade"] = "Mensalidade";
		$descricao["valor_multa"] = "Multa Rescisória";
		$descricao["valor_cobranca"] = "Tarifa Bancária";
		$descricao["valor_acrescimos"] = "Outros Acréscimos";
		$descricao["valor_decrescimos"] = "Outros Decréscimos";
		$descricao["valor_desconto_pos_venda"] = "Ajuste Pós-Venda";
		$descricao["valor_desconto_indique_amigo"] = "Desconto Indique Amigo";
		$descricao["valor_desconto_sindico"] = "Desconto Síndico";
		$descricao["valor_plano_dia"] = "Plano Dia";
		$descricao["valor_juro"] = "Juro";
		$descricao["valor_total"] = "Total";

		return $descricao;

	}

	function buscaDetalhamentoValores($cobranca_id) {

		$valores = array();
		$total = 0;

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.cli_id AS cobranca_cliente_id,
				tab_cobrancas_detalhes.cod_id AS id,
				tab_cobrancas_detalhes.ass_id AS ass_id,
				tab_cobrancas_detalhes.dec_id AS dec_id,
				tab_cobrancas_detalhes.ajf_id AS ajf_id,
				tab_cobrancas_detalhes.cod_valor_final AS valor,
				tab_cobrancas_detalhes.cod_descricao AS descricao,
				tab_planos.pls_id AS servico_id,
				tab_ajustes_financeiros.afc_id AS ajuste_categoria_id,
				tab_ajustes_financeiros.cli_id AS ajuste_cliente_id,
				tab_ajustes_financeiros.ajf_competencia_inicio AS competencia_ajuste_inicio,
				tab_ajustes_financeiros.ajf_competencia_termino AS competencia_ajuste_termino
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				LEFT OUTER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.dec_id = tab_cobrancas_detalhes.dec_id)
				LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes.ajf_id)
				LEFT OUTER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = IF(tab_descontos_concessoes.ass_id IS NOT NULL, tab_descontos_concessoes.ass_id, IF(tab_ajustes_financeiros.ass_id IS NOT NULL, tab_ajustes_financeiros.ass_id, tab_cobrancas_detalhes.ass_id)))
				LEFT OUTER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				tab_cobrancas.coa_id = $cobranca_id");

		while (($dados_cobranca = $this->bancodedados->resultadoArray($query)) != false) {
			$agrupador = null;
			if ($dados_cobranca["ass_id"] || $dados_cobranca["dec_id"] || ($dados_cobranca["ajf_id"] && $dados_cobranca["cobranca_cliente_id"] != $dados_cobranca["ajuste_cliente_id"])) {
				if (in_array($dados_cobranca["servico_id"], array(1))) {
					$agrupador = "valor_iptv";
				} else {
					$agrupador = "valor_outros";
				}
			} elseif ($dados_cobranca["ajf_id"]) {
				if ($dados_cobranca["ajuste_categoria_id"] == 10) {
					$agrupador = "valor_multa";
				} elseif ($dados_cobranca["ajuste_categoria_id"] == 19) {
					$agrupador = "valor_instalacao";
				} elseif ($dados_cobranca["ajuste_categoria_id"] == 20) {
					$agrupador = "valor_assistencia";
				} elseif ($dados_cobranca["ajuste_categoria_id"] == 23) {
					$agrupador = "valor_venda";
				} elseif ($dados_cobranca["ajuste_categoria_id"] == 27) {
					$agrupador = "valor_iptv";
				} else {
					$agrupador = "valor_outros";
				}
			} else {
				if ($dados_cobranca["descricao"] == "Taxa de emissão e envio de cobrança" || $dados_cobranca["descricao"] == "Tarifa Bancária") {
					$agrupador = "valor_cobranca";
				} elseif ($dados_cobranca["descricao"] == "Decréscimo") {
					$agrupador = "valor_decrescimos";
				} elseif ($dados_cobranca["descricao"] == "Acréscimo") {
					$agrupador = "valor_acrescimos";
				} elseif (substr($dados_cobranca["descricao"], 0, 4) == "Juro") {
					$agrupador = "valor_juro";
				} else {
					$agrupador = "valor_outros";
				}
			}

			if (array_key_exists($agrupador, $valores)) {
				$valores[$agrupador] += $dados_cobranca["valor"];
			} else {
				$valores[$agrupador] = $dados_cobranca["valor"];
			}

			$total += $dados_cobranca["valor"];

		}

		$valores["valor_total"] = $total;

		return $valores;

	}

	###########
	### RDS ###
	###########

	function buscaClientesCobrancas($cliente_id) {

		$cobrancas = array();
		$selecao_id = 0;

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_total AS valor_total,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m/%Y') AS vencimento,
				tab_cobrancas.cot_id AS status_id,
				IF(tab_cobrancas_lotes.cla_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6), tab_cobrancas_status.cot_nome, CONCAT(tab_cobrancas_status.cot_nome, '<br>(Lote Inativo)')) AS status,
				tab_cobrancas_status.cot_recebido AS status_recebido,
				tab_cobrancas_lotes.col_id AS lote_id,
				tab_cobrancas_lotes.cof_id AS lote_formato_id,
				tab_cobrancas_tipos.cop_id AS tipo_id,
				tab_cobrancas_tipos.cop_nome AS tipo_nome,
                tab_cobrancas.coa_aguardando_registro AS coa_aguardando_registro, 
                tab_cobrancas_formatos.cof_parametros AS formatos_parametros,
				IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas_formatos.cof_remessa IS NOT NULL AND (SELECT COUNT(*) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cri_nosso_numero = tab_cobrancas.coa_nosso_numero AND tab_cobrancas_remessas_itens.cri_status IN ('REGISTRADA', 'REJEITADA') AND tab_cobrancas_remessas_itens.cof_id = tab_cobrancas_formatos.cof_remessa) = 0, 1, 0) AS aguardando_registro
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
				INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
				INNER JOIN tab_cobrancas_tipos ON (tab_cobrancas.cop_id = tab_cobrancas_tipos.cop_id)
			WHERE
				tab_cobrancas.cli_id = $cliente_id
			ORDER BY
				IF(tab_cobrancas.coa_pai, cobranca_pai.coa_data_vencimento, tab_cobrancas.coa_data_vencimento),
				IF(tab_cobrancas.coa_pai, tab_cobrancas.coa_pai, tab_cobrancas.coa_id),
				IF(tab_cobrancas.coa_pai, 1, 0),
				tab_cobrancas.coa_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->temPermissao("ver_dg")) {
				$dados["link"] = "javascript:verCobranca({$dados["id"]});";
			}
			if ($this->temPermissao("cancelar_selecionadas") && $dados["status_id"] == 1) {
				$dados["selecao"] = "<input type=\"checkbox\" id=\"selecao_cobranca_{$selecao_id}\" value=\"{$dados["id"]}\" />";
				$selecao_id++;
			}
			if ($dados["tipo_id"] > 1 && !$dados["coa_pai"]) {
				$dados["codigo"] = "{$dados["codigo"]}<br>(Carnê)";
			}
			$dados["formato"] = $dados["formato_nome"];
			if ($dados["vencimento"]) $dados["formato"] .= "<br>".$this->formataData($dados["vencimento"]);
			if (!$dados["lote_id"]) $dados["formato"] .= '<br>Cobrança Avulsa';
			if ($dados["coa_pai"]) $dados["formato"] .= ' (Segunda Via)';
			
            $dados["formatos_parametros"] = unserialize($dados["formatos_parametros"]);
            if($dados["formatos_parametros"]["parametros"] == "sicredi") {
                if ($dados["status_id"] == 1 && $dados["coa_aguardando_registro"]) $dados["status"] .= ' (Aguardando registro)';
            } else {
				if ($dados["aguardando_registro"]) $dados["status"] .= ' (Aguardando registro)';
			}
		
			if ($dados["lote_formato_id"] == 5 && $dados["formato_id"] != 5 && !$dados["coa_pai"]) $dados["formato"] .= ' (Carteira Inadimplentes)';
			$dados["competencia"] = $this->buscaCobrancaCompetencia($dados["id"], $dados["tipo_id"]);
			$dados["valor_bonificacao"] = $this->formataDinheiro($dados["valor_bonificacao"]);
			$dados["valor_original"] = $this->formataDinheiro($dados["valor_original"], 'R$', true);
			$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"], 'R$', true);
			$dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
			if ($dados["data_pagamento"]) $dados["data_vencimento"] .= "<br>({$dados["data_pagamento"]})";
			if (!in_array($dados["status_id"], array(1, 8, 12))) $dados["valor_original"] .= "<br>({$dados["valor_total"]})";
			$dados["codigo"] = str_replace(" (", "<br>(", $dados["codigo"]);
			$dados["data_vencimento"] = "<div NOWRAP>{$dados["data_vencimento"]}</div>";
			$dados["valor_original"] = "<div NOWRAP>{$dados["valor_original"]}</div>";

			if ($dados["status_recebido"]) {
				$dados["cor_linha"] = "#C4F8C4";
			} elseif ($dados["tipo_id"] == 3 || $dados["tipo_id"] == 4) {
				$dados["cor_linha"] = "#BDE2EE";
			} elseif ($dados["status_id"] == 1) {
				$dados["cor_linha"] = "#D7D7D7";
			}

			$cobrancas[] = $dados;

		}

		return $cobrancas;

	}

	function buscaClientesCobrancasContrato($contrato_id) {
	    
	    $query = "
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_total AS valor_total,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m/%Y') AS vencimento,
				tab_cobrancas.cot_id AS status_id,
				IF(tab_cobrancas_lotes.cla_id IS NULL OR tab_cobrancas_lotes.cla_id IN (4, 6), tab_cobrancas_status.cot_nome, CONCAT(tab_cobrancas_status.cot_nome, '<br>(Lote Inativo)')) AS status,
				tab_cobrancas_status.cot_recebido AS status_recebido,
				tab_cobrancas_lotes.col_id AS lote_id,
				tab_cobrancas_lotes.cof_id AS lote_formato_id,
				tab_cobrancas_tipos.cop_id AS tipo_id,
				tab_cobrancas_tipos.cop_nome AS tipo_nome, 
                tab_cobrancas.coa_aguardando_registro AS coa_aguardando_registro, 
                tab_cobrancas_formatos.cof_parametros AS formatos_parametros,
				IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas_formatos.cof_remessa IS NOT NULL AND (SELECT COUNT(*) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.cri_nosso_numero = tab_cobrancas.coa_nosso_numero AND tab_cobrancas_remessas_itens.cri_status IN ('REGISTRADA', 'REJEITADA') AND tab_cobrancas_remessas_itens.cof_id = tab_cobrancas_formatos.cof_remessa) = 0, 1, 0) AS aguardando_registro
			FROM
				tab_cobrancas
                INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id)
                INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_cobrancas_detalhes.ass_id)
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
				INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
				INNER JOIN tab_cobrancas_tipos ON (tab_cobrancas.cop_id = tab_cobrancas_tipos.cop_id)
			WHERE
				 rel_ass_cor_estacontido.cor_id = $contrato_id
			ORDER BY
				IF(tab_cobrancas.coa_pai, cobranca_pai.coa_data_vencimento, tab_cobrancas.coa_data_vencimento),
				IF(tab_cobrancas.coa_pai, tab_cobrancas.coa_pai, tab_cobrancas.coa_id),
				IF(tab_cobrancas.coa_pai, 1, 0),
				tab_cobrancas.coa_id";
	    $query = $this->bancodedados->query($query);
	    
	    $cobrancas = array();
	    $selecao_id = 0;
	    
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	        
	        if ($this->temPermissao("ver_dg")) {
	            $dados["link"] = "javascript:verCobranca({$dados["id"]});";
	        }
	        if ($this->temPermissao("cancelar_selecionadas") && $dados["status_id"] == 1) {
	            $dados["selecao"] = "<input type=\"checkbox\" id=\"selecao_cobranca_{$selecao_id}\" value=\"{$dados["id"]}\" />";
	            $selecao_id++;
	        }
	        if ($dados["tipo_id"] > 1 && !$dados["coa_pai"]) {
	            $dados["codigo"] = "{$dados["codigo"]}<br>(Carn )";
	        }
	        $dados["formato"] = $dados["formato_nome"];
	        if ($dados["vencimento"]) $dados["formato"] .= "<br>".$this->formataData($dados["vencimento"]);
	        if (!$dados["lote_id"]) $dados["formato"] .= '<br>Cobran a Avulsa';
	        if ($dados["coa_pai"]) $dados["formato"] .= ' (Segunda Via)';

            $dados["formatos_parametros"] = unserialize($dados["formatos_parametros"]);
            if($dados["formatos_parametros"]["parametros"] == "sicredi") {
                if ($dados["status_id"] == 1 && $dados["coa_aguardando_registro"]) $dados["status"] .= ' (Aguardando registro)';
            } else {
				if ($dados["aguardando_registro"]) $dados["status"] .= ' (Aguardando registro)';
			}
	        
	        if ($dados["lote_formato_id"] == 5 && $dados["formato_id"] != 5 && !$dados["coa_pai"]) $dados["formato"] .= ' (Carteira Inadimplentes)';
	        $dados["competencia"] = $this->buscaCobrancaCompetencia($dados["id"], $dados["tipo_id"]);
	        $dados["valor_bonificacao"] = $this->formataDinheiro($dados["valor_bonificacao"]);
	        $dados["valor_original"] = $this->formataDinheiro($dados["valor_original"], 'R$', true);
	        $dados["valor_total"] = $this->formataDinheiro($dados["valor_total"], 'R$', true);
	        $dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
	        if ($dados["data_pagamento"]) $dados["data_vencimento"] .= "<br>({$dados["data_pagamento"]})";
	        if (!in_array($dados["status_id"], array(1, 8, 12))) $dados["valor_original"] .= "<br>({$dados["valor_total"]})";
	        $dados["codigo"] = str_replace(" (", "<br>(", $dados["codigo"]);
	        $dados["data_vencimento"] = "<div NOWRAP>{$dados["data_vencimento"]}</div>";
	        $dados["valor_original"] = "<div NOWRAP>{$dados["valor_original"]}</div>";
	        
	        if ($dados["status_recebido"]) {
	            $dados["cor_linha"] = "#C4F8C4";
	        } elseif ($dados["tipo_id"] == 3 || $dados["tipo_id"] == 4) {
	            $dados["cor_linha"] = "#BDE2EE";
	        } elseif ($dados["status_id"] == 1) {
	            $dados["cor_linha"] = "#D7D7D7";
	        }
	        
	        $cobrancas[] = $dados;
	        
	    }
	    
	    return $cobrancas;
	    
	}

	function geraListagemClientesCobrancas($itens) {

		if ($this->temPermissao("cancelar_selecionadas")) {
			$cabecalho = array ("selecao" => "", "codigo" => "Código", "formato" => "Formato", "competencia" => "Compet.", "data_vencimento" => "Data (V/P)", "valor_bonificacao" => "Bonificação", "valor_original" => "Valor (O/T)", "status" => "Status");
			$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20, 85, 200, 55, 70, 70, 70, 110), null, array("center"));
			$listagem->conteudo .= '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="cancelarSelecionadas()">';
		} else {
			$cabecalho = array ("codigo" => "Código", "formato" => "Formato", "competencia" => "Compet.", "data_vencimento" => "Data (V/P)", "valor_bonificacao" => "Bonificação", "valor_original" => "Valor (O/T)", "status" => "Status");
			$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(85, 220, 55, 70, 70, 70, 110), null, array("center"));
		}

		return $listagem->conteudo;

	}

	function buscaClientesCobrancasRelatorio($cliente_id) {

		$cobrancas = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_codigo AS codigo,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.cot_id AS status_id,
                tab_cobrancas.coa_aguardando_registro AS coa_aguardando_registro,
                tab_cobrancas_formatos.cof_parametros AS formatos_parametros,
				IF(tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1), 'Recebida', IF(tab_cobrancas.cot_id IN (6, 12), 'Cancelada', IF(tab_cobrancas.cot_id IN (7), 'Negociada', IF(tab_cobrancas.cot_id IN (8), 'Cortesia', 'Em aberto')))) AS status
			FROM
				tab_cobrancas
                INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				tab_cobrancas.cli_id = $cliente_id AND
				tab_cobrancas.coa_pai IS NULL
			ORDER BY
				tab_cobrancas.coa_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($dados["status"] != "Recebida" && ($dados_cobranca = $this->buscaDadosCobrancasRelatorio($dados["id"]))) {
				$dados_cobranca["codigo"] = $dados["codigo"];
				$dados_cobranca["data_vencimento"] = $dados["data_vencimento"];
				$dados_cobranca["valor_original"] = $dados["valor_original"];
				$dados = $dados_cobranca;
			}

			if ($dados["vencimento"]) $dados["formato"] .= "<br>".$this->formataData($dados["vencimento"]);
			if (!$dados["lote_id"]) $dados["formato"] .= '<br>Cobrança Avulsa';
			if ($dados["coa_pai"]) $dados["formato"] .= ' (Segunda Via)';
                        
            $dados["formatos_parametros"] = unserialize($dados["formatos_parametros"]);
            if($dados["formatos_parametros"]["parametros"] == "sicredi") {
                if ($dados["status_id"] == 1 && $dados["coa_aguardando_registro"]) $dados["status"] .= ' (Aguardando registro)';
            } else {
				if ($dados["aguardando_registro"]) $dados["status"] .= ' (Aguardando registro)';
            }
			
			if ($dados["lote_formato_id"] == 5 && $dados["formato_id"] != 5 && !$dados["coa_pai"]) $dados["formato"] .= ' (Carteira Inadimplentes)';

			$dados["competencia"] = str_replace("<br>", ", ", $this->buscaCobrancaCompetencia($dados["id"], $dados["tipo_id"]));
			$dados["valor_bonificacao"] = $this->formataDinheiro($dados["valor_bonificacao"]);
			$dados["valor_original"] = $this->formataDinheiro($dados["valor_original"], 'R$', true);
			$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"], 'R$', false);

			$cobrancas[] = $dados;

		}

		return $cobrancas;

	}

	function buscaDadosCobrancasRelatorio($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.coa_codigo AS codigo,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_pagamento,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.cot_id AS status_id,
				IF(tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1), 'Recebida', IF(tab_cobrancas.cot_id IN (6, 12), 'Cancelada', IF(tab_cobrancas.cot_id IN (7), 'Negociada', IF(tab_cobrancas.cot_id IN (8), 'Cortesia', 'Em aberto')))) AS status
			FROM
				tab_cobrancas
			WHERE
				tab_cobrancas.coa_pai = $cobranca_id AND
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)");

		return $this->bancodedados->resultadoArray($query);

	}

	###############
	### Regerar ###
	###############

	function regerar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item["coa_pai"]) {

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=regerar&i={$this->dados_item["coa_pai"]}");

		} elseif ($this->ehRegeravel()) {

			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.regerar.inc.php");

				$this->conteudo = str_replace("[data_vencimento]", $this->dados_item["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

				if (!$this->temPermissao("adicionar_cobranca_avulsa_plus")) {
					$this->conteudo = preg_replace("/<tr id=\"permissao_especial\".*?<\\/tr>/si", "", $this->conteudo);
				}

			} else {
				
				$cobranca_id = $this->regerarCobranca($this->dados_item["id"], $_POST["data_vencimento"], $_POST["nao_gerar_juros"], $_POST["comentario"]);
				
				if ($cobranca_id) {
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
					
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i=$cobranca_id");
					
				} else {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$this->dados_item["id"]}");
				}

			}

		} else {
			$this->fechar();
		}

	}
	
	function regerarCobranca($cobranca_id, $data_vencimento = null, $nao_gerar_juros = true, $comentario = null) {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);

		$this->bancodedados->transacaoIniciar();

		$this->buscaDadosItem($cobranca_id);

		// Cancela cobrança
		$this->bancodedados->query("UPDATE tab_cobrancas SET cot_id = 12 WHERE coa_id = {$cobranca_id}");

		// Cancela detalhes
		$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = {$cobranca_id}");
		
		if ($this->ehRegistrada($cobranca_id)) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_aguardando_registro = 1 WHERE coa_id = {$cobranca_id}");
		}

		// Registra Comentários
		if ($comentario) {
			$comentario = "<br><b>Comentário:</b> ".trim($comentario);
		} else {
			$comentario = "";
		}
		$this->registraComentario($cobranca_id, "<b>Ação:</b> Cobrança Regerada{$comentario}", $_SESSION["usuario"]);

        if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
            if($this->ehSicredi($_GET['i'])) {
                $boletoSicredi->cancelarBoleto($_GET["i"]);
            }
        }
    
		// Cancela Cobranças Filho
		$cobrancas_filho = $this->buscaCobrancasFilho($cobranca_id);
		foreach ($cobrancas_filho as $cobranca_filho_id) {
			// Cancela cobrança
			$this->bancodedados->query("UPDATE tab_cobrancas SET cot_id = 12 WHERE coa_id = {$cobranca_filho_id}");
			// Cancela detalhes
			$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = {$cobranca_filho_id}");
			// Registra Comentários
			$this->registraComentario($cobranca_filho_id, "<b>Ação:</b> Cobrança Regerada{$comentario}", $_SESSION["usuario"]);
					
            if($this->dados_item["formatos_parametros"]["parametros"] == "sicredi") {
                if($this->ehSicredi($cobranca_filho_id)) {
                    $boletoSicredi->cancelarBoleto($cobranca_filho_id);
                }
            }

		}

		// Se estiver regerando a competencia no mes q foi efetuada a migração, cancela todos os ajustes, senão
		// Cancela somente ajuste da ultima competência

		$ajustes = $this->buscaAjustesEstornoMigracaoCancelaveis($this->dados_item["id"]);
		foreach ($ajustes as $ajuste_id) {
			$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 6 WHERE ajf_id = $ajuste_id");
			$this->registraComentarioAjusteFinanceiro($ajuste_id, "Cobrança [{$this->dados_item["codigo"]}] Regerada");
		}

		// Dados cobrança
		$data_vencimento = $data_vencimento ? $data_vencimento : $this->dados_item["data_vencimento"];
		$data_vencimento = substr($data_vencimento, 6, 4)."-".substr($data_vencimento, 3, 2)."-".substr($data_vencimento, 0, 2);
		$gerar_juros = $nao_gerar_juros ? false : true;

		if (strtotime($data_vencimento) < strtotime("today")) {
			$nosso_numero = null;
			$formato_id = 1;
		} else {
			$nosso_numero = null;
			$formato_id = $this->buscaFormatoBoleto($this->dados_item["origem_id"]);
		}

		$vencimentos = $this->buscaVencimentos($this->dados_item["id"]);

		$vencimento_inicio = $vencimentos["inicio"];
		$vencimento_termino = $vencimentos["termino"];

		$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);

		// Gera Cobrança
		$geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
		$cobranca_regerada_id = $geracao->geraCobrancaAvulsa($this->dados_item["cliente_id"], $formato_id, $vencimento_inicio, $vencimento_termino, $data_vencimento, $dados_cliente["dia_vencimento"], $gerar_juros, 0, 0, true, false, $nosso_numero);
		
		if ($cobranca_regerada_id) {

			$this->finalizaBloqueio($this->dados_item["cliente_id"]);

			$this->registraComentario($cobranca_regerada_id, "<b>Ação:</b> Cobrança Regerada$comentario", $_SESSION["usuario"]);
			
		}
		
		$this->bancodedados->transacaoTerminar();

		return $cobranca_regerada_id;
		
	}

	function buscaTiposNota() {
		
		$tipos = array();

		$query = $this->bancodedados->query("
			SELECT
				fap_id AS id,
				fap_nome AS nome
			FROM
				tab_faturas_tipos
			WHERE
				fap_id IN (3, 4)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}

		return $tipos;
		
	}

	function registrar_nota() {
		
		//TODO
		
		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehRegistravelNota()) {

			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.registrar_nota.inc.php");

				$this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
				$this->conteudo = str_replace('[codigo]', $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace('[data_vencimento]', $this->dados_item["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace('[valor_original]', $this->formataDinheiro($this->dados_item["valor_original"]), $this->conteudo);
				
				$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTiposNota(), $this->dados_item["fatura"] ? $this->dados_item["fatura"]["tipo_id"] : null, true, "tipo", "100%"), $this->conteudo);
				$this->conteudo = str_replace('[numero]', $this->geraInput("text", "numero", "135px", $this->dados_item["fatura"] ? $this->dados_item["fatura"]["numero"] : "", true, null, "return formataNumero(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[emissao]', $this->geraInput("text", "emissao", "135px", $this->dados_item["fatura"] ? $this->dados_item["fatura"]["data_emissao"] : "", true, null, "return formataData(this, event)"), $this->conteudo);
				$this->conteudo = str_replace('[valor]', $this->geraInput("text", "valor", "135px", $this->dados_item["fatura"] ? $this->formataDinheiro($this->dados_item["fatura"]["valor"]) : "", true, null, "return formataDinheiro(this, event)"), $this->conteudo);

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$_POST["numero"] = preg_replace("/[^0-9]/", "", $_POST["numero"]);
				$_POST["emissao"] = substr($_POST["emissao"], 6, 4) . "-" . substr($_POST["emissao"], 3, 2) . "-" . substr($_POST["emissao"], 0, 2);
				$_POST["valor"] = (int) preg_replace("/[^0-9]/", "", $_POST["valor"]);

				if ($this->dados_item["fatura"]) {
					
					$this->bancodedados->query("
						UPDATE
							tab_faturas
						SET
							fat_data_emissao = '{$_POST["emissao"]}',
							fat_numero = {$_POST["numero"]},
							fat_valor = {$_POST["valor"]},
							fap_id = {$_POST["tipo"]}
						WHERE
							fat_id = {$this->dados_item["fatura"]["id"]}
					");
					
				} else {

					$vencimento = substr($this->dados_item["data_vencimento"], 6, 4) . "-" . substr($this->dados_item["data_vencimento"], 3, 2) . "-" . substr($this->dados_item["data_vencimento"], 0, 2); ;

					$this->bancodedados->query("
						INSERT INTO
							tab_faturas
						SET
							fat_codigo = '{$this->dados_item["codigo"]}',
							fat_data_emissao = '{$_POST["emissao"]}',
							fat_data_vencimento = '{$vencimento}',
							fat_numero = {$_POST["numero"]},
							fat_valor = {$_POST["valor"]},
							fap_id = {$_POST["tipo"]},
							cli_id = {$this->dados_item["cliente_id"]},
							coa_id = {$this->dados_item["id"]},
							emp_id = 1
					");
					
					$fatura_id = $this->bancodedados->ultimoIdInserido();

					$this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_id = {$_GET["i"]}");

					if ($this->dados_item["coa_pai"]) {
						$this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_id = {$this->dados_item["coa_pai"]} AND fat_id IS NULL");
						$this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_pai = {$this->dados_item["coa_pai"]} AND fat_id IS NULL");
					} else {
						$this->bancodedados->query("UPDATE tab_cobrancas SET fat_id = {$fatura_id} WHERE coa_pai = {$_GET["i"]} AND fat_id IS NULL");
					}

				}

				$q = "UPDATE tab_cobrancas SET gerado_nota = 1 WHERE coa_id = {$_GET["i"]}";
				$this->bancodedados->query($q);
                                
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_df_1&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}
		
	}

	#########################
	### Gerar Nota Fatura ###
	#########################
	
	function gerar_nota_fatura() {
		
		$boletos_fatura = controle::instanciaObjeto("cobrancas.fatura", $this->bancodedados);
		$boletos_fatura->gerarFatura($_GET["i"]);
		$boletos_fatura->exportar();
		
	}

	##########################
	### Enviar Nota Fatura ###
	##########################

	function enviar_nota_fatura() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);

			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.enviar.inc.php");
				
				$this->conteudo = str_replace("[emails]", implode("; ", $dados_cliente["emails"]), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				sort($emails = array_unique(preg_split("/ *[ ,;] */", strtolower($_POST["emails"]))));

				if ($_POST["adicionar_emails"]) {
					$this->cadastrarEmailsCliente($this->dados_item["cliente_id"], $emails);
				}

				$mensagens_emails = controle::instanciaObjeto("mensagens.emails");

				$mensagens_emails->setRemetente("financeiro@oletelecom.com.br", "Olé TV");
				$mensagens_emails->setAssunto("Fatura");
				for ($i = 0; $i < count($emails); $i++) {
					$mensagens_emails->adicionarDestinatario($emails[$i], $this->dados_item["clinte_nome"]);
				}
				$mensagens_emails->setConteudo($this->getConteudoEmailFatura($this->dados_item));

				$boletos_fatura = controle::instanciaObjeto("cobrancas.fatura", $this->bancodedados);
				
				$boletos_fatura->gerarFatura($_GET["i"]);
				
				$conteudo = $boletos_fatura->conteudo();
				
				$mensagens_emails->adicionarAnexo($conteudo, "Fatura {$this->dados_item["codigo"]}.pdf", "base64", "application/pdf");

				if ($mensagens_emails->enviar()) {
					
					$_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
					
					$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Enviar Nota<br><b>E-mail(s):</b> ".implode(", ", $emails).$_POST["comentario"], $_SESSION["usuario"]);
					
					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
					
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");
					
				} else {
					print "<script type=\"text/javascript\">alert(\"Erro ao enviar e-mail!\nFavor contactar o Administrador de Sistema.\"); window.location = \"./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}\";</script>";
					die();
				}

			}

		} else {
			$this->fechar();
		}

	}

	##################
	### Gerar Nota ###
	##################

	function gerar_nota() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehGeravelNota()) {

			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("cobrancas.gerar_nota.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->acao}&i={$_GET["i"]}", $this->conteudo);

			} else {

				$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
				$faturas_lotes->geraNotaCobranca($_GET["i"], 1);

				$_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
				$this->registraComentario($this->dados_item["id"], "<b>Ação:</b> Gerar Nota{$_POST["comentario"]}", $_SESSION["usuario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}
	        
    function pesquisa_boleto() {

        $filtro = $this->inicializaFiltroBoleto($_GET["f"]);
        $pagina = $_GET["p"] ? $_GET["p"] : 1;

        $this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=cobrancas"></script>';
        $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#252525"><strong>&nbsp;Buscar&nbsp; Cobranca</strong></td></tr></table>';
        $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
        $this->conteudo .= $filtro->formulario;

        if ($_GET["f"] || $_GET["p"]) {

            if(empty(trim($_GET["f"]['linha_digitavel'])) && empty(trim($_GET["f"]['nosso_numero']))) {
                $itens['linhas'] = null;
            } else {
                $itens = $this->buscaItens($filtro, $pagina, true);
            }
            $paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
            $listagem = $this->geraListagemBoletos($itens["itens"]);

            $this->conteudo .= '</td></tr></table>';
            $this->conteudo .= $paginacao->conteudo;
            $this->conteudo .= $listagem->conteudo;
            $this->conteudo .= $paginacao->conteudo;

        } else {
                $this->conteudo .= '</td></tr></table>';
        }

	}
        
        
	function geraListagemBoletos($itens) {

		$cabecalho = array ("contador" => "#", "cliente_nome" => "Cliente", "codigo" => "Código", "formato_nome" => "Formato", "data_vencimento" => "Data (V/P)", "nosso_numero" => "Nosso Número", "linha_digitavel" => "Linha Digitável", "linhaDigitavelValidaText" => "Status Linha" , "status_nome" => "Status");

		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 0, 90, 120, 60, 60, 80, 70), null, array("right"));

	}

	function inicializaFiltroBoleto($valores = "") {

            $itens = array();

            // Nosso número
            $linhaDigitavel = preg_replace("/[^0-9]/", "", $valores["linha_digitavel"]);
            $numSicredi1 = substr($linhaDigitavel, 6, 3);
            $numSicredi2 = substr($linhaDigitavel, 10, 5);
            $numSicoob1 = substr($linhaDigitavel, 19, 1);
            $numSicoob2 = substr($linhaDigitavel, 21, 6);
            
            $nossoNumeroSicoob = $numSicoob1 . $numSicoob2;
            $nossoNumeroSicredi = $numSicredi1 . $numSicredi2;
            $nossoNumero = strlen($linhaDigitavel) <= 16 ? substr($linhaDigitavel, 0, 8) : 0;
            
            $itens[] = array ("tipo" => "input", "nome" => "linha_digitavel", "descricao" => "Linha Digitável/ Nosso Número", "valor" => "", "where" => "(tab_cobrancas.coa_nosso_numero IN ('" . $nossoNumero . "','" . $nossoNumeroSicoob . "','" . $nossoNumeroSicredi . "'))");

            // Inicializa Classe Filtro
            $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

            return $filtro;
	}

	function baixar_em_lote() {
		
		if (!$_POST) {
			
			$cobrancas = $this->buscaCobrancasOrigem($_GET["origem"], $_GET["vencimento_inicial"], $_GET["vencimento_final"]);

			$cabecalho = array ("cobranca" => "<input type=\"checkbox\" checked onChange=\"atualizaSelecao(this)\" >", "ordem" => "#", "cliente_id" => "ID", "cliente_nome" => "Nome", "vencimento" => "Vencimento", "valor_original" => "Valor Original", "valor_final" => "Valor Final");

			$listagem = controle::instanciaObjeto("listagem", $cabecalho, $cobrancas, 0, false, array(20, 30, 50, 0, 80, 100, 100), null, array("center", "right", "center", "left", "center", "right", "right"));

			$this->conteudo .= $this->carregaArquivo("cobrancas.baixar_em_lote.inc.php");			

			$this->conteudo = str_replace("[origem]", $this->geraSelect($this->buscaOrigens(), $_GET["origem"], true, "origem", "100%"), $this->conteudo);
			$this->conteudo = str_replace("[vencimento_inicial]", $_GET["vencimento_inicial"], $this->conteudo);
			$this->conteudo = str_replace("[vencimento_final]", $_GET["vencimento_final"], $this->conteudo);
			$this->conteudo = str_replace("[listagem]", $listagem->conteudo, $this->conteudo);
			$this->conteudo = str_replace("[total]", $this->total_baixar_em_lote, $this->conteudo);
			$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaStatusManual(), 5, true, "tipo", "100%"), $this->conteudo);
			$this->conteudo = str_replace('[data_pagamento]', date("d/m/Y"), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m=cobrancas&a=baixar_em_lote&origem={$_GET["origem"]}&vencimento_inicial={$_GET["vencimento_inicial"]}&vencimento_final={$_GET["vencimento_final"]}", $this->conteudo);

		} else {
			
			for ($i = 0; $i < count($_POST["cobranca"]); $i++) {
				
				$cobranca_id = $_POST["cobranca"][$i];
				$tipo_id = $_POST["tipo"];
				$data_pagamento = $_POST["data_pagamento"];
				$valor_final = $_POST["valor_final"][$i];
				$comentario = $_POST["comentario"];
				
				$this->baixarCobranca($cobranca_id, $tipo_id, $data_pagamento, $valor_final, $comentario);
				
			}
			
			header("Location: ./?m=cobrancas&a=baixar_em_lote&origem={$_GET["origem"]}&vencimento_inicial={$_GET["vencimento_inicial"]}&vencimento_final={$_GET["vencimento_final"]}");
			
		}
		
	}
	
	var $total_baixar_em_lote;
	
	function buscaCobrancasOrigem($origem_id, $vencimento_inicial, $vencimento_final) {
		
		$cobrancas = array();
		
		$this->total_baixar_em_lote = 0;
		
		if ($origem_id && $vencimento_inicial && $vencimento_final) {
			
			$vencimento_inicial = substr($vencimento_inicial, 6, 4)."-".substr($vencimento_inicial, 3, 2)."-".substr($vencimento_inicial, 0, 2);
			$vencimento_final = substr($vencimento_final, 6, 4)."-".substr($vencimento_final, 3, 2)."-".substr($vencimento_final, 0, 2);
			
			$query = $this->bancodedados->query("
				SELECT
					tab_cobrancas.coa_id AS id,
					tab_cobrancas.coa_valor_original AS valor,
					DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS vencimento,
					tab_clientes.cli_id AS cliente_id,
					tab_clientes.cli_nome AS cliente_nome
				FROM
					tab_cobrancas
					INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
				WHERE
					tab_clientes.clo_id = {$origem_id} AND
					tab_cobrancas.coa_data_vencimento >= '{$vencimento_inicial}' AND
					tab_cobrancas.coa_data_vencimento <= '{$vencimento_final}' AND
					tab_cobrancas.coa_pai IS NULL AND
					tab_cobrancas.cot_id = 1
				ORDER BY
					tab_clientes.cli_nome,
					tab_cobrancas.coa_data_vencimento");

			$ordem = 1;

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				
				//if (($segunda_via = $this->buscaDadosSegundaVia($dados["id"])) != null) {
				//	$dados = $segunda_via;
				//}					
				
				$this->total_baixar_em_lote += $dados["valor"];
				
				$dados["cor_linha"] = '#99FF99';
				$dados["valor"] = $this->formataDinheiro($dados["valor"]);

				$dados["ordem"] = '<spam ordem="'.$dados["id"].'">'.$ordem.'</spam>';
				$dados["cobranca"] = '<input type="checkbox" name="cobranca[]" value="'.$dados["id"].'" checked onChange="atualizaSelecaoBaixarEmLote(this)" >';
				$dados["cliente_id"] = "[<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>]";
				$dados["valor_original"] = '<input valorOriginal="'.$dados["id"].'" value="'.$dados["valor"].'" type="text" class="inputBrancoAzulEscuro11 valorOriginal" style="width: 100%; text-align: right; background-color: #99FF99" disabled>';
				$dados["valor_final"] = '<input name="valor_final[]" valorFinal="'.$dados["id"].'" value="'.$dados["valor"].'" type="text" class="inputBrancoAzulEscuro11 valorFinal" onKeyPress="return formataDinheiro(this, event);" onKeyUp="atualizaBaixaEmLoteTotal()" onBlur="return formataDinheiro(this, event)" style="width: 100%; text-align: right">';
				
				$cobrancas[] = $dados;
				
				$ordem++;
				
			}
			
		}
		
		$this->total_baixar_em_lote = $this->formataDinheiro($this->total_baixar_em_lote);

		return $cobrancas;
		
	}
	
	function buscaDadosSegundaVia($cobranca_pai) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_valor_original AS valor,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS vencimento,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
			WHERE
				tab_cobrancas.coa_pai = {$cobranca_pai} AND
				tab_cobrancas.cot_id = 1
			ORDER BY
				tab_cobrancas.coa_id DESC");

		return $this->bancodedados->resultadoArray($query);
		
	}

}

?>
