<?php

require_once ("../../global.inc.php");

$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $bancodedados, null, false);

switch ($_GET["service"]) {
	
	case "accountDeviceActivation":
	
		if ($clientes_iptv->ativarContrato(addslashes($_GET["reference"]))) {
			print json_encode(array(
				"status" => "success"
			));
		}

		break;

	case "passwordRecoveryRequest":

		if ($clientes_iptv->recuperarSenha(addslashes($_GET["reference"]))) {
			print json_encode(array(
				"status" => "success"
			));
		} else {
			var_dump($clientes_iptv->ultimo_erro);
		}
	
		break;
		
	case "passwordRecoveryGetUsername":
	
		if (($usuario = $clientes_iptv->buscaUsuarioByHash(addslashes($_GET["hash"]))) != null) {
			print json_encode(array(
				"status" => "success",
				"usuario" => $usuario
			));
		}
	
		break;

	case "passwordRecoveryGetNomeParceiro":
	
		if (($nomeParceiro = $clientes_iptv->buscaNomeParceiroByUsuario(addslashes($_GET["hash"]), addslashes($_GET["username"]))) != null) {
			
			if (!mb_check_encoding($nomeParceiro, "UTF-8")) {
				$nomeParceiro = mb_convert_encoding($nomeParceiro, "UTF-8", "ISO-8859-1");
			}

			print json_encode(array(
				"status" => "success",
				"nome_parceiro" => $nomeParceiro
			), JSON_UNESCAPED_UNICODE);
		}

		break;

	case "passwordRecoveryGetLogoParceiro":
	
		if (($logoParceiro = $clientes_iptv->buscaLogoParceiroByUsuario(addslashes($_GET["hash"]))) != null) {
			
			print json_encode(array(
				"status" => "success",
				"logo_parceiro" => $logoParceiro
			), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		} 

		break;
	
	case "passwordRecoverySetPassword":
	
		if ($clientes_iptv->alterarSenha(addslashes($_GET["hash"]), $_POST["password"], $_GET["ip"])) {
			print json_encode(array(
				"status" => "success"
			));
		} else {
			$mensagem = $clientes_iptv->ultimo_erro;
			if (!mb_check_encoding($mensagem, "UTF-8")) {
				$mensagem = mb_convert_encoding($mensagem, "UTF-8", "ISO-8859-1");
			}
			print json_encode(array(
				"status" => "error",
				"message" => $mensagem
			));
		}

		break;

	default:
	
		header("HTTP/1.0 404 Not Found");

}

?>