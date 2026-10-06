<?

require_once("_global/modulo.class.inc.php");

class vendas_comissoes extends modulo {

    function __construct ($bancodedados, $permissoes, $executar = true) {
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

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_vendas_comissoes.vec_id AS id,
        tab_vendas_comissoes.ven_id AS venda_id,
        tab_clientes.cli_nome AS cliente_nome,
        IF(tab_condominios.con_nome IS NULL, 'Sem Condomínio', CONCAT(tab_condominios.con_nome, ', ', SUBSTRING(tab_clientes.cli_complemento, 2), IF(tab_condominios_blocos.cob_nome <> 'Único', CONCAT(' / ', tab_condominios_blocos.cob_nome), ''))) AS endereco,
        tab_vendas_comissoes.vec_valor AS valor,
        tab_vendas_comissoes_tipos.vct_id AS tipo_id,
        tab_vendas_comissoes_tipos.vct_nome AS tipo_nome,
        tab_vendas.ven_pontuacao AS pontuacao,
        DATE_FORMAT(tab_vendas.ven_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_usuarios.usu_id AS comissionado_id,
        tab_usuarios.usu_nome AS comissionado_nome
      FROM
        tab_vendas
        INNER JOIN tab_vendas_comissoes ON (tab_vendas_comissoes.ven_id = tab_vendas.ven_id)
        INNER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes_tipos.vct_id = tab_vendas_comissoes.vct_id)
        INNER JOIN tab_usuarios ON (tab_vendas_comissoes.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
     WHERE
  	    vec_id = $id".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["comentarios"] = $this->buscaComentarios($this->dados_item["id"]);
    }
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaTipos() {
    $query = "
      SELECT
        vct_id AS id,
        vct_nome AS nome
     FROM
  	    tab_vendas_comissoes_tipos
     ORDER BY
  	    vct_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaResponsavel($id) {
    $query = "SELECT usu_nome AS nome FROM tab_usuarios WHERE usu_id = '$id'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query,0,'nome');
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

  #############
  ### Geral ###
  #############

  function fechar() {
    unset($this->conteudo);
    if ($this->dados_item)
    $this->conteudo .= '<script type="text/javascript">if (window.opener != null && window.opener.document.getElementById("responsabilidades")) window.opener.buscarClienteResponsabilidades(' . $this->dados_item["cliente_id"] . ');</script>';
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  ###################
  ### Comentários ###
  ###################

  function buscaComentarios($id) {
    $query = "
      SELECT
			  IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
        DATE_FORMAT(tab_vendas_comissoes_comentarios.vco_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_vendas_comissoes_comentarios.vco_conteudo AS conteudo
      FROM
        tab_vendas_comissoes_comentarios
        INNER JOIN tab_usuarios ON (tab_vendas_comissoes_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        vec_id = $id
      ORDER BY
        tab_vendas_comissoes_comentarios.vco_datahora ASC,
        tab_vendas_comissoes_comentarios.vco_id ASC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
      $dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
      $dados["detalhamento"] .= "<b>Conteúdo:</b> {$dados["conteudo"]}<br>";
      $comentarios[] = $dados;
    }
    return $comentarios;
  }

  function registraComentario($id, $conteudo, $responsavel) {
    $query = "INSERT INTO tab_vendas_comissoes_comentarios (vco_datahora, vec_id, vco_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  function registraComentarioVenda($id, $conteudo, $responsavel) {
    $query = "INSERT INTO tab_vendas_comentarios (veo_datahora, ven_id, veo_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  function geraComentariosListagem($comentarios) {
    $cabecalho = array("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if(!empty($this->dados_item)) {
      $this->conteudo .= $this->carregaArquivo("vendas_comissoes.ver.inc.php");
      $this->conteudo = str_replace('[cliente]',$this->dados_item["cliente_nome"],$this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[data_hora]', $this->dados_item["data_hora"], $this->conteudo);
      $this->conteudo = str_replace('[valor]', $this->formataDinheiro($this->dados_item["valor"], "R$", true), $this->conteudo);
      $this->conteudo = str_replace('[comissionado]', $this->dados_item["comissionado_nome"], $this->conteudo);
      $this->conteudo = str_replace('[comentarios]', $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
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
    if(!empty($this->dados_item)) {
      if (empty($_POST)) {
        $this->conteudo .= $this->carregaArquivo("vendas_comissoes.editar.inc.php");
        $this->conteudo = str_replace("[cliente]", $this->dados_item["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
        $this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
        $this->conteudo = str_replace("[data_hora]", $this->dados_item["data_hora"], $this->conteudo);
        $this->conteudo = str_replace("[valor]", $this->formataDinheiro($this->dados_item["valor"], "R$", true), $this->conteudo);
        $this->conteudo = str_replace("[comissionado]", $this->geraSelect($this->buscaResponsaveis(true, $this->dados_item["comissionado_id"]), $this->dados_item["comissionado_id"], true, "comissionado", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[comentarios]", $this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
        $this->conteudo = str_replace("[venda_id]", $this->dados_item["venda_id"], $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        // Trata Dados
        $_POST["valor"] = preg_replace("/[^0-9]/", "", $_POST["valor"]);
        if ($this->dados_item["valor"] != $_POST["valor"] || $this->dados_item["comissionado_id"] != $_POST["comissionado"] || trim($_POST["comentario"])) {
          if ($this->dados_item["valor"] != $_POST["valor"]) $comentario .= "<br>Comissão alterada de <b>".$this->formataDinheiro($this->dados_item["valor"], "R$", true)."</b> para <b>".$this->formataDinheiro($_POST["valor"], "R$", true)."</b>";
          if ($this->dados_item["comissionado_id"] != $_POST["comissionado"]) $comentario .=  "<br>Comissionado alterado de <b>".$this->buscaResponsavel($this->dados_item["comissionado_id"])."</b> para <b>".$this->buscaResponsavel($_POST["comissionado"])."</b>";
          // Atualiza Valor
          $query = "UPDATE tab_vendas_comissoes SET usu_id = {$_POST["comissionado"]}, vec_valor = {$_POST["valor"]} WHERE vec_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          // Ataliza Usuários
          if ($this->dados_item["comissionado_id"] != $_POST["comissionado"]) {
            $query = "SELECT vec_id AS id FROM tab_vendas_comissoes WHERE ven_id = {$this->dados_item["venda_id"]} AND usu_id = {$this->dados_item["comissionado_id"]} AND vec_id <> {$_GET["i"]}";
            $query = $this->bancodedados->query($query);
            while (($venda_comissao = $this->bancodedados->resultadoArray($query)) != false) {
              $query_update = "UPDATE tab_vendas_comissoes SET usu_id = {$_POST["comissionado"]} WHERE vec_id = {$venda_comissao["id"]}";
              $this->bancodedados->query($query_update);
              // Registra Comentários
              $this->registraComentario($venda_comissao["id"], "<b>Ação:</b> Editado<br>Comissionado alterado de <b>".$this->buscaResponsavel($this->dados_item["comissionado_id"])."</b> para <b>".$this->buscaResponsavel($_POST["comissionado"])."</b>", $_SESSION["usuario"]);
            }
          }
          // Atualiza Responsável Pela Venda
          if (in_array($this->dados_item["tipo_id"], array(1, 3, 4, 6))) {
            $query = "UPDATE tab_vendas SET usu_id = {$_POST["comissionado"]} WHERE ven_id = {$this->dados_item["venda_id"]}";
            $this->bancodedados->query($query);
            // Registra Comentários
            $this->registraComentarioVenda($this->dados_item["venda_id"], "<b>Ação:</b> Editado<br> Vendedor alterado de <b>".$this->buscaResponsavel($this->dados_item["comissionado_id"])."</b> para <b>".$this->buscaResponsavel($_POST["comissionado"])."</b>", $_SESSION["usuario"]);
          }
          // Registra Comentários
          if (!empty($_POST["comentario"])) $comentario .= "<br>".trim($_POST["comentario"]);
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
    $this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=vendas_comissoes" id="vendas_comissoes.js"></script>';
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
    if (!$this->permissoes["vendas_comissoes"]["listar_todos"]) $valores["responsavel"] = $_SESSION["usuario"];
    $responsaveis = $this->buscaResponsaveis(false);
    foreach ($responsaveis as $id => $nome) {
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "responsavel", "descricao" => "Comissionado", "valor" => $select_responsaveis, "where" => "(tab_vendas_comissoes.usu_id = '[valor]')", "disabled" => ($this->permissoes["vendas_comissoes"]["listar_todos"])? false : true);
    // Fechamento -> Início
    $itens[] = array ("tipo" => "input", "nome" => "data_fechamento_inicio", "descricao" => "Fechamento &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_fechamento >= '".substr($valores["data_fechamento_inicio"], 6, 4)."-".substr($valores["data_fechamento_inicio"], 3, 2)."-".substr($valores["data_fechamento_inicio"], 0, 2)."')");
    // Fechamento -> Término
    $itens[] = array ("tipo" => "input", "nome" => "data_fechamento_termino", "descricao" => "Fechamento &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_fechamento <= '".substr($valores["data_fechamento_termino"], 6, 4)."-".substr($valores["data_fechamento_termino"], 3, 2)."-".substr($valores["data_fechamento_termino"], 0, 2)."')");
    // Tipos
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "(tab_vendas_comissoes.vct_id = [valor])");
    // Ativação -> Início
    $itens[] = array ("tipo" => "input", "nome" => "data_ativacao_inicio", "descricao" => "Ativação &raquo; Início", "valor" => "", "where" => "(tab_assinaturas.ass_data_ativacao >= '".substr($valores["data_ativacao_inicio"], 6, 4)."-".substr($valores["data_ativacao_inicio"], 3, 2)."-".substr($valores["data_ativacao_inicio"], 0, 2)."')");
    // Ativação -> Término
    $itens[] = array ("tipo" => "input", "nome" => "data_ativacao_termino", "descricao" => "Ativação &raquo; Término", "valor" => "", "where" => "(tab_assinaturas.ass_data_ativacao <= '".substr($valores["data_ativacao_termino"], 6, 4)."-".substr($valores["data_ativacao_termino"], 3, 2)."-".substr($valores["data_ativacao_termino"], 0, 2)."')");
    // Cliente
    $itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
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
        tab_vendas_comissoes.vec_id AS id,
        DATE_FORMAT(tab_vendas.ven_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_vendas_comissoes.vec_valor AS valor,
        tab_vendas_comissoes_tipos.vct_nome AS tipo,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente,
        tab_usuarios.usu_nome AS comissionado
      FROM
        tab_vendas
        INNER JOIN tab_vendas_comissoes ON (tab_vendas_comissoes.ven_id = tab_vendas.ven_id)
        INNER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes_tipos.vct_id = tab_vendas_comissoes.vct_id)
        INNER JOIN tab_usuarios ON (tab_vendas_comissoes.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_assinaturas ON (tab_vendas.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
      WHERE
        $filtro->parametros_sql".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        tab_vendas_comissoes.vec_id ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($item = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["vendas_comissoes"]["ver"]) {
        $item["link"] = "javascript:verVendasComissoes('".$item["id"]."');";
      }
      $item["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dm&i={$item["cliente_id"]}\" class=\"linkAzulEscuro11\">{$item["cliente_id"]}</a>";
      $item["valor"] = $this->formataDinheiro($item["valor"], "R$", true);
      $item["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $item;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array("contador" => "#", "cliente_id" => "ID", "cliente" => "Cliente", "data_hora" => "Data / Hora", "tipo" => "Tipo", "valor" => "Valor", "comissionado" => "Comissionado");
    if ($_GET["f"]["responsavel"] || !$this->permissoes["vendas_comissoes"]["listar_todos"]) unset($cabecalho["comissionado"]);
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array("valor" => "formataDinheiro"), null, null, array("right", "center", "left", "center", "center", "right"));
    return $listagem;
  }

}

?>
