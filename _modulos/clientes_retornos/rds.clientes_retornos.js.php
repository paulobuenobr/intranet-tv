<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_retornos = controle::instanciaObjeto("clientes_retornos", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "busca_clientes_retornos" && $_GET["cli"]) {
    $retornos = $clientes_retornos->geraListagemClientesRetornos($clientes_retornos->buscaClientesRetornos($_GET["cli"]));
    print "if (document.getElementById(\"retornos\")) document.getElementById(\"retornos\").innerHTML = '".addslashes($retornos)."';";
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscarClienteRetornos(cliente) {
  if (cliente) {
	  url = "rds.js.php?m=clientes_retornos&a=busca_clientes_retornos&cli=" + cliente;
    RDSCarregar("cliente_retornos_busca_clientes_retornos", url);
	}
}

function verClienteRetorno(retorno) {
	if (retorno) {
    abrir("clientes_retorno", "?m=clientes_retornos&a=ver&i=" + retorno, 450, 305, 0);
	}
}

function adicionarClienteRetorno(cliente) {
	if (cliente) {
    abrir("clientes_retorno", "?m=clientes_retornos&a=adicionar&cli=" + cliente, 450, 305, 0);
	}
}

<?

	if (false) { ?></script><? }

}

?>
