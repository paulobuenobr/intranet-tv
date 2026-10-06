<?php

// https://oletv.oletelecom.com.br/__util/cancela_contratos.php?dataAtivado=s

require_once("../global.inc.php");

global $bancodedados;

$dataAtivado = empty($_GET["dataAtivado"]) ? false : $_GET["dataAtivado"];
$cancela_contratos = new cancela_contratos($bancodedados);

if ($dataAtivado && ($dataAtivado == "true" || $dataAtivado == "1" || $dataAtivado == "s" || $dataAtivado == "sim")) {
	$cancela_contratos->cancelaContratos($dataAtivado);
} else {
	$cancela_contratos->cancelaContratos();
}

class cancela_contratos
{

	private $bancodedados;

	private $contratos;

	private $origens = "198";

	private $clientes = array(

		array('cliente_id' => '478152', 'cancelamento' =>'01/06/2026'),
		array('cliente_id' => '435897', 'cancelamento' =>'01/06/2026'),
		array('cliente_id' => '716101', 'cancelamento' =>'01/06/2026'),
		array('cliente_id' => '185908', 'cancelamento' =>'01/06/2026'),
		array('cliente_id' => '207339', 'cancelamento' =>'01/06/2026'),

	);

	##################
	### Construtor ###
	##################

	function cancela_contratos($bancodedados)
	{

		$this->bancodedados = $bancodedados;

		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
	}

	################
	### Importar ###
	################

	function cancelaContratos($dataAtivado = false)
	{

		print "<pre>";

		foreach ($this->clientes as $cliente) {
			$contratos = $this->buscaContratos($cliente["cliente_id"]);
			foreach ($contratos as $contrato) {

				$dataCancelamento = $cliente["cancelamento"];
				if ($dataAtivado) {
					$timestamp = strtotime($contrato["data_ativacao"]);
					if ($timestamp !== false) {
						$dataCancelamento = date('d/m/Y', $timestamp);
					}
				}

				$this->contratos->acaoCancelarContrato(
					$this->contratos->buscaDadosContrato($contrato["contrato_id"]),
					$dataCancelamento, 
					"Solicitação interna");
				print "[OK] [{$contrato["contrato_codigo"]}] [{$contrato["cliente_id"]}] {$contrato["cliente_nome"]}: {$dataCancelamento}\n";
			}
		}

		print "</pre>";
	}

	function buscaContratos($cliente_id)
	{

		$clientes_contratos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS contrato_codigo,
				tab_clientes.cli_id AS cliente_id,
				tab_contratos.cor_data_ativacao AS data_ativacao,
				tab_clientes.cli_nome AS cliente_nome
			FROM
				tab_clientes
				INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
			WHERE
				tab_clientes.cli_id = '{$cliente_id}' AND
				tab_clientes.clo_id IN ({$this->origens}) AND
				tab_contratos.crs_id IN (19, 20) AND
				tab_contratos.cor_pai IS NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes_contratos[] = $dados;
		}

		return $clientes_contratos;
	}
}
