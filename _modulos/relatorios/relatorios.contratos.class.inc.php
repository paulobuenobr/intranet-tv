<?

class relatorios_contratos {

  var $bancodedados;
  var $permissoes;

  function __construct($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }

  ######################
  ### Funções Gerais ###
  ######################

  function buscaConsulta() {
    $consulta = "
      SELECT DISTINCT
        tab_contratos.cor_id AS id,
        [campos_retorno]
      FROM
        tab_contratos
		INNER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
        [joins_filtro]
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        tab_contratos.cor_data_geracao";
    return $consulta;
  }

  function buscaCampos() {
    $campos[] = array ("categoria" => "Cliente", "nome" => "ID", "campo" => "cliente_id", "tabela" => "tab_contratos.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Origem", "campo" => "origem_nome", "tabela" => "tab_clientes_origens.clo_nome AS origem_nome, ", "joins" => array("INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Origem &raquo; Status", "campo" => "origem_status", "tabela" => "tab_clientes_origens_status.cos_nome AS origem_status, ", "joins" => array("INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)", "INNER JOIN tab_clientes_origens_status ON (tab_clientes_origens.cos_id = tab_clientes_origens_status.cos_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Nome", "campo" => "cliente_nome", "tabela" => "tab_clientes.cli_nome AS cliente_nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Nome Fantasia", "campo" => "nome_fantasia", "tabela" => "tab_clientes.cli_nome_fantasia AS nome_fantasia, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Contato", "campo" => "cliente_contato", "tabela" => "tab_clientes.cli_contato AS cliente_contato, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Endereço", "campo" => "cliente_endereco", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Condomínio", "campo" => "cliente_condominio", "tabela" => "tab_condominios.con_nome AS cliente_condominio, ", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Condomínio &raquo; Tecnologia", "campo" => "tecnologia", "tabela" => "CONCAT_WS(' / ', tab_condominios_tecnologias.cog_nome, tab_condominios_tecnologias_equipamentos.coq_nome) AS tecnologia, ", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_condominios_tecnologias ON (tab_condominios.cog_id = tab_condominios_tecnologias.cog_id)", "LEFT OUTER JOIN tab_condominios_tecnologias_equipamentos ON (tab_condominios.coq_id = tab_condominios_tecnologias_equipamentos.coq_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Bloco", "campo" => "cliente_bloco", "tabela" => "tab_condominios_blocos.cob_nome AS cliente_bloco, ", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Complemento", "campo" => "cliente_complemento", "tabela" => "SUBSTRING(tab_clientes.cli_complemento, 2) AS cliente_complemento, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Logradouro", "campo" => "cliente_logradouro", "tabela" => "tab_logradouros.log_nome AS cliente_logradouro, ", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))", "LEFT OUTER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Bairro", "campo" => "cliente_bairro", "tabela" => "tab_bairros.bai_nome AS cliente_bairro, ", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))", "LEFT OUTER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)", "LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Cidade", "campo" => "cliente_cidade", "tabela" => "tab_cidades.cid_nome AS cliente_cidade, ", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))", "LEFT OUTER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)", "LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)", "LEFT OUTER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Estado", "campo" => "cliente_estado", "tabela" => "tab_estados.est_sigla AS cliente_estado, ", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))", "LEFT OUTER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)", "LEFT OUTER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)", "LEFT OUTER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)", "LEFT OUTER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "CEP", "campo" => "cliente_cep", "tabela" => "CONCAT(SUBSTRING(tab_ceps.cep_id, 1, 5), '-', SUBSTRING(tab_ceps.cep_id, 6, 3)) AS cliente_cep, ", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Data Cadastro", "campo" => "cliente_data_cadastro", "tabela" => "DATE_FORMAT(tab_clientes.cli_data_cadastro, '%d/%m/%Y') AS cliente_data_cadastro, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Tipo", "campo" => "cliente_tipo", "tabela" => "tab_clientes_tipos.clt_nome AS cliente_tipo, ", "joins" => array("INNER JOIN tab_clientes_tipos ON (tab_clientes.clt_id = tab_clientes_tipos.clt_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "CPF/CNPJ", "campo" => "cliente_cpf_cnpj", "tabela" => "tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Telefone(s)", "campo" => "cliente_telefone", "tabela" => "tab_contratos.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Telefone (Individual)", "campo" => "cliente_telefone_individual", "tabela" => "REPLACE(CONCAT(tab_clientes_contatos_telefones.ccl_ddd, tab_clientes_contatos_telefones.ccl_numero),'-', '') AS cliente_telefone_individual, ", "joins" => array("INNER JOIN tab_clientes_contatos_telefones ON (tab_clientes.cli_id = tab_clientes_contatos_telefones.cli_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "E-mail(s)", "campo" => "cliente_email", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "E-mail (Individual)", "campo" => "cliente_email_individual", "tabela" => "CONCAT(tab_clientes_contatos_emails.ccm_usuario, '@', tab_clientes_contatos_emails.ccm_dominio) AS cliente_email_individual, ", "joins" => array("INNER JOIN tab_clientes_contatos_emails ON (tab_clientes.cli_id = tab_clientes_contatos_emails.cli_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Responsável", "campo" => "cliente_responsavel", "tabela" => "tab_usuarios.usu_nome AS cliente_responsavel, ", "joins" => array("INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)", "LEFT OUTER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Consultor", "campo" => "consultor_nome", "tabela" => "tab_consultor.nome AS consultor_nome, ", "joins" => array("INNER JOIN tab_clientes cli_consultor ON (tab_contratos.cli_id = cli_consultor.cli_id)", "LEFT OUTER JOIN tab_consultor ON (tab_consultor.id = cli_consultor.consultor)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Indicação", "campo" => "cliente_indicacao", "tabela" => "tab_clientes_indicacoes.cld_nome AS cliente_indicacao, ", "joins" => array("LEFT OUTER JOIN tab_clientes_indicacoes ON (tab_clientes.cld_id = tab_clientes_indicacoes.cld_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Status", "campo" => "cliente_status", "tabela" => "tab_clientes_status.cls_nome AS cliente_status, ", "joins" => array("INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "MAC's", "campo" => "cliente_macs", "tabela" => "tab_contratos.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Qtde Chamados (Período)", "campo" => "qtde_chamados", "tabela" => "tab_contratos.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Data Bloqueio Inadimplência", "campo" => "data_bloqueio", "tabela" => "tab_contratos.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Velocidade Máxima", "campo" => "velocidade_maxima", "tabela" => "tab_condominios.con_velocidade_maxima AS velocidade_maxima, ", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Boletos Vencidos", "campo" => "boletos_vencidos", "tabela" => "(SELECT COUNT(*) FROM tab_cobrancas WHERE tab_cobrancas.cli_id = tab_contratos.cli_id AND tab_cobrancas.coa_pai IS NULL AND tab_cobrancas.cot_id = 1 AND tab_cobrancas.coa_data_vencimento < CURDATE()) AS boletos_vencidos, ");
    
    $campos[] = array ("categoria" => "Origem", "nome" => "ERP", "campo" => "erp", "tabela" => "origemErp.clo_erp AS erp, ", "joins" => array("INNER JOIN tab_clientes clienteErp ON (tab_contratos.cli_id = clienteErp.cli_id)", "INNER JOIN tab_clientes_origens origemErp ON (clienteErp.clo_id = origemErp.clo_id)"));

    $campos[] = array ("categoria" => "Cobrança", "nome" => "Formato", "campo" => "cobranca_formato", "tabela" => "tab_clientes.cof_id AS cobranca_formato, ");
    $campos[] = array ("categoria" => "Cobrança", "nome" => "Vencimento", "campo" => "cobranca_vencimento", "tabela" => "tab_clientes.cli_ccd_vencimento AS cobranca_vencimento, ");

    $campos[] = array ("categoria" => "Contrato", "nome" => "ID", "campo" => "contrato_id", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Código", "campo" => "contrato_codigo", "tabela" => "tab_contratos.cor_codigo AS contrato_codigo, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Tipo", "campo" => "contrato_tipo", "tabela" => "tab_contratos_tipos.crt_nome AS contrato_tipo, ", "joins" => array("INNER JOIN tab_contratos_tipos ON (tab_contratos.crt_id = tab_contratos_tipos.crt_id)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Fechamento", "campo" => "contrato_data_geracao", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_geracao, '%d/%m/%Y') AS contrato_data_geracao, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Ativação", "campo" => "contrato_data_ativacao", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS contrato_data_ativacao, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Cancelamento", "campo" => "contrato_data_cancelamento", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_cancelamento,'%d/%m/%Y') AS contrato_data_cancelamento, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Anulação", "campo" => "contrato_data_anulacao", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_anulacao,'%d/%m/%Y') AS contrato_data_anulacao, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Última Alteração", "campo" => "contrato_data_ultima_alteracao", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Visita", "campo" => "contrato_data_visita", "tabela" => "DATE_FORMAT(tab_visitas.vis_data,'%d/%m/%Y') AS contrato_data_visita, ", "joins" => array("LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id AND tab_visitas.via_id <> 4)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Motivo do Cancelamento", "campo" => "contrato_cancelamento_motivo", "tabela" => "tab_cancelamentos_motivos.cam_nome AS contrato_cancelamento_motivo, ", "joins" => array("LEFT OUTER JOIN tab_cancelamentos ON (tab_contratos.cor_id = tab_cancelamentos.cor_id AND tab_cancelamentos.cas_id = 4)", "LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Motivos Anulação", "campo" => "contratos_motivos_anulacao", "tabela" => "tab_contratos_motivos_anulacao.cma_nome AS contratos_motivos_anulacao, ","joins" => array("INNER JOIN tab_contratos_motivos_anulacao ON (tab_contratos.cma_id = tab_contratos_motivos_anulacao.cma_id)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Serviço", "campo" => "contrato_servico", "tabela" => "tab_planos_servicos.pls_nome AS contrato_servico, ", "joins" => array("INNER JOIN tab_planos_servicos ON (tab_contratos.pls_id = tab_planos_servicos.pls_id)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Plano Principal", "campo" => "contrato_plano_principal", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Pontos", "campo" => "contrato_pontos", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Domínios", "campo" => "contrato_dominios", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Multa de Rescisão", "campo" => "contrato_multa", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Prazo de Vigência", "campo" => "contrato_vigencia", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Término Vigência", "campo" => "contrato_termino_vigencia", "tabela" => "DATE_FORMAT(tab_contratos.cor_termino_fidelidade, '%d/%m/%Y') AS contrato_termino_vigencia, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Vendedor", "campo" => "contrato_vendedor", "tabela" => "tab_vendedores.usu_nome AS contrato_vendedor, ", "joins" => array("INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)", "INNER JOIN tab_usuarios tab_vendedores ON (tab_vendas.usu_id = tab_vendedores.usu_id)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Instalador", "campo" => "contrato_instalador", "tabela" => "tab_instaladores.usu_nome AS contrato_instalador, ", "joins" => array("LEFT OUTER JOIN tab_visitas tab_visitas2 ON (tab_contratos.cor_id = tab_visitas2.cor_id)", "LEFT OUTER JOIN tab_usuarios tab_instaladores ON (tab_visitas2.usu_id = tab_instaladores.usu_id)"));
    $campos[] = array ("categoria" => "Contrato", "nome" => "Contrato Publicado", "campo" => "com_contrato", "tabela" => "tab_contratos.cor_codigo AS com_contrato, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Status", "campo" => "contrato_status", "tabela" => "tab_contratos_status.crs_nome AS contrato_status, ", "joins" => array("INNER JOIN tab_contratos_status ON (tab_contratos.crs_id = tab_contratos_status.crs_id)"));

    $campos[] = array ("categoria" => "Plano", "nome" => "Nome", "campo" => "plano_nome", "tabela" => "tab_planos.pla_nome AS plano_nome, ", "joins" => array("INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)", "INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)", "INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)"));
    $campos[] = array ("categoria" => "Plano", "nome" => "Data Ativação", "campo" => "plano_data_ativacao", "tabela" => "DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS plano_data_ativacao, ", "joins" => array("INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)", "INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)"));
    $campos[] = array ("categoria" => "Plano", "nome" => "Data Cancelamento", "campo" => "plano_data_cancelamento", "tabela" => "DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%d/%m/%Y') AS plano_data_cancelamento, ", "joins" => array("INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)", "INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)"));
    
    $campos[] = array ("categoria" => "Instalação", "nome" => "Valor Original", "campo" => "contrato_instalacao_valor_original", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Instalação", "nome" => "Desconto (Com Validade)", "campo" => "contrato_instalacao_desconto_cv", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Instalação", "nome" => "Desconto (Com Validade)", "campo" => "contrato_instalacao_desconto_cv_descricao", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Instalação", "nome" => "Desconto (Sem Validade)", "campo" => "contrato_instalacao_desconto_sv", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Instalação", "nome" => "Desconto (Sem Validade)", "campo" => "contrato_instalacao_desconto_sv_descricao", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Instalação", "nome" => "Valor Final", "campo" => "contrato_instalacao_valor_final", "tabela" => "(SELECT (PERIOD_DIFF(DATE_FORMAT(ajf_competencia_termino, '%Y%m'), DATE_FORMAT(ajf_competencia_inicio, '%Y%m')) + 1) * ajf_valor FROM tab_ajustes_financeiros WHERE tab_ajustes_financeiros.cor_id = tab_contratos.cor_id AND tab_ajustes_financeiros.afc_id = 19 LIMIT 1) AS contrato_instalacao_valor_final, ");

    $campos[] = array ("categoria" => "Mensalidade", "nome" => "Valor Original", "campo" => "contrato_mensalidade_valor_original", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Mensalidade", "nome" => "Desconto (Com Validade)", "campo" => "contrato_mensalidade_desconto_cv", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Mensalidade", "nome" => "Desconto (Com Validade)", "campo" => "contrato_mensalidade_desconto_cv_descricao", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Mensalidade", "nome" => "Desconto (Sem Validade)", "campo" => "contrato_mensalidade_desconto_sv", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Mensalidade", "nome" => "Desconto (Sem Validade)", "campo" => "contrato_mensalidade_desconto_sv_descricao", "tabela" => "tab_contratos.cor_parametros AS contrato_parametros, ");
    $campos[] = array ("categoria" => "Mensalidade", "nome" => "Valor Final", "campo" => "contrato_mensalidade_valor_final", "tabela" => "tab_contratos.cor_id AS contrato_id, ");

    for ($i = 0; $i < sizeof($campos); $i++) {
      $retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"], "joins" => $campos[$i]["joins"]);
    }
    return $retorno;
  }

  function buscaFiltros() {
    $filtros[] = array ("tipo" => "selectMultiple", "nome" => "Origem", "name" => "origem[]", "funcao" => "buscaOrigens()", "where" => "tab_clientes.clo_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Origem &raquo; Status", "name" => "origem_status", "funcao" => "buscaOrigensStatus()", "where" => "tab_clientes_origens.cos_id", "joins" => array("INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)"));
    $filtros[] = array ("tipo" => "selectText", "nome" => "Origem &raquo; ERP", "name" => "erp_nome", "funcao" => "buscaOrigensERP()", "where" => "tab_clientes_origens.clo_erp");
    $filtros[] = array ("tipo" => "select", "nome" => "Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_clientes.con_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Condomínio &raquo; Tecnologia", "name" => "tecnologia", "funcao" => "buscaCondominioTecnologias()", "where" => "tab_condominios.cog_id", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)"));
    
    $filtros[] = array ("tipo" => "select", "nome" => "Estado", "name" => "estado", "funcao" => "buscaEstados()", "onchange" => "buscaCidade(this.value)", "where" => "tab_ceps.est_id", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))"));
    $filtros[] = array ("tipo" => "select", "nome" => "Cidade", "name" => "cidade", "funcao" => "buscaCidades()", "where" => "tab_ceps.cid_id", "joins" => array("LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)", "LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)", "LEFT OUTER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))"));
    
    $filtros[] = array ("tipo" => "select", "nome" => "Responsável", "name" => "responsavel", "funcao" => "buscaClienteResponsaveis()", "where" => "tab_vendas.usu_id", "joins" => array("INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)"));
    $filtros[] = array ("tipo" => "select", "nome" => "Consultor", "name" => "consultor", "funcao" => "buscaConsultores()", "where" => "tab_clientes.consultor");
    $filtros[] = array ("tipo" => "selectAoLado", "nome" => "Cobrança", "name1" => "cobranca_formato", "funcao1" => "buscaCobrancaFormatos()", "where1" => "tab_clientes.cof_id", "name2" => "cobranca_vencimento", "funcao2" => "buscaCobrancaVencimentos()", "where2" => "tab_clientes.cli_ccd_vencimento");
    $filtros[] = array ("tipo" => "select", "nome" => "Serviço", "name" => "servico", "funcao" => "buscaContratoServicos()", "onchange" => "buscaPlanos(this.value)", "where" => "tab_contratos.pls_id");
    $filtros[] = array ("tipo" => "selectMultiple", "nome" => "Plano", "name" => "plano[]", "funcao" => "buscaPlanos()", "where" => "tab_assinaturas.pla_id", "joins" => array("INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)", "INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)"));
    $filtros[] = array ("tipo" => "checklist", "nome" => "Tipo", "name" => "tipo", "funcao" => "buscaContratoTipos()", "where" => "tab_contratos.crt_id", "selecionados" => array(11));
    $filtros[] = array ("tipo" => "select", "nome" => "Status", "name" => "status", "funcao" => "buscaContratoStatus()", "where" => "tab_contratos.crs_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Motivos Anulação", "name" => "motivos_anulacao", "funcao" => "buscaContratoMotivosAnulacao()", "where" => "tab_contratos.cma_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Visita &raquo; Instalador", "name" => "visitas_instalador", "funcao" => "buscaInstaladores()", "where" => "tab_visitas.usu_id", "joins" => array("LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)"));
    $filtros[] = array ("tipo" => "select", "nome" => "Visita &raquo; Status", "name" => "visitas_status", "funcao" => "buscaVisitasStatus()", "where" => "tab_visitas.via_id", "joins" => array("LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)"));
    $filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Tipo", "name" => "cliente_tipo", "funcao" => "buscaClientesTipos()", "where" => "tab_clientes.clt_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Indicação", "name" => "cliente_indicacao", "funcao" => "buscaClientesIndicacoes()", "where" => "tab_clientes.cld_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Cliente &raquo; Status", "name" => "cliente_status", "funcao" => "buscaClientesStatus()", "where" => "tab_clientes.cls_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Vendedor", "name" => "cliente_vendedor", "funcao" => "buscaVendedores()", "where" => "tab_vendas.usu_id", "joins" => array("INNER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)"));
    $filtros[] = array ("tipo" => "extra", "nome" => "Extra", "name" => "crs_id", "funcao" => "", "where" => "tab_contratos.crs_id");
    $filtros[] = array ("tipo" => "input", "nome" => "Bloqueio Inadimplência (dias)", "name" => "bloqueio_dias", "funcao" => "", "where" => "", "formatacao" => "formataNumero(this, event)", "joins" => array("INNER JOIN tab_servicos_ajustes ON (tab_contratos.cli_id = tab_servicos_ajustes.cli_id AND tab_servicos_ajustes.sac_id = 1 AND tab_servicos_ajustes.sas_id = 5)"));
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Geração", "name1" => "data_geracao_inicio", "name2" => "data_geracao_termino", "where" => "tab_contratos.cor_data_geracao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Ativação", "name1" => "data_ativacao_inicio", "name2" => "data_ativacao_termino", "where" => "tab_contratos.cor_data_ativacao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Cancelamento", "name1" => "data_cancelamento_inicio", "name2" => "data_cancelamento_termino", "where" => "tab_contratos.cor_data_cancelamento");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Anulação", "name1" => "data_anulacao_inicio", "name2" => "data_anulacao_termino", "where" => "tab_contratos.cor_data_anulacao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Visita", "name1" => "visita_inicio", "name2" => "visita_termino", "where" => "tab_visitas.via_id IN (2, 3) AND tab_visitas.vis_data", "joins" => array("LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)"));
    $periodos[] = array ("tipo" => "periodo", "nome" => "Chamados &raquo; Abertura", "name1" => "chamados_abertura_inicio", "name2" => "chamados_abertura_termino", "where" => "");
    return $periodos;
  }

  function buscaIntervalos() {
    $intervalos[] = array ("tipo" => "quantidade", "nome" => "Prazo de Vigência", "name1" => "vigencia_inferior", "name2" => "vigencia_superior", "where" => "1");
    $intervalos[] = array ("tipo" => "velocidade", "nome" => "Velocidade Máxima", "name1" => "velocidade_maxima_inferior", "name2" => "velocidade_maxima_superior", "where" => "con_velocidade_maxima", "joins" => array("LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)"));
   return $intervalos;
  }

  ###################
  ### Busca Dados ###
  ###################

  function formataDinheiro($valor, $moeda = 'R$') {
    if ($valor) {
      $valor = preg_replace("/[^0-9]/", "", intval($valor));
      $valor = number_format($valor / 100, 2, ",", ".");
      if ($moeda) $valor = $moeda." ".$valor;
    }
    return $valor;
  }

  function formataTamanho($tamanho) {
    if (!$tamanho) return "-";
    $unidade = floor(log($tamanho, 1024));
    if (round($tamanho / pow(1024, $unidade)) >= 1000) $unidade++;
    return number_format($tamanho / pow(1024, $unidade), 1, ",", ".")." ".$this->buscaUnidades("bps", $unidade);
  }

  function buscaUnidades($unidade = "", $valor = null) {
    $unidades["1"] = "K".$unidade;
    $unidades["2"] = "M".$unidade;
    $unidades["3"] = "G".$unidade;
    $unidades["4"] = "T".$unidade;
    return $valor ? $unidades[$valor] : $unidade;
  }

  function retornaValorDesconto($valor) {
    if ($valor) {
      if ($valor <= 1 && $valor > 0) {
        return ($valor * 100) . "%";
      } else {
        return $this->formataDinheiro($valor);
      }
    }
  }

  function calculaValorFinal($original, $cv, $sv) {
    if ($cv <= 1 && $cv > 0) {
      $cv = $original * $cv;
    }
    if ($sv <= 1 && $sv > 0) {
      $sv = $original * $sv;
    }
    return ($original - $cv - $sv > 0) ? $original - $cv - $sv : 0;
  }

  function buscaMACs($contrato_id) {
    $query = "
      SELECT DISTINCT
        clc_mac_address AS mac
      FROM
        tab_clientes_comodatos
      WHERE
        ccs_id IN (1, 2) AND
		clc_mac_address IS NOT NULL AND
		cor_id = {$contrato_id}";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $macs[] = $dados["mac"];
    }
    return @implode(", ", $macs);
  }
   
  function buscaEstados() {
    $query = "
      SELECT DISTINCT
        tab_estados.est_id AS id,
        tab_estados.est_sigla AS nome
      FROM
        tab_estados
      ORDER BY
        tab_estados.est_sigla";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cidades[$dados["id"]] = $dados["nome"];
    }
    return $cidades;
  }

  function buscaCidades() {
    $query = "
      SELECT DISTINCT
        tab_cidades.cid_id AS id,
        CONCAT(tab_cidades.cid_nome, ' / ', tab_estados.est_sigla) AS nome
      FROM
        tab_cidades
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      ORDER BY
        tab_estados.est_sigla,
        tab_cidades.cid_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cidades[$dados["id"]] = $dados["nome"];
    }
    return $cidades;
  }

  function buscaOrigens() {
    $query = "
      SELECT
        clo_id AS id,
        clo_nome AS nome
      FROM
        tab_clientes_origens
      WHERE
        1".($_SESSION["origem"] ? " AND
		clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[$dados["id"]] = $dados["nome"];
    }
    return $origens;
  }

	function buscaOrigensStatus() {
		
		$origens_status = array();

		$query = $this->bancodedados->query("
			SELECT
				cos_id AS id,
				cos_nome AS nome
			FROM
				tab_clientes_origens_status
			WHERE
				1
			ORDER BY
				nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens_status[$dados["id"]] = $dados["nome"];
		}
		
		return $origens_status;
		
	}

  function buscaOrigensERP () {
    $origens_erp = array();

		$query = $this->bancodedados->query("
			SELECT
        DISTINCT
        clo_erp AS id,
          clo_erp AS nome
      FROM
        tab_clientes_origens
      WHERE
        clo_erp IS NOT nULL
          AND clo_erp <> ''
      ORDER BY
        nome");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$origens_erp[$dados["id"]] = $dados["nome"];
		}
		
		return $origens_erp;

  }
		
  function buscaCondominios() {
    $query = "
      SELECT
        con_id AS id,
        con_nome AS nome
      FROM
        tab_condominios
      WHERE
        cos_id IS NOT NULL
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }

  function buscaCondominioTecnologias() {
    $query = "
      SELECT
        cog_id AS id,
        cog_nome AS nome
      FROM
        tab_condominios_tecnologias
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tecnologias["{$dados["id"]},"] = $dados["nome"];
      $equipamentos = $this->buscaCondominioTecnologiasEquipamentos($dados);
      if ($equipamentos) {
        $tecnologias = array_merge($tecnologias, $equipamentos);
      }
    }
    return $tecnologias;
  }

  function buscaCondominioTecnologiasEquipamentos($dados_tecnologia) {
    $query = "
      SELECT
        coq_id AS id,
        coq_nome AS nome
      FROM
        tab_condominios_tecnologias_equipamentos
      WHERE
        cog_id = {$dados_tecnologia["id"]}
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tecnologias["{$dados_tecnologia["id"]},{$dados["id"]}"] = "{$dados_tecnologia["nome"]} » {$dados["nome"]}";
    }
    return $tecnologias;
  }

  function buscaConsultores() {
    
    $query = "SELECT tab_consultor.* FROM tab_clientes INNER JOIN tab_consultor ON tab_clientes.consultor = tab_consultor.id";
    $query = $this->bancodedados->query($query);
    $consultores = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $consultores[$dados["id"]] = $dados["nome"];
      }
      
    return $consultores;
  }

  function buscaClienteResponsaveis() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
        INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
        INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
        INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
      WHERE
        tab_acoes.aco_nome = 'assumir_responsabilidade' AND
        tab_modulos.mod_nome = 'clientes_responsabilidades' AND
        tab_usuarios.uss_id = 1".($_SESSION["origem"] ? " AND
		tab_usuarios.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }

  function buscaClienteTelefones($id) {
    $query = "
      SELECT
        ccl_ramal AS ramal,
        CONCAT('(',ccl_ddd,') ',ccl_numero) AS telefone
      FROM
        tab_clientes_contatos_telefones
      WHERE
        cli_id = $id AND
		ccf_id > 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $telefone = $dados["telefone"];
      if ($dados["ramal"]) $telefone .= " (Ramal {$dados["ramal"]})";
      $lista[] = $telefone;
    }
    return implode(" / ", $lista);
  }

  function buscaClienteEmails($cliente_id) {
    $query = "
      SELECT
        CONCAT(ccm_usuario, '@', ccm_dominio) AS email
      FROM
        tab_clientes_contatos_emails
      WHERE
      	cli_id = $cliente_id
      UNION SELECT
        CONCAT(cle_usuario, '@', dom_dominio) AS email
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        tab_clientes_emails.ces_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados["email"];
    }
    return implode(", ", $lista);
  }

  function buscaContratoStatus() {
    $query = "
      SELECT
        crs_id AS id,
        crs_nome AS nome
      FROM
        tab_contratos_status
      WHERE
        crs_id > 5
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $contratos_status[$dados["id"]] = $dados["nome"];
    }
    $contratos_status["-1"] = "Em Aberto";
    $contratos_status["-2"] = "Ativo";
    asort($contratos_status);
    return $contratos_status;
  }

  function buscaClientesTipos() {
    $query = "
      SELECT
        clt_id AS id,
        clt_nome AS nome
      FROM
        tab_clientes_tipos
      ORDER BY
        clt_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaClientesStatus() {
    $query = "
      SELECT
        cls_id AS id,
        cls_nome AS nome
      FROM
        tab_clientes_status
      ORDER BY
        cls_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $statuss[$dados["id"]] = $dados["nome"];
    }
    return $statuss;
  }
  
function buscaContratoMotivosAnulacao() {
		$query = "
			SELECT
				cma_id AS id,
				cma_nome AS nome
			FROM
				tab_contratos_motivos_anulacao
			ORDER BY
				nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			 $motivos_anulacao[$dados["id"]] = $dados["nome"];
		}
		return $motivos_anulacao;
	}

  function buscaClientesIndicacoes() {
    $query = "
      SELECT
        cld_id AS id,
        cld_nome AS nome
      FROM
        tab_clientes_indicacoes
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $indicacoes[$dados["id"]] = $dados["nome"];
    }
    return $indicacoes;
  }

  function buscaVendedores() {
    $query = "
      SELECT DISTINCT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN tab_vendas ON (tab_usuarios.usu_id = tab_vendas.usu_id)
	  WHERE
		1".($_SESSION["origem"] ? " AND
		clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $vendedores[$dados["id"]] = $dados["nome"];
    }
    return $vendedores;
  }

  function buscaContratoServicos() {
    $query = "
      SELECT
        pls_id AS id,
        pls_nome AS nome
      FROM
        tab_planos_servicos";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $contrato_servicos[$dados["id"]] = $dados["nome"];
    }
    return $contrato_servicos;
  }
  
  function buscaPlanos($servico_id = null) {
		$servico_id = $_POST["servico"] ? $_POST["servico"] : $servico_id;
		if ($servico_id) {
	    $query = "
	      SELECT
	        pla_id AS id,
	        pla_nome AS nome
	      FROM
	        tab_planos
	      WHERE
	        pls_id = $servico_id";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      $planos[$dados["id"]] = $dados["nome"];
	    }
	    return $planos;
		}
  }

  function buscaCidade($estado_id = null) {
        $estado_id = $_POST["estado"] ? $_POST["estado"] : $estado_id;
        if ($estado_id) {
        $query = "
            SELECT DISTINCT
              tab_cidades.cid_id AS id,
              CONCAT(tab_cidades.cid_nome, ' / ', tab_estados.est_sigla) AS nome
            FROM
              tab_cidades
              INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
            WHERE tab_cidades.est_id = {$estado_id}
            ORDER BY
              tab_estados.est_sigla,
              tab_cidades.cid_nome";
        $query = $this->bancodedados->query($query);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $cidade[$dados["id"]] = $dados["nome"];
        }
            return $cidade;
        }
  }

  function buscaContratoTipos() {
    $query = "
      SELECT
        crt_id AS id,
        crt_nome AS nome
      FROM
        tab_contratos_tipos
      WHERE
        crt_id > 10";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $contrato_tipos[$dados["id"]] = $dados["nome"];
    }
    return $contrato_tipos;
  }

  function buscaVisitasStatus() {
    $query = "
      SELECT
        via_id AS id,
        via_nome AS nome
      FROM
        tab_visitas_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $visitas_status[$dados["id"]] = $dados["nome"];
    }
    return $visitas_status;
  }

  function buscaInstaladores() {
    $query = "
      SELECT
        tab_usuarios.usu_id AS id,
        tab_usuarios.usu_nome AS nome
      FROM
        tab_usuarios
        INNER JOIN tab_visitas ON (tab_usuarios.usu_id = tab_visitas.usu_id)
	  WHERE
		1".($_SESSION["origem"] ? " AND
		clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $usuarios[$dados["id"]] = $dados["nome"];
    }
    return $usuarios;
  }

  function buscaContratoInstalacaoValorOriginal($contrato_parametros) {
    if ($contrato_parametros["contrato_versao"] >= 5 || substr($contrato_parametros["contrato_versao"], 0, 6) == "sodisa") {
      $valor = $contrato_parametros["tabela_valor_original_instalacao"];
    } else  {
      $valor = ($contrato_parametros["cobranca_instalacao"]) ? $contrato_parametros["cobranca_instalacao"] : $contrato_parametros["instalacao_valor"];
    }
    return $valor;
  }

  function buscaContratoInstalacaoDescontos($contrato_parametros, $validade = "") {
    if ($contrato_parametros["contrato_versao"] >= 5 || substr($contrato_parametros["contrato_versao"], 0, 6) == "sodisa") {
      foreach ($contrato_parametros["contrato_descontos"] as $desconto) {
        if ($desconto["instalacao"] && ($validade == "" || ($validade == "sv" && $desconto["validade"] == "Indeterminada") || ($validade == "cv" && $desconto["validade"] != "Indeterminada"))) {
          $descontos[] = ($desconto["instalacao"] <= 1) ? $desconto["instalacao"] * $contrato_parametros["tabela_valor_original_instalacao"] : $desconto["instalacao"];
        }
      }
      $total = @array_sum($descontos);
      return ($total > $contrato_parametros["tabela_valor_original_instalacao"]) ? $contrato_parametros["tabela_valor_original_instalacao"] : $total;
    } else {
      for ($i=0;$i<sizeof($contrato_parametros["itens_descontos"]);$i++) {
        if ($contrato_parametros["itens_descontos"][$i]["instalacao"] && ($validade == "" || ($validade == "sv" && $contrato_parametros["itens_descontos"][$i]["prazo"] == "Indeterminado") || ($validade == "cv" && $contrato_parametros["itens_descontos"][$i]["prazo"] != "Indeterminado"))) $descontos[] = $contrato_parametros["itens_descontos"][$i]["instalacao"];
      }
      // Yadata
      if ($contrato_parametros["contrato_versao"] == 2) unset ($descontos);
      return @array_sum($descontos);
    }
  }

  function buscaContratoInstalacaoDescontosDescricao($contrato_parametros, $validade = "") {
    if ($contrato_parametros["contrato_versao"] >= 5 || substr($contrato_parametros["contrato_versao"], 0, 6) == "sodisa" || substr($contrato_parametros["contrato_versao"], 0, 6) == "sodisa") {
      foreach ($contrato_parametros["contrato_descontos"] as $desconto) {
        if ($desconto["instalacao"] && ($validade == "" || ($validade == "sv" && substr($desconto["validade"], 0, 5) == "Indet") || ($validade == "cv" && substr($desconto["validade"], 0, 5) != "Indet"))) {
          $descontos[] = $desconto["desconto_nome"]." (".$desconto["desconto_tipo_nome"]."): ".$this->formataDinheiro($desconto["instalacao"])." (".$desconto["validade"].")";
        }
      }
      return @implode(", ",$descontos);
    } else {
      for ($i=0;$i<sizeof($contrato_parametros["itens_descontos"]);$i++) if ($contrato_parametros["itens_descontos"][$i]["instalacao"] && ($validade == "" || ($validade == "sv" && $contrato_parametros["itens_descontos"][$i]["prazo"] == "Indeterminado") || ($validade == "cv" && $contrato_parametros["itens_descontos"][$i]["prazo"] != "Indeterminado"))) $descontos[] = $contrato_parametros["itens_descontos"][$i]["tipo"] . ": " . $this->formataDinheiro($contrato_parametros["itens_descontos"][$i]["instalacao"]) . " (" . $contrato_parametros["itens_descontos"][$i]["prazo"] . ")";
      // Yadata
      if ($contrato_parametros["contrato_versao"] == 2) unset ($descontos);
      return @implode(", ",$descontos);
    }
  }

  function buscaContratoMensalidadeValorOriginal($contrato_id) {
    $query = "
      SELECT
        SUM(ass_mensalidade) AS mensalidade
      FROM
        tab_assinaturas
        INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
      WHERE
        cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["mensalidade"];
    }
  }

  function buscaContratoMensalidadeDescontos($contrato_parametros, $validade = "") {
    if ($contrato_parametros["contrato_versao"] >= 5 || substr($contrato_parametros["contrato_versao"], 0, 6) == "sodisa") {
      foreach ($contrato_parametros["contrato_descontos"] as $desconto) {
        if ($desconto["mensalidade"] && ($validade == "" || ($validade == "sv" && substr($desconto["validade"], 0, 5) == "Indet") || ($validade == "cv" && substr($desconto["validade"], 0, 5) != "Indet"))) {
          $descontos[] = $desconto["mensalidade"];
        }
      }
      return @array_sum($descontos);
    } else {
      for ($i=0;$i<sizeof($contrato_parametros["itens_descontos"]);$i++) if ($contrato_parametros["itens_descontos"][$i]["mensalidade"] && ($validade == "" || ($validade == "sv" && $contrato_parametros["itens_descontos"][$i]["prazo"] == "Indeterminado") || ($validade == "cv" && $contrato_parametros["itens_descontos"][$i]["prazo"] != "Indeterminado"))) $descontos[] = $contrato_parametros["itens_descontos"][$i]["mensalidade"];
      // Yadata
      if ($contrato_parametros["contrato_versao"] == 2) unset ($descontos);
      return @array_sum($descontos);
    }
  }

  function buscaContratoMensalidadeDescontosDescricao($contrato_parametros, $validade = "") {
    if ($contrato_parametros["contrato_versao"] >= 5 || substr($contrato_parametros["contrato_versao"], 0, 6) == "sodisa") {
      foreach ($contrato_parametros["contrato_descontos"] as $desconto) {
        if ($desconto["mensalidade"] && ($validade == "" || ($validade == "sv" && substr($desconto["validade"], 0, 5) == "Indet") || ($validade == "cv" && substr($desconto["validade"], 0, 5) != "Indet"))) {
          $descontos[] = $desconto["desconto_nome"]." (".$desconto["desconto_tipo_nome"]."): ".$this->formataDinheiro($desconto["mensalidade"])." (".$desconto["validade"].")";
        }
      }
      return @implode(", ", $descontos);
    } else {
      for ($i=0;$i<sizeof($contrato_parametros["itens_descontos"]);$i++) if ($contrato_parametros["itens_descontos"][$i]["mensalidade"] && ($validade == "" || ($validade == "sv" && $contrato_parametros["itens_descontos"][$i]["prazo"] == "Indeterminado") || ($validade == "cv" && $contrato_parametros["itens_descontos"][$i]["prazo"] != "Indeterminado"))) $descontos[] = $contrato_parametros["itens_descontos"][$i]["tipo"] . ": " . $this->formataDinheiro($contrato_parametros["itens_descontos"][$i]["mensalidade"]) . " (" . $contrato_parametros["itens_descontos"][$i]["prazo"] . ")";
      // Yadata
      if ($contrato_parametros["contrato_versao"] == 2) unset ($descontos);
      return @implode(", ", $descontos);
    }
  }

  function buscaContratoPontos($contrato_id) {
    $query = "
      SELECT
        COUNT(tab_assinaturas.ass_id) AS pontos
      FROM
        rel_ass_cor_estacontido
        INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
      WHERE
        rel_ass_cor_estacontido.cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["pontos"];
    }
  }

  function buscaContratoDominios($contrato_id) {
    $query = "
      SELECT
        COUNT(tab_dominios.dom_id) AS dominios
      FROM
        rel_ass_cor_estacontido
        INNER JOIN tab_dominios ON (rel_ass_cor_estacontido.ass_id = tab_dominios.ass_id)
      WHERE
        rel_ass_cor_estacontido.cor_id = $contrato_id
      GROUP BY
        tab_dominios.dom_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["dominios"];
    }
  }

  function buscaContratoMulta($contrato_id) {
    $query = "
      SELECT
        cor_multa AS multa
      FROM
        tab_contratos
      WHERE
        tab_contratos.cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["multa"];
    }
  }

  function buscaContratoVigencia($contrato_id) {
    $query = "
      SELECT
        cor_multa AS multa,
        cor_parametros AS parametros
      FROM
        tab_contratos
      WHERE
        tab_contratos.cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["multa"]) {
        $dados["parametros"] = unserialize($dados["parametros"]);
        return $dados["parametros"]["contrato_prazo_vigencia"] ? $dados["parametros"]["contrato_prazo_vigencia"]." meses" : "";
      }
    }
  }

  function buscaCobrancaVencimentos() {
    $vencimentos["5"] = "Dia 05";
    $vencimentos["10"] = "Dia 10";
    $vencimentos["20"] = "Dia 20";
    return $vencimentos;
  }

  function buscaCobrancaFormatos($status = "1") {
    $query = "
      SELECT
        cof_id AS id,
        cof_nome AS nome
      FROM
        tab_cobrancas_formatos
      WHERE
        cfs_id LIKE '$status'
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $formatos[$dados["id"]] = $dados["nome"];
    }
    return $formatos;
  }
  
  function buscaDataBloqueio($cliente_id) {
    $query = "
      SELECT
        DATE_FORMAT(saj_inicio, '%d/%m/%Y') AS data
      FROM
        tab_servicos_ajustes
      WHERE
        cli_id = $cliente_id AND
        sac_id = 1 AND
        sas_id = 5
      LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["data"];
    }
  }
  
  function buscaChamados($cliente_id, $abertura_inicio, $abertura_termino) {
    $abertura_inicio = $abertura_inicio ? substr($abertura_inicio, 6, 4)."-".substr($abertura_inicio, 3, 2)."-".substr($abertura_inicio, 0, 2) : "";
    $abertura_termino = $abertura_termino ? substr($abertura_termino, 6, 4)."-".substr($abertura_termino, 3, 2)."-".substr($abertura_termino, 0, 2) : "";
    return $this->buscaChamadosClientes($cliente_id, $abertura_inicio, $abertura_termino);
  }

  function buscaChamadosClientes($cliente_id, $abertura_inicio, $abertura_termino) {
    $where[] = "tab_chamados.cli_id = $cliente_id";
    if ($abertura_inicio) $where[] = "DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%Y-%m-%d') >= '$abertura_inicio'";
    if ($abertura_termino) $where[] = "DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%Y-%m-%d') <= '$abertura_termino'";
    $where = implode(" AND\n        ", $where);
    $query = "
      SELECT
        COUNT(DISTINCT tab_chamados.cha_id) AS chamados
      FROM
        tab_chamados
      WHERE
        $where";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["chamados"];
    } else {
      return 0;
    }
  }

  function buscaPlanoPrincipal($contrato_id, $servico_id = null) {
    $query = "
      SELECT
        tab_planos.pla_nome AS plano
      FROM
        tab_planos
        INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
        INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
      WHERE
        rel_ass_cor_estacontido.cor_id = $contrato_id AND
        tab_assinaturas.ass_pai IS NULL".($servico_id ? " AND
        tab_planos.pls_id = $servico_id" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["plano"] = trim(preg_replace('/\([^\)]*\)/', '', $dados["plano"]));
      $dados["plano"] = trim(preg_replace('/\[[^\]]*\]/', '', $dados["plano"]));
      $dados["plano"] = trim(str_replace(array("Acesso Internet", "Pós Venda", "Pos Venda", "Ponto Principal", "Corujão", "Olé Banda Larga", "Olé Total", "Ponto Adicional", "Super Condomínio"), "", $dados["plano"]));
      return $dados["plano"];
    }
  }

  function buscaContratoMensalidadeValorFinal($contrato_id) {
    $query = "
      SELECT
        tab_assinaturas.ass_id AS id,
        tab_assinaturas.ass_mensalidade AS mensalidade
      FROM
        tab_assinaturas
        INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
      WHERE
        rel_ass_cor_estacontido.cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $mensalidade_final += $dados["mensalidade"] - $this->buscaDescontos($dados["id"], $dados["mensalidade"]);
    }
    return $mensalidade_final;
  }

  function buscaDescontos($assinatura_id, $mensalidade) {
    $query = "
      SELECT
        dec_mensalidade AS mensalidade
      FROM
        tab_descontos_concessoes
      WHERE
        ass_id = $assinatura_id AND
        dec_mensalidade > 0 AND
        (dec_validade IS NULL OR dec_validade >= 12)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $descontos += $dados["mensalidade"] > 1 ? $dados["mensalidade"] : $dados["mensalidade"] * $mensalidade;
    }
    return $descontos > $mensalidade ? $mensalidade : $descontos;
  }

  function buscaEnderecoCliente($cliente_id) {
    $query = "
      SELECT
        tab_condominios.con_nome AS condominio_nome,
        tab_condominios_blocos.cob_nome AS bloco_nome,
        tab_estados.est_nome AS estado,
        tab_estados.est_sigla AS estado_sigla,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro,
        tab_clientes.cli_numero AS numero,
        tab_clientes.cli_complemento AS complemento
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        tab_clientes.cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["complemento"]) {
        $dados["complemento_tipo_id"] = $dados["complemento"][0];
        $dados["complemento_tipo_nome"] = $this->buscaComplementoTipos($dados["complemento_tipo_id"], true);
        $dados["complemento_valor"] = substr($dados["complemento"], 1, strlen($dados["complemento"]));
        if ($dados["complemento_tipo_id"] != "o") {
          $dados["complemento"] = $dados["complemento_tipo_nome"]." ".$dados["complemento_valor"];
        } else {
          $dados["complemento"] = $dados["complemento_valor"];
        }
      }
      return $this->geraEnderecoFormatado($dados);
    }
  }
  
  function buscaComplementoTipos($tipo_id = null, $abreviado = false) {
    $tipos["a"] = $abreviado ? "Ap" : "Apartamento";
    $tipos["l"] = $abreviado ? "Lj" : "Loja";
    $tipos["s"] = $abreviado ? "Sl" : "Sala";
    $tipos["c"] = "Casa";
    $tipos["o"] = "Outro";
    return $tipo_id ? ($tipo_id != "o" ? $tipos[$tipo_id]." " : "") : $tipos;
  }

  function geraEnderecoFormatado($endereco) {
    if ($endereco["condominio_nome"]) {
      $endereco_formatado = $endereco["condominio_nome"];
      if ($endereco["complemento"]) {
        $endereco_formatado .= ", ".$endereco["complemento"];
        if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único") $endereco_formatado .= " / ".$endereco["bloco_nome"];
      } else {
        if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único") $endereco_formatado .= ", Bloco ".$endereco["bloco_nome"];
      }
    } else {
      if ($endereco["logradouro"]) $endereco_formatado = $endereco["logradouro"];
      if ($endereco["numero"]) $endereco_formatado .= ", " . $endereco["numero"];
      if ($endereco["complemento"]) $endereco_formatado .= ", " . $endereco["complemento"];
      if ($endereco["bairro"]) $endereco_formatado .= ", " . $endereco["bairro"];
      if ($endereco["cidade"]) $endereco_formatado .= ", " . $endereco["cidade"];
      if ($endereco["estado_sigla"]) $endereco_formatado .= ", " . $endereco["estado_sigla"];
    }
    return $endereco_formatado;
  }
  
  function buscaDataUltimaAlteracao($contrato_id) {
  	$query = "
  	  SELECT
  	    DATE_FORMAT(MAX(cor_data_ativacao), '%d/%m/%Y') AS data_ultima_alteracao
  	  FROM
  	    tab_contratos
  	  WHERE
  	    cor_pai = $contrato_id AND
  	    crt_id IN (12, 13)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["data_ultima_alteracao"];
    }
  } 

  function possuiContrato($codigo) {
	return
		file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$codigo} Telecom"))) ||
		file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$codigo} Telecom")))) ||
		file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$codigo} Telecom")))) ? "Sim" : "Não";
  }

}

?>
