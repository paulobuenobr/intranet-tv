<?php

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes = controle::instanciaObjeto("clientes", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "busca_alertas") {
		
		$alertas = $clientes->buscaAlertas($_GET["cliente"], $_GET["cpf_cnpj"], $_GET["bloco"], $_GET["cep"], $_GET["numero"], $_GET["complemento"]);
		$responsavel = null;
		
		if ($alertas) {
			
			print 'if (document.getElementById("duplicado_cpf_cnpj")) document.getElementById("duplicado_cpf_cnpj").value = "' . ($alertas["bloqueio_cpf_cnpj_duplicado"] ? '1' : '0') . '";';
			print 'if (document.getElementById("duplicado_endereco")) document.getElementById("duplicado_endereco").value = "' . ($alertas["bloqueio_endereco_duplicado"] ? '1' : '0') . '";';
			print 'if (document.getElementById("duplicado_bloqueio")) document.getElementById("duplicado_bloqueio").value = "' . ($alertas["bloqueio"] ? '1' : '0') . '";';
			print 'alerta_conteudo = \'' . addslashes($clientes->geraMensagemHtml($alertas)) . '\';';
			print 'alerta_conteudo_alterado = (alerta_conteudo_antigo != alerta_conteudo);';
			print 'if (alerta_conteudo_alterado) alerta_conteudo_antigo = \'' . addslashes($clientes->geraMensagemHtml($alertas)) . '\';';
			print 'if (document.getElementById("alertas") && alerta_conteudo_alterado) document.getElementById("alertas").innerHTML = alerta_conteudo;';
			print 'if (document.getElementById("alertas") && alerta_conteudo_alterado) alert(\'' . $clientes->geraMensagemTexto($alertas) . '\');';
			
			foreach ($alertas as $mensagens_alerta) {
				foreach ($mensagens_alerta["clientes"] as $cliente) {
					$responsavel = $responsavel ? $responsavel : $cliente["responsavel_id"];
				}
			}
			
			if ($responsavel) {
				print 'if (document.getElementById("responsavel")) document.getElementById("responsavel").disabled = true;';
				print 'if (document.getElementById("responsavel")) document.getElementById("responsavel").value = "' . $responsavel . '";';
				print 'if (document.getElementById("responsavel_duplicado")) document.getElementById("responsavel_duplicado").value = "' . $responsavel . '";';
			} else {
				print 'if (document.getElementById("responsavel")) document.getElementById("responsavel").disabled = false;';
				print 'if (document.getElementById("responsavel_duplicado")) document.getElementById("responsavel_duplicado").value = "";';
			}
			
		} else {
			
			print 'alerta_conteudo = \'\';';
			print 'alerta_conteudo_antigo = false;';
			
			print 'if (document.getElementById("alertas")) document.getElementById("alertas").innerHTML = "";';
			print 'if (document.getElementById("responsavel")) document.getElementById("responsavel").disabled = false;';
			print 'if (document.getElementById("duplicado_cpf_cnpj")) document.getElementById("duplicado_cpf_cnpj").value = "0";';
			print 'if (document.getElementById("duplicado_endereco")) document.getElementById("duplicado_endereco").value = "0";';
			print 'if (document.getElementById("duplicado_bloqueio")) document.getElementById("duplicado_bloqueio").value = "0";';
			
		}
		
	} elseif ($_GET["a"] == "verifica_cliente_telefone" && ! empty($_GET["telefone"])) {
		
		$query = $bancodedados->query("
			SELECT DISTINCT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				IF(tab_usuarios.usu_nome IS NULL, 'Sem responsável', tab_usuarios.usu_nome) AS responsavel,
				tab_clientes.clt_id AS tipo,
				tab_clientes.cls_id AS status,
				tab_clientes_status.cls_nome AS status_nome
			FROM
				tab_clientes
				INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
				INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
			WHERE
				ccf_id > 0 AND
				CONCAT('(', ccl_ddd, ') ', ccl_numero) = '{$_GET["telefone"]}' AND
				tab_clientes.cli_id <> {$_GET["cliente"]}".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));
		
		while (($cliente = $bancodedados->resultadoArray($query)) != false) {
			
			$cliente["tipo"] = ($cliente["tipo"] == 1) ? "CPF" : "CNPJ";
			$cliente["status"] = ($cliente["status"] == 2) ? "Cliente" : "Pessoa";
			if (! $cliente["cpf_cnpj"]) $cliente["cpf_cnpj"] = "Não Disponível";
			$cliente["endereco"] = $clientes->buscaEndereco($cliente["id"]);
			
			$clientes[] = $cliente;
			
		}
		
		if (sizeof($clientes) > 1) {
			
			$mensagem_js = "O telefone {$_GET["telefone"]} está cadastrado junto aos(às) seguintes clientes/prospects:\\n";
			$mensagem_html = "<b>Clientes/Prospects cadastrados(as) com o telefone {$_GET["telefone"]}:</b><br>";
			
			for ($i = 0; $i < sizeof($clientes); $i++) {
				$mensagem_js .= "\\n";
				$mensagem_js .= "{$clientes[$i]["nome"]} ({$clientes[$i]["status_nome"]})\\n";
				$mensagem_js .= " » {$clientes[$i]["tipo"]}: {$clientes[$i]["cpf_cnpj"]}\\n";
				$mensagem_js .= " » Endereço: {$clientes[$i]["endereco"]}\\n";
				$mensagem_js .= " » Responsável: {$clientes[$i]["responsavel"]}\\n";
				$mensagem_html .= "<br>";
				$mensagem_html .= "<b><a href=\"?m=clientes&a=ver_dc&i={$clientes[$i]["id"]}\" class=\"txtAzulEscuro11\">{$clientes[$i]["nome"]}</a></b> ({$clientes[$i]["status_nome"]})<br>";
				$mensagem_html .= " » <b>{$clientes[$i]["tipo"]}</b>: {$clientes[$i]["cpf_cnpj"]}<br>";
				$mensagem_html .= " » Endereço: {$clientes[$i]["endereco"]}<br>";
				$mensagem_html .= " » <b>Responsável</b>: {$clientes[$i]["responsavel"]}<br>";
			}
			
			print "if (document.getElementById(\"alertas_telefone\") != null) { document.getElementById(\"alertas_telefone\").innerHTML = '$mensagem_html'; } else { document.getElementById(\"alertas\").innerHTML += '<span id=\"alertas_telefone\">$mensagem_html</span><br>'; }";
			print "alert('$mensagem_js');";
			
		} elseif (sizeof($clientes) == 1) {
			
			$mensagem_js = "O telefone {$_GET["telefone"]} está cadastrado junto ao(à) seguinte {$clientes[0]["status"]}:\\n";
			$mensagem_js .= "\\n";
			$mensagem_js .= "{$clientes[0]["nome"]}\\n";
			$mensagem_js .= " » {$clientes[0]["tipo"]}: {$clientes[0]["cpf_cnpj"]}\\n";
			$mensagem_js .= " » Endereço: {$clientes[0]["endereco"]}\\n";
			$mensagem_js .= " » Responsável: {$clientes[0]["responsavel"]}\\n";
			$mensagem_js .= " » Status: {$clientes[0]["status_nome"]}\\n";
			$mensagem_js .= "\\n";
			$mensagem_js .= "Você deseja acessar o cadastro dele(a)?";
			
			$mensagem_html = "<b>{$clientes[0]["status"]} cadastrado(a) com o telefone {$_GET["telefone"]}:</b><br>";
			$mensagem_html .= "<br>";
			$mensagem_html .= "<b><a href=\"?m=clientes&a=ver_dc&i={$clientes[0]["id"]}\" class=\"txtAzulEscuro11\">{$clientes[0]["nome"]}</a></b> ({$clientes[0]["status_nome"]})<br>";
			$mensagem_html .= " » <b>{$clientes[0]["tipo"]}</b>: {$clientes[0]["cpf_cnpj"]}<br>";
			$mensagem_html .= " » Endereço: {$clientes[0]["endereco"]}<br>";
			$mensagem_html .= " » <b>Responsável</b>: {$clientes[0]["responsavel"]}<br>";
			
			print "if (document.getElementById(\"alertas_telefone\") != null) { document.getElementById(\"alertas_telefone\").innerHTML = '$mensagem_html'; } else { document.getElementById(\"alertas\").innerHTML += '<span id=\"alertas_telefone\">$mensagem_html</span><br>'; }";
			print "if (confirm('$mensagem_js')) window.open('?m=clientes&a=ver_dc&i={$clientes[0]["id"]}', '_blank');";
		
		} else {
			print "if (document.getElementById(\"alertas_telefone\") != null) document.getElementById(\"alertas_telefone\").innerHTML = '';";
		}
		
	} elseif ($_GET["a"] == "busca_panorama_condominio" && $_GET["condominio"]) {
		
		$query = $bancodedados->query("SELECT tab_condominios_status.cos_id AS id, tab_condominios_status.nome AS status_nome FROM tab_condominios, tab_condominios_status WHERE tab_condominios.cos_id = tab_condominios_status.cos_id AND tab_condominios.con_id = {$_GET["condominio"]}");
		$condominio_status = $bancodedados->resultadoArray($query);
		
		$query = $bancodedados->query("SELECT COUNT(*) FROM tab_clientes WHERE con_id = {$_GET["condominio"]} AND cls_id = 1");
		$clientes_prospects = intval($bancodedados->resultado($query));
		
		$query = $bancodedados->query("SELECT COUNT(*) FROM tab_clientes WHERE con_id = {$_GET["condominio"]} AND cls_id = 2");
		$clientes_ativos = intval($bancodedados->resultado($query));

		$query = $bancodedados->query("SELECT COUNT(*) FROM tab_clientes WHERE con_id = {$_GET["condominio"]} AND cls_id = 1 AND clu_id = 4");
		$clientes_prospects_fechados = intval($bancodedados->resultado($query));
		
		if ($clientes_ativos > 1 || $clientes_ativos == 0) $plural_ativos = "s";
		
		if ($clientes_prospects_fechados > 7 && $condominio_status["id"] == 1) {
			$clientes_prospects = "<font color=\"#FF0000\">$clientes_prospects</font>";
			$alerta_condominio_pronto = "alert('Este condomínio é prospect e já pode ser instalado!');";
		}
		
		$panorama_condominio = "<b>{$condominio_status["nome"]}</b> ($clientes_ativos cliente$plural_ativos)";
		
		print "if (document.getElementById(\"{$_GET["sufixo"]}panorama_condominio\") != null) { document.getElementById(\"{$_GET["sufixo"]}panorama_condominio\").innerHTML = '$panorama_condominio'; }";
		
		if (! $_GET["sufixo"]) {
			print "if (document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {";
			print "if (document.getElementById(\"cobranca_panorama_condominio\") != null) { document.getElementById(\"cobranca_panorama_condominio\").innerHTML = '$panorama_condominio'; }";
			print "}";
		}
		
		print $alerta_condominio_pronto;
		
	} elseif ($_GET["a"] == "busca_panorama_bloco" && $_GET["bloco"]) {
		
		$panorama_bloco = $clientes->buscaPanoramaBloco($_GET["bloco"]);
		
		print "if (document.getElementById(\"{$_GET["sufixo"]}panorama_bloco\") != null) { document.getElementById(\"{$_GET["sufixo"]}panorama_bloco\").innerHTML = '$panorama_bloco'; }";
		
		if (! $_GET["sufixo"]) {
			print "if (document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {";
			print "if (document.getElementById(\"cobranca_panorama_bloco\") != null) { document.getElementById(\"cobranca_panorama_bloco\").innerHTML = '$panorama_bloco'; }";
			print "}";
		}
		
	}

} else {

	if (false) { ?><script type="text/javascript"><?php }

?>

var alerta_conteudo = '';
var alerta_conteudo_antigo = '';
var alerta_conteudo_alterado = '';

function buscaAlertas(formulario) {
	var cpf_cnpj = formulario.cpf_cnpj ? formulario.cpf_cnpj.value : null;
	var bloco = formulario.condominio.value ? formulario.bloco.value : 0;
	var cep = formulario.condominio.value ? 0 : formulario.cep.value;
	var numero = formulario.condominio.value ? 0 : formulario.numero.value;
	var complemento = formulario.complemento_valor.value;
	if ((bloco != '' || cep != '') || cpf_cnpj != '') {
		url = "rds.js.php?m=clientes.alertas&a=busca_alertas&cliente=" + cliente_id + "&cpf_cnpj=" + cpf_cnpj + "&bloco=" + bloco + "&cep=" + cep + "&numero=" + numero + "&complemento=" + complemento;
		RDSCarregar("clientes_alertas_busca_alertas", url);
	} else {
		if (document.getElementById("alertas")) document.getElementById("alertas").innerHTML = '';
		if (document.getElementById("responsavel")) document.getElementById("responsavel").disabled = false;
		if (document.getElementById("duplicado_cpf_cnpj")) document.getElementById("duplicado_cpf_cnpj").value = "0";
		if (document.getElementById("duplicado_endereco")) document.getElementById("duplicado_endereco").value = "0";
		if (document.getElementById("duplicado_bloqueio")) document.getElementById("duplicado_bloqueio").value = "0";
	}
}

function verificaClientesMesmoTelefone(campo) {
	telefone = campo.value;
	if (validaTelefone(telefone) && cliente_id != null) {
		url = "rds.js.php?m=clientes.alertas&a=verifica_cliente_telefone&telefone=" + telefone + "&cliente=" + cliente_id;
		RDSCarregar("clientes_alertas_verifica_cliente_telefone", url);
		} else {
		if (document.getElementById("alertas_telefone") != null) {
			document.getElementById("alertas_telefone").innerHTML = '';
		}
	}
}

function buscaPanoramaCondominio(condominio, sufixo) {
	if (condominio) {
		url = "rds.js.php?m=clientes.alertas&a=busca_panorama_condominio&condominio=" + condominio + "&sufixo=" + sufixo;
		RDSCarregar("clientes_alertas_condominio", url);
	} else {
		if (document.getElementById(sufixo + "panorama_condominio") != null) {
			document.getElementById(sufixo + "panorama_condominio").innerHTML = '';
		}
		if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
			document.getElementById("cobranca_panorama_condominio").innerHTML = '';
		}
	}
}

function buscaPanoramaBloco(bloco, sufixo) {
	if (bloco) {
		url = "rds.js.php?m=clientes.alertas&a=busca_panorama_bloco&bloco=" + bloco + "&sufixo=" + sufixo;
		RDSCarregar("clientes_alertas_condominio", url);
	} else {
		if (document.getElementById(sufixo + "panorama_bloco") != null) {
			document.getElementById(sufixo + "panorama_bloco").innerHTML = '';
		}
		if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
			document.getElementById("cobranca_panorama_bloco").innerHTML = '';
		}
	}
}

function verificaPendencias(redirecionar) {
	if (pendencias && pendencias.length > 0) {
		var plural = pendencias.length > 1 ? "s" : "";
		if (!redirecionar) {
			alert("A T E N Ç Ã O !\n\nEsse cliente possui o" + plural + " seguinte" + plural + " dado" + plural + " pendente" + plural + ":\n\n " + pendencias.join("\n ") + "\n\nFavor solicitá-lo" + plural + " e cadastrá-lo" + plural + " no sistema.\n\nObrigado.");
		} else {
			if (confirm("A T E N Ç Ã O !\n\nEsse cliente possui o" + plural + " seguinte" + plural + " dado" + plural + " pendente" + plural + ":\n\n " + pendencias.join("\n ") + "\n\nFavor solicitá-lo" + plural + " e cadastrá-lo" + plural + " no sistema.\n\nObrigado.\n\nDeseja fazer isso agora?")) {
				window.location = "?m=clientes&a=ver_dc&atualizar_cadastro=1&i=" + cliente_id;
			}
		}
	}
}

<?php

	if (false) { ?></script><?php }

}

?>
