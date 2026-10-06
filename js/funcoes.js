function mudaClienteCondominio (campo, sufixo) {
  if (!sufixo) sufixo = '';
  condominio = campo.value;
  if (condominio) {
    buscaBlocos(condominio, 0, sufixo);
    if (eval('campo.form.' + sufixo + 'cep') != null && eval('campo.form.' + sufixo + 'numero') != null) {
      eval('campo.form.' + sufixo + 'cep.disabled = true;');
      eval('campo.form.' + sufixo + 'numero.disabled = true;');
      buscaEnderecoCondominio(condominio, sufixo);
    }
  } else {
    eval('atualizaSelect(campo.form.' + sufixo + 'bloco);');
    if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
      eval('atualizaSelect(campo.form.cobranca_bloco);');
    }
    if (eval('campo.form.' + sufixo + 'cep') != null && eval('campo.form.' + sufixo + 'numero') != null) {
      eval('campo.form.' + sufixo + 'cep.disabled = false;');
      eval('campo.form.' + sufixo + 'numero.disabled = false;');
      eval('campo.form.' + sufixo + 'cep.value = "";');
      eval('campo.form.' + sufixo + 'estado.value = "";');
      eval('campo.form.' + sufixo + 'cidade.value = "";');
      eval('campo.form.' + sufixo + 'bairro.value = "";');
      eval('campo.form.' + sufixo + 'logradouro.value = "";');
      eval('campo.form.' + sufixo + 'numero.value = "";');
      if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
        eval('campo.form.cobranca_cep.value = "";');
        eval('campo.form.cobranca_estado.value = "";');
        eval('campo.form.cobranca_cidade.value = "";');
        eval('campo.form.cobranca_bairro.value = "";');
        eval('campo.form.cobranca_logradouro.value = "";');
        eval('campo.form.cobranca_numero.value = "";');
      }
    }
  }
  if (eval('campo.form.' + sufixo + 'complemento_valor') != null) eval('campo.form.' + sufixo + 'complemento_valor.value = "";');
  if (eval('campo.form.' + sufixo + 'complemento_tipo') != null) eval('campo.form.' + sufixo + 'complemento_tipo.options[0].selected = true;');
  if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
    if (eval('campo.form.cobranca_complemento_valor') != null) eval('campo.form.cobranca_complemento_valor.value = "";');
    if (eval('campo.form.cobranca_complemento_tipo') != null) eval('campo.form.cobranca_complemento_tipo.options[0].selected = true;');
  }
  //if (document.getElementById(sufixo + "panorama_condominio") != null) buscaPanoramaCondominio(condominio);
  if (document.getElementById(sufixo + "panorama_condominio") != null) buscaPanoramaCondominio(condominio, sufixo);
  if (document.getElementById(sufixo + "panorama_bloco") != null) document.getElementById(sufixo + "panorama_bloco").innerHTML='';
  //if (document.getElementById(sufixo + "alertas") != null) verificaClientesMesmoEndereco(campo.form, 0);
  if (document.getElementById(sufixo + "alertas") != null) buscaAlertas(campo.form);

  ccsc = (condominio) ? 1 : 0;
}

function mudaClienteBloco (campo, sufixo) {
  if (!sufixo) sufixo = '';
  bloco = campo.value;
  if (bloco) {
    if (eval('campo.form.' + sufixo + 'cep') != null && eval('campo.form.' + sufixo + 'numero') != null) buscaEnderecoBloco(bloco, sufixo);
  } else {
    if (eval('campo.form.' + sufixo + 'cep') != null && eval('campo.form.' + sufixo + 'numero') != null) buscaEnderecoCondominio(campo.form.condominio.value, sufixo);
  }
  if (document.getElementById(sufixo + "panorama_bloco") != null) buscaPanoramaBloco(bloco, sufixo);
  if (document.getElementById(sufixo + "alertas") != null) buscaAlertas(campo.form);
  //if (document.getElementById(sufixo + "alertas") != null) verificaClientesMesmoEndereco(campo.form, 0);
}

function mudaClienteComplemento (campo, sufixo) {
  if (!sufixo) sufixo = '';
  if (document.getElementById(sufixo + "alertas") != null) buscaAlertas(campo.form);
}
var WSistemaOS;

function getIndex(campo) {
  var index = -1, i = 0, found = false;
  while (i < campo.form.length && index == -1) {
    if (campo.form[i] == campo)index = i; else i++;
  }
  return index;
}

function mudaClienteTipo(campo) {
  if (campo.value == 2) {
    // Pessoa Jur�dica
    pfpj = 0;
    document.getElementById("cpf_cnpj_label").innerHTML = "&nbsp;CNPJ:";
    campo.form.cpf_cnpj.maxLength = "18";
    if (campo.form.inscricao_estadual) campo.form.inscricao_estadual.disabled = campo.form.isento_inscricao_estadual.checked;
    if (campo.form.isento_inscricao_estadual) campo.form.isento_inscricao_estadual.disabled = false;
    if (campo.form.busca_incricao_estadual) campo.form.busca_incricao_estadual.disabled = false;
    if (campo.form.isento_inscricao_estadual && campo.form.isento_inscricao_estadual.checked) {
      campo.form.inscricao_estadual.value = "";
    }
    if (campo.form.nascimento) campo.form.nascimento.disabled = true;
    if (campo.form.nascimento) campo.form.nascimento.value = "";
  } else {
    // Pessoa F�sica
    pfpj = 1;
    document.getElementById("cpf_cnpj_label").innerHTML = "&nbsp;CPF:";
    campo.form.cpf_cnpj.maxLength = "14";
    if (campo.form.nascimento) campo.form.nascimento.disabled = false;
    if (campo.form.inscricao_estadual) campo.form.inscricao_estadual.value = "";
    if (campo.form.inscricao_estadual) campo.form.inscricao_estadual.disabled = true;
    if (campo.form.busca_incricao_estadual) campo.form.busca_incricao_estadual.disabled = true;
    if (campo.form.isento_inscricao_estadual) campo.form.isento_inscricao_estadual.checked = false;
    if (campo.form.isento_inscricao_estadual) campo.form.isento_inscricao_estadual.disabled = true;
  }
  if (document.getElementById("alertas") != null) {
    buscaAlertas(campo.form);
  }
}

function mudaClienteCPFCNPJ(campo) {
  if (document.getElementById("alertas") != null) {
    buscaAlertas(campo.form);
  }
}

function atualizaSelect(campo, dados, ativo, branco, desabilitado, atualizar) {
  var grupo, grupo_label;
  if (atualizar == undefined) atualizar = 1;
  if (desabilitado == undefined) desabilitado = 0;
  if (branco == undefined) branco = 1;
  campo.innerHTML = '';
  campo.options.length = branco;
  if (dados != null && dados.length > 0) {
    for (i = 0; i < dados.length; i++) {
      if (dados[i][2] && dados[i][2] != grupo_label) {
        grupo_label = dados[i][2];
        grupo = document.createElement("OPTGROUP");
        grupo.label = dados[i][2];
      }
      opcao = document.createElement("OPTION");
      opcao.value = dados[i][0];
      opcao.text = dados[i][1];
      if (grupo_label) {
        opcao.innerText = dados[i][1];
        grupo.appendChild(opcao);
        campo.appendChild(grupo);
      } else {
        campo.options.add(opcao);
      }
      if (dados[i][0] == ativo || (dados.length == 1 && !ativo)) {
        opcao.selected = true;
      }
    }
    campo.disabled = desabilitado;
    if (dados.length == 1 && !ativo && campo.onchange != null && atualizar) {
      campo.onchange();
    }
  } else {
    campo.disabled = true;
  }
}

function mOverLA(id,campos,cor) {
  for (var i=0;i<campos;i++) {
    if (document.getElementById('la_' + id + '_' + i)) {
      document.getElementById('la_' + id + '_' + i).style.cursor = 'pointer';
      document.getElementById('la_' + id + '_' + i).style.backgroundColor = cor;
    }
  }
}

function mOutLA(id,campos,cor) {
  for (var i=0;i<campos;i++) {
    if (document.getElementById('la_' + id + '_' + i)) {
      document.getElementById('la_' + id + '_' + i).style.cursor = 'default';
      document.getElementById('la_' + id + '_' + i).style.backgroundColor = cor;
    }
  }
}

function abreConsultaCEP() {
  abrir("consulta_cep", "consulta_cep.php", 350, 295, 1);
}

function geraDataAtual() {
  data_atual = new Date();
  dia_atual = data_atual.getDate();
  if (dia_atual < 10) dia_atual = '0' + dia_atual;
  mes_atual = data_atual.getMonth() + 1;
  if (mes_atual < 10) mes_atual = '0' + mes_atual;
  ano_atual = data_atual.getFullYear();
  return (dia_atual+'/'+mes_atual+'/'+ano_atual);
}

function abrirOS(url,id,nome,condominio,complemento,telefone,atendente,email) {
  url += "ord_adicionar.php?cliente=" + id + "&nome=" + nome + "&condominio=" + condominio + "&complemento=" + complemento + "&telefone=" + telefone + "&atendente=" + atendente + "&email=" + email;
  if (WSistemaOS != null) WSistemaOS.close();
  WSistemaOS = window.open(url, "sistema_os");
}

var PainelIntervalo;
var PainelPlacarSetor;
var PainelPlacarMes;
var PainelPlacarAno;

function carregaPainel(placar_setor,placar_mes,placar_ano) {
  PainelPlacarSetor = (placar_setor) ? placar_setor : '';
  PainelPlacarMes = (placar_mes) ? placar_mes : '';
  PainelPlacarAno = (placar_ano) ? placar_ano : '';
  RDSCarregar('painel','rds.js.php?m=painel&ps=' + PainelPlacarSetor + '&pm=' + PainelPlacarMes + '&pa=' + PainelPlacarAno);
  atualizaPainel();
}

function atualizaPainel() {
  PainelPlacarSetor = (PainelPlacarSetor) ? PainelPlacarSetor : '';
  PainelPlacarMes = (PainelPlacarMes) ? PainelPlacarMes : '';
  PainelPlacarAno = (PainelPlacarAno) ? PainelPlacarAno : '';
  clearInterval(PainelIntervalo);
  PainelIntervalo = setInterval("RDSCarregar('painel', 'rds.js.php?m=painel&ps=" + PainelPlacarSetor + "&pm=" + PainelPlacarMes + "&pa=" + PainelPlacarAno + "')", 60000);
}

function verDominio (dominio) {
  if (dominio) {
    abrir("dominio", "?m=dominios&a=ver&i=" + dominio, 450, 450, 0);
  }
}

function subtraiDatas(data_maior, data_menor) {
  data_maior = data_maior.split("/");
  data_menor = data_menor.split("/");
  data_maior = new Date(parseInt(data_maior[2], 10), parseInt(data_maior[1], 10) - 1, parseInt(data_maior[0], 10));
  data_menor = new Date(parseInt(data_menor[2], 10), parseInt(data_menor[1], 10) - 1, parseInt(data_menor[0], 10));
  return Math.round((data_maior - data_menor) / 864e5);
}

function somaDias(data, dias) {
  data = data.split("/");
  data = new Date(parseInt(data[2], 10), parseInt(data[1], 10) - 1, parseInt(data[0], 10));
  data.setDate(data.getDate() + parseInt(dias));
  return ((data.getDate() < 10 ? "0" + data.getDate() : data.getDate()) + "/" + (data.getMonth() + 1 < 10 ? "0" + (data.getMonth() + 1) : data.getMonth() + 1) + "/" + data.getFullYear());
}

function subtraiDias(data, dias) {
  data = data.split("/");
  data = new Date(parseInt(data[2], 10), parseInt(data[1], 10) - 1, parseInt(data[0], 10));
  data.setDate(data.getDate() - parseInt(dias));
  return ((data.getDate() < 10 ? "0" + data.getDate() : data.getDate()) + "/" + (data.getMonth() + 1 < 10 ? "0" + (data.getMonth() + 1) : data.getMonth() + 1) + "/" + data.getFullYear());
}

function mudaPossuiEmail (campo) {
  campo.form.email.disabled = (campo.checked) ? true : false;
}

var mailing_sistema_janela;

function chamarMailingSistemaJanela () {
  if (mailing_sistema_janela != null) mailing_sistema_janela.close();
  mailing_sistema_janela = window.open('', "mailing_sistema");
}

function abrirMailingSistema () {
  url = "?m=mailing_sistema&a=operar";
  parametros = "top,left,width=" + (screen.availWidth-10) + ",height=" + (screen.availHeight-30) + "statusbar=0,toolbar=0,scrollbars=0,resizable=1";
  if (mailing_sistema_janela != null) mailing_sistema_janela.close();
  mailing_sistema_janela = window.open(url, "mailing_sistema",parametros);
}

function mudaCondominioNumero (campo) {
  if (document.getElementById("alertas") != null)  verificaCondominiosMesmoEndereco(campo.form);
}

function abrirRecuperacaoSistema () {
  url = "?m=recuperacao_sistema&a=operar";
  parametros = "top,left,width=" + (screen.availWidth-10) + ",height=" + (screen.availHeight-30) + "statusbar=0,toolbar=0,scrollbars=0,resizable=1";
  if (mailing_sistema_janela != null) mailing_sistema_janela.close();
  mailing_sistema_janela = window.open(url, "recuperacao_sistema",parametros);
}

function abrirOperacaoSistema () {
  url = "?m=operacoes_sistema&a=operar";
  parametros = "top,left,width=" + (screen.availWidth-10) + ",height=" + (screen.availHeight-30) + "statusbar=0,toolbar=0,scrollbars=0,resizable=1";
  operacoes_sistema_janela = window.open(url, "operacoes_sistema", parametros);
}

function abrirChamadosSistema(chamado, cliente, condominio) {
  chamado = chamado ? chamado : '0';
  cliente = cliente ? cliente : '0';
  condominio = condominio ? condominio : '0';
  url = "?m=chamados_sistema&a=operar&chamado=" + chamado + "&cliente=" + cliente + "&condominio=" + condominio;
  parametros = "top,left,width=" + (screen.availWidth-10) + ",height=" + (screen.availHeight-30) + "statusbar=0,toolbar=0,scrollbars=0,resizable=1";
  chamados_sistema_janela = window.open(url, "_blank", parametros);
}

function abrirPlacarComercial() {
  url = "?m=placar&a=ver_comercial";
  parametros = "top,left,width=" + (screen.availWidth-10) + ",height=" + (screen.availHeight-30) + "statusbar=0,toolbar=0,scrollbars=0,resizable=1";
  placar_comercial_janela = window.open(url, "_blank", parametros);
}

function mudaClienteCobrancaFormato(campo) {
  cobranca_formato = campo.value.substr(0, 1);
  if (cobranca_formato != 4) {
    if (campo.form.cobranca_debito_operacao) {
      campo.form.cobranca_debito_operacao.disabled = true;
    }
    if (campo.form.cobranca_debito_agencia) {
      campo.form.cobranca_debito_agencia.disabled = true;
    }
    if (campo.form.cobranca_debito_conta) {
      campo.form.cobranca_debito_conta.disabled = true;
    }
  } else {
    if (campo.value.substr(2) == 4 || campo.value.substr(2) == 18 || campo.value.substr(2) == 23) { // CEF
      if (campo.form.cobranca_debito_operacao) {
        campo.form.cobranca_debito_operacao.disabled = false;
      }
    } else {
      if (campo.form.cobranca_debito_operacao) {
        campo.form.cobranca_debito_operacao.disabled = true;
      }
    }
    if (campo.form.cobranca_debito_agencia) {
      campo.form.cobranca_debito_agencia.disabled = false;
    }
    if (campo.form.cobranca_debito_conta) {
      campo.form.cobranca_debito_conta.disabled = false;
    }
  }
  campo.form.cobranca_debito_operacao.value = '';
  campo.form.cobranca_debito_agencia.value = '';
  campo.form.cobranca_debito_conta.value = '';
}

function mudaCondominial(campo) {
  if (campo.value != '') {
    if (campo.form.cobranca_vencimento) {
      campo.form.cobranca_vencimento.disabled = true;
      campo.form.cobranca_vencimento.value = '';
    }
    if (campo.form.cobranca_formato) {
      campo.form.cobranca_formato.disabled = true;
      campo.form.cobranca_formato.value = '';
    }
    if (campo.form.cobranca_debito_agencia) {
      campo.form.cobranca_debito_agencia.disabled = true;
      campo.form.cobranca_debito_agencia.value = '';
    }
    if (campo.form.cobranca_debito_conta) {
      campo.form.cobranca_debito_conta.disabled = true;
      campo.form.cobranca_debito_conta.value = '';
    }
  } else {
    if (campo.form.cobranca_vencimento) {
      campo.form.cobranca_vencimento.disabled = false;
    }
    if (campo.form.cobranca_formato) {
      campo.form.cobranca_formato.disabled = false;
    }
  }
}

function adicionarClienteRetorno(cliente) {
  if (cliente) {
    abrir("cliente_retorno", "?m=clientes_retornos&a=adicionar&cli=" + cliente, 450, 450, 0);
  }
}

function abrirJanela (url, target) {
  eval('WA'+target+' = window.open(url, target); WA' + target + '.focus();');
}

function naoEhTagA(event) {
  if (navigator.appName.indexOf("Netscape") != -1) {
    return (event.target.tagName != "A");
  } else {
    return (event.srcElement.tagName != "A");
  }
}

function naoEhCheckBox(event) {
  if (navigator.appName.indexOf("Netscape") != -1) {
    return (event.target.tagName != "INPUT");
  } else {
    return (event.srcElement.tagName != "INPUT");
  }
}

function mudaComissaoValorTipo(campo) {
  if (campo.name == 'instalacao_responsabilidade_tipo') {
    document.getElementById("instalacao_responsabilidade").value = '';
  } else if (campo.name == 'instalacao_responsabilidade_tipo_ativa') {
    document.getElementById("instalacao_responsabilidade_ativa").value = '';
  } else if (campo.name == 'mensalidade_responsabilidade_tipo') {
    document.getElementById("mensalidade_responsabilidade").value = '';
  } else if (campo.name == 'mensalidade_responsabilidade_tipo_ativa') {
    document.getElementById("mensalidade_responsabilidade_ativa").value = '';
  } else if (campo.name == 'instalacao_indicacao_tipo') {
    document.getElementById("instalacao_indicacao").value = '';
  } else if (campo.name == 'mensalidade_indicacao_tipo') {
    document.getElementById("mensalidade_indicacao").value = '';
  } else if (campo.name == 'instalacao_bonificacao_tipo') {
    document.getElementById("instalacao_bonificacao").value = '';
  } else if (campo.name == 'mensalidade_bonificacao_tipo') {
    document.getElementById("mensalidade_bonificacao").value = '';
  }
}

function abrir(nome, url, largura, altura, scrollbars, posX, posY) {
  if (!posX) posX = Math.ceil((screen.width - largura) / 2);
  if (!posY) posY = Math.ceil((screen.height - altura) / 2);
  if (ehNetscape()) {
    parametros = "width=" + largura + ",height=" + altura + ",screenX=" + posX + ",screenY=" + posY;
  } else {
    parametros = "width=" + largura + ",height=" + altura + ",left=" + posX + ",top=" + posY;
  }
  if (scrollbars) parametros += ",scrollbars=yes";
  janela = window.open(url, nome, parametros);
  janela.focus();
}

function ehNetscape() {
  return (navigator.appName.indexOf("Netscape") != -1);
}

function mudaAgrupamento(campo) {
  if (document.getElementById('campos[agrupamento_tipo]').checked || document.getElementById('campos[agrupamento_servico_plano]').checked) {
    if (campo.name == 'campos[agrupamento_tipo]') {
      document.getElementById('campos[agrupamento_servico_plano]').checked = false;
      document.getElementById('campos[comissao_tipo]').checked = true;
      document.getElementById('campos[servico_plano]').checked = false;
    } else if (campo.name == 'campos[agrupamento_servico_plano]') {
      document.getElementById('campos[agrupamento_tipo]').checked = false;
      document.getElementById('campos[comissao_tipo]').checked = false;
      document.getElementById('campos[servico_plano]').checked = true;
    }
    // Seleciona
    document.getElementById('campos[comissionado]').checked = true;
    document.getElementById('campos[comissao_valor]').checked = true;
    // Tira Sele��o
    document.getElementById('campos[geracao]').checked = false;
    document.getElementById('campos[cliente]').checked = false;
    document.getElementById('campos[condominio]').checked = false;
    document.getElementById('campos[contrato]').checked = false;
    document.getElementById('campos[contrato_status]').checked = false;
    document.getElementById('campos[vendedor]').checked = false;
    document.getElementById('campos[fechamento]').checked = false;
    document.getElementById('campos[ativacao]').checked = false;
    document.getElementById('campos[pontuacao]').checked = false;
    // Desativa
    document.getElementById('campos[comissionado]').disabled = true;
    document.getElementById('campos[comissao_tipo]').disabled = true;
    document.getElementById('campos[comissao_valor]').disabled = true;
    document.getElementById('campos[geracao]').disabled = true;
    document.getElementById('campos[cliente]').disabled = true;
    document.getElementById('campos[condominio]').disabled = true;
    document.getElementById('campos[contrato]').disabled = true;
    document.getElementById('campos[contrato_status]').disabled = true;
    document.getElementById('campos[servico_plano]').disabled = true;
    document.getElementById('campos[vendedor]').disabled = true;
    document.getElementById('campos[fechamento]').disabled = true;
    document.getElementById('campos[ativacao]').disabled = true;
    document.getElementById('campos[pontuacao]').disabled = true;
  } else {
    // Tira Sele��o
    document.getElementById('campos[comissionado]').checked = false;
    document.getElementById('campos[comissao_tipo]').checked = false;
    document.getElementById('campos[comissao_valor]').checked = false;
    document.getElementById('campos[geracao]').checked = false;
    document.getElementById('campos[cliente]').checked = false;
    document.getElementById('campos[condominio]').checked = false;
    document.getElementById('campos[contrato]').checked = false;
    document.getElementById('campos[contrato_status]').checked = false;
    document.getElementById('campos[servico_plano]').checked = false;
    document.getElementById('campos[vendedor]').checked = false;
    document.getElementById('campos[fechamento]').checked = false;
    document.getElementById('campos[ativacao]').checked = false;
    document.getElementById('campos[pontuacao]').checked = false;
    // Ativa
    document.getElementById('campos[comissionado]').disabled = false;
    document.getElementById('campos[comissao_tipo]').disabled = false;
    document.getElementById('campos[comissao_valor]').disabled = false;
    document.getElementById('campos[geracao]').disabled = false;
    document.getElementById('campos[cliente]').disabled = false;
    document.getElementById('campos[condominio]').disabled = false;
    document.getElementById('campos[contrato]').disabled = false;
    document.getElementById('campos[contrato_status]').disabled = false;
    document.getElementById('campos[servico_plano]').disabled = false;
    document.getElementById('campos[vendedor]').disabled = false;
    document.getElementById('campos[fechamento]').disabled = false;
    document.getElementById('campos[ativacao]').disabled = false;
    document.getElementById('campos[pontuacao]').disabled = false;
  }
}

function irPara(campo, evento) {
  tecla = (navigator.appName.indexOf("Netscape") != -1) ? evento.which : evento.keyCode;
  if (tecla == 13) {
    document.getElementById("ir").click();
  }
}

function clicaSubGrupo(subgrupo) {
  var id = 0;
  if (linha = document.getElementById(subgrupo + "_" + id)) {
    var visivel = linha.style.display != "none";
    if (botao = document.getElementById("botao_subgrupo_" + subgrupo)) {
      if (visivel) {
        botao.src = "imagens/adicionar.gif";
      } else {
        botao.src = "imagens/reduzir.gif";
      }
    }
    while (linha = document.getElementById(subgrupo + "_" + (id++))) {
      if (visivel) {
        linha.style.display = "none";
      } else {
        linha.style.display = ehNetscape() ? "table-row" : "block";
      }
    }
  }
}

function clicaGrupo(grupo_nome) {
  if (grupo_nome.substr(0, 10) == "agrupador_") {
    var id = 1;
    while (grupo = document.getElementById(grupo_nome + "_" + id)) {
      if (grupo.style.display == "none") {
        grupo.style.display = ehNetscape() ? "table-row-group" : "block";
        if (botao = document.getElementById("botao_" + grupo_nome)) {
          botao.src = "imagens/reduzir.gif";
        }
      } else {
        grupo.style.display = "none";
        if (grupo_agrupador = document.getElementById(grupo.id.substr(10))) {
          if (grupo_agrupador.style.display != "none") {
            clicaGrupo(grupo_agrupador.id);
          }
        }
        if (botao = document.getElementById("botao_" + grupo_nome)) {
          botao.src = "imagens/adicionar.gif";
        }
      }
      id++;
    }
  } else {
    if (grupo = document.getElementById(grupo_nome)) {
      if (grupo.style.display == "none") {
        grupo.style.display = ehNetscape() ? "table-row-group" : "block";
        if (botao = document.getElementById("botao_agrupador_" + grupo.id)) {
          botao.src = "imagens/reduzir.gif";
        }
      } else {
        grupo.style.display = "none";
        if (botao = document.getElementById("botao_agrupador_" + grupo.id)) {
          botao.src = "imagens/adicionar.gif";
        }
      }
    }
  }
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
  	do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
		return [curleft, curtop];
  }
}
