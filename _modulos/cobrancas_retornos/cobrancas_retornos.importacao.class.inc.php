<?php

class cobrancas_retornos_importacao {

	var $bancodedados;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}

	################
	### Importar ###
	################

	function importar($formato_id, $conteudo) {
		
		$dados_formato = $this->buscaDadosFormato($formato_id);
		
		if ($dados_formato["parametros"]["publicacao"] && method_exists($this, $dados_formato["parametros"]["publicacao"])) {
			return $this->{$dados_formato["parametros"]["publicacao"]}($dados_formato, $conteudo);
		}
	
	}

	###################
	### Busca Itens ###
	###################

	function buscaDadosFormato($formato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cof_id AS formato_id,
				cof_nome AS nome,
				cof_parametros AS parametros,
				cft_id AS tipo_id
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id = $formato_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
	
	}

	function buscaCobrancaPorCodigo($codigo, $formato_id = null) {

		$query = $this->bancodedados->query("
			SELECT
				coa_id
			FROM
				tab_cobrancas
			WHERE
				coa_codigo = '$codigo'".($formato_id ? " AND
				cof_id = $formato_id" : "")."
			ORDER BY
				coa_data_vencimento DESC,
				cot_id
			LIMIT 1");

		return intval($this->bancodedados->resultado($query), 10);

	}

	function buscaCobranca($cliente_id, $ano_vencimento, $mes_vencimento, $formato_id) {

		$query = $this->bancodedados->query("
			SELECT
				coa_id
			FROM
				tab_cobrancas
			WHERE
				cof_id = $formato_id AND
				cli_id = $cliente_id AND
				DATE_FORMAT(coa_data_vencimento, '%Y-%m') = '$ano_vencimento-$mes_vencimento'
			ORDER BY
				cot_id
			LIMIT 1");

		return intval($this->bancodedados->resultado($query), 10);

	}

	function buscaCobrancaVencimento($cliente_id, $vencimento, $formato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id
			FROM
				tab_cobrancas
			WHERE
				cof_id = $formato_id AND
				cli_id = $cliente_id AND
				coa_data_vencimento BETWEEN '".date("Y-m-d", strtotime("$vencimento - 5 days"))."' AND '".date("Y-m-d", strtotime("$vencimento + 5 days"))."'
			ORDER BY
				cot_id
			LIMIT 1");
		
		return intval($this->bancodedados->resultado($query), 10);
	
	}

	function buscaCobrancaMesmaConta($codigo, $cliente_original, $vencimento, $formato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id,
				coa_valor_original - coa_valor_bonificacao AS valor
			FROM
				tab_clientes
				INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
			WHERE
				tab_clientes.cli_ccd_debito_codigo = '$codigo' AND
				tab_clientes.cli_id != $cliente_original AND
				tab_cobrancas.cof_id = $formato_id AND
				tab_cobrancas.coa_data_vencimento BETWEEN '".date("Y-m-d", strtotime("$vencimento - 5 days"))."' AND '".date("Y-m-d", strtotime("$vencimento + 5 days"))."'
			ORDER BY
				cot_id
			LIMIT 1");

		return $this->bancodedados->resultadoArray($query);
	
	}

	#####################
	### HSBC - Boleto ###
	#####################

	function importarHSBCBoleto($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoHSBCBoleto($conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = "20".substr($conteudo, 98, 2)."-".substr($conteudo, 96, 2)."-".substr($conteudo, 94, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", trim($conteudo));
		
		for ($i = 1; $i < sizeof($conteudo) - 1; $i++) {
			if (substr($conteudo[$i], 37, 13)) {
				
				$registro = array();
				
				$registro["id"] = $this->buscaCobrancaPorCodigo(substr($conteudo[$i], 37, 13));
				$registro["data_processamento"] = "20".substr($conteudo[$i], 114, 2)."-".substr($conteudo[$i], 112, 2)."-".substr($conteudo[$i], 110, 2);
				$registro["data_credito"] = "20".substr($conteudo[$i], 86, 2)."-".substr($conteudo[$i], 84, 2)."-".substr($conteudo[$i], 82, 2);
				$registro["valor_juro"] = intval(substr($conteudo[$i], 266, 13), 10);
				$registro["valor_desconto"] = intval(substr($conteudo[$i], 240, 13), 10);
				$registro["valor_total"] = intval(substr($conteudo[$i], 253, 13), 10);
				$registro["erro"] = "";
				
				$dados["registros"][] = $registro;
				
			}
		}
		
		return $dados;
	
	}

	function validaConteudoHSBCBoleto($conteudo) {
		
		if (substr($conteudo, 0, 26) != "02RETORNO01COBRANCA CNR   ") return false;
		if (substr($conteudo, 79, 15) != "HSBC           ") return false;
		if (substr($conteudo, 108, 10) != "0003144313") return false;
		
		return true;
	
	}

	################################
	### Banco do Brasil - Boleto ###
	################################

	function importarBBBoleto($dados_formato, $conteudo) {
		
		$cabecalho = array();
		$registrosT = array();
		$registrosU = array();
		
		// Validação
		if (!preg_match("/^([0-9]{3})(0000)(0)([ ]{9})([12])([0-9]{14})([0-9 ]{20})([0-9]{5})([0-9a-z]{1})([0-9]{12})([0-9a-z]{1})([0 ])([0-9a-z ]{30})([0-9a-z ]{30})([ ]{10})(2)([0-9]{8})([0-9]{6})([0-9]{6})([0-9]{3})(00000)([0-9a-z ]{20})([0-9a-z ]{20})([ ]{11})([ ]{3})([ ]{2})([ ]{10})/i", $conteudo, $cabecalho)) return false;
		if ($cabecalho[1] != "001") return false; // 001 - Banco do Brasil
		if ($cabecalho[6] != $dados_formato["parametros"]["cnpj"]["valor"]) return false; // CNPJ
		if (intval(substr($cabecalho[7], 0, 9)) != intval($dados_formato["parametros"]["cedente"]["valor"], 10)) return false; // Versão do Arquivo
		if ($cabecalho[20] != "030") return false; // Versão do Arquivo
		if (!preg_match_all("/([0-9]{3})([0-9]{4})(3)([0-9]{5})(T)( )([0-9]{2})([0-9]{5})([0-9x])([0-9]{12})([0-9x])([0-9x ])([0-9a-z ]{20})([0-9])([0-9a-z ]{15})([0-9]{8})([0-9]{15})([0-9]{3})([0-9]{5})([0-9x])([0-9a-z ]{25})([0-9]{2})([0-9])([0-9]{15})([0-9a-z ]{40})([0-9]{10})([0-9]{15})([0-9a-z ]{10})([0-9a-z ]{17})/i", $conteudo, $registrosT)) return false;
		if (!preg_match_all("/([0-9]{3})([0-9]{4})(3)([0-9]{5})(U)( )([0-9]{2})([0-9]{15})([0-9]{15})([0-9]{15})([0-9]{15})([0-9]{15})([0-9]{15})([0-9]{15})([0-9]{15})([0-9]{8})([0-9]{8})([0-9 ]{4})([0-9 ]{8})([0-9]{15})([0-9a-z ]{30})([0-9 ]{3})([0-9 ]{20})([0-9a-z ]{7})/i", $conteudo, $registrosU)) return false;
		if (count($registrosT[0]) != count($registrosU[0])) return false;
		
		// Data Geração
		$dados = array();
		$dados["data_geracao"] = $this->formataDado($cabecalho[17], "([0-9]{2})([0-9]{2})([0-9]{4})", "\$dado[3]-\$dado[2]-\$dado[1]");
		$dados["registros"] = array();
		
		$duplicados = array();
		
		for ($i = 0; $i < count($registrosT[0]); $i++) {
			
			$codigo = intval($registrosT[13][$i]);
			
			$registro = array();
			
			$registro["id"] = $this->buscaCobrancaPorCodigo($codigo, $dados_formato["formato_id"]);
			if ($duplicados[$registro["id"]]) {
				$registro["duplicado"] = true;
			} else {
				$registro["duplicado"] = false;
			}
			
			$duplicados[$registro["id"]] = $registro["id"];

			if ($registro["id"] && $registrosU) {
				
				$registro["data_processamento"] = $this->formataDado($registrosU[16][$i], "([0-9]{2})([0-9]{2})([0-9]{4})", "\$dado[3]-\$dado[2]-\$dado[1]");
				$registro["data_credito"] = $this->formataDado($registrosU[17][$i], "([0-9]{2})([0-9]{2})([0-9]{4})", "\$dado[3]-\$dado[2]-\$dado[1]");
				$registro["valor_juro"] = intval($registrosU[8][$i], 10);
				$registro["valor_desconto"] = intval($registrosU[9][$i], 10) + intval($registrosU[10][$i], 10);
				$registro["valor_total"] = intval($registrosU[12][$i], 10);
				$registro["valor_iof"] = intval($registrosU[11][$i], 10);
				$registro["valor_liquido"] = intval($registrosU[13][$i], 10);
				$registro["valor_despesas"] = intval($registrosU[14][$i], 10);
				$registro["valor_creditos"] = intval($registrosU[15][$i], 10);
				$registro["erro"] = "";
				
				$dados["registros"][] = $registro;
				
			}
			
		}
		
		return $dados;
	
	}

	function formataDado($dado, $origem, $destino) {
		
		if (preg_match("/$origem/", $dado, $dado)) {
			eval("\$dado = \"$destino\";");
		}
		
		return $dado;
		
	}

	###########################################
	### Banco do Brasil - Débito Automático ###
	###########################################

	function importarBBDebito($dados_formato, $conteudo) {
		
		$dados = array();
		
		// Valida Conteudo
		if (!$this->validaConteudoBBDebito($conteudo)) return false;
		
		// Data de Geração
		$dados["data_geracao"] = substr($conteudo, 65, 4)."-".substr($conteudo, 69, 2)."-".substr($conteudo, 71, 2);
		$dados["registros"] = array();		
		
		// Registros
		$conteudo = preg_split("/\r?\n/", $conteudo);
		
		for ($i = 1; $i < sizeof($conteudo) - 1; $i++) {
			if (substr($conteudo[$i], 0, 1) == "F") {
				
				$registro = array();
				
				$registro["id"] = $this->buscaCobranca(intval(substr($conteudo[$i], 1, 10), 10), substr($conteudo[$i], 44, 4), substr($conteudo[$i], 48, 2), $dados_formato["formato_id"]);
				$registro["data_processamento"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
				$registro["data_credito"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
				$registro["valor_juro"] = 0;
				$registro["valor_desconto"] = 0;
				$registro["valor_total"] = intval(substr($conteudo[$i], 61, 6), 10);
				$registro["erro"] = $this->buscaErroBBDebito(intval(substr($conteudo[$i], 67, 2), 10));
				
				$dados["registros"][] = $registro;
				
			}
		}
		
		return $dados;
		
	}

	function importarBBDebito2($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoBBDebito($conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo, 65, 4)."-".substr($conteudo, 69, 2)."-".substr($conteudo, 71, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", $conteudo);
		
		for ($i = 1; $i < sizeof($conteudo) - 1; $i++) {
			if (substr($conteudo[$i], 0, 1) == "F") {
				
				$valor_cobranca = intval(substr($conteudo[$i], 61, 6), 10);
				$cobranca = $this->buscaCobrancaMesmaConta(substr($conteudo[$i], 1, 25), intval(substr($conteudo[$i], 2, 8), 10), substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2), $dados_formato["formato_id"]);
				
				if ($cobranca) {
					
					$registro = array();
					
					$registro["id"] = $cobranca["id"];
					$registro["data_processamento"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
					$registro["data_credito"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
					$registro["valor_juro"] = 0;
					$registro["valor_desconto"] = 0;
					$registro["valor_total"] = $cobranca["valor"];
					$registro["erro"] = $this->buscaErroBBDebito(intval(substr($conteudo[$i], 67, 2), 10));
					
					if ($valor_cobranca > $cobranca["valor"] || $registro["erro"]) {
						$dados["registros"][] = $registro;
						if ($valor_cobranca > $cobranca["valor"]) {
							$valor_cobranca -= $cobranca["valor"];
						}
					}
					
				}

				$registro = array();
				
				$registro["id"] = $this->buscaCobrancaVencimento(intval(substr($conteudo[$i], 2, 8), 10), substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2), $dados_formato["formato_id"]);
				$registro["data_processamento"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
				$registro["data_credito"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
				$registro["valor_juro"] = 0;
				$registro["valor_desconto"] = 0;
				$registro["valor_total"] = $valor_cobranca;
				$registro["erro"] = $this->buscaErroBBDebito(intval(substr($conteudo[$i], 67, 2), 10));
				
				$dados["registros"][] = $registro;
			
			}
		}
		
		return $dados;
		
	}

	function validaConteudoBBDebito($conteudo) {
		
		if (substr($conteudo, 42, 23) != "001BANCO DO BRASIL S.A.") return false;
		if (substr($conteudo, 79, 19) != "04DEBITO AUTOMATICO") return false;
		
		return true;
		
	}

	function buscaErroBBDebito($codigo) {
		
		$erro = array(
			 0 => "",
			 1 => "Insuficiência de Fundos",
			 2 => "Conta corrente não cadastrada",
			 4 => "Outras restrições",
			10 => "Agência em regime de encerramento",
			12 => "Valor inválido",
			13 => "Data de lançamento inválida",
			14 => "Agência inválida",
			15 => "DAC da conta corrente inválido",
			18 => "Data de débito anterior ao processamento",
			30 => "Sem contrato de débito automático",
			96 => "Manutenção do cadastro",
			97 => "Cancelamento - não encontrado",
			98 => "Cancelamento - não efetuado, fora de tempo hábil",
			99 => "Cancelamento - cancelado conforme solicitação"
		);
		
		if (array_key_exists($codigo, $erro)) {
			return $erro[$codigo];
		} else {
			return "Erro desconhecido";
		}
	
	}

	##########################
	### Santander - Boleto ###
	##########################

	function importarSantanderBoleto($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoSantanderBoleto($dados_formato, $conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo, 128, 4)."-".substr($conteudo, 125, 2)."-".substr($conteudo, 122, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", $conteudo);
		
		for ($i = 1; $i < sizeof($conteudo); $i++) {
			if (substr($conteudo[$i], 4, 6) == "SACADO") {
				
				$i += 2;
				
				$registro = array();
				
				$registro["id"] = $this->buscaCobrancaPorCodigo(substr($conteudo[$i], 26, 12));
				$registro["data_processamento"] =  "20".substr($conteudo[$i], 51, 2)."-".substr($conteudo[$i], 49, 2)."-".substr($conteudo[$i], 47, 2);
				$registro["data_credito"] =  "20".substr($conteudo[$i], 51, 2)."-".substr($conteudo[$i], 49, 2)."-".substr($conteudo[$i], 47, 2);
				$registro["valor_juro"] = intval(preg_replace("/[^0-9]/", "", substr($conteudo[$i], 70, 10)), 10);
				$registro["valor_desconto"] = intval(preg_replace("/[^0-9]/", "", substr($conteudo[$i], 82, 9)), 10);
				$registro["valor_total"] = intval(preg_replace("/[^0-9]/", "", substr($conteudo[$i], 97, 10)), 10);
				$registro["erro"] = "";
				
				$dados["registros"][] = $registro;
				
			}
		}
		
		return $dados;
		
	}

	function validaConteudoSantanderBoleto($dados_formato, $conteudo) {
		
		if (substr($conteudo, 0, 27) != "BANCO SANTANDER BRASIL S.A.") return false;
		if (substr($conteudo, 52, 44) != "AVISO DE MOVIMENTACAO DA CARTEIRA DE TITULOS") return false;
		
		return true;
		
	}

	##############################
	### Santander - Boleto 240 ###
	##############################

	function importarSantanderBoleto240($dados_formato, $conteudo) {
		
		// Registros
		$conteudo = preg_split("/\r?\n/", trim($conteudo));
		
		// Valida Conteudo
		if (!$this->validaConteudoSantanderBoleto240($dados_formato, $conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo[0], 147, 4)."-".substr($conteudo[0], 145, 2)."-".substr($conteudo[0], 143, 2);
		$dados["registros"] = array();
		
		for ($i = 2; $i < sizeof($conteudo); $i = $i + 2) {
			if (substr($conteudo[$i], 13, 1) == "T") {

				$registro = array();

				$registro["codigo"] = substr($conteudo[$i], 40, 12);
				$registro["id"] = $this->buscaCobrancaPorCodigo($registro["codigo"]);
				$registro["valor_original"] = intval(substr($conteudo[$i], 77, 15));
				$registro["data_vencimento"] = substr($conteudo[$i], 73, 4)."-".substr($conteudo[$i], 71, 2)."-".substr($conteudo[$i], 69, 2);

				if (substr($conteudo[$i + 1], 13, 1) == "U") {
					$registro["data_processamento"] = substr($conteudo[$i + 1], 141, 4)."-".substr($conteudo[$i + 1], 139, 2)."-".substr($conteudo[$i + 1], 137, 2);
					$registro["data_credito"] = substr($conteudo[$i + 1], 149, 4)."-".substr($conteudo[$i + 1], 147, 2)."-".substr($conteudo[$i + 1], 145, 2);
					$registro["valor_juro"] = intval(substr($conteudo[$i + 1], 17, 15));
					$registro["valor_desconto"] = intval(substr($conteudo[$i + 1], 32, 15)) + intval(substr($conteudo[$i + 1], 47, 15));
					$registro["valor_total"] = intval(substr($conteudo[$i + 1], 77, 15));
					$registro["valor_iof"] = intval(substr($conteudo[$i + 1], 62, 15));
					$registro["valor_liquido"] = intval(substr($conteudo[$i + 1], 92, 15));
					$registro["valor_despesas"] = intval(substr($conteudo[$i + 1], 107, 15));
					$registro["valor_creditos"] = intval(substr($conteudo[$i + 1], 122, 15));
				}

				$registro["erro"] = null;

				$dados["registros"][] = $registro;

			}
		}

		return $dados;
		
	}

	function buscaValorTotalCobranca($cobranca_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				coa_valor_total
			FROM
				tab_cobrancas
			WHERE
				coa_id = $cobranca_id");
		
		return $this->bancodedados->resultado($query);
		
	}

	function validaConteudoSantanderBoleto240($dados_formato, $conteudo) {
		
		// Valida Código Cedente
		if (substr($conteudo[0], 54, 6) != $dados_formato["parametros"]["cedente"]["valor"]) return false;
		// Validação do Banco
		if (!in_array(substr($conteudo[0], 0, 3), array("008", "033"))) return false;
		// Validação da Versão do Layout
		if (substr($conteudo[0], 163, 3) != "040") return false;
		
		return true;
		
	}

	######################
	### Caixa - Boleto ###
	######################

	function importarCaixaBoleto($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoCaixaBoleto($conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo, 147, 4)."-".substr($conteudo, 145, 2)."-".substr($conteudo, 143, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", trim($conteudo));
		
		for ($i = 2; $i < sizeof($conteudo); $i++) {
			if (substr($conteudo[$i], 13, 1) == "T" && substr($conteudo[$i], 15, 2) == "06") {
				
				$codigo = substr($conteudo[$i], 46, 10);
				$registro = array();
				
				$registro["id"] = $this->buscaCobrancaPorCodigo($codigo);
				$registro["nosso_numero"] = $codigo;
				
				$j = 1;
				
				while ($conteudo[$i + $j] && substr($conteudo[$i + $j], 13, 1) != "T") {
					if (substr($conteudo[$i + $j], 13, 1) == "U") {
						$registro["data_processamento"] =  substr($conteudo[$i + $j], 141, 4)."-".substr($conteudo[$i + $j], 139, 2)."-".substr($conteudo[$i + $j], 137, 2);
						$registro["data_credito"] =  substr($conteudo[$i + $j], 149, 4)."-".substr($conteudo[$i + $j], 147, 2)."-".substr($conteudo[$i + $j], 145, 2);
						$registro["valor_juro"] = intval(substr($conteudo[$i + $j], 17, 15));
						$registro["valor_desconto"] = intval(substr($conteudo[$i + $j], 32, 15)) + intval(substr($conteudo[$i + $j], 47, 15));
						$registro["valor_total"] = intval(substr($conteudo[$i + $j], 77, 15));
					}
					$j++;
				}
				
				$registro["erro"] = "";
				
				$dados["registros"][] = $registro;
				
			}
		}

		return $dados;
	
	}

	function validaConteudoCaixaBoleto($conteudo) {
		
		// Validação do Banco
		if (substr($conteudo, 0, 3) != "104") return false;
		// Validação da Versão do Layout
		if (substr($conteudo, 163, 3) != "030") return false;
		
		return true;
		
	}

	###########################
	### Banco Real - Boleto ###
	###########################

	function importarBancoRealBoleto($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoBancoRealBoleto($conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo, 147, 4)."-".substr($conteudo, 145, 2)."-".substr($conteudo, 143, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", trim($conteudo));
		
		for ($i = 2; $i < sizeof($conteudo); $i++) {
			if (substr($conteudo[$i], 13, 1) == "T") {
				
				$registro = array();
				
				$registro["id"] = $this->buscaCobrancaPorCodigo(substr($conteudo[$i], 44, 13));
				
				$j = 1;
				
				while ($conteudo[$i + $j] && substr($conteudo[$i + $j], 13, 1) != "T") {
					if (substr($conteudo[$i + $j], 13, 1) == "U") {
						$registro["data_processamento"] =  substr($conteudo[$i + $j], 141, 4)."-".substr($conteudo[$i + $j], 139, 2)."-".substr($conteudo[$i + $j], 137, 2);
						$registro["data_credito"] =  substr($conteudo[$i + $j], 149, 4)."-".substr($conteudo[$i + $j], 147, 2)."-".substr($conteudo[$i + $j], 145, 2);
						$registro["valor_juro"] = intval(substr($conteudo[$i + $j], 17, 15));
						$registro["valor_desconto"] = intval(substr($conteudo[$i + $j], 32, 15)) + intval(substr($conteudo[$i + $j], 47, 15));
						$registro["valor_total"] = intval(substr($conteudo[$i + $j], 77, 15));
					}
					$j++;
				}
				
				$registro["erro"] = "";
				
				$dados["registros"][] = $registro;
			
			}
		}
		
		return $dados;
		
	}

	function validaConteudoBancoRealBoleto($conteudo) {
		
		// Validação do Banco
		if (substr($conteudo, 0, 3) != "275") return false;
		// Validação da Versão do Layout
		if (substr($conteudo, 163, 3) != "030") return false;
		
		return true;
		
	}

	################################
	### BESC - Débito Automático ###
	################################

	function importarBESCDebito($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoBESCDebito($conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo, 147, 4)."-".substr($conteudo, 145, 2)."-".substr($conteudo, 143, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", trim($conteudo));
		
		for ($i = 2; $i < sizeof($conteudo); $i++) {
			if (substr($conteudo[$i], 13, 1) == "A") {
				
				$registro = array();
				
				$registro["id"] = $this->buscaCobrancaPorCodigo(substr($conteudo[$i], 73, 13));
				
				if (substr($conteudo[0], 0, 3) == "001") {
					$registro["data_processamento"] = substr($conteudo[$i], 154, 4)."-".substr($conteudo[$i], 158, 2)."-".substr($conteudo[$i], 160, 2);
					$registro["data_credito"] = substr($conteudo[$i], 154, 4)."-".substr($conteudo[$i], 158, 2)."-".substr($conteudo[$i], 160, 2);
				} else {
					$registro["data_processamento"] = substr($conteudo[$i], 158, 4)."-".substr($conteudo[$i], 156, 2)."-".substr($conteudo[$i], 154, 2);
					$registro["data_credito"] = substr($conteudo[$i], 158, 4)."-".substr($conteudo[$i], 156, 2)."-".substr($conteudo[$i], 154, 2);
				}
				
				$registro["valor_juro"] = 0;
				$registro["valor_desconto"] = 0;
				$registro["valor_total"] = intval(substr($conteudo[$i], 162, 15));
				
				if (substr($conteudo[0], 0, 3) == "001") {
					$registro["erro"] = $this->buscaErroBBDebito(intval(substr($conteudo[$i], 229, 3)));
				} else {
					$registro["erro"] = $this->buscaErroBESCDebito(substr($conteudo[$i], 230, 10));
				}
				
				$dados["registros"][] = $registro;
			
			}
		}
		
		return $dados;
	
	}

	function validaConteudoBESCDebito($conteudo) {
		
		if (substr($conteudo, 0, 3) == "027") {
			// Validação da Versão do Layout
			if (substr($conteudo, 163, 3) == "020") return true;
		} elseif (substr($conteudo, 0, 3) == "001") {
			// Validação da Versão do Layout
			if (substr($conteudo, 163, 3) == "082") return true;
		}
		
		return false;
	
	}

	function buscaErroBESCDebito($codigo) {
		
		$erro = array(
			 0 => null,
			 1 => 'A pedido da empresa',
			 2 => 'Por solicitação do BESC',
			 3 => 'Insuficiência de saldo em conta corrente',
			 4 => 'Vencimento do prazo de validade do lançamento',
			 5 => 'Limite de repescagem excedido',
			 6 => 'Agência e/ou conta corrente do destinatário inexistente',
			 7 => 'Seqüência inválida',
			 8 => 'Código de segmento inválido',
			 9 => 'Tipo de movimento inválido',
			10 => 'Banco destino não cadastrado',
			11 => 'Data de lançamento inválida ou não informada',
			12 => 'Tipo de moeda inválido',
			13 => 'Valor não informado',
			14 => 'Arquivo remessa com mais de um TRAILLER',
			15 => 'Arquivo remessa com mais de um TRAILLER de lote',
			16 => 'Arquivo remessa excedeu o valor máximo permitido para o convênio',
			17 => 'Banco inválido para o convênio',
			18 => 'Código de finalidade inválido',
			19 => 'Código de movimento inválido',
			20 => 'Data fim do lançamento menor que a data atual',
			21 => 'Data fim do lançamento menor que a data de lançamento',
			22 => 'Data fim do lançamento inválida ou não informada',
			23 => 'Operação inválida',
			24 => 'Data para provisão não permitida',
			25 => 'Data de recebimento esgotada',
			26 => 'Indicador de provisão inválido ',
			27 => 'Lançamento já existente',
			28 => 'Lote vazio',
			29 => 'Processamento não permitido após as 18:00 horas',
			30 => 'Número de lote inválido',
			31 => 'Lançamento inexistente',
			32 => 'Forma incompatível para conta corrente BESC ',
			33 => 'Forma incompatível para DOC ',
			34 => 'Quantidade de registros do TRAILLER de LOTE difere do arquivo',
			35 => 'Tempo para transmissão esgotado',
			36 => 'Tipo de registro inválido',
			37 => 'Valor total do lote difere da soma dos lançamentos deste lote',
			38 => 'Nome do destinatário não informado',
			39 => 'Tipo de moeda não informado',
			40 => 'Arquivo sem registro TRAILLER',
			41 => 'Forma de lançamento incompatível para crédito',
			42 => 'Forma de lançamento incompatível para débito',
			43 => 'LAY-OUT de arquivo inválido',
			44 => 'Tipo de CIC inválido',
			45 => 'CIC inválido',
			46 => 'Convênio inválido',
			47 => 'Agência informada para o convênio não confere com a cadastrada',
			48 => 'Dígito verificador da agência do convênio não confere',
			49 => 'Conta corrente do convênio não conferem',
			50 => 'Dígito verificador da conta corrente do convênio não confere',
			51 => 'Situação do lançamento não permite alteração',
			52 => 'Forma de lançamento inválida',
			53 => 'Convênio não permite lançamentos de DOC',
			54 => 'Informe identificação para forma de lançamento igual a recibo',
			55 => 'Conta corrente não permitida para forma de lançamento igual a recibo',
			56 => 'Tipo de identificador inválido',
			57 => 'Convênio do reg. DETALHE difere do convênio do reg. HEADER',
			58 => 'Data de início de validade do lançamento não informada',
			59 => 'Data de início do lançamento maior que data de lançamento',
			60 => 'Situação do lançamento inválida',
			61 => 'Tipo de documento inválido',
			62 => 'Valor do lançamento excede o permitido pelo convênio',
			63 => 'Lançamento duplicado (mesma conta/mesma data/mesmo valor)',
			64 => 'Convênio desativado',
			65 => 'Para forma lançamento = DOC, informe agência e conta centralizadora',
			66 => 'O lote deve existir para alteração',
			67 => 'Lote já existente',
			68 => 'Agência destinatária inexistente',
			69 => 'Agência e/ou conta corrente invalida(s)',
			70 => 'Agência e/ou conta poupança destinatário inexistente',
			71 => 'Conta corrente válida somente para recibo',
			72 => 'Agência pagamento cartão inválida',
			73 => 'Conta corrente para pagamento de cartão inválida',
			74 => 'CPF ou CNPJ do destinatário inválido',
			75 => 'Agência em regime de encerramento',
			76 => 'Conta corrente inválida para o cadastro de débito automático',
			77 => 'Manutenção do cadastro de débito automático',
			78 => 'Cancelamento por não ter sido encontrado o cadastro',
			79 => 'Cancelamento por estar fora do tempo hábil',
			80 => 'Outras restrições',
			81 => 'Código da Câmara de Compensação do Banco Favorecido Inválido',
			82 => 'Valor do Lançamento Inválido',
			93 => 'Conta inválida para débito'
		);
		
		$erros = array();
		
		for ($i = 0; $i < 5; $i++) {
			if (array_key_exists(intval(substr($codigo, ($i * 2), 2), 10), $erro)) {
				if ($erro[intval(substr($codigo, ($i * 2), 2), 10)]) {
					$erros[] = $erro[intval(substr($codigo, ($i * 2), 2), 10)];
				}
			} else {
				$erros[] = "Erro desconhecido";
			}
		}
		
		return $erros ? implode(". ", $erros) : "";
	
	}

	#################################
	### CAIXA - Débito Automático ###
	#################################

	function importarCEFDebito($dados_formato, $conteudo) {
		
		// Valida Conteudo
		if (!$this->validaConteudoCEFDebito($conteudo)) return false;
		
		// Data de Geração
		$dados = array();
		$dados["data_geracao"] = substr($conteudo, 65, 4)."-".substr($conteudo, 69, 2)."-".substr($conteudo, 71, 2);
		$dados["registros"] = array();
		
		// Registros
		$conteudo = preg_split("/\r?\n/", $conteudo);
		
		for ($i = 1; $i < sizeof($conteudo) - 1; $i++) {			
			if (substr($conteudo[$i], 0, 1) == "F") {
				
				$valor_cobranca = intval(substr($conteudo[$i], 61, 6), 10);
				$cobranca = $this->buscaCobrancaMesmaConta(substr($conteudo[$i], 1, 25), intval(substr($conteudo[$i], 2, 8), 10), substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2), $dados_formato["formato_id"]);
				
				if ($cobranca) {
					
					$registro = array();
					
					$registro["id"] = $cobranca["id"];
					$registro["data_processamento"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
					$registro["data_credito"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
					$registro["valor_juro"] = 0;
					$registro["valor_desconto"] = 0;
					$registro["valor_total"] = $cobranca["valor"];
					$registro["erro"] = $this->buscaErroCEFDebito(substr($conteudo[$i], 67, 2));
					
					if ($valor_cobranca > $cobranca["valor"] || $registro["erro"]) {
						$dados["registros"][] = $registro;
						if ($valor_cobranca > $cobranca["valor"]) {
							$valor_cobranca -= $cobranca["valor"];
						}
					}
					
				}

				$registro = array();

				$registro["id"] = $this->buscaCobranca(intval(substr($conteudo[$i], 2, 8), 10), substr($conteudo[$i], 44, 4), substr($conteudo[$i], 48, 2), $dados_formato["formato_id"]);
				$registro["data_processamento"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
				$registro["data_credito"] = substr($conteudo[$i], 44, 4)."-".substr($conteudo[$i], 48, 2)."-".substr($conteudo[$i], 50, 2);
				$registro["valor_juro"] = 0;
				$registro["valor_desconto"] = 0;
				$registro["valor_total"] = $valor_cobranca;
				$registro["erro"] = $this->buscaErroCEFDebito(substr($conteudo[$i], 67, 2));
				
				$dados["registros"][] = $registro;
			
			}		
		}
		
		return $dados;
		
	}

	function validaConteudoCEFDebito($conteudo) {
		
		if (substr($conteudo, 42, 23) != "104CAIXA ECON. FEDERAL ") return false;
		if (substr($conteudo, 79, 19) != "04DEBITO AUTOMATICO") return false;
		
		return true;
		
	}

	function buscaErroCEFDebito($codigo) {
		
		$erro = array(
			"00" => "",
			"01" => "Débito não efetuado - Insuficiência de fundos",
			"02" => "Débito não efetuado - Conta corrente não cadastrada",
			"04" => "Débito não efetuado - Outras restrições",
			"10" => "Débito não efetuado - Agências em regime de encerramento",
			"12" => "Débito não efetuado - Valor inválido",
			"13" => "Débito não efetuado - Data do lançamento inválida",
			"14" => "Débito não efetuado - Agência inválida",
			"15" => "Débito não efetuado - Dv da conta corrente inválido",
			"18" => "Débito não efetuado - Data do débito anterior a do processamento",
			"30" => "Débito não efetuado - Sem contrato de débito automático",
			"96" => "Devolução - Manutenção de cadastro",
			"97" => "Cancelamento - Não encontrado",
			"98" => "Cancelamento - Não efetuado, fora de tempo hábil",
			"99" => "Cancelamento - Cancelado conforme solicitação"
		);
		
		if (array_key_exists($codigo, $erro)) {
			return $erro[$codigo];
		}
		
		return "Erro desconhecido";
	
	}

}

?>