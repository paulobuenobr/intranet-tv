<?

if ($_GET && array_key_exists("a", $_GET)) {

	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$cobrancas = controle::instanciaObjeto("cobrancas", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "buscar") {

		$listagem = $cobrancas->geraListagemClientesCobrancas($cobrancas->buscaClientesCobrancas($_GET["cliente_id"]));
		
		print "if (document.getElementById(\"cobrancas\")) document.getElementById(\"cobrancas\").innerHTML = '".addslashes($listagem)."';";

	} elseif ($_GET["a"] == "cancelar_selecionadas") {

		$_GET["selecionadas"] = explode(",", $_GET["selecionadas"]);
		
		if (count($_GET["selecionadas"]) && $seguranca->permissoes["cobrancas"]["cancelar_selecionadas"]) {
			
			for ($i = 0; $i < count($_GET["selecionadas"]); $i++) {
				$cobrancas->cancelarCobranca($_GET["selecionadas"][$i]);
			}
			
			$listagem = $cobrancas->geraListagemClientesCobrancas($cobrancas->buscaClientesCobrancas($_GET["cliente_id"]));
			
			print "if (document.getElementById(\"cobrancas\")) document.getElementById(\"cobrancas\").innerHTML = '".addslashes($listagem)."a';";
		
		}

	} elseif($_GET['a'] = "buscar_notas") {
            
            $listagem = $cobrancas->geraDetalhamentoDasNotas($cobrancas->buscaNotas($_GET["i"]));
		
            print "if (document.getElementById(\"cobrancas_notas\")) document.getElementById(\"cobrancas_notas\").innerHTML = '".addslashes($listagem)."';";
        }

} else {

	if (false) { ?><script type="text/javascript"><? }

?>

var WImprimirCobranca;
var WGerarRecibo;

function buscarCobrancas(cliente) {
	
	if (cliente) {
		url = "rds.js.php?m=cobrancas&a=buscar&cliente_id=" + cliente;
		RDSCarregar("cobrancas_buscar", url);
	}
	
}

function verCobranca(cobranca) {
	
	if (cobranca) {
		abrir("cobranca", "?m=cobrancas&a=ver_dg&i=" + cobranca, 530, 500, 1);
	}
	
}

function adicionarCobranca(cliente) {
	
	if (cliente) {
		abrir("cobranca", "?m=cobrancas&a=adicionar_cobranca_avulsa&cli=" + cliente, 530, 500, 1);
	}
	
}

function verNota(nota, largura, altura) {
	
	if (nota) {
		abrir("nota", "?m=cobrancas&a=ver_nota&i=" + nota, largura, altura, 1);
	}
	
}

function buscarNotas(cobranca) {
	if (cobranca) {
		url = "rds.js.php?m=cobrancas&a=buscar_notas&i=" + cobranca;
		RDSCarregar("cobrancas_notas", url);
	}
}

function mudaVencimento(campo, evento) {
	
	var retorno = formataData(campo, evento);

	atualizaValorJuro();

	atualizaTotal();

	return retorno;

}

function mudaTipoAjuste(item) {
	
	atualizaValorFinal(item);
	
}

function mudaValorAjuste(campo, evento, item) {
	
	var retorno = false;

	if (evento.type == "blur") {
		document.getElementById("valor_ajuste_" + item).value = valorInteiro(document.getElementById("valor_ajuste_" + item).value);
		formataDinheiro(document.getElementById("valor_ajuste_" + item));
	} else {
		retorno = formataDinheiro(campo, evento);
	}

	atualizaValorFinal(item);

	return retorno;
	
}

function mudaValorFinal(campo, evento, item) {
	
	var retorno = false;

	if (evento.type == "blur") {
		document.getElementById("valor_final_" + item).value = valorInteiro(document.getElementById("valor_final_" + item).value);
		formataDinheiro(document.getElementById("valor_final_" + item));
	} else {
		retorno = formataDinheiro(campo, evento);
	}

	atualizaValorAjuste(item);

	return retorno;

}

function atualizaValorJuro() {
	
	if (document.getElementById("nao_cobrar_juro").checked) {
		document.getElementById("valor_juro").value = "R$ 0,00";
	} else {
		document.getElementById("valor_juro").value = geraCobrancaBaixaJuros(data_vencimento_1_via, document.getElementById("data_vencimento").value, valorInteiro(document.getElementById("valor_final_original").value));
		formataDinheiro(document.getElementById("valor_juro"));
	}

}

function atualizaValorAjuste(item) {
	
	var valor_original = valorInteiro(document.getElementById("valor_original_" + item).value);
	var valor_final = valorInteiro(document.getElementById("valor_final_" + item).value);

	if (valor_final == valor_original) {
		document.getElementById("tipo_ajuste_" + item).value = "+";
		document.getElementById("valor_ajuste_" + item).value = "R$ 0,00";
	} else if (valor_final > valor_original) {
		document.getElementById("tipo_ajuste_" + item).value = "+";
		document.getElementById("valor_ajuste_" + item).value = valor_final - valor_original;
		formataDinheiro(document.getElementById("valor_ajuste_" + item));
	} else {
		document.getElementById("tipo_ajuste_" + item).value = "-";
		document.getElementById("valor_ajuste_" + item).value = valor_original - valor_final;
		formataDinheiro(document.getElementById("valor_ajuste_" + item));
	}

	atualizaTotal();

}

function atualizaValorFinal(item) {
	
	var tipo_ajuste = document.getElementById("tipo_ajuste_" + item).value;
	var valor_original = valorInteiro(document.getElementById("valor_original_" + item).value);
	var valor_ajuste = valorInteiro(document.getElementById("valor_ajuste_" + item).value);

	if (tipo_ajuste == "+") {
		document.getElementById("valor_final_" + item).value = valor_original + valor_ajuste;
		formataDinheiro(document.getElementById("valor_final_" + item));
	} else {
		if (valor_original > valor_ajuste) {
			document.getElementById("valor_final_" + item).value = valor_original - valor_ajuste;
			formataDinheiro(document.getElementById("valor_final_" + item));
		} else {
			document.getElementById("valor_ajuste_" + item).value = document.getElementById("valor_original_" + item).value;
			document.getElementById("valor_final_" + item).value = "R$ 0,00";
		}
	}

	atualizaTotal();

}

function atualizaTotal() {
	
	var i = 0;
	var total = 0;
	var campo;

	while (campo = document.getElementById("valor_final_" + i)) {
		total += valorInteiro(campo.value);
		i++;
	}

	document.getElementById("valor_final_original").value = total;
	formataDinheiro(document.getElementById("valor_final_original"));

	atualizaValorJuro();

	document.getElementById("valor_final_total").value = total + valorInteiro(document.getElementById("valor_juro").value);
	formataDinheiro(document.getElementById("valor_final_total"));

}

function atualizaCobrancaBaixa(campo, evento) {
		
	var formulario = campo.form;
	var retorno = false;

	if (evento) {
		if (campo.name.indexOf("valor") != -1) retorno = formataDinheiro(campo, evento);
		if (campo.name.indexOf("data") != -1) retorno = formataData(campo, evento);
	}

	if (!campo.form.tipo.value) {
		
		formulario.data_pagamento.readOnly = true;
		formulario.valor_acrescimo.readOnly = true;
		formulario.valor_decrescimo.readOnly = true;
		formulario.comentario.readOnly = true;

		formulario.valor_juro.value = "";
		formulario.valor_desconto.value = "";
		formulario.valor_acrescimo.value = "";
		formulario.valor_decrescimo.value = "";
		formulario.valor_total.value = "";
		
	} else {
		
		if (formulario.tipo.value == 6 || formulario.tipo.value == 7) {
			formulario.data_pagamento.readOnly = true;
			formulario.data_pagamento.value = '';
		} else {
			formulario.data_pagamento.readOnly = false;
			if (!formulario.data_pagamento.value) {
				formulario.data_pagamento.value = formulario.data_vencimento.value;
			}
		}
		
		formulario.comentario.readOnly = false;

		var valor_original = valorInteiro(formulario.valor_original.value);
		var valor_juro = geraCobrancaBaixaJuros(formulario.data_vencimento.value, formulario.data_pagamento.value, valor_original);

		var valor_desconto = 0;
		var valor_acrescimo = 0;
		var valor_decrescimo = 0;
		var valor_total = 0;
		var valor_bonificacao = valorInteiro(formulario.valor_bonificacao.value);

		if (formulario.tipo.value == 6 || formulario.tipo.value == 7) {
			
			// Baixa Manual (Indevida) ou Baixa Manual (Negociada)
			
			formulario.valor_acrescimo.readOnly = true;
			formulario.valor_decrescimo.readOnly = true;
			valor_decrescimo = valor_original + valor_juro;
			
		} else {
			
			// Baixa Manual (Paga)
			
			formulario.valor_acrescimo.readOnly = false;
			formulario.valor_decrescimo.readOnly = false;
			formulario.valor_total.readOnly = false;

			if (!valor_juro) {
				valor_desconto = valor_bonificacao;
			}
			valor_acrescimo = valorInteiro(formulario.valor_acrescimo.value);
			valor_decrescimo = valorInteiro(formulario.valor_decrescimo.value);

			if (campo.name == "valor_total") {
				valor_total = valorInteiro(formulario.valor_total.value);
				valor_acrescimo = 0;
				valor_decrescimo = 0;
				diferenca = valor_total - (valor_original + valor_juro - valor_desconto + valor_acrescimo - valor_decrescimo);
				if (diferenca > 0) {
					valor_acrescimo += diferenca;
				} else {
					valor_decrescimo -= diferenca;
				}
			} else {
				diferenca = valor_original + valor_juro - valor_desconto + valor_acrescimo - valor_decrescimo;
				if (diferenca < 0) {
					if (campo.name == "valor_acrescimo") {
						valor_acrescimo += diferenca;
					} else if (campo.name == "valor_decrescimo") {
						valor_decrescimo += diferenca;
					}
				}
				valor_total = valor_original + valor_juro - valor_desconto + valor_acrescimo - valor_decrescimo;
			}
		}

		formulario.valor_juro.value = valor_juro;
		formulario.valor_desconto.value = valor_desconto;
		formulario.valor_acrescimo.value = valor_acrescimo;
		formulario.valor_decrescimo.value = valor_decrescimo;
		formulario.valor_total.value = valor_total;

		formataDinheiro(formulario.valor_juro);
		formataDinheiro(formulario.valor_desconto);
		formataDinheiro(formulario.valor_acrescimo);
		formataDinheiro(formulario.valor_decrescimo);
		formataDinheiro(formulario.valor_total);
		
	}
	
	return retorno;

}

function geraCobrancaBaixaJuros(data_vencimento, data_processamento, valor) {
	
	var dias = subtraiDatas(data_processamento, data_vencimento);
	var juros = ((dias > 0) ? parseInt((valor * 0.02) + (valor * 0.01 * dias / 30), 10) : 0);

	return juros;

}

function validaGeraSegundaVia(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.formato.value) {
		erro_vazio[erro_vazio.length] = "Formato";
	}

	maximo_vencimento = formulario.data_vencimento_maximo.value.substr(6, 4) + formulario.data_vencimento_maximo.value.substr(3, 2) + formulario.data_vencimento_maximo.value.substr(0, 2); // dd/mm/yyyy
	vencimento = formulario.data_vencimento.value.substr(6, 4) + formulario.data_vencimento.value.substr(3, 2) + formulario.data_vencimento.value.substr(0, 2); // dd/mm/yyyy

	if (!formulario.data_vencimento.value) {
		erro_vazio[erro_vazio.length] = "Vencimento";
	} else if (!validaData(formulario.data_vencimento.value)) {
		erro_invalido[erro_invalido.length] = "Vencimento";
	} else if (formulario.data_vencimento_maximo.value && vencimento > maximo_vencimento) {
		erro_invalido[erro_invalido.length] = "Vencimento (Maior que o máximo permitido - " + formulario.data_vencimento_maximo.value + ")";
	}

	var valor_total = valorInteiro(formulario.valor_final_original.value);

	if (!valor_total) {
		erro_vazio[erro_vazio.length] = "Total Itens não pode ser R$ 0,00";
	}

	return apresentaErros(erro_vazio, erro_invalido);

}

function validaAdicionarCobranca(formulario) {
		
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.formato.value) {
		erro_vazio[erro_vazio.length] = "Formato";
	}

	if (!formulario.vencimento_inicio.value) {
		erro_vazio[erro_vazio.length] = "Vencimento » Início";
	} else if (!validaCompetencia(formulario.vencimento_inicio.value)) {
		erro_invalido[erro_invalido.length] = "Vencimento » Início";
	}

	if (!formulario.vencimento_termino.value) {
		erro_vazio[erro_vazio.length] = "Vencimento » Término";
	} else  if (!validaCompetencia(formulario.vencimento_termino.value)) {
		erro_invalido[erro_invalido.length] = "Vencimento » Término";
	}

	if (!formulario.data_vencimento.value) {
		erro_vazio[erro_vazio.length] = "Data Vencimento";
	} else if (!validaData(formulario.data_vencimento.value)) {
		erro_invalido[erro_invalido.length] = "Data Vencimento";
	}

	return apresentaErros(erro_vazio, erro_invalido);

}

function imprimirBoleto(cobranca_id) {
	
	if (cobranca_id) {
		if (WImprimirCobranca != null) WImprimirCobranca.close();
		parametros = "top,left,width=" + ((screen.availWidth > 700) ? 700 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 95) + "statusbar=no,toolbar=yes,scrollbars=yes,resizable=1";
		WImprimirCobranca = window.open("?m=cobrancas&a=imprimir&i=" + cobranca_id, "imprimir_cobranca", parametros);
	}

}

function gerarRecibo(cobranca_id) {
	
	if (cobranca_id) {
		if (WGerarRecibo != null) WGerarRecibo.close();
		parametros = "top,left,width=" + ((screen.availWidth > 700) ? 700 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 95) + "statusbar=no,toolbar=yes,scrollbars=yes,resizable=1";
		WGerarRecibo = window.open("?m=cobrancas&a=gerar_recibo&i=" + cobranca_id, "gerar_recibo", parametros);
	}
	
}

function validaCobrancasEnviar(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.emails.value) {
		erro_vazio[erro_vazio.length] = "Email(s)";
	} else if (!validaEmails(formulario.emails.value)) {
		erro_invalido[erro_invalido.length] = "Email(s)";
	}

	return apresentaErros(erro_vazio,erro_invalido);

}

function validaCobrancaBaixa(formulario) {

	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}

	if (formulario.tipo.value != 6 && formulario.tipo.value != 7) {
		if (!formulario.data_pagamento.value) {
			erro_vazio[erro_vazio.length] = "Datas » Pagamento";
		} else if (!validaData(formulario.data_pagamento.value)) {
			erro_invalido[erro_invalido.length] = "Datas » Pagamento";
		}
	}

	if (!formulario.valor_juro.value) {
		erro_vazio[erro_vazio.length] = "Valores » Juro";
	} else if (!validaDinheiro(formulario.valor_juro.value)) {
		erro_invalido[erro_invalido.length] = "Valores » Juro";
	}

	if (!formulario.valor_desconto.value) {
		erro_vazio[erro_vazio.length] = "Valores » Desconto";
	} else if (!validaDinheiro(formulario.valor_desconto.value)) {
		erro_invalido[erro_invalido.length] = "Valores » Desconto";
	}

	if (!formulario.valor_acrescimo.value) {
		erro_vazio[erro_vazio.length] = "Valores » Acréscimo";
	} else if (!validaDinheiro(formulario.valor_acrescimo.value)) {
		erro_invalido[erro_invalido.length] = "Valores » Acréscimo";
	}

	if (!formulario.valor_decrescimo.value) {
		erro_vazio[erro_vazio.length] = "Valores » Decréscimo";
	} else if (!validaDinheiro(formulario.valor_decrescimo.value)) {
		erro_invalido[erro_invalido.length] = "Valores » Decréscimo";
	}

	if (!formulario.valor_total.value) {
		erro_vazio[erro_vazio.length] = "Valores » Total";
	} else if (!validaDinheiro(formulario.valor_total.value)) {
		erro_invalido[erro_invalido.length] = "Valores » Total";
	}

	if (apresentaErros(erro_vazio,erro_invalido)) {
		return confirm("Você deseja realmente confirmar a baixa desta cobrança?");
	} else {
		return false;
	}

}

function validaCobrancaContato (formulario) {

	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}

	return apresentaErros(erro_vazio, erro_invalido);

}

function cancelarSelecionadas() {
	
	var i = 0;
	var selecionadas = new Array();
	
	while (document.getElementById('selecao_cobranca_' + i)) {
		if (document.getElementById('selecao_cobranca_' + i).checked) {
			selecionadas[selecionadas.length] = document.getElementById('selecao_cobranca_' + i).value;
		}
		i++;
	}
	
	var quantidade = selecionadas.length;
	
	if (quantidade > 0) {
		if (quantidade == 1) {
			var mensagem = "Você deseja realmente cancelar a cobrança selecionada?";
		} else {
			var mensagem = "Você deseja realmente cancelar as " + quantidade + " cobranças selecionadas?";
		}
		if (confirm(mensagem)) {
			url = "rds.js.php?m=cobrancas&a=cancelar_selecionadas&cliente_id=" + cliente_id + "&selecionadas=" + selecionadas.join(",");
			RDSCarregar("cobrancas_cancelar_selecionadas", url);
		}
	}

}

function validaBaixarEmLoteBuscar(formulario) {

	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.origem.value) {
		erro_vazio[erro_vazio.length] = "Origem";
	}
	
	if (!formulario.vencimento_inicial.value) {
		erro_vazio[erro_vazio.length] = "Vencimento Inicial";
	} else if (!validaData(formulario.vencimento_inicial.value)) {
		erro_invalido[erro_invalido.length] = "Vencimento Inicial";
	}
	
	if (!formulario.vencimento_final.value) {
		erro_vazio[erro_vazio.length] = "Vencimento Final";
	} else if (!validaData(formulario.vencimento_final.value)) {
		erro_invalido[erro_invalido.length] = "Vencimento Final";
	}
	
	return apresentaErros(erro_vazio, erro_invalido);

}

function atualizaSelecao(campo) {
	
	if ($(campo).is(":checked")) {
		$('input[name="cobranca[]"]').attr("checked", "checked").change();
	} else {
		$('input[name="cobranca[]"]').attr("checked", "").change();
	}
}

function atualizaSelecaoBaixarEmLote(campo) {
	
	if ($(campo).is(":checked")) {
		$('#' + $(campo).val()).css('background-color', '#99FF99');
		$('input[valorOriginal="' + $(campo).val() + '"]').css('background-color', '#99FF99');
		$('input[valorFinal="' + $(campo).val() + '"]').attr( "disabled", "").css('background-color', '#FFFFFF');
	} else {
		$('#' + $(campo).val()).css('background-color', '#E8E8E8');
		$('input[valorOriginal="' + $(campo).val() + '"]').css('background-color', '#E8E8E8');
		$('input[valorFinal="' + $(campo).val() + '"]').attr( "disabled", "disabled").css('background-color', '#E8E8E8');
	}
	
	atualizaBaixaEmLoteTotal();
	
}

function atualizaBaixaEmLoteTotal() {

	var totalValorOriginal = 0;
	var totalValorFinal = 0;
	var ordem = 1;
	
	$('spam[ordem]').html('');
	
	$('input[name="cobranca[]"]:checked').each(function () {
		totalValorOriginal += valorInteiro($('input[valorOriginal="' + $(this).val() + '"]').val());
		totalValorFinal += valorInteiro($('input[valorFinal="' + $(this).val() + '"]').val());
		$('spam[ordem="' + $(this).val() + '"]').html(ordem);
		ordem++;
	});
	
	$('#totalValorOriginal').val(formataValorDinheiro(totalValorOriginal.toString()));
	$('#totalValorFinal').val(formataValorDinheiro(totalValorFinal.toString()));
	
}

function validaRegistrarNota(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	if (!formulario.numero.value || parseInt(limpaStringNumerico(formulario.numero.value), 10) == 0) {
		erro_vazio[erro_vazio.length] = "Número";
	}
	if (!formulario.emissao.value) {
		erro_vazio[erro_vazio.length] = "Emissão";
	} else if (!validaData(formulario.emissao.value)) {
		erro_invalido[erro_invalido.length] = "Emissão";
	}
	if (!formulario.valor.value || parseInt(limpaStringNumerico(formulario.valor.value), 10) == 0) {
		erro_vazio[erro_vazio.length] = "Valor";
	}
	
	return apresentaErros(erro_vazio, erro_invalido);

}

<?

	if (false) { ?></script><? }

}

?>
