<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_pontos = controle::instanciaObjeto("clientes_pontos", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "buscar_pontos" && $_GET["cliente"]) {

    $listagem = $clientes_pontos->geraClientesPontosListagem($clientes_pontos->buscaClientesPontos($_GET["cliente"]));
    $botao_adicionar = $clientes_pontos->geraBotaoAdicionar($_GET["cliente"]);
    print "if (document.getElementById(\"pontos_listagem\")) document.getElementById(\"pontos_listagem\").innerHTML = '".addslashes($listagem)."';";
    print "if (document.getElementById(\"pontos_adicionar\")) document.getElementById(\"pontos_adicionar\").innerHTML = '".addslashes($botao_adicionar)."';";

  } elseif ($_GET["a"] == "muda_assinatura" && $_GET["assinatura_id"]) {

    $velocidade = $clientes_pontos->buscaVelocidadeAssinatura($_GET["assinatura_id"]);
    $servico_id = $clientes_pontos->buscaServicoAssinatura($_GET["assinatura_id"]);
    print "if (document.getElementById(\"velocidade\")) document.getElementById(\"velocidade\").innerHTML = '$velocidade';";
    $interfaces = $clientes_pontos->buscaInterfacesAssinatura($_GET["interface_id"] && in_array($servico_id, array(1, 32)) ? true : false, $_GET["interface_id"]);
    if ($interfaces) {
      foreach ($interfaces AS $id => $nome) {
        $lista_interfaces[] = "new Array(\"$id\", \"$nome\")";
      }
      print "var lista_interfaces = new Array(".@implode(",", $lista_interfaces).");";
      print "atualizaSelect(document.getElementById(\"interface\"), lista_interfaces);";
      if (!$seguranca->permissoes["clientes_pontos"]["alterar_interface"]) {
        print "document.getElementById(\"interface\").disabled = true;";
      } else {
        print "document.getElementById(\"interface\").disabled = false;";
      }
    } else {
      print "alert('Sem interface disponível para esta assinatura!');";
    }

  } elseif ($_GET["a"] == "muda_interface" && $_GET["interface_id"]) {

    $ip = $clientes_pontos->buscaIPLivre($_GET["interface_id"], $_GET["assinatura_id"], $_GET["ponto_id"], true);
    print "document.getElementById(\"prefixo_ip_1\").value = '$ip[0]';";
    print "document.getElementById(\"prefixo_ip_2\").value = '$ip[1]';";
    print "document.getElementById(\"prefixo_ip_3\").value = '$ip[2]';";
    print "document.getElementById(\"endereco_ip\").value = '$ip[3]';";

  } elseif ($_GET["a"] == "verifica_mac_existente") {

    $_GET["mac"] = strtolower($_GET["mac"]);
    $query = "
     	SELECT
  	   	tab_clientes.cli_id AS cliente_id,
  	   	tab_clientes.cli_nome AS cliente_nome,
  	   	tab_clientes_pontos.clp_ip AS ip
     	FROM
  	  	tab_clientes_pontos
  	  	INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
  	  	INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
     	WHERE
  	  	tab_clientes_pontos.clp_endereco_mac = '{$_GET["mac"]}' AND
  	  	tab_clientes_pontos.clp_id != '{$_GET["ponto_id"]}' AND
  	  	tab_clientes_pontos.cps_id != 3";
    $query = $bancodedados->query($query);
    $mac = $bancodedados->resultadoArray($query);
    if ($mac) {
      $erro = "Este MAC já está sendo utilizado pelo cliente [{$mac["cliente_id"]}] {$mac["cliente_nome"]}".($mac["ip"] ? ", Endereço IP: {$mac["ip"]}" : "");
    } elseif ($clientes_pontos->ehMacEmpresa($_GET["mac"])) {
      $erro = "Este não é um MAC válido (Laptop Técnica)";
    } elseif ($clientes_pontos->ehMacInvalido($_GET["mac"], $_GET["ponto_id"])) {
      $erro = "Este não é um MAC válido";
    }
    if ($erro) {
      print "alert('$erro');";
      print "if (document.getElementById(\"mac\")) document.getElementById(\"mac\").value = '';";
      print "if (document.getElementById(\"mac\")) document.getElementById(\"mac\").focus();";
    }

  } elseif ($_GET["a"] == "captura_mac" && $_GET["servidor_id"]) {

  	$servidor = $clientes_pontos->buscaDadosServidor($_GET["servidor_id"]);
  	if ($servidor) {
    	$lista_mac = $clientes_pontos->capturaMacServidor($servidor["ip"], $servidor["sistema_operacional"]);
    	$lista_mac = $clientes_pontos->trataListaMAC($lista_mac);
    	$lista_mac = $clientes_pontos->formataListaMAC($lista_mac);
  	}
    if ($lista_mac) {
      print "clearInterval(intervaloOcultaMAC);";
      print "intervaloOcultaMAC = 0;";
      print "if (document.getElementById(\"lista_macs_conteudo\")) document.getElementById(\"lista_macs_conteudo\").innerHTML = '$lista_mac';";
      print "if (document.getElementById(\"lista_macs\")) document.getElementById(\"lista_macs\").style.display = 'block';";
    } else {
      print "if (document.getElementById(\"lista_macs\")) document.getElementById(\"lista_macs\").style.display = 'none';";
      print "alert('O servidor não retornou nenhum MAC sendo negado!');";
    }
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var intervaloOcultaMAC;

function buscaPontos(id, ver) {
	if (id) {
		url = "rds.js.php?m=clientes_pontos&a=buscar_pontos&cliente=" + id + "&ver=" + ver;
		RDSCarregar("clientes_pontos_buscar_pontos", url);
	}
}

function verPonto(ponto) {
	if (ponto) {
    abrir("clientes_pontos", "?m=clientes_pontos&a=ver&i=" + ponto, 525, 450, 1);
	}
}

function adicionarPonto(cliente) {
	if (cliente) {
    abrir("clientes_pontos", "?m=clientes_pontos&a=adicionar&i=" + cliente, 525, 450, 1);
	}
}

function mudaAssinatura(assinatura) {
  document.getElementById("velocidade").innerHTML = '';
  document.getElementById("interface").value = '';
  document.getElementById("interface").disabled = true;
  mudaInterface('');
  if (assinatura) {
  	url = "rds.js.php?m=clientes_pontos&a=muda_assinatura&assinatura_id=" + assinatura + "&interface_id=" + interface_id;
		RDSCarregar("clientes_pontos_muda_assinatura", url);
  }
}

function mudaInterface(interface) {
  document.getElementById("prefixo_ip_1").value = '';
  document.getElementById("prefixo_ip_2").value = '';
  document.getElementById("prefixo_ip_3").value = '';
  document.getElementById("endereco_ip").value = '';
  if (interface) {
  	url = "rds.js.php?m=clientes_pontos&a=muda_interface&interface_id=" + interface + "&assinatura_id=" + document.getElementById('assinatura').value + "&ponto_id=" + ponto_id;
		RDSCarregar("clientes_pontos_muda_interface", url);
  }
}

function verificaMacExistente(campo, ponto_id, sei_id) {
	mac = campo.value;
	if (mac.length == 17) {
  	url = "rds.js.php?m=clientes_pontos&a=verifica_mac_existente&mac=" + mac + "&ponto_id=" + ponto_id + "&sei_id=" + sei_id;
		RDSCarregar("clientes_pontos_verifica_mac_existente", url);
	}
}

function capturarMAC(servidor) {
	if (servidor) {
    if (document.getElementById("lista_macs_conteudo")) document.getElementById("lista_macs_conteudo").innerHTML = "<div class='txtAzulEscuro11'>Aguarde...</div>";
    if (document.getElementById("lista_macs")) document.getElementById("lista_macs").style.display = 'block';
    if (document.getElementById("mac")) document.getElementById("mac").focus();
  	url = "rds.js.php?m=clientes_pontos&a=captura_mac&servidor_id=" + servidor;
		RDSCarregar("clientes_pontos_captura_mac", url);
	}
}

function enviarMAC(mac) {
  if (mac) {
  	if (document.getElementById("mac")) document.getElementById("mac").value = mac;
    if (document.getElementById("mac")) document.getElementById("mac").focus();
    if (document.getElementById("lista_macs")) document.getElementById("lista_macs").style.display = "none";
  }
}

function ocultarMAC() {
  if (intervaloOcultaMAC) {
    if (document.getElementById("lista_macs")) document.getElementById("lista_macs").style.display = "none";
    clearInterval(intervaloOcultaMAC);
    intervaloOcultaMAC = 0;
  } else if (document.getElementById("lista_macs").style.display == "block") {
    intervaloOcultaMAC = setInterval('ocultarMAC()', 500);
  }
}

function validaClientePontos(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.assinatura && !formulario.assinatura.value) {
		erro_vazio[erro_vazio.length] = "Assinatura";
	}
	if (formulario.interface && !formulario.interface.value) {
		erro_vazio[erro_vazio.length] = "Interface";
	}
//	if (!formulario.mac.value) {
//		erro_vazio[erro_vazio.length] = "MAC";
//	} else
	if (formulario.mac.value && !validaMAC(formulario.mac.value)) {
		erro_invalido[erro_invalido.length] = "MAC";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
