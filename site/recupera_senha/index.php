<?php

if ($_POST) {
	
	if ($_GET["hash"]) {

		$account = new account($_GET["hash"]);
		
		$nomeParceiro = $account->getNomeParceiro($_GET["hash"]);
		if (!$nomeParceiro) {
			$nomeParceiro = "";
		}

		$logoParceiro = $account->getLogoParceiro($_GET["hash"]);
		if (!$logoParceiro) {
			$logoParceiro = false;
		}

		$result = $account->setPassword($_POST["password"]);
		
		if (!$result) {
			header("Location: ./?error=Erro de comunicação com o servidor&hash={$_GET["hash"]}");
			die();
		} else if ($result["status"] == "error") {
			header("Location: ./?error={$result["message"]}&hash={$_GET["hash"]}");
			die();
		} else {
			header("Location: ./?status=success");
			die();
		}
		
	} else {
	
		header("Location: ./");
		die();
		
	}
	
} else {
	
	if ($_GET["hash"]) {

		$account = new account($_GET["hash"]);
		
		$nomeParceiro = $account->getNomeParceiro($_GET["hash"]);
		if (!$nomeParceiro) {
			$nomeParceiro = "";
		}

		$logoParceiro = $account->getLogoParceiro($_GET["hash"]);
		if (!$logoParceiro) {
			$logoParceiro = false;
		}

		$username = $account->getUsername();
		
		if ($username) {
			$action = "change_password";
		} else {
			$action = "link_expired";
		}
		
	} else if ($_GET["status"] == "success") {
		$action = "success";
	} else {
		$action = "page_not_found";
	}

}

/**
 * @property string $url
 * @property string $hash
 */
class account {
	
	/**
	 * @var string
	 */
	// var $url = "https://oletv.oletelecom.com.br/rest/drm/";
	var $url = "http://localhost/intranet/intranet-tv/rest/drm/";
	
	/**
	 * @var string
	 */
	var $hash;
	
	/**
	 * @param string $hash
	 */
	function __construct($hash) {
		
		$this->hash = $hash;
		
	}
	
	/**
	 * @return string|null
	 */
	function getUsername() {
		
		$result = $this->execute(array(
			"service" => "passwordRecoveryGetUsername",
			"hash" => $this->hash
		));
	
		return $result != null && $result["status"] == "success" ? $result["usuario"] : null;
	}
	
	function getLogoParceiro() {
		
		$result = $this->execute(array(
			"service" => "passwordRecoveryGetLogoParceiro",
			"hash" => $this->hash
		));
		
		return $result != null && $result["status"] == "success" ? $result["logo_parceiro"] : null;
	}

	/**
	 * @param string $hash
	 * @param string $username
	 * @return string|null
	 */
	function getNomeParceiro($hash, $username = null) {
		
		$result = $this->execute(array(
			"service" => "passwordRecoveryGetNomeParceiro",
			"hash" => $hash,
			"username" => $username
		));
		
		return $result != null && $result["status"] == "success" ? $result["nome_parceiro"] : null;
	}
	
	/**
	 * @param string $password
	 * @return array|false
	 */
	function setPassword($password) {
		
		$result = $this->execute(array(
			"service" => "passwordRecoverySetPassword",
			"hash" => $this->hash,
			"ip" => $_SERVER['REMOTE_ADDR']
		), array(
			"password" => $password
		));
	
		return $result;
		
	}
	
	/**
	 * @param array $get_data
	 * @param array|null $post_data
	 * @return array|false
	 */
	function execute($get_data, $post_data = null) {

		$get_data = http_build_query($get_data);
		$post_data = $post_data ? http_build_query($post_data) : "";
		
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_POST, strlen($post_data) > 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("CONTENT-LENGTH: ".strlen($post_data)));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		
		if ($post_data) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		}

		$url = "{$this->url}?{$get_data}";
		curl_setopt($curl, CURLOPT_URL, $url);
		
		$response_body = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($curl);
		curl_close($curl);
		
		$resultado = json_decode($response_body, true);
		$json_error = json_last_error();
		$json_ok = ($json_error === JSON_ERROR_NONE) && !empty($resultado);
		
		if ($http_code != 200 || !$json_ok) {
			$this->logError($url, $post_data, $http_code, $response_body, $curl_error, $json_error);
		}

		if ($http_code == 200 && $json_error === JSON_ERROR_NONE) {
			return $resultado;
		}

		return false;

	}
	
	/**
	 * @param string $url
	 * @param string $post_data
	 * @param int $http_code
	 * @param string $response_body
	 * @param string $curl_error
	 * @param int $json_error
	 */
	function logError($url, $post_data, $http_code, $response_body, $curl_error, $json_error) {
		
		$log_file = __DIR__ . "/account.log";
		
		$log = array(
			"datetime" => date("Y-m-d H:i:s"),
			"request" => array(
				"url" => $url,
				"post_data" => $post_data,
				"remote_addr" => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null
			),
			"response_code" => $http_code,
			"response_body" => $response_body,
			"curl_error" => $curl_error,
			"json_error" => json_last_error_msg()
		);
		
		@file_put_contents(
			$log_file,
			json_encode($log, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
			FILE_APPEND | LOCK_EX
		);
		
	}
	
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Alterar senha</title>
		<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16">
		<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="favicon-64x64.png" sizes="64x64">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
		<style>
			:root {
				--purple: #4b17b0;
				--purple-dark: #3a1290;
				--purple-light: #ede7f8;
				--text-dark: #333333;
				--text-muted: #777777;
				--border: #d9d9d9;
				--bg-page: #f4f5f7;
				--error: #d93025;
				--success: #1e7e34;
			}

			* {
				box-sizing: border-box;
			}

			body {
				font-family: "Inter", sans-serif;
				margin: 0;
				min-height: 100vh;
				background: var(--bg-page);
				color: var(--text-dark);
			}

			.page-header {
				background: linear-gradient(135deg, #4b17b0 0%, #3a1290 55%, #2d0f73 100%);
				padding: 28px 16px 32px;
				text-align: center;
				position: relative;
				overflow: hidden;
			}

			.page-header::before {
				content: "";
				position: absolute;
				inset: 0;
				background:
					radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.08) 0%, transparent 45%),
					radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.06) 0%, transparent 40%);
				pointer-events: none;
			}

			.page-header__inner {
				position: relative;
				z-index: 1;
			}

			.page-header img {
				height: 42px;
				width: auto;
				display: block;
				margin: 0 auto 10px;
			}

			.page-header__tagline {
				margin: 0;
				color: rgba(255, 255, 255, 0.92);
				font-size: 14px;
				font-weight: 400;
			}

			.page-main {
				padding: 32px 16px 48px;
				display: flex;
				flex-direction: column;
				align-items: center;
			}

			.card {
				width: 100%;
				max-width: 520px;
				background: #ffffff;
				border-radius: 16px;
				box-shadow: 0 8px 32px rgba(75, 23, 176, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
				padding: 40px 36px 36px;
			}

			.card__icon {
				width: 56px;
				height: 56px;
				border-radius: 50%;
				background: var(--purple-light);
				display: flex;
				align-items: center;
				justify-content: center;
				margin: 0 auto 20px;
				color: var(--purple);
			}

			.card__icon svg {
				width: 26px;
				height: 26px;
			}

			.card__title {
				margin: 0 0 8px;
				font-size: 24px;
				font-weight: 700;
				text-align: center;
				color: var(--text-dark);
			}

			.card__subtitle {
				margin: 0 0 24px;
				font-size: 14px;
				text-align: center;
				color: var(--text-muted);
				line-height: 1.5;
			}

			.account-info {
				display: flex;
				align-items: flex-start;
				gap: 10px;
				padding: 16px 0 24px;
				border-top: 1px solid #eeeeee;
				border-bottom: 1px solid #eeeeee;
				margin-bottom: 24px;
				font-size: 14px;
				line-height: 1.5;
				color: var(--text-muted);
			}

			.account-info svg {
				flex-shrink: 0;
				margin-top: 2px;
				color: var(--purple);
			}

			.account-info strong {
				color: var(--text-dark);
				font-weight: 600;
			}

			.form-group {
				margin-bottom: 20px;
			}

			.form-group label {
				display: block;
				margin-bottom: 8px;
				font-size: 14px;
				font-weight: 600;
				color: var(--text-dark);
			}

			.input-wrap {
				position: relative;
			}

			.input-wrap input {
				width: 100%;
				padding: 12px 44px 12px 14px;
				border: 1px solid var(--border);
				border-radius: 8px;
				font-size: 14px;
				font-family: inherit;
				color: var(--text-dark);
				background: #ffffff;
				transition: border-color 0.2s, box-shadow 0.2s;
			}

			.input-wrap input::placeholder {
				color: #aaaaaa;
			}

			.input-wrap input:focus {
				outline: none;
				border-color: var(--purple);
				box-shadow: 0 0 0 3px rgba(75, 23, 176, 0.12);
			}

			.toggle-password {
				position: absolute;
				right: 12px;
				top: 50%;
				transform: translateY(-50%);
				background: none;
				border: none;
				padding: 4px;
				cursor: pointer;
				color: #999999;
				display: flex;
				align-items: center;
				justify-content: center;
			}

			.toggle-password:hover {
				color: var(--purple);
			}

			.form-hint {
				margin: 8px 0 0;
				font-size: 12px;
				color: var(--text-muted);
			}

			.btn-submit {
				width: 100%;
				margin-top: 8px;
				padding: 14px 24px;
				background: var(--purple);
				border: none;
				border-radius: 8px;
				color: #ffffff;
				font-size: 15px;
				font-weight: 600;
				font-family: inherit;
				cursor: pointer;
				transition: background 0.2s;
			}

			.btn-submit:hover {
				background: var(--purple-dark);
			}

			.message {
				margin: 16px 0 0;
				font-size: 14px;
				line-height: 1.5;
				text-align: center;
			}

			.message--error {
				color: var(--error);
			}

			.message--success {
				color: var(--success);
			}

			.card__body-text {
				margin: 0 0 12px;
				font-size: 15px;
				line-height: 1.6;
				color: var(--text-muted);
				text-align: center;
			}

			.page-footer {
				margin-top: 32px;
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				font-size: 12px;
				color: var(--text-muted);
				text-align: center;
				max-width: 520px;
				line-height: 1.5;
			}

			.page-footer svg {
				flex-shrink: 0;
				color: #999999;
			}

			@media (max-width: 480px) {
				.card {
					padding: 32px 24px 28px;
				}

				.card__title {
					font-size: 21px;
				}
			}
		</style>
		<script>
			function togglePassword(button) {
				var input = button.parentElement.querySelector("input");
				var isPassword = input.type === "password";
				input.type = isPassword ? "text" : "password";
				button.setAttribute("aria-label", isPassword ? "Ocultar senha" : "Mostrar senha");
			}

			function onSubmit(form) {
				var message = document.getElementById("message");
				message.innerHTML = "";
				message.className = "message message--error";

				if (!form.password.value) {
					message.innerHTML = "Você precisa digitar uma senha.";
					form.password.focus();
					return false;
				}
				if (form.password.value.length < 8) {
					message.innerHTML = "A senha precisa ter no mínimo 8 caracteres.";
					form.password.focus();
					return false;
				}
				if (form.password.value != form.confirmation.value) {
					message.innerHTML = "A confirmação não confere com a senha digitada.";
					form.confirmation.focus();
					return false;
				}
				return true;
			}
		</script>
	</head>
	<body>
		<header class="page-header">
			<div class="page-header__inner">
				<?php if (!$logoParceiro) { ?>
					<img src="logo.png">
				<?php } else { ?>
					<img src="<?=htmlspecialchars($logoParceiro, ENT_QUOTES, "UTF-8")?>" alt="Logo">
				<?php } ?>
				<p class="page-header__tagline">A sua melhor programação</p>
			</div>
		</header>

		<main class="page-main">
			<div class="card">
<?php

switch ($action) {

	case "change_password":
?>
				<div class="card__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
						<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
					</svg>
				</div>
				<h1 class="card__title">Redefina sua senha</h1>
				<p class="card__subtitle">Defina uma nova senha para acessar sua conta.</p>

				<div class="account-info">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
					<span>Alteração de senha da conta: <strong><?=htmlspecialchars($username, ENT_QUOTES, "UTF-8")?></strong></span>
				</div>

				<form id="form" method="POST" onsubmit="return onSubmit(this)">
					<div class="form-group">
						<label for="password">Senha</label>
						<div class="input-wrap">
							<input type="password" id="password" name="password" placeholder="Digite sua nova senha" autocomplete="new-password">
							<button type="button" class="toggle-password" aria-label="Mostrar senha" onclick="togglePassword(this)">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
									<circle cx="12" cy="12" r="3"></circle>
								</svg>
							</button>
						</div>
						<p class="form-hint">Use pelo menos 8 caracteres com letras, números e símbolos.</p>
					</div>

					<div class="form-group">
						<label for="confirmation">Confirmação</label>
						<div class="input-wrap">
							<input type="password" id="confirmation" name="confirmation" placeholder="Confirme sua nova senha" autocomplete="new-password">
							<button type="button" class="toggle-password" aria-label="Mostrar senha" onclick="togglePassword(this)">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
									<circle cx="12" cy="12" r="3"></circle>
								</svg>
							</button>
						</div>
					</div>

					<button type="submit" class="btn-submit">Enviar</button>
					<p id="message" class="message message--error"><?=isset($_GET["error"]) ? htmlspecialchars($_GET["error"], ENT_QUOTES, "UTF-8") : ""?></p>
				</form>
				<script>
					document.getElementById("form").password.focus();
				</script>
<?php

	break;

	case "success":
?>
				<div class="card__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
						<polyline points="22 4 12 14.01 9 11.01"></polyline>
					</svg>
				</div>
				<h1 class="card__title">Senha alterada</h1>
				<p class="card__body-text">Sua senha foi alterada com sucesso. Você já pode acessar sua conta com a nova senha.</p>
<?php

	break;

	case "page_not_found":
?>
				<div class="card__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
				</div>
				<h1 class="card__title">Página não encontrada</h1>
				<p class="card__body-text">O endereço acessado não é válido. Verifique o link recebido e tente novamente.</p>
<?php

	break;

	case "link_expired":
?>
				<div class="card__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"></circle>
						<polyline points="12 6 12 12 16 14"></polyline>
					</svg>
				</div>
				<h1 class="card__title">Link expirado</h1>
				<p class="card__body-text">Esse link não tem mais validade.</p>
				<p class="card__body-text">Solicite um novo link ou entre em contato com o suporte.</p>
				<p class="card__body-text">Obrigado pela compreensão.</p>
				</br>
				<p class="card__body-text"><b><?=htmlspecialchars($nomeParceiro, ENT_QUOTES, "UTF-8")?></b></p>
				
<?php

	break;

}

?>
			</div>

			<footer class="page-footer">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
				</svg>
				<span>Licença Anatel SEAC ATO Nº 2523, DE 22 DE JULHO DE 2016.</span>
			</footer>
		</main>
	</body>
</html>
