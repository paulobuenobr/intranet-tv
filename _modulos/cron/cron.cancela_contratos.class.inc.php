<?

class cron_cancela_contratos {
	
	var $bancodedados;
	
	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	##########################
	### Cancelar Contratos ###
	##########################
	
	function cancelarContratos() {
		
		$modulo_contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
		
		$contratos = $this->buscaContratosAguardandoCancelamento();
		
		foreach ($contratos as $contrato) {
			$modulo_contratos->cancelarContrato($contrato["id"]);
		}
		
		$clientes_contratos = $this->buscaClientesContratosInadimplentes();
		
		foreach ($clientes_contratos as $contrato) {
			$data_cancelamento = $this->buscaDataBloqueio($contrato["cliente_id"]);
			if (!$data_cancelamento) {
				$data_cancelamento = date("t/m/Y", strtotime("LAST MONTH"));
			}
			$modulo_contratos->acaoCancelarContrato($modulo_contratos->buscaDadosContrato($contrato["contrato_id"]), $data_cancelamento, "Cliente inadimplente");
		}
		
	}
	
	########################
	### Funções de Busca ###
	########################
	
	function buscaContratosAguardandoCancelamento() {
		
		$lista = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cor_id AS id
			FROM
				tab_contratos
			WHERE
				crs_id = 21 AND
				cor_pai IS NULL AND
				DATE_ADD(cor_data_cancelamento, INTERVAL 1 DAY) <= CURDATE()");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}
		
		return $lista;
		
	}
	
	function buscaDataBloqueio($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(DATE_SUB(saj_inicio, INTERVAL 1 DAY), '%d/%m/%Y') AS data_bloqueio
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sac_id = 1 AND
				sas_id = 5
			ORDER BY
				saj_inicio");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaClientesContratosInadimplentes() {
		
		$clientes_contratos = array ();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cli_id AS cliente_id,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome,
				(SELECT COUNT(tab_clientes_pontos.clp_id) FROM tab_clientes_pontos INNER JOIN rel_ass_cor_estacontido ON (tab_clientes_pontos.ass_id = rel_ass_cor_estacontido.ass_id) WHERE rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id AND tab_clientes_pontos.cps_id IN (1, 2)) AS pontos,
				(SELECT COUNT(tab_clientes_autenticacoes.cau_id) FROM tab_clientes_autenticacoes INNER JOIN rel_ass_cor_estacontido ON (tab_clientes_autenticacoes.ass_id = rel_ass_cor_estacontido.ass_id) WHERE rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id AND tab_clientes_autenticacoes.cas_id IN (1, 2)) AS autenticacoes
			FROM
				tab_contratos
				INNER JOIN tab_planos_servicos ON (tab_contratos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
			WHERE
				tab_contratos.cli_id NOT IN (114676,179719,187932) AND
				tab_contratos.pls_id IN (1) AND
				tab_contratos.crs_id IN (19, 20) AND
				tab_contratos.cor_pai IS NULL AND
				tab_clientes.cli_bloqueio_dias > 0 AND
				(
					SELECT
						COUNT(DISTINCT DATE_FORMAT(primeiras_vias.coa_data_vencimento, '%m/%Y'))
					FROM
						tab_cobrancas primeiras_vias
					WHERE
						primeiras_vias.cli_id = tab_contratos.cli_id AND
						primeiras_vias.coa_pai IS NULL AND
						primeiras_vias.cot_id = 1 AND
						primeiras_vias.coa_data_vencimento < DATE_SUB(CURDATE(), INTERVAL 10 DAY) AND
						(
							SELECT
								COUNT(*)
							FROM
								tab_cobrancas segundas_vias
							WHERE
								segundas_vias.cot_id = 1 AND
								segundas_vias.coa_pai = primeiras_vias.coa_id AND
								segundas_vias.coa_data_vencimento >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
						) = 0
				) > 1");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes_contratos[] = $dados;
		}
		
		return $clientes_contratos;
		
	}
	
}

?>
