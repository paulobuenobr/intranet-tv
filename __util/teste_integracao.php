<?

set_time_limit(0);
ignore_user_abort(1);
header('Content-Type: text/html; charset=ISO-8859-1');

require_once("../global.inc.php");

$teste_integracao = new teste_integracao($bancodedados);
$teste_integracao->executar();

class teste_integracao {

	private $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	function executar() {

		$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$publicacao->teste_integracao();

	}
	
}

?>
