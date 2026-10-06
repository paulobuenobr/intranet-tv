<?

set_time_limit(0);

/*
require_once("../../global.inc.php");
require_once("../../_modulos/mensagens/mensagens.emails.class.inc.php");

$envia_email = controle::instanciaObjeto("cron_visitas_instalacoes_chamados", $bancodedados);
$envia_email->verificaVisitasIntalacaoNaoAgendada();

//*/

class cron_visitas_instalacoes_chamados {
	
	var $bancodedados;
	var $remetente_email;
	var $remetente_nome;
	var $assunto;
	
	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->remetente_email = "administrativo@oletelecom.com.br";
		$this->remetente_nome = "Olé Telecom";
		$this->assunto = "Visita não agendada.";
	}
	
	function enviarEmail($codigo_contrato, $cliente_nome, $cliente_id, $data_geracao) {
		// Inicializa Classe de Envio de E-mails
		$mensagens_emails = new mensagens_emails();
		// Limpa Dados Mensagem
		$mensagens_emails->phpMailer->Encoding = '7bit';
		$mensagens_emails->limparDestinatarios();
		$mensagens_emails->limparReplys();
		$mensagens_emails->limparDestinatarioCopia();
		$mensagens_emails->limparDestinatarioCopiaOculta();
		$mensagens_emails->limparAnexos();
		// Define Dados Mensagem
		$mensagens_emails->setRemetente($this->remetente_email, $this->remetente_nome);
		$mensagens_emails->setAssunto($this->assunto);
		$mensagens_emails->adicionarDestinatario("administrativo@oletelecom.com.br", "Dayana");
		$mensagens_emails->setConteudo($this->getConteudo($codigo_contrato, $cliente_nome, $cliente_id, $data_geracao));
		// Anexos
		if ($mensagens_emails->enviar()) {
			print "OK ";
		} else {
			print "ERRO ";
		}
	}
	
	function verificaVisitasIntalacaoNaoAgendada() {
		$query = "
			SELECT
				tab_visitas.vis_id AS visita_id,
				DATE_FORMAT(tab_visitas.vis_data_geracao, '%d/%m/%Y') AS data_geracao,
				tab_visitas.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_contratos.cor_codigo AS codigo_contrato
			FROM
				tab_visitas
				INNER JOIN tab_contratos ON (tab_visitas.cor_id = tab_contratos.cor_id)
				INNER JOIN tab_clientes ON (tab_visitas.cli_id = tab_clientes.cli_id)
			WHERE
				DATE_ADD(tab_visitas.vis_data_geracao, INTERVAL 2 DAY) = CURDATE() AND
				tab_visitas.via_id = 1";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->enviarEmail($dados["codigo_contrato"], $dados["cliente_nome"], $dados["cliente_id"], $dados["data_geracao"]);
		}
	}
	
	function getConteudo($codigo_contrato, $cliente_nome, $cliente_id, $data_geracao) {
		return '
			<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
			  <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
			  <title>Olé Telecom</title>
			</head>
			<body>
				Visita aguardando agendamento há 2 dias: <br>
				[' . $cliente_id . '] ' . $cliente_nome . ' <br>
				Data Geração: ' . $data_geracao . ' <br>
				Código Contrato: ' . $codigo_contrato . ' <br>
			</body>
			</html>
			';
	}

}
?>
