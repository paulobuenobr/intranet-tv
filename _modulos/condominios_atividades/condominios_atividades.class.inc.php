<?

require_once("_global/modulo.class.inc.php");

class condominios_atividades extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
      $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function buscaDadosCondominio($condominio_id) {
    $query = "
      SELECT
        con_id AS id,
        con_nome AS nome,
        usu_id AS responsavel_id
      FROM 
        tab_condominios 
      WHERE 
        con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
  }

  function buscaDadosItem($atividade_id) {
    $query = "
      SELECT
        tab_condominios_atividades.cat_id AS id,
        DATE_FORMAT(tab_condominios_atividades.cat_data_inicio, '%d/%m/%Y') AS inicio,
        DATE_FORMAT(tab_condominios_atividades.cat_data_termino, '%d/%m/%Y') AS termino,
        tab_condominios_atividades.cat_conteudo AS conteudo,
        tab_usuarios.usu_id AS responsavel_id,
        tab_usuarios.usu_nome AS responsavel_nome,
        tab_condominios.con_id AS condominio_id,
        tab_condominios.con_nome AS condominio_nome
      FROM
        tab_condominios_atividades
        INNER JOIN tab_condominios ON (tab_condominios_atividades.con_id = tab_condominios.con_id)
        INNER JOIN tab_usuarios ON (tab_condominios_atividades.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_condominios_atividades.cat_id = $atividade_id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $dados["atividades"] = $this->buscaAtividades($this->dados_item["id"], true);
    }
  }
  
  function buscaAtividadesTipos() {
    $query = "
      SELECT
        cai_id AS id,
        cai_nome AS nome
      FROM 
        tab_condominios_atividades_tipos 
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $atividades_tipos[$dados["id"]] = $dados["nome"];
    }
    return $atividades_tipos;
  }

  function buscaResponsaveis($responsavel_id = null) {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_acoes
        INNER JOIN rel_gru_aco_tempermissao ON (tab_acoes.aco_id = rel_gru_aco_tempermissao.aco_id)
        INNER JOIN rel_usu_gru_fazparte ON (rel_gru_aco_tempermissao.gru_id = rel_usu_gru_fazparte.gru_id)
        INNER JOIN tab_usuarios ON (rel_usu_gru_fazparte.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
      WHERE
        (tab_usuarios.uss_id = 1 AND
         tab_acoes.aco_nome = 'assumir_responsabilidade' AND
         tab_modulos.mod_nome = 'condominios_responsabilidades')".($responsavel_id ? " OR
        (tab_usuarios.usu_id = $responsavel_id)" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }
  
  function buscaAtividades($atividade_id, $id = false) {
    $query = "
      SELECT
        tab_condominios_atividades_tipos.cai_id AS id,
        tab_condominios_atividades_tipos.cai_nome AS nome
      FROM
        tab_condominios_atividades_tipos
        INNER JOIN rel_cat_cai_estacontido ON (tab_condominios_atividades_tipos.cai_id = rel_cat_cai_estacontido.cai_id)
      WHERE
        rel_cat_cai_estacontido.cat_id = $atividade_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $atividades[] = $id ? $dados["id"] : $dados["nome"];
    }
    return $atividades;
  }

  function geraCheckList($nome, $itens, $selecionados = null, $checked = false) {
    $i = 1;
    $selecionados = $selecionados ? $selecionados : array();
    $checkboxes[] = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
    foreach ($itens as $valor => $item) {
      $checkboxes[] = "<tr class=\"txtAzulEscuro11\"><td width=\"25\"><input type=\"checkbox\" name=\"{$nome}[]\" id=\"{$nome}_{$i}\" value=\"$valor\"".((in_array($valor, $selecionados) || $checked) ? " checked" : "")." style=\"width:14px;height:14px\"></td><td><label for=\"{$nome}_{$i}\">$item</label></td></tr>";
      $i++;
    }
    $checkboxes[] = "</table>";
    return @implode("", $checkboxes);
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    unset($this->conteudo);
    if ($this->dados_item) {
      $this->conteudo .= '<script type="text/javascript">window.opener.buscaAtividades('.$this->dados_item["condominio_id"].');</script>';
    }
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
  	$dados_condominio = $this->buscaDadosCondominio($_GET["c"]);
    if ($dados_condominio) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_atividades.adicionar.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&c={$_GET["c"]}", $this->conteudo);
        $this->conteudo = str_replace('[condominio]', $dados_condominio["nome"], $this->conteudo);
        $this->conteudo = str_replace('[atividades]', $this->geraCheckList("atividade", $this->buscaAtividadesTipos()), $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $dados_condominio["responsavel_id"], true, "responsavel", "100%"), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        // Formata Dados
        $_POST["inicio"] = $_POST["inicio"] ? "'".substr($_POST["inicio"], 6, 4)."-".substr($_POST["inicio"], 3, 2)."-".substr($_POST["inicio"], 0, 2)."'" : "NULL";
        $_POST["termino"] = $_POST["termino"] ? "'".substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)."'" : "NULL";
        $_POST["conteudo"] = trim($_POST["conteudo"]);
        // Insere Registro
        $query = "INSERT INTO tab_condominios_atividades (cat_data_hora, cat_data_inicio, cat_data_termino, cat_conteudo, con_id, usu_id) VALUES (NOW(), {$_POST["inicio"]}, {$_POST["termino"]}, '{$_POST["conteudo"]}', {$_GET["c"]}, {$_POST["responsavel"]})";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Registra Atividades
        foreach ($_POST["atividade"] as $atividade_tipo) {
          $query = "INSERT INTO rel_cat_cai_estacontido (cat_id, cai_id) VALUES ($id, $atividade_tipo)";
          $this->bancodedados->query($query);
        }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios_atividades.ver.inc.php");
      $this->conteudo = str_replace('[condominio]', $this->dados_item["condominio_nome"], $this->conteudo);
      $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[atividades]', @implode(", ", $this->buscaAtividades($this->dados_item["id"])), $this->conteudo);
      $this->conteudo = str_replace('[conteudo]', $this->dados_item["conteudo"], $this->conteudo);
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"]) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_atividades.editar.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
        $this->conteudo = str_replace('[condominio]', $this->dados_item["condominio_nome"], $this->conteudo);
        $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $this->dados_item["responsavel_id"], true, "responsavel", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
        $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
        $this->conteudo = str_replace('[atividades]', $this->geraCheckList("atividade", $this->buscaAtividadesTipos(), $this->dados_item["atividades"]), $this->conteudo);
        $this->conteudo = str_replace('[conteudo]', $this->dados_item["conteudo"], $this->conteudo);
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        // Formata Dados
        $_POST["inicio"] = $_POST["inicio"] ? "'".substr($_POST["inicio"], 6, 4)."-".substr($_POST["inicio"], 3, 2)."-".substr($_POST["inicio"], 0, 2)."'" : "NULL";
        $_POST["termino"] = $_POST["termino"] ? "'".substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)."'" : "NULL";
        $_POST["conteudo"] = trim($_POST["conteudo"]);
        // Atualiza Atividade
        $query = "UPDATE tab_condominios_atividades SET cat_data_inicio = {$_POST["inicio"]}, cat_data_termino = {$_POST["termino"]}, cat_conteudo = '{$_POST["conteudo"]}', usu_id = {$_POST["responsavel"]} WHERE cob_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);
        // Registra Atividades
        $query = "DELETE FROM rel_cat_cai_estacontido WHERE cat_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        foreach ($_POST["atividade"] as $atividade_tipo) {
          $query = "INSERT INTO rel_cat_cai_estacontido (cat_id, cai_id) VALUES ({$_GET["i"]}, $atividade_tipo)";
          $this->bancodedados->query($query);
        }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_atividades.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "DELETE FROM rel_cat_cai_estacontido WHERE cat_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_condominios_atividades WHERE cat_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### RDS ###
  ###########

  
  function buscaCondominioAtividades($condominio_id) {
    $query = "
      SELECT
        tab_condominios_atividades.cat_id AS id,
        DATE_FORMAT(tab_condominios_atividades.cat_data_inicio, '%d/%m/%Y') AS inicio,
        DATE_FORMAT(tab_condominios_atividades.cat_data_termino, '%d/%m/%Y') AS termino,
        tab_condominios_atividades.cat_conteudo AS conteudo,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_condominios_atividades
        INNER JOIN tab_usuarios ON (tab_condominios_atividades.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_condominios_atividades.con_id = $condominio_id
      ORDER BY
        tab_condominios_atividades.cat_data_inicio";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["inicio"]) $dados["data"][] = $dados["inicio"];
      if ($dados["termino"] && $dados["termino"] != $dados["inicio"]) $dados["data"][] = $dados["termino"];
      $dados["data"] = implode("<br>", $dados["data"]);
      $dados["atividades"] = @implode(", ", $this->buscaAtividades($dados["id"]));
      if ($this->permissoes["condominios_atividades"]["ver"]) {
  		  $dados["link"] = "javascript:verAtividade({$dados["id"]})";
      }
      $atividades[] = $dados;
    }
    return $this->geraListagemCondominioAtividades($atividades);
  }

  function geraListagemCondominioAtividades($dados) {
    $cabecalho = array ("data" => "Data", "atividades" => "Atividade(s)", "responsavel" => "Responsável", "conteudo" => "Comentários");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(70, 170, 170), null, array("center"));
    return $listagem->conteudo;
  }

}

?>
