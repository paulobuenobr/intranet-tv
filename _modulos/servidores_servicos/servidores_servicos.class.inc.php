<?

require_once("_global/modulo.class.inc.php");

class servidores_servicos extends modulo {

    function __construct ($bancodedados, $permissoes, $executar = true) {
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

  ########################
  ### Busca Dados Item ###
  ########################

  function buscaDadosItem($id) {
    $query = "
			SELECT
				tab_servidores_servicos.sei_id AS sei_id,
				tab_servidores_servicos.sss_id AS status_id,
				tab_servidores_servicos.ses_parametros AS parametros,
				tab_servidores_servicos_tipos.sst_id AS tipo_id,
				tab_servidores_servicos_tipos.sst_nome AS nome,
				tab_servidores_servicos_tipos.sst_servico AS servico,
				tab_servidores_servicos_tipos.sst_exportar AS exportavel,
				tab_servidores_servicos_status.sss_nome AS status_nome,
				tab_servidores_interfaces.ser_id as servidor_id,
	  		tab_servidores_situacoes.seu_id AS servidor_situacao_id,
	  		tab_servidores_situacoes.seu_nome AS servidor_situacao_nome
			FROM
				tab_servidores_servicos
				INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos_tipos.sst_id = tab_servidores_servicos.sst_id)
				INNER JOIN tab_servidores_servicos_status ON (tab_servidores_servicos_status.sss_id = tab_servidores_servicos.sss_id)
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
        INNER JOIN tab_servidores ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
	  		INNER JOIN tab_servidores_situacoes ON (tab_servidores_situacoes.seu_id = tab_servidores.seu_id)
			WHERE
				tab_servidores_servicos.ses_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      if ($dados["servidor_situacao_id"] == 1) {
        $dados["servidor_situacao_nome"] = "<font color=\"green\"><b>{$dados["servidor_situacao_nome"]}</b></font>";
      } else {
        $dados["servidor_situacao_nome"] = "<font color=\"red\"><b>{$dados["servidor_situacao_nome"]}</b></font>";
      }
      $this->dados_item = $dados;
    } else {
      unset($this->dados_item);
    }
  }

  ##############
  ### Fechar ###
  ##############

  function fechar() {
    unset($this->conteudo);
    if ($_GET["sei_id"]) {
      $this->conteudo .= "
				<script type=\"text/javascript\">
					if (!window.opener.closed && window.opener.buscaServidoresServicos)
		    		window.opener.buscaServidoresServicos({$_GET["sei_id"]});
	    	</script>";
    }
    $this->conteudo .= "<script type=\"text/javascript\">window.close()</script>";
  }

  #############
  ### Geral ###
  #############

  function buscaListaServicos($interface_id) {
    $query = "
			SELECT DISTINCT
			  tab_servidores_servicos_tipos.sst_nome AS nome,
			  tab_servidores_servicos_tipos.sst_id AS id
			FROM
			  tab_servidores_servicos_tipos
			  INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos_tipos.sit_id = IF(tab_servidores_interfaces.sit_id = 6, 1, tab_servidores_interfaces.sit_id))
			  LEFT OUTER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id AND tab_servidores_servicos_tipos.sst_id = tab_servidores_servicos.sst_id)
			WHERE
			  tab_servidores_interfaces.sei_id = $interface_id AND
				tab_servidores_servicos.sst_id IS NULL AND
				tab_servidores_servicos_tipos.sst_disponivel = 1
			ORDER BY
				nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servicos[$dados["id"]] = $dados["nome"];
    }
    return $servicos;
  }

  function buscaDadosInterface($sei_id) {
    $query = "
			SELECT
    		tab_servidores_interfaces.sei_nome AS nome,
    		tab_servidores_interfaces.sei_endereco_ip AS endereco_ip,
	  		tab_servidores_interfaces.sei_prefixo_ip AS prefixo_ip,
	  		tab_servidores_interfaces.sit_id AS tipo_id,
 	  		tab_servidores.ser_id AS servidor_id,
 	  		tab_servidores.ser_nome AS servidor_nome
    	FROM
	  		tab_servidores
	    	INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
    	WHERE
    		tab_servidores_interfaces.sei_id = $sei_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaParametrosDefault() {
    // DHCP
    $parametros_default[1]["publicar"] = "./wave_dhcp c [tabela] 1";
    $parametros_default[1]["reiniciar"] = "./wave_dhcp r";
    $parametros_default[1]["publicar_reiniciar"] = "./wave_dhcp w [tabela] 1";
    // ARP
    $parametros_default[2]["publicar"] = "./wave_arp c [tabela] 1";
    $parametros_default[2]["reiniciar"] = "./wave_arp r";
    $parametros_default[2]["publicar_reiniciar"] = "./wave_arp w [tabela] 1";
    // HTB
    $parametros_default[3]["instalar"] = "./testebw instala [down] [up] [qt_150] [qt_256] [qt_300] [ip_150] [ip_300] [diretorio]";
    return $parametros_default;
  }

  function buscaInterfacesSemServico($servidor_id, $servico_id) {
    $query = "
      SELECT
        tab_servidores_interfaces.sei_id AS id
      FROM
        tab_servidores_interfaces
        LEFT OUTER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_servidores_interfaces.sit_id IN (1, 6)
      GROUP BY
        tab_servidores_interfaces.sei_id
      HAVING
        SUM(IF(tab_servidores_servicos.sst_id = $servico_id, 1, 0)) = 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $interfaces[] = $dados["id"];
    }
    return $interfaces;
  }

  function regerarIPs($servidor_id) {
    $servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
    $servidores->regerarIPs($servidor_id);
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
  	$dados_interface = $this->buscaDadosInterface($_GET["i"]);
    if ($dados_interface) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores_servicos.adicionar.inc.php");
        // Parametros Default
        $parametros_default = $this->buscaParametrosDefault();
        foreach ($parametros_default as $key => $lista_parametros) {
          foreach ($lista_parametros as $parametro => $valor) {
            $this->conteudo = str_replace("[parametro_{$key}_{$parametro}]", $valor, $this->conteudo);
          }
        }
        // Dados
        $this->conteudo = str_replace('[sei_id]', $_GET["i"], $this->conteudo);
        $this->conteudo = str_replace('[servidor_nome]', "{$dados_interface["servidor_nome"]} ({$dados_interface["nome"]}".($dados_interface["endereco_ip"] ? ", {$dados_interface["prefixo_ip"]}.{$dados_interface["endereco_ip"]}" : "").")", $this->conteudo);
        $this->conteudo = str_replace('[servicos]', $this->geraSelect($this->buscaListaServicos($_GET["i"]), null, true, "servico", "100%", "mudaServico(this.value)"), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $lista_parametros = $_POST["parametros_{$_POST["servico"]}"];
        foreach ($lista_parametros as $parametro => $valor) {
          $parametros[$parametro] = $valor;
        }
        $parametros = $parametros ? serialize($parametros) : "";
        $query = "INSERT INTO tab_servidores_servicos (sei_id, ses_parametros, sst_id, sss_id) VALUES ({$_GET["i"]}, '$parametros', {$_POST["servico"]}, 2)";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Insere serviço nas demais interfaces
        if ($_POST["todos"]) {
        	$interfaces = $this->buscaInterfacesSemServico($dados_interface["servidor_id"], $_POST["servico"]);
          if ($interfaces) {
            foreach ($interfaces as $interface_id) {
              $query = "INSERT INTO tab_servidores_servicos (sei_id, ses_parametros, sst_id, sss_id) VALUES ($interface_id, '$parametros', {$_POST["servico"]}, 2)";
              $this->bancodedados->query($query);
            }
          }
        }
        // Re-gera IP's
        $this->regerarIPs($dados_interface["servidor_id"]);
        // Registra Atividade
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
    if ($this->dados_item) {
      $dados_interface = $this->buscaDadosInterface($this->dados_item["sei_id"]);
      $this->conteudo .= $this->carregaArquivo("servidores_servicos.editar.inc.php");
      // Dados
      $this->conteudo = str_replace('[sei_id]', $this->dados_item["sei_id"], $this->conteudo);
      $this->conteudo = str_replace('[servidor_nome]', "{$dados_interface["servidor_nome"]} ({$dados_interface["nome"]}".($dados_interface["endereco_ip"] ? ", {$dados_interface["prefixo_ip"]}.{$dados_interface["endereco_ip"]}" : "").")", $this->conteudo);
      $this->conteudo = str_replace('[servicos]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["desativar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["exportar"] && $this->dados_item["exportavel"]) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" value="Exportar" style="width:60" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"]) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["reiniciar"] && $this->dados_item["tipo_id"] <= 2) $botoes[] = '<input type="button" name="reiniciar" class="buttonAzulEscuroBranco11" value="Reiniciar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reiniciar&i='.$_GET["i"].'\';">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
      $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
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
        $dados_interface = $this->buscaDadosInterface($this->dados_item["sei_id"]);
        $this->conteudo .= $this->carregaArquivo("servidores_servicos.editar.inc.php");
        // Parametros
        $lista_parametros = $this->dados_item["parametros"];
        if ($lista_parametros) {
          foreach ($lista_parametros as $parametro => $valor) {
            $this->conteudo = str_replace('[parametro_'.$this->dados_item["tipo_id"].'_'.$parametro.']', $valor, $this->conteudo);
          }
        }
        // Parametros Default
        $parametros_default = $this->buscaParametrosDefault();
        if ($parametros_default) {
          foreach ($parametros_default as $key => $lista_parametros) {
            foreach ($lista_parametros as $parametro => $valor) {
              $this->conteudo = str_replace('[parametro_'.$key.'_'.$parametro.']', $valor, $this->conteudo);
            }
          }
        }
        // Dados
        $this->conteudo = str_replace('[sei_id]', $this->dados_item["sei_id"], $this->conteudo);
        $this->conteudo = str_replace('[servidor_nome]', "{$dados_interface["servidor_nome"]} ({$dados_interface["nome"]}".($dados_interface["endereco_ip"] ? ", {$dados_interface["prefixo_ip"]}.{$dados_interface["endereco_ip"]}" : "").")", $this->conteudo);
        $this->conteudo = str_replace('[servicos]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[servico]', $this->dados_item["tipo_id"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        // Botões
        if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$_GET["i"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["desativar"] && $this->dados_item["status_id"] == 1) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" style="width:70" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$_GET["i"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["exportar"] && $this->dados_item["exportavel"]) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" value="Exportar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"]) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar" style="width:60" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["reiniciar"] && $this->dados_item["tipo_id"] <= 2) $botoes[] = '<input type="button" name="reiniciar" class="buttonAzulEscuroBranco11" value="Reiniciar" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=reiniciar&i='.$_GET["i"].'\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" style="width:50" value="Salvar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:55" value="Fechar" onClick="window.close()">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["remover"]) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" style="width:65" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$_GET["i"].'\';">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $lista_parametros = $_POST["parametros_".$_POST["servicos"]];
        foreach ($lista_parametros as $parametro => $valor) {
          $parametros[$parametro] = $valor;
        }
        if ($parametros) {
          $parametros = serialize($parametros);
        } else {
          $parametros = "";
        }
        $query = "
			  	UPDATE
			  		tab_servidores_servicos
			  	SET
			  		ses_parametros = '$parametros'
			  	WHERE
			  		ses_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ################
  ### Exportar ###
  ################

  function exportar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      header("Content-type: application/text;");
      header("Content-Disposition: attachment; filename={$this->dados_item["servico"]}.txt;");
   		$publicacao = controle::instanciaObjeto("servidores_servicos.{$this->dados_item["servico"]}", $this->bancodedados);
      print $publicacao->exportar($this->dados_item["servidor_id"]);
      exit();
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
        $this->conteudo .= $this->carregaArquivo("servidores_servicos.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}&sei_id={$this->dados_item["sei_id"]}", $this->conteudo);
      } else {
        $query = "
          DELETE
            tab_servidores_servicos
          FROM
            tab_servidores_servicos
            INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
          WHERE".($_POST["todos"] ? "
            tab_servidores_interfaces.ser_id = {$this->dados_item["servidor_id"]} AND
            tab_servidores_servicos.sst_id = {$this->dados_item["tipo_id"]}" : "
            tab_servidores_servicos.ses_id = {$_GET["i"]}");
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}&sei_id={$_GET["sei_id"]}");
      }
    } else {
      $this->fechar();
    }
  }

  #################
  ### Reiniciar ###
  #################

  function reiniciar() {
    if (empty($_POST["confirmar"])) {
      $this->conteudo .= $this->carregaArquivo("servidores_interfaces.publicar.inc.php");
      $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      if ($_GET["erro_dhcp"]) {
        $mensagem = "<b>DHCP:</b> " . $_GET["erro_dhcp"];
        $mensagem .= "<br><br>Clique em Confirmar para tentar novamente.";
      } elseif ($_GET["erro_arp"]) {
        $mensagem = "<b>ARP:</b> " . $_GET["erro_arp"];
        $mensagem .= "<br><br>Clique em Confirmar para tentar novamente.";
      } else {
        $mensagem = "Você deseja realmente <b>Reiniciar</b> este serviço?";
      }
      $this->conteudo = str_replace('[mensagem]', $mensagem, $this->conteudo);
      $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=reiniciar&i='.$_GET["i"], $this->conteudo);
    } else {
      $this->buscaDadosItem($_GET["i"]);
      if ($this->dados_item["tipo_id"] == 1) {
      	$erro = $this->reiniciar_dhcp($this->dados_item["servidor_id"]);
        if ($erro)
          header("Location: ./?m=".$this->dados_modulo["nome"]."&a=reiniciar&i={$_GET["i"]}".(($erro) ? "&erro_dhcp=$erro" : "")); else
          header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i={$_GET["i"]}");
      } elseif ($this->dados_item["tipo_id"] == 2) {
      	$erro = $this->reiniciar_arp($this->dados_item["servidor_id"]);
        if ($erro)
          header("Location: ./?m=".$this->dados_modulo["nome"]."&a=reiniciar&i={$_GET["i"]}".(($erro) ? "&erro_arp=$erro" : "")); else
          header("Location: ./?m=".$this->dados_modulo["nome"]."&a=ver&i={$_GET["i"]}");
      }
    }
  }

  ################
  ### Publicar ###
  ################

  function publicar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && !$_POST && !$_GET["sucesso"] && !$_GET["erro"]) {
      $this->conteudo .= $this->carregaArquivo("servidores_servicos.publicar.inc.php");
      $acoes = '<td bgcolor="#505050"><strong>&nbsp;Publicar&nbsp;</strong></td>';
      $this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
      if ($this->dados_item["servidor_situacao_id"] == 1) {
        $mensagem[] = "Você deseja realmente <b>publicar</b> este ponto?";
      } else {
        $mensagem[] = "Pode não ser possível publicar este servidor no momento!";
        $mensagem[] = "<b>Situação do servidor:</b> ".$this->dados_item["servidor_situacao_nome"];
        $mensagem[] = "Deseja continuar com a publicação assim mesmo?";
      }
      $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
      $this->conteudo = str_replace('[mensagem]', @implode("<br><br>", $mensagem), $this->conteudo);
      $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}", $this->conteudo);
    } elseif ($_GET["sucesso"]) {
      $this->conteudo .= "<script type=\"text/javascript\">alert('Ponto publicado com sucesso!');</script>";
      $this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}';</script>";
    } elseif ($_GET["erro"]) {
      $this->conteudo .= $this->carregaArquivo("servidores_servicos.publicar.inc.php");
      $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$mensagem = "{$_GET["erro"]}<br><br>Clique em Confirmar para tentar novamente.";
      $this->conteudo = str_replace('[mensagem]', $mensagem, $this->conteudo);
      $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}", $this->conteudo);
    } elseif ($_POST) {
    	$erro = $this->publicarServicos($this->dados_item["servidor_id"]);
			if ($erro) {
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=$erro");
      } else {
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], "publicar", $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&sucesso=1");
      }
    }
  }

	function publicarServicos($servidor_id) {
    $servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
    return $servidores->publicarServicos($servidor_id);
	}

  ################
  ### Exportar ###
  ################

  function exportar_arp($servidor) {
		$arp = controle::instanciaObjeto("servidores_servicos.arp", $this->bancodedados);
    return $arp->exportarTabela($servidor);
  }

  function exportar_dhcp($servidor) {
		$dhcp = controle::instanciaObjeto("servidores_servicos.dhcp", $this->bancodedados);
    return $dhcp->exportarTabela($servidor);
  }

  function exportar_publicacao($servidor) {
 		$publicacao = controle::instanciaObjeto("servidores_servicos.publicacao", $this->bancodedados);
    return $publicacao->exportar($servidor);
  }

  function exportar_publicacao_freebsd($servidor) {
 		$publicacao = controle::instanciaObjeto("servidores_servicos.publicacao_freebsd", $this->bancodedados);
    return $publicacao->exportar($servidor);
  }

  function exportar_publicacao_freebsd_nat($servidor) {
 		$publicacao = controle::instanciaObjeto("servidores_servicos.publicacao_freebsd_nat", $this->bancodedados);
    return $publicacao->exportar($servidor);
  }

  #################
  ### Reiniciar ###
  #################

  function reiniciar_arp($servidor) {
		$arp = controle::instanciaObjeto("servidores_servicos.arp", $this->bancodedados);
    return $arp->reiniciarServico($servidor);
  }

  function reiniciar_dhcp($servidor) {
		$dhcp = controle::instanciaObjeto("servidores_servicos.dhcp", $this->bancodedados);
    return $dhcp->reiniciarServico($servidor);
  }

  ##############
  ### Ativar ###
  ##############

  function ativar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores_servicos.ativar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "
          UPDATE
            tab_servidores_servicos
            INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
          SET
            tab_servidores_servicos.sss_id = 1
          WHERE
            tab_servidores_interfaces.ser_id = {$this->dados_item["servidor_id"]} AND
            tab_servidores_servicos.sst_id = {$this->dados_item["tipo_id"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  #################
  ### Desativar ###
  #################

  function desativar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores_servicos.desativar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=desativar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "
          UPDATE
            tab_servidores_servicos
            INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
          SET
            tab_servidores_servicos.sss_id = 2
          WHERE
            tab_servidores_interfaces.ser_id = {$this->dados_item["servidor_id"]} AND
            tab_servidores_servicos.sst_id = {$this->dados_item["tipo_id"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### RDS ###
  ###########

  function buscaServidoresServicos($interface_id) {
    $query = "
  		SELECT
  			tab_servidores_servicos.ses_id as ses_id,
  			tab_servidores_servicos.ses_parametros as parametros,
  			tab_servidores_servicos.sei_id as sei_id,
  			tab_servidores_servicos_tipos.sst_id AS tipo_id,
  			tab_servidores_servicos_tipos.sst_nome AS nome,
  			tab_servidores_servicos_status.sss_nome AS status
  		FROM
  			tab_servidores_servicos
  			INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos_tipos.sst_id = tab_servidores_servicos.sst_id)
  			INNER JOIN tab_servidores_servicos_status ON (tab_servidores_servicos.sss_id = tab_servidores_servicos_status.sss_id)
  		WHERE
  			tab_servidores_servicos.sei_id = $interface_id
  		ORDER BY
  			nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["servidores_servicos"]["ver"]) $dados["link"] = "javascript:verServidorServico({$dados["ses_id"]}, $interface_id)";
      $dados["parametros"] = ($dados["parametros"]) ? $this->geraParametros($dados["parametros"], $dados["tipo_id"]) : "Nenhum parâmetro definido";
      $dados["parametros"] = "<div style=\"text-align:left\">{$dados["parametros"]}</div>";
      $servicos[] = $dados;
    }
    return $servicos;
  }

  function geraServidoresServicosListagem($dados) {
  	$cabecalho = array ("nome" => "Nome", "parametros" => "Parâmetros", "status" => "Status");
  	$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
  	return $listagem->conteudo;
  }

  function geraParametros($parametros, $servico) {
    $parametros = @unserialize($parametros);
    switch ($servico) {
      case 1:
        if ($parametros["publicar"])
        $parametros_tratados[] = str_replace(" ", "&nbsp;", "<b>Publicar:</b> {$parametros["publicar"]}");
        if ($parametros["reiniciar"])
        $parametros_tratados[] = str_replace(" ", "&nbsp;", "<b>Reiniciar:</b> {$parametros["reiniciar"]}");
        if ($parametros["publicar_reiniciar"])
        $parametros_tratados[] = str_replace(" ", "&nbsp;", "<b>Publicar e Reiniciar:</b> {$parametros["publicar_reiniciar"]}");
        break;
      case 2:
        if ($parametros["publicar"])
        $parametros_tratados[] = str_replace(" ", "&nbsp;", "<b>Publicar:</b> {$parametros["publicar"]}");
        if ($parametros["reiniciar"])
        $parametros_tratados[] = str_replace(" ", "&nbsp;", "<b>Reiniciar:</b> {$parametros["reiniciar"]}");
        if ($parametros["publicar_reiniciar"])
        $parametros_tratados[] = str_replace(" ", "&nbsp;", "<b>Publicar e Reiniciar:</b> {$parametros["publicar_reiniciar"]}");
        break;
      case 3:
        if ($parametros["instalar"])
        $parametros_tratados[] = "<b>Instalar:</b> {$parametros["instalar"]}";
        break;
    }
    $parametros_tratados = @implode("<br>", $parametros_tratados);
    return $parametros_tratados;
  }

  function buscaQuantidadeServicos($interface_id) {
  	$query = "
  		SELECT
  			tab_servidores_servicos_tipos.sst_id as id
  		FROM
  			tab_servidores_interfaces
  			INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_interfaces.sit_id = tab_servidores_servicos_tipos.sit_id)
  		WHERE
  			tab_servidores_interfaces.sei_id = $interface_id";
  	$query = $this->bancodedados->query($query);
  	return $this->bancodedados->linhas($query);
  }

  function buscaServidoresServicosAdicionar($interface_id) {
    if ($this->permissoes["servidores_servicos"]["adicionar"] && $this->buscaListaServicos($interface_id)) {
 	  	return '<input type="button" class="buttonAzulEscuroBranco11" name="servicos_adicionar" value="Adicionar" onClick="javascript:adicionarServidorServico('.$interface_id.')">';
    }
  }

}

?>
