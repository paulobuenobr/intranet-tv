<?

/*
date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$integracao = controle::instanciaObjeto("clientes_telefones.integracao", $bancodedados);
var_dump($integracao->registrar(90521));
//*/

class clientes_telefones_integracao {

	var $bancodedados;
	var $bancodedados_opensips;
	
	var $usuarios = array(array("inicial" => 30398601, "final" => 30398800));
	
	var $sip_domain = "sip.oletelecom.com.br";
	
	
	function clientes_telefones_integracao($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->bancodedados_opensips = controle::instanciaObjeto("bancodedados", true, "201.33.223.34", "adm_local", "@oleadmin!", "opensips");
	}

	function registrar($assinatura_id, $tabela_venda_id = null, $tabela_roteamento_id = null) {
		$assinatura = $this->buscaDadosAssinatura($assinatura_id);
		
    if (intval($assinatura["parametros"]["telefonia_tipo"])) {
    	$tipo_id = intval($assinatura["parametros"]["telefonia_tipo"]);
    } elseif ($assinatura["parametros"]["telefonia_tipo"] == "PRE") {
    	$tipo_id = 1;
    } elseif ($assinatura["parametros"]["telefonia_tipo"] == "POS") {
    	$tipo_id = 2;
    } elseif ($assinatura["parametros"]["telefonia_tipo"] == "CONTROLE") {
    	$tipo_id = 3;
    } else {
			return array("erro" => "Plano de telefonia sem tipo definido");
    }
		
		if (!$tabela_venda_id) {
			$tabela_venda_id = $assinatura["parametros"]["telefonia_tabela_venda_id"];
		}
		
		if (!$tabela_roteamento_id) {
			$tabela_roteamento_id = $assinatura["parametros"]["telefonia_tabela_roteamento_id"];
		}
		
		if ($assinatura["telefones"] >= $assinatura["parametros"]["telefonia_linhas"]) {
			return array("erro" => "Assinatura sem plano de telefonia disponível");
		}
		
		if (!$tabela_venda_id) {
			return array("erro" => "Tabela de venda não definida");
		}
		
		if (!$tabela_roteamento_id) {
			return array("erro" => "Tabela de roteamento não definida");
		}
		
		$cliente = $this->buscaDadosCliente($assinatura["cliente_id"]);
		
		$usuario = $this->buscaUsuarioLivre();
		
		if (!$usuario) {
			return array("erro" => "Sem usuário disponível na plataforma de telefonia");
		}

		$senha = $this->gerarSenha();
		
		$customer_id = $this->buscaCustomerId($assinatura["cliente_id"]);
		
		if (!$customer_id) {
			
			$codigo_cidade = $this->buscaCodigoCidade($cliente["estado"], $cliente["cidade"]);
			$codigo_cidade = $codigo_cidade ? $codigo_cidade : "NULL";
			
			$query = "
				INSERT INTO
					customers (id, name, login, password, address, district, state_id, city_id, phone_number, email, cpf, cnpj, inscricao_estadual, inscricao_municipal)
				VALUES (NULL, '[{$cliente["id"]}] {$cliente["nome"]}', '{$usuario}', '{$senha}', '{$cliente["endereco"]}', '{$cliente["bairro"]}', '{$cliente["estado"]}', $codigo_cidade, '{$cliente["telefone"]}', '{$cliente["email"]}', '{$cliente["cpf"]}', '{$cliente["cnpj"]}', '', '')";
			$this->bancodedados_opensips->query($query);
			$customer_id = $this->bancodedados_opensips->ultimoIdInserido();
	
			$query = "
				INSERT INTO
					users (id, login, senha, usertype, block_total_sales)
				VALUES (NULL, '{$usuario}', MD5('$senha'), '3', 0)";
			$this->bancodedados_opensips->query($query);
		}
			
		$query = "
			INSERT INTO
				subscriber (id, username, domain, password, email_address, ha1, ha1b, rpid, customer_id, quota)
			VALUES (NULL, '{$usuario}', '{$this->sip_domain}', '{$senha}', '', '', '', NULL, $customer_id, '0.000000')";
		$this->bancodedados_opensips->query($query);
		$subscriber_id = $this->bancodedados_opensips->ultimoIdInserido();
		
		$query = "
			INSERT INTO
				user_profiles (id, username_ipaddress, techprefix, group_id, callerid, fixcallerid00, customer_id, table_id, max_channels)
			VALUES (NULL, '{$usuario}@{$this->sip_domain}', '', $tabela_roteamento_id, '', 0, $customer_id, $tabela_venda_id, 200)";
		$this->bancodedados_opensips->query($query);
		
		$query = "
			INSERT INTO
				remote_number (id, number, customer_id, service_id, type_service)
			VALUES (NULL, '5548{$usuario}', $customer_id, $subscriber_id, 2)";
		$this->bancodedados_opensips->query($query);
		
		$query = "
			INSERT INTO
				prepaid (id, reseller_id, account, balance, change_date, active_sessions, session_counter, max_sessions, postpaid, `usage`, quota_day, quota_month, current_global_timeout, last_global_update, call_count, low_balance_notify_enable, low_balance_notify, low_balance_notified)
			VALUES (NULL, 0, '{$usuario}@{$this->sip_domain}', '0.0000', NOW(), '', 0, 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '0.000000', 0)";
		$this->bancodedados_opensips->query($query);
			
		fopen("http://sip.oletelecom.com.br/painel/index.php?action=Aplicar", "r");
		
		$query = "
			INSERT INTO
				tab_clientes_telefones (clt_numero, clt_publicado, ctt_id, ass_id, cts_id)
			VALUES
				($usuario, 1, $tipo_id, {$assinatura["id"]}, 1)";
		$this->bancodedados->query($query);
		return $this->bancodedados->ultimoIdInserido();
	}
	
	function buscaCodigoCidade($estado, $cidade) {
		$query = "
			SELECT
			  id
			FROM
				cidade
			WHERE
				cod = '$estado' AND
				name = '$cidade'";
		$query = $this->bancodedados_opensips->query($query);
		return $this->bancodedados_opensips->resultado($query, 0, 0);
	}
	
	function buscaCustomerId($cliente_id) {
		$query = "
			SELECT
				id
			FROM
				customers
			WHERE
				name LIKE '[$cliente_id]%'
			LIMIT 1";
		$query = $this->bancodedados_opensips->query($query);
		return $this->bancodedados_opensips->resultado($query, 0, 0);
	}
	
	function buscaDadosAssinatura($assinatura_id) {
		$query = "
		  SELECT
		  	ass_id AS id,
		  	cli_id AS cliente_id,
		  	ass_parametros AS parametros,
		  	(SELECT COUNT(*) FROM tab_clientes_telefones WHERE tab_clientes_telefones.ass_id = tab_assinaturas.ass_id) AS telefones
		  FROM
		  	tab_assinaturas
		  WHERE
		    tab_assinaturas.ass_id = $assinatura_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}		
	}
	
	function buscaDadosCliente($cliente_id) {
		$query = "
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				IF(tab_clientes.clt_id = 1, REPLACE(REPLACE(REPLACE(tab_clientes.cli_cpf_cnpj, '.', ''), '-', ''), '/', ''), '') AS cpf,
				IF(tab_clientes.clt_id = 2, REPLACE(REPLACE(REPLACE(tab_clientes.cli_cpf_cnpj, '.', ''), '-', ''), '/', ''), '') AS cnpj,
				IF(tab_clientes.clt_id = 1, 'F', 'J') AS tipo_pessoa,
				CONCAT(CAST(tab_logradouros.log_nome AS CHAR CHARACTER SET utf8), ', ', IF(tab_clientes.cli_numero > 0, tab_clientes.cli_numero, 'S/N')) AS endereco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
        INSERT(LPAD(tab_clientes.cep_id, 8, '0'), 6, 0, '-') AS cep,
				tab_estados.est_sigla AS estado,
				(SELECT CONCAT(ccm_usuario, '@', ccm_dominio) FROM tab_clientes_contatos_emails WHERE tab_clientes_contatos_emails.cli_id = tab_clientes.cli_id ORDER BY cca_id LIMIT 0, 1) AS email,
				(SELECT CONCAT('(', ccl_ddd, ') ', ccl_numero) FROM tab_clientes_contatos_telefones WHERE tab_clientes_contatos_telefones.cli_id = tab_clientes.cli_id AND ccf_id > 0 ORDER BY ccf_id LIMIT 1) AS telefone
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_clientes.cep_id IS NOT NULL, tab_clientes.cep_id, IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_condominios.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes.cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
		return $this->bancodedados->resultadoArray($query);
	}
	
	function buscaUsuarioLivre() {
		$proximo = 0;
		foreach ($this->usuarios as $usuario) {
			$proximo = $usuario["inicial"];
			$query = "
				SELECT
					username
				FROM
					subscriber
				WHERE
					username BETWEEN {$usuario["inicial"]} AND {$usuario["final"]}
				ORDER BY
				  username";
			$query = $this->bancodedados_opensips->query($query);
			while (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
				if ($dados["username"] > $proximo) {
					return $proximo;
				}
				$proximo = $dados["username"] + 1;
			}
		}
		return $proximo;
	}
	
	function buscaNumeroMaximo($inicio, $fim) {
		$query = "
			SELECT
				MAX(username) AS numero
			FROM
				subscriber
			WHERE
				status = 'A' AND
				username BETWEEN $inicio AND $fim";
		$query = $this->bancodedados_opensips->query($query);
		if (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
			return $dados["numero"];
		}
	}
	
	function gerarSenha($caracteres = 6, $letras_minusculas = true, $letras_maiusculas = false, $numeros = true, $outros = null) {
		$seed = "";
		if ($letras_minusculas) {
			$seed .= "abcdefghijklmnopqrstuvwxyz";
		}
		if ($letras_maiusculas) {
			$seed .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		}
		if ($numeros) {
			$seed .= "0123456789";
		}
		if ($outros) {
			$seed .= $outros;
		}
		$seed_size = strlen($seed) - 1;
		$senha = "";
		for ($i = 0; $i < $caracteres; $i++) {
			$senha .= $seed[rand(0, $seed_size)];
		}
		return $senha;
	}
	
	function cancelar($assinatura_id) {
		
	}
	
	function inserirCredito($usuario, $valor, $observacao, $validade_meses = null) {
		$valor = intval(preg_replace('/[^\-0-9]/', "", $valor));
		if ($valor != 0) {
			$vencimento = $validade_meses ? "DATE_SUB(DATE_ADD(CURDATE(), INTERVAL $validade_meses MONTH), INTERVAL 1 DAY)" : "NULL";
			$saldo_remanescente = $this->bancodedados_opensips->resultado($this->bancodedados_opensips->query("SELECT balance FROM prepaid WHERE account = '$usuario@{$this->sip_domain}'"), 0, 0);
			$cliente_telefone_id = $this->bancodedados->resultado($this->bancodedados->query("SELECT clt_id FROM tab_clientes_telefones WHERE clt_numero = $usuario AND cts_id = 1"), 0, 0);
			$usuario_id = $_SESSION["usuario"] ? $_SESSION["usuario"] : "NULL";
			// Insere registro transação
			$this->bancodedados->query("
				INSERT INTO
					 tab_clientes_telefones_recargas (ctr_data_recarga, ctr_data_vencimento, ctr_valor_recarga, ctr_valor_saldo_remanescente, ctr_observacao, usu_id, clt_id)
				VALUES (CURDATE(), $vencimento, $valor, $saldo_remanescente, '$observacao', $usuario_id, $cliente_telefone_id)");
			// Atualiza crédito
			$this->bancodedados_opensips->query("
				UPDATE
					prepaid
				SET
					balance = balance + ($valor / 100),
					change_date = NOW()
				WHERE
				  account = '$usuario@{$this->sip_domain}'");
		}
	}
	
	function verificarCredito($assinatura_id) {
		
	}
	
	function verificarStatus($assinatura_id) {
		
	}
	
	function buscaTabelasVendas() {
		$query = "
		  SELECT
		  	id,
		  	name
		 	FROM
	 	  	billing_rate_table
		ORDER BY
	 	  name";
    $query = $this->bancodedados_opensips->query($query);
    while (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
    	$tabelas[] = $dados;
    }
    return $tabelas;
	}
			
	function buscaTabelaVenda($tabela_id) {
		$query = "
		  SELECT
		  	name
		 	FROM
	 	  	billing_rate_table
	 	  WHERE
	 	  	id = $tabela_id";
    $query = $this->bancodedados_opensips->query($query);
    return $this->bancodedados_opensips->resultado($query, 0, 0);
	}
	
	function buscaTabelasRoteamento() {
		$query = "
		  SELECT
		  	id,
		  	name
		 	FROM
	 	  	groups
	 	  WHERE
	 	  	deleted = 0
	 	  ORDER BY
	 	  	name";
    $query = $this->bancodedados_opensips->query($query);
    while (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
    	$tabelas[] = $dados;
    }
    return $tabelas;
	}	
		
	function buscaTabelaRoteamento($tabela_id) {
		$query = "
		  SELECT
		  	name
		 	FROM
	 	  	groups
	 	  WHERE
	 	  	id = $tabela_id";
    $query = $this->bancodedados_opensips->query($query);
    return $this->bancodedados_opensips->resultado($query, 0, 0);
	}
	
	function sincronizar() {
		$query = "
		  SELECT
		    tab_clientes.cli_id AS cliente_id,
		    tab_clientes.cli_nome AS cliente_nome,
		    tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
		    tab_assinaturas.ass_id AS assinatura_id,
		    tab_assinaturas.ast_id AS assinatura_status_id
		  FROM
		    tab_assinaturas
		    INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
		    INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		  WHERE
		  	tab_planos.pls_id = 14 AND
		  	tab_planos.pla_id IN (453, 463, 474, 488, 504, 510, 512, 515) AND
		  	tab_assinaturas.ast_id IN (1, 2)";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["cpf_cnpj"] = preg_replace("/[^0-9]/", "", $dados["cpf_cnpj"]);
			$dados_telefonia = $this->buscaDadosTelefonia($dados["cpf_cnpj"]);
			if ($dados_telefonia) {
				$grupo_id = $dados_telefonia["idGrupo"];
				$plano_id = $dados_telefonia["idPlano"] ? $dados_telefonia["idPlano"] : "NULL";
				$tabela_id = $dados_telefonia["idTabela"] ? $dados_telefonia["idTabela"] : "NULL";
				$numero = $dados_telefonia["username"];
				$senha = $dados_telefonia["password"];
				$assinatura_id = $dados["assinatura_id"];
				$status_id = $dados["assinatura_status_id"] == 3 ? 4 : 1;
				
				$query2 = "
					UPDATE
						grupo
					SET
						nome = '[{$dados["cliente_id"]}] {$dados["cliente_nome"]}',
						descricao = 'Intranet'
					WHERE
						id = $grupo_id";
				//print $query2."\n";
				//$this->bancodedados_opensips->query($query2);
				
				/*
				$query2 = "
					INSERT INTO
						tab_clientes_telefones (clt_grupo_id, clt_plano_id, clt_tabela_venda_id, clt_numero, clt_senha, ass_id, cts_id)
					VALUES
						($grupo_id, $plano_id, $tabela_id, $numero, '$senha', $assinatura_id, $status_id)";
				$this->bancodedados->query($query2);
				*/
				//var_dump($dados_telefonia);
				//print "OK {$dados["cliente_id"]} {$dados["cpf_cnpj"]}\n";
			} else {
				print "ERRO {$dados["cliente_id"]} {$dados["cpf_cnpj"]}\n";
			}
		}
	}
	
	function buscaDadosTelefonia($cpf_cnpj) {
		$query = "
		  SELECT
		  	subscriber.username,
		  	subscriber.password,
		  	usuario_grupo.idGrupo,
		  	gruposPosPago.idPlano,
		  	tabelaVendaGrupo.idTabela
		  FROM
		    subscriber
		    INNER JOIN usuario_grupo ON (subscriber.phplib_id = usuario_grupo.idUsuario)
		    LEFT OUTER JOIN gruposPosPago ON (usuario_grupo.idGrupo = gruposPosPago.idGrupo)
		    LEFT OUTER JOIN tabelaVendaGrupo ON (usuario_grupo.idGrupo = tabelaVendaGrupo.idGrupo AND tabelaVendaGrupo.status = 'A')
		  WHERE
		    subscriber.numInscricao = '$cpf_cnpj' AND
		    subscriber.status = 'A' AND
		    gruposPosPago.status = 'A'";
    $query = $this->bancodedados_opensips->query($query);
    if (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
    	return $dados;
    }
	}
	
	function buscaInfoTelefone($numero) {
		$query = "
			SELECT
				subscriber.password AS senha,
				location.contact AS ip,
				location.user_agent AS user_agent,
				DATE_FORMAT(location.expires, '%d/%m/%Y %H:%i:%s') AS expiracao,
				DATE_FORMAT(last_modified, '%d/%m/%Y %H:%i:%s') AS ultima_atualizacao,
				prepaid.balance * 100 AS saldo,
				(SELECT SUM(Price) FROM cdr.acc_".date("Ym")." WHERE UserName = CONCAT(subscriber.username, '@', subscriber.domain)) * 100 AS extrato,
				groups.id AS tabela_roteamento_id,
				groups.name AS tabela_roteamento_nome,
				billing_rate_table.id AS tabela_venda_id,
				billing_rate_table.name AS tabela_venda_nome
			FROM
				subscriber
				LEFT OUTER JOIN location ON (subscriber.username = location.username)
				LEFT OUTER JOIN user_profiles ON (CONCAT(subscriber.username, '@', subscriber.domain) = user_profiles.username_ipaddress)
				LEFT OUTER JOIN prepaid ON (user_profiles.prepaid_group_id = prepaid.id)
				LEFT OUTER JOIN groups ON (user_profiles.group_id = groups.id)
				LEFT OUTER JOIN billing_rate_table ON (user_profiles.table_id = billing_rate_table.id)
			WHERE
				subscriber.username = '$numero'";
    $query = $this->bancodedados_opensips->query($query);
    if (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
    	$dados["extrato"] = intval($dados["extrato"]);
    	$dados["saldo"] = intval($dados["saldo"]);
    	if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/s', $dados["ip"], $ip)) {
    		$dados["status_telefone"] = "<span style=\"color: green\">Online</span>";
    		$dados["ip"] = $ip[0];
    	} else {
    		$dados["status_telefone"] = "<span style=\"color: red\">Offline</span>";
    		$dados["ip"] = "";
    	}
    	return $dados;
    }
    return array();
	}
	
	function buscaExtratoMensal($numero, $mes, $ano) {
		$query = "
			SELECT
				(SELECT SUM(Price) FROM cdr.acc_{$ano}{$mes} WHERE UserName = CONCAT(subscriber.username, '@', subscriber.domain)) * 100 AS extrato
			FROM
				subscriber
			WHERE
				subscriber.username = '$numero'";
    $query = $this->bancodedados_opensips->query($query);
    if (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
    	return intval($dados["extrato"], 10);
    }
    return 0;
	}
	
	function buscaTelefonesOnline() {
		$query = "
			SELECT
				username
			FROM
				location";
    $query = $this->bancodedados_opensips->query($query);
    while (($dados = $this->bancodedados_opensips->resultadoArray($query)) != false) {
    	$telefones[] = $dados["username"];
    }
    return $telefones;
	}
	
	/**
	 * recalcula crédito proporcional de acordo com a data de ativação
	 * do contrato e atualiza na plataforma
	 */
	function ajustarCredito() {
		
	}
	
}

?>
