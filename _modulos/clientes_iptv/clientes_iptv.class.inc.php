<?

require_once("_global/modulo.class.inc.php");

class clientes_iptv extends modulo {

	var $dias_semana = array(
		1 => "Segunda",
		2 => "Terça",
		3 => "Quarta",
		4 => "Quinta",
		5 => "Sexta",
		6 => "Sábado",
		7 => "Domingo"
	);

	function __construct($bancodedados, $permissoes, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		$this->buscaDadosModulo(__CLASS__);

		if ($executar) {
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}

	}

	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($id) {
		
		$this->dados_item = $this->buscaDadosIptv($id);

	}
	
	function buscaDadosIptv($cliente_iptv_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cip_id AS id,
				tab_clientes_iptv.cip_usuario AS usuario,
				tab_clientes_iptv.cip_pontos AS pontos,
				tab_clientes_iptv.cip_dispositivos AS dispositivos,
				tab_clientes_iptv.cip_gravacao AS gravacao,
				tab_clientes_iptv.cip_pacotes AS pacotes,
				tab_clientes_iptv.cip_catalogs AS catalogs,
				tab_clientes_iptv.cip_apps AS apps,
				tab_clientes_iptv.cip_vod_telas AS vod_telas,
				tab_clientes_iptv.cip_publicado AS publicado,
				IF(tab_clientes_iptv.cip_recuperacao_datahora IS NULL OR cip_recuperacao_datahora <= DATE_SUB(NOW(), INTERVAL 5 MINUTE), 1, 0) AS eh_recuperavel,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.clo_id AS origem_id,
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS contrato_codigo,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_clientes_iptv_status.cis_id AS status_id,
				tab_clientes_iptv_status.cis_nome AS status_nome
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_contratos ON (tab_clientes_iptv.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_planos ON (tab_clientes_iptv.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes_iptv_status ON (tab_clientes_iptv.cis_id = tab_clientes_iptv_status.cis_id)
			WHERE
				tab_clientes_iptv.cip_id = $cliente_iptv_id".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["planos_a_la_carte"] = $this->buscaPlanosALaCarte($dados["contrato_id"]);
			$dados["planos_adicionais"] = $this->buscaPlanosAdicionais($dados["contrato_id"]);
			$dados["vod_catalogos"] = $this->buscaCatalogos($dados["catalogs"]);

			if ($dados["status_id"] == 3) {
				$dados["status_nome"] = "<span style=\"color:red\">{$dados["status_nome"]}</span>";
			}
			if ($dados["publicado"]) {
				$dados["status_nome"] .= " <span style=\"color:green\">(Publicado)</span>";
			} else {
				$dados["status_nome"] .= " <span style=\"color:red\">(Não Publicado)</span>";
			}
			
			return $dados;

		}

	}
	
	##################
	### Integração ###
	##################

	function ativarContrato($cliente_iptv_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cor_id as contrato_id,
				crs_id AS contrato_status_id
			FROM
				tab_contratos
			WHERE
				tab_contratos.cor_id = (
					SELECT
						cor_id
					FROM
						tab_clientes_iptv
					WHERE
						cip_id = {$cliente_iptv_id}
				)");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($dados["contrato_status_id"] <= 18) {
				
				$contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);

				$contratos->buscaDadosItem($dados["contrato_id"]);
				$contratos->ativarContrato();

			}
			
			return true;
			
		}
		
		return false;
		
	}
	
	function buscaUsuarioByHash($hash) {
		
		$query = $this->bancodedados->query("
			SELECT
				cip_usuario AS usuario
			FROM
				tab_clientes_iptv
			WHERE
				cip_recuperacao_hash = '{$hash}' AND
				cis_id = 1 AND
				cip_recuperacao_datahora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $dados["usuario"];
		}
		
	}
        
	function buscaHashByClienteIptv($clienteIptv) {
		$sql = "SELECT cip_recuperacao_hash AS hash
				FROM tab_clientes_iptv
				WHERE cip_id = '{$clienteIptv}' AND cip_recuperacao_datahora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
		
		$query = $this->bancodedados->query($sql);
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $dados["hash"];
		}
		return false;
	}


	function alterarSenha($hash, $senha, $ip) {
		
		$query = $this->bancodedados->query("
			SELECT
				cip_id AS id,
				IF(cip_recuperacao_datahora >= DATE_SUB(NOW(), INTERVAL 24 HOUR), 1, 0) AS valida
			FROM
				tab_clientes_iptv
			WHERE
				cip_recuperacao_hash = '{$hash}'");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		
			if ($dados["valida"]) {
		
				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				
				if ($publicacao->alterarSenha($dados["id"], $senha)) {
					
					$this->bancodedados->query("
						UPDATE
							tab_clientes_iptv
						SET
							cip_recuperacao_datahora = NULL,
							cip_recuperacao_hash = NULL
						WHERE
							cip_id = {$dados["id"]}");
							
					$this->registraComentario($dados["id"], "<b>Senha alterada pelo cliente:</b> Endereço IP {$ip}.");

					return true;

				} else {
					$this->ultimo_erro = "Erro de comunicação com o servidor";
				}
				
			} else {

				$this->bancodedados->query("
					UPDATE
						tab_clientes_iptv
					SET
						cip_recuperacao_datahora = NULL,
						cip_recuperacao_hash = NULL
					WHERE
						cip_id = {$dados["id"]}");
				
				$this->ultimo_erro = "Este link expirou. Solicite um novo link para alterar a sua senha.";
				
			}
			
		} else {
			//TODO Trocar mensagem: Ex. Este link não existe, já foi utilizado ou foi substituído por outro mais recente. Verifique sua caixa de e-mail.
			$this->ultimo_erro = "Este link não existe, já foi utilizado ou foi substituído por outro mais recente. Verifique sua caixa de e-mail.";
		}

		return false;

	}

	function recuperarSenha($cliente_iptv_id, $validaEnvio = false) {
		
		if (($dados = $this->buscaDadosIptv($cliente_iptv_id)) != null) {
			
			$hash = rtrim(strtr(base64_encode(random_bytes(96)), '+/', '-_'), '=');
                        
			$hashAntiga = $this->buscaHashByClienteIptv($cliente_iptv_id);
			if($hashAntiga) {
				$hash = $hashAntiga;    
			}
                        
			$this->bancodedados->query("
				UPDATE
					tab_clientes_iptv
				SET
					cip_recuperacao_datahora = NOW(),
					cip_recuperacao_hash = '{$hash}'
				WHERE
					cip_id = {$cliente_iptv_id}");

			if($validaEnvio) {
				$this->enviaEmailRecuperacao($dados["usuario"], $dados["cliente_nome"], $hash, $cliente_iptv_id);
				return true;
			}

			if ($this->enviaEmailRecuperacao($dados["usuario"], $dados["cliente_nome"], $hash, $cliente_iptv_id)) {
				return true;
			}
			
		}
		
		return false;
		
	}
	
	var $ultimo_erro;

	function loginEmail($tipo = 'senha_recuperacao') {
            
		$sql = "SELECT * FROM tab_email_smtp WHERE ativo = '1' AND tipo LIKE '$tipo' LIMIT 1";
		$query = $this->bancodedados->query($sql);

		return $this->bancodedados->resultadoArray($query);
	}

	function enviaEmailRecuperacao($email, $nome, $hash, $clienteIptvId = "") {

		require_once "_global/PHPMailer/PHPMailerAutoload.php";

		$mail = new PHPMailer(true);
		$dadosEmail = $this->loginEmail('senha_recuperacao');

		try {
			$mail->isSMTP();
			$mail->Host = $dadosEmail['host'];
			$mail->SMTPSecure = $dadosEmail['seguranca'];
			$mail->Port = $dadosEmail['porta'];
			$mail->CharSet = "UTF-8";
			$mail->Encoding = 'base64';

			$mail->SMTPOptions = array (
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			
			$mail->SMTPAuth = true;

			$mail->Username = $dadosEmail['usuario'];
			$mail->Password = $dadosEmail['senha'];

			$mail->From = $dadosEmail['usuario'];
			$mail->FromName = iconv('ISO-8859-1', 'UTF-8', $dadosEmail['de']);
			$mail->Subject = iconv('ISO-8859-1', 'UTF-8', "Definição de senha em " . date("d-m-Y H:i:s"));

			$mail->isHTML(true);

			$mail->addAddress($email, $nome);

			$mail->Body = iconv('ISO-8859-1', 'UTF-8', $this->pegaEmailVerificacaoConteudo($hash, $email, $nome));

			$mail->Timeout = 10;
			if($mail->send()) {
				$this->salvaLog("Enviado para: {$email} com token {$hash} -- {$clienteIptvId}");
				return true;
			}

		} catch (Exception $exc) {
			$error = $exc->getTraceAsString();
			$this->salvaLog($error);
		}

		$this->ultimo_erro = $mail->ErrorInfo;
		$this->salvaLog($this->ultimo_erro );

		return false;
		
	}
	
	function salvaLog($error = "") {

		$fp = fopen(DIR_RAIZ."log".DIRECTORY_SEPARATOR."PHPMailer.log", "a");
		fwrite($fp, date("d-m-Y H:i:s")." - ". $error."\n\n\n##########################\n\n");
		fclose($fp);
	}


	function pegaEmailVerificacaoConteudo($hash, $email, $nome) {
		
		$nmProvedor = '';
		$nuSuporte = '';
		$idParceiro = 0;
	    
		$queryParceiro = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cli_id AS cli_id, tab_clientes.clo_id AS clo_id
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
			WHERE
				tab_clientes_iptv.cip_recuperacao_hash = '{$hash}'");
	    
		if (($dadosParceiro = $this->bancodedados->resultadoArray($queryParceiro)) != false) {
			$idParceiro = $dadosParceiro["clo_id"];
		}	    
	    
		if ( $idParceiro > 0 ) {
			$query = $this->bancodedados->query("SELECT clo_nome, clo_telefone_suporte FROM tab_clientes_origens WHERE clo_id = '{$idParceiro}'");
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$nmProvedor = ' ('.$dados["clo_nome"].')';
				if ( $dados["clo_telefone_suporte"] != NULL && $dados["clo_telefone_suporte"] != '' ) {
					$nuSuporte = ' pelo telefone <b>'.$dados["clo_telefone_suporte"].'</b>';
				}
			}
		}
	    
		$conteudo = "
			<div style=\"font-family:sans-serif;font-size:12px;color:#444444;width:480px\">
				<p>Prezado Cliente, bem vindo ao seu app de TV e Streaming!</p>
				<p>&nbsp;</p>
				<p>Seu acesso foi habilitado através do contrato de parceria entre a <b>OléTV</b> e o seu <b>Provedor de Internet{$nmProvedor}!</b></p>
				<p>&nbsp;</p>
				<p>Para acessar o conteúdo dos canais disponíveis <b><a href=\"https://ottip.com.br/?hash={$hash}\">clique aqui</a></b> e registre a sua senha. Lembrando que o login é o seu e-mail: <i>{$email}</i>.</p>
				<p>&nbsp;</p>
				<p>Depois disso, siga os passos abaixo:</p>
				<p>1) Baixe o app do seu provedor na loja do seu dispositivo;</p>
				<p>2) Entre no app baixado e digite o seu e-mail e a sua senha;</p>
				<p>3) Clique em Entrar e divirta-se!</p>
				<p>&nbsp;</p>
				<p>Aproveite mais este benefício fornecido pelo seu Provedor de Internet.</p>
				<p>Em caso de dúvidas, entre em contato com seu provedor{$nuSuporte}.</p>
				<p>&nbsp;</p>
				<p>Atenciosamente,</p>
				<p>Equipe Olé TV!</p>
				<p>A sua TV. Via internet.</p>
			</div>";

		return preg_replace('/\r\n\s+/', '', $conteudo);
		
	}

	/**
	 * @param string $hash
	 * @param string $username
	 * @return string|null
	 */
	function buscaNomeParceiroByUsuario($hash, $username = null) {

		$where = $username ? "OR tab_clientes_iptv.cip_usuario LIKE '{$username}'" : "";
		$query = $this->bancodedados->query("
			SELECT 
				tab_clientes_origens.clo_nome
			FROM 
				tab_clientes_iptv
				INNER JOIN tab_clientes ON tab_clientes.cli_id = tab_clientes_iptv.cli_id
				INNER JOIN tab_clientes_origens ON tab_clientes_origens.clo_id = tab_clientes.clo_id
			WHERE 
				tab_clientes_iptv.cip_recuperacao_hash = '{$hash}' {$where}");
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $dados["clo_nome"];
		}
		
	}

	/**
	 * @param string $hash
	 * @param string|null $username
	 * @return string|false
	 */
	function buscaLogoParceiroByUsuario($hash, $username = null) {

		$where = $username ? "OR tab_clientes_iptv.cip_usuario LIKE '{$username}'" : "";
		$query = $this->bancodedados->query("
			SELECT 
				tab_clientes_origens.clo_logo
			FROM 
				tab_clientes_iptv
				INNER JOIN tab_clientes ON tab_clientes.cli_id = tab_clientes_iptv.cli_id
				INNER JOIN tab_clientes_origens ON tab_clientes_origens.clo_id = tab_clientes.clo_id
	
			WHERE 
				(tab_clientes_iptv.cip_recuperacao_hash = '{$hash}' {$where} ) AND tab_clientes_origens.clo_logo IS NOT NULL");
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $this->blobParaDataImagem($dados["clo_logo"]);
		}
	}

	/**
	 * @param string $blob
	 * @return string|null
	 */
	function blobParaDataImagem($blob) {

		if (!$blob || strlen($blob) === 0) {
			return null;
		}

		$info = @getimagesizefromstring($blob);

		if ($info === false) {
			return null;
		}

		return "data:{$info["mime"]};base64," . base64_encode($blob);
		
	}

	###########
	### Ver ###
	###########

	function ver() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);

				if ($_GET["matar_sessao"]) {

					$publicacao->accountKillDevice($_GET["matar_sessao"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

				} else {

					$dados_status = $publicacao->status($this->dados_item["id"]);
					
					$botoes = array();
					if ($this->ehPublicavel())  $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:60px" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$_GET["i"].'\';">';
					if ($this->ehAdmin()) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" style="width:80px" value="Sincronizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&sincronizar=true&i='.$_GET["i"].'\';">';
					if ($this->ehAlteravelUsuario()) $botoes[] = '<input type="button" name="alterar_usuario" class="buttonAzulEscuroBranco11" style="width:100px" value="Alterar Usuário" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=alterar_usuario&i='.$_GET["i"].'\';">';
					if ($this->ehRecuperavelSenha()) $botoes[] = '<input type="button" name="recuperar_senha" class="buttonAzulEscuroBranco11" style="width:110px" value="Recuperar Senha" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=recuperar_senha&i='.$_GET["i"].'\';">';
					$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" style="width:50px" value="Fechar" onclick="window.close()">';

					$this->conteudo .= $this->carregaArquivo("clientes_iptv.ver.inc.php");

					$this->conteudo = str_replace("[iptv_id]", $this->dados_item["id"], $this->conteudo);
					$this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
					$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
					$this->conteudo = str_replace("[contrato_codigo]", $this->dados_item["contrato_codigo"], $this->conteudo);
					$this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
					$this->conteudo = str_replace("[plano_nome]", $this->dados_item["plano_nome"], $this->conteudo);
					$this->conteudo = str_replace("[planos_adicionais]", $this->dados_item["planos_adicionais"] ? $this->dados_item["planos_adicionais"] : "&mdash;", $this->conteudo);
					$this->conteudo = str_replace("[pontos]", $this->dados_item["pontos"], $this->conteudo);
					$this->conteudo = str_replace("[dispositivos]", $this->dados_item["dispositivos"], $this->conteudo);
					$this->conteudo = str_replace("[vod_catalogos]", $this->dados_item["vod_catalogos"] ? $this->dados_item["vod_catalogos"] : "&mdash;", $this->conteudo);

					// Apps: converte os IDs (string separada por vírgula em cip_apps) em nomes legíveis
					// usando o mapa $apps definido na classe planos.
					$apps_ids = array_filter(array_map("trim", explode(",", $this->dados_item["apps"])), "strlen");
					$apps_nomes = array();
					if ($apps_ids) {
						$planos = controle::instanciaObjeto("planos", $this->bancodedados, $this->permissoes, false);
						foreach ($apps_ids as $app_id) {
							if (array_key_exists($app_id, $planos->apps)) {
								$apps_nomes[] = $planos->apps[$app_id];
							}
						}
					}
					$this->conteudo = str_replace("[apps]", $apps_nomes ? implode(", ", $apps_nomes) : "&mdash;", $this->conteudo);

					$this->conteudo = str_replace("[ajustes_programados]", $this->buscaAjustes($_GET["i"]), $this->conteudo);
					$this->conteudo = str_replace("[lista_comentarios]", $this->geraComentariosListagem($_GET["i"]), $this->conteudo);
					$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
					$this->conteudo = str_replace("[situacao]", $this->geraListagemStatus($this->trataDadosStatus($dados_status)), $this->conteudo);
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["id"]}", $this->conteudo);

				}

			} else {

				$_POST["comentarios"] = trim($_POST["comentarios"]);

				if ($_POST["comentarios"]) {
					$this->registraComentario($_GET["i"], $_POST["comentarios"], $_SESSION["usuario"]);
				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	#######################
	### Alterar Usuário ###
	#######################

	function alterar_usuario() {
		
		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				
				$this->conteudo .= $this->carregaArquivo("clientes_iptv.alterar_usuario.inc.php");
				
				$this->conteudo = str_replace("[cliente_nome]", $this->dados_item["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[contrato_codigo]", $this->dados_item["contrato_codigo"], $this->conteudo);
				$this->conteudo = str_replace("[usuario]", $this->dados_item["usuario"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=alterar_usuario&i={$this->dados_item["id"]}", $this->conteudo);

			} else {
				
				$reload = true;
				//$_POST["usuario"] = addslashes(trim($_POST["usuario"]));

				$_POST["usuario"] = filter_var($_POST["usuario"], FILTER_SANITIZE_EMAIL);
				
				if (!filter_var($_POST["usuario"], FILTER_VALIDATE_EMAIL)) {
					
					$this->registraComentario($_GET["i"], "<b>Erro</b>: Usuário <b>{$_POST["usuario"]}</b> não é um e-mail válido.", $_SESSION["usuario"]);
					
				} elseif ($this->dados_item["usuario"] != $_POST["usuario"] && ($id = $this->buscaIdPorUsuario($_POST["usuario"])) != null) {
					
					$this->registraComentario($_GET["i"], "<b>Erro</b>: Usuário <b>{$_POST["usuario"]}</b> já está cadastrado para o ID [{$id}].", $_SESSION["usuario"]);

				} elseif ($_POST["usuario"] && $this->dados_item["usuario"] != $_POST["usuario"]) {
				
					//TODO Bengin transaction
					$this->bancodedados->transacaoIniciar();
				
					$this->bancodedados->query("
						UPDATE
							tab_clientes_iptv
						SET
							cip_usuario = '{$_POST["usuario"]}',
							cip_publicado = 0
						WHERE
							cip_id = {$this->dados_item["id"]}");

					$this->registraComentario($_GET["i"], "<b>Ação</b>: Alteração de usuário: de <b>{$this->dados_item["usuario"]}</b> para <b>{$_POST["usuario"]}</b>.", $_SESSION["usuario"]);

					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				
					if ($this->recuperarSenha($this->dados_item["id"])) {
						$publicacao->publicar($this->dados_item["id"]);
						$this->registraComentario($_GET["i"], "<b>Ação</b>: Envio de e-mail: Foi enviado e-mail para <b>{$_POST["usuario"]}</b>.", $_SESSION["usuario"]);
						$this->bancodedados->transacaoSalvar();
					} else {
						//TODO Rollback
						//TODO Mostra erro na tela
						$this->bancodedados->transacaoCancelar();
						$reload = false;
						$this->conteudo .= "<script type=\"text/javascript\">alert('Atenção!\\n\\Não foi possivel alterar esse usuário, tente novamente mais tarde')</script>";
						$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=alterar_usuario&i={$_GET["i"]}'</script>";
					}
					
				}
				
				if($reload) {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				}

			}
			
		} else {
			$this->fechar();
		}

	}

	################
	### Publicar ###
	################

	function publicar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

				if ($_GET["retorno"] == "sucesso") {

					if($_GET["sincronizar"]) {
						$this->conteudo .= "<script type=\"text/javascript\">alert('Ponto IPTV sincronizado com sucesso!')</script>";
					} else {
						$this->conteudo .= "<script type=\"text/javascript\">alert('Ponto IPTV publicado com sucesso!')</script>";
					}
					
					$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}'</script>";

				} else {

					$botoes = array();
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';

					if ($_GET["retorno"] == "erro") {
						$mensagem = "Houve uma falha de comunicação com o servidor.<br>Clique em Confirmar para tentar novamente.";
					} else {

						if($_GET["sincronizar"]) {
							$mensagem = "Você deseja realmente <b>sincronizar</b> este ponto IPTV?";
						} else {
							$mensagem = "Você deseja realmente <b>publicar</b> este ponto IPTV?";
						}
					}

					$this->conteudo .= $this->carregaArquivo("clientes_iptv.publicar.inc.php");

					$this->conteudo = str_replace("[mensagem]", $mensagem, $this->conteudo);
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					
					if($_GET["sincronizar"]) {
						$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=publicar&sincronizar=true&i={$_GET["i"]}&debug={$_GET["debug"]}", $this->conteudo);
					} else {
						$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&debug={$_GET["debug"]}", $this->conteudo);
					}

				}

			} else {

				if($_GET["sincronizar"]) {

					$this->sincronizarClienteIptv($this->dados_item, true);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&sincronizar=true&retorno=sucesso");

				} else {
					
					$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
					
					if ($publicacao->publicar($this->dados_item["id"])) {
						header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno=sucesso");
					} else {
						header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno=erro");
					}
				}

			}

		} else {
			$this->fechar();
		}

	}

	#######################
	### Recuperar Senha ###
	#######################

	function recuperar_senha() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehRecuperavelSenha()) {
			$reload = true;
			if ($this->recuperarSenha($_GET["i"])) {
				$this->registraComentario($_GET["i"], "<b>Ação</b>: Recuperação de senha enviada para: {$this->dados_item["usuario"]}", $_SESSION["usuario"]);
			} else {
				//Exibir mensagem de erro de envio na tela
				$reload = false;
				$this->conteudo .= "<script type=\"text/javascript\">alert('Atenção!\\n\\Não foi possivel recuperar senha para o usuário, tente novamente mais tarde')</script>";
				$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}'</script>";
			}
			if($reload) {
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}

		} else {
			$this->fechar();
		}
		
	}

	##############
	### Buscar ###
	##############

	function buscar() {

		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;

		$this->conteudo .= '<script type="text/javascript" src="rds.js.php?m=clientes_iptv" id="clientes_iptv.js"></script>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		$this->conteudo .= $filtro->formulario;

		if ($_GET["f"] || $_GET["p"]) {

			$itens = $this->buscaItens($filtro, $pagina);
			$paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
			$listagem = $this->geraListagem($itens["itens"]);

			$this->conteudo .= '</td></tr></table>';
			$this->conteudo .= $paginacao->conteudo;
			$this->conteudo .= $listagem->conteudo;
			$this->conteudo .= $paginacao->conteudo;

		} else {

			$this->conteudo .= '</td></tr></table>';

		}

	}

	function inicializaFiltro($valores = "") {

		$lista_planos = $this->buscaListaPlanos();
		$lista_status = $this->buscaListaStatus();
		$lista_origens = $this->buscaClientesOrigens();

		$itens = array();

		// Cliente
		$itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "tab_clientes.cli_nome LIKE '%[valor]%'");
		// Usuário
		$itens[] = array ("tipo" => "input", "nome" => "usuario", "descricao" => "Usuário", "valor" => "", "where" => "tab_clientes_iptv.cip_usuario LIKE '%[valor]%'");
		//Plano
		$itens[] = array ("tipo" => "select", "nome" => "servico", "descricao" => "Plano", "valor" => $lista_planos, "where" => "tab_clientes_iptv.pla_id = [valor]");
		// Status
		$itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $lista_status, "where" => "tab_clientes_iptv.cis_id = [valor]");
		// Origem
		$itens[] = array ("tipo" => "select", "nome" => "origem", "descricao" => "Origem", "valor" => $lista_origens, "where" => "tab_clientes.clo_id = [valor]");
		// Filtro
		$filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");

		return $filtro;

	}

	function geraPaginacao($filtro, $total, $pagina) {

		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);

	}

	function buscaItens($filtro, $pagina) {

		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;

		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_clientes_iptv.cip_id AS id,
				tab_clientes_iptv.cip_pontos AS pontos,
				tab_clientes_iptv.cip_dispositivos AS dispositivos,
				tab_clientes_iptv.cip_gravacao AS gravacao,
				tab_clientes_iptv.cip_pacotes AS pacotes,
				tab_clientes_iptv.cip_catalogs AS catalogs,
				tab_clientes_iptv.cip_apps AS apps,
				tab_clientes_iptv.cip_publicado AS publicado,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes_origens.clo_id AS origem_id,
				tab_clientes_origens.clo_nome AS origem_nome,
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS contrato_codigo,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_clientes_iptv_status.cis_id AS status_id,
				tab_clientes_iptv_status.cis_nome AS status_nome
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
				INNER JOIN tab_contratos ON (tab_clientes_iptv.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_planos ON (tab_clientes_iptv.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes_iptv_status ON (tab_clientes_iptv.cis_id = tab_clientes_iptv_status.cis_id)
			WHERE
				$filtro->parametros_sql".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				tab_clientes.cli_nome
			LIMIT
				$limit_inicio,
				$limit_fim");

		$linhas = $this->bancodedados->linhasTotal($query);

		$itens = array();

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verIptv('{$dados["id"]}');";
			}
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			if ($dados["status_id"] == 3) {
				$dados["status_nome"] = "<span style=\"color:red\">{$dados["status_nome"]}</span>";
			}
			if ($dados["publicado"]) {
				$dados["status_nome"] .= "<br><span style=\"color:green\">(Publicado)</span>";
			} else {
				$dados["status_nome"] .= "<br><span style=\"color:red\">(Não Publicado)</span>";
			}
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");

			$itens[] = $dados;

		}

		return array("linhas" => $linhas, "itens" => $itens);

	}

	function geraListagem($itens) {

		$cabecalho = array ("contador" => "#", "cliente_id" => "ID", "cliente_nome" => "Cliente", "plano_nome" => "Plano", "pontos" => "Pontos", "dispositivos" => "Dispositivos", "origem_nome" => "Origem", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20", "35", "", "120", "70", "70", "70", "85"), null, array("right", "center", "left", "left", "center", "center", "center", "center"));

		return $listagem;

	}

	function buscaPlanosALaCarte($contrato_id) {

		$pacotes_a_la_carte = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_parametros AS parametros,
				tab_planos.pla_nome AS plano_nome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				tab_assinaturas.ass_pai IS NOT NULL AND
				rel_ass_cor_estacontido.cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);

			if (count($dados["parametros"]["iptv_pacotes"]) > 0) {
				$pacotes_a_la_carte[] = $dados["plano_nome"];
			}

		}

		return implode(", ", $pacotes_a_la_carte);

	}

	function buscaPlanosAdicionais($contrato_id) {

		$planos_adicionais = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_nome AS plano_nome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				tab_assinaturas.ass_pai IS NOT NULL AND
				rel_ass_cor_estacontido.cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos_adicionais[] = $dados["plano_nome"];
		}

		return implode(", ", $planos_adicionais);

	}

	function buscaCatalogos($catalogs) {

		$vod_catalogos = array();

		if ($catalogs) {

			$query = $this->bancodedados->query("
				SELECT
					ipt_nome AS nome
				FROM
					tab_iptv_pacotes
				WHERE
					ipt_codigo IN ($catalogs) AND
					ipt_tipo = 'CATALOGO'
				ORDER BY
					ipt_nome");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$vod_catalogos[] = $dados["nome"];
			}
			
		}

		return implode(", ", $vod_catalogos);

	}

	###################
	### Comentários ###
	###################

	function buscaComentarios($iptv_id) {
	    
	    $comentarios = array();

		$query = $this->bancodedados->query("
			SELECT
				IF(tab_usuarios.usu_id IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel,
				DATE_FORMAT(tab_clientes_iptv_comentarios.cic_datahora, '%d/%m/%Y (%H:%i)') AS data,
				tab_clientes_iptv_comentarios.cic_conteudo AS conteudo
			FROM
				tab_clientes_iptv_comentarios
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes_iptv_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_clientes_iptv_comentarios.cip_id = $iptv_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["detalhamento"][] = "<b>{$dados["responsavel"]}</b> em <b>{$dados["data"]}</b>";
			if ($dados["conteudo"]) $dados["detalhamento"][] = substr($dados["conteudo"], 0, 3) == "<b>" ? $dados["conteudo"] : "<b>Conteúdo:</b> {$dados["conteudo"]}";
			$dados["detalhamento"] = implode("<br>", $dados["detalhamento"]);

			$comentarios[] = $dados;

		}

		return $comentarios;

	}

	function geraComentariosListagem($id) {

		$comentarios = $this->buscaComentarios($id);
		$cabecalho = array ("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);

		return $listagem->conteudo;

	}

	##############
	### Fechar ###
	##############

	function fechar() {

		if ($_GET["id"]) {
			$this->conteudo = "
				<script type=\"text/javascript\">
					if (!window.opener.closed && window.opener.buscaIptv) {
						window.opener.buscaIptv({$_GET["id"]});
					}
					window.close();
				</script>";
		} else {
			$this->conteudo = '<script type="text/javascript">window.close();</script>';
		}

	}

	###########
	### RDS ###
	###########

	function buscaClientesIptv($cliente_id, $exibe_inativos = false) {

		$pontos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_iptv.cip_id AS id,
				tab_clientes_iptv.cip_pontos AS pontos,
				tab_clientes_iptv.cip_dispositivos AS dispositivos,
				tab_clientes_iptv.cip_gravacao AS gravacao,
				tab_clientes_iptv.cip_pacotes AS pacotes,
				tab_clientes_iptv.cip_catalogs AS catalogs,
				if(tab_clientes_iptv.cip_catalogs IS NULL OR tab_clientes_iptv.cip_catalogs = '', 'Não', 'Sim') AS vod,
				tab_clientes_iptv.cip_vod_telas AS vod_telas,
				tab_clientes_iptv.cip_publicado AS publicado,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.clo_id AS origem_id,
				tab_contratos.cor_id AS contrato_id,
				tab_contratos.cor_codigo AS contrato_codigo,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_clientes_iptv_status.cis_id AS status_id,
				tab_clientes_iptv_status.cis_nome AS status_nome
			FROM
				tab_clientes_iptv
				INNER JOIN tab_clientes ON (tab_clientes_iptv.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_contratos ON (tab_clientes_iptv.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_planos ON (tab_clientes_iptv.pla_id = tab_planos.pla_id)
				INNER JOIN tab_clientes_iptv_status ON (tab_clientes_iptv.cis_id = tab_clientes_iptv_status.cis_id)
			WHERE
				tab_clientes_iptv.cli_id = $cliente_id".($exibe_inativos ? "" : " AND
				tab_clientes_iptv.cis_id IN (1, 3)")."
			ORDER BY
				tab_clientes_iptv.cip_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "javascript:verIptv('{$dados["id"]}');";
			}
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			if ($dados["status_id"] == 3) {
				$dados["status_nome"] = "<span style=\"color:red\">{$dados["status_nome"]}</span>";
			}
			if ($dados["publicado"]) {
				$dados["status_nome"] .= "<br><span style=\"color:green\">(Publicado)</span>";
			} else {
				$dados["status_nome"] .= "<br><span style=\"color:red\">(Não Publicado)</span>";
			}

			$pontos[] = $dados;

		}

		return $pontos;

	}

	function geraClientesIptvListagem($dados) {

		$cabecalho = array ("plano_nome" => "Plano", "contrato_codigo" => "Contrato", "pontos" => "Pontos", "dispositivos" => "Dispositivos", "vod" => "VOD", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(0, 0, 70, 70, 70, 80), null, array("left", "center", "center", "center", "center", "center"));

		return $listagem->conteudo;

	}

	function ehAdmin($id = null) {

		$user = $id? $id : $_SESSION["usuario"];
		$query = "SELECT usu_administrador FROM tab_usuarios WHERE usu_id = $user";
		$query = $this->bancodedados->query($query);
		$ehAdmin = $this->bancodedados->resultadoArray($query);

		return $ehAdmin['usu_administrador']== 0 ? false : true;
	}

	###############
	### Funções ###
	###############

	function ehPublicavel() {

		if ($this->dados_item["status_id"] != 1) return false;	
		if (!$this->permissoes[$this->dados_modulo["nome"]]["publicar"]) return false;
		if ($this->dados_item["publicado"] && !$this->permissoes[$this->dados_modulo["nome"]]["publicar_plus"]) return false;

		return true;

	}

	function ehAlteravelUsuario() {

		if ($this->dados_item["status_id"] != 1) return false;	
		if (!$this->permissoes[$this->dados_modulo["nome"]]["alterar_usuario"]) return false;

		return true;

	}

	function ehRecuperavelSenha() {

		if ($this->dados_item["status_id"] != 1) return false;	
		if (!$this->permissoes[$this->dados_modulo["nome"]]["recuperar_senha"]) return false;
		if (!$this->dados_item["publicado"]) return false;
		if (!$this->dados_item["usuario"]) return false;
		if (!$this->dados_item["eh_recuperavel"]) return false;

		return true;

	}

	function trataDadosStatus($dados) {
		
		$dados_tratados = array();

		for ($i = 0; $i < count($dados); $i++) {

			if (!$dados[$i]["remotion"]) {

				if ($this->permissoes[$this->dados_modulo["nome"]]["matar_sessao"]) {
					$dados[$i]["botao"] = "<input type=\"button\" name=\"matar_sessao\" class=\"buttonAzulEscuroBranco11\" value=\"X\" style=\"width:23px;height:23px\" onClick=\"if (confirm('Tem certeza que deseja matar essa sessão?')) window.location='?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}&matar_sessao={$dados[$i]["id"]}'\">";
				}
				
				if ($dados[$i]["online"]) {
					$dados[$i]["status"] = "<div style=\"color:green\">Online</div>";
				} else {
					$dados[$i]["status"] = "<div style=\"color:red\">Offline</div>";
				}
				
				$dados_tratados[] = $dados[$i];
				
			}

		}

		return $dados_tratados;

	}

	function geraListagemStatus($dados) {

		$cabecalho = array ("brand" => "Marca", "model" => "Modelo", "macAddress" => "MAC", "status" => "Status", "botao" => "");

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(0, 0, 0, 50, 28), null, array("center", "center", "center", "center", "center"));

		return $listagem->conteudo;

	}
	
	function buscaIdPorUsuario($usuario) {
		
		$usuario = str_replace("_", "\\_", $usuario);
		$query = $this->bancodedados->query("
			SELECT
				cli_id AS cliente_id
			FROM
				tab_clientes_iptv
			WHERE
				cip_usuario like '{$usuario}' AND
				cis_id IN (1, 3)");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $dados["cliente_id"];
		}
		
	}
           
	function buscaPodeSepararpontos ($contrato_id) {
		
		$sql = "SELECT 
					tab_clientes.cli_id cliente_id, tab_clientes.clo_id origem_id,tab_contratos.cor_id contrato_id, tab_clientes_origens.clo_separar_pontos separar_pontos
				FROM 
					tab_clientes
					LEFT JOIN tab_contratos ON tab_contratos.cli_id = tab_clientes.cli_id
					LEFT JOIN tab_clientes_origens ON tab_clientes_origens.clo_id = tab_clientes.clo_id
				WHERE 
					tab_contratos.cor_id = {$contrato_id}";
					
		$query = $this->bancodedados->query($sql);

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				return $dados["separar_pontos"] == 1 ?  true : false;
		} else {
			return false;
		}

	}
	
	/**
	 * Sincroniza os pontos do cliente IPTV
	 * @param array $dados_iptv
	 * @return int
	 */
	function sincronizarClienteIptv($dados_iptv, $publicar = false) {
		
		$parametros = $this->buscaParametrosContrato($dados_iptv["contrato_id"]);

		$this->bancodedados->query("
			UPDATE
				tab_clientes_iptv
			SET
				cip_gravacao = {$parametros["gravacao"]},
				cip_pacotes = '{$parametros["pacotes"]}',
				cip_catalogs = '{$parametros["catalogs"]}',
				cip_apps = '{$parametros["apps"]}',
				cip_vod_telas = '{$parametros["vod_telas"]}',
				cip_streaming = '{$parametros["streaming"]}',
				cip_publicado = 0
			WHERE
				cip_id = {$dados_iptv["id"]}");

		if (!empty($parametros["catalogs"])) {
			$this->verificaVodOrigem($parametros["cliente_id"]);
		}
		
		if ($publicar) {
			$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
			$publicacao->publicar($dados_iptv["id"]);
		}

		return $dados_iptv["id"];

	}

	function updateClientesIptv($parametros, $dados_iptv, $pontos, $dispositivos) {
            
		if ($dados_iptv["pontos"] != $parametros["pontos"] || $dados_iptv["dispositivos"] != $parametros["dispositivos"] || $dados_iptv["gravacao"] != $parametros["gravacao"] || $dados_iptv["pacotes"] != $parametros["pacotes"]) {

			$this->bancodedados->query("
				UPDATE
					tab_clientes_iptv
				SET
					cip_pontos = {$pontos},
					cip_dispositivos = {$dispositivos},
					cip_gravacao = {$parametros["gravacao"]},
					cip_pacotes = '{$parametros["pacotes"]}',
					cip_catalogs = '{$parametros["catalogs"]}',
					cip_apps = '{$parametros["apps"]}',
					cip_vod_telas = '{$parametros["vod_telas"]}',
					cip_streaming = '{$parametros["streaming"]}',
					cip_publicado = 0
				WHERE
					cip_id = {$dados_iptv["id"]}");

		}

		if (!empty($parametros["catalogs"])) {
			$this->verificaVodOrigem($parametros["cliente_id"]);
		}

		$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$publicacao->publicar($dados_iptv["id"]);

		return $dados_iptv["id"];

	}

	function insertClientesIptv($contrato_id, $parametros, $pontos, $dispositivos) {
            
		$this->bancodedados->query("
			INSERT INTO
				tab_clientes_iptv
			SET
				cor_id = {$contrato_id},
				pla_id = {$parametros["plano_id"]},
				cli_id = {$parametros["cliente_id"]},
				cis_id = 1,
				cip_pontos = {$pontos},
				cip_dispositivos = {$dispositivos},
				cip_gravacao = {$parametros["gravacao"]},
				cip_pacotes = '{$parametros["pacotes"]}',
				cip_catalogs = '{$parametros["catalogs"]}',
				cip_apps = '{$parametros["apps"]}',
				cip_vod_telas = '{$parametros["vod_telas"]}',
				cip_streaming = '{$parametros["streaming"]}',
				cip_publicado = 0");

		$iptv_id = $this->bancodedados->ultimoIdInserido();

		$this->registraComentario($iptv_id, "<b>Ação</b>: Inserido", $_SESSION["usuario"]);

		if (!empty($parametros["catalogs"])) {
			$this->verificaVodOrigem($parametros["cliente_id"]);
		}

		$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
		$publicacao->publicar($iptv_id);

		return $iptv_id;

	}

	function verificaVodOrigem($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_origens.clo_id AS origem_id,
				tab_clientes_origens.clo_vod_status AS vod_status
			FROM
				tab_clientes
				INNER JOIN tab_clientes_origens ON tab_clientes.clo_id = tab_clientes_origens.clo_id
			WHERE
				tab_clientes.cli_id = {$cliente_id}");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($dados["vod_status"] != 'ATIVO') {

				$this->bancodedados->query("
					UPDATE
						tab_clientes_origens
					SET
						clo_vod_status = 'ATIVO'
					WHERE
						clo_id = {$dados["origem_id"]}");

				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				$publicacao->publicarVendor($dados["origem_id"]);

			}

		}

	}

	function insereIptv($contrato_id) {

		$parametros = $this->buscaParametrosContrato($contrato_id);
		$podeSepararpontos = $this->buscaPodeSepararpontos($contrato_id);

		if ($parametros["pontos"] || $parametros["dispositivos"]) {

			$dados_iptv = $this->buscaIptvPorContrato($contrato_id);
			$parametros["streaming"] = $parametros["streaming"] ? $parametros["streaming"] : 0;
                        
			if ($podeSepararpontos) {
				if ($dados_iptv) {
					foreach ($dados_iptv as $item) {
						$retorno[] = $this->updateClientesIptv($parametros, $item, $item["pontos"], $item["dispositivos"]);
					}
				} else {
					for ($i = 0 ; $i < $parametros["pontos"] ; $i++) {
						$retorno[] = $this->insertClientesIptv($contrato_id, $parametros, 1, 0);
					}
					for ($i = 0 ; $i < $parametros["dispositivos"] ; $i++) {
						$retorno[] = $this->insertClientesIptv($contrato_id, $parametros, 0, 1);
					}
				}
				
			} else {
				$pontos = $parametros["pontos"];
				$dispositivos = $parametros["dispositivos"];
				if ($dados_iptv) {
					foreach ($dados_iptv as $item) {
						$retorno = $this->updateClientesIptv($parametros, $item, $pontos, $dispositivos);
					}
				} else {
					$retorno = $this->insertClientesIptv($contrato_id, $parametros, $pontos, $dispositivos);
				}
			}
			
			return $retorno;
		}

	}

	function removeIptv($contrato_id) {

		$iptv = $this->buscaIptvPorContrato($contrato_id);

		if ($iptv) {
			$this->cancelaIptv($contrato_id, $_SESSION["usuario"]);
			foreach ($iptv as $item) {
				$this->bancodedados->query("DELETE FROM tab_clientes_iptv_comentarios WHERE cip_id = {$item['id']}");
				$this->bancodedados->query("DELETE FROM tab_clientes_iptv_ajustes WHERE cip_id = {$item['id']}");
				$this->bancodedados->query("DELETE FROM rel_saj_cip_contem WHERE cip_id = {$item['id']}");
				$this->bancodedados->query("DELETE FROM tab_clientes_iptv WHERE cip_id = {$item['id']}");
			}
		}

	}

	function cancelaIptv($contrato_id, $usuario_id = null) {

		$iptv = $this->buscaIptvPorContrato($contrato_id);

		if ($iptv) {
			foreach ($iptv as $item) {
				$this->bancodedados->query("
						UPDATE
								tab_clientes_iptv
						SET
								cis_id = 2,
								cip_publicado = 0
						WHERE
								cip_id = {$item['id']}");

				$this->registraComentario($item['id'], "<b>Ação</b>: Cancelado", $usuario_id);

				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				$publicacao->publicar($item['id']);
			}
		}

	}

	function descancelaIptv($contrato_id) {

		$iptv = $this->buscaIptvPorContrato($contrato_id);

		if ($iptv) {
			foreach ($iptv as $item) {
				$this->bancodedados->query("
						UPDATE
								tab_clientes_iptv
						SET
								cis_id = 1,
								cip_publicado = 0
						WHERE
								cip_id = {$item['id']}");

				$this->registraComentario($item['id'], "<b>Ação</b>: Desancelado", $_SESSION["usuario"]);

				$publicacao = controle::instanciaObjeto("clientes_iptv.publicacao", $this->bancodedados);
				$publicacao->publicar($item['id']);
			}
		}

	}
     
	function buscaParametrosContrato($contrato_id) {

		$parametros = array(
			"pontos" => 0,
			"dispositivos" => 0,
			"gravacao" => 0,
			"pacotes" => array(),
			"catalogs" => array(),
			"apps" => array(),
			"vod_telas" => 0,
			"streaming" => 0
		);

		$query = $this->bancodedados->query("
			SELECT
                cli_id AS cliente_id,
                pla_id AS plano_id,
                ass_pai AS assinatura_pai,
				ass_parametros AS parametros
			FROM
				tab_assinaturas
			WHERE
				ass_id IN (SELECT ass_id FROM rel_ass_cor_estacontido WHERE cor_id = $contrato_id)");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);

			if (!$dados["assinatura_pai"]) {
				$parametros["cliente_id"] = $dados["cliente_id"];
				$parametros["plano_id"] = $dados["plano_id"];
			}
		
			$parametros["pontos"] += $dados["parametros"]["iptv_stb_quantidade"];
			$parametros["dispositivos"] += $dados["parametros"]["iptv_aplicativos_quantidade"];
			$parametros["gravacao"] += $dados["parametros"]["iptv_gravacao_horas"];
			
			$pacotes = isset($dados["parametros"]["iptv_pacotes"]) && is_array($dados["parametros"]["iptv_pacotes"]) ? $dados["parametros"]["iptv_pacotes"] : array();
			$parametros["pacotes"] = array_merge($parametros["pacotes"], $pacotes);
							  
			$catalogs = isset($dados["parametros"]["vod_pacotes"]) && is_array($dados["parametros"]["vod_pacotes"]) ? $dados["parametros"]["vod_pacotes"] : array();
			$parametros["catalogs"] = array_merge($parametros["catalogs"], $catalogs);

			$apps = isset($dados["parametros"]["apps"]) && is_array($dados["parametros"]["apps"]) ? $dados["parametros"]["apps"] : array();
			$parametros["apps"] = array_merge($parametros["apps"], $apps);

			$parametros["vod_telas"] += isset($dados["parametros"]["vod_telas"]) ? $dados["parametros"]["vod_telas"] : 0;
			
			if ($parametros["streaming"] == 0 && isset($dados["parametros"]["iptv_streaming"])) {
			  $parametros["streaming"] = $dados["parametros"]["iptv_streaming"];
			}

		}

		sort($parametros["pacotes"]);
		sort($parametros["catalogs"]);
		sort($parametros["apps"]);

		$parametros["pacotes"] = implode(",", $parametros["pacotes"]);
		$parametros["catalogs"] = implode(",", $parametros["catalogs"]);
		$parametros["apps"] = implode(",", $parametros["apps"]);

		return $parametros;

	}

	function buscaIptvPorContrato($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				cip_id AS id,
				cip_pontos AS pontos,
				cip_dispositivos AS dispositivos,
				cip_gravacao AS gravacao,
				cip_pacotes AS pacotes,
				cip_catalogs AS catalogs,
				cip_apps AS apps,
				cip_publicado AS publicado,
				cli_id AS cliente_id,
				cor_id AS contrato_id,
				pla_id AS plano_id,
				cis_id AS status_id
			FROM
				tab_clientes_iptv
			WHERE
				cor_id = $contrato_id");

		$lista = array();
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}

		return $lista;

	}

	function buscaListaPlanos() {

		$query = $this->bancodedados->query("
			SELECT
				pla_id AS id,
				pla_nome AS nome
			FROM
				tab_planos
			WHERE
				plt_id = 1 AND
				pls_id = 1
			ORDER BY
				nome");

		return $this->bancodedados->resultadoArrayTotal($query);

	}

	function buscaListaStatus() {

		$query = $this->bancodedados->query("
			SELECT
				cis_id AS id,
				cis_nome AS nome
			FROM
				tab_clientes_iptv_status
			WHERE
				1
			ORDER BY
				nome");

		return $this->bancodedados->resultadoArrayTotal($query);

	}

	function buscaClientesOrigens() {

		$origens = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			WHERE
				1".($_SESSION["origem"] ? " AND
				clo_id = {$_SESSION["origem"]}" : "")."
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens[] = $dados;
		}

		return $origens;

	}

	function registraComentario($iptv_id, $conteudo, $responsavel_id = null) {

		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
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

	function buscaAjustes($iptv_id) {

		$lista = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_servicos_ajustes.saj_id AS id,
 				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
 				tab_servicos_ajustes_categorias.sac_nome AS categoria,
				tab_servicos_ajustes_status.sas_id AS status_id,
				tab_servicos_ajustes_status.sas_nome AS status,
				DATE_FORMAT(tab_clientes_iptv_ajustes.cia_inicio, '%d/%m/%Y (%H:%i)') AS aplicado_deste
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
				INNER JOIN rel_saj_cip_contem ON (rel_saj_cip_contem.saj_id = tab_servicos_ajustes.saj_id)
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
				LEFT OUTER JOIN tab_clientes_iptv_ajustes ON (tab_clientes_iptv_ajustes.saj_id = tab_servicos_ajustes.saj_id AND tab_clientes_iptv_ajustes.cia_termino IS NULL)
			WHERE
				rel_saj_cip_contem.cip_id = $iptv_id AND
				tab_servicos_ajustes_status.sas_id IN (5, 9)
			ORDER BY
				id");

		while (($resultado = $this->bancodedados->resultadoArray($query)) != false) {

			$regras = '&raquo; Início: '.$resultado["inicio"];
			if ($resultado["termino"]) $regras .= '&nbsp; &nbsp; &raquo; Término: '.$resultado["termino"];
			$regras .= $this->buscaRegras($resultado["id"]);
			$regras = str_replace("\n", " ", $regras);
			$regras = str_replace("\"", "\\'", $regras);
			$resultado["codigo"] = "<div align=\"center\">".sprintf("%06s", $resultado["id"])."</div>";
			$resultado["categoria"] = "<div align=\"left\" style=\"cursor:pointer\" onmouseover=\"hintShow('$regras', 390)\" onmouseout=\"hintHide()\">".$resultado["categoria"]."</div>";
			if ($resultado["status_id"] == 5) {
				if ($resultado["aplicado_deste"]) {
					$resultado["status"] .= " / Aplicado desde {$resultado["aplicado_deste"]}";
				} else {
					$resultado["status"] .= " / Não Aplicado";
				}
			}
			$resultado["categoria"] = "<div align=\"left\">".$resultado["categoria"]."</div>";
			if (!$resultado["inicio"]) $resultado["inicio"] = "-";
			if (!$resultado["termino"]) $resultado["termino"] = "-";

			$lista[] = $resultado;

		}

		$cabecalho = array ("codigo" => "Código", "categoria" => "Categoria", "status" => "Status");
		$larguras = array("40", "", "220");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $lista, 0, false, $larguras);

		return $listagem->conteudo;

	}

	function buscaRegras($ajuste_id) {

		$tabela = "";

		$query = $this->bancodedados->query("
			SELECT
				tab_servicos_ajustes_regras.sar_ajuste AS ajuste,
				tab_servicos_ajustes_regras.sar_dia_semana AS dia_semana,
				DATE_FORMAT(tab_servicos_ajustes_regras.sar_hora_inicio, '%H:%i') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes_regras.sar_hora_termino, '%H:%i') AS termino,
				tab_servicos_ajustes_tipos.sat_id AS tipo_id,
				tab_servicos_ajustes_tipos.sat_nome AS tipo
			FROM
				tab_servicos_ajustes_regras
				LEFT OUTER JOIN tab_servicos_ajustes_tipos ON (tab_servicos_ajustes_tipos.sat_id = tab_servicos_ajustes_regras.sat_id)
			 WHERE
			 	saj_id = $ajuste_id
			ORDER BY
				sar_id ASC");

		if ($this->bancodedados->linhas($query)) {

			$tabela .= '<table width="100%" cellpadding="1" cellspacing="1" border="0" bgcolor="#FFFFFF">';
			$tabela .= '<tr>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" colspan="5" align="center"><b>Regras</b></td>';
			$tabela .= '</tr>';
			$tabela .= '<tr>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10"><b>Tipo</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="50"><b>Ajuste</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="70"><b>Dia</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="45"><b>Início</b></td>';
			$tabela .= '<td bgcolor="#505050" class="txtBranco10" align="center" width="45"><b>Término</b></td>';
			$tabela .= '</tr>';

			while (($regra_ajuste = $this->bancodedados->resultadoArray($query)) != false) {
				$regra_ajuste["ajuste_tipo"] = $regra_ajuste["ajuste"] <= 1 ? 1 : 2;
				$regra_ajuste["ajuste_unidade"] = $regra_ajuste["ajuste_tipo"] == 2 ? floor(log($regra_ajuste["ajuste"], 1024)) : "";
				$regra_ajuste["ajuste_valor"] = $regra_ajuste["ajuste_tipo"] == 1 ? $regra_ajuste["ajuste"] * 100 : floor($regra_ajuste["ajuste"] / pow(1024, $regra_ajuste["ajuste_unidade"]));
				$regra_ajuste["ajuste_valor"] .= $regra_ajuste["ajuste_unidade"] ? " ".$this->buscaAjusteUnidade($regra_ajuste["tipo_id"], $regra_ajuste["ajuste_unidade"]) : "%";
				if (!$regra_ajuste["tipo"]) $regra_ajuste["tipo"] = "Todos";
				$regra_ajuste["dia_semana"] = (!$regra_ajuste["dia_semana"])? "Todos" : $this->dias_semana[$regra_ajuste["dia_semana"]];
				if (!$regra_ajuste["inicio"]) $regra_ajuste["inicio"] = "00:00";
				if (!$regra_ajuste["termino"]) $regra_ajuste["termino"] = "24:00";
				$tabela .= '<tr>';
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\">{$regra_ajuste["tipo"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["ajuste_valor"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["dia_semana"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["inicio"]}</td>";
				$tabela .= "<td bgcolor=\"#505050\" class=\"txtBranco10\" align=\"center\">{$regra_ajuste["termino"]}</td>";
				$tabela .= '</tr>';
			}

			$tabela .= '</table>';

		}

		return $tabela;

	}

	function buscaAjusteUnidade($ajuste_tipo_id, $unidade_id) {

		$multiplicadores = array(0 => "", 1 => "K", 2 => "M", 3 => "G");
		$unidades = array(1 => "bps", 2 => "B", 4 => "B", 5 => "B / mês", 7 => "B", 8 => "B / mês", 9 => "bps");

		return $multiplicadores[$unidade_id].$unidades[$ajuste_tipo_id];

	}

}

?>
