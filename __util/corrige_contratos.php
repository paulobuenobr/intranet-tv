<?

require_once ("../global.inc.php");

global $bancodedados;

$util = new util($bancodedados);
$util->executar();

class util {
	
	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	
	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function executar() {
		
		$query = $this->bancodedados->query("
			SELECT
				cor_id AS id,
				cor_parametros AS parametros,
				cli_id AS cliente_id
			FROM
				tab_contratos
			WHERE
				crs_id IN (11, 12, 13, 14, 15, 16, 17, 18) AND
				cor_data_geracao < '2019-07-26'");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			//print "{$dados["id"]}\n";
			
			//print $dados["parametros"]."\n";
			
// 			print "\n\n";
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			
			if (!$dados["parametros"]["parcelamento"] && $dados["parametros"]["tabela_valor_final_instalacao"] > 0) {
				
// 				$dados["parametros"]["parcelamento"] = 1;
// 				$dados["parametros"]["tabela_valor_parcela"] = $dados["parametros"]["tabela_valor_final_instalacao"];
				
// 				$dados["parametros"] = addslashes(serialize($dados["parametros"]));
				
// 				$this->bancodedados->query("UPDATE tab_contratos SET cor_parametros = '{$dados["parametros"]}' WHERE cor_id = {$dados["id"]}");
				
				print "Contrato corrigido: {$dados["cliente_id"]}\n";
				
			} else {
				print "Contrato OK: {$dados["cliente_id"]}\n";
			}
			
		}
		
	}
	
}

?>
