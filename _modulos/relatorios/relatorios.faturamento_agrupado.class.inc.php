<?

if (!$_SESSION["usuario"]) {
  set_time_limit(0);
  
  date_default_timezone_set('America/Sao_paulo');
  
  require_once("../../global.inc.php");
  
  $relatorio = controle::instanciaObjeto("relatorios_faturamento_agrupado", $bancodedados, null);
  $relatorio->buscaItens('11/2008');
}

class relatorios_faturamento_agrupado {

  var $bancodedados;
  var $permissoes;

  function __construct($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
    set_time_limit(0);
  }

  ######################
  ### Funções Gerais ###
  ######################

  function buscaPeriodos() {
    $periodos[] = array ("tipo" => "MesAno", "nome" => "Vencimento", "name" => "vencimento", "where" => "DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%Y%m%d')");
    return $periodos;
  }

  function formataDinheiro($valor, $exibir_nulo = false, $moeda = 'R$') {
    if (!$valor && !$exibir_nulo) return "";
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor));
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }

  function formataVencimento($vencimento, $formato = "Y-m-d") {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    if ($formato == "Y-m-d") {
      $vencimento = @explode("-", $vencimento);
      return $meses[intval($vencimento[1])]."/".$vencimento[0];
    } elseif ($formato == "m/Y") {
      $vencimento = @explode("/", $vencimento);
      return $meses[intval($vencimento[0])]."/".$vencimento[1];
    }
  }

  function buscaCampos($vencimento) {
    $campos["descricao"] = "Descrição ($vencimento)";
    $campos["Faturado"] = array("faturado_quantidade" => "Quantidade", "faturado_valor" => "Valor") ;
    $campos["Recebido"] = array("recebido_quantidade" => "Quantidade", "recebido_valor" => "Valor") ;
    return $campos;
  }

  function formataData($vencimento) {
    $meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
    $vencimento = @explode("/", $vencimento);
    return $meses[intval($vencimento[0])]."/".$vencimento[1];
  }

  function ordena_multiarray($array, $campos) {
    if (is_array($array) && sizeof($array)) {
      $tamanho_chave = 0;
      reset($array);
      while (($array_ordenacao_atual = current($array)) != false) {
        for ($i=0; $i < sizeof($campos); $i++) {
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
        for ($i=0; $i < sizeof($campos); $i++) {
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

  ###################
  ### Busca Itens ###
  ###################

  function buscaItens($vencimento) {
    // Formata Data
    $vencimento_inicial = date('Y-m-d', strtotime(substr($vencimento, -4)."-".substr($vencimento, 0, 2)."-"."01"));
    $vencimento_final = date('Y-m-t', strtotime(substr($vencimento, -4)."-".substr($vencimento, 0, 2)."-"."01"));
    // Busca Itens
    $query = "
      SELECT
        coa_id AS id,
        coa_valor_original AS valor_original,
        coa_valor_juro AS valor_juro,
        coa_valor_desconto AS valor_desconto,
        coa_valor_acrescimo AS valor_acrescimo,
        coa_valor_decrescimo AS valor_decrescimo,
        coa_valor_bonificacao AS valor_bonificacao,
        coa_valor_total AS valor_total,
        cot_id AS status_id
      FROM
        tab_cobrancas
		INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        coa_data_vencimento BETWEEN '$vencimento_inicial' AND '$vencimento_final' AND
        cot_id IN (1, 2, 3, 4, 5, 9, 11) AND
        coa_pai IS NULL".($_SESSION["origem"] ? " AND
		tab_clientes.clo_id = {$_SESSION["origem"]}" : "");
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if (!in_array($dados["status_id"], array(2, 3, 4, 5, 9)) && ($segunda_via = $this->buscaSegundaVia($dados["id"]))) {
        $dados = $segunda_via;
      }
      // Busca Detalhes
      $detalhes = $this->buscaDetalhes($dados["id"]);
      if ($detalhes) {
        foreach ($detalhes as $detalhe) {
          // Faturado
          $itens[$detalhe["ordem"]][$detalhe["descricao"]]["faturado"]["quantidade"] += 1;
          $itens[$detalhe["ordem"]][$detalhe["descricao"]]["faturado"]["valor"] += $detalhe["valor"];
          // Recebido
          if ($dados["status_id"] != 1) {
            $itens[$detalhe["ordem"]][$detalhe["descricao"]]["recebido"]["quantidade"] += 1;
            $itens[$detalhe["ordem"]][$detalhe["descricao"]]["recebido"]["valor"] += $detalhe["valor"];
          }
        }
      }
      if ($dados["valor_bonificacao"]) {
        $itens[5]["Outros (Bonificação)"]["faturado"]["valor"] -= $dados["valor_bonificacao"];
        $itens[5]["Outros (Bonificação)"]["faturado"]["quantidade"] += 1;
        if ($dados["valor_desconto"] > 0) {
          $itens[5]["Outros (Bonificação)"]["recebido"]["quantidade"] += 1;
          $itens[5]["Outros (Bonificação)"]["recebido"]["valor"] -= $dados["valor_desconto"] > $dados["valor_bonificacao"] ? $dados["valor_bonificacao"] : $dados["valor_desconto"];
          $dados["valor_desconto"] -= $dados["valor_desconto"] > $dados["valor_bonificacao"] ? $dados["valor_bonificacao"] : $dados["valor_desconto"];
        }
      }
      $itens[5]["Outros (Decréscimo)"]["recebido"]["valor"] -= $dados["valor_desconto"] + $dados["valor_decrescimo"];
      $itens[5]["Outros (Acréscimo)"]["recebido"]["valor"] += $dados["valor_acrescimo"];
    }
    foreach ($itens as $ordem => $grupo) {
      foreach ($grupo as $descricao => $detalhes) {
        $novo["descricao"] = $descricao;
        $novo["faturado_quantidade"] = $detalhes["faturado"]["quantidade"] ? number_format($detalhes["faturado"]["quantidade"], 0, ",", ".") : "";
        $novo["faturado_valor"] = $this->formataDinheiro($detalhes["faturado"]["valor"]);
        $novo["recebido_quantidade"] = $detalhes["recebido"]["quantidade"] ? number_format($detalhes["recebido"]["quantidade"], 0, ",", ".") : "";
        $novo["recebido_valor"] = $this->formataDinheiro($detalhes["recebido"]["valor"]);
        $novo["ordem"] = $ordem;
        $lista[] = $novo;
      }
    }
    $lista = $this->ordena_multiarray($lista, array("ordem", "descricao"));
    //var_dump($lista);
    return $lista;
  }
   
  function buscaSegundaVia($cobranca_pai_id) {
    $query = "
      SELECT
        coa_id AS id,
        coa_valor_original AS valor_original,
        coa_valor_juro AS valor_juro,
        coa_valor_desconto AS valor_desconto,
        coa_valor_acrescimo AS valor_acrescimo,
        coa_valor_decrescimo AS valor_decrescimo,
        coa_valor_bonificacao AS valor_bonificacao,
        coa_valor_total AS valor_total,
        cot_id AS status_id
      FROM
        tab_cobrancas
      WHERE
        coa_pai = $cobranca_pai_id AND
        cot_id IN (1, 2, 3, 4, 5, 9)
      ORDER BY
        coa_data_vencimento DESC
      LIMIT
        0, 1";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }
  
  function buscaDetalhes($cobranca_id) {
    $query = "
      SELECT
        tab_cobrancas_detalhes.ass_id AS assinatura_id,
      	tab_cobrancas_detalhes.dec_id AS desconto_id,
      	tab_cobrancas_detalhes.ajf_id AS ajuste_id,
      	tab_cobrancas_detalhes.cod_descricao AS descricao,
      	tab_cobrancas_detalhes.cod_valor_final + IF(COUNT(detalhes_filhos.cod_id) > 0, SUM(detalhes_filhos.cod_valor_final), 0) AS valor
      FROM
        tab_cobrancas_detalhes
        LEFT OUTER JOIN tab_cobrancas_detalhes AS detalhes_filhos ON (tab_cobrancas_detalhes.cod_id = detalhes_filhos.cod_pai)
      WHERE
        tab_cobrancas_detalhes.coa_id = $cobranca_id AND
        tab_cobrancas_detalhes.cod_pai IS NULL
      GROUP BY
        tab_cobrancas_detalhes.cod_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($dados["assinatura_id"]) {
        $dados["descricao"] = "Serviço (".$this->buscaServicoAssinatura($dados["assinatura_id"]).")";
        $dados["ordem"] = 1;
      } elseif ($dados["ajuste_id"]) {
        if (substr($dados["descricao"], -18) == "(Estorno Migração)") {
          $dados["descricao"] = "Serviço (".$this->buscaServicoAjusteAssinatura($dados["ajuste_id"]).")";
          $dados["ordem"] = 1;
        } elseif (substr($dados["descricao"], -22) == "(Técnico » Instalação)") {
          $dados["descricao"] = "Instalação (".$this->buscaServicoAjusteContrato($dados["ajuste_id"]).")";
          $dados["ordem"] = 2;
        } else {
          $dados["ordem"] = 3;
        }
      } elseif (in_array($dados["descricao"], array("Tarifa Bancária", "Juro", "Decréscimo", "Acréscimo"))) {
        $dados["descricao"] = "Outros ({$dados["descricao"]})";
        $dados["ordem"] = 5;
      } else {
        $dados["ordem"] = 4;
      }
      $detalhes[] = $dados;
    }
    return $detalhes;
  }
  
  function buscaServicoAssinatura($assinatura_id) {
    $query = "
      SELECT
        tab_planos_servicos.pls_nome AS servico
      FROM
        tab_assinaturas
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_assinaturas.ass_id = $assinatura_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["servico"];
    }
  }
  
  function buscaServicoAjusteAssinatura($ajuste_id) {
    $query = "
      SELECT
        tab_planos_servicos.pls_nome AS servico
      FROM
        tab_ajustes_financeiros
        INNER JOIN tab_assinaturas ON (tab_ajustes_financeiros.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
        INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_ajustes_financeiros.ajf_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["servico"];
    } else {
      return "Outros";
    }
  }

  
  function buscaServicoAjusteContrato($ajuste_id) {
    $query = "
      SELECT
        tab_planos_servicos.pls_nome AS servico
      FROM
        tab_ajustes_financeiros
        INNER JOIN tab_contratos ON (tab_ajustes_financeiros.cor_id = tab_contratos.cor_id)
        INNER JOIN tab_planos_servicos ON (tab_contratos.pls_id = tab_planos_servicos.pls_id)
      WHERE
        tab_ajustes_financeiros.ajf_id = $ajuste_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["servico"];
    } else {
      return "Outros";
    }
  }

}

?>
