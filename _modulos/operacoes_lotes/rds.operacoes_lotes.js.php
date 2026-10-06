<?

function buscaCidades () {
  GLOBAL $bancodedados;
  $query = "
    SELECT DISTINCT
      tab_cidades.cid_id AS id,
      tab_cidades.cid_nome AS nome
    FROM
      tab_condominios
      INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
    ORDER BY
      nome";
  $query = $bancodedados->query($query);
  $cidades["-1"] = "Todas";
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $cidades[$dados["id"]] = $dados["nome"];
  }
  return $cidades;
}

function buscaBairros ($cidade = '%') {
  if ($cidade == -1) $cidade = '%';
  GLOBAL $bancodedados;
  $query = "
    SELECT DISTINCT
      tab_bairros.bai_id AS id,
      tab_bairros.bai_nome AS nome
    FROM
      tab_condominios
      INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
    WHERE
      tab_bairros.cid_id LIKE '$cidade'
    ORDER BY
      nome";
  $query = $bancodedados->query($query);
  $bairros["-1"] = "Todos";
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $bairros[$dados["id"]] = $dados["nome"];
  }
  return $bairros;
}

function buscaLogradouros ($bairro = '%') {
  if ($bairro == -1) $bairro = '%';
  GLOBAL $bancodedados;
  $query = "
    SELECT DISTINCT
      tab_logradouros.log_id AS id,
      tab_logradouros.log_nome AS nome
    FROM
      tab_condominios
      INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
    WHERE
      tab_logradouros.bai_id LIKE '$bairro'
    ORDER BY
      nome";
  $query = $bancodedados->query($query);
  $logradouros["-1"] = "Todos";
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $logradouros[$dados["id"]] = $dados["nome"];
  }
  return $logradouros;
}

function buscaCondominios ($cidade = '%', $bairro = '%', $logradouro = '%') {
  if ($logradouro == -1) $logradouro = '%';
  if ($bairro == -1) $bairro = '%';
  if ($cidade == -1) $cidade = '%';
  GLOBAL $bancodedados;
  $query = "
    SELECT DISTINCT
      tab_condominios.con_id AS id,
      tab_condominios.con_nome AS nome
    FROM
      tab_condominios
      INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
    WHERE
      tab_cidades.cid_id LIKE '$cidade' AND
      tab_bairros.bai_id LIKE '$bairro' AND
      tab_logradouros.log_id LIKE '$logradouro'
    ORDER BY
      nome";
  $query = $bancodedados->query($query);
  $condominios["-1"] = "Todos";
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $condominios[$dados["id"]] = $dados["nome"];
  }
  return $condominios;
}

function geraScriptAtualizacaoSelect($nome, $itens = '', $ativo = '') {
  if ($nome) {
    while (($item = @current($itens)) != false) {
      $script_itens[] = 'new Array("' . @key($itens) . '","' . $item . '")';
      @next($itens);
    }
    $script_conteudo  = 'var itens_' . $nome . ' = new Array (' . @implode(',',$script_itens) . ');';
    $script_conteudo .= 'if (document.getElementById("' . $nome . '")) atualizaSelect(document.getElementById("' . $nome . '"),itens_' . $nome . ',"' . $ativo . '");';
  }
  return $script_conteudo;
}

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$operacoes_lotes = controle::instanciaObjeto("operacoes_lotes", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "adicionar_endereco") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["enderecos_lista"])));
    // verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["cidade"] == $_GET["cidade"] &&
      $valor["bairro"] == $_GET["bairro"] &&
      $valor["logradouro"] == $_GET["logradouro"] &&
      $valor["condominio"] == $_GET["condominio"] &&
      $chave != $_GET["id"] - 1) {
        print "alert('Endereço já cadastrado!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["cidade"] = $_GET["cidade"];
    $novo_item["bairro"] = $_GET["bairro"];
    $novo_item["logradouro"] = $_GET["logradouro"];
    $novo_item["condominio"] = $_GET["condominio"];
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    sort($itens_atuais);
    $listagem = $operacoes_lotes->geraEnderecosListagem($itens_atuais, true);

    print "if (document.getElementById(\"enderecos_listagem\")) document.getElementById(\"enderecos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"enderecos_lista\")) document.getElementById(\"enderecos_lista\").value = '".@serialize($itens_atuais)."';";
    print "if (document.getElementById(\"cidade\")) document.getElementById(\"cidade\").focus();";
    print "endereco_linha_id = 0;";

  } else if ($_GET["a"] == "remover_endereco") {

    $itens_atuais = @unserialize(stripslashes($_GET["enderecos_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $operacoes_lotes->geraEnderecosListagem($itens_atuais, true);

    print "if (document.getElementById(\"enderecos_listagem\")) document.getElementById(\"enderecos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"cidade\")) document.getElementById(\"cidade\").value = '{$item["cidade"]}';";
    print "if (document.getElementById(\"cidade\")) mudaEnderecoCampo({$item["cidade"]}, {$_GET["id"]});";
    print "if (document.getElementById(\"enderecos_lista\")) document.getElementById(\"enderecos_lista\").value = '".@serialize($itens_atuais)."';";
    print "endereco_linha_id = 0;";

  } else if ($_GET["a"] == "selecionar_endereco") {

    $itens_atuais = @unserialize(stripslashes($_GET["enderecos_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    print "if (document.getElementById(\"cidade\")) document.getElementById(\"cidade\").value = '{$item["cidade"]}';";
    print "if (document.getElementById(\"cidade\")) mudaEnderecoCampo({$item["cidade"]}, {$_GET["id"]});";

  } else if ($_GET["a"] == "busca_dados_endereco") {

    // Unserializa os dados do item selecionado
    if ($_GET["endereco_linha_id"]) {
      $itens_atuais = @unserialize(stripslashes($_GET["enderecos_lista"]));
      $item = $itens_atuais[$_GET["endereco_linha_id"] - 1];
    }
    // Muda os selects de acordo com o item selecionado
    print 'if (document.getElementById("div_cidade")) document.getElementById("div_cidade").innerHTML = \''.$operacoes_lotes->geraSelect(buscaCidades(), $item["cidade"], true, "cidade", "120px", "atualizaOperacoesLotesCidade(this.value)")."';";
    print 'if (document.getElementById("div_bairro")) document.getElementById("div_bairro").innerHTML = \''.$operacoes_lotes->geraSelect(buscaBairros($item["cidade"]), $item["bairro"], true, "bairro", "120px", 'atualizaOperacoesLotesBairro(document.getElementById(\\\'cidade\\\').value,this.value)')."';";
    print 'if (document.getElementById("div_logradouro")) document.getElementById("div_logradouro").innerHTML = \''.$operacoes_lotes->geraSelect(buscaLogradouros($item["bairro"]), $item["logradouro"], true, "logradouro", "195px", 'atualizaOperacoesLotesLogradouro(document.getElementById(\\\'cidade\\\').value,document.getElementById(\\\'bairro\\\').value,this.value)')."';";
    print 'if (document.getElementById("div_condominio")) document.getElementById("div_condominio").innerHTML = \''.$operacoes_lotes->geraSelect(buscaCondominios($item["cidade"], $item["bairro"], $item["logradouro"]), $item["condominio"], true, "condominio", "230px")."';";

  } else if ($_GET["a"] == "adicionar_filtro") {

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
    $listagem = $operacoes_lotes->geraFiltrosListagem($itens_atuais, true, $_GET["sistema"]);

    print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"filtros_lista\")) document.getElementById(\"filtros_lista\").value = '".@serialize($itens_atuais)."';";
    print "if (document.getElementById(\"campo\")) document.getElementById(\"campo\").focus();";
    print "filtro_linha_id = 0;";

  } else if ($_GET["a"] == "remover_filtro") {

    $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $operacoes_lotes->geraFiltrosListagem($itens_atuais, true, $_GET["sistema"]);

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
    $dados_filtro_tipo = $operacoes_lotes->buscaDadosLotesFiltros($_GET["id"]);
    print "if (document.getElementById(\"div_comparacao\")) document.getElementById(\"div_comparacao\").innerHTML = '".$operacoes_lotes->geraSelect($dados_filtro_tipo["parametros"]["comparacao"], $item["comparacao"], true, "comparacao", "120px")."';";
    if (is_array($dados_filtro_tipo["parametros"]["valor_conteudo"]) || !$dados_filtro_tipo["parametros"]["valor_conteudo"]) {
      if ($dados_filtro_tipo["parametros"]["valor_conteudo"]) {
        print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '".$operacoes_lotes->geraSelect($dados_filtro_tipo["parametros"]["valor_conteudo"], $item["valor"], true, "valor", "300px")."';";
      } else {
        print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '<input type=\"text\" name=\"valor\" id=\"valor\" value=\"{$item["valor"]}\" style=\"width:300px\" class=\"inputBrancoAzulEscuro11\"".($dados_filtro_tipo["parametros"]["formatacao"] ? " onkeypress=\"return {$dados_filtro_tipo["parametros"]["formatacao"]}\" onblur=\"return {$dados_filtro_tipo["parametros"]["formatacao"]}\"" : "").">';";
      }
    } else {
      print "if (document.getElementById(\"div_valor\")) document.getElementById(\"div_valor\").innerHTML = '".$operacoes_lotes->geraSelect($operacoes_lotes->buscaFiltroTipoConteudo($dados_filtro_tipo["parametros"]["valor_conteudo"]), $item["valor"], true, "valor", "300px")."';";
    }

  } else if ($_GET["a"] == "mudar_filtro") {

    // Filtros
    $itens_atuais = @unserialize(stripslashes($_GET["filtros_lista"]));
    $listagem = $operacoes_lotes->geraFiltrosListagem($itens_atuais, true, $_GET["sistema"]);
    print "if (document.getElementById(\"filtros_listagem\")) document.getElementById(\"filtros_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    // Últimos Contatos
    $ultimos_contatos = $operacoes_lotes->geraCheckBox($operacoes_lotes->buscaContatosCategorias($_GET["sistema"]), true, null);
    print "if (document.getElementById(\"ultimos_contatos\")) document.getElementById(\"ultimos_contatos\").innerHTML = '".str_replace("'", '\\\'', $ultimos_contatos)."';";

  } else if ($_GET["a"] == "adicionar_validade") {

    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["validades_lista"])));
    // verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["responsavel"] == $_GET["responsavel"] && $valor["inicio"] == $_GET["inicio"] && $valor["termino"] == $_GET["termino"] && $valor["periodo"] == $_GET["periodo"] && $chave != $_GET["id"] - 1) {
        print "alert('Validade já cadastrada!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["responsavel"] = $_GET["responsavel"];
    $novo_item["inicio"] = $_GET["inicio"];
    $novo_item["termino"] = $_GET["termino"];
    $novo_item["periodo"] = $_GET["periodo"];
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    sort($itens_atuais);
    $listagem = $operacoes_lotes->geraValidadesListagem($_GET["lote_id"], $itens_atuais, true);

    print "if (document.getElementById(\"validades_listagem\")) document.getElementById(\"validades_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"validades_lista\")) document.getElementById(\"validades_lista\").value = '".@serialize($itens_atuais)."';";
    print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").focus();";
    print "validade_linha_id = 0;";

  } else if ($_GET["a"] == "remover_validade") {

    $itens_atuais = @unserialize(stripslashes($_GET["validades_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $operacoes_lotes->geraValidadesListagem($_GET["lote_id"], $itens_atuais, true);

    print "if (document.getElementById(\"validades_listagem\")) document.getElementById(\"validades_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';";
    print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").value = '{$item["responsavel"]}';";
    print "if (document.getElementById(\"responsavel\")) mudaValidadeCampo({$item["responsavel"]}, {$_GET["id"]});";
    print "if (document.getElementById(\"validades_lista\")) document.getElementById(\"validades_lista\").value = '".@serialize($itens_atuais)."';";
    print "endereco_linha_id = 0;";

  } else if ($_GET["a"] == "selecionar_validade") {

    $itens_atuais = @unserialize(stripslashes($_GET["validades_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").value = '{$item["responsavel"]}';";
    print "if (document.getElementById(\"responsavel\")) mudaValidadeCampo({$item["responsavel"]}, {$_GET["id"]});";

  } else if ($_GET["a"] == "busca_dados_validade") {

    // Unserializa os dados do item selecionado
    if ($_GET["validade_linha_id"]) {
      $itens_atuais = @unserialize(stripslashes($_GET["validades_lista"]));
      $item = $itens_atuais[$_GET["validade_linha_id"] - 1];
    }
    // Muda os selects de acordo com o item selecionado
    print "if (document.getElementById(\"div_responsavel\")) document.getElementById(\"div_responsavel\").innerHTML = '".$operacoes_lotes->geraSelect($operacoes_lotes->buscaResponsaveis(), $item["responsavel"], true, "responsavel", "305px")."';";
    print "if (document.getElementById(\"div_inicio\")) document.getElementById(\"div_inicio\").innerHTML = '<input name=\"inicio\" id=\"inicio\" style=\"width:120px\" class=\"inputBrancoAzulEscuro11\" value=\"".$item["inicio"]."\" onKeyPress=\"return formataData(this,event);\" onBlur=\"return formataData(this,event);\">';";
    print "if (document.getElementById(\"div_termino\")) document.getElementById(\"div_termino\").innerHTML = '<input name=\"termino\" id=\"termino\" style=\"width:120px\" class=\"inputBrancoAzulEscuro11\" value=\"".$item["termino"]."\" onKeyPress=\"return formataData(this,event);\" onBlur=\"return formataData(this,event);\">';";
    print "if (document.getElementById(\"div_periodo\")) document.getElementById(\"div_periodo\").innerHTML = '".$operacoes_lotes->geraSelect($operacoes_lotes->buscaPeriodos(), $item["periodo"], true, "periodo", "120px")."';";

  } else if ($_GET["a"] == "atualiza_cidade") {

  	$bairros = buscaBairros($_GET["cidade"]);
    if ($bairros) {
      print geraScriptAtualizacaoSelect("bairro",$bairros);
      print geraScriptAtualizacaoSelect("logradouro");
      print geraScriptAtualizacaoSelect("condominio",buscaCondominios($_GET["cidade"]));
    } else {
      print geraScriptAtualizacaoSelect("bairro");
      print geraScriptAtualizacaoSelect("logradouro");
      print geraScriptAtualizacaoSelect("condominio",buscaCondominios());
    }

  } else if ($_GET["a"] == "atualiza_bairro") {

  	$logradouros = buscaLogradouros($_GET["bairro"]);
    if ($logradouros) {
      print geraScriptAtualizacaoSelect("logradouro",$logradouros);
      print geraScriptAtualizacaoSelect("condominio",buscaCondominios($_GET["cidade"],$_GET["bairro"]));
    } else {
      print geraScriptAtualizacaoSelect("logradouro");
      print geraScriptAtualizacaoSelect("condominio",buscaCondominios($_GET["cidade"]));
    }

  } else if ($_GET["a"] == "atualiza_logradouro") {

  	$condominios = buscaCondominios($_GET["cidade"], $_GET["bairro"], $_GET["logradouro"]);
    if ($condominios) {
      print geraScriptAtualizacaoSelect("condominio",$condominios);
    } else {
      print geraScriptAtualizacaoSelect("condominio",buscaCondominios($_GET["cidade"],$_GET["bairro"]));
    }

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verOperacao(operacao) {
	if (operacao) {
	  abrir("operacoes", "?m=operacoes&a=ver&i=" + operacao, 320, 320, 1);
	}
}

function inicializaOperacoesLotes (enderecos_lista, filtros_lista) {
  url = "rds.js.php?m=operacoes_lotes&a=inicializa&enderecos_lista=" + enderecos_lista + "&filtros_lista=" + filtros_lista;
  RDSCarregar("operacoes_lotes_inicializar_lote",url);
}

function atualizaOperacoesLotesCidade(cidade) {
  url = "rds.js.php?m=operacoes_lotes&a=atualiza_cidade&cidade=" + cidade;
  RDSCarregar("operacoes_lotes_atualizar_cidade",url);
}

function atualizaOperacoesLotesBairro(cidade,bairro) {
  url = "rds.js.php?m=operacoes_lotes&a=atualiza_bairro&cidade=" + cidade + "&bairro=" + bairro;
  RDSCarregar("operacoes_lotes_atualizar_bairro",url);
}

function atualizaOperacoesLotesLogradouro(cidade,bairro,logradouro) {
  url = "rds.js.php?m=operacoes_lotes&a=atualiza_logradouro&cidade=" + cidade + "&bairro=" + bairro + "&logradouro=" + logradouro;
  RDSCarregar("operacoes_lotes_atualizar_logradouro",url);
}

function mudaSistemaRegras(categoria) {
  if (categoria) {
	  // Seta no backup os Filtros do Sistema anterior
    backup = document.getElementById('filtros_lista').value;
    document.getElementById('filtros_lista').value = document.getElementById('filtros_lista_backup').value;
    document.getElementById('filtros_lista_backup').value = backup;
    // RDS
    url = "rds.js.php?m=operacoes_lotes&a=mudar_filtro&sistema=" + document.getElementById('sistema').value + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&id=" + filtro_linha_id;
    RDSCarregar("operacoes_lotes_mudar_filtro",url);
  }
}

function validaOperacaoLote (formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";
  if (!formulario.status.value) erro_vazio[erro_vazio.length] = "Status";

  return apresentaErros(erro_vazio,erro_invalido);

}

////////////////////////////////////////
// Funções da Listagem de Endereços ////
////////////////////////////////////////

var endereco_linha_id = 0;

function adicionarEndereco() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('cidade').value) {
    erro_vazio[erro_vazio.length] = "Cidade";
  }
  if (!document.getElementById('bairro').value) {
    erro_vazio[erro_vazio.length] = "Bairro";
  }
  if (!document.getElementById('logradouro').value) {
    erro_vazio[erro_vazio.length] = "Logradouro";
  }
  if (!document.getElementById('condominio').value) {
    erro_vazio[erro_vazio.length] = "Condomínio";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=operacoes_lotes&a=adicionar_endereco&cidade=" + document.getElementById('cidade').value + "&bairro=" + document.getElementById('bairro').value + "&logradouro=" + document.getElementById('logradouro').value + "&condominio=" + document.getElementById('condominio').value + "&enderecos_lista=" + document.getElementById('enderecos_lista').value  + "&id=" + endereco_linha_id;
    RDSCarregar("operacoes_lotes_adicionar_endereco", url);
  }
}

function removerEndereco(id) {
  if (id && confirm('Tem certeza que deseja remover este Endereco?')) {
	  url = "rds.js.php?m=operacoes_lotes&a=remover_endereco&id=" + id + "&enderecos_lista=" + document.getElementById('enderecos_lista').value;
    RDSCarregar("operacoes_lotes_remover_endereco", url);
  }
}

function selecionarLinhaEndereco() {
  if (endereco_linha_id) {
	  url = "rds.js.php?m=operacoes_lotes&a=selecionar_endereco&id=" + endereco_linha_id + "&enderecos_lista=" + document.getElementById('enderecos_lista').value;
	  RDSCarregar("operacoes_lotes_selecionar_endereco", url);
  }
}

function selecionarEndereco(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  endereco_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("cidade").value = "";
        mudaEnderecoCampo(document.getElementById("cidade").value);
        document.getElementById("bairro").value = "";
        document.getElementById("logradouro").value = "";
        document.getElementById("condominio").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        endereco_linha_id = id;
        selecionarLinhaEndereco();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function mudaEnderecoCampo(valor, id) {
  document.getElementById('cidade').disabled = !valor;
  document.getElementById('bairro').disabled = !valor;
  document.getElementById('logradouro').disabled = !valor;
  document.getElementById('condominio').disabled = !valor;
  document.getElementById('cidade').value = "";
  document.getElementById('bairro').value = "";
  document.getElementById('logradouro').value = "";
  document.getElementById('condominio').value = "";
  if (valor) {
	  url = "rds.js.php?m=operacoes_lotes&a=busca_dados_endereco&id=" + valor + "&enderecos_lista=" + document.getElementById('enderecos_lista').value + "&endereco_linha_id=" + id;
    RDSCarregar("operacoes_lotes_busca_dados_endereco", url);
  }
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
    url = "rds.js.php?m=operacoes_lotes&a=adicionar_filtro&campo=" + document.getElementById('campo').value + "&comparacao=" + document.getElementById('comparacao').value + "&valor=" + document.getElementById('valor').value + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&id=" + filtro_linha_id + "&sistema=" + document.getElementById('sistema').value;
    RDSCarregar("operacoes_lotes_adicionar_filtro", url);
  }
}

function removerFiltro(id) {
  if (id && confirm('Tem certeza que deseja remover este Filtro?')) {
	  url = "rds.js.php?m=operacoes_lotes&a=remover_filtro&id=" + id + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&sistema=" + document.getElementById('sistema').value;
    RDSCarregar("operacoes_lotes_remover_filtro", url);
  }
}

function selecionarLinhaFiltro() {
  if (filtro_linha_id) {
	  url = "rds.js.php?m=operacoes_lotes&a=selecionar_filtro&id=" + filtro_linha_id + "&filtros_lista=" + document.getElementById('filtros_lista').value;
	  RDSCarregar("operacoes_lotes_selecionar_filtro", url);
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
	  url = "rds.js.php?m=operacoes_lotes&a=busca_dados_filtro&id=" + valor + "&filtros_lista=" + document.getElementById('filtros_lista').value + "&filtro_linha_id=" + id;
    RDSCarregar("operacoes_lotes_busca_dados_filtro", url);
  }
}

////////////////////////////////////////
// Funções da Listagem de Validades ////
////////////////////////////////////////

var validade_linha_id = 0;

function adicionarValidade() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('responsavel').value) {
    erro_vazio[erro_vazio.length] = "Responsável";
  }
  if (!document.getElementById('periodo').value) {
    erro_vazio[erro_vazio.length] = "Período";
  }
  if (document.getElementById('inicio').value && !validaData(document.getElementById('inicio').value)) {
    erro_invalido[erro_invalido.length] = "Início";
  }
  if (document.getElementById('termino').value && !validaData(document.getElementById('termino').value)) {
    erro_invalido[erro_invalido.length] = "Término";
  }
  if (document.getElementById('inicio').value && document.getElementById('termino').value && subtraiDatas(document.getElementById('termino').value,document.getElementById('inicio').value) <= 1) {
    erro_invalido[erro_invalido.length] = "Término deve ser maior que Início";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=operacoes_lotes&a=adicionar_validade&responsavel=" + document.getElementById('responsavel').value + "&inicio=" + document.getElementById('inicio').value + "&termino=" + document.getElementById('termino').value + "&periodo=" + document.getElementById('periodo').value + "&validades_lista=" + document.getElementById('validades_lista').value  + "&id=" + validade_linha_id  + "&lote_id=" + (lote_id ? lote_id : 0);
    RDSCarregar("operacoes_lotes_adicionar_validade", url);
  }
}

function removerValidade(id) {
  if (id && confirm('Tem certeza que deseja remover esta Validade?')) {
	  url = "rds.js.php?m=operacoes_lotes&a=remover_validade&id=" + id + "&validades_lista=" + document.getElementById('validades_lista').value+ "&lote_id=" + (lote_id ? lote_id : 0);
    RDSCarregar("operacoes_lotes_remover_validade", url);
  }
}

function selecionarLinhaValidade() {
  if (validade_linha_id) {
	  url = "rds.js.php?m=operacoes_lotes&a=selecionar_validade&id=" + validade_linha_id + "&validades_lista=" + document.getElementById('validades_lista').value;
	  RDSCarregar("operacoes_lotes_selecionar_validade", url);
  }
}

function selecionarValidade(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  validade_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("responsavel").value = "";
        mudaValidadeCampo(document.getElementById("responsavel").value);
        document.getElementById("inicio").value = "";
        document.getElementById("termino").value = "";
        document.getElementById("periodo").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        validade_linha_id = id;
        selecionarLinhaValidade();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function mudaValidadeCampo(valor, id) {
  document.getElementById('responsavel').disabled = !valor;
  document.getElementById('inicio').disabled = !valor;
  document.getElementById('termino').disabled = !valor;
  document.getElementById('periodo').disabled = !valor;
  document.getElementById('cidade').value = "";
  document.getElementById('responsavel').value = "";
  document.getElementById('termino').value = "";
  document.getElementById('periodo').value = "";
  if (valor) {
	  url = "rds.js.php?m=operacoes_lotes&a=busca_dados_validade&id=" + valor + "&validades_lista=" + document.getElementById('validades_lista').value + "&validade_linha_id=" + id;
    RDSCarregar("operacoes_lotes_busca_dados_validade", url);
  }
}

<?

	if (false) { ?></script><? }

}

?>
