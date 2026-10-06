<?

set_time_limit(0);
ini_set("memory_limit", "128M");

require_once("../global.inc.php");

$util = new util($bancodedados);
$util->executar();

class util {

	private $bancodedados;
	private $iptv;

	##################
	### Construtor ###
	##################

	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, null, false);
		
	}

	################
	### Importar ###
	################

	function executar() {
		
		$query = "
			SELECT
				cor_id AS contrato_id,
				crs_id AS status_id
			FROM
				tab_contratos
			WHERE
				cor_pai IS NULL AND
				crs_id != 24 -- AND
				-- (SELECT COUNT(*) FROM tab_clientes_iptv WHERE tab_clientes_iptv.cor_id = tab_contratos.cor_id) = 0";
			
		$query = $this->bancodedados->query($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->iptv->insereIptv($dados["contrato_id"]);
			
			//if ($dados["status_id"] == 22) {
			//	$this->iptv->cancelaIptv($dados["contrato_id"]);
			//}
			
		}
		
	}
	
}

?>
