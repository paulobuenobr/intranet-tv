<?
if ($_GET["a"]) {
	global $bancodedados;
} else {

	global $bancodedados;

	$ehAdmin = false;
	if (!empty($_SESSION["usuario"]) && isset($bancodedados)) {
		$query = $bancodedados->query("SELECT usu_administrador FROM tab_usuarios WHERE usu_id = " . intval($_SESSION["usuario"]));
		$dadosAdmin = $bancodedados->resultadoArray($query);
		$ehAdmin = !empty($dadosAdmin["usu_administrador"]);
	}

	if (false) {
		?><script type="text/javascript"><?
	}
	?>

	var ehAdminCancelamentoMassa = <?php echo $ehAdmin ? "true" : "false"; ?>;

	function validaCancelamentoMassa(formulario) {

		var erro_vazio = new Array();
		var erro_invalido = new Array();

		if (!temOrigemSelecionada(formulario)) {
			erro_vazio[erro_vazio.length] = "Origens";
		}

		if (!formulario.data_cancelamento.value) {
			erro_vazio[erro_vazio.length] = "Data de Cancelamento (obrigatório)";
		} else if (!ehAdminCancelamentoMassa && formulario.data_cancelamento.value > dataHojeIso()) {
			erro_invalido[erro_invalido.length] = "Data de Cancelamento (não pode ser maior que a data atual)";
		}

		if (!formulario.lista_clientes.value || !formulario.lista_clientes.value.replace(/\s+/g, "")) {
			erro_vazio[erro_vazio.length] = "Clientes";
		} else {
			var tipoLista = "id";
			if (formulario.tipo_lista) {
				if (formulario.tipo_lista.length) {
					for (var i = 0; i < formulario.tipo_lista.length; i++) {
						if (formulario.tipo_lista[i].checked) {
							tipoLista = formulario.tipo_lista[i].value;
							break;
						}
					}
				} else if (formulario.tipo_lista.value) {
					tipoLista = formulario.tipo_lista.value;
				}
			}

			if (tipoLista == "id") {
				var itens = formulario.lista_clientes.value.split(/[\s,;]+/);
				for (var i = 0; i < itens.length; i++) {
					var item = itens[i].replace(/^\s+|\s+$/g, "");
					if (item && !/^\d+$/.test(item)) {
						erro_invalido[erro_invalido.length] = "Lista de IDs (somente números)";
						break;
					}
				}
			}
		}

		return apresentaErros(erro_vazio, erro_invalido);
	}

	function temOrigemSelecionada(formulario) {
		var origens = formulario["origem[]"];
		if (!origens) {
			return false;
		}
		if (!origens.length) {
			return origens.checked ? true : false;
		}
		for (var i = 0; i < origens.length; i++) {
			if (origens[i].checked) {
				return true;
			}
		}
		return false;
	}

	function dataHojeIso() {
		var hoje = new Date();
		var dia = (hoje.getDate() < 10 ? "0" : "") + hoje.getDate();
		var mes = ((hoje.getMonth() + 1) < 10 ? "0" : "") + (hoje.getMonth() + 1);
		var ano = hoje.getFullYear();
		return ano + "-" + mes + "-" + dia;
	}

	<? if (false) { ?></script><?
	}
}
?>
