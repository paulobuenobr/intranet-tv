<?

require_once("global.inc.php");
    
global $bancodedados;
    
$login = controle::instanciaObjeto("login", $bancodedados);
$login->deslogaUsuario();
header ("Location: ./");

?>
