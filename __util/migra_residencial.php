<?

ignore_user_abort(1);
set_time_limit(0);

require_once("../global.inc.php");

$util = new util($bancodedados);
$util->executar();

class util {

	private $bancodedados;
	private $diretorio;
	private $faturas_lotes;
  
	##################
	### Construtor ###
	##################

	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->clientes = controle::instanciaObjeto("clientes", $this->bancodedados, null, false);
		
	}
	
	function executar() {
		
		$query = "
			SELECT
				cli_id AS id
			FROM
				tab_clientes
			WHERE
				con_id IS NOT NULL
			-- LIMIT 100";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	   
			$this->clientes->buscaDadosItem($dados["id"]);
			
			$this->clientes->dados_item["numero"] = $this->clientes->dados_item["numero"] ? $this->clientes->dados_item["numero"] : "NULL";
			
			$this->bancodedados->query("
				UPDATE
					tab_clientes
				SET
					cob_id = NULL,
					con_id = NULL,
					cep_id = {$this->clientes->dados_item["cep_id"]},
					cli_numero = {$this->clientes->dados_item["numero"]}
					WHERE
						cli_id = {$this->clientes->dados_item["id"]}");
			
			print "{$this->clientes->dados_item["id"]}\n";
			
		}
    
	}
	
}

?>
