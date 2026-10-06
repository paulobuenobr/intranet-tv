<?php

if ($_GET["a"]) {

	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$contratos = controle::instanciaObjeto("contratos", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "buscar_contrato") {

		$cliente = $contratos->buscaClienteContrato($_GET["codigo"]);
		if (!$cliente) {
			$cliente = "Contrato não encontrado";
		}
		$status = $cliente ? "false" : "true";

		print "if (document.getElementById(\"cliente\")) document.getElementById(\"cliente\").innerHTML = '".addslashes($cliente)."';";
		print "if (document.getElementById(\"publicar\")) document.getElementById(\"publicar\").disabled = $status;";

	} elseif ($_GET["a"] == "buscar_contratos") {

		$dados_cliente = $contratos->buscaDadosCliente($_GET["cliente_id"]);
		$contratos = $contratos->buscaContratosCliente($_GET["cliente_id"]);

		if ($dados_cliente) {
			print "document.getElementById(\"cliente\").innerHTML = '".addslashes($dados_cliente["nome"])."';";
		} else {
			print "document.getElementById(\"cliente\").innerHTML = 'Cliente não encontrado';";
		}

		if (count($contratos) > 0) {
			
			$select = "<select name=\"codigo\" class=\"inputBrancoAzulEscuro11\" size=\"".(count($contratos) + 1)."\" style=\"width:300px;height:auto;\">";
			for ($i = 0; $i < count($contratos); $i++) {
				$select .= "<option value=\"{$contratos[$i]}\">{$contratos[$i]}</option>";
			}
			$select .= "</select>";

			print "document.getElementById(\"campo_codigo\").innerHTML = \"".addslashes($select)."\";";
			print "document.getElementById(\"publicar\").disabled = false;";
			
		} else {
			print "document.getElementById(\"campo_codigo\").innerHTML = \"<input name=\\\"codigo\\\" id=\\\"codigo\\\" type=\\\"text\\\" class=\\\"inputBrancoAzulEscuro11\\\" maxlength=\\\"20\\\" style=\\\"width:300px\\\" onblur=\\\"return formataContrato(this, event)\\\" onkeypress=\\\"return formataContrato(this, event)\\\" />";
			print "document.getElementById(\"publicar\").disabled = false;";
		}

	} elseif ($_GET["a"] == "buscar" && $_GET["cli"]) {

		$_GET["status"] = !$_GET["status"] ? "0" : $_GET["status"];
		$listagem = $contratos->geraListagemClientesContratos($contratos->buscaClientesContratos($_GET["cli"], $_GET["status"]));
		$botao_adicionar = $contratos->buscaBotaoAdicionar($_GET["cli"]);
		$clientes = controle::instanciaObjeto("clientes", $bancodedados, $seguranca->permissoes, false);
		
		print "if (document.getElementById(\"contratos\")) document.getElementById(\"contratos\").innerHTML = '".addslashes($listagem)."';";
		print "if (document.getElementById(\"contratos_adicionar\")) document.getElementById(\"contratos_adicionar\").innerHTML = '".addslashes($botao_adicionar)."';";
		print "if (document.getElementById(\"cliente_situacao\")) document.getElementById(\"cliente_situacao\").innerHTML = '".addslashes($clientes->buscaSituacaoFinanceira($_GET["cli"]))."';";
		print "if (document.getElementById(\"cliente_status\")) document.getElementById(\"cliente_status\").innerHTML = '".addslashes($clientes->buscaStatusCliente($_GET["cli"], true))."';";

	} elseif ($_GET["a"] == "muda_servico" && $_GET["cli"] && $_GET["servico"]) {

		$assinaturas = $contratos->geraListagemAssinaturasEditavel($contratos->buscaAssinaturasEditavel($_GET["cli"], 0, $_GET["servico"]));
		$descontos = $contratos->geraListagemDescontos($contratos->buscaDescontosProspect($_GET["cli"]));
		$dados_assinaturas = $contratos->buscaDadosAssinaturasProspect($_GET["cli"], $_GET["servico"]);
		
		print "document.getElementById(\"assinaturas\").innerHTML = \"".addslashes($assinaturas)."\";";
		print "document.getElementById(\"descontos\").innerHTML = \"".addslashes($descontos)."\";";
		print "document.getElementById(\"mensalidade_valor\").innerHTML = \"{$dados_assinaturas["mensalidade"]}\";";
		print "document.getElementById(\"instalacao_valor\").innerHTML = \"{$dados_assinaturas["instalacao"]}\";";
		print "document.getElementById(\"parcelamento\").innerHTML = \"{$dados_assinaturas["parcelamento"]}\";";
		print "document.getElementById(\"informacoes_assinaturas\").style.display = \"table-row-group\";";

	} elseif ($_GET["a"] == "gera_aditivo_alteracao_assinaturas" && $_GET["cli"] && $_GET["servico"]) {

		$assinaturas = $contratos->geraListagemAssinaturasEditavel($contratos->buscaAssinaturasEditavel($_GET["cli"], $_GET["contrato"], $_GET["servico"]));
		
		print "document.getElementById(\"assinaturas\").innerHTML = \"".addslashes($assinaturas)."\";";

	} elseif ($_GET["a"] == "atualiza_assinaturas" && $_GET["cli"] && $_GET["servico"]) {

		if ($_GET["contrato"] && !$contratos->ehSincronizado($_GET["contrato"])) {
			print "window.location = '?m=contratos&a=ver&i={$_GET["contrato"]}';";
		} else {
			
			if (!$_GET["manter_assinaturas"]) {
				$assinaturas = $contratos->geraListagemAssinaturasEditavel($contratos->buscaAssinaturasEditavel($_GET["cli"], $_GET["contrato"], $_GET["servico"], explode(",", $_GET["assinaturas"])));
				print "document.getElementById(\"assinaturas\").innerHTML = \"".addslashes($assinaturas)."\";";
			}
			
			$descontos = $contratos->geraListagemDescontos($contratos->buscaDescontosProspect($_GET["cli"], $_GET["assinaturas"] ? $_GET["assinaturas"] : "0"));
			$dados_assinaturas = $contratos->buscaDadosAssinaturasProspect($_GET["cli"], $_GET["servico"], $_GET["assinaturas"] ? $_GET["assinaturas"] : "0");
			
			print "if (document.getElementById(\"descontos\")) document.getElementById(\"descontos\").innerHTML = \"".addslashes($descontos)."\";";
			print "if (document.getElementById(\"mensalidade_valor\")) document.getElementById(\"mensalidade_valor\").innerHTML = \"{$dados_assinaturas["mensalidade"]}\";";
			print "if (document.getElementById(\"instalacao_valor\")) document.getElementById(\"instalacao_valor\").innerHTML = \"{$dados_assinaturas["instalacao"]}\";";
			print "if (document.getElementById(\"parcelamento\")) document.getElementById(\"parcelamento\").innerHTML = \"{$dados_assinaturas["parcelamento"]}\";";
			print "if (document.getElementById(\"multa\")) document.getElementById(\"multa\").innerHTML = \"{$dados_assinaturas["multa"]}\";";
			print "if (document.getElementById(\"vigencia\")) document.getElementById(\"vigencia\").innerHTML = \"{$dados_assinaturas["vigencia"]}\";";
			print "if (document.getElementById(\"informacoes_assinaturas\")) document.getElementById(\"informacoes_assinaturas\").style.display = \"table-row-group\";";
			
		}

	} elseif ($_GET["a"] == "gera_aditivo_migracao" && $_GET["contrato"]) {

		$assinaturas = $contratos->buscaAssinaturasAditivoMigracao($_GET["contrato"]);
		$cabecalho = array ("checkbox" => "", "plano_nome" => "Plano Atual", "novo_plano" => "Plano Novo");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $assinaturas, 0, false, array(15, 0, 260));
		
		print "document.getElementById(\"assinaturas\").innerHTML = \"".addslashes($listagem->conteudo)."\";";
		for ($i = 0; $i < count($assinaturas); $i++) {
			print "pontuacao_atual[$i] = '{$assinaturas[$i]["parametros"]["pontos_velocidade_download"]}';";
			print "pontuacao_nova[$i] = '{$assinaturas[$i]["parametros"]["pontos_velocidade_download"]}';";
		}
	
	}

} else {

	if (false) { ?><script type="text/javascript"><?php }

?>

var WContratoVisualizar;

function buscaContratosCliente(cliente) {
	if (cliente) {
		var lista_status = new Array();
		if (document.getElementById('status_1') && document.getElementById('status_1').checked) lista_status[lista_status.length] = '1,2,11,12,13,14,15,16,17,18';
		if (document.getElementById('status_2') && document.getElementById('status_2').checked) lista_status[lista_status.length] = '3,4,19,20,21';
		if (document.getElementById('status_3') && document.getElementById('status_3').checked) lista_status[lista_status.length] = '5,22';
		if (document.getElementById('status_4') && document.getElementById('status_4').checked) lista_status[lista_status.length] = '23';
		if (document.getElementById('status_5') && document.getElementById('status_5').checked) lista_status[lista_status.length] = '24';
		contrato_status = lista_status.join(',');
		url = "rds.js.php?m=contratos&a=buscar&cli=" + cliente + "&status=" + contrato_status;
		RDSCarregar("contratos_buscar", url);
	}
}

function adicionarContrato(cliente, contrato) {
	if (cliente) {
		abrir("contrato", "?m=contratos&a=adicionar&cli=" + cliente + (contrato ? "&cor=" + contrato : ""), 625, 620, 1);
	}
}

function verContrato(contrato) {
	if (contrato) {
		abrir("contrato", "?m=contratos&a=ver&i=" + contrato, 625, 620, 1);
	}
}

function verAssinatura(assinatura) {
	if (assinatura) {
		abrir("assinatura", "?m=assinaturas&a=ver&i=" + assinatura, 625, 520, 1);
	}
}

function visualizarContrato(contrato, tipo, exportar) {
	if (contrato) {
		if (exportar) {
			window.location = "?m=contratos&a=visualizar_" + tipo + "&i=" + contrato;
		} else {
			if (WContratoVisualizar != null) WContratoVisualizar.close();
			parametros = "top,left,width=" + ((screen.availWidth > 800) ? 800 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 95) + "statusbar=no,toolbar=yes,scrollbars=yes,resizable=1";
			WContratoVisualizar = window.open("?m=contratos&a=visualizar_" + tipo + "&i=" + contrato, "contrato_visualizar", parametros);
		}
	}
}

function mudaContratoServico(servico, cliente, contrato) {
	if (servico && cliente) {
		url = "rds.js.php?m=contratos&a=muda_servico&cli=" + cliente + "&servico=" + servico + "&contrato=" + contrato;
		RDSCarregar("contratos_muda_servico", url);
	} else {
		document.getElementById("informacoes_assinaturas").style.display = "none";
	}
}

function atualizaAssinaturas(manter_assinaturas) {
	url = "rds.js.php?m=contratos&a=atualiza_assinaturas&cli=" + cliente_id + "&servico=" + (document.getElementById("servico") ? document.getElementById("servico").value : servico_id) + "&assinaturas=" + pegaAssinaturas() + "&contrato=" + (typeof(contrato_id) == 'undefined' ? 0 : contrato_id) + "&manter_assinaturas=" + (manter_assinaturas ? manter_assinaturas : 0);
	RDSCarregar("contratos_atualiza_assinaturas", url);
}

function pegaAssinaturas() {
	var i = 0;
	var assinaturas = new Array();
	while (assinatura = document.getElementById('assinaturas_' + i)) {
		if (assinatura.checked) {
			assinaturas[assinaturas.length] = assinatura.value;
		}
		i++;
	}
	return assinaturas.join(",");
}

function mudaMesmoEndereco(campo, evento) {
	if (campo.checked) {
		// Atualiza valores
		campo.form.cobranca_condominio.value = campo.form.condominio.value;
		copiaSelect(campo.form.bloco, campo.form.cobranca_bloco);
		campo.form.cobranca_cep.value = campo.form.cep.value;
		campo.form.cobranca_estado.value = campo.form.estado.value;
		campo.form.cobranca_cidade.value = campo.form.cidade.value;
		campo.form.cobranca_bairro.value = campo.form.bairro.value;
		campo.form.cobranca_logradouro.value = campo.form.logradouro.value;
		campo.form.cobranca_numero.value = campo.form.numero.value;
		campo.form.cobranca_complemento_tipo.value = campo.form.complemento_tipo.value;
		campo.form.cobranca_complemento_valor.value = campo.form.complemento_valor.value;
		// Desabilita campos
		campo.form.cobranca_condominio.disabled = true;
		campo.form.cobranca_bloco.disabled = true;
		campo.form.cobranca_cep.disabled = true;
		campo.form.cobranca_numero.disabled = true;
		campo.form.cobranca_complemento_tipo.disabled = true;
		campo.form.cobranca_complemento_valor.disabled = true;
	} else {
		campo.form.cobranca_condominio.disabled = false;
		campo.form.cobranca_bloco.disabled = false;
		if (!campo.form.cobranca_condominio.value) {
			campo.form.cobranca_cep.disabled = false;
			campo.form.cobranca_estado.disabled = false;
			campo.form.cobranca_cidade.disabled = false;
			campo.form.cobranca_bairro.disabled = false;
			campo.form.cobranca_numero.disabled = false;
		}
		campo.form.cobranca_complemento_tipo.disabled = false;
		campo.form.cobranca_complemento_valor.disabled = false;
	}
}

function copiaSelect(origem, destino) {
	var dados = new Array();
	for (i = 0; i < origem.options.length; i++) {
		if (origem.options[i].value) {
			dados[dados.length] = new Array(origem.options[i].value, origem.options[i].innerHTML);
		}
	}
	atualizaSelect(destino, dados, origem.value, 1, 1, 0);
}

function atualizaBlocoCobranca() {
	if (document.getElementById('mesmo_endereco').checked) {
		document.getElementById('cobranca_bloco').value = document.getElementById('bloco').value;
	}
}

function atualizaComplementoTipoCobranca() {
	if (document.getElementById('mesmo_endereco').checked) {
		document.getElementById('cobranca_complemento_tipo').value = document.getElementById('complemento_tipo').value;
	}
}

function atualizaComplementoValorCobranca() {
	if (document.getElementById('mesmo_endereco').checked) {
		document.getElementById('cobranca_complemento_valor').value = document.getElementById('complemento_valor').value;
	}
}

function atualizaNumeroCobranca() {
	if (document.getElementById('mesmo_endereco').checked) {
		document.getElementById('cobranca_numero').value = document.getElementById('numero').value;
	}
}

function atualizaCondominioCobranca() {
	if (document.getElementById('mesmo_endereco').checked) {
		document.getElementById('cobranca_condominio').value = document.getElementById('condominio').value;
	}
}

function mudaContratoTipo(tipo, servico, contrato, cliente) {
	if (!tipo) {
		document.getElementById("assinaturas").innerHTML = "Selecione um Tipo";
	} else if (tipo == 12) { // Aditivo Alteração de Assinaturas
		url = "rds.js.php?m=contratos&a=gera_aditivo_alteracao_assinaturas&cli=" + cliente + "&servico=" + servico + "&contrato=" + contrato;
		RDSCarregar("contratos_gera_aditivo_alteracao_assinaturas", url);
	} else if (tipo == 13) { // Aditivo Migração de Assinaturas
		url = "rds.js.php?m=contratos&a=gera_aditivo_migracao&contrato=" + contrato;
		RDSCarregar("contratos_gera_aditivo_migracao", url);
	} else if (tipo == 14) { // Aditivo Mudança de Endereço
		window.open("?m=contratos&a=adicionar_aditivo_me&i=" + contrato, "_self");
	} else if (tipo == 15) { // Aditivo Alteração de Dados Cadastrais
		window.open("?m=contratos&a=adicionar_aditivo_mt&i=" + contrato, "_self");
	} else {
		document.getElementById("assinaturas").innerHTML = "Selecione um Tipo";
	}
}

function selecionaAssinatura(ordem) {
	document.getElementById('novo_plano_' + ordem).disabled = !document.getElementById('assinaturas_' + ordem).checked;
	document.getElementById('migrar_descontos_' + ordem).disabled = !document.getElementById('assinaturas_' + ordem).checked;
	document.getElementById('novo_plano_' + ordem).value = ''
	pontuacao_nova[ordem] = pontuacao_atual[ordem];
}

function mudaNovoPlano(campo, ordem) {
	novo_plano = campo.value.split(",");
	pontuacao_nova[ordem] = novo_plano[1];
}

function validaPontuacao() {
	for (var i = 1; i < pontuacao_nova.length; i++) {
		if (parseInt(pontuacao_nova[0], 10) < parseInt(pontuacao_nova[i], 10)) return false;
	}
	return true;
}

function nenhumaAssinaturaSelecionada() {
	var i = 0;
	var selecionado = false;
	while (assinatura = document.getElementById('assinaturas_' + i)) {
		selecionado = selecionado || assinatura.checked;
		i++;
	}
	return !selecionado;
}

function novoPlanoEmBranco() {
	var i = 0;
	var erro = false;
	while (assinatura = document.getElementById('assinaturas_' + i)) {
	erro = erro || (assinatura.checked && !document.getElementById('novo_plano_' + i).value);
	i++;
	}
	return erro;
}

function validaContratoComercialEnviar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.emails.value) {
		erro_vazio[erro_vazio.length] = "E-mail(s)";
	} else if (!validaEmails(formulario.emails.value)) {
		erro_invalido[erro_invalido.length] = "E-mail(s)";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaContratoPrincipalAdicionar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.servico.value) {
		erro_vazio[erro_vazio.length] = "Serviço";
	}
	if (nenhumaAssinaturaSelecionada()) {
		erro_vazio[erro_vazio.length] = "Assinaturas";
	}
	if (formulario.parcelamento && !formulario.parcelamento.value) {
		erro_vazio[erro_vazio.length] = "Parcelamento";
	}
	if (retorno = apresentaErros(erro_vazio, erro_invalido)) {
		formulario.salvar.disabled = true;
	}
	return retorno;
}

function validaContratoAditivoAdicionar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	if(document.getElementById('contrato_fidelidade_2').checked){
		if ((formulario.vigencia_minima.value * 1) > (formulario.vigencia.value * 1)) {
			erro_vazio[erro_vazio.length] = "Vigencia deve ser maior que " + formulario.vigencia_minima.value + " meses";
		}
		if ((formulario.valor_minimo_multa.value * 1) > (limpaStringNumerico(formulario.valor_multa.value) * 1)) {
			var multa = formulario.valor_minimo_multa.value;
			erro_vazio[erro_vazio.length] = "Multa deve ser maior que R$ " + (multa / 100).toFixed(2).replace(".", ",");
		}
	}
	if (formulario.tipo.value == 13) {
		if (nenhumaAssinaturaSelecionada()) {
			erro_vazio[erro_vazio.length] = "Assinaturas (No mínimo uma)";
		} else if (novoPlanoEmBranco()) {
			erro_vazio[erro_vazio.length] = "Novo Plano";
		}
	}
	if (retorno = apresentaErros(erro_vazio, erro_invalido)) {
		formulario.salvar.disabled = true;
	}
	return retorno;
}

function validaContratoPrincipalEditar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.data_geracao.value) {
		erro_vazio[erro_vazio.length] = "Data » Geração";
	} else if (!validaData(formulario.data_geracao.value)) {
		erro_invalido[erro_invalido.length] = "Data » Geração";
	}
	if (!formulario.data_ativacao.disabled) {
		if (!formulario.data_ativacao.value){
			erro_vazio[erro_vazio.length] = "Data » Ativação";
		} else if (!validaData(formulario.data_ativacao.value)) {
			erro_invalido[erro_invalido.length] = "Data » Ativação";
		}
	}
	if (!formulario.data_anulacao.disabled) {
		if (!formulario.data_anulacao.value){
			erro_vazio[erro_vazio.length] = "Data » Anulação";
		} else if (!validaData(formulario.data_anulacao.value)) {
			erro_invalido[erro_invalido.length] = "Data » Anulação";
		}
	}
	if (!formulario.data_cancelamento.disabled) {
		if (!formulario.data_cancelamento.value){
			erro_vazio[erro_vazio.length] = "Data » Cancelamento";
		} else if (!validaData(formulario.data_cancelamento.value)) {
			erro_invalido[erro_invalido.length] = "Data » Cancelamento";
		}
		if (subtraiDatas(formulario.data_cancelamento.value, formulario.data_ativacao.value) < 0) {
			erro_invalido[erro_invalido.length] = "Data » Cancelamento anterior Data » Ativação";
		}
	}
	if (retorno = apresentaErros(erro_vazio, erro_invalido)) {
		formulario.salvar.disabled = true;
	}
	return retorno;
}

function validaContratoAditivoEditar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.tipo.value == 13 && !formulario.data_ativacao.value) {
		if (nenhumaAssinaturaSelecionada()) {
			erro_vazio[erro_vazio.length] = "Assinaturas";
		} else if (novoPlanoEmBranco()) {
			erro_vazio[erro_vazio.length] = "Novo Plano";
		}
	}
	if (!formulario.data_geracao.value) {
		erro_vazio[erro_vazio.length] = "Data » Geração";
	} else if (!validaData(formulario.data_geracao.value)) {
		erro_invalido[erro_invalido.length] = "Data » Geração";
	}
	if (!formulario.data_ativacao.disabled) {
		if (!formulario.data_ativacao.value){
			erro_vazio[erro_vazio.length] = "Data » Ativação";
		} else if (!validaData(formulario.data_ativacao.value)) {
			erro_invalido[erro_invalido.length] = "Data » Ativação";
		}
	}
	if (!formulario.data_anulacao.disabled) {
		if (!formulario.data_anulacao.value){
			erro_vazio[erro_vazio.length] = "Data » Anulação";
		} else if (!validaData(formulario.data_anulacao.value)) {
			erro_invalido[erro_invalido.length] = "Data » Anulação";
		}
	}
	if (!formulario.data_cancelamento.disabled) {
		if (!formulario.data_cancelamento.value){
			erro_vazio[erro_vazio.length] = "Data » Cancelamento";
		} else if (!validaData(formulario.data_cancelamento.value)) {
			erro_invalido[erro_invalido.length] = "Data » Cancelamento";
		}
		if (subtraiDatas(formulario.data_cancelamento.value, formulario.data_ativacao.value) < 0) {
			erro_invalido[erro_invalido.length] = "Data » Cancelamento anterior Data » Ativação";
		}
	}
	if (retorno = apresentaErros(erro_vazio, erro_invalido)) {
		formulario.salvar.disabled = true;
	}
	return retorno;
}

function validaContratoCancelar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.data_cancelamento.value) {
		erro_vazio[erro_vazio.length] = "Data » Cancelamento";
	} else if (!validaData(formulario.data_cancelamento.value)) {
		erro_invalido[erro_invalido.length] = "Data » Cancelamento";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaContratoAtivar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.instalacao_valor && !formulario.instalacao_valor.value) {
		erro_vazio[erro_vazio.length] = "Valores » Instalação";
	}
	if (formulario.parcelamento && !formulario.parcelamento.disabled && !formulario.parcelamento.value) {
		erro_vazio[erro_vazio.length] = "Valores » Parcelamento";
	}
	if (!formulario.data_ativacao.value) {
		erro_vazio[erro_vazio.length] = "Ativação » Data";
	} else if (!validaData(formulario.data_ativacao.value)) {
		erro_invalido[erro_invalido.length] = "Ativação » Data";
	} else if (formulario.contrato_tipo && formulario.contrato_tipo.value ==  13) {
		if (subtraiDatas(formulario.data_ativacao.value, geraDataAtual()) < 0) {
			erro_invalido[erro_invalido.length] = "Ativação » Data anterior a data atual (" + geraDataAtual() + ")";
		}
	}
	if (formulario.instalador && !formulario.instalador.value && !formulario.instalador.disabled) {
		erro_vazio[erro_vazio.length] = "Ativação » Instalador";
	}
	var forma_pagamento = null;
	var i = 0;
	while ((selection = document.getElementById('situacao_' + i)) && !forma_pagamento) {
		if (selection.checked) {
			forma_pagamento = selection.value;
		}
		i++;
	}
	if (i > 0 && !forma_pagamento) {
		erro_vazio[erro_vazio.length] = "Adesão / Forma de Pagamento";
	}
	if (formulario.formato && !formulario.formato.value) {
		erro_vazio[erro_vazio.length] = "Cobrança » Formato";
	}
	if (formulario.nf_numero && !formulario.nf_numero.disabled) {
		var nf_numero = limpaStringNumerico(formulario.nf_numero.value);
		nf_numero = nf_numero ? parseInt(nf_numero, 10) : 0;
		if (!nf_numero) {
			//erro_vazio[erro_vazio.length] = "Nota Fiscal » Número";
		}
	}
	if (formulario.nf_emissao && !formulario.nf_emissao.disabled) {
		if (!formulario.nf_emissao.value) {
			//erro_vazio[erro_vazio.length] = "Nota Fiscal » Emissão";
		} else if (!validaData(formulario.nf_emissao.value)) {
			erro_invalido[erro_invalido.length] = "Nota Fiscal » Emissão";
		}
	}
	if (formulario.nf_valor && !formulario.nf_valor.disabled) {
		if (!formulario.nf_valor.value) {
			//erro_vazio[erro_vazio.length] = "Nota Fiscal » Valor";
		} else if (!validaDinheiro(formulario.nf_valor.value)) {
			erro_invalido[erro_invalido.length] = "Nota Fiscal » Valor";
		} else {
			var entrada = limpaStringNumerico(document.getElementById("entrada_" + forma_pagamento).innerHTML);
			var parcelas = limpaStringNumerico(document.getElementById("parcelas_" + forma_pagamento).innerHTML);
			var valor_parcela = limpaStringNumerico(document.getElementById("valor_parcela_" + forma_pagamento).innerHTML);
			var valor_nf = limpaStringNumerico(formulario.nf_valor.value);
			entrada = entrada ? parseInt(entrada, 10) : 0;
			parcelas = parcelas ? parseInt(parcelas, 10) : 0;
			valor_parcela = valor_parcela ? parseInt(valor_parcela, 10) : 0;
			valor_nf = valor_nf ? parseInt(valor_nf, 10) : 0;
			if (valor_nf < entrada + (valor_parcela * parcelas)) {
				erro_invalido[erro_invalido.length] = "Nota Fiscal » Valor inferior ao da adesão";
			}
		}
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function mudaValorInstalacao(valor) {
	if (valor == 0) {
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').value = '';
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').disabled = true;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').disabled = false;
	} else if (valor == 1) {
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').disabled = false;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').checked = false;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').disabled = true;
		if (document.getElementById('instalador')) document.getElementById('instalador').disabled = false;
	} else if (valor == 2) {
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').value = '';
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').disabled = true;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').checked = false;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').disabled = true;
		if (document.getElementById('instalador')) document.getElementById('instalador').disabled = false;
	}
}

function mudaValorInstalacaoAditivo(valor) {
	if (valor == 0) {
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').value = '';
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').disabled = true;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').disabled = false;
	} else if (valor == 1) {
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').disabled = true;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').checked = false;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').disabled = true;
		if (document.getElementById('instalador')) document.getElementById('instalador').disabled = false;
	} else if (valor == 2) {
		if (document.getElementById('parcelamento')) document.getElementById('parcelamento').disabled = false;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').checked = false;
		if (document.getElementById('sem_instalador')) document.getElementById('sem_instalador').disabled = true;
		if (document.getElementById('instalador')) document.getElementById('instalador').disabled = false;
	}
}

function desabilitaCampos(){
	if(document.getElementById('vigencia') && document.getElementById('valor_multa')){
		document.getElementById('vigencia').disabled = true;
		document.getElementById('valor_multa').disabled = true;
	}
}

function habilitaCampos(){
	if(document.getElementById('vigencia') && document.getElementById('valor_multa')){
		document.getElementById('vigencia').disabled = false;
		document.getElementById('valor_multa').disabled = false;
	}
}

function validaContratosDescancelar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.comentario && !formulario.comentario.value) {
		erro_vazio[erro_vazio.length] = "Comentário » Informe a razão do descancelamento";
	}
	return retorno;
}

function validaContratoAditivoME(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.duplicado_cpf_cnpj.value == 1 && formulario.duplicado_endereco.value == 1 && (formulario.duplicar_cpf_cnpj.value == 0 || formulario.duplicar_endereco.value == 0)) {
		alert('Você não tem permissão para adicionar um contrato em que o cliente tenha endereço e CPF/CNPJ duplicados.');
		return false;
	}
	if (formulario.duplicado_cpf_cnpj.value == 1 && formulario.duplicar_cpf_cnpj.value == 0) {
		alert('Você não tem permissão para adicionar um contrato em que o cliente tenha CPF ou CNPJ duplicado.');
		return false;
	}
	if (formulario.duplicado_endereco.value == 1 && formulario.duplicar_endereco.value == 0) {
		alert('Você não tem permissão para adicionar um contrato em que o cliente tenha endereço duplicado.');
		return false;
	}
	var selecao = new Array();
	var i = 0;
	while (selection = document.getElementById('forma_pagamento_' + i)) {
		selecao[selection.getAttribute('name')] = selection.checked || selecao[selection.getAttribute('name')] == true;
		i++;
	}
	for (var i in selecao) {
		if (!selecao[i]) {
			erro_vazio[erro_vazio.length] = i.substr(20, i.length - 21);
		}
	}
	if (formulario.condominio.value) {
		if (!formulario.bloco.value) {
			erro_vazio[erro_vazio.length] = "Endereço de Instalação » Bloco";
		}
	} else {
		if (!formulario.cep.value) {
			erro_vazio[erro_vazio.length] = "Endereço de Instalação » CEP";
		}
	}
	if (formulario.complemento_tipo.value != 'o' && !formulario.complemento_valor.value) {
		erro_vazio[erro_vazio.length] = "Endereço de Instalação  » Complemento";
	} else {
		if (formulario.complemento_tipo.value != 'o' && isNaN(formulario.complemento_valor.value)) {
			erro_invalido[erro_invalido.length] = "Endereço de Instalação  » Complemento";
		}
	}
	if (!formulario.mesmo_endereco.checked) {
		if (formulario.cobranca_condominio.value) {
			if (!formulario.cobranca_bloco.value) {
				erro_vazio[erro_vazio.length] = "Endereço de Cobrança / Correspondência » Bloco";
			}
		} else {
			if (!formulario.cobranca_cep.value) {
				erro_vazio[erro_vazio.length] = "Endereço de Cobrança / Correspondência  » CEP";
			}
		}
		if (formulario.cobranca_complemento_tipo.value != 'o' && !formulario.cobranca_complemento_valor.value) {
			erro_vazio[erro_vazio.length] = "Endereço de Cobrança / Correspondência  » Complemento";
		} else {
			if (formulario.cobranca_complemento_tipo.value != 'o' && isNaN(formulario.cobranca_complemento_valor.value)) {
				erro_invalido[erro_invalido.length] = "Endereço de Cobrança / Correspondência  » Complemento";
			}
		}
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaContratoAditivoMT(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.duplicado_cpf_cnpj.value == 1 && formulario.duplicado_endereco.value == 1) {
		alert('Você não tem permissão para adicionar um contrato em que o cliente tenha endereço e CPF/CNPJ duplicados.');
		return false;
	}
	if (formulario.duplicado_cpf_cnpj.value == 1 && formulario.duplicar_cpf_cnpj.value == 0) {
		alert('Você não tem permissão para adicionar um contrato em que o cliente tenha CPF ou CNPJ duplicado.');
		return false;
	}
	if (formulario.duplicado_endereco.value == 1 && formulario.duplicar_endereco.value == 0) {
		alert('Você não tem permissão para adicionar um contrato em que o cliente tenha endereço duplicado.');
		return false;
	}
	if (!formulario.nome.value) {
		erro_vazio[erro_vazio.length] = "Nome";
	}
	if (formulario.tipo.value == 2) {
		if (formulario.cpf_cnpj.value) {
			if (!validaCNPJ(formulario.cpf_cnpj.value)) {
				erro_invalido[erro_invalido.length] = "CNPJ";
			}
		}
	} else {
		if (formulario.cpf_cnpj.value) {
			if (!validaCPF(formulario.cpf_cnpj.value)) {
				erro_invalido[erro_invalido.length] = "CPF";
			}
		}
	}
	if (formulario.nascimento.value) {
		if (!validaData(formulario.nascimento.value)) {
			erro_invalido[erro_invalido.length] = "Nascimento";
		}
	}
	if (!formulario.mesmo_endereco.checked) {
		if (formulario.cobranca_condominio.value) {
			if (!formulario.cobranca_bloco.value) {
				erro_vazio[erro_vazio.length] = "Endereço de Cobrança / Correspondência » Bloco";
			}
		} else {
			if (!formulario.cobranca_cep.value) {
				erro_vazio[erro_vazio.length] = "Endereço de Cobrança / Correspondência  » CEP";
			}
		}
		if (formulario.cobranca_complemento_tipo.value != 'o' && !formulario.cobranca_complemento_valor.value) {
			erro_vazio[erro_vazio.length] = "Endereço de Cobrança / Correspondência  » Complemento";
		} else {
			if (formulario.cobranca_complemento_tipo.value != 'o' && isNaN(formulario.cobranca_complemento_valor.value)) {
				erro_invalido[erro_invalido.length] = "Endereço de Cobrança / Correspondência  » Complemento";
			}
		}
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function mudaFidelizacao(campo) {
	if (campo.value > 0) {
		campo.form.valor_multa.disabled = false;
	} else {
		campo.form.valor_multa.disabled = true;
		campo.form.valor_multa.value = "";
	}
}

function mudaValorInstalacao(campo, evento) {
	if (parseInt(0 + limpaStringNumerico(campo.value), 10) == 0) {
		campo.form.parcelamento.disabled = true;
		campo.form.parcelamento.value = 1;
	} else {
		campo.form.parcelamento.disabled = false;
	}
	return formataDinheiro(campo, evento);
}

function formataContrato(campo, evento) {
	var key; 
	var tecla;
	var conteudo;
	if (evento.altKey || evento.ctrlKey || evento.shiftKey) {
		return true;
	}
	if (navigator.appName.indexOf("Netscape") != -1) {
		tecla = evento.which;
	} else {
		tecla = evento.keyCode;
	}
	key = String.fromCharCode(tecla);
	if (ehTeclaValida(tecla)) {
		return true;
	} else if (ehNumero(key)) {
		campo.value = campo.value + key;
		conteudo = campo.value.replace(/[^0-9]/g, "");
		tamanho = conteudo.length;
		if (tamanho > 0) {
			if (tamanho > 17) tamanho = 17;
			conteudo_formatado = "";
			for (i = 0; i < tamanho; i++) {
				if (i == 4 || i == 15) conteudo_formatado += "/";
				if (i == 13) conteudo_formatado += "-";
				conteudo_formatado += conteudo.charAt(i);
			}
			campo.value = conteudo_formatado;
		}
	}
	if (campo.value.length >= 17) {
		url = "rds.js.php?m=contratos&a=buscar_contrato&codigo=" + campo.value;
		RDSCarregar("contratos_buscar_contrato", url);
	} else {
		document.getElementById('cliente').innerHTML = "";
		document.getElementById('publicar').disabled = true;
	}
	return false;
}

function validaContratoPublicar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.codigo.value) {
		erro_vazio[erro_vazio.length] = "Código";
	}
	if (!formulario.arquivo.value) {
		erro_vazio[erro_vazio.length] = "Arquivo";
	}
	retorno = apresentaErros(erro_vazio, erro_invalido);
	document.getElementById('publicar').disabled = retorno;
	return retorno;
}

function validaMudancaAnulacao(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.motivos_anulacao.value) {
		erro_vazio[erro_vazio.length] = "Motivo Anulação";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaAprovarTecnico(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!document.getElementById('pergunta_1_1').checked &&
		!document.getElementById('pergunta_1_2').checked &&
		!document.getElementById('pergunta_1_3').checked &&
		!document.getElementById('pergunta_1_4').checked) {
		erro_vazio[erro_vazio.length] = "Pergunta 1";
	} else if (document.getElementById('pergunta_1_4').checked && !formulario.pergunta_1_outro.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 1 » Outro";
	}
	if (!formulario.pergunta_2.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 2";
	}
	if (!formulario.pergunta_3.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 3";
	}
	if (!formulario.pergunta_4.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 4";
	}
	if (!formulario.pergunta_5.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 5";
	}
	if (!formulario.pergunta_6.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 6";
	}
	if (!document.getElementById('pergunta_7_1').checked &&
		!document.getElementById('pergunta_7_2').checked) {
		erro_vazio[erro_vazio.length] = "Pergunta 7";
	}
	if (!formulario.pergunta_8.value) {
		erro_vazio[erro_vazio.length] = "Pergunta 8";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function mudaFormaPagamento(campo) {
	var valor_total = document.getElementById("valor_total_" + campo.value).innerHTML.replace(/[^0-9]/g, "");
	if (valor_total > 0) {
		document.getElementById("nf_numero").disabled = false;
		document.getElementById("nf_emissao").disabled = false;
		document.getElementById("nf_valor").disabled = false;
	} else {
		document.getElementById("nf_numero").value = "";
		document.getElementById("nf_numero").disabled = true;
		document.getElementById("nf_emissao").value = "";
		document.getElementById("nf_emissao").disabled = true;
		document.getElementById("nf_valor").value = "";
		document.getElementById("nf_valor").disabled = true;
	}
}

function buscarContratos() {
	var cliente_id = document.getElementById("cliente_id").value;
	if (cliente_id) {
	  url = "rds.js.php?m=contratos&a=buscar_contratos&cliente_id=" + cliente_id;
	  RDSCarregar("contratos_buscar_contratos", url);
	}
}

function limparContratos() {
	document.getElementById("id").value = "";
	document.getElementById("cliente").innerHTML = "";
	document.getElementById("campo_codigo").innerHTML = "<input name=\"codigo\" id=\"codigo\" type=\"text\" class=\"inputBrancoAzulEscuro11\" maxlength=\"20\" style=\"width:300px\" onblur=\"return formataContrato(this, event)\" onkeypress=\"return formataContrato(this, event)\" />";
	document.getElementById("publicar").disabled = true;
}

<?php

	if (false) { ?></script><?php }

}

?>
