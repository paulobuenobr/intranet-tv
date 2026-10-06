String.prototype.trim = function() { return this.replace(/^\s+|\s+$/, ''); };

function ehNetscape() {
  return (navigator.appName.indexOf("Netscape") != -1);
}

function apresentaErros (erro_vazio,erro_invalido) {

	var total_erros = erro_vazio.length + erro_invalido.length;

	// Verifica sem tem erros
	if (total_erros > 0) {

		// Prepara o cabeçalho da mensagem de erro
		if (total_erros > 1) {
			var mensagem = "Os seguintes erros foram encontrados:\n\n";
		} else {
			var mensagem = "O seguinte erro foi encontrado:\n\n";
		}

		// Prepara a mensagem de erro dos campos de preenchimento obrigatório
		if (erro_vazio.length > 0) {

			// Prepara o cabeçalho da mensagem de erro
			if (erro_vazio.length > 1) {
				mensagem += " - Os seguintes campos são de preenchimento obrigatório:\n\n";
			} else {
				mensagem += " - O seguinte campo é de preenchimento obrigatório:\n\n";
			}

			// Lista os campos que não foram preenchidos
			for (i=0;i<erro_vazio.length;i++) {
				mensagem += "   * " + erro_vazio[i] + "\n"
			}

			// Insere um separadores
			mensagem += "\n";
		}

		// Prepara a mensagem de erro dos campos com preenchimento invalido
		if (erro_invalido.length>0) {

			// Prepara o cabeçalho da mensagem de erro
			if (erro_invalido.length>1) {
				mensagem += " - Os seguintes campos foram preenchidos incorretamente:\n\n";
			} else {
				mensagem += " - O seguinte campo foi preenchido incorretamente:\n\n";
			}

			// Lista os campos que foram preenchidos incorretamente
			for (i=0;i<erro_invalido.length;i++) {
				mensagem += "   * " + erro_invalido[i] + "\n"
			}

		}

	alert(mensagem);
	return false;
	} else {
		return true;
	}
}

function limparCampos () {
	document.getElementById('inicio').value = '';
	document.getElementById('termino').value = '';
}

function validaFormularioLogin (formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (formulario.modo.value == 'x') {
		erro_vazio[erro_vazio.length] = "Módulo";
	}
/*
	if (!formulario.login.value) {
		erro_vazio[erro_vazio.length] = "Login";
	}
*/
	if (!formulario.senha.value) {
		erro_vazio[erro_vazio.length] = "Senha";
	}

	return apresentaErros(erro_vazio,erro_invalido);
}

function validaFormularioFinanceiro (formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  if (!formulario.ativacao_data.value) {
		erro_vazio[erro_vazio.length] = "Ativação » Data";
	} else {
    if (!validaData(formulario.ativacao_data.value)) {
      erro_invalido[erro_invalido.length] = "Ativação » Data";
    }
  }

	if (formulario.vo_instalacao.value) {
	  if (!validaDinheiro(formulario.vo_instalacao.value) || parseInt(limpaStringNumerico(formulario.vo_instalacao.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » Instalação";
	}

	if (formulario.vo_rede.value) {
	  if (!validaDinheiro(formulario.vo_rede.value) || parseInt(limpaStringNumerico(formulario.vo_rede.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » Rede";
	}

	if (formulario.vo_hub.value) {
	  if (!validaDinheiro(formulario.vo_hub.value) || parseInt(limpaStringNumerico(formulario.vo_hub.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » HUB";
	}

	if (formulario.ve_cheque.value) {
	  if (!validaDinheiro(formulario.ve_cheque.value) || parseInt(limpaStringNumerico(formulario.ve_cheque.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » Cheque";
	}

	if (formulario.ve_dinheiro.value) {
	  if (!validaDinheiro(formulario.ve_dinheiro.value) || parseInt(limpaStringNumerico(formulario.ve_dinheiro.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » Dinheiro";
	}

	if (formulario.ve_fatura.value) {
	  if (!validaDinheiro(formulario.ve_fatura.value) || parseInt(limpaStringNumerico(formulario.ve_fatura.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » fatura";
	}

	if (formulario.vs_notas && formulario.vs_notas.value) {
	  if (!validaDinheiro(formulario.vs_notas.value) || parseInt(limpaStringNumerico(formulario.vs_notas.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Saída » Notas";
	}

	if (formulario.vs_combustivel && formulario.vs_combustivel.value) {
	  if (!validaDinheiro(formulario.vs_combustivel.value) || parseInt(limpaStringNumerico(formulario.vs_combustivel.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Saída » Combustível";
	}

	return apresentaErros(erro_vazio,erro_invalido);
}
/*
function validaFormularioFinanceiro (formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  if (!formulario.ativacao_data.value) {
		erro_vazio[erro_vazio.length] = "Ativação » Data";
	} else {
    if (!validaData(formulario.ativacao_data.value)) {
      erro_invalido[erro_invalido.length] = "Ativação » Data";
    }
  }

	if (formulario.ve_cheque.value) {
	  if (!validaDinheiro(formulario.ve_cheque.value) || parseInt(limpaStringNumerico(formulario.ve_cheque.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » Cheque";
	}

	if (formulario.ve_dinheiro.value) {
	  if (!validaDinheiro(formulario.ve_dinheiro.value) || parseInt(limpaStringNumerico(formulario.ve_dinheiro.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » Dinheiro";
	}

	if (formulario.ve_notas.value) {
	  if (!validaDinheiro(formulario.ve_notas.value) || parseInt(limpaStringNumerico(formulario.ve_notas.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » Notas";
	}

	if (formulario.ve_combustivel.value) {
	  if (!validaDinheiro(formulario.ve_combustivel.value) || parseInt(limpaStringNumerico(formulario.ve_combustivel.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Entrada » Combustível";
	}

	if (formulario.vs_parcelado.value) {
	  if (!validaDinheiro(formulario.vs_parcelado.value) || parseInt(limpaStringNumerico(formulario.vs_parcelado.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Saída » Parcelado";
	}

	if (formulario.vs_deposito.value) {
	  if (!validaDinheiro(formulario.vs_deposito.value) || parseInt(limpaStringNumerico(formulario.vs_deposito.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Saída » Depósito";
	}

	if (formulario.vs_notas.value) {
	  if (!validaDinheiro(formulario.vs_notas.value) || parseInt(limpaStringNumerico(formulario.vs_notas.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Saída » Notas";
	}

	if (formulario.vs_combustivel.value) {
	  if (!validaDinheiro(formulario.vs_combustivel.value) || parseInt(limpaStringNumerico(formulario.vs_combustivel.value)) == 0) erro_invalido[erro_invalido.length] = "Valores de Saída » Combustível";
	}

	if (formulario.vp_p1_valor.value) {
	  if (!validaDinheiro(formulario.vp_p1_valor.value) || parseInt(limpaStringNumerico(formulario.vp_p1_valor.value)) == 0) erro_invalido[erro_invalido.length] = "Valores das Parcelas » Parcela 1";
    if (!formulario.vp_p1_data.value) {
  		erro_vazio[erro_vazio.length] = "Datas das Parcelas » Parcela 1";
  	} else {
      if (!validaData(formulario.vp_p1_data.value)) {
        erro_invalido[erro_invalido.length] = "Datas das Parcelas » Parcela 1";
      }
    }
	}

	if (formulario.vp_p2_valor.value) {
	  if (!validaDinheiro(formulario.vp_p2_valor.value) || parseInt(limpaStringNumerico(formulario.vp_p2_valor.value)) == 0) erro_invalido[erro_invalido.length] = "Valores das Parcelas » Parcela 2";
    if (!formulario.vp_p2_data.value) {
  		erro_vazio[erro_vazio.length] = "Datas das Parcelas » Parcela 2";
  	} else {
      if (!validaData(formulario.vp_p2_data.value)) {
        erro_invalido[erro_invalido.length] = "Datas das Parcelas » Parcela 2";
      }
    }
	}

	if (formulario.vp_p3_valor.value) {
	  if (!validaDinheiro(formulario.vp_p3_valor.value) || parseInt(limpaStringNumerico(formulario.vp_p3_valor.value)) == 0) erro_invalido[erro_invalido.length] = "Valores das Parcelas » Parcela 3";
    if (!formulario.vp_p3_data.value) {
  		erro_vazio[erro_vazio.length] = "Datas das Parcelas » Parcela 3";
  	} else {
      if (!validaData(formulario.vp_p3_data.value)) {
        erro_invalido[erro_invalido.length] = "Datas das Parcelas » Parcela 3";
      }
    }
	}

	return apresentaErros(erro_vazio,erro_invalido);
}
*/

function validaFormularioTecnico (formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  if (!formulario.ativacao_data.value) {
		erro_vazio[erro_vazio.length] = "Ativação » Data";
	} else {
    if (!validaData(formulario.ativacao_data.value)) {
      erro_invalido[erro_invalido.length] = "Ativação » Data";
    }
  }

	if (formulario.vo_instalacao.value) {
	  if (!validaDinheiro(formulario.vo_instalacao.value) || parseInt(limpaStringNumerico(formulario.vo_instalacao.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » Instalação";
	}

	if (formulario.vo_rede.value) {
	  if (!validaDinheiro(formulario.vo_rede.value) || parseInt(limpaStringNumerico(formulario.vo_rede.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » Rede";
	}

	if (formulario.vo_hub.value) {
	  if (!validaDinheiro(formulario.vo_hub.value) || parseInt(limpaStringNumerico(formulario.vo_hub.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » HUB";
	}

	return apresentaErros(erro_vazio,erro_invalido);
}

function validaFormularioAdicionarContrato (formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  if (!formulario.codigo.value) {
		erro_vazio[erro_vazio.length] = "Contrato » Código";
	} else {
    if (!validaCodigo(formulario.codigo.value)) {
      erro_invalido[erro_invalido.length] = "Contrato » Código";
    }
  }

  if (!formulario.ativacao_data.value) {
		erro_vazio[erro_vazio.length] = "Ativação » Data";
	} else {
    if (!validaData(formulario.ativacao_data.value)) {
      erro_invalido[erro_invalido.length] = "Ativação » Data";
    }
  }

	if (formulario.vo_instalacao.value) {
	  if (!validaDinheiro(formulario.vo_instalacao.value) || parseInt(limpaStringNumerico(formulario.vo_instalacao.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » Instalação";
	}

	if (formulario.vo_rede.value) {
	  if (!validaDinheiro(formulario.vo_rede.value) || parseInt(limpaStringNumerico(formulario.vo_rede.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » Rede";
	}

	if (formulario.vo_hub.value) {
	  if (!validaDinheiro(formulario.vo_hub.value) || parseInt(limpaStringNumerico(formulario.vo_hub.value)) == 0) erro_invalido[erro_invalido.length] = "Valores Originais » HUB";
	}

	return apresentaErros(erro_vazio,erro_invalido);
}

function ehNumero(caracter) {
	var numerosValidos = "0123456789";
	if (numerosValidos.indexOf(caracter) == -1) {
		return false;
	} else {
		return true;
	}
}

function ehTeclaValida (tecla) {
	var LEFT = 37;
	var RIGHT = 39;
	var HOM = 36;
	var END = 35;
	var DEL = 46;
	var INS = 45;
	var CAPS = 20;
	var SHIFT = 16;
	var BACKSPACE = 8;
	var TAB = 0;
	if ((tecla == LEFT) || (tecla == RIGHT) || (tecla == HOM) || (tecla == END) || (tecla == DEL) || (tecla == INS) || (tecla == CAPS) || (tecla == SHIFT) || (tecla == BACKSPACE) || (tecla == TAB)) {
		return(true);
	} else {
  	return(false);
	}
}

function formataCodigo (campo, evento) {
	var key;
	var tecla;
	var retorno = true;
	if ( navigator.appName.indexOf("Netscape") != -1 ) {
		tecla = evento.which;
	} else {
		tecla = evento.keyCode;
	}
	key = String.fromCharCode(tecla);

	if ( ehNumero(key) || ehTeclaValida(tecla) ) {
		codigo = limpaStringNumerico(campo.value);
		tamanho = codigo.length;
		if (tamanho > 19) {
			tamanho = 19;
		}
		novo_codigo = "";
		for(i=0; i<=tamanho-1; i++)	{
			if (i == 4) {
				novo_codigo += "/";
			}
			if (i == 13) {
				novo_codigo += "-";
			}
			if (i == 15) {
				novo_codigo += "/";
			}
			novo_codigo += codigo.charAt(i);
		}
		campo.value = novo_codigo;
	} else {
		retorno = false;
	}
	return(retorno);
}

function limpaStringNumerico(string) {
	novo_string = "";
	for(var i = 0; i <= string.length - 1; i++) {
		if (ehNumero(string.charAt(i))) {
			novo_string += string.charAt(i);
		}
	}
	return(novo_string);
}

function formataNumero(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) return true;
  if (evento && (campo.readOnly || campo.value.length == campo.maxLength)) return false;
  if (ehNumero(key) || evento == null) {
    campo.value = campo.value + key;
    conteudo = limpaStringNumerico(campo.value);
    tamanho = conteudo.length;
    if (tamanho > 0) {
      conteudo_formatado = "";
      for (var i = 0; i <= tamanho - 1; i++)
      conteudo_formatado += conteudo.charAt(i);
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataDinheiro(campo, evento, simbolo, casas_decimais) {
  var key;
  var tecla;
  var conteudo;
  simbolo = (simbolo) ? simbolo : (campo.getAttribute("simbolo") ? campo.getAttribute("simbolo") : "R$");
  casas_decimais = parseInt((casas_decimais) ? casas_decimais : (campo.getAttribute("casas_decimais") ? campo.getAttribute("casas_decimais") : 2),10);
  if (evento && campo.readOnly) return false;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key) || evento == null) {
    if (evento) campo.value = campo.value + key;
    conteudo = limpaStringNumerico(campo.value);
    if (conteudo.length > 0) {
      conteudo = "" + parseInt(conteudo,10);
      while (conteudo.length < 1+casas_decimais) {
        conteudo = "0" + conteudo;
      }
      conteudo_formatado = (simbolo) ? simbolo + " " : "";
      for (var i = 0; i < conteudo.length; i++) {
        if (i != 0) {
          if (i == conteudo.length - casas_decimais) {
            conteudo_formatado += ",";
          } else {
            if ((conteudo.length - i - casas_decimais) % 3 == 0 && i < conteudo.length - casas_decimais) conteudo_formatado += ".";
          }
        }
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado.trim();
    }
  }
  return false;
}

function formataData(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key)) {
    campo.value = campo.value + key;
    conteudo = limpaStringNumerico(campo.value);
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 8) tamanho = 8;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i == 2 || i == 4) conteudo_formatado += "/";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function validaCodigo(codigo) {
	if (codigo.length == 17 || codigo.length == 20) {
    return true;
	} else {
	  return false;
	}
}

function validaData(data) {
  var filtro = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
  if (!filtro.test(data)) {
    return false;
  }
  dia = parseInt(data.substr(0, 2), 10);
  mes = parseInt(data.substr(3, 2), 10);
  ano = parseInt(data.substr(6, 4), 10);
  with (new Date(ano, mes - 1, dia)) {
    return (getDate() >= dia - 1 && getDate() <= dia + 1 && getMonth() == mes - 1 && getFullYear() == ano);
  }
}

function validaDinheiro (dinheiro) {
  var filtro_dinheiro = /^(R\$ [0-9]{1,3}(\.([0-9]{3})+)*\,([0-9]{2}))$/;
  if (filtro_dinheiro.test(dinheiro)) {
    return true;
  } else {
    return false;
  }
}
