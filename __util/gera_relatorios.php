<?

ignore_user_abort(1);
set_time_limit(0);

require_once("../global.inc.php");

$regera_faturas_ciclos = new gera_faturas_cobrancas($bancodedados);
$regera_faturas_ciclos->gerar();


class gera_faturas_cobrancas {

  private $bancodedados;
	private $cobrancas_retornos;
	private $diretorio;
	
  ##################
  ### Construtor ###
  ##################

	function gera_faturas_cobrancas($bancodedados) {
		$this->bancodedados = $bancodedados;
    $this->cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
    $this->diretorio = "/usr/local/www/apache24/data/intranet/relatorios";
	}
	
	function gerar() {
		$query = "
			SELECT
				coe_id AS id,
				DATE_FORMAT(coe_data_publicacao, '%Y') AS ano_publicacao,
				DATE_FORMAT(coe_data_publicacao, '%m') AS mes_publicacao,
				DATE_FORMAT(coe_data_publicacao, '%d-%m-%Y') AS data_publicacao,
				cof_nome AS formato
			FROM
				tab_cobrancas_retornos
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_retornos.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				coe_data_publicacao  BETWEEN '2012-11-01' AND '2014-10-31' AND 
				(SELECT COUNT(*) FROM rel_coa_coe_estacontido WHERE tab_cobrancas_retornos.coe_id = rel_coa_coe_estacontido.coe_id) > 0
			ORDER BY
				coe_data_publicacao DESC
			-- LIMIT 50";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["formato"] = $this->limpaNome($dados["formato"]) ;
    	
    	$diretorio = "{$this->diretorio}/{$dados["formato"]}/{$dados["ano_publicacao"]}/{$dados["mes_publicacao"]}";
    	
    	if (!file_exists($diretorio)) {
    		mkdir($diretorio, 777, true);
    	}
    	
    	$filename = "$diretorio/{$dados["data_publicacao"]}.xlsx";
    	
    	if (!file_exists($filename)) {
	    	$this->cobrancas_retornos->buscaDadosItem($dados["id"]);
	    	$conteudo = $this->cobrancas_retornos->geraPlanilhaExportacao();
	    	file_put_contents($filename, $conteudo);
    	}
		
		gc_collect_cycles();
    	
    }
	}
		
  function limpaNome($nome) {
  	$nome = strtr($nome, "ñÑçÇáàâãäªÁÀÂÃÄéèêëÉÈÊËíìîïÍÌÎÏóòôõö°ºÓÒÔÕÖúùûüÚÙÛÜ",
  	                     "nncCaaaaaaAAAAAeeeeEEEEiiiiIIIIoooooooOOOOOuuuuUUUU");
    $nome = preg_replace("/[^a-zA-Z]/", "", $nome);
    return trim($nome);
  }
	
	

	
}

?>
