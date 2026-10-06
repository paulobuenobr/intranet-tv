<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$pesquisas = controle::instanciaObjeto("pesquisas", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "exibir_respostas") {

    $listagem = $pesquisas->geraListagemRespostasAbertas($pesquisas->buscaRespostasAbertas($_GET["questao_id"]), $_GET["questao_id"]);
    print "if (document.getElementById(\"respostas_{$_GET["questao_id"]}\")) document.getElementById(\"respostas_{$_GET["questao_id"]}\").innerHTML = '".addslashes($listagem)."';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaAdicionarPesquisa(formulario) {
	var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.descricao.value) {
    erro_vazio[erro_vazio.length] = "Descrição";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

function exibirRespostas(questao_id) {
  if (questao_id) {
  	url = "rds.js.php?m=pesquisas&a=exibir_respostas&questao_id=" + questao_id;
  	RDSCarregar("pesquisas_exibir_respostas", url);
  }
}

<?

	if (false) { ?></script><? }

}

?>
