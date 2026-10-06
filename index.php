<?

header('Content-Type: text/html; charset=iso-8859-1');

require_once("global.inc.php");

global $bancodedados;

function error_handler($errno, $errstr) {
	
}

set_error_handler("error_handler");

$login = controle::instanciaObjeto("login", $bancodedados);

if ($login->estaLogado()) {
	
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$interfaces = controle::instanciaObjeto("interfaces", $bancodedados, $seguranca->permissoes);
	
	require_once("_painel/rds.painel.js.php");
	
	if ($seguranca->temPermissao($_GET["m"], $_GET["a"])) {
		
		if ($_GET["m"] == "operacoes_sistema") {
			$interfaces->geraTelaOperacaoSistema();
		} elseif ($_GET["m"] == "chamados_sistema") {
			$modulo = controle::instanciaObjeto($_GET["m"], $bancodedados, $seguranca->permissoes);
			$interfaces->conteudo = $modulo->conteudo;
		} else {
			$modulo = controle::instanciaObjeto($_GET["m"], $bancodedados, $seguranca->permissoes);
			$interfaces->geraTelaModulo($modulo);
		}
		
		$interfaces->mostra();
		
	} elseif ($seguranca->temPermissao("tarefas", "agenda")) {
		
		header("Location: ./?m=tarefas&a=agenda");
		
	} else {
		
		$interfaces->geraTelaInterna('');
		$interfaces->mostra();
		
	}
  
} else {
	
	header("Location: ./login.php?retorno=".str_replace("&", ",", $_SERVER["REQUEST_URI"]));
	
}

?>
