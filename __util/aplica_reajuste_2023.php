<PRE>
<?

require_once("../global.inc.php");

$util = new Reajuste($bancodedados);

$util->reajustaProxies();

class Reajuste {

	private $bancodedados;

	private $dataReajuste = "2024-03-01";

	private $reajusteProxies = array(
		117873 => array("plano" => "Olé Proxy", "valorAtual" => 350000, "valorReajustado" => 400000),
		138554 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		132769 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 235000, "valorReajustado" => 250000),
		141155 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		163273 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		155549 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 150000, "valorReajustado" => 175000),
		124383 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 300000, "valorReajustado" => 300000),
		126968 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		117065 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 275000),
		120333 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 280000),
		115815 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 275000),
		186716 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		117071 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		144751 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		115809 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		143215 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		125018 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 365600, "valorReajustado" => 375000),
		140249 => array("plano" => "Olé Proxy", "valorAtual" => 300000, "valorReajustado" => 270000),
		140222 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		182564 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		115190 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		120265 => array("plano" => "Olé Proxy", "valorAtual" => 350000, "valorReajustado" => 350000),
		126306 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		184194 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		122198 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 365600, "valorReajustado" => 365600),
		181118 => array("plano" => "Olé Proxy", "valorAtual" => 280000, "valorReajustado" => 320000),	
		125020 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 365760, "valorReajustado" => 375000),
		155204 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		156452 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		140352 => array("plano" => "Olé Proxy", "valorAtual" => 250000, "valorReajustado" => 255000),
		157620 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		182603 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		115810 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 150000, "valorReajustado" => 200000),
		115192 => array("plano" => "Olé Proxy", "valorAtual" => 150000, "valorReajustado" => 175000),
		129257 => array("plano" => "Olé Proxy", "valorAtual" => 300000, "valorReajustado" => 300000),
		126508 => array("plano" => "Olé Proxy", "valorAtual" => 235000, "valorReajustado" => 270000),
		119929 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 270000),
		142905 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		163719 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		115800 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 270000),
		117874 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 200000),
		147712 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 180000, "valorReajustado" => 175000),
		150816 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		121067 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 450000, "valorReajustado" => 450000),
		145815 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		119552 => array("plano" => "Olé Proxy", "valorAtual" => 200000, "valorReajustado" => 250000),
		118810 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 270000),
		124380 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 300000, "valorReajustado" => 350000),
		147544 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		124568 => array("plano" => "Olé Proxy", "valorAtual" => 235000, "valorReajustado" => 260000),
		118454 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		120370 => array("plano" => "Olé Proxy", "valorAtual" => 135000, "valorReajustado" => 175000),
		126347 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 200000, "valorReajustado" => 250000),
		127795 => array("plano" => "Olé Proxy", "valorAtual" => 135000, "valorReajustado" => 175000),
		128327 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 135000, "valorReajustado" => 175000),
		154380 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 275000),
		159483 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		132131 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		140461 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		115833 => array("plano" => "Olé Proxy", "valorAtual" => 150000, "valorReajustado" => 175000),
		131028 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		144798 => array("plano" => "Olé Proxy", "valorAtual" => 235000, "valorReajustado" => 235000),
		115207 => array("plano" => "Olé Proxy", "valorAtual" => 135000, "valorReajustado" => 175000),
		143197 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		180125 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		147008 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		120625 => array("plano" => "Olé Proxy", "valorAtual" => 170000, "valorReajustado" => 250000),
		126355 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		130375 => array("plano" => "Olé Proxy", "valorAtual" => 135000, "valorReajustado" => 175000),
		145509 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		171300 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		122190 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 400000, "valorReajustado" => 400000),
		115811 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		151555 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		115605 => array("plano" => "Olé Proxy", "valorAtual" => 1350000, "valorReajustado" => 1500000),
		115804 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		133765 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		135157 => array("plano" => "Olé Proxy", "valorAtual" => 135000, "valorReajustado" => 175000),
		117073 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 290000),
		118342 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		143188 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		127999 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		140445 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		162021 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		134063 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		151950 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		118158 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 285000),
		115202 => array("plano" => "Olé Proxy", "valorAtual" => 350000, "valorReajustado" => 400000),
		149186 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		159434 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 360000),
		115814 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 285000),
		161421 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		178298 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		155216 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 150000, "valorReajustado" => 175000),
		115184 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 350000),
		118343 => array("plano" => "Olé Proxy", "valorAtual" => 350000, "valorReajustado" => 400000),
		160572 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		118635 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 270000),
		115861 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		151959 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		133878 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		120015 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 300000),
		143579 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		141654 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		149363 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		156018 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		154958 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		127539 => array("plano" => "Olé Proxy", "valorAtual" => 135000, "valorReajustado" => 175000),
		129334 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 370000),
		143252 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 375000),
		129275 => array("plano" => "Olé Proxy", "valorAtual" => 200000, "valorReajustado" => 175000),
		180130 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		177824 => array("plano" => "Olé Proxy [70 / 30]", "valorAtual" => 350000, "valorReajustado" => 350000),
		117871 => array("plano" => "Olé Proxy", "valorAtual" => 270000, "valorReajustado" => 290000)
	);

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function reajustaProxies() {
	
		foreach ($this->reajusteProxies as $clienteId => $dados) {
			$this->reajustaAssinatura($clienteId, $dados["plano"], $dados["valorAtual"], $dados["valorReajustado"]);
		}
	
	}
	
	function reajustaAssinatura($clienteId, $plano, $valorAtual, $valorReajustado) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.cli_id = {$clienteId} AND
				tab_planos.pla_nome = '{$plano}' AND
				(tab_assinaturas.ass_data_ativacao <= '2023-01-01' OR tab_assinaturas.cli_id IN (140249, 181118, 120370)) AND
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
				), 0) = '{$valorAtual}'");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->migraAssinatura($dados["id"], $valorReajustado);
			print "OK - {$clienteId} ({$valorAtual} => {$valorReajustado})\n";
		} else {
			print "ERRO NÃO ENCONTRADO - {$clienteId}\n";
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
	
	/*
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
	*/	
 
}

?>
