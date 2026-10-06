<?

require_once("_global/modulo.class.inc.php");

class clientes_retornos extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
    if ($executar) {
  		$this->buscaDadosModulo($_GET["m"]);
  		$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
  		$this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
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
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
  }

  function buscaUsuarios() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_acoes
        INNER JOIN rel_gru_aco_tempermissao ON (tab_acoes.aco_id = rel_gru_aco_tempermissao.aco_id)
        INNER JOIN rel_usu_gru_fazparte ON (rel_gru_aco_tempermissao.gru_id = rel_usu_gru_fazparte.gru_id)
        INNER JOIN tab_usuarios ON (rel_usu_gru_fazparte.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_acoes.aco_id IN (110, 111, 112) # Clientes Retornos Ver, Editar, Finalizar
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $usuarios[$dados["id"]] = $dados["nome"];
    }
    return $usuarios;
  }

  function buscaStatus($listar = false) {
    $query = "
      SELECT
        ces_id AS id,
        ces_nome AS nome
      FROM
        tab_clientes_retornos_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($listar) {
        $status[] = $dados;
      } else {
        $status[$dados["id"]] = $dados["nome"];
      }
    }
    return $status;
  }

  function buscaPeriodos() {
    $query = "
      SELECT
        cee_id AS id,
        cee_nome AS nome
      FROM
        tab_clientes_retornos_periodos
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $periodos[$dados["id"]] = $dados["nome"];
    }
    return $periodos;
  }

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_clientes_retornos.clr_id AS id,
        DATE_FORMAT(tab_clientes_retornos.clr_abertura, '%d/%m/%Y') AS data_abertura,
        DATE_FORMAT(tab_clientes_retornos.clr_data, '%d/%m/%Y') AS data,
        tab_clientes_retornos.clr_comentario AS comentario,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_usuarios.usu_id AS usuario_id,
        tab_usuarios.usu_nome AS usuario_nome,
        tab_clientes_retornos_tipos.cet_id AS tipo_id,
        tab_clientes_retornos_tipos.cet_nome AS tipo_nome,
        tab_clientes_retornos_status.ces_id AS status_id,
        tab_clientes_retornos_status.ces_nome AS status_nome,
        tab_clientes_retornos_periodos.cee_id AS periodo_id,
        tab_clientes_retornos_periodos.cee_nome AS periodo_nome
      FROM
        tab_clientes_retornos
        INNER JOIN tab_clientes ON (tab_clientes_retornos.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_usuarios ON (tab_clientes_retornos.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_clientes_retornos_tipos ON (tab_clientes_retornos.cet_id = tab_clientes_retornos_tipos.cet_id)
        INNER JOIN tab_clientes_retornos_status ON (tab_clientes_retornos.ces_id = tab_clientes_retornos_status.ces_id)
        LEFT OUTER JOIN tab_clientes_retornos_periodos ON (tab_clientes_retornos.cee_id = tab_clientes_retornos_periodos.cee_id)
      WHERE
        tab_clientes_retornos.clr_id = $id".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["data_periodo"] = $this->dados_item["data"];
      if ($this->dados_item["periodo_nome"]) {
      	$this->dados_item["data_periodo"] .= " ({$this->dados_item["periodo_nome"]})";
      }
    }
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    $this->conteudo = "";
    if ($this->dados_item) {
      $this->conteudo .= '<script type="text/javascript">if (window.opener != null && window.opener.document.getElementById("retornos")) window.opener.buscarClienteRetornos('.$this->dados_item["cliente_id"].');</script>';
    }
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    $dados_cliente = $this->buscaDadosCliente($_GET["cli"]);
    if ($dados_cliente) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_retornos.adicionar.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&cli={$dados_cliente["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[usuario]', $this->geraSelect($this->buscaUsuarios(), $_SESSION["usuario"], false, "usuario", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[cliente]', $dados_cliente["nome"], $this->conteudo);
        $this->conteudo = str_replace('[periodo]', $this->geraSelect($this->buscaPeriodos(), true, null, "periodo", "70px"), $this->conteudo);
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        // Trata Dados
        $_POST["comentario"] = trim($_POST["comentario"]);
        $_POST["data"] = substr($_POST["data"], 6, 4).substr($_POST["data"], 3, 2).substr($_POST["data"], 0, 2);
        $_POST["periodo"] = ($_POST["periodo"]) ? "'{$_POST["periodo"]}'" : "NULL";
        // Finaliza Retornos Antigos
        if ($_POST["usuario"] == $_SESSION["usuario"]) {
          $query = "UPDATE tab_clientes_retornos SET clr_fechamento = NOW(), ces_id = 2 WHERE cli_id = {$_GET["cli"]} AND usu_id = {$_POST["usuario"]}";
          $this->bancodedados->query($query);
        }
        // Insere Retorno
        $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, cee_id, clr_comentario, cet_id, ces_id) VALUES ({$_GET["cli"]}, {$_POST["usuario"]}, NOW(), '{$_POST["data"]}', {$_POST["periodo"]}, '{$_POST["comentario"]}', 1, 1)";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        /*
        // Adiciona Responsabilidade
        if ($_POST["usuario"] == $_SESSION["usuario"] && !$dados_cliente["responsavel_id"] && $this->ehAssumivelResponsabilidade($_SESSION["usuario"])) {
          // Verifica se já possui uma responsabilidade aguardando aprovação
          if (!$this->possuiResponsabilidade($_SESSION["usuario"], $_GET["cli"])) {
            // Insere responsabilidade aguardando aprovação
            $this->insereResponsabilidade($_SESSION["usuario"], $_GET["cli"]);
          }
        }
        */
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      }
    } else {
      $this->fechar();
    }
  }

  function ehResponsavel($usuario_id, $cliente_id) {
    $query = "
      SELECT
        cli_id AS id
      FROM
        tab_clientes
      WHERE
      	cli_id = $cliente_id AND
      	usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultadoArray($query)) {
      return true;
    } else {
      return false;
    }
  }

  function ehAssumivelResponsabilidade($usuario_id) {
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
        tab_modulos.mod_nome = 'clientes_responsabilidades' AND
        tab_acoes.aco_nome = 'assumir_responsabilidade' AND
        tab_usuarios.usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query, 0, "tem_permissao")) {
      return true;
    } else {
      return false;
    }
  }

  function possuiResponsabilidade($usuario_id, $cliente_id) {
    $query = "
      SELECT
        cln_id AS id
      FROM
        tab_clientes_responsabilidades
      WHERE
      	cli_id = $cliente_id AND
      	usu_id = $usuario_id AND
      	cra_id < 4";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultadoArray($query)) {
      return true;
    } else {
      return false;
    }
  }

  function insereResponsabilidade($usuario_id, $cliente_id) {
    // Categoria: Cliente prospect condomínio com antena
    $dados_categoria = $this->buscaDadosResponsabilidadeCategoria(1);
    $data_inicio = date("Y-m-d H:i:s");
    $data_termino = date("Y-m-d H:i:s", strtotime("{$dados_categoria["parametros"]["prazo"]} days"));
    $parametros = @serialize($dados_categoria["parametros"]);
    // Insere Responsabilidade
    $query = "INSERT INTO tab_clientes_responsabilidades (cli_id, cln_inicio, cln_termino, cln_parametros, usu_id, crt_id, cri_id, cra_id) VALUES ($cliente_id, '$data_inicio', '$data_termino', '$parametros', $usuario_id, 1, 2, 1)";
    $this->bancodedados->query($query);
    $id = $this->bancodedados->ultimoIdInserido();
    // Insere Comentário
    $this->registraResponsabilidadeComentario($id, "<b>Ação:</b> Adicionado", $usuario_id);
  }

  function registraResponsabilidadeComentario($responsabilidade_id, $conteudo, $responsavel_id) {
    $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo, usu_id) VALUES (NOW(), $responsabilidade_id, '$conteudo', $responsavel_id)";
    $this->bancodedados->query($query);
  }

  function buscaDadosResponsabilidadeCategoria($categoria_id) {
    $query = "
      SELECT
        crt_id AS id,
        crt_nome AS nome,
        crt_parametros AS parametros
     FROM
  	    tab_clientes_responsabilidades_categorias
     WHERE
        crt_id = $categoria_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      return $dados;
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
  	$this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
    	if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && $_SESSION["usuario"] == $this->dados_item["usuario_id"] && $this->dados_item["status_id"] == 1 && $this->dados_item["tipo_id"] == 1) {
    		$this->editar();
    	} else {
	      $this->conteudo .= $this->carregaArquivo("clientes_retornos.ver.inc.php");
	      $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
	      $this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
	      $this->conteudo = str_replace('[usuario]', $this->dados_item["usuario_nome"], $this->conteudo);
	      $this->conteudo = str_replace('[data_abertura]', $this->dados_item["data_abertura"], $this->conteudo);
	      $this->conteudo = str_replace('[data_periodo]', $this->dados_item["data_periodo"], $this->conteudo);
	      $this->conteudo = str_replace('[comentario]', nl2br($this->dados_item["comentario"]), $this->conteudo);
	      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
	      //if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar&cli='.$this->dados_item["cliente_id"].'\';">';
	      if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar"] && $_SESSION["usuario"] == $this->dados_item["usuario_id"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
	      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    	}
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
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && $_SESSION["usuario"] == $this->dados_item["usuario_id"] && $this->dados_item["status_id"] == 1 && $this->dados_item["tipo_id"] == 1) {
	    	if (!$_POST) {
	        $this->conteudo .= $this->carregaArquivo("clientes_retornos.editar.inc.php");
	        $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
	        $this->conteudo = str_replace('[cliente_nome]', $this->dados_item["cliente_nome"], $this->conteudo);
	        $this->conteudo = str_replace('[usuario]', $this->dados_item["usuario_nome"], $this->conteudo);
	        $this->conteudo = str_replace('[data_abertura]', $this->dados_item["data_abertura"], $this->conteudo);
	        $this->conteudo = str_replace('[data_periodo]', $this->dados_item["data_periodo"], $this->conteudo);
	        $this->conteudo = str_replace('[comentario]', $this->dados_item["comentario"], $this->conteudo);
	        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
	        //if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar&cli='.$this->dados_item["cliente_id"].'\';">';
	        if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar"] && $_SESSION["usuario"] == $this->dados_item["usuario_id"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\';">';
	        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
	        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
	        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
	      } else {
	        $_POST["comentario"] = trim($_POST["comentario"]);
	        $query = "UPDATE tab_clientes_retornos SET clr_comentario = '{$_POST["comentario"]}' WHERE clr_id = {$_GET["i"]}";
	        $this->bancodedados->query($query);
	        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
	        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
	        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
	      }
      } else {
      	$this->ver();
      }
    } else {
      $this->fechar();
    }
  }

  #################
  ### Finalizar ###
  #################

  function finalizar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $_SESSION["usuario"] == $this->dados_item["usuario_id"] && $this->dados_item["status_id"] == 1) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("clientes_retornos.finalizar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_clientes_retornos SET ces_id = 2 WHERE clr_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        if ($this->ehAxuliaresFinanceiros($this->dados_item["usuario_id"])) {
          $query = "UPDATE tab_clientes_retornos SET ces_id = 2 WHERE cli_id = {$this->dados_item["cliente_id"]} AND ces_id = 1 AND clr_comentario = '{$this->dados_item["comentario"]}'";
          $this->bancodedados->query($query);
        }
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  function ehAxuliaresFinanceiros($usuario_id) {
    $query = "
      SELECT
        COUNT(*) AS usuarios
      FROM
        rel_usu_gru_fazparte
      WHERE
        usu_id = $usuario_id AND
        gru_id = 9";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["usuarios"];
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    if (!$_GET["f"] && !$_GET["p"]) {
      $_GET["f"]["data_inicio"] = date("d/m/Y", strtotime("-1 day"));
      $_GET["f"]["data_termino"] = date("d/m/Y", strtotime("+1 day"));
      $_GET["f"]["responsavel"] = $_SESSION["usuario"];
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
    // Cliente
    $itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
    // Condomínio
    $condominios = $this->buscaCondominios();
    foreach ($condominios as $id => $nome) {
      $select_condominios[] = array ("id" => $id, "nome" => $nome);
    }
    if (!$valores["condominio"]) $condominio_complemento = " OR tab_clientes.con_id IS NULL";
    $itens[] = array ("tipo" => "select", "nome" => "condominio", "descricao" => "Condomínio", "valor" => $select_condominios, "where" => "(tab_clientes.con_id LIKE '[valor]'$condominio_complemento)");
    // Responsável
    $responsaveis = $this->buscaResponsaveis();
    foreach ($responsaveis as $id => $nome) {
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "responsavel", "descricao" => "Responsável", "valor" => $select_responsaveis, "where" => "(tab_clientes_retornos.usu_id = [valor])", "disabled" => ($this->permissoes[$this->dados_modulo["nome"]]["listar_todos"])? false : true);
    if (!$this->permissoes[$this->dados_modulo["nome"]]["listar_todos"]) $valores["responsavel"] = $_SESSION["usuario"];
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio", "descricao" => "Data &raquo; Início", "valor" => "", "where" => "(tab_clientes_retornos.clr_data".(($valores["data_inicio"]) ? " >= '".substr($valores["data_inicio"],6,4).substr($valores["data_inicio"],3,2).substr($valores["data_inicio"],0,2)."'" : " IS NOT NULL").")");
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_termino", "descricao" => "Data &raquo; Término", "valor" => "", "where" => "(tab_clientes_retornos.clr_data".(($valores["data_termino"]) ? " <= '".substr($valores["data_termino"],6,4).substr($valores["data_termino"],3,2).substr($valores["data_termino"],0,2)."'" : " IS NOT NULL").")");
    // Status
    $status = $this->buscaStatus(true);
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $status, "where" => "(tab_clientes_retornos.ces_id = [valor])");

    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

    return $filtro;
  }

  function buscaCondominios() {
    $query = "
      SELECT
        con_id AS id,
        CONCAT(tab_condominios.con_nome,' (',tab_cidades.cid_nome,'/',tab_bairros.bai_nome,')') AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_ceps.cep_id = tab_condominios.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      WHERE
        cos_id IS NOT NULL
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }

  function buscaResponsaveis() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios,
        rel_usu_gru_fazparte,
        tab_grupos,
        rel_gru_aco_tempermissao,
        tab_acoes
      WHERE
        tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id AND
        rel_usu_gru_fazparte.gru_id = tab_grupos.gru_id AND
        tab_grupos.gru_id = rel_gru_aco_tempermissao.gru_id AND
        rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id AND
        tab_acoes.aco_id IN (19, 109)".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
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
        DATE_FORMAT(tab_clientes_retornos.clr_data,'%d/%m/%Y') AS data,
        tab_clientes_retornos.cee_id AS periodo,
        IF(tab_clientes.con_id IS NULL, 'Sem Condomínio', tab_condominios.con_nome) AS condominio,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_clientes.cls_id AS cliente_status,
        tab_clientes_retornos.clr_comentario AS comentario,
        tab_usuarios.usu_nome AS responsavel
      FROM
        tab_clientes_retornos
        INNER JOIN tab_clientes ON (tab_clientes_retornos.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        INNER JOIN tab_usuarios ON (tab_clientes_retornos.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_clientes_retornos.ces_id = '1' AND
        $filtro->parametros_sql".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        tab_clientes_retornos.clr_data DESC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $periodos = $this->buscaPeriodos();
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["clientes"]["ver_dm"]) {
        $dados["link"] = "?m=clientes&a=ver_dm&i={$dados["cliente_id"]}";
      }
      if ($dados["periodo"]) {
        $dados["data"] .= " ({$periodos[$dados["periodo"]]})";
      }
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "data" => "Data<br>(Período)", "condominio" => "Condomínio", "cliente_nome" => "Cliente", "comentario" => "Comentário", "responsavel" => "Responsável");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right"));
    return $listagem;
  }

  ###############################
  ### Busca Retornos Clientes ###
  ###############################

  function buscaClientesRetornos($cliente_id) {
    $query = "
      SELECT
        tab_clientes_retornos.clr_id AS id,
        DATE_FORMAT(tab_clientes_retornos.clr_data, '%d/%m/%Y') AS data,
        tab_clientes_retornos_periodos.cee_nome AS periodo,
        tab_clientes_retornos.clr_comentario AS comentario,
        tab_usuarios.usu_nome AS usuario,
        tab_clientes_retornos_status.ces_nome AS status
      FROM
        tab_clientes_retornos
        INNER JOIN tab_usuarios ON (tab_clientes_retornos.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_clientes_retornos_tipos ON (tab_clientes_retornos.cet_id = tab_clientes_retornos_tipos.cet_id)
        INNER JOIN tab_clientes_retornos_status ON (tab_clientes_retornos.ces_id = tab_clientes_retornos_status.ces_id)
        LEFT OUTER JOIN tab_clientes_retornos_periodos ON (tab_clientes_retornos.cee_id = tab_clientes_retornos_periodos.cee_id)
      WHERE
        tab_clientes_retornos.cli_id = $cliente_id
      ORDER BY
        tab_clientes_retornos.clr_data DESC,
        tab_clientes_retornos_periodos.cee_nome DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["clientes_retornos"]["ver"]) $dados["link"] = "javascript:verClienteRetorno({$dados["id"]});";
    	if ($dados["periodo"]) $dados["data"] .= " (".$dados["periodo"].")";
      $dados["comentario"] = preg_replace("/[\r\n\t ]+/", " ", $dados["comentario"]);
      $dados["comentario"] = strlen($dados["comentario"]) > 70 ? substr($dados["comentario"], 0, 67)."..." : $dados["comentario"];
      $dados["comentario"] = "<div nowrap>".$dados["comentario"]."</div>";
      $retornos[] = $dados;
    }
    return $retornos;
  }

  function geraListagemClientesRetornos($dados) {
    $cabecalho = array ("data" => "Data / Período", "comentario" => "Comentário", "usuario" => "Usuário", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

}

?>
