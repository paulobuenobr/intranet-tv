<?

class servidores_servicos_htb {

	var $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	################
	### Instalar ###
	################

	function instalar($servidor_id) {
		// Busca Dados
		$comando = $this->buscaParametrosServico($servidor_id);
		if ($comando) {
			$interface_download = $this->buscaInterfaceDownload($servidor_id);
			$interface_upload = $this->buscaInterfaceUpload($servidor_id);
			// Pontos 256
			$quantidade_pontos_256 = $this->quantidadePontos256($servidor_id);
			// Pontos 300
			$pontos_300 = $this->listagemPontos300($servidor_id);
			$quantidade_pontos_300 = count($pontos_300);
			$pontos_300 = @implode("\n", $pontos_300);
			// Pontos 150
			$pontos_150 = $this->listagemPontos150($servidor_id);
			$quantidade_pontos_150 = count($pontos_150);
			$pontos_150 = @implode("\n", $pontos_150);
			// Dados Servidor
			$dados_servidor = $this->buscaDadosServidor($servidor_id);
			$ip = $dados_servidor["ip_servidor"];
			if ($dados_servidor["sistema_operacional"] == 1 || $dados_servidor["sistema_operacional"] == 2) {
				$diretorio = "/etc/default/htb/"; // Debian
			} else {
				$diretorio = "/etc/sysconfig/htb/"; // Red Hat
			}
			// Busca Comando
			$comando = str_replace("[down]", $interface_download, $comando);
			$comando = str_replace("[up]", $interface_upload, $comando);
			$comando = str_replace("[qt_150]", $quantidade_pontos_150, $comando);
			$comando = str_replace("[qt_256]", $quantidade_pontos_256, $comando);
			$comando = str_replace("[qt_300]", $quantidade_pontos_300, $comando);
			$comando = str_replace("[ip_150]", "'$pontos_150'", $comando);
			$comando = str_replace("[ip_300]", "'$pontos_300'", $comando);
			$comando = str_replace("[diretorio]", $diretorio, $comando);
			// Executa Comando
			$retorno = $this->executar($ip, $comando);
			if ($retorno == "[tbw]1") {
				// Atualiza Pontos/Servidores Serviços
				$this->atualizaServicos($servidor_id);
				return ""; // Comando executado com sucesso
			} else {
				return "Erro desconhecido";
			}
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
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
			WHERE
				(tab_servidores_servicos.sst_id = '3') AND
				(tab_servidores_servicos.sss_id = '1') AND
				(tab_servidores_interfaces.ser_id = '$servidor_id')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			$dados = unserialize($dados["parametros"]);
			return $dados["instalar"];
    }
	}

	// Busca Parametros do Servidor
	function buscaDadosServidor($servidor_id) {
		$query = "
			SELECT
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

	function buscaInterfaceDownload($servidor_id) {
		$query = "
			SELECT
				sei_nome AS interface
			FROM
				tab_servidores_interfaces
			WHERE
				(tab_servidores_interfaces.sit_id = '1') AND
				(tab_servidores_interfaces.ser_id = '$servidor_id')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			return $dados["interface"];
    }
	}

	function buscaInterfaceUpload($servidor_id) {
		$query = "
			SELECT
				sei_nome AS interface
			FROM
				tab_servidores_interfaces
			WHERE
				(tab_servidores_interfaces.sit_id = '3') AND
				(tab_servidores_interfaces.ser_id = '$servidor_id')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
			return $dados["interface"];
    }
	}

	function listagemPontos300($servidor_id) {
		$query = "
	   	SELECT
	 			tab_clientes_pontos.clp_ip AS ip
   		FROM
				tab_clientes_pontos
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
  		WHERE
 	  		tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_velocidade_download = 307200 AND
   			tab_clientes_pontos.cps_id = 1
   		ORDER BY
	   		tab_servidores_interfaces.ser_id,
	   		tab_clientes_pontos.clp_ip";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	  	$pontos[] = $dados["ip"].",*:80";
  	}
  	return $pontos;
	}

	function listagemPontos150($servidor_id) {
		$query = "
	   	SELECT
	 			tab_clientes_pontos.clp_ip AS ip
   		FROM
				tab_clientes_pontos
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
  		WHERE
 	  		tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_velocidade_download = 153600 AND
   			tab_clientes_pontos.cps_id = 1
   		ORDER BY
	   		tab_servidores_interfaces.ser_id,
	   		tab_clientes_pontos.clp_ip";
	  $query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	  	$pontos[] = $dados["ip"].",*:80";
  	}
  	return $pontos;
	}

	function quantidadePontos256($servidor_id) {
		$query = "
	   	SELECT
	 			COUNT(tab_clientes_pontos.clp_id) AS quantidade
   		FROM
				tab_clientes_pontos
				INNER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
  		WHERE
 	  		(tab_servidores_interfaces.ser_id = '$servidor_id') AND
				(tab_clientes_pontos.clp_velocidade_download = '262144') AND
   			(tab_clientes_pontos.cps_id = '1')";
	  $query = $this->bancodedados->query($query);
	  $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
	  	return $dados["quantidade"];
  	}
	}

	function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 3 AND sei_id = $interface_id";
    $this->bancodedados->query($query);
	}

	################
	### Executar ###
	################

	function executar($ip, $comando) {
		$comando = "ssh -o 'ConnectTimeout ".SSH_TIMEOUT."' -i ".SSH_CHAVE." ".SSH_USUARIO."@$ip \"sudo $comando\"";
		return exec($comando);
	}

}


?>
