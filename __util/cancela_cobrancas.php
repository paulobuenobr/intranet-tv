<?php

// https://oletv.oletelecom.com.br/__util/cancela_cobrancas.php?dataAtivado=s

require_once("../global.inc.php");

global $bancodedados;

$dataAtivado = empty($_GET["dataAtivado"]) ? false : $_GET["dataAtivado"];
$cancela_cobrancas = new cancela_cobrancas($bancodedados);

$podeCancelar = $_GET["podeCancelar"] ?? false;

if (!$podeCancelar || !($podeCancelar == "true" || $podeCancelar == "1" || $podeCancelar == "s" || $podeCancelar == "sim")) {
	print "<pre>";
	print "[ERRO] A variável 'podeCancelar' deve ser definida como 'true', '1', 's' ou 'sim' para permitir o cancelamento das cobranças.\n";
	print "</pre>";
	exit;
}

if ($dataAtivado && ($dataAtivado == "true" || $dataAtivado == "1" || $dataAtivado == "s" || $dataAtivado == "sim")) {
	$cancela_cobrancas->cancelaCobrancas($dataAtivado);
} else {
	$cancela_cobrancas->cancelaCobrancas();
}

class cancela_cobrancas
{

	private $bancodedados;

	private $cobrancas;

	private $origens = "560,521";

	private $clientes = array(

		array('cliente_id' => '1040972', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1017155', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1098556', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1100983', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1118680', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1120066', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '740964', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1124236', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1124248', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1019072', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1124650', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '653573', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1092808', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1096555', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1125109', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1126426', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '733616', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1126090', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1127509', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1128214', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '849207', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1128523', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1130083', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1128556', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1130419', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1133611', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1133743', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1005856', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1123063', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1133746', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1131070', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1132600', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1071217', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '769622', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1123000', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1137392', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1141061', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1036277', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '800677', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '756655', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '1133683', 'cancelamento' =>'26/02/2026'),
		array('cliente_id' => '813422', 'cancelamento' =>'26/02/2026'),

	);

	##################
	### Construtor ###
	##################

	function cancela_cobrancas($bancodedados)
	{

		$this->bancodedados = $bancodedados;

		$this->cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
	}

	################
	### Importar ###
	################

	function cancelaCobrancas($dataAtivado = false)
	{

		print "<pre>";

		foreach ($this->clientes as $cliente) {
			$cobrancas = $this->buscaCobrancas($cliente["cliente_id"]);
			foreach ($cobrancas as $cobranca) {
				$dataCancelamento = $cliente["cancelamento"];
				$this->cobrancas->cancelarCobranca($cobranca["cobranca_id"], "Solicitação interna");
				print "[OK] [{$cobranca["cobranca_id"]}] [{$cobranca["cliente_id"]}] {$cobranca["cliente_nome"]}: {$dataCancelamento}\n";
			}
		}

		print "</pre>";
	}

	function buscaCobrancas($cliente_id)
	{

		$clientes_cobrancas = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome
			FROM
				tab_clientes
				INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
			WHERE
				tab_clientes.cli_id = '{$cliente_id}' AND
				tab_clientes.clo_id IN ({$this->origens}) AND
				tab_cobrancas.cot_id = 1");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes_cobrancas[] = $dados;
		}

		return $clientes_cobrancas;
	}
}
