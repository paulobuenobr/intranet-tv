<?

if ($_GET["a"]) {

	global $bancodedados;
	
  $central_telefonica = controle::instanciaObjeto("central_telefonica.asterisk", $bancodedados);
  
  if ($_GET["a"] == "logar_ramal" && intval($_GET["ramal"], 10)) {
    
    $_GET["ramal"] = intval($_GET["ramal"], 10);
    $central_telefonica->logarAgente($_GET["ramal"]);
    print "document.getElementById('ramal_controle').innerHTML = 'Aguarde...';";
    print "setLoginTimeout();";
    
  } elseif ($_GET["a"] == "discar" && $_GET["numero"]) {
    
    $central_telefonica->discar($_GET["numero"]);
    
  } elseif ($_GET["a"] == "chamada_recebida" && $_GET["numero"]) {
    
  	if ($_GET["numero"][0] == "a") {
  		$_GET["numero"] = substr($_GET["numero"], 1);
	    $mensagem = "Chamada do Agente {$_GET["numero"]}";
	    $agente = $central_telefonica->buscaAgente($_GET["numero"]);
	    if ($agente) {
	    	$mensagem .= " ($agente)";
	    }
  	} else {
	    $mensagem = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" height=\"18\" width=\"364\"><tr><td width=\"18\"><img src=\"imagens/telefone.png\" width=\"13\" height=\"13\"></td><td width=\"86\" align=\"center\" class=\"txtAzulEscuro11\">".$central_telefonica->formataTelefone($_GET["numero"])."</td>";
	    if (strlen($_GET["numero"]) == 10) {
	    	$clientes = $central_telefonica->buscaCliente($_GET["numero"]);
	    	if ($clientes) {
	  			print "if (chamado_atual == '0') verCliente({$clientes[0]["id"]}, {$clientes[0]["condominio_id"]});";
	  			if (count($clientes) == 1) {
	    			$mensagem .= "<td width=\"260\" class=\"txtAzulEscuro11\"><a href=\"javascript:verCliente({$clientes[0]["id"]}, {$clientes[0]["condominio_id"]})\" class=\"linkAzulEscuro11\">{$clientes[0]["nome"]}</a></td></tr></table>";
	  			} else {
	  				$mensagem .= "<td width=\"260\" class=\"txtAzulEscuro11\"><select class=\"inputBrancoAzulEscuro11\" style=\"height:18px;border:0;background-color:#E8E8E8;width:260px\">";
	  				foreach ($clientes as $cliente) {
	    				$mensagem .= "<option onclick=\"verCliente({$cliente["id"]}, {$cliente["condominio_id"]})\">{$cliente["nome"]}</option>";
	  				}
	  				$mensagem .= "</select></td></tr></table>";
	  			}
	    	} else {
	  				$mensagem .= "<td width=\"260\" class=\"txtAzulEscuro11\">Número não cadastrado</td></tr></table>";
	    	}
	    } else {
	    	$usuario = $central_telefonica->buscaUsuarioRamal($_GET["numero"]);
	    	if ($usuario) {
		    	$mensagem .= "<td width=\"260\" class=\"txtAzulEscuro11\">$usuario</td></tr></table>";
		    } else {
		    	$mensagem .= "<td width=\"260\" class=\"txtAzulEscuro11\">Ramal não cadastrado</td></tr></table>";
		    }
	    }
  	}
	  print "setMensagem('$mensagem');";
  	
  }
  
} else {
  
	if (false) { ?><script type="text/javascript"><? }
	
?>

function setMensagem(mensagem) {
  document.getElementById('asterisk_mensagem').innerHTML = mensagem;
}

function discar(numero) {
	if (numero) {
		url = "rds.js.php?m=central_telefonica&a=discar&numero=" + numero;
		RDSCarregar("central_telefonica_discar", url);
	}
}

function endCall(number) {
  setMensagem('Aguardando chamada');
}

function incomingCall(number) {
	url = "rds.js.php?m=central_telefonica&a=chamada_recebida&numero=" + number;
	RDSCarregar("central_telefonica_incoming_call", url);
}

function agentLogin(ramal) {
  agente_logado = true;
  clearInterval(login_timeout);
  setMensagem('Agente logado no ramal ' + ramal);
  document.getElementById('ramal_controle').innerHTML = '';
}

<?

	if (false) { ?></script><? }

}

?>
