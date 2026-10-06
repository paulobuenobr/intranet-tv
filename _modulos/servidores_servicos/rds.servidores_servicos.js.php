<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$servidores_servicos = controle::instanciaObjeto("servidores_servicos", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "buscar_servidores_servicos") {
    
    $servidores_adicionar = $servidores_servicos->buscaServidoresServicosAdicionar($_GET["sei_id"]);
    $servidores_listagem = $servidores_servicos->geraServidoresServicosListagem($servidores_servicos->buscaServidoresServicos($_GET["sei_id"]));
  	print "if (document.getElementById(\"servidores_servicos_adicionar\")) document.getElementById(\"servidores_servicos_adicionar\").innerHTML = '".str_replace("'", '\\\'', $servidores_adicionar)."';";
  	print "if (document.getElementById(\"servidores_servicos\")) document.getElementById(\"servidores_servicos\").innerHTML = '".str_replace("'", '\\\'', $servidores_listagem)."';";
  
  }  
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscaServidoresServicos(sei_id) {
	if (sei_id) {
		url = "rds.js.php?m=servidores_servicos&a=buscar_servidores_servicos&sei_id=" + sei_id;
		RDSCarregar("servidores_servicos_buscar_servidores_servicos", url);
	}
}

function verServidorServico(ses_id, sei_id) {
	if (ses_id) {
    abrir("servidor_servico", "?m=servidores_servicos&a=ver&i=" + ses_id + "&sei_id=" + sei_id, 480, 330, 0);
	}
}

function adicionarServidorServico(sei_id) {
	if (sei_id) {
    abrir("servidor_servico", "?m=servidores_servicos&a=adicionar&i=" + sei_id, 480, 330, 0);
	}
}

function mudaServico(servico) {
	if (servico && document.getElementById("parametros_" + servico)) {
		document.getElementById("parametros").innerHTML = document.getElementById("parametros_" + servico).innerHTML;
	} else {
	  document.getElementById("parametros").innerHTML = "Escolha um serviço";
	}
}

function validaServico(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
  if (!formulario.servico.value) erro_vazio[erro_vazio.length] = "Serviço";
	// DHCP
	if (formulario.servicos.value == 1) {
		if (!document.getElementById("publicar").value) erro_vazio[erro_vazio.length] = "Publicar";
		if (!document.getElementById("reiniciar").value) erro_vazio[erro_vazio.length] = "Reiniciar";
		if (!document.getElementById("publicar_reiniciar").value) erro_vazio[erro_vazio.length] = "Publicar e Reiniciar";
	} else
	// ARP
	if (formulario.servicos.value == 2) {
		if (!document.getElementById("publicar").value) erro_vazio[erro_vazio.length] = "Publicar";
		if (!document.getElementById("reiniciar").value) erro_vazio[erro_vazio.length] = "Reiniciar";
		if (!document.getElementById("publicar_reiniciar").value) erro_vazio[erro_vazio.length] = "Publicar e Reiniciar";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
