<?

class cron_responsabilidades_clientes {

  var $bancodedados;
  var $clientes;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  ##################################
  ### Atualiza Responsabilidades ###
  ##################################

  function atualizaRespClientes() {
    // Ativa responsabilidades que estão aprovadas e no periodo atual
    $this->ativaResponsabilidadesAprovadas();
    // Busca responsabilidades ativas que estão expiradas
    $resp_expiradas = $this->buscaResponsabilidadesAtivasExpiradas();
    for ($i = 0; $i < sizeof($resp_expiradas); $i++) {
      $id = $resp_expiradas[$i]["id"];
      $tipo = $resp_expiradas[$i]["tipo"];
      $inicio = $resp_expiradas[$i]["inicio"];
      $termino = $resp_expiradas[$i]["termino"];
      $cliente = $resp_expiradas[$i]["cliente"];
      $parametros = unserialize($resp_expiradas[$i]["parametros"]);
      if ($parametros["fase_atual"] == 0) {
        // Fase Inicial
        if ($parametros["prazo1"]) {
          $this->extenderResponsabilidade($id, $tipo, $inicio, $termino, $cliente, $parametros);
        } else {
          $this->expiraResponsabilidade($id, $tipo, $cliente, $termino);
        }
      } elseif ($parametros["fase_atual"] == 1) {
        // Fase 1
        if ($parametros["prazo2"]) {
          $this->extenderResponsabilidade($id, $tipo, $inicio, $termino, $cliente, $parametros);
        } else {
          $this->expiraResponsabilidade($id, $tipo, $cliente, $termino);
        }
      } elseif ($parametros["fase_atual"] == 2) {
        // Fase 2
        if ($parametros["prazo3"]) {
          $this->extenderResponsabilidade($id, $tipo, $inicio, $termino, $cliente, $parametros);
        } else {
          $this->expiraResponsabilidade($id, $tipo, $cliente, $termino);
        }
      }
    }
    $this->atualizaResponsabilidadesNulasDiferentes();
    $this->adicionaLembreteDePossivelTermino();
    $this->expiraResponsabilidadesAguardando();
  }

  #######################
  ### Ativa Aprovadas ###
  #######################

  function ativaResponsabilidadesAprovadas() {
    $query = "
      SELECT
        cln_id AS id,
        cli_id AS cliente,
        cri_id AS tipo,
        usu_id AS usuario
      FROM
        tab_clientes_responsabilidades
      WHERE
        cra_id = 2 AND
        cod_id IS NULL AND
        NOW() BETWEEN cln_inicio AND cln_termino";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $update = "UPDATE tab_clientes_responsabilidades SET cra_id = 4 WHERE cln_id = {$dados["id"]}";
      $this->bancodedados->query($update);
      if ($dados["tipo"] == 2) {
        $update = "UPDATE tab_clientes SET usu_id = {$dados["usuario"]} WHERE cli_id = '{$dados["cliente"]}'";
        $this->bancodedados->query($update);
      }
      $this->registraComentario($dados["id"], "Ativado");
    }
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaResponsabilidadesAtivasExpiradas() {
    $query = "
			SELECT
			  tab_clientes_responsabilidades.cln_id AS id,
			  tab_clientes_responsabilidades.cri_id AS tipo,
			  tab_clientes_responsabilidades.cli_id AS cliente,
			  tab_clientes_responsabilidades.cln_inicio AS inicio,
			  tab_clientes_responsabilidades.cln_termino AS termino,
			  tab_clientes_responsabilidades.cln_parametros AS parametros
			FROM
			  tab_clientes_responsabilidades
			WHERE
			 	tab_clientes_responsabilidades.cra_id = 4 AND
			 	tab_clientes_responsabilidades.cod_id IS NULL AND
				tab_clientes_responsabilidades.cln_termino < NOW()
			ORDER BY
        termino";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaResponsabilidadesNulasDiferentes() {
    $query = "
      SELECT
        tab_clientes_responsabilidades.cli_id AS cliente,
        tab_clientes_responsabilidades.usu_id AS usuario
      FROM
        tab_clientes_responsabilidades
        INNER JOIN tab_clientes ON (tab_clientes_responsabilidades.cli_id = tab_clientes.cli_id)
      WHERE
        (tab_clientes.usu_id IS NULL OR tab_clientes.usu_id != tab_clientes_responsabilidades.usu_id) AND
        (tab_clientes_responsabilidades.cri_id = 2) AND
        (tab_clientes_responsabilidades.cra_id = 4) AND
        (NOW() BETWEEN tab_clientes_responsabilidades.cln_inicio AND tab_clientes_responsabilidades.cln_termino)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaResponsabilidadesDoCondominio($tipo, $cliente, $termino) {
    $query = "
      SELECT
        cod_id AS id,
        cod_termino AS termino,
        crp_id AS tipo,
        cru_id AS status,
        tab_condominios_responsabilidades.usu_id AS usuario
      FROM
        tab_clientes
        INNER JOIN tab_condominios_responsabilidades ON (tab_clientes.con_id = tab_condominios_responsabilidades.con_id)
      WHERE
        '$termino' BETWEEN cod_inicio AND cod_termino AND
        tab_condominios_responsabilidades.crp_id = $tipo AND
        tab_clientes.cli_id = $cliente AND
        tab_condominios_responsabilidades.cru_id = 4";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaResponsabilidadesExpirando() {
    $query = "
      SELECT
        tab_clientes_responsabilidades.cln_id AS id,
        tab_clientes_responsabilidades.cli_id AS cliente,
        tab_usuarios.usu_id AS usuario
      FROM
        tab_clientes_responsabilidades
		INNER JOIN tab_clientes ON (tab_clientes_responsabilidades.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_usuarios ON (tab_clientes_responsabilidades.usu_id = tab_usuarios.usu_id)
      WHERE
        tab_clientes_responsabilidades.cra_id = 4 AND
        tab_clientes_responsabilidades.cri_id = 2 AND
        tab_clientes_responsabilidades.cln_lembrete = 0 AND
        tab_clientes_responsabilidades.cod_id IS NULL AND
        NOW() BETWEEN DATE_SUB(tab_clientes_responsabilidades.cln_termino, INTERVAL 1 DAY) AND tab_clientes_responsabilidades.cln_termino AND
		tab_clientes.clo_id = 1 AND
        tab_usuarios.uss_id = 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaResponsabilidadesAguardando() {
    $query = "
      SELECT
        cln_id AS id
      FROM
        tab_clientes_responsabilidades
      WHERE
        cra_id = 1 AND
        NOW() > cln_termino";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  #################################
  ### Extender Responsabilidade ###
  #################################

  function extenderResponsabilidade($id, $tipo, $inicio, $termino, $cliente, $parametros) {
    if ($parametros["fase_atual"] == 0) {
      $prazo = $parametros["prazo1"];
      $condicao = $parametros["condicao1"];
      $quantidade = $parametros["quantidade1"];
    } elseif ($parametros["fase_atual"] == 1) {
      $prazo = $parametros["prazo2"];
      $condicao = $parametros["condicao2"];
      $quantidade = $parametros["quantidade2"];
    } elseif ($parametros["fase_atual"] == 2) {
      $prazo = $parametros["prazo3"];
      $condicao = $parametros["condicao3"];
      $quantidade = $parametros["quantidade3"];
    }
    $termino = date("Y-m-d H:i:s", strtotime("{$termino} +{$prazo} day"));
    if ($this->verificaCondicao($cliente, $inicio, $termino, $condicao, $quantidade)) {
      $query = "
        SELECT
          cln_id AS id
        FROM
          tab_clientes_responsabilidades
        WHERE
          (cra_id = 4) AND
          (cln_id != $id) AND
          (cri_id = $tipo) AND
          (cli_id = $cliente) AND
          ((cln_inicio BETWEEN '$inicio' AND '$termino') OR
          (cln_termino BETWEEN '$inicio' AND '$termino'))";
      $query = $this->bancodedados->query($query);
      if ($this->bancodedados->linhas($query)) {
        // Finaliza as responsabilidades com datas conflitantes
        $this->finalizaConflitantes($this->bancodedados->resultadoArrayTotal($query));
      }
      // Extende a Responsabilidade
      $parametros["fase_atual"]++;
      $parametros = serialize($parametros);
      $this->prorrogaResponsabilidade($id, $termino, $parametros);
      $this->registraComentario($id, "Prorrogado (Meta: $condicao)");
    } else {
      // Expira a Responsabilidade (Meta não-atingida)
      $this->expiraResponsabilidade($id, $tipo, $cliente, $termino);
    }
  }

  ###################################
  #### Prorroga Responsabilidade ####
  ###################################

  function prorrogaResponsabilidade($id, $termino, $parametros) {
    $query = "UPDATE tab_clientes_responsabilidades SET cln_termino = '$termino', cln_parametros = '$parametros', cln_lembrete = 0 WHERE cln_id = $id";
    $this->bancodedados->query($query);
  }

  #################################
  #### Expira Responsabilidade ####
  #################################

  function expiraResponsabilidade($id, $tipo, $cliente, $termino) {
    // Expira Responsabilidade
    $query = "UPDATE tab_clientes_responsabilidades SET cra_id = 6 WHERE cln_id = $id";
    $this->bancodedados->query($query);
    // Registra Comentário
    $this->registraComentario($id, "Expirado");
    // Atualiza Status Cliente
    if ($tipo == 2) {
      $query = "UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '$cliente'";
      $this->bancodedados->query($query);
    }
    // Herda Responsabilidade do Condomínio
    $responsabilidades = $this->buscaResponsabilidadesDoCondominio($tipo, $cliente, $termino);
    if ($responsabilidades) {
      $this->herdaResponsabilidade($cliente, $responsabilidades);
    }
  }

  ##############################
  ### Herda Responsabilidade ###
  ##############################

  function herdaResponsabilidade($cliente, $responsabilidades) {
    for ($i = 0; $i < sizeof($responsabilidades); $i++) {
      $responsabilidade = $responsabilidades[$i];
      $query = "INSERT INTO tab_clientes_responsabilidades (cli_id, cln_inicio, cln_termino, cod_id, usu_id, cri_id, cra_id) VALUES ('$cliente', NOW(), '{$responsabilidade["termino"]}', '{$responsabilidade["id"]}', '{$responsabilidade["usuario"]}', '{$responsabilidade["tipo"]}', '{$responsabilidade["status"]}')";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      $this->registraComentario($id, "Ativado (Herdado do Condomínio)");
      if ($responsabilidade["tipo"] == 2) {
        $query = "UPDATE tab_clientes SET usu_id = {$responsabilidade["usuario"]} WHERE cli_id = '{$responsabilidade["cliente"]}'";
        $this->bancodedados->query($query);
      }
    }
  }

  #################################
  ### Finaliza Responsabilidade ###
  #################################

  function finalizaConflitantes($conflitantes) {
    for ($i = 0; $i < sizeof($conflitantes); $i++) {
      $id = $conflitantes[$i]["id"];
      $query = "UPDATE tab_clientes_responsabilidades SET cra_id = 5 WHERE cln_id = $id";
      $this->bancodedados->query($query);

      $this->registraComentario($id, "Finalizado");
    }
  }

  ########################
  ##### Comentários ######
  ########################

  function registraComentario($id, $acao) {
    $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo) VALUES (NOW(), $id, '<b>Ação:</b> $acao')";
    $this->bancodedados->query($query);
  }

  ##########################
  ### Verifica Condições ###
  ##########################

  function verificaCondicao($cliente, $inicio, $termino, $condicao, $quantidade) {
    if ($quantidade == 0) {
      return true;
    }
    if ($condicao == "Prospects") {
      if ($this->verificaProspects($cliente)) {
        return true;
      } else {
        return false;
      }
    } elseif ($condicao == "Retornos") {
      if ($this->verificaRetornos($cliente, $inicio, $termino, $quantidade)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function verificaProspects($cliente) {
    $query = "
      SELECT
        COUNT(tab_clientes.cli_id) AS quantidade
      FROM
        tab_clientes
      WHERE
        tab_clientes.cli_id = $cliente AND
        tab_clientes.cli_cpf_cnpj IS NOT NULL";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query, 0, 'quantidade')) {
      return true;
    } else {
      return false;
    }
  }

  function verificaRetornos($cliente, $inicio, $termino, $quantidade) {
    $query = "
      SELECT
        COUNT(tab_clientes_retornos.cli_id) AS quantidade
      FROM
        tab_clientes_retornos
        INNER JOIN tab_clientes ON (tab_clientes_retornos.cli_id = tab_clientes.cli_id)
      WHERE
        tab_clientes.cli_id = $cliente AND
        tab_clientes_retornos.cet_id = 1 AND
        tab_clientes.usu_id = tab_clientes_retornos.usu_id AND
        tab_clientes_retornos.clr_abertura BETWEEN '$inicio' AND '$termino'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query, 0, 'quantidade') >= $quantidade) {
      return true;
    } else {
      return false;
    }
  }

  #######################################
  ### Atualiza Responsabilidade Nulas ###
  #######################################

  function atualizaResponsabilidadesNulasDiferentes() {
    $nulas = $this->buscaResponsabilidadesNulasDiferentes();
    for ($i = 0; $i < sizeof($nulas); $i++) {
      $query = "UPDATE tab_clientes SET usu_id = {$nulas[$i]["usuario"]} WHERE cli_id = '{$nulas[$i]["cliente"]}'";
      $this->bancodedados->query($query);
    }
  }

  #################################
  ### Adiciona Retorno de Aviso ###
  #################################

  function adicionaLembreteDePossivelTermino() {
    // Adiciona um retorno para o responsável avisando que sua responsabilidade deverá expirar
    $responsabilidades = $this->buscaResponsabilidadesExpirando();
    for ($i = 0; $i < sizeof($responsabilidades); $i++) {
      $responsabilidade = $responsabilidades[$i];
      $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ({$responsabilidade["cliente"]}, {$responsabilidade["usuario"]}, NOW(), NOW(), 'Atenção: Sua responsabilidade poderá expirar em 1 dia.', 2, 1)";
      $this->bancodedados->query($query);
      $query = "UPDATE tab_clientes_responsabilidades SET cln_lembrete = 1 WHERE cln_id = {$responsabilidade["id"]}";
      $this->bancodedados->query($query);
    }
  }

  ##########################################
  ### Expira Responsabilidade Aguardando ###
  ##########################################

  function expiraResponsabilidadesAguardando() {
    // Expira responsabilidades que estejam com status aguardando e já tenha passado seu término
    $responsabilidades = $this->buscaResponsabilidadesAguardando();
    for ($i = 0; $i < sizeof($responsabilidades); $i++) {
      $responsabilidade = $responsabilidades[$i];
      $query = "UPDATE tab_clientes_responsabilidades SET cra_id = 8 WHERE cln_id = {$responsabilidade["id"]}";
      $this->bancodedados->query($query);
      $this->registraComentario($responsabilidade["id"], "Análise (Expirado)");
    }
  }

}

?>
