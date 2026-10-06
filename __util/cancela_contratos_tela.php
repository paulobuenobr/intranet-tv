<?php

require_once("../global.inc.php");

global $bancodedados;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$origens_raw = trim($_POST["origens"]);
	$origens_array = array_map('trim', explode(',', $origens_raw));
	$origens_array = array_filter($origens_array, 'is_numeric');
	$origens = implode(',', $origens_array);

	$data_raw = trim($_POST["data"]);
	$data = date("d/m/Y", strtotime(str_replace('/', '-', $data_raw)));

	$clientes_raw = trim($_POST["clientes"]);
	$clientes_linhas = array_filter(array_map('trim', preg_split('/[\n,;]+/', $clientes_raw)));
	$clientes = array();
	foreach ($clientes_linhas as $linha) {
		$clientes[] = array('cliente_id' => $linha, 'cancelamento' => $data);
	}

	$dataAtivado = !empty($_POST["dataAtivado"]);

	$cancela = new cancela_contratos_tela($bancodedados, $origens, $clientes);

	if ($dataAtivado) {
		$cancela->cancelaContratos(true);
	} else {
		$cancela->cancelaContratos();
	}
}

class cancela_contratos_tela
{

	/** @var object $bancodedados */
	private $bancodedados;
	/** @var contratos $contratos */
	private $contratos;
	/** @var string $origens */
	private $origens;
	/** @var array $clientes */
	private $clientes;

	##################
	### Construtor ###
	##################

	/** 
	 * Construtor da classe cancela_contratos_tela
	 * @param object $bancodedados
	 * @param string $origens
	 * @param array $clientes
	 */
	function cancela_contratos_tela($bancodedados, $origens, $clientes)
	{
		$this->bancodedados = $bancodedados;
		$this->origens = $origens;
		$this->clientes = $clientes;
		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
	}

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

				$this->contratos->acaoCancelarContrato($this->contratos->buscaDadosContrato($contrato["contrato_id"]), $dataCancelamento, "Solicitação interna");
				
				print "[OK] [{$contrato["contrato_codigo"]}] [{$contrato["cliente_id"]}] {$contrato["cliente_nome"]}: {$dataCancelamento}\n";
			}
		}

		print "</pre>";
	}

	/**
	 * Busca os contratos do cliente
	 * @param int $cliente_id
	 * @return array
	 */
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

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Cancelar Contratos</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 40px; }
		label { display: block; margin-top: 15px; font-weight: bold; }
		input[type="text"], textarea { width: 400px; padding: 8px; margin-top: 5px; }
		textarea { height: 200px; }
		button { margin-top: 20px; padding: 10px 20px; background: #c0392b; color: #fff; border: none; cursor: pointer; font-size: 14px; }
		button:hover { background: #e74c3c; }
		.checkbox-label { display: inline; font-weight: normal; margin-left: 5px; }
	</style>
</head>
<body>

<h2>Cancelar Contratos por Tela</h2>

<form method="POST">

	<label for="origens">Origens (separadas por vírgula)</label>
	<input type="text" id="origens" name="origens" placeholder="Ex: 533, 534" required>

	<label for="data">Data de Cancelamento</label>
	<input type="text" id="data" name="data" placeholder="DD/MM/AAAA" required>

	<label for="clientes">Clientes (um ID por linha)</label>
	<textarea id="clientes" name="clientes" placeholder="577343&#10;577349&#10;594645" required></textarea>

	<div style="margin-top: 15px;">
		<input type="checkbox" id="dataAtivado" name="dataAtivado" value="1">
		<label class="checkbox-label" for="dataAtivado">Usar data de ativação do contrato como data de cancelamento</label>
	</div>

	<button type="submit">Cancelar Contratos</button>

</form>

</body>
</html>
