<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$questoes = controle::instanciaObjeto("pesquisas_questoes", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "buscar_questoes") {

	  $listagem = str_replace(array("\r\n", "\n"), "", $questoes->geraListagemQuestoes($questoes->buscaQuestoes($_GET["pesquisa_id"])));
    print "if (document.getElementById(\"questoes\")) document.getElementById(\"questoes\").innerHTML = '".addslashes($listagem)."';";

  } elseif ($_GET["a"] == "adicionar_alternativa") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["lista_alternativas"])));
    // verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if (strtoupper(trim($valor["conteudo"])) == strtoupper(trim($_GET["conteudo"])) &&
          $chave != $_GET["id"] - 1) {
        print "alert('Alternativa já cadastrada para esta questão!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["ordem"] = $_GET["ordem"] === "" ? count($itens_atuais) + 1 : ($_GET["id"] && $itens_atuais[$_GET["id"] - 1]["ordem"] > intval($_GET["ordem"], 10) ? intval($_GET["ordem"], 10) - 1 : intval($_GET["ordem"], 10) + 1);
    $novo_item["conteudo"] = trim($_GET["conteudo"]);
		$novo_item["conteudo"][0] = strtoupper($novo_item["conteudo"][0]);
    if ($_GET["variavel_tipo_id"] <= 2) {
	    $novo_item["minimo"] = "";
	    $novo_item["maximo"] = "";
    } else {
	    if ($_GET["variavel_tipo_id"] == 3) {
    		$novo_item["minimo"] = intval(str_replace(",", ".", $_GET["minimo"]), 10);
    		$novo_item["maximo"] = intval(str_replace(",", ".", $_GET["maximo"]), 10);
	    } else {
    		$novo_item["minimo"] = number_format(intval(str_replace(",", ".", $_GET["minimo"]), 10), 2, ",", ".");
    		$novo_item["maximo"] = number_format(intval(str_replace(",", ".", $_GET["maximo"]), 10), 2, ",", ".");
	    }
    }
    $novo_item["eliminar"] = trim($_GET["eliminar"]);
    if ($_GET["id"]) {
    	$novo_item["id"] = $itens_atuais[$_GET["id"] - 1]["id"];
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    $itens_atuais = $questoes->reordenaAlternativas($itens_atuais);
    $listagem = $questoes->geraListagemAlternativas($_GET["tipo_id"], $_GET["variavel_tipo_id"], $itens_atuais, true);
    print "if (document.getElementById(\"listagem_alternativas\")) document.getElementById(\"listagem_alternativas\").innerHTML = '".str_replace("'", "\\'", $listagem)."';";
    print "if (document.getElementById(\"lista_alternativas\")) document.getElementById(\"lista_alternativas\").value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById(\"alternativa_conteudo\")) document.getElementById(\"alternativa_conteudo\").focus();";
    print "alternativa_linha_id = 0;";

  } elseif ($_GET["a"] == "remover_alternativa") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_alternativas"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $itens_atuais = $questoes->reordenaAlternativas($itens_atuais);
    $listagem = $questoes->geraListagemAlternativas($_GET["tipo_id"], $_GET["variavel_tipo_id"], $itens_atuais, true);
    print "if (document.getElementById(\"listagem_alternativas\")) document.getElementById(\"listagem_alternativas\").innerHTML = '".str_replace("'", "\\'", $listagem)."';";
    print "if (document.getElementById(\"lista_alternativas\")) document.getElementById(\"lista_alternativas\").value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById(\"alternativa_ordem\")) document.getElementById(\"alternativa_ordem\").value = '{$item["ordem"]}';";
    print "if (document.getElementById(\"alternativa_conteudo\")) document.getElementById(\"alternativa_conteudo\").value = '{$item["conteudo"]}';";
    print "if (document.getElementById(\"alternativa_minimo\")) document.getElementById(\"alternativa_minimo\").value = '{$item["minimo"]}';";
    print "if (document.getElementById(\"alternativa_maximo\")) document.getElementById(\"alternativa_maximo\").value = '{$item["maximo"]}';";
    print "if (document.getElementById(\"alternativa_conteudo\")) document.getElementById(\"alternativa_conteudo\").focus();";
    print "alternativa_linha_id = 0;";

  } elseif ($_GET["a"] == "selecionar_alternativa") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_alternativas"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    print "if (document.getElementById(\"alternativa_ordem\")) document.getElementById(\"alternativa_ordem\").value = '{$item["ordem"]}';";
    print "if (document.getElementById(\"alternativa_conteudo\")) document.getElementById(\"alternativa_conteudo\").value = '{$item["conteudo"]}';";
    print "if (document.getElementById(\"alternativa_minimo\")) document.getElementById(\"alternativa_minimo\").value = '{$item["minimo"]}';";
    print "if (document.getElementById(\"alternativa_maximo\")) document.getElementById(\"alternativa_maximo\").value = '{$item["maximo"]}';";
    print "if (document.getElementById(\"alternativa_eliminar\")) document.getElementById(\"alternativa_eliminar\").value = '{$item["eliminar"]}';";

  } elseif ($_GET["a"] == "muda_tipo") {

  	if ($_GET["tipo_id"] > 1) {
	    $itens_atuais = @unserialize(stripslashes($_GET["lista_alternativas"]));
	    if ($_GET["variavel_tipo_id"] == 3) {
	    	for ($i = 0; $i < count($itens_atuais); $i++) {
	    		$itens_atuais[$i]["minimo"] = intval(str_replace(",", ".", $itens_atuais[$i]["minimo"]), 10);
	    		$itens_atuais[$i]["maximo"] = intval(str_replace(",", ".", $itens_atuais[$i]["maximo"]), 10);
	    	}
	    } elseif ($_GET["variavel_tipo_id"] == 4) {
	    	for ($i = 0; $i < count($itens_atuais); $i++) {
	    		$itens_atuais[$i]["minimo"] = number_format(intval(str_replace(",", ".", $itens_atuais[$i]["minimo"]), 10), 2, ",", ".");
	    		$itens_atuais[$i]["maximo"] = number_format(intval(str_replace(",", ".", $itens_atuais[$i]["maximo"]), 10), 2, ",", ".");
	    	}
	    } else {
	    	for ($i = 0; $i < count($itens_atuais); $i++) {
	    		$itens_atuais[$i]["minimo"] = "";
	    		$itens_atuais[$i]["maximo"] = "";
	    	}
	    }
  	} else {
	    $itens_atuais = null;
  	}
    $listagem = $questoes->geraListagemAlternativas($_GET["tipo_id"], $_GET["variavel_tipo_id"], $itens_atuais, true);
    print "if (document.getElementById(\"listagem_alternativas\")) document.getElementById(\"listagem_alternativas\").innerHTML = '".str_replace("'", "\\'", $listagem)."';";
    print "if (document.getElementById(\"lista_alternativas\")) document.getElementById(\"lista_alternativas\").value = '".serialize($itens_atuais)."';";
    print "alternativa_linha_id = 0;";

  }

} else {

	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaQuestao(formulario) {
	var erro_vazio = new Array();
  var erro_invalido = new Array();
  if (!formulario.conteudo.value) {
    erro_vazio[erro_vazio.length] = "Conteúdo";
  }
  if (!formulario.tipo.value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }
  if (!formulario.variavel.value) {
    erro_vazio[erro_vazio.length] = "Variável";
  }
  if (formulario.tipo.value == 1 && !formulario.maximo_alternativas.disabled && !formulario.maximo_alternativas.value) {
    erro_vazio[erro_vazio.length] = "Máximo";
  }
  return apresentaErros(erro_vazio, erro_invalido);

}

function buscaQuestoes(pesquisa_id) {
	if (pesquisa_id) {
		url = "rds.js.php?m=pesquisas_questoes&a=buscar_questoes&pesquisa_id=" + pesquisa_id;
		RDSCarregar("pesquisas_questoes_buscar_questoes", url);
	}
}

function adicionarQuestao(pesquisa_id) {
  abrir("pesquisas_questoes", "?m=pesquisas_questoes&a=adicionar&i=" + pesquisa_id, 650, 450, 1);
}

function verQuestao(questao_id) {
  abrir("pesquisas_questoes", "?m=pesquisas_questoes&a=ver&i=" + questao_id, 650, 450, 1);
}

//////////////////
// Alternativas //
//////////////////

var alternativa_linha_id = 0;

function adicionarAlternativa() {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

	if (!document.getElementById('alternativa_conteudo').value)
		erro_vazio[erro_vazio.length] = "Conteúdo";

  if (apresentaErros(erro_vazio, erro_invalido)) {
	  url = "rds.js.php?m=pesquisas_questoes&a=adicionar_alternativa&ordem=" + document.getElementById('alternativa_ordem').value + "&conteudo=" + document.getElementById('alternativa_conteudo').value + "&minimo=" + (document.getElementById('alternativa_minimo') ? document.getElementById('alternativa_minimo').value : 0) + "&maximo=" + (document.getElementById('alternativa_maximo') ? document.getElementById('alternativa_maximo').value : 0) + "&eliminar=" + document.getElementById('alternativa_eliminar').value + "&lista_alternativas=" + document.getElementById('lista_alternativas').value + "&id=" + alternativa_linha_id + "&variavel_tipo_id=" + document.getElementById('variavel').value + "&tipo_id=" + document.getElementById('tipo').value;
    RDSCarregar("pesquisas_questoes_adicionar_alternativa", url);
  }

}

function removerAlternativa(id) {
  if (id && confirm('Tem certeza que deseja remover esta alternativa?')) {
	  url = "rds.js.php?m=pesquisas_questoes&a=remover_alternativa&id=" + id + "&lista_alternativas=" + document.getElementById('lista_alternativas').value + "&variavel_tipo_id=" + document.getElementById('variavel').value + "&tipo_id=" + document.getElementById('tipo').value;
    RDSCarregar("pesquisas_questoes_remover_alternativa", url);
  }
}

function selecionarLinhaAlternativa() {
  if (alternativa_linha_id) {
	  url = "rds.js.php?m=pesquisas_questoes&a=selecionar_alternativa&id=" + alternativa_linha_id + "&lista_alternativas=" + document.getElementById('lista_alternativas').value + "&variavel_tipo_id=" + document.getElementById('variavel').value + "&tipo_id=" + document.getElementById('tipo').value;
    RDSCarregar("pesquisas_questoes_selecionar_alternativa", url);
  }
}

function selecionarAlternativa(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  alternativa_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("alternativa_ordem").value = "";
        document.getElementById("alternativa_conteudo").value = "";
        if (document.getElementById("alternativa_minimo")) document.getElementById("alternativa_minimo").value = "";
        if (document.getElementById("alternativa_maximo")) document.getElementById("alternativa_maximo").value = "";
        document.getElementById("alternativa_eliminar").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        alternativa_linha_id = id;
        selecionarLinhaAlternativa();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function formataAlternativaLimite(campo, evento) {
  if (document.getElementById('variavel').value == 3) {
		return formataNumero(campo, evento);
	} else {
		return formataNumeroDecimal(campo, evento);
	}
}

function mudaVariavel() {
  var variavel_tipo_id = document.getElementById('variavel').value;
  document.getElementById('maximo').disabled = variavel_tipo_id <= 2;
  document.getElementById('minimo').disabled = variavel_tipo_id <= 2;
  if (variavel_tipo_id <= 2) {
    document.getElementById('maximo').value = "";
    document.getElementById('minimo').value = "";
  }
  mudaTipo();
}

function mudaTipo() {
	var tipo_id = document.getElementById('tipo').value;
	if (tipo_id == 1) {
		document.getElementById('maximo_alternativas').value = "";
	}
	document.getElementById('maximo_alternativas').disabled = tipo_id == 1;
	// Atualiza Alternativas
  url = "rds.js.php?m=pesquisas_questoes&a=muda_tipo&lista_alternativas=" + document.getElementById('lista_alternativas').value + "&variavel_tipo_id=" + document.getElementById('variavel').value + "&tipo_id=" + tipo_id;
  RDSCarregar("pesquisas_questoes_selecionar_alternativa", url);
}

<?

	if (false) { ?></script><? }

}

?>
