<?

set_time_limit(0);
ignore_user_abort(1);

require_once("../global.inc.php");
require_once "../_global/PHPMailer/PHPMailerAutoload.php";

$envia_email = new envia_email($bancodedados);
$envia_email->enviar();
//$envia_email->enviarEmail("Murilo Fernando de Oliveira", array("murilo@firsti.com.br"));

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

	function enviar($cliente_id = null) {
		
	  $clientes = $this->buscaClientes($cliente_id);
	  
	  foreach ($clientes as $cliente) {
      $this->enviarEmail($cliente["nome"], $cliente["emails"]);
	  }
	  
	}

	function enviarEmail($nome, $emails) {
		
    $mail = new PHPMailer();

    $mail->isSMTP();
		$mail->Host = "intramail.oletelecom.com.br";
		$mail->SMTPAuth = true;
		$mail->Username = "contato@oletelecom.com.br";
		$mail->Password = "Hy3;RqM?Wd7c!V#RuEmhg@Tffo4nH";
		$mail->SMTPSecure = "tls";
		$mail->Port = 587;      

		$mail->From = "contato@oletelecom.com.br";
		$mail->FromName = "Olé Telecom";
		$mail->Subject = "Feliz Natal";
		
		$mail->isHTML(true);
    
		for ($i = 0; $i < count($emails); $i++) {
			$mail->addAddress($emails[$i], $nome);
		}
				
		$mail->Body = '
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
  <title>Olé Telecom</title>
</head>
<body>
	<img src="cid:natal2017@oletelecom.com.br" width="800" height="800">
</body>
</html>
';
		
		$mail->AddEmbeddedImage('/usr/local/www/apache24/data/intranet/__util/natal2017.png', "natal2017@oletelecom.com.br");
    
		if ($mail->send()) {
			$this->logar("OK", implode(", ", $emails));
			sleep(5);
		} else {
			$this->logar("ERRO", implode(", ", $emails));
		}
		    
	}
		
	function logar($acao, $conteudo) {
		$file = fopen(DIR_RAIZ."/log/emails_natal_2017.log", "a");
		fwrite($file, sprintf("%s %-4.4s %s\r\n", date("d/m/Y H:i"), $acao, $conteudo));
		fclose($file);
	}
	
	##############
	### Buscar ###
	##############

	function buscaClientes($cliente_id = null) {
		
		$clientes = array();
		
    $query = "
			SELECT
			  tab_clientes.cli_id AS id,
			  tab_clientes.cli_nome AS nome
			FROM
			  tab_clientes
			WHERE".($cliente_id ? "
				cli_id = $cliente_id" : "
				cls_id = 2")."
			ORDER BY
				RAND()";
    
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
		
		$emails = array();
		
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
	
}

?>
