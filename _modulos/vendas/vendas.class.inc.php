<?

require_once("_global/modulo.class.inc.php");

class vendas extends modulo {

	function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
		if ($executar) {
    $this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
	   $this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
  }

  ########################
  ### Busca Dados Item ###
  ########################

  function buscaDadosItem($venda_id) {
    $query = "
      SELECT
        tab_vendas.ven_id AS id,
        tab_clientes.cli_nome AS cliente_nome,
        IF(tab_condominios.con_nome IS NULL, 'Sem Condomínio', CONCAT(tab_condominios.con_nome, ', ', SUBSTRING(tab_clientes.cli_complemento, 2), IF(tab_condominios_blocos.cob_nome <> 'Único', CONCAT(' / ', tab_condominios_blocos.cob_nome), ''))) AS endereco,
        DATE_FORMAT(tab_contratos.cor_data_geracao, '%d/%m/%Y') AS data_fechamento,
        DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS data_ativacao,
        tab_vendas.ven_pontuacao AS pontuacao,
        tab_assinaturas.ass_mensalidade - IF(COUNT(tab_descontos_concessoes.dec_id) > 0, SUM(IF((tab_descontos_concessoes.dec_validade IS NULL OR tab_descontos_concessoes.dec_validade = 0 OR tab_descontos_concessoes.dec_validade > 2) AND tab_descontos_concessoes.det_id != 4, IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade), 0)), 0) AS mensalidade,
        tab_assinaturas.ass_instalacao - IF(COUNT(tab_descontos_concessoes.dec_id) > 0, SUM(IF(tab_descontos_concessoes.dec_instalacao > 1, tab_descontos_concessoes.dec_instalacao, tab_descontos_concessoes.dec_instalacao * tab_assinaturas.ass_instalacao)), 0) AS instalacao,
        CONCAT(tab_planos_servicos.pls_nome, ' &raquo; ', tab_planos.pla_nome) AS plano,
        tab_contratos.cor_codigo AS contrato,
        tab_usuarios.usu_id AS vendedor_id,
        tab_usuarios.usu_nome AS vendedor_nome
      FROM
        tab_vendas
        INNER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_vendas.usu_id)
        INNER JOIN tab_contratos ON (tab_contratos.cor_id = tab_vendas.cor_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
        LEFT OUTER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
     WHERE
  	    tab_vendas.ven_id = $venda_id".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
  	 GROUP BY
  	    id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["instalacao"] = ($dados["instalacao"] < 0) ? 0 : $dados["instalacao"];
      $this->dados_item = $dados;
      $this->dados_item["comentarios"] = $this->buscaComentarios($this->dados_item["id"]);
    } else {
      unset($this->dados_item);
    }
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaResponsavel($usuario_id) {
    $query = "SELECT usu_nome AS nome FROM tab_usuarios WHERE usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 'nome');
  }

  function buscaResponsaveis($somente_ativos = true, $usuario_atual_id = null) {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
        INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
        INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
        INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
      WHERE
        tab_acoes.aco_nome = 'assumir_responsabilidade'".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")." AND
        tab_modulos.mod_nome = 'clientes_responsabilidades'".($somente_ativos ? " AND
        (tab_usuarios.uss_id = 1".($usuario_atual_id ? " OR tab_usuarios.usu_id = $usuario_atual_id" : "").")" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }

  function buscaComissoes($venda_id) {
    $query = "
      SELECT
        tab_vendas_comissoes.vec_id AS id,
        tab_vendas_comissoes.vec_valor AS valor,
        tab_vendas_comissoes_tipos.vct_nome AS tipo,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_vendas_comissoes
        INNER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes_tipos.vct_id = tab_vendas_comissoes.vct_id)
        INNER JOIN tab_usuarios ON (tab_vendas_comissoes.usu_id = tab_usuarios.usu_id)
      WHERE
        ven_id = $venda_id
      ORDER BY
        vec_valor DESC";
    $query = $this->bancodedados->query($query);
    $total = 0;
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		  $total += $dados["valor"];
      $dados["valor"] = $this->formataDinheiro($dados["valor"], "R$", true);
      if ($this->permissoes["vendas_comissoes"]["ver"]) {
      	$dados["link"] = "javascript:verVendasComissoes('{$dados["id"]}');";
      }
      $itens[] = $dados;
		}
		if ($total) {
			$itens[] = array("nome" => "<b>Total</b>", "valor" => "<b>".$this->formataDinheiro($total, "R$", true)."</b>");
		}
		return $itens;
  }

  function geraListagemComissoesDetalhes($itens) {
    $cabecalho = array("nome" => "Nome", "tipo" => "Tipo", "valor" => "Valor");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  #############
  ### Geral ###
  #############

  function fechar () {
    unset($this->conteudo);
    if ($this->dados_item)
    $this->conteudo .= '<script type="text/javascript">if (window.opener != null && window.opener.document.getElementById("responsabilidades")) window.opener.buscarClienteResponsabilidades(' . $this->dados_item["cliente_id"] . ');</script>';
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  ###################
  ### Comentários ###
  ###################

  function buscaComentarios($venda_id) {
    $query = "
      SELECT
			  IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
        DATE_FORMAT(tab_vendas_comentarios.veo_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_vendas_comentarios.veo_conteudo AS conteudo
      FROM
        tab_vendas_comentarios
        INNER JOIN tab_usuarios ON (tab_vendas_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        ven_id = $venda_id
      ORDER BY
        tab_vendas_comentarios.veo_datahora ASC,
        tab_vendas_comentarios.veo_id ASC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
      $dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
      $dados["detalhamento"] .= "<b>Conteúdo:</b> {$dados["conteudo"]}<br>";
      $comentarios[] = $dados;
    }
    return $comentarios;
  }

  function registraComentario ($id, $conteudo, $responsavel) {
    $query = "INSERT INTO tab_vendas_comentarios (veo_datahora, ven_id, veo_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  function registraComentarioComissoes ($id, $conteudo, $responsavel) {
    $query = "INSERT INTO tab_vendas_comissoes_comentarios (vco_datahora, vec_id, vco_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  function geraComentariosListagem ($comentarios) {
    $cabecalho = array("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("vendas.ver.inc.php");
      $this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[plano]', $this->dados_item["plano"], $this->conteudo);
      $this->conteudo = str_replace('[pontuacao]', $this->dados_item["pontuacao"], $this->conteudo);
      $this->conteudo = str_replace('[contrato]', $this->dados_item["contrato"], $this->conteudo);
      $this->conteudo = str_replace('[data_fechamento]', $this->dados_item["data_fechamento"], $this->conteudo);
      $this->conteudo = str_replace('[data_ativacao]', $this->dados_item["data_ativacao"], $this->conteudo);
      $this->conteudo = str_replace('[mensalidade]', $this->formataDinheiro($this->dados_item["mensalidade"], "R$", true), $this->conteudo);
      $this->conteudo = str_replace('[instalacao]', $this->formataDinheiro($this->dados_item["instalacao"], "R$", true), $this->conteudo);
      $this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]),$this->conteudo);
      $this->conteudo = str_replace('[vendedor]', $this->dados_item["vendedor_nome"], $this->conteudo);
      $this->conteudo = str_replace('[comissoes]', $this->geraListagemComissoesDetalhes($this->buscaComissoes($_GET["i"])), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,$this->dados_item["id"]);
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("vendas.editar.inc.php");
        $this->conteudo = str_replace('[venda_id]', $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace('[cliente]', $this->dados_item["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
        $this->conteudo = str_replace('[plano]', $this->dados_item["plano"], $this->conteudo);
        $this->conteudo = str_replace('[pontuacao]', $this->dados_item["pontuacao"], $this->conteudo);
        $this->conteudo = str_replace('[contrato]', $this->dados_item["contrato"], $this->conteudo);
        $this->conteudo = str_replace('[data_fechamento]', $this->dados_item["data_fechamento"], $this->conteudo);
        $this->conteudo = str_replace('[data_ativacao]', $this->dados_item["data_ativacao"], $this->conteudo);
        $this->conteudo = str_replace('[mensalidade]', $this->formataDinheiro($this->dados_item["mensalidade"], "R$", true), $this->conteudo);
        $this->conteudo = str_replace('[instalacao]', $this->formataDinheiro($this->dados_item["instalacao"], "R$", true), $this->conteudo);
        $this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]),$this->conteudo);
        $this->conteudo = str_replace('[vendedor]', $this->geraSelect($this->buscaResponsaveis(true, $this->dados_item["vendedor_id"]), $this->dados_item["vendedor_id"], true, "vendedor", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[comissoes]', $this->geraListagemComissoesDetalhes($this->buscaComissoes($_GET["i"])), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        // Trata Dados
        $_POST["pontuacao"] = number_format($_POST["pontuacao"], 1, '.', '');
        if ($this->dados_item["pontuacao"] != $_POST["pontuacao"] || $this->dados_item["vendedor_id"] != $_POST["vendedor"] || trim($_POST["comentario"])) {
          if ($this->dados_item["pontuacao"] != $_POST["pontuacao"]) $comentario .= "<br>Pontuação alterada de <b>{$this->dados_item["pontuacao"]}</b> para <b>{$_POST["pontuacao"]}</b>";
          if ($this->dados_item["vendedor_id"] != $_POST["vendedor"]) $comentario .=  "<br>Vendedor alterado de <b>".$this->buscaResponsavel($this->dados_item["vendedor_id"])."</b> para <b>".$this->buscaResponsavel($_POST["vendedor"])."</b>";
          // Atualiza Venda
          $query = "UPDATE tab_vendas SET ven_pontuacao = {$_POST["pontuacao"]}, usu_id = {$_POST["vendedor"]} WHERE ven_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          // Atualiza Comissoes
          if ($this->dados_item["vendedor_id"] != $_POST["vendedor"]) {
            $query = "UPDATE tab_vendas_comissoes SET usu_id = {$_POST["vendedor"]} WHERE ven_id = {$_GET["i"]} AND vct_id IN (1, 3, 4, 6)";
            $this->bancodedados->query($query);
            // Registra Comentários
            $query = "SELECT vec_id AS id FROM tab_vendas_comissoes WHERE ven_id = {$_GET["i"]} AND vct_id IN (1, 3, 4, 6)";
            $query = $this->bancodedados->query($query);
            $comentario_comissoes = "<br>Vendedor alterado de <b>".$this->buscaResponsavel($this->dados_item["vendedor_id"])."</b> para <b>".$this->buscaResponsavel($_POST["vendedor"])."</b>";
            while (($vendas_comissoes = $this->bancodedados->resultadoArray($query)) != false) {
              $this->registraComentarioComissoes($vendas_comissoes["id"], "<b>Ação:</b> Editado".$comentario_comissoes, $_SESSION["usuario"]);
            }
          }
          // Registra Comentários
          if (!empty($_POST["comentario"])) $comentario = "<br>" . trim($_POST["comentario"]);
          $this->registraComentario($_GET["i"], "<b>Ação:</b> Editado".$comentario, $_SESSION["usuario"]);
          // Registra Atividade
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        }
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=vendas" id="vendas.js"></script>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    $this->conteudo .= $filtro->formulario;
    if ($_GET["f"] || $_GET["p"]) {
      $itens = $this->buscaItens($filtro, $pagina);
      $paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
      $listagem = $this->geraListagem($itens["itens"]);
      $this->conteudo .= '</td></tr></table>';
      $this->conteudo .= $paginacao->conteudo;
      $this->conteudo .= $listagem->conteudo;
      $this->conteudo .= $paginacao->conteudo;
    } else {
      $this->conteudo .= '</td></tr></table>';
    }
  }

  function inicializaFiltro($valores = "") {
    // Responsável
    if (!$this->permissoes["vendas"]["listar_todos"]) $valores["vendedor"] = $_SESSION["usuario"];
    $responsaveis = $this->buscaResponsaveis(false);
    foreach ($responsaveis as $id => $nome) {
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "vendedor", "descricao" => "Vendedor", "valor" => $select_responsaveis, "where" => "(tab_vendas.usu_id = [valor])", "disabled" => ($this->permissoes["vendas"]["listar_todos"])? false : true);
    // Data Fechamento Início
    $itens[] = array ("tipo" => "input", "nome" => "data_fechamento_inicio", "descricao" => "Data Fechamento &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_fechamento >= '".substr($valores["data_fechamento_inicio"], 6, 4)."-".substr($valores["data_fechamento_inicio"], 3, 2)."-".substr($valores["data_fechamento_inicio"], 0, 2)."')");
    // Data Fechamento Término
    $itens[] = array ("tipo" => "input", "nome" => "data_fechamento_termino", "descricao" => "Data Fechamento &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_fechamento <= '".substr($valores["data_fechamento_termino"], 6, 4)."-".substr($valores["data_fechamento_termino"], 3, 2)."-".substr($valores["data_fechamento_termino"], 0, 2)."')");
    // Cliente
    $itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
    // Data Ativação Início
    $itens[] = array ("tipo" => "input", "nome" => "data_ativacao_inicio", "descricao" => "Data Ativação &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_ativacao >= '".substr($valores["data_ativacao_inicio"], 6, 4)."-".substr($valores["data_ativacao_inicio"], 3, 2)."-".substr($valores["data_ativacao_inicio"], 0, 2)."')");
    // Data Ativação Término
    $itens[] = array ("tipo" => "input", "nome" => "data_ativacao_termino", "descricao" => "Data Ativação &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_ativacao <= '".substr($valores["data_ativacao_termino"], 6, 4)."-".substr($valores["data_ativacao_termino"], 3, 2)."-".substr($valores["data_ativacao_termino"], 0, 2)."')");
    // Tipo
    $tipos[] = array ("id" => "1", "nome" => "Venda");
    $tipos[] = array ("id" => "2", "nome" => "Migração");
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $tipos, "where" => "(IF([valor] = 1, tab_assinaturas.ass_migrada IS NULL, tab_assinaturas.ass_migrada IS NOT NULL))");
    // Gera Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
    return $filtro;
  }

  function geraPaginacao($filtro, $total, $pagina) {
    $paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
    return $paginacao;
  }

  function buscaItens($filtro, $pagina) {
    $limit_inicio = ($pagina - 1) * PAG_ITENS;
    $limit_fim = PAG_ITENS;
    $query = "
      SELECT SQL_CALC_FOUND_ROWS
        tab_vendas.ven_id AS id,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente,
        tab_condominios.con_nome AS condominio,
        CONCAT(tab_planos_servicos.pls_nome, '<br>', tab_planos.pla_nome) AS plano,
        DATE_FORMAT(tab_assinaturas.ass_data_fechamento, '%d/%m/%Y') AS data_fechamento,
        DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS data_ativacao,
        SUM(tab_vendas_comissoes.vec_valor) AS total_comissoes,
        tab_assinaturas.ass_id AS assinatura_id,
        tab_assinaturas.ass_migrada AS migrada_id,
        tab_assinaturas.ass_mensalidade AS mensalidade,
        tab_assinaturas.ass_instalacao AS instalacao,
        tab_vendas.ven_pontuacao AS pontuacao,
        tab_usuarios.usu_nome AS vendedor
      FROM
        tab_vendas
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
        INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
        INNER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_vendas.usu_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_vendas_comissoes ON (tab_vendas_comissoes.ven_id = tab_vendas.ven_id)
      WHERE
        $filtro->parametros_sql".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
        id
      ORDER BY
        tab_assinaturas.ass_data_fechamento ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["vendas"]["ver"]) $dados["link"] = "javascript:verVendas('".$dados["id"]."');";
      if ($this->permissoes["clientes"]["ver_dc"]) $dados["cliente"] = "<a href=\"./?m=clientes&a=ver_dm&i={$dados["cliente_id"]}\" class=\"linkAzulEscuro11\">{$dados["cliente"]}</a>";
      if ($dados["condominio"]) {
        $dados["cliente"] = "{$dados["cliente"]}<br>({$dados["condominio"]})";
      }
      $comissoes_detalhes = $this->buscaComissoesDetalhes($dados["id"]);
      $comissoes_detalhes = str_replace(array("\n", "\""), array(" ", '\\\''), $comissoes_detalhes);
      $dados["total_comissoes"] = "<div onmouseover=\"hintShow('$comissoes_detalhes', 350)\" onmouseout=\"hintHide()\">".$this->formataDinheiro($dados["total_comissoes"], "R$", true)."</div>";
      $dados["valor_venda"] = $this->calculaValorVenda($dados["assinatura_id"]);
      if ($dados["migrada_id"] && ($migrada = $this->buscaAssinaturaMigrada($dados["migrada_id"]))) {
      	$valor_venda_migrada = $this->calculaValorVenda($migrada["assinatura_id"]);
      	if ($dados["valor_venda"] > $valor_venda_migrada) {
        	$dados["valor_venda"] -= $valor_venda_migrada;
      	} else {
        	$dados["valor_venda"] = 0;
      	}
        $dados["tipo"] = "Migração";
      } else {
        $dados["tipo"] = "Venda";
      }
      if ($dados["data_fechamento"]) $dados["datas"][] = "F: {$dados["data_fechamento"]}";
      if ($dados["data_ativacao"]) $dados["datas"][] = "A: {$dados["data_ativacao"]}";
      $dados["datas"] = implode("<br>", $dados["datas"]);
      $dados["valor_venda"] = $this->formataDinheiro($dados["valor_venda"], "R$", true);
      $dados["instalacao"] = $dados["instalacao"] - $this->buscaDescontosInstalacao($dados["assinatura_id"]);
      $dados["instalacao"] = $this->formataDinheiro($dados["instalacao"] > 0 ? $dados["instalacao"] : 0, "R$", true);
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function buscaAssinaturaMigrada($assinatura_id) {
    $query = "
      SELECT
        ass_id AS assinatura_id,
        ass_mensalidade AS mensalidade
      FROM
        tab_assinaturas
      WHERE
        ass_id = $assinatura_id";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
    	if ($dados) {
        return $dados;
      }
   }

  function calculaValorVenda($assinatura_id) {
    $query = "
      SELECT
        tab_assinaturas.ass_mensalidade - IFNULL(SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)), 0) AS mensalidade
      FROM
        tab_assinaturas
        LEFT OUTER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
      WHERE
        tab_assinaturas.ass_id = $assinatura_id AND
        tab_descontos_concessoes.dec_mensalidade > 0 AND
        ((tab_descontos_concessoes.dec_data_termino IS NULL) OR (tab_descontos_concessoes.dec_data_termino >= DATE_ADD(tab_assinaturas.ass_data_cancelamento, INTERVAL 6 MONTH)))";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
  }

  function buscaDescontosInstalacao($assinatura_id) {
    $query = "
      SELECT
        SUM(IF(tab_descontos_concessoes.dec_instalacao > 1, tab_descontos_concessoes.dec_instalacao, tab_descontos_concessoes.dec_instalacao * tab_assinaturas.ass_instalacao)) AS instalacao
      FROM
        tab_assinaturas
        INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
      WHERE
        tab_assinaturas.ass_id = $assinatura_id AND
        tab_descontos_concessoes.dec_instalacao > 0";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["instalacao"];
    }
  }

  function buscaComissoesDetalhes($venda_id) {
    $query = "
      SELECT
        tab_vendas_comissoes.vec_id AS id,
        tab_vendas_comissoes.vec_valor AS valor,
        tab_vendas_comissoes_tipos.vct_nome AS tipo,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_vendas_comissoes
        INNER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes_tipos.vct_id = tab_vendas_comissoes.vct_id)
        INNER JOIN tab_usuarios ON (tab_vendas_comissoes.usu_id = tab_usuarios.usu_id)
      WHERE
        ven_id = $venda_id";
    $query = $this->bancodedados->query($query);
	  if ($this->bancodedados->linhas($query)) {
	  	$tabela .= '<table width="100%" cellpadding="1" cellspacing="1" border="0" bgcolor="#FFFFFF">';
	  	$tabela .= '<td bgcolor="#505050" class="txtBranco10" width="150" align="center"><b>Nome</b></td>';
	  	$tabela .= '<td bgcolor="#505050" class="txtBranco10" width="120" align="center"><b>Tipo</b></td>';
	  	$tabela .= '<td bgcolor="#505050" class="txtBranco10" width="80" align="center"><b>Valor</b></td>';
	  	$tabela .= '</tr>';
			while (($item = $this->bancodedados->resultadoArray($query)) != false) {
        $item["valor"] = $this->formataDinheiro($item["valor"], "R$", true);
		  	$tabela .= '<tr>';
		  	$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\">{$item["responsavel"]}</td>";
		  	$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$item["tipo"]}</td>";
		  	$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"right\">{$item["valor"]}</td>";
		  	$tabela .= '</tr>';
			}
	  	$tabela .= '</table>';
	  }
		return $tabela;
  }

  function geraListagem($itens) {
    $cabecalho["contador"] = "#";
    $cabecalho["cliente"] = "Cliente (Condomínio)";
    $cabecalho["plano"] = "Serviço » Plano";
    $cabecalho["datas"] = "Datas";
    $cabecalho["tipo"] = "Tipo";
    $cabecalho["instalacao"] = "Adesão";
    $cabecalho["valor_venda"] = "Mensalidade";
    if ($this->permissoes["vendas_comissoes"]["listar"]) {
      $cabecalho["total_comissoes"] = "Comissão";
    }
    $cabecalho["vendedor"] = "Vendedor";
    if ($_GET["f"]["vendedor"] || !$this->permissoes["vendas"]["listar_todos"]) unset($cabecalho["vendedor"]);
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array("pontuacao" => "", "instalacao" => "formataDinheiro", "valor_venda" => "formataDinheiro", "total_comissoes" => "formataDinheiro"), array("", "", "", "75", "", "", "60", "60", "60"), null, array("right", "left", "center", "center", "center", "right", "right", "right"));
    return $listagem;
  }

}

?>
