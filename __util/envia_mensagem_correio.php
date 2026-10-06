<?

set_time_limit(0);
ignore_user_abort(1);

require_once("../global.inc.php");
require_once("../_global/PHPMailer/PHPMailerAutoload.php");

$util = new util($bancodedados);
//$util->enviaEmail("murilo@firsti.com.br", "Murilo Fernando de Oliveira");
$util->executar();

class util {

  private $bancodedados;
	
  ##################
  ### Construtor ###
  ##################

	function util($bancodedados) {
		$this->bancodedados = $bancodedados;
	}
	
	function executar() {
  	$query = "
  		SELECT DISTINCT
  			tab_clientes.cli_id AS cliente_id,
  			tab_clientes.cli_nome AS cliente_nome
  		FROM
  			tab_cobrancas_lotes
	  		INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_lotes.cof_id = tab_cobrancas_formatos.cof_id)
	  		INNER JOIN tab_cobrancas ON (tab_cobrancas_lotes.col_id = tab_cobrancas.col_id)
	  		INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
  		WHERE
  			tab_clientes.cli_id != 22 AND
  			tab_cobrancas.cot_id = 1 AND
				tab_cobrancas.cop_id IN (1, 2) AND
  			tab_cobrancas_formatos.cft_id = 2 AND
  			(SELECT COUNT(tab_cobrancas_filhas.coa_id) FROM tab_cobrancas AS tab_cobrancas_filhas WHERE tab_cobrancas_filhas.coa_pai = tab_cobrancas.coa_id) = 0 AND
  			tab_cobrancas_lotes.col_vencimento = '2015-10-01'";
  	$query = $this->bancodedados->query($query);
  	while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
  		$emails = $this->buscaContatosEmails($dados["cliente_id"]);
  		for ($i = 0; $i < count($emails); $i++) {
  			$this->enviaEmail($emails[$i], $dados["cliente_nome"]);
  		}
  	}
	}

	function enviaEmail($email, $nome) {
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
			$mail->Subject = "Greve dos Correios";
			
			$mail->isHTML(true);
			
			$mail->addAddress($email, $nome);
						
		$mail->Body = "
<html>
	<head>
	</head>
	<body style=\"font-family: Verdana, Arial, sans-serif; font-size: 12px; color: #444444;\">
		<div style=\"width: 480px\">
			<p><b>$nome</b>,</p>
			<p>Devido à greve dos Correios, há a possibilidade do seu boleto chegar com atraso. No entanto, lembramos que a Olé Telecom está disponibilizando, em seu site, a facilidade da emissão de segunda via de boleto de cobrança.</p>
			<p>Para ter acesso, entre no site da Olé Telecom no endereço <a href=\"http://www.ole.net.br/\">www.ole.net.br</a> ou simplesmente <a href=\"http://www.ole.net.br/site/faturas/faturas.php\">clicando aqui</a>. Em seguida, basta informar o CNPJ ou o CPF e a data de nascimento do titular.</p>
			<p>Em caso de duvidas, entre em contato com nosso setor financeiro <a href=\"http://soletelecom.mysuite.com.br/empresas/ole/atendimento.php\">clicando aqui</a> ou através dos telefones <b>0800 600 1228</b> ou <b>48 3028 0000</b>.</p>
			<p>Atenciosamente,</p>
			<p>Olé Telecom</p>
			<p> Setor Financeiro</p>
  		<img src=\"cid:rodape@oletelecom.com.br\" width=\"1242\" height=\"281\">
			</div>
	</body>
</html>";
		
		$mail->AddEmbeddedImage("/usr/local/www/apache24/data/intranet/__util/mail.google.com.png", "rodape@oletelecom.com.br", "logo", "base64", "image/png");
    
		if ($mail->send()) {
			$this->logar("OK", $email);
			sleep(5);
		} else {
			$this->logar("ERRO", $email);
		}
	}
	
  function buscaContatosEmails($cliente_id) {
  	$emails = array();
    $query = "
      SELECT
        CONCAT(ccm_usuario, '@', ccm_dominio) AS email
      FROM
        tab_clientes_contatos_emails
      WHERE
        cli_id = $cliente_id
      ORDER BY
        email";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[] = $dados["email"];
    }
    if (count($emails) == 0) {
      $query = "
        SELECT
          CONCAT(cle_usuario, '@', dom_dominio) AS email
        FROM
          tab_assinaturas
          INNER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
          INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
        WHERE
          cli_id = $cliente_id
        ORDER BY
          email";
      $query = $this->bancodedados->query($query);
      if ($this->bancodedados->linhas($query) == 1) {
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
          $emails[] = $dados["email"];
        }
      }
    }
    return $emails;
  }
		
	function logar($acao, $conteudo) {
		$file = fopen(DIR_RAIZ."/log/emails.log", "a");
		fwrite($file, sprintf("%s %-4.4s %s\r\n", date("d/m/Y H:i"), $acao, $conteudo));
		fclose($file);
	}
  
}

?>
