<?
	require_once ("../../global.inc.php");

	$server = new SoapServer(null, array('uri' => "faturas"));
	$server->setClass("callback", $bancodedados);
	$server->handle();

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
			$response ['faturas'] 		= array ();
			$response ['success'] 		= 1;
			$response ['message'] 		= TEXTERRORNOERROR;
			$data_vencimento 			= "";
			$data_pagamento 			= "";

			$cpf_cnpj = preg_replace("/[^0-9\\.\\-\\/]/", "", $cpf_cnpj);

			if (strlen($cpf_cnpj) == 0) {
				return $response;
			}

			if (($datanasc != "") && (strlen($cpf_cnpj) < 15))
			{
				$myDateTime 			= DateTime::createFromFormat('d/m/Y', $datanasc);
				$data_nascimento 		= $myDateTime->format('Y-m-d');

				$query 					= "SELECT cli_id, cli_nome FROM `tab_clientes` WHERE `cli_cpf_cnpj` = '" . $cpf_cnpj . "' AND cli_nascimento = '" . $data_nascimento ."' AND clt_id = '1'";
				$result					= $this->bancodedados->query ( $query );
				$dataresult				= $this->bancodedados->resultadoArray($result);

				$idcliente				= $dataresult['cli_id'];
				$nomecliente			= utf8_encode($dataresult['cli_nome']);
				$response['nomecli']	= $nomecliente;
			}
			else
			{
				$query 					= "SELECT 	cli_id, cli_nome 
													FROM `tab_clientes` 
													WHERE `cli_cpf_cnpj` = '" . $cpf_cnpj . "' 
													AND clt_id = '2'";

				$result					= $this->bancodedados->query ( $query );
				$dataresult				= $this->bancodedados->resultadoArray($result);

				$idcliente				= $dataresult['cli_id'];
				$nomecliente			= utf8_encode($dataresult['cli_nome']);
				$response['nomecli']	= $nomecliente;
			}


			$queryinvoices				= "SELECT 	coa_id, coa_codigo, coa_data_vencimento, 
													coa_data_pagamento, coa_valor_original, 
													coa_valor_total, cot_id, cft_id 
													FROM `tab_cobrancas` 
													INNER JOIN `tab_cobrancas_formatos` 
													ON (`tab_cobrancas`.cof_id = `tab_cobrancas_formatos`.cof_id) 
													WHERE `cli_id` = '" . $idcliente . "' 
													AND `cot_id` IN(1,2,3,4,5,8,9,11) 
													AND coa_pai IS NULL 
													ORDER BY coa_data_vencimento 
													DESC 
													LIMIT 12";
													
			
			$result						= $this->bancodedados->query ( $queryinvoices );

    		while (($row = $this->bancodedados->resultadoArray($result)) != false)
    		{
    			if ($row["cot_id"] == 1 || $row["cot_id"] == 11)
    			{

    				if ($row["cot_id"] == 1)
    				{
						$querySegundaVia	= "SELECT 	coa_id, coa_codigo, coa_data_vencimento, 
														coa_data_pagamento, coa_valor_original, 
														coa_valor_total, cot_id, cft_id 
														FROM `tab_cobrancas` 
														INNER JOIN `tab_cobrancas_formatos` 
														ON (`tab_cobrancas`.cof_id = `tab_cobrancas_formatos`.cof_id) 
														WHERE `coa_pai` = '" . $row["coa_id"] . "' 
														AND cot_id != 12 
														ORDER BY coa_codigo 
														DESC 
														LIMIT 1"; 					
    				}
    				else
    				{
						$querySegundaVia	= "SELECT 	coa_id, coa_codigo, coa_data_vencimento, 
														coa_data_pagamento, coa_valor_original, 
														coa_valor_total, cot_id, cft_id 
														FROM `tab_cobrancas` 
														INNER JOIN `tab_cobrancas_formatos` 
														ON (`tab_cobrancas`.cof_id = `tab_cobrancas_formatos`.cof_id) 
														WHERE `coa_pai` = '" . $row["coa_id"] . "' 
														AND cot_id IN (2,3,4,5,8,9) 
														LIMIT 1"; 					
    				}

					$resultSegundaVia		= $this->bancodedados->query ( $querySegundaVia );
					$segundaVia 			= $this->bancodedados->resultadoArray($resultSegundaVia);

					if ($segundaVia != null)
					{
						if ($row["cot_id"] == 1) {
							$row = $segundaVia;
						} else {
							$row["coa_data_pagamento"] = $segundaVia["coa_data_pagamento"];
							$row["coa_valor_total"] = $segundaVia["coa_valor_total"];
							$row["cot_id"] = $segundaVia["cot_id"];
							$row["cft_id"] = $segundaVia["cft_id"];
						}
					}    				
    			}

    			if ($row["cot_id"] == "1")
				{
					$status = "Em Aberto";
				}
				else if ($row["cot_id"] == "8")
				{
					$status = "Cortesia";
				}
				else
				{
					$status = "Paga";
				}

				$fatura['coa_status'] = $status;

    			if ($row["cot_id"] != 1 || $row["cft_id"] == 1 || $row["cft_id"] == 4)
    			{
    				$row["coa_id"] = 0;
    			}

    			if (($row['coa_valor_original'] != "") || (is_null($row['coa_valor_original'] )))
    			{

					$valor_original 	= "R$ " . number_format($row['coa_valor_original'] / 100, 2, ",", ".");
				}

				if (($row['coa_valor_total'] != "") || (is_null($row['coa_valor_total'] )))
    			{
    				if ($row['cot_id'] != '1')
    				{
						$valor_total 	= "R$ " . number_format($row['coa_valor_total'] / 100, 2, ",", ".");
					}
					else
					{
						$valor_total 	= "";
					}
				}

				if ($row['coa_data_vencimento'] != "") // Senão dá erro 500
				{
					$myDateTime 		= DateTime::createFromFormat('Y-m-d', $row['coa_data_vencimento']);
					$data_vencimento 	= $myDateTime->format('d/m/Y');
				}

				if ($row['coa_data_pagamento'] != "") // ídem
				{
					$myDateTime2 		= DateTime::createFromFormat('Y-m-d', $row['coa_data_pagamento']);
					$data_pagamento 	= $myDateTime2->format('d/m/Y');
				}

				$fatura['coa_id']					= $row['coa_id'];
  				$fatura['coa_codigo']				= $row['coa_codigo'];
				$fatura['coa_data_vencimento'] 		= $data_vencimento;
				$fatura['coa_data_pagamento']		= $data_pagamento;
				$fatura['coa_valor_original']		= $valor_original;
				$fatura['coa_valor_total']			= $valor_total;

				array_push($response ['faturas'], $fatura);
   			}

			$response ["success"] 		= 1;
			$response ["message"] 		= TEXTERRORNOERROR;

			return ( $response);
		}
		
		function getBoleto($cobranca_id)
		{
			$boletos_pdf 	= controle::instanciaObjeto ( "cobrancas.boletos_pdf", $this->bancodedados );
			$boletos_pdf->gerar ($cobranca_id);
			return base64_encode ($boletos_pdf->conteudo());
		}

		function getCodigoBoleto($cobranca_id)
		{
			$queryinvoices	= "SELECT coa_codigo FROM `tab_cobrancas` WHERE `coa_id` = '" . $cobranca_id . "'";
			$result			= $this->bancodedados->query ( $queryinvoices );
			return($this->bancodedados->resultado($result));
		}
	}

?>
