<?

class servidores_servicos_publicacao {

  var $bancodedados;
  var $publicacao;
  var $pontos_tecnica;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->pontos_tecnica = unserialize(PUBLICACAO_PONTOS_TECNICA);
  }

  ################
  ### Publicar ###
  ################

  function publicar($servidor_id) {
    if ($this->servicoPublicacaoAtivo($servidor_id)) {
  		$dados_servidor = $this->buscaDadosServidor($servidor_id);
      if (!$this->testaSSH($dados_servidor["ip_servidor"])) {
        return "Erro de SSH";
      }
      $this->buscaPublicacao($servidor_id);
      $this->salvarArquivo($servidor_id);
      if (file_exists("/tmp/intranet_$servidor_id.cfg")) {
        $this->enviar($dados_servidor["ip_servidor"], "/tmp/intranet_$servidor_id.cfg", "intranet.cfg");
        $this->removerArquivo($servidor_id);
        $retorno = $this->executar($dados_servidor["ip_servidor"], "./bacontrol -i");
        if (strpos($retorno, "[ws]") === false) {
          return "Erro durante publicação no servidor";
        } else {
          $this->atualizaServicos($servidor_id);
        }
      } else {
        return "Erro durante criação do arquivo";
      }
    }
  }

  function exportar($servidor_id) {
    $this->buscaPublicacao($servidor_id);
    return $this->publicacao;
  }

  function buscaPublicacao($servidor_id) {
    unset($this->publicacao);
    $pontos = $this->buscaPontos($servidor_id);
    foreach ($pontos as $ponto) {
    	$conteudo = array();
      $conteudo[] = sprintf("%-15s", $ponto["ip"]);
      $conteudo[] = $ponto["mac"];
      $conteudo[] = sprintf("%5s", $ponto["interface"]);
      $conteudo[] = sprintf("%4s", $ponto["download"]);
      $conteudo[] = sprintf("%4s", $ponto["upload"]);
      $conteudo[] = sprintf("%-20s", $ponto["host"]);
      $conteudo[] = $ponto["comentarios"];
      $this->publicacao[] = implode(" ", $conteudo);
    }
    $this->publicacao = @funcoes::mescla_arrays(array($this->geraCabecalho($servidor_id, $pontos)), $this->publicacao);
    $this->publicacao = @implode("\n", $this->publicacao);
  }

  function geraCabecalho($servidor_id, $pontos) {
    $cabecalho[] = $this->buscaInterfaceUpload($servidor_id);
    $interface = array("eth0" => 0, "eth1" => 0, "eth2" => 0, "eth3" => 0, "eth4" => 0, "eth5" => 0);
    foreach ($pontos as $ponto) {
      $interface[$ponto["interface"]] += 1;
      $velocidade_total += $ponto["download"];
    }
    $cabecalho[] = @implode(" ", $interface);
    $cabecalho[] = $this->calculaFator(count($pontos), $velocidade_total);
    $cabecalho[] = $this->buscaRedes($pontos);
    return implode(" ", $cabecalho);
  }

  function calculaFator($quantidade_pontos, $velocidade_total) {
    return ((15 * $quantidade_pontos) + 500) / $velocidade_total;
  }

  function buscaRedes($pontos) {
    foreach ($pontos as $ponto) {
      $ip = @explode(".", $ponto["ip"]);
      $redes[] = $ip[2];
    }
    $redes = array_unique($redes);
    sort($redes);
    return @implode(":", $redes);
  }

  ######################
  ### Banco de Dados ###
  ######################

  function buscaPontos($servidor_id) {
    $query = "
      SELECT DISTINCT
        tab_clientes_pontos.clp_ip AS ip,
        tab_servidores_interfaces.sei_prefixo_ip AS rede,
        tab_clientes_pontos.clp_endereco_mac AS mac,
        tab_servidores_interfaces.sei_nome AS interface,
        tab_clientes_pontos.clp_velocidade_download AS download,
        tab_clientes_pontos.clp_velocidade_upload AS upload,
        tab_clientes_pontos.clp_ajustado AS ajustado,
				tab_clientes_pontos.cps_id AS status_id,
        tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
        CONCAT(REPEAT('0', 6 - LENGTH(tab_clientes_pontos.clp_id)), tab_clientes_pontos.clp_id, ' ', IF(tab_condominios_blocos.cob_nome = 'Único' OR tab_condominios_blocos.cob_id IS NULL, SUBSTRING(tab_clientes.cli_complemento, 2), CONCAT(tab_condominios_blocos.cob_nome, ' ', SUBSTRING(tab_clientes.cli_complemento, 2)))) AS host,
        tab_clientes.cli_nome AS comentarios
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        LEFT OUTER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.clp_endereco_mac IS NOT NULL AND
        tab_clientes_pontos.clp_ip IS NOT NULL AND
        tab_clientes_pontos.cps_id = 1
      ORDER BY
        tab_servidores_interfaces.sei_nome,
        INET_ATON(tab_clientes_pontos.clp_ip)";
    $query = $this->bancodedados->query($query);
    while (($ponto = $this->bancodedados->resultadoArray($query)) != false) {
    	$ponto["ip_ordem"] = sprintf("%u", ip2long($ponto["ip"]));
      if ($ponto["ajustado"]) {
        $ponto["parametros_ajuste"] = @unserialize($ponto["parametros_ajuste"]);
        $ponto["download"] = $ponto["parametros_ajuste"]["download"];
        $ponto["upload"] = $ponto["parametros_ajuste"]["upload"];
      }
      $ponto["download"] = $ponto["download"] ? ceil($ponto["download"] / 1024) : 0;
      $ponto["upload"] = $ponto["upload"] ? ($ponto["upload"] / 1024) : 0;

      // ====> Xuncho - Jary pediu p/ passar golden park para 600 k
      if ($servidor_id == 149 && $ponto["download"] > 0 && $ponto["download"] < 630) {
        $ponto["download"] = 630;
        $ponto["upload"] = 300;
      }
      // <==== Xuncho
      
      
//      if (intval(date("Hi"), 10) >= 1855 && intval(date("Hi"), 10) <= 2400) {
//        $ponto["download"] = ceil($ponto["download"] * 0.90);
//      }
      
      
      $ponto["host"] = $this->limpaNome($ponto["host"]);
      $ponto["comentarios"] = (strlen($ponto["comentarios"]) > 100) ? substr($ponto["comentarios"], 0, 100) : $ponto["comentarios"];
      $ponto["comentarios"] = substr($this->limpaNome($ponto["comentarios"]), 0, 100);
      $pontos[] = $ponto;
    }
    $pontos_wave = $this->buscaPontosWave($servidor_id);
    if ($pontos_wave) {
      $pontos = funcoes::mescla_arrays($pontos_wave, $pontos);
    }
    return $this->ordena_multiarray($pontos, array("interface", "ip_ordem"));
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
    	$array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[$chave."_".key($array)] = key($array);
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

  function buscaPontosWave($servidor_id) {
    if ($servidor_id == 67) {
      return null;
    }
    $query = "
      SELECT DISTINCT
        sei_prefixo_ip AS rede,
        sei_nome AS interface
      FROM
        tab_servidores_interfaces
      WHERE
        ser_id = $servidor_id AND
        sit_id = 1 AND
        sia_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      for ($i = 0; $i < count($this->pontos_tecnica); $i++) {
        $ponto["ip"] = $dados["rede"].".".(25 - $i);
    		$ponto["ip_ordem"] = sprintf("%u", ip2long($ponto["ip"]));
        $ponto["mac"] = $this->pontos_tecnica[$i];
        $ponto["download"] = "300";
        $ponto["upload"] = "150";
        $ponto["interface"] = $dados["interface"];
        $ponto["host"] = "laptop_".sprintf("%02s", $i + 1)."_{$dados["interface"]}";
        $ponto["comentarios"] = "Laptop_".sprintf("%02s", $i + 1)."_Tecnica";
        $pontos[] = $ponto;
      }
    }
    return $pontos;
  }

  function servicoPublicacaoAtivo($servidor_id) {
    $query = "
      SELECT
        ses_id AS id
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        sst_id = 8 AND
        sss_id = 1 AND
        ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosServidor($servidor_id) {
    $query = "
      SELECT DISTINCT
        tab_servidores_interfaces.sei_prefixo_ip AS prefixo_ip,
        tab_servidores_interfaces.sei_mascara_rede AS mascara_rede,
        CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip_servidor
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores.ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function atualizaServicos($interface_id) {
    $query = "UPDATE tab_servidores_servicos SET ses_data_publicacao = NOW() WHERE sst_id = 8 AND sei_id = $interface_id";
    $this->bancodedados->query($query);
  }

	function buscaInterfaceUpload($servidor_id) {
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

	######################
	### Outras Funções ###
	######################

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

  ##########################
  ### Publicação e Envio ###
  ##########################

  function salvarArquivo($servidor_id) {
    $arquivo = @fopen("/tmp/intranet_$servidor_id.cfg", "w");
    @fwrite($arquivo, $this->publicacao);
    @fclose($arquivo);
  }

  function removerArquivo($servidor_id) {
    unlink("/tmp/intranet_$servidor_id.cfg");
  }

  function testaSSH($servidor_ip) {
    $comando = "/usr/local/libexec/check_ssh -t 3 $servidor_ip";
    $retorno = exec($comando);
    if (substr($retorno, 0, 6) == "SSH OK") {
      return true;
    } else {
      return false;
    }
  }

  function executar($servidor_ip, $comando) {
    $comando = "ssh -o 'ConnectTimeout ".SSH_TIMEOUT."' -i ".SSH_CHAVE." ".SSH_USUARIO."@$servidor_ip sudo \"$comando\"";
    return exec($comando);
  }

  function enviar($servidor_ip, $arquivo_origem, $arquivo_destino) {
    $comando = "scp -i ".SSH_CHAVE." $arquivo_origem ".SSH_USUARIO."@$servidor_ip:$arquivo_destino";
    return exec($comando);
  }

}

?>
