<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios_blocos = controle::instanciaObjeto("condominios_blocos", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "busca_condominios_blocos" && $_GET["con"]) {

  	if ($_GET["bloco_unico"] == "true") {
    	$query = "DELETE FROM tab_condominios_blocos WHERE con_id = '{$_GET["con"]}'";
      $query = $bancodedados->query($query);
    	$query = "INSERT INTO tab_condominios_blocos (cob_data_cadastro, cob_nome, con_id) VALUES (NOW(), 'Único','{$_GET["con"]}')";
      $query = $bancodedados->query($query);
      print "if (document.getElementById(\"blocos_adicionar\")) { document.getElementById(\"blocos_adicionar\").disabled = true; }";
  	} elseif ($_GET["bloco_unico"] == "false") {
    	$query = "DELETE FROM tab_condominios_blocos WHERE con_id = '{$_GET["con"]}' AND cob_nome = 'Único'";
      $query = $bancodedados->query($query);
      print "if (document.getElementById(\"blocos_adicionar\")) { document.getElementById(\"blocos_adicionar\").disabled = false; }";
  	}
    // Atualiza Listagem
  	$blocos = $condominios_blocos->buscaCondominioBlocos($_GET["con"]);
  	print "if (document.getElementById(\"blocos\")) { document.getElementById(\"blocos\").innerHTML = '".str_replace("'", '\\\'', $condominios_blocos->geraListagemCondominioBlocos($blocos))."'; }";
  	// Atualiza Select
  	foreach ($blocos as $bloco) {
    	$js_blocos[] = "new Array(\"{$bloco["id"]}\", \"{$bloco["nome"]}\")";
  	}
  	print "var {$_GET["sufixo"]}blocos = new Array(".@implode(",", $js_blocos).");";
  	print "if (document.getElementById(\"{$_GET["sufixo"]}bloco\")) atualizaSelect(document.getElementById(\"{$_GET["sufixo"]}bloco\"), {$_GET["sufixo"]}blocos, ".(($_GET["valor"]) ? $_GET["valor"] : $_GET["sufixo"]."bloco_ativo").");";
  	if (!$_GET["sufixo"]) {
  	  print "if (document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) atualizaSelect(document.getElementById(\"cobranca_bloco\"), blocos, ".(($_GET["valor"]) ? $_GET["valor"] : "0").", 1, 1, 0);";
  	}
    print "if (!{$_GET["sufixo"]}bloco_ativo) var {$_GET["sufixo"]}bloco_ativo; else mudaClienteBloco(document.getElementById(\"{$_GET["sufixo"]}bloco\"), '{$_GET["sufixo"]}');";

    if (count($blocos) > 1) {
      print "if (document.getElementById(\"contatos_adicionar\")) document.getElementById(\"contatos_adicionar\").disabled = false;";
    	print "if (document.getElementById(\"bloco_unico\")) document.getElementById(\"bloco_unico\").disabled = true;";
    } else {
      print "if (document.getElementById(\"contatos_adicionar\")) document.getElementById(\"contatos_adicionar\").disabled = true;";
    	print "if (document.getElementById(\"bloco_unico\")) document.getElementById(\"bloco_unico\").disabled = false;";
    }

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscaBlocos(condominio, ver, sufixo, valor, selecionar_unico) {
  if (!sufixo) sufixo = '';
  if (!valor) valor = '';
  if (!selecionar_unico) selecionar_unico = '';
  if (condominio) {
	  url = "rds.js.php?m=condominios_blocos&a=busca_condominios_blocos&con=" + condominio + "&ver=" + ver + "&sufixo=" + sufixo + "&valor=" + valor + "&selecionar_unico=" + selecionar_unico;
    RDSCarregar("condominios_blocos_busca_condominios_blocos", url);
	}
}

function mudaBlocoUnico(bloco_unico) {
  if (condominio_id) {
	  url = "rds.js.php?m=condominios_blocos&a=busca_condominios_blocos&con=" + condominio_id + "&bloco_unico=" + bloco_unico;
    RDSCarregar("condominios_blocos_busca_condominios_blocos", url);
	}
}

function verBloco(bloco) {
	if (bloco) {
    abrir("bloco", "?m=condominios_blocos&a=ver&i=" + bloco, 450, 380, 0);
	}
}

function adicionarBloco(condominio) {
	if (condominio) {
    abrir("bloco", "?m=condominios_blocos&a=adicionar&c=" + condominio, 450, 380, 0);
	}
}

<?

	if (false) { ?></script><? }

}

?>
