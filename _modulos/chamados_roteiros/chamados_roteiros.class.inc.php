<?

require_once("_global/modulo.class.inc.php");

class chamados_roteiros extends modulo {

  var $cliente_id;
  var $conexao;
  var $servidores;

  function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		$this->buscaDadosModulo(__CLASS__);
    if ($executar) {
  		$this->buscaDadosAcao($_GET["a"] ?  $_GET["a"] : "executar");
  		$this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
	}

  #############
  ### Geral ###
  #############

  function fechar() {
    $this->conteudo = '<script type="text/javascript">window.close();</script>';
  }

  #########################
  ### Busca Dados Itens ###
  #########################

	function buscaDadosCliente($cliente_id) {
    $query = "
      SELECT
        cli_id AS id,
        cli_nome AS nome
      FROM
        tab_clientes
      WHERE
        tab_clientes.cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["nome"] = "[{$dados["id"]}] {$dados["nome"]}";
      return $dados;
    }
	}

	function buscaRoteiros() {
    $query = "
      SELECT
        chr_id AS id,
        chr_nome AS nome
      FROM
        tab_chamados_roteiros
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $roteiros[$dados["id"]] = $dados["nome"];
    }
    return $roteiros;
	}

	function buscaDadosRoteiro($roteiro_id) {
    $query = "
      SELECT
        chr_id AS id,
        chr_nome AS nome,
        cre_id AS etapa_id
      FROM
        tab_chamados_roteiros
      WHERE
        chr_id = $roteiro_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
	}

	function buscaDadosEtapa($etapa_id) {
    $query = "
      SELECT
        cre_id AS id,
        cre_procedimento AS procedimento,
        cre_parametros AS parametros,
        cre_sim_id AS sim_id,
        cre_nao_id AS nao_id
      FROM
        tab_chamados_roteiros_etapas
      WHERE
        cre_id = $etapa_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      // Trata Dados
      $dados["parametros"] = @unserialize($dados["parametros"]);
      $dados["descricao"] = "<b>» {$dados["procedimento"]}</b>";
      if ($dados["parametros"]["orientacao"]) {
        $dados["descricao"] .= "<div class=\"orientacao\">{$dados["parametros"]["orientacao"]}</div>";
      }
      // Executa Procedimentos Automaticos
  	  if ($dados["parametros"]["funcao_situacao"] && method_exists($this, $dados["parametros"]["funcao_situacao"])) {
  	    $retorno = $this->{$dados["parametros"]["funcao_situacao"]}();
  	    if ($retorno["situacao_id"] !== null) {
          $this->registraEtapa($dados, $retorno["situacao_id"], $retorno["comentario"], $retorno["orientacao"]);
          return $this->buscaDadosEtapa($retorno["situacao_id"] ? $dados["sim_id"] : $dados["nao_id"]);
  	    } else {
  	      $dados["nao_id"] = $retorno["tentar_novamente"] ? $dados["id"] : $dados["nao_id"];
  	      $dados["sim_id"] = $retorno["tentar_novamente"] ? null : $dados["sim_id"];
  	      $dados["descricao"] .= "<div class=\"destaque\">{$retorno["comentario"]}</div>";
          if ($retorno["orientacao"]) {
            $dados["descricao"] .= "<div class=\"orientacao\">{$retorno["orientacao"]}</div>";
          }
          return $dados;
  	    }
  	  } else {
        return $dados;
  	  }
    }
	}

	function buscaSituacoes() {
    if ($_SESSION["roteiro"]["etapa_atual"]["sim_id"] && $_SESSION["roteiro"]["etapa_atual"]["nao_id"]) $botoes[] = '<input type="button" id="sim" style="width:30px" class="buttonAzulEscuroBranco11" value="Sim" onClick="return registraEtapa(1)">';
    if ($_SESSION["roteiro"]["etapa_atual"]["nao_id"] && $_SESSION["roteiro"]["etapa_atual"]["sim_id"]) $botoes[] = '<input type="button" id="nao" style="width:30px" class="buttonAzulEscuroBranco11" value="Não" onClick="return registraEtapa(0)">';
    if ($_SESSION["roteiro"]["etapa_atual"]["nao_id"] && !$_SESSION["roteiro"]["etapa_atual"]["sim_id"]) $botoes[] = '<input type="button" id="sim" style="width:30px" class="buttonAzulEscuroBranco11" value="OK" onClick="return registraEtapa(0)">';
    if ($_SESSION["roteiro"]["etapa_atual"]["sim_id"] && !$_SESSION["roteiro"]["etapa_atual"]["nao_id"]) $botoes[] = '<input type="button" id="sim" style="width:30px" class="buttonAzulEscuroBranco11" value="OK" onClick="return registraEtapa(1)">';
    return @implode(" ", $botoes);
	}

	################
	### Executar ###
	################

	function executar() {
	  $_SESSION["roteiro"]["etapas"] = array();
	  $_SESSION["roteiro"]["etapa_atual"] = null;
	  $dados_cliente = $this->buscaDadosCliente($_GET["i"]);
	  if ($dados_cliente) {
      $this->conteudo .= $this->carregaArquivo("chamados_roteiros.executar.inc.php");
      $this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td>', $this->conteudo);
      $this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
      $this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
      $this->conteudo = str_replace("[roteiros]", $this->geraSelect($this->buscaRoteiros(), null, true, "roteiro", "100%"), $this->conteudo);
      $this->conteudo = str_replace("[etapas]", $this->buscaEtapas(), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" id="iniciar" style="width:60px" class="buttonAzulEscuroBranco11" value="Iniciar" onClick="return iniciarRoteiro()">';
      $botoes[] = '<input type="button" id="fechar" style="width:60px" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
	  }
	}

	function iniciarRoteiro($roteiro_id) {
	  $dados_roteiro = $this->buscaDadosRoteiro($roteiro_id);
	  $_SESSION["roteiro"]["etapas"] = array();
	  $_SESSION["roteiro"]["etapa_atual"] = $this->buscaDadosEtapa($dados_roteiro["etapa_id"]);
	}

	function registraEtapa($dados_etapa, $situacao_id, $comentario = null, $orientacao = null) {
	  $dados_etapa["ordem"] = count($_SESSION["roteiro"]["etapas"]) + 1;
	  if ($comentario) $dados_etapa["descricao"] .= "<div class=\"destaque\">$comentario</div>";
	  if ($orientacao) $dados_etapa["descricao"] .= "<div class=\"orientacao\">$orientacao</div>";
	  $dados_etapa["situacao_id"] = $situacao_id;
	  $dados_etapa["situacao"] = $this->formataSituacao($situacao_id);
    $_SESSION["roteiro"]["etapas"][] = $dados_etapa;
	}

	function registraEtapaSituacao($situacao_id) {
    $this->registraEtapa($_SESSION["roteiro"]["etapa_atual"], $situacao_id);
	  $_SESSION["roteiro"]["etapa_atual"] = $this->buscaDadosEtapa($situacao_id ? $_SESSION["roteiro"]["etapa_atual"]["sim_id"] : $_SESSION["roteiro"]["etapa_atual"]["nao_id"]);
	}

	function buscaEtapas() {
    $etapas = $_SESSION["roteiro"]["etapas"];
	  if ($_SESSION["roteiro"]["etapa_atual"]) {
  	  $_SESSION["roteiro"]["etapa_atual"]["ordem"] = count($etapas) + 1;
  	  $_SESSION["roteiro"]["etapa_atual"]["situacao"] = $this->buscaSituacoes();
	    $etapas[] = $_SESSION["roteiro"]["etapa_atual"];
	  }
    return $this->geraListagemEtapas($etapas);
	}

	function geraListagemEtapas($etapas = null) {
    $cabecalho = array ("ordem" => "#", "descricao" => "Etapa", "situacao" => "Situação");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $etapas, null, false, array(15, 0, 65), false, array("right", "left", "center"));
    return $listagem->conteudo;
	}

	function formataSituacao($situacao_id) {
	  $situacao[0] = '<img src="imagens/reject.png" width="13" height="13" border="0">';
	  $situacao[1] = '<img src="imagens/accept.png" width="13" height="13" border="0">';
	  return $situacao[$situacao_id];
	}

	##################################
	### Funções Especiais - Etapas ###
	##################################

	function buscaSituacaoServidor() {
		$dados_servidor = $this->buscaServidorCliente($this->cliente_id);
	  if ($dados_servidor) {
  	  $retorno["comentario"][] = "<b>Servidor:</b> {$dados_servidor["servidor_nome"]} ({$dados_servidor["ip"]}) - {$dados_servidor["status_nome"]}";
  	  if ($dados_servidor["status_id"] == 1) {
  	    $retorno_ping = $this->testaPing($dados_servidor["ip"]);
  	    if ($retorno_ping["situacao"]) {
  	      $retorno["comentario"][] = "<b>Ping:</b> <b><font color=\"green\">OK</font></b> ({$retorno_ping["retorno"]})";
  	      $retorno_ssh = $this->testaSSH($dados_servidor["ip"]);
  	      if ($retorno_ssh["situacao"]) {
  	        $retorno["comentario"][] = "<b>SSH:</b> <b><font color=\"green\">OK</font></b> ({$retorno_ssh["retorno"]})";
            $retorno["situacao_id"] = true;
  	      } else {
  	        $retorno["comentario"][] = "<b>SSH:</b> <b><font color=\"red\">Erro</font></b> ({$retorno_ssh["retorno"]})";
            $retorno["situacao_id"] = false;
  	      }
  	    } else {
  	      $retorno["comentario"][] = "<b>Ping:</b> <b><font color=\"red\">Erro</font></b> ({$retorno_ping["retorno"]})";
          $retorno["situacao_id"] = false;
  	    }
  	  } else {
        $retorno["situacao_id"] = false;
  	  }
  	  $retorno["comentario"] = implode("<br>", $retorno["comentario"]);
	  } else {
  	  $retorno["situacao_id"] = false;
	    $retorno["comentario"] = "<b>Atenção!</b> Sem servidor cadastrado no condomínio do cliente";
	  }
	  if (!$retorno["situacao_id"]) {
  	  $retorno["orientacao"] = "Verifique a situação do servidor com o seu supervisor.";
	  }
	  return $retorno;
	}

	function buscaSituacaoCliente() {
	  $status_cliente = $this->buscaStatusCliente($this->cliente_id);
	  $dados_ajuste = $this->buscaAjusteAtivo($this->cliente_id);
    if ($dados_ajuste) {
  	  $retorno["comentario"][] = "<b>Ajuste Programado:</b> {$dados_ajuste["categoria_nome"]} (Desde {$dados_ajuste["ajuste_inicio"]})";
  	  if ($dados_ajuste["desbloqueio_inicio"]) {
        $retorno["comentario"][] = "<b>Desbloqueio Temporário:</b> de {$dados_ajuste["desbloqueio_inicio"]} até {$dados_ajuste["desbloqueio_termino"]}";
        $status = "<b>Status:</b> {$status_cliente["nome"]}";
        if ($status_cliente["id"] == 2) $status .= " <b><font color=\"green\">(Desbloqueado)</font></b>";
  	    $retorno["situacao_id"] = true;
  	  } else {
        if ($dados_ajuste["categoria_id"] == 1) {
          $status = "<b>Status:</b> {$status_cliente["nome"]}";
          if ($status_cliente["id"] == 2) $status .= " <b><font color=\"red\">(Bloqueado)</font></b>";
  	      $retorno["orientacao"] = "Cliente bloqueado por pendência financeira. Encaminhe o mesmo para o setor Financeiro.";
  	      $retorno["situacao_id"] = false;
        } else {
          $status = "<b>Status:</b> {$status_cliente["nome"]}";
          if ($status_cliente["id"] == 2) $status .= " <b><font color=\"green\">(Desbloqueado)</font></b>";
  	      $retorno["orientacao"] = "<b>Atenção!</b> Este cliente possui um ou mais Ajustes Programados. Conforme o período, pode ter algum dos seus serviços alterados ou bloqueados.";
  	      $retorno["situacao_id"] = true;
        }
  	  }
    } else {
      $status = "<b>Status:</b> {$status_cliente["nome"]}";
      if ($status_cliente["id"] == 2) $status .= " <b><font color=\"green\">(Desbloqueado)</font></b>";
	    $retorno["situacao_id"] = true;
    }
	  if ($status_cliente["id"] == 2) {
	    if (!$dados_ajuste || $dados_ajuste["desbloqueio_inicio"]) {
	    	$pontos = $this->buscaPontosDisponiveis($this->cliente_id);
  	    if ($pontos) {
    	    $retorno["comentario"][] = $pontos == 1 ? "<b>Assinaturas:</b> $pontos conta de acesso à Internet disponível" : "<b>Assinaturas:</b> $pontos contas disponíveis";
          $retorno["situacao_id"] = $retorno["situacao_id"] && true;
  	    } else {
    	    $retorno["comentario"][] = "<b>Assinaturas:</b> Nenhuma conta de acesso à Internet disponível";
    	    $retorno["orientacao"] = "Entre em contato com o setor de Pós-Vendas para verificação.";
          $retorno["situacao_id"] = false;
  	    }
	    }
	  } else {
	    $retorno["orientacao"] = "Entre em contato com o setor de Pós-Vendas para verificação.";
	    $retorno["situacao_id"] = false;
	  }
	  $retorno["comentario"][] = $status;
	  $retorno["comentario"] = implode("<br>", $retorno["comentario"]);
	  return $retorno;
	}

	function buscaPontosDeAcesso() {
		$clientes_pontos = $this->buscaClientePontos($this->cliente_id);
	  if ($clientes_pontos) {
      foreach ($clientes_pontos as $cliente_ponto) {
        $disponiveis += $cliente_ponto["situacao_id"];
        if ($cliente_ponto["erro_publicacao"]) {
          $erros[] = "<br><b>» Erro ({$cliente_ponto["mac"]}):</b> {$cliente_ponto["erro_publicacao"]}";
        }
      }
      $retorno["comentario"][] = "<div id=\"clientes_pontos\">".$this->geraClientesPontosListagem($clientes_pontos)."</div>";
      if ($disponiveis == count($clientes_pontos)) {
	      $retorno["situacao_id"] = true;
      } else {
        if ($disponiveis) {
          $retorno["orientacao"] = "<b>Atenção!</b> ".(count($clientes_pontos) - $disponiveis)." do(s) ponto(s) acima pode(m) não estar publicado(s) no servidor.";
	        $retorno["situacao_id"] = true;
        } else {
          if (count($clientes_pontos) > 1) {
            $retorno["orientacao"] = "<b>Atenção!</b> Os pontos acima não estão disponíveis para publicação.";
          } else {
            $retorno["orientacao"] = "<b>Atenção!</b> O ponto acima não está disponível para publicação.";
          }
	        $retorno["situacao_id"] = false;
        }
      }
      if ($erros) {
        $retorno["orientacao"] .= "<br>".implode("<br>", $erros);
      }
	  } else {
      $retorno["comentario"][] = "Nenhum ponto cadastrado";
      $retorno["orientacao"] = "<b>Atenção!</b> Solicite o(s) MAC(s) ao cliente e cadastre-o(s) na Intranet. Se o problema persistir, execute este roteiro novamente.";
	    $retorno["situacao_id"] = false;
	  }
	  $retorno["comentario"] = implode("<br>", $retorno["comentario"]);
	  return $retorno;
	}

	function verificaIP() {
		$dados_servidor = $this->buscaServidorCliente($this->cliente_id);
	  if ($dados_servidor) {
	  	$clientes_pontos = $this->buscaClientePontos($this->cliente_id);
  	  if ($clientes_pontos) {
  	    foreach ($clientes_pontos as $cliente_ponto) {
  	      if ($cliente_ponto["situacao_id"]) {
            $ip = $this->buscaIPPorMAC($dados_servidor["ip"], $cliente_ponto["mac"]);
        	  $retorno["comentario"][] = "<b>{$cliente_ponto["mac"]}</b> - ".($ip ? $ip : "Não foi possível capturar o IP no servidor");
  	      }
  	    }
  	    $retorno["comentario"] = implode("<br>", $retorno["comentario"]);
  	    $retorno["orientacao"] = "O IP que está pegando corresponde ao respectivo IP listado acima?";
  	  }
	  } else {
  	  $retorno["comentario"] = "<b>Erro:</b> Sem servidor cadastrado no condomínio do cliente.";
	  }
	  return $retorno;
	}

	######################################
	### Funções Especiais - Auxiliares ###
	######################################

	function buscaServidorCliente($cliente_id) {
	  // Serviço
    $query = "
      SELECT
        tab_servidores.ser_id AS id,
        tab_servidores.ser_nome AS servidor_nome,
        CONCAT(interface_servidor.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip,
        tab_servidores_status.set_id AS status_id,
        tab_servidores_status.set_nome AS status_nome
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
        INNER JOIN tab_servidores_interfaces interface_servidor ON (tab_servidores.sei_id = interface_servidor.sei_id)
        INNER JOIN tab_servidores_status ON (tab_servidores_status.set_id = tab_servidores.set_id)
      WHERE
        tab_clientes.cli_id = $cliente_id AND
        tab_clientes_pontos.cps_id IN (1, 2)
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
	  // Bloco
    $query = "
      SELECT
        tab_servidores.ser_id AS id,
        tab_servidores.ser_nome AS servidor_nome,
        CONCAT(interface_servidor.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip,
        tab_servidores_status.set_id AS status_id,
        tab_servidores_status.set_nome AS status_nome,
        tab_condominios_blocos.con_id AS condominio_id
      FROM
        tab_clientes
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
        INNER JOIN tab_servidores_interfaces interface_servidor ON (tab_servidores.sei_id = interface_servidor.sei_id)
        INNER JOIN tab_servidores_status ON (tab_servidores_status.set_id = tab_servidores.set_id)
      WHERE
        tab_clientes.cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
	}

	function testaPing($servidor_ip) {
    $comando = "/usr/local/libexec/nagios/check_ping -H $servidor_ip -w 100,40% -c 500,50%  -p 3";
    $retorno = exec($comando);
    if (substr($retorno, 0, 8) == "CRITICAL") {
      return array("situacao" => 0, "retorno" => $retorno);
    } elseif (substr($retorno, 0, 4) == "PING") {
      return array("situacao" => 1, "retorno" => $retorno);
    } else {
      return array("situacao" => 0, "retorno" => "Erro desconhecido");
    }
	}

	function testaSSH($servidor_ip) {
    $comando = "/usr/local/libexec/check_ssh -t 3 $servidor_ip";
    $retorno = exec($comando);
    if (substr($retorno, 0, 6) == "SSH OK") {
      return array("situacao" => 1, "retorno" => $retorno);
    } else {
      return array("situacao" => 0, "retorno" => $retorno);
    }
	}

	function buscaAjusteAtivo($cliente_id) {
    $query = "
      SELECT
        tab_servicos_ajustes.saj_id AS id,
        DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS ajuste_inicio,
        DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS ajuste_termino,
        DATE_FORMAT(desbloqueio.saj_inicio, '%d/%m/%Y (%H:%i)') AS desbloqueio_inicio,
        DATE_FORMAT(desbloqueio.saj_termino, '%d/%m/%Y (%H:%i)') AS desbloqueio_termino,
        tab_servicos_ajustes_categorias.sac_id AS categoria_id,
        tab_servicos_ajustes_categorias.sac_nome AS categoria
      FROM
        tab_servicos_ajustes
        INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
        LEFT OUTER JOIN tab_servicos_ajustes desbloqueio ON (tab_servicos_ajustes.saj_id = desbloqueio.saj_pai AND desbloqueio.sas_id IN (5, 9))
      WHERE
        tab_servicos_ajustes.cli_id = $cliente_id AND
        tab_servicos_ajustes.sac_id IN (1, 3, 10) AND
        tab_servicos_ajustes.sas_id IN (5, 9) AND
        tab_servicos_ajustes.saj_inicio <= NOW()
      ORDER BY
        tab_servicos_ajustes.sac_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
	}

	function buscaStatusCliente($cliente_id) {
    $query = "
      SELECT
        tab_clientes_status.cls_id AS id,
        tab_clientes_status.cls_nome AS nome
      FROM
        tab_clientes
        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
      WHERE
        tab_clientes.cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
	}

	function buscaPontosDisponiveis($cliente_id) {
    $query = "
			SELECT
  			tab_assinaturas.ass_id AS id,
 				tab_assinaturas.ass_parametros AS parametros
			FROM
  			tab_planos_servicos
				INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
    		INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
	  		INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
	  		INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id)
        LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
			WHERE
	      (tab_assinaturas.ast_id IN (1, 2, 5)) AND
        (tab_contratos.crt_id IN (11, 12)) AND
        (tab_contratos.crs_id IN (19, 20, 21) OR tab_visitas.vis_data <= CURDATE()) AND
				(tab_assinaturas.cli_id = $cliente_id)
      GROUP BY
        tab_assinaturas.ass_id
			ORDER BY
  			id";
    $query = $this->bancodedados->query($query);
  	while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
  		$dados["parametros"] = @unserialize($dados["parametros"]);
  		$disponivel += $dados["parametros"]["pontos_quantidade"];
  	}
  	return $disponivel;
	}

	function buscaClientePontos($cliente_id) {
    $lista_unidades = $this->buscaUnidadesVelocidade();
    $query = "
      SELECT
      	tab_clientes_pontos.clp_id AS id,
      	tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_clientes_pontos.clp_velocidade_download AS download,
        tab_clientes_pontos.clp_velocidade_upload AS upload,
      	tab_clientes_pontos_status.cps_id AS status_id,
      	tab_clientes_pontos_status.cps_nome AS status_nome,
     		tab_clientes_pontos.clp_publicado AS publicado,
     		tab_clientes_pontos.clp_ajustado AS ajustado,
     		tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
     		tab_clientes_pontos.sei_id AS interface_id,
     		tab_servidores.ser_id AS servidor_id,
    		tab_assinaturas.ass_parametros AS parametros,
   			CONCAT(tab_planos_servicos.pls_nome, '<br>', tab_planos.pla_nome, IF(tab_assinaturas.ast_id = 1, ' - Prospect', '')) AS assinatura,
  	  	tab_clientes_pontos.clp_ip AS ip,
			  CONCAT(tab_servidores.ser_nome, '<br>(', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS interface
      FROM
      	tab_clientes_pontos
  	  	INNER JOIN tab_clientes_pontos_status ON (tab_clientes_pontos_status.cps_id = tab_clientes_pontos.cps_id)
      	INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
  			INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
  			INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
  	  	LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
  	  	LEFT OUTER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
  	  	LEFT OUTER JOIN tab_servidores_interfaces interface_servidor ON (tab_servidores.sei_id = interface_servidor.sei_id)
      WHERE
      	tab_assinaturas.cli_id = $cliente_id AND
      	tab_clientes_pontos.cps_id IN (1, 2)
      ORDER BY
        status_id, tab_planos.plt_id, id";
    $query = $this->bancodedados->query($query);
    $erro_servidor = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $unidade_download = floor(log($dados["download"], 1024));
      if ($unidade_download < 1) $unidade_download = 1;
      if ($this->permissoes["clientes_pontos"]["ver"]) $dados["link"] = "javascript:verPonto({$dados["id"]})";
      $dados["status"] = $dados["status_nome"];
      $dados["velocidade"] = round($dados["download"] / pow(1024, $unidade_download), 1)." / ".
      round($dados["upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
      if ($dados["parametros_ajuste"] && $dados["ajustado"]) {
        $parametros = unserialize($dados["parametros_ajuste"]);
        $unidade = floor(log($parametros["download"], 1024));
        if ($unidade < 1) $unidade = 1;
        $velocidade = round($parametros["download"] / pow(1024, $unidade), 1)." / ".round($parametros["upload"] / pow(1024, $unidade), 1)." ".
        $lista_unidades[$unidade];
        $dados["velocidade"] .= "<br>($velocidade)";
      }
      if (!$dados["interface"]) {
        $dados["interface"] = "Nenhum";
      }
			// MAC (IP)
			$dados["mac_ip"] = $dados["mac"];
			if ($dados["ip"]) {
			  $dados["mac_ip"] .= "<br>({$dados["ip"]})";
			}
			if ($dados["servidor_id"] && !$dados["publicado"]) {
			  if (!$this->servidores) {
          $this->servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
			  }
        if (!$erro_servidor[$dados["servidor_id"]] && ($erro = $this->servidores->publicarServicos($dados["servidor_id"]))) {
          $erro_servidor[$dados["servidor_id"]] = $erro;
        }
			}
			if (!$dados["publicado"] && $dados["servidor_id"] && $erro_servidor[$dados["servidor_id"]]) {
        $dados["erro_publicacao"] = str_replace(array("<br><br>", "<br>"), array(". ", " "), $erro_servidor[$dados["servidor_id"]]).".";
			}
			if (!$dados["servidor_id"]) {
        $dados["erro_publicacao"] = "Sem servidor associado ao ponto";
			}
      // trata Status
      if ($dados["status_id"] != 3) {
        if ($dados["ajustado"]) {
          $dados["status"] .= " (Ajustado)";
        }
        if ($dados["publicado"]) {
          $dados["status"] .= "<br>Publicado";
        } else {
          $dados["status"] .= "<br>Não Publicado";
        }
      }
      if ($dados["publicado"] && $dados["status_id"] == 1 && (!$dados["ajustado"] || $parametros["download"] > 0)) {
        $dados["situacao_id"] = 1;
      } else {
        $dados["situacao_id"] = 0;
      }
      $dados["situacao"] = $this->formataSituacao($dados["situacao_id"]);
      $dados["status"] = '<table cellpadding="0" cellspacing="0" border="0"><tr class="txtAzulEscuro11"><td align="center" valign="center">'.$dados["status"].'</td><td align="right" width="20" valign="center">'.$dados["situacao"].'</td></tr></table>';
      $pontos[] = $dados;
    }
    return $pontos;
	}

	function buscaUnidadesVelocidade() {
  	$unidadecapacidade["1"] = "Kbps";
	  $unidadecapacidade["2"] = "Mbps";
	  $unidadecapacidade["3"] = "Gbps";
	  $unidadecapacidade["4"] = "Tbps";
		return $unidadecapacidade;
	}

  function geraClientesPontosListagem($dados) {
    $cabecalho = array ("interface" => "Servidor", "mac_ip" => "MAC (IP)", "velocidade" => "Velocidade", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, null, null, array("center"));
    return $listagem->conteudo;
  }

  function buscaIPPorMAC($ip_servidor, $mac) {
    if (!($erro = $this->SSHConectar($ip_servidor))) {
      $retorno = $this->SSHExecutar("arp -an | grep -i $mac | awk '{print $2}'", true);
      return substr(trim($retorno), 1, -1);
    } else {
      return $erro;
    }
  }

  ###################
  ### Funções SSH ###
  ###################

  function SSHConectar($ip) {
  	$this->conexao = ssh2_connect($ip, 22);
  	if ($this->conexao) {
      if (ssh2_auth_password($this->conexao, 'root', 'medsocdws!')) {
        return "";
      } else {
        return "Erro de autenticação no servidor";
      }
    } else {
      return "Erro ao conectar-se no servidor";
    }
  }

  function SSHExecutar($comando, $retorno = false) {
    if ($this->conexao) {
      $stream = ssh2_exec($this->conexao, $comando);
      if ($retorno) {
        stream_set_blocking($stream, true);
        return stream_get_contents($stream);
      }
    }
  }

}

?>
