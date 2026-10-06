<?php

set_time_limit(0);
ignore_user_abort(1);

require_once("../global.inc.php");
require_once "../_global/PHPMailer/PHPMailerAutoload.php";

$util = new util($bancodedados);

//$util->enviaEmail("Murilo Fernando de Oliveira", array("murilo@firsti.com.br"));

$util->executar();

class util {

	private $bancodedados;

	##################
	### Construtor ###
	##################

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}
	
	function executar() {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome
			FROM
				tab_clientes
			WHERE
				tab_clientes.cls_id = 2");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$emails = $this->buscaContatosEmails($dados["cliente_id"]);
			
			if (count($emails) > 0) {
				$this->enviaEmail($dados["cliente_nome"], $emails);
			}
			
		}
		
	}

	function enviaEmail($nome, $emails) {
		
		$mail = new PHPMailer();

		$mail->isSMTP();
		$mail->Host = "mail2.ole.net.br";
		$mail->SMTPAuth = true;
		$mail->Username = "boleto@oletv.net.br";
		$mail->Password = "bA)E,lfa~d8Kq+jE6Gee!H";
		$mail->SMTPSecure = "tls";
		$mail->Port = 587;      

		$mail->SMTPOptions = array (
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->From = "contato@oletv.net.br";
		$mail->FromName = "Olé TV";
		
		$mail->Subject = "Aviso importante";
		
		$mail->isHTML(true);
    
		for ($i = 0; $i < count($emails); $i++) {
			$mail->addAddress($emails[$i], $nome);
		}
		
		$mail->Body = "
<html>
	<head>
	</head>
	<body style=\"font-family: Verdana, Arial, sans-serif; font-size: 12px; color: #444444;\">
		<div style=\"width: 480px\">
			<p>Prezado Cliente,</p>
			<p>Informamos que a partir de hoje, os clientes Olé TV também terão acesso às suas faturas através do site:</p>
			<p>https://www.oletv.net.br/faturas/faturas.php</p>
			<p>Para isso, deverá ser informado o número do CPF e a data de nascimento (de acordo com os dados informados no cadastro).</p>
			<p>Atenciosamente,</p>
			<p>Equipe Olé TV</p>
		</div>
	</body>
</html>";
    
		if ($mail->send()) {
			$this->logar("OK", implode(", ", $emails));
			sleep(2);
		} else {
			$this->logar("ERRO", implode(", ", $emails));
		}
		
	}
	
	function buscaContatosEmails($cliente_id) {
		
		$emails = array();
		
		$query = $this->bancodedados->query("
			SELECT
				CONCAT(ccm_usuario, '@', ccm_dominio) AS email
			FROM
				tab_clientes_contatos_emails
			WHERE
				cli_id = $cliente_id
			ORDER BY
				email");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$emails[] = $dados["email"];
		}
		
		return $emails;
	
	}

	function logar($acao, $conteudo) {
		
		print sprintf("%s %-4.4s %s\r\n", date("d/m/Y H:i"), $acao, $conteudo);
		
		$file = fopen(DIR_RAIZ."/log/mensagem_manutencao.log", "a");
		
		fwrite($file, sprintf("%s %-4.4s %s\r\n", date("d/m/Y H:i"), $acao, $conteudo));
		fclose($file);
		
	}
  
}

?>
