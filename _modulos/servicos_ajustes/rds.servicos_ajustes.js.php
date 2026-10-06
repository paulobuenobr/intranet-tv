<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "buscar_regras") {

    $dados_categoria = $servicos_ajustes->buscaDadosCategoria($_GET["categoria_id"]);
    $listagem = $servicos_ajustes->geraRegrasListagem($dados_categoria["regras"], $dados_categoria["editavel"]);
    $duracao = $dados_categoria["duracao"] ? "{$dados_categoria["duracao"]} dia(s)" : "";
    $valor = $dados_categoria["valor"] ? $servicos_ajustes->formataDinheiro($dados_categoria["valor"]) : "";
    print "if (document.getElementById(\"duracao\")) document.getElementById(\"duracao\").innerHTML = '$duracao';";
    print "if (document.getElementById(\"valor\")) document.getElementById(\"valor\").innerHTML = '$valor';";
    print "if (document.getElementById(\"listagem_regras\")) document.getElementById(\"listagem_regras\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"lista_regras\")) document.getElementById(\"lista_regras\").value = '".serialize($dados_categoria["regras"])."';";
    print "regra_linha_id = 0;";

  } elseif ($_GET["a"] == "adicionar_regra") {

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
    $listagem = $servicos_ajustes->geraRegrasListagem($itens_atuais, true);
    print "if (document.getElementById(\"listagem_regras\")) document.getElementById(\"listagem_regras\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"lista_regras\")) document.getElementById(\"lista_regras\").value = '".serialize($itens_atuais)."';";
    print "if (document.getElementById(\"servico\")) document.getElementById(\"servico\").focus();";
    print "regra_linha_id = 0;";

  } elseif ($_GET["a"] == "remover_regra") {

    $itens_atuais = @unserialize(stripslashes($_GET["lista_regras"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $servicos_ajustes->geraRegrasListagem($itens_atuais, true);
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

  } elseif ($_GET["a"] == "verifica_periodo") {

  	$erro = array();
  	if ($_GET["termino"]) {
    	$dia = substr($_GET["termino"], 0, 2);
    	$mes = substr($_GET["termino"], 3, 2);
    	$ano = substr($_GET["termino"], 6, 4);
    	$hora = substr($_GET["termino"], 12, 2);
    	$minuto = substr($_GET["termino"], 15 , 2);
    	$termino = strtotime("{$ano}-{$mes}-{$dia} {$hora}:{$minuto}:00");
    	if ($termino < time()) {
    		$erro[] = "Término menor que a data atual";
    	}
  	}
  	if ($erro) {
  		foreach($erro as $valor) {
  			$erro_invalido .= "* $valor\\n";
  		}
  		$erro_invalido = "O".(count($erro) > 1 ? "s" : "")." seguinte".(count($erro) > 1 ? "s" : "")." erro".(count($erro) > 1 ? "s" : "")." fo".(count($erro) > 1 ? "ram" : "i")." encontrado".(count($erro) > 1 ? "s" : "").":\\n\\n".$erro_invalido;
      print "alert('$erro_invalido');";
  	} else {
  		print "if (document.getElementById(\"{$_GET["formulario"]}\")) document.getElementById(\"{$_GET["formulario"]}\").submit();";
  	}

  } elseif ($_GET["a"] == "atualiza_status") {

    $query = "
    	SELECT
    		sac_editavel as editavel
    	FROM
    		tab_servicos_ajustes_categorias
    	WHERE
    		sac_id = {$_GET["id"]}";
    $query = $bancodedados->query($query);
    $dados = $bancodedados->resultadoArray($query);
    if ($dados) {
    	$enabled = $dados["editavel"] ? "false" : "true";
  		print "document.{$_GET["formulario"]}.servico.disabled = $enabled;";
  		print "document.{$_GET["formulario"]}.ajuste.disabled = $enabled;";
  		print "document.{$_GET["formulario"]}.dia_semana.disabled = $enabled;";
  		print "document.{$_GET["formulario"]}.hora_inicio.disabled = $enabled;";
  		print "document.{$_GET["formulario"]}.hora_termino.disabled = $enabled;";
  		print "document.{$_GET["formulario"]}.registrar_regra.disabled = $enabled;";
    }

  } elseif ($_GET["a"] == "buscar_ajustes_clientes") {

    $listagem = $servicos_ajustes->geraListagemClientesAjustes($servicos_ajustes->buscaClientesAjustes($_GET["cliente"]));
    $botao_adicionar = $servicos_ajustes->buscaBotaoAdicionar($_GET["cliente"]);
    print "if (document.getElementById(\"ajustes\")) document.getElementById(\"ajustes\").innerHTML = '".addslashes($listagem)."';";
    print "if (document.getElementById(\"ajustes_adicionar\")) document.getElementById(\"ajustes_adicionar\").innerHTML = '".addslashes($botao_adicionar)."';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var servicos_ajustes;

function buscaServicosAjustes() {
	url = "rds.js.php?m=servicos_ajustes&a=buscar_servicos_ajustes";
	RDSCarregar("servicos_ajustes_buscar_servicos_ajustes", url);
}

function buscaAjustesCliente(cliente) {
	url = "rds.js.php?m=servicos_ajustes&a=buscar_ajustes_clientes&cliente=" + cliente;
	RDSCarregar("servicos_ajustes_buscar_ajustes_clientes", url);
}

function adicionarServicosAjustes() {
  abrir("servicos_ajustes", "?m=servicos_ajustes&a=adicionar", 700, 430, 1);
}

function verAjusteCliente(ajuste, cliente) {
  abrir("servicos_ajustes", "?m=servicos_ajustes&a=ver&i=" + ajuste, 700, 430, 1);
}

function adicionarAjusteCliente(cliente) {
  abrir("servicos_ajustes", "?m=servicos_ajustes&a=adicionar&cliente=" + cliente, 700, 430, 1);
}

function adicionarAjusteTemporarioCliente(cliente) {
	  abrir("servicos_ajustes", "?m=servicos_ajustes&a=adicionar_bloqueio_temporario&cliente=" + cliente, 700, 430, 1);
}

function alteraCategoria(categoria_id) {
	url = "rds.js.php?m=servicos_ajustes&a=buscar_regras&categoria_id=" + categoria_id;
	RDSCarregar("servicos_ajustes_buscar_regras", url);
}

function atualizaStatus(formulario) {
	categoria = formulario.categoria.value;
	url = "rds.js.php?m=servicos_ajustes&a=atualiza_status&id=" + categoria + "&formulario=" + formulario.name;
	RDSCarregar("servicos_ajustes_atualiza_status", url);
}

function validaServicosAjustes(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.categoria.value) {
		erro_vazio[erro_vazio.length] = "Categoria";
  }
  return apresentaErros(erro_vazio, erro_invalido);
}

function validaServicosAjustesBloqueioTemporario(formulario) {

	var erro_vazio = new Array();
	var erro_invalido = new Array();
	
	if (!formulario.inicio.value) {
		erro_vazio[erro_vazio.length] = "Data de Início!";
	}
	if (!formulario.termino.value) {
		erro_vazio[erro_vazio.length] = "Data Final!";
	}
	  
  return apresentaErros(erro_vazio, erro_invalido);

}

function contratoClick(campo, contrato) {
	// Seleciona todos abaixo
	if (campo.checked) {
		assinatura_i = 1;
		while (assinatura = document.getElementById("assinatura_" + contrato + "_" + assinatura_i)) {
			assinatura.checked = true;
			assinaturaClick(assinatura, contrato, assinatura_i);
			assinatura_i++;
		}
	}
}

function assinaturaClick(campo, contrato, assinatura) {
	// Seleciona todos abaixo
	if (campo.checked) {
		servico_i = 1;
		while (servico = document.getElementById("servico_" + contrato + "_" + assinatura + "_" + servico_i)) {
			servico.checked = true;
			servico_i++;
		}
	} else {
  	if (contrato = document.getElementById("contrato_" + contrato))
  		contrato.checked = false;
	}
}

function servicoClick(campo, contrato, assinatura) {
	if (!campo.checked) {
  	if (assinatura = document.getElementById("assinatura_" + contrato + "_" + assinatura))
  		assinatura.checked = false;
  	if (contrato = document.getElementById("contrato_" + contrato))
  		contrato.checked = false;
	}
}

// ////// //
// Regras //
// ////// //

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
		
	if (document.getElementById('ajuste_tipo').value == 1 && parseInt(document.getElementById('ajuste_valor').value) > 100)
		erro_invalido[erro_invalido.length] = "Valor";

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
	  url = "rds.js.php?m=servicos_ajustes&a=adicionar_regra&servico=" + document.getElementById('servico').value + "&ajuste_tipo=" + document.getElementById('ajuste_tipo').value + "&ajuste_valor=" + document.getElementById('ajuste_valor').value + "&ajuste_unidade=" + document.getElementById('ajuste_unidade').value + "&dia_semana=" + document.getElementById('dia_semana').value + "&hora_inicio=" + document.getElementById('hora_inicio').value + "&hora_termino=" + document.getElementById('hora_termino').value + "&lista_regras=" + document.getElementById('lista_regras').value + "&id=" + regra_linha_id;
    RDSCarregar("servicos_ajustes_adicionar_regra", url);
  }
}

function removerRegra(id) {
  if (id && confirm('Tem certeza que deseja remover esta regra?')) {
	  url = "rds.js.php?m=servicos_ajustes&a=remover_regra&id=" + id + "&lista_regras=" + document.getElementById('lista_regras').value;
    RDSCarregar("servicos_ajustes_remover_regra", url);
  }
}

function selecionarLinhaRegra() {
  if (regra_linha_id) {
	  url = "rds.js.php?m=servicos_ajustes&a=selecionar_regra&id=" + regra_linha_id + "&lista_regras=" + document.getElementById('lista_regras').value;
    RDSCarregar("servicos_ajustes_selecionar_regra", url);
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
	if (!formulario.lista_regras.value || formulario.lista_regras.value == 'a:0:{}')
		erro_vazio[erro_vazio.length] = "Regras";
	return apresentaErros(erro_vazio, erro_invalido);
}

function mudaServico(valor) {
  var servicos_especificos = "1,9,";
  //var servicos_especificos = "";
  if (valor && (servicos_especificos.indexOf(valor + ',') != -1)) {
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
