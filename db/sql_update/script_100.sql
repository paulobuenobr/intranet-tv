ALTER TABLE tab_cidades ADD COLUMN cid_ibge int(10) DEFAULT 0 AFTER cid_nome;

-- #################################################

ALTER TABLE tab_estados ADD COLUMN est_ibge int(10) DEFAULT 0 AFTER est_nome;

-- #################################################

ALTER TABLE tab_clientes_origens ADD COLUMN clo_pai int(10) DEFAULT NULL AFTER cos_id;

-- #################################################

ALTER TABLE tab_cobrancas 
    CHANGE COLUMN coa_data_geracao coa_data_geracao DATE NOT NULL ,
    CHANGE COLUMN coa_data_vencimento coa_data_vencimento DATE NOT NULL ;

ALTER TABLE tab_cobrancas ADD COLUMN gerado_nota INT(1) NOT NULL DEFAULT 1 AFTER cot_id;

ALTER TABLE tab_cobrancas CHANGE COLUMN gerado_nota gerado_nota INT(1) UNSIGNED NOT NULL DEFAULT 0 ;

-- #################################################

ALTER TABLE tab_ajustes_financeiros_categorias 
    ADD COLUMN gerar_nfse INT(1) NOT NULL DEFAULT 0 AFTER afc_disponivel,
    ADD COLUMN codigo_servico_id INT(45) UNSIGNED NULL AFTER gerar_nfse,
    ADD COLUMN cnae_id INT(20) UNSIGNED NULL AFTER codigo_servico_id;

ALTER TABLE tab_ajustes_financeiros_categorias
    ADD KEY IX_tab_ajustes_financeiros_categorias_cnae_id (cnae_id),
    ADD KEY IX_tab_ajustes_financeiros_categorias_codigo_servico_id (codigo_servico_id);

ALTER TABLE tab_ajustes_financeiros_categorias ADD COLUMN aliquota VARCHAR(10) NULL DEFAULT NULL AFTER cnae_id;

ALTER TABLE tab_ajustes_financeiros_categorias ADD COLUMN issRetido INT(1) NULL DEFAULT 2 AFTER aliquota;

SET FOREIGN_KEY_CHECKS=0;
    ALTER TABLE tab_ajustes_financeiros_categorias
        ADD CONSTRAINT tab_ajustes_financeiros_categorias_cnae_id FOREIGN KEY (cnae_id) REFERENCES tab_notas_cnae (id);
    ALTER TABLE tab_ajustes_financeiros_categorias 
        ADD CONSTRAINT tab_ajustes_financeiros_categorias_codigo_servico_id FOREIGN KEY (codigo_servico_id) REFERENCES tab_notas_servicos (id);
SET FOREIGN_KEY_CHECKS=1;

-- #################################################

ALTER TABLE tab_planos
    ADD COLUMN gerar_nfse INT(1) NOT NULL DEFAULT 0 AFTER plu_id,
    ADD COLUMN codigo_servico_id INT(45) UNSIGNED NULL AFTER gerar_nfse,
    ADD COLUMN cnae_id INT(20) UNSIGNED NULL AFTER codigo_servico_id;

ALTER TABLE tab_planos ADD KEY IX_tab_planos_cnae_id (cnae_id), ADD KEY IX_tab_planos_servico_id (codigo_servico_id);

ALTER TABLE tab_planos ADD COLUMN aliquota VARCHAR(10) NULL DEFAULT NULL AFTER cnae_id;

ALTER TABLE tab_planos ADD COLUMN issRetido INT(1) NULL DEFAULT 2 AFTER aliquota;

SET FOREIGN_KEY_CHECKS=0;
    ALTER TABLE tab_planos
        ADD CONSTRAINT tab_planos_cnae_id FOREIGN KEY (cnae_id) REFERENCES tab_notas_cnae (id);
    ALTER TABLE tab_planos
        ADD CONSTRAINT tab_planos_codigo_servico_id FOREIGN KEY (codigo_servico_id) REFERENCES tab_notas_servicos (id);
SET FOREIGN_KEY_CHECKS=1;

-- #################################################

ALTER TABLE tab_notas ADD COLUMN protocolo VARCHAR(45) NULL AFTER tomserv_contato_email;

-- #################################################

INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('ver_nt', 'ver Notas NTF-e ', '24', '2');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('ver_nota', 'ver Nota NTF-e', '24', '2');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('cancelar_nfse', 'Cancelar NFS-e', '24', '2');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('enviar_nfse', 'Enviar NFS-e', '24', '2');
INSERT INTO tab_acoes (aco_nome, aco_descricao, mod_id, act_id) VALUES ('gerar_nfse', 'Gerar NFS-e', '24', '2');

-- #################################################

INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) VALUES ('nfse', 'recepcionarLoteRpsCtb', '360', '2023-08-11 14:05:31', '2023-08-11 14:05:31', '2023-08-11 14:05:31', 'n', '0', '0');
INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) VALUES ('nfse', 'consultarLoteRpsCtb', '720', '2023-08-11 14:05:31', '2023-08-11 14:05:31', '2023-08-11 14:05:31', 'n', '0', '0');
INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) VALUES ('nfse', 'recepcionarLoteRpsNaoGeradasCtb', '720', '2023-08-11 14:05:31', '2023-08-11 14:05:31', '2023-08-11 14:05:31', 'n', '0', '0');
INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) VALUES ('nfse', 'emiteNotaSJSC', '720', '2023-10-20 11:50:04', '2023-10-20 11:50:04', '2023-10-20 11:50:04', 'n', '0', '0');
INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) VALUES ('nfse', 'emiteNotasNaoGeradasSJSC', '720', '2023-10-20 11:50:04', '2023-10-20 11:50:04', '2023-10-20 11:50:04', 'n', '0', '0');
INSERT INTO tab_cron (cro_classe, cro_acao, cro_intervalo_minutos, cro_ultima_execucao_real, cro_ultima_execucao_inicio, cro_ultima_execucao_termino, cro_tempo_real, cro_em_execucao, cro_ativo) VALUES ('nfse', 'gerarNaoCanceladosSJSC', '720', '2023-10-20 11:50:04', '2023-10-20 11:50:04', '2023-10-20 11:50:04', 'n', '0', '0');

-- #################################################

ALTER TABLE tab_cidades ADD COLUMN cid_tom INT(10) NULL AFTER cid_ibge;

-- #################################################

ALTER TABLE tab_notas 
    ADD COLUMN link_nfse VARCHAR(1000) NULL AFTER protocolo,
    ADD COLUMN serie_nfse VARCHAR(45) NULL AFTER link_nfse,
    ADD COLUMN data_nfse VARCHAR(45) NULL AFTER serie_nfse;

-- #################################################

ALTER TABLE tab_notas 
    ADD COLUMN lista_itens LONGTEXT NULL AFTER tomserv_contato_email;
