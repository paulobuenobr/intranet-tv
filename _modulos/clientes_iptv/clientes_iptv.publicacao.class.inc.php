<?

set_time_limit(0);
ini_set("default_socket_timeout", 600);

if ($_GET["debug"]) {
	header('Content-Type: text/html; charset=utf-8');
	print "<PRE>";
}

class clientes_iptv_publicacao {

	var $url;
	var $vendor_username;
	var $vendor_password;
	var $bancodedados;

	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;
                
		$this->buscaDadosAcessosIntegracao();
		
	}
        
	function buscaDadosAcessosIntegracao() {
		
		$sql = "SELECT * FROM tab_integracao_acesso WHERE tipo_acesso LIKE 'iptv_drm' AND status IS TRUE LIMIT 1";
		$query = $this->bancodedados->query($sql);

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->url = $dados['url'];
			$this->vendor_username = $dados['usuario'];
			$this->vendor_password = $dados['senha'];

			if ($_GET["debug"]) {
				print "url: {$this->url}\n";
				print "vendor_username: {$this->vendor_username}\n";
				print "vendor_password: {$this->vendor_password}\n\n";
			}

		}

	}

	##############
	### Status ###
	##############

	function teste_integracao() {

		// Publica vendor

		$vendorReference = "1234567890";
		print("<PRE>");
		print("Publicando Vendor 'Teste Integração'... ");
		flush();

		$vendor = array(
			"vendorReference" => "1234567890",
			"name" => utf8_encode("Teste Integração"),
			"packages" => "61,62,63,64",
			"catalogs" => "1,5,9",
			"hasVOD" => "true",
			"restrictCatalogs" => "false"
		);

		if ($this->vendorPublish($vendor)) {
			print("<span style=\"color:green\">OK</span>\n");
			flush();
		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}

		// Remove accounts

		print("Buscando accounts existentes... ");

		$retorno = $this->executar("account/list", array("vendorReference" => $vendor["vendorReference"]));

		if ($retorno["status"] == "success") {

			print("<span style=\"color:green\">OK</span>\n");
			flush();

			$accounts = $retorno["records"];

			foreach ($accounts as $account) {
				print("Removendo account '".iconv('UTF-8', 'ISO-8859-1', $account["name"])."'... ");
				flush();
				if ($this->accountRemove($account["reference"], $account["id"])) {
					print("<span style=\"color:green\">OK</span>\n");
					flush();
				} else {
					print("<span style=\"color:red\">ERRO</span>\n");
					die();
				}
			}
	
		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}

		// Publica account

		$account = array(
			"reference" => "1234567890",
			"vendorReference" => "1234567890",
			"name" => utf8_encode("Conta Teste Integração"),
			"information" => utf8_encode("[1234567890] Conta Teste Integração"),
			"packages" => "61,62,63,64",
			"catalogs" => "1,5,9",
			"macAddresses" => "ABCDEF123456",
			"maxDevices" => 10,
			"vod" => "ACTIVE",
			"channelDisponibility" => "IF_APPROVED",
			"username" => "teste@integracao",
			"status" => "ACTIVE"
		);

		print("Publicando account '".iconv('UTF-8', 'ISO-8859-1', $account["name"])."'... ");
		flush();

		if ($this->accountPublish($account)) {
			print("<span style=\"color:green\">OK</span>\n");
			flush();
		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}

		// Altera account	
	
		$account = array(
			"reference" => "1234567890",
			"vendorReference" => "1234567890",
			"name" => utf8_encode("Conta Teste Integração 2"),
			"information" => utf8_encode("[1234567890] Conta Teste Integração 2"),
			"packages" => "61",
			"catalogs" => "1",
			"macAddresses" => "123456ABCDEF",
			"maxDevices" => 1,
			"vod" => "ACTIVE",
			"channelDisponibility" => "IF_APPROVED",
			"username" => "teste2@integracao",
			"status" => "INACTIVE"
		);

		print("Alterando account '".iconv('UTF-8', 'ISO-8859-1', $account["name"])."'... ");
		flush();

		if ($this->accountPublish($account)) {
			print("<span style=\"color:green\">OK</span>\n");
			flush();
		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}
	
		// Confirmando alteração

		print("Confirmando alteração... ");
		flush();

		$retorno = $this->executar("account/list", array("vendorReference" => $vendor["vendorReference"]));

		if ($retorno["status"] == "success") {

			$accounts = $retorno["records"];

			foreach ($accounts as $dados) {
				
				if ($dados["name"] == $account["name"] &&
					$dados["information"] == $account["information"] &&
					$dados["packages"] == $account["packages"] &&
					$dados["catalogs"] == $account["vodCatalogs"] &&
					$dados["macAddresses"] == $account["macAddresses"] &&
					$dados["maxDevices"] == $account["maxDevices"] &&
					$dados["vod"] == $account["vod"] &&
					$dados["channelDisponibility"] == $account["channelDisponibility"] &&
					$dados["status"] == $account["status"]) {
					print("<span style=\"color:green\">OK</span>\n");
					flush();
				} else {
					print("<span style=\"color:red\">ERRO</span>\n");
					die();
				}
			}

		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}

		// Altera senha

		print("Alterando senha... ");
		flush();

		$resultado = $this->executar("account/changePassword", array("reference" => $account["reference"], "password" => "1234567890"));

		if ($resultado["status"] == "success") {
			print("<span style=\"color:green\">OK</span>\n");
			flush();
		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}

		// Remove account

		print("Removendo account... ");
		flush();
		
		if ($this->accountRemove($account["reference"])) {
			print("<span style=\"color:green\">OK</span>\n");
			flush();
		} else {
			print("<span style=\"color:red\">ERRO</span>\n");
			die();
		}

	}

	##############
	### Status ###
	##############

	function status($iptv_id) {

		if (($resultado = $this->executar("account/status", array("reference" => $iptv_id))) !== false) {
			if ($resultado["status"] == "success") {
				return $resultado["records"];
			}
		}

	}

	#####################
	### Alterar Senha ###
	#####################

	function alterarSenha($iptv_id, $senha) {
		
		if (($resultado = $this->executar("account/changePassword", array("reference" => $iptv_id, "password" => $senha))) !== false) {
			return $resultado["status"] == "success";
		}

		return false;

	}

	###########################
	### Publicar Aguardando ###
	###########################
	
	function publicarAguardando() {
		
		$lista_local = $this->buscaIptvAguardando();

		foreach ($lista_local as $reference => $dados) {
			
			$this->publicarIptv($dados);

		}
		
	}

	###################
	### Sincronizar ###
	###################

	function sincronizar($origem_id = null) {

		$vendors = $this->buscaOrigens($origem_id);

		foreach ($vendors as $dados) {
			$this->sincronizarVendor($dados);
		}

		if ($_GET["debug"]) {
			die();
		}
		
	}
	
	function publicarVendor($origem_id) {

		$vendors = $this->buscaOrigens($origem_id);

		foreach ($vendors as $dados) {
			$this->vendorPublish($dados);
		}
		
	}

	
	function sincronizarVendor($dados) {

		if ($_GET["debug"]) {
			print "\nSincronizando vendor: {$dados["name"]}\n\n";
			flush();
		}
                
		$dadosOrigem = $dados;
		
		$this->vendorPublish($dados);

		$retorno = $this->executar("account/list", array("vendorReference" => $dados["vendorReference"]));

		if ($retorno["status"] == "success") {

			$lista_remota = array();
			
			foreach ($retorno["records"] as $dados) {
				if ($dados["reference"]) {
					$lista_remota[$dados["reference"]] = $dados;
				} else {
					$this->accountRemove($dados["reference"], $dados["id"]);
					if ($_GET["debug"]) {
						print "{$dados["reference"]} REMOVIDO {$dados["id"]} {$dados["information"]} {$dados["name"]}\n";
						flush();
					}
				}
			}

			$lista_local = $this->buscaIptvPorOrigem($dados["vendorReference"]);

			foreach ($lista_remota as $reference => $dados) {
				
				if (array_key_exists($reference, $lista_local)) {
  
					if ($dados["vendorReference"] != $lista_local[$reference]["vendorReference"] ||
						$dados["name"] != $lista_local[$reference]["name"] ||
						$dados["information"] != $lista_local[$reference]["information"] ||
						$dados["packages"] != $lista_local[$reference]["packages"] ||
						$dados["vodCatalogs"] != $lista_local[$reference]["catalogs"] ||
						$dados["apps"] != $lista_local[$reference]["apps"] ||
						$dados["macAddresses"] != $lista_local[$reference]["macAddresses"] ||
						$dados["maxDevices"] != $lista_local[$reference]["maxDevices"] ||
						$dados["vod"] != $lista_local[$reference]["vod"] ||
						$dados["channelDisponibility"] != $lista_local[$reference]["channelDisponibility"] ||
						$dados["username"] != $lista_local[$reference]["username"] ||
						$dados["status"] != $lista_local[$reference]["status"]) {

						$this->publicarIptv($lista_local[$reference]);

						if ($_GET["debug"]) {
							
							var_dump($dados);
							var_dump($lista_local[$reference]);
							
							print "{$reference} ATUALIZADO {$lista_local[$reference]["information"]} {$lista_local[$reference]["name"]}\n";
							flush();
							
						}

					}

				} else {

					$this->accountRemove($reference, $dados["id"]);

					if ($_GET["debug"]) {
						print "{$reference} REMOVIDO {$dados["id"]} {$dados["information"]} {$dados["name"]}\n";
						flush();
					}

				}
				
			}

			foreach ($lista_local as $reference => $dados) {
				
				if (!array_key_exists($reference, $lista_remota)) {

					$this->publicarIptv($dados);

					if ($_GET["debug"]) {
						print "{$reference} INSERIDO {$dados["information"]} {$dados["name"]}\n";
						flush();
					}

				}
				
			}

		} else {
			
			if ($_GET["debug"]) {
				print "ERROR Nao foi possivel capturar accounts\n";
				flush();
			}
			
		}
		
	}

	################
	### Publicar ###
	################

	function publicar($iptv_id) {

		$retorno = $this->publicarIptv($this->buscaDadosIptv($iptv_id));

		if ($_GET["debug"]) {
			die();
		}

		return $retorno;

	}

	function publicarIptv($dados) {

		if ($dados) {

			if ($dados["status"] == "CANCELED") {

				if ($this->accountRemove($dados["reference"])) {

					$this->bancodedados->query("
						UPDATE
							tab_clientes_iptv
						SET
							cip_publicado = 1
						WHERE
							cip_id = {$dados["reference"]}");

					$this->registraComentario($dados["reference"], "<b>Ação</b>: Removido");

					return true;

				}

			} else {

				if ($this->accountPublish($dados)) {

					$this->bancodedados->query("
						UPDATE
							tab_clientes_iptv
						SET
							cip_publicado = 1
						WHERE
							cip_id = {$dados["reference"]}");

					if(!empty($_SESSION["usuario"])) {
						$this->registraComentario($dados["reference"], "<b>Ação</b>: Publicado", $_SESSION["usuario"]);
					}

					return true;

				}

			}

		}

		return false;

	}

	#################
	### Consultas ###
	#################

	function buscaOrigens($origem_id = null) {

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS vendorReference,
				clo_degustacao AS degustacao,
				clo_plano_vod AS plano_vod,
				clo_vod_status AS vod_status,
				clo_vod_exibir_titulos AS vod_exibir_titulos,
				clo_inicio_degustacao AS inicio_degustacao,                                
				clo_degustacao_tv AS degustacao_tv,
				clo_plano_tv AS plano_tv,
				clo_inicio_degustacao_tv AS inicio_degustacao_tv,
				clo_nome AS name
			FROM
				tab_clientes_origens
			WHERE".($origem_id ? "
				clo_id = {$origem_id}" : "
				clo_id > 0")."
			ORDER BY
				clo_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["name"] = utf8_encode($dados["name"]);
			$dados["hasVOD"] = $dados["vod_status"] === 'ATIVO' ? 'true' : 'false';
			$dados["restrictCatalogs"] = $dados["vod_exibir_titulos"] === 'CONTRATADOS' ? 'true' : 'false';
			$dados["packages"] = $this->bancodedados->resultado($this->bancodedados->query("SELECT GROUP_CONCAT(ipt_codigo ORDER BY CAST(ipt_codigo AS UNSIGNED)) FROM `tab_iptv_pacotes` WHERE `ipt_tipo` = 'pacote'"));
			$dados["catalogs"] = $this->bancodedados->resultado($this->bancodedados->query("SELECT GROUP_CONCAT(ipt_codigo ORDER BY CAST(ipt_codigo AS UNSIGNED)) FROM `tab_iptv_pacotes` WHERE `ipt_tipo` = 'catalogo'"));

			$origens[] = $dados;
			
		}

		return $origens;

	}

	function buscaDadosIptv($iptv_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cip_id AS reference,
				tab_clientes.clo_id AS vendorReference,
				IF(tab_clientes.cli_nome_fantasia IS NOT NULL AND tab_clientes.cli_nome_fantasia != '', tab_clientes.cli_nome_fantasia, tab_clientes.cli_nome) AS name,
				CONCAT('[', LPAD(tab_clientes.cli_id, 6, '0'), ']') AS information,
				tab_clientes_iptv.cip_pacotes AS packages,
				tab_clientes_iptv.cip_catalogs AS catalogs,
				tab_clientes_iptv.cip_apps AS apps,
				(SELECT GROUP_CONCAT(clc_mac_address ORDER BY clc_mac_address ASC SEPARATOR ',') FROM tab_clientes_comodatos WHERE tab_clientes_comodatos.cor_id = tab_clientes_iptv.cor_id AND tab_clientes_comodatos.ccs_id IN (1, 2, 4)) AS macAddresses,
				(tab_clientes_iptv.cip_pontos + tab_clientes_iptv.cip_dispositivos) AS maxDevices,
				IF(tab_clientes_iptv.cip_vod_telas > 0, 'ACTIVE', 'INACTIVE') AS vod,
				IF(tab_clientes_iptv.cip_streaming = 1, 'IF_APPROVED_PREMIUM', 'IF_APPROVED') AS channelDisponibility,
				tab_clientes_iptv.cip_usuario AS username,
				IF(tab_clientes_iptv.cis_id = 1, 'ACTIVE', IF(tab_clientes_iptv.cis_id = 3, 'INACTIVE', 'CANCELED')) AS status,
				tab_clientes_origens.cos_id AS origem_status_id,
				tab_clientes_origens.clo_degustacao AS degustacao,
				tab_clientes_origens.clo_plano_vod AS plano_vod,
				tab_clientes_origens.clo_inicio_degustacao AS inicio_degustacao,
				tab_clientes_origens.clo_degustacao_tv AS degustacao_tv,
				tab_clientes_origens.clo_plano_tv AS plano_tv,
				tab_clientes_origens.clo_inicio_degustacao_tv AS inicio_degustacao_tv
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
			WHERE
				tab_clientes_iptv.cip_id = $iptv_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
          
			if (($dados["origem_status_id"] == 5) || 
				($dados["origem_status_id"] == 3 && $dados["packages"] == "61")) {
				$dados["status"] = "INACTIVE";
			}

			if(empty($dados["packages"])) {
				$dados["packages"] =  null;
			} else {
				$dados["packages"] = preg_split("/\s*,\s*/", $dados["packages"]);
				sort($dados["packages"]);
				$dados["packages"] = implode(",", $dados["packages"]);
			}

			if(empty($dados["catalogs"])) {
				$dados["catalogs"] =  null;
			} else {
				$dados["catalogs"] = preg_split("/\s*,\s*/", $dados["catalogs"]);
				sort($dados["catalogs"]);
				$dados["catalogs"] = implode(",", $dados["catalogs"]);
			}

			if(!$dados["apps"]) {
				$dados["apps"] = "";
			} else {
				$dados["apps"] = array_unique(preg_split("/\s*,\s*/", $dados["apps"]));
				sort($dados["apps"]);
				$dados["apps"] = implode(",", $dados["apps"]);
			}

			if($dados['plano_vod'] > 0 && $dados['degustacao'] > 0) {

				$agora = strtotime(date('Y-m-d'));
				$dataInicio = strtotime($dados['inicio_degustacao']);
				$expira = strtotime(date('Y-m-d', strtotime($dados['inicio_degustacao'] . " {$dados['degustacao']} day")));
				$dados["degustacaoVOD"] =  ($agora < $expira && $agora >= $dataInicio);
			
				if($dados["degustacaoVOD"]) {
					$lista_catalogs = ($dados["catalogs"]? $dados["catalogs"] . ',' . $dados['plano_vod']: $dados['plano_vod']);
					$lista_catalogs = explode(",", $lista_catalogs);
					$lista_catalogs = array_unique($lista_catalogs);
					sort($lista_catalogs);
					$lista_catalogs = implode(",", $lista_catalogs);

					$dados["catalogs"] = $lista_catalogs;
				}
				
			}

			if($dados['plano_tv'] > 0 && $dados['degustacao_tv'] > 0) {

				$agora = strtotime(date('Y-m-d'));
				$dataInicio = strtotime($dados['inicio_degustacao_tv']);
				$expira = strtotime(date('Y-m-d', strtotime($dados['inicio_degustacao_tv'] . " {$dados['degustacao_tv']} day")));
				$dados["degustacaoTV"] =  ($agora < $expira && $agora >= $dataInicio);
			
				if($dados["degustacaoTV"]) {
					
					$lista_packages = ($dados["packages"] ? $dados["packages"] . ',' . $dados['plano_tv'] : $dados['plano_tv']);
					$lista_packages = explode(",", $dados["packages"]);
					$lista_packages = array_unique($lista_packages);
					sort($lista_packages);
					$lista_packages = implode(",", $lista_packages);
					
					$dados["packages"] = $lista_packages;
					
				}
				
			}

			$dados["name"] = utf8_encode($dados["name"]);
			$dados["information"] = utf8_encode($dados["information"]);

			return $dados;
			
		}

	}

	function buscaIptvAguardando() {
		
		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cip_id AS reference
			FROM
				tab_clientes_iptv
			WHERE
				tab_clientes_iptv.cis_id IN (1, 3) AND
				tab_clientes_iptv.cip_publicado = 0
			LIMIT 200");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$lista[$dados["reference"]] = $this->buscaDadosIptv($dados["reference"]);

		}

		return $lista;

	}

	function buscaIptvPorOrigem($origem_id) {

		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cip_id AS reference
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
			WHERE
				tab_clientes.clo_id = $origem_id AND
				tab_clientes_iptv.cis_id IN (1, 3)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$lista[$dados["reference"]] = $this->buscaDadosIptv($dados["reference"]);

		}

		return $lista;

	}

	function registraComentario($iptv_id, $conteudo, $responsavel_id = null) {

		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		if (mb_check_encoding($conteudo, 'UTF-8')) {
			$conteudo = iconv('UTF-8', 'ISO-8859-1', $conteudo);
		}
		$conteudo = addslashes($conteudo);
		if(!empty($conteudo) && !empty($responsavel_id)) {
			$this->bancodedados->query("
					INSERT INTO
							tab_clientes_iptv_comentarios
					SET
							cic_datahora = NOW(),
							cic_conteudo = '$conteudo',
							cip_id = $iptv_id,
							usu_id = $responsavel_id");
		}
	}

	######################
	### Publish Vendor ###
	######################

	function vendorPublish($dados) {

		if (($resultado = $this->executar("vendor/publish", $dados)) !== false) {
			return $resultado["status"] == "success";
		}

		return false;

	}

	#######################
	### Publish Account ###
	#######################

	function accountPublish($dados) {

		if (($resultado = $this->executar("account/publish", $dados)) !== false) {
			return $resultado["status"] == "success";
		}

		return false;

	}

	###################
	### Kill Device ###
	###################

	function accountKillDevice($deviceId) {

		if (($resultado = $this->executar("account/killDevice", array("deviceId" => $deviceId))) !== false) {
			return $resultado["status"] == "success";
		}

		return false;

	}

	######################
	### Remove Account ###
	######################

	function accountRemove($reference, $id = 0) {

		if (($resultado = $this->executar("account/remove", array("reference" => $reference, "id" => $id))) !== false) {
			return $resultado["status"] == "success";
		}

		return false;

	}

	###############
	### Excutar ###
	###############

	function executar($servico, $dados, $metodo = "POST") {

		$dados = http_build_query($dados);
		
		if ($_GET["debug"]) {
			print "Executando: {$this->url}/{$servico}\n";
			print "Dados: {$dados}\n";
			flush();
		}

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($curl, CURLOPT_POST, $metodo == "POST");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		if ($metodo == "POST") {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("VENDOR-USERNAME: {$this->vendor_username}", "VENDOR-PASSWORD: {$this->vendor_password}", "CONTENT-LENGTH: ".strlen($dados)));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $dados);
			curl_setopt($curl, CURLOPT_URL, "{$this->url}/{$servico}");
		} else {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("VENDOR-USERNAME: {$this->vendor_username}", "VENDOR-PASSWORD: {$this->vendor_password}", "CONTENT-LENGTH: 0"));
			curl_setopt($curl, CURLOPT_URL, "{$this->url}/{$servico}?{$dados}");
		}
		
		$retorno = curl_exec($curl);
		$codigo_http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($_GET["debug"]) {

			print "HTTP Response Code: {$codigo_http}\n";
			flush();

			if (!$retorno) {
				print "Erro: ".curl_error($curl)."\n";
				flush();
			}

		}

		curl_close($curl);

		$resultado = json_decode($retorno, true);

		if (json_last_error() === JSON_ERROR_NONE) {

			if ($_GET["debug"]) {
				print "Retorno: ".json_encode($resultado)."\n\n";
				flush();
			}

			return $resultado;

		} else {
			if ($_GET["debug"]) {
				if (!$retorno) {
					print "Erro: JSON Inválido: ".$retorno."\n\n";
					flush();
				}
			}
			
		}

		return false;

	}

}

?>
