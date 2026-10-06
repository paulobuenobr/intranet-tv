CREATE TABLE tab_consultor (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    status INT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id));
COMMIT;

ALTER TABLE tab_consultor ADD COLUMN data_cadastro DATETIME NOT NULL DEFAULT now() AFTER status;

INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id) VALUES ('consultor', 'Consultor', '1');

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('adicionar', 'Adicionar', (select mod_id from tab_modulos where mod_nome like 'consultor' LIMIT 1), '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('editar', 'Editar', (select mod_id from tab_modulos where mod_nome like 'consultor' LIMIT 1), '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('ver', 'Ver', (select mod_id from tab_modulos where mod_nome like 'consultor' LIMIT 1), '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('listar', 'Listar', (select mod_id from tab_modulos where mod_nome like 'consultor' LIMIT 1), '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('remover', 'Remover', (select mod_id from tab_modulos where mod_nome like 'consultor' LIMIT 1), '1');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('desbloquear_api', 'Desbloquear API', (select mod_id from tab_modulos where mod_nome like 'clientes_origens' LIMIT 1), '1');

ALTER TABLE tab_clientes ADD COLUMN consultor INT NULL AFTER cls_id;
ALTER TABLE tab_clientes CHANGE COLUMN consultor consultor INT(10) UNSIGNED NULL DEFAULT NULL ;
ALTER TABLE tab_clientes ADD CONSTRAINT tab_clientes_consultor FOREIGN KEY (consultor) REFERENCES tab_consultor (id);

INSERT INTO tab_menus (men_nome, men_modulo, men_acao, men_ordem, men_pai) VALUES ('Consultores', 'consultor', 'listar', '1', '19');





