<?

class servidores_servicos_dhcp {

	var $bancodedados;
	var $ip_inicial;
	var $ip_final;
	var $dominio;
	var $dns;
	var $max_lease_time;
	var $default_lease_time;
	var $tabela_conteudo;
	var $tabela;
  var $pontos_tecnica;

  function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->ip_inicial = 24; // Range de IP's - Valor inicial
		$this->ip_final = 254; // Range de IP's - Valor final
		$this->dominio = "wavesystem.com.br";
		$this->dns = "dns1.viawave.com.br,dns2.viawave.com.br"; // Lista de DNS separados por vírgula
		$this->max_lease_time = "1120";
		$this->default_lease_time = "520";
    $this->pontos_tecnica = unserialize(PUBLICACAO_PONTOS_TECNICA);
	}

	####################################################################
	### Funções: Exportar / Publicar / Reiniciar / PublicarReiniciar ###
	####################################################################

	function exportarTabela($servidor_id) {
		if ($this->geraTabela($servidor_id)) {
			return $this->tabela_conteudo;
		}
	}

	function publicarTabela($servidor_id) {
		// Busca dados do servidor - IP
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		$ip = $dados_servidor["ip_servidor"];
		if (!$this->testaSSH($ip)) {
		  return "Erro de SSH";
		}
		// Gera tabela
		if (!$this->geraTabela($servidor_id)) return "";
		$tabela = "'".$this->tabela_conteudo."'";
		// Busca parâmetros do serviço
		$parametros = $this->buscaParametrosServico($servidor_id);
		$comando = str_replace("[tabela]", $tabela, $parametros["publicar"]);
		// Executa comando
		$retorno = $this->executar($ip, $comando);
		if ($retorno == "[rws]1" || $retorno == "[rws]2") {
			return "Erro no arquivo de configuração";
		} elseif ($retorno == "[rws]3") {
			return "Tamanho do arquivo de configuração excedido";
		} elseif ($retorno == "[rws]4") {
			return "Parâmetro(s) inválido(s)";
		} elseif ($retorno == "[rws]5") {
			return "Nenhum parâmetro informado";
		} elseif ($retorno == "[rws]6") {
			return "Sistema Operacional inválido";
		} elseif ($retorno == "[rws]7") {
			// Atualiza Pontos/Servidores Serviços
			$this->atualizaServicos($servidor_id);
			return ""; // Comando executado com sucesso
		} else {
			return "Erro desconhecido";
		}
	}

	function reiniciarServico($servidor_id) {
		// Busca dados do servidor - IP
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		$ip = $dados_servidor["ip_servidor"];
		if (!$this->testaSSH($ip)) {
		  return "Erro de SSH";
		}
		// Busca parâmetros do serviço
		$parametros = $this->buscaParametrosServico($servidor_id);
		$comando = $parametros["reiniciar"];
		// Executa comando
		$retorno = $this->executar($ip, $comando);
		if ($retorno == "[rws]1" || $retorno == "[rws]2") {
			return "Erro no arquivo de configuração";
		} elseif ($retorno == "[rws]3") {
			return "Tamanho do arquivo de configuração excedido";
		} elseif ($retorno == "[rws]4") {
			return "Parâmetro(s) inválido(s)";
		} elseif ($retorno == "[rws]5") {
			return "Nenhum parâmetro informado";
		} elseif ($retorno == "[rws]6") {
			return "Sistema Operacional inválido";
		} elseif ($retorno == "[rws]0") {
			return ""; // Comando executado com sucesso
		} else {
			return "Erro desconhecido";
		}
	}

	function publicarReiniciarServico($servidor_id) {
		// Busca dados do servidor - IP
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		$ip = $dados_servidor["ip_servidor"];
		if (!$this->testaSSH($ip)) {
		  return "Erro de SSH";
		}
		// Gera tabela
		if (!$this->geraTabela($servidor_id)) return "";
		$tabela = "'".$this->tabela_conteudo."'";
		// Busca parâmetros do serviço
		$parametros = $this->buscaParametrosServico($servidor_id);
		$comando = str_replace("[tabela]", $tabela, $parametros["publicar_reiniciar"]);
		// Executa comando
		$retorno = $this->executar($ip, $comando);
		if ($retorno == "[rws]1" || $retorno == "[rws]2") {
			return "Erro no arquivo de configuração";
		} elseif ($retorno == "[rws]3") {
			return "Tamanho do arquivo de configuração excedido";
		} elseif ($retorno == "[rws]4") {
			return "Parâmetro(s) inválido(s)";
		} elseif ($retorno == "[rws]5") {
			return "Nenhum parâmetro informado";
		} elseif ($retorno == "[rws]6") {
			return "Sistema Operacional inválido";
		} elseif ($retorno == "[rws]0") {
			// Atualiza Pontos/Servidores Serviços
			$this->atualizaServicos($servidor_id);
			return ""; // Comando executado com sucesso
		} else {
			return "Erro desconhecido";
		}
	}

	#################################
	### Funcoes de Banco de Dados ###
	#################################

	function buscaParametrosServico($servidor_id) {
		$query = "
			SELECT
				tab_servidores_servicos.ses_parametros AS parametros
			FROM
				tab_servidores_servicos
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				(tab_servidores_servicos.sst_id = '1') AND
				(tab_servidores_interfaces.ser_id = '$servidor_id')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$dados = unserialize($dados["parametros"]);
			return $dados;
    }
	}

	function buscaPontos($interface_id) {
		$query = "
	   	SELECT
        CONCAT(tab_clientes_pontos.clp_id, ' ', IF(tab_condominios_blocos.cob_nome = 'Único' OR tab_condominios_blocos.cob_id IS NULL, SUBSTRING(tab_clientes.cli_complemento, 2), CONCAT(tab_condominios_blocos.cob_nome, ' ', SUBSTRING(tab_clientes.cli_complemento, 2)))) AS host,
   			tab_clientes_pontos.clp_endereco_mac AS mac,
	 			tab_clientes_pontos.clp_ip AS ip
   		FROM
        tab_clientes_pontos
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        LEFT OUTER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
  		WHERE
 	  		tab_clientes_pontos.sei_id = $interface_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
   			tab_clientes_pontos.cps_id NOT IN ('2', '3') AND
   			tab_clientes_pontos.clp_ip IS NOT NULL
   		ORDER BY
	   		ip";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	  	$dados["host"] = $this->limpaNome($dados["host"]);
   		$pontos[] = $dados;
  	}
  	return $pontos;
	}

  function limpaNome($nome) {
    $nome = strtolower($nome);
    $nome = str_replace(array("ç", "ñ"), array("c", "n"), $nome);
    $nome = preg_replace("/[áàâãäª]/", "a", $nome);
    $nome = preg_replace("/[éèêë]/", "e", $nome);
    $nome = preg_replace("/[íìîï]/", "i", $nome);
    $nome = preg_replace("/[óòôõö°º]/", "o", $nome);
    $nome = preg_replace("/[úùûü]/", "u", $nome);
    $nome = preg_replace("/[^a-z0-9() ]/", " ", $nome);
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome);
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "SA", "ME");
    $nome = ucwords($nome);
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
    $nome = trim($nome);
    $nome = preg_replace("[\r\n\t _]+", "_", $nome);
    return $nome;
  }

	function buscaDadosServidor($servidor_id) {
		$query = "
			SELECT
	  		tab_servidores_interfaces.sei_prefixo_ip AS prefixo_ip,
    		tab_servidores_interfaces.sei_endereco_ip AS endereco_ip,
  			tab_servidores_interfaces.sei_mascara_rede AS mascara_rede,
  			tab_servidores.ser_configuracao AS configuracao,
  			CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip_servidor
    	FROM
	  		tab_servidores
				INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
    	WHERE
    		(tab_servidores.ser_id = '$servidor_id')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$configuracao = unserialize($dados["configuracao"]);
			$dados["sistema_operacional"] = $configuracao["sistema_operacional"];
			return $dados;
		}
	}

	function buscaDadosInterface($interface_id) {
		$query = "
			SELECT
	  		tab_servidores_interfaces.sei_prefixo_ip AS prefixo_ip,
    		tab_servidores_interfaces.sei_endereco_ip AS endereco_ip,
				tab_servidores_interfaces.sei_mascara_rede AS mascara_rede
    	FROM
	  		tab_servidores_interfaces
    	WHERE
    		(tab_servidores_interfaces.sei_id = '$interface_id')";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
	}

	function buscaInterfaces($servidor_id) {
		$query = "
	   	SELECT
				tab_servidores_interfaces.sei_id AS id
   		FROM
	   		tab_servidores_interfaces
				INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
  		WHERE
   			tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_servidores_interfaces.sit_id = 1 AND
				tab_servidores_servicos.sst_id = 1";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
   		$lista[] = $dados["id"];
  	}
  	return $lista;
	}

	function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 1 AND sei_id = $interface_id";
    $this->bancodedados->query($query);
	}

	###################
	### Gera Tabela ###
	###################

	function geraTabela($servidor_id) {
		if (!$this->buscaParametrosServico($servidor_id)) return false; // Serviço DHCP não cadastrado
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		$interfaces = $this->buscaInterfaces($servidor_id);
		if (($interfaces) && ($dados_servidor["sistema_operacional"] == 2 || $dados_servidor["sistema_operacional"] == 4)) {
			$tabela[] = "ddns-update-style none;\n";
		}
		foreach($interfaces as $interface) {
			$dados_interface = $this->buscaDadosInterface($interface);
			$pontos = $this->buscaPontos($interface);
	    $tabela[] = "subnet {$dados_interface["prefixo_ip"]}.0 netmask {$dados_interface["mascara_rede"]} {";
			$tabela[] = "	range {$dados_interface["prefixo_ip"]}.".$this->ip_inicial." {$dados_interface["prefixo_ip"]}.".$this->ip_final.";";
			$tabela[] = "";
			$tabela[] = "	option routers			{$dados_interface["prefixo_ip"]}.{$dados_interface["endereco_ip"]};";
			$tabela[] = "	option subnet-mask		{$dados_interface["mascara_rede"]};";
			$tabela[] = "	option domain-name		\"".$this->dominio."\";";
			$tabela[] = "	option domain-name-servers	{$dados_interface["prefixo_ip"]}.{$dados_interface["endereco_ip"]}".(($this->dns) ? ",$this->dns" : "").";";
			$tabela[] = "		max-lease-time ".$this->max_lease_time.";";
			$tabela[] = "		default-lease-time ".$this->default_lease_time.";";
			$tabela[] = "		deny unknown-clients;";
			$tabela[] = "";
	    if ($servidor_id != 67) {
        for ($i = 0; $i < count($this->pontos_tecnica); $i++) {
    			$tabela[] = "	host laptop_".($i + 1)."_$interface {";
    			$tabela[] = "		hardware ethernet {$this->pontos_tecnica[$i]};";
    			$tabela[] = "		fixed-address {$dados_interface["prefixo_ip"]}.".(25 - $i).";";
    			$tabela[] = "	}";
        }
	    }
			foreach ($pontos as $ponto) {
				$tabela[] = "	host {$ponto["host"]} {";
				$tabela[] = "		hardware ethernet {$ponto["mac"]};";
				$tabela[] = "		fixed-address {$ponto["ip"]};";
				$tabela[] = "	}";
			}
			$tabela[] = "}";
		}
		$this->tabela_conteudo = @implode("\n", $tabela);
		return true;
	}

	function trataTabela($tabela) {
		foreach ($tabela as $ponto) {
			$tabela_tratada[] = array($ponto["ip"], $ponto["mac"]);
		}
		return $tabela_tratada;
	}

	#########################################
	### Funcoes para Publicação da tabela ###
	#########################################

  function testaSSH($servidor_ip) {
    $comando = "/usr/local/libexec/check_ssh -t 3 $servidor_ip";
    $retorno = exec($comando);
    if (substr($retorno, 0, 6) == "SSH OK") {
      return true;
    } else {
      return false;
    }
  }

	function executar($ip, $comando) {
		$comando = str_replace('"', '\"', $comando);
		$comando = "ssh -o 'ConnectTimeout ".SSH_TIMEOUT."' -i ".SSH_CHAVE." ".SSH_USUARIO."@$ip sudo \"$comando\"";
		return exec($comando);
	}

}

?>
