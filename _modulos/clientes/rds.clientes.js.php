<?php

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes = controle::instanciaObjeto("clientes", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "busca_inscricao_estadual") {
		
		if (($inscricao_estadual = $clientes->buscaInscricaoEstadual($_GET["cpf_cnpj"], $_GET["cliente_tipo_id"])) != false) {
			print "document.getElementById('inscricao_estadual').value = '$inscricao_estadual';";
			print "document.getElementById('isento_inscricao_estadual').checked = false;";
		} else {
			print "document.getElementById('inscricao_estadual').value = '';";
			print "document.getElementById('isento_inscricao_estadual').checked = true;";
		}
		print "document.getElementById('busca_incricao_estadual').disabled = false;";
		
	}
	
} else {
	
	if (false) { ?><script type="text/javascript"><?php }
	
?>

function validaClienteDadosCadastraisProspects(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.duplicado_bloqueio.value == 1) {
		alert('Não é permitido o cadastro duplicado deste cliente!');
		return false;
	}
	if (formulario.duplicado_cpf_cnpj.value == 1 && formulario.duplicar_cpf_cnpj.value == 0) {
		alert('Você não tem permissão para cadastrar um cliente com CPF ou CNPJ duplicado.');
		return false;
	}
	if (formulario.duplicado_endereco.value == 1 && formulario.duplicar_endereco.value == 0) {
		alert('Você não tem permissão para cadastrar um cliente com endereço duplicado.');
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
	if (formulario.condominio.value) {
		if (!formulario.bloco.value) {
			erro_vazio[erro_vazio.length] = "Endereço » Bloco";
		}
	} else {
		if (!formulario.cep.value) {
			erro_vazio[erro_vazio.length] = "Endereço » CEP";
		}
	}
	if (!formulario.complemento_tipo.value || (formulario.complemento_tipo.value != 'o' && !formulario.complemento_valor.value)) {
		erro_vazio[erro_vazio.length] = "Endereço » Complemento";
	} else if (formulario.complemento_valor.value && !/^\s*[a-zA-Z0-9]+\s*$/.test(formulario.complemento_valor.value)) {
		erro_invalido[erro_invalido.length] = "Endereço » Complemento (Utilize o campo Ponto de Referência para informações complementares)";
	}
	if (formulario.cobranca_formato.value == 3) {
		if (!formulario.cobranca_debito_agencia.value) {
			erro_vazio[erro_vazio.length] = "Dados de Cobrança » Código da Agência";
		} else {
			if (!validaAgenciaBB(formulario.cobranca_debito_agencia.value)) {
				erro_invalido[erro_invalido.length] = "Dados de Cobrança » Código da Agência";
			}
		}
		if (!formulario.cobranca_debito_conta.value) {
			erro_vazio[erro_vazio.length] = "Dados de Cobrança » Número da Conta";
		} else {
			if (!validaContaBB(formulario.cobranca_debito_conta.value)) {
				erro_invalido[erro_invalido.length] = "Dados de Cobrança » Número da Conta";
			}
		}
	}
	if (formulario.retorno_comentario && formulario.retorno_data && (formulario.retorno_data.value || formulario.retorno_comentario.value)) {
		if (!formulario.retorno_data.value) {
			erro_vazio[erro_vazio.length] = "Retorno » Data";
		} else {
			if (!validaData(formulario.retorno_data.value)) {
				erro_invalido[erro_invalido.length] = "Retorno » Data";
			}
		}
		if (!formulario.retorno_comentario.value) erro_vazio[erro_vazio.length] = "Retorno » Comentário";
	}
	if (telefone_quantidade == 0 && email_quantidade == 0) {
		erro_vazio[erro_vazio.length] = "Contato(s) » Pelo menos um (Telefone ou E-mail)";
	}
	//if (!formulario.situacao.value) {
	//	erro_vazio[erro_vazio.length] = "Situação";
	//}
	if (formulario.responsavel && !formulario.responsavel.disabled && !formulario.responsavel.value) {
		erro_vazio[erro_vazio.length] = "Responsável";
	}
	//if (!formulario.indicacao.value) {
	//	erro_vazio[erro_vazio.length] = "Indicação";
	//}
	//if (!formulario.operadora_origem.value) {
	//	erro_vazio[erro_vazio.length] = "Operadora Origem";
	//}
	return apresentaErros(erro_vazio,erro_invalido);
}

function buscarInscricaoEstadual(cpf_cnpj, cliente_tipo_id) {
	if (cpf_cnpj) {
		document.getElementById('busca_incricao_estadual').disabled = true;
		url = "rds.js.php?m=clientes&a=busca_inscricao_estadual&cpf_cnpj=" + cpf_cnpj + "&cliente_tipo_id=" + cliente_tipo_id;
		RDSCarregar("clientes_buscar_condominio_campo", url);
	}
}

function mudaSituacao(campo) {
	if (campo.value == 7) {
		campo.form.responsavel.value = "";
		campo.form.responsavel.disabled = true;
		campo.form.retorno_data.value = "";
		campo.form.retorno_data.disabled = true;
		campo.form.retorno_periodo.value = "";
		campo.form.retorno_periodo.disabled = true;
		campo.form.retorno_comentario.value = "";
		campo.form.retorno_comentario.disabled = true;
	} else {
		campo.form.responsavel.disabled = false;
		campo.form.retorno_data.disabled = false;
		campo.form.retorno_periodo.disabled = false;
		campo.form.retorno_comentario.disabled = false;
	}
}

var relatorio_financeiro;

function verRelatorioFinanceiro(cliente_id) {
	if (cliente_id) {
		if (relatorio_financeiro != null) relatorio_financeiro.close();
		parametros = "top,left,width=" + ((screen.availWidth > 960) ? 960 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 95) + "statusbar=no,toolbar=yes,scrollbars=yes,resizable=1";
		relatorio_financeiro = window.open("?m=clientes&a=relatorio_financeiro&i=" + cliente_id, "relatorio_financeiro", parametros);
	}
}


<?php
	
	if (false) { ?></script><?php }
	
}

?>
