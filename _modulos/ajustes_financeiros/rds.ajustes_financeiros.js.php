<?php

if ($_GET && array_key_exists("a", $_GET)) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	/**
	 * @var ajustes_financeiros $ajustes_financeiros
	 */
	$ajustes_financeiros = controle::instanciaObjeto("ajustes_financeiros", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "buscar") {
		
		$listagem = $ajustes_financeiros->geraListagemAjustesFinanceiros($ajustes_financeiros->buscaAjustesFinanceiros($_GET["cli"], $_GET["con"]));
		$campo = ($_GET["cli"]) ? "ajustes_financeiros" : "ajustes_financeiros_condominio";
		
		print "if (document.getElementById(\"$campo\")) document.getElementById(\"$campo\").innerHTML = '" . addslashes($listagem) . "';";
		
	} elseif ($_GET["a"] == "muda_tipo") {
		
		$categorias = $ajustes_financeiros->buscaCategorias(true, null, $_GET["tipo"] == 1, $_GET["tipo"] == 2, true);
		$lista_categorias = array ("new Array(\"\", \"\")");
		
		foreach ($categorias as $id => $categoria) {
			$lista_categorias[] = "new Array(\"$id\", \"$categoria\")";
		}
		
		print "var categorias = new Array(" . implode(",", $lista_categorias) . ");";
		print "atualizaSelect(document.getElementById(\"categoria\"), categorias, 0, 0);";
		print "atualizaDescricaoCustomServico(\"\");";
		
	}
	
} else {
	
	if (false) {
		?><script type="text/javascript"><?php
	}
	
?>

function buscarAjustesFinanceiros(cliente) {
	if (cliente) {
		url = "rds.js.php?m=ajustes_financeiros&a=buscar&cli=" + cliente;
		RDSCarregar("ajustes_financeiros_buscar", url);
	}
}

function buscaAjustesCondominio(condominio) {
	if (condominio) {
		url = "rds.js.php?m=ajustes_financeiros&a=buscar&con=" + condominio;
		RDSCarregar("ajustes_financeiros_buscar", url);
	}
}

function adicionarAjusteFinanceiro(cliente) {
	if (cliente) {
		abrir("ajuste_financeiro", "?m=ajustes_financeiros&a=adicionar&cli=" + cliente, 500, 450, 1);
	}
}

function adicionarAjusteCondominio(condominio) {
	if (condominio) {
		abrir("ajuste_financeiro", "?m=ajustes_financeiros&a=adicionar&con=" + condominio, 500, 450, 1);
	}
}

function verAjusteFinanceiro(ajuste_financeiro) {
	if (ajuste_financeiro) {
		abrir("ajuste_financeiro", "?m=ajustes_financeiros&a=ver&i=" + ajuste_financeiro, 500, 450, 1);
	}
}

function validaAjusteFinanceiro(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	var erro_competencia_inicio = false;
	if (!formulario.categoria.value) {
		erro_vazio[erro_vazio.length] = "Categoria";
	}
	if (formulario.descricao_custom_servico && !formulario.descricao_custom_servico.disabled && !formulario.descricao_custom_servico.value.replace(/^\s+|\s+$/g, "")) {
		erro_vazio[erro_vazio.length] = "Descrição customizada do serviço";
	}
	if (formulario.assinatura && !formulario.assinatura.disabled && !formulario.assinatura.value) {
		erro_vazio[erro_vazio.length] = "Assinatura";
	}
	if (!formulario.competencia_inicio.value) {
		erro_vazio[erro_vazio.length] = "Competência » Início";
		erro_competencia_inicio = true;
		} else if (!validaCompetencia(formulario.competencia_inicio.value)) {
		erro_invalido[erro_invalido.length] = "Competência » Início";
		erro_competencia_inicio = true;
	}
	if (formulario.competencia_termino.value) {
		if (!validaCompetencia(formulario.competencia_termino.value)) {
			erro_invalido[erro_invalido.length] = "Competência » Término";
		} else if (!erro_competencia_inicio && (competencia_valores_erro = verificaCompetenciaValores(formulario.competencia_inicio, formulario.competencia_termino))) {
			erro_invalido[erro_invalido.length] = "Competência » " + competencia_valores_erro;
		}
	} else if (!formulario.nf_numero.disabled) {
		erro_vazio[erro_vazio.length] = "Competência » Término";
	}
	if (!formulario.valor_mensal.value) {
		erro_vazio[erro_vazio.length] = "Valor » Mensal";
	} else {
		if (formulario.valor_tipo.value == 1) {
			if (!validaDinheiro(formulario.valor_mensal.value) || parseInt(limpaStringNumerico(formulario.valor_mensal.value), 10) == 0) {
				erro_invalido[erro_invalido.length] = "Valor » Mensal";
			}
		} else if (formulario.valor_tipo.value == 2) {
			if (!validaPorcentagem(formulario.valor_mensal.value) || parseInt(limpaStringNumerico(formulario.valor_mensal.value), 10) == 0) {
				erro_invalido[erro_invalido.length] = "Valor » Mensal";
			}
		} else if (formulario.valor_tipo.value == 3) {
			if (parseInt(limpaStringNumerico(formulario.valor_mensal.value)) < 1 || parseInt(limpaStringNumerico(formulario.valor_mensal.value), 10) > 31) {
				erro_invalido[erro_invalido.length] = "Valor (entre 1 e 31)";
			}
		} else {
			if (parseInt(limpaStringNumerico(formulario.valor_mensal.value)) < 1 || parseInt(limpaStringNumerico(formulario.valor_mensal.value), 10) > 720) {
				erro_invalido[erro_invalido.length] = "Valor (entre 1 e 720)";
			}
		}
	}
	if (!formulario.nf_numero.disabled && (!formulario.nf_numero.value || parseInt(limpaStringNumerico(formulario.nf_numero.value), 10) == 0)) {
		erro_vazio[erro_vazio.length] = "Nota Fiscal » Número";
	}
	if (!formulario.nf_emissao.disabled) {
		if (!formulario.nf_emissao.value) {
			erro_vazio[erro_vazio.length] = "Nota Fiscal » Emissão";
		} else if (!validaData(formulario.nf_emissao.value)) {
			erro_invalido[erro_invalido.length] = "Nota Fiscal » Emissão";
		}
	}
	if (!formulario.nf_valor.disabled) {
		if (!formulario.nf_valor.value || parseInt(limpaStringNumerico(formulario.nf_valor.value), 10) == 0) {
			erro_vazio[erro_vazio.length] = "Nota Fiscal » Valor";
		} else if (parseInt(limpaStringNumerico(formulario.nf_valor.value), 10) < parseInt(limpaStringNumerico(formulario.valor_total.value), 10)) {
			erro_invalido[erro_invalido.length] = "Nota Fiscal » Valor menor que do ajuste";
		}
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaEditarDadosNF(formulario) {

	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.nf_numero.disabled && (!formulario.nf_numero.value || parseInt(limpaStringNumerico(formulario.nf_numero.value), 10) == 0)) {
		erro_vazio[erro_vazio.length] = "Número";
	}
	if (!formulario.nf_emissao.value) {
		erro_vazio[erro_vazio.length] = "Emissão";
	} else if (!validaData(formulario.nf_emissao.value)) {
		erro_invalido[erro_invalido.length] = "Emissão";
	}
	if (!formulario.nf_valor.value || parseInt(limpaStringNumerico(formulario.nf_valor.value), 10) == 0) {
		erro_vazio[erro_vazio.length] = "Valor";
	}
	
	return apresentaErros(erro_vazio, erro_invalido);
	
}

function verificaCompetenciaValores(competencia_inicio, competencia_termino) {
	var competencia_inicio_mes, competencia_inicio_ano, competencia_termino_mes, competencia_termino_ano;
	competencia_inicio_mes = competencia_inicio.value.substring(0,2);
	competencia_inicio_ano = competencia_inicio.value.substring(3,7);
	competencia_termino_mes = competencia_termino.value.substring(0,2);
	competencia_termino_ano = competencia_termino.value.substring(3,7);
	if (((competencia_termino_ano * 12) + competencia_termino_mes) - ((competencia_inicio_ano * 12) + competencia_inicio_mes) < 0) {
		return "Término menor que Início";
	}
	return false;
}

function validaFinalizar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.competencia_termino.value) {
		erro_vazio[erro_vazio.length] = "Competência » Término";
	} else if (!validaCompetencia(formulario.competencia_termino.value)) {
		erro_invalido[erro_invalido.length] = "Competência » Término";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaAjusteAtivar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.formato && !formulario.formato.value) {
		erro_vazio[erro_vazio.length] = "Cobranças » Formato";
	}
	if (!formulario.data_vencimento.value) {
		erro_vazio[erro_vazio.length] = "Cobranças » Vencimento";
	} else {
		if (!validaData(formulario.data_vencimento.value)) {
			erro_invalido[erro_invalido.length] = "Cobranças » Vencimento";
		} else if (subtraiDatas(formulario.data_vencimento.value, formulario.dia_vencimento.value + "/" + formulario.vencimento_inicio.value) > formulario.vencimento_maximo.value) {
			erro_invalido[erro_invalido.length] = "Cobranças » Vencimento (Maior que o máximo permitido - " + somaDias(formulario.dia_vencimento.value + "/" + formulario.vencimento_inicio.value, formulario.vencimento_maximo.value) + ")";
		}
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaCobrancaEnviar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.emails.value) {
		erro_vazio[erro_vazio.length] = "E-mail(s)";
	} else if (!validaEmails(formulario.emails.value)) {
		erro_invalido[erro_invalido.length] = "E-mail(s)";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function mudaTipo(campo) {
	document.getElementById("categoria").value = "";
	document.getElementById("assinatura").value = "";
	document.getElementById("assinatura").disabled = campo.value == 1;
	document.getElementById("competencia_inicio").value = "";
	document.getElementById("competencia_termino").value = "";
	document.getElementById("parcelas").value = "";
	document.getElementById("valor_tipo").value = "1";
	document.getElementById("valor_tipo").disabled = campo.value == 1;
	document.getElementById("valor_mensal").value = "";
	document.getElementById("valor_total").value = "";
	document.getElementById("nf_numero").value = "";
	document.getElementById("nf_numero").disabled = true;
	document.getElementById("nf_emissao").value = "";
	document.getElementById("nf_emissao").disabled = true;
	document.getElementById("nf_valor").value = "";
	document.getElementById("nf_valor").disabled = true;
	atualizaDescricaoCustomServico("");
	url = "rds.js.php?m=ajustes_financeiros&a=muda_tipo&tipo=" + campo.value;
	RDSCarregar("ajustes_financeiros_muda_tipo", url);
}

function getCategoriaFlags(valor) {
	var partes = (valor || "").split(",");
	return {
		id: partes[0] || "",
		nf: partes.length > 1 ? partes[1] : "0",
		custom: partes.length > 2 ? partes[2] : "0"
	};
}

function atualizaDescricaoCustomServico(valorCategoria) {
	var campo = document.getElementById("descricao_custom_servico");
	if (!campo) {
		return;
	}
	var flags = getCategoriaFlags(valorCategoria);
	var habilitado = flags.custom == "1" || flags.custom == 1;
	campo.disabled = !habilitado;
	if (!habilitado) {
		campo.value = "";
	}
}

function mudaCategoria(campo) {
	atualizaDescricaoCustomServico(campo.value);
	if (document.getElementById("tipo_1").checked && campo.value) {
		var nf = getCategoriaFlags(campo.value).nf;
	} else {
		var nf = "0";
	}
}

function mudaCompetencia(campo, event) {
	if (event.type == "keypress") {
		var retorno = formataCompetencia(campo, event);
	} else {
		var retorno = formataCompetencia(campo);
	}
	var competencia_inicio = document.getElementById("competencia_inicio").value;
	var competencia_termino = document.getElementById("competencia_termino").value;
	if (competencia_inicio && validaCompetencia(competencia_inicio) && competencia_termino && validaCompetencia(competencia_termino)) {
		var inicio = parseInt(competencia_inicio.substr(3, 4)) * 12 + parseInt(competencia_inicio.substr(0, 2));
		var termino = parseInt(competencia_termino.substr(3, 4)) * 12 + parseInt(competencia_termino.substr(0, 2));
		if (termino >= inicio) {
			document.getElementById("parcelas").value = termino - inicio + 1;
			document.getElementById("valor_total").disabled = false;
		} else {
			document.getElementById("parcelas").value = "";
			document.getElementById("valor_total").disabled = false;
		}
	} else {
		document.getElementById("parcelas").value = "";
	}
	mudaValorMensal(document.getElementById("valor_mensal"), null);
	return retorno;
}

function mudaCompetenciaParcelas(campo, evento) {
	if (evento.type == "keypress") {
		var retorno = formataNumero(campo, evento);
	} else {
		var retorno = formataNumero(campo);
	}
	var parcelas = parseInt(campo.value);
	var competencia_inicio = document.getElementById("competencia_inicio").value;
	if (parcelas > 0 && competencia_inicio && validaCompetencia(competencia_inicio)) {
		var inicio = parseInt(competencia_inicio.substr(3, 4)) * 12 + parseInt(competencia_inicio.substr(0, 2));
		var termino = inicio + parcelas - 1;
		var mes = termino % 12 == 0 ? 12 : termino % 12;
		var ano = parseInt((termino - mes) / 12);
		document.getElementById("competencia_termino").value = mes.lpad("0", 2) + "/" + ano;
		document.getElementById("valor_total").disabled = false;
	} else {
		document.getElementById("competencia_termino").value = "";
		document.getElementById("parcelas").value = "";
		document.getElementById("valor_total").value = "";
		document.getElementById("valor_total").disabled = true;
	}
	mudaValorMensal(document.getElementById("valor_mensal"), null);
	return retorno;
}

function mudaValorTipo(campo) {
	document.getElementById("valor_mensal").value = "";
	if (campo.value != "1") {
		document.getElementById("valor_total").value = "";
		document.getElementById("valor_total").disabled = true;
	} else {
		document.getElementById("valor_total").disabled = false;
	}
}

function mudaValorMensal(campo, evento) {
	var tipo = document.getElementById("valor_tipo").value;
	if (tipo == "1") {
		if (evento && evento.type == "keypress") {
			var retorno = formataDinheiro(campo, evento);
		} else {
			var retorno = formataDinheiro(campo);
		}
	} else if (tipo == "2") {
		if (evento && evento.type == "keypress") {
			var retorno = formataPorcentagem(campo, evento);
		} else {
			var retorno = formataPorcentagem(campo);
		}
	} else {
		if (evento && evento.type == "keypress") {
			var retorno = formataNumero(campo, evento);
		} else {
			var retorno = formataNumero(campo);
		}
	}
	if (tipo == "1") {
		var valor = parseInt(0 + limpaStringNumerico(campo.value));
		var parcelas = parseInt(0 + document.getElementById("parcelas").value);
		if (valor > 0 && parcelas > 0) {
			document.getElementById("valor_total").value = parseInt(valor * parcelas);
			formataDinheiro(document.getElementById("valor_total"));
		}
	}
	if (tipo == "1" && parcelas > 0) {
		document.getElementById("valor_total").disabled = false;
	} else {
		document.getElementById("valor_total").value = "";
		document.getElementById("valor_total").disabled = true;
	}
	return retorno;
}

function mudaValorTotal(campo, evento) {
	if (evento.type == "keypress") {
		var retorno = formataDinheiro(campo, evento);
	} else {
		var retorno = formataDinheiro(campo);
	}
	var valor = parseInt(0 + limpaStringNumerico(campo.value));
	var parcelas = parseInt(0 + document.getElementById("parcelas").value);
	if (valor > 0 && parcelas > 0) {
		document.getElementById("valor_mensal").value = parseInt(valor / parcelas);
		formataDinheiro(document.getElementById("valor_mensal"));
	} else {
		document.getElementById("valor_mensal").value = "";
	}
	return retorno;
}

<?php
	
	if (false) {
		?></script><?php
	}
	
}

?>