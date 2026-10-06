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

		$mail->From = "contato@oletv.net.br";
		$mail->FromName = "Olé TV";
		
		$mail->Subject = "Informativo de modernização e manutenção programada";
		
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
			<p>São José, SC, 31 de Março de 2020</p>
			<p>Prezado Cliente,</p>
			<p>Assunto: Informativo de modernização e manutenção programada.</p>
			<p>Informamos que estaremos realizando uma interrupção dos nossos serviços de TV por assinatura, a interrupção se faz necessária para modernizar a infraestrutura e equipamentos da Olé TV, a interrupção está programada para acontecer conforme a data e  janela de manutenção abaixo:</p>
			<p>Data: 02/04/2020 - Quinta Feira.<br />
			Janela de manutenção: 02:00 às 05:00<br />
			Motivo: Ampliação do Core da Rede.<br />
			Sistemas e serviços afetados: Todos.</p>
			<p>#Ampliando a nossa capacidade de transmissão e melhorando ainda mais o nosso atendimento para você.</p>
			<p>Atenciosamente,</p>
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
