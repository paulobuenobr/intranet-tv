<?

require_once("_global/modulo.class.inc.php");

class chamados_categorias extends modulo {

  function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
  		$this->buscaDadosAcao(!$_GET["a"] ? "listar" : $_GET["a"]);
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
      $tamanho_chave = 0;
      reset($array);
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = @implode(",", $chave);
        $tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
        unset($chave);
        next($array);
      }
      reset($array);
      $array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = @implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
        }
        unset($chave);
        next($array);
      }
      ksort($array_ordenacao);
      while (is_int($array_id = current($array_ordenacao))) {
        $array_final[] = $array[$array_id];
        next($array_ordenacao);
      }
      return $array_final;
    }
  }

  function buscaDadosItem($categoria_id) {
    $this->dados_item = $this->buscaDadosCategoria($categoria_id);
  }

  function buscaDadosCategoria($categoria_id) {
    $query = "
      SELECT
        chc_id AS id,
        chc_nome AS nome,
        chc_restrita AS restrita,
        chc_pai AS pai
      FROM
        tab_chamados_categorias
      WHERE
        chc_id = $categoria_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["pai"]) {
        $dados_pai = $this->buscaDadosCategoria($dados["pai"]);
        $dados["nome_completo"] = "{$dados_pai["nome"]} » {$dados["nome"]}";
        $dados["pai_acima"] = $dados["pai"];
        $dados["pai"] = $dados_pai["pai"]	? $dados_pai["pai"] : $dados["pai"];
      } else {
        $dados["nome_completo"] = $dados["nome"];
      }
      return $dados;
    }
  }

  function buscaSetores($setor_id = null) {
    $query = "
      SELECT
        ors_id AS id
      FROM
        tab_organizacao_setores";
    if ($setor_id) {
      $query .= "
      WHERE
        ors_pai = $setor_id";
    }
    $query = $this->bancodedados->query($query);
    while (($setor = $this->bancodedados->resultadoArray($query)) != false) {
      $setores[$setor["id"]] = $this->buscaSetor($setor["id"]);
      $filhos = $this->buscaSetores($setor["id"]);
      if ($filhos) {
        $setores += $filhos;
      }
    }
    if ($setores) {
      asort($setores);
      return $setores;
    }
  }

  function buscaSetor($setor_id) {
    $query = "
      SELECT
        ors_nome AS nome,
        ors_pai AS pai
      FROM
        tab_organizacao_setores
      WHERE
        ors_id = $setor_id";
    $query = $this->bancodedados->query($query);
    $setor = $this->bancodedados->resultadoArray($query);
    if ($setor) {
      if ($setor["pai"]) {
        $setor_pai = $this->buscaSetor($setor["pai"]);
        $setor["nome"] = "{$setor_pai} » {$setor["nome"]}";
      }
      return $setor["nome"];
    }
  }

  function buscaFuncoes() {
    $query = "
      SELECT
        orf_id AS id,
        orf_nome AS nome
      FROM
        tab_organizacao_funcoes
      ORDER BY
        id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $funcoes[$dados["id"]] = $dados["nome"];
    }
    return $funcoes;
  }

  function buscaFuncao($funcao_id) {
    $query = "
      SELECT
        orf_nome AS nome
      FROM
        tab_organizacao_funcoes
      WHERE
        orf_id = $funcao_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

  function buscaListaCategorias($categoria_atual = "0", $gera_listagem = true) {
    $query = "
      SELECT
        chc_id AS id
      FROM
        tab_chamados_categorias
      WHERE
        chc_id <> $categoria_atual";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados_categoria = $this->buscaDadosCategoria($dados["id"]);
      if ($gera_listagem) {
        $dados["nome"] = $dados_categoria["nome"];
        $lista[] = $dados;
      } else {
        $lista[$dados["id"]] = $dados_categoria["nome"];
      }
    }
    @asort($lista);
    return $lista;
  }

  function buscaListaEtapas() {
    $query = "
      SELECT
        cet_id AS id,
        cet_nome AS nome
      FROM
        tab_chamados_etapas_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaObrigatoriaTipos() {
    $lista["0"] = "Não";
    $lista["1"] = "Sim";
    return $lista;
  }

  function buscaMultiplaTipos() {
    $lista["0"] = "Não";
    $lista["1"] = "Sim";
    return $lista;
  }

  function buscaRestricaoTipos() {
    $lista["0"] = "Não";
    $lista["1"] = "Sim";
    return $lista;
  }

  function buscaCategoriasEtapasAdicionar() {
    $adicionar["ordem"] = '<input type="text" name="ordem" id="ordem" style="width:40px" class="inputBrancoAzulEscuro11">';
    $adicionar["etapa_nome"] = $this->geraSelect($this->buscaListaEtapas(), null, true, "etapa", "180px");
    $adicionar["setor_nome"] = $this->geraSelect($this->buscaSetores(), null, true, "setor", "200px");
    $adicionar["funcao_nome"] = $this->geraSelect($this->buscaFuncoes(), null, true, "funcao", "125px");
    $adicionar["obrigatoria_nome"] = $this->geraSelect($this->buscaObrigatoriaTipos(), null, true, "obrigatoria", "65px");
    $adicionar["multipla_nome"] = $this->geraSelect($this->buscaMultiplaTipos(), null, true, "multipla", "45px");
    $adicionar["botoes"] = '<input type="button" name="adicionar_email" id="adicionar_email" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarEtapa()">';
    return $adicionar;
  }

  function buscaCategoriasEtapas($categoria_id, $editavel = false) {
    if ($editavel) {
      $lista[] = $this->buscaCategoriasEtapasAdicionar();
    }
    $query = "
      SELECT
        cce_id AS id,
        ors_id AS setor_id,
        orf_id AS funcao_id,
        tab_chamados_etapas_tipos.cet_id AS etapa_id,
        tab_chamados_etapas_tipos.cet_nome AS etapa_nome,
        cce_obrigatoria AS obrigatoria_id,
        IF(cce_obrigatoria, 'Sim', 'Não') AS obrigatoria_nome,
        cce_multipla AS multipla_id,
        IF(cce_multipla, 'Sim', 'Não') AS multipla_nome,
        cce_ordem AS ordem
      FROM
        tab_chamados_categorias_etapas
        INNER JOIN tab_chamados_etapas_tipos ON (tab_chamados_categorias_etapas.cet_id = tab_chamados_etapas_tipos.cet_id)
      WHERE
        chc_id = $categoria_id
      ORDER BY
        ordem";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      while (($item = $this->bancodedados->resultadoArray($query)) != false) {
        if ($editavel) {
          $item["link"] = "javascript:selecionarEtapa(this, event, {$item["id"]})";
          $item["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerEtapa(\''.$item["id"].'\')">';
        }
        $item["setor_nome"] = $this->buscaSetor($item["setor_id"]);
        $item["funcao_nome"] = $this->buscaFuncao($item["funcao_id"]);
        $lista[] = $item;
      }
    } elseif ($editavel) {
      $lista[] = array("mesclado" => "<center>Nenhum registro disponível</center>");
    }
    return $lista;
  }

  function geraCategoriasEtapasListagem($itens) {
    $cabecalho = array("ordem" => "Ordem", "etapa_nome" => "Etapa", "setor_nome" => "Setor", "funcao_nome" => "Função", "obrigatoria_nome" => "Obrigatória", "multipla_nome" => "Múltipla", "botoes" => "");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  ################
  ### Adcionar ###
  ################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("chamados_categorias.adicionar.inc.php");
      // Action
      $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
      // Conteúdo
      $this->conteudo = str_replace("[pai]", $this->geraSelect($this->buscaListaCategorias(0, false), null, true, "pai", "70%"), $this->conteudo);
      $this->conteudo = str_replace("[restrita]", $this->geraSelect($this->buscaRestricaoTipos(), null, true, "restrita", "20%"), $this->conteudo);
      $this->conteudo = str_replace("[etapas]", $this->geraCategoriasEtapasListagem($this->buscaCategoriasEtapas($_GET["i"], true)), $this->conteudo);
      $this->conteudo = str_replace("[lista_etapas]", @serialize($this->buscaCategoriasEtapas($_GET["i"])), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      // Trata Dados
      $_POST["pai"] = $_POST["pai"] ? $_POST["pai"] : 'NULL';
      // Atualiza cadastro
      $query = "
        INSERT INTO
          tab_chamados_categorias (chc_nome, chc_pai, chc_restrita)
          VALUES ('{$_POST["nome"]}', {$_POST["pai"]}, {$_POST["restrita"]})";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      $lista_etapas = @unserialize(stripslashes($_POST["lista_etapas"]));
      if ($lista_etapas) {
        foreach ($lista_etapas as $etapa) {
          $etapa["funcao_id"] = ($etapa["funcao_id"]) ? $etapa["funcao_id"] : 'NULL';
          $etapa["setor_id"] = ($etapa["setor_id"]) ? $etapa["setor_id"] : 'NULL';
          $query = "
            INSERT INTO
              tab_chamados_categorias_etapas (ors_id, orf_id, cet_id, chc_id, cce_obrigatoria, cce_ordem, cce_multipla)
            VALUES ({$etapa["setor_id"]}, {$etapa["funcao_id"]}, {$etapa["etapa_id"]}, $id, {$etapa["obrigatoria_id"]}, {$etapa["ordem"]}, {$etapa["multipla_id"]})";
          $this->bancodedados->query($query);
        }
      }
      // Registra atividades
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
      // Visualiza listagem
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i=$id");
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("chamados_categorias.editar.inc.php");
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
        // Conteúdo
        $this->conteudo = str_replace("[nome]", $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace("[pai]", $this->geraSelect($this->buscaListaCategorias($_GET["i"], false), $this->dados_item["pai"], true, "pai", "70%"), $this->conteudo);
        $this->conteudo = str_replace("[restrita]", $this->geraSelect($this->buscaRestricaoTipos(), $this->dados_item["restrita"], true, "restrita", "20%"), $this->conteudo);
        $this->conteudo = str_replace("[etapas]", $this->geraCategoriasEtapasListagem($this->buscaCategoriasEtapas($_GET["i"], true)), $this->conteudo);
        $this->conteudo = str_replace("[lista_etapas]", @serialize($this->buscaCategoriasEtapas($_GET["i"])), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        // Trata Dados
        $_POST["pai"] = $_POST["pai"] ? $_POST["pai"] : 'NULL';
        // Atualiza cadastro
        $query = "UPDATE tab_chamados_categorias SET chc_nome = '{$_POST["nome"]}', chc_pai = {$_POST["pai"]}, chc_restrita = {$_POST["restrita"]} WHERE chc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Remove Categorias Etapas Atuais
        $query = "DELETE FROM tab_chamados_categorias_etapas WHERE chc_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $lista_etapas = unserialize(stripslashes($_POST["lista_etapas"]));
        foreach ($lista_etapas as $etapa) {
          $etapa["funcao_id"] = ($etapa["funcao_id"]) ? $etapa["funcao_id"] : 'NULL';
          $etapa["setor_id"] = ($etapa["setor_id"]) ? $etapa["setor_id"] : 'NULL';
          $query = "
            INSERT INTO
              tab_chamados_categorias_etapas (ors_id, orf_id, cet_id, chc_id, cce_obrigatoria, cce_ordem, cce_multipla)
            VALUES ({$etapa["setor_id"]}, {$etapa["funcao_id"]}, {$etapa["etapa_id"]}, {$_GET["i"]}, {$etapa["obrigatoria_id"]}, {$etapa["ordem"]}, {$etapa["multipla_id"]})";
          $this->bancodedados->query($query);
        }
        // Registra atividades
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Visualiza listagem
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->editar();
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar\'">';
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
    // Condominios
    $lista_categorias = $this->buscaListaCategorias();
    $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $lista_categorias, "where" => "(chc_id = [valor] OR chc_pai = [valor])");
    // Inicializa Classe Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
    return $filtro;
  }

  function geraPaginacao($filtro, $total, $pagina) {
    $paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
    return $paginacao;
  }

  function buscaItens($filtro, $pagina) {
    $limit_inicio = ($pagina-1) * PAG_ITENS;
    $limit_fim = PAG_ITENS;
    $query = "
      SELECT SQL_CALC_FOUND_ROWS
        chc_id AS id,
        IF(chc_restrita, 'Sim', 'Não') AS restrita
      FROM
        tab_chamados_categorias
      WHERE
        $filtro->parametros_sql
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($item = $this->bancodedados->resultadoArray($query)) != false) {
      $item["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      if ($this->permissoes["chamados_categorias"]["ver"]) {
        $item["link"] = '?m=chamados_categorias&a=ver&i='.$item["id"];
      }
      $dados_categoria = $this->buscaDadosCategoria($item["id"]);
      $item["nome"] = $dados_categoria["nome_completo"];
      $itens[] = $item;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array("contador" => "#", "nome" => "Nome", "restrita" => "Restrita");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

}

?>
