<?php

class cron_anulacao_automatica {
	
	private $bancodedados;
	
	private $contratos;
	
	function cron_anulacao_automatica($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
		
	}
	
	##############
	### Anular ###
	##############
	
	function anular() {
		
		print "<PRE>\n";
		
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id as contrato_id,
				tab_contratos.cli_id AS cliente_id
			FROM
				tab_contratos
				LEFT OUTER JOIN tab_clientes ON (tab_clientes.cli_id = tab_contratos.cli_id)
			WHERE
				tab_clientes.clo_id NOT IN (1) AND
				crs_id IN (11, 12, 13, 14, 15, 16, 17) AND
				pls_id IN(1 , 3) AND
				cor_data_geracao < DATE_SUB(CURRENT_DATE, INTERVAL 45 DAY)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->contratos->buscaDadosItem($dados["contrato_id"]);
			
			if ($this->contratos->dados_item) {
				$this->contratos->anularContrato($this->contratos->dados_item, 16);
			}
			
			print $dados["cliente_id"]."\n";
			flush();
			
		}
		
		print "</PRE>\n";
		
	}
	
}

?>