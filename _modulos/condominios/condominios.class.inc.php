<?

require_once("_global/modulo.class.inc.php");

class condominios extends modulo {

    function __construct($bancodedados, $permissoes, $status = '%', $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    $this->status = $status;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      if ($_GET["a"] == "ver_dc" && $this->permissoes[$this->dados_modulo["nome"]]["editar_dc"]) $_GET["a"] = "editar_dc";
      if ($_GET["a"] == "ver_df" && $this->permissoes[$this->dados_modulo["nome"]]["editar_df"]) $_GET["a"] = "editar_df";
      if ($_GET["a"] == "ver_dm" && $this->permissoes[$this->dados_modulo["nome"]]["editar_dm"]) $_GET["a"] = "editar_dm";
      if ($_GET["a"] == "ver_dt" && $this->permissoes[$this->dados_modulo["nome"]]["editar_dt"]) $_GET["a"] = "editar_dt";
      if ($_GET["a"] == "ver_ch" && $this->permissoes[$this->dados_modulo["nome"]]["editar_ch"]) $_GET["a"] = "editar_ch";
      $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "buscar");
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  function buscaDadosItem($condominio_id) {
    $query = "
  		SELECT
  		  tab_condominios.con_id AS id,
  			tab_condominios.con_nome AS nome,
  			DATE_FORMAT(tab_condominios.con_data_cadastro, '%d/%m/%Y') AS data_cadastro,
  			DATE_FORMAT(tab_condominios.con_data_ativacao, '%d/%m/%Y') AS data_ativacao,
  			tab_condominios.cep_id AS cep,
  			tab_condominios.con_numero AS numero,
  			tab_condominios.con_comentarios AS comentarios,
  			tab_condominios.con_comentarios_comercial AS comentarios_comercial,
  			tab_condominios.con_velocidade_maxima AS velocidade_maxima,
  			tab_condominios.cnt_id AS tipo_id,
  			tab_condominios.con_pai AS condominio_pai,
  			tab_condominios_instalacoes_tipos.cit_id AS instalacao_tipo_id,
  			tab_condominios_instalacoes_tipos.cit_nome AS instalacao_tipo_nome,
  			tab_condominios_tipos.cnt_id AS tipo_id,
  			tab_condominios_tipos.cnt_nome AS tipo_nome,
  			tab_condominios_status.cos_id AS status_id,
  			tab_condominios_status.cos_nome AS status_nome,
  			tab_condominios_situacoes.coi_id AS situacao_id,
  			tab_condominios_situacoes.coi_nome AS situacao_nome,
  			tab_usuarios.usu_id AS responsavel_id,
  			tab_usuarios.usu_nome AS responsavel_nome,
  			tab_condominios_tecnologias.cog_id AS tecnologia_id,
  			tab_condominios_tecnologias.cog_nome AS tecnologia_nome,
  			tab_condominios_tecnologias_equipamentos.coq_id AS equipamento_id,
  			tab_condominios_tecnologias_equipamentos.coq_nome AS equipamento_nome,
  			tab_condominios_classes.ccl_id AS classe_id,
  			tab_condominios_classes.ccl_nome AS classe_nome
  		FROM
  			tab_condominios
  			INNER JOIN tab_condominios_instalacoes_tipos ON (tab_condominios.cit_id = tab_condominios_instalacoes_tipos.cit_id)
  			INNER JOIN tab_condominios_tipos ON (tab_condominios.cnt_id = tab_condominios_tipos.cnt_id)
  			INNER JOIN tab_condominios_situacoes ON (tab_condominios.coi_id = tab_condominios_situacoes.coi_id)
  			INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
  			LEFT OUTER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)
  			LEFT OUTER JOIN tab_condominios_classes ON (tab_condominios.ccl_id = tab_condominios_classes.ccl_id)
        LEFT OUTER JOIN tab_condominios_tecnologias ON (tab_condominios.cog_id = tab_condominios_tecnologias.cog_id)
        LEFT OUTER JOIN tab_condominios_tecnologias_equipamentos ON (tab_condominios.coq_id = tab_condominios_tecnologias_equipamentos.coq_id)
  		WHERE".($this->status && $this->status != "%" ? "
  			tab_condominios.cos_id = {$this->status} AND" : "")."
  			tab_condominios.con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      if ($this->permissoes["condominios"]["buscar_plus"] || $this->permissoes["condominios"]["buscar_plus_plus"] || $this->dados_item["status_id"] == 2 || $this->dados_item["responsavel_id"] == $_SESSION["usuario"]) {
        // Busca Endereço
        $endereco = $this->buscaEnderecoCEP($this->dados_item["cep"], $this->dados_item["numero"]);
        if ($endereco) {
        	$this->dados_item["cep"] = substr($this->dados_item["cep"], 0, 5)."-".substr($this->dados_item["cep"], 5, 3);
        	$this->dados_item["estado"] = $endereco["estado"];
          $this->dados_item["estado_sigla"] = $endereco["estado_sigla"];
          $this->dados_item["cidade"] = $endereco["cidade"];
          $this->dados_item["bairro"] = $endereco["bairro"];
          $this->dados_item["logradouro"] = $endereco["logradouro"];
          $this->dados_item["endereco"] = $endereco["endereco"];
        }
      } else {
        unset($this->dados_item);
      }
    }
  }

  function buscaEnderecoCEP($cep_id, $numero) {
    $query = "
      SELECT
        tab_estados.est_nome AS estado,
        tab_estados.est_sigla AS estado_sigla,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro
      FROM
        tab_ceps
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        tab_ceps.cep_id = $cep_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["endereco"][] = $dados["logradouro"];
      if ($numero) {
        $dados["endereco"][] = $numero;
      }
      $dados["endereco"][] = $dados["bairro"];
      $dados["endereco"][] = $dados["cidade"];
      $dados["endereco"][] = $dados["estado_sigla"];
      $dados["endereco"] = @implode(", ", $dados["endereco"]);
      return $dados;
    }
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaSituacoes() {
    $query = "
      SELECT
        coi_id AS id,
        coi_nome AS nome
      FROM
        tab_condominios_situacoes
      ORDER BY
        coi_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $situacoes[$dados["id"]] = $dados["nome"];
    }
    return $situacoes;
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

  function buscaTipos() {
    $query = "
      SELECT
        cnt_id AS id,
        cnt_nome AS nome
      FROM
        tab_condominios_tipos
      ORDER BY
        id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaClasses() {
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
      $classes[$dados["id"]] = $dados["nome"];
    }
    return $classes;
  }

  function buscaTipo($tipo_id) {
    $query = "
      SELECT
        cnt_id AS id,
        cnt_nome AS nome
      FROM
        tab_condominios_tipos
      WHERE
        cnt_id = $tipo_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }

  function buscaResponsaveis($somente_ativos = true, $usuario_atual_id = null) {
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
        tab_modulos.mod_nome = 'clientes_responsabilidades'".($somente_ativos ? " AND
        (tab_usuarios.uss_id = 1".($usuario_atual_id ? " OR tab_usuarios.usu_id = $usuario_atual_id" : "").")" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }


  function buscaBlocos($condominio_id, $editar = true, $instalacao_individual = false) {
  	$lista_interfaces = $this->buscaServidoresInterfaces();
  	$query = "
			SELECT
	 			tab_condominios_blocos.cob_id AS id,
	 			tab_condominios_blocos.cob_nome AS bloco,
	 			tab_condominios_blocos.sei_id AS interface_id,
	 			tab_condominios_blocos.cbs_id AS status_bloco,
	 			tab_condominios_blocos.cob_equipamento_ip AS equipamento_ip,
	 			tab_condominios_blocos.cob_ap_ip AS ap_ip,
	 			COUNT(DISTINCT tab_clientes_pontos.clp_id) + COUNT(DISTINCT tab_clientes_autenticacoes.cau_id) as pontos_ativos
			FROM
				tab_condominios_blocos
				LEFT OUTER JOIN tab_clientes ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id AND tab_clientes.cls_id = 2)
				LEFT OUTER JOIN tab_assinaturas ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				LEFT OUTER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id AND tab_clientes_pontos.cps_id = 1)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id AND tab_clientes_autenticacoes.cas_id = 1)
			WHERE
	  		tab_condominios_blocos.con_id = $condominio_id
	  	GROUP BY
	  		id
			ORDER BY
	  		bloco";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($instalacao_individual) {
        $dados["interface"] = "Instalação individual";
    	} else {
	      if ($editar) {
	        $dados["interface"] = "<div style=\"text-align:left\">".$this->geraSelect($lista_interfaces, $dados["interface_id"], true, "interface[{$dados["id"]}]", "263px");
	        if ($this->permissoes["servidores_interfaces"]["ver_dc"]) {
	          $dados["interface"] .= '<input type="button" class="buttonAzulEscuroBranco11" name="ver_interface" id="ver_interface" value="Ver" style="margin-left:3px;width:33px" onClick="verServidorInterface(document.getElementById(\'interface_'.$dados["id"].'\').value)">';
	        }
	        $dados["interface"] .= "</div>";
	        $dados["equipamento_ip"] = "<input type=\"text\" name=\"equipamento_ip[{$dados["id"]}]\" class=\"inputBrancoAzulEscuro11\" style=\"width:100px\" value=\"{$dados["equipamento_ip"]}\" />";
	        $dados["ap_ip"] = "<input type=\"text\" name=\"ap_ip[{$dados["id"]}]\" class=\"inputBrancoAzulEscuro11\" style=\"width:100px\" value=\"{$dados["ap_ip"]}\" />";
	      } else {
	        $dados["interface"] = $lista_interfaces[$dados["interface_id"]];
	      }
    	}
      $interfaces[] = $dados;
    }
    $cabecalho = array ("bloco" => "Bloco", "interface" => "Interface", "equipamento_ip" => "Equipamento » IP", "ap_ip" => "AP » IP", "pontos_ativos" => "Pontos");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $interfaces, 0, false, array(0, 300, 100, 100, 50), null, array("center", "left", "left", "left", "center"));
    return $listagem->conteudo;
  }

  function buscaServidoresInterfaces() {
    $query = "
			SELECT DISTINCT
				tab_servidores_interfaces.sei_id AS id,
				tab_servidores_interfaces.sei_nome AS interface_nome,
				tab_servidores.ser_nome AS servidor_nome,
				CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip) AS ip,
	 			tab_servidores.ser_nome AS nome
			FROM
	  		tab_servidores
	  		INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
			WHERE
	  		(tab_servidores_interfaces.sit_id = 1 AND tab_servidores_interfaces.sei_pai IS NULL) OR
	  		(tab_servidores_interfaces.sit_id = 6 AND tab_servidores_interfaces.sei_pai IS NOT NULL) 
			ORDER BY
	  		nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      //$dados["ip"] = $dados["ip"] ? $dados["ip"] : $this->buscaIPsRede($dados["id"]);
      $dados["ordem"] = $dados["servidor_nome"].sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["interface_nome"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["interface_nome"]));
      $dados["nome"] = "{$dados["servidor_nome"]} ({$dados["interface_nome"]}".($dados["ip"] ? ", {$dados["ip"]}" : "").")";
      $interfaces[] = $dados;
    }
	  $interfaces = $this->ordena_multiarray($interfaces, array("ordem"));
	  foreach ($interfaces as $dados) {
	    $retorno[$dados["id"]] = $dados["nome"];
	  }
	  return $retorno;
  }
  
  function buscaIPsRede($interface_id) {
		$query = "
  	  SELECT
  	    red_id AS id,
  	    red_endereco_ip AS endereco_ip,
  	    red_mascara AS mascara_rede
  	  FROM
  	    tab_redes
  	  WHERE
  	    sei_id = $interface_id";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ips[] = $this->geraIP($dados["endereco_ip"], 1, $dados["mascara_rede"]);
		}
		return $ips ? implode(", ", $ips) : "";
  }

	function geraIP($prefixo, $sub_rede, $mascara) {
		$sub_rede = decbin($sub_rede);
		$prefixo = explode(".", $prefixo);
		for ($i = 0; $i < count($prefixo); $i++) {
			$prefixo[$i] = sprintf("%08s", decbin($prefixo[$i]));
		}
		$prefixo = implode($prefixo);
		$mascara = sprintf("%0-32s", str_repeat("1", $mascara));
		$endereco_ip = sprintf("%0-32s", decbin(bindec($prefixo & $mascara) + bindec($sub_rede)));
		for ($i = 0; $i < 4; $i++) {
			$resultado[] = bindec(substr($endereco_ip, $i * 8, 8));
		}
		return implode(".", $resultado);
	}

  function buscaStatusDoCondominio($id) {
    $query = "SELECT cos_id AS id FROM tab_condominios WHERE con_id = '$id'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 0);
  }
  
  function buscaStatusDoBloco($id) {
    $query = "SELECT cbs_id AS id FROM tab_condominios WHERE con_id = '$id'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 0);
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
      $tamanho_chave = 0;
      reset($array);
      $array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
      	$chave = array();
        for ($i = 0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = implode(",", $chave);
        $tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
        next($array);
      }
      reset($array);
      while (($array_ordenacao_atual = current($array)) != false) {
      	$chave = array();
        for ($i = 0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
        }
        next($array);
      }
      ksort($array_ordenacao);
      while (is_int($array_id = current($array_ordenacao))) {
        $array_final[] = $array[$array_id];
        next($array_ordenacao);
      }
      return $array_final;
    }
  }
  
  function buscaPlanosPermissoes($condominio_id, $editavel = false) {
    $query = "
      SELECT
        p.pla_id AS plano_id,
        p.pla_nome AS plano_nome,
        p.pla_instalacao AS instalacao,
        p.pla_mensalidade AS mensalidade,
        s.pls_id AS servico_id,
        s.pls_nome AS servico_nome
      FROM
        tab_planos p
        INNER JOIN tab_planos_servicos s ON (p.pls_id = s.pls_id)
      WHERE
        p.plu_id = 1".(!$editavel ? " AND
        p.pla_id IN (SELECT r.pla_id FROM rel_con_pla_tempermissao r WHERE r.con_id = $condominio_id)" : "")."
      ORDER BY
        s.pls_nome,
        p.pla_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		  $dados["instalacao"] = $this->formataDinheiro($dados["instalacao"]);
		  $dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"]);
		  $dados["permissao"] = $this->buscaPermissaoPlano($condominio_id, $dados["plano_id"]);
		  if ($editavel) {
		    $dados["permissao_nome"] = $this->geraSelect($this->getTiposPermissoesPlanos(), $dados["permissao"], false, "permissao_plano[{$dados["plano_id"]}]", "80px");
		  } else {
		    $dados["permissao_nome"] = $this->getTiposPermissoesPlanos($dados["permissao"]);
		  }
			$permissoes[] = $dados;
		}
		return $permissoes;
  }
  
  function getTiposPermissoesPlanos($permissao = null) {
    $permissoes[0] = "-";
    $permissoes[1] = "Permitido";
    $permissoes[2] = "Negado";
    return $permissao !== null ? $permissoes[$permissao] : $permissoes;
  }
  
  function buscaPermissaoPlano($condominio_id, $plano_id) {
    $query = "
      SELECT
        r.rcp_permissao AS permissao
      FROM
        rel_con_pla_tempermissao r
      WHERE
        r.con_id = $condominio_id AND
        r.pla_id = $plano_id";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		if ($dados) {
		  return $dados["permissao"];
		}
		return "0";
  }

  function geraListagemPermissoesPlanos($dados) {
    $cabecalho = array ("servico_nome" => "Serviço", "plano_nome" => "Plano", "instalacao" => "Instalação", "mensalidade" => "Mensalidade", "permissao_nome" => "Permissão");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(0, 0, 80, 80, 80), null, array("left", "center", "right", "right", "center"));
    return $listagem->conteudo;
  }

  #############
  ### Geral ###
  #############

  function geraCheckBoxes($nome, $itens, $selecionados = null, $desativados = null, $todos_desativados = false) {
    if (!$itens) return "Nenhum registro disponível";
    $i = 0;
    $check_boxes = array();
    foreach ($itens as $id => $descricao) {
      $check_boxes[] = "<input type=\"checkbox\" name=\"{$nome}[]\" value=\"$id\" id=\"{$nome}_$i\"".($selecionados && in_array($id, $selecionados) ? " checked" : "").($todos_desativados || ($desativados && in_array($id, $desativados)) ? " disabled" : "")."><label for=\"{$nome}_$i\"> $descricao</label><br>";
      if ($desativados && in_array($id, $desativados)) {
      	$check_boxes[] = "<input type=\"hidden\" name=\"{$nome}[]\" value=\"$id\">";
      }
      $i++;
    }
    return implode("", $check_boxes);
  }

  function corrigeNome($nome) {
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
    $nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
    $nome = str_replace("'", "`", $nome); // Substitui apóstrofo por crase
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" $minusculas[$i] ", " $minusculas[$i] ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" $maiusculas[$i] ", " $maiusculas[$i] ", " $nome "); // Substitui termos maiúsculos
    return trim($nome);
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("condominios.adicionar.inc.php");
      $id = $this->geraIdProspect();
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar&c=$id", $this->conteudo);
      $this->conteudo = str_replace('[condominio_id]', $id, $this->conteudo);
      $this->conteudo = str_replace('[blocos]', $this->buscaCondominioBlocos($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[condominios_contatos]', $this->buscaCondominioContatos($this->dados_item["id"]), $this->conteudo);

		if ($this->permissoes["ceps"]["pesquisar"]) {
			$this->conteudo = str_replace("[pesquisar_cep]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />", $this->conteudo);
		} else {
			$this->conteudo = str_replace("[pesquisar_cep]", "", $this->conteudo);
		}

      // Adicionar Bloco
      if ($this->permissoes["condominios_blocos"]["adicionar"]) {
        $this->conteudo = str_replace("[blocos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="blocos_adicionar" id="blocos_adicionar" value="Adicionar" onClick="adicionarBloco('.$id.')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[blocos_adicionar]", '', $this->conteudo);
      }
      // Adicionar Contato
      if ($this->permissoes["condominios_contatos"]["adicionar"]) {
        $this->conteudo = str_replace("[contatos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="contatos_adicionar" id="contatos_adicionar" value="Adicionar" onClick="adicionarContato('.$id.')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[contatos_adicionar]", '', $this->conteudo);
      }
      $this->conteudo = str_replace('[tipos]', $this->geraSelect($this->buscaTipos(), null, true, "tipo", "250px"),$this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $_SESSION["usuario"], true, "responsavel", "250px"), $this->conteudo);

      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
      $_POST["numero"] = $_POST["numero"] ? "'{$_POST["numero"]}'" : "NULL";
      $_POST["cep"] = preg_replace("/[^0-9]/", "", $_POST["cep"]);
      $query = "UPDATE tab_condominios SET con_data_cadastro = NOW(), con_nome = '{$_POST["nome"]}', cep_id = '{$_POST["cep"]}', con_numero = {$_POST["numero"]}, con_comentarios = '{$_POST["comentarios"]}', con_velocidade_maxima = 10485760, coi_id = '1', cnt_id = '{$_POST["tipo"]}', cos_id = 1 WHERE con_id = '{$_GET["c"]}'";
      $this->bancodedados->query($query);
      $query = "UPDATE tab_condominios_blocos SET cep_id = NULL, cob_numero = NULL WHERE cep_id = '{$_POST["cep"]}' AND cob_numero = {$_POST["numero"]} AND con_id = '{$_GET["c"]}'";
      $this->bancodedados->query($query);
			if ($_POST["responsavel"]) {
	      $id = $this->registraResponsabilidade($_GET["c"], $_POST["responsavel"]);
	      $this->registraComentarioResponsabilidade($id, $_POST["responsavel"]);
			}
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["c"]);
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["c"]}");
    }
  }

  function geraIdProspect() {
    $query = "
      SELECT
        con_id AS id
      FROM
      	tab_condominios
      WHERE
        usu_id = {$_SESSION["usuario"]} AND
        cos_id IS NULL";
    $query = $this->bancodedados->query($query);
    if ( $this->bancodedados->linhas($query) ) {
      $id = $this->bancodedados->resultado($query, 0, "id");
      $query = "DELETE tab_condominios_contatos FROM tab_condominios_contatos INNER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_condominios_contatos.cob_id) WHERE tab_condominios_blocos.con_id = '$id'";
      $this->bancodedados->query($query);
      $query = "DELETE FROM tab_condominios_blocos WHERE con_id = '$id'";
      $this->bancodedados->query($query);
    } else {
      $query = "INSERT INTO tab_condominios (usu_id) VALUES ({$_SESSION["usuario"]})";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
    }
    return $id;
  }

  function registraResponsabilidade($condominio_id, $responsavel) {
    $parametro = $this->buscaResponsabilidadeParametros();
    $parametros = unserialize($parametro);
    $termino = date("Y-m-d H:i:s", strtotime("+ {$parametros["prazo"]} days"));
    // Altera Responsável Condomínio
    if ($this->temPermissao("assumir_responsabilidade", "condominios_responsabilidades", $responsavel)) {
      $tipo = 2;
      $query = "UPDATE tab_condominios SET usu_id = $responsavel WHERE con_id = $condominio_id";
      $query = $this->bancodedados->query($query);
    } else {
      $tipo = 1;
      $query = "UPDATE tab_condominios SET usu_id = NULL WHERE con_id = $condominio_id";
      $query = $this->bancodedados->query($query);
    }
    // Registra Responsável
    $query = "INSERT INTO tab_condominios_responsabilidades (con_id, cod_inicio, cod_termino, cod_parametros, usu_id, crg_id, crp_id, cru_id) VALUES ($condominio_id, NOW(), '$termino', '$parametro', $responsavel, 1, $tipo, 4)";
    $query = $this->bancodedados->query($query);
    // Retorna ID
    return $this->bancodedados->ultimoIdInserido();
  }

  function registraComentarioResponsabilidade($id, $responsavel) {
    $query = "INSERT INTO tab_condominios_responsabilidades_comentarios (cod_id, crm_datahora, crm_conteudo, usu_id) VALUES ($id, NOW(), '<b>Ação:</b> Adicionado com Auto-Ativação', $responsavel)";
    $this->bancodedados->query($query);
  }

  function buscaResponsabilidadeParametros() {
    $query = "
      SELECT
        crg_parametros AS parametros
      FROM
        tab_condominios_responsabilidades_categorias
      WHERE
        crg_id = 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "parametros");
  }

	function buscaPontos($condominio_id) {
		
		$pontos = array();
		$lista_unidades = $this->buscaUnidadesVelocidade();
		
		$query = "
			SELECT
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS nome,
				SUBSTRING(tab_clientes.cli_complemento, 2) + 0 AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.clp_endereco_mac AS mac,
				tab_clientes_pontos.clp_velocidade_download AS download,
				tab_clientes_pontos.clp_velocidade_upload AS upload,
				tab_clientes_pontos_status.cps_id AS status_id,
				tab_clientes_pontos_status.cps_nome AS status,
				tab_clientes_pontos.clp_publicado AS publicado,
				tab_clientes_pontos.clp_ajustado AS ajustado,
				tab_clientes_pontos.clp_parametros_ajuste AS parametros_ajuste,
				tab_assinaturas.ass_parametros AS parametros,
				tab_clientes_pontos.clp_ip AS ip
			FROM
				tab_clientes_pontos
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
				INNER JOIN tab_clientes_pontos_status ON (tab_clientes_pontos.cps_id = tab_clientes_pontos_status.cps_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
			WHERE
				tab_clientes.con_id = $condominio_id AND
				tab_clientes_pontos.cps_id IN (1, 2)
			GROUP BY
				tab_clientes_pontos.clp_id
			ORDER BY
				bloco,
				complemento,
				ip";
			
		$query = $this->bancodedados->query($query);
		
		while (($ponto = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["clientes_pontos"]["ver"]) {
				$ponto["link"] = "javascript:verPonto({$ponto["id"]})";
			}
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$ponto["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$ponto["cliente_id"]}\" class=\"txtAzulEscuro11\">{$ponto["cliente_id"]}</a>";
			}
			$parametros = unserialize($ponto["parametros_ajuste"]);
			// Trata Status
			$bloqueado = $ponto["download"] <= 0 || ($ponto["ajustado"] && $parametros["download"] <= 0);
			if ($ponto["status_id"] != 3) {
				if ($bloqueado) {
					$ponto["status"] .= "<br>(Bloqueado)";
				}
				if ($ponto["publicado"] && $ponto["status_id"] != 2 && !$bloqueado) {
					$ponto["status"] .= "<br>(Publicado)";
				} elseif (!$ponto["publicado"]) {
					$ponto["status"] .= "<br>(Não Publicado)";
				}
			}
			if ($ponto["status_id"] == 1 && $ponto["publicado"] && !$bloqueado) {
				$ponto["status"] = "<span style=\"color:green\">{$ponto["status"]}<span>";
			} else {
				$ponto["status"] = "<span style=\"color:red\">{$ponto["status"]}<span>";
			}
			$unidade_download = floor(log($ponto["download"], 1024));
			if ($unidade_download < 1) $unidade_download = 1;
			$ponto["velocidade"] = round($ponto["download"] / pow(1024, $unidade_download), 1)." / ".
			round($ponto["upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
			if ($ponto["parametros_ajuste"] && $ponto["ajustado"]) {
				$unidade = floor(log($parametros["download"], 1024));
				if ($unidade < 1) $unidade = 1;
				$velocidade = round($parametros["download"] / pow(1024, $unidade), 1)." / ".round($parametros["upload"] / pow(1024, $unidade), 1)." ".
				$lista_unidades[$unidade];
				$ponto["velocidade"] .= "<br>($velocidade)";
			}
			// Trata apartamento
			if ($ponto["complemento"]) $ponto["apartamento"][] = $ponto["complemento"];
			if ($ponto["bloco"] && $ponto["bloco"] != "Único") $ponto["apartamento"][] = $ponto["bloco"];
			$ponto["apartamento"] = $ponto["apartamento"] ? implode(" / ", $ponto["apartamento"]) : "-";
			$ponto["mac_ip"] = array();
			if ($ponto["mac"]) $ponto["mac_ip"][] = $ponto["mac"];
			if ($ponto["ip"]) $ponto["mac_ip"][] = "({$ponto["ip"]})";
			$ponto["mac_ip"] = implode("<br>", $ponto["mac_ip"]);
			$ponto["cont"] = count($pontos) + 1;
			$pontos[] = $ponto;
			
		}
		
		$cabecalho = array ("cont" => "#", "cliente_id" => "ID", "nome" => "Cliente", "apartamento" => "Apartamento", "mac_ip" => "MAC (IP)", "velocidade" => "Velocidade", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $pontos, 0, false, array(), null, array("right"));
		
		return $listagem->conteudo;
	
	}
  
  function buscaAutenticacoes($condominio_id) {

		$autenticacoes = array();
		$lista_unidades = $this->buscaUnidadesVelocidade();

		$query = "
			SELECT
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS nome,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.cau_usuario AS usuario,
				tab_clientes_autenticacoes.cau_endereco_ip AS endereco_ip,
				tab_clientes_autenticacoes.cau_velocidade_download AS download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS upload,
				tab_clientes_autenticacoes.cau_ajuste_download AS ajuste_download,
				tab_clientes_autenticacoes.cau_ajuste_upload AS ajuste_upload,
				tab_clientes_autenticacoes.cau_concentrador_ip AS concentrador_ip,
				tab_clientes_autenticacoes.cau_concentrador_nome AS concentrador_nome,
				tab_clientes_autenticacoes_status.cas_id AS status_id,
				tab_clientes_autenticacoes_status.cas_nome AS status,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				IF(tab_clientes_autenticacoes.ser_id IS NULL, 'Não<br>definido', CONCAT(tab_servidores.ser_nome, '<br><span style=\"color:#232323\">(', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')</span>')) AS concentrador,
				tab_clientes_autenticacoes.cau_ajustado AS ajustado
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_clientes_autenticacoes_status ON (tab_clientes_autenticacoes.cas_id = tab_clientes_autenticacoes_status.cas_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_clientes_autenticacoes.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
				LEFT OUTER JOIN tab_servidores ON (tab_clientes_autenticacoes.ser_id = tab_servidores.ser_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_clientes.con_id = $condominio_id AND
				tab_clientes_autenticacoes.cas_id IN (1, 2)
			GROUP BY
				tab_clientes_autenticacoes.cau_id
			ORDER BY
				bloco,
				complemento";
			
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($this->permissoes["clientes_pontos"]["ver"]) {
				$dados["link"] = "javascript:verAutenticacao({$dados["id"]})";
			}
			if ($this->permissoes["clientes"]["ver_dt"]) {
				$dados["cliente_id"] = "<a href=\"./?m=clientes&a=ver_dt&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			// Usuário
			if ($dados["endereco_ip"]) {
				$dados["usuario"] .= "<br>({$dados["endereco_ip"]})";
			}
			// Trata Status
			$bloqueado = $dados["download"] <= 0 || ($dados["ajustado"] && $dados["ajuste_download"] <= 0);

			if ($dados["status_id"] != 3) {
				if ($bloqueado) {
					$dados["status"] .= "<br>(Bloqueado)";
				}
				if ($dados["publicado"] && $dados["status_id"] != 2 && !$bloqueado) {
					$dados["status"] .= "<br>(Publicado)";
				} elseif (!$dados["publicado"]) {
					$dados["status"] .= "<br>(Não Publicado)";
				}
			}
			if ($dados["status_id"] == 1 && $dados["publicado"] && !$bloqueado) {
				$dados["status"] = "<span style=\"color:green\">{$dados["status"]}<span>";
			} else {
				$dados["status"] = "<span style=\"color:red\">{$dados["status"]}<span>";
			}
			// Trata Velocidade
			$unidade_download = floor(log($dados["download"], 1024));
			if ($unidade_download < 1) $unidade_download = 1;
			$dados["velocidade"] = round($dados["download"] / pow(1024, $unidade_download), 1)." / ".
			round($dados["upload"] / pow(1024, $unidade_download), 1)." ".$lista_unidades[$unidade_download];
			if ($dados["ajustado"]) {
				$unidade = floor(log($dados["ajuste_download"], 1024));
				if ($unidade < 1) $unidade = 1;
				$velocidade = round($dados["ajuste_download"] / pow(1024, $unidade), 1)." / ".round($dados["ajuste_upload"] / pow(1024, $unidade), 1)." ".
				$lista_unidades[$unidade];
				$dados["velocidade"] .= "<br>($velocidade)";
			}
			// Trata apartamento
			if ($dados["complemento"]) $dados["apartamento"][] = $dados["complemento"];
			if ($dados["bloco"] && $dados["bloco"] != "Único") $dados["apartamento"][] = $dados["bloco"];
			$dados["apartamento"] = $dados["apartamento"] ? implode(" / ", $dados["apartamento"]) : "-";
			$dados["cont"] = count($autenticacoes) + 1;
			//$dados["situacao"] = $this->buscaStatusAutenticacao($dados["id"], true);
			$autenticacoes[] = $dados;

		}

		$cabecalho = array ("cont" => "#", "cliente_id" => "ID", "nome" => "Cliente", "apartamento" => "Apto", "usuario" => "Usuário / IP", "concentrador" => "Concentrador", "velocidade" => "Velocidade", "status" => "Status", "situacao" => "");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $autenticacoes, 0, false, array(0, 0, 0, 0, 0, 0, 0, 0, 16), null, array("right"));

		return $listagem->conteudo;
	
	}
	
	function buscaStatusAutenticacao($autenticacao_id, $icon = false) {
		
		$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao", $this->bancodedados);
		
		return $publicacao->verificarStatus($autenticacao_id, $icon);
		
	}

  function buscaUnidadesVelocidade() {
  	$unidades["1"] = "Kbps";
	  $unidades["2"] = "Mbps";
	  $unidades["3"] = "Gbps";
	  $unidades["4"] = "Tbps";
		return $unidades;
  }

  function buscaUnidades($unidade = "", $valor = null) {
    $unidades["1"] = "K$unidade";
    $unidades["2"] = "M$unidade";
    $unidades["3"] = "G$unidade";
    return $valor ? $unidades[$valor] : $unidades;
  }

  function buscaTamanho($tamanho) {
    $tamanhos["tamanho"] = $tamanho;
    while ($tamanhos["tamanho"] > 1023) {
      $tamanhos["tamanho"] = $tamanhos["tamanho"] / 1024;
      $tamanhos["tipo"] += 1;
    }
    if ($tamanhos["tamanho"] == '0') $tamanhos["tamanho"] = '';
    return $tamanhos;
  }

  function buscaCondominioResponsabilidades($condominio_id) {
    $condominios_responsabilidades = controle::instanciaObjeto("condominios_responsabilidades", $this->bancodedados, $this->permissoes, false);
    return $condominios_responsabilidades->geraListagemCondominiosResponsabilidades($condominios_responsabilidades->buscaCondominiosResponsabilidades($condominio_id));
  }

  function buscaCondominioDescontos($condominio_id) {
    $descontos_concessoes = controle::instanciaObjeto("descontos_concessoes", $this->bancodedados, $this->permissoes, false);
    return $descontos_concessoes->geraListagemDescontosCondominios($descontos_concessoes->buscaDescontosCondominios($condominio_id));
  }

  function buscaCondominioBlocos($condominio_id) {
    $condominios_blocos = controle::instanciaObjeto("condominios_blocos", $this->bancodedados, $this->permissoes, false);
    return $condominios_blocos->geraListagemCondominioBlocos($condominios_blocos->buscaCondominioBlocos($condominio_id));
  }

  function buscaCondominioContatos($condominio_id) {
    $condominios_contatos = controle::instanciaObjeto("condominios_contatos", $this->bancodedados, $this->permissoes, false);
    return $condominios_contatos->geraListagemCondominioContatos($condominios_contatos->buscaCondominioContatos($condominio_id));
  }

  function buscaAjustesFinanceiros($condominio_id) {
    $ajustes_financeiros = controle::instanciaObjeto("ajustes_financeiros", $this->bancodedados, $this->permissoes, false);
    return $ajustes_financeiros->geraListagemAjustesFinanceiros($ajustes_financeiros->buscaAjustesFinanceiros(null, $condominio_id));
  }

  function buscaChamados($condominio_id) {
    $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
    return $chamados->geraChamadosListagem($chamados->buscaChamados($condominio_id, "con_id"));
  }

  function regerarIPs($servidor_id) {
    $servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
    $servidores->regerarIPs($servidor_id);
  }

  function buscaCondominioServidores($condominio_id) {
    $query = "
      SELECT DISTINCT
        tab_servidores_interfaces.ser_id AS id
      FROM
        tab_condominios_blocos
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        con_id = $condominio_id
      ORDER BY
        cob_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados["id"];
    }
    return $servidores;
  }
  
  function buscaAtividades($condominio_id) {
    $condominios_atividades = controle::instanciaObjeto("condominios_atividades", $this->bancodedados, $this->permissoes, false);
    return $condominios_atividades->buscaCondominioAtividades($condominio_id);
  }
  
  function buscaTecnologias() {
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
      $tecnologias[$dados["id"]] = $dados["nome"];
    }
    return $tecnologias;
  }
 
  function buscaInstalacoesTipos() {
    $query = "
      SELECT
        cit_id AS id,
        cit_nome AS nome
      FROM
        tab_condominios_instalacoes_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }
  
  function buscaEquipamentos($tecnologia_id) {
    $query = "
      SELECT
        coq_id AS id,
        coq_nome AS nome
      FROM
        tab_condominios_tecnologias_equipamentos
      WHERE
        cog_id = $tecnologia_id
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $equipamentos[$dados["id"]] = $dados["nome"];
    }
    return $equipamentos;
  }
  
  function buscaCondominios($condominio_id = null) {
    $query = "
      SELECT
        con_id AS id,
        CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)".($condominio_id ? "
      WHERE
        tab_condominios.con_id = $condominio_id" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($condominio_id) {
        return $dados["nome"];
      }
      $condominios[$dados["id"]] = $dados["nome"];
    }
    return $condominios;
  }
  
  function buscaPops($condominio_id) {
    return $this->geraListagemPops($this->buscaPopsRecursivo($condominio_id));
  }
  
  function buscaPopsRecursivo($condominio_id) {
    $query = "
      SELECT
        tab_condominios.con_id AS id,
        CONCAT(tab_condominios.con_nome, '<br>(', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS nome,
        tab_condominios.con_pai AS condominio_pai,
  			tab_condominios.con_velocidade_maxima AS velocidade_maxima,
        tab_condominios_tecnologias.cog_nome AS tecnologia,
        tab_condominios_tecnologias_equipamentos.coq_nome AS equipamento
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        LEFT OUTER JOIN tab_condominios_tecnologias ON (tab_condominios.cog_id = tab_condominios_tecnologias.cog_id)
        LEFT OUTER JOIN tab_condominios_tecnologias_equipamentos ON (tab_condominios.coq_id = tab_condominios_tecnologias_equipamentos.coq_id)
      WHERE
        tab_condominios.con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $velocidade_maxima = $this->buscaTamanho($dados["velocidade_maxima"]);
      $dados["velocidade_maxima"] = $velocidade_maxima["tamanho"] ? $velocidade_maxima["tamanho"]." ".$this->buscaUnidades("bps", $velocidade_maxima["tipo"]) : "";
      $dados["links"] .= $this->geraLinks($dados["id"]);
      $condominios[] = $dados;
      if ($dados["condominio_pai"]) {
        $condominios = funcoes::mescla_arrays($condominios, $this->buscaPopsRecursivo($dados["condominio_pai"]));
      }
      return $condominios;
    }
  }

  function geraListagemPops($dados) {
    $cabecalho = array ("nome" => "Condomínio", "velocidade_maxima" => "Velocidade Máxima", "tecnologia" => "Tecnologia", "equipamento" => "Equipamento", "links" => "Links");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(230, 110, 110, 0, 80), null, array("center"));
    return $listagem->conteudo;
  }
  
  function buscaDadosBloco($bloco_id) {
  	$query = "
  		SELECT
  			tab_condominios_blocos.cob_id AS id,
  			tab_condominios_blocos.cob_nome AS nome,
  			tab_servidores_interfaces.sei_id AS	interface_id,
  			tab_servidores_interfaces.ser_id AS servidor_id
  		FROM
  			tab_condominios_blocos
  			LEFT OUTER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
  		WHERE
  			tab_condominios_blocos.cob_id = $bloco_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }
  
  function buscaDadosInterface($interface_id) {
  	$query = "
  		SELECT
  			sei_id AS	id,
  			ser_id AS servidor_id
  		FROM
  			tab_servidores_interfaces
  		WHERE
  			sei_id = $interface_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }
  
  ############################
  ### Ver Dados Cadastrais ###
  ############################

  function ver_dc() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.ver_dc.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[condominio_id]', $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[classe]', $this->dados_item["classe_nome"], $this->conteudo);
      $this->conteudo = str_replace('[cep]', $this->dados_item["cep"], $this->conteudo);
      $this->conteudo = str_replace('[estado]', $this->dados_item["estado"], $this->conteudo);
      $this->conteudo = str_replace('[cidade]', $this->dados_item["cidade"], $this->conteudo);
      $this->conteudo = str_replace('[bairro]', $this->dados_item["bairro"], $this->conteudo);
      $this->conteudo = str_replace('[logradouro]', $this->dados_item["logradouro"], $this->conteudo);
      $this->conteudo = str_replace('[numero]', $this->dados_item["numero"], $this->conteudo);
      $this->conteudo = str_replace('[comentarios]', nl2br($this->dados_item["comentarios"]), $this->conteudo);
      $this->conteudo = str_replace('[blocos]', $this->buscaCondominioBlocos($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[condominios_contatos]', $this->buscaCondominioContatos($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[data_cadastro]', $this->dados_item["data_cadastro"], $this->conteudo);
      $this->conteudo = str_replace('[data_ativacao]', $this->dados_item["data_ativacao"], $this->conteudo);
      $this->conteudo = str_replace('[situacao]', $this->dados_item["situacao_nome"], $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Adicionar Bloco
      if ($this->permissoes["condominios_blocos"]["adicionar"]) {
        $this->conteudo = str_replace("[blocos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="blocos_adicionar" id="blocos_adicionar" value="Adicionar" onClick="adicionarBloco('.$this->dados_item["id"].')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[blocos_adicionar]", '', $this->conteudo);
      }
      // Adicionar Contato
      if ($this->permissoes["condominios_contatos"]["adicionar"]) {
        $this->conteudo = str_replace("[contatos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="contatos_adicionar" id="contatos_adicionar" value="Adicionar" onClick="adicionarContato('.$this->dados_item["id"].')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[contatos_adicionar]", '', $this->conteudo);
      }
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dc"])
      $botoes[] = '<input type="button" name="editar_dc" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_dc&i='.$this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Registra Atividade
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  ###############################
  ### Editar Dados Cadastrais ###
  ###############################

  function editar_dc() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios.editar_dc.inc.php");
        // Ações
	      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
        // Dados
        $atualizavel = $this->dados_item["status_id"] != 2 || $this->permissoes["condominios"]["atualizar_dados_cadastrais"];
        $alertas = $this->buscaAlertasCondominio($this->dados_item["id"], $this->dados_item["cep"], $this->dados_item["numero"]);
        $this->conteudo = str_replace('[alertas]', $alertas ? $alertas["mensagem_html"] : "", $this->conteudo);
        $this->conteudo = str_replace('[condominio_id]', $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace('[nome]', $this->geraInput("text", "nome", "100%", $this->dados_item["nome"], $atualizavel, "80"), $this->conteudo);
        $this->conteudo = str_replace('[tipos]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true, "tipo", "250px", null, $atualizavel), $this->conteudo);
        $this->conteudo = str_replace('[classe]', $this->geraSelect($this->buscaClasses(), $this->dados_item["classe_id"], true, "classe", "250px"), $this->conteudo);
        $this->conteudo = str_replace('[cep]', $this->geraInput("text", "cep", "250px", $this->dados_item["cep"], $atualizavel, "9", "return formataCEP(this, event)"), $this->conteudo);
        $this->conteudo = str_replace('[estado]', $this->dados_item["estado"], $this->conteudo);
        $this->conteudo = str_replace('[cidade]', $this->dados_item["cidade"], $this->conteudo);
        $this->conteudo = str_replace('[bairro]', $this->dados_item["bairro"], $this->conteudo);
        $this->conteudo = str_replace('[logradouro]', $this->dados_item["logradouro"], $this->conteudo);
        $this->conteudo = str_replace('[numero]', $this->geraInput("text", "numero", "400px", $this->dados_item["numero"], $atualizavel, "10", null, null, "mudaCondominioNumero(this)"), $this->conteudo);
        $this->conteudo = str_replace('[comentarios]', $this->dados_item["comentarios"], $this->conteudo);
        $this->conteudo = str_replace('[blocos]', $this->buscaCondominioBlocos($this->dados_item["id"]), $this->conteudo);
        $this->conteudo = str_replace('[condominios_contatos]', $this->buscaCondominioContatos($this->dados_item["id"]), $this->conteudo);
        $this->conteudo = str_replace('[data_cadastro]', $this->dados_item["data_cadastro"], $this->conteudo);
        $this->conteudo = str_replace('[data_ativacao]', $this->dados_item["data_ativacao"], $this->conteudo);
        $this->conteudo = str_replace('[situacao]', $this->dados_item["situacao_nome"], $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		if ($this->permissoes["ceps"]["pesquisar"]) {
			$this->conteudo = str_replace("[pesquisar_cep]", "<input type=\"button\" class=\"buttonAzulEscuroBranco11\" name=\"pesquisar_cep\" id=\"pesquisar_cep\" value=\"Pesquisar\" onClick=\"pesquisarCEP()\" />", $this->conteudo);
		} else {
			$this->conteudo = str_replace("[pesquisar_cep]", "", $this->conteudo);
		}


        // Adicionar Bloco
        if ($this->permissoes["condominios_blocos"]["adicionar"]) {
          $this->conteudo = str_replace("[blocos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="blocos_adicionar" id="blocos_adicionar" value="Adicionar" onClick="adicionarBloco('.$this->dados_item["id"].')">', $this->conteudo);
        } else {
          $this->conteudo = str_replace("[blocos_adicionar]", '', $this->conteudo);
        }
        // Adicionar Contato
        if ($this->permissoes["condominios_contatos"]["adicionar"]) {
          $this->conteudo = str_replace("[contatos_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="contatos_adicionar" id="contatos_adicionar" value="Adicionar" onClick="adicionarContato('.$this->dados_item["id"].')">', $this->conteudo);
        } else {
          $this->conteudo = str_replace("[contatos_adicionar]", '', $this->conteudo);
        }
        // Action
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_dc&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        // Trata Dados
        if ($this->dados_item["status_id"] != 2 || $this->permissoes["condominios"]["atualizar_dados_cadastrais"]) {
          $_POST["nome"] = $this->corrigeNome($_POST["nome"]);
          $_POST["numero"] = $_POST["numero"] ? "'{$_POST["numero"]}'" : "NULL";
          $_POST["cep"] = preg_replace("/[^0-9]/", "", $_POST["cep"]);
          $_POST["situacao"] = $_POST["situacao"] ? $_POST["situacao"] : $this->dados_item["situacao_id"];
          $_POST["tipo"] = $_POST["tipo"] ? $_POST["tipo"] : 3;
        } else {
          $_POST["nome"] = $this->dados_item["nome"];
          $_POST["numero"] = $this->dados_item["numero"] ? $this->dados_item["numero"] : "NULL";
          $_POST["cep"] = $this->dados_item["cep"] ? preg_replace("/[^0-9]/", "", $this->dados_item["cep"]) : "NULL";
          $_POST["situacao"] = $this->dados_item["situacao_id"];
          $_POST["tipo"] = $this->dados_item["tipo_id"];
        }
        $_POST["comentarios"] = trim($_POST["comentarios"]);
        $_POST["classe"] = $_POST["classe"] ? $_POST["classe"] : "NULL";
        // Atualiza Regisro
        $query = "UPDATE tab_condominios SET con_nome = '{$_POST["nome"]}', cep_id = '{$_POST["cep"]}', con_numero = {$_POST["numero"]}, con_comentarios = '{$_POST["comentarios"]}', cnt_id = {$_POST["tipo"]}, ccl_id = {$_POST["classe"]} WHERE con_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        // Redireciona
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ############################
  ### Ver Dados Comerciais ###
  ############################

  function ver_dm() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.ver_dm.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[responsabilidades]', $this->buscaCondominioResponsabilidades($_GET["i"]), $this->conteudo);
      $this->conteudo = str_replace('[descontos_condominio]', $this->buscaCondominioDescontos($_GET["i"]), $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace('[mensagem]', $_GET["q"] ? "Foi(ram) encontrado(s) {$_GET["q"]} mailing(s)!" : ($_GET["e"] ? "Nenhum mailing encontrado!" : ""), $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dm"]) $botoes[] = '<input type="button" name="editar_dm" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_dm&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["capturar_mailing"] && ($this->dados_item["responsavel_id"] == $_SESSION["usuario"])) $botoes[] = '<input type="button" name="capturar_mailing" class="buttonAzulEscuroBranco11" value="Capturar Mailing" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=capturar_mailing&i='.$this->dados_item["id"].'\'">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Parametros da Função buscaDescontosCondominio
      $this->conteudo = str_replace('[descontos_parametros]', $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace("[descontos_adicionar]", ($this->permissoes["descontos_concessoes"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="descontos_adicionar" value="Adicionar" onClick="adicionarDescontoConcessaoCondominio('.$this->dados_item["id"].')">' : '', $this->conteudo);
      $this->conteudo = str_replace("[responsabilidades_adicionar]", ($this->permissoes["condominios_responsabilidades"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="responsabilidades_adicionar" value="Adicionar" onClick="adicionarCondominioResponsabilidade('.$this->dados_item["id"].')">' : '', $this->conteudo);
      $this->conteudo = str_replace("[atividades_adicionar]", ($this->permissoes["condominios_atividades"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="atividades_adicionar" value="Adicionar" onClick="adicionarAtividade('.$this->dados_item["id"].')">' : '', $this->conteudo);
      //$this->conteudo = str_replace("[atividades]", $this->buscaAtividades($this->dados_item["id"]), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  ###############################
  ### Editar Dados Comerciais ###
  ###############################

  function editar_dm() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios.editar_dm.inc.php");
	      // Ações
	      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
	      // Dados
				$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
        $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
        $this->conteudo = str_replace('[responsabilidades]', $this->buscaCondominioResponsabilidades($_GET["i"]), $this->conteudo);
        $this->conteudo = str_replace('[descontos_condominio]', $this->buscaCondominioDescontos($_GET["i"]), $this->conteudo);
        $this->conteudo = str_replace('[planos]', $this->geraListagemPermissoesPlanos($this->buscaPlanosPermissoes($_GET["i"], $this->permissoes["condominios"]["editar_planos_permissoes"])), $this->conteudo);
        $this->conteudo = str_replace('[comentarios]', $this->dados_item["comentarios_comercial"], $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        $this->conteudo = str_replace('[mensagem]', $_GET["q"] ? "Foi(ram) encontrado(s) {$_GET["q"]} mailing(s)!" : ($_GET["e"] ? "Nenhum mailing encontrado!" : ""), $this->conteudo);
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        if (1 || $this->permissoes[$this->dados_modulo["nome"]]["capturar_mailing"] && ($this->dados_item["responsavel_id"] == $_SESSION["usuario"])) $botoes[] = '<input type="button" name="capturar_mailing" class="buttonAzulEscuroBranco11" value="Capturar Mailing" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=capturar_mailing&i='.$this->dados_item["id"].'\'">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_dm&i={$this->dados_item["id"]}", $this->conteudo);
        // Parametros da Função buscaDescontosCondominio
        $descontos_parametros[] = $this->dados_item["id"];
        ($this->permissoes["descontos_concessoes"]["ver"]) ?	$descontos_parametros[] = "1" : $descontos_parametros[] = "0";
        $this->conteudo = str_replace('[descontos_parametros]', implode(",", $descontos_parametros), $this->conteudo);
        $this->conteudo = str_replace("[descontos_adicionar]", ($this->permissoes["descontos_concessoes"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="descontos_adicionar" value="Adicionar" onClick="adicionarDescontoConcessaoCondominio('.$this->dados_item["id"].')">' : '', $this->conteudo);
        $this->conteudo = str_replace("[atividades_adicionar]", ($this->permissoes["condominios_atividades"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="atividades_adicionar" value="Adicionar" onClick="adicionarAtividade('.$this->dados_item["id"].')">' : '', $this->conteudo);
        //$this->conteudo = str_replace("[atividades]", $this->buscaAtividades($this->dados_item["id"]), $this->conteudo);
        $this->conteudo = str_replace("[responsabilidades_adicionar]", ($this->permissoes["condominios_responsabilidades"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="responsabilidades_adicionar" value="Adicionar" onClick="adicionarCondominioResponsabilidade('.$this->dados_item["id"].')">' : '', $this->conteudo);
      } else {
        $_POST["comentarios"] = trim($_POST["comentarios"]);
        $query = "UPDATE tab_condominios SET con_comentarios_comercial = '{$_POST["comentarios"]}' WHERE con_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        if ($this->permissoes["condominios"]["editar_planos_permissoes"]) {
          $query = "DELETE FROM rel_con_pla_tempermissao WHERE con_id = {$_GET["i"]}"; 
          $this->bancodedados->query($query);
          foreach ($_POST["permissao_plano"] as $plano_id => $permissao) {
            if ($permissao != 0) {
              $query = "INSERT INTO rel_con_pla_tempermissao (con_id, pla_id, rcp_permissao) VALUES ({$_GET["i"]}, $plano_id, $permissao)"; 
              $this->bancodedados->query($query); 
            }
          }
        }
        // Registra Atividade
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"],$this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dm&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  #############################
  ### Ver Dados Financeiros ###
  #############################

  function ver_df() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.ver_df.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[ajustes_financeiros_condominio]', $this->buscaAjustesFinanceiros($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      if ($this->permissoes["condominios"]["capturar_mailing"] && $this->dados_item["responsavel_id"] == $_SESSION["usuario"]) $botoes[] = '<input type="button" name="capturar_mailing" class="buttonAzulEscuroBranco11" value="Capturar Mailing" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=capturar_mailing&i='.$this->dados_item["id"].'\'">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Parametros da Função buscaAjustesCondominio
      $ajustes_parametros[] = $this->dados_item["id"];
      $ajustes_parametros[] = ($this->permissoes["ajustes_financeiros"]["ver"]) ?	"1" : "0";
      $this->conteudo = str_replace('[ajustes_parametros]', implode(",", $ajustes_parametros), $this->conteudo);
      // Registra Atividade
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  ################################
  ### Editar Dados Financeiros ###
  ################################

  function editar_df() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.editar_df.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[ajustes_financeiros_condominio]', $this->buscaAjustesFinanceiros($this->dados_item["id"]), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      // Action
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_dm&i={$this->dados_item["id"]}", $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Parametros da Função buscaAjustesCondominio
      $ajustes_parametros[] = $this->dados_item["id"];
      $ajustes_parametros[] = ($this->permissoes["ajustes_financeiros"]["ver"]) ?	"1" : "0";
      $this->conteudo = str_replace('[ajustes_parametros]', implode(",", $ajustes_parametros), $this->conteudo);
      // Botão Adicionar Ajuste
      if ($this->permissoes["ajustes_financeiros"]["adicionar"]) {
        $this->conteudo = str_replace("[ajustes_adicionar]", '<input type="button" class="buttonAzulEscuroBranco11" name="ajustes_adicionar" value="Adicionar" onClick="adicionarAjusteCondominio('.$this->dados_item["id"].')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace("[ajustes_adicionar]", '', $this->conteudo);
      }
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  #############################
  ### Editar Dados Técnicos ###
  #############################

  function ver_dt() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.ver_dt.inc.php");
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Dados
      $this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[interfaces]', $this->buscaBlocos($_GET['i'], false), $this->conteudo);
      $this->conteudo = str_replace('[instalacao_tipo]', $this->dados_item["instalacao_tipo_nome"], $this->conteudo);
      $velocidade_maxima = $this->buscaTamanho($this->dados_item["velocidade_maxima"]);
      $this->conteudo = str_replace('[velocidade_maxima]', $velocidade_maxima["tamanho"] ? $velocidade_maxima["tamanho"]." ".$this->buscaUnidades("bps", $velocidade_maxima["tipo"]) : "", $this->conteudo);
      $this->conteudo = str_replace('[tecnologia]', $this->dados_item["tecnologia_nome"], $this->conteudo);
      $this->conteudo = str_replace('[equipamento]', $this->dados_item["equipamento_nome"], $this->conteudo);
      $this->conteudo = str_replace('[condominio_pop]', $this->buscaCondominios($this->dados_item["condominio_pai"]), $this->conteudo);
      $this->conteudo = str_replace('[pops]', $this->buscaPops($this->dados_item["id"]), $this->conteudo);
      $this->conteudo = str_replace('[condominios_pontos]', $this->buscaPontos($_GET['i']), $this->conteudo);
      $this->conteudo = str_replace('[condominios_autenticacoes]', $this->buscaAutenticacoes($_GET['i']), $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"]) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["regerar_ips"]) $botoes[] = '<input type="button" name="regerar_ips" class="buttonAzulEscuroBranco11" value="Regerar IP\'s" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar_ips&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  #############################
  ### Editar Dados Técnicos ###
  #############################

  function editar_dt() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios.editar_dt.inc.php");
        // Ações
	      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
        // Dados
        $this->conteudo = str_replace('[id]', $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
        $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
        $this->conteudo = str_replace('[instalacao_tipo]', $this->geraSelect($this->buscaInstalacoesTipos(), $this->dados_item["instalacao_tipo_id"], false, "instalacao_tipo", "155px"), $this->conteudo);
        $velocidade_maxima = $this->buscaTamanho($this->dados_item["velocidade_maxima"]);
        $this->conteudo = str_replace('[velocidade_maxima]', $this->geraInput("text", "velocidade_maxima", "80px", $velocidade_maxima["tamanho"], $this->permissoes["condominios"]["editar_velocidade_maxima"], null, "return formataNumero(this, event)"), $this->conteudo);
        $this->conteudo = str_replace('[velocidade_maxima_unidade]', $this->geraSelect($this->buscaUnidades("bps"), $velocidade_maxima["tipo"], false, "velocidade_maxima_unidade", "70px", null, $this->permissoes["condominios"]["editar_velocidade_maxima"]), $this->conteudo);
        $this->conteudo = str_replace('[tecnologia]', $this->geraSelect($this->buscaTecnologias(), $this->dados_item["tecnologia_id"], true, "tecnologia", "155px", "mudaTecnologia(this.value)"), $this->conteudo);
        $this->conteudo = str_replace('[equipamento]', $this->geraSelect($this->dados_item["tecnologia_id"] ? $this->buscaEquipamentos($this->dados_item["tecnologia_id"]) : null, $this->dados_item["equipamento_id"], true, "equipamento", "240px", null, $this->dados_item["tecnologia_id"]), $this->conteudo);
        $this->conteudo = str_replace('[condominio_pop]', $this->geraSelect($this->buscaCondominios(), $this->dados_item["condominio_pai"], true, "condominio_pop", "400px"), $this->conteudo);
        $this->conteudo = str_replace('[pops]', $this->buscaPops($this->dados_item["id"]), $this->conteudo);
        $this->conteudo = str_replace('[interfaces]', $this->buscaBlocos($_GET['i'], true, $this->dados_item["instalacao_tipo_id"] == 1), $this->conteudo);
        $this->conteudo = str_replace('[condominios_pontos]', $this->buscaPontos($_GET['i']), $this->conteudo);
        $this->conteudo = str_replace('[condominios_autenticacoes]', $this->buscaAutenticacoes($_GET['i']), $this->conteudo);
        $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        // Botões
        if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"]) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&i='.$this->dados_item["id"].'\';">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["regerar_ips"]) $botoes[] = '<input type="button" name="regerar_ips" class="buttonAzulEscuroBranco11" value="Regerar IP\'s" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar_ips&i='.$this->dados_item["id"].'\';">';
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
        // Action
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar_dt&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        // Atualiza Dados
        if ($this->permissoes["condominios"]["editar_velocidade_maxima"]) {
        	$_POST["velocidade_maxima"] = $_POST["velocidade_maxima"] * pow(1024, $_POST["velocidade_maxima_unidade"]);
        } else {
        	$_POST["velocidade_maxima"] = $this->dados_item["velocidade_maxima"];
        }
        if ($this->dados_item["instalacao_tipo_id"] != $_POST["instalacao_tipo"] || $this->dados_item["velocidade_maxima"] != $_POST["velocidade_maxima"] || $this->dados_item["tecnologia_id"] != $_POST["tecnologia"] || $this->dados_item["equipamento_id"] != $_POST["equipamento"] || $this->dados_item["condominio_pai"] != $_POST["condominio_pop"]) {
          $_POST["tecnologia"] = $_POST["tecnologia"] ? $_POST["tecnologia"] : "NULL";
          $_POST["equipamento"] = $_POST["equipamento"] ? $_POST["equipamento"] : "NULL";
          $_POST["condominio_pop"] = $_POST["condominio_pop"] ? $_POST["condominio_pop"] : "NULL";
          $query = "UPDATE tab_condominios SET cit_id = {$_POST["instalacao_tipo"]}, con_velocidade_maxima = {$_POST["velocidade_maxima"]}, cog_id = {$_POST["tecnologia"]}, coq_id = {$_POST["equipamento"]}, con_pai = {$_POST["condominio_pop"]} WHERE con_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
        }
        // Atualiza Interfaces
        if ($_POST["instalacao_tipo"] == 1) {
          $this->bancodedados->query("UPDATE tab_condominios_blocos SET sei_id = NULL WHERE con_id = {$this->dados_item["id"]}");
        } else {
	        foreach ($_POST["interface"] as $bloco_id => $interface_id) {
	        	$dados_bloco = $this->buscaDadosBloco($bloco_id);
	        	
	        	// Atuaiza Bloco
        		$_POST["interface"][$bloco_id] = trim($_POST["interface"][$bloco_id]);
        		$_POST["interface"][$bloco_id] = $_POST["interface"][$bloco_id] ? $_POST["interface"][$bloco_id] : "NULL";
        		$_POST["equipamento_ip"][$bloco_id] = trim($_POST["equipamento_ip"][$bloco_id]);
        		$_POST["equipamento_ip"][$bloco_id] = preg_match("/^[0-9]{1,3}(\\.[0-9]{1,3}){3}$/", $_POST["equipamento_ip"][$bloco_id]) ? "'{$_POST["equipamento_ip"][$bloco_id]}'" : "NULL";
        		$_POST["ap_ip"][$bloco_id] = trim($_POST["ap_ip"][$bloco_id]);
        		$_POST["ap_ip"][$bloco_id] = preg_match("/^[0-9]{1,3}(\\.[0-9]{1,3}){3}$/", $_POST["ap_ip"][$bloco_id]) ? "'{$_POST["ap_ip"][$bloco_id]}'" : "NULL";

          	$this->bancodedados->query("UPDATE tab_condominios_blocos SET sei_id = {$_POST["interface"][$bloco_id]}, cob_equipamento_ip = {$_POST["equipamento_ip"][$bloco_id]}, cob_ap_ip = {$_POST["ap_ip"][$bloco_id]} WHERE cob_id = $bloco_id");
	        	
	        	if ($interface_id) {
	        		$dados_interface = $this->buscaDadosInterface($interface_id);
		        	if ($dados_bloco["interface_id"] != $interface_id) {
								// Instancia ClientesPontos
								$clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);
								// Busca pontos do cliente
								$query = "
		              SELECT
		                tab_clientes_pontos.clp_id AS id,
		                tab_clientes_pontos.clp_regra AS regra,
		                tab_servidores_interfaces.sei_id AS interface_id,
		                tab_servidores_interfaces.ser_id AS servidor_id,
		                tab_assinaturas.ass_id AS assinatura_id
		              FROM
		              	tab_clientes
		                INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
		                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		                INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
		                LEFT OUTER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
		              WHERE
		          			tab_clientes.cob_id = $bloco_id AND
		                tab_clientes_pontos.cps_id IN (1, 2) AND
		          			tab_planos.pls_id IN (1, 32, 35)";
								$query = $this->bancodedados->query($query);
								while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
									// atualiza interface/ip do ponto
									$regra = $dados["servidor_id"] == $dados_interface["servidor_id"] ? $dados["regra"] : $clientes_pontos->buscaProximaRegra($dados_interface["servidor_id"]);
									$ip = $clientes_pontos->buscaIPLivre($dados_interface["id"], $dados["assinatura_id"], $dados["id"]);
									$ip = $ip ? "'$ip'" : "NULL";
									$query_ponto = "UPDATE tab_clientes_pontos SET clp_regra = $regra, sei_id = {$dados_interface["id"]}, clp_ip = $ip, clp_publicado = 0 WHERE clp_id = {$dados["id"]}";
									$this->bancodedados->query($query_ponto);
								}
		          	// Autenticacoes
		          	$this->bancodedados->query("
		          		UPDATE
		          			tab_clientes_autenticacoes
		          			INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
		          			INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
		                INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
	          			SET
		          			tab_clientes_autenticacoes.sei_id = {$dados_interface["id"]},
		          			tab_clientes_autenticacoes.ser_id = {$dados_interface["servidor_id"]},
										tab_clientes_autenticacoes.cau_publicado = 0
		          		WHERE
		          			tab_clientes.cob_id = $bloco_id AND
		          			tab_clientes_autenticacoes.cas_id IN (1, 2) AND
		          			tab_planos.pls_id IN (1, 32, 35)");
		        	}
	        	} else {
	          	// Autenticações
	          	$this->bancodedados->query("
	          		UPDATE
	          			tab_clientes_autenticacoes
	          			INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
	          			INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
	          			INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
	          		SET
	          			tab_clientes_autenticacoes.sei_id = NULL,
	          			tab_clientes_autenticacoes.ser_id = NULL,
	          			tab_clientes_autenticacoes.cau_publicado = 0
	          		WHERE
	          			tab_clientes.cob_id = $bloco_id AND
	          			tab_clientes_autenticacoes.cas_id IN (1, 2) AND
		          		tab_planos.pls_id IN (1, 32, 35)");
		          // Pontos
		          $this->bancodedados->query("
		          	UPDATE
		          		tab_clientes_pontos
		          		INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
		          		INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
		          		INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		          	SET
		          		tab_clientes_pontos.sei_id = NULL,
		          		tab_clientes_pontos.clp_publicado = 0
		          	WHERE
		          		tab_clientes.cob_id = $bloco_id AND
		          		tab_clientes_pontos.cps_id IN (1, 2) AND
		          		tab_planos.pls_id IN (1, 32, 35)");
	          	// Servidor
							if ($dados_bloco["servidor_id"]) {
	          		$this->bancodedados->query("UPDATE tab_servidores SET ser_publicado = 0 WHERE ser_id = {$dados_bloco["servidor_id"]}");
          		}
        		}
  		    }
  		  }
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dt&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ####################
  ### Ver Chamados ###
  ####################

  function ver_ch() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.ver_ch.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace('[chamados]', $this->buscaChamados($_GET["i"]), $this->conteudo);
      $this->conteudo = str_replace('[chamados_adicionar]', $this->permissoes["chamados"]["adicionar"] ? '<input type="button" name="chamados_adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarChamado(\''.$this->dados_item["id"].'\', \'condominio_id\');">' : '', $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dm"]) $botoes[] = '<input type="button" name="editar_dm" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_dm&i='.$this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  #######################
  ### Editar Chamados ###
  #######################

  function editar_ch() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("condominios.ver_ch.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[endereco]', $this->dados_item["endereco"], $this->conteudo);
      $this->conteudo = str_replace("[chamados]", $this->buscaChamados($_GET["i"]), $this->conteudo);
      $this->conteudo = str_replace("[chamados_adicionar]", $this->permissoes["chamados"]["adicionar"] ? '<input type="button" name="chamados_adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarChamado(\''.$this->dados_item["id"].'\', \'condominio_id\');">' : '', $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_df" => "Dados Financeiros", "ver_dm" => "Dados Comerciais", "ver_dt" => "Dados Técnicos", "ver_ch" => "Chamados")), $this->conteudo);
      // Botões
      if ($this->permissoes[$this->dados_modulo["nome"]]["editar_dm"]) $botoes[] = '<input type="button" name="editar_dm" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar_dm&i='.$this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["ativar"] && $this->dados_item["status_id"] != 2) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["cancelar"] && $this->dados_item["status_id"] == 2) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  ####################
  ### Regerar IP's ###
  ####################

  function regerar_ips() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios.regerar_ips.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=regerar_ips&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
      	$servidores = $this->buscaCondominioServidores($_GET["i"]);
  		  if ($servidores) {
  		    foreach ($servidores as $servidor_id) {
  		      $this->regerarIPs($servidor_id);
  		    }
  		  }
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dt&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
    }
  }

  ##############
  ### Ativar ###
  ##############

  function ativar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$this->condominioDescontosPendentes($this->dados_item["id"])) {
        if (!$_POST) {
        	$blocos=$this->buscaBlocosCondominio($this->dados_item['id']);
        	$blocos_selecionados=array_keys($blocos);
          $this->conteudo .= $this->carregaArquivo("condominios.ativar.confirmacao.inc.php");
          $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
	        $velocidade_maxima = $this->buscaTamanho($this->dados_item["velocidade_maxima"] ? $this->dados_item["velocidade_maxima"] : 10485760);
	        $this->conteudo = str_replace('[velocidade_maxima]', $this->geraInput("text", "velocidade_maxima", "80px", $velocidade_maxima["tamanho"], $this->permissoes["condominios"]["editar_velocidade_maxima"], null, "return formataNumero(this, event)"), $this->conteudo);
	        $this->conteudo = str_replace('[velocidade_maxima_unidade]', $this->geraSelect($this->buscaUnidades("bps"), $velocidade_maxima["tipo"], false, "velocidade_maxima_unidade", "70px", null, $this->permissoes["condominios"]["editar_velocidade_maxima"]), $this->conteudo);
          $this->conteudo = str_replace('[blocos]', $this->geraCheckBoxes('blocos', $blocos, $blocos_selecionados), $this->conteudo);
          $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
          $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
          $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
          $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=ativar&i={$this->dados_item["id"]}", $this->conteudo);
        } else {
        	//Ativar Blocos
        	$query = "UPDATE tab_condominios_blocos SET cbs_id = 2 WHERE con_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          foreach ($_POST["blocos"] as $bloco_id){
          	$query = "UPDATE tab_condominios_blocos SET cbs_id = 1 WHERE cob_id = $bloco_id";
          $this->bancodedados->query($query);
          }
          if ($this->permissoes["condominios"]["editar_velocidade_maxima"]) {
        		$_POST["velocidade_maxima"] = $_POST["velocidade_maxima"] * pow(1024, $_POST["velocidade_maxima_unidade"]);
        	} else {
	        	$_POST["velocidade_maxima"] = $this->dados_item["velocidade_maxima"] ? $this->dados_item["velocidade_maxima"] : 10485760;
        	}
        	//Ativar Condominios
          $query = "UPDATE tab_condominios SET con_data_ativacao = NOW(), con_velocidade_maxima = {$_POST["velocidade_maxima"]}, cos_id = 2 WHERE con_id = {$_GET["i"]}";
          $this->bancodedados->query($query);
          $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
          $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
        }
      } else {
        // Descontos Pendentes
        $this->conteudo .= $this->carregaArquivo("condominios.ativar.descontos_pendentes.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
    }
  }

  ##############
  ### Ativar ###
  ##############

  function cancelar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios.cancelar.confirmacao.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=cancelar&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
      	// Desativa Blocos
        $query = "UPDATE tab_condominios_blocos SET cbs_id = 2 WHERE con_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
      	// Cancela Condomínio
        $query = "UPDATE tab_condominios SET cos_id = 3 WHERE con_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
    }
  }

  function condominioDescontosPendentes($condominio_id) {
    $query = "
      SELECT
        dec_id AS id
      FROM
        tab_descontos_concessoes
      WHERE
        dcs_id = 1 AND
        con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->linhas($query)) ? true : false;
  }

  function contaItemBlocos($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cob_id) AS contagem
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function contaItemApartamentos($id) {
    $query = "
      SELECT
        SUM(cob_quantidade) AS contagem
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query,0,"contagem"));
  }

  function contaItemClientesMailing($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        tab_clientes.cls_id = 4 AND
        tab_clientes.con_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function contaItemClientesProspects($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        tab_clientes.cls_id = 1 AND
        tab_clientes.con_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function contaItemClientesAtivos($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        tab_clientes.cls_id = 2 AND
        tab_clientes.con_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function contaItemClientesCancelados($id) {
    $query = "
      SELECT
        COUNT(DISTINCT cli_id) AS contagem
      FROM
        tab_clientes
      WHERE
        tab_clientes.cls_id = 3 AND
       tab_clientes.con_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function contaItemPontosAtivos($id) {
    $query = "
    	SELECT
    		COUNT(DISTINCT tab_clientes_pontos.clp_id) AS contagem
    	FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
    		INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
    	WHERE
    		tab_clientes.con_id = $id AND
    		tab_clientes_pontos.cps_id = 1";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function contaItemPontosCancelados($id) {
    $query = "
    	SELECT
    		COUNT(DISTINCT tab_clientes_pontos.clp_id) AS contagem
    	FROM
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
    		INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
    	WHERE
    		tab_clientes.con_id = $id AND
    		tab_clientes_pontos.cps_id = 3";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "contagem"));
  }

  function buscaItemMensalidade($condominio) {
    $mensalidade = $this->calculaItemMensalidadeComDescontos($condominio, $this->buscaMensalidadeBase(91), "1");
    return $this->formataDinheiro($mensalidade);
  }

  function buscaMensalidadeBase($plano_id) {
    $query = "
    	SELECT
    		pla_mensalidade AS mensalidade
    	FROM
    		tab_planos
    	WHERE
    		pla_id = $plano_id
    	ORDER BY
    		pla_mensalidade DESC
    	LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, "mensalidade");
  }

  function calculaItemMensalidadeComDescontos($condominio, $mensalidade, $servico) {
    $query = "
			SELECT
			  tab_descontos_concessoes.des_id AS tipo,
			  tab_descontos_concessoes.dec_mensalidade AS mensalidade
			FROM
			  tab_descontos_concessoes
			  INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
			  (tab_descontos_concessoes.con_id = '$condominio') AND
			  (tab_descontos_concessoes.dcs_id = '5') AND
			  ((tab_descontos.pls_id IS NULL) OR (tab_descontos.pls_id = '$servico'))";
    $query = $this->bancodedados->query($query);
    for ($i=0;$i<$this->bancodedados->linhas($query);$i++) {
      $desconto_atual = $this->bancodedados->resultado($query,$i,"mensalidade");
      if (!$desconto_atual && $this->bancodedados->resultado($query,$i,"tipo") == 26) $desconto_atual = "1390";
      if ($desconto_atual <= 1) {
        $desconto_atual = $desconto_atual * $mensalidade;
      }
      $desconto_total += $desconto_atual;
    }
    $mensalidade = ($mensalidade >= $desconto_total) ? $mensalidade - $desconto_total : 0;
    return intval($mensalidade);
  }

  function buscaEndereco($cep, $numero) {
    $query = "
      SELECT
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro
      FROM
        tab_ceps
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      WHERE
        tab_ceps.cep_id = $cep";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return "{$dados["logradouro"]}, $numero, {$dados["bairro"]}, {$dados["cidade"]}";
    }
  }

  function buscaContato($id) {
    $query = "
      SELECT
        IF(LOCATE(' ', coc_nome) > 1, LEFT(coc_nome, LOCATE(' ', coc_nome) - 1), coc_nome) AS nome,
        coc_tel_residencial AS residencial,
        coc_tel_comercial AS comercial,
        coc_tel_celular AS celular
      FROM
        tab_condominios_contatos
      WHERE
        con_id = $id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["residencial"] || $dados["comercial"] || $dados["celular"]) {
        $contatos[] = $dados["nome"];
        if ($dados["residencial"]) $contatos[] = $dados["residencial"];
        if ($dados["comercial"]) $contatos[] = $dados["comercial"];
        if ($dados["celular"]) $contatos[] = $dados["celular"];
      }
    }
    return $contatos ? "<div style=\"width:80px\">".implode("<br>", $contatos)."</div>" : "";
  }

  ##############
  ### Buscar ###
  ##############

  function buscar() {
    if ($_POST) {
      $id = md5(uniqid(rand(), true));
      $_SESSION["POST"][$id] = $_POST;
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar&i=$id");
      exit;
    } elseif ($_GET["i"] && $_SESSION["POST"][$_GET["i"]]) {
      $_POST = $_SESSION["POST"][$_GET["i"]];
    }
    if ($_POST) {
	    $_POST["cidade1"] = $_POST["cidade2"] ? $this->buscaCidade($_POST["cidade2"]) : $_POST["cidade1"];
	    $_POST["bairro1"] = $_POST["bairro2"] ? $this->buscaBairro($_POST["bairro2"]) : $_POST["bairro1"];
    }
    $this->conteudo .= $this->carregaArquivo("condominios.buscar.inc.php");
    $this->conteudo = str_replace('[nome]', $_POST["nome"], $this->conteudo);
    $this->conteudo = str_replace('[cidade1]', $this->geraInput("text", "cidade1", "298px", $_POST["cidade1"], !$_POST["cidade2"], "80"), $this->conteudo);
    $this->conteudo = str_replace('[cidade2]', $this->geraSelect($this->buscaCidades(), $_POST["cidade2"], true, "cidade2", "300px", "atualizaBairros(this.value)"), $this->conteudo);
    $this->conteudo = str_replace('[bairro1]', $this->geraInput("text", "bairro1", "298px", $_POST["bairro1"], !$_POST["bairro1"], "80"), $this->conteudo);
    $this->conteudo = str_replace('[bairro2]', $this->geraSelect($this->buscaBairros($_POST["cidade2"]), $_POST["bairro2"], true, "bairro2", "300px", "atualizaBairrosCampo(this.value)", $_POST["cidade2"] || $_POST["bairro2"]), $this->conteudo);
    $this->conteudo = str_replace('[logradouro]', $_POST["logradouro"], $this->conteudo);
    $this->conteudo = str_replace('[cep]', $_POST["cep"], $this->conteudo);
    $this->conteudo = str_replace('[numero]', $_POST["numero"], $this->conteudo);
    $this->conteudo = str_replace('[data_cadastro_inicio]', $_POST["data_cadastro_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[data_cadastro_termino]', $_POST["data_cadastro_termino"], $this->conteudo);
    $this->conteudo = str_replace('[data_ativacao_inicio]', $_POST["data_ativacao_inicio"], $this->conteudo);
    $this->conteudo = str_replace('[data_ativacao_termino]', $_POST["data_ativacao_termino"], $this->conteudo);
    $this->conteudo = str_replace('[responsavel]', $this->geraSelect($this->buscaResponsaveis(), $_POST["responsavel"], true, "responsavel", "300px"), $this->conteudo);
    $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $_POST["tipo"], true, "tipo", "150px"), $this->conteudo);
    $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $_POST["status"], true, "status", "150px"), $this->conteudo);
    $this->conteudo = str_replace('[situacao]', $this->geraSelect($this->buscaSituacoes(), $_POST["situacao"], true, "situacao", "150px"), $this->conteudo);
    //$this->conteudo = str_replace('[responsavel_disabled]', $this->permissoes["condominios"]["buscar_plus"] ? "" : "disabled", $this->conteudo);
    $this->conteudo = str_replace('[resultado]', $this->geraResultadoBusca(), $this->conteudo);
    $botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
    $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=buscar", $this->conteudo);
  }

  function geraResultadoBusca() {
    if ($_POST) {
      // Campos
      //$campos = array ("id" => "#", "nome" => "Nome", "endereco" => "Endereço", "blocos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Blocos no Condomínio\");' onMouseOut='hintHide();' class='txtBranco11'>BL</a>", "apartamentos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Apartamentos no Condomínio\");' onMouseOut='hintHide();' class='txtBranco11'>AS</a>", "clientes_mailings" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Mailing\");' onMouseOut='hintHide();' class='txtBranco11'>CM</a>", "clientes_prospects" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Prospects\");' onMouseOut='hintHide();' class='txtBranco11'>CP</a>", "clientes_ativos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Ativos\");' onMouseOut='hintHide();' class='txtBranco11'>CA</a>", "clientes_cancelados" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Cancelados\");' onMouseOut='hintHide();' class='txtBranco11'>CC</a>", "ativos_pontos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Pontos Ativos\");' onMouseOut='hintHide();' class='txtBranco11'>AP</a>", "velocidade_maxima" => "<a href='#' onMouseOver='hintShow(\"Velocidade Máxima\");' onMouseOut='hintHide();' class='txtBranco11'>VM</a>", "mensalidade" => "Mensalidade", "responsavel" => "Responsável", "contato" => "Contato(s)", "status" => "Status / Links");
      //$campos = array ("id" => "#", "nome" => "Nome", "endereco" => "Endereço", "blocos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Blocos no Condomínio\");' onMouseOut='hintHide();' class='txtBranco11'>BL</a>", "apartamentos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Apartamentos no Condomínio\");' onMouseOut='hintHide();' class='txtBranco11'>AS</a>", "clientes_mailings" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Mailing\");' onMouseOut='hintHide();' class='txtBranco11'>CM</a>", "clientes_prospects" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Prospects\");' onMouseOut='hintHide();' class='txtBranco11'>CP</a>", "clientes_ativos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Ativos\");' onMouseOut='hintHide();' class='txtBranco11'>CA</a>", "clientes_cancelados" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Cancelados\");' onMouseOut='hintHide();' class='txtBranco11'>CC</a>", "ativos_pontos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Pontos Ativos\");' onMouseOut='hintHide();' class='txtBranco11'>AP</a>", "velocidade_maxima" => "<a href='#' onMouseOver='hintShow(\"Velocidade Máxima\");' onMouseOut='hintHide();' class='txtBranco11'>VM</a>", "comentarios_comercial" => "Comentários", "responsavel" => "Responsável", "contato" => "Contato(s)", "status" => "Status / Links");
      $campos = array ("id" => "#", "nome" => "Nome", "endereco" => "Endereço", "blocos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Blocos no Condomínio\");' onMouseOut='hintHide();' class='txtBranco11'>BL</a>", "apartamentos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Apartamentos no Condomínio\");' onMouseOut='hintHide();' class='txtBranco11'>AP</a>", "clientes_ativos" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Ativos\");' onMouseOut='hintHide();' class='txtBranco11'>CA</a>", "clientes_cancelados" => "<a href='#' onMouseOver='hintShow(\"Quantidade de Clientes Cancelados\");' onMouseOut='hintHide();' class='txtBranco11'>CC</a>", "velocidade_maxima" => "<a href='#' onMouseOver='hintShow(\"Velocidade Máxima\");' onMouseOut='hintHide();' class='txtBranco11'>VM</a>", "comentarios_comercial" => "Comentários", "responsavel" => "Responsável", "contato" => "Contato(s)", "status" => "Status / Links");
      // Filtros
      $where = array();
      if (trim($_POST["nome"])) {
		$where[] = $this->geraQueryTexto($_POST["nome"], "tab_condominios.con_nome");
     }
     if ($_POST["cidade2"]) {
     	$where[] = "tab_cidades.cid_id = '$_POST[cidade2]'";
     } elseif (trim($_POST["cidade1"])) {
		$where[] = $this->geraQueryTexto($_POST["cidade1"], "tab_cidades.cid_nome");
      }
      if ($_POST["bairro2"]) {
      	$where[] = "tab_bairros.bai_id = '$_POST[bairro2]'";
      } elseif (trim($_POST["bairro1"])) {
 		$where[] = $this->geraQueryTexto($_POST["bairro1"], "tab_bairros.bai_nome");
      }
      if (trim($_POST["logradouro"])) {
 		$where[] = $this->geraQueryTexto($_POST["logradouro"], "tab_logradouros.log_nome");
      }
		$_POST["cep"] = preg_replace("/[^0-9]/", "", $_POST["cep"]);
      if ($_POST["cep"]) {
        $where[] = "tab_condominios.cep_id = {$_POST["cep"]}";
      }
      if ($_POST["numero"]) {
        $_POST["numero"] = intval($_POST["numero"], 10);
        $where[] = "tab_condominios.con_numero = {$_POST["numero"]}";
      }
      if ($_POST["data_cadastro_inicio"]) {
        $_POST["data_cadastro_inicio"] = substr($_POST["data_cadastro_inicio"], 6, 4).substr($_POST["data_cadastro_inicio"], 3, 2).substr($_POST["data_cadastro_inicio"], 0, 2);
        $where[] = "(DATE_FORMAT(tab_condominios.con_data_cadastro,'%Y%m%d') >= '{$_POST["data_cadastro_inicio"]}')";
      }
      if ($_POST["data_cadastro_termino"]) {
        $_POST["data_cadastro_termino"] = substr($_POST["data_cadastro_termino"], 6, 4).substr($_POST["data_cadastro_termino"], 3, 2).substr($_POST["data_cadastro_termino"], 0, 2);
        $where[] = "(DATE_FORMAT(tab_condominios.con_data_cadastro,'%Y%m%d') <= '{$_POST["data_cadastro_termino"]}')";
      }
      if ($_POST["data_ativacao_inicio"]) {
        $_POST["data_ativacao_inicio"] = substr($_POST["data_ativacao_inicio"], 6, 4).substr($_POST["data_ativacao_inicio"], 3, 2).substr($_POST["data_ativacao_inicio"], 0, 2);
        $where[] = "(DATE_FORMAT(tab_condominios.con_data_ativacao,'%Y%m%d') >= '{$_POST["data_ativacao_inicio"]}')";
      }
      if ($_POST["data_ativacao_termino"]) {
        $_POST["data_ativacao_termino"] = substr($_POST["data_ativacao_termino"], 6, 4).substr($_POST["data_ativacao_termino"], 3, 2).substr($_POST["data_ativacao_termino"], 0, 2);
        $where[] = "(DATE_FORMAT(tab_condominios.con_data_ativacao,'%Y%m%d') <= '{$_POST["data_ativacao_termino"]}')";
      }
      if ($_POST["responsavel"]) {
        $where[] = "tab_condominios.usu_id = {$_POST["responsavel"]}";
        unset($campos["responsavel"]);
      } elseif (!$this->permissoes["condominios"]["buscar_plus"]) {
        // Responsável ou condomínio ativo
        $where[] = "(tab_condominios.usu_id = {$_SESSION["usuario"]} || tab_condominios.cos_id = 2)";
      }
      if ($_POST["status"]) {
        $where[] = "tab_condominios.cos_id = {$_POST["status"]}";
      }
      if ($_POST["situacao"]) {
        $where[] = "tab_condominios.coi_id = {$_POST["situacao"]}";
      }
      if ($_POST["tipo"]) {
        $where[] = "tab_condominios.cnt_id = {$_POST["tipo"]}";
      }
      
      $where = implode(' AND ', $where);
      if (!$where) {
      	$where = '1';
      }

      if ($this->permissoes["condominios"]["buscar_ilimitado"] || ($this->permissoes["condominios"]["buscar_plus_plus"] && ($_POST["bairro2"] || $_POST["responsavel"]))) {
        $limite_itens = 0;
      } else {
        $limite_itens = BUS_ITENS;
      }

      $query = "
      SELECT
        tab_condominios.con_id AS id,
        tab_condominios.con_nome AS nome,
        tab_condominios.con_numero AS numero,
        tab_condominios.con_comentarios_comercial AS comentarios_comercial,
  			tab_condominios.cep_id AS cep,
        tab_condominios_status.cos_id AS cos_id,
        tab_condominios_status.cos_nome AS status,
        tab_condominios_situacoes.coi_id AS situacao_id,
        tab_condominios_situacoes.coi_nome AS situacao_nome,
        tab_condominios.con_velocidade_maxima AS velocidade_maxima,
        tab_usuarios.usu_nome AS responsavel,
        tab_condominios_tecnologias.cog_nome AS tecnologia,
        tab_condominios_tecnologias_equipamentos.coq_nome AS equipamento
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
        INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
        INNER JOIN tab_condominios_situacoes ON (tab_condominios.coi_id = tab_condominios_situacoes.coi_id)
        LEFT OUTER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)
        LEFT OUTER JOIN tab_condominios_tecnologias ON (tab_condominios.cog_id = tab_condominios_tecnologias.cog_id)
        LEFT OUTER JOIN tab_condominios_tecnologias_equipamentos ON (tab_condominios.coq_id = tab_condominios_tecnologias_equipamentos.coq_id)
      WHERE
        $where
      ORDER BY
        tab_condominios.con_nome ASC";
      $query = $this->bancodedados->query($query);
      if (!$limite_itens || ($this->bancodedados->linhas($query) <= $limite_itens)) {
				$itens = array();
      	while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
          if ($this->permissoes["condominios"]["ver_dc"]) {
            $dados["link"] = "?m=condominios&a=ver_dc&i={$dados["id"]}";
          }
          $dados["blocos"] = $this->contaItemBlocos($dados["id"]);
          $dados["apartamentos"] = $this->contaItemApartamentos($dados["id"]);
          $dados["clientes_mailings"] = $this->contaItemClientesMailing($dados["id"]);
          $dados["clientes_prospects"] = $this->contaItemClientesProspects($dados["id"]);
          $dados["clientes_ativos"] = $this->contaItemClientesAtivos($dados["id"]);
          $dados["ativos_pontos"] = $this->contaItemPontosAtivos($dados["id"]);
          $dados["clientes_cancelados"] = $this->contaItemClientesCancelados($dados["id"]);
          //$dados["pontos_cancelados"] = $this->contaItemPontosCancelados($dados["id"]);
          //$dados["mensalidade"] = $this->buscaItemMensalidade($dados["id"]);
          $dados["endereco"] = $this->buscaEndereco($dados["cep"], $dados["numero"]);
          $dados["contato"] = $this->buscaContato($dados["id"]);
          if ($dados["situacao_id"] != 1) {
            $dados["status"] .= "<br>({$dados["situacao_nome"]})";
          }
          $dados["status"] .= $this->geraLinks($dados["id"]);
          $velocidade_maxima = $this->buscaTamanho($dados["velocidade_maxima"]);
          $dados["velocidade_maxima"] = $velocidade_maxima["tamanho"] ? $velocidade_maxima["tamanho"]." ".$this->buscaUnidades("bps", $velocidade_maxima["tipo"]) : "";
          /*
          $tecnologia = array();
          if ($dados["velocidade_maxima"]) {
            $tecnologia[] = $dados["velocidade_maxima"];
          }
          if ($dados["tecnologia"]) {
            $tecnologia[] = $dados["tecnologia"];
          }
          if ($dados["equipamento"]) {
            $tecnologia[] = "({$dados["equipamento"]})";
          }
          $dados["velocidade_maxima"] = implode("<br>", $tecnologia);
          */
          $dados["id"] = count($itens) + 1;
          $itens[] = $dados;
        }
        $listagem = controle::instanciaObjeto("listagem", $campos, $itens);
        $resultado = $listagem->conteudo;
      } else {
        $resultado = "Muitos registros foram encontrados, especifique melhor a sua busca!<br>(Máximo $limite_itens itens)";
      }
      return $resultado;
    } else {
      return "";
    }
  }

  function geraLinks($id) {
    if ($this->permissoes["condominios"]["ver_dc"]) $links[] = "<a class='txtAzulEscuro11' href='?m=condominios&a=ver_dc&i=$id'>DC</a>";
    if ($this->permissoes["condominios"]["ver_df"]) $links[] = "<a class='txtAzulEscuro11' href='?m=condominios&a=ver_df&i=$id'>DF</a>";
    if ($this->permissoes["condominios"]["ver_dm"]) $links[] = "<a class='txtAzulEscuro11' href='?m=condominios&a=ver_dm&i=$id'>DM</a>";
    if ($this->permissoes["condominios"]["ver_dt"]) $links[] = "<a class='txtAzulEscuro11' href='?m=condominios&a=ver_dt&i=$id'>DT</a>";
    if (sizeof($links)) {
      $links = "<div style=\"width:80px\" nowrap>".implode(" ", $links)."</div>";
    }
    return $links;
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

  function buscaBairros($cidade = '%') {
    $query = "
      SELECT DISTINCT
        tab_bairros.bai_id AS id,
        tab_bairros.bai_nome AS nome
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      WHERE
        tab_bairros.cid_id LIKE '$cidade'
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $bairros[$dados["id"]] = $dados["nome"];
    }
    return $bairros;
  }

  function buscaCidade($cidade_id) {
    $query = "
      SELECT
        tab_cidades.cid_nome AS nome
      FROM
        tab_cidades
      WHERE
        tab_cidades.cid_id = $cidade_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 0);
  }

  function buscaBairro($bairro_id) {
    $query = "
      SELECT
        tab_bairros.bai_nome AS nome
      FROM
        tab_bairros
      WHERE
        tab_bairros.bai_id = $bairro_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultado($query, 0, 0);
  }

  ###############################
  ### Verifica Mesmo Endereço ###
  ###############################

  function buscaAlertasCondominio($condominio_id, $cep_id, $numero) {
  	$condominios = array();
  	$cep_id = preg_replace("/[^0-9]/", "", $cep_id);
  	if ($condominio_id && $cep_id) {
	  	$numero = $numero ? $numero : "NULL";
	    $query = "
	      SELECT
	        tab_condominios.con_id AS id,
	        tab_condominios.con_nome AS nome,
	        tab_condominios_situacoes.coi_id AS situacao_id,
	        tab_condominios_situacoes.coi_nome AS situacao_nome,
	        tab_condominios_status.cos_id AS status_id,
	        tab_condominios_status.cos_nome AS status_nome,
	        tab_usuarios.usu_id AS responsavel_id,
	        tab_usuarios.usu_nome AS responsavel_nome
	      FROM
	        tab_condominios
	        INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
	        LEFT OUTER JOIN tab_condominios_situacoes ON (tab_condominios.coi_id = tab_condominios_situacoes.coi_id)
	        LEFT OUTER JOIN tab_usuarios ON (tab_condominios.usu_id = tab_usuarios.usu_id)
	      WHERE
	        tab_condominios.cep_id = $cep_id AND
	        tab_condominios.con_numero = $numero AND
	        tab_condominios.con_id <> $condominio_id";
	    $query = $this->bancodedados->query($query);
	    $responsavel_id = null;
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	      if ($dados["status_id"] == 1) $dados["status_nome"] .= " ({$dados["situacao_nome"]})";
	      if (!$responsavel_id) $responsavel_id = $dados["responsavel_id"];
	      $dados["responsavel_nome"] = $dados["responsavel_nome"] ? $dados["responsavel_nome"] : "Sem responsável";
	      $condominios[] = $dados;
	    }
  	}
    if (count($condominios)) {
      if (count($condominios) == 1) {
        $mensagem_js = "O seguinte condomínio está cadastrado no mesmo endereço:\\n";
        $mensagem_html = "Condomínio cadastrado no mesmo endereço:</b><br>";
      } else {
        $mensagem_js = "Os seguintes condomínios estão cadastrados no mesmo endereço:\\n";
        $mensagem_html = "Condomínios cadastrados no mesmo endereço:</b><br>";
      }
      foreach ($condominios as $condominio) {
        $mensagem_js .= "\\n";
        $mensagem_js .= "{$condominio["nome"]} ({$condominio["status_nome"]})\\n";
        $mensagem_js .= " » Responsável: {$condominio["responsavel_nome"]}\\n";
        $mensagem_html .= "<br>";
        $mensagem_html .= "<b><a href=\"?m=condominios&a=ver_dc&i={$condominio["id"]}\" class=\"txtAzulEscuro11\">{$condominio["nome"]}</a></b> ({$condominio["status_nome"]})<br>";
        $mensagem_html .= " » <b>Responsável</b>: {$condominio["responsavel_nome"]}<br>";
      }
      return array("responsavel_id" => $responsavel_id, "mensagem_js" => $mensagem_js, "mensagem_html" => $mensagem_html);
    }
  }

  ########################
  ### Capturar Mailing ###
  ########################

  function capturar_mailing() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item && ($this->dados_item["responsavel_id"] == $_SESSION["usuario"] || 1)) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("condominios.capturar_mailing.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=capturar_mailing&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        set_time_limit(0);
        $mailing = $this->capturaMailing($_GET["i"]);
        if ($mailing) {
          $cobranca_formato_id = $this->buscaCobrancaFormatoPadrao();
          $responsabilidade = $this->buscaResponsabilidadeCondominio($_GET["i"]);
          foreach ($mailing as $cliente) {
            $cliente_id = $this->geraRegistroCliente($cliente, $this->dados_item["responsavel_id"], $cobranca_formato_id);
            $responsabilidade_id = $this->registraResponsabilidadeCliente($cliente_id, $responsabilidade["inicio"], $responsabilidade["termino"], $responsabilidade["parametros"], $responsabilidade["responsavel"], $responsabilidade["id"], 'NULL', $responsabilidade["tipo"], 4);
            $this->registraComentarioResponsabilidadeCliente($responsabilidade_id, "Adicionado com Auto-Ativação (Herdado do Condomínio)");
          }
          $operacao_lote_id = $this->geraLoteOperacao($this->dados_item["id"], $this->dados_item["responsavel_id"], "{$this->dados_item["nome"]} ({$this->dados_item["cidade"]} / {$this->dados_item["bairro"]})");
          $this->distribuirResponsaveis($operacao_lote_id);
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dm&i={$_GET["i"]}&q=".count($mailing));
        } else {
          header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dm&i={$_GET["i"]}&e=1");
        }
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  function buscaResponsabilidadeCondominio($condominio_id) {
    $query = "
      SELECT
        cod_id AS id,
        IF(cod_inicio < NOW(), NOW(), cod_inicio) AS inicio,
        cod_termino AS termino,
        cod_parametros AS parametros,
        usu_id AS responsavel,
        crp_id AS tipo,
        IF((NOW() BETWEEN cod_inicio AND cod_termino) AND (crp_id = 2), 1, 0) AS atual
      FROM
        tab_condominios_responsabilidades
      WHERE
        cod_termino >= NOW() AND
        cru_id IN (2, 4) AND
        con_id = $condominio_id
      ORDER BY
        atual DESC";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
  }

  function registraResponsabilidadeCliente($cliente_id, $inicio, $termino, $parametros, $responsavel_id, $responsabilidade_pai_id, $categoria_id, $tipo_id, $status_id) {
    if (!$responsabilidade_pai_id) $responsabilidade_pai_id = "NULL";
    if ($inicio != 'NOW()') $inicio = "'$inicio'";
    if ($termino != 'NOW()') $termino = "'$termino'";
    $query = "INSERT INTO tab_clientes_responsabilidades (cli_id, cln_inicio, cln_termino, cln_parametros, usu_id, cod_id, crt_id, cri_id, cra_id) VALUES ($cliente_id, $inicio, $termino, '$parametros', $responsavel_id, $responsabilidade_pai_id, $categoria_id, $tipo_id, $status_id)";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
  }

  function registraComentarioResponsabilidadeCliente($responsabilidade_id, $comentario) {
    $query = "INSERT INTO tab_clientes_responsabilidades_comentarios (crc_datahora, cln_id, crc_conteudo) VALUES (NOW(), $responsabilidade_id, '<b>Ação:</b> $comentario')";
    $this->bancodedados->query($query);
  }

  function buscaCobrancaFormatoPadrao() {
    $query = "
      SELECT
        cof_id AS formato_id
      FROM
        tab_cobrancas_formatos
      WHERE
        cfs_id = 1 AND
        cft_id = 2";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return  $dados["formato_id"];
    }
  }

  function geraRegistroCliente($cliente, $usuario_id, $cobranca_formato_id) {
    // Busca Cliente Existente
    if (!($cliente_id = $this->buscaCliente($cliente))) {
      // Trata Dados Cliente
      $cliente["bloco_id"] = $cliente["bloco_id"] ? $cliente["bloco_id"] : "NULL";
      $cliente["complemento"] = $cliente["complemento"] ? "'a{$cliente["complemento"]}'" : "NULL";
      // Insere Novo Cliente
      $query = "INSERT INTO tab_clientes (cli_nome, cli_data_cadastro, cof_id, cob_id, con_id, cli_complemento, cls_id, usu_id, clo_id) VALUES ('{$cliente["nome"]}', NOW(), $cobranca_formato_id, {$cliente["bloco_id"]}, {$cliente["condominio_id"]}, {$cliente["complemento"]}, 4, $usuario_id, 4)";
      $this->bancodedados->query($query);
      $cliente_id = $this->bancodedados->ultimoIdInserido();
    }
    // Trata Dados Contato
    $ddd = substr($cliente["telefone"], 1, 2);
    $numero = substr($cliente["telefone"], 5);
    // Procura Contato Existente
    $query = "SELECT ccl_id FROM tab_clientes_contatos_telefones WHERE cli_id = $cliente_id AND ccl_ddd = '$ddd' AND ccl_numero = '$numero'";
    $query = $this->bancodedados->query($query);
    if (!$this->bancodedados->resultadoArray($query)) {
      // Insere Novo Contato
      $query = "INSERT INTO tab_clientes_contatos_telefones (ccl_ddd, ccl_numero, cli_id, ccf_id) VALUES ('$ddd', '$numero', $cliente_id, 6)";
      $this->bancodedados->query($query);
    }
    return $cliente_id;
  }

  function geraLoteOperacao($condominio_id, $usuario_id, $lote_nome) {
    $regras = $this->geraRegrasOperacao($usuario_id, $condominio_id);
    return $this->insereLoteOperacao($lote_nome, 1, $regras);
  }

	function insereLoteOperacao($nome, $sistema_id, $regras) {
    $query = "
      INSERT INTO
        tab_operacoes_lotes (opl_nome, oli_id, opl_regras, ols_id)
      VALUES ('$nome', $sistema_id, '$regras', 1)";
    $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
	}

	function geraRegrasOperacao($usuario_id, $condominio_id) {
    $regras["enderecos"] = null;
    // Filtro Condomínio
    $regras["filtros"][] = $this->geraRegraFiltro(14, "=", $condominio_id);
    // Filtro Responsável
    $regras["filtros"][] = $this->geraRegraFiltro(12, "=", $usuario_id);
    // Filtro Status do Cliente (Inativo)
    $regras["filtros"][] = $this->geraRegraFiltro(7, "<>", 2);
    // Operadores do Lote
    $regras["validades"][] = $this->geraRegraValidade($usuario_id, "", "", -1);
    // Últimos Contatos
    $regras["ultimos_contatos"] = "-1, 19, 20, 21, 22, 23, 24, 32";
    return serialize($regras);
	}

	function geraRegraFiltro($campo, $comparacao, $valor) {
	  $filtros["campo"] = $campo;
	  $filtros["comparacao"] = $comparacao;
	  $filtros["valor"] = $valor;
	  return $filtros;
	}

	function geraRegraValidade($responsavel_id, $inicio, $termino, $periodo) {
    $validade["responsavel"] = $responsavel_id;
    $validade["inicio"] = $inicio;
    $validade["termino"] = $termino;
    $validade["periodo"] = $periodo;
    return $validade;
	}

	function distribuirResponsaveis($lote_id) {
    $operacoes_lotes = controle::instanciaObjeto("operacoes_lotes", $this->bancodedados, null, false);
    $operacoes_lotes->distribuirResponsaveis($lote_id);
	}

  function capturaMailing($condominio_id) {
    $mailing = controle::instanciaObjeto("mailing", true, true, true);
    $enderecos = $this->buscaEnderecosBlocos($condominio_id);
    if ($enderecos) {
      foreach ($enderecos as $endereco) {
      	$retorno = $mailing->buscaRegistrosPorEndereco($endereco["estado"], $endereco["cidade"], $endereco["bairro"], $endereco["logradouro"], array($endereco["cep"]), $endereco["numero"], null, null);
        if ($retorno) {
          $mailings = funcoes::mescla_arrays($mailings, $this->trataRetorno($retorno, $condominio_id, $endereco["blocos"]));
        }
      }
      return $mailings;
    }
  }

  function trataRetorno($retorno, $condominio_id, $blocos) {
    foreach ($retorno as $registro) {
      $registro["endereco"] = strtolower($registro["endereco"]);
      if ($registro["origem"] == "guiafacil") {
        $registro["endereco"] = str_replace("bl", " bl ", $registro["endereco"]);
        $registro["endereco"] = str_replace("ap", " ap ", $registro["endereco"]);
      }
      preg_match_all("| bl (.*) |U", " {$registro["endereco"]} ", $bloco);
      if (!preg_match_all("| ap (.*) |U", " {$registro["endereco"]} ", $complemento)) {
        if (!preg_match_all("| sl (.*) |U", " {$registro["endereco"]} ", $complemento)) {
          preg_match_all("| lj (.*) |U", " {$registro["endereco"]} ", $complemento);
        }
      }
      $novo_registro["nome"] = $registro["nome"];
      $novo_registro["telefone"] = $registro["telefone"];
      $novo_registro["condominio_id"] = $condominio_id;
      $novo_registro["bloco_id"] = count($blocos) == 1 ? $blocos[0] : ($bloco[1] ? $this->buscaBloco($bloco[1][0], $condominio_id) : null);
      $novo_registro["complemento"] = $complemento[1] ? $complemento[1][0] : null;
      if (!$this->buscaCliente($novo_registro)) {
        $registros[] = $novo_registro;
      }
    }
    return $registros;
  }

  function buscaCliente($mailing) {
    if ($mailing["bloco_id"] && $mailing["complemento"]) {
      $query = "
        SELECT
          cli_id AS id,
          cli_nome AS nome,
          cls_id AS status_id
        FROM
          tab_clientes
        WHERE
          cob_id = {$mailing["bloco_id"]} AND
          cli_complemento LIKE '_{$mailing["complemento"]}'";
      $query = $this->bancodedados->query($query);
      while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        if ($dados["status_id"] != 3 || ($this->geraSoundex($mailing["nome"]) == $this->geraSoundex($dados["nome"]))) {
          return $dados["id"];
        }
      }
    }
  }

  function geraSoundex($texto) {
    $texto = str_replace(array(" de ", " do ", " da ", " dos ", " das ", " e "), " ", $texto);
    $texto = preg_replace("/[\r\n\t ]+/", " ", $texto);
    $texto = trim($texto);
    $texto = explode(" ", $texto);
    for ($i = 1; $i < count($texto); $i++) {
      $texto[$i] = substr($texto[$i], 0, 1);
    }
    return soundex("a".implode($texto));
  }

  function buscaBloco($bloco, $condominio_id) {
    $query = "
      SELECT
        cob_id AS id
      FROM
        tab_condominios_blocos
      WHERE
        con_id = $condominio_id AND
        cob_nome LIKE '$bloco'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }
  
 function buscaBlocosCondominio($condominio_id) {
 	 $blocos = array();
 	 if ($condominio_id) {
	   $query = "
	     SELECT
	       cob_id AS id,
	       cob_nome AS nome
	     FROM
	       tab_condominios_blocos
	     WHERE
	       con_id = $condominio_id
		 ORDER BY
			nome";
	    $query = $this->bancodedados->query($query);
	    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			  $blocos[$dados["id"]]=$dados["nome"];
	    }
	}
    return $blocos;
  }

  function buscaEnderecosBlocos($condominio_id) {
    $query = "
      SELECT
        tab_condominios_blocos.cob_id AS bloco_id,
        tab_logradouros.log_nome AS logradouro,
        IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, tab_condominios.con_numero) AS numero,
        tab_bairros.bai_nome AS bairro,
        tab_cidades.cid_nome AS cidade,
        tab_estados.est_sigla AS estado,
  			INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep
      FROM
        tab_condominios
        INNER JOIN tab_condominios_blocos ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
        INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_condominios.cep_id) = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
        INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
      WHERE
        tab_condominios_blocos.con_id = $condominio_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $blocos["{$dados["cep"]},{$dados["numero"]}"][] = $dados["bloco_id"];
      $enderecos["{$dados["cep"]},{$dados["numero"]}"] = $dados;
    }
    foreach ($enderecos as $chave => $endereco) {
      unset($enderecos[$chave]["bloco_id"]);
      $enderecos[$chave]["blocos"] = $blocos[$chave];
    }
    return $enderecos;
  }

  ################
  ### Publicar ###
  ################

  function publicar() {
	  if (!$_POST) {
			if ($_GET["sucesso"]) {
				$this->conteudo .= "<script type=\"text/javascript\">alert('Servidor(es) publicado(s) com sucesso!')</script>";
				$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a=ver_dt&i={$_GET["i"]}'</script>";
			} else {
	    	$this->conteudo .= $this->carregaArquivo("condominios.publicar.inc.php");
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
    	  $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onclick="window.close()">';
				if ($_GET["erro"]) {
					$mensagem = "{$_GET["erro"]}<br><br>Clique em Confirmar para tentar novamente.";
				} else {
					$mensagem = "Você deseja realmente <b>publicar</b> o(s) servidor(es) deste condomínio?";
				}
				$this->conteudo = str_replace('[mensagem]', $mensagem, $this->conteudo);
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}", $this->conteudo);
			}
		} else {
			$servidores = $this->buscaCondominioServidores($_GET["i"]);
		  if ($servidores) {
		    foreach ($servidores as $servidor_id) {
		    	$erro = $this->publicarServicos($servidor_id);
			    if ($erro) {
	  	      header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=$erro");
			    }
		    }
		  } else {
	      header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=Nenhum servidor vinculado");
		  }
   		header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&sucesso=1");
		}
  }

	function publicarServicos($servidor_id) {
    $servidores = controle::instanciaObjeto("servidores", $this->bancodedados, $this->permissoes, false);
    return $servidores->publicarServicos($servidor_id);
	}
	
	function geraQueryTexto($texto, $campo, $campo_alternativo = null) {
		
		$blacklist = array("o", "a", "e", "de", "do", "dos", "di", "da", "das");
		//$logradouros = array("rua", "estrada", "rodovia", "praça", "avenida", "alameda", "beco", "loteamento", "via", "vila", "travessa", "servidao", "passagem", "parque", "largo", "jardim", "passeio");
		$logradouros = array("alameda", "aeroporto", "área", "avenida", "campo", "chácara", "colônia", "condomínio", "conjunto", "distrito", "esplanada", "estação", "estrada", "favela", "fazenda", "feira", "jardim", "ladeira", "lago", "lagoa", "largo", "loteamento", "morro", "núcleo", "parque", "passarela", "pátio", "praça", "quadra", "recanto", "residencial", "rua", "rodovia", "servidão", "setor", "sítio", "travessa", "trecho", "trevo", "vale", "vereda", "via", "viaduto", "viela", "vila");
		
		$texto = mb_strtolower($texto);
		$texto = preg_replace("/[ç]/", "c", $texto);
		$texto = preg_replace("/[ñ]/", "n", $texto);
		$texto = preg_replace("/[áàâãä]/", "a", $texto);
		$texto = preg_replace("/[éèêë]/", "e", $texto);
		$texto = preg_replace("/[íìîï]/", "i", $texto);
		$texto = preg_replace("/[óòôõö]/", "o", $texto);
		$texto = preg_replace("/[úùûü]/", "u", $texto);
		$texto = trim(preg_replace("/[^a-z0-9]+/", " ", $texto));
		$texto = explode(" ", $texto);
		
		$tokens = array();
		
		for ($i = 0; $i < count($texto); $i++) {
			if (!in_array($texto[$i], $blacklist) && ($i != 0 || !in_array($texto[$i], $logradouros))) {
				if ($campo_alternativo) {
					$tokens[] = "({$campo} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}' OR {$campo_alternativo} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}')";
				} else {
					$tokens[] = "({$campo} REGEXP '[[:<:]]{$this->geraExpressao($texto[$i])}')";
				}
			}
		}

		return implode(" AND ", $tokens);
		
	}
	
	function geraExpressao($token) {

		$origem  = array(
			"/(.)\\1+/",
			"/nh/",
			"/([^p])h/",
			"/s$/",
			"/(.)/",
			"/([aeiou]) l ([b-df-hj-np-tv-z])/",
			"/(g n)/",
			"/(d t|t d|t) $/",
			"/(f|p h) /",
			"/([vw] )+/",
			"/([mn] )+/",
			"/(([jg]|gu) )+/",
			"/(([cszxkq]|qu) )+/",
			"/a /",
			"/[eiy]+ /",
			"/[ou]+ /",
			"/([a-z0-9]) /",
			"/ +/",
			"/$/"
		);
		
		$destino = array(
			"$1",
			"gn",
			"$1",
			"",
			"$1 ",
			"$1 (h?[lu])+ $2",
			" (gn|nh) ",
			" (t+|dt|td) ",
			" (f+|ph) ",
			" (h?[vw])+ ",
			" (h?[mn])+ ",
			" (h?[jgu])+ ",
			" (h?[cszxkquç])+ ",
			"(h?[aáàâãä])+ ",
			"(h?[eéèêëiíìîïy])+ ",
			"(h?[oóòôõöuúùûü])+ ",
			"(h?$1)+ ",
			"",
			"h?"
		);		

		return preg_replace($origem, $destino, $token);
		
	}

}

?>
