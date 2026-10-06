<?

require_once("_global/modulo.class.inc.php");

class chamados extends modulo {

  var $instancia;

  function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    $this->buscaDadosModulo("chamados");
    if ($executar) {
      $this->instancia = "";
      if ($_GET["a"] == "ver" && $this->temPermissao("editar")) $_GET["a"] = "editar";
      $this->buscaDadosAcao(!$_GET["a"] ? "listar" : $_GET["a"]);
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function fecharJanela() {
    unset($this->conteudo);
    if ($this->dados_item["cliente_id"]) {
      $this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscaChamados) window.opener.buscaChamados(\''.$this->dados_item["cliente_id"].'\', \'cli_id\');</script>';
    }
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  function geraRadioGroup($nome, $itens, $selecionado = "") {
  	$checkboxes = array();
    $i = 1;
    foreach ($itens as $valor => $item) {
      $checkboxes[] = "<input type=\"radio\" name=\"$nome\" id=\"{$nome}_{$i}\" value=\"$valor\"".($valor == $selecionado ? " checked" : "")." style=\"width:14px;height:14px\">&nbsp;<label for=\"{$nome}_{$i}\">$item</label>";
      $i++;
    }
    return implode("<br>", $checkboxes);
  }

  #######################
  ### Geral - Funções ###
  #######################

  function corrigeNome($nome) {
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
    $nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
    $nome = preg_replace("/[ ]+/", " ", $nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
    $nome = trim($nome); // Remove excessos do início e do fim
    return $nome;
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
      $tamanho_chave = 0;
      foreach ($array as $id => $valor) {
      	$chave = array();
        for ($i = 0; $i < sizeof($campos); $i++) {
          $chave[] = $valor[$campos[$i]];
        }
        $chave = implode(",", $chave);
        $tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
      }
      foreach ($array as $id => $valor) {
      	$chave = array();
      	for ($i = 0; $i < sizeof($campos); $i++) {
          $chave[] = $valor[$campos[$i]];
        }
        $chave = implode(",", $chave);
        if (!$valor[$chave]) {
          $array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".$id] = $id;
        }
      }
      ksort($array_ordenacao);
      while (is_int($array_id = current($array_ordenacao))) {
        $array_final[] = $array[$array_id];
        next($array_ordenacao);
      }
      return $array_final;
    }
  }

  function geraChamadoCodigo($chamado_id) {
    return $chamado_id."-".$this->modulo11("$chamado_id");
  }
  
  function buscaDataVisitaTecnica($chamado_id){
  	$query = "
  		SELECT
  			CONCAT(DATE_FORMAT(vis_data, '%d/%m/%Y'), ' (', vip_nome, ')') AS data
  		FROM
  			tab_visitas
  			INNER JOIN tab_visitas_periodos ON (tab_visitas.vip_id = tab_visitas_periodos.vip_id) 
  		WHERE 
  		 	cha_id = $chamado_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }
  

  function modulo11($valor) {
    $peso = 2;
    $soma = 0;
    for ($i = strlen($valor) - 1; $i >= 0; $i--) {
      $soma += strval($valor[$i] * $peso);
      $peso = ($peso < 9) ? $peso + 1 : 2;
    }
    $resto = $soma % 11;
    return in_array($resto, array(0, 1, 10)) ? 1 : 11 - $resto;
  }

  function registraEtapa($dados_chamado, $etapa_tipo_id, $dados = null, $usuario_id = null) {
    if ($this->validaEtapa($etapa_tipo_id, $dados)) {
      // Execução Etapa Ações
      $dados["retorno_execucao"] = $this->executaEtapaAcoes($dados_chamado["id"], $etapa_tipo_id, $dados);
      // Gera Etapa Conteudo
      $conteudo = addslashes(serialize($this->geraEtapaConteudo($etapa_tipo_id, $dados)));
      $datahora = date("Y-m-d H:i:s");
      $usuario_id = $usuario_id ? $usuario_id : ($_SESSION["usuario"] ? $_SESSION["usuario"] : "NULL");
      $lida = $this->ehChamadoAcompanhado($dados_chamado["id"]) ? "1" : "0";
      // Registra Etapa
      if (!array_key_exists("nivel_stress", $dados) || !$dados["nivel_stress"]) $dados["nivel_stress"] = "NULL";
      if (!array_key_exists("etapa_pai", $dados) || !$dados["etapa_pai"]) $dados["etapa_pai"] = "NULL";
      $query = "
        INSERT INTO
          tab_chamados_etapas (che_datahora, che_conteudo, che_lida, cha_id, usu_id, cen_id, cet_id, che_pai)
        VALUES (NOW(), '$conteudo', $lida, {$dados_chamado["id"]}, $usuario_id, {$dados["nivel_stress"]}, $etapa_tipo_id, {$dados["etapa_pai"]})";
      $this->bancodedados->query($query);
      $etapa_id = $this->bancodedados->ultimoIdInserido();
      // Atualiza Etapa Atual
      $etapa_atual = $this->buscaCategoriaTipoOrdem($dados_chamado["categoria_id"], $etapa_tipo_id);
      if ($etapa_atual) {
        $query = "UPDATE tab_chamados SET cha_etapa_atual = $etapa_atual WHERE cha_id = {$dados_chamado["id"]}";
        $this->bancodedados->query($query);
      }
      // Executa Ação Chamado c/ Vinculos Filho
      if ($etapa_tipo_id == 5 && ($chamados_filho = $this->buscaChamadosFilhoSimples($dados_chamado["id"]))) {
        $dados["conteudo"] = "Conteúdo herdado do chamado [n]{$dados_chamado["codigo"]}[/n]:\n[d]{$dados["conteudo"]}[/d]";
        $dados["etapa_pai"] = $etapa_id;
        foreach ($chamados_filho as $chamado_filho) {
          $this->registraEtapa($chamado_filho, $etapa_tipo_id, $dados, $usuario_id);
        }
      }
    }
  }

  function validaEtapa($etapa_tipo_id, $dados) {
    switch ($etapa_tipo_id) {
      case 5: // Comentário
      	$dados["conteudo"] = preg_replace('/[^\r\na-zA-Z0-9:áéíóúÁÉÍÓÚãõÃÕ :»;.,!?çÇ{}()"@\-\/´_=$~^+\'#-\[\]]/i', "", $dados["conteudo"]);
        return strlen(trim($dados["conteudo"])) > 0;
      case 12: // Defeito Reclamado
      	$dados["conteudo"] = preg_replace('/[^\r\na-zA-Z0-9:áéíóúÁÉÍÓÚãõÃÕ :»;.,!?çÇ{}()"@\-\/´_=$~^+\'#-\[\]]/i', "", $dados["conteudo"]);
        return strlen(trim($dados["conteudo"])) > 0;
      case 13: // Defeito Constatado
      	$dados["conteudo"] = preg_replace('/[^\r\na-zA-Z0-9:áéíóúÁÉÍÓÚãõÃÕ :»;.,!?çÇ{}()"@\-\/´_=$~^+\'#-\[\]]/i', "", $dados["conteudo"]);
        return strlen(trim($dados["conteudo"])) > 0;
      case 14: // Serviço Realizado
      	$dados["conteudo"] = preg_replace('/[^\r\na-zA-Z0-9:áéíóúÁÉÍÓÚãõÃÕ :»;.,!?çÇ{}()"@\-\/´_=$~^+\'#-\[\]]/i', "", $dados["conteudo"]);
        return strlen(trim($dados["conteudo"])) > 0;
      case 19: // Comentário Externo
      	$dados["conteudo"] = preg_replace('/[^\r\na-zA-Z0-9:áéíóúÁÉÍÓÚãõÃÕ :»;.,!?çÇ{}()"@\-\/´_=$~^+\'#-\[\]]/i', "", $dados["conteudo"]);
        return strlen(trim($dados["conteudo"])) > 0;
      case 16: // Solicitação de Fechamento
      	return $dados["responsavel_fechamento"];
      case 20: // Liberação Técnico
        return trim($dados["mac"]) ? true : false;
    }
    return true;
  }

  function executaEtapaAcoes($chamado_id, $etapa_tipo_id, $dados, $usuario_id = null) {
  	switch ($etapa_tipo_id) {
      case 2: // Categoria
        $query = "UPDATE tab_chamados SET chc_id = {$dados["categoria"]} WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        // Gera Visita
        $dados_categoria = $this->buscaDadosCategoria($dados["categoria"]);        
        $dados_chamado = $this->buscaDadosChamado($chamado_id);	
        if ($dados_categoria["visita_tipo_id"]) {
        	$query = "
        		SELECT
        			vis_id AS visita_id
        		FROM
        			tab_visitas
        		WHERE
        			cha_id = $chamado_id AND
        			vit_id = {$dados_categoria["visita_tipo_id"]}";
        	$query = $this->bancodedados->query($query);
        	$visita_id = $this->bancodedados->resultado($query);
        	if ($visita_id) {
		        $query = "
		        	UPDATE
		        		tab_visitas
		          SET
		          	via_id = 1,
		          	vis_data = NULL
		        	WHERE
		        		vis_id = $visita_id";
		        $this->bancodedados->query($query);
        	} else {
        		$cliente_id = $dados_chamado["cliente_id"] ? $dados_chamado["cliente_id"] : "NULL";
        		$condominio_id = $dados_chamado["condominio_id"] ? $dados_chamado["condominio_id"] : "NULL";
		        $query = "
		        	INSERT INTO
		        		tab_visitas (cli_id, con_id, vit_id, via_id, cha_id, vis_data_geracao) 
		          VALUES ($cliente_id, $condominio_id, {$dados_categoria["visita_tipo_id"]}, 1, $chamado_id, CURDATE())";
		        $this->bancodedados->query($query);
		        $visita_id = $this->bancodedados->ultimoIdInserido();
        	}
        	// Registra Comentário
    			$visitas = controle::instanciaObjeto("visitas", $this->bancodedados, $this->permissoes, false);
    			$visitas->registraComentario($visita_id, "Chamado encaminhado para {$dados_categoria["nome"]}", 20, $_SESSION["usuario"]);
        }
        $query = "
          SELECT
          	vis_id AS visita_id,
          	via_id AS status_id
         	FROM
         		tab_visitas
         	WHERE
         		cha_id = $chamado_id AND
        		via_id IN (1, 2) AND
        		vit_id != '{$dados_categoria["visita_tipo_id"]}'";
        $query = $this->bancodedados->query($query);
        $dados_visita = $this->bancodedados->resultadoArray($query);
        if ($dados_visita) {
        	$this->bancodedados->query("UPDATE tab_visitas SET via_id = IF(via_id = 1, 4, 3) WHERE vis_id = {$dados_visita["visita_id"]}");
        	// Registra Comentário
    			$visitas = controle::instanciaObjeto("visitas", $this->bancodedados, $this->permissoes, false);
    			$visitas->registraComentario($dados_visita["visita_id"], "Chamado encaminhado para {$dados_categoria["nome"]}", $dados_visita["status_id"] == 1 ? 21 : 18, $_SESSION["usuario"]);
        }
        break;
      case 3: // Encaminhamento
        if (count($encaminhamentos = $this->buscaChamadosEncaminhamentos($chamado_id)) > 1) {
          if ($this->ehEncaminhamentoRetorno($encaminhamentos, $dados["responsavel"])) {
            $situacao_id = 3;
          } else {
            $situacao_id = 2;
          }
        } else {
          $situacao_id = 1;
        }
        if ($dados["responsavel"]) {
          $responsavel = explode(",", $dados["responsavel"]);
          $responsavel[0] = $responsavel[0] ? $responsavel[0] : "NULL";
          $responsavel[1] = $responsavel[1] ? $responsavel[1] : "NULL";
          $responsavel[2] = $responsavel[2] ? $responsavel[2] : "NULL";
        } else {
          $origem_categoria = $this->buscaDadosCategoriaEtapa($dados["categoria"], 3);
          $responsavel[0] = $origem_categoria["setor_id"] ? $origem_categoria["setor_id"] : "NULL";
          $responsavel[1] = $origem_categoria["funcao_id"] ? $origem_categoria["funcao_id"] : "NULL";
          $responsavel[2] = "NULL";
        }
        $visualizado = $responsavel[2] == $_SESSION["usuario"] ? "1" : "0";
        $query = "UPDATE tab_chamados SET ors_id = {$responsavel[0]}, orf_id = {$responsavel[1]}, usu_id = $responsavel[2], cha_datahora_encaminhamento = NOW(), chi_id = $situacao_id, cha_visualizado = $visualizado WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        // Desagenda visita
//        if ($responsavel[2] == "NULL") {
//        	$query = "
//        		SELECT
//        			vis_id AS visita_id,
//        			via_id AS status_id
//        		FROM
//        			tab_visitas
//        		WHERE
//        			cha_id = $chamado_id";
//			    $query = $this->bancodedados->query($query);
//	       	if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
//				   	if ($dados["status_id"] == 2) {
//				   		$query = "UPDATE tab_visitas SET via_id = 1, vis_data = NULL WHERE vis_id = {$dados["visita_id"]}";
//				  		$query = $this->bancodedados->query($query);
//				   		$query = "INSERT INTO tab_visitas_comentarios (vis_id, vic_datahora, vic_conteudo, vcc_id, usu_id) VALUES ({$dados["visita_id"]}, NOW(), 'Chamado sem responsável', 19, {$_SESSION["usuario"]})";
//				  		$query = $this->bancodedados->query($query);
//				   	}
//			    }
//        }
        break;
      case 4: // Prioridade
        $query = "UPDATE tab_chamados SET chp_id = {$dados["prioridade"]} WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
      case 6: // Fechamento
        $query = "UPDATE tab_chamados SET chs_id = 2, cha_datahora_fechamento = NOW(), cha_aguardando_retorno = 0, cha_datahora_retorno = NULL WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        // Finaliza visita
        $query = "UPDATE tab_visitas SET via_id = IF(via_id = 1, 4, 3) WHERE cha_id = $chamado_id";
	    	$this->bancodedados->query($query);
        break;
      case 8: // Acompanhamento
        $query = "INSERT INTO rel_cha_usu_ehacompanhado (cha_id, usu_id) VALUES ($chamado_id, {$_SESSION["usuario"]})";
        $this->bancodedados->query($query);
        break;
      case 9: // Cancelamento de Acompanhamento
        $query = "DELETE FROM rel_cha_usu_ehacompanhado WHERE cha_id = $chamado_id AND usu_id = {$_SESSION["usuario"]}";
        $this->bancodedados->query($query);
        break;
      case 10: // Geração de Vínculo
        $chamado_pai = explode(",", $dados["chamado_pai"]);
        $query = "UPDATE tab_chamados SET cha_pai = {$chamado_pai[0]} WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
      case 11: // Remoção de Vínculo
        $query = "UPDATE tab_chamados SET cha_pai = NULL WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
      case 15: // Andamento
        $query = "UPDATE tab_chamados SET cha_andamento = {$dados["andamento"]} WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
      case 17: // Solicitação de Retorno
        $query = "UPDATE tab_chamados SET cha_aguardando_retorno = 1 WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
      case 18: // Retorno
        $query = "UPDATE tab_chamados SET cha_aguardando_retorno = 0 WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
      case 20: // Liberar Técnico
        return $this->liberarTecnica($chamado_id, $dados["mac"]);
        break;
      case 21: // Registrar Visualização
        $query = "UPDATE tab_chamados SET cha_visualizado = 1 WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
      	break;
      case 22: // Agendamento de Retorno
      	$data = substr($dados["data_hora_retorno"], 6, 4)."-".substr($dados["data_hora_retorno"], 3, 2)."-".substr($dados["data_hora_retorno"], 0, 2)." ".substr($dados["data_hora_retorno"], 12, 5); 
      	$query = "UPDATE tab_chamados SET cha_datahora_retorno = '$data', cha_aguardando_retorno = 1 WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
     case 23: // Desagendamento de Retorno
        $query = "UPDATE tab_chamados SET cha_datahora_retorno = null, cha_aguardando_retorno = 0 WHERE cha_id = $chamado_id";
        $this->bancodedados->query($query);
        break;
     case 25: // Desagendamento de visita
	  		$this->bancodedados->query("UPDATE tab_visitas SET vis_data = NULL WHERE cha_id = $chamado_id AND via_id = 2");
        break;
     case 26: // Reabertura
	  		$this->bancodedados->query("UPDATE tab_chamados SET chs_id = 1, cha_datahora_fechamento = NULL WHERE cha_id = $chamado_id");
        break;
    }
  }

  function geraEtapaConteudo($etapa_tipo_id, $dados) {
    switch ($etapa_tipo_id) {
      case 1: // Abertura
        $parametros["setor_id"] = null;
        $parametros["funcao_id"] = null;
        $parametros["usuario_id"] = $_SESSION["usuario"];
        return array("conteudo" => "", "parametros" => $parametros);
      case 2: // Categoria
        $dados_categoria = $this->buscaDadosCategoria($dados["categoria"]);
        return array("conteudo" => $dados_categoria["nome"]);
      case 3: // Encaminhamento
        if ($dados["responsavel"]) {
          $responsavel = explode(",", $dados["responsavel"]);
          if ($responsavel[0]) $destino[] = $this->buscaSetor($responsavel[0]);
          if ($responsavel[1]) $destino[] = $this->buscaFuncao($responsavel[1]);
          if ($responsavel[2]) $destino[] = $this->buscaUsuario($responsavel[2]);
          $parametros["setor_id"] = $responsavel[0];
          $parametros["funcao_id"] = $responsavel[1];
          $parametros["usuario_id"] = $responsavel[2];
        } else {
          $origem_categoria = $this->buscaDadosCategoriaEtapa($dados["categoria"], 3);
          if ($origem_categoria["setor_id"]) $destino[] = $this->buscaSetor($origem_categoria["setor_id"]);
          if ($origem_categoria["funcao_id"]) $destino[] = $this->buscaFuncao($origem_categoria["funcao_id"]);
          $parametros["setor_id"] = $origem_categoria["setor_id"];
          $parametros["funcao_id"] = $origem_categoria["funcao_id"];
        }
        $conteudo = implode(" » ", $destino);
        return array("conteudo" => $conteudo, "parametros" => $parametros);
      case 4: // Prioridade
        return array("conteudo" => $this->buscaPrioridades($dados["prioridade"]));
      case 5: // Comentário Interno
        if ($dados["sistema"]) {
          return array("conteudo" => stripslashes(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"]))));
        } else {
          return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
        }
      case 10: // Geração de Vínculo
        $dados_chamado = $this->buscaDadosChamado($dados["chamado_pai"]);
        return array("conteudo" => "<b>Chamado: </b> {$dados_chamado["codigo"]} - {$dados_chamado["categoria_nome"]}");
      case 12: // Defeito Reclamado
        return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
      case 13: // Defeito Constatado
        return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
      case 14: // Serviço Realizado
        return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
      case 15: // Andamento
        return array("conteudo" => "{$dados["andamento"]}%");
      case 16: // Solicitamento de Fechamento
        $responsavel = explode(",", $dados["responsavel_fechamento"]);
        if ($responsavel[0]) $responsavel_nome[] = $this->buscaSetor($responsavel[0]);
        if ($responsavel[1]) $responsavel_nome[] = $this->buscaFuncao($responsavel[1]);
        if ($responsavel[2]) $responsavel_nome[] = $this->buscaUsuario($responsavel[2]);
        $parametros["setor_id"] = $responsavel[0];
        $parametros["funcao_id"] = $responsavel[1];
        $parametros["usuario_id"] = $responsavel[2];
        $responsavel_nome = implode(" » ", $responsavel_nome);
        return array("conteudo" => "<b>Responsável: </b> ".$responsavel_nome, "parametros" => $parametros);
      case 17: // Solicitação de Retorno
        return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
      case 18: // Retorno
        return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
      case 19: // Comentário Externo
        if ($dados["sistema"]) {
          return array("conteudo" => stripslashes(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"]))));
        } else {
          return array("conteudo" => stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1")));
        }
      case 20: // Liberar Tecnico
        $comentario = stripslashes(htmlspecialchars(trim(str_replace(array("\r\n", "%2B"), array("\n", "+"), $dados["conteudo"])), ENT_COMPAT, "ISO-8859-1"));
        return array("conteudo" => "<b>MAC:</b> {$dados["mac"]}".($dados["retorno_execucao"] ? "<br><b>&nbsp;» Erro(s):</b> ".implode(", ", $dados["retorno_execucao"]) : "").($comentario ? "<br><b>&nbsp;» Comentário:</b> $comentario" : ""));
      case 22: // Agendamento de retorno
        return array("conteudo" => "<b>Data de Retorno:</b> {$dados["data_hora_retorno"]}");
      case 24: // Agendamento de visita
        return array("conteudo" => "<b>Data da Visita:</b> {$dados["data"]} ({$dados["turno"]})");
    }
  }

  function formataConteudo($conteudo) {
    $conteudo = "<div style=\"padding-left:12px\">$conteudo</div>";
    $procurar = array("[n]", "[/n]", "[i]", "[/i]", "[s]", "[/s]", "[vermelho]", "[/vermelho]", "[azul]", "[/azul]", "[verde]", "[/verde]", "[d]", "[/d]");
    $substituir = array("<b>", "</b>", "<i>", "</i>", "<u>", "</u>", "<font color=\"red\">", "</font>", "<font color=\"blue\">", "</font>", "<font color=\"green\">", "</font>", "<div class=\"chamadoDestaque\">", "</div>");
    $conteudo = str_replace($procurar, $substituir, $conteudo);
    return str_replace("</div><br>", "</div>", $conteudo);
  }

  function editaEtapaConteudo($etapa_id, $dados) {
    $dados_etapa = $this->buscaDadosEtapa($etapa_id);
    if ($dados_etapa && (!$dados_etapa["lida"] || $this->permissoes["chamados"]["editar_etapa_plus"])) {
	    $conteudo = addslashes(serialize($this->geraEtapaConteudo($dados_etapa["tipo_id"], $dados)));
	    $query = "UPDATE tab_chamados_etapas SET che_conteudo = '$conteudo' WHERE che_id = $etapa_id";
	    $this->bancodedados->query($query);
	    $etapas_filhas = $this->buscaEtapasFilhas($etapa_id);
	    if ($etapas_filhas) {
	      $chamado_codigo = $this->buscaChamadoCodigoPorEtapa($etapa_id);
	      $dados["conteudo"] = "Conteúdo herdado do chamado [n]{$chamado_codigo}[/n]:\n[d]{$dados["conteudo"]}[/d]";
	      foreach ($etapas_filhas as $etapa_filha_id) {
	        $this->editaEtapaConteudo($etapa_filha_id, $dados);
	      }
	    }
    }
  }

  function buscaChamadosEncaminhamentos($chamado_id) {
  	$encaminhamentos = array();
    $query = "
      SELECT
        che_conteudo AS conteudo
      FROM
        tab_chamados_etapas
      WHERE
        cha_id = $chamado_id AND
        cet_id IN (1, 3)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      $encaminhamentos[] = $dados;
    }
    return $encaminhamentos;
  }

  function ehEncaminhamentoRetorno($encaminhamentos, $responsavel) {
    $responsavel = explode(",", $responsavel);
    foreach ($encaminhamentos as $encaminhamento) {
      if ($encaminhamento["conteudo"]["parametros"]["usuario_id"] && ($encaminhamento["conteudo"]["parametros"]["usuario_id"] == $responsavel[2])) {
        return true;
      } elseif ($encaminhamento["conteudo"]["parametros"]["setor_id"] && ($encaminhamento["conteudo"]["parametros"]["setor_id"] == $responsavel[0])) {
        if (!$encaminhamento["conteudo"]["parametros"]["funcao_id"] || ($encaminhamento["conteudo"]["parametros"]["usuario_id"] == $responsavel[1])) {
          if (!$encaminhamento["conteudo"]["parametros"]["usuario_id"] || ($encaminhamento["conteudo"]["parametros"]["usuario_id"] == $responsavel[2])) {
            return true;
          }
        }
      }
    }
    return false;
  }

  function buscaChamadoCodigoPorEtapa($etapa_id) {
    $query = "
      SELECT
        (SELECT tab_chamados.cha_codigo FROM tab_chamados WHERE tab_chamados.cha_id = tab_chamados_etapas.cha_id) AS codigo
      FROM
        tab_chamados_etapas
      WHERE
        tab_chamados_etapas.che_id = $etapa_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  function buscaChamadoCodigo($chamado_id) {
    $query = "
      SELECT
        cha_codigo
      FROM
        tab_chamados
      WHERE
        tab_chamados.cha_id = $chamado_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  function buscaDadosEtapa($etapa_id) {
    $query = "
      SELECT
        che_id AS id,
        cet_id AS tipo_id,
        che_conteudo AS conteudo,
        che_lida AS lida
      FROM
        tab_chamados_etapas
      WHERE
        tab_chamados_etapas.che_id = $etapa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      return $dados;
    }
  }

  function buscaEtapas($chamado_id, $detalhada = null, $externas = 0, $ultima = false, $ultimos_primeiro = false, $impressao = false) {
    // Filtro
    if ($detalhada === null && array_key_exists("chamados_etapas_detalhamento", $_SESSION["instancias"][$this->instancia])) {
      $detalhada = $_SESSION["instancias"][$this->instancia]["chamados_etapas_detalhamento"];
    }
    $detalhada = $detalhada ? "(0, 1)" : "(0)";
    $externas = !$externas ? "(0, 1)" : "(0)";
    // Define Etapas Comentários de outros usuários como Lidas
    $query = "UPDATE tab_chamados_etapas INNER JOIN tab_chamados_etapas_tipos ON (tab_chamados_etapas.cet_id = tab_chamados_etapas_tipos.cet_id) SET che_lida = 1 WHERE cha_id = $chamado_id AND cet_sistema IN $detalhada AND usu_id != {$_SESSION["usuario"]}";
    $this->bancodedados->query($query);
    // Buscar
    $query = "
      SELECT DISTINCT
        tab_chamados_etapas.che_id AS id,
        DATE_FORMAT(tab_chamados_etapas.che_datahora, '%d/%m/%Y (%H:%i)') AS datahora,
        tab_chamados_etapas.che_conteudo AS conteudo,
        tab_chamados_etapas.che_lida AS lida,
        tab_chamados_etapas.che_pai AS etapa_pai_id,
        tab_usuarios.usu_id AS usuario_id,
        IF(tab_usuarios.usu_nome IS NOT NULL, tab_usuarios.usu_nome, 'Sistema') AS usuario_nome,
        tab_chamados_etapas_niveis_stress.cen_id AS nivel_stress_id,
        tab_chamados_etapas_niveis_stress.cen_nome AS nivel_stress_nome,
        tab_chamados_etapas_tipos.cet_id AS tipo_id,
        tab_chamados_etapas_tipos.cet_nome AS tipo_nome
      FROM
        tab_chamados_etapas
        LEFT OUTER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_chamados_etapas.usu_id)
        LEFT OUTER JOIN tab_chamados_etapas_niveis_stress ON (tab_chamados_etapas_niveis_stress.cen_id = tab_chamados_etapas.cen_id)
        INNER JOIN tab_chamados_etapas_tipos ON (tab_chamados_etapas_tipos.cet_id = tab_chamados_etapas.cet_id)
      WHERE
        tab_chamados_etapas.cha_id = $chamado_id AND
        tab_chamados_etapas_tipos.cet_sistema IN $detalhada AND
        tab_chamados_etapas_tipos.cet_interno IN $externas".($impressao ? " AND
        tab_chamados_etapas_tipos.cet_id IN (5, 12, 13, 14, 19)" : "").($ultima ? "
      ORDER BY
        tab_chamados_etapas.che_id DESC
      LIMIT 0, 1" : ($ultimos_primeiro ? "
      ORDER BY
      	IF(tab_chamados_etapas_tipos.cet_id = 19, 0, 1) ASC,
        tab_chamados_etapas.che_id DESC" : "
      ORDER BY
        tab_chamados_etapas.che_datahora ASC,
        tab_chamados_etapas.che_id ASC"));
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      $etapas[] = $dados;
    }
    return $etapas;
  }

  function geraListagemEtapas($itens) {
    for ($i = 0; $i < sizeof($itens); $i++) {
    	$itens[$i]["detalhamento"] = array();
      $itens[$i]["detalhamento"][] = "<b>{$itens[$i]["usuario_nome"]}</b> em <b>{$itens[$i]["datahora"]}</b>";
      $itens[$i]["detalhamento"][] = "<b>&nbsp;&raquo; {$itens[$i]["tipo_nome"]}</b>".($itens[$i]["conteudo"] ? $this->formataEtapaConteudo($itens[$i]) : "");
      if ($itens[$i]["nivel_stress_nome"]) $itens[$i]["detalhamento"][] = "<b>&nbsp;&raquo; Nível de Stress</b>: {$itens[$i]["nivel_stress_nome"]}";
      $itens[$i]["detalhamento"] = implode("<br>", $itens[$i]["detalhamento"]);
      if (!$itens[$i]["etapa_pai_id"] && ($this->temPermissao("editar_etapa_plus") || $this->temPermissao("remover_etapa_plus") || (($itens[$i]["usuario_id"] == $_SESSION["usuario"] && !$itens[$i]["lida"] && in_array($itens[$i]["tipo_id"], array(5, 12, 13, 14, 19))) || (in_array($itens[$i]["tipo_id"], array(12, 13, 14)) && $this->temPermissao("editar_etapa_plus")) || (in_array($itens[$i]["tipo_id"], array(12, 13, 14)) && $this->temPermissao("editar_etapa"))))) {
        $itens[$i]["detalhamento"] = "<div style=\"float:right\"><span style=\"text-decoration:underline;cursor:pointer\" onclick=\"editarEtapa({$itens[$i]["id"]}, 0, 1)\">Editar</span>&nbsp; <span style=\"text-decoration:underline;cursor:pointer\" onclick=\"removerEtapa({$itens[$i]["id"]})\">Remover</span></div>{$itens[$i]["detalhamento"]}";
      }
      $itens[$i]["id"] = "etapa_{$itens[$i]["id"]}";
    }
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function formataEtapaConteudo($etapa) {
    $conteudo = $etapa["conteudo"]["conteudo"];
    $conteudo = str_replace("\n ", "\n&nbsp;", $conteudo);
    $conteudo = str_replace("  ", " &nbsp;", $conteudo);
    $conteudo = str_replace("\r\n", "\n", $conteudo);
    $conteudo = str_replace("\n", "<br>", $conteudo);
    if (in_array($etapa["tipo_id"], array(5, 12, 13, 14, 17, 18, 19))) {
      $conteudo = $this->formataConteudo($conteudo);
    } else {
      if (substr($conteudo, 0, 3) == "<b>") {
        $conteudo = "<br><b>&nbsp;&raquo; </b>{$conteudo}";
      } else {
        $conteudo = $conteudo ? ": {$conteudo}" : "";
      }
    }
    return $conteudo;
  }

  function liberarTecnica($servidor_id, $mac) {
    if ($this->dados_item["cliente_id"]) {
      $lista_servidores = $this->buscaServidorCliente($this->dados_item["cliente_id"]);
    } elseif ($this->dados_item["condominio_id"]) {
      $lista_servidores = $this->buscaServidorCondominio($this->dados_item["condominio_id"]);
    }
    $servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
    foreach ($lista_servidores as $servidor_id) {
    	$erro = $servidores->liberarPonto($servidor_id, $mac);
      if ($erro) {
        $erros[] = $erro;
      }
    }
    return $erros;
  }

  #####################
  ### Geral - Busca ###
  #####################

  function buscaDadosItem($id, $sistema = false) {
    $this->dados_item = $this->buscaDadosChamado($id, $sistema);
  }

  function buscaDadosChamado($id, $sistema = false) {
    $query = "
      SELECT
        tab_chamados.cha_id AS id,
        tab_chamados.cha_codigo AS codigo,
        DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
        DATE_FORMAT(tab_chamados.cha_datahora_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
        DATE_FORMAT(tab_chamados.cha_datahora_encaminhamento, '%d/%m/%Y (%H:%i)') AS encaminhamento,
        tab_chamados.cha_solicitante AS solicitante,
        tab_chamados.cha_etapa_atual AS etapa_atual,
        tab_chamados.cli_id AS cliente_id,
        tab_chamados.con_id AS condominio_id,
        tab_chamados.chc_id AS categoria_id,
        tab_chamados.cha_pai AS chamado_pai,
        tab_chamados.cha_andamento AS andamento,
        tab_chamados.cha_aguardando_retorno AS aguardando_retorno,
        DATE_FORMAT(tab_chamados.cha_datahora_retorno, '%d/%m/%Y (%H:%i)') AS data_hora_retorno,
        tab_chamados.cha_visualizado AS visualizado,
        IF(tab_chamados.ors_id, tab_chamados.ors_id, 0) AS setor_id,
        IF(tab_chamados.orf_id, tab_chamados.orf_id, 0) AS funcao_id,
        IF(tab_chamados.usu_id, tab_chamados.usu_id, 0) AS usuario_id,
        tab_chamados_prioridades.chp_id AS prioridade_id,
        tab_chamados_prioridades.chp_nome AS prioridade_nome,
        tab_chamados_status.chs_id AS status_id,
        tab_chamados_status.chs_nome AS status_nome,
        tab_clientes.cli_nome AS cliente_nome,
        tab_clientes.clo_id AS origem_id,
        tab_condominios.con_nome AS condominio_nome,
        tab_condominios.cnt_id AS condominio_tipo_id
      FROM
        tab_chamados
        INNER JOIN tab_chamados_status ON (tab_chamados_status.chs_id = tab_chamados.chs_id)
        INNER JOIN tab_chamados_prioridades ON (tab_chamados_prioridades.chp_id = tab_chamados.chp_id)
        LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios ON (tab_chamados.con_id = tab_condominios.con_id)
      WHERE
        cha_id = $id".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "").(!$sistema && $_SESSION["usuario"] && !$this->temPermissao("buscar_plus") ? " AND
        (tab_chamados.cha_id IN (SELECT cha_id FROM tab_chamados_etapas WHERE cha_id = $id AND usu_id = {$_SESSION["usuario"]}) OR tab_chamados.usu_id = {$_SESSION["usuario"]})" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados_categoria = $this->buscaDadosCategoria($dados["categoria_id"]);
      $dados["categoria_nome"] = $dados_categoria["nome"];
      $dados["duracao"] = $this->formataDuracao($dados["abertura"], $dados["fechamento"]);
      $dados["duracao_setor"] = $this->formataDuracao($dados["encaminhamento"], $dados["fechamento"]);
      $dados["responsavel_nome"] = array();
      if ($dados["setor_id"]) $dados["responsavel_nome"][] = $this->buscaSetor($dados["setor_id"]);
      if ($dados["funcao_id"]) $dados["responsavel_nome"][] = $this->buscaFuncao($dados["funcao_id"]);
      if ($dados["usuario_id"]) $dados["responsavel_nome"][] = $this->buscaUsuario($dados["usuario_id"]);
      $dados["responsavel_id"] = "{$dados["setor_id"]},{$dados["funcao_id"]},{$dados["usuario_id"]}";
      $dados["responsavel_nome"] = implode(" » ", $dados["responsavel_nome"]);
      return $dados;
    }
  }

  function buscaChamadoClienteCategoria($cliente_id, $categoria_id) {
  	$query = "
  	  SELECT
  	    cha_id
  	  FROM
  	    tab_chamados
  	  WHERE
  	    cli_id = $cliente_id AND
  	    chc_id = $categoria_id AND
  	    chs_id = 1
  	  LIMIT
  	    0, 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }
  
  function formataDuracao($abertura, $fechamento = null) {
    if (!$abertura) {
      return "";
    }
    $fechamento = $fechamento ? $fechamento : date('d/m/Y (H:i)');
    if (strtotime(substr($fechamento, 6, 4)."-".substr($fechamento, 3, 2)."-".substr($fechamento, 0, 2)." ".substr($fechamento, 12, 2).":".substr($fechamento, 15, 2)) <
        strtotime(substr($abertura, 6, 4)."-".substr($abertura, 3, 2)."-".substr($abertura, 0, 2)." ".substr($abertura, 12, 2).":".substr($abertura, 15, 2))) {
      return "menos de 1 minuto";
    }
    // Anos
    $anos = intval(substr($fechamento, 6, 4), 10) - intval(substr($abertura, 6, 4), 10);
    // Meses
    $meses = intval(substr($fechamento, 3, 2), 10) - intval(substr($abertura, 3, 2), 10);
    // Dias
    $dias = intval(substr($fechamento, 0, 2), 10) - intval(substr($abertura, 0, 2), 10);
    // Horas
    $horas = intval(substr($fechamento, 12, 2), 10) - intval(substr($abertura, 12, 2), 10);
    // Minutos
    $minutos = intval(substr($fechamento, 15, 2), 10) - intval(substr($abertura, 15, 2), 10);
    // Recalcula Negativos
    if ($minutos < 0) {
      $horas--;
      $minutos = 60 + $minutos;
    }
    if ($horas < 0) {
      $dias--;
      $horas = 24 + $horas;
    }
    if ($dias < 0) {
      $meses--;
      $dias = intval(date("t", strtotime(substr($abertura, 6, 4)."-".substr($abertura, 3, 2)."-".substr($abertura, 0, 2))), 10) + $dias;
    }
    if ($meses < 0) {
      $anos--;
      $meses = 12 + $meses;
    }
    // Anos
    $duracao_formatada = array();
    if ($anos) {
      $duracao_formatada[] = $anos." ".($anos > 1 ? "anos" : "ano");
    }
    if ($meses) {
      $duracao_formatada[] = $meses." ".($meses > 1 ? "meses" : "mês");
    }
    // Dias
    if ($dias && !$anos) {
      $duracao_formatada[] = $dias." ".($dias > 1 ? "dias" : "dia");
    }
    // Horas
    if ($horas && !$meses && !$anos) {
      $duracao_formatada[] = $horas." ".($horas > 1 ? "horas" : "hora");
    }
    // Minutos
    if ($minutos && !$dias && !$meses && !$anos) {
      $duracao_formatada[] = $minutos." ".($minutos > 1 ? "min" : "min");
    }
    // Segundos
    if (!$duracao_formatada) {
      return "menos de 1 minuto";
    }
    return implode(" ", $duracao_formatada);
  }

  function buscaChamadosCategorias($usuario_id = null, $categoria_atual = null) {
  	$lista = array();
    $query = "
      SELECT
        chc_id AS id
      FROM
        tab_chamados_categorias";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if (!$usuario_id || $categoria_atual == $dados["id"] || $this->chamadosCategoriasTemAcesso($usuario_id, $dados["id"])) {
        $dados = $this->buscaDadosCategoria($dados["id"]);
        $lista[$dados["id"]] = $dados["nome"];
      }
    }
    asort($lista);
    return $lista;
  }

  function chamadosCategoriasTemAcesso($usuario_id, $categoria_id) {
    $query = "
      SELECT
        tab_chamados_categorias.chc_id AS id,
        tab_chamados_categorias.chc_restrita AS restrita,
        tab_chamados_categorias.chc_pai AS pai,
        tab_chamados_categorias_etapas.cce_id AS etapa_id,
        tab_chamados_categorias_etapas.ors_id AS setor_id
      FROM
        tab_chamados_categorias
        LEFT OUTER JOIN tab_chamados_categorias_etapas ON (tab_chamados_categorias_etapas.chc_id = tab_chamados_categorias.chc_id AND tab_chamados_categorias_etapas.cet_id = 1)
      WHERE
        tab_chamados_categorias.chc_id = $categoria_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["etapa_id"] && (!$dados["restrita"] || $this->ehSetorUsuario($dados["setor_id"], $usuario_id))) {
        return true;
      } elseif ($dados["pai"] && !$dados["etapa_id"]) {
        if ($this->chamadosCategoriasTemAcesso($usuario_id, $dados["pai"])) {
          return true;
        }
      } else {
        return false;
      }
    }
  }

  function buscaUsuarioEtapa($chamado_id, $etapa_tipo_id, $campo = null) {
    $query = "
      SELECT
        tab_chamados_etapas.cet_id AS etapa_id,
        tab_usuarios.usu_id AS usuario_id,
        IF(tab_usuarios.usu_id IS NOT NULL, tab_usuarios.usu_nome, 'Sistema') AS usuario_nome
      FROM
        tab_chamados_etapas
        LEFT OUTER JOIN tab_usuarios ON (tab_chamados_etapas.usu_id = tab_usuarios.usu_id)
      WHERE
        cha_id = $chamado_id AND
        cet_id = $etapa_tipo_id
      ORDER BY
        tab_chamados_etapas.che_datahora ".($etapa_tipo_id == 1 ? "ASC" : "DESC")."
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $campo ? $dados[$campo] : $dados;
    }
  }

  function buscaDadosCategoria($categoria_id) {
    $query = "
      SELECT
        chc_id AS id,
        chc_nome AS nome,
        chc_pai AS pai,
        vit_id AS visita_tipo_id
      FROM
        tab_chamados_categorias
      WHERE
        chc_id = $categoria_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["pai"]) {
        $dados_pai = $this->buscaDadosCategoria($dados["pai"]);
        $dados["nome"] = $dados_pai["nome"]." » ".$dados["nome"];
        $dados["pai_acima"] = $dados["pai"];
        $dados["pai"] = $dados_pai["pai"]  ? $dados_pai["pai"] : $dados["pai"];
      }
      return $dados;
    }
  }

  function buscaEtapasTipos($categoria_id = 0, $ordem = '%', $chamado_id = 0, $tipo_id = null) {
    if ($tipo_id) {
      $query = "
        SELECT
          cet_id AS id,
          cet_nome AS nome
        FROM
          tab_chamados_etapas_tipos
        WHERE
          cet_id = $tipo_id";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
    	if ($dados) {
        return array($dados["id"] => $dados["nome"]);
      }
    }
    $categoria_id = $categoria_id ? $categoria_id : '0';
    $ordem = $ordem ? $ordem : '%';
    $chamado_id = $chamado_id ? $chamado_id : '0';
    $query = "
      SELECT DISTINCT
        tab_chamados_etapas_tipos.cet_id AS id,
        tab_chamados_etapas_tipos.cet_nome AS nome,
        tab_chamados_categorias_etapas.cce_obrigatoria AS obrigatoria
      FROM
        tab_chamados_etapas_tipos
        LEFT OUTER JOIN tab_chamados_categorias_etapas ON (tab_chamados_categorias_etapas.cet_id = tab_chamados_etapas_tipos.cet_id)
        LEFT OUTER JOIN tab_chamados_etapas ON (tab_chamados_etapas.cet_id = tab_chamados_categorias_etapas.cet_id AND tab_chamados_etapas.cha_id = '$chamado_id')
        LEFT OUTER JOIN tab_chamados ON (tab_chamados.cha_id = '$chamado_id')
      WHERE
        (tab_chamados_categorias_etapas.chc_id = '$categoria_id' AND
        (tab_chamados_etapas.che_id IS NULL OR tab_chamados_categorias_etapas.cce_multipla = 1) AND
        ((tab_chamados_categorias_etapas.cce_ordem LIKE '$ordem' AND tab_chamados_categorias_etapas.cce_multipla = 1) OR
         tab_chamados_categorias_etapas.cce_ordem - 1 LIKE '$ordem' OR
         tab_chamados_categorias_etapas.cce_ordem IS NULL)) OR
        (tab_chamados_etapas_tipos.cet_id IN (5, 19)) OR
        (tab_chamados_etapas_tipos.cet_id = 17 AND tab_chamados.cha_aguardando_retorno = 0) OR
        (tab_chamados_etapas_tipos.cet_id = 18 AND tab_chamados.cha_aguardando_retorno = 1)
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
      if (!$dados["obrigatoria"] && $ordem < $this->buscaUltimaEtapa($categoria_id)) {
      	$proxima_etapa = $this->buscaEtapasTipos($categoria_id, $ordem + 1, $chamado_id);
        if ($proxima_etapa) {
          $lista += $proxima_etapa;
        }
      }
    }
    if ($lista) {
    	$lista = array_unique($lista);
      asort($lista);
      return $lista;
    }
  }

  function buscaEtapaTipo($categoria_id, $ordem) {
    $query = "
      SELECT DISTINCT
        cet_id AS id
      FROM
        tab_chamados_categorias_etapas
      WHERE
        chc_id = $categoria_id AND
        cce_ordem = $ordem";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  function buscaEtapaTipoNome($tipo_id) {
    $query = "
      SELECT
        cet_nome AS nome
      FROM
        tab_chamados_etapas_tipos
      WHERE
        cet_id = $tipo_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if (substr($dados["nome"], 0, 12) == "Registro de ") {
        $dados["nome"] = substr($dados["nome"], 12);
      }
      return $dados["nome"];
    }
  }

  function buscaUltimaEtapa($categoria_id) {
    $query = "
      SELECT
        MAX(cce_ordem) AS ordem
      FROM
        tab_chamados_categorias_etapas
      WHERE
        chc_id = $categoria_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["ordem"];
    }
  }

  function buscaEtapasPendentes($chamado_id) {
    $query = "
      SELECT
        tab_chamados_etapas_tipos.cet_nome AS nome
      FROM
        tab_chamados
        INNER JOIN tab_chamados_categorias_etapas ON (tab_chamados_categorias_etapas.chc_id = tab_chamados.chc_id)
        INNER JOIN tab_chamados_etapas_tipos ON (tab_chamados_etapas_tipos.cet_id = tab_chamados_categorias_etapas.cet_id)
        LEFT OUTER JOIN tab_chamados_etapas ON (tab_chamados_etapas.cha_id = tab_chamados.cha_id AND tab_chamados_etapas.cet_id = tab_chamados_categorias_etapas.cet_id)
      WHERE
        tab_chamados.cha_id = $chamado_id AND
        tab_chamados_categorias_etapas.cce_obrigatoria = 1 AND
        tab_chamados_etapas.cet_id IS NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $pendencias[] = "&raquo; {$dados["nome"]}";
    }
    return $pendencias;
  }

  function buscaUltimaEtapaChamado($chamado_id) {
    $query = "
      SELECT
        tab_chamados_etapas.che_id AS id,
        DATE_FORMAT(tab_chamados_etapas.che_datahora, '%d/%m/%Y (%H:%i)') AS datahora,
        tab_chamados_etapas.che_conteudo AS conteudo,
        tab_chamados_etapas.che_lida AS lida,
        tab_usuarios.usu_id AS usuario_id,
        IF(tab_usuarios.usu_nome IS NOT NULL, tab_usuarios.usu_nome, 'Sistema') AS usuario_nome,
        tab_chamados_etapas_niveis_stress.cen_id AS nivel_stress_id,
        tab_chamados_etapas_niveis_stress.cen_nome AS nivel_stress_nome,
        tab_chamados_etapas_tipos.cet_id AS tipo_id,
        tab_chamados_etapas_tipos.cet_nome AS tipo_nome
      FROM
        tab_chamados_etapas
        LEFT OUTER JOIN tab_usuarios ON (tab_usuarios.usu_id = tab_chamados_etapas.usu_id)
        LEFT OUTER JOIN tab_chamados_etapas_niveis_stress ON (tab_chamados_etapas_niveis_stress.cen_id = tab_chamados_etapas.cen_id)
        INNER JOIN tab_chamados_etapas_tipos ON (tab_chamados_etapas_tipos.cet_id = tab_chamados_etapas.cet_id)
      WHERE
        tab_chamados_etapas.cha_id = $chamado_id AND
        tab_chamados_etapas_tipos.cet_sistema = 0
      ORDER BY
        id DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      // Define etapa de outros usuários como lida
      if (!$dados["lida"] && $dados["usuario_id"] != $_SESSION["usuario"]) {
        $query = "UPDATE tab_chamados_etapas SET che_lida = 1 WHERE che_id = {$dados["id"]}";
        $this->bancodedados->query($query);
      }
      // Trata Dados
      $dados["conteudo"]["conteudo"] = $dados["conteudo"]["conteudo"] ? ": ".strip_tags(preg_replace("/[ \r\n\t]+/", " ", preg_replace("/\\[[^\\]]*\\]/", "", $dados["conteudo"]["conteudo"]))) : "";
      $dados["conteudo"]["conteudo"] = strlen($dados["conteudo"]["conteudo"]) > 220 ? substr($dados["conteudo"]["conteudo"], 0, 220)."..." : $dados["conteudo"]["conteudo"];
      $ultima_etapa = array();
      $ultima_etapa[] = "<b>{$dados["usuario_nome"]}</b> em <b>{$dados["datahora"]}</b>";
      $ultima_etapa[] = "<b>&nbsp;&raquo; {$dados["tipo_nome"]}</b>".$dados["conteudo"]["conteudo"];
      if ($dados["nivel_stress_nome"]) $ultima_etapa[] = "<b>&nbsp;&raquo; Nível de Stress</b>: {$dados["nivel_stress_nome"]}";
      return implode('<br>', $ultima_etapa);
    }
  }

  function buscaDadosCliente($cliente_id) {
    $query = "
      SELECT
        tab_clientes.cli_id AS id,
        tab_clientes.cli_nome AS nome,
        tab_clientes.cli_complemento AS complemento,
 		tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
        tab_clientes.clt_id AS pessoa_tipo_id,
        IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
        tab_clientes.cof_id AS formato_cobranca_id,
        DATE_FORMAT(tab_clientes.cli_nascimento, '%d/%m') AS data_aniversario,
        DATE_FORMAT(tab_clientes.cli_data_cadastro, '%d/%m/%Y') AS data_cadastro,
        tab_clientes_status.cls_id AS status_id,
        tab_clientes_status.cls_nome AS status_nome,
        tab_condominios.con_id AS condominio_id,
        tab_condominios.con_nome AS condominio,
        tab_condominios.cnt_id AS condominio_tipo_id,
        tab_condominios_blocos.cob_id AS bloco_id,
        tab_condominios_blocos.cob_nome AS bloco_nome,
        tab_estados.est_sigla AS estado,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro,
				tab_clientes.cli_ponto_referencia AS ponto_referencia,
        INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
        tab_clientes_origens.clo_id AS origem_id,
        tab_clientes_origens.clo_nome AS origem_nome
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.con_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
        INNER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)
        INNER JOIN tab_bairros ON (tab_bairros.bai_id = tab_logradouros.bai_id)
        INNER JOIN tab_cidades ON (tab_cidades.cid_id = tab_bairros.cid_id)
        INNER JOIN tab_estados ON (tab_estados.est_id = tab_cidades.est_id)
        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
      WHERE
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["condominio_id"]) {
        $dados["condominio_nome"] = $dados["condominio"];
        if ($dados["bairro"]) {
          $dados["condominio_nome"] .= " / ".$dados["bairro"];
        }
        $dados["condominio_nome"] = "<span style=\"text-decoration:underline;cursor:pointer\" onclick=\"verCondominio({$dados["condominio_id"]})\">{$dados["condominio_nome"]}</span>";
        $condominio_tipo = $this->buscaCondominioTipo($dados["condominio_tipo_id"]);
        if ($condominio_tipo) {
          $dados["condominio_nome"] .= " ($condominio_tipo)";
        }
        $dados_condominio = $this->buscaDadosCondominio($dados["condominio_id"]);
        $dados["condominio_endereco"] = $dados_condominio["endereco"];
      }
      $dados["telefones"] = $this->buscaClientesTelefones($cliente_id);
      $dados["emails"] = $this->buscaClientesEmails($cliente_id);
      if ($dados["complemento"]) {
        $complemento_tipos = $this->buscaComplementoTipos();
        $dados["complemento_tipo_id"] = $dados["complemento"][0];
        $dados["complemento_tipo_nome"] = $complemento_tipos[$dados["complemento_tipo_id"]];
        $dados["complemento_valor"] = substr($dados["complemento"], 1, strlen($dados["complemento"]));
        if ($dados["complemento_tipo_id"] != "o") {
          $dados["complemento"] = $dados["complemento_tipo_nome"]." ".intval($dados["complemento_valor"]);
        } else {
          $dados["complemento"] = $dados["complemento_valor"];
        }
      }
      $dados["endereco"] = $this->geraEnderecoFormatado($dados);
      $dados["endereco_completo"] = $this->geraEnderecoFormatado($dados, true);

      return $dados;
    }
  }

  function buscaServicosCliente($cliente_id) {
  	$servicos = array();
    $query = "
      SELECT
        tab_planos.pla_nome AS plano_nome,
        tab_planos_servicos.pls_nome AS servico_nome
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_assinaturas.ast_id IN (2, 5) AND
        tab_planos.pls_id NOT IN (21)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["plano_nome"] = trim(preg_replace("/\\([^\\]]*\\)|\\[[^\\]]*\\]/", "", $dados["plano_nome"]));
      if (array_key_exists($dados["servico_nome"], $servicos) && array_key_exists($dados["plano_nome"], $servicos[$dados["servico_nome"]])) {
      $servicos[$dados["servico_nome"]][$dados["plano_nome"]] += 1;
      } else {
      	$servicos[$dados["servico_nome"]][$dados["plano_nome"]] = 1;
      }
    }
    if ($servicos){
	    foreach ($servicos as $planos_servicos => $planos) {
	    	$cliente_planos = array();
	      foreach ($planos as $plano => $quantidade) {
	        $cliente_planos[] = "($quantidade) $plano";
	      }
	      $cliente_servicos[] = "$planos_servicos: ".implode(", ", $cliente_planos);
	    }
	    return implode("; ", $cliente_servicos);
    }
  }
  
  function buscaAutenticacao($cliente_id){
  	$autenticacoes = array();
  	$query = "
	  SELECT
	  		tab_clientes_autenticacoes.cau_usuario AS usuario,
        tab_clientes_autenticacoes.cau_equipamento_ip AS equipamento_ip
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_assinaturas.ast_id IN (1, 2, 5) AND
        tab_clientes_autenticacoes.cas_id = 1";
  	$query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $autenticacoes[] = $dados["usuario"].($dados["equipamento_ip"] ? " ({$dados["equipamento_ip"]})" : "");
    }
	return implode("; ", $autenticacoes);
  }

  function buscaMac($cliente_id) {
  	    $query = "
	      SELECT
	        clp_endereco_mac AS mac
	      FROM
	        tab_assinaturas
	        INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
	      WHERE
	        tab_assinaturas.cli_id = $cliente_id AND
	        tab_assinaturas.ast_id IN (1, 2, 5) AND
	        tab_clientes_pontos.cps_id IN (1)";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      $macs[] = $dados["mac"];
	    }		  
    return $macs ? implode("; ", $macs) : "";
  }

  function buscaDadosCondominio($condominio_id) {
    $query = "
      SELECT
        tab_condominios.con_id AS id,
        CONCAT(tab_condominios.con_nome, ' (', tab_condominios_tipos.cnt_nome, ')') AS nome,
        tab_condominios.cep_id AS cep,
        tab_condominios.con_numero AS numero,
        DATE_FORMAT(tab_condominios.con_data_cadastro, '%d/%m/%Y') AS data_cadastro,
        DATE_FORMAT(tab_condominios.con_data_ativacao, '%d/%m/%Y') AS data_ativacao,
        tab_condominios_status.cos_id AS status_id,
        tab_condominios_status.cos_nome AS status_nome,
        tab_condominios_tipos.cnt_id AS tipo_id,
        tab_condominios_tipos.cnt_nome AS tipo_nome
      FROM
        tab_condominios
        INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
        INNER JOIN tab_condominios_tipos ON (tab_condominios.cnt_id = tab_condominios_tipos.cnt_id)
      WHERE
        con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["endereco"] = $this->buscaEndereco($dados["cep"], $dados["numero"]);
      return $dados;
    }
  }

  function buscaEndereco($cep, $numero) {
    $query = "
      SELECT
        tab_estados.est_sigla AS estado,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro
      FROM
        tab_ceps
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        tab_ceps.cep_id = $cep";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["logradouro"]) $endereco[] = $dados["logradouro"];
      if ($numero) $endereco[] = $numero;
      if ($dados["bairro"]) $endereco[] = $dados["bairro"];
      if ($dados["cidade"]) $endereco[] = $dados["cidade"];
      if ($dados["estado"]) $endereco[] = $dados["estado"];
      return implode(", ", $endereco);
    }
  }

  function buscaCondominioCliente($cliente_id) {
    $query = "
      SELECT
         con_id AS condominio_id
      FROM
        tab_clientes
      WHERE
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["condominio_id"];
    }
  }

  function buscaComplementoTipos($tipo = null) {
    $tipos["a"] = "Ap";
    $tipos["l"] = "Lj";
    $tipos["s"] = "Sl";
    $tipos["c"] = "Casa";
    $tipos["o"] = "";
    if ($tipo) {
      return $tipos[$tipo];
    } else {
      return $tipos;
    }
  }

  function buscaClientesTelefones($cliente_id) {
  	$lista = array();
    $query = "
      SELECT
        ccl_ramal AS ramal,
        CONCAT('(', ccl_ddd, ') ', ccl_numero) AS telefone
      FROM
        tab_clientes_contatos_telefones
      WHERE
        cli_id = $cliente_id AND
		ccf_id > 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $telefone = $dados["telefone"];
      if ($dados["ramal"]) $telefone .= " (Ramal {$dados["ramal"]})";
      $lista[] = $telefone;
    }
    return implode(" / ", $lista);
  }

  function buscaClientesEmails($cliente_id) {
  	$emails = array();
    $query = "
      SELECT
        CONCAT(ccm_usuario, '@', ccm_dominio) AS email
      FROM
        tab_clientes_contatos_emails
      WHERE
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[] = $dados["email"];
    }
    return implode("; ", $emails);
  }

  function geraEnderecoFormatado($endereco, $completo = false) {
    if (array_key_exists("condominio_nome", $endereco) && $endereco["condominio_nome"]) {
      $endereco_formatado = $completo ? $endereco["condominio"] : $endereco["condominio_nome"];
      if ($endereco["complemento"]) {
        $endereco_formatado .= ", ".$endereco["complemento"];
        if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único") $endereco_formatado .= " / ".$endereco["bloco_nome"];
      } else {
        if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único") $endereco_formatado .= ", ".$endereco["bloco_nome"];
      }
      if ($completo) {
      	$complemento = array();
        if ($endereco["logradouro"]) $complemento[] = $endereco["logradouro"];
        if ($endereco["numero"]) $complemento[] = $endereco["numero"];
        if ($endereco["bairro"]) $complemento[] = $endereco["bairro"];
        if ($endereco["cidade"]) $complemento[] = $endereco["cidade"];
      	if ($endereco["cep"]) $complemento[] = "CEP: {$endereco["cep"]}";
        $endereco_formatado .= " - ".implode(", ", $complemento);
      }
    } else {
    	$endereco_formatado = array();
      if ($endereco["logradouro"]) $endereco_formatado[] = $endereco["logradouro"];
      if ($endereco["numero"]) $endereco_formatado[] = $endereco["numero"];
      if ($endereco["complemento"]) $endereco_formatado[] = $endereco["complemento"];
      if ($endereco["bairro"]) $endereco_formatado[] = $endereco["bairro"];
      if ($endereco["cidade"]) $endereco_formatado[] = $endereco["cidade"];
      if ($endereco["cep"]) $endereco_formatado[] = "CEP: {$endereco["cep"]}";
      $endereco_formatado = implode(", ", $endereco_formatado);
    }
    return $endereco_formatado;
  }

  function buscaNiveisStress() {
  	$lista = array();
    $query = "
      SELECT
        cen_id AS id,
        cen_nome AS nome
      FROM
        tab_chamados_etapas_niveis_stress
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaPrioridades($prioridade_id = null) {
  	$lista = array();
    $query = "
      SELECT
        chp_id AS id,
        chp_nome AS nome
      FROM
        tab_chamados_prioridades
        ".($prioridade_id ? "WHERE chp_id = $prioridade_id" : "")."
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($prioridade_id) {
        return $dados["nome"];
      } else {
        $lista[$dados["id"]] = $dados["nome"];
      }
    }
    return $lista;
  }
  
  function buscaRetorno(){
  	return array (0=>" ", 1=>"Agendar Para", 2=>"Desagendar");
  }
  
  function buscaSituacao($situacao_id, $status_nome) {
    if ($situacao_id == 2) {
      $imagem = '<img src="imagens/seta_encaminhamento.png" width="13" height="13" border="0">';
    } elseif ($situacao_id == 3) {
      $imagem = '<img src="imagens/seta_retorno.png" width="13" height="13" border="0">';
    } else {
      $imagem = '<img src="imagens/seta_nao_encaminhamento.png" width="13" height="13" border="0">';
    }
    return '<table cellpadding="0" cellspacing="1" border="0"><tr class="txtAzulEscuro11"><td>'.$status_nome.'</td><td>'.$imagem.'</td></tr></table>';
  }

  function buscaIconeFavoritos($chamado_id, $listagem) {
    if ($this->ehChamadoFavorito($chamado_id, $_SESSION["usuario"])) {
      return '<div id="favoritos_'.($listagem ? $listagem.'_' : '').$chamado_id.'" width="13" height="13" style="float:right;margin-left:2px;cursor:pointer"><img src="imagens/favoritos_remover.png" id="favoritos_icone" width="13" height="13" border="0" title="Remover do Favoritos" onclick="favoritosRemover('.$chamado_id.')"></div>';
    } else {
      return '<div id="favoritos_'.($listagem ? $listagem.'_' : '').$chamado_id.'" width="13" height="13" style="float:right;margin-left:2px;cursor:pointer"><img src="imagens/favoritos_adicionar.png" id="favoritos_icone" width="13" height="13" border="0" title="Adicionar ao Favoritos" onclick="favoritosAdicionar('.$chamado_id.')"></div>';
    }
  }

  function buscaIconesChamado($dados_chamado) {
    // Icone Favoritos
    $icones[] = $this->buscaIconeFavoritos($dados_chamado["id"], "chamado");
    // Icone Aguardando Retorno
    if ($dados_chamado["aguardando_retorno"]) {
      $icones[] = '<img src="imagens/aguardando_retorno.png" width="11" height="14" border="0" style="float:right;margin-left:2px" title="Aguardando Retorno">';
    }
    // Icone Visualizações
    $visualizacao = $this->buscaIconeVisualizacoes($this->instancia, $dados_chamado["id"]);
    if ($visualizacao) {
      $icones[] = "<div id=\"chamados_visualizacao_{$dados_chamado["id"]}\" style=\"float:right;margin-left:2px\">".$visualizacao."</div>";
    }
    return implode("", $icones);
  }

  function buscaAndamentos($andamento) {
    for ($i = 0; $i <= 100; $i = $i + 10) {
      $lista[$i] = "$i%";
    }
    if (!array_key_exists($andamento, $lista)) {
      $lista[$andamento] = "$andamento%";
      ksort($lista);
    }
    return $lista;
  }

  function buscaChamadosPaiPossiveis($usuario_id, $cliente_id = null, $condominio_id = null) {
  	$lista = array();
    // Filtros
    if ($cliente_id && $condominio_id) {
      $where[] = "tab_chamados.con_id = $condominio_id";
    } elseif ($condominio_id) {
      $where[] = "tab_chamados.con_id IS NULL";
      $where[] = "tab_chamados.cli_id IS NULL";
    } else {
    	return null;
    }
    // Chamados Abertos
    $where[] = "tab_chamados.chs_id = 1";
    $where = implode(' AND ', $where);
    $query = "
      SELECT
        tab_chamados.cha_id AS id,
        tab_chamados.chc_id AS categoria_id,
        tab_chamados.cha_codigo AS codigo,
        tab_chamados.cli_id AS cliente_id,
        tab_chamados.con_id AS condominio_id,
        IF(tab_chamados.cli_id IS NOT NULL, tab_clientes.con_id, tab_chamados.con_id) AS cliente_condominio_id
      FROM
        tab_chamados
        LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
      WHERE
        $where
      ORDER BY
        codigo";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categoria = $this->buscaDadosCategoria($dados["categoria_id"]);
      $lista["{$dados["id"]},{$dados["cliente_id"]},{$dados["cliente_condominio_id"]},{$dados["condominio_id"]}"] = "{$dados["codigo"]} - {$categoria["nome"]}";
    }
    return $lista;
  }

  function buscaValorChamadoPai($chamado_id) {
  	if (!$chamado_id) {
  		return null;
  	}
    $query = "
      SELECT
        tab_chamados.cha_id AS id,
        tab_chamados.cli_id AS cliente_id,
        tab_chamados.con_id AS condominio_id,
        IF(tab_chamados.cli_id IS NOT NULL, tab_clientes.con_id, tab_chamados.con_id) AS cliente_condominio_id
      FROM
        tab_chamados
        LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
      WHERE
        tab_chamados.cha_id = $chamado_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return "{$dados["id"]},{$dados["cliente_id"]},{$dados["cliente_condominio_id"]},{$dados["condominio_id"]}";
    }
  }

  function buscaChamadosFilhoSimples($chamado_id) {
  	$dados = array();
    $query = "
      SELECT
        cha_id AS id,
        chc_id AS categoria_id
      FROM
        tab_chamados
      WHERE
        cha_pai = $chamado_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $itens[] = $dados;
    }
    return $itens;
  }

  function buscaEtapasFilhas($etapa_id) {
  	$itens = array();
    $query = "
      SELECT
        che_id AS id
      FROM
        tab_chamados_etapas
      WHERE
        che_pai = $etapa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $itens[] = $dados["id"];
    }
    return $itens;
  }

  function buscaCondominioTipo($tipo_id) {
    $query = "
      SELECT
        cnt_nome
      FROM
        tab_condominios_tipos
      WHERE
        cnt_id = $tipo_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  function buscaCondominiosTipos() {
  	$tipos = array();
    $query = "
      SELECT
        cnt_id AS id,
        cnt_nome AS nome
      FROM
        tab_condominios_tipos
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaChamadosFilho($chamado_id) {
  	$itens = array();
    $query = "
      SELECT DISTINCT
        tab_chamados.cha_id AS chamado_id,
        tab_chamados.cha_codigo AS codigo,
        tab_chamados.cha_solicitante AS solicitante,
        tab_chamados.chc_id AS categoria_id,
        tab_chamados.ors_id AS setor_id,
        tab_chamados.orf_id AS funcao_id,
        tab_chamados.usu_id AS usuario_id,
        tab_chamados.cli_id AS cliente_id,
        tab_chamados.con_id AS condominio_id,
        tab_chamados.cha_andamento AS andamento,
        tab_chamados.chi_id AS situacao_id,
        tab_chamados.cha_aguardando_retorno AS aguardando_retorno,
        tab_clientes.cli_nome AS cliente,
        tab_clientes.clt_id AS pessoa_tipo_id,
        tab_condominios.con_nome AS condominio,
        tab_condominios.con_id AS cliente_condominio_id,
        tab_condominios.cnt_id AS condominio_tipo_id,
        DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
        DATE_FORMAT(tab_chamados.cha_datahora_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
        DATE_FORMAT(tab_chamados.cha_datahora_encaminhamento, '%d/%m/%Y (%H:%i)') AS encaminhamento,
        tab_chamados_status.chs_id AS status_id,
        tab_chamados_status.chs_nome AS status_nome,
        tab_chamados_prioridades.chp_id AS prioridade_id,
        tab_chamados_prioridades.chp_nome AS prioridade
      FROM
        tab_chamados
        LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios ON (IF(tab_chamados.cli_id IS NOT NULL, tab_clientes.con_id, tab_chamados.con_id) = tab_condominios.con_id)
        INNER JOIN tab_chamados_status ON (tab_chamados_status.chs_id = tab_chamados.chs_id)
        INNER JOIN tab_chamados_prioridades ON (tab_chamados_prioridades.chp_id = tab_chamados.chp_id)
      WHERE
        tab_chamados.cha_pai = $chamado_id
      ORDER BY
        tab_chamados_status.chs_id,
        tab_chamados.cha_datahora_encaminhamento";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categoria = $this->buscaDadosCategoria($dados["categoria_id"]);
      $dados["categoria"] = $categoria["nome"];
      if ($dados["status_id"] == 1) {
        $dados["status"] = $this->buscaSituacao($dados["situacao_id"], $dados["status_nome"]).$this->buscaPrioridade($dados["prioridade_id"]).$this->buscaAndamento($dados["andamento"]);
      } else {
        $dados["status"] = $dados["status_nome"].$this->buscaAndamento($dados["andamento"]);
      }
      $responsavel = array();
      if ($dados["setor_id"]) $responsavel[] = $this->buscaSetor($dados["setor_id"]);
      if ($dados["funcao_id"]) $responsavel[] = $this->buscaFuncao($dados["funcao_id"]);
      if ($dados["usuario_id"]) $responsavel[] = $this->buscaUsuario($dados["usuario_id"]);
      $dados["responsavel"] = implode(" » ", $responsavel);
      $dados["duracao"] = $this->formataDuracao($dados["abertura"], $dados["fechamento"]);
      $dados["duracao_setor"] = $this->formataDuracao($dados["encaminhamento"], $dados["fechamento"]);
      $dados["datas"] = array();
      if ($dados["abertura"]) $dados["datas"][] = "A: {$dados["abertura"]}";
      if ($dados["status_id"] == 1 && $dados["situacao_id"] != 1) $dados["datas"][] = "E: {$dados["encaminhamento"]}";
      if ($dados["fechamento"]) $dados["datas"][] = "F: {$dados["fechamento"]}";
      if ($dados["status_id"] == 1) $dados["datas"][] = "<span style=\"color:#232323\">({$dados["duracao_setor"]})</span>";
      if ($dados["status_id"] == 2) $dados["datas"][] = "<span style=\"color:#232323\">({$dados["duracao"]})</span>";
      $dados["datas"] = '<div nowrap>'.implode('<br>', $dados["datas"]).'</div>';
      // Dados Chamado
      $dados["dados"] = array();
      $dados["dados"][] = "<b>Código:</b> {$dados["codigo"]}";
      if ($dados["cliente_id"]) {
        $dados["cliente"] = strlen($dados["cliente"]) > 35 ? substr($dados["cliente"], 0, 35)."..." : $dados["cliente"];
        $dados["dados"][] = "<b>Cliente:</b> {$dados["cliente"]}";
      } elseif ($dados["condominio_id"]) {
        $dados["condominio"] = strlen($dados["condominio"]) > 35 ? substr($dados["condominio"], 0, 35)."..." : $dados["condominio"];
        $dados["dados"][] = "<b>Condomínio:</b> {$dados["condominio"]}";
      }
      $dados["dados"][] = "<b>Categoria:</b> {$dados["categoria"]}";
      // Icone Wave Garantia
      if ($dados["cliente_id"]) {
        $dados["dados"][1] = '<div style="float:right;margin-left:2px">'.$this->buscaWaveGarantia($dados["cliente_id"]).'</div>'.$dados["dados"][1];
      }
      // Icone Cliente
      $hint = $this->formataHintChamado($dados);
      if ($dados["cliente_id"]) {
        if ($dados["condominio_tipo_id"] == 1) {
          $cliente_tipo = "residencial";
        } elseif ($dados["condominio_tipo_id"] == 2) {
          $cliente_tipo = "comercial";
        } else {
          if ($dados["pessoa_tipo_id"] == 1) {
            $cliente_tipo = "residencial";
          } else {
            $cliente_tipo = "comercial";
          }
        }
        $dados["dados"][1] = '<img src="imagens/cliente_'.$cliente_tipo.'.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      } elseif ($dados["condominio_id"]) {
        $dados["dados"][1] = '<img src="imagens/condominio.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      } else {
        $dados["dados"][1] = '<img src="imagens/empresa.png" width="13" height="13" border="0" style="float:right;margin-left:2px" onmouseover="hintShow(\''.$hint["conteudo"].'\', \''.$hint["largura"].'\')" onmouseout="hintHide()">'.$dados["dados"][1];
      }
      $dados["dados"] = implode("<br>", $dados["dados"]);
      // Icone Visualização
      $dados["dados"] = "<div id=\"chamados_filho_visualizacao_{$dados["chamado_id"]}\" style=\"float:right;margin-left:2px\">".$this->buscaIconeVisualizacoes($this->instancia, $dados["chamado_id"])."</div>".$dados["dados"];
      // Icone Aguardando Retorno
      if ($dados["aguardando_retorno"]) {
        $dados["dados"] = '<img src="imagens/aguardando_retorno.png" width="11" height="14" border="0" style="float:right;margin-left:2px" title="Aguardando Retorno">'.$dados["dados"];
      }
      // Icone Favoritos
      $dados["dados"] = $this->buscaIconeFavoritos($dados["chamado_id"], "filho").$dados["dados"];
      // Link
      $dados["link"] = "javascript:verChamado('{$dados["chamado_id"]}', '{$dados["cliente_id"]}', '{$dados["cliente_condominio_id"]}', '{$dados["condominio_id"]}', 1, event)";
      $dados["id"] = "chamados_filho_{$dados["chamado_id"]}";
      $itens[] = $dados;
    }
    return $itens;
  }

  function formataHintChamado($dados) {
    $hint["Código"] = strip_tags($dados["codigo"]);
    if ($dados["cliente_id"]) {
      $hint["Cliente"] = $dados["cliente"];
    }
    if ($dados["condominio_id"] || $dados["cliente_condominio_id"]) {
      $hint["Condomínio"] = "{$dados["condominio"]} (".$this->buscaCondominioTipo($dados["condominio_tipo_id"]).")";
    }
    if ($dados["solicitante"]) {
      $hint["Solicitante"] = $dados["solicitante"];
    }
    $hint["Categoria"] = $dados["categoria"];
    $hint["Responsável"] = $dados["responsavel"];
    $hint["Prioridade"] = $dados["prioridade"];
    $hint["Andamento"] = "{$dados["andamento"]}%";
    if ($dados["abertura"]) {
      $hint["Abertura"] = "{$dados["abertura"]} por ".$this->buscaUsuarioEtapa($dados["chamado_id"], 1, "usuario_nome");
    }
    if ($dados["encaminhamento"]) {
      $hint["Encaminhamento"] = "{$dados["encaminhamento"]} por ".$this->buscaUsuarioEtapa($dados["chamado_id"], 3, "usuario_nome");
    }
    if ($dados["fechamento"]) {
      $hint["Fechamento"] = "{$dados["fechamento"]} por ".$this->buscaUsuarioEtapa($dados["chamado_id"], 6, "usuario_nome");
    }
    $hint["Duração"] = ($dados["duracao_setor"] != $dados["duracao"] && $dados["status_id"] == 1 ? " {$dados["duracao_setor"]} ({$dados["duracao"]})" : $dados["duracao"]);
    $hint["Status"] = $dados["status_nome"];
    $ultima_etapa = $this->buscaUltimaEtapaChamado($dados["chamado_id"]);
    if ($ultima_etapa) {
      $hint[] = "Última Etapa:";
      $hint[] = $ultima_etapa;
    }
    $hint["conteudo"] = $this->geraTabelaHint($hint);
    $hint["largura"] = "430";
    return $hint;
  }

  function geraTabelaHint($hint) {
    $conteudo = '<table width="100%" cellpadding="1" cellspacing="1" border="0" bgcolor="#4286B7">';
    foreach ($hint as $descricao => $valor) {
      $conteudo .= "<tr bgcolor=\"#505050\" class=\"txtBranco10\">";
      if (!is_numeric($descricao)) {
        $conteudo .= "<td style=\"width:90px\">$descricao:</td>";
        $conteudo .= "<td>$valor</td>";
      } else {
        $conteudo .= "<td colspan=\"2\">$valor</td>";
      }
      $conteudo .= "</tr>";
    }
    $conteudo .= '</table>';
    $conteudo = preg_replace('/\s+/', ' ', $conteudo);
    $conteudo = preg_replace('/\\\\?[\'"]/', '\\\'', $conteudo);
    return $conteudo;
  }

  function buscaPrioridade($prioridade_id) {
    $prioridade = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" title=\"Prioridade: ".$this->buscaPrioridades($prioridade_id)."\"><tr>";
    for ($i = 1; $i <= 5; $i++) {
      $prioridade .= "<td><img src=\"imagens/estrela_".($i <= $prioridade_id ? "amarela" : "cinza").".png\" width=\"9\" height=\"10\" border=\"0\"></td>";
    }
    $prioridade .= "</tr></table>";
    return $prioridade;
  }

  function buscaAndamento($andamento_valor) {
    $etapas = 8;
    $cor_vazio = '#DDDDDD';
    $cor_cheio = 'green';
    $etapa = round($etapas * $andamento_valor / 100);
    $andamento  = '<div title="Andamento: '.$andamento_valor.'%" style="width:41px;height:8px;background-color:#FFFFFF;border:1px solid;margin-top:4px;margin-bottom:1px;padding:0px">';
    $andamento  .= '<table height="8" width="41" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF" border="0"><tr>';
    for ($i = 1; $i <= $etapas; $i++) {
      $andamento .= '<td bgcolor="'.($i <= $etapa ? $cor_cheio : $cor_vazio).'"></td>';
    }
    $andamento .= '</tr></table>';
    $andamento .= '</div>';
    return $andamento;
  }

  function geraListagemChamados($itens) {
    $cabecalho = array ("dados" => "Dados", "datas" => "Datas/Duração", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 110, 50));
    return $listagem->conteudo;
  }

  function buscaDadosCategoriaEtapa($categoria_id, $etapa_tipo_id) {
    $categoria_id = $categoria_id ? $categoria_id : "%";
    $query = "
      SELECT
        tab_chamados_categorias_etapas.cce_id AS id,
        tab_chamados_categorias_etapas.ors_id AS setor_id,
        tab_chamados_categorias_etapas.orf_id AS funcao_id,
        tab_chamados_categorias_etapas.cce_obrigatoria AS obrigatoria,
        tab_chamados_categorias.chc_pai AS pai
      FROM
        tab_chamados_categorias
        LEFT OUTER JOIN tab_chamados_categorias_etapas ON (tab_chamados_categorias_etapas.chc_id = tab_chamados_categorias.chc_id AND tab_chamados_categorias_etapas.cet_id = $etapa_tipo_id)
      WHERE
        tab_chamados_categorias.chc_id LIKE '$categoria_id'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["id"]) {
        return $dados;
      } elseif ($dados["pai"]) {
        return $this->buscaDadosCategoriaEtapa($dados["pai"], $etapa_tipo_id);
      }
    }
  }

  function buscaCategoriaTipoOrdem($categoria_id, $etapa_tipo_id) {
    $query = "
      SELECT
        cce_ordem
      FROM
        tab_chamados_categorias_etapas
      WHERE
        chc_id = $categoria_id AND
        cet_id = $etapa_tipo_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  function buscaCategoriasAbaixo($categoria_id) {
  	$categorias = array();
    $query = "
      SELECT
        chc_id AS categoria_id
      FROM
        tab_chamados_categorias
      WHERE
        chc_pai = $categoria_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categorias[] = $dados["categoria_id"];
      $categorias_abaixo = $this->buscaCategoriasAbaixo($dados["categoria_id"]);
      if ($categorias_abaixo) {
        $categorias = funcoes::mescla_arrays($categorias, $categorias_abaixo);
      }
    }
    return $categorias;
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

  function buscaFuncao($funcao_id) {
    $query = "
      SELECT
        orf_nome
      FROM
        tab_organizacao_funcoes
      WHERE
        orf_id = $funcao_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  function buscaUsuario($usuario_id) {
    $query = "
      SELECT
        usu_nome
      FROM
        tab_usuarios
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  function ehSetorUsuario($setor_id, $usuario_id) {
    $query = "
      SELECT
        tab_organizacao_setores.ors_id AS setor_id,
        rel_usu_orf_ors_exerce.usu_id AS usuario_id,
        setor_filho.ors_id AS setor_filho
      FROM
        tab_organizacao_setores
        LEFT OUTER JOIN rel_usu_orf_ors_exerce ON (rel_usu_orf_ors_exerce.ors_id = tab_organizacao_setores.ors_id AND rel_usu_orf_ors_exerce.usu_id = $usuario_id)
        LEFT OUTER JOIN tab_organizacao_setores setor_filho ON (setor_filho.ors_pai = tab_organizacao_setores.ors_id)
      WHERE
        tab_organizacao_setores.ors_id = $setor_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["usuario_id"]) {
        return true;
      } elseif ($dados["setor_filho"] && $this->ehSetorUsuario($dados["setor_filho"], $usuario_id)) {
        return true;
      }
    }
    return false;
  }

  function buscaResponsaveisSetor($usuario_id) {
    $query = "
      SELECT
        ors_id AS setor_id,
        orf_id AS funcao_id
      FROM
        rel_usu_orf_ors_exerce
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    $responsaveis = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if (!array_key_exists("{$dados["setor_id"]},,", $responsaveis)) {
        $responsaveis["{$dados["setor_id"]},,"] = $this->buscaSetor($dados["setor_id"]);
      }
      if (!array_key_exists("{$dados["setor_id"]},{$dados["funcao_id"]},", $responsaveis)) {
        $responsaveis["{$dados["setor_id"]},{$dados["funcao_id"]},"] = $this->buscaSetor($dados["setor_id"])." » ".$this->buscaFuncao($dados["funcao_id"]);
      }
      $responsaveis["{$dados["setor_id"]},{$dados["funcao_id"]},$usuario_id"] = $this->buscaSetor($dados["setor_id"])." » ".$this->buscaFuncao($dados["funcao_id"])." » ".$this->buscaUsuario($usuario_id);
    }
    return $responsaveis;
  }

  function buscaResponsaveisTipos() {
  	$responsaveis_tipos = array();
    $responsaveis_tipos["1"] = "Responsável";
    $responsaveis_tipos["2"] = "Participante";
    $responsaveis_tipos["3"] = "Abertura";
    $responsaveis_tipos["4"] = "Fechamento";
    $responsaveis_tipos["5"] = "S/ Responsável";
    $responsaveis_tipos["6"] = "Encaminhamento";
    return $responsaveis_tipos;
  }

  function buscaDataTipos() {
  	$data_tipos = array();
    $data_tipos["1"] = "Data Abertura";
    $data_tipos["2"] = "Data Fechamento";
    $data_tipos["3"] = "Data Participação";
    $data_tipos["6"] = "Data Encaminhamento";
    return $data_tipos;
  }

  function buscaResponsaveis($categoria_id = null, $responsavel_atual_id = null, $responsavel_atual_nome = null) {
    if ($categoria_id) {
    	$responsavel_nome = array();
      $dados = $this->buscaDadosCategoriaEtapa($categoria_id, 3);
      $responsavel_id[] = (array_key_exists("setor_id", $dados) && $dados["setor_id"]) ? $dados["setor_id"] : "0";
      $responsavel_id[] = (array_key_exists("funcao_id", $dados) && $dados["funcao_id"]) ? $dados["funcao_id"] : "0";
      $responsavel_id[] = (array_key_exists("usuario_id", $dados) && $dados["usuario_id"]) ? $dados["usuario_id"] : "0";
      if (array_key_exists("setor_id", $dados) && $dados["setor_id"]) $responsavel_nome[] = $this->buscaSetor($dados["setor_id"]);
      if (array_key_exists("funcao_id", $dados) && $dados["funcao_id"]) $responsavel_nome[] = $this->buscaFuncao($dados["funcao_id"]);
      if (array_key_exists("usuario_id", $dados) && $dados["usuario_id"]) $responsavel_nome[] = $this->buscaUsuario($dados["usuario_id"]);
      $responsavel_categoria[implode(",", $responsavel_id)] = implode(" » ", $responsavel_nome);
      $responsaveis = funcoes::mescla_arrays($this->buscaDestinosAcima($dados["setor_id"], $dados["funcao_id"]), $responsavel_categoria, $this->buscaDestinosAbaixo($dados["setor_id"], $dados["funcao_id"] = null));
      if ($responsavel_atual_id && !$responsaveis[$responsavel_atual_id]) {
        $responsaveis[$responsavel_atual_id] = $responsavel_atual_nome;
      }
    } else {
    	$setores = $this->buscaSetores();
      if ($setores) {
        foreach ($setores as $setor_id => $setor_nome) {
          $responsaveis["$setor_id,0,0"] = $setor_nome;
          $responsaveis = funcoes::mescla_arrays($responsaveis, $this->buscaDestinosAbaixo($setor_id));
        }
      }
    }
    asort($responsaveis);
    return $responsaveis;
  }

  function buscaDestinosAcima($setor_id, $funcao_id) {
  	$destinos = array();
    if ($funcao_id) {
      $ultima_funcao = $funcao_id;
    } else {
    	$funcoes = $this->buscaFuncoesDisponiveis($setor_id);
      if ($funcoes) {
        $ultima_funcao = key($funcoes);
      }
    }
    while (($setor_id = $this->buscaSetorPai($setor_id)) && ($ultima_funcao < 4)) {
      $setor_nome = $this->buscaSetor($setor_id);
      $funcoes = $this->buscaFuncoesDisponiveis($setor_id);
      if ($funcoes) {
        foreach ($funcoes as $funcao_id => $funcao_nome) {
          if ($funcao_id > $ultima_funcao) {
            $ultima_funcao = $funcao_id;
            $destinos["$setor_id,$funcao_id,0"] = "$setor_nome » $funcao_nome";
            if ($funcao_id != 4) {
            	$usuarios = $this->buscaUsuarios($setor_id, $funcao_id);
              if ($usuarios) {
                foreach ($usuarios as $usuario_id => $usuario_nome) {
                  $destinos["$setor_id,$funcao_id,$usuario_id"] = "$setor_nome » $funcao_nome » $usuario_nome";
                }
              }
            }
          }
        }
      }
    }
    asort($destinos);
    return $destinos;
  }

  function buscaDestinosAbaixo($setor_id, $funcao_id = null) {
  	$destinos = array();
    if ($funcao_id && $funcao_id != 4) {
    	$usuarios = $this->buscaUsuarios($setor_id, $funcao_id);
      if ($usuarios) {
        $setor_nome = $this->buscaSetor($setor_id);
        $funcao_nome = $this->buscaFuncao($funcao_id);
        foreach ($usuarios as $usuario_id => $usuario_nome) {
          $destinos["$setor_id,$funcao_id,$usuario_id"] = "$setor_nome » $funcao_nome » $usuario_nome";
        }
      }
    } else {
    	$funcoes = $this->buscaFuncoesDisponiveis($setor_id);
      if ($funcoes) {
        $setor_nome = $this->buscaSetor($setor_id);
        foreach ($funcoes as $funcao_id => $funcao_nome) {
          $destinos["$setor_id,$funcao_id,0"] = "$setor_nome » $funcao_nome";
          if ($funcao_id != 4) {
          	$usuarios = $this->buscaUsuarios($setor_id, $funcao_id);
            if ($usuarios) {
              foreach ($usuarios as $usuario_id => $usuario_nome) {
                $destinos["$setor_id,$funcao_id,$usuario_id"] = "$setor_nome » $funcao_nome » $usuario_nome";
              }
            }
          }
        }
      }
      $setores_filhos = $this->buscaSetores($setor_id);
      if ($setores_filhos) {
        foreach ($setores_filhos as $setor_id => $setor_nome) {
          $destinos["$setor_id,0,0"] = $setor_nome;
          $funcoes = $this->buscaFuncoesDisponiveis($setor_id);
          if ($funcoes) {
            foreach ($funcoes as $funcao_id => $funcao_nome) {
              $destinos["$setor_id,$funcao_id,0"] = "$setor_nome » $funcao_nome";
              if ($funcao_id != 4) {
              	$usuarios = $this->buscaUsuarios($setor_id, $funcao_id);
                if ($usuarios) {
                  foreach ($usuarios as $usuario_id => $usuario_nome) {
                    $destinos["$setor_id,$funcao_id,$usuario_id"] = "$setor_nome » $funcao_nome » $usuario_nome";
                  }
                }
              }
            }
          }
        }
      }
    }
    return $destinos;
  }

  function buscaSetorPai($setor_id) {
    $query = "
      SELECT
        ors_pai AS pai
      FROM
        tab_organizacao_setores
      WHERE
        ors_id = $setor_id AND
        ors_pai IS NOT NULL";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
  }

  function buscaSetores($setor_id = null) {
  	$setores = array();
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
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $setores[$dados["id"]] = $this->buscaSetor($dados["id"]);
      $filhos = $this->buscaSetores($dados["id"]);
      if ($filhos) {
        $setores = array_merge($setores, $filhos);
      }
    }
    asort($setores);
    return $setores;
  }

  function buscaFuncoes() {
  	$funcoes = array();
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

  function buscaFuncoesDisponiveis($setor_id) {
  	$funcoes = array();
    $query = "
      SELECT DISTINCT
        tab_organizacao_funcoes.orf_id AS id,
        tab_organizacao_funcoes.orf_nome AS nome
      FROM
        tab_organizacao_funcoes
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_organizacao_funcoes.orf_id = rel_usu_orf_ors_exerce.orf_id)
      WHERE
        rel_usu_orf_ors_exerce.ors_id = $setor_id
      ORDER BY
        id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $funcoes[$dados["id"]] = $dados["nome"];
    }
    return $funcoes;
  }

  function buscaUsuarios($setor_id, $funcao_id) {
  	$usuarios = array();
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
      WHERE
        ors_id = $setor_id AND
        orf_id = $funcao_id AND
        uss_id = 1
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $usuarios[$dados["id"]] = $dados["nome"];
    }
    return $usuarios;
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

  function ehResponsavelChamado($chamado_id, $usuario_id, $particular = false, $ignorar_plus = false) {
  	if (!$ignorar_plus && $this->temPermissao("responsabilidade_plus")) {
  		return true;
  	}
  	$responsabilidades = $this->buscaResponsabilidadesUsuario($usuario_id);
    $eh_responsavel = array();
    $eh_responsavel[] = "(tab_chamados.usu_id = $usuario_id)";
    foreach ($responsabilidades as $responsabilidade) {
      $eh_responsavel[] = "(tab_chamados.ors_id = {$responsabilidade["setor_id"]} AND (IFNULL(tab_chamados.orf_id, 0) < {$responsabilidade["funcao_id"]} OR (tab_chamados.orf_id = {$responsabilidade["funcao_id"]} AND (tab_chamados.usu_id IS NULL OR (SELECT uss_id FROM tab_usuarios WHERE tab_usuarios.usu_id = tab_chamados.usu_id) = 2))))";
    }
    $eh_responsavel = "IFNULL(".implode(" OR ", $eh_responsavel).", 0)";
    $query = "
      SELECT
        $eh_responsavel AS eh_responsavel
      FROM
        tab_chamados
      WHERE
        tab_chamados.cha_id = $chamado_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 0);
  }

  function existeChamadosAguardandoAgendamento($usuario_id) {
  	$responsabilidades = $this->buscaResponsabilidadesUsuario($usuario_id);
    $where_responsabilidades = array();
    $where_responsabilidades[] = "(tab_chamados.usu_id = $usuario_id)";
    foreach ($responsabilidades as $responsabilidade) {
      $where_responsabilidades[] = "(tab_chamados.ors_id = {$responsabilidade["setor_id"]} AND (IFNULL(tab_chamados.orf_id, 0) < {$responsabilidade["funcao_id"]} OR (tab_chamados.orf_id = {$responsabilidade["funcao_id"]} AND (tab_chamados.usu_id IS NULL OR (SELECT uss_id FROM tab_usuarios WHERE tab_usuarios.usu_id = tab_chamados.usu_id) = 2))))";
    }
    $where[] = "(".implode(" OR ", $where_responsabilidades).")";
    $where[] = "tab_chamados.cha_aguardando_retorno = 1";
    $where[] = "tab_chamados.cha_datahora_retorno <= NOW()";
    $where = implode(' AND ', $where);
    $query = "
      SELECT
        COUNT(cha_id) AS chamados
      FROM
        tab_chamados
      WHERE
        $where";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query) > 0;
  }
  
  function ehResponsavelCategoria($categoria_id, $usuario_id) {
    $origem_categoria = $this->buscaDadosCategoriaEtapa($categoria_id, 3);
    $responsabilidades = $this->buscaResponsabilidadesUsuario($usuario_id);
    if ($responsabilidades) {
      foreach ($responsabilidades as $responsabilidade) {
        if ($responsabilidade["setor_id"] == $origem_categoria["setor_id"] && (!$origem_categoria["funcao_id"] || $origem_categoria["funcao_id"] <= $responsabilidade["funcao_id"])) {
          return true;
        }
      }
    }
    return false;
  }

  function possuiFilhoAberto($chamado_id) {
    $query = "
      SELECT
        COUNT(cha_id) AS chamados

      FROM
        tab_chamados
      WHERE
        cha_pai = $chamado_id AND
        chs_id = 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 0) > 0;
    }

  function buscaUsuariosAcompanhamentos($chamado_id) {
  	$lista = array();
    $query = "
      SELECT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome,
        tab_usuarios.usu_email AS email
      FROM
        rel_cha_usu_ehacompanhado
        INNER JOIN tab_usuarios ON (rel_cha_usu_ehacompanhado.usu_id = tab_usuarios.usu_id)
      WHERE
        cha_id = $chamado_id AND
        usu_email != ''";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista [] = $dados;
    }
    return $lista;
  }

  function buscaDadosUsuario($usuario_id) {
    $query = "
      SELECT
        usu_id AS id,
        usu_nome AS nome,
        usu_email AS email
      FROM
        tab_usuarios
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
    }

  function buscaResponsabilidadesUsuario($usuario_id) {
  	$responsabilidades = array();
    $query = "
      SELECT
        ors_id AS setor_id,
        orf_id AS funcao_id
      FROM
        rel_usu_orf_ors_exerce
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsabilidades[] = array("setor_id" => $dados["setor_id"], "funcao_id" => $dados["funcao_id"], "usuario_id" => $usuario_id);
      $setores_abaixo = $this->buscaSetoresAbaixo($dados["setor_id"]);
      foreach ($setores_abaixo as $setor_id) {
        $responsabilidades[] = array("setor_id" => $setor_id, "funcao_id" => $dados["funcao_id"], "usuario_id" => $usuario_id);
      }
    }
    return $responsabilidades;
  }

  function buscaSetoresAbaixo($setor_id) {
  	$setores = array();
    $query = "
      SELECT
        ors_id AS setor_id
      FROM
        tab_organizacao_setores
      WHERE
        ors_pai = $setor_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $setores[] = $dados["setor_id"];
      $setores_abaixo = $this->buscaSetoresAbaixo($dados["setor_id"]);
      if ($setores_abaixo) {
        $setores = funcoes::mescla_arrays($setores, $setores_abaixo);
      }
    }
    return $setores;
  }

  function buscaWaveGarantia($cliente_id, $completo = false) {
    if ($this->ehClienteWaveGarantia($cliente_id)) {
      return '<img src="imagens/accept.png" width="13" height="13" border="0" title="Com Olé Garantia">';
    } else {
      if ($completo) {
        return '--';
      } else {
        return '';
      }
    }
  }

  function ehClienteWaveGarantia($cliente_id) {
    $query = "
      SELECT
        COUNT(cor_id)
     FROM
        tab_contratos
      WHERE
        cli_id = $cliente_id AND
        pls_id = 21 AND
        crs_id IN (19, 20, 21)";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query));
  }

  function buscaIconeVisualizacoes($instancia, $chamado_id, $retorna_usuarios = false) {
  	$visualizacoes = $this->verificaChamadoVisualizacoes($instancia, $chamado_id);
    if ($visualizacoes) {
      if ($visualizacoes["usuarios"]) {
        if ($retorna_usuarios) {
          $dados["icone"] = '<img src="imagens/visualizacao.png" style="cursor:pointer" width="13" height="13" border="0" onmouseover="hintShow(\''.implode("<hr>", $visualizacoes["usuarios"]).'\')" onmouseout="hintHide()">';
          $dados["usuarios"] = $visualizacoes["usuarios"];
        } else {
          $dados = '<img src="imagens/visualizacao.png" style="cursor:pointer" width="13" height="13" border="0" onmouseover="hintShow(\''.implode("<hr>", $visualizacoes["usuarios"]).'\')" onmouseout="hintHide()">';
        }
      } else {
        $dados = '';
      }
    } else {
      $dados = '';
    }
    return $dados;
  }

  function verificaChamadoVisualizacoes($instancia, $chamado_id) {
    // Limpa Registros Expirados (> 1 min)
    $query = "DELETE FROM tab_chamados_visualizacoes WHERE chv_data_visualizacao_ultima < DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
    $this->bancodedados->query($query);
    // Verifica se outro usuário/instancia está lendo o chamado
    $visualizacoes = array();
    $query = "
      SELECT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome,
        DATE_FORMAT(tab_chamados_visualizacoes.chv_data_visualizacao_inicio, '%d/%m/%Y (%H:%i:%s)') AS inicio
      FROM
        tab_chamados_visualizacoes
        INNER JOIN tab_usuarios ON (tab_chamados_visualizacoes.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_chamados_visualizacoes.cha_id = $chamado_id AND
        (tab_chamados_visualizacoes.usu_id != {$_SESSION["usuario"]} OR
         tab_chamados_visualizacoes.chv_instancia != '$instancia')
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $visualizacoes["usuarios"][] = "Usuário: {$dados["nome"]}<br>Desde: {$dados["inicio"]}";
    }
    return $visualizacoes;
  }

  function registraChamadoVisualizacao($instancia, $chamado_id = null) {
    if ($chamado_id) {
      // Procura registro existente
      $query = "
        SELECT
          chv_id AS id,
          cha_id AS chamado_id
        FROM
          tab_chamados_visualizacoes
        WHERE
          usu_id = {$_SESSION["usuario"]} AND
          chv_instancia = '$instancia'";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
    	if ($dados) {
        // Atualiza registro
        $query = "
          UPDATE
            tab_chamados_visualizacoes
          SET
            cha_id = $chamado_id,
            chv_data_visualizacao_inicio = ".($dados["chamado_id"] == $chamado_id ? "chv_data_visualizacao_inicio" : "NOW()").",
            chv_data_visualizacao_ultima = NOW()
          WHERE
            chv_id = {$dados["id"]}";
        $this->bancodedados->query($query);
      } else {
        // Insere registro
        $query = "
          INSERT INTO
            tab_chamados_visualizacoes (usu_id, cha_id, chv_instancia, chv_data_visualizacao_inicio, chv_data_visualizacao_ultima)
          VALUES
            ({$_SESSION["usuario"]}, $chamado_id, '$instancia', NOW(), NOW())";
        $this->bancodedados->query($query);
      }
    } else {
      // Remove todos os registros do usuário/instância
      $query = "DELETE FROM tab_chamados_visualizacoes WHERE usu_id = {$_SESSION["usuario"]} AND chv_instancia = '$instancia'";
      $this->bancodedados->query($query);
    }
  }

  function possuiPermissaoSolicitacaoFechamento($chamado_id, $usuario_id) {
    $query = "
      SELECT
        che_conteudo AS conteudo
      FROM
        tab_chamados_etapas
      WHERE
        cha_id = $chamado_id AND
        cet_id = 16";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      $responsabilidades = $this->buscaResponsabilidadesUsuario($usuario_id);
      if ($responsabilidades) {
        foreach ($responsabilidades as $responsabilidade) {
          if ($dados["conteudo"]["parametros"]["usuario_id"] && ($responsabilidade["usuario_id"] == $dados["conteudo"]["parametros"]["usuario_id"])) {
            return true;
          } elseif ($responsabilidade["setor_id"] == $dados["conteudo"]["parametros"]["setor_id"]) {
            if (!$dados["conteudo"]["parametros"]["funcao_id"] || ($responsabilidade["funcao_id"] == $dados["conteudo"]["parametros"]["funcao_id"])) {
              if (!$dados["conteudo"]["parametros"]["usuario_id"] || ($responsabilidade["usuario_id"] == $dados["conteudo"]["parametros"]["usuario_id"])) {
                return true;
              }
            }
          }
        }
      }
      return false;
    } else {
      return true;
    }
  }

  function buscaResponsavelFechamento($chamado_id, $tipo = 'nome') {
    $query = "
      SELECT
        che_conteudo AS conteudo
      FROM
        tab_chamados_etapas
      WHERE
        cha_id = $chamado_id AND
        cet_id = 16";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["conteudo"] = unserialize($dados["conteudo"]);
      $parametros = $dados["conteudo"]["parametros"];
      if ($tipo == 'nome') {
        if ($parametros["setor_id"]) $responsavel_nome[] = $this->buscaSetor($parametros["setor_id"]);
        if ($parametros["funcao_id"]) $responsavel_nome[] = $this->buscaFuncao($parametros["funcao_id"]);
        if ($parametros["usuario_id"]) $responsavel_nome[] = $this->buscaUsuario($parametros["usuario_id"]);
        return implode(" » ", $responsavel_nome);
      } elseif ($tipo == 'id') {
        return "{$parametros["setor_id"]},{$parametros["funcao_id"]},{$parametros["usuario_id"]}";
      }
    }
  }

  function ehChamadoFavorito($chamado_id, $usuario_id) {
    $query = "
      SELECT
        cha_id AS id
      FROM
        rel_cha_usu_ehfavorito
      WHERE
        cha_id = $chamado_id AND
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->linhas($query);
  }

  #############################
  ### Envia Acompanhamentos ###
  #############################

  function enviaAcompanhamentos($dados_chamado, $acompanhamentos, $usuarios) {
    // Dados Lote - Chamados (Acompanhamentos)
    $dados_lote = $this->buscaDadosMensagensLotes(32);
    // Dados Usuário
    $dados_usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);
    // Conteúdo Acompanhamento
    $conteudo = $this->geraConteudoAcompanhamento($dados_chamado, $acompanhamentos);
    // Parâmetros
    $parametros = $dados_lote["parametros"]["envio"];
    $parametros["remetente_nome"] = $dados_usuario["nome"];
    $parametros["remetente_email"] = $dados_usuario["email"];
    $parametros["reply_nome"] = $dados_usuario["nome"];
    $parametros["reply_email"] = $dados_usuario["email"];
    $parametros["assunto"] = "Chamado # {$dados_chamado["codigo"]}";
    $parametros["dados_conteudo"]["conteudo_texto"] = $conteudo["texto"];
    $parametros["dados_conteudo"]["conteudo_html"] = $conteudo["html"];
    // Gera Mensagens
    foreach ($usuarios as $usuario) {
      $parametros["dados_conteudo"]["usuario_nome"] = $usuario["nome"];
      $parametros["destinatarios"] = array($usuario["email"]);
      // Serializa Prametros
      $parametros_serializados = addslashes(serialize($parametros));
      // Insere mensagem no banco
      $query = "
        INSERT INTO
          tab_mensagens (men_parametros, men_data_hora_geracao, usu_id, mes_id, mlm_id, mel_id)
        VALUES ('$parametros_serializados', NOW(), {$usuario["id"]}, 1, {$dados_lote["modelo_id"]}, {$dados_lote["id"]})";
      $this->bancodedados->query($query);
    }
    // Envia Acompanhamento de Chamados Filhos
    $chamados_filho = $this->buscaChamadosFilhoSimples($dados_chamado["id"]);
    if ($chamados_filho) {
      foreach ($chamados_filho as $chamado_filho) {
      	$usuarios = $this->buscaUsuariosAcompanhamentos($chamado_filho["id"]);
        if ($usuarios) {
          $this->enviaAcompanhamentos($this->buscaDadosChamado($chamado_filho["id"]), $acompanhamentos, $usuarios);
        }
      }
    }
  }

  function geraConteudoAcompanhamento($dados_chamado, $acompanhamentos) {
    $ultimo_acompanhamento = $acompanhamentos[count($acompanhamentos) - 1];
    unset($acompanhamentos[count($acompanhamentos) - 1]);
    $mensagem_etapas = (count($acompanhamentos) ? "Foram registradas as etapas de " : "Foi registrada a etapa de ");
    $acompanhamentos = (count($acompanhamentos) ? "<b>".implode("</b>, <b>", $acompanhamentos)."</b> e " : "")."<b>$ultimo_acompanhamento</b>";
    $conteudo["texto"] = $mensagem_etapas.strip_tags($acompanhamentos)." no seguinte chamado:\n\n";
    $conteudo["html"] = $mensagem_etapas.$acompanhamentos." no seguinte chamado:<br><br>";
    // Dados do Chamado
    $dados_chamado = $this->trataDadosChamado($dados_chamado);
    $conteudo["texto"] .= strip_tags($this->formataDadosTexto($dados_chamado));
    $conteudo["html"] .= $this->formataDadosHtml($dados_chamado);
    return $conteudo;
  }

  function formataDadosTexto($dados_chamado) {
    $conteudo = "https://intranet.wavesystem.com.br/m=chamados_sistema&a=operar&chamado={$dados_chamado["id"]}&cliente={$dados_chamado["cliente_id"]}&condominio={$dados_chamado["condominio_id"]}\n\n";
    foreach ($dados_chamado as $descricao => $valor) {
      if (!is_numeric($descricao)) {
        $conteudo .= "$descricao: $valor";
      } else {
        $conteudo .= str_replace("&nbsp;&raquo;", " »", $valor)."\n";
      }
      $conteudo .= "\n";
    }
    return $conteudo;
  }

  function formataDadosHtml($dados_chamado) {
    $conteudo = '<table width="100%" cellpadding="3" cellspacing="0" border="1" align="center">';
    foreach ($dados_chamado as $descricao => $valor) {
      $conteudo .= "<tr>";
      if (!is_numeric($descricao)) {
        $conteudo .= "<td style=\"width:100px\"><b>$descricao:</b></td>";
        $conteudo .= "<td>$valor</td>";
      } else {
        $conteudo .= "<td colspan=\"2\">$valor</td>";
      }
      $conteudo .= "</tr>";
    }
    $conteudo .= '</table>';
    return $conteudo;
  }

  function trataDadosChamado($dados_chamado) {
    $dados["Código"] = "<a href=\"https://intranet.wavesystem.com.br/m=chamados_sistema&a=operar&chamado={$dados_chamado["id"]}&cliente={$dados_chamado["cliente_id"]}&condominio={$dados_chamado["condominio_id"]}\">".strip_tags($dados_chamado["codigo"])."</a>";
    if ($dados_chamado["cliente_id"]) {
      $dados["Cliente"] = $dados_chamado["cliente_nome"];
    }
    if ($dados_chamado["condominio_id"] || $dados_chamado["cliente_condominio_id"]) {
      $dados["Condomínio"] = "{$dados_chamado["condominio_nome"]} (".$this->buscaCondominioTipo($dados_chamado["condominio_tipo_id"]).")";
    }
    if ($dados_chamado["solicitante"]) {
      $dados["Solicitante"] = $dados_chamado["solicitante"];
    }
    $dados["Categoria"] = $dados_chamado["categoria_nome"];
    $dados["Responsável"] = $dados_chamado["responsavel_nome"];
    $dados["Prioridade"] = $dados_chamado["prioridade_nome"];
    $dados["Andamento"] = "{$dados_chamado["andamento"]}%";
    if ($dados_chamado["abertura"]) {
      $dados["Abertura"] = "{$dados_chamado["abertura"]} por ".$this->buscaUsuarioEtapa($dados_chamado["id"], 1, "usuario_nome");
    }
    if ($dados_chamado["encaminhamento"]) {
      $dados["Encaminhamento"] = "{$dados_chamado["encaminhamento"]} por ".$this->buscaUsuarioEtapa($dados_chamado["id"], 3, "usuario_nome");
    }
    if ($dados_chamado["fechamento"]) {
      $dados["Fechamento"] = "{$dados_chamado["fechamento"]} por ".$this->buscaUsuarioEtapa($dados_chamado["id"], 6, "usuario_nome");
    }
    $dados["Status"] = $dados_chamado["status_nome"];
    $etapas = $this->buscaEtapas($dados_chamado["id"], 1);
    if ($etapas) {
      $dados[] = "\n\n<br><b>Etapas:</b><br><br>\n";
      $etapas = $this->formataEtapasAcompanhamento($etapas);
      $dados = funcoes::mescla_arrays($dados, $etapas);
    }
    return $dados;
  }

  function formataEtapasAcompanhamento($etapas) {
    for ($i = 0; $i < sizeof($etapas); $i++) {
    	$itens[$i] = array();
      $itens[$i][] = "<b>{$etapas[$i]["usuario_nome"]}</b> em <b>{$etapas[$i]["datahora"]}</b>";
      $itens[$i][] = "<b>&nbsp;&raquo; ".$etapas[$i]["tipo_nome"]."</b>".($etapas[$i]["conteudo"] ? $this->formataEtapaConteudo($etapas[$i]) : "");
      if ($itens[$i]["nivel_stress_nome"]) $itens[$i][] = "<b>&nbsp;&raquo; Nível de Stress</b>: ".$etapas[$i]["nivel_stress_nome"];
      $itens[$i] = implode("<br>\n", $itens[$i]);
    }
    return $itens;
  }

  function buscaDadosMensagensLotes($mensagem_lote_id) {
    $query = "
      SELECT
        mel_id AS id,
        mlm_id AS modelo_id,
        mel_parametros AS parametros
      FROM
        tab_mensagens_lotes
      WHERE
        mel_id = $mensagem_lote_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      return $dados;
    }
  }

  function existePesquisaPendente($chamado_id) {
    $query = "
      SELECT
        pea_id
      FROM
        tab_pesquisas_aplicacoes
      WHERE
        cha_id = $chamado_id AND
        pea_termino IS NULL";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
    }

  ##################
  ### Permissões ###
  ##################

  function ehFechavel($dados) {
    if (!$this->temPermissao("fechar")) return false;
    if ($dados["status_id"] != 1) return false;
    if ($this->temPermissao("fechar_plus")) return true;
    if (!$this->ehResponsavelChamado($dados["id"], $_SESSION["usuario"])) return false;
    if ($this->possuiFilhoAberto($dados["id"])) return false;
    if ($this->existePesquisaPendente($dados["id"])) return false;
    return true;
  }
  
  function ehReabrivel($dados) {
    if (!$this->temPermissao("reabrir")) return false;
    if ($dados["status_id"] != 2) return false;
    if ($this->temPermissao("reabrir_plus")) return true;
    if (!$this->ehResponsavelChamado($dados["id"], $_SESSION["usuario"])) return false;
    return true;
  }

  function ehIniciavelAcompanhamento($dados) {
    if (!$this->temPermissao("acompanhamento_iniciar")) return false;
    if ($this->ehChamadoAcompanhado($dados["id"], $_SESSION["usuario"])) return false;
    return true;
  }

  function ehSolicitavelFechamento($dados) {
    if ($this->buscaResponsavelFechamento($dados["id"])) return false;
  	if ($this->temPermissao("solicitar_fechamento_plus")) return true;
    if (!$this->temPermissao("solicitar_fechamento")) return false;
    if (!$this->ehResponsavelChamado($dados["id"], $_SESSION["usuario"])) return false;
    return true;
  }

  function ehLiberavelTecnico($dados) {
    if (!$this->temPermissao("liberar_tecnico")) return false;
    if ($dados["status_id"] != 1) return false;
    if ($dados["cliente_id"]) {
      if (!$this->buscaServidorCliente($dados["cliente_id"])) return false;
    } elseif ($dados["condominio_id"]) {
      if (!$this->buscaServidorCondominio($dados["condominio_id"])) return false;
    } else {
      return false;
    }
    return true;
  }

  function ehCancelavelAcompanhamento($dados) {
    if (!$this->temPermissao("acompanhamento_cancelar")) return false;
    if (!$this->ehChamadoAcompanhado($dados["id"], $_SESSION["usuario"])) return false;
    return true;
  }
	
  function ehAgendavelVisitaTecnica($dados) {
    if ($dados["status_id"] != 1) return false;
  	if (!$dados["cliente_id"] && !$dados["condominio_id"]) return false;
    if (!$this->temPermissao("agendar_visita_tecnica", "visitas")) return false;
    return true;
  }
  
  function ehDesagendavelVisitaTecnica($dados) {
  	if (!$this->buscaDataVisitaTecnica($dados["id"])) return false;
    if (!$this->temPermissao("desagendar_visita_tecnica", "visitas")) return false;
    return true;
  }
  
  function ehImprimivel($dados) {
    if (!$this->temPermissao("imprimir")) return false;
    return true;
  }

  function ehRemovivel($dados) {
    if (!$this->temPermissao("remover")) return false;
    return true;
  }

  function ehChamadoAcompanhado($chamado_id, $usuario_id = null) {
    $query = "
      SELECT
        COUNT(cha_id)
      FROM
        rel_cha_usu_ehacompanhado
      WHERE
        cha_id = $chamado_id".($usuario_id ? " AND
        usu_id = $usuario_id" : "");
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query) > 0;
  }

  function buscaServidorCliente($cliente_id) {
  	$servidores = array();
    $query = "
      SELECT DISTINCT
        tab_servidores_interfaces.ser_id AS servidor_id
      FROM
        tab_clientes
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_clientes.cli_id = $cliente_id AND
        tab_servidores_servicos.sst_id IN (9, 10, 12)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados["servidor_id"];
    }
    if (count($servidores)) {
      return $servidores;
    }
    $query = "
      SELECT DISTINCT
        tab_servidores_interfaces.ser_id AS servidor_id
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_servidores_servicos.sst_id IN (9, 10, 12)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados["servidor_id"];
    }
    return $servidores;
  }

  function buscaServidorCondominio($condominio_id) {
  	$servidores = array();
    $query = "
      SELECT DISTINCT
        tab_servidores_interfaces.ser_id AS servidor_id
      FROM
        tab_condominios_blocos
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_condominios_blocos.con_id = $condominio_id AND
        tab_servidores_servicos.sst_id IN (9, 10, 12)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados["servidor_id"];
    }
    return $servidores;
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
    $this->conteudo .= $this->carregaArquivo("chamados.buscar.inc.php");
    $this->conteudo = str_replace('[codigo]', $_POST["codigo"], $this->conteudo);
    $this->conteudo = str_replace('[cliente]', $_POST["cliente"], $this->conteudo);
    $this->conteudo = str_replace('[condominio]', $this->geraSelect($this->buscaCondominios(), $_POST["condominio"], true, "condominio", "450px"), $this->conteudo);
    $this->conteudo = str_replace('[categoria]', $this->geraSelect($this->buscaChamadosCategorias($_SESSION["usuario"]), $_POST["categoria"], true, "categoria", "450px"), $this->conteudo);
    $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $_POST["responsavel"], true, "responsavel", "450px"), $this->conteudo);
    $this->conteudo = str_replace('[data_abertura_inicio]', $_POST["data_abertura_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[data_abertura_termino]', $_POST["data_abertura_termino"], $this->conteudo);
    $this->conteudo = str_replace('[data_fechamento_inicio]', $_POST["data_fechamento_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[data_fechamento_termino]', $_POST["data_fechamento_termino"], $this->conteudo);
    $this->conteudo = str_replace('[duracao_inicio]', $_POST["duracao_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[duracao_termino]', $_POST["duracao_termino"], $this->conteudo);
    $this->conteudo = str_replace('[prioridade]', $this->geraSelect($this->buscaPrioridades(), $_POST["prioridade"], true, "prioridade", "100px"), $this->conteudo);
    $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $_POST ? $_POST["status"] : "1", true, "status", "100px"), $this->conteudo);
    $this->conteudo = str_replace('[resultado]', $this->geraResultadoBusca(), $this->conteudo);
    $botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
    $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
    $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=buscar", $this->conteudo);
  }

  function geraResultadoBusca() {
    if ($_SESSION["POST"][$_GET["i"]]) {
      // Campos
      $cabecalho = array ("id" => "#", "dados" => "Dados", "datas" => "Datas/Duração", "prioridade" => "Prioridade", "status" => "Status");
      // Filtros
      if ($_POST["codigo"]) {
        $_POST["codigo"] = trim($_POST["codigo"]);
        $where[] = "tab_chamados.cha_codigo LIKE '%{$_POST["codigo"]}%'";
      }
      if ($_POST["cliente"]) {
        $_POST["cliente"] = trim($_POST["cliente"]);
        $where[] = "(tab_clientes.cli_nome LIKE '%{$_POST["cliente"]}%' OR tab_clientes.cli_nome_fantasia LIKE '%{$_POST["cliente"]}%')";
      }
      if ($_POST["condominio"]) {
        $where[] = "tab_condominios.con_id = {$_POST["condominio"]}";
      }
      if ($_POST["categoria"]) {
        $categorias = funcoes::mescla_arrays(array($_POST["categoria"]), $this->buscaCategoriasAbaixo($_POST["categoria"]));
        $categorias = implode(", ", $categorias);
        $where[] = "tab_chamados.chc_id IN ($categorias)";
      }
      if ($_POST["responsavel"]) {
        $_POST["responsavel"] = explode(",", $_POST["responsavel"]);
        if ($_POST["responsavel"][0] && !$_POST["responsavel"][1] && !$_POST["responsavel"][2]) {
          $setores = funcoes::mescla_arrays(array($_POST["responsavel"][0]), $this->buscaSetoresAbaixo($_POST["responsavel"][0]));
          $setores = implode(", ", $setores);
          $where[] = "tab_chamados.ors_id IN ($setores)";
        } else {
          if ($_POST["responsavel"][0]) $where[] = "tab_chamados.ors_id = {$_POST["responsavel"][0]}";
          if ($_POST["responsavel"][1]) $where[] = "tab_chamados.orf_id = {$_POST["responsavel"][1]}";
          if ($_POST["responsavel"][2]) $where[] = "tab_chamados.usu_id = {$_POST["responsavel"][2]}";
        }
      }
      if ($_POST["data_abertura_inicio"]) {
        $data = substr($_POST["data_abertura_inicio"], 6, 4)."-".substr($_POST["data_abertura_inicio"], 3, 2)."-".substr($_POST["data_abertura_inicio"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_abertura >= '$data'";
      }
      if ($_POST["data_abertura_termino"]) {
        $data = substr($_POST["data_abertura_termino"], 6, 4)."-".substr($_POST["data_abertura_termino"], 3, 2)."-".substr($_POST["data_abertura_termino"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_abertura <= '$data'";
      }
      if ($_POST["data_fechamento_inicio"]) {
        $data = substr($_POST["data_fechamento_inicio"], 6, 4)."-".substr($_POST["data_fechamento_inicio"], 3, 2)."-".substr($_POST["data_fechamento_inicio"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_fechamento >= '$data'";
      }
      if ($_POST["data_fechamento_termino"]) {
        $data = substr($_POST["data_fechamento_termino"], 6, 4)."-".substr($_POST["data_fechamento_termino"], 3, 2)."-".substr($_POST["data_fechamento_termino"], 0, 2);
        $where[] = "tab_chamados.cha_datahora_fechamento <= '$data'";
      }
      if ($_POST["duracao_inicio"] && !$_POST["duracao_termino"]) {
        $where[] = "ROUND((UNIX_TIMESTAMP(IF(tab_chamados.cha_datahora_fechamento, tab_chamados.cha_datahora_fechamento, NOW())) - UNIX_TIMESTAMP(tab_chamados.cha_datahora_abertura)) / 3600) >= {$_POST["duracao_inicio"]}";
      }
      if ($_POST["duracao_termino"] && !$_POST["duracao_inicio"]) {
        $where[] = "ROUND((UNIX_TIMESTAMP(IF(tab_chamados.cha_datahora_fechamento, tab_chamados.cha_datahora_fechamento, NOW())) - UNIX_TIMESTAMP(tab_chamados.cha_datahora_abertura)) / 3600) <= {$_POST["duracao_termino"]}";
      }
      if ($_POST["duracao_termino"] && $_POST["duracao_inicio"]) {
        $where[] = "ROUND((UNIX_TIMESTAMP(IF(tab_chamados.cha_datahora_fechamento, tab_chamados.cha_datahora_fechamento, NOW())) - UNIX_TIMESTAMP(tab_chamados.cha_datahora_abertura)) / 3600) BETWEEN {$_POST["duracao_inicio"]} AND {$_POST["duracao_termino"]}";
      }
      if ($_POST["prioridade"]) {
        $where[] = "tab_chamados.chp_id = {$_POST["prioridade"]}";
      }
      if ($_POST["status"]) {
        $where[] = "tab_chamados.chs_id = {$_POST["status"]}";
      }
      if (!$this->temPermissao("buscar_plus")) {
        $where[] = "(tab_chamados.cha_id IN (SELECT cha_id FROM tab_chamados_etapas WHERE usu_id = {$_SESSION["usuario"]}) OR tab_chamados.usu_id = {$_SESSION["usuario"]})";
      }

      if ($_SESSION["origem"]) {
        $where[] = "tab_clientes.clo_id = {$_SESSION["origem"]}";
      }	  
	  
      $where = $where ? implode(" AND\n          ", $where) : '1';
      // Consulta
      $query = "
        SELECT DISTINCT
          tab_chamados.cha_id AS id,
          tab_chamados.cha_codigo AS codigo,
          tab_chamados.chc_id AS categoria_id,
          tab_chamados.ors_id AS setor_id,
          tab_chamados.orf_id AS funcao_id,
          tab_chamados.usu_id AS usuario_id,
          tab_chamados.cli_id AS cliente_id,
          tab_chamados.con_id AS condominio_id,
          tab_clientes.cli_nome AS cliente_nome,
          tab_condominios.con_nome AS condominio_nome,
          DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
          DATE_FORMAT(tab_chamados.cha_datahora_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
          tab_chamados_status.chs_nome AS status,
          tab_chamados_prioridades.chp_nome AS prioridade
        FROM
          tab_chamados
          LEFT OUTER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
          LEFT OUTER JOIN tab_condominios ON (IF(tab_chamados.cli_id IS NOT NULL, tab_clientes.con_id, tab_chamados.con_id) = tab_condominios.con_id)
          INNER JOIN tab_chamados_status ON (tab_chamados_status.chs_id = tab_chamados.chs_id)
          INNER JOIN tab_chamados_prioridades ON (tab_chamados_prioridades.chp_id = tab_chamados.chp_id)
        WHERE
          $where
        ORDER BY
          tab_chamados_status.chs_id,
          tab_chamados.cha_datahora_encaminhamento";
      $query = $this->bancodedados->query($query);
      $itens = array();
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $dados["link"] = "javascript:verChamado({$dados["id"]})";
        $dados["dados"] = array();
        $dados["dados"][] = "<b>Código:</b> {$dados["codigo"]}";
        if ($dados["cliente_id"]) $dados["dados"][] = "<b>Cliente:</b> {$dados["cliente_nome"]}";
        if ($dados["condominio_id"]) $dados["dados"][] = "<b>Condomínio:</b> {$dados["condominio_nome"]}";
        $categoria = $this->buscaDadosCategoria($dados["categoria_id"]);
        $dados["dados"][] = "<b>Categoria:</b> {$categoria["nome"]}";
        $dados["responsavel"] = array();
        if ($dados["setor_id"]) $dados["responsavel"][] = $this->buscaSetor($dados["setor_id"]);
        if ($dados["funcao_id"]) $dados["responsavel"][] = $this->buscaFuncao($dados["funcao_id"]);
        if ($dados["usuario_id"]) $dados["responsavel"][] = $this->buscaUsuario($dados["usuario_id"]);
        $dados["dados"][] = "<b>Responsável:</b> ".implode(" » ", $dados["responsavel"]);
        $dados["dados"] = implode("<br>", $dados["dados"]);
        $dados["datas"] = array();
        if ($dados["abertura"]) $dados["datas"][] = "A: {$dados["abertura"]}";
        if ($dados["status_id"] == 1 && $dados["situacao_id"] != 1) $dados["datas"][] = "E: {$dados["encaminhamento"]}";
        if ($dados["fechamento"]) $dados["datas"][] = "F: {$dados["fechamento"]}";
        $dados["datas"] = "<div nowrap>".implode("<br>", $dados["datas"])."</div>";
        $dados["datas"] .= "(".$this->formataDuracao($dados["abertura"], $dados["fechamento"]).")";
        $dados["id"] = count($itens) + 1;
        $itens[] = $dados;
      }
      $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, null, null, array("right", "left"));
      return $listagem->conteudo;
    }
    return "Nenhum registro encontrado!";
  }
  
  function buscaVisitasOutrosChamados($cliente_id, $condominio_id, $chamado_id){
  	$query = "
  		SELECT
  			COUNT(*)
  		FROM
  			tab_visitas
  		WHERE".($cliente_id ? "
  			cli_id = $cliente_id AND" : "
  			con_id = $condominio_id AND")."
  			cha_id != $chamado_id AND
  			vit_id = 2 AND
  			via_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
  	return $this->bancodedados->resultado($query, 0, 0);
  }

  function buscaStatus() {
    $query = "
      SELECT
        chs_id AS id,
        chs_nome AS nome
      FROM
        tab_chamados_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaTipos() {
    $tipos[1] = "Cliente";
    $tipos[2] = "Condomínio";
    $tipos[3] = "Interno";
    return $tipos;
  }

  function buscaCondominios() {
    $query = "
      SELECT
        con_id AS id,
        CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, '/', tab_bairros.bai_nome, ')') AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)
        INNER JOIN tab_bairros ON (tab_bairros.bai_id = tab_logradouros.bai_id)
        INNER JOIN tab_cidades ON (tab_cidades.cid_id = tab_bairros.cid_id)
      WHERE
        cos_id IS NOT NULL
      ORDER BY
        tab_condominios.con_nome,
        tab_cidades.cid_nome,
        tab_bairros.bai_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }

  ############################
  ### Busca Chamados - RDS ###
  ############################

  function buscaChamados($chamado_id, $tipo = "cli_id") {
  	$lista = array();
  	$chamado_id = preg_replace("/[^0-9]/", "", $chamado_id);
  	if ($chamado_id && ($tipo == "cli_id" || $tipo == "con_id")) {
	    $query = "
	      SELECT
	        tab_chamados.cha_id AS id,
	        tab_chamados.cha_codigo AS codigo,
	        tab_chamados.chc_id AS categoria_id,
	        tab_chamados.chi_id AS situacao_id,
	        tab_chamados.chs_id AS status_id,
	        IF(tab_chamados.ors_id, tab_chamados.ors_id, 0) AS setor_id,
	        IF(tab_chamados.orf_id, tab_chamados.orf_id, 0) AS funcao_id,
	        IF(tab_chamados.usu_id, tab_chamados.usu_id, 0) AS usuario_id,
	        DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%d/%m/%Y (%H:%i)') AS abertura,
	        DATE_FORMAT(tab_chamados.cha_datahora_fechamento, '%d/%m/%Y (%H:%i)') AS fechamento,
	        DATE_FORMAT(tab_chamados.cha_datahora_encaminhamento, '%d/%m/%Y (%H:%i)') AS encaminhamento,
	        tab_chamados_status.chs_nome AS status,
	        tab_chamados_prioridades.chp_nome AS prioridade
	      FROM
	        tab_chamados
	        INNER JOIN tab_chamados_status ON (tab_chamados_status.chs_id = tab_chamados.chs_id)
	        INNER JOIN tab_chamados_prioridades ON (tab_chamados_prioridades.chp_id = tab_chamados.chp_id)
	      WHERE
	        tab_chamados.$tipo = $chamado_id".(!$this->temPermissao("buscar_plus") ? " AND
	        (tab_chamados.cha_id IN (SELECT cha_id FROM tab_chamados_etapas WHERE tab_chamados_etapas.cha_id = tab_chamados.cha_id AND usu_id = {$_SESSION["usuario"]}) OR tab_chamados.usu_id = {$_SESSION["usuario"]})" : "")."
	      ORDER BY
	        tab_chamados_status.chs_id,
	        tab_chamados.cha_datahora_abertura";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      if ($this->temPermissao("ver")) $dados["link"] = "javascript:verChamado({$dados["id"]})";
	      $dados["dados"] = array();
	      $dados["dados"][] = "<b>Código:</b> {$dados["codigo"]}";
	      $categoria = $this->buscaDadosCategoria($dados["categoria_id"]);
	      $dados["dados"][] = "<b>Categoria:</b> {$categoria["nome"]}";
	      $dados["responsavel"] = array();
	      if ($dados["setor_id"]) $dados["responsavel"][] = $this->buscaSetor($dados["setor_id"]);
	      if ($dados["funcao_id"]) $dados["responsavel"][] = $this->buscaFuncao($dados["funcao_id"]);
	      if ($dados["usuario_id"]) $dados["responsavel"][] = $this->buscaUsuario($dados["usuario_id"]);
	      $dados["dados"][] = "<b>Responsável:</b> ".implode(" » ", $dados["responsavel"]);
	      $dados["dados"] = implode("<br>", $dados["dados"]);
	      $dados["datas"] = array();
	      if ($dados["abertura"]) $dados["datas"][] = "A: {$dados["abertura"]}";
	      if ($dados["status_id"] == 1 && $dados["situacao_id"] != 1) $dados["datas"][] = "E: {$dados["encaminhamento"]}";
	      if ($dados["fechamento"]) $dados["datas"][] = "F: {$dados["fechamento"]}";
	      $dados["datas"] = "<div nowrap>".implode("<br>", $dados["datas"])."</div>";
	      $dados["datas"] .= "(".$this->formataDuracao($dados["abertura"], $dados["fechamento"]).")";
	      $lista[] = $dados;
	    }
  	}
    return $lista;
  }

  function geraChamadosListagem($itens) {
    $cabecalho = array("dados"=> "Dados", "datas" => "Datas/Duração", "prioridade" => "Prioridade", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 110));
    return $listagem->conteudo;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if ($_GET["cliente_id"] && ($dados_cliente = $this->buscaDadosCliente($_GET["cliente_id"]))) {
      if (!$_POST) {
        $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
        $this->conteudo .= $this->carregaArquivo("chamados.cliente_adicionar.inc.php");
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&cliente_id={$_GET["cliente_id"]}", $this->conteudo);
        // Conteúdo
        $this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
        $this->conteudo = str_replace("[cliente_id]", $_GET["cliente_id"], $this->conteudo);
        $this->conteudo = str_replace("[endereco]", strip_tags($dados_cliente["endereco"]), $this->conteudo);
        $this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($contatos->buscaContatosTelefones($_GET["cliente_id"]), true, $dados_cliente["status_id"] == 2), $this->conteudo);
        $this->conteudo = str_replace("[lista_telefones]", serialize($contatos->buscaContatosTelefones($_GET["cliente_id"])), $this->conteudo);
        $this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($contatos->buscaContatosEmails($_GET["cliente_id"]), true, $dados_cliente["status_id"] == 2), $this->conteudo);
        $this->conteudo = str_replace("[lista_emails]", serialize($contatos->buscaContatosEmails($_GET["cliente_id"])), $this->conteudo);
        $this->conteudo = str_replace("[nivel_stress]", $this->geraSelect($this->buscaNiveisStress(), null, true, "nivel_stress", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[categoria]", $this->geraSelect($this->buscaChamadosCategorias($_SESSION["usuario"]), null, true, "categoria", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[prioridade]", $this->geraSelect($this->buscaPrioridades(), null, true, "prioridade", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa(), $this->conteudo);
        $this->conteudo = str_replace("[responsavel_fechamento]", $this->geraSelect($this->buscaResponsaveisSetor($_SESSION["usuario"]), null, true, "responsavel_fechamento", "100%"), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        $_POST["atualizar_contatos"] = true;
        $id = $this->adicionarChamado($_GET["cliente_id"], null, $_POST);
        // Visualiza Registro
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      }
    } elseif ($_GET["condominio_id"] && ($dados_condominio = $this->buscaDadosCondominio($_GET["condominio_id"]))) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("chamados.condominio_adicionar.inc.php");
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&condominio_id={$_GET["condominio_id"]}", $this->conteudo);
        // Conteúdo
        $this->conteudo = str_replace("[nome]", $dados_condominio["nome"], $this->conteudo);
        $this->conteudo = str_replace("[condominio_id]", $dados_condominio["id"], $this->conteudo);
        $this->conteudo = str_replace("[endereco]", strip_tags($dados_condominio["endereco"]), $this->conteudo);
        $this->conteudo = str_replace("[nivel_stress]", $this->geraSelect($this->buscaNiveisStress(), null, true, "nivel_stress", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[categoria]", $this->geraSelect($this->buscaChamadosCategorias($_SESSION["usuario"]), null, true, "categoria", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[prioridade]", $this->geraSelect($this->buscaPrioridades(), null, true, "prioridade", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[retorno]", $this->geraSelect, $this->conteudo);
        $this->conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa(), $this->conteudo);
        $this->conteudo = str_replace("[responsavel_fechamento]", $this->geraSelect($this->buscaResponsaveisSetor($_SESSION["usuario"]), null, true, "responsavel_fechamento", "100%"), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        $_POST["atualizar_contatos"] = true;
        $id = $this->adicionarChamado(null, $_GET["condominio_id"], $_POST);
        // Visualiza Registro
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      }
    } else {
      $this->fecharJanela();
    }
  }

  function adicionarChamado($cliente_id, $condominio_id, $dados, $usuario_id = null) {
    // Atualiza Contatos
    if ($cliente_id && $dados["atualizar_contatos"]) {
      $contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
      $contatos->atualizaContatoTelefones($cliente_id, unserialize(stripslashes($dados["lista_telefones"])));
      $contatos->atualizaContatoEmails($cliente_id, unserialize(stripslashes($dados["lista_emails"])));
    }
    // Trata Dados
    $dados["solicitante"] = $this->corrigeNome($dados["solicitante"]);
    //$codigo = $this->geraChamadoCodigo($cliente_id, $condominio_id, $_SESSION["usuario"]);
    $cliente_id = $cliente_id ? $cliente_id : "NULL";
    $condominio_id = $condominio_id ? $condominio_id : "NULL";
    // Gera Chamado
    $query = "
      INSERT INTO
        tab_chamados (cha_datahora_abertura, cha_solicitante, cli_id, con_id, chc_original, chc_id, ors_id, orf_id, usu_id, chp_id, chi_id, chs_id)
      VALUES (NOW(), '{$dados["solicitante"]}', $cliente_id, $condominio_id, {$dados["categoria"]}, {$dados["categoria"]}, NULL, NULL, NULL, {$dados["prioridade"]}, 1, 1)";
    $this->bancodedados->query($query);
    $id = $this->bancodedados->ultimoIdInserido();

    $codigo = $this->geraChamadoCodigo($id);
    $query = "UPDATE tab_chamados SET cha_codigo = '$codigo' WHERE cha_id = '$id'";
    $this->bancodedados->query($query);

    $this->buscaDadosItem($id, true);
    // Registra Etapa Abertura
    $this->registraEtapa($this->dados_item, 1, $dados, $usuario_id);
    unset($dados["nivel_stress"]);
    // Registra Etapa Categoria
    $this->registraEtapa($this->dados_item, 2, $dados, $usuario_id);
    // Registra Etapa Encaminhamento
    $this->registraEtapa($this->dados_item, 3, $dados, $usuario_id);
    // Registra Etapa Prioridade
    $this->registraEtapa($this->dados_item, 4, $dados, $usuario_id);
    // Registra Etapa Comentário
    if ($dados["tipo"]) {
      $this->registraEtapa($this->dados_item, $dados["tipo"], $dados, $usuario_id);
    }
    // Registra Etapa de Solicitação de Fechamento
    if ($dados["categoria"] == 105) {
		$this->registraEtapa($this->dados_item, 16, array("responsavel_fechamento" => "14,0,0"), $usuario_id);
	} elseif ($dados["responsavel_fechamento"]) {
		$this->registraEtapa($this->dados_item, 16, $dados, $usuario_id);
    }
    // Registra Etapa de Acompanhamento
    if ($dados["acompanhar"]) {
      $this->registraEtapa($this->dados_item, 8, $dados, $usuario_id);
    }
    // Registra como Favorito
    if ($dados["favoritos"]) {
      $query = "INSERT INTO rel_cha_usu_ehfavorito (cha_id, usu_id) VALUES ($id, {$_SESSION["usuario"]})";
      $this->bancodedados->query($query);
    }
    return $id;
  }

  function buscaNovaEtapa($categoria_id = null, $etapa_atual = null, $chamado_id = null, $editavel = true) {
    $conteudo = $this->carregaArquivo("chamados.nova_etapa.inc.php");
    $conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaEtapasTipos($categoria_id, $etapa_atual, $chamado_id), 5, true, "tipo", "100%", "document.getElementById('etapa_tipo').value = tipo; mudaChamadoEtapaTipo(this.value);", $editavel), $conteudo);
    $conteudo = str_replace("[etapa_tipo]", "5", $conteudo);
    return $conteudo;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      // Registra Visualização
      $this->registraChamadoVisualizacao($this->instancia, $this->dados_item["id"]);
      // Registra Etapa Visualização
      if (!$this->dados_item["visualizado"] && $this->dados_item["usuario_id"] != $_SESSION["usuario"] && $this->ehResponsavelChamado($this->dados_item["id"], $_SESSION["usuario"], false, true)) {
        $this->registraEtapa($this->dados_item, 21);
      }
      // Conteúdo
      $this->conteudo .= $this->carregaArquivo("chamados.ver.inc.php");
      // Conteúdo
      $this->conteudo = str_replace("[chamado_id]", $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace("[codigo]", $this->temPermissao("operar", "chamados_sistema") ? $this->buscaIconesChamado($this->dados_item)."<span style=\"text-decoration:underline;cursor:pointer\" onclick=\"if (window.opener && window.opener.abrirChamadosSistema) window.opener.abrirChamadosSistema('{$this->dados_item["id"]}', '{$this->dados_item["cliente_id"]}', '{$this->dados_item["condominio_id"]}'); else abrirChamadosSistema('{$this->dados_item["id"]}', '{$this->dados_item["cliente_id"]}', '{$this->dados_item["condominio_id"]}'); window.close();\">".$this->dados_item["codigo"]."</span>" : $this->dados_item["codigo"], $this->conteudo);
      if ($this->dados_item["cliente_id"]) {
        $this->conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $this->conteudo);
      	$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);
        $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
        $this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
        $this->conteudo = str_replace("[telefones]", $this->buscaClientesTelefones($this->dados_item["cliente_id"]), $this->conteudo);
        $this->conteudo = str_replace("[emails]", $this->buscaClientesEmails($this->dados_item["cliente_id"]), $this->conteudo);
        $this->conteudo = str_replace("[endereco]", strip_tags($dados_cliente["endereco"]), $this->conteudo);
      } elseif ($this->dados_item["condominio_id"]) {
        $this->conteudo = preg_replace('/<tbody id="grupo_dados_cliente">.*?<\/tbody>\s+/s', '', $this->conteudo);
      	$dados_condominio = $this->buscaDadosCondominio($this->dados_item["condominio_id"]);
        $this->conteudo = str_replace("[nome]", $dados_condominio["nome"], $this->conteudo);
        $this->conteudo = str_replace("[endereco]", $dados_condominio["endereco"], $this->conteudo);
      } else {
        $this->conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $this->conteudo);
        $this->conteudo = preg_replace('/<tbody id="grupo_dados_cliente">.*?<\/tbody>\s+/s', '', $this->conteudo);
      }
      $this->conteudo = str_replace("[solicitante]", $this->dados_item["solicitante"], $this->conteudo);
      $this->conteudo = str_replace("[categoria]", $this->dados_item["categoria_nome"], $this->conteudo);
      $this->conteudo = str_replace("[responsavel]", $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace("[prioridade]", $this->dados_item["prioridade_nome"], $this->conteudo);
      $this->conteudo = str_replace("[andamento]", $this->dados_item["andamento"]."%", $this->conteudo);
      $this->conteudo = str_replace("[abertura]", $this->dados_item["abertura"]." por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 1, "usuario_nome"), $this->conteudo);
      $this->conteudo = str_replace("[encaminhamento]", $this->dados_item["encaminhamento"] ? $this->dados_item["encaminhamento"]." por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 3, "usuario_nome") : "", $this->conteudo);
      $this->conteudo = str_replace("[fechamento]", $this->dados_item["fechamento"] ? $this->dados_item["fechamento"]." por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 6, "usuario_nome") : "", $this->conteudo);
      $this->conteudo = str_replace("[duracao]", $this->dados_item["duracao_setor"] != $this->dados_item["duracao"] && $this->dados_item["status_id"] == 1 ? "{$this->dados_item["duracao_setor"]} ({$this->dados_item["duracao"]})" : $this->dados_item["duracao"], $this->conteudo);
			$this->conteudo = str_replace("[data_visita_tecnica]", $this->buscaDataVisitaTecnica($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace("[etapas]", $this->geraListagemEtapas($this->buscaEtapas($this->dados_item["id"], 0)), $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaEtapasTipos($this->dados_item["categoria_id"], $this->dados_item["etapa_atual"], $this->dados_item["id"]), "", true), $this->conteudo);
      $this->conteudo = str_replace("[etapas_itens]", $this->carregaArquivo("chamados.etapas.inc.php"), $this->conteudo);
      $this->conteudo = str_replace("[instancia]", $this->instancia, $this->conteudo);
      $visualizacao = $this->buscaIconeVisualizacoes($this->instancia, $this->dados_item["id"], true);
      if ($visualizacao) {
        $visualizacao["usuarios"] = implode("\\n\\n", str_replace("<br>", "\\n", $visualizacao["usuarios"]));
        $mensagem = "Atenção!\\n\\nEste chamado está sendo visualizado pelo(s) usuário(s):\\n\\n{$visualizacao["usuarios"]}";
      } else {
        $mensagem = "";
      }
      $this->conteudo = str_replace("[mensagem_visualizacao]", $mensagem, $this->conteudo);
      // Botões
      if ($this->ehIniciavelAcompanhamento($this->dados_item)) $botoes[] = '<input type="button" name="acompanhar" class="buttonAzulEscuroBranco11" value="Iniciar Acompanhamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=acompanhamento_iniciar&i='.$this->dados_item["id"].'\';">';
      if ($this->ehCancelavelAcompanhamento($this->dados_item)) $botoes[] = '<input type="button" name="parar_de_acompanhar" class="buttonAzulEscuroBranco11" value="Cancelar Acompanhamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=acompanhamento_cancelar&i='.$this->dados_item["id"].'\';">';
      if ($this->ehFechavel($this->dados_item) && $this->possuiPermissaoSolicitacaoFechamento($this->dados_item["id"], $_SESSION["usuario"])) $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar Chamado" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=fechar&i='.$this->dados_item["id"].'\';">';
      if ($this->ehImprimivel($this->dados_item)) $botoes[] = '<input type="button" id="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onclick="imprimeChamado()">';
      if ($this->ehRemovivel($this->dados_item)) $botoes[] = '<input type="button" id="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="fechar_janela" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      $this->fecharJanela();
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
    	if (!$_POST) {
        // Registra Visualização
        $this->registraChamadoVisualizacao($this->instancia, $this->dados_item["id"]);
	      // Registra Etapa Visualização
	      if (!$this->dados_item["visualizado"] && $this->dados_item["usuario_id"] != $_SESSION["usuario"] && $this->ehResponsavelChamado($this->dados_item["id"], $_SESSION["usuario"], false, true)) {
	        $this->registraEtapa($this->dados_item, 21);
	      }
        // Conteúdo
        $this->conteudo .= $this->carregaArquivo("chamados.editar.inc.php");
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
        // Conteúdo
        $eh_editavel = $this->dados_item["status_id"] == 1 && $this->ehResponsavelChamado($this->dados_item["id"], $_SESSION["usuario"]);
        $this->conteudo = str_replace("[chamado_id]", $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace("[codigo]", $this->temPermissao("operar", "chamados_sistema") ? $this->buscaIconesChamado($this->dados_item)."<span style=\"text-decoration:underline;cursor:pointer\" onclick=\"if (window.opener && window.opener.abrirChamadosSistema) window.opener.abrirChamadosSistema('{$this->dados_item["id"]}', '{$this->dados_item["cliente_id"]}', '{$this->dados_item["condominio_id"]}'); else abrirChamadosSistema('{$this->dados_item["id"]}', '{$this->dados_item["cliente_id"]}', '{$this->dados_item["condominio_id"]}'); window.close();\">".$this->dados_item["codigo"]."</span>" : $this->dados_item["codigo"], $this->conteudo);
        if ($this->dados_item["cliente_id"]) {
          $this->conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $this->conteudo);
          $dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);
          $this->conteudo = str_replace("[id]", $this->dados_item["cliente_id"], $this->conteudo);
          $this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
          $this->conteudo = str_replace("[telefones]", $this->buscaClientesTelefones($this->dados_item["cliente_id"]), $this->conteudo);
          $this->conteudo = str_replace("[emails]", $this->buscaClientesEmails($this->dados_item["cliente_id"]), $this->conteudo);
          $this->conteudo = str_replace("[endereco]", strip_tags($dados_cliente["endereco"]), $this->conteudo);
          $this->conteudo = str_replace("[tipo]", "cli_id", $this->conteudo);
        } elseif ($this->dados_item["condominio_id"]) {
          $this->conteudo = preg_replace('/<tbody id="grupo_dados_cliente">.*?<\/tbody>\s+/s', '', $this->conteudo);
          $dados_condominio = $this->buscaDadosCondominio($this->dados_item["condominio_id"]);
          $this->conteudo = str_replace("[id]", $this->dados_item["condominio_id"], $this->conteudo);
          $this->conteudo = str_replace("[nome]", $dados_condominio["nome"], $this->conteudo);
          $this->conteudo = str_replace("[endereco]", $dados_condominio["endereco"], $this->conteudo);
          $this->conteudo = str_replace("[tipo]", "con_id", $this->conteudo);
        } else {
          $this->conteudo = str_replace("[id]", "", $this->conteudo);
          $this->conteudo = str_replace("[tipo]", "", $this->conteudo);
          $this->conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $this->conteudo);
          $this->conteudo = preg_replace('/<tbody id="grupo_dados_cliente">.*?<\/tbody>\s+/s', '', $this->conteudo);
        }
        $this->conteudo = str_replace("[solicitante]", $this->dados_item["solicitante"], $this->conteudo);
        $this->conteudo = str_replace("[categoria]", $this->geraSelect($this->buscaChamadosCategorias($_SESSION["usuario"], $this->dados_item["categoria_id"]), $this->dados_item["categoria_id"], true, "categoria", "100%", "mudaCategoria(this)", $eh_editavel), $this->conteudo);
        $this->conteudo = str_replace("[responsavel]", $this->geraSelect($this->buscaResponsaveis($this->dados_item["categoria_id"]), $this->dados_item["responsavel_id"], true, "responsavel", "100%", null, $eh_editavel), $this->conteudo);
        $this->conteudo = str_replace("[prioridade]", $this->geraSelect($this->buscaPrioridades(), $this->dados_item["prioridade_id"], true, "prioridade", "200px", null, $eh_editavel), $this->conteudo);
        $this->conteudo = str_replace("[andamento]", $this->geraSelect($this->buscaAndamentos($this->dados_item["andamento"]), $this->dados_item["andamento"], true, "andamento", "200px", null, $eh_editavel), $this->conteudo);
        $this->conteudo = str_replace("[abertura]", $this->dados_item["abertura"]." por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 1, "usuario_nome"), $this->conteudo);
        $this->conteudo = str_replace("[encaminhamento]", $this->dados_item["encaminhamento"] ? $this->dados_item["encaminhamento"]." por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 3, "usuario_nome") : "", $this->conteudo);
        $this->conteudo = str_replace("[fechamento]", $this->dados_item["fechamento"] ? $this->dados_item["fechamento"]." por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 6, "usuario_nome") : "", $this->conteudo);
        $this->conteudo = str_replace("[duracao]", $this->dados_item["duracao_setor"] != $this->dados_item["duracao"] && $this->dados_item["status_id"] == 1 ? "{$this->dados_item["duracao_setor"]} ({$this->dados_item["duracao"]})" : $this->dados_item["duracao"], $this->conteudo);
				$this->conteudo = str_replace("[data_visita_tecnica]", $this->buscaDataVisitaTecnica($this->dados_item["id"]), $this->conteudo);
        $this->conteudo = str_replace("[etapas]", $this->geraListagemEtapas($this->buscaEtapas($this->dados_item["id"], 0)), $this->conteudo);
        $this->conteudo = str_replace("[editavel]", $eh_editavel ? "" : "disabled", $this->conteudo);
        $this->conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($this->dados_item["categoria_id"], $this->dados_item["etapa_atual"], $this->dados_item["id"], $eh_editavel), $this->conteudo);
        $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
        $this->conteudo = str_replace("[instancia]", $this->instancia, $this->conteudo);
        $visualizacao = $this->buscaIconeVisualizacoes($this->instancia, $this->dados_item["id"], true);
        if ($visualizacao) {
          $visualizacao["usuarios"] = implode("\\n\\n", str_replace("<br>", "\\n", $visualizacao["usuarios"]));
          $mensagem = "Atenção!\\n\\nEste chamado está sendo visualizado pelo(s) usuário(s):\\n\\n{$visualizacao["usuarios"]}";
        } else {
          $mensagem = "";
        }
        $this->conteudo = str_replace("[mensagem_visualizacao]", $mensagem, $this->conteudo);
        // Botões
        if ($this->ehIniciavelAcompanhamento($this->dados_item)) $botoes[] = '<input type="button" name="acompanhar" class="buttonAzulEscuroBranco11" value="Iniciar Acompanhamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=acompanhamento_iniciar&i='.$this->dados_item["id"].'\';">';
        if ($this->ehCancelavelAcompanhamento($this->dados_item)) $botoes[] = '<input type="button" name="parar_de_acompanhar" class="buttonAzulEscuroBranco11" value="Cancelar Acompanhamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=acompanhamento_cancelar&i='.$this->dados_item["id"].'\';">';
        if ($this->ehFechavel($this->dados_item) && $this->possuiPermissaoSolicitacaoFechamento($this->dados_item["id"], $_SESSION["usuario"])) $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar Chamado" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=fechar&i='.$this->dados_item["id"].'\';">';
        if ($this->ehImprimivel($this->dados_item)) $botoes[] = '<input type="button" id="imprimir" class="buttonAzulEscuroBranco11" value="Imprimir" onclick="imprimeChamado()">';
        if ($this->ehRemovivel($this->dados_item)) $botoes[] = '<input type="button" id="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="button" name="fechar_janela" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        $this->editarChamado($this->dados_item, $_POST);
        // Visualiza Registro
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fecharJanela();
    }
  }

  function editarChamado($dados_chamado, $dados) {
  	$acompanhamentos = array();
    if ($dados["categoria"] && $dados["categoria"] != $dados_chamado["categoria_id"]) {
      // Registro de Categoria
      $this->registraEtapa($dados_chamado, 2, $dados);
      $acompanhamentos[] = "Categoria";
      // Busca Responsável se não informado
      if (!$dados["responsavel"]) {
        $origem_categoria = $this->buscaDadosCategoriaEtapa($dados["categoria"], 3);
        $responsavel[0] = ($origem_categoria["setor_id"]) ? $origem_categoria["setor_id"] : "0";
        $responsavel[1] = ($origem_categoria["funcao_id"]) ? $origem_categoria["funcao_id"] : "0";
        $responsavel[2] = '0';
        $dados["responsavel"] = "{$responsavel[0]},{$responsavel[1]},{$responsavel[2]}";
      }
    }
    if ($dados["responsavel"] && $dados["responsavel"] != $dados_chamado["responsavel_id"]) {
      // Registro de Responsável
      $this->registraEtapa($dados_chamado, 3, $dados);
      $acompanhamentos[] = "Responsável";
    }
    if ($dados["prioridade"] && $dados["prioridade"] != $dados_chamado["prioridade_id"]) {
      // Registro de Prioridade
      $this->registraEtapa($dados_chamado, 4, $dados);
      $acompanhamentos[] = "Prioridade";
    }
    if ($dados["andamento"] && $dados["andamento"] != $dados_chamado["andamento"]) {
      // Registro de Andamento
      $this->registraEtapa($dados_chamado, 15, $dados);
      $acompanhamentos[] = "Andamento";
    }
    if ($dados["data_hora_retorno"] && $dados["data_hora_retorno"] != $dados_chamado["data_hora_retorno"]) {
      // Registro de Agendamento de Retorno
      $this->registraEtapa($dados_chamado, 22, $dados);
      $acompanhamentos[] = "Retorno"; 
    } elseif ($dados["retorno"] == 2 && $dados_chamado["aguardando_retorno"]){
      // Registro de Desagendamento de Retorno
      $this->registraEtapa($dados_chamado, 23, $dados);
      $acompanhamentos[] = "Retorno"; 
    }
    if ($dados["chamado_pai"] && $dados["chamado_pai"] != $dados_chamado["chamado_pai"]) {
      // Registro de Registro de Vínculo
      $this->registraEtapa($dados_chamado, 10, $dados);
      $acompanhamentos[] = "Vínculo";
    }
    if (!$dados["chamado_pai"] && $dados_chamado["chamado_pai"]) {
      // Registro de Remoção de Vínculo
      $this->registraEtapa($dados_chamado, 11, $dados);
      $acompanhamentos[] = "Remoção de Vínculo";
    }
    // Registro de Nova Etapa
    if ($dados["tipo"] && $this->validaEtapa($dados["tipo"], $dados)) {
      $this->registraEtapa($dados_chamado, $dados["tipo"], $dados);
      $acompanhamentos[] = $this->buscaEtapaTipoNome($dados["tipo"]);
    }
    // Envia Acompanhamento
    if ($acompanhamentos && $usuarios = $this->buscaUsuariosAcompanhamentos($dados_chamado["id"])) {
      $this->enviaAcompanhamentos($dados_chamado, $acompanhamentos, $usuarios);
    }
  }

  ######################
  ### Fechar Chamado ###
  ######################

  function fechar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehFechavel($this->dados_item)) {
    	$pendencias = $this->buscaEtapasPendentes($this->dados_item["id"]);
      if ($pendencias) {
        $this->conteudo .= $this->carregaArquivo("chamados.pendencias.inc.php");
        // Conteúdo
        $this->conteudo = str_replace("[pendencias]", implode("<br>", $pendencias), $this->conteudo);
        // Botões
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      } else {
        if (!$_POST["confirmar"]) {
          $this->conteudo .= $this->carregaArquivo("chamados.fechar.inc.php");
          // Conteúdo
          $this->conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($this->dados_item["categoria_id"], $this->dados_item["etapa_atual"], $this->dados_item["id"]), $this->conteudo);
          // Botões
          $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
          $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
          $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
          $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=fechar&i={$_GET["i"]}", $this->conteudo);
        } else {
          $this->registraEtapa($this->dados_item, 6, $_POST);
          $acompanhamentos[] = "Fechamento";
          if ($_POST["conteudo"]) {
            $this->registraEtapa($this->dados_item, 5, $_POST);
            $acompanhamentos[] = "Comentário (Interno)";
          }
          // Envia Acompanhamento
          if ($acompanhamentos && $usuarios = $this->buscaUsuariosAcompanhamentos($this->dados_item["id"])) {
            $this->enviaAcompanhamentos($this->dados_item, $acompanhamentos, $usuarios);
          }
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
        }
      }
    } else {
      $this->fecharJanela();
    }
  }

  ################################
  ### Acompanhamento - Iniciar ###
  ################################

  function acompanhamento_iniciar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("chamados.acompanhamento.inc.php");
        $this->conteudo = str_replace("[acao]", "iniciar o acompanhamento", $this->conteudo);
        $this->conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($this->dados_item["categoria_id"], $this->dados_item["etapa_atual"], $this->dados_item["id"]), $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=acompanhamento_iniciar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $this->registraEtapa($this->dados_item, 8, $_POST);
        $acompanhamentos[] = "Início de Acompanhamento";
        // Registra Etapa de Comentário
        if ($_POST["conteudo"]) {
          $this->registraEtapa($this->dados_item, 5, $_POST);
          $acompanhamentos[] = "Comentário (Interno)";
        }
        // Envia Acompanhamento
        if ($acompanhamentos && $usuarios = $this->buscaUsuariosAcompanhamentos($this->dados_item["id"])) {
          $this->enviaAcompanhamentos($this->dados_item, $acompanhamentos, $usuarios);
        }
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fecharJanela();
    }
  }

  #################################
  ### Acompanhamento - Cancelar ###
  #################################

  function acompanhamento_cancelar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("chamados.acompanhamento.inc.php");
        $acoes  = '<td bgcolor="#505050"><b>&nbsp;Acompanhamento (Cancelar)&nbsp;</b></td>';
        $this->conteudo = str_replace("[acoes]", $acoes, $this->conteudo);
        $this->conteudo = str_replace("[acao]", "cancelar o acompanhamento", $this->conteudo);
        $this->conteudo = str_replace("[nova_etapa]", $this->buscaNovaEtapa($this->dados_item["categoria_id"], $this->dados_item["etapa_atual"], $this->dados_item["id"]), $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=acompanhamento_cancelar&i={$_GET["i"]}", $this->conteudo);
      } else {
        // Registra Cancelamento de Acompanhamento
        $this->registraEtapa($this->dados_item, 9, $_POST);
        $acompanhamentos[] = "Cancelamento de Acompanhamento";
        // Registra Etapa de Comentário
        if ($_POST["conteudo"]) {
          $this->registraEtapa($this->dados_item, 5, $_POST);
          $acompanhamentos[] = "Comentário (Interno)";
        }
        // Envia Acompanhamento
        if ($acompanhamentos && $usuarios = $this->buscaUsuariosAcompanhamentos($this->dados_item["id"])) {
          $this->enviaAcompanhamentos($this->dados_item, $acompanhamentos, $usuarios);
        }
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fecharJanela();
    }
  }

  ################
  ### Imprimir ###
  ################

  function imprimir() {
    if (array_key_exists("tipo", $_GET) && $_GET["tipo"] == "matricial") {
      $chamados = explode(",", $_GET["i"]);
      $conteudo = "";
      for ($i = 0; $i < count($chamados); $i++) {
        $this->buscaDadosItem($chamados[$i]);
        $conteudo .= $this->geraConteudoMatricial($this->dados_item, $i);
      }
      if ($conteudo) {
        print "[chamados][OK]$conteudo";
      }
      exit();
    }
    $this->conteudo = $this->carregaArquivo("modelos/chamado.html");
	
	if ($_GET["inicio"] && $_GET["fim"]) {
		$query = "
			SELECT
				GROUP_CONCAT(DISTINCT tab_chamados.cha_id)
			FROM
				tab_chamados
				INNER JOIN rel_cha_caa_contem ON (rel_cha_caa_contem.cha_id = tab_chamados.cha_id)
			WHERE
				tab_chamados.cha_datahora_fechamento BETWEEN '{$_GET["inicio"]}' AND '{$_GET["fim"]}' AND
				rel_cha_caa_contem.caa_id IN (3, 38, 16, 34, 15, 9, 29, 45, 22, 47, 32, 42, 27, 19, 37, 26, 24, 28, 50, 48, 49, 33, 4, 17, 20, 39, 6, 1, 7, 10, 12, 13, 2)
		";
		$query = $this->bancodedados->query($query);
		$chamados = explode(",", $this->bancodedados->resultado($query));
	} elseif ($_GET["periodo"]) {
		$query = "
			SELECT
				GROUP_CONCAT(cha_id)
			FROM
				tab_chamados
			WHERE
				cha_datahora_fechamento LIKE '{$_GET["periodo"]}%'
				AND (SELECT COUNT(*) FROM rel_cha_caa_contem WHERE tab_chamados.cha_id = rel_cha_caa_contem.cha_id AND caa_id IN (3, 38, 16, 34, 15, 9, 29, 45, 22, 47, 32, 42, 27, 19, 37, 26, 24, 28, 50, 48, 49, 33, 4, 17, 20, 39, 6, 1, 7, 10, 12, 13, 2)) > 0";
		$query = $this->bancodedados->query($query);
		$chamados = explode(",", $this->bancodedados->resultado($query));
	} else {
		$chamados = explode(",", $_GET["i"]);
	}
	
    for ($i = 0; $i < count($chamados); $i++) {
      $this->buscaDadosItem($chamados[$i]);
      $conteudo .= $this->geraConteudoImpressao($i);
    }
    $this->conteudo = str_replace("[chamados]", $_GET["i"], $this->conteudo);
    $this->conteudo = str_replace("[conteudo]", $conteudo, $this->conteudo);
    $this->conteudo = str_replace("[exibir_vazios]", $chamados[0] ? "" : "_down", $this->conteudo);
    $this->conteudo = str_replace("[exibir_vazios_status]", $chamados[0] ? "false" : "true", $this->conteudo);
    print $this->conteudo;
    exit();
  }

  function geraConteudoMatricial($dados_chamado, $ordem) {
    // Formata Dados
    $dados_chamado["categoria_nome"] = preg_replace("/\\[[^\\]]*\\]/", "", str_replace("»", "-", $dados_chamado["categoria_nome"]));
    $dados_chamado["abertura"] .= " por ".$this->reduzNome($this->buscaUsuarioEtapa($dados_chamado["id"], 1, "usuario_nome"));
    $dados_chamado["fechamento"] .= $dados_chamado["fechamento"] ? " por ".$this->reduzNome($this->buscaUsuarioEtapa($dados_chamado["id"], 6, "usuario_nome")) : "";
    // Cabeçalho
    $conteudo[] = "#218".str_repeat("#196", 73)."#194".str_repeat("#196", 60)."#191";
    $conteudo[] = "#179<large>".$this->formataTexto("<b>OLÉ TELECOM</b>", 36, "center")."</large> #179<large>".$this->formataTexto("<b>CHAMADO Nº {$dados_chamado["codigo"]}</b>", 30, "center")."</large>#179";
    $conteudo[] = "#195".str_repeat("#196", 73)."#197".str_repeat("#196", 60)."#180";
    // Dados do Chamado
    $conteudo[] = "#179".$this->formataTexto("<b>Solicitante:</b> {$dados_chamado["solicitante"]}", 73)."#179".$this->formataTexto("<b>Abertura:</b>   {$dados_chamado["abertura"]}", 60)."#179";
    $conteudo[] = "#179".$this->formataTexto("<b>Categoria:</b>   {$dados_chamado["categoria_nome"]}", 73)."#179".$this->formataTexto("<b>Fechamento:</b> {$dados_chamado["fechamento"]}", 60)."#179";
    $conteudo[] = "#195".str_repeat("#196", 73)."#193".str_repeat("#196", 60)."#180";
    // Dados do Cliente
    $dados_cliente = $this->buscaDadosCliente($dados_chamado["cliente_id"]);
    if ($dados_cliente) {
      $dados_cliente["garantia"] = ($this->ehClienteWaveGarantia($dados_chamado["cliente_id"])) ? "Sim" : "Não";
      $dados_cliente["servicos"] = $this->buscaServicosCliente($dados_chamado["cliente_id"]);
      $conteudo[] = "#179<large>".$this->formataTexto("<b>DADOS DO CLIENTE</b>", 67, "center")."</large>#179";
      $conteudo[] = "#195".str_repeat("#196", 134)."#180";
      $conteudo[] = "#179".$this->formataTexto("<b>Nome:</b>     [<b>{$dados_chamado["cliente_id"]}</b>] {$dados_cliente["nome"]}", 134)."#179";
      $conteudo[] = "#179".$this->formataTexto("<b>Endereço:</b> {$dados_cliente["endereco_completo"]}", 134)."#179";
	  	$conteudo[] = "#179".$this->formataTexto("<b>Ponto de Referência:</b> {$dados_cliente["ponto_referencia"]}", 134)."#179";
      $conteudo[] = "#179".$this->formataTexto("<b>Telefone:</b> {$dados_cliente["telefones"]}", 134)."#179";
      $conteudo[] = "#179".$this->formataTexto("<b>Serviço:</b>  {$dados_cliente["servicos"]}", 120).$this->formataTexto("<b>Garantia:</b> ".($this->ehClienteWaveGarantia($dados_chamado["cliente_id"]) ? "Sim" : "Não"), 14)."#179";
    }
    // Detalhamento
    $conteudo[] = "#195".str_repeat("#196", 134)."#180";
    $conteudo[] = "#179<large>".$this->formataTexto("<b>DETALHAMENTO</b>", 67, "center")."</large>#179";
    $conteudo[] = "#195".str_repeat("#196", 134)."#180";
    $detalhamento = $this->buscaEtapasImpressaoMatricial($dados_chamado["id"]);
    for ($i = 0; $i < 17; $i++) {
      $conteudo[] = "#179".$this->formataTexto($detalhamento[$i], 134)."#179";
    }
    // Assinatura
    $conteudo[] = "#195".str_repeat("#196", 83)."#194".str_repeat("#196", 50)."#180";
    $conteudo[] = "#179".$this->formataTexto("Eu ___________________________________, declaro que os serviços solicitados foram", 83)."#179".$this->formataTexto("<b>Ass.:</b>", 50)."#179";
    $conteudo[] = "#179".$this->formataTexto("realizados e os equipamentos encontram-se em perfeito estado de funcionamento.", 83)."#179".str_repeat(" ", 50)."#179";
    // Valor/Entrada/Saída/Técnico
    $conteudo[] = "#195".str_repeat("#196", 27)."#194".str_repeat("#196", 27)."#194".str_repeat("#196", 27)."#197".str_repeat("#196", 50)."#180";
    $conteudo[] = "#179".$this->formataTexto("<b>Valor:</b>", 27)."#179".$this->formataTexto("<b>Entrada:</b>", 27)."#179".$this->formataTexto("<b>Saída:</b>", 27)."#179".$this->formataTexto("<b>Técnico:</b>", 50)."#179";
    $conteudo[] = "#195".str_repeat("#196", 27)."#193".str_repeat("#196", 27)."#193".str_repeat("#196", 27)."#193".str_repeat("#196", 50)."#180";
    // Rodapé
    if($dados_cliente["origem_id"] == 6){
    	$conteudo[] = "#179".$this->formataTexto("TEC Service - Rua Sete de Setembro, 38, Kobrasol, São José, SC, CEP 88102-030 - Fone (48) 3028-0000", 134, "center")."#179";
    }else{
    	$conteudo[] = "#179".$this->formataTexto("OLÉ Telecom - Rua Altamiro Guimarães, 265, Sala 101, Centro, Florianópolis, SC, CEP 88015-510 - Fone (48) 3028-0000", 134, "center")."#179";
    }
    $conteudo[] = "#192".str_repeat("#196", 134)."#217";
    // Retorna Conteúdo
    return "<small>".implode($conteudo, "\n");
  }

  function buscaEtapasImpressaoMatricial($chamado_id, $exibir_internos = 0) {
    $etapas = $this->buscaEtapas($chamado_id, 0, !$exibir_internos);
    foreach ($etapas as $etapa) {
      $etapa["usuario_nome"] = $this->reduzNome($etapa["usuario_nome"]);
      $etapa["tipo_nome"] = substr($etapa["tipo_nome"], 12);
      $conteudo[] = "<b>{$etapa["usuario_nome"]}</b> em {$etapa["datahora"]} - <b>{$etapa["tipo_nome"]}</b>: {$etapa["conteudo"]["conteudo"]}";
    }
    return $conteudo;
  }

  function reduzNome($nome) {
    $nome = explode(" ", $nome);
    return $nome[0]." ".$nome[count($nome) - 1];
  }

  function formataTexto($texto, $comprimento, $alinhamento = "left") {
    if (strlen(strip_tags($texto)) > $comprimento) {
      $texto = substr(trim($texto), 0, $comprimento);
    } else {
      $texto = trim($texto);
    }
    $comprimento_texto = strlen(strip_tags($texto));
    if ($alinhamento == "left") {
      return $texto.str_repeat(" ", $comprimento - $comprimento_texto);
    } elseif ($alinhamento == "right") {
      return str_repeat(" ", $comprimento - $comprimento_texto).$texto;
    } else {
      return str_repeat(" ", floor(($comprimento - $comprimento_texto) / 2)).$texto.str_repeat(" ", ceil(($comprimento - $comprimento_texto) / 2));
    }
  }

  function geraConteudoImpressao($ordem) {
    $conteudo = $this->carregaArquivo("chamados.imprimir.inc.php");
    // Separação de Folhas
    $conteudo = str_replace('[separacao]', $ordem != 0 ? 'page-break-before:always' : '', $conteudo);
    if ($this->dados_item) {
      // Trata Dados
      $this->dados_item["titulo"] = $this->dados_item["cliente_id"] || $this->dados_item["condominio_id"] ? "CHAMADO" : "CHAMADO INTERNO";
      $this->dados_item["abertura"] .= " por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 1, "usuario_nome");
      $this->dados_item["fechamento"] .= $this->dados_item["fechamento"] ? " por ".$this->buscaUsuarioEtapa($this->dados_item["id"], 6, "usuario_nome") : "";
      // Substitui Dados Chamado
      foreach ($this->dados_item as $campo_nome => $campo_valor) {
        $conteudo = str_replace("[$campo_nome]", $campo_valor, $conteudo);
      }
      if ($this->dados_item["cliente_id"]) {
        $conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $conteudo);
        // Substitui Dados Cliente
        $dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);
        if ($dados_cliente) {
			if ($dados_cliente["pessoa_tipo_id"] == 1) {
				$dados_cliente["cpf_cnpj"] = "";
			}
        	$dados_cliente["garantia"] = $this->ehClienteWaveGarantia($this->dados_item["cliente_id"]) ? "Sim" : "Não";
          $dados_cliente["servicos"] = $this->buscaServicosCliente($this->dados_item["cliente_id"]);
          $autenticacao = $this->buscaAutenticacao($this->dados_item["cliente_id"]);
          if ($autenticacao) {
          	$dados_cliente["ponto_de_acesso"] = $autenticacao;
          	$dados_cliente["tipo_ponto_acesso"] = "Usuario:";
          } else {
          	$dados_cliente["ponto_de_acesso"] = $this->buscaMac($this->dados_item["cliente_id"]);
          	$dados_cliente["tipo_ponto_acesso"] = "MAC:";
          }
          foreach ($dados_cliente as $campo_nome => $campo_valor) {
            $conteudo = str_replace("[cliente_$campo_nome]", strip_tags($campo_valor), $conteudo);
          }
        }
      } elseif ($this->dados_item["condominio_id"]) {
        $conteudo = preg_replace('/<tbody id="grupo_dados_cliente">.*?<\/tbody>\s+/s', '', $conteudo);
        $dados_condominio = $this->buscaDadosCondominio($this->dados_item["condominio_id"]);
        if ($dados_condominio) {
          foreach ($dados_condominio as $campo_nome => $campo_valor) {
            $conteudo = str_replace("[condominio_$campo_nome]", strip_tags($campo_valor), $conteudo);
          }
        }
      } else {
        $conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $conteudo);
        $conteudo = preg_replace('/<tbody id="grupo_dados_cliente">.*?<\/tbody>\s+/s', '', $conteudo);
      }
      // Etapas
      $conteudo = str_replace("[chamado]", $this->dados_item["id"], $conteudo);
      $conteudo = str_replace("[etapas]", $this->buscaEtapasImpressao($this->dados_item["id"], true, 0, false), $conteudo);
	  if ($_GET["periodo"] || ($_GET["inicio"] && $_GET["fim"])) {
		$conteudo = str_replace("[etapas_altura]", "auto", $conteudo);
	  } else {
		$conteudo = str_replace("[etapas_altura]", $this->dados_item["cliente_id"] ? "500px" : ($this->dados_item["condominio_id"] ? "570px" : "640px"), $conteudo);
	  }
    } else {
      $conteudo = preg_replace('/<tbody id="grupo_dados_condominio">.*?<\/tbody>\s+/s', '', $conteudo);
      $conteudo = str_replace("[titulo]", "CHAMADO", $conteudo);
      $conteudo = str_replace("[chamado]", "0", $conteudo);
      $conteudo = str_replace("[etapas]", $this->buscaEtapasImpressao(null, 0, true, false), $conteudo);
    }
    // Remove conteúdo entre colchetes
	$conteudo = preg_replace('/\[[^\]]*\]/', '', $conteudo);
	return $conteudo;
  }

  function buscaEtapasImpressao($chamado_id, $exibir_internos, $exibir_vazios, $retorno_tela = true) {
    if ($chamado_id && !$exibir_vazios) {
      $listagem = $this->geraListagemEtapasImpressao($this->buscaEtapas($chamado_id, 0, !$exibir_internos, false, true, true), "ÚLTIMOS COMENTÁRIOS");
    }
    if ($exibir_vazios) {
      $listagem = $this->geraListagemEtapasImpressao($this->buscaEtapas($chamado_id, 0, 0, true));
    	$listagem .= $listagem ? '<div style="height:17px"></div>' : '';
      $linhas_comentario = array('<table width="100%" class="margem">'.str_repeat('<tr><td class="margem_td" style="height:17px;border-right:0px;border-left:0px;border-top:0px"></td></tr>', 2).'<tr><td height="15" style="border:0px;"></td></tr></table>');
      $listagem .= $this->geraListagemEtapasImpressao($linhas_comentario, "COMENTÁRIOS");
      $listagem .= $listagem ? '<div style="height:17px"></div>' : '';
    }
		$listagem = preg_replace('/\[[^\]]*\]/', '', $listagem);
    if ($retorno_tela) {
      print "document.getElementById('etapas_$chamado_id').innerHTML = '".addslashes($listagem)."';";
    } else {
      return preg_replace('/\s*(<br>)+\s*/i', "<br>", $listagem);
    }
  }

  function geraListagemEtapasImpressao($etapas, $titulo = "DETALHAMENTO") {
    if ($etapas) {
      $conteudo[] = '<table class="quadro" cellpadding="0" cellspacing="0" border="0" style="margin:-1px;width:101%">';
      // Título
      $conteudo[] = '<tr><td class="titulo"><b>'.$titulo.'</b></td></tr>';
      for ($i = 0; $i < sizeof($etapas); $i++) {
        $conteudo[] = '<tr><td>';
        if (is_array($etapas[$i])) {
        	if ($etapas[$i]["usuario_id"] || $etapas[$i]["tipo_id"] != 19) {
          	$conteudo[] = sprintf("%02s", sizeof($etapas) - $i).". <b>{$etapas[$i]["usuario_nome"]}</b> em <b>{$etapas[$i]["datahora"]}</b><br>";
        	}
          $conteudo[] = $this->formataEtapaConteudo($etapas[$i]);
        } else {
          $conteudo[] = $etapas[$i];
        }
        $conteudo[] = '</td></tr>';
      }
      $conteudo[] = '</table>';
      return implode($conteudo);
    } else {
      return "";
    }
  }

	###############
	### Remover ###
	###############

	function remover() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$this->conteudo .= $this->carregaArquivo("chamados.remover.inc.php");
				$acoes  = '<td bgcolor="#505050"><b>&nbsp;Remover&nbsp;</b></td>';
				$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->removerChamado($this->dados_modulo["id"]);

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
				// Fechar
				$this->fecharJanela();
				
			}

		} else {

			$this->fechar();

		}

	}
  
	function removerChamado($chamado_id) {

		if ($chamado_id) {
			$this->bancodedados->query("DELETE FROM tab_chamados_etapas WHERE cha_id = {$chamado_id}");
			$this->bancodedados->query("DELETE FROM rel_cha_usu_ehacompanhado WHERE cha_id = {$chamado_id}");
			$this->bancodedados->query("DELETE FROM rel_cha_usu_ehfavorito WHERE cha_id = {$chamado_id}");
			$this->bancodedados->query("DELETE FROM tab_chamados_visualizacoes WHERE cha_id = {$chamado_id}");
			$this->bancodedados->query("DELETE tab_visitas_comentarios FROM tab_visitas INNER JOIN tab_visitas_comentarios ON (tab_visitas_comentarios.vis_id = tab_visitas.vis_id) WHERE cha_id = {$chamado_id}");
			$this->bancodedados->query("DELETE FROM tab_visitas WHERE cha_id = {$chamado_id}");
			$this->bancodedados->query("DELETE FROM tab_chamados WHERE cha_id = {$chamado_id}");
		}

	}

  #############################
  ### Favoritos - Adicionar ###
  #############################

  function favoritosAdicionar($chamado_id, $usuario_id) {
    $query = "INSERT INTO rel_cha_usu_ehfavorito (cha_id, usu_id) VALUES ($chamado_id, $usuario_id)";
    $this->bancodedados->query($query);
    print 'if (document.getElementById("favoritos_chamado_'.$chamado_id.'")) document.getElementById("favoritos_chamado_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_remover.png" id="favoritos_icone" width="13" height="13" border="0" title="Remover de Favoritos" onclick="favoritosRemover('.$chamado_id.')">\';';
  }

  ###########################
  ### Favoritos - Remover ###
  ###########################

  function favoritosRemover($chamado_id, $usuario_id) {
    $query = "DELETE FROM rel_cha_usu_ehfavorito WHERE cha_id = $chamado_id AND usu_id = $usuario_id";
    $this->bancodedados->query($query);
    print 'if (document.getElementById("favoritos_chamado_'.$chamado_id.'")) document.getElementById("favoritos_chamado_'.$chamado_id.'").innerHTML = \'<img src="imagens/favoritos_adicionar.png" id="favoritos_icone" width="13" height="13" border="0" title="Adicionar ao Favoritos" onclick="favoritosAdicionar('.$chamado_id.')">\';';
  }

  ###########################
  ### Remover Etapa - RDS ###
  ###########################

  function removerEtapa($etapa_id, $atualiza_listagem = true) {
  	$query = "
      SELECT
        che_lida AS lida
      FROM
        tab_chamados_etapas
      WHERE
        che_id = $etapa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados && !$dados["lida"]) {  	
	  	$etapas_filhas = $this->buscaEtapasFilhas($etapa_id);
	    if ($etapas_filhas) {
	      foreach ($etapas_filhas as $etapa_filha_id) {
	        $this->removerEtapa($etapa_filha_id, false);
	      }
	    }
	    $query = "DELETE FROM tab_chamados_etapas WHERE che_id = $etapa_id";
	    $this->bancodedados->query($query);
	    // Atualiza Listagem de Etapas
	    if ($atualiza_listagem) {
	      print "buscarEtapas(chamado_atual, document.getElementById('tipo_1').checked ? 0 : 1);";
	    }
    }
  }

  ####################
  ### Editar Etapa ###
  ####################

  function editar_etapa() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $dados_etapa = $this->buscaDadosEtapa($_GET["etapa"]);
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("chamados.editar_etapa.inc.php");
        $this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
        $this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaEtapasTipos($this->dados_item["categoria_id"], $this->dados_item["etapa_atual"], $this->dados_item["id"]), $dados_etapa["tipo_id"], true, "tipo", "100%", null, false), $this->conteudo);
        $this->conteudo = str_replace("[conteudo]", $dados_etapa["conteudo"]["conteudo"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_etapa&i={$_GET["i"]}&etapa={$_GET["etapa"]}", $this->conteudo);
      } else {
        $this->editaEtapaConteudo($_GET["etapa"], $_POST);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fecharJanela();
    }
  }
}

?>
