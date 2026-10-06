<?php

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	/**
	 * @var clientes_origens $clientes_origens
	 */
	$clientes_origens = controle::instanciaObjeto("clientes_origens", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "adicionar_cidade") {
		
		$itens_atuais = unserialize(stripslashes($_GET["cidades_lista"]));

		$existente = false;
		
		foreach ($itens_atuais as $dados) {
			if ($dados["cidade_id"] == $_GET["cidade"]) {
				$existente = true;
			}
		}

		if (!$existente) {
			
			$novo_item = array(
				"cidade_id" => $_GET["cidade"],
				"cidade_nome" => $clientes_origens->getCidadeNome($_GET["cidade"]),
				"estado_id" => $_GET["estado"],
				"estado_nome" => $clientes_origens->getEstadoNome($_GET["estado"])
			);
			
			if ($_GET["id"]) {
				$itens_atuais[$_GET["id"] - 1] = $novo_item;
			} else {
				$itens_atuais[] = $novo_item;
			}
			
			$clientes_origens->ordena_multiarray($itens_atuais, array("estado_nome", "cidade_nome"));
			
		}
		
		$listagem = $clientes_origens->geraCidadesListagem($itens_atuais, true);
		
		print "document.getElementById(\"cidades_listagem\").innerHTML = '" . str_replace("'", '\\\'', $listagem) . "';";
		print "document.getElementById(\"cidades_lista\").value = '" . serialize($itens_atuais) . "';";
        print "document.getElementById(\"estado_lista\").focus();";
		
	} elseif ($_GET["a"] == "remover_cidade") {
		
		$itens_atuais = unserialize(stripslashes($_GET["cidades_lista"]));
		
		$item = $itens_atuais[$_GET["id"] - 1];
		
		unset($itens_atuais[$_GET["id"] - 1]);
		
		sort($itens_atuais);
		
		//$itens_atuais = $clientes_origens->ordena_multiarray($itens_atuais, array("estado_nome", "cidade_nome"));
		
		$listagem = $clientes_origens->geraCidadesListagem($itens_atuais, true);
		
		print "document.getElementById(\"cidades_listagem\").innerHTML = '" . str_replace("'", '\\\'', $listagem) . "';";
		print "document.getElementById(\"cidades_lista\").value = '" . serialize($itens_atuais) . "';";
		print "document.getElementById(\"estado_lista\").value = '{$item["estado_id"]}';";
		print "atualizaSelect(document.getElementById(\"cidade_lista\"), new Array(".implode(",", $clientes_origens->buscaEstadoCidades($item["estado_id"]))."), ".$item["cidade_id"].");";
		
	} elseif ($_GET["a"] == "busca_cidades") {
		
		print "atualizaSelect(document.getElementById(\"cidade_lista\"), new Array(".implode(",", $clientes_origens->buscaEstadoCidades($_GET["estado"]))."));";
		
	} elseif($_GET["a"] == "desbloquearApi") {
		
		$clientes_origens->desbloquearApi($id = $_GET["id"]);
		
		print "alert('API desbloqueada com sucesso!');";
		print "window.location = '?m=clientes_origens&a=editar_dt&i=" . intval($_GET["id"]) . "';";
	}

} else {
	
	if (false) { ?><script type="text/javascript"><?php }
	
?>

function newkeyapi() {
	var tmp =  parseInt( Date.now() ).toString();
	document.getElementById('apihash').value = hex_sha1(tmp);
	document.getElementById('apikey').value = hex_sha1( document.getElementById('apihash').value );
}

function toggleRemoverLogo(checkbox) {
	var logoInput = document.getElementById('logo');
	var logoPreview = document.getElementById('logo_preview');

	if (!logoInput || !logoPreview) {
		return;
	}

	if (checkbox.checked) {
		// logoPreview.style.display = 'none';
		logoPreview.style.visibility = 'hidden';
		logoInput.value = '';
		logoInput.disabled = true;
	} else {
		// logoPreview.style.display = 'block';
		logoPreview.style.visibility = 'visible';
		logoInput.disabled = false;
	}
}

function desbloquearApi(id) {
	if (confirm('Tem certeza que deseja Desbloquear API?')) {
		if (id) {
			url = "rds.js.php?m=clientes_origens&a=desbloquearApi&id=" + id;
			RDSCarregar("clientes_origens", url);
		}
	}
}

function validaClienteOrigem(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.nome.value) {
		erro_vazio[erro_vazio.length] = "Nome";
	}
	if (!formulario.razao_social.value) {
		erro_vazio[erro_vazio.length] = "Razão Social";
	}
	if (!formulario.cnpj.value) {
		erro_vazio[erro_vazio.length] = "CNPJ";
	} else if (!validaCNPJ(formulario.cnpj.value)) {
		erro_invalido[erro_invalido.length] = "CNPJ";
	}
	if (!formulario.cep.value) {
		erro_vazio[erro_vazio.length] = "Endereço » CEP";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaOrigemCancelar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.data_cancelamento.value) {
		erro_vazio[erro_vazio.length] = "Data » Cancelamento";
	} else if (!validaData(formulario.data_cancelamento.value)) {
		erro_invalido[erro_invalido.length] = "Data » Cancelamento";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function adicionarCidade() {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!document.getElementById('estado_lista').value) {
		erro_vazio[erro_vazio.length] = "Estado";
	}
	if (!document.getElementById('cidade_lista').value) {
		erro_vazio[erro_vazio.length] = "Cidade";
	}
	if (apresentaErros(erro_vazio, erro_invalido)) {
		url = "rds.js.php?m=clientes_origens&a=adicionar_cidade&estado=" + document.getElementById('estado_lista').value + "&cidade=" + document.getElementById('cidade_lista').value + "&cidades_lista=" + document.getElementById('cidades_lista').value;
		RDSCarregar("clientes_origens_adicionar_cidade", url);
	}
}

function removerCidade(id) {
	if (id && confirm('Tem certeza que deseja remover esta Cidade?')) {
		url = "rds.js.php?m=clientes_origens&a=remover_cidade&id=" + id + "&cidades_lista=" + document.getElementById('cidades_lista').value;
		RDSCarregar("clientes_origens_remover_cidade", url);
	}
}

function mudaEstadoLista(estado) {
	if (estado) {
		url = "rds.js.php?m=clientes_origens&a=busca_cidades&estado=" + estado;
		RDSCarregar("clientes_origens_busca_cidades", url);
	} else {
		document.getElementById('cidade_lista').innerHTML = '';
		document.getElementById('cidade_lista').disabled = true;
	}
}

<?php

	if (false) { ?></script><?php }

}

?>