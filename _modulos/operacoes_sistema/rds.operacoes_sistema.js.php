<?

function geraDadosCliente($cliente, $sistema, $contato_registro) {
  if ($cliente) {
    GLOBAL $operacoes_sistema;
    // Dados do Div Operação
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("operacao_cliente_atual")', 'value', $cliente);

    // Dados do Div Contato
    $dados_cliente = $operacoes_sistema->buscaDadosCliente($cliente);

    print $operacoes_sistema->geraCodigoAlterarValor('contato_inicio', '', 'new Date()');
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_registro")', 'innerHTML', $contato_registro);
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_inicio")', 'value', date("Y-m-d H:i:s"));
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_nome")', 'innerHTML', $dados_cliente["nome"]);
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_endereco")', 'innerHTML', $dados_cliente["endereco"]);
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_telefone")', 'innerHTML', $dados_cliente["telefones"]);
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_planos")', 'innerHTML', $dados_cliente["planos"]);
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_status")', 'innerHTML', $dados_cliente["status"]);
    if ($sistema == 1) {
      print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_responsavel")', 'innerHTML', $dados_cliente["responsavel"]);
    } else {
      print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("contato_cobrancas_em_aberto")', 'innerHTML', $operacoes_sistema->buscaClienteCobrancas($cliente));
    }

    // Dados do Div Histórico
    print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("historico_conteudo")', 'innerHTML', $operacoes_sistema->geraListagemHistorico($operacoes_sistema->buscaClientesContatos($cliente, $sistema)));
  }
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

function buscaClienteNoLote($query) {
  GLOBAL $bancodedados;
  $query = $bancodedados->query(stripslashes($query));
  if ($bancodedados->linhas($query)) {
    return true;
  } else {
    return false;
  }
}

function buscaScriptConteudo($script_id) {
  GLOBAL $bancodedados;
  $query = "
			SELECT
			  ops_conteudo AS conteudo,
			  osf_id AS formato
			FROM
			  tab_operacoes_scripts
			WHERE
			  ops_id = $script_id";
  $query = $bancodedados->query($query);
  $dados = $bancodedados->resultadoArray($query);
  if ($dados) {
    $dados = preg_replace("/[\r\n]+/", "%0D%0A", $dados["conteudo"]);
  	return $dados;
  }
}

function buscaScripts($lote_id) {
  GLOBAL $bancodedados;
  $query = "
			SELECT DISTINCT
			  tab_operacoes_scripts.ops_id AS id,
			  CONCAT(ost_nome, ' » ', ops_titulo) AS nome
			FROM
			  tab_operacoes_lotes
			  INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_lotes.oli_id = tab_operacoes_lotes_sistemas.oli_id)
			  INNER JOIN tab_operacoes_scripts ON (tab_operacoes_lotes_sistemas.oli_id = tab_operacoes_scripts.oli_id)
			  INNER JOIN tab_operacoes_scripts_tipos ON (tab_operacoes_scripts.ost_id = tab_operacoes_scripts_tipos.ost_id)
			WHERE
			  tab_operacoes_scripts.osa_id = 1 AND
			  tab_operacoes_lotes.opl_id = $lote_id
			ORDER BY
			  nome";
  $query = $bancodedados->query($query);
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
  	$scripts[$dados["id"]] = $dados["nome"];
  }
  return $scripts;
}

if ($_GET["a"]) {

	global $bancodedados;
	
  $operacoes_sistema = controle::instanciaObjeto("operacoes_sistema", $bancodedados);
	
	if ($_GET["a"] == "muda_cliente_posterior") {
	
	  $clientes = $_SESSION["operacoes_sistema"]["clientes"];
	  if (count($clientes["lote"]) > 1) {
	    for ($i = 0; $i < count($clientes["lote"]); $i++) {
	      if ($clientes["lote"][$i] == $_GET["cliente"]) {
	        // Pega o proximo cliente do lote
	        $j = $i + 1;
	        $proximo = $clientes["lote"][$j];
	        if (!$proximo) {
	          $j = 0;
	          $proximo = $clientes["lote"][$j];
	        }
	
	        // Faz a consulta para ver se aquele cliente em especifico continua no lote
	        if (buscaClienteNoLote(str_replace("[cliente_id]", "$proximo", $_GET["consulta_sql"]))) {
	          break;
	        } else {
	          // Retira o cliente do lote caso ele não esteja mais nas regras
	          $i = $i - 1;
	          $lote_alterado = true;
	          unset($clientes["lote"][$j]);
	          sort($clientes["lote"]);
	        }
	
	        if (count($clientes["lote"]) == 1) {
	          $proximo = false;
	          print "alert('Não existe mais nenhum cliente nesse lote.');";
	          break;
	        }
	      }
	    }
	
	    if ($lote_alterado) {
	      $_SESSION["operacoes_sistema"]["clientes"] = $clientes;
	    }
	
	    foreach ($clientes["lote"] as $chave => $cliente) {
	      if ($cliente == $proximo) {
	        $registro_codigo = "(".($chave + 1)." / ".count($clientes["lote"]).")";
	        break;
	      }
	    }
	
	    geraDadosCliente($proximo, $_GET["sistema"], $registro_codigo);
	
	  } else {
	    print "alert('Não existe mais nenhum cliente nesse lote.');";
	  }
	
	} elseif ($_GET["a"] == "muda_cliente_anterior") {
	
	  $clientes = $_SESSION["operacoes_sistema"]["clientes"];
	  if (count($clientes["lote"]) > 1) {
	    for ($i = count($clientes["lote"]); $i >= 0; $i--) {
	      if ($clientes["lote"][$i] == $_GET["cliente"]) {
	        // Pega o proximo cliente do lote
	        $j = $i - 1;
	        $anterior = $clientes["lote"][$j];
	        if (!$anterior) {
	          $j = count($clientes["lote"]) - 1;
	          $anterior = $clientes["lote"][$j];
	        }
	
	        // Faz a consulta para ver se aquele cliente em especifico continua no lote
	        if (buscaClienteNoLote(str_replace("[cliente_id]", "$anterior", $_GET["consulta_sql"]))) {
	          break;
	        } else {
	          // Retira o cliente do lote caso ele não esteja mais nas regras
	          $i = $i + 1;
	          $lote_alterado = true;
	          unset($clientes["lote"][$j]);
	          sort($clientes["lote"]);
	        }
	
	        if (count($clientes["lote"]) == 1) {
	          $anterior = false;
	          print "alert('Não existe mais nenhum cliente nesse lote.');";
	          break;
	        }
	      }
	    }
	
	    if ($lote_alterado) {
	      $_SESSION["operacoes_sistema"]["clientes"] = $clientes;
	    }
	
	    foreach ($clientes["lote"] as $chave => $cliente) {
	      if ($cliente == $anterior) {
	        $registro_codigo = "(".($chave + 1)." / ".count($clientes["lote"]).")";
	        break;
	      }
	    }
	
	    geraDadosCliente($anterior, $_GET["sistema"], $registro_codigo);
	  } else {
	    print "alert('Não existe mais nenhum cliente nesse lote.');";
	  }
	
	} elseif ($_GET["a"] == "registra_contato") {
	
	  // Registra Contato
	  $termino = date("Y-m-d H:i:s");
	  $_GET["comentario"] = addslashes($_GET["comentario"]);
	  $query = "INSERT INTO tab_operacoes_contatos (cli_id, opc_inicio, opc_termino, opc_comentario, usu_id, ope_id, occ_id) VALUES ({$_GET["cliente"]}, '{$_GET["inicio"]}', '$termino', '{$_GET["comentario"]}', {$_SESSION["usuario"]}, {$_SESSION["operacao_id"]}, {$_GET["categoria"]})";
	  $query = $bancodedados->query($query);
	
	  // Inserir responsabilidade se contato for positivo e pós-venda
	  // Categoria: Cliente ativo (Migração)
	  	  
	  // Atualiza o Histórico de Contatos
	  print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("historico_conteudo")', 'innerHTML', $operacoes_sistema->geraListagemHistorico($operacoes_sistema->buscaClientesContatos($_GET["cliente"], $_GET["sistema"])));
	
	  // Atualiza Estatisticas
	  print $operacoes_sistema->geraCodigoAlterarValor('document.getElementById("operacao_estatisticas")', 'innerHTML', $operacoes_sistema->geraOperacaoEstatisticas($_SESSION["operacao_id"], $_GET["sistema"]));
	  
	} elseif ($_GET["a"] == "gera_scripts") {
	
	  if ($_GET["lote"]) {
	    // Usa o ID do lote para buscar os scripts daquele sistema
	    $scripts = buscaScripts($_GET["lote"]);
	    if ($scripts) {
	      $disabled = "false";
	      print geraScriptAtualizacaoSelect("script_nome", $scripts);
	    } else {
	      $disabled = "true";
	      print "if (document.getElementById(\"script_conteudo\")) document.getElementById(\"script_conteudo\").innerHTML = '';";
	    }
	  } else {
	    $disabled = "true";
	    print "if (document.getElementById(\"script_conteudo\")) document.getElementById(\"script_conteudo\").innerHTML = '';";
	  }
	  print "if (document.getElementById(\"script_nome\")) { document.getElementById(\"script_nome\").disabled = $disabled; document.getElementById(\"script_nome\").selectedIndex = ''; } ";
	
	} elseif ($_GET["a"] == "muda_script") {
	
	  if ($_GET["script"]) {
	    print "if (document.getElementById(\"script_conteudo\")) document.getElementById(\"script_conteudo\").innerHTML = unescape('".buscaScriptConteudo($_GET["script"])."');";
	  } else {
	    print "if (document.getElementById(\"script_conteudo\")) document.getElementById(\"script_conteudo\").innerHTML = '';";
	  }
	
	} elseif ($_GET["a"] == "atualiza_operacao") {
	
	  if ($_SESSION["operacao_id"]) {
	    $operacoes_sistema->atualizaOperacao($_SESSION["operacao_id"]);
	  }

  } else {
  	
	  $operacoes_sistema->executa();
	  print "redimensionaDivs();";
	  
  }
	  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

/////////////////////////////////////////////
// Funções de Inicialização e Temporização //
/////////////////////////////////////////////

function inicializaOperacaoSistema() {
  url = "rds.js.php?m=operacoes_sistema&a=inicializa";
  RDSCarregar("operacoes_sistema_inicializa", url);
}

function atualizaOperacao() {
  if (document.getElementById('operacao_lote').value) {
    url = "rds.js.php?m=operacoes_sistema&a=atualiza_operacao";
    RDSCarregar("operacoes_sistema_atualiza_operacao", url);
    event.returnValue = "Fechar esta janela implicará na finalização da operação atual.";
  }
}

function inicializaSistemaOperacaoTempos() {
  atualizaSistemaOperacaoTempos();
  setInterval("atualizaSistemaOperacaoTempos()", 1000);
}

function atualizaSistemaOperacaoTempos() {
  var agora = new Date();
  atualizaSistemaOperacaoDataHora(agora);
  atualizaSistemaOperacaoDuracao(agora);
  atualizaSistemaContatoDuracao(agora);
}

function atualizaSistemaOperacaoDataHora (agora) {
  var dia = agora.getDate(); if (dia < 10) dia = "0" + dia;
  var mes = agora.getMonth() + 1; if (mes < 10) mes = "0" + mes;
  var hora = agora.getHours(); if (hora < 10) hora = "0" + hora;
  var minuto = agora.getMinutes(); if (minuto < 10) minuto = "0" + minuto;
  if (document.getElementById("data_hora")) {
    document.getElementById("data_hora").innerHTML = dia + "/" + mes + " (" + hora + ":" + minuto + ")";
  }
}

function atualizaSistemaOperacaoDuracao(agora) {
  if (operacao_inicio) {
    var diferenca = agora.getTime() - operacao_inicio.getTime();
    var dia = Math.floor(diferenca/1000/60/60/24);
    diferenca -= dia*1000*60*60*24;
    var hora = Math.floor(diferenca/1000/60/60);
    diferenca -= hora*1000*60*60
    var minuto = Math.floor(diferenca/1000/60);
    diferenca -= minuto*1000*60
    var segundo = Math.floor(diferenca/1000);
    if (hora < 10) hora = "0" + hora;
    if (minuto < 10) minuto = "0" + minuto;
    if (segundo < 10) segundo = "0" + segundo;
    
    //var operacao_duracao = new Date(agora.getTime() - operacao_inicio.getTime());
    //operacao_duracao.setHours(operacao_duracao.getHours() - 21);
    //var hora = operacao_duracao.getHours(); if (hora < 10) hora = "0" + hora;
    //var minuto = operacao_duracao.getMinutes(); if (minuto < 10) minuto = "0" + minuto;
    //var segundo = operacao_duracao.getSeconds(); if (segundo < 10) segundo = "0" + segundo;
    if (document.getElementById("operacao_duracao")) {
      document.getElementById("operacao_duracao").innerHTML = hora + ":" + minuto + ":" + segundo;
    }
  }
}

function atualizaSistemaContatoDuracao(agora) {
  if (contato_inicio) {
    var diferenca = agora.getTime() - contato_inicio.getTime();
    var dia = Math.floor(diferenca/1000/60/60/24);
    diferenca -= dia*1000*60*60*24;
    var hora = Math.floor(diferenca/1000/60/60);
    diferenca -= hora*1000*60*60
    var minuto = Math.floor(diferenca/1000/60);
    diferenca -= minuto*1000*60
    var segundo = Math.floor(diferenca/1000);
    if (hora < 10) hora = "0" + hora;
    if (minuto < 10) minuto = "0" + minuto;
    if (segundo < 10) segundo = "0" + segundo;

    //var contato_duracao = new Date(agora.getTime() - contato_inicio.getTime());
    //contato_duracao.setHours(contato_duracao.getHours() - 21);
    //var hora = contato_inicio.getHours() - agora.getHours(); if (hora < 10) hora = "0" + hora;
    //var minuto = contato_inicio.getMinutes(); if (minuto < 10) minuto = "0" + minuto;
    //var segundo = contato_duracao.getSeconds(); if (segundo < 10) segundo = "0" + segundo;
    if (document.getElementById("contato_duracao")) {
      document.getElementById("contato_duracao").innerHTML = hora + ":" + minuto + ":" + segundo;
    }
  }
}

function alertaOperacaoSistemaOperacaoNenhumLoteDisponivel() {
  var mensagem;
  mensagem = "Não existe nenhum Lote disponível para operação!\n";
  mensagem += "\n";
  mensagem += "Deseja finalizar o sistema?";
  if (confirm(mensagem)) {
    window.close();
  }
}

function alertaOperacaoSistemaOperacaoNenhumRegistroDisponivel() {
  var mensagem;
  mensagem = "Não há nenhum Registro disponível neste Lote para operação!\n";
  alert(mensagem);
}

function mudaOperacaoSistemaOperacao(lote) {
  if (operacao_lote) {
    mensagem  = "A alteração do Lote para Operação implicará na finalização da operação atual";
    mensagem += (lote.value) ? " e abertura de uma nova operação!\n" : "!\n";
    mensagem += "\n";
    mensagem += "Você deseja realmente alterá-lo?";
    if (!confirm(mensagem)) {
      for (i = 0; i < lote.options.length; i++) {
        if (lote.options[i].value == operacao_lote) {
          lote.options[i].selected = true;
        }
      }
      return false;
    }
  }
  url = "rds.js.php?m=operacoes_sistema&a=muda_operacao&lote=" + lote.value;
  RDSCarregar("operacoes_sistema_muda_operacao", url);

  // Gera os scripts disponiveis para esse sistema
  geraScripts(lote.value);
}

//////////////////////////////////////
// Funções relacionadas aos Scripts //
//////////////////////////////////////

function geraScripts(lote_id) {
  url = "rds.js.php?m=operacoes_sistema&a=gera_scripts&lote=" + lote_id;
  RDSCarregar("operacoes_sistema_gera_scripts",url);
}

function mudaScriptConteudo(script) {
  url = "rds.js.php?m=operacoes_sistema&a=muda_script&script=" + script;
  RDSCarregar("operacoes_sistema_muda_script",url);
}

////////////////////
// Funções Gerais //
////////////////////

function abreClienteViaOperacao(acao) {
  if (window.opener != null) {
    window.opener.location="?m=clientes&a=ver_" + acao + "&i=" + document.getElementById("operacao_cliente_atual").value;
  } else {
    url = "?m=clientes&a=ver_" + acao + "&i=" + document.getElementById("operacao_cliente_atual").value;
    window.open(url, "dados_cliente");
  }
}

function buscaClienteAnterior() {
  url = "rds.js.php?m=operacoes_sistema&a=muda_cliente_anterior&cliente=" +  document.getElementById("operacao_cliente_atual").value + "&clientes=" + document.getElementById("operacao_clientes").value + "&sistema=" + document.getElementById("operacao_sistema").value + "&consulta_sql=" + document.getElementById("operacao_consulta_sql").value;
  RDSCarregar("operacoes_sistema_muda_cliente_anterior",url);
}

function buscaClientePosterior() {
  url = "rds.js.php?m=operacoes_sistema&a=muda_cliente_posterior&cliente=" + document.getElementById("operacao_cliente_atual").value + "&clientes=" + document.getElementById("operacao_clientes").value + "&sistema=" + document.getElementById("operacao_sistema").value + "&consulta_sql=" + document.getElementById("operacao_consulta_sql").value;
  RDSCarregar("operacoes_sistema_muda_cliente_posterior", url);
}

function editaClienteContatos() {
  abrir("cliente_contatos", "?m=clientes&a=editar_ct&i=" + document.getElementById("operacao_cliente_atual").value, 420, 400, 1);
}

function registraClienteContato() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById("contato_categoria").value) {
    erro_vazio[erro_vazio.length] = "Categoria";
  }

  if (erro_vazio.length > 0) {
    apresentaErros(erro_vazio, erro_invalido);
  } else {
    url = "rds.js.php?m=operacoes_sistema&a=registra_contato&cliente=" + document.getElementById("operacao_cliente_atual").value + "&categoria=" + document.getElementById("contato_categoria").value + "&sistema=" + document.getElementById("operacao_sistema").value + "&comentario=" + escape(document.getElementById("contato_comentario").value,1) + "&inicio=" + document.getElementById("contato_inicio").value;
    RDSCarregar("operacoes_sistema_registra_contato", url);
    document.getElementById("contato_categoria").value = '';
    document.getElementById("contato_comentario").value = '';
  }
}

function adicionaClienteRetorno() {
  abrir("clientes_retornos", "?m=clientes_retornos&a=adicionar&cli=" + document.getElementById("operacao_cliente_atual").value, 450, 305, 0);
}

var operacao_lote;
var operacao_inicio;
var contato_inicio;

<?

	if (false) { ?></script><? }

}

?>
