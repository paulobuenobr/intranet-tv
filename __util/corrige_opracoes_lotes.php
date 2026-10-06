<PRE>
<?

ignore_user_abort(true);
set_time_limit(0);

require_once ("../global.inc.php");

global $bancodedados;

$util = new util($bancodedados);
$util->executar();

class util {
	
	private $bancodedados;
	
	private $operacoes_lotes;
	
	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
		$this->operacoes_lotes = controle::instanciaObjeto("operacoes_lotes", $this->bancodedados, null, false);

	}
	
	function executar() {

		$query = $this->bancodedados->query("
			SELECT
				opl_id AS id,
				opl_regras AS regras
			FROM
				tab_operacoes_lotes
			WHERE
				 ols_id = 1 AND
				 oli_id = 2");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["regras"] = unserialize($dados["regras"]);
			
			$filtros = array();
			
			foreach ($dados["regras"]["filtros"] as $regra) {
				
				if ($regra["campo"] == 30) {
					$filtros[] = $regra;
				}
				
			}
			
			$filtros[] = array(
				"campo" => 17,
				"comparacao" => "=",
				"valor" => 1
			);
			
			$dados["regras"]["filtros"] = $filtros;
			
			$dados["regras"]["validades"] = array(array(
				"responsavel" => 3097,
				"inicio" => "",
				"termino" => "",
				"periodo" => -1
			));
			
			//var_dump($dados["regras"]["validades"]); die();
			
			$dados["regras"] = serialize($dados["regras"]);

			$this->bancodedados->query("UPDATE tab_operacoes_lotes SET opl_regras = '{$dados["regras"]}' WHERE opl_id = {$dados["id"]}");
			
			$this->operacoes_lotes->distribuirResponsaveis($dados["id"]);

		}

	}
	
}

?>
</PRE>
