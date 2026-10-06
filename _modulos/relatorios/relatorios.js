function existeCampoSelecionado(campo_nome, inicio) {
  var i = inicio;
  var selecionado = false;
  while (campo = document.getElementById(campo_nome + '_' + i)) {
    selecionado = selecionado || campo.checked;
    i++;
  }
  return selecionado;
}

function validaRelatorioVisitas(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.data_geracao_inicio.value && !validaData(formulario.data_geracao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Geração » Início";
  }
  if (formulario.data_geracao_termino.value && !validaData(formulario.data_geracao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Geração » Término";
  }
  if (validaData(formulario.data_geracao_inicio.value) && validaData(formulario.data_geracao_termino.value) && subtraiDatas(formulario.data_geracao_termino.value, formulario.data_geracao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Geração » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_geracao_inicio.value) && validaData(formulario.data_geracao_termino.value) && subtraiDatas(formulario.data_geracao_termino.value, formulario.data_geracao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Geração » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_agendamento_inicio.value && !validaData(formulario.data_agendamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Agendamento » Início";
  }
  if (formulario.data_agendamento_termino.value && !validaData(formulario.data_agendamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Agendamento » Término";
  }
  if (validaData(formulario.data_agendamento_inicio.value) && validaData(formulario.data_agendamento_termino.value) && subtraiDatas(formulario.data_agendamento_termino.value, formulario.data_agendamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Agendamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_agendamento_inicio.value) && validaData(formulario.data_agendamento_termino.value) && subtraiDatas(formulario.data_agendamento_termino.value, formulario.data_agendamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Agendamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }
  
  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioContratos(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!existeCampoSelecionado('tipo', 1)) {
    erro_vazio[erro_vazio.length] = "Tipo (Pelo menos um)";
  }
  if (formulario.data_geracao_inicio.value && !validaData(formulario.data_geracao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Geração » Início";
  }
  if (formulario.data_geracao_termino.value && !validaData(formulario.data_geracao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Geração » Término";
  }
  if (validaData(formulario.data_geracao_inicio.value) && validaData(formulario.data_geracao_termino.value) && subtraiDatas(formulario.data_geracao_termino.value, formulario.data_geracao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Geração » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_geracao_inicio.value) && validaData(formulario.data_geracao_termino.value) && subtraiDatas(formulario.data_geracao_termino.value, formulario.data_geracao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Geração » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_ativacao_inicio.value && !validaData(formulario.data_ativacao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Início";
  }
  if (formulario.data_ativacao_termino.value && !validaData(formulario.data_ativacao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Término";
  }
  if (validaData(formulario.data_ativacao_inicio.value) && validaData(formulario.data_ativacao_termino.value) && subtraiDatas(formulario.data_ativacao_termino.value, formulario.data_ativacao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_ativacao_inicio.value) && validaData(formulario.data_ativacao_termino.value) && subtraiDatas(formulario.data_ativacao_termino.value, formulario.data_ativacao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_cancelamento_inicio.value && !validaData(formulario.data_cancelamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Cancelamento » Início";
  }
  if (formulario.data_cancelamento_termino.value && !validaData(formulario.data_cancelamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Cancelamento » Término";
  }
  if (validaData(formulario.data_cancelamento_inicio.value) && validaData(formulario.data_cancelamento_termino.value) && subtraiDatas(formulario.data_cancelamento_termino.value, formulario.data_cancelamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Cancelamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_cancelamento_inicio.value) && validaData(formulario.data_cancelamento_termino.value) && subtraiDatas(formulario.data_cancelamento_termino.value, formulario.data_cancelamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Cancelamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.chamados_abertura_inicio.value && !validaData(formulario.chamados_abertura_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Chamados » Abertura » Início";
  }
  if (formulario.chamados_abertura_termino.value && !validaData(formulario.chamados_abertura_termino.value)) {
    erro_invalido[erro_invalido.length] = "Chamados » Abertura » Término";
  }
  if (validaData(formulario.chamados_abertura_inicio.value) && validaData(formulario.chamados_abertura_termino.value) && subtraiDatas(formulario.chamados_abertura_termino.value, formulario.chamados_abertura_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Chamados » Abertura » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.chamados_abertura_inicio.value) && validaData(formulario.chamados_abertura_termino.value) && subtraiDatas(formulario.chamados_abertura_termino.value, formulario.chamados_abertura_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Chamados » Abertura » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.vigencia_inferior.value && formulario.vigencia_superior.value && parseInt(formulario.vigencia_inferior.value, 10) > parseInt(formulario.vigencia_superior.value, 10)) {
    erro_invalido[erro_invalido.length] = "Prazo de Vigência » Início maior que Término";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioCobrancas(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.competencia_inicio.value && !validaCompetencia(formulario.competencia_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Competência » Início";
  }
  if (formulario.competencia_termino.value && !validaCompetencia(formulario.competencia_termino.value)) {
    erro_invalido[erro_invalido.length] = "Competência » Término";
  }
  if (validaCompetencia(formulario.competencia_inicio.value) && validaCompetencia(formulario.competencia_termino.value) && subtraiDatas('01/' + formulario.competencia_termino.value, '01/' + formulario.competencia_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Competência » Início maior que Término";
  }
  if (formulario.data_geracao_inicio.value && !validaData(formulario.data_geracao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Geração » Início";
  }
  if (formulario.data_geracao_termino.value && !validaData(formulario.data_geracao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Geração » Término";
  }
  if (validaData(formulario.data_geracao_inicio.value) && validaData(formulario.data_geracao_termino.value) && subtraiDatas(formulario.data_geracao_termino.value, formulario.data_geracao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Geração » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_geracao_inicio.value) && validaData(formulario.data_geracao_termino.value) && subtraiDatas(formulario.data_geracao_termino.value, formulario.data_geracao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Geração » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_vencimento_inicio.value && !validaData(formulario.data_vencimento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Vencimento » Início";
  }
  if (formulario.data_vencimento_termino.value && !validaData(formulario.data_vencimento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Vencimento » Término";
  }
  if (validaData(formulario.data_vencimento_inicio.value) && validaData(formulario.data_vencimento_termino.value) && subtraiDatas(formulario.data_vencimento_termino.value, formulario.data_vencimento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Vencimento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_vencimento_inicio.value) && validaData(formulario.data_vencimento_termino.value) && subtraiDatas(formulario.data_vencimento_termino.value, formulario.data_vencimento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Vencimento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_processamento_inicio.value && !validaData(formulario.data_processamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Processamento » Início";
  }
  if (formulario.data_processamento_termino.value && !validaData(formulario.data_processamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Processamento » Término";
  }
  if (validaData(formulario.data_processamento_inicio.value) && validaData(formulario.data_processamento_termino.value) && subtraiDatas(formulario.data_processamento_termino.value, formulario.data_processamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Processamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_processamento_inicio.value) && validaData(formulario.data_processamento_termino.value) && subtraiDatas(formulario.data_processamento_termino.value, formulario.data_processamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Processamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_pagamento_inicio.value && !validaData(formulario.data_pagamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Pagamento » Início";
  }
  if (formulario.data_pagamento_termino.value && !validaData(formulario.data_pagamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Pagamento » Término";
  }
  if (validaData(formulario.data_pagamento_inicio.value) && validaData(formulario.data_pagamento_termino.value) && subtraiDatas(formulario.data_pagamento_termino.value, formulario.data_pagamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Pagamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_pagamento_inicio.value) && validaData(formulario.data_pagamento_termino.value) && subtraiDatas(formulario.data_pagamento_termino.value, formulario.data_pagamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Pagamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_credito_inicio.value && !validaData(formulario.data_credito_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Crédito » Início";
  }
  if (formulario.data_credito_termino.value && !validaData(formulario.data_credito_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Crédito » Término";
  }
  if (validaData(formulario.data_credito_inicio.value) && validaData(formulario.data_credito_termino.value) && subtraiDatas(formulario.data_credito_termino.value, formulario.data_credito_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Crédito » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_credito_inicio.value) && validaData(formulario.data_credito_termino.value) && subtraiDatas(formulario.data_credito_termino.value, formulario.data_credito_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Crédito » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.valor_original_inicio.value && !validaDinheiro(formulario.valor_original_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Valor Original » Início";
  }
  if (formulario.valor_original_termino.value && !validaDinheiro(formulario.valor_original_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Original » Término";
  }
  if (formulario.valor_original_inicio.value && formulario.valor_original_termino.value && valorInteiro(formulario.valor_original_inicio.value) > valorInteiro(formulario.valor_original_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Original » Início maior que Término";
  }
  if (formulario.valor_acrescimo_inicio.value && !validaDinheiro(formulario.valor_acrescimo_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Valor Acréscimo » Início";
  }
  if (formulario.valor_acrescimo_termino.value && !validaDinheiro(formulario.valor_acrescimo_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Acréscimo » Término";
  }
  if (formulario.valor_acrescimo_inicio.value && formulario.valor_acrescimo_termino.value && valorInteiro(formulario.valor_acrescimo_inicio.value) > valorInteiro(formulario.valor_acrescimo_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Acréscimo » Início maior que Término";
  }
  if (formulario.valor_decrescimo_inicio.value && !validaDinheiro(formulario.valor_decrescimo_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Valor Decréscimo » Início";
  }
  if (formulario.valor_decrescimo_termino.value && !validaDinheiro(formulario.valor_decrescimo_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Decréscimo » Término";
  }
  if (formulario.valor_decrescimo_inicio.value && formulario.valor_decrescimo_termino.value && valorInteiro(formulario.valor_decrescimo_inicio.value) > valorInteiro(formulario.valor_decrescimo_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Decréscimo » Início maior que Término";
  }
  if (formulario.valor_total_inicio.value && !validaDinheiro(formulario.valor_total_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Valor Total » Início";
  }
  if (formulario.valor_total_termino.value && !validaDinheiro(formulario.valor_total_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Total » Término";
  }
  if (formulario.valor_total_inicio.value && formulario.valor_total_termino.value && valorInteiro(formulario.valor_total_inicio.value) > valorInteiro(formulario.valor_total_termino.value)) {
    erro_invalido[erro_invalido.length] = "Valor Total » Início maior que Término";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioCondominios(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.data_cadastro_inicio.value && !validaData(formulario.data_cadastro_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Cadastro » Início";
  }
  if (formulario.data_cadastro_termino.value && !validaData(formulario.data_cadastro_termino.value)) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Cadastro » Término";
  }
  if (validaData(formulario.data_cadastro_inicio.value) && validaData(formulario.data_cadastro_termino.value) && subtraiDatas(formulario.data_cadastro_termino.value, formulario.data_cadastro_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Cadastro » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_cadastro_inicio.value) && validaData(formulario.data_cadastro_termino.value) && subtraiDatas(formulario.data_cadastro_termino.value, formulario.data_cadastro_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Cadastro » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_ativacao_inicio.value && !validaData(formulario.data_ativacao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Ativação » Início";
  }
  if (formulario.data_ativacao_termino.value && !validaData(formulario.data_ativacao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Ativação » Término";
  }
  if (validaData(formulario.data_ativacao_inicio.value) && validaData(formulario.data_ativacao_termino.value) && subtraiDatas(formulario.data_ativacao_termino.value, formulario.data_ativacao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Ativação » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_ativacao_inicio.value) && validaData(formulario.data_ativacao_termino.value) && subtraiDatas(formulario.data_ativacao_termino.value, formulario.data_ativacao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Condomínio » Data Ativação » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.cli_data_ativacao_inicio.value && !validaData(formulario.cli_data_ativacao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Ativação » Início";
  }
  if (formulario.cli_data_ativacao_termino.value && !validaData(formulario.cli_data_ativacao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Ativação » Término";
  }
  if (validaData(formulario.cli_data_ativacao_inicio.value) && validaData(formulario.cli_data_ativacao_termino.value) && subtraiDatas(formulario.cli_data_ativacao_termino.value, formulario.cli_data_ativacao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Ativação » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.cli_data_ativacao_inicio.value) && validaData(formulario.cli_data_ativacao_termino.value) && subtraiDatas(formulario.cli_data_ativacao_termino.value, formulario.cli_data_ativacao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Ativação » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.cli_data_cancelamento_inicio.value && !validaData(formulario.cli_data_cancelamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Cancelamento » Início";
  }
  if (formulario.cli_data_cancelamento_termino.value && !validaData(formulario.cli_data_cancelamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Cancelamento » Término";
  }
  if (validaData(formulario.cli_data_cancelamento_inicio.value) && validaData(formulario.cli_data_cancelamento_termino.value) && subtraiDatas(formulario.cli_data_cancelamento_termino.value, formulario.cli_data_cancelamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Cancelamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.cli_data_cancelamento_inicio.value) && validaData(formulario.cli_data_cancelamento_termino.value) && subtraiDatas(formulario.cli_data_cancelamento_termino.value, formulario.cli_data_cancelamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Clientes » Data Cancelamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.cli_data_pedido_cancelamento_inicio.value && !validaData(formulario.cli_data_pedido_cancelamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Cancelamentos » Data Solicitação » Início";
  }
  if (formulario.cli_data_pedido_cancelamento_termino.value && !Solicitação(formulario.cli_data_pedido_cancelamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Cancelamentos » Data Cancelamento » Término";
  }
  if (validaData(formulario.cli_data_pedido_cancelamento_inicio.value) && validaData(formulario.cli_data_pedido_cancelamento_termino.value) && subtraiDatas(formulario.cli_data_pedido_cancelamento_termino.value, formulario.cli_data_pedido_cancelamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Cancelamentos » Data Solicitação » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.cli_data_pedido_cancelamento_inicio.value) && validaData(formulario.cli_data_pedido_cancelamento_termino.value) && subtraiDatas(formulario.cli_data_pedido_cancelamento_termino.value, formulario.cli_data_pedido_cancelamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Cancelamentos » Data Solicitação » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.chamados_abertura_inicio.value && !validaData(formulario.chamados_abertura_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Chamados » Data Abertura » Início";
  }
  if (formulario.chamados_abertura_termino.value && !Solicitação(formulario.chamados_abertura_termino.value)) {
    erro_invalido[erro_invalido.length] = "Chamados » Data Abertura » Término";
  }
  if (validaData(formulario.chamados_abertura_inicio.value) && validaData(formulario.chamados_abertura_termino.value) && subtraiDatas(formulario.chamados_abertura_termino.value, formulario.chamados_abertura_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Chamados » Data Abertura » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.chamados_abertura_inicio.value) && validaData(formulario.chamados_abertura_termino.value) && subtraiDatas(formulario.chamados_abertura_termino.value, formulario.chamados_abertura_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Chamados » Data Abertura » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.prospects_inferior.value && formulario.prospects_superior.value && valorInteiro(formulario.prospects_inferior.value) > valorInteiro(formulario.prospects_superior.value)) {
    erro_invalido[erro_invalido.length] = "Qtde Clientes Prospects » Início maior que Término";
  }
  if (formulario.ativos_inferior.value && formulario.ativos_superior.value && valorInteiro(formulario.ativos_inferior.value) > valorInteiro(formulario.ativos_superior.value)) {
    erro_invalido[erro_invalido.length] = "Qtde Clientes Ativos » Início maior que Término";
  }
  if (formulario.cancelados_inferior.value && formulario.cancelados_superior.value && valorInteiro(formulario.cancelados_inferior.value) > valorInteiro(formulario.cancelados_superior.value)) {
    erro_invalido[erro_invalido.length] = "Qtde Clientes Cancelados » Início maior que Término";
  }
  if (formulario.pedidos_cancelamento_inferior.value && formulario.pedidos_cancelamento_superior.value && valorInteiro(formulario.pedidos_cancelamento_inferior.value) > valorInteiro(formulario.pedidos_cancelamento_superior.value)) {
    erro_invalido[erro_invalido.length] = "Qtde Clientes Pedidos de Cancelamento » Início maior que Término";
  }
  if (formulario.velocidade_maxima_inferior.value && formulario.velocidade_maxima_superior.value && valorInteiro(formulario.velocidade_maxima_inferior.value) > valorInteiro(formulario.velocidade_maxima_superior.value)) {
    erro_invalido[erro_invalido.length] = "Velocidade Máxima » Início maior que Término";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioComissoes(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.data_fechamento_inicio.value && !validaData(formulario.data_fechamento_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Fechamento » Início";
  }
  if (formulario.data_fechamento_termino.value && !validaData(formulario.data_fechamento_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Fechamento » Término";
  }
  if (validaData(formulario.data_fechamento_inicio.value) && validaData(formulario.data_fechamento_termino.value) && subtraiDatas(formulario.data_fechamento_termino.value, formulario.data_fechamento_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Fechamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_fechamento_inicio.value) && validaData(formulario.data_fechamento_termino.value) && subtraiDatas(formulario.data_fechamento_termino.value, formulario.data_fechamento_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Fechamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_ativacao_inicio.value && !validaData(formulario.data_ativacao_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Início";
  }
  if (formulario.data_ativacao_termino.value && !validaData(formulario.data_ativacao_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Término";
  }
  if (validaData(formulario.data_ativacao_inicio.value) && validaData(formulario.data_ativacao_termino.value) && subtraiDatas(formulario.data_ativacao_termino.value, formulario.data_ativacao_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_ativacao_inicio.value) && validaData(formulario.data_ativacao_termino.value) && subtraiDatas(formulario.data_ativacao_termino.value, formulario.data_ativacao_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Ativação » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioRecebimentos(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.venc_inicio.value) {
    erro_vazio[erro_vazio.length] = "Competência » Início";
  } else if (!validaCompetencia(formulario.venc_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Competência » Início";
  }
  if (!formulario.venc_termino.value) {
    erro_vazio[erro_vazio.length] = "Competência » Término";
  } else if (!validaCompetencia(formulario.venc_termino.value)) {
    erro_invalido[erro_invalido.length] = "Competência » Término";
  }
  if (validaCompetencia(formulario.venc_inicio.value) && validaCompetencia(formulario.venc_termino.value) && subtraiDatas('01/' + formulario.venc_termino.value, '01/' + formulario.venc_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Competência » Início maior que Término";
  }
  if (!formulario.proc_inicio.value) {
    erro_vazio[erro_vazio.length] = "Processamento » Início";
  } else if (!validaCompetencia(formulario.proc_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Processamento » Início";
  }
  if (!formulario.proc_termino.value) {
    erro_vazio[erro_vazio.length] = "Processamento » Término";
  } else if (!validaCompetencia(formulario.proc_termino.value)) {
    erro_invalido[erro_invalido.length] = "Processamento » Término";
  }
  if (validaCompetencia(formulario.proc_inicio.value) && validaCompetencia(formulario.proc_termino.value) && subtraiDatas('01/' + formulario.proc_termino.value, '01/' + formulario.proc_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Processamento » Início maior que Término";
  }
  
  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioRecuperacoes(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.proc_inicio.value) {
    erro_vazio[erro_vazio.length] = "Processamento » Início";
  } else if (!validaData(formulario.proc_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Processamento » Início";
  }
  if (!formulario.proc_termino.value) {
    erro_vazio[erro_vazio.length] = "Processamento » Término";
  } else if (!validaData(formulario.proc_termino.value)) {
    erro_invalido[erro_invalido.length] = "Processamento » Término";
  }
  if (validaData(formulario.proc_inicio.value) && validaData(formulario.proc_termino.value) && subtraiDatas(formulario.proc_termino.value, formulario.proc_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Processamento » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.proc_inicio.value) && validaData(formulario.proc_termino.value) && subtraiDatas(formulario.proc_termino.value, formulario.proc_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Processamento » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioFaturamento(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.venc_inicio.value) {
    erro_vazio[erro_vazio.length] = "Vencimento » Início";
  } else if (!validaCompetencia(formulario.venc_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Vencimento » Início";
  } else if (formulario.venc_inicio.value && subtraiDatas("01/" + formulario.venc_inicio.value, vencimento_maximo) > 0) {
    erro_invalido[erro_invalido.length] = "Vencimento » Início maior que o máximo disponível (" + vencimento_maximo.substr(-7) + ")";
  } else if (formulario.venc_inicio.value && subtraiDatas("01/" + formulario.venc_inicio.value, vencimento_minimo) < 0) {
    erro_invalido[erro_invalido.length] = "Vencimento » Início menor que o mínimo disponível (" + vencimento_minimo.substr(-7) + ")";
  }
  if (!formulario.venc_termino.value) {
    erro_vazio[erro_vazio.length] = "Vencimento » Término";
  } else if (!validaCompetencia(formulario.venc_termino.value)) {
    erro_invalido[erro_invalido.length] = "Vencimento » Término";
  } else if (formulario.venc_termino.value && subtraiDatas("01/" + formulario.venc_termino.value, vencimento_maximo) > 0) {
    erro_invalido[erro_invalido.length] = "Vencimento » Término maior que o máximo disponível (" + vencimento_maximo.substr(-7) + ")";
  } else if (formulario.venc_termino.value && subtraiDatas("01/" + formulario.venc_termino.value, vencimento_minimo) < 0) {
    erro_invalido[erro_invalido.length] = "Vencimento » Término menor que o mínimo disponível (" + vencimento_minimo.substr(-7) + ")";
  }
  if (formulario.venc_inicio.value && formulario.venc_termino.value && subtraiDatas("01/" + formulario.venc_inicio.value, "01/" + formulario.venc_termino.value) > 0) {
    erro_invalido[erro_invalido.length] = "Vencimento » Início maior que Término";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioFaturamentoGeral(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.vencimento.value) {
    erro_vazio[erro_vazio.length] = "Vencimento";
  } else if (!validaCompetencia(formulario.vencimento.value)) {
    erro_invalido[erro_invalido.length] = "Vencimento";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioServicosAjustes(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.data_inicio_inicio.value && !validaData(formulario.data_inicio_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Início » Início";
  }
  if (formulario.data_inicio_termino.value && !validaData(formulario.data_inicio_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Início » Término";
  }
  if (validaData(formulario.data_inicio_inicio.value) && validaData(formulario.data_inicio_termino.value) && subtraiDatas(formulario.data_inicio_termino.value, formulario.data_inicio_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Início » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_inicio_inicio.value) && validaData(formulario.data_inicio_termino.value) && subtraiDatas(formulario.data_inicio_termino.value, formulario.data_inicio_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Início » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.data_termino_inicio.value && !validaData(formulario.data_termino_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data Término » Início";
  }
  if (formulario.data_termino_termino.value && !validaData(formulario.data_termino_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data Término » Término";
  }
  if (validaData(formulario.data_termino_inicio.value) && validaData(formulario.data_termino_termino.value) && subtraiDatas(formulario.data_termino_termino.value, formulario.data_termino_inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Data Término » Início maior que Término";
  } else if (formulario.periodo.value > 0 && validaData(formulario.data_termino_inicio.value) && validaData(formulario.data_termino_termino.value) && subtraiDatas(formulario.data_termino_termino.value, formulario.data_termino_inicio.value) > formulario.periodo.value) {
    erro_invalido[erro_invalido.length] = "Data Término » Período máximo de " + formulario.periodo.value + " dia(s)";
  }
  if (formulario.vencimento_inicio.value && formulario.vencimento_termino.value && parseInt(formulario.vencimento_inicio.value, 10) > parseInt(formulario.vencimento_termino.value, 10)) {
    erro_invalido[erro_invalido.length] = "Dia Vencimento » Início maior que Término";
  }
  if (!existeCampoSelecionado('campos', 0)) {
    erro_vazio[erro_vazio.length] = "Campos » Pelo menos um";
  }

  return apresentaErros(erro_vazio, erro_invalido);
  
}

function validaRelatorioCentralTelefonicaGeral(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.data_inicio.value) {
    erro_vazio[erro_vazio.length] = "Data » Início";
  } else if (!validaData(formulario.data_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data » Início";
  } else if (formulario.data_inicio.value && subtraiDatas('22/02/2007', formulario.data_inicio.value) > 0) {
    erro_invalido[erro_invalido.length] = "Data » Início inferior a 22/02/2007";
  } else if (formulario.data_inicio.value && formulario.data_termino.value && subtraiDatas(formulario.data_inicio.value, formulario.data_termino.value) > 0) {
    erro_invalido[erro_invalido.length] = "Data » Início maior que Término";
  }
  if (!formulario.data_termino.value) {
    erro_vazio[erro_vazio.length] = "Data » Término";
  } else if (!validaData(formulario.data_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data » Término";
  } else if (formulario.data_termino.value && subtraiDatas(formulario.data_termino.value, geraDataAtual()) > -1) {
    erro_invalido[erro_invalido.length] = "Data » Término maior que " + subtraiDias(geraDataAtual(), 1);
  }
  if (formulario.data_inicio.value && formulario.data_termino.value && subtraiDatas(formulario.data_termino.value, formulario.data_inicio.value) > 30) {
    erro_invalido[erro_invalido.length] = "Data » Intervalo maior que 30 dias";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function validaRelatorioCentralTelefonica(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.data_inicio.value) {
    erro_vazio[erro_vazio.length] = "Data » Início";
  } else if (!validaData(formulario.data_inicio.value)) {
    erro_invalido[erro_invalido.length] = "Data » Início";
  } else if (formulario.data_inicio.value && subtraiDatas('22/02/2007', formulario.data_inicio.value) > 0) {
    erro_invalido[erro_invalido.length] = "Data » Início inferior a 22/02/2007";
  } else if (formulario.data_inicio.value && formulario.data_termino.value && subtraiDatas(formulario.data_inicio.value, formulario.data_termino.value) > 0) {
    erro_invalido[erro_invalido.length] = "Data » Início maior que Término";
  }
  if (!formulario.data_termino.value) {
    erro_vazio[erro_vazio.length] = "Data » Término";
  } else if (!validaData(formulario.data_termino.value)) {
    erro_invalido[erro_invalido.length] = "Data » Término";
  } else if (formulario.data_termino.value && subtraiDatas(formulario.data_termino.value, geraDataAtual()) > -1) {
    erro_invalido[erro_invalido.length] = "Data » Término maior que " + subtraiDias(geraDataAtual(), 1);
  }
  if (formulario.data_inicio.value && formulario.data_termino.value && subtraiDatas(formulario.data_termino.value, formulario.data_inicio.value) > 30) {
    erro_invalido[erro_invalido.length] = "Data » Intervalo maior que 30 dias";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}