CREATE TABLE tab_youcast_status (id INT UNSIGNED NOT NULL AUTO_INCREMENT, status VARCHAR(45) NULL, PRIMARY KEY (id));

CREATE TABLE tab_youcast_clientes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente INT NULL,
  contrato INT NULL,
  data DATETIME NULL DEFAULT now(),
  documento VARCHAR(45) NULL,
  email VARCHAR(255) NULL,
  origem INT NULL,
  produto VARCHAR(155) NULL,
  status INT UNSIGNED NULL,
  PRIMARY KEY (id));

ALTER TABLE tab_youcast_clientes ADD INDEX status_idx (status ASC) ;

ALTER TABLE tab_youcast_clientes ADD CONSTRAINT status FOREIGN KEY (status) REFERENCES tab_youcast_status (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE tab_youcast_clientes ADD COLUMN envido_email INT(1) NULL DEFAULT 0 AFTER status;

INSERT INTO tab_youcast_status (id, status) VALUES ('1', 'Ativo');
INSERT INTO tab_youcast_status (id, status) VALUES ('2', 'Inativo');
INSERT INTO tab_youcast_status (id, status) VALUES ('3', 'Pendente');
INSERT INTO tab_youcast_status (id, status) VALUES ('4', 'Cancelado');
INSERT INTO tab_youcast_status (id, status) VALUES ('5', 'Removido');

-----------------------------------------------------------------------

ALTER TABLE tab_youcast_clientes 
  CHANGE COLUMN cliente cliente MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL ,
  ADD INDEX cliente_idx (cliente ASC) ;

ALTER TABLE tab_youcast_clientes 
ADD CONSTRAINT cliente
  FOREIGN KEY (cliente)
  REFERENCES tab_clientes (cli_id)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

-----------------------------------------------------------------------

CREATE TABLE tab_youcast_retorno (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  retorno TEXT NULL,
  cliente INT NULL,
  tipo VARCHAR(45) NULL,
  data DATETIME NULL DEFAULT now(),
  produto VARCHAR(155) NULL,
  PRIMARY KEY (id));

ALTER TABLE tab_youcast_retorno 
  ADD COLUMN ret_status VARCHAR(45) NULL AFTER produto,
  ADD COLUMN ret_msg VARCHAR(150) NULL AFTER ret_status;
