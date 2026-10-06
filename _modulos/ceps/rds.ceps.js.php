<?php

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	/**
	 * @var ceps $ceps
	 */
	$ceps = controle::instanciaObjeto("ceps", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "busca_endereco_cep") {

		$_GET["cep"] = preg_replace("/[^0-9]/", "", $_GET["cep"]);
		
		if (($dados_cep = $ceps->buscaDadosCEP($_GET["cep"])) != false) {
			
			$cidades = $ceps->buscaCidades($dados_cep["estado_id"]);
			$bairros = $ceps->buscaBairros($dados_cep["cidade_id"]);
			
			print "document.getElementById('estado').value = '{$dados_cep["estado_id"]}';";
			
			atualizaSelect("cidade", $cidades, $dados_cep["cidade_id"], true, false, true, false);
			atualizaSelect("bairro", $bairros, $dados_cep["bairro_id"], true, false, false, false);
			
			print "document.getElementById('cidade').disabled = false;";
			print "document.getElementById('bairro').disabled = false;";
			print "document.getElementById('logradouro').value = '{$dados_cep["logradouro_nome"]}';";
			print "document.getElementById('observacao').value = '{$dados_cep["observacao"]}';";
			print "document.getElementById('novo').disabled = false;";
			print "if (document.getElementById('preencher')) document.getElementById('preencher').disabled = false;";
			
		} elseif (($dados_cep = $ceps->buscaDadosRegiao($_GET["cep"])) != false) {
			
			$cep = substr(sprintf("%08.8s", $dados_cep["cep_geral"]), 0, 5)."-".substr(sprintf("%08.8s", $dados_cep["cep_geral"]), 5, 3);
			
			$cidades = $ceps->buscaCidades($dados_cep["estado_id"]);
			$bairros = $ceps->buscaBairros($dados_cep["cidade_id"]);
			
			atualizaSelect("cidade", $cidades, null, true, false, true, false);
			atualizaSelect("bairro", $bairros, null, true, false, false, false);
			
			print "cep = '{$cep}';";
			print "document.getElementById('cep').value = '{$cep}';";
			print "document.getElementById('estado').value = '{$dados_cep["estado_id"]}';";
			print "document.getElementById('cidade').value = '{$dados_cep["cidade_id"]}';";
			print "document.getElementById('bairro').value = '{$dados_cep["bairro_id"]}';";
			print "document.getElementById('cidade').disabled = false;";
			print "document.getElementById('bairro').disabled = false;";
			print "document.getElementById('logradouro').value = '';";
			print "document.getElementById('observacao').value = '';";
			print "document.getElementById('novo').disabled = false;";
			
		} else {
			
			print "document.getElementById('estado').value = '';";
			print "document.getElementById('cidade').value = '';";
			print "document.getElementById('cidade').disabled = true;";
			print "document.getElementById('bairro').value = '';";
			print "document.getElementById('bairro').disabled = true;";
			print "document.getElementById('logradouro').value = '';";
			print "document.getElementById('observacao').value = '';";
			
		}

	} else if ($_GET["a"] == "busca_cidades") {

		print "document.getElementById('bairro').value = '';";
		print "document.getElementById('bairro').disabled = true;";
		
		$cidades = $ceps->buscaCidades($_GET["estado"]);
		
		atualizaSelect("cidade", $cidades, null, true, false, true, true);

		if (count($cidades) == 1) {
			print "document.getElementById('novo').disabled = false;";
		} else {
			print "document.getElementById('novo').disabled = true;";
		}
		
	} else if ($_GET["a"] == "busca_bairros") {

		$dados_cidade = $ceps->buscaDadosCidade($_GET["cidade"]);
		$bairros = $ceps->buscaBairros($_GET["cidade"]);
		
		if ($dados_cidade["cep_geral"]) {
			$cep = substr(sprintf("%08.8s", $dados_cidade["cep_geral"]), 0, 5)."-".substr(sprintf("%08.8s", $dados_cidade["cep_geral"]), 5, 3);
			print "cep = '{$cep}';";
			print "document.getElementById('cep').value = '{$cep}';";
		}
		
		atualizaSelect("bairro", $bairros, null, true, false, false, true);

	} else if ($_GET["a"] == "novo_bairro") {
		
		if (($bairro_id = $ceps->adicionarBairro($_GET["bairro"], $_GET["cidade"])) != false) {
			print "bairro = $bairro_id;";
			atualizaSelect("bairro", $ceps->buscaBairros($_GET["cidade"]), $bairro_id, true, false, false, true);
		}
		print "novoBairro()";
		
	} else if ($_GET["a"] == "salva_cep") {

		$cep_id = $ceps->adicionarCEP($_GET["estado"], $_GET["cidade"], $_GET["bairro"], $_GET["novo_bairro"], $_GET["logradouro"], $_GET["observacao"], $_GET["cep"]);
		
		print "alert('{$ceps->ultimo_status}');";
		
		if ($cep_id) {
			print "cep = '';";
			print "document.getElementById('cep').value = '$cep_id';";
			print "buscaCEP(document.getElementById('cep'));";
			print "if (document.getElementById('preencher')) document.getElementById('preencher').disabled = false;";
		}

		print "document.getElementById('cep').focus();";
		
	}

} else {
	
	if (false) { ?><script type="text/javascript"><?php }
	
?>

var cep;

function buscaCEP(campo) {
	if (campo.value != cep) {
		if (document.getElementById('preencher')) document.getElementById('preencher').disabled = true;
		resetaNovoBairro();
		cep = campo.value;
		if (campo.value.length == 9) {
			url = "rds.js.php?m=ceps&a=busca_endereco_cep&cep=" + campo.value;
			RDSCarregar("ceps.busca_endereco_cep", url);
		}
	}
}

function mudaEstado(campo) {
	resetaNovoBairro();
	if (campo.value) {
		url = "rds.js.php?m=ceps&a=busca_cidades&estado=" + campo.value;
		RDSCarregar("ceps.busca_cidades", url);
	} else {
		document.getElementById('cidade').value = "";
		document.getElementById('cidade').disabled = true;
		document.getElementById('bairro').value = "";
		document.getElementById('bairro').disabled = true;
	}
}

function mudaCidade(campo) {
	if (campo.value) {
		document.getElementById("novo").disabled = false;
		url = "rds.js.php?m=ceps&a=busca_bairros&cidade=" + campo.value;
		RDSCarregar("ceps.busca_bairros", url);
	} else {
		resetaNovoBairro();
		document.getElementById('bairro').value = "";
		document.getElementById('bairro').disabled = true;
	}
}

function salvaCEP() {
	var cep = document.getElementById('cep').value;
	var estado = document.getElementById('estado').value;
	var cidade = document.getElementById('cidade').value;
	var bairro = document.getElementById('bairro').value;
	var novo_bairro = document.getElementById('novo_bairro').value;
	var logradouro = document.getElementById('logradouro').value;
	var observacao = document.getElementById('observacao').value;
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!cep) {
		erro_vazio[erro_vazio.length] = "CEP";
	}
	if (!estado) {
		erro_vazio[erro_vazio.length] = "Estado";
	}
	if (!cidade) {
		erro_vazio[erro_vazio.length] = "Cidade";
	}
	if (!bairro && !novo_bairro) {
		erro_vazio[erro_vazio.length] = "Bairro";
	}
	if (!logradouro) {
		erro_vazio[erro_vazio.length] = "Logradouro";
	}
	if (apresentaErros(erro_vazio, erro_invalido)) {
		url = "rds.js.php?m=ceps&a=salva_cep&estado=" + estado + "&cidade=" + cidade + "&bairro=" + bairro + "&novo_bairro=" + novo_bairro + "&logradouro=" + logradouro + "&observacao=" + observacao + "&cep=" + cep;
		RDSCarregar("ceps.busca_bairros", url);
	}
}

function adicionarCEP() {
	if (document.getElementById('cep') && !document.getElementById('cep').disabled) {
		abrir("pesquisar_cep", "?m=ceps&a=adicionar&tipo=cadastro", 700, 450, 1);
	}
}

function pesquisarCEP() {
	if (document.getElementById('cep') && !document.getElementById('cep').disabled) {
		abrir("pesquisar_cep", "?m=ceps&a=pesquisar", 700, 450, 1);
	}
}

function preencherCEP(cep) {
	if (cep === undefined) {
		cep = document.getElementById('cep').value;
	}
	if (cep.length == 9) {
		if (window.opener && window.opener.document.getElementById('cep') && !window.opener.document.getElementById('cep').disabled) {
			window.opener.document.getElementById('cep').value = cep;
			window.opener.document.getElementById('cep').dispatchEvent(new Event('blur'));
			window.opener.document.getElementById('cep').select();
		}
		window.close();
	}
}

var bairro;

function novoBairro() {
	if (document.getElementById("novo_bairro").style.display == "none") {
		bairro = document.getElementById("bairro").value;
		document.getElementById("bairro").style.display = "none";
		document.getElementById("bairro").value = "";
		document.getElementById("novo_bairro").value = "";
		document.getElementById("novo_bairro").style.display = "inline-block";
		document.getElementById("novo").value = "Cancelar";
		document.getElementById("novo_bairro").focus();
	} else {
		document.getElementById("bairro").style.display = "inline-block";
		document.getElementById("novo_bairro").style.display = "none";
		document.getElementById("novo").value = "Novo Bairro";
		document.getElementById("bairro").value = bairro;
		document.getElementById("bairro").focus();
	}
}

function resetaNovoBairro() {
	document.getElementById("bairro").style.display = "inline-block";
	document.getElementById("novo_bairro").style.display = "none";
	document.getElementById("novo").value = "Novo Bairro";
	document.getElementById("novo").disabled = true;
	bairro = "";
}

function novoBairroOnKeyPress(evento) {
	if (evento.keyCode == 13) {
		url = "rds.js.php?m=ceps&a=novo_bairro&cidade=" + document.getElementById("cidade").value + "&bairro=" +  document.getElementById("novo_bairro").value;
		RDSCarregar("ceps.novo_bairro", url);
	} else if (evento.keyCode == 27) {
		novoBairro();
	}
}

<?php

	if (false) { ?></script><?php }

}

?>
