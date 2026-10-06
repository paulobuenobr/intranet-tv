<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$pesquisas_aplicacoes = controle::instanciaObjeto("pesquisas_aplicacoes", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "avancar") {

  	// Grava Respostas
  	$_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["respostas"]["aberta"] = trim($_GET["resposta_aberta"]);
  	$_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["respostas"]["fechada"] = $_GET["resposta_fechada"] ? explode(",", $_GET["resposta_fechada"]) : null;
  	// Exibe Questão
		$_GET["questao_atual"]++;
	  if ($_GET["questao_atual"] <= $_GET["questao_total"]) {
			$pesquisas_aplicacoes->buscaDadosQuestao($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"], $_GET["questao_atual"]);
			$questoes = addslashes(str_replace(array("\r\n", "\n"), "<br>", $pesquisas_aplicacoes->geraQuestao($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"], $_GET["questao_atual"])));
			$respostas = addslashes($pesquisas_aplicacoes->geraResposta($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"], $_GET["questao_atual"]));
			print "document.getElementById('questao').innerHTML = '$questoes';";
			print "document.getElementById('resposta').innerHTML = '$respostas';";
		  print "document.getElementById('andamento').innerHTML = '{$_GET["questao_atual"]} / {$_GET["questao_total"]}';";
		  print "questao_atual = {$_GET["questao_atual"]};";
		  print "maximo_alternativas = {$_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["maximo_alternativas"]};";
		  print "numero_alternativas = ".count($_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["alternativas"]).";";
		  if ($_GET["questao_atual"] == $_GET["questao_total"]) {
		  	print "document.getElementById('avancar').value = 'Concluir';";
		  } else {
		  	print "document.getElementById('avancar').value = 'Avancar >>';";
		  }
	  } else {
	  	$pesquisas_aplicacoes->concluirAplicacao($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"]);
	  }

  } elseif ($_GET["a"] == "voltar") {

  	// Grava Respostas
  	$_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["respostas"]["aberta"] = trim($_GET["resposta_aberta"]);
  	$_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["respostas"]["fechada"] = $_GET["resposta_fechada"] ? explode(",", $_GET["resposta_fechada"]) : null;
  	// Exibe Questão
		if ($_GET["questao_atual"] == 1) {
			print "history.back();";
		} else {
			$_GET["questao_atual"]--;
			$pesquisas_aplicacoes->buscaDadosQuestao($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"], $_GET["questao_atual"]);
			$questoes = addslashes($pesquisas_aplicacoes->geraQuestao($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"], $_GET["questao_atual"]));
			$respostas = addslashes($pesquisas_aplicacoes->geraResposta($_GET["pesquisa_id"], $_GET["pesquisa_aplicacao_id"], $_GET["questao_atual"]));
			print "document.getElementById('questao').innerHTML = '$questoes';";
			print "document.getElementById('resposta').innerHTML = '$respostas';";
		  print "document.getElementById('andamento').innerHTML = '{$_GET["questao_atual"]} / {$_GET["questao_total"]}';";
		  print "questao_atual = {$_GET["questao_atual"]};";
		  print "maximo_alternativas = {$_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["maximo_alternativas"]};";
		  print "numero_alternativas = ".count($_SESSION["respostas"][$_GET["pesquisa_aplicacao_id"]][$_GET["questao_atual"]]["alternativas"]).";";
		  if ($_GET["questao_atual"] == $_GET["questao_total"]) {
		  	print "document.getElementById('avancar').value = 'Concluir';";
		  } else {
		  	print "document.getElementById('avancar').value = 'Avancar >>';";
		  }
		}

  } elseif ($_GET["a"] == "busca") {

    $listagem = $pesquisas_aplicacoes->geraListagemPesquisasAplicacoes($pesquisas_aplicacoes->buscaPesquisasAplicacoes($_GET["cliente_id"]));
    print "if (document.getElementById(\"pesquisas\")) document.getElementById(\"pesquisas\").innerHTML = '".addslashes($listagem)."';";

  }

} else {

	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaAdicionarPesquisasAplicacoes(formulario) {

	var erro_vazio = new Array();
  var erro_invalido = new Array();

	var pesquisa_selecionada = null;
	for (i = 0; i < formulario.pesquisa.length; i++)
		if (formulario.pesquisa[i].checked)
			pesquisa_selecionada = formulario.pesquisa[i].value;

  if (pesquisa_selecionada == null) {
    erro_vazio[erro_vazio.length] = "Pesquisa";
  }

  return apresentaErros(erro_vazio, erro_invalido);

}

function adicionarPesquisasAplicacoes(cliente_id) {
  if (cliente_id) {
  	abrir("pesquisas_aplicacoes", "?m=pesquisas_aplicacoes&a=adicionar&i=" + cliente_id, 575, 450, 1);
  }
}

function verPesquisasAplicacoes(pesquisa_aplicacao_id) {
  if (pesquisa_aplicacao_id) {
  	abrir("pesquisas_aplicacoes", "?m=pesquisas_aplicacoes&a=ver&i=" + pesquisa_aplicacao_id, 575, 450, 1);
  }
}

function buscaPesquisasAplicacoes(cliente_id) {
  if (cliente_id) {
		url = "rds.js.php?m=pesquisas_aplicacoes&a=busca&cliente_id=" + cliente_id;
		RDSCarregar("pesquisas_aplicacoes_busca", url);
  }
}

function aplicacaoAvancar() {
  var resposta_fechada = buscaRespostaFechada();
  var resposta_aberta = buscaRespostaAberta();
	url = "rds.js.php?m=pesquisas_aplicacoes&a=avancar&pesquisa_aplicacao_id=" + pesquisa_aplicacao_id + "&pesquisa_id=" + pesquisa_id + "&questao_atual=" + questao_atual + "&questao_total=" + questao_total + "&resposta_fechada=" + resposta_fechada + "&resposta_aberta=" + resposta_aberta;
	RDSCarregar("pesquisas_aplicacoes_avancar", url);
}

function aplicacaoVoltar() {
  var resposta_fechada = buscaRespostaFechada();
  var resposta_aberta = buscaRespostaAberta();
	url = "rds.js.php?m=pesquisas_aplicacoes&a=voltar&pesquisa_aplicacao_id=" + pesquisa_aplicacao_id + "&pesquisa_id=" + pesquisa_id + "&questao_atual=" + questao_atual + "&questao_total=" + questao_total + "&resposta_fechada=" + resposta_fechada + "&resposta_aberta=" + resposta_aberta;
	RDSCarregar("pesquisas_aplicacoes_voltar", url);
}

function buscaRespostaFechada() {
  var i = 1;
  var resposta = new Array();
  while (checkbox = document.getElementById('resposta_' + i)) {
  	if (checkbox.checked) {
	    resposta[resposta.length] = i;
  	}
    i++;
  }
  return resposta.join(",");
}

function buscaRespostaAberta() {
  if (document.getElementById('resposta_nominal')) {
    return document.getElementById('resposta_nominal').value;
  } 
  if (document.getElementById('resposta_ordinal')) {
    return document.getElementById('resposta_ordinal').value;
  }
  if (document.getElementById('resposta_discreta')) {
    return document.getElementById('resposta_discreta').value;
  }
  if (document.getElementById('resposta_continua')) {
    return document.getElementById('resposta_continua').value;
  }
  return "";
}

function clicaResposta(ordem) {
	if (document.getElementById('resposta_' + ordem).checked) {
		var assinalados = 0;
	  for (i = 1; i < numero_alternativas; i++) {
	    alternativa = (ordem + i - 1) % (numero_alternativas) + 1;
			assinalados = document.getElementById('resposta_' + alternativa).checked && assinalados < maximo_alternativas ? assinalados + 1 : assinalados;
			document.getElementById('resposta_' + alternativa).checked = document.getElementById('resposta_' + alternativa).checked && assinalados < maximo_alternativas;
	  }
  }
}

<?

	if (false) { ?></script><? }

}

?>
