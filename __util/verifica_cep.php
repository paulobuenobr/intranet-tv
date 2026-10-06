<pre>
<?

ignore_user_abort(1);
set_time_limit(0);

require_once("../global.inc.php");

$util = new util($bancodedados);
$util->executar();


class util {

	private $bancodedados;

	##################
	### Construtor ###
	##################

	function util($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	function executar() {

		$query = "
			SELECT
				tab_clientes.cli_id AS cliente_id,
				tab_ceps.cep_id AS cep_id,
				tab_logradouros.log_nome AS logradouro
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = (IF(tab_clientes.cep_id IS NOT NULL, tab_clientes.cep_id, tab_condominios.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
			WHERE
				tab_clientes.cls_id = 2 AND
				tab_clientes.cof_id = 35 -- AND
				-- tab_ceps.cid_id = 33";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$parametros = $this->buscaParametrosContrato($dados["cliente_id"]);
			
			if ($parametros) {
				
				if (str_replace("-", "", $parametros["cliente_endereco_cep"]) == $dados["cep_id"] &&
					$parametros["cliente_endereco_logradouro"] != $dados["logradouro"]) {
					print "{$dados["cliente_id"]}, {$dados["logradouro"]}, {$parametros["cliente_endereco_logradouro"]}\n";
				}
				
			}
			
		}
		
	}
	
	function buscaParametrosContrato($cliente_id) {
		
		$query = "
			SELECT
				cor_parametros AS parametros
			FROM
				tab_contratos
			WHERE
				tab_contratos.cli_id = $cliente_id AND
				tab_contratos.crs_id IN (19, 20)
			ORDER BY
				tab_contratos.cor_data_ativacao DESC
			LIMIT 1";
		
		$query = $this->bancodedados->query($query);
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return unserialize($dados["parametros"]);
		}
		
	}

}

?>
