<?

require_once("_global/modulo.class.inc.php");

class servidores extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    if ($executar) {
      $this->buscaDadosModulo($_GET["m"]);
      if ($_GET["a"] == "ver_dc" && $_GET["i"] == 1) $_GET["a"] = "ver_di";
      if ($_GET["a"] == "ver_dc" && $this->permissoes[$this->dados_modulo["nome"]]["editar_dc"]) $_GET["a"] = "editar_dc";
      if ($_GET["a"] == "ver_di" && $this->permissoes[$this->dados_modulo["nome"]]["editar_di"]) $_GET["a"] = "editar_di";
      $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
      $this->conteudo = $this->geraTitulo();
      $this->{$this->acao}();
    }
  }

  ########################
  ### Busca Dados Item ###
  ########################

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_servidores.ser_id AS id,
        tab_servidores.ser_nome AS nome,
        tab_servidores.ser_endereco_ip AS endereco_ip,
        tab_servidores.ser_ip_secundario AS endereco_ip_secundario,
        tab_servidores.set_id AS status_id,
        tab_servidores.ser_configuracao AS configuracao,
        tab_servidores.sei_id AS interface_id,
        UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(tab_servidores.ser_data_hora_situacao) AS duracao,
        tab_servidores_status.set_nome AS status_nome,
        tab_servidores_tipos.sep_id AS tipo_id,
        tab_servidores_tipos.sep_nome AS tipo_nome,
        tab_servidores_situacoes.seu_id AS situacao_id,
        tab_servidores_situacoes.seu_nome AS situacao_nome,
        tab_servidores_sistemas_operacionais.sso_id AS sistema_operacional_id,
        tab_servidores_sistemas_operacionais.sso_nome AS sisteam_operacional_nome,
		tab_servidores.ser_publicacao_versao AS publicacao_versao
      FROM
        tab_servidores
        INNER JOIN tab_servidores_tipos ON (tab_servidores_tipos.sep_id = tab_servidores.sep_id)
        INNER JOIN tab_servidores_status ON (tab_servidores_status.set_id = tab_servidores.set_id)
        INNER JOIN tab_servidores_situacoes ON (tab_servidores_situacoes.seu_id = tab_servidores.seu_id)
        INNER JOIN tab_servidores_sistemas_operacionais ON (tab_servidores.sso_id = tab_servidores_sistemas_operacionais.sso_id)
      WHERE
        tab_servidores.ser_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["configuracao"] = @unserialize($this->dados_item["configuracao"]);
    }
  }

  function formataDuracao($duracao) {
    // Dias
    if (($resto = $duracao % 86400) <> $duracao) {
      $duracao_formatada[] = (($duracao - $resto) / 86400)." ".((($duracao - $resto) / 86400) > 1 ? "dias" : "dia");
      $duracao = $resto;
    }
    // Horas
    if (($resto = $duracao % 3600) <> $duracao) {
      $duracao_formatada[] = (($duracao - $resto) / 3600)." ".((($duracao - $resto) / 3600) > 1 ? "horas" : "hora");
      $duracao = $resto;
    }
    // Minutos
    if (($resto = $duracao % 60) <> $duracao) {
      $duracao_formatada[] = (($duracao - $resto) / 60)." ".((($duracao - $resto) / 60) > 1 ? "minutos" : "minuto");
      $duracao = $resto;
    }
    // Segundos
    if ($duracao > 0) {
      $duracao_formatada[] = $duracao." ".($duracao > 1 ? "segundos" : "segundo");
    }
    return @implode(", ", $duracao_formatada);
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaStatus() {
    $query = "
      SELECT
        set_id AS id,
        set_nome AS nome
      FROM
        tab_servidores_status
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaSituacoes() {
    $query = "
      SELECT
        seu_id AS id,
        seu_nome AS nome
      FROM
        tab_servidores_situacoes
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $situacoes[] = $dados;
    }
    return $situacoes;
  }

  function buscaTipos() {
    $query = "
      SELECT
        sep_id AS id,
        sep_nome AS nome
      FROM
        tab_servidores_tipos
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $tipos[$dados["id"]] = $dados["nome"];
    }
    return $tipos;
  }

  function buscaSistemasOperacionais() {
    $query = "
      SELECT
        sso_id AS id,
        sso_nome AS nome
      FROM
        tab_servidores_sistemas_operacionais
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $sistemas[$dados["id"]] = $dados["nome"];
    }
    return $sistemas;
  }
  
  function buscaServidoresInterfaces($servidor_id) {
    $servidores_interfaces = controle::instanciaObjeto("servidores_interfaces", $this->bancodedados, $this->permissoes, false);
    return $servidores_interfaces->geraServidoresInterfacesListagem($servidores_interfaces->buscaServidoresInterfaces($servidor_id));
  }

  #############
  ### Geral ###
  #############

  function buscaPrefixoIPInterface($interface) {
    $query = "SELECT sei_prefixo_ip AS prefixo FROM tab_servidores_interfaces WHERE sei_id = $interface";
    $query = $this->bancodedados->query($query);
    $prefixo = $this->bancodedados->resultadoArray($query);
    return $prefixo["prefixo"];
  }

  function buscaPrefixoIP($prefixo, $posicao) {
    $prefixo = explode('.', $prefixo);
    return $prefixo[$posicao];
  }

  function buscaHDTipos() {
    $lista["1"] = "IDE";
    $lista["2"] = "SCSI";
    $lista["4"] = "SCSI (RAID)";
    $lista["3"] = "SATA";
    $lista["5"] = "SATA (RAID)";
    $lista["6"] = "BUSCARD (Compact Flash)";
    $lista["7"] = "BUSCARD (SD Card)";
    $lista["8"] = "BUSCARD (Smartmedia)";
    return $lista;
  }

  function buscaMemoriaTipos() {
    $lista["1"] = "EDO SIMM";
    $lista["2"] = "EDO RAM";
    $lista["3"] = "DIMM";
    $lista["4"] = "DIMM II";
    $lista["5"] = "DDR";
    $lista["6"] = "DDR II";
    $lista["7"] = "FPM";
    return $lista;
  }

  function buscaProcessadorModelos() {
    $lista["1"] = "Cyrix 486";
    $lista["2"] = "Cyrix 586";
    $lista["3"] = "Intel 486";
    $lista["4"] = "Intel Pentium";
    $lista["5"] = "Intel Pentium MMX";
    $lista["6"] = "Intel Pentium II MMX";
    $lista["7"] = "Intel Pentium III";
    $lista["8"] = "Intel Pentium IV";
    $lista["9"] = "Intel Celerom";
    $lista["10"] = "Intel Xeon";
    $lista["11"] = "AMD 486";
    $lista["12"] = "AMD 586";
    $lista["13"] = "AMD Soekris";
    $lista["14"] = "AMD K6";
    $lista["15"] = "AMD K6-II";
    $lista["16"] = "AMD Duron";
    $lista["17"] = "AMD Athlon";
    $lista["18"] = "AMD Athlon Tunderbird";
    $lista["19"] = "AMD Athlon XP";
    $lista["20"] = "AMD Sempron";
    return $lista;
  }

  function buscaUnidades($unidade = "") {
    $unidades["1"] = "K".$unidade;
    $unidades["2"] = "M".$unidade;
    $unidades["3"] = "G".$unidade;
    $unidades["4"] = "T".$unidade;
    return $unidades;
  }

  function buscaInterfaces($servidor_id = null, $interface_padrao_id = null) {
    $query = "
      SELECT
        tab_servidores_interfaces.sei_id AS id,
        CONCAT(tab_servidores.ser_nome, ' (',tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS nome
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
      WHERE
        (tab_servidores_interfaces.sit_id = 3".($servidor_id ? " OR
         tab_servidores_interfaces.sei_id = $interface_padrao_id" : "").")".($servidor_id ? " AND
        tab_servidores_interfaces.ser_id <> $servidor_id" : "")."
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $nterfaces[$dados["id"]] = $dados["nome"];
    }
    return $nterfaces;
  }

  function verInterface($interface_id) {
    $query = "
      SELECT
        CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_nome, ', ', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip, ')') AS nome
      FROM
        tab_servidores_interfaces,
        tab_servidores
      WHERE
        tab_servidores.ser_id = tab_servidores_interfaces.ser_id AND
        tab_servidores_interfaces.sei_id = $interface_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["nome"];
    }
  }

	function buscaCondominiosServidor($servidor_id) {
	  $query = "
	    SELECT
	      tab_condominios.con_id AS condominio_id,
	      tab_servidores_interfaces.sei_nome AS interface,
	      CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, ' / ', tab_bairros.bai_nome, ')') AS condominio_nome,
	      tab_condominios_blocos.cob_nome AS bloco_nome,
	      COUNT(DISTINCT tab_clientes_pontos.clp_id) AS pontos_ativos
	    FROM
	      tab_condominios
	      INNER JOIN tab_condominios_blocos ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
	      INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
	      INNER JOIN tab_bairros ON (tab_condominios.bai_id = tab_bairros.bai_id)
	      INNER JOIN tab_cidades ON (tab_condominios.cid_id = tab_cidades.cid_id)
	      LEFT OUTER JOIN tab_clientes ON (tab_condominios_blocos.cob_id = tab_clientes.cob_id)
	      LEFT OUTER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
  			LEFT OUTER JOIN tab_clientes_pontos ON (tab_condominios_blocos.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.ass_id = tab_assinaturas.ass_id AND tab_clientes_pontos.cps_id = 1)
	    WHERE
	      tab_servidores_interfaces.ser_id = $servidor_id
	    GROUP BY
	      tab_condominios_blocos.cob_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["ordem"] = sprintf("% -15s", preg_replace("/[^a-zA-Z _]/", "", $dados["interface"])).sprintf("%05s", preg_replace("/[^0-9]/", "", $dados["interface"]));
      if ($this->permissoes["condominios"]["ver_dt"]) {
        $dados["link"] = "?m=condominios&a=ver_dt&i={$dados["condominio_id"]}";
      }
    	$condominios[] = $dados;
    }
    return $this->geraCondominiosServidor($condominios);
	}

	function geraCondominiosServidor($dados) {
		$dados = $this->ordena_multiarray($dados, array("ordem", "condominio_nome", "bloco_nome"));
		for ($i = 0; $i < count($dados); $i++) {
		  $dados[$i]["id"] = $i + 1;
		}
    $cabecalho = array ("id" => "#", "interface" => "Interface", "condominio_nome" => "Condomínio", "bloco_nome" => "Bloco", "pontos_ativos" => "Pontos Ativos");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, array("pontos_ativos" => "formataNumero"), array("20"), null, array("right"));
    return $listagem->conteudo;
	}

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
      $tamanho_chave = 0;
      reset($array);
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i = 0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = @implode(",", $chave);
        $tamanho_chave = strlen($chave) > $tamanho_chave ? strlen($chave) : $tamanho_chave;
        unset($chave);
        next($array);
      }
      reset($array);
      $array_ordenacao = array();
      while (($array_ordenacao_atual = current($array)) != false) {
      	for ($i = 0; $i < sizeof($campos); $i++) {
          $chave[] = $array_ordenacao_atual[$campos[$i]];
        }
        $chave = @implode(",", $chave);
        if (!$array_ordenacao[$chave]) {
          $array_ordenacao[sprintf("% -{$tamanho_chave}s", $chave)."_".key($array)] = key($array);
        }
        unset($chave);
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

	function buscaServicosExportaveis($servidor_id, $todos = false) {
	  $query = "
	    SELECT DISTINCT
	      tab_servidores_servicos_tipos.sst_id AS tipo_id,
	      tab_servidores_servicos_tipos.sst_servico AS servico
	    FROM
	      tab_servidores_interfaces
	      INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
	      INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos.sst_id = tab_servidores_servicos_tipos.sst_id)
	    WHERE
	      tab_servidores_interfaces.ser_id = $servidor_id AND
	      tab_servidores_servicos_tipos.sst_exportar = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($todos) {
        return $dados;
      }
    	$servicos[$dados["tipo_id"]] = $dados;
    }
    return $servicos;
	}

	##################
	### Permissões ###
	##################

	function ehExportavel() {
	  if (!$this->permissoes["servidores"]["exportar"]) return false;
    if (count($this->buscaServicosExportaveis($this->dados_item["id"])) != 1) return false;
    return true;
	}

	function ehRegeravelIPs() {
	  if (!$this->permissoes["servidores"]["regerar_ips"]) return false;
    return true;
	}

	function ehPublicavel() {
	  if (!$this->permissoes["servidores"]["publicar"]) return false;
	  if ($this->dados_item["status_id"] != 1) return false;
	  return true;
	}

	function ehAtivavel() {
	  if (!$this->permissoes["servidores"]["ativar"]) return false;
	  if ($this->dados_item["status_id"] != 2) return false;
	  return true;
  }

	function ehDesativavel() {
	  if (!$this->permissoes["servidores"]["desativar"]) return false;
	  if ($this->dados_item["status_id"] != 1) return false;
	  return true;
	}

	function ehRemovivel() {
	  if (!$this->permissoes["servidores"]["remover"]) return false;
	  return true;
	}

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("servidores.adicionar.inc.php");
      $this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), null, true, "tipo", "291px"), $this->conteudo);
      $this->conteudo = str_replace('[servidor_interface]', $this->geraSelect($this->buscaInterfaces(), null, true, "servidor_interface", "291px", "mudaInterface(this)"), $this->conteudo);
      $this->conteudo = str_replace('[sistema_operacional]', $this->geraSelect($this->buscaSistemasOperacionais(), null, true, "sistema_operacional", "291px"), $this->conteudo);
      $this->conteudo = str_replace('[processador_modelo]', $this->geraSelect($this->buscaProcessadorModelos(), null, true, "processador_modelo", "159px"), $this->conteudo);
      $this->conteudo = str_replace('[processador_unidade]', $this->geraSelect($this->buscaUnidades("Hz"), 2, false, "processador_unidade", "60px"), $this->conteudo);
      $this->conteudo = str_replace('[hd_tipo]', $this->geraSelect($this->buscaHDTipos(), null, true, "hd_tipo", "159px"), $this->conteudo);
      $this->conteudo = str_replace('[hd_unidade]', $this->geraSelect($this->buscaUnidades("B"), 2, false, "hd_unidade", "60px"), $this->conteudo);
      $this->conteudo = str_replace('[memoria_tipo]', $this->geraSelect($this->buscaMemoriaTipos(), null, true, "memoria_tipo", "159px"), $this->conteudo);
      $this->conteudo = str_replace('[memoria_unidade]', $this->geraSelect($this->buscaUnidades("B"), 2, false, "memoria_unidade", "60px"), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace('[botoes]', implode(' ', $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=adicionar", $this->conteudo);
    } else {
      //$configuracao_parametros["sistema_operacional"] = $_POST["sistema_operacional"];
      $configuracao_parametros["hd_tipo"] = $_POST["hd_tipo"];
      $configuracao_parametros["hd_tamanho"] = $_POST["hd_tamanho"] * pow(1024, $_POST["hd_unidade"]);
      $configuracao_parametros["processador_modelo"] = $_POST["processador_modelo"];
      $configuracao_parametros["processador_clock"] = $_POST["processador_clock"] * pow(1024, $_POST["processador_unidade"]);
      $configuracao_parametros["memoria_tipo"] = $_POST["memoria_tipo"];
      $configuracao_parametros["memoria_tamanho"] = $_POST["memoria_tamanho"] * pow(1024, $_POST["memoria_unidade"]);
      $configuracao_parametros["comentarios"] = $_POST["comentarios"];
      $configuracao = addslashes(serialize($configuracao_parametros));
      $_POST["sistema_operacional"] = $_POST["sistema_operacional"] ? $_POST["sistema_operacional"] : 'NULL';
      $_POST["servidor_interface"] = ($_POST["servidor_interface"]) ? $_POST["servidor_interface"] : 'NULL';
      $_POST["endereco_ip"] = ($_POST["endereco_ip"]) ? $_POST["endereco_ip"] : 'NULL';
      $query = "
        INSERT INTO
          tab_servidores (ser_nome, ser_configuracao, sso_id, set_id, sei_id, ser_endereco_ip, sep_id)
        VALUES
          ('{$_POST["nome"]}',  '$configuracao', {$_POST["sistema_operacional"]}, 2, {$_POST["servidor_interface"]}, {$_POST["endereco_ip"]}, {$_POST["tipo"]})";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			// Redireciona
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$id}");
    }
  }

  ########################################
  ### Ver Dados Cadastrais Servidores ####
  ########################################

  function ver_dc() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      // Busca dados
      $prefixo = $this->buscaPrefixoIPInterface($this->dados_item["interface_id"]);
      $unidades = $this->buscaUnidades();
      $hd_unidade = floor(log($this->dados_item["configuracao"]["hd_tamanho"], 1024));
      $processador_unidade = floor(log($this->dados_item["configuracao"]["processador_clock"], 1024));
      $memoria_unidade = floor(log($this->dados_item["configuracao"]["memoria_tamanho"], 1024));
      $hd_unidade = ($hd_unidade > 3) ? 3 : $hd_unidade;
      $processador_unidade = ($processador_unidade > 3) ? 3 : $processador_unidade;
      $memoria_unidade = ($memoria_unidade > 3) ? 3 : $memoria_unidade;
      $hd_tamanho = round($this->dados_item["configuracao"]["hd_tamanho"] / pow(1024, $hd_unidade), 2);
      $processador_clock = round($this->dados_item["configuracao"]["processador_clock"] / pow(1024, $processador_unidade), 2);
      $memoria_tamanho = round($this->dados_item["configuracao"]["memoria_tamanho"] / pow(1024, $memoria_unidade), 2);
      $hd_tipos = $this->buscaHDTipos();
      $processador_modelos = $this->buscaProcessadorModelos();
      $memoria_tipos = $this->buscaMemoriaTipos();
      // Gera tela
      $this->conteudo .= $this->carregaArquivo("servidores.ver_dc.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[tipo]', $this->dados_item["tipo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[servidor_interface]', $this->verInterface($this->dados_item["interface_id"]), $this->conteudo);
      $this->conteudo = str_replace('[endereco_ip]', $prefixo.".".$this->dados_item["endereco_ip"], $this->conteudo);
      $this->conteudo = str_replace('[sistema_operacional]', $this->dados_item["sistema_operacional_nome"], $this->conteudo);
      $this->conteudo = str_replace('[processador]', $processador_modelos[$this->dados_item["configuracao"]["processador_modelo"]].", ".$processador_clock." ".$unidades[$processador_unidade]."Hz", $this->conteudo);
      $this->conteudo = str_replace('[hd]', $hd_tipos[$this->dados_item["configuracao"]["hd_tipo"]].", ".$hd_tamanho." ".$unidades[$hd_unidade]."B", $this->conteudo);
      $this->conteudo = str_replace('[memoria]', $memoria_tipos[$this->dados_item["configuracao"]["memoria_tipo"]].", ".$memoria_tamanho." ".$unidades[$memoria_unidade]."B", $this->conteudo);
      $this->conteudo = str_replace('[comentarios]',  nl2br($this->dados_item["configuracao"]["comentarios"]), $this->conteudo);
      $this->conteudo = str_replace('[situacao]', $this->dados_item["situacao_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_di" => "Interfaces", "ver_co" => "Condomínios")), $this->conteudo);
      // Botões
      if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
      if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
		 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&retorno='.$_GET["a"].'&i='.$_GET["i"].'\'">';
		 	if ($this->ehExportavel()) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" style="width:80" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\'">';
      if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ###########################################
  ### Editar Dados Cadastrais Servidores ####
  ###########################################

  function editar_dc() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $prefixo = $this->buscaPrefixoIPInterface($this->dados_item["interface_id"]);
      if (!$_POST) {
        // Busca dados
        $hd_unidade = floor(log($this->dados_item["configuracao"]["hd_tamanho"], 1024));
        $processador_unidade = floor(log($this->dados_item["configuracao"]["processador_clock"], 1024));
        $memoria_unidade = floor(log($this->dados_item["configuracao"]["memoria_tamanho"], 1024));
        $hd_unidade = $hd_unidade > 0 ? ($hd_unidade > 3 ? 3 : $hd_unidade) : 1;
        $processador_unidade = $processador_unidade > 0 ? ($processador_unidade > 3 ? 3 : $processador_unidade) : 1;
        $memoria_unidade = $memoria_unidade > 0 ? ($memoria_unidade > 3 ? 3 : $memoria_unidade) : 1;
        $hd_tamanho = round($this->dados_item["configuracao"]["hd_tamanho"] / pow(1024, $hd_unidade), 2);
        $processador_clock = round($this->dados_item["configuracao"]["processador_clock"] / pow(1024, $processador_unidade), 2);
        $memoria_tamanho = round($this->dados_item["configuracao"]["memoria_tamanho"] / pow(1024, $memoria_unidade), 2);
        // Gera tela
        $this->conteudo .= $this->carregaArquivo("servidores.editar_dc.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"],$this->conteudo);
      	$this->conteudo = str_replace('[tipo]', $this->geraSelect($this->buscaTipos(), $this->dados_item["tipo_id"], true, "tipo", "291px", null, false), $this->conteudo);
	      $this->conteudo = str_replace('[servidor_interface]', $this->geraSelect($this->buscaInterfaces($this->dados_item["id"], $this->dados_item["interface_id"]), $this->dados_item["interface_id"], true, "servidor_interface", "291px", "mudaInterface(this)"), $this->conteudo);
		
		
        $this->conteudo = str_replace('[prefixo_ip_1]', $this->buscaPrefixoIP($prefixo, 0), $this->conteudo);
        $this->conteudo = str_replace('[prefixo_ip_2]', $this->buscaPrefixoIP($prefixo, 1), $this->conteudo);
        $this->conteudo = str_replace('[prefixo_ip_3]', $this->buscaPrefixoIP($prefixo, 2), $this->conteudo);
        $this->conteudo = str_replace('[endereco_ip]', $this->dados_item["endereco_ip"], $this->conteudo);
		
        $ipsecundario = explode(".", $this->dados_item["endereco_ip_secundario"]);

        $this->conteudo = str_replace('[prefixo_ip_secundario_1]', $ipsecundario[0], $this->conteudo);
        $this->conteudo = str_replace('[prefixo_ip_secundario_2]', $ipsecundario[1], $this->conteudo);
        $this->conteudo = str_replace('[prefixo_ip_secundario_3]', $ipsecundario[2], $this->conteudo);
        $this->conteudo = str_replace('[prefixo_ip_secundario_4]', $ipsecundario[3], $this->conteudo);		
		
		
	      $this->conteudo = str_replace('[sistema_operacional]', $this->geraSelect($this->buscaSistemasOperacionais(), $this->dados_item["sistema_operacional_id"], true, "sistema_operacional", "291px"), $this->conteudo);
	      $this->conteudo = str_replace('[processador_modelo]', $this->geraSelect($this->buscaProcessadorModelos(), $this->dados_item["configuracao"]["processador_modelo"], true, "processador_modelo", "159px"), $this->conteudo);
        $this->conteudo = str_replace('[processador_clock]', $processador_clock, $this->conteudo);
	      $this->conteudo = str_replace('[processador_unidade]', $this->geraSelect($this->buscaUnidades("Hz"), $processador_unidade, false, "processador_unidade", "60px"), $this->conteudo);
	      $this->conteudo = str_replace('[hd_tipo]', $this->geraSelect($this->buscaHDTipos(), $this->dados_item["configuracao"]["hd_tipo"], true, "hd_tipo", "159px"), $this->conteudo);
        $this->conteudo = str_replace('[hd_tamanho]', $hd_tamanho, $this->conteudo);
	      $this->conteudo = str_replace('[hd_unidade]', $this->geraSelect($this->buscaUnidades("B"), $hd_unidade, false, "hd_unidade", "60px"), $this->conteudo);
	      $this->conteudo = str_replace('[memoria_tipo]', $this->geraSelect($this->buscaMemoriaTipos(), $this->dados_item["configuracao"]["memoria_tipo"], true, "memoria_tipo", "159px"), $this->conteudo);
        $this->conteudo = str_replace('[memoria_tamanho]', $memoria_tamanho, $this->conteudo);
	      $this->conteudo = str_replace('[memoria_unidade]', $this->geraSelect($this->buscaUnidades("B"), $memoria_unidade, false, "memoria_unidade", "60px"), $this->conteudo);
        $this->conteudo = str_replace('[comentarios]', $this->dados_item["configuracao"]["comentarios"], $this->conteudo);
        $this->conteudo = str_replace('[situacao]', $this->dados_item["situacao_nome"], $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
        $this->conteudo = str_replace('[id]', $_GET["i"], $this->conteudo);
        // Ações
        $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_di" => "Interfaces", "ver_co" => "Condomínios")), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
        if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=ativar&i=' . $this->dados_item["id"] . '\';">';
        if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=desativar&i=' . $this->dados_item["id"] . '\';">';
  		 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&retorno='.$_GET["a"].'&i='.$_GET["i"].'\'">';
  		 	if ($this->ehExportavel()) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" style="width:80" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\'">';
        if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", implode(" ",$botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_dc&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
        $configuracao_parametros["hd_tipo"] = $_POST["hd_tipo"];
        $configuracao_parametros["hd_tamanho"] = $_POST["hd_tamanho"] * pow(1024, $_POST["hd_unidade"]);
        $configuracao_parametros["processador_modelo"] = $_POST["processador_modelo"];
        $configuracao_parametros["processador_clock"] = $_POST["processador_clock"] * pow(1024, $_POST["processador_unidade"]);
        $configuracao_parametros["memoria_tipo"] = $_POST["memoria_tipo"];
        $configuracao_parametros["memoria_tamanho"] = $_POST["memoria_tamanho"] * pow(1024, $_POST["memoria_unidade"]);
        $configuracao_parametros["comentarios"] = $_POST["comentarios"];
        $configuracao = addslashes(serialize($configuracao_parametros));
        $_POST["sistema_operacional"] = $_POST["sistema_operacional"] ? $_POST["sistema_operacional"] : 'NULL';
        $_POST["servidor_interface"] = $_POST["servidor_interface"] ? $_POST["servidor_interface"] : 'NULL';
		
        $_POST["endereco_ip"] = $_POST["endereco_ip"] ? $_POST["endereco_ip"] : 'NULL';
		
        $ip_secundario = 'NULL';

        if (($_POST['prefixo_ip_secundario_1'] != "") && ($_POST['prefixo_ip_secundario_2'] != "") && ($_POST['prefixo_ip_secundario_3'] != "") && ($_POST['prefixo_ip_secundario_4'] != ""))
        {
          $ip_secundario = "'" . $_POST['prefixo_ip_secundario_1'] . "." . $_POST['prefixo_ip_secundario_2'] . "." . $_POST['prefixo_ip_secundario_3'] . "." . $_POST['prefixo_ip_secundario_4'] . "'";
        }

        $_POST["endereco_ip_secundario"] = $ip_secundario;

        $query = "UPDATE tab_servidores SET ser_nome = '{$_POST["nome"]}', ser_configuracao = '$configuracao', sso_id = {$_POST["sistema_operacional"]}, sei_id = {$_POST["servidor_interface"]}, ser_endereco_ip = {$_POST["endereco_ip"]}, ser_ip_secundario = {$_POST["endereco_ip_secundario"]}  WHERE ser_id = {$_GET["i"]}";
		
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##############################################
  ### Ver Interfaces (servidores-Interfaces) ###
  ##############################################

  function ver_di() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("servidores.ver_di.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[situacao]', $this->dados_item["situacao_nome"], $this->conteudo);
      $this->conteudo = str_replace('[interfaces]', $this->buscaServidoresInterfaces($_GET["i"]), $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_di" => "Interfaces", "ver_co" => "Condomínios")), $this->conteudo);
      // Botões
		 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&retorno='.$_GET["a"].'&i='.$_GET["i"].'\'">';
		 	if ($this->ehExportavel()) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" style="width:80" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\'">';
		 	if ($this->ehRegeravelIPs()) $botoes[] = '<input type="button" name="regerar_ips" class="buttonAzulEscuroBranco11" style="width:80" value="Regerar IPs" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar_ips&i='.$_GET["i"].'\'">';
		 	if ($this->permissoes["servidores"]["clonar"]) $botoes[] = '<input type="button" name="clonar" class="buttonAzulEscuroBranco11" style="width:120" value="Clonar Interfaces" onClick="window.location=\'?m=servidores&a=clonar&i='.$_GET["i"].'\'">';
		 	if ($this->permissoes["servidores"]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" style="width:120" value="Migrar Interfaces" onClick="window.location=\'?m=servidores&a=migrar&i='.$_GET["i"].'\'">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      if ($this->permissoes["servidores_interfaces"]["adicionar"]) {
        $this->conteudo = str_replace('[servidores_interfaces_adicionar]', '<input type="button" class="buttonAzulEscuroBranco11" name="servidores_interfaces_adicionar" value="Adicionar" onClick="adicionarServidorInterface('.$this->dados_item["id"].')">', $this->conteudo);
      } else {
        $this->conteudo = str_replace('[servidores_interfaces_adicionar]', '', $this->conteudo);
      }
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

  #################################################
  ### Editar Interfaces (Servidores-Interfaces) ###
  #################################################

  function editar_di() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("servidores.editar_di.inc.php");
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[situacao]', $this->dados_item["situacao_nome"], $this->conteudo);
      $this->conteudo = str_replace('[interfaces]', $this->buscaServidoresInterfaces($_GET["i"]), $this->conteudo);
      $this->conteudo = str_replace('[servidores_interfaces_adicionar]', ($this->permissoes["servidores_interfaces"]["adicionar"]) ? '<input type="button" class="buttonAzulEscuroBranco11" name="servidores_interfaces_adicionar" value="Adicionar" onClick="adicionarServidorInterface('.$this->dados_item["id"].')">' : "", $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      // Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_di" => "Interfaces", "ver_co" => "Condomínios")), $this->conteudo);
      // Botões
		 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&retorno='.$_GET["a"].'&i='.$_GET["i"].'\'">';
		 	if ($this->ehExportavel()) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" style="width:80" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\'">';
		 	if ($this->ehRegeravelIPs()) $botoes[] = '<input type="button" name="regerar_ips" class="buttonAzulEscuroBranco11" style="width:80" value="Regerar IPs" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=regerar_ips&i='.$_GET["i"].'\'">';
		 	if ($this->permissoes["servidores"]["clonar"]) $botoes[] = '<input type="button" name="clonar" class="buttonAzulEscuroBranco11" style="width:120" value="Clonar Interfaces" onClick="window.location=\'?m=servidores&a=clonar&i='.$_GET["i"].'\'">';
		 	if ($this->permissoes["servidores"]["migrar"]) $botoes[] = '<input type="button" name="migrar" class="buttonAzulEscuroBranco11" style="width:120" value="Migrar Interfaces" onClick="window.location=\'?m=servidores&a=migrar&i='.$_GET["i"].'\'">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
      $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=editar_di&i={$this->dados_item["id"]}", $this->conteudo);
    } else {
      header("Location: ./?m=".$this->dados_modulo["nome"]);
    }
  }

	#######################
	### Ver Condomínios ###
	#######################

	function ver_co() {
		$this->buscaDadosItem($_GET["i"]);
	  if ($this->dados_item) {
     	$this->conteudo .= $this->carregaArquivo("servidores.ver_co.inc.php");
     	// Ações
      $this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver_dc" => "Dados Cadastrais", "ver_di" => "Interfaces", "ver_co" => "Condomínios")), $this->conteudo);
     	// Dados
    	$this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[condominios]', $this->buscaCondominiosServidor($_GET["i"]), $this->conteudo);
			// Botões
		 	if ($this->ehPublicavel()) $botoes[] = '<input type="button" name="publicar" class="buttonAzulEscuroBranco11" style="width:80" value="Publicar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=publicar&retorno='.$_GET["a"].'&i='.$_GET["i"].'\'">';
		 	if ($this->ehExportavel()) $botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" style="width:80" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar&i='.$_GET["i"].'\'">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
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
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar\'">';
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
    // Nome
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "tab_servidores.ser_nome LIKE '%[valor]%'");
    // Endereço IP
    $itens[] = array ("tipo" => "input", "nome" => "endereco_ip", "descricao" => "Endereço IP", "valor" => "", "where" => "CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) LIKE '%[valor]%'");
    // Tipo
    $tipos = $this->buscaTipos();
    foreach ($tipos as $id => $nome) {
      $select_tipos[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "tipo", "descricao" => "Tipo", "valor" => $select_tipos, "where" => "tab_servidores.sep_id = [valor]");
    // Situação
    $situacoes = $this->buscaSituacoes();
    $situacoes[] = array("id" => "-1", "nome" => "Não Operante", "where" => "(tab_servidores.seu_id IN (2, 3))");
    $itens[] = array ("tipo" => "select", "nome" => "situacao", "descricao" => "Situação", "valor" => $situacoes, "where" => "tab_servidores.seu_id = [valor]");
    // Sistemas
    $sistemas = $this->buscaSistemasOperacionais();
    foreach ($sistemas as $id => $nome) {
      $select_sistemas[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema Operacional", "valor" => $select_sistemas, "where" => "tab_servidores.sso_id = [valor]");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_statuss[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_statuss, "where" => "tab_servidores.set_id = [valor]");
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
        tab_servidores.ser_id AS id,
        tab_servidores.ser_nome AS nome,
        CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS endereco_ip,
        tab_servidores.sep_id AS tipo_id,
        tab_servidores.set_id AS status_id
      FROM
        tab_servidores
        LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
        INNER JOIN tab_servidores_situacoes ON (tab_servidores_situacoes.seu_id = tab_servidores.seu_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
        tab_servidores.ser_nome ASC
      LIMIT
        $limit_inicio,
        $limit_fim";
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $tipos = $this->buscaTipos();
    $status = $this->buscaStatus();
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"]) $dados["link"] = "?m=servidores&a=ver_dc&i={$dados["id"]}";
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver_dc"]) $dados["links"][] = "<a href=\"./?m=servidores&a=ver_dc&i={$dados["id"]}\" class=\"txtAzulEscuro11\">DC</a>";
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver_di"]) $dados["links"][] = "<a href=\"./?m=servidores&a=ver_di&i={$dados["id"]}\" class=\"txtAzulEscuro11\">DI</a>";
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver_co"]) $dados["links"][] = "<a href=\"./?m=servidores&a=ver_co&i={$dados["id"]}\" class=\"txtAzulEscuro11\">CO</a>";
      $dados["links"] = implode(" ", $dados["links"]);
      $dados["condominios"] = $this->buscaCondominiosLigados($dados["id"], '2');
//      $dados["endereco_ip"] = $this->formataIP($dados["endereco_ip"]);
      $dados["pontos"] = $this->buscaPontosLigados($dados["id"], '1');
      $dados["tipo"] = $tipos[$dados["tipo_id"]];
      $dados["status"] = $status[$dados["status_id"]];
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function formataIP($ip) {
    $ip = explode(".", $ip);
    $conteudo[] = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr class=\"txtAzulEscuro11\">";
    foreach ($ip as $valor) {
      if (count($conteudo) > 1) {
        $conteudo[] = "<td style=\"text-align:center;width:5px;\">.</td>";
      }
      $conteudo[] = "<td style=\"text-align:center;width:20px;\">$valor</td>";
    }
    $conteudo[] = "</tr></table>";
    return implode($conteudo);
  }

  function geraListagem($itens) {
    $cabecalho = array("contador" => "#", "nome" => "Nome", "endereco_ip" => "Endereço IP", "condominios" => "Condomínios", "pontos" => "Pontos Ativos", "tipo" => "Tipo", "status" => "Status", "links" => "Links");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array("pontos" => "formataNumero", "condominios" => "formataNumero"), array("20"), null, array("right"));
    return $listagem;
  }

  function buscaCondominiosLigados($servidor_id, $status = '%') {
    $servidores_filhos = $this->buscaServidoresFilhos($servidor_id);
    if (!in_array($servidor_id, $servidores_filhos)) $servidores_filhos[] = $servidor_id;
    $servidores = implode(",", $servidores_filhos);
    $query = "
      SELECT
        COUNT(DISTINCT tab_condominios_blocos.con_id) AS itens
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_condominios_blocos ON (tab_servidores_interfaces.sei_id = tab_condominios_blocos.sei_id)
        INNER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
      WHERE
        tab_condominios.cos_id LIKE '$status' AND
        tab_servidores_interfaces.ser_id IN ($servidores)";
    return $this->bancodedados->resultado($this->bancodedados->query($query), 0, "itens");
  }

  function buscaPontosLigados($servidor, $status = '%') {
    $servidores_filhos = $this->buscaServidoresFilhos($servidor);
    if (!in_array($servidor, $servidores_filhos)) $servidores_filhos[] = $servidor;
    $servidor = implode(",", $servidores_filhos);
    $query = "
      SELECT
        COUNT(DISTINCT tab_clientes_pontos.clp_id) AS itens
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
      WHERE
        tab_clientes_pontos.cps_id LIKE '$status' AND
        tab_servidores_interfaces.ser_id IN ($servidor)";
    $query = $this->bancodedados->query($query);
    $pontos = $this->bancodedados->resultado($query, 0, "itens");
    
    $query = "
      SELECT
        COUNT(cau_id) AS itens
      FROM
        tab_clientes_autenticacoes
      WHERE
        cas_id LIKE '$status' AND
        ser_id IN ($servidor)";
    $query = $this->bancodedados->query($query);
    $autenticacoes = $this->bancodedados->resultado($query, 0, "itens");
    
    return $pontos + $autenticacoes;
  }

  function buscaServidoresFilhos($servidor_id) {
    $query = "
      SELECT
        tab_servidores.ser_id AS id
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores ON (tab_servidores_interfaces.sei_id = tab_servidores.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    $servidores = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if (!in_array($dados["id"], $servidores)) {
        $servidores[] = $dados["id"];
        $servidores = funcoes::mescla_arrays($servidores, $this->buscaServidoresFilhos($dados["id"]));
      }
    }
    return $servidores;
  }

  ##############
  ### Ativar ###
  ##############

  function ativar() {
    if ($_GET["i"]) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores.ativar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=ativar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_servidores SET set_id = 1 WHERE ser_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  #################
  ### Desativar ###
  #################

  function desativar() {
    if ($_GET["i"]) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores.desativar.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=desativar&i={$_GET["i"]}", $this->conteudo);
      } else {
        $query = "UPDATE tab_servidores SET set_id = 2 WHERE ser_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ###############
  ### Remover ###
  ###############

  function remover() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
			if (($erros = $this->buscaErros($_GET["i"])) || !$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores.remover.inc.php");
				$this->conteudo = str_replace('[mensagem]', $erros ? "Foram encontratos os seguintes erros:<br><br>$erros<br><br>Não é possível continuar com a remoção." : "Tem certeza que deseja continuar com a remoção deste servidor?", $this->conteudo);
				if (!$erros) $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=remover&i={$_GET["i"]}", $this->conteudo);
      } else {
			  // Remove Vínculo c/ Pontos
			  $query = "UPDATE tab_clientes_pontos INNER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id) SET tab_clientes_pontos.sei_id = NULL WHERE tab_servidores_interfaces.ser_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
			  // Remove Vínculo c/ Autenticacoes
				$query = "UPDATE tab_clientes_autenticacoes INNER JOIN tab_servidores_interfaces ON (tab_clientes_autenticacoes.sei_id = tab_servidores_interfaces.sei_id) SET tab_clientes_autenticacoes.sei_id = NULL WHERE tab_servidores_interfaces.ser_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Vínculo c/ Rede / Interface
			  $query = "UPDATE tab_redes INNER JOIN tab_servidores_interfaces ON (tab_redes.sei_id = tab_servidores_interfaces.sei_id) SET tab_redes.sei_id = NULL WHERE tab_servidores_interfaces.ser_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Vínculo c/ Rede / Rede
				$query = "UPDATE tab_redes SET ser_id = NULL WHERE ser_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
				// Remove Serviços
				$query = "DELETE tab_servidores_servicos FROM tab_servidores_servicos INNER JOIN tab_servidores_interfaces ON (tab_servidores_servicos.sei_id = tab_servidores_interfaces.sei_id) WHERE tab_servidores_interfaces.ser_id = {$_GET["i"]}";
				$this->bancodedados->query($query);
      	// Remove Interfaces
        $query = "DELETE FROM tab_servidores_interfaces WHERE ser_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
				// Remove Servidor
        $query = "DELETE FROM tab_servidores WHERE ser_id = {$_GET["i"]}";
        $this->bancodedados->query($query);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_dc&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  function buscaErros($servidor_id) {
  	// Servidores
    $query = "
      SELECT
        tab_servidores_interfaces.sei_nome AS interface_nome,
        tab_servidores.ser_nome AS servidor_nome
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id
      ORDER BY
        interface_nome,
        servidor_nome";
		$query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $erros[] = "&raquo;&nbsp; Interface <b>{$dados["interface_nome"]}</b> vinculada ao Servidor <b>{$dados["servidor_nome"]}</b>";
	  }
	  // LInks
    $query = "
      SELECT
        tab_servidores_interfaces.sei_nome AS interface_nome,
        tab_servidores_links.sel_nome AS link_nome
      FROM
        tab_servidores_links
        INNER JOIN tab_servidores_interfaces ON (tab_servidores_links.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id
      ORDER BY
        interface_nome,
        link_nome";
		$query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $erros[] = "&raquo;&nbsp; Interface <b>{$dados["interface_nome"]}</b> vinculada ao Link <b>{$dados["link_nome"]}</b>";
	  }
	  // Condomínios Blocos
    $query = "
      SELECT
        tab_servidores_interfaces.sei_nome AS interface_nome,
        tab_condominios.con_nome AS condominio_nome
      FROM
        tab_condominios
        INNER JOIN tab_condominios_blocos ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id
      ORDER BY
        interface_nome,
        condominio_nome";
		$query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $erros[] = "&raquo;&nbsp; Interface <b>{$dados["interface_nome"]}</b> vinculada ao Condomínio <b>{$dados["condominio_nome"]}</b>";
	  }
	  // Pontos Condominio
    $query = "
      SELECT
        tab_servidores_interfaces.sei_nome AS interface_nome,
        COUNT(tab_clientes_pontos.clp_id) as quantidade,
        tab_condominios.con_nome AS condominio,
        tab_condominios_blocos.cob_nome AS bloco
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.cps_id != 3 AND
        tab_planos.pls_id IN (1, 2)
      GROUP BY
        tab_condominios_blocos.cob_id";
		$query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $erros[] = "&raquo;&nbsp; Interface <b>{$dados["interface_nome"]}</b> vinculada a <b>{$dados["quantidade"]}</b> ponto(s) no condomínio <b>{$dados["condominio"]} &raquo; Bloco {$dados["bloco"]}</b>";
	  }
	  // Pontos Cliente
    $query = "
      SELECT
        tab_servidores_interfaces.sei_nome AS interface_nome,
        COUNT(tab_clientes_pontos.clp_id) as quantidade,
        tab_clientes.cli_id AS cliente_id,
        tab_clientes.cli_nome AS cliente_nome
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id AND
        tab_clientes_pontos.cps_id != 3 AND
        tab_planos.pls_id IN (3, 5, 36)
      GROUP BY
        tab_clientes.cli_id";
		$query = $this->bancodedados->query($query);
	  while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    $erros[] = "&raquo;&nbsp; Interface <b>{$dados["interface_nome"]}</b> vinculada a <b>{$dados["quantidade"]}</b> ponto(s) no cliente <b>[{$dados["cliente_id"]}] {$dados["cliente_nome"]}</b>";
	  }
	  return @implode("<br>", $erros);
  }
  
  function buscaPeriodoPublicacao($servidor_id) {
  	$query = "
  	  SELECT
  	    UNIX_TIMESTAMP() - UNIX_TIMESTAMP(ser_data_hora_publicacao_inicio) AS segundos
  	  FROM
  	    tab_servidores
  	  WHERE
  	    ser_id = $servidor_id AND
  	    ser_publicando = 1";
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
	  if ($dados) {
	  	return $this->formataDuracao(intval($dados["segundos"], 10));
	  }
  }

  ################
  ### Publicar ###
  ################

  function publicar() {
  	$this->buscaDadosItem($_GET["i"]);
	  if ($this->ehPublicavel() && !$_POST && !$_GET["sucesso"] && !$_GET["erro"]) {
	  	if ($this->permissoes["servidores"]["publicar_plus"]) {
	  		$periodo_publicacao = $this->buscaPeriodoPublicacao($_GET["i"]);
	  	}
    	$this->conteudo .= $this->carregaArquivo("servidores.publicar.inc.php");
   	  $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
    	if ($periodo_publicacao) {
				$this->conteudo = str_replace('[mensagem]', "Este servidor está em processo de publicação há <b>$periodo_publicacao</b>.<br>Você deseja publicá-lo mesmo assim?", $this->conteudo);
    		$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}&debug={$_GET["debug"]}&forcar=1", $this->conteudo);
    	} else {
				$this->conteudo = str_replace('[mensagem]', "Você deseja realmente <b>publicar</b> este servidor?", $this->conteudo);
    		$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}&debug={$_GET["debug"]}&forcar=0", $this->conteudo);
    	}
			$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
	  } elseif (!$_POST) {
			if ($_GET["sucesso"]) {
				$this->conteudo .= "<script type=\"text/javascript\">alert('Servidor publicado com sucesso!')</script>";
				$this->conteudo .= "<script type=\"text/javascript\">window.location = './?m={$this->dados_modulo["nome"]}&a={$_GET["retorno"]}&i={$_GET["i"]}'</script>";
			} else {
	    	$this->conteudo .= $this->carregaArquivo("servidores.publicar.inc.php");
  			$acoes  = '<td bgcolor="#505050"><strong>&nbsp;Publicar&nbsp;</strong></td>';
      	$this->conteudo = str_replace('[acoes]', $acoes, $this->conteudo);
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[mensagem]', "{$_GET["erro"]}<br><br>Clique em Confirmar para tentar novamente.", $this->conteudo);
				$this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}&debug={$_GET["debug"]}", $this->conteudo);
			}
		} else {
	  	if (!$this->permissoes["servidores"]["publicar_plus"]) {
	  		$_GET["forcar"] = 0;
	  	}
		
			if ($this->dados_item["publicacao_versao"] == 2) {
				$publicacao = controle::instanciaObjeto("clientes_autenticacoes.publicacao2", $this->bancodedados);
				$erro = $publicacao->publicarConcentrador($this->dados_item["id"]);
				if ($erro) {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=$erro&retorno={$_GET["retorno"]}");
					die();
				}			
			}
		
			$erro = $this->publicarServicos($this->dados_item["id"], null, null, $_GET["forcar"]);
			if ($erro) {
	   		header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&erro=$erro&retorno={$_GET["retorno"]}");
			} elseif (!$_GET["debug"]) {
    		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
    		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], "publicar", $_GET["i"]);
	   		header("Location: ./?m={$this->dados_modulo["nome"]}&a=publicar&i={$_GET["i"]}&retorno={$_GET["retorno"]}&sucesso=1");
			} else {
			  die();
			}
		}
  }

  ################
  ### Exportar ###
  ################

  function exportar() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $servico = $this->buscaServicosExportaveis($this->dados_item["id"], true);
      header("Content-type: application/text;");
      header("Content-Disposition: attachment; filename={$servico["servico"]}.txt;");
   		$publicacao = controle::instanciaObjeto("servidores_servicos.{$servico["servico"]}", $this->bancodedados);
      print $publicacao->exportar($this->dados_item["id"]);
      exit();
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=listar");
    }
  }

  ##################
  ### Ver Árvore ###
  ##################

  function estrutura() {
    $this->conteudo .= $this->carregaArquivo("servidores.estrutura.inc.php");
    $this->conteudo = str_replace('[tree_items]', $this->geraEstrutura("NULL"), $this->conteudo);
  }

  function geraEstrutura($sei_id, $tem_pai = 0) {
  	$servidores = $this->buscaServidorEstrutura($sei_id);
    if ($servidores) {
      foreach ($servidores as $servidor) {
        $interfaces = $this->buscaInterfacesEstrutura($servidor["id"]);
        $link = "'?m=servidores&a=ver_dc&i={$servidor["id"]}'";
        $hint = array();
        $hint[] = "&raquo; Quantidade de Interfaces: ".sizeof($interfaces);
        $hint[] = "&raquo; Status: ".$servidor["status_nome"];
        //$hint[] = "Aqui poderiam ter comentários.";
        $nome = "<span onmouseover=\"hintShow(\\'".implode("<br>", $hint)."\')\" onmouseout=\"hintHide()\">{$servidor["nome"]}".(($servidor["ip"]) ? " ({$servidor["ip"]})" : "")."</span>";
        $tree_items .= "['$nome', $link, '".(($tem_pai) ? "s0" : "sn")."', \n";
        if ($interfaces) {
          foreach ($interfaces as $interface) {
            $link = 'null';
            unset($hint);
            $hint[] = "&raquo; Tipo: {$interface["sit_nome"]}";
            //$hint[] = "Aqui poderiam ter comentários.";
            if ($interface["sit_id"] == 1 || $interface["sit_id"] == 6) {
              // Ligação Servidor/Clientes
              $quantidade_pontos = $this->buscaQuantidadePontos($interface["sei_id"]);
              if ($quantidade_pontos) $hint[] = "&raquo; Quantidade de Pontos: $quantidade_pontos";
            } elseif ($interface["sit_id"] == 2) {
              // Ligação Servidor/Link
              $interface_link = $this->buscaLink($interface["sei_id"]);
              if ($interface_link) $hint[] = "&raquo; Link: $interface_link";
            } elseif ($interface["sit_id"] == 3) {
              // Ligação Servidor/Servidor
              $servidores_interfaces = $this->buscaServidorEstrutura($interface["sei_id"]);
              if (sizeof($servidores_interfaces)) $hint[] = "&raquo; Quantidade de Servidores: ".sizeof($servidores_interfaces);
            }
            $hint[] = "&raquo; Status: ".$interface["sia_nome"];
            $nome = "<span onmouseover=\"hintShow(\\'".implode("<br>", $hint)."\')\" onmouseout=\"hintHide()\">{$interface["sei_nome"]}/{$interface["sit_sigla"]} ({$interface["ip"]})</span>";
            $tree_items .= "['$nome', $link, 'i{$interface["sit_id"]}', \n";
            if ($interface["sit_id"] == 3) {
              // Ligação Servidor/Servidor
              $tree_items .= $this->geraEstrutura($interface["sei_id"], 1);
            }
            $tree_items .= "],\n";
          }
        }
        $tree_items .= "],\n";
      }
    }
    return $tree_items;
  }

  function buscaServidorEstrutura($interface_id) {
    $interface_id = ($interface_id == "NULL") ? "IS NULL" : "= $interface_id";
    $query = "
        SELECT
          tab_servidores.ser_id AS id,
          CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip) AS ip,
          tab_servidores.ser_nome AS nome,
          tab_servidores_status.set_nome AS status_nome
        FROM
          tab_servidores
          INNER JOIN tab_servidores_status ON (tab_servidores.set_id = tab_servidores_status.set_id)
          LEFT OUTER JOIN tab_servidores_interfaces ON (tab_servidores_interfaces.sei_id = tab_servidores.sei_id)
        WHERE
          tab_servidores.sei_id $interface_id
        ORDER BY
          nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados;
    }
    return $servidores;
  }

  function buscaInterfacesEstrutura($servidor_id) {
    $query = "
      SELECT
        tab_servidores_interfaces.sei_id AS sei_id,
        tab_servidores_interfaces.sei_nome AS sei_nome,
        tab_servidores_interfaces.ser_id AS ser_id,
        CONCAT(tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores_interfaces.sei_endereco_ip) AS ip,
        tab_servidores_interfaces_tipos.sit_id AS sit_id,
        tab_servidores_interfaces_tipos.sit_sigla AS sit_sigla,
        tab_servidores_interfaces_tipos.sit_nome AS sit_nome,
        tab_servidores_interfaces_status.sia_nome AS sia_nome
      FROM
        tab_servidores_interfaces
        INNER JOIN tab_servidores_interfaces_tipos ON (tab_servidores_interfaces.sit_id = tab_servidores_interfaces_tipos.sit_id)
        INNER JOIN tab_servidores_interfaces_status ON (tab_servidores_interfaces.sia_id = tab_servidores_interfaces_status.sia_id)
      WHERE
        tab_servidores_interfaces.ser_id = $servidor_id
      ORDER BY
        sei_nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $interfaces[] = $dados;
    }
    return $interfaces;
  }

  function buscaQuantidadePontos($interface_id) {
    $query = "
      SELECT
        COUNT(clp_id) AS pontos
      FROM
        tab_clientes_pontos
      WHERE
        sei_id = $interface_id AND
        cps_id = 1";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query, 0, "pontos"), 10);
  }

  function buscaLink($interface_id) {
    $query = "
      SELECT
        sel_nome as nome
      FROM
        tab_servidores_links
      WHERE
        sei_id = $interface_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["nome"];
    }
  }
  
  #########################
  ### Ajusta Velocidade ###
  #########################
  
  function ajustaVelocidade($servidor_id, $ajuste = null) {
  	$servicos = $this->buscaServicosInstalados($servidor_id);
    if ($servicos) {
      foreach ($servicos as $servico) {
        if (!isset($this->{$servico["servico"]})) {
          $this->{$servico["servico"]} = controle::instanciaObjeto("servidores_servicos.{$servico["servico"]}", $this->bancodedados);
        }
        if (method_exists($this->{$servico["servico"]}, "ajustaVelocidades")) {
          if ($this->{$servico["servico"]}->ajustaVelocidades($servidor_id, $ajuste)) {
            if ($ajuste) {
              $query = "UPDATE tab_servidores SET ser_ajustado = 1 WHERE ser_id = $servidor_id";
            } else {
              $query = "UPDATE tab_servidores SET ser_ajustado = 0 WHERE ser_id = $servidor_id";
            }
            $this->bancodedados->query($query);
          }
        }
      }
    }
  }

  ##############################
  ### Publicação de Serviços ###
  ##############################

  function publicarServicos($servidor_id, $comando = null, $ponto_id = null, $forcar = false) {
    if ($_GET["debug"] || $forcar || $this->ehServidorPublicavel($servidor_id)) {
    	$servicos = $this->buscaServicosInstalados($servidor_id);
      if ($servicos) {
        // Define servidor como Publicando
        $query = "UPDATE tab_servidores SET ser_publicando = 1, ser_data_hora_publicacao = NOW(), ser_data_hora_publicacao_inicio = IF(ser_data_hora_publicacao_inicio <= ser_data_hora_publicacao_termino OR ser_data_hora_publicacao_inicio IS NULL, NOW(), ser_data_hora_publicacao_inicio) WHERE ser_id = $servidor_id";
        $this->bancodedados->query($query);
        // Publica Serviços
        foreach ($servicos as $servico) {
        	$retorno = $this->publicaServico($servico["servico"], $comando ? $comando : $servico["comando"], $servidor_id, $ponto_id);
          if ($retorno) {
		        // Define servidor como não Publicando
		        $query = "UPDATE tab_servidores SET ser_publicando = 0 WHERE ser_id = $servidor_id";
		        $this->bancodedados->query($query);
		        // Abre chamado
		        $data_hora_erro = $this->servidorErroPublicacao($servidor_id);
           	if ($data_hora_erro) {
             	$chamado = $this->abreChamadoAdmSistemas($servidor_id, "<b>{$servico["nome"]}:</b> $retorno", $data_hora_erro);
           	}
           	return "<b>{$servico["nome"]}:</b> $retorno".($chamado ? "<br><br>Este servidor está aguardando publicação desde {$data_hora_erro}.<br>Chamado aberto para Administradores de Sistema: <b>$chamado</b>" : "");
          }
        }
        // Define Servidor e Pontos como Publicados
        $this->defineServidorPublicado($servidor_id);
        // Finaliza Chamado Aberto p/ o Servidor
        $this->finalizaChamadoServidor($servidor_id);
      } else {
        return "Nenhum serviço disponível para publicação";
      }
    } else {
      return "Publicação do servidor em processo. Aguarde";
    }
  }

  function liberarPonto($servidor_id, $mac) {
  	$servicos = $this->buscaServicosInstalados($servidor_id);
    if ($servicos) {
      foreach ($servicos as $servico) {
        if (!$this->{$servico["servico"]}) {
          $this->{$servico["servico"]} = controle::instanciaObjeto("servidores_servicos.{$servico["servico"]}", $this->bancodedados);
        }
        if (method_exists($this->{$servico["servico"]}, "liberarPonto")) {
          return $this->{$servico["servico"]}->liberarPonto($servidor_id, $mac);
        }
      }
    }
  }

  function defineServidorPublicado($servidor_id) {
    // Atualiza Data/Hora publicação
    $query = "UPDATE tab_servidores SET ser_publicado = 1, ser_publicando = 0, ser_data_hora_publicacao_termino = NOW() WHERE ser_id = $servidor_id";
    $this->bancodedados->query($query);
    // Seta Pontos como Publicados
    $query = "UPDATE tab_clientes_pontos INNER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id) SET clp_publicado = 1 WHERE ser_id = $servidor_id";
    $this->bancodedados->query($query);
  }

  function servidorErroPublicacao($servidor_id) {
    $query = "
      SELECT
        DATE_FORMAT(ser_data_hora_publicacao_inicio, '%d/%m/%Y (%H:%i)') AS data_hora_inicio
      FROM
        tab_servidores
      WHERE
        ser_id = $servidor_id AND
        ser_data_hora_publicacao_inicio <= DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["data_hora_inicio"];
    }
  }

  function abreChamadoAdmSistemas($servidor_id, $erro, $data_hora_erro) {
    // Verifica se já existe chamado aberto p/ o servidor
    $query = "
      SELECT
        tab_chamados.cha_codigo AS codigo
      FROM
        tab_servidores
        INNER JOIN tab_chamados ON (tab_servidores.cha_id = tab_chamados.cha_id)
      WHERE
        tab_servidores.ser_id = $servidor_id AND
        tab_chamados.chs_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["codigo"];
    }
    // Busca Dados do Servidor
    $this->buscaDadosItem($servidor_id);
    $prefixo = $this->buscaPrefixoIPInterface($this->dados_item["interface_id"]);
    // Gera Dados Chamado
    $dados_chamado["solicitante"] = "Sistema (Intranet)";
    $dados_chamado["categoria"] = 107;
    $dados_chamado["prioridade"] = 4;
    $dados_chamado["tipo"] = 5;
    $dados_chamado["conteudo"][] = 'O seguinte servidor está aguardando publicação desde [n]'.$data_hora_erro.'[/n]:';
    $dados_chamado["conteudo"][] = '[d][n]Nome:[/n] '.$this->dados_item["nome"];
    $dados_chamado["conteudo"][] = '[n]IP:[/n] '.$prefixo.'.'.$this->dados_item["endereco_ip"];
    $dados_chamado["conteudo"][] = '[n]Erro:[/n] '.$erro.'[/d]';
    $dados_chamado["conteudo"] = implode("\n", $dados_chamado["conteudo"]);
    // Instancia Classe Chamados
    $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
    $chamado_id = $chamados->adicionarChamado(null, null, $dados_chamado, "NULL");
    // Registra chamado no servidor
    $query = "UPDATE tab_servidores SET cha_id = $chamado_id WHERE ser_id = $servidor_id";
    $this->bancodedados->query($query);
    // Retorna Codigo
    return $chamados->buscaChamadoCodigo($chamado_id);
  }

  function finalizaChamadoServidor($servidor_id) {
    $query = "
      SELECT
        tab_chamados.cha_id AS chamado_id
      FROM
        tab_servidores
        INNER JOIN tab_chamados ON (tab_servidores.cha_id = tab_chamados.cha_id)
      WHERE
        tab_servidores.ser_id = $servidor_id AND
        tab_chamados.chs_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
      $dados_chamado = $chamados->buscaDadosChamado($dados["chamado_id"]);
      /// Registra Andamento = 100%
      if ($dados_chamado["andamento"] != 100) {
        $chamados->registraEtapa($dados_chamado, 15, array("andamento" => "100"), "NULL");
      }
      // Registra Comentário
      $chamados->registraEtapa($dados_chamado, 5, array("conteudo" => "Ponto publicado com sucesso"), "NULL");
      // Registra Etapa Fechamento
      $chamados->registraEtapa($dados_chamado, 6, null, "NULL");
    }
    // Desassocia Chamado/Servidor
    $query = "UPDATE tab_servidores SET cha_id = NULL WHERE ser_id = $servidor_id";
    $this->bancodedados->query($query);
  }

  function publicaServico($servico, $comando, $servidor_id, $ponto_id = null) {
    if (!$this->$servico) {
      $this->$servico = controle::instanciaObjeto("servidores_servicos.$servico", $this->bancodedados);
    }
    return $this->$servico->$comando($servidor_id, $ponto_id);
  }

  function ehServidorPublicavel($servidor_id) {
    $query = "
			SELECT
		 		ser_publicando AS publicando
			FROM
			  tab_servidores
			WHERE
		 		ser_id = $servidor_id";
    $query = $this->bancodedados->query($query);
    return ($this->bancodedados->resultado($query, 0, "publicando")) ? false : true;
  }

  function buscaServicosInstalados($servidor_id) {
    $query = "
			SELECT DISTINCT
				tab_servidores_servicos_tipos.sst_nome AS nome,
				tab_servidores_servicos_tipos.sst_servico AS servico,
				tab_servidores_servicos_tipos.sst_comando AS comando
			FROM
			  tab_servidores_interfaces
			  INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
			  INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos.sst_id = tab_servidores_servicos_tipos.sst_id)
			WHERE
		 		tab_servidores_interfaces.ser_id = $servidor_id AND
		 		tab_servidores_interfaces.sia_id = 1 AND
		 		tab_servidores_servicos_tipos.sst_servico IS NOT NULL AND
		 		tab_servidores_servicos_tipos.sst_comando IS NOT NULL AND
		 		tab_servidores_servicos.sss_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["servico"] && $dados["comando"]) {
        $servicos[] = $dados;
      }
    }
    return $servicos;
  }

  ####################
  ### Regerar IP's ###
  ####################

  function regerar_ips() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("servidores.regerar_ips.inc.php");
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        $this->conteudo = str_replace('[botoes]', @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=regerar_ips&i={$this->dados_item["id"]}", $this->conteudo);
      } else {
	      $this->regerarIPs($_GET["i"]);
        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_di&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar");
    }
  }

  function regerarIPs($servidor_id = null, $interface_id = null) {
    // Certifica que todos os pontos estão associados às respectivas interfaces
    $query = "
      UPDATE
        tab_clientes
        INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes_pontos ON (tab_assinaturas.ass_id = tab_clientes_pontos.ass_id)
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
      SET
        tab_clientes_pontos.sei_id = tab_condominios_blocos.sei_id
      WHERE".($servidor_id ? "
        tab_servidores_interfaces.ser_id = $servidor_id" : "
        tab_servidores_interfaces.sei_id = $interface_id")." AND
        tab_clientes_pontos.cps_id IN (1, 2) AND
        tab_planos.pls_id IN (1, 2)";
    $this->bancodedados->query($query);
    // Regera IPs
    if ($servidor_id && ($interfaces = $this->buscaInterfacesServidor($servidor_id, 1))) {
	    // Busca Interfaces do Condomínio
	    $regra = 1;
    	// Instancia ClientesPontos
      foreach ($interfaces as $interface_id) {
      	$this->regeraIPsInterface($interface_id, $regra);
      }
    } elseif ($interface_id) {
    	$this->regeraIPsInterface($interface_id);
    }
  }

  function regeraIPsInterface($interface_id, &$regra = null) {
		// Limpa IPs Pontos
		$query = "
		  UPDATE
		    tab_clientes_pontos 
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
		  SET
		    tab_clientes_pontos.clp_ip = NULL
	    WHERE 
	      tab_clientes_pontos.sei_id = $interface_id AND
        tab_planos.pls_id NOT IN (3, 5, 33, 34, 35, 36)";
    $this->bancodedados->query($query);
		// Instancia Classe clientes_pontos
  	$clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);
		// Busca Pontos da Interface
		$pontos = $this->buscaIntervacePontos($interface_id);
    if ($pontos) {
      foreach ($pontos as $ponto) {
				$ip = $clientes_pontos->buscaIPLivre($interface_id, $ponto["assinatura_id"], $ponto["id"]);
				$ip = $ip ? "'$ip'" : "NULL";
        // Atualiza IP dos Pontos
        $query = "UPDATE tab_clientes_pontos SET ".($regra ? "clp_regra = $regra, " : "")."clp_ip = $ip, clp_publicado = 0 WHERE clp_id = {$ponto["id"]}";
        $this->bancodedados->query($query);
        if ($regra) $regra++;
      }
    }
    // Atualiza Data Alteraçao do Serviço
    $query = "UPDATE tab_servidores_servicos SET ses_data_alteracao = NOW() WHERE sei_id = $interface_id AND sst_id IN (1, 2, 3, 8)";
    $this->bancodedados->query($query);
  }

  function buscaInterfacesServidor($servidor_id, $tipo_id) {
    $query = "
      SELECT
        sei_id AS id
      FROM
        tab_servidores_interfaces
      WHERE
        ser_id = $servidor_id AND
        sit_id = $tipo_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $interfaces[] = $dados["id"];
    }
    return $interfaces;
  }

  function buscaIntervacePontos($interface_id) {
    $query = "
      SELECT DISTINCT
        tab_clientes_pontos.clp_id AS id,
        tab_clientes_pontos.ass_id AS assinatura_id
      FROM
        tab_clientes_pontos
        INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
      WHERE
        tab_clientes_pontos.sei_id = $interface_id AND
        tab_clientes_pontos.cps_id IN (1, 2) AND
        tab_planos.pls_id <> 3
      ORDER BY
        tab_condominios_blocos.cob_nome,
        SUBSTRING(tab_clientes.cli_complemento, 2) + 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $pontos[] = $dados;
    }
    return $pontos;
  }

  ##############
  ### Clonar ###
  ##############

  function clonar() {
		$this->buscaDadosItem($_GET["i"]);
	  if ($this->dados_item) {
			if (!$_POST) {
	    	$this->conteudo .= $this->carregaArquivo("servidores.clonar.inc.php");
	    	// Dados
	     	$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["nome"], $this->conteudo);
	     	$this->conteudo = str_replace('[servidor]', $this->geraSelect($this->buscaServidores(), null, true, "servidor", "100%"), $this->conteudo);
	    	// Botões
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=migrar&i={$_GET["i"]}", $this->conteudo);
			} else {
    	  $query = "SELECT * FROM tab_servidores_interfaces WHERE ser_id = {$_GET["i"]}";
        $query = $this->bancodedados->query($query);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
          // formata dados
          $dados["sei_nome"] = $dados["sei_nome"] ? "'{$dados["sei_nome"]}'" : "''";
          $dados["sei_descricao"] = $dados["sei_descricao"] ? "'{$dados["sei_descricao"]}'" : "''";
          $dados["sei_classe"] = $dados["sei_classe"] ? "'{$dados["sei_classe"]}'" : "''";
          $dados["sei_prefixo_ip"] = $dados["sei_prefixo_ip"] ? "'{$dados["sei_prefixo_ip"]}'" : "NULL";
          $dados["sei_mascara_rede"] = $dados["sei_mascara_rede"] ? "'{$dados["sei_mascara_rede"]}'" : "NULL";
          $dados["sei_endereco_ip"] = $dados["sei_endereco_ip"] ? "{$dados["sei_endereco_ip"]}" : "NULL";
          $dados["sei_reserva_ips"] = $dados["sei_reserva_ips"] ? "{$dados["sei_reserva_ips"]}" : "NULL";
          $dados["sei_configuracao"] = $dados["sei_configuracao"] ? "'{$dados["sei_configuracao"]}'" : "NULL";
          $dados["sei_pai"] = $dados["sei_pai"] ? "'{$dados["sei_pai"]}'" : "NULL";
          $dados["sit_id"] = $dados["sit_id"] ? "{$dados["sit_id"]}" : "0";
          $dados["sip_id"] = $dados["sip_id"] ? "{$dados["sip_id"]}" : "1";
          $dados["sia_id"] = $dados["sia_id"] ? "{$dados["sia_id"]}" : "0";
          // insere interface
          $this->bancodedados->query("INSERT INTO tab_servidores_interfaces (ser_id, sei_nome, sei_descricao, sei_classe, sei_prefixo_ip, sei_mascara_rede, sei_endereco_ip, sei_reserva_ips, sei_configuracao, sei_pai, sit_id, sip_id, sia_id) VALUES ({$_POST["servidor"]}, {$dados["sei_nome"]}, {$dados["sei_descricao"]}, {$dados["sei_classe"]}, {$dados["sei_prefixo_ip"]}, {$dados["sei_mascara_rede"]}, {$dados["sei_endereco_ip"]}, {$dados["sei_reserva_ips"]}, {$dados["sei_configuracao"]}, {$dados["sei_pai"]}, {$dados["sit_id"]}, {$dados["sip_id"]}, {$dados["sia_id"]})");
          $interface_id = $this->bancodedados->ultimoIdInserido();
          // insere serviços
      	  $query_servicos = "SELECT * FROM tab_servidores_servicos WHERE sei_id = {$dados["sei_id"]}";
          $query_servicos = $this->bancodedados->query($query_servicos);
          while (($dados = $this->bancodedados->resultadoArray($query_servicos)) != false) {
            // formata dados
            $dados["ses_parametros"] = $dados["ses_parametros"] ? "'{$dados["ses_parametros"]}'" : "NULL";
            $dados["sst_id"] = $dados["sst_id"] ? $dados["sst_id"] : "0";
            $dados["ses_data_alteracao"] = $dados["ses_data_alteracao"] ? "'{$dados["ses_data_alteracao"]}'" : "NULL";
            $dados["ses_data_publicacao"] = $dados["ses_data_publicacao"] ? "'{$dados["ses_data_publicacao"]}'" : "NULL";
            $dados["sss_id"] = $dados["sss_id"] ? $dados["sss_id"] : "0";
            // insere serviço
            $this->bancodedados->query("INSERT INTO tab_servidores_servicos (sei_id, ses_parametros, sst_id, ses_data_alteracao, ses_data_publicacao, sss_id) VALUES ($interface_id, {$dados["ses_parametros"]}, {$dados["sst_id"]}, {$dados["ses_data_alteracao"]}, {$dados["ses_data_publicacao"]}, {$dados["sss_id"]})");
          }
        }
			  // Registra Atividade
			  $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        // Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_di&i={$_GET["i"]}");
			}
    } else {
			$this->fechar();
    }
  }

  ##############
  ### Migrar ###
  ##############
  
  function migrar() {
		$this->buscaDadosItem($_GET["i"]);
	  if ($this->dados_item) {
			if (!$_POST) {
	    	$this->conteudo .= $this->carregaArquivo("servidores.migrar.inc.php");
	    	// Dados
	     	$this->conteudo = str_replace('[servidor_nome]', $this->dados_item["nome"], $this->conteudo);
	     	$this->conteudo = str_replace('[servidor]', $this->geraSelect($this->buscaServidores(), null, true, "servidor", "100%"), $this->conteudo);
	    	// Botões
	    	$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
	    	$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$this->conteudo = str_replace('[botoes]', implode(" ", $botoes), $this->conteudo);
	    	$this->conteudo = str_replace('[action]', "?m={$this->dados_modulo["nome"]}&a=migrar&i={$_GET["i"]}", $this->conteudo);
			} else {
        $servidores_interfaces = controle::instanciaObjeto("servidores_interfaces", $this->bancodedados, $this->permissoes, false);
    	  $query = "
    	    SELECT
    	      sei_id AS id,
    	      sei_nome AS nome,
    	      sit_id AS tipo_id
    	    FROM
    	      tab_servidores_interfaces 
  	      WHERE
  	        ser_id = {$_GET["i"]}";
        $query = $this->bancodedados->query($query);
        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
        	$interface_destino = $this->buscaInterfaceDestino($_POST["servidor"], $dados["nome"], $dados["tipo_id"]);
          if ($interface_destino) {
  			    $servidores_interfaces->migrarInterface($dados["id"], $interface_destino);
          }
        }
				// Regera IP's Interface Destino
		    $this->regerarIPs($_POST["servidor"]);
				// Registra Atividade
			  $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
        // Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_di&i={$_GET["i"]}");
			}
    } else {
			$this->fechar();
    }
  }

	function buscaServidores() {
		$query = "
  		SELECT
  	    tab_servidores.ser_id AS id,
        CONCAT(tab_servidores.ser_nome, ' (', tab_servidores_interfaces.sei_prefixo_ip, '.', tab_servidores.ser_endereco_ip, ')') AS nome,
  			COUNT(DISTINCT tab_clientes_pontos.clp_id) AS pontos_total
  	  FROM
  	    tab_servidores
  	    INNER JOIN tab_servidores_interfaces ON (tab_servidores.sei_id = tab_servidores_interfaces.sei_id)
  	    INNER JOIN tab_servidores_interfaces tab_interfaces_clientes ON (tab_servidores.ser_id = tab_interfaces_clientes.ser_id)
  	    LEFT OUTER JOIN tab_clientes_pontos ON (tab_interfaces_clientes.sei_id = tab_clientes_pontos.sei_id AND tab_clientes_pontos.cps_id = 1)
  	  GROUP BY
  	  	tab_servidores.ser_id
  	  ORDER BY
  	    tab_servidores.ser_nome";
		$query = $this->bancodedados->query($query);
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servidores[$dados["id"]] = "{$dados["nome"]} [{$dados["pontos_total"]}]";
		}
		return $servidores;
	}
	
	function buscaInterfaceDestino($servidor_id, $nome, $tipo_id) {
	  $query = "
	    SELECT 
	      sei_id 
      FROM 
       tab_servidores_interfaces 
      WHERE 
        ser_id = $servidor_id AND 
        sei_nome = '$nome' AND
        sit_id = $tipo_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["sei_id"];
    }
	}

}
