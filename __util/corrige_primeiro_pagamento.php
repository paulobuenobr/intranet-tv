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
		
		$this->cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);

	}
	
	function executar() {

		$query = $this->bancodedados->query("
			SELECT
				coa_id AS cobranca_id
			FROM
				tab_cobrancas
			WHERE
				 cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1) AND
				 coa_data_processamento >= '2023-04-11'
			-- LIMIT 100");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->cobrancas->atualizaComissaoPrimeiroPagamento($dados["cobranca_id"], true);

		}

	}
	
}

?>
</PRE>
