<?

require_once("_global/modulo.class.inc.php");

class mensagens extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
      $this->buscaDadosAcao(empty($_GET["a"]) ? "buscar" : $_GET["a"]);
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function buscaDadosItem($mensagem_id) {
    $query = "
      SELECT
        tab_mensagens.men_id AS id,
        tab_mensagens.men_parametros AS parametros,
        tab_mensagens.cli_id AS cliente_id,
        tab_mensagens.mes_id AS status_id,
        tab_mensagens.mlm_id AS modelo_id,
        tab_mensagens.mel_id AS lote_id,
        DATE_FORMAT(men_data_hora_geracao, '%d/%m/%Y (%H:%i)') AS data_geracao,
        DATE_FORMAT(men_data_hora_envio, '%d/%m/%Y (%H:%i)') AS data_envio,
        tab_mensagens_lotes.mlt_id AS tipo_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_mensagens_status.mes_nome AS status_nome,
        CONCAT(tab_mensagens_lotes_modelos.mlm_nome, ' (v. ', tab_mensagens_lotes_modelos.mlm_versao, ')') AS modelo_nome,
        tab_mensagens_lotes_modelos.mlm_conteudo_texto AS conteudo_texto,
        tab_mensagens_lotes_modelos.mlm_conteudo_html AS conteudo_html,
        tab_mensagens_lotes.mel_nome AS lote_nome,
        tab_mensagens_lotes_tipos.mlt_nome AS tipo_nome
      FROM
        tab_mensagens
        INNER JOIN tab_clientes ON (tab_mensagens.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_mensagens_status ON (tab_mensagens.mes_id = tab_mensagens_status.mes_id)
        INNER JOIN tab_mensagens_lotes ON (tab_mensagens.mel_id = tab_mensagens_lotes.mel_id)
        INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
        INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
      WHERE
        tab_mensagens.men_id = $mensagem_id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["parametros"] = @unserialize($this->dados_item["parametros"]);
      $this->dados_item["comentarios"] = $this->buscaDadosComentario($mensagem_id);
      $this->dados_item["detalhes"] = $this->buscaDadosLinks($mensagem_id);
    }
  }

  function buscaCondominio($condominio_id) {
  	$condominio_id = preg_replace_callback("/[^0-9]/", "", $condominio_id);
  	if ($condominio_id) {
	    $query = "SELECT con_nome AS nome FROM tab_condominios WHERE con_id = $condominio_id";
	    $query = $this->bancodedados->query($query);
	    return $this->bancodedados->resultado($query);
  	}
  }

  #############
  ### Geral ###
  #############

  function geraSelect($itens, $selecionado = null, $item_nulo = false) {
    if ($item_nulo) $select = "<option></option>";
    foreach ($itens as $valor => $item) {
      $select .= "<option value=\"$valor\"".(isset($selecionado) && $valor == $selecionado ? " selected" : "").">$item</option>";
    }
    return $select;
  }

  ##############
  ### Buscas ###
  ##############

  function buscaTipos() {
    $query = "
      SELECT
        mlt_id AS id,
        mlt_nome AS nome
      FROM
        tab_mensagens_lotes_tipos
      ORDER BY
        mlt_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaLotes() {
    $query = "
      SELECT
        mel_id AS id,
        mel_nome AS nome
      FROM
        tab_mensagens_lotes
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaCondominios() {
    $query = "
      SELECT
        con_id AS id,
        con_nome AS nome
      FROM
        tab_condominios
      WHERE
        cos_id IS NOT NULL
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaStatus() {
    $query = "
      SELECT
        mes_id AS id,
        mes_nome AS nome
      FROM
        tab_mensagens_status
      ORDER BY
        mes_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaModelos() {
    $query = "
      SELECT
        mlm_id AS id,
        CONCAT(mlm_nome, ' (v. ', mlm_versao, ')') AS nome
      FROM
        tab_mensagens_lotes_modelos
      ORDER BY
        mlm_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaDestinos() {
    $lista["0"] = "Todos os e-mails";
    $lista["1"] = "E-mails de contato";
    $lista["2"] = "E-mails Wave System";
    return $lista;
  }

  ###################
  ### Comentários ###
  ###################

  function geraListagemComentarios($comentarios) {
    for ($i = 0; $i < sizeof($comentarios); $i++) {
      $comentarios[$i]["responsavel"] = ($comentarios[$i]["responsavel_id"]) ? $comentarios[$i]["responsavel_nome"] : "Sistema";
      $comentarios[$i]["detalhamento"][] = "<b>".$comentarios[$i]["responsavel"]."</b> em <b>".$comentarios[$i]["data_hora"]."</b>";
      if ($comentarios[$i]["conteudo"]) $comentarios[$i]["detalhamento"][] = preg_replace("/[\r\n\t ]+/", " ", $comentarios[$i]["conteudo"]);
      $comentarios[$i]["detalhamento"] = @implode('<br>', $comentarios[$i]["detalhamento"]);
    }
    $cabecalho = array ("detalhamento" => "Detalhamento de Comentários");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  function buscaDadosComentario($id) {
    $query = "
      SELECT
        tab_mensagens_comentarios.mec_id AS id,
        DATE_FORMAT(tab_mensagens_comentarios.mec_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        CONCAT('<b>Ação</b>: ', tab_mensagens_comentarios_categorias.mcc_nome, IF(tab_mensagens_comentarios.mec_conteudo IS NOT NULL AND tab_mensagens_comentarios.mec_conteudo <> '', CONCAT('<br><b>Comentários</b>: ', tab_mensagens_comentarios.mec_conteudo), '')) AS conteudo,
        tab_usuarios.usu_id AS responsavel_id,
        IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel_nome
      FROM
        tab_mensagens_comentarios
        INNER JOIN tab_mensagens_comentarios_categorias ON (tab_mensagens_comentarios.mcc_id = tab_mensagens_comentarios_categorias.mcc_id)
        LEFT OUTER JOIN tab_usuarios ON (tab_mensagens_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_mensagens_comentarios.men_id = $id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados;
    }
    return $lista;
  }

  #############
  ### Links ###
  #############

  function geraListagemLinks($itens) {
    $cabecalho = array ("url" => "URL", "data_hora_envio" => "Último Envio", "data_hora_visualizacao" => "Visualização");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function buscaDadosLinks($id) {
    $query = "
      SELECT DISTINCT
        tab_mensagens_links.mei_id AS id,
        CONCAT('/?m=', tab_mensagens_links.mei_url_modulo, '&a=', tab_mensagens_links.mei_url_acao, IF(tab_mensagens_links.mei_url_id IS NOT NULL, CONCAT('&id=', tab_mensagens_links.mei_url_id), '')) AS url,
        DATE_FORMAT(tab_mensagens_links.mei_datahora_visualizacao, '%d/%m/%Y (%H:%i)') AS data_hora_visualizacao,
        DATE_FORMAT(MAX(tab_mensagens_envios.mee_datahora), '%d/%m/%Y (%H:%i)') AS data_hora_envio
      FROM
        tab_mensagens_links
        LEFT OUTER JOIN tab_mensagens_envios ON (tab_mensagens_links.men_id = tab_mensagens_envios.men_id)
      WHERE
        tab_mensagens_links.men_id = $id
      GROUP BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados;
    }
    return $lista;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo = $this->carregaArquivo("mensagens.ver.inc.php");
      $this->conteudo = str_replace("[lote]", $this->dados_item["lote_nome"], $this->conteudo);
      $this->conteudo = str_replace("[cliente]", "<span style=\"text-decoration:underline;cursor:pointer\" onclick=\"window.location = './?m=clientes&a=ver_dc&i={$this->dados_item["cliente_id"]}'\">".$this->dados_item["cliente_nome"]."</span>", $this->conteudo);
      $this->conteudo = str_replace("[mostra_conteudo_tipo_1]", ($this->dados_item["tipo_id"] == 1) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
      $this->conteudo = str_replace("[mostra_conteudo_tipo_2]", ($this->dados_item["tipo_id"] == 2) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
      $this->conteudo = str_replace("[remetente]", ($this->dados_item["parametros"]["remetente_nome"]) ? $this->dados_item["parametros"]["remetente_nome"]." / ".$this->dados_item["parametros"]["remetente_email"] : $this->dados_item["parametros"]["remetente_email"], $this->conteudo);
      $this->conteudo = str_replace("[destinatarios]", @implode("; ", $this->dados_item["parametros"]["destinatarios"]), $this->conteudo);
      $this->conteudo = str_replace("[cc]", ($this->dados_item["parametros"]["cc_nome"]) ? $this->dados_item["parametros"]["cc_nome"]." / ".$this->dados_item["parametros"]["cc_email"] : @implode("; ", $this->dados_item["parametros"]["cc_email"]), $this->conteudo);
      $this->conteudo = str_replace("[bcc]", ($this->dados_item["parametros"]["bcc_nome"]) ? $this->dados_item["parametros"]["bcc_nome"]." / ".$this->dados_item["parametros"]["bcc_email"] : @implode("; ", $this->dados_item["parametros"]["bcc_email"]), $this->conteudo);
      $this->conteudo = str_replace("[reply]", ($this->dados_item["parametros"]["reply_nome"]) ? $this->dados_item["parametros"]["reply_nome"]." / ".$this->dados_item["parametros"]["reply_email"] : $this->dados_item["parametros"]["reply_email"], $this->conteudo);
      $this->conteudo = str_replace("[assunto]", $this->dados_item["parametros"]["assunto"], $this->conteudo);
      $this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[modelo]", $this->dados_item["modelo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
      $this->conteudo = str_replace("[data_envio]", $this->dados_item["data_envio"], $this->conteudo);
      $this->conteudo = str_replace("[links]", $this->geraListagemLinks($this->dados_item["detalhes"]), $this->conteudo);
      $this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->permissoes["mensagens_lotes_modelos"]["ver"]) {
        $botoes[] = '<input type="button" name="visualizar" class="buttonAzulEscuroBranco11" value="Visualizar" onclick="verModelo('.$_GET["i"].');">';
      }
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back();">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=buscar");
    }
  }

  ##################
  ### Visualizar ###
  ##################

  function visualizar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if ($_GET["content"] == "message") {
        if($this->dados_item["conteudo_html"]) {
          $this->conteudo = $this->dados_item["conteudo_html"];
          $this->conteudo = str_replace("cid:logo@wavesystem", "imagens/logo.jpg", $this->conteudo);
          $this->conteudo = str_replace("<imagem:logo_ole.jpg>", "imagens/logo_ole_pequeno.jpg", $this->conteudo);
        } else {
          $this->conteudo = $this->dados_item["conteudo_texto"];
        }
        $this->conteudo = str_replace("<a href=", "<a target='_blank' href=", $this->conteudo);
        if ($this->dados_item["parametros"]["dados_conteudo"]) {
          foreach ($this->dados_item["parametros"]["dados_conteudo"] as $chave => $valor) {
            $this->conteudo = str_replace("[$chave]", $valor, $this->conteudo);
          }
        }
        die($this->conteudo);
      } else {
        $this->conteudo = $this->carregaArquivo("mensagens.visualizar.inc.php");
        $this->conteudo = str_replace("[id]", $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace("[destinatarios]", @implode("; ", $this->dados_item["parametros"]["destinatarios"]), $this->conteudo);
        $this->conteudo = str_replace("[assunto]", $this->dados_item["parametros"]["assunto"], $this->conteudo);
        $this->conteudo = str_replace("[data_envio]", $this->dados_item["data_envio"], $this->conteudo);
        // Botões
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close();">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      }
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=buscar");
    }
  }

  ##############
  ### Buscar ###
  ##############

  function buscar() {
    if ($_POST) {
      $id = md5(uniqid(rand(), true));
      $_SESSION["POST"][$id] = $_POST;
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar&i=$id");
      exit;
    } elseif ($_SESSION["POST"][$_GET["i"]]) {
      $_POST = $_SESSION["POST"][$_GET["i"]];
    }
    $this->conteudo .= $this->carregaArquivo("mensagens.buscar.inc.php");
    if (empty($_POST["condominio2"])) {
      $this->conteudo = str_replace('[condominio1]', $_POST["condominio1"], $this->conteudo);
      $this->conteudo = str_replace('[condominio2]', $this->geraSelect($this->buscaCondominios()), $this->conteudo);
    } else {
      $this->conteudo = str_replace('[condominio1]', $this->buscaCondominio($_POST["condominio2"]), $this->conteudo);
      $this->conteudo = str_replace('[condominio2]', $this->geraSelect($this->buscaCondominios(), $_POST["condominio2"]), $this->conteudo);
    }
    $this->conteudo = str_replace('[lotes]', $this->geraSelect($this->buscaLotes(), $_POST["lote"]), $this->conteudo);
    $this->conteudo = str_replace('[tipos]', $this->geraSelect($this->buscaTipos(), $_POST["tipo"]), $this->conteudo);
    $this->conteudo = str_replace('[modelos]', $this->geraSelect($this->buscaModelos(), $_POST["modelo"]), $this->conteudo);
    $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $_POST["status"]), $this->conteudo);
    $this->conteudo = str_replace('[data1]', $_POST["data1"], $this->conteudo);
    $this->conteudo = str_replace('[data2]', $_POST["data2"], $this->conteudo);
    $this->conteudo = str_replace('[cliente_nome]', $_POST["cliente_nome"], $this->conteudo);
    $this->conteudo = str_replace('[resultado]', $this->geraResultadoBusca($_POST), $this->conteudo);
    $botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
    $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=buscar", $this->conteudo);
    $this->conteudo = str_replace('[modulo]', $this->dados_modulo["nome"], $this->conteudo);
  }

  function geraResultadoBusca($dados) {
    if (!empty($dados["g"])) {
      if ($dados["lote"]) {
        $where[] = "(tab_mensagens.mel_id = {$dados["lote"]})";
      }
      if ($dados["tipo"]) {
        $where[] = "(tab_mensagens_lotes_modelos.mlt_id = {$dados["tipo"]})";
      }
      if ($dados["modelo"]) {
        $where[] = "(tab_mensagens_lotes_modelos.mlm_id = {$dados["modelo"]})";
      }
      if ($dados["status"]) {
        $where[] = "(tab_mensagens.mes_id = {$dados["status"]})";
      }
      if ($dados["data1"]) {
        $dados["data1"] = substr($dados["data1"], 6, 4)."-".substr($dados["data1"], 3, 2)."-".substr($dados["data1"], 0, 2);
        $where[] = "(tab_mensagens.men_data_hora_envio >= '{$dados["data1"]} 00:00:00')";
      }
      if ($dados["data2"]) {
        $dados["data2"] = substr($dados["data2"], 6, 4)."-".substr($dados["data2"], 3, 2)."-".substr($dados["data2"], 0, 2);
        $where[] = "(tab_mensagens.men_data_hora_envio <= '{$dados["data2"]} 23:59:59')";
      }
      if (trim($dados["cliente_nome"])) {
        $dados["cliente_nome"] = trim($dados["cliente_nome"]);
        $where[] = "(tab_clientes.cli_nome LIKE '%{$dados["cliente_nome"]}%')";
      }
      if (trim($dados["condominio1"])) {
        $dados["condominio1"] = trim($dados["condominio1"]);
        $where[] = "(tab_condominios.con_nome LIKE '%{$dados["condominio1"]}%')";
      }
      if ($dados["condominio2"]) {
        $where[] = "(tab_clientes.con_id = {$dados["condominio2"]})";
      }
      $where = @implode(" AND\n          ", $where);

      $query = "
        SELECT DISTINCT
          tab_mensagens.men_id AS id,
          tab_clientes.cli_nome AS cliente,
          tab_condominios.con_nome AS condominio,
          tab_mensagens_status.mes_nome AS status,
          DATE_FORMAT(max(tab_mensagens_envios.mee_datahora),'%d/%m/%Y') AS data_envio,
          CONCAT(tab_mensagens_lotes_modelos.mlm_nome, ' (v. ', tab_mensagens_lotes_modelos.mlm_versao, ')') AS modelo,
          tab_mensagens_lotes.mel_nome AS lote,
          tab_mensagens_lotes_tipos.mlt_nome AS tipo
        FROM
          tab_mensagens
          LEFT OUTER JOIN tab_mensagens_envios ON (tab_mensagens.men_id = tab_mensagens_envios.men_id)
          INNER JOIN tab_clientes ON (tab_mensagens.cli_id = tab_clientes.cli_id)
          LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
          INNER JOIN tab_mensagens_lotes ON (tab_mensagens.mel_id = tab_mensagens_lotes.mel_id)
          INNER JOIN tab_mensagens_status ON (tab_mensagens.mes_id = tab_mensagens_status.mes_id)
          INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
          INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
        WHERE
          $where
        GROUP BY
          id
        ORDER BY
          tab_mensagens_envios.mee_datahora,
          tab_mensagens_lotes.mel_nome";
      $query = $this->bancodedados->query($query);
      $registros = array();
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        if ($this->permissoes["mensagens"]["ver"]) {
        	$dados["link"] = "?m=mensagens&a=ver&i={$dados["id"]}";
        }
        $dados["id"] = count($registros) + 1;
        $registros[] = $dados;
      }
      $cabecalho = array ("id" => "#", "lote" => "Lote", "data_envio" => "Último Envio", "cliente" => "Cliente", "condominio" => "Condominio", "status" => "Status");
      $listagem = controle::instanciaObjeto("listagem", $cabecalho, $registros);
      return $listagem->conteudo;
    }
    return "Nenhum registro encontrado!";
  }
  
  function geraMensagensClientesListagem($dados) {
    $cabecalho = array ("id" => "#", "lote" => "Lote", "data_envio" => "Data Envio", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array("20"), null, array("right", "left"));
    return $listagem->conteudo;
  }
  
  function buscaMensagensClientes($cliente_id) {
    $query = "
      SELECT DISTINCT
        tab_mensagens.men_id AS id,
        tab_mensagens_status.mes_nome AS status,
        DATE_FORMAT(max(tab_mensagens_envios.mee_datahora),'%d/%m/%Y') AS data_envio,
        CONCAT(tab_mensagens_lotes_modelos.mlm_nome, ' (v. ', tab_mensagens_lotes_modelos.mlm_versao, ')') AS modelo,
        tab_mensagens_lotes.mel_nome AS lote,
        tab_mensagens_lotes_tipos.mlt_nome AS tipo
      FROM
        tab_mensagens
        LEFT OUTER JOIN tab_mensagens_envios ON (tab_mensagens.men_id = tab_mensagens_envios.men_id)
        INNER JOIN tab_mensagens_lotes ON (tab_mensagens.mel_id = tab_mensagens_lotes.mel_id)
        INNER JOIN tab_mensagens_status ON (tab_mensagens.mes_id = tab_mensagens_status.mes_id)
        INNER JOIN tab_mensagens_lotes_modelos ON (tab_mensagens.mlm_id = tab_mensagens_lotes_modelos.mlm_id)
        INNER JOIN tab_mensagens_lotes_tipos ON (tab_mensagens_lotes.mlt_id = tab_mensagens_lotes_tipos.mlt_id)
      WHERE
        tab_mensagens.cli_id = $cliente_id
      GROUP BY
        id
      ORDER BY
        tab_mensagens_envios.mee_datahora,
        tab_mensagens_lotes.mel_nome";
    $query = $this->bancodedados->query($query);
    $mensagens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["mensagens"]["visualizar"]) {
        $dados["link"] = "javascript:verModelo({$dados["id"]})";
      }
      $dados["id"] = count($mensagens) + 1;
      $mensagens[] = $dados;
    }
    return $mensagens;
  }

}

?>
