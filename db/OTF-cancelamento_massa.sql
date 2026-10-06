INSERT INTO tab_modulos (mod_nome, mod_descricao, mos_id)
VALUES ('cancelamento_massa', 'Cancelamento em Massa', 1);

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id)
VALUES (
	'gerar_cancelamento',
	'Gerar Cancelamento',
	(SELECT mod_id FROM tab_modulos WHERE mod_nome = 'cancelamento_massa' LIMIT 1),
	1
);
