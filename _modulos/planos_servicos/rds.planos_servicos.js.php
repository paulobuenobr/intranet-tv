<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$planos_servicos = controle::instanciaObjeto("planos_servicos", $bancodedados, $seguranca->permissoes, false);
	
	if ($_GET["a"] == "adicionar_forma_pagamento") {
		
		$itens_atuais = @unserialize(stripslashes(stripslashes($_GET["formas_pagamento_lista"])));
		// Trata novos itens
		$novo_item["categoria"] = intval($_GET["categoria"], 10);
		$novo_item["entrada"] = intval(preg_replace("/[^0-9]/", "", $_GET["entrada"]), 10);
		$novo_item["parcelas"] = intval(preg_replace("/[^0-9]/", "", $_GET["parcelas"]), 10);
		$novo_item["valor_parcela"] = intval(preg_replace("/[^0-9]/", "", $_GET["valor_parcela"]), 10);
		$novo_item["forma"] = intval(preg_replace("/[^0-9]/", "", $_GET["forma"]), 10);
		$novo_item["multa"] = intval(preg_replace("/[^0-9]/", "", $_GET["multa"]), 10);
		$novo_item["fidelidade"] = intval(preg_replace("/[^0-9]/", "", $_GET["fidelidade"]), 10);
		if ($_GET["id"]) {
			$novo_item["id"] = $itens_atuais[$_GET["id"] - 1]["id"];
			$itens_atuais[$_GET["id"] - 1] = $novo_item;
		} else {
			$itens_atuais[] = $novo_item;
		}
		for ($i = 0; $i < count($itens_atuais); $i++) {
			$ordenado[$itens_atuais[$i]["categoria"]][] = $itens_atuais[$i];
		}
		ksort($ordenado);
		$itens_atuais = array();
		foreach ($ordenado as $itens) {
			foreach ($itens as $item) {
				$itens_atuais[] = $item;
			}
		}
		$listagem = $planos_servicos->buscaFormasPagamentoListagem($itens_atuais, true);
		print "
      if (document.getElementById(\"formas_pagamento_listagem\")) {
        document.getElementById(\"formas_pagamento_listagem\").innerHTML = '" . str_replace("'", '\\\'', $listagem) . "';
      }
      if (document.getElementById(\"formas_pagamento_lista\")) {
        document.getElementById(\"formas_pagamento_lista\").value = '" . serialize($itens_atuais) . "';
      }
      if (document.getElementById(\"formas_pagamento_categoria\")) {
        document.getElementById(\"formas_pagamento_categoria\").focus();
      }
      forma_pagamento_linha_id = 0;";
	
	} elseif ($_GET["a"] == "remover_forma_pagamento") {
		
		$itens_atuais = @unserialize(stripslashes($_GET["formas_pagamento_lista"]));
		$item = $itens_atuais[$_GET["id"] - 1];
		for ($i = 0; $i < count($itens_atuais); $i++) {
			if ($i != $_GET["id"] - 1) {
				$novos_itens[] = $itens_atuais[$i];
			}
		}
		$itens_atuais = $novos_itens;
		$listagem = $planos_servicos->buscaFormasPagamentoListagem($itens_atuais, true);
		print "
      if (document.getElementById(\"formas_pagamento_listagem\")) {
        document.getElementById(\"formas_pagamento_listagem\").innerHTML = '" . str_replace("'", '\\\'', $listagem) . "';
      }
      if (document.getElementById(\"formas_pagamento_categoria\")) {
        document.getElementById(\"formas_pagamento_categoria\").value = '{$item["categoria"]}';
      }
      if (document.getElementById(\"formas_pagamento_entrada\")) {
        document.getElementById(\"formas_pagamento_entrada\").value = '{$item["entrada"]}';
      }
      if (document.getElementById(\"formas_pagamento_parcelas\")) {
        document.getElementById(\"formas_pagamento_parcelas\").value = '{$item["parcelas"]}';
      }
      if (document.getElementById(\"formas_pagamento_valor_parcela\")) {
        document.getElementById(\"formas_pagamento_valor_parcela\").value = '{$item["valor_parcela"]}';
      }
      if (document.getElementById(\"formas_pagamento_forma\")) {
        document.getElementById(\"formas_pagamento_forma\").value = '{$item["forma"]}';
      }
      if (document.getElementById(\"formas_pagamento_multa\")) {
        document.getElementById(\"formas_pagamento_multa\").value = '{$item["multa"]}';
      }
      if (document.getElementById(\"formas_pagamento_fidelidade\")) {
        document.getElementById(\"formas_pagamento_fidelidade\").value = '{$item["fidelidade"]}';
      }
      if (document.getElementById(\"formas_pagamento_lista\")) {
        document.getElementById(\"formas_pagamento_lista\").value = '" . serialize($itens_atuais) . "';
      }
      forma_pagamento_linha_id = 0;";
	
	} elseif ($_GET["a"] == "selecionar_forma_pagamento") {
		
		$itens_atuais = @unserialize(stripslashes($_GET["formas_pagamento_lista"]));
		$item = $itens_atuais[$_GET["id"] - 1];
		$item["entrada"] = $item["entrada"] > 0 ? $planos_servicos->formataDinheiro($item["entrada"]) : "";
		$item["parcelas"] = $item["parcelas"] > 0 ? $item["parcelas"] : "";
		$item["valor_parcela"] = $item["valor_parcela"] > 0 ? $planos_servicos->formataDinheiro($item["valor_parcela"]) : "";
		$item["multa"] = $item["multa"] > 0 ? $planos_servicos->formataDinheiro($item["multa"]) : "";
		$item["fidelidade"] = $item["fidelidade"] > 0 ? $item["fidelidade"] : "";
		print "
      if (document.getElementById(\"formas_pagamento_categoria\")) {
        document.getElementById(\"formas_pagamento_categoria\").value = '{$item["categoria"]}';
      }
      if (document.getElementById(\"formas_pagamento_entrada\")) {
        document.getElementById(\"formas_pagamento_entrada\").value = '{$item["entrada"]}';
      }
      if (document.getElementById(\"formas_pagamento_parcelas\")) {
        document.getElementById(\"formas_pagamento_parcelas\").value = '{$item["parcelas"]}';
      }
      if (document.getElementById(\"formas_pagamento_valor_parcela\")) {
        document.getElementById(\"formas_pagamento_valor_parcela\").value = '{$item["valor_parcela"]}';
      }
      if (document.getElementById(\"formas_pagamento_forma\")) {
        document.getElementById(\"formas_pagamento_forma\").value = '{$item["forma"]}';
      }
      if (document.getElementById(\"formas_pagamento_multa\")) {
        document.getElementById(\"formas_pagamento_multa\").value = '{$item["multa"]}';
      }
      if (document.getElementById(\"formas_pagamento_fidelidade\")) {
        document.getElementById(\"formas_pagamento_fidelidade\").value = '{$item["fidelidade"]}';
      }";
	
	}  elseif ($_GET["a"] == "adicionar_classificacao_fiscal") {
		
		$itens_atuais = @unserialize(stripslashes(stripslashes($_GET["classificacao_fiscal_lista"])));
		// Trata novos itens
		$novo_item["tipo_id"] = $_GET["tipo_id"];
		$novo_item["taxa"] = floatval(str_replace(",", ".", preg_replace("/[^0-9,.]/", "", $_GET["taxa"])));
		if ($_GET["id"]) {
			$novo_item["id"] = $itens_atuais[$_GET["id"] - 1]["id"];
			$itens_atuais[$_GET["id"] - 1] = $novo_item;
		} else {
			$itens_atuais[] = $novo_item;
		}
		/*
		for ($i = 0; $i < count($itens_atuais); $i++) {
			$ordenado[$itens_atuais[$i]["sigla"]][] = $itens_atuais[$i];
		}
		ksort($ordenado);
		$itens_atuais = array();
		foreach ($ordenado as $itens) {
			foreach ($itens as $item) {
				$itens_atuais[] = $item;
			}
		} */
		$listagem = $planos_servicos->buscaClassificacaoFiscalListagem($itens_atuais, true);
		print "
      if (document.getElementById(\"classificacao_fiscal_listagem\")) {
        document.getElementById(\"classificacao_fiscal_listagem\").innerHTML = '" . str_replace("'", '\\\'', $listagem) . "';
      }
      if (document.getElementById(\"classificacao_fiscal_lista\")) {
        document.getElementById(\"classificacao_fiscal_lista\").value = '" . serialize($itens_atuais) . "';
      }
      if (document.getElementById(\"classificacao_fiscal_tipo\")) {
        document.getElementById(\"classificacao_fiscal_tipo\").focus();
      }
      classificacao_fiscal_linha_id = 0;";
	
	} elseif ($_GET["a"] == "remover_classificacao_fiscal") {
		
		$itens_atuais = @unserialize(stripslashes($_GET["classificacao_fiscal_lista"]));
		$item = $itens_atuais[$_GET["id"] - 1];
		$item["taxa"] = number_format($item["taxa"], 2, ",", ".");
		for ($i = 0; $i < count($itens_atuais); $i++) {
			if ($i != $_GET["id"] - 1) {
				$novos_itens[] = $itens_atuais[$i];
			}
		}
		$itens_atuais = $novos_itens;
		$listagem = $planos_servicos->buscaClassificacaoFiscalListagem($itens_atuais, true);
		print "
      if (document.getElementById(\"classificacao_fiscal_listagem\")) {
        document.getElementById(\"classificacao_fiscal_listagem\").innerHTML = '" . str_replace("'", '\\\'', $listagem) . "';
      }
      if (document.getElementById(\"classificacao_fiscal_tipo\")) {
        document.getElementById(\"classificacao_fiscal_tipo\").value = '{$item["tipo_id"]}';
      }
      if (document.getElementById(\"classificacao_fiscal_taxa\")) {
        document.getElementById(\"classificacao_fiscal_taxa\").value = '{$item["taxa"]}';
      }
      if (document.getElementById(\"classificacao_fiscal_lista\")) {
        document.getElementById(\"classificacao_fiscal_lista\").value = '" . serialize($itens_atuais) . "';
      }
      classificacao_fiscal_linha_id = 0;";
	
	} elseif ($_GET["a"] == "selecionar_classificacao_fiscal") {
		
		$itens_atuais = @unserialize(stripslashes($_GET["classificacao_fiscal_lista"]));
		$item = $itens_atuais[$_GET["id"] - 1];
		$item["taxa"] = number_format($item["taxa"], 2, ",", ".");
		
		print "
      if (document.getElementById(\"classificacao_fiscal_tipo\")) {
        document.getElementById(\"classificacao_fiscal_tipo\").value = '{$item["tipo_id"]}';
      }
      if (document.getElementById(\"classificacao_fiscal_taxa\")) {
        document.getElementById(\"classificacao_fiscal_taxa\").value = '{$item["taxa"]}';
      }";
	
	}

} else {
	
	if (false) {
		?><script type="text/javascript"><?
	}
	
?>

function validaServico(form) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!form.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

var forma_pagamento_linha_id = 0;

function formaPagamentoAdicionar() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('formas_pagamento_categoria').value) {
    erro_vazio[erro_vazio.length] = "Categoria";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=planos_servicos&a=adicionar_forma_pagamento&categoria=" + document.getElementById('formas_pagamento_categoria').value + "&entrada=" + document.getElementById('formas_pagamento_entrada').value + "&parcelas=" + document.getElementById('formas_pagamento_parcelas').value + "&valor_parcela=" + document.getElementById('formas_pagamento_valor_parcela').value + "&forma=" + document.getElementById('formas_pagamento_forma').value + "&multa=" + document.getElementById('formas_pagamento_multa').value + "&fidelidade=" + document.getElementById('formas_pagamento_fidelidade').value + "&formas_pagamento_lista=" + document.getElementById('formas_pagamento_lista').value + "&id=" + forma_pagamento_linha_id;
    RDSCarregar("planos_adicionar_forma_pagamento", url);
  }
}

function removerFormaPagamento(id) {
  if (id && confirm('Tem certeza que deseja remover esta Forma de Pagamento?')) {
	  url = "rds.js.php?m=planos_servicos&a=remover_forma_pagamento&id=" + id + "&formas_pagamento_lista=" + document.getElementById('formas_pagamento_lista').value;
    RDSCarregar("planos_remover_forma_pagamento", url);
  }
}

function selecionarLinhaFormaPagamento() {
  if (forma_pagamento_linha_id) {
	  url = "rds.js.php?m=planos_servicos&a=selecionar_forma_pagamento&id=" + forma_pagamento_linha_id + "&formas_pagamento_lista=" + document.getElementById('formas_pagamento_lista').value;
	  RDSCarregar("planos_selecionar_forma_pagamento", url);
  }
}

function selecionarFormaPagamento(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  forma_pagamento_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("formas_pagamento_categoria").value = "";
        document.getElementById("formas_pagamento_entrada").value = "";
        document.getElementById("formas_pagamento_parcelas").value = "";
        document.getElementById("formas_pagamento_valor_parcela").value = "";
        document.getElementById("formas_pagamento_forma").value = "";
        document.getElementById("formas_pagamento_multa").value = "";
        document.getElementById("formas_pagamento_fidelidade").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        forma_pagamento_linha_id = id;
        selecionarLinhaFormaPagamento();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

var classificacao_fiscal_linha_id = 0;

function classificacaoFiscalAdicionar() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('classificacao_fiscal_tipo').value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=planos_servicos&a=adicionar_classificacao_fiscal&tipo_id=" + document.getElementById('classificacao_fiscal_tipo').value + "&taxa=" + document.getElementById('classificacao_fiscal_taxa').value + "&classificacao_fiscal_lista=" + document.getElementById('classificacao_fiscal_lista').value + "&id=" + classificacao_fiscal_linha_id;
    RDSCarregar("planos_servicos_adicionar_classificacao_fiscal", url);
  }
}

function removerClassificacaoFiscal(id) {
  if (id && confirm('Tem certeza que deseja remover esta Classificacao Fiscal?')) {
	  url = "rds.js.php?m=planos_servicos&a=remover_classificacao_fiscal&id=" + id + "&classificacao_fiscal_lista=" + document.getElementById('classificacao_fiscal_lista').value;
    RDSCarregar("planos_servicos_remover_classificacao_fiscal", url);
  }
}

function selecionarLinhaClassificacaoFiscal() {
  if (classificacao_fiscal_linha_id) {
	  url = "rds.js.php?m=planos_servicos&a=selecionar_classificacao_fiscal&id=" + classificacao_fiscal_linha_id + "&classificacao_fiscal_lista=" + document.getElementById('classificacao_fiscal_lista').value;
	  RDSCarregar("planos_servicos_selecionar_classificacao_fiscal", url);
  }
}

function selecionarClassificacaoFiscal(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  classificacao_fiscal_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("classificacao_fiscal_tipo").value = "";
        document.getElementById("classificacao_fiscal_taxa").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        classificacao_fiscal_linha_id = id;
        selecionarLinhaClassificacaoFiscal();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

<?
	
	if (false) {
		?></script><?
	}
}

?>






