<?php

class cron_ativacao_automatica {
	
	private $bancodedados;

	private $publicacao;
	
	private $contratos;
	
	function cron_ativacao_automatica($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
		
	}
	
	##############
	### Ativar ###
	##############
	
	function ativar() {
		
		print "<PRE>\n";
		flush();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id as contrato_id,
				tab_contratos.cor_codigo as codigo,
				tab_contratos.cli_id as cliente_id,
				DATE_FORMAT(tab_contratos.cor_data_geracao, '%d/%m/%Y') AS geracao,
				tab_clientes.cli_nome AS cliente,
				tab_clientes_origens.clo_nome AS origem,
				tab_clientes_iptv.cip_id AS iptv_id
			FROM
				tab_contratos
				INNER JOIN tab_clientes_iptv ON (tab_clientes_iptv.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
			WHERE
				tab_contratos.crs_id IN (11, 12, 13, 14, 15, 16, 17, 18) AND
				tab_contratos.cor_data_geracao >= DATE_SUB(CURRENT_DATE, INTERVAL 45 DAY)
			ORDER BY 
				tab_contratos.cor_data_geracao DESC
			-- LIMIT 100");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$inicio = intval(microtime(true) * 1000000);
			
			$dados_status = $this->publicacao->status($dados["iptv_id"]);

			if (count($dados_status)) {
				
				$data_ativacao = date("d/m/Y", $dados_status[0]["insertion"] / 1000);
								
				print "{$dados["cliente_id"]}\t{$data_ativacao}\n";
				flush();
				
				$this->contratos->buscaDadosItem($dados["contrato_id"]);
				$this->contratos->ativarContrato($data_ativacao);
				
			}
		
			$tempo = intval(microtime(true) * 1000000) - $inicio;

			if ($tempo < 1000000) {
				usleep(1000000 - $tempo);
			}
				
		}
		
		print "</PRE>\n";
		flush();
		
	}
	
}

?>