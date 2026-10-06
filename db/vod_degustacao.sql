ALTER TABLE tab_clientes_origens 
ADD COLUMN clo_degustacao INT(4) NULL AFTER clo_pai,
ADD COLUMN clo_plano_vod INT(4) NOT NULL DEFAULT 0 AFTER clo_degustacao;

ALTER TABLE tab_clientes_origens CHANGE COLUMN clo_degustacao clo_degustacao INT(4) NULL DEFAULT 0;

ALTER TABLE tab_clientes_origens ADD COLUMN clo_inicio_degustacao DATETIME NULL AFTER clo_plano_vod;

ALTER TABLE tab_clientes_origens CHANGE COLUMN clo_inicio_degustacao clo_inicio_degustacao DATE NULL DEFAULT NULL;

ALTER TABLE tab_clientes_origens CHANGE COLUMN clo_plano_vod clo_plano_vod VARCHAR(150) NULL DEFAULT NULL;

ALTER TABLE tab_clientes_iptv ADD COLUMN cip_catalogs VARCHAR(100) NOT NULL AFTER cip_pacotes;

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('editar_dt_degustacao', 'Editar data degustação VOD', '103', '1');

------------------------------------------
------------------------------------------

ALTER TABLE tab_clientes_origens 
ADD COLUMN clo_degustacao_tv INT(4) NULL AFTER clo_pai,
ADD COLUMN clo_plano_tv INT(4) NOT NULL DEFAULT 0 AFTER clo_degustacao_tv;

ALTER TABLE tab_clientes_origens CHANGE COLUMN clo_degustacao_tv clo_degustacao_tv INT(4) NULL DEFAULT 0;

ALTER TABLE tab_clientes_origens ADD COLUMN clo_inicio_degustacao_tv DATETIME NULL AFTER clo_plano_tv;

ALTER TABLE tab_clientes_origens CHANGE COLUMN clo_inicio_degustacao_tv clo_inicio_degustacao_tv DATE NULL DEFAULT NULL;

ALTER TABLE tab_clientes_origens CHANGE COLUMN clo_plano_tv clo_plano_tv VARCHAR(150) NULL DEFAULT NULL;

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('editar_dt_degustacao_tv', 'Editar data degustação TV', '103', '1');

-------------------------------------------
-------------------------------------------

CREATE TABLE tab_integracao_acesso (
  id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  url VARCHAR(255) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  usuario VARCHAR(255) NOT NULL,
  tipo_acesso VARCHAR(150) NOT NULL,
  status INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id));

INSERT INTO tab_integracao_acesso (url, senha, usuario, tipo_acesso, status) 
VALUES ('https://iptv.oletelecom.com.br:8443/drm/services/integration', 'txt32MVP1977', 'admin', 'iptv_drm', '1');

-------------------------------------------
-------------------------------------------
CREATE TABLE tab_catalogos_vods (
  id INT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo INT(4) NOT NULL,
  nome VARCHAR(150) NOT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id));
ALTER TABLE tab_catalogos_vods ADD COLUMN nome_catalogo VARCHAR(45) NOT NULL AFTER nome;
ALTER TABLE tab_catalogos_vods CHANGE COLUMN codigo codigo VARCHAR(150) NOT NULL;
INSERT INTO tab_catalogos_vods (codigo, nome, nome_catalogo, status) VALUES ('5', 'Essencial', 'Nirvana', '1');
INSERT INTO tab_catalogos_vods (codigo, nome, nome_catalogo, status) VALUES ('5,1', 'Premium', 'Nirvana,Vubiquity', '1');

-------------------------------------------
-------------------------------------------

CREATE TABLE tab_catalogos_tvs (
  id INT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo INT(4) NOT NULL,
  nome VARCHAR(150) NOT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id));
ALTER TABLE tab_catalogos_tvs ADD COLUMN nome_catalogo VARCHAR(45) NOT NULL AFTER nome;
ALTER TABLE tab_catalogos_tvs CHANGE COLUMN codigo codigo VARCHAR(150) NOT NULL;
INSERT INTO tab_catalogos_tvs (codigo, nome, nome_catalogo, status) VALUES ('24, 25, 26, 27, 29', 'Premium HD + HBO', 'Premium HD + HBO', '1');
INSERT INTO tab_catalogos_tvs (codigo, nome, nome_catalogo, status) VALUES ('24, 25, 26, 27', 'Premium HD', 'Premium HD', '1');

-------------------------------------------
-------------------------------------------

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('ver_degustacao', 'Ver degustação VOD', '103', '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('ver_degustacao_tv', 'Ver degustação TV', '103', '1');

-------------------------------------------
-------------------------------------------