<?

if ($_GET["a"]) {

	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "buscar_iptv") {

		$listagem = $clientes_iptv->geraClientesIptvListagem($clientes_iptv->buscaClientesIptv($_GET["cliente"], $_GET["exibir_inativos"] == "true"));
		print "if (document.getElementById(\"clientes_iptv_listagem\")) document.getElementById(\"clientes_iptv_listagem\").innerHTML = '".addslashes($listagem)."';";

	}

} else {

	if (false) { ?><script type="text/javascript"><? }

?>

function buscaIptv(cliente, exibir_inativos) {
	
	if (cliente) {
		url = "rds.js.php?m=clientes_iptv&a=buscar_iptv&cliente=" + cliente + "&exibir_inativos=" + exibir_inativos;
		RDSCarregar("clientes_iptv_buscar_iptv", url);
	}
	
}

function verIptv(iptv_id) {
	
	if (iptv_id) {
		abrir("clientes_iptv", "?m=clientes_iptv&a=ver&i=" + iptv_id, 655, 470, 1);
	}
	
}

function validaAlterarUsuario(formulario) {
	
	var erro_vazio = new Array();  
	var erro_invalido = new Array();
	
	if (!formulario.usuario.value) {
	  erro_vazio[erro_vazio.length] = "Usuário";
	} else if (!validaEmail(formulario.usuario.value)) {
	  erro_invalido[erro_invalido.length] = "Usuário";
	}

	return apresentaErros(erro_vazio, erro_invalido);
	
}


<?

	if (false) { ?></script><? }

}

?>
