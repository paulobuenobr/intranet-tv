<?

class central_telefonica_asterisk {
  
	var $bancodedados;
  var $verbose;
	
  var $fp;
  var $endereco_ip;
  var $porta;
  var $secret;
  var $usuario;
  
  var $comando;
  
  var $agente_id;
  var $ramal;
  var $discador;
  
  function __construct($bancodedados, $verbose = false) {
  	$this->bancodedados = $bancodedados;
    $this->verbose = $verbose;  
  	$this->comando = new Comando();
    $this->endereco_ip = "192.168.170.1";
    $this->porta = 5038;
    $this->secret = "df08072005";
    $this->usuario = "discador";
    if ($_SESSION["usuario"]) {
    	$this->buscaDadosUsuario();
    }
    // Conectar
    $this->conectar();
	  $this->logar();
  }
	  
  function buscaDadosUsuario() {
    $query = "
      SELECT
        usu_ramal AS ramal,
        usu_agente_id AS agente_id,
        usu_senha_discagem AS senha_discagem        
      FROM
        tab_usuarios
      WHERE
        usu_id = {$_SESSION["usuario"]}";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $this->ramal = $dados["ramal"];
      $this->agente_id = $dados["agente_id"];
      $this->discador = $dados["senha_discagem"];
      return true;
    }
    return false;
  }
  
	function buscaCliente($telefone) {
		$ddd = substr($telefone, 0, 2);
		$numero = substr($telefone, 2, 4)."-".substr($telefone, 6, 4);
		$query = "
		  SELECT
		    tab_clientes.cli_id AS id,
		    tab_clientes.cli_nome AS nome,
		    tab_condominios.con_id AS condominio_id,
		    tab_condominios.con_nome AS condominio_nome,
		    tab_bairros.bai_nome AS bairro
		  FROM
		  	tab_clientes_contatos_telefones
		    INNER JOIN tab_clientes ON (tab_clientes_contatos_telefones.cli_id = tab_clientes.cli_id)
		    LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
		    INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_clientes.cep_id IS NOT NULL, tab_clientes.cep_id, tab_condominios.cep_id))
		    INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
		    INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
		  WHERE
		    ccl_ddd = '$ddd' AND
		    ccl_numero = '$numero'
		  ORDER BY
		    tab_clientes.cli_nome";
		$query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	  	if ($dados["condominio_id"]) {
	  		$dados["nome"] = "{$dados["nome"]} ({$dados["condominio_nome"]} / {$dados["bairro"]})";
	  	} else {
	  		$dados["nome"] = "{$dados["nome"]} ({$dados["bairro"]})";
	  		$dados["condominio_id"] = "0";
	  	}
	    $clientes[] = $dados;
	  }
	  return $clientes;
	}
	
	function buscaUsuarioRamal($ramal) {
		$query = "
		  SELECT
		    usu_nome AS nome
		  FROM
		    tab_usuarios
		  WHERE
		    usu_ramal = '$ramal' AND
		    uss_id = 1";
	  $query = $this->bancodedados->query($query);
	  $dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	    return $dados["nome"];
	  }
	}
	
	function buscaAgente($agente) {
		$query = "
		  SELECT
		    usu_nome AS nome
		  FROM
		    tab_usuarios
		  WHERE
		    usu_agente_id = '$agente' AND
		    uss_id = 1";
	  $query = $this->bancodedados->query($query);
	  $dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	    return $dados["nome"];
	  }
	}
	
	function formataTelefone($telefone) {
		if (strlen($telefone) == 8)
			return substr($telefone, 0, 4)."-".substr($telefone, 4, 4);
		if (strlen($telefone) == 10)
			return "(".substr($telefone, 0, 2).") ".substr($telefone, 2, 4)."-".substr($telefone, 6, 4);
		else
			return $telefone;
	}
	
  function logar() {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "login");
    $this->comando->add("Username", $this->usuario);
    $this->comando->add("Secret", $this->secret);
    $this->comando->add("Events", "off");
    $retorno = $this->executar(false);
    return (strpos($retorno, "Message: Authentication accepted") != false);
  }
  
  function logarAgente($ramal) {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "Originate");
    $this->comando->add("Channel", "SIP/{$ramal}");
    $this->comando->add("Context", "ramais_sip");
    $this->comando->add("Exten", "*8888{$this->agente_id}");
    $this->comando->add("Priority", "1");
    $this->comando->add("Callerid", "Login");
    $this->comando->add("Timeout", "20000");
    return $this->executar();
  }
  
  function deslogarAgente() {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "AgentLogoff");
    $this->comando->add("Agent", $this->agente_id);
    return $this->executar();
  }
  
  function pausarAgente($fila, $razao) {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "QueuePause");
    $this->comando->add("Interface", "agent/{$this->agente_id}");
    $this->comando->add("Paused", "true");
    $this->comando->add("Queue", $fila);
    $this->comando->add("Reason", $razao);
    return $this->executar();
  }
  
  function despausarAgente($fila) {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "QueuePause");
    $this->comando->add("Interface", "agent/{$this->agente_id}");
    $this->comando->add("Paused", "false");
    $this->comando->add("Queue", $fila);
    return $this->executar();
  }
  
  function discar($numero) {
    if (!$this->fp) return 0;
    if (strlen($numero) > 3 && $this->discador) $numero = $this->discador.$numero;
    $this->comando->add("Action", "Originate");
    $this->comando->add("Channel", $this->agente_id ? "Agent/{$this->agente_id}" : "SIP/{$this->ramal}");
    $this->comando->add("Context", "ramais_sip");
    $this->comando->add("Exten", $numero);
    $this->comando->add("Priority", "1");
    $this->comando->add("Callerid", $this->ramal);
    $this->comando->add("Timeout", "40000");
    return $this->executar();
  }
  
  function discarParaDestino($numero, $destino) {
  	$numero = "0".preg_replace("/[^0-9]/", "", $numero);
  	$destino = preg_replace("/[^0-9]/", "", $destino);
  	if (!$this->fp) return 0;
    $this->comando->add("Action", "Originate");
    $this->comando->add("Channel", "SIP/$numero@olefone");
    $this->comando->add("Context", "ramais_sip");
    $this->comando->add("Exten", $destino);
    $this->comando->add("Priority", "1");
    $this->comando->add("Callerid", $numero);
    $this->comando->add("Timeout", "40000");
    return $this->executar();
  }
  
  function transferir($destino) {
    if (!$this->fp) return 0;
    $channels = $this->getTransferChannels($destino);
    if ($channels) {
      $this->comando->add("Action", "Redirect");
      $this->comando->add("Channel", $channels["origem"]);
      $this->comando->add("ExtraChannel", $channels["destino"]);
      $this->comando->add("Exten", $destino);
      $this->comando->add("Context", "default");
      $this->comando->add("Priority", "1");
      return $this->executar();
    }
  }
  
  function getTransferChannels($destino) {
    $retorno = $this->getChannels();
    if ($this->agente_id) {
      preg_match("/\bSIP\/{$this->agente_id}[^\s]*/i", $retorno, $origem);
    } else {
      preg_match("/\bAgent\/{$this->ramal}[^\s]*/i", $retorno, $origem);
    }
    preg_match("/\bSIP\/{$destino}[^\s]*/i", $retorno, $destino);
    return array("origem" => $origem, "destino" => $destino);
  }
  
  function monitorarFilas($fila = null) {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "QueueStatus");
    if ($fila) {
      $this->comando->add("Queue", $fila);
    }
    return $this->executar();
  }
  
  function getChannels() {
    if (!$this->fp) return 0;
    $this->comando->add("Action", "Command");
    $this->comando->add("Command", "core show channels");
    return $this->executar(false);
  }
  /**
   * Conecta no Asterisk Manager
   */
  function conectar() {
  	$errno = null;
  	$errstr = null;
    $this->fp = fsockopen($this->endereco_ip, $this->porta, $errno, $errstr, 20);
    if (!$this->fp) {
      return 0;
    }
    stream_set_timeout($this->fp, 1);
    $retorno = fgets($this->fp);
    if ($this->verbose) {
      print $retorno."\r\n";
    }
    return $retorno;
  }
  /**
   * Conecta comandos no buffer
   */
  function executar($formatar_retorno = true) {
    $comando = $this->comando->get();
    if ($this->verbose) {
      print $comando;
    }
    fwrite($this->fp, $comando);
    $retorno = stream_get_contents($this->fp);
    if ($this->verbose) {
      print $retorno;
    }
    if ($formatar_retorno) {
      return $this->formataRetorno($retorno);
    } else {
      return $retorno;
    }
  }  
  /**
   * Formata retorno
   */
  function formataRetorno($retorno) {
    $lista = array();
    $registro = array();
    $retorno = preg_split("/\r?\n/", trim($retorno));
    for ($i = 0; $i < count($retorno); $i++) {
      if ($retorno[$i]) {
        $retorno[$i] = preg_split("/: /", $retorno[$i]);
        $registro[$retorno[$i][0]] = $retorno[$i][1];
      } else {
        $lista[] = $registro;
        $registro = array();
      }
    }
    if (!$lista && $registro) {
      $lista[] = $registro;
    }
    return $lista;
  }

}

class Comando {
  
  var $buffer;
  
  function Comando() {
    $this->buffer = array();
  }
  
  function add($command, $value) {
    $this->buffer[] = "{$command}: $value";
  }
  
  function get() {
    $comando = implode($this->buffer, "\r\n")."\r\n\r\n";
    $this->buffer = array();
    return $comando;
  }
  
}

?>
