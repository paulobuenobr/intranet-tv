<?php

// https://oletv.oletelecom.com.br/__util/corrige_formatoCobranca.php?origem=632

require_once("../global.inc.php");

global $bancodedados;

$corrige_formatoCobranca = new corrige_formatoCobranca($bancodedados);

$atualizaDemais = empty($_GET["atualiza"]) ? false : $_GET["atualiza"];

if ($atualizaDemais == "true" || $atualizaDemais == "1" || $atualizaDemais === true || $atualizaDemais === 1 || $atualizaDemais === "on" || $atualizaDemais === "s") {
	$corrige_formatoCobranca->corrige(true);
} else {
	$corrige_formatoCobranca->corrige();
}
$corrige_formatoCobranca->corrige($atualizaDemais);

class corrige_formatoCobranca
{

	private $bancodedados;

	##################
	### Construtor ###
	##################

	function corrige_formatoCobranca($bancodedados)
	{
		$this->bancodedados = $bancodedados;
	}

	function corrige($atualizaDemais = false)
	{

		$this->bancodedados->query("
			SET SQL_SAFE_UPDATES = 0;
			UPDATE tab_clientes_origens clo
			JOIN (
				SELECT clo_id, COUNT(*) AS qtd
				FROM rel_cof_clo_disponivel
				GROUP BY clo_id
			) r ON r.clo_id = clo.clo_id
			SET clo.clo_cobranca_carteira = 
				CASE 
					WHEN r.qtd = 1 THEN 1
					ELSE 0
				END;
			SET SQL_SAFE_UPDATES = 1;");

		if ($atualizaDemais) {
			$sql = "SELECT rel_cof_clo_disponivel.* FROM rel_cof_clo_disponivel INNER JOIN tab_cobrancas_formatos ON tab_cobrancas_formatos.cof_id = rel_cof_clo_disponivel.cof_id WHERE cfs_id = 1;";
			$query = $this->bancodedados->query($sql);
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($dados['cof_id'] == 1) {

					$query = $this->bancodedados->query("UPDATE tab_clientes_origens SET clo_bloqueio_dias = '0', clo_cobranca_carteira = '1' WHERE clo_id = '{$dados['clo_id']}';");
					
					$query = $this->bancodedados->query("UPDATE tab_clientes SET cli_bloqueio_dias = NULL, cof_id = 1 WHERE clo_id = '{$dados['clo_id']}' AND cls_id = 2 AND cli_bloqueio_dias > 0;");
					
					$query = $this->bancodedados->query("UPDATE tab_cobrancas SET coa_data_bloqueio = NULL WHERE cof_id = 1 AND coa_data_bloqueio IS NOT NULL AND cot_id = 1 AND cli_id IN (SELECT cli_id FROM tab_clientes WHERE clo_id = '{$dados['clo_id']}');");
					
				} else {

					$query = $this->bancodedados->query("UPDATE tab_clientes_origens SET clo_bloqueio_dias = '15', clo_cobranca_carteira = '0' WHERE clo_id = '{$dados['clo_id']}';");
				}
			}
		}
	}
}
