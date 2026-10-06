<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios = controle::instanciaObjeto("condominios", $bancodedados, $seguranca->permissoes, '%', false);

  if ($_GET["a"] == "verifica_condominio_endereco" && $_GET["condominio"] && $_GET["cep"] && $_GET["numero"]) {

  	$alertas = $condominios->buscaAlertasCondominio($_GET["condominio"], $_GET["cep"], $_GET["numero"]);
    if ($alertas) {
      print "if (document.getElementById(\"alertas\")) document.getElementById(\"alertas\").innerHTML = '{$alertas["mensagem_html"]}';";
      print "alert('{$alertas["mensagem_js"]}');";
      print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").value = '{$alertas["responsavel_id"]}';";
      if ($alertas["responsavel_id"] != $_SESSION["usuario"] || !$seguranca->permissoes["condominios"]["mudar_responsavel"]) {
        print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").disabled = true;";
      } else {
        print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").disabled = false;";
      }
    } else {
      print "if (document.getElementById(\"alertas\")) document.getElementById(\"alertas\").innerHTML = '';";
      print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").value = '" . $_SESSION["usuario"] . "';";
      print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").disabled = false;";
    }

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verificaCondominiosMesmoEndereco(formulario) {
  cep = limpaStringNumerico(formulario.cep.value);
  numero = formulario.numero.value;
  if (cep.length == 8 && numero && condominio_id) {
    url = "rds.js.php?m=condominios.alertas&a=verifica_condominio_endereco&cep=" + cep + "&numero=" + numero + "&condominio=" + condominio_id;
		RDSCarregar("condominios_alertas_verifica_condominio_endereco", url);
  } else {
    if (document.getElementById("alertas") != null) document.getElementById("alertas").innerHTML = '';
    if (formulario.responsavel != null) formulario.responsavel.disabled = false;
  }
}

<?

	if (false) { ?></script><? }

}

?>
