<?

// Data Hora Alteração
define("DATA_HORA_ALTERACAO", "2019-07-10 11:22:00");

// Diretorios
define("DIR_RAIZ", dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR);
define("DIR_GLOBAL", DIR_RAIZ."_global/");
define("DIR_MODULOS", DIR_RAIZ."_modulos/");
define("DIR_PAINEL", DIR_RAIZ."_painel/");

// Outros
define("PAG_ITENS", array_key_exists("itens", $_GET) ? $_GET["itens"] : 60); // Quantidade de itens por página
define("PAG_ITENS_SISTEMA_CHAMADOS", 15); // Quantidade de itens por página
define("BUS_ITENS", 20); // Quantidade de itens por busca
define("RELATORIO_PERIODO", 45); // Periodo máximo para os relatórios

// Painel
define("PAINEL_DELAY", 1800); // Delay entre refresh do painel (30 minutos)
define("PAINEL_DELAY_ANTERIORES", 3600); // Delay entre refresh do painel (8 horas)
define("PAINEL_COR_BOM", "#066606"); // Verde
define("PAINEL_COR_MEDIO", "#666606"); // Amarelo
define("PAINEL_COR_RUIM", "#660606"); // Vermelho

// Comercial
define("COMERCIAL_LIMITE_RESPONSABILIDADE_CONDOMINIO", 100);
define("COMERCIAL_LIMITE_DIAS_CANCELAMENTO", 7);

// Descontos
define("DESCONTOS_ADICIONAR_RETORNO", 15); // Dias antes de expirar

// Cobranças
define("COBRANCA_SEGUNDAVIA_VENCIMENTO_MINIMO", 10); // dias
define("COBRANCA_SEGUNDAVIA_VENCIMENTO_MAXIMO", 60); // dias
define("COBRANCA_AVULSA_VENCIMENTO_MAXIMO", 60); // dias
define("COBRANCA_BLOQUEIO_GERENCIAMENTO_DO_CLIENTE", 5); // dias
define("COBRANCA_DIAS_ENVIO_SERASA", 90); // dias
define("BLOQUEIO_INADINPLENCIA_SEGUNDA_VIA", 5); // Quantidade de dias para bloqueio
define("BLOQUEIO_INADINPLENCIA_PRE_PAGO", 15); // Quantidade de dias para bloqueio
define("BLOQUEIO_INADINPLENCIA_POS_PAGO", 15); // Quantidade de dias para bloqueio

// Verificação de Crédito
define("FINANCEIRO_VERIFICACAO_VALIDADE", 15); // dias
define("FINANCEIRO_LIMITE_CHEQUES", 5); // cheques
define("FINANCEIRO_LIMITE_RESTRICOES", 5); // cheques

// Cobrança Online
define("COBRANCA_ONLINE_URL_EXTERNA", "http://www.oletelecom.com.br/cobrancas/");

// Inicio Contrato
define("CONTRATO_VERSAO", 2);
define("CONTRATO_COBRANCA_HUB", 12000);
define("CONTRATO_COBRANCA_PLACA_DE_REDE", 2500);
define("CONTRATO_COBRANCA_DESCONTO_AVISTA", 0);
define("CONTRATO_COBRANCA_TAXA_DESLIGAMENTO", 10000);
define("CONTRATO_PRAZO_CARENCIA", 30); // dias
define("CONTRATO_PRAZO_RELIGAMENTO", 24); // horas
define("CONTRATO_MES_REAJUSTE", 7); // mes
define("CONTRATO_TAXA_VISITA_TECNICA", 3000); // dinheiro
define("CONTRATO_TAXA_RELIGAMENTO", 2000); // dinheiro
define("CONTRATO_CUSTO_HUB", 12000); // dinheiro
define("CONTRATO_CUSTO_PLACA_DE_REDE", 2500); // dinheiro
define("CONTRATO_INSTALACAO_MUDANCA_ENDERECO", 6000); // dinheiro
define("CONTRATO_VIGENCIA_MINIMA", 12); // meses
define("CONTRATO_VIGENCIA_MAXIMA", 24); // meses
define("CONTRATO_VIGENCIA_LIMITE", 12000); // dinheiro
define("CONTRATO_VIGENCIA_EDITAVEL_LIMITE", 6000); // dinheiro
define("CONTRATO_MULTA_ATRASO_PF", 2); // porcento
define("CONTRATO_MULTA_ATRASO_PJ", 10); // porcento
define("CONTRATO_INDICE_REAJUSTE_PF", "Índice Nacional de Preços ao Consumidor do Instituto Brasileiro de Geografia e Estatística – INPC/IBGE");
define("CONTRATO_INDICE_REAJUSTE_PJ", "Índice Geral de Preços de Mercado da Fundação Getúlio Vargas - IGP-M/FGV");

// Contrato Online
define("CONTRATO_ONLINE_URL_EXTERNA", "http://www.wavesystem.com.br/contratos/");

// Integração
define("INTEGRACAO_DISPONIVEL", true);
define("INTEGRACAO_ENDERECO_ORIGEM", "127.0.0.1");

// Sistema
define("URL_SISTEMA_COMPRAS", "http://www.wavesystem.com.br/pedidos/"); // URL do sistema de compras
define("URL_SISTEMA_OS", "./os/"); // URL do sistema de os
define("URL_SISTEMA_ESTOQUE","./estoque/"); // URL do sistema de estoque
define("URL_SISTEMA_WEBMAIL", "http://webmail.wavesystem.com.br/"); // URL do sistema de cancelamento

// Mailing
define("MAILING_TELELISTAS_EMAIL", "nicio@inf.ufsc.br");
define("MAILING_TELELISTAS_SENHA", "123456");

// SSH
define("SSH_CHAVE", DIR_RAIZ.".ssh/servers_rsa");
define("SSH_USUARIO", "intranet");
define("SSH_TIMEOUT", "60");

// Técnica
$pontos_tecnica = array(
    "00:1f:c6:8b:c9:de",
    "00:19:46:00:01:62",
    "00:02:cb:bf:70:a2",
    "00:19:46:00:06:a8",
    "00:90:f5:5b:89:fe",
    "00:90:f5:5b:61:3d",
    "00:40:f4:eb:a0:94",
    "00:40:ca:7d:68:ed",
    "00:1b:fc:6d:f2:e3",
    "00:1b:38:4e:88:f1",
    "00:1e:8c:cf:5c:ff",
    "00:13:20:20:cf:c8",
    "00:1e:8c:bc:ab:97",
    "00:13:20:20:cf:c8",
    "00:1e:8c:bc:a5:09",
    "00:90:f5:5f:70:85",
    "00:22:15:55:4d:f6",
    "00:1f:c6:87:B6:86",
    "00:1f:c6:53:0e:9b",
    "00:22:15:10:41:ca",
    "00:1f:c6:8b:c8:7c",
    "48:5b:39:5b:11:27"
);

define("PUBLICACAO_PONTOS_TECNICA", serialize($pontos_tecnica));


// Visitas
//dias Uteis
define("VISITAS_TURNO_INSTALACAO", 20);
define("VISITAS_TURNO_MIGRACAO", 20);

define("VISITAS_CASAS_MANHA", 6);
define("VISITAS_CASAS_TARDE", 8);
define("VISITAS_CASAS_NOITE", 0);
define("VISITAS_CONDOMINIO_MANHA", 8);
define("VISITAS_CONDOMINIO_TARDE", 8);
define("VISITAS_CONDOMINIO_NOITE", 2);

//sabado
define("VISITAS_CASAS_SABADO_MANHA", 4);
define("VISITAS_CASAS_SABADO_TARDE", 4);
define("VISITAS_CASAS_SABADO_NOITE", 1);
define("VISITAS_CONDOMINIO_SABADO_MANHA", 4);
define("VISITAS_CONDOMINIO_SABADO_TARDE", 4);
define("VISITAS_CONDOMINIO_SABADO_NOITE", 1);

//domingo
define("VISITAS_CASAS_DOMINGO_MANHA", 4);
define("VISITAS_CASAS_DOMINGO_TARDE", 4);
define("VISITAS_CASAS_DOMINGO_NOITE", 1);
define("VISITAS_CONDOMINIO_DOMINGO_MANHA", 4);
define("VISITAS_CONDOMINIO_DOMINGO_TARDE", 4);
define("VISITAS_CONDOMINIO_DOMINGO_NOITE", 1);

// E-mail
define("MENSAGEM_SERVIDOR_SMTP", "200.194.232.99");

?>
