<?

require_once("../global.inc.php");

$util = new util($bancodedados);
$util->executar();

class util {

  private $bancodedados;
  private $cobrancas_geracao;
	
  ##################
  ### Construtor ###
  ##################

	function util($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
	}
	
	function executar() {
		$query = "
			SELECT
				coa_id AS id,
				cli_id AS cliente_id,
				coa_codigo AS codigo,
				coa_data_vencimento AS data_vencimento,
				coa_parametros AS parametros,
				coa_valor_original AS valor_original,
				coa_valor_bonificacao AS valor_bonificacao,
				cof_id AS formato_id
			FROM
				tab_cobrancas
			WHERE
				coa_data_vencimento >= '2014-10-19' AND
				-- coa_data_vencimento = '2014-11-20' AND
				cof_id IN (27, 28)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["parametros"] = unserialize($dados["parametros"]);
    	
    	$cobranca = array();
      $cobranca["valor_final"] = $dados["valor_original"];
      $cobranca["valor_bonificacao"] = $dados["valor_bonificacao"];
      $cobranca["parametros"] = $dados["parametros"];
    	
      $boleto = $this->cobrancas_geracao->buscaParametrosBoleto($dados["cliente_id"], $dados["codigo"], $cobranca, $dados["formato_id"], $dados["data_vencimento"]);
      
      if ($boleto["linha_digitavel"] != $dados["parametros"]["boleto"]["linha_digitavel"]) {
      	
      	$boleto["data_emissao"] = $dados["parametros"]["boleto"]["data_emissao"];
      	$boleto["data_processamento"] = $dados["parametros"]["boleto"]["data_processamento"];
      	$boleto["competencia"] = $dados["parametros"]["boleto"]["competencia"];
      	
      	$dados["parametros"]["boleto"] = $boleto;
      	$dados["parametros"] = serialize($dados["parametros"]);
      	
      	$this->bancodedados->query("UPDATE tab_cobrancas SET coa_parametros = '{$dados["parametros"]}' WHERE coa_id = {$dados["id"]}");
      	
				print "{$dados["id"]}\t{$dados["data_vencimento"]}\n";
				
      } else {
//      	var_dump($dados["data_vencimento"]);
//      	var_dump($boleto["linha_digitavel"]);
//      	var_dump($dados["parametros"]["boleto"]["linha_digitavel"]);
//      	die();
      }
    }
	}
	
}

?>
