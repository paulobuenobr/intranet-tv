<?

	if (false) { ?><script type="text/javascript"><? }
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_consultas_credito = controle::instanciaObjeto("clientes_consultas_credito", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "buscar") {
    
    $listagem = $clientes_consultas_credito->geraListagemClientesConsultasCredito($clientes_consultas_credito->buscaClientesConsultasCredito($_GET["cliente_id"]));
    print "if (document.getElementById(\"consultas_credito\")) document.getElementById(\"consultas_credito\").innerHTML = '".addslashes($listagem)."';";
    
  }
	
?>

function buscaClientesConsultasCredito(cliente_id) {
  if (cliente_id) {
	  url = "rds.js.php?m=clientes_consultas_credito&a=buscar&cliente_id=" + cliente_id;
    RDSCarregar("clientes_consultas_credito_buscar", url);
	}
}

function verConsulta(consulta_id) {
	if (consulta_id) {
    abrir("consultas_credito", "?m=clientes_consultas_credito&a=ver&i=" + consulta_id, 625, 520, 1);
	}
}

function efetuarConsulta(cliente_id) {
	if (cliente_id) {
    abrir("consultas_credito", "?m=clientes_consultas_credito&a=consultar&i=" + cliente_id, 625, 520, 1);
	}
}

<?

	if (false) { ?></script><? }
	
?>
