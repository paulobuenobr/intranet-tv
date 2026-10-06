<?

set_time_limit(0);
ini_set("default_socket_timeout", 600);

if ($_GET["concentrador"]) {

	date_default_timezone_set('America/Sao_paulo');

	require_once("../../global.inc.php");

	$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao", $bancodedados, true);
	print $publicacao->publicarConcentrador($_GET["concentrador"]);
	
}

class clientes_autenticacoes_publicacao {

	var $conexao_concentrador;
	var $conexao_equipamento;
	var $conexao_ap;

	var $debug = false;

	var $usuario = "root";
	var $senha = "CWMo9t8jCkAm3asFCke";
	var $pool = "pppoe";

	var $concentrador;
	
	function clientes_autenticacoes_publicacao($bancodedados, $debug = false) {
		
		$this->debug = $debug || (array_key_exists("debug", $_GET) && $_GET["debug"]);
		$this->bancodedados = $bancodedados;
		
	}
	
	#######################
	### Liberar Técnico ###
	#######################
	
	function liberar_tecnico($concentrador_ip) {
		
		$erro = $this->conectarSSH($this->conexao_ssh, $concentrador_ip);
		
		if ($erro !== true) {
			return $erro;
		}
		
		$ponto_ativo = $this->executarSSH($this->conexao_ssh, "/ppp active print detail where name=\"hotspot@oletelecom.com.br\"");
		
		if (preg_match("/name[=:]\"hotspot@oletelecom.com.br\"/i", $ponto_ativo)) {
			
			$this->executarSSH($this->conexao_ssh, "/queue simple remove <pppoe-hotspot@oletelecom.com.br>");
			$this->executarSSH($this->conexao_ssh, "/system scheduler remove liberar-tecnico");
			$this->executarSSH($this->conexao_ssh, "/system scheduler add disabled=no interval=1h name=liberar-tecnico on-event=\"/ppp active remove [/ppp active find name=\\\"hotspot@oletelecom.com.br\\\"]\\r\\n/system scheduler remove liberar-tecnico\"");
			$this->executarSSH($this->conexao_ssh, "quit");
			
			return 0;
			
		}
		
		return "Usuário hotspot@oletelecom.com.br não está conectado";
		
	}

	#############################
	### Publicar Concentrador ###
	#############################
	
	function publicarConcentradores($concentradores = null) {

		$query = "
			SELECT
				ser_id AS id,
				ser_nome AS nome
			FROM
				tab_servidores
			WHERE".($concentradores ? "
				ser_id IN ($concentradores)" : "
				set_id = 1 AND
				(SELECT COUNT(*) FROM tab_clientes_autenticacoes WHERE tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id AND tab_clientes_autenticacoes.cas_id IN (1, 2)) > 0")."
			ORDER BY
				ser_data_hora_publicacao ASC";

		$query = $this->bancodedados->query($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$erro = $this->publicarConcentrador($dados["id"]);
			
			if ($this->debug) {
				if ($erro) {
					print "Erro ao publicar concentrador {$dados["nome"]}: $erro\n";
				}
				print "\n\n";
			}
			
		}

	}
		
	#############################
	### Publicar Concentrador ###
	#############################
	
	function publicarConcentrador($servidor_id) {
		
		$this->bancodedados->query("UPDATE tab_servidores SET ser_data_hora_publicacao_inicio = NOW() WHERE ser_id = $servidor_id");
		$this->concentrador = $this->buscaDadosConcentrador($servidor_id);
		if ($this->concentrador) {
			if ($this->debug) {
				print "Publicando [{$this->concentrador["id"]}] {$this->concentrador["nome"]} ({$this->concentrador["ip"]})\n";
			}
			// Conecta no concentrador
    	$retorno = $this->conectarSSH($this->conexao_concentrador, $this->concentrador["ip"]);
    	if ($retorno !== true) {
				$this->bancodedados->query("UPDATE tab_servidores SET ser_data_hora_publicacao_termino = NOW() WHERE ser_id = $servidor_id");
    		return "Erro ao conectar-se no POP {$this->concentrador["nome"]} ({$this->concentrador["ip"]}): $retorno";
    	}
    	// Verifica Permissões
    	if (!$this->verificaPermissoes()) {
    		return "Usuário '$this->usuario' sem permissões no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
    	}
    	// Verifica Pool
    	if (!$this->verificaPool()) {
    		return "Pool '$this->pool' não cadastrado no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
    	}
    	// Atualiza dados Authentication & Accounting
    	$this->executarSSH($this->conexao_concentrador, "/ppp aaa set use-radius=no accounting=yes");
			// Busca profiles
			$tentativas = 0;
    	do {
				$tentativas++;
    		$profiles_quantidade = $this->executarSSH($this->conexao_concentrador, "/ppp profile print count-only");
			} while (!is_numeric($profiles_quantidade) && $tentativas < 3);
			if (!is_numeric($profiles_quantidade)) {
    		return "Erro ao recuperar lista de profiles no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
			}
			if ($profiles_quantidade) {
				$tentativas = 0;
				do {
					$tentativas++;
					$profiles = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp profile print detail without-paging"));
					if (count($profiles) < $profiles_quantidade) {
						print "Lista profiles incompleta\n";
					}
				} while (count($profiles) < $profiles_quantidade && $tentativas < 3);
				if (count($profiles) < $profiles_quantidade && $this->debug) {
    			return "Erro ao recuperar lista de profiles no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
				}
			} else {
				$profiles = array();
			}
			// Busca secrets
			$tentativas = 0;
    	do {
				$tentativas++;
    		$secrets_quantidade = $this->executarSSH($this->conexao_concentrador, "/ppp secret print count-only");
    	} while (!is_numeric($secrets_quantidade) && $tentativas < 3);
			if (!is_numeric($secrets_quantidade)) {
    		return "Erro ao recuperar lista de secrets no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
			}
			if ($secrets_quantidade) {
				$tentativas = 0;
				do {
					$tentativas++;
					$secrets = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp secret print detail without-paging"));
					if (count($secrets) < $secrets_quantidade && $this->debug) {
						print "Lista secrets incompleta\n";
					}
				} while (count($secrets) < $secrets_quantidade && $tentativas < 3);
				if (count($secrets) < $secrets_quantidade) {
    			return "Erro ao recuperar lista de secrets no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
				}
			} else {
				$secrets = array();
			}
			// Busca queues
			$tentativas = 0;
    	do {
				$tentativas++;
				$queues_quantidade = $this->executarSSH($this->conexao_concentrador, "/queue simple print count-only where dynamic=yes");
    	} while (!is_numeric($queues_quantidade) && $tentativas < 3);
			if (!is_numeric($queues_quantidade)) {
    		return "Erro ao recuperar lista de queues no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
			}
			if ($queues_quantidade) {
				$tentativas = 0;
				do {
					$tentativas++;
					$queues = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/queue simple print detail without-paging where dynamic=yes"));
					if (count($queues) < $queues_quantidade && $this->debug) {
						print "Lista de queues incompleta\n";
					}
				} while (count($queues) < $queues_quantidade && $tentativas < 3);
				if (count($queues) < $queues_quantidade) {
    			return "Erro ao recuperar lista de queues no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
				}
			} else {
				$queues = array();
			}
			// Busca ativos
			$tentativas = 0;
    	do {
				$tentativas++;
				$ativos_quantidade = $this->executarSSH($this->conexao_concentrador, "/ppp active print count-only");
    	} while (!is_numeric($ativos_quantidade) && $tentativas < 3);
			if (!is_numeric($ativos_quantidade)) {
    		return "Erro ao recuperar lista de ativos no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
			}
			if ($ativos_quantidade) {
				$tentativas = 0;
				do {
					$tentativas++;
					$ativos = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp active print detail without-paging"));
					if (count($ativos) < $ativos_quantidade && $this->debug) {
						print "Lista ativos incompleta\n";
					}
				} while (count($ativos) < $ativos_quantidade && $tentativas < 3);
				if (count($ativos) < $ativos_quantidade) {
    			return "Erro ao recuperar lista de ativos no concentrador {$this->concentrador["nome"]} ({$this->concentrador["ip"]})";
				}
			} else {
				$ativos = array();
			}
			// Publica pontos
			$autenticacoes = $this->buscaConcentradorAutenticacoes($servidor_id);
			for ($i = 0; $i < count($autenticacoes); $i++) {
				$dados = $this->buscaDadosAutenticacao($autenticacoes[$i]);
				
				$profile = array_key_exists($dados["usuario"], $profiles) ? $profiles[$dados["usuario"]] : null;
				$secret = array_key_exists($dados["usuario"], $secrets) ? $secrets[$dados["usuario"]] : null;
				$queue = array_key_exists("<pppoe-{$dados["usuario"]}>", $queues) ? $queues["<pppoe-{$dados["usuario"]}>"] : null;
				$ativo = array_key_exists($dados["usuario"], $ativos) ? $ativos[$dados["usuario"]] : null;
				
				$this->publicaPonto($dados, $profile, $secret, $queue, $ativo);
				
				if (array_key_exists($dados["usuario"], $profiles)) {
					unset($profiles[$dados["usuario"]]);
				}
				if (array_key_exists($dados["usuario"], $secrets)) {
					unset($secrets[$dados["usuario"]]);
				}
				if (array_key_exists($dados["usuario"], $ativos)) {
					unset($ativos[$dados["usuario"]]);
				}
			}
			// Desconsidera profiles padrão
			if (array_key_exists("default", $profiles)) {
				unset($profiles["default"]);
			}
			if (array_key_exists("default-encryption", $profiles)) {
				unset($profiles["default-encryption"]);
			}
			// Remove secrets remanescentes
			foreach ($secrets as $usuario => $secret) {
				$this->logar("Remove SECRET", $usuario);
    		$this->executarSSH($this->conexao_concentrador, "/ppp secret remove [/ppp secret find name=\"{$usuario}\"]");
			}
			// Remove profiles remanescentes
			foreach ($profiles as $usuario => $profile) {
				$this->logar("Remove PROFILE", $usuario);
				$this->executarSSH($this->conexao_concentrador, "/ppp profile remove [/ppp profile find name=\"{$usuario}\"]");
			}
			// Remove ativos remanescentes
			foreach ($ativos as $usuario => $queue) {
				if ($servidor_id != 680 || ($usuario != "pping" && substr($usuario, 0, 7) != "t-guest")) {
					$this->logar("Remove ACTIVE", $usuario);
					$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$usuario}\"]");
				}
			}
			// Fecha SSH
			$this->executarSSH($this->conexao_concentrador, "/quit");
		}
		$this->bancodedados->query("UPDATE tab_servidores SET ser_publicado = 1, ser_data_hora_publicacao_termino = NOW(), ser_data_hora_publicacao = NOW() WHERE ser_id = $servidor_id");

	}
	
	function logar($acao, $conteudo) {
		
		$file = fopen(DIR_RAIZ."/log/publicacao.log", "a");
		fwrite($file, sprintf("%s %-25.25s %-20.20s %s\r\n", date("d/m/Y H:i"), $this->concentrador["nome"], $acao, $conteudo));
		fclose($file);
		
	}

	#############################
	### Publicar Autenticação ###
	#############################
	
	function publicarAutenticacao($autenticacao_id) {
		$dados = $this->buscaDadosAutenticacao($autenticacao_id);
		if ($dados) {
			$this->concentrador = $this->buscaDadosConcentrador($dados["servidor_id"]);
			// Conecta no concentrador
    	$retorno = $this->conectarSSH($this->conexao_concentrador, $dados["concentrador_ip"]);
    	if ($retorno !== true) {
    		return "Erro ao conectar-se no POP {$dados["concentrador_nome"]} ({$dados["concentrador_ip"]}): $retorno";
    	}
    	// Verifica Permissões
    	if (!$this->verificaPermissoes()) {
    		return "Usuário '$this->usuario' sem permissões no concentrador {$dados["concentrador_nome"]} ({$dados["concentrador_ip"]})";
    	}
    	// Verifica Pool
    	if (!$this->verificaPool()) {
    		return "Pool '$this->pool' não cadastrado no concentrador {$dados["concentrador_nome"]} ({$dados["concentrador_ip"]})";
    	}
    	// Busca profiles
			$profiles = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp profile print detail where name=\"{$dados["usuario"]}\""));
			// Busca secrets
			$secrets = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp secret print detail where name=\"{$dados["usuario"]}\""));
			//Queues
			$queues = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/queue simple print detail where name=\"<pppoe-{$dados["usuario"]}>\""));
			//Ativos
			$ativos = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp active print detail where name=\"{$dados["usuario"]}\""));
			// Publica ponto
			$this->publicaPonto($dados, $profiles[$dados["usuario"]], $secrets[$dados["usuario"]], $queues["<pppoe-{$dados["usuario"]}>"], $ativos[$dados["usuario"]]);
			// Fecha SSH
			$this->executarSSH($this->conexao_concentrador, "/quit");
		}
	}
	
	function publicaPonto($dados, $profile, $secret, $queue, $ativo) {
		
		$profile_updated = $this->publicaProfile($dados, $profile);
		$secret_updated = $this->publicaSecret($dados, $secret);

		if ($ativo) {
			if ($dados["cancelado"]) {
				$this->logar("Remove ACTIVE", "{$dados["usuario"]} (Cancelado)");
				$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$dados["usuario"]}\"]");
			} elseif ($dados["bloqueado"]) {
				if ($queue && $queue["max-limit"] != "1k/1k") {
					$this->logar("Remove ACTIVE", "{$dados["usuario"]} (Bloqueado)");
					$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$dados["usuario"]}\"]");
				}
			} elseif ($secret_updated) {
				$this->logar("Remove ACTIVE", "{$dados["usuario"]} (Secret alterado)");
				$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$dados["usuario"]}\"]");
			} else {
				if (!$queue) {
					$queues = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/queue simple print detail where name=\"<pppoe-{$dados["usuario"]}>\""));
					$queue = $queues["<pppoe-{$dados["usuario"]}>"];
				}
				if (!$queue) {
					$ativos = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp active print detail where name=\"{$dados["usuario"]}\""));
					$ativo = $ativos[$dados["usuario"]];
				}
				if ($ativo) {
					if (!$queue) {
						$this->logar("Remove ACTIVE", "{$dados["usuario"]} (Queue inexistente) SUSPEITO!");
						$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$dados["usuario"]}\"]");
					} elseif ($queue && $queue["max-limit"] == "1k/1k") {
						$this->logar("Remove ACTIVE", "{$dados["usuario"]} (Desbloqueado)");
						$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$dados["usuario"]}\"]");
					} elseif ($queue && $queue["max-limit"] != $dados["velocidade"]) {
						$this->logar("Remove ACTIVE", "{$dados["usuario"]} (Queue diferente: {$queue["max-limit"]} => {$dados["velocidade"]})");
						$this->executarSSH($this->conexao_concentrador, "/ppp active remove [/ppp active find name=\"{$dados["usuario"]}\"]");
					}
				}
			}
		} 
		
		if (!$dados["publicado"]) {
			$this->bancodedados->query("UPDATE tab_clientes_autenticacoes SET cau_publicado = 1 WHERE cau_id = {$dados["id"]}");
		}
		
	}
	
	function publicaProfile($dados, $profile) {
		
		$alterado = false;
		
		if ($dados["cancelado"]) {
			// Se cancelado, remove profile
			$this->logar("Remove PROFILE", "{$dados["usuario"]} (Cancelado)");
			$this->executarSSH($this->conexao_concentrador, "/ppp profile remove [/ppp profile find name=\"{$dados["usuario"]}\"]");
			$alterado = true;
		} elseif (!$profile) {
			// Se não existe, insere registro
			$this->logar("Insere PROFILE", "{$dados["usuario"]}");
			$this->executarSSH($this->conexao_concentrador, "/ppp profile add name={$dados["usuario"]} remote-address={$dados["ip"]} rate-limit={$dados["velocidade"]} only-one=yes comment=\"{$dados["comentario"]}\"");
			$alterado = true;
		} elseif ($profile["remote-address"] != $dados["ip"] || $profile["rate-limit"] != $dados["velocidade"] || $profile["only-one"] != "yes" || $profile["comment"] != $dados["comentario"]) {
			// Se existe e desatualizado, atualiza dados
			$this->logar("Atualiza PROFILE", "{$dados["usuario"]} (IP: '{$profile["remote-address"]}' => '{$dados["ip"]}', Velocidade: '{$profile["rate-limit"]}' => '{$dados["velocidade"]}')");
			$this->executarSSH($this->conexao_concentrador, "/ppp profile set [/ppp profile find name=\"{$dados["usuario"]}\"] remote-address={$dados["ip"]} rate-limit={$dados["velocidade"]} only-one=yes comment=\"{$dados["comentario"]}\"");
			$alterado = $profile["remote-address"] != $dados["ip"] || $profile["rate-limit"] != $dados["velocidade"] || $profile["only-one"] != "yes";
		}
		
		return $alterado;
		
	}
	
	function publicaSecret($dados, $secret) {
		
		$alterado = false;
		
		if ($dados["cancelado"]) {
			// Se cancelado, remove secret
			$this->logar("Remove SECRET", "{$dados["usuario"]} (Cancelado)");
			$this->executarSSH($this->conexao_concentrador, "/ppp secret remove [/ppp secret find name=\"{$dados["usuario"]}\"]");
			$alterado = true;
		} elseif (!$secret) {
			// Se não existe, insere registro
			$this->logar("Insere SECRET", "{$dados["usuario"]}");
			$this->executarSSH($this->conexao_concentrador, "/ppp secret add name={$dados["usuario"]} password={$dados["senha"]} caller-id=\"{$dados["mac"]}\" service=pppoe profile={$dados["usuario"]} comment=\"{$dados["comentario"]}\"");
			if (!$dados["ativo"]) {
				$this->logar("Desativa SECRET", "{$dados["usuario"]}");
				$this->executarSSH($this->conexao_concentrador, "/ppp secret disable [/ppp secret find name=\"{$dados["usuario"]}\"]");
			}
			$alterado = true;
		} else {
			if ($secret["password"] != $dados["senha"] || $secret["caller-id"] != $dados["mac"] || $secret["service"] != "pppoe" || $secret["profile"] != $dados["usuario"] || $secret["comment"] != $dados["comentario"]) {
				// Se existe e desatualizado, atualiza dados
				$this->logar("Atualiza SECRET", "{$dados["usuario"]}");
				$this->executarSSH($this->conexao_concentrador, "/ppp secret set [/ppp secret find name=\"{$dados["usuario"]}\"] password={$dados["senha"]} caller-id=\"{$dados["mac"]}\" service=pppoe profile={$dados["usuario"]} comment=\"{$dados["comentario"]}\"");
				$alterado = $secret["password"] != $dados["senha"] || $secret["caller-id"] != $dados["mac"] || $secret["service"] != "pppoe" || $secret["profile"] != $dados["usuario"];
			}
			if (!$dados["ativo"] && !in_array("X", $secret["flags"])) {
				$this->logar("Desativa SECRET", "{$dados["usuario"]} (Inativo)");
				$this->executarSSH($this->conexao_concentrador, "/ppp secret disable [/ppp secret find name=\"{$dados["usuario"]}\"]");
				$alterado = true;
			} elseif ($dados["ativo"] && in_array("X", $secret["flags"])) {
				$this->logar("Ativa SECRET", "{$dados["usuario"]}");
				$this->executarSSH($this->conexao_concentrador, "/ppp secret enable [/ppp secret find name=\"{$dados["usuario"]}\"]");
				$alterado = true;
			}
		}
		
		return $alterado;
		
	}
	
	function verificaPool() {
		
		$pool = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ip pool print detail where name={$this->pool}"));
		
		return array_key_exists($this->pool, $pool);
		
	}
	
	function verificaPermissoes() {
		
		$permissoes = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/user print detail where name=\"{$this->usuario}\""));
		
		return array_key_exists($this->usuario, $permissoes) && $permissoes[$this->usuario]["group"] == "full";
		
	}
	
	########################
	### Verificar Status ###
	########################
	
	function verificarStatus($autenticacao_id, $icon = false) {
		
		$dados = $this->buscaDadosAutenticacao($autenticacao_id);
		
		if (!$dados["concentrador_ip"]) {
			return "<span style=\"color:red\">POP não definido</span>";
		}
		
		// Conecta no POP
		$retorno = $this->conectarSSH($this->conexao_concentrador, $dados["concentrador_ip"]);
		if ($retorno !== true) {
			return "<span style=\"color:red\">$retorno</span>";
		}
		
		// Busca status do usuario
		$status = $this->extraiRetorno($this->executarSSH($this->conexao_concentrador, "/ppp active print detail where name={$dados["usuario"]}"));
		
		// Desconecta do POP
		$this->executarSSH($this->conexao_concentrador, "/quit");
		
		if ($icon) {
			if ($status && array_key_exists($dados["usuario"], $status)) {
				return "<img src=\"./imagens/icon_on.png\" width=\"10\" height=\"10\">";
			} else {
				return "<img src=\"./imagens/icon_off.png\" width=\"10\" height=\"10\">";
			}
		} else {
			if ($status && array_key_exists($dados["usuario"], $status)) {
				return "<span style=\"color:green\">Online</span> (MAC: <span style=\"color:#232323\">{$status[$dados["usuario"]]["caller-id"]}</span>, IP: <span style=\"color:#232323\">{$status[$dados["usuario"]]["address"]}</span>, Uptime: <span style=\"color:#232323\">{$status[$dados["usuario"]]["uptime"]}</span>).";
			} else {
				return "<span style=\"color:red\">Offline</span>";
			}
		}

	}
	
	###########################
	### Formatação de Dados ###
	###########################
	
	function extraiRetorno($retorno, $key = "name") {
		$retorno = preg_replace('/Flags[^\r\n]+\s*/i', "", $retorno);
		$retorno = preg_split('/\s*\r\n\r\n\s*/', $retorno);
		$estrutura = array();
		for ($i = 0; $i < count($retorno); $i++) {
			$retorno[$i] = trim(preg_replace('/[\s\r\n]+/', " ", $retorno[$i]));
			// Flags
			if (preg_match('/^[0-9]+\s(([\S\s]\s)*)/i', $retorno[$i], $flags)) {
				if (trim($flags[1])) {
					$flags = explode(" ", trim($flags[1]));
				} else {
					$flags = array();
				}
			} else {
				$flags = array();
			}
			// Fields
			$entry = array();
			if (preg_match_all('/([^\s]+)=("[^"]*"|[^\s]*)/i', $retorno[$i], $fields)) {
				for ($j = 0; $j < count($fields[1]); $j++) {
					$entry[$fields[1][$j]] = str_replace('"', '', $fields[2][$j]);
				}
				if (preg_match('/;;; (.+) '.addslashes($fields[1][0]).'='.addslashes($fields[2][0]).'/i', $retorno[$i], $comentario)) {
					$entry["comment"] = $comentario[1];
				} else {
					$entry["comment"] = "";
				}
				if (array_key_exists($key, $entry)) {
					$entry["flags"] = $flags;
					$estrutura[$entry[$key]] = $entry;
				}
			}
		}
		return $estrutura;
	}

	function limpaNome($nome) {
			
		$nome = strtr($nome, "ñÑçÇáàâãäªÁÀÂÃÄéèêëÉÈÊËíìîïÍÌÎÏóòôõö°ºÓÒÔÕÖúùûüÚÙÛÜ", "nncCaaaaaaAAAAAeeeeEEEEiiiiIIIIoooooooOOOOOuuuuUUUU");
		$nome = preg_replace('/[^a-zA-Z0-9\-_\.\/\(\),\[\] ]/', '', $nome);
		return trim($nome);
	
	}
	
	######################
	### Banco de Dados ###
	######################
	
	function buscaDadosAutenticacao($autenticacao_id) {
		$query = "
			SELECT
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_usuario AS usuario,
				tab_clientes_autenticacoes.cau_senha AS senha,
				tab_clientes_autenticacoes.cau_endereco_mac	AS mac,
				tab_clientes_autenticacoes.cau_pool AS pool,
				tab_clientes_autenticacoes.cau_endereco_ip AS ip,
				tab_clientes_autenticacoes.cau_equipamento_ip AS equipamento_ip,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				tab_clientes_autenticacoes.ser_id AS servidor_id,
				tab_servidores.ser_nome AS concentrador_nome,
				CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS concentrador_ip,
				IF(cau_ajustado = 1, cau_ajuste_download, cau_velocidade_download) AS download,
				IF(cau_ajustado = 1, cau_ajuste_upload, cau_velocidade_upload) AS upload,

				IF(cas_id != 1 OR cau_velocidade_download = 0 OR cau_velocidade_upload = 0 OR (cau_ajustado = 1 AND (cau_ajuste_download =  0 OR cau_velocidade_upload = 0)), 1, 0) AS bloqueado,

				IF(cas_id = 3, 1, 0) AS cancelado,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.clt_id AS cliente_tipo_id
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_servidores ON (tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_clientes_autenticacoes.cau_id = $autenticacao_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
//		if ($dados["servidor_id"] == 610)  {
//			$dados["download"] = $dados["download"] > 524288 ? 524288 : $dados["download"];
//			$dados["upload"] = $dados["upload"] > 307200 ? 307200 : $dados["upload"];
//		}

			if ($dados["upload"] < $dados["download"] / 10) {
				$dados["upload"] = intval($dados["download"] / 10);
			}
			
			//if ($dados["upload"] > 0 && $dados["upload"] < 512000) {
			//	$dados["upload"] = 512000;
			//}
			
			if ($dados["upload"] > 0 && $dados["upload"] < 1048576) {
				$dados["upload"] = 1048576;
			}

    	$dados["download"] = $dados["download"] ? intval($dados["download"]/1024)."k" : "1k";
    	$dados["upload"] = $dados["upload"] ? intval($dados["upload"]/1024)."k" : "1k";
    	if ($dados["bloqueado"]) {
	    	$dados["ip"] = "10.10.10.10";
    		$dados["velocidade"] = "1k/1k";
    	} else {
	    	$dados["ip"] = $dados["ip"] ? $dados["ip"] : $dados["pool"];
    		$dados["velocidade"] = "{$dados["upload"]}/{$dados["download"]}";
    	}
    	$dados["cliente_nome"] = $this->limpaNome($dados["cliente_nome"]);
    	$dados["comentario"] = preg_replace('/\s{2,}/', ' ', trim(substr("[{$dados["cliente_id"]}] {$dados["cliente_nome"]}", 0, 40)));
    	$dados["ativo"] = !$dados["cancelado"];
    	return $dados;
    }
	}
	
	function buscaDadosConcentrador($servidor_id) {
		$query = "
			SELECT
				tab_servidores.ser_id AS id,
				tab_servidores.ser_nome AS nome,
				CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip
			FROM
				tab_servidores
				INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_servidores.ser_id = '{$servidor_id}'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
	}
	
	function buscaConcentradorAutenticacoes($servidor_id) {
		$autenticacoes = array();
		$query = "
			SELECT
				cau_id AS id
			FROM
				tab_clientes_autenticacoes
			WHERE
				(ser_id = $servidor_id".($servidor_id == 680 ? "" : " OR ser_id IS NULL").") AND
				cas_id IN (1, 2)
			ORDER BY
				cau_usuario";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$autenticacoes[] = $dados["id"];
    }
    return $autenticacoes;
	}
		
	###########
	### SSH ###
	###########
	
  function testaSSH($servidor_ip) {
    $comando = "/usr/local/nagios/libexec/check_ssh -t 3 $servidor_ip";
    $retorno = exec($comando);
    if (substr($retorno, 0, 6) == "SSH OK") {
      return true;
    } else {
      return false;
    }
  }
	
  function conectarSSH(&$conexao, $ip, $usuario = null, $senha = null) {
  	if ($this->debug) {
  		print "Conectando em $ip\n";
  		flush();
  	}
//    if (!$this->testaSSH($ip)) {
//	      return "Erro de SSH";
//    }
  	if (function_exists("ssh2_connect")) {
	  	$usuario = $usuario ? $usuario : $this->usuario;
	  	$senha = $senha ? $senha : $this->senha;
	  	$conexao = ssh2_connect($ip, 22);
			if ($conexao) {
	      if (ssh2_auth_password($conexao, $usuario, $senha)) {
	        return true;
	      } else {
	        return "Falha de autenticação";
	      }
	    } else {
	      return "Falha de conexão";
	    }
  	} else {
  		return "Falha ao carregar biblioteca SSH2";
  	}
  }

  function executarSSH(&$conexao, $comando) {
  	if ($this->debug) {
  		print "$comando\n";
  		flush();
  	}
    if ($conexao) {
    	$retorno = "";
      $stream = ssh2_exec($conexao, $comando);
      stream_set_blocking($stream, true);
      stream_set_timeout($stream, 600);
      while (($linha = fgets($stream)) != false) {
        $retorno .= $linha;
      }
			fclose($stream);
			return trim($retorno);
    }
  }
    
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  function verificarConexao($ip, $ip_ap) {
  	// Equipamento do cliente
  	$erro = $this->conectarSSH($this->conexao_concentrador, $ip, "ubnt", "cbcftm");
  	if ($erro) {
  		$erro = $this->conectarSSH($this->conexao_concentrador, $ip, "ubnt", "vagabunda");
  		if ($erro) {
  			$erro = $this->conectarSSH($this->conexao_concentrador, $ip, "ubnt", "ppca24");
  			if ($erro) {
	  			$erro = $this->conectarSSH($this->conexao_concentrador, $ip, "root", "cbcftm");
	  			if ($erro) {
					$erro = $this->conectarSSH($this->conexao_concentrador, $ip, "ubnt", "ppcA24!,");
					if ($erro) {
						return array("erro" => $erro);
					}
	  			}
  			}
  		}
  	}
  	// AP
  	$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "ubnt", "cbcftm");
  	if ($erro) {
  		$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "ubnt", "vagabunda");
  		if ($erro) {
  			$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "ubnt", "ppca24");
  			if ($erro) {
  				$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "admin", "vagabunda");
  				if ($erro) {
	  				$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "admin", "elr3OII,");
	  				if ($erro) {
		  				$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "admin", "cbcftm");
		  				if ($erro) {
							$erro = $this->conectarSSH($this->conexao_ap, $ip_ap, "ubnt", "ppcA24!,");
							if ($erro) {
								return array("erro" => $erro);
							}
		  				}
	  				}
  				}
  			}
  		}
  	}
  	// Executa 3 verificações
  	for ($i = 0; $i < 3; $i++) {
  		$dados = $this->executarVerificacao();
  		if (is_array($dados)) {
  			$verificacoes[] = $dados;
  		} else {
  			return array("erro" => $dados);
  		}
  		sleep(2);
  	}
  	return $verificacoes;
  }
  
  function executarVerificacao() {
  	$retorno = $this->executarSSH($this->conexao_concentrador, "mca-status");
  	$dados = array();
  	if (preg_match("/mca-status: not found/", $retorno)) {
  		// Intelbras
			$retorno = $this->executarSSH($this->conexao_concentrador, "iwconfig");
			if (!preg_match("/Link Quality=([^\\s]*)\\s*Signal level:([^\\s]*)\\s*Signal Noise:([^\\s]*)/i", $retorno, $matches)) {
				preg_match("/Link Quality=([^\\s]*)\\s*Signal level:([^\\s]*)\\s*dBm\\s*Noise level:([^\\s]*)/i", $retorno, $matches);
			}
			$dados["platform"] = "WOM 5000 Intelbras";
			$dados["quality"] = $matches[1];
			$dados["signal"] = $matches[2];
			$dados["noise"] = $matches[3];
			$dados["snr"] = $dados["signal"] - $dados["noise"];
			$retorno = $this->executarSSH($this->conexao_concentrador, "ifconfig");
			if (preg_match("/ra0\\s+Link encap:Ethernet\\s+HWaddr\\s+([a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2})/i", $retorno, $matches)) {
				$dados["mac"] = $matches[1];
			}
  	} else {
			// Ubiquit
	  	preg_match_all("/([a-z]+)=([^\r\n,]+)[\r\n,]+/i", $retorno, $matches);
			for ($i = 0; $i < count($matches[1]); $i++) {
				$dados[strtolower($matches[1][$i])] = $matches[2][$i];
			}
			preg_match("/deviceId=([a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2}:[a-f0-9]{2})/i", $retorno, $matches);
			$dados["mac"] = $matches[1];
			$dados["ccq"] = $dados["ccq"] / 10;
  	}
  	// AP Data
  	$retorno = $this->executarSSH($this->conexao_ap, "interface wireless registration-table print where mac-address={$dados["mac"]}");
  	if (preg_match("/{$dados["mac"]}\\s+[^\\s]+\\s+([\\-0-9]+)dBm/i", $retorno, $matches)) {
			$dados["apsignal"] = $matches[1];
  	} else {
	  	$retorno = $this->executarSSH($this->conexao_ap, "wstalist");
	  	if (preg_match("/wstalist: not found/", $retorno) || preg_match("/Unknown command: 'wstalist'/", $retorno)) {
		  	$retorno = $this->executarSSH($this->conexao_ap, "iwlist peers");
	  		if (preg_match("/Unknown command: 'iwlist'/", $retorno)) {
		  		// Intelbras
			  	$this->executarSSH($this->conexao_ap, "shell");
			  	$retorno = $this->executarSSH($this->conexao_ap, "cat /proc/net/ra0/peers");
			  	preg_match("/Peer MAC address\\s+=\\s+{$dados["mac"]}\\s+".
			  	           "Connection time\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Interface\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Has WDS clients\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "IEEE mode\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "IEEE parameters\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Capabilities\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx rate\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx MCS\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx packets\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx bytes\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx overruns\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx successful\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx failed\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx retried\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx not retried\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx legacy\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx legacy bytes\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx A-MSDUs\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx A-MSDU bytes\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx packets in A-MSDUs\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx MPDUs\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx MPDU bytes\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx fragmented\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx fragmented bytes\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx packets\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx bytes\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx dropped\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx CCK\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx OFDM\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx HT-Mixed\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx HT-Greenfield\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx A-MSDUs\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx A-MPDUs\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx Short Guard Interval\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx 40MHz bandwidth\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx last RSSI0\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx last RSSI1\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx last RSSI2\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "SNR0\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "SNR1\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Tx packets per MCS\\s+=\\s+([^\\r\\n]*)\\s+".
			  	           "Rx packets per MCS\\s+=\\s+([^\\s]*)/i", $retorno, $matches);
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
			if (preg_match("/{$dados["mac"]}|[0-9]+|[0-9\\.\\-]+|[0-9\\-\\.]+|([0-9\\-\\.]+)|([0-9\\-\\.]+)/i", $retorno, $matches)) {
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
  	return $dados;
  }
  
}

?>
