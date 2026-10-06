<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$redes = controle::instanciaObjeto("redes", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "nova_rede") {
  	
  	if ($redes->adicionarNovaRede($_GET["rede"])) {
  		$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
	    print "document.getElementById(\"nova_rede\").focus();";
  	}
    
  } elseif ($_GET["a"] == "remove_rede") {
  	
  	$rede = $redes->removerRede($_GET["rede_id"]);
  	if ($rede) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia($rede), "'"));
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
	    print "document.getElementById(\"nova_rede\").focus();";
  	}
    
  } elseif ($_GET["a"] == "reduz_rede") {
  	
  	if ($redes->reduzRede($_GET["rede_id"])) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  	}
    
  } elseif ($_GET["a"] == "expande_rede") {
  	
  	if ($redes->expandeRede($_GET["rede_id"])) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  	}
    
  } elseif ($_GET["a"] == "define_servidor") {
  	
  	if ($_GET["distribuir"]) {
  		if (!($erro = $redes->distribuirInterfaces($_GET["rede_id"], $_GET["servidor_id"]))) {
		  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
			  print "document.getElementById('container_servidores').style.display = 'none';";
		    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  		} else {
  			print "alert('$erro');";
  		}
  	} else {
  		if (!($erro = $redes->defineServidor($_GET["rede_id"], $_GET["servidor_id"]))) {
	  		$redes->defineServidor($_GET["rede_id"], $_GET["servidor_id"]);
		  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
			  print "document.getElementById('container_servidores').style.display = 'none';";
		    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  		} else {
  			print "alert('$erro');";
  		}
  	}
  	print "document.getElementById('associar_servidor').disabled = false;";
	  print "document.getElementById('auto_distribuir_servidor').disabled = false;";
    
  } elseif ($_GET["a"] == "limpa_servidor") {
  	
  	if ($redes->limpaServidor($_GET["rede_id"], $_GET["servidor_id"])) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
			print "document.getElementById('container_informacoes').style.display = 'none';";
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  	}
    
  } elseif ($_GET["a"] == "define_interface") {
  	
  	if (!($erro = $redes->defineInterface($_GET["rede_id"], $_GET["interface_id"]))) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
		  print "document.getElementById('container_interfaces').style.display = 'none';";
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  	} else {
  		print "alert('$erro');";
  	}
    
  } elseif ($_GET["a"] == "limpa_interface") {
  	
  	if ($redes->limpaInterface($_GET["rede_id"])) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  	}
    
  } elseif ($_GET["a"] == "limpa_interfaces") {
  	
  	if ($redes->limpaInterfaces($_GET["interfaces"])) {
	  	$topologia = str_replace(array("\\\\'"), array("\\\\\\'"), addcslashes($redes->buscaTopologia(), "'"));
	    print "exibeInfo();";
	    print "document.getElementById(\"topologia\").innerHTML = '$topologia';";
  	}
    
  } elseif ($_GET["a"] == "exibe_servidores") {
  	
  	$servidores = $redes->buscaServidores();
  	if ($servidores) {
      foreach ($servidores as $servidor) {
        $servidores_array[] = 'new Array("'.$servidor["id"].'", "'.$servidor["nome"].'", "'.$servidor["grupo"].'")';
      }
    	print "atualizaSelect(document.getElementById('lista_servidores'), new Array(".@implode(", ", $servidores_array)."), '{$_GET["servidor_id"]}', false);";
		  print "var posicao = findPos(document.getElementById('celula_servidor_{$_GET["rede_id"]}'));";
		  if (count($servidores) == 1) {
			  print "document.getElementById('associar_servidor').disabled = false;";
			  print "document.getElementById('auto_distribuir_servidor').disabled = false;";
		  } else {
			  print "document.getElementById('associar_servidor').disabled = true;";
			  print "document.getElementById('auto_distribuir_servidor').disabled = true;";
		  }
		  print "document.getElementById('container_servidores').style.left = posicao[0] + 10;";
		  print "document.getElementById('container_servidores').style.top = posicao[1] + 10;";
		  print "document.getElementById('container_servidores').style.display = 'block';";
		  print "document.getElementById('lista_servidores').tag = '{$_GET["rede_id"]}';";
	    print "document.getElementById('lista_servidores').scrollTop = 0;";
		  print "document.getElementById('lista_servidores').focus();";
  	}
    
  } elseif ($_GET["a"] == "exibe_interfaces") {
  	
  	$interfaces = $redes->buscaInterfaces($_GET["servidor_id"]);
  	if ($interfaces) {
      foreach ($interfaces as $id => $nome) {
        $interfaces_array[] = 'new Array("'.$id.'", "'.$nome.'")';
      }
    	print "atualizaSelect(document.getElementById('lista_interfaces'), new Array(".@implode(", ", $interfaces_array)."), '{$_GET["interface_id"]}', false);";
		  print "var posicao = findPos(document.getElementById('celula_interface_{$_GET["rede_id"]}'));";
		  print "document.getElementById('container_interfaces').style.left = posicao[0] + 10;";
		  print "document.getElementById('container_interfaces').style.top = posicao[1] + 10;";
		  print "document.getElementById('container_interfaces').style.display = 'block';";
		  print "document.getElementById('lista_interfaces').tag = '{$_GET["rede_id"]}';";
		  if (!$_GET["interface_id"]) {
	    	print "document.getElementById('lista_interfaces').scrollTop = 0;";
		  }
		  print "document.getElementById('lista_interfaces').focus();";
  	} else {
  		print "alert('Nenhuma interface disponível!');";
  	}
    
  } elseif ($_GET["a"] == "exibe_info") {
  	
  	$conteudo = $redes->buscaInformacoes($_GET["servidor_id"]);
  	if ($conteudo) {
    	print "document.getElementById('container_informacoes').innerHTML = '$conteudo';";
		  print "var posicao = findPos(document.getElementById('celula_servidor_{$_GET["rede_id"]}'));";
		  print "document.getElementById('container_informacoes').style.left = posicao[0] + 10;";
		  print "document.getElementById('container_informacoes').style.top = posicao[1] + 10;";
		  print "document.getElementById('container_informacoes').style.display = 'block';";
		  print "document.getElementById('container_informacoes').servidor_id = '{$_GET["servidor_id"]}';";
		  print "document.getElementById('container_informacoes').rede_id = '{$_GET["rede_id"]}';";
  	}
    
  } elseif ($_GET["a"] == "define_dhcp") {
  	
    $redes->defineDHCP($_GET["rede_id"], $_GET["dhcp"]);
    
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function novaRede() {
	if (document.getElementById('nova_rede').value) {
  	exibePainelCarregando();
	  url = "rds.js.php?m=redes&a=nova_rede&rede=" + document.getElementById('nova_rede').value;
		RDSCarregar("redes_nova_rede", url);
	}
}

function removeRede(rede_id) {
	if (confirm("Tem certeza que deseja remover esta classse?")) {
	  exibePainelCarregando();
	  url = "rds.js.php?m=redes&a=remove_rede&rede_id=" + rede_id;
		RDSCarregar("redes_remove_rede", url);
	}
}

function expandeRede(rede_id) {
  exibePainelCarregando();
  url = "rds.js.php?m=redes&a=expande_rede&rede_id=" + rede_id;
	RDSCarregar("redes_expande_rede", url);
}

function reduzRede(rede_id) {
  exibePainelCarregando();
  url = "rds.js.php?m=redes&a=reduz_rede&rede_id=" + rede_id;
	RDSCarregar("redes_reduz_rede", url);
}

function defineDHCP(rede_id, valor) {
  exibePainelCarregando();
  url = "rds.js.php?m=redes&a=define_dhcp&rede_id=" + rede_id + "&dhcp=" + (valor ? 1 : 0);
	RDSCarregar("redes_define_dhcp", url);
}

function defineServidor(rede_id, servidor_id) {
  if (!document.getElementById('associar_servidor').disabled) {
  	exibePainelCarregando();
	  document.getElementById('associar_servidor').disabled = true;
	  document.getElementById('auto_distribuir_servidor').disabled = true;
	  url = "rds.js.php?m=redes&a=define_servidor&rede_id=" + rede_id + "&servidor_id=" + servidor_id;
		RDSCarregar("redes_define_servidor", url);
  }
}

function autoDistribuir(rede_id, servidor_id) {
  if (!document.getElementById('auto_distribuir_servidor').disabled) {
  	exibePainelCarregando();
	  document.getElementById('associar_servidor').disabled = true;
	  document.getElementById('auto_distribuir_servidor').disabled = true;
	  url = "rds.js.php?m=redes&a=define_servidor&rede_id=" + rede_id + "&servidor_id=" + servidor_id + "&distribuir=" + 1;
		RDSCarregar("redes_define_servidor", url);
  }
}

function limpaServidor(rede_id, servidor_id) {
  servidor_id = (servidor_id == undefined) ? "" : servidor_id;
	if (confirm("Tem certeza que deseja desatribuir o servidor desta rede?")) {
  	exibePainelCarregando();
	  url = "rds.js.php?m=redes&a=limpa_servidor&rede_id=" + rede_id + "&servidor_id=" + servidor_id;
		RDSCarregar("redes_limpa_servidor", url);
	}
}

function limpaServidores(servidor_id) {
	if (confirm("Tem certeza que deseja desatribuir todas as redes deste servidor?")) {
  	exibePainelCarregando();
	  url = "rds.js.php?m=redes&a=limpa_servidor&servidor_id=" + servidor_id;
		RDSCarregar("redes_limpa_servidor", url);
	}
}

function defineInterface(rede_id, interface_id) {
  exibePainelCarregando();
  url = "rds.js.php?m=redes&a=define_interface&rede_id=" + rede_id + "&interface_id=" + interface_id;
	RDSCarregar("redes_define_interface", url);
}

function limpaInterface(rede_id, interface_id) {
	if (confirm("Tem certeza que deseja desatribuir a interface desta rede?")) {
  	exibePainelCarregando();
	  url = "rds.js.php?m=redes&a=limpa_interface&rede_id=" + rede_id + "&interface_id=" + interface_id;
		RDSCarregar("redes_limpa_interface", url);
	}
}

function limpaInterfaces() {
  var i = 0;
  var interfaces = [];
  while (checkbox = document.getElementById('checkbox_interfaces_' + i)) {
    if (checkbox.checked) {
      interfaces[interfaces.length] = "interfaces[" + interfaces.length + "]" + "=" + checkbox.value;
    }
    i++;
  }
  if (interfaces.length > 0 && confirm("Tem certeza que deseja desatribuir as redes das interfaces selecionadas?")) {
  	exibePainelCarregando();
	  url = "rds.js.php?m=redes&a=limpa_interfaces&" + interfaces.join("&");
		RDSCarregar("redes_limpa_interfaces", url);
  }
}

function exibeServidores(rede_id, servidor_id) {
  exibePainelCarregando();
  url = "rds.js.php?m=redes&a=exibe_servidores&rede_id=" + rede_id + "&servidor_id=" + servidor_id;
	RDSCarregar("redes_exibe_servidores", url);
}

function exibeInterfaces(rede_id, servidor_id, interface_id) {
  exibePainelCarregando();
  url = "rds.js.php?m=redes&a=exibe_interfaces&rede_id=" + rede_id + "&servidor_id=" + servidor_id + "&interface_id=" + interface_id;
	RDSCarregar("redes_exibe_interfaces", url);
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft;
		curtop = obj.offsetTop;
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		}
	}
	return [curleft, curtop];
}

function exibeInfo(rede_id, servidor_id) {
	exibePainelCarregando();
  rede_id = rede_id ? rede_id : document.getElementById('container_informacoes').rede_id;
  servidor_id = servidor_id ? servidor_id : document.getElementById('container_informacoes').servidor_id;
  url = "rds.js.php?m=redes&a=exibe_info&rede_id=" + rede_id + "&servidor_id=" + servidor_id;
	RDSCarregar("redes_exibe_info", url);
}

function interfacesSelectAll(estado) {
  var i = 0;
  while (checkbox = document.getElementById('checkbox_interfaces_' + i)) {
    checkbox.checked = estado;
    i++;
  }
}

function exibePainelCarregando() {
  if (document.getElementById('carregando')) document.getElementById('carregando').style.top = document.body.scrollTop + 2;
  if (document.getElementById('carregando')) document.getElementById('carregando').style.left = document.body.clientWidth - 80;
  if (document.getElementById('carregando')) document.getElementById('carregando').style.display = 'block';
  if (document.getElementById('bloqueio')) document.getElementById('bloqueio').style.width = document.body.clientWidth;
  if (document.getElementById('bloqueio')) document.getElementById('bloqueio').style.height = document.body.clientHeight;
  if (document.getElementById('bloqueio')) document.getElementById('bloqueio').style.display = 'block';
}

function ocultaPainelCarregando() {
  if (document.getElementById('carregando')) document.getElementById('carregando').style.display = 'none';
  if (document.getElementById('bloqueio')) document.getElementById('bloqueio').style.display = 'none';
}

<?

	if (false) { ?></script><? }

}

?>

if (window.ocultaPainelCarregando) ocultaPainelCarregando();
