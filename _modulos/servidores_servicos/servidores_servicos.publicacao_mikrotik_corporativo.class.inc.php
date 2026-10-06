<?

set_time_limit(0);
ini_set("default_socket_timeout", 600);

//require_once("telnet_mikrotik.class.php");

/*
date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$teste = controle::instanciaObjeto("servidores_servicos_publicacao_mikrotik_corporativo", $bancodedados);
print $teste->publicar(605, 33513);
//*/

class servidores_servicos_publicacao_mikrotik_corporativo {

  var $bancodedados;
  var $conexao;
  var $ip;
  var $usuario;
  var $senha;
  var $telnet;
  var $pool;
  
  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->usuario = "admin";
    $this->senha = "M0rT4d3l41";
  }

  ################
  ### Publicar ###
  ################

  function publicar($servidor_id, $ponto_id = null) {
    // Busca Dados Servidor
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
    // Conectar
    $erro_conexao = $this->conectar($dados_servidor["ip_servidor"]);
    if ($erro_conexao) {
      return $erro_conexao;
    }
    if ($ponto_id) {
    	return $this->publicarPonto($ponto_id);
    } else {
    	return $this->publicarServidor($servidor_id);
    }
  }
  
  function publicarServidor($servidor_id) {
  	$interfaces = $this->buscaInterfaces($servidor_id);
    if ($interfaces) {
    	foreach ($interfaces as $interface) {
    		$verificar_pool = true;
    		// Busca Endereço Interface
    		$retorno = $this->executar("/ip address print without-paging where interface={$interface["nome"]}");
    		$endereco_interface = $this->trataEnderecoInterface($retorno);
    		// Busca Lease
    		$retorno = $this->executar("/ip dhcp-server lease print detail without-paging where server=dhcp_{$interface["nome"]}");
    		if ($retorno === false) {
		  		return "Desconectado do servidor";
    		}
    		$pontos_lease = $this->trataLeasePontos($retorno);
    		/*
    		// Busca ARP
			  $retorno = $this->executar("/ip arp print detail without-paging where interface={$interface["nome"]}");
    		if ($retorno === false) {
		  		return "Desconectado do servidor";
    		}
			  $pontos_arp = $this->trataArpPontos($retorno);
			  */
    		// Busca Queue
			  $retorno = $this->executar("/queue simple print detail without-paging where interface={$interface["nome"]}");
    		if ($retorno === false) {
		  		return "Desconectado do servidor";
    		}
    		$pontos_queue = $this->trataQueuePontos($retorno);
    		
    		// Busca pontos
    		$pontos = $this->buscaPontosClientes($interface["id"]);
			  if ($pontos) {
		    	foreach ($pontos as $ponto) {
//		    		if ($ponto["empresarial"]) {
		    			// Cliente empresarial
		    			/*
		    			// Insere/atualiza arp
		    			if (!$pontos_arp || !$pontos_arp[$ponto["mac"]] || ($pontos_arp[$ponto["mac"]] && $pontos_arp[$ponto["mac"]]["status"] == "D")) {
		    				$this->executar("/ip arp add address={$ponto["ip"]} mac-address={$ponto["mac"]} interface={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
		    			} else {
								if ($ponto["comentario"] != $pontos_arp[$ponto["mac"]]["comentario"] ||
								    $ponto["ip"] != $pontos_arp[$ponto["mac"]]["ip"] ||
								    $ponto["bloqueado"] != $pontos_arp[$ponto["mac"]]["bloqueado"]) {
				    			$this->executar("/ip arp set [/ip arp find mac-address={$ponto["mac"]}] address={$ponto["ip"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
								}
		    				unset($pontos_arp[$ponto["mac"]]);
		    			} */
		    			$target_address = "{$endereco_interface["rede"]}/{$endereco_interface["classe"]}";
		    			// Insere/atualiza queue
		    			if (!$pontos_queue || !$pontos_queue[$target_address]) {
			    			$this->executar("/queue simple add target-addresses=$target_address max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$ponto["interface"]} name=\"cliente{$ponto["cliente_id"]}\" comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
		    			} else {
								if ($ponto["comentario"] != $pontos_queue[$ponto["rede"]]["comentario"] ||
								    $ponto["download"] != $pontos_queue[$ponto["rede"]]["download"] ||
								    $ponto["upload"] != $pontos_queue[$ponto["rede"]]["upload"] ||
								    $ponto["bloqueado"] != $pontos_queue[$ponto["rede"]]["bloqueado"]) {
				    			$this->executar("/queue simple set [/queue simple find target-addresses=$target_address]  max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$ponto["interface"]} name=\"cliente{$ponto["cliente_id"]}\" comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
								}
		    				unset($pontos_queue[$target_address]);
		    			}
//		    		} else {
//		    			// Cliente não-empresarial
//		    			if ($verificar_pool) {
//		    				$erro = $this->verificaPool($ponto["interface"], $pontos);
//						    if ($erro) {
//						    	return $erro;
//						    }
//						    $erro = $this->verificaDHCPServer($ponto["dhcp_server"]);
//						    if ($erro) {
//						    	return $erro;
//						    }
//						    $verificar_pool = false;
//		    			}
//		    			// Atualiza IP
//		    			if ($pontos_lease[$ponto["mac"]]["address"] != $ponto["ip"] && in_array($pontos_lease[$ponto["mac"]]["address"], $this->pool[$ponto["interface"]])) {
//		    				$ponto["ip"] = $pontos_lease[$ponto["mac"]]["address"];
//		    				unset($this->pool[$ponto["interface"]][array_search($ponto["ip"], $this->pool[$ponto["interface"]])]);
//		    				$this->atualizaPontoIP($ponto["id"], $ponto["ip"]);
//		    			} elseif (!$ponto["ip"] || !in_array($ponto["ip"], $this->pool[$ponto["interface"]])) {
//		    				$ponto["ip"] = array_shift($this->pool[$ponto["interface"]]);
//		    				$this->atualizaPontoIP($ponto["id"], $ponto["ip"]);
//		    			} else {
//		    				unset($this->pool[$ponto["interface"]][array_search($ponto["ip"], $this->pool[$ponto["interface"]])]);
//		    			}
//		    			// Insere/atualiza lease
//				    	if (!$pontos_lease || !$pontos_lease[$ponto["mac"]]) {
//				    		$this->executar("/ip dhcp-server lease add server={$ponto["dhcp_server"]} address={$ponto["ip"]} mac-address={$ponto["mac"]} comment=\"{$ponto["comentario"]}\" rate-limit={$ponto["upload"]}/{$ponto["download"]} block-access=no disabled={$ponto["bloqueado"]}");
//				    	} else {
//								if ($ponto["comentario"] != $pontos_lease[$ponto["mac"]]["comentario"] ||
//								    $ponto["ip"] != $pontos_lease[$ponto["mac"]]["address"] ||
//										$ponto["download"] != $pontos_lease[$ponto["mac"]]["download"] ||
//								    $ponto["upload"] != $pontos_lease[$ponto["mac"]]["upload"] ||
//								    $ponto["dhcp_server"] != $pontos_lease[$ponto["mac"]]["server"] ||
//								    $ponto["bloqueado"] != $pontos_lease[$ponto["mac"]]["bloqueado"]) {
//				    			$this->executar("/ip dhcp-server lease set [/ip dhcp-server lease find mac-address={$ponto["mac"]}] address={$ponto["ip"]} comment=\"{$ponto["comentario"]}\" rate-limit={$ponto["upload"]}/{$ponto["download"]} block-access=no disabled={$ponto["bloqueado"]}");
//								}
//				    		unset($pontos_lease[$ponto["mac"]]);
//				    	}
//				    	/*
//		    			// Insere/atualiza arp
//		    			if (!$pontos_arp || !$pontos_arp[$ponto["mac"]] || ($pontos_arp[$ponto["mac"]] && $pontos_arp[$ponto["mac"]]["status"] == "D")) {
//		    				$this->executar("/ip arp add address={$ponto["ip"]} mac-address={$ponto["mac"]} interface={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
//		    			} else {
//								if ($ponto["comentario"] != $pontos_arp[$ponto["mac"]]["comentario"] ||
//								    $ponto["ip"] != $pontos_arp[$ponto["mac"]]["ip"] ||
//								    $ponto["bloqueado"] != $pontos_arp[$ponto["mac"]]["bloqueado"]) {
//				    			$this->executar("/ip arp set [/ip arp find mac-address={$ponto["mac"]}] address={$ponto["ip"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
//								}
//		    				unset($pontos_arp[$ponto["mac"]]);
//		    			} */
//		    		}
		    	}
		    }
		    // Remove leases
		    if ($pontos_lease) {
			    foreach ($pontos_lease as $ponto) {
		    		$this->executar("/ip dhcp-server lease remove [/ip dhcp-server lease find mac-address={$ponto["mac"]}]");
			    }
		    }
		    /*
		    // Remove arp
		    if ($pontos_arp) {
			    foreach ($pontos_arp as $ponto) {
			    	if ($ponto["status"] != "D") {
		    			$this->executar("/ip arp remove [/ip arp find address={$ponto["ip"]}]");
			    	}
			    }
		    } */
		    // Remove queue
		    if ($pontos_queue) {
			    foreach ($pontos_queue as $ponto) {
			    	if ($ponto["status"] != "D") {
		    			$this->executar("/queue simple remove [/queue simple find name={$ponto["nome"]}]");
			    	}
			    }
		    }
    	}
    }
  }
  
  function publicarPonto($ponto_id) {
  	$ponto = $this->buscaPontosClientes(null, $ponto_id);
  	if ($ponto) {
//			if ($ponto["empresarial"]) {
    		$endereco_interface = $this->trataEnderecoInterface($this->executar("/ip address print without-paging where interface={$ponto["interface"]}"));
    		$target_address = "{$endereco_interface["rede"]}/{$endereco_interface["classe"]}";
    		//$pontos_arp = $this->trataArpPontos($this->executar("/ip arp print detail without-paging where mac-address={$ponto["mac"]}"));
	    	$pontos_queue = $this->trataQueuePontos($this->executar("/queue simple print detail without-paging where target-addresses={$target_address}"));
	    	/*
	    	// Insere/atualiza arp
    		if (!$pontos_arp || $pontos_arp[$ponto["mac"]]["status"] == "D") {
    			$this->executar("/ip arp add address={$ponto["ip"]} mac-address={$ponto["mac"]} interface={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
    		} else {
					if ($ponto["comentario"] != $pontos_arp[$ponto["mac"]]["comentario"] ||
					    $ponto["interface"] != $pontos_arp[$ponto["mac"]]["interface"] ||
					    $ponto["ip"] != $pontos_arp[$ponto["mac"]]["ip"] ||
					    $ponto["bloqueado"] != $pontos_arp[$ponto["mac"]]["bloqueado"]) {
		    		$this->executar("/ip arp set [/ip arp find address={$ponto["ip"]}] interface={$ponto["interface"]} address={$ponto["ip"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
					}
    		} */
    		// Insere/atualiza queue
    		if (!$pontos_queue) {
	    		$this->executar("/queue simple add target-addresses={$target_address} max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$ponto["interface"]} name=\"cliente{$ponto["cliente_id"]}\" comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
    		} else {
					if ($ponto["comentario"] != $pontos_queue[$target_address]["comentario"] ||
					    $ponto["download"] != $pontos_queue[$target_address]["max-limit-download"] ||
					    $ponto["upload"] != $pontos_queue[$target_address]["max-limit-upload"] ||
					    $ponto["bloqueado"] != $pontos_queue[$target_address]["bloqueado"]) {
		    		$this->executar("/queue simple set [/queue simple find target-addresses={$target_address}] max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$ponto["interface"]} name=\"cliente{$ponto["cliente_id"]}\" comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
					}
    		}
//  		} else {
//  			$erro = $this->verificaPool($ponto["interface"], $this->buscaPontosClientes($ponto["interface_id"]));
//		    if ($erro) {
//		    	return $erro;
//		    }
//		    $erro = $this->verificaDHCPServer($ponto["dhcp_server"]);
//		    if ($erro) {
//		    	return $erro;
//		    }
//		    $pontos_lease = $this->trataLeasePontos($this->executar("/ip dhcp-server lease print detail without-paging where mac-address={$ponto["mac"]}"));
//    		// Atualiza IP
//    		if ($pontos_lease[$ponto["mac"]]["address"] != $ponto["ip"] && in_array($pontos_lease[$ponto["mac"]]["address"], $this->pool[$ponto["interface"]])) {
//    			$ponto["ip"] = $pontos_lease[$ponto["mac"]]["address"];
//    			unset($this->pool[$ponto["interface"]][array_search($ponto["ip"], $this->pool[$ponto["interface"]])]);
//    			$this->atualizaPontoIP($ponto["id"], $ponto["ip"]);
//    		} elseif (!$ponto["ip"] || !in_array($ponto["ip"], $this->pool[$ponto["interface"]])) {
//    			$ponto["ip"] = array_shift($this->pool[$ponto["interface"]]);
//    			$this->atualizaPontoIP($ponto["id"], $ponto["ip"]);
//    		} else {
//    			unset($this->pool[$ponto["interface"]][array_search($ponto["ip"], $this->pool[$ponto["interface"]])]);
//    		}
//		    // Insere/atualiza lease
//		    if (!$pontos_lease) {
//					$this->executar("/ip dhcp-server lease add server={$ponto["dhcp_server"]} address={$ponto["interface"]} mac-address={$ponto["mac"]} comment=\"{$ponto["comentario"]}\" rate-limit={$ponto["upload"]}/{$ponto["download"]} block-access=no disabled={$ponto["bloqueado"]}");
//			  } else {
//  				if ($ponto["comentario"] != $pontos_lease[$ponto["mac"]]["comentario"] ||
//					    $ponto["download"] != $pontos_lease[$ponto["mac"]]["download"] ||
//					    $ponto["upload"] != $pontos_lease[$ponto["mac"]]["upload"] ||
//					    $ponto["bloqueado"] != $pontos_lease[$ponto["mac"]]["bloqueado"]) {
//	    			$this->executar("/ip dhcp-server lease set [/ip dhcp-server lease find mac-address={$ponto["mac"]}] server={$ponto["dhcp_server"]} address={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" rate-limit={$ponto["upload"]}/{$ponto["download"]} block-access=no disabled={$ponto["bloqueado"]}");
//					}
//			  }
//			  /*
//    		// Insere/atualiza arp
//    		if (!$pontos_arp) {
//    			$this->executar("/ip arp add address={$ponto["ip"]} mac-address={$ponto["mac"]} interface={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
//    		} else {
//					if ($ponto["comentario"] != $pontos_arp[$ponto["mac"]]["comentario"] ||
//					    $ponto["ip"] != $pontos_arp[$ponto["mac"]]["ip"] ||
//					    $ponto["bloqueado"] != $pontos_arp[$ponto["mac"]]["bloqueado"]) {
//		    		$this->executar("/ip arp set [/ip arp find mac-address={$ponto["mac"]}] address={$ponto["ip"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
//					}
//    		} */
//  		}
  	}
  }
    
  function verificaPool($interface, &$pontos) {
    $retorno = $this->executar("/ip pool print detail without-paging where name=\"$interface\"");
    if ($retorno === false) {
		  return "Desconectado do servidor";
    } else {
    	$pool = $this->trataPool($retorno);
    	if ($pool) {
	    	$ip = ip2long($pool["ip_inicial"]);
	    	$ip_final = ip2long($pool["ip_final"]);
	    	while ($ip <= $ip_final) {
	    		$this->pool[$interface][] = long2ip($ip);
	    		$ip++;
	    	}
	    	foreach ($pontos as $ponto) {
	    		if ($ponto["ip"] && array_key_exists($ponto["ip"], $this->pool[$interface])) {
    				unset($this->pool[$interface][array_search($ponto["ip"], $this->pool[$interface])]);
	    		}
	    	}
    	} else {
			  return "Pool [$interface] não encontrado";
    	}
    }
  }
    
  function verificaDHCPServer($dhcp_server) {
    $retorno = $this->executar("/ip dhcp-server print detail without-paging where name=\"$dhcp_server\"");
    if ($retorno === false) {
		  return "Desconectado do servidor";
    } elseif (strrpos($retorno, $dhcp_server) === false) {
		  return "DHCP Server [$dhcp_server] não encontrado";
    }
  }
  
  function trataEnderecoInterface($retorno) {
  	if (preg_match('/[0-9]+\s+([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)\/([0-9]+)\s+([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $retorno, $matches)) {
	  	$interface["ip"] = $matches[1];
	  	$interface["classe"] = $matches[2];
	  	$interface["rede"] = $matches[3];
	  	return $interface;
  	}
  }
   
  function trataPool($retorno) {
  	if (preg_match('/[0-9]+\s+name=\"([^\"]+)\"\s+ranges=([0-9\.]+)(-([0-9\.]+))?/', $retorno, $matches)) {
	  	$pool["name"] = $matches[1];
	  	$pool["ip_inicial"] = $matches[2];
	  	$pool["ip_final"] = $matches[4] ? $matches[4] : $matches[2];
	  	return $pool;
  	}
  }
   
  function trataLeasePontos($retorno) {
  	$pontos = null;
  	preg_match_all('/[0-9]+\s([XRDB ])\s+;+\s+([^=]*)\s+address=([^\s]+)\s+mac-address=([^\s]+)\s+rate-limit="([^\/]+)\/([^"]+)"\s+server=([^\s]+)/', $retorno, $matches);
    for ($i = 0; $i < count($matches[1]); $i++) {
	    $pontos[$matches[4][$i]]["status"] = $matches[1][$i];
	    $pontos[$matches[4][$i]]["comentario"] = trim(str_replace(array("\r", "\n"), "", $matches[2][$i]));
	    $pontos[$matches[4][$i]]["address"] = $matches[3][$i];
	    $pontos[$matches[4][$i]]["mac"] = $matches[4][$i];
	    $pontos[$matches[4][$i]]["upload"] = $matches[5][$i];
	    $pontos[$matches[4][$i]]["download"] = $matches[6][$i];
	    $pontos[$matches[4][$i]]["server"] = $matches[7][$i];
	    $pontos[$matches[4][$i]]["bloqueado"] = $matches[1][$i] == "X" || $matches[1][$i] == "B" ? "yes" : "no";
    }
    return $pontos;
  }
  
  function trataArpPontos($retorno) {
  	$pontos = null;
  	preg_match_all('/[0-9]+\s([XIHD ])\s*;*\s*([^=]*)\s+address=([^\s]+)\s+mac-address=([^\s]+)\s+interface=([^\s]+)/', $retorno, $matches);
    for ($i = 0; $i < count($matches[1]); $i++) {
	    $pontos[$matches[4][$i]]["status"] = $matches[1][$i];
	    $pontos[$matches[4][$i]]["comentario"] = trim(str_replace(array("\r", "\n"), "", $matches[2][$i]));
	    $pontos[$matches[4][$i]]["ip"] = $matches[3][$i];
	    $pontos[$matches[4][$i]]["mac"] = $matches[4][$i];
	    $pontos[$matches[4][$i]]["interface"] = $matches[5][$i];
	    $pontos[$matches[4][$i]]["bloqueado"] = $matches[1][$i] == "X" ? "yes" : "no";
    }
    return $pontos;
  }
    
  function trataQueuePontos($retorno) {
  	$pontos = null;
    preg_match_all('/[0-9]+\s([XID ])\s*;*\s*([^=]*)\s+name="(.*)"\s+target-addresses=([^\s]+)\s+interface=([^\s]+)\s+parent=([^\s]+)\s+direction=([^\s]+)\s+priority=([^\s]+)\s+queue=([^\s]+)\s+limit-at=([^\/]+)\/([^\s]+)\s+max-limit=([^\/]+)\/([^\s]+)\s+burst-limit=([^\/]+)\/([^\s]+)\s+burst-threshold=([^\/]+)\/([^\s]+)\s+burst-time=([^\/]+)\/([^\s]+)\s+total-queue=([^\s]+)/', $retorno, $matches);
  	for ($i = 0; $i < count($matches[1]); $i++) {
  		if ($pontos && array_key_exists($matches[4][$i], $pontos)) {
  			$matches[4][$i] .= $matches[3][$i];
  		}
	    $pontos[$matches[4][$i]]["status"] = $matches[1][$i];
	    $pontos[$matches[4][$i]]["comentario"] = trim(str_replace(array("\r", "\n"), "", $matches[2][$i]));
	    $pontos[$matches[4][$i]]["nome"] = $matches[3][$i];
	    $pontos[$matches[4][$i]]["rede"] = $matches[4][$i];
	    $pontos[$matches[4][$i]]["interface"] = $matches[5][$i];
	    $pontos[$matches[4][$i]]["parent"] = $matches[6][$i];
	    $pontos[$matches[4][$i]]["direction"] = $matches[7][$i];
	    $pontos[$matches[4][$i]]["priority"] = $matches[8][$i];
	    $pontos[$matches[4][$i]]["queue"] = $matches[9][$i];
	    $pontos[$matches[4][$i]]["limit-at-upload"] = $matches[10][$i];
	    $pontos[$matches[4][$i]]["limit-at-download"] = $matches[11][$i];
	    $pontos[$matches[4][$i]]["max-limit-upload"] = $matches[12][$i];
	    $pontos[$matches[4][$i]]["max-limit-download"] = $matches[13][$i];
	    $pontos[$matches[4][$i]]["burst-limit-upload"] = $matches[14][$i];
	    $pontos[$matches[4][$i]]["burst-limit-download"] = $matches[15][$i];
	    $pontos[$matches[4][$i]]["burst-threshold-upload"] = $matches[16][$i];
	    $pontos[$matches[4][$i]]["burst-threshold-download"] = $matches[17][$i];
	    $pontos[$matches[4][$i]]["burst-time-upload"] = $matches[18][$i];
	    $pontos[$matches[4][$i]]["burst-time-download"] = $matches[19][$i];
	    $pontos[$matches[4][$i]]["total-queue"] = $matches[20][$i];
    	$pontos[$matches[4][$i]]["bloqueado"] = $matches[1][$i] == "X" ? "yes" : "no";
    }
    return $pontos;
  }
    
	###############
  ### Conexão ###
  ###############

  function testaSSH($servidor_ip) {
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
  		if (ssh2_auth_password($this->conexao, $this->usuario, $this->senha)) {
        return "";
      } else {
        return "Erro de autenticação no servidor";
      }
    } else {
      return "Erro ao conectar-se no servidor";
    }
  }
  
  function executar($comando, $retorno = true) {
    if ($this->conexao) {
      if ($_GET["debug"]) {
        print $comando."\n"; flush();
      }
      $stream = ssh2_exec($this->conexao, $comando);
      if ($retorno) {
//        $stream_error = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
  			stream_set_blocking($stream, true);
//        stream_set_blocking($stream_error, true);
        stream_set_timeout($stream, 600);
//        stream_set_timeout($stream_error, 600);
//        while (($linha = fgets($stream_error)) != false) {
//          $linhas[] = $linha;
//        }
        while (($linha = fgets($stream)) != false) {
          $linhas[] = $linha;
        }
        fclose($stream);
//        fclose($stream_error);
        return implode("\n", $linhas);
      }
    }
  }

  ######################
  ### Banco de Dados ###
  ######################

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
      return $dados;
    }
  }
  
  function buscaInterfaces($servidor_id) {
    $query = "
      SELECT DISTINCT
        tab_servidores_interfaces.sei_id AS id,
        tab_servidores_interfaces.sei_nome AS nome
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores_servicos on (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_servidores_interfaces.sia_id = 1 AND
        tab_servidores_servicos.sst_id = 16";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$interfaces[] = $dados;
    }
    return $interfaces;
  }
  
  function atualizaPontoIP($ponto_id, $ip) {
  	$query = "UPDATE tab_clientes_pontos SET clp_ip = '$ip' WHERE clp_id = $ponto_id";
  	$this->bancodedados->query($query);
  }
  
  function buscaPontosClientes($interface_id = null, $ponto_id = null) {
  	$pontos = null;
    $query = "
      SELECT DISTINCT
        tab_clientes_pontos.clp_id AS id,
        tab_clientes_pontos.clp_ip AS ip,
      	tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_clientes_pontos.clp_velocidade_upload AS upload,
        tab_clientes_pontos.clp_velocidade_download AS download,
        tab_clientes_pontos.clp_ajustado AS ajustado,
        tab_clientes_pontos.cps_id AS status_id,
        tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
        tab_clientes_pontos.clp_regra AS regra,
        tab_clientes_pontos.sei_id AS interface_id,
        tab_clientes.cli_id AS cliente_id,
        CONCAT('[', tab_clientes.cli_id, '] ', tab_clientes.cli_nome, IF(tab_clientes.con_id IS NOT NULL, CONCAT(' (', tab_condominios.con_nome, IF(tab_condominios_blocos.cob_nome = 'Único' OR tab_clientes.cob_id IS NULL, IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', CONCAT(', ', SUBSTRING(tab_clientes.cli_complemento, 2)), ''), CONCAT(', ', IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', CONCAT(SUBSTRING(tab_clientes.cli_complemento, 2), ' / '), ''), tab_condominios_blocos.cob_nome)), ')'), '')) AS comentario,
        tab_servidores_interfaces.sei_nome AS interface,
        IF(tab_planos.pls_id IN (3, 36) AND tab_clientes_pontos.clp_ip IS NOT NULL, 1, 0) AS empresarial
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores_servicos on (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
      WHERE".($ponto_id ? "
        tab_clientes_pontos.clp_id = $ponto_id" : "
        tab_servidores_interfaces.sei_id = $interface_id")." AND
        tab_servidores_servicos.sst_id = 16 AND
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
      $dados["upload"] .= "k";
      $dados["download"] .= "k";
      $dados["mac"] = strtoupper($dados["mac"]);
      //if ($dados["empresarial"]) {
	    //  $dados["rede"] = $dados["ip"]."/30";
	    //  $dados["ip"] = explode(".", $dados["ip"]);
	    //  $dados["ip"] = $dados["ip"][0].".".$dados["ip"][1].".".$dados["ip"][2].".".($dados["ip"][3] + 2);
      //}
      $dados["dhcp_server"] = "dhcp_{$dados["interface"]}";
      $dados["status"] = $dados["status_id"] != 1 || !$dados["upload"] || !$dados["download"] || ($dados["ajustado"] && (!$dados["parametros_ajuste"]["upload"] || !$dados["parametros_ajuste"]["download"])) ? 0 : 1;
      $dados["bloqueado"] = $dados["status"] ? "no" : "yes";
      if ($ponto_id) {
      	return $dados;
      } else {
      	$pontos[] = $dados;
      }
    }
    return $pontos;
  }

  function limpaNome($nome) {
    $nome = preg_replace("/[ñ]/", "n", $nome);
    $nome = preg_replace("/[Ñ]/", "N", $nome);
    $nome = preg_replace("/[ç]/", "c", $nome);
    $nome = preg_replace("/[Ç]/", "C", $nome);
    $nome = preg_replace("/[áàâãäª]/", "a", $nome);
    $nome = preg_replace("/[ÁÀÂÃÄ]/", "A", $nome);
    $nome = preg_replace("/[éèêë]/", "e", $nome);
    $nome = preg_replace("/[ÉÈÊË]/", "E", $nome);
    $nome = preg_replace("/[íìîï]/", "i", $nome);
    $nome = preg_replace("/[ÍÌÎÏ]/", "I", $nome);
    $nome = preg_replace("/[óòôõö°º]/", "o", $nome);
    $nome = preg_replace("/[ÓÒÔÕÖ]/", "O", $nome);
    $nome = preg_replace("/[úùûü]/", "u", $nome);
    $nome = preg_replace("/[ÚÙÛÜ]/", "U", $nome);
    $nome = preg_replace("/[^a-zA-Z0-9\\-_.\\/(),\\[\\] ]/", "", $nome);
    $nome = trim($nome);
    return $nome;
  }
  
}

?>
