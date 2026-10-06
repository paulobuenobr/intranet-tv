<?

if ($_GET && array_key_exists("a", $_GET)) {
  
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_negativacoes = controle::instanciaObjeto("clientes_negativacoes", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "buscar") {
    
    $listagem = $clientes_negativacoes->geraListagemClientesNegativacoes($clientes_negativacoes->buscaClientesNegativacoes($_GET["cliente_id"]));
    print "if (document.getElementById(\"clientes_negativacoes\")) document.getElementById(\"clientes_negativacoes\").innerHTML = '".addslashes($listagem)."';";
    
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscaClientesNegativacoes(cliente_id) {
  if (cliente_id) {
	  url = "rds.js.php?m=clientes_negativacoes&a=buscar&cliente_id=" + cliente_id;
    RDSCarregar("clientes_negativacoes_buscar", url);
	}
}

function adicionarClienteNegativacao(cliente_id) {
	if (cliente_id) {
    abrir("clientes_negativacoes", "?m=clientes_negativacoes&a=adicionar&i=" + cliente_id, 500, 450, 1);
	}
}

function verClienteNegativacao(cliente_negativacao_id) {
	if (cliente_negativacao_id) {
	    abrir("clientes_negativacoes", "?m=clientes_negativacoes&a=ver&i=" + cliente_negativacao_id, 500, 450, 1);
	}
}

function validaClienteNegativacaoAdicionar(formulario) {

	  var erro_vazio = new Array();
	  var erro_invalido = new Array();
	  var erro_competencia_inicio = false;

	  if (!formulario.codigo.value) {
	    erro_vazio[erro_vazio.length] = "Código";
	  }
	  
	  if (!formulario.data_inclusao.value) {
		  erro_vazio[erro_vazio.length] = "Data de inclusão";
	  } else if (!validaData(formulario.data_inclusao.value)) {
	    erro_invalido[erro_invalido.length] = "Data de inclusão";
	  }

	  return apresentaErros(erro_vazio, erro_invalido);
		
}

function validaClienteNegativacaoBaixar(formulario) {

	  var erro_vazio = new Array();
	  var erro_invalido = new Array();
	  var erro_competencia_inicio = false;

	  if (!formulario.data_baixa.value) {
		  erro_vazio[erro_vazio.length] = "Data de baixa";
	  } else if (!validaData(formulario.data_baixa.value)) {
	    erro_invalido[erro_invalido.length] = "Data de baixa";
	  }

	  if (!formulario.status.value) {
		  erro_vazio[erro_vazio.length] = "Status";
	  }
	  
	  return apresentaErros(erro_vazio, erro_invalido);
		
}

<?

	if (false) { ?></script><? }

}

?>
