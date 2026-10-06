<?

require("../global.inc.php");
$interfaces = controle::instanciaObjeto("interfaces", $bancodedados);
$integracao = controle::instanciaObjeto("integracao", $bancodedados);
$interfaces->geraTelaIntegracaoOnline($integracao->conteudo, $_GET["origem"]);
$interfaces->mostra();

?>
