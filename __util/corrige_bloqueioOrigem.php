<?php

// https://oletv.oletelecom.com.br/__util/corrige_bloqueioOrigem.php?origem=632

require_once("../global.inc.php");

global $bancodedados;

$origem = empty($_GET["origem"]) ? false : $_GET["origem"];

if ($origem) {
	$corrige_bloqueio = new corrige_bloqueioOrigem($bancodedados);
	$corrige_bloqueio->corrige($origem);
} else {
	echo "Faltando parametros.";
}

class corrige_bloqueioOrigem
{

	private $bancodedados;

	##################
	### Construtor ###
	##################

	function corrige_bloqueioOrigem($bancodedados)
	{
		$this->bancodedados = $bancodedados;
	}

	function corrige($origem)
	{
		$sql = "SELECT rel_cof_clo_disponivel.* FROM rel_cof_clo_disponivel INNER JOIN tab_cobrancas_formatos ON tab_cobrancas_formatos.cof_id = rel_cof_clo_disponivel.cof_id WHERE clo_id = '{$origem}' AND cfs_id = 1;";
		$query = $this->bancodedados->query($sql);
		if ($this->bancodedados->linhas($query) == 1) {
			$dados = $this->bancodedados->resultadoArray($query);
			if ($dados['cof'] == 1) {
				$query = $this->bancodedados->query("UPDATE tab_clientes_origens SET clo_bloqueio_dias = '0' WHERE clo_id = '{$origem}';");
		
				$query = $this->bancodedados->query("UPDATE tab_clientes SET cli_bloqueio_dias = NULL, cof_id = 1 WHERE clo_id = '{$origem}' AND cls_id = 2 AND cli_bloqueio_dias > 0;");
		
				$query = $this->bancodedados->query("UPDATE tab_cobrancas SET coa_data_bloqueio = NULL WHERE cof_id = 1 AND coa_data_bloqueio IS NOT null AND cot_id = 1 AND cli_id IN (SELECT cli_id FROM tab_clientes WHERE clo_id = '{$origem}');");
			} else {
				return false;
			}
		} else {
			return false;
		}

		return true;
	}
}
