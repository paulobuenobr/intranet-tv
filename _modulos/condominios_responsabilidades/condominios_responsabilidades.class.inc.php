<?

require_once("_global/modulo.class.inc.php");

class condominios_responsabilidades extends modulo {

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
        tab_condominios_responsabilidades.cod_id AS id,
        DATE_FORMAT(tab_condominios_responsabilidades.cod_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_condominios_responsabilidades.cod_termino,'%d/%m/%Y (%H:%i)') AS termino,
        tab_condominios_responsabilidades.cod_inicio AS data_hora_inicio,
        tab_condominios_responsabilidades.cod_termino AS data_hora_termino,
        tab_condominios.con_id AS condominio_id,
        tab_condominios.con_nome AS condominio_nome,
        tab_usuarios.usu_id AS responsavel_id,
        tab_usuarios.usu_nome AS responsavel_nome,
        tab_condominios_responsabilidades_categorias.crg_id AS categoria_id,
        tab_condominios_responsabilidades_categorias.crg_nome AS categoria_nome,
        tab_condominios_responsabilidades_tipos.crp_id AS tipo_id,
        tab_condominios_responsabilidades_tipos.crp_nome AS tipo_nome,
        tab_condominios_responsabilidades_status.cru_id AS status_id,
        tab_condominios_responsabilidades_status.cru_nome AS status_nome
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_usuarios ON (tab_condominios_responsabilidades.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_condominios ON (tab_condominios_responsabilidades.con_id = tab_condominios.con_id)
        INNER JOIN tab_condominios_responsabilidades_categorias ON (tab_condominios_responsabilidades.crg_id = tab_condominios_responsabilidades_categorias.crg_id)
        INNER JOIN tab_condominios_responsabilidades_tipos ON (tab_condominios_responsabilidades.crp_id = tab_condominios_responsabilidades_tipos.crp_id)
        INNER JOIN tab_condominios_responsabilidades_status ON (tab_condominios_responsabilidades.cru_id = tab_condominios_responsabilidades_status.cru_id)
      WHERE
        cod_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if($dados) {
      $dados["atividades"] = $this->buscaResponsabilidadesAtividades($dados["id"], true);
      $this->dados_item = $dados;
      $this->dados_item["comentarios"] = $this->buscaComentarios($this->dados_item["id"]);
    } else {
      unset($this->dados_item);
    }
  }

  function buscaResponsabilidadesAtividades($responsabilidade_id, $id = false) {
    $query = "
      SELECT
        tab_condominios_responsabilidades_atividades.cov_id AS id,
        tab_condominios_responsabilidades_atividades.cov_nome AS nome
      FROM
        tab_condominios_responsabilidades_atividades
        INNER JOIN rel_cov_cod_estacontido ON (tab_condominios_responsabilidades_atividades.cov_id = rel_cov_cod_estacontido.cov_id)
      WHERE
        rel_cov_cod_estacontido.cod_id = $responsabilidade_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $atividades[] = $id ? $dados["id"] : $dados["nome"];
    }
    return $atividades;
  }
  
  function buscaClientesComResponsabilidade($dados) {
    $query = "
      SELECT
        cli_id AS cliente_id
      FROM
        tab_clientes
      WHERE
        con_id = {$dados["condominio"]} AND
        usu_id = {$dados["usuario"]}";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$clientes[] = $dados["cliente_id"];
    }
    return $clientes;
  }

  function buscaClientesSemResponsabilidade($dados) {
    $query = "
      SELECT DISTINCT
        tab_clientes.cli_id AS cliente
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_clientes_responsabilidades ON (tab_clientes.cli_id = tab_clientes_responsabilidades.cli_id)
      WHERE
        tab_clientes.con_id = '{$dados["condominio"]}' AND
        tab_clientes_responsabilidades.cra_id IN (2,4) AND
        tab_clientes_responsabilidades.cri_id = '{$dados["tipo"]}' AND
        (('{$dados["inicio"]}' BETWEEN cln_inicio AND cln_termino) OR
         ('{$dados["termino"]}' BETWEEN cln_inicio AND cln_termino) OR
         (cln_inicio BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
         (cln_termino BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}'))";
    $query = $this->bancodedados->query($query);
    $query = $this->bancodedados->resultadoArrayTotal($query);

    for ($i = 0; $i < sizeof($query); $i++) {
      $com_responsabilidade[] = $query[$i]["cliente"];
    }
    $com_responsabilidade = @implode(", ", $com_responsabilidade);
    if ($com_responsabilidade) $where = "AND tab_clientes.cli_id NOT IN ($com_responsabilidade)";

    $query = "
      SELECT
        cli_id AS cliente
      FROM
        tab_clientes
      WHERE
        con_id = '{$dados["condominio"]}'
        $where";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaDadosCondominio($id) {
    $query = "SELECT con_id AS id, con_nome AS nome FROM tab_condominios WHERE con_id = '$id'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      return $this->bancodedados->resultadoArray($query);
    }
  }

  function buscaDadosResponsabilidade($id) {
    $query = "
      SELECT
        con_id AS condominio,
        crp_id AS tipo,
        cod_inicio AS inicio,
        cod_termino AS termino
	    FROM
	      tab_condominios_responsabilidades
	    WHERE
	      cod_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaTipos() {
    $query = "
      SELECT
        crp_id AS id,
        crp_nome AS nome
	    FROM
	      tab_condominios_responsabilidades_tipos
	    ORDER BY
	      crp_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaStatus() {
    $query = "
      SELECT
        cru_id AS id,
        cru_nome AS nome
	    FROM
	      tab_condominios_responsabilidades_status
	    ORDER BY
	      cru_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaStatusCondominio() {
    $query = "
      SELECT
        cos_id AS id,
        cos_nome AS nome
     FROM
        tab_condominios_status
     ORDER BY
        cos_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaCategorias($disponiveis = false, $categoria_atual = null) {
    $query = "
      SELECT
        crg_id AS id,
        crg_nome AS nome
     FROM
        tab_condominios_responsabilidades_categorias".($disponiveis ? "
     WHERE
        crg_disponivel = 1".($categoria_atual ? " OR
        crg_id = $categoria_atual" : "") : "")."
     ORDER BY
        crg_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categorias[$dados["id"]] = $dados["nome"];
    }
    return $categorias;
  }

  function buscaCategoriaParametros($id) {
    $query = "
      SELECT
        crg_parametros AS parametros
     FROM
        tab_condominios_responsabilidades_categorias
     WHERE
        crg_id = $id";
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
		        DATE_ADD(cod_inicio, INTERVAL {$parametros["prazo"]} DAY) AS termino
		      FROM
		        tab_condominios_responsabilidades
		      WHERE
		        cod_id = $responsabilidade_id";
		    $query = $this->bancodedados->query($query);
		    $dados["termino"] = $this->bancodedados->resultado($query);
	    	return $dados;
	    }
    }
  }

  function buscaDadosPeriodo($id) {
    $query = "
      SELECT
        con_id AS condominio,
        IF((NOW() BETWEEN cod_inicio AND cod_termino) AND (crp_id = 2), 1, 0) AS atual
     FROM
        tab_condominios_responsabilidades
     WHERE
        cod_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosPeriodoCliente($id) {
    $query = "
      SELECT
        cli_id AS cliente,
        IF((NOW() BETWEEN cln_inicio AND cln_termino) AND (cri_id = 2), 1, 0) AS atual
     FROM
        tab_clientes_responsabilidades
     WHERE
        cln_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaResponsaveis () {
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
        ((tab_acoes.aco_nome = 'assumir_responsabilidade' AND
         tab_modulos.mod_nome = 'condominios_responsabilidades') OR
        (tab_acoes.aco_nome = 'assumir_subcomissionamento' AND
         tab_modulos.mod_nome = 'condominios_responsabilidades') OR
        (tab_usuarios.usu_id = {$_SESSION["usuario"]}))".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }

  function temPermissao($usuario, $modulo, $acao) {
    $query = "
      SELECT
        COUNT(rel_gru_aco_tempermissao.aco_id) AS temPermissao
      FROM
        tab_usuarios
        INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
        INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
        INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
        INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
      WHERE
        tab_modulos.mod_nome = '$modulo' AND
        tab_acoes.aco_nome = '$acao' AND
        tab_usuarios.usu_id = '$usuario'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query, 0, "temPermissao")) {
    	return 2;
    }
    return 1;
  }
  
  function buscaAtividades() {
    $query = "
      SELECT
        cov_id AS id,
        cov_nome AS nome
      FROM 
        tab_condominios_responsabilidades_atividades
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $atividades[$dados["id"]] = $dados["nome"];
    }
    return $atividades;
  }

  ###################
  ### Comentários ###
  ###################

  function buscaComentarios($id) {
    $query = "
      SELECT
        IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
        DATE_FORMAT(tab_condominios_responsabilidades_comentarios.crm_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_condominios_responsabilidades_comentarios.crm_conteudo AS conteudo
      FROM
        tab_condominios_responsabilidades_comentarios
        LEFT OUTER JOIN tab_usuarios ON (tab_condominios_responsabilidades_comentarios.usu_id = tab_usuarios.usu_id)
      WHERE
        cod_id = $id
      ORDER BY
        tab_condominios_responsabilidades_comentarios.crm_datahora ASC,
        tab_condominios_responsabilidades_comentarios.crm_id ASC";
    $query = $this->bancodedados->query($query);
    while(($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["detalhamento"] = "<b>{$dados["responsavel"]}</b> em ";
      $dados["detalhamento"] .= "<b>{$dados["data_hora"]}</b><br>";
      $dados["detalhamento"] .= substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}<br>";
      $comentarios[] = $dados;
    }
    return $comentarios;
  }

  function registraComentario ($id, $conteudo, $responsavel) {
    $query = "INSERT INTO tab_condominios_responsabilidades_comentarios (crm_datahora, cod_id, crm_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  function geraComentariosListagem ($comentarios) {
    $cabecalho = array("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  function registraComentarioCliente ($id, $conteudo, $responsavel) {
    $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo, usu_id) VALUES (NOW(), '$id', '$conteudo', '$responsavel')";
    $this->bancodedados->query($query);
  }

  #############
  ### Geral ###
  #############

  function geraCheckList($nome, $dados, $selecionados = null, $checked = false, $disabled = false, $colunas = 2) {
    $i = 1;
    $item = 1;
    $itens = ceil(count($dados) / $colunas);
    $selecionados = $selecionados ? $selecionados : array();
    $checkboxes[] = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
    $checkboxes[] = "<tr>";
    foreach ($dados as $id => $valor) {
      if ($item == 1) {
        $checkboxes[] = "<td valign=\"top\">";
      }
      $checkboxes[] = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\"><td width=\"25\"><input type=\"checkbox\" name=\"{$nome}[]\" id=\"{$nome}_{$i}\" value=\"$id\"".((in_array($id, $selecionados) || $checked) ? " checked" : "")." style=\"width:14px;height:14px\"".($disabled ? " disabled" : "")."></td><td><label for=\"{$nome}_{$i}\">$valor</label></td></tr></table>";
      $i++;
      $item++;
      if ($item > $itens) {
        $checkboxes[] = "</td>";
        $item = 1;
      }
    }
    $checkboxes[] = "</tr>";
    $checkboxes[] = "</table>";
    return @implode("", $checkboxes);
  }

  function fechar() {
    unset($this->conteudo);
    if ($this->dados_item)
    $this->conteudo .= '<script type="text/javascript">if (window.opener != null && window.opener.document.getElementById("responsabilidades")) window.opener.buscarCondominioResponsabilidades(' . $this->dados_item["condominio_id"] . ');</script>';
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  function formataDatas($inicio, $prazo) {
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
    $dados_condominio = $this->buscaDadosCondominio($_GET["i"]);
    if ($dados_condominio) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.adicionar.inc.php");
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&i={$dados_condominio["id"]}", $this->conteudo);
        $this->conteudo = str_replace('[nome]', $dados_condominio["nome"], $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $_SESSION["usuario"], true, "responsavel", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(true), null, true, "categoria", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos()), $this->conteudo);
        $this->conteudo = str_replace('[atividades]', $this->geraCheckList("atividade", $this->buscaAtividades()), $this->conteudo);
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        // Trata Dados
        $tipo = $this->temPermissao($_POST["responsavel"], $this->dados_modulo["nome"], "assumir_responsabilidade") == 2 ? 2 : 1;
        $parametro = $this->buscaCategoriaParametros($_POST["categoria"]);
        $parametros = unserialize($parametro);
        $datas = $this->formataDatas($_POST["inicio"], $parametros["prazo"]);
        // Insere Registro
        $query = "INSERT INTO tab_condominios_responsabilidades (con_id, cod_inicio, cod_termino, cod_parametros, usu_id, crg_id, crp_id, cru_id) VALUES ('{$_GET["i"]}', '{$datas["inicio"]}', '{$datas["termino"]}', '$parametro', '{$_POST["responsavel"]}', '{$_POST["categoria"]}', '$tipo', '1')";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Registra Atividades
        foreach ($_POST["atividade"] as $atividade_id) {
          $query = "INSERT INTO rel_cov_cod_estacontido (cov_id, cod_id) VALUES ($atividade_id, $id)";
          $this->bancodedados->query($query);
        }
        // Registra Comentário
        if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
        $this->registraComentario($id, "<b>Ação:</b> Adicionado".$comentario, $_SESSION["usuario"]);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
        // Exibe Registro
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      }
    } else {
      $this->fechar();
    }
  }

  ########################
  ### Ver Detalhamento ###
  ########################

  function ver_dd() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.ver_dd.inc.php");
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Gerais", "ver_dd" => "Detalhamento")), $this->conteudo);
      $this->conteudo = str_replace('[condominio_id]',$this->dados_item["condominio_id"],$this->conteudo);
      $this->conteudo = str_replace('[condominio_nome]',$this->dados_item["condominio_nome"],$this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $listagem = $this->geraFilhosListagem($this->buscaClientes($this->dados_item["id"]));
      $this->conteudo = str_replace('[listagem]', $listagem->conteudo, $this->conteudo);
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,'id='.$this->dados_item["id"]);
    } else {
      $this->fechar();
    }
  }

  function buscaClientes($id) {
    $query = "
      SELECT DISTINCT
        tab_clientes.cli_id AS id,
        cli_data_cadastro AS data,
        cli_nome AS nome,
        cls_nome AS status,
        cod_inicio AS inicio,
        cod_termino AS termino,
        tab_clientes_responsabilidades.usu_id AS usuario,
        DATE_FORMAT(cli_data_cadastro, '(%d/%m/%Y)') AS prospeccao
      FROM
        tab_clientes_status
        INNER JOIN tab_clientes ON (tab_clientes_status.cls_id = tab_clientes.cls_id)
        INNER JOIN tab_clientes_responsabilidades ON (tab_clientes.cli_id = tab_clientes_responsabilidades.cli_id)
        INNER JOIN tab_condominios_responsabilidades ON (tab_clientes_responsabilidades.cod_id = tab_condominios_responsabilidades.cod_id)
      WHERE
        tab_clientes_responsabilidades.cod_id = $id
      ORDER BY
        tab_clientes.cls_id, tab_clientes.cli_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["clientes"]["ver_dm"]) {
      	$dados["link"] = "javascript:window.open('?m=clientes&a=ver_dm&i={$dados["id"]}', 'cliente')";
      }
      $dados["retornos"] = $this->buscaQuantidadeRetornos($dados);
      $dados["assinaturas"] = $this->buscaQuantidadeAssinaturas($dados);
      $clientes[] = $dados;
    }
    return $clientes;
  }

  function buscaQuantidadeRetornos($dados) {
    $query = "
      SELECT
        COUNT(*) AS quantidade
      FROM
        tab_clientes_retornos
      WHERE
        cli_id = {$dados["id"]} AND
        cet_id = 1 AND
        usu_id = {$dados["usuario"]} AND
        clr_abertura BETWEEN '{$dados["inicio"]}' AND DATE_ADD('{$dados["termino"]}', INTERVAL 90 DAY)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "quantidade");
  }

  function buscaQuantidadeAssinaturas($dados) {
    $query = "
      SELECT
        COUNT(*) AS quantidade
      FROM
        tab_assinaturas
      WHERE
        cli_id = {$dados["id"]} AND
        ass_data_cadastro BETWEEN '{$dados["inicio"]}' AND DATE_ADD('{$dados["termino"]}', INTERVAL 90 DAY)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "quantidade");
  }

  function geraFilhosListagem($itens) {
    $cabecalho = array("nome" => "Nome", "prospeccao" => "Prospecção", "retornos" => "Retornos", "assinaturas" => "Assinaturas", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if(!empty($this->dados_item)) {
      $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.ver.inc.php");
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Gerais", "ver_dd" => "Detalhamento")), $this->conteudo);
      $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
      $this->conteudo = str_replace('[condominio_nome]', $this->dados_item["condominio_nome"], $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[categoria]', $this->dados_item["categoria_nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace('[comentarios]',$this->geraComentariosListagem($this->dados_item["comentarios"]), $this->conteudo);
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
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
      	$editavel = $this->ehEditavel() || $this->ehEditavelPlus();
        $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.editar.inc.php");
      	$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Dados Gerais", "ver_dd" => "Detalhamento")), $this->conteudo);
        $this->conteudo = str_replace('[condominio_id]', $this->dados_item["condominio_id"], $this->conteudo);
        $this->conteudo = str_replace('[condominio_nome]', $this->dados_item["condominio_nome"], $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $this->dados_item["responsavel_id"], true, "responsavel", "100%", null, $editavel), $this->conteudo);
        $this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaCategorias(true, $this->dados_item["categoria_id"]), $this->dados_item["categoria_id"], true, "categoria", "100%", null, $editavel), $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true, "tipo", "100%", null, $editavel), $this->conteudo);
        $this->conteudo = str_replace('[comentarios]',$this->geraComentariosListagem($this->dados_item["comentarios"]),$this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
        $this->conteudo = str_replace('[termino]', $this->geraInput("text", "termino", "120px", $this->dados_item["termino"], $this->ehProrrogavel(), "8", null, "carregaTarefaCalendarioNavegacao(this.name, 1, this.value)"), $this->conteudo);
        $this->conteudo = str_replace('[atividades]', $this->geraCheckList("atividade", $this->buscaAtividades(), $this->dados_item["atividades"], false, $this->dados_item["status_id"] > 4), $this->conteudo);
        if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="aprovar" class="buttonAzulEscuroBranco11" value="Aprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=aprovar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="reprovar" class="buttonAzulEscuroBranco11" value="Reprovar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=reprovar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=finalizar&i=' . $this->dados_item["id"] . '\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        if ($this->ehRemovivel() || $this->ehRemovivelPlus()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=remover&i=' . $this->dados_item["id"] . '\';">';

        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        if ($this->dados_item["status_id"] == 3) {
          $status = 1;
        } else {
          $status = $this->dados_item["status_id"];
        }

        if ($this->ehEditavel() || $this->ehEditavelPlus()) {
          if ($this->dados_item["tipo_id"] != $_POST["tipo"] || $this->dados_item["responsavel_id"] != $_POST["responsavel"]) {
            if ($this->temPermissao($_POST["responsavel"], $this->dados_modulo["nome"], "assumir_responsabilidade") == $_POST["tipo"]) {
              $query = "UPDATE tab_condominios_responsabilidades SET crp_id = {$_POST["tipo"]}, usu_id = {$_POST["responsavel"]}, cru_id = '$status' WHERE cod_id = {$_GET["i"]}";
              $this->bancodedados->query($query);
              $alterado = true;
            }
          }
          if ($this->dados_item["categoria_id"] != $_POST["categoria"]) {
            $dados = $this->buscaDadosPrazo($_GET["i"], $_POST["categoria"]);
            if (!$this->existeAtivadoAprovadoNoTermino($_GET["i"], $dados["termino"], $_POST["tipo"])) {
              $query = "UPDATE tab_condominios_responsabilidades SET crg_id = {$_POST["categoria"]}, cru_id = '$status', cod_parametros = '{$dados["parametros"]}', cod_termino = '{$dados["termino"]}' WHERE cod_id = {$_GET["i"]}";
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
              $query = "UPDATE tab_condominios_responsabilidades SET cod_termino = {$_POST["termino"]}, cru_id = '$status' WHERE cod_id = {$_GET["i"]}";
              $this->bancodedados->query($query);
              $alterado = true;
            }
          }
        }

        if ($alterado && in_array($status, array(2,4))) {
          $this->alteraResponsabilidadesFilhas($_GET["i"]);
        }

        if ($alterado || $_POST["comentario"]) {
          if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
          $this->registraComentario($_GET["i"], "<b>Ação:</b> Editado".$comentario, $_SESSION["usuario"]);
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        }
        // Registra Atividades
        if ($this->dados_item["status_id"] <= 4) {
          $query = "DELETE FROM rel_cov_cod_estacontido WHERE cod_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          foreach ($_POST["atividade"] as $atividade_id) {
            $query = "INSERT INTO rel_cov_cod_estacontido (cov_id, cod_id) VALUES ($atividade_id, {$_GET["i"]})";
            $this->bancodedados->query($query);
          }
        }
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  function alteraResponsabilidadesFilhas($id) {
    $query = "SELECT cod_inicio AS inicio, cod_termino AS termino, cod_parametros AS parametros, usu_id AS usuario, crp_id AS tipo FROM tab_condominios_responsabilidades WHERE cod_id = $id";
    $dados = $this->bancodedados->resultadoArray($this->bancodedados->query($query));

    $query = "SELECT cli_id AS id FROM tab_clientes_responsabilidades WHERE cod_id = $id";
    $clientes = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));

    for ($i = 0; $i < sizeof($clientes); $i++) {
      $query = "
        SELECT
          COUNT(cln_id) AS quantidade
        FROM
          tab_clientes_responsabilidades
        WHERE
          cli_id = {$clientes[$i]["id"]} AND
          cra_id IN (2, 4) AND
          cri_id = {$dados["tipo"]} AND
          (cod_id != $id OR cod_id IS NULL) AND
          (('{$dados["termino"]}' BETWEEN cln_inicio AND cln_termino) OR
           (cln_inicio BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
           (cln_termino BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}'))";
      $query = $this->bancodedados->query($query);
      if (!$this->bancodedados->resultado($query, 0, "quantidade")) {
        $query = "UPDATE tab_clientes_responsabilidades SET cln_termino = '{$dados["termino"]}', cln_parametros = '{$dados["parametros"]}', usu_id = '{$dados["usuario"]}', cri_id = '{$dados["tipo"]}' WHERE cod_id = '$id' AND cli_id = '{$clientes[$i]["id"]}'";
        $this->bancodedados->query($query);
      }
    }
  }

  ###############
  ### Aprovar ###
  ###############

  function aprovar() {
	  
	  if (in_array($this->dados_item["responsavel_id"], array(191, 192))) {
		  $limite = COMERCIAL_LIMITE_RESPONSABILIDADE_CONDOMINIO;
	  } else {
		  $limite = 1000;
	  }
	  
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehAprovavel()) {
      $quantidade_responsabilidades = $this->buscaQuantidadeResponsabilidades($this->dados_item["responsavel_id"], $this->dados_item["data_hora_inicio"], $this->dados_item["data_hora_termino"]);
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.aprovar.inc.php");
        $this->conteudo = str_replace('[conflito]', $this->existeAtivadoAprovado($_GET["i"]) ? '1' : '0', $this->conteudo);
        $this->conteudo = str_replace('[limite]',  $limite && $quantidade_responsabilidades >= $limite ? '1' : '0', $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=aprovar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $this->finalizaConflitantes($_GET["i"]);
        if ($limite && $quantidade_responsabilidades >= $limite) {
        	$responsabilidades = $this->buscaResponsabilidadesAntigas($this->dados_item["responsavel_id"], $this->dados_item["data_hora_inicio"], $this->dados_item["data_hora_termino"], $quantidade_responsabilidades - $limite + 1);
          if ($responsabilidades) {
            foreach ($responsabilidades as $responsabilidade) {
              // Finaliza Responsabilidade Condomínio
              $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 5, cod_termino = NOW() WHERE cod_id = {$responsabilidade["id"]}";
              $this->bancodedados->query($query);
              // Registra Comentário Responsabilidade Condomínio
              $this->registraComentario($responsabilidade["id"], "<b>Ação:</b> Finalizado", $_SESSION["usuario"]);
              // Limpa Responsabilidade Condomínio
              $query = "UPDATE tab_condominios SET usu_id = NULL WHERE con_id = {$responsabilidade["condominio_id"]}";
              $this->bancodedados->query($query);
              // Finaliza Responsabilidade Clientes
              $this->finalizarClientes($responsabilidade["id"]);
            }
          }
        }
        $query = "
          SELECT
            con_id AS condominio,
            cod_parametros AS parametros,
            usu_id AS usuario,
            cod_inicio AS inicio,
            cod_termino AS termino,
            crp_id AS tipo,
            IF((NOW() BETWEEN cod_inicio AND cod_termino) AND (crp_id = 2), 1, 0) AS atual
          FROM
            tab_condominios_responsabilidades
          WHERE
            cod_id = {$_GET["i"]}";
        $query = $this->bancodedados->query($query);
        $dados = $this->bancodedados->resultadoArray($query);
        if ($dados["atual"]) {
          $query = "UPDATE tab_condominios SET usu_id = {$dados["usuario"]} WHERE con_id = {$dados["condominio"]}";
          $this->bancodedados->query($query);
          $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 4, cod_inicio = NOW() WHERE cod_id = '{$_GET["i"]}'";
          $this->bancodedados->query($query);
          if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
          $this->registraComentario($_GET["i"], "<b>Ação:</b> Aprovado com Auto-Ativação".$comentario, $_SESSION["usuario"]);
	        // Atualiza Responsabilidades de Clientes q Já possuem Responsabilidade
	        $clientes = $this->buscaClientesComResponsabilidade($dados);
	        if ($clientes) {
	      		$query = "UPDATE tab_clientes_responsabilidades SET cln_termino = '{$dados["termino"]}' WHERE cli_id IN (".implode(", ", $clientes).") AND cln_termino < '{$dados["termino"]}' AND cri_id = 2 AND cra_id = 4";
	        	$this->bancodedados->query($query);
	        }
        } else {
          $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 2 WHERE cod_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
          $this->registraComentario($_GET["i"], "<b>Ação:</b> Aprovado".$comentario, $_SESSION["usuario"]);
        }
        // Insere Responsabilidade nos Clientes
        $clientes = $this->buscaClientesSemResponsabilidade($dados);
        if ($clientes) {
          for ($i = 0; $i < sizeof($clientes); $i++) {
            if ($dados["atual"]) {
              $query = "INSERT INTO tab_clientes_responsabilidades (cli_id, cra_id, cln_inicio, cln_termino, cri_id, usu_id, cod_id, cln_parametros) VALUES ({$clientes[$i]["cliente"]}, 4, NOW(), '{$dados["termino"]}', '{$dados["tipo"]}', {$dados["usuario"]}, {$_GET["i"]}, '{$dados["parametros"]}')";
              $this->bancodedados->query($query);
              $id = $this->bancodedados->ultimoIdInserido();
              $this->registraComentarioCliente($id, "<b>Ação:</b> Ativado (Herdado do Condomínio)", $dados["usuario"]);
              $query = "UPDATE tab_clientes SET usu_id = {$dados["usuario"]} WHERE cli_id = '{$clientes[$i]["cliente"]}'";
              $this->bancodedados->query($query);
            } else {
              $query = "INSERT INTO tab_clientes_responsabilidades (cli_id, cra_id, cln_inicio, cln_termino, cri_id, usu_id, cod_id, cln_parametros) VALUES ({$clientes[$i]["cliente"]}, 2, '{$dados["inicio"]}', '{$dados["termino"]}', '{$dados["tipo"]}', {$dados["usuario"]}, {$_GET["i"]}, '{$dados["parametros"]}')";
              $this->bancodedados->query($query);
              $id = $this->bancodedados->ultimoIdInserido();
              $this->registraComentarioCliente($id, "<b>Ação:</b> Aprovado (Herdado do Condomínio)", $dados["usuario"]);
            }
          }
        }
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }
  
  function buscaResponsabilidadesAntigas($usuario_id, $inicio, $termino, $quantidade) {
    $query = "
      SELECT
        tab_condominios_responsabilidades.cod_id AS id,
        tab_condominios_responsabilidades.con_id AS condominio_id
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_condominios ON (tab_condominios_responsabilidades.con_id = tab_condominios.con_id)
      WHERE
        tab_condominios.cos_id = 2 AND
        tab_condominios_responsabilidades.cru_id IN (2, 4) AND
        tab_condominios_responsabilidades.usu_id = $usuario_id AND
        ((tab_condominios_responsabilidades.cod_inicio BETWEEN '$inicio' AND '$termino') OR
         (tab_condominios_responsabilidades.cod_termino BETWEEN '$inicio' AND '$termino'))
      ORDER BY
        tab_condominios_responsabilidades.cod_inicio
      LIMIT 
        0, $quantidade";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsabilidades[] = $dados;
    }
    return $responsabilidades;
  }

  function finalizaConflitantes($id) {
    $dados = $this->buscaDadosResponsabilidade($id);
    $query = "
      SELECT
        cod_id AS id,
        cru_id AS status
      FROM
        tab_condominios_responsabilidades
      WHERE
        (con_id = {$dados["condominio"]}) AND
        (cru_id IN (2, 4)) AND
        (crp_id = {$dados["tipo"]}) AND
        ((cod_inicio BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
         (cod_termino BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
         ('{$dados["inicio"]}' BETWEEN cod_inicio AND cod_termino) OR
         ('{$dados["termino"]}' BETWEEN cod_inicio AND cod_termino))";
    $queryConflitos = $this->bancodedados->query($query);
    while (($conflitante = $this->bancodedados->resultadoArray($queryConflitos)) != false) {
      if ($conflitante["status"] == 2) {
        // Conflitante possui Status Análise (Aprovado)
        $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 7 WHERE cod_id = {$conflitante["id"]}";
        $this->bancodedados->query($query);
        $this->registraComentario($conflitante["id"], "<b>Ação:</b> Cancelado", $_SESSION["usuario"]);
      } else if ($conflitante["status"] == 4) {
        // Conflitante possui Status Ativo
        $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 5, cod_termino = NOW() WHERE cod_id = {$conflitante["id"]}";
        $this->bancodedados->query($query);
        $this->registraComentario($conflitante["id"], "<b>Ação:</b> Finalizado", $_SESSION["usuario"]);
      }
      $dados = $this->buscaDadosPeriodo($conflitante["id"]);
      if ($dados["atual"]) {
        $query = "UPDATE tab_condominios SET usu_id = NULL WHERE con_id = {$dados["condominio"]}";
        $this->bancodedados->query($query);
      }
      $this->finalizaConflitantesFilhos($conflitante["id"], $dados["atual"], $conflitante["status"]);
    }
  }

  function finalizaConflitantesFilhos($id, $atual, $status) {
    $query = "
      SELECT
        cln_id AS id,
        cli_id AS cliente
      FROM
        tab_clientes_responsabilidades
      WHERE
        cod_id = $id";
    $query = $this->bancodedados->query($query);
    while (($filho = $this->bancodedados->resultadoArray($query)) != false) {
      if ($status == 2) {
        // Filho possui Status Análise (Aprovado)
        $update = "UPDATE tab_clientes_responsabilidades SET cra_id = '7' WHERE cln_id = '{$filho["id"]}'";
        $this->bancodedados->query($update);
        $this->registraComentarioCliente($filho["id"], "<b>Ação:</b> Cancelado", $_SESSION["usuario"]);
      } else if ($status == 4) {
        // Filho possui Status Ativo
        $update = "UPDATE tab_clientes_responsabilidades SET cra_id = '5', cln_termino = NOW() WHERE cln_id = '{$filho["id"]}'";
        $this->bancodedados->query($update);
        $this->registraComentarioCliente($filho["id"], "<b>Ação:</b> Finalizado", $_SESSION["usuario"]);
      }
      if ($atual) {
        $update = "UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '{$filho["cliente"]}'";
        $this->bancodedados->query($update);
      }
    }
  }

  ################
  ### Reprovar ###
  ################

  function reprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehReprovavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.reprovar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=reprovar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_condominios_responsabilidades SET cru_id = '3' WHERE cod_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
        $this->registraComentario($_GET["i"], "<b>Ação:</b> Reprovado".$comentario, $_SESSION["usuario"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  function existeAtivadoAprovado($id) {
    $query = "
      SELECT
        con_id AS id,
        crp_id AS tipo,
        cod_inicio AS inicio,
        cod_termino AS termino
      FROM
        tab_condominios_responsabilidades
      WHERE
        cod_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);

    $query = "
      SELECT
        COUNT(cod_id) AS quantidade
      FROM
        tab_condominios_responsabilidades
      WHERE
        con_id = {$dados["id"]} AND
        cru_id IN (2,4) AND
        crp_id = {$dados["tipo"]} AND
        ((cod_inicio BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
         (cod_termino BETWEEN '{$dados["inicio"]}' AND '{$dados["termino"]}') OR
         ('{$dados["inicio"]}' BETWEEN cod_inicio AND cod_termino) OR
         ('{$dados["termino"]}' BETWEEN cod_inicio AND cod_termino))";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "quantidade") ? true : false;
  }

  function existeAtivadoAprovadoNoTermino($id, $termino, $tipo) {
    $query = "
      SELECT
        con_id AS id,
        cod_inicio AS inicio
      FROM
        tab_condominios_responsabilidades
      WHERE
        cod_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);

    $query = "
      SELECT
        COUNT(cod_id) AS quantidade
      FROM
        tab_condominios_responsabilidades
      WHERE
        (con_id = {$dados["id"]}) AND
        (cru_id IN (2,4)) AND
        (cod_id != $id) AND
        (crp_id = $tipo) AND
        (($termino BETWEEN cod_inicio AND cod_termino) OR
         (cod_inicio BETWEEN '{$dados["inicio"]}' AND $termino) OR
         (cod_termino BETWEEN '{$dados["inicio"]}' AND $termino))";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "quantidade") ? true : false;
  }

  function buscaQuantidadeResponsabilidades($usuario_id, $inicio, $termino) {
    $query = "
      SELECT
        tab_condominios_responsabilidades.cod_inicio AS inicio,
        tab_condominios_responsabilidades.cod_termino AS termino
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_condominios ON (tab_condominios_responsabilidades.con_id = tab_condominios.con_id)
      WHERE
        tab_condominios.cos_id = 2 AND
        tab_condominios_responsabilidades.cru_id IN (2, 4) AND
        tab_condominios_responsabilidades.usu_id = $usuario_id AND
        ((tab_condominios_responsabilidades.cod_inicio BETWEEN '$inicio' AND '$termino') OR
         (tab_condominios_responsabilidades.cod_termino BETWEEN '$inicio' AND '$termino'))";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if (strtotime($dados["inicio"]) >= strtotime($inicio)) {
        $cenarios[strtotime($dados["inicio"])] = 0;
      }
      if (strtotime($dados["termino"]) + 1 >= strtotime($inicio)) {
        $cenarios[strtotime($dados["termino"]) + 1] = 0;
      }
    }
    // Adiciona cenários da responsabilidade solicitada
    $cenarios[strtotime($inicio)] = 0;
    $cenarios[strtotime($termino)] = 0;
    ksort($cenarios);
    foreach ($cenarios as $cenario => $quantidade) {
      $cenarios[$cenario] = $this->buscaQuantidadeCenario($usuario_id, date("Y-m-d H:i:s", $cenario));
      $maior_quantidade = $cenarios[$cenario] > $maior_quantidade ? $cenarios[$cenario] : $maior_quantidade;
    }
    return $maior_quantidade;
  }

  function buscaQuantidadeCenario($usuario_id, $data) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_condominios_responsabilidades.cod_id) AS quantidade
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_condominios ON (tab_condominios_responsabilidades.con_id = tab_condominios.con_id)
      WHERE
        tab_condominios.cos_id = 2 AND
        tab_condominios_responsabilidades.cru_id IN (2, 4) AND
        tab_condominios_responsabilidades.usu_id = $usuario_id AND
        '$data' BETWEEN tab_condominios_responsabilidades.cod_inicio AND tab_condominios_responsabilidades.cod_termino";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "quantidade");
  }

  #################
  ### Finalizar ###
  #################

  function finalizar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehFinalizavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.finalizar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_condominios_responsabilidades SET cru_id = '5', cod_termino = IF(NOW() < cod_inicio, cod_inicio, NOW()) WHERE cod_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $dados = $this->buscaDadosPeriodo($_GET["i"]);
        if ($dados["atual"]) {
          $query = "UPDATE tab_condominios SET usu_id = NULL WHERE con_id = {$dados["condominio"]}";
          $this->bancodedados->query($query);
        }
        if ($_POST["comentario"]) $comentario = "<br><b>Comentário:</b> ".trim($_POST["comentario"]);
        $this->registraComentario($_GET["i"], "<b>Ação:</b> Finalizado".$comentario, $_SESSION["usuario"]);
        $this->finalizarClientes($_GET["i"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
        exit;
      }
    } else {
      $this->fechar();
    }
  }

  function finalizarClientes($id) {
    $query = "SELECT cln_id AS id FROM tab_clientes_responsabilidades WHERE cod_id = '$id' AND cra_id IN (2,4)";
    $query = $this->bancodedados->query($query);
    while (($responsabilidades = $this->bancodedados->resultadoArray($query)) != false) {
      $this->registraComentarioCliente($responsabilidades["id"], "<b>Ação:</b> Finalizado (Herdado do Condomínio)", $_SESSION["usuario"]);
      $dados = $this->buscaDadosPeriodoCliente($responsabilidades["id"]);
      if ($dados["atual"]) {
        // Limpa Responsável
        $query_update = "UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '{$dados["cliente"]}'";
        $this->bancodedados->query($query_update);
      }
    }
    $query = "UPDATE tab_clientes_responsabilidades SET cra_id = '5', cln_termino = IF(NOW() < cln_inicio, cln_inicio, NOW()) WHERE cod_id = '$id' AND cra_id IN (2,4)";
    $this->bancodedados->query($query);
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && ($this->ehRemovivel() || $this->ehRemovivelPlus())) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios_responsabilidades.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "DELETE FROM rel_cov_cod_estacontido WHERE cod_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_condominios_responsabilidades_comentarios WHERE cod_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $this->removeResponsabilidadesFilhas($_GET["i"]);
        $query = "DELETE FROM tab_condominios_responsabilidades WHERE cod_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  function removeResponsabilidadesFilhas($id) {
    $query = "SELECT cln_id AS id FROM tab_clientes_responsabilidades WHERE cod_id = $id";
    $resp = $this->bancodedados->resultadoArrayTotal($this->bancodedados->query($query));
    for ($i = 0; $i < sizeof($resp); $i++) {
      $query = "DELETE FROM tab_clientes_responsabilidades_comentarios WHERE cln_id = {$resp[$i]["id"]}";
      $this->bancodedados->query($query);
      $query = "DELETE FROM tab_clientes_responsabilidades WHERE cln_id = {$resp[$i]["id"]}";
      $this->bancodedados->query($query);
    }
  }

  ##############
  ### Listar ###
  ##############

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
    $responsaveis = $this->buscaResponsaveis();
    foreach ($responsaveis as $id => $nome) {
      $select_responsaveis[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "responsavel", "descricao" => "Responsável", "valor" => $select_responsaveis, "where" => "(tab_condominios_responsabilidades.usu_id = [valor])", "disabled" => ($this->permissoes["condominios_responsabilidades"]["listar_todos"]) ? false : true);
    if (!$this->permissoes["condominios_responsabilidades"]["listar_todos"]) {
      $valores["responsavel"] = $_SESSION["usuario"];
    }
    // Categorias
    $categorias = $this->buscaCategorias();
    foreach ($categorias as $id => $nome) {
      $select_categorias[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "categoria", "descricao" => "Categoria", "valor" => $select_categorias, "where" => "(tab_condominios_responsabilidades.crg_id = [valor])");
    // Tipos
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "(tab_condominios_responsabilidades.crp_id = [valor])");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio_inicio", "descricao" => "Data Início &raquo; Início", "valor" => "", "where" => "tab_condominios_responsabilidades.cod_inicio >= ".$this->formataData($valores["data_inicio_inicio"]));
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_inicio_termino", "descricao" => "Data Início &raquo; Término", "valor" => "", "where" => "tab_condominios_responsabilidades.cod_inicio <= ".$this->formataData($valores["data_inicio_termino"]));
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_statuss[] = array ("id" => $id, "nome" => $nome);
    }
    $select_statuss[] = array ("id" => "-1", "nome" => "Análise", "where" => "(tab_condominios_responsabilidades.cru_id IN (1, 2, 3, 7, 8))");
    $select_statuss[] = array ("id" => "-1", "nome" => "Inativo", "where" => "(tab_condominios_responsabilidades.cru_id IN (5, 6))");
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_statuss, "where" => "(tab_condominios_responsabilidades.cru_id = [valor])");
    // Data Início
    $itens[] = array ("tipo" => "input", "nome" => "data_termino_inicio", "descricao" => "Data Término &raquo; Início", "valor" => "", "where" => "tab_condominios_responsabilidades.cod_termino >= ".$this->formataData($valores["data_termino_inicio"]));
    // Data Término
    $itens[] = array ("tipo" => "input", "nome" => "data_termino_termino", "descricao" => "Data Término &raquo; Término", "valor" => "", "where" => "tab_condominios_responsabilidades.cod_termino <= ".$this->formataData($valores["data_termino_termino"]));

    // Condomínio » Status
    $status = $this->buscaStatusCondominio();
    foreach ($status as $id => $nome) {
      $select_status_condominio[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "condominio_status", "descricao" => "Condomínio » Status", "valor" => $select_status_condominio, "where" => "(tab_condominios.cos_id = [valor])");
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
        tab_condominios.con_id AS id,
        tab_condominios.con_nome AS condominio,
        DATE_FORMAT(tab_condominios_responsabilidades.cod_inicio, '%d/%m/%Y<br>(%H:%i)') AS inicio,
        DATE_FORMAT(tab_condominios_responsabilidades.cod_termino, '%d/%m/%Y<br>(%H:%i)') AS termino,
        tab_usuarios.usu_nome AS responsavel,
        tab_condominios_responsabilidades_categorias.crg_nome AS categoria_nome,
        tab_condominios_responsabilidades_tipos.crp_nome AS tipo_nome,
        tab_condominios_responsabilidades_status.cru_nome AS status_nome
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_condominios_responsabilidades_tipos ON (tab_condominios_responsabilidades.crp_id = tab_condominios_responsabilidades_tipos.crp_id)
        INNER JOIN tab_condominios_responsabilidades_categorias ON (tab_condominios_responsabilidades.crg_id = tab_condominios_responsabilidades_categorias.crg_id)
        INNER JOIN tab_condominios_responsabilidades_status ON (tab_condominios_responsabilidades.cru_id = tab_condominios_responsabilidades_status.cru_id)
        INNER JOIN tab_usuarios ON (tab_condominios_responsabilidades.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_condominios ON (tab_condominios_responsabilidades.con_id = tab_condominios.con_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        tab_condominios_responsabilidades.cod_inicio
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["condominios"]["ver_dm"]) {
        $dados["link"] = "?m=condominios&a=ver_dm&i={$dados["id"]}";
      }
      $dados["categoria_nome"] = str_replace(" (", "<br>(", $dados["categoria_nome"]);
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array("condominio" => "Condomínio", "inicio" => "Início", "termino" => "Término", "responsavel" => "Responsável", "categoria_nome" => "Categoria", "tipo_nome" => "Tipo", "status_nome" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem;
  }

  #################
  ### Condições ###
  #################

  function ehAprovavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["aprovar"]) return false;
    if (!in_array($this->dados_item["status_id"], array(1, 3))) return false;
    if ($this->existeAtivadoAprovado($_GET["i"]) && !$this->permissoes[$this->dados_modulo["nome"]]["aprovar_plus"]) return false;
    return true;
  }

  function ehReprovavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["reprovar"] && $this->dados_item["status_id"] == 1) return true;
    return false;
  }

  function ehRemovivel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["status_id"] == 1 && $this->dados_item["responsavel_id"] == $_SESSION["usuario"]) return true;
    return false;
  }

  function ehRemovivelPlus() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["remover_plus"] && $this->dados_item["status_id"] < 4) return true;
    return false;
  }

  function ehFinalizavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["finalizar"] && in_array($this->dados_item["status_id"], array(2,4))) return true;
    return false;
  }

  function ehEditavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && in_array($this->dados_item["status_id"], array(1,3)) && $this->dados_item["responsavel_id"] == $_SESSION["usuario"]) return true;
    return false;
  }

  function ehEditavelPlus() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["editar_plus"] && $this->dados_item["status_id"] < 4) return true;
    return false;
  }

  function ehProrrogavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["prorrogar"] && $this->dados_item["status_id"] < 5) return true;
    return false;
  }

  ###########
  ### RDS ###
  ###########

  function geraListagemCondominiosResponsabilidades($dados) {
    $cabecalho = array ("responsavel" => "Responsável", "periodo" => "Período", "categoria" => "Categoria", "tipo" => "Tipo", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  function buscaCondominiosResponsabilidades($condominio_id) {
    $query = "
      SELECT
        tab_condominios_responsabilidades.cod_id AS id,
        DATE_FORMAT(tab_condominios_responsabilidades.cod_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_condominios_responsabilidades.cod_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tab_usuarios.usu_nome AS responsavel,
        tab_condominios_responsabilidades_categorias.crg_nome AS categoria,
        tab_condominios_responsabilidades_tipos.crp_nome AS tipo,
        tab_condominios_responsabilidades_status.cru_nome AS status
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_condominios_responsabilidades_tipos ON (tab_condominios_responsabilidades.crp_id = tab_condominios_responsabilidades_tipos.crp_id)
        INNER JOIN tab_condominios_responsabilidades_categorias ON (tab_condominios_responsabilidades.crg_id = tab_condominios_responsabilidades_categorias.crg_id)
        INNER JOIN tab_condominios_responsabilidades_status ON (tab_condominios_responsabilidades.cru_id = tab_condominios_responsabilidades_status.cru_id)
        INNER JOIN tab_usuarios ON (tab_condominios_responsabilidades.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_condominios_responsabilidades.con_id = $condominio_id
      ORDER BY
        tab_condominios_responsabilidades.cod_inicio,
        tab_condominios_responsabilidades.cod_termino";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["condominios_responsabilidades"]["ver"]) {
        $dados["link"] = "javascript:verCondominioResponsabilidade({$dados["id"]})";
        if ($dados["inicio"]) $dados["periodo"][] = $dados["inicio"];
        if ($dados["termino"]) $dados["periodo"][] = $dados["termino"];
        $dados["periodo"] = implode("<br>", $dados["periodo"]);
        $dados["categoria"] = str_replace(" (", "<br>(", $dados["categoria"]);
        $dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
      }
      $responsabilidades[] = $dados;
    }
    return $responsabilidades;
  }

  function buscaCondominioResponsavel($condominio_id) {
    $query = "
      SELECT
        tab_usuarios.usu_nome AS nome
      FROM
        tab_condominios
        INNER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_condominios.con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

}

?>
