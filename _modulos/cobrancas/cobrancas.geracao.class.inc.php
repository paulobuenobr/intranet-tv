<?php

class cobrancas_geracao {

	private $meses = array(
		 1 => "Jan",
		 2 => "Fev",
		 3 => "Mar",
		 4 => "Abr",
		 5 => "Mai",
		 6 => "Jun",
		 7 => "Jul",
		 8 => "Ago",
		 9 => "Set",
		10 => "Out",
		11 => "Nov",
		12 => "Dez");

	private $bancodedados;

	##################
	### Construtor ###
	##################

	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;

	}

	###########################
	### Gera Cobrancas Lote ###
	###########################

	function geraCobrancasLote($lote_id) {

		$dados_lote = $this->buscaDadosLote($lote_id);
		$dados_formato = $this->buscaDadosFormato($dados_lote["formato_id"]);

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id AS id,
				LPAD(tab_clientes.cli_ccd_vencimento, 2, '0') AS vencimento,
				tab_clientes.cls_id AS status_id,
				tab_clientes.clo_id AS origem_id
			FROM
				tab_clientes
				INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
			WHERE
				tab_clientes.cof_id = {$dados_lote["formato_id"]} AND
				tab_clientes.cli_pai IS NULL
			GROUP BY
				id
			ORDER BY
				id");

		while (($cliente = $this->bancodedados->resultadoArray($query)) != false) {

			$cobranca = $this->geraDadosCobranca($cliente["id"], $dados_lote["vencimento_mes"], $dados_lote["vencimento_ano"], $dados_formato);

			if ($cobranca) {

				$formato_id = !$cobranca["valor_final"] ? 3 : ($cliente["eh_inadimplente"] && $dados_formato["tipo_id"] != 4 ? 2 : $dados_lote["formato_id"]);

				if ($dados_lote["tipo_id"] == 1 || $formato_id == $dados_lote["formato_id"]) {

					$data_vencimento = "{$dados_lote["vencimento_ano"]}-{$dados_lote["vencimento_mes"]}-{$cliente["vencimento"]}";
					$cliente["eh_inadimplente"] = $this->ehInadimplente($cliente["id"]);
					$lote_id = !$cobranca["valor_final"] || ($cliente["eh_inadimplente"] && $dados_formato["tipo_id"] != 4) ? $this->buscaLote(!$cobranca["valor_final"] ? 3 : 2, "{$dados_lote["vencimento_ano"]}-{$dados_lote["vencimento_mes"]}-01") : $dados_lote["id"];

					$this->geraRegistroCobranca($cliente["id"], $cobranca, $formato_id, $data_vencimento, $lote_id);

				}

			}

		}

	}

	#############################
	### Gera Cobrança Cliente ###
	#############################

	function geraDadosCobranca($cliente_id, $vencimento_mes, $vencimento_ano, $dados_formato) {

		$cobranca_geral = array();

		// Busca Clientes Cobrança

		$clientes = $this->buscaClientesCobranca($cliente_id);

		foreach ($clientes as $cliente_cobranca_id) {

			$valor_cobranca = 0;

			// Busca Cobrança Mês Vencimento 2 meses antes

			$vencimento_anterior_mes = date("m", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 2 month ago"));
			$vencimento_anterior_ano = date("Y", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 2 month ago"));

			$cobranca_mes_anterior = $this->buscaCobrancasCliente($cliente_cobranca_id, $vencimento_anterior_mes, $vencimento_anterior_ano, $dados_formato["formato_id"], $valor_cobranca, null, null, null, 0);

			if ($cobranca_mes_anterior && ($cobranca_mes_anterior["valor_total"] != 0 || $cobranca_mes_anterior["assinaturas"])) {
				$cobranca_geral = $this->agrupaCobrancas($cobranca_mes_anterior, $cobranca_geral);
				$valor_cobranca = 0;
			}

			// Busca Cobrança Mês Vencimento 1 mês antes

			$vencimento_anterior_mes = date("m", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago"));
			$vencimento_anterior_ano = date("Y", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago"));

			$cobranca_mes_anterior = $this->buscaCobrancasCliente($cliente_cobranca_id, $vencimento_anterior_mes, $vencimento_anterior_ano, $dados_formato["formato_id"], $valor_cobranca, null, null, null, 0);

			if ($cobranca_mes_anterior && ($cobranca_mes_anterior["valor_total"] != 0 || $cobranca_mes_anterior["assinaturas"])) {
				$cobranca_geral = $this->agrupaCobrancas($cobranca_mes_anterior, $cobranca_geral);
				$valor_cobranca = 0;
			}

			// Busca Cobrança Período Vencimento

			$valor_final = count($clientes) > 1 && $cliente_cobranca_id == $cliente_id ? $cobranca_geral["valor_final"] : 0;

			$cobranca_atual = $this->buscaCobrancasCliente($cliente_cobranca_id, $vencimento_mes, $vencimento_ano, $dados_formato["formato_id"], $valor_cobranca, null, null, null, $valor_final, true);

			if ($cobranca_atual) {
				$cobranca_geral = $this->agrupaCobrancas($cobranca_geral, $cobranca_atual);
			}

		}

		if (
				($cobranca_geral["valor_final"] <= 0) || 
				($cobranca_geral["valor_final"] == $cobranca_geral["valor_cobranca"]) || 
				($cobranca_geral["valor_final"] <= $cobranca_geral["valor_bonificacao"])
			) {
			$cobranca_geral["valor_final"] = 0;
			$cobranca_geral["valor_bonificacao"] = 0;
			$cobranca_geral["valor_cobranca"] = 0;
		}

		// Valor Mínimo

		$valor_minimo = $dados_formato["tipo_id"] == 4 ? 0 : 800;

		// Retorna Cobrança

		if (
				(
					$cobranca_geral["valor_final"] && 
					(!$valor_minimo || $cobranca_geral["valor_final"] - $cobranca_geral["valor_bonificacao"] >= $valor_minimo)
				) || (
					!$cobranca_geral["valor_final"] && 
					(count($cobranca_geral["assinaturas"]) || count($cobranca_geral["ajustes_financeiros"]))
				)
			) {
			return $cobranca_geral;
		}

		return null;

	}

	############################
	### Gera Cobranca Avulsa ###
	############################

	function geraCobrancaAvulsa($cliente_id, $formato_id, $vencimento_inicio, $vencimento_termino, $data_vencimento, $dia_vencimento, $gerar_juros = true, $valor_acrescimo = 0, $valor_decrescimo = 0, $valor_minimo = true, $somente_instalacao = false, $nosso_numero = null) {

		$formato_id = $formato_id ? $formato_id : 1;
		
		$vencimento_mes_termino = substr($vencimento_termino, 0, 2);
		$vencimento_ano_termino = substr($vencimento_termino, 3, 4);

		$cobranca_geral = array();

		// Busca Clientes Cobrança

		$clientes = $this->buscaClientesCobranca($cliente_id);

		foreach ($clientes as $cliente_cobranca_id) {

			$valor_cobranca = 0;

			// Reseta Vencimento Mes/Ano

			$vencimento_mes = substr($vencimento_inicio, 0, 2);
			$vencimento_ano = substr($vencimento_inicio, 3, 4);

			// Busca Cobrança Mês Vencimento 2 meses antes

			$vencimento_anterior_mes = date("m", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 2 month ago"));
			$vencimento_anterior_ano = date("Y", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 2 month ago"));

			$cobranca_mes_anterior = $this->buscaCobrancasCliente($cliente_cobranca_id, $vencimento_anterior_mes, $vencimento_anterior_ano, $formato_id, $valor_cobranca, null, null, null, 0, false, $somente_instalacao);

			if ($cobranca_mes_anterior && ($cobranca_mes_anterior["valor_total"] != 0 || $cobranca_mes_anterior["assinaturas"])) {
				$cobranca_geral = $this->agrupaCobrancas($cobranca_mes_anterior, $cobranca_geral);
				$valor_cobranca = 0;
			}

			// Busca Cobrança Mês Vencimento 1 mês antes

			$vencimento_anterior_mes = date("m", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago"));
			$vencimento_anterior_ano = date("Y", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago"));

			$cobranca_mes_anterior = $this->buscaCobrancasCliente($cliente_cobranca_id, $vencimento_anterior_mes, $vencimento_anterior_ano, $formato_id, $valor_cobranca, null, null, null, 0, false, $somente_instalacao);

			if ($cobranca_mes_anterior && ($cobranca_mes_anterior["valor_total"] != 0 || $cobranca_mes_anterior["assinaturas"])) {
				$cobranca_geral = $this->agrupaCobrancas($cobranca_mes_anterior, $cobranca_geral);
				$valor_cobranca = 0;
			}

			// Busca Cobrança Período Vencimento

			$ultima_cobranca = null;

			$vencimento_mes = substr($vencimento_inicio, 0, 2);
			$vencimento_ano = substr($vencimento_inicio, 3, 4);

			while ("{$vencimento_ano}{$vencimento_mes}" <= "{$vencimento_ano_termino}{$vencimento_mes_termino}") {

				$valor_final = count($clientes) > 1 && $cliente_cobranca_id == $cliente_id ? $cobranca_geral["valor_final"] : 0;
				$cobranca_atual = $this->buscaCobrancasCliente($cliente_cobranca_id, $vencimento_mes, $vencimento_ano, $formato_id, $valor_cobranca, null, null, $ultima_cobranca, $valor_final, true, $somente_instalacao);

				if ($cobranca_atual) {

					$ultima_cobranca = $cobranca_atual;

					if ($gerar_juros && $dia_vencimento && ($juro = $this->calculaJuro($cobranca_atual["valor_final"], "{$vencimento_ano}-{$vencimento_mes}-{$dia_vencimento}", $data_vencimento))) {
						$cobranca_atual["juro"]["{$vencimento_mes}-{$vencimento_ano}"]["descricao"] = "Juro (Ref. ".$this->meses[intval($vencimento_mes)]." / {$vencimento_ano})";
						$cobranca_atual["juro"]["{$vencimento_mes}-{$vencimento_ano}"]["valor_final"] = $juro;
						$cobranca_atual["valor_final"] += $juro;
					}

					$cobranca_geral = $this->agrupaCobrancas($cobranca_geral, $cobranca_atual);
					$valor_cobranca = 0;

				}

				$vencimento = explode("-", date('m-Y', strtotime("{$vencimento_ano}-{$vencimento_mes}-01 + 1 month")));

				$vencimento_mes = $vencimento[0];
				$vencimento_ano = $vencimento[1];

			}

		}

		$cobranca_geral["valor_acrescimo"] = (int) preg_replace("/[^0-9]/", "", $valor_acrescimo);
		$cobranca_geral["valor_decrescimo"] = (int) preg_replace("/[^0-9]/", "", $valor_decrescimo);
		$cobranca_geral["valor_final"] = $cobranca_geral["valor_final"] + $cobranca_geral["valor_acrescimo"] - $cobranca_geral["valor_decrescimo"];

		if (
				($cobranca_geral["valor_final"] <= 0) || 
				($cobranca_geral["valor_final"] == $cobranca_geral["valor_cobranca"]) || 
				($cobranca_geral["valor_final"] <= $cobranca_geral["valor_bonificacao"])
			) {
			$cobranca_geral["valor_final"] = 0;
			$cobranca_geral["valor_bonificacao"] = 0;
			$cobranca_geral["valor_cobranca"] = 0;
		}

		// Valor Mínimo

		$valor_minimo = $valor_minimo ? 1000 : 0;

		// Gera cobrança e retorna ID

		if (
				(
					$cobranca_geral["valor_final"] && 
					(!$valor_minimo || $cobranca_geral["valor_final"] - $cobranca_geral["valor_bonificacao"] >= $valor_minimo)
				) || (
					!$cobranca_geral["valor_final"] && 
					(count($cobranca_geral["assinaturas"]) || count($cobranca_geral["ajustes_financeiros"]))
				)
			) {
			return $this->geraRegistroCobranca($cliente_id, $cobranca_geral, $formato_id, $data_vencimento, "NULL", $nosso_numero);
		}
		
		return null;

	}
	
	########################
	### Gera Segunda Via ###
	########################

	function geraSegundaVia($dados_cobranca, $formato_id, $data_vencimento, $valor_original, $valor_juro = 0, $tipo_ajustes = array(), $valor_ajustes = array(), $tarifa_bancaria = 0, $comentario = null, $usuario_id = null, $atualizar_data_bloqueio = true, $manter_bonificacao = false, $manter_nosso_numero = false) {

		$this->bancodedados->transacaoIniciar();

		// Busca Dados Formato
		$dados_formato = $this->buscaDadosFormato($formato_id);

		// Formata Dados
		$data_vencimento_original = substr($dados_cobranca["data_vencimento"], 6, 4)."-".substr($dados_cobranca["data_vencimento"], 3, 2)."-".substr($dados_cobranca["data_vencimento"], 0, 2);
		$data_vencimento = substr($data_vencimento, 6, 4)."-".substr($data_vencimento, 3, 2)."-".substr($data_vencimento, 0, 2);

		// Gera Nosso Número
		$codigo_cobranca = $this->geraCodigoCobranca($dados_cobranca["cliente_id"], $data_vencimento);

		if ($manter_nosso_numero) {
			$nosso_numero = $dados_cobranca["nosso_numero"];
		} elseif ($dados_formato["remessa"]) {
			$nosso_numero = $this->geraNossoNumero($dados_formato["remessa"]);
		} else {
			$nosso_numero = $codigo_cobranca;
		}

		// Valores
		$valor_original = intval(preg_replace("/[^0-9]/", "", $valor_original));
		$valor_juro = intval(preg_replace("/[^0-9]/", "", $valor_juro));
		$valor_total = $valor_original + $valor_juro;
		$valor_bonificacao = $manter_bonificacao || strtotime($data_vencimento_original) >= strtotime($data_vencimento) ? intval($dados_cobranca["valor_bonificacao"]) : 0;

		// Parâmetros
		$parametros = $dados_cobranca["parametros"];

		if (in_array($dados_formato["tipo_id"], array(2, 3))) {

			$cobranca = array();

			$cobranca["valor_final"] = $valor_total;
			$cobranca["valor_bonificacao"] = $valor_bonificacao;
			$cobranca["parametros"] = $parametros;
			$cobranca["parametros"]["endereco_cobranca"] = $this->buscaEnderecoCobranca($dados_cobranca["cliente_id"]);

			$parametros["boleto"] = $this->buscaParametrosBoleto($dados_cobranca["cliente_id"], $nosso_numero, $codigo_cobranca, $cobranca, $formato_id, $data_vencimento);

			if (strlen($dados_cobranca["competencia"]) == 8) {
				$parametros["boleto"]["competencia"] = substr($dados_cobranca["competencia"], 0, 3)." / ".substr($dados_cobranca["competencia"], 4, 4);
			}

		} else {
			unset($parametros["boleto"]);
		}

		$parametros = addslashes(serialize($parametros));

		// Data Bloqueio

		if ($atualizar_data_bloqueio) {

			if ($this->buscaDiasBloqueio($dados_cobranca["cliente_id"])) {

				$data_bloqueio = date("Y-m-d", strtotime("{$data_vencimento} + ".BLOQUEIO_INADINPLENCIA_SEGUNDA_VIA." days"));
				if (date("w", strtotime($data_bloqueio)) == 0) { // Domingo
					$data_bloqueio = date("Y-m-d", strtotime("{$data_bloqueio} + 1 days"));
				} elseif (date("w", strtotime($data_bloqueio)) == 6) { // Sábado
					$data_bloqueio = date("Y-m-d", strtotime("{$data_bloqueio} + 2 days"));
				}
				$data_bloqueio = "'{$data_bloqueio}'";

				// Atualiza Data Bloqueio Cobrança Pai
				$this->atualizaDataBloqueioCobrancaPai($dados_cobranca["id"], $data_bloqueio);

			} else {

				$data_bloqueio = "NULL";

			}

		} else {

			$data_bloqueio = $dados_cobranca["data_bloqueio"] ? "'{$dados_cobranca["data_bloqueio"]}'" : "NULL";

		}

		$grupo_id = $dados_cobranca["grupo_id"] ? $dados_cobranca["grupo_id"] : "NULL";
		$fatura_lote_cobranca_id = $dados_cobranca["fatura_lote_cobranca_id"] ? $dados_cobranca["fatura_lote_cobranca_id"] : "NULL";
		$fatura_id = $dados_cobranca["fatura_id"] ? $dados_cobranca["fatura_id"] : "NULL";

		// Insere Cobrança

		$aguardando_registro = !$manter_nosso_numero && $dados_formato["remessa"] ? 1 : 0;

		$dias_protesto = $this->buscaDiasProtesto($dados_cobranca["cliente_id"]);

		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas
			SET
				cli_id = {$dados_cobranca["cliente_id"]},
				col_id = NULL,
				cof_id = {$formato_id},
				coa_data_geracao = CURDATE(),
				coa_data_vencimento = '{$data_vencimento}',
				coa_data_bloqueio = {$data_bloqueio},
				coa_codigo = '{$codigo_cobranca}',
				coa_nosso_numero = '{$nosso_numero}',
				coa_parametros = '{$parametros}',
				coa_valor_original = {$valor_total},
				coa_valor_bonificacao = {$valor_bonificacao},
				coa_dias_protesto = {$dias_protesto},
				coa_aguardando_registro = {$aguardando_registro},
				coa_grupo_id = {$grupo_id},
				cot_id = 1,
				coa_pai = {$dados_cobranca["id"]},
				cop_id = {$dados_cobranca["tipo_id"]},
				fco_id = {$fatura_lote_cobranca_id},
				fat_id = {$fatura_id}");

		$cobranca_id = $this->bancodedados->ultimoIdInserido();

		// Detalhamentos

		$detalhamentos = $this->buscaDetalhamento($dados_cobranca["id"]);

		$cobranca_detalhe_id =  null;
		$detalhamentos_inseridos = array();

		foreach ($detalhamentos as $detalhamento) {

			if (($detalhamento["ass_id"] || $detalhamento["dec_id"] || $detalhamento["ajf_id"] || $detalhamento["cod_pai"]) && strpos($detalhamento["descricao"], "Tarifa Bancária") === false  && $detalhamento["descricao"] != "Taxa de emissão e envio de cobrança") {

				$detalhamento["ass_id"] = $detalhamento["ass_id"] ? $detalhamento["ass_id"] : "NULL";
				$detalhamento["dec_id"] = $detalhamento["dec_id"] ? $detalhamento["dec_id"] : "NULL";
				$detalhamento["ajf_id"] = $detalhamento["ajf_id"] ? $detalhamento["ajf_id"] : "NULL";
				$detalhamento["cod_pai"] = $detalhamento["cod_pai"] ? $cobranca_detalhe_id : "NULL";

				$this->bancodedados->query("
					INSERT INTO
						tab_cobrancas_detalhes
					SET
						coa_id = {$cobranca_id},
						ass_id = {$detalhamento["ass_id"]},
						dec_id = {$detalhamento["dec_id"]},
						ajf_id = {$detalhamento["ajf_id"]},
						cod_competencia_inicio = '{$detalhamento["competencia_inicio"]}',
						cod_competencia_termino = '{$detalhamento["competencia_termino"]}',
						cod_descricao = '{$detalhamento["descricao"]}',
						cod_valor_original = {$detalhamento["valor_original"]},
						cod_valor_final = {$detalhamento["valor_final"]},
						cod_pai = {$detalhamento["cod_pai"]}");

				$detalhe_id = $this->bancodedados->ultimoIdInserido();

				$cobranca_detalhe_id = $detalhamento["cod_pai"] != "NULL" ? $detalhamento["cod_pai"] : $detalhe_id;

				if ($detalhamento["cod_pai"] == "NULL") {
					$detalhamentos_inseridos[$detalhe_id] = $detalhamento;
				}

			}

		}

		foreach ($detalhamentos_inseridos as $id_novo => $detalhamento) {

			if (array_key_exists($detalhamento["id"], $tipo_ajustes)) {

				if ($tipo_ajustes[$detalhamento["id"]] == "+") {
					$valor = intval(preg_replace("/[^0-9]/", "", $valor_ajustes[$detalhamento["id"]]));
					$descricao = "Acréscimo";
				} else {
					$valor = intval(preg_replace("/[^0-9]/", "", $valor_ajustes[$detalhamento["id"]])) * -1;
					$descricao = "Decréscimo";
				}

				if ($valor != 0) {
					$this->bancodedados->query("
						INSERT INTO
							tab_cobrancas_detalhes
						SET
							coa_id = {$cobranca_id},
							ass_id = NULL,
							dec_id = NULL,
							ajf_id = NULL,
							cod_competencia_inicio = '{$detalhamento["competencia_inicio"]}',
							cod_competencia_termino = '{$detalhamento["competencia_termino"]}',
							cod_descricao = '{$descricao}',
							cod_valor_original = {$valor},
							cod_valor_final = {$valor},
							cod_pai = {$id_novo}");
				}

			}

		}

		// Juro

		if ($valor_juro) {
			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_detalhes
				SET
					coa_id = {$cobranca_id},
					ass_id = NULL,
					dec_id = NULL,
					ajf_id = NULL,
					cod_competencia_inicio = NULL,
					cod_competencia_termino = NULL,
					cod_descricao = 'Juro',
					cod_valor_original = {$valor_juro},
					cod_valor_final = {$valor_juro},
					cod_pai = NULL");
		}

		// Registra Comentários

		if ($comentario) {
			if (substr($comentario, 0, 3) != "<b>") {
				$comentario = "<br><b>Comentário:</b> ".trim($comentario);
			} else {
				$comentario = "<br>".trim($comentario);
			}
		}

		$usuario_id = $usuario_id ? $usuario_id : "NULL";

		$this->registraComentario($cobranca_id, "<b>Ação:</b> Gerar Segunda Via{$comentario}", $usuario_id);

		$this->bancodedados->transacaoTerminar();
				
        if($dados_formato["parametros"]["parametros"] == "sicredi") {
            $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
            $boletoSicredi->criarBoleto($formato_id, $cobranca_id);
        }

		return $cobranca_id;

	}

	##########################
	### Funções de Geração ###
	##########################
	
	function buscaDiasProtesto($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_assinaturas.ass_id)
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.cli_id = {$cliente_id} AND
				tab_assinaturas.ast_id = 2 AND
				tab_planos.pls_id = 2");
		
		return $this->bancodedados->resultado($query) > 0 ? 10 : 0;
		
	}

	function calculaJuro($valor_final, $data_vencimento_original, $data_vencimento_final) {

		if (($dias = (strtotime($data_vencimento_final) - strtotime($data_vencimento_original)) / 60 / 60 / 24) > 0) {
			return round(($valor_final * 0.02) + ($valor_final * 0.01 * $dias / 30));
		}
		return false;

	}

	function buscaClientesCobranca($cliente_id) {

		$clientes = array();

		$query = $this->bancodedados->query("
			SELECT
				cli_id AS id
			FROM
				tab_clientes
			WHERE
				cli_pai = {$cliente_id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes[] = $dados["id"];
		}

		$clientes[] = $cliente_id;

		return $clientes;

	}

	function registraComentario($cobranca_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_comentarios
			SET
				com_datahora = NOW(),
				coa_id = {$cobranca_id},
				com_conteudo = '{$conteudo}',
				usu_id = {$usuario_id}");

	}

	function buscaVia($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id)
			FROM
				tab_cobrancas
			WHERE
				coa_pai = {$cobranca_id}");
		
		return $this->bancodedados->resultado($query) + 1;

	}

	function buscaDetalhamento($cobranca_id) {

		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				cod_id AS id,
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
				tab_cobrancas_detalhes.coa_id = {$cobranca_id}
			ORDER BY
				tab_cobrancas_detalhes.cod_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}

		return $lista;
	
	}

	function atualizaDataBloqueioCobrancaPai($cobranca_id, $data_bloqueio) {

		$this->bancodedados->query("
			UPDATE
				tab_cobrancas
			SET
				coa_data_bloqueio = {$data_bloqueio}
			WHERE
				coa_id = {$cobranca_id} AND
				coa_data_bloqueio < {$data_bloqueio}");

	}

	function possuiCobranca($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id)
			FROM
				tab_cobrancas
			WHERE
				cli_id = $cliente_id");

		return $this->bancodedados->resultado($query) > 0;

	}

	function agrupaCobrancas($cobranca_mes_anterior = null, $cobranca_mes_atual = null) {

		if (!$cobranca_mes_anterior) {
			return $cobranca_mes_atual;
		}

		if (!$cobranca_mes_atual) {
			return $cobranca_mes_anterior;
		}

		$cobranca_mes_atual["sistema_id"] = $cobranca_mes_atual["sistema_id"] == 1 ? $cobranca_mes_anterior["sistema_id"] : $cobranca_mes_atual["sistema_id"];
		$cobranca_mes_atual["assinaturas"] = funcoes::mescla_arrays($cobranca_mes_anterior["assinaturas"], $cobranca_mes_atual["assinaturas"]);
		$cobranca_mes_atual["juro"] = funcoes::mescla_arrays($cobranca_mes_anterior["juro"], $cobranca_mes_atual["juro"]);
		$cobranca_mes_atual["ajustes_financeiros"] = $cobranca_mes_atual["ajustes_financeiros"] || $cobranca_mes_anterior["ajustes_financeiros"] ? funcoes::mescla_arrays($cobranca_mes_anterior["ajustes_financeiros"], $cobranca_mes_atual["ajustes_financeiros"]) : null;
		$cobranca_mes_atual["valor_bonificacao"] = $cobranca_mes_atual["valor_bonificacao"] + $cobranca_mes_anterior["valor_bonificacao"];
		$cobranca_mes_atual["valor_total"] = $cobranca_mes_atual["valor_total"] + $cobranca_mes_anterior["valor_total"];
		$cobranca_mes_atual["valor_cobranca"] = $cobranca_mes_atual["valor_cobranca"] + $cobranca_mes_anterior["valor_cobranca"];
		$cobranca_mes_atual["valor_final"] = $cobranca_mes_atual["valor_final"] + $cobranca_mes_anterior["valor_final"];

		return $cobranca_mes_atual;

	}

	function geraRegistroCobranca($cliente_id, $cobranca, $formato_id, $data_vencimento, $lote_id = "NULL", $nosso_numero = null) {

		$this->bancodedados->transacaoIniciar();

		// Gera Código

		$codigo_cobranca = $this->geraCodigoCobranca($cliente_id, $data_vencimento);

		if ($codigo_cobranca) {

			$cobranca["valor_final"] = intval($cobranca["valor_final"]);
			$cobranca["valor_bonificacao"] = intval($cobranca["valor_bonificacao"]);

			if (!$cobranca["valor_final"]) {
				$formato_id = 3;
			}

			// Busca Dados Formato

			$dados_formato = $this->buscaDadosFormato($formato_id);

			// Gera Nosso Número

			if (!$nosso_numero) {
				if ($dados_formato["remessa"]) {
					$nosso_numero = $this->geraNossoNumero($dados_formato["remessa"]);
				} else {
					$nosso_numero = $codigo_cobranca;
				}
			}

			// Status da Cobrança

			$status_id = !$cobranca["valor_final"] || $cobranca["valor_final"] == $cobranca["valor_bonificacao"] ? 8 : 1;

			// Tipo de cobrança

			$tipo_id = $lote_id && $lote_id != "NULL" ? $this->buscaTipoLote($lote_id) : 1;

			// Parâmetros de Boleto

			if (in_array($dados_formato["tipo_id"], array(2, 3))) {
				$cobranca["parametros"]["boleto"] = $this->buscaParametrosBoleto($cliente_id, $nosso_numero, $codigo_cobranca, $cobranca, $formato_id, $data_vencimento, $lote_id);
			}

			$parametros = addslashes(serialize($cobranca["parametros"]));

			// Bloqueio

			$dias_bloqueio = $this->buscaDiasBloqueio($cliente_id);

			if ($dias_bloqueio) {

				$data_bloqueio = date("Y-m-d", strtotime("{$data_vencimento} {$dias_bloqueio} days"));
				if (date("w", strtotime($data_bloqueio)) == 0) { // Domingo
					$data_bloqueio = date("Y-m-d", strtotime("{$data_bloqueio} 1 days"));
				} elseif (date("w", strtotime($data_bloqueio)) == 6) { // Sábado
					$data_bloqueio = date("Y-m-d", strtotime("{$data_bloqueio} 2 days"));
				}
				$data_bloqueio = "'{$data_bloqueio}'";

			} else {

				$data_bloqueio = "NULL";

			}

			// Insere Cobrança

			$aguardando_registro = $dados_formato["remessa"] ? 1 : 0;
			
			$dias_protesto = $this->buscaDiasProtesto($cliente_id);

			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas
				SET
					cli_id = {$cliente_id},
					col_id = {$lote_id},
					cof_id = {$formato_id},
					coa_data_geracao = CURDATE(),
					coa_data_vencimento = '{$data_vencimento}',
					coa_data_bloqueio = {$data_bloqueio},
					coa_codigo = '{$codigo_cobranca}',
					coa_nosso_numero = '{$nosso_numero}',
					coa_parametros = '{$parametros}',
					coa_valor_original = {$cobranca["valor_final"]},
					coa_valor_bonificacao = {$cobranca["valor_bonificacao"]},
					coa_dias_protesto = {$dias_protesto},
					coa_aguardando_registro = {$aguardando_registro},
					cot_id = {$status_id},
					cop_id = {$tipo_id}");

			$cobranca_id = $this->bancodedados->ultimoIdInserido();

			// Insere Cobrança Detalhes Assinaturas

			if ($cobranca["assinaturas"]) {

				foreach ($cobranca["assinaturas"] as $assinatura) {

					if ($cobranca["valor_cobranca"]) {
						$assinatura["valor_original"] += $cobranca["valor_cobranca"];
						$assinatura["valor_final"] += $cobranca["valor_cobranca"];
						$cobranca["valor_cobranca"] = 0;
					}

					$detalhe_pai = $this->geraCobrancaDetalhe($cobranca_id, $assinatura["id"], 'NULL', 'NULL', $assinatura["competencia_inicio"], $assinatura["competencia_termino"], $assinatura["descricao"], $assinatura["valor_original"], $assinatura["valor_final"], 'NULL');

					// Insere Cobrança Detalhes Descontos

					if ($assinatura["descontos_concessoes"]) {
						foreach ($assinatura["descontos_concessoes"] as $desconto) {
							$this->geraCobrancaDetalhe($cobranca_id, 'NULL', $desconto["id"], 'NULL', $desconto["competencia_inicio"], $desconto["competencia_termino"], $desconto["descricao"], $desconto["valor_original"], $desconto["valor_final"], $detalhe_pai);
						}
					}

					// Insere Cobrança Detalhes Ajustes Financeiros / Assinatura

					if ($assinatura["ajustes_financeiros"]) {
						foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
							$this->geraCobrancaDetalhe($cobranca_id, 'NULL', 'NULL', $ajuste_financeiro["id"], $ajuste_financeiro["competencia_inicio"], $ajuste_financeiro["competencia_termino"], $ajuste_financeiro["descricao"], $ajuste_financeiro["valor_original"], $ajuste_financeiro["valor_final"], $detalhe_pai);
						}
					}

				}

			}

			// Insere Cobrança Detalhes Ajustes Financeiros / Cliente

			if ($cobranca["ajustes_financeiros"]) {

				foreach ($cobranca["ajustes_financeiros"] as $ajuste_financeiro) {

					if ($cobranca["valor_cobranca"]) {
						$ajuste_financeiro["valor_original"] += $cobranca["valor_cobranca"];
						$ajuste_financeiro["valor_final"] += $cobranca["valor_cobranca"];
						$cobranca["valor_cobranca"] = 0;
					}

					$this->geraCobrancaDetalhe($cobranca_id, 'NULL', 'NULL', $ajuste_financeiro["id"], $ajuste_financeiro["competencia_inicio"], $ajuste_financeiro["competencia_termino"], $ajuste_financeiro["descricao"], $ajuste_financeiro["valor_original"], $ajuste_financeiro["valor_final"], 'NULL');

				}

			}

			// Insere Cobrança Juros

			if ($cobranca["juro"]) {
				foreach ($cobranca["juro"] as $juro) {
					$this->geraCobrancaDetalhe($cobranca_id, 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', $juro["descricao"], $juro["valor_final"], $juro["valor_final"], 'NULL');
				}
			}

			// Insere Valor Acréscimo

			if ($cobranca["valor_acrescimo"]) {
				$this->geraCobrancaDetalhe($cobranca_id, 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', "Acréscimo", $cobranca["valor_acrescimo"], $cobranca["valor_acrescimo"], 'NULL');
			}

			// Insere Valor Decréscimo

			if ($cobranca["valor_decrescimo"]) {
				$this->geraCobrancaDetalhe($cobranca_id, 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', "Decréscimo", -$cobranca["valor_decrescimo"], -$cobranca["valor_decrescimo"], 'NULL');
			}

			// Insere Cobrança Detalhes Taxa de Emissão e Envio da Cobrança

			if ($cobranca["valor_cobranca"]) {
				$this->geraCobrancaDetalhe($cobranca_id, 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', 'Tarifa Bancária', $cobranca["valor_cobranca"], $cobranca["valor_cobranca"], 'NULL');
			}

			$this->bancodedados->transacaoTerminar();

            if(($lote_id == null || $lote_id == "NULL") && $dados_formato["parametros"]["parametros"] == "sicredi") {
                $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
                $boletoSicredi->criarBoleto($formato_id, $cobranca_id);
            } 
                        
			return $cobranca_id;

		} else {

			return "erro_codigo";

		}

	}

	function geraParametrosFatura($cobranca_id) {

		$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
		$dados_fatura = $this->buscaDadosFatura($cobranca_id);

		$faturas_items = $faturas_lotes->buscaFaturaItens($dados_fatura);

		if (count($faturas_items["itens"])) {

			$items = array();

			foreach ($faturas_items["itens"] as $item) {
				$chave = "{$item["empresa_nome"]} ({$item["empresa_cnpj"]}) - {$item["descricao"]}";
				if (!array_key_exists($chave, $items)) {
					$items[$chave] = 0;
				}
				$items[$chave] += $item["valor_total"];
			}

			$parametros = unserialize($this->bancodedados->resultado($this->bancodedados->query("SELECT coa_parametros FROM tab_cobrancas WHERE coa_id = {$cobranca_id}")));

			$parametros["itens"] = array();

			foreach ($items as $item => $valor) {
				$parametros["itens"][] = $item.": ".$this->formataDinheiro($valor);
			}

			$parametros = addslashes(serialize($parametros));

			$this->bancodedados->query("UPDATE tab_cobrancas SET coa_parametros = '{$parametros}' WHERE coa_id = {$cobranca_id}");

		}

	}

	function buscaDadosFatura($cobranca_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.cli_id AS cliente_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_desconto AS valor_desconto,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_juro AS valor_juro,
				tab_cobrancas.coa_data_geracao AS data_geracao,
				tab_cobrancas.coa_data_processamento AS data_processamento,
				tab_cobrancas.coa_data_vencimento AS data_vencimento,
				tab_cobrancas.cot_id AS status_id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.cop_id AS tipo_id
			FROM
				tab_cobrancas
			WHERE
				tab_cobrancas.coa_id = {$cobranca_id}");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaTipoLote($lote_id) {

		$query = $this->bancodedados->query("
			SELECT
				cop_id
			FROM
				tab_cobrancas_lotes
			WHERE
				col_id = {$lote_id}");

		return $this->bancodedados->resultado($query);

	}

	function geraCodigoCobranca($cliente_id, $data_vencimento) {

		// Mês vencimento
		$nosso_numero  = sprintf("%02s", (int) substr($data_vencimento, 5, 2));
		// Ano vencimento
		$nosso_numero .= sprintf("%02s", (int) substr($data_vencimento, 2, 2));
		// Contador: cobrança no mês por cliente
		$nosso_numero .= "__";
		// ID cliente
		$nosso_numero .= sprintf("%06s", (int) $cliente_id);

		$contador = $this->buscaContadorNossoNumero($cliente_id, $nosso_numero);

		if ($contador < 100) {
			return str_replace("__", sprintf("%02s", $contador), $nosso_numero);
		}

	}

	function buscaNossoNumero($cobranca_id, $formato_id) {

		$query = $this->bancodedados->query("
			SELECT
				coa_nosso_numero
			FROM
				tab_cobrancas
			WHERE
				cof_id IN (
					SELECT
						cof_id
					FROM
						tab_cobrancas_formatos
					WHERE
						cof_remessa = (
							SELECT
								cof_remessa
							FROM
								tab_cobrancas_formatos
							WHERE
								cof_id = {$formato_id}
						)
				) AND (
					coa_id = {$cobranca_id} OR
					coa_pai = {$cobranca_id}
				)
			LIMIT 1");

		return $this->bancodedados->resultado($query);

	}

	function geraNossoNumero($formato_id) {

		if ($formato_id == 8) {

			$this->bancodedados->query("
				UPDATE
					tab_cobrancas_formatos
				SET
					cof_nosso_numero = last_insert_id(IF(SUBSTRING(cof_nosso_numero, 1, 2) = DATE_FORMAT(CURDATE(), '%y'), cof_nosso_numero + 1, CONCAT(DATE_FORMAT(CURDATE(), '%y'), '200001')))
				WHERE
					cof_id = 8");

			$query = $this->bancodedados->query("SELECT last_insert_id()");

			return $this->bancodedados->resultado($query);
			
		} else {

			$this->bancodedados->query("
				UPDATE
					tab_cobrancas_formatos
				SET
					cof_nosso_numero = last_insert_id(cof_nosso_numero + 1)
				WHERE
					cof_id = {$formato_id}");
	
			$query = $this->bancodedados->query("SELECT last_insert_id()");
	
			return sprintf("%07s", $this->bancodedados->resultado($query));

		}

	}

	function buscaContadorNossoNumero($cliente_id, $nosso_numero) {

		$query = $this->bancodedados->query("
			SELECT
				MAX(SUBSTRING(coa_codigo, 5, 2)) AS ordem
			FROM
				tab_cobrancas
			WHERE
				cli_id = {$cliente_id} AND
				coa_codigo LIKE '{$nosso_numero}'
			HAVING
				COUNT(coa_id) > 0");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			return intval($dados["ordem"]) + 1;
		}
		
		return 0;

	}

	function buscaNossoNumeroOrdem($cliente_id, $codigo_prefixo) {

		$query = $this->bancodedados->query("
			SELECT
				MAX(SUBSTRING(coa_codigo, 5, 1)) AS ordem
			FROM
				tab_cobrancas
			WHERE
				cli_id = {$cliente_id} AND
				coa_codigo LIKE '{$codigo_prefixo}%'
			HAVING
				COUNT(coa_id) > 0");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			return $dados["ordem"] + 1;
		}

		return 0;

	}

	function buscaParametrosBoleto($cliente_id, $nosso_numero, $codigo_cobranca, $cobranca, $formato_id, $data_vencimento, $lote_id = null) {

		// Busca Dados

		$dados_formato = $this->buscaDadosFormato($formato_id);
		$dados_cliente = $this->buscaDadosCliente($cliente_id);

		// Monta Endereço Cobrança

		$endereco_cobranca = array();

		if ($cobranca["parametros"]["endereco_cobranca"]["condominio"]) {
			$endereco_cobranca[] = "Condomínio ".$cobranca["parametros"]["endereco_cobranca"]["condominio"];
			if ($cobranca["parametros"]["endereco_cobranca"]["bloco"] && $cobranca["parametros"]["endereco_cobranca"]["bloco"] != "Único") $endereco_cobranca[] = "Bloco ".$cobranca["parametros"]["endereco_cobranca"]["bloco"];
			if ($cobranca["parametros"]["endereco_cobranca"]["complemento"]) $endereco_cobranca[] = $cobranca["parametros"]["endereco_cobranca"]["complemento"];
		} else {
			if ($cobranca["parametros"]["endereco_cobranca"]["logradouro"]) $endereco_cobranca[] = $cobranca["parametros"]["endereco_cobranca"]["logradouro"];
			if ($cobranca["parametros"]["endereco_cobranca"]["numero"]) $endereco_cobranca[] = $cobranca["parametros"]["endereco_cobranca"]["numero"];
			if ($cobranca["parametros"]["endereco_cobranca"]["bairro"]) $endereco_cobranca[] = $cobranca["parametros"]["endereco_cobranca"]["bairro"];
			if ($cobranca["parametros"]["endereco_cobranca"]["cidade"]) $endereco_cobranca[] = $cobranca["parametros"]["endereco_cobranca"]["cidade"];
			if ($cobranca["parametros"]["endereco_cobranca"]["estado"]) $endereco_cobranca[] = $cobranca["parametros"]["endereco_cobranca"]["estado"];
		}

		$endereco_cobranca = implode(", ", $endereco_cobranca);

		// Parametros Boleto

		$parametros = array();

		$parametros["agencia"] = $dados_formato["parametros"]["agencia"]["valor_formatado"];
		$parametros["sacado"][] = $dados_cliente["nome"];
		$parametros["sacado"][] = $endereco_cobranca;
		$parametros["sacado"] = implode("<br>", $parametros["sacado"]);
		$parametros["empresa_nome"] = $dados_formato["parametros"]["empresa"]["valor_formatado"];
		$parametros["empresa_cnpj"] = $dados_formato["parametros"]["cnpj"]["valor_formatado"];
		$parametros["competencia"] = $this->buscaCompetencia($cobranca);
		$parametros["data_emissao"] = date("d/m/Y");
		$parametros["data_processamento"] = date("d/m/Y");
		$parametros["especie_moeda"] = $dados_formato["parametros"]["moeda"]["valor_formatado"];
		$parametros["modalidade_carteira"] = $dados_formato["parametros"]["modalidade_carteira"]["valor_formatado"];
		$parametros["instrucoes"] = $dados_formato["parametros"]["instrucoes"];
		if ($cobranca["valor_bonificacao"]) {
			$parametros["instrucoes"] = "Conceder desconto de ".$this->formataDinheiro(intval($cobranca["valor_bonificacao"]))." para pagamento até ".date("d/m/Y", strtotime($data_vencimento)).". ".$dados_formato["parametros"]["instrucoes"];
		}
		$parametros["instrucoes"] = mb_strtoupper($parametros["instrucoes"], "iso-8859-1");
		$parametros["aceite"] = $dados_formato["parametros"]["aceite"];
		$parametros["local_pagamento"] = $dados_formato["parametros"]["local_pagamento"];
		$parametros["especie_doc"] = $dados_formato["parametros"]["especie_doc"];
		$parametros["versao"] = $dados_formato["parametros"]["boleto_versao"];
		$parametros["numero_documento"] = $codigo_cobranca;

		// Instancia classe geração parametros

		if ($dados_formato["parametros"]["parametros"]) {

			$dados = array();

			$dados["codigo_banco"] = $dados_formato["parametros"]["codigo_banco"]["valor"];
			$dados["codigo_cedente"] = $dados_formato["parametros"]["cedente"]["valor"];
			$dados["agencia"] = $dados_formato["parametros"]["agencia"]["valor"];
			$dados["posto"] = $dados_formato["parametros"]["posto"]["valor"];
			$dados["modalidade_carteira"] = $dados_formato["parametros"]["modalidade_carteira"]["valor"];
			$dados["moeda"] = $dados_formato["parametros"]["moeda"]["valor"];
			$dados["data_vencimento"] = date("d/m/Y", strtotime($data_vencimento));
			$dados["nosso_numero"] = $nosso_numero;
			$dados["valor"] = intval($cobranca["valor_final"]);

			$boletos_parametros = controle::instanciaObjeto("cobrancas.parametros_boletos_{$dados_formato["parametros"]["parametros"]}", $dados);
			
			$parametros = funcoes::mescla_arrays($parametros, $boletos_parametros->geraBoletoParametros());

		}

		return $parametros;

	}

	function buscaCompetencia($cobranca) {

		$competencias = array();

		if ($cobranca["assinaturas"]) {
			foreach ($cobranca["assinaturas"] as $assinatura) {
				$competencias[] = substr($assinatura["competencia_inicio"], 0, 7);
			}
		}

		if (!$cobranca["assinaturas"] && $cobranca["ajustes_financeiros"]) {
			foreach ($cobranca["ajustes_financeiros"] as $ajuste_financeiro) {
				$competencias[] = substr($ajuste_financeiro["competencia_inicio"], 0, 7);
			}
		}

		$competencias = array_unique($competencias);

		if (count($competencias) == 1) {
			return $this->formataData(substr($competencias[0], 5, 2)."/".substr($competencias[0], 0, 4));
		}

	}

	function formataData($vencimento) {

		$vencimento = explode("/", $vencimento);

		return substr($this->meses[intval($vencimento[0])], 0, 3)." / ".$vencimento[1];

	}

	function buscaDadosCliente($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cli_nome AS nome,
				cli_cpf_cnpj AS cpf_cnpj,
				clt_id AS cliente_tipo,
				cof_id AS formato_id
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaLote($formato_id, $vencimento) {

		$query = $this->bancodedados->query("
			SELECT
				col_id AS id
			FROM
				tab_cobrancas_lotes
			WHERE
				cof_id = {$formato_id} AND
				col_vencimento = '{$vencimento}'");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {

			return $dados["id"];

		} else {

			$dados_formato = $this->buscaDadosFormato($formato_id);
			$nome = "{$dados_formato["nome"]} - ".$this->meses[intval(date("m", strtotime("$vencimento")))]."/".date("Y", strtotime("$vencimento"));

			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_lotes 
				SET
					col_nome = '{$nome}',
					col_vencimento = '{$vencimento}',
					col_sincronizado = 1,
					cof_id = {$formato_id},
					cla_id = 1");

			return $this->bancodedados->ultimoIdInserido();

		}

	}

	function geraCobrancaDetalhe($cobranca_id, $assinatura_id, $desconto_id, $ajuste_id, $competencia_inicio, $competencia_termino, $descricao, $valor_original, $valor_final, $descricao_pai) {

		$competencia_inicio = $competencia_inicio == 'NULL' ? $competencia_inicio : "'{$competencia_inicio}'";
		$competencia_termino = $competencia_inicio == 'NULL' ? $competencia_termino : "'{$competencia_termino}'";

		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_detalhes
			SET
				coa_id = {$cobranca_id},
				ass_id = {$assinatura_id},
				dec_id = {$desconto_id},
				ajf_id = {$ajuste_id},
				cod_competencia_inicio = {$competencia_inicio},
				cod_competencia_termino = {$competencia_termino},
				cod_descricao = '{$descricao}',
				cod_valor_original = {$valor_original},
				cod_valor_final = {$valor_final},
				cod_pai = {$descricao_pai}");

		return $this->bancodedados->ultimoIdInserido();

	}

	function calculaValorTotalAssinaturas($assinaturas, $condominial) {

		if (!$assinaturas) {
			return 0;
		}

		$valor_total = 0;

		foreach ($assinaturas as $assinatura) {

			$valor_total_assinatura = $assinatura["valor_final"];

			if ($assinatura["descontos_concessoes"]) {
				foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
					if ($desconto_concessao["tipo_id"] != 4 || $condominial) {
						$valor_total_assinatura += $desconto_concessao["valor_final"];
					}
				}
			}

			if ($assinatura["ajustes_financeiros"]) {
				foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
					if (!$ajuste_financeiro["bonificacao"] || $condominial) {
						$valor_total_assinatura += $ajuste_financeiro["valor_final"];
					}
				}
			}

			$valor_total += $valor_total_assinatura > 0 ? $valor_total_assinatura : 0;

		}

		return $valor_total > 0 ? $valor_total : 0;

	}

	function calculaValorTotalAssinatura($assinatura, $condominial) {

		$valor_total = $assinatura["valor_final"];

		if ($assinatura["descontos_concessoes"]) {
			foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
				if ($desconto_concessao["tipo_id"] != 4 || $condominial) {
					$valor_total += $desconto_concessao["valor_final"];
				}
			}
		}

		if ($assinatura["ajustes_financeiros"]) {
			foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
				if (!$ajuste_financeiro["bonificacao"] || $condominial) {
					$valor_total += $ajuste_financeiro["valor_final"];
				}
			}
		}

		return $valor_total;

	}

	function recalculaAssinatura($assinatura, $condominial) {

		$valor_total = $assinatura["valor_final"];

		// Calcula valores acréscimo (ajustes)

		if ($assinatura["ajustes_financeiros"]) {
			foreach ($assinatura["ajustes_financeiros"] as $chave => $ajuste_financeiro) {
				if ($ajuste_financeiro["valor_final"] > 0 && (!$ajuste_financeiro["bonificacao"] || $condominial)) {
					$valor_total += $ajuste_financeiro["valor_final"];
				}
			}
		}

		// Reduz valor descontos/ajustes negativos

		if ($assinatura["descontos_concessoes"]) {
			foreach ($assinatura["descontos_concessoes"] as $chave => $desconto_concessao) {
				if ($desconto_concessao["valor_final"] < 0 && ($desconto_concessao["tipo_id"] != 4 || $condominial)) {
					$valor_total += $desconto_concessao["valor_final"];
					if ($valor_total < 0) {
						$assinatura["descontos_concessoes"][$chave]["valor_final"] -= $valor_total;
						$valor_total = 0;
					}
				}
			}
		}

		// Reduz valor ajustes negativos

		if ($assinatura["ajustes_financeiros"]) {
			foreach ($assinatura["ajustes_financeiros"] as $chave => $ajuste_financeiro) {
				if ($ajuste_financeiro["valor_final"] < 0 && (!$ajuste_financeiro["bonificacao"] || $condominial)) {
					$valor_total += $ajuste_financeiro["valor_final"];
					if ($valor_total < 0) {
						$assinatura["ajustes_financeiros"][$chave]["valor_final"] -= $valor_total;
						$valor_total = 0;
					}
				}
			}
		}

		return $assinatura;

	}

	function calculaValorTotalCobranca($cobranca, $valor_total, $valor_final, $final, $condominial) {

		if ($cobranca["ajustes_financeiros"]) {
			foreach ($cobranca["ajustes_financeiros"] as $ajuste_financeiro) {
				if (!$ajuste_financeiro["bonificacao"] || $condominial) {
					$valor_total += $ajuste_financeiro["valor_final"];
				}
			}
		}

		return $valor_total > 0 || $valor_final > 0 || !$final ? $valor_total : 0;

	}

	function calculaValorBonificacao($cobranca, $final) {

		$valor_bonificacao = 0;

		if ($cobranca["assinaturas"]) {
			
			foreach ($cobranca["assinaturas"] as $assinatura) {

				if ($assinatura["descontos_concessoes"]) {
					foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
						if ($desconto_concessao["tipo_id"] == 4) {
							$valor_bonificacao -= $desconto_concessao["valor_final"];
						}
					}
				}

				if ($assinatura["ajustes_financeiros"]) {
					foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
						if ($ajuste_financeiro["bonificacao"]) {
							$valor_bonificacao -= $ajuste_financeiro["valor_final"];
						}
					}
				}

			}
		
		}

		if ($cobranca["ajustes_financeiros"]) {
			foreach ($cobranca["ajustes_financeiros"] as $ajuste_financeiro) {
				if ($ajuste_financeiro["bonificacao"]) {
					$valor_bonificacao -= $ajuste_financeiro["valor_final"];
				} elseif ($ajuste_financeiro["ajuste_proporcional"] > 0) {
					$valor_bonificacao -= $valor_bonificacao * $ajuste_financeiro["ajuste_proporcional"];
				}
			}
		}

		return $valor_bonificacao > 0 ? $valor_bonificacao : 0;

	}

	function excluiDescontosBonificacao($cobranca) {

		if ($cobranca["assinaturas"]) {

			foreach ($cobranca["assinaturas"] as $chave => $assinatura) {

				// Descontos Concessoes

				$descontos_nao_bonificacao = array();
				if ($assinatura["descontos_concessoes"]) {
					foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
						if ($desconto_concessao["tipo_id"] != 4) {
							$descontos_nao_bonificacao[] = $desconto_concessao;
						}
					}
				}

				$cobranca["assinaturas"][$chave]["descontos_concessoes"] = $descontos_nao_bonificacao;

				// Ajustes Financeiros - Assinatura

				$ajustes_nao_bonificacao = array();
				if ($assinatura["ajustes_financeiros"]) {
					foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
						if (!$ajuste_financeiro["bonificacao"]) {
							$ajustes_nao_bonificacao[] = $ajuste_financeiro;
						}
					}
				}

				$cobranca["assinaturas"][$chave]["ajustes_financeiros"] = $ajustes_nao_bonificacao;

			}

		}

		// Ajustes Financeiros - Cliente

		$ajustes_nao_bonificacao = array();
		if ($cobranca["ajustes_financeiros"]) {
			foreach ($cobranca["ajustes_financeiros"] as $ajuste_financeiro) {
				if (!$ajuste_financeiro["bonificacao"]) {
					$ajustes_nao_bonificacao[] = $ajuste_financeiro;
				}
			}
		}

		$cobranca["ajustes_financeiros"] = $ajustes_nao_bonificacao;

		return $cobranca;

	}

	########################
	### Funções de Busca ###
	########################

	function buscaOrigemCliente($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				clo_id
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");

		return $this->bancodedados->resultado($query);

	}

	function buscaDiasBloqueio($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cli_bloqueio_dias
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");

		return (int) $this->bancodedados->resultado($query);

	}

	function buscaDadosLote($lote_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_lotes.col_id AS id,
				tab_cobrancas_lotes.cop_id AS tipo_id,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m') AS vencimento_mes,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%Y') AS vencimento_ano,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cft_id AS formato_tipo_id,
				tab_cobrancas_formatos.cof_parametros AS parametros
			FROM
				tab_cobrancas_lotes
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_lotes.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				col_id = {$lote_id}");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

		return false;

	}

	function buscaDadosFormato($formato_id) {

		$query = $this->bancodedados->query("
			SELECT
				cof_id AS formato_id,
				cof_nome AS nome,
				cof_parametros AS parametros,
				cof_remessa AS remessa,
				cft_id AS tipo_id
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id = {$formato_id}");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

		return false;

	}

	function buscaParametrosCobranca($cliente_id, $formato_id) {

		$dados_formato = $this->buscaDadosFormato($formato_id);

		$parametros = array();

		$parametros["titular"] = $this->buscaDadosTitular($cliente_id);

		if (in_array($dados_formato["tipo_id"], array(2, 3))) { // Boleto Impresso/Online
			$parametros["endereco_cobranca"] = $this->buscaEnderecoCobranca($cliente_id);
		} elseif (in_array($dados_formato["tipo_id"], array(4))) { // Débito em Conta
			$parametros["dados_cobranca"] = $this->buscaDadosCobranca($cliente_id);
		}

		return $parametros;

	}

	function buscaDadosTitular($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cli_nome AS nome,
				cli_cpf_cnpj AS cpf_cnpj
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$telefones = $this->buscaClienteTelefones($cliente_id);

			if ($telefones) {
				$dados = funcoes::mescla_arrays($dados, $telefones);
			}

			return $dados;

		}

		return false;

	}

	function buscaClienteTelefones($cliente_id) {

		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_contatos_telefones_tipos.ccf_nome AS tipo,
				CONCAT('(', ccl_ddd, ') ', ccl_numero, IF(ccl_ramal <> '', CONCAT(' (Ramal ', ccl_ramal, ')'), '')) AS telefone
			FROM
				tab_clientes_contatos_telefones
				INNER JOIN tab_clientes_contatos_telefones_tipos ON (tab_clientes_contatos_telefones.ccf_id = tab_clientes_contatos_telefones_tipos.ccf_id)
			WHERE
				tab_clientes_contatos_telefones.cli_id = {$cliente_id} AND
				tab_clientes_contatos_telefones.ccf_id > 0");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["tipo"]][] = $dados["telefone"];
		}

		if ($lista) {

			$telefones = array();

			foreach ($lista as $tipo => $numero) {
				$telefones["tel_".strtolower($tipo)] = implode(", ", $numero);
			}

			return $telefones;

		}

	}

	function buscaEnderecoCobranca($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes_cobranca_dados.ccd_numero)) AS numero,
				tab_clientes_cobranca_dados.ccd_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep
			FROM
				tab_clientes_cobranca_dados
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes_cobranca_dados.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes_cobranca_dados.cli_id = {$cliente_id}");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
			return $dados;
		}

		return $this->buscaEndereco($cliente_id);

	}

	function buscaEndereco($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes.cli_numero)) AS numero,
				tab_clientes.cli_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes.cli_id = {$cliente_id}");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
			return $dados;
		}

	}

	function corrigeComplementoCliente($complemento) {

		$complementos_tipos = array(
			"a" => "Apto ",
			"l" => "Loja ",
			"s" => "Sala ",
			"c" => "Casa ",
			"o" => ""
		);

		if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
			$complemento = $complementos_tipos[$complemento[0]].substr($complemento, 1, strlen($complemento));
			return $complemento;
		}

		return false;

	}

	function buscaDadosCobranca($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cli_ccd_debito_operacao AS operacao,
				cli_ccd_debito_agencia AS agencia,
				cli_ccd_debito_conta AS conta_corrente
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");

		return $this->bancodedados->resultadoArray($query);

	}

	function ehInadimplente($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				saj_inicio <= '".date("Y-m-d", strtotime("30 days ago"))."' AND
				cli_id = {$cliente_id} AND
				sac_id = 1 AND
				sas_id = 5");

		return $this->bancodedados->resultado($query);

	}

	function buscaCobrancasCancelamento($cliente_id, $vencimento_inicial, $assinaturas_canceladas, $data_cancelamento) {

		$dados_cliente = $this->buscaDadosCliente($cliente_id);
		$dados_formato = $this->buscaDadosFormato($dados_cliente["formato_id"]);
		$vencimento_ano = substr($vencimento_inicial, 0, 4);
		$vencimento_mes = substr($vencimento_inicial, 5, 2);

		// Busca Cobranças

		$i = 0;
		$cobrancas = array();

		while (($cobranca = $this->buscaCobrancasCliente($cliente_id, $vencimento_mes, $vencimento_ano, $dados_formato["formato_id"], 0, $assinaturas_canceladas, $data_cancelamento)) || ($i < 3)) {

			if ($cobranca) {

				if (!$this->ehCobrancaCancelamento($cobranca, $assinaturas_canceladas)) {
					return $cobrancas;
				}

				$cobrancas["cobrancas"][] = array("referencia" => "Mensalidade", "vencimento" => "{$vencimento_mes}/{$vencimento_ano}", "valor" => $this->formataDinheiro($cobranca["valor_final"]), "valor_reajustado" => $this->formataDinheiro($cobranca["valor_final"]));
				$cobrancas["total"] += $cobranca["valor_final"];

			}

			$vencimento_inicial = date("Y-m", strtotime("{$vencimento_inicial}-01 NEXT MONTH"));
			$vencimento_ano = substr($vencimento_inicial, 0, 4);
			$vencimento_mes = substr($vencimento_inicial, 5, 2);

			$i++;
			
		}

		return $cobrancas;

	}

	function ehCobrancaCancelamento($dados_cobranca, $assinaturas_canceladas) {

		$assinaturas_canceladas = explode(", ", $assinaturas_canceladas);

		if ($dados_cobranca["assinaturas"]) {

			foreach ($dados_cobranca["assinaturas"] as $dados_assinatura) {
				if (in_array($dados_assinatura["id"], $assinaturas_canceladas)) {
					return true;
				}
			}

			return false;

		}

		return true;

	}

	function formataDinheiro($valor, $moeda = 'R$') {

		$valor = preg_replace("/[^0-9]/", "", intval($valor));
		$valor = number_format($valor / 100, 2, ",", ".");

		if ($moeda) $valor = $moeda." ".$valor;

		return $valor;

	}
	
	function buscaCobrancasCliente($cliente_id, $vencimento_mes, $vencimento_ano, $formato_id, $valor_cobranca = 0, $assinaturas_canceladas = null, $data_cancelamento = null, $ultima_cobranca = null, $valor_final = 0, $final = false, $somente_instalacao = false) {

		$condominial = $this->ehCondominial($cliente_id);
		$assinaturas = array();
		$sistema_id = null;

		if (!$somente_instalacao) {

			$query = "
				SELECT
					tab_assinaturas.ass_id AS id,
					tab_planos.pla_nome AS descricao,
					tab_assinaturas.ass_mensalidade AS valor_original,
					tab_assinaturas.ass_data_ativacao AS data_ativacao,
					tab_assinaturas.ass_data_cancelamento AS data_cancelamento,
					tab_assinaturas.pli_id AS sistema_id,
					tab_assinaturas.ass_parametros AS parametros,
					tab_assinaturas.ass_migrada AS migrada_id
				FROM
					tab_assinaturas
					INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
					LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id AND tab_cobrancas_detalhes.cod_cancelado = 0 AND '{$vencimento_ano}{$vencimento_mes}' = DATE_FORMAT(IF(tab_assinaturas.pli_id = 1, DATE_ADD(tab_cobrancas_detalhes.cod_competencia_inicio, INTERVAL 1 MONTH), tab_cobrancas_detalhes.cod_competencia_inicio), '%Y%m'))
				WHERE
					tab_cobrancas_detalhes.cod_id IS NULL AND
					tab_assinaturas.cli_id = {$cliente_id} AND
					tab_assinaturas.ass_mensalidade > 0 AND
					tab_assinaturas.ast_id NOT IN (1, 6) AND
					DATE_FORMAT(IF(tab_assinaturas.pli_id = 1, DATE_ADD(tab_assinaturas.ass_data_ativacao, INTERVAL 1 MONTH), tab_assinaturas.ass_data_ativacao), '%Y%m') <= '{$vencimento_ano}{$vencimento_mes}' AND
					(tab_assinaturas.ass_data_cancelamento IS NULL OR
					(DATE_FORMAT(IF(tab_assinaturas.pli_id = 1, DATE_ADD(tab_assinaturas.ass_data_cancelamento, INTERVAL 1 MONTH), tab_assinaturas.ass_data_cancelamento), '%Y%m') >= '{$vencimento_ano}{$vencimento_mes}' AND
					tab_assinaturas.ass_data_ativacao <> tab_assinaturas.ass_data_cancelamento))
				GROUP BY
					id";

			if ($assinaturas_canceladas) {
				$query = str_replace("tab_assinaturas.ass_data_cancelamento", "IF(tab_assinaturas.ass_id IN ({$assinaturas_canceladas}), '{$data_cancelamento}', tab_assinaturas.ass_data_cancelamento)", $query);
			}

			$query = $this->bancodedados->query($query);

			while (($assinatura = $this->bancodedados->resultadoArray($query)) != false) {

				$assinatura["parametros"] = unserialize($assinatura["parametros"]);

				// Data Referencia

				$referencia_mes = $assinatura["sistema_id"] == 1 ? date("m", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago")) : $vencimento_mes;
				$referencia_ano = $assinatura["sistema_id"] == 1 ? date("Y", strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago")) : $vencimento_ano;

				// Dias Referência

				$mensalidade_dias_total = date("t", strtotime("{$referencia_ano}-{$referencia_mes}-01"));
				$mensalidade_dias_inicio = date("Ym", strtotime($assinatura["data_ativacao"])) == $referencia_ano.$referencia_mes ? date("d", strtotime($assinatura["data_ativacao"])) : "01";
				$mensalidade_dias_termino = $assinatura["data_cancelamento"] && date("Ym", strtotime($assinatura["data_cancelamento"])) == $referencia_ano.$referencia_mes ? date("d", strtotime($assinatura["data_cancelamento"])) : $mensalidade_dias_total;

				// Verifica Assinatura Não-Mensal

				if ($assinatura["parametros"]["periodicidade"] && ($assinatura["parametros"]["periodicidade"] > 1)) {
					$eh_geravel_cobranca_assinatura = !$this->possuiCobrancaDetalheAssinatura($assinatura["id"], $vencimento_mes, $vencimento_ano, $assinatura["sistema_id"], $assinatura["parametros"]["periodicidade"], $ultima_cobranca);
				} else {
					$eh_geravel_cobranca_assinatura = true;
				}

				if ($eh_geravel_cobranca_assinatura && (($mensalidade_dias_ativo = ($mensalidade_dias_termino - $mensalidade_dias_inicio + 1)) > 0)) {

					// Dados Assinatura
					$assinatura["valor_original"] = intval($assinatura["valor_original"]);
					$assinatura["valor_final"] = intval($assinatura["valor_original"] * ($mensalidade_dias_ativo / $mensalidade_dias_total));
					$assinatura["competencia_inicio"] = "{$referencia_ano}-{$referencia_mes}-{$mensalidade_dias_inicio}";
					$assinatura["competencia_termino"] = "{$referencia_ano}-{$referencia_mes}-{$mensalidade_dias_termino}";

					// Busca Descontos Concessoes
					$assinatura["descontos_concessoes"] = $this->buscaDescontosConcessoes($assinatura, $referencia_mes, $referencia_ano);

					// Busca Ajustes Financeiros
					$assinatura["ajustes_financeiros"] = $this->buscaAjustesFinanceirosAssinatura($assinatura, $vencimento_mes, $vencimento_ano, $assinatura["competencia_inicio"], $assinatura["competencia_termino"]);

					// Calcula Valor Total Assinatura
					$valor_total_assinatura = $this->calculaValorTotalAssinatura($assinatura, $condominial);
					
					if ($valor_total_assinatura < 0) {
						$assinatura = $this->recalculaAssinatura($assinatura, $condominial);
					}

					// Remove Bonificação se Total Assinatura <= ZERO
					if ($valor_total_assinatura <= 0) {
						if ($assinatura["descontos_concessoes"]) {
							$descontos_concessoes = array();
							foreach ($assinatura["descontos_concessoes"] as $desconto_concessao) {
								if ($desconto_concessao["tipo_id"] != 4 || $condominial) {
									$descontos_concessoes[] = $desconto_concessao;
								}
							}
							$assinatura["descontos_concessoes"] = $descontos_concessoes;
						}
						if ($assinatura["ajustes_financeiros"]) {
							$ajustes_financeiros = array();
							foreach ($assinatura["ajustes_financeiros"] as $ajuste_financeiro) {
								if (!$ajuste_financeiro["bonificacao"] || $condominial) {
									$ajustes_financeiros[] = $ajuste_financeiro;
								}
							}
							$assinatura["ajustes_financeiros"] = $ajustes_financeiros;
						}
					}

					$assinaturas[] = $assinatura;

				}

				// Sistema Pré/Pós-Pago
				$sistema_id = !$sistema_id || $sistema_id == 1 ? $assinatura["sistema_id"] : $sistema_id;

			}

		}

		$cobranca = array();
		$cobranca["sistema_id"] = $sistema_id;
		$cobranca["assinaturas"] = $assinaturas;

		// Busca Ajustes Financeiros do Cliente

		$valor_total = $this->calculaValorTotalAssinaturas($assinaturas, $condominial);
		$cobranca["ajustes_financeiros"] = $this->buscaAjustesFinanceirosCliente($cliente_id, $valor_total + $valor_final, $vencimento_mes, $vencimento_ano, $assinaturas_canceladas, $data_cancelamento, $final, $sistema_id, $somente_instalacao);
		$estorno_migracao = false;

		if ($cobranca["ajustes_financeiros"]) {
			foreach ($cobranca["ajustes_financeiros"] as $ajuste) {
				if ($ajuste["valor_final"] < 0) {
					$total_descontos_ajuste += $ajuste["valor_final"];
				}
				if (substr($ajuste["descricao"], -18) == "(Estorno Migração)") {
					$estorno_migracao = true;
				}
			}
		}

		$cobranca["parametros"] = $this->buscaParametrosCobranca($cliente_id, $formato_id);
		$cobranca["valor_total"] = $this->calculaValorTotalCobranca($cobranca, $valor_total, $valor_final, $final && !$estorno_migracao, $condominial);

		if (!$condominial) {

			// Calcula Valor Bonificação
			$cobranca["valor_bonificacao"] = round($this->calculaValorBonificacao($cobranca, $final && !$estorno_migracao));

			// Exclui Descontos/Ajustes Bonificação
			$cobranca = $this->excluiDescontosBonificacao($cobranca);

		}

		// Remove Bonificação se total ajustes negativos >= total cobrança

		if (!$estorno_migracao && $total_descontos_ajuste + $valor_total <= 0) {
			$cobranca["valor_bonificacao"] = 0;
		}

		// Calcula Valor da Cobrança

		if (!$ultima_cobranca) {
			if (!$cobranca["assinaturas"] || $this->ehClienteNovo($cliente_id)) {
				$cobranca["valor_cobranca"] = 0;
			} elseif (($cobranca["valor_total"] - $cobranca["valor_bonificacao"]) > 0 && $valor_cobranca > 0) {
				$cobranca["valor_cobranca"] = $valor_cobranca;
			} else {
				$cobranca["valor_cobranca"] = 0;
			}
		}

		if (!array_key_exists("valor_bonificacao", $cobranca)) {
			$cobranca["valor_bonificacao"] = 0;
		}		

		$cobranca["valor_final"] = $cobranca["valor_total"] + $cobranca["valor_cobranca"];

		if (count($cobranca["assinaturas"]) || $cobranca["valor_final"] > 0 || $valor_final > 0) {
			return $cobranca;
		}

		return false;

	}

	function ehCondominial($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cli_pai AS cliente_pai
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");

		return $this->bancodedados->resultado($query);

	}

	function ehClienteNovo($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.cli_id = {$cliente_id} AND
				tab_assinaturas.ass_data_ativacao >= '2008-01-01' AND
				tab_assinaturas.ast_id NOT IN (1, 6) AND
				tab_assinaturas.ass_migrada IS NULL AND
				tab_assinaturas.ass_pai IS NULL AND
				tab_planos.pls_id NOT IN (21)");

		return $this->bancodedados->resultado($query);

	}
	
	function possuiCobrancaDetalheAssinatura($assinatura_id, $vencimento_mes, $vencimento_ano, $sistema_id, $periodicidade, $ultima_cobranca = null) {

		$data_final = $sistema_id == 1 ? strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 MONTH AGO") : strtotime("{$vencimento_ano}-{$vencimento_mes}-01");
		$data_inicial = strtotime(date("Y-m-d", $data_final)." ".($periodicidade - 1)." MONTH AGO");

		// Verifica Ultima Cobrança Sendo Gerada

		if ($ultima_cobranca) {
			foreach ($ultima_cobranca["assinaturas"] as $assinatura) {
				if ($assinatura["id"] == $assinatura_id) {
					if (strtotime($assinatura["competencia_inicio"]) >= $data_inicial && strtotime($assinatura["competencia_inicio"]) <= $data_final) {
						return true;
					}
				}
			}
		}

		// Verifica Cobrança Gerada

		$query = $this->bancodedados->query("
			SELECT
				COUNT(ass_id)
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
			WHERE
				coa_pai IS NULL AND
				cot_id NOT IN (6, 12) AND
				ass_id = {$assinatura_id} AND
				cod_competencia_inicio BETWEEN '".date("Y-m-d", $data_inicial)."' AND '".date("Y-m-t", $data_final)."'");

		return $this->bancodedados->resultado($query);

	}

	function buscaAjustesFinanceirosCliente($cliente_id, $valor_total, $vencimento_mes, $vencimento_ano, $assinaturas_canceladas = null, $data_cancelamento = null, $final = false, $sistema_id = null, $somente_instalacao = false) {

		$competencia = strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 month ago");
		$competencia_mes = date("m", $competencia);
		$competencia_ano = date("Y", $competencia);

		// Busca Ajustes Financeiros

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_ajustes_financeiros.ajf_id AS id,
				tab_ajustes_financeiros.aft_id AS tipo,
				tab_ajustes_financeiros.ajf_valor AS valor_original,
				tab_ajustes_financeiros.ajf_valor_tipo AS valor_tipo,
				tab_ajustes_financeiros.ajf_competencia_termino AS data_termino,
				tab_ajustes_financeiros.ass_id AS assinatura_id,
				tab_ajustes_financeiros.ajf_descricao_custom AS descricao_custom,
				tab_ajustes_financeiros_categorias.custom AS custom,
				tab_ajustes_financeiros_categorias.afc_nome AS descricao,
				tab_ajustes_financeiros_categorias.afc_bonificacao AS bonificacao,
				tab_assinaturas.ass_data_cancelamento AS data_cancelamento,
				tab_assinaturas.pli_id AS sistema_id,
				tab_planos.pla_nome AS plano
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
				LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id AND tab_cobrancas_detalhes.cod_cancelado = 0 AND '{$competencia_ano}{$competencia_mes}' BETWEEN DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%Y%m') AND DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_termino, '%Y%m'))
				LEFT OUTER JOIN tab_assinaturas ON (tab_ajustes_financeiros.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE".($final ? "
				(tab_cobrancas_detalhes.cod_id IS NULL) AND" : "
				(tab_cobrancas_detalhes.cod_id IS NULL OR (tab_ajustes_financeiros.aft_id = 2 AND ajf_valor <= 1)) AND")."
				(tab_ajustes_financeiros.afs_id IN (5, 7, 8)) AND".($somente_instalacao ? "
				(tab_ajustes_financeiros.afc_id = 19) AND" : "
				(tab_ajustes_financeiros.afc_id NOT IN (11)) AND")."
				(tab_ajustes_financeiros.cli_id = {$cliente_id}) AND
				(tab_ajustes_financeiros.ass_id IS NULL OR tab_ajustes_financeiros.afc_id = 13) AND
				(tab_ajustes_financeiros.ajf_competencia_inicio <= '{$competencia_ano}-{$competencia_mes}-01') AND
				(tab_ajustes_financeiros.ajf_competencia_termino >= '{$competencia_ano}-{$competencia_mes}-01' OR tab_ajustes_financeiros.ajf_competencia_termino IS NULL)
			ORDER BY
				tab_ajustes_financeiros_categorias.afc_bonificacao,
				tab_ajustes_financeiros.ajf_competencia_inicio");

		if ($this->bancodedados->linhas($query)) {

			$ajustes_financeiros = array();
			$valor_total_ajustes = 0;
			$existe_estorno_migracao = false;
			$ajuste_dias_mes = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				if ($dados["assinatura_id"]) {

					// Estorno Migração
				
					$dados["plano"] = preg_replace("/\\([^\\)]*\\)/", "", $dados["plano"]);
					$dados["plano"] = trim(preg_replace("/\\[[^\\]]*\\]/", "", $dados["plano"]));
					$dados["descricao"] = "{$dados["plano"]} (Estorno Migração)";

					$mensalidade = $this->buscaMensalidadeAssinatura($dados["assinatura_id"], $competencia_ano, $competencia_mes);
					$bonificacao = $this->buscaBonificacaoAssinatura($dados["assinatura_id"], $valor_total, $competencia_ano, $competencia_mes);

					$mensalidade -= $bonificacao;

					if ($dados["valor_tipo"] == 1) {
						$dados["valor_original"] = intval($dados["valor_original"]);
					} elseif ($dados["valor_tipo"] == 2) {
						$dados["valor_original"] = intval(round($mensalidade * $dados["valor_original"]));
					} elseif ($dados["valor_tipo"] == 3) {
						$dados["valor_original"] = intval(round(($mensalidade / $ajuste_dias_mes) * $dados["valor_original"]));
					} else {
						$dados["valor_original"] = intval(round(($mensalidade / ($ajuste_dias_mes * 24)) * $dados["valor_original"]));
					}

					$dados["competencia_inicio"] = date("Y-m-d", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
					$dados["competencia_termino"] = date("Y-m-d", strtotime("{$competencia_ano}-{$competencia_mes}-{$ajuste_dias_mes}"));

					$existe_estorno_migracao = true;

				} else {

					$dados["descricao"] = "Ajuste Financeiro ({$dados["descricao"]})";
					if ($dados["valor_tipo"] == 1) {
						$dados["valor_original"] = intval($dados["valor_original"]);
					} elseif ($dados["valor_tipo"] == 2) {
						$dados["ajuste_proporcional"] = $dados["valor_original"];
						$dados["valor_original"] = intval(round($valor_total * $dados["valor_original"]));
					} elseif ($dados["valor_tipo"] == 3) {
						$dados["ajuste_proporcional"] = $dados["valor_original"] / $ajuste_dias_mes;
						$dados["valor_original"] = intval(round(($valor_total / $ajuste_dias_mes) * $dados["valor_original"]));
					} else {
						$dados["ajuste_proporcional"] = $dados["valor_original"] / ($ajuste_dias_mes * 24);
						$dados["valor_original"] = intval(round(($valor_total / ($ajuste_dias_mes * 24)) * $dados["valor_original"]));
					}
					$dados["competencia_inicio"] = "{$competencia_ano}-{$competencia_mes}-01";
					$dados["competencia_termino"] = "{$competencia_ano}-{$competencia_mes}-{$ajuste_dias_mes}";

				}
				
				if ($dados["tipo"] <= 2) {
					$dados["valor_original"] *= $dados["tipo"] == 1 ? 1 : -1;
				} else {
					$dados["valor_original"] = $dados["valor_original"] - $valor_total;
				}
				
				$dados["valor_final"] = $dados["valor_original"];

				if($dados["custom"]) {
					$dados["descricao"] = $dados["descricao_custom"];
				}

				unset($dados["tipo"]);
				unset($dados["valor_tipo"]);

				$valor_total_ajustes += $dados["valor_final"];

				$ajustes_financeiros[] = $dados;
				
			}

			// Trata valor_final

			if ($valor_total + $valor_total_ajustes < 0 && $final && !$existe_estorno_migracao) {

				$valor_total_cobranca = $valor_total;

				// Ajustes Positivos

				foreach ($ajustes_financeiros as $chave => $ajuste_financeiro) {
					if ($ajuste_financeiro["valor_final"] > 0) {
						$valor_total_cobranca += $ajuste_financeiro["valor_final"];
					}
				}

				// Ajustes Negativos

				foreach ($ajustes_financeiros as $chave => $ajuste_financeiro) {
					if ($ajuste_financeiro["valor_final"] < 0) {
						if ($valor_total_cobranca + $ajuste_financeiro["valor_final"] > 0) {
							$valor_total_cobranca += $ajuste_financeiro["valor_final"];
						} else {
							$ajustes_financeiros[$chave]["valor_final"] = -$valor_total_cobranca;
							$valor_total_cobranca = 0;
						}
					}
				}

			}

			return $ajustes_financeiros;

		}

		return false;

	}

	function buscaMensalidadeAssinatura($assinatura_id, $competencia_ano, $competencia_mes) {

		$mensalidade = 0;
		$inicio = "{$competencia_ano}-{$competencia_mes}-01";
		$termino = date("Y-m-t", strtotime($inicio));
	
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_detalhes.cod_id AS id,
				tab_cobrancas_detalhes.cod_valor_final AS valor_final
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
			WHERE
				tab_cobrancas_detalhes.ass_id = {$assinatura_id} AND
				tab_cobrancas_detalhes.cod_competencia_inicio >= '{$inicio}' AND
				tab_cobrancas_detalhes.cod_competencia_termino <= '{$termino}' AND
				tab_cobrancas.coa_pai IS NULL AND
				tab_cobrancas.cot_id NOT IN (6, 12)
			ORDER BY
				tab_cobrancas_detalhes.cod_competencia_inicio");

		while (($dados = $this->bancodedados->resultadoArray($query)) != null) {
			$mensalidade += intval($dados["valor_final"]) + intval($this->buscaValorDetalhesFilhos($dados["id"]));
		}
		
		return $mensalidade;

	}

	function buscaValorDetalhesFilhos($detalhe_id) {

		$query = $this->bancodedados->query("
			SELECT
				SUM(cod_valor_final)
			FROM
				tab_cobrancas_detalhes
			WHERE
				cod_pai = {$detalhe_id}");

		return $this->bancodedados->resultado($query);

	}

	function buscaBonificacaoAssinatura($assinatura_id, $mensalidade, $competencia_ano, $competencia_mes) {

		$bonificacao = 0;
		$inicio = "{$competencia_ano}-{$competencia_mes}-01";
		$termino = date("Y-m-t", strtotime($inicio));
		$dias_mes = date("t", strtotime($inicio));

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos_concessoes.dec_mensalidade AS valor_original,
				DATEDIFF(
					IF(tab_descontos_concessoes.dec_data_termino <= '{$termino}', tab_descontos_concessoes.dec_data_termino, '{$termino}'),
					IF(tab_descontos_concessoes.dec_data_inicio >= '{$inicio}', tab_descontos_concessoes.dec_data_inicio, '{$inicio}')
				) + 1 AS dias
			FROM
				tab_descontos_concessoes
			WHERE
				(tab_descontos_concessoes.det_id = 4) AND
				(tab_descontos_concessoes.dec_mensalidade > 0) AND
				(tab_descontos_concessoes.ass_id = {$assinatura_id}) AND
				(tab_descontos_concessoes.dec_data_inicio <= '{$termino}') AND
				(tab_descontos_concessoes.dec_data_termino IS NULL OR tab_descontos_concessoes.dec_data_termino >= '{$inicio}')");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$bonificacao += intval(($dados["valor_original"] <= 1 ? $dados["valor_original"] * $mensalidade : $dados["valor_original"]) / $dias_mes * $dados["dias"]);
		}

		return $bonificacao;

	}

	function buscaAjustesFinanceirosAssinatura($assinatura, $vencimento_mes, $vencimento_ano, $competencia_inicio, $competencia_termino) {

		$competencia = strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 MONTH AGO");
		$competencia_mes = date("m", $competencia);
		$competencia_ano = date("Y", $competencia);

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_ajustes_financeiros.ajf_id AS id,
				tab_ajustes_financeiros.aft_id AS tipo,
				tab_ajustes_financeiros.ajf_valor AS valor_original,
				tab_ajustes_financeiros.ajf_valor_tipo AS valor_tipo,
				tab_ajustes_financeiros.ajf_descricao_custom AS descricao_custom,
				tab_ajustes_financeiros_categorias.custom AS custom,
				tab_ajustes_financeiros_categorias.afc_nome AS descricao,
				tab_ajustes_financeiros_categorias.afc_bonificacao AS bonificacao
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
			WHERE
				(tab_ajustes_financeiros.afs_id IN (5, 7, 8)) AND
				(tab_ajustes_financeiros.afc_id NOT IN (11)) AND
				(tab_ajustes_financeiros.ass_id = {$assinatura["id"]}) AND
				(tab_ajustes_financeiros.ajf_competencia_inicio <= '{$competencia_ano}-{$competencia_mes}-01') AND
				(tab_ajustes_financeiros.ajf_competencia_termino >= '{$competencia_ano}-{$competencia_mes}-01' OR tab_ajustes_financeiros.ajf_competencia_termino IS NULL)");

		if ($this->bancodedados->linhas($query)) {

			$ajustes_financeiros = array();
			$ajuste_dias_mes = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));

			while (($ajuste_financeiro = $this->bancodedados->resultadoArray($query)) != false) {

				$ajuste_financeiro["descricao"] = "Ajuste Financeiro ({$ajuste_financeiro["descricao"]})";
				
				if ($ajuste_financeiro["valor_tipo"] == 1) {
					$ajuste_financeiro["valor_original"] = intval($ajuste_financeiro["valor_original"]);
				} elseif ($ajuste_financeiro["valor_tipo"] == 2) {
					$ajuste_financeiro["valor_original"] = intval(round($assinatura["valor_original"] * $ajuste_financeiro["valor_original"]));
				} elseif ($ajuste_financeiro["valor_tipo"] == 3) {
					$ajuste_financeiro["valor_original"] = intval(round($assinatura["valor_original"] / $ajuste_dias_mes * $ajuste_financeiro["valor_original"]));
				} else {
					$ajuste_financeiro["valor_original"] = intval(round($assinatura["valor_original"] / $ajuste_dias_mes / 24 * $ajuste_financeiro["valor_original"]));
				}
				
				$ajuste_financeiro["valor_original"] *= $ajuste_financeiro["tipo"] == 1 ? 1 : -1;

				$ajuste_financeiro["valor_final"] = $ajuste_financeiro["valor_original"];
				$ajuste_financeiro["competencia_inicio"] = $competencia_inicio;
				$ajuste_financeiro["competencia_termino"] = $competencia_termino;

				if($ajuste_financeiro["custom"]) {
					$ajuste_financeiro["descricao"] = $ajuste_financeiro["descricao_custom"];
				}

				unset($ajuste_financeiro["tipo"]);
				unset($ajuste_financeiro["valor_tipo"]);

				$ajustes_financeiros[] = $ajuste_financeiro;

			}

			return $ajustes_financeiros;

		}

		return false;

	}

	function buscaDescontosConcessoes($assinatura, $competencia_mes, $competencia_ano) {

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos_concessoes.dec_mensalidade AS valor_original,
				tab_descontos_concessoes.dec_data_termino AS data_termino,
				tab_descontos_tipos.det_nome AS tipo,
				tab_descontos_tipos.det_id AS tipo_id,
				tab_descontos.des_nome AS descricao
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos.det_id = tab_descontos_tipos.det_id)
			WHERE
				(dcs_id >= 5) AND
				(dec_mensalidade > 0) AND
				(ass_id = {$assinatura["id"]}) AND
				(dec_data_termino IS NULL OR dec_data_termino >= '{$competencia_ano}-{$competencia_mes}-01')
			ORDER BY
				tab_descontos_concessoes.det_id DESC");

		if ($this->bancodedados->linhas($query)) {

			$descontos = array();
			$desconto_dias_total = date("t", strtotime("{$competencia_ano}-{$competencia_mes}-01"));
			$desconto_dias_inicio = date("Ym", strtotime($assinatura["data_ativacao"])) == $competencia_ano.$competencia_mes ? date("d", strtotime($assinatura["data_ativacao"])) : "01";
			$desconto_dias_termino_assinatura = $assinatura["data_cancelamento"] && date("Ym", strtotime($assinatura["data_cancelamento"])) == $competencia_ano.$competencia_mes ? date("d", strtotime($assinatura["data_cancelamento"])) : $desconto_dias_total;

			while (($desconto_atual = $this->bancodedados->resultadoArray($query)) != false) {

				if ($desconto_atual["data_termino"] && date("Ym", strtotime($desconto_atual["data_termino"])) == $competencia_ano.$competencia_mes && date("d", strtotime($desconto_atual["data_termino"])) < $desconto_dias_termino_assinatura) {
					$desconto_dias_termino = date("d", strtotime($desconto_atual["data_termino"]));
				} else {
					$desconto_dias_termino = $desconto_dias_termino_assinatura;
				}

				$desconto_atual["descricao"] = "Desconto {$desconto_atual["tipo"]} ({$desconto_atual["descricao"]})";
				$desconto_dias_ativo = ($desconto_dias_termino - $desconto_dias_inicio) + 1;
				$desconto_atual["valor_original"] = -intval($desconto_atual["valor_original"] <= 1 ? $desconto_atual["valor_original"] * $assinatura["valor_original"] : $desconto_atual["valor_original"]);
				$desconto_atual["valor_final"] = intval($desconto_atual["valor_original"] * ($desconto_dias_ativo / $desconto_dias_total));
				$desconto_atual["competencia_inicio"] = "{$competencia_ano}-{$competencia_mes}-{$desconto_dias_inicio}";
				$desconto_atual["competencia_termino"] = "{$competencia_ano}-{$competencia_mes}-{$desconto_dias_termino}";

				unset($desconto_atual["data_termino"]);

				$descontos[] = $desconto_atual;

			}

			return $descontos;

		}

		return false;

	}

	function buscaStatusCliente($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				cls_id
			FROM
				tab_clientes
			WHERE
				cli_id = {$cliente_id}");
		
		return $this->bancodedados->resultado($query);

	}

	function ehGeravelCobranca($cliente_id, $vencimento_mes, $vencimento_ano) {

		$dias_mes = date("t", strtotime("{$vencimento_ano}-{$vencimento_mes}-01"));

		// Verifica existência de assinatura ativa no período

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_assinaturas.ass_id)
			FROM
				tab_assinaturas
				LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id AND tab_cobrancas_detalhes.cod_cancelado = 0)
			WHERE
				tab_assinaturas.cli_id = {$cliente_id} AND
				tab_assinaturas.ass_mensalidade > 0 AND
				tab_assinaturas.ast_id NOT IN (1, 6) AND
				IF(tab_assinaturas.pli_id = 1, DATE_ADD(tab_assinaturas.ass_data_ativacao, INTERVAL 1 MONTH), tab_assinaturas.ass_data_ativacao) <= '{$vencimento_ano}-{$vencimento_mes}-{$dias_mes}' AND
				(tab_assinaturas.ass_data_cancelamento IS NULL OR
				(tab_assinaturas.ass_data_ativacao <> tab_assinaturas.ass_data_cancelamento AND
				IF(tab_assinaturas.pli_id = 1, DATE_ADD(tab_assinaturas.ass_data_cancelamento, INTERVAL 1 MONTH), tab_assinaturas.ass_data_cancelamento) >= '{$vencimento_ano}-{$vencimento_mes}-01'))
			GROUP BY
				tab_assinaturas.cli_id
			HAVING
				SUM(IF('{$vencimento_ano}{$vencimento_mes}' = DATE_FORMAT(IF(tab_assinaturas.pli_id = 1, DATE_ADD(tab_cobrancas_detalhes.cod_competencia_inicio, INTERVAL 1 MONTH), tab_cobrancas_detalhes.cod_competencia_inicio), '%Y%m'), 1, 0)) = 0");

		if ($this->bancodedados->resultado($query) > 0) {
			return true;
		}

		$competencia = strtotime("{$vencimento_ano}-{$vencimento_mes}-01 1 MONTH AGO");
		$competencia_mes = date("m", $competencia);
		$competencia_ano = date("Y", $competencia);

		// Busca Ajustes Financeiros

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_ajustes_financeiros.ajf_id)
			FROM
				tab_ajustes_financeiros
				LEFT OUTER JOIN tab_cobrancas_detalhes ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id AND '{$competencia_ano}{$competencia_mes}' BETWEEN DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_inicio, '%Y%m') AND DATE_FORMAT(tab_cobrancas_detalhes.cod_competencia_termino, '%Y%m'))
			WHERE
				tab_cobrancas_detalhes.cod_id IS NULL AND
				tab_ajustes_financeiros.afs_id IN (5, 7, 8) AND
				tab_ajustes_financeiros.cli_id = {$cliente_id} AND
				tab_ajustes_financeiros.ajf_valor_tipo = 1 AND
				tab_ajustes_financeiros.ass_id IS NULL AND
				tab_ajustes_financeiros.ajf_competencia_inicio <= '{$competencia_ano}-{$competencia_mes}-01' AND
				(ajf_competencia_termino >= '{$competencia_ano}-{$competencia_mes}-01' OR ajf_competencia_termino IS NULL)
			ORDER BY
				ajf_competencia_inicio");

		return $this->bancodedados->resultado($query) > 0;

	}

}

?>