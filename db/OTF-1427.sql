-- ##############################################################
-- Insere campos clo_vod_status e clo_vod_exibir_titulos
-- na tabela tab_clientes_origens
-- ##############################################################

ALTER TABLE `tab_clientes_origens`
	ADD `clo_vod_status` ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'INATIVO' AFTER `clo_pai`,
	ADD `clo_vod_exibir_titulos` ENUM('TODOS','CONTRATADOS') NOT NULL DEFAULT 'TODOS' AFTER `clo_vod_status`;

-- ##############################################################
-- Cria tabela tab_iptv_pacotes
-- ##############################################################

CREATE TABLE IF NOT EXISTS `tab_iptv_pacotes` (
	`ipt_id` int(10) unsigned NOT NULL,
	`ipt_nome` varchar(100) NOT NULL,
	`ipt_codigo` varchar(100) NOT NULL,
	`ipt_tipo` enum('PACOTE','CATALOGO') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `tab_iptv_pacotes`
	ADD PRIMARY KEY (`ipt_id`);

ALTER TABLE `tab_iptv_pacotes`
	MODIFY `ipt_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

INSERT INTO `tab_iptv_pacotes` (`ipt_id`, `ipt_nome`, `ipt_codigo`, `ipt_tipo`) VALUES
	(1, 'Vubiquity', '1', 'CATALOGO'),
	(2, 'Nirvana', '5', 'CATALOGO'),
	(3, 'Youcast', '9', 'CATALOGO'),
	(4, 'Mobile', '60', 'PACOTE'),
	(5, '0. Life Line HD', '61', 'PACOTE'),
	(6, '1. Start HD', '62', 'PACOTE'),
	(7, '2. Top HD', '63', 'PACOTE'),
	(8, '3. Premium HD', '64', 'PACOTE'),
	(10, 'À la carte - HBO', '67', 'PACOTE'),
	(11, 'À la carte - Sex Privé', '68', 'PACOTE'),
	(12, 'News+', '88', 'PACOTE'),
	(13, 'Á la carte - HTO Adultos', '90', 'PACOTE'),
	(14, 'À la carte - HTO', '91', 'PACOTE'),
	(15, 'Life+ SVA I', '92', 'PACOTE'),
	(16, 'Life+ SVA II', '94', 'PACOTE'),
	(17, 'Total+', '96', 'PACOTE'),
	(18, 'À la carte - Playboy', '104', 'PACOTE'),
	(19, 'À la carte - Playboy + SexyHot', '105', 'PACOTE'),
	(20, 'À la carte - Playboy + Sexy Hot + Venus + Sextreme', '106', 'PACOTE'),
	(21, 'Simba', '114', 'PACOTE'),
	(24, 'Light+', '120', 'PACOTE'),
	(25, 'Full', '121', 'PACOTE'),
	(26, 'À la carte - ESPN', '124', 'PACOTE');

-- ##############################################################
-- Módulo: iptv_pacotes
-- Ações: adicionar, listar, editar, ver, remover
-- Menu: insere IPTV Pacotes dentro de Planos
-- ##############################################################

INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id) VALUES ('iptv_pacotes', 'IPTV Pacotes', '1');

INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id) SELECT 'adicionar', 'Adicionar', 1, mod_id FROM tab_modulos WHERE mod_nome = 'iptv_pacotes' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id) SELECT 'listar',    'Listar',    1, mod_id FROM tab_modulos WHERE mod_nome = 'iptv_pacotes' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id) SELECT 'editar',    'Editar',    1, mod_id FROM tab_modulos WHERE mod_nome = 'iptv_pacotes' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id) SELECT 'ver',       'Ver',       1, mod_id FROM tab_modulos WHERE mod_nome = 'iptv_pacotes' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id) SELECT 'remover',   'Remover',   1, mod_id FROM tab_modulos WHERE mod_nome = 'iptv_pacotes' LIMIT 1;

INSERT INTO tab_menus (men_nome, men_ordem, men_modulo, men_acao, men_pai)
    SELECT 'IPTV Pacotes', 99, 'iptv_pacotes', 'listar', men_pai
    FROM tab_menus
    WHERE men_modulo = 'planos' AND men_acao = 'listar'
    LIMIT 1;
