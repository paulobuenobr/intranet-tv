<?

header('Content-Type: text/html; charset=iso-8859-1');

require_once("global.inc.php");

$erros = array (
	"bancodedados" => "Não foi possível conectar ao servidor de Banco de Dados!"
);

if ($erros[$_GET["id"]]) {
	$interfaces = controle::instanciaObjeto("interfaces");
	$interfaces->geraTelaErro($erros[$_GET["id"]]);
	$interfaces->mostra();
} else {
	header("Location: ./");
}

?>
