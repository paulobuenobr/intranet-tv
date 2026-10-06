<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios = controle::instanciaObjeto("condominios", $bancodedados, $seguranca->permissoes, '%', false);

  if ($_GET["a"] == "buscar_pontos") {

    $pontos = str_replace("'", '\\\'', $condominios->buscaPontos($_GET["i"]));
    print "document.getElementById(\"condominios_pontos\").innerHTML = '$pontos';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var condominios_pontos;

function buscaCondominiosPontos(ponto) {
	if (ponto) {
		url = "rds.js.php?m=condominios.pontos&a=buscar_pontos&i=" + ponto;
		RDSCarregar("condominios_pontos_buscar_pontos", url);
	}
}

function verPonto(ponto) {
	if (ponto) {
    abrir("condominios_pontos", "?m=clientes_pontos&a=ver&i=" + ponto, 525, 450, 1);
	}
}

function verAutenticacao(ponto) {
	if (ponto) {
    abrir("condominios_autenticacoes", "?m=clientes_autenticacoes&a=ver&i=" + ponto, 555, 470, 1);
	}
}

<?

	if (false) { ?></script><? }

}

?>
