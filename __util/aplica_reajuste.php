<?

require_once("../global.inc.php");

$util = new util($bancodedados);
//$util->reajustarPlanos();
$util->reajustarAssinaturas();
//$util->enviarAviso();

class util {

  private $bancodedados;
  private $indice_reajuste = 1.085;
  private $planos_reajustados = false;
	
  ##################
  ### Construtor ###
  ##################

	function util($bancodedados) {
		$this->bancodedados = $bancodedados;
	}
	
	function reajustarPlanos() {
		
		$query = "
			SELECT
				tab_planos.pla_id AS id,
				tab_planos.pla_nome AS nome,
				tab_planos.pla_mensalidade AS mensalidade_original,
				tab_planos.pla_parametros AS parametros,
				IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_planos.pla_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.pla_id = tab_planos.pla_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.dec_data_termino IS NULL AND tab_descontos_concessoes.det_id != 4), 0) AS promocao,
				(SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_planos.pla_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.pla_id = tab_planos.pla_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.det_id = 4) AS bonificacao,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome
			FROM
				tab_planos
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				tab_planos.pls_id IN (1, 5, 11, 14, 20, 21, 28, 30, 32, 33) AND
				tab_planos.pla_nome NOT LIKE '%20mbps%' AND
				tab_planos.pla_nome NOT LIKE '%30mbps%' AND
				tab_planos.pla_nome NOT LIKE '%50mbps%' AND
				tab_planos.pla_mensalidade > 0 AND
				tab_planos.plu_id = 1
			ORDER BY
				tab_planos_servicos.pls_nome, tab_planos.pla_mensalidade";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
    	$dados["parametros"] = unserialize($dados["parametros"]);
    	
    	if (($dados["servico_id"] != 1 && $dados["servico_id"] != 3 && $dados["servico_id"] != 28) || ($dados["servico_id"] == 1 && $dados["parametros"]["pontos_velocidade_download"] <= 20971520) || $dados["parametros"]["pontos_velocidade_download"] <= 15728640) {
    	
	    	$dados["mensalidade_original"] = (int) $dados["mensalidade_original"];
	    	$dados["promocao"] = (int) $dados["promocao"];
	    	$dados["bonificacao"] = (int) $dados["bonificacao"];
	    	
	    	if ($dados["id"] == 624 || $dados["id"] == 627) {
	    		// CondomÃ­nios - 15MB
		    	$dados["mensalidade_ajustada"] = 11590;	    		
    		} elseif ($dados["id"] == 625 || $dados["id"] == 628) {
    			// CondomÃ­nios - 20MB
		    	$dados["mensalidade_ajustada"] = 12590;	    		
	    	} else {
		    	$dados["mensalidade_ajustada"] = (int) round($dados["mensalidade_original"] * $this->indice_reajuste);	    		
	    	}	    	
	    	
	    	if ($dados["promocao"]) {
	    		$dados["promocao_ajustada"] = (int) $dados["mensalidade_ajustada"] - ($dados["mensalidade_original"] - $dados["promocao"]);
	    	} else {
	    		$dados["promocao_ajustada"] = 0;
	    	}
	    	
	    	print "UPDATE tab_planos SET pla_mensalidade = {$dados["mensalidade_ajustada"]} WHERE pla_id = {$dados["id"]};\n";
	    	
	    	if ($dados["promocao"]) {
	    		print "UPDATE tab_descontos_concessoes SET dec_mensalidade = {$dados["promocao_ajustada"]} WHERE pla_id = {$dados["id"]} AND dcs_id = 5 AND dec_data_termino IS NULL AND det_id != 4;\n";
	    	}
	    	
	    	$dados["mensalidade_promocao_ate_vencimento"] = (int) $dados["mensalidade_ajustada"] - $dados["bonificacao"] - $dados["promocao_ajustada"];
	    	$dados["mensalidade_promocao_apos_vencimento"] = (int) $dados["mensalidade_ajustada"] - $dados["promocao_ajustada"];
	    	
	    	$dados["mensalidade_final_ate_vencimento"] = (int) $dados["mensalidade_ajustada"] - $dados["bonificacao"];
	    	$dados["mensalidade_final_apos_vencimento"] = (int) $dados["mensalidade_ajustada"];
	    	
	    	//var_dump($dados);
	    	
	    	//print "{$dados["servico_nome"]}\t{$dados["nome"]}\t{$this->formataDinheiro($dados["mensalidade_promocao_ate_vencimento"])}\t{$this->formataDinheiro($dados["mensalidade_promocao_apos_vencimento"])}\t{$this->formataDinheiro($dados["mensalidade_final_ate_vencimento"])}\t{$this->formataDinheiro($dados["mensalidade_final_apos_vencimento"])}\n";
	    	
    	}
    	
    }
    
	}
	
	function verificaCobrancasReajustadas() {
		
		$query = "
			SELECT
				coa_id AS id
			FROM
				tab_cobrancas_detalhes
				INNER JOIN reajuste ON (tab_cobrancas_detalhes.ass_id = reajuste.ass_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = reajuste.ass_id)
			WHERE
				cod_competencia_inicio > ass_data_cancelamento";
		
		$query = "
			UPDATE
				tab_cobrancas_detalhes
				INNER JOIN reajuste ON (tab_cobrancas_detalhes.ass_id = reajuste.ass_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = reajuste.ass_id)
			SET
				tab_cobrancas_detalhes.ass_id = reajuste.ass_reajustada
			WHERE
				cod_competencia_inicio > ass_data_cancelamento";
		
	}
	
	var $planos = array();
	var $data_ativacao = array(1 => "2015-12-01", 2 => "2016-01-01");
	
	function reajustarAssinaturas($cliente_id = null) {
		
		$this->carregaPlanos();
		
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.cli_id AS cliente_id,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.ass_parametros AS parametros,
				tab_planos.pls_id AS servico_id,
				IF(tab_assinaturas.pli_id = 1, 1, 0) AS pospago
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE".($cliente_id ? "
				tab_assinaturas.cli_id = $cliente_id AND" : "")."
				tab_planos.pls_id IN (1, 3, 5, 9, 11, 12, 14, 20, 21, 28, 30, 32, 33) AND
				(tab_planos.pls_id != 3 OR tab_assinaturas.ass_data_ativacao >= '2016-01-01') AND
				tab_planos.pla_nome NOT LIKE '%20mbps%' AND
				tab_planos.pla_nome NOT LIKE '%30mbps%' AND
				tab_planos.pla_nome NOT LIKE '%50mbps%' AND
				tab_assinaturas.ass_mensalidade > 0 AND
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.ass_id NOT IN (SELECT ass_id FROM reajuste) AND
				tab_assinaturas.ass_id NOT IN (SELECT ass_reajustada FROM reajuste)
			ORDER BY
				RAND()";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
    	$dados["parametros"] = unserialize($dados["parametros"]);
    	
    	$dados["mensalidade"] = (int) $dados["mensalidade"];
    	$dados["pospago"] = (bool) $dados["pospago"];
    	
			if ($dados["parametros"]["pontos_velocidade_download"]) {
	     	$dados["velocidade"] = (int) $dados["parametros"]["pontos_velocidade_download"];
	    }elseif ($dados["parametros"]["autenticacoes_velocidade_download"]) {
	    	$dados["velocidade"] = (int) $dados["parametros"]["autenticacoes_velocidade_download"];
	    }
	      
	    $dados_plano = $this->buscaDadosPlano($dados["servico_id"], $dados["velocidade"]);
	      
      if (!$this->planos_reajustados || !$dados_plano || ($dados["mensalidade"] < $dados_plano["mensalidade"])) {
      		
    		$dados["mensalidade_ajustada"] = (int) round($dados["mensalidade"] * $this->indice_reajuste);
    		
    		if ($this->planos_reajustados && $dados_plano && $dados["mensalidade_ajustada"] > $dados_plano["mensalidade"]) {
    			$dados["mensalidade_ajustada"] = $dados_plano["mensalidade"];
    		}
    		
//    		$this->reajustarAssinatura($dados["id"], $dados["mensalidade"], $dados["mensalidade_ajustada"]);
    		
				print "{$dados["cliente_id"]}\t{$dados["velocidade"]}\t{$dados["mensalidade"]}\t{$dados["mensalidade_ajustada"]}\n";
				
      } else {
      		
				print "{$dados["cliente_id"]}\t{$dados["velocidade"]}\t{$dados["mensalidade"]}\tNAO REAJUSTADO\n";
      		
      }
      
    }
    
	}
	
	function reajustarAssinatura($assinatura_id, $mensalidade_original, $mensalidade_ajustada) {
		
		$query = "
			SELECT
				ass_id AS id,
				IFNULL((SELECT reajuste.ass_reajustada FROM reajuste WHERE reajuste.ass_id = tab_assinaturas.ass_pai), ass_pai) AS pai_id,
				ass_data_ativacao AS data_ativacao,
				ass_mensalidade AS mensalidade,
				cli_id AS cliente_id,
				pla_id AS plano_id,
				pli_id AS sistema_id,
				ass_parametros AS parametros,
				ass_mensalidade - IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5), 0) AS mensalidade_final,
				(SELECT tab_contratos.cor_id FROM rel_ass_cor_estacontido INNER JOIN tab_contratos ON (rel_ass_cor_estacontido.cor_id = tab_contratos.cor_id) WHERE rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id AND tab_contratos.cor_pai IS NULL) AS contrato_id
			FROM
				tab_assinaturas
			WHERE
				ass_id = $assinatura_id";
		
    $query = $this->bancodedados->query($query);
    
    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
    	$dados["pai_id"] = $dados["pai_id"] ? $dados["pai_id"] : "NULL";
    	$dados["mensalidade_final"] = (int) $dados["mensalidade_final"] <= 0 ? 0 : (int) $dados["mensalidade_final"];
    	
    	if (strtotime($this->data_ativacao[$dados["sistema_id"]]) > strtotime($dados["data_ativacao"])) {
    		$data_ativacao = $this->data_ativacao[$dados["sistema_id"]];
    	} else {
    		$data_ativacao = $dados["data_ativacao"];
    	}
    	
    	$this->bancodedados->query("
    		INSERT INTO
    			tab_assinaturas (
		    		ass_pai,
		    		ass_data_cadastro,
		    		ass_data_fechamento,
		    		ass_data_confirmacao,
		    		ass_data_ativacao,
		    		ass_mensalidade,
		    		ass_parametros,
		    		ass_migrada,
		    		cli_id,
		    		pla_id,
		    		pli_id,
		    		ast_id
    			) VALUES (
    				{$dados["pai_id"]},
    				'{$data_ativacao}',
    				'{$data_ativacao}',
    				'{$data_ativacao}',
    				'{$data_ativacao}',
    				{$mensalidade_ajustada},
    				'{$dados["parametros"]}',
    				{$assinatura_id},
    				{$dados["cliente_id"]},
    				{$dados["plano_id"]},
    				{$dados["sistema_id"]},
    				2
    			)");
    	
    	$assinatura_migrada_id = $this->bancodedados->ultimoIdInserido();
    	
    	$this->bancodedados->query("UPDATE tab_assinaturas SET ast_id = 4, ass_data_cancelamento = DATE_SUB('{$data_ativacao}', INTERVAL 1 DAY) WHERE ass_id = $assinatura_id");
    	
    	$this->bancodedados->query("UPDATE rel_ass_cor_estacontido SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]} AND cor_id = {$dados["contrato_id"]}");
    	
    	$this->bancodedados->query("UPDATE rel_ass_cor_estacontido SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]} AND cor_id = (SELECT cor_id FROM tab_contratos WHERE cor_pai = {$dados["contrato_id"]} AND crs_id < 18)");
    	
    	$this->bancodedados->query("UPDATE tab_assinaturas SET ass_pai = $assinatura_migrada_id WHERE ass_pai = {$dados["id"]}");
    	
    	$this->bancodedados->query("UPDATE tab_clientes_pontos SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]}");
    	$this->bancodedados->query("UPDATE tab_clientes_autenticacoes SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]}");
    	$this->bancodedados->query("UPDATE tab_clientes_emails SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]}");
    	$this->bancodedados->query("UPDATE tab_dominios SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]}");
    	$this->bancodedados->query("UPDATE tab_clientes_comodatos SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]}");
    	$this->bancodedados->query("UPDATE rel_saj_ass_contem SET ass_id = $assinatura_migrada_id WHERE ass_id = {$dados["id"]}");
    	
    	$this->bancodedados->query("INSERT INTO tab_ajustes_financeiros (ajf_pai, ajf_competencia_inicio, ajf_competencia_termino, ajf_datahora, ajf_parcelas, ajf_valor, ajf_valor_total, ajf_valor_tipo, ajf_nf_numero, ajf_nf_emissao, ajf_nf_valor, con_id, cli_id, ass_id, cor_id, afc_id, aft_id, afs_id) SELECT ajf_pai, ajf_competencia_inicio, ajf_competencia_termino, ajf_datahora, ajf_parcelas, ajf_valor, ajf_valor_total, ajf_valor_tipo, ajf_nf_numero, ajf_nf_emissao, ajf_nf_valor, con_id, cli_id, $assinatura_migrada_id, cor_id, afc_id, aft_id, afs_id FROM tab_ajustes_financeiros WHERE ass_id = {$dados["id"]}");
    	
    	$ajuste = $mensalidade_ajustada - $dados["mensalidade"];
    	
    	$descontos = $this->buscaDescontosAtivos($assinatura_id);
    	
    	for ($i = 0; $i < count($descontos); $i++) {
    		
    		if ($ajuste > 0 && ($descontos[$i]["termino"] || $dados["mensalidade_final"] == 0) && $descontos[$i]["mensalidade"] > 1) {
    			$descontos[$i]["mensalidade"] += $ajuste;
    			$ajuste = 0;
    		}
    		
    		$descontos[$i]["dec_pai"] = $descontos[$i]["dec_pai"] ? $descontos[$i]["dec_pai"] : "NULL";
    		$descontos[$i]["termino"] = $descontos[$i]["termino"] ? "'{$descontos[$i]["termino"]}'" : "NULL";
    		
    		$this->bancodedados->query("
    			INSERT INTO
    				tab_descontos_concessoes (
    					dec_pai,
    					dec_data_cadastro,
    					dec_data_inicio,
    					dec_data_termino,
    					dec_mensalidade,
    					ass_id,
    					des_id,
    					det_id,
    					dcs_id
    				) VALUES (
    					{$descontos[$i]["dec_pai"]},
    					'{$data_ativacao}',
    					'{$data_ativacao}',
    					{$descontos[$i]["termino"]},
    					{$descontos[$i]["mensalidade"]},
    					{$assinatura_migrada_id},
    					{$descontos[$i]["desconto_id"]},
    					{$descontos[$i]["tipo_id"]},
    					5
    				)
    		");
    					
    		$desconto_id = $this->bancodedados->ultimoIdInserido();
    		
    		$this->bancodedados->query("UPDATE tab_descontos_concessoes SET dcs_id = 6 WHERE dec_id = {$descontos[$i]["id"]}");
    		
    		$this->bancodedados->query("UPDATE rel_dec_cor_estacontido SET dec_id = $desconto_id WHERE dec_id = {$descontos[$i]["id"]} AND cor_id = {$dados["contrato_id"]}");
    					
    	}
    	
    	$this->bancodedados->query("INSERT INTO reajuste (ass_id, cli_id, processamento, ass_reajustada, mensalidade_original, mensalidade_final) VALUES ({$dados["id"]}, {$dados["cliente_id"]}, NOW(), $assinatura_migrada_id, $mensalidade_original, $mensalidade_ajustada)");
    	
    }

	}
	
	function buscaDescontosAtivos($assinatura_id) {
		
		$descontos = array();
		
		$query = "
			SELECT
				dec_id AS id,
				dec_pai AS pai_id,
				dec_data_termino AS termino,
				dec_mensalidade AS mensalidade,
				des_id AS desconto_id,
				det_id AS tipo_id,
				(SELECT descontos_plano.pla_id FROM tab_descontos_concessoes descontos_plano WHERE descontos_assinatura.dec_pai = descontos_plano.dec_id) AS plano_id
			FROM
				tab_descontos_concessoes descontos_assinatura
			WHERE
				ass_id = $assinatura_id AND
				dec_mensalidade > 0 AND
				dcs_id = 5
			ORDER BY
				dec_mensalidade DESC";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$descontos[] = $dados;
    }
    
    return $descontos;
    
	}
	
	function buscaDadosPlano($servico_id, $velocidade) {
		if (!$velocidade) {
			return null;
		}
		if (array_key_exists($velocidade, $this->planos[$servico_id])) {
			return $this->planos[$servico_id][$velocidade];
		}
		$dados_plano = null;
		foreach ($this->planos[$servico_id] as $plano_velocidade => $plano_dados) {
			if ($plano_velocidade < $velocidade) {
				$dados_plano = $plano_dados;
			} else {
				return $plano_dados;
			}
		}
		return null;
	}
	
	function carregaPlanos() {
		
		$query = "
			SELECT
				tab_planos.pla_id AS id,
				tab_planos.pls_id AS servico_id,
				tab_planos.pla_mensalidade AS mensalidade,
				tab_planos.pla_parametros AS parametros
			FROM
				tab_planos
			WHERE
				tab_planos.pls_id IN (1, 28) AND
				tab_planos.plu_id = 1
			-- LIMIT 50";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
    	$dados["parametros"] = unserialize($dados["parametros"]);
    	
    	$dados["velocidade"] = (int) $dados["parametros"]["pontos_velocidade_download"];
    	$dados["mensalidade"] = (int) $dados["mensalidade"];
    	
    	if (!array_key_exists($dados["servico_id"], $this->planos)) {
    		$this->planos[$dados["servico_id"]] = array();
    	}
    	
    	$this->planos[$dados["servico_id"]][$dados["velocidade"]] = array("velocidade" => $dados["velocidade"], "mensalidade" => $dados["mensalidade"]);
    }
    
    foreach ($this->planos AS $servico_id => $planos) {
    	ksort($this->planos[$servico_id]);
    }
    
	}
	
	function gerarRelatorio() {
		
		print utf8_encode("ID\tAtivaï¿½ï¿½o\tServiï¿½o\tVelocidade\tMensalidade Original\tBonificacao\tMensalide Atï¿½ Vencimento\n");
		
		$query = "
			SELECT
				tab_assinaturas.cli_id AS cliente_id,
				tab_planos_servicos.pls_nome AS servico,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS ativacao,
				tab_assinaturas.ass_mensalidade - IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.dec_data_termino IS NULL AND tab_descontos_concessoes.det_id != 4), 0) AS mensalidade,
				(SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.det_id = 4) AS bonificacao,
				tab_assinaturas.ass_parametros AS parametros
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				tab_planos.pls_id IN (1) AND -- condominios
				-- tab_planos.pls_id IN (5, 28) AND -- casas
				tab_assinaturas.ast_id = 2
			-- LIMIT 50";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
    	$dados["parametros"] = unserialize($dados["parametros"]);
    	
      if ($dados["parametros"]["autenticacoes_velocidade_download"]) {
      	$dados["velocidade"] = $this->formataVelocidade($dados["parametros"]["autenticacoes_velocidade_download"]);
      } elseif ($dados["parametros"]["pontos_velocidade_download"]) {
      	$dados["velocidade"] = $this->formataVelocidade($dados["parametros"]["pontos_velocidade_download"]);
      } else {
      	$dados["velocidade"] = "";
      }
      
      $dados["mensalidade_ate_vencimento"] = $dados["mensalidade"] > 0 ? $dados["mensalidade"] - $dados["bonificacao"] : 0;
      
      $dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"]);
      $dados["bonificacao"] = $this->formataDinheiro($dados["bonificacao"]);
      $dados["mensalidade_ate_vencimento"] = $this->formataDinheiro($dados["mensalidade_ate_vencimento"]);
      
      print utf8_encode("{$dados["cliente_id"]}\t{$dados["ativacao"]}\t{$dados["servico"]}\t{$dados["velocidade"]}\t{$dados["mensalidade"]}\t{$dados["bonificacao"]}\t{$dados["mensalidade_ate_vencimento"]}\n");
      
    }
    
	}
  
  function formataVelocidade($velocidade) {
 	  $indice = floor(log($velocidade, 1024));
 	  $indice = $indice < 1 ? 1 : $indice;
 	  return round($velocidade / pow(1024, $indice), 1)." ".$this->buscaUnidade("bps", $indice);

  }

	function buscaUnidade($unidade, $indice = null) {
  	$unidades["1"] = "K$unidade";
	  $unidades["2"] = "M$unidade";
	  $unidades["3"] = "G$unidade";
	  $unidades["4"] = "T$unidade";
		return $indice ? $unidades[$indice] : $unidades;
	}
	  
  function formataDinheiro($valor, $moeda = 'R$', $exibir_nulo = false) {
    $valor_formatado = preg_replace("/[^0-9]/", "", intval($valor, 10));
    if (!$valor_formatado && !$exibir_nulo) return false;
    $valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
    if ($moeda) $valor_formatado = "$moeda $valor_formatado";
    if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
    return $valor_formatado;
  }
  
  function relatorioAssinaturas() {
  			
		$query = "
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_migrada	AS migrada,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS ativacao,
				tab_assinaturas.ass_mensalidade - IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.dec_data_termino IS NULL), 0) AS mensalidade,
				IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.dec_data_termino IS NOT NULL), 0) AS desconto_temporario,
				tab_assinaturas.ass_parametros AS parametros,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_planos.pls_id AS servico_id,
				IF(tab_assinaturas.pli_id = 1, 1, 0) AS pospago
			FROM
				tab_assinaturas
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_planos.pls_id IN (3) AND
				tab_planos.pla_nome NOT LIKE '%FLEX%' AND
				tab_planos.pla_nome NOT LIKE '%LIGHT%' AND
				tab_assinaturas.ast_id = 2 -- AND
				-- tab_assinaturas.ass_id NOT IN (SELECT ass_id FROM reajuste) AND
				-- tab_assinaturas.ass_id NOT IN (SELECT ass_reajustada FROM reajuste) AND
				-- tab_assinaturas.ass_data_ativacao < '2015-12-01'";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
	    $dados["mensalidade"] = (int) $dados["mensalidade"];
    	
    	if ($dados["mensalidade"] > 0) {
    		
    		$anterior = $this->buscaMensalidadeAnterior($dados["migrada"], $dados["plano_id"], $dados["mensalidade"]);
    		
    		if ($anterior) {
    			$dados["mensalidade_anterior"] = $anterior["mensalidade"];
    			$dados["migracao"] = $anterior["cancelamento"];
    			$dados["ativacao"] = $anterior["ativacao"];
    		} else {
    			$dados["mensalidade_anterior"] = "";
    			$dados["migracao"] = "";
    		}
    	
    		$dados["mensalidade_reajustada"] = (int) round($dados["mensalidade"] * $this->indice_reajuste);;
    		
  			print "{$dados["cliente_id"]}\t{$dados["cliente_nome"]}\t{$dados["plano_nome"]}\t{$dados["ativacao"]}\t{$dados["migracao"]}\t{$this->formataDinheiro($dados["mensalidade_anterior"])}\t{$this->formataDinheiro($dados["mensalidade"])}\t{$this->formataDinheiro($dados["mensalidade_reajustada"])}\t{$this->formataDinheiro($dados["desconto_temporario"])}\n";
  			
    	}
    	
    }
    	
  }
    
    function buscaMensalidadeAnterior($assinatura_id, $plano_id, $mensalidade) {
 			
			$query = "
				SELECT
					tab_assinaturas.ass_id AS id,
					DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS ativacao,
					DATE_FORMAT(tab_assinaturas.ass_data_cancelamento, '%d/%m/%Y') AS cancelamento,
					tab_assinaturas.ass_mensalidade - IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dec_data_termino IS NULL), 0) AS mensalidade,
					ass_migrada AS migrada
				FROM
					tab_assinaturas
				WHERE
					pla_id = $plano_id AND
					ass_id = $assinatura_id";
			
	    $query = $this->bancodedados->query($query);
	    
	    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
	    	
	    	$dados["mensalidade"] = (int) $dados["mensalidade"];
	    	//var_dump($dados);
	    	//print "***{$dados["mensalidade"]} < $mensalidade *** \n";
	    	if ($dados["mensalidade"] > 0 && $dados["mensalidade"] < $mensalidade) {
	    		return $dados;
	    	} elseif ($dados["migrada"]) {
	    		return $this->buscaMensalidadeAnterior($dados["migrada"], $plano_id, $mensalidade);
	    	}
	    	
	    }
    	
   }
   
   function enviarAviso() {
   	
		$query = "
			SELECT DISTINCT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome
			FROM
				tab_clientes
				INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				(SELECT COUNT(*) FROM tab_clientes_contatos_emails WHERE tab_clientes_contatos_emails.cli_id = tab_clientes.cli_id) > 0 AND
				tab_planos.pls_id IN (1, 3, 5, 9, 11, 12, 14, 20, 21, 28, 30, 32, 33) AND
				(tab_planos.pls_id != 3 OR tab_assinaturas.ass_data_ativacao >= '2016-01-01') AND
				tab_planos.pla_nome NOT LIKE '%20mbps%' AND
				tab_planos.pla_nome NOT LIKE '%30mbps%' AND
				tab_planos.pla_nome NOT LIKE '%50mbps%' AND
				tab_assinaturas.ass_mensalidade > 0 AND
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.cli_id NOT IN (SELECT cli_id FROM reajuste_email)";
		
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	if ($this->enviarEmailAviso($dados["nome"], $this->buscaContatosEmails($dados["id"]))) {
    		$this->bancodedados->query("INSERT INTO reajuste_email (cli_id, datahora) VALUES ({$dados["id"]}, NOW())");
    	}
    }
   	
  }

  function buscaContatosEmails($cliente_id) {
  	
  	$emails = array();
  	
    $query = "
      SELECT
        CONCAT(ccm_usuario, '@', ccm_dominio) AS email
      FROM
        tab_clientes_contatos_emails
      WHERE
        cli_id = $cliente_id
      ORDER BY
        email";
    
    $query = $this->bancodedados->query($query);
    
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[] = $dados["email"];
    }
    
    return $emails;
    
	}
    
	function enviarEmailAviso($nome, $emails) {
   
		require_once "../_global/PHPMailer/PHPMailerAutoload.php";

		$mail = new PHPMailer();

		$mail->isSMTP();
		$mail->Host = "intramail.oletelecom.com.br";
		$mail->SMTPAuth = true;
		$mail->Username = "contato@oletelecom.com.br";
		$mail->Password = "Hy3;RqM?Wd7c!V#RuEmhg@Tffo4nH";
		$mail->SMTPSecure = "tls";
		$mail->Port = 587;     
		
		$mail->From = "contato@oletelecom.com.br";
		$mail->FromName = "Olé Telecom";
		$mail->Subject = "Aviso";

		$mail->isHTML(true);

		for ($i = 0; $i < count($emails); $i++) {
			$mail->addAddress($emails[$i], $nome);
		}
		
		$mail->Body = "
<html>
	<head>
	</head>
	<body style=\"font-family: Verdana, Arial, sans-serif; font-size: 12px; color: #444444;\">
		<div style=\"width: 480px;text-align:justify\">
			<p>Prezado assinante,</p>
			<p>Frente às alterações ocorridas na Política Econômica Nacional, impactando diretamente em nossa planilha de custos, informamos que à partir do vencimento de Janeiro/2017 estaremos aplicando o reajuste de 8,50% sobre a nossa tabela de preços, com base na disposição da ANATEL, através da Resolução nº 632, de 7 de março de 2014.</p>
			<p>Contamos com a vossa compreensão, pois nosso principal objetivo é a melhora constante na prestação de serviços, primando pela qualidade e excelência, visando manter e ampliar a sua satisfação.</p>
			<p>Atenciosamente,</p>
  		<img src=\"cid:logo@oletelecom.com.br\" width=\"181\" height=\"105\">
		</div>
	</body>
</html>";

	  $mail->addEmbeddedImage("../imagens/logo_ole.jpg", "logo@oletelecom.com.br");
		
		if ($mail->send()) {
			$mail->smtpClose();
			sleep(2);
			return true;
		} else {
			$mail->smtpClose();
			return false;
		}

	}
  
}

?>
