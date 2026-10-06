function validaDocumentosAdicionar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.nome.value)
		erro_vazio[erro_vazio.length] = "Nome";
  if (!formulario.tipo.value)
		erro_vazio[erro_vazio.length] = "Tipo";
  if (!formulario.categoria.value)
		erro_vazio[erro_vazio.length] = "Categoria";
  if (formulario.tipo.value == '1') {
		if(!formulario.arquivo.value)
		  erro_vazio[erro_vazio.length] = "Arquivo";
  }else {
    if (!formulario.conteudo.value)
		  erro_vazio[erro_vazio.length] = "Conteúdo";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaDocumentosPerguntar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.pergunta.value) erro_vazio[erro_vazio.length] = "Pergunta";

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaDocumentosResponder(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.resposta.value) erro_vazio[erro_vazio.length] = "Resposta";

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaDocumentosEditar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.nome.value)
		erro_vazio[erro_vazio.length] = "Nome";
  if (!formulario.categoria.value)
		erro_vazio[erro_vazio.length] = "Categoria";
  if (document.getElementById("tipo_conteudo").style.display != 'none') {
    if (!formulario.conteudo.value)
		  erro_vazio[erro_vazio.length] = "Conteúdo";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

function mudaTipo(valor) {
  if(document.getElementById('tipo').value == 1) {
    document.getElementById('tipo_conteudo').style.display = "none";
    document.getElementById('tipo_arquivo').style.display = (ehNetscape() ? 'table-row-group' : 'block');
  } else {
    document.getElementById('tipo_conteudo').style.display = (ehNetscape() ? 'table-row-group' : 'block');
    document.getElementById('tipo_arquivo').style.display = "none";
  }
}