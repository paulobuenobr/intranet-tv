CREATE TABLE tab_sicredi_log_liquidados (
  id int(10) NOT NULL,
  datahora datetime DEFAULT current_timestamp(),
  executado_baixa int(8) DEFAULT 0,
  ja_baixada int(8) DEFAULT 0,
  tipo varchar(5) DEFAULT 'ONTEM' COMMENT 'ONTEM\nHOJE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE tab_sicredi_log_liquidados
  ADD PRIMARY KEY (id);

ALTER TABLE tab_sicredi_log_liquidados
  MODIFY id int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

---------------------------------------------------------------
---------------------------------------------------------------

CREATE TABLE tab_sicredi_log (
  id int(10) NOT NULL,
  timetamp DATE NULL,
  error VARCHAR(45) NULL,
  code VARCHAR(6) NULL,
  message VARCHAR(2000) NULL,
  coa_id MEDIUMINT(8) NULL,
  fat_id MEDIUMINT(8) NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE tab_sicredi_log
  ADD PRIMARY KEY (id),
  ADD KEY IX_tab_sicredi_log_coa_id (coa_id);

ALTER TABLE tab_sicredi_log 
    CHANGE COLUMN id id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
    CHANGE COLUMN coa_id coa_id MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL ,
    CHANGE COLUMN fat_id fat_id MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE tab_sicredi_log   
  ADD KEY fat_id (fat_id);

ALTER TABLE tab_sicredi_log
  MODIFY id int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE tab_sicredi_log
  ADD CONSTRAINT tab_sicredi_log_fat_id FOREIGN KEY (fat_id) REFERENCES tab_faturas (fat_id);

ALTER TABLE tab_sicredi_log
  ADD CONSTRAINT tab_sicredi_log_coa_id FOREIGN KEY (coa_id) REFERENCES tab_cobrancas (coa_id);

------------------------------------------------------------------------------------------------
------------------------------------------------------------------------------------------------


ALTER TABLE tab_cobrancas_remessas ADD COLUMN cbr_gerado_auto INT(1) NULL DEFAULT 0 AFTER cof_id;

------------------------------------------------------------------------------------------------
------------------------------------------------------------------------------------------------

INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) 
    VALUES ('boleto_sicredi', 'gerarBoletosSicredi', '1440', '2023-12-12 07:01:01', '2023-12-12 07:01:01', '2023-12-12 07:01:01', 's', '0', '1');

INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo)
    VALUES ('boleto_sicredi', 'liquidadosPorDia', '1440', '2024-02-19 08:07:17', '2024-02-19 08:07:17', '2024-02-19 08:07:17', 's', '0', '1');

INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo)
    VALUES ('boleto_sicredi', 'liquidadosHoje', '10', '2023-12-12 07:01:01', '2023-12-12 07:01:01', '2023-12-12 07:01:01', 's', '0', '0');

INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo)
    VALUES ('boleto_sicredi', 'cancelarBoletos', '720', '2023-12-12 07:01:01', '2023-12-12 07:01:01', '2023-12-12 07:01:01', 's', '0', '1');

------------------------------------------------------------------------------------------------
------------------------------------------------------------------------------------------------

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('verificar_liquidacao', 'Verificar Liquidação(Verificar se foi liquidado(pago)', '24', '2');

------------------------------------------------------------------------------------------------
------------------------------------------------------------------------------------------------


CREATE TABLE tab_sicredi_authenticate (
  id int(1) NOT NULL,
  data datetime DEFAULT current_timestamp(),
  data_expira datetime DEFAULT NULL,
  scope varchar(45) DEFAULT NULL,
  access_token longtext DEFAULT NULL,
  expires_in int(4) DEFAULT NULL,
  refresh_expires_in int(4) DEFAULT NULL,
  refresh_token longtext DEFAULT NULL,
  token_type varchar(45) DEFAULT NULL,
  id_token longtext DEFAULT NULL,
  not_before_policy varchar(150) DEFAULT NULL,
  session_state varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO tab_sicredi_authenticate (id, data, data_expira, scope, access_token, expires_in, refresh_expires_in, refresh_token, token_type, id_token, not_before_policy, session_state) VALUES
(1, '2024-03-27 11:25:52', '2024-03-27 12:25:22', 'cobranca email profile', 'eyJ0eXAiOiJKV1QiLCJraWQiOiIzMWVkMTk1My02YzM1LTRjMWMtOGJmMy1jNTkwZGI4NzE4ZTciLCJhbGciOiJSUzI1NiJ9.eyJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwiZW1haWxfdmVyaWZpZWQiOmZhbHNlLCJ0eXAiOiJCZWFyZXIiLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiIxMjM0NTY3ODkiLCJzaWQiOiI1Y2E5NmU0Mi0wM2YwLTQyZTAtYWU4My0zZTdlNzJmMjYyYzgiLCJhY3IiOiIxIiwib3BlbmFwaV91c2VybmFtZSI6IjEyMzQ1Njc4OSIsIm9wZW5hcGlfY29udGV4dCI6IkNPQlJBTkNBIiwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbImRlZmF1bHQtcm9sZXMtb3BlbmFwaSIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iXX0sImF6cCI6Im9wZW5hcGktZ3ctc2Vuc2VkaWEiLCJzY29wZSI6ImNvYnJhbmNhIGVtYWlsIHByb2ZpbGUiLCJzZXNzaW9uX3N0YXRlIjoiNWNhOTZlNDItMDNmMC00MmUwLWFlODMtM2U3ZTcyZjI2MmM4IiwianRpIjoiNWNhOTZlNDItMDNmMC00MmUwLWFlODMtM2U3ZTcyZjI2MmM4Iiwic3ViIjoiZjplNmFhZWIwZC0zZGE3LTQ5ZmUtODM4NC00ODJjYzA1ZGVlYjU6Q09CUkFOQ0E6MTIzNDU2Nzg5IiwiaXNzIjoiaHR0cHM6Ly9hdXRoLW9wZW5hcGkudWF0LnNpY3JlZGkuY2xvdWQvcmVhbG1zL29wZW5hcGkiLCJpYXQiOjE3MTE1NDk1NTMsImV4cCI6MTcxMTU1MzE1MywiYXVkIjoiYWNjb3VudCJ9.Fz8U39Rgl5Yo-okaGSrIPi2_NIEcZEaL0qtxbUngfXWQ1kZ-h5wSZXSa0vFTZ4iPx1WZ9-l-EuCYmkUDtQ9lsaqF0ugOOhSghlRrHs_89PRFyQo5YjVLQHU3J3qLRCAEvJAnvOLZcfvA8qBDXinxMVKvo6EmIdvf_aajl5AojCuFMvtC9Zb8X4DhhL7teUPobDxEs0c79UNYwOxhso3FFm3ip7-2XX-DQkSU3H6iMwkN-V3j31MwF6KicoBOni8ur61ioV_839hivQ8_Qd9TV-urZd9lkbB75EJiB4ixM1TCuc4g8K1wf25dF45Cgge7U59EbHk-hpRWKGw_g3HFXQ', 3600, 900, 'eyJ0eXAiOiJKV1QiLCJraWQiOiIzMWVkMTk1My02YzM1LTRjMWMtOGJmMy1jNTkwZGI4NzE4ZTciLCJhbGciOiJSUzI1NiJ9.eyJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImF6cCI6Im9wZW5hcGktZ3ctc2Vuc2VkaWEiLCJzY29wZSI6ImNvYnJhbmNhIGVtYWlsIHByb2ZpbGUiLCJ0eXAiOiJSZWZyZXNoIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiMTIzNDU2Nzg5Iiwic2Vzc2lvbl9zdGF0ZSI6IjVjYTk2ZTQyLTAzZjAtNDJlMC1hZTgzLTNlN2U3MmYyNjJjOCIsImp0aSI6IjVjYTk2ZTQyLTAzZjAtNDJlMC1hZTgzLTNlN2U3MmYyNjJjOCIsInNpZCI6IjVjYTk2ZTQyLTAzZjAtNDJlMC1hZTgzLTNlN2U3MmYyNjJjOCIsInN1YiI6ImY6MDc5M2ZiYTEtY2ZiZC00MDNlLThiMmUtZTM5NmY4OGIyYzczOkNPQlJBTkNBOjEyMzQ1Njc4OSIsImlzcyI6Imh0dHBzOi8vYXV0aC1vcGVuYXBpLnVhdC5zaWNyZWRpLmNsb3VkL3JlYWxtcy9vcGVuYXBpIiwiaWF0IjoxNzExNTQ5NTUzLCJleHAiOjE3MTE1NTA0NTMsImF1ZCI6Imh0dHBzOi8vYXV0aC1vcGVuYXBpLnVhdC5zaWNyZWRpLmNsb3VkL3JlYWxtcy9vcGVuYXBpIn0.RPTu0JybABaFZilG4wL5M4_J1eRRORvhSIwIAwgtN4vnjd_KC74yPR6CbwZ9-R5-llG_RgtOFKkMyYhwz_G1Cwxn9tG6iHN2-3BjzmPKghCv5Aj5iVI_RUMDLfwWWcDDB4EZCSQLkX20khq6T_Y9_hTi6luTYQCNiBj8hX21MTuxMbkdfNCoq0Z6SRksX--ObqLofyhZF6EbrOJCzT5hBV9CHPerTN0qH7h7pig8TXD46RwP5e9jCPT6aRA7MjFmEnQeKkmlKTRe66I7S-o768VzcF7E4PEwh3D-qidzAYtvAyabmYgXwqktSmpIh8ACgxOj9ggzmV4lGjhwsvLdOw', 'Bearer', 'eyJ0eXAiOiJKV1QiLCJraWQiOiIzMWVkMTk1My02YzM1LTRjMWMtOGJmMy1jNTkwZGI4NzE4ZTciLCJhbGciOiJSUzI1NiJ9.eyJvcGVuYXBpX3VzZXJuYW1lIjoiMTIzNDU2Nzg5IiwiZW1haWxfdmVyaWZpZWQiOmZhbHNlLCJvcGVuYXBpX2NvbnRleHQiOiJDT0JSQU5DQSIsImF6cCI6Im9wZW5hcGktZ3ctc2Vuc2VkaWEiLCJzY29wZSI6ImNvYnJhbmNhIGVtYWlsIHByb2ZpbGUiLCJhdXRoX3RpbWUiOjAsInR5cCI6IklEIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiMTIzNDU2Nzg5Iiwic2Vzc2lvbl9zdGF0ZSI6IjVjYTk2ZTQyLTAzZjAtNDJlMC1hZTgzLTNlN2U3MmYyNjJjOCIsImp0aSI6IjVjYTk2ZTQyLTAzZjAtNDJlMC1hZTgzLTNlN2U3MmYyNjJjOCIsInNpZCI6IjVjYTk2ZTQyLTAzZjAtNDJlMC1hZTgzLTNlN2U3MmYyNjJjOCIsInN1YiI6ImY6MjQ5YjJhNmEtZjg0MS00YzdjLWExZTQtMmM3YzFlYmEzNmFlOkNPQlJBTkNBOjEyMzQ1Njc4OSIsImlzcyI6Imh0dHBzOi8vYXV0aC1vcGVuYXBpLnVhdC5zaWNyZWRpLmNsb3VkL3JlYWxtcy9vcGVuYXBpIiwiaWF0IjoxNzExNTQ5NTUzLCJleHAiOjE3MTE1NTMxNTMsImF1ZCI6Im9wZW5hcGktZ3ctc2Vuc2VkaWEifQ.Wi_2UVdiEjmg-VlPpgI56C7aqp76dzAoKcMWzAfisCitnfxt7y3Thtk1c0JJbCyJIuVXX-x_z69r7mRJfBjNUIusUj2o8NZ92doR8P9H6EY5OvTm8SuFQRJiMCDfVlWUGK9CVPcRSUu0wVEmL1VZYwvJEEOJXF5U-Qr35QWnZIAaRryg_KMMp62UztPw8OmFxhpRj2x8LFQItBkxMs_0z0ShT9ebfg5qS0eaWH09AY-e8hdWfKSuO3kIo31WXAb1KDa75XaKQANshPEY5rTnewWM5YTRVEDcAO_SYabv2uEEP_EJgjsBUsVR170jqZo8dOZQ95ZcubS94DBNG5jyMw', '0', '5ca96e42-03f0-42e0-ae83-3e7e72f262c8');


ALTER TABLE tab_sicredi_authenticate
  ADD PRIMARY KEY (id);

ALTER TABLE tab_sicredi_authenticate
  MODIFY id int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

------------------------------------------------------------------------------------------------
------------------------------------------------------------------------------------------------

CREATE TABLE tab_sicredi_login (
  id int(2) UNSIGNED NOT NULL,
  homologacao int(1) DEFAULT 0,
  api_key varchar(50) DEFAULT NULL,
  client_id varchar(50) DEFAULT NULL,
  client_secret varchar(50) DEFAULT NULL,
  password varchar(300) DEFAULT NULL,
  username varchar(45) DEFAULT NULL,
  beneficiary varchar(45) DEFAULT NULL,
  cooperative varchar(45) DEFAULT NULL,
  post varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='cooperative\n';


INSERT INTO tab_sicredi_login (id, homologacao, api_key, client_id, client_secret, password, username, beneficiary, cooperative, post) VALUES
(1, 1, 'b795152e-faa2-4a91-8349-76f9fd315c3a', 'a4832c61-3c81-4c59-9fe3-27b5beec3ccc', 'e59fd23e-0822-4548-9277-bb0968d2244a', 'teste123', '123456789', '12345', '6789', '03'),
(2, 0, 'dc99bb96-ead4-43a9-aef0-7eb53721be52', '34124b76-0bc0-4b10-b990-cf0cc0a86e98', 'a44e2bdb-1422-460c-86e0-db5c84ce8355', '04D71752ED34B0665D8C61EB90C6CDA84880D11E9D9D06F8BE1F5C1549687CBB', '888190226', '88819', '0226', '32');

ALTER TABLE tab_sicredi_login
  ADD PRIMARY KEY (id);

ALTER TABLE tab_sicredi_login
  MODIFY id int(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- ----------------------------------------------------------------------------------------------
-- ----------------------------------------------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

ALTER TABLE tab_cobrancas ADD COLUMN coa_data_registro DATE NULL AFTER cot_id, ADD COLUMN coa_data_cancelamento DATE NULL AFTER coa_data_registro;

-- ----------------------------------------------------------------------------------------------
-- ----------------------------------------------------------------------------------------------

INSERT INTO tab_menus (men_nome, men_modulo, men_acao, men_ordem, men_pai) VALUES ('Pesquisa Boleto', 'cobrancas', 'pesquisa_boleto', '1', '16');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('pesquisa_boleto', 'Pesquisar boleto', '24', '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('ver_boleto', 'Ver boleto', '24', '2');

-- ----------------------------------------------------------------------------------------------
-- ----------------------------------------------------------------------------------------------

CREATE TABLE tab_log_retorno (
  id INT(10) NOT NULL AUTO_INCREMENT,
  id_retorno INT(10) NULL,
  nosso_numero VARCHAR(10) NULL,
  coa_id MEDIUMINT(8) NULL,
  tipo ENUM('REGISTRO', 'REJEICAO', 'LIQUIDACAO', 'BAIXA') NULL,
  data_hora TIMESTAMP NULL,
  PRIMARY KEY (id));

ALTER TABLE tab_log_retorno 
    CHANGE COLUMN data_hora data_hora TIMESTAMP NULL DEFAULT now() ;

ALTER TABLE tab_log_retorno 
    CHANGE COLUMN coa_id coa_id MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL ;

ALTER TABLE tab_log_retorno ADD CONSTRAINT coa_id FOREIGN KEY (coa_id)
    REFERENCES tab_cobrancas (coa_id) ON DELETE NO ACTION ON UPDATE NO ACTION;


-- ----------------------------------------------------------------------------------------------
-- ----------------------------------------------------------------------------------------------
