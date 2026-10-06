<?

require_once "_global/PHPMailer/PHPMailerAutoload.php";

class mensagens_emails {
	
	var $phpMailer;
	
	function __construct() {
		
		$this->phpMailer = new PHPMailer();
		
		$this->phpMailer->isSMTP();
		$this->phpMailer->isHTML(true);
		$this->phpMailer->SMTPKeepAlive = true;
		$this->phpMailer->Host = MENSAGEM_SERVIDOR_SMTP;
		
	}
	
	function setSMTP($host, $porta, $usuario, $senha) {
		
		$this->phpMailer->SMTPAuth = true;
		$this->phpMailer->Host = $host;
		$this->phpMailer->Port = $porta;
		$this->phpMailer->Username = $usuario;
		$this->phpMailer->Password = $senha;
		
	}
	
	function setRemetente($email, $nome) {
		
		$this->phpMailer->From = $email;
		$this->phpMailer->FromName = $nome;
		
	}
	
	function adicionarReply($email, $nome) {
		
		$this->phpMailer->AddReplyTo($email, $nome);
		
	}
	
	function limparReplys() {
		
		$this->phpMailer->ClearReplyTos();
		
	}
	
	function setAssunto($assunto) {
		
		$this->phpMailer->Subject = $assunto;
		
	}
	
	function setConteudo($conteudo_html = "", $conteudo_texto = "") {
		
		if ($conteudo_html) {
			$this->phpMailer->Body = $conteudo_html;
		}
		if ($conteudo_texto) {
			$this->phpMailer->AltBody = $conteudo_texto;
		}
		
	}
	
	function adicionarDestinatario($email, $nome = "") {
		
		$this->phpMailer->AddAddress($email, $nome);
		
	}
	
	function limparDestinatarios() {
		
		$this->phpMailer->ClearAddresses();
		
	}
	
	function adicionarDestinatarioCopia($email, $nome = "") {
		
		$this->phpMailer->AddCC($email, $nome);
		
	}
	
	function limparDestinatarioCopia() {
		
		$this->phpMailer->ClearCCs();
		
	}
	
	function adicionarDestinatarioCopiaOculta($email, $nome = "") {
		
		$this->phpMailer->AddBCC($email, $nome);
		
	}
	
	function limparDestinatarioCopiaOculta() {
		
		$this->phpMailer->ClearBCCs();
		
	}
	
	function adicionarImagem($path, $cid, $name = "", $encoding = "base64", $type = "application/octet-stream") {
		
		$this->phpMailer->AddEmbeddedImage($path, $cid, $name, $encoding, $type);
		
	}
	
	function adicionarAnexo($string, $filename, $encoding = "base64", $type = "application/octet-stream") {
		
		$this->phpMailer->AddStringAttachment($string, $filename, $encoding, $type);
		
	}
	
	function adicionarAnexoArquivo($path, $name = "", $encoding = "base64", $type = "application/octet-stream") {
		
		$this->phpMailer->AddAttachment($path, $name, $encoding, $type);
		
	}
	
	function limparAnexos() {
		
		$this->phpMailer->ClearAttachments();
		
	}
	
	function enviar() {
		
		if ($this->phpMailer->Send()) {
			return true;
		} else {
			$this->erro = $this->phpMailer->ErrorInfo;
			return false;
		}
		
	}
	
	function getErro() {
		
		return $this->erro;
		
	}
	
	function fecharSMTP() {
		
		$this->phpMailer->SmtpClose();
		
	}
	
}

?>
