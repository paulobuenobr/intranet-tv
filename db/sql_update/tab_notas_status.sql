CREATE TABLE tab_notas_status (
  id int(2) NOT NULL,
  nome varchar(45) DEFAULT NULL,
  descricao varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO tab_notas_status (id, nome, descricao) VALUES
(1, 'Cancelada', 'Cancelada'),
(2, 'Aguardando', 'Aguardando'),
(3, 'Enviado', 'Enviado'),
(4, 'Emitida', 'Emitida'),
(5, 'Erro', 'Erro');

ALTER TABLE tab_notas_status
  ADD PRIMARY KEY (id);

ALTER TABLE tab_notas_status
  MODIFY id int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;