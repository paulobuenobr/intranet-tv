<?php

if ($_POST) {
	
	if ($_GET["hash"]) {

		$account = new account($_GET["hash"]);
		
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

class account {
	
	var $url = "https://oletv.oletelecom.com.br/rest/drm/";
	
	var $hash;
	
	function __construct($hash) {
		
		$this->hash = $hash;
		
	}
	
	function getUsername() {
		
		$result = $this->execute(array(
			"service" => "passwordRecoveryGetUsername",
			"hash" => $this->hash
		));
	
		return $result != null && $result["status"] == "success" ? $result["usuario"] : null;
		
	}
	
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

?><!DOCTYPE html>
<html lang="pt-BR">
	<head>
		<meta charset="utf-8">
		<title>Olé TV</title>
		<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16">
		<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="favicon-64x64.png" sizes="64x64">
		<style>
			body {
				font-family: sans-serif;
				margin: 0;
			}
			#header {
				background-color: #302387;
				padding: 20px 0px 15px 0px;
			}
			#header > div {
				max-width: 1140px;
				margin-right: auto;
				margin-left: auto;
			}
			#header img {
				width: 110px;
			}
			#content {
				max-width: 1140px;
				margin-top: 100px;
				margin-right: auto;
				margin-left: auto;
			}
			input[type="password"] {
				margin: 5px;
				padding: 5px;
				width: 200px;
				box-sizing: border-box;
			}
			input[type="submit"] {
				box-sizing: border-box;
				margin: 5px;
				padding: 7px;
				background-color: #302387;
				border: 0;
				color: #ffffff;
				width: 200px;
				cursor: pointer;
			}
			#message {
				color: #ff0000;
			}
		</style>
		<script>
			function onSubmit(form) {
				document.getElementById('message').innerHTML = '';
				if (!form.password.value) {
					document.getElementById('message').innerHTML = 'Você precisa digitar uma senha.';
					form.password.focus();
					return false;
				}
				if (form.password.value.length < 6) {
					document.getElementById('message').innerHTML = 'A senha precisa ter no mínimo 6 caracteres.';
					form.password.focus();
					return false;
				}
				if (form.password.value != form.confirmation.value) {
					document.getElementById('message').innerHTML = 'A confirmação não confere com a senha digitada.';
					form.confirmation.focus();
					return false;
				}
				return true;
			}
		</script>
	</head>
	<body>
		<div id="header">
			<div><img src="logo.png" /></div>
		</div>
		<div id="content">
<?php

switch ($action) {

	case "change_password":
?>
	<p>Alteração de senha da conta: <?=$username?></p>
	<form id="form" method="POST" onsubmit="return onSubmit(this)">
		<div><input type="password" name="password" placeholder="Senha" /></div>
		<div><input type="password" name="confirmation" placeholder="Confirmação" /></div>
		<div><input type="submit" value="Enviar" /></div>
		<p id="message"><?=$_GET["error"]?></p>
	</form>
	<script>
		document.getElementById('form').password.focus();
	</script>
<?php

	break;

	case "success":
?>
	<p>Senha alterada com sucesso.</p>
<?php

	break;

	case "page_not_found":
?>
	<p>Página não encontrada.</p>
<?php

	break;

	case "link_expired":

?>
	<p>Esse link não tem mais validade.</p>
	<p>Solicite um novo link ou entre em contato com o suporte.</p>
	<p>Obrigado pela compreensão,</p>
	<p>OléTV</p>
<?php

	break;

}

?>
		</div>
	</body>
</html>
