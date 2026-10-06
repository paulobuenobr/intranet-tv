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
				cep_id
			FROM
				tab_ceps
			WHERE
				cid_id = 5982 AND
				cep_id LIKE '5520___'");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$cep_corrigido = "55200".substr($dados["cep_id"], 4);
			
			$this->bancodedados->query("INSERT IGNORE INTO tab_ceps SELECT {$cep_corrigido}, log_id, bai_id, cid_id, est_id, cep_exportacao, cep_zona FROM tab_ceps WHERE cep_id = {$dados["cep_id"]}");
			$this->bancodedados->query("UPDATE tab_clientes SET cep_id = {$cep_corrigido} WHERE cep_id = {$dados["cep_id"]}");
			$this->bancodedados->query("UPDATE tab_clientes_cobranca_dados SET cep_id = {$cep_corrigido} WHERE cep_id = {$dados["cep_id"]}");
			$this->bancodedados->query("UPDATE tab_usuarios SET cep_id = {$cep_corrigido} WHERE cep_id = {$dados["cep_id"]}");
			$this->bancodedados->query("UPDATE tab_clientes_origens SET cep_id = {$cep_corrigido} WHERE cep_id = {$dados["cep_id"]}");
			$this->bancodedados->query("DELETE FROM tab_ceps WHERE cep_id = {$dados["cep_id"]}");
						
		}
		
	}
		
}

?>
