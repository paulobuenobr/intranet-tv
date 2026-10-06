<?

class sva_youcast
{

	/**
	 * @var object
	 */
	private $bancodedados;

	/**
	 * @var string
	 */
	private $api_url = "https://produtosdigitais.youcast.tv.br/api/v2/customers/";

	/**
	 * @var string
	 */
	private $login = "OLETV";

	/**
	 * @var string
	 */
	private $secret = "edbf56a693eb4bd91c8db767e27c0f236f4f702408a1fbe695c8ca0e986d4328";

	/**
	 * @var int
	 */
	private $timestamp;

	/**
	 * @var string
	 */
	private $sha1;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * Construtor da classe.
	 * @param array $bancodedados
	 */
	function __construct($bancodedados)
	{

		$this->bancodedados = $bancodedados;
		$this->timestamp = time();

		// SHA1(timestamp + login + secret)
		$this->sha1 = sha1($this->timestamp . $this->login . $this->secret);

		// Token final
		$this->token = $this->gerarToken();
	}

	/**
	 * Gera o token de autenticação.
	 * @return string - Token de autenticação.
	 */
	private function gerarToken()
	{
		return $this->login . ":" . $this->timestamp . ":" . $this->sha1;
	}

	/**
	 * Gera os headers da API.
	 * @return array - Headers da API.
	 */
	private function gerarHeaders()
	{
		return [
			"Authorization: {$this->token}",
			"Content-Type: application/json",
			"Accept: application/json"
		];
	}

	/**
	 * Adiciona um subscriber.
	 * @param string $cliente - ID do cliente.
	 * @param string $origem - Origem do produto.
	 * @param string $produto - Produto a ser adicionado.
	 * @return void
	 */
	function adicionar($cliente, $produto, $origem)
	{
		$body = [
			"brand_id" => $origem,
			"resources_id" => [$produto],
		];
		$json_body = json_encode($body);

		$url = $this->api_url . $cliente;

		// CURL
		$curl = curl_init();

		// CURL Options
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => $json_body,
			CURLOPT_HTTPHEADER => $this->gerarHeaders()
		]);

		$response = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$error = curl_error($curl);

		curl_close($curl);

		$json_response = json_decode($response, true);
		$ret_status = $json_response["statusCode"];
		$ret_msg = $json_response["message"];
		$sql = "INSERT INTO tab_youcast_retorno (retorno, cliente, tipo, data, produto, ret_status, ret_msg) VALUES ('$response', '$cliente', 'adicionar', NOW(), '$produto', '$ret_status', '$ret_msg')";
		$this->bancodedados->query($sql);

		if ($error) {
			return false;
		} else {

			$dados = $this->buscaDadosCliente($cliente);
			if ($dados && !empty($dados["email"]) && empty($dados["envido_email"])) {
				if($this->enviarEmail($dados["email"], $this->bodyEmail($cliente, $dados["documento"]))){
					$this->bancodedados->query("UPDATE tab_youcast_clientes SET envido_email = '1' WHERE cliente = '$cliente'");
				} else {
					$this->bancodedados->query("UPDATE tab_youcast_clientes SET envido_email = '0' WHERE cliente = '$cliente'");
				}
			}

			return $response;
		}
	}

	/**
	 * Remove um subscriber.
	 * @param string $cliente - ID do cliente.
	 * @param string $produto - Produto a ser removido.
	 * @return string - Response da API.
	 */
	function remover($cliente, $produto)
	{
		$body = [
			"resources_id" => [$produto],
		];
		$json_body = json_encode($body);

		$url = $this->api_url . $cliente;

		// CURL
		$curl = curl_init();

		// CURL Options
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "DELETE",
			CURLOPT_POSTFIELDS => $json_body,
			CURLOPT_HTTPHEADER => $this->gerarHeaders()
		]);

		$response = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$error = curl_error($curl);

		curl_close($curl);

		$json_response = json_decode($response, true);
		$ret_status = $json_response["statusCode"];
		$ret_msg = $json_response["message"];
		$sql = "INSERT INTO tab_youcast_retorno (retorno, cliente, tipo, data, produto, ret_status, ret_msg) VALUES ('$response', '$cliente', 'remover', NOW(), '$produto', '$ret_status', '$ret_msg')";
		$this->bancodedados->query($sql);

		if ($error) {

			return false;
		} else {

			return $response;
		}
	}

	/**
	 * Login na API do Youcast.
	 * @param array $json - JSON com os dados de login.
	 * @return string - Response da API.
	 */
	function login($json)
	{

		$usuario = $json["usuario"];
		$senha = $json["senha"];

		$sql = "SELECT * FROM tab_youcast_clientes WHERE cliente LIKE '$usuario' AND documento LIKE '$senha';";
		$query = $this->bancodedados->query($sql);
		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$retorno = [
				"subscriber_id" => $dados["cliente"],
				"country_code" => "BR",
			];
			return json_encode($retorno);
		} else {
			$retorno = [
				'subscriber_id' => $usuario,
				'error' => "Usuario ou senha invalidos"
			];
			return json_encode($retorno);
		}
	}

	/**
	 * Verifica a autorização de um cliente.
	 * @param array $json - JSON com os dados de autorização.
	 * @return string - Response da API.
	 */
	function autorizacao($json)
	{

		$cliente = $json["cliente"];
		$produto = $json["produto"];

		$sql = "SELECT * FROM tab_youcast_clientes WHERE cliente LIKE '$cliente' AND produto LIKE '$produto' AND status = '1';";
		$query = $this->bancodedados->query($sql);
		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			return [
				'access' => true,
				'rating' => 'G',
				'ttl' => 3600,
				'error' => null,
			];
		} else {
			return [
				'access' => false,
				'rating' => 'G',
				'ttl' => 3600,
				'error' => [
					'errorCode' => 'IDP-010',
					'details' => "check fail: {$cliente}"
				]
			];
		}
	}

	/**
	 * Envia um email.
	 * @param string $email - Email do destinatário.
	 * @param string $corpo - Corpo do email.
	 * @return boolean - True se o email foi enviado com sucesso, false caso contrário.
	 */
	function enviarEmail($email, $corpo) {

		require_once "_global/PHPMailer/PHPMailerAutoload.php";

		$dadosEmail = $this->loginEmail('youcast');
		$assunto = "Ativação de Acesso - Telemedicina YouCast";

		try {
			$mail = new PHPMailer(true);
			$mail->isSMTP();
			$mail->Host = $dadosEmail['host'];
			$mail->SMTPSecure = $dadosEmail['seguranca'];
			$mail->Port = $dadosEmail['porta'];
			$mail->CharSet = "UTF-8";
			$mail->Encoding = 'base64';
			$mail->SMTPAuth = true;
			$mail->Username = $dadosEmail['usuario'];
			$mail->Password = $dadosEmail['senha'];
			$mail->From = $dadosEmail['usuario'];
			$mail->FromName = iconv('ISO-8859-1', 'UTF-8', $dadosEmail['de']);
			$mail->Subject = iconv('ISO-8859-1', 'UTF-8', $assunto);
			$mail->Body = iconv('ISO-8859-1', 'UTF-8', $corpo);
			$mail->Timeout = 10;
			$mail->addAddress($email);
			$mail->isHTML(true);

			return $mail->send();

		} catch (Exception $exc) {
			return false;
		}
	}

	/**
	 * Busca os dados de login do email.
	 * @param string $tipo - Tipo de login.
	 * @return array - Dados de login do email.
	 */
	function loginEmail($tipo = 'youcast') {
            
		$sql = "SELECT * FROM tab_email_smtp WHERE ativo = '1' AND tipo LIKE '$tipo' LIMIT 1";
		$query = $this->bancodedados->query($sql);

		return $this->bancodedados->resultadoArray($query);
	}

	/**
	 * Gera o link de acesso.
	 * @return string - Link de acesso.
	 */
	function linkAcesso()
	{
		return "https://produtosdigitais.youcast.tv.br/";
	}

	/**
	 * Busca os dados do cliente.
	 * @param string $cliente - Cliente.
	 * @return array - Dados do cliente.
	 */
	function buscaDadosCliente($cliente)
	{
		$sql = "SELECT * FROM tab_youcast_clientes WHERE cliente LIKE '$cliente';";
		$query = $this->bancodedados->query($sql);
		$dados = $this->bancodedados->resultadoArray($query);

		return $dados;
	}

	/**
	 * Masca o CPF.
	 * @param string $documento - Documento do cliente.
	 * @return string - CPF mascarado.
	 */
	function mascaraCPF($documento)
	{
		$doc = preg_replace('/[^0-9]/', '', $documento);
		return '***.'. substr($doc, 3, 3) .'.***-**';
	}

	/**
	 * Busca a saudação do cliente.
	 * @return string - Saudação do cliente.
	 */
	function buscaSaudacao()
	{
		$horario = intval(strtotime("H"), 10);
		if ($horario <= 12) {
			return "Bom dia";
		} elseif ($horario <= 18) {
			return "Boa tarde";
		} else {
			return "Boa noite";
		}
	}

	/**
	 * Gera o corpo do email.
	 * @param string $cliente - Cliente.
	 * @param string $documento - Documento do cliente.
	 * @return string - Corpo do email.
	 */
	function bodyEmail($cliente, $documento)
	{
		
		$htm = '<!DOCTYPE html>
				<html lang="pt-BR">
				<head>
					<meta charset="UTF-8">
					<title>Ativação de Acesso - Telemedicina YouCast</title>
				</head>
				<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

					<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5;padding:20px 0;">
						<tr>
							<td align="center">

								<table width="600" cellpadding="0" cellspacing="0" border="0"
									style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
									<tr>
										<td align="center" style="background-color:#0056b3;padding:25px;">
											<h1 style="margin:0;color:#ffffff;font-size:24px;">
												Telemedicina YouCast
											</h1>
										</td>
									</tr>
									<tr>
										<td style="padding:30px;color:#333333;font-size:16px;line-height:1.6;">

											<p style="margin-top:0;">
												<strong>Prezado cliente,</strong>
											</p>

											<p>
												<strong>'. $this->buscaSaudacao() .'</strong>
											</p>

											<p>
												Seu acesso à plataforma de <strong>Telemedicina YouCast</strong>
												foi ativado com sucesso.
											</p>

											<p>
												Para realizar o primeiro acesso, utilize as seguintes credenciais:
											</p>

											<table cellpadding="8" cellspacing="0" border="0"
												style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;width:100%;">
												<tr>
													<td>
														<strong>Usuário:</strong> '. $cliente .'
													</td>
												</tr>
												<tr>
													<td>
														<strong>Senha do acesso (CPF):</strong>
														'. $this->mascaraCPF($documento) .'
													</td>
												</tr>
											</table>

											<p style="margin-top:25px;">
												Para concluir seu cadastro e acessar todos os recursos disponíveis,
												clique no botão abaixo:
											</p>

											<p style="text-align:center;margin:30px 0;">
												<a href="'. $this->linkAcesso() .'"
												style="background-color:#28a745;
														color:#ffffff;
														text-decoration:none;
														padding:14px 30px;
														border-radius:5px;
														display:inline-block;
														font-weight:bold;">
													Acessar Plataforma
												</a>
											</p>

											<p>
												Caso o botão acima não funcione, copie e cole o link abaixo em seu navegador:
											</p>

											<p style="word-break:break-all;">
												<a href="'. $this->linkAcesso() .'" style="color:#0056b3;">
													'. $this->linkAcesso() .'
												</a>
											</p>

											<p>
												Após o primeiro acesso, recomendamos a atualização de sua senha e a conferência dos seus dados cadastrais.
											</p>

											<p>
												Em caso de dúvidas ou dificuldades no acesso, entre em contato com nossa equipe de suporte.
											</p>

											<p style="margin-top:30px;">
												Atenciosamente,<br>
												<strong>Equipe de Telemedicina YouCast</strong>
											</p>

										</td>
									</tr>

									<tr>
										<td align="center"
											style="background-color:#f8f9fa;padding:20px;color:#666666;font-size:12px;">
											Esta é uma mensagem automática. Por favor, não responda este e-mail.
										</td>
									</tr>

								</table>

							</td>
						</tr>
					</table>

				</body>
				</html>';

		return $htm;
	}
}
