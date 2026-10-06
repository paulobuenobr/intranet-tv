<?

ignore_user_abort(1);

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$placar = controle::instanciaObjeto("placar", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "atualiza_data_hora") {

    print "document.getElementById('data_hora').innerHTML = '".$placar->buscaDataHora()."';";

  }

  if ($_GET["a"] == "atualiza_tela") {

    $dados = $placar->buscaDadosPlacarComercial(date("Y-01-01"), date("Y-m-d"));
    print "document.getElementById('placar').innerHTML = '".str_replace("'", '\\\'', $placar->geraPlacarComercial($dados))."';";
    print "document.getElementById('placar_total').innerHTML = '".str_replace("'", '\\\'', $placar->geraPlacarTotal($dados))."';";
    print "proxima_atualizacao = 300;";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function atualizaTela() {
  exibePainelCarregando();
  url = "rds.js.php?m=placar.comercial&a=atualiza_tela";
  RDSCarregar("placar_atualiza_atualiza_tela", url);
}

function atualizaProximaAtualizacao() {
  proxima_atualizacao--;
  if (proxima_atualizacao > 0) {
    var minutos = parseInt(proxima_atualizacao / 60, 10);
    var segundos = proxima_atualizacao % 60;
    segundos = segundos < 10 ? "0" + segundos : segundos;
    document.getElementById('proxima_atualizacao').innerHTML = minutos + ":" + segundos;
  } else if (proxima_atualizacao == 0) {
    document.getElementById('proxima_atualizacao').innerHTML = "0:00";
    atualizaTela();
  }
}

function atualizaDataHora() {
  if (atualiza_data_hora != 0) {
    clearInterval(atualiza_data_hora);
    atualiza_data_hora = 0;
    setInterval('atualizaDataHora()', 60000);
  }
  url = "rds.js.php?m=placar.comercial&a=atualiza_data_hora";
  RDSCarregar("placar_atualiza_data_hora", url);
}

function exibePainelCarregando() {
  if (document.getElementById('carregando')) {
    atualizando = true;
    document.getElementById('carregando').style.display = 'block';
  }
}

function ocultaPainelCarregando() {
  if (document.getElementById('carregando')) {
    document.getElementById('carregando').style.display = 'none';
  }
}

<?

	if (false) { ?></script><? }

}

?>

ocultaPainelCarregando();
