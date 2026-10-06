<?

class chamados_sistema {

  var $bancodedados;
  var $permissoes;
  var $acao;
  var $conteudo;
  var $chamados;
  var $instancia;

  function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if (array_key_exists("instancia", $_GET) && $_GET["instancia"]) {
      $this->instancia = $_GET["instancia"];
    } elseif (array_key_exists("dados_situacao", $_GET) && $_GET["dados_situacao"]["instancia"]) {
      $this->instancia = $_GET["dados_situacao"]["instancia"];
    }
    $this->chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
    $this->chamados->instancia = $this->instancia;
    if ($executar) {
      $this->acao = array_key_exists("a", $_GET) && $_GET["a"] ? $_GET["a"] : "operar";
      $this->{$this->acao}();
    }
  }

  function carregaArquivo($arquivo) {
    if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
    $fp = fopen($arquivo, "r");
    $retorno = fread($fp, filesize($arquivo));
    fclose($fp);
    return $retorno;
  }

  function geraSelect($itens, $selecionado = null, $item_nulo = false) {
    if ($item_nulo) {
      $select = "<option></option>";
    } else {
    	$select = "";
    }
    if ($itens) {
      foreach ($itens as $valor => $item) {
        $select .= "<option value=\"$valor\"".(isset($selecionado) && $valor == $selecionado ? " selected" : "").">$item</option>";
      }
    }
    return $select;
  }

  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }

  #########################
  ### Interface Sistema ###
  #########################

  function operar() {
    if ($_GET["instancia"]) {
      $instancia = $_GET["instancia"];
    } else {
      if ($_GET["nova_instancia"] || !$_SESSION["instancias"] || !$_SESSION["ultima_instancia"] || $_GET["chamado"]) {
        $instancia = md5(uniqid(rand(), true));
        if ($_GET["chamado"]) {
          $_GET["cliente"] = $_GET["cliente"] ? $_GET["cliente"] : "0";
          $_GET["cliente_condominio"] = $_GET["cliente"] && $_GET["condominio"] ? $_GET["condominio"] : "0";
          $_GET["condominio"] = $_GET["condominio"] && !$_GET["cliente"] ? $_GET["condominio"] : "0";
          $this->registraTela($instancia, $_GET["cliente"], $_GET["cliente_condominio"], $_GET["condominio"], $_GET["chamado"], "", "0", "chamados", null, "historico_geral", "dados_resumo", "true", 1, false);
        }
      } else {
        $instancia = $_SESSION["ultima_instancia"];
        unset($_SESSION["ultima_instancia"]);
      }
      header("Location: ./?m=chamados_sistema&a=operar&instancia=$instancia");
      exit;
    }
    $tela_atual = $this->buscaTelaAtual($instancia, 0);
    $this->geraInterface($instancia, $tela_atual);
    $this->geraCabecalho();
    $this->geraPainel();
    $this->geraUsuario();
    $this->geraNavegacao($instancia);
    $this->geraBusca($instancia, $tela_atual);
    $this->geraHistorico($tela_atual);
    $this->geraResumo($tela_atual);
    $this->geraChamado($instancia, $tela_atual);
    $this->executaComandos($instancia, $tela_atual);
    
    $this->conteudo = str_replace('[data_hora_alteracao]', DATA_HORA_ALTERACAO, $this->conteudo);
  }

  function geraInterface($instancia, $tela_atual) {
    $this->conteudo = $this->carregaArquivo("chamados_sistema.interface.inc.php");
    $this->conteudo = str_replace('[instancia]', $instancia, $this->conteudo);
    $this->conteudo = str_replace('[cliente_atual]', $tela_atual["cliente_id"], $this->conteudo);
    $this->conteudo = str_replace('[cliente_condominio_atual]', $tela_atual["cliente_condominio_id"], $this->conteudo);
    $this->conteudo = str_replace('[condominio_atual]', $tela_atual["condominio_id"], $this->conteudo);
    $this->conteudo = str_replace('[chamado_atual]', $tela_atual["chamado_id"], $this->conteudo);
    $this->conteudo = str_replace('[chamado_acao_atual]', $tela_atual["chamado_acao"], $this->conteudo);
    $this->conteudo = str_replace('[chamado_etapa_atual]', $tela_atual["chamado_etapa_id"], $this->conteudo);
    $this->conteudo = str_replace('[aba_busca_atual]', $tela_atual["aba_busca"], $this->conteudo);
    $this->conteudo = str_replace('[aba_historico_atual]', $tela_atual["aba_historico"], $this->conteudo);
    $this->conteudo = str_replace('[aba_resumo_atual]', $tela_atual["aba_resumo"], $this->conteudo);
    $this->conteudo = str_replace('[painel_historico]', $tela_atual["painel_historico"], $this->conteudo);
    $this->conteudo = str_replace('[pagina_busca_atual]', $tela_atual["pagina_busca"], $this->conteudo);
  }

  function geraCabecalho() {
    $this->conteudo = str_replace('[cabecalho]', $this->carregaArquivo("chamados_sistema.cabecalho.inc.php"), $this->conteudo);
  }

  function geraUsuario() {
    $this->conteudo = str_replace('[usuario]', $this->carregaArquivo("chamados_sistema.usuario.inc.php"), $this->conteudo);
    $this->conteudo = str_replace('[usuario_nome]', $this->buscaUsuarioNome($_SESSION["usuario"], true), $this->conteudo);
    $this->conteudo = str_replace('[data_hora]', date('d/m H:i'), $this->conteudo);
    $this->conteudo = str_replace('[alerta_agendamento_visivel]', $this->ehAlertaAgendamentoVisivel() ? "" : "style=\"display:none\"", $this->conteudo);
    
    $agent_id = $this->buscaAgenteId();
    if ($agent_id) {
    	$this->conteudo = str_replace('[painel_ramais]', "", $this->conteudo);
    	$this->conteudo = str_replace('[painel_agente]', $this->geraPainelAgente($agent_id), $this->conteudo);
    } else {
    	$this->conteudo = str_replace('[painel_ramais]', $this->geraPainelRamais(), $this->conteudo);
    	$this->conteudo = str_replace('[painel_agente]', "", $this->conteudo);
    }
  }
  
  function ehAlertaAgendamentoVisivel() {
  	return $this->chamados->existeChamadosAguardandoAgendamento($_SESSION["usuario"]);
  }
  
  function geraPainelRamais() {
    $interfaces = controle::instanciaObjeto("interfaces", $this->bancodedados, $this->permissoes);
  	return "
                <td width=\"5\"></td>
                <td width=\"30\">
                  <script type=\"text/javascript\">
                    var menu_ramal = new menu([".$interfaces->geraPainelRamais()."], RAMAL_POS);
                  </script>
                </td>";
  }
  
  function geraPainelAgente($agent_id) {
  	return "
        <tr>
          <td height=\"5\"></td>
        </tr>
        <tr>
          <td colspan=\"7\">
            <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
              <tr>
                <td style=\"width:110px;background-color:#252525;border:1px solid #FFFFFF\" id=\"painel_ramal\">
                  <table height=\"20\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#252525\">
                    <tr>
                      <td>
											  <applet name=\"Asterisk\" archive=\"_java/asterisk-controller.jar?atualizacao=[data_hora_alteracao]\" code=\"br.com.oletelecom.view.Controller\" width=\"108\" height=\"20\" mayscript>
											    <param name=\"mayscript\" value=\"true\">
			 							      <param name=\"ramais\" value=\"".$this->geraListaRamais()."\">
										      <param name=\"address\" value=\"192.168.170.1\">
											    <param name=\"port\" value=\"5038\">
											    <param name=\"user\" value=\"discador\">
											    <param name=\"password\" value=\"df08072005\">
											    <param name=\"memberName\" value=\"$agent_id\">
									      </applet>
                      </td>
                    </tr>
                  </table>
                </td>
                <td style=\"width:5px\"></td>
                <td>
                  <table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#FFFFFF\">
                    <tr>
                      <td bgcolor=\"#E8E8E8\" style=\"height:20px;padding-left:5px;border:1px solid #252525\" class=\"txtAzulEscuro11\" valign=\"middle\" id=\"asterisk_mensagem\">&nbsp;</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>";
  }

  function geraListaRamais() {
  	$usuarios = $this->buscaUsuarios();
  	foreach ($usuarios as $usuario) {
  		$ramais[] = "{$usuario["setor"]},{$usuario["nome"]},{$usuario["ramal"]},{$usuario["tipo"]}";
  	}
  	sort($ramais);
  	$ramais = array_unique($ramais);
  	return implode(";", $ramais);
  }
  
  function buscaAgenteId() {
    $query = "
      SELECT
        usu_agente_id AS agente_id,
        usu_ramal AS ramal
      FROM
        tab_usuarios
      WHERE
        usu_id = {$_SESSION["usuario"]}";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	if ($dados["agente_id"]) {
      	return "Agent/{$dados["agente_id"]}";
    	} else if ($dados["ramal"]) {
    		return "SIP/{$dados["ramal"]}";
    	}
    }
  } 
  
  function buscaUsuarios() {
  	$setores = array();
    $query = "
      SELECT
        tab_usuarios.usu_id AS usuario_id,
        tab_usuarios.usu_nome AS nome,
        tab_usuarios.usu_ramal AS ramal,
        tab_usuarios.usu_agente_id AS agente,
        rel_usu_orf_ors_exerce.ors_id AS setor_id,
        tab_organizacao_funcoes.orf_id AS funcao_id,
        tab_organizacao_funcoes.orf_nome AS funcao
      FROM
        tab_usuarios
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
        INNER JOIN tab_organizacao_funcoes ON (rel_usu_orf_ors_exerce.orf_id = tab_organizacao_funcoes.orf_id)
      WHERE
        tab_usuarios.uss_id = 1 AND
        rel_usu_orf_ors_exerce.ors_id != 5
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if (($dados["ramal"] || $dados["agente"]) && ($dados["setor_id"] != 9 || !$this->ehUsuarioSuporte($dados["usuario_id"]))) {
	      $dados["nome"] = explode(" ", $dados["nome"]);
	      $dados["nome"] = $dados["nome"][0];
	      if ($dados["funcao_id"] != 1) {
	      	$dados["nome"] .= " [{$dados["funcao"]}]"; 
	      }
    		if ($dados["agente"]) {
	      	$dados["ramal"] = "{$dados["agente"]}";
	      	$dados["tipo"] = "a";
	      } else {
	      	$dados["tipo"] =  "r";
	      }
	      if (!array_key_exists($dados["setor_id"], $setores)) {
	      	$setores[$dados["setor_id"]] = $this->buscaSetorPai($dados["setor_id"]);
	      }
	      $dados["setor"] = $setores[$dados["setor_id"]];
	      $usuarios[$dados["setor"].$dados["usuario_id"]] = $dados;
    	}
    }
    return $usuarios;
  }
  
  function ehUsuarioSuporte($usuario_id) {
  	$query = "
  	  SELECT
        COUNT(*) AS usuarios
       FROM
        rel_usu_orf_ors_exerce
      WHERE
        usu_id = $usuario_id AND
        ors_id = 14";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["usuarios"];
    }
  }
  
  function buscaSetorPai($setor_id) {
  	$query = "
  	  SELECT
  	    ors_nome AS nome,
  	    ors_pai AS pai_id
  	  FROM
  	    tab_organizacao_setores
  	  WHERE
  	    ors_id = $setor_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
	  	if ($setor_id == 15) return "Ouvidoria";
	  	if (in_array($setor_id, array(10, 12, 26))) return "Técnico";
	  	if ($dados["pai_id"] == 2) return $this->buscaSetorPai($dados["pai_id"]);
    	return $dados["nome"];
    }
  }

  function geraNavegacao($instancia) {
    $navegacao = $this->buscaBarraNavegacao($instancia);
    $this->conteudo = str_replace('[navegacao]', $navegacao, $this->conteudo);
  }

  function atualizaBarraNavegacao($instancia) {
    print "document.getElementById('navegacao').innerHTML = '".addslashes($this->buscaBarraNavegacao($instancia))."';";
  }

  function buscaBarraNavegacao($instancia) {
    $navegacao[] = '<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">';
    $navegacao[] = '<tr align="center" bgcolor="#E8E8E8">';
    $navegacao[] = '<td width="20%" style="border-top:1px solid #252525;border-bottom:1px solid #252525;border-left:1px solid #252525"><img title="Voltar" width="13" height="13"'.($_SESSION["instancias"][$instancia] && array_key_exists($_SESSION["tela_atual"][$instancia] - 1, $_SESSION["instancias"][$instancia]) ? ' src="imagens/navegacao_voltar.png" style="cursor:pointer" onclick="navegacaoIr(-1)"' : ' src="imagens/navegacao_voltar_disabled.png"').'></td>';
    $navegacao[] = '<td width="20%" style="border-top:1px solid #252525;border-bottom:1px solid #252525"><img title="Avançar" width="13" height="13"'.($_SESSION["instancias"][$instancia] && array_key_exists($_SESSION["tela_atual"][$instancia] + 1, $_SESSION["instancias"][$instancia]) ? ' src="imagens/navegacao_avancar.png" style="cursor:pointer" onclick="navegacaoIr(+1)"' : ' src="imagens/navegacao_avancar_disabled.png"').'></td>';
    $navegacao[] = '<td width="20%" style="border-top:1px solid #252525;border-bottom:1px solid #252525"><img src="imagens/navegacao_atualizar.png" title="Atualizar [F5]" width="13" height="13" style="cursor:pointer" onclick="navegacaoIr(0)"></td>';
    $navegacao[] = '<td width="20%" style="border-top:1px solid #252525;border-bottom:1px solid #252525"><img src="imagens/navegacao_pagina_inicial.png" title="Página Inicial" width="13" height="13" style="cursor:pointer" onclick="navegacaoIr(\'h\')"></td>';
    $navegacao[] = '<td width="20%" style="border-top:1px solid #252525;border-bottom:1px solid #252525;border-right:1px solid #252525"><img src="imagens/navegacao_nova_janela.png" title="Nova Janela" width="13" height="13" style="cursor:pointer;" onclick="novaJanela()"></td>';
    $navegacao[] = '</tr>';
    $navegacao[] = '</table>';
    return implode("", $navegacao);
  }

  function executaComandos($instancia, $tela_atual) {
	$comandos = array();
    if ($tela_atual["chamado_id"] || $tela_atual["chamado_acao"] == "adicionar_chamado") {
      $comandos[] = "atualizaCaracteresRestantes();";
    }
    $visualizacao = $this->chamados->buscaIconeVisualizacoes($instancia, $tela_atual["chamado_id"], true);
    if ($visualizacao) {
      $visualizacao["usuarios"] = implode("\\n\\n", str_replace("<br>", "\\n", $visualizacao["usuarios"]));
      $comandos[] = "alert('Atenção!\\n\\nEste chamado está sendo visualizado pelo(s) usuário(s):\\n\\n{$visualizacao["usuarios"]}');";
    }
    $comandos = implode("  \n", $comandos);
    $this->conteudo = str_replace('[comandos]', $comandos, $this->conteudo);
  }

  function geraBusca($instancia, $tela_atual) {
    $this->conteudo = str_replace('[busca]', $this->carregaArquivo("chamados_sistema.busca.inc.php"), $this->conteudo);
    $acoes  = '<td bgcolor="#'.($tela_atual["aba_busca"] == "chamados" || $tela_atual["aba_busca"] == "aguardando_retorno" ? '505050' : '828282').'" class="txtBranco11" style="cursor:pointer" onclick="mostrarBuscaChamados()" id="busca_chamados_aba"><b>Chamados</b> <span style="font-size:9px">[F6]</span></td>';
    $acoes .= '<td bgcolor="#'.($tela_atual["aba_busca"] == "clientes" ? '505050' : '828282').'" class="txtBranco11" style="cursor:pointer" onclick="mostrarBuscaClientes()" id="busca_clientes_aba"><b>Clientes</b> <span style="font-size:9px">[F7]</span></td>';
    $acoes .= '<td bgcolor="#'.($tela_atual["aba_busca"] == "condominios" ? '505050' : '828282').'" class="txtBranco11" style="cursor:pointer" onclick="mostrarBuscaCondominios()" id="busca_condominios_aba"><b>Condomínios</b> <span style="font-size:9px">[F8]</span></td>';
    $acoes .= '<td bgcolor="#'.($tela_atual["aba_busca"] == "meus_chamados" ? '505050' : '828282').'" class="txtBranco11" style="cursor:pointer" onclick="mostrarMeusChamados()" id="meus_chamados_aba"><b>Meus Chamados</b> <span style="font-size:9px">[F9]</span></td>';
    $acoes .= '<td bgcolor="#'.($tela_atual["aba_busca"] == "favoritos" ? '505050' : '828282').'" class="txtBranco11" style="cursor:pointer" onclick="mostrarFavoritos()" id="favoritos_aba"><b>Favoritos</b> <span style="font-size:9px">[F10]</span></td>';
    $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
    // Exibição Formulários
    $this->conteudo = str_replace('[exibir_busca_chamados]', ($tela_atual["aba_busca"] == "chamados" && !$tela_atual["busca_chamados"] ? '' : 'display:none'), $this->conteudo);
    $this->conteudo = str_replace('[exibir_busca_clientes]', ($tela_atual["aba_busca"] == "clientes" && !$tela_atual["busca_clientes"] ? '' : 'display:none'), $this->conteudo);
    $this->conteudo = str_replace('[exibir_busca_condominios]', ($tela_atual["aba_busca"] == "condominios" && !$tela_atual["busca_condominios"] ? '' : 'display:none'), $this->conteudo);
    $this->conteudo = str_replace('[exibir_busca_resultado]', ($tela_atual["aba_busca"] == "meus_chamados" || $tela_atual["aba_busca"] == "favoritos" || $tela_atual["aba_busca"] == "aguardando_retorno" || $tela_atual["busca_chamados"] || $tela_atual["busca_clientes"] || $tela_atual["busca_condominios"] ? '' : 'display:none'), $this->conteudo);
    $this->conteudo = str_replace('[exibir_busca_status]', ($tela_atual["aba_busca"] == "meus_chamados" || $tela_atual["aba_busca"] == "favoritos" || $tela_atual["busca_chamados"] || $tela_atual["busca_clientes"] || $tela_atual["busca_condominios"] ? '' : 'display:none'), $this->conteudo);
    if ($tela_atual["aba_busca"] == "meus_chamados") {
      $conteudo_busca = $this->geraResultadoMeusChamados(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], false);
    } elseif ($tela_atual["aba_busca"] == "favoritos") {
      $conteudo_busca = $this->geraResultadoFavoritos(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], false);
    } elseif ($tela_atual["aba_busca"] == "aguardando_retorno") {
      $conteudo_busca = $this->geraResultadoAguardandoRetorno(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], false);
    } elseif ($tela_atual["busca_chamados"]) {
      $conteudo_busca = $this->geraResultadoBuscaChamados($tela_atual["busca_chamados"], null, $tela_atual["chamado_id"], false);
    } elseif ($tela_atual["busca_clientes"]) {
      $conteudo_busca = $this->geraResultadoBuscaClientes($tela_atual["busca_clientes"], null, $tela_atual["cliente_id"], false);
    } elseif ($tela_atual["busca_condominios"]) {
      $conteudo_busca = $this->geraResultadoBuscaCondominios($tela_atual["busca_condominios"], null, $tela_atual["condominio_id"], false);
    } else {
      $this->conteudo = str_replace('[busca_resultado_listagem]', '', $this->conteudo);
    }
    $this->conteudo = str_replace('[busca_resultado_listagem]', $conteudo_busca["listagem"], $this->conteudo);
    $this->conteudo = str_replace('[busca_status]', $conteudo_busca["busca_status"], $this->conteudo);
    
    $condominios = $this->chamados->buscaCondominios();
    if ($_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia] + 1]) {
      $tela_busca = $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia] + 1];
    } else {
      $tela_busca = $tela_atual;
    }
    // Campos Chamados
    $this->conteudo = str_replace('[chamados_codigo]', $tela_busca["busca_chamados"]["codigo"], $this->conteudo);
    $this->conteudo = str_replace('[chamados_tipo]', $this->geraSelect($this->chamados->buscaTipos(), $tela_busca["busca_chamados"]["tipo"]), $this->conteudo);
    $this->conteudo = str_replace('[chamados_cliente]', $tela_busca["busca_chamados"]["cliente"], $this->conteudo);
    $this->conteudo = str_replace('[chamados_condominio]', $this->geraSelect($condominios, $tela_busca["busca_chamados"]["condominio"]), $this->conteudo);
    $this->conteudo = str_replace('[chamados_conteudo]', $tela_busca["busca_chamados"]["conteudo"], $this->conteudo);
    $this->conteudo = str_replace('[chamados_categoria]', $this->geraSelect($this->chamados->buscaChamadosCategorias(), $tela_busca["busca_chamados"]["categoria"]), $this->conteudo);
    $this->conteudo = str_replace('[chamados_responsavel]', $this->geraSelect($this->chamados->buscaResponsaveis(), $tela_busca["busca_chamados"]["responsavel"]), $this->conteudo);
    $this->conteudo = str_replace('[chamados_responsavel_tipo]', $this->geraSelect($this->chamados->buscaResponsaveisTipos(), $tela_busca["busca_chamados"]["responsavel_tipo"]), $this->conteudo);
    $this->conteudo = str_replace('[chamados_data_tipo]', $this->geraSelect($this->chamados->buscaDataTipos(), $tela_busca["busca_chamados"]["data_tipo"]), $this->conteudo);
    $this->conteudo = str_replace('[chamados_data_inicio]', $tela_busca["busca_chamados"]["data_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[chamados_data_termino]', $tela_busca["busca_chamados"]["data_termino"], $this->conteudo);
    $this->conteudo = str_replace('[chamados_status]', $this->geraSelect($this->chamados->buscaStatus(), $tela_busca["busca_chamados"] ? $tela_busca["busca_chamados"]["status"] : "1"), $this->conteudo);
    // Campos Clientes
    $this->conteudo = str_replace('[clientes_id]', $tela_busca["busca_clientes"]["id"], $this->conteudo);
    $this->conteudo = str_replace('[clientes_nome]', $tela_busca["busca_clientes"]["nome"], $this->conteudo);
    $this->conteudo = str_replace('[clientes_condominio]', $this->geraSelect($condominios, $tela_busca["busca_clientes"]["condominio"]), $this->conteudo);
    $this->conteudo = str_replace('[clientes_blocos]', $tela_busca["busca_clientes"]["condominio"] ? $this->geraSelect($this->buscaCondominiosBlocos($tela_busca["busca_clientes"]["condominio"]), $tela_busca["busca_clientes"]["bloco"]) : "", $this->conteudo);
    $this->conteudo = str_replace('[clientes_blocos_editavel]', $tela_busca["busca_clientes"]["condominio"] ? "" : "disabled", $this->conteudo);
    $this->conteudo = str_replace('[clientes_complemento]', $tela_busca["busca_clientes"]["complemento"], $this->conteudo);
    $this->conteudo = str_replace('[clientes_tipos]', $this->geraSelect($this->buscaClientesTipos(), $tela_busca["busca_clientes"]["tipo"]), $this->conteudo);
    $this->conteudo = str_replace('[clientes_cpf_cnpj]', $tela_busca["busca_clientes"]["cpf_cnpj"], $this->conteudo);
    $this->conteudo = str_replace('[clientes_telefone]', $tela_busca["busca_clientes"]["telefone"], $this->conteudo);
    $this->conteudo = str_replace('[clientes_email]', $tela_busca["busca_clientes"]["email"], $this->conteudo);
    $this->conteudo = str_replace('[clientes_status]', $this->geraSelect($this->buscaClientesStatus(), $tela_busca["busca_clientes"] ? $tela_busca["busca_clientes"]["status"] : "2"), $this->conteudo);
    // Campos Condomínios
    $this->conteudo = str_replace('[condominios_nome]', $tela_busca["busca_condominios"]["condominios_nome"], $this->conteudo);
    $this->conteudo = str_replace('[condominios_cidades]', $this->geraSelect($this->buscaCidades(), $tela_busca["busca_condominios"]["cidade"]), $this->conteudo);
    $this->conteudo = str_replace('[condominios_bairros]', $tela_busca["busca_condominios"]["cidade"] ? $this->geraSelect($this->buscaBairros($tela_busca["busca_condominios"]["cidade"]), $tela_busca["busca_condominios"]["bairro"]) : "", $this->conteudo);
    $this->conteudo = str_replace('[condominios_bairros_editavel]', $tela_busca["busca_condominios"]["cidade"] ? "" : "disabled", $this->conteudo);
    $this->conteudo = str_replace('[condominios_logradouro]', $tela_busca["busca_condominios"]["logradouro"], $this->conteudo);
    $this->conteudo = str_replace('[condominios_responsavel]', $this->geraSelect($this->buscaCondominiosResponsaveis(), $tela_busca["busca_condominios"]["resposnavel"]), $this->conteudo);
    $this->conteudo = str_replace('[condominios_data_cadastro_inicio]', $tela_busca["busca_condominios"]["data_cadastro_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[condominios_data_cadastro_termino]', $tela_busca["busca_condominios"]["data_cadastro_termino"], $this->conteudo);
    $this->conteudo = str_replace('[condominios_data_ativacao_inicio]', $tela_busca["busca_condominios"]["data_ativacao_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[condominios_data_ativacao_termino]', $tela_busca["busca_condominios"]["data_ativacao_termino"], $this->conteudo);
    $this->conteudo = str_replace('[condominios_tipo]', $this->geraSelect($this->chamados->buscaCondominiosTipos(), $tela_busca["busca_condominios"]["tipo"]), $this->conteudo);
    $this->conteudo = str_replace('[condominios_status]', $this->geraSelect($this->buscaCondominiosStatus(), $tela_busca["busca_condominios"] ? $tela_busca["busca_condominios"]["status"] : "2"), $this->conteudo);
  }

  function geraHistorico($dados) {
    $this->conteudo = str_replace('[historico]', $this->carregaArquivo("chamados_sistema.historico.inc.php"), $this->conteudo);
    $abas  = '<td bgcolor="#'.($dados["aba_historico"] == "historico_geral" ? '505050' : '828282').'" class="txtBranco11" width="33%" onclick="mostrarHistorico(this.id)" id="historico_geral" style="cursor:pointer;font-weight:bold">'.(!$dados["condominio_id"] ? "Cliente" : "Condomínio").'</td>';
    $abas .= '<td bgcolor="#'.($dados["aba_historico"] == "historico_condominio_cliente" ? '505050' : '828282').'" class="txtBranco11" width="33%" onclick="mostrarHistorico(this.id)" id="historico_condominio_cliente" style="cursor:pointer;font-weight:bold'.($dados["cliente_id"] ? "" : ";display:none").'">Condomínio do Cliente</td>';
    $abas .= '<td bgcolor="#'.($dados["aba_historico"] == "historico_clientes_condominio" ? '505050' : '828282').'" class="txtBranco11" width="33%" onclick="mostrarHistorico(this.id)" id="historico_clientes_condominio" style="cursor:pointer;font-weight:bold">Clientes do Condomínio</td>';
    $this->conteudo = str_replace('[abas]', $abas, $this->conteudo);
    if ($dados["cliente_id"] || $dados["condominio_id"]) {
      $this->conteudo = str_replace('[historico_listagem]', $this->geraResultadoHistorico($dados["aba_historico"], $dados["cliente_id"], $dados["cliente_condominio_id"], $dados["condominio_id"], $dados["chamado_id"], false), $this->conteudo);
    } else {
      $this->conteudo = str_replace('[historico_listagem]', "", $this->conteudo);
    }
  }

  function geraChamado($instancia, $dados = null) {
    $this->conteudo = str_replace('[chamado]', $this->carregaArquivo("chamados_sistema.chamado.inc.php"), $this->conteudo);
    if ($dados["chamado_id"]) {
      if ($dados["chamado_acao"] == "acompanhar") {
        $dados_chamado = $this->chamadoAcompanhar($this->buscaDadosSituacao($instancia), null, false);
      } elseif ($dados["chamado_acao"] == "registrar_nova_etapa") {
        $dados_chamado = $this->chamadoNovaEtapa($this->buscaDadosSituacao($instancia), null, false);
      } elseif ($dados["chamado_acao"] == "fechar_chamado") {
        $dados_chamado = $this->chamadoFechar($this->buscaDadosSituacao($instancia), null, false);
      } elseif ($dados["chamado_acao"] == "editar_etapa") {
        $dados_chamado = $this->editarEtapa($this->buscaDadosSituacao($instancia), $dados["chamado_etapa_id"], null, false);
      } else {
        $dados_chamado = $this->chamadoVer($dados["chamado_id"], null, false);
      }
      $this->conteudo = str_replace('[dados_chamado]', $dados_chamado, $this->conteudo);
      $this->conteudo = str_replace('[exibir_fechar_chamado]', 'block', $this->conteudo);
    } elseif ($dados["chamado_acao"] == "adicionar_chamado") {
      $this->conteudo = str_replace('[usuarios_chamado]', '', $this->conteudo);
      $this->conteudo = str_replace('[dados_chamado]', $this->chamadoAdicionar($this->buscaDadosSituacao($instancia), null, false, false), $this->conteudo);
      $this->conteudo = str_replace('[exibir_fechar_chamado]', 'block', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[usuarios_chamado]', '', $this->conteudo);
      $this->conteudo = str_replace('[dados_chamado]', '', $this->conteudo);
      $this->conteudo = str_replace('[exibir_fechar_chamado]', 'none', $this->conteudo);
    }
  }

  function buscaDadosSituacao($instancia) {
    $dados["chamado_atual"] = $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]]["chamado_id"];
    $dados["cliente_atual"] = $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]]["cliente_id"];
    $dados["cliente_condominio_atual"] = $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]]["cliente_condominio_id"];
    $dados["condominio_atual"] = $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]]["condominio_id"];
    return $dados;
  }

  function geraResumo($dados) {
    $this->conteudo = str_replace("[resumo]", $this->carregaArquivo("chamados_sistema.resumo.inc.php"), $this->conteudo);
    // Resumo
    if ($dados["cliente_id"]) {
      $dados_resumo = $this->geraDadosResumoCliente($dados["cliente_id"], false);
    } elseif ($dados["condominio_id"]) {
      $dados_resumo = $this->geraDadosResumoCondominio($dados["condominio_id"], false);
    }
    $this->conteudo = str_replace('[dados_resumo_conteudo]', $dados_resumo["resumo"], $this->conteudo);
    $this->conteudo = str_replace('[resumo_box_display]', $dados["cliente_id"] || $dados["condominio_id"] ? "table-row-group" : "none", $this->conteudo);
    $this->conteudo = str_replace('[dados_contato_conteudo]', $dados_resumo["contatos"], $this->conteudo);
    // Abas
    $abas  = '<td bgcolor="#'.($dados["aba_resumo"] == "dados_resumo" ? '505050' : '828282').'" style="font-weight:bold;cursor:pointer" id="dados_resumo" onclick="if (cliente_atual != \'0\' || condominio_atual != \'0\') exibeResumo(this.id, true)">'.($dados["cliente_id"] ? 'Resumo do Cliente <span id=\'dados_resumo_origem\'>('.$dados_resumo["origem"].')</span>' : ($dados["condominio_id"] ? 'Resumo do Condomínio' : 'Chamado Interno')).'</td>';
    $abas .= '<td bgcolor="#'.($dados["aba_resumo"] == "dados_contato" ? '505050' : '828282').'" style="font-weight:bold;cursor:pointer'.($dados["cliente_id"] || $dados["condominio_id"] ? '' : ';display:none').'" id="dados_contato" onclick="exibeResumo(this.id, true)">Contato(s)</td>';
    $abas .= '<td bgcolor="#505050" style="width:16px;cursor:pointer;margin:0px;padding-top:0px;padding-bottom:0px;padding-right:0px;padding-left:1px" id="resumo_fechar" title="Fechar" onclick="if (cliente_atual != 0 || condominio_atual != 0) verCliente(0, 0)">&nbsp;X&nbsp;</td>';
    $this->conteudo = str_replace('[abas]', $abas, $this->conteudo);
    //$this->conteudo = str_replace('[exibir_dados_resumo]', ($dados["cliente_id"] || $dados["condominio_id"]) && $dados["aba_resumo"] == "dados_resumo" ? "block" : "none", $this->conteudo);
    //$this->conteudo = str_replace('[exibir_dados_contato]', ($dados["cliente_id"] || $dados["condominio_id"]) && $dados["aba_resumo"] == "dados_contato" ? "block" : "none", $this->conteudo);
    // Botões
	  $this->conteudo = str_replace("[botao_novo_status]", $dados["cliente_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_chamado_interno_status]", "", $this->conteudo);
	  $this->conteudo = str_replace("[botao_ver_dc_status]", $dados["cliente_id"] || $dados["condominio_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_ver_df_status]", $dados["cliente_id"] || $dados["condominio_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_ver_dm_status]", $dados["cliente_id"] || $dados["condominio_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_ver_dt_status]", $dados["cliente_id"] || $dados["condominio_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_ver_da_status]", $dados["cliente_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_ver_ch_status]", $dados["cliente_id"] || $dados["condominio_id"] ? "" : "Disabled", $this->conteudo);
	  $this->conteudo = str_replace("[botao_roteiros_status]", $dados["cliente_id"] ? "" : "Disabled", $this->conteudo);
    
    if ($dados["cliente_id"]) {
    } else if ($dados["condominio_id"]) {
    	
    } else {
    	
    }
  }

  function buscaBotoesResumo($cliente_id = null, $condominio_id = null) {
	$botoes = array();
    if ($this->permissoes["chamados"]["adicionar"]) {
      if ($cliente_id || $condominio_id) {
        $botoes[] = '<input type="button" id="adicionar_chamado" value="Novo [F2]" style="width:80px" class="buttonAzulEscuroBranco11" onclick="adicionarChamado()">';
      }
      $botoes[] = '<input type="button" id="adicionar_chamado" value="Chamado Interno [F4]" style="width:125px" class="buttonAzulEscuroBranco11" onclick="adicionarChamadoInterno()">';
    }
    if ($cliente_id) {
      if ($this->permissoes["clientes"]["ver_dc"]) $botoes[] = '<input type="button" id="clientes_ver_dc" value="DC" title="Dados Cadastrais" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCliente(\'dc\')">';
      if ($this->permissoes["clientes"]["ver_df"]) $botoes[] = '<input type="button" id="clientes_ver_df" value="DF" title="Dados Financeiros" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCliente(\'df\')">';
      if ($this->permissoes["clientes"]["ver_dm"]) $botoes[] = '<input type="button" id="clientes_ver_dm" value="DM" title="Dados Comerciais" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCliente(\'dm\')">';
      if ($this->permissoes["clientes"]["ver_dt"]) $botoes[] = '<input type="button" id="clientes_ver_dt" value="DT" title="Dados Técnicos" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCliente(\'dt\')">';
      if ($this->permissoes["clientes"]["ver_da"]) $botoes[] = '<input type="button" id="clientes_ver_da" value="DA" title="Dados Administrativos" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCliente(\'da\')">';
      if ($this->permissoes["clientes"]["ver_da"]) $botoes[] = '<input type="button" id="clientes_ver_ch" value="CH" title="Chamados" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCliente(\'ch\')">';
      if ($this->permissoes["chamados_roteiros"]["executar"]) $botoes[] = '<input type="button" id="roteiros" value="Roteiros" style="width:55px" class="buttonAzulEscuroBranco11" onclick="executarRoteiros()">';
    } elseif ($condominio_id) {
      if ($this->permissoes["condominios"]["ver_dc"]) $botoes[] = '<input type="button" id="clientes_ver_dc" value="DC" title="Dados Cadastrais" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCondominio(\'dc\')">';
      if ($this->permissoes["condominios"]["ver_df"]) $botoes[] = '<input type="button" id="clientes_ver_df" value="DF" title="Dados Financeiros" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCondominio(\'df\')">';
      if ($this->permissoes["condominios"]["ver_dm"]) $botoes[] = '<input type="button" id="clientes_ver_dm" value="DM" title="Dados Comerciais" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCondominio(\'dm\')">';
      if ($this->permissoes["condominios"]["ver_dt"]) $botoes[] = '<input type="button" id="clientes_ver_dt" value="DT" title="Dados Técnicos" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCondominio(\'dt\')">';
      if ($this->permissoes["condominios"]["ver_ch"]) $botoes[] = '<input type="button" id="clientes_ver_ch" value="CH" title="Chamados" style="width:24px" class="buttonAzulEscuroBranco11" onclick="verDadosCondominio(\'ch\')">';
    }
    return implode("&nbsp;", $botoes);
  }

  function atualizaBotoesResumo($cliente_id = null, $condominio_id = null) {
    if ($cliente_id) {
    	print "document.getElementById('botao_novo').disabled = false;";
    	print "document.getElementById('botao_novo').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_chamado_interno').disabled = false;";
    	print "document.getElementById('botao_chamado_interno').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dc').disabled = false;";
    	print "document.getElementById('botao_ver_dc').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_df').disabled = false;";
    	print "document.getElementById('botao_ver_df').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dm').disabled = false;";
    	print "document.getElementById('botao_ver_dm').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dt').disabled = false;";
    	print "document.getElementById('botao_ver_dt').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_da').disabled = false;";
    	print "document.getElementById('botao_ver_da').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_ch').disabled = false;";
    	print "document.getElementById('botao_ver_ch').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_roteiros').disabled = false;";
    	print "document.getElementById('botao_roteiros').className = 'buttonAzulEscuroBranco11';";
    } else if ($condominio_id) {
    	print "document.getElementById('botao_novo').disabled = false;";
    	print "document.getElementById('botao_novo').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_chamado_interno').disabled = false;";
    	print "document.getElementById('botao_chamado_interno').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dc').disabled = false;";
    	print "document.getElementById('botao_ver_dc').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_df').disabled = false;";
    	print "document.getElementById('botao_ver_df').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dm').disabled = false;";
    	print "document.getElementById('botao_ver_dm').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dt').disabled = false;";
    	print "document.getElementById('botao_ver_dt').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_da').disabled = true;";
    	print "document.getElementById('botao_ver_da').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_ver_ch').disabled = false;";
    	print "document.getElementById('botao_ver_ch').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_roteiros').disabled = true;";
    	print "document.getElementById('botao_roteiros').className = 'buttonAzulEscuroBranco11Disabled';";
    } else {
    	print "document.getElementById('botao_novo').disabled = true;";
    	print "document.getElementById('botao_novo').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_chamado_interno').disabled = false;";
    	print "document.getElementById('botao_chamado_interno').className = 'buttonAzulEscuroBranco11';";
    	print "document.getElementById('botao_ver_dc').disabled = true;";
    	print "document.getElementById('botao_ver_dc').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_ver_df').disabled = true;";
    	print "document.getElementById('botao_ver_df').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_ver_dm').disabled = true;";
    	print "document.getElementById('botao_ver_dm').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_ver_dt').disabled = true;";
    	print "document.getElementById('botao_ver_dt').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_ver_da').disabled = true;";
    	print "document.getElementById('botao_ver_da').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_ver_ch').disabled = true;";
    	print "document.getElementById('botao_ver_ch').className = 'buttonAzulEscuroBranco11Disabled';";
    	print "document.getElementById('botao_roteiros').disabled = true;";
    	print "document.getElementById('botao_roteiros').className = 'buttonAzulEscuroBranco11Disabled';";
    }
  }

  function geraPainel() {
    $painel = $this->buscaDadosPainel();
    $this->conteudo = str_replace("[painel]", $painel, $this->conteudo);
  }

  function buscaDadosPainel() {
    $dados_painel["Administração"] = $this->buscaDadosSetor(1);
    $dados_painel["Financeiro"] = $this->buscaDadosSetor(3);
    $dados_painel["Comercial"] = $this->buscaDadosSetor(2);
    $dados_painel["Técnico"] = $this->buscaDadosSetor(4);
    $dados_painel["T.I."] = $this->buscaDadosSetor(5);
    $painel = '<table width="100%" height="71" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">';
    foreach ($dados_painel as $descricao => $valor) {
      $painel .= '<tr><td class="txtBranco10" width="55%" bgcolor="#505050">&nbsp;'.$descricao.'</td><td class="txtBranco10" bgcolor="#828282" align="center">'.$valor.'</td></tr>';
    }
    $painel .= '</table>';
    return $painel;
  }

  function buscaDadosSetor($setor_id) {
    $setores = funcoes::mescla_arrays(array($setor_id), $this->chamados->buscaSetoresAbaixo($setor_id));
    $setores = implode(", ", $setores);
    $query = "
      SELECT
        COUNT(cha_id) AS quantidade
      FROM
        tab_chamados
      WHERE
        ors_id IN ($setores) AND
        chs_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["quantidade"];
    }
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaUsuarioNome($usuario_id, $gera_aniversariante = false) {
    $query = "
      SELECT
       SUBSTRING_INDEX(usu_nome, ' ', 1) AS nome,
       IF(usu_nascimento = CURDATE(), 1, 0) AS aniversariante
      FROM
        tab_usuarios
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"].($gera_aniversariante && $dados["aniversariante"] ? " <img src=\"imagens/bolo_aniversario.png\" width=\"13\" height=\"13\" border=\"0\" style=\"margin:0px\" title=\"Feliz Aniversário!\">" : "");
    }
  }

  function buscaClientesStatus() {
    $query = "
      SELECT
        cls_id AS id,
        cls_nome AS nome
      FROM
        tab_clientes_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaCondominiosStatus() {
    $query = "
      SELECT
        cos_id AS id,
        cos_nome AS nome
      FROM
        tab_condominios_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaClientesTipos() {
    $query = "
      SELECT
        clt_id AS id,
        clt_nome AS nome
      FROM
        tab_clientes_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaCondominiosBlocos($condominio_id) {
  	$lista = array();
  	if ($condominio_id) {
	    $query = "
	      SELECT
	        cob_id AS id,
	        cob_nome AS nome
	      FROM
	        tab_condominios_blocos
	      WHERE
	        con_id = $condominio_id
	      ORDER BY
	        nome";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      $lista[$dados["id"]] = $dados["nome"];
	    }
  	}
    return $lista;
  }

  function buscaCidades() {
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
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cidades[$dados["id"]] = $dados["nome"];
    }
    return $cidades;
  }

  function buscaBairros($cidade_id) {
    $query = "
      SELECT
        bai_id AS id,
        bai_nome AS nome
      FROM
        tab_bairros
      WHERE
        cid_id = $cidade_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $bairros[$dados["id"]] = $dados["nome"];
    }
    return $bairros;
  }

  function buscaCondominiosResponsaveis() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN tab_condominios ON (tab_usuarios.usu_id = tab_condominios.usu_id)
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }

  function buscaQuantidadeBlocos($condominio_id) {
    $query = "
      SELECT
        COUNT(DISTINCT cob_id) AS quantidade
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "quantidade"));
  }

  function buscaQuantidadeApartamentos($condominio_id) {
    $query = "
      SELECT
        SUM(cob_quantidade) AS quantidade
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "quantidade"));
  }

  function buscaClientesAtivos($condominio_id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS quantidade
      FROM
        tab_clientes
      WHERE
        tab_clientes.con_id = $condominio_id AND
        tab_clientes.cls_id = 2";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "quantidade"));
  }

  function buscaClientesPontos($cliente_id) {
    $pontos_disponiveis = $this->buscaPontosDisponiveis($cliente_id);
    if (!$pontos_disponiveis) {
    	return "--";
    }
  	$query = "
      SELECT
        CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome) AS assinatura,
        tab_clientes_pontos.clp_ip AS ip,
        tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_clientes_pontos.clp_velocidade_download AS download,
        tab_clientes_pontos.clp_velocidade_upload AS upload,
        tab_clientes_pontos.clp_ajustado AS ajustado,
        tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
        tab_clientes_pontos.clp_publicado AS publicado,
        tab_clientes_pontos.sei_id AS interface_id,
        tab_clientes_pontos_status.cps_id AS status_id,
        tab_clientes_pontos_status.cps_nome AS status_nome
      FROM
        tab_clientes_pontos
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
        INNER JOIN tab_clientes_pontos_status ON (tab_clientes_pontos.cps_id = tab_clientes_pontos_status.cps_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_clientes_pontos.cps_id IN (1, 2)
      ORDER BY
        tab_planos.plt_id,
        tab_planos.pla_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      // Trata Velocidade
      $unidades = $this->buscaUnidadesVelocidade();
      $unidade = floor(log($dados["download"], 1024));
      if ($unidade < 1) $unidade = 1;
      $velocidade = round($dados["download"] / pow(1024, $unidade), 1)." / ".
      round($dados["upload"] / pow(1024, $unidade), 1)." ".
      $unidades[$unidade];
      // atribui velocidade ao campo
      $dados["velocidade"] = $velocidade;
      // verifica se tem ajuste e mostra os valores ajustados
      if ($dados["parametros_ajuste"] && $dados["ajustado"]) {
        $parametros = unserialize($dados["parametros_ajuste"]);
        $unidade = floor(log($parametros["download"], 1024));
        if ($unidade < 1) $unidade = 1;
        $velocidade = round($parametros["download"] / pow(1024, $unidade), 1)." / ".
        round($parametros["upload"] / pow(1024, $unidade), 1)." ".
        $unidades[$unidade];
        $dados["velocidade"] .= " ($velocidade)";
      }      // Trata Status
      if ($dados["status_id"] != 3) {
        if ($dados["ajustado"]) {
          $dados["status_nome"] .= " (Ajustado)";
        }
        if ($dados["publicado"]) {
          $dados["status_nome"] .= " / Publicado";
        } else {
          $dados["status_nome"] .= " / Não Publicado";
        }
      }
      $dados_interface = $this->buscaDadosInterface($dados["interface_id"]);
      $dados["hint"][] = $dados["assinatura"];
      $dados["hint"][] = "Inteface: ".$dados_interface["interface_nome"];
      if ($dados["ip"]) {
      	$dados["hint"][] = "Endereço IP: {$dados["ip"]}";
      } else {
      	$redes = $this->buscaRedes($dados["interface_id"]);
      	if ($redes) {
	      	$dados["hint"][] = "Rede(s): ".implode(", ", $redes);
      	}
      }
      $dados["hint"][] = "MAC: {$dados["mac"]}";
      $dados["hint"][] = "Velocidade: {$dados["velocidade"]}";
      $dados["hint"][] = "Status: {$dados["status_nome"]}";
      $pontos[] = implode("<br>", $dados["hint"]);
    }
    $autenticacoes = $this->buscaClientesAutenticacoes($cliente_id);
    if ($autenticacoes) {
    	if ($pontos) {
    		$pontos = array_merge($pontos, $autenticacoes);
    	} else {
    		$pontos = $autenticacoes;
    	}
    }
    if ($pontos) {
      return "<div style=\"cursor:pointer\" onmouseover=\"hintShow('".implode("<hr>", $pontos)."', 400)\" onmouseout=\"hintHide()\">".count($pontos)."/$pontos_disponiveis</div>";
    } else {
      return "0/$pontos_disponiveis";
    }
  }
  
  function buscaClientesAutenticacoes($cliente_id) {
  	$query = "
      SELECT
        CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome) AS assinatura,
				tab_clientes_autenticacoes.cau_velocidade_download AS velocidade_download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS velocidade_upload,
				tab_clientes_autenticacoes.cau_ajuste_download AS ajuste_download,
				tab_clientes_autenticacoes.cau_ajuste_upload AS ajuste_upload,
				tab_clientes_autenticacoes.cau_ajustado AS ajustado,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				tab_clientes_autenticacoes_status.cas_id AS status_id,
				tab_clientes_autenticacoes_status.cas_nome AS status_nome
      FROM
        tab_clientes_autenticacoes
        INNER JOIN tab_clientes_autenticacoes_status ON (tab_clientes_autenticacoes.cas_id = tab_clientes_autenticacoes_status.cas_id)
        INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_clientes_autenticacoes.cas_id IN (1, 2)
      ORDER BY
        tab_planos.plt_id,
        tab_planos.pla_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	// Gera dados velocidade
   	  $unidades = $this->buscaUnidadesVelocidade();
   	  $unidade = floor(log($dados["velocidade_download"], 1024));
   	  $unidade = $unidade < 1 ? 1 : $unidade;
   	  $velocidade = round($dados["velocidade_download"] / pow(1024, $unidade), 1)." / ".
   	                round($dados["velocidade_upload"] / pow(1024, $unidade), 1)." ".
   	                $unidades[$unidade];
			// Atribui velocidade ao campo
   		$dados["velocidade"] = $velocidade;
   		// Verifica se tem ajuste e mostra os valores ajustados
    	if ($dados["ajustado"] && $dados["ajuste_download"] && $dados["ajuste_upload"]) {
	   	  $unidade = floor(log($dados["ajuste_download"], 1024));
	   	  if ($unidade < 1) $unidade = 1;
	   	  $velocidade = round($dados["ajuste_download"] / pow(1024, $unidade), 1)." / ".
	   	                round($dados["ajuste_upload"] / pow(1024, $unidade), 1)." ".
	   	                $unidades[$unidade];
   			$dados["velocidade"] .= " ($velocidade)";
   			// Velocidade Final
   			$dados["final_download"] = intval(round($dados["ajuste_download"] / 1024), 10);
   			$dados["final_upload"] = intval(round($dados["ajuste_upload"] / 1024), 10);
    	} else {
   			// Velocidade Final
   			$dados["final_download"] = intval(round($dados["velocidade_download"] / 1024), 10);
   			$dados["final_upload"] = intval(round($dados["velocidade_upload"] / 1024), 10);
    	}
			$dados["bloqueado"] = (!$dados["ajustado"] && ($dados["velocidade_download"] <= 0 || $dados["velocidade_upload"] <= 0)) || ($dados["ajustado"] && ($dados["ajuste_download"] <= 0 || $dados["ajuste_upload"] <= 0));
    	// Trata Status
			if ($dados["status_id"] != 3) {
				if ($dados["ajustado"] && !$dados["bloqueado"]) {
					$dados["status_nome"] .= " (Ajustado)";
				} elseif ($dados["bloqueado"]) {
					$dados["status_nome"] .= " (Bloqueado)";
				}
				if ($dados["publicado"] && $dados["status_id"] != 2 && !$dados["bloqueado"]) {
					$dados["status_nome"] .= " (Publicado)";
				} elseif (!$dados["publicado"] && !$dados["bloqueado"]) {
					$dados["status_nome"] .= " (Não Publicado)";
				}
			}
      $dados["hint"][] = $dados["assinatura"];
      $dados["hint"][] = "Velocidade: {$dados["velocidade"]}";
      $dados["hint"][] = "Status: {$dados["status_nome"]}";
      $autenticacoes[] = implode("<br>", $dados["hint"]);
    } 
    return $autenticacoes;
  }
  
  function buscaRedes($interface_id) {
  	$redes = array();
  	if ($interface_id) {
			$query = "
			  SELECT
			    red_endereco_ip AS ip,
			    red_mascara AS mascara
			  FROM
			    tab_redes
			  WHERE
			    sei_id = $interface_id";
			$query = $this->bancodedados->query($query);
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$redes[] = "{$dados["ip"]} / {$dados["mascara"]}";
			}
  	}
		return $redes;
  }

	function buscaDadosInterface($interface_id) {
		if ($interface_id) {
	    $query = "
	      SELECT
	        tab_servidores_interfaces.ser_id AS servidor_id,
	        tab_servidores_interfaces.sei_prefixo_ip AS prefixo,
				  CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, IF(interface_pai.sei_id IS NULL, '', CONCAT(' [', interface_pai.sei_nome, ']')), IF(tab_servidores_interfaces.sei_prefixo_ip IS NULL, '', CONCAT(', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip)), ')') AS interface_nome
	      FROM
	        tab_servidores_interfaces
	        INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
				  LEFT OUTER JOIN tab_servidores_interfaces interface_pai ON (tab_servidores_interfaces.sei_pai = interface_pai.sei_id)
	      WHERE
	        tab_servidores_interfaces.sei_id = $interface_id";
	    $query = $this->bancodedados->query($query);
	    return $this->bancodedados->resultadoArray($query);
		}
	}

  function buscaPontosDisponiveis($cliente_id) {
    $pontos_disponiveis = 0;
    $query = "
      SELECT
        tab_assinaturas.ass_id AS id,
         tab_assinaturas.ass_parametros AS parametros
      FROM
        tab_assinaturas
        INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
        INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
        LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
      WHERE
        (tab_assinaturas.ast_id IN (1, 2, 5)) AND
        (tab_contratos.crt_id IN (11, 12)) AND
        (tab_contratos.crs_id IN (19, 20, 21) OR tab_visitas.vis_data <= CURDATE()) AND
        (tab_assinaturas.cli_id = $cliente_id)
      GROUP BY
        tab_assinaturas.ass_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $parametros = unserialize($dados["parametros"]);
      $pontos_disponiveis += $parametros["pontos_quantidade"];
    }
    return $pontos_disponiveis;
  }

  function buscaUnidadesVelocidade() {
    $unidadecapacidade["1"] = "Kbps";
    $unidadecapacidade["2"] = "Mbps";
    $unidadecapacidade["3"] = "Gbps";
    $unidadecapacidade["4"] = "Tbps";
    return $unidadecapacidade;
  }

  function buscaClientesDominios($cliente_id) {
    $query = "
      SELECT DISTINCT
        CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome) AS assinatura,
        tab_dominios.doo_id AS origem,
        tab_dominios.dom_responsavel AS responsavel,
        tab_dominios.dom_fapesp AS fapesp,
        tab_dominios.dom_dominio AS dominio,
        tab_dominios.dom_emails AS emails,
        tab_dominios_status.dos_nome AS status
      FROM
        tab_planos_servicos
        INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
        INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
        INNER JOIN tab_dominios ON (tab_assinaturas.ass_id = tab_dominios.ass_id)
        INNER JOIN tab_dominios_status ON (tab_dominios.dos_id = tab_dominios_status.dos_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_dominios.dos_id IN (3, 4)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["hint"][] = "Assinatura: ".preg_replace("/\\[[^\\]]*\\]/", "", $dados["assinatura"]);
      //if ($dados["origem"]) $dados["hint"][] = "Origem: {$dados["origem"]}";
      //if ($dados["responsavel"]) $dados["hint"][] = "Responsável: {$dados["responsavel"]}";
      //if ($dados["fapesp"]) $dados["hint"][] = "ID Fapesp: {$dados["fapesp"]}";
      if ($dados["dominio"]) $dados["hint"][] = "Domínio: {$dados["dominio"]}";
      //if ($dados["emails"]) $dados["hint"][] = "E-mail(s): {$dados["emails"]}";
      $dados["hint"][] = "Status: {$dados["status"]}";
      $dominios[] = implode("<br>", $dados["hint"]);
    }
    if ($dominios) {
      return "<div style=\"cursor:pointer\" onmouseover=\"hintShow('".implode("<hr>", $dominios)."', 400)\" onmouseout=\"hintHide()\">".count($dominios)."/".count($dominios)."</div>";
    } else {
      return "--";
    }
  }

  function buscaCondasVoIP($cliente_id) {
    return "N/D";
  }

  function buscaClientesEmails($cliente_id) {
    $emails_disponiveis = $this->buscaEmailsDisponiveis($cliente_id);
    if (!$emails_disponiveis) {
    	return "--";
    }
  	$query = "
      SELECT
        CONCAT(tab_planos_servicos.pls_nome, ' - ', tab_planos.pla_nome) AS assinatura,
        CONCAT(tab_clientes_emails.cle_usuario, '@', tab_dominios.dom_dominio) AS email,
        tab_clientes_emails.cle_capacidade AS capacidade,
        tab_clientes_emails_status.ces_id AS status_id,
        tab_clientes_emails_status.ces_nome AS status_nome
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
        INNER JOIN tab_clientes_emails_status ON (tab_clientes_emails.ces_id = tab_clientes_emails_status.ces_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_clientes_emails.ces_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["capacidade"] = $this->formataCapacidade($dados["capacidade"]);
      $dados["hint"][] = $dados["assinatura"];
      $dados["hint"][] = "E-mail: {$dados["email"]}";
      $dados["hint"][] = "Capacidade: {$dados["capacidade"]}";
      $dados["hint"][] = "Status: {$dados["status_nome"]}";
      $emails[] = implode("<br>", $dados["hint"]);
    }
    if ($emails) {
      return "<div style=\"cursor:pointer\" onmouseover=\"hintShow('".implode("<hr>", $emails)."', 400)\" onmouseout=\"hintHide()\">".count($emails)."/$emails_disponiveis</div>";
    } else {
      return "0/$emails_disponiveis";
    }
  }

  function buscaEmailsDisponiveis($cliente_id) {
    $pontos_disponiveis = 0;
    $query = "
      SELECT DISTINCT
        tab_assinaturas.ass_id AS id,
         tab_assinaturas.ass_parametros AS parametros
      FROM
        tab_assinaturas
        INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
        INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
      WHERE
        tab_assinaturas.ast_id IN (1, 2, 5) AND
        tab_assinaturas.cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $parametros = unserialize($dados["parametros"]);
      $pontos_disponiveis += $parametros["emails_quantidade"];
    }
    return $pontos_disponiveis;
  }

  function formataCapacidade($capacidade) {
    $unidades["1"] = "KB";
    $unidades["2"] = "MB";
    $unidades["3"] = "GB";
    $unidades["4"] = "TB";
    $unidade_capacidade = floor(log($capacidade, 1024));
    return round($capacidade / pow(1024, $unidade_capacidade), 2)." ".$unidades[$unidade_capacidade];
  }

  function buscaPontosAtivos($condominio_id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes_pontos.clp_id) AS quantidade
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
      WHERE
        tab_clientes.con_id = $condominio_id AND
        tab_clientes_pontos.cps_id = 1";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "quantidade"));
  }

  function buscaMensalidade($condominio_id) {
    $mensalidade = $this->calculaMensalidade($condominio_id, $this->buscaMensalidadeBase(91), "1");
    return $this->formataDinheiro($mensalidade);
  }

  function buscaUltimaCobranca($dados_cliente) {
    $data_inicial = date("Y-m-01", strtotime("TODAY"));
    $data_final = date("Y-m-t", strtotime("NEXT MONTH"));
    $query = "
      SELECT
        coa_valor_original AS valor_original
      FROM
        tab_cobrancas
      WHERE
        cli_id = {$dados_cliente["id"]} AND
        coa_data_vencimento BETWEEN '$data_inicial' AND '$data_final' AND
        cot_id NOT IN (12) AND
        coa_pai IS NULL
      ORDER BY
        coa_data_vencimento DESC";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $this->formataDinheiro($dados["valor_original"]);
    } else {
    	return "Não disponível";
    }
  }

  function buscaSituacaoFinanceira($cliente_id) {
  	$dados_ajuste = $this->buscaAjusteAtivo($cliente_id);
    if ($dados_ajuste) {
      $cor = $dados_ajuste["desbloqueio_inicio"] ? "orange" : "red";
      $descricao = $dados_ajuste["desbloqueio_inicio"] ? "Susp. Temp." : "Bloqueado";
      $hint[] = $dados_ajuste["categoria"];
      $hint[] = $dados_ajuste["desbloqueio_inicio"] ? "Início: {$dados_ajuste["desbloqueio_inicio"]}<br>Término: {$dados_ajuste["desbloqueio_termino"]}" : "Início: {$dados_ajuste["ajuste_inicio"]}".($dados_ajuste["ajuste_termino"] ? "<br>Término: {$dados_ajuste["ajuste_termino"]}" : "");
      $mensalidades = $this->buscaMensalidadesPendentes($cliente_id);
      if ($mensalidades) {
        $descricao .= " ({$mensalidades["quantidade"]})";
        $hint[] = "<hr>{$mensalidades["hint"]}";
      }
      $hint = implode("<br>", $hint);
      return "<div style=\"color:$cor;font-weight:bold;cursor:pointer\" onmouseover=\"hintShow('$hint', 180)\" onmouseout=\"hintHide()\">$descricao</div>";
    } else {
    	$mensalidades = $this->buscaMensalidadesPendentes($cliente_id);
      if ($mensalidades) {
        return "<div style=\"color:orange;font-weight:bold;cursor:pointer\" onmouseover=\"hintShow('{$mensalidades["hint"]}', 180)\" onmouseout=\"hintHide()\">Pendente ({$mensalidades["quantidade"]})</div>";
      } else {
        return "<div style=\"color:green;font-weight:bold\">Regular</div>";
      }
    }
  }

  function buscaMensalidadesPendentes($cliente_id) {
    $query = "
      SELECT
        IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.coa_id, tab_cobrancas.coa_id) AS id,
        IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.coa_codigo, tab_cobrancas.coa_codigo) AS codigo,
        DATE_FORMAT(IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.coa_data_vencimento, tab_cobrancas.coa_data_vencimento), '%d/%m/%Y') AS data_vencimento,
        IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.coa_valor_original, tab_cobrancas.coa_valor_original) AS valor_original,
        IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.col_id, tab_cobrancas.col_id) AS lote_id,
        IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.coa_pai, tab_cobrancas.coa_pai) AS coa_pai
      FROM
        tab_cobrancas
        LEFT OUTER JOIN tab_cobrancas tab_cobrancas_filhas ON (tab_cobrancas_filhas.coa_pai = tab_cobrancas.coa_id)
      WHERE
        tab_cobrancas.cli_id = $cliente_id AND
        tab_cobrancas.cot_id = 1 AND
        tab_cobrancas.coa_pai IS NULL
      GROUP BY
        tab_cobrancas.coa_id
      HAVING
        IF(COUNT(tab_cobrancas_filhas.coa_id) > 0 AND MAX(tab_cobrancas_filhas.coa_data_vencimento) > MAX(tab_cobrancas.coa_data_vencimento), MAX(tab_cobrancas_filhas.coa_data_vencimento) <= DATE_SUB(NOW(), INTERVAL 1 DAY), MAX(tab_cobrancas.coa_data_vencimento) <= DATE_SUB(NOW(), INTERVAL 1 DAY))
      ORDER BY
        IF(tab_cobrancas_filhas.coa_id, tab_cobrancas_filhas.coa_data_vencimento, tab_cobrancas.coa_data_vencimento)";
    $query = $this->bancodedados->query($query);
    $quantidade = $this->bancodedados->linhas($query);
    if ($quantidade) {
	    $plural = $quantidade == 1 ? "" : "s";
	    $hints[] = "$quantidade cobrança{$plural} vencida{$plural}:";
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      // Trata Dados
	      $dados["valor_original"] = $this->formataDinheiro($dados["valor_original"]);
	      $dados["competencia"] = $this->buscaCobrancaCompetencia($dados["id"]);
	      if ($dados["lote_id"]) $dados["formato_nome"] .= " - ".$this->formataData($dados["data_vencimento"]);
	      if (!$dados["lote_id"]) $dados["formato_nome"] .= ' - Cobrança Avulsa';
	      if ($dados["coa_pai"]) $dados["formato_nome"] .= ' (Segunda Via)';
	      // Gera Hint
	      $dados_hint = "Código: {$dados["codigo"]}<br>";
	      $dados_hint .= "Vencimento: {$dados["data_vencimento"]}<br>";
	      $dados_hint .= "Valor: {$dados["valor_original"]}";
	      if ($hints && count($hints) > 4) {
	        $hints[] = "...";
	        break;
	      }
	      $hints[] = $dados_hint;
	    }
      return array("quantidade" => $quantidade, "hint" => implode("<hr>", $hints));
    } else {
    	return null;
    }
  }

  function buscaCobrancaCompetencia($cobranca_id) {
	$competencias = array();
    $query = "
      SELECT
        DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
      FROM
        tab_cobrancas_detalhes
      WHERE
        coa_id = $cobranca_id AND
        cod_competencia_inicio IS NOT NULL AND
        ass_id IS NOT NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $competencias[] = $this->formataData($dados["competencia"]);
    }
    if (!$competencias) {
      $query = "
        SELECT
          DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
        FROM
          tab_cobrancas_detalhes
        WHERE
          coa_id = $cobranca_id AND
          cod_competencia_inicio IS NOT NULL";
      $query = $this->bancodedados->query($query);
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $competencias[] = $this->formataData($dados["competencia"]);
      }
    }
    return implode(", ", array_unique($competencias));
  }

  function formataData($vencimento) {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    $vencimento = explode("/", $vencimento);
    return $meses[intval($vencimento[0])]."/".$vencimento[1];
  }

  function buscaAjusteAtivo($cliente_id) {
    $query = "
      SELECT
        tab_servicos_ajustes.saj_id AS id,
        DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS ajuste_inicio,
        DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS ajuste_termino,
        DATE_FORMAT(desbloqueio.saj_inicio, '%d/%m/%Y (%H:%i)') AS desbloqueio_inicio,
        DATE_FORMAT(desbloqueio.saj_termino, '%d/%m/%Y (%H:%i)') AS desbloqueio_termino,
        tab_servicos_ajustes_categorias.sac_id AS categoria_id,
        tab_servicos_ajustes_categorias.sac_nome AS categoria
      FROM
        tab_servicos_ajustes
        INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
        LEFT OUTER JOIN tab_servicos_ajustes desbloqueio ON (tab_servicos_ajustes.saj_id = desbloqueio.saj_pai AND desbloqueio.sas_id IN (5, 9))
      WHERE
        tab_servicos_ajustes.cli_id = $cliente_id AND
        tab_servicos_ajustes.sac_id IN (1, 3, 10) AND
        tab_servicos_ajustes.sas_id IN (5, 9) AND
        tab_servicos_ajustes.saj_inicio <= NOW()
      ORDER BY
        tab_servicos_ajustes.sac_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
     	$dados["categoria"] = preg_replace("/\\([^\\)]*\\)/", "", $dados["categoria"]);
      return $dados;
    }
  }

  function buscaMensalidadeBase($plano_id) {
    $query = "
      SELECT
        pla_mensalidade AS mensalidade
      FROM
        tab_planos
      WHERE
        pla_id = $plano_id
      ORDER BY
        pla_mensalidade DESC
      LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "mensalidade");
  }

  function buscaClienteDataAtivacao($cliente_id, $assinatura_id = null) {
    if ($assinatura_id) {
      $filtro = "ass_id = $assinatura_id";
    } else {
      $filtro = "ast_id = 2";
    }
    $query = "
      SELECT
        ass_id AS id,
        ass_migrada AS migrada_id,
        DATE_FORMAT(ass_data_ativacao, '%d/%m/%Y') AS data_ativacao
      FROM
        tab_assinaturas
      WHERE
        cli_id = $cliente_id AND
        ass_pai IS NULL AND
        $filtro
      ORDER BY
        ass_data_ativacao DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if (!$dados["migrada_id"]) {
        return $dados["data_ativacao"];
      } else {
        return $this->buscaClienteDataAtivacao($cliente_id, $dados["migrada_id"]);
      }
    }
  }

  function buscaClienteDataCancelamento($cliente_id) {
    $query = "
      SELECT
        DATE_FORMAT(ass_data_cancelamento, '%d/%m/%Y') AS data_cancelamento
      FROM
        tab_assinaturas
      WHERE
        cli_id = $cliente_id AND
        ass_pai IS NULL AND
        ast_id = 3
      ORDER BY
        ass_data_cancelamento DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["data_cancelamento"];
    }
  }

  function buscaClienteDataProspeccao($cliente_id) {
    $query = "
      SELECT
        DATE_FORMAT(ass_data_cadastro, '%d/%m/%Y') AS data_cadastro
      FROM
        tab_assinaturas
      WHERE
        cli_id = $cliente_id AND
        ass_pai IS NULL AND
        ast_id = 1
      ORDER BY
        ass_data_cadastro DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["data_cadastro"];
    }
  }

  function calculaMensalidade($condominio_id, $mensalidade, $servico_id) {
    $query = "
      SELECT
        tab_descontos_concessoes.des_id AS tipo,
        tab_descontos_concessoes.dec_mensalidade AS mensalidade
      FROM
        tab_descontos_concessoes,
        tab_descontos
      WHERE
        (tab_descontos_concessoes.des_id = tab_descontos.des_id) AND
        (tab_descontos_concessoes.con_id = '$condominio_id') AND
        (tab_descontos_concessoes.dcs_id = '5') AND
        ((tab_descontos.pls_id IS NULL) OR (tab_descontos.pls_id = '$servico_id'))";
    $query = $this->bancodedados->query($query);
    for ($i = 0; $i < $this->bancodedados->linhas($query); $i++) {
      $desconto_atual = $this->bancodedados->resultado($query, $i, "mensalidade");
      if (!$desconto_atual && $this->bancodedados->resultado($query, $i, "tipo") == 26) {
        $desconto_atual = "1390";
      }
      if ($desconto_atual <= 1) {
        $desconto_atual = $desconto_atual * $mensalidade;
      }
      $desconto_total += $desconto_atual;
    }
    $mensalidade = ($mensalidade >= $desconto_total) ? $mensalidade - $desconto_total : 0;
    return intval($mensalidade);
  }

  function buscaSituacaoServidor($condominio_id = null, $bloco_id = null) {
  	if (!$condominio_id && !$bloco_id) {
  		return "";
  	}
    $where = ($condominio_id) ? "tab_condominios_blocos.con_id = $condominio_id" : "tab_condominios_blocos.cob_id = $bloco_id";
    $query = "
      SELECT
        tab_servidores.ser_id AS servidor_id,
        tab_servidores.ser_nome AS servidor_nome,
        tab_servidores.ser_configuracao AS configuracao,
        CONCAT(interface_servidor.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip,
        DATE_FORMAT(tab_servidores.ser_data_hora_situacao, '%d/%m/%Y (%H:%i)') AS data_hora_situacao,
        tab_servidores_situacoes.seu_id AS situacao_id,
        tab_servidores_situacoes.seu_nome AS situacao_nome,
        tab_servidores_status.set_nome AS status_nome,
        tab_servidores_sistemas_operacionais.sso_nome AS sisteam_operacional_nome
      FROM
        tab_condominios_blocos
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
        INNER JOIN tab_servidores_situacoes ON (tab_servidores.seu_id = tab_servidores_situacoes.seu_id)
        INNER JOIN tab_servidores_interfaces interface_servidor ON (tab_servidores.sei_id = interface_servidor.sei_id)
        INNER JOIN tab_servidores_status ON (tab_servidores_status.set_id = tab_servidores.set_id)
        INNER JOIN tab_servidores_sistemas_operacionais ON (tab_servidores.sso_id = tab_servidores_sistemas_operacionais.sso_id)
      WHERE
        $where
      GROUP BY
        tab_servidores.ser_id
      ORDER BY
        tab_servidores.seu_id DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["configuracao"] = unserialize($dados["configuracao"]);
      $dados["duracao"] = $this->chamados->formataDuracao($dados["data_hora_situacao"]);
      $dados["hint"][] = "Servidor: {$dados["servidor_nome"]} ({$dados["ip"]})";
      $dados["hint"][] = "Sistema Operacional: {$dados["sisteam_operacional_nome"]}";
      $dados["hint"][] = "Status: {$dados["status_nome"]}";
      $dados["hint"] = implode("<br>", $dados["hint"]);
      $imagem = $dados["situacao_id"] == 1 ? "seta_para_cima" : "seta_para_baixo";
      $situacao = "<img src=\"imagens/$imagem.png\" width=\"13\" height=\"13\" border=\"0\" style=\"cursor:pointer\"".($this->permissoes["servidores"]["ver_dc"] ? " onclick=\"verDadosServidor({$dados["servidor_id"]})\"" : "")." onmouseover=\"hintShow('{$dados["hint"]}', 270)\" onmouseout=\"hintHide()\">";
    } else {
      $situacao = "--";
    }
    return $situacao;
  }

  function buscaNovaEtapa($categoria_id = null, $etapa_atual = null, $chamado_id = null, $editavel = true, $etapa_id = 5) {
    $conteudo = $this->carregaArquivo("chamados_sistema.chamado.nova_etapa.inc.php");
    $conteudo = str_replace("[tipo]", $this->geraSelect($this->chamados->buscaEtapasTipos($categoria_id, $etapa_atual, $chamado_id, $etapa_id), $etapa_id), $conteudo);
    $conteudo = str_replace("[editavel]", $editavel ? "" : "disabled", $conteudo);
    foreach (array(5, 20) as $etapa) {
      $conteudo = str_replace("[etapa_conteudo_{$etapa}_display]", ($etapa == $etapa_id) ? "" : "display:none", $conteudo);
    }
    if ($etapa_id == 20) {
      $conteudo = str_replace("[mac]", $this->geraSelect($this->buscaMACsEmpresa(), null, true), $conteudo);
    }
    return $conteudo;
  }
  
  function buscaMACsEmpresa() {
    $pontos_tecnica = unserialize(PUBLICACAO_PONTOS_TECNICA);
    foreach ($pontos_tecnica as $ponto) {
      $lista[$ponto] = $ponto;
    }
    return $lista;
  }

  #####################################
  ### Gera Resultado Busca Chamados ###
  #####################################

  function geraResultadoBuscaChamados($dados_filtro = array(), $dados_situacao = null, $chamado_id = null, $retorno_tela = true, $atualizacao = false) {
    // Campos
    $campos = array ("dados" => '<div style="float:right;margin-left:2px">'.$this->geraCheckBox(0, 0)."</div>Dados", "datas" => "Datas/Duração", "status" => "Status");
    // Filtros
	$join = array();
	$where = array();
    if ($dados_filtro["meus_chamados"] || $dados_filtro["aguardando_retorno"]) {
      // Chamados Abertos
      $where[] = "tab_chamados.chs_id = 1";
      // Sem Chamado Pai
      $where[] = "tab_chamados.cha_pai IS NULL";
      // Responsável - Setor
      $responsabilidades = $this->chamados->buscaResponsabilidadesUsuario($_SESSION["usuario"]);
    	$where_responsabilidades = array();
      $where_responsabilidades[] = "(tab_chamados.usu_id = {$_SESSION["usuario"]})";
    	foreach ($responsabilidades as $responsabilidade) {
        $where_responsabilidades[] = "(tab_chamados.ors_id = {$responsabilidade["setor_id"]} AND (IFNULL(tab_chamados.orf_id, 0) < {$responsabilidade["funcao_id"]} OR (tab_chamados.orf_id = {$responsabilidade["funcao_id"]} AND (tab_chamados.usu_id IS NULL OR (SELECT uss_id FROM tab_usuarios WHERE tab_usuarios.usu_id = tab_chamados.usu_id) = 2))))";
      }
      $where[] = "(".implode(" OR ", $where_responsabilidades).")";
      if (array_key_exists("aguardando_retorno", $dados_filtro) && $dados_filtro["aguardando_retorno"]) {
	      $where[] = "tab_chamados.cha_aguardando_retorno = 1";
      	$where[] = "tab_chamados.cha_datahora_retorno <= NOW()";
      } else {
      	$where[] = "tab_chamados.chc_id NOT IN (1052459, 1052463)";
      }
    } elseif ($dados_filtro["favoritos"]) {
      $where[] = "rel_cha_usu_ehfavorito.usu_id = {$_SESSION["usuario"]}";
      $join[] = "INNER JOIN rel_cha_usu_ehfavorito ON (tab_chamados.cha_id = rel_cha_usu_ehfavorito.cha_id)";
    } elseif ($dados_filtro["codigo"]) {
      $dados_filtro["codigo"] = preg_replace("/\-[0-9]*/", "", trim($dados_filtro["codigo"]));
      //$where[] = "tab_chamados.cha_codigo LIKE '{$dados_filtro["codigo"]}%'";
      $where[] = "tab_chamados.cha_id = '{$dados_filtro["codigo"]}'";
    } else {
      if ($dados_filtro["tipo"] == "1") {
        $where[] = "tab_chamados.cli_id IS NOT NULL";
      } elseif ($dados_filtro["tipo"] == "2") {
        $where[] = "tab_chamados.con_id IS NOT NULL";
      } elseif ($dados_filtro["tipo"] == "3") {
        $where[] = "tab_chamados.cli_id IS NULL";
        $where[] = "tab_chamados.con_id IS NULL";
      }
      if ($dados_filtro["cliente_id"]) {
        $where[] = "tab_chamados.cli_id = {$dados_filtro["cliente_id"]}";
      }
      if ($dados_filtro["conteudo"]) {
        $dados_filtro["conteudo"] = addslashes(trim($dados_filtro["conteudo"]));
        $join[] = "INNER JOIN tab_chamados_etapas ON (tab_chamados.cha_id = tab_chamados_etapas.cha_id)";
        $where[] = "MATCH(tab_chamados_etapas.che_conteudo) AGAINST ('\"{$dados_filtro["conteudo"]}\"')";
      }
      if (trim($dados_filtro["cliente"])) {
		$where[] = $this->geraQueryTexto($dados_filtro["cliente"], "tab_clientes.cli_nome");
      }
      if ($dados_filtro["condominio"]) {
        $where[] = "tab_condominios.con_id = {$dados_filtro["condominio"]}";
      }
      if ($dados_filtro["categoria"]) {
        $categorias = funcoes::mescla_arrays(array($dados_filtro["categoria"]), $this->chamados->buscaCategoriasAbaixo($dados_filtro["categoria"]));
        $categorias = implode(", ", $categorias);
        $where[] = "tab_chamados.chc_id IN ($categorias)";
      }
      if ($dados_filtro["responsavel"] && $dados_filtro["responsavel_tipo"] == 1) {
        $dados_filtro["responsavel"] = explode(",", $dados_filtro["responsavel"]);
        if ($dados_filtro["responsavel"][0] && !$dados_filtro["responsavel"][1] && !$dados_filtro["responsavel"][2]) {
          $setores = funcoes::mescla_arrays(array($dados_filtro["responsavel"][0]), $this->chamados->buscaSetoresAbaixo($dados_filtro["responsavel"][0]));
          $setores = implode(", ", $setores);
          $where[] = "tab_chamados.ors_id IN ($setores)";
        } else {
          if ($dados_filtro["responsavel"][0]) $where[] = "tab_chamados.ors_id = {$dados_filtro["responsavel"][0]}";
          if ($dados_filtro["responsavel"][1]) $where[] = "tab_chamados.orf_id = {$dados_filtro["responsavel"][1]}";
          if ($dados_filtro["responsavel"][2]) $where[] = "tab_chamados.usu_id = {$dados_filtro["responsavel"][2]}";
        }
      }
      if ($dados_filtro["responsavel"] && in_array($dados_filtro["responsavel_tipo"], array(2, 3, 4))) {
        $dados_filtro["responsavel"] = explode(",", $dados_filtro["responsavel"]);
        $join[] = "INNER JOIN tab_chamados_etapas ON (tab_chamados.cha_id = tab_chamados_etapas.cha_id)";
        if ($dados_filtro["responsavel"][2]) {
          $where[] = "tab_chamados_etapas.usu_id = {$dados_filtro["responsavel"][2]}";
        } else {
          $join[] = "INNER JOIN rel_usu_orf_ors_exerce ON (tab_chamados_etapas.usu_id = rel_usu_orf_ors_exerce.usu_id)";
          if ($dados_filtro["responsavel"][0]) {
            $where[] = "rel_usu_orf_ors_exerce.ors_id = {$dados_filtro["responsavel"][0]}";
          }
          if ($dados_filtro["responsavel"][1]) {
            $where[] = "rel_usu_orf_ors_exerce.orf_id = {$dados_filtro["responsavel"][1]}";
          }
        }
        if ($dados_filtro["responsavel_tipo"] == 3) {
          // Abertura
          $where[] = "tab_chamados_etapas.cet_id = 1";
        }
        if ($dados_filtro["responsavel_tipo"] == 4) {
          // Fechamento
          $where[] = "tab_chamados_etapas.cet_id = 6";
        }
      }
      if ($dados_filtro["responsavel"] && $dados_filtro["responsavel_tipo"] == 5) {
        $dados_filtro["responsavel"] = explode(",", $dados_filtro["responsavel"]);
        if ($dados_filtro["responsavel"][0] && !$dados_filtro["responsavel"][1] && !$dados_filtro["responsavel"][2]) {
          $setores = funcoes::mescla_arrays(array($dados_filtro["responsavel"][0]), $this->chamados->buscaSetoresAbaixo($dados_filtro["responsavel"][0]));
          $setores = implode(", ", $setores);
          $where[] = "tab_chamados.ors_id IN ($setores)";
        } else {
          if ($dados_filtro["responsavel"][0]) $where[] = "tab_chamados.ors_id = {$dados_filtro["responsavel"][0]}";
          if ($dados_filtro["responsavel"][1]) $where[] = "tab_chamados.orf_id = {$dados_filtro["responsavel"][1]}";
        }
        $where[] = "tab_chamados.usu_id IS NULL";
      }
      
      // Encaminhamento
      if ($dados_filtro["responsavel"] && $dados_filtro["responsavel_tipo"] == 6 && $dados_filtro["data_inicio"] && $dados_filtro["data_termino"] && $dados_filtro["data_tipo"] == 6) {
      	
        $join[] = "INNER JOIN tab_chamados_etapas ON (tab_chamados.cha_id = tab_chamados_etapas.cha_id)";
        
				$where[] = "tab_chamados_etapas.cet_id = 3";
        
      	$dados_filtro["responsavel"] = explode(",", $dados_filtro["responsavel"]);
      	
        if ($dados_filtro["responsavel"][2]) {
        	
          $where[] = "tab_chamados_etapas.che_conteudo REGEXP '\"usuario_id\";s:".strlen($dados_filtro["responsavel"][2]).":\"{$dados_filtro["responsavel"][2]}\"'";
        	
        } else {
        	
          $where[] = "0";
        	        	
        }
        
        $data = substr($dados_filtro["data_inicio"], 6, 4)."-".substr($dados_filtro["data_inicio"], 3, 2)."-".substr($dados_filtro["data_inicio"], 0, 2)." 00:00:00";
        $where[] = "tab_chamados_etapas.che_datahora >= '$data'";
        
        $data = substr($dados_filtro["data_termino"], 6, 4)."-".substr($dados_filtro["data_termino"], 3, 2)."-".substr($dados_filtro["data_termino"], 0, 2)." 23:59:59";
        $where[] = "tab_chamados_etapas.che_datahora <= '$data'";
        
      }
      
      if ($dados_filtro["data_inicio"] && $dados_filtro["data_tipo"] == 1) {
        $data = substr($dados_filtro["data_inicio"], 6, 4)."-".substr($dados_filtro["data_inicio"], 3, 2)."-".substr($dados_filtro["data_inicio"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_abertura >= '$data'";
      }
      if ($dados_filtro["data_termino"] && $dados_filtro["data_tipo"] == 1) {
        $data = substr($dados_filtro["data_termino"], 6, 4)."-".substr($dados_filtro["data_termino"], 3, 2)."-".substr($dados_filtro["data_termino"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_abertura <= '$data 23:59:59'";
      }
      if ($dados_filtro["data_inicio"] && $dados_filtro["data_tipo"] == 2) {
        $data = substr($dados_filtro["data_inicio"], 6, 4)."-".substr($dados_filtro["data_inicio"], 3, 2)."-".substr($dados_filtro["data_inicio"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_fechamento >= '$data'";
      }
      if ($dados_filtro["data_termino"] && $dados_filtro["data_tipo"] == 2) {
        $data = substr($dados_filtro["data_termino"], 6, 4)."-".substr($dados_filtro["data_termino"], 3, 2)."-".substr($dados_filtro["data_termino"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_fechamento <= '$data 23:59:59'";
      }
      if ($dados_filtro["data_inicio"] && $dados_filtro["data_tipo"] == 3) {
        $data = substr($dados_filtro["data_inicio"], 6, 4)."-".substr($dados_filtro["data_inicio"], 3, 2)."-".substr($dados_filtro["data_inicio"], 0, 2);
        $where[] = "tab_chamados_etapas.che_datahora >= '$data'";
        $join[] = "INNER JOIN tab_chamados_etapas ON (tab_chamados.cha_id = tab_chamados_etapas.cha_id)";
      }
      if ($dados_filtro["data_termino"] && $dados_filtro["data_tipo"] == 3) {
        $data = substr($dados_filtro["data_termino"], 6, 4)."-".substr($dados_filtro["data_termino"], 3, 2)."-".substr($dados_filtro["data_termino"], 0, 2);
        $where[] = "tab_chamados_etapas.che_datahora <= '$data 23:59:59'";
        $join[] = "INNER JOIN tab_chamados_etapas ON (tab_chamados.cha_id = tab_chamados_etapas.cha_id)";
      }
      if ($dados_filtro["prioridade"]) {
        $where[] = "tab_chamados.chp_id = {$dados_filtro["prioridade"]}";
      }
      if ($dados_filtro["status"]) {
        $where[] = "tab_chamados.chs_id = {$dados_filtro["status"]}";
      }
    }
    if (!$this->permissoes["chamados"]["buscar_plus"]) {
      $where[] = "(tab_chamados.cha_id IN (SELECT cha_id FROM tab_chamados_etapas WHERE usu_id = {$_SESSION["usuario"]}) OR tab_chamados.usu_id = {$_SESSION["usuario"]})";
    }
	
	if ($_SESSION["origem"]) {
        $where[] = "tab_clientes.clo_id = {$_SESSION["origem"]}";
	}
	
    $where = count($where) ? implode(" AND\n        ", array_unique($where)) : "1";
    $join = implode("\n        ", array_unique($join));
    $dados_filtro["pagina"] = $dados_filtro["pagina"] ? $dados_filtro["pagina"] : 1;
    $limite_itens = PAG_ITENS_SISTEMA_CHAMADOS;
    $limite_inicio = ($dados_filtro["pagina"] - 1) * $limite_itens;
    // Consulta
    $query = "
      SELECT SQL_CALC_FOUND_ROWS
        tab_chamados.cha_id AS chamado_id,
        tab_chamados.cha_codigo AS codigo,
        tab_chamados.cha_solicitante AS solicitante,
        tab_chamados.chc_id AS categoria_id,
        tab_chamados.ors_id AS setor_id,
        tab_chamados.orf_id AS funcao_id,
        tab_chamados.usu_id AS usuario_id,
        tab_chamados.cli_id AS cliente_id,
        tab_chamados.con_id AS condominio_id,
        tab_chamados.cha_andamento AS andamento,
        tab_chamados.chi_id AS situacao_id,
        tab_chamados.cha_aguardando_retorno AS aguardando_retorno,
        tab_chamados.cha_visualizado AS visualizado,
        tab_clientes.cli_nome AS cliente,
        tab_clientes.clt_id AS pessoa_tipo_id,
        tab_condominios.con_nome AS condominio,
        tab_condominios.con_id AS cliente_condominio_id,
        tab_condominios.cnt_id AS condominio_tipo_id,
        DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
        DATE_FORMAT(tab_chamados.cha_datahora_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
        DATE_FORMAT(tab_chamados.cha_datahora_encaminhamento, '%d/%m/%Y (%H:%i)') AS encaminhamento,
        tab_chamados_status.chs_id AS status_id,
        tab_chamados_status.chs_nome AS status_nome,
        tab_chamados_prioridades.chp_id AS prioridade_id,
        tab_chamados_prioridades.chp_nome AS prioridade
      FROM
        tab_chamados
        LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (IF(tab_chamados.cli_id IS NOT NULL, tab_clientes.con_id, tab_chamados.con_id) = tab_condominios.con_id)
        INNER JOIN tab_chamados_status ON (tab_chamados_status.chs_id = tab_chamados.chs_id)
        INNER JOIN tab_chamados_prioridades ON (tab_chamados_prioridades.chp_id = tab_chamados.chp_id)
        $join
      WHERE
        $where
      GROUP BY
        chamado_id
      ORDER BY
        tab_chamados_status.chs_id,
        tab_chamados.cha_datahora_encaminhamento
      LIMIT
        $limite_inicio,
        $limite_itens";
        
    $query = $this->bancodedados->query($query);
    $itens["linhas"] = $this->bancodedados->linhasTotal($query);
    if ($itens["linhas"] && $dados_filtro["pagina"] > ceil($itens["linhas"] / PAG_ITENS_SISTEMA_CHAMADOS)) {
      $dados_filtro["pagina"] = ceil($itens["linhas"] / PAG_ITENS_SISTEMA_CHAMADOS);
      return $this->geraResultadoBuscaChamados($dados_filtro, $dados_situacao, $chamado_id, $retorno_tela, $atualizacao);
    } else if (!$itens["linhas"] && $dados_filtro["pagina"] != 1) {
      $dados_filtro["pagina"] = 1;
      return $this->geraResultadoBuscaChamados($dados_filtro, $dados_situacao, $chamado_id, $retorno_tela, $atualizacao);
    }
    $i = 1;
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categoria = $this->chamados->buscaDadosCategoria($dados["categoria_id"]);
      $dados["categoria"] = $categoria["nome"];
      if ($dados["status_id"] == 1) {
        $dados["status"] = $this->chamados->buscaSituacao($dados["situacao_id"], $dados["status_nome"]).$this->chamados->buscaPrioridade($dados["prioridade_id"]).$this->chamados->buscaAndamento($dados["andamento"]);
      } else {
        $dados["status"] = $dados["status_nome"].$this->chamados->buscaAndamento($dados["andamento"]);
      }
      $responsavel = array();
      if ($dados["setor_id"]) $responsavel[] = $this->chamados->buscaSetor($dados["setor_id"]);
      if ($dados["funcao_id"]) $responsavel[] = $this->chamados->buscaFuncao($dados["funcao_id"]);
      if ($dados["usuario_id"]) $responsavel[] = $this->chamados->buscaUsuario($dados["usuario_id"]);
      $dados["responsavel"] = implode(" » ", $responsavel);
      $dados["duracao"] = $this->chamados->formataDuracao($dados["abertura"], $dados["fechamento"]);
      $dados["duracao_setor"] = $this->chamados->formataDuracao($dados["encaminhamento"], $dados["fechamento"]);
      if ($dados["abertura"]) $dados["datas"][] = "A: {$dados["abertura"]}";
      if ($dados["status_id"] == 1 && $dados["situacao_id"] != 1) $dados["datas"][] = "E: {$dados["encaminhamento"]}";
      if ($dados["fechamento"]) $dados["datas"][] = "F: {$dados["fechamento"]}";
      $dados["datas"][] = "<span style=\"color:#232323\">({$dados["duracao"]})</span>";
      $dados["datas"] = '<div nowrap>'.implode('<br>', $dados["datas"]).'</div>';
      // Dados Chamado
      $dados["dados"][] = "<b>Código:</b> {$dados["codigo"]}";
      if ($dados["cliente_id"]) {
        $dados["cliente_formatado"] = strlen($dados["cliente"]) > 35 ? substr($dados["cliente"], 0, 35)."..." : $dados["cliente"];
        $dados["dados"][] = "<b>Cliente:</b> {$dados["cliente_formatado"]}";
      } elseif ($dados["condominio_id"]) {
        $dados["condominio"] = strlen($dados["condominio"]) > 35 ? substr($dados["condominio"], 0, 35)."..." : $dados["condominio"];
        $dados["dados"][] = "<b>Condomínio:</b> {$dados["condominio"]}";
      } else {
        $dados["dados"][] = "<b>Solicitante:</b> {$dados["solicitante"]}";
      }
      $dados["categoria_formatado"] = strlen($dados["categoria"]) > 35 ? substr($dados["categoria"], 0, 35)."..." : $dados["categoria"];
      $dados["dados"][] = '<div style="float:right;margin-left:2px">'.$this->geraCheckBox($dados["chamado_id"], $i).'</div>'."<b>Categoria:</b> {$dados["categoria_formatado"]}";
      // Icone Wave Garantia
      if ($dados["cliente_id"]) {
        $dados["dados"][1] = '<div style="float:right;margin-left:2px">'.$this->chamados->buscaWaveGarantia($dados["cliente_id"]).'</div>'.$dados["dados"][1];
      }
      // Icone Cliente
      $hint = $this->chamados->formataHintChamado($dados);
      if ($dados["cliente_id"]) {
        if ($dados["condominio_tipo_id"] == 1) {
          $cliente_tipo = "residencial";
        } elseif ($dados["condominio_tipo_id"] == 2) {
          $cliente_tipo = "comercial";
        } else {
          if ($dados["pessoa_tipo_id"] == 1) {
            $cliente_tipo = "residencial";
          } else {
            $cliente_tipo = "comercial";
          }
        }
        $dados["dados"][1] = '<img src="imagens/cliente_'.$cliente_tipo.'.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      } elseif ($dados["condominio_id"]) {
        $dados["dados"][1] = '<img src="imagens/condominio.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      } else {
        $dados["dados"][1] = '<img src="imagens/empresa.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      }
      $dados["dados"] = implode("<br>", $dados["dados"]);
      // Icone Visualização
      $dados["dados"] = "<div id=\"chamados_busca_visualizacao_{$dados["chamado_id"]}\" style=\"float:right;margin-left:2px\">".$this->chamados->buscaIconeVisualizacoes($this->instancia, $dados["chamado_id"])."</div>".$dados["dados"];
      // Icone Aguardando Retorno
      if ($dados["aguardando_retorno"]) {
      	$dados["dados"] = '<img src="imagens/aguardando_retorno.png" width="11" height="14" border="0" style="float:right;margin-left:2px" title="Aguardando Retorno">'.$dados["dados"];
      }
      // Icone Favoritos
      $dados["dados"] = $this->chamados->buscaIconeFavoritos($dados["chamado_id"], "").$dados["dados"];
      // Link
      $dados["link"] = "javascript:verChamado('{$dados["chamado_id"]}', '{$dados["cliente_id"]}', '{$dados["cliente_condominio_id"]}', '{$dados["condominio_id"]}', 1, event)";
      $dados["id"] = "chamados_{$dados["chamado_id"]}";
      if (!$dados["visualizado"]) {
      	$dados["cor_linha"] = "#D7D7D7";
      }
      $itens["dados"][] = $dados;
      $i++;
    }
    $chamado_atual = $chamado_id ? "chamados_{$chamado_id}" : "";
    $listagem = controle::instanciaObjeto("listagem", $campos, $itens["dados"], 0, false, array(0, 110, 50), $chamado_atual);

    if ($retorno_tela) {
      if (!array_key_exists("codigo", $dados_filtro) || !$dados_filtro["codigo"] || (array_key_exists("codigo", $dados_filtro) && $dados_filtro["codigo"] && count($itens["dados"]) > 1)) {
        // Atualiza Listagem
        print "document.getElementById('busca_status').innerHTML = '".$this->geraBuscaStatus(count($itens["dados"]), $itens["linhas"], $dados_filtro["pagina"])."';";
        print "pagina_busca_atual = {$dados_filtro["pagina"]};";
        print "document.getElementById('busca_chamados_formulario').style.display = 'none';";
        print "document.getElementById('busca_resultado').style.display = ehNetscape() ? 'table-row-group' : 'block';";
        print "if (document.getElementById('busca_resultado_listagem')) document.getElementById('busca_resultado_listagem').innerHTML = '".addslashes(preg_replace("/[ \r\n\t]+/", " ", $listagem->conteudo))."';";
        if (!$atualizacao) {
          print "if (document.getElementById('busca_resultado_listagem')) document.getElementById('busca_resultado_listagem').scrollTop = 0;";
        }
      } else {
        if ($itens["dados"][0]["chamado_id"]) {
          print "limparFormulario('buscarChamadosIr');";
          if ($itens["dados"][0]["cliente_id"]) {
            // Gera Dados Resumo
            $this->geraDadosResumoCliente($itens["dados"][0]["cliente_id"]);
            // Gera Listagem Histórico
            $this->geraResultadoHistorico("historico_geral", $itens["dados"][0]["cliente_id"], $itens["dados"][0]["cliente_condominio_id"]);
          } elseif ($itens["dados"][0]["condominio_id"]) {
            // Gera Dados Resumo
            $this->geraDadosResumoCondominio($itens["dados"][0]["condominio_id"]);
            // Gera Listagem Histórico
            if (!$dados_situacao["chamado_alterado"]) {
              $this->geraResultadoHistorico("historico_geral", null, null, $itens["dados"][0]["condominio_id"]);
            }
          } else {
            $this->geraDadosChamadoInterno();
          }
          // Atualiza Registro Atual
          $this->atualizaRegistroAtual($itens["dados"][0]["cliente_id"], $itens["dados"][0]["cliente_id"] ? $itens["dados"][0]["cliente_condominio_id"] : $itens["dados"][0]["condominio_id"], $itens["dados"][0]["chamado_id"]);
          // Chamado
          $this->chamadoVer($itens["dados"][0]["chamado_id"]);
        } else {
          print "alert('Chamado não encontrado!');";
        }
      }
    } else {
      $conteudo["listagem"] = $listagem->conteudo;
      $conteudo["busca_status"] = $this->geraBuscaStatus(count($itens["dados"]), $itens["linhas"], $dados_filtro["pagina"]);
      return $conteudo;
    }
  }

  function geraCheckBox($chamado_id, $ordem) {
    return '<input type="checkbox" id="seleciona_chamado_'.$ordem.'" value="'.$chamado_id.'" onclick="selecionaChamado(this, '.$ordem.')" style="margin:0px;height:10px;width:10px;margin-top:1px;margin-right:1px">';
  }

  function geraBuscaStatus($linhas, $total, $pagina) {
    $inicial = (($pagina - 1) * PAG_ITENS_SISTEMA_CHAMADOS) + 1;
    $final = $inicial + PAG_ITENS_SISTEMA_CHAMADOS - 1 <= $total ? $inicial + PAG_ITENS_SISTEMA_CHAMADOS - 1 : $total;
    $conteudo = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr align=\"center\" class=\"txtBranco11\" style=\"font-size:10px\">";
    $conteudo .= "<td align=\"left\" width=\"267\">&nbsp;Registros encontrados: ".number_format($total, null, null, ".").($total ? " (Exibindo ".number_format($inicial, null, null, ".")." a ".number_format($final, null, null, ".").")" : "")."</td>";
    $conteudo .= "<td align=\"center\" width=\"20\"><img src=\"imagens/imprimir_pequeno.png\" title=\"Imprimir\" width=\"10\" height=\"10\" border=\"0\" onclick=\"imprimeChamados()\" style=\"cursor:pointer\"></td>";
    $conteudo .= "<td align=\"right\">".$this->geraPaginacao($total, $pagina)."&nbsp;</td>";
    $conteudo .= "</tr></table>";
    return $conteudo;
  }

  function geraPaginacao($total, $pagina) {
    $paginas_total = ceil($total / PAG_ITENS_SISTEMA_CHAMADOS);
    $paginas_exibir = 5;
    if ($paginas_total > 1) {
      $pagina_inicial = $this->buscaPaginaInicial($pagina, $paginas_total, $paginas_exibir);
      $pagina_final = ($pagina_inicial + $paginas_exibir - 1 < $paginas_total) ? $pagina_inicial + $paginas_exibir - 1 : $paginas_total;
      $paginas_conteudo[] = '<img title="Página Anterior [Ctrl + PageUp]" src="imagens/seta_left.gif" width="4" height="7"'.($pagina == 1 ? '' : ' id="pagina_anterior" style="cursor:pointer" onclick="mudaPagina(0, -1)"').'>';
      for ($i = $pagina_inicial; $i <= $pagina_final; $i++) {
        if ($i == $pagina) {
          $paginas_conteudo[] = "<span style=\"color:#232323;font-weight:bold;margin-bottom:3px\">$i</span>";
        } else {
          $paginas_conteudo[] = "<span style=\"cursor:pointer\" onclick=\"mudaPagina($i, 0)\">$i</span>";
        }
      }
      $paginas_conteudo[] = '<img title="Próxima Página [Ctrl + PageDown]" src="imagens/seta_right.gif" width="4" height="7"'.($pagina == $paginas_total ? '' : ' id="proxima_pagina" style="cursor:pointer" onclick="mudaPagina(0, +1)"').'>';
      $paginas_conteudo = implode("&nbsp;|&nbsp;", $paginas_conteudo);
      $paginas_conteudo = '<img title="Primeira Página" src="imagens/seta_left_left.gif" width="7" height="7"'.($pagina == 1 ? '' : ' style="cursor:pointer" onclick="mudaPagina(1, 0)"').'> '.$paginas_conteudo.' <img title="Última Página" src="imagens/seta_right_right.gif" width="7" height="7"'.($pagina == $paginas_total ? '' : ' style="cursor:pointer" onclick="mudaPagina('.$paginas_total.', 0)"').'>';
      return $paginas_conteudo;
    } else {
      return "";
    }
  }

  function buscaPaginaInicial($pagina, $paginas_total, $paginas_exibir) {
    if ($pagina <= ceil($paginas_exibir / 2) || $paginas_total <= $paginas_exibir) {
      return 1;
    } else {
      if ($pagina < ($paginas_total - floor($paginas_exibir / 2))) {
        return $pagina - floor($paginas_exibir / 2);
      } else {
        return $paginas_total - floor($paginas_exibir / 2) - 2;
      }
    }
  }

  function atualizaBuscaChamados($instancia) {
    $tela_atual = $this->buscaTelaAtual($instancia, 0);
    if ($tela_atual["aba_busca"] == "meus_chamados") {
      $this->geraResultadoMeusChamados(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, true);
    } elseif ($tela_atual["aba_busca"] == "favoritos") {
      $this->geraResultadoFavoritos(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, true);
    } elseif ($tela_atual["aba_busca"] == "aguardando_retorno") {
      $this->geraResultadoAguardandoRetorno(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, true);
    } elseif ($tela_atual["aba_busca"] == "chamados" && $tela_atual["busca_chamados"]) {
      $this->geraResultadoBuscaChamados($tela_atual["busca_chamados"], null, $tela_atual["chamado_id"], true, true);
    }
    if ($tela_atual["painel_historico"] == "true") {
      $this->geraResultadoHistorico($tela_atual["aba_historico"], $tela_atual["cliente_id"], $tela_atual["cliente_condominio_id"], $tela_atual["condominio_id"], $tela_atual["chamado_id"], true);
    }
  }

  ####################################
  ### Gera Resultado Meus Chamados ###
  ####################################

  function geraResultadoMeusChamados($dados_situacao = null, $pagina = 1, $chamado_id = null, $retorno_tela = true, $atualizacao = false) {
    $dados_filtro["meus_chamados"] = true;
    $dados_filtro["pagina"] = $pagina;
    return $this->geraResultadoBuscaChamados($dados_filtro, $dados_situacao, $chamado_id, $retorno_tela, $atualizacao);
  }

  ################################
  ### Gera Resultado Favoritos ###
  ################################

  function geraResultadoFavoritos($dados_situacao = null, $pagina = 1, $chamado_id = null, $retorno_tela = true, $atualizacao = false) {
    $dados_filtro["favoritos"] = true;
    $dados_filtro["pagina"] = $pagina;
    return $this->geraResultadoBuscaChamados($dados_filtro, $dados_situacao, $chamado_id, $retorno_tela, $atualizacao);
  }

  #########################################
  ### Gera Resultado Aguardando Retorno ###
  #########################################

  function geraResultadoAguardandoRetorno($dados_situacao = null, $pagina = 1, $chamado_id = null, $retorno_tela = true, $atualizacao = false) {
    $dados_filtro["aguardando_retorno"] = true;
    $dados_filtro["pagina"] = $pagina;
    return $this->geraResultadoBuscaChamados($dados_filtro, $dados_situacao, $chamado_id, $retorno_tela, $atualizacao);
  }
  
  #####################################
  ### Gera Resultado Busca Clientes ###
  #####################################

  function geraResultadoBuscaClientes($dados_filtro, $dados_situacao = null, $cliente_id = null, $retorno_tela = true, $atualizacao = false) {
    // Campos
    $campos = array ("dados" => "Dados", "status" => "Status");
    $where = array();
    $join = array();
    // Filtros
    $dados_filtro["id"] = preg_replace("/[^0-9]/", "", $dados_filtro["id"]);
    if ($dados_filtro["id"]) {
      $where[] = "tab_clientes.cli_id = {$dados_filtro["id"]}";
    }
    $dados_filtro["nome"] = addslashes(trim($dados_filtro["nome"]));
    if (trim($dados_filtro["nome"])) {
 		$where[] = $this->geraQueryTexto($dados_filtro["nome"], "tab_clientes.cli_nome");
    }
    if ($dados_filtro["condominio"]) {
      $where[] = "tab_clientes.con_id = {$dados_filtro["condominio"]}";
    }
    if ($dados_filtro["bloco"]) {
      $where[] = "tab_clientes.cob_id = {$dados_filtro["bloco"]}";
    }
    $dados_filtro["complemento"] = addslashes(trim($dados_filtro["complemento"]));
    if ($dados_filtro["complemento"]) {
      $where[] = "tab_clientes.cli_complemento LIKE '_{$dados_filtro["complemento"]}'";
    }
    if ($dados_filtro["tipo"]) {
      $where[] = "tab_clientes.clt_id = {$dados_filtro["tipo"]}";
    }
    $dados_filtro["cpf_cnpj"] = trim($dados_filtro["cpf_cnpj"]);
    if ($dados_filtro["cpf_cnpj"]) {
      $where[] = "tab_clientes.cli_cpf_cnpj = '{$dados_filtro["cpf_cnpj"]}'";
    }
    if ($dados_filtro["email"]) {
      $dados_filtro["email"] = explode("@", $dados_filtro["email"]);
      $where[] = "tab_clientes_contatos_emails.ccm_usuario = '{$dados_filtro["email"][0]}'";
      $where[] = "tab_clientes_contatos_emails.ccm_dominio = '{$dados_filtro["email"][1]}'";
      $join[] = "INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)";
    }
    if ($dados_filtro["telefone"]) {
      $dados_filtro["telefone"] = explode(" ", $dados_filtro["telefone"]);
      $dados_filtro["telefone"][0] = preg_replace("/[^0-9]/", "", $dados_filtro["telefone"][0]);
      $where[] = "tab_clientes_contatos_telefones.ccl_ddd = '{$dados_filtro["telefone"][0]}'";
      $where[] = "tab_clientes_contatos_telefones.ccl_numero = '{$dados_filtro["telefone"][1]}'";
      $join[] = "INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)";
    }
    if ($dados_filtro["status"]) {
      $where[] = "tab_clientes.cls_id = {$dados_filtro["status"]}";
    } else {
      $where[] = "tab_clientes.cls_id IS NOT NULL";
    }
	
	if ($_SESSION["origem"]) {
        $where[] = "tab_clientes.clo_id = {$_SESSION["origem"]}";
	}

    $where = implode(" AND\n        ", $where);
    $join = implode("\n        ", array_unique($join));

    $dados_filtro["pagina"] = $dados_filtro["pagina"] ? $dados_filtro["pagina"] : 1;
    $limite_itens = PAG_ITENS_SISTEMA_CHAMADOS;
    $limite_inicio = ($dados_filtro["pagina"] - 1) * $limite_itens;
    $query = "
      SELECT SQL_CALC_FOUND_ROWS DISTINCT
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS nome,
        tab_clientes.clt_id AS cliente_tipo_id,
        IF(tab_clientes.con_id IS NULL, tab_clientes.cep_id, tab_condominios.cep_id) AS cep,
        IF(tab_clientes.con_id IS NULL, tab_clientes.cli_numero, tab_condominios.con_numero) AS numero,
        tab_condominios.con_nome AS condominio,
        tab_condominios.con_id AS cliente_condominio_id,
        tab_condominios.cnt_id AS condominio_tipo_id,
        tab_condominios_blocos.cob_nome AS bloco,
        SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo,
        SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
        tab_clientes_status.cls_nome AS status
      FROM
        tab_clientes
        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        $join
      WHERE
        tab_clientes.cli_data_cadastro IS NOT NULL AND
        $where
      ORDER BY
        tab_clientes.cli_nome ASC
      LIMIT
        $limite_inicio,
        $limite_itens";
    $query = $this->bancodedados->query($query);
    $itens["linhas"] = $this->bancodedados->linhasTotal($query);
    if (($this->permissoes["clientes"]["buscar_plus"] && ($dados_filtro["condominio"] || $itens["linhas"] <= BUS_ITENS)) || $this->permissoes["clientes"]["buscar_plus_plus"] || $itens["linhas"] == 1) {
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $dados["link"] = "javascript:verCliente('{$dados["cliente_id"]}', '{$dados["cliente_condominio_id"]}')";
        if ($dados["cliente_condominio_id"]) {
          if ($dados["complemento"]) {
            $dados["complemento"] = $this->chamados->buscaComplementoTipos($dados["complemento_tipo"])." ".$dados["complemento"];
            if ($dados["bloco"] && $dados["bloco"] != "Único") {
              $dados["complemento"] .= " / {$dados["bloco"]}";
            }
          }
          $dados["endereco"] = "{$dados["condominio"]}, {$dados["complemento"]}";
        } else {
          $dados["endereco"] = $this->chamados->buscaEndereco($dados["cep"], $dados["numero"]);
        }
        $dados["dados"][] = "<b>Nome:</b> {$dados["nome"]}";
        $dados["dados"][] = "<b>Endereço:</b> ".(strlen($dados["endereco"]) > 50 ? substr($dados["endereco"], 0, 47)."..." : $dados["endereco"]);
        $dados["dados"] = implode("<br>", $dados["dados"]);
        if ($dados["condominio_tipo_id"] == 1) {
          $cliente_tipo = "residencial";
          $icone_hint = "Cliente Residencial";
        } elseif ($dados["condominio_tipo_id"] == 2) {
          $cliente_tipo = "comercial";
          $icone_hint = "Cliente Comercial";
        } else {
          if ($dados["cliente_tipo_id"] == 1) {
            $cliente_tipo = "residencial";
            $icone_hint = "Cliente Residencial";
          } else {
            $cliente_tipo = "comercial";
            $icone_hint = "Cliente Comercial";
          }
        }
        $dados["dados"] = '<div style="float:right;margin-left:2px">'.$this->chamados->buscaWaveGarantia($dados["cliente_id"]).'</div>'.$dados["dados"];
        $dados["dados"] = '<img src="imagens/cliente_'.$cliente_tipo.'.png" width="13" height="13" border="0" style="float:right;margin-left:2px" title="'.$icone_hint.'">'.$dados["dados"];
        $dados["id"] = "clientes_{$dados["cliente_id"]}";
        $itens["dados"][] = $dados;
      }
      $cliente_atual = $cliente_id ? "clientes_$cliente_id" : "";
      $listagem = controle::instanciaObjeto("listagem", $campos, $itens["dados"], 0, false, array(0, 70), $cliente_atual);
      if ($retorno_tela) {
        if (trim($dados_filtro["id"])) {
          if ($itens["dados"][0]["cliente_id"]) {
            print "limparFormulario('buscarClientesIr');";
            $this->verCliente($itens["dados"][0]["cliente_id"], $itens["dados"][0]["cliente_condominio_id"]);
          } else {
            print "alert('Cliente não encontrado!');";
          }
        } else {
          print "document.getElementById('busca_status').innerHTML = '".$this->geraBuscaStatus(count($itens["dados"]), $itens["linhas"], $dados_filtro["pagina"])."';";
          print "pagina_busca_atual = {$dados_filtro["pagina"]};";
          print "document.getElementById('busca_clientes_formulario').style.display = 'none';";
          print "document.getElementById('busca_resultado').style.display = ehNetscape() ? 'table-row-group' : 'block';";
          print "if (document.getElementById('busca_resultado_listagem')) document.getElementById('busca_resultado_listagem').innerHTML = '".addslashes($listagem->conteudo)."';";
          if (!$atualizacao) {
            print "if (document.getElementById('busca_resultado_listagem')) document.getElementById('busca_resultado_listagem').scrollTop = 0;";
          }
        }
      } else {
        $conteudo["listagem"] = $listagem->conteudo;
        $conteudo["busca_status"] = $this->geraBuscaStatus(count($itens["dados"]), $itens["linhas"], $dados_filtro["pagina"]);
        return $conteudo;
      }
    } else {
      if ($retorno_tela) {
        print "alert('Atenção!\\n\\nMuitos registros foram encontrados. Especifique melhor\\na sua busca!');";
      } else {
        $conteudo["listagem"] = "<div algin=\"center\"><b>Atenção!</b><br>Muitos registros foram encontrados, especifique melhor a sua busca!</div>";
        $conteudo["busca_status"] = "";
      }
    }
  }

  function verCliente($cliente_id, $cliente_condominio_id) {
    // Gera Dados Resumo
    $this->geraDadosResumoCliente($cliente_id);
    // Limpa Chamado Atual
    print "document.getElementById('dados_chamado').innerHTML = '';";
    // Gera Listagem Histórico
    $this->geraResultadoHistorico("historico_geral", $cliente_id, $cliente_condominio_id);
    // Atualiza Registro Atual
    $this->atualizaRegistroAtual($cliente_id, $cliente_condominio_id);
  }

  ########################################
  ### Gera Resultado Busca Condomínios ###
  ########################################

  function geraResultadoBuscaCondominios($dados_filtro, $dados_situacao = null, $condominio_id = null, $retorno_tela = true, $atualizacao = false) {
    // Campos
    $campos = array ("dados" => "Dados", "status" => "Status");
    // Filtros
	$where = array();
    if (trim($dados_filtro["nome"])) {
 		$where[] = $this->geraQueryTexto($dados_filtro["nome"], "tab_condominios.con_nome");
    }
    if ($dados_filtro["cidade"]) {
      $where[] = "tab_cidades.cid_id = '{$dados_filtro["cidade"]}'";
    }
    if ($dados_filtro["bairro"]) {
      $where[] = "tab_bairros.bai_id = '{$dados_filtro["bairro"]}'";
    }
    if ($dados_filtro["logradouro"]) {
      $where[] = "tab_logradouros.log_nome LIKE '%{$dados_filtro["logradouro"]}%'";
    }
    if ($dados_filtro["data_cadastro_inicio"]) {
      $dados_filtro["data_cadastro_inicio"] = substr($dados_filtro["data_cadastro_inicio"], 6, 4)."-".substr($dados_filtro["data_cadastro_inicio"], 3, 2)."-".substr($dados_filtro["data_cadastro_inicio"], 0, 2);
      $where[] = "tab_condominios.con_data_cadastro >= '{$dados_filtro["data_cadastro_inicio"]}'";
    }
    if ($dados_filtro["data_cadastro_termino"]) {
      $dados_filtro["data_cadastro_termino"] = substr($dados_filtro["data_cadastro_termino"], 6, 4)."-".substr($dados_filtro["data_cadastro_termino"], 3, 2)."-".substr($dados_filtro["data_cadastro_termino"], 0, 2);
      $where[] = "tab_condominios.con_data_cadastro <= '{$dados_filtro["data_cadastro_termino"]}'";
    }
    if ($dados_filtro["data_ativacao_inicio"]) {
      $dados_filtro["data_ativacao_inicio"] = substr($dados_filtro["data_ativacao_inicio"], 6, 4)."-".substr($dados_filtro["data_ativacao_inicio"], 3, 2)."-".substr($dados_filtro["data_ativacao_inicio"], 0, 2);
      $where[] = "tab_condominios.con_data_ativacao >= '{$dados_filtro["data_ativacao_inicio"]}'";
    }
    if ($dados_filtro["data_ativacao_termino"]) {
      $dados_filtro["data_ativacao_termino"] = substr($dados_filtro["data_ativacao_termino"], 6, 4)."-".substr($dados_filtro["data_ativacao_termino"], 3, 2)."-".substr($dados_filtro["data_ativacao_termino"], 0, 2);
      $where[] = "tab_condominios.con_data_ativacao <= '{$dados_filtro["data_ativacao_termino"]}'";
    }
    if ($dados_filtro["responsavel"]) {
      $where[] = "tab_condominios.usu_id = {$dados_filtro["responsavel"]}";
    }
    if ($dados_filtro["tipo"]) {
      $where[] = "tab_condominios.cnt_id = {$dados_filtro["tipo"]}";
    }
    if ($dados_filtro["status"]) {
      $where[] = "tab_condominios.cos_id = {$dados_filtro["status"]}";
    }

    $where = count($where) ? " AND ".implode(' AND ', $where) : "";

    $dados_filtro["pagina"] = $dados_filtro["pagina"] ? $dados_filtro["pagina"] : 1;
    $limite_itens = PAG_ITENS_SISTEMA_CHAMADOS;
    $limite_inicio = ($dados_filtro["pagina"] - 1) * $limite_itens;
    $query = "
      SELECT SQL_CALC_FOUND_ROWS
        tab_condominios.con_id AS condominio_id,
        CONCAT(tab_condominios.con_nome, ' (', tab_condominios_tipos.cnt_nome, ')') AS nome,
        tab_condominios.con_numero AS numero,
        tab_condominios.cep_id AS cep,
        tab_condominios_status.cos_nome AS status
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_ceps.cep_id = tab_condominios.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
        INNER JOIN tab_condominios_tipos ON (tab_condominios.cnt_id = tab_condominios_tipos.cnt_id)
        LEFT OUTER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_condominios.cos_id IS NOT NULL
        $where
      ORDER BY
        tab_condominios.con_nome ASC
      LIMIT
        $limite_inicio,
        $limite_itens";
    $query = $this->bancodedados->query($query);
    $itens["linhas"] = $this->bancodedados->linhasTotal($query);
    if ($this->permissoes["condominios"]["buscar_plus_plus"] || ($this->permissoes["condominios"]["buscar_plus"] && $itens["linhas"] <= BUS_ITENS) || $itens["linhas"] == 1) {
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $dados["link"] = "javascript:verCondominio({$dados["condominio_id"]})";
        $dados["dados"][] = "<b>Nome:</b> {$dados["nome"]}";
        $dados["dados"][] = "<b>Endereço:</b> ".$this->chamados->buscaEndereco($dados["cep"], $dados["numero"]);
        $dados["dados"] = implode("<br>", $dados["dados"]);
        $dados["id"] = "condominios_{$dados["condominio_id"]}";
        $itens["dados"][] = $dados;
      }
      $condominio_atual = $condominio_id ? "condominios_{$condominio_id}" : "";
      $listagem = controle::instanciaObjeto("listagem", $campos, $itens["dados"], 0, false, null, $condominio_atual);
      if ($retorno_tela) {
        print "document.getElementById('busca_status').innerHTML = '".$this->geraBuscaStatus(count($itens["dados"]), $itens["linhas"], $dados_filtro["pagina"])."';";
        print "pagina_busca_atual = {$dados_filtro["pagina"]};";
        print "document.getElementById('busca_condominios_formulario').style.display = 'none';";
        print "document.getElementById('busca_resultado').style.display = ehNetscape() ? 'table-row-group' : 'block';";
        print "if (document.getElementById('busca_resultado_listagem')) document.getElementById('busca_resultado_listagem').innerHTML = '".addslashes($listagem->conteudo)."';";
        if (!$atualizacao) {
          print "if (document.getElementById('busca_resultado_listagem')) document.getElementById('busca_resultado_listagem').scrollTop = 0;";
        }
      } else {
        $conteudo["listagem"] = $listagem->conteudo;
        $conteudo["busca_status"] = $this->geraBuscaStatus(count($itens["dados"]), $itens["linhas"], $dados_filtro["pagina"]);
        return $conteudo;
      }
    } else {
      if ($retorno_tela) {
        print "alert('Atenção!\\n\\nMuitos registros foram encontrados. Especifique melhor\\na sua busca!');";
      } else {
        $conteudo["listagem"] = "<div algin=\"center\"><b>Atenção!</b><br>Muitos registros foram encontrados, especifique melhor a sua busca!</div>";
        $conteudo["busca_status"] = "";
      }
    }
  }

  function verCondominio($condominio_id) {
    // Gera Dados Resumo
    $this->geraDadosResumoCondominio($condominio_id);
    // Limpa Chamado Atual
    print "document.getElementById('dados_chamado').innerHTML = '';";
    // Gera Listagem Histórico
    $this->geraResultadoHistorico("historico_geral", null, null, $condominio_id);
    // Atualiza Registro Atual
    $this->atualizaRegistroAtual(null, $condominio_id);
  }

  ################################
  ### Gera Resultado Histórico ###
  ################################

  function geraResultadoHistorico($aba, $cliente_id = null, $cliente_condominio_id = null, $condominio_id = null, $chamado_id = null, $retorno_tela = true, $atualizacao = false) {
    if ($aba == "historico_geral") {
      if ($cliente_id) {
        $filtro = "tab_chamados.cli_id = $cliente_id";
      } elseif ($condominio_id) {
        $filtro = "tab_chamados.con_id = $condominio_id";
      } else {
      	return "";
      }
      $limite = "LIMIT 0, 5";
    } elseif ($aba == "historico_condominio_cliente") {
      $filtro = "tab_chamados.con_id = $cliente_condominio_id AND tab_chamados.chs_id = 1";
      $limite = "";
    } elseif ($aba == "historico_clientes_condominio") {
      if ($cliente_id) {
        $filtro = "tab_chamados.cli_id != $cliente_id AND tab_condominios.con_id = $cliente_condominio_id AND tab_chamados.cli_id IS NOT NULL AND tab_chamados.chs_id = 1";
      } else {
        $filtro = "tab_condominios.con_id = $condominio_id AND tab_chamados.cli_id IS NOT NULL AND tab_chamados.chs_id = 1";
      }
      $limite = "";
    } else {
    	return "";
    }
    $itens = array();
    $query = "
      SELECT DISTINCT
        tab_chamados.cha_id AS chamado_id,
        tab_chamados.cha_codigo AS codigo,
        tab_chamados.cha_solicitante AS solicitante,
        tab_chamados.chc_id AS categoria_id,
        tab_chamados.ors_id AS setor_id,
        tab_chamados.orf_id AS funcao_id,
        tab_chamados.usu_id AS usuario_id,
        tab_chamados.cli_id AS cliente_id,
        tab_chamados.con_id AS condominio_id,
        tab_chamados.cha_andamento AS andamento,
        tab_chamados.chi_id AS situacao_id,
        tab_chamados.cha_aguardando_retorno AS aguardando_retorno,
        tab_clientes.cli_nome AS cliente,
        tab_clientes.clt_id AS pessoa_tipo_id,
        tab_condominios.con_id AS cliente_condominio_id,
        tab_condominios.con_nome AS condominio,
        tab_condominios.cnt_id AS condominio_tipo_id,
        DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
        DATE_FORMAT(tab_chamados.cha_datahora_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
        DATE_FORMAT(tab_chamados.cha_datahora_encaminhamento, '%d/%m/%Y (%H:%i)') AS encaminhamento,
        tab_chamados_status.chs_id AS status_id,
        tab_chamados_status.chs_nome AS status_nome,
        tab_chamados_prioridades.chp_id AS prioridade_id,
        tab_chamados_prioridades.chp_nome AS prioridade
      FROM
        tab_chamados
        LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (IF(tab_chamados.cli_id IS NOT NULL, tab_clientes.con_id, tab_chamados.con_id) = tab_condominios.con_id)
        INNER JOIN tab_chamados_status ON (tab_chamados_status.chs_id = tab_chamados.chs_id)
        INNER JOIN tab_chamados_prioridades ON (tab_chamados_prioridades.chp_id = tab_chamados.chp_id)
      WHERE
        $filtro
      ORDER BY
        tab_chamados_status.chs_id,
        tab_chamados.cha_datahora_abertura DESC
      $limite";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["link"] = "javascript:verChamado('{$dados["chamado_id"]}', '{$dados["cliente_id"]}', '{$dados["cliente_condominio_id"]}', '{$dados["condominio_id"]}', 0, event)";
      $categoria = $this->chamados->buscaDadosCategoria($dados["categoria_id"]);
      $dados["categoria"] = $categoria["nome"];
      $responsavel = array();
      if ($dados["setor_id"]) $responsavel[] = $this->chamados->buscaSetor($dados["setor_id"]);
      if ($dados["funcao_id"]) $responsavel[] = $this->chamados->buscaFuncao($dados["funcao_id"]);
      if ($dados["usuario_id"]) $responsavel[] = $this->chamados->buscaUsuario($dados["usuario_id"]);
      $dados["responsavel"] = implode(" » ", $responsavel);
      $dados["duracao"] = $this->chamados->formataDuracao($dados["abertura"], $dados["fechamento"]);
      $dados["duracao_setor"] = $this->chamados->formataDuracao($dados["encaminhamento"], $dados["fechamento"]);
      if ($dados["abertura"]) $dados["datas"][] = "A: {$dados["abertura"]}";
      if ($dados["status_id"] == 1 && $dados["situacao_id"] != 1) $dados["datas"][] = "E: {$dados["encaminhamento"]}";
      if ($dados["fechamento"]) $dados["datas"][] = "F: {$dados["fechamento"]}";
      //if ($dados["status_id"] == 1) $dados["datas"][] = "<span style=\"color:#232323\">({$dados["duracao_setor"]})</span>";
      //if ($dados["status_id"] == 2) $dados["datas"][] = "<span style=\"color:#232323\">({$dados["duracao"]})</span>";
      $dados["datas"][] = "<span style=\"color:#232323\">({$dados["duracao"]})</span>";
      $dados["datas"] = "<div nowrap>".implode("<br>", $dados["datas"])."</div>";
      if ($dados["status_id"] == 1) {
        $dados["status"] = $this->chamados->buscaSituacao($dados["situacao_id"], $dados["status_nome"]).$this->chamados->buscaPrioridade($dados["prioridade_id"]).$this->chamados->buscaAndamento($dados["andamento"]);
      } else {
        $dados["status"] = $dados["status_nome"].$this->chamados->buscaAndamento($dados["andamento"]);
      }
      // Dados Chamado
      $dados["dados"][] = "<b>Código:</b> {$dados["codigo"]}";
      if ($dados["cliente_id"]) {
        $dados["cliente"] = strlen($dados["cliente"]) > 35 ? substr($dados["cliente"], 0, 35)."..." : $dados["cliente"];
        $dados["dados"][] = "<b>Cliente:</b> {$dados["cliente"]}";
      } elseif ($dados["condominio_id"]) {
        $dados["condominio"] = strlen($dados["condominio"]) > 35 ? substr($dados["condominio"], 0, 35)."..." : $dados["condominio"];
        $dados["dados"][] = "<b>Condomínio:</b> {$dados["condominio"]}";
      }
      $dados["dados"][] = "<b>Categoria:</b> {$dados["categoria"]}";
      // Icone Wave Garantia
      if ($dados["cliente_id"]) {
        $dados["dados"][1] = '<div style="float:right;margin-left:2px">'.$this->chamados->buscaWaveGarantia($dados["cliente_id"]).'</div>'.$dados["dados"][1];
      }
      // Icone Cliente
      $hint = $this->chamados->formataHintChamado($dados);
      if ($dados["cliente_id"]) {
        if ($dados["condominio_tipo_id"] == 1) {
          $cliente_tipo = "residencial";
        } elseif ($dados["condominio_tipo_id"] == 2) {
          $cliente_tipo = "comercial";
        } else {
          if ($dados["pessoa_tipo_id"] == 1) {
            $cliente_tipo = "residencial";
          } else {
            $cliente_tipo = "comercial";
          }
        }
        $dados["dados"][1] = '<img src="imagens/cliente_'.$cliente_tipo.'.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      } elseif ($dados["condominio_id"]) {
        $dados["dados"][1] = '<img src="imagens/condominio.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      } else {
        $dados["dados"][1] = '<img src="imagens/empresa.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      }
      $dados["dados"] = implode("<br>", $dados["dados"]);
      // Icone Visualização
      $dados["dados"] = "<div id=\"chamados_historico_visualizacao_{$dados["chamado_id"]}\" style=\"float:right;margin-left:2px\">".$this->chamados->buscaIconeVisualizacoes($this->instancia, $dados["chamado_id"])."</div>".$dados["dados"];
      // Icone Aguardando Retorno
      if ($dados["aguardando_retorno"]) {
        $dados["dados"] = '<img src="imagens/aguardando_retorno.png" width="13" height="13" border="0" style="float:right;margin-left:2px" title="Aguardando Retorno">'.$dados["dados"];
      }
      // Ícone Favoritos
      $dados["dados"] = $this->chamados->buscaIconeFavoritos($dados["chamado_id"], "historico").$dados["dados"];
      $dados["id"] = "historico_chamados_{$dados["chamado_id"]}";
      $itens[] = $dados;
    }
    $chamado_atual = $chamado_id ? "historico_chamados_{$chamado_id}" : "";
    $campos = array ("dados" => "Dados", "datas" => "Datas/Duração", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $campos, $itens, 0, false, array(0, 110, 50), $chamado_atual, null, null, null, null, false);
    if ($retorno_tela) {
      print "if (document.getElementById('historico_listagem')) document.getElementById('historico_listagem').innerHTML = '".addslashes($listagem->conteudo)."';";
      if (!$atualizacao) {
        print "if (document.getElementById('historico_listagem')) document.getElementById('historico_listagem').scrollTop = 0;";
      }
      // Histórico
      if ($aba == "historico_geral") {
        print "document.getElementById('historico_geral').bgColor = '#505050';";
        print "document.getElementById('historico_condominio_cliente').bgColor = '#828282';";
        print "document.getElementById('historico_clientes_condominio').bgColor = '#828282';";
        print "aba_historico_atual = 'historico_geral';";
      } elseif ($aba == "historico_clientes_condominio") {
        print "document.getElementById('historico_geral').bgColor = '#828282';";
        print "document.getElementById('historico_condominio_cliente').bgColor = '#828282';";
        print "document.getElementById('historico_clientes_condominio').bgColor = '#505050';";
        print "aba_historico_atual = 'historico_clientes_condominio';";
      } else {
        print "document.getElementById('historico_geral').bgColor = '#828282';";
        print "document.getElementById('historico_condominio_cliente').bgColor = '#505050';";
        print "document.getElementById('historico_clientes_condominio').bgColor = '#828282';";
        print "aba_historico_atual = 'historico_condominio_cliente';";
      }
    } else {
      return $listagem->conteudo;
    }
  }

  #################################
  ### Gera Dados Resumo Cliente ###
  #################################

  function geraDadosResumoCliente($cliente_id, $retorno_tela = true) {
  	$dados_cliente = $this->chamados->buscaDadosCliente($cliente_id);
    if ($dados_cliente) {
      // Trata Dados
      if ($dados_cliente["data_aniversario"] == date("d/m")) {
        $dados_cliente["nome"] = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\"><td>[$cliente_id] {$dados_cliente["nome"]}&nbsp;</td><td> <img src=\"imagens/bolo_aniversario.png\" width=\"13\" height=\"13\" border=\"0\"></td></tr></table>";
      } else {
        $dados_cliente["nome"] = "[$cliente_id] {$dados_cliente["nome"]}";
      }
      // Conteúdo Resumo
      $conteudo_resumo = $this->carregaArquivo("chamados_sistema.resumo_cliente.inc.php");
      $conteudo_resumo = str_replace("[nome]", $dados_cliente["nome"], $conteudo_resumo);
	    $conteudo_resumo = str_replace("[origem]", $dados_cliente["origem_nome"], $conteudo_resumo);
      $conteudo_resumo = str_replace("[endereco]", $dados_cliente["endereco"], $conteudo_resumo);
      $conteudo_resumo = str_replace("[telefones]", $this->buscaClientesTelefones($cliente_id), $conteudo_resumo);
      $conteudo_resumo = str_replace("[resumo]", $this->buscaResumoCliente($dados_cliente), $conteudo_resumo);
      $conteudo_resumo = preg_replace("/[ \r\n\t]+/", " ", $conteudo_resumo);
      // Conteúdo Contatos
      //$conteudo_contatos = $this->carregaArquivo("chamados_sistema.resumo_contatos.inc.php");
      //$conteudo_contatos = str_replace("[nome]", $dados_cliente["nome"], $conteudo_contatos);
      //$conteudo_contatos = str_replace("[contatos]", $this->buscaClientesContatos($cliente_id, $dados_cliente["status_id"] == 2), $conteudo_contatos);
      //$conteudo_contatos = preg_replace("/[ \r\n\t]+/", " ", $conteudo_contatos);
    }
    if ($retorno_tela) {
      //print "if (cliente_atual == '0') exibeResumo('dados_resumo', false);";
    	print "if (document.getElementById('dados_resumo_conteudo')) document.getElementById('dados_resumo_conteudo').innerHTML = '".addslashes($conteudo_resumo)."';";
    	print "if (document.getElementById('resumo_box')) document.getElementById('resumo_box').style.display = 'table-row-group';";
    	//print "if (document.getElementById('dados_contato_conteudo')) document.getElementById('dados_contato_conteudo').innerHTML = '".addslashes($conteudo_contatos)."';";
      //print "if (document.getElementById('dados_resumo')) document.getElementById('dados_resumo').innerHTML = 'Resumo do Cliente (".$dados_cliente["origem_nome"].")';";
      //print "if (document.getElementById('dados_contato')) document.getElementById('dados_contato').style.display = ehNetscape() ? 'table-cell' : 'block';";
      print "if (document.getElementById('historico_geral')) document.getElementById('historico_geral').innerHTML = 'Cliente';";
      // Atualiza Botões
      $this->atualizaBotoesResumo($cliente_id);
      // Exibe Abas Histórico
      print "document.getElementById('historico_condominio_cliente').style.display = ehNetscape() ? 'table-cell' : 'block';";
      print "document.getElementById('historico_clientes_condominio').style.display = ehNetscape() ? 'table-cell' : 'block';";
      print "if (aba_historico_atual == 'historico_geral') {";
      print "  document.getElementById('historico_condominio_cliente').bgColor = '#828282';";
      print "  document.getElementById('historico_clientes_condominio').bgColor = '#828282';";
      print "}";
      //$dados_pendentes = $this->buscaDadosPendentesCliente($cliente_id);
      //if ($dados_pendentes) {
      //  $plural = count($dados_pendentes) > 1 ? "s" : "";
      //  print "if (atualizando) alert(\"A T E N Ç Ã O !\\n\\nEsse cliente possui o$plural seguinte$plural dado$plural pendente$plural:\\n\\n".implode("\\n", $dados_pendentes)."\\n\\nFavor solicitá-lo$plural e cadastrá-lo$plural no sistema.\\n\\nObrigado.\");";
      //}
    } else {
      //return array("resumo" => $conteudo_resumo, "contatos" => $conteudo_contatos, "origem" => $dados_cliente["origem_nome"]);
      return array("resumo" => $conteudo_resumo, "origem" => $dados_cliente["origem_nome"]);
    }
  }

  function buscaDadosPendentesCliente($cliente_id) {
    $clientes = controle::instanciaObjeto("clientes", $this->bancodedados, $this->permissoes, false);
    $dados_cliente = $clientes->buscaDadosItem($cliente_id);
    $dados_pendentes = $clientes->buscaDadosPendentes($clientes->dados_item, true);
    if ($dados_pendentes["alertar"]) {
    	return $dados_pendentes["dados"];
    }
  }

  function buscaResumoCliente($dados_cliente) {
    // Dados
    $dados["status"] = "<center>{$dados_cliente["status_nome"]}</center>";
    $dados["wave_garantia"] = $this->chamados->buscaWaveGarantia($dados_cliente["id"], true);
    $dados["pontos"] = $this->buscaClientesPontos($dados_cliente["id"]);
    $dados["emails"] = $this->buscaClientesEmails($dados_cliente["id"]);
    $dados["dominios"] = $this->buscaClientesDominios($dados_cliente["id"]);
//    $dados["voip"] = $this->buscaCondasVoIP($dados_cliente["id"]);
/*
    if ($dados_cliente["status_id"] == 1) {
      $dados["prospeccao"] = $this->buscaClienteDataProspeccao($dados_cliente["id"]);
    } elseif ($dados_cliente["status_id"] == 2) {
      $dados["ativacao"] = $this->buscaClienteDataAtivacao($dados_cliente["id"]);
    } elseif ($dados_cliente["status_id"] == 3) {
      $dados["cancelamento"] = $this->buscaClienteDataCancelamento($dados_cliente["id"]);
    } else {
      $dados["cadastro"] = $dados_cliente["data_cadastro"];
    }
*/
    $dados["financeiro"] = $this->buscaSituacaoFinanceira($dados_cliente["id"]);
    $dados["ultima_cobranca"] = $this->buscaUltimaCobranca($dados_cliente);
    $dados["situacao_servidor"] = $this->buscaSituacaoServidor(null, $dados_cliente["bloco_id"]);
    // Listagem
    $campos["status"] = "<center>Status</center>";
    if ($dados_cliente["status_id"] == 1) {
      $campos["prospeccao"] = "Prospecção";
    } elseif ($dados_cliente["status_id"] == 2) {
      $campos["ativacao"] = "Ativação";
    } elseif ($dados_cliente["status_id"] == 3) {
      $campos["cancelamento"] = "Cancel.";
    } else {
      $campos["cadastro"] = "Cadastro";
    }
    $campos["situacao_servidor"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Situação do Servidor', 120)\" onmouseout=\"hintHide()\">SS</div>";
    $campos["wave_garantia"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Olé Garantia', 80)\" onmouseout=\"hintHide()\">OG</div>";
    $campos["pontos"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Pontos de Acesso', 92)\" onmouseout=\"hintHide()\">PA</div>";
    $campos["emails"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Contas de E-mail', 92)\" onmouseout=\"hintHide()\">CE</div>";
    $campos["dominios"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Contas de Domínio', 100)\" onmouseout=\"hintHide()\">CD</div>";
    $campos["financeiro"] = "Situação";
    $listagem = controle::instanciaObjeto("listagem", $campos, array(0 => $dados), 0, false, array(55, 60, 20, 20, 25, 25, 25, 90));
    return $listagem->conteudo;
  }

  function buscaClientesTelefones($cliente_id) {
    global $login;
    $query = "
      SELECT
        ccl_ramal AS ramal,
        CONCAT('(', ccl_ddd, ') ', ccl_numero) AS telefone,
        ccl_ddd AS ddd,
        ccl_numero AS numero,
        ccf_id tipo_id
      FROM
        tab_clientes_contatos_telefones
      WHERE
      	cli_id = $cliente_id AND
		ccf_id > 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $telefone = $dados["telefone"];
      if ($dados["ddd"] == 48) {
        $numero = preg_replace("/[^0-9]/", "", "0".$dados["numero"]);
      } else {
        $numero = preg_replace("/[^0-9]/", "", "00".$dados["ddd"].$dados["numero"]);
      }
      if ($dados["tipo_id"] == 1)
      	$telefone .= " R";
      else if ($dados["tipo_id"] == 2)
      	$telefone .= " C";
      else if ($dados["tipo_id"] == 4)
      	$telefone .= " F";
      if ($dados["ramal"]) $telefone .= " (Ramal {$dados["ramal"]})";
      //if ($login->usuario["agente_id"] || ($login->usuario["ramal"] && $dados["numero"][0] < 7)) {
        $lista_com_ramal[] = "<a href=\"javascript:discar('$numero')\" title=\"Discar para este número\" class=\"linkDiscar\">$telefone</a>";
      //} else {
      //  $lista_com_ramal[] = $telefone;
      //}
    }
    return implode(" | ", $lista_com_ramal);
  }
  
  function buscaClientesContatos($cliente_id, $removivel) {
    $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
    $conteudo = '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td><b>Telefone(s):</b></td><td id="listagem_telefones">'.$contatos->geraContatosTelefonesListagem($contatos->buscaContatosTelefones($cliente_id), $this->permissoes["clientes_contatos"]["editar"], $removivel).'</td></tr>';
    $conteudo .= '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td><b>E-mail(s):</b></td><td id="listagem_emails">'.$contatos->geraContatosEmailsListagem($contatos->buscaContatosEmails($cliente_id), $this->permissoes["clientes_contatos"]["editar"], $removivel).'</td></tr>';
    $conteudo .= '<tr style="display:none"><td><input type="hidden" id="lista_telefones" value=\''.serialize($contatos->buscaContatosTelefones($cliente_id)).'\'>';
    $conteudo .= '<input type="hidden" id="lista_emails" value=\''.serialize($contatos->buscaContatosEmails($cliente_id)).'\'></td></tr>';
    $conteudo .= '<input type="hidden" id="cliente_id" value=\''.$cliente_id.'\'>';
    $conteudo .= '<input type="hidden" id="auto_salvar" value="1"></td></tr>';
    return $conteudo;
  }

  ###############################
  ### Atualiza Registro Atual ###
  ###############################

  function atualizaRegistroAtual($cliente_id = null, $condominio_id = null, $chamado_id = null) {
    // Trata Dados
    $cliente_id = $cliente_id ? $cliente_id : "0";
    $cliente_condominio_id = $condominio_id && !$cliente_id ? $condominio_id : "0";
    $condominio_id = $condominio_id ? $condominio_id : "0";
    $chamado_id = $chamado_id ? $chamado_id : "0";
    // Deseleciona Registros Antigos
    $this->setaCorRegistro("", $cliente_id, $condominio_id, $chamado_id);
    // Seta Registro Atual
    print "cliente_atual = '$cliente_id';";
    print "cliente_condominio_atual = '$condominio_id';";
    print "condominio_atual = '$cliente_condominio_id';";
    print "chamado_atual = '$chamado_id';";
    if (!$chamado_id) {
      print "if (chamado_acao_atual != 'adicionar_chamado') chamado_acao_atual = '';";
      print "chamado_etapa_atual = '0';";
    }
    // Redimensiona Divs
    print "redimensionaDivs();";
    // Seleciona Registros Atuais
    $this->setaCorRegistro("#ffcc66");
  }

  function setaCorRegistro($cor = "", $cliente_id = "0", $condominio_id = "0", $chamado_id = "0") {
    print "if (cliente_atual != '$cliente_id' && document.getElementById('clientes_' + cliente_atual)) document.getElementById('clientes_' + cliente_atual).style.backgroundColor = '$cor';";
    print "if (condominio_atual != '$condominio_id' && document.getElementById('condominios_' + condominio_atual)) document.getElementById('condominios_' + condominio_atual).style.backgroundColor = '$cor';";
    print "if (chamado_atual != '$chamado_id' && document.getElementById('chamados_' + chamado_atual) && ('$cor' != '' || (document.getElementById('chamados_' + chamado_atual).style.backgroundColor != '#D7D7D7' && document.getElementById('chamados_' + chamado_atual).style.backgroundColor != 'rgb(215, 226, 238)'))) document.getElementById('chamados_' + chamado_atual).style.backgroundColor = '$cor';";
    print "if (chamado_atual != '$chamado_id' && document.getElementById('historico_chamados_' + chamado_atual)) document.getElementById('historico_chamados_' + chamado_atual).style.backgroundColor = '$cor';";
  }

  ####################################
  ### Gera Dados Resumo Condomínio ###
  ####################################

  function geraDadosChamadoInterno() {
    print "if (document.getElementById('dados_resumo_conteudo')) document.getElementById('dados_resumo_conteudo').innerHTML = '';";
    print "if (document.getElementById('resumo_box')) document.getElementById('resumo_box').style.display = 'none';";
    print "if (document.getElementById('historico_listagem')) document.getElementById('historico_listagem').innerHTML = '';";
    print "document.getElementById('historico_geral').bgColor = '#505050';";
    print "document.getElementById('historico_geral').style.display = ehNetscape() ? 'table-cell' : 'block';";
    print "document.getElementById('historico_condominio_cliente').bgColor = '#828282';";
    print "document.getElementById('historico_condominio_cliente').style.display = ehNetscape() ? 'table-cell' : 'block';";
    print "document.getElementById('historico_clientes_condominio').bgColor = '#828282';";
    print "document.getElementById('historico_clientes_condominio').style.display = 'none';";
    print "aba_historico_atual = 'historico_geral';";
    print "redimensionaDivs();";
    // Atualiza Botões
    $this->atualizaBotoesResumo();
  }

  ####################################
  ### Gera Dados Resumo Condomínio ###
  ####################################

  function geraDadosResumoCondominio($condominio_id, $retorno_tela = true) {
  	$dados_condominio = $this->chamados->buscaDadosCondominio($condominio_id);
    if ($dados_condominio) {
      // Conteúdo Resumo
      $conteudo_resumo = $this->carregaArquivo("chamados_sistema.resumo_condominio.inc.php");
      $conteudo_resumo = str_replace("[nome]", $dados_condominio["nome"], $conteudo_resumo);
      $conteudo_resumo = str_replace("[endereco]", $dados_condominio["endereco"], $conteudo_resumo);
      $conteudo_resumo = str_replace("[telefones]", $this->buscaCondominiosTelefones($condominio_id), $conteudo_resumo);
      $conteudo_resumo = str_replace("[resumo]", $this->buscaResumoCondominio($dados_condominio), $conteudo_resumo);
      $conteudo_resumo = preg_replace("/[ \r\n\t]+/", " ", $conteudo_resumo);
    }
    if ($retorno_tela) {
      //print "if (condominio_atual == '0') exibeResumo('dados_resumo', false);";
      if ($condominio_id) {
	      print "if (document.getElementById('dados_resumo_conteudo')) document.getElementById('dados_resumo_conteudo').innerHTML = '".addslashes($conteudo_resumo)."';";
	    	print "if (document.getElementById('resumo_box')) document.getElementById('resumo_box').style.display = 'table-row-group';";
      } else {
	      print "if (document.getElementById('dados_resumo_conteudo')) document.getElementById('dados_resumo_conteudo').innerHTML = '';";
      	print "if (document.getElementById('resumo_box')) document.getElementById('resumo_box').style.display = 'none';";
      }
      print "if (document.getElementById('historico_geral')) document.getElementById('historico_geral').innerHTML = 'Condomínio';";
      // Atualiza Botões
      $this->atualizaBotoesResumo(null, $condominio_id);
      // Exibe Abas Histórico
      print "document.getElementById('historico_condominio_cliente').style.display = 'none';";
      print "document.getElementById('historico_clientes_condominio').style.display = ehNetscape() ? 'table-cell' : 'block';";
      print "if (aba_historico_atual == 'historico_geral') {";
      print "  document.getElementById('historico_clientes_condominio').bgColor = '#828282';";
      print "}";
    } else {
      return array("resumo" => $conteudo_resumo);
    }
  }

  function buscaResumoCondominio($dados_condominio) {
    // Dados
    $dados["status"] = "<center>".$dados_condominio["status_nome"]."</center>";
    $dados["cadastro"] = $dados_condominio["data_cadastro"];
    $dados["ativacao"] = $dados_condominio["data_ativacao"];
    $dados["blocos"] = $this->buscaQuantidadeBlocos($dados_condominio["id"]);
    $dados["apartamentos"] = $this->buscaQuantidadeApartamentos($dados_condominio["id"]);
    $dados["clientes_ativos"] = $this->buscaClientesAtivos($dados_condominio["id"]);
    $dados["pontos_ativos"] = $this->buscaPontosAtivos($dados_condominio["id"]);
    $dados["mensalidade"] = $this->buscaMensalidade($dados_condominio["id"]);
    $dados["situacao_servidor"] = $this->buscaSituacaoServidor($dados_condominio["id"]);
    // Listagem
    $campos["status"] = "<center>Status</center>";
    if ($dados_condominio["status_id"] == 1) {
      $campos["cadastro"] = "Dt. Cad.";
    } else {
      $campos["ativacao"] = "Dt. Ativ.";
    }
    $campos["situacao_servidor"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Situação do Servidor', 120)\" onmouseout=\"hintHide()\">SS</div>";
    $campos["blocos"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Blocos', 40)\" onmouseout=\"hintHide()\">BL</div>";
    $campos["apartamentos"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Apartamentos', 40)\" onmouseout=\"hintHide()\">AP</div>";
    $campos["clientes_ativos"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Clientes Ativos', 80)\" onmouseout=\"hintHide()\">CA</div>";
    $campos["pontos_ativos"] = "<div style=\"cursor:pointer\" onmouseover=\"hintShow('Pontos Ativos', 75)\" onmouseout=\"hintHide()\">PA</div>";
    $campos["mensalidade"] = "Mensalidade";
    $listagem = controle::instanciaObjeto("listagem", $campos, array(0 => $dados), 0, false, array(55, 60, 20, 20, 20, 20, 20));
    return $listagem->conteudo;
  }

  function buscaCondominiosTelefones($condominio_id) {
  	global $login;
	$contatos = array();
    $query = "
      SELECT
        tab_condominios_contatos.coc_id AS id,
        tab_condominios_contatos.coc_nome AS nome,
        tab_condominios_contatos.coc_tel_residencial AS telefone_residencial,
        tab_condominios_contatos.coc_tel_comercial AS telefone_comercial,
        tab_condominios_contatos.coc_tel_celular AS telefone_celular,
        tab_condominios_contatos_tipos.cct_nome AS tipo
      FROM
        tab_condominios_contatos
        INNER JOIN tab_condominios_contatos_tipos ON (tab_condominios_contatos.cct_id = tab_condominios_contatos_tipos.cct_id)
      WHERE
        tab_condominios_contatos.con_id = $condominio_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["telefone_residencial"]) $dados["telefones"][] = $dados["telefone_residencial"];
      if ($dados["telefone_comercial"]) $dados["telefones"][] = $dados["telefone_comercial"];
      if ($dados["telefone_celular"]) $dados["telefones"][] = $dados["telefone_celular"];
      if ($dados["telefones"]) {
	      foreach ($dados["telefones"] as $id => $telefone) {
	      	$numero = preg_replace("/[^0-9]/", "", $telefone);
	      	//if ($login->usuario["agente_id"] || ($login->usuario["ramal"] && $numero[0] < 7)) {
			      if (substr($numero, 0, 2) == 48) {
			        $numero = preg_replace("/[^0-9]/", "", "0".substr($numero, 2));
			      } else {
			        $numero = preg_replace("/[^0-9]/", "", "00".$numero);
			      }
	      		$dados["telefones"][$id] = "<a href=\"javascript:discar('$numero')\" title=\"Discar para este número\" class=\"linkDiscar\">$telefone</a>";
      		//}
      	}
      	$contatos[] = "{$dados["nome"]} ({$dados["tipo"]}): ".implode(" | ", $dados["telefones"]);
      }
    }
    return implode(", ", $contatos);
  }
  
  function desagendarVisitaTecnica($chamado_id) {
  	$dados_chamado = $this->chamados->buscaDadosChamado($chamado_id);
  	if ($dados_chamado) {
	  	$this->chamados->registraEtapa($dados_chamado, 25);
  	}
  }
  
  #########################
  ### Chamado Adicionar ###
  #########################

  function chamadoAdicionar($dados_situacao = null, $dados = null, $exibir_chamado = true, $retorno_tela = true) {
    if (!$dados) {
      $conteudo = $this->carregaArquivo("chamados_sistema.chamado.adicionar.inc.php");
      $conteudo = str_replace("[solicitante]", $dados_situacao["cliente_atual"] || $dados_situacao["condominio_atual"] ? "" : $this->buscaUsuarioNome($_SESSION["usuario"]), $conteudo);
      $conteudo = str_replace("[solicitante_editavel]", $dados_situacao["cliente_atual"] || $dados_situacao["condominio_atual"] ? "" : "readonly", $conteudo);
      $conteudo = str_replace("[nivel_stress]", $this->geraSelect($this->chamados->buscaNiveisStress($_SESSION["usuario"])), $conteudo);
      $conteudo = str_replace("[categoria]", $this->geraSelect($this->chamados->buscaChamadosCategorias($_SESSION["usuario"])), $conteudo);
      $conteudo = str_replace("[prioridade]", $this->geraSelect($this->chamados->buscaPrioridades()), $conteudo);
      $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa(), $conteudo);
      if ($this->permissoes["chamados"]["solicitar_fechamento"]) {
      	$conteudo = str_replace("[responsavel_fechamento]", $this->geraSelect($this->chamados->buscaResponsaveisSetor($_SESSION["usuario"])), $conteudo);
      } else {
        $conteudo = preg_replace("/<!--solicitacao_fechamento-->.*?<!--\\/solicitacao_fechamento-->/si", "", $conteudo);
      }
      $botoes[] = '<input type="button" id="salvar_chamado" class="buttonAzulEscuroBranco11" value="Salvar" onclick="adicionarChamadoSalvar(1)">';
      $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
      $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
      $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
      // Atualiza Registro Atual
      if ($retorno_tela) {
        print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
        if ($dados_situacao["cliente_atual"] || $dados_situacao["condominio_atual"]) {
          print "if (document.getElementById('solicitante')) document.getElementById('solicitante').focus();";
        } else {
          print "if (document.getElementById('nivel_stress')) document.getElementById('nivel_stress').focus();";
        }
        $this->atualizaRegistroAtual($dados_situacao["cliente_atual"], $dados_situacao["cliente_atual"] ? $dados_situacao["cliente_condominio_atual"] : $dados_situacao["condominio_atual"]);
        print "atualizaCaracteresRestantes();";
        print "chamado_alterado = false;";
      } else {
        return $conteudo;
      }
    } else {
      $chamado_id = $this->chamados->adicionarChamado($dados_situacao["cliente_atual"], $dados_situacao["condominio_atual"], $dados);
      if ($exibir_chamado) {
        // Exibe Chamado
        $this->chamadoVer($chamado_id);
        if ($dados_situacao["cliente_atual"] || $dados_situacao["condominio_atual"]) {
          // Atualiza Histórico
          $this->geraResultadoHistorico('historico_geral', $dados_situacao["cliente_atual"], $dados_situacao["cliente_condominio_atual"], $dados_situacao["condominio_atual"]);
        }
        // Atualiza Registro Atual
        $this->atualizaRegistroAtual($dados_situacao["cliente_atual"], $dados_situacao["cliente_atual"] ? $dados_situacao["cliente_condominio_atual"] : $dados_situacao["condominio_atual"], $chamado_id);
      }
      print "chamado_alterado = false;";
      // Atualiza Busca
      print "atualizaBusca();";
    }
  }

  ###################
  ### Chamado Ver ###
  ###################

  function chamadoVer($chamado_id = null, $dados = null, $retorno_tela = true, $atualizacao = false) {
    if (!$chamado_id) {
      $this->chamados->registraChamadoVisualizacao($this->instancia);
      if ($retorno_tela) {
        print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '';";
        print "chamado_acao_atual = '';";
        print "chamado_alterado = false;";
      } else {
        return "";
      }
    } else {
      if ($this->permissoes["chamados"]["editar"]) {
        return $this->chamadoEditar($chamado_id, $dados, true, $retorno_tela, $atualizacao);
      } else {
      	$dados_chamado = $this->chamados->buscaDadosChamado($chamado_id);
        if ($dados_chamado) {
        	if (!$dados_chamado["visualizado"] && $this->chamados->ehResponsavelChamado($dados_chamado["id"], $_SESSION["usuario"])) {
        		// Registra Etapa Visualização
        		$this->chamados->registraEtapa($dados_chamado, 21);
        		$visualizado = true;
        	} else {
        		$visualizado = false;
        	}
          // Atualiza Registro Chamado Bloqueado
          $this->chamados->registraChamadoVisualizacao($this->instancia, $chamado_id);
          // Conteúdo
          $conteudo = $this->carregaArquivo("chamados_sistema.chamado.ver.inc.php");
          $conteudo = str_replace("[codigo]", "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr class=\"txtAzulEscuro11\"><td>".$dados_chamado["codigo"]."</td><td>".$this->chamados->buscaIconesChamado($dados_chamado)."</td></tr></table>", $conteudo);
          $conteudo = str_replace("[solicitante]", $dados_chamado["solicitante"], $conteudo);
          $conteudo = str_replace("[categoria]", $dados_chamado["categoria_nome"], $conteudo);
          $conteudo = str_replace("[responsavel]", $dados_chamado["responsavel_nome"], $conteudo);
          $conteudo = str_replace("[prioridade]", $dados_chamado["prioridade_nome"], $conteudo);
          $conteudo = str_replace("[andamento]", $dados_chamado["andamento"]."%", $conteudo);
          $conteudo = str_replace("[data_hora_retorno]", $dados_chamado["data_hora_retorno"], $conteudo);
          if ($dados_chamado["chamado_pai"]) {
            $dados_chamado_pai = $this->chamados->buscaDadosChamado($dados_chamado["chamado_pai"]);
            $conteudo = str_replace("[exibir_chamado_pai]", "", $conteudo);
            $conteudo = str_replace("[chamado_pai]", "{$dados_chamado_pai["codigo"]} - {$dados_chamado_pai["categoria_nome"]}", $conteudo);
          } else {
            $conteudo = str_replace("[exibir_chamado_pai]", "display:none", $conteudo);
          }
          $chamados_filho = $this->chamados->buscaChamadosFilho($chamado_id);
          if ($chamados_filho) {
            $conteudo = str_replace("[exibir_chamados_filho]", "", $conteudo);
            $conteudo = str_replace("[listagem_chamados_filho]", $this->chamados->geraListagemChamados($chamados_filho), $conteudo);
          } else {
            $conteudo = str_replace("[exibir_chamados_filho]", "display:none", $conteudo);
          }
          $conteudo = str_replace("[chamado_pai]", $dados_chamado["chamado_pai_nome"], $conteudo);
          $conteudo = str_replace("[abertura]", $dados_chamado["abertura"]." por <span style=\"color:#232323\">".$this->chamados->buscaUsuarioEtapa($dados_chamado["id"], 1, "usuario_nome")."</span>", $conteudo);
          $conteudo = str_replace("[encaminhamento]", $dados_chamado["encaminhamento"] ? $dados_chamado["encaminhamento"]." por <span style=\"color:#232323\">".$this->chamados->buscaUsuarioEtapa($dados_chamado["id"], 3, "usuario_nome")."</span>" : "", $conteudo);
          $conteudo = str_replace("[fechamento]", $dados_chamado["fechamento"] ? $dados_chamado["fechamento"]." por <span style=\"color:#232323\">".$this->chamados->buscaUsuarioEtapa($dados_chamado["id"], 6, "usuario_nome")."</span>" : "", $conteudo);
          $conteudo = str_replace("[duracao]", $dados_chamado["duracao_setor"] != $dados_chamado["duracao"] && $dados_chamado["status_id"] == 1 ? "{$dados_chamado["duracao_setor"]} <span style=\"color:#232323\">({$dados_chamado["duracao"]})</span>" : $dados_chamado["duracao"], $conteudo);
					$conteudo = str_replace("[data_visita_tecnica]", $this->chamados->buscaDataVisitaTecnica($chamado_id), $conteudo);
          $conteudo = str_replace("[resumida_checked]", array_key_exists("chamados_etapas_detalhamento", $_SESSION["instancias"][$this->instancia]) && $_SESSION["instancias"][$this->instancia]["chamados_etapas_detalhamento"] ? "" : "checked", $conteudo);
          $conteudo = str_replace("[detalhada_checked]", array_key_exists("chamados_etapas_detalhamento", $_SESSION["instancias"][$this->instancia]) && $_SESSION["instancias"][$this->instancia]["chamados_etapas_detalhamento"] ? "checked" : "", $conteudo);
          $conteudo = str_replace("[etapas]", $this->chamados->geraListagemEtapas($this->chamados->buscaEtapas($dados_chamado["id"])), $conteudo);
          $conteudo = str_replace("[status]", $dados_chamado["status_nome"], $conteudo);
          // Botões
          if ($this->chamados->ehLiberavelTecnico($dados_chamado)) $botoes[] = '<input type="button" id="liberar_tecnico" class="buttonAzulEscuroBranco11" value="Liberar Técnico" onclick="executaAcao(\'liberar_tecnico\', 1)">';
          if ($this->chamados->ehIniciavelAcompanhamento($dados_chamado)) $botoes[] = '<input type="button" id="acompanhar" class="buttonAzulEscuroBranco11" value="Iniciar Acompanhamento" onclick="executaAcao(\'acompanhar\', 1)">';
          if ($this->chamados->ehSolicitavelFechamento($dados_chamado)) $botoes[] = '<input type="button" id="solicitar_fechamento" class="buttonAzulEscuroBranco11" value="Solicitar Fechamento" onclick="executaAcao(\'solicitar_fechamento\', 1)">';
          if ($this->chamados->ehCancelavelAcompanhamento($dados_chamado)) $botoes[] = '<input type="button" id="parar_de_acompanhar" class="buttonAzulEscuroBranco11" value="Cancelar Acompanhamento" onclick="executaAcao(\'acompanhar\', 1)">';
          if ($this->chamados->ehFechavel($dados_chamado)) $botoes[] = '<input type="button" id="fechar" class="buttonAzulEscuroBranco11" value="Fechar Chamado" onclick="executaAcao(\'fechar_chamado\', 1)">';
          if ($this->chamados->ehReabrivel($dados_chamado)) $botoes[] = '<input type="button" id="reabrir" class="buttonAzulEscuroBranco11" value="Reabrir Chamado" onclick="executaAcao(\'reabrir_chamado\', 1)">';
          if ($this->chamados->ehImprimivel($dados_chamado)) $botoes[] = '<input type="button" id="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onclick="imprimeChamado()">';
          if ($this->chamados->ehAgendavelVisitaTecnica($dados_chamado)) $botoes[] = '<input type="button" id="agendar_visita" class="buttonAzulEscuroBranco11" value="Agendar Visita Tecnica" onclick="agendarVisitaTecnica()">';
          if ($this->chamados->ehDesagendavelVisitaTecnica($dados_chamado)) $botoes[] = '<input type="button" id="desagendar_visita" class="buttonAzulEscuroBranco11" value="Desagendar Visita Tecnica" onclick="desagendarVisitaTecnica()">';
          $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
          $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        }
        if ($retorno_tela) {
          if ($this->ehAlertaAgendamentoVisivel()) {
            print "exibeAlertaAgendamento();";
          } else {
    	      print "ocultaAlertaAgendamento();";
          } 
          $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
          print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
          if (!$atualizacao) {
            print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').scrollTop = 0;";
          }
          print "chamado_acao_atual = '';";
          print "chamado_alterado = false;";
          // Atualiza Icone Visualizações
          $visualizacao = $this->chamados->buscaIconeVisualizacoes($this->instancia, $chamado_id, true);
          if ($visualizacao) {
            $visualizacao["usuarios"] = implode("\\n\\n", str_replace("<br>", "\\n", $visualizacao["usuarios"]));
            print "if (document.getElementById('chamados_busca_visualizacao_$chamado_id')) document.getElementById('chamados_busca_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            print "if (document.getElementById('chamados_historico_visualizacao_$chamado_id')) document.getElementById('chamados_historico_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            print "if (document.getElementById('chamados_filhos_visualizacao_$chamado_id')) document.getElementById('chamados_filhos_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            if (!$atualizacao && $_SESSION["instancias"][$this->instancia][$_SESSION["tela_atual"][$this->instancia]]["chamado_id"] != $chamado_id) {
              print "alert('Atenção!\\n\\nEste chamado está sendo visualizado pelo(s) usuário(s):\\n\\n{$visualizacao["usuarios"]}');";
            }
          } else {
            print "if (document.getElementById('chamados_busca_visualizacao_$chamado_id')) document.getElementById('chamados_busca_visualizacao_$chamado_id').innerHTML = '';";
            print "if (document.getElementById('chamados_historico_visualizacao_$chamado_id')) document.getElementById('chamados_historico_visualizacao_$chamado_id').innerHTML = '';";
            print "if (document.getElementById('chamados_filhos_visualizacao_$chamado_id')) document.getElementById('chamados_filhos_visualizacao_$chamado_id').innerHTML = '';";
          }
        } else {
          return $conteudo;
        }
      }
    }
  }

  ######################
  ### Chamado Editar ###
  ######################

  function chamadoEditar($chamado_id = null, $dados = null, $exibir_chamado = true, $retorno_tela = true, $atualizacao = false) {
  	$dados_chamado = $this->chamados->buscaDadosChamado($chamado_id);
  	
    if ($dados_chamado) {
      if (!$dados_chamado["visualizado"] && $this->chamados->ehResponsavelChamado($dados_chamado["id"], $_SESSION["usuario"])) {
        // Registra Etapa Visualização
        $this->chamados->registraEtapa($dados_chamado, 21);
        $visualizado = true;
      } else {
        $visualizado = false;
      }
    	// Atualiza Registro Chamado Bloqueado
      $this->chamados->registraChamadoVisualizacao($this->instancia, $chamado_id);
      // Conteúdo
      if (!$dados) {
        $eh_editavel = $dados_chamado["status_id"] == 1 && $this->chamados->ehResponsavelChamado($dados_chamado["id"], $_SESSION["usuario"]);
        $conteudo = $this->carregaArquivo("chamados_sistema.chamado.editar.inc.php");
        $conteudo = str_replace("[codigo]", "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr class=\"txtAzulEscuro11\"><td>".$dados_chamado["codigo"]."</td><td>".$this->chamados->buscaIconesChamado($dados_chamado)."</td></tr></table>", $conteudo);
        $conteudo = str_replace("[solicitante]", $dados_chamado["solicitante"], $conteudo);
        $conteudo = str_replace("[categoria]", $this->geraSelect($this->chamados->buscaChamadosCategorias($_SESSION["usuario"], $dados_chamado["categoria_id"]), $dados_chamado["categoria_id"]), $conteudo);
        $conteudo = str_replace("[responsavel]", $this->geraSelect($this->chamados->buscaResponsaveis($dados_chamado["categoria_id"], $dados_chamado["responsavel_id"], $dados_chamado["responsavel_nome"]), $dados_chamado["responsavel_id"]), $conteudo);
        $conteudo = str_replace("[prioridade]", $this->geraSelect($this->chamados->buscaPrioridades(), $dados_chamado["prioridade_id"]), $conteudo);
        $conteudo = str_replace("[andamento]", $this->geraSelect($this->chamados->buscaAndamentos($dados_chamado["andamento"]), $dados_chamado["andamento"]), $conteudo);
        $conteudo = str_replace("[retorno]", $this->geraSelect($this->chamados->buscaRetorno()), $conteudo);
				$conteudo = str_replace("[data_visita_tecnica]", $this->chamados->buscaDataVisitaTecnica($chamado_id), $conteudo);
        $conteudo = str_replace("[data_hora_retorno]", $dados_chamado["data_hora_retorno"], $conteudo);
        $chamados_possiveis = array();
        if ((($dados_chamado["cliente_id"] || $dados_chamado["condominio_id"]) && $eh_editavel) || $dados_chamado["chamado_pai"]) {
          $condominio_id = $dados_chamado["cliente_id"] ? $this->chamados->buscaCondominioCliente($dados_chamado["cliente_id"]) : $dados_chamado["condominio_id"];
          $chamados_possiveis = $this->chamados->buscaChamadosPaiPossiveis($_SESSION["usuario"], $dados_chamado["cliente_id"], $condominio_id);
          if ($chamados_possiveis) {
            $conteudo = str_replace("[exibir_chamado_pai]", "", $conteudo);
            $conteudo = str_replace("[chamado_pai]", $this->geraSelect($chamados_possiveis, $this->chamados->buscaValorChamadoPai($dados_chamado["chamado_pai"])), $conteudo);
          }
        }
        if (!$chamados_possiveis) {
          $conteudo = str_replace("[exibir_chamado_pai]", "display:none", $conteudo);
        }
        $chamados_filho = $this->chamados->buscaChamadosFilho($chamado_id);
        if ($chamados_filho) {
          $conteudo = str_replace("[exibir_chamados_filho]", "", $conteudo);
          $conteudo = str_replace("[listagem_chamados_filho]", $this->chamados->geraListagemChamados($chamados_filho), $conteudo);
        } else {
          $conteudo = str_replace("[exibir_chamados_filho]", "display:none", $conteudo);
        }
        $conteudo = str_replace("[abertura]", $dados_chamado["abertura"]." por <span style=\"color:#232323\">".$this->chamados->buscaUsuarioEtapa($dados_chamado["id"], 1, "usuario_nome")."</span>", $conteudo);
        $conteudo = str_replace("[encaminhamento]", $dados_chamado["encaminhamento"]." por <span style=\"color:#232323\">".$this->chamados->buscaUsuarioEtapa($dados_chamado["id"], 3, "usuario_nome")."</span>", $conteudo);
        $conteudo = str_replace("[fechamento]", $dados_chamado["fechamento"] ? $dados_chamado["fechamento"]." por <span style=\"color:#232323\">".$this->chamados->buscaUsuarioEtapa($dados_chamado["id"], 6, "usuario_nome")."</span>" : "", $conteudo);
        $conteudo = str_replace("[duracao]", $dados_chamado["duracao_setor"] != $dados_chamado["duracao"] && $dados_chamado["status_id"] == 1 ? "{$dados_chamado["duracao_setor"]} <span style=\"color:#232323\">({$dados_chamado["duracao"]})</span>" : $dados_chamado["duracao"], $conteudo);
        $conteudo = str_replace("[resumida_checked]", array_key_exists("chamados_etapas_detalhamento", $_SESSION["instancias"][$this->instancia]) && $_SESSION["instancias"][$this->instancia]["chamados_etapas_detalhamento"] ? "" : "checked", $conteudo);
        $conteudo = str_replace("[detalhada_checked]", array_key_exists("chamados_etapas_detalhamento", $_SESSION["instancias"][$this->instancia]) && $_SESSION["instancias"][$this->instancia]["chamados_etapas_detalhamento"] ? "checked" : "", $conteudo);
        $conteudo = str_replace("[etapas]", $this->chamados->geraListagemEtapas($this->chamados->buscaEtapas($dados_chamado["id"])), $conteudo);
        $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($dados_chamado["categoria_id"], $dados_chamado["etapa_atual"], $dados_chamado["id"], $eh_editavel), $conteudo);
        $conteudo = str_replace("[editavel]", $eh_editavel ? "" : "disabled", $conteudo);
        $conteudo = str_replace("[status]", $dados_chamado["status_nome"], $conteudo);
        // Botões
        if ($this->chamados->ehLiberavelTecnico($dados_chamado)) $botoes[] = '<input type="button" id="liberar_tecnico" class="buttonAzulEscuroBranco11" value="Liberar Técnico" onclick="executaAcao(\'liberar_tecnico\', 1)">';
        if ($this->chamados->ehIniciavelAcompanhamento($dados_chamado)) $botoes[] = '<input type="button" id="acompanhar" style="width:150px" class="buttonAzulEscuroBranco11" value="Iniciar Acompanhamento" onclick="executaAcao(\'acompanhar\', 1)">';
        if ($this->chamados->ehSolicitavelFechamento($dados_chamado)) $botoes[] = '<input type="button" id="solicitar_fechamento" class="buttonAzulEscuroBranco11" value="Solicitar Fechamento" onclick="executaAcao(\'solicitar_fechamento\', 1)">';
        if ($this->chamados->ehCancelavelAcompanhamento($dados_chamado)) $botoes[] = '<input type="button" style="width:170px" id="parar_de_acompanhar" class="buttonAzulEscuroBranco11" value="Cancelar Acompanhamento" onclick="executaAcao(\'acompanhar\', 1)">';
        if ($this->chamados->ehFechavel($dados_chamado)) $botoes[] = '<input type="button" id="fechar" style="width:110px" class="buttonAzulEscuroBranco11" value="Fechar Chamado" onclick="executaAcao(\'fechar_chamado\', 1)">';
        if ($this->chamados->ehReabrivel($dados_chamado)) $botoes[] = '<input type="button" id="reabrir" class="buttonAzulEscuroBranco11" value="Reabrir Chamado" onclick="executaAcao(\'reabrir_chamado\', 1)">';
        if ($this->chamados->ehImprimivel($dados_chamado)) $botoes[] = '<input type="button" id="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onclick="imprimeChamado()">';
        if ($this->chamados->ehAgendavelVisitaTecnica($dados_chamado)) $botoes[] = '<input type="button" id="agendar_visita" class="buttonAzulEscuroBranco11" value="Agendar Visita Tecnica" onclick="agendarVisitaTecnica()">';
        if ($this->chamados->ehDesagendavelVisitaTecnica($dados_chamado)) $botoes[] = '<input type="button" id="desagendar_visita" class="buttonAzulEscuroBranco11" value="Desagendar Visita Tecnica" onclick="desagendarVisitaTecnica()">';
        if ($this->permissoes["chamados"]["remover"]) $botoes[] = '<input type="button" id="remover_chamado" class="buttonAzulEscuroBranco11" value="Remover Chamado" onclick="removerChamado()">';
        $botoes[] = '<input type="button" id="salvar_chamado" class="buttonAzulEscuroBranco11" value="Salvar" onclick="editarChamadoSalvar(1)">';
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        if ($retorno_tela) {
          if ($this->ehAlertaAgendamentoVisivel()) {
            print "exibeAlertaAgendamento();";
          } else {
    	      print "ocultaAlertaAgendamento();";
          } 
          $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
          print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
          if (!$atualizacao) {
            print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').scrollTop = 0;";
          }
          print "atualizaCaracteresRestantes();";
          print "chamado_acao_atual = '';";
          print "chamado_alterado = false;";
          // Atualiza cor Visualizado
          if ($visualizado) {
	          print "atualizaCorLinhaChamado($chamado_id);";
	          //$this->setaCorRegistro("#E8E8E8", $dados_chamado["cliente_id"], $dados_chamado["condominio_id"], $dados_chamado["id"]);
          	//print "alert(1);";
          }
          // Atualiza Icone Visualizações
          $visualizacao = $this->chamados->buscaIconeVisualizacoes($this->instancia, $chamado_id, true);
          if ($visualizacao) {
            $visualizacao["usuarios"] = implode("\\n\\n", str_replace("<br>", "\\n", $visualizacao["usuarios"]));
            print "if (document.getElementById('chamados_visualizacao_$chamado_id')) document.getElementById('chamados_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            print "if (document.getElementById('chamados_busca_visualizacao_$chamado_id')) document.getElementById('chamados_busca_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            print "if (document.getElementById('chamados_historico_visualizacao_$chamado_id')) document.getElementById('chamados_historico_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            print "if (document.getElementById('chamados_filhos_visualizacao_$chamado_id')) document.getElementById('chamados_filhos_visualizacao_$chamado_id').innerHTML = '".addslashes($visualizacao["icone"])."';";
            if (!$atualizacao && $_SESSION["instancias"][$this->instancia][$_SESSION["tela_atual"][$this->instancia]]["chamado_id"] != $chamado_id) {
              print "alert('Atenção!\\n\\nEste chamado está sendo visualizado pelo(s) usuário(s):\\n\\n{$visualizacao["usuarios"]}');";
            }
          } else {
            print "if (document.getElementById('chamados_visualizacao_$chamado_id')) document.getElementById('chamados_visualizacao_$chamado_id').innerHTML = '';";
            print "if (document.getElementById('chamados_busca_visualizacao_$chamado_id')) document.getElementById('chamados_busca_visualizacao_$chamado_id').innerHTML = '';";
            print "if (document.getElementById('chamados_historico_visualizacao_$chamado_id')) document.getElementById('chamados_historico_visualizacao_$chamado_id').innerHTML = '';";
            print "if (document.getElementById('chamados_filhos_visualizacao_$chamado_id')) document.getElementById('chamados_filhos_visualizacao_$chamado_id').innerHTML = '';";
          }
        } else {
          return $conteudo;
        }
      } else {
        $this->chamados->editarChamado($dados_chamado, $dados);
        if ($exibir_chamado) {
          print "chamado_alterado = false;";
          // Exibe Chamado
          $this->chamadoVer($chamado_id);
        }
      }
    } else {
      if ($retorno_tela) {
        print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '';";
        print "chamado_atual = '0';";
        print "chamado_acao_atual = '';";
        print "chamado_alterado = false;";
      }
    }
  }

  ##########################
  ### Chamado Acompanhar ###
  ##########################

  function chamadoAcompanhar($dados_situacao, $dados = null, $retorno_tela = true) {
  	$dados_chamado = $this->chamados->buscaDadosChamado($dados_situacao["chamado_atual"]);
    if ($dados_chamado) {
      $eh_acompanhada = $this->chamados->ehChamadoAcompanhado($dados_situacao["chamado_atual"], $_SESSION["usuario"]);
      if (!$dados) {
        $conteudo = $this->carregaArquivo("chamados_sistema.chamado.acompanhar.inc.php");
        $conteudo = str_replace("[acao]", ($eh_acompanhada ? "cancelar o acompanhamento" : "iniciar o acompanhamento"), $conteudo);
        $conteudo = str_replace("[codigo]", $dados_chamado["codigo"], $conteudo);
        $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($dados_chamado["categoria_id"], $dados_chamado["etapa_atual"], $dados_chamado["id"], false), $conteudo);
        $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="executaAcao(\'acompanhar\', 1)">';
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
        if ($retorno_tela) {
          print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
          print "atualizaCaracteresRestantes();";
        } else {
          return $conteudo;
        }
      } else {
        if (!$eh_acompanhada) {
          // Registra Acompanhamento
          $this->chamados->registraEtapa($dados_chamado, 8, $dados);
          $acompanhamentos[] = "Início de Acompanhamento";
        } else {
          // Registra Cancelamento de Acompanhamento
          $this->chamados->registraEtapa($dados_chamado, 9, $dados);
          $acompanhamentos[] = "Cancelamento de Acompanhamento";
        }
        if ($dados["conteudo"]) {
          $this->chamados->registraEtapa($dados_chamado, 5, $dados);
          $acompanhamentos[] = "Comentario (Interno)";
        }
        // Envia Acompanhamento
        if ($acompanhamentos && $usuarios = $this->chamados->buscaUsuariosAcompanhamentos($dados_chamado["id"])) {
          $this->chamados->enviaAcompanhamentos($dados_chamado, $acompanhamentos, $usuarios);
        }
        // Visualiza Chamado
        if ($retorno_tela) {
          print "chamado_alterado = false;";
          $this->chamadoVer($dados_situacao["chamado_atual"]);
        }
      }
    }
  }

  ####################################
  ### Chamado Solicitar Fechamento ###
  ####################################

  function chamadoSolicitarFechamento($dados_situacao, $dados = null, $retorno_tela = true) {
  	$dados_chamado = $this->chamados->buscaDadosChamado($dados_situacao["chamado_atual"]);
    if ($dados_chamado) {
      $eh_acompanhada = $this->chamados->ehChamadoAcompanhado($dados_situacao["chamado_atual"], $_SESSION["usuario"]);
      if (!$dados) {
        $conteudo = $this->carregaArquivo("chamados_sistema.chamado.solicitar_fechamento.inc.php");
        $conteudo = str_replace("[codigo]", $dados_chamado["codigo"], $conteudo);
      	$conteudo = str_replace("[responsavel_fechamento]", $this->geraSelect($this->chamados->buscaResponsaveisSetor($_SESSION["usuario"])), $conteudo);
        $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="executaAcao(\'solicitar_fechamento\', 1)">';
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
        if ($retorno_tela) {
          print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
          print "atualizaCaracteresRestantes();";
        } else {
          return $conteudo;
        }
      } else {
        if ($dados["responsavel_fechamento"]) {
          $this->chamados->registraEtapa($dados_chamado, 16, $dados);
          $acompanhamentos[] = "Comentario (Interno)";
        }
        // Visualiza Chamado
        if ($retorno_tela) {
          print "chamado_alterado = false;";
          $this->chamadoVer($dados_situacao["chamado_atual"]);
        }
      }
    }
  }

  #######################
  ### Liberar Técnico ###
  #######################

  function liberarTecnico($dados_situacao, $dados = null, $retorno_tela = true) {
  	$dados_chamado = $this->chamados->buscaDadosChamado($dados_situacao["chamado_atual"]);
    if ($dados_chamado) {
      if (!$dados) {
        $conteudo = $this->carregaArquivo("chamados_sistema.chamado.liberar_tecnico.inc.php");
        $conteudo = str_replace("[codigo]", $dados_chamado["codigo"], $conteudo);
        $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($dados_chamado["categoria_id"], $dados_chamado["etapa_atual"], $dados_chamado["id"], false, 20), $conteudo);
        $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="executaAcao(\'liberar_tecnico\', 1)">';
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
        if ($retorno_tela) {
          print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
          print "atualizaCaracteresRestantes();";
        } else {
          return $conteudo;
        }
      } else {
        // Registra Etapa
        $this->chamados->dados_item = $dados_chamado;
        $this->chamados->registraEtapa($dados_chamado, 20, $dados);
        // Visualiza Chamado
        if ($retorno_tela) {
          print "chamado_alterado = false;";
          $this->chamadoVer($dados_situacao["chamado_atual"]);
        }
      }
    }
  }
  
  ######################
  ### Chamado Fechar ###
  ######################

  function chamadoFechar($dados_situacao, $dados = null, $retorno_tela = true) {
    $dados_chamado = $this->chamados->buscaDadosChamado($dados_situacao["chamado_atual"]);
    if ($dados_chamado && $this->chamados->ehFechavel($dados_chamado)) {
    	$pendencias = $this->chamados->buscaEtapasPendentes($dados_chamado["id"]);
      if ($pendencias) {
        $conteudo = $this->carregaArquivo("chamados_sistema.chamado.pendencias.inc.php");
        $conteudo = str_replace("[pendencias]", implode("<br>", $pendencias), $conteudo);
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
        if ($retorno_tela) {
          print "if (document.getElementById(\"dados_chamado\")) document.getElementById(\"dados_chamado\").innerHTML = '".addslashes($conteudo)."';";
        } else {
          return $conteudo;
        }
      } else {
        if (!$this->chamados->possuiPermissaoSolicitacaoFechamento($dados_situacao["chamado_atual"], $_SESSION["usuario"])) {
          if (!$dados) {
            $conteudo = $this->carregaArquivo("chamados_sistema.chamado.encaminhar.inc.php");
            $conteudo = str_replace("[codigo]", $dados_chamado["codigo"], $conteudo);
            $conteudo = str_replace("[andamento]", $this->geraSelect($this->chamados->buscaAndamentos($dados_chamado["andamento"]), $dados_chamado["andamento"]), $conteudo);
            $conteudo = str_replace("[responsavel]", $this->chamados->buscaResponsavelFechamento($dados_situacao["chamado_atual"]), $conteudo);
            $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($dados_chamado["categoria_id"], $dados_chamado["etapa_atual"], $dados_chamado["id"], false), $conteudo);
            $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
            $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="executaAcao(\'fechar_chamado\', 1, \'\', true)">';
            $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
            $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
            if ($retorno_tela) {
              print "if (document.getElementById(\"dados_chamado\")) document.getElementById(\"dados_chamado\").innerHTML = '".addslashes($conteudo)."';";
            } else {
              return $conteudo;
            }
          } else {
            $dados["responsavel"] = $this->chamados->buscaResponsavelFechamento($dados_situacao["chamado_atual"], "id");
            // Registro de Andamento
            if ($dados["andamento"] && $dados["andamento"] != $dados_chamado["andamento"]) {
              $this->chamados->registraEtapa($dados_chamado, 15, $dados);
              $acompanhamentos[] = "Andamento";
            }
            // Registro de Encaminhamento
            $this->chamados->registraEtapa($dados_chamado, 3, $dados);
            $acompanhamentos[] = "Encaminhamento";
            // Registro de Comentário
            if ($dados["conteudo"]) {
              $this->chamados->registraEtapa($dados_chamado, $dados["tipo"], $dados);
              $acompanhamentos[] = $this->chamados->buscaEtapaTipoNome($dados["tipo"]);
            }
            // Envia Acompanhamento
            if ($acompanhamentos && $usuarios = $this->chamados->buscaUsuariosAcompanhamentos($dados_chamado["id"])) {
              $this->chamados->enviaAcompanhamentos($dados_chamado, $acompanhamentos, $usuarios);
            }
            // Visualiza Chamado
            if ($retorno_tela) {
              print "chamado_alterado = false;";
              $this->chamadoVer($dados_situacao["chamado_atual"]);
            }
          }
        } elseif (!$dados) {
          $conteudo = $this->carregaArquivo("chamados_sistema.chamado.fechar.inc.php");
          $conteudo = str_replace("[codigo]", $dados_chamado["codigo"], $conteudo);
          $conteudo = str_replace("[andamento]", $this->geraSelect($this->chamados->buscaAndamentos($dados_chamado["andamento"]), $dados_chamado["andamento"]), $conteudo);
          $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($dados_chamado["categoria_id"], $dados_chamado["etapa_atual"], $dados_chamado["id"], false), $conteudo);
          $conteudo = str_replace("[mensagem]", $dados_chamado["aguardando_retorno"] ? $this->buscaMensagemAguardandoRetorno($dados_chamado) : "", $conteudo);
          $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="executaAcao(\'fechar_chamado\', 1)">';
          $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
          $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
          $conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=fechar&i={$_GET["i"]}", $conteudo);
          $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
          if ($retorno_tela) {
            print "if (document.getElementById(\"dados_chamado\")) document.getElementById(\"dados_chamado\").innerHTML = '".addslashes($conteudo)."';";
            print "atualizaCaracteresRestantes();";
          } else {
            return $conteudo;
          }
        } else {
          // Registro de Andamento
          if ($dados["andamento"] && $dados["andamento"] != $dados_chamado["andamento"]) {
            $this->chamados->registraEtapa($dados_chamado, 15, $dados);
            $acompanhamentos[] = "Andamento";
          }
          // Registro de Comentário
          if ($dados["conteudo"]) {
            $this->chamados->registraEtapa($dados_chamado, $dados["tipo"], $dados);
            $acompanhamentos[] = $this->chamados->buscaEtapaTipoNome($dados["tipo"]);
          }
          // Registro de Fechamento
          $this->chamados->registraEtapa($dados_chamado, 6, $dados);
          $acompanhamentos[] = "Fechamento";
          // Envia Acompanhamento
          if ($acompanhamentos && $usuarios = $this->chamados->buscaUsuariosAcompanhamentos($dados_chamado["id"])) {
            $this->chamados->enviaAcompanhamentos($dados_chamado, $acompanhamentos, $usuarios);
          }

          // Visualiza Chamado
          if ($retorno_tela) {
            print "chamado_alterado = false;";
            $this->chamadoVer($dados_situacao["chamado_atual"]);
          }
        }
      }
    }
  }
  
  function buscaMensagemAguardandoRetorno($dados_chamado) {
  	$mensagem = "
  	  <tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
  	    <td colspan=\"2\" style=\"padding:10px;text-align:center\">
  	      <span style=\"color:red\">Este chamado possui retorno agendado para {$dados_chamado["data_hora_retorno"]}.<br>
  	        Se prosseguir, o retorno será desagendado.
  	      </span>
  	    </td>
  	  </tr>";
  	return $mensagem;
  }
  
  #######################
  ### Chamado Reabrir ###
  #######################

  function chamadoReabrir($dados_situacao, $dados = null, $retorno_tela = true) {
    $dados_chamado = $this->chamados->buscaDadosChamado($dados_situacao["chamado_atual"]);
    if ($dados_chamado && $this->chamados->ehReabrivel($dados_chamado)) {
    	if (!$dados) {
        $conteudo = $this->carregaArquivo("chamados_sistema.chamado.reabrir.inc.php");
        $conteudo = str_replace("[codigo]", $dados_chamado["codigo"], $conteudo);
        $conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($dados_chamado["categoria_id"], $dados_chamado["etapa_atual"], $dados_chamado["id"], false), $conteudo);
        $conteudo = str_replace("[mensagem]", $dados_chamado["aguardando_retorno"] ? $this->buscaMensagemAguardandoRetorno($dados_chamado) : "", $conteudo);
        $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="executaAcao(\'reabrir_chamado\', 1)">';
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        $conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=reabrir&i={$_GET["i"]}", $conteudo);
        $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
        if ($retorno_tela) {
          print "if (document.getElementById(\"dados_chamado\")) document.getElementById(\"dados_chamado\").innerHTML = '".addslashes($conteudo)."';";
          print "atualizaCaracteresRestantes();";
        } else {
          return $conteudo;
        }
      } else {
        // Registro de Comentário
        if ($dados["conteudo"]) {
          $this->chamados->registraEtapa($dados_chamado, $dados["tipo"], $dados);
          $acompanhamentos[] = $this->chamados->buscaEtapaTipoNome($dados["tipo"]);
        }
        // Registro de Reabertura
        $this->chamados->registraEtapa($dados_chamado, 26, $dados);
        $acompanhamentos[] = "Reabertura";
        // Envia Acompanhamento
        if ($acompanhamentos && $usuarios = $this->chamados->buscaUsuariosAcompanhamentos($dados_chamado["id"])) {
          $this->chamados->enviaAcompanhamentos($dados_chamado, $acompanhamentos, $usuarios);
        }
        // Visualiza Chamado
        if ($retorno_tela) {
          print "chamado_alterado = false;";
          $this->chamadoVer($dados_situacao["chamado_atual"]);
        }
      }
    }
  }
  
  ##################################
  ### Remover Etapa (Comentário) ###
  ##################################

  function removerEtapa($etapa_id, $atualiza_listagem = true) {
    $query = "
      SELECT
        che_lida AS lida
      FROM
        tab_chamados_etapas
      WHERE
        che_id = $etapa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados && (!$dados["lida"] || $this->permissoes["chamados"]["remover_etapa_plus"])) {
  	$etapas_filhas = $this->chamados->buscaEtapasFilhas($etapa_id);
    if ($etapas_filhas) {
      foreach ($etapas_filhas as $etapa_filha_id) {
        $this->removerEtapa($etapa_filha_id, false);
      }
    }
    $query = "DELETE FROM tab_chamados_etapas WHERE che_id = $etapa_id";
    $this->bancodedados->query($query);
    // Atualiza Listagem de Etapas
    if ($atualiza_listagem) {
      print "buscarEtapasChamado();";
    }
  }
  }

  #################################
  ### Editar Etapa (Comentário) ###
  #################################

  function editarEtapa($dados_situacao, $etapa_id, $dados = null, $retorno_tela = true) {
  	$dados_etapa = $this->chamados->buscaDadosEtapa($etapa_id);
    if ($dados_etapa) {
      if (!$dados) {
        $conteudo .= $this->carregaArquivo("chamados_sistema.chamado.editar_etapa.inc.php");
        // Ações
        $acoes = '<td bgcolor="#505050"><b>&nbsp;Editar Etapa&nbsp;</b></td>';
        $conteudo = str_replace("[acoes]", $acoes, $conteudo);
        // Botões
        $botoes[] = '<input type="button" id="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onclick="editarEtapa('.$etapa_id.', 1, 1)">';
        $botoes[] = '<input type="button" id="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="navegacaoIr(-1)">';
        $conteudo = str_replace("[botoes]", implode(" ", $botoes), $conteudo);
        $conteudo = preg_replace("/[ \r\n\t]+/", " ", $conteudo);
        if ($retorno_tela) {
          $conteudo = str_replace("[conteudo]", "", $conteudo);
          print "if (document.getElementById('dados_chamado')) document.getElementById('dados_chamado').innerHTML = '".addslashes($conteudo)."';";
          print "if (document.getElementById('conteudo')) document.getElementById('conteudo').value = unescape('".str_replace("+", " ", urlencode($dados_etapa["conteudo"]["conteudo"]))."');";
          print "atualizaCaracteresRestantes();";
        } else {
          $conteudo = str_replace("[conteudo]", $dados_etapa["conteudo"]["conteudo"], $conteudo);
          return $conteudo;
        }
      } else {
        $this->chamados->editaEtapaConteudo($etapa_id, $dados);
        // Visualiza Chamado
        if ($retorno_tela) {
          print "chamado_alterado = false;";
          $this->chamadoVer($dados_situacao["chamado_atual"]);
        }
      }
    }
  }

  #############################
  ### Favoritos - Adicionar ###
  #############################

  function favoritosAdicionar($chamado_id, $usuario_id, $atualiza_busca, $retorno_tela = true) {
    $query = "INSERT INTO rel_cha_usu_ehfavorito (cha_id, usu_id) VALUES ($chamado_id, $usuario_id)";
    $this->bancodedados->query($query);
    if ($retorno_tela) {
      print 'if (document.getElementById("favoritos_chamado_'.$chamado_id.'")) document.getElementById("favoritos_chamado_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_remover.png" id="favoritos_icone" width="13" height="13" border="0" title="Remover de Favoritos" onclick="favoritosRemover('.$chamado_id.')">\';';
      print 'if (document.getElementById("favoritos_historico_'.$chamado_id.'")) document.getElementById("favoritos_historico_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_remover.png" id="favoritos_icone" width="13" height="13" border="0" title="Remover de Favoritos" onclick="favoritosRemover('.$chamado_id.')">\';';
      print 'if (document.getElementById("favoritos_filho_'.$chamado_id.'")) document.getElementById("favoritos_filho_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_remover.png" id="favoritos_icone" width="13" height="13" border="0" title="Remover de Favoritos" onclick="favoritosRemover('.$chamado_id.')">\';';
      if ($atualiza_busca) {
        $tela_atual = $this->buscaTelaAtual($this->instancia, 0);
        $this->geraResultadoFavoritos(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, true);
      } else {
        print 'if (document.getElementById("favoritos_'.$chamado_id.'")) document.getElementById("favoritos_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_remover.png" id="favoritos_icone" width="13" height="13" border="0" title="Remover de Favoritos" onclick="favoritosRemover('.$chamado_id.')">\';';
      }
    }
  }

  ###########################
  ### Favoritos - Remover ###
  ###########################

  function favoritosRemover($chamado_id, $usuario_id, $atualiza_busca, $retorno_tela = true) {
    $query = "DELETE FROM rel_cha_usu_ehfavorito WHERE cha_id = $chamado_id AND usu_id = $usuario_id";
    $this->bancodedados->query($query);
    if ($retorno_tela) {
      print 'if (document.getElementById("favoritos_chamado_'.$chamado_id.'")) document.getElementById("favoritos_chamado_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_adicionar.png" id="favoritos_icone" width="13" height="13" border="0" title="Adicionar ao Favoritos" onclick="favoritosAdicionar('.$chamado_id.')">\';';
      print 'if (document.getElementById("favoritos_historico_'.$chamado_id.'")) document.getElementById("favoritos_historico_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_adicionar.png" id="favoritos_icone" width="13" height="13" border="0" title="Adicionar ao Favoritos" onclick="favoritosAdicionar('.$chamado_id.')">\';';
      print 'if (document.getElementById("favoritos_filho_'.$chamado_id.'")) document.getElementById("favoritos_filho_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_adicionar.png" id="favoritos_icone" width="13" height="13" border="0" title="Adicionar ao Favoritos" onclick="favoritosAdicionar('.$chamado_id.')">\';';
      if ($atualiza_busca) {
        $tela_atual = $this->buscaTelaAtual($this->instancia, 0);
        $this->geraResultadoFavoritos(null, $tela_atual["pagina_busca"], $tela_atual["chamado_id"], true, true);
      } else {
        print 'if (document.getElementById("favoritos_'.$chamado_id.'")) document.getElementById("favoritos_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_adicionar.png" id="favoritos_icone" width="13" height="13" border="0" title="Adicionar ao Favoritos" onclick="favoritosAdicionar('.$chamado_id.')">\';';
      }
    }
  }

  ###################################
  ### Registro de Telas na Sessão ###
  ###################################

  function registraTela($instancia, $cliente_id, $cliente_condominio_id, $condominio_id, $chamado_id, $chamado_acao, $chamado_etapa_id, $aba_busca, $dados_filtro, $aba_historico, $aba_resumo, $painel_historico, $pagina_busca, $atualiza_barra_navegacao = true) {
    $dados["cliente_id"] = $cliente_id;
    $dados["cliente_condominio_id"] = $cliente_condominio_id;
    $dados["condominio_id"] = $condominio_id;
    $dados["chamado_id"] = $chamado_id;
    $dados["chamado_acao"] = $chamado_acao;
    $dados["chamado_etapa_id"] = $chamado_etapa_id;
    $dados["aba_busca"] = $aba_busca;
    $dados["aba_historico"] = $aba_historico;
    $dados["aba_resumo"] = $aba_resumo;
    $dados["painel_historico"] = $painel_historico;
    $dados["pagina_busca"] = $pagina_busca;
    if ($dados_filtro) {
      $dados["busca_{$aba_busca}"] = $dados_filtro;
    }
    $dados_atual = $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]];
    // Registra Tela Atual
    if ($dados_atual != $dados) {
      // Apaga as Telas Posteriores à Atual
      for ($i = $_SESSION["tela_atual"][$instancia] + 1; $i <= count($_SESSION["instancias"][$instancia]) + 1; $i++) {
        unset($_SESSION["instancias"][$instancia][$i]);
      }
      // Registra Nova Tela
      $_SESSION["tela_atual"][$instancia] += 1;
      $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]] = $dados;
    }
    // Limpa anteriores a 100
    while (count($_SESSION["instancias"][$instancia]) > 100) {
      reset($_SESSION["instancias"][$instancia]);
      unset($_SESSION["instancias"][$instancia][key($_SESSION["instancias"][$instancia])]);
    }
    // Atualiza Barra Navegação
    if ($atualiza_barra_navegacao) {
      $this->atualizaBarraNavegacao($instancia);
    }
    // Atualiza Registro Chamado Bloqueado
    $this->chamados->registraChamadoVisualizacao($instancia, $chamado_id);
  }

  ################################
  ### Busca de Telas na Sessão ###
  ################################

  function buscaTelaAtual($instancia, $referencia = null) {
    if ($referencia !== null && $referencia !== 'h' && $_SESSION["instancias"][$instancia] && array_key_exists($_SESSION["tela_atual"][$instancia] + $referencia, $_SESSION["instancias"][$instancia])) {
      $_SESSION["tela_atual"][$instancia] += $referencia;
    } else {
      $this->registraTela($instancia, "0", "0", "0", "0", "", "0", "chamados", null, "historico_geral", "dados_resumo", "true", 1, false);
    }
    return $_SESSION["instancias"][$instancia][$_SESSION["tela_atual"][$instancia]];
  }
	  
	function geraQueryTexto($texto, $field) {
		
		$blacklist = array("o", "a", "e", "de", "do", "dos", "di", "da", "das");
		//$logradouros = array("rua", "estrada", "rodovia", "praça", "avenida", "alameda", "beco", "loteamento", "via", "vila", "travessa", "servidao", "passagem", "parque", "largo", "jardim", "passeio");
		$logradouros = array("alameda", "aeroporto", "área", "avenida", "campo", "chácara", "colônia", "condomínio", "conjunto", "distrito", "esplanada", "estação", "estrada", "favela", "fazenda", "feira", "jardim", "ladeira", "lago", "lagoa", "largo", "loteamento", "morro", "núcleo", "parque", "passarela", "pátio", "praça", "quadra", "recanto", "residencial", "rua", "rodovia", "servidão", "setor", "sítio", "travessa", "trecho", "trevo", "vale", "vereda", "via", "viaduto", "viela", "vila");
		
		$texto = mb_strtolower($texto);
		$texto = preg_replace("/[ç]/", "c", $texto);
		$texto = preg_replace("/[ñ]/", "n", $texto);
		$texto = preg_replace("/[áàâãä]/", "a", $texto);
		$texto = preg_replace("/[éèêë]/", "e", $texto);
		$texto = preg_replace("/[íìîï]/", "i", $texto);
		$texto = preg_replace("/[óòôõö]/", "o", $texto);
		$texto = preg_replace("/[úùûü]/", "u", $texto);
		$texto = trim(preg_replace("/[^a-z0-9]+/", " ", $texto));
		$texto = explode(" ", $texto);
		
		$tokens = array();
		
		for ($i = 0; $i < count($texto); $i++) {
			if (!in_array($texto[$i], $blacklist) && ($i != 0 || !in_array($texto[$i], $logradouros))) {
				$tokens[] = "{$field} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}'";
			}
		}
		
		return implode(" AND ", $tokens);
		
	}
	
	function geraExpressao($token) {

		$origem  = array(
			"/(.)\\1+/",
			"/nh/",
			"/([^p])h/",
			"/s$/",
			"/(.)/",
			"/([aeiou]) l ([b-df-hj-np-tv-z])/",
			"/(g n)/",
			"/(d t|t d|t) $/",
			"/(f|p h) /",
			"/([vw] )+/",
			"/([mn] )+/",
			"/(([jg]|gu) )+/",
			"/(([cszxkq]|qu) )+/",
			"/a /",
			"/[eiy]+ /",
			"/[ou]+ /",
			"/([a-z0-9]) /",
			"/ +/",
			"/$/"
		);
		
		$destino = array(
			"$1",
			"gn",
			"$1",
			"",
			"$1 ",
			"$1 (h?[lu])+ $2",
			" (gn|nh) ",
			" (t+|dt|td) ",
			" (f+|ph) ",
			" (h?[vw])+ ",
			" (h?[mn])+ ",
			" (h?[jgu])+ ",
			" (h?[cszxkquç])+ ",
			"(h?[aáàâãä])+ ",
			"(h?[eéèêëiíìîïy])+ ",
			"(h?[oóòôõöuúùûü])+ ",
			"(h?$1)+ ",
			"",
			"h?"
		);		

		return preg_replace($origem, $destino, $token);
		
	}

}

?>
