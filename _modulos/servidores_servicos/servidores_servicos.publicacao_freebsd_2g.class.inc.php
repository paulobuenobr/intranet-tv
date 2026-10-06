<?
/*
set_time_limit(0);

date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$teste = controle::instanciaObjeto("servidores_servicos_publicacao_freebsd_2g", $bancodedados);
print $teste->ajustaVelocidades(481);
*/

class servidores_servicos_publicacao_freebsd_2g {

  var $bancodedados;
  var $conexao_primario;
  var $interface_wan;
  var $publicacao;
  var $publicacao_dhcp;
  var $caminho;
  var $ldap;
  var $pontos_tecnica;
  var $nova_linha;
  // Variáveis DHCP
  var $domain_name;
  var $domain_name_servers;
	var $default_lease_time;
	var $max_lease_time;

	function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->pontos_tecnica = unserialize(PUBLICACAO_PONTOS_TECNICA);
    // Variáveis DHCP
    $this->domain_name = "oletelecom.com.br";
    $this->domain_name_servers = "200.194.232.2, 200.194.232.3";
  	$this->default_lease_time = "600";
  	$this->max_lease_time = "86400";
  	$this->nova_linha = $_SERVER && array_key_exists('HTTP_USER_AGENT', $_SERVER) && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false ? "\r\n" : "\n";
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
        if (!$this->conectar($dados_servidor["ip_servidor"], $this->conexao_primario)) {
          // Executa Comandos
          for ($i = 0; $i < count($comandos); $i++) {
            $this->executar($comandos[$i], $this->conexao_primario);
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

  function publicar($servidor_id, $ponto_id = null) {
    if ($_GET["debug"]) {
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "                                                                                                   \n";
      print "<PRE>";
    }
    if ($this->existePontoSemRegra($servidor_id)) {
      $this->regerarRegras($servidor_id);
      $ponto_id = null;
    }
    // Verifica Serviço de Publicação Ativo
    if (!$this->servicoPublicacaoAtivo($servidor_id)) {
      return "Nenhum serviço <b>Publicação FreeBSD 2G</b> ativo";
    }
    // Busca Dados Servidor
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		// Verifica Status do Serviço
		if ($dados_servidor["status_id"] != 1) {
      return "Servidor inativo";
		}
    // Gera Arquivos Publicação
    $this->geraPublicacao($servidor_id, true);
    $this->geraDHCP($servidor_id, true);
    // Verifica Criação dos Arquivos
    if (!file_exists($this->publicacao) || !file_exists($this->publicacao_dhcp)) {
      return "Erro durante criação do arquivo";
    }
		// Testa Conexão SSH
    if (!$this->testaSSH($dados_servidor["ip_servidor"])) {
      return "Erro de SSH - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Conexão com o Servidor
    $erro_conexao = $this->conectar($dados_servidor["ip_servidor"], $this->conexao_primario);
    if ($erro_conexao) {
      return "$erro_conexao - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Envia Arquivo Publicação
    if (!$this->enviar($this->publicacao, "{$this->caminho}/publicacaoBSD.cfg", $this->conexao_primario)) {
      return "Erro durante envio do arquivo de (Publicação) - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Envia Arquivo DHCP
    if ($this->publicacao_dhcp && !$this->enviar($this->publicacao_dhcp, "{$this->caminho}/dhcpd.conf", $this->conexao_primario)) {
      return "Erro durante envio do arquivo de (DHCP) - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Remove Arquivo Temporário
    $this->removerArquivo($this->publicacao);
    if ($this->publicacao_dhcp) {
    	$this->removerArquivo($this->publicacao_dhcp);
    }
    // Executa publicacaoBSD
    if ($ponto_id) {
      $erro_publicacao = $this->executar("publicacaoBSD -p ".$this->buscaRegraPonto($ponto_id), $this->conexao_primario, true);
    } else {
      $erro_publicacao = $this->executar("publicacaoBSD -a", $this->conexao_primario, true);
    }
    if (strpos($erro_publicacao, "[erro]") !== false) {
      return implode(", ", $erro_publicacao)." - Servidor Primário ({$dados_servidor["ip_servidor"]})";
    }
    // Publica DHCP
    if ($this->publicacao_dhcp) {
    	$this->executar("publicacaoBSD -d", $this->conexao_primario, false);
    }
    // Define Serviços Como Atualizados
    if (!$ponto_id) {
      $this->atualizaServicos($servidor_id);
    }
  }

  #########################
  ### Publicar Firewall ###
  #########################

  function publicar_firewall($servidor_id) {
    // Verifica Serviço de Publicação Ativo
    if (!$this->servicoPublicacaoAtivo($servidor_id)) {
      return "Nenhum serviço <b>Publicação FreeBSD 2G</b> ativo";
    }
    // Busca Dados Servidor
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		// Verifica Status do Serviço
		if ($dados_servidor["status_id"] != 1) {
      return "Servidor inativo";
		}
    // Gera Arquivos Publicação
    $this->geraPublicacao($servidor_id, true);
    $this->geraDHCP($servidor_id, true);
    // Verifica Criação dos Arquivos
    if (!file_exists($this->publicacao)) {
      return "Erro durante criação do arquivo";
    }
		// Testa Conexão SSH
    if (!$this->testaSSH($dados_servidor["ip_servidor"])) {
      return "Erro de SSH - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Conexão com o Servidor
    $erro_conexao = $this->conectar($dados_servidor["ip_servidor"], $this->conexao_primario);
    if ($erro_conexao) {
      return "$erro_conexao - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Envia Arquivo Publicação
    if (!$this->enviar($this->publicacao, "{$this->caminho}/publicacao", $this->conexao_primario)) {
      return "Erro durante envio do arquivo de (Publicação) - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Remove Arquivo Temporário
    $this->removerArquivo($this->publicacao);
    // Executa comandos
    $erro_publicacao = $this->executar("publicacaoBSD ".($this->publicacao_dhcp ? "-d " : "")."-f", $this->conexao_primario, true);
    // Define Serviços Como Atualizados
    $this->atualizaServicos($servidor_id);
  }

  #####################
  ### Liberar Ponto ###
  #####################
  
  function liberarPonto($servidor_id, $mac) {
    // Verifica Serviço de Publicação Ativo
    if (!$this->servicoPublicacaoAtivo($servidor_id)) {
      return "Nenhum serviço <b>Publicação FreeBSD 2G</b> ativo";
    }
    // Busca Dados Servidor
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		// Verifica Status do Serviço
		if ($dados_servidor["status_id"] != 1) {
      return "Servidor inativo";
		}
		// Testa Conexão SSH
    if (!$this->testaSSH($dados_servidor["ip_servidor"])) {
      return "Erro de SSH - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Conexão com o Servidor
    $erro_conexao = $this->conectar($dados_servidor["ip_servidor"], $this->conexao_primario);
    if ($erro_conexao) {
      return "$erro_conexao - Servidor ({$dados_servidor["ip_servidor"]})";
    }
    // Publicar
    $erro_publicacao = $this->executar("publicacaoBSD -l $mac -1 -1 60", $this->conexao_primario, true);
    if (strpos($erro_publicacao, "[erro]") !== false) {
      return implode(", ", $erro_publicacao)." - Servidor Primário ({$dados_servidor["ip_servidor"]})";
    }
  }

  ################
  ### Exportar ###
  ################

  function exportar($servidor_id) {
    // Gera rc.conf
    $conteudo[] = "###############";
    $conteudo[] = "### rc.conf ###";
    $conteudo[] = "###############";
    $conteudo[] = "";
    $conteudo[] = $this->geraRcConf($servidor_id);
    // Gera publicacaoBSD.cfg
    $conteudo[] = "";
    $conteudo[] = "";
    $conteudo[] = "#########################";
    $conteudo[] = "### publicacaoBSD.cfg ###";
    $conteudo[] = "#########################";
    $conteudo[] = "";
		$this->buscaDadosServidor($servidor_id);
    if ($this->existePontoSemRegra($servidor_id)) {
      $this->regerarRegras($servidor_id);
    }
    $conteudo[] = $this->geraPublicacao($servidor_id, false);
    // Gera DHCP
    $conteudo[] = "";
    $conteudo[] = "";
    $conteudo[] = "############";
    $conteudo[] = "### DHCP ###";
    $conteudo[] = "############";
    $conteudo[] = "";
    $conteudo[] = $this->geraDHCP($servidor_id);
    return implode($this->nova_linha, $conteudo);
  }
  
  function geraPublicacao($servidor_id, $salvar = false) {
    // Configurações
    $publicacao[] = "#!caminho_arquivos={$this->caminho}";
    $publicacao[] = "#!bloqueio_tcp=135-139,445,593,1025-1030,1080,1363,1364,1368,1377,2745,2283,2535,2745,3127,3410,4444,5554,27374,8866,9898,10080,12345,17300,65506";
    $publicacao[] = "#!bloqueio_udp=135-139,445,4444";
    $publicacao[] = "#!limite_conexoes=800";
    $publicacao[] = "#!interfaces=".implode(",", $this->buscaInterfaces($servidor_id, false));
    $publicacao[] = "#!interface_wan={$this->interface_wan}";
    $publicacao[] = "#!nat=0";
    // Pontos
    $clientes_pontos = $this->buscaPontosClientes($servidor_id);
    if ($clientes_pontos) {
      foreach ($clientes_pontos as $ponto) {
      	$ponto["regra"] = sprintf("% ".strlen(count($clientes_pontos))."s", $ponto["regra"]);
      	$ponto["interface_download"] = sprintf("% -6s", $ponto["interface_download"]);
      	$ponto["interface_upload"] = sprintf("% -6s", $ponto["interface_upload"]);
      	$ponto["download"] = sprintf("% 4s", $ponto["download"]);
      	$ponto["upload"] = sprintf("% 4s", $ponto["upload"]);
        $publicacao[] = "{$ponto["regra"]}; {$ponto["mac"]}; {$ponto["status"]}; {$ponto["empresarial"]}; {$ponto["interface_download"]}; {$ponto["interface_upload"]}; {$ponto["download"]}; {$ponto["upload"]}; {$ponto["comentario"]}";
      }
    }
    if ($salvar) {
      $this->publicacao = "/tmp/publicacaoBSD.cfg_{$servidor_id}_".date("Y_m_d_H_i_s");
      $this->salvarArquivo($this->publicacao, implode($this->nova_linha, $publicacao));
    } else {
      return implode($this->nova_linha, $publicacao);
    }
  }
  
  function geraDHCP($servidor_id, $salvar = false) {
		// Cabeçalho
  	$conteudo[] = "option domain-name \"{$this->domain_name}\";";
  	$conteudo[] = "option domain-name-servers {$this->domain_name_servers};";
  	$conteudo[] = "default-lease-time {$this->default_lease_time};";
  	$conteudo[] = "max-lease-time {$this->max_lease_time};";
  	$conteudo[] = "authoritative;";
  	$conteudo[] = "ddns-update-style none;";
  	$conteudo[] = "log-facility local7;";
  	$conteudo[] = "deny duplicates;";
  	// Busca Interfaces
  	$interfaces = $this->buscaInterfaces($servidor_id);
  	if ($interfaces) {
  		for ($i = 0; $i < count($interfaces); $i++) {
  			$redes = $this->buscaRedes($interfaces[$i]["id"], true);
				if ($redes) {
					$conteudo[] = "";
			  	$conteudo[] = "shared-network {$interfaces[$i]["nome"]} {";
  				for ($r = 0; $r < count($redes); $r++) {
	  				$conteudo[] = "\tsubnet {$redes[$r]["rede"]} netmask {$redes[$r]["mascara"]} {";
	  				$conteudo[] = "\t\trange {$redes[$r]["host_inicial"]} {$redes[$r]["host_final"]};";
	  				$conteudo[] = "\t\toption routers {$redes[$r]["gateway"]};";
	  				$conteudo[] = "\t\tserver-identifier {$redes[$r]["gateway"]};";
	  				$conteudo[] = "\t\toption broadcast-address {$redes[$r]["broadcast"]};";
	  				$conteudo[] = "\t\tdeny unknown-clients;";
	  				$pontos = $this->buscaPontosEmpresarial($redes[$r]["id"]);
	  				if ($pontos) {
  						for ($p = 0; $p < count($pontos); $p++) {
			  				$conteudo[] = "\t\thost {$interfaces[$i]["nome"]}-{$pontos[$p]["cliente_id"]}-{$pontos[$p]["id"]} {";
			  				$conteudo[] = "\t\t\tfixed-address {$pontos[$p]["ip"]};";
			  				$conteudo[] = "\t\t\thardware ethernet {$pontos[$p]["mac"]};";
			  				$conteudo[] = "\t\t}";
	  					}
	  				}
	  				$conteudo[] = "\t}";
  				}
			  	if (($pontos = $this->buscaPontos($interfaces[$i]["id"])) || $this->pontos_tecnica) {
	  				$conteudo[] = "\tgroup {";
	  				// Laptops Técnica
  					for ($p = 0; $p < count($this->pontos_tecnica); $p++) {
  						$conteudo[] = "\t\thost {$interfaces[$i]["nome"]}-ole-".sprintf("%0".strlen(count($this->pontos_tecnica))."s", $p + 1)." { hardware ethernet {$this->pontos_tecnica[$p]}; }";
  					}
	  				// Pontos Clientes
  					for ($p = 0; $p < count($pontos); $p++) {
  						$conteudo[] = "\t\thost {$interfaces[$i]["nome"]}-{$pontos[$p]["cliente_id"]}-{$pontos[$p]["id"]} { hardware ethernet {$pontos[$p]["mac"]}; }";
  					}
	  				$conteudo[] = "\t}";
			  	}
					$conteudo[] = "}";
				}
  		}
  	}
    if ($salvar) {
      $this->publicacao_dhcp = "/tmp/publicacaoBSD.DHCP_{$servidor_id}_".date("Y_m_d_H_i_s");
      $this->salvarArquivo($this->publicacao_dhcp, implode($this->nova_linha, $conteudo));
    } else {
      return implode($this->nova_linha, $conteudo);
    }
  }
  
  function buscaPontosEmpresarial($rede_id) {
    $query = "
      SELECT
        tab_clientes_pontos.clp_id AS id,
        tab_clientes_pontos.clp_ip AS ip,
        tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_assinaturas.cli_id AS cliente_id
      FROM
        tab_clientes_pontos
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_redes ON (tab_clientes_pontos.sei_id = tab_redes.sei_id)
      WHERE
        tab_redes.red_id = $rede_id AND
        INET_ATON(tab_clientes_pontos.clp_ip) - INET_ATON(tab_redes.red_endereco_ip) < POW(2, 32 - tab_redes.red_mascara) AND
        INET_ATON(tab_clientes_pontos.clp_ip) > INET_ATON(tab_redes.red_endereco_ip) AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        tab_clientes_pontos.clp_ip IS NOT NULL AND
        tab_clientes_pontos.cps_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$pontos[] = $dados;
    }
		return $pontos;
  }
  
  function geraRcConf($servidor_id) {
  	$interfaces = $this->buscaInterfaces($servidor_id);
  	if ($interfaces) {
  		$interfaces_nome = array();
  		for ($i = 0; $i < count($interfaces); $i++) {
  			$interfaces_nome[] = $interfaces[$i]["nome"];
  		}
			$conteudo[] = "cloned_interfaces=\"".implode(" ", $interfaces_nome)."\"";
			$conteudo[] = "";
  		for ($i = 0; $i < count($interfaces); $i++) {
  			$redes = $this->buscaRedes($interfaces[$i]["id"]);
  			if ($redes) {
  				for ($j = 0; $j < count($redes); $j++) {
						$conteudo[] = "ifconfig_{$interfaces[$i]["nome"]}_alias{$j}=\"inet {$redes[$j]["gateway"]} netmask {$redes[$j]["mascara"]}\"";
  				}
  			}
  		}
  	}
    return implode($this->nova_linha, $conteudo);
  }

	function ordena_multiarray($array, $campos) {
		if (is_array($array) && sizeof($array)) {
			$tamanho_chave = 0;
			reset($array);
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				$tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
				unset($chave);
				next($array);
			}
			reset($array);
			$array_ordenacao = array();
			while (($array_ordenacao_atual = current($array)) != false) {
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = @implode(",", $chave);
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
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
  
  function buscaInterfaces($servidor_id, $detalhado = true) {
   $query = "
      SELECT
      	sei_id AS id,
      	sei_nome AS nome
      FROM
        tab_servidores_interfaces
      WHERE
        ser_id = $servidor_id AND
        sit_id IN (1, 6) AND
        ((sit_id = 1 AND sei_pai IS NULL) OR
	  		 (sit_id = 6 AND sei_pai IS NOT NULL))";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["nome"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["nome"]));
    	$interfaces[] = $dados;
    }
		$interfaces = $this->ordena_multiarray($interfaces, array("ordem"));
		if ($detalhado) {
			return $interfaces;
		} else {
			for ($i = 0; $i < count($interfaces); $i++) {
				$lista_interfaces[] = $interfaces[$i]["nome"];
			}
			return $lista_interfaces;
		}
  }
  
  function buscaRedes($interface_id, $filtrar_publicacao_dhcp = false) {
    $query = "
      SELECT
  	    tab_redes.red_id AS id,
  	    tab_redes.red_endereco_ip AS rede,
  	    tab_redes.red_mascara AS mascara,
  	    tab_redes.red_reserva_ips AS reserva_ips
      FROM
  	    tab_redes
      WHERE
        sei_id = $interface_id".($filtrar_publicacao_dhcp ? " AND
        red_publicar_dhcp = 1" : "")."
      ORDER BY
        INET_ATON(red_endereco_ip)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["gateway"] = $this->geraIP($dados["rede"], 1, $dados["mascara"]);
    	$dados["broadcast"] = $this->geraIP($dados["rede"], pow(2, 32 - $dados["mascara"]) - 1, $dados["mascara"]);
    	$dados["host_inicial"] = $this->geraIP($dados["rede"], 2 + $dados["reserva_ips"] + ($dados["mascara"] < 30 ? $this->buscaQuantidadePontosIpFixo($dados["id"]) : 0), $dados["mascara"]);
    	$dados["host_final"] = $this->geraIP($dados["rede"], pow(2, 32 - $dados["mascara"]) - 2, $dados["mascara"]);
    	$dados["mascara"] = $this->formataMascara($dados["mascara"]);
    	$redes[] = $dados;
    }
		return $redes;
  }
  
  function buscaQuantidadePontosIpFixo($rede_id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes_pontos.clp_id) AS pontos
      FROM
        tab_clientes_pontos
        INNER JOIN tab_redes ON (tab_clientes_pontos.sei_id = tab_redes.sei_id)
      WHERE
        tab_redes.red_id = $rede_id AND
        INET_ATON(tab_clientes_pontos.clp_ip) - INET_ATON(tab_redes.red_endereco_ip) < POW(2, 32 - tab_redes.red_mascara) AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        tab_clientes_pontos.clp_ip IS NOT NULL AND
  	    tab_clientes_pontos.cps_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "pontos");
  }
  
  function buscaPontos($interface_id) {
    $query = "
      SELECT
        tab_clientes_pontos.clp_id AS id,
        tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_assinaturas.cli_id AS cliente_id
      FROM
        tab_clientes_pontos
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
      WHERE
        tab_clientes_pontos.sei_id = $interface_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        tab_clientes_pontos.clp_ip IS NULL AND
  	    tab_clientes_pontos.cps_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$pontos[] = $dados;
    }
		return $pontos;
  }

	function formataMascara($mascara) {
		$mascara = sprintf("%0-32s", str_repeat("1", $mascara));
		for ($i = 0; $i < 4; $i++) {
			$resultado[] = bindec(substr($mascara, $i * 8, 8));
		}
		return implode(".", $resultado);
	}

	function geraIP($prefixo, $sub_rede, $mascara) {
		$sub_rede = decbin($sub_rede);
		$prefixo = explode(".", $prefixo);
		for ($i = 0; $i < count($prefixo); $i++) {
			$prefixo[$i] = sprintf("%08s", decbin($prefixo[$i]));
		}
		$prefixo = implode($prefixo);
		$mascara = sprintf("%0-32s", str_repeat("1", $mascara));
		$endereco_ip = sprintf("%0-32s", decbin(bindec($prefixo & $mascara) + bindec($sub_rede)));
		for ($i = 0; $i < 4; $i++) {
			$resultado[] = bindec(substr($endereco_ip, $i * 8, 8));
		}
		return implode(".", $resultado);
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
        tab_clientes_pontos.clp_regra AS regra,
        CONCAT(tab_clientes.cli_nome, IF(tab_clientes.con_id IS NOT NULL, CONCAT(' (', tab_condominios.con_nome, IF(tab_condominios_blocos.cob_nome = 'Único' OR tab_clientes.cob_id IS NULL, IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', CONCAT(', ', SUBSTRING(tab_clientes.cli_complemento, 2)), ''), CONCAT(', ', IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', CONCAT(SUBSTRING(tab_clientes.cli_complemento, 2), ' / '), ''), tab_condominios_blocos.cob_nome)), ')'), '')) AS comentario,
        IF(tab_servidores_interfaces.sei_pai IS NULL, tab_servidores_interfaces.sei_nome, tab_interface_pai.sei_nome) AS interface_download,
        tab_servidores_interfaces.sei_nome AS interface_upload,
        IF(tab_planos.pls_id = 3, 1, 0) AS empresarial
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_servidores_interfaces tab_interface_pai ON (tab_servidores_interfaces.sei_pai = tab_interface_pai.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        tab_clientes_pontos.clp_regra IS NOT NULL AND
        tab_clientes_pontos.cps_id IN (1, 2)
      ORDER BY
        tab_clientes_pontos.clp_regra";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["comentario"] = $this->limpaNome($dados["comentario"]);
      $dados["parametros_ajuste"] = @unserialize($dados["parametros_ajuste"]);
      if ($dados["ajustado"] && $dados["parametros_ajuste"]["upload"] && $dados["parametros_ajuste"]["download"]) {
        $dados["upload"] = $dados["parametros_ajuste"]["upload"] ? $dados["parametros_ajuste"]["upload"] / 1024 : 0;
        $dados["download"] = $dados["parametros_ajuste"]["download"] ? $dados["parametros_ajuste"]["download"] / 1024 : 0;
      } else {
        $dados["upload"] = $dados["upload"] ? $dados["upload"] / 1024 : 0;
        $dados["download"] = $dados["download"] ? $dados["download"] / 1024 : 0;
      }
      $dados["status"] = $dados["status_id"] != 1 || !$dados["upload"] || !$dados["download"] || ($dados["ajustado"] && (!$dados["parametros_ajuste"]["upload"] || !$dados["parametros_ajuste"]["download"])) ? 0 : 1;
      $pontos[] = $dados;
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
        tab_servidores_servicos.sst_id = 12 AND
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
      $this->caminho = "/usr/home/intranet";
      $this->interface_wan = $this->buscaInterfaceWan($servidor_id);
      return $dados;
    }
  }

  function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 12 AND sei_id = $interface_id";
    $this->bancodedados->query($query);
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
		 		(tab_servidores_servicos.sst_id = 12) AND
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

	function buscaInterfaceWan($servidor_id) {
		$query = "
			SELECT
				sei_nome AS interface
			FROM
				tab_servidores_interfaces
			WHERE
				sit_id = 5 AND
				ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			return $dados["interface"];
    }
	}
	
	function buscaRegraPonto($ponto_id) {
		$query = "
			SELECT
				clp_regra AS regra
			FROM
				tab_clientes_pontos
			WHERE
				clp_id = $ponto_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			return $dados["regra"];
    }
  }
  
  function existePontoSemRegra($servidor_id) {
    return true;
  }
  
  function regerarRegras($servidor_id) {
    $query = "
      SELECT DISTINCT
        tab_clientes_pontos.clp_id AS id
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_servidores_interfaces tab_interface_pai ON (tab_servidores_interfaces.sei_pai = tab_interface_pai.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        -- tab_clientes_pontos.clp_regra IS NULL AND
        tab_clientes_pontos.cps_id IN (1, 2)
      ORDER BY
        tab_condominios.con_nome,
        tab_condominios_blocos.cob_nome,
        SUBSTRING(tab_clientes.cli_complemento, 2) + 0,
        tab_clientes.cli_nome,
        tab_clientes_pontos.clp_id";
    $query = $this->bancodedados->query($query);
    $regra = 0;
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $regra++;
      $this->bancodedados->query("UPDATE tab_clientes_pontos SET clp_regra = $regra WHERE clp_id = {$dados["id"]}");
    }
  }
  
  function buscaRegraMaxima($servidor_id) {
    $query = "
      SELECT
        MAX(tab_clientes_pontos.clp_regra) AS regra
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        tab_clientes_pontos.clp_regra IS NOT NULL AND
        tab_clientes_pontos.cps_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["regra"] ? $dados["regra"] : 0;
    } else {
      return 0;
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

  function conectar($ip, &$conexao_primario) {
  	$conexao_primario = ssh2_connect($ip, 22);
  	if ($conexao_primario) {
      if ($this->ldap) {
        $usuario = "intranet";
        $senha = "2i1a16wx";
      } else {
        $usuario = "root";
        $senha = "medsocdws!";
      }
      if (ssh2_auth_password($conexao_primario, $usuario, $senha)) {
        return null;
      } else {
        return "Erro de autenticação no servidor";
      }
    } else {
      return "Erro ao conectar-se no servidor";
    }
  }

  function executar($comando, &$conexao_primario, $retorno = false) {
    if ($this->ldap) {
      $comando = "sudo $comando";
    }
    if ($conexao_primario) {
      if ($_GET["debug"]) {
        print $comando."\n"; flush();
      }
      $stream = ssh2_exec($conexao_primario, $comando);
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
        return implode($this->nova_linha, $linhas);
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

  function enviar($arquivo_origem, $arquivo_destino, &$conexao_primario) {
    return ssh2_scp_send($conexao_primario, $arquivo_origem, $arquivo_destino, 0755);
  }

}

?>
