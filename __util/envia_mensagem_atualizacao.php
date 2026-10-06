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
				tab_clientes.cls_id = 2 AND
				tab_clientes.cli_id IN (116558, 116148, 114738, 114828, 115349, 66568, 114798, 115360, 117440, 116071, 67230, 115449, 76700, 117295, 116956, 114669, 117742, 20673, 114962, 117766, 104112, 115473, 86217, 115257, 115291, 107757, 2177, 62493, 114757, 83755, 115299, 115298, 96337, 115953)");
				
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
		$mail->Host = "mail.ole.net.br";
		$mail->SMTPAuth = true;
		$mail->Username = "suporte@oletv.net.br";
		$mail->Password = "18@%78NgmIjp";
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
		
		$mail->Subject = "Informativo de modernização do seu equipamento";
		
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
			<p>Com o intuito de melhorar a qualidade dos nossos serviços, informamos que, a partir de amanhã, efetuaremos a atualização do software do seu equipamento.</p>
			<p>Para tanto, basta aceitar a atualização quando a mensagem aparecer na sua tela e proceder conforme as orientações apresentadas.</p>
			<p>A atualização é obrigatória, e deve ser efetuada assim que possível.</p>
			<p>Em caso de dúvidas, por favor, entre em contato conosco.</p>
			<p>Obrigado pela atenção,</p>
			<p>A Direção<br />
			OléTV<br />
			Telefones: (48) 4042 6262<br />
			E-mail: contato@oletv.net.br</p>	
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
