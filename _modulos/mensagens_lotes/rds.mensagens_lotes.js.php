<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$mensagens_lotes = controle::instanciaObjeto("mensagens_lotes", $bancodedados, $seguranca->permissoes, false);
  
	if ($_GET["a"] == "adicionar_filtro") {
	
	  $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["filtros_lista"])));
	  // verifica duplicado na listagem
	  foreach ($itens_atuais as $chave => $valor) {
	    if ($valor["campo"] == $_GET["campo"] &&
	    $valor["comparacao"] == $_GET["comparacao"] &&
	    $valor["valor"] == $_GET["valor"] &&
	    $chave != $_GET["id"] - 1) {
	      print "alert('Filtro jс cadastrado!');";
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
	  $listagem = $mensagens_lotes->geraFiltrosListagem($itens_atuais, true);
	  print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
	  print "if (document.getElementById(\"filtros_lista\")) document.getElementById(\"filtros_lista\").value = '".serialize($itens_atuais)."';";
	  print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").focus();";
	  print "filtro_linha_id = 0;";
	
	} elseif ($_GET["a"] == "remover_filtro") {
	
	  $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
	  $item = $itens_atuais[$_GET["id"] - 1];
	  unset($itens_atuais[$_GET["id"] - 1]);
	  sort($itens_atuais);
	  $listagem = $mensagens_lotes->geraFiltrosListagem($itens_atuais, true);
	  print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
	  print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").value = '{$item["campo"]}';";
	  print "if (document.getElementById(\"campo\")) mudaFiltroCampo({$item["campo"]}, {$_GET["id"]});";
	  print "if (document.getElementById(\"filtros_lista\")) document.getElementById(\"filtros_lista\").value = '".serialize($itens_atuais)."';";
	  print "filtro_linha_id = 0;";
	
	} elseif ($_GET["a"] == "selecionar_filtro") {
	
	  $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
	  $item = $itens_atuais[$_GET["id"] - 1];
	  print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").value = '{$item["campo"]}';";
	  print "if (document.getElementById(\"campo\")) mudaFiltroCampo({$item["campo"]}, {$_GET["id"]});";
	
	} elseif ($_GET["a"] == "busca_dados_filtro") {
	
	  if ($_GET["filtro_linha_id"]) {
	    $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
	    $item = $itens_atuais[$_GET["filtro_linha_id"] - 1];
	  }
	  $dados_filtro_tipo = $mensagens_lotes->buscaDadosLotesFiltros($_GET["id"]);
	  print "if (document.getElementById(\"div_comparacao\")) document.getElementById(\"div_comparacao\").innerHTML = '<select name=\"comparacao\" id=\"comparacao\" style=\"width:120px\" class=\"inputBrancoAzulEscuro11\">".$mensagens_lotes->geraSelect($dados_filtro_tipo["parametros"]["comparacao"], $item["comparacao"], true)."</select>';";
	  if (!$dados_filtro_tipo["parametros"]["valor_conteudo"]) {
	    print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '<input type=\"text\" name=\"valor\" id=\"valor\" value=\"{$item["valor"]}\" style=\"width:230px\" class=\"inputBrancoAzulEscuro11\"".($dados_filtro_tipo["parametros"]["formatacao"] ? " onkeypress=\"return {$dados_filtro_tipo["parametros"]["formatacao"]}\" onblur=\"return {$dados_filtro_tipo["parametros"]["formatacao"]}\"" : "").">';";
	  } elseif (is_array($dados_filtro_tipo["parametros"]["valor_conteudo"])) {
	    print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '<select name=\"valor\" id=\"valor\" style=\"width:230px\" class=\"inputBrancoAzulEscuro11\">".$mensagens_lotes->geraSelect($dados_filtro_tipo["parametros"]["valor_conteudo"], $item["valor"], true)."</select>';";
	  } else {
	    print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '<select name=\"valor\" id=\"valor\" style=\"width:230px\" class=\"inputBrancoAzulEscuro11\">".$mensagens_lotes->geraSelect($mensagens_lotes->buscaFiltroTipoConteudo($dados_filtro_tipo["parametros"]["valor_conteudo"]), $item["valor"], true)."</select>';";
	  }
	
	} elseif ($_GET["a"] == "atualiza_lista_modelos") {
	
	  $modelos = $mensagens_lotes->buscaModelos();
	  foreach ($modelos as $id => $nome) {
	    $lista_modelos[] = "new Array($id, '$nome')";
	  }
	  print "atualizaSelect(document.getElementById('modelo'), new Array(".@implode(", ", $lista_modelos)."), {$_GET["id"]});";

  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verModelo(modelo_id) {
	if (modelo_id) {
    abrir("mensagens_lotes_modelos", "?m=mensagens_lotes_modelos&a=ver&i=" + modelo_id, 620, 520, 1);
	}
}

function validaMensagensLotesAdicionar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.lote_nome.value)
		erro_vazio[erro_vazio.length] = "Nome";
  if (!formulario.tipo.value)
		erro_vazio[erro_vazio.length] = "Dados Lote Л Tipo";
	if (formulario.tipo.value == '1') {
    if (!formulario.modelo.value)
  		erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Modelo";
    if (!formulario.destino.value)
  		erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Destino";
		if(!formulario.remetente_email.value)
		  erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Remetente";
		if(formulario.remetente_email.value && !validaEmail(formulario.remetente_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л Remetente";
		if(formulario.cc_email.value && !validaEmail(formulario.cc_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л CC";
		if(formulario.bcc_email.value && !validaEmail(formulario.bcc_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л BCC";
		if(formulario.reply_email.value && !validaEmail(formulario.reply_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л Reply";
		if (!formulario.assunto.value)
		  erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Assunto";
  } else {
    if (!formulario.modelo.value)
  		erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Modelo";
    if (!formulario.remetente_nome.value)
		  erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Remetente";
  }
  if (!formulario.envio_quantidade.value)
		erro_vazio[erro_vazio.length] = "Dados de Envio Л Quantidade";
	if (formulario.envio_intervalo.value && !formulario.envio_intervalo_tipo.value)
		erro_vazio[erro_vazio.length] = "Dados de Envio Л Intervalo";

	return apresentaErros(erro_vazio, erro_invalido);
}

function validaMensagensLotesEditarDc(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!formulario.nome.value)
		erro_vazio[erro_vazio.length] = "Nome";

	return apresentaErros(erro_vazio, erro_invalido);
}

function validaMensagensLotesEditarDe(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();

  if (!formulario.tipo.value)
		erro_vazio[erro_vazio.length] = "Dados Lote Л Tipo";
	if (formulario.tipo.value == '1') {
    if (!formulario.modelo.value)
  		erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Modelo";
    if (!formulario.destino.value)
  		erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Destino";
		if(!formulario.remetente_email.value)
		  erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Remetente";
		if(formulario.remetente_email.value && !validaEmail(formulario.remetente_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л Remetente";
		if(formulario.cc_email.value && !validaEmail(formulario.cc_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л CC";
		if(formulario.bcc_email.value && !validaEmail(formulario.bcc_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л BCC";
		if(formulario.reply_email.value && !validaEmail(formulario.reply_email.value))
		  erro_invalido[erro_invalido.length] = "Dados da Mensagem Л Reply";
		if (!formulario.assunto.value)
		  erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Assunto";
  } else {
    if (!formulario.modelo.value)
  		erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Modelo";
    if (!formulario.remetente_nome.value)
		  erro_vazio[erro_vazio.length] = "Dados da Mensagem Л Remetente";
  }
  if (formulario.envio_quantidade.value < 1)
		erro_vazio[erro_vazio.length] = "Dados de Envio Л Quantidade";
  if (formulario.envio_intervalo.value && !formulario.envio_intervalo_tipo.value)
		erro_vazio[erro_vazio.length] = "Dados de Envio Л Intervalo";

	return apresentaErros(erro_vazio, erro_invalido);
}

var filtro_linha_id = 0;

function adicionarFiltro() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('campo').value) {
    erro_vazio[erro_vazio.length] = "Campo";
  }
  if (!document.getElementById('comparacao').value) {
    erro_vazio[erro_vazio.length] = "Comparaчуo";
  }
  if (!document.getElementById('valor').value) {
    erro_vazio[erro_vazio.length] = "Valor";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=mensagens_lotes&a=adicionar_filtro&campo=" + document.getElementById('campo').value + "&comparacao=" + document.getElementById('comparacao').value + "&valor=" + document.getElementById('valor').value + "&filtros_lista=" + document.getElementById('filtros_lista').value  + "&id=" + filtro_linha_id;
    RDSCarregar("mensagens_lotes_adicionar_filtro", url);
  }
}

function removerFiltro(id) {
  if (id && confirm('Tem certeza que deseja remover este Filtro?')) {
	  url = "rds.js.php?m=mensagens_lotes&a=remover_filtro&id=" + id + "&filtros_lista=" + document.getElementById('filtros_lista').value;
    RDSCarregar("mensagens_lotes_remover_filtro", url);
  }
}

function selecionarLinhaFiltro() {
  if (filtro_linha_id) {
	  url = "rds.js.php?m=mensagens_lotes&a=selecionar_filtro&id=" + filtro_linha_id + "&filtros_lista=" + document.getElementById('filtros_lista').value;
	  RDSCarregar("mensagens_lotes_selecionar_filtro", url);
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
  document.getElementById('comparacao').disabled = !valor;
  document.getElementById('valor').disabled = !valor;
  document.getElementById('comparacao').value = "";
  document.getElementById('valor').value = "";
  if (valor) {
	  url = "rds.js.php?m=mensagens_lotes&a=busca_dados_filtro&id=" + valor + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&filtro_linha_id=" + id;
    RDSCarregar("mensagens_lotes_busca_dados_filtro", url);
  }
}

function mudaTipo(valor) {
  document.getElementById('conteudo_tipo_1').style.display = (document.getElementById('tipo').value == 1) ? (ehNetscape() ? 'table-row-group' : 'block') : "none";
  document.getElementById('conteudo_tipo_2').style.display = (document.getElementById('tipo').value == 2) ? (ehNetscape() ? 'table-row-group' : 'block') : "none";
}

function mudaTipoLote(valor) {
  if (document.getElementById('lote_tipo').value == 1) {
    document.getElementById('conteudo_lote').style.display = (ehNetscape() ? 'table-row-group' : 'block');
    document.getElementById('conteudo_email').style.display = (ehNetscape() ? 'table-row-group' : 'block');
  } else if (document.getElementById('lote_tipo').value == 2) {
    document.getElementById('conteudo_lote').style.display = "none";
    document.getElementById('conteudo_email').style.display = "none";
  }
}

function atualizaListaModelos(modelo_id) {
  url = "rds.js.php?m=mensagens_lotes&a=atualiza_lista_modelos&id=" + modelo_id;
  RDSCarregar("mensagens_lotes_atualiza_lista_modelos", url);
}

<?

	if (false) { ?></script><? }

}

?>
