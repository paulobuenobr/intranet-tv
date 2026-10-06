<?

class servidores_servicos_arp {

	var $bancodedados;
	var $tabela_conteudo;
	var $tabela;
  var $pontos_tecnica;

  function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
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
		// Gera tabela
		if (!$this->geraTabela($servidor_id)) return "";
		$tabela = "'".$this->tabela_conteudo."'";
		// Busca dados do servidor - IP
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		$ip = $dados_servidor["ip_servidor"];
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
		// Gera tabela
		if (!$this->geraTabela($servidor_id)) return "";
		$tabela = "'".$this->tabela_conteudo."'";
		// Busca dados do servidor - IP
		$dados_servidor = $this->buscaDadosServidor($servidor_id);
		$ip = $dados_servidor["ip_servidor"];
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

	// Busca Parametros do Serviço
	function buscaParametrosServico($servidor_id) {
		$query = "
			SELECT
				tab_servidores_servicos.ses_parametros AS parametros
			FROM
				tab_servidores_servicos
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_servidores_servicos.sst_id = '2' AND
				tab_servidores_interfaces.ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$dados = unserialize($dados["parametros"]);
			return $dados;
    }
	}

	// Busca Pontos Ativos Vinculados a Interface
	function buscaPontos($interface_id) {
		$query = "
	   	SELECT
			  tab_clientes.cli_nome as cliente,
	   		SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
			  tab_condominios_blocos.cob_nome AS bloco,
   			tab_clientes_pontos.clp_endereco_mac AS mac,
	 			tab_clientes_pontos.clp_ip AS ip,
	 			tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
	 			tab_clientes_pontos.clp_ajustado AS ajustado,
				tab_clientes_pontos.cps_id AS status_id
   		FROM
				tab_clientes_pontos
				LEFT OUTER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
				LEFT OUTER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
  		WHERE
 	  		tab_clientes_pontos.sei_id = $interface_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
   			tab_clientes_pontos.cps_id != 3
   		ORDER BY
	   		ip";
	  $query = $this->bancodedados->query($query);
	  $ponto = $this->bancodedados->resultadoArray($query);
	  while ($ponto) {
	  	$parametros_ajuste = @unserialize($ponto["parametros_ajuste"]);
	  	if (($ponto["status_id"] == 2) || (($ponto["status_id"] == 1) && ($ponto["ajustado"] && $parametros_ajuste["download"] == 0))) {
	  		$ponto["mac"] = "aa:".$ponto["mac"];
				$ponto["comentarios"] .= " (Bloqueado)";
	  	}
	  	if ($ponto["cliente"]) {
	  		$ponto["cliente"] = $this->limpaNome($ponto["cliente"]);
				$ponto["comentarios"] = $ponto["cliente"]." (".(($ponto["bloco"] == "Único") ? $ponto["complemento"] : "{$ponto["complemento"]}-{$ponto["bloco"]}").")";
	  	} else {
	  		$ponto["comentarios"] = "(Ponto sem assinatura)";
	  	}
	  	$pontos[$ponto["ip"]] = "{$ponto["mac"]};{$ponto["comentarios"]}";
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


	// Busca Parametros do Servidor
	function buscaDadosServidor($servidor_id) {
		$query = "
			SELECT
	    	tab_servidores_interfaces.sei_id AS id,
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

	// Busca Parametros da Interface
	function buscaDadosInterface($interface_id) {
		$query = "
			SELECT
	  		tab_servidores_interfaces.sei_prefixo_ip AS prefixo_ip,
    		tab_servidores_interfaces.sei_endereco_ip AS endereco_ip
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
			tab_servidores_servicos.sst_id = 2";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
   		$interfaces[] = $dados["id"];
  	}
  	return $interfaces;
	}

	function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 2 AND sei_id = $interface_id";
    $this->bancodedados->query($query);
	}

	###################
	### Gera Tabela ###
	###################

	function geraTabela($servidor_id) {
		if (!$this->buscaParametrosServico($servidor_id)) return false; // Serviço ARP não cadastrado
		$interfaces = $this->buscaInterfaces($servidor_id);
		foreach($interfaces as $interface) {
			$dados_interface = $this->buscaDadosInterface($interface);
			$lista_ips = array();
			for ($i = 1; $i < 255; $i++) {
				if ($i != $dados_interface["endereco_ip"]) {
					$lista_ips[$dados_interface["prefixo_ip"].".".$i] = "aa:aa:aa:aa:aa:aa";
				}
			}
      for ($i = 0; $i < count($this->pontos_tecnica); $i++) {
			 $lista_ips[$dados_interface["prefixo_ip"].".".(25 - $i)] = $this->pontos_tecnica[$i];
      }
			$pontos = $this->buscaPontos($interface);
			foreach ($lista_ips as $ip => $mac) {
				if ($pontos[$ip])
				  $tabela[] = $ip.";".$pontos[$ip]; else
				  $tabela[] = $ip.";".$mac;
			}
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

	function executar($ip, $comando) {
		$comando = "ssh -o 'ConnectTimeout ".SSH_TIMEOUT."' -i ".SSH_CHAVE." ".SSH_USUARIO."@$ip sudo \"$comando\"";
		return exec($comando);
	}

}

?>
