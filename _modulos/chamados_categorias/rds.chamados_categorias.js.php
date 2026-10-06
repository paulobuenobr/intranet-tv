<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$categorias = controle::instanciaObjeto("chamados_categorias", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "adicionar_etapa") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["lista_etapas"])));
    // Trata novos itens
    $novo_item["id"] = ($_GET["id"]) ? $_GET["id"] : rand();
    $novo_item["ordem"] = $_GET["ordem"];
    $etapas = $categorias->buscaListaEtapas();
    $novo_item["etapa_id"] = $_GET["etapa"];
    $novo_item["etapa_nome"] = $etapas[$_GET["etapa"]];
    $setores = $categorias->buscaSetores();
    $novo_item["setor_id"] = $_GET["setor"];
    $novo_item["setor_nome"] = $setores[$_GET["setor"]];
    $funcoes = $categorias->buscaFuncoes();
    $novo_item["funcao_id"] = $_GET["funcao"];
    $novo_item["funcao_nome"] = $funcoes[$_GET["funcao"]];
    $obrigatoria_tipos = $categorias->buscaObrigatoriaTipos();
    $novo_item["obrigatoria_id"] = $_GET["obrigatoria"];
    $novo_item["obrigatoria_nome"] = $obrigatoria_tipos[$_GET["obrigatoria"]];
    $multipla_tipos = $categorias->buscaMultiplaTipos();
    $novo_item["multipla_id"] = $_GET["multipla"];
    $novo_item["multipla_nome"] = $multipla_tipos[$_GET["multipla"]];

    if ($_GET["id"]) {
      foreach ($itens_atuais as $chave => $valor) {
        if ($valor["id"] == $_GET["id"]) $itens_atuais[$chave] = $novo_item;
      }
    } else {
      $itens_atuais[] = $novo_item;
    }
    $itens_atuais = $categorias->ordena_multiarray($itens_atuais, array("ordem", "etapa_nome", "setor_nome", "funcao_nome"));
    $dados = funcoes::mescla_arrays(array($categorias->buscaCategoriasEtapasAdicionar()), $itens_atuais);
    for ($i = 1; $i < count($dados); $i++) {
      if ($dados[$i]["id"]) {
        $dados[$i]["botoes"] = "<input type=\"button\" name=\"remover\" id=\"remover\" value=\"X\" style=\"width:25px\" class=\"buttonAzulEscuroBranco11\" onclick=\"removerEtapa({$dados[$i]["id"]})\">";
        $dados[$i]["link"] = "javascript:selecionarEtapa(this, event, {$dados[$i]["id"]})";
      }
    }
    $dados = $categorias->geraCategoriasEtapasListagem($dados);
    print "if (document.getElementById(\"listagem_etapas\")) document.getElementById(\"listagem_etapas\").innerHTML = '".str_replace("'", '\\\'', $dados)."';";
    print "if (document.getElementById(\"lista_etapas\")) document.getElementById(\"lista_etapas\").value = '".serialize($itens_atuais)."';";
    print "etapa_linha_id = 0;";

  } elseif ($_GET["a"] == "remover_etapa") {

    $dados[] = $categorias->buscaCategoriasEtapasAdicionar();
    $itens_atuais = @unserialize(stripslashes($_GET["lista_etapas"]));
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["id"] == $_GET["id"]) {
        $item = $valor;
        unset($itens_atuais[$chave]);
      }
    }
    if ($itens_atuais) {
      $dados = funcoes::mescla_arrays($dados, $itens_atuais);
    } else {
      $dados[] = array("mesclado" => "<span style=\"height:21px;width:100%;text-align:center;vertical-align:middle\">Nenhum registro disponível</span>");
    }
    for ($i = 1; $i < count($dados); $i++) {
      if ($dados[$i]["id"]) {
        $dados[$i]["botoes"] = "<input type=\"button\" name=\"remover\" id=\"remover\" value=\"X\" style=\"width:25px\" class=\"buttonAzulEscuroBranco11\" onclick=\"removerEtapa({$dados[$i]["id"]})\">";
        $dados[$i]["link"] = "javascript:selecionarEtapa(this, event, {$dados[$i]["id"]})";
      }
    }
    $dados = $categorias->geraCategoriasEtapasListagem($dados);
    print "if (document.getElementById(\"listagem_etapas\")) document.getElementById(\"listagem_etapas\").innerHTML = '".str_replace("'", '\\\'', $dados)."';";
    print "if (document.getElementById(\"lista_etapas\")) document.getElementById(\"lista_etapas\").value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById(\"ordem\")) document.getElementById(\"ordem\").value = '{$item["ordem"]}';";
    print "if (document.getElementById(\"etapa\")) document.getElementById(\"etapa\").value = '{$item["etapa_id"]}';";
    print "if (document.getElementById(\"setor\")) document.getElementById(\"setor\").value = '{$item["setor_id"]}';";
    print "if (document.getElementById(\"funcao\")) document.getElementById(\"funcao\").value = '{$item["funcao_id"]}';";
    print "if (document.getElementById(\"obrigatoria\")) document.getElementById(\"obrigatoria\").value = '{$item["obrigatoria_id"]}';";
    print "if (document.getElementById(\"multipla\")) document.getElementById(\"multipla\").value = '{$item["multipla_id"]}';";
    print "email_linha_id = 0;";

  } elseif ($_GET["a"] == "selecionar_etapa") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_etapas"]));
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["id"] == $_GET["id"]) {
        $item = $valor;
      }
    }
    print "if (document.getElementById(\"ordem\")) document.getElementById(\"ordem\").value = '{$item["ordem"]}';";
    print "if (document.getElementById(\"etapa\")) document.getElementById(\"etapa\").value = '{$item["etapa_id"]}';";
    print "if (document.getElementById(\"setor\")) document.getElementById(\"setor\").value = '{$item["setor_id"]}';";
    print "if (document.getElementById(\"funcao\")) document.getElementById(\"funcao\").value = '{$item["funcao_id"]}';";
    print "if (document.getElementById(\"obrigatoria\")) document.getElementById(\"obrigatoria\").value = '{$item["obrigatoria_id"]}';";
    print "if (document.getElementById(\"multipla\")) document.getElementById(\"multipla\").value = '{$item["multipla_id"]}';";

  }

} else {

	if (false) { ?><script type="text/javascript"><? }
	
?>

var etapa_linha_id = 0;

function adicionarEtapa() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('etapa').value) {
    erro_vazio[erro_vazio.length] = "Etapa";
  }
  if (!document.getElementById('obrigatoria').value) {
    erro_vazio[erro_vazio.length] = "Obrigatória";
  }
  if (!document.getElementById('multipla').value) {
    erro_vazio[erro_vazio.length] = "Múltipla";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
	  url = "rds.js.php?m=chamados_categorias&a=adicionar_etapa&ordem=" + document.getElementById('ordem').value + "&etapa=" + document.getElementById('etapa').value + "&setor=" + document.getElementById('setor').value + "&funcao=" + document.getElementById('funcao').value + "&obrigatoria=" + document.getElementById('obrigatoria').value + "&multipla=" + document.getElementById('multipla').value + "&lista_etapas=" + document.getElementById('lista_etapas').value + "&id=" + etapa_linha_id;
    RDSCarregar("chamados_adicionar_etapa", url);
  }
}

function removerEtapa(id) {
  if (id && confirm('Tem certeza que deseja remover este e-mail?')) {
	  url = "rds.js.php?m=chamados_categorias&a=remover_etapa&id=" + id + "&lista_etapas=" + document.getElementById('lista_etapas').value;
    RDSCarregar("chamados_remover_contatos_etapas", url);
  }
}

function selecionarLinhaEtapa() {
  if (etapa_linha_id) {
	  url = "rds.js.php?m=chamados_categorias&a=selecionar_etapa&id=" + etapa_linha_id + "&lista_etapas=" + document.getElementById('lista_etapas').value;
    RDSCarregar("chamados_selecionar_etapa", url);
  }
}

function selecionarEtapa(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  etapa_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("ordem").value = "";
        document.getElementById("etapa").value = "";
        document.getElementById("setor").value = "";
        document.getElementById("funcao").value = "";
        document.getElementById("obrigatoria").value = "";
        document.getElementById("multipla").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        etapa_linha_id = linha.id;
        selecionarLinhaEtapa();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function validaCategoria(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('nome').value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }
  if (!document.getElementById('restrita').value) {
    erro_vazio[erro_vazio.length] = "Restrita";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}
