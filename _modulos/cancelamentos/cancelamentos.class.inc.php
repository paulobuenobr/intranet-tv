<?

require_once("_global/modulo.class.inc.php");

class cancelamentos extends modulo {

  function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
      $this->buscaDadosAcao(!$_GET["a"] ? "listar" : $_GET["a"]);
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function buscaDadosCliente($cliente_id) {
    $query = "
      SELECT
        tab_clientes.cli_id AS id,
        tab_clientes.cli_nome AS nome,
        tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
        tab_clientes.clt_id AS cliente_tipo_id,
        tab_clientes.cli_complemento AS complemento,
      	IF(tab_clientes.cep_id IS NULL, IF(tab_condominios_blocos.cep_id IS NULL, tab_condominios.con_numero, tab_condominios_blocos.cob_numero), tab_clientes.cli_numero) AS numero,
        INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
        tab_estados.est_sigla AS estado,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro,
        tab_condominios.con_nome AS condominio_nome,
        tab_condominios_blocos.cob_nome AS bloco_nome
      FROM
        tab_clientes
    		LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
    		LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
    		INNER JOIN tab_ceps ON (IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
    		INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
    		INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
    		INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
    		INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["complemento"]) {
        $complemento_tipos = $this->buscaComplementoTipos();
        $dados["complemento_tipo_id"] = $dados["complemento"][0];
        $dados["complemento_tipo_nome"] = $complemento_tipos[$dados["complemento_tipo_id"]];
        $dados["complemento_valor"] = intval(substr($dados["complemento"], 1, strlen($dados["complemento"])));
        if ($dados["complemento_tipo_id"] != "o") {
          $dados["complemento"] = "{$dados["complemento_tipo_nome"]} {$dados["complemento_valor"]}";
        } else {
          $dados["complemento"] = $dados["complemento_valor"];
        }
      }
      $dados["endereco"] = $this->geraEnderecoFormatado($dados);
      return $dados;
    }
  }

  function buscaContatosEmails($cliente_id) {
    $query = "
      SELECT
        CONCAT(ccm_usuario, '@', ccm_dominio) AS contato_email
      FROM
        tab_clientes_contatos_emails
      WHERE
        cli_id = $cliente_id
      ORDER BY
        contato_email";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[] = $dados["contato_email"];
    }
    return $lista;
  }

  function buscaComplementoTipos() {
    $tipos["a"] = "Apto";
    $tipos["l"] = "Loja";
    $tipos["s"] = "Sala";
    $tipos["c"] = "Casa";
    $tipos["o"] = "Outro";
    return $tipos;
  }

  function geraEnderecoFormatado($endereco) {
    if ($endereco["condominio_nome"]) {
      $endereco_formatado = $endereco["condominio_nome"];
      if ($endereco["complemento"]) {
        $endereco_formatado .= ", ".$endereco["complemento"];
        if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único") $endereco_formatado .= " / ".$endereco["bloco_nome"];
      } else {
        if ($endereco["bloco_nome"] && $endereco["bloco_nome"] != "Único") $endereco_formatado .= $endereco["bloco_nome"];
      }
    } else {
      if ($endereco["logradouro"]) $endereco_formatado = $endereco["logradouro"];
      if ($endereco["numero"]) $endereco_formatado .= ", ".$endereco["numero"];
      if ($endereco["complemento"]) $endereco_formatado .= ", ".$endereco["complemento"];
      if ($endereco["bairro"]) $endereco_formatado .= ", ".$endereco["bairro"];
      if ($endereco["cidade"]) $endereco_formatado .= ", ".$endereco["cidade"];
      if ($endereco["estado"]) $endereco_formatado .= ", ".$endereco["estado"];
      if ($endereco["cep"]) $endereco_formatado .= ", ".$endereco["cep"];
    }
    return $endereco_formatado;
  }

  function buscaDadosUsuario($usuario_id) {
    $query = "
      SELECT
        usu_id AS id,
        usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosGrupo($grupo_id) {
    $query = "
      SELECT
        gru_id AS id,
        gru_nome AS nome
      FROM
        tab_grupos
      WHERE
        gru_id = $grupo_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function usuarioGrupoFazParte($grupo_id, $usuario_id) {
    $query = "
      SELECT
        usu_id AS id
      FROM
        rel_usu_gru_fazparte
      WHERE
        gru_id = $grupo_id AND
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosServico($servico_id) {
    $query = "
      SELECT
        pls_id AS id,
        pls_nome AS nome
      FROM
        tab_planos_servicos
      WHERE
        pls_id = $servico_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaDadosItem($cancelamento_id) {
    $query = "
      SELECT
        tab_cancelamentos.can_id AS id,
        tab_cancelamentos.cli_id AS cliente_id,
        tab_contratos.cor_id AS contrato_id,
        tab_contratos.cor_codigo AS contrato_codigo,
        tab_planos_servicos.pls_id AS contrato_servico_id,
        tab_planos_servicos.pls_nome AS contrato_servico,
        DATE_FORMAT(tab_cancelamentos.can_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_cancelamentos.can_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tab_cancelamentos_origens.cao_id AS origem_id,
        tab_cancelamentos_origens.cao_nome AS origem_nome,
        tab_cancelamentos_motivos.cam_id AS motivo_id,
        tab_cancelamentos_motivos.cam_nome AS motivo_nome,
        tab_cancelamentos_status.cas_id AS status_id,
        tab_cancelamentos_status.cas_nome AS status_nome,
        DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS contrato_ativacao,
        tab_contratos.cor_vigencia AS vigencia,
        tab_contratos.cor_termino_fidelidade AS termino_fidelidade,
        tab_contratos.cor_multa AS multa,
        DATE_FORMAT(tab_contratos.cor_termino_fidelidade, '%d/%m/%Y') AS vigencia_expiracao,
        IF(tab_contratos.cor_termino_fidelidade >= CURDATE(), 1, 0) AS vigencia_ativa
      FROM
        tab_cancelamentos
		INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_cancelamentos.cli_id)
        LEFT OUTER JOIN tab_cancelamentos_origens ON (tab_cancelamentos_origens.cao_id = tab_cancelamentos.cao_id)
        LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos_motivos.cam_id = tab_cancelamentos.cam_id)
        INNER JOIN tab_cancelamentos_status ON (tab_cancelamentos_status.cas_id = tab_cancelamentos.cas_id)
        LEFT OUTER JOIN tab_contratos ON (tab_contratos.cor_id = tab_cancelamentos.cor_id)
        LEFT OUTER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
      WHERE
        tab_cancelamentos.can_id = $cancelamento_id".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	$dados_cliente = $this->buscaDadosCliente($dados["cliente_id"]);
      if ($dados_cliente) {
        $this->dados_item = funcoes::mescla_arrays($dados_cliente, $dados);
        $this->dados_item["etapas"] = $this->buscaEtapas($cancelamento_id);
        $ultima_etapa_confirmacao_formal = $this->buscaUltimaEtapaConfirmacaoFormal($cancelamento_id);
        foreach ($this->dados_item["etapas"] as $etapa) {
          // Etapas Cumpridas
          if (!($etapa["tipo_id"] == 6 && $etapa["parametros"]["situacao"] == 3) && $etapa["tipo_id"] != 7 && $etapa["tipo_id"] != 8) {
            $this->dados_item["etapas_cumpridas"][$etapa["tipo_id"]] = true;
          }
          if ($ultima_etapa_confirmacao_formal) {
            $this->dados_item["etapas_cumpridas"][$ultima_etapa_confirmacao_formal] = true;
          }
          // Etapas Em Andamento
          if ($etapa["status_id"] == 1) {
            if ($etapa["tipo_id"] == 4 && $etapa["parametros"]["usuario"] == $_SESSION["usuario"] && $this->permissoes[$this->dados_modulo["nome"]]["tomar_ciencia"]) {
              $this->dados_item["etapas_em_andamento"]["ciencia_pessoal"][] = $etapa["id"];
              $alerta = ' <font color="#FF0000">(Aguardando Ciência)</font>';
            } elseif ($etapa["tipo_id"] == 9 && $this->permissoes[$this->dados_modulo["nome"]]["tomar_ciencia_comercial"]) {
              $this->dados_item["etapas_em_andamento"]["ciencia_comercial"][] = $etapa["id"];
              $alerta = ' <font color="#FF0000">(Aguardando Ciência)</font>';
            } elseif ($etapa["tipo_id"] == 6 && $this->usuarioGrupoFazParte($etapa["parametros"]["grupo"], $_SESSION["usuario"]) && $this->permissoes[$this->dados_modulo["nome"]]["desconto_aprovar"] && $this->permissoes[$this->dados_modulo["nome"]]["desconto_reprovar"]) {
              $this->dados_item["etapas_em_andamento"]["desconto"][] = $etapa["id"];
              $alerta = ' <font color="#FF0000">(Aguardando Aprovação)</font>';
            } else {
              $this->dados_item["etapas_em_andamento"]["outro"][] = $etapa["id"];
            }
          }
        }
        reset($this->dados_item["etapas"]);
        $this->dados_item["assinaturas"] = $this->buscaAssinaturas($dados["contrato_id"]);
        $this->dados_item["equipamentos"] = $this->buscaEquipamentosComodato($dados["contrato_id"]);
        $vigencia_filhos = $this->buscaVigenciaAtivaFilhos($dados["contrato_id"]);
        if ($vigencia_filhos) {
	        $this->dados_item["prazo_vigencia"] = $this->formataPrazoVigencia(1, $vigencia_filhos["vigencia"], $vigencia_filhos["vigencia_expiracao"], $vigencia_filhos["multa"], $vigencia_filhos["termino_fidelidade"]);
	        $this->dados_item["vigencia_ativa"] = 1;
	      } elseif ($dados["vigencia_ativa"] || !$dados["formas_pagamento"]) {
        	$this->dados_item["prazo_vigencia"] = $this->formataPrazoVigencia($dados["vigencia_ativa"], $dados["vigencia"], $dados["vigencia_expiracao"], $dados["multa"], $dados["termino_fidelidade"]);
	      }
      }
    } else {
      unset($this->dados_item);
    }
  }
  
  function buscaVigenciaAtivaFilhos($contrato_id) {
    $query = "
      SELECT
        DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS data_ativacao,
        DATE_FORMAT(DATE_SUB(DATE_ADD(tab_contratos.cor_data_ativacao, INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), '%d/%m/%Y') AS vigencia_expiracao,
        tab_contratos.cor_vigencia AS vigencia,
        tab_contratos.cor_multa AS multa,
        tab_contratos.cor_termino_fidelidade AS termino_fidelidade
      FROM
        tab_contratos
      WHERE
        tab_contratos.cor_pai = $contrato_id AND
        tab_contratos.cor_multa > 0 AND
        tab_contratos.cor_termino_fidelidade >= CURDATE()
      ORDER BY
        tab_contratos.cor_id DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function buscaUltimaEtapaConfirmacaoFormal($cancelamento_id) {
    $query = "
      SELECT
        cet_id AS tipo_id
      FROM
        tab_cancelamentos_etapas
      WHERE
        cet_id IN (7, 8) AND
        can_id = $cancelamento_id
      ORDER BY
        cae_inicio DESC";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "tipo_id");
  }

  function buscaAssinaturas($contrato_id) {
    $query = "
      SELECT
        ass_id AS id
      FROM
        rel_ass_cor_estacontido
      WHERE
        cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $assinaturas[] = $dados["id"];
      $assinaturas_vinculadas = $this->buscaAssinaturasVinculadas($dados["id"]);
      if ($assinaturas_vinculadas) {
        $assinaturas = funcoes::mescla_arrays($assinaturas, $assinaturas_vinculadas);
      }
    }
    return $assinaturas;
  }

  function buscaAssinaturasVinculadas($assinatura_id) {
    $query = "
      SELECT
        tab_assinaturas_vinculos.ass_filha AS id
      FROM
        tab_assinaturas_vinculos
        INNER JOIN tab_assinaturas_vinculos vinculos_pai ON (tab_assinaturas_vinculos.ass_filha = vinculos_pai.ass_filha)
      WHERE
        tab_assinaturas_vinculos.ass_id = $assinatura_id
      HAVING
        COUNT(vinculos_pai.ass_id) = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $assinaturas[] = $dados["id"];
    }
    return $assinaturas;
  }

  function formataPrazoVigencia($vigencia_ativa, $vigencia, $data_expiracao, $multa, $termino_fidelidade = null) {
    $vigencia_formatada = $this->formataVigencia($vigencia, $termino_fidelidade);
    if ($vigencia) {
      if ($data_expiracao) {
        $vigencia_formatada .= $vigencia_ativa ? " - Até $data_expiracao" : " - <font color=\"red\">Expirada em $data_expiracao</font>";
      }
      $vigencia_formatada .= " - Multa Rescisória: ".$this->formataDinheiro($multa);
    } elseif ($termino_fidelidade) {
      if ($vigencia_ativa) {
        $vigencia_formatada = "Até $vigencia_formatada";
      } else {
        $vigencia_formatada = "<font color=\"red\">Expirada em $vigencia_formatada</font>";
      }
      $vigencia_formatada .= " - Multa Rescisória: ".$this->formataDinheiro($multa);
    }
    return $vigencia_formatada;
  }
  
  function formataVigencia($vigencia, $termino_fidelidade) {
    if ($vigencia) {
      return $vigencia.($vigencia > 1 ? " meses" : " mês");
    } elseif ($termino_fidelidade) {
      return date("d/m/Y", strtotime($termino_fidelidade));
    } else {
      return "Contrato sem Plano Fidelidade";
    }
  }
  
  function buscaCobrancasAtrasadas($cliente_id, $quantidade_maxima = 0) {
    return false;
    $query = "
      SELECT
        count(coa_id) AS quantidade
      FROM
        tab_cobrancas
      WHERE
        cli_id = $cliente_id AND
        cot_id = 1 AND
        coa_data_vencimento < DATE_SUB(CURDATE(), INTERVAL ".COBRANCA_BLOQUEIO_GERENCIAMENTO_DO_CLIENTE." DAY)";
    $query = $this->bancodedados->query($query);
    $cobrancas = $this->bancodedados->resultadoArray($query);
    if ($cobrancas) {
      return ($cobrancas["quantidade"] > $quantidade_maxima) ? $cobrancas["quantidade"] : false;
    } else {
      return false;
    }
  }
  
  function buscaEquipamentosComodato($contrato_id) {
    $equipamentos = Array();
    $query="
    	SELECT 
    		tab_equipamentos.equ_nome AS nome
    	FROM
    		tab_clientes_comodatos
    		INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
 		WHERE 
 			tab_clientes_comodatos.cor_id = $contrato_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $equipamentos[] = $dados["nome"];
    }
    return $equipamentos;
  }
    
  ###################
  ### Busca Itens ###
  ###################

  function buscaOrigens() {
    $query = "
      SELECT
        cao_id AS id,
        cao_nome AS nome
      FROM
        tab_cancelamentos_origens
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $origens[$dados["id"]] = $dados["nome"];
    }
    return $origens;
  }

  function buscaMotivos($status_id = null) {
    $query = "
      SELECT
        cam_id AS id,
        cam_nome AS nome
      FROM
        tab_cancelamentos_motivos".($status_id ? "
      WHERE
        cms_id = $status_id" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $motivos[$dados["id"]] = $dados["nome"];
    }
    return $motivos;
  }

  function buscaCondominios() {
    $query = "
      SELECT
        con_id AS id,
        CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, '/', tab_bairros.bai_nome, ')') AS nome
      FROM
        tab_condominios
        INNER JOIN tab_logradouros ON (tab_condominios.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_condominios.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_condominios.cid_id = tab_cidades.cid_id)
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

  function buscaStatus() {
    $query = "
      SELECT
        cas_id AS id,
        cas_nome AS nome
      FROM
        tab_cancelamentos_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaServicos() {
    $query = "
      SELECT
        pls_id AS id,
        pls_nome AS nome
      FROM
        tab_planos_servicos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servicos[] = $dados;
    }
    return $servicos;
  }

  ##############
  ### Etapas ###
  ##############

  function buscaEtapas($id) {
    $query = "
      SELECT
        tab_cancelamentos_etapas.cae_id AS id,
        DATE_FORMAT(tab_cancelamentos_etapas.cae_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_cancelamentos_etapas.cae_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tab_cancelamentos_etapas.cae_parametros AS parametros,
        tab_usuarios.usu_id AS responsavel_id,
        tab_usuarios.usu_nome AS responsavel_nome,
        tab_cancelamentos_etapas_tipos.cet_id AS tipo_id,
        tab_cancelamentos_etapas_tipos.cet_nome AS tipo_nome,
        tab_cancelamentos_etapas_niveis.cen_id AS nivel_id,
        tab_cancelamentos_etapas_niveis.cen_nome AS nivel_nome,
        tab_cancelamentos_etapas_status.ces_id AS status_id,
        tab_cancelamentos_etapas_status.ces_nome AS status_nome
      FROM
        tab_cancelamentos_etapas
        INNER JOIN tab_usuarios ON (tab_cancelamentos_etapas.usu_id = tab_usuarios.usu_id)
        INNER JOIN tab_cancelamentos_etapas_tipos ON (tab_cancelamentos_etapas.cet_id = tab_cancelamentos_etapas_tipos.cet_id)
        INNER JOIN tab_cancelamentos_etapas_niveis ON (tab_cancelamentos_etapas.cen_id = tab_cancelamentos_etapas_niveis.cen_id)
        INNER JOIN tab_cancelamentos_etapas_status ON (tab_cancelamentos_etapas.ces_id = tab_cancelamentos_etapas_status.ces_id)
      WHERE
        tab_cancelamentos_etapas.can_id = $id
      ORDER BY
        tab_cancelamentos_etapas.cae_inicio ASC,
        tab_cancelamentos_etapas.cae_id ASC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      $dados["conteudo"] = $this->geraEtapaConteudo($dados);
      $etapas[] = $dados;
    }
    return $etapas;
  }

  function extraiEtapaParametros($tipo, $variaveis) {
    switch ($tipo) {
      case (1): {
        $parametros["origem"] = $variaveis["etapa_conteudo_1_origem"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_1_comentario"]);
        break;
      }
      case (2): {
        $parametros["motivo"] = $variaveis["etapa_conteudo_2_motivo"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_2_comentario"]);
        break;
      }
      case (3): {
        $parametros["descricao"] = trim($variaveis["etapa_conteudo_3_descricao"]);
        break;
      }
      case (4): {
        $parametros["usuario"] = $variaveis["etapa_conteudo_4_usuario"];
        $parametros["descricao"] = trim($variaveis["etapa_conteudo_4_descricao"]);
        $parametros["descarte_etapa_3_motivo"] = trim($variaveis["etapa_conteudo_4_conteudo_descarte_etapa_3_motivo"]);
        break;
      }
      case (5): {
        $parametros["data"] = $variaveis["etapa_conteudo_5_data"];
        $parametros["periodo"] = $variaveis["etapa_conteudo_5_periodo"];
        $parametros["local"] = $variaveis["etapa_conteudo_5_local"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_5_comentario"]);
        break;
      }
      case (6): {
        $parametros["motivo"] = $variaveis["etapa_conteudo_6_motivo"];
        $parametros["duracao"] = $variaveis["etapa_conteudo_6_duracao"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_6_comentario"]);
        $parametros["descarte_etapa_3_motivo"] = trim($variaveis["etapa_conteudo_6_conteudo_descarte_etapa_3_motivo"]);
        $parametros["descarte_etapa_4_motivo"] = trim($variaveis["etapa_conteudo_6_conteudo_descarte_etapa_4_motivo"]);
        break;
      }
      case (7): {
        $parametros["data"] = $variaveis["etapa_conteudo_7_data"];
        $parametros["formato"] = $variaveis["etapa_conteudo_7_formato"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_7_comentario"]);
        break;
      }
      case (8): {
        $parametros["data"] = $variaveis["etapa_conteudo_8_data"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_8_comentario"]);
        break;
      }
      case (9): {
        $parametros["novo_endereco"] = trim($variaveis["etapa_conteudo_9_novo_endereco"]);
        $parametros["mudar_cobranca"] = ($variaveis["etapa_conteudo_9_mudar_cobranca"]) ? true : false;
        $parametros["novo_morador"] = trim($variaveis["etapa_conteudo_9_novo_morador"]);
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_9_comentario"]);
        break;
      }
      case (10): {
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_10_comentario"]);
        break;
      }
      case (11): {
        $parametros["origem"] = $variaveis["etapa_conteudo_11_origem"];
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_11_comentario"]);
        break;
      }
      case (12): {
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_12_comentario"]);
        break;
      }
      case (13): {
        $parametros["comentario"] = trim($variaveis["etapa_conteudo_13_comentario"]);
        break;
      }
      case (14): {
        $parametros["emails"] = $_POST["emails"];
        $parametros["comentario"] = $_POST["comentario"];
        $parametros["data_cancelamento"] = $_POST["data_cancelamento"];
        break;
      }
    }
    if ($variaveis["descartada"]) $parametros["descartada"] = true;
    return $parametros;
  }

  function registraEtapa($id, $tipo, $parametros, $nivel = 3) {
    switch ($tipo) {
      case (1): {
        $auto_finalizada = true;
        $query = "UPDATE tab_cancelamentos SET cao_id = {$parametros["origem"]} WHERE can_id = $id";
        $this->bancodedados->query($query);
        break;
      }
      case (2): {
        $auto_finalizada = true;
        $query = "UPDATE tab_cancelamentos SET cam_id = {$parametros["motivo"]} WHERE can_id = $id";
        $this->bancodedados->query($query);
        break;
      }
      case (3): {
        $auto_finalizada = true;
        break;
      }
      case (4): {
        $auto_finalizada = ($parametros["usuario"] && !$parametros["descartada"]) ? false : true;
        if (!$this->dados_item["etapas_cumpridas"][3] && !$parametros["descartada"]) {
          $this->registraEtapa($id, 3, array("descartada" => true, "motivo" => $parametros["descarte_etapa_3_motivo"]));
        }
        unset($parametros["descarte_etapa_3_motivo"]);
        break;
      }
      case (5): {
        $auto_finalizada = true;
        break;
      }
      case (6): {
        $auto_finalizada = false;
        $parametros["situacao"] = 1;
        $parametros["grupo"] = $this->buscaEtapasTipos6ReponsavelMotivo($parametros["motivo"]);
        if (!$this->dados_item["etapas_cumpridas"][3]) {
          $this->registraEtapa($id, 3, array("descartada" => true, "motivo" => $parametros["descarte_etapa_3_motivo"]));
        }
        unset($parametros["descarte_etapa_3_motivo"]);
        if (!$this->dados_item["etapas_cumpridas"][4]) {
          $this->registraEtapa($id, 4, array("descartada" => true, "motivo" => $parametros["descarte_etapa_4_motivo"]));
        }
        unset($parametros["descarte_etapa_4_motivo"]);
        break;
      }
      case (7): {
        $auto_finalizada = true;
        break;
      }
      case (8): {
        $auto_finalizada = true;
        break;
      }
      case (9): {
        $auto_finalizada = false;
        break;
      }
      case (10): {
        $auto_finalizada = true;
        for ($i = 0; $i < sizeof($this->dados_item["etapas_em_andamento"]["ciencia_pessoal"]); $i++) {
          $query = "UPDATE tab_cancelamentos_etapas SET cae_termino = NOW(), ces_id = 2 WHERE cae_id = {$this->dados_item["etapas_em_andamento"]["ciencia_pessoal"][$i]}";
          $this->bancodedados->query($query);
        }
        for ($i = 0; $i < sizeof($this->dados_item["etapas_em_andamento"]["ciencia_comercial"]); $i++) {
          $query = "UPDATE tab_cancelamentos_etapas SET cae_termino = NOW(), ces_id = 2 WHERE cae_id = {$this->dados_item["etapas_em_andamento"]["ciencia_comercial"][$i]}";
          $this->bancodedados->query($query);
        }
        break;
      }
      case (11): {
        $auto_finalizada = true;
        break;
      }
      case (12): {
        $auto_finalizada = true;
        for ($i = 0; $i < sizeof($this->dados_item["etapas_em_andamento"]["desconto"]); $i++) {
          for ($j = 0; $j < sizeof($this->dados_item["etapas"]); $j++) {
            if ($this->dados_item["etapas"][$j]["id"] == $this->dados_item["etapas_em_andamento"]["desconto"][$i]) {
              $this->dados_item["etapas"][$j]["parametros"]["situacao"] = 2;
              $parametros[$i] = @serialize($this->dados_item["etapas"][$j]["parametros"]);
            }
          }
          $query = "UPDATE tab_cancelamentos_etapas SET cae_termino = NOW(), ces_id = 2, cae_parametros = '".addslashes($parametros[$i])."' WHERE cae_id = {$this->dados_item["etapas_em_andamento"]["desconto"][$i]}";
          $this->bancodedados->query($query);
        }
        break;
      }
      case (13): {
        $auto_finalizada = true;
        for ($i = 0; $i < sizeof($this->dados_item["etapas_em_andamento"]["desconto"]); $i++) {
          for ($j = 0; $j < sizeof($this->dados_item["etapas"]); $j++) {
            if ($this->dados_item["etapas"][$j]["id"] == $this->dados_item["etapas_em_andamento"]["desconto"][$i]) {
              $this->dados_item["etapas"][$j]["parametros"]["situacao"] = 3;
              $parametros[$i] = @serialize($this->dados_item["etapas"][$j]["parametros"]);
            }
          }
          $query = "UPDATE tab_cancelamentos_etapas SET cae_termino = NOW(), ces_id = 2, cae_parametros = '".addslashes($parametros[$i])."' WHERE cae_id = {$this->dados_item["etapas_em_andamento"]["desconto"][$i]}";
          $this->bancodedados->query($query);
        }
        break;
      }
      case (14) : {
        $auto_finalizada = true;
      }
    }
    if ($auto_finalizada) {
      $termino = "NOW()";
      $status = "2";
    } else {
      $termino = "NULL";
      $status = "1";
    }
    if ($parametros["usuario"]) $parametros["usuario"] = intval($parametros["usuario"]);
    $parametros = addslashes(serialize($parametros));
    $query = "INSERT INTO tab_cancelamentos_etapas (can_id, cae_inicio, cae_termino, cae_parametros, usu_id, cet_id, cen_id, ces_id) VALUES ($id, NOW(), $termino, '$parametros', {$_SESSION["usuario"]}, $tipo, $nivel, $status)";
    $this->bancodedados->query($query);
    $id_etapa = $this->bancodedados->ultimoIdInserido();
    $query = "UPDATE tab_cancelamentos SET cae_id = $id_etapa WHERE can_id = $id";
    $this->bancodedados->query($query);
  }

  function geraEtapaConteudo($etapa) {
    // Etapa Descartada
    if ($etapa["parametros"]["descartada"]) $alerta = ' (Etapa Descartada)';
    // Conteudo
    $conteudo  = "<b>{$etapa["responsavel_nome"]}</b> ".($etapa["termino"] && $etapa["termino"] != $etapa["inicio"] ? "de <b>{$etapa["inicio"]}</b> a <b>{$etapa["termino"]}</b>" : "em <b>{$etapa["inicio"]}</b>");
    $conteudo .= "<br><b>&nbsp;&raquo; {$etapa["tipo_nome"]}</b>$alerta";
    if ($etapa["parametros"]["descartada"]) {
      if ($etapa["parametros"]["motivo"]) $conteudo .= "<br><b>&nbsp;&raquo; Motivo:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["motivo"])."<br>";
    } else {
      switch ($etapa["tipo_id"]) {
        case 1: {
          if ($etapa["parametros"]["origem"]) {
            $origens = $this->buscaOrigens();
            $conteudo .= "<b>:</b> ".$origens[$etapa["parametros"]["origem"]];
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 2: {
          if ($etapa["parametros"]["motivo"]) {
            $motivos = $this->buscaMotivos();
            $conteudo .= "<b>:</b> ".$motivos[$etapa["parametros"]["motivo"]];
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 3: {
          if ($etapa["parametros"]["descricao"]) {
            $conteudo .= "<b>:</b> ".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["descricao"])."<br>";
          }
          break;
        }
        case 4: {
          if ($etapa["parametros"]["usuario"]) {
            $usuario = $this->buscaDadosUsuario($etapa["parametros"]["usuario"]);
            $conteudo .= "<br><b>&nbsp;&raquo; Usuário:</b> ".$usuario["nome"];
          }
          if ($etapa["parametros"]["descricao"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Descrição:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["descricao"]);
          }
          break;
        }
        case 5: {
          if ($etapa["parametros"]["data"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Data:</b> ".$etapa["parametros"]["data"];
          }
          if ($etapa["parametros"]["periodo"]) {
            $periodos = $this->buscaEtapasTipos5Periodos();
            $conteudo .= "<br><b>&nbsp;&raquo; Período:</b> ".$periodos[$etapa["parametros"]["periodo"]];
          }
          if ($etapa["parametros"]["local"]) {
            $locais = $this->buscaEtapasTipos5Locais();
            $conteudo .= "<br><b>&nbsp;&raquo; Local:</b> ".$locais[$etapa["parametros"]["local"]];
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 6: {
          if ($etapa["parametros"]["motivo"]) {
            $motivos = $this->buscaEtapasTipos6Motivos();
            $conteudo .= "<br><b>&nbsp;&raquo; Motivo:</b> ".$motivos[$etapa["parametros"]["motivo"]];
          }
          if ($etapa["parametros"]["duracao"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Duração:</b> ".$etapa["parametros"]["duracao"]." dia(s)";
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          if ($etapa["parametros"]["grupo"] || $etapa["parametros"]["situacao"]) {
            $conteudo .= '<hr style="height:1;color:#505050">';
          }
          if ($etapa["parametros"]["grupo"]) {
            $usuario = $this->buscaDadosGrupo($etapa["parametros"]["grupo"]);
            $conteudo .= "<b>&nbsp;&raquo; Responsável:</b> ".$usuario["nome"];
          }
          if ($etapa["parametros"]["situacao"]) {
            $situacoes = $this->buscaEtapasTipos6Situacoes();
            $conteudo .= ($etapa["parametros"]["grupo"] ? "<br>" : "")."<b>&nbsp;&raquo; Situação:</b> ".$situacoes[$etapa["parametros"]["situacao"]];
          }
          break;
        }
        case 7: {
          if ($etapa["parametros"]["data"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Data:</b> ".$etapa["parametros"]["data"];
          }
          if ($etapa["parametros"]["formato"]) {
            $formatos = $this->buscaEtapasTipos7Formatos();
            $conteudo .= "<br><b>&nbsp;&raquo; Formato:</b> ".$formatos[$etapa["parametros"]["formato"]];
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 8: {
          if ($etapa["parametros"]["data"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Data:</b> ".$etapa["parametros"]["data"];
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 9: {
          if ($etapa["parametros"]["novo_endereco"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Novo Endereço:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["novo_endereco"]);
          }
          if ($etapa["parametros"]["mudar_cobranca"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Mudar Cobrança?</b> ".(($etapa["parametros"]["mudar_cobranca"]) ? "Sim" : "Não");
          }
          if ($etapa["parametros"]["novo_morador"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Novo Morador:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["novo_morador"]);
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 10: {
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 11: {
          if ($etapa["parametros"]["origem"]) {
            $origens = $this->buscaOrigens();
            $conteudo .= "<br><b>&nbsp;&raquo; Origem:</b> ".$origens[$etapa["parametros"]["origem"]];
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 12: {
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 13: {
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          break;
        }
        case 14: {
          if ($etapa["parametros"]["emails"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; E-mail(s):</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["emails"]);
          }
          if ($etapa["parametros"]["comentario"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Comentário:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["comentario"]);
          }
          if ($etapa["parametros"]["data_cancelamento"]) {
            $conteudo .= "<br><b>&nbsp;&raquo; Data Cancelamento:</b>&nbsp;".preg_replace("/[ \r\n\t]+/", " ", $etapa["parametros"]["data_cancelamento"]);
          }
          break;
        }
      }
    }
    return $conteudo;
  }

  function geraEtapasListagem($comentarios) {
    $cabecalho = array ("conteudo" => "Conteúdo", "nivel_nome" => "Nível de Stress", "status_nome" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);
    return $listagem->conteudo;
  }

  ####################
  ### Etapas Tipos ###
  ####################

  function buscaEtapaTipos() {
    $query = "
      SELECT
        cet_id AS id,
        cet_nome AS nome
      FROM
        tab_cancelamentos_etapas_tipos
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function filtraEtapasTiposPossiveis($tipos) {
    // 1
    if ($this->dados_item["etapas_cumpridas"][1]) unset($tipos[1]);
    // 2
    //if ($this->dados_item["etapas_cumpridas"][2] || !$this->dados_item["etapas_cumpridas"][1] || (!$this->dados_item["etapas_cumpridas"][7] && $this->dados_item["origem_id"] == 4)) unset($tipos[2]);
    // 3
    if (!$this->dados_item["etapas_cumpridas"][1] || !$this->dados_item["etapas_cumpridas"][2]) unset($tipos[3]);
    // 4
    if (!$this->dados_item["etapas_cumpridas"][1] || !$this->dados_item["etapas_cumpridas"][2]) unset($tipos[4]);
    // 5
    if (!$this->dados_item["etapas_cumpridas"][1] || !$this->dados_item["etapas_cumpridas"][2]) unset($tipos[5]);
    // 6
    if ($this->dados_item["etapas_cumpridas"][6] || !$this->dados_item["etapas_cumpridas"][1] || !$this->dados_item["etapas_cumpridas"][2]) unset($tipos[6]);
    // 7
    if ($this->dados_item["etapas_cumpridas"][7] || !$this->dados_item["etapas_cumpridas"][1]) unset($tipos[7]);
    // 8
    if ($this->dados_item["etapas_cumpridas"][8] || !$this->dados_item["etapas_cumpridas"][1] || !$this->dados_item["etapas_cumpridas"][7]) unset($tipos[8]);
    // 9
    if ($this->dados_item["etapas_cumpridas"][9] || !$this->dados_item["etapas_cumpridas"][1] || !$this->dados_item["etapas_cumpridas"][2]) unset($tipos[9]);
    // 10
    unset($tipos[10]);
    // 11
    unset($tipos[11]);
    // 12
    unset($tipos[12]);
    // 13
    unset($tipos[13]);
    // 14
    unset($tipos[14]);
    return $tipos;
  }

  function buscaEtapaNiveis() {
    $query = "SELECT cen_id AS id, cen_nome AS nome FROM tab_cancelamentos_etapas_niveis ORDER BY id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$niveis[$dados["id"]] = $dados["nome"];
    }
    return $niveis;
  }

  function buscaEtapaStatus() {
    $query = "SELECT ces_id AS id, ces_nome AS nome FROM tab_cancelamentos_etapas_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaEtapasTipos4Usuarios() {
    $query = "SELECT tab_usuarios.usu_id AS id, tab_usuarios.usu_nome AS nome FROM tab_acoes INNER JOIN rel_gru_aco_tempermissao ON (tab_acoes.aco_id = rel_gru_aco_tempermissao.aco_id) INNER JOIN rel_usu_gru_fazparte ON (rel_gru_aco_tempermissao.gru_id = rel_usu_gru_fazparte.gru_id) INNER JOIN tab_usuarios ON (rel_usu_gru_fazparte.usu_id = tab_usuarios.usu_id) WHERE (tab_acoes.aco_id = 148) AND (tab_usuarios.usu_id <> '{$_SESSION["usuario"]}') AND (tab_usuarios.uss_id = 1) ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$usuarios[$dados["id"]] = $dados["nome"];
    }
    return $usuarios;
  }

  function buscaEtapasTipos5Periodos() {
    $periodos[1] = "Manhã";
    $periodos[2] = "Tarde";
    $periodos[3] = "Noite";
    $periodos[4] = "Indiferente";
    return $periodos;
  }

  function buscaEtapasTipos5Locais() {
    $locais[1] = "Na Empresa";
    $locais[2] = "No Cliente";
    $locais[3] = "Por Telefone";
    return $locais;
  }

  function buscaEtapasTipos6Motivos() {
    $motivos[1] = "Serviço oferecido funcionou mas estava ruim (no período)";
    $motivos[2] = "Serviço oferecido não funcionou (no período)";
    $motivos[3] = "Re-avaliação de qualidade do serviço oferecido";
    return $motivos;
  }

  function buscaEtapasTipos6ReponsavelMotivo($motivo) {
    $responsavel[1] = 12; // Gerente Técnico
    $responsavel[2] = 12; // Gerente Técnico
    $responsavel[3] = 13; // Diretor Técnico
    return $responsavel[$motivo];
  }

  function buscaEtapasTipos6Situacoes() {
    $motivos[1] = "Em Análise";
    $motivos[2] = "Aprovado";
    $motivos[3] = "Reprovado";
    return $motivos;
  }

  function buscaEtapasTipos7Formatos() {
    $formatos[1] = "Aviso Registrado (AR)";
    $formatos[2] = "Termo de Cancelamento (TC)";
    return $formatos;
  }

  function possuiAditivoPendente($contrato_id) {
    $query = "
      SELECT
        cor_id
      FROM
        tab_contratos
      WHERE
        cor_pai = $contrato_id AND
        crs_id < 19";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->linhas($query)) {
      return true;
    }
  }

  #############
  ### Geral ###
  #############

  function fechar() {
    unset($this->conteudo);
    if ($this->dados_item) $this->conteudo .= '<script type="text/javascript">if (window.opener != null && window.opener.document.getElementById("cancelamentos")) window.opener.buscaCancelamentosCliente('.$this->dados_item["cliente_id"].');</script>';
    $this->conteudo .= '<script type="text/javascript">window.close();</script>';
  }

  function ehEditavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["editar"] && $this->dados_item["status_id"] <= 2) return true;
    return false;
  }

  function ehCienciavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["tomar_ciencia"] && $this->dados_item["status_id"] <= 2 && ($this->dados_item["etapas_em_andamento"]["ciencia_pessoal"] || $this->dados_item["etapas_em_andamento"]["ciencia_comercial"])) return true;
    return false;
  }

  function ehFinalizavel() {
    if (!$this->permissoes[$this->dados_modulo["nome"]]["finalizar"]) return false;
    if ($this->dados_item["status_id"] == 2 && !$this->dados_item["etapas_em_andamento"] && $this->dados_item["etapas_cumpridas"][1] && $this->dados_item["etapas_cumpridas"][2]) return true;
    return false;
  }

  function ehAprovavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["desconto_aprovar"] && $this->dados_item["status_id"] == 2 && $this->dados_item["etapas_em_andamento"]["desconto"]) return true;
    return false;
  }

  function ehReprovavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["desconto_reprovar"] && $this->dados_item["status_id"] == 2 && $this->dados_item["etapas_em_andamento"]["desconto"]) return true;
    return false;
  }

  function ehRemovivel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["remover"] && $this->dados_item["status_id"] == 1) return true;
    return false;
  }

  function ehResolicitavel() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["resolicitar"] && $this->dados_item["status_id"] <= 2) return true;
    return false;
  }

  function ehVisualizavelRequerimento() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["visualizar_requerimento"] && $this->dados_item["status_id"] <= 2 && file_exists(DIR_RAIZ."modelos/requerimento_rescisao_{$this->dados_item["contrato_servico_id"]}_{$this->dados_item["cliente_tipo_id"]}.html")) return true;
    return false;
  }

  function ehEnviavelRequerimento() {
    if ($this->permissoes[$this->dados_modulo["nome"]]["enviar_requerimento"] && $this->dados_item["status_id"] <= 2 && file_exists(DIR_RAIZ."modelos/requerimento_rescisao_{$this->dados_item["contrato_servico_id"]}_{$this->dados_item["cliente_tipo_id"]}.html")) return true;
    return false;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
  	$dados_cliente = $this->buscaDadosCliente($_GET["cliente_id"]);
    if ($dados_cliente) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.adicionar.inc.php");
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&cliente_id={$dados_cliente["id"]}", $this->conteudo);
        // Conteúdo
        $this->conteudo = str_replace("[cliente]", $dados_cliente["nome"], $this->conteudo);
        $this->conteudo = str_replace("[endereco]", $dados_cliente["endereco"], $this->conteudo);
        $this->conteudo = str_replace("[etapa_conteudo_1_comentario]", $this->geraSelect($this->buscaOrigens(), null, true, "etapa_conteudo_1_origem", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[etapa_nivel]", $this->geraSelect($this->buscaEtapaNiveis(), 3, false, "etapa_nivel", "100%"), $this->conteudo);
        $this->conteudo = str_replace("[contratos]", $this->geraListagemContratos($this->buscaContratosAtivos($dados_cliente["id"])), $this->conteudo);
        $this->conteudo = str_replace("[mensagem_inadimplente]", (($cobrancas = $this->buscaCobrancasAtrasadas($dados_cliente["id"])) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none"), $this->conteudo);
        $this->conteudo = str_replace("[cobrancas]", $cobrancas, $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        // Insere Registro
        $query = "INSERT INTO tab_cancelamentos (cli_id, cor_id, can_inicio, cas_id) VALUES ({$dados_cliente["id"]}, {$_POST["contrato"]}, NOW(), 1)";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Registra Etapa 1
        $parametros = $this->extraiEtapaParametros(1, $_POST);
        $this->registraEtapa($id, 1, $parametros, $_POST["etapa_nivel"]);
        // Registra Atividades
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
        // Redicionamento
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
      }
    } else {
      $this->fechar();
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      // Conteudo
      $this->conteudo .= $this->carregaArquivo("cancelamentos.ver.inc.php");
      // Ações
      $acoes  = '<td bgcolor="#505050"><b>&nbsp;Ver&nbsp;</b></td>';
      $this->conteudo = str_replace("[acoes]", $acoes, $this->conteudo);
      // Conteúdo
      $this->conteudo = str_replace("[cliente_id]", $this->dados_item["cliente_id"], $this->conteudo);
      $this->conteudo = str_replace("[cliente_nome]", $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace("[endereco]", $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace("[contrato]", $this->dados_item["contrato_codigo"] ? $this->dados_item["contrato_codigo"]." (".$this->dados_item["contrato_servico"].")" : "Todos", $this->conteudo);
      $this->conteudo = str_replace("[mensagem_inadimplente]", (($cobrancas = $this->buscaCobrancasAtrasadas($this->dados_item["cliente_id"])) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none"), $this->conteudo);
      $this->conteudo = str_replace("[cobrancas]", $cobrancas, $this->conteudo);
      $this->conteudo = str_replace('[exibir_mensagem_vigencia]', $this->dados_item["vigencia_ativa"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
      $this->conteudo = str_replace('[vigencia]', $this->dados_item["prazo_vigencia"], $this->conteudo);
      $this->conteudo = str_replace('[exibir_mensagem_comodato]', $this->dados_item["equipamentos"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
      $this->conteudo = str_replace('[equipamentos_comodato]', implode(", ", $this->dados_item["equipamentos"]), $this->conteudo);
      $this->conteudo = str_replace("[origem]", $this->dados_item["origem_nome"], $this->conteudo);
      $this->conteudo = str_replace("[motivo]", $this->dados_item["motivo_nome"], $this->conteudo);
      $this->conteudo = str_replace("[inicio]", $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace("[termino]", $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace("[etapas]", $this->geraEtapasListagem($this->dados_item["etapas"]), $this->conteudo);
      $this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\'">';
      if ($this->ehCienciavel()) $botoes[] = '<input type="button" name="tomar_ciencia" class="buttonAzulEscuroBranco11" value="Tomar Ciência" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tomar_ciencia&i='.$this->dados_item["id"].'\'">';
      if ($this->ehResolicitavel()) $botoes[] = '<input type="button" name="resolicitar" class="buttonAzulEscuroBranco11" value="Re-solicitar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=resolicitar&i='.$this->dados_item["id"].'\'">';
      if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="desconto_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar Desconto" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desconto_aprovar&i='.$this->dados_item["id"].'\'">';
      if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="desconto_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar Desconto" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desconto_reprovar&i='.$this->dados_item["id"].'\'">';
      if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\'">';
      if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'">';
      if ($this->ehVisualizavelRequerimento()) $botoes[] = '<input type="button" name="visualizar_requerimento" class="buttonAzulEscuroBranco11" value="Visualizar Requerimento" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=visualizar_requerimento&i='.$this->dados_item["id"].'\'">';
      if ($this->ehEnviavelRequerimento()) $botoes[] = '<input type="button" name="enviar_requerimento" class="buttonAzulEscuroBranco11" value="Enviar Requerimento" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_requerimento&i='.$this->dados_item["id"].'\'">';
      $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      // Registra Atividade
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, 'id='.$this->dados_item["id"]);
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if ($this->dados_item["status_id"] < 3) {
        if (!$_POST) {
          $this->conteudo .= $this->carregaArquivo("cancelamentos.editar.inc.php");
          // Conteudo
          $this->conteudo = str_replace('[cliente_id]', $this->dados_item["cliente_id"], $this->conteudo);
          $this->conteudo = str_replace('[cliente]', $this->dados_item["nome"], $this->conteudo);
          $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
          $this->conteudo = str_replace('[contrato]', $this->dados_item["contrato_codigo"] ? $this->dados_item["contrato_codigo"]." (".$this->dados_item["contrato_servico"].")" : "Todos", $this->conteudo);
          $this->conteudo = str_replace('[mensagem_inadimplente]', (($cobrancas = $this->buscaCobrancasAtrasadas($this->dados_item["cliente_id"])) ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none"), $this->conteudo);
          $this->conteudo = str_replace('[cobrancas]', $cobrancas, $this->conteudo);
          $this->conteudo = str_replace('[exibir_mensagem_vigencia]', $this->dados_item["vigencia_ativa"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
          $this->conteudo = str_replace('[vigencia]', $this->dados_item["prazo_vigencia"], $this->conteudo);
          $this->conteudo = str_replace('[exibir_mensagem_comodato]', $this->dados_item["equipamentos"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
          $this->conteudo = str_replace('[equipamentos_comodato]', implode(", ", $this->dados_item["equipamentos"]), $this->conteudo);
          $this->conteudo = str_replace('[origem]', $this->dados_item["origem_nome"], $this->conteudo);
          $this->conteudo = str_replace('[motivo]', $this->dados_item["motivo_nome"], $this->conteudo);
          $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
          $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
          $this->conteudo = str_replace('[etapas]', $this->geraEtapasListagem($this->dados_item["etapas"]), $this->conteudo);
          $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
          $this->conteudo = str_replace('[etapa_tipo]', $this->geraSelect($this->filtraEtapasTiposPossiveis($this->buscaEtapaTipos()), null, true, "etapa_tipo", "100%", "mudaCancelamentoEtapaTipo(this.value)"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_nivel]', $this->geraSelect($this->buscaEtapaNiveis(), 3, false, "etapa_nivel", "100%"), $this->conteudo);
          // Etapas Tipos
          $this->conteudo = str_replace('[etapa_conteudo_1_origem]', $this->geraSelect($this->buscaOrigens(), null, true, "etapa_conteudo_1_origem", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_2_motivo]', $this->geraSelect($this->buscaMotivos(1), null, true, "etapa_conteudo_2_motivo", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_4_usuario]', $this->geraSelect($this->buscaEtapasTipos4Usuarios(), null, true, "etapa_conteudo_4_usuario", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_5_periodo]', $this->geraSelect($this->buscaEtapasTipos5Periodos(), null, true, "etapa_conteudo_5_periodo", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_5_local]', $this->geraSelect($this->buscaEtapasTipos5Locais(), null, true, "etapa_conteudo_5_local", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_6_motivo]', $this->geraSelect($this->buscaEtapasTipos6Motivos(), null, true, "etapa_conteudo_6_motivo", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapa_conteudo_7_formato]', $this->geraSelect($this->buscaEtapasTipos7Formatos(), null, true, "etapa_conteudo_7_formato", "100%"), $this->conteudo);
          $this->conteudo = str_replace('[etapas_cumpridas]', implode(", ", @array_keys($this->dados_item["etapas_cumpridas"])), $this->conteudo);
          // Botoes
          $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
          if ($this->ehCienciavel()) $botoes[] = '<input type="button" name="tomar_ciencia" class="buttonAzulEscuroBranco11" value="Tomar Ciência" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tomar_ciencia&i='.$this->dados_item["id"].'\'">';
          if ($this->ehResolicitavel()) $botoes[] = '<input type="button" name="resolicitar" class="buttonAzulEscuroBranco11" value="Re-solicitar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=resolicitar&i='.$this->dados_item["id"].'\'">';
          if ($this->ehAprovavel()) $botoes[] = '<input type="button" name="desconto_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar Desconto" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desconto_aprovar&i='.$this->dados_item["id"].'\'">';
          if ($this->ehReprovavel()) $botoes[] = '<input type="button" name="desconto_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar Desconto" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desconto_reprovar&i='.$this->dados_item["id"].'\'">';
          if ($this->ehFinalizavel()) $botoes[] = '<input type="button" name="finalizar" class="buttonAzulEscuroBranco11" value="Finalizar" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=finalizar&i='.$this->dados_item["id"].'\'">';
          if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\'">';
          if ($this->ehVisualizavelRequerimento()) $botoes[] = '<input type="button" name="visualizar_requerimento" class="buttonAzulEscuroBranco11" value="Visualizar Requerimento" onclick="visualizarRequerimento('.$this->dados_item["id"].')">';
          if ($this->ehEnviavelRequerimento()) $botoes[] = '<input type="button" name="enviar_requerimento" class="buttonAzulEscuroBranco11" value="Enviar Requerimento" onclick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_requerimento&i='.$this->dados_item["id"].'\'">';
          $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
          $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
          $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
          $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
        } else {
          $query = "UPDATE tab_cancelamentos SET cas_id = 2 WHERE can_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          $parametros = $this->extraiEtapaParametros($_POST["etapa_tipo"], $_POST);
          $this->registraEtapa($_GET["i"], $_POST["etapa_tipo"], $parametros, $_POST["etapa_nivel"]);
          // Registra Atividade
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
        }
      } else {
        $this->ver();
      }
    } else {
      $this->fechar();
    }
  }

  #####################
  ### Tomar Ciência ###
  #####################

  function tomar_ciencia() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehCienciavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.tomar_ciencia.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=tomar_ciencia&i={$_GET["i"]}", $this->conteudo);
      } else {
        $parametros = $this->extraiEtapaParametros(10, $_POST);
        $this->registraEtapa($_GET["i"], 10, $parametros);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ########################
  ### Desconto Aprovar ###
  ########################

  function desconto_aprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehAprovavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.desconto_aprovar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=desconto_aprovar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $parametros = $this->extraiEtapaParametros(12, $_POST);
        $this->registraEtapa($_GET["i"], 12, $parametros);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  #########################
  ### Desconto Reprovar ###
  #########################

  function desconto_reprovar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehAprovavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.desconto_reprovar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=desconto_reprovar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $parametros = $this->extraiEtapaParametros(13, $_POST);
        $this->registraEtapa($_GET["i"], 13, $parametros);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  ####################
  ### Re-Solicitar ###
  ####################

  function resolicitar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehResolicitavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.resolicitar.inc.php");
        $this->conteudo = str_replace('[etapa_conteudo_11_origem]', $this->geraSelect($this->buscaOrigens(), null, true, "etapa_conteudo_11_origem", "100%"), $this->conteudo);
        $this->conteudo = str_replace('[etapa_nivel]', $this->geraSelect($this->buscaEtapaNiveis(), 3, false, "etapa_nivel", "100%"), $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=resolicitar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $parametros = $this->extraiEtapaParametros (11, $_POST);
        $this->registraEtapa($_GET["i"], 11, $parametros, $_POST["etapa_nivel"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  #################
  ### Finalizar ###
  #################

  function finalizar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehFinalizavel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.finalizar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[data_cancelamento]", $this->geraInput("text", "data_cancelamento", "150px", $this->dados_item["motivo_id"] == 28 ? $this->dados_item["contrato_ativacao"] : date("d/m/Y"), $this->dados_item["motivo_id"] != 28, "10", "return formataData(this,event)"), $this->conteudo);
        $this->conteudo = str_replace("[perdido]", $this->geraInput("radio", array("name" => "status", "id" => "status_4"), null, "4", ($this->dados_item["origem_id"] == 6 || ($this->dados_item["etapas_cumpridas"][7] && !$this->dados_item["etapas_cumpridas"][8] && !$this->possuiAditivoPendente($this->dados_item["contrato_id"]))), null, null, "mudaStatus()"), $this->conteudo);
        $this->conteudo = str_replace("[exibir_mensagem_vigencia]", $this->dados_item["vigencia_ativa"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
        $this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
        $this->conteudo = str_replace('[exibir_mensagem_comodato]', $this->dados_item["equipamentos"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
        $this->conteudo = str_replace('[equipamentos_comodato]', implode(", ", $this->dados_item["equipamentos"]), $this->conteudo);
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$_GET["i"]}", $this->conteudo);
      } elseif ($_POST["status"] == 4 && !$_POST["confirmacao"] && $this->vigenciaAtiva($_POST["data_cancelamento"], $this->dados_item["vigencia_expiracao"])) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.finalizar.confirmacao.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
        $this->conteudo = str_replace('[exibir_mensagem_comodato]', $this->dados_item["equipamentos"] ? ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
        $this->conteudo = str_replace('[equipamentos_comodato]', implode(", ", $this->dados_item["equipamentos"]), $this->conteudo);
        $this->conteudo = str_replace("[status]", $_POST["status"], $this->conteudo);
        $this->conteudo = str_replace("[data_cancelamento]", $_POST["data_cancelamento"], $this->conteudo);
        $this->conteudo = str_replace("[comentario]", $_POST["comentario"], $this->conteudo);
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=finalizar&i={$_GET["i"]}", $this->conteudo);
      } else {
        if ($this->dados_item["motivo_id"] == 28) {
          $_POST["data_cancelamento"] = $this->dados_item["contrato_ativacao"];
        }
        // Registra Desconto Retenção
        if ($_POST["status"] == 3) {
          for ($i = 0; $i < sizeof($this->dados_item["etapas"]); $i++) {
            if ($this->dados_item["etapas"][$i]["tipo_id"] == 6) {
              $etapa_desconto = $this->dados_item["etapas"][$i];
            }
          }
          if ($etapa_desconto) {
            $this->registraDescontoRetencao($this->dados_item["cliente_id"], $etapa_desconto);
          }
        } elseif ($_POST["status"] == 4) {
          // Efetua Cancelamento do Contrato
          $this->cancelarContrato($this->dados_item["contrato_id"], $_POST["data_cancelamento"], $_POST["comentario"], $this->dados_item["motivo_id"] == 28);
        }
        // Atualiza Solicitação de Cancelamento
        $query = "UPDATE tab_cancelamentos SET cas_id = {$_POST["status"]}, can_termino = NOW() WHERE can_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }
  
  function vigenciaAtiva($data_cancelamento, $vigencia_expiracao) {
    // dd/mm/yyyy
    $data_cancelamento  = explode("/", $data_cancelamento);
    $vigencia_expiracao = explode("/", $vigencia_expiracao);
    return $vigencia_expiracao[2].$vigencia_expiracao[1].$vigencia_expiracao[0] > $data_cancelamento[2].$data_cancelamento[1].$data_cancelamento[0];
  }

  function cancelarContrato($contrato_id, $data_cancelamento, $comentario, $remover_venda = false) {
    $contratos = controle::instanciaObjeto("contratos", $this->bancodedados, $this->permissoes, false);
    $dados_contrato = $contratos->buscaDadosContrato($contrato_id);
    $contratos->acaoCancelarContrato($dados_contrato, $data_cancelamento, $comentario, $_SESSION["usuario"], $remover_venda);
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && $this->ehRemovivel()) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("cancelamentos.remover.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_cancelamentos SET cae_id = NULL WHERE can_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_cancelamentos_etapas WHERE can_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $query = "DELETE FROM tab_cancelamentos WHERE can_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        $this->fechar();
      }
    } else {
      $this->fechar();
    }
  }

  ##############
  ### Listar ###
  ##############

  function listar() {
    $filtro = $this->inicializaFiltro($_GET["f"]);
    $pagina = $_GET["p"] ? $_GET["p"] : 1;
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
    $this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
    $this->conteudo .= $filtro->formulario;
    if ($_GET["f"] || $_GET["p"]) {
      $itens = $this->buscaItens($filtro, $pagina);
      $paginacao = $this->geraPaginacao($filtro, $itens["linhas"], $pagina);
      $listagem = $this->geraListagem($itens["itens"]);
      $this->conteudo .= '</td></tr></table>';
      $this->conteudo .= $paginacao->conteudo;
      $this->conteudo .= $listagem->conteudo;
      $this->conteudo .= $paginacao->conteudo;
    } else {
      $this->conteudo .= '</td></tr></table>';
    }
  }

  function inicializaFiltro($valores = "") {
    // Cliente
    $itens[] = array ("tipo" => "input", "nome" => "cliente", "descricao" => "Cliente", "valor" => "", "where" => "(tab_clientes.cli_nome LIKE '%[valor]%')");
    // Origem
    $origens = $this->buscaOrigens();
    foreach ($origens as $id => $nome) {
      $select_origens[] = array ("id" => $id, "nome" => $nome);
    }
    if (!$valores["origem"]) $origem_nula = " OR tab_cancelamentos.cao_id IS NULL";
    $itens[] = array ("tipo" => "select", "nome" => "origem", "descricao" => "Origem", "valor" => $select_origens, "where" => "(tab_cancelamentos.cao_id = '[valor]'$origem_nula)");
    // Motivo
    $motivos = $this->buscaMotivos();
    foreach ($motivos as $id => $nome) {
      if ($id == 1) {
        $select_motivos[] = array ("id" => "1", "nome" => "Mudança de Endereço", "where" => "(tab_cancelamentos.cam_id IN (1, 10, 11, 12))");
      } else {
        $select_motivos[] = array ("id" => $id, "nome" => $nome);
      }
    }
    if (!$valores["motivo"]) $motivo_nulo = " OR tab_cancelamentos.cam_id IS NULL";
    $itens[] = array ("tipo" => "select", "nome" => "motivo", "descricao" => "Motivo", "valor" => $select_motivos, "where" => "(tab_cancelamentos.cam_id = '[valor]'$motivo_nulo)");
    // Data Abertura -> Início
    $itens[] = array ("tipo" => "input", "nome" => "data_abertura_inicio", "descricao" => "Data Abertura &raquo Início", "valor" => "", "where" => "(tab_cancelamentos.can_inicio >= '".substr($valores["data_abertura_inicio"], 6, 4)."-".substr($valores["data_abertura_inicio"], 3, 2)."-".substr($valores["data_abertura_inicio"], 0, 2)." 00:00:00')");
    // Data Abertura -> Término
    $itens[] = array ("tipo" => "input", "nome" => "data_abertura_termino", "descricao" => "Data Abertura &raquo Término", "valor" => "", "where" => "(tab_cancelamentos.can_inicio <= '".substr($valores["data_abertura_termino"], 6, 4)."-".substr($valores["data_abertura_termino"], 3, 2)."-".substr($valores["data_abertura_termino"], 0, 2)." 23:59:59')");
    // Condominio
    $condominios = $this->buscaCondominios();
    foreach ($condominios as $id => $nome) {
      $select_condominios[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "condominio", "descricao" => "Condomínio", "valor" => $select_condominios, "where" => "(tab_condominios.con_id = [valor])");
    // Data fechamento -> Início
    $itens[] = array ("tipo" => "input", "nome" => "data_fechamento_inicio", "descricao" => "Data Fechamento &raquo Início", "valor" => "", "where" => "(tab_cancelamentos.can_termino >= '".substr($valores["data_fechamento_inicio"], 6, 4)."-".substr($valores["data_fechamento_inicio"], 3, 2)."-".substr($valores["data_fechamento_inicio"], 0, 2)." 00:00:00')");
    // Data fechamento -> Término
    $itens[] = array ("tipo" => "input", "nome" => "data_fechamento_termino", "descricao" => "Data Fechamento &raquo Término", "valor" => "", "where" => "(tab_cancelamentos.can_termino <= '".substr($valores["data_fechamento_termino"], 6, 4)."-".substr($valores["data_fechamento_termino"], 3, 2)."-".substr($valores["data_fechamento_termino"], 0, 2)." 23:59:59')");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
    }
    $select_status[] = array ("id" => "-1", "nome" => "Finalizado", "where" => "(tab_cancelamentos.cas_id = 3 OR tab_cancelamentos.cas_id = 4)");
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_cancelamentos.cas_id = [valor])");
    // Data Cancelamento -> Início
    $itens[] = array ("tipo" => "input", "nome" => "data_cancelamento_inicio", "descricao" => "Data Cancelamento &raquo Início", "valor" => "", "where" => "(tab_cancelamentos.cas_id = 4 AND tab_contratos.cor_data_cancelamento >= '".substr($valores["data_cancelamento_inicio"], 6, 4)."-".substr($valores["data_cancelamento_inicio"], 3, 2)."-".substr($valores["data_cancelamento_inicio"], 0, 2)."')");
    // Data Cancelamento -> Término
    $itens[] = array ("tipo" => "input", "nome" => "data_cancelamento_termino", "descricao" => "Data Cancelamento &raquo Término", "valor" => "", "where" => "(tab_cancelamentos.cas_id = 4 AND tab_contratos.cor_data_cancelamento <= '".substr($valores["data_cancelamento_termino"], 6, 4)."-".substr($valores["data_cancelamento_termino"], 3, 2)."-".substr($valores["data_cancelamento_termino"], 0, 2)."')");
    // Serviço
    $itens[] = array ("tipo" => "select", "nome" => "servico", "descricao" => "Serviço", "valor" => $this->buscaServicos(), "where" => "(tab_contratos.pls_id = [valor])");
    // Gera Filtro
    $filtro = controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
    return $filtro;
  }

  function geraPaginacao($filtro, $total, $pagina) {
    $paginacao = controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
    return $paginacao;
  }

  function buscaItens($filtro, $pagina) {
    $limit_inicio = ($pagina - 1) * PAG_ITENS;
    $limit_fim = PAG_ITENS;
    $query = "
      SELECT SQL_CALC_FOUND_ROWS
        tab_cancelamentos.can_id AS id,
        DATE_FORMAT(tab_cancelamentos.can_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_cancelamentos.can_termino, '%d/%m/%Y (%H:%i)') AS termino,
        tab_cancelamentos_origens.cao_nome AS origem,
        tab_cancelamentos_motivos.cam_nome AS motivo,
        tab_cancelamentos_status.cas_nome AS status,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome,
        tab_clientes.cls_id AS cliente_status,
        tab_condominios.con_nome AS condominio_nome
      FROM
        tab_cancelamentos
        LEFT OUTER JOIN tab_cancelamentos_origens ON (tab_cancelamentos.cao_id = tab_cancelamentos_origens.cao_id)
        LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id)
        LEFT OUTER JOIN tab_cancelamentos_status ON (tab_cancelamentos.cas_id = tab_cancelamentos_status.cas_id)
        INNER JOIN tab_clientes ON (tab_cancelamentos.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_contratos ON (tab_cancelamentos.cor_id = tab_contratos.cor_id)
      WHERE
    		{$filtro->parametros_sql}".($_SESSION["origem"] ? " AND
			tab_clientes.clo_id = {$_SESSION["origem"]}" : "")."
      ORDER BY
        tab_cancelamentos.cas_id ASC,
        tab_cancelamentos.can_inicio ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["contador"] = $limit_inicio + count($itens) + 1;
      if ($this->permissoes["clientes"]["ver_da"]) $dados["link"] = "?m=clientes&a=ver_da&i={$dados["cliente_id"]}";
      $dados["cliente_nome"] = strlen($dados["cliente_nome"]) > 50 ? substr($dados["cliente_nome"], 0, 47)."..." : $dados["cliente_nome"];
      if ($dados["condominio_nome"]) $dados["cliente_nome"] .= "<br>({$dados["condominio_nome"]})";
      if (!$dados["origem"]) $dados["origem"] = "Indefinida";
      $dados["origem"] = str_replace(array(" por ", " na "), array("<br>por ", "<br>na "), $dados["origem"]);
      if (!$dados["motivo"]) $dados["motivo"] = "Indefinido";
      $dados["motivo"] = str_replace(" (", "<br>(", $dados["motivo"]);
      $dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
      $dados["data_hora"] = "A: {$dados["inicio"]}";
      if ($dados["termino"]) $dados["data_hora"] .= "<br>F: {$dados["termino"]}";
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "cliente_nome" => "Cliente (Condomínio)", "origem" => "Origem", "motivo" => "Motivo", "data_hora" => "Data / Hora", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 0, 70, 0, 110, 70), null, array("right"));
    return $listagem;
  }

  #########################
  ### Desconto Retenção ###
  #########################

  function registraDescontoRetencao($cliente, $etapa_desconto) {
    if ($cliente && $etapa_desconto) {
      for ($i = 0; $i < ceil($etapa_desconto["parametros"]["duracao"] / 30); $i++) {
        $ajuste_inicio = $ajuste_termino = date('Y-m-01', strtotime("+ $i months"));
        $ajuste_valor = ($i == ceil($etapa_desconto["parametros"]["duracao"] / 30) - 1) ? ($etapa_desconto["parametros"]["duracao"] < 30 ? $etapa_desconto["parametros"]["duracao"] : $etapa_desconto["parametros"]["duracao"] % 30) : 30;
        // Insere Ajuste Financeiro
        $query = "INSERT INTO tab_ajustes_financeiros (ajf_datahora, cli_id, afc_id, aft_id, ajf_valor, ajf_valor_tipo, ajf_competencia_inicio, ajf_competencia_termino, afs_id) VALUES (NOW(), $cliente, 4, 2, '$ajuste_valor', 3, '$ajuste_inicio', '$ajuste_termino', 5)";
        $this->bancodedados->query($query);
        $id = $this->bancodedados->ultimoIdInserido();
        // Regisro Comentário
        $query = "INSERT INTO tab_ajustes_financeiros_comentarios (afm_datahora, ajf_id, afm_conteudo, usu_id) VALUES (NOW(), '$id', 'Retenção de Cancelamento', '{$_SESSION["usuario"]}')";
        $this->bancodedados->query($query);
      }
    }
  }

  ###############################
  ### Visualizar Requerimento ###
  ###############################

  function visualizar_requerimento() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $conteudo = $this->geraConteudoRequerimento($this->dados_item);
      print $conteudo;
      die();
    } else {
      $this->fechar();
    }
  }

  function geraConteudoRequerimento($dados) {
    $meses = array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
    $dados["data"] = date("d")." de ".$meses[date("m") - 1]." de ".date("Y");
    $conteudo = $this->carregaArquivo(DIR_RAIZ."modelos/requerimento_rescisao_{$dados["contrato_servico_id"]}_{$dados["cliente_tipo_id"]}.html");
    foreach ($dados as $campo_nome => $campo_valor) {
      $conteudo = str_replace("[$campo_nome]", $campo_valor, $conteudo);
    }
    return $conteudo;
  }

  ###########################
  ### Enviar Requerimento ###
  ###########################

  function enviar_requerimento() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        // Busca Dados
        $lista_emails = $this->buscaContatosEmails($this->dados_item["cliente_id"]);
        $lista_emails = @implode("; ", $lista_emails);
        // Conteúdo
        $this->conteudo .= $this->carregaArquivo("cancelamentos.enviar.inc.php");
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=enviar_requerimento&i={$_GET["i"]}", $this->conteudo);
        $this->conteudo = str_replace("[emails]", $lista_emails, $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onclick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      } else {
        sort($emails = array_unique(preg_split("/ *[ ,;] */", strtolower($_POST["emails"]))));
        $this->enviaMensagemRequerimento($this->dados_item, $emails, $_POST["data_cancelamento"]);
        // Registra Etapa
        $parametros = $this->extraiEtapaParametros(14, $_POST);
        $this->registraEtapa($_GET["i"], 14, $parametros);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      $this->fechar();
    }
  }

  function enviaMensagemRequerimento($dados_cancelamento, $emails, $data_cancelamento) {
    $dados_lote = $this->buscaDadosMensagensLotes(18);
    $parametros = $dados_lote["parametros"]["envio"];
    $parametros["dados_conteudo"]["id"] = $dados_cancelamento["cliente_id"];
    $parametros["dados_conteudo"]["cliente_nome"] = $dados_cancelamento["nome"];
    $parametros["destinatarios"] = $emails;
    $cobrancas = $this->buscaCobrancasEmAberto($dados_cancelamento, $data_cancelamento);
    if ($cobrancas) {
      $parametros["dados_conteudo"]["cobrancas_em_aberto_texto"] = "\nCobranças em aberto até esta data:\n\n{$cobrancas["texto"]}\n";
      $parametros["dados_conteudo"]["cobrancas_em_aberto_html"] = "<br>{$cobrancas["html"]}";
    }
    $link_0 = md5("contratos"."ver".$dados_cancelamento["contrato_id"]);
    $parametros["dados_conteudo"]["url"] = CONTRATO_ONLINE_URL_EXTERNA;
    $parametros["dados_conteudo"]["link_0"] = $link_0;
    $parametros["dados_conteudo"]["cpf_cnpj"] = $dados_cancelamento["cpf_cnpj"];
    $parametros["dados_conteudo"]["contrato"] = $dados_cancelamento["contrato_codigo"];
    // Requerimento em Anexo
    $parametros["anexos"][0]["conteudo"] = str_replace("imagens/logo.jpg", "http://www.wavesystem.com.br/integracao/imagens/logo.jpg", $this->geraConteudoRequerimento($dados_cancelamento));
    $parametros["anexos"][0]["nome"] = "Requerimento.html";
    // Serializa Prametros
    $parametros = @serialize($parametros);
    // Insere mensagem no banco
    $query = "
      INSERT INTO
        tab_mensagens (men_parametros, men_data_hora_geracao, cli_id, mes_id, mlm_id, mel_id)
      VALUES ('$parametros', NOW(), {$dados_cancelamento["cliente_id"]}, 1, {$dados_lote["modelo_id"]}, {$dados_lote["id"]})";
    $this->bancodedados->query($query);
    // id da mensagem
    $id = $this->bancodedados->ultimoIdInserido();
    // Insere link no banco
    $query = "
      INSERT INTO
        tab_mensagens_links (mei_md5, mei_url_modulo, mei_url_acao, mei_url_id, men_id)
      VALUES ('$link_0', 'contratos', 'ver', '{$dados_cancelamento["contrato_id"]}', $id)";
    $this->bancodedados->query($query);
  }

  function buscaDadosMensagensLotes($id) {
    $query = "
      SELECT
        tab_mensagens_lotes.mel_id AS id,
        tab_mensagens_lotes.mlm_id AS modelo_id,
        tab_mensagens_lotes.mel_parametros AS parametros
      FROM
        tab_mensagens_lotes
      WHERE
        tab_mensagens_lotes.mel_id = $id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      return $dados;
    }
  }

  function buscaContratosAtivos($cliente_id) {
    $query = "
      SELECT DISTINCT
        tab_contratos.cor_id AS id,
        tab_contratos.cor_codigo AS codigo,
        tab_planos_servicos.pls_nome AS servico,
        DATE_FORMAT(cor_data_geracao, '%d/%m/%Y') AS data_geracao,
        DATE_FORMAT(cor_data_ativacao, '%d/%m/%Y') AS data_ativacao,
        DATE_FORMAT(cor_data_cancelamento, '%d/%m/%Y') AS data_cancelamento,
        tab_contratos_tipos.crt_nome AS tipo,
        tab_contratos_status.crs_nome AS status,
        tab_planos_servicos.pls_id AS servico_id
      FROM
        tab_contratos
        INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
        INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
        INNER JOIN tab_contratos_tipos ON (tab_contratos.crt_id = tab_contratos_tipos.crt_id)
        INNER JOIN tab_contratos_status ON (tab_contratos.crs_id = tab_contratos_status.crs_id)
        LEFT OUTER JOIN tab_cancelamentos ON (tab_cancelamentos.cor_id = tab_contratos.cor_id AND tab_cancelamentos.cas_id <= 2)
        LEFT OUTER JOIN tab_contratos_vinculos ON (tab_contratos_vinculos.cor_filho = tab_contratos.cor_id AND tab_contratos_vinculos.pvt_id = 3)
      WHERE
        tab_contratos_vinculos.cov_id IS NULL AND
        tab_contratos.cli_id = $cliente_id AND
        tab_contratos.cor_pai IS NULL AND
        tab_contratos.crs_id IN (19, 20, 21)
      GROUP BY
        id
      HAVING
        COUNT(tab_cancelamentos.can_id) = 0
      ORDER BY
        codigo";
    $query = $this->bancodedados->query($query);
    $contratos = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["contratos"]["ver"]) $dados["link"] = "javascript:verContrato({$dados["id"]});";
      $dados["radio"] = "<div align=\"center\"><input type=\"radio\" name=\"contrato\" value=\"{$dados["id"]}\" id=\"contrato_{$dados["id"]}\"".(!count($contratos) ? " checked" : "")."></div>";
      $dados["codigo"] = "<div nowrap>{$dados["codigo"]}</div>";
      $dados["datas"][] = "G: {$dados["data_geracao"]}";
      if ($dados["data_ativacao"]) $dados["datas"][] = "A: {$dados["data_ativacao"]}";
      if ($dados["data_cancelamento"]) $dados["datas"][] = "C: {$dados["data_cancelamento"]}";
      $dados["datas"] = "<div nowrap>".implode("<br>", $dados["datas"])."</div>";
      $contratos[] = $dados;
    }
    return $contratos;
  }

  function geraListagemContratos($dados) {
    $cabecalho = array ("radio" => "", "codigo" => "Código", "servico" => "Serviço", "datas" => "Datas", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(30));
    return $listagem->conteudo;
  }

  function buscaCobrancasEmAberto($dados_cancelamento, $data_cancelamento) {
  	$cobrancas = $this->calculaCobrancas($dados_cancelamento, $data_cancelamento);
    if ($cobrancas) {
      // Cabecalho - HTML
      $tabela["html"] = '<table width="100%" cellpadding="2" cellspacing="1" border="1" align="center">';
      $tabela["html"] .= '<tr><td style="text-align:left;padding:5px;text-align:center;font-weight:bold" colspan="4">Cobranças em aberto*</td></tr>';
      $tabela["html"] .= '<tr><td style="text-align:center"><b>Referência</b></td><td style="text-align:center"><b>Vencimento</b></td><td style="text-align:center"><b>Valor Original</b></td><td style="text-align:center"><b>Valor Corrigido**</b></td></tr>';
      // Cabecalho -TEXTO
      $tabela["texto"] = "Cobranças em aberto*\n\n";
      $tabela["texto"] .= $this->cortaTexto("Referência", 20, "left")."  ";
      $tabela["texto"] .= $this->cortaTexto("Vencimento", 10, "center")."  ";
      $tabela["texto"] .= $this->cortaTexto("Valor Original", 20, "right")."  ";
      $tabela["texto"] .= $this->cortaTexto("Valor Corrigido**", 20, "right")."\n";
      foreach ($cobrancas as $cobranca) {
        if ($cobranca["referencia"] == "<b>Total</b>") {
          // Linha - HTML
          $tabela["html"] .= "<tr><td colspan=\"3\" style=\"text-align:left;width:25%\">{$cobranca["referencia"]}</td>";
          $tabela["html"] .= "<td style=\"text-align:right;width:25%\">{$cobranca["valor_reajustado"]}</td></tr>";
        } else {
          // Linha - HTML
          $tabela["html"] .= "<tr><td style=\"text-align:left;width:25%\">{$cobranca["referencia"]}</td>";
          $tabela["html"] .= "<td style=\"text-align:center;width:25%\">{$cobranca["vencimento"]}</td>";
          $tabela["html"] .= "<td style=\"text-align:right;width:25%\">{$cobranca["valor"]}</td>";
          $tabela["html"] .= "<td style=\"text-align:right;width:25%\">{$cobranca["valor_reajustado"]}</td></tr>";
        }
        // Linha - TEXTO
        $tabela["texto"] .= $this->cortaTexto($cobranca["referencia"], 20, "left")."  ";
        $tabela["texto"] .= $this->cortaTexto($cobranca["vencimento"], 10, "right")."  ";
        $tabela["texto"] .= $this->cortaTexto($cobranca["valor"], 20, "right")."  ";
        $tabela["texto"] .= $this->cortaTexto($cobranca["valor_reajustado"], 20, "right")."\n";
      }
      // Rodapé - HTML
      $tabela["html"] .= '<tr><td style="padding:3px" colspan="4"><table width="100%" cellpadding="0" cellpasing="0" border="0">';
      $tabela["html"] .= '<tr><td style="text-align:left;font-size:10px;width:20px" valign="top">*</td><td style="text-align:left;font-size:10px">Valores calculados para rescisão efetuada em <b>'.$data_cancelamento.'</b>, conforme data de recebimento do documento em nossa empresa.</td></tr>';
      $tabela["html"] .= '<tr><td style="text-align:left;font-size:10px" valign="top">**</td><td style="text-align:left;font-size:10px">Correção calculada com base em <b>'.date('d/m/Y').'</b>.</td></tr>';
      $tabela["html"] .= '</table></td></tr>';
      $tabela["html"] .= '</table><br>';
      // Rodapé - TEXTO
      $tabela["texto"] .= "\n";
      $tabela["texto"] .= "*  Valores calculados para rescisão efetuada em $data_cancelamento, conforme data de\n";
      $tabela["texto"] .= "   recebimento do documento em nossa empresa.\n";
      $tabela["texto"] .= "** Correção calculada com base em ".date('d/m/Y').".\n";
      return $tabela;
    }
  }

  function cortaTexto($texto, $tamanho, $alinhamento = "left") {
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip ($trans_tbl);
    $texto = strip_tags(strtr($texto, $trans_tbl));
    if (strlen($texto) >= $tamanho) {
      return ($alinhamento == "left") ? substr($texto, 0, $tamanho) : substr($texto, -$tamanho);
    } elseif ($alinhamento == "left") {
      return $texto.str_repeat(" ", $tamanho - strlen($texto));
    } elseif ($alinhamento == "right") {
      return str_repeat(" ", $tamanho - strlen($texto)).$texto;
    } elseif ($alinhamento == "center") {
      return str_repeat(" ", floor($tamanho - strlen($texto)) / 2).$texto.str_repeat(" ", (floor($tamanho - strlen($texto)) / 2) + (($tamanho - strlen($texto)) % 2));
    }
  }

  function calculaCobrancas($dados_cancelamento, $data_cancelamento) {
    $query = "
      SELECT
        DATE_FORMAT(coa_data_vencimento, '%d/%m/%Y') AS vencimento,
        coa_valor_original AS valor,
        IF(coa_data_vencimento < CURDATE(), coa_valor_original + FLOOR(coa_valor_original * 0.02 + (coa_valor_original * 0.01 * (TO_DAYS(CURDATE()) - TO_DAYS(coa_data_vencimento)) / 30)), coa_valor_original) AS valor_reajustado
      FROM
        tab_cobrancas
      WHERE
        cli_id = {$dados_cancelamento["cliente_id"]} AND
        coa_pai IS NULL AND
        cot_id = 1
      ORDER BY
        coa_data_vencimento";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $total += $dados["valor_reajustado"];
      $dados["referencia"] = "Mensalidade";
      $dados["valor"] = $this->formataDinheiro($dados["valor"]);
      $dados["valor_reajustado"] = $this->formataDinheiro($dados["valor_reajustado"]);
      $ultimo_vencimento = substr($dados["vencimento"], 6, 4)."-".substr($dados["vencimento"], 3, 2);
      $cobrancas[] = $dados;
    }
    $vencimento_inicial = date("Y-m");
    $cobrancas_futuras = $this->buscaCobrancasFuturas($dados_cancelamento, $vencimento_inicial, $data_cancelamento);
    if ($cobrancas_futuras) {
      $cobrancas = @funcoes::mescla_arrays($cobrancas, $cobrancas_futuras["cobrancas"]);
      $total += $cobrancas_futuras["total"];
    }
    // Multa Rescisória
    if ($dados_cancelamento["vigencia_ativa"]) {
      $total += $dados_cancelamento["multa"];
      $cobrancas[] = array("referencia" => "Multa Rescisória", "vencimento" => "---", "valor" => $this->formataDinheiro($dados_cancelamento["multa"]), "valor_reajustado" => $this->formataDinheiro($dados_cancelamento["multa"]));
    }
    if ($cobrancas) {
      $cobrancas[] = array("referencia" => "<b>Total</b>", "vencimento" => "&nbsp;", "valor" => "&nbsp;", "valor_reajustado" => "<b>".$this->formataDinheiro($total)."</b>");
      return $cobrancas;
    }
  }

  function buscaCobrancasFuturas($dados_cancelamento, $vencimento_inicial, $data_cancelamento) {
    $data_cancelamento = substr($data_cancelamento, 6, 4)."-".substr($data_cancelamento, 3, 2)."-".substr($data_cancelamento, 0, 2);
    $cobrancas_geracao = controle::instanciaObjeto("cobrancas_geracao", $this->bancodedados);
    return $cobrancas_geracao->buscaCobrancasCancelamento($dados_cancelamento["cliente_id"], $vencimento_inicial, @implode(", ", $dados_cancelamento["assinaturas"]), $data_cancelamento);
  }

  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }

  ######################
  ### Busca Listagem ###
  ######################

  function buscaBotaoAdicionar($cliente_id) {
    if (!$this->permissoes["cancelamentos"]["adicionar"]) return "";
    if (!$this->possuiContratosDisponiveis($cliente_id)) return "";
    return '<input type="button" class="buttonAzulEscuroBranco11" name="assinaturas_adicionar" value="Adicionar" onClick="adicionarCancelamento('.$cliente_id.')">';
  }

  function possuiContratosDisponiveis($cliente_id) {
    $query = "
      SELECT
        tab_contratos.cor_id AS id
      FROM
        tab_contratos
        LEFT OUTER JOIN tab_cancelamentos ON (tab_cancelamentos.cli_id = $cliente_id AND (tab_cancelamentos.cor_id = tab_contratos.cor_id OR tab_cancelamentos.cor_id IS NULL) AND tab_cancelamentos.cas_id <= 2)
      WHERE
        tab_contratos.cli_id = $cliente_id AND
        tab_contratos.cor_pai IS NULL AND
        tab_contratos.crs_id IN (19, 20, 21)
      GROUP BY
        id
      HAVING
        COUNT(tab_cancelamentos.can_id) = 0";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return true;
    }
  }

  function buscaClientesCancelamentos($cliente_id) {
    $query = "
      SELECT
        tab_cancelamentos.can_id AS id,
        DATE_FORMAT(tab_cancelamentos.can_inicio,'%d/%m/%Y (%H:%i)') AS inicio,
        DATE_FORMAT(tab_cancelamentos.can_termino,'%d/%m/%Y (%H:%i)') AS termino,
        tab_cancelamentos_origens.cao_nome AS origem,
        tab_cancelamentos_motivos.cam_nome AS motivo,
        tab_cancelamentos_status.cas_nome AS status,
        IF(tab_contratos.cor_id IS NOT NULL, tab_contratos.cor_codigo, 'Todos') AS contrato,
        IF(tab_contratos.cor_id IS NOT NULL, tab_planos_servicos.pls_nome, 'Todos') AS servico
      FROM
        tab_cancelamentos
        INNER JOIN tab_cancelamentos_origens ON (tab_cancelamentos.cao_id = tab_cancelamentos_origens.cao_id)
        INNER JOIN tab_cancelamentos_status ON (tab_cancelamentos.cas_id = tab_cancelamentos_status.cas_id)
        LEFT OUTER JOIN tab_cancelamentos_motivos ON (tab_cancelamentos.cam_id = tab_cancelamentos_motivos.cam_id)
        LEFT OUTER JOIN tab_contratos ON (tab_contratos.cor_id = tab_cancelamentos.cor_id)
        LEFT OUTER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
      WHERE
        tab_cancelamentos.cli_id = $cliente_id
      ORDER BY
        tab_planos_servicos.pls_nome ASC,
        tab_cancelamentos.cas_id ASC,
        tab_cancelamentos.can_inicio ASC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes["cancelamentos"]["ver"]) $dados["link"] = "javascript:verCancelamento({$dados["id"]});";
      if (!$dados["origem"]) $dados["origem"] = "Indefinida";
      if (!$dados["motivo"]) $dados["motivo"] = "Indefinido";
      $dados["motivo"] = str_replace("(", "<br>(", $dados["motivo"]);
      $dados["contrato"] = "<div nowrap>".$dados["contrato"]."</div>";
      $dados["data_hora"] = "<div nowrap>A: ".$dados["inicio"];
      if ($dados["termino"]) $dados["data_hora"] .= "<br>F: ".$dados["termino"];
      $dados["data_hora"] .= "</div>";
      $dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
      $cancelamentos[] = $dados;
    }
    return $cancelamentos;
  }

  function geraListagemCancelamentos($dados) {
    $cabecalho = array ("contrato" => "Contrato", "servico" => "Serviço", "motivo" => "Motivo", "data_hora" => "Data / Hora", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

}

?>
