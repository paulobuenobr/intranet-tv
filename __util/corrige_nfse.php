<?php

require_once("../global.inc.php");

// Exemplo de uso:
// 1 = true
// 2 = false
// http://oletv.oletelecom.com.br/__util/corrige_nfse.php?cobranca=ID_COBRANCA&gerado=2

global $bancodedados;

$cobranca = empty($_GET["cobranca"]) ? false : $_GET["cobranca"];
$gerado = empty($_GET["gerado"]) ? false : $_GET["gerado"];

if ($cobranca && $gerado) {
	$corrige_nfse = new corrige_nfse($bancodedados);
	$corrige_nfse->corrige($cobranca, $gerado);
} else {
	echo "Faltando parametros.";
}

class corrige_nfse
{

	private $bancodedados;

	##################
	### Construtor ###
	##################

	function corrige_nfse($bancodedados)
	{
		$this->bancodedados = $bancodedados;
	}

	function corrige($cobranca, $gerado)
	{
		if ($gerado == '2') {
			$query = $this->bancodedados->query("UPDATE tab_cobrancas SET gerado_nota = '0' WHERE coa_id = '{$cobranca}';");
		} elseif ($gerado == '1') {
			$query = $this->bancodedados->query("UPDATE tab_cobrancas SET gerado_nota = '1' WHERE coa_id = '{$cobranca}';");
		} else {
			return false;
		}
	}
}
