<?php

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$contatos = controle::instanciaObjeto("clientes.contatos", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "adicionar_contatos_telefones") {
		
		$itens_atuais = unserialize(stripslashes(stripslashes($_GET["lista_telefones"])));
		
		foreach ($itens_atuais as $chave => $valor) {
			if ($valor["contato_ddd"] == $_GET["contato_ddd"] && $valor["contato_numero"] == $_GET["contato_numero"] && $valor["contato_ramal"] == $_GET["contato_ramal"] && $valor["id"] != $_GET["id"]) {
				print "alert('Número já cadastrado para este cliente!');";
				die();
			}
		}
		
		//if (! $_GET["confirmar"] && ($clientes = $contatos->buscaTelefoneDuplicado($_GET["cliente_id"], $_GET["contato_numero"], $_GET["contato_ddd"]))) {
		//	print "if (confirm('Número já cadastrado para o(s) seguinte(s) cliente(s):\\n\\n * " . implode("\\n * ", $clientes) . "\\n\\nDeseja adicioná-lo assim mesmo?')) {";
		//	print "url = \"rds.js.php?m=clientes.contatos&a=adicionar_contatos_telefones&contato_ddd={$_GET["contato_ddd"]}&contato_numero={$_GET["contato_numero"]}&contato_ramal={$_GET["contato_ramal"]}&contato_telefone_tipo={$_GET["contato_telefone_tipo"]}&lista_telefones=" . urlencode($_GET["lista_telefones"]) . "&id={$_GET["id"]}&cliente_id={$_GET["cliente_id"]}&confirmar=1&auto_salvar={$_GET["auto_salvar"]}\";";
		//	print "RDSCarregar(\"clientes_contatos_adicionar_contatos_telefones\", url); }";
		//	die();
		//}
		
		if ($_SESSION["origem"] > 1 && $contatos->buscaTelefones($_GET["cliente_id"], $_GET["contato_numero"], $_GET["contato_ddd"]) >= 1) {
			print "alert('Não é permitido cadastro de telefone duplicado.');";
			die();
		}
		
		$novo_item = array(
			"id" => $_GET["id"] ? $_GET["id"] : rand(),
			"contato_ddd" => $_GET["contato_ddd"],
			"contato_numero" => $_GET["contato_numero"],
			"contato_ramal" => $_GET["contato_ramal"],
			"contato_telefone_tipo_id" => $_GET["contato_telefone_tipo"],
			"contato_telefone_tipo" => $contatos->buscaContatoTelefoneTipo($_GET["contato_telefone_tipo"])
		);
		
		if ($_GET["id"]) {
			foreach ($itens_atuais as $chave => $valor) {
				if ($valor["id"] == $_GET["id"]) $itens_atuais[$chave] = $novo_item;
			}
		} else {
			$itens_atuais[] = $novo_item;
		}
		
		if ($_GET["auto_salvar"]) {
			$contatos->atualizaContatoTelefones($_GET["cliente_id"], $itens_atuais);
		}
		
		$dados = $contatos->geraContatosTelefonesListagem($itens_atuais, true, $_GET["removivel"]);
		
		print "if (document.getElementById(\"listagem_telefones\")) document.getElementById(\"listagem_telefones\").innerHTML = '" . str_replace("'", '\\\'', $dados) . "';";
		print "if (document.getElementById(\"lista_telefones\")) document.getElementById(\"lista_telefones\").value = '" . serialize($itens_atuais) . "';";
		print "if (document.getElementById(\"contato_ddd\")) document.getElementById(\"contato_ddd\").focus();";
		print "telefone_quantidade = " . count($itens_atuais) . ";";
		print "telefone_linha_id = 0;";
		
	} elseif ($_GET["a"] == "remover_contatos_telefones") {
		
		$itens_atuais = unserialize(stripslashes($_GET["lista_telefones"]));
		
		foreach ($itens_atuais as $chave => $valor) {
			if ($valor["id"] == $_GET["id"]) {
				$item = $valor;
				unset($itens_atuais[$chave]);
			}
		}
		
		if ($_GET["auto_salvar"]) {
			$contatos->atualizaContatoTelefones($_GET["cliente_id"], $itens_atuais);
		}
		
		$dados = $contatos->geraContatosTelefonesListagem($itens_atuais, true, $_GET["removivel"]);
		
		print "if (document.getElementById(\"listagem_telefones\")) document.getElementById(\"listagem_telefones\").innerHTML = '" . str_replace("'", '\\\'', $dados) . "';";
		print "if (document.getElementById(\"lista_telefones\")) document.getElementById(\"lista_telefones\").value = '" . serialize($itens_atuais) . "';";
		print "if (document.getElementById(\"contato_ddd\")) document.getElementById(\"contato_ddd\").value = '{$item["contato_ddd"]}';";
		print "if (document.getElementById(\"contato_numero\")) document.getElementById(\"contato_numero\").value = '{$item["contato_numero"]}';";
		print "if (document.getElementById(\"contato_ramal\")) document.getElementById(\"contato_ramal\").value = '{$item["contato_ramal"]}';";
		print "if (document.getElementById(\"contato_telefone_tipo\")) document.getElementById(\"contato_telefone_tipo\").value = '{$item["contato_telefone_tipo_id"]}';";
		print "if (document.getElementById(\"contato_ddd\")) document.getElementById(\"contato_ddd\").focus();";
		print "telefone_quantidade = " . count($itens_atuais) . ";";
		print "telefone_linha_id = 0;";
		
	} elseif ($_GET["a"] == "selecionar_contatos_telefones") {
		
		$itens_atuais = unserialize(stripslashes($_GET["lista_telefones"]));
		
		foreach ($itens_atuais as $chave => $valor) {
			if ($valor["id"] == $_GET["id"]) $item = $valor;
		}
		
		print "if (document.getElementById(\"contato_ddd\")) document.getElementById(\"contato_ddd\").value = '{$item["contato_ddd"]}';";
		print "if (document.getElementById(\"contato_numero\")) document.getElementById(\"contato_numero\").value = '{$item["contato_numero"]}';";
		print "if (document.getElementById(\"contato_ramal\")) document.getElementById(\"contato_ramal\").value = '{$item["contato_ramal"]}';";
		print "if (document.getElementById(\"contato_telefone_tipo\")) document.getElementById(\"contato_telefone_tipo\").value = '{$item["contato_telefone_tipo_id"]}';";
		print "if (document.getElementById(\"contato_ddd\")) document.getElementById(\"contato_ddd\").focus();";
		
	} elseif ($_GET["a"] == "adicionar_contatos_emails") {
		
		$itens_atuais = unserialize(stripslashes(stripslashes($_GET["lista_emails"])));

		foreach ($itens_atuais as $chave => $valor) {
			if ($valor["contato_email"] == $_GET["contato_email"] && $valor["id"] != $_GET["id"]) {
				print "alert('E-mail já cadastrado para este cliente!');";
				die();
			}
		}
		
		$contato_email = explode("@", $_GET["contato_email"]);
		
		if (($clientes = $contatos->buscaEmailDuplicado($_GET["cliente_id"], $contato_email[0], $contato_email[1])) && ! $_GET["confirmar"]) {
			print "if (confirm('E-mail já cadastrado para o(s) seguinte(s) cliente(s):\\n\\n * " . implode("\\n * ", $clientes) . "\\n\\nDeseja adicioná-lo assim mesmo?')) {";
			print "url = \"rds.js.php?m=clientes.contatos&a=adicionar_contatos_emails&contato_email={$_GET["contato_email"]}&contato_email_tipo={$_GET["contato_email_tipo"]}&lista_emails=" . urlencode($_GET["lista_emails"]) . "&id={$_GET["id"]}&cliente_id={$_GET["cliente_id"]}&confirmar=1\";";
			print "RDSCarregar(\"clientes_contatos_adicionar_contatos_emails\", url); }";
			die();
		}
		
		$novo_item = array(
			"id" => $_GET["id"] ? $_GET["id"] : rand(),
			"contato_email" => $_GET["contato_email"],
			"contato_email_tipo_id" => $_GET["contato_email_tipo"],
			"contato_email_tipo" => $contatos->buscaContatoEmailTipo($_GET["contato_email_tipo"])
		);
		
		if ($_GET["id"]) {
			foreach ($itens_atuais as $chave => $valor) {
				if ($valor["id"] == $_GET["id"]) $itens_atuais[$chave] = $novo_item;
			}
		} else {
			$itens_atuais[] = $novo_item;
		}
		
		if ($_GET["auto_salvar"]) {
			$contatos->atualizaContatoEmails($_GET["cliente_id"], $itens_atuais);
		}
		
		$dados = $contatos->geraContatosEmailsListagem($itens_atuais, true, $_GET["removivel"]);
		
		print "if (document.getElementById(\"listagem_emails\")) document.getElementById(\"listagem_emails\").innerHTML = '" . str_replace("'", '\\\'', $dados) . "';";
		print "if (document.getElementById(\"lista_emails\")) document.getElementById(\"lista_emails\").value = '" . serialize($itens_atuais) . "';";
		print "email_quantidade = " . count($itens_atuais) . ";";
		print "email_linha_id = 0;";
		
	} elseif ($_GET["a"] == "remover_contatos_emails") {
		
		$itens_atuais = unserialize(stripslashes($_GET["lista_emails"]));
		
		foreach ($itens_atuais as $chave => $valor) {
			if ($valor["id"] == $_GET["id"]) {
				$item = $valor;
				unset($itens_atuais[$chave]);
			}
		}
		
		if ($_GET["auto_salvar"]) {
			$contatos->atualizaContatoEmails($_GET["cliente_id"], $itens_atuais);
		}
		
		$dados = $contatos->geraContatosEmailsListagem($itens_atuais, true, $_GET["removivel"]);
		
		print "if (document.getElementById(\"listagem_emails\")) document.getElementById(\"listagem_emails\").innerHTML = '" . str_replace("'", '\\\'', $dados) . "';";
		print "if (document.getElementById(\"lista_emails\")) document.getElementById(\"lista_emails\").value = '" . serialize($itens_atuais) . "';";
		print "if (document.getElementById(\"contato_email\")) document.getElementById(\"contato_email\").value = '{$item["contato_email"]}';";
		print "if (document.getElementById(\"contato_email_tipo\")) document.getElementById(\"contato_email_tipo\").value = '{$item["contato_email_tipo_id"]}';";
		print "email_quantidade = " . count($itens_atuais) . ";";
		print "email_linha_id = 0;";
		
	} elseif ($_GET["a"] == "selecionar_contatos_emails") {
		
		$itens_atuais = unserialize(stripslashes($_GET["lista_emails"]));
		
		foreach ($itens_atuais as $chave => $valor) {
			if ($valor["id"] == $_GET["id"]) {
				$item = $valor;
			}
		}
		
		print "if (document.getElementById(\"contato_email\")) document.getElementById(\"contato_email\").value = '{$item["contato_email"]}';";
		print "if (document.getElementById(\"contato_email_tipo\")) document.getElementById(\"contato_email_tipo\").value = '{$item["contato_email_tipo_id"]}';";
		
	}
	
} else {
	
	if (false) { ?><script type="text/javascript"><?php }
	
?>

var telefone_linha_id = 0;

function adicionarTelefone(removivel) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!document.getElementById('contato_ddd').value) {
		erro_vazio[erro_vazio.length] = "DDD";
	}
	if (!document.getElementById('contato_numero').value) {
		erro_vazio[erro_vazio.length] = "Número";
	} else if (!validaTelefoneSemDDD(document.getElementById('contato_numero').value)) {
		erro_invalido[erro_invalido.length] = "Número";
	}
	if (!document.getElementById('contato_telefone_tipo').value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	if (apresentaErros(erro_vazio, erro_invalido)) {
		var auto_salvar = (document.getElementById('auto_salvar') && document.getElementById('auto_salvar').value == "1") ? 1 : 0;
		url = "rds.js.php?m=clientes.contatos&a=adicionar_contatos_telefones&contato_ddd=" + document.getElementById('contato_ddd').value + "&contato_numero=" + document.getElementById('contato_numero').value + "&contato_ramal=" + document.getElementById('contato_ramal').value + "&contato_telefone_tipo=" + document.getElementById('contato_telefone_tipo').value + "&lista_telefones=" + document.getElementById('lista_telefones').value + "&id=" + telefone_linha_id + "&cliente_id=" + document.getElementById('cliente_id').value + "&auto_salvar=" + auto_salvar + "&removivel=" + removivel;
		RDSCarregar("clientes_contatos_adicionar_contatos_telefones", url);
	}
}

function removerTelefone(id, removivel) {
	if (id && confirm('Tem certeza que deseja remover este telefone?')) {
		var auto_salvar = (document.getElementById('auto_salvar') && document.getElementById('auto_salvar').value == "1") ? 1 : 0;
		url = "rds.js.php?m=clientes.contatos&a=remover_contatos_telefones&id=" + id + "&lista_telefones=" + document.getElementById('lista_telefones').value + "&auto_salvar=" + auto_salvar + "&cliente_id=" + document.getElementById('cliente_id').value + "&removivel=" + removivel;
		RDSCarregar("clientes_contatos_remover_contatos_telefones", url);
	}
}

function selecionarLinhaTelefone() {
	if (telefone_linha_id) {
		url = "rds.js.php?m=clientes.contatos&a=selecionar_contatos_telefones&id=" + telefone_linha_id + "&lista_telefones=" + document.getElementById('lista_telefones').value;
		RDSCarregar("clientes_contatos_selecionar_contatos_telefones", url);
	}
}

function selecionarTelefone(linha, evento, id) {
	if (ehNetscape()) {
		if (evento.target.tagName != "TD") return false;
		var tabela = linha.parentNode;
	} else {
		if (evento.srcElement.tagName != "TD") return false;
		var tabela = linha.parentElement;
	}
	telefone_linha_id = 0;
	for (i = 0; i < tabela.rows.length; i++) {
		if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
			if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
				linha.style.backgroundColor = "";
				document.getElementById("contato_ddd").value = "";
				document.getElementById("contato_numero").value = "";
				document.getElementById("contato_ramal").value = "";
				document.getElementById("contato_telefone_tipo").value = "";
			} else {
				linha.style.backgroundColor = "#ffcc66";
				telefone_linha_id = linha.id;
				selecionarLinhaTelefone();
			}
		} else {
			tabela.rows[i].style.backgroundColor = "";
		}
	}
}

var email_linha_id = 0;

function adicionarEmail(removivel) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!document.getElementById('contato_email').value) {
		erro_vazio[erro_vazio.length] = "E-mail";
	} else if (!validaEmail(document.getElementById('contato_email').value)) {
		erro_invalido[erro_invalido.length] = "E-mail";
	}
	if (!document.getElementById('contato_email_tipo').value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	if (apresentaErros(erro_vazio, erro_invalido)) {
		//if (document.getElementById('contato_email_tipo').value == 1 || confirm("Tem certeza que deseja adicionar este e-mail como Contato?\nO cliente não receberá e-mails automáticos do sistema.")) {
			var auto_salvar = (document.getElementById('auto_salvar') && document.getElementById('auto_salvar').value == "1") ? 1 : 0;
			url = "rds.js.php?m=clientes.contatos&a=adicionar_contatos_emails&contato_email=" + document.getElementById('contato_email').value + "&contato_email_tipo=" + document.getElementById('contato_email_tipo').value + "&lista_emails=" + document.getElementById('lista_emails').value + "&id=" + email_linha_id + "&cliente_id=" + document.getElementById('cliente_id').value + "&auto_salvar=" + auto_salvar + "&removivel=" + removivel;
			RDSCarregar("clientes_contatos_adicionar_contatos_emails", url);
		//}
	}
}

function removerEmail(id, removivel) {
	if (id && confirm('Tem certeza que deseja remover este e-mail?')) {
		var auto_salvar = (document.getElementById('auto_salvar') && document.getElementById('auto_salvar').value == "1") ? 1 : 0;
		url = "rds.js.php?m=clientes.contatos&a=remover_contatos_emails&id=" + id + "&lista_emails=" + document.getElementById('lista_emails').value+ "&auto_salvar=" + auto_salvar + "&cliente_id=" + document.getElementById('cliente_id').value + "&removivel=" + removivel;
		RDSCarregar("clientes_contatos_remover_contatos_emails", url);
	}
}

function selecionarLinhaEmail() {
	if (email_linha_id) {
		url = "rds.js.php?m=clientes.contatos&a=selecionar_contatos_emails&id=" + email_linha_id + "&lista_emails=" + document.getElementById('lista_emails').value;
		RDSCarregar("clientes_contatos_selecionar_contatos_emails", url);
	}
}

function selecionarEmail(linha, evento, id) {
	if (ehNetscape()) {
		if (evento.target.tagName != "TD") return false;
		var tabela = linha.parentNode;
	} else {
		if (evento.srcElement.tagName != "TD") return false;
		var tabela = linha.parentElement;
	}
	email_linha_id = 0;
	for (i = 0; i < tabela.rows.length; i++) {
		if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
			if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
				linha.style.backgroundColor = "";
				document.getElementById("contato_email").value = "";
				document.getElementById("contato_email_tipo").value = "";
			} else {
				linha.style.backgroundColor = "#ffcc66";
				email_linha_id = linha.id;
				selecionarLinhaEmail();
			}
		} else {
			tabela.rows[i].style.backgroundColor = "";
		}
	}
}

<?php
	
	if (false) { ?></script><?php }
	
}

?>
