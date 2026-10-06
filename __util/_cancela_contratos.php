<?

ini_set("memory_limit", "128M");
set_time_limit(0);

require_once("../global.inc.php");

$cancela_contratos = new cancela_contratos($bancodedados);
$cancela_contratos->cancelaContratos();

class cancela_contratos {

  private $bancodedados;
	private $contratos;

  ##################
  ### Construtor ###
  ##################

	function cancela_contratos($bancodedados) {
		$this->bancodedados = $bancodedados;
    $this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
	}

	################
	### Importar ###
	################

	function cancelaContratos() {
		$clientes_contratos = $this->buscaClientesContratosInadimplentes();
	  if ($clientes_contratos) {
	    print "\n\n";
	    foreach ($clientes_contratos as $contrato) {
				$data_cancelamento = $this->buscaDataBloqueio($contrato["cliente_id"]);
				if (!$data_cancelamento) {
					$data_cancelamento = date("t/m/Y", strtotime("LAST MONTH"));
				}
				//if (DateTime::createFromFormat('!d/m/Y', $data_cancelamento)->getTimestamp() < strtotime("2016-03-01")) {
					print "[OK] {$contrato["cliente_id"]} ({$contrato["servico_nome"]}): $data_cancelamento\n";
				  $this->contratos->acaoCancelarContrato($this->contratos->buscaDadosContrato($contrato["contrato_id"]), $data_cancelamento, "Cliente inadimplente");
				//}
	    }
	  }
	}
	
	function buscaDataBloqueio($cliente_id) {
		$query = "
			SELECT
				DATE_FORMAT(DATE_SUB(saj_inicio, INTERVAL 1 DAY), '%d/%m/%Y') AS data_bloqueio
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sac_id = 1 AND
				sas_id = 5
			ORDER BY
				saj_inicio";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultado($query, 0, 0);
	}

	function buscaClientesContratosInadimplentes() {
	  $query = "
      SELECT
      	tab_contratos.cor_id AS contrato_id,
        tab_contratos.cli_id AS cliente_id,
        (SELECT COUNT(tab_clientes_pontos.clp_id) FROM tab_clientes_pontos INNER JOIN rel_ass_cor_estacontido ON (tab_clientes_pontos.ass_id = rel_ass_cor_estacontido.ass_id) WHERE rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id AND tab_clientes_pontos.cps_id IN (1, 2)) AS pontos,
        (SELECT COUNT(tab_clientes_autenticacoes.cau_id) FROM tab_clientes_autenticacoes INNER JOIN rel_ass_cor_estacontido ON (tab_clientes_autenticacoes.ass_id = rel_ass_cor_estacontido.ass_id) WHERE rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id AND tab_clientes_autenticacoes.cas_id IN (1, 2)) AS autenticacoes,
        tab_planos_servicos.pls_id AS servico_id,
        tab_planos_servicos.pls_nome AS servico_nome
      FROM
      	tab_contratos
        INNER JOIN tab_planos_servicos ON (tab_contratos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_contratos.crs_id IN (19, 20) AND
        tab_contratos.cor_pai IS NULL AND
        tab_contratos.pls_id IN (1, 5, 20, 28, 32, 33) AND
        (
        	SELECT
        		COUNT(DISTINCT DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%m/%Y'))
        	FROM
        		tab_cobrancas
        	WHERE
        		tab_cobrancas.cli_id = tab_contratos.cli_id AND
        		tab_cobrancas.coa_pai IS NULL AND
        		tab_cobrancas.cot_id = 1 AND
        		tab_cobrancas.coa_data_vencimento < DATE_SUB(CURDATE(), INTERVAL 10 DAY)
        ) > 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["pontos"] || $dados["autenticacoes"] || $dados["servico_id"] == 20) {
        $clientes_contratos[] = $dados;
      } else {
      	print "[ERRO] {$dados["cliente_id"]} ({$dados["servico_nome"]})\n";
      }
    }
    return $clientes_contratos;
	}

}

?>
