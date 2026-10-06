CREATE TABLE tab_notas_log (
  id int(10) NOT NULL,
  nota int(10) DEFAULT NULL,
  retorno mediumtext DEFAULT NULL,
  cod varchar(20) DEFAULT NULL,
  msg mediumtext DEFAULT NULL,
  data datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE tab_notas_log
  ADD PRIMARY KEY (id),
  ADD KEY IX_tab_notas_log_nota_id (nota);

ALTER TABLE tab_notas_log
  MODIFY id int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE tab_notas_log
  ADD CONSTRAINT tab_notas_log_nota_id FOREIGN KEY (nota) REFERENCES tab_notas (id) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;