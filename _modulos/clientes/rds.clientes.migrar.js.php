<?php

if ($_GET["a"]) {
	
	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes = controle::instanciaObjeto("clientes", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "busca_cliente_migracao_nome" && $_GET["origem"] && $_GET["destino"]) {
	
		print "var mensagem = '';";
		
		if ($_GET["origem"] == $_GET["destino"]) {
			print "mensagem = 'Os clientes Origem e Destino devem ser diferentes!';";
		} else {
			if (($dados = $clientes->buscaDadosCliente($_GET["destino"])) != false) {
				if ($dados["cpf_cnpj"]) $dados["nome"] .= " ({$dados["cpf_cnpj"]})";
				print "if (document.getElementById(\"cliente_destino_nome\")) document.getElementById(\"cliente_destino_nome\").value = '{$dados["nome"]}';";
			} else {
				print "mensagem = 'Não existe nenhum cliente disponível com o id {$_GET["destino"]}!';";
			}
		}
		
		print "if (mensagem) { alert(mensagem); document.getElementById(\"cliente_destino_id\").value = ''; document.getElementById(\"cliente_destino_nome\").value = ''; document.getElementById(\"cliente_destino_id\").focus(); }";
	
	}

} else {
	
	if (false) { ?><script type="text/javascript"><?php }
	
?>

function buscaClienteMigracaoNome (origem, destino) {
	if (origem && destino) {
		url = "rds.js.php?m=clientes.migrar&a=busca_cliente_migracao_nome&origem=" + origem + "&destino=" + destino;
		RDSCarregar("clientes_migrar_busca_cliente_migracao_nome", url);
	}
}

function validaClienteMigrar (formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.cliente_destino_id.value) {
		erro_vazio[erro_vazio.length] = "Cliente Destino";
	} else if (formulario.cliente_destino_id.value == formulario.cliente_origem_id.value) {
		erro_invalido[erro_invalido.length] = "Cliente Destino (Deve ser diferente do Cliente Origem)";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><?php }

}

?>
