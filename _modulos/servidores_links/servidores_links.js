function validaLink(formulario) {
  var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.nome.value) {
		erro_vazio[erro_vazio.length] = "Nome";
	}
	if (!formulario.servidor_interface.value) {
		erro_vazio[erro_vazio.length] = "Interface";
	}
	if (!formulario.download.value) {
		erro_vazio[erro_vazio.length] = "Velocidade Download";
	} else
	if (isNaN(formulario.download.value)) {
		erro_invalido[erro_invalido.length] = "Velocidade Download";
	}
	if (!formulario.upload.value) {
		erro_vazio[erro_vazio.length] = "Velocidade Upload";
	} else
	if (isNaN(formulario.upload.value)) {
		erro_invalido[erro_invalido.length] = "Velocidade Upload";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}