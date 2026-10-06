<?

if ($_GET["a"]) {
	
	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$servidores_interfaces = controle::instanciaObjeto("servidores_interfaces", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "busca_interfaces") {

    $interfaces = $servidores_interfaces->buscaInterfaces($_GET["servidor_id"], $_GET["tipo_id"]);
    foreach ($interfaces AS $interface) {
      $lista_interfaces[] = "new Array(\"{$interface["id"]}\", \"{$interface["nome"]}\")";
    }
    print "var lista_interfaces = new Array(".@implode(",", $lista_interfaces).");";
    print "atualizaSelect(document.getElementById(\"interface\"), lista_interfaces);";

  } elseif ($_GET["a"] == "buscar_servidores_interfaces") {

    $interfaces = $servidores_interfaces->geraServidoresInterfacesListagem($servidores_interfaces->buscaServidoresInterfaces($_GET["servidor"]));
    print "if (document.getElementById(\"interfaces\")) document.getElementById(\"interfaces\").innerHTML = '".str_replace("'", '\\\'', $interfaces)."';";

  } elseif ($_GET["a"] == "verifica_ip_existente") {

    $query = "
			SELECT
				tab_servidores.ser_nome AS nome
			FROM
				tab_servidores
			WHERE
				tab_servidores.ser_endereco_ip = {$_GET["endereco_ip"]} AND
				tab_servidores.sei_id = {$_GET["interface"]}";
    $query = $bancodedados->query($query);
    $cliente_ponto = $bancodedados->resultadoArray($query);
    if ($cliente_ponto) {
      $mensagem = "O Endereço IP \"{$_GET["prefixo"]}.{$_GET["endereco_ip"]}\" já está sendo utilizado pelo servidor \"".addslashes($cliente_ponto["nome"])."\".";
      print "alert('$mensagem');";
      print "document.getElementById(\"endereco_ip\").value = '';";
      print "document.getElementById(\"endereco_ip\").focus();";
    } else {
      $query = "
				SELECT
				  tab_servidores.ser_nome AS servidor_nome,
					tab_servidores_interfaces.sei_id AS id
				FROM
					tab_servidores_interfaces
					INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
				WHERE
					tab_servidores_interfaces.sei_prefixo_ip = '{$_GET["prefixo"]}' AND
					tab_servidores_interfaces.sei_endereco_ip = {$_GET["endereco_ip"]} AND
					tab_servidores_interfaces.sei_id <> {$_GET["interface"]} AND
			    tab_servidores.sep_id <> 2";
      $query = $bancodedados->query($query);
      $dados = $bancodedados->resultadoArray($query);
      if ($dados) {
        $mensagem = "O Endereço IP \"{$_GET["prefixo"]}.{$_GET["endereco_ip"]}\" já está sendo utilizado por outra interface no servidor <{$dados["servidor_nome"]}>.";
        print "alert('$mensagem');";
        print "document.getElementById(\"endereco_ip\").value = '';";
        print "document.getElementById(\"endereco_ip\").focus();";
      }
    }
    
  } elseif ($_GET["a"] == "valida_nome") {

    $_GET["nome"] = trim(strtoupper($_GET["nome"]));
    $query = "
  		SELECT
  			tab_servidores_interfaces.sei_id AS id
  		FROM
  			tab_servidores_interfaces
  		WHERE
  			tab_servidores_interfaces.sei_nome = '{$_GET["nome"]}' AND
  			tab_servidores_interfaces.ser_id = {$_GET["servidor"]} AND
  			tab_servidores_interfaces.sei_id <> {$_GET["interface"]}";
    $query = $bancodedados->query($query);
    $cliente_ponto = $bancodedados->resultadoArray($query);
    if ($cliente_ponto) {
      $mensagem = "O nome \"{$_GET["nome"]}\" já está sendo utilizado por outra interface neste servidor.";
      print "alert('$mensagem');";
      print "document.getElementById(\"nome\").value = '';";
      print "document.getElementById(\"nome\").focus();";
    }

  } elseif ($_GET["a"] == "adicionar_carp") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["lista_carp"])));

    // Verifica CARP Repetida (Nome ou IP)
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["nome"] == $_GET["nome"] && $chave != ($_GET["id"] - 1)) {
        print "alert('CARP já cadastrada nesta interface!');";
        die();
      }
      if (($_GET["endereco_ip_1"] && $_GET["endereco_ip_2"] && $_GET["endereco_ip_3"] && $_GET["endereco_ip_4"]) && $valor["endereco_ip"] == "{$_GET["endereco_ip_1"]}.{$_GET["endereco_ip_2"]}.{$_GET["endereco_ip_3"]}.{$_GET["endereco_ip_4"]}" && $chave != ($_GET["id"] - 1)) {
        print "alert('IP já cadastrado nesta interface!');";
        die();
      }
    }
    $interface = $servidores_interfaces->buscaServidorInterfaceNome($_GET["servidor_id"], $_GET["nome"]);
    if ($interface) {
      if ($interface != $_GET["interface_id"]) {
        print "alert('Interface \'{$_GET["nome"]}\' já cadastrada neste servidor!');";
        die();
      }
    }
    if (($_GET["endereco_ip_1"] && $_GET["endereco_ip_2"] && $_GET["endereco_ip_3"] && $_GET["endereco_ip_4"]) && ($interface = $servidores_interfaces->buscaServidorInterfaceIP($_GET["servidor_id"], "{$_GET["endereco_ip_1"]}.{$_GET["endereco_ip_2"]}.{$_GET["endereco_ip_3"]}", $_GET["endereco_ip_4"]))) {
      if ($interface != $_GET["interface_id"]) {
        print "alert('Interface \'{$_GET["endereco_ip_1"]}.{$_GET["endereco_ip_2"]}.{$_GET["endereco_ip_3"]}.{$_GET["endereco_ip_4"]}\' já cadastrada neste servidor!');";
        die();
      }
    }
    // Trata novos itens
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1]["nome"] = $_GET["nome"];
      $itens_atuais[$_GET["id"] - 1]["endereco_ip"] = ($_GET["endereco_ip_1"] && $_GET["endereco_ip_2"] && $_GET["endereco_ip_3"] && $_GET["endereco_ip_4"]) ? "{$_GET["endereco_ip_1"]}.{$_GET["endereco_ip_2"]}.{$_GET["endereco_ip_3"]}.{$_GET["endereco_ip_4"]}" : "";
      $itens_atuais[$_GET["id"] - 1]["mascara_rede"] = ($_GET["mascara_rede_1"] && $_GET["mascara_rede_2"] && $_GET["mascara_rede_3"] && $_GET["mascara_rede_4"]) ? "{$_GET["mascara_rede_1"]}.{$_GET["mascara_rede_2"]}.{$_GET["mascara_rede_3"]}.{$_GET["mascara_rede_4"]}" : "";
    } else {
      $novo_item["nome"] = $_GET["nome"];
      $novo_item["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $novo_item["nome"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $novo_item["nome"]));
      $novo_item["endereco_ip"] = ($_GET["endereco_ip_1"] && $_GET["endereco_ip_2"] && $_GET["endereco_ip_3"] && $_GET["endereco_ip_4"]) ? "{$_GET["endereco_ip_1"]}.{$_GET["endereco_ip_2"]}.{$_GET["endereco_ip_3"]}.{$_GET["endereco_ip_4"]}" : "";
      $novo_item["mascara_rede"] = ($_GET["mascara_rede_1"] && $_GET["mascara_rede_2"] && $_GET["mascara_rede_3"] && $_GET["mascara_rede_4"]) ? "{$_GET["mascara_rede_1"]}.{$_GET["mascara_rede_2"]}.{$_GET["mascara_rede_3"]}.{$_GET["mascara_rede_4"]}" : "";
      $itens_atuais[] = $novo_item;
    }
    $itens_atuais = $servidores_interfaces->ordena_multiarray($itens_atuais, array("ordem"));
    $listagem = $servidores_interfaces->geraListagemCarp($itens_atuais, true);
    print "if (document.getElementById('listagem_carp')) document.getElementById('listagem_carp').innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById('lista_carp')) document.getElementById('lista_carp').value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById('carp_nome')) document.getElementById('carp_nome').focus();";
    print "carp_linha_id = 0;";

  } elseif ($_GET["a"] == "remover_carp") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_carp"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    if ($item["id"] && ($erros = $servidores_interfaces->buscaErros($item["id"]))) {
      $erros = "Foram encontratos os seguintes vínculos à esta interface:<br><br><b>$erros</b><br><br>Não é possível continuar com a remoção.";
      $erros = str_replace(array("&nbsp;", "&raquo;", "<br>", "<b>", "</b>"), array(" ", "»", "\\n", "", ""), $erros);
      print "alert('$erros');";
      exit;
    }
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $servidores_interfaces->geraListagemCarp($itens_atuais, true);
    print "if (document.getElementById('listagem_carp')) document.getElementById('listagem_carp').innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById('lista_carp')) document.getElementById('lista_carp').value = '".serialize($itens_atuais)."';";
    $item["endereco_ip"] = explode(".", $item["endereco_ip"]);
    $item["mascara_rede"] = explode(".", $item["mascara_rede"]);
    $rede = $servidores_interfaces->buscaRede($item["id"]);
    if ($rede) {
	    print "document.getElementById('carp_endereco_ip_1').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_2').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_3').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_4').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_1').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_2').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_3').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_4').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_1').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_endereco_ip_2').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_endereco_ip_3').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_endereco_ip_4').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_1').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_2').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_3').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_4').style.backgroundColor = '#E8E8E8';";
    } else {
	    print "document.getElementById('carp_endereco_ip_1').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_2').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_3').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_4').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_1').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_2').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_3').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_4').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_1').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_endereco_ip_2').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_endereco_ip_3').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_endereco_ip_4').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_1').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_2').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_3').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_4').style.backgroundColor = '#ffffff';";
    }
    print "document.getElementById('carp_nome').value = '{$item["nome"]}';";
    print "document.getElementById('carp_endereco_ip_1').value = '{$item["endereco_ip"][0]}';";
    print "document.getElementById('carp_endereco_ip_2').value = '{$item["endereco_ip"][1]}';";
    print "document.getElementById('carp_endereco_ip_3').value = '{$item["endereco_ip"][2]}';";
    print "document.getElementById('carp_endereco_ip_4').value = '{$item["endereco_ip"][3]}';";
    print "document.getElementById('carp_mascara_rede_1').value = '{$item["mascara_rede"][0]}';";
    print "document.getElementById('carp_mascara_rede_2').value = '{$item["mascara_rede"][1]}';";
    print "document.getElementById('carp_mascara_rede_3').value = '{$item["mascara_rede"][2]}';";
    print "document.getElementById('carp_mascara_rede_4').value = '{$item["mascara_rede"][3]}';";
    print "document.getElementById('carp_nome').focus();";
    print "carp_linha_id = 0;";

  } elseif ($_GET["a"] == "selecionar_carp") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_carp"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    $item["endereco_ip"] = explode(".", $item["endereco_ip"]);
    $item["mascara_rede"] = explode(".", $item["mascara_rede"]);
    $rede = $servidores_interfaces->buscaRede($item["id"]);
    if ($rede) {
	    print "document.getElementById('carp_endereco_ip_1').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_2').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_3').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_4').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_1').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_2').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_3').disabled = true;";
	    print "document.getElementById('carp_mascara_rede_4').disabled = true;";
	    print "document.getElementById('carp_endereco_ip_1').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_endereco_ip_2').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_endereco_ip_3').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_endereco_ip_4').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_1').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_2').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_3').style.backgroundColor = '#E8E8E8';";
	    print "document.getElementById('carp_mascara_rede_4').style.backgroundColor = '#E8E8E8';";
    } else {
	    print "document.getElementById('carp_endereco_ip_1').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_2').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_3').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_4').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_1').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_2').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_3').disabled = false;";
	    print "document.getElementById('carp_mascara_rede_4').disabled = false;";
	    print "document.getElementById('carp_endereco_ip_1').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_endereco_ip_2').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_endereco_ip_3').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_endereco_ip_4').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_1').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_2').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_3').style.backgroundColor = '#ffffff';";
	    print "document.getElementById('carp_mascara_rede_4').style.backgroundColor = '#ffffff';";
    }
    print "document.getElementById('carp_nome').value = '{$item["nome"]}';";
    print "document.getElementById('carp_endereco_ip_1').value = '{$item["endereco_ip"][0]}';";
    print "document.getElementById('carp_endereco_ip_2').value = '{$item["endereco_ip"][1]}';";
    print "document.getElementById('carp_endereco_ip_3').value = '{$item["endereco_ip"][2]}';";
    print "document.getElementById('carp_endereco_ip_4').value = '{$item["endereco_ip"][3]}';";
    print "document.getElementById('carp_mascara_rede_1').value = '{$item["mascara_rede"][0]}';";
    print "document.getElementById('carp_mascara_rede_2').value = '{$item["mascara_rede"][1]}';";
    print "document.getElementById('carp_mascara_rede_3').value = '{$item["mascara_rede"][2]}';";
    print "document.getElementById('carp_mascara_rede_4').value = '{$item["mascara_rede"][3]}';";
    print "document.getElementById('carp_nome').focus();";

  } elseif ($_GET["a"] == "adicionar_classe") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["lista_pool"])));

    $_GET["classe"] = $servidores_interfaces->corrigeClasse($_GET["classe"]);
    // Verifica Classe Repetida
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["classe"] == $_GET["classe"] && $chave != ($_GET["id"] - 1)) {
        print "alert('Classe já cadastrada nesta interface!');";
        die();
      }
    }
    // Trata novos itens
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1]["classe"] = $_GET["classe"];
    } else {
      $novo_item["classe"] = $_GET["classe"];
      $itens_atuais[] = $novo_item;
    }
    sort($itens_atuais);
    $listagem = $servidores_interfaces->geraListagemPool($itens_atuais, true);
    print "if (document.getElementById('listagem_pool')) document.getElementById('listagem_pool').innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById('lista_pool')) document.getElementById('lista_pool').value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById('classe_1')) document.getElementById('classe_1').focus();";
    print "pool_linha_id = 0;";

  } else if ($_GET["a"] == "adicionar_ssid") {
  
    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["lista_ssid"])));

    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1]["ssid"] = $_GET["ssid"];
      $itens_atuais[$_GET["id"] - 1]["ip"] = $_GET["ip"];
      $itens_atuais[$_GET["id"] - 1]["tipo"] = $_GET["tipo"];
    } else {
      $novo_item["ssid"] = $_GET["ssid"];
      $novo_item["ip"] = $_GET["ip"];
      $novo_item["tipo"] = $_GET["tipo"];
      $itens_atuais[] = $novo_item;
    }
    //sort($itens_atuais);
    $listagem = $servidores_interfaces->geraListagemSSID($itens_atuais, true);
    print "if (document.getElementById('listagem_ssid')) document.getElementById('listagem_ssid').innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById('lista_ssid')) document.getElementById('lista_ssid').value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById('ssid')) document.getElementById('ssid').focus();";
    print "ssid_linha_id = 0;";

  } elseif ($_GET["a"] == "remover_classe") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_pool"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $servidores_interfaces->geraListagemPool($itens_atuais, true);
    print "if (document.getElementById('listagem_pool')) document.getElementById('listagem_pool').innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById('lista_pool')) document.getElementById('lista_pool').value = '".serialize($itens_atuais)."';";
    $classe = explode("/", $item["classe"]);
    $classe[0] = explode(".", $classe[0]);
    print "document.getElementById('classe_1').value = '{$classe[0][0]}';";
    print "document.getElementById('classe_2').value = '{$classe[0][1]}';";
    print "document.getElementById('classe_3').value = '{$classe[0][2]}';";
    print "document.getElementById('classe_4').value = '{$classe[0][3]}';";
    print "document.getElementById('classe_5').value = '{$classe[1]}';";
    print "document.getElementById('classe_1').focus();";
    print "pool_linha_id = 0;";
  	
  } elseif ($_GET["a"] == "remover_ssid") {
  	
    $itens_atuais = @unserialize(stripslashes($_GET["lista_ssid"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $servidores_interfaces->geraListagemSSID($itens_atuais, true);
    print "if (document.getElementById('listagem_ssid')) document.getElementById('listagem_ssid').innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById('lista_ssid')) document.getElementById('lista_ssid').value = '".serialize($itens_atuais)."';";
    print "document.getElementById('ssid').value = '{$item["ssid"]}';";
    print "document.getElementById('ssid').focus();";
    print "ssid_linha_id = 0;";
    
  } elseif ($_GET["a"] == "selecionar_classe") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_pool"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    $classe = explode("/", $item["classe"]);
    $classe[0] = explode(".", $classe[0]);
    print "document.getElementById('classe_1').value = '{$classe[0][0]}';";
    print "document.getElementById('classe_2').value = '{$classe[0][1]}';";
    print "document.getElementById('classe_3').value = '{$classe[0][2]}';";
    print "document.getElementById('classe_4').value = '{$classe[0][3]}';";
    print "document.getElementById('classe_5').value = '{$classe[1]}';";
    print "document.getElementById('classe_1').focus();";
    
  } elseif ($_GET["a"] == "selecionar_ssid") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_ssid"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    $ssid = $item["ssid"];
    $ip = $item["ip"];
    $ip = explode(".", $ip);
    $tipo = $item["tipo"];
    print "document.getElementById('ssid').value = '$ssid';";
    print "document.getElementById('ip_1').value = '{$ip[0]}';";
    print "document.getElementById('ip_2').value = '{$ip[1]}';";
    print "document.getElementById('ip_3').value = '{$ip[2]}';";
    print "document.getElementById('ip_4').value = '{$ip[3]}';";
    print "document.getElementById('ssid_tipo').value = '{$tipo}';";
    print "document.getElementById('ssid').focus();";
    
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function mudaTipoInterface(tipo) {
  if (!tipo) {
    document.getElementById('dados_ip').style.display = 'none';
    document.getElementById('dados_carp').style.display = 'none';
  } else if (tipo == 6) {
    document.getElementById('dados_ip').style.display = 'none';
    document.getElementById('dados_carp').style.display = (ehNetscape() ? 'table-row-group' : 'block');
  } else {
    document.getElementById('dados_ip').style.display = (ehNetscape() ? 'table-row-group' : 'block');
    document.getElementById('dados_carp').style.display = 'none';
  }
}

function buscaServidoresInterfaces(servidor) {
	if (servidor) {
		url = "rds.js.php?m=servidores_interfaces&a=buscar_servidores_interfaces&servidor=" + servidor;
		RDSCarregar("servidores_interfaces_buscar_servidores_interfaces", url);
	}
}

function verServidorInterface(interface_id, tipo) {
	if (interface_id) {
	  tipo = tipo ? tipo : 'dc';
    abrir("servidor_interface", "?m=servidores_interfaces&a=ver_" + tipo + "&i=" + interface_id, 470, 447, 1);
	}
}

function adicionarServidorInterface(servidor) {
	if (servidor) {
    abrir("servidor_interface", "?m=servidores_interfaces&a=adicionar&servidor_id=" + servidor, 470, 447, 1);
	}
}

function validaEnderecoIPServidorInterface(campo, interface_id) {
	if (campo.form.prefixo_ip_1.value && campo.form.prefixo_ip_3.value && campo.form.prefixo_ip_3.value && campo.form.endereco_ip.value) {
		prefixo = campo.form.prefixo_ip_1.value + '.' + campo.form.prefixo_ip_2.value + '.' + campo.form.prefixo_ip_3.value;
		url = "rds.js.php?m=servidores_interfaces&a=verifica_ip_existente&endereco_ip=" + campo.form.endereco_ip.value + "&interface=" + interface_id + "&prefixo=" + prefixo;
		RDSCarregar("servidores_interfaces_verifica_ip_existente", url);
	}
}

function validaNome(campo, servidor, interface_id) {
	if (campo.value) {
		url = "rds.js.php?m=servidores_interfaces&a=valida_nome&nome=" + campo.value + "&servidor=" + servidor + "&interface=" + interface_id;
		RDSCarregar("servidores_interfaces_valida_nome", url);
	}
}

function alteraSituacaoPadrao(padrao) {
	padrao.form.essid.disabled = padrao.value != 3;
	padrao.form.chave_criptografia.disabled = padrao.value != 3;
	padrao.form.canal.disabled = padrao.value != 3;
	if (padrao.value != 3) {
		padrao.form.essid.value = "";
		padrao.form.chave_criptografia.value = "";
		padrao.form.canal.value = "";
	}
	return true;
}

function alteraTipo(tipo, quantidade) {
	for (i = 1; i <= quantidade; i++) {
		document.getElementById("servico_" + i).disabled = tipo.value != 1;
		if (tipo.value != 1) {
		  document.getElementById("servico_" + i).checked = false;
		}
	}
	return true;
}

function validaServidoresInterfaces(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.nome.value) {
		erro_vazio[erro_vazio.length] = "Nome";
	}
	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	} else if (formulario.tipo.value != 6) {
  	endereco_ip = formulario.prefixo_ip_1.value + "." + formulario.prefixo_ip_2.value + "." + formulario.prefixo_ip_3.value + "." + formulario.endereco_ip.value;
  	if (endereco_ip == "...") {
  	  if (formulario.tipo.value != 1) {
  		  erro_vazio[erro_vazio.length] = "Endereço IP";
  	  }
  	} else if (!validaMascaraIP(endereco_ip)) {
  		erro_invalido[erro_invalido.length] = "Endereço IP";
  	}
  	mascara_rede = formulario.mascara_rede_1.value + "." + formulario.mascara_rede_2.value + "." + formulario.mascara_rede_3.value + "." + formulario.mascara_rede_4.value;
  	if (mascara_rede == "...") {
  	  if (formulario.tipo.value != 1) {
    		erro_vazio[erro_vazio.length] = "Máscara de Rede";
  	  }
  	} else if (!validaMascaraIP(mascara_rede)) {
  		erro_invalido[erro_invalido.length] = "Máscara de Rede";
  	}
	}
	if (!formulario.padrao.value) {
		erro_vazio[erro_vazio.length] = "Padrao";
	}
	if (formulario.padrao.value == 3 && !formulario.essid.value) {
		erro_vazio[erro_vazio.length] = "ESSID";
	}
	if (formulario.padrao.value == 3 && !formulario.chave_criptografia.value) {
		erro_vazio[erro_vazio.length] = "Chave de Criptografia";
	}
	if (formulario.padrao.value == 3 && !formulario.canal.value) {
		erro_vazio[erro_vazio.length] = "Canal";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaServico(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.servicos.value) erro_vazio[erro_vazio.length] = "Serviço";
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaMigrar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.servidor.value) erro_vazio[erro_vazio.length] = "Servidor";
	// if (!formulario.interface.value) erro_vazio[erro_vazio.length] = "Interface";
	return apresentaErros(erro_vazio, erro_invalido);
}

function validaInterfaceCarp(interface) {
  var filtro = /^carp[_]*[0-9]{1,3}$/;
  return filtro.test(interface);
}

/////////////////////
// Interfaces CARP //
/////////////////////

var carp_linha_id = 0;

function adicionarCarp() {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

	if (!document.getElementById('carp_nome').value) {
		erro_vazio[erro_vazio.length] = "Nome";
	} else if (!validaInterfaceCarp(document.getElementById('carp_nome').value)) {
		erro_invalido[erro_invalido.length] = "Nome";
	}

  if (apresentaErros(erro_vazio, erro_invalido)) {
	  url = "rds.js.php?m=servidores_interfaces&a=adicionar_carp&nome=" + document.getElementById('carp_nome').value + "&endereco_ip_1=" + document.getElementById('carp_endereco_ip_1').value + "&endereco_ip_2=" + document.getElementById('carp_endereco_ip_2').value + "&endereco_ip_3=" + document.getElementById('carp_endereco_ip_3').value + "&endereco_ip_4=" + document.getElementById('carp_endereco_ip_4').value + "&mascara_rede_1=" + document.getElementById('carp_mascara_rede_1').value + "&mascara_rede_2=" + document.getElementById('carp_mascara_rede_2').value + "&mascara_rede_3=" + document.getElementById('carp_mascara_rede_3').value + "&mascara_rede_4=" + document.getElementById('carp_mascara_rede_4').value + "&lista_carp=" + document.getElementById('lista_carp').value + "&interface_id=" + interface_id + "&servidor_id=" + servidor_id + "&id=" + carp_linha_id;
    RDSCarregar("servidores_interfaces_adicionar_carp", url);
  }

}

function removerCarp(id) {
  if (id && confirm('Tem certeza que deseja remover este CARP?')) {
	  url = "rds.js.php?m=servidores_interfaces&a=remover_carp&id=" + id + "&lista_carp=" + document.getElementById('lista_carp').value;
    RDSCarregar("servidores_interfaces_remover_carp", url);
  }
}

function selecionarCarp(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  carp_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("carp_nome").value = "";
        document.getElementById("carp_endereco_ip_1").value = "";
        document.getElementById("carp_endereco_ip_2").value = "";
        document.getElementById("carp_endereco_ip_3").value = "";
        document.getElementById("carp_endereco_ip_4").value = "";
        document.getElementById("carp_mascara_rede_1").value = "";
        document.getElementById("carp_mascara_rede_2").value = "";
        document.getElementById("carp_mascara_rede_3").value = "";
        document.getElementById("carp_mascara_rede_4").value = "";
	      document.getElementById("carp_endereco_ip_1").disabled = false;
	      document.getElementById("carp_endereco_ip_2").disabled = false;
	      document.getElementById("carp_endereco_ip_3").disabled = false;
	      document.getElementById("carp_endereco_ip_4").disabled = false;
	      document.getElementById("carp_mascara_rede_1").disabled = false;
	      document.getElementById("carp_mascara_rede_2").disabled = false;
	      document.getElementById("carp_mascara_rede_3").disabled = false;
	      document.getElementById("carp_mascara_rede_4").disabled = false;
	      document.getElementById("carp_endereco_ip_1").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_endereco_ip_2").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_endereco_ip_3").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_endereco_ip_4").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_mascara_rede_1").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_mascara_rede_2").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_mascara_rede_3").style.backgroundColor = "#ffffff";
	      document.getElementById("carp_mascara_rede_4").style.backgroundColor = "#ffffff";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        carp_linha_id = id;
        selecionarLinhaCarp();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function selecionarLinhaCarp() {
  if (carp_linha_id) {
	  url = "rds.js.php?m=servidores_interfaces&a=selecionar_carp&id=" + carp_linha_id + "&lista_carp=" + document.getElementById('lista_carp').value;
    RDSCarregar("servidores_interfaces_selecionar_carp", url);
  }
}

function buscaInterfaces(servidor_id, tipo_id) {
  if (servidor_id) {
	  url = "rds.js.php?m=servidores_interfaces&a=busca_interfaces&servidor_id=" + servidor_id + "&tipo_id=" + tipo_id;
    RDSCarregar("servidores_interfaces_busca_interfaces", url);
  } else {
    document.getElementById('interface').value = "";
    document.getElementById('interface').disabled = true;
  }
}

function verServidor(servidor_id) {
  if (window.opener) {
    window.opener.location = "./?m=servidores&a=ver_di&i=" + servidor_id;
  }
}

/////////////////////
// Interfaces Pool //
/////////////////////

var pool_linha_id = 0;

function adicionarClasse() {

  var erro_vazio = new Array();
  var erro_invalido = new Array();
  
	var classe = document.getElementById('classe_1').value + "." + document.getElementById('classe_2').value + "." + document.getElementById('classe_3').value + "." + document.getElementById('classe_4').value + "/" + document.getElementById('classe_5').value;

	if (!document.getElementById('classe_1').value && !document.getElementById('classe_2').value && !document.getElementById('classe_3').value && !document.getElementById('classe_4').value && !document.getElementById('classe_5').value) {
		erro_vazio[erro_vazio.length] = "Classe";
	} else if (!validaClasse(classe) || document.getElementById('classe_5').value > 32) {
		erro_invalido[erro_invalido.length] = "Classe";
	}

  if (apresentaErros(erro_vazio, erro_invalido)) {
	  url = "rds.js.php?m=servidores_interfaces&a=adicionar_classe&classe=" + classe + "&lista_pool=" + document.getElementById('lista_pool').value + "&interface_id=" + interface_id + "&servidor_id=" + servidor_id + "&id=" + pool_linha_id;
    RDSCarregar("servidores_interfaces_adicionar_classe", url);
  }
}

function validaClasse(classe) {
  var filtro = /^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2}$/;
  return filtro.test(classe);
}

function validaIP(ip) {
  var filtro = /^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/;
  return filtro.test(ip);
}

function removerClasse(id) {
  if (id && confirm('Tem certeza que deseja remover esta classe?')) {
	  url = "rds.js.php?m=servidores_interfaces&a=remover_classe&id=" + id + "&lista_pool=" + document.getElementById('lista_pool').value;
    RDSCarregar("servidores_interfaces_remover_classe", url);
  }
}


function selecionarClasse(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  pool_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha.firstChild) : (tabela.rows(i) == linha.firstChild)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("classe_1").value = "";
        document.getElementById("classe_2").value = "";
        document.getElementById("classe_3").value = "";
        document.getElementById("classe_4").value = "";
        document.getElementById("classe_5").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        pool_linha_id = id;
        selecionarLinhaClasse();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function selecionarLinhaClasse() {
  if (pool_linha_id) {
	  url = "rds.js.php?m=servidores_interfaces&a=selecionar_classe&id=" + pool_linha_id + "&lista_pool=" + document.getElementById('lista_pool').value;
    RDSCarregar("servidores_interfaces_selecionar_pool", url);
  }
}

////////////////////////
//      SSID's       //
///////////////////////

var ssid_linha_id = 0;

function adicionarSSID() {

  var erro_vazio = new Array();
  var erro_invalido = new Array();
	
	var ssid = document.getElementById('ssid').value;
	var ip = document.getElementById('ip_1').value + "." +  document.getElementById('ip_2').value + "." + document.getElementById('ip_3').value + "." + document.getElementById('ip_4').value;
	var tipo = document.getElementById('ssid_tipo').value;

	if (!ssid) {
		erro_vazio[erro_vazio.length] = "SSID";
	}
	
	if (ip == '...') {
		erro_vazio[erro_vazio.length] = "IP";
	} else if (!validaIP(ip)) {
		erro_invalido[erro_invalido.length] = "IP";
	}


	if (!tipo) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	
  if (apresentaErros(erro_vazio, erro_invalido)) {
		url = "rds.js.php?m=servidores_interfaces&a=adicionar_ssid&ssid=" + ssid + "&ip=" + ip + "&tipo=" + tipo + "&lista_ssid=" + document.getElementById('lista_ssid').value + "&interface_id=" + interface_id + "&servidor_id=" + servidor_id + "&id=" + ssid_linha_id;
	  RDSCarregar("servidores_interfaces_adicionar_ssid", url);
  }
}

function removerSSID(id) {
  if (id && confirm('Tem certeza que deseja remover este ssid?')) {
	url = "rds.js.php?m=servidores_interfaces&a=remover_ssid&id=" + id + "&lista_ssid=" + document.getElementById('lista_ssid').value;
    RDSCarregar("servidores_interfaces_remover_ssid", url);
  }
}

function selecionarSSID(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  
  ssid_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha.firstChild) : (tabela.rows(i) == linha.firstChild)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("ssid").value = "";
        document.getElementById("ssid_tipo").value = "";
        document.getElementById("ip_1").value = "";
        document.getElementById("ip_2").value = "";
        document.getElementById("ip_3").value = "";
        document.getElementById("ip_4").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        ssid_linha_id = id;
        selecionarLinhaSSID();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function selecionarLinhaSSID() {
  if (ssid_linha_id) {
	  url = "rds.js.php?m=servidores_interfaces&a=selecionar_ssid&id=" + ssid_linha_id + "&lista_ssid=" + document.getElementById('lista_ssid').value;
    RDSCarregar("servidores_interfaces_selecionar_ssid", url);
  }
}

<?

	if (false) { ?></script><? }

}

?>
