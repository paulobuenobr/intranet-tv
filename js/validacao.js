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
      for(var i=0;i<erro_vazio.length;i++) {
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
      for(var i=0;i<erro_invalido.length;i++) {
        mensagem += "   * " + erro_invalido[i] + "\n"
      }

    }

    alert(mensagem);
    return false;
  } else {
    return true;
  }

}

function validaCondominioDadosCadastrais (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }

  if (!formulario.tipo.value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }

  if (!formulario.cep.value) {
    erro_vazio[erro_vazio.length] = "Endereço » CEP";
  } else {
    if (!cep_valido) {
      erro_invalido[erro_invalido.length] = "Endereço » CEP";
    }
  }

  if (!formulario.numero.value) {
    erro_vazio[erro_vazio.length] = "Endereço » Número (s/n utilize 0)";
  } else {
    if (isNaN(formulario.numero.value)) {
      erro_invalido[erro_invalido.length] = "Endereço » Número";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaBloco (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }

  if (!formulario.cep.value) {
    erro_vazio[erro_vazio.length] = "Endereço » CEP";
  } else {
    if (!cep_valido) {
      erro_invalido[erro_invalido.length] = "Endereço » CEP";
    }
  }

  if (!formulario.numero.value) {
    //erro_vazio[erro_vazio.length] = "Endereço » Número";
  } else {
    if (isNaN(formulario.numero.value)) {
      erro_invalido[erro_invalido.length] = "Endereço » Número";
    }
  }

  if (!formulario.quantidade.value) {
    erro_vazio[erro_vazio.length] = "Apto(s)/Sala(s)";
  } else {
    if (isNaN(formulario.quantidade.value)) {
      erro_invalido[erro_invalido.length] = "Apto(s)/Sala(s)";
    }
  }
  return apresentaErros(erro_vazio,erro_invalido);

}

function validaHora(hora) {
  var filtro = /^[0-9]{2}\:[0-9]{2}$/;
  if (filtro.test(hora)) {
	hora = hora.split(":");
    return hora[0] <= 24 && hora[1] < 60;
  } else {
    return false;
  }
}

function validaTelefone(telefone) {
  var filtro = /^\(([0-9]{2})\) ([0-9]{3,5})\-([0-9]{4})$/;
  if (filtro.test(telefone)) {
    return true;
  } else {
    return false;
  }
}

function validaTelefoneSemDDD(telefone) {
  var filtro = /^([0-9]{4,5})\-([0-9]{4})$/;
  if (filtro.test(telefone)) {
    return true;
  } else {
    return false;
  }
}

function validaEmail(email) {
  var filtro = /^([a-zA-Z0-9])+(([\.\_\-]([a-zA-Z0-9\.]+)|[\_\.\-])+)*\@([a-zA-Z0-9])+([\.\_\-]([a-zA-Z0-9])+)*\.([a-zA-Z0-9]+)$/;
  if (filtro.test(email)) {
    return true;
  } else {
    return false;
  }
}

function validaEmails(emails) {
  var filtro = /^([a-zA-Z0-9])+(([\.\_\-]([a-zA-Z0-9\.]+)|[\_\.\-])+)*\@([a-zA-Z0-9])+([\.\_\-]([a-zA-Z0-9])+)*\.([a-zA-Z0-9]+)([ ]*[,; ][ ]*([a-zA-Z0-9])+(([\.\_\-]([a-zA-Z0-9]+)|[\_\.])+)*\@([a-zA-Z0-9])+([\.\_\-]([a-zA-Z0-9])+)*\.([a-zA-Z0-9]+))*$/;
  if (filtro.test(emails)) {
    return true;
  } else {
    return false;
  }
}

function validaLogin(login) {
  var filtro = /^([a-zA-Z0-9])+([\.\_\-]([a-zA-Z0-9\_\.])+)$/;
  if (filtro.test(login)) {
    return true;
  } else {
    return false;
  }
}

function validaInscricaoEstadual(iscricao_estadual) {
  var filtro = /^([0-9]{3})\.([0-9]{3})\.([0-9]{3})$/;
  if (filtro.test(iscricao_estadual)) {
    return true;
  } else {
    return false;
  }
}

function validaCPF(cpf) {
  var filtro = /^([0-9]{3})\.([0-9]{3})\.([0-9]{3})\-([0-9]{2})$/;
  if (filtro.test(cpf)) {
    // Remove . e - do CPF
    cpf = cpf.replace(/[^0-9]/g, "");
    // Define os digitos verificadores
    digito1 = parseInt(cpf.charAt(9),10);
    digito2 = parseInt(cpf.charAt(10),10);
    // Calcula o somatório e verifica se é igual ao digito verificador 1
    soma = 0;
    multiplicador = 10;
    verificador = 0;
    for(var i=0;i<9;i++) {
      soma += parseInt(cpf.charAt(i),10) * multiplicador;
      multiplicador--;
    }
    verificador = soma % 11;
    if ( verificador == 0 || verificador == 1 ) {
      verificador = 0;
    } else {
      verificador = 11 - verificador;
    }
    if ( digito1 != verificador ) {
      return false;
    }
    // Calcula o somatório e verifica se é igual ao digito verificador 2
    soma = 0;
    multiplicador = 11;
    verificador = 0;
    for(var i=0;i<10;i++) {
      soma += parseInt(cpf.charAt(i),10) * multiplicador;
      multiplicador--;
    }
    verificador = soma % 11;
    if ( verificador == 0 || verificador == 1 ) {
      verificador = 0;
    } else {
      verificador = 11 - verificador;
    }
    if ( digito2 != verificador ) {
      return false;
    }
    return true;
  } else {
    return false;
  }
}

function validaCNPJ(cnpj) {
  var i;
  var soma;
  var resto;
  var dv1;
  var dv2;
  var pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
  var pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

  cnpj = limpaCNPJ(cnpj);

  if (cnpj.length != 14) {
    return false;
  }

  if (/^(.)\1{13}$/.test(cnpj)) {
    return false;
  }

  soma = 0;
  for (i = 0; i < 12; i++) {
    soma += valorCNPJMod11(cnpj.charAt(i)) * pesos1[i];
  }
  resto = soma % 11;
  dv1 = resto > 1 ? 11 - resto : 0;
  if (parseInt(cnpj.charAt(12), 10) !== dv1) {
    return false;
  }

  soma = 0;
  for (i = 0; i < 13; i++) {
    soma += valorCNPJMod11(cnpj.charAt(i)) * pesos2[i];
  }
  resto = soma % 11;
  dv2 = resto > 1 ? 11 - resto : 0;

  return parseInt(cnpj.charAt(13), 10) === dv2;
}

function validaVoipCodigo(voip_codigo) {
  var filtro = /^([0-9a-zA-Z\_]+)$/;
  if (filtro.test(voip_codigo)) {
    return true;
  } else {
    return false;
  }
}

function validaVoipUsuario(voip_usuario) {
  var filtro = /^([a-zA-Z0-9])+([\_]([a-zA-Z0-9])+)*$/;
  if (filtro.test(voip_usuario)) {
    return true;
  } else {
    return false;
  }
}

function validaClienteDadosCadastrais(formulario) {
  // Editar

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.atualizacao_cadastro.value == 0 && formulario.duplicado_bloqueio.value == 1) {
    alert('Não é permitido o cadastro duplicado deste cliente!');
    return false;
  }
  
  if (formulario.atualizacao_cadastro.value == 0 && formulario.duplicado_cpf_cnpj.value == 1 && formulario.duplicar_cpf_cnpj.value == 0) {
    alert('Você não tem permissão para cadastrar um cliente com CPF ou CNPJ duplicado.');
    return false;
  }

  if (formulario.atualizacao_cadastro.value == 0 && formulario.duplicado_endereco.value == 1 && formulario.duplicar_endereco.value == 0) {
    alert('Você não tem permissão para cadastrar um cliente com endereço duplicado.');
    return false;
  }

  if (!formulario.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }

  if (!formulario.tipo.value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }

  if (formulario.tipo.value == 2) {
    if (formulario.cpf_cnpj.value) {
      if (!validaCNPJ(formulario.cpf_cnpj.value)) {
        erro_invalido[erro_invalido.length] = "CNPJ";
      }
    }
    if (formulario.inscricao_estadual.value) {
      if (formulario.inscricao_estadual.value.length < 6) {
        erro_invalido[erro_invalido.length] = "Inscrição Estadual";
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
    if (!formulario.bloco.value && (!formulario.bloco_original || formulario.bloco_original.value != '')) {
      erro_vazio[erro_vazio.length] = "Endereço » Bloco";
    }
  } else {
    if (!formulario.cep.value) {
      erro_vazio[erro_vazio.length] = "Endereço » CEP";
    }
  }

  if (formulario.complemento_tipo.value != 'o' && !formulario.complemento_valor.value) {
    erro_vazio[erro_vazio.length] = "Endereço » Complemento";
  }

  if (!formulario.situacao.value) {
    erro_vazio[erro_vazio.length] = "Situação";
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaClienteDadosFinanceiros(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.condominio.value) {
    if (!formulario.bloco.value) {
      erro_vazio[erro_vazio.length] = "Endereço » Bloco";
    }
  } else {
    if (!formulario.cep.value) {
      erro_vazio[erro_vazio.length] = "Endereço » CEP";
    }
  }

  if (formulario.complemento_tipo.value != 'o' && !formulario.complemento_valor.value) {
    erro_vazio[erro_vazio.length] = "Endereço » Complemento";
  }

  if (formulario.condominial && formulario.condominial.value == '') {
    if (!formulario.cobranca_vencimento.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Vencimento";
    }
    if (!formulario.cobranca_formato.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Formato";
    }
  }

  if (formulario.cobranca_formato.value.substr(2) == 4 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 23) { // CEF
    if (!formulario.cobranca_debito_operacao.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Operação";
    } else {
      if (!validaOperacaoCEF(formulario.cobranca_debito_operacao.value)) {
        erro_invalido[erro_invalido.length] = "Dados de Cobrança » Operação";
      }
    }
  }
  if (formulario.cobranca_formato.value.substr(0, 1) == 4) { // Débito em conta
    if (!formulario.cobranca_debito_agencia.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Agência";
    } else {
      if (formulario.cobranca_formato.value.substr(2) == 4 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 23) { // CEF
        if (!validaAgenciaCEF(formulario.cobranca_debito_agencia.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Agência";
        }
      } else {
        if (!validaAgenciaBB(formulario.cobranca_debito_agencia.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Agência";
        }
      }
    }
    if (!formulario.cobranca_debito_conta.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Conta";
    } else {
      if (formulario.cobranca_formato.value.substr(2) == 4 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 23) { // CEF
        if (!validaContaCEF(formulario.cobranca_debito_conta.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Conta";
        }
      } else if (formulario.cobranca_formato.value.substr(2) == 19 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 24) { // BESC
        if (!validaContaBESC(formulario.cobranca_debito_conta.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Conta";
        }
      } else {
        if (!validaContaBB(formulario.cobranca_debito_conta.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Conta";
        }
      }
    }
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaClienteDadosComerciais(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.cobranca_vencimento.disabled && !formulario.cobranca_vencimento.value) {
    erro_vazio[erro_vazio.length] = "Dados de Cobrança » Vencimento";
  }
  if (!formulario.cobranca_formato.disabled && !formulario.cobranca_formato.value) {
    erro_vazio[erro_vazio.length] = "Dados de Cobrança » Formato";
  }
  if (formulario.cobranca_formato.value.substr(2) == 4 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 23) { // CEF
    if (!formulario.cobranca_debito_operacao.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Operação";
    } else {
      if (!validaOperacaoCEF(formulario.cobranca_debito_operacao.value)) {
        erro_invalido[erro_invalido.length] = "Dados de Cobrança » Operação";
      }
    }
  }
  if (formulario.cobranca_formato.value.substr(0, 1) == 4) { // Débito em conta
    if (!formulario.cobranca_debito_agencia.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Agência";
    } else {
      if (formulario.cobranca_formato.value.substr(2) == 4 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 23) { // CEF
        if (!validaAgenciaCEF(formulario.cobranca_debito_agencia.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Agência";
        }
      } else {
        if (!validaAgenciaBB(formulario.cobranca_debito_agencia.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Agência";
        }
      }
    }
    if (!formulario.cobranca_debito_conta.value) {
      erro_vazio[erro_vazio.length] = "Dados de Cobrança » Conta";
    } else {
      if (formulario.cobranca_formato.value.substr(2) == 4 || formulario.cobranca_formato.value.substr(2) == 18 || formulario.cobranca_formato.value.substr(2) == 23) { // CEF
        if (!validaContaCEF(formulario.cobranca_debito_conta.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Conta";
        }
      } else {
        if (!validaContaBB(formulario.cobranca_debito_conta.value)) {
          erro_invalido[erro_invalido.length] = "Dados de Cobrança » Conta";
        }
      }
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaClienteDadosTecnicos (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.login.value) {
    if (!validaLogin(formulario.login.value)) {
      erro_invalido[erro_invalido.length] = "Login";
    }
  }

  if (formulario.instalacao_data != null && formulario.instalacao_data.value) {
    if (!validaData(formulario.instalacao_data.value)) {
      erro_invalido[erro_invalido.length] = "Instalação (Data)";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaDataHora(data_hora) {
  var filtro = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4} \([0-9]{2}\:[0-9]{2}\)$/;
  if (filtro.test(data_hora)) {
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

function validaCompetencia(competencia) {
  return validaData('01/' + competencia);
}

function validaLogin(login) {
  var filtro = /^([a-zA-Z0-9])+(([\.\_\-]([a-zA-Z0-9]+)|[\_])+)*$/;
  if (filtro.test(login)) {
    return true;
  } else {
    return false;
  }
}

function validaDescontoConcessao (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.servico != null && !formulario.servico.value) {
    erro_vazio[erro_vazio.length] = "Serviço";
  }

  if (formulario.desconto != null && !formulario.desconto.value) {
    erro_vazio[erro_vazio.length] = "Desconto";
  }

  if (formulario.instalacao_valor.value) {
    if (!validaDesconto(formulario.instalacao_valor.value)) {
      erro_invalido[erro_invalido.length] = "Instalação";
    }
  }

  if (formulario.mensalidade_valor.value) {
    if (!validaDesconto(formulario.mensalidade_valor.value)) {
      erro_invalido[erro_invalido.length] = "Mensalidade";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaDesconto (desconto) {
  if (validaDinheiro(desconto) || validaPorcentagem(desconto)) {
    return true;
  } else {
    return false;
  }
}

function validaDinheiro (dinheiro) {
  var filtro_dinheiro = /^(([^0-9]+)?[0-9]{1,3}(\.([0-9]{3})+)*\,([0-9]{2,}))$/;
  if (filtro_dinheiro.test(dinheiro)) {
    return true;
  } else {
    return false;
  }
}

function validaPorcentagem (porcentagem) {
  if (porcentagem == "100%") return true;
  var filtro_porcentagem = /^([0-9]{1,2}\%)$/;
  if (filtro_porcentagem.test(porcentagem)) {
    return true;
  } else {
    return false;
  }
}

function validaGrupo(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaClienteDominio (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.dominio && formulario.dominio.value) {
    if (!validaDominio(formulario.dominio.value) || (!dominio_checado && formulario.origem.value != 2)) {
      erro_invalido[erro_invalido.length] = "Domínio";
    }
  } else {
    erro_vazio[erro_vazio.length] = "Domínio";
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaDominio(dominio) {
  // d+(ed+)*.(d2|d4))
  var filtro = /^([a-zA-Z0-9])+([\.\_\-]([a-zA-Z0-9])+)*\.([a-zA-Z0-9]{2,4})$/;
  if (filtro.test(dominio)) {
    return true;
  } else {
    return false;
  }
}

function validaContratoBusca (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.data_geracao_inicio.value) {
    if (!validaData(formulario.data_geracao_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Geração Início";
    }
  }

  if (formulario.data_geracao_termino.value) {
    if (!validaData(formulario.data_geracao_termino.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Geração Término";
    }
  }

  if (formulario.data_ativacao_inicio.value) {
    if (!validaData(formulario.data_ativacao_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Ativação Início";
    }
  }

  if (formulario.data_ativacao_termino.value) {
    if (!validaData(formulario.data_ativacao_termino.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Ativação Término";
    }
  }

  if (formulario.data_cancelamento_inicio.value) {
    if (!validaData(formulario.data_cancelamento_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Cancelamento Início";
    }
  }

  if (formulario.data_cancelamento_termino.value) {
    if (!validaData(formulario.data_cancelamento_termino.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Cancelamento Término";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);
}

function validaClienteBusca (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

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

  if (formulario.telefone.value) {
    if (!validaTelefone(formulario.telefone.value)) {
      erro_invalido[erro_invalido.length] = "Telefone";
    }
  }

  if (formulario.email.value) {
    if (!validaEmail(formulario.email.value)) {
      erro_invalido[erro_invalido.length] = "E-mail";
    }
  }

  if (formulario.data_cadastro_inicio.value) {
    if (!validaData(formulario.data_cadastro_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Cadastro Início";
    }
  }

  if (formulario.data_cadastro_termino.value) {
    if (!validaData(formulario.data_cadastro_termino.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Cadastro Término";
    }
  }

  if (formulario.data_ass_cadastro_inicio.value) {
    if (!validaData(formulario.data_ass_cadastro_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Cadastro Início";
    }
  }

  if (formulario.data_ass_cadastro_termino.value) {
    if (!validaData(formulario.data_ass_cadastro_termino.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Cadastro Término";
    }
  }

  if (formulario.data_ass_fechamento_inicio.value) {
    if (!validaData(formulario.data_ass_fechamento_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Fechamento Início";
    }
  }

  if (formulario.data_ass_fechamento_termino.value) {
    if (!validaData(formulario.data_ass_fechamento_termino.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Fechamento Término";
    }
  }

  if (formulario.data_ass_ativacao_inicio.value) {
    if (!validaData(formulario.data_ass_ativacao_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Ativação Início";
    }
  }

  if (formulario.data_ass_ativacao_termino.value) {
    if (!validaData(formulario.data_ass_ativacao_termino.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Ativação Término";
    }
  }

  if (formulario.data_ass_cancelamento_inicio.value) {
    if (!validaData(formulario.data_ass_cancelamento_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Cancelamento Início";
    }
  }

  if (formulario.data_ass_cancelamento_termino.value) {
    if (!validaData(formulario.data_ass_cancelamento_termino.value)) {
      erro_invalido[erro_invalido.length] = "Assinatura » Data Cancelamento Término";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);
}

function validaCondominioBusca (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.data_cadastro_inicio.value) {
    if (!validaData(formulario.data_cadastro_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Cadastro Início";
    }
  }

  if (formulario.data_cadastro_termino.value) {
    if (!validaData(formulario.data_cadastro_termino.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Cadastro Término";
    }
  }

  if (formulario.data_ativacao_inicio.value) {
    if (!validaData(formulario.data_ativacao_inicio.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Ativação Início";
    }
  }

  if (formulario.data_ativacao_termino.value) {
    if (!validaData(formulario.data_ativacao_termino.value)) {
      erro_invalido[erro_invalido.length] = "Período » Data Ativação Término";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);
}

function validaMailingNomesAtualizarEtapa1 (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.condominio.value) erro_vazio[erro_vazio.length] = "Condomínio";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaMailingNomesAtualizarEtapa2 (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();
  var ok = false;

  for(var i=0;i<formulario.elements.length;i++) if (formulario.elements[i].type == "checkbox" && formulario.elements[i].checked) ok = true;
  if (!ok) erro_vazio[erro_vazio.length] = "OK";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaMailingNomesAtualizarEtapa3 (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();
  var ok = false;

  for(var i=0;i<formulario.elements.length;i++) if (formulario.elements[i].type == "checkbox" && formulario.elements[i].checked) ok = true;
  if (!ok) erro_vazio[erro_vazio.length] = "OK";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaMailingLotes (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";
  if (formulario.inicio.value && !validaData(formulario.inicio.value)) erro_invalido[erro_invalido.length] = "Validade » Início";
  if (formulario.termino.value && !validaData(formulario.termino.value)) erro_invalido[erro_invalido.length] = "Validade » Término";
  if (!formulario.status.value) erro_vazio[erro_vazio.length] = "Status";
  if (!formulario.regras.value) erro_vazio[erro_vazio.length] = "Regras";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaMailingLotesRegistrar (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();
  var situacao = false;

  for(var i=0;i<formulario.elements.length;i++) if (formulario.elements[i].type == "checkbox" && formulario.elements[i].name.indexOf("situacao") != -1 && formulario.elements[i].checked) situacao = true;
  if (!situacao) erro_vazio[erro_vazio.length] = "Último Contato";

  if (formulario.regras.value.indexOf(extraiMailingLotesRegraFormulario(formulario)) != -1) erro_invalido[erro_invalido.length] = "Regra já Existente!";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaMailingScripts (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.titulo.value) erro_vazio[erro_vazio.length] = "Título";
  if (!formulario.servico.value) erro_vazio[erro_vazio.length] = "Serviço";
  if (!formulario.tipo.value) erro_vazio[erro_vazio.length] = "Tipo";
  if (!formulario.conteudo.value) erro_vazio[erro_vazio.length] = "Conteúdo";
  if (!formulario.formato.value) erro_vazio[erro_vazio.length] = "Formato";
  if (!formulario.status.value) erro_vazio[erro_vazio.length] = "Status";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaClienteRetorno (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.usuario) {
    if (!formulario.usuario.value) erro_vazio[erro_vazio.length] = "Usuário";
  }

  if (formulario.data) {
    if (!formulario.data.value) {
      erro_vazio[erro_vazio.length] = "Data";
    } else {
      if (!validaData(formulario.data.value)) {
        erro_invalido[erro_invalido.length] = "Data";
      }
    }
  }

  if (!formulario.comentario.value) erro_vazio[erro_vazio.length] = "Comentário";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaClienteResponsabilidadeAprovar (formulario) {
  if (formulario.conflito.value == 1 && formulario.permissao.value == 0) {
    alert('Você não tem permissão para aprovar uma responsabilidade com datas conflitantes.');
    return false;
  } else if (formulario.conflito.value == 1 && formulario.permissao.value == 1) {
    retorno = confirm('Este cliente possui outra(s) responsabilidade(s) vigente(s), a aprovação desta responsabilidade implicará na finalização da(s) responsabilidade(s) ativa(s), você realmente deseja aprovar?');
    if (!retorno) return false;
  }
  return true;
}

function validaCobrancasRetornosPublicar (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.arquivo.value) erro_vazio[erro_vazio.length] = "Arquivo";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaRetornosPublicar (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.formato.value) erro_vazio[erro_vazio.length] = "Formato";
  if (!formulario.arquivo.value) erro_vazio[erro_vazio.length] = "Arquivo";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaTarefaEditar (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.etapa_origem.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Origem";
  if (!formulario.etapa_tipo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Tipo";

  switch (parseInt(formulario.etapa_tipo.value)) {
    case (1) : {}
    case (2) : {
      // Tipo
      if (formulario.novo_tipo_nome.value || formulario.novo_tipo_autoaprovado.value) {
        if (!formulario.novo_tipo_nome.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Novo Tipo » Nome";
        if (!formulario.novo_tipo_autoaprovado.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Novo Tipo » Aprovação";
      } else {
        if (!formulario.novo_tipo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Tipo";
      }
      break;
    }
    case (3) : {}
    case (4) : {
      // Prazo
      if (!formulario.prazo_inicio.value) {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Início";
      } else {
        if (!validaDataHora(formulario.prazo_inicio.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Início";
      }
      if (!formulario.prazo_termino.value) {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Término";
      } else {
        if (!validaDataHora(formulario.prazo_termino.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Término";
      }
      break;
    }
    case (5) : {
      // Andamento
      if (!formulario.andamento.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Andamento";
      break;
    }
    case (6) : {}
    case (7) : {
      // Previsão
      if (!formulario.previsao_inicio.value) {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Início";
      } else {
        if (!validaDataHora(formulario.previsao_inicio.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Início";
      }
      if (!formulario.previsao_termino.value) {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Término";
      } else {
        if (!validaDataHora(formulario.previsao_termino.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Término";
      }
      break;
    }
    case (8) : {
      // Comentário
      if (!formulario.conteudo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Conteúdo";
      break;
    }
    case (9) : {
      // Anexo
      if (!formulario.arquivo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Arquivo";
      break;
    }
    case (10) : {}
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaTarefaEncaminhar (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.destino.value) erro_vazio[erro_vazio.length] = "Destino";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaPrefixoIP (prefixo) {
  var filtro = /^([0-9]{1,3})$/;
  if (filtro.test(prefixo)) {
    return true;
  } else {
    return false;
  }
}

function validaMascaraIP (mascara) {
  var filtro = /^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/;
  if (filtro.test(mascara)) {
    return true;
  } else {
    return false;
  }
}

function validaMAC(mac) {
  var filtro = /^([a-fA-F0-9]{2})\:([a-fA-F0-9]{2})\:([a-fA-F0-9]{2})\:([a-fA-F0-9]{2})\:([a-fA-F0-9]{2})\:([a-fA-F0-9]{2})$/;
  if (filtro.test(mac)) {
    return true;
  } else {
    return false;
  }
}

function validaRecuperacaoLote (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";
  if (formulario.inicio.value && !validaData(formulario.inicio.value)) erro_invalido[erro_invalido.length] = "Validade » Início";
  if (formulario.termino.value && !validaData(formulario.termino.value)) erro_invalido[erro_invalido.length] = "Validade » Término";
  if (!formulario.status.value) erro_vazio[erro_vazio.length] = "Status";
  if (!formulario.regras.value) erro_vazio[erro_vazio.length] = "Regras";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaRecuperacaoLoteRegistrar (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();
  var situacao = false;

  for(var i=0;i<formulario.elements.length;i++) if (formulario.elements[i].type == "checkbox" && formulario.elements[i].name.indexOf("situacao") != -1 && formulario.elements[i].checked) situacao = true;
  if (!situacao) erro_vazio[erro_vazio.length] = "Último Contato";
  if (formulario.cobranca_data_exclusiva.value && !validaData(formulario.cobranca_data_exclusiva.value)) erro_invalido[erro_invalido.length] = "Data Exclusiva";

  if (formulario.regras.value.indexOf(extraiRecuperacaoLoteRegraFormulario(formulario)) != -1) erro_invalido[erro_invalido.length] = "Regra já Existente!";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaRecuperacaoScript (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.titulo.value) erro_vazio[erro_vazio.length] = "Título";
  if (!formulario.tipo.value) erro_vazio[erro_vazio.length] = "Tipo";
  if (!formulario.conteudo.value) erro_vazio[erro_vazio.length] = "Conteúdo";
  if (!formulario.formato.value) erro_vazio[erro_vazio.length] = "Formato";
  if (!formulario.status.value) erro_vazio[erro_vazio.length] = "Status";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaCondominioDadosTecnicos (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  return apresentaErros(erro_vazio,erro_invalido);
}

function validaAgenciaBB(agencia) {
  agencia = limpaStringBB(agencia);

  // Substitui X por 10
  if (agencia.charAt(4) == 'X' || agencia.charAt(4) == 'x') {
    digito = 10;
  } else {
    digito = parseInt(agencia.charAt(4));
  }

  // Calcula o somatório e verifica se é igual ao digito
  soma = 0;
  multiplicador = 5;
  verificador = 0;
  for(var i=0; i<4; i++) {
    soma += parseInt(agencia.charAt(i)) * multiplicador;
    multiplicador--;
  }
  verificador = soma % 11;
  if ( verificador == 0 ) {
    verificador = 0;
  } else {
    verificador = 11 - verificador;
  }
  if ( digito != verificador ) {
    return false;
  } else {
    return true;
  }
}

function validaContaBB(conta) {
  conta = limpaStringBB(conta);

  // Substitui X por 10
  if (conta.charAt(7) == 'X' || conta.charAt(7) == 'x') {
    digito = 10;
  } else {
    digito = parseInt(conta.charAt(7));
  }

  // Calcula o somatório e verifica se é igual ao digito
  soma = 0;
  multiplicador = 8;
  verificador = 0;
  for(var i=0; i<7; i++) {
    soma += parseInt(conta.charAt(i)) * multiplicador;
    multiplicador--;
  }
  verificador = soma % 11;
  if ( verificador == 0 ) {
    verificador = 0;
  } else {
    verificador = 11 - verificador;
  }
  if ( digito != verificador ) {
    return false;
  } else {
    return true;
  }
}

function validaAgenciaCEF(agencia) {
  var filtro = /^[0-9]{4}$/;
  if (filtro.test(agencia)) {
    return true;
  } else {
    return false;
  }
}

function validaContaCEF(conta) {
  var filtro = /^[0-9]{8}-[0-9]{1}$/;
  if (filtro.test(conta)) {
    return true;
  } else {
    return false;
  }
}

function validaOperacaoCEF(operacao) {
  var filtro = /^[0-9]{3}$/;
  if (filtro.test(operacao)) {
    return true;
  } else {
    return false;
  }
}

function validaContaBESC(conta) {
  var filtro = /^[0-9]{3}.[0-9]{3}-[0-9]{1}$/;
  if (filtro.test(conta)) {
    return true;
  } else {
    return false;
  }
}
