<?

class relatorios_condominios {

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
      SELECT
        [campos_retorno]
      FROM
        tab_condominios
        INNER JOIN tab_condominios_blocos ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)
        INNER JOIN tab_bairros ON (tab_bairros.bai_id = tab_logradouros.bai_id)
        [joins_filtro]
      WHERE
        [campos_filtro]
      GROUP BY
        tab_condominios.con_id
      [HAVING]
      ORDER BY
        tab_condominios.con_nome ASC";
    return $consulta;
  }

  function buscaCampos() {
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "ID", "campo" => "condominio_id", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Nome", "campo" => "nome", "tabela" => "tab_condominios.con_nome AS nome, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Quantidade Blocos", "campo" => "qtde_blocos", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Quantidade Apartamentos", "campo" => "qtde_aptos", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Origens", "campo" => "origens", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Contato", "campo" => "contato", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Velocidade Máxima", "campo" => "velocidade_maxima", "tabela" => "tab_condominios.con_velocidade_maxima AS velocidade_maxima, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Classe Social", "campo" => "classe", "tabela" => "tab_condominios_classes.ccl_nome AS classe, ", "joins" => array("LEFT OUTER JOIN tab_condominios_classes ON (tab_condominios.ccl_id = tab_condominios_classes.ccl_id)"));
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Tecnologia", "campo" => "tecnologia", "tabela" => "CONCAT_WS(' / ', tab_condominios_tecnologias.cog_nome, tab_condominios_tecnologias_equipamentos.coq_nome) AS tecnologia, ", "joins" => array("LEFT OUTER JOIN tab_condominios_tecnologias ON (tab_condominios.cog_id = tab_condominios_tecnologias.cog_id)", "LEFT OUTER JOIN tab_condominios_tecnologias_equipamentos ON (tab_condominios.coq_id = tab_condominios_tecnologias_equipamentos.coq_id)"));
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "POP", "campo" => "pop", "tabela" => "tab_condominios.con_pai AS pai_id, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Tipo", "campo" => "tipo", "tabela" => "tab_condominios_tipos.cnt_nome AS tipo, ", "joins" => array("INNER JOIN tab_condominios_tipos ON (tab_condominios.cnt_id = tab_condominios_tipos.cnt_id)"));
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Tipo Instalação", "campo" => "instalacao_tipo", "tabela" => "tab_condominios_instalacoes_tipos.cit_nome AS instalacao_tipo, ", "joins" => array("LEFT OUTER JOIN tab_condominios_instalacoes_tipos ON (tab_condominios.cit_id = tab_condominios_instalacoes_tipos.cit_id)"));
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Data de Ativação", "campo" => "data_ativacao", "tabela" => "DATE_FORMAT(tab_condominios.con_data_ativacao, '%d/%m/%Y') AS data_ativacao, ");
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Situação", "campo" => "situacao", "tabela" => "tab_condominios_situacoes.coi_nome AS situacao, ", "joins" => array("LEFT OUTER JOIN tab_condominios_situacoes ON (tab_condominios.coi_id = tab_condominios_situacoes.coi_id)"));
    $campos[] = array ("categoria" => "Dados Cadastrais", "nome" => "Status", "campo" => "status", "tabela" => "tab_condominios_status.cos_nome AS status, ", "joins" => array("INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)"));

    $campos[] = array ("categoria" => "Endereço", "nome" => "Logradouro", "campo" => "logradouro", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Endereço", "nome" => "Número", "campo" => "numero", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Endereço", "nome" => "Bairro", "campo" => "bairro", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Endereço", "nome" => "Cidade", "campo" => "cidade", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Endereço", "nome" => "Estado", "campo" => "estado", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Endereço", "nome" => "CEP", "campo" => "cep", "tabela" => "tab_condominios.con_id AS id, ");

    $campos[] = array ("categoria" => "Comercial", "nome" => "Responsável", "campo" => "responsavel", "tabela" => "tab_usuarios.usu_nome AS responsavel, ", "joins" => array("LEFT OUTER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)"));
    $campos[] = array ("categoria" => "Comercial", "nome" => "Última Respons. (Início)", "campo" => "ultima_responsabilidade_inicio", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Comercial", "nome" => "Última Respons. (Término)", "campo" => "ultima_responsabilidade_termino", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Comercial", "nome" => "Últimas Atividades", "campo" => "ultimas_atividades", "tabela" => "tab_condominios.con_id AS id, ");

    $campos[] = array ("categoria" => "Síndico", "nome" => "Nome", "campo" => "sindico_nome", "tabela" => "tab_condominios_contatos.coc_nome AS sindico_nome, ", "joins" => array("LEFT OUTER JOIN tab_condominios_contatos ON (tab_condominios.con_id = tab_condominios_contatos.con_id AND tab_condominios_contatos.cct_id = 3)"));
    $campos[] = array ("categoria" => "Síndico", "nome" => "E-mail(s)", "campo" => "sindico_emails", "tabela" => "tab_condominios_contatos.coc_email AS sindico_emails, ", "joins" => array("LEFT OUTER JOIN tab_condominios_contatos ON (tab_condominios.con_id = tab_condominios_contatos.con_id AND tab_condominios_contatos.cct_id = 3)"));
    $campos[] = array ("categoria" => "Síndico", "nome" => "Telefone(s)", "campo" => "sindico_telefones", "tabela" => "CONCAT_WS('<br>', tab_condominios_contatos.coc_tel_residencial, tab_condominios_contatos.coc_tel_comercial, tab_condominios_contatos.coc_tel_celular) AS sindico_telefones, ", "joins" => array("LEFT OUTER JOIN tab_condominios_contatos ON (tab_condominios.con_id = tab_condominios_contatos.con_id AND tab_condominios_contatos.cct_id = 3)"));
    $campos[] = array ("categoria" => "Síndico", "nome" => "Validade do Mandato", "campo" => "sindico_mandato", "tabela" => "DATE_FORMAT(tab_condominios_contatos.coc_validade, '%d/%m/%Y') AS sindico_mandato, ", "joins" => array("LEFT OUTER JOIN tab_condominios_contatos ON (tab_condominios.con_id = tab_condominios_contatos.con_id AND tab_condominios_contatos.cct_id = 3)"));

    $campos[] = array ("categoria" => "Clientes", "nome" => "Mailings", "campo" => "qtde_mailing_disponivel", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Prospects", "campo" => "qtde_prospects", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Ativos", "campo" => "qtde_ativos", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Ativos no Período (Principal)", "campo" => "qtde_ativos_periodo_principal", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Ativos no Período (Aditivo)", "campo" => "qtde_ativos_periodo_aditivo", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Cancelados", "campo" => "qtde_cancelados", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Cancelados no Período (Princ...)", "campo" => "qtde_cancelados_periodo_principal", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Cancelados no Período (Aditivo)", "campo" => "qtde_cancelados_periodo_aditivo", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Velocidade Vendida", "campo" => "velocidade_vendida", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Clientes", "nome" => "Velocidade Vendida (bits)", "campo" => "velocidade_vendida_bits", "tabela" => "tab_condominios.con_id AS id, ");

    $campos[] = array ("categoria" => "Valores", "nome" => "Mensalidade", "campo" => "mensalidade", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Valores", "nome" => "Desconto Indeterminado (Parcial)", "campo" => "desconto_indeterminado", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Valores", "nome" => "Desconto Com Prazo (Parcial)", "campo" => "desconto_com_prazo", "tabela" => ", ");
    $campos[] = array ("categoria" => "Valores", "nome" => "Desconto Total", "campo" => "desconto", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Valores", "nome" => "Total Última Cobrança", "campo" => "total_ultima_cobranca", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Valores", "nome" => "Ticket Médio", "campo" => "ticket_medio", "tabela" => "tab_condominios.con_id AS id, ");

    $campos[] = array ("categoria" => "Retenção", "nome" => "Pedidos Cancelamento", "campo" => "qtde_pedidos_de_cancelamento", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Retenção", "nome" => "Pedidos Cancelamento Período", "campo" => "qtde_pedidos_de_cancelamento_periodo", "tabela" => "tab_condominios.con_id AS id, ");

    $campos[] = array ("categoria" => "Chamados", "nome" => "Chamados do Condomínio (Período)", "campo" => "qtde_chamados_condominio", "tabela" => "tab_condominios.con_id AS id, ");
    $campos[] = array ("categoria" => "Chamados", "nome" => "Chamados dos Clientes (Período)", "campo" => "qtde_chamados_clientes", "tabela" => "tab_condominios.con_id AS id, ");

    for ($i = 0; $i < sizeof($campos); $i++) {
      $retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"], "joins" => $campos[$i]["joins"]);
    }
    return $retorno;
  }

  function buscaFiltros() {
    $filtros[] = array ("tipo" => "select", "nome" => "Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_condominios.con_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Cidade", "name" => "cidade", "funcao" => "buscaCidades()", "where" => "tab_bairros.cid_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Bairro", "name" => "bairro", "funcao" => "buscaBairros()", "where" => "tab_logradouros.bai_id");
    $filtros[] = array ("tipo" => "input", "nome" => "Comentário", "name" => "comentario", "where" => "tab_condominios.con_comentarios");
    $filtros[] = array ("tipo" => "select", "nome" => "Responsável", "name" => "responsavel", "funcao" => "buscaCondominioResponsaveis()", "where" => "tab_condominios.usu_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Tipo", "name" => "tipo", "funcao" => "buscaCondominioTipos()", "where" => "tab_condominios.cnt_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Classe Social", "name" => "classe", "funcao" => "buscaCondominioClasses()", "where" => "tab_condominios.ccl_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Tecnologia", "name" => "tecnologia", "funcao" => "buscaCondominioTecnologias()", "where" => "tab_condominios.cog_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Situação", "name" => "situacao", "funcao" => "buscaSituacoes()", "where" => "tab_condominios.coi_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Status", "name" => "status", "funcao" => "buscaStatus()", "where" => "tab_condominios.cos_id");
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Condomínio &raquo; Cadastro", "name1" => "data_cadastro_inicio", "name2" => "data_cadastro_termino", "where" => "tab_condominios.con_data_cadastro");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Condomínio &raquo; Ativação", "name1" => "data_ativacao_inicio", "name2" => "data_ativacao_termino", "where" => "tab_condominios.con_data_ativacao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Clientes &raquo; Ativação", "name1" => "cli_data_ativacao_inicio", "name2" => "cli_data_ativacao_termino", "where" => "");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Clientes &raquo; Cancelamento", "name1" => "cli_data_cancelamento_inicio", "name2" => "cli_data_cancelamento_termino", "where" => "");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Cancelamentos &raquo; Solicitação", "name1" => "cli_data_pedido_cancelamento_inicio", "name2" => "cli_data_pedido_cancelamento_termino", "where" => "");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Chamados &raquo; Abertura", "name1" => "chamados_abertura_inicio", "name2" => "chamados_abertura_termino", "where" => "");
    return $periodos;
  }

  function buscaIntervalos() {
    $intervalos[] = array ("tipo" => "quantidade", "nome" => "Qtde Clientes Prospects", "name1" => "prospects_inferior", "name2" => "prospects_superior", "where" => "1", "joins" => "LEFT OUTER JOIN tab_clientes clientes_prospects ON (tab_condominios.con_id = clientes_prospects.con_id AND clientes_prospects.cls_id IN (1))", "having" => "count(DISTINCT clientes_prospects.cli_id)");
    $intervalos[] = array ("tipo" => "quantidade", "nome" => "Qtde Clientes Ativos", "name1" => "ativos_inferior", "name2" => "ativos_superior", "where" => "1", "joins" => "LEFT OUTER JOIN tab_clientes clientes_ativos ON (tab_condominios.con_id = clientes_ativos.con_id AND clientes_ativos.cls_id IN (2))", "having" => "count(DISTINCT clientes_ativos.cli_id)");
    $intervalos[] = array ("tipo" => "quantidade", "nome" => "Qtde Clientes Cancelados", "name1" => "cancelados_inferior", "name2" => "cancelados_superior", "where" => "1", "joins" => "LEFT OUTER JOIN tab_clientes clientes_cancelados ON (tab_condominios.con_id = clientes_cancelados.con_id AND clientes_ativos.cls_id IN (3))", "having" => "count(DISTINCT clientes_cancelados.cli_id)");
    $intervalos[] = array ("tipo" => "quantidade", "nome" => "Qtde Pedidos de Cancel.", "name1" => "pedidos_cancelamento_inferior", "name2" => "pedidos_cancelamento_superior", "where" => "1", "joins" => "LEFT OUTER JOIN tab_cancelamentos cancelamentos ON (tab_clientes.cli_id = cancelamentos.cli_id)", "having" => "COUNT(DISTINCT cancelamentos.can_id)");
    $intervalos[] = array ("tipo" => "velocidade", "nome" => "Velocidade Máxima", "name1" => "velocidade_maxima_inferior", "name2" => "velocidade_maxima_superior", "where" => "con_velocidade_maxima");
    return $intervalos;
  }

  ###################
  ### Busca Dados ###
  ###################

  function buscaUnidades($unidade = "", $valor = null) {
    $unidades["1"] = "K".$unidade;
    $unidades["2"] = "M".$unidade;
    $unidades["3"] = "G".$unidade;
    $unidades["4"] = "T".$unidade;
    return $valor ? $unidades[$valor] : $unidade;
  }

  function formataTamanho($tamanho) {
    if (!$tamanho) return "-";
    $unidade = floor(log($tamanho, 1024));
    if (round($tamanho / pow(1024, $unidade)) >= 1000) $unidade++;
    return number_format($tamanho / pow(1024, $unidade), 1, ",", ".")." ".$this->buscaUnidades("bps", $unidade);
  }

  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }

  function retornaValorDesconto($valor) {
    if ($valor <= 1 && $valor > 0) {
      return ($valor * 100)."%";
    } else {
      return $this->formataDinheiro($valor);
    }
  }

  function geraDadosListagem($dados) {
    return @implode("<hr>", $dados);
    $cabecalho = array ("detalhamento" => "Detalhamento ");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  function buscaCondominioContatos($id) {
    $query = "
      SELECT
        tab_condominios_contatos.coc_nome AS nome,
        tab_condominios_contatos.coc_email AS email,
        tab_condominios_contatos_tipos.cct_nome AS tipo,
        tab_condominios_contatos.coc_tel_residencial AS residencial,
        tab_condominios_contatos.coc_tel_comercial AS comercial,
        tab_condominios_contatos.coc_tel_celular AS celular
      FROM
        tab_condominios_blocos
        INNER JOIN tab_condominios_contatos ON (tab_condominios_blocos.cob_id = tab_condominios_contatos.cob_id)
        INNER JOIN tab_condominios_contatos_tipos ON (tab_condominios_contatos.cct_id = tab_condominios_contatos_tipos.cct_id)
      WHERE
        tab_condominios_blocos.con_id = $id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["residencial"]) $dados["telefones"][] = $dados["residencial"];
      if ($dados["comercial"]) $dados["telefones"][] = $dados["comercial"];
      if ($dados["celular"]) $dados["telefones"][] = $dados["celular"];
      $dados["telefones"] = @implode(" / ", $dados["telefones"]);
      if ($dados["nome"]) $dados["detalhamento"][] = "<b>Nome:</b> ".$dados["nome"]." (".$dados["tipo"].") ";
      if ($dados["email"]) $dados["detalhamento"][] = "<b>E-mail:</b> ".$dados["email"]." ";
      if ($dados["telefones"]) $dados["detalhamento"][] = "<b>Telefone(s):</b> ".$dados["telefones"]." ";
      $dados["detalhamento"] = '<div style="text-align: left; width: 100%">'.@implode('<br>', $dados["detalhamento"]).'</div>';
      $contatos[] = $dados["detalhamento"];
    }
    return $contatos;
  }

  function buscaStatus() {
    $query = "
      SELECT
        cos_id AS id,
        cos_nome AS nome
      FROM
        tab_condominios_status
      ORDER BY
        cos_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaSituacoes() {
    $query = "
      SELECT
        coi_id AS id,
        coi_nome AS nome
      FROM
        tab_condominios_situacoes
      ORDER BY
        coi_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
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

  function buscaBairros() {
    $query = "
      SELECT DISTINCT
        tab_bairros.bai_id AS id,
        CONCAT(tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome) AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $bairros[$dados["id"]] = $dados["nome"];
    }
    return $bairros;
  }

  function buscaCidades() {
    $query = "
      SELECT DISTINCT
        tab_cidades.cid_id AS id,
        tab_cidades.cid_nome AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cidades[$dados["id"]] = $dados["nome"];
    }
    return $cidades;
  }

  function buscaCondominioResponsaveis() {
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

  function buscaCondominioTipos() {
    $query = "
      SELECT
        cnt_id AS id,
        cnt_nome AS nome
      FROM
        tab_condominios_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaCondominioClasses() {
    $query = "
      SELECT
        ccl_id AS id,
        ccl_nome AS nome
      FROM
        tab_condominios_classes
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
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

  function buscaOrigens($condominio_id) {
    $query = "
      SELECT DISTINCT
        tab_clientes_origens.clo_nome AS origem
      FROM
        tab_clientes
        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
        INNER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
      WHERE
        tab_condominios.con_id = $condominio_id AND
        tab_assinaturas.ass_data_ativacao < '2006-12-01' AND
        tab_assinaturas.ast_id IN (2, 5) AND
        tab_clientes.cls_id IN (2)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[] = $dados["origem"];
    }
    return @implode(", ", $origens);
  }

  function buscaEndereco($id) {
    $query = "
      SELECT
        tab_estados.est_nome AS estado,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro,
        INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
        tab_condominios.con_numero AS numero
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        tab_condominios.con_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaQtdeMailingDisponivel($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS clientes
      FROM
        tab_clientes
      WHERE
        con_id = $id AND
        cls_id = 4";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaQtdeMailingOperados($id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS clientes
      FROM
        tab_clientes
        INNER JOIN tab_operacoes_contatos ON (tab_clientes.cli_id = tab_operacoes_contatos.cli_id)
      WHERE
        tab_clientes.con_id = $id AND
        tab_clientes.cls_id = 4";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaQtdeBlocos($id) {
    $query = "
      SELECT
        COUNT(cob_id) AS blocos
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["blocos"];
    }
  }

  function buscaQtdeAtivos($id) {
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS clientes
      FROM
        tab_clientes
        INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
      WHERE
        tab_clientes.con_id = $id AND
        tab_clientes.cls_id = 2 AND
        tab_contratos.crs_id IN (19, 20, 21) AND
        tab_contratos.pls_id IN (1, 28)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaVelocidadeVendida($condominio_id, $formatado = false) {
    $query = "
      SELECT
        SUM(tab_clientes_pontos.clp_velocidade_download) AS velocidade
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
      WHERE
        tab_clientes.con_id = $condominio_id AND
        tab_clientes_pontos.cps_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $formatado ? $this->formataTamanho($dados["velocidade"]) : ($dados["velocidade"] ? $dados["velocidade"] : 0);
    } else {
      return 0;
    }
  }

  function buscaQtdeAtivosNoPeriodo($id, $inicio, $termino, $tipo) {
    $tipo = ($tipo == "principal") ? "11" : "12, 13";
    $inicio = $this->buscaInicio("tab_contratos.cor_data_ativacao", $inicio);
    $termino = $this->buscaTermino("tab_contratos.cor_data_ativacao", $termino);
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS clientes
      FROM
        tab_clientes
        INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
      WHERE
        tab_clientes.con_id = $id AND
        $inicio AND
        $termino AND
        tab_contratos.pls_id = 1 AND
        tab_contratos.crt_id IN ($tipo)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaQtdeProspects($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS clientes
      FROM
        tab_clientes
      WHERE
        tab_clientes.con_id = $id AND
        tab_clientes.cls_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaQtdeCancelados($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS clientes
      FROM
        tab_clientes
      WHERE
        tab_clientes.con_id = $id AND
        tab_clientes.cls_id = 3";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaQtdeCanceladosNoPeriodo($id, $inicio, $termino, $tipo) {
    $tipo = ($tipo == "principal") ? "11" : "12, 13";
    $inicio = $this->buscaInicio("tab_contratos.cor_data_cancelamento", $inicio);
    $termino = $this->buscaTermino("tab_contratos.cor_data_cancelamento", $termino);
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes.cli_id) AS clientes
      FROM
        tab_clientes
        INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
      WHERE
        tab_clientes.con_id = $id AND
        $inicio AND
        $termino AND
        tab_contratos.pls_id = 1 AND
        tab_contratos.crt_id IN ($tipo)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["clientes"];
    }
  }

  function buscaQtdeAptos($id) {
    $query = "
      SELECT
        SUM(cob_quantidade) AS apartamentos
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["apartamentos"];
    }
  }

  function buscaQtdePedidosCancelamento($id) {
    $query = "
      SELECT
        COUNT(tab_cancelamentos.can_id) AS cancelamentos
      FROM
        tab_clientes
        INNER JOIN tab_cancelamentos ON (tab_clientes.cli_id = tab_cancelamentos.cli_id)
      WHERE
        tab_clientes.con_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["cancelamentos"];
    }
  }

  function buscaClientesComSistemaAtivo($id) {
    $query = "
      SELECT DISTINCT
        tab_clientes.cli_nome AS cliente,
        tab_condominios_blocos.cob_nome AS bloco,
        tab_clientes.cli_complemento AS complemento
      FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
      WHERE
        tab_clientes.con_id = $id AND
        tab_assinaturas.ast_id IN (2, 5)
      ORDER BY
        cliente";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $ativo["bloco"] = $dados["bloco"];
      $ativo["complemento"] = preg_replace("/[^0-9]/", "", $dados["complemento"]);
      if (!$ativo["complemento"]) unset($ativo["complemento"]);
      if ($ativo["bloco"] == "Único") unset($ativo["bloco"]);
      $ativos[] = $dados["cliente"]." (".implode("/", $ativo).")";
    }
    return ($ativos) ? @implode(", ", $ativos) : 'Sem clientes ativos';
  }

  function buscaQtdePedidosCancelamentoNoPeriodo($id, $inicio, $termino) {
    $inicio = $this->buscaInicio("tab_cancelamentos.can_inicio", $inicio);
    $termino = $this->buscaTermino("tab_cancelamentos.can_inicio", $termino);
    $query = "
      SELECT
        COUNT(tab_cancelamentos.can_id) AS cancelamentos
      FROM
        tab_clientes
        INNER JOIN tab_cancelamentos ON (tab_clientes.cli_id = tab_cancelamentos.cli_id)
      WHERE
        tab_clientes.con_id = $id AND
        $inicio AND
        $termino";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["cancelamentos"];
    }
  }

  function buscaMensalidade($id) {
    $mensalidade = 5990; // R$ 59,00
    $desconto = $this->buscaDesconto($id);
    if ($desconto <= 1) {
      $mensalidade = $mensalidade - ($mensalidade * $desconto);
    } else {
      $mensalidade = $mensalidade - $desconto;
    }
    return $mensalidade;
  }

  function buscaDesconto($id) {
    $query = "
      SELECT
        dec_mensalidade AS mensalidade
      FROM
        tab_descontos_concessoes
      WHERE
        dcs_id = 5 AND
        con_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["mensalidade"];
    }
  }

  function buscaDescontoIndeterminado($id) {
    $query = "
      SELECT
        dec_mensalidade AS mensalidade
      FROM
        tab_descontos_concessoes
      WHERE
        dcs_id = 5 AND
        con_id = $id AND
        dec_data_termino IS NULL";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["mensalidade"];
    }
  }

  function buscaDescontoComPrazo($id) {
    $query = "
      SELECT
        dec_mensalidade AS mensalidade
      FROM
        tab_descontos_concessoes
      WHERE
        dcs_id = 5 AND
        con_id = $id AND
        dec_data_termino IS NOT NULL";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["mensalidade"];
    }
  }

  function buscaInicio($campo, $inicio) {
    if ($inicio) {
      $inicio = substr($inicio, 6, 4)."-".substr($inicio, 3, 2)."-".substr($inicio, 0, 2);
      $inicio = "$campo >= '$inicio'";
    } else {
      $inicio = 1;
    }
    return $inicio;
  }

  function buscaTermino($campo, $termino) {
    if ($termino) {
      $termino = substr($termino, 6, 4)."-".substr($termino, 3, 2)."-".substr($termino, 0, 2);
      $termino = "$campo <= '$termino'";
    } else {
      $termino = 1;
    }
    return $termino;
  }

  function buscaChamadosClientes($condominio_id, $abertura_inicio, $abertura_termino) {
    $where[] = "tab_clientes.con_id = $condominio_id";
    if ($abertura_inicio) $where[] = "DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%Y-%m-%d') >= '$abertura_inicio'";
    if ($abertura_termino) $where[] = "DATE_FORMAT(tab_chamados.cha_datahora_abertura, '%Y-%m-%d') <= '$abertura_termino'";
    $where = implode(" AND\n        ", $where);
    $query = "
      SELECT
        COUNT(DISTINCT tab_chamados.cha_id) AS chamados
      FROM
        tab_chamados
        INNER JOIN tab_clientes ON (tab_chamados.cli_id = tab_clientes.cli_id)
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

  function buscaChamadosCondominio($condominio_id, $abertura_inicio, $abertura_termino) {
    $where[] = "tab_chamados.con_id = $condominio_id";
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

  function buscaUltimaResponsabilidadeInicio($condominio_id) {
    $query = "
      SELECT
        DATE_FORMAT(cod_inicio, '%d/%m/%Y') AS data
      FROM
        tab_condominios_responsabilidades
      WHERE
        con_id = $condominio_id
      ORDER BY
        cod_inicio DESC
      LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["data"];
    }
  }

  function buscaUltimaResponsabilidadeTermino($condominio_id, $id = false) {
    $query = "
      SELECT
        cod_id AS id,
        DATE_FORMAT(cod_termino, '%d/%m/%Y') AS data
      FROM
        tab_condominios_responsabilidades
      WHERE
        con_id = $condominio_id AND
        cru_id IN (4, 5, 6)
      ORDER BY
        cod_termino DESC
      LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $id ? $dados["id"] : $dados["data"];
    }
  }

  function buscaUltimasAtividades($condominio_id) {
  	$responsabilidade_id = $this->buscaUltimaResponsabilidadeTermino($condominio_id, true);
    if ($responsabilidade_id) {
      $query = "
        SELECT
          DATE_FORMAT(tab_condominios_responsabilidades.cod_inicio, '%d/%m/%Y') AS data_inicio,
          DATE_FORMAT(tab_condominios_responsabilidades.cod_termino, '%d/%m/%Y') AS data_termino,
          tab_condominios_responsabilidades_atividades.cov_nome AS atividade
        FROM
          tab_condominios_responsabilidades
          INNER JOIN rel_cov_cod_estacontido ON (tab_condominios_responsabilidades.cod_id = rel_cov_cod_estacontido.cod_id)
          INNER JOIN tab_condominios_responsabilidades_atividades ON (rel_cov_cod_estacontido.cov_id = tab_condominios_responsabilidades_atividades.cov_id)
        WHERE
          tab_condominios_responsabilidades.cod_id = $responsabilidade_id AND
          tab_condominios_responsabilidades.cru_id IN (4, 5, 6)";
      $query = $this->bancodedados->query($query);
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $atividades["data_inicio"] = $dados["data_inicio"];
        $atividades["data_termino"] = $dados["data_termino"];
        $atividades["atividades"][] = $dados["atividade"];
      }
    }
    if ($atividades) {
      return $atividades["data_inicio"]." - ".$atividades["data_termino"].": ".implode(", ", $atividades["atividades"]);
    } else {
      return "";
    }
  }

  function buscaTotalUltimaCobranca($condominio_id, $vencimento) {
    $query = "
      SELECT
        tab_cobrancas.coa_valor_original AS valor_original
      FROM
        tab_clientes
        INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
        INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
      WHERE
        tab_clientes.con_id = $condominio_id AND
        tab_cobrancas.coa_data_vencimento LIKE '$vencimento%' AND
        tab_cobrancas.coa_pai IS NULL AND
        tab_contratos.crs_id IN (19, 20, 21) AND
        tab_contratos.pls_id IN (1, 2)
      GROUP BY
        tab_cobrancas.coa_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $total += $dados["valor_original"];
    }
    return $this->formataDinheiro($total);
  }

  function buscaUltimoVencimento() {
    $query = "
      SELECT
        DATE_FORMAT(MAX(col_vencimento), '%Y-%m') as vencimento
      FROM
        tab_cobrancas_lotes";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["vencimento"];
    }
  }

  function buscaPOP($condominio_id) {
    $query = "
      SELECT
        con_nome AS nome,
        cog_nome AS tecnologia,
        coq_nome AS equipamento
      FROM
        tab_condominios
        LEFT OUTER JOIN tab_condominios_tecnologias ON (tab_condominios.cog_id = tab_condominios_tecnologias.cog_id)
        LEFT OUTER JOIN tab_condominios_tecnologias_equipamentos ON (tab_condominios.coq_id = tab_condominios_tecnologias_equipamentos.coq_id)
      WHERE
        tab_condominios.con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"].($dados["tecnologia"] ? "<br>({$dados["tecnologia"]}".($dados["equipamento"] ? " / {$dados["equipamento"]}" : "").")" : "");
    }
  }
  
  function buscaTicketMedio($condominio_id) {
    $query = "
      SELECT
        SUM(tab_assinaturas.ass_mensalidade - (SELECT SUM(IF(dec_mensalidade  > 1, dec_mensalidade, ass_mensalidade * dec_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5)) / COUNT(DISTINCT tab_clientes.cli_id) AS valor
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
      WHERE
        tab_clientes.con_id = $condominio_id AND
        tab_assinaturas.ast_id = 2 AND
        tab_planos.pls_id IN (1, 21)
      GROUP BY
        tab_clientes.con_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if (intval($dados["valor"]) > 0) {
        return $this->formataDinheiro(intval($dados["valor"]));
      }
    }
  }

}

?>
