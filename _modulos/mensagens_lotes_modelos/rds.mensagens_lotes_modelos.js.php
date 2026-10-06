<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$mensagens_lotes_modelos = controle::instanciaObjeto("mensagens_lotes_modelos", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "atualiza_listagem") {
  	
    $_GET = @unserialize(stripslashes($_GET["dados_get"]));
    $filtro = $mensagens_lotes_modelos->inicializaFiltro($_GET["f"]);
    $pagina = (!$_GET["p"]) ? 1 : $_GET["p"];
    $itens = $mensagens_lotes_modelos->buscaItens($filtro, $pagina);
    $paginacao = $mensagens_lotes_modelos->geraPaginacao($filtro, $itens["linhas"], $pagina);
    $listagem = $mensagens_lotes_modelos->geraListagem($itens["itens"]);
    print "if (document.getElementById(\"paginacao_1\")) document.getElementById(\"paginacao_1\").innerHTML = '".str_replace("'", '\\\'', $paginacao->conteudo)."';";
    print "if (document.getElementById(\"paginacao_2\")) document.getElementById(\"paginacao_2\").innerHTML = '".str_replace("'", '\\\'', $paginacao->conteudo)."';";
    print "if (document.getElementById(\"listagem\")) document.getElementById(\"listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem->conteudo)."';";
    
  }
  
} else {

	if (false) { ?><script type="text/javascript"><? }

?>

function validaMensagensLotesModelosAdicionar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.nome.value)
		erro_vazio[erro_vazio.length] = "Nome";
  if (!formulario.tipo.value)
		erro_vazio[erro_vazio.length] = "Tipo";
  if (!formulario.conteudo_texto.value)
		erro_vazio[erro_vazio.length] = "Conteúdo Texto";
  if (!formulario.tipo.value || formulario.tipo.value == '1') {
		if (!formulario.conteudo_html.value)
		  erro_vazio[erro_vazio.length] = "Conteúdo HTML";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaMensagensLotesModelosEditar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  if (!formulario.conteudo_texto.value)
		erro_vazio[erro_vazio.length] = "Conteúdo Texto";
	if (formulario.conteudo_html && !formulario.conteudo_html.value)
	  erro_vazio[erro_vazio.length] = "Conteúdo HTML";

  return apresentaErros(erro_vazio, erro_invalido);
}

function mudaTipo(valor) {
  if(document.getElementById('tipo').value == 2) {
    document.getElementById('conteudo_email_html').style.display = 'none';
  } else {
    document.getElementById('conteudo_email_html').style.display = (ehNetscape() ? 'table-row-group' : 'block');
  }
}

function atualizarListagem() {
  url = "rds.js.php?m=mensagens_lotes_modelos&a=atualiza_listagem" + "&dados_get=" + dados_get;
  RDSCarregar("mensagens_lotes_modelos_atualiza_listagem", url);
}

<?

	if (false) { ?></script><? }

}

?>
