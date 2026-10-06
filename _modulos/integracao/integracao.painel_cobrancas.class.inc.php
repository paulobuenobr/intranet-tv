<?

class integracao_painel_cobrancas {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "listar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

	##############
	### Listar ###
	##############
	
	function listar() {
    $query = "
  		SELECT
  		  tab_cobrancas.coa_id AS id,
  		  tab_cobrancas.coa_pai AS coa_pai,
  		  tab_cobrancas.coa_codigo AS codigo,
  		  DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
  		  IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas.coa_data_vencimento < CURDATE(), 1, 0) AS em_atraso,
  		  DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_processamento,
  		  tab_cobrancas.coa_valor_original AS valor_original,
  		  tab_cobrancas.coa_valor_total AS valor_total,
  		  tab_cobrancas.cot_id AS status_id
  		FROM
  		  tab_cobrancas
  		WHERE
  		  tab_cobrancas.cli_id = {$_GET["usuario"]} AND
  		  tab_cobrancas.cot_id NOT IN (6, 12) AND
  		  tab_cobrancas.coa_pai IS NULL AND
  		  (tab_cobrancas.col_id IS NULL OR tab_cobrancas.col_id NOT IN (SELECT col_id FROM tab_cobrancas_lotes WHERE cla_id != 4))
  		ORDER BY
  		  tab_cobrancas.coa_data_vencimento DESC
      LIMIT
        0, 12";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$segunda_via = $this->buscaCobrancaSegundaVia($dados["id"]);
      if ($segunda_via) {
        if ($segunda_via["status_id"] > 1) {
          $segunda_via["valor_original"] = $dados["valor_original"];
          $segunda_via["data_vencimento"] = $dados["data_vencimento"];
        }
        $dados = $segunda_via;
      }
      $dados["status"] = $this->buscaStatusTratado($dados["status_id"]);
      if ($dados["em_atraso"]) {
        $dados["status"] = "Em Atraso";
      }
      $dados["valor_original"] = $this->formataDinheiro($dados["valor_original"]);
      if ($dados["status_id"] == 1) {
        $dados["valor_total"] = "";
      } else {
        $dados["valor_total"] = $this->formataDinheiro($dados["valor_total"]);
      }
      if (!in_array($dados["status_id"], array(2, 3, 4, 5, 9))) {
        $dados["data_processamento"] = "";
      }
      $dados["competencia"] = $this->buscaCobrancaCompetencia($dados["id"]);
      $cobrancas[] = $dados;
    }
    $this->conteudo = serialize($cobrancas);
	}
	
	function buscaCobrancaSegundaVia($cobranca_id) {
    $query = "
  		SELECT
  		  tab_cobrancas.coa_id AS id,
  		  tab_cobrancas.coa_pai AS coa_pai,
  		  tab_cobrancas.coa_codigo AS codigo,
  		  DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
  		  IF(tab_cobrancas.cot_id = 1 AND tab_cobrancas.coa_data_vencimento < CURDATE(), 1, 0) AS em_atraso,
  		  DATE_FORMAT(tab_cobrancas.coa_data_pagamento, '%d/%m/%Y') AS data_processamento,
  		  tab_cobrancas.coa_valor_original AS valor_original,
  		  tab_cobrancas.coa_valor_total AS valor_total,
  		  tab_cobrancas.cot_id AS status_id
  		FROM
  		  tab_cobrancas
  		WHERE
  		  tab_cobrancas.cot_id NOT IN (6, 11, 12) AND
  		  tab_cobrancas.coa_pai = $cobranca_id
  		ORDER BY
  		  tab_cobrancas.coa_id DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
	}
	
	################
	### Detalhes ###
	################
	
	function detalhes() {
	  $this->conteudo = serialize($this->buscaDetalhesCobranca($_GET["i"]));
	}
	
	function buscaDetalhesCobranca($cobranca_id) {
    $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, "", false);
    $cobrancas->buscaDadosItem($cobranca_id);
    if ($cobrancas->dados_item && $cobrancas->dados_item["cliente_id"] == $_GET["usuario"]) {
      if ($cobrancas->dados_item["status_id"] == 1 && !$cobrancas->dados_item["em_atraso"] && $cobrancas->dados_item["valor_bonificacao"]) {
        $cobrancas->dados_item["valor_bonificado"] = $this->formataDinheiro($cobrancas->dados_item["valor_original"] - $cobrancas->dados_item["valor_bonificacao"]);
      }
      $cobrancas->dados_item["valor_original"] = $this->formataDinheiro($cobrancas->dados_item["valor_original"]);
      $cobrancas->dados_item["detalhamento"] = $cobrancas->geraCobrancaDetalhamento($cobrancas->dados_item["id"], $cobrancas->dados_item["valor_bonificacao"], $cobrancas->dados_item["data_vencimento"]);
      $cobrancas->dados_item["imprimivel_boleto"] = $cobrancas->dados_item["status_id"] == 1 && $cobrancas->dados_item["parametros"]["boleto"] && (!$cobrancas->dados_item["lote_id"] || $cobrancas->dados_item["lote_status_id"] = 4);
      $cobrancas->dados_item["geravel_segunda_via"] = $cobrancas->dados_item["em_atraso"] && (!$cobrancas->dados_item["lote_id"] || $cobrancas->dados_item["lote_status_id"] = 4);
      $cobrancas->dados_item["competencia"] = $this->buscaCobrancaCompetencia($cobranca_id);
      $cobrancas->dados_item["status"] = $this->buscaStatusTratado($cobrancas->dados_item["status_id"]);
      if ($cobrancas->dados_item["em_atraso"]) {
        $cobrancas->dados_item["status"] = "Em Atraso";
      }
      return $cobrancas->dados_item;
    }
    return null;
	}
	
	###################
	### Segunda Via ###
	###################
	
	function segunda_via() {
	  // Instancia Objetos
    $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, "", false);
    $cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
	  // Busca Dados Cobrança
    $cobrancas->buscaDadosItem($_GET["i"]);
    if ($cobrancas->dados_item && $cobrancas->dados_item["cliente_id"] == $_GET["usuario"]) {
      if ($cobrancas->dados_item["coa_pai"]) {
        $cobrancas->buscaDadosItem($cobrancas->dados_item["coa_pai"]);
      }
      // Dados Segunda-Via
      $formato_id = $cobrancas->buscaFormatoBoleto();
      $data_vencimento = date("d/m/Y", strtotime("+ 5 days"));
      $valor_original = $cobrancas->dados_item["valor_original"];
      $valor_juro = $cobrancas_geracao->calculaJuro($valor_original, $this->codificaData($cobrancas->dados_item["data_vencimento"]), $this->codificaData($data_vencimento));
      $comentario = "<b>Olé Controle:</b> Gerada pelo cliente ({$_GET["ip_cliente"]})";
      // Gera Segunda-Via
      $cobranca_id = $cobrancas_geracao->geraSegundaVia($cobrancas->dados_item, $formato_id, $data_vencimento, $valor_original, $valor_juro, array(), array(), 0, $comentario);
      if ($cobranca_id) {
        $cobrancas->finalizaBloqueio($cobrancas->dados_item["cliente_id"]);
        $dados_cobranca = $this->buscaDetalhesCobranca($cobranca_id);
        $dados_cobranca["bloqueado"] = $this->clienteBloqueado($cobrancas->dados_item["cliente_id"]);
        $this->conteudo = serialize($dados_cobranca);
      }
    }
	}
	  
  function clienteBloqueado($cliente_id) {
    $query = "
     SELECT 
       COUNT(*) bloqueios
     FROM 
       tab_servicos_ajustes saj 
     WHERE 
       saj.cli_id = $cliente_id AND 
       saj.sas_id IN (5, 9) AND 
       saj.sac_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["bloqueios"] >= 1;
    }
    return false;
  }
	
	function codificaData($data) {
    return substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2);
	}
	
	################
	### Imprimir ###
	################
	
	function imprimir() {
	  $dados_cobranca = $this->buscaDadosCobranca($_GET["i"]);
  	if ($dados_cobranca && $dados_cobranca["cliente_id"] == $_GET["usuario"]) {
      $this->conteudo = $this->carregaArquivo(DIR_RAIZ."modelos/cobrancas_boletos_{$dados_cobranca["parametros"]["boleto"]["versao"]}.html");
      foreach ($dados_cobranca["parametros"]["boleto"] as $chave => $valor) {
        $this->conteudo = str_replace("[$chave]", $valor, $this->conteudo);
      }
      $this->conteudo = str_replace("[titulo]", "Olé Telecom", $this->conteudo);
      $this->conteudo = serialize(preg_replace("/\\[[^\\]]*\\]/", "", $this->conteudo));
  	} else {
      $this->conteudo = serialize("<h1>Acesso não autorizado</h1>");
  	}
	}
	
	##############
	### Buscas ###
	##############
	
	function buscaStatusTratado($status_id) {
  	$status[1] = "Em Aberto";  // Em Aberto
  	$status[2] = "Paga";       // Baixa Automática (Total)
  	$status[3] = "Parcial";    // Baixa Automática (Parcial)
  	$status[4] = "Paga";       // Baixa Manual (Paga na Empresa)
  	$status[5] = "Paga";       // Baixa Manual (Depósito Identificado)
  	$status[6] = "Cancelada";  // Baixa Manual (Indevida)
  	$status[7] = "Negociada";  // Baixa Manual (Negociada)
  	$status[8] = "Cortesia";   // Baixa Automática (Cortesia)
  	$status[9] = "Paga";       // Baixa Manual (Assistência Técnica)
  	$status[11] = "Cancelada"; // Baixa Manual (Paga em Outra Via)
  	$status[12] = "Cancelada"; // Cancelada
  	return $status[$status_id];
	}
	

  function buscaCobrancaCompetencia($cobranca_id) {
    $query = "
      SELECT
        DATE_FORMAT(MIN(cod_competencia_inicio), '%m/%Y') AS competencia_inicio,
        DATE_FORMAT(MAX(cod_competencia_inicio), '%m/%Y') AS competencia_termino
      FROM
        tab_cobrancas_detalhes
      WHERE
        coa_id = $cobranca_id AND
        cod_competencia_inicio IS NOT NULL
      ORDER BY
        cod_competencia_inicio";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["competencia_inicio"] == $dados["competencia_termino"]) {
        return $dados["competencia_inicio"];
      } else {
        return "{$dados["competencia_inicio"]} a {$dados["competencia_termino"]}";
      }
    }
  }

  function buscaDadosCobranca($cobranca_id) {
    $query = "
      SELECT
        coa_id AS id,
        cof_id AS formato_id,
        cli_id AS cliente_id,
        coa_parametros AS parametros
      FROM
        tab_cobrancas
      WHERE
        coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = @unserialize($dados["parametros"]);
      return $dados;
    }
  }
  
  #############
  ### Geral ###
  #############

  function formataDinheiro($valor, $moeda = 'R$') {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }

  function formataData($vencimento) {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    $vencimento = @explode("/", $vencimento);
    return $meses[intval($vencimento[0])]."/".$vencimento[1];
  }

	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}

}

?>
