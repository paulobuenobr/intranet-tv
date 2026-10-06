<?

/*
set_time_limit(0);

date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$cron = controle::instanciaObjeto("cron_operacoes_lotes", $bancodedados);
$cron->geraLoteAvulso();
*/

class cron_operacoes_lotes {

	var $bancodedados;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
	}

	#############################
	### Cria Lote Recuperação ###
	#############################

	function geraLoteRecuperacaoCobrancas() {
		if (date("w") == 1) {
    	$this->criaLoteRecuperacaoCobrancas(date("d/m/Y", strtotime("10 DAY AGO")), date("d/m/Y", strtotime("4 DAY AGO")));
		}
	}
	
	#############################
	### Desativa Lotes Vazios ###
	#############################

	function desativaLotesVazios() {
	  for ($sistema_id = 1; $sistema_id <= 3; $sistema_id++) {
	  	$lotes = $this->buscaLotesAtivos($sistema_id);
  	  if ($lotes) {
  	    foreach ($lotes as $lote_id) {
  	      //if ($this->loteNaoOperado($lote_id) || !$this->buscaClientesLote($lote_id)) {
  	      if (!$this->buscaClientesLote($lote_id)) {
  	      	$this->desativaLote($lote_id);
  	      }
  	    }
  	  }
	  }
	}

	############################################
	### Cria Lote Inadimplentes - Financeiro ###
	############################################

	function geraLoteInadimplentesFinanceiro() {
		$cobrancas_lotes = $this->buscaCobrancasLotesInadimplentes();
	  if ($cobrancas_lotes) {
	    foreach ($cobrancas_lotes as $cobrancas_lote) {
	      $this->criaLoteInadimplentesFinanceiro($cobrancas_lote);
	    }
	  }
	}

	###########################################
	### Cria Lote Inadimplentes - Pós-Venda ###
	###########################################

	function geraLoteInadimplentesPosVenda() {
		$cobrancas_lotes = $this->buscaCobrancasLotesInadimplentes();
	  if ($cobrancas_lotes) {
	    foreach ($cobrancas_lotes as $cobrancas_lote) {
	      $this->criaLoteInadimplentesPosVenda($cobrancas_lote);
	    }
	  }
	}

	########################
	### Funções de Busca ###
	########################

	function buscaLotesAtivos($sistema_id) {
	  $query = "
	    SELECT
	      opl_id AS lote_id
	    FROM
	      tab_operacoes_lotes
	    WHERE
	      oli_id = $sistema_id AND
	      ols_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lotes[] = $dados["lote_id"];
    }
    return $lotes;
	}

	function buscaClientesLote($lote_id) {
    $operacoes_sistema = controle::instanciaObjeto("operacoes_sistema", $this->bancodedados, false);
    $operacao = $operacoes_sistema->gerarOperacao($lote_id);
    return $operacao["lote"];
	}
	
	function loteNaoOperado($lote_id) {
		$query = "      
			SELECT
        ope_termino AS termino
      FROM
        tab_operacoes
      WHERE
        opl_id = $lote_id
			ORDER BY
        ope_termino DESC
      LIMIT
      	0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return strtotime($dados["termino"]) < strtotime(date("Y-m-d", strtotime("6 month ago")));
    }
	}

	function buscaCobrancasLotesInadimplentes() {
    $query = "
      SELECT
        col_id AS id,
        DATE_FORMAT(col_vencimento, '%m/%Y') AS vencimento
      FROM
        tab_cobrancas_lotes
      WHERE
        DATE_FORMAT(col_data_ativacao, '%Y-%m-%d') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y-%m-%d') AND
        cof_id = 5 AND
        cla_id = 4";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $lotes[] = $dados;
    }
    return $lotes;
	}

	function buscaOperacoesContatosCategorias($sistema_id) {
	  $categorias[] = "-1"; // Nenhum
	  $categorias[] = "-2"; // Somente Último
    $query = "
      SELECT
        occ_id AS id
      FROM
        tab_operacoes_contatos_categorias
      WHERE
        oli_id = $sistema_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $categorias[] = $dados["id"];
    }
    return $categorias;
	}

	###############################
	### Funções de Persistência ###
	###############################

	function desativaLote($lote_id) {
    $query = "UPDATE tab_operacoes_lotes SET ols_id = 2 WHERE opl_id = $lote_id";
    $this->bancodedados->query($query);
	}

	function criaLoteInadimplentesFinanceiro($cobrancas_lote) {
    $nome = "Inadimplentes - Financeiro ({$cobrancas_lote["vencimento"]})";
    $sistema_id = 3;
    $regras = $this->geraRegrasInadimplentesFinanceiro($cobrancas_lote["id"]);
    $this->insereLoteOperacao($nome, $sistema_id, $regras);
	}

	function criaLoteInadimplentesPosVenda($cobrancas_lote) {
    $nome = "Inadimplentes - Pós-Venda ({$cobrancas_lote["vencimento"]})";
    $sistema_id = 3;
    $regras = $this->geraRegrasInadimplentesPosVenda($cobrancas_lote["id"]);
    $this->insereLoteOperacao($nome, $sistema_id, $regras);
	}

	function criaLoteRecuperacaoCobrancas($data_vencimento_inicial, $data_vencimento_final) {
    $nome = "Vencidos de {$data_vencimento_inicial} à {$data_vencimento_final}";
    
    $regras = $this->geraRegrasRecuperacao($data_vencimento_inicial, $data_vencimento_final);
    
    $lote_id = $this->insereLoteOperacao($nome, 2, $regras);
    $this->distribuirResponsaveis($lote_id);
    $this->atualizaRegrasRecuperacao($lote_id);
	}

	function buscaLotePorNome($nome) {
	  $query = "
	    SELECT
	      opl_id AS id
	    FROM
	      tab_operacoes_lotes
	    WHERE
	      opl_nome = '$nome'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
	}

	function geraRegrasInadimplentesFinanceiro($lote_id) {
    $regras["enderecos"] = null;
    // Filtro Lote de Cobrança Inadimplente
    $regras["filtros"][] = $this->geraRegraFiltro(16, "=", $lote_id);
    // Filtro Situação do Cliente != Enviado para SPC
    $regras["filtros"][] = $this->geraRegraFiltro(15, "!=", 6);
    // Filtro Tipo Cobrança != Cheque
    $regras["filtros"][] = $this->geraRegraFiltro(21, "!=", 26);
    
    
    
    $regras["filtros"][] = $this->geraRegraFiltro(17, "=", 1);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 11);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 12);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 13);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 15);
    
    
    
    // Operadores do Lote
    foreach ($this->operadores_inadimplentes_financeiro as $operador_id) {
      $regras["validades"][] = $this->geraRegraValidade($operador_id, "", "", -1);
    }
    $regras["ultimos_contatos"] = "34, 35, 36, 37, 38, 39, 40";
    return serialize($regras);
	}

	function geraRegrasInadimplentesPosVenda($lote_id) {
    $regras["enderecos"] = null;
    // Filtro Lote de Cobrança Inadimplente
    $regras["filtros"][] = $this->geraRegraFiltro(16, "=", $lote_id);
    // Filtro Status do Cliente Ativo
    $regras["filtros"][] = $this->geraRegraFiltro(7, "=", 2);
    // Operadores do Lote
    foreach ($this->operadores_inadimplentes_posvenda as $operador_id) {
      $regras["validades"][] = $this->geraRegraValidade($operador_id, "", "", -1);
    }
    $regras["ultimos_contatos"] = "-1, -2, 34, 35, 36, 37, 38";
    return serialize($regras);
	}

	function geraRegrasRecuperacao($data_vencimento_inicial, $data_vencimento_final) {
    $regras["enderecos"] = null;

    $regras["filtros"][] = $this->geraRegraFiltro(30, ">=", $data_vencimento_inicial);
    $regras["filtros"][] = $this->geraRegraFiltro(30, "<=", $data_vencimento_final);
    //$regras["filtros"][] = $this->geraRegraFiltro(41, "-", "-");
    
    $regras["filtros"][] = $this->geraRegraFiltro(17, "=", 1);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 11);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 12);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 13);
    //$regras["filtros"][] = $this->geraRegraFiltro(17, "<>", 15);

    // Operadores do Lote
    $operadores = $this->buscaUsuariosOperacao("24");
    if ($operadores) {
      foreach ($operadores as $operador_id) {
        $regras["validades"][] = $this->geraRegraValidade($operador_id, "", "", -1);
      }
    }
    $regras["ultimos_contatos"] = @implode(", ", $this->buscaOperacoesContatosCategorias(2));
    return serialize($regras);
	}
	
	function atualizaRegrasRecuperacao($lote_id) {
	  // Substitui Regra 30 por 31 (Segundas-vias)
	  $query = "
	    SELECT
	      opl_regras AS regras
	    FROM
	      tab_operacoes_lotes
	    WHERE
	      opl_id = $lote_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["regras"] = unserialize($dados["regras"]);
      foreach ($dados["regras"]["filtros"] as $chave => $filtro) {
        if ($filtro["campo"] == 30) {
          $dados["regras"]["filtros"][$chave]["campo"] = 31;
        }
      }
      $dados["regras"] = serialize($dados["regras"]);
      $query = "UPDATE tab_operacoes_lotes SET opl_regras = '{$dados["regras"]}' WHERE opl_id = $lote_id";
    }
	}

	function buscaUsuariosOperacao($grupo_id) {
    $query = "
      SELECT
        tab_usuarios.usu_id AS id
      FROM
        rel_usu_gru_fazparte
        INNER JOIN tab_usuarios ON (rel_usu_gru_fazparte.usu_id = tab_usuarios.usu_id)
      WHERE
        rel_usu_gru_fazparte.gru_id IN ($grupo_id) AND
        tab_usuarios.uss_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $usuarios[] = $dados["id"];
    }
    return $usuarios;
	}

	function geraRegraFiltro($campo, $comparacao = null, $valor = null) {
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

	function insereLoteOperacao($nome, $sistema_id, $regras) {
    $query = "
      INSERT INTO
        tab_operacoes_lotes (opl_nome, oli_id, opl_recuperacao_vencimento, opl_regras, ols_id)
      VALUES ('$nome', $sistema_id, CURDATE(), '$regras', 1)";
    $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
	}

	function distribuirResponsaveis($lote_id) {
    $operacoes_lotes = controle::instanciaObjeto("operacoes_lotes", $this->bancodedados, null, false);
    $operacoes_lotes->distribuirResponsaveis($lote_id);
	}

}

?>