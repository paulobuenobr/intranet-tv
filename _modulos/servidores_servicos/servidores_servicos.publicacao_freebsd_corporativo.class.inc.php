<?

class servidores_servicos_publicacao_freebsd_corporativo {

  var $bancodedados;
  var $publicacao;
  var $velocidades;
  var $pontos_tecnica;
  var $interfaces;
  var $conexao;
  var $arquivo_publicacao;
  var $ldap;
  var $caminho;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->pontos_tecnica = unserialize(PUBLICACAO_PONTOS_TECNICA);
  }
  
  ##########################
  ### Ajusta Velocidades ###
  ##########################
  
  function ajustaVelocidades($servidor_id, $ajuste = null) {
    $velocidades = $this->buscaVelocidadesServidor($servidor_id);
    foreach ($velocidades as $velocidade) {
      if (!$ajuste || array_key_exists($velocidade, $ajuste)) {
        $comandos[] = "/sbin/ipfw pipe 301 config bw ".($ajuste ? $ajuste[$velocidade] : $velocidade)."Kbit/s";
      }
    }
    if ($comandos) {
		  $dados_servidor = $this->buscaDadosServidor($servidor_id);
      if ($this->testaSSH($dados_servidor["ip_servidor"])) {
        // Conecta
        if (!$this->conectar($dados_servidor["ip_servidor"], $this->conexao)) {
          // Executa Comandos
          for ($i = 0; $i < count($comandos); $i++) {
            $this->executar($comandos[$i], $this->conexao);
          }
          return true;
        }
      }
    }
    return false;
  }

  ################
  ### Publicar ###
  ################

  function publicar($servidor_id) {
    if (array_key_exists("debug", $_GET)) {
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "<PRE>";
    }
    if ($this->servicoPublicacaoAtivo($servidor_id)) {
  		$dados_servidor = $this->buscaDadosServidor($servidor_id);
      $pontos_clientes = $this->buscaPontosClientes($dados_servidor["id"]);
  		if ($dados_servidor["status_id"] != 1) {
  			if (array_key_exists("debug", $_GET)) {
  				print "Servidor inativo";
  				die();
  			}
        return "Servidor inativo";
  		}
      if (!$this->testaSSH($dados_servidor["ip_servidor"])) {
      	if (array_key_exists("debug", $_GET)) {
      		print "Erro de SSH";
      		die();
      	}
        return "Erro de SSH";
      }
      if (!($erro = $this->conectar($dados_servidor["ip_servidor"]))) {
        // Busca Interfaces
        $this->buscaInterfaces($servidor_id);
        // Gera Arquivos
        $this->geraPublicacao($dados_servidor, true);
        if (file_exists($this->arquivo_publicacao)) {
          $this->executar("montar", true);
          if (!$this->enviar($this->arquivo_publicacao, "{$this->caminho}/publicacao")) {
          	if (array_key_exists("debug", $_GET)) {
	          	print "Erro durante envio do arquivo de (Publicação)";
	          	die();
          	}
            return "Erro durante envio do arquivo de (Publicação)";
          }
          // Verifica/Adiciona Velocidades
          $regra = $this->executar("/sbin/ipfw show 300-301 | grep pipe | awk '{print $1 \" \" $13}'", true);
          foreach ($this->velocidades["velocidades"] as $tipo => $velocidades) {
            foreach ($velocidades as $velocidade => $codigo) {
              //if (strpos($regra, "$codigo") === false) {
                $executar[] = "/sbin/ipfw pipe $codigo config bw ".($tipo == "upload" ? $velocidade : round($velocidade * 100 / 100))."Kbit/s";
              //}
              foreach ($this->velocidades["interfaces"][$tipo][$velocidade] as $interface) {
                if (strpos($regra, "$codigo $interface") === false) {
                  $executar[] = "/sbin/ipfw -q add $codigo pipe $codigo ip from any to any ".($tipo == "upload" ? "in" : "out")." via $interface";
                }
              }
            }
          }
          foreach ($pontos_clientes as $ponto) {
            if (!$ponto["download"] || !$ponto["upload"]) {
              $executar[] = "/sbin/ipfw add 100 deny all from any to any via {$ponto["interface"]}";
            } else {
              $executar[] = "/sbin/ipfw delete 100";
            }
          }
          $executar[] = "desmontar";
          // Executa comandos
          foreach ($executar as $comando) {
            $this->executar($comando);
          }
          // Remove Arquivos Temporários
          $this->removerArquivo($this->arquivo_publicacao);
          // Define Serviços Como Atualizados
          $this->atualizaServicos($servidor_id);
        } else {
        	if (array_key_exists("debug", $_GET)) {
        		print "Erro durante criação do arquivo";
        		die();
        	}
          return "Erro durante criação do arquivo";
        }
      } else {
      	if (array_key_exists("debug", $_GET)) {
      		print $erro;
      		die();
      	}
        return $erro;
      }
    } else {
    	if (array_key_exists("debug", $_GET)) {
    		print "Serviço Publicação FreeBSD inativo";
    		die();
    	}
      return "Serviço Publicação FreeBSD inativo";
    }
  }

  function publicar_firewall($servidor_id) {
    if ($this->servicoPublicacaoAtivo($servidor_id)) {
  		$dados_servidor = $this->buscaDadosServidor($servidor_id);
  		if ($dados_servidor["status_id"] != 1) {
        return "Servidor inativo";
  		}
      if (!$this->testaSSH($dados_servidor["ip_servidor"])) {
        return "Erro de SSH";
      }
      if (!($erro = $this->conectar($dados_servidor["ip_servidor"]))) {
        // Busca Interfaces
        $this->buscaInterfaces($servidor_id);
        // Gera Arquivos
        $this->geraPublicacao($dados_servidor, true);
        if (file_exists($this->arquivo_publicacao)) {
          $this->executar("montar");
          if (!$this->enviar($this->arquivo_publicacao, "{$this->caminho}/publicacao")) {
            return "Erro durante envio do arquivo de (Publicação)";
          }
          // Executa Firewall
          $this->executar("/usr/local/etc/rc.d/firewall.sh");
          // Remove Arquivos Temporários
          $this->removerArquivo($this->arquivo_publicacao);
          // Define Serviços Como Atualizados
          $this->atualizaServicos($servidor_id);
        } else {
          return "Erro durante criação do arquivo";
        }
      } else {
        return $erro;
      }
    } else {
      return "Serviço Publicação FreeBSD inativo";
    }
  }

  ################
  ### Exportar ###
  ################
  
  function exportar($servidor_id) {
    // Busca Interfaces Cliente
    $this->buscaInterfaces($servidor_id);
    // Busca Dados Servidor
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
    // Retorna Contéudo
    return $this->geraPublicacao($dados_servidor);
  }

  function geraPublicacao($dados_servidor, $salvar = false) {
    $pontos_clientes = $this->buscaPontosClientes($dados_servidor["id"]);
    // Bloqueio de Vírus
    $publicacao[] = "#!/bin/sh";
    $publicacao[] = "";
    // Velocidades
    foreach ($this->velocidades["velocidades"] as $tipo => $velocidades) {
      $publicacao[] = "############################".($tipo == "upload" ? "" : "##");
      $publicacao[] = "### VELOCIDADE (".strtoupper($tipo).") ###";
      $publicacao[] = "############################".($tipo == "upload" ? "" : "##");
      $publicacao[] = "";
      ksort($velocidades);
      foreach ($velocidades as $velocidade => $codigo) {
        $velocidade = $this->geraControleVelocidade($velocidade, $codigo, $tipo, $this->velocidades["interfaces"][$tipo][$velocidade]);
        $publicacao = array_merge($publicacao, $velocidade);
      }
    }
    $publicacao[] = "";
    foreach ($pontos_clientes as $ponto) {
      if (!$ponto["download"] || !$ponto["upload"]) {
        $publicacao[] = "/sbin/ipfw add 100 deny all from any to any via {$ponto["interface"]}";
      }
    }
    $publicacao[] = "### Fim";
    $publicacao[] = "";
    if ($salvar) {
      // Salva Arquivo Temporário
      $this->arquivo_publicacao = "/tmp/freebsd_publicacao_{$dados_servidor["id"]}_".date("Y_m_d_H_i_s");
      $this->salvarArquivo($this->arquivo_publicacao, implode("\n", $publicacao));
    } else {
      return implode("\n", $publicacao);
    }
  }

  function geraControleVelocidade($velocidade, $codigo, $tipo, $interfaces) {
    $publicacao[] = "### {$velocidade} Kbps";
    $publicacao[] = "/sbin/ipfw pipe ".(300 + ($tipo == "upload" ? 0 : 1))." config bw ".($tipo == "upload" ? $velocidade : round($velocidade * 100 / 100))."Kbit/s";
    foreach ($interfaces as $interface) {
      $publicacao[] = "/sbin/ipfw -q add ".(300 + ($tipo == "upload" ? 0 : 1))." pipe ".(300 + ($tipo == "upload" ? 0 : 1))." ip from any to any ".($tipo == "upload" ? "in" : "out")." via $interface";
    }
    $publicacao[] = "";
    return $publicacao;
  }

  ######################
  ### Banco de Dados ###
  ######################
  
  function buscaVelocidadesServidor($servidor_id) {
    $query = "
      SELECT
        clp_ajustado AS ajustado,
        clp_velocidade_download AS download,
        clp_parametros_ajuste AS parametros_ajuste
      FROM
        tab_clientes_pontos
        INNER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        ser_id = $servidor_id AND
  	    cps_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros_ajuste"] = @unserialize($dados["parametros_ajuste"]);
      if ($dados["ajustado"] && $dados["parametros_ajuste"]["upload"] && $dados["parametros_ajuste"]["download"]) {
        $dados["download"] = $dados["parametros_ajuste"]["download"] ? $dados["parametros_ajuste"]["download"] / 1024 : 0;
      } else {
        $dados["download"] = $dados["download"] ? $dados["download"] / 1024 : 0;
      }
      $velocidades[$dados["download"]] = $dados["download"];
    }
    ksort($velocidades);
		return $velocidades;
  }

  function buscaPontosClientes($servidor_id) {
    $query = "
      SELECT DISTINCT
        tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_clientes_pontos.clp_velocidade_upload AS upload,
        tab_clientes_pontos.clp_velocidade_download AS download,
        tab_clientes_pontos.clp_ajustado AS ajustado,
				tab_clientes_pontos.cps_id AS status_id,
        tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
        CONCAT(tab_clientes.cli_nome, IF(tab_clientes.con_id IS NOT NULL, CONCAT(' (', tab_condominios.con_nome, IF(tab_condominios_blocos.cob_nome = 'Único' OR tab_clientes.cob_id IS NULL, IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', CONCAT(', ', SUBSTRING(tab_clientes.cli_complemento, 2)), ''), CONCAT(', ', IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', CONCAT(SUBSTRING(tab_clientes.cli_complemento, 2), ' / '), ''), tab_condominios_blocos.cob_nome)), ')'), '')) AS comentarios,
        tab_servidores_interfaces.sei_nome AS interface,
        IF(tab_planos.pls_id = 3, 1, 0) AS corporativo
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.cps_id = 1
      ORDER BY
        tab_condominios.con_nome,
        tab_condominios_blocos.cob_nome,
        SUBSTRING(tab_clientes.cli_complemento, 2) + 0";
    $query = $this->bancodedados->query($query);
    while (($ponto = $this->bancodedados->resultadoArray($query)) != false) {
      $ponto["codigo"] = 10000;
      $ponto["comentarios"] = $this->limpaNome($ponto["comentarios"]);
      if ($ponto["ajustado"]) {
        $ponto["parametros_ajuste"] = @unserialize($ponto["parametros_ajuste"]);
        $ponto["upload"] = $ponto["parametros_ajuste"]["upload"] ? $ponto["parametros_ajuste"]["upload"] / 1024 : 0;
        $ponto["download"] = $ponto["parametros_ajuste"]["download"] ? $ponto["parametros_ajuste"]["download"] / 1024 : 0;
      } else {
        $ponto["upload"] = $ponto["upload"] ? $ponto["upload"] / 1024 : 0;
        $ponto["download"] = $ponto["download"] ? $ponto["download"] / 1024 : 0;
      }
      $ponto["upload"] = intval(ceil($ponto["upload"]), 10);
      $ponto["download"] = intval(ceil($ponto["download"]), 10);
      if ($ponto["upload"]) {
        $this->velocidades["velocidades"]["upload"][$ponto["upload"]] = 300;
        $this->velocidades["interfaces"]["upload"][$ponto["upload"]][$ponto["interface"]] = $ponto["interface"];
      }
      if ($ponto["download"]) {
        $this->velocidades["velocidades"]["download"][$ponto["download"]] = 301;
        $this->velocidades["interfaces"]["download"][$ponto["download"]][$ponto["interface"]] = $ponto["interface"];
      }
      $pontos[] = $ponto;
    }
    return $pontos;
  }

  function limpaNome($nome) {
    $nome = str_replace(array("ç", "ñ"), array("c", "n"), $nome);
    $nome = preg_replace("/[áàâãäª]/", "a", $nome);
    $nome = preg_replace("/[éèêë]/", "e", $nome);
    $nome = preg_replace("/[íìîï]/", "i", $nome);
    $nome = preg_replace("/[óòôõö°º]/", "o", $nome);
    $nome = preg_replace("/[úùûü]/", "u", $nome);
  	$nome = trim($nome);
    return $nome;
  }

  function servicoPublicacaoAtivo($servidor_id) {
    $query = "
      SELECT
        ses_id AS id
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_servidores_servicos.sss_id = 1 AND
        tab_servidores_servicos.sst_id = 14 AND
        tab_servidores_interfaces.sia_id = 1 AND
        tab_servidores_interfaces.ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosServidor($servidor_id) {
    $query = "
      SELECT DISTINCT
        tab_servidores.ser_id AS id,
        tab_servidores.set_id AS status_id,
        tab_servidores_interfaces.sei_prefixo_ip AS prefixo_ip,
        tab_servidores_interfaces.sei_endereco_ip AS endereco_ip,
        tab_servidores_interfaces.sei_mascara_rede AS mascara_rede,
        CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip_servidor,
  			tab_servidores.ser_configuracao AS configuracao
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores.ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["configuracao"] = unserialize($dados["configuracao"]);
      $dados["ldap"] = array_key_exists("sistema_operacional", $dados["configuracao"]) && $dados["configuracao"]["sistema_operacional"] == 6;
      $this->ldap = $dados["ldap"];
      $this->caminho = $dados["ldap"] ? "/home/intranet" : "/usr/local/etc";
      return $dados;
    }
  }

  function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 14 AND sei_id = $interface_id";
    $this->bancodedados->query($query);
  }

  function buscaInterfaces($servidor_id) {
    $this->interfaces = array();
    $query = "
      SELECT
        tab_servidores_interfaces.sei_id AS id,
        tab_servidores_interfaces.sei_nome AS interface,
        CONCAT(sei_prefixo_ip, '.', sei_endereco_ip) AS ip
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_servidores_interfaces.sit_id IN (1, 6) AND
        tab_servidores_interfaces.sei_pai IS NULL AND
        tab_servidores_interfaces.sia_id = 1 AND
        tab_servidores_servicos.sst_id = 14
      ORDER BY
        tab_servidores_interfaces.sei_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
       $this->interfaces[] = $dados;
    }
  }

	function servidorAtualizado($servidor_id) {
		$query = "
			SELECT DISTINCT
				tab_servidores_interfaces.ser_id AS servidor_id
			FROM
			  tab_servidores_servicos
			  INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				(tab_servidores_servicos.ses_data_publicacao IS NULL || tab_servidores_servicos.ses_data_alteracao > tab_servidores_servicos.ses_data_publicacao) AND
		 		(tab_servidores_servicos.sss_id = 1) AND
		 		(tab_servidores_servicos.sst_id = 14) AND
		 		(tab_servidores_interfaces.sia_id = 1) AND
		 		(tab_servidores_interfaces.sit_id IN (1, 6)) AND
				(tab_servidores_interfaces.ser_id = $servidor_id)";
	  $query = $this->bancodedados->query($query);
	  if ($this->bancodedados->resultadoArray($query)) {
	  	return false;
	  } else {
	  	return true;
	  }
	}

	##########################
  ### Publicação e Envio ###
  ##########################

  function testaSSH($servidor_ip) {
  return true;
    if (array_key_exists("debug", $_GET)) {
    	print "/usr/local/libexec/check_ssh -t 3 $servidor_ip\n";
    }
    $comando = "/usr/local/libexec/check_ssh -t 3 $servidor_ip";
    $retorno = exec($comando);
    if (substr($retorno, 0, 6) == "SSH OK") {
      return true;
    } else {
      return false;
    }
  }

  function conectar($ip) {
    if (array_key_exists("debug", $_GET)) {
    	print "ssh2_connect({$ip}, 22)\n";
    }
  	$this->conexao = ssh2_connect($ip, 22);
    if ($this->conexao) {
      if ($this->ldap) {
        $usuario = "intranet";
        $senha = "2i1a16wx";
      } else {
        $usuario = "root";
        $senha = "medsocdws!";
      }
      if (ssh2_auth_password($this->conexao, $usuario, $senha)) {
        return "";
      } else {
        return "Erro de autenticação no servidor";
      }
    } else {
      return "Erro ao conectar-se no servidor";
    }
  }

  function executar($comando, $retorno = false) {
    if ($this->ldap) {
      $comando = "sudo $comando";
    }
    if ($this->conexao) {
      if (array_key_exists("debug", $_GET)) {
        print $comando."\n"; flush();
      }
      $stream = ssh2_exec($this->conexao, $comando);
      if ($retorno) {
        $stream_error = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
      	stream_set_blocking($stream, true);
        stream_set_blocking($stream_error, true);
        while (($linha = fgets($stream_error)) != false) {
          $linhas[] = $linha;
        }
        while (($linha = fgets($stream)) != false) {
          $linhas[] = $linha;
        }
        fclose($stream);
        fclose($stream_error);
        return implode("\n", $linhas);
      }
    }
  }

  function salvarArquivo($arquivo, $conteudo) {
    $arquivo = @fopen($arquivo, "w");
    @fwrite($arquivo, $conteudo);
    @fclose($arquivo);
  }

  function removerArquivo($arquivo) {
  	if (array_key_exists("debug", $_GET)) {
  		print "unlink({$arquivo});\n";
  	}
  	unlink($arquivo);
  }

  function enviar($arquivo_origem, $arquivo_destino) {
    if (array_key_exists("debug", $_GET)) {
      print "ssh2_scp_send({$this->conexao}, {$arquivo_origem}, {$arquivo_destino}, 0755)\n"; flush();
    }
  	return ssh2_scp_send($this->conexao, $arquivo_origem, $arquivo_destino, 0755);
  }

}

?>
