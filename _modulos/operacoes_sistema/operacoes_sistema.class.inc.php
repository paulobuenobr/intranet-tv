<?

set_time_limit(0);
date_default_timezone_set('America/Sao_paulo');

require_once("_global/modulo.class.inc.php");

class operacoes_sistema extends modulo {

  var $dados_usuario;

  function __construct($bancodedados, $executar = true) {
    $this->bancodedados = $bancodedados;
		$this->buscaDadosModulo(__CLASS__);
    if ($executar) {
      $this->acao = $_GET["a"];
      $this->dados_usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
      if (method_exists($this, $this->acao)) {
        $this->{$this->acao}();
      }
    }
  }

  ########################
  ### Busca Dados Item ###
  ########################

  function buscaDadosUsuario($usuario_id) {
  	$lotes = $this->buscaLotesDisponiveis();
    if ($lotes) {
      $query = "SELECT usu_nome AS nome FROM tab_usuarios WHERE usu_id = $usuario_id";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
      if ($dados) {
        $usuario["id"] = $usuario_id;
        $usuario["nome"] = $dados["nome"];
        $usuario["lotes"] = $lotes;
    		return $usuario;
      }
    }
  }

  function buscaLotesDisponiveis() {
    $query = "
			SELECT
			  opl_id AS id,
			  opl_nome AS nome,
			  opl_regras AS regras
			FROM
			  tab_operacoes_lotes
			WHERE
			  tab_operacoes_lotes.ols_id = 1
			ORDER BY
				opl_recuperacao_vencimento DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["regras"] = unserialize($dados["regras"]);
      if ($this->estaNaValidade($dados["regras"]["validades"])) {
        $lotes[$dados["id"]] = $dados["nome"];
      }
    }
    return $lotes;
  }

  function buscaScriptConteudo($tipo_id) {
    $query = "
			SELECT
			  ops_titulo AS titulo,
			  ops_conteudo AS conteudo,
			  osf_id AS formato
			FROM
			  tab_operacoes_scripts
			WHERE
			  ost_id = $tipo_id AND
			  osa_id = 1
			ORDER BY
			  ops_id DESC";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["formato"] == 1) {
        $dados["conteudo"] = nl2br($dados["conteudo"]);
      }
	    return $dados;
    }
  }

  function buscaSistemaLote($lote_id) {
    $query = "SELECT oli_id AS id	FROM tab_operacoes_lotes WHERE opl_id = $lote_id";
    return $this->bancodedados->resultado($this->bancodedados->query($query), 0, 'id');
  }

  function buscaClientesContatos($cliente_id, $sistema_id) {
    $query = "
			SELECT
			  DATE_FORMAT(tab_operacoes_contatos.opc_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
			  SEC_TO_TIME(UNIX_TIMESTAMP(tab_operacoes_contatos.opc_termino)-UNIX_TIMESTAMP(tab_operacoes_contatos.opc_inicio)) AS duracao,
			  tab_operacoes_contatos.opc_comentario AS comentario,
			  tab_operacoes_contatos_categorias.occ_nome AS categoria,
			  tab_usuarios.usu_nome AS responsavel
			FROM
			  tab_operacoes_contatos
			  INNER JOIN tab_operacoes_contatos_categorias ON (tab_operacoes_contatos.occ_id = tab_operacoes_contatos_categorias.occ_id)
			  INNER JOIN tab_usuarios ON (tab_operacoes_contatos.usu_id = tab_usuarios.usu_id)
			WHERE
			  tab_operacoes_contatos.cli_id = $cliente_id AND
			 	-- tab_operacoes_contatos.opc_termino IS NOT NULL AND
			 	tab_operacoes_contatos_categorias.oli_id = $sistema_id
			ORDER BY
			 	tab_operacoes_contatos.opc_termino DESC
			LIMIT
			  0, 20";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $detalhamento = array();
      $detalhamento[] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data_hora"]}</b><br>";
      $detalhamento[] = "<b>&nbsp;&raquo; Duração:</b> {$dados["duracao"]}<br>";
      $detalhamento[] = "<b>&nbsp;&raquo; Categoria:</b> {$dados["categoria"]}<br>";
      if ($dados["comentario"]) {
      	$detalhamento[] = "<b>&nbsp;&raquo; Conteúdo:</b> {$dados["comentario"]}<br>";
      }
      $dados["detalhamento"] = preg_replace("/[\r\n\t ]+/", " ", implode("\n", $detalhamento));
      $contatos[] = $dados;
    }
    return $contatos;
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaScripts() {
    $query = "
			SELECT DISTINCT
			  tab_operacoes_scripts.ops_id AS id,
			  CONCAT(ost_nome, ' » ', ops_titulo) AS nome
			FROM
			  tab_operacoes_scripts
			  INNER JOIN tab_operacoes_scripts_tipos ON (tab_operacoes_scripts.ost_id = tab_operacoes_scripts_tipos.ost_id)
			WHERE
			  tab_operacoes_scripts.osa_id = 1
			ORDER BY
			  nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $scripts[$dados["id"]] = $dados["nome"];
    }
    return $scripts;
  }

  function buscaContatosCategorias($sistema_id) {
    $query = "
      SELECT
        occ_id AS id,
        occ_nome AS nome
      FROM
        tab_operacoes_contatos_categorias
      WHERE
        oli_id = $sistema_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $contatos_tipos[$dados["id"]] = $dados["nome"];
    }
    return $contatos_tipos;
  }

  #############
  ### Geral ###
  #############

  function formataData($data) {
    if (strlen($data) == 10) {
      return "'".substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2)."'";
    } else { // mm/yyyy
      return "'".substr($data, 3, 4)."-".substr($data, 0, 2)."'";
    }
  }

  function geraListagemHistorico($contatos) {
    $cabecalho = array("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $contatos);
    return $listagem->conteudo;
  }

  function verificaPermissao($usuario, $acao) {
    $query = "
      SELECT
        COUNT(rel_gru_aco_tempermissao.aco_id) AS tem_permissao
      FROM
        tab_usuarios
        INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
        INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
        INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
        INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
      WHERE
        tab_modulos.mod_nome = 'clientes' AND
        tab_acoes.aco_nome = '$acao' AND
        tab_usuarios.usu_id = $usuario";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "tem_permissao");
  }

  ###############
  ### Executa ###
  ###############

  function executa() {
    print trim(preg_replace("/[\r\n\t ]+/"," ",$this->conteudo));
  }

  ####################
  ### Gerar Código ###
  ####################

  function geraCodigoAlterarValor($nome, $propriedade, $conteudo) {
    if ($propriedade) {
      $conteudo = addslashes($conteudo);
      $conteudo = str_replace("<script ", "<scr' + 'ipt ", $conteudo);
      $codigo = "if ($nome) $nome.$propriedade = '$conteudo';";
    } else {
      $codigo = "$nome = $conteudo;";
    }
    return $codigo;
  }

  function geraCodigoExecutarFuncao($nome, $parametros = "") {
    if (is_array($parametros)) $parametros = @implode("', '", $parametros);
    $codigo = "$nome('$parametros');";
    return $codigo;
  }

  function geraCodigoAlertarValor($objeto, $propriedade) {
    $codigo = 'if (' . $objeto . ') alert(' . $objeto . (($propriedade) ? '.' . $propriedade : '') . ');';
    return $codigo;
  }

  ##################
  ### Inicializa ###
  ##################

  function inicializa() {
    if ($this->dados_usuario["lotes"]) {
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao")', 'innerHTML', $this->inicializaAreaOperacao());
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("script")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato")', 'innerHTML', $this->inicializaAreaContato());
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("registro")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("historico")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoExecutarFuncao("inicializaSistemaOperacaoTempos");
    } else {
      $this->conteudo .= $this->geraCodigoExecutarFuncao("alertaOperacaoSistemaOperacaoNenhumLoteDisponivel");
    }
  }

  function inicializaAreaOperacao() {
    $operacao = $this->carregaArquivo("operacoes_sistema.operacao.inc.php");
    $operacao = str_replace("[data_hora]", "", $operacao);
    $operacao = str_replace("[usuario]", $this->dados_usuario["nome"], $operacao);
    $operacao = str_replace("[operacao_lote]", $this->geraSelect($this->dados_usuario["lotes"], null, true, "operacao_lote", "100%", "mudaOperacaoSistemaOperacao(this)"),$operacao);
    $operacao = str_replace("[operacao_duracao]", "00:00:00", $operacao);
    $operacao = str_replace("[operacao_registros]", "0", $operacao);
    $operacao = str_replace("[operacao_estatisticas]", "Selecione um lote para operação", $operacao);
    return $operacao;
  }

  function inicializaAreaScript() {
    $scripts = $this->buscaScripts();
    if ($scripts) {
      $script = $this->carregaArquivo("operacoes_sistema.script.inc.php");
      $script = str_replace("[script_nome]", $this->geraSelect($scripts, null, true, "script_nome", "100%", "mudaScriptConteudo(this.value)", false), $script);
    } else {
      $script = "Nenhum script disponível!";
    }
    return $script;
  }

  function inicializaAreaContato($operacao = false, $sistema = 0) {
    if ($operacao) {
      if ($sistema == 1) {
        $contato = $this->carregaArquivo("operacoes_sistema.contato.prospeccao.inc.php");
      } elseif ($sistema == 2 || $sistema == 3) {
        $contato = $this->carregaArquivo("operacoes_sistema.contato.recuperacao.inc.php");
      }
      $contato = str_replace("[botoes]", ($this->verificaPermissao($_SESSION["usuario"], "editar_ct")) ? '<input type="button" class="buttonAzulEscuroBranco11" id="prev_cliente" name="editar_contato" value="Editar Contatos" onClick="editaClienteContatos();">' : '', $contato);
    } else {
      $contato = "Selecione um lote para operação";
    }
    return $contato;
  }

  function inicializaAreaRegistro ($sistema) {
    $registro = $this->carregaArquivo("operacoes_sistema.registro.inc.php");
    $registro = str_replace("[contato_categoria]", $this->geraSelect($this->buscaContatosCategorias($sistema), null, true, "contato_categoria", "100%"), $registro);
    return $registro;
  }

  #####################
  ### Muda Operação ###
  #####################

  function muda_operacao() {
    if ($_SESSION["operacao_id"]) {
      $this->atualizaOperacao($_SESSION["operacao_id"]);
    }
    if ($_GET["lote"]) {
      $_SESSION["operacao_id"] = $this->inicializaOperacao($_GET["lote"],$_SESSION["usuario"]);
      $dados_operacao = $this->buscaDadosOperacao($_SESSION["operacao_id"]);
      if ($dados_operacao["lote"]) {
        $this->geraOperacaoTelas($_GET["lote"], $dados_operacao);
      } else {
        $this->conteudo .= $this->geraCodigoExecutarFuncao("alertaOperacaoSistemaOperacaoNenhumRegistroDisponivel");
      }
    } else {
      $_SESSION["operacao_id"] = '';
      $this->conteudo .= $this->geraCodigoAlterarValor('operacao_lote', '', "''");
      $this->conteudo .= $this->geraCodigoAlterarValor('operacao_inicio', '', "''");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_duracao")', 'innerHTML', "00:00:00");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_registros")', 'innerHTML', "0");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_estatisticas")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("script")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("registro")', 'innerHTML', "Selecione um lote para operação");
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("historico")', 'innerHTML', "Selecione um lote para operação");
    }
  }

  function inicializaOperacao($lote, $usuario) {
    $query = "DELETE FROM tab_operacoes_contatos WHERE usu_id = '$usuario' AND occ_id IS NULL AND opc_termino IS NULL";
    $query = $this->bancodedados->query($query);
    $query = "INSERT INTO tab_operacoes (ope_inicio, ope_termino, usu_id, opl_id) VALUES (NOW(), NOW(), '$usuario', '$lote')";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
  }

  function atualizaOperacao($operacao_id) {
    $query = "SELECT COUNT(opc_id) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query, 0, "contatos")) {
      $query = "UPDATE tab_operacoes SET ope_termino = NOW() WHERE ope_id = $operacao_id";
      $query = $this->bancodedados->query($query);
    } else {
      $query = "DELETE FROM tab_operacoes WHERE ope_id = $operacao_id";
      $query = $this->bancodedados->query($query);
    }
  }

  function geraOperacaoTelas($lote, $dados_operacao) {
    // Busca qual o sistema desse Lote
    $sistema = $this->buscaSistemaLote($lote);
    // Dados do Div Operação
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("script")', 'innerHTML', $this->inicializaAreaScript());
    $this->conteudo .= $this->geraCodigoAlterarValor('operacao_lote', '', "'$lote'");
    $this->conteudo .= $this->geraCodigoAlterarValor('operacao_inicio', '', 'new Date()');
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_registros")', 'innerHTML', count($dados_operacao["lote"]));
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_estatisticas")', 'innerHTML', "Nenhuma estatística disponível");
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_sistema")', 'value', $sistema);
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_cliente_atual")', 'value', $dados_operacao["lote"][0]);
    $dados_operacao["consulta_sql"] = str_replace("cli_nome <> ''", "tab_clientes.cli_id = [cliente_id]", $dados_operacao["consulta_sql"]);
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("operacao_consulta_sql")', 'value', $dados_operacao["consulta_sql"]);
    unset($dados_operacao["consulta_sql"]);
    $_SESSION["operacoes_sistema"]["clientes"] = $dados_operacao;
    // Dados do Div Contato
    $dados_cliente = $this->buscaDadosCliente($dados_operacao["lote"][0]);
    $this->conteudo .= $this->geraCodigoAlterarValor('contato_inicio', '', 'new Date()');
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato")', 'innerHTML', $this->inicializaAreaContato(true, $sistema));
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_registro")', 'innerHTML', "(1 / ".count($dados_operacao["lote"]).")");
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_inicio")', 'value', date("Y-m-d H:i:s"));
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_nome")', 'innerHTML', $dados_cliente["nome"]);
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_links")', 'innerHTML', $this->geraOperacaoContatoLinks());
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_endereco")', 'innerHTML', $dados_cliente["endereco"]);
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_telefone")', 'innerHTML', $dados_cliente["telefones"]);
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_planos")', 'innerHTML', $dados_cliente["planos"]);
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_status")', 'innerHTML', $dados_cliente["status"]);
    if ($sistema == 1) {
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_responsavel")', 'innerHTML', $dados_cliente["responsavel"]);
    } else {
      $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("contato_cobrancas_em_aberto")', 'innerHTML', $this->buscaClienteCobrancas($dados_operacao["lote"][0]));
    }
    // Dados do Div Registro
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("registro")', 'innerHTML', $this->inicializaAreaRegistro($sistema));
    // Dados do Div Histórico
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("historico")', 'innerHTML', $this->carregaArquivo("operacoes_sistema.historico.inc.php"));
    $this->conteudo .= $this->geraCodigoAlterarValor('document.getElementById("historico_conteudo")', 'innerHTML', $this->geraListagemHistorico($this->buscaClientesContatos($dados_operacao["lote"][0], $sistema)));
  }

  function geraOperacaoContatoLinks() {
    $acoes = array('dc', 'df', 'dm', 'dt', 'da');
    foreach ($acoes as $acao) {
      if ($this->verificaPermissao($_SESSION["usuario"], "ver_".$acao)) {
        $links[] = '<input type="button" class="buttonAzulEscuroBranco11" value="'.strtoupper($acao).'" onclick="abreClienteViaOperacao(\''.$acao.'\')">';
      }
    }
    if ($links) $links = @implode(" ", $links);
    return $links;
  }

  function geraOperacaoEstatisticas($operacao, $sistema) {
    if ($sistema == 1) {
      $estatisticas = $this->buscaQtdeContatosProspeccao($operacao);
    } else {
      $estatisticas = $this->buscaQtdeContatosRecuperacao($operacao);
    }
    return @implode("&nbsp; | &nbsp;", $estatisticas);
  }

  function buscaQtdeContatosProspeccao($operacao_id) {
    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND (occ_id >= 19 AND occ_id <= 21)";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Não Atende', 80)\" onMouseOut=\"hintHide()\" style=\"cursor:pointer\">NA</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND (occ_id >= 22 AND occ_id <= 24)";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Ocupado', 80);\" onMouseOut=\"hintHide();\" style=\"cursor:pointer\">OC</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND (occ_id >= 25 AND occ_id <= 29)";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Sem Interesse', 80);\" onMouseOut=\"hintHide();\" style=\"cursor:pointer\">SI</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 32";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Com Interesse', 80);\" onMouseOut=\"hintHide();\" style=\"cursor:pointer\">CI</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 33";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Já é Cliente e/ou Prospect', 160);\" onMouseOut=\"hintHide();\" style=\"cursor:pointer\">JCP</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id IN (30, 31)";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Erro', 80);\" onMouseOut=\"hintHide();\" style=\"cursor:pointer\">ER</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Total', 80);\" onMouseOut=\"hintHide();\" style=\"cursor:pointer\">TT</span></b> " . $total;
    return $estatisticas;
  }

  function buscaQtdeContatosRecuperacao($operacao_id) {
    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 1";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Cliente Atendeu', 160);\" style=\"cursor:pointer\" onMouseOut=\"hintHide();\">CA</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id IN (2, 34)";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Cliente não Atendeu', 160);\" style=\"cursor:pointer\" onMouseOut=\"hintHide();\">CNA</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 3";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Telefone não Existe', 160);\" style=\"cursor:pointer\" onMouseOut=\"hintHide();\">TNE</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));

    $query = "SELECT COUNT(*) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $operacao_id AND occ_id = 4";
    $query = $this->bancodedados->query($query);
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Outro', 80);\" style=\"cursor:pointer\" onMouseOut=\"hintHide();\">OU</span></b> " . intval($this->bancodedados->resultado($query,0,"contatos"));
    $total += intval($this->bancodedados->resultado($query,0,"contatos"));
    $estatisticas[] = "<b><span onMouseOver=\"hintShow('Total', 80);\" style=\"cursor:pointer\" onMouseOut=\"hintHide();\">TT</span></b> " . $total;
    return $estatisticas;
  }

  function buscaDadosOperacao($id) {
    $query = "SELECT opl_id AS lote FROM tab_operacoes WHERE ope_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados_operacao = $this->gerarOperacao($dados["lote"], $_SESSION["usuario"]);
      $operacao["id"] = $id;
      $operacao["lote"] = $dados_operacao["lote"];
      $operacao["consulta_sql"] = $dados_operacao["consulta_sql"];
	    return $operacao;
    }
  }

  ####################################
  ### Funções dos Dados do Cliente ###
  ####################################

  function buscaDadosCliente($cliente_id) {
    $query = "
      SELECT
        cli_nome AS nome,
        IF(usu_nome IS NOT NULL, usu_nome, 'Sem responsável') AS responsavel,
		    CONCAT(GROUP_CONCAT(DISTINCT tab_planos.pla_nome ORDER BY tab_planos.pls_id SEPARATOR ', ')) AS planos,
        tab_clientes_status.cls_nome AS status
      FROM
        tab_clientes
    		LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
    		LEFT OUTER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id AND tab_assinaturas.ast_id IN (2, 5))
    		LEFT OUTER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
    		INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
      WHERE
      	tab_clientes.cli_id = $cliente_id
      GROUP BY
        tab_clientes.cli_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$dados["telefones"] = $this->buscaClientesTelefones($cliente_id);
      $dados["endereco"] = $this->buscaEnderecoCliente($cliente_id);
      return $dados;
    }
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
    return @implode(" | ", $lista_com_ramal);
  }
  
  function buscaEnderecoCliente($cliente_id) {
    $query = "
			SELECT
			  con_nome AS condominio,
			  con_velocidade_maxima AS velocidade_maxima,
			  cob_nome AS bloco,
			  cli_numero AS numero,
			  cli_complemento AS complemento,
			  log_nome AS logradouro,
			  bai_nome AS bairro,
			  cid_nome AS cidade,
			  est_sigla AS estado
			FROM
			  tab_clientes
			  LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
			  LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
			  LEFT OUTER JOIN tab_ceps ON (tab_clientes.cep_id = tab_ceps.cep_id)
			  LEFT OUTER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
			  LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
			  LEFT OUTER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			  LEFT OUTER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
			  cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $endereco = $this->bancodedados->resultadoArray($query);
    if ($endereco["condominio"]) {
    	$endereco["velocidade_maxima"] = $this->formataVelocidade($endereco["velocidade_maxima"]);
      $endereco_tratado = "{$endereco["condominio"]} ({$endereco["velocidade_maxima"]})";
      if ($endereco["bloco"] != "Único") $endereco_tratado .= ", Bloco " . $endereco["bloco"];
      if ($endereco["complemento"]) $endereco_tratado .= ", " . $this->corrigeEnderecoClienteComplemento($endereco["complemento"]);
    } else {
      $endereco_tratado = $endereco["logradouro"];
      if ($endereco["numero"]) $endereco_tratado .= ", " . $endereco["numero"];
      if ($endereco["complemento"]) $endereco_tratado .= ", " . $this->corrigeEnderecoClienteComplemento($endereco["complemento"]);
      if ($endereco["bairro"]) $endereco_tratado .= ", " . $endereco["bairro"];
      if ($endereco["cidade"]) $endereco_tratado .= ", " . $endereco["cidade"];
      if ($endereco["estado"]) $endereco_tratado .= "/" . $endereco["estado"];
    }
    return $endereco_tratado;
  }

  function corrigeEnderecoClienteComplemento($complemento) {
    $complementos_tipos["a"] = "Apto ";
    $complementos_tipos["l"] = "Loja ";
    $complementos_tipos["s"] = "Sala ";
    $complementos_tipos["c"] = "Casa ";
    $complementos_tipos["o"] = "";
    $complemento = $complementos_tipos[$complemento[0]] . substr($complemento,1,strlen($complemento));
    return $complemento;
  }

  function formataVelocidade($velocidade) {
  	$unidades["1"] = "Kbps";
	  $unidades["2"] = "Mbps";
	  $unidades["3"] = "Gbps";
	  $unidades["4"] = "Tbps";
  	$unidade = floor(log($velocidade, 1024));
   	$unidade = $unidade < 1 ? 1 : $unidade;
   	return round($velocidade / pow(1024, $unidade), 1).$unidades[$unidade];
  }
  
  #################################
  ##### Funções das Cobranças #####
  #################################

  function buscaClienteCobrancas($cliente_id) {
    $query = "
			SELECT
			  coa_id AS id
			FROM
			  tab_cobrancas
			WHERE
			  cli_id = $cliente_id AND
			  coa_pai IS NULL AND
			  cot_id = 1
			ORDER BY
			  coa_data_vencimento";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cobrancas[$dados["id"]] = $this->buscaDadosCobranca($dados["id"]);
    }
    return $this->agrupaCobrancas($cobrancas);
  }

  function buscaDadosCobranca($cobranca_id) {
    $query = "
			SELECT
			  DATE_FORMAT(coa_data_vencimento,'%m/%Y') AS vencimento,
			  coa_valor_original AS valor_original,
				TO_DAYS(NOW()) - TO_DAYS(coa_data_vencimento) AS dias_atraso
			FROM
			  tab_cobrancas
			WHERE
			  coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["valor_correcao"] = $this->calculaValorCorrecao($dados["valor_original"],"0.02","0.01",$dados["dias_atraso"]);
      $cobranca["id"] = $cobranca_id;
      $cobranca["codigo"] = $dados["codigo"];
      $cobranca["vencimento"] = $dados["vencimento"];
      $cobranca["valor_original"] = $dados["valor_original"];
      $cobranca["valor_correcao"] = $dados["valor_correcao"];
      $cobranca["valor_corrigido"] = $dados["valor_original"] + $dados["valor_correcao"];
	    return $cobranca;
    }
  }

  function calculaValorCorrecao($valor_original, $multa, $juros, $dias_atraso) {
    return ($valor_original * $multa) + (($valor_original * $juros / 30) * $dias_atraso);
  }

  function agrupaCobrancas($cobrancas) {
    if ($cobrancas) {
      foreach ($cobrancas as $id => $dados) {
        $total["vencimento"][] = $dados["vencimento"];
        $total["valor_original"] += $dados["valor_original"];
        $total["valor_correcao"] += $dados["valor_correcao"];
        $total["valor_corrigido"] += $dados["valor_corrigido"];
        $detalhamento[] = "{$dados["vencimento"]} (".$this->formataDinheiro($dados["valor_original"])." + ".$this->formataDinheiro($dados["valor_correcao"])." = ".$this->formataDinheiro($dados["valor_corrigido"]).")";
      }
      if (count($cobrancas) > 1) {
        for ($i = 0; $i < count($total["vencimento"]); $i++) {
          $cobrancas_agrupadas[] = "<span onMouseOver=\"hintShow('$detalhamento[$i]');\" onMouseOut=\"hintHide();\">" . $total["vencimento"][$i] . "</span>";
        }
        $cobrancas_agrupadas = @implode(", ", $cobrancas_agrupadas)." (".$this->formataDinheiro($total["valor_original"])." + ".$this->formataDinheiro($total["valor_correcao"])." = ".$this->formataDinheiro($total["valor_corrigido"]).")";
      } else {
        $cobrancas_agrupadas = $detalhamento[0];
      }
    }
    return $cobrancas_agrupadas;
  }

  ######################
  ### Gerar Operação ###
  ######################

  function gerarOperacao($lote_id, $usuario_id = null, $completo = false) {
    // Busca Dados Lote
    $dados_lote = $this->buscaDadosLotes($lote_id);
    // Trata Filtros Lotes
    $filtros_tratados = $this->trataFiltros($dados_lote["regras"]["filtros"]);
    // Gera Cliente Filtros
    foreach ($filtros_tratados as $filtro) {
      $dados_filtro_tipo = $this->buscaDadosFiltroTipo($filtro["campo"]);
      if ($dados_filtro_tipo["parametros"]["juncao"]) {
        foreach ($dados_filtro_tipo["parametros"]["juncao"] as $dados_juncao) {
          $cliente_juncoes[] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_juncao);
        }
      }
      if ($dados_filtro_tipo["parametros"]["filtro"]["where"]) {
        foreach ($dados_filtro_tipo["parametros"]["filtro"]["where"] as $dados_filtro) {
          $cliente_filtros["where"][] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_filtro);
        }
      }
      if ($dados_filtro_tipo["parametros"]["filtro"]["having"]) {
        foreach ($dados_filtro_tipo["parametros"]["filtro"]["having"] as $dados_filtro) {
          $cliente_filtros["having"][] = str_replace(array("[comparacao]", "[valor]"), array($filtro["comparacao"], $filtro["valor"]), $dados_filtro);
        }
      }
    }
    if ($usuario_id && !$this->ehRecuperacaoGerente($usuario_id) && $this->possuiResponsabilidade($lote_id)) {
      $cliente_juncoes[] = "INNER JOIN tab_operacoes_lotes_responsabilidades ON (tab_clientes.cli_id = tab_operacoes_lotes_responsabilidades.cli_id)";
      $cliente_filtros["where"][] = "tab_operacoes_lotes_responsabilidades.usu_id = $usuario_id";
      $cliente_filtros["where"][] = "tab_operacoes_lotes_responsabilidades.opl_id = $lote_id";
    }
    $cliente_campos[] = "tab_clientes.cli_id AS id";
    if ($dados_lote["sistema"] == 2) {
      $cliente_campos[] = "SUM(tab_cobrancas.coa_valor_original) AS valor_original";
      $group_by = "tab_clientes.cli_id";
      //$group_by = "tab_cobrancas.coa_id";
    } else {
      $group_by = "tab_clientes.cli_id";
    }
    if ($dados_lote["regras"]["ultimos_contatos"]) {
      $cliente_filtros["contatos"] = $dados_lote["regras"]["ultimos_contatos"];
    }
    $enderecos = $this->trataEnderecos($dados_lote["regras"]["enderecos"]);
    return $this->buscaClientes(@implode(",\n        ", array_unique($cliente_campos)), @implode("\n        ", array_unique(funcoes::mescla_arrays($cliente_juncoes ? $cliente_juncoes : array(), $enderecos["juncoes"] ? $enderecos["juncoes"] : array()))), @implode(" AND\n        ", array_unique(funcoes::mescla_arrays($cliente_filtros["where"] ? $cliente_filtros["where"] : array(), $enderecos["filtros"] ? $enderecos["filtros"] : array()))), @implode(" AND\n        ", array_unique($cliente_filtros["having"])), $group_by, $cliente_filtros["contatos"], $dados_lote["sistema"], $completo);
  }

  function possuiResponsabilidade($lote_id) {
    $query = "
      SELECT
        COUNT(olr_id) AS responsabilidades
      FROM
        tab_operacoes_lotes_responsabilidades
      WHERE
        opl_id = $lote_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["responsabilidades"];
    }
  }

  function ehRecuperacaoGerente($usuario_id) {
  	if ($usuario_id == 39) {
  		return $usuario_id;
  	}
    $query = "
      SELECT
        usu_id AS id
      FROM
        rel_usu_gru_fazparte
      WHERE
        gru_id = 23 AND
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  function buscaDadosLotes($id) {
    $query = "
      SELECT
        opl_regras AS regras,
        oli_id AS sistema
      FROM
        tab_operacoes_lotes
      WHERE
        opl_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["regras"] = @unserialize($dados["regras"]);
    }
    return $dados;
  }

  function trataFiltros($filtros_lote) {
    foreach ($filtros_lote as $filtro) {
      $filtros_agrupados[$filtro["campo"]][$filtro["comparacao"]][] = $filtro["valor"];
    }
    foreach ($filtros_agrupados as $campo => $filtros) {
      foreach ($filtros as $comparacao => $valores) {
        for ($i = 0; $i < count($valores); $i++) {
          $valores[$i] = (stristr($valores[$i], "/")) ? $this->formataData($valores[$i]) : $valores[$i];
        }
        if ($comparacao == "=" && count($valores) > 1) {
          $filtros_tratados[] = array("campo" => $campo, "comparacao" => "IN", "valor" => "(".implode(", ", $valores).")");
        } else if ($comparacao == "<>" && count($valores) > 1) {
          $filtros_tratados[] = array("campo" => $campo, "comparacao" => "NOT IN", "valor" => "(".implode(", ", $valores).")");
        } else if ($comparacao == ">" && count($valores) > 1) {
          $filtros_tratados[] = array("campo" => $campo, "comparacao" => ">", "valor" => max($valores));
        } else if ($comparacao == "<" && count($valores) > 1) {
          $filtros_tratados[] = array("campo" => $campo, "comparacao" => "<", "valor" => min($valores));
        } else if ($comparacao == ">=" && count($valores) > 1) {
          $filtros_tratados[] = array("campo" => $campo, "comparacao" => ">=", "valor" => max($valores));
        } else if ($comparacao == "<=" && count($valores) > 1) {
          $filtros_tratados[] = array("campo" => $campo, "comparacao" => "<=", "valor" => min($valores));
        } else {
          foreach ($valores as $valor) {
            $filtros_tratados[] = array("campo" => $campo, "comparacao" => $comparacao, "valor" => $valor);
          }
        }
      }
    }
    return $filtros_tratados;
  }

  function trataEnderecos($filtros_enderecos) {
    $juncoes = $this->buscaJuncoesPreDefinidas();
    if ($filtros_enderecos) {
      foreach ($filtros_enderecos as $filtro_endereco) {
        if ($filtro_endereco["condominio"] > 0) {
          // Tal Condomínio
          $enderecos["juncoes"][] = $juncoes["bloco"];
          $enderecos["filtros"][] = "tab_clientes.con_id = {$filtro_endereco["condominio"]}";
        } else if ($filtro_endereco["logradouro"] > 0) {
          // Tal Logradouro
          $enderecos["juncoes"][] = $juncoes["bloco"];
          $enderecos["juncoes"][] = $juncoes["condominio"];
          $enderecos["juncoes"][] = $juncoes["cep"];
          $enderecos["filtros"][] = "tab_ceps.log_id = {$filtro_endereco["logradouro"]}";
        } else if ($filtro_endereco["bairro"] > 0) {
          // Tal Bairro
          $enderecos["juncoes"][] = $juncoes["bloco"];
          $enderecos["juncoes"][] = $juncoes["condominio"];
          $enderecos["juncoes"][] = $juncoes["cep"];
          $enderecos["juncoes"][] = $juncoes["logradouro"];
          $enderecos["filtros"][] = "tab_logradouros.bai_id = {$filtro_endereco["bairro"]}";
        } else if ($filtro_endereco["cidade"] > 0) {
          // Tal Cidade
          $enderecos["juncoes"][] = $juncoes["bloco"];
          $enderecos["juncoes"][] = $juncoes["condominio"];
          $enderecos["juncoes"][] = $juncoes["cep"];
          $enderecos["juncoes"][] = $juncoes["logradouro"];
          $enderecos["juncoes"][] = $juncoes["bairro"];
          $enderecos["filtros"][] = "tab_bairros.cid_id = {$filtro_endereco["cidade"]}";
        }
      }
    }
    if ($enderecos["juncoes"]) {
      $enderecos["juncoes"] = array_unique($enderecos["juncoes"]);
    }
    if ($enderecos["filtros"]) {
      $enderecos["filtros"] = implode(" OR\n          ", array_unique($enderecos["filtros"]));
      $enderecos["filtros"] = "(\n          ".$enderecos["filtros"]."\n        )";
    }
    return $enderecos;
  }

  function buscaJuncoesPreDefinidas() {
    $juncoes["bloco"] = "LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)";
    $juncoes["condominio"] = "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)";
    $juncoes["cep"] = "LEFT OUTER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)";
    $juncoes["logradouro"] = "LEFT OUTER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)";
    $juncoes["bairro"] = "LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)";
    return $juncoes;
  }

  function buscaDadosFiltroTipo($filtro_tipo_id) {
    $query = "
  		SELECT
  		  olf_nome AS nome,
  			olf_parametros AS parametros
  		FROM
  			tab_operacoes_lotes_filtros
  		WHERE
  			olf_id = $filtro_tipo_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      $dados["parametros"]["comparacao"]["IN"] = "IN";
	    return $dados;
    }
  }

  function buscaCamposVariaveis($mensagem) {
    preg_match_all('/\[(.*)\]/U', $mensagem, $campos);
    return array_unique($campos[1]);
  }

  function buscaClientes($campos, $juncoes, $filtro_where, $filtro_having, $group_by, $contatos, $sistema_id, $completo = false) {
    $query = "
      SELECT DISTINCT
        $campos
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_operacoes_contatos ON (tab_clientes.cli_id = tab_operacoes_contatos.cli_id)
        $juncoes";
    if ($filtro_where) {
      $query .= "
      WHERE
        cli_nome <> '' AND
        $filtro_where";
    } else {
      $query .= "
      WHERE
        cli_nome <> ''";
    }
    $query .= "
      GROUP BY
        $group_by";
    if ($filtro_having) {
      $query .= "
      HAVING
        $filtro_having";
    }
//    $query .= "
//      ORDER BY
//        tab_operacoes_contatos.opc_inicio,".($sistema_id == 2 ? "
//        SUM(tab_cobrancas.coa_valor_original) DESC" : "
//        tab_clientes.cli_id");
    $query .= "
      ORDER BY
        tab_clientes.cli_nome";

    $constantes = $this->buscaCamposVariaveis($query);
    if ($constantes) {
      foreach ($constantes as $constante) {
        $query = str_replace("[$constante]", constant($constante), $query);
      }
    }
    $clientes["consulta_sql"] = $query;
    //print $query; die();
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $todos_clientes[] = $dados;
    }
    if ($todos_clientes) {
      foreach ($todos_clientes as $cliente) {
        if ($this->buscaClienteUltimoContato($cliente["id"], $contatos, $sistema_id)) {
          $clientes["lote"][] = ($completo) ? $cliente : $cliente["id"];
        }
      }
    }
    return $clientes;
  }

  function buscaClienteUltimoContato($cliente_id, $contatos, $sistema_id) {
    $contatos = explode(", ", $contatos);
    $query = "
      SELECT
        occ_id AS categoria
      FROM
        tab_clientes
        INNER JOIN tab_operacoes_contatos ON (tab_clientes.cli_id = tab_operacoes_contatos.cli_id)
        INNER JOIN tab_operacoes ON (tab_operacoes_contatos.ope_id = tab_operacoes.ope_id)
        INNER JOIN tab_operacoes_lotes ON (tab_operacoes.opl_id = tab_operacoes_lotes.opl_id)
      WHERE
        tab_clientes.cli_id = $cliente_id AND
        tab_operacoes_lotes.oli_id = $sistema_id
      ORDER BY
        opc_inicio DESC".(in_array("-2", $contatos) ? "
      LIMIT 0, 1" : "");
    $query = $this->bancodedados->query($query);
    if (!$this->bancodedados->linhas($query) && in_array("-1", $contatos)) {
      return true;
    } else {
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        if (in_array($dados["categoria"], $contatos)) {
          return true;
        }
      }
    }
    return false;
  }

  function estaNaValidade($validades) {
    foreach ($validades as $validade) {
      if (in_array($validade["responsavel"], array(-1, $_SESSION["usuario"]))) {
        if ($validade["inicio"]) $validade["inicio"] = str_replace("'", "", $this->formataData($validade["inicio"]));
        if (!$validade["inicio"] || $validade["inicio"] <= date("Ymd")) {
          if ($validade["termino"]) $validade["termino"] = str_replace("'", "", $this->formataData($validade["termino"]));
          if (!$validade["termino"] || $validade["termino"] >= date("Ymd")) {
            // 1: Manhã	=> 00:00 até 13:00
            // 2: Tarde => 13:00 até 18:00
            // 3: Noite => 18:00 até 24:00
            if ($validade["periodo"] == -1 || ($validade["periodo"] == 1 && date("Hi") >= 0 && date("Hi") <= 1300) || ($validade["periodo"] == 2 && date("Hi") >= 1300 && date("Hi") <= 1800) || ($validade["periodo"] == 3 && date("Hi") >= 1800 && date("Hi") <= 2400)) {
              return true;
            }
          }
        }
      }
    }
    return false;
  }

}

?>
