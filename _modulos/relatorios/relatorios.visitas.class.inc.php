<?

class relatorios_visitas {

  var $bancodedados;
  var $permissoes;

  function __construct ($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }

  ####################################
  ########## Funções Gerais ##########
  ####################################

  function buscaConsulta () {
    $consulta = "
      SELECT
        [campos_retorno]
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
        INNER JOIN tab_clientes_tipos ON (tab_clientes.clt_id = tab_clientes_tipos.clt_id)
        LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_visitas ON (tab_clientes.cli_id = tab_visitas.cli_id)
        INNER JOIN tab_visitas_status ON (tab_visitas.via_id = tab_visitas_status.via_id)
        LEFT OUTER JOIN tab_contratos ON (tab_visitas.cor_id = tab_contratos.cor_id)
        LEFT OUTER JOIN tab_contratos_tipos ON (tab_contratos.crt_id = tab_contratos_tipos.crt_id)
        LEFT OUTER JOIN tab_contratos_status ON (tab_contratos.crs_id = tab_contratos_status.crs_id)
        LEFT OUTER JOIN tab_visitas_periodos ON (tab_visitas.vip_id = tab_visitas_periodos.vip_id)
        LEFT OUTER JOIN tab_visitas_comentarios ON (tab_visitas.vis_id = tab_visitas_comentarios.vis_id)
      WHERE
        [campos_filtro]".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      GROUP BY
      	tab_visitas.vis_id
      ORDER BY
        tab_clientes.cli_nome ASC,
      	tab_visitas_comentarios.vic_datahora DESC";
    return $consulta;
  }

  function buscaCampos () {
    $campos[] = array ("categoria" => "Cliente", "nome" => "Nome", "campo" => "cliente_nome", "tabela" => "tab_clientes.cli_nome AS cliente_nome, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Condomínio", "campo" => "cliente_condominio", "tabela" => "tab_condominios.con_nome AS cliente_condominio, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Bloco", "campo" => "cliente_bloco", "tabela" => "tab_condominios_blocos.cob_nome AS cliente_bloco, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Complemento", "campo" => "cliente_complemento", "tabela" => "SUBSTRING(tab_clientes.cli_complemento,2) AS cliente_complemento, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Data Cadastro", "campo" => "cliente_data_cadastro", "tabela" => "DATE_FORMAT(tab_clientes.cli_data_cadastro,'%d/%m/%Y') AS cliente_data_cadastro, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Tipo", "campo" => "cliente_tipo", "tabela" => "tab_clientes_tipos.clt_nome AS cliente_tipo, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "CPF/CNPJ", "campo" => "cliente_cpf_cnpj", "tabela" => "tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Telefone(s)", "campo" => "cliente_telefone", "tabela" => "tab_clientes.cli_id AS cliente_id, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Responsável", "campo" => "cliente_responsavel", "tabela" => "tab_usuarios.usu_nome AS cliente_responsavel, ");
    $campos[] = array ("categoria" => "Cliente", "nome" => "Status", "campo" => "cliente_status", "tabela" => "tab_clientes_status.cls_nome AS cliente_status, ");

    $campos[] = array ("categoria" => "Visita", "nome" => "Data Agendamento", "campo" => "visita_data", "tabela" => "DATE_FORMAT(tab_visitas.vis_data,'%d/%m/%Y') AS visita_data, ");
    $campos[] = array ("categoria" => "Visita", "nome" => "Periodo", "campo" => "visita_periodo", "tabela" => "tab_visitas_periodos.vip_nome AS visita_periodo, ");
    $campos[] = array ("categoria" => "Visita", "nome" => "Comentários", "campo" => "visita_comentarios", "tabela" => "tab_visitas.vis_id AS visita_id, ");
    $campos[] = array ("categoria" => "Visita", "nome" => "Status", "campo" => "visita_status", "tabela" => "tab_visitas_status.via_nome AS visita_status, ");

    $campos[] = array ("categoria" => "Contrato", "nome" => "Código", "campo" => "contrato_codigo", "tabela" => "tab_contratos.cor_codigo AS contrato_codigo, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Geração", "campo" => "contrato_data_geracao", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_geracao,'%d/%m/%Y') AS contrato_data_geracao, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Ativação", "campo" => "contrato_data_ativacao", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_ativacao,'%d/%m/%Y') AS contrato_data_ativacao, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Data Cancelamento", "campo" => "contrato_data_cancelamento", "tabela" => "DATE_FORMAT(tab_contratos.cor_data_cancelamento,'%d/%m/%Y') AS contrato_data_cancelamento, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Mensalidade", "campo" => "contrato_mensalidade", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Pontos", "campo" => "contrato_pontos", "tabela" => "tab_contratos.cor_id AS contrato_id, ");
    $campos[] = array ("categoria" => "Contrato", "nome" => "Status", "campo" => "contrato_status", "tabela" => "tab_contratos_status.crs_nome AS contrato_status, ");

    for ($i = 0; $i < sizeof($campos); $i++) {
      $retorno[$campos[$i]["categoria"]][] = array("nome" => $campos[$i]["nome"], "campo" => $campos[$i]["campo"], "tabela" => $campos[$i]["tabela"]);
    }
    return $retorno;
  }

  function buscaFiltros () {
    $filtros[] = array ("tipo" => "select", "nome" => "Condomínio", "name" => "condominio", "funcao" => "buscaCondominios()", "where" => "tab_clientes.con_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Comentário Categoria", "name" => "comentario_categoria", "funcao" => "buscaVisitaComentariosCategorias()", "where" => "tab_visitas_comentarios.vcc_id");
    $filtros[] = array ("tipo" => "input", "nome" => "Comentário Conteúdo", "name" => "comentario_conteudo", "where" => "tab_visitas_comentarios.vic_conteudo");
    $filtros[] = array ("tipo" => "select", "nome" => "Responsável", "name" => "responsavel", "funcao" => "buscaClienteResponsaveis()", "where" => "tab_usuarios.usu_id");
    $filtros[] = array ("tipo" => "select", "nome" => "Status", "name" => "status", "funcao" => "buscaStatus()", "where" => "tab_visitas.via_id");
    return $filtros;
  }

  function buscaPeriodos () {
    $periodos[] = array ("tipo" => "periodo", "nome" => "Geração do Contrato", "name1" => "data_geracao_inicio", "name2" => "data_geracao_termino", "where" => "tab_contratos.cor_data_geracao");
    $periodos[] = array ("tipo" => "periodo", "nome" => "Agendamento", "name1" => "data_agendamento_inicio", "name2" => "data_agendamento_termino", "where" => "tab_visitas.vis_data");
    return $periodos;
  }

  ####################################
  ########### Busca Dados ############
  ####################################

  function geraComentariosListagem ($comentarios) {
    $cabecalho = array ("detalhamento" => "Detalhamento");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  function buscaVisitaComentarios($id) {
    $query = "
      SELECT
        DATE_FORMAT(tab_visitas_comentarios.vic_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
        tab_visitas_comentarios_categorias.vcc_nome AS categoria,
        tab_visitas_comentarios.vic_conteudo AS conteudo,
        IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel
      FROM
        tab_visitas_comentarios
        LEFT OUTER JOIN tab_usuarios ON (tab_visitas_comentarios.usu_id = tab_usuarios.usu_id)
        LEFT OUTER JOIN tab_visitas_comentarios_categorias ON (tab_visitas_comentarios.vcc_id = tab_visitas_comentarios_categorias.vcc_id)
      WHERE
        tab_visitas_comentarios.vis_id = $id
      ORDER BY
        tab_visitas_comentarios.vic_datahora ASC,
        tab_visitas_comentarios.vic_id ASC
      ";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["detalhamento"][] = "<b>Adicionado por:</b> " . $dados["responsavel"] . " <b>Em:</b> " . $dados["responsavel"] . ($dados["categoria"]) ? " <b>Categoria:</b> " . $dados["categoria"] . " " : " ";
      $dados["detalhamento"][] = "<b>Conteúdo:</b> " . nl2br($dados["conteudo"]) . " ";
      $dados["detalhamento"] = '<span style="text-align: justify; width: 100%">' . @implode('<br>', $dados["detalhamento"]) . '</span>';
      $comentarios[] = $dados;
    }
    return $comentarios;
  }

  function buscaVisitaComentariosCategorias() {
    $query = "SELECT vcc_id AS id, vcc_nome AS nome FROM tab_visitas_comentarios_categorias ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$categorias[$dados["id"]] = $dados["nome"];
    }
    return $categorias;
  }

  function buscaStatus () {
    $visitas_status = $this->buscaVisitaStatus();
    $visitas_status["-1"] = "Em Aberto";
    asort($visitas_status);
    return $visitas_status;
  }

  function buscaCondominios () {
    $query = "SELECT con_id AS id, con_nome AS nome FROM tab_condominios WHERE cos_id IS NOT NULL ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }

  function buscaClienteResponsaveis () {
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

  function buscaClienteTelefones ($id) {
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

  function buscaVisitaStatus () {
    $query = "SELECT via_id AS id, via_nome AS nome FROM tab_visitas_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $visitas_status[$dados["id"]] = $dados["nome"];
    }
    return $visitas_status;
  }

  function buscaContratoMensalidade($contrato, $condominio = "") {
    $query = "
			SELECT
			  tab_assinaturas.ass_id AS id,
			  tab_assinaturas.ass_mensalidade AS plano_mensalidade,
			  tab_planos.pla_id AS plano_id,
			  tab_planos.pls_id AS plano_servico,
			  tab_planos.pla_desconto_condominio AS desconto_condominio
			FROM
			  rel_ass_cor_estacontido
			  INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			  INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
			  rel_ass_cor_estacontido.cor_id = '$contrato'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        $dados["condominio"] = $condominio;
        $mensalidade_total += $this->buscaMensalidadeAssinatura($dados);
      }
    }
    return $mensalidade_total;
  }

  function buscaMensalidadeAssinatura ($assinatura) {
    // Descontos
    $descontos_condominio = $this->buscaDescontosCondominio($assinatura["condominio"], $assinatura["plano_servico"], $assinatura["plano_id"], $assinatura["plano_mensalidade"]);
    $descontos_assinatura = $this->buscaDescontosAssinatura($assinatura["id"], $assinatura["plano_id"].$assinatura["adicional_id"], $assinatura["plano_mensalidade"].$assinatura["adicional_mensalidade"]);
    $descontos = $descontos_condominio + $descontos_assinatura;
    $assinatura_valor = intval($assinatura["plano_mensalidade"] + $assinatura["adicional_mensalidade"]);
    $assinatura_valor -= $descontos;
    return $assinatura_valor;
  }

  function buscaDescontosCondominio($condominio, $servico, $desconto_condominio, $mensalidade) {
    // Verifica se o plano é tem a maior mensalidade
    if ($desconto_condominio) {
      $query = "
				SELECT
				  tab_descontos_concessoes.dec_mensalidade AS mensalidade
				FROM
				  tab_descontos_concessoes
				  INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				WHERE
				  tab_descontos_concessoes.con_id = $condominio AND
				  tab_descontos_concessoes.dec_data_termino IS NULL AND
				  tab_descontos_concessoes.dcs_id = 2 AND
				  tab_descontos.pls_id = $servico";
      $query = $this->bancodedados->query($query);
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        // Se o desconto é em porcentagem, converte para valor
        if ($dados["mensalidade"] <= 1) {
        	$dados["mensalidade"] = $dados["mensalidade"] * $mensalidade;
        }
        $desconto_total += $dados["mensalidade"];
      }
      return $desconto_total;
    }
  }

  function buscaDescontosAssinatura ($assinatura, $plano, $mensalidade) {
    $query = "
      SELECT
        tab_descontos_concessoes.dec_mensalidade AS mensalidade
      FROM
        tab_descontos_concessoes
        INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
      WHERE
        tab_descontos_concessoes.ass_id = $assinatura AND
        tab_descontos_concessoes.dec_validade IS NULL AND
        tab_descontos_concessoes.dcs_id = 2";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      // Se o desconto é em porcentagem, converte para valor
      if ($dados["mensalidade"] <= 1) {
      	$dados["mensalidade"] = $dados["mensalidade"] * $mensalidade;
      }
      $desconto_total += $dados["mensalidade"];
    }
    return $desconto_total;
  }

  function buscaContratoPontos ($contrato) {
    $query = "
			SELECT
			  COUNT(tab_clientes_pontos.clp_id) AS pontos
			FROM
			  rel_ass_cor_estacontido
			  INNER JOIN tab_clientes_pontos ON (rel_ass_cor_estacontido.ass_id = tab_clientes_pontos.ass_id)
			WHERE
			  rel_ass_cor_estacontido.cor_id = '$contrato'
			GROUP BY
				tab_clientes_pontos.clp_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["pontos"];
    }
  }

}

?>
