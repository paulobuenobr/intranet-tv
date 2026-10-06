<?php

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes = controle::instanciaObjeto("clientes", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "atualiza_plano") {
		
		if (($planos = $clientes->buscaPlanos($_GET["servico"])) != false) {
			print $clientes->geraScriptAtualizacaoSelect("plano", $planos);
		} else {
			print $clientes->geraScriptAtualizacaoSelect("plano");
		}
		
	} elseif ($_GET["a"] == "condominio_campo") {
		
		if (($condominio = $clientes->buscaCondominio($_GET["condominio"])) != false) {
			print "document.getElementById('condominio1').value = '$condominio';";
		}
		
	} elseif ($_GET["a"] == "atualiza_cidade") {
		
		if ($_GET["cidade"] && ($bairros = $clientes->buscaBairros($_GET["cidade"]))) {
			print $clientes->geraScriptAtualizacaoSelect("bairro2", $bairros);
		} else {
			print $clientes->geraScriptAtualizacaoSelect("bairro2");
		}
		
	} elseif ($_GET["a"] == "cidade_campo") {
		
		if (($cidade = $clientes->buscaCidade($_GET["cidade"])) != false) {
			print "document.getElementById('cidade1').value = '$cidade';";
		}
		
	} elseif ($_GET["a"] == "bairro_campo") {
		
		if (($bairro = $clientes->buscaBairro($_GET["bairro"])) != false) {
			print "document.getElementById('bairro1').value = '$bairro';";
		}
		
	}

} else {

	if (false) { ?><script type="text/javascript"><?php }

?>

function atualizaPlanos(servico) {
	if (servico) {
		document.getElementById('plano').disabled = false;
	} else {
		document.getElementById('plano').disabled = true;
	}
	url = "rds.js.php?m=clientes.buscar&a=atualiza_plano&servico=" + servico;
	RDSCarregar("clientes_buscar_atualiza_plano", url);
}

function atualizaCondominiosCampo(condominio) {
	if (condominio) {
		url = "rds.js.php?m=clientes.buscar&a=condominio_campo&condominio=" + condominio;
		RDSCarregar("clientes_buscar_condominio_campo", url);
		document.getElementById('condominio1').disabled = true;
	} else {
		document.getElementById('condominio1').disabled = false;
	}
	document.getElementById('condominio1').value = '';
}

function atualizaBairros(cidade) {
	if (cidade) {
		url = "rds.js.php?m=clientes.buscar&a=cidade_campo&cidade=" + cidade;
		RDSCarregar("condominios_buscar_cidade_campo", url);
		document.getElementById('bairro1').value = '';
		document.getElementById('cidade1').disabled = true;
	} else {
		document.getElementById('cidade1').value = '';
		document.getElementById('cidade1').disabled = false;
		document.getElementById('bairro1').value = '';
		document.getElementById('bairro1').disabled = false;
	}
	url = "rds.js.php?m=clientes.buscar&a=atualiza_cidade&cidade=" + cidade;
	RDSCarregar("clientes_buscar_atualiza_cidade", url);
}

function atualizaBairrosCampo(bairro) {
	if (bairro) {
		url = "rds.js.php?m=clientes.buscar&a=bairro_campo&bairro=" + bairro;
		RDSCarregar("clientes_buscar_bairro_campo", url);
		document.getElementById('bairro1').disabled = true;
	} else {
		document.getElementById('bairro1').value = '';
		document.getElementById('bairro1').disabled = false;
	}
}

<?

	if (false) { ?></script><?php }

}

?>
