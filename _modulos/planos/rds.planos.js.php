<?

if ($_GET["a"]) {
  
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$planos = controle::instanciaObjeto("planos", $bancodedados, $seguranca->permissoes, false);
  
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
    $listagem = $planos->buscaFormasPagamentoListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"formas_pagamento_listagem\")) {
        document.getElementById(\"formas_pagamento_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
      }
      if (document.getElementById(\"formas_pagamento_lista\")) {
        document.getElementById(\"formas_pagamento_lista\").value = '".serialize($itens_atuais)."';
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
    $listagem = $planos->buscaFormasPagamentoListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"formas_pagamento_listagem\")) {
        document.getElementById(\"formas_pagamento_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
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
        document.getElementById(\"formas_pagamento_lista\").value = '".serialize($itens_atuais)."';
      }
      forma_pagamento_linha_id = 0;";
    
  } elseif ($_GET["a"] == "selecionar_forma_pagamento") {
    
    $itens_atuais = @unserialize(stripslashes($_GET["formas_pagamento_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    $item["entrada"] = $item["entrada"] > 0 ? $planos->formataDinheiro($item["entrada"]) : "";
    $item["parcelas"] = $item["parcelas"] > 0 ? $item["parcelas"] : "";
    $item["valor_parcela"] = $item["valor_parcela"] > 0 ? $planos->formataDinheiro($item["valor_parcela"]) : "";
    $item["multa"] = $item["multa"] > 0 ? $planos->formataDinheiro($item["multa"]) : "";
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
    
  } elseif ($_GET["a"] == "adicionar_vinculo") {
    
    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["vinculos_lista"])));
    // verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["plano"] == $_GET["plano"] && $chave != $_GET["id"] - 1) {
        print "alert('Plano já vinculado!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["servico"] = $_GET["servico"];
    $novo_item["plano"] = $_GET["plano"];
    $novo_item["aplicacao"] = $_GET["aplicacao"];
    $novo_item["tipo"] = $_GET["tipo"];
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    $listagem = $planos->buscaVinculosListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"vinculos_listagem\")) {
        document.getElementById(\"vinculos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
      }
      if (document.getElementById(\"vinculos_lista\")) {
        document.getElementById(\"vinculos_lista\").value = '".serialize($itens_atuais)."';
      }
      if (document.getElementById(\"vinculos_servico\")) {
        document.getElementById(\"vinculos_servico\").focus();
      }
      vinculo_linha_id = 0;";
    
  } elseif ($_GET["a"] == "remover_vinculo") {
    
    $itens_atuais = @unserialize(stripslashes($_GET["vinculos_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    sort($itens_atuais);
    $listagem = $planos->buscaVinculosListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"vinculos_listagem\")) {
        document.getElementById(\"vinculos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
      }
      if (document.getElementById(\"vinculos_servico\")) {
        document.getElementById(\"vinculos_servico\").value = '{$item["servico"]}';
        mudaServico({$item["servico"]}, {$_GET["id"]});
      }
      if (document.getElementById(\"vinculos_aplicacao\")) {
        document.getElementById(\"vinculos_aplicacao\").value = '{$item["aplicacao"]}';
      }
      if (document.getElementById(\"vinculos_tipo\")) {
        document.getElementById(\"vinculos_tipo\").value = '{$item["tipo"]}';
      }
      if (document.getElementById(\"vinculos_lista\")) {
        document.getElementById(\"vinculos_lista\").value = '".serialize($itens_atuais)."';
      }
      vinculo_linha_id = 0;";
    
  } elseif ($_GET["a"] == "selecionar_vinculo") {
    
    $itens_atuais = @unserialize(stripslashes($_GET["vinculos_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    print "
      if (document.getElementById(\"vinculos_servico\")) {
        document.getElementById(\"vinculos_servico\").value = '{$item["servico"]}';
        mudaServico({$item["servico"]}, {$_GET["id"]});
      }
      if (document.getElementById(\"vinculos_aplicacao\")) {
        document.getElementById(\"vinculos_aplicacao\").value = '{$item["aplicacao"]}';
      }
      if (document.getElementById(\"vinculos_tipo\")) {
        document.getElementById(\"vinculos_tipo\").value = '{$item["tipo"]}';
      }";



    } elseif ($_GET["a"] == "adicionar_planos_limitacao") {

      $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["planos_limitacao_lista"])));

      foreach ($itens_atuais as $chave => $valor) {
        if ($valor["plano"] == $_GET["planos_limitacao_plano"] && $chave != $_GET["id"] - 1) {
          print "alert('Plano já vinculado!');";
          die();
        }
      }
      
      $novo_item["plano"] = $_GET["planos_limitacao_plano"];
      $novo_item["valor"] = intval(preg_replace("/[^0-9]/", "", $_GET["planos_limitacao_valor"]));
      
      if ($_GET["id"]) {
        $itens_atuais[$_GET["id"] - 1] = $novo_item;
      } else {
        $itens_atuais[] = $novo_item;
      }
      
      $listagem = $planos->buscaPlanosLimitacaoListagem($itens_atuais, true);
      
      print "
    if (document.getElementById(\"planos_limitacao_listagem\")) {
      document.getElementById(\"planos_limitacao_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
    }
    if (document.getElementById(\"planos_limitacao_lista\")) {
      document.getElementById(\"planos_limitacao_lista\").value = '".serialize($itens_atuais)."';
    }
    if (document.getElementById(\"planos_limitacao_plano\")) {
      document.getElementById(\"planos_limitacao_plano\").focus();
    }
    planos_limitacao_linha_id = 0;";

  } elseif ($_GET["a"] == "adicionar_multiservice") {

      $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["multiservice_lista"])));

      foreach ($itens_atuais as $chave => $valor) {
        if ($valor["player"] == $_GET["player"] && $chave != $_GET["id"] - 1) {
          print "alert('Multiservice já vinculado!');";
          die();
        }
      }
      
      $novo_item["servico"] = $_GET["servico"];
      $novo_item["player"] = $_GET["player"];
      
      if ($_GET["id"]) {
        $itens_atuais[$_GET["id"] - 1] = $novo_item;
      } else {
        $itens_atuais[] = $novo_item;
      }
      
      $listagem = $planos->buscaMultiserviceListagem($itens_atuais, true);
      
      print "
    if (document.getElementById(\"multiservice_listagem\")) {
      document.getElementById(\"multiservice_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
    }
    if (document.getElementById(\"multiservice_lista\")) {
      document.getElementById(\"multiservice_lista\").value = '".serialize($itens_atuais)."';
    }
    if (document.getElementById(\"multiservice_servico\")) {
      document.getElementById(\"multiservice_servico\").focus();
    }
    multiservice_linha_id = 0;";

  } elseif ($_GET["a"] == "busca_multiservice_servicos") {

      if ($_GET["id"]) {
        
        if ($_GET["multiservice_linha_id"]) {
          $itens_atuais = @unserialize(stripslashes($_GET["multiservice_lista"]));
          $item = $itens_atuais[$_GET["multiservice_linha_id"] - 1];
        }
                          $lista_multiservice_servico = str_replace("'", '\\\'', $planos->geraSelect($planos->buscaMultiserviceServicos($_GET['id']), $item["plano"], true));
                              
                          print " 
              if (div_planos = document.getElementById('div_multiservice')) {
                      div_planos.innerHTML = '<select name=\"multiservice_servico\" id=\"multiservice_servico\" style=\"width:100%\" class=\"inputBrancoAzulEscuro11\">$lista_multiservice_servico</select>';
              }";
      
      } else {
        
        print " 
          if (div_planos = document.getElementById('div_multiservice')) {
      div_planos.innerHTML = '<select name=\"multiservice_servico\" id=\"multiservice_servico\" style=\"width:100%\" class=\"inputBrancoAzulEscuro11\">{$_GET["id"]}</select>';
    }                            

    if (select_multiservice_servico = document.getElementById('multiservice_servico')) {
      select_multiservice_servico.value = '';
      select_multiservice_servico.disabled = true;
    }";
    
      }

    } elseif ($_GET["a"] == "remover_planos_limitacao") {

      $itens_atuais = @unserialize(stripslashes($_GET["planos_limitacao_lista"]));
      $item = $itens_atuais[$_GET["id"] - 1];
      
      unset($itens_atuais[$_GET["id"] - 1]);
      sort($itens_atuais);
      
      if($_GET["id"] == 0) {
        $listagem = $planos->buscaPlanosLimitacaoListagem([], true, true);
        $itens_atuais_serialized = "";
      } else {
        $listagem = $planos->buscaPlanosLimitacaoListagem($itens_atuais, true);
        $itens_atuais_serialized = serialize($itens_atuais);
      }
      
      print "
    if (document.getElementById(\"planos_limitacao_listagem\")) {
      document.getElementById(\"planos_limitacao_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
    }
    if (document.getElementById(\"planos_limitacao_plano\")) {
      document.getElementById(\"planos_limitacao_plano\").value = '{$item["plano"]}';
    }
    if (document.getElementById(\"planos_limitacao_valor\")) {
      document.getElementById(\"planos_limitacao_valor\").value = '{$item["valor"]}';
    }
    if (document.getElementById(\"planos_limitacao_lista\")) {
      document.getElementById(\"planos_limitacao_lista\").value = '". $itens_atuais_serialized ."';
    }
    planos_limitacao_linha_id = 0;";

	} elseif ($_GET["a"] == "remover_multiservice") {

      $itens_atuais = @unserialize(stripslashes($_GET["multiservice_lista"]));
      $item = $itens_atuais[$_GET["id"] - 1];
      
      unset($itens_atuais[$_GET["id"] - 1]);
      sort($itens_atuais);
      
      $listagem = $planos->buscaMultiserviceListagem($itens_atuais, true);
      
      print "
    if (document.getElementById(\"multiservice_listagem\")) {
      document.getElementById(\"multiservice_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."';
    }
    if (document.getElementById(\"multiservice_player\")) {
      document.getElementById(\"multiservice_player\").value = '{$item["player"]}';
      mudaMultiserviceServico({$item["player"]}, {$_GET["id"]});
    }
    if (document.getElementById(\"multiservice_servico\")) {
      document.getElementById(\"multiservice_servico\").value = '{$item["servico"]}';
    }
    if (document.getElementById(\"multiservice_lista\")) {
      document.getElementById(\"multiservice_lista\").value = '".serialize($itens_atuais)."';
    }
    multiservice_linha_id = 0;";

	} elseif ($_GET["a"] == "adicionar_voip_tabelas_plano") {
    
    $itens_atuais = @unserialize(stripslashes(stripslashes($_GET["voip_tabelas_planos_lista"])));
    // verifica duplicado na listagem
    foreach ($itens_atuais as $chave => $valor) {
      if ($valor["voip_tabela"] == $_GET["voip_tabela"] && $chave != $_GET["id"] - 1) {
        print "alert('Tabela já vinculada!');";
        die();
      }
    }
    // Trata novos itens
    $novo_item["voip_tabela"] = $_GET["voip_tabela"];
    $novo_item["multiplicador"] = $_GET["multiplicador"];
    if ($_GET["id"]) {
      $itens_atuais[$_GET["id"] - 1] = $novo_item;
    } else {
      $itens_atuais[] = $novo_item;
    }
    for ($i=0;$i<sizeof($itens_atuais);$i++) {
      $itens_atuais[sizeof($itens_atuais)-1]["prioridade"] = sizeof($itens_atuais);
    }
    $listagem = $planos->buscaVoipTabelasPlanosListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"voip_tabelas_planos_listagem\")) { document.getElementById(\"voip_tabelas_planos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."'; }
      if (document.getElementById(\"voip_tabelas_planos_lista\")) { document.getElementById(\"voip_tabelas_planos_lista\").value = '".serialize($itens_atuais)."'; }
      if (document.getElementById(\"voip_tabelas_plano_voip_tabela\")) { document.getElementById(\"voip_tabelas_plano_voip_tabela\").focus(); }
      voip_tabelas_plano_linha_id = 0;
    ";
    
  } elseif ($_GET["a"] == "remover_voip_tabelas_plano") {
    
    $itens_atuais = @unserialize(stripslashes($_GET["voip_tabelas_planos_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    unset($itens_atuais[$_GET["id"] - 1]);
    if (sizeof($itens_atuais)>0) {
      foreach ($itens_atuais AS $item_atual) {
        $novos_itens_atuais[] = $item_atual;
        $novos_itens_atuais[sizeof($novos_itens_atuais)-1]["prioridade"] = sizeof($novos_itens_atuais);
      }
    }
    $itens_atuais = $novos_itens_atuais;
    $listagem = $planos->buscaVoipTabelasPlanosListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"voip_tabelas_planos_listagem\")) { document.getElementById(\"voip_tabelas_planos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."'; }
      if (document.getElementById(\"voip_tabelas_planos_lista\")) { document.getElementById(\"voip_tabelas_planos_lista\").value = '".serialize($itens_atuais)."'; }
      if (document.getElementById(\"voip_tabelas_plano_voip_tabela\")) { document.getElementById(\"voip_tabelas_plano_voip_tabela\").value = '{$item["voip_tabela"]}'; }
      if (document.getElementById(\"voip_tabelas_plano_multiplicador\")) { document.getElementById(\"voip_tabelas_plano_multiplicador\").value = '{$item["multiplicador"]}'; }
      voip_tabelas_plano_linha_id = 0;
    ";
    
  } elseif ($_GET["a"] == "subir_voip_tabelas_plano") {
    
    $_GET["id"]--;
    $itens_atuais = @unserialize(stripslashes($_GET["voip_tabelas_planos_lista"]));
    if (sizeof($itens_atuais)>0) {
      for ($i=0;$i<sizeof($itens_atuais);$i++) {
        $novos_itens_atuais[] = ($i == $_GET["id"]-1) ? $itens_atuais[$i+1] : (($i == $_GET["id"]) ? $itens_atuais[$i-1] : $itens_atuais[$i]);
        $novos_itens_atuais[sizeof($novos_itens_atuais)-1]["prioridade"] = sizeof($novos_itens_atuais);
      }
    }
    $itens_atuais = $novos_itens_atuais;
    $listagem = $planos->buscaVoipTabelasPlanosListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"voip_tabelas_planos_listagem\")) { document.getElementById(\"voip_tabelas_planos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."'; }
      if (document.getElementById(\"voip_tabelas_planos_lista\")) { document.getElementById(\"voip_tabelas_planos_lista\").value = '".serialize($itens_atuais)."'; }
      voip_tabelas_plano_linha_id = 0;
    ";
    
  } elseif ($_GET["a"] == "descer_voip_tabelas_plano") {
    
    $_GET["id"]--;
    $itens_atuais = @unserialize(stripslashes($_GET["voip_tabelas_planos_lista"]));
    if (sizeof($itens_atuais)>0) {
      for ($i=0;$i<sizeof($itens_atuais);$i++) {
        $novos_itens_atuais[] = ($i == $_GET["id"]) ? $itens_atuais[$i+1] : (($i == $_GET["id"]+1) ? $itens_atuais[$i-1] : $itens_atuais[$i]);
        $novos_itens_atuais[sizeof($novos_itens_atuais)-1]["prioridade"] = sizeof($novos_itens_atuais);
      }
    }
    $itens_atuais = $novos_itens_atuais;
    $listagem = $planos->buscaVoipTabelasPlanosListagem($itens_atuais, true);
    print "
      if (document.getElementById(\"voip_tabelas_planos_listagem\")) { document.getElementById(\"voip_tabelas_planos_listagem\").innerHTML = '".str_replace("'", '\\\'', $listagem)."'; }
      if (document.getElementById(\"voip_tabelas_planos_lista\")) { document.getElementById(\"voip_tabelas_planos_lista\").value = '".serialize($itens_atuais)."'; }
      voip_tabelas_plano_linha_id = 0;
    ";
    
  } elseif ($_GET["a"] == "selecionar_voip_tabelas_plano") {
    
    $itens_atuais = @unserialize(stripslashes($_GET["voip_tabelas_planos_lista"]));
    $item = $itens_atuais[$_GET["id"] - 1];
    print "
      if (document.getElementById(\"voip_tabelas_plano_voip_tabela\")) { document.getElementById(\"voip_tabelas_plano_voip_tabela\").value = '{$item["tabela"]}'; }
      if (document.getElementById(\"voip_tabelas_plano_multiplicador\")) { document.getElementById(\"voip_tabelas_plano_multiplicador\").value = '{$item["multiplicador"]}'; }
    ";
    
  } elseif ($_GET["a"] == "busca_planos") {
    
    if ($_GET["id"]) {
      if ($_GET["vinculo_linha_id"]) {
        $itens_atuais = @unserialize(stripslashes($_GET["vinculos_lista"]));
        $item = $itens_atuais[$_GET["vinculo_linha_id"] - 1];
      }
      $lista_planos = str_replace("'", '\\\'', $planos->geraSelect($planos->buscaPlanosServico($_GET["id"]), $item["plano"], true));
      print "
        if (div_planos = document.getElementById('div_plano')) {
          div_planos.innerHTML = '<select name=\"vinculos_plano\" id=\"vinculos_plano\" style=\"width:233px\" class=\"inputBrancoAzulEscuro11\">$lista_planos</select>';
        }";
    } else {
      print "
       if (select_planos = document.getElementById('vinculos_plano')) {
         select_planos.value = '';
         select_planos.disabled = true;
       }";
    }
	
  } elseif ($_GET["a"] == "atualiza_aplicavel") {

      // Gera o widget completo de checkboxes (mesmo padrão de Origens, IPTV/VOD Pacotes, Apps).
      $widget = $planos->geraCheckBoxesLista("aplicavel[]", $planos->buscaPlanosAplicaveis($_GET["servico_id"]), array(), "350px", "170px");
      // Remove <script>...</script>: innerHTML não os executa, e os helpers (marcarTodos/atualizaMaster)
      // já estão definidos pela renderização inicial dos outros checkboxes da página.
      $widget = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $widget);
      // Sanitiza para embutir no print JS: escapa aspas simples e remove quebras de linha.
      $widget_js = str_replace(array("\r\n", "\r", "\n", "'"), array(' ', ' ', ' ', "\\'"), $widget);
      print "if (document.getElementById('aplicavel_container')) { document.getElementById('aplicavel_container').innerHTML = '$widget_js'; }";

  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var vinculo_linha_id = 0;
var multiservice_linha_id = 0;
var forma_pagamento_linha_id = 0;
var planos_limitacao_linha_id = 0;

function planosLimitacaoAdicionar() {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('planos_limitacao_plano').value) {
    erro_vazio[erro_vazio.length] = "Plano";
  }
  if (!document.getElementById('planos_limitacao_valor').value) {
    erro_vazio[erro_vazio.length] = "Valor";
  } else if (document.getElementById('planos_limitacao_valor').value <= 0) {
    erro_invalido[erro_invalido.length] = "Valor deve ser maior que 0";
  }
  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=planos&a=adicionar_planos_limitacao&planos_limitacao_plano=" + document.getElementById('planos_limitacao_plano').value 
                                          + "&planos_limitacao_valor=" + document.getElementById('planos_limitacao_valor').value 
                                          + "&planos_limitacao_lista=" + document.getElementById('planos_limitacao_lista').value 
                                          + "&id=" + planos_limitacao_linha_id;
    RDSCarregar("planos_adicionar_planos_limitacao", url);
  }
}

function removerPlanosLimitacao(id) {

  if(id == 0) {
    url = "rds.js.php?m=planos&a=remover_planos_limitacao&id=" + id + "&planos_limitacao_lista=" + document.getElementById('planos_limitacao_lista').value;
    RDSCarregar("planos_remover_planos_limitacao", url);
  } else {
    if (id && confirm('Tem certeza que deseja remover este Plano de Limite?')) {
      url = "rds.js.php?m=planos&a=remover_planos_limitacao&id=" + id + "&planos_limitacao_lista=" + document.getElementById('planos_limitacao_lista').value;
      RDSCarregar("planos_remover_planos_limitacao", url);
    }
  }
}

function multiserviceAdicionar() {

	var erro_vazio = new Array();
	var erro_invalido = new Array();

	if (!document.getElementById('multiservice_player').value) {
		erro_vazio[erro_vazio.length] = "Player";
	}
	if (!document.getElementById('multiservice_servico').value) {
		erro_vazio[erro_vazio.length] = "Serviço";
	}
	if (apresentaErros(erro_vazio, erro_invalido)) {
		url = "rds.js.php?m=planos&a=adicionar_multiservice&servico=" + document.getElementById('multiservice_servico').value 
                        + "&player=" + document.getElementById('multiservice_player').value 
                        + "&multiservice_lista=" + document.getElementById('multiservice_lista').value  + "&id=" + multiservice_linha_id;
		RDSCarregar("planos_adicionar_multiservice", url);
	}

}

function removerMultiservice(id) {

	if (id && confirm('Tem certeza que deseja remover este Multiservice?')) {
		url = "rds.js.php?m=planos&a=remover_multiservice&id=" + id + "&multiservice_lista=" + document.getElementById('multiservice_lista').value;
		RDSCarregar("planos_remover_multiservice", url);
	}

}

function mudaMultiserviceServico(player, id) {

	if (player) {
            if(id) {
		url = "rds.js.php?m=planos&a=busca_multiservice_servicos&id=" + player + "&multiservice_lista=" + document.getElementById('multiservice_lista').value + "&multiservice_linha_id=" + id;
            } else {
		url = "rds.js.php?m=planos&a=busca_multiservice_servicos&id=" + player;
            }
		RDSCarregar("planos_busca_multiservice_servicos", url);
	} else if (select_multiservice_servico = document.getElementById('multiservice_servico')) {
		select_multiservice_servico.value = '';
		select_multiservice_servico.disabled = true;
	}
	
	atualizaAplicavel();

}

function selecionarbuscaMultiservice(linha, evento, id) {
	
	if (ehNetscape()) {
		if (evento.target.tagName != "TD") return false;
		var tabela = linha.parentNode;
	} else {
		if (evento.srcElement.tagName != "TD") return false;
		var tabela = linha.parentElement;
	}
	
	multiservice_linha_id = 0;
	
	for (i = 0; i < tabela.rows.length; i++) {
		if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
			if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
				linha.style.backgroundColor = "";
				mudaServico(document.getElementById("multiservice_player").value);
				document.getElementById("multiservice_servico").value = "";
			} else {
				linha.style.backgroundColor = "#ffcc66";
				multiservice_linha_id = id;
				selecionarLinhaVinculo();
			}
		} else {
			tabela.rows[i].style.backgroundColor = "";
		}
	}
	
}

function selecionarLinhaMultiservice() {
	
	if (multiservice_linha_id) {
		url = "rds.js.php?m=planos&a=selecionar_multiservice&id=" + multiservice_linha_id + "&multiservice_lista=" + document.getElementById('multiservice_lista').value;
		RDSCarregar("planos_selecionar_vinculo", url);
	}
	
}

function vinculoAdicionar() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('vinculos_servico').value) {
    erro_vazio[erro_vazio.length] = "Serviço";
  }
  if (!document.getElementById('vinculos_plano').value) {
    erro_vazio[erro_vazio.length] = "Plano";
  }
  if (!document.getElementById('vinculos_aplicacao').value) {
    erro_vazio[erro_vazio.length] = "Aplicação";
  }
  if (!document.getElementById('vinculos_tipo').value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=planos&a=adicionar_vinculo&servico=" + document.getElementById('vinculos_servico').value + "&plano=" + document.getElementById('vinculos_plano').value + "&aplicacao=" + document.getElementById('vinculos_aplicacao').value + "&tipo=" + document.getElementById('vinculos_tipo').value + "&vinculos_lista=" + document.getElementById('vinculos_lista').value  + "&id=" + vinculo_linha_id;
    RDSCarregar("planos_adicionar_vinculo", url);
  }
}

function removerVinculo(id) {
  if (id && confirm('Tem certeza que deseja remover este Vínculo?')) {
	  url = "rds.js.php?m=planos&a=remover_vinculo&id=" + id + "&vinculos_lista=" + document.getElementById('vinculos_lista').value;
    RDSCarregar("planos_remover_vinculo", url);
  }
}

function selecionarLinhaVinculo() {
  if (vinculo_linha_id) {
	  url = "rds.js.php?m=planos&a=selecionar_vinculo&id=" + vinculo_linha_id + "&vinculos_lista=" + document.getElementById('vinculos_lista').value;
	  RDSCarregar("planos_selecionar_vinculo", url);
  }
}

function selecionarVinculo(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  vinculo_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("vinculos_servico").value = "";
        mudaServico(document.getElementById("vinculos_servico").value);
        document.getElementById("vinculos_aplicacao").value = "";
        document.getElementById("vinculos_tipo").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        vinculo_linha_id = id;
        selecionarLinhaVinculo();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function mudaServico(servico, id) {
  if (servico) {
	  url = "rds.js.php?m=planos&a=busca_planos&id=" + servico + "&vinculos_lista=" + document.getElementById('vinculos_lista').value + "&vinculo_linha_id=" + id;
    RDSCarregar("planos_busca_planos", url);
  } else {
    if (select_planos = document.getElementById('vinculos_plano')) {
      select_planos.value = '';
      select_planos.disabled = true;
    }
  }
  atualizaAplicavel();
}

function formaPagamentoAdicionar() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('formas_pagamento_categoria').value) {
    erro_vazio[erro_vazio.length] = "Categoria";
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=planos&a=adicionar_forma_pagamento&categoria=" + document.getElementById('formas_pagamento_categoria').value + "&entrada=" + document.getElementById('formas_pagamento_entrada').value + "&parcelas=" + document.getElementById('formas_pagamento_parcelas').value + "&valor_parcela=" + document.getElementById('formas_pagamento_valor_parcela').value + "&forma=" + document.getElementById('formas_pagamento_forma').value + "&multa=" + document.getElementById('formas_pagamento_multa').value + "&fidelidade=" + document.getElementById('formas_pagamento_fidelidade').value + "&formas_pagamento_lista=" + document.getElementById('formas_pagamento_lista').value + "&id=" + forma_pagamento_linha_id;
    RDSCarregar("planos_adicionar_forma_pagamento", url);
  }
}

function removerFormaPagamento(id) {
  if (id && confirm('Tem certeza que deseja remover esta Forma de Pagamento?')) {
	  url = "rds.js.php?m=planos&a=remover_forma_pagamento&id=" + id + "&formas_pagamento_lista=" + document.getElementById('formas_pagamento_lista').value;
    RDSCarregar("planos_remover_forma_pagamento", url);
  }
}

function selecionarLinhaFormaPagamento() {
  if (forma_pagamento_linha_id) {
	  url = "rds.js.php?m=planos&a=selecionar_forma_pagamento&id=" + forma_pagamento_linha_id + "&formas_pagamento_lista=" + document.getElementById('formas_pagamento_lista').value;
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
  vinculo_linha_id = 0;
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

function mudaTelefoniaTipo(campo) {
	if (campo.value == "PRE") {
		document.getElementById("telefonia_credito").disabled = false;
	} else {
		document.getElementById("telefonia_credito").value = "";
		document.getElementById("telefonia_credito").disabled = true;
	}
}

function validaPlano (formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }

  if (!formulario.servico.value) {
    erro_vazio[erro_vazio.length] = "Serviço";
  }

  if (!formulario.tipo.value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }

  if (!formulario.sistema.value) {
    erro_vazio[erro_vazio.length] = "Sistema";
  }

  if(formulario.tipo_limitacao.value == 2) {
    if (!String(formulario.valor_max.value).trim()) {
      erro_vazio[erro_vazio.length] = "Valor Máximo";
    } else {
      var valorMax = parseFloat(String(formulario.valor_max.value).trim().replace("R$", "").replace(",", ".")) * 100;
      if (isNaN(valorMax)) {
        erro_invalido[erro_invalido.length] = "Valor Máximo (informe apenas número)";
      } else if (valorMax <= 0) {
        erro_invalido[erro_invalido.length] = "Valor Máximo deve ser maior que 0";
      }
    }
  }

  if (!formulario.gerar_nfse.value) {
    erro_vazio[erro_vazio.length] = "Gerar NFS-e";
  } else {
      if(parseInt(formulario.gerar_nfse.value) === 1) {
        if (!formulario.codigo_servico.value) {
          erro_vazio[erro_vazio.length] = "Código de serviço";
        }
        if (!formulario.cnae.value) {
          erro_vazio[erro_vazio.length] = "CNAE";
        }
        if (!formulario.codigo_nbs.value) {
          erro_vazio[erro_vazio.length] = "Código de NBS";
        }
        if (!formulario.aliquota.value) {
          erro_vazio[erro_vazio.length] = "Alíquota";
        }
        if (!formulario.issRetido.value) {
          erro_vazio[erro_vazio.length] = "ISS Retido";
        }
      }
  }

  if (!formulario.periodicidade.value) {
    erro_vazio[erro_vazio.length] = "Periodicidade";
  }

  if (!formulario.instalacao.value) {
    erro_vazio[erro_vazio.length] = "Valores » Instalação";
  }

  if (!formulario.mensalidade.value) {
    erro_vazio[erro_vazio.length] = "Valores » Mensalidade";
  }

  if (!formulario.parcelamento_maximo.value) {
    erro_vazio[erro_vazio.length] = "Valores » Parcelamento Máximo";
  } else if (!/^[0-9]+$/g.test(formulario.parcelamento_maximo.value) || formulario.parcelamento_maximo.value < 1 || formulario.parcelamento_maximo.value > 12) {
    erro_invalido[erro_invalido.length] = "Valores » Parcelamento Máximo (1 a 12)";
  }


  if (formulario.pontos_quantidade.value && !formulario.pontos_velocidade_download.value) {
    erro_vazio[erro_vazio.length] = "Pontos » Velocidade Download";
  }

  if (formulario.pontos_quantidade.value && !formulario.pontos_velocidade_upload.value) {
    erro_vazio[erro_vazio.length] = "Pontos » Velocidade Upload";
  }

  if (formulario.pontos_velocidade_download.value && !formulario.pontos_velocidade_download_unidade.value) {
    erro_vazio[erro_vazio.length] = "Pontos » Velocidade Download » Unidade";
  }

  if (formulario.pontos_velocidade_upload.value && !formulario.pontos_velocidade_upload_unidade.value) {
    erro_vazio[erro_vazio.length] = "Pontos » Velocidade Upload » Unidade";
  }

  if (formulario.emails_quantidade.value && !formulario.emails_capacidade.value) {
    erro_vazio[erro_vazio.length] = "E-Mails » Capacidade";
  }

  if (formulario.emails_capacidade.value && !formulario.emails_capacidade_unidade.value) {
    erro_vazio[erro_vazio.length] = "E-Mails » Capacidade » Unidade";
  }

  if (formulario.hospedagens_quantidade.value && !formulario.hospedagens_capacidade.value) {
    erro_vazio[erro_vazio.length] = "Hospedagens » Capacidade";
  }

  if (formulario.hospedagens_quantidade.value && !formulario.hospedagens_transferencia.value) {
    erro_vazio[erro_vazio.length] = "Hospedagens » Transferência";
  }

  if (formulario.hospedagens_capacidade.value && !formulario.hospedagens_capacidade_unidade.value) {
    erro_vazio[erro_vazio.length] = "Hospedagens » Capacidade » Unidade";
  }

  if (formulario.hospedagens_transferencia.value && !formulario.hospedagens_transferencia_unidade.value) {
    erro_vazio[erro_vazio.length] = "Hospedagens » Transferência » Unidade";
  }

  if (formulario.basesdedados_quantidade.value && !formulario.basesdedados_capacidade.value) {
    erro_vazio[erro_vazio.length] = "Base de Dados » Capacidade";
  }

  if (formulario.basesdedados_capacidade.value && !formulario.basesdedados_capacidade_unidade.value) {
    erro_vazio[erro_vazio.length] = "Base de Dados » Capacidade » Unidade";
  }

  if (formulario.telefonia_linhas?.value > 0) {
    if (!formulario.telefonia_tipo[0].checked && !formulario.telefonia_tipo[1].checked && !formulario.telefonia_tipo[2].checked) {
        erro_vazio[erro_vazio.length] = "Telefonia » Tipo";
    } else {
            if (formulario.telefonia_tipo[0].checked) {
                    if (!formulario.telefonia_credito.value) {
                        erro_vazio[erro_vazio.length] = "Telefonia » Crédito inicial";
                  }
            }
    }
  }

  if (!formulario.instalacao_referencia.value) {
    if (formulario.instalacao_responsabilidade_interno.value || formulario.instalacao_responsabilidade_externo.value || formulario.instalacao_responsabilidade_ativo.value || formulario.instalacao_indicacao.value || formulario.instalacao_bonificacao.value || formulario.instalacao_bonificacao_filtro.value) {
      erro_vazio[erro_vazio.length] = "Comissões » Instalação » Referência";
    }
  } else {
    if (formulario.instalacao_responsabilidade_interno.value || formulario.instalacao_responsabilidade_interno_tipo.value) {
      if (!formulario.instalacao_responsabilidade_interno.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Comissão » Vend. Interno";
      } else if (!validaDesconto(formulario.instalacao_responsabilidade_interno.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Instalação » Comissão » Vend. Interno";
      } else if (formulario.instalacao_responsabilidade_interno.value && !formulario.instalacao_responsabilidade_interno_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Comissão » Vend. Interno";
      }
    }
    if (formulario.instalacao_responsabilidade_externo.value || formulario.instalacao_responsabilidade_externo_tipo.value) {
      if (!formulario.instalacao_responsabilidade_externo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Comissão » Vend. Externo";
      } else if (!validaDesconto(formulario.instalacao_responsabilidade_externo.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Instalação » Comissão » Vend. Externo";
      } else if (formulario.instalacao_responsabilidade_externo.value && !formulario.instalacao_responsabilidade_externo_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Comissão » Vend. Externo";
      }
    }
    if (formulario.instalacao_responsabilidade_ativo.value || formulario.instalacao_responsabilidade_ativo_tipo.value) {
      if (!formulario.instalacao_responsabilidade_ativo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Comissão » Vend. Ativo";
      } else if (!validaDesconto(formulario.instalacao_responsabilidade_ativo.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Instalação » Comissão » Vend. Ativo";
      } else if (formulario.instalacao_responsabilidade_ativo.value && !formulario.instalacao_responsabilidade_ativo_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Comissão » Vend. Ativo";
      }
    }
    if (formulario.instalacao_indicacao.value || formulario.instalacao_indicacao_tipo.value) {
      if (!formulario.instalacao_indicacao.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Sub-Comissão";
      } else if (!validaDesconto(formulario.instalacao_indicacao.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Instalação » Sub-Comissão";
      } else if (formulario.instalacao_indicacao.value && !formulario.instalacao_indicacao_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Sub-Comissão";
      }
    }
    if (formulario.instalacao_bonificacao.value || formulario.instalacao_bonificacao.value) {
      if (!formulario.instalacao_bonificacao.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Bonificação";
      } else if (!validaDesconto(formulario.instalacao_bonificacao.value) || !validaDinheiro(formulario.instalacao_bonificacao_filtro.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Instalação » Bonificação";
      } else if (formulario.instalacao_bonificacao.value && (!formulario.instalacao_bonificacao_tipo.value || !formulario.instalacao_bonificacao_condicao.value || !formulario.instalacao_bonificacao_filtro.value)) {
        erro_vazio[erro_vazio.length] = "Comissões » Instalação » Bonificação";
      }
    }
  }

  if (!formulario.mensalidade_referencia.value) {
    if (formulario.mensalidade_responsabilidade_interno.value || formulario.mensalidade_responsabilidade_externo.value || formulario.mensalidade_responsabilidade_ativo.value || formulario.mensalidade_indicacao.value || formulario.mensalidade_bonificacao.value || formulario.mensalidade_bonificacao_filtro.value) {
      erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Referência";
    }
  } else {
    if (formulario.mensalidade_responsabilidade_interno.value || formulario.mensalidade_responsabilidade_interno_tipo.value) {
      if (!formulario.mensalidade_responsabilidade_interno.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Comissão » Vend. Interno";
      } else if (!validaDesconto(formulario.mensalidade_responsabilidade_interno.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Mensalidade » Comissão » Vend. Interno";
      } else if (formulario.mensalidade_responsabilidade_interno.value && !formulario.mensalidade_responsabilidade_interno_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Comissão » Vend. Interno";
      }
    }
    if (formulario.mensalidade_responsabilidade_externo.value || formulario.mensalidade_responsabilidade_externo_tipo.value) {
      if (!formulario.mensalidade_responsabilidade_externo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Comissão » Vend. Externo";
      } else if (!validaDesconto(formulario.mensalidade_responsabilidade_externo.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Mensalidade » Comissão » Vend. Externo";
      } else if (formulario.mensalidade_responsabilidade_externo.value && !formulario.mensalidade_responsabilidade_externo_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Comissão » Vend. Externo";
      }
    }
    if (formulario.mensalidade_responsabilidade_ativo.value || formulario.mensalidade_responsabilidade_ativo_tipo.value) {
      if (!formulario.mensalidade_responsabilidade_ativo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Comissão » Vend. Ativo";
      } else if (!validaDesconto(formulario.mensalidade_responsabilidade_ativo.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Mensalidade » Comissão » Vend. Ativo";
      } else if (formulario.mensalidade_responsabilidade_ativo.value && !formulario.mensalidade_responsabilidade_ativo_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Comissão » Vend. Ativo";
      }
    }
    if (formulario.mensalidade_indicacao.value || formulario.mensalidade_indicacao_tipo.value) {
      if (!formulario.mensalidade_indicacao.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Sub-Comissão";
      } else if (!validaDesconto(formulario.mensalidade_indicacao.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Mensalidade » Sub-Comissão";
      } else if (formulario.mensalidade_indicacao.value && !formulario.mensalidade_indicacao_tipo.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Sub-Comissão";
      }
    }
    if (formulario.mensalidade_bonificacao.value || formulario.mensalidade_bonificacao.value) {
      if (!formulario.mensalidade_bonificacao.value) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Bonificação";
      } else if (!validaDesconto(formulario.mensalidade_bonificacao.value) || !validaDinheiro(formulario.mensalidade_bonificacao_filtro.value)) {
        erro_invalido[erro_invalido.length] = "Comissões » Mensalidade » Bonificação";
      } else if (formulario.mensalidade_bonificacao.value && (!formulario.mensalidade_bonificacao_tipo.value || !formulario.mensalidade_bonificacao_condicao.value || !formulario.mensalidade_bonificacao_filtro.value)) {
        erro_vazio[erro_vazio.length] = "Comissões » Mensalidade » Bonificação";
      }
    }
  }

  if (!formulario.premiacao_pontos.value) {
    erro_vazio[erro_vazio.length] = "Premiação » Pontos";
  }

  if (!formulario.status.value) {
    erro_vazio[erro_vazio.length] = "Status";
  }

  return apresentaErros(erro_vazio,erro_invalido);

}

var voip_tabelas_plano_linha_id = 0;

function adicionarVoipTabelasPlano() {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!document.getElementById('voip_tabelas_plano_voip_tabela').value) erro_vazio[erro_vazio.length] = "Tabela";
  if (!document.getElementById('voip_tabelas_plano_multiplicador').value) erro_vazio[erro_vazio.length] = "Multiplicador";

  if (apresentaErros(erro_vazio, erro_invalido)) {
    url = "rds.js.php?m=planos&a=adicionar_voip_tabelas_plano&voip_tabela=" + document.getElementById('voip_tabelas_plano_voip_tabela').value + "&multiplicador=" + document.getElementById('voip_tabelas_plano_multiplicador').value + "&voip_tabelas_planos_lista=" + document.getElementById('voip_tabelas_planos_lista').value  + "&id=" + voip_tabelas_plano_linha_id;
    RDSCarregar("planos_adicionar_voip_tabelas_plano", url);
  }
}

function removerVoipTabelasPlano(id) {
  if (id && confirm('Tem certeza que deseja remover esta Tarifa?')) {
	  url = "rds.js.php?m=planos&a=remover_voip_tabelas_plano&id=" + id + "&voip_tabelas_planos_lista=" + document.getElementById('voip_tabelas_planos_lista').value;
    RDSCarregar("planos_remover_voip_tabelas_plano", url);
  }
}

function selecionarLinhaVoipTabelasPlano() {
  if (voip_tabelas_plano_linha_id) {
	  url = "rds.js.php?m=planos&a=selecionar_voip_tabelas_plano&id=" + voip_tabelas_plano_linha_id + "&voip_tabelas_planos_lista=" + document.getElementById('voip_tabelas_planos_lista').value;
	  RDSCarregar("planos_selecionar_voip_tabelas_plano", url);
  }
}

function selecionarVoipTabelasPlano(linha, evento, id) {
  if (ehNetscape()) {
    if (evento.target.tagName != "TD") return false;
    var tabela = linha.parentNode;
  } else {
    if (evento.srcElement.tagName != "TD") return false;
    var tabela = linha.parentElement;
  }
  voip_tabelas_plano_linha_id = 0;
  for (i = 0; i < tabela.rows.length; i++) {
    if (ehNetscape() ? (tabela.rows[i] == linha) : (tabela.rows(i) == linha)) {
      if (linha.style.backgroundColor == "#ffcc66" || linha.style.backgroundColor == "rgb(255, 204, 102)") {
        linha.style.backgroundColor = "";
        document.getElementById("voip_tabelas_plano_voip_tabela").value = "";
        document.getElementById("voip_tabelas_plano_multiplicador").value = "";
      } else {
        linha.style.backgroundColor = "#ffcc66";
        voip_tabelas_plano_linha_id = id;
        selecionarLinhaVoipTabelasPlano();
      }
    } else {
      tabela.rows[i].style.backgroundColor = "";
    }
  }
}

function subirVoipTabelasPlano(id) {
  if (id) {
	  url = "rds.js.php?m=planos&a=subir_voip_tabelas_plano&id=" + id + "&voip_tabelas_planos_lista=" + document.getElementById('voip_tabelas_planos_lista').value;
    RDSCarregar("planos_subir_voip_tabelas_plano", url);
  }
}

function descerVoipTabelasPlano(id) {
  if (id) {
	  url = "rds.js.php?m=planos&a=descer_voip_tabelas_plano&id=" + id + "&voip_tabelas_planos_lista=" + document.getElementById('voip_tabelas_planos_lista').value;
    RDSCarregar("planos_descer_voip_tabelas_plano", url);
  }
}

function atualizaAplicavel(id) {

	if (document.getElementById('servico').value && document.getElementById('tipo').value == 2) {
		document.getElementById('aplicavel_linha').style.display = "table-row";
		url = "rds.js.php?m=planos&a=atualiza_aplicavel&servico_id=" + document.getElementById('servico').value;
		RDSCarregar("planos_atualiza_aplicavel", url);
	} else {
		document.getElementById('aplicavel_linha').style.display = "none";
		var aplicavelContainer = document.getElementById('aplicavel_container');
		if (aplicavelContainer) aplicavelContainer.innerHTML = "";
	}

}

function mudaTipoLimitacao(value) {
  var parceiroPlano = document.getElementById('parceiro_plano');
  var parceiroMax = document.getElementById('parceiro_max');
  var valorMax = document.getElementById('valor_max');
  var planosLimitacaoLista = document.getElementById('planos_limitacao_lista');
  var planosLimitacaoValor = document.getElementById('planos_limitacao_valor');
  var planosLimitacaoPlano = document.getElementById('planos_limitacao_plano');
  var planosLimitacaoAdicionar = document.getElementById('planos_limitacao_adicionar');

  if (!parceiroPlano || !parceiroMax) {
    return;
  }

  if(parseInt(value) === 1) {
    parceiroPlano.disabled = false;
    parceiroMax.disabled = false;
    
    removerPlanosLimitacao(0);

    valorMax.disabled = true;
    planosLimitacaoValor.disabled = true;
    planosLimitacaoAdicionar.disabled = true;
    planosLimitacaoPlano.disabled = true;

    planosLimitacaoLista.value = '';
    planosLimitacaoPlano.value = '';
    planosLimitacaoValor.value = '';
    valorMax.value = '';

  } else {
    parceiroPlano.disabled = true;
    parceiroPlano.selectedIndex = 0;
    parceiroMax.disabled = true;
    parceiroMax.value = '';

    valorMax.disabled = false;
    planosLimitacaoLista.disabled = false;
    planosLimitacaoPlano.disabled = false;
    planosLimitacaoValor.disabled = false;
    planosLimitacaoAdicionar.disabled = false;
  }
}

function mudaGerarNota(value) {

    if(parseInt(value) !== 1) {
        document.getElementById("codigo_servico").disabled = true;
        document.getElementById("cnae").disabled = true;
        document.getElementById("codigo_nbs").disabled = true;
        document.getElementById("aliquota").disabled = true;
        document.getElementById("issRetido").disabled = true;
        
        document.getElementById("codigo_servico").value = null;
        document.getElementById("codigo_nbs").value = null;
        document.getElementById("cnae").value = null;
        document.getElementById("aliquota").value = null;
        document.getElementById("issRetido").value = null;
    } else {
        document.getElementById("codigo_servico").disabled = false;
        document.getElementById("cnae").disabled = false;
        document.getElementById("codigo_nbs").disabled = false;
        document.getElementById("aliquota").disabled = false;
        document.getElementById("issRetido").disabled = false;
        
        document.getElementById("codigo_servico").value = 206;
        document.getElementById("cnae").value = 312;
        document.getElementById("codigo_nbs").value = 117039900;
        document.getElementById("aliquota").value = '2,00';
        document.getElementById("issRetido").value = '2';
    }
}

<?

	if (false) { ?></script><? }

}

?>
