<PRE>
<?

require_once("../global.inc.php");

$util = new Reajuste($bancodedados);

//$util->reajustaClientes();

class Reajuste {

	private $bancodedados;

	private $dataReajuste = "2022-04-01";

	private $reajusteProxies = array(
		1337 => array("valorAtual" => 100000, "valorReajustado" => 150000),
		1339 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		1342 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		1346 => array("valorAtual" => 200000, "valorReajustado" => 350000),
		1987 => array("valorAtual" => 980000, "valorReajustado" => 1350000),
		2001 => array("valorAtual" => 500000, "valorReajustado" => 650000),
		2204 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		2219 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		2224 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		2229 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		2232 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		2234 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		2275 => array("valorAtual" => 100000, "valorReajustado" => 150000),
		2282 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		5850 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		5853 => array("valorAtual" => 235000, "valorReajustado" => 350000),
		5857 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		6208 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		6209 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		6233 => array("valorAtual" => 200000, "valorReajustado" => 270000),
		6397 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		6729 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		6741 => array("valorAtual" => 235000, "valorReajustado" => 350000),
		7419 => array("valorAtual" => 250600, "valorReajustado" => 270000),
		10644 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		10887 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		10996 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		11571 => array("valorAtual" => 215600, "valorReajustado" => 250000),
		11657 => array("valorAtual" => 135000, "valorReajustado" => 170000),
		11821 => array("valorAtual" => 235000, "valorReajustado" => 270000),
		12775 => array("valorAtual" => 297400, "valorReajustado" => 350000),
		12779 => array("valorAtual" => 166200, "valorReajustado" => 200000),
		13432 => array("valorAtual" => 180000, "valorReajustado" => 270000),
		14149 => array("valorAtual" => 250600, "valorReajustado" => 270000),
		14464 => array("valorAtual" => 250600, "valorReajustado" => 270000),
		15070 => array("valorAtual" => 250600, "valorReajustado" => 270000),
		15135 => array("valorAtual" => 381200, "valorReajustado" => 450000),
		18912 => array("valorAtual" => 365600, "valorReajustado" => 400000)
	);

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function reajustaProxies() {
	
		foreach ($this->reajusteProxies as $assinaturaId => $dados) {
			$this->reajustaAssinatura($assinaturaId, $dados["valorAtual"], $dados["valorReajustado"]);
		}
	
	}
	
	function reajustaAssinatura($assinaturaId, $valorAtual, $valorReajustado) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_mensalidade - IFNULL((
					SELECT
						SUM(IF(tab_descontos_concessoes.dec_mensalidade <= 1, 
							tab_assinaturas.ass_mensalidade * tab_descontos_concessoes.dec_mensalidade, 
							tab_descontos_concessoes.dec_mensalidade
						))
					FROM
						tab_descontos_concessoes
					WHERE
						tab_descontos_concessoes.ass_id  = tab_assinaturas.ass_id AND
						tab_descontos_concessoes.dcs_id = 5
				), 0) AS valor,
				tab_assinaturas_status.ast_id AS statusId,
				tab_assinaturas_status.ast_nome	AS statusNome
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
			WHERE
				tab_assinaturas.ass_id = {$assinaturaId}");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["statusId"] == 2) {
				if ($dados["valor"] == $valorAtual) {
					$this->migraAssinatura($assinaturaId, $valorReajustado);
				} else {
					print "ERRO VALOR - {$assinaturaId} ({$dados["valor"]} => {$valorAtual})\n";
				}
			} else {
				print "ERRO STATUS - {$assinaturaId} ({$dados["statusNome"]})\n";
			}
		}	
		
	}
  
  	function migraAssinatura($assinaturaId, $valorReajustado) {
		
		$query = $this->bancodedados->query("
			SELECT
				cli_id AS clienteId,
				pla_id AS planoId,
				pli_id AS sistemaId,
				ass_parametros AS parametros,
				(SELECT tab_contratos.cor_id FROM rel_ass_cor_estacontido INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id) WHERE rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id AND tab_contratos.cor_pai IS NULL) AS contratoId
			FROM
				tab_assinaturas
			WHERE
				ass_id = {$assinaturaId}");
    
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->bancodedados->transacaoIniciar();
			
			$this->bancodedados->query("
				INSERT INTO
					tab_assinaturas (
						ass_data_cadastro,
						ass_data_fechamento,
						ass_data_confirmacao,
						ass_data_ativacao,
						ass_mensalidade,
						ass_parametros,
						ass_migrada,
						cli_id,
						pla_id,
						pli_id,
						ast_id
					) VALUES (
						'{$this->dataReajuste}',
						'{$this->dataReajuste}',
						'{$this->dataReajuste}',
						'{$this->dataReajuste}',
						{$valorReajustado},
						'{$dados["parametros"]}',
						{$assinaturaId},
						{$dados["clienteId"]},
						{$dados["planoId"]},
						{$dados["sistemaId"]},
						2
					)");
			
			$assinaturaMigradaId = $this->bancodedados->ultimoIdInserido();
			
			$this->bancodedados->query("UPDATE tab_assinaturas SET ast_id = 4, ass_data_cancelamento = DATE_SUB('{$this->dataReajuste}', INTERVAL 1 DAY) WHERE ass_id = {$assinaturaId}");
			$this->bancodedados->query("UPDATE tab_assinaturas SET ass_pai = {$assinaturaMigradaId} WHERE ass_pai = {$assinaturaId}");
			
			$this->bancodedados->query("UPDATE tab_descontos_concessoes SET dcs_id = 6 WHERE ass_id = {$assinaturaId} AND dcs_id = 5");
			
			$this->bancodedados->query("DELETE rel_dec_cor_estacontido FROM rel_dec_cor_estacontido INNER JOIN tab_descontos_concessoes ON (rel_dec_cor_estacontido.dec_id = tab_descontos_concessoes.dec_id) WHERE rel_dec_cor_estacontido.cor_id = {$dados["contratoId"]} AND tab_descontos_concessoes.ass_id = {$assinaturaId}");
			
			$this->bancodedados->query("UPDATE rel_ass_cor_estacontido SET ass_id = {$assinaturaMigradaId} WHERE ass_id = {$assinaturaId} AND cor_id = {$dados["contratoId"]}");
			$this->bancodedados->query("UPDATE rel_ass_cor_estacontido SET ass_id = {$assinaturaMigradaId} WHERE ass_id = {$assinaturaId} AND cor_id = (SELECT cor_id FROM tab_contratos WHERE cor_pai = {$dados["contratoId"]} AND crs_id < 18)");
			
			$this->bancodedados->query("UPDATE rel_saj_ass_contem SET ass_id = {$assinaturaMigradaId} WHERE ass_id = {$assinaturaId}");

			$this->bancodedados->query("UPDATE tab_clientes_comodatos SET ass_id = {$assinaturaMigradaId} WHERE ass_id = {$assinaturaId}");
						
			$this->bancodedados->transacaoTerminar();
			
		}
		
	}
	
	function reajustaClientes() {

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.cli_id AS clienteId,
				tab_assinaturas.ass_id AS assinaturaId,
				tab_assinaturas.ass_mensalidade - IFNULL((
					SELECT
						SUM(IF(tab_descontos_concessoes.dec_mensalidade <= 1, 
							tab_assinaturas.ass_mensalidade * tab_descontos_concessoes.dec_mensalidade, 
							tab_descontos_concessoes.dec_mensalidade
						))
					FROM
						tab_descontos_concessoes
					WHERE
						tab_descontos_concessoes.ass_id  = tab_assinaturas.ass_id AND
						tab_descontos_concessoes.dcs_id = 5
				), 0) AS valor,
				tab_planos.pla_id AS planoId,
				tab_planos.pla_nome AS planoNome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_clientes.clo_id > 1 AND
				tab_assinaturas.pla_id IN (730, 731, 732) AND
				tab_assinaturas.ass_data_ativacao <= '2021-12-31' AND
				tab_assinaturas.ast_id = 2");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$reajuste = 0;
			
			if ($dados["valor"] > 0) {
				if ($dados["planoId"] == 730 && $dados["valor"] < 6990) {
					$reajuste = 6990 - $dados["valor"];
				} elseif ($dados["planoId"] == 731 && $dados["valor"] < 10990) {
					$reajuste = 10990 - $dados["valor"];
				} elseif ($dados["planoId"] == 732 && $dados["valor"] < 12990) {
					$reajuste = 12990 - $dados["valor"];
				}			
			}
			
			if ($reajuste > 0) {
				
				$valorAtual = $dados["valor"] / 100;
				$valorReajustado = $valorAtual + ($reajuste / 100);

				printf("%s\t%s\t%s\t%s\n", $dados["clienteId"], $dados["planoNome"], number_format($valorAtual, 2, ',', '.'), number_format($valorReajustado, 2, ',', '.'));
				
				$this->migraAssinatura($dados["assinaturaId"], $dados["valor"] + $reajuste);

			}
			
		}

	}		
 
}

?>
