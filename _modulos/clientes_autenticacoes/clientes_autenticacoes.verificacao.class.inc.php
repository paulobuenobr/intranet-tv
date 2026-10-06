<?

set_time_limit(0);

if ($_GET["teste"] == 1) {

	require_once("../../global.inc.php");
	$publicacao = controle::instanciaObjeto("clientes_autenticacoes.verificacao", $bancodedados);
	var_dump($publicacao->verificarConexao("192.168.58.3", "192.168.58.2", 2, "ABC"));

}

class clientes_autenticacoes_verificacao {
	
	var $conexao_ssh;
	var $conexao_ssh_ap;
	var $reiniciar = false;
	
	function clientes_autenticacoes_verificacao($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function testaSSH($servidor_ip) {
		
		$comando = "/usr/local/nagios/libexec/check_ssh -t 3 $servidor_ip";
		$retorno = exec($comando);
		
		return substr($retorno, 0, 6) == "SSH OK";
		
	}
	
	function conectarSSH(&$conexao, $ip, $usuario = null, $senha = null) {
		
		$usuario = $usuario ? $usuario : "root";
		$senha = $senha ? $senha : "KaL4Br354,";
		
		if (!$this->testaSSH($ip)) {
			return "Erro de SSH";
		}
		
		$conexao = ssh2_connect($ip, 22);
		
		if ($conexao) {
			if (ssh2_auth_password($conexao, $usuario, $senha)) {
				return "";
			} else {
				return "Erro de autenticação em ($ip)";
			}
		} else {
			return "Erro ao conectar-se em ($ip)";
		}
		
	}
	
	function executarSSH(&$conexao, $comando) {
		
		if ($conexao) {
			
			$linhas = array();
			$stream = ssh2_exec($conexao, $comando);
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
		
	#########################
	### Verificar Conexão ###
	#########################
	
	function verificarConexao_NEW($autenticacao_id, $equipamento_ip, $ap_ip) {
		
		if ($autenticacao_id) {
			$dados = $this->buscaDadosAutenticacao($autenticacao_id);
		}
		
		// Senhas possíveis
		$senhas[] = array("usuario" => "ubnt", "senha" => "cbcftm");
		$senhas[] = array("usuario" => "ubnt", "senha" => "vagabunda");
		$senhas[] = array("usuario" => "ubnt", "senha" => "ppca24");
		$senhas[] = array("usuario" => "ubnt", "senha" => "ppcA24!,");
		$senhas[] = array("usuario" => "root", "senha" => "cbcftm");
		$senhas[] = array("usuario" => "admin", "senha" => "vagabunda");
		$senhas[] = array("usuario" => "admin", "senha" => "elr3OII,");
		$senhas[] = array("usuario" => "admin", "senha" => "cbcftm");
		
		// Conexão com o equipamento
		$retorno = false;
		for ($i = 0; $i < count($senhas) && $retorno !== true; $i++) {
			$retorno = $this->conectarSSH($this->conexao_equipamento, $equipamento_ip, $senhas[$i]["usuario"], $senhas[$i]["senha"]);
		}
		
		if ($retorno !== true) {
			return array("erro" => $retorno);
		}
		
		// Conexão com a AP
		$retorno = false;
		for ($i = 0; $i < count($senhas) && $retorno !== true; $i++) {
			$retorno = $this->conectarSSH($this->conexao_ap, $ap_ip, $senhas[$i]["usuario"], $senhas[$i]["senha"]);
		}
		if ($retorno !== true) {
			return array("erro" => $retorno);
		}
		
		// Busca dados equipamento
		$equipamento = $this->buscaDadosEquipamento();
		
		// Busca dados AP
		for ($i = 0; $i < 3; $i++) {
			$resultado = $this->executaTesteSinal();
			if (is_array($resultado)) {
				$verificacoes[] = $resultado;
			} else {
				return array("erro" => $resultado);
			}
		}
		
	}

	function verificarConexao($ip, $ap_ip, $ap_tipo_id, $nome_equipamento) {
		
		if ($ap_tipo_id != 3) {
			// Equipamento do cliente
			$erro = $this->conectarSSH($this->conexao_ssh, $ip, "ubnt", "cbcftm");
			if ($erro) {
				$erro = $this->conectarSSH($this->conexao_ssh, $ip, "ubnt", "vagabunda");
				if ($erro) {
					$erro = $this->conectarSSH($this->conexao_ssh, $ip, "ubnt", "ppca24");
					if ($erro) {
						$erro = $this->conectarSSH($this->conexao_ssh, $ip, "root", "cbcftm");
						if ($erro) {
							$erro = $this->conectarSSH($this->conexao_ssh, $ip, "admin", "vagabunda");
							if ($erro) {
								$erro = $this->conectarSSH($this->conexao_ssh, $ip, "admin", "cbcftm");
								if ($erro) {
									$erro = $this->conectarSSH($this->conexao_ssh, $ip, "ubnt", "ppcA24!,");
									if ($erro) {
										return array("erro" => $erro);
									}
								}
							}
						}
					}
				}
			}
			// AP
			$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "root", "medsocdws!");
			if ($erro) {
				$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "ubnt", "cbcftm");
				if ($erro) {
					$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "ubnt", "vagabunda");
					if ($erro) {
						$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "ubnt", "ppca24");
						if ($erro) {
							$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "admin", "vagabunda");
							if ($erro) {
								$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "admin", "M0rT4d3l41");
								if ($erro) {
									$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "admin", "cbcftm");
									if ($erro) {
										$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "admin", "ppca24");
										if ($erro) {
											$erro = $this->conectarSSH($this->conexao_ssh_ap, $ap_ip, "ubnt", "ppcA24!,");
											if ($erro) {
												return array("erro" => $erro);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		$this->reiniciar = false;
		
		// Executa 3 verificações
		for ($i = 0; $i < 3; $i++) {
			
			$dados = $this->executarVerificacao($ip, $ap_ip, $ap_tipo_id, $nome_equipamento);
		
			if ($this->reiniciar) {
				$this->executarSSH($this->conexao_ssh, "reboot");
			}
			
			if (is_array($dados)) {
				$verificacoes[] = $dados;
			} else {
				return array("erro" => $dados);
			}
			
			sleep(2);
			
		}
		
		return $verificacoes;
		
	}
	
	function executarVerificacao($cliente_ip, $ap_ip, $ap_tipo_id, $nome_equipamento) {
		
		$dados = array();
		
		if ($ap_tipo_id == 3) {
			$dados["platform"] = "Canopy";
			$contents = file_get_contents("http://{$cliente_ip}/main.cgi");
			if (preg_match("/<span class='var int' id='PowerLevel'>\\s*([^\\s]+)\\s*dBm\\s*<\\/span>/mi", $contents, $matches)) {
				$dados["signal"] = $matches[1];				
			}
			if (preg_match("/<tr><td class='sectionlefttop'>Site Name :<\\/td>\\s*<td class='sectionrighttop'>\\s*([^\\s]*)\\s*<\\/td><\\/tr>/mi", $contents, $matches)) {
				$dados["devicename"] = $matches[1];
			} elseif (preg_match("/<span class='var str' id='SiteNameStats'>\\s*(.*)\\s*<\\/span>/mi", $contents, $matches)) {
				$dados["devicename"] = trim($matches[1]);
			}
			if (preg_match("/Subscriber Module\\s*-\\s*([0-9a-f]{2}-[0-9a-f]{2}-[0-9a-f]{2}-[0-9a-f]{2}-[0-9a-f]{2}-[0-9a-f]{2})/mi", $contents, $matches)) {
			
				$mac = $matches[1];
						
				$this->curl_start() ;
				
				$retorno = $this->curl_post("http://{$ap_ip}/login.cgi", null, array("CanopyUsername" => "admin", "CanopyPassword" => "ppca24"));
				
				if (preg_match("/name=\"mac_esn\" value=\"([^\"]+)\"/", $retorno, $matches)) {
					$mac_esn = $matches[1];
				}
				if (preg_match("/name=\"Session\" value=\"([^\"]+)\"/", $retorno, $matches)) {
					$session = $matches[1];
				}
				
				$retorno = file_get_contents("http://{$ap_ip}/main.cgi?mac_esn={$mac_esn}&Session={$session}&catindex=0&pageindex=2");

				if (preg_match("/<a href=\"http:\\/\\/{$ap_ip}:1080\\/\\?mac_esn=[0-9a-f]+\"><b>LUID: [0-9]+<\\/b> : MAC: {$mac}<\\/a>  State: IN SESSION \\(Encrypt Disabled\\)<br \\/>&nbsp; &nbsp; &nbsp; Site Name : [^<]*<br \\/>&nbsp; &nbsp; &nbsp; Software Version : [^<]*<br \\/>&nbsp; &nbsp; &nbsp; Software Boot Version : [^<]*<br \\/>&nbsp; &nbsp; &nbsp; FPGA Version : [0-9]* \\(DES,  Sched, US\\/ETSIv1.3.1\\) P10<br \\/>&nbsp; &nbsp; &nbsp; Session Timeout: [0-9]+, AirDelay [0-9]+ \\(approximately [0-9.]+ miles \\([0-9]+ feet\\)\\)<br \\/>&nbsp; &nbsp; &nbsp; Session Count: [0-9]+, Reg Count [0-9]+, Re-Reg Count [0-9]+<br \\/>&nbsp; &nbsp; &nbsp; RSSI \\(Avg\\/Last\\): [0-9]+\\/[0-9]+ &nbsp; Jitter \\(Avg\\/Last\\): [0-9]+\\/[0-9]+ &nbsp; Power Level \\(Avg\\/Last\\):  [\\-0-9]+\\/([\\-0-9]+)<br \\/>/im", $retorno, $matches)) {
					$dados["apsignal"] = $matches[1];
				}
				
				$dados["mac"] = str_replace("-", ":", $mac);
				
			}
			
			return $dados;
			
		}
		
		$retorno = trim($this->executarSSH($this->conexao_ssh, "hostname"));
		if (preg_match("/^APC/", $retorno)) {
			// Intelbras
			$dados["platform"] = $retorno;
			$retorno = trim($this->executarSSH($this->conexao_ssh, "cat /tmp/system.cfg | grep snmpd.name"));
			if (preg_match("/^snmpd\\.name *\\= *(.*)/", $retorno, $matches)) {
				$dados["devicename"] = $matches[1];
				if ($dados["devicename"] != $nome_equipamento) {
					$this->executarSSH($this->conexao_ssh, "sed -i 's/snmpd.name *= *{$dados["devicename"]}/snmpd.name = {$nome_equipamento}/g' /tmp/system.cfg");
					$this->executarSSH($this->conexao_ssh, "sysconf -w /tmp/system.cfg");
					$this->reiniciar = true;
				}
			}
			$retorno = $this->executarSSH($this->conexao_ssh, "iwconfig");
			if (!preg_match("/Link Quality=([^\\s]*)\\s*Signal level:([^\\s]*)\\s*Signal Noise:([^\\s]*)/i", $retorno, $matches)) {
				preg_match("/Link Quality=([^\\s]*)\\s*Signal level:([^\\s]*)\\s*dBm\\s*Noise level:([^\\s]*)/i", $retorno, $matches);
			}
			$dados["quality"] = $matches[1];
			$dados["signal"] = $matches[2];
			$dados["noise"] = $matches[3];
			$dados["snr"] = $dados["signal"] - $dados["noise"];
			$retorno = $this->executarSSH($this->conexao_ssh, "ifconfig");
			if (preg_match("/ra0\\s+Link encap:Ethernet\\s+HWaddr\\s+([a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2})/i", $retorno, $matches)) {
				$dados["mac"] = $matches[1];
			}
		} else {
			$retorno = $this->executarSSH($this->conexao_ssh, "cat /etc/product | grep productName");
			if (preg_match("/\"productName\":\"(APC 5A-20)\"/", $retorno, $matches)) {
				// Intelbras APC 5A-20
				$dados["platform"] = $matches[1];
				$retorno = json_decode($this->executarSSH($this->conexao_ssh, "cat /tmp/config.json"), true);			
				if ($retorno != null) {
					$dados["devicename"] = $retorno["system"]["device"]["name"];
					if ($dados["devicename"] != $nome_equipamento) {
						$this->executarSSH($this->conexao_ssh, "sed -i '/./{H;$!d} ; x ; s/\\(\"device\":\\s*{\\s*\"firmwareid\":\\s*\"[^\"]*\",\\s*\"name\":\\s*\"\\)[^\"]*\\(\"\\)/\\1{$nome_equipamento}\\2/g' /tmp/config.json");
						$this->executarSSH($this->conexao_ssh, "sysconf -w /tmp/config.json");
						$this->reiniciar = true;
					}
				}
				$retorno = $this->executarSSH($this->conexao_ssh, "iwconfig");
				if (!preg_match("/Link Quality=([^\\s]*)\\s*Signal level[:=]([^\\s]*)\\s*Signal Noise[:=]([^\\s]*)/i", $retorno, $matches)) {
					preg_match("/Link Quality=([^\\s]*)\\s*Signal level[:=]([^\\s]*)\\s*dBm\\s*Noise level[:=]([^\\s]*)/i", $retorno, $matches);
				}
				$dados["quality"] = $matches[1];
				$dados["signal"] = $matches[2];
				$dados["noise"] = $matches[3];
				$dados["snr"] = $dados["signal"] - $dados["noise"];
				$retorno = $this->executarSSH($this->conexao_ssh, "ifconfig");
				if (preg_match("/ath0\\s+Link encap:Ethernet\\s+HWaddr\\s+([a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2})/i", $retorno, $matches)) {
					$dados["mac"] = $matches[1];
				}
			} else {
				$retorno = $this->executarSSH($this->conexao_ssh, "mca-status");
				if (preg_match("/mca-status: not found/", $retorno)) {
					// Intelbras
					$retorno = $this->executarSSH($this->conexao_ssh, "iwconfig");
					if (!preg_match("/Link Quality=([^\\s]*)\\s*Signal level:([^\\s]*)\\s*Signal Noise:([^\\s]*)/i", $retorno, $matches)) {
						preg_match("/Link Quality=([^\\s]*)\\s*Signal level:([^\\s]*)\\s*dBm\\s*Noise level:([^\\s]*)/i", $retorno, $matches);
					}
					$dados["platform"] = "WOM 5000 Intelbras";
					$dados["quality"] = $matches[1];
					$dados["signal"] = $matches[2];
					$dados["noise"] = $matches[3];
					$dados["snr"] = $dados["signal"] - $dados["noise"];
					$retorno = $this->executarSSH($this->conexao_ssh, "ifconfig");
					if (preg_match("/ra0\\s+Link encap:Ethernet\\s+HWaddr\\s+([a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2})/i", $retorno, $matches)) {
						$dados["mac"] = $matches[1];
					}
				} else {
					// Ubiquit
					preg_match_all("/([a-z0-9_]+)=([^\r\n,]+)[\r\n,]+/i", $retorno, $matches);
					for ($i = 0; $i < count($matches[1]); $i++) {
						$dados[strtolower($matches[1][$i])] = $matches[2][$i];
					}
					preg_match("/deviceId=([a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2})/i", $retorno, $matches);
					$dados["mac"] = $matches[1];
					$dados["ccq"] = $dados["ccq"] / 10;
				}
			}
		}
		
		// AP Data
		
		$retorno = $this->executarSSH($this->conexao_ssh_ap, "cat /etc/product | grep productName");
		if (preg_match("/\"productName\":\"(APC 5A-20)\"/", $retorno)) {
			
			// Intelbras APC 5A-20
			$retorno = json_decode($this->executarSSH($this->conexao_ssh_ap, "cat /var/run/stats/wireless.json"), true);
			
			foreach ($retorno["peers"] as $peer) {
				if ($peer["mac"] == $dados["mac"]) {
					$dados["apsignal1"] = $peer["signal"][0];
					$dados["apsignal2"] = $peer["signal"][1];
					$dados["apccq"] = $peer["rxCcqPercent"];					
					return $dados;
				}
			}
			
			return "Cliente não encontrado na AP informada";

		} else {
			
			$retorno = trim($this->executarSSH($this->conexao_ssh_ap, "hostname"));
			if (preg_match("/^APC/", $retorno)) {
				
				$retorno = $this->executarSSH($this->conexao_ssh_ap, "more /tmp/stats/".strtoupper($dados["mac"])."/local");
				
				$retorno = substr($retorno, 8);
				$retorno = preg_replace("/=/", ":", $retorno);
				$retorno = preg_replace("/'/", "\"", $retorno);
				$retorno = preg_replace("/([a-z_0-9]+) :/i", "\"$1\" :", $retorno);
				$retorno = preg_replace("/,(\\s+})/", "$1", $retorno);
				
				$retorno = json_decode($retorno, true);

				$dados["apsignal1"] = $retorno["link_status"]["signal_level"]["antenna0"];
				$dados["apsignal2"] = $retorno["link_status"]["signal_level"]["antenna1"];
				$dados["apnoise"] = $retorno["link_status"]["noise_level"];
				$dados["apsnr1"] = $retorno["link_status"]["SNR"]["snr0"];
				$dados["apsnr2"] = $retorno["link_status"]["SNR"]["snr1"];
				
			} else {
				$retorno = $this->executarSSH($this->conexao_ssh_ap, "interface wireless registration-table print where mac-address={$dados["mac"]}");
				if (preg_match("/{$dados["mac"]}\\s+[^\\s]+\\s+([\\-0-9]+)dBm/i", $retorno, $matches)) {
					// mikrotik
					$dados["apsignal"] = $matches[1];
				} else {
					$retorno = $this->executarSSH($this->conexao_ssh_ap, "wstalist");
					if (preg_match("/wstalist: not found/", $retorno) || preg_match("/Unknown command: 'wstalist'/", $retorno)) {
						$retorno = $this->executarSSH($this->conexao_ssh_ap, "iwlist peers");
						if (preg_match("/Unknown command: 'iwlist'/", $retorno)) {
							// Intelbras
							$retorno = $this->executarSSH($this->conexao_ssh_ap, "cat /proc/net/ra0/peers");
							preg_match("/Peer MAC address\\s+=\\s+{$dados["mac"]}\\s+" . "Connection time\\s+=\\s+([^\\r\\n]*)\\s+" . "Interface\\s+=\\s+([^\\r\\n]*)\\s+" . "Has WDS clients\\s+=\\s+([^\\r\\n]*)\\s+" . "IEEE mode\\s+=\\s+([^\\r\\n]*)\\s+" . "IEEE parameters\\s+=\\s+([^\\r\\n]*)\\s+" . "Capabilities\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx rate\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx MCS\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx packets\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx bytes\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx overruns\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx successful\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx failed\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx retried\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx not retried\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx legacy\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx legacy bytes\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx A-MSDUs\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx A-MSDU bytes\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx packets in A-MSDUs\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx MPDUs\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx MPDU bytes\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx fragmented\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx fragmented bytes\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx packets\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx bytes\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx dropped\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx CCK\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx OFDM\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx HT-Mixed\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx HT-Greenfield\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx A-MSDUs\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx A-MPDUs\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx Short Guard Interval\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx 40MHz bandwidth\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx last RSSI0\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx last RSSI1\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx last RSSI2\\s+=\\s+([^\\r\\n]*)\\s+" . "SNR0\\s+=\\s+([^\\r\\n]*)\\s+" . "SNR1\\s+=\\s+([^\\r\\n]*)\\s+" . "Tx packets per MCS\\s+=\\s+([^\\r\\n]*)\\s+" . "Rx packets per MCS\\s+=\\s+([^\\s]*)/i", $retorno, $matches);
							$dados["apsignal1"] = $matches[36];
							$dados["apsignal2"] = $matches[37];
							$dados["apsnr1"] = $matches[39];
							$dados["apsnr2"] = $matches[40];
						} else {
							preg_match("/{$dados["mac"]}\\s+:\\s+Quality=[^\\s]*\\s+Signal level=([^\\s]*) dBm\\s+Noise level=([^\\s]*) dBm/i", $retorno, $matches);
							$dados["apsignal"] = $matches[1];
							$dados["apnoise"] = $matches[2];
							$dados["apsnr"] = $dados["apsignal"] - $dados["apnoise"];
						}
					} else {
						if (preg_match("/{$dados["mac"]}\\|[0-9]+\\|[0-9\\.\\-]+\\|[0-9\\-\\.]+\\|([0-9\\-\\.]+)\\|([0-9\\-\\.]+)/i", $retorno, $matches)) {
							$dados["apsignal"] = $matches[1];
							$dados["apccq"] = $matches[2] / 10;
						} else {
							// Ubiquit
							$retorno = json_decode($retorno);
							$dados_ap = null;
							$i = 0;
							do {
								if (strtolower($retorno[$i]->mac) == strtolower($dados["mac"])) {
									$dados_ap = $retorno[$i];
								}
								$i++;
							} while ($dados_ap == null && $i < count($retorno));
							
							if (!$dados_ap) {
								return "Cliente não encontrado na AP informada";
							}
							
							$dados["apccq"] = $dados_ap->ccq;
							$dados["apsignal"] = $dados_ap->signal;
						}
					}
				}
					
			}
			
		}
		
		return $dados;
		
	}
	
	/**
	* Send a POST requst using cURL
	* @param string $url to request
	* @param array $post values to send
	* @param array $options for cURL
	* @return string
	*/
	
	var $ch = null;
	
	function curl_start() {
		
		$this->ch = curl_init();
		
	}
	
	function curl_post($url, array $get = null, array $post = null) {
		
		curl_setopt($this->ch, CURLOPT_POST, $post ? true : false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($post)); 
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 4); 
		curl_setopt($this->ch, CURLOPT_URL, $url.(strpos($url, '?') === false ? '?' : '').http_build_query($get));

		if(!($result = curl_exec($this->ch))) {
			trigger_error(curl_error($this->ch));
		}

		return $result;
		
	}
		
}

?>
