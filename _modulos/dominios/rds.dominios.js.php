<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$dominios = controle::instanciaObjeto("dominios", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "busca_dominios" && $_GET["cli"]) {
  	
    $listagem = $dominios->geraClientesDominiosListagem($dominios->buscaClientesDominios($_GET["cli"]));
    print "if (document.getElementById(\"dominios_listagem\")) document.getElementById(\"dominios_listagem\").innerHTML = '".addslashes($listagem)."';";
    
  } elseif ($_GET["a"] == "checa_dominio" && $_GET["dom"]) {
  	
    $_GET["dom"] = preg_replace("/^www\\./", "", trim(strtolower($_GET["dom"])));
    if (substr($_GET["dom"], strlen($_GET["dom"]) - 3, strlen($_GET["dom"])) == ".br") {
      $pagina = implode("", file("http://registro.br/cgi-bin/nicbr/whois?qr={$_GET["dom"]}"));
      if (strpos($pagina, "Domínio inexistente:")) {
        $mensagem = "O domínio {$_GET["dom"]} encontra-se disponível!";
        $dominio_checado = "1";
      } else {
        $mensagem = "O domínio {$_GET["dom"]} já está registrado!";
        $dominio_checado = "0";
      }
    } else {
      $pagina = implode("", file("http://reports.internic.net/cgi/whois?type=domain&whois_nic={$_GET["dom"]}"));
      if (strpos($pagina,"No match for domain")) {
        $mensagem = "O domínio {$_GET["dom"]} encontra-se disponível!";
        $dominio_checado = "1";
      } else {
        $mensagem = "O domínio {$_GET["dom"]} já está registrado!";
        $dominio_checado = "0";
      }
    }
    print "alert('$mensagem');";
    print "dominio_checado = $dominio_checado;";
    
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var dominio_checado = 0, WDominio;

function buscaDominios(cliente, ver) {
  if (cliente) {
    url = "rds.js.php?m=dominios&a=busca_dominios&cli=" + cliente + "&ver=" + ver;
    RDSCarregar("dominios_busca_dominios", url);
  }
}

function verDominio(dominio) {
	if (dominio) {
    abrir("dominio", "?m=dominios&a=ver&i=" + dominio, 450, 450, 0);
	}
}

function checaDominio(dominio) {
  if (dominio) {
    if (validaDominio(dominio)) {
      url = "rds.js.php?m=dominios&a=checa_dominio&dom=" + dominio;
      RDSCarregar("dominios_checa_dominio", url);
    } else {
      alert('Domínio inválido!');
    }
  } else {
    alert('Domínio não preenchido!');
  }
}

function alertaDominioAdicionado(dominio) {
  if (dominio) {
    var alerta = '';
    alerta  = 'O Plano adicionado está vinculado à um domínio!\n';
    alerta += '\n';
    alerta += 'Deseja configurá-lo agora?';
    if (confirm(alerta)) {
      verDominio(dominio);
    }
  }
}

<?

	if (false) { ?></script><? }

}

?>
