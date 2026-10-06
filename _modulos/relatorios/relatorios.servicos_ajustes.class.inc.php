<?

class relatorios_servicos_ajustes { 

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
        tab_servicos_ajustes.saj_id AS id,
        [campos_retorno]
      FROM
        tab_servicos_ajustes
        INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes_categorias.sac_id = tab_servicos_ajustes.sac_id)
        INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes_status.sas_id = tab_servicos_ajustes.sas_id)
        INNER JOIN tab_clientes ON (tab_servicos_ajustes.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_servicos_ajustes desbloqueio ON (tab_servicos_ajustes.saj_id = desbloqueio.saj_pai AND desbloqueio.sas_id IN (5, 9))
        [joins_filtro]
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
      	tab_servicos_ajustes.saj_id
      ORDER BY
        tab_servicos_ajustes_status.sas_nome ASC,
        tab_servicos_ajustes.saj_inicio ASC,
        tab_condominios.con_nome ASC,
        tab_clientes.cli_nome";
    return $consulta;
  }

  function buscaCampos() {
    $campos[] = array ("categoria" => "Cliente", "nome" => "ID", "campo" => "cliente_id", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Origem", "campo" => "origem_nome", "tabela" => "tab_clientes_origens.clo_nome AS origem_nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Nome", "campo" => "cliente_nome", "tabela" => "tab_clientes.cli_nome AS cliente_nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Vencimento", "campo" => "cliente_vencimento", "tabela" => "tab_clientes.cli_ccd_vencimento AS cliente_vencimento, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Formato de Pagamento", "campo" => "cliente_formato", "tabela" => "tab_cobrancas_formatos.cof_nome AS cliente_formato, ", "joins" => array("INNER JOIN tab_cobrancas_formatos ON (tab_clientes.cof_id = tab_cobrancas_formatos.cof_id)"));
    $campos[] = array ("categoria" => "Cliente", "nome" => "Condomínio", "campo" => "nome", "tabela" => "tab_condominios.con_nome AS nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Complemento", "campo" => "cliente_complemento", "tabela" => "CONCAT(SUBSTRING(tab_clientes.cli_complemento, 2), IF(tab_condominios_blocos.cob_id, CONCAT(IF(SUBSTRING(tab_clientes.cli_complemento, 2) != '', ' / ', ''), tab_condominios_blocos.cob_nome), '')) AS cliente_complemento, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "MAC's", "campo" => "cliente_macs", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Velocidades", "campo" => "cliente_velocidades");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Status", "campo" => "cliente_status", "tabela" => "tab_clientes_status.cls_nome AS cliente_status, ");

    $campos[] = array ("categoria" => "Ajuste", "nome" => "Categoria", "campo" => "categoria", "tabela" => "tab_servicos_ajustes_categorias.sac_nome AS categoria, ");
    $campos[] = array ("categoria" => "Ajuste", "nome" => "Início", "campo" => "inicio", "tabela" => "DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y %H:%i') AS inicio, ");
    $campos[] = array ("categoria" => "Ajuste", "nome" => "Término", "campo" => "termino", "tabela" => "DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y %H:%i') AS termino, ");
    $campos[] = array ("categoria" => "Ajuste", "nome" => "Status", "campo" => "status", "tabela" => "IF(desbloqueio.saj_id IS NULL, tab_servicos_ajustes_status.sas_nome, CONCAT(tab_servicos_ajustes_status.sas_nome, ' (Suspensão Temporária)')) AS status, ");
    $campos[] = array ("categoria" => "Ajuste", "nome" => "Suspensão » Início", "campo" => "suspensao_inicio", "tabela" => "DATE_FORMAT(desbloqueio.saj_inicio, '%d/%m/%Y %H:%i') AS suspensao_inicio, ");
    $campos[] = array ("categoria" => "Ajuste", "nome" => "Suspensão » Término", "campo" => "suspensao_termino", "tabela" => "DATE_FORMAT(desbloqueio.saj_termino, '%d/%m/%Y %H:%i') AS suspensao_termino, ");

    for ($i = 0; $i < sizeof($campos); $i++) {
      $retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"], "joins" => $campos[$i]["joins"]);
    }
    return $retorno;
  }

  function buscaFiltros() {
    $filtros[] = array ("tipo" => "select", "nome" => "Origem", "name" => "origem", "funcao" => "buscaOrigens()", "where" => "tab_clientes.clo_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Categoria", "name" => "categoria", "funcao" => "buscaCategorias()", "where" => "tab_servicos_ajustes.sac_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_clientes.con_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Status", "name" => "status", "funcao" => "buscaStatus()", "where" => "tab_servicos_ajustes.sas_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Clientes &raquo; Status", "name" => "clientes_status", "funcao" => "buscaClientesStatus()", "where" => "tab_clientes.cls_id");
    return $filtros;
  }

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Início", "name1" => "data_inicio_inicio", "name2" => "data_inicio_termino", "where" => "tab_servicos_ajustes.saj_inicio");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Término", "name1" => "data_termino_inicio", "name2" => "data_termino_termino", "where" => "tab_servicos_ajustes.saj_termino");
    return $periodos;
  }

  function buscaIntervalos() {
    $intervalos[] = array ("tipo" => "dias", "nome" => "Dia Vencimento", "name1" => "vencimento_inicio", "name2" => "vencimento_termino", "where" => "tab_clientes.cli_ccd_vencimento");
    return $intervalos;
  }

  ###################
  ### Busca Dados ###
  ###################

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

  function geraDadosListagem($comentarios) {
    $cabecalho = array ("detalhamento" => "Detalhamento ");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  function buscaStatus() {
    $query = "
      SELECT
        sas_id AS id,
        sas_nome AS nome
      FROM
        tab_servicos_ajustes_status
      ORDER BY
        sas_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    $status[-1] = "Análise";
    $status[-2] = "Inativo";
    asort($status);
    return $status;
  }

  function buscaClientesStatus() {
    $query = "
      SELECT
        cls_id AS id,
        cls_nome AS nome
      FROM
        tab_clientes_status
      ORDER BY
        nome";
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
        CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, '/', tab_bairros.bai_nome, ')') AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
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

  function buscaCategorias() {
    $query = "
      SELECT
        sac_id as id,
        sac_nome as nome
      FROM
        tab_servicos_ajustes_categorias
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categorias[$dados["id"]] = $dados["nome"];
    }
    return $categorias;
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

  function buscaMACs($cliente_id) {
    $query = "
      SELECT
        clp_endereco_mac AS mac
      FROM
        tab_assinaturas
        INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
      WHERE
        tab_assinaturas.cli_id = $cliente_id AND
        IF(tab_assinaturas.ast_id IN (1, 2, 5), tab_clientes_pontos.cps_id IN (1, 2), tab_clientes_pontos.cps_id IN (3))";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $macs[] = $dados["mac"];
    }
    return @implode(", ", $macs);
  }

  function buscaPontos($ajuste_id) {
    $query = "
      SELECT
        tab_planos.pla_nome AS plano
      FROM
        tab_planos
        INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
        INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        INNER JOIN rel_saj_clp_contem ON (tab_clientes_pontos.clp_id = rel_saj_clp_contem.clp_id)
      WHERE
        tab_clientes_pontos.cps_id IN (1, 2) AND
        rel_saj_clp_contem.saj_id = $ajuste_id
      UNION
      SELECT
        tab_planos.pla_nome AS plano
      FROM
        tab_planos
        INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
        INNER JOIN tab_clientes_autenticacoes ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
        INNER JOIN rel_saj_cau_contem ON (tab_clientes_autenticacoes.cau_id = rel_saj_cau_contem.cau_id)
      WHERE
        tab_clientes_autenticacoes.cas_id IN (1, 2) AND
        rel_saj_cau_contem.saj_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["plano"] = trim(preg_replace('/\([^\)]*\)/', '', $dados["plano"]));
      $dados["plano"] = trim(preg_replace('/\[[^\]]*\]/', '', $dados["plano"]));
      $dados["plano"] = trim(str_replace(array("Acesso Internet", "Pós Venda", "Pos Venda", "Ponto Principal", "Corujão", "Olé Banda Larga", "Olé Total", "PME"), "", $dados["plano"]));
      $velocidades[$dados["plano"]] = $dados["plano"];
    }
    return @implode(", ", $velocidades);
  }

}

?>
