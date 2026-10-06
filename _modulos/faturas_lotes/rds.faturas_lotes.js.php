<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$faturas_lotes = controle::instanciaObjeto("faturas_lotes", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "adicionar_filtro") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["filtros_lista"])));
    // Verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["campo"] == $_GET["campo"] &&
      $valor["comparacao"] == $_GET["comparacao"] &&
      $valor["valor"] == $_GET["valor"] &&
      $chave != $_GET["id"] - 1) {
        print "alert('Filtro já cadastrado!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["campo"] = $_GET["campo"];
    $novo_item["comparacao"] = $_GET["comparacao"];
    $novo_item["valor"] = $_GET["valor"];
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    sort($itens_atuais);
    $listagem = $faturas_lotes->geraFiltrosListagem($itens_atuais, true);

    print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"filtros_lista\")) document.getElementById(\"filtros_lista\").value = '".@serialize($itens_atuais)."';";
    print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").focus();";
    print "filtro_linha_id = 0;";

  } else if ($_GET["a"] == "remover_filtro") {

    $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $faturas_lotes->geraFiltrosListagem($itens_atuais, true);

    print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").value = '{$item["campo"]}';";
    print "if (document.getElementById(\"campo\")) mudaFiltroCampo({$item["campo"]}, {$_GET["id"]});";
    print "if (document.getElementById(\"filtros_lista\")) document.getElementById(\"filtros_lista\").value = '".@serialize($itens_atuais)."';";
    print "filtro_linha_id = 0;";

  } else if ($_GET["a"] == "selecionar_filtro") {

    $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];

    print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").value = '{$item["campo"]}';";
    print "if (document.getElementById(\"campo\")) mudaFiltroCampo({$item["campo"]}, {$_GET["id"]});";
    
  } else if ($_GET["a"] == "busca_dados_filtro") {
 
    if ($_GET["filtro_linha_id"]) {
      $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
      $item = $itens_atuais[$_GET["filtro_linha_id"] - 1];
    }
    $dados_filtro_tipo = $faturas_lotes->buscaDadosLotesFiltros($_GET["id"]);
    print "if (document.getElementById(\"div_comparacao\")) document.getElementById(\"div_comparacao\").innerHTML = '".$faturas_lotes->geraSelect($dados_filtro_tipo["parametros"]["comparacao"], $item["comparacao"], true, "comparacao", "120px")."';";
    if (is_array($dados_filtro_tipo["parametros"]["valor_conteudo"]) || !$dados_filtro_tipo["parametros"]["valor_conteudo"]) {
    	if ($dados_filtro_tipo["parametros"]["valor_conteudo"]) {
        print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '".$faturas_lotes->geraSelect($dados_filtro_tipo["parametros"]["valor_conteudo"], $item["valor"], true, "valor", "300px")."';";
      } else {
        print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '<input type=\"text\" name=\"valor\" id=\"valor\" value=\"{$item["valor"]}\" style=\"width:300px\" class=\"inputBrancoAzulEscuro11\"".($dados_filtro_tipo["parametros"]["formatacao"] ? " onkeypress=\"return {$dados_filtro_tipo["parametros"]["formatacao"]}\" onblur=\"return {$dados_filtro_tipo["parametros"]["formatacao"]}\"" : "").">';";
      }
    } else {
    	print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '".$faturas_lotes->geraSelect($faturas_lotes->buscaFiltroTipoConteudo($dados_filtro_tipo["parametros"]["valor_conteudo"]), $item["valor"], true, "valor", "300px")."';";
    }

  } else if ($_GET["a"] == "mudar_filtro") {

    $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
    $listagem = $faturas_lotes->geraFiltrosListagem($itens_atuais, true, $_GET["sistema"]);
    print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaAdicionarFaturaLote(formulario) {
	
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.data_ciclo.value) {
		erro_vazio[erro_vazio.length] = "Ciclo";
	}
	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}

	return apresentaErros(erro_vazio,erro_invalido);

}

//////////////////////////////////////
// Funções da Listagem de Filtros ////
//////////////////////////////////////

var filtro_linha_id = 0;

function adicionarFiltro() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('campo').value) {
    erro_vazio[erro_vazio.length] = "Campo";
  }
  if (!document.getElementById('comparacao').value) {
    erro_vazio[erro_vazio.length] = "Comparação";
  }
  if (!document.getElementById('valor').value) {
    erro_vazio[erro_vazio.length] = "Valor";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=faturas_lotes&a=adicionar_filtro&campo=" + document.getElementById('campo').value + "&comparacao=" + document.getElementById('comparacao').value + "&valor=" + document.getElementById('valor').value + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&id=" + filtro_linha_id;
    RDSCarregar("faturas_lotes_adicionar_filtro", url);
  }
}

function removerFiltro(id) {
  if (id && confirm('Tem certeza que deseja remover este Filtro?')) {
	  url = "rds.js.php?m=faturas_lotes&a=remover_filtro&id=" + id + "&filtros_lista=" + document.getElementById('filtros_lista').value;
    RDSCarregar("faturas_lotes_remover_filtro", url);
  }
}

function selecionarLinhaFiltro() {
  if (filtro_linha_id) {
	  url = "rds.js.php?m=faturas_lotes&a=selecionar_filtro&id=" + filtro_linha_id + "&filtros_lista=" + document.getElementById('filtros_lista').value;
	  RDSCarregar("faturas_lotes_selecionar_filtro", url);
  }
}

function selecionarFiltro(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  filtro_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("campo").value = "";
        mudaFiltroCampo(document.getElementById("campo").value);
        document.getElementById("comparacao").value = "";
        document.getElementById("valor").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        filtro_linha_id = id;
        selecionarLinhaFiltro();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function mudaFiltroCampo(valor, id) {
  document.getElementById('comparacao').disabled = true;
  document.getElementById('valor').disabled = true;
  document.getElementById('comparacao').value = "";
  document.getElementById('valor').value = "";
  if (valor) {
	  url = "rds.js.php?m=faturas_lotes&a=busca_dados_filtro&id=" + valor + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&filtro_linha_id=" + id;
    RDSCarregar("faturas_lotes_busca_dados_filtro", url);
  }
}

function verCobranca(cobranca) {
	if (cobranca) {
    abrir("fatura", "?m=cobrancas&a=ver_dg&i=" + cobranca, 530, 500, 1);
	}
}

<?

	if (false) { ?></script><? }

}

?>
