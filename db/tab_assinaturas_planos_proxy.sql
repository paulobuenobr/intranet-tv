-- Vínculo entre plano contratado, assinatura parceira elegível e plano proxy (limitação por valor).
-- Regra de negócio: ao incluir um plano limitado por proxy, gravar a primeira assinatura (ass_id)
-- com capacidade disponível para aquele plano, conforme a lógica de tab_planos_limitacao / possuiLimiteDisponivel.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `tab_assinaturas_planos_proxy` (
  `asp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pla_id` smallint(5) unsigned NOT NULL COMMENT 'ID do plano adicionado (contratado)',
  `ass_id` mediumint(8) unsigned NOT NULL COMMENT 'ID da assinatura parceira com valor disponível',
  `pla_proxy_id` smallint(5) unsigned NOT NULL COMMENT 'ID do plano proxy (limitador)',
  PRIMARY KEY (`asp_id`),
  KEY `idx_asp_pla_id` (`pla_id`),
  KEY `idx_asp_ass_id` (`ass_id`),
  KEY `idx_asp_pla_proxy_id` (`pla_proxy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `tab_assinaturas_planos_proxy`
  ADD CONSTRAINT `fk_asp_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asp_ass_id` FOREIGN KEY (`ass_id`) REFERENCES `tab_assinaturas` (`ass_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asp_pla_proxy_id` FOREIGN KEY (`pla_proxy_id`) REFERENCES `tab_planos` (`pla_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

COMMIT;
