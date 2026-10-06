<?

set_time_limit(0);
ini_set("default_socket_timeout", 600);

if (array_key_exists("wsdl", $_GET)) {
	header('Content-type: text/xml');
	$wsdl = "invoice.wsdl";
	$fp = fopen($wsdl, "r");
	$retorno = fread($fp, filesize($wsdl));
	fclose($fp);
	print $retorno;
	die();
}

require_once ("../../global.inc.php");

if ($_GET["ciclo"]) {
	$invoice = new invoice($bancodedados);
	print $invoice->generateFile($_GET["ciclo"]);
	die();
}

$server = new SoapServer("http://oletv.oletelecom.com.br/soap/invoice/?wsdl");
$server->setClass("invoice", $bancodedados);
$server->handle();

class invoice {
	
	var $bancodedados;
	
	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function invoice_cycle_finished($ciclo) {
		
		$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
		$faturas_lotes->cicloAprovado($ciclo);
		
//		$lote_id = $this->buscaLotePorCiclo($ciclo);
//		if ($lote_id) {
//			$this->bancodedados->query("UPDATE tab_faturas_lotes SET fls_id = 3 WHERE fal_id = $lote_id");
//	    $comentario = "<b>Ação:</b> Geração completada";
//	    $this->registraComentario($lote_id, $comentario);
//		}

	}
	
	function buscaLotePorCiclo($ciclo) {
		
		$query = $this->bancodedados->query("SELECT fal_id FROM tab_faturas_lotes WHERE fal_ciclo = '$ciclo' AND fap_id = 2");
		return $this->bancodedados->resultado($query);
		
	}
	
	function registraComentario($lote_id, $conteudo, $responsavel_id = "NULL") {
		
		$query = "INSERT INTO tab_faturas_lotes_comentarios (flc_datahora, fal_id, flc_conteudo, usu_id) VALUES (NOW(), $lote_id, '$conteudo', $responsavel_id)";
		$this->bancodedados->query($query);
		
	}
	
	function generateFile($ciclo) {
		
		return utf8_encode($this->export_invoice_cycle($ciclo));
		
	}
	
	function export_invoice_cycle($ciclo) {
		
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		
		//$xml->startDocument('1.0', 'ISO-8859-1');
		
		$xml->startElement("Invoicing");
		
		$query = "
			SELECT
				fcl_id AS id,
				fcl_nome AS nome,
				fcl_nome_fantasia AS nome_fantasia,
				fcl_tipo_pessoa AS tipo_pessoa,
				fcl_cpf_cnpj AS cpf_cnpj,
				fcl_inscricao_municipal AS inscricao_municipal,
				fcl_inscricao_estadual AS inscricao_estadual,
				fcl_endereco_logradouro AS endereco_logradouro,
				fcl_endereco_numero AS endereco_numero,
				fcl_endereco_complemento AS endereco_complemento,
				fcl_endereco_bairro AS endereco_bairro,
				fcl_endereco_cep AS endereco_cep,
				cli_id AS cliente_id
			FROM
			  tab_faturas_lotes_clientes
			WHERE
				fal_id = (SELECT fal_id FROM tab_faturas_lotes WHERE fal_ciclo = '$ciclo' AND fap_id = 2)";
		$query = $this->bancodedados->query($query);
		
		if ($this->bancodedados->linhas($query) == 0) {
			$xml->writeElement("customer", "");
			$xml->endElement();
			$content = $xml->outputMemory();
			$this->log($ciclo, strlen($content));
			return $content;
		}
    
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
			$xml->startElement("customer");
				
			$xml->writeElement("customer_acctnbr", $dados["cliente_id"]);
			$xml->writeElement("customer_name", $dados["nome"]);
			$xml->writeElement("customer_fancy_name", $dados["nome_fantasia"]);
			$xml->writeElement("customer_type", $dados["tipo_pessoa"]);
			$xml->writeElement("customer_invoice_type", "1");
			
			$xml->startElement("customer_address");
    	
			$xml->writeElement("address_customer_acctnbr", $dados["cliente_id"]);
			$xml->writeElement("address_customer_CFP_CNPJ", $dados["cpf_cnpj"]);
			$xml->writeElement("address_customer_street", $dados["endereco_logradouro"]);
			$xml->writeElement("address_customer_number", $dados["endereco_numero"]);
			$xml->writeElement("address_customer_complement", $dados["endereco_complemento"]);
			$xml->writeElement("address_customer_neighborhood", $dados["endereco_bairro"]);
			$xml->writeElement("address_customer_countryCode", "");
			$xml->writeElement("address_customer_stateCode", "");
			$xml->writeElement("address_customer_cityCode", "");
			$xml->writeElement("address_customer_zipcode", $dados["endereco_cep"]);
			$xml->writeElement("address_customer_cityAccount", $dados["inscricao_municipal"]);
			$xml->writeElement("address_customer_estateAccount", $dados["inscricao_estadual"]);
			$xml->writeElement("address_customer_id", "");
			$xml->writeElement("address_customer_master_account_flag", "1");
			$xml->writeElement("address_customer_btn", "");
			
			$xml->endElement();
    	
			$query_faturas = "
				SELECT
					tab_faturas.fat_id AS id,
					tab_faturas.coa_id AS cobranca_id,
					tab_faturas.fat_codigo AS codigo,
					tab_faturas.fat_data_emissao AS data_emissao,
					tab_faturas.fat_data_vencimento AS data_vencimento,
					tab_faturas.fat_natureza AS natureza
				FROM
					tab_faturas
				WHERE
					tab_faturas.emp_id = 1 AND
					tab_faturas.fal_id = (SELECT fal_id FROM tab_faturas_lotes WHERE fal_ciclo = '$ciclo' AND fap_id = 2) AND
					tab_faturas.fcl_id = {$dados["id"]}";
			$query_faturas = $this->bancodedados->query($query_faturas);
			
			while (($dados_fatura = $this->bancodedados->resultadoArray($query_faturas)) != false) {
				
				$xml->startElement("invoice");
				
				$xml->writeElement("invoice_customer_acctnbr", $dados["cliente_id"]);
				$xml->writeElement("invoice_id", $dados_fatura["cobranca_id"]);
				$xml->writeElement("invoice_serie", "2");
				$xml->writeElement("invoice_number", $dados_fatura["codigo"]);
				$xml->writeElement("invoice_issue_date", $dados_fatura["data_emissao"]);
				$xml->writeElement("invoice_NSU", "");
				$xml->writeElement("invoice_NSU_date", "");
				$xml->writeElement("invoice_due_date", $dados_fatura["data_vencimento"]);
				$xml->writeElement("invoice_cycle_start_date", "");
				$xml->writeElement("invoice_cycle_end_date", "");
				$xml->writeElement("invoice_natureza_operacao", $dados_fatura["natureza"]);
				
				$query_itens = "
					SELECT
						tab_faturas_itens.fai_quantidade AS quantidade,
						tab_faturas_itens.fai_valor_unitario AS valor_unitario,
						tab_faturas_itens.fai_valor_total AS valor_total,
						tab_faturas_itens.fai_valor_icms AS valor_icms,
						tab_faturas_itens.fai_natureza AS natureza,
						tab_classes_fiscais_tipos.cft_sigla AS sigla
					FROM
						tab_faturas_itens
						INNER JOIN tab_classes_fiscais_tipos ON (tab_faturas_itens.cft_id = tab_classes_fiscais_tipos.cft_id)
					WHERE
						tab_faturas_itens.fat_id = {$dados_fatura["id"]}";
				$query_itens = $this->bancodedados->query($query_itens);
				
				while (($dados_item = $this->bancodedados->resultadoArray($query_itens)) != false) {
					
					$dados_item["valor_unitario"] = number_format($dados_item["valor_unitario"] / 100, 2, ".", "");
					$dados_item["valor_total"] = number_format($dados_item["valor_total"] / 100, 2, ".", "");
					$dados_item["valor_icms"] = number_format($dados_item["valor_icms"] / 100, 2, ".", "");
					
					$xml->startElement("invoices_features_charges");
					
					$xml->writeElement("ifc_invoice_id", $dados_fatura["cobranca_id"]);
					$xml->writeElement("ifc_serie", "2");
					$xml->writeElement("ifc_invoice_number", $dados_fatura["codigo"]);
					$xml->writeElement("ifc_item_code", $dados_item["sigla"]);
					$xml->writeElement("ifc_item_qty", $dados_item["quantidade"]);
					$xml->writeElement("ifc_item_unit_price", $dados_item["valor_unitario"]);
					$xml->writeElement("ifc_item_total_price", $dados_item["valor_total"]);
					$xml->writeElement("ifc_item_icms_value", $dados_item["valor_icms"]);
					$xml->writeElement("ifc_item_btn_did", "");
					$xml->writeElement("ifc_item_btn_type", "");
					$xml->writeElement("ifc_prePaid_postPaid_Flag", "1");
					$xml->writeElement("ifc_natureza_operacao", $dados_item["natureza"]);
				
					$xml->endElement();
										
				}
				
				$xml->endElement();
				
			}
			
			$xml->endElement();
			
		}
    
		$xml->startElement("USOCS");
		
		$query = "
			SELECT
				cft_sigla AS sigla,
				cft_descricao AS descricao,
				cft_tributacao AS tributacao,
				cft_codigo AS codigo
			FROM
				tab_classes_fiscais_tipos
			ORDER BY
				cft_sigla";
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$xml->startElement("item_usoc");
			
			$xml->writeElement("item_code", $dados["sigla"]);
			$xml->writeElement("description", $dados["descricao"]);
			$xml->writeElement("tax_type", $dados["tributacao"]);
			$xml->writeElement("classificacao_fiscal", $dados["codigo"]);
				
			$xml->endElement();
			
		}
    
		$xml->endElement();
		
		$xml->endElement();

		$content = $xml->outputMemory();
		
		$this->log($ciclo, strlen($content));
		
		return $content;
		
	}
	
	function log($ciclo, $tamanho) {
		
		$handle = fopen(dirname(__FILE__).DIRECTORY_SEPARATOR."soap.log", "a");
		fwrite($handle, date("Y-m-d H:i:s")."\t$ciclo\t$tamanho\n");
		
	}
	
}

?>
