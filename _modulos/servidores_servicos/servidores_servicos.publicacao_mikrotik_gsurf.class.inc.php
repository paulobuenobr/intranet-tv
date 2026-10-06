<?

set_time_limit(0);
ini_set("default_socket_timeout", 600);

/*
date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$_GET["debug"] = 1;

$teste = controle::instanciaObjeto("servidores_servicos_publicacao_mikrotik_gsurf", $bancodedados);
print $teste->publicar(610);
//*/

class servidores_servicos_publicacao_mikrotik_gsurf {

  var $bancodedados;
  var $pontos_tecnica;
  var $conexao;
  var $ip;
  var $usuario;
  var $senha;
  
  var $redes;
  var $equipamentos;
  
  var $residuais;
  
  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->pontos_tecnica = unserialize(PUBLICACAO_PONTOS_TECNICA);
    $this->usuario = "admin";
    $this->senha = "M0rT4d3l41";
  }

  function popularRedes() {
    $this->redes[] = array("ip" => "172.29.30.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.31.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.32.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.33.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.34.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.35.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.37.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.38.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.39.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.40.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.41.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.43.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.44.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.45.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.46.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.50.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.52.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.53.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.54.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.55.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.58.0", "classe" => 24);
    $this->redes[] = array("ip" => "172.29.59.0", "classe" => 24);
    
    for ($i = 0; $i < count($this->redes); $i++) {
    	$this->buscaIPsRede($this->redes[$i]["ip"], $this->redes[$i]["classe"]);
    }
  }
  
  function popularEquipamentos() {
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.31.81/32", "comentario" => "POP: Nano-Bigua", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.31.82/32", "comentario" => "POP: Ric Silveira", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.32.2/32", "comentario" => "POP: Gsurf", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.33.250/32", "comentario" => "POP: Nano-Morrinhos", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.34.246/32", "comentario" => "POP: garopaba1", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.230/32", "comentario" => "POP: Nano-Rosa", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.242/32", "comentario" => "POP: GS-Ressacada", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.243/32", "comentario" => "POP: GS-Ressacada", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.245/32", "comentario" => "POP: RemavC", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.248/32", "comentario" => "POP: Nano-Grama", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.249/32", "comentario" => "POP: Nano-Praia", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.250/32", "comentario" => "POP: GS-Rosa", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.252/32", "comentario" => "POP: RemavA", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.35.253/32", "comentario" => "POP: RemavB", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.37.230/32", "comentario" => "POP: Silveira_Centro", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.37.231/32", "comentario" => "POP: Nano-Silveira2", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.37.240/32", "comentario" => "POP: NanoCentro", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.37.252/32", "comentario" => "POP: SilveiraPraia", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.243/32", "comentario" => "POP: AP Praia do Rosa Encantada", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.244/32", "comentario" => "POP: Switch PoE Encantada", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.245/32", "comentario" => "POP: Energia", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.246/32", "comentario" => "POP: ERB Encantada AP1", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.247/32", "comentario" => "POP: Nano-Ouvidor", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.248/32", "comentario" => "POP: ERB Encantada AP2", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.249/32", "comentario" => "POP: Nano-Ouvidor", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.250/32", "comentario" => "POP: Gs-encantada01", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.251/32", "comentario" => "POP: Gs-encantada01", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.252/32", "comentario" => "POP: Gs-encantada02", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.39.253/32", "comentario" => "POP: ERB Encantada AP3", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.40.238/32", "comentario" => "POP: ferrugem11", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.41.240/32", "comentario" => "POP: Nano-Silveira", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.41.249/32", "comentario" => "POP: Switch PoE Silveira Centro I", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.41.250/32", "comentario" => "POP: Silveira3", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.41.251/32", "comentario" => "POP: Silveira1", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.41.252/32", "comentario" => "POP: Gs-silveira2", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.41.253/32", "comentario" => "POP: Switch PoE Silveira Centro II", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.43.249/32", "comentario" => "POP: GS-AMB", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.43.250/32", "comentario" => "POP: Gs-ambrosio", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.43.251/32", "comentario" => "POP: Gs-ambrosio1", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.43.252/32", "comentario" => "POP: ambrosio", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.44.210/32", "comentario" => "POP: AP4-Nano-Ferrugem", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.44.220/32", "comentario" => "POP: Ferrugem44241", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.44.230/32", "comentario" => "POP: Gs-ferrugem01", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.44.231/32", "comentario" => "POP: Switch PoE Ferrugem", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.44.233/32", "comentario" => "POP: Gs-ferrugem02", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.46.210/32", "comentario" => "POP: Nano-Ressacada", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.46.250/32", "comentario" => "POP: Ressacada3", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.46.251/32", "comentario" => "POP: Ressacada Ap4", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.46.253/32", "comentario" => "POP: Ressacada1", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.2/32", "comentario" => "POP: GS-Bigua", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.3/32", "comentario" => "POP: gsdir45", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.224/32", "comentario" => "POP: Bigua", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.250/32", "comentario" => "POP: GS-Bigua2", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.251/32", "comentario" => "POP: Gs-palhocinha02", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.252/32", "comentario" => "POP: Gs-palhocinha01", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.50.253/32", "comentario" => "POP: Palhocinha", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.210/32", "comentario" => "POP: GS-PB", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.232/32", "comentario" => "POP: Nano-Laerte", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.233/32", "comentario" => "POP: POP Laerte", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.238/32", "comentario" => "POP: POP Siriu", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.239/32", "comentario" => "POP: Siriu switch poe", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.240/32", "comentario" => "POP: Siriu bullet 5.8", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.245/32", "comentario" => "POP: Nano-Siriu", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.249/32", "comentario" => "POP: Silveira4", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.250/32", "comentario" => "POP: Silveira", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.251/32", "comentario" => "POP: Gs-siriu", "bloqueado" => "no");
    $this->equipamentos[] = array("download" => 0, "upload" => 0, "ip" => "172.29.53.252/32", "comentario" => "POP: AP2 do Silveira Praia", "bloqueado" => "no");
    
    for ($i = 0; $i < count($this->equipamentos); $i++) {
    	$this->residuais[$this->equipamentos[$i]["ip"]] = $this->equipamentos[$i];
    }
  }
  
  function buscaIPsRede($ip, $classe) {
		$ips = array();
		$quantidade_ips = pow(2, 32 - $classe);
		$ip = ip2long($ip);
		for ($i = 1; $i < $quantidade_ips - 2; $i++) {
			$temp = long2ip($ip + $i)."/32";
			$this->residuais[$temp]= array("ip" => $temp, "comentario" => "IP nao utilizado", "download" => 1024, "upload" => 1024, "bloqueado" => "no");
		}
		return $ips;
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
  	$this->popularRedes();
  	$this->popularEquipamentos();
  	$interfaces = $this->buscaInterfaces($servidor_id);
    if ($interfaces) {
    	foreach ($interfaces as $interface) {
    		// Busca queue
	    	$retorno = $this->extraiRetorno($this->executar("/queue simple print detail without-paging where interface={$interface["nome"]}"), "target-addresses");
    		// Busca pontos
    		$this->buscaPontosClientes($interface["id"]);
	    	foreach ($this->residuais as $ponto) {
    			// Insere/atualiza queue
    			if (!$retorno[$ponto["ip"]]) {
	    			$this->executar("/queue simple add target-addresses={$ponto["ip"]} max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$interface["nome"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
    			} else {
						if ($ponto["comentario"] != $retorno[$ponto["ip"]]["comment"] ||
					  	  "{$ponto["upload"]}/{$ponto["download"]}" !== $retorno[$ponto["ip"]]["max-limit"] ||
					  	  ($ponto["bloqueado"] == "yes") != in_array("X", $retorno[$ponto["ip"]]["flags"])) {
		  					$this->executar("/queue simple set [/queue simple find name={$retorno[$ponto["ip"]]["name"]}] max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$interface["nome"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
			  	  }
    				unset($retorno[$ponto["ip"]]);
    			}
	    	}
		    // Remove queue
		    foreach ($retorno as $ponto) {
		    	if (!in_array("D", $ponto["flags"]) && $ponto["comment"] != "Sistema") {
	    			$this->executar("/queue simple remove [/queue simple find name={$ponto["name"]}]");
		    	}
		    }
    	}
    }
    $this->executar("/quit");
  }
  
  function publicarPonto($ponto_id) {
  	$ponto = $this->buscaPontosClientes(null, $ponto_id);
  	if ($ponto) {
	    $retorno = $this->extraiRetorno($this->executar("/queue simple print detail without-paging where target-addresses={$ponto["ip"]}"), "target-addresses");
	    // Insere/atualiza queue
    	if (!$retorno[$ponto["ip"]]) {
	    	$this->executar("/queue simple add target-addresses={$ponto["ip"]} max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
    	} else {
				if ($ponto["comentario"] != $retorno[$ponto["ip"]]["comment"] ||
					  "{$ponto["upload"]}/{$ponto["download"]}" !== $retorno[$ponto["ip"]]["max-limit"] ||
					  ($ponto["bloqueado"] == "yes") != in_array("X", $retorno[$ponto["ip"]]["flags"])) {
		  		$this->executar("/queue simple set [/queue simple find name={$retorno[$ponto["ip"]]["name"]}] max-limit={$ponto["upload"]}/{$ponto["download"]} interface={$ponto["interface"]} comment=\"{$ponto["comentario"]}\" disabled={$ponto["bloqueado"]}");
				}
    		unset($retorno[$ponto["ip"]]);
    	}
  	}
    $this->executar("/quit");
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
  			stream_set_blocking($stream, true);
        stream_set_timeout($stream, 600);
				$linhas = "";
        while (($linha = fgets($stream)) != false) {
          $linhas .= $linha;
        }
        fclose($stream);
        return $linhas;
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
        tab_servidores_servicos.sst_id = 17";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$interfaces[] = $dados;
    }
    return $interfaces;
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
        tab_servidores_servicos.sst_id = 17 AND
        tab_clientes_pontos.clp_ip IS NOT NULL AND
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
      $dados["status"] = $dados["status_id"] != 1 || !$dados["upload"] || !$dados["download"] || ($dados["ajustado"] && (!$dados["parametros_ajuste"]["upload"] || !$dados["parametros_ajuste"]["download"])) ? 0 : 1;
      //$dados["bloqueado"] = $dados["status"] ? "no" : "yes";
      if ($dados["ip"]) {
      	$dados["ip"] = "{$dados["ip"]}/32";
      }
      $dados["bloqueado"] = "no";
      if (!$dados["status"]) {
        $dados["upload"] = 1024;
        $dados["download"] = 1024;
      }
      if ($ponto_id) {
      	return $dados;
      } else {
      	$this->residuais[$dados["ip"]] = $dados;
//      	$pontos[] = $dados;
      }
    }
//    return $pontos;
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
					if (array_key_exists($entry[$key], $estrutura)) {
						$estrutura[$entry[$key].uniqid()] = $entry;
					} else {
						$estrutura[$entry[$key]] = $entry;
					}
				}
			}
		}
		return $estrutura;
	}
  
}

?>
