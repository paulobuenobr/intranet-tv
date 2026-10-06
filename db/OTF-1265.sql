SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

INSERT INTO tab_menus (men_nome, men_ordem, men_pai) VALUES ('Multiservise', '1', '3');

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

INSERT INTO tab_menus (men_nome, men_ordem, men_modulo, men_acao, men_pai) SELECT "Players", 1, "multiservice_players", "listar", men_id FROM tab_menus WHERE men_nome LIKE 'Multiservise' AND men_pai = 3 LIMIT 1;
INSERT INTO tab_menus (men_nome, men_ordem, men_modulo, men_acao, men_pai) SELECT "Serviços", 2, "multiservice_servicos", "listar", men_id FROM tab_menus WHERE men_nome LIKE 'Multiservise' AND men_pai = 3 LIMIT 1;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id) VALUES ('multiservice_players', 'Multiservise Players', '1');
INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id) VALUES ('multiservice_servicos', 'Multiservise Serviços', '1');

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "adicionar", "Adicionar", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_players' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "listar", "Listar", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_players' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "editar", "Editar", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_players' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "ver", "Ver", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_players' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "remover", "Remover", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_players' LIMIT 1;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "adicionar", "Adicionar", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_servicos' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "listar", "Listar", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_servicos' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "editar", "Editar", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_servicos' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "ver", "Ver", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_servicos' LIMIT 1;
INSERT INTO tab_acoes (aco_nome, aco_descricao, act_id, mod_id ) SELECT "remover", "Remover", 1 , mod_id FROM tab_modulos WHERE mod_nome LIKE 'multiservice_servicos' LIMIT 1;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_multiservice_status (
    id int unsigned NOT NULL,
    nome varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE tab_multiservice_status ADD PRIMARY KEY (id);
ALTER TABLE tab_multiservice_status MODIFY id int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE tab_multiservice_status MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT;

INSERT INTO tab_multiservice_status (id, nome) VALUES (1, 'Ativo'), (2, 'Inativo');

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_multiservice_players (
    id int UNSIGNED NOT NULL,
    nome varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    descricao tinytext CHARACTER SET utf8mb4 DEFAULT NULL,
    data_criacao datetime NOT NULL DEFAULT current_timestamp(),
    status int UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE tab_multiservice_players ADD PRIMARY KEY (id), ADD KEY tab_multiservice_players_status_id_idx (status);
ALTER TABLE tab_multiservice_players MODIFY id int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE tab_multiservice_players ADD CONSTRAINT tab_multiservice_status_id FOREIGN KEY (status) REFERENCES tab_multiservice_status (id);
ALTER TABLE tab_multiservice_players ADD COLUMN codigo VARCHAR(150) NOT NULL AFTER nome;

INSERT INTO tab_multiservice_players (id, nome, descricao, data_criacao, status) VALUES (1, 'IGS', 'IGS', '2025-04-01 15:42:07', 1);
-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_multiservice_servicos (
    id int UNSIGNED NOT NULL,
    nome varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    player int UNSIGNED NOT NULL,
    codigo varchar(150) CHARACTER SET utf8mb4 NOT NULL,
    data_criacao datetime NOT NULL DEFAULT current_timestamp(),
    descricao tinytext CHARACTER SET utf8mb4 DEFAULT NULL,
    status int UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE tab_multiservice_servicos ADD PRIMARY KEY (id), ADD KEY tab_multiservice_players_id_idx (player), ADD KEY tab_multiservice_players_status_id_idx (status);
ALTER TABLE tab_multiservice_servicos MODIFY id int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE tab_multiservice_servicos
    ADD CONSTRAINT tab_multiservice_players_id FOREIGN KEY (player) REFERENCES tab_multiservice_players (id),
    ADD CONSTRAINT tab_multiservice_players_status_id FOREIGN KEY (status) REFERENCES tab_multiservice_status (id);

INSERT INTO tab_multiservice_servicos (id, nome, player, codigo, data_criacao, descricao, status) VALUES (1, 'Serve 01', 1, 'ZXSWRFC1S', '2025-04-02 16:07:01', '', 1);
-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_planos_multiservice (
    id int UNSIGNED NOT NULL,
    player int UNSIGNED NOT NULL,
    servico int UNSIGNED NOT NULL,
    pla_id smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE tab_planos_multiservice
    ADD PRIMARY KEY (id),
    ADD KEY tab_planos_pla_id_idx (pla_id),
    ADD KEY tab_multiservice_servicos_id_idx (servico),
    ADD KEY tab_multiservice_players_id_idx (player);

ALTER TABLE tab_planos_multiservice MODIFY id int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE tab_planos_multiservice
    ADD CONSTRAINT tab_multiservice_players_id1 FOREIGN KEY (player) REFERENCES tab_multiservice_players (id),
    ADD CONSTRAINT tab_multiservice_servicos_id1 FOREIGN KEY (servico) REFERENCES tab_multiservice_servicos (id),
    ADD CONSTRAINT tab_planos_pla_id_ms FOREIGN KEY (pla_id) REFERENCES tab_planos (pla_id);

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

ALTER TABLE tab_contratos ADD COLUMN cor_multiservice_enviado INT(1) NOT NULL DEFAULT 1 AFTER crs_id;
ALTER TABLE tab_contratos CHANGE COLUMN cor_multiservice_enviado cor_multiservice_enviado INT(1) NOT NULL DEFAULT 0;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_multiservice_envio (
    id int UNSIGNED NOT NULL,
    contrato mediumint(8) UNSIGNED NOT NULL,
    cliente mediumint(8) UNSIGNED NOT NULL,
    enviado tinytext DEFAULT NULL,
    recebido tinytext DEFAULT NULL,
    data_gerado datetime NOT NULL DEFAULT current_timestamp(),
    status int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE tab_multiservice_envio
    ADD PRIMARY KEY (id), 
    ADD KEY tab_multiservice_envio_cliente (cliente),
    ADD KEY tab_multiservice_envio_contrato (contrato);

ALTER TABLE tab_multiservice_envio
    ADD CONSTRAINT tab_multiservice_envio_cliente FOREIGN KEY (cliente) REFERENCES tab_clientes (cli_id),
    ADD CONSTRAINT tab_multiservice_envio_contrato FOREIGN KEY (contrato) REFERENCES tab_contratos (cor_id);

ALTER TABLE tab_multiservice_envio CHANGE COLUMN id id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT ;

ALTER TABLE tab_multiservice_envio CHANGE COLUMN data_gerado data_gerado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ;

ALTER TABLE tab_multiservice_envio CHANGE enviado enviado TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, CHANGE recebido recebido TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) 
VALUES ('multiservice', 'envia', '360', '2023-12-12 06:00:00', '2023-12-12 06:00:00', '2023-12-12 06:00:00', 's', '0', '1');

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_multiservice_login (
    id int(4) NOT NULL,
    usuario varchar(255) DEFAULT NULL,
    senha varchar(255) DEFAULT NULL,
    ativo int(1) DEFAULT 1,
    link varchar(255) DEFAULT NULL,
    accessKey varchar(155) DEFAULT NULL,
    homologacao int(1) NOT NULL DEFAULT 0,
    data_ativa_sva date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tab_multiservice_login (id, usuario, senha, ativo, link, accessKey, homologacao, data_ativa_sva) VALUES
(1, 'carlos_oletv', 'carlos@123SecureWeb', 1, 'http://multiservice.ole.net.br:3001', NULL, 1, '2026-02-16'),
(2, 'carlos_oletv', 'carlos@123SecureWeb', 0, 'http://multiservice.ole.net.br:3001/api', NULL, 0, '2026-02-16');

ALTER TABLE tab_multiservice_login ADD PRIMARY KEY (id);

ALTER TABLE tab_multiservice_login MODIFY id int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

CREATE TABLE tab_multiservice_authenticate (
  id int(1) NOT NULL,
  data datetime DEFAULT current_timestamp(),
  data_expira datetime DEFAULT NULL,
  accessToken longtext DEFAULT NULL,
  expireIn int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tab_multiservice_authenticate (id, data, data_expira, accessToken, expireIn) VALUES
(1, '2026-02-13 14:28:23', '2026-02-14 14:27:53', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImNhcmxvc19vbGV0diIsInN1YiI6IjI2ZWIxNmUwLTBkN2YtNGNlNS05OWQxLThjMzkwOGRmNGNjYyIsImxldmVsIjoic3VwZXJfYWRtaW4iLCJwcm9maWxlIjoic3VwZXJfYWRtaW4iLCJwYXJ0bmVySWQiOm51bGwsImlhdCI6MTc3MTAwMzcwMywiZXhwIjoxNzcxMDkwMTAzfQ.TOKUOv0rynllz-Q0YHqY24jH7JnfVLo86mH_9qsSGpo', 86400);

ALTER TABLE tab_multiservice_authenticate
  ADD PRIMARY KEY (id);

ALTER TABLE tab_multiservice_authenticate
  MODIFY id int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- ------------------------------------------------------------------------
-- ------------------------------------------------------------------------

UPDATE tab_clientes_origens_comentarios SET conteudo = '<b>Ação</b>: Bloqueado Origem' WHERE conteudo LIKE '%Bloquado Origem%'