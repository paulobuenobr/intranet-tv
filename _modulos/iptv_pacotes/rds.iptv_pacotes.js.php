<?
if ($_GET["a"]) {
	global $bancodedados;
} else {

	if (false) {
		?><script type="text/javascript"><? }
	?>

	function validaIptvPacote(formulario) {

		var erro_vazio = new Array();
		var erro_invalido = new Array();

		if (!formulario.nome.value) {
			erro_vazio[erro_vazio.length] = "Nome";
		}

		if (!formulario.codigo.value) {
			erro_vazio[erro_vazio.length] = "Código";
		}

		if (!formulario.tipo.value) {
			erro_vazio[erro_vazio.length] = "Tipo";
		}

		return apresentaErros(erro_vazio, erro_invalido);

	}

	<? if (false) { ?></script><?
	}
}
?>
