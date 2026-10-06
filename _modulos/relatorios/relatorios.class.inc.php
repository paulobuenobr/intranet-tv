<?

class relatorios {

  var $bancodedados;
  var $permissoes;
  var $acao;
  var $conteudo;
  var $dados_item;
  var $dados_modulo;
  var $dados_acao;

  function __construct($bancodedados, $permissoes, $executar = true) {
    set_time_limit(0);
    if ($_POST || $_GET["POST"]) {
      $id = md5(uniqid(rand(), true));
      $_SESSION["POST"][$id] = $_POST ? $_POST : $_GET["POST"];
      header("Location: ./?m={$_GET["m"]}&a={$_GET["a"]}&i=$id");
      exit;
    } elseif ($_GET["i"] && $_SESSION["POST"][$_GET["i"]]) {
      $_POST = $_SESSION["POST"][$_GET["i"]];
    }
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      $_GET["a"] = $_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"] ? "editar" : $_GET["a"];
      $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function geraTitulo() {
    $titulo  = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
    $titulo .= '<tr align="center" class="txtBranco11">';
    $titulo .= '<td bgcolor="#505050"><b>';
    $titulo .= $this->dados_modulo["descricao"];
    $titulo .= '</b></td></tr></table>';
    return $titulo;
  }

  function carregaArquivo($arquivo) {
    if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
    $fp = fopen($arquivo, "r");
    $retorno = fread($fp, filesize($arquivo));
    fclose($fp);
    return $retorno;
  }

  function buscaDadosModulo($nome) {
    $query = "SELECT mod_id AS id, mod_nome AS nome, mod_descricao AS descricao FROM tab_modulos WHERE mod_nome = '$nome'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      $this->dados_modulo = $this->bancodedados->resultadoArray($query);
    }
  }

  function buscaDadosAcao($nome) {
    $query = "SELECT aco_id AS id, aco_nome AS nome, aco_descricao AS descricao, act_id AS tipo FROM tab_acoes WHERE aco_nome = '$nome' and mod_id = '{$this->dados_modulo["id"]}'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      $this->dados_acao = $this->bancodedados->resultadoArray($query);
      $this->acao = $this->dados_acao["nome"];
    }
  }

  #############
  ### Geral ###
  #############

  function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
    if (!$valor && !$exibir_nulo) return false;
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }

  function geraArquivoCSV($campos, $linhas, $resumido = false, $separador = ";") {
    foreach ($campos as $chave => $descricao) {
      if (is_array($descricao)) {
        foreach ($descricao as $sub_descricao) {
          if ($resumido) {
            $cabecalho[] = "\"".addslashes($sub_descricao)."\"";
          } else {
            $cabecalho[] =  "\"".addslashes("$chave » $sub_descricao")."\"";
          }
        }
      } else {
        $cabecalho[] = "\"".addslashes($descricao)."\"";
      }
    }
    $arquivo_csv[] = @implode($separador, $cabecalho);
    do {
      unset($linhas[key($linhas)]["cor_linha"]);
      $linha_atual = current($linhas);
      $linha_atual_tratada = array();
      foreach ($campos as $chave => $descricao) {
        if (is_array($descricao)) {
          foreach ($descricao as $descricao_chave => $descricao_nome) {
            $linha_atual_tratada[$descricao_chave] = "\"".addslashes($linha_atual[$descricao_chave])."\"";
          }
        } else {
          $linha_atual_tratada[$chave] = "\"".addslashes($linha_atual[$chave])."\"";
        }
      }
      $arquivo_csv[] = @implode($separador, $linha_atual_tratada);
    } while (next($linhas));
    $arquivo_csv = strip_tags(str_replace("<br>", " ", implode("\n", $arquivo_csv)));
    return $arquivo_csv;
  }

  function estaNoIntervalo($valor, $inferior, $superior) {
    if ($inferior && $valor < $inferior) return false;
    if ($superior && $valor > $superior) return false;
    return true;
  }

  function buscaRelatorioPeriodo() {
    return $this->permissoes["relatorios"]["{$_GET["a"]}_plus"] ? "0" : RELATORIO_PERIODO;
  }

  ###############
  ### Visitas ###
  ###############

  function visitas() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $visitas = controle::instanciaObjeto("relatorios.visitas", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Visitas', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioVisitas(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $visitas->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $visitas),$this->conteudo);
    $periodos = $visitas->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    $this->conteudo = str_replace("[intervalos]", "", $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $visitas->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(), $this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace('[action]','?m=relatorios&a=visitas', $this->conteudo);
    if ($_POST) {
      // Opções Extras: Implementar nessa área
      if ($_POST["status"] == -1) $_POST["status"] = "1 OR tab_visitas.via_id = 2";
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $visitas->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos, $intervalos), $_POST);
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($_POST["campos"]);
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item["contador"] = intval($item["contador"]+1);
          for ($i = 0, $size = sizeof($campos_chaves); $i < $size; $i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/"," ",$item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["cliente_telefone"]) {
            $item["cliente_telefone"] = $visitas->buscaClienteTelefones($item_atual["cliente_id"]);
          }
          if ($_POST["campos"]["contrato_mensalidade"]) $item["contrato_mensalidade"] = $this->formataDinheiro($visitas->buscaContratoMensalidade($item_atual["contrato_id"]));
          if ($_POST["campos"]["contrato_pontos"]) $item["contrato_pontos"] = intval($visitas->buscaContratoPontos($item_atual["contrato_id"]));
          if ($_POST["campos"]["visita_comentarios"]) $item["visita_comentarios"] = $visitas->geraComentariosListagem($visitas->buscaVisitaComentarios($item_atual["visita_id"]));
          $itens[] = $item;
        }
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        // Retira as TAGs HTML do campo Contato
        for ($i = 0; $i < sizeof($itens); $i++) {
          $itens[$i]["visita_comentarios"] = strip_tags($itens[$i]["visita_comentarios"]);
        }
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }

  #################
  ### Contratos ###
  #################

  function contratos() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $contratos = controle::instanciaObjeto("relatorios.contratos", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Contratos', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioContratos(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $contratos->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $contratos), $this->conteudo);
    $periodos = $contratos->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos), $this->conteudo);
    $intervalos = $contratos->buscaIntervalos();
    $this->conteudo = str_replace('[intervalos]', $interface->geraIntervalosTemplate($intervalos), $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $contratos->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(), $this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=contratos', $this->conteudo);
    if ($_POST) {
      // Opções Extras: Implementar nessa área
      if ($_POST["bloqueio_dias"]) $_POST["bloqueio_dias"] = "DATE_ADD(tab_servicos_ajustes.saj_inicio, INTERVAL {$_POST["bloqueio_dias"]} DAY) <= NOW()";

      if ($_POST["status"] == "-1") $_POST["status"] = "1 OR tab_contratos.crs_id IN (1, 2, 11, 12, 13, 14, 15, 16, 17, 18)";
      if ($_POST["status"] == "-2") $_POST["status"] = "3 OR tab_contratos.crs_id IN (3, 4, 19, 20, 21)";
      if ($_POST["status"] == "22") $_POST["status"] = "5 OR tab_contratos.crs_id IN (5, 22)";
	  
      if ($_POST["data_cancelamento_inicio"] || $_POST["data_cancelamento_termino"]) $_POST["crs_id"] = "<> 23";
      if ($_POST["tecnologia"]) {
        $_POST["tecnologia"] = explode(",", $_POST["tecnologia"]);
        $_POST["tecnologia"] = $_POST["tecnologia"][0].($_POST["tecnologia"][1] ? " AND tab_condominios.coq_id = {$_POST["tecnologia"][1]}" : "");
      }
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $contratos->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos, $intervalos), $_POST);
      //var_dump($this->consulta);die();
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($campos);
        $cobranca_formatos = $contratos->buscaCobrancaFormatos();
        $cobranca_vencimentos = $contratos->buscaCobrancaVencimentos();
      	$item = array();
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item_atual["contador"] = $item["contador"] + 1;
          for ($i = 0; $i < sizeof($campos_chaves); $i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/"," ",$item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["cliente_id"] && $this->permissoes["clientes"]["ver_dc"]) $item["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dc&i={$item["cliente_id"]}\" class=\"linkAzulEscuro11\">{$item["cliente_id"]}</a>";
          if ($_POST["campos"]["contrato_plano_principal"]) $item["contrato_plano_principal"] = $contratos->buscaPlanoPrincipal($item_atual["contrato_id"], $_POST["servico"] );
          if ($_POST["campos"]["com_contrato"]) $item["com_contrato"] = $contratos->possuiContrato($item_atual["com_contrato"]);
          if ($_POST["campos"]["cliente_macs"]) $item["cliente_macs"] = $contratos->buscaMACs($item_atual["id"]);
          if ($_POST["campos"]["cliente_telefone"]) $item["cliente_telefone"] = $contratos->buscaClienteTelefones($item_atual["cliente_id"]);
          if ($_POST["campos"]["cliente_email"]) $item["cliente_email"] = $contratos->buscaClienteEmails($item_atual["cliente_id"]);
          if ($_POST["campos"]["qtde_chamados"]) $item["qtde_chamados"] = $contratos->buscaChamados($item_atual["cliente_id"], $_POST["chamados_abertura_inicio"], $_POST["chamados_abertura_termino"]);
          if ($_POST["campos"]["data_bloqueio"]) $item["data_bloqueio"] = $contratos->buscaDataBloqueio($item_atual["cliente_id"]);
          if ($_POST["campos"]["cliente_endereco"]) $item["cliente_endereco"] = $contratos->buscaEnderecoCliente($item_atual["cliente_id"]);
          if (is_array($contratos_parametros = @unserialize($item_atual["contrato_parametros"]))) {
            $item_atual["contrato_parametros"] = $contratos_parametros;
          } else {
            $item_atual["contrato_parametros"] = @explode(",",$item_atual["contrato_parametros"]);
            for ($i=0;$i<sizeof($item_atual["contrato_parametros"]);$i++) {
              $item_atual["contrato_parametros"][$i] = explode("=",$item_atual["contrato_parametros"][$i]);
              $contratos_parametros[$item_atual["contrato_parametros"][$i][0]] = $item_atual["contrato_parametros"][$i][1];
            }
            $item_atual["contrato_parametros"] = $contratos_parametros;
          }
          // Instalação
          if ($_POST["campos"]["contrato_instalacao_valor_original"]) $item["contrato_instalacao_valor_original"] = $this->formataDinheiro($contratos->buscaContratoInstalacaoValorOriginal($item_atual["contrato_parametros"]));
          if ($_POST["campos"]["contrato_instalacao_desconto_cv"]) $item["contrato_instalacao_desconto_cv"] = $contratos->retornaValorDesconto($contratos->buscaContratoInstalacaoDescontos($item_atual["contrato_parametros"],"cv"));
          if ($_POST["campos"]["contrato_instalacao_desconto_cv_descricao"]) $item["contrato_instalacao_desconto_cv_descricao"] = $contratos->buscaContratoInstalacaoDescontosDescricao($item_atual["contrato_parametros"],"cv");
          if ($_POST["campos"]["contrato_instalacao_desconto_sv"]) $item["contrato_instalacao_desconto_sv"] = $contratos->retornaValorDesconto($contratos->buscaContratoInstalacaoDescontos($item_atual["contrato_parametros"],"sv"));
          if ($_POST["campos"]["contrato_instalacao_desconto_sv_descricao"]) $item["contrato_instalacao_desconto_sv_descricao"] = $contratos->buscaContratoInstalacaoDescontosDescricao($item_atual["contrato_parametros"],"sv");
          if ($_POST["campos"]["contrato_instalacao_valor_final"]) $item["contrato_instalacao_valor_final"] = $this->formataDinheiro($item_atual["contrato_instalacao_valor_final"]);
          // Mensalidade
          if ($_POST["campos"]["contrato_mensalidade_valor_original"]) $item["contrato_mensalidade_valor_original"] = $this->formataDinheiro($contratos->buscaContratoMensalidadeValorOriginal($item_atual["contrato_id"]));
          if ($_POST["campos"]["contrato_mensalidade_desconto_cv"]) $item["contrato_mensalidade_desconto_cv"] = $contratos->retornaValorDesconto($contratos->buscaContratoMensalidadeDescontos($item_atual["contrato_parametros"],"cv"));
          if ($_POST["campos"]["contrato_mensalidade_desconto_cv_descricao"]) $item["contrato_mensalidade_desconto_cv_descricao"] = $contratos->buscaContratoMensalidadeDescontosDescricao($item_atual["contrato_parametros"],"cv");
          if ($_POST["campos"]["contrato_mensalidade_desconto_sv"]) $item["contrato_mensalidade_desconto_sv"] = $contratos->retornaValorDesconto($contratos->buscaContratoMensalidadeDescontos($item_atual["contrato_parametros"],"sv"));
          if ($_POST["campos"]["contrato_mensalidade_desconto_sv_descricao"]) $item["contrato_mensalidade_desconto_sv_descricao"] = $contratos->buscaContratoMensalidadeDescontosDescricao($item_atual["contrato_parametros"],"sv");
          if ($_POST["campos"]["contrato_mensalidade_valor_final"]) $item["contrato_mensalidade_valor_final"] = $this->formataDinheiro($contratos->buscaContratoMensalidadeValorFinal($item_atual["contrato_id"]));
          // Outros Campos
          if ($_POST["campos"]["contrato_pontos"]) $item["contrato_pontos"] = intval($contratos->buscaContratoPontos($item_atual["contrato_id"]));
          if ($_POST["campos"]["contrato_dominios"]) $item["contrato_dominios"] = intval($contratos->buscaContratoDominios($item_atual["contrato_id"]));
          if ($_POST["campos"]["contrato_multa"]) $item["contrato_multa"] = $this->formataDinheiro($contratos->buscaContratoMulta($item_atual["contrato_id"]));
          if ($_POST["campos"]["cobranca_formato"]) $item["cobranca_formato"] = $cobranca_formatos[$item_atual["cobranca_formato"]];
          if ($_POST["campos"]["cobranca_vencimento"]) $item["cobranca_vencimento"] = $cobranca_vencimentos[$item_atual["cobranca_vencimento"]];
          if ($_POST["campos"]["contrato_vigencia"]) $item["contrato_vigencia"] = $contratos->buscaContratoVigencia($item_atual["contrato_id"]);
          if ($_POST["campos"]["velocidade_maxima"]) $item["velocidade_maxima"] = $contratos->formataTamanho($item_atual["velocidade_maxima"]);
          // Filtra Vigência
          if ($_POST["vigencia_inferior"] || $_POST["vigencia_superior"]) {
            if ($this->estaNoIntervalo(intval($contratos->buscaContratoVigencia($item_atual["contrato_id"])), $_POST["vigencia_inferior"], $_POST["vigencia_superior"])) {
              $itens[] = $item;
            }
          } else {
            $itens[] = $item;
          }
        }
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens, 0, false, null, null, array("right"));
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }

  #################
  ### Cobranças ###
  #################

  function cobrancas() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $cobrancas = controle::instanciaObjeto("relatorios.cobrancas", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Cobranças', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioCobrancas(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $cobrancas->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $cobrancas),$this->conteudo);
    $periodos = $cobrancas->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    $intervalos = $cobrancas->buscaIntervalos();
    $this->conteudo = str_replace('[intervalos]', $interface->geraIntervalosTemplate($intervalos),$this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $cobrancas->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace("[periodo]", $this->buscaRelatorioPeriodo(), $this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace("[action]", '?m=relatorios&a=cobrancas', $this->conteudo);
    if ($_POST) {
      // Opções Extras: Implementar nessa área
      if ($_POST["formato_tipo"]) $_POST["formato_tipo"] = "tab_cobrancas_formatos.cft_id IN ({$_POST["formato_tipo"]})";
      if ($_POST["tipo"] == 1) $_POST["tipo"] = "tab_cobrancas.coa_pai IS NULL";
      if ($_POST["tipo"] == 2) $_POST["tipo"] = "tab_cobrancas.coa_pai IS NOT NULL";
      if ($_POST["status"] == "-1") $_POST["status"] = "2 OR tab_cobrancas.cot_id IN (3)";
      if ($_POST["status"] == "-2") $_POST["status"] = "4 OR tab_cobrancas.cot_id IN (5, 6, 7, 9)";
      if ($_POST["status"] == "-3") $_POST["status"] = "4 OR tab_cobrancas.cot_id IN (5, 9)";
      if ($_POST["status"] == "-4") $_POST["status"] = "2 OR tab_cobrancas.cot_id IN (3, 4, 5, 9)";
      if ($_POST["status"] ==  "1") $_POST["coa_pai"] = "IS NULL";
      if ($_POST["lote"] < 0) {
        if ($_POST["lote"] == "-1") $_POST["coa_pai"] = "IS NOT NULL";
        if ($_POST["lote"] == "-2") $_POST["col_id"] = "tab_cobrancas_lotes.col_id IS NULL";
        unset($_POST["lote"]);
      } else {
        if ($_POST["lote"]) $_POST["col_id"] = "tab_cobrancas_lotes.col_id = {$_POST["lote"]}";
      }
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      // Constrói e Executa Consulta
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $cobrancas->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos, $intervalos), $_POST);
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($campos);
        if ($_POST["campos"]["lote"]) $lotes = $cobrancas->buscaCobrancasLotes();
        if ($_POST["campos"]["status"]) $status = $cobrancas->buscaCobrancasStatus();
        $item = array();
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item_atual["contador"] = intval($item["contador"]+1);
          
          if(array_key_exists("nota_fiscal_numero",$item_atual)) {
              if(empty($item_atual['nota_fiscal_numero'])) {
                  $nota = $this->buscaNota($item_atual['cobranca_id']);

                  if($nota) {
                    $item_atual['nota_fiscal_numero'] = $nota['numero'];
                    if(array_key_exists("nota_fiscal_emissao",$item_atual)) {
                        $item_atual['nota_fiscal_emissao'] = $nota['emissao'];
                    }
                    if(array_key_exists("nota_fiscal_valor",$item_atual)) {
                        $item_atual['nota_fiscal_valor'] = $nota['servico_valores_valorServicos'];              
                    }
                    if(array_key_exists("nota_fiscal_tipo",$item_atual)) {
                        $item_atual['nota_fiscal_tipo'] = "Prefeitura";
                    }
                  }
              }
          }
          
          for ($i=0;$i<sizeof($campos_chaves);$i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/"," ",$item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["lote"]) {
            if ($item_atual["lote"]) {
              $item["lote"] = $lotes[$item_atual["lote"]];
            } else {
              $item["lote"] = "Cobranças Avulsas";
            }
            if ($item_atual["coa_pai"]) {
              $item["lote"] .= " (Segunda Via)";
            }
          }
          if ($_POST["campos"]["cliente_id"] && $this->permissoes["clientes"]["ver_dc"]) $item["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dc&i={$item["cliente_id"]}\" class=\"linkAzulEscuro11\">{$item["cliente_id"]}</a>";
          if ($_POST["campos"]["servico"]) {
          	if ($item_atual["cobranca_pai_id"]) {
          		$item["servico"] = $cobrancas->buscaServicos($item_atual["cobranca_pai_id"], $item_atual["cliente_id"]);
          	} else {
          		$item["servico"] = $cobrancas->buscaServicos($item_atual["cobranca_id"], $item_atual["cliente_id"]);
          	}
          }
          if ($_POST["campos"]["cliente_telefone"]) $item["cliente_telefone"] = $cobrancas->buscaClienteTelefones($item_atual["cliente_id"]);
          if ($_POST["campos"]["cliente_email"]) $item["cliente_email"] = $cobrancas->buscaClienteEmails($item_atual["cliente_id"]);
          if ($_POST["campos"]["valor_bonificacao"]) $item["valor_bonificacao"] = $this->formataDinheiro($item_atual["valor_bonificacao"]);
          if ($_POST["campos"]["valor_original"]) $item["valor_original"] = $this->formataDinheiro($item_atual["valor_original"]);
          if ($_POST["campos"]["valor_original_ultima_via"]) $item["valor_original_ultima_via"] = $this->formataDinheiro($cobrancas->buscaValorOriginalUltimaVia($item_atual["cobranca_id"]));
          if ($_POST["campos"]["valor_acrescimo"]) $item["valor_acrescimo"] = $this->formataDinheiro($item_atual["valor_acrescimo"]);
          if ($_POST["campos"]["valor_decrescimo"]) $item["valor_decrescimo"] = $this->formataDinheiro($item_atual["valor_decrescimo"]);
          if ($_POST["campos"]["valor_total"]) $item["valor_total"] = $this->formataDinheiro($item_atual["valor_total"]);
          if ($_POST["campos"]["cliente_condominio"]) $item["cliente_condominio"] = $cobrancas->buscaCondominioNome($item_atual["condominio_id"]);
          if ($_POST["campos"]["cliente_endereco"]) $item["cliente_endereco"] = $cobrancas->formataEndereco($cobrancas->buscaEnderecoCliente($item_atual["cliente_id"]));
          if ($_POST["campos"]["cliente_endereco_cobranca"]) $item["cliente_endereco_cobranca"] = $cobrancas->formataEndereco($cobrancas->buscaEnderecoCobrancaCliente($item_atual["cliente_id"]));
          if ($_POST["campos"]["cliente_endereco_resumido"]) $item["cliente_endereco_resumido"] = $cobrancas->formataEnderecoResumido($cobrancas->buscaEnderecoCliente($item_atual["cliente_id"]));
          if ($_POST["campos"]["cliente_macs"]) $item["cliente_macs"] = $cobrancas->buscaMACs($item_atual["cliente_id"]);
          if ($_POST["campos"]["comentario"]) $item["comentario"] = preg_replace("/[\r\n\t ]+/"," ",$item_atual["comentario"]);
          if ($_POST["campos"]["competencia"]) $item["competencia"] = $cobrancas->buscaCobrancaCompetencia($item_atual["id"]);
          if ($_POST["campos"]["nota_fiscal_valor"]) $item["nota_fiscal_valor"] = $this->formataDinheiro($item_atual["nota_fiscal_valor"]);
          $itens[] = $item;
        }
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens, 0, false, null, null, array("right"));
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }
  
  function buscaNota($cobrancaId) {
      
      $item = array();
      $query = $this->bancodedados->query("SELECT *, DATE_FORMAT(dataEmissao,'%d/%m/%Y') AS emissao FROM tab_notas WHERE coa_id = '{$cobrancaId}' LIMIT 1");
      while (($dadosItem = $this->bancodedados->resultadoArray($query)) != false) {
          $item[] = $dadosItem;
      }
      
      return $item[0];
  }

  ###################
  ### Condominios ###
  ###################

  function condominios() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $condominios = controle::instanciaObjeto("relatorios.condominios", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Condomínios', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioCondominios(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $condominios->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $condominios), $this->conteudo);
    $periodos = $condominios->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos), $this->conteudo);
    $intervalos = $condominios->buscaIntervalos();
    $this->conteudo = str_replace('[intervalos]', $interface->geraIntervalosTemplate($intervalos), $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $condominios->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(),$this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=condominios', $this->conteudo);
    if ($_POST) {
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      if ($_POST["tecnologia"]) {
        $_POST["tecnologia"] = explode(",", $_POST["tecnologia"]);
        $_POST["tecnologia"] = $_POST["tecnologia"][0].($_POST["tecnologia"][1] ? " AND tab_condominios.coq_id = {$_POST["tecnologia"][1]}" : "");
      }
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $condominios->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos, $intervalos), $_POST);
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($_POST["campos"]);
        $vencimento = $condominios->buscaUltimoVencimento();
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item["contador"] = intval($item["contador"] + 1);
          for ($i = 0; $i < sizeof($campos_chaves); $i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/", " ", $item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["estado"] || $_POST["campos"]["cidade"] || $_POST["campos"]["bairro"] || $_POST["campos"]["logradouro"] || $_POST["campos"]["numero"] || $_POST["campos"]["cep"]) $endereco = $condominios->buscaEndereco($item_atual["id"]);
          if ($_POST["campos"]["estado"]) $item["estado"] = $endereco["estado"];
          if ($_POST["campos"]["cidade"]) $item["cidade"] = $endereco["cidade"];
          if ($_POST["campos"]["bairro"]) $item["bairro"] = $endereco["bairro"];
          if ($_POST["campos"]["logradouro"]) $item["logradouro"] = $endereco["logradouro"];
          if ($_POST["campos"]["numero"]) $item["numero"] = $endereco["numero"];
          if ($_POST["campos"]["cep"]) $item["cep"] = $endereco["cep"];
          if ($_POST["campos"]["contato"]) $item["contato"] = $condominios->geraDadosListagem($condominios->buscaCondominioContatos($item_atual["id"]));
          if ($_POST["campos"]["qtde_aptos"]) $item["qtde_aptos"] = $condominios->buscaQtdeAptos($item_atual["id"]);
          if ($_POST["campos"]["qtde_blocos"]) $item["qtde_blocos"] = $condominios->buscaQtdeBlocos($item_atual["id"]);
          if ($_POST["campos"]["origens"]) $item["origens"] = $condominios->buscaOrigens($item_atual["id"]);
          if ($_POST["campos"]["qtde_ativos"]) $item["qtde_ativos"] = $condominios->buscaQtdeAtivos($item_atual["id"]);
          if ($_POST["campos"]["velocidade_vendida"]) $item["velocidade_vendida"] = $condominios->buscaVelocidadeVendida($item_atual["id"], true);
          if ($_POST["campos"]["velocidade_vendida_bits"]) $item["velocidade_vendida_bits"] = $condominios->buscaVelocidadeVendida($item_atual["id"]);
          if ($_POST["campos"]["qtde_ativos_periodo_principal"]) $item["qtde_ativos_periodo_principal"] = $condominios->buscaQtdeAtivosNoPeriodo($item_atual["id"], $_POST["cli_data_ativacao_inicio"], $_POST["cli_data_ativacao_termino"], "principal");
          if ($_POST["campos"]["qtde_ativos_periodo_aditivo"]) $item["qtde_ativos_periodo_aditivo"] = $condominios->buscaQtdeAtivosNoPeriodo($item_atual["id"], $_POST["cli_data_ativacao_inicio"], $_POST["cli_data_ativacao_termino"], "aditivo");
          if ($_POST["campos"]["qtde_prospects"]) $item["qtde_prospects"] = $condominios->buscaQtdeProspects($item_atual["id"]);
          if ($_POST["campos"]["qtde_cancelados"]) $item["qtde_cancelados"] = $condominios->buscaQtdeCancelados($item_atual["id"]);
          if ($_POST["campos"]["qtde_cancelados_periodo_principal"]) $item["qtde_cancelados_periodo_principal"] = $condominios->buscaQtdeCanceladosNoPeriodo($item_atual["id"], $_POST["cli_data_cancelamento_inicio"], $_POST["cli_data_cancelamento_termino"], "principal");
          if ($_POST["campos"]["qtde_cancelados_periodo_aditivo"]) $item["qtde_cancelados_periodo_aditivo"] = $condominios->buscaQtdeCanceladosNoPeriodo($item_atual["id"], $_POST["cli_data_cancelamento_inicio"], $_POST["cli_data_cancelamento_termino"], "aditivo");
          if ($_POST["campos"]["qtde_pedidos_de_cancelamento"]) $item["qtde_pedidos_de_cancelamento"] = $condominios->buscaQtdePedidosCancelamento($item_atual["id"]);
          if ($_POST["campos"]["qtde_pedidos_de_cancelamento"]) $item["qtde_pedidos_de_cancelamento_periodo"] = $condominios->buscaQtdePedidosCancelamentoNoPeriodo($item_atual["id"], $_POST["cli_data_pedido_cancelamento_inicio"], $_POST["cli_data_pedido_cancelamento_termino"]);
          if ($_POST["campos"]["qtde_mailing_disponivel"]) $item["qtde_mailing_disponivel"] = $condominios->buscaQtdeMailingDisponivel($item_atual["id"]);
          if ($_POST["campos"]["qtde_mailing_operados"]) $item["qtde_mailing_operados"] = $condominios->buscaQtdeMailingOperados($item_atual["id"]);
          if ($_POST["campos"]["clientes_com_sistema_ativo"]) $item["clientes_com_sistema_ativo"] = $condominios->buscaClientesComSistemaAtivo($item_atual["id"]);
          if ($_POST["campos"]["mensalidade"]) $item["mensalidade"] = $this->formataDinheiro($condominios->buscaMensalidade($item_atual["id"]));
          if ($_POST["campos"]["desconto"]) $item["desconto"] = $condominios->retornaValorDesconto($condominios->buscaDesconto($item_atual["id"]));
          if ($_POST["campos"]["desconto_indeterminado"]) $item["desconto_indeterminado"] = $condominios->retornaValorDesconto($condominios->buscaDescontoIndeterminado($item_atual["id"]));
          if ($_POST["campos"]["desconto_com_prazo"]) $item["desconto_com_prazo"] = $condominios->retornaValorDesconto($condominios->buscaDescontoComPrazo($item_atual["id"]));
          if ($_POST["campos"]["qtde_chamados_condominio"]) $item["qtde_chamados_condominio"] = $condominios->buscaChamadosCondominio($item_atual["id"], $_POST["chamados_abertura_inicio"], $_POST["chamados_abertura_termino"]);
          if ($_POST["campos"]["qtde_chamados_clientes"]) $item["qtde_chamados_clientes"] = $condominios->buscaChamadosClientes($item_atual["id"], $_POST["chamados_abertura_inicio"], $_POST["chamados_abertura_termino"]);
          if ($_POST["campos"]["velocidade_maxima"]) $item["velocidade_maxima"] = $condominios->formataTamanho($item_atual["velocidade_maxima"]);
          if ($_POST["campos"]["ultima_responsabilidade_inicio"]) $item["ultima_responsabilidade_inicio"] = $condominios->buscaUltimaResponsabilidadeInicio($item_atual["id"]);
          if ($_POST["campos"]["ultima_responsabilidade_termino"]) $item["ultima_responsabilidade_termino"] = $condominios->buscaUltimaResponsabilidadeTermino($item_atual["id"]);
          if ($_POST["campos"]["ultimas_atividades"]) $item["ultimas_atividades"] = $condominios->buscaUltimasAtividades($item_atual["id"]);
          if ($_POST["campos"]["total_ultima_cobranca"]) $item["total_ultima_cobranca"] = $condominios->buscaTotalUltimaCobranca($item_atual["id"], $vencimento);
          if ($_POST["campos"]["ticket_medio"]) $item["ticket_medio"] = $condominios->buscaTicketMedio($item_atual["id"]);
          if ($_POST["campos"]["pop"]) $item["pop"] = $item_atual["pai_id"] ? $condominios->buscaPOP($item_atual["pai_id"]) : "-";
          if ($_POST["campos"]["condominio_id"] && $this->permissoes["condominios"]["ver_dm"]) $item["condominio_id"] = "<a href=\"./?m=condominios&a=ver_dm&i={$item_atual["id"]}\" class=\"linkAzulEscuro11\">{$item_atual["id"]}</a>";
          $itens[] = $item;
        }
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        // Retira as TAGs HTML do campo Contato
        for ($i = 0; $i < sizeof($itens); $i++) {
          $itens[$i]["contato"] = strip_tags($itens[$i]["contato"]);
        }
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', '', $this->conteudo);
    }
  }

  #################
  ### Comissões ###
  #################

  function comissoes() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $comissoes = controle::instanciaObjeto("relatorios.comissoes", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace("[acoes]", 'Comissões', $this->conteudo);
    $this->conteudo = str_replace("[funcoes]", 'validaRelatorioComissoes(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $comissoes->buscaFiltros();
    $this->conteudo = str_replace("[filtros]", $interface->geraFiltrosTemplate($filtros, $comissoes), $this->conteudo);
    $periodos = $comissoes->buscaPeriodos();
    $this->conteudo = str_replace("[periodos]", $interface->geraPeriodosTemplate($periodos), $this->conteudo);
    $this->conteudo = str_replace("[intervalos]", "", $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $comissoes->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    // Script Agrupamento
    $this->conteudo .= "<script type=\"text/javascript\">if (document.getElementById('campos[agrupamento_tipo]').checked) mudaAgrupamento(document.getElementById('campos[agrupamento_tipo]'))</script>";
    $this->conteudo .= "<script type=\"text/javascript\">if (document.getElementById('campos[agrupamento_servico_plano]').checked) mudaAgrupamento(document.getElementById('campos[agrupamento_servico_plano]'))</script>";
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(),$this->conteudo);
    $this->conteudo = str_replace("[action]", "?m=relatorios&a=comissoes", $this->conteudo);
    if ($_POST) {
      if ($_POST["contrato_status"] == "-1") $_POST["contrato_status"] = "3 OR tab_contratos.crs_id IN (4, 19, 20, 21)";
      if ($_POST["contrato_status"] == "-2") $_POST["contrato_status"] = "1 OR tab_contratos.crs_id IN (2, 11, 12, 13, 14, 15, 16, 17, 18)";
      if ($_POST["contrato_status"] == "-3") $_POST["contrato_status"] = "1 OR tab_contratos.crs_id NOT IN (3, 4, 19, 20, 21)";
      $_POST["comissionado"] = ($this->permissoes["relatorios"]["comissoes_plus"]) ? $_POST["comissionado"] : $_SESSION["usuario"];
      if ($_POST["campos"]["agrupamento_tipo"]) {
        $_POST["campos"]["comissionado"] = "on";
        $_POST["campos"]["comissao_tipo"] = "on";
        $_POST["campos"]["comissao_valor"] = "on";
        $_POST["campos"]["pontuacao"] = "on";
        unset($_POST["campos"]["agrupamento_tipo"]);
        $agrupamento = "comissao_tipo";
      }
      if ($_POST["campos"]["agrupamento_servico_plano"]) {
        $_POST["campos"]["comissionado"] = "on";
        $_POST["campos"]["comissao_valor"] = "on";
        $_POST["campos"]["servico_plano"] = "on";
        unset($_POST["campos"]["agrupamento_servico_plano"]);
        $agrupamento = "servico_plano";
      }
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $comissoes->buscaConsulta($agrupamento), funcoes::mescla_arrays($filtros, $periodos), $_POST);
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($_POST["campos"]);
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item["venda_id"] = $item_atual["venda_id"];
          $item["contador"] = intval($item["contador"]+1);
          for ($i = 0; $i < sizeof($campos_chaves); $i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/", " ", $item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["cliente_id"] && $this->permissoes["clientes"]["ver_df"]) $item["cliente_id"] = "<a href=\"./?m=clientes&a=ver_df&i={$item["cliente_id"]}\" class=\"linkAzulEscuro11\">{$item["cliente_id"]}</a>";
      		if ($_POST["campos"]["valor_venda"]) $item["valor_venda"] = $comissoes->buscaValorVenda($item_atual["assinatura_id"], "mensalidade", true);
      		if ($_POST["campos"]["valor_mensalidade"]) $item["valor_mensalidade"] = $comissoes->buscaValorVenda($item_atual["assinatura_id"], "mensalidade");
      		if ($_POST["campos"]["valor_instalacao"]) $item["valor_instalacao"] = $comissoes->buscaValorVenda($item_atual["assinatura_id"], "instalacao");
      		if ($_POST["campos"]["valor_mensalidade_final"]) $item["valor_mensalidade_final"] = $comissoes->buscaValorVenda($item_atual["assinatura_id"], "mensalidade", false, false, true);
      		if ($_POST["campos"]["valor_instalacao_final"]) $item["valor_instalacao_final"] = $comissoes->buscaValorVenda($item_atual["assinatura_id"], "instalacao", false, false, true);
      		//if ($_POST["campos"]["primeiro_pagamento"]) $item["primeiro_pagamento"] = $comissoes->buscaPrimeiroPagamento($item_atual["assinatura_id"], $item_atual["contrato_id"]);
          if ($_POST["campos"]["vendedor"]) $item["vendedor"] = $comissoes->buscaUsuarioNome($item_atual["vendedor"]);
          if ($_POST["campos"]["comissao_valor"]) $item["comissao_valor"] = $this->formataDinheiro($item_atual["comissao_valor"]);
          if ($_POST["campos"]["servico_plano"]) $item["servico_plano"] = $comissoes->buscaServicoPlano($item_atual["servico_plano"]);
          if ($_POST["campos"]["contrato_status"]) $item["contrato_status"] = $comissoes->buscaContratoStatusNome($item_atual["contrato_status"]);
          $itens[] = $item;
        }
      }
      if ($agrupamento) {
        $agrupamento = $comissoes->agrupaItensComissoes($itens, $agrupamento);
        $itens = $agrupamento["itens"];
        $campos = $agrupamento["campos"];
      } else {
        $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]',"",$this->conteudo);
    }
  }

  ####################
  ### Recebimentos ###
  ####################

  function recebimentos() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $recebimentos = controle::instanciaObjeto("relatorios.recebimentos", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Recebimentos', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioRecebimentos(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $recebimentos->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $recebimentos),$this->conteudo);
    $periodos = $recebimentos->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    $this->conteudo = str_replace('[intervalos]', '', $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $recebimentos->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace('[botoes]', $interface->geraBotoes(),$this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=recebimentos', $this->conteudo);
    if ($_POST) {
      // Opções Extras: Implementar nessa área
      $_POST["valor_total"] = "> 0";
      $_POST["status"] = "NOT IN (1, 6, 7, 8, 11)";
      $_POST["campos"]["valor_qtde"] = "on";
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $recebimentos->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos), $_POST);
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item_atual["formato"] = trim(preg_replace("/\\[[^\\]]*\\]/", "", $item_atual["formato"]));
          $item_atual["contador"] = intval($item_atual["contador"], 10) + 1;
          $itens[$item_atual["formato"].$item_atual["vencimento"]][$item_atual["vencimento"]][$item_atual["pagamento"]]["quantidade"] = $item_atual["quantidade"];
          $itens[$item_atual["formato"].$item_atual["vencimento"]][$item_atual["vencimento"]][$item_atual["pagamento"]]["valor"] = $item_atual["valor"];
          $lotes[$item_atual["formato"].$item_atual["vencimento"]] = $item_atual["formato"];
        }
      }
      if ($itens) {
        $agrupamento = $recebimentos->agrupaItensRecebimentos($itens, $_POST, $lotes);
        $itens = $agrupamento["itens"];
        $campos = $agrupamento["campos"];
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }

  ####################
  ### Recuperações ###
  ####################

  function recuperacoes() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $recuperacoes = controle::instanciaObjeto("relatorios.recuperacoes", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Recuperações', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioRecuperacoes(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $recuperacoes->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $recuperacoes),$this->conteudo);
    $periodos = $recuperacoes->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    $this->conteudo = str_replace('[intervalos]', '', $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $recuperacoes->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace('[botoes]', $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=recuperacoes', $this->conteudo);
    if ($_POST) {
      // Opções Extras: Implementar nessa área
      $_POST["responsavel"] = ($this->permissoes["relatorios"]["recuperacoes_plus"]) ? $_POST["responsavel"] : $_SESSION["usuario"];
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $recuperacoes->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos), $_POST);
      //print "<PRE>".$this->consulta; die();
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($_POST["campos"]);
        if ($_POST["campos"]["cliente_status"]) $clientes_status = $recuperacoes->buscaClientesStatus();
        if ($_POST["campos"]["cobranca_status"]) $cobrancas_status = $recuperacoes->buscaCobrancasStatus();
        if ($_POST["campos"]["responsavel"]) $responsaveis = $recuperacoes->buscaResponsaveis();
        $item = array();
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item["contador"]++;
          for ($i = 0; $i < sizeof($campos_chaves); $i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/", " ", $item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["cliente_status"]) $item["cliente_status"] = $clientes_status[$item_atual["cliente_status"]];
          if ($_POST["campos"]["cobranca_status"]) $item["cobranca_status"] = $cobrancas_status[$item_atual["cobranca_status"]];
          if ($_POST["campos"]["responsavel"]) $item["responsavel"] = $responsaveis[$item_atual["responsavel"]];
          if ($item_atual["cobranca_status_id"] == 11) {
          	$cobranca_original = $recuperacoes->buscaCobrancaOriginal($item_atual["cobranca_id"]);
            if ($cobranca_original) {
              if ($_POST["campos"]["valor_original"]) $item["valor_original"] = $this->formataDinheiro($cobranca_original["valor_original"]);
              if ($_POST["campos"]["valor_juro"]) $item["valor_juro"] = $this->formataDinheiro($cobranca_original["valor_juro"]);
              if ($_POST["campos"]["valor_desconto"]) $item["valor_desconto"] = $this->formataDinheiro($cobranca_original["valor_desconto"]);
              if ($_POST["campos"]["valor_acrescimo"]) $item["valor_acrescimo"] = $this->formataDinheiro($cobranca_original["valor_acrescimo"]);
              if ($_POST["campos"]["valor_decrescimo"]) $item["valor_decrescimo"] = $this->formataDinheiro($cobranca_original["valor_decrescimo"]);
              if ($_POST["campos"]["valor_total"]) $item["valor_total"] = $this->formataDinheiro($cobranca_original["valor_total"]);
              $itens[] = $item;
            } else {
              $item["contador"]--;
            }
          } else {
            if ($_POST["campos"]["valor_original"]) $item["valor_original"] = $this->formataDinheiro($item_atual["valor_original"]);
            if ($_POST["campos"]["valor_juro"]) $item["valor_juro"] = $this->formataDinheiro($item_atual["valor_juro"]);
            if ($_POST["campos"]["valor_desconto"]) $item["valor_desconto"] = $this->formataDinheiro($item_atual["valor_desconto"]);
            if ($_POST["campos"]["valor_acrescimo"]) $item["valor_acrescimo"] = $this->formataDinheiro($item_atual["valor_acrescimo"]);
            if ($_POST["campos"]["valor_decrescimo"]) $item["valor_decrescimo"] = $this->formataDinheiro($item_atual["valor_decrescimo"]);
            if ($_POST["campos"]["valor_total"]) $item["valor_total"] = $this->formataDinheiro($item_atual["valor_total"]);
            $itens[] = $item;
          }
        }
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }

  ###################
  ### Faturamento ###
  ###################

  function faturamento() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $faturamento = controle::instanciaObjeto("relatorios.faturamento", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Faturamento', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioFaturamento(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $periodos = $faturamento->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    // Oculta Itens Não Usados
    $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    $this->conteudo = str_replace('[periodo]', '', $this->conteudo);
    $this->conteudo = str_replace('[intervalos]', '', $this->conteudo);
    $this->conteudo = str_replace('[filtros]', '', $this->conteudo);
    // Botões
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace('[botoes]', $interface->geraBotoes(),$this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=faturamento', $this->conteudo);
    // Vencimentos Mínimo/Máximo
    $this->conteudo .= "\n\n\n<script type=\"text/javascript\">\n";
    $this->conteudo .= "  var vencimento_minimo = '01/10/2005';\n";
    $this->conteudo .= "  var vencimento_maximo = '".$faturamento->buscaUltimoVencimentoLote()."';\n";
    $this->conteudo .= "</script>\n\n";
    if ($_POST["venc_inicio"] && $_POST["venc_termino"]) {
      $campos = $faturamento->buscaCampos($_POST["venc_inicio"], $_POST["venc_termino"]);
      $itens = $faturamento->buscaItens($_POST["venc_inicio"], $_POST["venc_termino"]);
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto('listagem', $campos, $itens, 0, false, null, null, @explode(",", "left".str_repeat(",right", (count($campos) - 1) * 2)));
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', '', $this->conteudo);
    }
  }

  #########################
  ### Faturamento Geral ###
  #########################

  function faturamento_geral() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $faturamento_geral = controle::instanciaObjeto("relatorios.faturamento_geral", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Faturamento Geral', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioFaturamentoGeral(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $periodos = $faturamento_geral->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    // Oculta Itens Não Usados
    $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    $this->conteudo = str_replace('[periodo]', '', $this->conteudo);
    $this->conteudo = str_replace('[intervalos]', '', $this->conteudo);
    $this->conteudo = str_replace('[filtros]', '', $this->conteudo);
    // Botões
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace('[botoes]', $interface->geraBotoes(),$this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=faturamento_geral', $this->conteudo);
    if ($_POST["vencimento"]) {
      $campos = $faturamento_geral->buscaCampos($_POST["vencimento"]);
      $itens = $faturamento_geral->buscaItens($_POST["vencimento"]);
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto('listagem', $campos, $itens, 0, false, null, null, @explode(",", "left".str_repeat(",right", (count($campos) - 1) * 2)));
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', '', $this->conteudo);
    }
  }

  ###########################
  ### Ajustes Programados ###
  ###########################

  function servicos_ajustes() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $servicos_ajustes = controle::instanciaObjeto("relatorios.servicos_ajustes", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Ajustes Programados', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioServicosAjustes(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $servicos_ajustes->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $servicos_ajustes),$this->conteudo);
    $periodos = $servicos_ajustes->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    $intervalos = $servicos_ajustes->buscaIntervalos();
    $this->conteudo = str_replace('[intervalos]', $interface->geraIntervalosTemplate($intervalos), $this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $servicos_ajustes->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(),$this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(),$this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=servicos_ajustes', $this->conteudo);
    if ($_POST) {
      $campos = $interface->geraCamposTitulos($_POST["campos"], $this->campos);
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $servicos_ajustes->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos, $intervalos), $_POST);
      $query = $this->bancodedados->query($this->consulta);
      if ($this->bancodedados->linhas($query)) {
        $campos_chaves = array_keys($_POST["campos"]);
        while (($item_atual = $this->bancodedados->resultadoArray($query)) != false) {
          $item["contador"] = intval($item["contador"] + 1);
          for ($i = 0; $i < sizeof($campos_chaves); $i++) {
            $item[$campos_chaves[$i]] = trim(preg_replace("/[\r\n ;]+/", " ", $item_atual[$campos_chaves[$i]]));
          }
          if ($_POST["campos"]["cliente_macs"]) $item["cliente_macs"] = $servicos_ajustes->buscaMACs($item_atual["cliente_id"]);
          if ($_POST["campos"]["cliente_id"] && $this->permissoes["clientes"]["ver_dc"]) $item["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dc&i={$item["cliente_id"]}\" class=\"linkAzulEscuro11\">{$item["cliente_id"]}</a>";
          if ($_POST["campos"]["cliente_velocidades"]) $item["cliente_velocidades"] = $servicos_ajustes->buscaPontos($item_atual["id"]);
          $itens[] = $item;
        }
      }
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        // Retira as TAGs HTML do campo Contato
        for ($i = 0; $i < sizeof($itens); $i++) {
          $itens[$i]["contato"] = strip_tags($itens[$i]["contato"]);
        }
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }

  #############################
  ### Cobranças (Detalhado) ###
  #############################

  function cobrancas_detalhado() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $cobrancas = controle::instanciaObjeto("relatorios.cobrancas_detalhado", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Cobranças (Detalhado)', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioCobrancas(this)', $this->conteudo);
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($cobrancas->buscaFiltros(), $cobrancas), $this->conteudo);
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($cobrancas->buscaPeriodos()), $this->conteudo);
    $this->conteudo = str_replace('[intervalos]', '', $this->conteudo);
    $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(), $this->conteudo);
    $this->conteudo = str_replace('[botoes]', $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=cobrancas_detalhado', $this->conteudo);
    if ($_POST) {
      $itens_geral = $cobrancas->buscaCobrancasDetalhado();
      if ($_POST["tipo_saida"] == "tela") {
        if ($itens_geral) {
          $listagem = controle::instanciaObjeto("listagem", $itens_geral["campos"], $itens_geral["itens"], 0, $itens_geral["total"], $itens_geral["larguras"], null, $itens_geral["alinhamentos"]);
          $listagem_conteudo[] = $listagem->conteudo;
          $this->conteudo = str_replace('[resultado]', implode("\n", $listagem_conteudo), $this->conteudo);
        } else {
          $this->conteudo = str_replace('[resultado]', 'Nenhum registro encontrado', $this->conteudo);
        }
      } else {
        if ($itens_geral) {
          header("Content-type: application/csv;");
          header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
          // Retira as TAGs HTML do campo Contato
          print $this->geraArquivoCSV($itens_geral["campos"], $itens_geral["itens"], true)."\n\n";
          exit();
        }
      }
    } else {
      $this->conteudo = str_replace('[resultado]', '', $this->conteudo);
    }
  }

  ##############################
  ### Faturamento (Agrupado) ###
  ##############################
  
  function faturamento_agrupado() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $faturamento_agrupado = controle::instanciaObjeto("relatorios.faturamento_agrupado", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Faturamento (Agrupado)', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', 'validaRelatorioFaturamentoGeral(this)', $this->conteudo);
    // Insere Filtros no Formulário
    $periodos = $faturamento_agrupado->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos), $this->conteudo);
    // Oculta Itens Não Usados
    $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    $this->conteudo = str_replace('[periodo]', '', $this->conteudo);
    $this->conteudo = str_replace('[intervalos]', '', $this->conteudo);
    $this->conteudo = str_replace('[filtros]', '', $this->conteudo);
    // Botões
    $this->conteudo = str_replace('[periodo]', $this->buscaRelatorioPeriodo(), $this->conteudo);
    $this->conteudo = str_replace('[botoes]', $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace('[action]', '?m=relatorios&a=faturamento_agrupado', $this->conteudo);
    if ($_POST["vencimento"]) {
      $campos = $faturamento_agrupado->buscaCampos($_POST["vencimento"]);
      $itens = $faturamento_agrupado->buscaItens($_POST["vencimento"]);
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto('listagem', $campos, $itens, 0, array("faturado_valor" => "formataDinheiro", "recebido_valor" => "formataDinheiro"), null, null, array("left", "right", "right", "right", "right"));
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', '', $this->conteudo);
    }
  }

  ############################
  ### Cobranças (Agrupado) ###
  ############################

  function cobrancas_agrupado() {
    $interface = controle::instanciaObjeto("relatorios.interface", $this->bancodedados);
    $cobrancas = controle::instanciaObjeto("relatorios.cobrancas_agrupado", $this->bancodedados, $this->permissoes);
    $this->conteudo .= $this->carregaArquivo("relatorios.estrutura.inc.php");
    $this->conteudo = str_replace('[acoes]', 'Cobranças (Agrupado)', $this->conteudo);
    $this->conteudo = str_replace('[funcoes]', '', $this->conteudo);
    // Insere Filtros no Formulário
    $filtros = $cobrancas->buscaFiltros();
    $this->conteudo = str_replace('[filtros]', $interface->geraFiltrosTemplate($filtros, $cobrancas),$this->conteudo);
    $periodos = $cobrancas->buscaPeriodos();
    $this->conteudo = str_replace('[periodos]', $interface->geraPeriodosTemplate($periodos),$this->conteudo);
    $intervalos = $cobrancas->buscaIntervalos();
    $this->conteudo = str_replace('[intervalos]', $interface->geraIntervalosTemplate($intervalos),$this->conteudo);
    // Insere Campos de Resultado no Formulário
    $this->campos = $cobrancas->buscaCampos();
    if ($this->campos) {
      $this->conteudo = str_replace('[campos]', $interface->geraCheckBox($this->campos, $_POST["campos"]), $this->conteudo);
      $this->conteudo = str_replace('[exibe_campos]', 'table-row', $this->conteudo);
    } else {
      $this->conteudo = str_replace('[exibe_campos]', 'none', $this->conteudo);
    }
    $this->conteudo = str_replace("[periodo]", $this->buscaRelatorioPeriodo(), $this->conteudo);
    $this->conteudo = str_replace("[botoes]", $interface->geraBotoes(), $this->conteudo);
    $this->conteudo = str_replace("[action]", '?m=relatorios&a=cobrancas_agrupado', $this->conteudo);
    if ($_POST) {
      if ($_POST["formato_tipo"]) $_POST["formato_tipo"] = "tab_cobrancas_formatos.cft_id IN ({$_POST["formato_tipo"]})";
      if ($_POST["tipo"] == 1) $_POST["tipo"] = "tab_cobrancas.coa_pai IS NULL";
      if ($_POST["tipo"] == 2) $_POST["tipo"] = "tab_cobrancas.coa_pai IS NOT NULL";
      if ($_POST["status"] == -1) $_POST["status"] = "tab_cobrancas.cot_id IN (2, 3)";
      if ($_POST["status"] == -2) $_POST["status"] = "tab_cobrancas.cot_id IN (4, 5, 6, 7, 9)";
      if ($_POST["status"] == -3) $_POST["status"] = "tab_cobrancas.cot_id IN (4, 5, 9)";
      if ($_POST["status"] == -4) $_POST["status"] = "tab_cobrancas.cot_id IN (2, 3, 4, 5, 9)";
      if ($_POST["status"] == -5) $_POST["status"] = "tab_cobrancas.cot_id IN (2, 3, 4, 5, 9)";
      if ($_POST["status"] >   0) $_POST["status"] = "tab_cobrancas.cot_id IN ({$_POST["status"]})";
      if ($_POST["status"] ==  1) $_POST["coa_pai"] = "IS NULL";
      if ($_POST["lote"] < 0) {
        if ($_POST["lote"] == -1) $_POST["coa_pai"] = "IS NOT NULL";
        if ($_POST["lote"] == -2) $_POST["col_id"] = "tab_cobrancas.col_id IS NULL";
        unset($_POST["lote"]);
      } else {
        if ($_POST["lote"]) $_POST["col_id"] = "tab_cobrancas.col_id = {$_POST["lote"]}";
      }
      // Constrói e Executa Consulta
      $this->consulta = $interface->constroiConsulta($this->campos, $_POST["campos"], $cobrancas->buscaConsulta(), funcoes::mescla_arrays($filtros, $periodos, $intervalos), $_POST);
      $itens = $cobrancas->buscaItens($this->consulta);
      $campos = $cobrancas->buscaCamposListagem();
      if ($_POST["tipo_saida"] == "tela") {
        $listagem = controle::instanciaObjeto('listagem', $campos, $itens, 0, array("faturado_valor" => "formataDinheiro", "recebido_valor" => "formataDinheiro"), array(0, 150), null, array("left", "right"));
        $this->conteudo = str_replace('[resultado]', $listagem->conteudo, $this->conteudo);
      } else {
        header("Content-type: application/csv;");
        header("Content-Disposition: attachment; filename={$_GET["a"]}.csv;");
        print $this->geraArquivoCSV($campos, $itens, false, $_POST["tipo_saida"]);
        exit();
      }
    } else {
      $this->conteudo = str_replace('[resultado]', "", $this->conteudo);
    }
  }

}

?>
