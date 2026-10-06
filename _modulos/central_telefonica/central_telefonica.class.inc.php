<?

set_time_limit(0);

require_once("_global/modulo.class.inc.php");

class central_telefonica extends modulo {

  var $bancodedados_asterisk;
  
  var $dispositions = array("NO ANSWER" => "Não atendida", "ANSWERED" => "Atendida", "FAILED" => "Erro", "BUSY" => "Ocupada");
  var $situacao = array("1" => "Atendida", "2" => "Não Atendida", "3" => "Não atendida (Perdida)", "4" => "Não atendida (Recuperada)");
  var $filas = array ("Agendamento" => "Agendamento", "Comercial" => "Comercial", "Financeiro" => "Financeiro", "Ouvidoria" => "Ouvidoria", "Suporte" => "Suporte");
  
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
  
	##############
	### Painel ###
	##############
	
	function painel() {
    $this->conteudo .= $this->carregaArquivo("central_telefonica.painel.inc.php");
    $this->conteudo = str_replace('[extension]', $this->buscaRamal(), $this->conteudo);
    $this->conteudo = str_replace('[member_names]', $this->buscaMemberNames(), $this->conteudo);
    $this->conteudo = str_replace('[param_admin]', $this->permissoes["central_telefonica"]["painel_admin"] ? "<param name=\"admin\" value=\"true\">" : "", $this->conteudo);
	}
	  
  function buscaRamal() {
    $query = "
      SELECT
        usu_ramal AS ramal
      FROM
        tab_usuarios
      WHERE
        usu_id = {$_SESSION["usuario"]}";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["ramal"];
    }
  }

  function buscaMemberNames() {
    $query = "
      SELECT
        tab_usuarios.usu_nome AS nome,
        tab_usuarios.usu_ramal AS ramal,
        tab_usuarios.usu_agente_id AS agente
      FROM
        tab_usuarios
      WHERE
        tab_usuarios.uss_id = 1
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($dados["ramal"] || $dados["agente"]) {
    		if ($dados["agente"]) {
	      	$dados["memberName"] = "Agent/{$dados["agente"]}";
	      } else {
	      	$dados["memberName"] = "SIP/{$dados["ramal"]}";
	      }
	      $usuarios[] = "{$dados["memberName"]},{$dados["nome"]}";
    	}
    }
    return implode(";", $usuarios);
  }
  
  ###########################
  ### Relatório Detalhado ###
  ###########################
  
  function relatorio_detalhado() {
    if ($_POST) {
      $id = md5(uniqid(rand(), true));
      $_SESSION["POST"][$id] = $_POST;
      header("Location: ./?m=central_telefonica&a=relatorio_detalhado&i=$id");
    } else {
	    $this->conteudo .= $this->carregaArquivo("central_telefonica.relatorio_detalhado.inc.php");
	    $this->conteudo = str_replace('[origem_tipo]', $this->geraSelect($this->buscaNumeroTipos(), $_SESSION["POST"][$_GET["i"]]["origem_tipo"], true, "origem_tipo", "100px"), $this->conteudo);
	    $this->conteudo = str_replace('[origem]', $_SESSION["POST"][$_GET["i"]]["origem"], $this->conteudo);
	    $this->conteudo = str_replace('[destino_tipo]', $this->geraSelect($this->buscaNumeroTipos(), $_SESSION["POST"][$_GET["i"]]["destino_tipo"], true, "destino_tipo", "100px"), $this->conteudo);
	    $this->conteudo = str_replace('[destino]', $_SESSION["POST"][$_GET["i"]]["destino"], $this->conteudo);
	    $this->conteudo = str_replace('[setor_origem]', $this->geraSelect($this->buscaSetores(), $_SESSION["POST"][$_GET["i"]]["setor_origem"], true, "setor_origem", "243px"), $this->conteudo);
	    $this->conteudo = str_replace('[setor_destino]', $this->geraSelect($this->buscaSetores(), $_SESSION["POST"][$_GET["i"]]["setor_destino"], true, "setor_destino", "243px"), $this->conteudo);
	    $this->conteudo = str_replace('[fila]', $_SESSION["POST"][$_GET["i"]]["fila"], $this->conteudo);
	    $this->conteudo = str_replace('[inicio]', $_SESSION["POST"][$_GET["i"]] ? $_SESSION["POST"][$_GET["i"]]["inicio"] : date("d/m/Y"), $this->conteudo);
	    $this->conteudo = str_replace('[hora_inicio]', $_SESSION["POST"][$_GET["i"]]["inicio"] ? $_SESSION["POST"][$_GET["i"]]["hora_inicio"] : "", $this->conteudo);
	    $this->conteudo = str_replace('[fim]', $_SESSION["POST"][$_GET["i"]] ? $_SESSION["POST"][$_GET["i"]]["fim"] : date("d/m/Y"), $this->conteudo);
	    $this->conteudo = str_replace('[hora_fim]', $_SESSION["POST"][$_GET["i"]]["fim"] ? $_SESSION["POST"][$_GET["i"]]["hora_fim"] : "", $this->conteudo);
	    $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $_SESSION["POST"][$_GET["i"]]["tipo"], true, "tipo", "243px"), $this->conteudo);
	    $this->conteudo = str_replace('[situacao]', $this->geraSelect($this->situacao, $_SESSION["POST"][$_GET["i"]]["situacao"], true, "situacao", "243px"), $this->conteudo);
	    
	    $botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
	    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
	    $this->conteudo = str_replace('[resultado]', $this->geraResultadoDetalhado($_SESSION["POST"][$_GET["i"]]), $this->conteudo);
	    $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
	    $this->conteudo = str_replace('[action]', '?m=central_telefonica&a=relatorio_detalhado', $this->conteudo);
    }
  }
  
  function buscaNumeroTipos() {
  	$tipos["externo"] = "Externo";
  	$tipos["ramal"] = "Ramal";
    $tipos["agente"] = "Agente";
    return $tipos;
  }
  
  function buscaTipos() {
  	$tipos["entrantes"] = "Entrantes";
  	$tipos["saintes"] = "Saintes";
    $tipos["internas"] = "Internas";
    return $tipos;
  }
  
  function buscaTiposAnalitico() {
  	$tipos["i"] = "Internas";
  	$tipos["e"] = "Externas";
    return $tipos;
  }
  
  function buscaSetores($nome_como_indice = false) {
  	$query = "
  	  SELECT
  	    cts_id AS id,
  	    cts_nome AS nome
  	  FROM
  	    tab_central_telefonica_setores
  	  ORDER BY
  	    cts_nome";
  	$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($nome_como_indice) {
				$setores[$dados["nome"]] = $dados["nome"];
			} else {
				$setores[$dados["id"]] = $dados["nome"];
			}
		}
		return $setores;
  }
  
  function salvar() {
  	$query = "
  	  SELECT
  	    ctl_data_hora AS data_hora,
  	    IF(clt_id = 1, ctl_origem, ctl_destino) AS numero,
  	    ctl_arquivo_gravacao AS arquivo
  	  FROM
  	    tab_central_telefonica_ligacoes
  	  WHERE
  	    ctl_id = '{$_GET["i"]}'";
  	$query = $this->bancodedados->query($query);
  	$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$content = file_get_contents("http://192.168.170.1/monitor/{$dados["arquivo"]}.WAV");
			if ($content) {
				$filename = str_replace(array("(", ")") , "", strip_tags($this->formataTelefone($dados["numero"])))." ".str_replace(":" , "-", $dados["data_hora"]);
			  header('Content-disposition: attachment; filename="'.$filename.'"');
				header('Content-type: audio/x-wav');
				print $content;
				die();
			}
		}
		die("<h1>Arquivo não encontrado</h1><p>O arquivo que você está tentando acessar não foi encontrado no servidor de telefonia.</p>");
  }
  
  function geraResultadoDetalhado($post) {
  	if (!$post) {
  		return "";
  	}
		if ($post["fila"]) {
			$filtro[] = "ctl_fila LIKE '%{$post["fila"]}%'";
		}
		if ($post["origem"]) {
			$post["origem"] = preg_replace("/[^0-9]/", "", $post["origem"]);
			if ($post["origem_tipo"] == "ramal") {
				$filtro[] = "ctl_origem LIKE 'SIP/{$post["origem"]}'";
			} elseif ($post["origem_tipo"] == "agente") {
				$filtro[] = "ctl_origem LIKE 'Agent/{$post["origem"]}'";
			} else {
				$filtro[] = "ctl_origem LIKE '%{$post["origem"]}%'";
				if ($post["origem_tipo"] == "externo") {
					$filtro[] = "ctl_origem NOT LIKE 'SIP/%'";
					$filtro[] = "ctl_origem NOT LIKE 'Agent/%'";
				}
			}
		} elseif ($post["origem_tipo"]) {
			if ($post["origem_tipo"] == "ramal") {
				$filtro[] = "ctl_origem LIKE 'SIP/%'";
			} elseif ($post["origem_tipo"] == "agente") {
				$filtro[] = "ctl_origem LIKE 'Agent/%'";
			} else {
				$filtro[] = "ctl_origem NOT LIKE 'SIP/%'";
				$filtro[] = "ctl_origem NOT LIKE 'Agent/%'";
			}
		}
		if ($post["destino"]) {
			$post["destino"] = preg_replace("/[^0-9]/", "", $post["destino"]);
			if ($post["destino_tipo"] == "ramal") {
				$filtro[] = "ctl_destino LIKE 'SIP/{$post["destino"]}'";
			} elseif ($post["destino_tipo"] == "agente") {
				$filtro[] = "ctl_destino LIKE 'Agent/{$post["destino"]}'";
			} else {
				$filtro[] = "ctl_destino LIKE '%{$post["destino"]}%'";
				if ($post["destino_tipo"] == "externo") {
					$filtro[] = "ctl_destino NOT LIKE 'SIP/%'";
					$filtro[] = "ctl_destino NOT LIKE 'Agent/%'";
				}
			}
		} elseif ($post["destino_tipo"]) {
			if ($post["destino_tipo"] == "ramal") {
				$filtro[] = "ctl_destino LIKE 'SIP/%'";
			} elseif ($post["destino_tipo"] == "agente") {
				$filtro[] = "ctl_destino LIKE 'Agent/%'";
			} else {
				$filtro[] = "ctl_destino NOT LIKE 'SIP/%'";
				$filtro[] = "ctl_destino NOT LIKE 'Agent/%'";
			}
		}
		if ($post["setor_origem"]) {
			$filtro[] = "cts_origem_id = {$post["setor_origem"]}";
		}
		if ($post["setor_destino"]) {
			$filtro[] = "cts_destino_id = {$post["setor_destino"]}";
		}
		if ($post["inicio"]) {
			$post["inicio"] = substr($post["inicio"], 6, 4)."-".substr($post["inicio"], 3, 2)."-".substr($post["inicio"], 0, 2);
			$filtro[] = "ctl_data_hora >= '{$post["inicio"]} 00:00:00'";
		}
		if ($post["fim"]) {
			$post["fim"] = substr($post["fim"], 6, 4)."-".substr($post["fim"], 3, 2)."-".substr($post["fim"], 0, 2);
			$filtro[] = "ctl_data_hora <= '{$post["fim"]} 23:59:59'";
		}
		
		if ($post["hora_inicio"]) {
			$post["hora_inicio"] = str_replace(":", "", $post["hora_inicio"]);
			$filtro[] = "DATE_FORMAT(ctl_data_hora, '%H%i') >= {$post["hora_inicio"]}";
		}
		if ($post["hora_fim"]) {
			$post["hora_fim"] = str_replace(":", "", $post["hora_fim"]);
			$filtro[] = "DATE_FORMAT(ctl_data_hora, '%H%i') <= {$post["hora_fim"]}";
		}
		
		if ($post["tipo"]) {
			if ($post["tipo"] == "entrantes") {
				$filtro[] = "clt_id = 1";
			} elseif ($post["tipo"] == "saintes") {
				$filtro[] = "clt_id = 2";
			} elseif ($post["tipo"] == "internas") {
				$filtro[] = "clt_id = 3";
			}
		}
		if ($post["situacao"]) {
			if ($post["situacao"] == 1) {
				$filtro[] = "tab_central_telefonica_ligacoes.cls_id = 1";
			} elseif ($post["situacao"] == 2) {
				$filtro[] = "tab_central_telefonica_ligacoes.cls_id IN (2, 3)";
			} elseif ($post["situacao"] == 3) {
				$filtro[] = "tab_central_telefonica_ligacoes.cls_id = 2";
			} elseif ($post["situacao"] == 4) {
				$filtro[] = "tab_central_telefonica_ligacoes.cls_id = 3";
			}
		}
		if (!$filtro) {
			return "";
		}
		$filtro = implode(" AND\n        ", $filtro);
    $query = "
      SELECT
        ctl_id AS ligacao_id,
        DATE_FORMAT(ctl_data_hora, '%d/%m/%Y') AS data,
        DATE_FORMAT(ctl_data_hora, '%H:%i') AS hora,
        ctl_duracao AS duracao,
        ctl_espera AS espera,
        ctl_fila AS fila,
        ctl_origem AS origem,
        ctl_destino AS destino,
        ctl_arquivo_gravacao AS arquivo_gravacao,
        usu_origem_id AS usuario_origem_id,
        usu_destino_id AS usuario_destino_id,
        cli_id AS cliente_id,
        cls_nome AS situacao,
        cts_origem_id AS setor_origem_id,
        cts_destino_id AS setor_destino_id,
        clt_id AS tipo_id
      FROM
        tab_central_telefonica_ligacoes
        INNER JOIN tab_central_telefonica_ligacoes_situacoes ON (tab_central_telefonica_ligacoes.cls_id = tab_central_telefonica_ligacoes_situacoes.cls_id)
      WHERE
        $filtro
      ORDER BY
        ctl_data_hora";
	  $registros = array();
	  $permissao_ouvir = false;
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["count"] = count($registros) + 1;
			$dados["data_hora"] = str_replace(" (", "<br>(", $dados["data_hora"]);
			$dados["duracao"] = $this->formataDuracao($dados["duracao"]);
			$dados["situacao"] = str_replace(" (", "<br>(", $dados["situacao"]);
			$dados["origem"] = $this->formataTelefone($dados["origem"]);
			$dados["destino"] = $this->formataTelefone($dados["destino"]);
			
			if ($post["opcoes"] && in_array("exportar_csv", $post["opcoes"])) {
				if ($dados["usuario_origem_id"]) {
					$dados["origem_pessoa"] = $this->buscaUsuario($dados["usuario_origem_id"], false);
				}
				if ($dados["usuario_destino_id"]) {
					$dados["destino_pessoa"] = $this->buscaUsuario($dados["usuario_destino_id"], false);
				}
				if ($dados["cliente_id"]) {
					if ($dados["tipo_id"] == 1) {
						$dados["origem_pessoa"] = "[{$dados["cliente_id"]}] ".$this->buscaCliente($dados["cliente_id"], false);
					} else {
						$dados["destino_pessoa"] = "[{$dados["cliente_id"]}] ".$this->buscaCliente($dados["cliente_id"], false);
					}
				}
			} else {
				if ($dados["usuario_origem_id"]) {
					$dados["origem"] .= "<br>".$this->buscaUsuario($dados["usuario_origem_id"]);
				}
				if ($dados["usuario_destino_id"]) {
					$dados["destino"] .= "<br>".$this->buscaUsuario($dados["usuario_destino_id"]);
				}
				if ($dados["cliente_id"]) {
					if ($dados["tipo_id"] == 1) {
						$dados["origem"] .= "<br>[<a href=\"./?m=clientes&a=ver_dc&i={$dados["cliente_id"]}\" style=\"text-decoration:underline\" class=\"linkAzulEscuro11\">{$dados["cliente_id"]}</a>] ".$this->buscaCliente($dados["cliente_id"]);
					} else {
						$dados["destino"] .= "<br>[<a href=\"./?m=clientes&a=ver_dc&i={$dados["cliente_id"]}\" style=\"text-decoration:underline\" class=\"linkAzulEscuro11\">{$dados["cliente_id"]}</a>] ".$this->buscaCliente($dados["cliente_id"]);
					}
				}
			}
			
			if ($dados["arquivo_gravacao"]) {
				if ($this->permissoes["central_telefonica"]["ouvir"] ||
				    ($this->permissoes["central_telefonica"]["ouvir_comercial"] && ($dados["setor_origem_id"] == 1 || $dados["setor_destino_id"] == 1)) ||
				    ($this->permissoes["central_telefonica"]["ouvir_financeiro"] && ($dados["setor_origem_id"] == 2 || $dados["setor_destino_id"] == 2)) ||
				    ($this->permissoes["central_telefonica"]["ouvir_suporte"] && ($dados["setor_origem_id"] == 3 || $dados["setor_destino_id"] == 3)) ||
				    ($this->permissoes["central_telefonica"]["ouvir_ouvidoria"] && ($dados["setor_origem_id"] == 4 || $dados["setor_destino_id"] == 4)) ||
				    ($this->permissoes["central_telefonica"]["ouvir_administrativo"] && ($dados["setor_origem_id"] == 5 || $dados["setor_destino_id"] == 5))) {
				  $permissao_ouvir = true;
				  $dados["botao"] = "<img src=\"imagens/sound.png\" width=\"16\" height=\"16\" onclick=\"ouvirArquivo('http://192.168.170.1/monitor/{$dados["arquivo_gravacao"]}.WAV')\" title=\"Ouvir\" style=\"cursor:pointer\"> ";
					if ($this->permissoes["central_telefonica"]["salvar"]) {
				  	$dados["botao"] .= "<img src=\"imagens/download.png\" width=\"16\" height=\"16\" onclick=\"window.location = '?m=central_telefonica&a=salvar&i={$dados["ligacao_id"]}'\" title=\"Salvar\" style=\"cursor:pointer\"> ";
					}
				}
			}
			$registros[] = $dados;
		}
		if ($registros) {
			if ($post["opcoes"] && in_array("exportar_csv", $post["opcoes"])) {
				$campos = array("data" => "Data", "hora" => "Hora", "fila" => "Fila", "origem" => "Origem", "origem_pessoa" => "Origem » Pessoa", "destino" => "Destino", "destino_pessoa" => "Destino » Pessoa", "duracao" => "Duração", "situacao" => "Situação");
				header("Content-type: application/csv;");
		    header("Content-Disposition: attachment; filename=central_telefonica_analitico.csv;");
		    die($this->exportarCSV($campos, $registros));
			} else {
				if ($permissao_ouvir) {
					$campos = array("count" => "#", "data" => "Data", "hora" => "Hora", "fila" => "Fila", "origem" => "Origem", "destino" => "Destino", "duracao" => "Duração", "situacao" => "Situação", "botao" => "&nbsp;");
				} else {
					$campos = array("count" => "#", "data" => "Data", "hora" => "Hora", "fila" => "Fila", "origem" => "Origem", "destino" => "Destino", "duracao" => "Duração", "situacao" => "Situação");
				}
				$listagem = controle::instanciaObjeto("listagem", $campos, $registros, 0, false, array("20", "70", "", "", "", "", "", "40"), null, array("right"));
				$resultado = $listagem->conteudo;
			}
		} else {
			$resultado = "Nenhum registro encontrado";
		}
		return $resultado;
  }
  
  function buscaUsuario($usuario_id, $abreviar = true) {
		$query = "
		  SELECT
		    usu_nome AS nome
		  FROM
		  	tab_usuarios
		  WHERE
		    usu_id = $usuario_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	  	if ($abreviar && strlen($dados["nome"]) > 15) {
	  		$dados["nome"] = trim(substr($dados["nome"], 0, 15))."...";
	  	}
	  	return $dados["nome"];
	  }
  }
  
  function buscaCliente($cliente_id, $abreviar = true) {
		$query = "
		  SELECT
		    tab_clientes.cli_nome AS nome
		  FROM
		    tab_clientes
		  WHERE
		    cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	  	if ($abreviar && strlen($dados["nome"]) > 15) {
	  		$dados["nome"] = trim(substr($dados["nome"], 0, 15))."...";
	  	}
	  	return $dados["nome"];
	  }
  }
  
  function buscaClientePorTelefone($telefone) {
		$ddd = substr($telefone, 1, 2);
		$numero = substr($telefone, 5, 9);
		$query = "
		  SELECT
		    tab_clientes.cli_id AS id,
		    tab_clientes.cli_nome AS nome
		  FROM
		  	tab_clientes_contatos_telefones
		    INNER JOIN tab_clientes ON (tab_clientes_contatos_telefones.cli_id = tab_clientes.cli_id)
		  WHERE
		    ccl_ddd = '$ddd' AND
		    ccl_numero = '$numero'
		  ORDER BY
		    IF(tab_clientes.cls_id = 2, 1, IF(tab_clientes.cls_id = 1, 2, tab_clientes.cls_id)),
			tab_clientes_contatos_telefones.ccf_id DESC
			LIMIT 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	  	if (strlen($dados["nome"]) > 15) {
	  		$dados["nome"] = trim(substr($dados["nome"], 0, 15))."...";
	  	}
	  	return $dados;
	  }
  }
  
  function formataTelefone($numero) {
  	if (substr($numero, 0, 3) == "SIP") {
  		$formatado = "Ramal ".substr($numero, 4);
  		$numero = substr($numero, 4);
  	} elseif (substr($numero, 0, 5) == "Agent") {
  		$formatado = "Agente ".substr($numero, 6);
  		$numero = "a".substr($numero, 6);
  	} elseif (substr($numero, 0, 4) == "0800") {
			$formatado = substr($numero, 0, 4)." ".substr($numero, 4, 3)."-".substr($numero, 7, 4);
			$numero = "0".$numero;
		} elseif (strlen($numero) == 8) {
			$formatado = substr($numero, 0, 4)."-".substr($numero, 4, 4);
			$numero = "0".$numero;
		} elseif (strlen($numero) == 10) {
			$formatado = "(".substr($numero, 0, 2).") ".substr($numero, 2, 4)."-".substr($numero, 6, 4);
			$numero = substr($numero, 0, 2) == 48 ? "0".substr($numero, 2) : "00".$numero;
		} else {
			return $numero;
		}
  	return "<a href=\"javascript:discar('$numero')\" title=\"Discar para este número\" class=\"linkDiscar\">$formatado</a>";
  }
  
  function ehLigacaoRecuperada($src, $calldate) {
	  $query = "
	    SELECT
	      COUNT(*) AS ligacoes
	  	FROM
	  	  cdr
	  	WHERE
	  	  src = '$src' and
	  	  calldate > '$calldate%' AND
	  	  calldate <= '".substr($calldate, 0, 10)." 23:59:59"."' AND
	  	  disposition = 'ANSWERED' AND
	  	  dstchannel != ''
	  	ORDER BY
	  	  calldate";
   	$query = $this->bancodedados_asterisk->query($query);
   	$dados = $this->bancodedados_asterisk->resultadoArray($query);
    if ($dados) {
   		return $dados["ligacoes"];
    }
  }
  
  function formataDuracao($duracao) {
  	$segundos = $duracao % 60;
  	$duracao = ($duracao - $segundos) / 60;
  	$minutos = $duracao % 60;
  	$horas = ($duracao - $minutos) / 60;
  	return sprintf("%02s:%02s:%02s", $horas, $minutos, $segundos);
  }
  
  ###########################
  ### Relatório Analítico ###
  ###########################
  
  function relatorio_analitico() {
    if ($_POST) {
      $id = md5(uniqid(rand(), true));
      $_SESSION["POST"][$id] = $_POST;
      header("Location: ./?m=central_telefonica&a=relatorio_analitico&i=$id");
    } else {
		
			$this->agrupadores["ctl_data"] = "Data";
			$this->agrupadores["ctl_dia_semana"] = "Dia da Semana";
			$this->agrupadores["ctl_mes"] = "Mês";
			$this->agrupadores["ctl_setor"] = "Setor";
			$this->agrupadores["ctl_hora"] = "Hora";
			$this->agrupadores["ctl_operador"] = "Operador";
			$this->agrupadores["usu_nome"] = "Usuário";
			$this->agrupadores["ctl_tipo"] = "Tipo";
			
    	if ($_SESSION["POST"][$_GET["i"]]["agrupadores"]) {
    		$temp = array();
    		foreach ($_SESSION["POST"][$_GET["i"]]["agrupadores"] as $agrupador) {
	    		$temp[$agrupador] = $this->agrupadores[$agrupador];
    		}
    		$temp = array_merge($temp, $this->agrupadores);
    		$this->agrupadores = $temp;
    	}
			
			$this->campos["ctl_ligacoes_efetuadas_atendidas"] = "Ligações efetuadas atendidas";
			$this->campos["ctl_ligacoes_efetuadas_perdidas"] = "Ligações efetuadas perdidas";
			$this->campos["ctl_ligacoes_efetuadas_recuperadas"] = "Ligações efetuadas recuperadas";
			$this->campos["ctl_ligacoes_recebidas_atendidas"] = "Ligações recebidas atendidas";
			$this->campos["ctl_ligacoes_recebidas_perdidas"] = "Ligações recebidas perdidas";
			$this->campos["ctl_ligacoes_recebidas_recuperadas"] = "Ligações recebidas recuperadas";
			$this->campos["ctl_total_duracao_recebidas"] = "Total duração recebidas";
			$this->campos["ctl_total_duracao_efetuadas"] = "Total duração efetuadas";
			$this->campos["ctl_total_espera_recebidas"] = "Total espera recebidas";

			$this->calculos["ligacoes_efetuadas_nao_atendidas"] = "Ligações efetuadas não atendidas";
			$this->calculos["ligacoes_recebidas_nao_atendidas"] = "Ligações recebidas não atendidas";
			$this->calculos["total_chamadas_atendidas"] = "Total de chamadas atendidas";
			$this->calculos["tempo_total_em_chamada"] = "Tempo total em chamada";
			$this->calculos["media_duracao_recebidas"] = "Média duração recebidas";
			$this->calculos["media_duracao_efetuadas"] = "Média duração efetuadas";
			$this->calculos["media_espera_recebidas"] = "Média espera recebidas";
			
			$this->opcoes["destacar_agrupamento"] = "Destacar agrupamento";
			$this->opcoes["exportar_csv"] = "Exportar para CSV";
			
    	$this->conteudo .= $this->carregaArquivo("central_telefonica.relatorio_analitico.inc.php");
	    $this->conteudo = str_replace('[data_inicio]', $_GET["i"] ? $_SESSION["POST"][$_GET["i"]]["data_inicio"] : date("d/m/Y"), $this->conteudo);
	    $this->conteudo = str_replace('[data_fim]', $_GET["i"] ? $_SESSION["POST"][$_GET["i"]]["data_fim"] : date("d/m/Y"), $this->conteudo);
	    $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTiposAnalitico(), $_SESSION["POST"][$_GET["i"]]["tipo"], true, "tipo", "164px"), $this->conteudo);
	    $this->conteudo = str_replace('[setor]', $this->geraSelect($this->buscaSetores(true), $_SESSION["POST"][$_GET["i"]]["setor"], true, "setor", "164px"), $this->conteudo);
	    $this->conteudo = str_replace('[agrupadores]', $this->geraCamposAnalitico($this->agrupadores, $_SESSION["POST"][$_GET["i"]], "agrupadores", true), $this->conteudo);
	    $this->conteudo = str_replace('[campos]', $this->geraCamposAnalitico($this->campos, $_SESSION["POST"][$_GET["i"]], "campos")."\n".$this->geraCamposAnalitico($this->calculos, $_SESSION["POST"][$_GET["i"]], "calculos"), $this->conteudo);
	    $this->conteudo = str_replace('[opcoes]', $this->geraCamposAnalitico($this->opcoes, $_SESSION["POST"][$_GET["i"]], "opcoes"), $this->conteudo);
	    
	    $botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
	    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
	    $this->conteudo = str_replace('[resultado]', $this->geraResultadoAnalitico($_SESSION["POST"][$_GET["i"]]), $this->conteudo);
	    $this->conteudo = str_replace('[botoes]',  implode(" ", $botoes), $this->conteudo);
	    $this->conteudo = str_replace('[action]', '?m=central_telefonica&a=relatorio_analitico', $this->conteudo);
    }
  }
  
  function geraCamposAnalitico($campos, $valores, $tipo, $setas = false) {
  	foreach ($campos as $chave => $valor) {
  		$lista[] = "<tr><td align=\"left\" width=\"15\"><input type=\"checkbox\" name=\"{$tipo}[]\" style=\"margin:0px\" value=\"$chave\" id=\"$chave\"".($valores[$tipo] && in_array($chave, $valores[$tipo]) ? " checked" : "")."></td>".($setas ? "<td align=\"left\" width=\"15\"><img src=\"imagens/seta_cima.gif\" width=\"7\" height=\"8\" style=\"cursor:pointer\" class=\"subir-linha\"><img src=\"imagens/seta_baixo.gif\" width=\"7\" height=\"8\" style=\"cursor:pointer\" class=\"descer-linha\"></td>" : "")."<td class=\"txtAzulEscuro11\" align=\"left\"><label for=\"$chave\">$valor</label></td></tr>";
  	}
  	return "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\">".implode("							  \n", $lista)."</table>";
  }
  
  function geraResultadoAnalitico($post) {
  	if (!$post || !$post["campos"] || !$post["agrupadores"] ) {
  		return "";
  	}
		if ($post["data_inicio"]) {
			$post["data_inicio"] = substr($post["data_inicio"], 6, 4)."-".substr($post["data_inicio"], 3, 2)."-".substr($post["data_inicio"], 0, 2);
			$filtro[] = "ctl_data >= '{$post["data_inicio"]}'";
		}
		if ($post["data_fim"]) {
			$post["data_fim"] = substr($post["data_fim"], 6, 4)."-".substr($post["data_fim"], 3, 2)."-".substr($post["data_fim"], 0, 2);
			$filtro[] = "ctl_data <= '{$post["data_fim"]}'";
		}
		if ($post["tipo"]) {
			$filtro[] = "ctl_tipo = '{$post["tipo"]}'";
		}
		if ($post["setor"]) {
			$filtro[] = "ctl_setor = '{$post["setor"]}'";
		}
		if (in_array("usu_nome", $post["agrupadores"])) {
			$join[] = "LEFT OUTER JOIN tab_usuarios ON (tab_central_telefonica_agregados.usu_id = tab_usuarios.usu_id)";
		}
		
		$dias_semana = array("Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado");
		
		$lista_campos["ctl_data"] = "Data";
		$lista_campos["ctl_mes"] = "Mês";
		$lista_campos["ctl_dia_semana"] = "Dia Semana";
		$lista_campos["ctl_setor"] = "Setor";
		$lista_campos["ctl_hora"] = "Hora";
		$lista_campos["ctl_operador"] = "Operador";
		$lista_campos["usu_nome"] = "Usuário";
		$lista_campos["ctl_tipo"] = "Tipo";
		$lista_campos["ctl_ligacoes_efetuadas_atendidas"] = "L.E.A.";
		$lista_campos["ctl_ligacoes_efetuadas_perdidas"] = "L.E.P.";
		$lista_campos["ctl_ligacoes_efetuadas_recuperadas"] = "L.E.R.";
		$lista_campos["ctl_ligacoes_recebidas_atendidas"] = "L.R.A.";
		$lista_campos["ctl_ligacoes_recebidas_perdidas"] = "L.R.P.";
		$lista_campos["ctl_ligacoes_recebidas_recuperadas"] = "L.R.R.";
		$lista_campos["ctl_total_duracao_recebidas"] = "T.D.R.";
		$lista_campos["ctl_total_duracao_efetuadas"] = "T.D.E.";
		$lista_campos["ctl_total_espera_recebidas"] = "T.E.R.";
		$lista_campos["ligacoes_efetuadas_nao_atendidas"] = "L.E.N.";
		$lista_campos["ligacoes_recebidas_nao_atendidas"] = "L.R.N.";
		$lista_campos["total_chamadas_atendidas"] = "T.C.A.";
		$lista_campos["tempo_total_em_chamada"] = "T.T.C.";
		$lista_campos["media_duracao_recebidas"] = "M.D.R.";
		$lista_campos["media_duracao_efetuadas"] = "M.D.E.";
		$lista_campos["media_espera_recebidas"] = "M.E.R.";
			
		$usuarios = array();
		
		foreach ($lista_campos as $chave => $valor) {
			if (in_array($chave, $post["agrupadores"])) {
				$fields[] = $chave;
			} elseif (in_array($chave, $post["campos"])) {
				$fields[] = "SUM($chave) $chave";
			}
		}
		
		if ($post["calculos"] && in_array("ligacoes_efetuadas_nao_atendidas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_ligacoes_efetuadas_perdidas) + SUM(ctl_ligacoes_efetuadas_recuperadas)) ligacoes_efetuadas_nao_atendidas";
		}
		if ($post["calculos"] && in_array("ligacoes_recebidas_nao_atendidas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_ligacoes_recebidas_perdidas) + SUM(ctl_ligacoes_recebidas_recuperadas)) ligacoes_recebidas_nao_atendidas";
		}
		if ($post["calculos"] && in_array("total_chamadas_atendidas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_ligacoes_efetuadas_atendidas) + SUM(ctl_ligacoes_recebidas_atendidas)) total_chamadas_atendidas";
		}
		if ($post["calculos"] && in_array("tempo_total_em_chamada", $post["calculos"])) {
			$fields[] = "(SUM(ctl_total_duracao_recebidas) + SUM(ctl_total_duracao_efetuadas)) tempo_total_em_chamada";
		}
		if ($post["calculos"] && in_array("quantidade_total_de_chamadas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_ligacoes_efetuadas_atendidas) + SUM(ctl_ligacoes_recebidas_atendidas)) quantidade_total_de_chamadas";
		}
		if ($post["calculos"] && in_array("media_duracao_recebidas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_total_duracao_recebidas) DIV SUM(ctl_ligacoes_recebidas_atendidas)) media_duracao_recebidas";
		}
		if ($post["calculos"] && in_array("media_duracao_efetuadas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_total_duracao_efetuadas) DIV SUM(ctl_ligacoes_efetuadas_atendidas)) media_duracao_efetuadas";
		}
		if ($post["calculos"] && in_array("media_espera_recebidas", $post["calculos"])) {
			$fields[] = "(SUM(ctl_total_espera_recebidas) DIV SUM(ctl_ligacoes_recebidas_atendidas)) media_espera_recebidas";
		}
		
  	$query = "
   	  SELECT
   	    ".implode(",\n   	    ", $fields)."
   	  FROM
   	    tab_central_telefonica_agregados".($join ? "
   	    ".implode("\n   	    ", $join) : "")."
   	  WHERE
   	    ".implode(" AND\n   	    ", $filtro)."
   	  GROUP BY
  	    ".implode(",\n   	    ", array_merge($post["agrupadores"]))."
  	  ORDER BY
  	    ".implode(",\n   	    ", array_merge($post["agrupadores"]));
   	//die($query);
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if (array_key_exists("ctl_dia_semana", $dados)) {
				$dados["ctl_dia_semana"] = $dias_semana[$dados["ctl_dia_semana"]];
			}
			if (array_key_exists("ctl_data", $dados)) {
				$dados["ctl_data"] = date("d/m/Y", strtotime($dados["ctl_data"]));
			}
			if (array_key_exists("ctl_mes", $dados)) {
				$dados["ctl_mes"] = date("m/Y", strtotime($dados["ctl_mes"]));
			}
			if (array_key_exists("ctl_hora", $dados)) {
				$dados["ctl_hora"] = sprintf("%02s", $dados["ctl_hora"]);
			}
			if (array_key_exists("ctl_tipo", $dados)) {
				$dados["ctl_tipo"] = $dados["ctl_tipo"] == "i" ? "Interna" : "Externa";
			}
			foreach ($dados as $campo => $valor) {
				if (is_numeric($valor)) {
					if ($post["opcoes"] && in_array("destacar_agrupamento", $post["opcoes"])) {
						$total[$dados[$post["agrupadores"][0]]][$campo] += $valor;
					} else {
						$total[$campo] += $valor;
					}
				}
			}
			if (array_key_exists("ctl_total_duracao_recebidas", $dados) && $dados["ctl_total_duracao_recebidas"]) {
				$dados["ctl_total_duracao_recebidas"] = $this->formataDuracao($dados["ctl_total_duracao_recebidas"]);
			}
			if (array_key_exists("ctl_total_duracao_efetuadas", $dados) && $dados["ctl_total_duracao_efetuadas"]) {
				$dados["ctl_total_duracao_efetuadas"] = $this->formataDuracao($dados["ctl_total_duracao_efetuadas"]);
			}
			if (array_key_exists("ctl_total_espera_recebidas", $dados)) {
				$dados["ctl_total_espera_recebidas"] = $this->formataDuracao($dados["ctl_total_espera_recebidas"]);
			}
			if (array_key_exists("tempo_total_em_chamada", $dados)) {
				$dados["tempo_total_em_chamada"] = $this->formataDuracao($dados["tempo_total_em_chamada"]);
			}
			if (array_key_exists("media_duracao_recebidas", $dados)) {
				$dados["media_duracao_recebidas"] = $this->formataDuracao($dados["media_duracao_recebidas"]);
			}
			if (array_key_exists("media_duracao_efetuadas", $dados)) {
				$dados["media_duracao_efetuadas"] = $this->formataDuracao($dados["media_duracao_efetuadas"]);
			}
			if (array_key_exists("media_espera_recebidas", $dados)) {
				$dados["media_espera_recebidas"] = $this->formataDuracao($dados["media_espera_recebidas"]);
			}
			if (array_key_exists("ctl_operador", $dados)) {
				if (!$dados["ctl_operador"]) {
					$dados["ctl_operador"] = "-";
				} elseif (substr($dados["ctl_operador"], 0, 3) == "SIP") {
					$dados["ctl_operador"] = "Ramal ".substr($dados["ctl_operador"], 4);
				} else {
					$dados["ctl_operador"] = "Agente ".substr($dados["ctl_operador"], 6);
				}
			}
			if (array_key_exists("usu_nome", $dados) && !$dados["usu_nome"]) {
				$dados["usu_nome"] = "-";
			}
			if ($post["opcoes"] && in_array("destacar_agrupamento", $post["opcoes"])) {
				$registros[$dados[$post["agrupadores"][0]]][] = $dados;
			} else {
				$registros[] = $dados;
			}
		}
		if ($registros) {
			foreach ($total as $campo => $valor) {
				if (is_array($valor)) {
					foreach ($valor as $sub_campo => $sub_valor) {
						if (strpos($sub_campo, "duracao") !== false || strpos($sub_campo, "tempo") !== false || strpos($sub_campo, "espera") !== false) {
							$total[$campo][$sub_campo] = $this->formataDuracao($sub_valor);
						}
					}
					$total[$campo]["cor_linha"] = "#c9d4e0";
				} else {
					if (strpos($campo, "duracao") !== false || strpos($campo, "tempo") !== false || strpos($campo, "espera") !== false) {
						$total[$campo] = $this->formataDuracao($valor);
					}
				}
			}
			$titulos = array_merge($this->agrupadores, $this->campos, $this->calculos);
			if ($post["opcoes"] && in_array("destacar_agrupamento", $post["opcoes"])) {
				$grupo = $post["agrupadores"][0];
				unset($post["agrupadores"][0]);
				if (in_array("exportar_csv", $post["opcoes"])) {
			    header("Content-type: application/csv;");
			    header("Content-Disposition: attachment; filename=central_telefonica_analitico.csv;");
				}
				foreach ($registros as $chave => $sub_registros) {
					$campos = array();
					foreach ($post["agrupadores"] as $agrupador) {
							$campos[$chave][$agrupador] = "<spam title=\"".$titulos[$agrupador]."\">{$lista_campos[$agrupador]}</spam>";
							$alinhamentos[] = "left";
					}
					foreach ($post["campos"] as $campo) {
							$campos[$chave][$campo] = "<spam title=\"".$titulos[$campo]."\">{$lista_campos[$campo]}</spam>";
							$alinhamentos[] = "right";
					}
					if ($post["calculos"]) {
						foreach ($post["calculos"] as $campo) {
								$campos[$chave][$campo] = "<spam title=\"".$titulos[$campo]."\">{$lista_campos[$campo]}</spam>";
								$alinhamentos[] = "right";
						}
					}
					
					$total[$chave][$post["agrupadores"][1]] = "Total";
					$sub_registros[] = $total[$chave];
					
					if ($post["opcoes"] && in_array("exportar_csv", $post["opcoes"])) {
						print $this->exportarCSV($campos, $sub_registros);
					} else {
						$listagem = controle::instanciaObjeto("listagem", $campos, $sub_registros, count($post["agrupadores"]) - 1, null, null, null, $alinhamentos);
						$resultado .= $listagem->conteudo;
					}
				}
				if (in_array("exportar_csv", $post["opcoes"])) {
					die();
				}
			} else {
				foreach ($post["agrupadores"] as $agrupador) {
						$campos[$agrupador] = "<spam title=\"".$titulos[$agrupador]."\">{$lista_campos[$agrupador]}</spam>";
						$alinhamentos[] = "left";
				}
				foreach ($post["campos"] as $campo) {
						$campos[$campo] = "<spam title=\"".$titulos[$campo]."\">{$lista_campos[$campo]}</spam>";
						$alinhamentos[] = "right";
				}
				if ($post["calculos"]) {
					foreach ($post["calculos"] as $campo) {
							$campos[$campo] = "<spam title=\"".$titulos[$campo]."\">{$lista_campos[$campo]}</spam>";
							$alinhamentos[] = "right";
					}
				}
				$total["cor_linha"] = "#c9d4e0";
				$total[$post["agrupadores"][0]] = "Total";
				$registros[] = $total;
				if ($post["opcoes"] && in_array("exportar_csv", $post["opcoes"])) {
			    header("Content-type: application/csv;");
			    header("Content-Disposition: attachment; filename=central_telefonica_analitico.csv;");
					die($this->exportarCSV($campos, $registros));
				} else {
					$listagem = controle::instanciaObjeto("listagem", $campos, $registros, count($post["agrupadores"]) - 1, null, null, null, $alinhamentos);
					$resultado = $listagem->conteudo;
				}
			}
		} else {
			$resultado = "Nenhum registro encontrado";
		}
		return "
			<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
				<td height=\"30\" colspan=\"2\"><strong>&nbsp;Resultado:</strong></td>
			</tr>
			<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
				<td colspan=\"2\" align=\"center\">
					$resultado
				</td>
			</tr>";
  }
  
  function exportarCSV($campos, $linhas, $separador = ";") {
  	
    foreach ($campos as $chave => $descricao) {
      if (is_array($descricao)) {
      	$cabecalho1[] = $chave;
        foreach ($descricao as $sub_descricao) {
          $cabecalho1[] = "";
        	$cabecalho2[] = "\"".addslashes($sub_descricao)."\"";
        }
      } else {
        $cabecalho2[] = "\"".addslashes($descricao)."\"";
      }
    }
    if ($cabecalho1) {
   	  $arquivo_csv[] = @implode($separador, $cabecalho1);
    }
    $arquivo_csv[] = @implode($separador, $cabecalho2);
    do {
      $linha_atual = current($linhas);
      $linha_atual_tratada = array();
      foreach ($campos as $chave => $descricao) {
        if (is_array($descricao)) {
          foreach ($descricao as $descricao_chave => $descricao_nome) {
            $linha_atual_tratada[$descricao_chave] = "\"".addslashes($linha_atual[$descricao_chave])."\"";
          }
        } else {
          $linha_atual_tratada[$chave] = "\"".addslashes($linha_atual[$chave])."\"";
        }
      }
      $arquivo_csv[] = @implode($separador, $linha_atual_tratada);
    } while (next($linhas));
    
    return strip_tags(str_replace("<br>", " ", implode("\n", $arquivo_csv)."\n\n"));
  }
  
}

?>
