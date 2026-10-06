<?

class tarefas {

  var $bancodedados;
  var $permissoes;
  var $acao;
  var $conteudo;
  var $dados_item;
  var $dados_modulo;
  var $dados_acao;

  function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao(empty($_GET["a"]) ? "agenda" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

	function geraTitulo() {
		$titulo	= '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
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

  function buscaDadosUsuario($usuario_id) {
    $query = "
      SELECT
        usu_id AS id,
        usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
  }

  function buscaDiretor($setor_id) {
    $query = "
      SELECT
        tab_usuarios.usu_id AS id
      FROM
        tab_usuarios
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
      WHERE
        ors_id = $setor_id AND
        orf_id = 4";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tar_id AS protocolo,
        ors_id AS setor,
        orf_id AS funcao,
        usu_id AS usuario,
        tar_titulo AS titulo,
        DATE_FORMAT(tar_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
        DATE_FORMAT(tar_prazo_inicio, '%d/%m/%Y (%H:%i)') AS prazo_inicio,
        DATE_FORMAT(tar_prazo_termino, '%d/%m/%Y (%H:%i)') AS prazo_termino,
        DATE_FORMAT(tar_previsao_inicio, '%d/%m/%Y (%H:%i)') AS previsao_inicio,
        DATE_FORMAT(tar_previsao_termino, '%d/%m/%Y (%H:%i)') AS previsao_termino,
        DATE_FORMAT(tar_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
        tar_andamento AS andamento,
        tat_id AS tipo,
        tas_id AS status
      FROM
        tab_tarefas
      WHERE
        tar_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $this->dados_item["id"] = $id;
      $dados["setor"] = $dados["setor"] ? $this->buscaDadosSetor($dados["setor"]) : null;
      $this->dados_item["setor_id"] = $dados["setor"]["id"];
      $this->dados_item["setor_nome"] = $dados["setor"]["nome"];
      $dados["funcao"] = $dados["funcao"] ? $this->buscaDadosFuncao($dados["funcao"]) : null;
      $this->dados_item["funcao_id"] = $dados["funcao"]["id"];
      $this->dados_item["funcao_nome"] = $dados["funcao"]["nome"];
      $dados["usuario"] = $dados["usuario"] ? $this->buscaDadosUsuario($dados["usuario"]) : null;
      $this->dados_item["usuario_id"] = $dados["usuario"]["id"];
      $this->dados_item["usuario_nome"] = $dados["usuario"]["nome"];
      $this->dados_item["abertura"] = $dados["abertura"];
      $this->dados_item["prazo"] = array();
      if ($dados["prazo_inicio"]) $this->dados_item["prazo"][] = "<b>Início:</b> " . $dados["prazo_inicio"];
      if ($dados["prazo_termino"]) $this->dados_item["prazo"][] = "<b>Término:</b> " . $dados["prazo_termino"];
      $this->dados_item["prazo"] = @implode(" ",$this->dados_item["prazo"]);
      $this->dados_item["previsao"] = array();
      if ($dados["previsao_inicio"]) $this->dados_item["previsao"][] = "<b>Início:</b> " . $dados["previsao_inicio"];
      if ($dados["previsao_termino"]) $this->dados_item["previsao"][] = "<b>Término:</b> " . $dados["previsao_termino"];
      $this->dados_item["previsao"] = @implode(" ",$this->dados_item["previsao"]);
      $this->dados_item["fechamento"] = $dados["fechamento"];
      $this->dados_item["andamento"] = $dados["andamento"];
      $tipos = $this->buscaTipos();
      $this->dados_item["tipo_id"] = $dados["tipo"];
      $this->dados_item["titulo"] = $dados["titulo"];
      $this->dados_item["protocolo"] = sprintf("%08s", $dados["protocolo"]);
      $this->dados_item["tipo_nome"] = $tipos[$dados["tipo"]];
      $status = $this->buscaStatus();
      $this->dados_item["status_id"] = $dados["status"];
      $this->dados_item["status_nome"] = $status[$dados["status"]];
      $this->dados_item["etapas"] = $this->buscaEtapas($id);
    } else {
      unset($this->dados_item);
    }
  }

  function buscaDadosSetor($setor_id) {
    $query = "
      SELECT
        ors_id AS id,
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
        $setor_pai = $this->buscaDadosSetor($setor["pai"]);
        $setor["nome"] = $setor_pai["nome"]." » ".$setor["nome"];
        $setor["pai_acima"] = $setor["pai"];
        $setor["pai"] = $setor_pai["pai"]  ? $setor_pai["pai"] : $setor["pai"];
      }
      return $setor;
    }
  }

  function buscaDadosFuncao($funcao_id) {
  	if ($funcao_id) {
	    $query = "
	      SELECT
	        orf_id AS id,
	        orf_nome AS nome
	      FROM
	        tab_organizacao_funcoes
	      WHERE
	        orf_id = $funcao_id";
	    $query = $this->bancodedados->query($query);
	    return $this->bancodedados->resultadoArray($query);
  	}
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaSetores($usuario_id = null) {
    $query = "
      SELECT DISTINCT
        tab_organizacao_setores.ors_id AS id
      FROM
        tab_organizacao_setores".($usuario_id ? "
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_organizacao_setores.ors_id = rel_usu_orf_ors_exerce.ors_id)
      WHERE
        rel_usu_orf_ors_exerce.usu_id = $usuario_id" : "");
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $setores[] = $this->buscaDadosSetor($dados["id"]);
    }
    return @$this->ordena_multiarray($setores, array("nome", "id"));;
  }

  function buscaFuncoes() {
    $query = "
      SELECT
        orf_id AS id,
        orf_nome AS nome
      FROM
        tab_organizacao_funcoes
      ORDER BY
        orf_id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $funcoes[$dados["id"]] = $dados["nome"];
    }
    return $funcoes;
  }

  function buscaTipos($detalhes = false) {
    $query = "
      SELECT
        tat_id AS id,
        tat_nome AS nome,
  			tat_disponivel AS disponivel,
  			ors_id AS setor
      FROM
        tab_tarefas_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($detalhes) {
        $tipos[] = $dados;
      } else {
        $tipos[$dados["id"]] = $dados["nome"];
      }
    }
    return $tipos;
  }

  function buscaDadosTipo($id) {
  	$query = "
  		SELECT
        tat_id AS id,
  			tat_nome AS nome,
  			tat_disponivel AS disponivel,
  			ors_id AS setor
  		FROM
  			tab_tarefas_tipos
  		WHERE
  			tat_id = $id";
  	$query = $this->bancodedados->query($query);
  	$dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
  	  $dados["setor"] = $this->buscaDadosSetor($dados["setor"]);
  	 return $dados;
  	}
  }

  function filtraTiposDisponiveis($tipos, $origem, $destino) {
    // Origem
    $origem_array = @explode(",", $origem);
    $origem_setor = $origem_array[0] ? $this->buscaDadosSetor($origem_array[0]) : null;
    $origem_funcao = $origem_array[1] ? $this->buscaDadosFuncao($origem_array[1]) : null;
    // Destino
    $destino_array = @explode(",", $destino);
    $destino_setor = $destino_array[0] ? $this->buscaDadosSetor($destino_array[0]) : null;
    $destino_funcao = $destino_array[1] ? $this->buscaDadosFuncao($destino_array[1]) : null;
    // Destinos Abaixo
    $destinos_abaixo = $this->buscaDestinosAbaixo($origem_setor["id"], $origem_funcao["id"]);
    for ($i = 0; $i < @sizeof($destinos_abaixo); $i++) {
    	if ($destinos_abaixo[$i]["id"] == $destino) $destino_tipo = "abaixo";
    }
    // Destinos Acima
    $destinos_acima = $this->buscaDestinosAcima($origem_setor["id"], $origem_funcao["id"]);
    for ($i = 0; $i < @sizeof($destinos_acima); $i++) {
    	if ($destinos_acima[$i]["id"] == $destino) $destino_tipo = "acima";
    }
    // Origem e Destino Iguais
    if ($origem_setor["id"] == $destino_setor["id"] && $origem_funcao["id"] == $destino_funcao["id"]) {
    	$destino_tipo = "mesmo";
    }
    // Filtra Disponíveis
    foreach ($tipos as $tipo) {
  		if ($tipo["disponivel"] == 1 && $tipo["setor"] == $destino_setor["id"]) {
  			$tipos_disponiveis[] = $tipo;
  		}
    }
    // Retorna Tipos Disponíveis
    return $tipos_disponiveis;
  }

  function filtraEtapaTiposDisponiveis($etapa_tipos, $tarefa, $origem) {
    $origem = @explode(",", $origem);
    $origem["setor"] = $origem[0] ? $this->buscaDadosSetor($origem[0]) : null;
    $origem["funcao"] = $origem[1] ? $this->buscaDadosFuncao($origem[1]) : null;
    $origem["usuario"] = $origem[1] ? $this->buscaDadosUsuario($origem[2]) : null;

    $solicitante = $this->buscaTarefaSolicitante($tarefa);
    $responsavel = $this->buscaTarefaResponsavel($tarefa);

    // 2 Registro de Tipo
    if ($origem["setor"]["id"] == $responsavel["setor"]["id"] && !$origem["setor"]["pai"] && $origem["funcao"]["id"] == 4) {
      if ($etapa_tipos[2]) $etapa_tipos_disponiveis[] = array("id" => 2, "nome" => $etapa_tipos[2]);
    }
    // 4 Registro de Prazo
    if ($origem["setor"]["id"] == $solicitante["setor"]["id"] && $origem["funcao"]["id"] == $solicitante["funcao"]["id"] && $origem["usuario"]["id"] == $solicitante["usuario"]["id"]) {
      if ($etapa_tipos[4]) $etapa_tipos_disponiveis[] = array("id" => 4, "nome" => $etapa_tipos[4]);
    }
  	// 5 Registro de Andamento
    if ($origem["setor"]["id"] == $responsavel["setor"]["id"] && $origem["funcao"]["id"] == $responsavel["funcao"]["id"] && $origem["usuario"]["id"] == $responsavel["usuario"]["id"]) {
      if ($etapa_tipos[5]) $etapa_tipos_disponiveis[] = array("id" => 5, "nome" => $etapa_tipos[5]);
    }
    // 7 Registro de Previsão
    if ($origem["setor"]["id"] == $responsavel["setor"]["id"] && $origem["funcao"]["id"] == $responsavel["funcao"]["id"] && $origem["usuario"]["id"] == $responsavel["usuario"]["id"]) {
      if ($etapa_tipos[7]) $etapa_tipos_disponiveis[] = array("id" => 7, "nome" => $etapa_tipos[7]);
    }
    // 8 Registro de Comentário
    if ($etapa_tipos[8]) $etapa_tipos_disponiveis[] = array("id" => 8, "nome" => $etapa_tipos[8]);
    // 9 Registro de Anexo
    if ($etapa_tipos[9]) $etapa_tipos_disponiveis[] = array("id" => 9, "nome" => $etapa_tipos[9]);

    return $etapa_tipos_disponiveis;
  }

  function buscaStatus() {
    $query = "
      SELECT
        tas_id AS id,
        tas_nome AS nome
      FROM
        tab_tarefas_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaDadosStatus($status_id) {
  	$query = "
      SELECT
        tas_id AS id,
        tas_nome AS nome
      FROM
        tab_tarefas_status
      WHERE
        tas_id = $status_id";
  	$query = $this->bancodedados->query($query);
  	return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosEtapaTipo($tipo_id) {
  	$query = "
      SELECT
        tet_id AS id,
        tet_nome AS nome
      FROM
        tab_tarefas_etapas_tipos
      WHERE
        tet_id = $tipo_id";
  	$query = $this->bancodedados->query($query);
  	return $this->bancodedados->resultadoArray($query);
  }

  #############
  ### Geral ###
  #############

  function buscaUnidades($unidade = "") {
    $unidades[0] = $unidade;
    $unidades[1] = "K".$unidade;
    $unidades[2] = "M".$unidade;
    $unidades[3] = "G".$unidade;
    $unidades[4] = "T".$unidade;
    return $unidades;
  }

  function formataUnidade($valor, $unidade = "B") {
    $unidades = $this->buscaUnidades($unidade);
    $unidade_id = floor(log($valor, 1024));
     $valor = round($valor / pow(1024, $unidade_id), 2);
    return number_format($valor, 2, ",", ".")." ".$unidades[$unidade_id];
  }

  function geraSelect($itens, $selecionado = null, $item_nulo = false) {
    if ($item_nulo) $select = "<option></option>";
    foreach ($itens as $valor => $item) {
      $select .= "<option value=\"$valor\"".(isset($selecionado) && $valor == $selecionado ? " selected" : "").">$item</option>";
    }
    return $select;
  }

  function geraSelectHorarios($horario_ativo) {
    for ($hora = 0; $hora < 24; $hora++) {
      for ($minuto = 0; $minuto < 60; $minuto = $minuto + 10) {
        $horario = (($hora >= 10) ? $hora : "0".$hora).":".(($minuto >= 10) ? $minuto : "0".$minuto);
        $horarios[$horario] = $horario;
      }
    }
    return '<table width="100%" cellpadding="1" bgcolor="#505050"><tr class="txtBranco11"><td width="20%"><b>&nbsp;Horário:</b></td><td><select name="calendario_tarefa_horario" id="calendario_tarefa_horario" class="inputBrancoAzulEscuro11" style="width: 100%" onChange="window.opener.mudaTarefaDataHorario(\'\',\'\',\'\',this.value);">'.$this->geraSelect($horarios, $horario_ativo).'</select></td></tr></table>';
  }

  function buscaParticipantes($tarefa_id) {
    $query = "
      -- Participantes
      SELECT DISTINCT
        usu_id AS id
      FROM
        tab_tarefas_etapas
      WHERE
        tar_id = $tarefa_id
      UNION
      -- Responsaveis
      SELECT DISTINCT
        IF(tab_tarefas.usu_id IS NOT NULL, tab_tarefas.usu_id, rel_usu_orf_ors_exerce.usu_id) AS id
      FROM
        tab_tarefas
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_tarefas.ors_id = rel_usu_orf_ors_exerce.ors_id) AND (tab_tarefas.orf_id = rel_usu_orf_ors_exerce.orf_id)
      WHERE
        tab_tarefas.tar_id = $tarefa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $participantes[] = $dados["id"];
    }
    return $participantes;
  }

  function atualizaAguardaCiencia($tarefa_id, $usuario_id) {
    // Busca Participantes
    $participantes = $this->buscaParticipantes($tarefa_id);
    // Remove Ciências Pendentes
    $query = "DELETE FROM rel_tar_usu_aguardaciencia WHERE tar_id = $tarefa_id";
    $this->bancodedados->query($query);
    for ($i = 0; $i < sizeof($participantes); $i++) {
      if ($participantes[$i] != $usuario_id) {
        $query = "INSERT INTO rel_tar_usu_aguardaciencia (tar_id, usu_id) VALUES ($tarefa_id, $participantes[$i])";
        $this->bancodedados->query($query);
      }
    }
  }

  function ehEditavel() {
    $participantes = $this->buscaParticipantes($this->dados_item["id"]);
    if (@in_array($_SESSION["usuario"], $participantes) && $this->dados_item["status_id"] == 1) return true;
    return false;
  }

  function ehCienciavel() {
    $query = "SELECT DISTINCT usu_id AS id FROM rel_tar_usu_aguardaciencia WHERE tar_id = '{$this->dados_item["id"]}' AND usu_id = '{$_SESSION["usuario"]}'";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->linhas($query)) ? true : false;
  }

  function ehCapturavel() {
    $query = "SELECT DISTINCT rel_usu_orf_ors_exerce.usu_id AS id FROM tab_tarefas INNER JOIN rel_usu_orf_ors_exerce ON (tab_tarefas.ors_id = rel_usu_orf_ors_exerce.ors_id) AND (tab_tarefas.orf_id = rel_usu_orf_ors_exerce.orf_id) WHERE tab_tarefas.tar_id = '{$this->dados_item["id"]}' AND tab_tarefas.usu_id IS NULL AND rel_usu_orf_ors_exerce.usu_id = '{$_SESSION["usuario"]}'";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->linhas($query) && $this->dados_item["status_id"] == 1) ? true : false;
  }

  function ehEncaminhavel() {
    $query = "SELECT DISTINCT usu_id FROM tab_tarefas WHERE tar_id = '{$this->dados_item["id"]}' AND usu_id = '{$_SESSION["usuario"]}'";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->linhas($query) && $this->dados_item["status_id"] == 1) ? true : false;
  }

  function ehFechavel() {
    $query = "SELECT DISTINCT usu_id FROM tab_tarefas_etapas WHERE tar_id = '{$this->dados_item["id"]}' AND usu_id = '{$_SESSION["usuario"]}' AND tet_id = '1'";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->linhas($query) && $this->dados_item["status_id"] == 1) ? true : false;
  }

  function ehVisivel() {
    $participantes = $this->buscaParticipantes($this->dados_item["id"]);
    if (@in_array($_SESSION["usuario"],$participantes)) return true;
    return false;
  }

  ##############
  ### Etapas ###
  ##############

  function extraiEtapaParametros($tipo, $variaveis) {
    if ($variaveis["origem"]) {
      $variaveis["origem"] = explode(",", $variaveis["origem"]);
      $parametros["origem_setor"] = trim($variaveis["origem"][0]);
      $parametros["origem_funcao"] = trim($variaveis["origem"][1]);
      $parametros["origem_usuario"] = trim($variaveis["origem"][2]);
    }
    switch ($tipo) {
      case 1: // Origem
        break;
      case 2: // Tipo
        if ($variaveis["novo_tipo_nome"]) {
          $parametros["novo_tipo"]["nome"] = $variaveis["novo_tipo_nome"];
          $parametros["novo_tipo"]["autoaprovado"] = ($variaveis["novo_tipo_autoaprovado"]) ? 1 : 0;
        } else {
          $parametros["tipo"] = $variaveis["tipo"];
        }
        break;
      case 3: // Descrição
        $parametros["descricao"] = trim($variaveis["descricao"]);
        break;
      case 4: // Prazo
        $parametros["prazo_inicio"] = $variaveis["prazo_inicio"];
        $parametros["prazo_termino"] = $variaveis["prazo_termino"];
        break;
      case 5: // Andamento
        $parametros["andamento"] = intval($variaveis["andamento"]);
        break;
      case 6: // Encaminhamento
        $variaveis["destino"] = explode(",", $variaveis["destino"]);
        $parametros["destino_setor"] = trim($variaveis["destino"][0]);
        $parametros["destino_funcao"] = trim($variaveis["destino"][1]);
        $parametros["destino_usuario"] = trim($variaveis["destino"][2]);
        $parametros["comentario"] = trim($variaveis["comentario"]);
        break;
      case 7: // Previsão
        $parametros["previsao_inicio"] = $variaveis["previsao_inicio"];
        $parametros["previsao_termino"] = $variaveis["previsao_termino"];
        break;
      case 8: // Comentário
        $parametros["conteudo"] = trim($variaveis["conteudo"]);
        break;
      case 9: // Anexo
        if (!$variaveis["arquivo"]["error"]) {
          $parametros["arquivo"]["nome"] = $variaveis["arquivo"]["name"];
          $parametros["arquivo"]["tipo"] = $variaveis["arquivo"]["type"];
          $parametros["arquivo"]["tamanho"] = $variaveis["arquivo"]["size"];
          $parametros["arquivo"]["temporario"] = $variaveis["arquivo"]["tmp_name"];
          $parametros["arquivo"]["hash"] = md5($parametros["arquivo"]["nome"].$parametros["arquivo"]["tipo"].$parametros["arquivo"]["tamanho"].$parametros["arquivo"]["temporario"].date("YmdHis"));
        }
        $parametros["comentario"] = trim($variaveis["comentario"]);
        break;
      case 10: // Captura
        $parametros["comentario"] = trim($variaveis["comentario"]);
        break;
      case 12: // Convite
        $variaveis["destino"] = explode(",", $variaveis["destino"]);
        $parametros["destino_setor"] = trim($variaveis["destino"][0]);
        $parametros["destino_funcao"] = trim($variaveis["destino"][1]);
        $parametros["destino_usuario"] = trim($variaveis["destino"][2]);
        $parametros["comentario"] = trim($variaveis["comentario"]);
        break;
      case 14: // Abertura
        break;
      case 15:// Fechamento
        $parametros["origem_setor"] = $this->dados_item["setor_id"];
        $parametros["origem_funcao"] = $this->dados_item["funcao_id"];
        $parametros["origem_usuario"] = $this->dados_item["usuario_id"];
        break;
    }
    return $parametros;
  }

  function registraEtapa($tipo, $id, $parametros) {
    switch ($tipo) {
      case 1: // Origem
        $setor = $parametros["origem_setor"];
        $funcao = $parametros["origem_funcao"];
        $usuario = $parametros["origem_usuario"];
        $query = "UPDATE tab_tarefas SET ors_id = '$setor', orf_id = '$funcao', usu_id = '$usuario' WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
      case 2: // Tipo
        if ($parametros["novo_tipo"]) {
          $query = "INSERT INTO tab_tarefas_tipos (tat_nome, tat_disponivel, ors_id) VALUES ('{$parametros["novo_tipo"]["nome"]}', 1, '{$parametros["origem_setor"]}')";
          $this->bancodedados->query($query);
          $parametros["tipo"] = $this->bancodedados->ultimoIdInserido();
          if ($parametros["novo_tipo"]["autoaprovado"]) {
            $query = "INSERT INTO rel_tat_ors_orf_ehautoaprovada (tat_id, ors_id, orf_id) VALUES ('{$parametros["tipo"]}', '{$parametros["origem_setor"]}', '{$parametros["origem_funcao"]}')";
            $this->bancodedados->query($query);
          }
        }
        $query = "UPDATE tab_tarefas SET tat_id = '{$parametros["tipo"]}' WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
      case 3: // Descrição
        break;
      case 4: // Prazo
        $prazo_inicio = (!$parametros["prazo_inicio"]) ? "NULL" : "'" . substr($parametros["prazo_inicio"],6,4) . substr($parametros["prazo_inicio"],3,2) . substr($parametros["prazo_inicio"],0,2) . substr($parametros["prazo_inicio"],12,2) . substr($parametros["prazo_inicio"],15,2) . "00'";
        $prazo_termino = (!$parametros["prazo_termino"]) ? "NULL" : "'" . substr($parametros["prazo_termino"],6,4) . substr($parametros["prazo_termino"],3,2) . substr($parametros["prazo_termino"],0,2) . substr($parametros["prazo_termino"],12,2) . substr($parametros["prazo_termino"],15,2) . "00'";
        $query = "UPDATE tab_tarefas SET tar_prazo_inicio = $prazo_inicio, tar_prazo_termino = $prazo_termino WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
      case 5: // Andamento
        $query = "UPDATE tab_tarefas SET tar_andamento = '{$parametros["andamento"]}' WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
      case 6: // Encaminhamento
        $setor = $parametros["destino_setor"];
        $funcao = $parametros["destino_funcao"];
        if ($parametros["destino_usuario"]) {
          $usuario = "'".$parametros["destino_usuario"]."'";
        } else {
          $usuarios = $this->buscaDestinosPossiveis($setor, $funcao);
          if (sizeof($usuarios) == 1) {
            $parametros["destino_usuario"] = $usuarios[0];
            $usuario = "'".$parametros["destino_usuario"]."'";
          } else {
            $usuario = "NULL";
          }
        }
        $query = "UPDATE tab_tarefas SET ors_id = '$setor', orf_id = '$funcao', usu_id = $usuario WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
      case 7: // Previsão
        $previsao_inicio = (!$parametros["previsao_inicio"]) ? "NULL" : "'".substr($parametros["previsao_inicio"], 6, 4) . substr($parametros["previsao_inicio"],3,2).substr($parametros["previsao_inicio"], 0, 2).substr($parametros["previsao_inicio"], 12, 2).substr($parametros["previsao_inicio"], 15, 2)."00'";
        $previsao_termino = (!$parametros["previsao_termino"]) ? "NULL" : "'".substr($parametros["previsao_termino"], 6, 4) . substr($parametros["previsao_termino"],3,2).substr($parametros["previsao_termino"], 0, 2).substr($parametros["previsao_termino"], 12, 2).substr($parametros["previsao_termino"], 15, 2)."00'";
        $query = "UPDATE tab_tarefas SET tar_previsao_inicio = $previsao_inicio, tar_previsao_termino = $previsao_termino WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
      case 8: // Comentário
        break;
      case 9: // Anexo
        if ($parametros["arquivo"]) {
          move_uploaded_file($parametros["arquivo"]["temporario"], "tarefas/".$parametros["arquivo"]["hash"]);
          unset($parametros["arquivo"]["temporario"]);
        }
        break;
      case 10: // Captura
        $query = "UPDATE tab_tarefas SET usu_id = '{$parametros["origem_usuario"]}' WHERE tar_id = '$id'";
        $this->bancodedados->query($query);
        break;
    }
    $auto_fechada = true;
    if ($auto_fechada) {
      $termino = "NOW()";
      $status = "2";
    } else {
      $termino = "NULL";
      $status = "1";
    }
    $parametros_serializados = addslashes(serialize($parametros));
    $query = "INSERT INTO tab_tarefas_etapas (tar_id, ors_id, orf_id, usu_id, tae_inicio, tae_termino, tae_parametros, tet_id, tes_id) VALUES ('$id', '{$parametros["origem_setor"]}', '{$parametros["origem_funcao"]}', '{$parametros["origem_usuario"]}', NOW(), $termino,'$parametros_serializados', '$tipo', '$status')";
    $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
  }

  function buscaEtapas($tarefa_id, $detalhada = null) {
    $etapas_tipos = ($detalhada) ? "0" : "1, 2, 4, 5, 6, 7, 10, 11, 14, 15";
    $query = "
      SELECT
        tae_id AS id,
        ors_id AS setor,
        orf_id AS funcao,
        usu_id AS usuario,
        DATE_FORMAT(tae_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tae_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tae_parametros AS parametros,
        tet_id AS tipo,
        tes_id AS status
      FROM
        tab_tarefas_etapas
      WHERE
        tar_id = $tarefa_id AND
        tet_id NOT IN ($etapas_tipos)
      ORDER BY
        tae_inicio ASC,
        tae_id ASC";
    $query = $this->bancodedados->query($query);
    $tipos = $this->buscaEtapaTipos();
    $status = $this->buscaEtapaStatus();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $etapa["id"] = $dados["id"];
      $etapa["setor"] = $dados["setor"] ? $this->buscaDadosSetor($dados["setor"]) : null;
      $etapa["setor_id"] = $etapa["setor"]["id"];
      $etapa["setor_nome"] = $etapa["setor"]["nome"];
      $etapa["funcao"] = $dados["funcao"] ? $this->buscaDadosFuncao($dados["funcao"]) : null;
      $etapa["funcao_id"] = $etapa["funcao"]["id"];
      $etapa["funcao_nome"] = $etapa["funcao"]["nome"];
      $etapa["usuario"] = $dados["usuario"] ? $this->buscaDadosUsuario($dados["usuario"]) : null;
      $etapa["usuario_id"] = $etapa["usuario"]["id"];
      $etapa["usuario_nome"] = $etapa["usuario"]["nome"];
      $etapa["inicio"] = $dados["inicio"];
      $etapa["termino"] = $dados["termino"];
      $etapa["data_hora"] = ($etapa["inicio"] == $etapa["termino"]) ? $dados["inicio"] : $dados["inicio"] . " / " . $dados["termino"];
      $etapa["parametros"] = @unserialize($dados["parametros"]);
      $etapa["tipo_id"] = $dados["tipo"];
      $etapa["tipo_nome"] = $tipos[$dados["tipo"]];
      $etapa["status_id"] = $dados["status"];
      $etapa["status_nome"] = $status[$dados["status"]];
      $etapa["detalhamento"] = $this->geraEtapaDetalhamento($etapa);
      $etapas[] = $etapa;
    }
    return $etapas;
  }

  function geraEtapaDetalhamento($etapa) {
    $titulo = "<b>{$etapa["tipo_nome"]}</b>";
    $responsavel = "<b>Por:</b> {$etapa["usuario_nome"]} ({$etapa["setor_nome"]} » {$etapa["funcao_nome"]}) <b>Em:</b> {$etapa["data_hora"]}";
    switch ($etapa["tipo_id"]) {
      case 1:
        $conteudo[] = "$titulo ({$etapa["setor_nome"]} » {$etapa["funcao_nome"]} » {$etapa["usuario_nome"]})";
        $conteudo[] = $responsavel;
        break;
      case 2:
        $tipos = $this->buscaTipos();
        $conteudo[] = "$titulo ({$tipos[$etapa["parametros"]["tipo"]]})";
        $conteudo[] = $responsavel;
        break;
      case 3:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        $conteudo[] = "<b>&nbsp;&raquo; <u>Conteúdo</u>:</b> ".preg_replace("/[\r\n\t ]+/", " ", $etapa["parametros"]["descricao"]);
        break;
      case 4:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["prazo_inicio"]) $conteudo[] = "<b>&nbsp;&raquo; Início:</b> ".$etapa["parametros"]["prazo_inicio"];
        if ($etapa["parametros"]["prazo_termino"]) $conteudo[] = "<b>&nbsp;&raquo; Término:</b> ".$etapa["parametros"]["prazo_termino"];
        break;
      case 5:
        $conteudo[] = "$titulo ({$etapa["parametros"]["andamento"]}%)";
        $conteudo[] = $responsavel;
        break;
      case 6:
        $destino_setor = $etapa["parametros"]["destino_setor"] ? $this->buscaDadosSetor($etapa["parametros"]["destino_setor"]) : null;
        $destino_funcao = $etapa["parametros"]["destino_funcao"] ? $this->buscaDadosFuncao($etapa["parametros"]["destino_funcao"]) : null;
        $destino_usuario = $etapa["parametros"]["destino_usuario"] ? $this->buscaDadosUsuario($etapa["parametros"]["destino_usuario"]) : null;
        $destino = $destino_setor["nome"]." » ".$destino_funcao["nome"].(($destino_usuario["nome"]) ? " » ".$destino_usuario["nome"] : "");
        $conteudo[] = "$titulo ($destino)";
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["comentario"]) $conteudo[] = "<b>&nbsp;&raquo;  <u>Comentário</u>:</b> ".preg_replace("/[\r\n\t ]+/", " ", $etapa["parametros"]["comentario"]);
        break;
      case 7:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["previsao_inicio"]) $conteudo[] = "<b>&nbsp;&raquo; Início:</b> " . $etapa["parametros"]["previsao_inicio"];
        if ($etapa["parametros"]["previsao_termino"]) $conteudo[] = "<b>&nbsp;&raquo; Término:</b> " . $etapa["parametros"]["previsao_termino"];
        break;
      case 8:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["conteudo"]) $conteudo[] = "<b>&nbsp;&raquo; <u>Conteúdo</u>:</b> ".preg_replace("/[\r\n\t ]+/", " ", $etapa["parametros"]["conteudo"]);
        break;
      case 9:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["arquivo"]) {
          $conteudo[] = '<b>&nbsp;&raquo; <u>Arquivo</u>:</b> <a class="txtAzulEscuro11" href="?m=tarefas&a=abrir_anexo&i=' . $etapa["tarefa_id"] . '&e=' . $etapa["id"] . '&h=' . $etapa["parametros"]["arquivo"]["hash"] . '">' . $etapa["parametros"]["arquivo"]["nome"] . ' (' . $this->formataUnidade($etapa["parametros"]["arquivo"]["tamanho"]) . ')</a>';
        } else {
          $conteudo[] = '<b>&nbsp;&raquo; <u>Arquivo</u>:</b> Erro na publicação!';
        }
        if ($etapa["parametros"]["comentario"]) $conteudo[] = "<b>&nbsp;&raquo;  <u>Comentário</u>:</b> " . preg_replace("/[\r\n\t ]+/"," ",$etapa["parametros"]["comentario"]);
        break;
      case 10:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["comentario"]) $conteudo[] = "<b>&nbsp;&raquo;  <u>Comentário</u>:</b> " . preg_replace("/[\r\n\t ]+/"," ",$etapa["parametros"]["comentario"]);
        break;
      case 12:
        $destino_setor = $etapa["parametros"]["destino_setor"] ? $this->buscaDadosSetor($etapa["parametros"]["destino_setor"]) : null;
        $destino_funcao = $etapa["parametros"]["destino_funcao"] ? $this->buscaDadosFuncao($etapa["parametros"]["destino_funcao"]) : null;
        $destino_usuario = $etapa["parametros"]["destino_usuario"] ? $this->buscaDadosUsuario($etapa["parametros"]["destino_usuario"]) : null;
        $destino = $destino_setor["nome"]." » ".$destino_funcao["nome"].(($destino_usuario["nome"]) ? " » ".$destino_usuario["nome"] : "");
        $conteudo[] = "$titulo ($destino)";
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["comentario"]) $conteudo[] = "<b>&nbsp;&raquo; <u>Comentário</u>:</b> ".preg_replace("/[\r\n\t ]+/", " ", $etapa["parametros"]["comentario"]);
        break;
      case 13:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        if ($etapa["parametros"]["comentario"]) $conteudo[] = "<b>&nbsp;&raquo; <u>Comentário</u>:</b> ".preg_replace("/[\r\n\t ]+/", " ", $etapa["parametros"]["comentario"]);
        break;
      case 14:
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        break;
      case 15;
        $conteudo[] = $titulo;
        $conteudo[] = $responsavel;
        break;
    }
    return @implode("<br>", $conteudo);
  }

  function buscaEtapaTipos() {
    $query = "
      SELECT
        tet_id AS id,
        tet_nome AS nome
      FROM
        tab_tarefas_etapas_tipos
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaEtapaStatus() {
    $query = "
      SELECT
        tes_id AS id,
        tes_nome AS nome
      FROM
        tab_tarefas_etapas_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaEtapaConteudoAndamentos() {
    for ($i = 0; $i <= 100; $i = $i + 5) {
      $andamentos[$i] = "$i%";
    }
    return $andamentos;
  }

  function buscaDestinosPossiveis($setor_id, $funcao_id) {
    $query = "SELECT usu_id AS id FROM rel_usu_orf_ors_exerce WHERE ors_id = $setor_id AND orf_id = $funcao_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $destinos[] = $dados["id"];
    }
    return $destinos;
  }

  function geraEtapasListagem($etapas) {
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $etapas);
    return $listagem->conteudo;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("tarefas.adicionar.inc.php");
      // Dados
      if (strlen($_GET["inicio"]) == 10) {
        $dia = substr($_GET["inicio"], 0, 2);
        $mes = substr($_GET["inicio"], 3, 2);
        $ano = substr($_GET["inicio"], 6, 4);
        if (strtotime("{$ano}-{$mes}-{$dia}") == time()) {
          $minuto = date("i");
          $hora = date("H");
          if ($minuto % 10) {
            $minuto = sprintf("%02s", $minuto - ($minuto % 10) + 10);
            if ($minuto == 60 && $hora < 23) {
              $hora = sprintf("%02s", date("H") + 1);
              $minuto = "00";
            } else {
              $hora = date("H");
            }
          } else {
            $minuto =  sprintf("%02s", $minuto - ($minuto % 10));
            $hora = date("H");
          }
          $_GET["inicio"] .= " (".$hora.":".$minuto.")";
        } else {
          $_GET["inicio"] .= " (08:00)";
        }
      }
      $this->conteudo = str_replace('[prazo_inicio]', ($_GET["inicio"]) ? $_GET["inicio"] : "" , $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace('[botoes]', @implode(' ', $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=adicionar', $this->conteudo);
    } else {
      $query = "INSERT INTO tab_tarefas (tar_abertura, tar_andamento, tas_id, tar_titulo) VALUES (NOW(), 0, 1, '".$_POST["titulo"]."')";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      $_POST["origem"] .= ",".$_SESSION["usuario"];
      // Abertura
      $parametros = $this->extraiEtapaParametros(14, $_POST);
      $this->registraEtapa(14, $id, $parametros);
      // Origem
      $parametros = $this->extraiEtapaParametros(1, $_POST);
      $this->registraEtapa(1, $id, $parametros);
      // Tipo
      if ($_POST["tipo"]) {
        $parametros = $this->extraiEtapaParametros(2, $_POST);
        $this->registraEtapa(2, $id, $parametros);
      }
      // Descrição
      $parametros = $this->extraiEtapaParametros(3, $_POST);
      $this->registraEtapa(3, $id, $parametros);
      // Prazo
      if ($_POST["prazo_inicio"] || $_POST["prazo_termino"]) {
        $parametros = $this->extraiEtapaParametros(4, $_POST);
        $this->registraEtapa(4, $id, $parametros);
      }
      // Encaminhamento
      $parametros = $this->extraiEtapaParametros(6, $_POST);
      $this->registraEtapa(6, $id, $parametros);
      // Auto Convite
      $this->autoConvite($id, $_POST);
      // Atualiza Aguarda Ciência
      $this->atualizaAguardaCiencia($id, $_SESSION["usuario"]);
      header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$id);
    }
  }

  function autoConvite($id, $dados) {
    // Trata Origem
    $origem = $dados["origem"];
    $origem = explode(",", $origem);
    $origem["setor"] = $origem[0] ? $this->buscaDadosSetor(intval($origem[0])) : null;
    $origem["funcao"] = intval($origem[1]);
    $origem["usuario"] = intval($origem[2]);
    // Trata Destino
    $destino = $dados["destino"];
    $destino = explode(",", $destino);
    $destino["setor"] = $destino[0] ? $this->buscaDadosSetor(intval($destino[0])) : null;
    $destino["funcao"] = intval($destino[1]);
    $destino["usuario"] = intval($destino[2]);
    // Se envia para outro setor E Se o outro setor não é pai
    if (($origem["setor"]["id"] != $destino["setor"]["id"]) && ($origem["setor"]["pai"] != $destino["setor"]["id"])) {
      // Se origem nao eh diretor (cria etapa para o diretor do setor de origem)
      if ($origem["funcao"] != 4) {
        $parametros["origem_setor"] = ($origem["setor"]["pai"]) ? $origem["setor"]["pai"] : $origem["setor"]["id"];
        $parametros["origem_funcao"] = 4;
        $parametros["origem_usuario"] = $this->buscaDiretor($parametros["origem_setor"]);
        $this->registraEtapa(13, $id, $parametros);
      }
      // Se destino nao eh diretor (cria etapa para o diretor do setor de destino)
      if ($destino["funcao"] != 4) {
        $parametros["origem_setor"] = ($destino["setor"]["pai"]) ? $destino["setor"]["pai"] : $destino["setor"]["id"];
        $parametros["origem_funcao"] = 4;
        $parametros["origem_usuario"] = $this->buscaDiretor($parametros["origem_setor"]);
        $this->registraEtapa(13, $id, $parametros);
      }
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item) && $this->ehVisivel()) {
      $this->conteudo .= $this->carregaArquivo("tarefas.ver.inc.php");
      $this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[titulo]', $this->dados_item["titulo"], $this->conteudo);
      $this->conteudo = str_replace('[protocolo]', $this->dados_item["protocolo"], $this->conteudo);
      $this->conteudo = str_replace('[setor]', $this->dados_item["setor_nome"], $this->conteudo);
      $this->conteudo = str_replace('[funcao]', $this->dados_item["funcao_nome"], $this->conteudo);
      $this->conteudo = str_replace('[usuario]', $this->dados_item["usuario_nome"], $this->conteudo);
      $this->conteudo = str_replace('[abertura]', $this->dados_item["abertura"], $this->conteudo);
      $this->conteudo = str_replace('[prazo]', $this->dados_item["prazo"], $this->conteudo);
      $this->conteudo = str_replace('[previsao]', $this->dados_item["previsao"], $this->conteudo);
      $this->conteudo = str_replace('[fechamento]', $this->dados_item["fechamento"], $this->conteudo);
      $this->conteudo = str_replace('[andamento]', $this->dados_item["andamento"]."%", $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && $this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"] . '\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["encaminhar"] && $this->ehEncaminhavel()) $botoes[] = '<input type="button" name="encaminhar" class="buttonAzulEscuroBranco11" value="Encaminhar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=encaminhar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["capturar"] && $this->ehCapturavel()) $botoes[] = '<input type="button" name="capturar" class="buttonAzulEscuroBranco11" value="Capturar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=capturar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["tomar_ciencia"] && $this->ehCienciavel()) $botoes[] = '<input type="button" name="tomar_ciencia" class="buttonAzulEscuroBranco11" value="Tomar Ciência" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=tomar_ciencia&i=' . $this->dados_item["id"] . '\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["fechar"] && $this->ehFechavel()) $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=fechar&i=' . $this->dados_item["id"] . '\';">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar () {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item)) {
      if ($this->ehEditavel()) {
        if (empty($_POST)) {
          $this->conteudo .= $this->carregaArquivo("tarefas.editar.inc.php");
          $this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
          $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
          $this->conteudo = str_replace('[titulo]', $this->dados_item["titulo"], $this->conteudo);
          $this->conteudo = str_replace('[protocolo]', $this->dados_item["protocolo"], $this->conteudo);
          $this->conteudo = str_replace('[setor]', $this->dados_item["setor_nome"], $this->conteudo);
          $this->conteudo = str_replace('[funcao]', $this->dados_item["funcao_nome"], $this->conteudo);
          $this->conteudo = str_replace('[usuario]', $this->dados_item["usuario_nome"], $this->conteudo);
          $this->conteudo = str_replace('[abertura]', $this->dados_item["abertura"], $this->conteudo);
          $this->conteudo = str_replace('[prazo]', $this->dados_item["prazo"], $this->conteudo);
          $this->conteudo = str_replace('[previsao]', $this->dados_item["previsao"], $this->conteudo);
          $this->conteudo = str_replace('[fechamento]', $this->dados_item["fechamento"], $this->conteudo);
          $this->conteudo = str_replace('[andamento]', $this->dados_item["andamento"]."%", $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_andamento]', $this->geraSelect($this->buscaEtapaConteudoAndamentos(), $this->dados_item["andamento"]), $this->conteudo);
          $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
          // Botões
          $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
          $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
          $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
          if ($this->permissoes[$this->dados_modulo["nome"]]["encaminhar"] && $this->ehEncaminhavel()) $botoes[] = '<input type="button" name="encaminhar" class="buttonAzulEscuroBranco11" value="Encaminhar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=encaminhar&i=' . $this->dados_item["id"] . '\';">';
          if ($this->permissoes[$this->dados_modulo["nome"]]["convidar"] && $this->ehEncaminhavel()) $botoes[] = '<input type="button" name="convidar" class="buttonAzulEscuroBranco11" value="Convidar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=convidar&i=' . $this->dados_item["id"] . '\';">';
          if ($this->permissoes[$this->dados_modulo["nome"]]["capturar"] && $this->ehCapturavel()) $botoes[] = '<input type="button" name="capturar" class="buttonAzulEscuroBranco11" value="Capturar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=capturar&i=' . $this->dados_item["id"] . '\';">';
          if ($this->permissoes[$this->dados_modulo["nome"]]["tomar_ciencia"] && $this->ehCienciavel()) $botoes[] = '<input type="button" name="tomar_ciencia" class="buttonAzulEscuroBranco11" value="Tomar Ciência" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=tomar_ciencia&i=' . $this->dados_item["id"] . '\';">';
          if ($this->permissoes[$this->dados_modulo["nome"]]["fechar"] && $this->ehFechavel()) $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=fechar&i=' . $this->dados_item["id"] . '\';">';
          $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
          $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"],$this->conteudo);
        } else {
          $_POST["origem"] = $_POST["etapa_origem"].",".$_SESSION["usuario"];
          if ($_FILES["arquivo"]) $_POST["arquivo"] = $_FILES["arquivo"];
          $parametros = $this->extraiEtapaParametros ($_POST["etapa_tipo"], $_POST);
          $this->registraEtapa($_POST["etapa_tipo"], $_GET["i"], $parametros);
          $this->atualizaAguardaCiencia($_GET["i"], $_SESSION["usuario"]);
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
          header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i=".$_GET["i"]);
        }
      } else {
        $this->ver();
      }
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  ##################
  ### Encaminhar ###
  ##################

  function encaminhar() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item) && $this->ehEncaminhavel()) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("tarefas.encaminhar.inc.php");
        $this->conteudo = str_replace('[origem_id]',$this->dados_item["setor_id"].",".$this->dados_item["funcao_id"].",".$this->dados_item["usuario_id"],$this->conteudo);
        $this->conteudo = str_replace('[origem_nome]',$this->dados_item["setor_nome"]." » ".$this->dados_item["funcao_nome"]." » ".$this->dados_item["usuario_nome"],$this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]",@implode(" ",$botoes),$this->conteudo);
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=encaminhar&i=' . $_GET["i"],$this->conteudo);
      } else {
        $_POST["origem"] = $this->dados_item["setor_id"].",".$this->dados_item["funcao_id"].",".$_SESSION["usuario"];
        $parametros = $this->extraiEtapaParametros(6, $_POST);
        $this->registraEtapa(6, $_GET["i"], $parametros);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ################
  ### Convidar ###
  ################

  function convidar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehEncaminhavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("tarefas.convidar.inc.php");
        $this->conteudo = str_replace('[origem_id]', "{$this->dados_item["setor_id"]},{$this->dados_item["funcao_id"]},{$this->dados_item["usuario_id"]}", $this->conteudo);
        $this->conteudo = str_replace('[origem_nome]', "{$this->dados_item["setor_nome"]} » {$this->dados_item["funcao_nome"]} » {$this->dados_item["usuario_nome"]}", $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=convidar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $_POST["origem"] = "{$this->dados_item["setor_id"]},{$this->dados_item["funcao_id"]},{$_SESSION["usuario"]}";
        $parametros = $this->extraiEtapaParametros (12, $_POST);
        $this->registraEtapa(12, $_GET["i"], $parametros);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ################
  ### Capturar ###
  ################

  function capturar() {
    $this->buscaDadosItem($_GET["i"]);
    if (!$this->dados_item && $this->ehCapturavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("tarefas.capturar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=capturar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $_POST["origem"] = "{$this->dados_item["setor_id"]},{$this->dados_item["funcao_id"]},{$_SESSION["usuario"]}";
        $parametros = $this->extraiEtapaParametros (10, $_POST);
        $this->registraEtapa(10, $_GET["i"], $parametros);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  #####################
  ### Tomar Ciência ###
  #####################

  function tomar_ciencia() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item) && $this->ehCienciavel()) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("tarefas.tomar_ciencia.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=tomar_ciencia&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "DELETE FROM rel_tar_usu_aguardaciencia WHERE tar_id = {$_GET["i"]} AND usu_id = {$_SESSION["usuario"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    $this->buscaDadosItem($_GET["i"]);
    if (!empty($this->dados_item) && $this->ehFechavel()) {
      if (empty($_POST["confirmar"])) {
        $this->conteudo .= $this->carregaArquivo("tarefas.fechar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=fechar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_tarefas INNER JOIN tab_tarefas_etapas ON (tab_tarefas.tar_id = tab_tarefas_etapas.tar_id) AND (tab_tarefas_etapas.tet_id = '1') SET tab_tarefas.tar_fechamento = NOW(), tab_tarefas.tas_id = 2, tab_tarefas.ors_id = tab_tarefas_etapas.ors_id, tab_tarefas.orf_id = tab_tarefas_etapas.orf_id, tab_tarefas.usu_id = tab_tarefas_etapas.usu_id WHERE tab_tarefas.tar_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);
        // Fechamento
        $parametros = $this->extraiEtapaParametros(15, $_POST);
        $this->registraEtapa(15, $_GET["i"], $parametros);
        $this->atualizaAguardaCiencia($_GET["i"], $_SESSION["usuario"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ###################
  ### Abrir Anexo ###
  ###################

  function abrir_anexo() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehVisivel()) {
      for ($i = 0; $i < sizeof($this->dados_item["etapas"]); $i++) {
      	if ($this->dados_item["etapas"][$i]["id"] == $_GET["e"]) break;
      }
      if ($this->dados_item["etapas"][$i]["parametros"]["arquivo"]["hash"] == $_GET["h"] && file_exists("tarefas/".$this->dados_item["etapas"][$i]["parametros"]["arquivo"]["hash"])) {
        header("Content-type: {$this->dados_item["etapas"][$i]["parametros"]["arquivo"]["tipo"]};");
        header("Content-Disposition: attachment; filename={$this->dados_item["etapas"][$i]["parametros"]["arquivo"]["nome"]};");
        print $this->carregaArquivo("tarefas/".$this->dados_item["etapas"][$i]["parametros"]["arquivo"]["hash"]);
        exit();
      } else {
        $this->conteudo = '<script type="text/javascript">alert("Arquivo não disponível!");history.back();</script>';
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    if (!$_GET["f"] && !$_GET["p"]) {
      $_GET["f"]["status"] = "1";
    }
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
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
    // Protocolo
    $itens[] = array ("tipo" => "input", "nome" => "protocolo", "descricao" => "Protocolo", "valor" => "", "where" => "(tab_tarefas.tar_id = [valor])");
    // Título
    $itens[] = array ("tipo" => "input", "nome" => "titulo", "descricao" => "Título", "valor" => "", "where" => "(tab_tarefas.tar_titulo LIKE '%[valor]%')");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_tarefas.tas_id = [valor])");
    // Referencia
    $referencias = $this->buscaReferencias();
    foreach ($referencias as $id => $nome) {
      if ($id != ',,,') $select_referencias[] = array ("id" => $id, "nome" => $nome);
    }
    $referencia = @explode(",", $_GET["f"]["referencia"]);
    $referencia_setor = ($referencia[0]) ? $referencia[0] : '%';
    $referencia_funcao = ($referencia[1]) ? $referencia[1] : '%';
    $referencia_usuario = ($referencia[2]) ? $referencia[2] : '%';
    if ($referencia[0]) $sql[] = "tab_tarefas.ors_id = $referencia[0]";
    if ($referencia[1]) $sql[] = "tab_tarefas.orf_id = $referencia[1]";
    if ($referencia[2]) $sql[] = "tab_tarefas.usu_id = $referencia[2]";
    $itens[] = array ("tipo" => "select", "nome" => "referencia", "descricao" => "Referência", "valor" => implode(" AND ", $sql), "where" => $sql, "colspan" => 2);
    // Filtro
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
    $filtro_usuario = $this->geraFiltroUsuario($_SESSION["usuario"]);
    $query = "
      SELECT SQL_CALC_FOUND_ROWS DISTINCT
        tab_tarefas.tar_id AS id,
        responsavel_setor.ors_nome AS responsavel_setor,
        responsavel_setor_pai.ors_nome AS responsavel_setor_pai,
        responsavel_funcao.orf_nome AS responsavel_funcao,
        responsavel_usuario.usu_nome AS responsavel_usuario,
        solicitante_setor.ors_nome AS solicitante_setor,
        solicitante_setor_pai.ors_nome AS solicitante_setor_pai,
        solicitante_funcao.orf_nome AS solicitante_funcao,
        solicitante_usuario.usu_nome AS solicitante_usuario,
        tab_tarefas.tar_titulo AS titulo,
        DATE_FORMAT(tab_tarefas.tar_prazo_inicio, '%d/%m/%Y (%H:%i)') AS prazo_inicio,
        DATE_FORMAT(tab_tarefas.tar_prazo_inicio, '%d/%m/%Y (%H:%i)') AS prazo_termino,
        CONCAT(tab_tarefas.tar_andamento, '%') AS andamento,
        tab_tarefas_tipos.tat_nome AS tipo,
        tab_tarefas_status.tas_nome AS status
      FROM
        tab_tarefas
        LEFT OUTER JOIN tab_usuarios responsavel_usuario ON (tab_tarefas.usu_id = responsavel_usuario.usu_id)
        INNER JOIN tab_organizacao_funcoes responsavel_funcao ON (tab_tarefas.orf_id = responsavel_funcao.orf_id)
        INNER JOIN tab_organizacao_setores responsavel_setor ON (tab_tarefas.ors_id = responsavel_setor.ors_id)
        LEFT OUTER JOIN tab_organizacao_setores responsavel_setor_pai ON (responsavel_setor_pai.ors_id = responsavel_setor.ors_pai)
        INNER JOIN tab_tarefas_etapas etapa_origem ON (tab_tarefas.tar_id = etapa_origem.tar_id) AND (etapa_origem.tet_id = 1)
        INNER JOIN tab_tarefas_etapas ON (tab_tarefas.tar_id = tab_tarefas_etapas.tar_id)
        INNER JOIN tab_usuarios solicitante_usuario ON (etapa_origem.usu_id = solicitante_usuario.usu_id)
        INNER JOIN tab_organizacao_funcoes solicitante_funcao ON (etapa_origem.orf_id = solicitante_funcao.orf_id)
        INNER JOIN tab_organizacao_setores solicitante_setor ON (etapa_origem.ors_id = solicitante_setor.ors_id)
        LEFT OUTER JOIN tab_organizacao_setores solicitante_setor_pai ON (solicitante_setor_pai.ors_id = solicitante_setor.ors_pai)
        INNER JOIN tab_tarefas_tipos ON (tab_tarefas_tipos.tat_id = tab_tarefas.tat_id)
        INNER JOIN tab_tarefas_status ON (tab_tarefas_status.tas_id = tab_tarefas.tas_id)
      WHERE
        (tab_tarefas.usu_id = {$_SESSION["usuario"]} OR tab_tarefas_etapas.usu_id = {$_SESSION["usuario"]}) AND
        $filtro->parametros_sql
      ORDER BY
        tab_tarefas.tar_id ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($item = $this->bancodedados->resultadoArray($query)) != false) {
      // Busca dados
      $tarefa["descricao"] = $this->buscaTarefaDescricao($item["id"]);
      $tarefa["situacao"] = $this->buscaTarefaSituacao($item["id"]);
      // Link
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) $conteudo["link"] = '?m=tarefas&a=ver&i='.$item["id"];
      // Conteúdo
      $conteudo["conteudo"] = "<b>Protocolo:</b> ".sprintf("%08s", $item["id"])."<br>";
      $conteudo["conteudo"] .= "<b>Tipo:</b> {$item["tipo"]}<br>";
      if ($item["titulo"]) $conteudo["conteudo"] .= "<b>Título:</b> {$item["titulo"]}<br>";
      $conteudo["conteudo"] .= "<b>Solicitante:</b> ".(($item["solicitante_setor_pai"]) ? "{$item["responsavel_setor_pai"]} » " : "")."{$item["solicitante_setor"]} » {$item["solicitante_funcao"]} » {$item["solicitante_usuario"]}<br>";
      $conteudo["conteudo"] .= "<b>Responsável:</b> ".(($item["responsavel_setor_pai"]) ? "{$item["responsavel_setor_pai"]} » " : "")."{$item["responsavel_setor"]} » {$item["responsavel_funcao"]} » {$item["responsavel_usuario"]}<br>";
      if ($item["prazo_inicio"] || $item["prazo_termino"]) {
        $conteudo["conteudo"] .= "<b>Prazo</b>";
        if ($item["prazo_inicio"]) $conteudo["conteudo"] .= " <b>Início:</b> ".$item["prazo_inicio"];
        if ($item["prazo_termino"]) $conteudo["conteudo"] .= " <b>Término:</b> ".$item["prazo_termino"];
        $conteudo["conteudo"] .= "<br>";
      }
      if ($tarefa["descricao"]) $conteudo["conteudo"] .= "<b>Descrição:</b> {$tarefa["descricao"]}<br>";
      $conteudo["conteudo"] .= "<b>Andamento:</b> {$item["andamento"]}<br>";
      if ($tarefa["situacao"]) $conteudo["conteudo"] .= "<b>Situacao:</b> {$tarefa["situacao"]}<br>";
      $conteudo["conteudo"] .= "<b>Status:</b> {$item["status"]}<br>";
      // Última Etapa
      $etapas = $this->buscaEtapas($item["id"]);
      $hints = array();
      foreach ($etapas as $etapa) $hints[] = strip_tags($etapa["detalhamento"], '<br>');
      $hint = implode('<hr style=\\\'height: 1; color: #FFFFFF\\\'>', $hints);
      $conteudo["ultima_etapa"] = "<div valign=\"top\" style=\"text-align:left;width:100%;height:100%\" onmouseover=\"hintShow('$hint')\" onmouseout=\"hintHide()\">".$this->buscaUltimaEtapa($item["id"])."</div>";
      $conteudo["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $conteudo;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "conteudo" => "Tarefa", "ultima_etapa" => "Última&nbsp;Etapa");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "70%", "30%"), null, array("right"));
    return $listagem;
  }

  function buscaUltimaEtapa($tarefa_id) {
    $query = "
      SELECT
        tar_id AS tarefa_id,
        tae_id AS id,
        ors_id AS setor,
        orf_id AS funcao,
        usu_id AS usuario,
        DATE_FORMAT(tae_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tae_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tae_parametros AS parametros,
        tet_id AS tipo,
        tes_id AS status
      FROM
        tab_tarefas_etapas
      WHERE
        tar_id = $tarefa_id
      ORDER BY
        tae_id DESC
       LIMIT 0,1";
    $query = $this->bancodedados->query($query);
    $tipos = $this->buscaEtapaTipos();
    $status = $this->buscaEtapaStatus();
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $etapa["id"] = $dados["id"];
      $etapa["tarefa_id"] = $dados["tarefa_id"];
      $etapa["setor"] = $dados["setor"] ? $this->buscaDadosSetor($dados["setor"]) : null;
      $etapa["setor_id"] = $etapa["setor"]["id"];
      $etapa["setor_nome"] = $etapa["setor"]["nome"];
      $etapa["funcao"] = $dados["funcao"] ? $this->buscaDadosFuncao($dados["funcao"]) : null;
      $etapa["funcao_id"] = $etapa["funcao"]["id"];
      $etapa["funcao_nome"] = $etapa["funcao"]["nome"];
      $etapa["usuario"] = $dados["usuario"] ? $this->buscaDadosUsuario($dados["usuario"]) : null;
      $etapa["usuario_id"] = $etapa["usuario"]["id"];
      $etapa["usuario_nome"] = $etapa["usuario"]["nome"];
      $etapa["inicio"] = $dados["inicio"];
      $etapa["termino"] = $dados["termino"];
      $etapa["data_hora"] = ($etapa["inicio"] == $etapa["termino"]) ? $dados["inicio"] : "{$dados["inicio"]} / {$dados["termino"]}";
      $etapa["parametros"] = unserialize($dados["parametros"]);
      $etapa["tipo_id"] = $dados["tipo"];
      $etapa["tipo_nome"] = $tipos[$dados["tipo"]];
      $etapa["status_id"] = $dados["status"];
      $etapa["status_nome"] = $status[$dados["status"]];
    }
    return $this->geraEtapaDetalhamento($etapa);
  }

  function buscaTarefaSolicitante($tarefa_id) {
    return $this->buscaResponsavelTarefaEtapaUltima($tarefa_id, 1);
  }

  function geraFiltroUsuario($usu_id) {
    $query = "
      SELECT
        ors_id,
        orf_id
      FROM
        rel_usu_orf_ors_exerce
      WHERE
        usu_id = $usu_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $filtro[] = "(responsavel_setor.ors_id = {$dados["ors_id"]} AND
                     responsavel_funcao.orf_id <= {$dados["orf_id"]})";
    }
    return @implode(" OR ", $filtro);
  }

  function buscaResponsavelTarefaEtapaUltima($tarefa_id, $etapa_tipo = null) {
    $query = "
      SELECT
        ors_id AS setor,
        orf_id AS funcao,
        usu_id AS usuario
      FROM
        tab_tarefas_etapas
      WHERE
        tar_id = $tarefa_id".($etapa_tipo ? " AND
        tet_id = '$etapa_tipo'" : "")."
      ORDER BY
        tae_termino DESC
      LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $responsavel["setor"] = $dados["setor"] ? $this->buscaDadosSetor($dados["setor"]) : null;
      $responsavel["funcao"] = $dados["funcao"] ? $this->buscaDadosFuncao($dados["funcao"]) : null;
      $responsavel["usuario"] = $dados["usuario"] ? $this->buscaDadosUsuario($dados["usuario"]) : null;
    }
    return $responsavel;
  }

  function buscaTarefaResponsavel($tarefa_id) {
    $query = "
      SELECT
        ors_id AS setor,
        orf_id AS funcao,
        usu_id AS usuario
      FROM
        tab_tarefas
      WHERE
        tar_id = $tarefa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $responsavel["setor"] = $dados["setor"] ? $this->buscaDadosSetor($dados["setor"]) : null;
      $responsavel["funcao"] = $dados["funcao"] ? $this->buscaDadosFuncao($dados["funcao"]) : null;
      $responsavel["usuario"] = $dados["usuario"] ? $this->buscaDadosUsuario($dados["usuario"]) : null;
    }
    return $responsavel;
  }

  function buscaTarefaDescricao($tarefa_id) {
    $query = "
      SELECT
        tae_parametros AS parametros
      FROM
        tab_tarefas_etapas
      WHERE
        tar_id = $tarefa_id AND
        tet_id = 3";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$dados["parametros"] = unserialize($dados["parametros"]);
	    return preg_replace("/[\r\n\t ]+/", " ", $dados["parametros"]["descricao"]);
    }
  }

  function buscaTarefaSituacao($tarefa_id) {
    $query = "
      SELECT
        IF(tar_prazo_termino < CURDATE() AND rel_tar_usu_aguardaciencia.usu_id IS NULL, 'Pendente', IF(tar_prazo_termino < CURDATE() AND rel_tar_usu_aguardaciencia.usu_id IS NOT NULL, 'Pendente, Aguardando Ciência', IF(rel_tar_usu_aguardaciencia.usu_id IS NOT NULL, 'Aguardando Ciência', ''))) AS situacao
      FROM
        tab_tarefas
        LEFT OUTER JOIN rel_tar_usu_aguardaciencia ON (tab_tarefas.tar_id = rel_tar_usu_aguardaciencia.tar_id)
      WHERE
        tar_id = $tarefa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["situacao"];
    }
  }

  ##############
  ### Agenda ###
  ##############

  function agenda() {
    // Busca Filtros
    $referencia = $_GET["referencia"] ? $_GET["referencia"] : ",,{$_SESSION["usuario"]}";
    $formato = $_GET["formato"] ? $_GET["formato"] : "mes";
    $status = $_GET["status"] ? $_GET["status"] : "1";
    // Busca Data
    $dia = $_GET["dia"] ? $_GET["dia"] : ($formato == "mes" ? "01" : date("d"));
    $mes = $_GET["mes"] ? $_GET["mes"] : date("m");
    $ano = $_GET["ano"] ? $_GET["ano"] : date("Y");
    // Conteúdo
    $this->conteudo .= $this->carregaArquivo("tarefas.agenda.inc.php");
    $this->conteudo = str_replace("[listagem_referencia]", $this->geraSelect($this->buscaReferencias(), $referencia), $this->conteudo);
    $this->conteudo = str_replace("[listagem_formato]", $this->geraSelect($this->buscaFormatos(), $formato), $this->conteudo);
    $this->conteudo = str_replace("[listagem_status]", $this->geraSelect($this->buscaStatus(), $status), $this->conteudo);
    $this->conteudo = str_replace("[referencia]", $_GET["referencia"], $this->conteudo);
    $this->conteudo = str_replace("[formato]", $_GET["formato"], $this->conteudo);
    $this->conteudo = str_replace("[status]", $_GET["status"], $this->conteudo);
    $this->conteudo = str_replace("[dia]", $_GET["dia"], $this->conteudo);
    $this->conteudo = str_replace("[mes]", $_GET["mes"], $this->conteudo);
    $this->conteudo = str_replace("[ano]", $_GET["ano"], $this->conteudo);
    $this->conteudo = str_replace("[exibicao]", $_SESSION["exibicao"] ? $_SESSION["exibicao"] : 1, $this->conteudo);
    $this->conteudo = str_replace("[tarefas_agenda]", stripslashes($this->geraCalendario($referencia, $formato, $status, $dia, $mes, $ano)), $this->conteudo);
    $this->conteudo = str_replace("[tarefas_aguardando_ciencia]", $this->geraListagemTarefasAguardandoCiencia($this->buscaTarefasAguardandoCiencia($_SESSION["usuario"])), $this->conteudo);
    $this->conteudo = str_replace("[tarefas_sem_prazo]", $this->geraListagemTarefasSemPrazo($this->buscaTarefasSemPrazo($referencia)), $this->conteudo);
  }

  function buscaTarefasCalendario($dia, $mes, $ano, $referencia, $formato, $status = null) {
  	// Referência
  	$referencia = @explode(",", $referencia);
  	$referencia_setor = ($referencia[0]) ? $referencia[0] : '%';
  	$referencia_funcao = ($referencia[1]) ? $referencia[1] : '%';
  	$referencia_usuario = ($referencia[2]) ? $referencia[2] : '%';
  	// Formato e Data
  	switch ($formato) {
  		case ("mes") : {
  			$periodo_abertura = "tar_abertura LIKE '$ano-$mes%'";
  			$periodo_prazo_inicio = "tar_prazo_inicio LIKE '$ano-$mes%'";
  			$periodo_prazo_termino = "tar_prazo_termino LIKE '$ano-$mes%'";
  			$periodo_fechamento = "tar_fechamento LIKE '$ano-$mes%'";
  			break;
  		}
  		case ("semana") : {
  			$periodo_abertura = "WEEK(tar_abertura) LIKE WEEK('$ano-$mes-$dia')";
  			$periodo_prazo_inicio = "WEEK(tar_prazo_inicio) LIKE WEEK('$ano-$mes-$dia')";
  			$periodo_prazo_termino = "WEEK(tar_prazo_termino) LIKE WEEK('$ano-$mes-$dia')";
  			$periodo_fechamento = "WEEK(tar_fechamento) LIKE WEEK('$ano-$mes-$dia')";
  			break;
  		}
  		case ("dia") : {
  			$periodo_abertura = "tar_abertura LIKE '$ano-$mes-$dia%'";
  			$periodo_prazo_inicio = "tar_prazo_inicio LIKE '$ano-$mes-$dia%'";
  			$periodo_prazo_termino = "tar_prazo_termino LIKE '$ano-$mes-$dia%'";
  			$periodo_fechamento = "tar_fechamento LIKE '$ano-$mes-$dia%'";
  			break;
  		}
  	}
  	$query = "
  		SELECT DISTINCT
  		  tab_tarefas.tar_id AS id,
  		  tar_titulo AS titulo,
  		  IF ($periodo_abertura, 1, 0) AS abertura_ok,
  		  DATE_FORMAT(tar_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
  		  DATE_FORMAT(tar_abertura, '%d') AS calendario_abertura_dia,
  		  DATE_FORMAT(tar_abertura, '%H:00') AS calendario_abertura_horario,
  		  IF ($periodo_prazo_inicio, 1, 0) AS prazo_inicio_ok,
  		  DATE_FORMAT(tar_prazo_inicio,'%d/%m/%Y (%H:%i)') AS prazo_inicio,
  		  DATE_FORMAT(tar_prazo_inicio,'%d') AS calendario_prazo_inicio_dia,
  		  DATE_FORMAT(tar_prazo_inicio,'%H:00') AS calendario_prazo_inicio_horario,
  		  IF ($periodo_prazo_termino, 1, 0) AS prazo_termino_ok,
  		  DATE_FORMAT(tar_prazo_termino, '%d/%m/%Y (%H:%i)') AS prazo_termino,
  		  DATE_FORMAT(tar_prazo_termino, '%d') AS calendario_prazo_termino_dia,
  		  DATE_FORMAT(tar_prazo_termino, '%H:00') AS calendario_prazo_termino_horario,
  		  IF ($periodo_fechamento, 1, 0) AS fechamento_ok,
  		  DATE_FORMAT(tar_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
  		  DATE_FORMAT(tar_fechamento, '%d') AS calendario_fechamento_dia,
  		  DATE_FORMAT(tar_fechamento, '%H:00') AS calendario_fechamento_horario,
  		  CONCAT(tar_andamento, '%') AS andamento,
  		  tat_id AS tipo,
  		  tas_id AS status
  		FROM
  		  tab_tarefas
  		  INNER JOIN tab_tarefas_etapas ON (tab_tarefas.tar_id = tab_tarefas_etapas.tar_id)
  		WHERE".($referencia[0] ? "
  		  (tab_tarefas.ors_id = $referencia[0]) AND" : "").($referencia[1] ? "
  		  (tab_tarefas.orf_id = $referencia[1]) AND" : "").($referencia[2] ? "
  		  (tab_tarefas.usu_id = $referencia[2]) AND" : "").($status ? "
  		  (tab_tarefas.tas_id = $status) AND" : "")."
  		  (tab_tarefas_etapas.usu_id = {$_SESSION["usuario"]} OR tab_tarefas.usu_id = {$_SESSION["usuario"]}) AND
  		  ($periodo_abertura OR $periodo_prazo_inicio OR $periodo_prazo_termino OR $periodo_fechamento)";
  	$query = $this->bancodedados->query($query);
  	while (($tarefa = $this->bancodedados->resultadoArray($query)) != false) {
  		$tarefa_tratada["id"] = $tarefa["id"];
      $tarefa_tratada["protocolo"] = sprintf("%08s", $tarefa["id"]);
      $tarefa_tratada["titulo"] = $tarefa["titulo"];

  		$tarefa_tratada["abertura_ok"] = $tarefa["abertura_ok"];
  		$tarefa_tratada["abertura"] = $tarefa["abertura"];
  		$tarefa_tratada["calendario_abertura_dia"] = $tarefa["calendario_abertura_dia"];
  		$tarefa_tratada["calendario_abertura_horario"] = $tarefa["calendario_abertura_horario"];

  		$tarefa_tratada["prazo_inicio_ok"] = $tarefa["prazo_inicio_ok"];
  		$tarefa_tratada["prazo_inicio"] = $tarefa["prazo_inicio"];
  		$tarefa_tratada["calendario_prazo_inicio_dia"] = $tarefa["calendario_prazo_inicio_dia"];
  		$tarefa_tratada["calendario_prazo_inicio_horario"] = $tarefa["calendario_prazo_inicio_horario"];

  		$tarefa_tratada["prazo_termino_ok"] = $tarefa["prazo_termino_ok"];
  		$tarefa_tratada["prazo_termino"] = $tarefa["prazo_termino"];
  		$tarefa_tratada["calendario_prazo_termino_dia"] = $tarefa["calendario_prazo_termino_dia"];
  		$tarefa_tratada["calendario_prazo_termino_horario"] = $tarefa["calendario_prazo_termino_horario"];

  		$tarefa_tratada["fechamento_ok"] = $tarefa["fechamento_ok"];
  		$tarefa_tratada["fechamento"] = $tarefa["fechamento"];
  		$tarefa_tratada["calendario_fechamento_dia"] = $tarefa["calendario_fechamento_dia"];
  		$tarefa_tratada["calendario_fechamento_horario"] = $tarefa["calendario_fechamento_horario"];

  		$tarefa_tratada["andamento"] = $tarefa["andamento"];
  		$tarefa_tratada["solicitante"] = $this->buscaTarefaSolicitante($tarefa["id"]);
  		$tarefa_tratada["responsavel"] = $this->buscaTarefaResponsavel($tarefa["id"]);
  		$tarefa_tratada["descricao"] = $this->buscaTarefaDescricao($tarefa["id"]);
  		$tarefa_tratada["tipo"] = $this->buscaDadosTipo($tarefa["tipo"]);
  		$tarefa_tratada["status"] = $this->buscaDadosStatus($tarefa["status"]);
  		$tarefas[] = $tarefa_tratada;
  	}
  	return $tarefas;
  }

  function geraTarefasCalendarioEventos($tarefas, $formato) {
    if ($tarefas) {
    	foreach ($tarefas as $tarefa) {
        if ($tarefa["protocolo"]) $tarefa["conteudo"][] = "<b> » Protocolo:</b> {$tarefa["protocolo"]}";
        if ($tarefa["tipo"]) $tarefa["conteudo"][] = "<b> » Tipo:</b> {$tarefa["tipo"]["nome"]}";
        if ($tarefa["titulo"]) $tarefa["conteudo"][] = "<b> » Título:</b> {$tarefa["titulo"]}";
        if ($tarefa["responsavel"]) $tarefa["conteudo"][] = "<b> » Responsável:</b> ".(($tarefa["responsavel"]["usuario"]["nome"]) ? $tarefa["responsavel"]["usuario"]["nome"] : "{$tarefa["responsavel"]["setor"]["nome"]} » {$tarefa["responsavel"]["funcao"]["nome"]}");
        $tarefa["conteudo"] = @implode("<br>", $tarefa["conteudo"]);
        if ($tarefa["abertura_ok"]) $eventos[$tarefa["calendario_abertura_dia"]][($formato=="mes") ? "00:00" : $tarefa["calendario_abertura_horario"]][] = array("titulo" => ($formato == "mes") ? "Abertura" : ($formato == "semana" ? "<b>Abertura</b> ({$tarefa["titulo"]})" : "<b>Abertura &raquo; Título:</b> {$tarefa["titulo"]} <b>&raquo; Responsável:</b> {$tarefa["responsavel"]["usuario"]["nome"]}"), "link" => "?m=tarefas&a=ver&i={$tarefa["id"]}", "conteudo" => $tarefa["conteudo"], "dados" => $tarefa);
        if ($tarefa["prazo_inicio_ok"]) $eventos[$tarefa["calendario_prazo_inicio_dia"]][($formato=="mes") ? "00:00" : $tarefa["calendario_prazo_inicio_horario"]][] = array("titulo" => ($formato == "mes") ? "Início" : ($formato == "semana" ? "<b>Início</b> ({$tarefa["titulo"]})" : "<b>Início &raquo; Título:</b> {$tarefa["titulo"]} <b>&raquo; Responsável:</b> {$tarefa["responsavel"]["usuario"]["nome"]}"), "link" => "?m=tarefas&a=ver&i={$tarefa["id"]}", "conteudo" => $tarefa["conteudo"], "dados" => $tarefa);
        if ($tarefa["prazo_termino_ok"]) $eventos[$tarefa["calendario_prazo_termino_dia"]][($formato=="mes") ? "00:00" : $tarefa["calendario_prazo_termino_horario"]][] = array("titulo" => ($formato == "mes") ? "Término" : ($formato == "semana" ? "<b>Término</b> ({$tarefa["titulo"]})" : "<b>Término &raquo; Título:</b> {$tarefa["titulo"]} <b>&raquo; Responsável:</b> {$tarefa["responsavel"]["usuario"]["nome"]}"), "link" => "?m=tarefas&a=ver&i={$tarefa["id"]}", "conteudo" => $tarefa["conteudo"], "dados" => $tarefa);
        if ($tarefa["fechamento_ok"]) $eventos[$tarefa["calendario_fechamento_dia"]][($formato=="mes") ? "00:00" : $tarefa["calendario_fechamento_horario"]][] = array("titulo" => ($formato == "mes") ? "Fechamento" : ($formato == "semana" ? "<b>Fechamento</b> ({$tarefa["titulo"]})" : "<b>Fechamento &raquo; Título:</b> {$tarefa["titulo"]} <b>&raquo; Responsável:</b> {$tarefa["responsavel"]["usuario"]["nome"]}"), "link" => "?m=tarefas&a=ver&i={$tarefa["id"]}", "conteudo" => $tarefa["conteudo"], "dados" => $tarefa);
      }
    }
    return $eventos;
  }

  function geraCalendario($referencia, $formato, $status, $dia, $mes, $ano) {
    $calendario = controle::instanciaObjeto("tarefas.calendario", $dia, $mes, $ano);
    $tarefas_calendario = $this->buscaTarefasCalendario($dia, $mes, $ano, $referencia, $formato, $status);
    $eventos = $this->geraTarefasCalendarioEventos($tarefas_calendario, $formato);
    $calendario->setEventosEmLote($eventos);
    switch ($formato) {
      case ("mes") :
        $calendario->setLinkOnClick(addslashes("buscarTarefaListagem('[dia_atual]','[mes]','[ano]','{$_GET["referencia"]}', 'dia', '{$_GET["status"]}', event)"));
        $calendario->setLinkMesAnterior(addslashes("buscarTarefaListagem('[mes_anterior_dia]', '[mes_anterior_mes]', '[mes_anterior_ano]', '{$_GET["referencia"]}', 'mes', '{$_GET["status"]}')"));
        $calendario->setLinkMesSeguinte(addslashes("buscarTarefaListagem('[mes_seguinte_dia]', '[mes_seguinte_mes]', '[mes_seguinte_ano]', '{$_GET["referencia"]}', 'mes', '{$_GET["status"]}')"));
        $calendario->setAniversariantes($this->buscaAniversariantes($mes));
        $tarefas_agenda = $calendario->geraCalendarioMes();
        break;
      case ("semana") :
        $calendario->setLinkOnClick(addslashes("buscarTarefaListagem('[dia_atual]', '[mes]', '[ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}', event)"));
        $calendario->setLinkMesAnterior(addslashes("buscarTarefaListagem('[mes_anterior_dia]', '[mes_anterior_mes]', '[mes_anterior_ano]', '{$_GET["referencia"]}', 'semana', '{$_GET["status"]}')"));
        $calendario->setLinkSemanaAnterior(addslashes("buscarTarefaListagem('[semana_anterior_dia]', '[semana_anterior_mes]', '[semana_anterior_ano]', '{$_GET["referencia"]}', 'semana', '{$_GET["status"]}')"));
        $calendario->setLinkSemanaSeguinte(addslashes("buscarTarefaListagem('[semana_seguinte_dia]', '[semana_seguinte_mes]', '[semana_seguinte_ano]', '{$_GET["referencia"]}', 'semana', '{$_GET["status"]}')"));
        $calendario->setLinkMesSeguinte(addslashes("buscarTarefaListagem('[mes_seguinte_dia]', '[mes_seguinte_mes]', '[mes_seguinte_ano]', '{$_GET["referencia"]}', 'semana', '{$_GET["status"]}')"));
        $calendario->setAniversariantes($this->buscaAniversariantes($mes));
        $tarefas_agenda = $calendario->geraCalendarioSemana();
        break;
      case ("dia") :
        $calendario->setLinkMesAnterior(addslashes("buscarTarefaListagem('[mes_anterior_dia]', '[mes_anterior_mes]', '[mes_anterior_ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}')"));
        $calendario->setLinkSemanaAnterior(addslashes("buscarTarefaListagem('[semana_anterior_dia]', '[semana_anterior_mes]', '[semana_anterior_ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}')"));
        $calendario->setLinkDiaAnterior(addslashes("buscarTarefaListagem('[dia_anterior_dia]', '[dia_anterior_mes]', '[dia_anterior_ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}')"));
        $calendario->setLinkDiaSeguinte(addslashes("buscarTarefaListagem('[dia_seguinte_dia]', '[dia_seguinte_mes]', '[dia_seguinte_ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}')"));
        $calendario->setLinkSemanaSeguinte(addslashes("buscarTarefaListagem('[semana_seguinte_dia]', '[semana_seguinte_mes]', '[semana_seguinte_ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}')"));
        $calendario->setLinkMesSeguinte(addslashes("buscarTarefaListagem('[mes_seguinte_dia]', '[mes_seguinte_mes]', '[mes_seguinte_ano]', '{$_GET["referencia"]}', 'dia', '{$_GET["status"]}')"));
        $tarefas_agenda = $calendario->geraCalendarioDia();
        break;
    }
    return preg_replace("/[\r\n\t ]+/", " ", $tarefas_agenda);
  }

  function buscaTarefasAguardandoCiencia($usuario_id) {
  	$query = "
  		SELECT
  		  tab_tarefas.tar_id AS id,
  		  DATE_FORMAT(tab_tarefas.tar_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
  		  DATE_FORMAT(tab_tarefas.tar_prazo_inicio, '%d/%m/%Y (%H:%i)') AS prazo_inicio,
  		  DATE_FORMAT(tab_tarefas.tar_prazo_termino, '%d/%m/%Y (%H:%i)') AS prazo_termino,
  		  DATE_FORMAT(tab_tarefas.tar_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
  		  CONCAT(tab_tarefas.tar_andamento, '%') AS andamento,
  		  tab_tarefas.tat_id AS tipo,
  		  tab_tarefas.tas_id AS status
  		FROM
  		  rel_tar_usu_aguardaciencia
  		  INNER JOIN tab_tarefas ON (rel_tar_usu_aguardaciencia.tar_id = tab_tarefas.tar_id)
  		WHERE
  		  rel_tar_usu_aguardaciencia.usu_id = $usuario_id";
  	$query = $this->bancodedados->query($query);
  	while (($tarefa = $this->bancodedados->resultadoArray($query)) != false) {
  		$tarefa_tratada["id"] = $tarefa["id"];
  		$tarefa["solicitante"] = $this->buscaTarefaSolicitante($tarefa["id"]);
  		$tarefa["responsavel"] = $this->buscaTarefaResponsavel($tarefa["id"]);
  		$tarefa["descricao"] = $this->buscaTarefaDescricao($tarefa["id"]);
  		$tarefa["tipo"] = $this->buscaDadosTipo($tarefa["tipo"]);
  		$tarefa["status"] = $this->buscaDadosStatus($tarefa["status"]);
  		if ($tarefa["solicitante"]) $tarefa["conteudo"][] = "<b>Solicitante:</b> {$tarefa["solicitante"]["setor"]["nome"]} » {$tarefa["solicitante"]["funcao"]["nome"]} » {$tarefa["solicitante"]["usuario"]["nome"]}";
      if ($tarefa["responsavel"]) $tarefa["conteudo"][] = "<b>Responsável:</b> {$tarefa["responsavel"]["setor"]["nome"]} » {$tarefa["responsavel"]["funcao"]["nome"]} » {$tarefa["responsavel"]["usuario"]["nome"]}";
      if ($tarefa["abertura"]) $tarefa["conteudo_abertura_fechamento"][] = "<b>Abertura:</b> {$tarefa["abertura"]}";
      if ($tarefa["fechamento"]) $tarefa["conteudo_abertura_fechamento"][] = "<b>Fechamento:</b> {$tarefa["fechamento"]}";
      if ($tarefa["conteudo_abertura_fechamento"]) $tarefa["conteudo"][] = @implode(" ", $tarefa["conteudo_abertura_fechamento"]);
      if ($tarefa["prazo_inicio"]) $tarefa["conteudo_prazo"][] = "<b>Prazo Início:</b> {$tarefa["prazo_inicio"]}";
      if ($tarefa["prazo_termino"]) $tarefa["conteudo_prazo"][] = "<b>Prazo Término:</b> {$tarefa["prazo_termino"]}";
      if ($tarefa["conteudo_prazo"]) $tarefa["conteudo"][] = @implode(" ", $tarefa["conteudo_prazo"]);
      if ($tarefa["descricao"]) $tarefa["conteudo"][] = "<b>Descrição:</b> {$tarefa["descricao"]}";
      if ($tarefa["andamento"]) $tarefa["conteudo_andamento_tipo_status"][] = "<b>Andamento:</b> {$tarefa["andamento"]}";
      if ($tarefa["tipo"]) $tarefa["conteudo_andamento_tipo_status"][] = "<b>Tipo:</b> {$tarefa["tipo"]["nome"]}";
      if ($tarefa["status"]) $tarefa["conteudo_andamento_tipo_status"][] = "<b>Status:</b> {$tarefa["status"]["nome"]}";
      if ($tarefa["conteudo_andamento_tipo_status"]) $tarefa["conteudo"][] = @implode(" ", $tarefa["conteudo_andamento_tipo_status"]);
      $tarefa_tratada["conteudo"] = @implode("<br>", $tarefa["conteudo"]);
      if ($this->permissoes["tarefas"]["ver"]) $tarefa_tratada["link"] = "?m=tarefas&a=ver&i={$tarefa["id"]}";
  		$tarefas[] = $tarefa_tratada;
  	}
  	return $tarefas;
  }

  function geraListagemTarefasAguardandoCiencia($dados) {
    $listagem = controle::instanciaObjeto("listagem", array("conteudo"=> "Conteúdo"), $dados);
    return preg_replace("/[\r\n\t ]+/", " ", $listagem->conteudo);
  }

  function buscaTarefasSemPrazo($referencia) {
  	// Referência
  	$referencia = @explode(",", $referencia);
  	$query = "
  		SELECT DISTINCT
  		  tab_tarefas.tar_id AS id,
  		  tab_tarefas.tar_titulo AS titulo,
  		  DATE_FORMAT(tab_tarefas.tar_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
  		  CONCAT(tab_tarefas.tar_andamento, '%') AS andamento,
  		  tab_tarefas.tat_id AS tipo
  		FROM
  		  tab_tarefas
  		  INNER JOIN tab_tarefas_etapas ON (tab_tarefas.tar_id = tab_tarefas_etapas.tar_id)
  		WHERE".($referencia[0] ? "
  		  tab_tarefas.ors_id = $referencia[0] AND" : "").($referencia[1] ? "
  		  tab_tarefas.orf_id = $referencia[1] AND" : "").($referencia[2] ? "
  		  tab_tarefas.usu_id = $referencia[2] AND" : "")."
  		  tab_tarefas_etapas.usu_id = {$_SESSION["usuario"]} AND
  		  tar_prazo_inicio IS NULL AND
  		  tar_prazo_termino IS NULL AND
  			tas_id = 1";
  	$query = $this->bancodedados->query($query);
  	while (($tarefa = $this->bancodedados->resultadoArray($query)) != false) {
  		$tarefa_tratada["id"] = $tarefa["id"];
  		$tarefa["solicitante"] = $this->buscaTarefaSolicitante($tarefa["id"]);
  		$tarefa["responsavel"] = $this->buscaTarefaResponsavel($tarefa["id"]);
  		$tarefa["descricao"] = $this->buscaTarefaDescricao($tarefa["id"]);
  		$tarefa["tipo"] = $this->buscaDadosTipo($tarefa["tipo"]);
      if ($tarefa["tipo"]) $tarefa["conteudo"][] = "<b>Tipo:</b> {$tarefa["tipo"]["nome"]}";
  		if ($tarefa["titulo"]) $tarefa["conteudo"][] = "<b>Título:</b> {$tarefa["titulo"]}";
  		if ($tarefa["solicitante"]) $tarefa["conteudo"][] = "<b>Solicitante:</b> {$tarefa["solicitante"]["setor"]["nome"]} » {$tarefa["solicitante"]["funcao"]["nome"]} » {$tarefa["solicitante"]["usuario"]["nome"]}";
      if ($tarefa["responsavel"]) $tarefa["conteudo"][] = "<b>Responsável:</b> {$tarefa["responsavel"]["setor"]["nome"]} » {$tarefa["responsavel"]["funcao"]["nome"]} » {$tarefa["responsavel"]["usuario"]["nome"]}";
      if ($tarefa["descricao"]) $tarefa["conteudo"][] = "<b>Descrição:</b> {$tarefa["descricao"]}";
      if ($tarefa["abertura"]) $tarefa["conteudo"][] = "<b>Abertura:</b> {$tarefa["abertura"]}";
      if ($tarefa["andamento"]) $tarefa["conteudo"][] = "<b>Andamento:</b> {$tarefa["andamento"]}";
      $tarefa_tratada["conteudo"] = @implode("<br>", $tarefa["conteudo"]);
      if ($this->permissoes["tarefas"]["ver"]) $tarefa_tratada["link"] = "?m=tarefas&a=ver&i={$tarefa["id"]}";
  		$tarefas[] = $tarefa_tratada;
  	}
  	return $tarefas;
  }

  function geraListagemTarefasSemPrazo($dados) {
    $listagem = controle::instanciaObjeto("listagem", array("conteudo"=> "Conteúdo"), $dados);
    return preg_replace("/[\r\n\t ]+/", " ", $listagem->conteudo);
  }

  function buscaEtapaOrigens($tarefa_id, $usuario_id) {
    $query = "
    	SELECT DISTINCT
    		rel_usu_orf_ors_exerce.ors_id AS setor,
    		rel_usu_orf_ors_exerce.orf_id AS funcao
    	FROM
        tab_tarefas
        INNER JOIN tab_tarefas_etapas ON (tab_tarefas.tar_id = tab_tarefas_etapas.tar_id)
        INNER JOIN rel_usu_orf_ors_exerce ON ((rel_usu_orf_ors_exerce.usu_id = tab_tarefas_etapas.usu_id AND rel_usu_orf_ors_exerce.orf_id = tab_tarefas_etapas.orf_id AND rel_usu_orf_ors_exerce.ors_id = tab_tarefas_etapas.ors_id) OR (rel_usu_orf_ors_exerce.usu_id = tab_tarefas.usu_id AND rel_usu_orf_ors_exerce.orf_id = tab_tarefas.orf_id AND rel_usu_orf_ors_exerce.ors_id = tab_tarefas.ors_id))
    	WHERE
    		tab_tarefas_etapas.tar_id = $tarefa_id AND
    		rel_usu_orf_ors_exerce.usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$origens[] = $this->buscaDadosOrigem($dados["setor"], $dados["funcao"]);
    }
    return $this->ordena_multiarray($origens, array("nome", "id"));
  }

  function buscaReferencias() {
    $usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
    $referencias_origens = $this->buscaOrigens($usuario["id"]);
    $referencias_destinos[] = array("id" => ",,{$usuario["id"]}", "nome" => " » {$usuario["nome"]}");
    for ($i = 0; $i < sizeof($referencias_origens); $i++) {
      $origem = explode(",", $referencias_origens[$i]["id"]);
      $referencias_destinos[] = array("id" => $referencias_origens[$i]["id"].",".$usuario["id"], "nome" => $referencias_origens[$i]["nome"]." » ".$usuario["nome"]);
      $destinos_abaixo = $this->buscaDestinosAbaixo($origem[0], $origem[1]);
      if ($destinos_abaixo) {
        $referencias_destinos = @funcoes::mescla_arrays($destinos_abaixo, $referencias_destinos);
      }
    }
    $referencias = $this->ordena_multiarray($referencias_destinos, array("nome"));
    $lista[",,,"] = "";
    foreach ($referencias as $referencia) {
      $lista[$referencia["id"]] = $referencia["nome"];
    }
    return $lista;
  }

  function buscaOrigens($usuario_id) {
    $query = "
      SELECT DISTINCT
        ors_id AS setor,
        orf_id AS funcao
      FROM
        rel_usu_orf_ors_exerce
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[] = $this->buscaDadosOrigem($dados["setor"], $dados["funcao"]);
    }
    return @$this->ordena_multiarray($origens, array("nome", "id"));
  }

  function buscaDadosOrigem($setor, $funcao) {
    $setor = $setor ? $this->buscaDadosSetor($setor) : null;
    $funcao = $funcao ? $this->buscaDadosFuncao($funcao) : null;
    return array("id" => "{$setor["id"]},{$funcao["id"]}", "nome" => "{$setor["nome"]} ({$funcao["nome"]})");
  }

  function buscaDestinos($origem) {
    $origem = explode(",", $origem);
    $setor = $origem[0];
    $funcao = $origem[1];
    $usuario = $origem[2];
    $destinos_acima = $this->buscaDestinosAcima($setor, $funcao);
    $destinos_atual = array($this->buscaDadosDestino($setor, $funcao, $usuario, "Instância Atual"));
    $destinos_abaixo = $this->buscaDestinosAbaixo($setor, $funcao);
    if ($destinos_abaixo) {
      $destinos_abaixo = @$this->ordena_multiarray($destinos_abaixo, array("nome"));
    }
    return @funcoes::mescla_arrays($destinos_acima, $destinos_atual, $destinos_abaixo);
  }

  function buscaDestinosAcima($setor, $funcao, $grupo = "Instâncias Superiores") {
    $setor = $setor ? $this->buscaDadosSetor($setor) : null;
    $funcao = $funcao ? $this->buscaDadosFuncao($funcao) : null;
    $destinos = array();
    switch ($funcao["id"]) {
      case (4): { // Diretor
        if ($setor["pai"]) { // É Setor Filho
          if ($this->ehDestinoDisponivel($setor["pai"], 4)) {
            $destinos[] = $this->buscaDadosDestino($setor["pai"], 4, "", $grupo);
          }
          $destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAcima($setor["pai"], 4, $grupo));
        } else { // Não é Setor Filho
          $setores = $this->buscaSetores();
          while (($setor_atual = current($setores)) != false) {
            if ($setor_atual["id"] != $setor["id"]) {
              if (!$setor_atual["pai"]) {
                if ($this->ehDestinoDisponivel($setor_atual["id"], 4)) {
                  $destinos[] = $this->buscaDadosDestino($setor_atual["id"], 4, "", $grupo);
                }
                $destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAbaixo($setor_atual["id"], 4, $grupo));
              }
            }
            next($setores);
          }
        }
        break;
      }
      default: { // Funcionário, Supervisor e Gerente
        if ($this->ehDestinoDisponivel($setor["id"], $funcao["id"] + 1)) {
          $destinos[] = $this->buscaDadosDestino($setor["id"], $funcao["id"] + 1, "", $grupo);
          if (!$this->ehDestinoDisponivel($setor["id"], $funcao["id"] + 2) && !$this->ehDestinoDisponivel($setor["pai"], $funcao["id"] + 2) &&
              !$this->ehDestinoDisponivel($setor["id"], $funcao["id"] + 3) && !$this->ehDestinoDisponivel($setor["pai"], $funcao["id"] + 3)) {
          	$destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAcima($setor["id"], $funcao["id"] + 1, $grupo));
          }
        } else {
  	      if ($this->ehDestinoDisponivel($setor["pai"], $funcao["id"] + 1)) {
          	$destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAcima($setor["pai"], $funcao["id"], $grupo));
        	} else {
          	$destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAcima($setor["id"], $funcao["id"] + 1, $grupo));
        	}
        }
        break;
      }
      break;
    }
    return $destinos;
  }

  function buscaDestinosAbaixo($setor, $funcao, $grupo = "Instâncias Inferiores") {
    $setor = $setor ? $this->buscaDadosSetor($setor) : null;
    $funcao = $funcao ? $this->buscaDadosFuncao($funcao) : null;
    $destinos = array();
    switch ($funcao["id"]) {
      case (1): { // Funcionário
        $setores = $this->buscaSetores();
        while (($setor_atual = current($setores)) != false) {
          if ($setor_atual["pai"] == $setor["id"]) {
            if ($this->ehDestinoDisponivel($setor_atual["id"], 4)) {
              $destinos[] = $this->buscaDadosDestino($setor_atual["id"], 4, "", $grupo);
            } else {
              $destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAbaixo($setor_atual["id"], 4, $grupo));
            }
          }
          next($setores);
        }
        break;
      }
      default: { // Diretor
        if ($this->ehDestinoDisponivel($setor["id"], $funcao["id"] - 1)) {
          $destinos[] = $this->buscaDadosDestino($setor["id"], $funcao["id"] - 1, "", $grupo, $grupo == "Instâncias Superiores");
          $usuarios = $this->buscaUsuarios(1);
          while (($usuario_atual = current($usuarios)) != false) {
            if ($this->ehDestinoDisponivel($setor["id"], $funcao["id"] - 1, $usuario_atual["id"]) && !$this->ehDestinoDisponivel($setor["pai_acima"], $funcao["id"] - 1)) {
              $destinos_usuarios[] = $this->buscaDadosDestino($setor["id"], $funcao["id"] - 1, $usuario_atual["id"], $grupo, $grupo == "Instâncias Superiores");
            }
            next($usuarios);
          }
          if (count($destinos_usuarios) > 1) {
            $destinos = funcoes::mescla_arrays($destinos, $destinos_usuarios);
          } else {
            $destinos = $destinos_usuarios;
          }
        } else {
          $destinos = funcoes::mescla_arrays($destinos, $this->buscaDestinosAbaixo($setor["id"], $funcao["id"] - 1, $grupo));
        }
        break;
      }
      break;
    }
    return $destinos;
  }

  function buscaUsuarios($status_id = null) {
    $query = "
      SELECT
        usu_id AS id,
        usu_nome AS nome
      FROM
        tab_usuarios".($status_id ? "
      WHERE
        uss_id = $status_id" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $usuarios[] = $dados;
    }
    return $usuarios;
  }

  function ehDestinoDisponivel($setor_id, $funcao_id, $usuario_id = null) {
  	if ($setor_id && $funcao_id) {
	    $query = "
	      SELECT
	        usu_id AS usuario
	      FROM
	        rel_usu_orf_ors_exerce
	      WHERE
	        ors_id = $setor_id AND
	        orf_id = $funcao_id".($usuario_id ? " AND
	        usu_id = $usuario_id" : "");
	    $query = $this->bancodedados->query($query);
	    return $this->bancodedados->linhas($query) > 0;
  	}
  	return false;
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
    	$array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[$chave."_".key($array)] = key($array);
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

  function buscaDadosDestino($setor, $funcao, $usuario = "", $grupo = "", $sub_instancia = false) {
    $setor = $setor ? $this->buscaDadosSetor($setor) : null;
    $funcao = $funcao ? $this->buscaDadosFuncao($funcao) : null;
    if ($usuario) {
      $usuario = $this->buscaDadosUsuario($usuario);
      $usuario_nome = " » {$usuario["nome"]}";
    }
    $id = array();
    if ($setor) {
    	$id[] = $setor["id"];
    }
    if ($funcao) {
    	$id[] = $funcao["id"];
    }
    if ($usuario) {
    	$id[] = $usuario["id"];
    }
    return array("id" => implode(", ", $id), "nome" => ($sub_instancia ? "» " : "")."{$setor["nome"]} ({$funcao["nome"]})$usuario_nome", "grupo" => $grupo);
  }

  function buscaFormatos() {
    $formatos["dia"] = "Diário";
    $formatos["semana"] = "Semanal";
    $formatos["mes"] = "Mensal";
    return $formatos;
  }
  
  function buscaAniversariantes($mes) {
    $query = "
      SELECT
        usu_nome AS nome,
        DATE_FORMAT(usu_nascimento, '%d') AS dia
      FROM
        tab_usuarios
      WHERE
        usu_nascimento LIKE '%-{$mes}-%' AND
        uss_id = 1
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["nome_reduzido"] = strlen($dados["nome"]) > 12 ? substr($dados["nome"], 0, 10)."..." : $dados["nome"];
      $aniversariantes[$dados["dia"]][] = "<span onmouseover=\"hintShow('{$dados["nome"]}')\" onmouseout=\"hintHide()\"><img src=\"imagens/bolo_aniversario.png\" width=\"13\" height=\"13\" border=\"0\" style=\"margin:0px\"> {$dados["nome_reduzido"]}</span>";
    }
    return $aniversariantes;
  }


}

?>
