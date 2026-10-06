String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/, "");
};

String.prototype.lpad = function(padString, length) {
    var str = this;
    while (str.length < length)
        str = padString + str;
    return str;
}

Number.prototype.lpad = function(padString, length) {
    var str = this.toString();
    while (str.length < length)
        str = padString + str;
    return str;
}

function ehNumero(caracter) {
  var numerosValidos = "0123456789";
  if (numerosValidos.indexOf(caracter) == -1) {
    return false;
  } else {
    return true;
  }
}

function ehAba(caracter) {
  var abasValidas = "cfmtah";
  if (abasValidas.indexOf(caracter) == -1) {
    return false;
  } else {
    return true;
  }
}

function ehHexa(caracter) {
  var teclasValidas = "0123456789abcdefABCDEF";
  if (teclasValidas.indexOf(caracter) != -1 || ehNumero(caracter)) {
    return true;
  } else {
    return false;
  }
}

function ehTeclaValida(tecla) {
  var ENTER = 13;
  var BACKSPACE = 8;
  var DELETE = 46;
  if ((tecla == ENTER) || (tecla == BACKSPACE) || (tecla == DELETE) || (tecla == 0)) {
    return true;
  } else {
    return false;
  }
}

function ehTeclaValida2(tecla) {
  var ENTER = 13;
  var BACKSPACE = 8;
  var DELETE = 46;
  var BARRA = 47;
  var TRACO = 45;
  if ((tecla == ENTER) || (tecla == BACKSPACE) || (tecla == DELETE) || (tecla == 0) || (tecla == TRACO) || (tecla == BARRA)) {
    return true;
  } else {
    return false;
  }
}

function limpaStringNumerico(valor) {
  return valor.replace(/[^0-9\-]/g, "");
}

function valorInteiro(valor) {
  valor = limpaStringNumerico(valor);
  return !valor || isNaN(valor) ? 0 : parseInt(valor, 10);
}

function limpaPonto(string, caracter) {
  novo_string = "";
  for (var i = 0; i <= string.length - 1; i++) {
    if (string.charAt(i) != caracter) {
      novo_string += string.charAt(i);
    }
  }
  return novo_string;
}

function formataCEP(campo, evento, sufixo, formatonly) {
  var key;
  var tecla;
  var conteudo;
  if (!sufixo) sufixo = '';
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
  	if (tecla != 0 && tecla != 8 && tecla != 46 && typeof(buscaEnderecoCEP) !== 'undefined') {
  		buscaEnderecoCEP(campo.value);
  	}
    return true;
  } else if (ehNumero(key)) {
    campo.value = campo.value + key;
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 8) tamanho = 8;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i == 5) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
    document.getElementById('cobranca_cep').value = document.getElementById('cep').value;
  }
  if (!formatonly && typeof(buscaEnderecoCEP) !== 'undefined') {
	  buscaEnderecoCEP(campo.value, sufixo);
  }
  return false;
}

function formataTelefone(campo, evento) {
  if (document.getElementById("alertas") != null)
  mudaClienteTelefone(campo);
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 11) tamanho = 11;
      conteudo_formatado = "(";
      (tamanho > 9) ? hifen = 6 : hifen = 5;
      hifen = tamanho > 6 ? tamanho - 4 : -1;
      for (var i = 0; i < tamanho; i++) {
        if (i == 2) conteudo_formatado += ") ";
        if (i == hifen) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataTelefoneSemDDD(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (ehNetscape()) {
    tecla = evento.which;
    tamanhoBrowser = 10;
    tamanhoBrowserHifen = 8;
  } else {
    tecla = evento.keyCode;
    tamanhoBrowser = 10;
    tamanhoBrowserHifen = 8;
  }
  key = String.fromCharCode(tecla);
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key)) {
    campo.value = campo.value + key;
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > tamanhoBrowser) tamanho = tamanhoBrowser;
      hifen = (tamanho > tamanhoBrowserHifen) ? 5 : 4;
      conteudo_formatado = '';
      for (var i = 0; i < tamanho; i++) {
        if (i == hifen) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
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

function formataDataHora(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 12) tamanho = 12;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i == 2 || i == 4) conteudo_formatado += "/";
        if (i == 8) conteudo_formatado += " (";
        if (i == 10) conteudo_formatado += ":";
        conteudo_formatado += conteudo.charAt(i);
        if (i == 11) conteudo_formatado += ")";
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataHora(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 4) tamanho = 4;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i == 2) conteudo_formatado += ":";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataCompetencia(campo, evento) {
  if (document.getElementById("parametros") != null && campo.value.length == 7)
  mudaCobrancaLoteFormatoCompetencia(campo.form);
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 6) tamanho = 6;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i == 2) conteudo_formatado += "/";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataCPFCNPJ(campo, evento) {
  if (campo.form.tipo.value == 2) {
    if (document.getElementById("alertas") != null && (campo.value.length == 18 || campo.value.length == 0)) {
      mudaClienteCPFCNPJ(campo);
    }
    return formataCNPJ(campo,evento);
  } else {
    if (document.getElementById("alertas") != null && (campo.value.length == 14 || campo.value.length == 0)) {
      mudaClienteCPFCNPJ(campo);
    }
    return formataCPF(campo,evento);
  }
}

/**
 * Remove pontuacao e normaliza CNPJ alfanumerico (Receita Federal).
 * Posicoes 1-12: 0-9 e A-Z | Posicoes 13-14 (DV): apenas 0-9
 */
function limpaCNPJ(valor) {
  var i;
  var caractere;
  var base = "";
  var dv = "";

  valor = (valor || "").replace(/[^0-9A-Za-z]/g, "").toUpperCase();

  for (i = 0; i < valor.length && base.length < 12; i++) {
    caractere = valor.charAt(i);
    if (/[0-9A-Z]/.test(caractere)) {
      base += caractere;
    }
  }

  for (; i < valor.length && dv.length < 2; i++) {
    caractere = valor.charAt(i);
    if (/[0-9]/.test(caractere)) {
      dv += caractere;
    }
  }

  return base + dv;
}

function valorCNPJMod11(caractere) {
  return caractere.charCodeAt(0) - 48;
}

function aplicaMascaraCNPJ(conteudo) {
  var i;
  var tamanho;
  var conteudo_formatado = "";

  conteudo = limpaCNPJ(conteudo);
  tamanho = conteudo.length;

  if (tamanho > 14) {
    tamanho = 14;
    conteudo = conteudo.substring(0, 14);
  }

  for (i = 0; i < tamanho; i++) {
    if (i == 2 || i == 5) conteudo_formatado += ".";
    if (i == 8) conteudo_formatado += "/";
    if (i == 12) conteudo_formatado += "-";
    conteudo_formatado += conteudo.charAt(i);
  }

  return conteudo_formatado;
}

function formataCPF(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 11) tamanho = 11;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i == 3 || i == 6) conteudo_formatado += ".";
        if (i == 9) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataCNPJ(campo, evento) {
  var key;
  var tecla;
  var conteudoAtual;
  var posicao;

  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }

  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    if (ehTeclaValida(tecla)) {
      setTimeout(function() {
        campo.value = aplicaMascaraCNPJ(campo.value);
      }, 0);
    }
    return true;
  }

  conteudoAtual = limpaCNPJ(campo.value);
  posicao = conteudoAtual.length;

  if (posicao >= 14) {
    return false;
  }

  if (posicao < 12) {
    if (!ehAlfaNumero(key)) {
      return false;
    }
    key = key.toUpperCase();
  } else if (!ehNumero(key)) {
    return false;
  }

  campo.value = aplicaMascaraCNPJ(conteudoAtual + key);
  return false;
}

function formataInscricaoEstadual(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida2(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key)) {
    campo.value = campo.value + key;
    conteudo = campo.value;
//    conteudo = campo.value.replace(/[^0-9]/g, "");
//    tamanho = conteudo.length;
//    if (tamanho > 0) {
//      if (tamanho > 9) tamanho = 9;
//      conteudo_formatado = "";
//      for (var i = 0; i < tamanho; i++) {
//        if (i == 3 || i == 6) conteudo_formatado += ".";
//        conteudo_formatado += conteudo.charAt(i);
//      }
//      campo.value = conteudo_formatado;
//    }
  }
  return false;
}

function formataDesconto(campo, evento, tipo) {
  if (tipo == 1) {
    return formataDinheiro(campo, evento);
  } else {
    return formataPorcentagem(campo, evento);
  }
}

function formataComissao(campo, evento, tipo) {
  if (tipo == 1) {
    return formataDinheiro(campo, evento);
  } else {
    return formataPorcentagem(campo, evento);
  }
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
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

function formataValorDinheiro(valor) {
  conteudo = valor.replace(/[^0-9]/g, "");
  tamanho = conteudo.length;
  if (tamanho > 0) {
    conteudo = "" + parseInt(conteudo, 10);
    while (tamanho < 3) {
      conteudo = "0" + conteudo;
      tamanho++;
    }
    conteudo_formatado = "R$ ";
    for (var i = 0; i < tamanho; i++) {
      if (i != 0) {
        if (i == tamanho - 2) {
          conteudo_formatado += ",";
        } else {
          if ((tamanho - i - 2) % 3 == 0) conteudo_formatado += ".";
        }
      }
      conteudo_formatado += conteudo.charAt(i);
    }
    return conteudo_formatado;
  }
}

function formataPorcentagem(campo, evento) {
  var key = "";
  var tecla;
  var conteudo;
  if (evento && campo.readOnly) return false;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key) || evento == null) {
    campo.value = campo.value + key;
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      conteudo_formatado = "";
      for (var i = 0; i <= tamanho - 1; i++) {
        conteudo_formatado += conteudo.charAt(i);
      }
      conteudo_formatado += "%";
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataDuracao(campo, evento) {
  var key = "";
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 2) tamanho = 2;
      conteudo_formatado = "";
      for (var i = 0; i <= tamanho - 1; i++) {
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataNumero(campo, evento) {
  var key = "";
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
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

function formataID(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla).toLowerCase();
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) return true;
  if (evento && (campo.readOnly || campo.value.length == campo.maxLength)) return false;
  if (ehNumero(key) || (ehAba(key) && campo.value.length == 0) || evento == null) {
    campo.value = campo.value + key;
    if (ehAba(campo.value.charAt(0))) {
      aba = campo.value.charAt(0);
    } else {
      aba = '';
    }
    conteudo = campo.value.replace(/[^0-9]/g, "");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      conteudo_formatado = "";
      for (var i = 0; i <= tamanho - 1; i++) {
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = aba + conteudo_formatado;
    }
  }
  return false;
}

function formataNumeroDecimal(campo, evento, separador, casas_decimais) {
  var key;
  var tecla;
  var conteudo;
  separador = (separador) ? separador : (campo.getAttribute("separador") ? campo.getAttribute("separador") : ",");
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    if (conteudo.length > 0) {
      conteudo = "" + parseInt(conteudo,10);
      while (conteudo.length < 1+casas_decimais) conteudo = "0" + conteudo;
      conteudo_formatado = "";
      for (var i = 0; i < conteudo.length; i++) {
        if (i != 0 && i == conteudo.length - casas_decimais) conteudo_formatado += separador;
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado.trim();
    }
  }
  return false;
}

function formataMAC(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehHexa(key)) {
    campo.value = campo.value + key.toLowerCase();
    conteudo = limpaPonto(campo.value,":");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 12) tamanho = 12;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if (i ==  2) conteudo_formatado += ":";
        if (i ==  4) conteudo_formatado += ":";
        if (i ==  6) conteudo_formatado 
        += ":";
        if (i ==  8) conteudo_formatado += ":";
        if (i == 10) conteudo_formatado += ":";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataNumeroIP(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if ((key == "." || key == ".") && campo.value.length > 0) {
    campo.form[(getIndex(campo) + 1) % campo.form.length].focus();
    return false;
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key)) {
    campo.value = campo.value + key;
    conteudo = limpaPonto(campo.value, ".");
    tamanho = conteudo.length;
    if (tamanho > 0) {
      if (tamanho > 3) tamanho = 3;
      conteudo_formatado = "";
      for (var i = 0; i < tamanho; i++) {
        if ((ehNumero(conteudo.charAt(i))) && (conteudo_formatado + conteudo.charAt(i) <= 255))
        conteudo_formatado += conteudo.charAt(i);
      }
      if (conteudo_formatado == "08") campo.value = "8"; else
      if (conteudo_formatado == "09") campo.value = "9"; else
      campo.value = parseInt(conteudo_formatado);
    }
    if (campo.value.length == 3) {
      campo.form[(getIndex(campo) + 1) % campo.form.length].focus();
    }
  }
  return false;
}

function formataNumeroReal(campo, evento) {
  var tecla;
  var conteudo;
  conteudo = campo.value;
  if (ehNetscape()) {
    tecla = evento.which;
  } else {
    if (evento.keyCode == 44) evento.keyCode = 46;
    tecla = evento.keyCode;
  }
  key = String.fromCharCode(tecla);
  if (ehNumero(key) || ehTeclaValida(tecla) || (key == "." && conteudo.indexOf(".") == -1))
  return true; else
  return false;
}

function formataAgencia(campo, evento, formato) {
  if (formato.substr(2) == 3 || formato.substr(2) == 20 || formato.substr(2) == 25) { // BB
    return formataAgenciaBB(campo, evento);
  } else if (formato.substr(2) == 4 || formato.substr(2) == 18 || formato.substr(2) == 23) { // CEF
    return formataAgenciaCEF(campo, evento);
  } else if (formato.substr(2) == 19 || formato.substr(2) == 24) { // BESC
    return formataAgenciaBESC(campo, evento);
  }
}

function formataConta(campo, evento, formato) {
  if (formato.substr(2) == 3 || formato.substr(2) == 20 || formato.substr(2) == 25) { // BB
    return formataContaBB(campo, evento);
  } else if (formato.substr(2) == 4 || formato.substr(2) == 18 || formato.substr(2) == 23) { // CEF
    return formataContaCEF(campo, evento);
  } else if (formato.substr(2) == 19 || formato.substr(2) == 24) { // BESC
    return formataContaBESC(campo, evento);
  }
}

function formataOperacao(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (key.replace(/[^0-9]/g, "")) {
    conteudo = campo.value.replace(/[^0-9]/g, "") + key;
    conteudo = parseInt(conteudo, 10) + "";
    tamanho = conteudo.length;
    if (tamanho > 0) {
      conteudo_formatado = "";
      if (tamanho > 3) tamanho = 3;
      for (var i = 0; i < tamanho; i++) {
        conteudo_formatado += conteudo.charAt(i);
      }
      while (conteudo_formatado.length < 3) {
        conteudo_formatado = "0" + conteudo_formatado;
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataAgenciaBB(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "") + key;
    conteudo = parseInt(conteudo, 10) + "";
    if (conteudo.length > 0) {
      while (conteudo.length < 5) {
        conteudo = "0" + conteudo;
      }
      conteudo_formatado = "";
      for (var i = 0; i < 5; i++) {
        if (i == 4) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataAgenciaCEF(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (key.replace(/[^0-9]/g, "")) {
    conteudo = campo.value.replace(/[^0-9]/g, "") + key;
    conteudo = parseInt(conteudo, 10) + "";
    tamanho = conteudo.length;
    if (tamanho > 0) {
      conteudo_formatado = "";
      if (tamanho > 4) tamanho = 4;
      for (var i = 0; i < tamanho; i++) {
        conteudo_formatado += conteudo.charAt(i);
      }
      while (conteudo_formatado.length < 4) {
        conteudo_formatado = "0" + conteudo_formatado;
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataAgenciaBESC(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "") + key;
    conteudo = parseInt(conteudo, 10) + "";
    if (conteudo.length > 0) {
      while (conteudo.length < 5) {
        conteudo = "0" + conteudo;
      }
      conteudo_formatado = "";
      for (var i = 0; i < 5; i++) {
        if (i == 4) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataContaCEF(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "");
    conteudo = parseInt(conteudo, 10) + "";
    if (conteudo.length > 0) {
      while (conteudo.length < 9) {
        conteudo = "0" + conteudo;
      }
      conteudo_formatado = "";
      for (var i = 0; i < 9; i++) {
        if (i == 8) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataContaBESC(campo, evento) {
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
    conteudo = campo.value.replace(/[^0-9]/g, "") + key;
    conteudo = parseInt(conteudo, 10) + "";
    if (conteudo.length > 0) {
      while (conteudo.length < 7) {
        conteudo = "0" + conteudo;
      }
      conteudo_formatado = "";
      for (var i = 0; i < 7; i++) {
        if (i == 6) conteudo_formatado += "-";
        if (i == 3) conteudo_formatado += ".";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function formataContaBB(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento) {
    tecla = ehNetscape() ? evento.which : evento.keyCode;
    key = String.fromCharCode(tecla);
  }
  if (evento && (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey)) {
    return true;
  } else if (ehNumero(key) || key == "x") {
    conteudo = campo.value.replace(/[^0-9]/g, "") + (key == "x" ? "" : key);
    conteudo = parseInt(conteudo, 10) + (key == "x" ? "x" : "");
    if (conteudo.length > 0) {
      while (conteudo.length < 8) {
        conteudo = "0" + conteudo;
      }
      conteudo_formatado = "";
      for (var i = 0; i < 8; i++) {
        if (i == 1) conteudo_formatado += ".";
        if (i == 4) conteudo_formatado += ".";
        if (i == 7) conteudo_formatado += "-";
        conteudo_formatado += conteudo.charAt(i);
      }
      campo.value = conteudo_formatado;
    }
  }
  return false;
}

function pulaDDD(campo, evento) {
  var key;
  var tecla;
  var retorno = true;
  if (ehNetscape()) {
    tecla = evento.which;
    tamanho = 0;
  } else {
    tecla = evento.keyCode;
    tamanho = 1;
  }
  key = String.fromCharCode(tecla);

  if (document.getElementById("contato_ddd").value.length > tamanho && !document.getElementById("contato_numero").value.length && !ehTeclaValida(tecla)) {
    document.getElementById("contato_numero").focus();
  } else {
    return formataNumero(campo, evento);
  }
}

function pulaTelefoneSemDDD(campo, evento) {
  var key;
  var tecla;
  var retorno = true;
  if (ehNetscape()) {
    tecla = evento.which;
    tamanho = 9;
  } else {
    tecla = evento.keyCode;
    tamanho = 10;
  }
  key = String.fromCharCode(tecla);

  if (document.getElementById("contato_numero").value.length > tamanho && !document.getElementById("contato_ramal").value.length && !ehTeclaValida(tecla)) {
    document.getElementById("contato_ramal").focus();
  } else {
    return formataTelefoneSemDDD(campo, evento);
  }
}

function ehAlfaNumero(caracter) {
  var teclasValidas = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  if (teclasValidas.indexOf(caracter) != -1 || ehNumero(caracter)) {
    return true;
  } else {
    return false;
  }
}

function limpaVoipCodigo(string) {
  novo_string = "";
  for (var i = 0; i <= string.length - 1; i++) {
    if (ehAlfaNumero(string.charAt(i)) || string.charAt(i) == "_") {
      novo_string += string.charAt(i);
    }
  }
  return novo_string;
}

function formataVoipCodigo(campo, evento) {
  var key;
  var tecla;
  var conteudo;
  if (evento && campo.readOnly) return false;
  if (evento) {
    if (navigator.appName.indexOf("Netscape") != -1) {
      tecla = evento.which;
    } else {
      tecla = evento.keyCode;
    }
    key = String.fromCharCode(tecla);
  }
  if (evento && ehTeclaValida(tecla)) {
    return true;
  } else if (ehAlfaNumero(key) || key == "_" || evento == null) {
    if (evento) campo.value = campo.value + key;
    campo.value = limpaVoipCodigo(campo.value);
  }
  return false;
}
