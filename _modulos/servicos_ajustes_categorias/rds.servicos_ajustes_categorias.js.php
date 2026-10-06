<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$servicos_ajustes_categorias = controle::instanciaObjeto("servicos_ajustes_categorias", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "adicionar_regra") {
  	
    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["lista_regras"])));
    // verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["servico"] == $_GET["servico"] &&
          $valor["ajuste_tipo"] == $_GET["ajuste_tipo"] &&
          $valor["ajuste_valor"] == $_GET["ajuste_valor"] &&
          $valor["ajuste_unidade"] == $_GET["ajuste_unidade"] &&
          $valor["dia_semana"] == $_GET["dia_semana"] &&
          $valor["hora_inicio"] == $_GET["hora_inicio"] &&
          $valor["hora_termino"] == $_GET["hora_termino"] &&
          $chave != $_GET["id"] - 1) {
        print "alert('Regra já cadastrada para esta categoria!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["servico"] = $_GET["servico"];
    $novo_item["ajuste_tipo"] = $_GET["ajuste_tipo"];
    $novo_item["ajuste_valor"] = preg_replace("/[^0-9]/", "", $_GET["ajuste_valor"]);
    $novo_item["ajuste_unidade"] = $_GET["ajuste_unidade"];
    $novo_item["dia_semana"] = $_GET["dia_semana"];
    $novo_item["hora_inicio"] = $_GET["hora_inicio"];
    $novo_item["hora_termino"] = $_GET["hora_termino"];
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    $listagem = $servicos_ajustes_categorias->geraRegrasListagem($itens_atuais, true);
    print "if (document.getElementById(\"listagem_regras\")) document.getElementById(\"listagem_regras\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"lista_regras\")) document.getElementById(\"lista_regras\").value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById(\"servico\")) document.getElementById(\"servico\").focus();";
    print "regra_linha_id = 0;";
    
  } elseif ($_GET["a"] == "remover_regra") {
  	
    $itens_atuais = @unserialize(stripslashes($_GET["lista_regras"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $servicos_ajustes_categorias->geraRegrasListagem($itens_atuais, true);
    print "if (document.getElementById(\"listagem_regras\")) document.getElementById(\"listagem_regras\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"lista_regras\")) document.getElementById(\"lista_regras\").value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById(\"servico\")) document.getElementById(\"servico\").value = '{$item["servico"]}';";
    print "if (document.getElementById(\"servico\")) mudaServico({$item["servico"]});";
    print "if (document.getElementById(\"ajuste_tipo\")) document.getElementById(\"ajuste_tipo\").value = '{$item["ajuste_tipo"]}';";
    print "if (document.getElementById(\"ajuste_tipo\")) mudaAjusteTipo({$item["ajuste_tipo"]});";
    print "if (document.getElementById(\"ajuste_valor\")) document.getElementById(\"ajuste_valor\").value = '{$item["ajuste_valor"]}';";
    print "if (document.getElementById(\"ajuste_unidade\")) document.getElementById(\"ajuste_unidade\").value = '{$item["ajuste_unidade"]}';";
    print "if (document.getElementById(\"dia_semana\")) document.getElementById(\"dia_semana\").value = '{$item["dia_semana"]}';";
    print "if (document.getElementById(\"hora_inicio\")) document.getElementById(\"hora_inicio\").value = '{$item["hora_inicio"]}';";
    print "if (document.getElementById(\"hora_termino\")) document.getElementById(\"hora_termino\").value = '{$item["hora_termino"]}';";
    print "if (document.getElementById(\"servico\")) document.getElementById(\"servico\").focus();";
    print "regra_linha_id = 0;";
    
  } elseif ($_GET["a"] == "selecionar_regra") {
  	
    $itens_atuais = @unserialize(stripslashes($_GET["lista_regras"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    print "if (document.getElementById(\"servico\")) document.getElementById(\"servico\").value = '{$item["servico"]}';";
    print "if (document.getElementById(\"servico\")) mudaServico({$item["servico"]});";
    print "if (document.getElementById(\"ajuste_tipo\")) document.getElementById(\"ajuste_tipo\").value = '{$item["ajuste_tipo"]}';";
    print "if (document.getElementById(\"ajuste_tipo\")) mudaAjusteTipo({$item["ajuste_tipo"]});";
    print "if (document.getElementById(\"ajuste_valor\")) document.getElementById(\"ajuste_valor\").value = '{$item["ajuste_valor"]}';";
    print "if (document.getElementById(\"ajuste_unidade\")) document.getElementById(\"ajuste_unidade\").value = '{$item["ajuste_unidade"]}';";
    print "if (document.getElementById(\"dia_semana\")) document.getElementById(\"dia_semana\").value = '{$item["dia_semana"]}';";
    print "if (document.getElementById(\"hora_inicio\")) document.getElementById(\"hora_inicio\").value = '{$item["hora_inicio"]}';";
    print "if (document.getElementById(\"hora_termino\")) document.getElementById(\"hora_termino\").value = '{$item["hora_termino"]}';";
    print "if (document.getElementById(\"servico\")) document.getElementById(\"servico\").focus();";
    
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var regra_linha_id = 0;

function adicionarRegra() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();
  var servicos_especificos = "1,2,4,5,7,8,9,";

	if (!document.getElementById('servico').value)
		erro_vazio[erro_vazio.length] = "Serviço";

	if (!document.getElementById('ajuste_tipo').value && (!document.getElementById('servico').value || (document.getElementById('servico').value && servicos_especificos.indexOf(document.getElementById('servico').value + ',') != -1)))
		erro_vazio[erro_vazio.length] = "Tipo";

	if (!document.getElementById('ajuste_valor').value)
		erro_vazio[erro_vazio.length] = "Valor";

	if (document.getElementById('ajuste_tipo').value == 2 && !document.getElementById('ajuste_unidade').value)
		erro_vazio[erro_vazio.length] = "Unidade";

	if (!document.getElementById('dia_semana').value)
		erro_vazio[erro_vazio.length] = "Dia da Semana";

	if (!document.getElementById('hora_inicio').value)
		erro_vazio[erro_vazio.length] = "Início";

	if (!document.getElementById('hora_termino').value)
		erro_vazio[erro_vazio.length] = "Término";

	if (document.getElementById('hora_inicio').value && (document.getElementById('hora_inicio').value == document.getElementById('hora_termino').value))
		erro_invalido[erro_invalido.length] = "Início igual ao Término";

  if (apresentaErros(erro_vazio, erro_invalido)) {
	  url = "rds.js.php?m=servicos_ajustes_categorias&a=adicionar_regra&servico=" + document.getElementById('servico').value + "&ajuste_tipo=" + document.getElementById('ajuste_tipo').value + "&ajuste_valor=" + document.getElementById('ajuste_valor').value + "&ajuste_unidade=" + document.getElementById('ajuste_unidade').value + "&dia_semana=" + document.getElementById('dia_semana').value + "&hora_inicio=" + document.getElementById('hora_inicio').value + "&hora_termino=" + document.getElementById('hora_termino').value + "&lista_regras=" + document.getElementById('lista_regras').value + "&id=" + regra_linha_id;
    RDSCarregar("servicos_ajustes_categorias_adicionar_regra", url);
  }
}

function removerRegra(id) {
  if (id && confirm('Tem certeza que deseja remover esta regra?')) {
	  url = "rds.js.php?m=servicos_ajustes_categorias&a=remover_regra&id=" + id + "&lista_regras=" + document.getElementById('lista_regras').value;
    RDSCarregar("servicos_ajustes_categorias_remover_regra", url);
  }
}

function selecionarLinhaRegra() {
  if (regra_linha_id) {
	  url = "rds.js.php?m=servicos_ajustes_categorias&a=selecionar_regra&id=" + regra_linha_id + "&lista_regras=" + document.getElementById('lista_regras').value;
    RDSCarregar("servicos_ajustes_categorias_selecionar_regra", url);
  }
}

function selecionarRegra(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  regra_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("servico").value = "";
        mudaServico(document.getElementById("servico").value);
        document.getElementById("ajuste_tipo").value = "";
        mudaAjusteTipo(document.getElementById("ajuste_tipo").value);
        document.getElementById("ajuste_valor").value = "";
        document.getElementById("ajuste_unidade").value = "";
        document.getElementById("dia_semana").value = "";
        document.getElementById("hora_inicio").value = "";
        document.getElementById("hora_termino").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        regra_linha_id = id;
        selecionarLinhaRegra();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function validaCategoria(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.descricao.value)
		erro_vazio[erro_vazio.length] = "Descrição";
	return apresentaErros(erro_vazio, erro_invalido);
}

function mudaServico(valor) {
  var servicos_especificos = "1,2,4,5,7,8,9,";
  if (valor && servicos_especificos.indexOf(valor + ',') != -1) {
    document.getElementById('ajuste_tipo').disabled = false;
    document.getElementById('ajuste_tipo').value = "";
  } else {
    document.getElementById('ajuste_tipo').disabled = true;
    document.getElementById('ajuste_tipo').value = valor ? "1" : "";
  }
  mudaAjusteTipo(document.getElementById('ajuste_tipo').value);
}

function formataAjusteValor(campo, evento) {
  if (document.getElementById('ajuste_tipo').value == 1) {
    return formataPorcentagem(campo, evento);
  } else {
    return formataNumero(campo, evento);
  }
}

function mudaAjusteTipo(valor) {
  document.getElementById('ajuste_valor').disabled = !document.getElementById('ajuste_tipo').value;
  document.getElementById('ajuste_valor').value = "";
  if (document.getElementById('ajuste_tipo').value == 2) {
    atualizaSelect(document.getElementById('ajuste_unidade'), geraListaUnidades(document.getElementById('servico').value));
  } else {
    atualizaSelect(document.getElementById('ajuste_unidade'), []);
  }
}

function geraListaUnidades(servico) {
  var lista_unidades = new Array();
  var multiplicadores = buscaMultiplicadores();
  var unidade = buscaUnidade(servico);
  for (i = 0; i <= 3; i++) {
    lista_unidades[lista_unidades.length] = new Array(i, multiplicadores[i] + unidade);
  }
  return lista_unidades;
}

function buscaMultiplicadores() {
  var multiplicadores = new Array();
  multiplicadores[0] = "";
  multiplicadores[1] = "K";
  multiplicadores[2] = "M";
  multiplicadores[3] = "G";
  return multiplicadores;
}

function buscaUnidade(servico) {
  var unidades = new Array();
  unidades[1] = "bps";
  unidades[2] = "B";
  unidades[4] = "B";
  unidades[5] = "B / mês";
  unidades[7] = "B";
  unidades[8] = "B / mês";
  unidades[9] = "bps";
  return unidades[servico];
}

<?

	if (false) { ?></script><? }

}

?>
