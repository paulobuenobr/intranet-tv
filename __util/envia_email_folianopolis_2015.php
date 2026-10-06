<?

set_time_limit(0);
ignore_user_abort(1);

require_once("../global.inc.php");
require_once("../_modulos/mensagens/mensagens.emails.class.inc.php");

$envia_email = new envia_email($bancodedados);
$envia_email->enviar();

class envia_email {

  var $remetente_email;
  var $remetente_nome;
  var $assunto;
  var $bancodedados;

  function envia_email($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->remetente_email = "contato@oletelecom.com.br";
    $this->remetente_nome = "Olé Telecom";
  }

	##############
	### Enviar ###
	##############

	function enviar() {
	  $clientes = $this->buscaClientes();
	  foreach ($clientes as $cliente) {
	    foreach ($cliente["emails"] as $email) {
	      $this->enviarEmail($cliente["nome"], $email);
	    }
	  }
	}

	function enviarEmail($nome, $email) {
    // Inicializa Classe de Envio de E-mails
    $mensagens_emails = new mensagens_emails();
    // Limpa Dados Mensagem
    $mensagens_emails->phpMailer->Encoding = "7bit";
    // Define Dados Mensagem
    $mensagens_emails->setRemetente($this->remetente_email, $this->remetente_nome);
    $mensagens_emails->setAssunto("Folianópolis 2015");
    $mensagens_emails->adicionarDestinatario($email, $nome);
    $mensagens_emails->setConteudo($this->getConteudo());
    // Anexos
    $mensagens_emails->adicionarImagem('/usr/local/www/apache24/data/intranet/imagens/logo_ole_pequeno.jpg', "logo@oletelecom.com.br", "", "base64", "image/jpeg");
    if ($mensagens_emails->enviar()) {
    	print "OK - $email";
    	sleep(12);
    } else {
    	print "ERRO - $email";
    }
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
				1";
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
				<p>Promoção cliente Olé Telecom no Folianópolis 2015.</p>
				<p>Curta a página da Olé Telecom no Facebook e Compartilhe a promoção em modo público, para concorrer a dois Abadás no Bloco e um Abadá no Camarote Balada do Folianópolis, serão dois sorteados na Sexta-feira e dois sorteados no Sábado, mas lembre-se precisa curtir e compartilhar a promoção em modo público na página da Olé Telecom no Facebook.</p> 
				<p>Os sorteados serão comunicados as 12:00h do dia 30/10/2015 e deverão comparecer até as 19:30h no Beiramar Shopping, sendo necessário levar um documento com foto e CPF do ganhador.</p>
				<p>Clique no link abaixo:</p>
				<p><a href="http://sorteiefb.com.br/tab/promocao/501333">http://sorteiefb.com.br/tab/promocao/501333</a></p>
				<p>Logo após clique em curtir e no botão quero participar.</p>  		
  			<img src="cid:logo@oletelecom.com.br" width="122" height="71">
  		</tr>
  </table>
</body>
</html>
';
	}
	
}

?>
