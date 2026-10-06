<?

ignore_user_abort(1);
set_time_limit(0);

require_once("../global.inc.php");

$util = new util($bancodedados);
//$util->executar();

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
				cli_id AS id,
				cli_cpf_cnpj AS cpf_cnpj
			FROM
				tab_clientes
			WHERE
				1
			GROUP BY
				cli_cpf_cnpj
			HAVING
				COUNT(cli_id) > 1
			ORDER BY
				cli_id DESC
			-- LIMIT 10";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	   
			$query_migrar = "
				SELECT
					cli_id AS id
				FROM
					tab_clientes
				WHERE
					cli_cpf_cnpj = '{$dados["cpf_cnpj"]}' AND
					cli_id != {$dados["id"]}";
			$query_migrar = $this->bancodedados->query($query_migrar);
			while (($migrar = $this->bancodedados->resultadoArray($query_migrar)) != false) {
	   
				print "Migrado: {$migrar["id"]} -> {$dados["id"]}<br>";
				
				$this->clientes->migraCliente($migrar["id"], $dados["id"]);

			}
			
		}
    
	}
	
}

?>
