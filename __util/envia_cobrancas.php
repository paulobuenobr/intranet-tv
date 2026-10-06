<?

require_once("../global.inc.php");

$util = new util($bancodedados);
$util->executar();

class util {

  private $bancodedados;
  private $cobrancas;
	
  ##################
  ### Construtor ###
  ##################

	function util($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
	}
	
	function executar() {
		$query = "
			SELECT
				coa_id AS id,
				cli_id AS cliente_id
			FROM
				tab_cobrancas
			WHERE
				col_id IS NULL AND
				cot_id = 1 AND
				coa_enviada = 0 AND
				cof_id IN (34, 35) AND
				(SELECT COUNT(*) FROM tab_cobrancas_remessas_itens WHERE tab_cobrancas_remessas_itens.coa_id = tab_cobrancas.coa_id AND tab_cobrancas_remessas_itens.cri_status = 'REGISTRADA') = 1";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->cobrancas->buscaDadosItem($dados["id"]);
			$this->cobrancas->enviaBoletoAutomatico();
			print "{$dados["id"]}\t{$dados["cliente_id"]}\n";
		}
	}
	
}

?>
