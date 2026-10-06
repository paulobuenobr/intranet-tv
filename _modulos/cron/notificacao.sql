INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo)
VALUES ('notificacao_cobranca', 'enviaCobrancas', '1440', '2024-07-04 10:38:57', '2024-07-04 10:38:57', '2024-07-04 10:38:57', 's', '0', '1');

-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `tab_dias_notificar` (
  `id` int(8) UNSIGNED NOT NULL,
  `dias` int(8) NOT NULL DEFAULT 0,
  `assunto` varchar(300) DEFAULT NULL,
  `conteudo` longtext DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 1 COMMENT '0 - inativo\n1 - ativo',
  `tipo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tab_dias_notificar` (`id`, `dias`, `assunto`, `conteudo`, `status`, `tipo`) VALUES
(1, 3, 'Aviso de vencimento para faturas em aberto no sistema', '<p>Olá, [NOME_DO_CLIENTE]!</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>\n<p>Estamos passando para lembrar que sua fatura da Olé TV vencerá em 3 dias. Para garantir que tudo continue funcionando perfeitamente, pedimos que realize o pagamento do boleto anexo até a data de vencimento. Se porventura tenha efetuado a quitação do boleto nas últimas 24h, por favor, desconsiderar a mensagem.</p>\n<p>&nbsp;</p>\n<p>Caso precise de qualquer assistência ou tenha dúvidas, entre em contato conosco através do telefone ou WhatsApp <a href=\"https://wa.me/554830280000\">(48) 3028-0000</a>, estamos aqui para ajudar!</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>\n<p>A equipe Olé TV agradece e deseja um ótimo dia!</p>', 1, 'notificacao_cobranca'),
(2, 0, 'Aviso de vencimento para faturas em aberto no sistema', '<p>Olá, [NOME_DO_CLIENTE]!</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>\n<p>Estamos passando para lembrar que sua fatura da Olé TV vence hoje. Para garantir que tudo continue funcionando perfeitamente, pedimos que realize o pagamento do boleto anexo. Se porventura tenha efetuado a quitação do boleto nas últimas 24h, por favor, desconsiderar a mensagem.</p>', 1, 'notificacao_cobranca');

ALTER TABLE `tab_dias_notificar` ADD PRIMARY KEY (`id`);

ALTER TABLE `tab_dias_notificar`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

-- --------------------------------------------------------

