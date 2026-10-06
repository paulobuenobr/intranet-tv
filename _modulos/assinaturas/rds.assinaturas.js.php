<?

if ($_GET && array_key_exists("a", $_GET)) {
	
	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	/**
	 * @var assinaturas $assinaturas
	 */
	$assinaturas = controle::instanciaObjeto("assinaturas", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "busca_assinaturas_cliente") {

		$_GET["status"] = (!$_GET["status"]) ? "0" : $_GET["status"];
		
		$lista_assinaturas = $assinaturas->buscaClientesAssinaturas($_GET["cliente_id"], $_GET["status"]);
		$lista_assinaturas = $assinaturas->geraListagemClientesAssinaturas($lista_assinaturas);
		
		print "if (document.getElementById(\"assinaturas\")) document.getElementById(\"assinaturas\").innerHTML = '".str_replace("'", '\\\'', $lista_assinaturas)."';";

	} elseif ($_GET["a"] == "busca_servicos") {

		$pfpj = $_GET["pfpj"] ? "pf" : "pj";
		$ccsc = $_GET["ccsc"] ? "cc" : "sc";
		$servicos = $assinaturas->buscaPlanosServicos($_GET["cli"], $_GET["origem"], $pfpj, $ccsc, $_GET["condominio"]);
		$lista_servicos = array();
		
		foreach ($servicos AS $id => $nome) {
			$lista_servicos[] = "new Array(\"$id\", \"$nome\")";
		}
		
		print "var servicos = new Array(".implode(",", $lista_servicos).");";
		print "if (document.getElementById(\"servico\")) atualizaSelect(document.getElementById(\"servico\"), servicos);";

	} elseif ($_GET["a"] == "busca_planos") {

		$pfpj = $_GET["pfpj"] ? "pf" : "pj";
		$ccsc = $_GET["ccsc"] ? "cc" : "sc";
		$planos = $assinaturas->buscaServicosPlanos($_GET["ser"], $pfpj, $ccsc, $_GET["condominio"], $_GET["origem"]);
		$lista_planos = array();
		
		foreach ($planos as $id => $nome) {
			$lista_planos[] = "new Array(\"$id\", \"$nome\")";
		}
		
		print "if (document.getElementById(\"servico\").value) {";
		print "  var planos = new Array(".implode(",", $lista_planos).");";
		print "  if (document.getElementById(\"plano\")) atualizaSelect(document.getElementById(\"plano\"), planos".($_GET["plano"] ? ", {$_GET["plano"]}" : "").");";
		print "  mudaAssinaturaPlano(document.getElementById(\"plano\"));";
		print "} else {";
		print "  if (document.getElementById(\"plano\")) document.getElementById(\"plano\").value = \"\";";
		print "  if (document.getElementById(\"plano\")) document.getElementById(\"plano\").disabled = true;";
		print "  if (document.getElementById(\"sistema\")) document.getElementById(\"sistema\").value = \"\";";
		print "  if (document.getElementById(\"sistema\")) document.getElementById(\"sistema\").disabled = true;";
		print "}";

	} elseif ($_GET["a"] == "busca_adicionais") {

		$pfpj = $_GET["pfpj"] ? "pf" : "pj";
		$ccsc = $_GET["ccsc"] ? "cc" : "sc";
		$planos_adicionais = $assinaturas->buscaPlanosAdicionais($_GET["ass"], $pfpj, $ccsc, $_GET["condominio"], $_GET["origem"]);
		$lista_planos = array();
		
		foreach ($planos_adicionais as $id => $nome) {
			$lista_planos[] = "new Array(\"$id\",\"$nome\")";
		}
		
		print "var adicionais = new Array(".implode(",", $lista_planos).");";
		print "if (document.getElementById(\"plano\")) atualizaSelect(document.getElementById(\"plano\"), adicionais".($_GET["plano"] ? ", {$_GET["plano"]}" : "").");";
		print "mudaAssinaturaAdicional(document.getElementById(\"plano\"));";

	} elseif ($_GET["a"] == "busca_dados_plano") {

		$dados_plano = $assinaturas->buscaDadosPlano($_GET["pla"]);
		$instalacao = $assinaturas->formataDinheiro($dados_plano["instalacao"]);
		$mensalidade = $assinaturas->formataDinheiro($dados_plano["mensalidade"]);
		$formas_pagamento = $assinaturas->buscaFormasPagamento($_GET["pla"]);
		$sistemas = $assinaturas->buscaSistemas($_GET["cli"], $dados_plano["sistema_id"]);

		$_GET["sistema"] = $_GET["sistema"] ? $_GET["sistema"] : key($sistemas);
		
		if ($_GET["cli"]) {
			$descontos_automaticos = $assinaturas->buscaDescontosAutomaticos($_GET["con"], $_GET["cli"], $dados_plano, $_GET["sistema"]);
		}
		
		// Sistemas
		$lista_sistemas = array();
		foreach ($sistemas as $id => $nome) {
			if (count($sistemas) == 1 || $id == 2) {
				$lista_sistemas[] = "new Array(\"$id\", \"$nome\")";
			}
		}
		$lista_sistemas = "new Array(".implode(",", $lista_sistemas).")";
		
		print "if (document.getElementById(\"sistema\")) atualizaSelect(document.getElementById(\"sistema\"), $lista_sistemas, {$_GET["sistema"]}, 0);";
		
		// Descontos
		if ($descontos_automaticos) {
			if ($descontos_automaticos["instalacao"]) {
				$instalacao .= " (".$assinaturas->formataDinheiro($dados_plano["instalacao"] - $descontos_automaticos["instalacao"], "R$", true).")";
			}
			if ($descontos_automaticos["mensalidade"]) {
				$mensalidade .= " (".$assinaturas->formataDinheiro($dados_plano["mensalidade"] - $descontos_automaticos["mensalidade"], "R$", true).")";
			}
		}
		
		print "if (document.getElementById('plano').value) {";
		print "  document.getElementById('instalacao').value = '$instalacao';";
		print "  document.getElementById('mensalidade').value = '$mensalidade';";
		print "  if (document.getElementById('descontos')) document.getElementById('descontos').innerHTML = '".addslashes($assinaturas->geraListagemAssinaturasDescontos($descontos_automaticos["listagem"], false))."';";
		print "  if (document.getElementById('formas_pagamento')) document.getElementById('formas_pagamento').innerHTML = '".addslashes($assinaturas->geraListagemFormasPagamento($formas_pagamento))."';";
		print "} else {";
		print "  document.getElementById('instalacao').value = '';";
		print "  document.getElementById('mensalidade').value = '';";
		print "  if (document.getElementById('descontos')) document.getElementById('descontos').innerHTML = 'Selecione um plano';";
		print "  if (document.getElementById('formas_pagamento')) document.getElementById('formas_pagamento').innerHTML = 'Selecione um plano';";
		print "  if (document.getElementById('sistema')) document.getElementById('sistema').value = '';";
		print "  if (document.getElementById('sistema')) document.getElementById('sistema').disabled = true;";
		print "}";

	} elseif ($_GET["a"] == "busca_dados_adicional") {

		if ($_GET["pld"]) {
			
			$dados_plano = $assinaturas->buscaDadosPlano($_GET["pld"]);

			$instalacao = $assinaturas->formataDinheiro($dados_plano["instalacao"]);
			$mensalidade = $assinaturas->formataDinheiro($dados_plano["mensalidade"]);
			
			if ($_GET["con"] && $dados_plano["desconto_condominio"]) {
				if (($descontos_automaticos = $assinaturas->buscaDescontosAutomaticos($_GET["con"], $_GET["cli"], $dados_plano)) != false) {
					if ($descontos_automaticos["instalacao"]) $instalacao .= " (".$assinaturas->formataDinheiro($dados_plano["instalacao"] - $descontos_automaticos["instalacao"]).")";
					if ($descontos_automaticos["mensalidade"]) $mensalidade .= " (".$assinaturas->formataDinheiro($dados_plano["mensalidade"] - $descontos_automaticos["mensalidade"]).")";
				}
			}

			print "document.getElementById('instalacao').value = '$instalacao';";
			print "document.getElementById('mensalidade').value = '$mensalidade';";
			print "if (document.getElementById('descontos')) document.getElementById('descontos').innerHTML = '".addslashes($assinaturas->geraListagemAssinaturasDescontos($descontos_automaticos["listagem"]))."';";
		
		} else {
			
			print "document.getElementById('instalacao').value = '';";
			print "document.getElementById('mensalidade').value = '';";
			print "if (document.getElementById('descontos')) document.getElementById('descontos').innerHTML = '".addslashes($assinaturas->geraListagemAssinaturasDescontos(null))."';";
			
		}

	} elseif ($_GET["a"] == "busca_assinatura_descontos") {

		$descontos = $assinaturas->buscaAssinaturasDescontos($_GET["ass"]);
		$descontos_listagem = $assinaturas->geraListagemAssinaturasDescontos($descontos["listagem"]);
		$vigencia = $assinaturas->geraVigencia($assinaturas->buscaDadosAssinatura($_GET["ass"]));
		
		if ($descontos) {
			print "if (document.getElementById(\"instalacao\")) document.getElementById(\"instalacao\").innerHTML = '".$assinaturas->formataDinheiro($descontos["assinatura_instalacao"]).($descontos["instalacao"] ? " (".$assinaturas->formataDinheiro($descontos["assinatura_instalacao"] - $descontos["instalacao"], "R$", true).")" : "")."';";
			print "if (document.getElementById(\"mensalidade\")) document.getElementById(\"mensalidade\").innerHTML = '".$assinaturas->formataDinheiro($descontos["assinatura_mensalidade"]).($descontos["mensalidade"] ? " (".$assinaturas->formataDinheiro($descontos["assinatura_mensalidade"] - $descontos["mensalidade"], "R$", true).")" : "")."';";
		} else {
			print "if (document.getElementById(\"instalacao\")) document.getElementById(\"instalacao\").innerHTML = '".$assinaturas->formataDinheiro($descontos["assinatura_instalacao"])."';";
			print "if (document.getElementById(\"mensalidade\")) document.getElementById(\"mensalidade\").innerHTML = '".$assinaturas->formataDinheiro($descontos["assinatura_mensalidade"])."';";
		}
		
		print "if (document.getElementById(\"descontos\")) document.getElementById(\"descontos\").innerHTML = '".addslashes($descontos_listagem)."';";
		print "if (window.opener && window.opener.buscaAssinaturasCliente) window.opener.buscaAssinaturasCliente({$_GET["cli"]});";
		print "if (document.getElementById(\"vigencia\")) document.getElementById(\"vigencia\").innerHTML = '$vigencia';";

	}

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var cliente, condominio, pfpj, ccsc;

function adicionarAssinatura(cliente_id, assinatura_id) {
	if (cliente_id) {
		if (typeof assinatura_id == 'undefined') assinatura_id = "";
		if (typeof condominio_id == 'undefined') condominio_id = "";
		condominio_id = document.getElementById("condominio") ? document.getElementById("condominio").value : condominio_id = "";
		abrir("assinaturas", "?m=assinaturas&a=adicionar&cliente_id=" + cliente_id + "&assinatura_id=" + assinatura_id + "&condominio_id=" + condominio_id, 625, 520, 1);
	}
}

function adicionarAssinaturaAdicional(cliente_id, assinatura_id) {
	if (cliente_id) {
		condominio_id = document.getElementById("condominio") ? document.getElementById("condominio").value : condominio_id = "";
		window.location = "?m=assinaturas&a=adicionar&cliente_id=" + cliente_id + "&assinatura_id=" + assinatura_id + "&condominio_id=" + condominio_id;
	}
}

function verAssinatura(assinatura_id) {
	if (assinatura_id) {
		abrir("assinaturas", "?m=assinaturas&a=ver&i=" + assinatura_id, 625, 520, 1);
	}
}

function buscaAssinaturaServicos() {
	url = "rds.js.php?m=assinaturas&a=busca_servicos&pfpj=" + pfpj + "&ccsc=" + ccsc + "&cli=" + cliente + "&condominio=" + condominio + "&origem=" + origem;
	RDSCarregar("assinaturas_busca_servicos", url);
}

function buscaAssinaturaPlanos(servico, plano) {
	if (servico) {
		url = "rds.js.php?m=assinaturas&a=busca_planos&ser=" + servico + "&pfpj=" + pfpj + "&ccsc=" + ccsc + "&plano=" + plano + "&condominio=" + condominio + "&origem=" + origem;
		RDSCarregar("assinaturas_busca_planos", url);
	}
}

function buscaAssinaturaDadosPlano(plano, sistema) {
	if (plano) {
		url = "rds.js.php?m=assinaturas&a=busca_dados_plano&pla=" + plano + "&sistema=" + sistema + "&con=" + condominio + "&cli=" + cliente;
		RDSCarregar("assinaturas_busca_dados_plano", url);
	}
}

function buscaAssinaturaAdicionais(assinatura, plano) {
	if (assinatura) {
		url = "rds.js.php?m=assinaturas&a=busca_adicionais&ass=" + assinatura + "&pfpj=" + pfpj + "&ccsc=" + ccsc + "&plano=" + plano + "&condominio=" + condominio + "&origem=" + origem;
		RDSCarregar("assinaturas_busca_adicionais", url);
	}
}

function buscaAssinaturaDadosAdicional(adicional, sistema_padrao) {
	url = "rds.js.php?m=assinaturas&a=busca_dados_adicional&pld=" + adicional + "&sistema_padrao=" + sistema_padrao + "&con=" + condominio + "&cli=" + cliente;
	RDSCarregar("assinaturas_busca_dados_adicional", url);
}

function buscaAssinaturaDescontos(assinatura) {
	if (assinatura && window['cliente']) {
		url = "rds.js.php?m=assinaturas&a=busca_assinatura_descontos&ass=" + assinatura + "&con=" + condominio + "&cli=" + cliente;
		RDSCarregar("assinaturas_busca_assinatura_descontos", url);
		if (window.opener && window.opener.atualizaAssinaturas && document.getElementById("assinaturas") && document.getElementById("descontos") && document.getElementById("parcelamento")) {
			window.opener.atualizaAssinaturas();
		}
	}
}

function buscaAssinaturasCliente(cliente_id) {
	if (cliente_id) {
		var lista_status = new Array();
		if ((document.getElementById('status_1') && document.getElementById('status_1').checked) || !document.getElementById('status_1')) lista_status[lista_status.length] = '1';
		if ((document.getElementById('status_2') && document.getElementById('status_2').checked) || !document.getElementById('status_2')) lista_status[lista_status.length] = '2,5';
		if ((document.getElementById('status_3') && document.getElementById('status_3').checked) || !document.getElementById('status_3')) lista_status[lista_status.length] = '3';
		if ((document.getElementById('status_4') && document.getElementById('status_4').checked) || !document.getElementById('status_4')) lista_status[lista_status.length] = '4';
		if ((document.getElementById('status_5') && document.getElementById('status_5').checked) || !document.getElementById('status_5')) lista_status[lista_status.length] = '6';
		assinatura_status = lista_status.join(',');
		url = "rds.js.php?m=assinaturas&a=busca_assinaturas_cliente&cliente_id=" + cliente_id + "&status=" + assinatura_status;
		RDSCarregar("assinaturas_busca_assinaturas_cliente", url);
	}
}

function mudaAssinaturaServico(campo) {
	servico = campo.value;
	campo.form.instalacao.value = "";
	campo.form.mensalidade.value = "";
	atualizaSelect(campo.form.plano);
	if (document.getElementById("descontos")) document.getElementById("descontos").innerHTML = "Selecione um plano";
	if (document.getElementById("plano")) document.getElementById("plano").value = "";
	if (document.getElementById("plano")) document.getElementById("plano").disabled = true;
	if (document.getElementById("sistema")) document.getElementById("sistema").value = "";
	if (document.getElementById("sistema")) document.getElementById("sistema").disabled = true;
	if (servico) buscaAssinaturaPlanos(servico);
}

function mudaAssinaturaPlano(campo) {
	plano = campo.value;
	sistema = campo.form.sistema.value;
	if (plano) {
		buscaAssinaturaDadosPlano(plano, sistema);
	} else {
		campo.form.instalacao.value = "";
		campo.form.mensalidade.value = "";
		if (document.getElementById("descontos")) document.getElementById("descontos").innerHTML = "Selecione um plano";
		if (document.getElementById("formas_pagamento")) document.getElementById("formas_pagamento").innerHTML = "Selecione um plano";
		if (document.getElementById("sistema")) document.getElementById("sistema").value = "";
		if (document.getElementById("sistema")) document.getElementById("sistema").disabled = true;
	}
}

function mudaSistema(campo) {
	sistema = campo.value;
	plano = campo.form.plano.value;
	if (plano) {
		buscaAssinaturaDadosPlano(plano, sistema);
	} else {
		campo.form.instalacao.value = "";
		campo.form.mensalidade.value = "";
		if (document.getElementById("descontos")) document.getElementById("descontos").innerHTML = "Selecione um plano";
		if (document.getElementById("sistema")) document.getElementById("sistema").value = "";
		if (document.getElementById("sistema")) document.getElementById("sistema").disabled = true;
	}
}

function mudaAssinaturaAdicional(campo, sistema_padrao) {
	buscaAssinaturaDadosAdicional(campo.value, sistema_padrao);
}

function validaAssinatura(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.servico && !formulario.servico.value) {
		erro_vazio[erro_vazio.length] = "Serviço";
	}
	if (formulario.plano && !formulario.plano.value) {
		erro_vazio[erro_vazio.length] = "Plano";
	}
	if (formulario.sistema && !formulario.sistema.value) {
		erro_vazio[erro_vazio.length] = "Sistema";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
