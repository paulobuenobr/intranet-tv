<?php
define('API_HOST', 'https://'.$_SERVER['HTTP_HOST'].'/api');//'https://oletv.oletelecom.com.br/api'
define('INTRANET_HOST', 'http://'.$_SERVER['HTTP_HOST']);//'http://oletv.oletelecom.com.br'

define('INTRANET_HOSTFISICO', '/var/www/html/intranet');
define('API_HOSTFISICO', '/var/www/html/intranet/api');
define("DIR_RAIZ", INTRANET_HOSTFISICO.'/');
define("DIR_GLOBAL", DIR_RAIZ."_global/");
define("DIR_MODULOS", DIR_RAIZ."_modulos/");
define("DIR_PAINEL", DIR_RAIZ."_painel/");

// Banco de dados
define('DB_HOST', '10.0.100.250');
define('DB_NAME', 'tvintranet');
define('DB_USER', 'tvintranetuser');
define('DB_PASS', 'WSinTr@net');



define('DIAS_BLOQUEIO', 15); // buscar do clientes origens
define('COBRANCA_FORMATO', 5); // vai cair fora
define('DIAS_VENCIMENTO', ['5'=>'Dia 05', '6'=>'Dia 06', '7'=>'Dia 07', '8'=>'Dia 08', '10'=>'Dia 10', '20'=>'Dia 20'] );
define('TIPOS_COMPLEMENTO', ['a'=>'Apartamento', 'l'=>'Loja', 's'=>'Sala', 'c'=>'Casa', 'o'=>'Outro'] );
define('TIPOS_TELEFONE', ['1'=>'Residencial', '2'=>'Comercial', '3'=>'Celular', '4'=>'Fax', '5'=>'Recados', '7'=>'WhatsApp'] );
define('TIPOS_CLIENTE', ['1'=>'Pessoa Física', '2'=>'Pessoa Jurídica'] );
define('TIPOS_EMAIL', ['1'=>'Contato/News','2'=>'Contato'] ); // sempre como contato, retirar essa tratativa
define('STATUS_BOLETO', ['Aberto'=>'Aberto','Pago'=>'Pago'] );

define('DECODER_PADRAO', 12);


$_SESSION["usuario"] = null;


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
define("CONTRATO_INDICE_REAJUSTE_PF", " ndice Nacional de Pre os ao Consumidor do Instituto Brasileiro de Geografia e Estat stica   INPC/IBGE");
define("CONTRATO_INDICE_REAJUSTE_PJ", " ndice Geral de Pre os de Mercado da Funda  o Get lio Vargas - IGP-M/FGV");

// Email para avisos
//const MAIL_ALERT = 'suporte@futurasites.com.br';

//ERROR 1452 (23000) at line 21051: Cannot add or update a child row: a foreign key constraint fails (`tvintranet`.`#sql-dbc_20`, CONSTRAINT `rel_usu_gru_fazparte_gru_id` FOREIGN KEY (`gru_id`) REFERENCES `tab_grupos` (`gru_id`))




// ADD tab_cobrancas_status =  15 	Baixa Parcial (Empresa Cobrança) 	1 	1