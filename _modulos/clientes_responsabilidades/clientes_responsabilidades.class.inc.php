<?

require_once("_global/modulo.class.inc.php");

class clientes_responsabilidades extends modulo {

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

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_clientes_responsabilidades.cln_id AS id,
        tab_clientes_responsabilidades.cod_id AS pai,
        DATE_FORMAT(tab_clientes_responsabilidades.cln_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_clientes_responsabilidades.cln_termino,'%d/%m/%Y (%H:%i)') AS termino,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_usuarios.usu_id AS responsavel_id,
        tab_usuarios.usu_nome AS responsavel_nome,
        tab_clientes_responsabilidades_categorias.crt_id AS categoria_id,
        tab_clientes_responsabilidades_categorias.crt_nome AS categoria_nome,
        tab_clientes_responsabilidades_tipos.cri_id AS tipo_id,
        tab_clientes_responsabilidades_tipos.cri_nome AS tipo_nome,
        tab_clientes_responsabilidades_status.cra_id AS status_id,
        tab_clientes_responsabilidades_status.cra_nome AS status_nome
      FROM
  	    tab_clientes_responsabilidades
  	    INNER JOIN tab_usuarios ON (tab_clientes_responsabilidades.usu_id = tab_usuarios.usu_id)
  	    INNER JOIN tab_clientes ON (tab_clientes_responsabilidades.cli_id = tab_clientes.cli_id)
  	    LEFT OUTER JOIN tab_clientes_responsabilidades_categorias ON (tab_clientes_responsabilidades.crt_id = tab_clientes_responsabilidades_categorias.crt_id)
  	    INNER JOIN tab_clientes_responsabilidades_tipos ON (tab_clientes_responsabilidades.cri_id = tab_clientes_responsabilidades_tipos.cri_id)
  	    INNER JOIN tab_clientes_responsabilidades_status ON (tab_clientes_responsabilidades.cra_id = tab_clientes_responsabilidades_status.cra_id)
      WHERE
  	    cln_id = $id".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if($this->dados_item) {
      $this->dados_item["comentarios"] = $this->buscaComentarios($this->dados_item["id"]);
    }
  }

  function buscaDadosCliente($cliente_id) {
    $query = "
      SELECT 
        cli_id AS id, 
        cli_nome AS nome, 
        usu_id AS responsavel_id 
      FROM 
        tab_clientes 
      WHERE 
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    if($this->bancodedados->linhas($query)) {
      return $this->bancodedados->resultadoArray($query);
    }
  }

  function buscaDadosPeriodo($id) {
    $query = "
      SELECT
        cli_id AS cliente,
        usu_id AS usuario,
        IF((NOW() BETWEEN cln_inicio AND cln_termino) AND (cri_id = 2), 1, 0) AS atual
      FROM
  	    tab_clientes_responsabilidades
      WHERE
  	    cln_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosResponsabilidade($id) {
    $query = "
      SELECT
        cli_id AS cliente,
        cri_id AS tipo,
        cln_inicio AS inicio,
        cln_termino AS termino,
        usu_id AS usuario
      FROM
  	    tab_clientes_responsabilidades
      WHERE
  	    cln_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaResponsabilidadesAnteriores($dados) {
    $query = "
      SELECT
        cli_id AS cliente,
        usu_id AS usuario
      FROM
  	    tab_clientes_responsabilidades
      WHERE
  	    cri_id = {$dados["tipo"]} AND
  	    cli_id = {$dados["cliente"]} AND
  	    usu_id != {$dados["usuario"]} AND
  	    cln_termino BETWEEN DATE_SUB('{$dados["inicio"]}', INTERVAL 3 DAY) AND '{$dados["inicio"]}'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaUsuarioNome($id) {
    $query = "SELECT usu_nome AS nome FROM tab_usuarios WHERE usu_id = '$id'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query,0,"nome");
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaTipos() {
    $query = "
      SELECT
        cri_id AS id,
        cri_nome AS nome
      FROM
  	    tab_clientes_responsabilidades_tipos
      ORDER BY
  	    cri_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaStatus() {
    $query = "
      SELECT
        cra_id AS id,
        cra_nome AS nome
      FROM
  	    tab_clientes_responsabilidades_status
      ORDER BY
  	    cra_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaCategorias($herdado = false) {
    $query = "
      SELECT
        crt_id AS id,
        crt_nome AS nome
      FROM
        tab_clientes_responsabilidades_categorias
      WHERE
        crt_disponivel = 1
      ORDER BY
        crt_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categorias[$dados["id"]] = $dados["nome"];
    }
    if ($herdado) {
      $categorias[0] = "Herdado do Condomínio";
    }
    return $categorias;
  }

  function buscaCategoriaParametros($id) {
    $query = "
      SELECT
        crt_parametros AS parametros
     FROM
  	    tab_clientes_responsabilidades_categorias
     WHERE
        crt_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "parametros");
  }

  function buscaDadosPrazo($responsabilidade_id, $categoria_id) {
    $dados["parametros"] = $this->buscaCategoriaParametros($categoria_id);
    if ($dados["parametros"]) {
	    $parametros = unserialize($dados["parametros"]);
	    if ($parametros["prazo"]) {
		    $query = "
		      SELECT
		        DATE_ADD(cln_inicio, INTERVAL {$parametros["prazo"]} DAY) AS termino
		     FROM
		  	    tab_clientes_responsabilidades
		     WHERE
		        cln_id = $responsabilidade_id";
		    $query = $this->bancodedados->query($query);
		    $dados["termino"] = $this->bancodedados->resultado($query);
		    return $dados;
	    }
    }
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

  ###################
  ### Comentários ###
  ###################

  function buscaComentarios($id) {
    $query = "
      SELECT
			  IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
        DATE_FORMAT(tab_clientes_responsabilidades_comentarios.crc_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_clientes_responsabilidades_comentarios.crc_conteudo AS conteudo
      FROM
        tab_clientes_responsabilidades_comentarios
        LEFT OUTER JOIN tab_usuarios ON (tab_clientes_responsabilidades_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        cln_id = $id
      ORDER BY
        tab_clientes_responsabilidades_comentarios.crc_datahora ASC,
        tab_clientes_responsabilidades_comentarios.crc_id ASC";
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
    $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  function geraComentariosListagem ($comentarios) {
    $cabecalho = array("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
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

  function formataDatas ($inicio, $prazo) {
    if (!$inicio) {
      $datas["inicio"] = date("Y-m-d H:i:s");
    } else {
      $datas["inicio"] = str_replace("(", "", $inicio);
      $datas["inicio"] = str_replace(")", "", $datas["inicio"]);
      $datas["inicio"] = $datas["inicio"] . ":00";
      $data_hora = explode(" ", $datas["inicio"]);
      $data = explode("/", $data_hora[0]);
      $hora = explode(":", $data_hora[1]);
      $datas["inicio"] = "{$data[2]}-{$data[1]}-{$data[0]} {$hora[0]}:{$hora[1]}:{$hora[2]}";
    }
    $datas["termino"] = date("Y-m-d H:i:s", strtotime("{$datas["inicio"]} +$prazo days"));
    return $datas;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
  	$dados_cliente = $this->buscaDadosCliente($_GET["i"]);
    if ($dados_cliente) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.adicionar.inc.php");
        $this->conteudo = str_replace('[action]', '?m=' . $this->dados_modulo["nome"] . '&a=adicionar&i=' . $dados_cliente["id"], $this->conteudo);
        $this->conteudo = str_replace('[cliente]', $dados_cliente["nome"], $this->conteudo);
        $this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(), null, true, "categoria", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $_SESSION["usuario"], true, "responsavel", "100%"), $this->conteudo);
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        // Trata Dados
        $tipo = $this->temPermissao("assumir_responsabilidade", $this->dados_modulo["nome"], $_POST["responsavel"]) ? 2 : 1;
        $parametro = $this->buscaCategoriaParametros($_POST["categoria"]);
        $parametros = unserialize($parametro);
        $datas = $this->formataDatas($_POST["inicio"], $parametros["prazo"]);
        // Insere Registro
        $query = "INSERT INTO tab_clientes_responsabilidades (cli_id, cln_inicio, cln_termino, cln_parametros, usu_id, crt_id, cri_id, cra_id) VALUES ({$_GET["i"]}, '{$datas["inicio"]}', '{$datas["termino"]}', '$parametro', {$_POST["responsavel"]}, {$_POST["categoria"]}, $tipo, 1)";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Registra Comentário
        if (trim($_POST["comentario"])) {
          $comentario = "<br>".trim($_POST["comentario"]);
        }
        $this->registraComentario($id, "<b>Ação:</b> Adicionado".$comentario, $_SESSION["usuario"]);
        
        if (!$dados_cliente["responsavel_id"] && $_POST["categoria"] != 4 && !$this->existeAtivadoAprovado($id, $_POST["responsavel"])) {
          //$this->aprovarResponsabilidade($id);
        } else if (!$dados_cliente["responsavel_id"] && $_POST["categoria"] == 4 && $this->usuarioPosVenda($_POST["responsavel"])) {
          $this->aprovarResponsabilidade($id);
        }
        
        // REgistra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
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
    if(!empty($this->dados_item)) {
      $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.ver.inc.php");
      $this->conteudo = str_replace('[cliente_id]',$this->dados_item["cliente_id"],$this->conteudo);
      $this->conteudo = str_replace('[cliente_nome]',$this->dados_item["cliente_nome"],$this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[categoria]', $this->dados_item["categoria_nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace('[comentarios]',$this->geraComentariosListagem($this->dados_item["comentarios"]),$this->conteudo);

      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$this->dados_item["id"]);
    } else {
      $this->fechar();
    }
  }

  #######################
  ######## Editar #######
  #######################

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if(!empty($this->dados_item)) {
      if (empty($_POST)) {
      	$editavel = $this->ehEditavel() || $this->ehEditavelPlus();
        $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.editar.inc.php");
        $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
        $this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
        $this->conteudo = str_replace('[comentarios]',$this->geraComentariosListagem($this->dados_item["comentarios"]),$this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
        $this->conteudo = str_replace('[termino]', $this->geraInput("text", "termino", "50%", $this->dados_item["termino"], $this->ehProrrogavel(), "8", null, "carregaTarefaCalendarioNavegacao(this.name, 1, this.value)"), $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true, "tipo", "50%", null, $editavel), $this->conteudo);
        $this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(true), $this->dados_item["categoria_id"], true, "categoria", "100%", null, $editavel), $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(true, $this->dados_item["responsavel_id"]), $this->dados_item["responsavel_id"], true, "responsavel", "100%", null, $editavel), $this->conteudo);
        if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" value="Reprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=finalizar&i=' . $this->dados_item["id"] . '\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        if ($this->ehRemovivel() || $this->ehRemovivelPlus()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';

        $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"],$this->conteudo);
      } else {
        if ($this->dados_item["status_id"] == 3) {
          $status = 1;
        } else {
          $status = $this->dados_item["status_id"];
        }

        if ($this->ehEditavel() || $this->ehEditavelPlus()) {
          if ($this->dados_item["tipo_id"] != $_POST["tipo"] || $this->dados_item["responsavel_id"] != $_POST["responsavel"]) {
            if ($this->temPermissao("assumir_responsabilidade", $this->dados_modulo["nome"], $_POST["responsavel"]) == ($_POST["tipo"] == 2)) {
              $query = "UPDATE tab_clientes_responsabilidades SET cri_id = '{$_POST["tipo"]}', usu_id = '{$_POST["responsavel"]}', cra_id = '$status' WHERE cln_id = '{$_GET["i"]}'";
              $this->bancodedados->query($query);
              $alterado = true;
            }
          }
          if ($this->dados_item["categoria_id"] != $_POST["categoria"]) {
            $dados = $this->buscaDadosPrazo($_GET["i"], $_POST["categoria"]);
            if (!$this->existeAtivadoAprovadoNoTermino($_GET["i"], $dados["termino"], $_POST["tipo"])) {
              $query = "UPDATE tab_clientes_responsabilidades SET crt_id = '{$_POST["categoria"]}', cra_id = '$status', cln_parametros = '{$dados["parametros"]}', cln_termino = '{$dados["termino"]}' WHERE cln_id = '{$_GET["i"]}'";
              $this->bancodedados->query($query);
              $alterado = true;
            }
          }
        }

        if ($this->ehProrrogavel()) {
          if ($this->dados_item["termino"] != $_POST["termino"]) {
            $_POST["termino"] = "'".substr($_POST["termino"], 6, 4)."-".substr($_POST["termino"], 3, 2)."-".substr($_POST["termino"], 0, 2)." ".substr($_POST["termino"], 12, 2).":".substr($_POST["termino"], 15, 2).":00'";
            if (!$_POST["tipo"]) $_POST["tipo"] = $this->dados_item["tipo_id"];
            if (!$this->existeAtivadoAprovadoNoTermino($_GET["i"], $_POST["termino"], $_POST["tipo"])) {
              $query = "UPDATE tab_clientes_responsabilidades SET cln_termino = {$_POST["termino"]}, cra_id = '$status' WHERE cln_id = '{$_GET["i"]}'";
              $this->bancodedados->query($query);
              $alterado = true;
            }
          }
        }

        if ($alterado || !empty($_POST["comentario"])) {
          if (!empty($_POST["comentario"])) $comentario = "<br>" . trim($_POST["comentario"]);
          $this->registraComentario($_GET["i"], "<b>Ação:</b> Editado".$comentario, $_SESSION["usuario"]);
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$_GET["i"]);
        }
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
      }
    } else {
      $this->fechar();
    }
  }

  #######################
  ####### Aprovar #######
  #######################

  function aprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item) && $this->ehAprovavel()) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.aprovar.inc.php");
        if ($this->existeAtivadoAprovado($_GET["i"])) {
          $this->conteudo = str_replace('[conflito]', '1', $this->conteudo);
        } else {
          $this->conteudo = str_replace('[conflito]', '0', $this->conteudo);
        }
        if ($this->permissoes[$this->dados_modulo["nome"]]["aprovar_plus"]) {
          $this->conteudo = str_replace('[permissao]', '1', $this->conteudo);
        } else {
          $this->conteudo = str_replace('[permissao]', '0', $this->conteudo);
        }
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $_GET["i"],$this->conteudo);
      } else {
        $this->aprovarResponsabilidade($_GET["i"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$_GET["i"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
      }
    } else {
      $this->fechar();
    }
  }
  
  function aprovarResponsabilidade($id) {
    $dados = $this->buscaDadosPeriodo($id);
    $this->finalizaConflitantes($id);
    // Auto-ativa a responsabilidade caso ela esteja entre periodo vigente
    if ($dados["atual"]) {
      $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '4', cln_inicio = NOW() WHERE cln_id = '$id'";
      $this->bancodedados->query($query);
      $query = "UPDATE tab_clientes SET usu_id = {$dados["usuario"]} WHERE cli_id = '{$dados["cliente"]}'";
      $this->bancodedados->query($query);
      if (!empty($_POST["comentario"])) $comentario = "<br>" . trim($_POST["comentario"]);
      $this->registraComentario($_GET["i"], "<b>Ação:</b> Aprovado com Auto-Ativação".$comentario, $_SESSION["usuario"]);
      // Atualiza Status Cliente
      $this->atualizaStatusCliente($dados["cliente"]);
    } else {
      $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '2' WHERE cln_id = '$id'";
      $this->bancodedados->query($query);
      if (!empty($_POST["comentario"])) $comentario = "<br>" . trim($_POST["comentario"]);
      $this->registraComentario($_GET["i"], "<b>Ação:</b> Aprovado".$comentario, $_SESSION["usuario"]);
    }
    // Envia um retorno para o antigo responsável caso o novo tenha sido aprovado em até 3 dias após o término da responsabilidade do antigo (Fica ligado, mané!)
    $dados = $this->buscaDadosResponsabilidade($id);
    $anteriores = $this->buscaResponsabilidadesAnteriores($dados);
    if (sizeof($anteriores)) {
      $usuario = $this->buscaUsuarioNome($dados["usuario"]);
      for ($i = 0; $i < sizeof($anteriores); $i++) {
        $anteriores = $anteriores[$i];
        $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ({$anteriores["cliente"]}, {$anteriores["usuario"]}, NOW(), NOW(), '$usuario recebeu responsabilidade sobre esse cliente num período de até 3 dias depois do término da sua responsabilidade.', 2, 1)";
        $this->bancodedados->query($query);
      }
    }
  }

  function finalizaConflitantes($id) {
    $dados = $this->buscaDadosResponsabilidade($id);
    $query = "
      SELECT
        cln_id AS id,
        cra_id AS status
      FROM
        tab_clientes_responsabilidades
      WHERE
        (cli_id = {$dados["cliente"]}) AND
        (cra_id IN (2,4)) AND
        (cri_id = {$dados["tipo"]}) AND
        (
          (cln_inicio BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
          (cln_termino BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
          ('{$dados["inicio"]}' BETWEEN cln_inicio AND cln_termino) OR
          ('{$dados["termino"]}' BETWEEN cln_inicio AND cln_termino)
        )";
    $queryConflitos = $this->bancodedados->query($query);
    while (($conflitante = $this->bancodedados->resultadoArray($queryConflitos)) != false) {
      if ($conflitante["status"] == 2) {
        // Conflitante possui Status Análise (Aprovado)
        $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '7' WHERE cln_id = '{$conflitante["id"]}'";
        $this->bancodedados->query($query);
        $this->registraComentario($conflitante["id"], "<b>Ação:</b> Cancelado", $_SESSION["usuario"]);
      } else if ($conflitante["status"] == 4) {
        // Conflitante possui Status Ativo
        $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '5', cln_termino = NOW() WHERE cln_id = '{$conflitante["id"]}'";
        $this->bancodedados->query($query);
        $this->registraComentario($conflitante["id"], "<b>Ação:</b> Finalizado", $_SESSION["usuario"]);
      }
      $dados = $this->buscaDadosPeriodo($conflitante["id"]);
      if ($dados["atual"]) {
        $query = "UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '{$dados["cliente"]}'";
        $this->bancodedados->query($query);
        // Atualiza Status Cliente
        $this->atualizaStatusCliente($dados["cliente"]);
      }
    }
  }

  #######################
  ###### Reprovar #######
  #######################

  function reprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if(!empty($this->dados_item) && $this->ehReprovavel()) {
      if(empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.reprovar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $_GET["i"],$this->conteudo);
      } else {
        $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '3' WHERE cln_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);

        if (!empty($_POST["comentario"])) $comentario = "<br>" . trim($_POST["comentario"]);
        $this->registraComentario($_GET["i"], "<b>Ação:</b> Reprovado".$comentario, $_SESSION["usuario"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$_GET["i"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
      }
    } else {
      $this->fechar();
    }
  }
  
  function usuarioPosVenda($usuario_id) {
  	if ($usuario_id) {
	    $query = "
	      SELECT
	        ors_id
	      FROM
	        rel_usu_orf_ors_exerce
	      WHERE
	        usu_id = $usuario_id AND
	        ors_id IN (6, 15)";
	    $query = $this->bancodedados->query($query);
  	}
  }

  function existeAtivadoAprovado($id, $usuario_id = null) {
    $query = "
      SELECT
        cli_id AS id,
        cri_id AS tipo,
        cln_inicio AS inicio,
        cln_termino AS termino
      FROM
        tab_clientes_responsabilidades
      WHERE
        cln_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);

    $query = "
      SELECT
        COUNT(cln_id) AS quantidade
      FROM
        tab_clientes_responsabilidades
      WHERE
        cli_id = {$dados["id"]} AND
        cra_id IN (2,4) AND
        cri_id = {$dados["tipo"]} AND
        (
          (cln_inicio BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
          (cln_termino BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
          ('{$dados["inicio"]}' BETWEEN cln_inicio AND cln_termino) OR
          ('{$dados["termino"]}' BETWEEN cln_inicio AND cln_termino)
        )".($usuario_id ? " AND
        usu_id != $usuario_id" : "");
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query,0,"quantidade")) return true;
    return false;
  }

  function existeAtivadoAprovadoNoTermino ($id, $termino, $tipo) {
    $query = "
      SELECT
        cli_id AS id,
        cln_inicio AS inicio
      FROM
        tab_clientes_responsabilidades
      WHERE
        cln_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);

    $query = "
      SELECT
        COUNT(cln_id) AS quantidade
      FROM
        tab_clientes_responsabilidades
      WHERE
        (cli_id = {$dados["id"]}) AND
        (cra_id IN (2,4)) AND
        (cln_id != $id) AND
        (cri_id = $tipo) AND
        (
          ($termino BETWEEN cln_inicio AND cln_termino) OR
          (cln_inicio BETWEEN '{$dados["inicio"]}' AND $termino) OR
          (cln_termino BETWEEN '{$dados["inicio"]}' AND $termino)
        )";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query,0,"quantidade")) return true;
    return false;
  }

  #######################
  ###### Finalizar ######
  #######################

  function finalizar() {
    $this->buscaDadosItem($_GET["i"]);
    if(!empty($this->dados_item) && $this->ehFinalizavel()) {
      if(empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.finalizar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=finalizar&i=' . $_GET["i"],$this->conteudo);
      } else {
        $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '5', cln_termino = IF(NOW() < cln_inicio, cln_inicio, NOW()) WHERE cln_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);

        $dados = $this->buscaDadosPeriodo($_GET["i"]);
        if ($dados["atual"]) {
          $query = "UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '{$dados["cliente"]}'";
          $this->bancodedados->query($query);
          // Atualiza Status Cliente
          $this->atualizaStatusCliente($dados["cliente"]);
        }

        if (!empty($_POST["comentario"])) $comentario = "<br>" . trim($_POST["comentario"]);
        $this->registraComentario($_GET["i"], "<b>Ação:</b> Finalizado".$comentario, $_SESSION["usuario"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$_GET["i"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
      }
    } else {
      $this->fechar();
    }
  }

  #######################
  ####### Remover #######
  #######################

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item) && ($this->ehRemovivel() || $this->ehRemovivelPlus())) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("clientes_responsabilidades.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $_GET["i"],$this->conteudo);
      } else {
        $query = "DELETE FROM tab_clientes_responsabilidades_comentarios WHERE cln_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_clientes_responsabilidades WHERE cln_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);

        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$_GET["i"]);
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ##########################
  ######### Listar #########
  ##########################

  function listar() {
    if (!$_GET["f"] && !$_GET["p"]) {
      $_GET["f"]["status"] = 1;
    }
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
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
    $responsaveis = $this->buscaResponsaveis(false);
    foreach ($responsaveis as $id => $nome) {
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "responsavel", "descricao" => "Responsável", "valor" => $select_responsaveis, "where" => "tab_clientes_responsabilidades.usu_id = [valor]", "disabled" => ($this->permissoes["clientes_responsabilidades"]["listar_todos"])? false : true);
    if (!$this->permissoes["clientes_responsabilidades"]["listar_todos"]) $valores["responsavel"] = $_SESSION["usuario"];
    // Categorias
    $categorias = $this->buscaCategorias();
    foreach ($categorias as $id => $nome) {
      $select_categorias[] = array ("id" => $id, "nome" => $nome);
    }
    $select_categorias[] = array ("id" => "-1", "nome" => "Cliente prospect");
    $select_categorias[] = array ("id" => "-2", "nome" => "Herdado do Condomínio");
    if ($valores["categoria"]) {
      if ($valores["categoria"] == "-1") {
        $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "tab_clientes_responsabilidades.crt_id IN (1, 2, 3)");
      } elseif ($valores["categoria"] == "-2") {
        $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "tab_clientes_responsabilidades.crt_id IS NULL");
      } else {
        $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "tab_clientes_responsabilidades.crt_id = [valor]");
      }
    } else {
      $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "1");
    }
    // Tipos
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "tab_clientes_responsabilidades.cri_id = [valor]");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio_inicio", "descricao" => "Data Início &raquo; Início", "valor" => "", "where" => "tab_clientes_responsabilidades.cln_inicio >= ".$this->formataData($valores["data_inicio_inicio"]));
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio_termino", "descricao" => "Data Início &raquo; Término", "valor" => "", "where" => "tab_clientes_responsabilidades.cln_inicio <= ".$this->formataData($valores["data_inicio_termino"]));
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
    }
    $select_status[] = array ("id" => "-1", "nome" => "Análise", "where" => "tab_clientes_responsabilidades.cra_id IN (1, 2, 3, 7, 8)");
    $select_status[] = array ("id" => "-1", "nome" => "Inativo", "where" => "tab_clientes_responsabilidades.cra_id IN (5, 6)");
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "tab_clientes_responsabilidades.cra_id = [valor]");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_termino_inicio", "descricao" => "Data Término &raquo; Início", "valor" => "", "where" => "tab_clientes_responsabilidades.cln_termino >= ".$this->formataData($valores["data_termino_inicio"]));
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_termino_termino", "descricao" => "Data Término &raquo; Término", "valor" => "", "where" => "tab_clientes_responsabilidades.cln_termino <= ".$this->formataData($valores["data_termino_termino"]));
    // Gera Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
    return $filtro;
  }

  function formataData($data) {
    return "'".substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2)."'";
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
        tab_clientes.cli_id AS id,
        tab_clientes.cli_nome AS cliente,
        DATE_FORMAT(tab_clientes_responsabilidades.cln_inicio,'%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_clientes_responsabilidades.cln_termino,'%d/%m/%Y (%H:%i)') AS termino,
        tab_usuarios.usu_nome AS responsavel,
        IF(tab_clientes_responsabilidades_categorias.crt_nome IS NULL, 'Herdado do Condomínio', tab_clientes_responsabilidades_categorias.crt_nome) AS categoria_nome,
        tab_clientes_responsabilidades_tipos.cri_nome AS tipo_nome,
        tab_clientes_responsabilidades_status.cra_nome AS status_nome
      FROM
        tab_clientes_responsabilidades
        INNER JOIN tab_clientes_responsabilidades_tipos ON (tab_clientes_responsabilidades.cri_id = tab_clientes_responsabilidades_tipos.cri_id)
        LEFT OUTER JOIN tab_clientes_responsabilidades_categorias ON (tab_clientes_responsabilidades.crt_id = tab_clientes_responsabilidades_categorias.crt_id)
        INNER JOIN tab_clientes_responsabilidades_status ON (tab_clientes_responsabilidades.cra_id = tab_clientes_responsabilidades_status.cra_id)
        INNER JOIN tab_usuarios ON (tab_clientes_responsabilidades.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_clientes ON (tab_clientes_responsabilidades.cli_id = tab_clientes.cli_id)
      WHERE
        $filtro->parametros_sql".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        tab_clientes.cli_nome,
        tab_clientes_responsabilidades.cln_inicio,
        tab_clientes_responsabilidades.cln_termino
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["clientes"]["ver_dm"]) {
        $dados["link"] = "?m=clientes&a=ver_dm&i={$dados["id"]}";
      }
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array("contador" => "#", "cliente" => "Cliente", "inicio" => "Início", "termino" => "Término", "responsavel" => "Responsável", "categoria_nome" => "Categoria", "tipo_nome" => "Tipo", "status_nome" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

  ###############################
  ###### Controle de Ações ######
  ###############################

  function ehAprovavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["aprovar"] && in_array($this->dados_item["status_id"], array(1,3))) return true;
    return false;
  }

  function ehReprovavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && $this->dados_item["status_id"] == 1) return true;
    return false;
  }

  function ehRemovivel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["status_id"] == 1 && !$this->dados_item["pai"] && $this->dados_item["responsavel_id"] == $_SESSION["usuario"]) return true;
    return false;
  }

  function ehRemovivelPlus() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["remover_plus"] && $this->dados_item["status_id"] < 4 && !$this->dados_item["pai"]) return true;
    return false;
  }

  function ehFinalizavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar"] && in_array($this->dados_item["status_id"], array(2,4))) return true;
    return false;
  }

  function ehEditavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && in_array($this->dados_item["status_id"], array(1,3)) && !$this->dados_item["pai"] && $this->dados_item["responsavel_id"] == $_SESSION["usuario"]) return true;
    return false;
  }

  function ehEditavelPlus() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["editar_plus"] && $this->dados_item["status_id"] < 4 && !$this->dados_item["pai"]) return true;
    return false;
  }

  function ehProrrogavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["prorrogar"] && !$this->dados_item["pai"] && $this->dados_item["status_id"] < 5) return true;
    return false;
  }

  ########################################
  ### Busca Clientes Responsabilidades ###
  ########################################

  function buscaClientesResponsabilidades($cliente_id) {
    $query = "
      SELECT
        tab_clientes_responsabilidades.cln_id AS id,
        DATE_FORMAT(tab_clientes_responsabilidades.cln_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_clientes_responsabilidades.cln_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tab_usuarios.usu_nome AS responsavel,
        IF(tab_clientes_responsabilidades_categorias.crt_nome IS NULL, 'Herdado do Condomínio', tab_clientes_responsabilidades_categorias.crt_nome) AS categoria,
        tab_clientes_responsabilidades_tipos.cri_nome AS tipo,
        tab_clientes_responsabilidades_status.cra_nome AS status
      FROM
        tab_clientes_responsabilidades
        INNER JOIN tab_clientes_responsabilidades_tipos ON (tab_clientes_responsabilidades.cri_id = tab_clientes_responsabilidades_tipos.cri_id)
        LEFT OUTER JOIN tab_clientes_responsabilidades_categorias ON (tab_clientes_responsabilidades.crt_id = tab_clientes_responsabilidades_categorias.crt_id)
        INNER JOIN tab_clientes_responsabilidades_status ON (tab_clientes_responsabilidades.cra_id = tab_clientes_responsabilidades_status.cra_id)
        INNER JOIN tab_usuarios ON (tab_clientes_responsabilidades.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_clientes ON (tab_clientes_responsabilidades.cli_id = tab_clientes.cli_id)
      WHERE
        tab_clientes_responsabilidades.cli_id = $cliente_id
      ORDER BY
        tab_clientes_responsabilidades.cln_inicio,
        tab_clientes_responsabilidades.cln_termino";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["clientes_responsabilidades"]["ver"]) $dados["link"] = "javascript:verClienteResponsabilidade({$dados["id"]});";
      if ($dados["inicio"]) $dados["periodo"][] = $dados["inicio"];
      if ($dados["termino"]) $dados["periodo"][] = $dados["termino"];
      $dados["periodo"] = implode("<br>", $dados["periodo"]);
      $dados["categoria"] = str_replace(" (", "<br>(", $dados["categoria"]);
      $dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
      $responsabilidades[] = $dados;
    }
    return $responsabilidades;
  }

  function geraListagemClientesResponsabilidades($dados) {
    $cabecalho = array ("responsavel" => "Responsável", "periodo" => "Período", "categoria" => "Categoria", "tipo" => "Tipo", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  function buscaClienteResponsavel($cliente_id) {
    $query = "
      SELECT
        usu_nome AS nome
      FROM
        tab_clientes
        INNER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
      WHERE
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

  ###############################
  ### Atualiza Status Cliente ###
  ###############################

  function atualizaStatusCliente($cliente_id) {
    $clientes = controle::instanciaObjeto("clientes", $this->bancodedados, $this->permissoes, false);
    $clientes->atualizaStatusCliente($cliente_id);
  }

}

?>
