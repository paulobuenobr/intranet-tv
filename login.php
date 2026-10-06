<?

header('Content-Type: text/html; charset=iso-8859-1');

require_once("global.inc.php");

global $bancodedados;

$login = controle::instanciaObjeto("login", $bancodedados);

if (empty($_GET["retorno"])) $_GET["retorno"] = "./";

if ($login->estaLogado()) {
	
    header("Location: ".str_replace(",", "&", $_GET["retorno"]));
    
} else {
	
    if (!$_POST) {
    	
    	/**
    	 * @var interfaces $interfaces
    	 */
    	$interfaces = controle::instanciaObjeto("interfaces");
    	$interfaces->geraTelaLogin($_GET["retorno"]);
    	$interfaces->mostra();
    	
    } else {
    	
    	if ($login->logaUsuario($_POST["usuario"], md5($_POST["senha"]))) {
    		header("Location: ".str_replace(",", "&", $_GET["retorno"]));
    	} else {
    		print "
<script>
	alert('Usuário ou Senha incorreto(a)!');
	window.location = './login.php?retorno=".str_replace("&", ",", $_GET["retorno"])."';
</script>";
    	}
    	
    }
    
}

?>
