<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$chamados_roteiros = controle::instanciaObjeto("chamados_roteiros", $bancodedados, $seguranca->permissoes, false);
  $chamados_roteiros->cliente_id = $_GET["cliente_id"];

  if ($_GET["a"] == "iniciar") {

    $chamados_roteiros->iniciarRoteiro($_GET["roteiro_id"]);
    $etapas = addslashes($chamados_roteiros->buscaEtapas());
    print "document.getElementById('etapas').innerHTML = '$etapas';";
    print "document.body.scrollTop = document.body.scrollHeight;";
    print "document.getElementById('iniciar').value = 'Reiniciar';";
    print "document.getElementById('iniciar').disabled = false;";

  } elseif ($_GET["a"] == "registra_etapa") {

    $chamados_roteiros->registraEtapaSituacao($_GET["situacao_id"]);
    $etapas = addslashes($chamados_roteiros->buscaEtapas());
    print "document.getElementById('etapas').innerHTML = '$etapas';";
    print "document.body.scrollTop = document.body.scrollHeight;";

  } elseif ($_GET["a"] == "busca_clientes_pontos") {

    $clientes_pontos = $chamados_roteiros->buscaClientePontos($_GET["cliente_id"]);
    $clientes_pontos = addslashes($chamados_roteiros->geraClientesPontosListagem($clientes_pontos));
    print "document.getElementById('clientes_pontos').innerHTML = '$clientes_pontos';";

  }

} else {

	if (false) { ?><script type="text/javascript"><? }
	
?>

function iniciarRoteiro() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();
  if (!document.getElementById('roteiro').value) {
    erro_vazio[erro_vazio.length] = "Roteiro";
    return apresentaErros(erro_vazio, erro_invalido);
  }
  document.getElementById('iniciar').disabled = true;
	url = "rds.js.php?m=chamados_roteiros&a=iniciar&cliente_id=" + cliente_id + "&roteiro_id=" + document.getElementById('roteiro').value;
	RDSCarregar("chamados_roteiros", url);
}

function registraEtapa(situacao_id) {
  if (document.getElementById('sim')) document.getElementById('sim').disabled = true;
  if (document.getElementById('nao')) document.getElementById('nao').disabled = true;
	url = "rds.js.php?m=chamados_roteiros&a=registra_etapa&cliente_id=" + cliente_id + "&situacao_id=" + situacao_id;
	RDSCarregar("chamados_roteiros", url);
}

function verPonto(ponto) {
	if (ponto) {
    abrir("clientes_pontos", "?m=clientes_pontos&a=ver&i=" + ponto, 525, 450, 1);
	}
}

function buscaPontos(cliente_id) {
	url = "rds.js.php?m=chamados_roteiros&a=busca_clientes_pontos&cliente_id=" + cliente_id;
	RDSCarregar("chamados_roteiros", url);
}

<?

	if (false) { ?></script><? }

}

?>
