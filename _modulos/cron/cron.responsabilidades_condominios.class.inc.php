<?

class cron_responsabilidades_condominios {

  var $bancodedados;
  var $clientes;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  ##################################
  ### Atualiza Responsabilidades ###
  ##################################

  function atualizaRespCondominios() {
    // Ativa responsabilidades que estão aprovadas e no periodo atual
    $this->ativaResponsabilidadesAprovadas();

    // Busca responsabilidades ativas que estão expiradas
    $resp_expiradas = $this->buscaResponsabilidadesAtivasExpiradas();

    for ($i = 0; $i < sizeof($resp_expiradas); $i++) {
      $id = $resp_expiradas[$i]["id"];
      $tipo = $resp_expiradas[$i]["tipo"];
      $inicio = $resp_expiradas[$i]["inicio"];
      $termino = $resp_expiradas[$i]["termino"];
      $condominio = $resp_expiradas[$i]["condominio"];
      $parametros = unserialize($resp_expiradas[$i]["parametros"]);
      switch ($parametros["fase_atual"]) {
        case 0:
        // Fase Inicial
        if ($parametros["prazo1"]) {
          $this->extenderResponsabilidade($id, $tipo, $inicio, $termino, $condominio, $parametros);
        } else {
          $this->expiraResponsabilidade($id, $tipo, $condominio);
        }
        break;

        case 1:
        // Fase 1
        if ($parametros["prazo2"]) {
          $this->extenderResponsabilidade($id, $tipo, $inicio, $termino, $condominio, $parametros);
        } else {
          $this->expiraResponsabilidade($id, $tipo, $condominio);
        }
        break;

        case 2:
        // Fase 2
        if ($parametros["prazo3"]) {
          $this->extenderResponsabilidade($id, $tipo, $inicio, $termino, $condominio, $parametros);
        } else {
          $this->expiraResponsabilidade($id, $tipo, $condominio);
        }
        break;
      }
    }
    $this->atualizaResponsabilidadesNulasDiferentes();
    $this->expiraResponsabilidadesAguardando();
  }

  #######################
  ### Ativa Aprovadas ###
  #######################

  function ativaResponsabilidadesAprovadas() {
    $query = "
      SELECT
        cod_id AS id,
        usu_id AS usuario,
        crp_id AS tipo,
        con_id AS condominio,
        cod_termino AS termino
      FROM
        tab_condominios_responsabilidades
      WHERE
        cru_id = 2 AND
        NOW() BETWEEN cod_inicio AND cod_termino";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $update = "UPDATE tab_condominios_responsabilidades SET cru_id = 4 WHERE cod_id = {$dados["id"]}";
      $this->bancodedados->query($update);
      if ($dados["tipo"] == 2) {
        $update = "UPDATE tab_condominios SET usu_id = {$dados["usuario"]} WHERE con_id = {$dados["condominio"]}";
        $this->bancodedados->query($update);
      }
      $this->registraComentario($dados["id"], "Ativado");
      $this->ativaResponsabilidadesAprovadasFilhas($dados["id"], $dados["usuario"]);
      // Atualiza Responsabilidades de Clientes q Já possuem Responsabilidade
      $clientes = $this->buscaClientesComResponsabilidade($dados);
      if ($clientes) {
    		$update = "UPDATE tab_clientes_responsabilidades SET cln_termino = '{$dados["termino"]}' WHERE cli_id IN (".implode(", ", $clientes).") AND cln_termino < '{$dados["termino"]}' AND cri_id = 2 AND cra_id = 4";
      	$this->bancodedados->query($update);
      }
    }
  }

  function buscaClientesComResponsabilidade($dados) {
    $query = "
      SELECT
        cli_id AS cliente_id
      FROM
        tab_clientes
      WHERE
        con_id = {$dados["condominio"]} AND
        usu_id = {$dados["usuario"]}";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$clientes[] = $dados["cliente_id"];
    }
    return $clientes;
  }

  function ativaResponsabilidadesAprovadasFilhas($id, $usuario) {
    $query = "
      SELECT
        cln_id AS id,
        cri_id AS tipo,
        cli_id AS cliente
      FROM
        tab_clientes_responsabilidades
      WHERE
        cod_id = $id AND
        cra_id = 2";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $update = "UPDATE tab_clientes_responsabilidades SET cra_id = 4 WHERE cln_id = {$dados["id"]}";
      $this->bancodedados->query($update);
      if ($dados["tipo"] == 2) {
        $update = "UPDATE tab_clientes SET usu_id = $usuario WHERE cli_id = '{$dados["cliente"]}'";
        $this->bancodedados->query($update);
        // Atualiza Status Cliente
        $this->atualizaStatusCliente($dados["cliente"]);
      }
      $this->registraComentarioCliente($dados["id"], "Ativado (Herdado do Condomínio)");
    }
  }

  ########################
  ### Funções de Busca ###
  ########################

  function buscaResponsabilidadesAtivasExpiradas() {
    $query = "
			SELECT
			  tab_condominios_responsabilidades.cod_id AS id,
			  tab_condominios_responsabilidades.crp_id AS tipo,
			  tab_condominios_responsabilidades.con_id AS condominio,
			  tab_condominios_responsabilidades.cod_inicio AS inicio,
			  tab_condominios_responsabilidades.cod_termino AS termino,
			  tab_condominios_responsabilidades.cod_parametros AS parametros
			FROM
			  tab_condominios_responsabilidades
			WHERE
			 	tab_condominios_responsabilidades.cru_id = 4 AND
				DATE_FORMAT(tab_condominios_responsabilidades.cod_termino, '%Y%m%d%H%i%s') < DATE_FORMAT(NOW(), '%Y%m%d%H%i%s')
			ORDER BY
        termino";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaClientesFilhos($id) {
    $query = "
      SELECT
        cln_id AS id,
        cli_id AS cliente
      FROM
        tab_clientes_responsabilidades
      WHERE
        cra_id = 4 AND
        cod_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaCondominiosResponsabilidadesNulasDiferentes() {
    $query = "
      SELECT
        tab_condominios_responsabilidades.con_id AS condominio,
        tab_condominios_responsabilidades.usu_id AS usuario
      FROM
        tab_condominios_responsabilidades
        INNER JOIN tab_condominios ON (tab_condominios_responsabilidades.con_id = tab_condominios.con_id)
      WHERE
        (tab_condominios.usu_id IS NULL OR
          tab_condominios.usu_id != tab_condominios_responsabilidades.usu_id) AND
        (tab_condominios_responsabilidades.crp_id = 2) AND
        (tab_condominios_responsabilidades.cru_id = 4) AND
        (NOW() BETWEEN tab_condominios_responsabilidades.cod_inicio AND tab_condominios_responsabilidades.cod_termino)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaClientesResponsabilidadesNulasDiferentes() {
    $query = "
      SELECT
        tab_clientes_responsabilidades.cli_id AS cliente,
        tab_clientes_responsabilidades.usu_id AS usuario
      FROM
        tab_clientes_responsabilidades
        INNER JOIN tab_clientes ON (tab_clientes_responsabilidades.cli_id = tab_clientes.cli_id)
      WHERE
        (tab_clientes.usu_id IS NULL OR
          tab_clientes.usu_id != tab_clientes_responsabilidades.usu_id) AND
        (tab_clientes_responsabilidades.cri_id = 2) AND
        (tab_clientes_responsabilidades.cra_id = 4) AND
        (NOW() BETWEEN tab_clientes_responsabilidades.cln_inicio AND tab_clientes_responsabilidades.cln_termino)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  function buscaResponsabilidadesAguardando() {
    $query = "
      SELECT
        cod_id AS id
      FROM
        tab_condominios_responsabilidades
      WHERE
        cru_id = 1 AND
        NOW() > cod_termino";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArrayTotal($query);
  }

  #################################
  ### Extender Responsabilidade ###
  #################################

  function extenderResponsabilidade($id, $tipo, $inicio, $termino, $condominio, $parametros) {
    if ($parametros["fase_atual"] == 0) {
      $prazo = $parametros["prazo1"];
      $condicao = $parametros["condicao1"];
      $quantidade = $parametros["quantidade1"];
    }
    if ($parametros["fase_atual"] == 1) {
      $prazo = $parametros["prazo2"];
      $condicao = $parametros["condicao2"];
      $quantidade = $parametros["quantidade2"];
    }
    if ($parametros["fase_atual"] == 2) {
      $prazo = $parametros["prazo3"];
      $condicao = $parametros["condicao3"];
      $quantidade = $parametros["quantidade3"];
    }
    $termino = date("Y-m-d H:i:s", strtotime("{$termino} +{$prazo} day"));
    if ($this->verificaCondicao($condominio, $inicio, $termino, $condicao, $quantidade)) {
      $query = "
        SELECT
          cod_id AS id
        FROM
          tab_condominios_responsabilidades
        WHERE
          (cru_id = 4) AND
          (cod_id != $id) AND
          (crp_id = $tipo) AND
          (con_id = $condominio) AND
          ((cod_inicio BETWEEN '$inicio' AND '$termino') OR
           (cod_termino BETWEEN '$inicio' AND '$termino'))";
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

      $this->prorrogaResponsabilidadeCliente($id, $tipo, $inicio, $termino, $parametros);
    } else {
      // Expira a Responsabilidade (Meta não-atingida)
      $this->expiraResponsabilidade($id, $tipo, $condominio);
    }
  }

  #########################################
  #### Prorroga Responsabilidade Dados ####
  #########################################

  function prorrogaResponsabilidade($id, $termino, $parametros) {
    $query = "UPDATE tab_condominios_responsabilidades SET cod_termino = '$termino', cod_parametros = '$parametros' WHERE cod_id = $id";
    $this->bancodedados->query($query);
  }

  function prorrogaResponsabilidadeCliente($id, $tipo, $inicio, $termino, $parametros) {
    $clientes = $this->buscaClientesFilhos($id);

    for ($i = 0; $i < sizeof ($clientes); $i++) {
      $id = $clientes[$i]["id"];
      $cliente = $clientes[$i]["cliente"];
      $this->finalizaConflitantesFilhos($id, $cliente, $tipo, $inicio, $termino);

      $query = "UPDATE tab_clientes_responsabilidades SET cln_termino = '$termino', cln_parametros = '$parametros' WHERE cln_id = $id";
      $this->bancodedados->query($query);
      $this->registraComentarioCliente($id, "Prorrogado (Herdado do Condomínio)");
    }
  }

  #################################
  ### Finaliza Responsabilidade ###
  #################################

  function expiraResponsabilidade($id, $tipo, $condominio) {
    $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 6 WHERE cod_id = $id";
    $this->bancodedados->query($query);
    $this->registraComentario($id, "Expirado");

    if ($tipo == 2) {
      $query = "UPDATE tab_condominios SET usu_id = NULL WHERE con_id = $condominio";
      $this->bancodedados->query($query);
    }

    $this->expiraResponsabilidadeCliente($id, $tipo);
  }

  function expiraResponsabilidadeCliente($id, $tipo) {
    $clientes = $this->buscaClientesFilhos($id);

    for ($i = 0; $i < sizeof($clientes); $i++) {
      $this->registraComentarioCliente($clientes[$i]["id"], "Expirado (Herdado do Condomínio)");

      if ($tipo == 2) {
        $query = "UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '{$clientes[$i]["cliente"]}'";
        $this->bancodedados->query($query);
        // Atualiza Status Cliente
        $this->atualizaStatusCliente($clientes[$i]["cliente"]);
      }
    }

    $query = "UPDATE tab_clientes_responsabilidades SET cra_id = 6 WHERE cod_id = $id";
    $this->bancodedados->query($query);
  }

  #################################
  ### Finaliza Responsabilidade ###
  #################################

  function finalizaConflitantes($conflitantes) {
    for ($i = 0; $i < sizeof($conflitantes); $i++) {
      $id = $conflitantes[$i]["id"];
      $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 5 WHERE cod_id = $id";
      $this->bancodedados->query($query);

      $this->registraComentario($id, "Finalizado");
    }
  }

  function finalizaConflitantesFilhos($id, $cliente, $tipo, $inicio, $termino) {
    $query = "
      SELECT
        cln_id AS id
      FROM
        tab_clientes_responsabilidades
      WHERE
        (cln_id != '$id') AND
        (cri_id = '$tipo') AND
        (cli_id = '$cliente') AND
        ((cln_inicio BETWEEN '$inicio' AND '$termino') OR
        (cln_termino BETWEEN '$inicio' AND '$termino'))";
    $query = $this->bancodedados->query($query);
    $conflitantes = $this->bancodedados->resultadoArrayTotal($query);

    for ($i = 0; $i < sizeof($conflitantes); $i++) {
      $cln = $conflitantes[$i]["id"];
      $query = "UPDATE tab_clientes_responsabilidades SET cra_id = 5 WHERE cln_id = $cln";
      $this->bancodedados->query($query);

      $this->registraComentarioCliente($cln, "Finalizado");
    }
  }

  ########################
  ##### Comentários ######
  ########################

  function registraComentario($id, $acao) {
    $query = "INSERT INTO tab_condominios_responsabilidades_comentarios (crm_datahora, cod_id, crm_conteudo) VALUES (NOW(), $id, '<b>Ação:</b> $acao')";
    $this->bancodedados->query($query);
  }

  function registraComentarioCliente($id, $acao) {
    $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo) VALUES (NOW(), $id, '<b>Ação:</b> $acao')";
    $this->bancodedados->query($query);
  }

  ##########################
  ### Verifica Condições ###
  ##########################

  function verificaCondicao($condominio, $inicio, $termino, $condicao, $quantidade) {
    if ($quantidade == 0) return true;

    switch ($condicao) {
      case "Ativos":
      // Ativos
      if ($this->verificaAtivos($condominio, $inicio, $termino, $quantidade)) {
        return true;
      } else {
        return false;
      }
      break;

      case "Prospects":
      // Prospects
      if ($this->verificaProspects()) {
        return true;
      } else {
        return false;
      }
      break;
    }
  }

  function verificaAtivos($condominio, $inicio, $termino, $quantidade) {
    $query = "
      SELECT
        COUNT(tab_clientes.cli_id) AS quantidade
      FROM
        tab_condominios_blocos
        INNER JOIN tab_clientes ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
        INNER JOIN tab_contratos ON (tab_clientes.cli_id = tab_contratos.cli_id)
      WHERE
        tab_condominios_blocos.con_id = $condominio AND
        tab_contratos.cor_data_ativacao BETWEEN '$inicio' AND '$termino'";
    $query = $this->bancodedados->query($query);
    if ($this->bancodedados->resultado($query,0,'quantidade') >= $quantidade) { return true; }
    return false;
  }

  function verificaProspects() {
    // Só serve de pai para os clientes
    return true;
  }

  #######################################
  ### Atualiza Responsabilidade Nulas ###
  #######################################

  function atualizaResponsabilidadesNulasDiferentes() {
    $condominio_nulas = $this->buscaCondominiosResponsabilidadesNulasDiferentes();

    for ($i = 0; $i < sizeof($condominio_nulas); $i++) {
      $query = "UPDATE tab_condominios SET usu_id = {$condominio_nulas[$i]["usuario"]} WHERE con_id = {$condominio_nulas[$i]["condominio"]}";
      $this->bancodedados->query($query);
    }

    $cliente_nulas = $this->buscaClientesResponsabilidadesNulasDiferentes();

    for ($i = 0; $i < sizeof($cliente_nulas); $i++) {
      $query = "UPDATE tab_clientes SET usu_id = {$cliente_nulas[$i]["usuario"]} WHERE cli_id = '{$cliente_nulas[$i]["cliente"]}'";
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
      $query = "UPDATE tab_condominios_responsabilidades SET cru_id = 8 WHERE cod_id = {$responsabilidade["id"]}";
      $this->bancodedados->query($query);
      $this->registraComentario($responsabilidade["id"], "Análise (Expirado)");
    }
  }

  ###############################
  ### Atualiza Status Cliente ###
  ###############################

  function atualizaStatusCliente($cliente_id) {
    if (!$this->clientes) {
      $this->clientes = controle::instanciaObjeto("clientes", $this->bancodedados, $this->permissoes, false);
    }
    $this->clientes->atualizaStatusCliente($cliente_id);
  }

}

?>
