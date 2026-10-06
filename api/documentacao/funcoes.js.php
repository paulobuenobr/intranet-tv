<?php 
require_once '../config/config_api.php';
echo "var urlChamada = '".API_HOST."/';";
?>

$(function () {
	$('.accordion .collapse').on('shown.bs.collapse hidden.bs.collapse', atualizaTextoBotaoAccordions);
});

function accordionsDaDocumentacao() {
	return $('.accordion .collapse');
}

function todosAccordionsAbertos() {
	var $collapses = accordionsDaDocumentacao();
	return $collapses.length > 0 && $collapses.filter('.show').length === $collapses.length;
}

function atualizaTextoBotaoAccordions() {
	$('#btnToggleAccordions').text(todosAccordionsAbertos() ? 'Fechar todos' : 'Abrir todos');
}

function toggleTodosAccordions() {
	var $collapses = accordionsDaDocumentacao();
	if (todosAccordionsAbertos()) {
		$collapses.collapse('hide');
	} else {
		$collapses.collapse('show');
	}
}

function chamaSimula(formulario) {
	
	$('#return' + $('#'+formulario).attr('data-destino') ).html('<pre class="pre"><code>CARREGANDO...</code></pre>');
	
	var action = $('#'+formulario).attr('action');
	//fieldget
	$.each($('.fieldget'+$('#'+formulario).attr('data-destino')), function () {
		action += '/'+$(this).val();        
    });
	
	
	var str = $('#'+formulario).serialize();
	$.ajax({
		type: "POST",
		url: urlChamada + action,
		data: str,
		dataType : "json",
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		processData: false,
		//contentType: false,
		success: function(r) {
			$('#return' + $('#'+formulario).attr('data-destino') ).html('<pre class="pre"><code>'+ JSON.stringify(r, null, 2) +'</code></pre>');
			//Caso o retorno seja um PDF, já abre o mesmo em uma nova janela
			if (r.retorno_status == true && r.formato == "application/pdf") {
				window.open("data:application/pdf;base64, " + r.conteudo );
			} 
			
		}
	});

}