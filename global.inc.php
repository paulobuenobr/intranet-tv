<?

date_default_timezone_set('America/Sao_paulo');

require_once("_global/constantes.inc.php");
require_once("_global/controle.class.inc.php");
require_once("_global/funcoes.class.inc.php");

set_include_path(get_include_path().PATH_SEPARATOR.DIR_RAIZ);

if (!$_SERVER || !array_key_exists("REQUEST_URI", $_SERVER) || strpos($_SERVER["REQUEST_URI"], "erro.php") === false) {
	$bancodedados = controle::instanciaObjeto("bancodedados");
	$sessao = controle::instanciaObjeto("sessao", $bancodedados);
}

?>
