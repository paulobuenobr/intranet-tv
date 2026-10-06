<?

set_time_limit(0);
/*
date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$central_telefonica = controle::instanciaObjeto("cron_central_telefonica", $bancodedados);
$central_telefonica->gerarAgregado();
//$central_telefonica->importarLigacoes();
//*/
class cron_central_telefonica {

  var $bancodedados;
  var $bancodedados_central;
  
  const SITUACAO_ATENDIDA = 1;
  const SITUACAO_NAO_ATENDIDA_PERDIDA = 2;
  const SITUACAO_NAO_ATENDIDA_RECUPERADA = 3;

  const TIPO_ENTRANTE = 1;
  const TIPO_SAINTE = 2;
  const TIPO_INTERNA = 3;
  
  var $ramais;
  var $setores;
  
  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->bancodedados_central = controle::instanciaObjeto("bancodedados", true, "192.168.170.1", "root", "df08072005", "asterisk");
  }
  
  #########################
  ### Importar Ligações ###
  #########################
  
  function importarLigacoes() {
  	$this->geraEstruturaSetores();
  	$query = "
 	  	SELECT
  	    calldate,
  	    src,
  	    dst,
  	    dcontext,
  	    channel,
  	    dstchannel,
  	    lastapp,
  	    lastdata,
  	    duration,
  	    billsec,
  	    disposition,
  	    userfield
  	  FROM
  	    cdr
  	  WHERE
        -- calldate LIKE '2014-07-09%' AND
        calldate >= NOW() - INTERVAL 1 HOUR AND
        -- calldate >= '2017-10-07 09:10:57' AND
        lastapp NOT IN ('ChanSpy', 'AgentLogin', 'VoiceMail', 'VoiceMailMain') AND
        src NOT IN ('sipp') AND
        dst NOT LIKE '*%' AND
        dcontext != 'qualitintas'";
    $query = $this->bancodedados_central->query($query);
    while (($dados = $this->bancodedados_central->resultadoArray($query)) != false) {
    	$ligacao["cls_id"] = $this->pegaSituacaoLigacao($dados, $ligacao);
    	$ligacao["ctl_data_hora"] = $dados["calldate"];
    	$ligacao["ctl_duracao"] = intval($dados["duration"], 10);
    	$ligacao["ctl_espera"] = $ligacao["cls_id"] == self::SITUACAO_ATENDIDA ? $dados["duration"] - $dados["billsec"] : $ligacao["ctl_duracao"];
    	$ligacao["ctl_fila"] = $this->pegaFilaLigacao($dados, $ligacao);
    	$ligacao["ctl_origem"] = $this->pegaOrigemLigacao($dados, $ligacao);
    	$ligacao["ctl_destino"] = $this->pegaDestinoLigacao($dados, $ligacao);
    	$ligacao["clt_id"] = $this->pegaTipoLigacao($dados, $ligacao);
    	$ligacao["cts_origem_id"] = $this->pegaSetorOrigemLigacao($dados, $ligacao);
    	$ligacao["cts_destino_id"] = $this->pegaSetorDestinoLigacao($dados, $ligacao);
    	$ligacao["cli_id"] = $this->pegaClienteLigacao($dados, $ligacao);
    	$ligacao["usu_origem_id"] = $this->pegaUsuarioOrigemLigacao($dados, $ligacao);
    	$ligacao["usu_destino_id"] = $this->pegaUsuarioDestinoLigacao($dados, $ligacao);
    	$ligacao["ctl_arquivo_gravacao"] = $ligacao["cls_id"] == self::SITUACAO_ATENDIDA ? substr($dados["userfield"], 0, 32) : null;
    	// Insere Registro
    	if ((($ligacao["clt_id"] == self::TIPO_ENTRANTE && $ligacao["ctl_origem"]) || ($ligacao["clt_id"] == self::TIPO_SAINTE && $ligacao["ctl_destino"]) || ($ligacao["ctl_origem"] && $ligacao["ctl_destino"])) && !$this->buscaLigacao($ligacao)) {
    	//if ((($ligacao["clt_id"] == self::TIPO_ENTRANTE && $ligacao["ctl_origem"]) || ($ligacao["clt_id"] == self::TIPO_SAINTE && $ligacao["ctl_destino"]) || ($ligacao["ctl_origem"] && $ligacao["ctl_destino"]))) {
	    	// Ajustes
	    	if ($ligacao["clt_id"] == self::TIPO_INTERNA) {
	    		$ligacao["ctl_fila"] = null;
	    	}
	    	if ($ligacao["ctl_origem"] == "-") {
	    		$ligacao["ctl_origem"] = null;
	    	}
	    	// Insere registro
    		$this->insereRegistro("tab_central_telefonica_ligacoes", $ligacao);
    		if ($ligacao["cls_id"] == self::SITUACAO_ATENDIDA) {
    			if ($ligacao["clt_id"] == self::TIPO_ENTRANTE) {
    				// Entrante
    				$this->atualizaLigacaoRecuperada($ligacao["ctl_origem"], $ligacao["ctl_data_hora"]);
    			} elseif ($ligacao["clt_id"] == self::TIPO_SAINTE) {
    				// Sainte
    				$this->atualizaLigacaoRecuperada($ligacao["ctl_destino"], $ligacao["ctl_data_hora"]);
    			}
    		}
    	}
    }
  }
  
  function geraEstruturaSetores() {
  	$query = "
  		SELECT
  		  cts_id AS	id,
  		  cts_nome AS	setor,
  		  cts_ramais AS ramais,
  		  cts_agentes AS agentes
  		FROM
  		  tab_central_telefonica_setores";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["ramais"] = explode(",", $dados["ramais"]);
    	$dados["agentes"] = explode(",", $dados["agentes"]);
    	for ($i = 0; $i < count($dados["ramais"]); $i++) {
    		$this->ramais["SIP/{$dados["ramais"][$i]}"] = $dados["id"];
    	}
    	for ($i = 0; $i < count($dados["agentes"]); $i++) {
    		$this->ramais["Agent/{$dados["agentes"][$i]}"] = $dados["id"];
    	}
    	$this->setores[$dados["setor"]] = $dados["id"];
    }
  }
  
  function buscaLigacao($dados) {
  	$query = "
  		SELECT
  			* 
  		FROM 
  			tab_central_telefonica_ligacoes 
  		WHERE 
  			ctl_data_hora = '{$dados["ctl_data_hora"]}' AND
  			ctl_origem ".(!$dados["ctl_origem"] || $dados["ctl_origem"] == "-" ? "IS NULL" : "= '{$dados["ctl_origem"]}'")." AND
  			ctl_destino ".(!$dados["ctl_destino"] || $dados["ctl_destino"] == "-" ? "IS NULL" : "= '{$dados["ctl_destino"]}'");
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }
  
  function pegaFilaLigacao(&$dados, &$ligacao) {
  	if ($dados["lastapp"] == "Queue") {
      preg_match("/^[^,]+/i", $dados["lastdata"], $fila);
  		return $fila[0];
  	} elseif (preg_match("/^[a-z_]{4,}.*$/i", $dados["dst"], $fila)) {
      return $fila[0];
  	} elseif ($dados["lastapp"] == "Goto" && preg_match("/^[a-z_]{4,}[^,]*,([a-z_]{4,}[^,]*),1$/i", $dados["lastdata"], $fila)) {
      return $fila[1];
  	}
  }
  
  function pegaOrigemLigacao(&$dados, &$ligacao) {
  	if ($dados["src"] == "asterisk") {
  		return "-";
  	} elseif (substr($dados["channel"], 0, 6) == "Agent/") {
  		return $dados["channel"];
  	} elseif (is_numeric($dados["src"]) && strlen($dados["src"]) > 3 && preg_match("/^(SIP\\/[0-9]{3})-.*$/i", $dados["channel"], $origem)) {
  		return $origem[1];
  	} elseif (preg_match("/^0*(0800[0-9]*)$/", $dados["src"], $origem)) {
  		return $origem[1];
  	} elseif (preg_match("/^0*([1-9][0-9])?([1-9][0-9])?([1-9][0-9]{6,7})$/", $dados["src"], $origem)) {
  		if (intval($origem[1], 10) && !intval($origem[2], 10)) {
  			return $origem[1].$origem[3];
  		} elseif (intval($origem[1], 10) && intval($origem[2], 10)) {
  			return $origem[2].$origem[3];
  		} else {
  			return "48".$origem[3];
  		}
  	} elseif ($dados["src"] == $dados["dst"] && preg_match("/^(SIP\\/[0-9]{3})-.*$/i", $dados["channel"], $destino)) {
  		return $destino[1];
  	} elseif (is_numeric($dados["src"]) && intval($dados["src"], 10) > 99 && strlen($dados["src"]) == 3) {
  		return "SIP/".$dados["src"];
  	}
  }
  
  function pegaDestinoLigacao(&$dados, &$ligacao) {
  	if ($ligacao["ctl_fila"] && $ligacao["cls_id"] != self::SITUACAO_ATENDIDA) {
  		return null;
  	}
  	if (substr($dados["dstchannel"], 0, 6) == "Agent/") {
  		return $dados["dstchannel"];
  	} elseif (is_numeric($dados["dst"]) && strlen($dados["dst"]) <= 4 && preg_match("/^(SIP\\/[0-9]{3,4})-.*$/i", $dados["dstchannel"], $destino)) {
  		return $destino[1];
  	} elseif ((($dados["src"] == "asterisk") || (is_numeric($dados["src"]) && strlen($dados["src"]) >= 10)) && is_numeric($dados["dst"]) && strlen($dados["dst"]) >= 10 && preg_match("/^(SIP\\/[0-9]{3,4})-.*$/i", $dados["dstchannel"], $destino)) {
  		return $destino[1];
  	} elseif (preg_match("/^0*(0800[0-9]*)$/", $dados["dst"], $destino)) {
  		return $destino[1];
  	} elseif (preg_match("/^0*(400[24][0-9]*)$/", $dados["dst"], $destino)) {
  		return $destino[1];
  	} elseif (preg_match("/^[0-9]{4}#[1-9][0-9]([1-9][0-9])([1-9][0-9]{7})$/", $dados["dst"], $destino)) {
  		return $destino[1].$destino[2];
  	} elseif (preg_match("/^0*0([1-9][0-9])25([1-9][0-9]{7})$/", $dados["dst"], $destino)) {
  		return $destino[1].$destino[2];
  	} elseif (preg_match("/^0*([1-9][0-9])?([1-9][0-9])?([1-9][0-9]{7})$/", $dados["dst"], $destino)) {
  		if (intval($destino[1], 10) && intval($destino[2], 10)) {
  			return $destino[2].$destino[3];
  		} else {
  			return "48".$destino[3];
  		}
  	} elseif (is_numeric($dados["dst"]) && strlen($dados["dst"]) == 3) {
  		if ($dados["dcontext"] == "agent_transfer") {
  			if ($dados["dstchannel"]) {
  	    	if (preg_match("/^(SIP\\/[0-9]{3,4})-.*$/i", $dados["dstchannel"], $destino)) {
  	    		return $destino[1];
  	    	} else {
  	    		$transfer = $this->buscaDadosTransferencia($dados);
  					if ($transfer) {
			    		$ligacao["ctl_fila"] = $this->pegaFilaLigacao($transfer, $transfer);
				    	if ($transfer["channel"] == $dados["dstchannel"]) {
				    		$temp = $ligacao["ctl_origem"];
				    		$ligacao["ctl_origem"] = $this->pegaOrigemLigacao($transfer, $transfer);
					    	return $temp;
				    	} else {
					    	return $this->pegaDestinoLigacao($transfer, $transfer);
				    	}
  					} else {
  						return null;
  					}
  	    	}
  			} else {
  				return null;
  			}
  		} else {
	  		return "SIP/{$dados["dst"]}";
  		}
  	} elseif (preg_match("/^(SIP\\/[0-9]{3,4})-.*$/i", $dados["dstchannel"], $destino)) {
  		return $destino[1];
  	} elseif (preg_match("/^(SIP\\/[0-9]{3,4})(,.*)?$/i", $dados["lastdata"], $destino)) {
  		return $destino[1];
  	} elseif (preg_match("/^[0-9]*([1-9][0-9])([1-9][0-9]{7})$/", $dados["dst"], $destino)) {
  		return $destino[1].$destino[2];
  	} elseif (is_numeric($dados["src"]) && strlen($dados["src"]) >= 8) {
  		if (preg_match("/^(SIP\\/[0-9]{3,4})-.*$/i", $dados["channel"], $destino)) {
  			return $destino[1];
  		}
  	} elseif (preg_match("/^0*10([1-9][0-9]{2,3})$/", $dados["dst"], $destino)) {
  		return "SIP/".$destino[1];
  	} elseif (is_numeric($dados["dst"]) && strlen($dados["dst"]) > 1) {
 			return $dados["dst"];
  	}
  }
  
  function buscaDadosTransferencia(&$dados) {
  	$query = "
 	  SELECT
  	    calldate,
  	    src,
  	    dst,
  	    dcontext,
  	    channel,
  	    dstchannel,
  	    lastapp,
  	    lastdata,
  	    duration,
  	    billsec,
  	    disposition,
  	    userfield
  	  FROM
  	    cdr
  	  WHERE
        calldate BETWEEN '{$dados["calldate"]}' - INTERVAL 1 HOUR AND '{$dados["calldate"]}' - INTERVAL 1 SECOND AND
        (channel = '{$dados["dstchannel"]}' OR dstchannel = '{$dados["dstchannel"]}')
      ORDER BY
        calldate DESC
      LIMIT 0, 1";
    $query = $this->bancodedados_central->query($query);
    return $this->bancodedados_central->resultadoArray($query);
  }
  
  function pegaUsuarioOrigemLigacao(&$dados, &$ligacao) {
  	if ($ligacao["ctl_origem"] && ($ligacao["clt_id"] == self::TIPO_INTERNA || $ligacao["clt_id"] == self::TIPO_SAINTE)) {
  		return $this->buscaUsuario($ligacao["ctl_origem"]);
  	}
  }
  
  function pegaUsuarioDestinoLigacao(&$dados, &$ligacao) {
  	if ($ligacao["ctl_destino"] && ($ligacao["clt_id"] == self::TIPO_INTERNA || $ligacao["clt_id"] == self::TIPO_ENTRANTE)) {
  		return $this->buscaUsuario($ligacao["ctl_destino"]);
  	}
  }
  
  function pegaClienteLigacao(&$dados, &$ligacao) {
  	if ($ligacao["clt_id"] == self::TIPO_ENTRANTE && strlen($ligacao["ctl_origem"]) == 10) {
  		$telefone = $ligacao["ctl_origem"];
  	} elseif ($ligacao["clt_id"] == self::TIPO_SAINTE && strlen($ligacao["ctl_destino"]) == 10) {
  		$telefone = $ligacao["ctl_destino"];
  	}
  	if ($telefone) {
			$ddd = substr($telefone, 0, 2);
			$numero = substr($telefone, 2, 4)."-".substr($telefone, 6, 4);
			$query = "
			  SELECT
			    tab_clientes.cli_id AS id
			  FROM
			  	tab_clientes_contatos_telefones
			    INNER JOIN tab_clientes ON (tab_clientes_contatos_telefones.cli_id = tab_clientes.cli_id)
			  WHERE
			    ccl_ddd = '$ddd' AND
			    ccl_numero = '$numero'
			  ORDER BY
			    IF(tab_clientes.cls_id = 2, 1, IF(tab_clientes.cls_id = 1, 2, tab_clientes.cls_id)),
			    clt_id DESC
			  LIMIT
			    0, 1";
			$query = $this->bancodedados->query($query);
			$cliente = $this->bancodedados->resultadoArray($query);
		  if ($cliente) {
		  	return $cliente["id"];
		  }
  	}
  }
  
  function pegaTipoLigacao(&$dados, &$ligacao) {
  	if ((substr($ligacao["ctl_origem"], 0, 4) == "SIP/" || substr($ligacao["ctl_origem"], 0, 6) == "Agent/") && (substr($ligacao["ctl_destino"], 0, 4) == "SIP/" || substr($ligacao["ctl_destino"], 0, 6) == "Agent/")) {
  		return self::TIPO_INTERNA;
  	} elseif (substr($ligacao["ctl_origem"], 0, 4) == "SIP/" || substr($ligacao["ctl_origem"], 0, 6) == "Agent/") {
  		return self::TIPO_SAINTE;
  	} else {
  		return self::TIPO_ENTRANTE;
  	}
  }
  
  function pegaSetorOrigemLigacao(&$dados, &$ligacao) {
  	if ($ligacao["clt_id"] != self::TIPO_ENTRANTE && array_key_exists($ligacao["ctl_origem"], $this->ramais)) {
  		return $this->ramais[$ligacao["ctl_origem"]];
  	}
  }
  
  function pegaSetorDestinoLigacao(&$dados, &$ligacao) {
  	if ($ligacao["clt_id"] != self::TIPO_SAINTE && array_key_exists($ligacao["ctl_destino"], $this->ramais)) {
  		return $this->ramais[$ligacao["ctl_destino"]];
  	} elseif ($ligacao["clt_id"] == self::TIPO_ENTRANTE && $ligacao["ctl_fila"]) {
  		$fila = explode("_", $ligacao["ctl_fila"]);
  		if (array_key_exists($fila[0], $this->setores)) {
  			return $this->setores[$fila[0]];
  		}
  	}
  }
  
  function pegaSituacaoLigacao(&$dados, &$ligacao) {
  	if ($dados["disposition"] == "ANSWERED" && $dados["dstchannel"]) {
  		return self::SITUACAO_ATENDIDA;
  	} else {
  		return self::SITUACAO_NAO_ATENDIDA_PERDIDA;
  	}
  }
  
  function insereRegistro($tabela, &$dados) {
  	foreach ($dados as $campo => $valor) {
  		$valores[] = is_int($valor) || (is_numeric($valor) && $valor[0] != '0') ? $valor : (!$valor ? "NULL" : "'$valor'"); 
  	}
  	$query = "INSERT INTO $tabela (".implode(", ", array_keys($dados)).") VALUES (".implode(", ", $valores).")";
		$this->bancodedados->query($query);
  }
  
  function atualizaLigacaoRecuperada($numero, $data_hora) {
  	if (strlen($numero) >= 8) {
  		$data_hora = date("Y-m-d H:i:s", strtotime("$data_hora -1 second"));
		$this->bancodedados->query("UPDATE tab_central_telefonica_ligacoes SET cls_id = 3 WHERE ctl_origem = '$numero' AND ctl_data_hora BETWEEN '".substr($data_hora, 0, 10)." 00:00:00' AND '$data_hora' AND cls_id = 2");
		$this->bancodedados->query("UPDATE tab_central_telefonica_ligacoes SET cls_id = 3 WHERE ctl_destino = '$numero' AND ctl_data_hora BETWEEN '".substr($data_hora, 0, 10)." 00:00:00' AND '$data_hora' AND cls_id = 2");
  	}
  }
  
  ######################
  ### Gerar Agregado ###
  ######################
  
  function gerarAgregado() {
  	$agregado = array();
  	$calldate = date("Y-m-d H", strtotime("1 hour ago"));
  	$query = "
  	  SELECT
  	    ctl_data_hora AS data_hora,
  	    ctl_duracao AS duracao,
  	    ctl_espera AS espera,
  	    ctl_fila AS fila,
  	    ctl_origem AS origem,
  	    ctl_destino AS destino,
  	    usu_origem_id AS usuario_origem_id,
  	    usu_destino_id AS usuario_destino_id,
  	    cts_origem_id AS setor_origem_id,
  	    cts_destino_id AS setor_destino_id,
  	    clt_id AS tipo_id,
  	    cls_id AS situacao_id
  	  FROM
  	    tab_central_telefonica_ligacoes
  	  WHERE
		ctl_data_hora >= '2014-08-19 00:00:00'
  	    -- ctl_data_hora BETWEEN '$calldate:00:00' AND '$calldate:59:59'";
  	$query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	// Dados
    	$dados["data"] = substr($dados["data_hora"], 0, 10);
    	$dados["mes"] = substr($dados["data_hora"], 0, 7);
    	$dados["hora"] = substr($dados["data_hora"], 11, 2);
    	$dados["tipo"] = $dados["tipo_id"] == self::TIPO_INTERNA ? "i" : "e";
    	$dados["setor"] = $dados["tipo_id"] == self::TIPO_ENTRANTE ? $this->buscaLigacaoSetor($dados["setor_destino_id"]) : $this->buscaLigacaoSetor($dados["setor_origem_id"]);
    	$dados["usuario"] = $dados["tipo_id"] == self::TIPO_ENTRANTE ? $dados["usuario_destino_id"] : $dados["usuario_origem_id"];
    	$dados["operador"] = $dados["tipo_id"] == self::TIPO_ENTRANTE ? $dados["destino"] : $dados["origem"];
    	// Agrupador
    	$dados["agrupador"] = $dados["data"].",".$dados["hora"].",".$dados["tipo"].",".$dados["setor"].",".$dados["operador"];
    	// Indicadores
    	if ($dados["tipo_id"] == self::TIPO_ENTRANTE) {
    		if ($dados["situacao_id"] == self::SITUACAO_ATENDIDA) {
    			$dados["duracao_recebida"] = $dados["duracao"];
    			$dados["espera_recebida"] = $dados["espera"];
    			$dados["recebida_atendida"] = 1;
    		} elseif ($dados["situacao_id"] == self::SITUACAO_NAO_ATENDIDA_PERDIDA) {
    			$dados["recebida_perdida"] = 1;
    		} else {
    			$dados["recebida_recuperada"] = 1;
    		}
    	} else {
    		if ($dados["situacao_id"] == self::SITUACAO_ATENDIDA) {
    			$dados["duracao_efetuada"] = $dados["duracao"];
    			$dados["efetuada_atendida"] = 1;
    		} elseif ($dados["situacao_id"] == self::SITUACAO_NAO_ATENDIDA_PERDIDA) {
    			$dados["efetuada_perdida"] = 1;
    		} else {
    			$dados["efetuada_recuperada"] = 1;
    		}
    	}
    	// Agregado
    	$agregado[$dados["agrupador"]]["data"] = $dados["data"];
    	$agregado[$dados["agrupador"]]["mes"] = $dados["mes"];
    	$agregado[$dados["agrupador"]]["hora"] = $dados["hora"];
    	$agregado[$dados["agrupador"]]["tipo"] = $dados["tipo"];
    	$agregado[$dados["agrupador"]]["setor"] = $dados["setor"];
    	$agregado[$dados["agrupador"]]["operador"] = $dados["operador"];
    	$agregado[$dados["agrupador"]]["dia_semana"] = date("w", strtotime($dados["data"]));
    	$agregado[$dados["agrupador"]]["usu_id"] = $dados["usuario"];
    	$agregado[$dados["agrupador"]]["ligacoes_efetuadas_atendidas"] += $dados["efetuada_atendida"];
    	$agregado[$dados["agrupador"]]["ligacoes_efetuadas_perdidas"] += $dados["efetuada_perdida"];
    	$agregado[$dados["agrupador"]]["ligacoes_efetuadas_recuperadas"] += $dados["efetuada_recuperada"];
    	$agregado[$dados["agrupador"]]["ligacoes_recebidas_atendidas"] += $dados["recebida_atendida"];
    	$agregado[$dados["agrupador"]]["ligacoes_recebidas_perdidas"] += $dados["recebida_perdida"];
    	$agregado[$dados["agrupador"]]["ligacoes_recebidas_recuperadas"] += $dados["recebida_recuperada"];
    	$agregado[$dados["agrupador"]]["total_duracao_recebidas"] += $dados["duracao_recebida"];
    	$agregado[$dados["agrupador"]]["total_duracao_efetuadas"] += $dados["duracao_efetuada"];
    	$agregado[$dados["agrupador"]]["total_espera_recebidas"] += $dados["espera_recebida"];
    	// Ligação Interna
    	if ($dados["tipo"] == "i") {
    		// Dados
    		$dados["operador"] = $dados["destino"];
    		$dados["setor"] = $this->buscaLigacaoSetor($dados["setor_destino_id"]);
    		$dados["usuario"] = $dados["usuario_destino_id"];
    		// Agrupador
    		$dados["agrupador"] = $dados["data"].",".$dados["hora"].",".$dados["tipo"].",".$dados["setor"].",".$dados["operador"];
    		// Agregado
	    	$agregado[$dados["agrupador"]]["data"] = $dados["data"];
	    	$agregado[$dados["agrupador"]]["mes"] = $dados["mes"];
	    	$agregado[$dados["agrupador"]]["hora"] = $dados["hora"];
	    	$agregado[$dados["agrupador"]]["tipo"] = $dados["tipo"];
	    	$agregado[$dados["agrupador"]]["setor"] = $dados["setor"];
	    	$agregado[$dados["agrupador"]]["operador"] = $dados["operador"];
	    	$agregado[$dados["agrupador"]]["dia_semana"] = date("w", strtotime($dados["data"]));
	    	$agregado[$dados["agrupador"]]["usu_id"] = $dados["usuario"];
	    	$agregado[$dados["agrupador"]]["ligacoes_recebidas_atendidas"] += $dados["efetuada_atendida"];
	    	$agregado[$dados["agrupador"]]["ligacoes_recebidas_perdidas"] += $dados["efetuada_perdida"];
	    	$agregado[$dados["agrupador"]]["ligacoes_recebidas_recuperadas"] += $dados["efetuada_recuperada"];
	    	$agregado[$dados["agrupador"]]["total_duracao_recebidas"] += $dados["duracao_efetuada"];
    	}
    }
    foreach ($agregado as $dados) {
    	// Corrige dados
    	$dados["data"] = "'{$dados["data"]}'";
    	$dados["mes"] = "'{$dados["mes"]}'";
    	$dados["setor"] = "'{$dados["setor"]}'";
    	$dados["operador"] = "'{$dados["operador"]}'";
    	$dados["tipo"] = "'{$dados["tipo"]}'";
    	$dados["usu_id"] = $dados["usu_id"] ? $dados["usu_id"] : "NULL";
    	$dados["ligacoes_efetuadas_atendidas"] = $dados["ligacoes_efetuadas_atendidas"] ? $dados["ligacoes_efetuadas_atendidas"] : 0;
    	$dados["ligacoes_efetuadas_perdidas"] = $dados["ligacoes_efetuadas_perdidas"] ? $dados["ligacoes_efetuadas_perdidas"] : 0;
    	$dados["ligacoes_efetuadas_recuperadas"] = $dados["ligacoes_efetuadas_recuperadas"] ? $dados["ligacoes_efetuadas_recuperadas"] : 0;
    	$dados["ligacoes_recebidas_atendidas"] = $dados["ligacoes_recebidas_atendidas"] ? $dados["ligacoes_recebidas_atendidas"] : 0;
    	$dados["ligacoes_recebidas_perdidas"] = $dados["ligacoes_recebidas_perdidas"] ? $dados["ligacoes_recebidas_perdidas"] : 0;
    	$dados["ligacoes_recebidas_recuperadas"] = $dados["ligacoes_recebidas_recuperadas"] ? $dados["ligacoes_recebidas_recuperadas"] : 0;
    	$dados["total_duracao_recebidas"] = $dados["total_duracao_recebidas"] ? $dados["total_duracao_recebidas"] : 0;
    	$dados["total_duracao_efetuadas"] = $dados["total_duracao_efetuadas"] ? $dados["total_duracao_efetuadas"] : 0;
    	$dados["total_espera_recebidas"] = $dados["total_espera_recebidas"] ? $dados["total_espera_recebidas"] : 0;
    	// Atualiza/insere registro
    	$id = $this->buscaAgregado($dados);
    	if ($id) {
    		$this->atualizaAgregado($id, $dados);
    	} else {
    		$this->insereAgregado($dados);
    	}
    }
  }
  
  function insereAgregado($dados) {
		$query = "
			INSERT INTO
				tab_central_telefonica_agregados (
					ctl_data, 
					ctl_mes, 
					ctl_hora, 
					ctl_setor, 
					ctl_operador, 
					ctl_ligacoes_efetuadas_atendidas, 
					ctl_ligacoes_efetuadas_perdidas, 
					ctl_ligacoes_efetuadas_recuperadas, 
					ctl_ligacoes_recebidas_atendidas, 
					ctl_ligacoes_recebidas_perdidas, 
					ctl_ligacoes_recebidas_recuperadas, 
					ctl_total_duracao_recebidas, 
					ctl_total_duracao_efetuadas, 
					ctl_total_espera_recebidas, 
					ctl_dia_semana,
					ctl_tipo,
					usu_id)
			VALUES (
				{$dados["data"]},
				{$dados["mes"]},
				{$dados["hora"]},
				{$dados["setor"]},
				{$dados["operador"]},
				{$dados["ligacoes_efetuadas_atendidas"]},
				{$dados["ligacoes_efetuadas_perdidas"]},
				{$dados["ligacoes_efetuadas_recuperadas"]},
				{$dados["ligacoes_recebidas_atendidas"]},
				{$dados["ligacoes_recebidas_perdidas"]},
				{$dados["ligacoes_recebidas_recuperadas"]},
				{$dados["total_duracao_recebidas"]},
				{$dados["total_duracao_efetuadas"]},
				{$dados["total_espera_recebidas"]},
				{$dados["dia_semana"]},
				{$dados["tipo"]},
				{$dados["usu_id"]})";
		$this->bancodedados->query($query);
  }
  
  function atualizaAgregado($id, $dados) {
		$query = "
			UPDATE
				tab_central_telefonica_agregados
		  SET
				ctl_ligacoes_efetuadas_atendidas = {$dados["ligacoes_efetuadas_atendidas"]}, 
				ctl_ligacoes_efetuadas_perdidas = {$dados["ligacoes_efetuadas_perdidas"]}, 
				ctl_ligacoes_efetuadas_recuperadas = {$dados["ligacoes_efetuadas_recuperadas"]}, 
				ctl_ligacoes_recebidas_atendidas = {$dados["ligacoes_recebidas_atendidas"]}, 
				ctl_ligacoes_recebidas_perdidas = {$dados["ligacoes_recebidas_perdidas"]}, 
				ctl_ligacoes_recebidas_recuperadas = {$dados["ligacoes_recebidas_recuperadas"]}, 
				ctl_total_duracao_recebidas = {$dados["total_duracao_recebidas"]}, 
				ctl_total_duracao_efetuadas = {$dados["total_duracao_efetuadas"]}, 
				ctl_total_espera_recebidas = {$dados["total_espera_recebidas"]}
			WHERE
		    ctl_id = $id";
		$this->bancodedados->query($query);
  }
  
  function buscaAgregado(&$agregado) {
  	$query = "
  	  SELECT
  	    ctl_id
  	  FROM
  	    tab_central_telefonica_agregados
  	  WHERE
  	    ctl_data = {$agregado["data"]} AND
  	    ctl_hora = {$agregado["hora"]} AND
  	    ctl_setor = {$agregado["setor"]} AND
  	    ctl_operador = {$agregado["operador"]} AND
  	    ctl_tipo = {$agregado["tipo"]}";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["id"];
    }
  }
  
  var $ligacao_setor = array();
  
  function buscaLigacaoSetor($ligacao_setor_id) {
  	if (!$ligacao_setor_id) {
  		return null;
  	}
  	if (array_key_exists($ligacao_setor_id, $this->ligacao_setor)) {
  		return $this->ligacao_setor[$ligacao_setor_id];
  	}
	  $query = "
 	    SELECT
 	      cts_nome AS nome
 	    FROM
 	      tab_central_telefonica_setores
 	    WHERE
	      cts_id = $ligacao_setor_id";
   	$query = $this->bancodedados->query($query);
   	$dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$this->ligacao_setor[$ligacao_setor_id] = $dados["nome"];
    	return $dados["nome"];
    }
  }
  
  function buscaUsuario($operador) {
  	if (substr($operador, 0, 3) == "SIP") {
  		$where = "usu_ramal = ".substr($operador, -3);
  	} else {
  		$where = "usu_agente_id = ".substr($operador, -3);
  	}
	  $query = "
 	    SELECT
 	      usu_id AS id
 	    FROM
 	      tab_usuarios
 	    WHERE
	      $where AND
		  uss_id = 1
 	    ORDER BY
 	      usu_id DESC
 	    LIMIT
 	      0, 1";
   	$query = $this->bancodedados->query($query);
   	$dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["id"];
    }
  }
  
  function buscaSetor($usuario_id) {
  	$query = "
  	  SELECT
        ors_id AS setor_id
  	  FROM
  	  	rel_usu_orf_ors_exerce	
  	  WHERE
  	    usu_id = $usuario_id
  	  LIMIT
  	    0, 1";
   	$query = $this->bancodedados->query($query);
   	$dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $this->buscaSetorPai($dados["setor_id"]);
    }
  }
    
  function buscaSetorPai($setor_id) {
  	if (in_array($setor_id, array(14, 15))) {
  		return $setor_id;
  	}
  	$query = "
  	  SELECT
  	    ors_id AS id,
  	    ors_pai AS pai_id
  	  FROM
  	    tab_organizacao_setores
  	  WHERE
  	    ors_id = $setor_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	if ($dados["pai_id"]) {
    		return $this->buscaSetorPai($dados["pai_id"]);
    	} else {
    		return $dados["id"];
    	}
    }
  }

  function ehLigacaoRecuperada($src, $calldate) {
	  $query = "
	    SELECT
	      COUNT(*) AS ligacoes
	  	FROM
	  	  cdr
	  	WHERE
	  	  src = '$src' and
	  	  calldate > '$calldate' AND
	  	  calldate <= '".substr($calldate, 0, 10)." 23:59:59' AND
	  	  disposition = 'ANSWERED' AND
	  	  dstchannel != ''
	  	ORDER BY
	  	  calldate";
   	$query = $this->bancodedados_central->query($query);
   	$dados = $this->bancodedados_central->resultadoArray($query);
    if ($dados) {
    	return $dados["ligacoes"];
    }
  }

}

?>
