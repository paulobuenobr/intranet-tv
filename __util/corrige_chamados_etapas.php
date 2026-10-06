<?

require_once("../global.inc.php");

$util = new util($bancodedados);
$util->executar();

class util {

	private $bancodedados;

	##################
	### Construtor ###
	##################
	
	private $categorias = array(
		"a:1:{s:8:\"conteudo\";s:10:\"Estrutural\";}" => 113,
		"a:1:{s:8:\"conteudo\";s:10:\"Financeiro\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:10:\"Instalação\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:10:\"Manutenção\";}" => 1052451,
		"a:1:{s:8:\"conteudo\";s:11:\"Agendamento\";}" => 109,
		"a:1:{s:8:\"conteudo\";s:11:\"Call Center\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:13:\"Controladoria\";}" => 1052465,
		"a:1:{s:8:\"conteudo\";s:14:\"Administrativo\";}" => 111,
		"a:1:{s:8:\"conteudo\";s:14:\"Migração PPPoE\";}" => 107,
		"a:1:{s:8:\"conteudo\";s:14:\"T.I. (Diretor)\";}" => 114,
		"a:1:{s:8:\"conteudo\";s:15:\"Desenvolvimento\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:15:\"Suporte Externo\";}" => 1052447,
		"a:1:{s:8:\"conteudo\";s:16:\"Suporte » Outros\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:16:\"Suporte » Sodisa\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:17:\"Técnico (Diretor)\";}" => 115,
		"a:1:{s:8:\"conteudo\";s:17:\"Técnico (Gerente)\";}" => 115,
		"a:1:{s:8:\"conteudo\";s:18:\"Comercial » Outros\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:18:\"Comercial Garopaba\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:18:\"Manutenção de Rede\";}" => 112,
		"a:1:{s:8:\"conteudo\";s:18:\"Pós-venda » Outros\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:18:\"Suporte » Serviços\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:19:\"Comercial » Cliente\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:19:\"Financeiro » Outros\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:19:\"Instalação » Outros\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:19:\"Suporte Empresarial\";}" => 1052453,
		"a:1:{s:8:\"conteudo\";s:20:\"Pós-venda » Contrato\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:21:\"Administração de Rede\";}" => 107,
		"a:1:{s:8:\"conteudo\";s:21:\"Financeiro » Cobrança\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:21:\"Instalação » Ativação\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:21:\"Suporte » CSM Telecom\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:22:\"Comercial » Condomínio\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:22:\"Residencial (Vistoria)\";}" => 1052456,
		"a:1:{s:8:\"conteudo\";s:23:\"Estrutural (Instalação)\";}" => 113,
		"a:1:{s:8:\"conteudo\";s:23:\"Pós-venda » Reclamações\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:23:\"Suporte » Internet fora\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:24:\"Desenvolvimento » Outros\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:24:\"Suporte » Internet lenta\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:24:\"Suporte » Serviços » FTP\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:24:\"Suporte Externo Garopaba\";}" => 1052461,
		"a:1:{s:8:\"conteudo\";s:25:\"Administração de Sistemas\";}" => 106,
		"a:1:{s:8:\"conteudo\";s:26:\"Desenvolvimento » Intranet\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:26:\"Manutenção de Computadores\";}" => 1052451,
		"a:1:{s:8:\"conteudo\";s:27:\"Comercial » Cliente » Venda\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:27:\"Suporte » Serviços » E-mail\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:29:\"Comercial » Cliente » Dúvidas\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:29:\"Instalação » Mudança de Ponto\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:30:\"Suporte » Wave System Internet\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:31:\"Instalação » Ativação » Retorno\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:31:\"Suporte » Controle de Qualidade\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:32:\"Comercial » Condomínio » Contato\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:32:\"Comercial » Condomínio » Plantão\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:32:\"Instalação » Ativação » Agendada\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:33:\"Comercial » Cliente » Informações\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:33:\"Pós-venda » Reclamações » Serviço\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:33:\"Residencial (Vistoria) / Garopaba\";}" => 1052456,
		"a:1:{s:8:\"conteudo\";s:33:\"Suporte » Internet fora » Cliente\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:34:\"Comercial » Cliente » Renegociação\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:34:\"Residencial (Retirada Equipamento)\";}" => 1052455,
		"a:1:{s:8:\"conteudo\";s:34:\"Suporte » Internet fora » Backbone\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:34:\"Suporte » Internet lenta » Cliente\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:35:\"Pós-venda » Contrato » Cancelamento\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:35:\"Suporte » Internet lenta » Backbone\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:36:\"Suporte » Internet fora » Condomínio\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:36:\"Suporte » Serviços » FTP » Adicionar\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:37:\"Pós-venda » Reclamações » Atendimento\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:37:\"Suporte » Internet lenta » Condomínio\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:37:\"Suporte » Serviços » E-mail » Remover\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:37:\"Suporte » Serviços » FTP » Configurar\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:38:\"Estrutural (Manutenção / Modernização)\";}" => 1052452,
		"a:1:{s:8:\"conteudo\";s:38:\"Suporte » Internet fora » Access Point\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:38:\"Suporte » Serviços » Acesso à Internet\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:39:\"Instalação » Mudança de Ponto » Retorno\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:39:\"Manutenção » Manutenção de Computadores\";}" => 1052451,
		"a:1:{s:8:\"conteudo\";s:39:\"Suporte » Internet lenta » Access Point\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:39:\"Suporte » Serviços » E-mail » Adicionar\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:4:\"T.I.\";}" => 114,
		"a:1:{s:8:\"conteudo\";s:40:\"Desenvolvimento » Intranet » Módulo novo\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:40:\"Instalação » Mudança de Ponto » Agendada\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:40:\"Suporte » Serviços » E-mail » Configurar\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:40:\"Suporte » Serviços » FTP » Alterar senha\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:41:\"Financeiro » Cobrança » Reenvio de boleto\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:42:\"Instalação » Mudança de Ponto » Reagendada\";}" => 108,
		"a:1:{s:8:\"conteudo\";s:42:\"Pós-venda » Contrato » Mudança de endereço\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:43:\"Suporte » Internet fora » Cliente » Serviço\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:43:\"Suporte » Serviços » E-mail » Alterar senha\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:44:\"Suporte » Internet fora » Cliente » Hardware\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:44:\"Suporte » Internet fora » Cliente » Software\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:44:\"Suporte » Internet lenta » Cliente » Serviço\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:45:\"Administração de Rede (Teste de equipamentos)\";}" => 107,
		"a:1:{s:8:\"conteudo\";s:45:\"Desenvolvimento » Intranet » Módulo existente\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:45:\"Financeiro » Cobrança » Negociação de débitos\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:45:\"Suporte » Internet fora » Cliente » Pendência\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:45:\"Suporte » Internet lenta » Cliente » Hardware\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:46:\"Financeiro » Cobrança » Desbloqueio temporário\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:46:\"Pós-venda » Contrato » Mudança de titularidade\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:46:\"Suporte » Internet lenta » Cliente » Pendência\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:47:\"Suporte » Internet fora » Backbone » Cabeamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:48:\"Financeiro » Cobrança » Comprovante de pagamento\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:49:\"Comercial » Condomínio » Divulgação / Panfletagem\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:49:\"Financeiro » Cobrança » Questionamento de valores\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:49:\"Suporte » Internet fora » Cliente » Serviço » FTP\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:49:\"Suporte » Internet fora » Condomínio » Cabeamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:50:\"Suporte » Internet fora » Condomínio » Equipamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:50:\"Suporte » Internet lenta » Cliente » Serviço » FTP\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:50:\"Suporte » Internet lenta » Condomínio » Cabeamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:50:\"Suporte » Serviços » Acesso à Internet » Adicionar\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:51:\"Pós-venda » Contrato » Revisão / redução de valores\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:51:\"Suporte » Internet fora » Cliente » Serviço » Jogos\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:51:\"Suporte » Serviços » Acesso à Internet » Configurar\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:52:\"Pós-venda » Contrato » Alteração de dados cadastrais\";}" => 101,
		"a:1:{s:8:\"conteudo\";s:52:\"Suporte » Internet fora » Cliente » Serviço » E-mail\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:52:\"Suporte » Internet lenta » Cliente » Serviço » Jogos\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:52:\"Suporte » Serviços » Acesso à Internet » Alterar MAC\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:53:\"Suporte » Internet lenta » Access Point » Equipamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:53:\"Suporte » Internet lenta » Cliente » Serviço » E-mail\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:55:\"Financeiro » Cobrança » Alteração de forma de pagamento\";}" => 104,
		"a:1:{s:8:\"conteudo\";s:55:\"Suporte » Internet fora » Cliente » Serviço » Navegação\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:56:\"Desenvolvimento » Intranet » Módulo existente » Melhoria\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:56:\"Desenvolvimento » Intranet » Módulo existente » Problema\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:56:\"Suporte » Internet lenta » Cliente » Serviço » Navegação\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:57:\"Suporte » Internet fora » Cliente » Hardware » Cabeamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:58:\"Suporte » Internet fora » Cliente » Hardware » Equipamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:58:\"Suporte » Internet fora » Cliente » Pendência » Financeira\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:58:\"Suporte » Internet lenta » Cliente » Hardware » Cabeamento\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:59:\"Suporte » Internet lenta » Cliente » Serviço » Peer to Peer\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:60:\"Suporte » Internet fora » Cliente » Serviço » Voz/Video/Chat\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:61:\"Suporte » Internet lenta » Cliente » Serviço » Voz/Video/Chat\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:62:\"Suporte » Internet fora » Cliente » Pendência » Administrativa\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:67:\"Desenvolvimento » Intranet » Módulo existente » Nova funcionalidade\";}" => 102,
		"a:1:{s:8:\"conteudo\";s:7:\"Compras\";}" => 1052457,
		"a:1:{s:8:\"conteudo\";s:7:\"Suporte\";}" => 105,
		"a:1:{s:8:\"conteudo\";s:7:\"Técnico\";}" => 115,
		"a:1:{s:8:\"conteudo\";s:9:\"Comercial\";}" => 103,
		"a:1:{s:8:\"conteudo\";s:9:\"Pós-venda\";}" => 101
	);

	function util($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}

	function executar() {
				
		$query = "
			SELECT
				che_id AS id,
				che_conteudo AS conteudo
			FROM
				tab_chamados_etapas
			WHERE
				tab_chamados_etapas.cet_id = 2 AND
				tab_chamados_etapas.che_id > 4936057
			ORDER BY
				tab_chamados_etapas.che_id ASC";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
					
			if ($dados["conteudo"] && array_key_exists($dados["conteudo"], $this->categorias)) {
				$this->bancodedados->query("INSERT INTO chamados_etapas SET chc_id = {$this->categorias[$dados["conteudo"]]}, che_id = {$dados["id"]}");
			}
			
		}
		
	}
	
}

?>
