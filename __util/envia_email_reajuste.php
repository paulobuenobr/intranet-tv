<?

set_time_limit(0);
ignore_user_abort(1);

require_once("../global.inc.php");
require_once "../_global/PHPMailer/PHPMailerAutoload.php";

$envia_email = new envia_email($bancodedados);
$envia_email->enviar();

class envia_email {

  var $remetente_email;
  var $remetente_nome;
  var $assunto;
  var $bancodedados;

  function envia_email($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

	##############
	### Enviar ###
	##############

	function enviar() {
	  $clientes = $this->buscaClientes();
	  foreach ($clientes as $cliente) {
      $this->enviarEmail($cliente["nome"], $cliente["emails"]);
	  }
	}

	function enviarEmail($nome, $emails) {
    $mail = new PHPMailer();

    $mail->isSMTP();
		$mail->Host = "intramail.oletelecom.com.br";
		$mail->SMTPAuth = true;
		$mail->Username = "boleto";
		$mail->Password = "xzj9zCsn";
		$mail->SMTPSecure = "tls";
		$mail->Port = 587;      

		$mail->From = "boleto@oletelecom.com.br";
		$mail->addReplyTo("financeiro@oletelecom.com.br", "Olé Telecom");
		$mail->FromName = "Olé Telecom";
		$mail->Subject = "Reajuste de mensalidade";
		
		$mail->isHTML(true);
    
		for ($i = 0; $i < count($emails); $i++) {
			$mail->addAddress($emails[$i], $nome);
		}
		
		$mail->Body = $this->getConteudo();
		
		$mail->AddEmbeddedImage('/usr/local/www/apache24/data/intranet/imagens/logo_ole_pequeno.jpg', "logo@oletelecom.com.br");
    
		if ($mail->send()) {
			$this->logar("OK", implode(", ", $emails));
			sleep(5);
		} else {
			$this->logar("ERRO", implode(", ", $emails));
		}
		    
	}
		
	function logar($acao, $conteudo) {
		$file = fopen(DIR_RAIZ."/log/emails_reajuste.log", "a");
		fwrite($file, sprintf("%s %-4.4s %s\r\n", date("d/m/Y H:i"), $acao, $conteudo));
		fclose($file);
	}
	
	##############
	### Buscar ###
	##############

	function buscaClientes() {
		//return array(array("id" => 1, "nome" => "Murilo Fernando de Oliveira", "emails" => array("murilo@firsti.com.br")));
		
		$clientes = array();
		
    $query = "
			SELECT
			  tab_clientes.cli_id AS id,
			  tab_clientes.cli_nome AS nome
			FROM
			  tab_clientes
			WHERE
				cls_id = 2";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $dados["emails"] = $this->buscaEmails($dados["id"]);
	    if ($dados["emails"]) {
	    	$clientes[] = $dados;
	    }
    }
    return $clientes;
	}
		
	function buscaEmails($cliente_id) {
    $query = "
	    SELECT
	      CONCAT(ccm_usuario, '@', ccm_dominio) AS email
	    FROM
	      tab_clientes_contatos_emails
	    WHERE
	      cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[] = $dados["email"];
    }
    return $emails;
	}

	function getConteudo() {
    return '
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
  <title>Olé Telecom</title>
</head>
<body>
  <table width="400" border="0" cellpadding="0" cellspacing="0">
  	<tr>
  		<td>
				<p>Prezado Assinante,</p>
				<p>Frente às alterações ocorridas na política econômica nacional, impactando diretamente em nossa planilha de custos, informamos que a partir do mês de Dezembro/2015 estaremos aplicando o reajuste na ordem de 9,93% sobre a nossa tabela de preços. Com base na disposição da ANATEL, através da Resolução nº 632, de 7 de março de 2014.</p>
				<p>Contamos com a vossa compreensão, pois nosso principal objetivo é a melhora constante na prestação de serviços, primando pela qualidade e excelência, visando manter e ampliar a sua satisfação.</p>
				<p>Atenciosamente,</p>
				<p>A Direção</p>
				<img src="cid:logo@oletelecom.com.br" width="122" height="71">
  		</tr>
  </table>
</body>
</html>
';
	}
	
}

?>
