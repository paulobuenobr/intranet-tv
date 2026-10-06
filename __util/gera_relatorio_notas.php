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
			
		print "Modelo 21\n\n";

		$query = $this->bancodedados->query("
			SELECT
				SUM((SELECT SUM(fai_valor_total) FROM tab_faturas_itens WHERE tab_faturas.fat_id = tab_faturas_itens.fat_id)) AS total,
				SUM((SELECT SUM(fai_valor_icms) FROM tab_faturas_itens WHERE tab_faturas.fat_id = tab_faturas_itens.fat_id)) AS icms,
				SUM((SELECT SUM(fco_valor_juro) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas.coa_id = tab_faturas_lotes_cobrancas.coa_id)) AS juros
			FROM
				tab_faturas
			WHERE
				fap_id = 2 AND
				fat_cancelada = 0 AND
				fat_data_emissao BETWEEN DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m-01') AND LAST_DAY(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			print "Total: ".number_format($dados["total"] / 100, 2, ",", ".")."\n";
			print "ICMS: ".number_format($dados["icms"] / 100, 2, ",", ".")."\n";
			print "Juros: ".number_format($dados["juros"] / 100, 2, ",", ".")."\n";
			
		}
		
		print "\nNota Fatura\n\n";
		
		$query = $this->bancodedados->query("
			SELECT
				SUM((SELECT SUM(fai_valor_total) FROM tab_faturas_itens WHERE tab_faturas.fat_id = tab_faturas_itens.fat_id)) AS total,
				SUM((SELECT SUM(fco_valor_juro) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas.coa_id = tab_faturas_lotes_cobrancas.coa_id)) AS juros
			FROM
				tab_faturas
			WHERE
				fap_id = 1 AND
				fat_cancelada = 0 AND
				fat_data_emissao BETWEEN DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m-01') AND LAST_DAY(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			print "Total: ".number_format($dados["total"] / 100, 2, ",", ".")."\n";
			print "Juros: ".number_format($dados["juros"] / 100, 2, ",", ".")."\n";
			
		}

	}
	
}

?>
</PRE>
