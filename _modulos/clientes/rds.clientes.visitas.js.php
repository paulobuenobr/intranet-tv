<?php

if ($_GET["a"]) {

	global $bancodedados;
		
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$visitas = controle::instanciaObjeto("visitas", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "busca_visitas" && $_GET["cliente"]) {
	
		$listagem = $visitas->geraClientesVisitasListagem($visitas->buscaClientesVisitas($_GET["cliente"]));
		    
		print "if (document.getElementById(\"visitas_listagem\")) document.getElementById(\"visitas_listagem\").innerHTML = '".addslashes($listagem)."';";
		
	}

} else {
	
	if (false) { ?><script type="text/javascript"><?php }
	
?>

function buscaVisitas(cliente,ver) {
	if (cliente) {
		url = "rds.js.php?m=clientes.visitas&a=busca_visitas&cliente=" + cliente;
		RDSCarregar("clientes_visitas_busca_visitas", url);
	}
}

function verVisita(visita) {
	if (visita) {
	    abrir("visita", "?m=visitas&a=ver&i=" + visita, 450, 450, 1);
	}
}

function adicionarVisita(cliente) {
	if (cliente) {
	    abrir("visita", "?m=visitas&a=adicionar&c=" + cliente, 450, 450, 1);
	}
}

<?

	if (false) { ?></script><?php }

}

?>
