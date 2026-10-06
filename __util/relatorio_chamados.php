<?

$_GET["inicio"] = "2018-08-28 00:00:00";
$_GET["fim"] = "2018-08-31 23:59:59";

if (!($_GET["periodo"] || ($_GET["inicio"] && $_GET["fim"]))) {
	die();
}

set_time_limit(0);
ignore_user_abort(1);

require_once("../global.inc.php");

$util = new util($bancodedados);
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
			SELECT
				cha_id AS id,
				IF(cli_id IS NOT NULL AND cli_id NOT IN (97391, 79592, 61026), 'CLIENTE', IF(con_id IS NOT NULL, 'CONDOMINIO', 'INTERNO')) AS tipo,
				IF(cli_id IS NOT NULL AND cli_id NOT IN (97391, 79592, 61026), (SELECT IF(clt_id = 1, 'FISICA', 'JURIDICA') FROM tab_clientes WHERE tab_clientes.cli_id = tab_chamados.cli_id), '') AS cliente_tipo,
				(SELECT COUNT(vis_id) FROM tab_visitas WHERE tab_visitas.cha_id = tab_chamados.cha_id AND tab_visitas.via_id = 3) AS visitas,
				(SELECT COUNT(*) FROM chamados_etapas INNER JOIN tab_chamados_etapas ON (chamados_etapas.che_id = tab_chamados_etapas.che_id) WHERE tab_chamados_etapas.cha_id = tab_chamados.cha_id AND chamados_etapas.chc_id = 1052453) AS suporte_empresarial,
				IF((cli_id IS NOT NULL AND cli_id IN (108455,108381,108344,107684,107682,107681,107679,107677,106982,106977,106962,104647,104081,103964,102835,102796,102784,101387,100999,99767,99626,98871,98651,98533,97034,96706,96082,93947,93887,92491,92483,92478,92455,91247,90956,90673,88599,86113,85109,84020,83390,82749,80421,79057,77522,76070,74764,70855,67987,67015,59758,54177,9292)) OR (con_id IS NOT NULL AND con_id IN (2048,2031,2030,2028,1983,1950,1651,1562,1536,1341,1315,1292,1116,1036,984,891,557,443,376,318,306,122,106)), 1, 0) AS chamado_pop
			FROM
				tab_chamados
			WHERE
				cha_datahora_fechamento BETWEEN '{$_GET["inicio"]}' AND '{$_GET["fim"]}' 
				-- AND cha_datahora_fechamento LIKE '{$_GET["periodo"]}%'
				-- AND con_id IS NOT NULL
				-- AND cha_datahora_fechamento LIKE '2014-07%'
				-- AND cha_id NOT IN (SELECT cha_id FROM rel_cha_caa_contem WHERE caa_id IN (7))
			ORDER BY
				cha_id ASC
			-- LIMIT 500, 500";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->trataChamado($dados);
		}
		
		print "<div>Chamados com acao: {$this->chamados_com_acao} de {$this->chamados} </div>";

	}

	var $chamados = 0;
	var $chamados_com_acao = 0;
	var $chamado_id = 0;
	
	function trataChamado($dados_chamado) {
		
		$this->chamado_id = $dados_chamado["id"];
			
		///*
		$query = "DELETE FROM rel_cha_caa_contem WHERE cha_id = {$this->chamado_id}";
		$this->bancodedados->query($query);
		//*/
		
		$atendimento_suporte_interno = false;
		$atendimento_agendamento = false;
		$atendimento_financeiro = false;
		$atendimento_ouvidoria = false;
		$atendimento_comercial = false;
		$estrutural_condominio = false;
		$retirada_equipamento = false;
		$instalacao_roteador = false;
		$mudanca_ponto = false;

		$this->chamados++;
		
		$this->acoes = array();
		
		if ($dados_chamado["suporte_empresarial"]) {
			$this->registraAcao("SUPORTE_EMPRESARIAL");
		}
	
		if ($dados_chamado["chamado_pop"]) {
			$this->registraAcao("CHAMADO_POP");
		}
		
		print ("<div style=\"background-color:black;color:white;padding:3px 5px\">CHAMADO: {$dados_chamado["id"]} ({$dados_chamado["tipo"]})</div>");
	
		$query = "
			SELECT
				che_id AS id,
				che_conteudo AS conteudo
			FROM
				tab_chamados_etapas
			WHERE
				tab_chamados_etapas.cha_id = {$dados_chamado["id"]} AND
				tab_chamados_etapas.cet_id IN (5, 12, 13, 14, 19)
			ORDER BY
				tab_chamados_etapas.che_id ASC";
		
		$query = $this->bancodedados->query($query);
		
		print ("<ul style=\"background-color:#dddddd;margin:0;padding-top:5px;padding-bottom:7px\">");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["conteudo"] = unserialize($dados["conteudo"]);

			$conteudo = html_entity_decode(strip_tags(mb_strtolower($dados["conteudo"]["conteudo"])));
			
			$conteudo = preg_replace("/\\[\\/?(n|i|s|d|l|azul|verde|vermelho)\\]/", "", $conteudo);

			$conteudo = preg_replace("/[a-z0-9\\-\\.]+@[a-z0-9\\-\\.]+/", " ", $conteudo);
			
			$conteudo = preg_replace("/[ç]/", "c", $conteudo);
			$conteudo = preg_replace("/[ñ]/", "n", $conteudo);
			$conteudo = preg_replace("/[áàâãäª]/", "a", $conteudo);
			$conteudo = preg_replace("/[éèêë]/", "e", $conteudo);
			$conteudo = preg_replace("/[íìîï]/", "i", $conteudo);
			$conteudo = preg_replace("/[óòôõö°º]/", "o", $conteudo);
			$conteudo = preg_replace("/[úùûü]/", "u", $conteudo);

			$conteudo = preg_replace("/ *\b(os|as|de|do|dos|da|das|em|ao|aos|no|nos|na|nas|um|uma|uns|umas)\b */", " ", $conteudo);
			
			$conteudo = preg_replace("/[^a-z]+/", " ", $conteudo);
			
			$conteudo = preg_replace("/( *\b.\b *)+/", " ", $conteudo);
			
			$conteudo = preg_replace("/( *\bresposta bytes tempo ms ttl\b *)+/", " ", $conteudo);
			$conteudo = preg_replace("/( *\bmac fd ip uptime\b *)+/", " ", $conteudo);
			$conteudo = preg_replace("/( *\bmicrosoft windows versao copyright microsoft corporation todos os direitos reservados\b *)+/", " ", $conteudo);
			$conteudo = preg_replace("/( *\busersmiranda ping disparando com bytes dados estatisticas ping para pacotes enviados recebidos perdidos perda aproximar um numero redondo vezes milissegundos minimo ms maximo ms media ms usersmiranda\b *)+/", " ", $conteudo);
			$conteudo = preg_replace("/( *\buserssuporte interno ping disparando com bytes dados estatisticas ping para pacotes enviados recebidos perdidos perda aproximar um numero redondo vezes milissegundos minimo ms maximo ms media ms userssuporte interno\b *)+/", " ", $conteudo);
			
			$conteudo = trim($conteudo);
			
			if ($conteudo) {
				print ("<li>$conteudo</li>");
			}
			
			if (preg_match("/^agendar retirada equipamento/", $conteudo)) {
				$retirada_equipamento = true;
			}
			
			if ($retirada_equipamento) {
				if (preg_match("/(retirad[oa]s?|retirei|recolhid[oa]s?) (equipamento|radio|air|apc|pat|router|roteador|intelbras|wow|nano|com sucesso)|foi feito retirada|retirado ok|deixado router link janela compras|retirado equipamento/", $conteudo)) {
					$this->registraAcao("RETIRADA_EQUIPAMENTO");
				}
			}
			
			if (preg_match("/(agendado pagamento|fara pagamento hj|ficou encaminhar comprovante|enviar comprovante pagamento|negociei valor boleto aberto|ficou enviar comprovante|ciente fatura aberto|agendado pagamento para dia|prosseguir com cobranca|continuaremos com cobranca|solicitar bloqueio cliente deve boleto|autorizou negativacao|cliente agendou pagamento debito|cliente efetuou pagamento|deve ser cobrado multa|precisa pagar vencimento|favor excluir serasa|exclusao serasa|baixa cobranca cliente negativado|combinamos boletos para vencimento|cliente nao devolveu roteador|nao pagou parcelamento|negativar novamente|vamos negativar|parcelamento debito|combinamos boleto para pagamento|inserir serasa|ver possibilidade negativar|gerar valor comodato|ja esta lote cobranca|se( for)? possivel negativar|negativar comodato|(favor|sugiro) negativar|negativado hoje|ver possibilidade negativacao)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_COBRANCA_INADIMPLENCIA");
			}
			
			if (preg_match("/(duvida serasa esclarecida|valores explicado|acesso portal assinante|foi informada que estaria com restricao|valor explicado entendido|sanar duvidas referente boleto|retorno para verificar detalhamento fatura|informado que valor esta correto|entender detalhamento fatura ligar|duvidas? sobre( (sua|seu))? (fatura|boleto|cobranca|reajuste)|falar com financeiro sobre sua fatura|preciso nf)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_INFORMACOES");
			}
			
			if (preg_match("/(devolucao dinheiro|depositado hoje caixa eletronico|fazer devolucao valor|desistiu instalacao pagou instalacao|nao ha viabilidade favor devolver quantia)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_DEVOLUCAO_VALORES");
			}
			
			if (preg_match("/(alterar data vencimento|alterar vencimento para dia|alterada data vencimento|alteracao( data)? vencimento|vencimento alterado|alterado vencimento)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_ALTERACAO_VENCIMENTO");
			}
			
			if (preg_match("/(gerado nf|notas clientes|envia copia nota|cliente pede contato sobre nf instalacao)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_ENVIO_NOTA_FISCAL");
			}
			
			if (preg_match("/(menos para proximo (vencimento|faturamento)|boleto valor esta cancelado|tirar juro para pagamento|verificar cobrancas contrato|solicitado envio nova fatura com desconto|gerado notas boletos|favor cancelar boleto|aguardando o indicado pagar para fazer o ajuste|cliente pagou boleto cancelado|financeiro baixar valor comodato|solicitou manter servico por favor verificar fatura|faturas baixadas|pagou sem desconto fazer ajsuste|favor aplicar desconto|pediu para ajustar boleto|favor inserir vencimento|efetuou pagamento boleto removido|gerar proximos boletos|abonarmos multa|solicita desconto|favor abonar fatura|faturas corrigidas|concedemos manter bonificacao|gerada multa parcelas|aprovada cortesia|sem desconto favor fazer ajuste|pagar boletos separados|ajuste bonificacao|desmembrar valores vencimentos|feito ajuste conforme|pagou boleto sem desconto|indique amigo|nao sera dado desconto|cliente pagou sem desconto ajuste|favor baixar faturas aberto|favor incluir proximo boleto|favor incluir desconto|favor ajustar faturas|abonar multa rescisoria|boletos sem desconto favor conceder|cancelado ajuste multa|pagou sem desconto fazer ajuste|ajuste multa cancelado|baixado valor cobranca multa|julia favor fazer ajuste|dias desconto problemas|feito ajuste fatura|dar( dias)? desconto fatura|favor gerar cobranca|faturas? ajustadas?|ajuste cobranca|gerados?( todos?)? boletos|boletos? gerados?|verificar cobrancas?|(fiz|fei.o)( ajuste)? (dias?|mes(es)?) desconto|favor fazer (re)?ajuste fatura|recalculada multa proporcional|favor parcelar multa|feito ajuste desconto|conceder desconto)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_AJUSTE_FATURA");
			}
			
			if (preg_match("/(solicita via faturas|via fatura atualizada|agendamos pagamento debito|combinamos boleto para|favor envia via fatura atualizada|pede que encaminhe seu boleto|enviaremos forma fisica tambem|solicita via fatura por mail atualizada|precisa boleto com data atualizada|pedindo boleto para hoje|segunda via boleto|mandar fatura|nao estava recebendo boletos|solicita retorno fatura|nao chegou sua fatura|pediu boleto para hoje|solicita codigo barras|solicita via para pagamento|solicitando via fatura|boleto para pagamento hoje|nao tinha recebido boleto|precisa segunda via|deseja segunda via|queria via boleto|boleto atualizado|boleto( ja)?( foi)? enviado|boletos? pelo correio|boletos? por mail|(re)?enviar (notas?|boletos?)|boletos enviados|cliente pediu boleto|cliente (solicit(a|ou)( que)?|quer)( (envio|enviassem?))?( (seu|sua))? (boletos?|segunda via|via boleto|f.tura)|deseja( todos)?( receber)? (boletos?|faturas?)|(enviar|enviasse|enviei|enviada|encaminhar|enviarem|emiti)( novamente)? (boletos?|faturas?|cobrancas?|segunada via|segunda via)|gerei (boleto|nova via)|novo boleto|gerar (boletos?|faturas?)|(re)?envio( (seu|sua))? (boleto|fatura)|enviad[oa] (boleto|fatura)|encaminhar( para email)? (fatura|boleto)|nao (recebeu|ter recebido)( (seu|sua))? (fatura|boleto)|boleto enviado)/", $conteudo)) {
				$this->registraAcao("FINANCEIRO_SEGUNDA_VIA_BOLETO");
			}

			if (preg_match("/(favor bloquear)/", $conteudo)) {
				$this->registraAcao("BLOQUEIO_CLIENTE");
			}

			if (preg_match("/solicita suspensao bloqueio|solicitando liberacao internet|ja pagou boleto pediu desbloqueio|liberei sinal|aplicar suspensao temporaria|estava bloqueado .*liberou|sinal liberado|liberar sinal|dada suspensao|favor dar suspensao temporaria|ja liberei sinal|solicitou suspensao temporaria|favor liberar sinal|aplique suspensao temporaria|(cliente desbloqueado|desbloqueio sinal|sinal foi liberado|favor desbloquear)/", $conteudo)) {
				$this->registraAcao("DESBLOQUEIO_CLIENTE");
			}

			if (preg_match("/(cancelar contrato via radio|favor cancelar este cliente|cancelar plano radio com data|favor cancelar com data|favor cancelar via radio|favor cancelar contrato|cancelamento efetuado|quando instalar fibra cancelar radio|cancelar data apos|assim que instalar cancelar|cancelar este sem multa|radio cancelada com data|formalizado cancelamento|cancelar plano antigo radio|cancelar radio com data|favor cancelar evento|contrato cancelado|assim que instalar fibra cancelar radio|monitorar plano verao|efetuar cancelamento plano|favor cancelar plano|cancelamento efetuado com sucesso|cancelar com data falecimento|agendado cancelamento|cancelado conforme|conforme solicitado contrato foi cancelado)/", $conteudo)) {
				$this->registraAcao("CANCELAMENTO");
			}

			if (preg_match("/(favor ativar dois contrato|favor tivar com data|favor ativar om data|favor ativar eventos?|ativo conforme informacao|ativar( contrato)?( cliente)? com data)/", $conteudo)) {
				$this->registraAcao("ATIVACAO");
			}

			if (preg_match("/(queria liberacao sinal feito|feito suspensao temporaria|sinal ficara liberado por|feita suspensao bloqueio|apl suspensao temporaria|aplicada suspensao|acao suspensao temporaria|concebido suspensao temporaria|(apliquei|aplicado) sus?p)/", $conteudo)) {
				$this->registraAcao("SUSPENSAO_TEMPORARIA_BLOQUEIO");
			}

			if (preg_match("/(instalar voip outro computador|utilizar aplicativo zoiper|problemas recorrentes suas duas linhas telefonicas)/", $conteudo)) {
				$this->registraAcao("SUPORTE_TECNICO_TELEFONIA");
			}
					
			if (preg_match("/((alteracao|troca|mudanca) ponto)/", $conteudo)) {
				$mudanca_ponto = true;
			}
			
			if (preg_match("/((trocado|instalado) (router|roteador))/", $conteudo)) {
				$instalacao_roteador = true;
			}
			
			if (preg_match("/(fazer viabilidade para radio|verificar disponibilidade para atender evento|sem visada|(verificar|realizar)( se tem)? viabilidade)/", $conteudo) && !preg_match("/fibra/", $conteudo)) {
				$this->registraAcao("VERIFICACAO_VIABILIDADE");
			}
			
			if (preg_match("/(metros fibra aproximadamente|verificar disponibilidade coordenadas|segue coordenadas|verificar seguintes coordenados|coordenadas paa fibra|coordenadas geograficas servico ser contratado internet fibra|verificar viabilidade instalacao para fibra|aproximadamente metros fibra|fibra coordenadas|verificar quantidade fibra|informou que quantidade fibra sera|viabiliade para fibra|fazer viabilidade para coordenadas|verificar viabilidade instalacao fibra|viabilidade atendimento via fibra|(verificar|realizar) (disponibilidade|viabilidade) para( (atendimento|instalacao))? fibra|coordenadas para fibra|(disponibilidade|viabilidade) para fibra)/", $conteudo)) {
				$this->registraAcao("VERIFICACAO_VIABILIDADE_FIBRA");
			}
			
			if (preg_match("/(solicitou termino suspensao temporaria|efetuar bloqueio temporario|bloqueio agendado|solicita bloqueio temporario|solicitou bloqueio temporario)/", $conteudo)) {
				$this->registraAcao("BLOQUEIO_A_PEDIDO");
			}
			
			if (preg_match("/(re pesquisao satisfacao|pesquisa pos instalacao)/", $conteudo)) {
				$this->registraAcao("PESQUISA_POS_INSTALACAO");
			}
			
			if (preg_match("/(configurando equipamentos para novo pop)/", $conteudo)) {
				$this->registraAcao("POP_INFRAESTRUTURA");
			}
			
			if (preg_match("/(adicionado nagios mapa|alterar velocidade condominio|realizamos ajustes enlace|temos radio neste condominio|adm redes esta verificando|alterado pool cliente pppoe para liberados|adm rede esta verificando|alterado pool pppoe para liberados|aumentar velocidade condominio|servidor esta aguardando publicacao|reiniciado olt|atualizamos nagios mapa|aumentar velocidade predio|mudamos pool cliente pppoe para liberados|liberar pool port|cliente pediu pra alterar rota|adicionado cliente cacti|inserir cliente cacti|alterado porta|liberacao portas|liberar porta|queue simple add|ativar blocos|organizar cliente mapa|liberado para este condominio velocidade|ponto instalado sem padroes minimos qualidade|ramires ja limitou mb voip|corrigir mapa|retirar este cliente|adicionar mapa|atualizamos novamente nos mapas|(retirar|colocar|removido|alterar|atualizado|atualizar|inserir) (mapa|nagios))/", $conteudo)) {
				$this->registraAcao("ADMINISTRACAO_DE_REDE");
			}
			
			if (preg_match("/(router instalado configurado)/", $conteudo)) {
				$this->registraAcao("ROTEADOR_INSTALACAO");
			}
						
			if (preg_match("/(controle banda estava configurado errado feito ajuste|troca nome rede senha feito|alteracao senha feito|auxilio para configurar router|senha nome wifi alterado|senha rede foi feita alteracao|roteador estava resetado configurado|nome senha rede definidos|senha nome rede fizemos essa alteracao|feita alteracao senha|alterar senha rede alterado|alterar nome senha sua rede wifi|alteracao senha alterada|nova rb para configurar|roteador desconfigurado reconfiguramos|alteracao senha wifi ficou ok|configurei router|wirellless senha foi feita alteracao|router foi reconfigurado|entregue rb para adm|entregue rb patrimonio para adm|resetou router reconfiguramos|senha nome router foi feita|roteador tp link configurado|pediu que reiniciassemos router procedimento foi feito|alterar senha rede alterada|router aproveitei mudei canal|acessei router apliquei dns|router desconfigurou configuramos|senha alterada conforme solicitado|acessei router coloquei dns|roteador havia resetado sozinho reconfiguramos|alterar nome senha rede wi fi|trocar senha seu wi fi alterado|roteador nao conecta estava desconfigurado reconfiguramos|nome rede wi fi alterada|alteracao senha alterado com sucesso wifi|tirei canal automatico|router novo configurado|configurar pppoe configurado|alterar senha rede senha alterada|configurando roteador novo|configuracao wi fi|trocar nome rede wi fi feito|router resetado reconfiguramos|dns errado router foi corrigido|senha rede wifi alterada|router pois haviam resetado configuracoes feita|troca nome rede senha foi feito|roteador link one configurado|alterar senha rede wi fi alterada|roteador havia resetado configurado|foi mudado canal|roteador reconfiguramos|acessamos roteador habilitamos|router desconfigurado reconfiguramos|portas configuradas router|alterada senha (wi ?fi|roteador)|fiz alteracao canal|configurar seu router novo link configuramos|solicitou alteracao nome rede|resetou router configuramos|alterado senha nome seu wifi|wi ?fi senha alterada|router .*(adicionei|alterei) dns|(trocado|alterado) senha (roteador|router|wireless|wi ?fi)|roteador resetado refeita configuracoes|alterar nome rede senha wireless alterado|alterar senha sua rede alterada|trocado senha com cliente wi fi|configurar router totolink configurado|router desconfigurado configuramos|alterei nome rede|feito alteracoes roteador|trocado dns|alterar sua senha rede|repetidor sinal configuramos|nome rede alterada|roteador resetado reconfiguramos|roteador feito alteracoes|configurar roteador|(router|roteador) estava desconfigurado|alterei senha remotamente|(trocar?|alterar)( se)? senha (wi ?fi|wireless|router|roteador)|(adquiriu|comprou) novo (router|roteador)|queria trocar senha|cabo direto ficou ok|(feito|realizei) cabo direto|senha wifi alterado|mudar nome rede|roteador resetado refeita configuracao|(alterado|alteramos|setado|colocado|coloquei|adicionado) dns|(configuramos|configurado|reconfigurado|configuracao|alteramos para|alterado parar?) pppoe|(re)?configura(do|mos|cao) (router|roteador)|(router|roteador|wireless) (re)?configurado|acessado (router|roteador|wireless) para trocar senha|(router|roteador|wireless)( desconfigurado)? foi configurado|reiniciado (router|roteador|wireless) sem sucesso reconfigurado|resetado (router|roteador|wireless)|(troquei|trocado|alterei|alterado|alteremos|alteramos|coloquei) canal)/", $conteudo)) {
				$this->registraAcao("ROTEADOR_CONFIGURACAO");
			}
				
			if (preg_match("/(rb ja configurada|rb para amd configurar|nome senha rede alterada|roteador estava resetado reconfiguramos|mudei canal|router mudei canal|coloquei controle banda|rede wireless alterado|feito alteracao canal roteador|alterado senha ficou ok|apos alteracoes router|feita alteracao nome senha router|coloquei controle banda|resetou roteador configuramos|alteracao senha wifi alterada|troca sua senha wifi realizada|mudei canal router|repetidor foi feita configuracao|novo router feita configuracao|rb com patrimonio configurada|alterada nome rede|senha rede wi fi alterada|senha wi fi assim mudamos|feito alteracoes (canal|dns)|solicitou troca senha sua rede wifi|criar queue rb|separar rb mandar para adm configurar|fiz alteracao senha rb|separar rb entregar adms para configurar|rb configurada)/", $conteudo)) {
				$this->registraAcao("ROTEADOR_CONFIGURACAO");
			}

			if (preg_match("/(trocado antena|(fiz|foi necessario) troca antena)/", $conteudo)) {
				$this->registraAcao("ANTENA_SUBSTITUICAO");
			}
			
			if (preg_match("/(realizamos ajustes ap radio|realizamos ajustes radio|(mudamos|trocado|troquei|migrei|migramos|migrado).? cliente ap)/", $conteudo)) {
				$this->registraAcao("RADIO_CONFIGURACAO");
			}
			
			if (preg_match("/(configurar novamente seu email|configuramos outlook|configurar seu outlook configurado|configurar mail outlook|configurado outlook)/", $conteudo)) {
				$this->registraAcao("EMAIL_CONFIGURACAO");
			}
		
			if (preg_match("/(feita fusao caixa)/", $conteudo)) {
				$this->registraAcao("FIBRA_FUSAO");
			}
		
			if (preg_match("/(instalado onu)/", $conteudo)) {
				$this->registraAcao("FIBRA_ONU_INSTALACAO");
			}
						
			if (preg_match("/(trocada onu|trocado onu)/", $conteudo)) {
				$this->registraAcao("FIBRA_ONU_SUBSTITUICAO");
			}
						
			if (preg_match("/(in ns ns csmtelecom com br|in ns ns oletelecom com br|servidor dns ns oletelecom com br|servidor dns ns csmtelecom com br|alterar dns para ns oletelecom com br)/", $conteudo)) {
				$this->registraAcao("SUPORTE_CORPORATIVO");
			}

			if ($dados_chamado["tipo"] == "INTERNO") {
				if (preg_match("/(computador( [a-z]+)? esta com problema|montarem maquinas suporte)/", $conteudo)) {
					$this->registraAcao("MANUTENCAO_COMPUTADOR");
				}
				if (preg_match("/(trocar ip radios)/", $conteudo)) {
					$this->registraAcao("ADMINISTRACAO_DE_REDE");
				}
			}
					
			if (preg_match("/(internet cai|queria mudar senha wifi|senha antiga redefinimos para nova|solicitando usuario senha|fica lenta toda hora|sua internet voltou|nao consegue assistir netflix|internet parou funcionar|internet funcionando ok|reclamando conexao|informou que seu roteador nao liga|megas esta muito abaixo|solicitando auxilio para conectar|reiniciado roteador|trocado equipamentos condominio favor testar|ligou sem acesso|informa que ja foi resolvido|sinal internet estava lento|informa que sinal esta normal|com problema sinal|com conexao ruim|cliente esta saturando seu link|estamos com problema regiao|reclamando velocidade|alega que esta perdendo pacotes|desejava saber sua senha|conexao esta oscilando|ta oscilando muito|alega que seu ping|informacao sobre sua conexao|queria saber senha wifi|cliente nao lembra mais senha seu email|cliente conectado navegando normalmente|com internet lenta|fica com acesso limitado|contato sobre sua velocidade|alega que vez quando tem quedas|senha estava errada|informa que partir seu sinal fica lento|com problema para coenctar|internet esta ruim|esta ok cliente vai monitorar|cliente informa que sua conexao|cliente pede retorno daqui minutos verificado que sua antena|solicita desviar mail|cliente entrou contato verifiquei que temos clientes predio|estamos sem sinal|contato com cliente explicado manutencao|tirar duvidas mail|conexao estava pessima|informa que sua conexao esta muito abaixo|esqueceu senha acesso mail|alega que nao consegue enviar emails|reiniciamos roteador voltou funcionar|ficando sem sinal|com problemas ping|ligou sem sinal|problema sinal sua internet|ligou solicitando senha wifi|nao conecta via cabo|auxilio para verificar lista dispositivos conectado|informar que esta novamente sem sinal|cliente internet ok|informa que sua conexao esta ruim|conexao esta funcionando normalmente|senha acesso seu roteador|alega falta conexcao|esta sem acesso|esqueceu sua senha mail|fez teste banda esta ok|contato reclamando conexao|feito testes com cabo direto|internet fica lent|testes velocidade via wi fi|lentidao alguns horarios|reclamou lentidao|ligou para pedir senha|problemas para navegar|extrema lentidao|com oscilacao sinal|informei manutencao|informou que esta ok|internet esta pessima|conexao esta caindo|esta com muita lentidao|cliente questiona latencia|fazer conexao pppoe|nao navega|internet esta oscilando|precisava acesso roteador|cliente nagos nao precisa retorno|internet ficou instavel|ligar fazer testes conexao|oscilacao rede|internet esta funcionando|reclama latencia alta|havendo instabilidade|manutencao eletrica condominio cliente ciente|reclamar sinal internet|stendo lentidao|olicitando usuario senha acesso router|vem tendo problema oscilacao|fez teste cabo direto|co lentidao sinal|sinal ficou bastante ruim|ligou sema cesso|com muita oscilacao|nao acessa nenhuma pagina|gostaria verificar senha wifi|contato dizendo estar com quedas|informando estar sem acesso|esta tendo oscilacao sinal|internet esta caindo|nagios nao pede retorno|usuario pppoe informado|internet estava caindo muito|reclama que nao esta recebendo mb|tenho notado que quedas sinal|informou nao esta conseguindo conectar|criar conexao via cabo direto|informando que esta com conexao ruim|internet continua horrivel|estava sem sinal|internet anda lenta instavel|internet vem oscilando|nao esta chegando mbps|contato assim que problema regiao for resolvido|nao estar conseguindo receber mail|informa que problema ponto|queria saber sua senha email|instabilidade sua internet|relatando quedas|fica toda hora caindo|informando mesmo internet|informa que internet travou|informado problema regiao|alegou quedas lentidao|internet caindo|informad[ao] sobre problema nosso ponto transmissao|informado que conexao normalizou|solicita retorno assim que for normalizado|nagios vai aguardar|vai aguardar problema ser resolvido|informando perda pacote|contratado tem oscilaccoes|solicitando troca senha esta offline|informa que sua internet esta fraca|tinha esquecido senha wifi|cliente bloqueou sua senha para mail|reclamando ma qualidade internet|informando estar com quedas|fiquei manha toda sem ole|gostaria saber qual senha|contato informando que nao consegue entrar email|contato com problemas ping|contato informando que teste ping|nao conseguia navegar|desativou seu wifi sem querer|reclamou sobre demora receber mails|informou que problema ja foi resolvido|consumindo toda banda|conexao ja esta normalizada|informa que conseguiu acessar normalmente|internet funcionando caso precise|informou que seu problema é de quedas|informei problema regiao|cliente ciente nao solicita retorno|informa que sua internet esta pessima|esqueceu senha foi informado|cliente alegou lentidao|informou sobre lentidao|esta com ping alto|alegou que sua conexao teve quedas|agora internet voltou|suporte devido lentidao|nova senha mail|estava sem sinal cabo roteador|voltou sinal internet|online nao navega|fizemos cabo direto|cai toda hora|fizemos teste velocidade|informou que sempre tem lentidao|sinal internet esta lenta|entrei contato ja esta funcionando|ajuda para realizar teste velocidade|relata instabilidade sinal internet|nao conseguia conectar|cliente com banda bem abaixo|informei sobre problema regiao|sinal esta muito ruim|instabilidade sinal internet|consumindo toda sua cote mail|relatando lentidao|oscilando muito velocidade|internet nao esta funcionando|padiu pra alterar senha|com problema banda|ponto esta oscilando|internet nao esta funcionando|ponto transmissao estava fora|conexao voltou normal|reclamando estabilidade|fazer cabo direto|consumindo toda banda upload|acesso remoto|conexao via wifi cai|sua conexao se normalizou|com oscilacao quedas lentidao|aguarda solucao problema|utilizar cabo direto|consta nagios cliente ciente|conexao se normalizou|minha conexao cai|reiniciamos seu roteador|cliente usando todo upload|cliente com quedas|nao consegue navegar|internet cai direto|reclamando internet|fazer cabo direto internet|cliente ciente sobre problema|contato solicitando senha|internet esta pior|sinal internet esta oscilando|problema condominio cliente ja tem chamado aberto|problemas email|online mas nao conecta|alegou instabilidade sua rede|reclamando oscilacoes|cliente ciente problema|cliente com router desconfigurado|informar cliente que estamos fazendo manutencao|foi informado manutencao|problemas oscilacao|quedas constantes|nao esta conseguindo acessar|criada nova senha com cliente|problemas tecnicos ponto transmissao|ja esta funcionando normalmente|oscilacao sinal internet|informando que conexao|conexao estava caindo|conexao vive caindo|pede retorno sem sinal|nao dava sua banda|muito lenta|informando problema rede|cliente offline|nao estava conseguindo acessar sua rede|nao estavam conseguindo conectar rede|realizamos testes speedtest|esta com quedas|conexao ja esta funcionando|reclama que wi fi vive caindo|reclamando quantidade velocidade|nao conseguia acessar|alega oscilacao|acessamos seu roteador configuramos|online porem nao navega|esqueceu senha wifi|motivo falta sinal|pedido tecnico entrei contato|informando que seus mail estao demorando|internet esta ok|informa que ja esta ok|nao conseguia acessar paginas|internet tem estado instavel|voltou funcionar normalmente|gostaria configurar aparelho|consultar sua senha|problema conexao|estava consumindo upload|esta reclamando quedas|contato consta nagios|entrou contato esta com quedas|solicitei para verificar cabos|velocidade contratada menor que velocidade|problema nao envio recebimento via webmail|internet ficou muito lenta|nao esta conseguindo enviar receber emails|problemas referente email|bloqueou senha seu email|ligou com problema velocidade|entrou contato sem acesso internet|verificarmos configuracao email|alegando que so cai|problema acesso internet|solicita resolucao problema conexao|cliente com problema email|contato informando problemas link|apenas precisava usuario senha|informado que esta com problemas condominio|nao lembrar senha wifi|informa nao estar conseguindo navegar|pedido tecnico tentei contato|tentei contato pedido tecnico|informa que seu router trava|informa nao estar conseguindo acessar|estava com problema rede|mesmo informa que esta ok|conexao esta tudo ok|verificar problema conexao|solicita fazer cabo direto|colocou cabo principal seu pc|realizasse cabo direto|cliente informou que sinal voltou|online sem navegar|nao estava conseguindo navegar|internet esta muito ruim|online porem nao navegava|informa que internet esta ok|cliente alega quedas|internet com velocidade muito baixa|cliente online reclamou lentidao|cliente aparece nagios|queria boleto com bonificacao|estao com link fora|cliente informa estar com ping alto|problemas sinal|problemas estabilidade|problemas internet|contato informando lentidao|sinal esta pessimo|sem conexa|cliente sem sinal|reiniciamos router|com lentidao|problemas com internet|problemas com uso internet|problemas muita falta sinal|solicitando senha rede wifi|estar com oscilacao|suporte voce recebeu mensagem|nao consegue conectar|problema seu mai|internet funcionando pelo cabo|oscilacao sua rede|problema outlook|oscilacao internet|dificuldades para navega|usuario senha pppoe|conectada mas nao navega|conexao esta muito lenta|informa lentidao|duvidas referente conexao|feito internet ja esta normal|falta conexao|reclama quedas|ponto transmissao volta|internet muito lenta|retorno quando internet for normalizada|(informando|reclama) lentidao|cabos estavam mal conectados|reclamando lentidao|informar falta conexao|problema esta ponto transmissao|ponto transmissao voltar|problemas lentidao|entrou contato com quedas|problema ponto transmissao|ligou reclamando que esta com problema|estava com cabo mal conectado|cliente reiniciou router|sinal esta oscilando|nagios nao solicita retorno|ligou com lentidao|esta sem sinal|informa estar com (quedas|lentidao)|problemas conexao|online mas nao navega|informar senha( seu)? roteador|alega falta conexao|problema lentidao|cliente( ligou)? sem acesso|cliente alega lentidao|esta ciente problema|^nagios$|quedas conexao|nao consegue acessar|cliente quer alterar senha|nao navega ciente problema|cliente ciente manutencao|conexao( muito)? lenta|ja voltou|conexao esta normal|entrou contato para saber|reiniciado router|(queria|solicitou)( usuario)? senha|conexao lenta|gostaria saber sua senha|consumo banda|cliente com instabilidade|sem( sinal)? internet|alega estar (sem conexao|com quedas)|com conexao lenta|((esta|estava) )?(com problema|se[nm]) (cone*xao|internet)|cliente( entrou contato)?( esta)?( com)?( reclama)? lentidao|esta(va)? com lentidao|cliente( esta)? nagios|mensagem deixada central telefonica para setor suporte)/", $conteudo)) {
				$atendimento_suporte_interno = true;
			}

			if (preg_match("/(prazo para concluir instalacao|tentando reagendar visita|cliente quer reagendar|mensagem deixada central telefonica para setor agendamento)/", $conteudo)) {
				$atendimento_agendamento = true;
			}
				
			if (preg_match("/(enviar nova ata|alterado para recebimento online|pedem contato financeiro|pagou hj|confirmado pagamento boleto vencimento|cliente bloqueado mas informa ter pago|informada primeiro boleto|repassada setor financeiro|alterar endereco cobranca|instlacao sera isenta favor liberar|desconto referente faltas conexao|repassadas pelo setor financeiro|solicitando reembolso|deseja falar com financeiro|orientado gerar novas vias|contado com financeiro|precisa falar com financeiro|desconto sindico finalizou|contato referente valores|contato com financeiro|retorno( setor)? financeiro|esclarecido sobre desconto faturas|solicita contato financeiro|questionando valor cobrado fatura|mail aumento internet|pede contato financeiro|querendo falar com setor financeiro|duvidas fatura|duvida sobre pagamento|vai encaminhar comprovante|pede contato setor financeiro|explicado valor boleto|mensagem deixada central telefonica para setor financeiro)/", $conteudo)) {
				$atendimento_financeiro = true;
			}
				
			if (preg_match("/(solicita migracao para valor promocional|deseja aumentar sua velocidade|efetuar migracao plano|vai efetuar cancelamento|casa foi avariada depois da instalação|deseja aderir oferta|confirmado migracao plano|deseja efetuar cancelamento|ofertado desconto|solicitou informacoes plano|solicitou saber valor plano contratado apos periodo desconto|contato para aumentar plano|desistiu cancelamento|plano descancelado|solicitou informacao sobre fidelidade|cliente vai migrar para|solicitou saber se poderia se mantido desconto|fazer mudanca plano|retido com proposta|quer migrar para|retencao cliente|cliente solicitou migracao|gerada migracao plano|informei manutencao|solicita retorno para ter desconto|alterou plano para velocidade|cancelado id conforme|gostaria migrar para plano|esta pensando cancelar|ofertado para cliente migar|gerado migracao plano|solicitando data inicio seu contrato|ofertado cliente manter desconto anterior|cliente solicita descancelamento|solicita informacoes sobre cancelamento|gostaria registrar reclamacao|informa que desejam cancelamento|cliente quer aumentar plano|contato para migrar plano|adeseja passar para mbps|contato desejando cancelamento|migrou para velocidade|cliente deseja migrar|pede contato pos venda|deseja migrar plano|cliente retido|abertura reclamacao|deseja fazer alteracao endereco|solicitando alteracao plano|solicita desligamento servico|cliente pede desconto|cliente deseja cancelar|cliente mesmo deseja aderir plano|realizada migracao conforme solicitado|solicitou alteracao plano|contrato descancelado|notifica los cancelamento|questionou valor cobranca multa|ele queria migracao|conversou sobre cancelamento|deseja aumentar plano|retorno sobre migracao|contato sobre cancelamento|entrou solicitando cancelamento|informacoes sobre suspensao temporaria|solicitou migracao para plano|informa que ira procurar outra operadora|contato para cancelamento|pensando cancelar servico|quando der problema madrugada|cliente deseja migrar plano|deseja colocar roteador comodato|gostaria verificar negociacao mensalidade|negociacao expirou deseja negociar|solicitou saber vigencia|solicitou desligamento servico|verificar ponto cortesia|solicitacao cancelamento|deseja alterar seu plano|quer manter desconto|informou que iria cancela|retida com desconto|entender migracao plano|migrar seu plano|retencao plano coletivo|saber qual periodo fidelidade|informacoes cancelamento|procedimento cancelamento|mudando para residencia|solicita desligamento contrato|feito migracao plano|solicitou manter servico ativo|cadastro descancelado|cliente solicita roteador|informando que nao quer efetuar cancelamento|solicitou contato pos venda|renovado desconto por|solicita retorno migrar|mudou para condominio qual nao atendemos|solicitar cancelamento|mudanca titularidade|ouvidoria|solicita migracao plano|solicitando desligamento servico|solicitou cancelamento|solicita migracao para plano|solicita cancelamento|aceitou oferta para migracao|solicitou migracao plano|cliente periodo fidelidade ciente cobranca multa|cliente deseja verificar plano|assunto migracao plano|mantida cobranca multa|deseja retornar plano antigo|avaliado sobre cobranca multa|contato solicitando cancelamento|feita migracao para novos planos|deseja aumentar seu plano|providenciar alteracao endereco|solicitou informacoes sobre cancelamento|plano coletivo retencao|cliente solicita cancelamento|aguarda retorno ouvidoria|deseja cancelar|duvidas sobre contrato|mudanca endereco|mudar titularidade|contato com setor ouvidoria|mudanca titularidade|cliente deseja fazer cancelamento|contato respeito cancelamento|cliente quer cancelar|pede retorno setor pos venda|saber procedimento para cancelar|contato ouvidoria|cliente quer cancelamento|quer trocar titular|mensagem deixada central telefonica para setor ouvidoria)/", $conteudo)) {
				$atendimento_ouvidoria = true;
			}
				
			if (preg_match("/(interessado contratar|informacoes plano ofertados|recebeu folder promocao deseja aderir|solicita contato atendente para contratar servico|comprar novo router|deseja mais informacoes sobre fibra|duvidas sobre seu plano|solicita contato para contratar|prospect solicita alteracao plano|informacoes sobre valores planos|nao pagou instalacao favor excluir cadastro|pagamentos ok aguardando instalacao|pede contato scheila|informado valores informacoes|gostaria saber quando sera instalacao|pediu par saber sobre tv|pegar contrato documentos|buscar assinatura contrato|buscar contrato assinado|comercial negociou com|quer aumentar velocidade|feito contato informado valores|ofertado aumento velocidade|contato com setor comercial|deseja informacoes para adquirir|gostaria saber se fibra otica esta disponivel|solicitacao para aumentar sua banda|deseja retorno comercial|dar sequencia instalacao deste cliente|verificar possibilidade aumentar banda upload|repassado setor comercial|deseja saber sobre planos|mudando plano condominio para residencial|ja conversou com comercial|cliente quer hospedar site|gostaria adquirir roteador|deseja contratar|ofertado pelo setor comercial|conversar com setor comercial|solicitou adquirir roteador|mudando deste endereco|fazer cancelamento contrato|quero migrar|desconto plano coletivo|passar para setor comercial|quer informacoes sobre outros planos|contato deseja contratar|contato para fazer nova contratacao|cliente contratou|gostaria comprar roteador|contato com comercial|retorno para informacoes fibra|cliente pede contato sobre fibra|nao pagou instalacao favor cancelar contrato|deseja adquirir roteador|cliente deseja adquirir plano|retorno setor comercial|quer aumentar sua velocidade|contato questionando sobre oferta plano|nao atendemos endereco|contato tem interesse contratar|deseja adquirir router|cliente solicita roteador comodato|passsado valores informacoes|interesse saber sobre servico cameras|gostaria saber como funciona internet ole|solicita contato comercial|passei valores para|se possivel migrar|deseja aderir promocao|deseja aumentar sua banda|quer mudar plano|pede contato comercial|contato sobre aumento velocidade|pede contato comercial|deseja contratar servico|pede retorno comercial|deseja contato setor comercial|deseja contratar internet|qual custo faixa|deseja saber planos|duvidas ja foram retiradas com atendente comercial|quer (re)?contratar|gostaria saber planos|informacoes sobre plano|sanar duvidas referentes plano|gostaria aumentar velocidade|verificar disponibilidade internet|disponibilidade adquirir roteador|querendo comprar router|mensagem deixada central telefonica para setor comercial|mensagem deixada central telefonica para setor comercial|possivel cliente)/", $conteudo)) {
				$atendimento_comercial = true;
			}
		
			if ($dados_chamado["tipo"] == "INTERNO") {			
				if (preg_match("/(atualizamos novamente mapas|perca pacotes)/", $conteudo)) {
					$this->registraAcao("ADMINISTRACAO_DE_REDE");
				}				
				if (preg_match("/(telefone regiao)/", $conteudo)) {
					$this->registraAcao("COMERCIAL_ATENDIMENTO");
				}				
				if (preg_match("/(critical host unreachable|celesc)/", $conteudo)) {
					$this->registraAcao("ADMINISTRACAO_DE_REDE_MONITORAMENTO");
				}
			}
		
			if (preg_match("/(onu monitoramento)/", $conteudo)) {
				$this->registraAcao("ADMINISTRACAO_DE_REDE_MONITORAMENTO");
			}	

			if ($dados_chamado["tipo"] == "CLIENTE") {
				
				if (preg_match("/(trocado radio)/", $conteudo)) {
					$this->registraAcao("CLIENTE_TROCA_RADIO");
				}				
				
			} elseif ($dados_chamado["tipo"] == "CONDOMINIO") {
				
				if (preg_match("/(resolvido.*configurado vlan pppoe swtich intelbras)/", $conteudo)) {
					$this->registraAcao("CONDOMINIO_MANUTENCAO");
				}				
				if (preg_match("/(trocado radio)/", $conteudo)) {
					$this->registraAcao("CONDOMINIO_TROCA_RADIO");
				}				
				if (preg_match("/(passar cabo fibra otica|ativo fibra|feita passagem fibra|passar fibra ate caixa)/", $conteudo)) {
					$estrutural_condominio = true;
					$this->registraAcao("CONDOMINIO_ESTRUTURAL_FIBRA");
				}				
				if (preg_match("/(favor fazer trabalho condominio|autorizou fixar qualquer informe|pedir autorizacao para fixacao informativo|fazer panfletagem|autoriz(ou|a)( divulgacao)?( para)? panfletagem)/", $conteudo)) {
					$this->registraAcao("CONDOMINIO_PANFLETAGEM");
				}
				if (preg_match("/(nao responde ping|voltou predio continua fora|problemas eletrica|cacti|packet loss|host unreachable|host status down)/", $conteudo)) {
					$this->registraAcao("ADMINISTRACAO_DE_REDE_MONITORAMENTO");
				}	
			
			}

		}
					
		if (in_array("AJUSTE_FATURA", $this->acoes) && in_array("SEGUNDA_VIA_BOLETO", $this->acoes)) {
			$this->acoes = array_diff($this->acoes, array("SEGUNDA_VIA_BOLETO"));
		} 
		if (in_array("COBRANCA_INADIMPLENCIA", $this->acoes) && in_array("SEGUNDA_VIA_BOLETO", $this->acoes)) {
			$this->acoes = array_diff($this->acoes, array("SEGUNDA_VIA_BOLETO"));
		} 
		if (in_array("COBRANCA_INADIMPLENCIA", $this->acoes) && in_array("AJUSTE_FATURA", $this->acoes)) {
			$this->acoes = array_diff($this->acoes, array("AJUSTE_FATURA"));
		} 
		if (array_key_exists($dados_chamado["id"], $this->chamados_pop)) {
			$this->registraAcao($this->chamados_pop[$dados_chamado["id"]]);
		} elseif (!$retirada_equipamento && !$estrutural_condominio && $dados_chamado["visitas"]) {
			if ($dados_chamado["tipo"] == "CONDOMINIO") {
				$this->registraAcao("VISITA_TECNICA_CONDOMINIO");
			} elseif ($dados_chamado["tipo"] == "INTERNO") {
				$this->registraAcao("VISITA_TECNICA_ESTRUTURA");
			} elseif ($dados_chamado["cliente_tipo"] == "FISICA") {
				if ($mudanca_ponto) {
					$this->registraAcao("MUDANCA_DE_PONTO");
				}
				if ($instalacao_roteador) {
					$this->registraAcao("ROTEADOR_INSTALACAO");
				}
				$this->registraAcao("VISITA_TECNICA_PESSOA_FISICA");
			} elseif ($dados_chamado["cliente_tipo"] == "JURIDICA") {
				if ($mudanca_ponto) {
					$this->registraAcao("MUDANCA_DE_PONTO");
				}
				if ($instalacao_roteador) {
					$this->registraAcao("ROTEADOR_INSTALACAO");
				}
				$this->registraAcao("VISITA_TECNICA_PESSOA_JURIDICA");
			}
		}

		print ("</ul>");

		//TODO ATENDIMENTO_SUPORTE_INTERNO_PESSOA_FISICA vs ATENDIMENTO_SUPORTE_INTERNO_PESSOA_JURIDICA
		if (!count($this->acoes) && $atendimento_suporte_interno) {
			$this->registraAcao("ATENDIMENTO_SUPORTE_INTERNO");
		}

		if (!count($this->acoes) && $atendimento_agendamento) {
			$this->registraAcao("AGENDAMENTO_ATENDIMENTO");
		}

		if (!count($this->acoes) && $atendimento_financeiro) {
			$this->registraAcao("FINANCEIRO_ATENDIMENTO");
		}

		if (!count($this->acoes) && $atendimento_ouvidoria) {
			$this->registraAcao("OUVIDORIA_ATENDIMENTO");
		}

		if (!count($this->acoes) && $atendimento_comercial) {
			$this->registraAcao("COMERCIAL_ATENDIMENTO");
		}
		
		if (count($this->acoes)) {
			$this->chamados_com_acao++;
			print "<div style=\"margin-bottom:20px;color:white;background-color:#666666;font-weight:bold;padding:3px 5px\">ACOES: ".implode(", ", $this->acoes)."</div>";
		} else {
			print "<div style=\"margin-bottom:20px;color:white;background-color:red;font-weight:bold;padding:3px 5px\">ACOES: NENHUMA</div>";
		}
		
	}
	
	var $id_acoes = array();
	
	function registraAcao($acao) {
		
		if (!in_array($acao, $this->acoes)) {
			$this->acoes[] = $acao;
			sort($this->acoes);
			
			///*
			
			if (!array_key_exists($acao, $this->id_acoes)) {
				
				$query = $this->bancodedados->query("SELECT caa_id FROM tab_chamados_acoes WHERE caa_nome = '{$acao}'");
				if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
					
					$this->id_acoes[$acao] = $dados["caa_id"];
					
				} else {
				
					$query = "INSERT INTO tab_chamados_acoes (caa_nome) VALUES ('{$acao}')";
					$this->bancodedados->query($query);
					$this->id_acoes[$acao] = $this->bancodedados->ultimoIdInserido();

				}
				
			}
			
			$acao_id = $this->id_acoes[$acao];
		
			$query = "INSERT INTO rel_cha_caa_contem (cha_id, caa_id) VALUES ({$this->chamado_id}, {$acao_id})";
			$this->bancodedados->query($query);
			
			//*/

		}

	}
	
	var $chamados_pop = array(
		"570741" => "POP_INFRAESTRUTURA",
		"571379" => "POP_INFRAESTRUTURA",
		"572323" => "POP_INFRAESTRUTURA",
		"570219" => "POP_MANUTENCAO",
		"570276" => "POP_MANUTENCAO",
		"570165" => "POP_MANUTENCAO",
		"570521" => "POP_MANUTENCAO",
		"570518" => "POP_MANUTENCAO",
		"570709" => "POP_MANUTENCAO",
		"551627" => "POP_MANUTENCAO",
		"570937" => "POP_MANUTENCAO",
		"562544" => "POP_MANUTENCAO",
		"571252" => "POP_MANUTENCAO",
		"571580" => "POP_MANUTENCAO",
		"571583" => "POP_MANUTENCAO",
		"571824" => "POP_MANUTENCAO",
		"571752" => "POP_MANUTENCAO",
		"571800" => "POP_MANUTENCAO",
		"571777" => "POP_MANUTENCAO",
		"571639" => "POP_MANUTENCAO",
		"571907" => "POP_MANUTENCAO",
		"572203" => "POP_MANUTENCAO",
		"560377" => "POP_MANUTENCAO",
		"572122" => "POP_MANUTENCAO",
		"572306" => "POP_MANUTENCAO",
		"572369" => "POP_MANUTENCAO",
		"572429" => "POP_MANUTENCAO",
		"572194" => "POP_MANUTENCAO",
		"572348" => "POP_MANUTENCAO",
		"549460" => "POP_MODERNIZACAO",
		"572625" => "POP_MANUTENCAO",
		"572686" => "POP_MANUTENCAO",
		"572818" => "POP_MANUTENCAO",
		"572823" => "POP_MANUTENCAO",
		"572824" => "POP_MANUTENCAO",
		"572856" => "POP_MANUTENCAO",
		"572595" => "POP_MANUTENCAO",
		"572696" => "POP_MANUTENCAO",
		"571995" => "POP_MANUTENCAO",
		"572794" => "POP_MANUTENCAO"
	);
	
}

?>
