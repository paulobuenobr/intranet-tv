<?

set_time_limit(0);
/*
date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$clientes_autenticacoes = controle::instanciaObjeto("cron_clientes_autenticacoes", $bancodedados);
$clientes_autenticacoes->capturaConcentradores();
*/
class cron_clientes_autenticacoes {

	var $bancodedados;
	var $clientes_autenticacoes;
	
	var $mh = null;
	var $handles = array();

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->clientes_autenticacoes = controle::instanciaObjeto("clientes_autenticacoes", $this->bancodedados, null, false);
	}
		
	#####################
	### Publica Todos ###
	#####################
	
	function publicarConcentradores2() {
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao2", $this->bancodedados);
		$publicacao->publicarConcentradores();
	}
	
	function publicarConcentradores() {
		if (array_key_exists("concentradores", $_GET) && $_GET["concentradores"]) {
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao2", $this->bancodedados);
    	$publicacao->publicarConcentradores($_GET["concentradores"]);
		} else {
			print "<ul>";
			$this->mh = curl_multi_init();
			$concentradores = $this->buscaConcentradores(true);
			$concentradores_id = array();
			$pontos = 0;
			for ($i = 0; $i < count($concentradores); $i++) {
				$concentradores_id[] = $concentradores[$i]["id"];
				$pontos += $concentradores[$i]["pontos"];
				if ($pontos > 300 || $i == count($concentradores) - 1) {
					$this->adicionaPublicacao(implode(",", $concentradores_id));
					$concentradores_id = array();
					$pontos = 0;
				}
			}
			print "</ul>";
			// Envia requisições
			$running = null;
			do {
				curl_multi_exec($this->mh, $running);
			} while ($running > 0);
			for ($i = 0; $i < count($this->handles); $i++) {
				curl_multi_remove_handle($this->mh, $this->handles[$i]);
			}
			curl_multi_close($this->mh);
		}
	}
	
	function adicionaPublicacao($concentradores) {
		$url = "http://intranet.tifloripa.com.br/cron.php?i=45&concentradores=$concentradores";
		print "<li>$url</li>";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 900);
		curl_multi_add_handle($this->mh, $ch);
		$this->handles[] = $ch;
	}
	
	function buscaConcentradores($todos = false) {
		$concentradores = array();
    $query = "
      SELECT
				tab_servidores.ser_id AS id,
				(SELECT COUNT(*) FROM tab_clientes_autenticacoes WHERE tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id AND tab_clientes_autenticacoes.cas_id IN (1, 2)) AS pontos
      FROM
      	tab_servidores
      WHERE
      	tab_servidores.set_id = 1 AND
				-- tab_servidores.ser_id NOT IN (610) AND
      	(SELECT COUNT(*) FROM tab_clientes_autenticacoes WHERE tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id AND tab_clientes_autenticacoes.cas_id IN (1, 2)) > 0".($todos ? "" : " AND
       	tab_servidores.ser_publicado = 0")."
      ORDER BY
        pontos DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$concentradores[] = $dados;
    }
    return $concentradores;
	}
	
	###############################
	### Publica Clientes Emails ###
	###############################

	function publicaClientesAutenticacoes() {
		
		$this->bancodedados->query("
			UPDATE
				tab_clientes_autenticacoes
				INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
				INNER JOIN tab_servidores ON (tab_servidores_interfaces.ser_id = tab_servidores.ser_id)
			SET
				tab_clientes_autenticacoes.ser_id = tab_servidores_interfaces.ser_id,
				tab_clientes_autenticacoes.sei_id = tab_condominios_blocos.sei_id,
				tab_clientes_autenticacoes.cau_concentrador_nome = tab_servidores.ser_nome,
				tab_clientes_autenticacoes.cau_publicado = 0
			WHERE
				tab_clientes_autenticacoes.cau_id NOT IN (16685, 15689) AND
				tab_clientes_autenticacoes.cas_id IN (1, 2) AND
				tab_planos.pls_id IN (1, 32, 35) AND
				(tab_clientes_autenticacoes.sei_id IS NULL OR tab_condominios_blocos.sei_id != tab_clientes_autenticacoes.sei_id)");
		
		$this->bancodedados->query("
			UPDATE
			  tab_clientes_autenticacoes
			  INNER JOIN tab_servidores ON (tab_servidores.ser_id = tab_clientes_autenticacoes.ser_id)
			  INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			SET
				tab_clientes_autenticacoes.cau_concentrador_nome = tab_servidores.ser_nome,
				tab_clientes_autenticacoes.cau_concentrador_ip = CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip)");

		$this->bancodedados->query("
			UPDATE
			  tab_clientes_autenticacoes
			  INNER JOIN tab_servidores_ssids ON (tab_clientes_autenticacoes.ssi_id = tab_servidores_ssids.ssi_id)
			SET
			  tab_clientes_autenticacoes.cau_equipamento_ssid = tab_servidores_ssids.ssi_nome");
				
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao2", $this->bancodedados);
		
		$autenticacoes = $this->buscaClientesAutenticacoes();
		
		if ($autenticacoes) {
			
			for ($i = 0; $i < count($autenticacoes); $i++) {
				
				$publicacao->publicarAutenticacao($autenticacoes[$i]);
				
			}
			
		}
		
	}

	function buscaClientesAutenticacoes($aguardando_publicacao = true) {
   $query = "
      SELECT
		cau_id AS id
      FROM
      	tab_clientes_autenticacoes
      WHERE".($aguardando_publicacao ? "
       	cau_publicado = 0" : "
       	cas_id IN (1, 2)")."
      ORDER BY
        RAND()";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$autenticacoes[] = $dados["id"];
    }
    return $autenticacoes;
	}
	
	function capturaConcentradores() {
		$concentradores = array();
    $query = "
      SELECT
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_emails.cle_usuario AS usuario,
				tab_dominios.dom_dominio AS dominio
      FROM
      	tab_clientes_autenticacoes
  			INNER JOIN tab_clientes_emails ON (tab_clientes_autenticacoes.cle_id = tab_clientes_emails.cle_id)
  			INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_clientes_autenticacoes.cas_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["concentrador_ip"] = $this->publicacao->buscaIPConcentrador($dados["usuario"], $dados["dominio"]);
    	if (array_key_exists($dados["concentrador_ip"], $concentradores)) {
    		$dados["concentrador_nome"] = $concentradores[$dados["concentrador_ip"]];
    	} else {
    		$dados["concentrador_nome"] = $this->publicacao->buscaConcentradorNome($dados["concentrador_ip"]);
    		$concentradores[$dados["concentrador_ip"]] = $dados["concentrador_nome"];
    	}
    	if ($dados["concentrador_nome"] || $dados["concentrador_ip"]) {
    		$this->bancodedados->query("UPDATE tab_clientes_autenticacoes SET cau_concentrador_ip = '{$dados["concentrador_ip"]}', cau_concentrador_nome = '{$dados["concentrador_nome"]}', cau_concentrador_atualizacao = NOW() WHERE cau_id = {$dados["id"]}");
    	}
    }
	}
	
	function publicarTodos() {
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao2", $this->bancodedados);
		$autenticacoes = $this->buscaClientesAutenticacoes(false);
		if ($autenticacoes) {
			for ($i = 0; $i < count($autenticacoes); $i++) {
				$publicacao->publicarAutenticacao($autenticacoes[$i]);
			}
		}
	}
	
	#################
	### Verificar ###
	#################

	function verificar() {
		$clientes_autenticacoes = controle::instanciaObjeto("clientes_autenticacoes", $this->bancodedados, null, false);
		$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, null, false);
		$query = "
      SELECT
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_equipamento_ip AS ip,
				tab_assinaturas.cli_id AS cliente_id
      FROM
      	tab_clientes_autenticacoes
      	INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
      WHERE
        tab_clientes_autenticacoes.cas_id = 1 AND
        tab_clientes_autenticacoes.cau_equipamento_ip IS NOT NULL AND
        tab_clientes_autenticacoes.cau_data_alteracao <= CURDATE()";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
  		$verificacao = $clientes_autenticacoes->verificarConexao($dados["ip"]);

  		if ($verificacao["erro"]) {
  			continue;
  		}
  		
  		$autorizado = true;
  		
  		for ($i = 0; $i < count($verificacao); $i++) {
  			$autorizado = $autorizado && $verificacao[$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL && $verificacao[$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ && $verificacao[$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL;
  			
  			$signal_formatado[] = $verificacao[$i]["signal"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$verificacao[$i]["signal"]}[/verde]" : "[vermelho]{$verificacao[$i]["signal"]}[/vermelho]";
  			$ccq_formatado[] = $verificacao[$i]["ccq"] >= clientes_autenticacoes::MIN_CCQ ? "[verde]{$verificacao[$i]["ccq"]}[/verde]" : "[vermelho]{$verificacao[$i]["ccq"]}[/vermelho]";
  			$apsignal_formatado[] = $verificacao[$i]["apsignal"] >= clientes_autenticacoes::MIN_SIGNAL ? "[verde]{$verificacao[$i]["apsignal"]}[/verde]" : "[vermelho]{$verificacao[$i]["apsignal"]}[/vermelho]";
  		}
  		
  		$signal_formatado = implode(", ", $signal_formatado);
  		$apsignal_formatado = implode(", ", $apsignal_formatado);
  		$ccq_formatado = implode(", ", $ccq_formatado);
  		
			// Comentario Verificação
			if ($signal_formatado) {
				$comentario_verificacao[] = "<b>Signal</b>: ".str_replace(array("[verde]", "[/verde]", "[vermelho]", "[/vermelho]"), array("<span style=\"color:green\">", "</span>", "<span style=\"color:red\">", "</span>"), $signal_formatado);
			}
			if ($apsignal_formatado) {
				$comentario_verificacao[] = "<b>AP Signal</b>: ".str_replace(array("[verde]", "[/verde]", "[vermelho]", "[/vermelho]"), array("<span style=\"color:green\">", "</span>", "<span style=\"color:red\">", "</span>"), $apsignal_formatado);
			}
			if ($ccq_formatado) {
				$comentario_verificacao[] = "<b>CCQ</b>: ".str_replace(array("[verde]", "[/verde]", "[vermelho]", "[/vermelho]"), array("<span style=\"color:green\">", "</span>", "<span style=\"color:red\">", "</span>"), $ccq_formatado);
			}
			if ($comentario_verificacao) {
				$comentario_verificacao = "<b>Ação</b>: Verificação automática da qualidade do sinal<br>".implode("<br>", $comentario_verificacao);
				$clientes_autenticacoes->registraComentario($dados["id"], $comentario_verificacao, null);
			}
			
			if (!$autorizado) {
		    // Gera Dados Chamado
	    	$dados_chamado["solicitante"] = "Sistema (Intranet)";
			  $dados_chamado["categoria"] = 1052454; // Técnico (Diretor)
			  $dados_chamado["prioridade"] = 3;
			  $dados_chamado["tipo"] = 5;
    		$dados_chamado["sistema"] = true;
			  $dados_chamado["conteudo"] = "Ponto sem padrões mínimos de qualidade.\n\n";
			  $dados_chamado["conteudo"] .= "Dados do equipamento:";
    		$dados_chamado["conteudo"] .= "[d]";
			  $dados_chamado["conteudo"] .= "[n]Modelo:[/n] {$verificacao[0]["platform"]}\n";
			  $dados_chamado["conteudo"] .= "[n]Nome:[/n] {$verificacao[0]["devicename"]}\n";
			  $dados_chamado["conteudo"] .= "[n]MAC:[/n] {$verificacao[0]["apmac"]}\n";
			  $dados_chamado["conteudo"] .= "[n]SSID:[/n] {$verificacao[0]["essid"]}";
    		$dados_chamado["conteudo"] .= "[/d]";
			  $dados_chamado["conteudo"] .= "Qualidade do sinal:";
			  $dados_chamado["conteudo"] .= "[d]";
			  $dados_chamado["conteudo"] .= "[n]Signal:[/n] $signal_formatado\n";
			  $dados_chamado["conteudo"] .= "[n]AP Signal:[/n] $apsignal_formatado\n";
			  $dados_chamado["conteudo"] .= "[n]CCQ:[/n] $ccq_formatado";
			  $dados_chamado["conteudo"] .= "[/d]";
    		// Adiciona Chamado
			  $chamado_id = $chamados->adicionarChamado($dados["cliente_id"], null, $dados_chamado);
			  // Solicitação de fechamento
			  $chamados->registraEtapa($chamados->buscaDadosChamado($chamado_id, true), 16, array("responsavel_fechamento" => "4,4,12"));
			}
			
			$this->bancodedados->query("UPDATE tab_clientes_autenticacoes SET cau_data_alteracao = NULL WHERE cau_id = {$dados["id"]}");
			
    }
	}	
	
	
	
}

?>
