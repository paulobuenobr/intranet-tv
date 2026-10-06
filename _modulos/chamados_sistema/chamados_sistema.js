var data = new Date();
//if (window.atualizaPainel) var atualiza_painel = setInterval('atualizaPainel()', (60 - data.getSeconds()) * 1000);
if (window.atualizaTela) var atualiza_tela = setInterval('atualizaTela()', 60000);

function redefineAtualizaTela() {
  clearInterval(atualiza_tela);
  atualiza_tela = setInterval('atualizaTela()', 60000);
}

function pressionaTecla(evento) {
  if (executaComando(evento)) {
    if (!ehNetscape()) {
      evento.keyCode = null;
    }
    return false;
  } else {
    return true;
  }
}

function executaComando(evento) {
  var tecla = ehNetscape() ? evento.which : evento.keyCode;
  var objeto = ehNetscape() ? evento.target : evento.srcElement;
  if (objeto.type != "button" && objeto.type != "text" && objeto.type != "textarea" && objeto.type != "select" && objeto.type != "password" && objeto.type != "checkbox" && objeto.type != "radio" && objeto.type != "file") {
    if (tecla == 8) { // Voltar
      navegacaoIr('-1');
      return true;
    }
  }
  if (document.getElementById('conteudo') == objeto) {
    if (evento.ctrlKey && tecla == 76) { // Limpar
      document.getElementById('limpar').click();
      return true;
    }
    if (evento.ctrlKey && tecla == 78) { // Negrito
      document.getElementById('negrito').click();
      return true;
    }
    if (evento.ctrlKey && tecla == 73) { // Itálico
      document.getElementById('italico').click();
      return true;
    }
    if (evento.ctrlKey && tecla == 83) { // Sublinhado
      document.getElementById('sublinhado').click();
      return true;
    }
    if (evento.ctrlKey && tecla == 68) { // Destaque
      document.getElementById('destaque').click();
      return true;
    }
  }
  if (tecla == 113 && (cliente_atual != '0' || condominio_atual != '0')) { // F2
    adicionarChamado();
    return true;
  } else if (!evento.altKey && tecla == 115) { // F4
    adicionarChamadoInterno();
    return true;
  } else if (tecla == 116) { // F5
    if (evento.ctrlKey) {
      history.go(0);
    } else {
      navegacaoIr(0);
    }
    return true;
  } else if (tecla == 117) { // F6
    mostrarBuscaChamados();
    return true;
  } else if (tecla == 118) { // F7
    mostrarBuscaClientes();
    return true;
  } else if (tecla == 119) { // F8
    mostrarBuscaCondominios();
    return true;
  } else if (tecla == 120) { // F9
    mostrarMeusChamados();
    return true;
  } else if (tecla == 121) { // F10
    mostrarFavoritos();
    return true;
  } else if (evento.ctrlKey && tecla == 107) { // Ctrl + Aumentar Fonte
    alteraTamanhoFonte(+1);
    return true;
  } else if (evento.ctrlKey && tecla == 109) { // Ctrl - Diminuir Fonte
    alteraTamanhoFonte(-1);
    return true;
  } else if (evento.ctrlKey && tecla == 72) { // Ctrl H Ocultar/Exibir Histórico
    recolherHistorico();
    return true;
  } else if (evento.ctrlKey && tecla == 34 && document.getElementById('proxima_pagina')) { // Ctrl PageDown - Próxima Página
    mudaPagina(0, +1);
    return true;
  } else if (evento.ctrlKey && tecla == 33 && document.getElementById('pagina_anterior')) { // Ctrl PageUp - Página Anterior
    mudaPagina(0, -1);
    return true;
  }
  return false;
}

function redimensionaDivs() {
  if (document.getElementById('dados_chamado')) {
    //if (cliente_atual == '0' && condominio_atual == '0') {
    //  document.getElementById('tabela_resumo').style.height = 70;
    //}
    document.getElementById('bloqueio').style.width = document.body.clientWidth;
    document.getElementById('bloqueio').style.height = document.body.clientHeight;
    document.getElementById('carregando').style.left = document.body.clientWidth - 80;
    document.getElementById('coluna_2').style.height = document.body.clientHeight - 10;
    if (cliente_atual != '0' || condominio_atual != '0') {
	    document.getElementById('dados_chamado').style.height = document.body.clientHeight - document.getElementById('tabela_resumo').clientHeight - 78;
	    document.getElementById('tabela_chamado').style.height = document.body.clientHeight - document.getElementById('tabela_resumo').clientHeight - 58;
    } else {
	    document.getElementById('dados_chamado').style.height = document.body.clientHeight - 65;
	    document.getElementById('tabela_chamado').style.height = document.body.clientHeight - 45;
    }
    var offset = 0
    if (document.getElementById('asterisk_mensagem')) offset += 27;
    if (painel_historico && (cliente_atual != '0' || condominio_atual != '0')) {
      document.getElementById('tabela_busca').style.height = '100%';
      if (aba_busca_atual == 'meus_chamados' || aba_busca_atual == 'favoritos') {
        document.getElementById('coluna_busca').style.height = (document.body.clientHeight - document.getElementById('cabecalho').clientHeight - 20) / 2;
        document.getElementById('busca_resultado_listagem').style.height = (document.body.clientHeight - document.getElementById('cabecalho').clientHeight - 20) / 2 - 49;
      } else {
        document.getElementById('coluna_busca').style.height = 323;
        document.getElementById('busca_resultado_listagem').style.height = 274;
      }

      document.getElementById('seta_historico').src = 'imagens/seta_down.gif';
      document.getElementById('historico_conteudo').style.display = ehNetscape() ? 'table-row-group' : 'block';
      document.getElementById('historico_listagem').style.height = document.body.clientHeight - document.getElementById('cabecalho').clientHeight - document.getElementById('coluna_busca').clientHeight - 72;
    } else {
      document.getElementById('seta_historico').src = 'imagens/seta_up.gif';
      document.getElementById('coluna_historico').style.height = 25;
      document.getElementById('historico_conteudo').style.display = 'none';

      document.getElementById('tabela_busca').style.height = '100%';
      document.getElementById('coluna_busca').style.height = document.body.clientHeight - document.getElementById('coluna_historico').clientHeight - document.getElementById('cabecalho').clientHeight - 20;
      document.getElementById('busca_resultado_listagem').style.height = document.body.clientHeight - document.getElementById('coluna_historico').clientHeight - document.getElementById('cabecalho').clientHeight - 69;
    }
    document.getElementById('tabela_geral').style.height = document.body.clientHeight;
  }
}

function atualizaPNG() {
  if (ehNetscape()) return;
  var arVersion = navigator.appVersion.split("MSIE");
  var version = parseFloat(arVersion[1]);
  if ((version >= 5.5) && (document.body.filters)) {
    for(var i = 0; i < document.images.length; i++) {
      var img = document.images[i];
      var imgName = img.src.toUpperCase();
      if (imgName.substring(imgName.length-3, imgName.length) == "PNG") {
        var imgID = (img.id) ? "id='" + img.id + "' " : "";
        var imgClass = (img.className) ? "class='" + img.className + "' " : "";
        var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' ";
        var imgStyle = "display:inline-block;" + img.style.cssText;
        if (img.align == "left") imgStyle = "float:left;" + imgStyle;
        if (img.align == "right") imgStyle = "float:right;" + imgStyle;
        if (img.parentElement.href) imgStyle = "cursor:pointer;" + imgStyle;
        var strNewHTML = "<span " + imgID + imgClass + imgTitle
        + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
        + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
        + "(src=\'" + img.src + "\', sizingMethod='image');\"></span>";
        img.outerHTML = strNewHTML;
        i = i - 1;
      }
    }
  }
}

function buscaEstilo(estilo) {
  var meuStyleSheet = document.styleSheets[0];
  var meusEstilos = meuStyleSheet.cssRules ? meuStyleSheet.cssRules : meuStyleSheet.rules;
  for (i = 0; i < meusEstilos.length; i++) {
    if (meusEstilos[i].selectorText.toLowerCase() == estilo.toLowerCase()){
      return meusEstilos[i];
    }
  }
}

function alteraTamanhoFonte(incremento) {
  var estilos = new Array('.txtAzulEscuro11');
  for (i = 0; i < estilos.length; i++) {
    if (estilo = buscaEstilo(estilos[i])) {
      estilo.style.fontSize = (parseFloat(estilo.style.fontSize.substr(0, estilo.style.fontSize.indexOf('px'))) + incremento) + 'px';
    }
  }
}

function novaJanela() {
  var url = "?m=chamados_sistema&a=operar&nova_instancia=1";
  var parametros = "top,left,width=" + (screen.availWidth - 10) + ",height=" + (screen.availHeight - 30) + "statusbar=0,toolbar=0,scrollbars=0,resizable=1";
  window.open(url, "_blank", parametros);
}

function exibindoPainelCarregando() {
  if (document.getElementById('carregando')) {
    if (document.getElementById('carregando').style.display == 'none') {
      return false;
    } else {
      return true;
    }
  } else {
    return false;
  }
}

function exibePainelCarregando(bloqueio) {
  if (document.getElementById('carregando')) {
    atualizando = true;
    document.getElementById('carregando').style.display = 'block';
    if (bloqueio) {
      document.getElementById('bloqueio').style.display = 'block';
    }
  }
}

function ocultaPainelCarregando() {
  if (document.getElementById('carregando')) {
    document.getElementById('carregando').style.display = 'none';
    document.getElementById('bloqueio').style.display = 'none';
  }
}

/////////////////////////////
/// Mostra Busca Chamados ///
/////////////////////////////

function mostrarBuscaChamados() {
  // Define Aba Atual
  aba_busca_atual = 'chamados';
  // Oculta/exibe formulários de busca
  document.getElementById('busca_clientes_formulario').style.display = 'none';
  document.getElementById('busca_condominios_formulario').style.display = 'none';
  if (document.getElementById('busca_chamados_formulario').style.display == 'none') {
    document.getElementById('busca_chamados_formulario').style.display = ehNetscape() ? 'table-row-group' : 'block';
    document.getElementById('busca_resultado').style.display = 'none';
    document.getElementById('busca_chamados_aba').bgColor = '#505050';
  }
  // Deseleciona as outras abas
  document.getElementById('busca_clientes_aba').bgColor = '#828282';
  document.getElementById('busca_condominios_aba').bgColor = '#828282';
  document.getElementById('meus_chamados_aba').bgColor = '#828282';
  document.getElementById('favoritos_aba').bgColor = '#828282';
  // Seta Foco
  //document.getElementById('chamados_codigo').focus();
  redimensionaDivs();
  // Registra Tela
  registraTela();
}

/////////////////////////////
/// Mostra Busca Clientes ///
/////////////////////////////

function mostrarBuscaClientes() {
  // Define Aba Atual
  aba_busca_atual = 'clientes';
  // Oculta/exibe formulários de busca
  document.getElementById('busca_condominios_formulario').style.display = 'none';
  document.getElementById('busca_chamados_formulario').style.display = 'none';
  if (document.getElementById('busca_clientes_formulario').style.display == 'none') {
    document.getElementById('busca_clientes_formulario').style.display = ehNetscape() ? 'table-row-group' : 'block';
    document.getElementById('busca_resultado').style.display = 'none';
    document.getElementById('busca_clientes_aba').bgColor = '#505050';
  }
  // Deseleciona as outras abas
  document.getElementById('busca_condominios_aba').bgColor = '#828282';
  document.getElementById('busca_chamados_aba').bgColor = '#828282';
  document.getElementById('meus_chamados_aba').bgColor = '#828282';
  document.getElementById('favoritos_aba').bgColor = '#828282';
  // Seta Foco
  //document.getElementById('clientes_id').focus();
  redimensionaDivs();
  // Registra Tela
  registraTela();
}

////////////////////////////////
/// Mostra Busca Condomínios ///
////////////////////////////////

function mostrarBuscaCondominios() {
  // Define Aba Atual
  aba_busca_atual = 'condominios';
  // Oculta/exibe formulários de busca
  document.getElementById('busca_chamados_formulario').style.display = 'none';
  document.getElementById('busca_clientes_formulario').style.display = 'none';
  if (document.getElementById('busca_condominios_formulario').style.display == 'none') {
    document.getElementById('busca_condominios_formulario').style.display = ehNetscape() ? 'table-row-group' : 'block';
    document.getElementById('busca_resultado').style.display = 'none';
    document.getElementById('busca_condominios_aba').bgColor = '#505050';
  }
  // Deseleciona as outras abas
  document.getElementById('busca_chamados_aba').bgColor = '#828282';
  document.getElementById('busca_clientes_aba').bgColor = '#828282';
  document.getElementById('meus_chamados_aba').bgColor = '#828282';
  document.getElementById('favoritos_aba').bgColor = '#828282';
  // Seta Foco
  //document.getElementById('condominios_nome').focus();
  redimensionaDivs();
  // Registra Tela
  registraTela();
}

function buscaDadosFiltroChamados(pagina) {
  dados_filtro = new Array();
  dados_filtro[dados_filtro.length] = 'dados_filtro[tipo]=' + escape(document.getElementById('chamados_tipo').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[conteudo]=' + escape(document.getElementById('chamados_conteudo').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[cliente]=' + escape(document.getElementById('chamados_cliente').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[condominio]=' + escape(document.getElementById('chamados_condominio').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[categoria]=' + escape(document.getElementById('chamados_categoria').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[responsavel_tipo]=' + escape(document.getElementById('chamados_responsavel_tipo').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[responsavel]=' + escape(document.getElementById('chamados_responsavel').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_tipo]=' + escape(document.getElementById('chamados_data_tipo').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_inicio]=' + escape(document.getElementById('chamados_data_inicio').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_termino]=' + escape(document.getElementById('chamados_data_termino').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[status]=' + escape(document.getElementById('chamados_status').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[pagina]=' + (pagina ? pagina : 1);
  return dados_filtro.join('&');
}

function buscaDadosFiltroChamadosIr() {
  dados_filtro = new Array();
  dados_filtro[dados_filtro.length] = 'dados_filtro[codigo]=' + escape(document.getElementById('chamados_codigo').value);
  return dados_filtro.join('&');
}

function buscaDadosFiltroClientes(pagina) {
  dados_filtro = new Array();
  dados_filtro[dados_filtro.length] = 'dados_filtro[nome]=' + escape(document.getElementById('clientes_nome').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[condominio]=' + escape(document.getElementById('clientes_condominio').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[bloco]=' + escape(document.getElementById('clientes_bloco').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[complemento]=' + escape(document.getElementById('clientes_complemento').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[tipo]=' + escape(document.getElementById('clientes_tipo').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[cpf_cnpj]=' + escape(document.getElementById('clientes_cpf_cnpj').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[email]=' + escape(document.getElementById('clientes_email').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[telefone]=' + escape(document.getElementById('clientes_telefone').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[status]=' + escape(document.getElementById('clientes_status').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[pagina]=' + (pagina ? pagina : 1);
  return dados_filtro.join('&');
}

function buscaDadosFiltroClientesIr() {
  dados_filtro = new Array();
  dados_filtro[dados_filtro.length] = 'dados_filtro[id]=' + escape(document.getElementById('clientes_id').value);
  return dados_filtro.join('&');
}

function buscaDadosFiltroCondominios(pagina) {
  dados_filtro = new Array();
  dados_filtro[dados_filtro.length] = 'dados_filtro[nome]=' + escape(document.getElementById('condominios_nome').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[cidade]=' + escape(document.getElementById('condominios_cidade').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[bairro]=' + escape(document.getElementById('condominios_bairro').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[logradouro]=' + escape(document.getElementById('condominios_logradouro').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[responsavel]=' + escape(document.getElementById('condominios_responsavel').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_cadastro_inicio]=' + escape(document.getElementById('condominios_data_cadastro_inicio').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_cadastro_termino]=' + escape(document.getElementById('condominios_data_cadastro_termino').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_ativacao_inicio]=' + escape(document.getElementById('condominios_data_ativacao_inicio').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[data_ativacao_termino]=' + escape(document.getElementById('condominios_data_ativacao_termino').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[tipo]=' + escape(document.getElementById('condominios_tipo').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[status]=' + escape(document.getElementById('condominios_status').value);
  dados_filtro[dados_filtro.length] = 'dados_filtro[pagina]=' + (pagina ? pagina : 1);
  return dados_filtro.join('&');
}

function verDados(aba) {
	if (cliente_atual != '0') {
		verDadosCliente(aba);
	} else if (condominio_atual != '0') {
		verDadosCondominio(aba);
	}
}

function verDadosCliente(aba) {
  if (window.opener) {
    window.opener.location = "?m=clientes&a=ver_" + aba + "&i=" + cliente_atual;
    //window.opener.focus();
  } else {
    url = "?m=clientes&a=ver_" + aba + "&i=" + cliente_atual;
    window.open(url, "dados_cliente");
  }
}

function verDadosServidor(servidor) {
  if (window.opener) {
    window.opener.location = "?m=servidores&a=ver_dc&i=" + servidor;
  } else {
    url = "?m=servidores&a=ver_dc&i=" + servidor;
    window.open(url, "dados_servidor");
  }
}

function verDadosCondominio(aba) {
  if (window.opener) {
    window.opener.location = "?m=condominios&a=ver_" + aba + "&i=" + condominio_atual;
  } else {
    url = "?m=condominios&a=ver_" + aba + "&i=" + condominio_atual;
    window.open(url, "dados_condominio");
  }
}

function editarContatos() {
  abrir("cliente_contatos", "?m=clientes&a=editar_ct&i=" + cliente_atual, 420, 400, 1);
}

function limparFormulario(formulario) {
  if (formulario == 'buscarChamados') {
    document.getElementById('chamados_tipo').value = '';
    document.getElementById('chamados_conteudo').value = '';
    document.getElementById('chamados_cliente').value = '';
    document.getElementById('chamados_condominio').value = '';
    document.getElementById('chamados_categoria').value = '';
    document.getElementById('chamados_responsavel_tipo').value = '1';
    document.getElementById('chamados_responsavel').value = '';
    document.getElementById('chamados_data_tipo').value = '1';
    document.getElementById('chamados_data_inicio').value = '';
    document.getElementById('chamados_data_termino').value = '';
    document.getElementById('chamados_status').value = '';
    mudaChamadoTipo('');
  } else if (formulario == 'buscarChamadosIr') {
    document.getElementById('chamados_codigo').value = '';
  } else if (formulario == 'buscarClientes') {
    document.getElementById('clientes_nome').value = '';
    document.getElementById('clientes_condominio').value = '';
    document.getElementById('clientes_bloco').value = '';
    document.getElementById('clientes_bloco').disabled = true;
    document.getElementById('clientes_complemento').value = '';
    document.getElementById('clientes_tipo').value = '';
    document.getElementById('clientes_cpf_cnpj').value = '';
    document.getElementById('clientes_email').value = '';
    document.getElementById('clientes_telefone').value = '';
    document.getElementById('clientes_status').value = '';
  } else if (formulario == 'buscarClientesIr') {
    document.getElementById('clientes_id').value = '';
  } else if (formulario == 'buscarCondominios') {
    document.getElementById('condominios_nome').value = '';
    document.getElementById('condominios_cidade').value = '';
    document.getElementById('condominios_bairro').value = '';
    document.getElementById('condominios_logradouro').value = '';
    document.getElementById('condominios_responsavel').value = '';
    document.getElementById('condominios_data_cadastro_inicio').value = '';
    document.getElementById('condominios_data_cadastro_termino').value = '';
    document.getElementById('condominios_data_ativacao_inicio').value = '';
    document.getElementById('condominios_data_ativacao_termino').value = '';
    document.getElementById('condominios_tipo').value = '';
    document.getElementById('condominios_status').value = '';
  }
}

function formataClienteCPFCNPJ(campo, evento) {
  if (document.getElementById('clientes_tipo').value == 2) {
    return formataCNPJ(campo, evento);
  } else {
    return formataCPF(campo, evento);
  }
}

function mudaChamadoClienteTipo(campo) {
  if (campo.value == 2) {
    document.getElementById('cpf_cnpj_label').innerHTML = '&nbsp;CNPJ:';
  } else {
    document.getElementById('cpf_cnpj_label').innerHTML = '&nbsp;CPF:';
  }
}

function mudaChamadoTipo(valor) {
  document.getElementById('chamados_cliente').disabled = (valor == 2 || valor == 3) ? true : false;
  document.getElementById('chamados_condominio').disabled = (valor == 3) ? true : false;
  if (valor == 2) {
    document.getElementById('chamados_cliente').value = "";
  } else if (valor == 3) {
    document.getElementById('chamados_cliente').value = "";
    document.getElementById('chamados_condominio').value = "";
  }
}

function executarComando(evento, comando) {
  var tecla = ehNetscape() ? evento.which : evento.keyCode;
  if (tecla == 13) {
    eval(comando + '()');
  } else if (tecla == 27) {
    limparFormulario(comando);
  }
}

function validaNovaEtapa() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('tipo').value) erro_vazio[erro_vazio.length] = "Tipo";
  if (!document.getElementById('nivel_stress').value) erro_vazio[erro_vazio.length] = "Nível de Stress";

  if (apresentaErros(erro_vazio, erro_invalido)) {
    return true;
  } else {
    ocultaPainelCarregando();
    abort();
  }
}

function mudaChamadoEtapaTipo(tipo) {
  tipos = new Array(5, 12, 13, 14, 17, 18, 19, 20);
  for (i = 0; i < tipos.length; i++) {
    if (document.getElementById("etapa_conteudo_" + tipos[i])) {
      document.getElementById("etapa_conteudo_" + tipos[i]).style.display = (bucaTipoEtapa(tipos[i]) == bucaTipoEtapa(tipo) ? (ehNetscape() ? 'table-row-group' : 'block') : 'none');
      chamado_alterado = true;
    }
  }
}

function bucaTipoEtapa(tipo_id) {
  if (tipo_id == 5 || tipo_id == 12 || tipo_id == 13 || tipo_id == 14 || tipo_id == 17 || tipo_id == 18 || tipo_id == 19) {
    return 5;
  } else {
    return tipo_id;
  }
}

function buscaDadosSituacao() {
  dados_situacao = new Array();
  dados_situacao[dados_situacao.length] = 'dados_situacao[instancia]=' + instancia;
  dados_situacao[dados_situacao.length] = 'dados_situacao[cliente_atual]=' + cliente_atual;
  dados_situacao[dados_situacao.length] = 'dados_situacao[cliente_condominio_atual]=' + cliente_condominio_atual;
  dados_situacao[dados_situacao.length] = 'dados_situacao[condominio_atual]=' + condominio_atual;
  dados_situacao[dados_situacao.length] = 'dados_situacao[chamado_atual]=' + chamado_atual;
  dados_situacao[dados_situacao.length] = 'dados_situacao[aba_historico_atual]=' + aba_historico_atual;
  dados_situacao[dados_situacao.length] = 'dados_situacao[aba_busca_atual]=' + aba_busca_atual;
  dados_situacao[dados_situacao.length] = 'dados_situacao[chamado_alterado]=' + (chamado_alterado ? '1' : '0');
  return '&' + dados_situacao.join('&');
}

function exibeResumo(aba, registra_tela) {
	/*
  var abas = new Array('dados_resumo', 'dados_contato');
  if (aba == 'dados_resumo') {
    document.getElementById('dados_chamado').style.height = document.body.clientHeight - 135 - 80;
    document.getElementById('tabela_chamado').style.height = document.body.clientHeight - 135 - 60;
    document.getElementById('tabela_resumo').style.height = 135;
  } else {
    document.getElementById('dados_chamado').style.height = document.body.clientHeight - 245 - 80;
    document.getElementById('tabela_chamado').style.height = document.body.clientHeight - 245 - 60;
    document.getElementById('tabela_resumo').style.height = 245;
  }
  for (i = 0; i < abas.length; i++) {
    if (abas[i] == aba) {
      document.getElementById(abas[i] + '_conteudo').style.display = "block";
      document.getElementById(abas[i]).bgColor = '#505050';
    } else {
      document.getElementById(abas[i] + '_conteudo').style.display = "none";
      document.getElementById(abas[i]).bgColor = '#828282';
    }
  }
  // Atualiza Aba Atual
  aba_resumo_atual = aba;
  // Registra Tela
  if (registra_tela) {
    redimensionaDivs();
    registraTela();
  }
  */
}

function atualizaBusca() {
  if (document.getElementById('busca_resultado').style.display != 'none') {
    if (aba_busca_atual == 'chamados') {
      buscarChamados();
    } else if (aba_busca_atual == 'meus_chamados') {
      mostrarMeusChamados(pagina_busca_atual);
    } else if (aba_busca_atual == 'favoritos') {
      mostrarFavoritos(pagina_busca_atual);
    }
  }
}

function verCondominiosContato(contato) {
	if (contato) {
    abrir("contato", "?m=condominios_contatos&a=ver&i=" + contato, 450, 500, 0);
	}
}

function adicionarCondominiosContato(condominio) {
	if (condominio) {
		abrir("contato", "?m=condominios_contatos&a=adicionar&c=" + condominio, 450, 500, 0);
	}
}

function buscaCondominiosContatos(condominio) {
	if (condominio) {
	    exibePainelCarregando(true);
		url = "rds.js.php?m=chamados_sistema&a=busca_condominios_contatos&condominio=" + condominio;
	    RDSCarregar("busca_condominios_contatos", url);
	}
}

function aplicarTag(tag) {
  if (document.getElementById('conteudo')) {
    var tag_valores = new Array();
  	wrapText(document.getElementById('conteudo'), '[' + tag + ']', '[/' + tag + ']');
  	chamado_alterado = true;
  }
}

function limparTags() {
  if (document.getElementById('conteudo')) {
    var texto = buscaSelectedText(document.getElementById('conteudo'));
    var tags = new Array("n", "i", "s", "d", "azul", "verde", "vermelho");
    for (i = 0; i < tags.length; i++) {
      var filtro = new RegExp("\\[" + tags[i] + "\\]" + "([^\\[]*)" + "\\[\\/" + tags[i] + "\\]", "g");
      texto = texto.replace(filtro, "$1");
    }
    replaceSelection(document.getElementById('conteudo'), texto);
  	chamado_alterado = true;
  }
}

function buscaSelectedText(obj) {
  if (obj.setSelectionRange) {
    var selectionStart = obj.selectionStart;
    var selectionEnd = obj.selectionEnd;
    return obj.value.substring(selectionStart, selectionEnd);
  }
}

function wrapText(obj, beginTag, endTag) {
  if (obj.setSelectionRange) {
    var selectionStart = obj.selectionStart;
    var selectionEnd = obj.selectionEnd;
    var selectionText = obj.value.substring(selectionStart, selectionEnd);
    if (selectionText.substr(selectionText.length - 1, 1) == ' ') {
      selectionText = selectionText.substr(0, selectionText.length - 1);
      endTag += ' ';
    }
    obj.value = obj.value.substring(0, selectionStart) +
                beginTag +
                selectionText +
                endTag +
                obj.value.substring(selectionEnd);
    if (selectionStart != selectionEnd) {
      setSelectionRange(obj, selectionStart, selectionStart + beginTag.length + selectionText.length + endTag.length);
    } else {
      setCaretToPos(obj, selectionStart + beginTag.length);
    }
  } else if (document.selection) {
    obj.focus();
    var range = document.selection.createRange();
    if (range.parentElement() == obj) {
      var selectionText = range.text;
      range.text = beginTag + range.text + endTag;
      if (selectionText) {
        range.moveStart('character', -(beginTag.length + selectionText.length + endTag.length));
      } else {
        range.moveStart('character', -(beginTag.length + 1));
        range.moveEnd('character', -(beginTag.length + 1));
      }
      range.select();
    }
  }
}

function setSelectionRange(obj, selectionStart, selectionEnd) {
  if (obj.setSelectionRange) {
    obj.focus();
    obj.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (obj.createTextRange) {
    var range = obj.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}

function setCaretToPos(obj, pos) {
  setSelectionRange(obj, pos, pos);
}

function replaceSelection(obj, replaceString) {
  if (obj.setSelectionRange) {
    var selectionStart = obj.selectionStart;
    var selectionEnd = obj.selectionEnd;
    obj.value = obj.value.substring(0, selectionStart) + replaceString + obj.value.substring(selectionEnd);
    if (selectionStart != selectionEnd) {
      setSelectionRange(obj, selectionStart, selectionStart + replaceString.length);
    } else {
      setCaretToPos(obj, selectionStart + replaceString.length);
    }
  } else if (document.selection) {
    var range = document.selection.createRange();
    if (range.parentElement() == obj) {
      var isCollapsed = range.text == '';
      range.text = replaceString;
      if (!isCollapsed)  {
        range.moveStart('character', -replaceString.length);
        range.select();
      }
    }
  }
}

//var comprimento_maximo = (ehNetscape() ? 8100 : 4000) - 400;
var comprimento_maximo = 3600;

function verificaTamanhoMaximo(evento) {
  if (evento.type == 'blur') {
    var conteudoCortado = unescape(escape(document.getElementById('conteudo').value).substring(0, comprimento_maximo));
//    if (conteudoCortado.substr(-1) == '%') {
//      conteudoCortado = conteudoCortado.substring(0, conteudoCortado.length - 1);
//    } else if (conteudoCortado.substr(-2, 1) == '%') {
//      conteudoCortado = conteudoCortado.substring(0, conteudoCortado.length - 2);
//    }
    document.getElementById('conteudo').value = conteudoCortado;
  } else {
    var tecla = ehNetscape() ? evento.which : evento.keyCode;
    if (ehTeclaValida(tecla) || evento.ctrlKey || evento.altKey) {
      return true;
    } else {
      var comprimento_atual = escape(document.getElementById('conteudo').value).length;
      return (comprimento_maximo > comprimento_atual) ? true : false;
    }
  }
}

function atualizaCaracteresRestantes() {
  if (document.getElementById('conteudo') && document.getElementById('caracteres_restantes')) {
    var comprimento_atual = escape(document.getElementById('conteudo').value).length;
    document.getElementById('caracteres_restantes').innerHTML = 'Caracteres Restantes: ' + (comprimento_maximo >= comprimento_atual ? comprimento_maximo - comprimento_atual : 0);
  }
}

function verChamadoPai(evento) {
  if (document.getElementById('chamado_pai') && document.getElementById('chamado_pai').value) {
    var dados_chamado = document.getElementById('chamado_pai').value.split(',');
    var atualizar_historico = (cliente_atual && cliente_atual == dados_chamado[1]) || (condominio_atual && condominio_atual == dados_chamado[3]) ? "0" : "1";
    verChamado(dados_chamado[0], dados_chamado[1], dados_chamado[2], dados_chamado[3], atualizar_historico, evento);
  }
}

function trataChamadoAlterado(referencia) {
  exibePainelCarregando(true);
  if (window.hintHide) hintHide();
  if (chamado_alterado) {
    if (confirm("Deseja salvar as alterações antes de continuar?")) {
      if (chamado_atual != '0') {
        if (chamado_acao_atual == 'editar_etapa') {
          editarEtapa(chamado_etapa_atual, 1, 0, referencia);
        } else if (chamado_acao_atual == 'acompanhar') {
          executaAcao('acompanhar', 0, referencia);
        } else if (chamado_acao_atual == 'registrar_nova_etapa') {
          executaAcao('registrar_nova_etapa', 0, referencia);
        } else if (chamado_acao_atual == 'fechar_chamado') {
          executaAcao('fechar_chamado', 0, referencia);
        } else {
          editarChamadoSalvar(0, referencia);
        }
      } else {
        adicionarChamadoSalvar(0, referencia);
      }
      if (chamado_atual != '0' && !document.getElementById('chamados_' + chamado_atual) && !document.getElementById('historico_chamados_' + chamado_atual)) {
        chamado_alterado = false;
      }
      return true;
    } else {
      chamado_alterado = false;
      return false;
    }
  }
  return false;
}

function imprimeChamado() {
	if (chamado_atual) {
		parametros = "top,left,width=" + ((screen.availWidth > 760) ? 760 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 50) + "statusbar=no,toolbar=no,scrollbars=yes,resizable=1";
		chamado_imprimir = window.open("?m=chamados&a=imprimir&i=" + chamado_atual, "chamado_imprimir", parametros);
		chamado_imprimir.focus();
	}
}

function agendarVisitaTecnica(){	
    abrir("agendar_visita_tecnica", "?m=visitas&a=agendar_visita_tecnica&i=" + chamado_atual, 1200, 620, 1);
	}

function recolherHistorico() {
  if ((cliente_atual != '0') || (condominio_atual != '0')) {
    // Inverte Status do Painel Histórico
    painel_historico = !painel_historico;
    // Redimensiona Divs
    redimensionaDivs();
    // Registra Tela
    registraTela();
  }
}

function mudaPagina(pagina, referencia) {
  pagina = pagina != 0 ? pagina : pagina_busca_atual + referencia;
  if (aba_busca_atual == 'chamados') {
    buscarChamados(pagina);
  } else if (aba_busca_atual == 'clientes') {
    buscarClientes(pagina);
  } else if (aba_busca_atual == 'condominios') {
    buscarCondominios(pagina);
  } else if (aba_busca_atual == 'meus_chamados') {
    mostrarMeusChamados(pagina);
  } else if (aba_busca_atual == 'favoritos') {
    mostrarFavoritos(pagina);
  }
}

function validaChamadoFechar(encaminhamento) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('andamento').value) erro_vazio[erro_vazio.length] = "Andamento";

  if (apresentaErros(erro_vazio, erro_invalido)) {
    if (document.getElementById('andamento').value < 100 && !encaminhamento) {
      return confirm('Deseja realmente fechar este chamado não concluído - ' + document.getElementById('andamento').value + '% ?');
    } else {
      return true;
    }
  } else {
    return false;
  }
}

function validaChamadoLiberarTecnico() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('mac').value) erro_vazio[erro_vazio.length] = "MAC";

  return apresentaErros(erro_vazio, erro_invalido);
}


function validaSolicitarFechamento() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('responsavel_fechamento').value) erro_vazio[erro_vazio.length] = "Responsável";

  return apresentaErros(erro_vazio, erro_invalido);
}

function selecionaChamado(campo, ordem) {
  if (chamado_atual != 0) {
    verChamado(0, cliente_atual, cliente_condominio_atual, condominio_atual);
  }
  if (ordem == 0) {
    selecionaTodos();
  } else {
    if (document.getElementById('seleciona_chamado_' + ordem).checked) {
      mudaCorLinhaChamado(document.getElementById('seleciona_chamado_' + ordem).value, '#d7e2ee');
    } else {
      mudaCorLinhaChamado(document.getElementById('seleciona_chamado_' + ordem).value, '');
    }
  }
}

function selecionaTodos() {
  var i = 1;
  while (document.getElementById('seleciona_chamado_' + i)) {
    document.getElementById('seleciona_chamado_' + i).checked = document.getElementById('seleciona_chamado_0').checked;
    if (document.getElementById('seleciona_chamado_' + i).checked) {
      mudaCorLinhaChamado(document.getElementById('seleciona_chamado_' + i).value, '#d7e2ee');
    } else {
      mudaCorLinhaChamado(document.getElementById('seleciona_chamado_' + i).value, '');
    }
    i++;
  }
}

function mudaCorLinhaChamado(chamado, cor) {
  if (document.getElementById('chamados_' + chamado)) document.getElementById('chamados_' + chamado).style.backgroundColor = cor;
}

function imprimeChamados() {
  if (buscaChamadosSelecionados() != 0) {
    parametros = "top,left,width=" + ((screen.availWidth > 760) ? 760 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 50) + "statusbar=no,toolbar=no,scrollbars=yes,resizable=1";
  	chamado_imprimir = window.open("?m=chamados&a=imprimir&i=" + buscaChamadosSelecionados(), "chamado_imprimir", parametros);
    chamado_imprimir.focus();
  }
}

function buscaChamadosSelecionados() {
  var i = 1;
  var chamados = new Array();
  while (document.getElementById('seleciona_chamado_' + i)) {
    if (document.getElementById('seleciona_chamado_' + i).checked) {
      chamados[chamados.length] = document.getElementById('seleciona_chamado_' + i).value;
    }
    i++;
  }
  return chamados.length > 0 ? chamados : 0;
}

function executarRoteiros() {
  abrir("executar_roteiros", "?m=chamados_roteiros&a=executar&i=" + cliente_atual, 600, 500, 1);
}

function corrigeConteudo(conteudo) {
  conteudo = conteudo.replace(/\+/g, "%2B");
  return escape(conteudo);
}

function verContrato(contrato) {
	if (contrato) {
    abrir("contrato", "?m=contratos&a=ver&i=" + contrato, 625, 520, 1);
	}
}

function mudaRetorno() {
	if (document.getElementById('retorno').value == 0) {
    document.getElementById('data_hora_retorno').disabled = true;
  } else if (document.getElementById('retorno').value == 2){
	  document.getElementById('data_hora_retorno').value = '';
    document.getElementById('data_hora_retorno').disabled = true;
  }else {
	document.getElementById('data_hora_retorno').disabled = false;
  }
}

function exibeAlertaAgendamento() {
	document.getElementById('alerta_agendamento_visivel_1').style.display = ehNetscape() ? 'table-cell' : 'block';
	document.getElementById('alerta_agendamento_visivel_2').style.display = ehNetscape() ? 'table-cell' : 'block';
}

function ocultaAlertaAgendamento() {
	document.getElementById('alerta_agendamento_visivel_1').style.display = 'none';
	document.getElementById('alerta_agendamento_visivel_2').style.display = 'none';
}

function atualizaDataVisita(chamado_id, data, periodo) {
	if (chamado_atual == chamado_id && document.getElementById('data_visita')) {
		document.getElementById('data_visita').innerHTML = data + " (" + periodo + ")";
	}
}

var janela_grvacao;
function ouvirGravacao(arquivo) {
	window.open("http://asterisk.oletelecom.com.br/monitor/" + arquivo + ".WAV", "_blank");
//	if (janela_grvacao) janela_grvacao.close();
//	janela_grvacao = window.open("", "ouvir", "height=100,width=350,resizable=no,menubar=no,scrollbars=no,status=no,titlebar=no,toolbar=no");
//	janela_grvacao.document.write("<html>");
//	janela_grvacao.document.write("<body>");
//	janela_grvacao.document.write("<object type=\"audio/x-wav\" data=\"http://192.168.170.1/monitor/" + arquivo + ".WAV\" width=\"300\" height=\"50\">");
//	janela_grvacao.document.write("<param name=\"src\" value=\"http://192.168.170.1/monitor/" + arquivo + ".WAV\" />");
//	janela_grvacao.document.write("<param name=\"autoplay\" value=\"true\" />");
//	janela_grvacao.document.write("<param name=\"controlelr\" value=\"true\" />");
//	janela_grvacao.document.write("<param name=\"autoStart\" value=\"0\" />");
//	janela_grvacao.document.write("</object>");
//	janela_grvacao.document.write("</body>");
//	janela_grvacao.document.write("</html>");
//	janela_grvacao.focus();
}
