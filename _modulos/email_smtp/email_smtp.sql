CREATE TABLE `tab_email_smtp` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `porta` varchar(6) NOT NULL,
  `de` varchar(255) DEFAULT NULL,
  `seguranca` varchar(6) DEFAULT 'tls',
  `tipo` varchar(45) DEFAULT NULL,
  `ativo` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `tab_modulos` (`mod_nome`, `mod_descricao`) VALUES ('email_smtp', 'E-mail SMTP');

SET @mod_id = LAST_INSERT_ID();

INSERT INTO `tab_acoes` (`mod_id`, `aco_nome`, `aco_descricao`, `act_id`) VALUES
(@mod_id, 'buscar', 'Buscar', 1),
(@mod_id, 'ver', 'Ver', 1),
(@mod_id, 'adicionar', 'Adicionar', 1),
(@mod_id, 'editar', 'Editar', 1),
(@mod_id, 'remover', 'Remover', 1);
