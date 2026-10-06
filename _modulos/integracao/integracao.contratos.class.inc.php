<?

class integracao_contratos {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "buscar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

  #############
  ### Geral ###
  #############

	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}

	function transicao($url) {
		die("<script type=\"text/javascript\">window.location = '$url';</script>");
	}

  ###################
  ### Confirmação ###
  ###################

  function confirmacao() {
    if ($_GET["i"]) {
  	  $dados_contrato = $this->buscaDadosContrato($_GET["i"]);
  	  if ($dados_contrato["status_id"] == 14) {
        $this->conteudo = "<p align=\"center\">Seu contrato foi definido como <b>Reprovado</b>.<br><br>Em breve entraremos em contato.</p>";
      } elseif ($dados_contrato["status_id"] >= 15 && $dados_contrato["status_id"] < 19) {
    	  $this->conteudo = "<p align=\"center\">Seu contrato foi definido como <b>Aprovado</b>.<br><br>Em breve entraremos em contato para agendar a instalação.</p>";
      } elseif ($dados_contrato["status_id"] >= 19) {
        $this->conteudo = "<p align=\"center\">Seu contrato foi definido como <b>Aprovado</b> e <b>Ativo</b>.</p>";
      } else {
  			$this->erro = 3;
      }
    }
  }

  ##############
  ### Buscar ###
  ##############

  function buscar() {
  	if (!$_POST) {
    	$this->conteudo = $this->carregaArquivo("integracao.contratos.buscar.inc.php");
    	$this->conteudo = str_replace('[origem]', $_GET["origem"], $this->conteudo);
    	$this->conteudo = str_replace('[origem_nome]', $this->buscaOrigemNome($_GET["origem"]), $this->conteudo);
    	$this->conteudo = str_replace('[telefone]', $this->buscaOrigemTelefone($_GET["origem"]), $this->conteudo);
    	$this->conteudo = str_replace('[contrato]', '', $this->conteudo);
    	$this->conteudo = str_replace('[cpf_cnpj]', '', $this->conteudo);
  	} else {
  		$contrato_id = $this->buscaContrato($_POST["contrato"], $_POST["cpf_cnpj"]);
  		if ($contrato_id) {
  		  $dados_contrato = $this->buscaDadosContrato($contrato_id);
        $acao = in_array($dados_contrato["servico_id"], array(1, 2, 3, 5, 28, 32, 33, 34, 36)) ? "vistoria" : "ver";
  			$this->transicao("?origem={$_GET["origem"]}&m=contratos&a=$acao&i=$contrato_id");
  		} else {
  			$this->erro = 11;
  		}
  	}
  }

  function buscaOrigemTelefone($origem_id) {
    $origens[1] = "3028-0000";
    $origens[2] = "3028-0000";
    $origens[3] = "3028-0000";
    $origens[4] = "3028-0000";
    return $origens[$origem_id];
  }

  function buscaOrigemNome($origem_id) {
    $origens[1] = "Wave System Internet";
    $origens[2] = "CSM Telecom";
    $origens[3] = "SodisaNET";
    $origens[4] = "Olé Telecom";
    return $origens[$origem_id];
  }

  function buscaCobrancasAtrasadas($cliente_id, $quantidade_maxima = 0) {
    return false;
    $query = "
      SELECT
        count(coa_id) AS quantidade
      FROM
        tab_cobrancas
      WHERE
        cli_id = $cliente_id AND
        cot_id = 1 AND
        coa_data_vencimento < CURDATE()";
    $query = $this->bancodedados->query($query);
    $cobrancas = $this->bancodedados->resultadoArray($query);
    if ($cobrancas) {
      return ($cobrancas["quantidade"] > $quantidade_maxima) ? $cobrancas["quantidade"] : false;
    } else {
      return false;
    }
  }
  
  function ehVisualizavelVistoria($contrato_id) {
    $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, "", false);
    $contratos->buscaDadosItem($contrato_id);
  	return $contratos->ehVisualizavelVistoria();
  }
  
  function geraContrato($contrato_id, $tipo = "contrato") {
    $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, "", false);
    $conteudo = $contratos->geraConteudo($contratos->buscaDadosContrato($contrato_id), $tipo, true);
    return $conteudo;
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
  	$dados_contrato = $this->buscaDadosContrato($_GET["i"]);
  	if ($dados_contrato) {
  		if (!$_POST) {
  		  $contrato_conteudo = $this->geraContrato($_GET["i"]);
	    	$this->conteudo = $this->carregaArquivo("integracao.contratos.ver.inc.php");
	    	$this->conteudo = str_replace('[contrato_conteudo]', $contrato_conteudo, $this->conteudo);
	    	// Action
	    	$this->conteudo = str_replace('[action]', "?m=contratos&a=ver&i={$_GET["i"]}", $this->conteudo);
				$botoes[] = "<br><input type=\"button\" name=\"imprimir\" class=\"formButton\" value=\"Imprimir\" style=\"width:85px\" onclick=\"window.location = './?m=contratos&a=imprimir&i={$_GET["i"]}'\">";
				if ($this->ehVisualizavelVistoria($_GET["i"])) {
					$botoes[] = "<input type=\"button\" name=\"vistoria\" class=\"formButton\" value=\"Vistoria\" style=\"width:85px\" onclick=\"window.location = './?m=contratos&a=vistoria&i={$_GET["i"]}'\">";
				}
  			if (in_array($dados_contrato["status_id"], array(13))) {
		    	$this->conteudo = str_replace('[exibir_comentarios]', "block", $this->conteudo);
  				$botoes[] = "<input type=\"submit\" name=\"enviar\" class=\"formButton\" value=\"Aprovar\" style=\"width:85px\">";
  				$botoes[] = "<input type=\"submit\" name=\"enviar\" class=\"formButton\" value=\"Reprovar\" style=\"width:85px\" onclick=\"if(!this.form.comentarios.value) { alert('Por favor, informe o motivo da\\nreprovação do contrato'); return false; }\">";
  				$botoes[] = "<br><input type=\"button\" name=\"sair\" class=\"formButton\" value=\"Sair\" style=\"width:85px\" onclick=\"window.location = './'\">";
		    	$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
    			// registra comentário
  	      $this->registraComentario($_GET["i"], 7, "<b>IP</b>: {$_GET["ip_cliente"]}");
  			} else {
		    	$this->conteudo = str_replace('[exibir_comentarios]', "none", $this->conteudo);
  				$botoes[] = "<input type=\"button\" name=\"sair\" class=\"formButton\" value=\"Sair\" style=\"width:85px\" onclick=\"window.location = './'\">";
		    	$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
		    	if (!$this->possuiComentarioVisualizacao($_GET["i"])) {
    	      $this->registraComentario($_GET["i"], 7, "<b>IP</b>: {$_GET["ip_cliente"]}");
		    	}
  			}
  		} else {
  			if (in_array($dados_contrato["status_id"], array(13))) {
	  			if ($_POST["enviar"] == "Reprovar") {
	  				$this->reprovar();
	  			} elseif ($_POST["enviar"] == "Aprovar") {
	  				$this->aprovar();
	  			}
	  			if ($dados_contrato["parametros_tipo"]["impressao_obrigatoria"]) {
					 $this->transicao("?origem={$_GET["origem"]}&m=contratos&a=imprimir&i={$_GET["i"]}");
	  			} else {
					 $this->transicao("?origem={$_GET["origem"]}&m=contratos&a=confirmacao&i={$_GET["i"]}");
	  			}
  			} else {
					$this->erro = 12;
  			}
  		}
  	} else {
  		$this->erro = 11;
  	}
  }

  ################
  ### Vistoria ###
  ################
  
  function vistoria() {
  	$dados_contrato = $this->buscaDadosContrato($_GET["i"]);
  	if ($dados_contrato) {
  		if (!$_POST) {
  		  $contrato_conteudo = $this->geraContrato($_GET["i"], "vistoria");
	    	$this->conteudo = $this->carregaArquivo("integracao.contratos.ver.inc.php");
	    	$this->conteudo = str_replace('[contrato_conteudo]', $contrato_conteudo, $this->conteudo);
	    	// Action
	    	$this->conteudo = str_replace('[action]', "?m=contratos&a=ver&i={$_GET["i"]}", $this->conteudo);
				$botoes[] = "<br><input type=\"button\" name=\"imprimir\" class=\"formButton\" value=\"Imprimir\" style=\"width:85px\" onclick=\"window.location = './?m=contratos&a=imprimir_vistoria&i={$_GET["i"]}'\">";
				$botoes[] = "<input type=\"button\" name=\"contrato\" class=\"formButton\" value=\"Contrato\" style=\"width:85px\" onclick=\"window.location = './?m=contratos&a=ver&i={$_GET["i"]}'\">";
	    	$this->conteudo = str_replace('[exibir_comentarios]', "none", $this->conteudo);
				$botoes[] = "<input type=\"button\" name=\"sair\" class=\"formButton\" value=\"Sair\" style=\"width:85px\" onclick=\"window.location = './'\">";
	    	$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
  		} else {
  			if (in_array($dados_contrato["status_id"], array(13))) {
	  			if ($_POST["enviar"] == "Reprovar") {
	  				$this->reprovar();
	  			} elseif ($_POST["enviar"] == "Aprovar") {
	  				$this->aprovar();
	  			}
	  			if ($dados_contrato["parametros_tipo"]["impressao_obrigatoria"]) {
					 $this->transicao("?origem={$_GET["origem"]}&m=contratos&a=imprimir&i={$_GET["i"]}");
	  			} else {
					 $this->transicao("?origem={$_GET["origem"]}&m=contratos&a=confirmacao&i={$_GET["i"]}");
	  			}
  			} else {
					$this->erro = 12;
  			}
  		}
  	} else {
  		$this->erro = 11;
  	}
  }

  ################
  ### Imprimir ###
  ################

  function imprimir() {
  	$this->conteudo = $this->geraContrato($_GET["i"]);
  }


  ################
  ### Imprimir ###
  ################

  function imprimir_vistoria() {
  	$this->conteudo = $this->geraContrato($_GET["i"], "vistoria");
  }

  ###############
  ### Aprovar ###
  ###############

  function aprovar() {
    // Busca Dados Contrato
    $contrato = $this->buscaDadosContrato($_GET["i"]);
    if (!$this->ehAutoAprovavelTecnico($contrato["parametros_tipo"], $contrato["parametros_servico"], $contrato["parametros"], $contrato["tipo_id"])) {
      $status_id = 15;
    } else {
      $status_id = 17;
    }
    // Migra Endereço Cliente
    if ($contrato["tipo_id"] == 14) {
      $this->migraEnderecoCliente($_GET["i"]);
    }
    // Atualiza Status Contrato
  	$this->atualizaStatusContrato($contrato["id"], $status_id);
  	// Registra Comentários Contrato
  	$_POST["comentarios"] = ($_POST["comentarios"]) ? "{$_POST["comentarios"]}<br><b>IP</b>: {$_GET["ip_cliente"]}" : "<b>IP</b>: {$_GET["ip_cliente"]}";
  	$this->registraComentario($contrato["id"], 8, $_POST["comentarios"]);
  	// Gera Visita
  	if ($contrato["parametros_servico"]["gera_visita"] && !$this->ehAutoAprovavelTecnico($contrato["parametros_tipo"], $contrato["parametros_servico"], $contrato["parametros"], $contrato["tipo_id"])) {
      $visita = $this->geraVisita($contrato["cli_id"], $contrato["id"]);
      // Registra Comentário Visita
      $this->registraComentarioVisita($visita, "<u>Adicionada</u>: Contrato aprovado pelo cliente ({$contrato["codigo"]})", 16);
  	} else {
    	if ($contrato["parametros_tipo"]["auto_ativavel"] && !$contrato["parametros_tipo"]["impressao_obrigatoria"] && !$this->buscaCobrancasAtrasadas($contrato["cli_id"])) {
    	  // Ativa Contrato
    	  $this->ativar_contrato($contrato["id"]);
    	} else {
    	  // Gera retorno ao responsável
    	  $comentarios = "Contrato {$contrato["codigo"]} aprovado pelo cliente<br><b>Comentarios</b>: {$_POST["comentarios"]}";
        $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ('{$contrato["cli_id"]}', '{$contrato["responsavel"]}', NOW(), CURDATE(), '$comentarios', 2, 1)";
        $this->bancodedados->query($query);
    	}
  	}
  }

  function ehAutoAprovavelTecnico($tipo_parametros, $servico_parametros, $contrato_parametros, $contrato_tipo_id) {
    if ($tipo_parametros["auto_aprovacao_tecnico"] || ($tipo_parametros["auto_aprovacao_tecnico"] === null && $servico_parametros["auto_aprovacao_tecnico"])) return true;
    if ($contrato_tipo_id != 14 && !$contrato_parametros["contrato_servico_pontos_quantidade"]) return true;
    return false;
  }

  function ativar_contrato($contrato_id) {
    $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, "", false);
    $contratos->buscaDadosItem($contrato_id);
    if ($contratos->dados_item["tipo_id"] == 11) {
      $contratos->ativar_contrato_principal($contratos->dados_item, date('Y-m-d'), 20);
    } elseif ($contratos->dados_item["tipo_id"] == 12) {
      $contratos->ativar_contrato_aditivo_alteracao($contratos->dados_item, date('Y-m-d'), 20);
    } elseif ($contratos->dados_item["tipo_id"] == 13) {
      $contratos->ativar_contrato_aditivo_migracao($contratos->dados_item, date('Y-m-d'), 20);
    } elseif ($contratos->dados_item["tipo_id"] == 14) {
      $contratos->ativar_contrato_aditivo_me(date('Y-m-d'), 20);
    } elseif ($contratos->dados_item["tipo_id"] == 15) {
      $contratos->ativar_contrato_aditivo_mt(date('Y-m-d'), 20);
    }
  }

  function migraEnderecoCliente($contrato_id) {
    $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, "", false);
    $contratos->migraEnderecoCliente($contrato_id);
  }

  function geraVisita($cliente_id, $contrato_id) {
    $query = "SELECT vis_id AS id FROM tab_visitas WHERE cli_id = $cliente_id AND cor_id = $contrato_id AND via_id = 1";
    $query = $this->bancodedados->query($query);
    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      return $dados["id"];
    } else {
      $query = "INSERT INTO tab_visitas (cli_id, cor_id, via_id, vit_id, vis_data_geracao) VALUES ($cliente_id, $contrato_id, 1, 1, CURDATE())";
      $this->bancodedados->query($query);
      return $this->bancodedados->ultimoIdInserido();
    }
  }

  function registraComentarioVisita($visita_id, $conteudo, $categoria_id = null, $responsavel_id = null) {
    $categoria_id = $categoria_id ? $categoria_id : "NULL";
    $responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
    $query = "INSERT INTO tab_visitas_comentarios (vic_datahora, vis_id, vic_conteudo, vcc_id, usu_id) VALUES (NOW(), $visita_id, '$conteudo', $categoria_id, $responsavel_id)";
    $this->bancodedados->query($query);
  }

  ################
  ### Reprovar ###
  ################

  function reprovar() {
    // Busca Dados Contrato
    $contrato = $this->buscaDadosContrato($_GET["i"]);
    // Atualiza Status Contrato
  	$this->atualizaStatusContrato($contrato["id"], 14);
  	// Registra Comentários Contrato
  	if ($_POST["comentarios"]) $_POST["comentarios"] = "<b>Comentário do Cliente:</b> {$_POST["comentarios"]}<br>";
  	$this->registraComentario($_GET["i"], 9, "{$_POST["comentarios"]}<b>IP</b>: {$_GET["ip_cliente"]}");
    // Define contrato como renegociação
    $query = "UPDATE tab_contratos SET cor_renegociacao = 1 WHERE tab_contratos.cor_id = {$contrato["id"]}";
    $this->bancodedados->query($query);
    // Manda situação do cliente para renegociação
    $query = "UPDATE tab_clientes SET clu_id = 4 WHERE cli_id = {$contrato["cli_id"]}";
    $this->bancodedados->query($query);
    // Finaliza Visita, se houver
    $query = "UPDATE tab_visitas SET via_id = 3 WHERE cli_id = {$contrato["cli_id"]} AND via_id NOT (1, 2)";
    $this->bancodedados->query($query);
    // Registra Retorno para o cliente
    $comentarios = "Enviado para <b>Re-negociação</b> (Gerado em {$contrato["data_geracao"]})";
    if (trim($_POST["comentarios"])) $comentarios .= "\n » <b>Comentário do cliente</b>: ".preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentarios"]));
    $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ({$contrato["cli_id"]}, {$contrato["responsavel"]}, NOW(), CURDATE(), '$comentarios', 2, 1)";
    $this->bancodedados->query($query);
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaContrato($codigo, $cpf_cnpj) {
    $query = "
      SELECT
        tab_contratos.cor_id AS id
      FROM
        tab_contratos
        INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
      WHERE
	      tab_clientes.cli_cpf_cnpj = '$cpf_cnpj' AND
	      tab_contratos.cor_codigo = '$codigo' AND
	      tab_contratos.cor_sincronizado = 1 AND
	      tab_contratos.crs_id >= 13";
	  $query = $this->bancodedados->query($query);
	  $contrato = $this->bancodedados->resultadoArray($query);
	  if ($contrato) {
	  	return $contrato["id"];
	  }
  }

  function buscaDadosContrato($contrato_id) {
    $query = "
      SELECT
        tab_contratos.cor_id AS id,
        DATE_FORMAT(tab_contratos.cor_data_geracao, '%d/%m/%Y') AS data_geracao,
        tab_contratos.cli_id AS cli_id,
        tab_contratos.cor_codigo AS codigo,
        tab_contratos.crs_id AS status_id,
        tab_contratos.crt_id AS tipo_id,
        tab_contratos.cor_parametros AS parametros,
        tab_contratos_tipos.crt_parametros AS parametros_tipo,
        tab_planos_servicos.pls_parametros AS parametros_servico,
        MIN(comentarios_gerado.usu_id) AS responsavel
      FROM
        tab_contratos
        INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_contratos_tipos ON (tab_contratos_tipos.crt_id = tab_contratos.crt_id)
        LEFT OUTER JOIN tab_contratos_comentarios comentarios_gerado ON (comentarios_gerado.cor_id = tab_contratos.cor_id AND comentarios_gerado.ccc_id = 1)
        INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
      WHERE
	      tab_contratos.cor_id = $contrato_id AND
	      tab_contratos.cor_sincronizado = 1
	    GROUP BY
	      id";
	  $query = $this->bancodedados->query($query);
	  $dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	  	$dados["parametros"] = unserialize($dados["parametros"]);
	  	$dados["parametros_tipo"] = unserialize($dados["parametros_tipo"]);
	  	$dados["parametros_servico"] = unserialize($dados["parametros_servico"]);
	  	return $dados;
	  }
  }
  
  function possuiComentarioVisualizacao($contrato_id) {
    $query = "
      SELECT
        COUNT(coo_id) AS comentarios
      FROM
        tab_contratos_comentarios
      WHERE
        cor_id = $contrato_id AND
        ccc_id = 7";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query);
  }

  function atualizaStatusContrato($contrato_id, $status_id) {
    $query = "UPDATE tab_contratos SET crs_id = $status_id WHERE cor_id = $contrato_id";
    $this->bancodedados->query($query);
  }

  function registraComentario($contrato_id, $categoria_id, $conteudo = '') {
    $query = "
      INSERT INTO
        tab_contratos_comentarios (coo_datahora, coo_conteudo, cor_id, ccc_id, usu_id)
      VALUES
        (NOW(), '$conteudo', $contrato_id, $categoria_id, NULL)";
    $this->bancodedados->query($query);
  }

}

?>
