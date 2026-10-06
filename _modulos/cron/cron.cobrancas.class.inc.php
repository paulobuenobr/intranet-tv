<?

class cron_cobrancas {

	var $bancodedados;

	function __construct($bancodedados) {
	  
		$this->bancodedados = $bancodedados;

	}
  
	function enviaCobrancas(){

		if (date("d") < 20) {
			print "Dia atual (".date("d").") é menor que 20. Envio não executado.\n";
			return;
		}
	  
		$enviados = 0;
		$erros = array();
		$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id
			FROM
				tab_cobrancas_lotes
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_lotes.cof_id = tab_cobrancas_formatos.cof_id)
				INNER JOIN tab_cobrancas ON (tab_cobrancas_lotes.col_id = tab_cobrancas.col_id)
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_cobrancas.cot_id = 1 AND
				tab_cobrancas.cop_id IN (1, 2) AND
				tab_cobrancas.coa_aguardando_registro = 0 AND
				tab_cobrancas.coa_enviada = 0 AND
				((tab_cobrancas_formatos.cft_id = 2 AND
				  tab_clientes.cli_copia_cobranca = 1) OR
				 tab_cobrancas_formatos.cft_id = 3) AND
				(SELECT COUNT(tab_cobrancas_filhas.coa_id) FROM tab_cobrancas AS tab_cobrancas_filhas WHERE tab_cobrancas_filhas.coa_pai = tab_cobrancas.coa_id) = 0 AND
				tab_cobrancas_lotes.col_vencimento = DATE_ADD('".date("Y")."-".(date("m"))."-01"."', INTERVAL 1 MONTH)
			ORDER BY
				tab_cobrancas.coa_data_vencimento");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$cobrancas->buscaDadosItem($dados["id"]);
			
			$emails = $cobrancas->buscaContatosEmails($cobrancas->dados_item["cliente_id"]);
			
			if (count($emails) > 0) {
				if ($cobrancas->enviaBoleto($emails, null, null, false, true) === true) {
					$enviados++;
				} else {
					$erros[] = $cobrancas->dados_item["codigo"];
				}
				sleep(2 * count($emails));
			}
			
		}

		print "<PRE>\n";
		print "Cobranças enviadas: $enviados\n";
		print "\n";
		if (count($erros)) {
			print "Cobranças não enviadas (".count($erros)."):\n";
			print "\n";
			for ($i = 0; $i < count($erros); $i++) {
				print "\t{$erros[$i]}\n";
			}
		}

	}
  
	function cancelaBoletosAntigos() {
		
		$query = "
			UPDATE
				tab_cobrancas
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			SET
				coa_aguardando_registro = 1
			WHERE
				tab_cobrancas_formatos.cof_remessa IS NOT NULL AND
				tab_cobrancas.coa_data_geracao BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), INTERVAL 15 DAY) AND
				tab_cobrancas.cot_id = 1 AND
				(
					SELECT
						COUNT(*)
					FROM
						tab_cobrancas_remessas_itens
					WHERE
						tab_cobrancas_remessas_itens.coa_id = tab_cobrancas.coa_id AND
						tab_cobrancas_remessas_itens.cri_tipo = 'BAIXA'
				) = 0 AND
					(
					SELECT
						COUNT(*)
					FROM
						tab_cobrancas_remessas_itens
					WHERE
						tab_cobrancas_remessas_itens.coa_id = tab_cobrancas.coa_id AND
						tab_cobrancas_remessas_itens.cri_status = 'REGISTRADA'
				) > 0";
		$this->bancodedados->query($query);
		
	}
  
}

?>
