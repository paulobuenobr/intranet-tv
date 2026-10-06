CREATE TABLE tab_notas_local_emissao (
  id int(11) NOT NULL,
  local varchar(255) DEFAULT NULL,
  uf varchar(4) DEFAULT NULL,
  sigla varchar(45) DEFAULT NULL,
  padrao int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO tab_notas_local_emissao (id, local, uf, sigla, padrao) VALUES
(1, 'Curitiba', 'PR', 'ctb', 0),
(2, 'São José', 'SC', 'sjsc', 1),
(3, 'Florianopolis', 'SC', 'flp', 0);

ALTER TABLE tab_notas_local_emissao ADD PRIMARY KEY (id);

ALTER TABLE tab_notas_local_emissao
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;