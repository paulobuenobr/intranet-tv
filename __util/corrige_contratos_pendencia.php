<PRE>
<?

require_once ("../global.inc.php");

global $bancodedados;

$util = new util($bancodedados);
$util->executar();

class util {
	
	private $bancodedados;
	
	private $contratos;
	
	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
		$this->cobrancas = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);

		
	}
	
	function executar() {
		
		$this->bancodedados->transacaoIniciar();

		$query = $this->bancodedados->query("
			SELECT
				cor_id AS id,
				cor_codigo AS codigo,
				cli_id AS cliente_id,
				crs_id AS status_id
			FROM
				tab_contratos
			WHERE
				crs_id IN (19, 20)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$possuiContrato = $this->possuiContrato($dados["codigo"]);
			
			if ($dados["status_id"] == 19 && $possuiContrato) {
				
				print "{$dados["cliente_id"]} {$dados["codigo"]} SEM PENDÊNCIA\n";
				
				$this->bancodedados->query("UPDATE tab_contratos SET crs_id = 20 WHERE cor_id = '{$dados["id"]}'");
				
			} elseif ($dados["status_id"] == 20 && !$possuiContrato) {
				
				print "{$dados["cliente_id"]} {$dados["codigo"]} COM PENDÊNCIA\n";
				
				$this->bancodedados->query("UPDATE tab_contratos SET crs_id = 19 WHERE cor_id = '{$dados["id"]}'");
				
			}
			
		}
		
		$this->bancodedados->transacaoTerminar();

	}
	
	function possuiContrato($codigo) {

		return file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$codigo} Telecom"))) ||
			file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$codigo} Telecom")))) ||
			file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$codigo} Telecom"))));

	}
	
}

?>
