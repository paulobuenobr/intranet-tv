<?

class servidores_servicos_publicacao_freebsd {

  var $bancodedados;
  var $publicacao;
  var $velocidades;
  var $pontos_tecnica;
  var $interfaces;
  var $conexao;
  var $arquivo_publicacao;
  var $arquivo_clientes;
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
        $comandos[] = "/sbin/ipfw pipe ".($velocidade + 1)." config mask dst-ip 0x0000ffff bw ".($ajuste ? $ajuste[$velocidade] : $velocidade)."Kbit/s";
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
    if ($_GET["debug"]) {
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "<PRE>";
    }
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
        // Verifica Consistência Interfaces
        /*
        foreach ($this->interfaces as $interface) {
          $ip_interface = $this->executar("ifconfig {$interface["interface"]} | grep \"{$interface["ip"]}\" | awk '{print $2}'", true);
          if (preg_replace("/[^0-9]/", "", $ip_interface) != $interface["ip"]) {
            $interfaces_inconsistencia[] = "{$interface["interface"]} ({$interface["ip"]})";
          }
        }
        if ($interfaces_inconsistencia) {
          return "Inconsistência IP Interface(s): ".implode(", ", $interfaces_inconsistencia);
        }
        */
        // Gera Arquivos
        $this->geraClientes($servidor_id, true);
        $this->geraPublicacao($dados_servidor, true);
        if (file_exists($this->arquivo_publicacao) && file_exists($this->arquivo_clientes)) {
          $this->executar("montar", true);
          if (!$this->enviar($this->arquivo_publicacao, "{$this->caminho}/publicacao")) {
            return "Erro durante envio do arquivo de (Publicação)";
          }
          if (!$this->enviar($this->arquivo_clientes, "{$this->caminho}/clientes")) {
            return "Erro durante envio do arquivo de (Clientes)";
          }
          // Verifica/Adiciona Velocidades
          $regra = $this->executar("/sbin/ipfw show 45000-58000 | grep pipe | awk '{print $1 \" \" $16}'", true);
          foreach ($this->velocidades["velocidades"] as $tipo => $velocidades) {
            foreach ($velocidades as $velocidade => $codigo) {
              if (strpos($regra, "$codigo") === false) {
                $executar[] = "/sbin/ipfw pipe ".($velocidade + ($tipo == "upload" ? 0 : 1))." config mask ".($tipo == "upload" ? "src" : "dst")."-ip 0x0000ffff bw ".($tipo == "upload" ? $velocidade : round($velocidade * 100 / 100))."Kbit/s";
              }
              foreach ($this->velocidades["interfaces"][$tipo][$velocidade] as $interface) {
                if (strpos($regra, "$codigo $interface") === false) {
                  $executar[] = "/sbin/ipfw -q add $codigo pipe ".($velocidade + ($tipo == "upload" ? 0 : 1))." ip from any to any MAC any any ".($tipo == "upload" ? "in" : "out")." via $interface";
                  $executar[] = "/sbin/ipfw -q add ".($codigo + ($tipo == "upload" ? 5 : 4))." skipto 59000 all from any to any ".($tipo == "upload" ? "in" : "out")." via $interface";
                }
              }
            }
          }
          // Verifica/Adiciona Bloqueio Demais MAC's
          $regra = $this->executar("/sbin/ipfw show 44000 | awk '{print $15}'", true);
          foreach ($this->interfaces as $interface) {
            if (strpos($regra, $interface["interface"]) === false) {
              $executar[] = "/sbin/ipfw -q add 44000 deny all from any to any MAC any any in via {$interface["interface"]}";
            }
          }
          /*
          // Verifica/Adiciona Limite Conexão
          $regra = $this->executar("/sbin/ipfw show 61000 | grep limit | awk '{print $15}'", true);
          foreach ($this->interfaces as $interface) {
            if (strpos($regra, $interface["interface"]) === false) {
              $executar[] = "/sbin/ipfw -q add 61000 allow ip from any to any MAC any any in via {$interface["interface"]} limit src-addr 40";
              $executar[] = "/sbin/ipfw -q add 61000 allow ip from any to any MAC any any out via {$interface["interface"]} limit dst-addr 40";
            }
          }
          */
          $executar[] = "sh {$this->caminho}/clientes";
          $executar[] = "desmontar";
          // Executa comandos
          foreach ($executar as $comando) {
            $this->executar($comando);
          }
          // Remove Arquivos Temporários
          $this->removerArquivo($this->arquivo_publicacao);
          $this->removerArquivo($this->arquivo_clientes);
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
        // Verifica Consistência Interfaces
        /*
        foreach ($this->interfaces as $interface) {
          $ip_interface = $this->executar("ifconfig {$interface["interface"]} | grep \"{$interface["ip"]}\" | awk '{print $2}'", true);
          if (preg_replace("/[^0-9]/", "", $ip_interface) != $interface["ip"]) {
            $interfaces_inconsistencia[] = "{$interface["interface"]} ({$interface["ip"]})";
          }
        }
        if ($interfaces_inconsistencia) {
          return "Inconsistência IP Interface(s): ".implode(", ", $interfaces_inconsistencia);
        }
        */
        // Gera Arquivos
        $this->geraClientes($servidor_id, true);
        $this->geraPublicacao($dados_servidor, true);
        if (file_exists($this->arquivo_publicacao) && file_exists($this->arquivo_clientes)) {
          $this->executar("montar");
          if (!$this->enviar($this->arquivo_publicacao, "{$this->caminho}/publicacao")) {
            return "Erro durante envio do arquivo de (Publicação)";
          }
          if (!$this->enviar($this->arquivo_clientes, "{$this->caminho}/clientes")) {
            return "Erro durante envio do arquivo de (Clientes)";
          }
          // Executa Firewall
          $this->executar("/usr/local/etc/rc.d/firewall.sh");
          // Remove Arquivos Temporários
          $this->removerArquivo($this->arquivo_publicacao);
          $this->removerArquivo($this->arquivo_clientes);
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

  #####################
  ### Liberar Ponto ###
  #####################
  
  function liberarPonto($servidor_id, $mac) {
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
        // Monta Executar
        $executar[] = "/sbin/ipfw -q add 997 skipto 60000 ip from any to any MAC $mac any out";
        $executar[] = "/sbin/ipfw -q add 997 skipto 60000 ip from any to any MAC any $mac in";
        $executar[] = "echo '/sbin/ipfw delete 997' | at now + 60 minutes";
        // Executa comandos
        foreach ($executar as $comando) {
          $this->executar($comando);
        }
      } else {
        return $erro;
      }
    } else {
      return "Serviço Publicação FreeBSD (NAT) inativo";
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
    // Gera Conteúdo Clientes
    $conteudo[] = "#########################";
    $conteudo[] = "### CONTEÚDO CLIENTES ###";
    $conteudo[] = "#########################";
    $conteudo[] = "";
    $conteudo[] = $this->geraClientes($servidor_id);
    // Gera Conteúdo Publicacao
    $conteudo[] = "";
    $conteudo[] = "###########################";
    $conteudo[] = "### CONTEÚDO PUBLICAÇÃO ###";
    $conteudo[] = "###########################";
    $conteudo[] = "";
    $conteudo[] = $this->geraPublicacao($dados_servidor);
    // Retorna Contéudo
    return implode("\n", $conteudo);
  }

  function geraPublicacao($dados_servidor, $salvar = false) {
    // Bloqueio de Vírus
    $publicacao[] = "#!/bin/sh";
    $publicacao[] = "";
    $publicacao[] = "#########################";
    $publicacao[] = "### BLOQUEIO DE VIRUS ###";
    $publicacao[] = "#########################";
    $publicacao[] = "";
    $publicacao = array_merge($publicacao, $this->buscaBloqueiosVirus());
    // Velocidades
    foreach ($this->velocidades["velocidades"] as $tipo => $velocidades) {
      $publicacao[] = "############################".($tipo == "upload" ? "" : "##");
      $publicacao[] = "### VELOCIDADES (".strtoupper($tipo).") ###";
      $publicacao[] = "############################".($tipo == "upload" ? "" : "##");
      $publicacao[] = "";
      ksort($velocidades);
      foreach ($velocidades as $velocidade => $codigo) {
        $velocidade = $this->geraControleVelocidade($velocidade, $codigo, $tipo, $this->velocidades["interfaces"][$tipo][$velocidade]);
        $publicacao = array_merge($publicacao, $velocidade);
      }
    }
    // Limite de Conexões
    /*
    $publicacao[] = "##########################";
    $publicacao[] = "### LIMITE DE CONEXOES ###";
    $publicacao[] = "##########################";
    $publicacao[] = "";
    foreach ($this->interfaces as $interface) {
      $publicacao[] = "/sbin/ipfw -q add 61000 allow ip from any to any MAC any any in via {$interface["interface"]} limit src-addr 40";
      $publicacao[] = "/sbin/ipfw -q add 61000 allow ip from any to any MAC any any out via {$interface["interface"]} limit dst-addr 40";
    }
    $publicacao[] = "";
    */
    // Regras Clientes
    $publicacao[] = "###############################";
    $publicacao[] = "### EXECUTA REGRAS CLIENTES ###";
    $publicacao[] = "###############################";
    $publicacao[] = "";
    $publicacao[] = "sh {$this->caminho}/clientes";
    $publicacao[] = "";
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

  function geraClientes($servidor_id, $salvar = false) {
    // Busca Dados
    $this->velocidades = array();
    //$pontos_tecnica = $this->buscaPontosTecnica();
    $pontos_clientes = $this->buscaPontosClientes($servidor_id);
    // Delta Regras
    $publicacao[] = "#!/bin/sh";
    $publicacao[] = "";
    $publicacao[] = "#############################";
    $publicacao[] = "### DELETA REGRAS ANTIGAS ###";
    $publicacao[] = "#############################";
    $publicacao[] = "";
    $publicacao[] = "/sbin/ipfw delete 44000";
    $publicacao[] = "/sbin/ipfw delete 10000";
    $publicacao[] = "/sbin/ipfw delete 60500";
    $publicacao[] = "";
    // Pontos - Empresa
    if ($this->pontos_tecnica) {
      $publicacao[] = "#########################";
      $publicacao[] = "### LAPTOPS - EMPRESA ###";
      $publicacao[] = "#########################";
      $publicacao[] = "";
      foreach ($this->pontos_tecnica as $ponto) {
        $publicacao[] = "### {$ponto["comentarios"]}";
        $publicacao[] = "/sbin/ipfw -q add {$ponto["codigo"]} skipto {$this->velocidades["velocidades"]["download"][$ponto["download"]]} ip from any to any MAC {$ponto["mac"]} any out";
        $publicacao[] = "/sbin/ipfw -q add {$ponto["codigo"]} skipto {$this->velocidades["velocidades"]["upload"][$ponto["upload"]]} ip from any to any MAC any {$ponto["mac"]} in";
        $publicacao[] = "";
      }
    }
    if ($pontos_clientes) {
      // Clientes x Velocidades
      $publicacao[] = "##############################";
      $publicacao[] = "### CLIENTES X VELOCIDADES ###";
      $publicacao[] = "##############################";
      $publicacao[] = "";
      foreach ($pontos_clientes as $ponto) {
        $publicacao[] = "### ".(!$ponto["download"] || !$ponto["upload"] ? "[Bloqueado] " : "")."{$ponto["comentarios"]}";
        $publicacao[] = (!$ponto["download"] || !$ponto["upload"] ? "### " : "")."/sbin/ipfw -q add {$ponto["codigo"]} skipto ".($ponto["download"] ? $this->velocidades["velocidades"]["download"][$ponto["download"]] : "00000")." ip from any to any MAC {$ponto["mac"]} any out via {$ponto["interface"]}";
        $publicacao[] = (!$ponto["download"] || !$ponto["upload"] ? "### " : "")."/sbin/ipfw -q add {$ponto["codigo"]} skipto ".($ponto["upload"] ? $this->velocidades["velocidades"]["upload"][$ponto["upload"]] : "00000")." ip from any to any MAC any {$ponto["mac"]} in via {$ponto["interface"]}";
        if ($ponto["corporativo"]) {
          $publicacao[] = (!$ponto["download"] || !$ponto["upload"] ? "### " : "")."/sbin/ipfw -q add 60500 skipto 62000 all from any to any MAC any {$ponto["mac"]} in via {$ponto["interface"]}";
          $publicacao[] = (!$ponto["download"] || !$ponto["upload"] ? "### " : "")."/sbin/ipfw -q add 60500 skipto 62000 all from any to any MAC {$ponto["mac"]} any out via {$ponto["interface"]}";
        }
        $publicacao[] = "";
      }
    }
    $publicacao[] = "#############################";
    $publicacao[] = "### BLOQUEIA DEMAIS MAC'S ###";
    $publicacao[] = "#############################";
    $publicacao[] = "";
    foreach ($this->interfaces as $interface) {
      $publicacao[] = "/sbin/ipfw -q add 44000 deny all from any to any MAC any any in via {$interface["interface"]}";
    }
    $publicacao[] = "";
    $publicacao[] = "### Fim";
    $publicacao[] = "";
    if ($salvar) {
      // Salva Arquivo Temporário
      $this->arquivo_clientes = "/tmp/freebsd_clientes_{$servidor_id}_".date("Y_m_d_H_i_s");
      $this->salvarArquivo($this->arquivo_clientes, implode("\n", $publicacao));
    } else {
      return implode("\n", $publicacao);
    }
  }

  function geraControleVelocidade($velocidade, $codigo, $tipo, $interfaces) {
    $publicacao[] = "### {$velocidade} Kbps";
    $publicacao[] = "/sbin/ipfw pipe ".($velocidade + ($tipo == "upload" ? 0 : 1))." config mask ".($tipo == "upload" ? "src" : "dst")."-ip 0x0000ffff bw ".($tipo == "upload" ? $velocidade : round($velocidade * 100 / 100))."Kbit/s";
    foreach ($interfaces as $interface) {
      $publicacao[] = "/sbin/ipfw -q add ".($codigo)." pipe ".($velocidade + ($tipo == "upload" ? 0 : 1))." ip from any to any MAC any any ".($tipo == "upload" ? "in" : "out")." via $interface";
      $publicacao[] = "/sbin/ipfw -q add ".($codigo + ($tipo == "upload" ? 5 : 4))." skipto 59000 all from any to any ".($tipo == "upload" ? "in" : "out")." via $interface";
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
        clp_endereco_mac IS NOT NULL AND
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
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
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
      
      
//      if (intval(date("Hi"), 10) >= 1855 && intval(date("Hi"), 10) <= 2400) {
//        $ponto["download"] = ceil($ponto["download"] * 0.90);
//      }
      
      
      if ($ponto["upload"]) {
        $this->velocidades["velocidades"]["upload"][$ponto["upload"]] = 45000 + $ponto["upload"];
        $this->velocidades["interfaces"]["upload"][$ponto["upload"]][$ponto["interface"]] = $ponto["interface"];
      }
      if ($ponto["download"]) {
        $this->velocidades["velocidades"]["download"][$ponto["download"]] = 45000 + $ponto["download"] + 1;
        $this->velocidades["interfaces"]["download"][$ponto["download"]][$ponto["interface"]] = $ponto["interface"];
      }
      $pontos[] = $ponto;
    }
    return $pontos;
  }

  function buscaPontosTecnica() {
    for ($i = 0; $i < count($this->pontos_tecnica); $i++) {
      $ponto["codigo"] = 10000;
      $ponto["mac"] = $this->pontos_tecnica[$i];
      $ponto["download"] = "300";
      $ponto["upload"] = "150";
      $ponto["comentarios"] = "Laptop ".($i + 1)." - Técnica";
      $this->velocidades["velocidades"]["upload"][$ponto["upload"]] = 45000 + $ponto["upload"];
      $this->velocidades["velocidades"]["download"][$ponto["download"]] = 45000 + $ponto["download"] + 1;
      foreach ($this->interfaces as $interface) {
        $this->velocidades["interfaces"]["upload"][$ponto["upload"]][$interface["interface"]] = $interface["interface"];
        $this->velocidades["interfaces"]["download"][$ponto["download"]][$interface["interface"]] = $interface["interface"];
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

  function buscaBloqueiosVirus() {
    foreach ($this->interfaces as $interface) {
      $bloqueios[] = "/sbin/ipfw -q add 400 deny tcp from any to any dst-port 135-139,445,593,1025-1030,1080,1363,1364,1368,1377,2745,2283,2535,2745,3127,3410,4444,5554,27374,8866,9898,10080,12345,17300,65506 in via {$interface["interface"]}";
      $bloqueios[] = "/sbin/ipfw -q add 400 deny udp from any to any dst-port 135-139,445,4444 in via {$interface["interface"]}";
    }
    $bloqueios[] = "";
    return $bloqueios;
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
        tab_servidores_servicos.sst_id = 10 AND
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
      $dados["ldap"] = $dados["configuracao"]["sistema_operacional"] == 6;
      $this->ldap = $dados["ldap"];
      $this->caminho = $dados["ldap"] ? "/home/intranet" : "/usr/local/etc";
      return $dados;
    }
  }

  function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 10 AND sei_id = $interface_id";
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
        tab_servidores_servicos.sst_id = 10
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
		 		(tab_servidores_servicos.sst_id = 10) AND
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
    $comando = "/usr/local/libexec/check_ssh -t 3 $servidor_ip";
    $retorno = exec($comando);
    if (substr($retorno, 0, 6) == "SSH OK") {
      return true;
    } else {
      return false;
    }
  }

  function conectar($ip) {
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
      if ($_GET["debug"]) {
        print $comando."\n"; flush();
      }
      $stream = ssh2_exec($this->conexao, $comando);
      if ($retorno) {
        $stream_error = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
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
    unlink($arquivo);
  }

  function enviar($arquivo_origem, $arquivo_destino) {
    return ssh2_scp_send($this->conexao, $arquivo_origem, $arquivo_destino, 0755);
  }

}

?>
