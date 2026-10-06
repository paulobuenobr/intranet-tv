var ocultar_painel = true;

<?

ignore_user_abort(1);

if (false) { ?><script type="text/javascript"><? }

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$chamados_sistema = controle::instanciaObjeto("chamados_sistema", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "navegacao_ir") {

    if (!$_GET["referencia"] && ($_GET["data_hora_alteracao"] != DATA_HORA_ALTERACAO)) {
      print "history.go(0);";
      die();
    }
    if ($chamados_sistema->ehAlertaAgendamentoVisivel()) {
    	print "exibeAlertaAgendamento();";
    } else {
    	print "ocultaAlertaAgendamento();";
    }
    print "document.getElementById('data_hora').innerHTML = '".date('d/m H:i')."';";
    if ($_GET["atualizacao"]) {
      print "if (!atualizando) {";
    }
    // Busca Dados Tela Atual
    $tela_atual = $chamados_sistema->buscaTelaAtual($_GET["instancia"], $_GET["referencia"]);
    if ($_GET["referencia"] == 'h') {
      $tela_anterior = $_SESSION["instancias"][$_GET["instancia"]][$_SESSION["tela_atual"][$_GET["instancia"]] - 1];
    } elseif ($_GET["referencia"]) {
      $tela_anterior = $_SESSION["instancias"][$_GET["instancia"]][$_SESSION["tela_atual"][$_GET["instancia"]] - $_GET["referencia"]];
    } else {
      $tela_anterior = array("cliente_id" => null, "cliente_condominio_id" => null, "condominio_id" => null, "chamado_id" => null, "chamado_acao" => null, "chamado_etapa_id" => null, "aba_busca" => null, "aba_historico" => null, "aba_resumo" => null, "painel_historico" => null, "pagina_busca" => null, "busca_chamados" => null, "busca_clientes" => null, "busca_condominois" => null, "busca_meus_chamados" => null, "busca_favoritos" => null);
    }
    if (!$_GET["atualizacao"]) {
      // Atualiza Barra Navegação
      $chamados_sistema->atualizaBarraNavegacao($_GET["instancia"]);
      // Atualiza Busca - Cores das Abas
      print "document.getElementById('busca_chamados_aba').bgColor = '".($tela_atual["aba_busca"] == "chamados" || $tela_atual["aba_busca"] == "aguardando_retorno" ? "#505050" : "#828282")."';";
      print "document.getElementById('busca_clientes_aba').bgColor = '".($tela_atual["aba_busca"] == "clientes" ? "#505050" : "#828282")."';";
      print "document.getElementById('busca_condominios_aba').bgColor = '".($tela_atual["aba_busca"] == "condominios" ? "#505050" : "#828282")."';";
      print "document.getElementById('meus_chamados_aba').bgColor = '".($tela_atual["aba_busca"] == "meus_chamados" ? "#505050" : "#828282")."';";
      print "document.getElementById('favoritos_aba').bgColor = '".($tela_atual["aba_busca"] == "favoritos" ? "#505050" : "#828282")."';";
      // Atualiza Busca
      if ($_SESSION["instancias"][$_GET["instancia"]][$_SESSION["tela_atual"][$_GET["instancia"]] + 1]) {
        $tela_busca = $_SESSION["instancias"][$_GET["instancia"]][$_SESSION["tela_atual"][$_GET["instancia"]] + 1];
      } else {
        $tela_busca = $tela_atual;
      }
      // Atualiza Busca - Dadods Busca - Chamados
      if ($tela_busca["busca_chamados"] != $tela_anterior["busca_chamados"]) {
        print "document.getElementById('chamados_codigo').value = '{$tela_busca["busca_chamados"]["codigo"]}';";
        print "document.getElementById('chamados_tipo').value = '{$tela_busca["busca_chamados"]["tipo"]}';";
        print "document.getElementById('chamados_conteudo').value = '{$tela_busca["busca_chamados"]["conteudo"]}';";
        print "document.getElementById('chamados_cliente').value = '{$tela_busca["busca_chamados"]["cliente"]}';";
        print "document.getElementById('chamados_condominio').value = '{$tela_busca["busca_chamados"]["condominio"]}';";
        print "document.getElementById('chamados_categoria').value = '{$tela_busca["busca_chamados"]["categoria"]}';";
        print "document.getElementById('chamados_responsavel_tipo').value = '{$tela_busca["busca_chamados"]["responsavel_tipo"]}';";
        print "document.getElementById('chamados_responsavel').value = '{$tela_busca["busca_chamados"]["responsavel"]}';";
        print "document.getElementById('chamados_data_tipo').value = '{$tela_busca["busca_chamados"]["data_tipo"]}';";
        print "document.getElementById('chamados_data_inicio').value = '{$tela_busca["busca_chamados"]["data_inicio"]}';";
        print "document.getElementById('chamados_data_termino').value = '{$tela_busca["busca_chamados"]["data_termino"]}';";
        print "document.getElementById('chamados_status').value = '{$tela_busca["busca_chamados"]["status"]}';";
      }
      // Atualiza Busca - Dados Busca - Clientes
      if ($tela_busca["busca_clientes"] != $tela_anterior["busca_clientes"]) {
        print "document.getElementById('clientes_id').value = '{$tela_busca["busca_clientes"]["id"]}';";
        print "document.getElementById('clientes_nome').value = '{$tela_busca["busca_clientes"]["nome"]}';";
        print "document.getElementById('clientes_condominio').value = '{$tela_busca["busca_clientes"]["condominio"]}';";
        $blocos = $chamados_sistema->buscaCondominiosBlocos($tela_busca["busca_clientes"]["condominio"]);
        foreach ($blocos as $id => $nome) {
          $js_blocos[] = 'new Array("'.$id.'", "'.$nome.'")';
        }
      	print "atualizaSelect(document.getElementById('clientes_bloco'), new Array(".@implode(", ", $js_blocos)."));";
        print "document.getElementById('clientes_bloco').value = '{$tela_busca["busca_clientes"]["bloco"]}';";
        print "document.getElementById('clientes_complemento').value = '{$tela_busca["busca_clientes"]["complemento"]}';";
        print "document.getElementById('clientes_tipo').value = '{$tela_busca["busca_clientes"]["tipo"]}';";
        print "document.getElementById('clientes_cpf_cnpj').value = '{$tela_busca["busca_clientes"]["cpf_cnpj"]}';";
        print "document.getElementById('clientes_telefone').value = '{$tela_busca["busca_clientes"]["telefone"]}';";
        print "document.getElementById('clientes_email').value = '{$tela_busca["busca_clientes"]["email"]}';";
        print "document.getElementById('clientes_status').value = '{$tela_busca["busca_clientes"]["status"]}';";
      }
      // Atualiza Busca - Dadods Busca - Condomínios
      if ($tela_busca["busca_condominios"] != $tela_anterior["busca_condominios"]) {
        print "document.getElementById('condominios_nome').value = '{$tela_busca["busca_condominios"]["condominios_nome"]}';";
        print "document.getElementById('condominios_cidade').value = '{$tela_busca["busca_condominios"]["cidade"]}';";
        $bairros = $chamados_sistema->buscaBairros($tela_busca["busca_condominios"]["cidade"]);
        foreach ($bairros as $id => $nome) {
          $js_bairros[] = 'new Array("'.$id.'", "'.$nome.'")';
        }
      	print "atualizaSelect(document.getElementById('condominios_bairro'), new Array(".@implode(", ", $js_bairros)."));";
        print "document.getElementById('condominios_bairro').value = '{$tela_busca["busca_condominios"]["bairro"]}';";
        print "document.getElementById('condominios_logradouro').value = '{$tela_busca["busca_condominios"]["logradouro"]}';";
        print "document.getElementById('condominios_responsavel').value = '{$tela_busca["busca_condominios"]["resposnavel"]}';";
        print "document.getElementById('condominios_data_cadastro_inicio').value = '{$tela_busca["busca_condominios"]["data_cadastro_inicio"]}';";
        print "document.getElementById('condominios_data_cadastro_termino').value = '{$tela_busca["busca_condominios"]["data_cadastro_termino"]}';";
        print "document.getElementById('condominios_data_ativacao_inicio').value = '{$tela_busca["busca_condominios"]["data_ativacao_inicio"]}';";
        print "document.getElementById('condominios_data_ativacao_termino').value = '{$tela_busca["busca_condominios"]["data_ativacao_termino"]}';";
        print "document.getElementById('condominios_tipo').value = '{$tela_busca["busca_condominios"]["tipo"]}';";
        print "document.getElementById('condominios_status').value = '{$tela_busca["busca_condominios"]["status"]}';";
      }
    }
    // Atualiza Busca - Painel Selecionado
    if ($tela_atual["aba_busca"] == "meus_chamados" && ($tela_atual["aba_busca"] != $tela_anterior["aba_busca"] || $tela_atual["pagina_busca"] != $tela_anterior["pagina_busca"] || $_GET["chamado_alterado"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
      print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
      print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
      $chamados_sistema->geraResultadoMeusChamados(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, $_GET["atualizacao"]);
    } elseif ($tela_atual["aba_busca"] == "favoritos" && ($tela_atual["aba_busca"] != $tela_anterior["aba_busca"] || $tela_atual["pagina_busca"] != $tela_anterior["pagina_busca"] || $_GET["chamado_alterado"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
      print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
      print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
      $chamados_sistema->geraResultadoFavoritos(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, $_GET["atualizacao"]);
    } elseif ($tela_atual["aba_busca"] == "aguardando_retorno" && ($tela_atual["aba_busca"] != $tela_anterior["aba_busca"] || $tela_atual["pagina_busca"] != $tela_anterior["pagina_busca"] || $_GET["chamado_alterado"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
      print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
      print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
      $chamados_sistema->geraResultadoAguardandoRetorno(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, $_GET["atualizacao"]);
    } elseif ($tela_atual["busca_chamados"] && ($tela_atual["busca_chamados"] != $tela_anterior["busca_chamados"] || $tela_atual["pagina_busca"] != $tela_anterior["pagina_busca"] || $_GET["chamado_alterado"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
      print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
      print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
      $chamados_sistema->geraResultadoBuscaChamados($tela_atual["busca_chamados"], null, $tela_atual["chamado_id"], true, $_GET["atualizacao"]);
    } elseif ($tela_atual["busca_clientes"] && ($tela_atual["busca_clientes"] != $tela_anterior["busca_clientes"] || $tela_atual["pagina_busca"] != $tela_anterior["pagina_busca"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
      print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
      print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
      $chamados_sistema->geraResultadoBuscaClientes($tela_atual["busca_clientes"], null, $tela_atual["cliente_id"], true, $_GET["atualizacao"]);
    } elseif ($tela_atual["busca_condominios"] && ($tela_atual["busca_condominios"] != $tela_anterior["busca_condominios"] || $tela_atual["pagina_busca"] != $tela_anterior["pagina_busca"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
      print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
      print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
      $chamados_sistema->geraResultadoBuscaCondominios($tela_atual["busca_condominios"], null, $tela_atual["condominio_id"], true, $_GET["atualizacao"]);
    } elseif ($tela_atual["aba_busca"] != $tela_anterior["aba_busca"] || ($tela_atual["busca_chamados"] != $tela_anterior["busca_chamados"] || $tela_atual["busca_clientes"] != $tela_anterior["busca_clientes"] || $tela_atual["busca_condominios"] != $tela_anterior["busca_condominios"])) {
      print "document.getElementById('busca_chamados_formulario').style.display = ".($tela_atual["aba_busca"] == "chamados" ? "ehNetscape() ? 'table-row-group' : 'block'" : "'none'").";";
      print "document.getElementById('busca_clientes_formulario').style.display = ".($tela_atual["aba_busca"] == "clientes" ? "ehNetscape() ? 'table-row-group' : 'block'" : "'none'").";";
      print "document.getElementById('busca_condominios_formulario').style.display = ".($tela_atual["aba_busca"] == "condominios" ? "ehNetscape() ? 'table-row-group' : 'block'" : "'none'").";";
      print "document.getElementById('busca_resultado').style.display = 'none';";
    }
    // Atualiza Histórico
    if (($tela_atual["painel_historico"] != $tela_anterior["painel_historico"]) || ($tela_atual["aba_historico"] != $tela_anterior["aba_historico"]) || ($tela_atual["cliente_id"] != $tela_anterior["cliente_id"]) || ($tela_atual["condominio_id"] != $tela_anterior["condominio_id"]) || $_GET["chamado_alterado"]) {
      if ($tela_atual["painel_historico"] == "true") {
        $chamados_sistema->geraResultadoHistorico($tela_atual["aba_historico"], $tela_atual["cliente_id"], $tela_atual["cliente_condominio_id"], $tela_atual["condominio_id"], $tela_atual["chamado_id"], true, $_GET["atualizacao"]);
      }
    }
    // Atualiza Resumo
    if (($tela_atual["cliente_id"] != $tela_anterior["cliente_id"]) || ($tela_atual["condominio_id"] != $tela_anterior["condominio_id"])) {
      if ($tela_atual["cliente_id"]) {
        $chamados_sistema->geraDadosResumoCliente($tela_atual["cliente_id"], true);
      } elseif ($tela_atual["condominio_id"]) {
        $chamados_sistema->geraDadosResumoCondominio($tela_atual["condominio_id"], true);
      } else {
        $chamados_sistema->geraDadosChamadoInterno(false);
      }
    }
    if (!$_GET["atualizacao"]) {
      // Deseleciona Registros Antigos
      $chamados_sistema->setaCorRegistro("", $tela_atual["cliente_id"], $tela_atual["condominio_id"], $tela_atual["chamado_id"]);
      // Atualiza Registro Atual
      print "cliente_atual = '{$tela_atual["cliente_id"]}';";
      print "cliente_condominio_atual = '{$tela_atual["cliente_condominio_id"]}';";
      print "condominio_atual = '{$tela_atual["condominio_id"]}';";
      print "chamado_atual = '{$tela_atual["chamado_id"]}';";
      print "chamado_acao_atual = '{$tela_atual["chamado_acao"]}';";
      print "chamado_etapa_atual = '{$tela_atual["chamado_etapa_id"]}';";
      print "aba_busca_atual = '{$tela_atual["aba_busca"]}';";
      print "aba_historico_atual = '{$tela_atual["aba_historico"]}';";
      print "aba_resumo_atual = '{$tela_atual["aba_resumo"]}';";
      print "painel_historico = {$tela_atual["painel_historico"]};";
      print "chamado_alterado = false;";
      // Seleciona Registros Atuais
      $chamados_sistema->setaCorRegistro("#ffcc66");
    }
    // Redimensiona Divs
    print "redimensionaDivs();";
    // Atualiza Chamado
    if ($tela_atual["chamado_id"]) {
      if ($tela_atual["chamado_acao"] && $tela_atual["chamado_acao"] != $tela_anterior["chamado_acao"] && !$_GET["atualizacao"]) {
        if ($tela_atual["chamado_acao"] == "acompanhar") {
          $chamados_sistema->chamadoAcompanhar($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), null, true);
        } elseif ($tela_atual["chamado_acao"] == "fechar_chamado") {
          $chamados_sistema->chamadoFechar($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), null, true);
        } elseif ($tela_atual["chamado_acao"] == "reabrir_chamado") {
          $chamados_sistema->chamadoReabrir($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), null, true);
        } elseif ($tela_atual["chamado_acao"] == "editar_etapa") {
          $chamados_sistema->editarEtapa($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), $tela_atual["chamado_etapa_id"], null, true);
        } elseif ($tela_atual["chamado_acao"] == "adicionar_chamado") {
          $chamados_sistema->chamadoAdicionar($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), null, true, true);
        } elseif ($tela_atual["chamado_acao"] == "liberar_tecnico") {
          $chamados_sistema->liberarTecnico($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), null, true, true);
        }
      } elseif (($tela_atual["chamado_id"] != $tela_anterior["chamado_id"] || $tela_atual["chamado_acao"] != $tela_anterior["chamado_acao"] || $_GET["chamado_alterado"]) && (!$_GET["chamado_alterado"] || !$_GET["atualizacao"])) {
        $chamados_sistema->chamadoVer($tela_atual["chamado_id"], null, true, $_GET["atualizacao"]);
      }
    } elseif ($tela_atual["chamado_acao"] == "adicionar_chamado" && !$_GET["atualizacao"]) {
      $chamados_sistema->chamadoAdicionar($chamados_sistema->buscaDadosSituacao($_GET["instancia"]), null, true, true);
    } elseif (!$_GET["atualizacao"] && ($tela_atual["chamado_id"] != $tela_anterior["chamado_id"] || $tela_atual["chamado_acao"] != $tela_anterior["chamado_acao"])) {
      $chamados_sistema->chamadoVer();
    }
    if ($_GET["atualizacao"]) {
      print "}";
    }

  } elseif ($_GET["a"] == "atualiza_painel") {

    print "document.getElementById('painel').innerHTML = '".addslashes($chamados_sistema->buscaDadosPainel())."';";
    // Atualiza Registro Chamado Bloqueado
    if ($_GET["chamado_atual"]) {
      $chamados_sistema->chamados->registraChamadoVisualizacao($_GET["instancia"], $_GET["chamado_atual"]);
    }

  } elseif ($_GET["a"] == "atualiza_registro_atual") {

    $chamados_sistema->atualizaRegistroAtual($_GET["dados_situacao"]["cliente_atual"], $_GET["dados_situacao"]["cliente_atual"] ? $_GET["dados_situacao"]["cliente_condominio_atual"] : $_GET["dados_situacao"]["condominio_atual"], $_GET["dados_situacao"]["chamado_atual"]);

  } elseif ($_GET["a"] == "registra_tela") {

  	$_GET["dados_filtro"] = array_key_exists("dados_filtro", $_GET) ? $_GET["dados_filtro"] : array();
  	
    $chamados_sistema->registraTela($_GET["dados"]["instancia"], $_GET["dados"]["cliente_atual"], $_GET["dados"]["cliente_condominio_atual"], $_GET["dados"]["condominio_atual"], $_GET["dados"]["chamado_atual"], $_GET["dados"]["chamado_acao_atual"], $_GET["dados"]["chamado_etapa_atual"], $_GET["dados"]["aba_busca_atual"], $_GET["dados_filtro"], $_GET["dados"]["aba_historico_atual"], $_GET["dados"]["aba_resumo_atual"], $_GET["dados"]["painel_historico"], $_GET["dados"]["pagina_busca_atual"]);

  } elseif ($_GET["a"] == "buscar_chamados") {

    // Atualiza Resultado Busca
    $chamados_sistema->geraResultadoBuscaChamados($_GET["dados_filtro"], $_GET["dados_situacao"], $_GET["dados_situacao"]["chamado_atual"]);
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "buscar_meus_chamados") {

    print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
    print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
    print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
    print "document.getElementById('meus_chamados_aba').bgColor = '#505050';";
    // Deseleciona as outras abas
    print "document.getElementById('busca_chamados_aba').bgColor = '#828282';";
    print "document.getElementById('busca_clientes_aba').bgColor = '#828282';";
    print "document.getElementById('busca_condominios_aba').bgColor = '#828282';";
    print "document.getElementById('favoritos_aba').bgColor = '#828282';";
    // Define Aba Atual
    print "aba_busca_atual = 'meus_chamados';";
    // Atualiza Resultado Busca
    $chamados_sistema->geraResultadoMeusChamados($_GET["dados_situacao"], $_GET["pagina"], $_GET["dados_situacao"]["chamado_atual"]);
    print "redimensionaDivs();";
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "buscar_favoritos") {

    print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
    print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
    print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
    print "document.getElementById('favoritos_aba').bgColor = '#505050';";
    // Deseleciona as outras abas
    print "document.getElementById('busca_chamados_aba').bgColor = '#828282';";
    print "document.getElementById('busca_clientes_aba').bgColor = '#828282';";
    print "document.getElementById('busca_condominios_aba').bgColor = '#828282';";
    print "document.getElementById('meus_chamados_aba').bgColor = '#828282';";
    // Define Aba Atual
    print "aba_busca_atual = 'favoritos';";
    // Atualiza Resultado Busca
    $chamados_sistema->geraResultadoFavoritos($_GET["dados_situacao"], $_GET["pagina"], $_GET["dados_situacao"]["chamado_atual"]);
    print "redimensionaDivs();";
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "buscar_aguardando_retorno") {

    print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
    print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
    print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
    print "document.getElementById('busca_chamados_aba').bgColor = '#505050';";
    // Deseleciona as outras abas
    print "document.getElementById('favoritos_aba').bgColor = '#828282';";
    print "document.getElementById('busca_clientes_aba').bgColor = '#828282';";
    print "document.getElementById('busca_condominios_aba').bgColor = '#828282';";
    print "document.getElementById('meus_chamados_aba').bgColor = '#828282';";
    // Define Aba Atual
    print "aba_busca_atual = 'aguardando_retorno';";
    // Atualiza Resultado Busca
    $chamados_sistema->geraResultadoAguardandoRetorno($_GET["dados_situacao"], $_GET["pagina"], $_GET["dados_situacao"]["chamado_atual"]);
    print "redimensionaDivs();";
    // Registra Tela
    print "registraTela();";
    
  } elseif ($_GET["a"] == "buscar_clientes") {

    // Atualiza Resultado Busca
    $chamados_sistema->geraResultadoBuscaClientes($_GET["dados_filtro"], $_GET["dados_situacao"], $_GET["dados_situacao"]["cliente_atual"]);
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "buscar_condominios") {

    // Atualiza Resultado Busca
    $chamados_sistema->geraResultadoBuscaCondominios($_GET["dados_filtro"], $_GET["dados_situacao"], $_GET["dados_situacao"]["condominio_atual"]);
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "ver_chamado") {

    // Atualiza Dados Resumo
    if ($_GET["atualizar_dados_resumo"] || (!$_GET["cliente"] && $_GET["condominio"])) {
      if ($_GET["cliente"]) {
        // Gera Dados Resumo
        $chamados_sistema->geraDadosResumoCliente($_GET["cliente"]);
        if ($_GET["atualizar_historico"]) {
          // Gera Listagem Histórico
          $chamados_sistema->geraResultadoHistorico('historico_geral', $_GET["cliente"], null, null, $_GET["chamado"], true, true);
        }
      } elseif ($_GET["condominio"]) {
        // Gera Dados Resumo
        $chamados_sistema->geraDadosResumoCondominio($_GET["condominio"]);
        if ($_GET["atualizar_historico"]) {
          // Gera Listagem Histórico
          $chamados_sistema->geraResultadoHistorico('historico_geral', null, null, $_GET["condominio"], $_GET["chamado"], true, true);
        }
      } else {
        // Gera Dados Resumo
        $chamados_sistema->geraDadosChamadoInterno();
      }
    }
    // Atualiza Registro Atual
    $chamados_sistema->atualizaRegistroAtual($_GET["cliente"], $_GET["cliente"] ? $_GET["cliente_condominio"] : $_GET["condominio"], $_GET["chamado"]);
    // Chamado
    $chamados_sistema->chamadoVer($_GET["chamado"]);
    // Se chamado anteior foi alterado, atualiza busca
    if ($_GET["chamado_alterado"]) {
      print "atualizaBusca();";
    }
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "ver_cliente") {

    if ($_GET["cliente"]) {
      $chamados_sistema->verCliente($_GET["cliente"], $_GET["condominio"]);
    } else {
      $chamados_sistema->geraDadosChamadoInterno();
      $chamados_sistema->atualizaRegistroAtual(0, 0, 0);
      $chamados_sistema->chamadoVer(0);
    }
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "ver_condominio") {

    $chamados_sistema->verCondominio($_GET["condominio"]);
    // Registra Tela
    print "registraTela();";

  } elseif ($_GET["a"] == "busca_blocos") {

    $blocos = $chamados_sistema->buscaCondominiosBlocos($_GET["condominio"]);
    foreach ($blocos as $id => $nome) {
      $js_blocos[] = 'new Array("'.$id.'", "'.$nome.'")';
    }
  	print "if (document.getElementById(\"clientes_bloco\")) atualizaSelect(document.getElementById(\"clientes_bloco\"), new Array(".@implode(", ", $js_blocos)."));";

  } elseif ($_GET["a"] == "busca_bairros") {

    $bairros = $chamados_sistema->buscaBairros($_GET["cidade"]);
    foreach ($bairros as $id => $nome) {
      $js_bairros[] = 'new Array("'.$id.'", "'.$nome.'")';
    }
  	print "if (document.getElementById(\"condominios_bairro\")) atualizaSelect(document.getElementById(\"condominios_bairro\"), new Array(".@implode(", ", $js_bairros)."));";

  } elseif ($_GET["a"] == "mostrar_historico") {

    $chamados_sistema->geraResultadoHistorico($_GET["aba"], $_GET["cliente_atual"], $_GET["cliente_condominio_atual"], $_GET["condominio_atual"], $_GET["chamado_atual"]);
    $chamados_sistema->atualizaRegistroAtual($_GET["cliente_atual"], $_GET["cliente_atual"] ? $_GET["cliente_condominio_atual"] : $_GET["condominio_atual"], $_GET["chamado_atual"]);
    print "registraTela();";

  } elseif ($_GET["a"] == "adicionar_chamado") {

    $chamados_sistema->chamadoAdicionar($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
    if (!$_GET["dados"]) {
      print "chamado_acao_atual = 'adicionar_chamado';";
    } else {
      print "chamado_acao_atual = '';";
    }
    print "registraTela();";
    if (array_key_exists("referencia", $_GET) && $_GET["referencia"] !== "") {
      print "navegacaoIr('{$_GET["referencia"]}', true);";
    }

  } elseif ($_GET["a"] == "adicionar_chamado_interno") {

    $chamados_sistema->geraDadosChamadoInterno();
    $chamados_sistema->chamadoAdicionar(array(), $_GET["dados"], $_GET["exibir_chamado"]);
    if (!$_GET["dados"]) {
      print "chamado_acao_atual = 'adicionar_chamado';";
    } else {
      print "chamado_acao_atual = '';";
    }
    print "registraTela();";

  } elseif ($_GET["a"] == "editar_chamado_salvar") {

    // Editar Chamado
    $chamados_sistema->chamadoEditar($_GET["chamado_atual"], $_GET["dados"], $_GET["exibir_chamado"]);
    if ($_GET["exibir_chamado"]) {
      // Gera Listagem Histórico
      $chamados_sistema->geraResultadoHistorico('historico_geral', $_GET["cliente_atual"], $_GET["cliente_condominio_atual"], $_GET["condominio_atual"], $_GET["chamado_atual"], true, true);
      // Atualiza Busca
      $chamados_sistema->atualizaBuscaChamados($_GET["instancia"]);
    }
    print "registraTela();";
    if (array_key_exists("referencia", $_GET) && $_GET["referencia"] !== "") {
      print "navegacaoIr('{$_GET["referencia"]}', true);";
    }

  } elseif ($_GET["a"] == "executar_acao") {

    if ($_GET["acao"] == "acompanhar") {
      $chamados_sistema->chamadoAcompanhar($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
    } elseif ($_GET["acao"] == "solicitar_fechamento") {
      $chamados_sistema->chamadoSolicitarFechamento($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
    } elseif ($_GET["acao"] == "registrar_nova_etapa") {
      $chamados_sistema->chamadoNovaEtapa($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
    } elseif ($_GET["acao"] == "fechar_chamado") {
      $chamados_sistema->chamadoFechar($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
      // Atualiza Busca
      $chamados_sistema->atualizaBuscaChamados($_GET["dados_situacao"]["instancia"]);
    } elseif ($_GET["acao"] == "reabrir_chamado") {
      $chamados_sistema->chamadoReabrir($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
      // Atualiza Busca
      $chamados_sistema->atualizaBuscaChamados($_GET["dados_situacao"]["instancia"]);
    } elseif ($_GET["acao"] == "liberar_tecnico") {
      $chamados_sistema->liberarTecnico($_GET["dados_situacao"], $_GET["dados"], $_GET["exibir_chamado"]);
    }
    // Atualiza Busca
    if (!$_GET["dados"]) {
      print "chamado_acao_atual = '{$_GET["acao"]}';";
      print "atualizaCaracteresRestantes();";
    } else {
      print "chamado_acao_atual = '';";
    }
    print "registraTela();";
    if (array_key_exists("referencia", $_GET) && $_GET["referencia"] !== "") {
      print "navegacaoIr('{$_GET["referencia"]}', true);";
    }

  } elseif ($_GET["a"] == "buscar_etapas") {

    $_SESSION["instancias"][$_GET["instancia"]]["chamados_etapas_detalhamento"] = $_GET["detalhada"] ? true : false;
    $dados = $chamados_sistema->chamados->geraListagemEtapas($chamados_sistema->chamados->buscaEtapas($_GET["chamado_id"], $_GET["detalhada"]));
    print "if (document.getElementById(\"etapas\")) document.getElementById(\"etapas\").innerHTML = '".str_replace("'", '\\\'', $dados)."';";

  } elseif ($_GET["a"] == "busca_responsaveis") {

    if ($_GET["categoria_id"] && (!$_GET["somente_responsavel"] || $chamados_sistema->chamados->ehResponsavelCategoria($_GET["categoria_id"], $_SESSION["usuario"]))) {
      $responsaveis = $chamados_sistema->chamados->buscaResponsaveis($_GET["categoria_id"]);
      foreach ($responsaveis as $id => $responsavel) {
        $lista_responsaveis[] = "new Array(\"$id\", \"$responsavel\")";
      }
    }
    print "var responsaveis = new Array(".@implode(",", $lista_responsaveis).");";
    print "if (document.getElementById(\"responsavel\")) atualizaSelect(document.getElementById(\"responsavel\"), responsaveis);";

  } elseif ($_GET["a"] == "busca_condominios_contatos") {

    if ($_GET["condominio"]) {
      $condominios_contatos = $chamados_sistema->buscaCondominiosContatos($_GET["condominio"]);
      print "if (document.getElementById(\"condominios_contatos\")) document.getElementById(\"condominios_contatos\").innerHTML = '".str_replace("'", '\\\'', $condominios_contatos)."';";
    }

  } elseif ($_GET["a"] == "remove_etapa") {

    if ($_GET["etapa"]) {
      $chamados_sistema->removerEtapa($_GET["etapa"]);
    }

  } elseif ($_GET["a"] == "edita_etapa") {

    if ($_GET["etapa"]) {
      $chamados_sistema->editarEtapa($_GET["dados_situacao"], $_GET["etapa"], $_GET["dados"], $_GET["exibir_chamado"]);
      if (!$_GET["dados"]) {
        print "chamado_acao_atual = 'editar_etapa';";
        print "chamado_etapa_atual = '{$_GET["etapa"]}';";
      } else {
        print "chamado_acao_atual = '';";
        print "chamado_etapa_atual = '0';";
      }
      print "registraTela();";
      if (array_key_exists("referencia", $_GET) && $_GET["referencia"] !== "") {
        print "navegacaoIr('{$_GET["referencia"]}', true);";
      }
    }

  } elseif ($_GET["a"] == "atualiza_ultima_instancia") {

    if (count($_SESSION["instancias"][$_GET["instancia"]]) > 1) {
      $_SESSION["ultima_instancia"] = $_GET["instancia"];
    }
    $chamados_sistema->chamados->registraChamadoVisualizacao($_GET["instancia"]);

  } elseif ($_GET["a"] == "favoritos_adicionar") {

    $chamados_sistema->favoritosAdicionar($_GET["chamado"], $_SESSION["usuario"], $_GET["atualiza_busca"]);

  } elseif ($_GET["a"] == "favoritos_remover") {

    $chamados_sistema->favoritosRemover($_GET["chamado"], $_SESSION["usuario"], $_GET["atualiza_busca"]);

  } elseif ($_GET["a"] == "desagendar_visita_tecnica") {
	
		$chamados_sistema->desagendarVisitaTecnica($_GET["chamado"]);
		print "ocultar_painel = false;";
		print "atualizaChamado();";
	
	} elseif ($_GET["a"] == "remover_chamado") {
	
		$chamados_sistema->chamados->removerChamado($_GET["chamado"]);
		print "navegacaoIr(0);";
	
	}

?>

if (window.ocultaPainelCarregando && ocultar_painel) ocultaPainelCarregando();
if (window.redefineAtualizaTela) redefineAtualizaTela();
if (window.hintHide) hintHide();
atualizando = false;

<?
	
} else {
	
?>

function atualizaTela() {
  if (!atualizando) {
    navegacaoIr('0', true, true);
  }
}

function navegacaoIr(referencia, ignorar_alteracoes, atualizacao) {
  if (ignorar_alteracoes || !trataChamadoAlterado(referencia)) {
    if (!atualizacao) exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=navegacao_ir&instancia=" + instancia + "&referencia=" + referencia + "&chamado_alterado=" + (chamado_alterado ? '1' : '0') + "&atualizacao=" + (atualizacao ? '1' : '0') + "&data_hora_alteracao=" + (data_hora_alteracao ? data_hora_alteracao : '');
    RDSCarregar("chamados_sistema_navegacao_ir", url);
  }
}

function registraTela() {
  var dados = new Array();
  dados[dados.length] = 'dados[instancia]=' + instancia;
  dados[dados.length] = 'dados[cliente_atual]=' + cliente_atual;
  dados[dados.length] = 'dados[cliente_condominio_atual]=' + cliente_condominio_atual;
  dados[dados.length] = 'dados[condominio_atual]=' + condominio_atual;
  dados[dados.length] = 'dados[chamado_atual]=' + chamado_atual;
  dados[dados.length] = 'dados[chamado_acao_atual]=' + chamado_acao_atual;
  dados[dados.length] = 'dados[chamado_etapa_atual]=' + chamado_etapa_atual;
  dados[dados.length] = 'dados[aba_busca_atual]=' + aba_busca_atual;
  dados[dados.length] = 'dados[aba_historico_atual]=' + aba_historico_atual;
  dados[dados.length] = 'dados[aba_resumo_atual]=' + aba_resumo_atual;
  dados[dados.length] = 'dados[painel_historico]=' + painel_historico;
  dados[dados.length] = 'dados[pagina_busca_atual]=' + pagina_busca_atual;
  if (aba_busca_atual != 'meus_chamados' && aba_busca_atual != 'favoritos' && aba_busca_atual != 'aguardando_retorno' && document.getElementById('busca_resultado').style.display != 'none') {
    if (aba_busca_atual == 'chamados') {
      if (document.getElementById('chamados_codigo').value) {
        dados_filtro = buscaDadosFiltroChamadosIr();
      } else {
        dados_filtro = buscaDadosFiltroChamados(pagina_busca_atual);
      }
    } else if (aba_busca_atual == 'clientes') {
      if (document.getElementById('clientes_id').value) {
        dados_filtro = buscaDadosFiltroClientesIr();
      } else {
        dados_filtro = buscaDadosFiltroClientes(pagina_busca_atual);
      }
    } else if (aba_busca_atual == 'condominios') {
      dados_filtro = buscaDadosFiltroCondominios(pagina_busca_atual);
    }
    dados_filtro = '&' + dados_filtro;
  } else {
    dados_filtro = '';
  }
  dados = '&' + dados.join('&') + dados_filtro;
  exibePainelCarregando(false);
  url = "rds.js.php?m=chamados_sistema&a=registra_tela" + dados;
  RDSCarregar("chamados_sistema_registra_tela", url);
}

function atualizaRegistroAtual() {
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=atualiza_registro_atual" + buscaDadosSituacao();
  RDSCarregar("chamados_sistema_atualiza_registro_atual", url);
}

////////////////////////////
/// Mostra Meus Chamados ///
////////////////////////////

function mostrarMeusChamados(pagina) {
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=buscar_meus_chamados" + buscaDadosSituacao() + "&pagina=" + (pagina ? pagina : 1);
  RDSCarregar("chamados_sistema_buscar_meus_chamados", url);
}

////////////////////////
/// Mostra Favoritos ///
////////////////////////

function mostrarFavoritos(pagina) {
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=buscar_favoritos" + buscaDadosSituacao() + "&pagina=" + (pagina ? pagina : 1);
  RDSCarregar("chamados_sistema_buscar_favoritos", url);
}
/////////////////////////////////
/// Mostra Aguardando Retorno ///
/////////////////////////////////

function mostrarAguardandoRetorno(pagina) {
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=buscar_aguardando_retorno" + buscaDadosSituacao() + "&pagina=" + (pagina ? pagina : 1);
  RDSCarregar("chamados_sistema_buscar_aguardando_retorno", url);
}

///////////////////////
/// Buscar Chamados ///
///////////////////////

function buscarChamados(pagina) {
  // Limpa Campo Ir
  document.getElementById('chamados_codigo').value = '';
  // Dados Filtro
  dados_filtro = buscaDadosFiltroChamados(pagina);
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=buscar_chamados&" + dados_filtro + buscaDadosSituacao();
  RDSCarregar("chamados_sistema_buscar_chamados", url);
}

///////////////////
/// Chamados Ir ///
///////////////////

function buscarChamadosIr() {
  if (document.getElementById('chamados_codigo').value) {
    trataChamadoAlterado();
    // Limpa Dados Formulário
    limparFormulario('buscarChamados');
    // Dados Filtro
    dados_filtro = buscaDadosFiltroChamadosIr();
    // RDS
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=buscar_chamados&" + dados_filtro + buscaDadosSituacao();
    RDSCarregar("chamados_sistema_buscar_chamados", url);
  }
}

///////////////////////
/// Buscar Clientes ///
///////////////////////

function buscarClientes(pagina) {
  // Limpa Campo Ir
  document.getElementById('clientes_id').value = '';
  // Dados Filtro
  dados_filtro = buscaDadosFiltroClientes(pagina);
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=buscar_clientes&" + dados_filtro + buscaDadosSituacao();
  RDSCarregar("chamados_sistema_buscar_clientes", url);
}

///////////////////
/// Clientes Ir ///
///////////////////

function buscarClientesIr() {
  if (document.getElementById('clientes_id').value) {
    trataChamadoAlterado();
    // Limpa Dados Formulário
    limparFormulario('buscarClientes');
    // Dados Filtro
    dados_filtro = buscaDadosFiltroClientesIr();
    // RDS
    url = "rds.js.php?m=chamados_sistema&a=buscar_clientes&" + dados_filtro + buscaDadosSituacao();
    RDSCarregar("chamados_sistema_buscar_clientes", url);
  }
}

//////////////////////////
/// Buscar Condomínios ///
//////////////////////////

function buscarCondominios(pagina) {
  // Dados Filtro
  dados_filtro = buscaDadosFiltroCondominios(pagina);
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=buscar_condominios&" + dados_filtro + buscaDadosSituacao();
  RDSCarregar("chamados_sistema_buscar_condominios", url);
}

function buscaCondominioContatos(condominio) {
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=busca_condominios_contatos&condominio=" + condominio;
  RDSCarregar("chamados_busca_condominios_contatos", url);
}

///////////////////
/// Ver Cliente ///
///////////////////

function verCliente(cliente, condominio) {
  if (cliente_atual != cliente || !cliente) {
    trataChamadoAlterado();
    // RDS
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=ver_cliente&instancia=" + instancia + "&cliente=" + cliente + "&condominio=" + condominio;
    RDSCarregar("chamados_sistema_ver_cliente", url);
  }
}

//////////////////////
/// Ver Condomínio ///
//////////////////////

function verCondominio(condominio) {
  if (condominio_atual != condominio) {
    trataChamadoAlterado();
    // RDS
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=ver_condominio&instancia=" + instancia + "&condominio=" + condominio;
    RDSCarregar("chamados_sistema_ver_condominio", url);
  }
}

///////////////////
/// Ver Chamado ///
///////////////////

function verChamado(chamado, cliente, cliente_condominio, condominio, atualizar_historico, evento) {
  if (evento) {
    var target_id = ehNetscape() ? evento.target.id : evento.srcElement.id;
    if (target_id == "favoritos_icone") {
      return false;
    }
    if (evento.altKey) {
      abrir("chamados", "?m=chamados&a=ver&i=" + chamado, 620, 520, 1);
      return false;
    }
  }
  if (document.getElementById('seleciona_chamado_0') && chamado != 0 && buscaChamadosSelecionados()) {
    document.getElementById('seleciona_chamado_0').checked = false;
    selecionaTodos();
  }
  trataChamadoAlterado();
  if ((cliente && (cliente_atual != cliente)) || (condominio && (condominio_atual != condominio)) || (cliente == 0 && condominio == 0 && (cliente_atual != 0 || condominio_atual != 0))) {
    var atualizar_dados_resumo = 1;
  } else {
    var atualizar_dados_resumo = 0;
  }
  if (chamado_alterado) {
    atualizar_historico = 1;
  } else {
    atualizar_historico = atualizar_historico ? atualizar_historico : (!cliente && condominio && aba_historico_atual == 'historico_condominio_cliente' ? 1 : 0);
  }
  // RDS
  exibePainelCarregando(true);
  url = "rds.js.php?m=chamados_sistema&a=ver_chamado&instancia=" + instancia + "&cliente=" + cliente + "&cliente_condominio=" + cliente_condominio + "&condominio=" + condominio + "&chamado=" + chamado + "&atualizar_dados_resumo=" + atualizar_dados_resumo + "&atualizar_historico=" + atualizar_historico + "&chamado_alterado=" + (chamado_alterado ? 1 : 0);
  RDSCarregar("chamados_sistema_ver_chamado", url);
}

function atualizaChamado() {
	chamado_alterado = false;
	verChamado(chamado_atual, cliente_atual, cliente_condominio_atual, condominio_atual, null, false);
}

/////////////////////////
/// Adicionar Chamado ///
/////////////////////////

function adicionarChamado() {
  trataChamadoAlterado();
  if (document.getElementById('botao_novo') && document.getElementById('botao_novo').disabled == false) {
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=adicionar_chamado" + buscaDadosSituacao();
    RDSCarregar("chamados_sistema_adicionar_chamado", url);
  }
}

function adicionarChamadoInterno() {
  trataChamadoAlterado();
  if (document.getElementById('botao_chamado_interno') && document.getElementById('botao_chamado_interno').disabled == false) {
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=adicionar_chamado_interno" + buscaDadosSituacao();
    RDSCarregar("chamados_sistema_adicionar_chamado_interno", url);
  }
}

function adicionarChamadoSalvar(exibir_chamado, referencia) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  referencia = referencia ? referencia : '';

  if (!document.getElementById('solicitante').value) erro_vazio[erro_vazio.length] = "Solicitante";
  if (!document.getElementById('nivel_stress').value) erro_vazio[erro_vazio.length] = "Nível de Stress";
  if (!document.getElementById('categoria').value) erro_vazio[erro_vazio.length] = "Categoria";
  if (!document.getElementById('prioridade').value) erro_vazio[erro_vazio.length] = "Prioridade";

  if (apresentaErros(erro_vazio, erro_invalido)) {
    // Dados
    dados = new Array();
    dados[dados.length] = 'dados[solicitante]=' + document.getElementById('solicitante').value;
    dados[dados.length] = 'dados[nivel_stress]=' + document.getElementById('nivel_stress').value;
    dados[dados.length] = 'dados[categoria]=' + document.getElementById('categoria').value;
    dados[dados.length] = 'dados[responsavel]=' + document.getElementById('responsavel').value;
    dados[dados.length] = 'dados[prioridade]=' + document.getElementById('prioridade').value;
    dados[dados.length] = 'dados[tipo]=' + document.getElementById('tipo').value;
    dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
    dados[dados.length] = 'dados[responsavel_fechamento]=' + (document.getElementById('responsavel_fechamento') ? document.getElementById('responsavel_fechamento').value : 0);
    dados[dados.length] = 'dados[acompanhar]=' + (document.getElementById('acompanhar').checked ? 1 : 0);
    dados[dados.length] = 'dados[favoritos]=' + (document.getElementById('favoritos').checked ? 1 : 0);
    dados = '&' + dados.join('&');
    // RDS
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=adicionar_chamado" + dados + buscaDadosSituacao() + "&exibir_chamado=" + exibir_chamado + "&referencia=" + referencia;
    RDSCarregar("chamados_sistema_adicionar_chamado", url);
  } else {
    ocultaPainelCarregando();
    abort();
  }
}

function editarChamadoSalvar(exibir_chamado, referencia) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  referencia = referencia ? referencia : '';

  if (!document.getElementById('categoria').value) erro_vazio[erro_vazio.length] = "Categoria";
  if (!document.getElementById('prioridade').value) erro_vazio[erro_vazio.length] = "Prioridade";
  if (!document.getElementById('andamento').value) erro_vazio[erro_vazio.length] = "Andamento";
  if (document.getElementById('retorno').value == 1) {
	  if (!document.getElementById('data_hora_retorno').value) {
	    erro_vazio[erro_vazio.length] = "Data de Retorno";
	  } else if (!validaDataHora(document.getElementById('data_hora_retorno').value)) {
	    erro_invalido[erro_invalido.length] = "Data de Retorno";
	  }
  }

  if (apresentaErros(erro_vazio, erro_invalido)) {
    // Dados
    dados = new Array();
    dados[dados.length] = 'dados[categoria]=' + document.getElementById('categoria').value;
    dados[dados.length] = 'dados[responsavel]=' + document.getElementById('responsavel').value;
    dados[dados.length] = 'dados[prioridade]=' + document.getElementById('prioridade').value;
    dados[dados.length] = 'dados[andamento]=' + document.getElementById('andamento').value;
    dados[dados.length] = 'dados[retorno]=' + document.getElementById('retorno').value;
    dados[dados.length] = 'dados[data_hora_retorno]=' + document.getElementById('data_hora_retorno').value;
    dados[dados.length] = 'dados[chamado_pai]=' + document.getElementById('chamado_pai').value;
    dados[dados.length] = 'dados[tipo]=' + escape(document.getElementById('tipo').value);
    dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
    dados = '&' + dados.join('&');
    // RDS
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=editar_chamado_salvar&cliente_atual=" + cliente_atual + "&cliente_condominio_atual=" + cliente_condominio_atual + "&condominio_atual=" + condominio_atual + "&chamado_atual=" + chamado_atual + dados + "&exibir_chamado=" + exibir_chamado + "&referencia=" + referencia + "&instancia=" + instancia;
    RDSCarregar("chamados_sistema_editar_chamado_salvar", url);
  } else {
    ocultaPainelCarregando();
    abort();
  }
}

function mudaClienteCondominio(campo) {
  if (campo.value) {
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=busca_blocos&instancia=" + instancia + "&condominio=" + campo.value;
    RDSCarregar("chamados_sistema_busca_blocos", url);
  } else {
    document.getElementById('clientes_bloco').value = '';
    document.getElementById('clientes_bloco').disabled = true;
  }
}

function mudaCategoria(campo, somente_responsavel) {
  if (campo.value) {
    exibePainelCarregando(true);
    somente_responsavel = somente_responsavel ? "1" : "0";
  	url = "rds.js.php?m=chamados_sistema&a=busca_responsaveis&categoria_id=" + campo.value + "&somente_responsavel=" + somente_responsavel;
    RDSCarregar("chamados_sistema_busca_responsaveis", url);
  } else {
    if (document.getElementById("responsavel")) {
      document.getElementById("responsavel").value = "";
      document.getElementById("responsavel").disabled = true;
    }
  }
}

function atualizaCondominiosBairros(cidade) {
  if (cidade) {
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=busca_bairros&instancia=" + instancia + "&cidade=" + cidade;
    RDSCarregar("chamados_sistema_busca_bairros", url);
  } else {
    document.getElementById('condominios_bairro').value = '';
    document.getElementById('condominios_bairro').disabled = true;
  }
}

function mostrarHistorico(aba) {
  if (cliente_atual != '0' || condominio_atual != '0') {
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=mostrar_historico&instancia=" + instancia + "&cliente_atual=" + cliente_atual + "&cliente_condominio_atual=" + cliente_condominio_atual + "&condominio_atual=" + condominio_atual + "&chamado_atual=" + chamado_atual + "&aba=" + aba;
    RDSCarregar("chamados_sistema_mostrar_historico", url);
  }
}

function executaAcao(acao, exibir_chamado, referencia, encaminhamento) {
  if (!document.getElementById('confirmar')) trataChamadoAlterado();

  var dados = new Array();
  var validacao = false;

  referencia = referencia ? referencia : '';

  if (acao == 'acompanhar') {
    if (document.getElementById('confirmar')) {
      validacao = true;
      dados[dados.length] = 'dados[confirmar]=1';
      dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
    }
  } else if (acao == 'fechar_chamado') {
    if (document.getElementById('confirmar')) {
      validacao = validaChamadoFechar(encaminhamento);
      dados[dados.length] = 'dados[confirmar]=1';
      dados[dados.length] = 'dados[andamento]=' + document.getElementById('andamento').value;
      dados[dados.length] = 'dados[tipo]=' + document.getElementById('tipo').value;
      dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
    }
  } else if (acao == 'reabrir_chamado') {
	    if (document.getElementById('confirmar')) {
	      validacao = true;
	      dados[dados.length] = 'dados[confirmar]=1';
	      dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
	    }
  } else if (acao == 'liberar_tecnico') {
    if (document.getElementById('confirmar')) {
      validacao = validaChamadoLiberarTecnico();
      dados[dados.length] = 'dados[mac]=' + document.getElementById('mac').value;
      dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
    }
  } else if (acao == 'solicitar_fechamento') {
    if (document.getElementById('confirmar')) {
      validacao = validaSolicitarFechamento();
      dados[dados.length] = 'dados[responsavel_fechamento]=' + (document.getElementById('responsavel_fechamento') ? document.getElementById('responsavel_fechamento').value : 0);
    }
  }
  if (!document.getElementById('confirmar') || validacao) {
    if (dados.length > 0) {
      dados = '&' + dados.join('&');
    } else {
      dados = '';
    }
    exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=executar_acao&acao=" + acao + dados + "&exibir_chamado=" + exibir_chamado + buscaDadosSituacao() + "&referencia=" + referencia;
    RDSCarregar("chamados_sistema_executar_acao", url);
  }
}

function buscarEtapasChamado(chamado_id) {
  if (chamado_atual != '0') {
    exibePainelCarregando(true);
    var detalhada = document.getElementById('tipo_1').checked ? 0 : 1;
	  url = "rds.js.php?m=chamados_sistema&a=buscar_etapas&instancia=" + instancia + "&chamado_id=" + chamado_atual + "&detalhada=" + detalhada;
    RDSCarregar("chamados_sistema_buscar_etapas", url);
	}
}

function editarEtapa(etapa, salvar, exibir_chamado, referencia) {
  if (salvar != 1) trataChamadoAlterado();
  if (etapa) {
    // Trata Dados
    var dados = new Array();
    if (salvar == 1) {
      dados[dados.length] = 'dados[conteudo]=' + corrigeConteudo(document.getElementById('conteudo').value);
    }
    if (dados.length > 0) {
      dados = '&' + dados.join('&');
    } else {
      dados = '';
    }
    // Referência
    referencia = referencia ? referencia : '';
    // RDS
    exibePainelCarregando(true);
	  url = "rds.js.php?m=chamados_sistema&a=edita_etapa&etapa=" + etapa +  buscaDadosSituacao() + "&exibir_chamado=" + exibir_chamado + "&dados=" + dados + "&referencia=" + referencia;
    RDSCarregar("chamados_sistema_edita_etapa", url);
	}
}

function removerEtapa(etapa) {
  if (confirm('Tem certeza que deseja remover esta etapa?')) {
    if (etapa) {
      exibePainelCarregando(true);
  	  url = "rds.js.php?m=chamados_sistema&a=remove_etapa&instancia=" + instancia + "&etapa=" + etapa;
      RDSCarregar("chamados_sistema_remove_etapa", url);
  	}
  }
}

function atualizaPainel() {
  if (atualiza_painel != 0) {
    clearInterval(atualiza_painel);
    atualiza_painel = 0;
    setInterval('atualizaPainel()', 60000);
  }
  url = "rds.js.php?m=chamados_sistema&a=atualiza_painel&instancia=" + instancia + "&chamado_atual=" + chamado_atual;
  RDSCarregar("chamados_sistema_atualiza_painel", url);
}

function atualizaUltimaInstancia() {
  if (window.opener) {
    url = "rds.js.php?m=chamados_sistema&a=atualiza_ultima_instancia&instancia=" + instancia;
    window.opener.RDSCarregar("chamados_sistema_atualiza_ultima_instancia", url);
  }
}

function favoritosAdicionar(chamado) {
  if (chamado) {
    var atualiza_busca = (aba_busca_atual == "favoritos") ? 1 : 0;
    url = "rds.js.php?m=chamados_sistema&a=favoritos_adicionar&instancia=" + instancia + "&chamado=" + chamado + "&atualiza_busca=" + atualiza_busca;
    RDSCarregar("chamados_sistema_favoritos_adicionar", url);
  }
}

function favoritosRemover(chamado) {
  if (chamado) {
    var atualiza_busca = (aba_busca_atual == "favoritos") ? 1 : 0;
    url = "rds.js.php?m=chamados_sistema&a=favoritos_remover&instancia=" + instancia + "&chamado=" + chamado + "&atualiza_busca=" + atualiza_busca;
    RDSCarregar("chamados_sistema_favoritos_remover", url);
  }
}

function atualizaCorLinhaChamado(chamado) {
  var linha = document.getElementById('chamados_' + chamado);
  if (linha) {
		linha.setAttribute('onmouseover', ("" + linha.getAttribute('onmouseover')).replace(/#D7D7D7/gi, '#E8E8E8').replace(/#dee9f5/gi, '#ffffff'));
		linha.setAttribute('onmouseout', ("" + linha.getAttribute('onmouseout')).replace(/#D7D7D7/gi, '#E8E8E8'));
    linha.setAttribute('bgcolor', '#E8E8E8');
  }
}

function desagendarVisitaTecnica() {
  if (confirm('Tem certeza que deseja desagendar a visita desse chamado?')) {
		exibePainelCarregando(true);
    url = "rds.js.php?m=chamados_sistema&a=desagendar_visita_tecnica&chamado=" + chamado_atual;
    RDSCarregar("chamados_sistema_desagendar_visita_tecnica", url);
  }
}

function removerChamado() {
	if (confirm('Tem certeza que deseja remover permanentemente chamado?')) {
		url = "rds.js.php?m=chamados_sistema&a=remover_chamado&chamado=" + chamado_atual;
		RDSCarregar("chamados_sistema_remover_chamado", url);
	}
}

<?

}

	if (false) { ?></script><? }

?>
