<?

require_once("../global.inc.php");

$regera_faturas_ciclos = new gera_faturas_cobrancas($bancodedados);
$regera_faturas_ciclos->corrigirDistribuicaoArredondamento();
//$regera_faturas_ciclos->recalculaFaturaLoteCobranca();


class gera_faturas_cobrancas {

  private $bancodedados;
	private $faturas_lotes;
	
  ##################
  ### Construtor ###
  ##################

	function gera_faturas_cobrancas($bancodedados) {
		$this->bancodedados = $bancodedados;
    $this->faturas_lotes = controle::instanciaObjeto("faturas_lotes", $this->bancodedados, null, false);
	}
	
	function gerar() {
		$query = "
			SELECT
				fal_id AS id,
				fal_ciclo AS ciclo
			FROM
				tab_faturas_lotes
			WHERE
				fal_data_ciclo >= '2012-11-01'
				-- fal_id != 393
				-- fal_id = 2136
				-- fls_id = 1
			ORDER BY
				fal_data_ciclo,
				fal_ciclo_sequencia
			-- LIMIT 200, 100";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$this->faturas_lotes->geraFaturas($dados["id"]);
    }
	}

	function corrigeFaturasDuplicadas() {
		$query = "
			SELECT
				coa_id AS cobranca_id
			FROM
				tab_faturas_lotes_cobrancas
			WHERE
				1
			GROUP BY
				coa_id
			HAVING
				COUNT(fco_id) > 1
			ORDER BY
				fco_id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$faturas = $this->buscaFaturasCobranca($dados["cobranca_id"]);
    	$faturas_lotes_cobrancas = $this->buscaFaturasLotesCobrancas($dados["cobranca_id"]);
    	if (count($faturas) == count($faturas_lotes_cobrancas)) {
	    	for ($i = 0; $i < count($faturas); $i++) {
	    		$this->bancodedados->query("UPDATE tab_faturas SET coa_id = {$faturas[$i]["cobranca_id"]} WHERE fat_id = {$faturas[$i]["fatura_id"]}");
	    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$faturas_lotes_cobrancas[$i]["id"]}, fat_id = {$faturas[$i]["fatura_id"]} WHERE coa_id = {$faturas[$i]["cobranca_id"]}");
	    		$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET coa_id = {$faturas[$i]["cobranca_id"]}, fat_id = {$faturas[$i]["fatura_id"]} WHERE fco_id = {$faturas_lotes_cobrancas[$i]["id"]}");
	    	}
    	} else {
    		print "ERRO";
    		var_dump($faturas);
    		var_dump($faturas_lotes_cobrancas);
    		die();
    	}
    }
	}
	
	function buscaFaturasLotesCobrancas($cobranca_id) {
		$faturas_lotes_cobrancas = array();
		$query = "
			SELECT
				fco_id AS id
			FROM
				tab_faturas_lotes_cobrancas
			WHERE
				coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$faturas_lotes_cobrancas[] = $dados;
    }
    return $faturas_lotes_cobrancas;
	}
	
	function buscaFaturasCobranca($cobranca_id) {
		$faturas = array();
		$query = "
			SELECT
				tab_faturas.fat_id AS fatura_id,
				tab_faturas.fat_codigo AS codigo,
				tab_cobrancas.coa_id AS cobranca_id
			FROM
				tab_faturas
				INNER JOIN tab_cobrancas ON (tab_faturas.fat_codigo = tab_cobrancas.coa_codigo)
			WHERE
				tab_faturas.coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$faturas[] = $dados;
    }
    return $faturas;
	}
	
	function corrigir() {
		$query = "
			SELECT
				tab_cobrancas.coa_id,
				tab_cobrancas.coa_codigo,
				fco_valor_scm,
				fco_valor_juro,
				fco_valor_instalacao,
				fco_valor_equipamentos,
				fco_valor_servicos,
				fco_valor_garantia,
				coa_valor_total,
				coa_valor_juro,
				coa_valor_acrescimo,
				coa_valor_decrescimo,
				coa_valor_desconto,
				CAST(coa_valor_total AS SIGNED) - (CAST(fco_valor_scm AS SIGNED) + CAST(fco_valor_juro AS SIGNED) + CAST(fco_valor_instalacao AS SIGNED) + CAST(fco_valor_equipamentos AS SIGNED) + CAST(fco_valor_servicos AS SIGNED) + CAST(fco_valor_garantia AS SIGNED)) AS diferenca
			FROM
				tab_cobrancas
				INNER JOIN tab_faturas_lotes_cobrancas ON (tab_cobrancas.coa_id = tab_faturas_lotes_cobrancas.coa_id)
			WHERE
				cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1) AND
				fco_valor_scm + fco_valor_juro + fco_valor_instalacao + fco_valor_equipamentos + fco_valor_servicos + fco_valor_garantia != coa_valor_total";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	
    	if ($dados["coa_valor_juro"] > 0 && $dados["diferenca"] == $dados["coa_valor_acrescimo"] && $dados["coa_valor_acrescimo"] < 1000) {
    		$dados["fco_valor_juro"] += $dados["diferenca"];
    		$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET fco_valor_juro = {$dados["fco_valor_juro"]} WHERE coa_id = {$dados["coa_id"]}");
    		print "{$dados["coa_codigo"]}\n";
    	} elseif ($dados["fco_valor_juro"] > 0 && $dados["diferenca"] < 0 && $dados["fco_valor_juro"] + $dados["diferenca"] >= 0) {
    		$dados["fco_valor_juro"] += $dados["diferenca"];
    		$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET fco_valor_juro = {$dados["fco_valor_juro"]} WHERE coa_id = {$dados["coa_id"]}");
    		print "{$dados["coa_codigo"]}\n";
    	}
    	
    }
	}
	
	function reassociar() {
//		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE 1");
		
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
				1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados["fatura_id"] = $dados["fatura_id"] ? $dados["fatura_id"] : "NULL";
			// Seta fatura gerada na cobranca
			$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$dados["fatura_lote_cobranca_id"]}, fat_id = {$dados["fatura_id"]} WHERE coa_id = {$dados["cobranca_id"]}");
			if ($dados["cobranca_pai_id"]) {
				// Seta fatura gerada na cobranca pai
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$dados["fatura_lote_cobranca_id"]}, fat_id = {$dados["fatura_id"]} WHERE coa_id = {$dados["cobranca_pai_id"]} AND (SELECT COUNT(*) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id) = 0");
				// Seta fatura gerada nas cobrancas irmãs
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$dados["fatura_lote_cobranca_id"]}, fat_id = {$dados["fatura_id"]} WHERE coa_pai = {$dados["cobranca_pai_id"]} AND (SELECT COUNT(*) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id) = 0");
			} else {
				// Seta fatura gerada nas cobrancas filhas
				$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$dados["fatura_lote_cobranca_id"]}, fat_id = {$dados["fatura_id"]} WHERE coa_pai = {$dados["cobranca_id"]} AND (SELECT COUNT(*) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id) = 0");
			}
    }
	}
	
	function removeFaturaLoteCobranca() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_cobrancas.coa_data_processamento >= '2014-01-01' AND
				tab_faturas_lotes_cobrancas.fat_id IS NULL AND
				tab_faturas_lotes_cobrancas.fco_valor_scm > 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	print "{$dados["codigo"]}\n";
    	$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_id = {$dados["cobranca_id"]}");
    	$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas SET fco_id = {$dados["fatura_lote_cobranca_id"]}");
    }
	}

	function removeFaturaLoteCobrancaIndevida1() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_faturas_lotes_cobrancas.fco_valor_scm > 0 AND
				tab_faturas_lotes_cobrancas.fat_id IS NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$fatura = $this->buscaFaturaOutraVia($dados);
    	if ($fatura) {
    		print "{$dados["codigo"]}\n";
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE coa_id = {$dados["cobranca_id"]}");
    		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas SET fco_id = {$dados["fatura_lote_cobranca_id"]}");
    	}
    }
	}
	
	function removeFaturaLoteCobrancaIndevida2() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_faturas_lotes_cobrancas.fat_id IS NULL AND
				cot_id NOT IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$fatura = $this->buscaFaturaOutraVia($dados);
    	if ($fatura) {
    		print "{$dados["codigo"]}\n";
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE coa_id = {$dados["cobranca_id"]}");
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    	} else {
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    	}
    }
	}
	
	function removeFaturaLoteCobrancaIndevida3() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.fco_id = tab_cobrancas.fco_id)
			WHERE
				tab_faturas_lotes_cobrancas.fco_valor_scm > 0 AND
				tab_faturas_lotes_cobrancas.fat_id IS NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$fatura = $this->buscaFaturaOutraVia($dados);
    	if ($fatura) {
    		print "{$dados["codigo"]}\n";
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE coa_id = {$dados["cobranca_id"]}");
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    	}
    }
	}
		
	function removeFaturaLoteCobrancaIndevida4() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
				-- tab_cobrancas.coa_id = 1669647 AND
				tab_faturas_lotes_cobrancas.fco_valor_scm > 0 AND
				tab_faturas_lotes_cobrancas.fat_id IS NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$fatura = $this->buscaFaturaOutraVia($dados);
    	if ($fatura) {
    		print "{$dados["codigo"]}\n";
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE coa_id = {$dados["cobranca_id"]}");
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = {$fatura["fatura_lote_cobranca_id"]}, fat_id = {$fatura["fatura_id"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    	}
    }
	}
		
	function removeFaturaLoteCobrancaIndevida5() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_cobrancas ON (tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_cobrancas.cot_id IN (6, 7, 8, 12) AND
				tab_faturas_lotes_cobrancas.fat_id IS NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    		print "{$dados["codigo"]}\n";
    		$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    		$this->bancodedados->query("DELETE FROM tab_faturas_lotes_cobrancas WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    }
	}
	
	function buscaFaturaOutraVia($dados) {
		$cobrancas = array();
		
		if ($dados["cobranca_pai_id"]) {
			$cobrancas[] = $dados["cobranca_pai_id"];
			$query = "
				SELECT
					coa_id AS id
				FROM
					tab_cobrancas
				WHERE
					coa_pai = {$dados["cobranca_pai_id"]} AND
					coa_id != {$dados["cobranca_id"]}";
	    $query = $this->bancodedados->query($query);
	    while (($cobranca = $this->bancodedados->resultadoArray($query)) != false) {
				$cobrancas[] = $cobranca["id"];
	    }
		} else {
			$query = "
				SELECT
					coa_id AS id
				FROM
					tab_cobrancas
				WHERE
					coa_pai = {$dados["cobranca_id"]}";
	    $query = $this->bancodedados->query($query);
	    while (($cobranca = $this->bancodedados->resultadoArray($query)) != false) {
				$cobrancas[] = $cobranca["id"];
	    }
		}
		
		$cobrancas = implode(", ", $cobrancas);
		
		$query = "
			SELECT
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_faturas_lotes_cobrancas
			WHERE
				tab_faturas_lotes_cobrancas.coa_id IN ($cobrancas) AND
				tab_faturas_lotes_cobrancas.fat_id IS NOT NULL";
    $query = $this->bancodedados->query($query);
    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	return $dados;
    }
	}
	
	function corrigirFaturaLoteCobranca() {
		$query = "
			SELECT
        tab_cobrancas.coa_id AS id,
        tab_cobrancas.cli_id AS cliente_id,
        tab_cobrancas.coa_codigo AS codigo,
        tab_cobrancas.coa_valor_total AS valor_total,
        tab_cobrancas.coa_valor_original AS valor_original,
        tab_cobrancas.coa_valor_desconto AS valor_desconto,
        tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo,
        tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo,
        tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
        tab_cobrancas.coa_valor_juro AS valor_juro,
        tab_cobrancas.coa_data_geracao AS data_geracao,
        tab_cobrancas.coa_data_processamento AS data_processamento,
        tab_cobrancas.coa_data_vencimento AS data_vencimento,
        tab_cobrancas.cot_id AS status_id,
        tab_cobrancas.coa_pai AS coa_pai,
        tab_cobrancas.cop_id AS tipo_id,
        tab_cobrancas.cof_id AS formato_id,
        tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id
			FROM
				tab_cobrancas
				INNER JOIN tab_faturas_lotes_cobrancas ON (tab_cobrancas.fco_id = tab_faturas_lotes_cobrancas.fco_id)
			WHERE
				fco_valor_scm = 0 AND
				cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1) AND
				-- tab_faturas_lotes_cobrancas.fat_id IS NULL AND
				fco_valor_scm + fco_valor_juro + fco_valor_instalacao + fco_valor_equipamentos + fco_valor_servicos + fco_valor_garantia != coa_valor_total
			LIMIT 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$fatura_itens = $this->faturas_lotes->buscaFaturasItens();
    	
    }
	}
	
	function desassociaPagamentoOutraVia() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS fatura_lote_cobranca_id
			FROM
				tab_cobrancas
				INNER JOIN tab_faturas_lotes_cobrancas ON (tab_cobrancas.fco_id = tab_faturas_lotes_cobrancas.fco_id)
			WHERE
				tab_faturas_lotes_cobrancas.coa_id != tab_cobrancas.coa_id AND
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1) AND
				(
					SELECT
						COUNT(*)
				 	FROM
				 		tab_cobrancas segundas_vias
				 	WHERE
				 		segundas_vias.coa_id != tab_cobrancas.coa_id AND
				 		segundas_vias.fco_id = tab_cobrancas.fco_id AND
				 		segundas_vias.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)
				) > 0";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_id = {$dados["cobranca_id"]}");
    }
    // PROVA
    // SELECT * FROM `tab_faturas_lotes_cobrancas` WHERE (SELECT COUNT(*) FROM tab_cobrancas WHERE tab_faturas_lotes_cobrancas.fco_id = tab_cobrancas.fco_id AND cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)) > 1
    
	}
	
	function separaCobrancaDuplicadaMesmaVia() {
    $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
		$cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
    $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
    
		$query = "
			SELECT
				coa_id AS id
			FROM
				rel_coa_coe_estacontido
			WHERE
				1
			GROUP BY
				coa_id
			HAVING
				COUNT(coe_id) > 1
			ORDER BY
				coe_id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$retornos = $this->buscaRetornos($dados["id"]);

    	$cobrancas->buscaDadosItem($dados["id"]);
    	
    	print "{$cobrancas->dados_item["codigo"]}\n";
    	
    	if ($cobrancas->dados_item["coa_pai"]) {
    		$cobrancas->buscaDadosItem($cobrancas->dados_item["coa_pai"]);
    	}
    	
    	// Corrige primeira baixa
	    $cobrancas_retornos->darBaixa(array("id" => $dados["id"], "valor_total" => $retornos[0]["valor"], "data_processamento" => $retornos[0]["data_processamento"], "data_pagamento" => $retornos[0]["data_pagamento"]), $retornos[0]["id"]);
	    
	    // Remove comentario antigo
	    $this->bancodedados->query("DELETE FROM tab_cobrancas_comentarios WHERE com_id = {$retornos[0]["comentario_id"]}");
	    
	    for ($i = 1; $i < count($retornos); $i++) {
	      $cobranca_id = $cobrancas_geracao->geraSegundaVia($cobrancas->dados_item, $cobrancas->dados_item["formato_id"], $cobrancas->dados_item["data_vencimento"], $cobrancas->dados_item["valor_original"]);
	      $cobrancas_retornos->darBaixa(array("id" => $cobranca_id, "valor_total" => $retornos[$i]["valor"], "data_processamento" => $retornos[$i]["data_processamento"], "data_pagamento" => $retornos[$i]["data_pagamento"]), $retornos[$i]["id"]);
	      // Atualiza vinculo cobrança correta
	      $this->bancodedados->query("UPDATE rel_coa_coe_estacontido SET coa_id = $cobranca_id WHERE coa_id = {$dados["id"]} AND coe_id = {$retornos[$i]["id"]}");
		    // Remove comentario antigo
		    $this->bancodedados->query("DELETE FROM tab_cobrancas_comentarios WHERE com_id = {$retornos[$i]["comentario_id"]}");
    	}
    	
//    	var_dump($dados["id"]);
//    	var_dump($retornos);
//      die();
    }
	}
	
	function separaCobrancaDuplicadaMesmoDia() {
    $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
		$cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
    $cobrancas_retornos = controle::instanciaObjeto("cobrancas_retornos", $this->bancodedados, null, false);
		
    $query = "
			SELECT
				coa_id AS cobranca_id,
				coe_id AS retorno_id,
				cos_descricao AS descricao
			FROM
				tab_cobrancas_retornos_erros
			WHERE
				coa_id NOT IN (1670198, 1665272) AND
				cos_descricao LIKE 'Baixa Duplicada Mesmo Dia%'
			ORDER BY
				coe_id DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$cobrancas->buscaDadosItem($dados["cobranca_id"]);
    	
    	print "{$dados["retorno_id"]} - {$dados["cobranca_id"]} - {$cobrancas->dados_item["codigo"]}\n";
    	
    	$data_pagamento = $cobrancas->dados_item["coa_data_pagamento"];
    	$data_processamento = $cobrancas->dados_item["coa_data_processamento"];
    	
    	preg_match("/\\(R\\$ ([0-9.]+),([0-9]{2})\\)/", $dados["descricao"], $matches_valor);
    	
    	if ($matches_valor) {
    		$valor = (int) str_replace(".", "", $matches_valor[1]).$matches_valor[2];
    	} else {
    		
    		print "Erro valor descrição\n";
    		var_dump($dados);
    		die();
    		
    	}
    	
    	if ($cobrancas->dados_item["coa_pai"]) {
    		$cobrancas->buscaDadosItem($cobrancas->dados_item["coa_pai"]);
    	}
    	
      $cobranca_id = $cobrancas_geracao->geraSegundaVia($cobrancas->dados_item, $cobrancas->dados_item["formato_id"], $cobrancas->dados_item["data_vencimento"], $cobrancas->dados_item["valor_original"]);
      
	    $cobrancas_retornos->darBaixa(array("id" => $cobranca_id, "valor_total" => $valor, "data_processamento" => $data_processamento, "data_pagamento" => $data_pagamento), $dados["retorno_id"]);
      
      $this->bancodedados->query("INSERT INTO rel_coa_coe_estacontido (coa_id, coe_id) VALUES ($cobranca_id, {$dados["retorno_id"]})");
	    
//	    die();
    }
	}
	
	function buscaRetornos($cobranca_id) {
		$retornos = array();
		$query = "
			SELECT
				coe_id AS id,
				coe_data_publicacao AS data_publicacao
			FROM
				tab_cobrancas_retornos  
			WHERE
				coe_id IN (SELECT coe_id FROM rel_coa_coe_estacontido WHERE coa_id = $cobranca_id)
			ORDER BY
				coe_id";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$retornos[] = array_merge($dados, $this->buscaComentarioRetorno($cobranca_id, $dados["data_publicacao"]));
    }
    return $retornos;
	}
	
	function buscaComentarioRetorno($cobranca_id, $data_publicacao) {
		$query = "
			SELECT
				com_id AS comentario_id,
				com_conteudo AS comentario,
				DATE_FORMAT(com_datahora, '%Y-%m-%d') AS data_processamento
			FROM
				tab_cobrancas_comentarios
			WHERE
				coa_id = $cobranca_id AND
				com_datahora LIKE '$data_publicacao%' AND
				com_conteudo LIKE '<b>Ação:</b> Baixa Automática%'";
    $query = $this->bancodedados->query($query);
    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	preg_match("/(Pagamento|Processamento):<\\/b> ([0-9]{2})\\/([0-9]{2})\\/([0-9]{4})/", $dados["comentario"], $matches_data);
    	preg_match("/(Valor Total|Valor):<\\/b> R\\$ ([0-9.]+),([0-9]{2})/", $dados["comentario"], $matches_valor);
    	if ($matches_data && $matches_valor) {
    		return array(
    			"comentario_id" => $dados["comentario_id"],
    			"data_processamento" => $dados["data_processamento"],
    			"data_pagamento" => "$matches_data[4]-$matches_data[3]-$matches_data[2]",
    			"valor" => (int) str_replace(".", "", $matches_valor[2]).$matches_valor[3]
    		);
    	}
    }
    
    		print "Erro comentario";
    		var_dump($cobranca_id);
    		var_dump($data_publicacao);
    		var_dump($dados);
    		die();
	}
	
	
	function corrigePublicacaoErrada() {
		$query = "
			SELECT
				coa_id AS cobranca_id,
				coe_id AS retorno_id	
			FROM
				rel_coa_coe_estacontido
			WHERE
				(
					SELECT
						(SELECT cof_cedente FROM tab_cobrancas_formatos WHERE tab_cobrancas_formatos.cof_id = tab_cobrancas.cof_id)
					FROM
						tab_cobrancas
					WHERE
						rel_coa_coe_estacontido.coa_id = tab_cobrancas.coa_id
				) != (
					SELECT
						(SELECT cof_cedente FROM tab_cobrancas_formatos WHERE tab_cobrancas_formatos.cof_id = tab_cobrancas_retornos.cof_id)
					FROM
						tab_cobrancas_retornos
					WHERE
						rel_coa_coe_estacontido.coe_id = tab_cobrancas_retornos.coe_id
				)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	print "{$dados["cobranca_id"]}\t{$dados["retorno_id"]}\n";
    }
	}
	
	function reassociaFaturaLoteCobranca() {
		$query = "
			SELECT
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_pai AS cobranca_pai_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_faturas_lotes_cobrancas.fco_id AS cobranca_fatura_lote_id,
				tab_faturas_lotes_cobrancas.fat_id AS fatura_id
			FROM
				tab_cobrancas
				INNER JOIN tab_faturas_lotes_cobrancas ON (tab_cobrancas.coa_id = tab_faturas_lotes_cobrancas.coa_id)
			WHERE
				tab_cobrancas.cot_id IN (11) AND
				tab_faturas_lotes_cobrancas.fat_id IS NOT NULL";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$segunda_via = $this->buscaSegundaViaBaixada($dados["cobranca_id"], $dados["cobranca_pai_id"]);
    	if ($segunda_via) {
    		print "OK {$dados["codigo"]} => {$segunda_via["codigo"]}\n";
    		$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET coa_id = {$segunda_via["cobranca_id"]} WHERE fco_id = {$dados["cobranca_fatura_lote_id"]}");
    		$this->bancodedados->query("UPDATE tab_faturas SET coa_id = {$segunda_via["cobranca_id"]} WHERE fat_id = {$dados["fatura_id"]}");
    	} else {
    		print "ERRO: SEGUNDA VIA NÃO ENCONTRADA\n";
    		var_dump($dados);
    		die();
    	}
    }
	}
	
	function buscaSegundaViaBaixada($cobranca_id, $cobranca_pai_id) {
		$query = "
			SELECT
				coa_id AS cobranca_id,
				coa_codigo AS codigo
			FROM
				tab_cobrancas
			WHERE
				coa_id != $cobranca_id AND
				cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1) AND".($cobranca_pai_id ? "
				(coa_pai = $cobranca_pai_id OR coa_id = $cobranca_pai_id)" : "
				(coa_pai = $cobranca_id)");
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
	}
	
	function recalculaFaturaLoteCobranca() {
		$query = "
			SELECT
				fco_id AS fatura_lote_cobranca_id,
				coa_id AS cobranca_id,
				fco_valor_scm AS valor_scm,
				fco_valor_sva AS valor_sva,
				fco_valor_juro AS valor_juro,
				fco_valor_instalacao AS valor_instalacao,
				fco_valor_equipamentos AS valor_equipamentos,
				fco_valor_servicos AS valor_servicos,
				fco_valor_garantia AS valor_garantia
			FROM
				tab_faturas_lotes_cobrancas
			WHERE
				-- fal_id = 2208 AND
				1
			-- LIMIT 1000";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$dados_cobranca = $this->buscaDadosCobranca($dados["cobranca_id"]);
    	$fatura_itens = $this->faturas_lotes->buscaFaturaItens($dados_cobranca, null, true);
    	
    	if ($dados["valor_scm"] != $fatura_itens["valor_scm"] ||
    			 $dados["valor_sva"] != $fatura_itens["valor_sva"] ||
    			 $dados["valor_juro"] != $fatura_itens["valor_juro"] ||
    			 $dados["valor_instalacao"] != $fatura_itens["valor_instalacao"] ||
    			 $dados["valor_equipamentos"] != $fatura_itens["valor_equipamentos"] ||
    			 $dados["valor_servicos"] != $fatura_itens["valor_servicos"] ||
    			 $dados["valor_garantia"] != $fatura_itens["valor_garantia"]) {
    			 	
//    		print "-- {$dados_cobranca["codigo"]}\nUPDATE tab_faturas_lotes_cobrancas SET fco_valor_scm = {$fatura_itens["valor_scm"]}, fco_valor_sva = {$fatura_itens["valor_sva"]}, fco_valor_juro = {$fatura_itens["valor_juro"]}, fco_valor_instalacao = {$fatura_itens["valor_instalacao"]}, fco_valor_servicos = {$fatura_itens["valor_servicos"]}, fco_valor_equipamentos = {$fatura_itens["valor_equipamentos"]}, fco_valor_garantia = {$fatura_itens["valor_garantia"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]};\n";
				$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET fco_valor_scm = {$fatura_itens["valor_scm"]}, fco_valor_sva = {$fatura_itens["valor_sva"]}, fco_valor_juro = {$fatura_itens["valor_juro"]}, fco_valor_instalacao = {$fatura_itens["valor_instalacao"]}, fco_valor_servicos = {$fatura_itens["valor_servicos"]}, fco_valor_equipamentos = {$fatura_itens["valor_equipamentos"]}, fco_valor_garantia = {$fatura_itens["valor_garantia"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
				flush();
    	}
    	
    }
	}
	
	function buscaDadosCobranca($cobranca_id) {
		$query = "
      SELECT
        tab_cobrancas.coa_id AS id,
        tab_cobrancas.cli_id AS cliente_id,
        tab_cobrancas.coa_codigo AS codigo,
        tab_cobrancas.coa_valor_total AS valor_total,
        tab_cobrancas.coa_valor_original AS valor_original,
        tab_cobrancas.coa_valor_desconto AS valor_desconto,
        tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo,
        tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo,
        tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
        tab_cobrancas.coa_valor_juro AS valor_juro,
        tab_cobrancas.coa_data_geracao AS data_geracao,
        tab_cobrancas.coa_data_processamento AS data_processamento,
        tab_cobrancas.coa_data_vencimento AS data_vencimento,
        tab_cobrancas.cot_id AS status_id,
        tab_cobrancas.coa_pai AS coa_pai,
        tab_cobrancas.cop_id AS tipo_id,
        tab_cobrancas.cof_id AS formato_id
      FROM
				tab_cobrancas
			WHERE
				tab_cobrancas.coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
	}
	
	function corrigirDiferenca() {
		$query = "
			SELECT
				fco_id AS fatura_lote_cobranca_id,
				coa_id AS cobranca_id,
				fco_valor_scm AS valor_scm,
				fco_valor_sva AS valor_sva,
				fco_valor_juro AS valor_juro,
				fco_valor_instalacao AS valor_instalacao,
				fco_valor_equipamentos AS valor_equipamentos,
				fco_valor_servicos AS valor_servicos,
				fco_valor_garantia AS valor_garantia,
				SUM(fai_valor_total) AS valor_fatura,
				fco_valor_scm - SUM(IF(fai_tipo = 'SCM', fai_valor_total, 0)) AS valor_diferenca_scm,
				fco_valor_sva - SUM(IF(fai_tipo = 'SVA', fai_valor_total, 0)) AS valor_diferenca_sva
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_faturas_itens ON (tab_faturas_lotes_cobrancas.fat_id = tab_faturas_itens.fat_id)
			WHERE
				1
			GROUP BY
				tab_faturas_lotes_cobrancas.fco_id
			HAVING
				fco_valor_scm + fco_valor_sva > SUM(fai_valor_total) AND
				fco_valor_scm + fco_valor_sva - SUM(fai_valor_total) <= fco_valor_juro";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$juros = $this->buscaJuros($dados["cobranca_id"]);
    	if ($dados["valor_diferenca"] <= $juros) {
    		$dados["valor_scm"] -= $dados["valor_diferenca_scm"];
    		$dados["valor_sva"] -= $dados["valor_diferenca_sva"];
    		$dados["valor_juro"] += $dados["valor_diferenca_scm"] + $dados["valor_diferenca_sva"];
    		print "UPDATE tab_faturas_lotes_cobrancas SET fco_valor_scm = {$dados["valor_scm"]}, fco_valor_sva = {$dados["valor_sva"]}, fco_valor_juro = {$dados["valor_juro"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]};\n";    		
    	}
    }
	}
		
	function corrigirDistribuicaoArredondamento() {
		$query = "
			SELECT
				fco_id AS fatura_lote_cobranca_id,
				coa_id AS cobranca_id,
				SUM(fai_valor_total) AS valor_fatura,
				fco_valor_scm AS valor_scm,
				fco_valor_sva AS valor_sva,
				SUM(IF(fai_tipo = 'SCM', fai_valor_total, 0)) AS fatura_scm,
				SUM(IF(fai_tipo = 'SVA', fai_valor_total, 0)) AS fatura_sva,
				fco_valor_scm - SUM(IF(fai_tipo = 'SCM', fai_valor_total, 0)) AS diferenca_scm
			FROM
				tab_faturas_lotes_cobrancas
				INNER JOIN tab_faturas_itens ON (tab_faturas_lotes_cobrancas.fat_id = tab_faturas_itens.fat_id)
			WHERE
				1
			GROUP BY
				tab_faturas_lotes_cobrancas.fco_id
			HAVING
				fco_valor_scm + fco_valor_sva = SUM(fai_valor_total) AND
				fco_valor_scm != SUM(IF(fai_tipo = 'SCM', fai_valor_total, 0))";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	$diferenca_scm = $dados["valor_scm"] - $dados["fatura_scm"];
    	$diferenca_sva = $dados["valor_sva"] - $dados["fatura_sva"];
    	
    	$dados["valor_scm"] = $dados["fatura_scm"];
    	$dados["valor_sva"] = $dados["fatura_sva"];
    	$this->bancodedados->query("UPDATE tab_faturas_lotes_cobrancas SET fco_valor_scm = {$dados["valor_scm"]}, fco_valor_sva = {$dados["valor_sva"]} WHERE fco_id = {$dados["fatura_lote_cobranca_id"]}");
    }
	}
	
	function buscaJuros($cobranca_id) {
		$query = "
      SELECT
        coa_valor_juro AS juro_cobranca,
        (SELECT SUM(cod_valor_final) FROM tab_cobrancas_detalhes WHERE tab_cobrancas_detalhes.coa_id = tab_cobrancas.coa_id AND tab_cobrancas_detalhes.cod_descricao = 'Juro') AS juro_detalhes
      FROM
				tab_cobrancas
			WHERE
				coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
    	return $dados["juro_cobranca"] + $dados["juro_detalhes"];
    }
    return 0;
	}
	
}


/*

UPDATE
	tab_faturas_lotes_cobrancas
SET
	tab_faturas_lotes_cobrancas.fat_id = (SELECT tab_faturas.fat_id FROM tab_faturas WHERE tab_faturas_lotes_cobrancas.coa_id = tab_faturas.coa_id)
WHERE
	1


UPDATE
	tab_cobrancas
SET
	coa_fatura_gerada = 1
WHERE
	 coa_fatura_gerada = 0 AND
	 (SELECT COUNT(*) FROM `tab_faturas_lotes_cobrancas` WHERE tab_cobrancas.coa_id = tab_faturas_lotes_cobrancas.coa_id) > 0

SELECT
	*
FROM
	tab_cobrancas
WHERE
	coa_fatura_gerada = 1 AND
	(SELECT COUNT(*) FROM tab_faturas_lotes_cobrancas WHERE tab_faturas_lotes_cobrancas.coa_id = tab_cobrancas.coa_id) = 0 AND
	(SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas.coa_id = tab_cobrancas.coa_id) = 0



SELECT
	*
FROM
	`tab_faturas_lotes_cobrancas`
WHERE
	`fco_valor_instalacao` = 0 AND
	`fco_valor_equipamentos` = 0 AND
	`fco_valor_servicos` = 0 AND
	(SELECT COUNT(*) FROM tab_faturas WHERE tab_faturas_lotes_cobrancas.coa_id = tab_faturas.coa_id) = 0


UPDATE
	`tab_faturas_lotes_cobrancas`
SET
	fco_valor_scm = (SELECT SUM(fai_valor_total) FROM tab_faturas INNER JOIN tab_faturas_itens ON (tab_faturas.fat_id = tab_faturas_itens.fat_id) WHERE tab_faturas_lotes_cobrancas.coa_id = tab_faturas.coa_id)
WHERE
  1


SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Débito não efetuado%'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Sem contrato de débito automático'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Cancelamento - Cancelado conforme solicitação'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Cancelamento - Não encontrado'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Conta corrente não cadastrada'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Conta inválida para débito'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Insuficiência de Fundos'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Insuficiência de saldo em conta corrente'

SELECT CONCAT("DELETE FROM rel_coa_coe_estacontido WHERE coa_id = ", coa_id, " AND coe_id = ", coe_id, ";") AS query  
FROM `tab_cobrancas_retornos_erros`
WHERE `cos_descricao` LIKE 'Erro desconhecido'


 
 */

?>
