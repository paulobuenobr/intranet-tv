<?php

if ($_GET && array_key_exists("a", $_GET)) {

	// endpoint AJAX reservado, se necessário

} else {

	if (false) {
		?><script type="text/javascript"><?php
	}

?>

function mudaGerarNota(value) {

	var codigoServico = document.getElementById("codigo_servico_id");
	var cnae = document.getElementById("cnae_id");
	var codigoNds = document.getElementById("nds");
	var aliquota = document.getElementById("aliquota");
	var issRetido = document.getElementById("issRetido");

	if (!codigoServico || !cnae || !codigoNds || !aliquota || !issRetido) {
		return;
	}

	if (parseInt(value, 10) !== 1) {
		codigoServico.disabled = true;
		cnae.disabled = true;
		codigoNds.disabled = true;
		aliquota.disabled = true;
		issRetido.disabled = true;

		codigoServico.value = "";
		cnae.value = "";
		codigoNds.value = "";
		aliquota.value = "";
		issRetido.value = "";
	} else {
		codigoServico.disabled = false;
		cnae.disabled = false;
		codigoNds.disabled = false;
		aliquota.disabled = false;
		issRetido.disabled = false;

		if (!codigoServico.value) {
			codigoServico.value = "7";
		}
		if (!cnae.value) {
			cnae.value = "312";
		}
		if (!codigoNds.value) {
			codigoNds.value = "115013000";
		}
		if (!aliquota.value) {
			aliquota.value = "2,00";
		}
		if (!issRetido.value) {
			issRetido.value = "2";
		}
	}
}

function validaCategoriaAjusteFinanceiro(formulario) {

	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.afc_nome.value || !String(formulario.afc_nome.value).replace(/^\s+|\s+$/g, "")) {
		erro_vazio[erro_vazio.length] = "Nome";
	}

	if (!formulario.gerar_nfse.value && formulario.gerar_nfse.value !== "0") {
		erro_vazio[erro_vazio.length] = "Gerar NFS-e";
	} else if (parseInt(formulario.gerar_nfse.value, 10) === 1) {
		if (!formulario.codigo_servico_id.value) {
			erro_vazio[erro_vazio.length] = "Código Serviço";
		}
		if (!formulario.cnae_id.value) {
			erro_vazio[erro_vazio.length] = "CNAE";
		}
		if (!formulario.nds.value) {
			erro_vazio[erro_vazio.length] = "NBS";
		}
		if (!formulario.aliquota.value) {
			erro_vazio[erro_vazio.length] = "Alíquota";
		}
		if (!formulario.issRetido.value) {
			erro_vazio[erro_vazio.length] = "ISS Retido";
		}
	}

	return apresentaErros(erro_vazio, erro_invalido);
}

<?php

	if (false) {
		?></script><?php
	}

}

?>
