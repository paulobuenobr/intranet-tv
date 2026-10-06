<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_responsabilidades = controle::instanciaObjeto("clientes_responsabilidades", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "busca_clientes_responsabilidades" && $_GET["cli"]) {
    $responsabilidades = $clientes_responsabilidades->geraListagemClientesResponsabilidades($clientes_responsabilidades->buscaClientesResponsabilidades($_GET["cli"]));
    $responsavel = $clientes_responsabilidades->buscaClienteResponsavel($_GET["cli"]);
    print "if (document.getElementById(\"responsabilidades\")) document.getElementById(\"responsabilidades\").innerHTML = '".str_replace("'", '\\\'', $responsabilidades)."';";
    print "if (document.getElementById(\"cliente_responsavel\")) document.getElementById(\"cliente_responsavel\").innerHTML = '".$responsavel."';";
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscarClienteResponsabilidades(cliente) {
  if (cliente) {
	  url = "rds.js.php?m=clientes_responsabilidades&a=busca_clientes_responsabilidades&cli=" + cliente;
    RDSCarregar("cliente_responsabilidades_busca_clientes_responsabilidades", url);
	}
}

function verClienteResponsabilidade(responsabilidade) {
	if (responsabilidade) {
    abrir("cliente_responsabilidade", "?m=clientes_responsabilidades&a=ver&i=" + responsabilidade, 450, 520, 1);
	}
}

function adicionarClienteResponsabilidade(cliente) {
	if (cliente) {
    abrir("cliente_responsabilidade", "?m=clientes_responsabilidades&a=adicionar&i=" + cliente, 450, 520, 1);
	}
}

function validaClienteResponsabilidade (formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.categoria.value) erro_vazio[erro_vazio.length] = "Categoria";
  if (!formulario.responsavel.value) erro_vazio[erro_vazio.length] = "Responsável";

  return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
