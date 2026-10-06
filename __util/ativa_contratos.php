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
	
	private $publicacao;
	
	private $contratos;
	
	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
		
	}
	
	function executar() {
		
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
				crs_id IN (11, 12, 13, 14, 15, 16, 17, 18) AND
				(SELECT COUNT(*) FROM tab_clientes_comodatos WHERE tab_clientes_comodatos.cor_id = tab_contratos.cor_id AND clc_mac_address IS NOT NULL) > 0
			GROUP BY
				tab_contratos.cor_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados_status = $this->publicacao->status($dados["iptv_id"]);
			
			print "{$dados["cliente_id"]}\t{$dados["cliente"]}\t{$dados["origem"]}\t{$dados["codigo"]}\t{$dados["geracao"]}";
			
			if (file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$dados["codigo"]} Telecom")))) {
				print "\tSIM";
			} else {
				print "\tNÃO";
			}
			
			if (count($dados_status)) {
				
				$data_ativacao = substr($dados_status[0]["insertion"], 0, 11);
				
				$dados_status[0]["insertion"] = date("d/m/Y", strtotime($dados_status[0]["insertion"]));
				$dados_status[0]["lastSeenDate"] = $dados_status[0]["lastSeenDate"] ? date("d/m/Y", strtotime($dados_status[0]["lastSeenDate"])) : "";
				
				if (strtotime($data_ativacao) < strtotime("2020-02-01")) {
					$data_ativacao = "2020-02-01";
				}
				
				$data_ativacao = date("d/m/Y", strtotime($data_ativacao));
				
				print "\t{$dados_status[0]["insertion"]}\t{$dados_status[0]["lastSeenDate"]}\t{$data_ativacao}";
				
				$this->contratos->buscaDadosItem($dados["contrato_id"]);
 				$this->contratos->ativarContrato($data_ativacao);
				
			}
			
			print "\n";
			
			
		}
		
	}
	
}

?>
</PRE>
