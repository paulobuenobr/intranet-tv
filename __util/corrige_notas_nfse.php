<?php

require_once("../global.inc.php");

// Exemplo de uso:
// 1 = true
// 2 = false
// http://oletv.oletelecom.com.br/__util/corrige_notas_nfse.php?cobranca=IDS_COBRANCA

// Parametros:
// cobranca = IDS_COBRANCA
// status = 1 (Cancelada) | 2 (Aguardando) | 3 (Enviado) | 4 (Emitida) | 5 (Erro)
// deleta_nota = 1 (Deleta a nota)
// todas_para_deletar = 1 (Deleta todas as notas)
// gerado_nota = 1 (Corrige o status da nota)

// Exemplo de uso:

// Corrige o status da nota para erro e gerada nao nota
// http://oletv.oletelecom.com.br/__util/corrige_notas_nfse.php?cobranca=IDS_COBRANCA&status=1
// http://localhost/intranet/intranet-tv/__util/corrige_notas_nfse.php?cobranca=IDS_COBRANCA&status=1
// http://localhost/intranet/intranet-tv/__util/corrige_notas_nfse.php?cobranca=8331689&status=1


// Corrige o status da nota para nota emitida e gerada nota
// http://oletv.oletelecom.com.br/__util/corrige_notas_nfse.php?cobranca=IDS_COBRANCA&status=0&gerado_nota=1
// http://localhost/intranet/intranet-tv/__util/corrige_notas_nfse.php?cobranca=IDS_COBRANCA&status=0&gerado_nota=1


// Deleta a nota especifica de uma cobranca
// http://oletv.oletelecom.com.br/__util/corrige_notas_nfse.php?cobranca=IDS_COBRANCA&&deleta_nota=1
// http://localhost/intranet/intranet-tv//corrige_notas_nfse.php?cobranca=IDS_COBRANCA&&deleta_nota=1


// Deleta todas as notas nao geradas
// http://oletv.oletelecom.com.br/__util/corrige_notas_nfse.php?todas_para_deletar=1
// http://localhost/intranet/intranet-tv/__util/corrige_notas_nfse.php?todas_para_deletar=1

global $bancodedados;

/**
 * Aceita 1, sim, s, y, yes, true (case-insensitive) como verdadeiro; qualquer outra coisa = false.
 * @param string $valor
 * @return boolean
 */
function parametroVerdadeiro($valor) {
	if ($valor === null || $valor === false || $valor === '') {
		return false;
	}
	return in_array(strtolower(trim((string) $valor)), array("1", "sim", "s", "y", "yes", "true"), true);
}

$cobranca = empty($_GET["cobranca"]) ? false : $_GET["cobranca"];
$corrigeStatus = empty($_GET["status"]) ? false : parametroVerdadeiro($_GET["status"]);
$corrigeGeradoNota = empty($_GET["gerado_nota"]) ? false : parametroVerdadeiro($_GET["gerado_nota"]);
$deletaNota = empty($_GET["deleta_nota"]) ? false : parametroVerdadeiro($_GET["deleta_nota"]);
$todasParaDeletar = empty($_GET["todas_para_deletar"]) ? false : parametroVerdadeiro($_GET["todas_para_deletar"]);

if (!empty($_GET["status"]) && $cobranca) {
	$corrige_notas_nfse = new corrige_notas_nfse($bancodedados);
	$corrige_notas_nfse->corrige_notaStatus($cobranca, $corrigeStatus);
} elseif ($corrigeStatus && $cobranca && $corrigeGeradoNota) {
	$corrige_notas_nfse = new corrige_notas_nfse($bancodedados);
	$corrige_notas_nfse->corrige_notaStatus($cobranca, $corrigeStatus, $corrigeGeradoNota);
} elseif ($deletaNota && $cobranca) {
	$corrige_notas_nfse = new corrige_notas_nfse($bancodedados);
	$corrige_notas_nfse->deletaNota($cobranca);
} elseif ($todasParaDeletar) {
	$corrige_notas_nfse = new corrige_notas_nfse($bancodedados);
	$corrige_notas_nfse->deletaTodasNotas();
} elseif ($cobranca) {
	$corrige_notas_nfse = new corrige_notas_nfse($bancodedados);
	$corrige_notas_nfse->corrige($cobranca);
} else {
	echo "Faltando parametros.";
}


class corrige_notas_nfse
{

	/**
	 * @var object $bancodedados
	 */
	private $bancodedados;

	##################
	### Construtor ###
	##################
	/**
	 * Construtor
	 * @param object $bancodedados
	 * @return void
	 */
	function corrige_notas_nfse($bancodedados)
	{
		$this->bancodedados = $bancodedados;
	}

	/**
	 * Corrige a nota
	 * @param int $cobranca
	 * @return void
	 */
	function corrige($cobranca){
		$dadosDaNota = $this->buscaNota($cobranca);

		foreach($dadosDaNota as $nota) {
			$nfse = controle::instanciaObjeto("nfse", $this->bancodedados);
			$nfse->CancelarNotaSJSC($nota["id"], $nota["numero"]);

			$cobranca_id = $nota['coa_id'];
			$this->bancodedados->query("UPDATE tab_cobrancas SET gerado_nota = '0' WHERE coa_id = '{$cobranca_id}';");
		}
		
	}

	/**
	 * Busca a nota
	 * @param int $cobranca
	 * @return array
	 */
	function buscaNota($cobranca = null) {

		$nota = array();
		
		if($cobranca) {
			$q = "SELECT * FROM tab_notas WHERE coa_id in($cobranca)";
		}
		$query = $this->bancodedados->query($q);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$nota[] = $dados;
		}

		return $nota;
	}

	/**
	 * Corrige o status da nota
	 * @param int $cobranca
	 * @param int $corrigeStatus
	 * @param int $corrigeGeradoNota
	 * @return void
	 */
	function corrige_notaStatus($cobranca, $corrigeStatus = false, $corrigeGeradoNota = false) {

		$query = $this->bancodedados->query("SELECT * FROM tab_notas WHERE coa_id IN('{$cobranca}') ;");

		$sqlStatus = 'status_id = ' . ($corrigeStatus ? '5' : '4');
		$sqlGeradoNota = ', nota_gerada = ' . ($corrigeGeradoNota ? '1' : '0');

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("UPDATE tab_notas SET {$sqlStatus} {$sqlGeradoNota} WHERE id = '{$dados['id']}';");
		}
	}


	/**
	 * Deleta a nota
	 * @param int $cobranca
	 * @return void
	 */
	function deletaNota($cobranca) {

		$query = $this->bancodedados->query("SELECT * FROM tab_notas WHERE coa_id IN('{$cobranca}');");
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("DELETE FROM tab_notas_log WHERE nota = '{$dados['id']}';");
			$this->bancodedados->query("DELETE FROM tab_notas WHERE id = '{$dados['id']}';");
			$this->bancodedados->query("UPDATE tab_cobrancas SET gerado_nota = '0' WHERE coa_id = '{$dados['coa_id']}';");
		}
	}

	/**
	 * Deleta todas as notas
	 * @return void
	 */
	function deletaTodasNotas() {
		$query = $this->bancodedados->query("SELECT * FROM tab_notas INNER JOIN tab_cobrancas on tab_cobrancas.coa_id = tab_notas.coa_id WHERE numero IS null");
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("DELETE FROM tab_notas_log WHERE nota = '{$dados['id']}';");
			$this->bancodedados->query("DELETE FROM tab_notas WHERE id = '{$dados['id']}';");
			$this->bancodedados->query("UPDATE tab_cobrancas SET gerado_nota = '0' WHERE coa_id = '{$dados['coa_id']}';");
		}
	}
}
