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
    $this->faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
    $this->diretorio = "/usr/local/www/apache24/data/intranet/__util/faturas";
	}
	
	function executar() {
		
		$query = "
			SELECT
				fat_id AS id,
				LPAD(fat_numero, 5, '0') AS numero,
				fat_codigo AS codigo,
				fat_serie AS serie,
				fal_ciclo AS ciclo,
				DATE_FORMAT(fat_data_emissao, '%m') AS mes,
				DATE_FORMAT(fat_data_emissao, '%Y') AS ano
			FROM
				tab_faturas
				INNER JOIN tab_faturas_lotes ON (tab_faturas.fal_id = tab_faturas_lotes.fal_id)
			WHERE
				tab_faturas.fat_id IN (SELECT fat_id FROM tab_faturas_itens WHERE cft_id = 15 ) AND
				tab_faturas.fat_cancelada = 0
			ORDER BY
				ano, mes, numero
			-- LIMIT 10";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
		  $diretorio = "{$this->diretorio}/{$dados["ano"]}/{$dados["mes"]}";
		  
    	$filename = "$diretorio/{$dados["numero"]}.pdf";
    	
    	if (!file_exists($filename)) {

	    	$conteudo = $this->faturas_lotes->pegaFaturaPdf($dados["ciclo"], $dados["codigo"], $dados["serie"]);
	    	
	    	if ($conteudo) {
	    	
		    	if (!file_exists($diretorio)) {
		    		mkdir($diretorio, 777, true);
		    	}
		    	
		    	file_put_contents($filename, $conteudo);
		    	
	    	}
    		
    	}
    	
    }
    
	}
	
}

?>
