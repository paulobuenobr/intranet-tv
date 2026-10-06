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
	
	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function executar() {

		$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
		$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);

		$query = $this->bancodedados->query("
			SELECT
				fat_id AS fatura_id,
				coa_id AS cobranca_id
			FROM
				tab_faturas
			WHERE
				fap_id = 2 AND
				fat_cancelada = 0 AND
				fat_data_emissao BETWEEN '2022-10-01' AND '2022-11-08'");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$retorno = $faturas_lotes->cancelarFatura($dados["fatura_id"]);
			
			if ($retorno == "100") {
				
				$cobrancas->cancelarFatura($dados["fatura_id"]);
				$cobrancas->registraComentario($dados["cobranca_id"], "<b>Ação:</b> Nota Cancelada");
				
			}
			
			print $dados["cobranca_id"]."\n"; flush();
			
		}

	}
	
}

?>
</PRE>
