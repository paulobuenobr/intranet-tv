<?

require_once("_global/modulo.class.inc.php");

class operacoes_lotes extends modulo {

    function __construct($bancodedados, $permissoes, $executar = true) {
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			if ($_GET["a"] == "ver" && $this->permissoes[$this->dados_modulo["nome"]]["editar"]) $_GET["a"] = "editar";
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

  function buscaDadosItem($id) {
    $query = "
      SELECT
        tab_operacoes_lotes.opl_id AS id,
        tab_operacoes_lotes.opl_nome AS nome,
        tab_operacoes_lotes.opl_regras AS regras,
        tab_operacoes_lotes_status.ols_id AS status_id,
        tab_operacoes_lotes_status.ols_nome AS status_nome,
        tab_operacoes_lotes_sistemas.oli_id AS sistema_id,
        tab_operacoes_lotes_sistemas.oli_nome AS sistema_nome
      FROM
        tab_operacoes_lotes
        INNER JOIN tab_operacoes_lotes_status ON (tab_operacoes_lotes.ols_id = tab_operacoes_lotes_status.ols_id)
        INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_lotes.oli_id = tab_operacoes_lotes_sistemas.oli_id)
      WHERE
        opl_id = $id";
    $query = $this->bancodedados->query($query);
    $this->dados_item = $this->bancodedados->resultadoArray($query);
    if ($this->dados_item) {
      $this->dados_item["operacoes"] = $this->buscaItemOperacoes($id);
      $this->dados_item["regras"] = unserialize($this->dados_item["regras"]);
    }
  }

  function buscaItemOperacoes($id) {
    $query = "
      SELECT
        ope_id AS id,
        DATE_FORMAT(ope_inicio, '%d/%m/%Y (%H:%i)') AS data_hora,
        SEC_TO_TIME(UNIX_TIMESTAMP(ope_termino) - UNIX_TIMESTAMP(ope_inicio)) AS duracao,
        usu_nome AS responsavel,
        (SELECT COUNT(DISTINCT tab_contratos.cli_id) FROM tab_contratos WHERE tab_contratos.cor_data_geracao BETWEEN ope_inicio AND ope_termino AND tab_contratos.cli_id IN (SELECT tab_operacoes_contatos.cli_id FROM tab_operacoes_contatos WHERE tab_operacoes_contatos.ope_id = tab_operacoes.ope_id)) AS prospeccoes,
        (SELECT COUNT(DISTINCT tab_contratos.cli_id) FROM tab_contratos WHERE tab_contratos.cor_data_geracao BETWEEN ope_inicio AND ope_termino AND tab_contratos.crs_id >= 19 AND tab_contratos.cli_id IN (SELECT tab_operacoes_contatos.cli_id FROM tab_operacoes_contatos WHERE tab_operacoes_contatos.ope_id = tab_operacoes.ope_id)) AS ativacoes
      FROM
        tab_operacoes
        INNER JOIN tab_usuarios ON (tab_operacoes.usu_id = tab_usuarios.usu_id)
      WHERE
        opl_id = $id
      ORDER BY
        ope_inicio";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["contatos"] = $this->contaItemOperacaoContatos($dados["id"]);
      if ($this->permissoes["operacoes"]["ver"]) {
      	$dados["link"] = "javascript:verOperacao({$dados["id"]})";
      }
      $operacoes[] = $dados;
    }
    return $operacoes;
  }

  function contaItemOperacaoContatos ($id) {
    $query = "SELECT COUNT(opc_id) AS contatos FROM tab_operacoes_contatos WHERE ope_id = $id";
    $query = $this->bancodedados->query($query);
    return intval($this->bancodedados->resultado($query,0,"contatos"));
  }

  function buscaContatosCategorias ($id) {
    $categorias["-1"] = "Incluir se não tiver nenhum contato";
    $categorias["-2"] = "Considerar somente o último contato";
    $query = "
      SELECT
        occ_id AS id,
        occ_nome AS nome
      FROM
        tab_operacoes_contatos_categorias
      WHERE
        oli_id = $id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categorias[$dados["id"]] = $dados["nome"];
    }
    return $categorias;
  }

  function buscaCidade ($id) {
    $query = "SELECT cid_nome AS nome FROM tab_cidades WHERE cid_id = $id";
    return $this->bancodedados->resultado($this->bancodedados->query($query),0,"nome");
  }

  function buscaBairro ($id) {
    $query = "SELECT bai_nome AS nome FROM tab_bairros WHERE bai_id = $id";
    return $this->bancodedados->resultado($this->bancodedados->query($query),0,"nome");
  }

  function buscaLogradouro ($id) {
    $query = "SELECT log_nome AS nome FROM tab_logradouros WHERE log_id = $id";
    return $this->bancodedados->resultado($this->bancodedados->query($query),0,"nome");
  }

  function buscaCondominio($condominio_id) {
  	if ($condominio_id == -1) {
  		return "Todos";
  	} elseif ($condominio_id) {
      $query = "SELECT con_nome FROM tab_condominios WHERE con_id = $condominio_id";
      $query = $this->bancodedados->query($query);
      return $this->bancodedados->resultado($query);
  	}
  }

  function buscaContatosCategoriasNome($selecionados_ids) {
    $query = "SELECT occ_nome AS nome FROM tab_operacoes_contatos_categorias WHERE occ_id IN ($selecionados_ids)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$selecionados_nomes[] = $dados["nome"];
    }
    return implode(", ",$selecionados_nomes);
  }

  function buscaResponsavel($usuario_id, $lote_id) {
    $query = "SELECT usu_nome AS nome FROM tab_usuarios WHERE usu_id = $usuario_id";
    return $this->bancodedados->resultado($this->bancodedados->query($query), 0, "nome")." (".$this->buscaRegistrosUsuario($usuario_id, $lote_id).")";
  }

  function buscaRegistrosUsuario($usuario_id, $lote_id) {
    $query = "SELECT COUNT(cli_id) AS registros FROM tab_operacoes_lotes_responsabilidades WHERE usu_id = $usuario_id AND opl_id = $lote_id";
    return $this->bancodedados->resultado($this->bancodedados->query($query), 0, "registros");
  }

  function buscaPeriodo($id) {
    $query = "SELECT olp_nome AS nome FROM tab_operacoes_lotes_periodos WHERE olp_id = $id";
    return $this->bancodedados->resultado($this->bancodedados->query($query),0,"nome");
  }

  ###################
  ### Busca Itens ###
  ###################

  function buscaResponsaveis () {
    $query = "
      SELECT DISTINCT
        usu_id AS id,
        usu_nome AS nome
      FROM
        tab_usuarios
      WHERE
        uss_id = 1
      ORDER BY
        nome";
    $query = $this->bancodedados->query($query);
    $responsaveis["-1"] = "Todos";
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$responsaveis[$dados["id"]] = $dados["nome"];
    }
    return $responsaveis;
  }

  function buscaSistemas () {
    $query = "SELECT oli_id AS id, oli_nome AS nome FROM tab_operacoes_lotes_sistemas ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$sistemas[$dados["id"]] = $dados["nome"];
    }
    return $sistemas;
  }

  function buscaStatus () {
    $query = "SELECT ols_id AS id, ols_nome AS nome FROM tab_operacoes_lotes_status ORDER BY nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$status[$dados["id"]] = $dados["nome"];
    }
    return $status;
  }

  function buscaPeriodos () {
    $query = "SELECT olp_id AS id, olp_nome AS nome FROM tab_operacoes_lotes_periodos";
    $query = $this->bancodedados->query($query);
    $periodos["-1"] = "Todos";
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$periodos[$dados["id"]] = $dados["nome"];
    }
    return $periodos;
  }

  function buscaCidades () {
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
    $cidades["-1"] = "Todas";
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $cidades[$dados["id"]] = $dados["nome"];
    }
    return $cidades;
  }

  #############
  ### Geral ###
  #############

  function geraCheckBox($campos, $checked = false, $selecionados = "", $quantidade_colunas = 2) {
    if ($campos) {
      $itens_por_coluna = $quantidade_colunas ? ceil(count($campos) / $quantidade_colunas) : count($campos);
      $checkbox = '<table width="100%" border="0" cellspacing="1" cellpadding="1">';
      $checkbox .= '<tr class="txtAzulEscuro11">';
      $item_coluna = 1;
      foreach ($campos as $id => $descricao) {
        if ($item_coluna == 1) {
          $checkbox .= '<td width="25%" valign="top">';
        }
        $checkbox .= '<input name="categorias['.$id.']" id="categorias['.$id.']" type="checkbox"'.($checked || in_array($id, $selecionados) ? " checked" : "").'><label for="categorias['.$id.']"> '.$descricao.'</label>';
        if ($item_coluna == $itens_por_coluna) {
          $checkbox .= '</td>';
          $item_coluna = 1;
        } else {
          $checkbox .= '<br>';
          $item_coluna++;
        }
      }
      $checkbox .= '</tr></table>';
    }
    return $checkbox;
  }

  function formataData ($data) {
    return "'" . substr($data,6,4) . substr($data,3,2) . substr($data,0,2) . "'";
  }

  function geraItemListagemOperacoes($operacoes) {
    $cabecalho = array ("data_hora" => "Data/Hora", "duracao" => "Duração", "contatos" => "Contatos", "prospeccoes" => "Prospecções", "ativacoes" => "Ativações", "responsavel" => "Responsável");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $operacoes, 0, array("contatos" => "formataNumero", "prospeccoes" => "formataNumero", "ativacoes" => "formataNumero"));
    return $listagem->conteudo;
  }

  function trataRegras($categorias, $enderecos, $filtros, $validades, $sistema) {
    $categorias_sistema = $this->buscaContatosCategorias($sistema);
    foreach ($categorias as $chave => $categoria) {
      if (array_key_exists($chave, $categorias_sistema)) {
        $chaves[] = $chave;
      }
    }
    $regras["enderecos"] = @unserialize(stripslashes($enderecos));
    $regras["filtros"] = @unserialize(stripslashes($filtros));
    $regras["validades"] = @unserialize(stripslashes($validades));
    // Transforma Strings em Inteiros
    if ($regras["enderecos"]) {
      for ($i = 0; $i < count($regras["enderecos"]); $i++) {
        $regras["enderecos"][$i]["cidade"] = intval($regras["enderecos"][$i]["cidade"]);
        $regras["enderecos"][$i]["bairro"] = intval($regras["enderecos"][$i]["bairro"]);
        $regras["enderecos"][$i]["logradouro"] = intval($regras["enderecos"][$i]["logradouro"]);
        $regras["enderecos"][$i]["condominio"] = intval($regras["enderecos"][$i]["condominio"]);
      }
    }
    if ($regras["filtros"]) {
      for ($i = 0; $i < count($regras["filtros"]); $i++) {
        $regras["filtros"][$i]["campo"] = intval($regras["filtros"][$i]["campo"]);
      }
    }
    if ($regras["validades"]) {
      for ($i = 0; $i < count($regras["validades"]); $i++) {
        $regras["validades"][$i]["responsavel"] = intval($regras["validades"][$i]["responsavel"]);
        $regras["validades"][$i]["periodo"] = intval($regras["validades"][$i]["periodo"]);
      }
    }
    $regras["ultimos_contatos"] = implode(", ", $chaves);
    return @serialize($regras);
  }

  #################
  ### Endereços ###
  #################

  function buscaEnderecosListagem($itens, $editavel = true) {
    if ($editavel) {
      $cabecalho = array("cidade" => "Cidade", "bairro" => "Bairro", "logradouro" => "Logradouro", "condominio" => "Condomínio", "botoes" => "");
    } else {
      $cabecalho = array("cidade" => "Cidade", "bairro" => "Bairro", "logradouro" => "Logradouro", "condominio" => "Condomínio");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function buscaEnderecos($enderecos, $editavel = false) {
    if ($editavel) {
      $lista[] = $this->buscaEnderecosAdicionar();
    }
    if ($enderecos) {
      foreach ($enderecos as $endereco) {
        if ($editavel) {
          $endereco["link"] = "javascript:selecionarEndereco(this, event, {$endereco["id"]})";
          $endereco["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerEndereco(\''.$endereco["id"].'\')">';
        }
        $lista[] = $endereco;
      }
    } elseif ($editavel) {
      $lista[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    return $lista;
  }

  function buscaEnderecosAdicionar() {
    $adicionar["cidade"] = '<div id="div_cidade">'.$this->geraSelect($this->buscaCidades(), null, true, "cidade", "120px", "atualizaOperacoesLotesCidade(this.value)").'</div>';
    $adicionar["bairro"] = '<div id="div_bairro"><select name="bairro" id="bairro" class="inputBrancoAzulEscuro11" style="width: 120px" onChange="atualizaOperacoesLotesBairro(document.getElementById(\'cidade\').value,this.value)" disabled><option value="-1">Todos</option></select></div>';
    $adicionar["logradouro"] = '<div id="div_logradouro"><select name="logradouro" id="logradouro" class="inputBrancoAzulEscuro11" style="width: 195px" onChange="atualizaOperacoesLotesLogradouro(document.getElementById(\'cidade\').value,document.getElementById(\'bairro\').value,this.value)" disabled><option value="-1">Todos</option></select></div>';
    $adicionar["condominio"] = '<div id="div_condominio"><select name="condominio" id="condominio" class="inputBrancoAzulEscuro11" style="width: 230px" disabled><option value="-1">Todos</option></select></div>';
    $adicionar["botoes"] = '<input type="button" name="adicionar_endereco" id="adicionar_endereco" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarEndereco()">';
    return $adicionar;
  }

  function geraEnderecosListagem($dados = null, $editavel = true) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        if ($editavel) {
          $dados[$i]["link"] = "javascript:selecionarEndereco(this, event, ".($i + 1).")";
          $dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerEndereco(\''.($i + 1).'\')">';
        }

        $dados[$i]["cidade"] = ($dados[$i]["cidade"] != -1) ? $this->buscaCidade($dados[$i]["cidade"]) : "Todas";
        $dados[$i]["bairro"] = ($dados[$i]["bairro"] != -1) ? $this->buscaBairro($dados[$i]["bairro"]) : "Todos";
        $dados[$i]["logradouro"] = ($dados[$i]["logradouro"] != -1) ? $this->buscaLogradouro($dados[$i]["logradouro"]) : "Todos";
        $dados[$i]["condominio"] = $this->buscaCondominio($dados[$i]["condominio"]);
      }
    } else {
      $dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    if ($editavel) {
      $dados = funcoes::mescla_arrays(array($this->buscaEnderecosAdicionar()), $dados);
      $cabecalho = array("cidade" => "Cidade", "bairro" => "Bairro", "logradouro" => "Logradouro", "condominio" => "Condomínio", "botoes" => "");
    } else {
      $cabecalho = array("cidade" => "Cidade", "bairro" => "Bairro", "logradouro" => "Logradouro", "condominio" => "Condomínio");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  ###############
  ### Filtros ###
  ###############

  function buscaFiltrosListagem($itens, $editavel = true) {
    if ($editavel) {
      $cabecalho = array("campo" => "Parametro", "comparacao" => "Comparação", "valor" => "Valor", "botoes" => "");
    } else {
      $cabecalho = array("campo" => "Parametro", "comparacao" => "Comparação", "valor" => "Valor");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function buscaLotesFiltros($sistema_id = 1) {
    $query = "
  		SELECT
  			olf_id AS id,
  			olf_nome AS nome
  		FROM
  			tab_operacoes_lotes_filtros
  	  WHERE
  	     oli_id = $sistema_id OR
  	     oli_id IS NULL
  		ORDER BY
  			nome";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaDadosLotesFiltros($filtro_id) {
    $query = "
  		SELECT
  		  olf_nome AS nome,
  			olf_parametros AS parametros
  		FROM
  			tab_operacoes_lotes_filtros
  		WHERE
  			olf_id = $filtro_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      return $dados;
    }
  }

  function buscaFiltroTipoConteudo($query) {
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lista[$dados["id"]] = $dados["nome"];
    }
    return $lista;
  }

  function buscaFiltrosAdicionar($sistema_id) {
    $adicionar["campo"] = $this->geraSelect($this->buscaLotesFiltros($sistema_id), null, true, "campo", "260px", "mudaFiltroCampo(this.value)");
    $adicionar["comparacao"] = '<div id="div_comparacao"><select name="comparacao" id="comparacao" style="width:120px" class="inputBrancoAzulEscuro11" disabled></select></div>';
    $adicionar["valor"] = '<div id="div_valor"><select name="valor" id="valor" style="width:300px" class="inputBrancoAzulEscuro11" disabled></select></div>';
    $adicionar["botoes"] = '<input type="button" name="adicionar_filtro" id="adicionar_filtro" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarFiltro()">';
    return $adicionar;
  }

  function geraFiltrosListagem($dados = null, $editavel = true, $sistema_id = null) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        if ($editavel) {
          $dados[$i]["link"] = "javascript:selecionarFiltro(this, event, ".($i + 1).")";
          $dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerFiltro(\''.($i + 1).'\')">';
        }
        $dados_tipo = $this->buscaDadosLotesFiltros($dados[$i]["campo"]);
        $dados[$i]["campo"] = $dados_tipo["nome"];
        $dados[$i]["comparacao"] = $dados_tipo["parametros"]["comparacao"][$dados[$i]["comparacao"]];
        if (is_array($dados_tipo["parametros"]["valor_conteudo"])) {
          $dados[$i]["valor"] = $dados_tipo["parametros"]["valor_conteudo"][$dados[$i]["valor"]];
        } else if ($dados_tipo["parametros"]["valor_conteudo"]) {
          $dados_valor = $this->buscaFiltroTipoConteudo($dados_tipo["parametros"]["valor_conteudo"]);
          $dados[$i]["valor"] = $dados_valor[$dados[$i]["valor"]];
        }
      }
    } else {
      $dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    if ($editavel) {
      $dados = funcoes::mescla_arrays(array($this->buscaFiltrosAdicionar($sistema_id)), $dados);
      $cabecalho = array("campo" => "Campo", "comparacao" => "Comparação", "valor" => "Valor", "botoes" => "");
    } else {
      $cabecalho = array("campo" => "Campo", "comparacao" => "Comparação", "valor" => "Valor");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  #################
  ### Validades ###
  #################

  function buscaValidadesListagem($itens, $editavel = true) {
    if ($editavel) {
      $cabecalho = array("responsavel" => "Responsável", "inicio" => "Início", "termino" => "Término", "periodo" => "Período", "botoes" => "");
    } else {
      $cabecalho = array("responsavel" => "Responsável", "inicio" => "Início", "termino" => "Término", "periodo" => "Período");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
    return $listagem->conteudo;
  }

  function buscaValidades($validades, $editavel = false) {
    if ($editavel) {
      $lista[] = $this->buscaValidadesAdicionar();
    }
    if ($validades) {
      foreach ($validades as $validade) {
        if ($editavel) {
          $validade["link"] = "javascript:selecionarValidade(this, event, {$validade["id"]})";
          $validade["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerValidade(\''.$validade["id"].'\')">';
        }
        $lista[] = $validade;
      }
    } elseif ($editavel) {
      $lista[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    return $lista;
  }

  function buscaValidadesAdicionar() {
    $adicionar["responsavel"] = '<div id="div_responsavel">'.$this->geraSelect($this->buscaResponsaveis(), null, true, "responsavel", "305px").'</div>';
    $adicionar["inicio"] = '<div id="div_inicio"><input name="inicio" id="inicio" class="inputBrancoAzulEscuro11" style="width:120px" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></div>';
    $adicionar["termino"] = '<div id="div_termino"><input name="termino" id="termino" class="inputBrancoAzulEscuro11" style="width:120px" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);"></div>';
    $adicionar["periodo"] = '<div id="div_periodo">'.$this->geraSelect($this->buscaPeriodos(), null, true, "periodo", "120px").'</div>';
    $adicionar["botoes"] = '<input type="button" name="adicionar_validade" id="adicionar_validade" value="OK" style="width:25px" class="buttonAzulEscuroBranco11" onclick="adicionarValidade()">';
    return $adicionar;
  }

  function geraValidadesListagem($lote_id = null, $dados = null, $editavel = true) {
    if ($dados) {
      for ($i = 0; $i < count($dados); $i++) {
        if ($editavel) {
          $dados[$i]["link"] = "javascript:selecionarValidade(this, event, ".($i + 1).")";
          $dados[$i]["botoes"] = '<input type="button" name="remover" id="remover" value="X" style="width:25px" class="buttonAzulEscuroBranco11" onclick="removerValidade(\''.($i + 1).'\')">';
        }

        $dados[$i]["responsavel"] = ($dados[$i]["responsavel"] != -1) ? $this->buscaResponsavel($dados[$i]["responsavel"], $lote_id) : "Todos";
        $dados[$i]["periodo"] = ($dados[$i]["periodo"] != -1) ? $this->buscaPeriodo($dados[$i]["periodo"]) : "Todos";
      }
    } else {
      $dados[] = array("mesclado" => "<table width=\"100%\" height=\"21\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" align=\"center\">Nenhum registro disponível</td></tr></table>");
    }
    if ($editavel) {
      $dados = funcoes::mescla_arrays(array($this->buscaValidadesAdicionar()), $dados);
      $cabecalho = array("responsavel" => "Responsável", "inicio" => "Início", "termino" => "Término", "periodo" => "Período", "botoes" => "");
    } else {
      $cabecalho = array("responsavel" => "Responsável", "inicio" => "Início", "termino" => "Término", "periodo" => "Período");
    }
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados);
    return $listagem->conteudo;
  }

  #################
  ### Adicionar ###
  #################

  function adicionar() {
    if (!$_POST) {
      $this->conteudo .= $this->carregaArquivo("operacoes_lotes.adicionar.inc.php");
      // Dados
      $this->conteudo = str_replace('[action]', '?m='.$this->dados_modulo["nome"].'&a=adicionar', $this->conteudo);
      $this->conteudo = str_replace('[sistema]', $this->geraSelect($this->buscaSistemas(), null, true, "sistema", null, "mudaSistemaRegras(this.value)"), $this->conteudo);
      $this->conteudo = str_replace('[enderecos]', $this->buscaEnderecosListagem($this->buscaEnderecos(null, true)), $this->conteudo);
      $this->conteudo = str_replace('[filtros]', $this->geraFiltrosListagem(null, false), $this->conteudo);
      $this->conteudo = str_replace('[validades]', $this->buscaValidadesListagem($this->buscaValidades(null, true)), $this->conteudo);
      $this->conteudo = str_replace('[ultimos_contatos]', $this->geraCheckBox($this->buscaContatosCategorias('1'), true), $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), null, true, "status"), $this->conteudo);
      // Botões
      $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
      $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
    } else {
      $_POST["nome"] = trim($_POST["nome"]);
      $regras = $this->trataRegras($_POST["categorias"],$_POST["enderecos_lista"],$_POST["filtros_lista"],$_POST["validades_lista"],$_POST["sistema"]);

      $query = "INSERT INTO tab_operacoes_lotes (opl_nome, opl_regras, ols_id, oli_id) VALUES ('{$_POST["nome"]}', '$regras', '{$_POST["status"]}', '{$_POST["sistema"]}')";
      $this->bancodedados->query($query);
      $id = $this->bancodedados->ultimoIdInserido();

      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $id);
      header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");
    }
  }

  ###########
  ### Ver ###
  ###########

  function ver () {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      $this->conteudo .= $this->carregaArquivo("operacoes_lotes.ver.inc.php");
      // Dados;
      $this->conteudo = str_replace('[lote_id]', $this->dados_item["id"], $this->conteudo);
      $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
      $this->conteudo = str_replace('[sistema]', $this->dados_item["sistema_nome"], $this->conteudo);
      $this->conteudo = str_replace('[inicio]', $this->dados_item["inicio"], $this->conteudo);
      $this->conteudo = str_replace('[termino]', $this->dados_item["termino"], $this->conteudo);
      $this->conteudo = str_replace('[periodo]', $this->dados_item["periodo_nome"], $this->conteudo);
      $this->conteudo = str_replace('[enderecos]', $this->geraEnderecosListagem($this->dados_item["regras"]["enderecos"], false), $this->conteudo);
      $this->conteudo = str_replace('[filtros]', $this->geraFiltrosListagem($this->dados_item["regras"]["filtros"], false, $this->dados_item["sistema_id"]), $this->conteudo);
      $this->conteudo = str_replace('[validades]', $this->geraValidadesListagem($this->dados_item["id"], $this->dados_item["regras"]["validades"], false), $this->conteudo);
      $this->conteudo = str_replace('[ultimos_contatos]', $this->buscaContatosCategoriasNome($this->dados_item["regras"]["ultimos_contatos"]), $this->conteudo);
      $this->conteudo = str_replace('[responsavel]', $this->dados_item["responsavel_nome"], $this->conteudo);
      $this->conteudo = str_replace('[status]', $this->dados_item["status_nome"], $this->conteudo);
      $this->conteudo = str_replace('[operacoes]', $this->geraItemListagemOperacoes($this->dados_item["operacoes"]), $this->conteudo);
      // Botões
      $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
      if ($this->permissoes[$this->dados_modulo["nome"]]["distribuir"]) $botoes[] = '<input type="button" name="distribuir" class="buttonAzulEscuroBranco11" style="width:65" value="Distribuir" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=distribuir&i='.$this->dados_item["id"].'\';">';
      $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
      $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
      $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao,$this->dados_item["id"]);
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  ##############
  ### Editar ###
  ##############

  function editar () {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("operacoes_lotes.editar.inc.php");
        // Dados
        $this->conteudo = str_replace('[lote_id]', $this->dados_item["id"], $this->conteudo);
        $this->conteudo = str_replace('[nome]', $this->dados_item["nome"], $this->conteudo);
        $this->conteudo = str_replace('[sistema]', $this->geraSelect($this->buscaSistemas(), $this->dados_item["sistema_id"], false, "sistema", null, "mudaSistemaRegras(this.value)"), $this->conteudo);
        $this->conteudo = str_replace('[enderecos]', $this->geraEnderecosListagem($this->dados_item["regras"]["enderecos"]), $this->conteudo);
        $this->conteudo = str_replace('[enderecos_lista]', @serialize($this->dados_item["regras"]["enderecos"]), $this->conteudo);
        $this->conteudo = str_replace('[filtros]', $this->geraFiltrosListagem($this->dados_item["regras"]["filtros"], true, $this->dados_item["sistema_id"]),$this->conteudo);
        $this->conteudo = str_replace('[filtros_lista]', @serialize($this->dados_item["regras"]["filtros"]), $this->conteudo);
        $this->conteudo = str_replace('[validades]', $this->geraValidadesListagem($this->dados_item["id"], $this->dados_item["regras"]["validades"], true, $this->dados_item["sistema_id"]),$this->conteudo);
        $this->conteudo = str_replace('[validades_lista]', @serialize($this->dados_item["regras"]["validades"]), $this->conteudo);
        $this->conteudo = str_replace('[ultimos_contatos]', $this->geraCheckBox($this->buscaContatosCategorias($this->dados_item["sistema_id"]), false, explode(", ", $this->dados_item["regras"]["ultimos_contatos"])), $this->conteudo);
        $this->conteudo = str_replace('[status]', $this->geraSelect($this->buscaStatus(), $this->dados_item["status_id"], false, "status"), $this->conteudo);
        $this->conteudo = str_replace('[operacoes]', $this->geraItemListagemOperacoes($this->dados_item["operacoes"]), $this->conteudo);
        // Botões
        $botoes[] = '<input type="submit" name="salvar" class="buttonAzulEscuroBranco11" value="Salvar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
        if ($this->permissoes[$this->dados_modulo["nome"]]["distribuir"]) $botoes[] = '<input type="button" name="distribuir" class="buttonAzulEscuroBranco11" style="width:65" value="Distribuir" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=distribuir&i='.$this->dados_item["id"].'\';">';
        $this->conteudo = str_replace("[botoes]", @implode(" ",$botoes),$this->conteudo);
        // Action
        $this->conteudo = str_replace('[action]','?m=' . $this->dados_modulo["nome"] . '&a=editar&i=' . $this->dados_item["id"],$this->conteudo);
      } else {
        $_POST["nome"] = trim($_POST["nome"]);
        $regras = $this->trataRegras($_POST["categorias"], $_POST["enderecos_lista"], $_POST["filtros_lista"], $_POST["validades_lista"], $_POST["sistema"]);

        $query = "UPDATE tab_operacoes_lotes SET opl_nome = '{$_POST["nome"]}', opl_regras = '$regras', ols_id = '{$_POST["status"]}', oli_id = '{$_POST["sistema"]}' WHERE opl_id = '{$_GET["i"]}'";
        $this->bancodedados->query($query);

        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
        $atividade->registraAtividade($_SESSION["usuario"],$this->dados_modulo["id"],$this->acao,$this->dados_item["id"]);
        header("Location: ./?m=" . $this->dados_modulo["nome"] . "&a=ver&i=" . $_GET["i"]);
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
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
    if ($this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) $botoes[] = '<input type="button" class="buttonAzulEscuroBranco11" name="adicionar" value="Adicionar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=adicionar\'">';
    $this->conteudo .= @implode(" ",$botoes);
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
    $itens[] = array ("tipo" => "input", "nome" => "nome", "descricao" => "Nome", "valor" => "", "where" => "(tab_operacoes_lotes.opl_nome LIKE '%[valor]%')");
    // Sistema
    $sistemas = $this->buscaSistemas();
    foreach ($sistemas as $id => $nome) {
      $select_sistema[] = array ("id" => $id, "nome" => $nome);
      next($sistemas);
    }
    $itens[] = array ("tipo" => "select", "nome" => "sistema", "descricao" => "Sistema", "valor" => $select_sistema, "where" => "(tab_operacoes_lotes.oli_id = [valor])");
    // Status
    $status = $this->buscaStatus();
    foreach ($status as $id => $nome) {
      $select_status[] = array ("id" => $id, "nome" => $nome);
    }
    $itens[] = array ("tipo" => "select", "nome" => "status", "descricao" => "Status", "valor" => $select_status, "where" => "(tab_operacoes_lotes.ols_id = [valor])");
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
        tab_operacoes_lotes.opl_id AS id,
        tab_operacoes_lotes.opl_nome AS nome,
        tab_operacoes_lotes_sistemas.oli_nome AS sistema,
        tab_operacoes_lotes_status.ols_nome AS status
      FROM
        tab_operacoes_lotes
        INNER JOIN tab_operacoes_lotes_sistemas ON (tab_operacoes_lotes.oli_id = tab_operacoes_lotes_sistemas.oli_id)
        INNER JOIN tab_operacoes_lotes_status ON (tab_operacoes_lotes.ols_id = tab_operacoes_lotes_status.ols_id)
      WHERE
        $filtro->parametros_sql
      ORDER BY
      	tab_operacoes_lotes.oli_id,
        tab_operacoes_lotes.opl_recuperacao_vencimento,
		tab_operacoes_lotes.opl_nome
      LIMIT
        $limit_inicio,
        $limit_fim";
    //Inadimplentes 20/04/2008 (Olé Telecom)
    $query = $this->bancodedados->query($query);
    $linhas = $this->bancodedados->linhasTotal($query);
    $itens = array();
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
        $dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
      }
      $dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
      $itens[] = $dados;
    }
    return array("linhas" => $linhas, "itens" => $itens);
  }

  function geraListagem($itens) {
    $cabecalho = array ("contador" => "#", "nome" => "Nome", "sistema" => "Sistema", "status" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array("20"), null, array("right", "left"));
    return $listagem;
  }

  ##################
  ### Distribuir ###
  ##################

  function distribuir() {
    $this->buscaDadosItem($_GET["i"]);
    if ($this->dados_item) {
      if (!$_POST) {
        $this->conteudo .= $this->carregaArquivo("operacoes_lotes.distribuir.inc.php");
        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
        $this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=distribuir&i={$_GET["i"]}", $this->conteudo);
      } else {
        set_time_limit(0);
        ini_set("memory_limit", "128M");
        $this->distribuirResponsaveis($_GET["i"]);
        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
      }
    } else {
      header("Location: ./?m={$this->dados_modulo["nome"]}");
    }
  }

  function distribuirResponsaveis($lote_id) {
    // Busca Dados Item
    if (!$this->dados_item) {
      $this->buscaDadosItem($lote_id);
    }
    // Limpa Responsáveis Atuais
    $this->limpaDistribuicao($lote_id);
    // Filtra Usuarios Recuperação
    foreach ($this->dados_item["regras"]["validades"] as $indice => $validade) {
      if ($this->dados_item["sistema_id"] == 1) {
        $operadores[] = $validade["responsavel"];
      } elseif ($this->ehUsuarioRecuperacao($validade["responsavel"], "24")) {
        $operadores[] = $validade["responsavel"];
      } elseif ($this->ehUsuarioRecuperacao($validade["responsavel"], "26")) {
        $assistentes[] = $validade["responsavel"];
      }
    }
    // Busca Clientes
    $clientes = $this->buscaClientesLote($lote_id);
    if ($clientes) {
      $operador = 0;
      $assistente = 0;
      if ($clientes["lote"]) {
        foreach($clientes["lote"] as $cliente) {
          /*
          if (($this->dados_item["sistema_id"] == 1) || ($cliente["valor_original"] <= 10000)) {
          */
            if (!$operadores[$operador]) {
              $operador = 0;
            }
            $responsavel_id = $operadores[$operador];
            $operador++;
          /*
          } else {
            if (!$assistentes[$assistente]) {
              $assistente = 0;
            }
            $responsavel_id = $assistentes[$assistente];
            $assistente++;
          }
          */
          $query = "INSERT INTO tab_operacoes_lotes_responsabilidades (opl_id, cli_id, usu_id) VALUES ($lote_id, {$cliente["id"]}, $responsavel_id)";
          $this->bancodedados->query($query);
        }
      }
    }
  }

  function ehUsuarioRecuperacao($usuario_id, $grupo_id) {
    $query = "
      SELECT
        usu_id AS id
      FROM
        rel_usu_gru_fazparte
      WHERE
        gru_id IN ($grupo_id) AND
        usu_id = $usuario_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  function limpaDistribuicao($lote_id) {
    $query = "DELETE FROM tab_operacoes_lotes_responsabilidades WHERE opl_id = $lote_id";
    $this->bancodedados->query($query);
  }

  function buscaClientesLote($lote_id) {
    $operacoes_sistema = controle::instanciaObjeto("operacoes_sistema", $this->bancodedados, false);
    return $operacoes_sistema->gerarOperacao($lote_id, null, true);
  }

}

?>
