<?php

	require_once ("../../global.inc.php");

	$server = new SoapServer(null, array('uri' => "faturas"));
	$server->setClass("callback", $bancodedados);
	$server->handle();
//
//	$callback = new callback($bancodedados);
//	var_dump($callback->getFaturas("006.880.390-73", "10/10/1986"));
	
	class callback
	{
		var $bancodedados;
		
		function __construct($bancodedados)
		{
			$this->bancodedados = $bancodedados;
		}
		
		function getFaturas($cpf_cnpj, $datanasc)
		{
			require_once ("config.php");

			$response = array();
			$response ['nomecli'] 		= "";
			$response ['clientes'] 		= array ();
			$response ['success'] 		= 1;
			$response ['message'] 		= TEXTERRORNOERROR;
			$data_vencimento 			= "";
			$data_pagamento 			= "";

			$cpf_cnpj = preg_replace("/[^0-9\\.\\-\\/]/", "", $cpf_cnpj);

			if (strlen($cpf_cnpj) == 0) {
				return $response;
			}

			$filters = array();
			
			$filters[] = "cli_cpf_cnpj = '" . $cpf_cnpj . "'";
			$filters[] = "cls_id IN (1, 2, 3)";
			$filters[] = "(SELECT COUNT(*) FROM tab_cobrancas WHERE tab_clientes.cli_id = tab_cobrancas.cli_id AND cot_id != 12) > 0";
			
			if (($datanasc != "") && (strlen($cpf_cnpj) < 15))
			{
				$myDateTime 			= DateTime::createFromFormat('d/m/Y', $datanasc);
				$data_nascimento 	= $myDateTime->format('Y-m-d');
				
				$filters[] = "cli_nascimento = '" . $data_nascimento ."'";
				$filters[] = "clt_id = 1";
			}
			else
			{
				$filters[] = "clt_id = 2";
			}
			
			$querycli 			= "
				SELECT
					cli_id, 
					cli_nome,
					cli_complemento AS complemento,
					IF(tab_clientes.con_id IS NULL, cli_numero, con_numero) AS numero,
					tab_condominios.con_nome AS condominio,
					tab_condominios_blocos.cob_nome AS bloco,
					tab_logradouros.log_nome AS logradouro,
					tab_bairros.bai_nome AS bairro,
					tab_cidades.cid_nome AS cidade,
					IF(cls_id = 2, 'Ativo', 'Inativo') AS status
				FROM 
					tab_clientes
					LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
					LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
					INNER JOIN tab_ceps ON (IF(tab_clientes.con_id IS NULL, tab_clientes.cep_id, tab_condominios.cep_id) = tab_ceps.cep_id)
					INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
					INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
					INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				WHERE
					" . implode(" AND ", $filters)."
				ORDER BY
					cls_id";
			$resultcli			= $this->bancodedados->query ( $querycli );
			$idcliente = "";
    	while (($datacli = $this->bancodedados->resultadoArray($resultcli)) != false)
    	{
    		
				$idcliente						= $datacli['cli_id'];
				$nomecliente					= utf8_encode($datacli['cli_nome']);
				$response['nomecli']	= $nomecliente;
				
				$cliente = array();

				$temBloqueioAtivo = $this->temBloqueioAtivo($idcliente);
				$cliente['temBloqueioAtivo'] = $temBloqueioAtivo;
				if($temBloqueioAtivo) {
					$cliente['txt_temBloqueioAtivo'] = utf8_encode("<b style='font-size: 16px;line-height: 3;'>Você possui um bloqueio ativo em seu cadastro. Você pode desbloquear seu acesso através do botão.</b>");
				} else {
					$cliente['txt_temBloqueioAtivo'] = "";
				}
				$cliente['cliente_id'] = sprintf("%06s", $datacli['cli_id']);


				$jaFoiDesbloqueado = $this->jaFoiDesbloqueado($idcliente);
				$cliente['jaFoiDesbloqueado'] = $jaFoiDesbloqueado;
				if($jaFoiDesbloqueado) {
					$cliente['txt_jaFoiDesbloqueado'] = utf8_encode("<b style='font-size: 16px;line-height: 3;'>Você já realizou um desbloqueio em seu cadastro. Caso tenha dúvidas, entre em contato com o setor financeiro.</b>");
				} else {
					$cliente['txt_jaFoiDesbloqueado'] = "";
				}
				
				if ($datacli["condominio"]) {
					$complemento = formataComplemento($datacli["complemento"], $datacli["bloco"]);
					$cliente["endereco"] = sprintf("%06s - %s", $datacli['cli_id'], $datacli["condominio"]);
					if ($complemento)
					{
						$cliente["endereco"] .= ", {$complemento}";
					}
				} else {
					$cliente["endereco"] = sprintf("%06s - %s", $datacli['cli_id'], $datacli["logradouro"]);
					if ($datacli["numero"]) {
						$cliente["endereco"] .= ", {$datacli["numero"]}";
					}
					$cliente["endereco"] .= ", {$datacli["bairro"]}";
				}
				
				$cliente["endereco"] = utf8_encode($cliente["endereco"]);
				
				$cliente["faturas"] = array();
				
    		$queryinvoices				= "
    			SELECT
    				coa_id,
    				coa_codigo,
    				coa_data_vencimento,
    				coa_data_pagamento, 
    				coa_valor_original,
    				coa_valor_total, 
    				cot_id, 
    				cft_id 
					FROM
						tab_cobrancas 
						INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id) 
					WHERE
						cli_id = '" . $idcliente . "' AND
						cot_id IN(1, 2, 3, 4, 5, 8, 9, 11) AND
						coa_pai IS NULL
					ORDER BY
						coa_data_vencimento 
					DESC
						LIMIT 12";
			
				$resultinvoices						= $this->bancodedados->query ( $queryinvoices );

    		while (($datainvoice = $this->bancodedados->resultadoArray($resultinvoices)) != false) {
    			
				$data_vencimento = "";
				$data_pagamento = "";
				$valor_original = "";
				$valor_total = "";
    			
    			if ($datainvoice["cot_id"] == 1 || $datainvoice["cot_id"] == 11) {
					$querySegundaVia	= "
						SELECT
							coa_id,
							coa_codigo,
							coa_data_vencimento,
							coa_data_pagamento,
							coa_valor_original,
							coa_valor_total,
							cot_id, cft_id 
						FROM
							tab_cobrancas
							INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id) 
						WHERE
							coa_pai = '" . $datainvoice["coa_id"] . "' AND
							cot_id ".($datainvoice["cot_id"] == 1 ? "!= 12" : "IN (2, 3, 4, 5, 8, 9)")."
						ORDER BY
							coa_codigo DESC 
						LIMIT 1"; 					

					$resultSegundaVia		= $this->bancodedados->query ( $querySegundaVia );
					$segundaVia 			= $this->bancodedados->resultadoArray($resultSegundaVia);

					if ($segundaVia != null)
					{
						if ($datainvoice["cot_id"] == 1) {
							$datainvoice = $segundaVia;
						} else {
							$datainvoice["coa_data_pagamento"] = $segundaVia["coa_data_pagamento"];
							$datainvoice["coa_valor_total"] = $segundaVia["coa_valor_total"];
							$datainvoice["cot_id"] = $segundaVia["cot_id"];
							$datainvoice["cft_id"] = $segundaVia["cft_id"];
						}
					}
					
    			}

    			if ($datainvoice["cot_id"] == "1") {
					$status = "Em Aberto";
				} else if ($datainvoice["cot_id"] == "8") {
					$status = "Cortesia";
				} else {
					$status = "Paga";
				}

    			if ($datainvoice["cot_id"] != 1 || $datainvoice["cft_id"] == 1 || $datainvoice["cft_id"] == 4) {
    				$datainvoice["coa_id"] = 0;
    			}

    			if (($datainvoice['coa_valor_original'] != "") || (is_null($datainvoice['coa_valor_original'] ))) {
					$valor_original 	= "R$ " . number_format($datainvoice['coa_valor_original'] / 100, 2, ",", ".");
				}

				if (($datainvoice['coa_valor_total'] != "") || (is_null($datainvoice['coa_valor_total'] ))) {
    				if ($datainvoice['cot_id'] != '1') {
						$valor_total 	= "R$ " . number_format($datainvoice['coa_valor_total'] / 100, 2, ",", ".");
					} else {
						$valor_total 	= "";
					}
				}

				if ($datainvoice['coa_data_vencimento'] != "") { // SenÃ£o dÃ¡ erro 500
					$myDateTime 		= DateTime::createFromFormat('Y-m-d', $datainvoice['coa_data_vencimento']);
					$data_vencimento 	= $myDateTime->format('d/m/Y');
				}

				if ($datainvoice['coa_data_pagamento'] != "") {
					$myDateTime2 		= DateTime::createFromFormat('Y-m-d', $datainvoice['coa_data_pagamento']);
					$data_pagamento 	= $myDateTime2->format('d/m/Y');
				}

				$fatura = array();
				$fatura['coa_id']					= $datainvoice['coa_id'];
				$fatura['coa_codigo']				= $datainvoice['coa_codigo'];
				$fatura['coa_data_vencimento'] 		= $data_vencimento;
				$fatura['coa_data_pagamento']		= $data_pagamento;
				$fatura['coa_valor_original']		= $valor_original;
				$fatura['coa_valor_total']			= $valor_total;
				$fatura['coa_status'] = $status;
				
				array_push($cliente['faturas'], $fatura);
				
			}
				
			array_push($response['clientes'], $cliente);
				
    	}

			return ( $response);
		}

		
		function getDesbloquear($cpf_cnpj, $cliente_id, $datanasc)
		{
			require_once ("config.php");

			$response = array();
			$response ['nomecli'] 		= "";
			$response ['clientes'] 		= array ();
			$response ['success'] 		= 1;
			$response ['message'] 		= TEXTERRORNOERROR;
			$data_vencimento 			= "";
			$data_pagamento 			= "";

			$cpf_cnpj = preg_replace("/[^0-9\\.\\-\\/]/", "", $cpf_cnpj);

			if (strlen($cpf_cnpj) == 0) {
				return $response;
			}

			$filters = array();
			$response['desbloqueado'] = true;
			return true;
		}

		function jaFoiDesbloqueado($cliente) {

			$query = "
					SELECT 
						COUNT(*) desbloqueio 
					FROM 
						tab_servicos_ajustes 
					WHERE 
						cli_id = $cliente AND sas_id != 5 AND YEAR(saj_termino) = YEAR(CURDATE())
			AND MONTH(saj_termino) = MONTH(CURDATE())";
			$result = $this->bancodedados->query ( $query );
			$retorno = $this->bancodedados->resultado($result);
			
			// return true;
			if($retorno > 0) {
				return true;
			}
			
			return false;
		}

		
		function temBloqueioAtivo($cliente) {

			$query = "SELECT COUNT(*) qtd FROM tab_servicos_ajustes WHERE cli_id = $cliente AND sas_id = 5";
			$result = $this->bancodedados->query ( $query );
			$retorno = $this->bancodedados->resultado($result);
			
			if($retorno > 0) {
				return true;
			}

			return false;
		}

		function getBoleto($cobranca_id)
		{
		
			$boletos_pdf = controle::instanciaObjeto("cobrancas.boletos_pdf", $this->bancodedados);
			$boletos_pdf->gerarBoleto($cobranca_id);
			
			return base64_encode($boletos_pdf->conteudo());
			
		}

		function getCodigoBoleto($cobranca_id)
		{
			$queryinvoices	= "SELECT coa_codigo FROM tab_cobrancas WHERE coa_id = '" . $cobranca_id . "'";
			$result			= $this->bancodedados->query ( $queryinvoices );
			return($this->bancodedados->resultado($result));
		}
	}
	
function formataComplemento($complemento, $bloco) {
	$complementos_tipos["a"] = "Apto ";
	$complementos_tipos["l"] = "Loja ";
	$complementos_tipos["s"] = "Sala ";
	$complementos_tipos["c"] = "Casa ";
	$complementos_tipos["o"] = "";
	if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
		$complemento = $complementos_tipos[$complemento[0]].substr($complemento, 1, strlen($complemento));
		if ($bloco && $bloco != "Ãšnico") {
			$complemento .= " / {$bloco}";
		}
		return $complemento;
	} else {
		return "";
	}
}