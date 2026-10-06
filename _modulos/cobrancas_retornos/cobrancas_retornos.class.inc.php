<?php

require_once("_global/modulo.class.inc.php");

class cobrancas_retornos extends modulo {
	
	private $chamados;
	
	function __construct($bancodedados, $permissoes, $executar = true) {
		
		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;
		
		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao(empty($_GET["a"]) ? "listar" : $_GET["a"]);
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
		
	}
	
	function buscaDadosLote($lote_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				col_id AS id,
				col_nome AS nome
			FROM
				tab_cobrancas_lotes
			WHERE
				col_id = $lote_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaDadosItem($cobrnaca_retorno_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_retornos.coe_id AS id,
				DATE_FORMAT(tab_cobrancas_retornos.coe_data_geracao,'%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(tab_cobrancas_retornos.coe_data_publicacao,'%d/%m/%Y') AS data_publicacao,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cof_nome AS formato_nome,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'REGISTRO') AS registros,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'BAIXA') AS baixas,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'REJEICAO') AS rejeicoes,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'LIQUIDACAO') AS liquidacoes
			FROM
				tab_cobrancas_retornos
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_retornos.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				tab_cobrancas_retornos.coe_id = $cobrnaca_retorno_id");
		
		$this->dados_item = $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaDadosFormato($formato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cof_id AS id,
				cof_nome AS nome,
				cof_parametros AS parametros
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id = $formato_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
		
	}
	
	function contabilizaRegistros($id, $dada_geracao) {
		
		$registros = array();
		
		$query = $this->bancodedados->query("
			SELECT
				IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas_formatos.cof_nome, IF(tab_cobrancas.coa_pai IS NOT NULL, 'Segundas Vias', 'Cobranças Avulsas')) AS formato,
				DATE_FORMAT(IF(cobranca_pai.coa_id IS NULL, tab_cobrancas.coa_data_vencimento, cobranca_pai.coa_data_vencimento), '%m/%Y') AS vencimento_original,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				COUNT(DISTINCT tab_cobrancas.coa_id) AS registros,
				SUM(tab_cobrancas.coa_valor_total - tab_cobrancas.coa_valor_iof) AS total,
				tab_cobrancas_formatos.cof_id AS formato_id,
				IF(tab_cobrancas.col_id IS NOT NULL, 1, IF(tab_cobrancas.coa_pai IS NOT NULL, 2, 3)) AS tipo
			FROM
				tab_cobrancas_retornos_registros
				INNER JOIN tab_cobrancas ON (tab_cobrancas_retornos_registros.coa_id = tab_cobrancas.coa_id)
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
				LEFT OUTER JOIN tab_cobrancas cobranca_pai ON (cobranca_pai.coa_id = tab_cobrancas.coa_pai)
				LEFT OUTER JOIN tab_cobrancas_lotes ON (tab_cobrancas_lotes.col_id = IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.col_id, cobranca_pai.col_id))
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_cobrancas_retornos_registros.coe_id = $id AND
				tab_cobrancas_retornos_registros.cog_tipo = 'LIQUIDACAO' AND
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1 AND cot_manual = 0)
			GROUP BY
				formato,
				tab_cobrancas.coa_data_vencimento
			ORDER BY
				tab_cobrancas_formatos.cof_nome,
				IF(tab_cobrancas.col_id IS NOT NULL, tab_cobrancas.coa_data_vencimento, cobranca_pai.coa_data_vencimento),
				tab_cobrancas.coa_data_vencimento");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes["cobrancas"]["buscar"]) {
				$dados["link"] = "?m=cobrancas&a=buscar&f[formato]={$dados["formato_id"]}&f[retorno_data_geracao]=$dada_geracao&f[competencia]={$dados["competencia"]}&f[data_vencimento]={$dados["data_vencimento"]}&f[tipo]={$dados["tipo"]}";
			}
			$registros_total += $dados["registros"];
			$valor_total += $dados["total"];
			$dados["vencimento_original"] = "<center>".$this->formataData($dados["vencimento_original"])."</center>";
			$dados["data_vencimento"] = "<center>{$dados["data_vencimento"]}</center>";
			$dados["total"] = "<div align=\"right\">".$this->formataDinheiro($dados["total"])."</div>";
			
			$registros[] = $dados;
			
		}
		
		if ($registros_total > 1) {
			$registros[] = array("formato" => "<b>Total</b>", "registros" => "<b>$registros_total</b>", "total" => "<div style=\"text-align:right\"><b>".$this->formataDinheiro($valor_total)."</b></div>");
		}
		
		return $registros;
		
	}
	
	function buscaFormatos() {
		
		$formatos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cof_id AS id,
				cof_nome AS nome,
				cof_parametros AS parametros
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_publicavel = 1
			ORDER BY
				cof_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($dados["parametros"] && array_key_exists("publicacao", $dados["parametros"]) && $dados["parametros"]["publicacao"]) {
				$formatos[$dados["id"]] = $dados["nome"];
			}
		}
		
		return $formatos;
		
	}
	
	function buscaErros($retorno_id) {
		
		$cobrancas = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_valor_original AS valor_original,
				(tab_cobrancas.coa_valor_original + tab_cobrancas.coa_valor_juro) - tab_cobrancas.coa_valor_desconto AS valor_devido,
				tab_cobrancas.coa_valor_total AS valor_pago,
				tab_cobrancas.coa_valor_decrescimo AS valor_diferenca,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas.cli_id AS cliente_id,
				tab_cobrancas_retornos_erros.cos_descricao AS descricao,
				tab_cobrancas_status.cot_nome AS status
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_retornos_erros ON (tab_cobrancas.coa_id = tab_cobrancas_retornos_erros.coa_id)
				INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
			WHERE
				tab_cobrancas_retornos_erros.coe_id = $retorno_id
			ORDER BY
				descricao,
				valor_diferenca DESC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["valor_devido"] = $this->formataDinheiro($dados["valor_devido"]);
			$dados["valor_original"] = $this->formataDinheiro($dados["valor_original"]);
			$dados["valor_pago"] = $this->formataDinheiro($dados["valor_pago"]);
			$dados["valor_diferenca"] = $dados["descricao"] == "Pagamento Parcial" && $dados["valor_diferenca"] ? $this->formataDinheiro($dados["valor_diferenca"]) : "-";
			if ($this->permissoes["cobrancas"]["ver_dg"]) {
				$dados["link"] = "javascript:verCobranca({$dados["id"]})";
			}
			if ($this->permissoes["clientes"]["ver_df"]) {
				$dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			
			$cobrancas[] = $dados;
			
		}
		
		return $cobrancas;
		
	}
	
	function buscaNaoEncontradas($retorno_id) {
		
		$cobrancas = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_retornos_nao_encontradas.crn_id AS id,
				tab_cobrancas_retornos_nao_encontradas.crn_codigo AS codigo,
				DATE_FORMAT(tab_cobrancas_retornos_nao_encontradas.crn_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas_retornos_nao_encontradas.crn_valor_original AS valor_original,
				tab_cobrancas_retornos_nao_encontradas.crn_valor_pago AS valor_pago,
				tab_cobrancas.coa_id AS cobranca_id,
				tab_cobrancas.coa_codigo AS cobranca_codigo
			FROM
				tab_cobrancas_retornos_nao_encontradas
				LEFT OUTER JOIN tab_cobrancas ON (tab_cobrancas_retornos_nao_encontradas.coa_id = tab_cobrancas.coa_id)
			WHERE
				tab_cobrancas_retornos_nao_encontradas.coe_id = $retorno_id
			ORDER BY
				tab_cobrancas_retornos_nao_encontradas.crn_codigo");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["valor_original"] = $this->formataDinheiro($dados["valor_original"]);
			$dados["valor_pago"] = $this->formataDinheiro($dados["valor_pago"]);
			if ($dados["cobranca_id"] && $this->permissoes["cobrancas"]["ver_dg"]) {
				$dados["link"] = "javascript:verCobranca({$dados["cobranca_id"]})";
			}
			
			$cobrancas[] = $dados;
			
		}
		
		return $cobrancas;
		
	}
	
	function buscaRegistrosAgrupados($retorno_id = null, $query = null) {
		
		$itens = array();
		
		$query = $this->bancodedados->query($query ? $query : "
			SELECT DISTINCT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_juro AS valor_juro,
				tab_cobrancas.coa_valor_desconto AS valor_desconto,
				tab_cobrancas.coa_valor_acrescimo AS valor_acrescimo,
				tab_cobrancas.coa_valor_decrescimo AS valor_decrescimo,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_total AS valor_total,
				tab_cobrancas.cot_id AS status_id,
				tab_cobrancas.cli_id AS cliente_id,
				IF(tab_cobrancas.coa_data_vencimento > tab_cobrancas.coa_data_pagamento, 1, 0) AS atrasada
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_retornos_registros ON (tab_cobrancas.coa_id = tab_cobrancas_retornos_registros.coa_id)
			WHERE
				tab_cobrancas_retornos_registros.coe_id = $retorno_id AND
				tab_cobrancas_retornos_registros.cog_tipo = 'LIQUIDACAO' AND
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1 AND cot_manual = 0)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($dados["valor_total"] > $dados["valor_original"] + $dados["valor_juro"] - $dados["valor_desconto"] + $dados["valor_acrescimo"] - $dados["valor_decrescimo"]) {
				if ($dados["atrasada"]) {
					$dados["valor_juro"] += $dados["valor_total"] - ($dados["valor_original"] + $dados["valor_juro"] - $dados["valor_desconto"] + $dados["valor_acrescimo"] - $dados["valor_decrescimo"]);
				} else {
					$dados["valor_acrescimo"] += $dados["valor_total"] - ($dados["valor_original"] + $dados["valor_juro"] - $dados["valor_desconto"] + $dados["valor_acrescimo"] - $dados["valor_decrescimo"]);
				}
			} elseif ($dados["valor_total"] < $dados["valor_original"] + $dados["valor_juro"] - $dados["valor_desconto"] + $dados["valor_acrescimo"] - $dados["valor_decrescimo"]) {
				$dados["valor_decrescimo"] -= $dados["valor_total"] - ($dados["valor_original"] + $dados["valor_juro"] - $dados["valor_desconto"] + $dados["valor_acrescimo"] - $dados["valor_decrescimo"]);
			}
			
			$detalhes = $this->buscaDetalhes($dados["id"]);
			
			if ($detalhes) {
				
				if ($dados["valor_acrescimo"] - $dados["valor_desconto"] - $dados["valor_decrescimo"] != 0) {
					
					$outros = $dados["valor_acrescimo"] - $dados["valor_desconto"] - $dados["valor_decrescimo"];
					
					$dados["valor_acrescimo"] = 0;
					$dados["valor_decrescimo"] = 0;
					$dados["valor_desconto"] = 0;
					
					foreach ($detalhes as $descricao => $valores) {
						$detalhes[$descricao]["valor"] += round($outros / $dados["valor_original"] * $valores["valor"]);
					}
					
				}
				
				$valor_total = 0;
				
				foreach ($detalhes as $detalhe) {
					// Recebido
					if ($dados["status_id"] != 1) {
						$itens[$detalhe["descricao"]]["quantidade"] += 1;
						$itens[$detalhe["descricao"]]["valor"] += $detalhe["valor"];
					}
					$valor_total += $detalhe["valor"];
				}
				
			}
			
			if ($valor_total + $dados["valor_juro"] != $dados["valor_total"]) {
				$itens[$detalhes[0]["descricao"]]["valor"] += $dados["valor_total"] - ($valor_total + $dados["valor_juro"]);
			}
			
			if ($dados["valor_juro"]) {
				$itens["Juros / Multa"]["valor"] += $dados["valor_juro"];
				$dados["valor_juro"] = 0;
			}
			
		}
		
		if ($itens) {
			
			$lista = array();
			
			foreach ($itens as $descricao => $detalhes) {
				
				$novo = array();
				
				$novo["descricao"] = $descricao;
				$novo["recebido_quantidade"] = $detalhes["quantidade"] ? number_format($detalhes["quantidade"], 0, ",", ".") : "";
				$novo["recebido_valor"] = $this->formataDinheiro($detalhes["valor"]);
				
				$lista[] = $novo;
				
			}
			
			$lista = $this->ordena_multiarray($lista, array("descricao"));
			
			return $lista;
			
		}
		
	}
	
	function buscaDetalhes($cobranca_id) {
		
		$servicos = array();
		$detalhes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas_detalhes.ass_id AS assinatura_id,
				tab_cobrancas_detalhes.ajf_id AS ajuste_id,
				tab_ajustes_financeiros.afc_id AS ajuste_cateogria_id,
				tab_cobrancas_detalhes.cod_descricao AS descricao,
				tab_cobrancas_detalhes.cod_valor_final + IF(COUNT(detalhes_filhos.cod_id) > 0, SUM(detalhes_filhos.cod_valor_final), 0) AS valor
			FROM
				tab_cobrancas_detalhes
				LEFT OUTER JOIN tab_cobrancas_detalhes AS detalhes_filhos ON (tab_cobrancas_detalhes.cod_id = detalhes_filhos.cod_pai)
				LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_cobrancas_detalhes.cod_pai IS NULL
			GROUP BY
				tab_cobrancas_detalhes.cod_id
			ORDER BY
				assinatura_id DESC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if (!$dados["valor"]) {
				continue;
			}
			
			if ($dados["assinatura_id"]) {
				
				$servico = $this->buscaServicoAssinatura($dados["assinatura_id"]);
				if ($servico == "Serviços Técnicos (Serviços)") {
					$dados["descricao"] = "Serviços Técnicos";
				} else {
					$dados["descricao"] = "$servico (Mensalidade)";
				}
				// Conta Serviços
				$servicos[$servico]++;
				
			} elseif ($dados["ajuste_id"]) {
				
				if ($dados["ajuste_cateogria_id"] == 19) {
					$descricao = $this->buscaServicoAjusteAssinatura($dados["ajuste_id"]);
					if ($descricao) {
						$dados["descricao"] = "$descricao (Instalação)";
					} elseif (($descricao = $this->buscaServicoAjusteContrato($dados["ajuste_id"])) != false) {
						$dados["descricao"] = "$descricao (Instalação)";
					} else {
						if ($servicos) {
							$dados["descricao"] = key($servicos)." (Instalação)";
						} else {
							$dados["descricao"] = "Internet Condominial (Instalação)";
						}
					}
				} elseif ($dados["ajuste_cateogria_id"] == 20) {
					$dados["descricao"] = "Serviços Técnicos";
				} elseif ($dados["ajuste_cateogria_id"] == 23) {
					$dados["descricao"] = "Equipamentos";
				} elseif (substr($dados["descricao"], -18) == "(Estorno Migração)") {
					$servico = $this->buscaServicoAjusteAssinatura($dados["ajuste_id"]);
					if (!$servico) var_dump($dados["ajuste_id"]);
					if ($servico == "Serviços Técnicos (Serviços)") {
						$dados["descricao"] = "Serviços Técnicos";
					} else {
						$dados["descricao"] = "$servico (Mensalidade)";
					}
				} elseif (($descricao = $this->buscaDescricaoAjuste($dados["ajuste_id"])) != false) {
					$dados["descricao"] = $descricao;
				} elseif (($descricao = $this->buscaServicoAjusteContrato($dados["ajuste_id"])) != false) {
					if ($descricao == "Serviços Técnicos (Serviços)") {
						$dados["descricao"] = "Serviços Técnicos";
					} else {
						$dados["descricao"] = "$descricao (Mensalidade)";
					}
				} else {
					if ($servicos) {
						if (key($servicos) == "Serviços Técnicos (Serviços)") {
							$dados["descricao"] = "Serviços Técnicos";
						} else {
							$dados["descricao"] = key($servicos)." (Mensalidade)";
						}
					} else {
						$dados["descricao"] = "Internet Condominial (Mensalidade)";
					}
				}
				
			} elseif (in_array($dados["descricao"], array("Juro", "Juros"))) {
				
				$dados["descricao"] = "Juros / Multa";
				
			} else {
				
				if ($servicos) {
					if (key($servicos) == "Serviços Técnicos (Serviços)") {
						$dados["descricao"] = "Serviços Técnicos";
					} else {
						$dados["descricao"] = key($servicos)." (Mensalidade)";
					}
				} else {
					$dados["descricao"] = "Internet Condominial (Mensalidade)";
				}
				
			}
			
			$detalhes[] = $dados;
			
		}
		
		return $detalhes;
		
	}
	
	function buscaDescricaoAjuste($ajuste_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				afc_id AS categoria_id
			FROM
				tab_ajustes_financeiros
			WHERE
				ajf_id = $ajuste_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if (in_array($dados["categoria_id"], array(1, 2, 7))) {
				return "Internet Condominial (Mensalidade)";
			} elseif (in_array($dados["categoria_id"], array(24))) {
				return "Internet Empresarial (Mensalidade)";
			} elseif (in_array($dados["categoria_id"], array(20))) {
				return "Serviços Técnicos (Serviços)";
			} elseif (in_array($dados["categoria_id"], array(23))) {
				return "Serviços Técnicos (Equipamentos)";
			}
		}
		
	}
	
	function buscaAgrupadorServico($servico_id) {
		
		//TODO Serviço deveria ter uma categoria para agrupamento!
		
		$agrupadores = array();
		
		$agrupadores[1]  = "Internet Condominial";
		$agrupadores[2]  = "Internet Condominial";
		$agrupadores[5]  = "Internet Condominial";
		$agrupadores[23] = "Internet Condominial";
		$agrupadores[32] = "Internet Condominial";
		
		$agrupadores[28] = "Internet Residencial";
		$agrupadores[33] = "Internet Residencial";
		$agrupadores[38] = "Internet Residencial";
		
		$agrupadores[3]  = "Internet Empresarial";
		$agrupadores[4]  = "Internet Empresarial";
		$agrupadores[6]  = "Internet Empresarial";
		$agrupadores[7]  = "Internet Empresarial";
		$agrupadores[8]  = "Internet Empresarial";
		$agrupadores[12] = "Internet Empresarial";
		$agrupadores[13] = "Internet Empresarial";
		$agrupadores[31] = "Internet Empresarial";
		$agrupadores[34] = "Internet Empresarial";
		$agrupadores[35] = "Internet Empresarial";
		$agrupadores[36] = "Internet Empresarial";
		
		$agrupadores[39] = "Infraestrutura";
		
		$agrupadores[14] = "Telefonia IP";
		
		$agrupadores[9]  = "Serviços Data Center";
		$agrupadores[10] = "Serviços Data Center";
		$agrupadores[11] = "Serviços Data Center";
		$agrupadores[20] = "Serviços Data Center";
		$agrupadores[25] = "Serviços Data Center";
		$agrupadores[30] = "Serviços Data Center";
		
		$agrupadores[26] = "Serviços Personalizados";
		$agrupadores[27] = "Serviços Personalizados";
		$agrupadores[29] = "Serviços Personalizados";
		
		$agrupadores[21] = "Serviços Técnicos (Serviços)";
		
		return $agrupadores[$servico_id];
		
	}
	
	function buscaServicoAssinatura($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pls_id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id");
		
		if (($servico_id = $this->bancodedados->resultado($query)) != false) {
			return $this->buscaAgrupadorServico($servico_id);
		}
		
	}
	
	function buscaServicoAjusteAssinatura($ajuste_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pls_id
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_assinaturas ON (tab_ajustes_financeiros.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_ajustes_financeiros.ajf_id = $ajuste_id");
		
		if (($servico_id = $this->bancodedados->resultado($query)) != false) {
			return $this->buscaAgrupadorServico($servico_id);
		}
		
	}
	
	function buscaServicoAjusteContrato($ajuste_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.pls_id AS servico_id
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_contratos ON (tab_ajustes_financeiros.cor_id = tab_contratos.cor_id)
			WHERE
				tab_ajustes_financeiros.ajf_id = $ajuste_id");
		
		if (($servico_id = $this->bancodedados->resultado($query)) != false) {
			return $this->buscaAgrupadorServico($servico_id);
		}
		
	}
	
	#############
	### Geral ###
	#############
	
	function formataDataCompleta($data) {
		
		return substr($data, 8, 2)."/".substr($data, 5, 2)."/".substr($data, 0, 4) ;
		
	}
	
	function formataData($vencimento) {
		
		$meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
		$vencimento = explode("/", $vencimento);
		
		return $meses[intval($vencimento[0], 10)]." / ".$vencimento[1];
		
	}
	
	function ordena_multiarray($array, $campos) {
		
		if (is_array($array) && sizeof($array)) {
			
			$tamanho_chave = 0;
			reset($array);
			
			while (($array_ordenacao_atual = current($array)) != false) {
				$chave = array();
				for ($i = 0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				$chave = implode(",", $chave);
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
			
			$array_final = array();
			
			while (is_int($array_id = current($array_ordenacao))) {
				$array_final[] = $array[$array_id];
				next($array_ordenacao);
			}
			
			return $array_final;
			
		}
		
	}
	
	###########
	### Ver ###
	###########
	
	function ver() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.ver.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[quantidade_registros]', $this->dados_item["registros"], $this->conteudo);
			$this->conteudo = str_replace('[quantidade_baixas]', $this->dados_item["baixas"], $this->conteudo);
			$this->conteudo = str_replace('[quantidade_rejeicoes]', $this->dados_item["rejeicoes"], $this->conteudo);
			$this->conteudo = str_replace('[quantidade_liquidacoes]', $this->dados_item["liquidacoes"], $this->conteudo);
			$this->conteudo = str_replace('[registros]', $this->geraRegistrosListagem($this->contabilizaRegistros($this->dados_item["id"], $this->dados_item["data_geracao"], 1)), $this->conteudo);
			$this->conteudo = str_replace('[erros]', $this->geraErrosListagem($this->buscaErros($this->dados_item["id"])), $this->conteudo);
			$this->conteudo = str_replace('[nao_encontradas]', $this->geraNaoEncontradasListagem($this->buscaNaoEncontradas($this->dados_item["id"])), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $this->dados_item["id"]);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function geraRegistrosListagem($itens) {
		
		$cabecalho = array ("formato" => "Formato", "vencimento_original" => "Vencimento Original", "data_vencimento" => "Vencimento", "registros" => "Registro(s)", "total" => "Total Pago");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 2, false, array(0, 110, 110, 110, 110));
		
		return $listagem->conteudo;
		
	}
	
	function geraRegistrosAgrupadosListagem($itens) {
		
		$campos = array();
		$campos["descricao"] = "Descrição";
		$campos["recebido_valor"] = "Valor";
		
		$listagem = controle::instanciaObjeto('listagem', $campos, $itens, 0, array("faturado_valor" => "formataDinheiro", "recebido_valor" => "formataDinheiro"), array(0, 150), null, array("left", "right"));
		
		return $listagem->conteudo;
		
	}
	
	function geraErrosListagem($itens) {
		
		$cabecalho = array ("descricao" => "Descrição", "cliente_id" => "ID", "codigo" => "Código", "data_vencimento" => "Vencimento", "valor_devido" => "Valor Devido", "valor_pago" => "Valor Pago", "valor_diferenca" => "Valor Diferença", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, null, null, null, array("left", "center", "center", "center", "right", "right", "right", "center"));
		
		return $listagem->conteudo;
		
	}
	
	function geraNaoEncontradasListagem($itens) {
		
		$cabecalho = array ("codigo" => "Código", "data_vencimento" => "Vencimento", "valor_original" => "Valor Original", "valor_pago" => "Valor Pago", "cobranca_codigo" => "Cobrança Associada");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, null, null, null, array("center", "center", "right", "right"));
		
		return $listagem->conteudo;
		
	}
	
	#################
	### Registros ###
	#################
	
	function registros() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->temPermissao("exportar_detalhado")) $botoes[] = '<input type="button" name="exportar_detalhado" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar_detalhado&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.registros.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[registros]', $this->buscaRegistros($this->dados_item["id"], 'REGISTRO'), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function buscaRegistros($retorno_id, $tipo) {
		
		$itens = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.cli_id AS cliente_id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_parametros AS parametros,
				DATE_FORMAT(tab_cobrancas.coa_data_geracao, '%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				IF(tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1 AND cot_manual = 0), tab_cobrancas.coa_valor_total, tab_cobrancas.coa_valor_original) AS valor,
				tab_cobrancas_status.cot_nome AS status
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_status ON (tab_cobrancas.cot_id = tab_cobrancas_status.cot_id)
			WHERE
				tab_cobrancas.coa_id IN (
					SELECT
						coa_id
					FROM
						tab_cobrancas_retornos_registros
					WHERE
						coe_id = {$retorno_id} AND
						cog_tipo = '{$tipo}'
				)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($this->permissoes["cobrancas"]["ver_dg"]) {
				$dados["link"] = "javascript:verCobranca({$dados["id"]})";
			}
			if ($this->permissoes["clientes"]["ver_df"]) {
				$dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			$dados["nosso_numero"] = $dados["parametros"]["boleto"]["nosso_numero"];
			$dados["valor"] = $this->formataDinheiro($dados["valor"]);
			$dados["contador"] = number_format(count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
			
		}
		
		$cabecalho = array ("contador" => "#", "cliente_id" => "ID", "codigo" => "Código", "nosso_numero" => "Nosso Número", "data_geracao" => "Geração", "data_vencimento" => "Venc.", "valor" => "Valor", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array("valor" => "formataDinheiro"),  array(20, 60, 100, 70, 70, 70, 90), null, array("right", "center", "center", "center", "center", "center", "right", "left"));
		
		return $listagem->conteudo;
		
	}
	
	##############
	### Baixas ###
	##############
	
	function baixas() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->temPermissao("exportar_detalhado")) $botoes[] = '<input type="button" name="exportar_detalhado" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar_detalhado&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.baixas.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[baixas]', $this->buscaRegistros($this->dados_item["id"], 'BAIXA'), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	#################
	### Rejeições ###
	#################
	
	function rejeicoes() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->temPermissao("exportar_detalhado")) $botoes[] = '<input type="button" name="exportar_detalhado" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar_detalhado&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.rejeicoes.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[rejeicoes]', $this->buscaRegistros($this->dados_item["id"], 'REJEICAO'), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	###################
	### Liquidações ###
	###################
	
	function liquidacoes() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->temPermissao("exportar_detalhado")) $botoes[] = '<input type="button" name="exportar_detalhado" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar_detalhado&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.liquidacoes.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[liquidacoes]', $this->buscaRegistros($this->dados_item["id"], 'LIQUIDACAO'), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	################
	### Agrupado ###
	################
	
	function agrupado() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.agrupado.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[registros_agrupados]', $this->geraRegistrosAgrupadosListagem($this->buscaRegistrosAgrupados($this->dados_item["id"])), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	#################
	### Detalhado ###
	#################
	
	function detalhado() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$botoes = array();
			if ($this->temPermissao("exportar_detalhado")) $botoes[] = '<input type="button" name="exportar_detalhado" class="buttonAzulEscuroBranco11" value="Exportar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=exportar_detalhado&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.detalhado.inc.php");
			
			$this->conteudo = str_replace('[acoes]', $this->geraAcoes(array("ver" => "Ver", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquidações", "agrupado" => "Agrupado", "detalhado" => "Detalhado")), $this->conteudo);
			$this->conteudo = str_replace('[formato]', $this->dados_item["formato_nome"], $this->conteudo);
			$this->conteudo = str_replace('[data_geracao]', $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace('[data_publicacao]', $this->dados_item["data_publicacao"], $this->conteudo);
			$this->conteudo = str_replace('[detalhes]', $this->buscaDetalhesRetorno($this->dados_item["id"], true), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function buscaDetalhesRetorno($retorno_id, $tabela = false, $format = true) {
		
		$detalhes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_pai AS coa_pai,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_valor_total AS valor_recebido,
				tab_cobrancas.fco_id AS fatura_lote_cobranca_id,
				tab_cobrancas.fat_id AS fatura_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj
			FROM
				tab_cobrancas_retornos_registros
				INNER JOIN tab_cobrancas ON (tab_cobrancas_retornos_registros.coa_id = tab_cobrancas.coa_id)
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_cobrancas_retornos_registros.coe_id = $retorno_id AND
				tab_cobrancas_retornos_registros.cog_tipo = 'LIQUIDACAO' AND
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1 AND cot_manual = 0)
			ORDER BY
				tab_cobrancas.coa_codigo");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->temPermissao("ver_df", "clientes")) {
				$dados["cliente_id"] = "<a href=\"?m=clientes&a=ver_df&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente_id"]}</a>";
			}
			if ($this->permissoes["cobrancas"]["ver_dg"]) {
				$dados["link"] = "javascript:verCobranca({$dados["id"]})";
			}
			if ($dados["fatura_lote_cobranca_id"]) {
				$dados = array_merge($dados, $this->buscaDadosFatura($dados["fatura_lote_cobranca_id"], $format));
			}
			
			$dados["valor_total"] = $dados["valor_scm"] + $dados["valor_sva"] + $dados["valor_equipamentos"] + $dados["valor_instalacao"] + $dados["valor_servicos"] + $dados["valor_garantia"] + $dados["valor_juros"];
			$dados["valor_diferenca"] = $dados["valor_recebido"] - $dados["valor_total"];
			$dados["valor_servicos_instalacao"] = $dados["valor_servicos"] + $dados["valor_instalacao"] + $dados["valor_garantia"];
			
			if ($format) {
				$dados["nf_valor_scm"] = $this->formataDinheiro($dados["nf_valor_scm"]);
				$dados["nf_valor_sva"] = $this->formataDinheiro($dados["nf_valor_sva"]);
				$dados["valor_scm"] = $this->formataDinheiro($dados["valor_scm"]);
				$dados["valor_sva"] = $this->formataDinheiro($dados["valor_sva"]);
				$dados["valor_equipamentos"] = $this->formataDinheiro($dados["valor_equipamentos"]);
				$dados["valor_instalacao"] = $this->formataDinheiro($dados["valor_instalacao"]);
				$dados["valor_servicos"] = $this->formataDinheiro($dados["valor_servicos"]);
				$dados["valor_garantia"] = $this->formataDinheiro($dados["valor_garantia"]);
				$dados["valor_servicos_instalacao"] = $this->formataDinheiro($dados["valor_servicos_instalacao"]);
				$dados["valor_juros"] = $this->formataDinheiro($dados["valor_juros"]);
				$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"]);
				$dados["valor_diferenca"] = $this->formataDinheiro($dados["valor_diferenca"]);
			} else {
				$dados["nf_valor_scm"] = $dados["nf_valor_scm"] / 100;
				$dados["nf_valor_sva"] = $dados["nf_valor_sva"] / 100;
				$dados["valor_scm"] = $dados["valor_scm"] / 100;
				$dados["valor_sva"] = $dados["valor_sva"] / 100;
				$dados["valor_equipamentos"] = $dados["valor_equipamentos"] / 100;
				$dados["valor_instalacao"] = $dados["valor_instalacao"] / 100;
				$dados["valor_servicos"] = $dados["valor_servicos"] / 100;
				$dados["valor_garantia"] = $dados["valor_garantia"] / 100;
				$dados["valor_servicos_instalacao"] = $dados["valor_servicos_instalacao"] / 100;
				$dados["valor_juros"] = $dados["valor_juros"] / 100;
				$dados["valor_total"] = $dados["valor_total"] / 100;
				$dados["valor_diferenca"] = $dados["valor_diferenca"] / 100;
			}
			
			$detalhes[] = $dados;
			
		}
		
		if ($tabela) {
			
			$cabecalho = array ("cliente_id" => "ID", "codigo" => "Boleto", "valor_scm" => "SCM", "valor_sva" => "SVA", "valor_equipamentos" => "Equip.", "valor_instalacao" => "Instalação", "valor_servicos" => "Serviços", "valor_garantia" => "Garantia", "valor_juros" => "Juros", "valor_total" => "Total", "valor_diferenca" => "Diferença");
			$listagem = controle::instanciaObjeto("listagem", $cabecalho, $detalhes, 0, array("valor_diferenca" => "formataDinheiro", "valor_total" => "formataDinheiro", "valor_scm" => "formataDinheiro", "valor_sva" => "formataDinheiro", "valor_equipamentos" => "formataDinheiro", "valor_instalacao" => "formataDinheiro", "valor_juros" => "formataDinheiro", "valor_servicos" => "formataDinheiro", "valor_garantia" => "formataDinheiro"), null, null, array("center", "center", "right", "right", "right", "right", "right", "right", "right", "right", "right"));
			
			return $listagem->conteudo;
			
		}
		
		return $detalhes;
		
	}
	
	function buscaDadosNF($cobranca_id, $ajuste_categoria_id) {
		
		$nf = array("numero" => array(), "emissao" => array(), "valor" => 0);
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_ajustes_financeiros.ajf_nf_numero AS numero,
				tab_ajustes_financeiros.ajf_nf_emissao AS emissao,
				tab_ajustes_financeiros.ajf_nf_valor AS valor
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_ajustes_financeiros.afc_id IN ($ajuste_categoria_id) AND
				tab_ajustes_financeiros.ajf_nf_numero IS NOT NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$nf["numero"][] = $dados["numero"];
			if (!in_array($dados["emissao"], $nf["emissao"])) {
				$nf["emissao"][] = $dados["emissao"];
			}
			$nf["valor"] += $dados["valor"];
			
		}
		
		if (count($nf["numero"])) {
			
			$nf["numero"] = implode(", ", $nf["numero"]);
			if (count($nf["emissao"]) > 1) {
				for ($i = 0; $i < count($nf["emissao"]); $i++) {
					$nf["emissao"][$i] = date("d/m/Y", strtotime($nf["emissao"][$i]));
				}
				$nf["emissao"] = implode(", ", $nf["emissao"]);
			} else {
				$nf["emissao"] = $nf["emissao"][0];
			}
			$nf["valor"] = $nf["valor"] / 100;
			
			return $nf;
			
		}
		
		return null;
		
	}
	
	function buscaDadosFatura($fatura_lote_cobranca_id, $format = true) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_faturas.fat_id AS fatura_id,".($format ? "
				DATE_FORMAT(tab_faturas.fat_data_emissao, '%d/%m/%Y') AS nf_emissao," : "
				tab_faturas.fat_data_emissao AS nf_emissao,")."
				tab_faturas.fat_numero AS nf_numero,
				(SELECT SUM(fai_valor_total) FROM tab_faturas_itens WHERE tab_faturas_itens.fat_id = tab_faturas.fat_id AND tab_faturas_itens.fai_tipo = 'SCM') AS nf_valor_scm,
				(SELECT SUM(fai_valor_total) FROM tab_faturas_itens WHERE tab_faturas_itens.fat_id = tab_faturas.fat_id AND tab_faturas_itens.fai_tipo = 'SVA') AS nf_valor_sva,
				tab_faturas_lotes_cobrancas.fco_valor_scm AS valor_scm,
				tab_faturas_lotes_cobrancas.fco_valor_sva AS valor_sva,
				tab_faturas_lotes_cobrancas.fco_valor_equipamentos AS valor_equipamentos,
				tab_faturas_lotes_cobrancas.fco_valor_instalacao AS valor_instalacao,
				tab_faturas_lotes_cobrancas.fco_valor_servicos AS valor_servicos,
				tab_faturas_lotes_cobrancas.fco_valor_garantia AS valor_garantia,
				tab_faturas_lotes_cobrancas.fco_valor_juro AS valor_juros
			FROM
				tab_faturas_lotes_cobrancas
				LEFT OUTER JOIN tab_faturas ON (tab_faturas.fat_id = tab_faturas_lotes_cobrancas.fat_id AND tab_faturas.fat_cancelada = 0)
			WHERE
				tab_faturas_lotes_cobrancas.fco_id = $fatura_lote_cobranca_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	##########################
	### Exportar Detalhado ###
	##########################
	
	function exportar_detalhado() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
			header("Content-Disposition: attachment; filename=".str_replace(array("(", ")", " "), "", $this->dados_item["formato_nome"])."-".str_replace("/", "-", $this->dados_item["data_publicacao"]).".xlsx");
			
			print $this->geraPlanilhaExportacao();
			die();
			
		} else {
			header("Location: ./?m={$this->dados_modulo["nome"]}");
		}
		
	}
	
	function geraPlanilhaExportacao() {
		
		set_time_limit(0);
		
		require_once "_global/phpexcel/PHPExcel.php";
		require_once "_global/phpexcel/PHPExcel/IOFactory.php";
		require_once "_global/phpexcel/PHPExcel/Writer/Excel2007.php";
		
		PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array("memoryCacheSize" => "8MB"));
		
		$php_excel = new PHPExcel();
		
		$active_sheet = $php_excel->getActiveSheet();
		
		$active_sheet->setTitle(str_replace("/", "-", $this->dados_item["data_publicacao"]));
		
		$php_excel->getProperties()->setCreator(utf8_encode("Olé Telecom"));
		$php_excel->getProperties()->setLastModifiedBy(utf8_encode("Olé Telecom"));
		$php_excel->getProperties()->setTitle(utf8_encode("Relatório ".mb_strtoupper($this->dados_item["formato_nome"])));
		$php_excel->getProperties()->setSubject("");
		$php_excel->getProperties()->setDescription("");
		
		$active_sheet->getProtection()->setAutoFilter(true);
		$active_sheet->getProtection()->setDeleteColumns(true);
		$active_sheet->getProtection()->setDeleteRows(true);
		$active_sheet->getProtection()->setFormatCells(true);
		$active_sheet->getProtection()->setFormatColumns(true);
		$active_sheet->getProtection()->setFormatRows(true);
		$active_sheet->getProtection()->setInsertColumns(true);
		$active_sheet->getProtection()->setInsertHyperlinks(true);
		$active_sheet->getProtection()->setObjects(true);
		$active_sheet->getProtection()->setPivotTables(true);
		$active_sheet->getProtection()->setScenarios(true);
		$active_sheet->getProtection()->setSort(true);
		$active_sheet->getProtection()->setPassword("inTr@NET", false);
		$active_sheet->getProtection()->setSheet(true);
		
		$active_sheet->getPageMargins()->setTop(0.4);
		$active_sheet->getPageMargins()->setLeft(0.2);
		$active_sheet->getPageMargins()->setRight(0.2);
		$active_sheet->getPageMargins()->setBottom(0.2);
		$active_sheet->getPageMargins()->setHeader(0.2);
		
		$active_sheet->getHeaderFooter()->setAlignWithMargins(true);
		$active_sheet->getHeaderFooter()->setOddHeader(utf8_encode("&LRELATÓRIO ".mb_strtoupper($this->dados_item["formato_nome"])."&C{$this->dados_item["data_publicacao"]}&R&P / &N"));
		
		$objPageSetup = new PHPExcel_Worksheet_PageSetup();
		
		$objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$objPageSetup->setRowsToRepeatAtTop(array(1, 3));
		$objPageSetup->setFitToHeight(0);
		$objPageSetup->setFitToWidth(1);
		
		$active_sheet->setPageSetup($objPageSetup);
		
		$active_sheet->SetCellValue("A1", utf8_encode("Cliente"));
		$active_sheet->SetCellValue("B1", utf8_encode("CPF/CNPJ"));
		$active_sheet->SetCellValue("C1", utf8_encode("Fatura"));
		$active_sheet->SetCellValue("D1", utf8_encode("Recebido"));
		$active_sheet->SetCellValue("J1", utf8_encode("NF de Comunicação"));
		$active_sheet->SetCellValue("O1", utf8_encode("NF de Produto"));
		$active_sheet->SetCellValue("R1", utf8_encode("NF de Serviço"));
		$active_sheet->SetCellValue("U1", utf8_encode("NF Faltantes"));
		
		$active_sheet->SetCellValue("D2", utf8_encode("SCM"));
		$active_sheet->SetCellValue("E2", utf8_encode("SVA"));
		$active_sheet->SetCellValue("F2", utf8_encode("Produto"));
		$active_sheet->SetCellValue("G2", utf8_encode("Serviço"));
		$active_sheet->SetCellValue("H2", utf8_encode("Juros"));
		$active_sheet->SetCellValue("I2", utf8_encode("Total"));
		
		$active_sheet->SetCellValue("J2", utf8_encode("Nº"));
		$active_sheet->SetCellValue("K2", utf8_encode("Emissão"));
		$active_sheet->SetCellValue("L2", utf8_encode("SCM"));
		$active_sheet->SetCellValue("M2", utf8_encode("SVA"));
		$active_sheet->SetCellValue("N2", utf8_encode("Total"));
		
		$active_sheet->SetCellValue("O2", utf8_encode("Nº"));
		$active_sheet->SetCellValue("P2", utf8_encode("Emissão"));
		$active_sheet->SetCellValue("Q2", utf8_encode("Total"));
		
		$active_sheet->SetCellValue("R2", utf8_encode("Nº"));
		$active_sheet->SetCellValue("S2", utf8_encode("Emissão"));
		$active_sheet->SetCellValue("T2", utf8_encode("Total"));
		
		$active_sheet->SetCellValue("U2", utf8_encode("SCM"));
		$active_sheet->SetCellValue("V2", utf8_encode("SVA"));
		$active_sheet->SetCellValue("W2", utf8_encode("Produto"));
		$active_sheet->SetCellValue("X2", utf8_encode("Serviço"));
		$active_sheet->SetCellValue("Y2", utf8_encode("Total"));
		
		$active_sheet->mergeCells("A1:A2");
		$active_sheet->mergeCells("B1:B2");
		$active_sheet->mergeCells("C1:C2");
		$active_sheet->mergeCells("D1:I1");
		$active_sheet->mergeCells("J1:N1");
		$active_sheet->mergeCells("O1:Q1");
		$active_sheet->mergeCells("R1:T1");
		$active_sheet->mergeCells("U1:Y1");
		
		$active_sheet->getColumnDimension("A")->setWidth(30.71);
		$active_sheet->getColumnDimension("B")->setWidth(18.00);
		$active_sheet->getColumnDimension("C")->setWidth(13.14);
		$active_sheet->getColumnDimension("D")->setWidth(10.14);
		$active_sheet->getColumnDimension("E")->setWidth(10.14);
		$active_sheet->getColumnDimension("F")->setWidth(10.14);
		$active_sheet->getColumnDimension("G")->setWidth(10.14);
		$active_sheet->getColumnDimension("H")->setWidth(10.14);
		$active_sheet->getColumnDimension("I")->setWidth(10.14);
		$active_sheet->getColumnDimension("J")->setWidth( 5.00);
		$active_sheet->getColumnDimension("K")->setWidth( 8.71);
		$active_sheet->getColumnDimension("L")->setWidth(10.14);
		$active_sheet->getColumnDimension("M")->setWidth(10.14);
		$active_sheet->getColumnDimension("N")->setWidth(10.14);
		$active_sheet->getColumnDimension("O")->setWidth( 5.00);
		$active_sheet->getColumnDimension("P")->setWidth( 8.71);
		$active_sheet->getColumnDimension("Q")->setWidth(10.14);
		$active_sheet->getColumnDimension("R")->setWidth( 5.00);
		$active_sheet->getColumnDimension("S")->setWidth( 8.71);
		$active_sheet->getColumnDimension("T")->setWidth(10.14);
		$active_sheet->getColumnDimension("U")->setWidth(10.14);
		$active_sheet->getColumnDimension("V")->setWidth(10.14);
		$active_sheet->getColumnDimension("W")->setWidth(10.14);
		$active_sheet->getColumnDimension("X")->setWidth(10.14);
		$active_sheet->getColumnDimension("Y")->setWidth(10.14);
		
		$active_sheet->getRowDimension(3)->setRowHeight(5.00);
		
		$active_sheet->getStyle("A1:Y2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$active_sheet->getStyle("A1:Y2")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$active_sheet->getStyle("A1:Y2")->getFont()->setBold(true);
		
		$active_sheet->getStyle("A1:Y2")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$active_sheet->getStyle("A1:Y2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->setStartColor(new PHPExcel_Style_Color("0099CCFF"));
		
		$active_sheet->freezePane("B4");
		
		$detalhes = $this->buscaDetalhesRetorno($this->dados_item["id"], false, false);
		
		for ($i = 0; $i < count($detalhes); $i++) {
			
			$linha = 4 + $i;
			
			$active_sheet->getCell("A$linha")->setValue(utf8_encode($detalhes[$i]["cliente_nome"]));
			$active_sheet->getCell("B$linha")->setValue(utf8_encode($detalhes[$i]["cliente_cpf_cnpj"]));
			$active_sheet->getCell("C$linha")->setValue(utf8_encode($detalhes[$i]["codigo"]));
			
			if ($detalhes[$i]["valor_scm"]) {
				$active_sheet->getCell("D$linha")->setValue($detalhes[$i]["valor_scm"]);
			}
			
			if ($detalhes[$i]["valor_sva"]) {
				$active_sheet->getCell("E$linha")->setValue($detalhes[$i]["valor_sva"]);
			}
			
			if ($detalhes[$i]["valor_equipamentos"]) {
				
				$active_sheet->getCell("F$linha")->setValue($detalhes[$i]["valor_equipamentos"]);
				
				$dados_nf = $this->buscaDadosNF($detalhes[$i]["id"], 23);
				
				if ($dados_nf) {
					
					$active_sheet->getCell("O$linha")->setValue($dados_nf["numero"]);
					if (strlen($dados_nf["emissao"]) == 10) {
						$active_sheet->getCell("P$linha")->setValue(PHPExcel_Shared_Date::PHPToExcel(strtotime($dados_nf["emissao"])));
					} else {
						$active_sheet->getCell("P$linha")->setValue($dados_nf["emissao"]);
					}
					$active_sheet->getCell("Q$linha")->setValue($dados_nf["valor"]);
					
				} else {
					
					$objConditional = new PHPExcel_Style_Conditional();
					$objConditional->setConditionType(PHPExcel_Style_Conditional::CONDITION_EXPRESSION);
					$objConditional->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_EQUAL);
					$objConditional->addCondition("O$linha=0");
					$objConditional->getStyle()->getNumberFormat()->setFormatCode("0");
					$objConditional->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN);
					$objConditional->getStyle()->getFill()->getStartColor()->setRGB("FFCC00");
					$objConditional->getStyle()->getFill()->getEndColor()->setRGB("FF9933");
					
					$active_sheet->getStyle("O$linha")->setConditionalStyles(array($objConditional))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
					
					$objConditional = new PHPExcel_Style_Conditional();
					$objConditional->setConditionType(PHPExcel_Style_Conditional::CONDITION_EXPRESSION);
					$objConditional->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_EQUAL);
					$objConditional->addCondition("P$linha=0");
					$objConditional->getStyle()->getNumberFormat()->setFormatCode("dd/mm/yy");
					$objConditional->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN);
					$objConditional->getStyle()->getFill()->getStartColor()->setRGB("FFCC00");
					$objConditional->getStyle()->getFill()->getEndColor()->setRGB("FF9933");
					
					$active_sheet->getStyle("P$linha")->setConditionalStyles(array($objConditional))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
					
					$objConditional = new PHPExcel_Style_Conditional();
					$objConditional->setConditionType(PHPExcel_Style_Conditional::CONDITION_EXPRESSION);
					$objConditional->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_EQUAL);
					$objConditional->addCondition("Q$linha=0");
					$objConditional->getStyle()->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
					$objConditional->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN);
					$objConditional->getStyle()->getFill()->getStartColor()->setRGB("FFCC00");
					$objConditional->getStyle()->getFill()->getEndColor()->setRGB("FF9933");
					
					$active_sheet->getStyle("Q$linha")->setConditionalStyles(array($objConditional))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
					
				}
				
			}
			
			if ($detalhes[$i]["valor_servicos_instalacao"]) {
				
				$active_sheet->getCell("G$linha")->setValue($detalhes[$i]["valor_servicos_instalacao"]);
				
				$dados_nf = $this->buscaDadosNF($detalhes[$i]["id"], "19, 20");
				
				if ($dados_nf) {
					
					$active_sheet->getCell("R$linha")->setValue($dados_nf["numero"]);
					if (strlen($dados_nf["emissao"]) == 10) {
						$active_sheet->getCell("S$linha")->setValue(PHPExcel_Shared_Date::PHPToExcel(strtotime($dados_nf["emissao"])));
					} else {
						$active_sheet->getCell("S$linha")->setValue($dados_nf["emissao"]);
					}
					$active_sheet->getCell("T$linha")->setValue($dados_nf["valor"]);
					
				} else {
					
					$objConditional = new PHPExcel_Style_Conditional();
					$objConditional->setConditionType(PHPExcel_Style_Conditional::CONDITION_EXPRESSION);
					$objConditional->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_EQUAL);
					$objConditional->addCondition("R$linha=0");
					$objConditional->getStyle()->getNumberFormat()->setFormatCode("0");
					$objConditional->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN);
					$objConditional->getStyle()->getFill()->getStartColor()->setRGB("FFCC00");
					$objConditional->getStyle()->getFill()->getEndColor()->setRGB("FF9933");
					
					$active_sheet->getStyle("R$linha")->setConditionalStyles(array($objConditional))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
					
					$objConditional = new PHPExcel_Style_Conditional();
					$objConditional->setConditionType(PHPExcel_Style_Conditional::CONDITION_EXPRESSION);
					$objConditional->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_EQUAL);
					$objConditional->addCondition("S$linha=0");
					$objConditional->getStyle()->getNumberFormat()->setFormatCode("dd/mm/yy");
					$objConditional->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN);
					$objConditional->getStyle()->getFill()->getStartColor()->setRGB("FFCC00");
					$objConditional->getStyle()->getFill()->getEndColor()->setRGB("FF9933");
					
					$active_sheet->getStyle("S$linha")->setConditionalStyles(array($objConditional))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
					
					$objConditional = new PHPExcel_Style_Conditional();
					$objConditional->setConditionType(PHPExcel_Style_Conditional::CONDITION_EXPRESSION);
					$objConditional->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_EQUAL);
					$objConditional->addCondition("T$linha=0");
					$objConditional->getStyle()->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
					$objConditional->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_PATTERN_DARKDOWN);
					$objConditional->getStyle()->getFill()->getStartColor()->setRGB("FFCC00");
					$objConditional->getStyle()->getFill()->getEndColor()->setRGB("FF9933");
					
					$active_sheet->getStyle("T$linha")->setConditionalStyles(array($objConditional))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
					
				}
				
			}
			
			if ($detalhes[$i]["valor_juros"]) {
				$active_sheet->getCell("H$linha")->setValue($detalhes[$i]["valor_juros"]);
			}
			
			$active_sheet->getCell("I$linha")->setValue("=D$linha+E$linha+F$linha+G$linha+H$linha");
			
			if (array_key_exists("nf_numero", $detalhes[$i]) && $detalhes[$i]["nf_numero"]) {
				$active_sheet->getCell("J$linha")->setValue($detalhes[$i]["nf_numero"]);
			}
			if (array_key_exists("nf_emissao", $detalhes[$i]) && $detalhes[$i]["nf_emissao"]) {
				$active_sheet->getCell("K$linha")->setValue(PHPExcel_Shared_Date::PHPToExcel(strtotime($detalhes[$i]["nf_emissao"])));
			}
			if (array_key_exists("nf_valor_scm", $detalhes[$i]) && $detalhes[$i]["nf_valor_scm"]) {
				$active_sheet->getCell("L$linha")->setValue($detalhes[$i]["nf_valor_scm"]);
			}
			if (array_key_exists("nf_valor_sva", $detalhes[$i]) && $detalhes[$i]["nf_valor_sva"]) {
				$active_sheet->getCell("M$linha")->setValue($detalhes[$i]["nf_valor_sva"]);
			}
			
			$active_sheet->getCell("N$linha")->setValue("=L$linha+M$linha");
			
			$active_sheet->getCell("U$linha")->setValue("=IF(L$linha<D$linha,D$linha-L$linha,0)");
			$active_sheet->getCell("V$linha")->setValue("=IF(M$linha<E$linha,E$linha-M$linha,0)");
			$active_sheet->getCell("W$linha")->setValue("=IF(Q$linha<F$linha,F$linha-Q$linha,0)");
			$active_sheet->getCell("X$linha")->setValue("=IF(T$linha<G$linha,G$linha-T$linha,0)");
			
			$active_sheet->getCell("Y$linha")->setValue("=U$linha+V$linha+W$linha+X$linha");
			
			$active_sheet->getStyle("C$linha")->getNumberFormat()->setFormatCode("000000000000");
			$active_sheet->getStyle("D$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("E$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("F$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("G$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("H$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("I$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("K$linha")->getNumberFormat()->setFormatCode("dd/mm/yy");
			$active_sheet->getStyle("L$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("M$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("N$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("P$linha")->getNumberFormat()->setFormatCode("dd/mm/yy");
			$active_sheet->getStyle("Q$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("S$linha")->getNumberFormat()->setFormatCode("dd/mm/yy");
			$active_sheet->getStyle("T$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("U$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("V$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("W$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("X$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			$active_sheet->getStyle("Y$linha")->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
			
		}
		
		$active_sheet->getCell("A".($linha + 1))->setValue("Totais");
		$active_sheet->mergeCells("A".($linha + 1).":C".($linha + 1));
		
		$active_sheet->getCell("D".($linha + 1))->setValue("=SUM(D4:D$linha)");
		$active_sheet->getStyle("D".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("E".($linha + 1))->setValue("=SUM(E4:E$linha)");
		$active_sheet->getStyle("E".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("F".($linha + 1))->setValue("=SUM(F4:F$linha)");
		$active_sheet->getStyle("F".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("G".($linha + 1))->setValue("=SUM(G4:G$linha)");
		$active_sheet->getStyle("G".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("H".($linha + 1))->setValue("=SUM(H4:H$linha)");
		$active_sheet->getStyle("H".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("I".($linha + 1))->setValue("=SUM(I4:I$linha)");
		$active_sheet->getStyle("I".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("L".($linha + 1))->setValue("=SUM(L4:L$linha)");
		$active_sheet->getStyle("L".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("M".($linha + 1))->setValue("=SUM(M4:M$linha)");
		$active_sheet->getStyle("M".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("N".($linha + 1))->setValue("=SUM(N4:N$linha)");
		$active_sheet->getStyle("N".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("Q".($linha + 1))->setValue("=SUM(Q4:Q$linha)");
		$active_sheet->getStyle("Q".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("T".($linha + 1))->setValue("=SUM(T4:T$linha)");
		$active_sheet->getStyle("T".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("U".($linha + 1))->setValue("=SUM(U4:U$linha)");
		$active_sheet->getStyle("U".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("V".($linha + 1))->setValue("=SUM(V4:V$linha)");
		$active_sheet->getStyle("V".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("W".($linha + 1))->setValue("=SUM(W4:W$linha)");
		$active_sheet->getStyle("W".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("X".($linha + 1))->setValue("=SUM(X4:X$linha)");
		$active_sheet->getStyle("X".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		$active_sheet->getCell("Y".($linha + 1))->setValue("=SUM(Y4:Y$linha)");
		$active_sheet->getStyle("Y".($linha + 1))->getNumberFormat()->setFormatCode("#,##0.00;[red]-#,##0.00;");
		
		$linha++;
		
		$active_sheet->getStyle("A4:Y$linha")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$active_sheet->getStyle("A1:Y$linha")->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("D1:D$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("I2:I$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("J1:J$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("O1:O$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("R1:R$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("U1:U$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$active_sheet->getStyle("Y2:Y$linha")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		
		$active_sheet->getStyle("A$linha:Y$linha")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		
		$active_sheet->getStyle("A$linha:Y$linha")->getFont()->setBold(true);
		$active_sheet->getStyle("A$linha:Y$linha")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->setStartColor(new PHPExcel_Style_Color("0099CCFF"));
		
		$php_excel->getActiveSheet()->setSelectedCell("B4");
		
		$php_excel_writer = new PHPExcel_Writer_Excel2007($php_excel);
		
		$php_excel_writer->setPreCalculateFormulas(true);
		
		ob_start();
		
		$php_excel_writer->save("php://output");
		
		$content = ob_get_contents();
		
		ob_end_clean();
		
		$php_excel->garbageCollect();
		
		return $content;
		
	}
	
	################
	### Publicar ###
	################
	
	function publicar() {
		
		if (!$_POST) {
			
			$erro = array(
				"1" => "Formato de arquivo inválido!",
				"2" => "Problema no envio do arquivo!",
				"3" => "Este arquivo já foi publicado!"
			);
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="publicar" class="buttonAzulEscuroBranco11" value="Publicar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("cobrancas_retornos.publicar.inc.php");
			
			$this->conteudo = str_replace("[formatos]", $this->geraSelect($this->buscaFormatos(), null, true, "formato", "300px"), $this->conteudo);
			$this->conteudo = str_replace("[mensagem]", $_GET["erro"] ? (array_key_exists($_GET["erro"], $erro) ? "ERRO: {$erro[$_GET["erro"]]}" : "ERROR: {$_GET["erro"]}") : "", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=publicar", $this->conteudo);
			
		} else {
			
			if (!$_FILES["arquivo"]["error"]) {
				
				set_time_limit(0);
				ini_set("memory_limit", "128M");
				
				$conteudo = $this->carregaArquivo($_FILES["arquivo"]["tmp_name"]);
				
				$dados_formato = $this->buscaDadosFormato($_POST["formato"]);
				
				$caminho = dirname(__FILE__)."/cobrancas_retornos.{$dados_formato["parametros"]["publicacao"]}.class.inc.php";
				
				if (file_exists($caminho)) {
					
					require_once $caminho;
					
					$classe = "cobrancas_retornos_{$dados_formato["parametros"]["publicacao"]}";
					
					$publicacao = new $classe($this->bancodedados, $this, $dados_formato["id"], $conteudo);
					
					$retorno_id = $publicacao->importar($_POST["ignorar_sequencial"] ? true : false);
					
					if (!$retorno_id) {
						$erro = $publicacao->getErro();
					}
					
				} else {
					
					$retornos_importacao = controle::instanciaObjeto("cobrancas_retornos.importacao", $this->bancodedados);
					
					$dados_retorno = $retornos_importacao->importar($_POST["formato"], $conteudo);
					
					if ($this->buscaRetorno($_POST["formato"], $dados_retorno["data_geracao"])) {
						$this->compararRetorno($_POST["formato"], $conteudo, $dados_retorno);
					} else if ($dados_retorno) {
						$retorno_id = $this->publicarRetorno($_POST["formato"], $conteudo, $dados_retorno);
					} else {
						$erro = 1;
					}
					
				}
				
			} else {
				$erro = 2;
			}
			
			if ($erro) {
				
				header("Location: ./?m=cobrancas_retornos&a=publicar&erro=$erro");
				
			} else {
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $retorno_id);
				
				header("Location: ./?m=cobrancas_retornos&a=ver&i=$retorno_id");
				
			}
			
		}
		
	}
	
	function compararRetorno($formato_id, &$conteudo, &$dados_retorno) {
		
		print "<PRE>";
		
		// Gera Retorno
		$retorno_id = $this->buscaRetorno($formato_id, $dados_retorno["data_geracao"]);
		
		print "******************************\n";
		print "RETORNO $retorno_id\n";
		print "******************************\n";
		
		$valor_total = 0;
		$total_registros = 0;
		
		if ($retorno_id) {
			for ($i = 0; $i < count($dados_retorno["registros"]); $i++) {
				$registro = $dados_retorno["registros"][$i];
				$total_registros++;
				$valor_total += $registro["valor_total"];
				if ($registro["id"]) {
					if (!$this->bancodedados->resultado($this->bancodedados->query("SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE coe_id = $retorno_id AND coa_id = {$registro["id"]}"))) {
						print "{$registro["codigo"]}\tNAO ASSOCIADA AO RETORNO\n";
					}
					$dados_cobranca = $this->buscaDadosCobranca($registro["id"]);
					if ($registro["valor_iof"]) {
						print "{$registro["codigo"]}\tIOF ({$registro["valor_iof"]})\n";
						$this->bancodedados->query("UPDATE tab_cobrancas SET coa_valor_iof = {$registro["valor_iof"]} WHERE coa_id = {$registro["id"]}");
					}
					if ($registro["valor_total"] == $dados_cobranca["valor_total"]) {
						print "{$registro["codigo"]}\tOK\n";
					} else {
						print "{$registro["codigo"]}\tVALOR DIFERENTE (Arquivo: {$registro["valor_total"]} / Atual: {$dados_cobranca["valor_total"]})\n";
					}
				} else {
					if (!$this->bancodedados->resultado($this->bancodedados->query("SELECT COUNT(*) FROM tab_cobrancas_retornos_nao_encontradas WHERE coe_id = $retorno_id AND crn_codigo = '{$registro["codigo"]}'"))) {
						$this->registraNaoEncontrada($retorno_id, $registro["codigo"], $registro["data_vencimento"], $registro["valor_original"], $registro["valor_total"]);
					}
					print "{$registro["codigo"]}\tNAO ENCONTRADA\n";
				}
			}
		}
		
		print "******************************\n";
		print "Registros: $total_registros\n";
		print "Total: $valor_total\n";
		print "******************************\n";
		print "</PRE>";
		
		die();
		
	}
	
	function publicarRetorno($formato_id, &$conteudo, &$dados_retorno) {
		
		$retorno_id = $this->geraRetorno($formato_id, $dados_retorno["data_geracao"], $conteudo);
		
		if ($retorno_id) {
			for ($i = 0; $i < count($dados_retorno["registros"]); $i++) {
				$registro = $dados_retorno["registros"][$i];
				if ($registro["id"]) {
					if ($registro["erro"]) {
						$this->registraErro($retorno_id, $registro["id"], $registro["erro"]);
						$this->registraComentarioCobranca($registro["id"], "<b>Erro:</b> {$registro["erro"]}");
					} else {
						$this->darBaixa($registro, $retorno_id);
					}
				} else {
					$this->registraNaoEncontrada($retorno_id, $registro["codigo"], $registro["data_vencimento"], $registro["valor_original"], $registro["valor_total"]);
				}
			}
		}
		
		return $retorno_id;
		
	}
	
	function contaRetornos($cobranca_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(coe_id) AS quantidade
			FROM
				tab_cobrancas_retornos_registros
			WHERE
				coa_id = $cobranca_id");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function registraErro($retorno_id, $cobranca_id, $descricao) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(cos_id)
			FROM
				tab_cobrancas_retornos_erros
			WHERE
				coe_id = $retorno_id AND
				coa_id = $cobranca_id AND
				cos_descricao = '$descricao'");
		
		if ($this->bancodedados->resultado($query) == 0) {
			
			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_retornos_erros
				SET
					coe_id = $retorno_id,
					coa_id = $cobranca_id,
					cos_descricao = '$descricao'");
			
		}
		
	}
	
	function registraNaoEncontrada($retorno_id, $codigo, $data_vencimento, $valor_original, $valor_pago) {
		
		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_retornos_nao_encontradas
			SET
				coe_id = $retorno_id,
				crn_codigo = '$codigo',
				crn_data_vencimento = '$data_vencimento',
				crn_valor_original = $valor_original,
				crn_valor_pago = $valor_pago");
		
	}
	
	function removeErro($retorno_id, $cobranca_id, $descricao) {
		
		$this->bancodedados->query("
			DELETE FROM
				tab_cobrancas_retornos_erros
			WHERE
				coe_id = $retorno_id AND
				coa_id = $cobranca_id AND
				cos_descricao = '$descricao'");
		
	}
	
	function geraRetorno($formato_id, $data_geracao, &$conteudo) {
		
		$query = $this->bancodedados->query("
			SELECT
				coe_id AS id
			FROM
				tab_cobrancas_retornos
			WHERE
				cof_id = $formato_id AND
				coe_data_geracao = '$data_geracao'");
		
		$retorno_id = $this->bancodedados->resultado($query);
		
		if ($retorno_id) {
			
			$this->bancodedados->query("
				UPDATE
					tab_cobrancas_retornos
				SET
					coe_conteudo = '$conteudo'
				WHERE
					coe_id = $retorno_id");
			
		} else {
			
			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_retornos
				SET
					cof_id = $formato_id,
					coe_data_geracao = '$data_geracao',
					coe_data_publicacao = NOW(),
					coe_conteudo = '$conteudo'");
			
			$retorno_id = $this->bancodedados->ultimoIdInserido();
			
		}
		
		return $retorno_id;
		
	}
	
	function buscaRetorno($formato_id, $data_geracao) {
		
		$query = $this->bancodedados->query("
			SELECT
				coe_id
			FROM
				tab_cobrancas_retornos
			WHERE
				cof_id = $formato_id AND
				coe_data_geracao = '$data_geracao'");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	function buscaDadosCobranca($cobranca_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id,
				coa_codigo AS codigo,
				coa_nosso_numero AS nosso_numero,
				coa_valor_original AS valor_original,
				coa_valor_bonificacao AS valor_bonificacao,
				coa_valor_total AS valor_total,
				coa_data_vencimento AS data_vencimento,
				coa_data_processamento AS data_processamento,
				coa_pai AS coa_pai,
				cli_id AS cliente_id,
				cot_id AS status_id,
				coa_grupo_id AS grupo_id,
				fco_id AS fatura_lote_cobranca_id
			FROM
				tab_cobrancas
			WHERE
				coa_id = $cobranca_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaCobrancasFilho($cobranca_id) {
		
		$cobrancas = array();
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id
			FROM
				tab_cobrancas
			WHERE
				coa_pai = $cobranca_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cobrancas[] = $dados["id"];
		}
		
		return $cobrancas;
		
	}
	
	function buscaDadosStatus($status_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cot_id AS id,
				cot_nome AS nome,
				cot_recebido AS recebido,
				cot_manual AS manual
			FROM
				tab_cobrancas_status
			WHERE
				cot_id = $status_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}
	
	var $clientes_negativacoes;
	       
    function insereLogRetorno($retornoId, $nosso_numero, $coa_id ) {
        
        $query = "
            INSERT INTO
                tab_log_retorno
            SET
                id_retorno = $retornoId,
                nosso_numero = '$nosso_numero',
                coa_id = '$coa_id',
                tipo = 'LIQUIDACAO',
                data_hora = NOW()";
        $this->bancodedados->query($query);
		
    }


    function darBaixa($registro, $retorno_id = null , $ehLote = false) {
		
		$dados_cobranca = $this->buscaDadosCobranca($registro["id"]);
		
		if ($dados_cobranca["status_id"] == 2 || $dados_cobranca["status_id"] == 3) {
			return;
        }
    
        if($ehLote && $retorno_id) {
            $this->insereLogRetorno($retorno_id, $dados_cobranca['nosso_numero'], $dados_cobranca['id']);
        }
		
		$registro["valor_acrescimo"] = 0;
		$registro["valor_decrescimo"] = 0;
		
		$dados_status = $this->buscaDadosStatus($dados_cobranca["status_id"]);
		
		if ($dados_status["recebido"]) {
			
			$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
			$cobrancas_geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
			
			if ($dados_cobranca["coa_pai"]) {
				$cobrancas->buscaDadosItem($dados_cobranca["coa_pai"]);
			} else {
				$cobrancas->buscaDadosItem($dados_cobranca["id"]);
			}
			
			$registro["id"] = $cobrancas_geracao->geraSegundaVia($cobrancas->dados_item, $cobrancas->dados_item["formato_id"], $cobrancas->dados_item["data_vencimento"], $cobrancas->dados_item["valor_original"]);
			
			$dados_cobranca = $this->buscaDadosCobranca($registro["id"]);
			
            if($retorno_id) {
				$this->registraErro($retorno_id, $registro["id"], "Baixa Duplicada");
            }
			
		}
		
		$registro["valor_juro"] = $this->calculaJuros($dados_cobranca, $registro["data_processamento"]);
		$registro["valor_desconto"] = $this->buscaBonificacao($dados_cobranca, $registro["data_processamento"]);
		
		$diferenca = $registro["valor_total"] - ($dados_cobranca["valor_original"] + $registro["valor_juro"] - $registro["valor_desconto"]);
		
		if ($diferenca > 0) {
			$registro["valor_acrescimo"] = $diferenca;
		} elseif ($diferenca < 0) {
			$registro["valor_decrescimo"] -= $diferenca;
		}
		
		$status_id = $registro["valor_decrescimo"] <= 1 ? 2 : 3;
		
		// Gera Vínculo c/ Retorno
		
		if($retorno_id) {
			$this->geraVinculoRetornoCobranca($registro["id"], $retorno_id);
        }
		
		// Desassocia dados fatura
		
		if ($dados_cobranca["fatura_lote_cobranca_id"] && $this->bancodedados->resultado($this->bancodedados->query("SELECT COUNT(*) FROM tab_cobrancas WHERE fco_id = {$dados_cobranca["fatura_lote_cobranca_id"]} AND cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1)")) > 0) {
			$this->bancodedados->query("UPDATE tab_cobrancas SET fco_id = NULL, fat_id = NULL WHERE coa_id = {$dados_cobranca["id"]}");
		}
		
		// Atualiza Cobrança
		
		$this->bancodedados->query("
			UPDATE
				tab_cobrancas
			SET
				tab_cobrancas.coa_data_processamento = CURDATE(),
				tab_cobrancas.coa_data_pagamento = '{$registro["data_processamento"]}',
				tab_cobrancas.coa_data_credito = '{$registro["data_credito"]}',
				tab_cobrancas.coa_valor_juro = {$registro["valor_juro"]},
				tab_cobrancas.coa_valor_desconto = {$registro["valor_desconto"]},
				tab_cobrancas.coa_valor_acrescimo = {$registro["valor_acrescimo"]},
				tab_cobrancas.coa_valor_decrescimo = {$registro["valor_decrescimo"]},
				tab_cobrancas.coa_valor_total = {$registro["valor_total"]},
				tab_cobrancas.coa_valor_iof = {$registro["valor_iof"]},
				tab_cobrancas.cot_id = $status_id
			WHERE
				tab_cobrancas.coa_id = {$registro["id"]}");
		
		// Descancela detalhes
		
		$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 0 WHERE coa_id = {$registro["id"]}");
		
		// Registra erro pagto parcial
		
		if ($retorno_id && $status_id == 3) {
			$this->registraErro($retorno_id, $registro["id"], "Pagamento Parcial");
		}
		
		// Registra Comentário
		
		$this->registraComentarioCobranca($registro["id"], "<b>Ação:</b> Baixa Automática (".($status_id == 2 ? "Total" : "Parcial").")<br><b>Pagamento:</b> ".$this->formataDataCompleta($registro["data_processamento"])."<br><b>Valor Total:</b> ".$this->formataDinheiro($registro["valor_total"]));
		
		// Baixa Cobrança Pai
		
		$comentario_segunda_via = "<b>Ação:</b> Baixa (Segunda Via)<br><b>Código:</b> {$dados_cobranca["codigo"]}<br><b>Processamento:</b> ".$this->formataDataCompleta($registro["data_processamento"])."<br><b>Valor Total:</b> ".$this->formataDinheiro($registro["valor_total"]);
		
		if ($dados_cobranca["coa_pai"]) {
			
			$query = "UPDATE tab_cobrancas SET cot_id = 11, coa_data_processamento = CURDATE() WHERE coa_id = {$dados_cobranca["coa_pai"]} AND cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido IS NULL OR cot_recebido = 0)";
			$this->bancodedados->query($query);
			$this->registraComentarioCobranca($dados_cobranca["coa_pai"], $comentario_segunda_via);
			
			// Baixa Cobranças Irmãs
			
			$cobrancas_filho = $this->buscaCobrancasFilho($dados_cobranca["coa_pai"]);
			
			if ($cobrancas_filho) {
				foreach ($cobrancas_filho as $cobranca_filho) {
					if ($cobranca_filho != $dados_cobranca["id"]) {
						$query = "UPDATE tab_cobrancas SET cot_id = 11, coa_data_processamento = CURDATE() WHERE coa_id = $cobranca_filho AND cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido IS NULL OR cot_recebido = 0)";
						$this->bancodedados->query($query);
						$this->registraComentarioCobranca($cobranca_filho, $comentario_segunda_via);
					}
				}
			}
			
		}
		
		// Baixa Cobranças Filhas
		
		$cobrancas_filho = $this->buscaCobrancasFilho($dados_cobranca["id"]);
		
		if ($cobrancas_filho) {
			foreach ($cobrancas_filho as $cobranca_filho) {
				$query = "UPDATE tab_cobrancas SET cot_id = 11, coa_data_processamento = CURDATE() WHERE coa_id = $cobranca_filho AND cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido IS NULL OR cot_recebido = 0)";
				$this->bancodedados->query($query);
				$this->registraComentarioCobranca($cobranca_filho, $comentario_segunda_via);
			}
		}
		
		$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
		
		$cobrancas->baixarFilhosDoGrupo($dados_cobranca["id"], $dados_cobranca["codigo"], $comentario_segunda_via);
		
		// Cancela Grupos Pais
		
		if ($dados_cobranca["grupo_id"]) {
			$cobrancas->cancelarGrupoPai($dados_cobranca["grupo_id"], "<b>Ação:</b> Cancelada<br><b>Comentário:</b> Baixa da cobrança <b>{$dados_cobranca["codigo"]}");
		}
		
		// Finaliza Bloqueio
		
		$cobrancas->finalizaBloqueio($dados_cobranca["cliente_id"]);
		
		// Atualiza Comissão Primeiro Pagamento
		
		$this->atualizaComissaoPrimeiroPagamento($dados_cobranca["id"]);
		
		// Atualiza Comissão Primeiro Pagamento
		
		$this->aprovaContrato($dados_cobranca["id"]);
		
		// Se negativado abre chamado financeiro
		
		if (!$this->clientes_negativacoes) {
			$this->clientes_negativacoes = controle::instanciaObjeto("clientes_negativacoes", $this->bancodedados, $this->permissoes, false);
		}
		
		if ($this->clientes_negativacoes->ehClienteNegativado($dados_cobranca["cliente_id"])) {
			$this->abreChamadoFinanceiro($dados_cobranca["cliente_id"]);
		}
		
	}
	
	function abreChamadoFinanceiro($cliente_id) {
		
		if (!$this->chamados) {
			$this->chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
		}
		
		$dados = array(
			"conteudo" => "Verificar baixa de cobrança de cliente negativado!",
			"sistema" => true,
			"solicitante" => "Sistema (Intranet)",
			"categoria" => 104,
			"prioridade" => 3,
			"tipo" => 5
		);
		
		$this->chamados->adicionarChamado($cliente_id, null, $dados);
		
	}
	
	function atualizaComissaoPrimeiroPagamento($cobranca_id) {
		
		if (!$this->cobrancas) {
			$this->cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
		}
		$this->cobrancas->atualizaComissaoPrimeiroPagamento($cobranca_id);
		
	}
	
	function aprovaContrato($cobranca_id) {
		
		if (!$this->cobrancas) {
			$this->cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
		}
		
		$this->cobrancas->aprovaContrato($cobranca_id);
		
	}
	
	function buscaCobrancaDetalhes($cobranca_id) {
		
		$cobranca_detalhes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cod_id AS id,
				cod_descricao AS descricao,
				cod_valor_original AS valor_original,
				cod_valor_final AS valor_final,
				DATE_FORMAT(cod_competencia_inicio, '%m') AS competencia_mes,
				DATE_FORMAT(cod_competencia_inicio, '%Y') AS competencia_ano,
				DATE_FORMAT(cod_competencia_inicio, '%d/%m/%Y') AS competencia_inicio,
				DATE_FORMAT(cod_competencia_termino, '%d/%m/%Y') AS competencia_termino,
				cod_competencia_termino - cod_competencia_inicio + 1 AS competencia_dias,
				ass_id AS ass_id,
				dec_id AS dec_id,
				ajf_id AS ajf_id,
				cod_pai AS cod_pai
			FROM
				tab_cobrancas_detalhes
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id
			ORDER BY
				tab_cobrancas_detalhes.cod_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cobranca_detalhes[] = $dados;
		}
		
		return $cobranca_detalhes;
		
	}
	
	function geraVinculoRetornoCobranca($cobranca_id, $retorno_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_cobrancas_retornos_registros
			WHERE
				coa_id = $cobranca_id AND
				coe_id = $retorno_id");
		
		if ($this->bancodedados->resultado($query) == 0) {
			
			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_retornos_registros
				SET
					coa_id = $cobranca_id,
					coe_id = $retorno_id");
			
		}
		
	}
	
	function registraComentarioCobranca($cobranca_id, $conteudo, $usuario_id = null) {
		
		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_comentarios
			SET
				com_datahora = NOW(),
				coa_id = $cobranca_id,
				com_conteudo = '$conteudo',
				usu_id = $usuario_id");
		
	}
	
	function buscaBonificacao($dados_cobranca, $data_pagamento) {
		
		if (strtotime($data_pagamento) <= strtotime($this->buscaProximoDiaUtil($dados_cobranca["data_vencimento"]))) {
			return $dados_cobranca["valor_bonificacao"];
		} else {
			return 0;
		}
		
	}
	
	function calculaJuros($dados_cobranca, $data_processamento) {
		
		if (($dias = (int) ((strtotime($data_processamento) - strtotime($this->buscaProximoDiaUtil($dados_cobranca["data_vencimento"]))) / 60 / 60 / 24)) > 0) {
			return (int) ($dados_cobranca["valor_original"] * 0.02) + (int) ($dados_cobranca["valor_original"] * 0.01 / 30 * $dias);
		} else {
			return 0;
		}
		
	}
	
	function buscaProximoDiaUtil($data) {
		
		if ($this->ehFeriado($data)) {
			$data = date("Y-m-d", strtotime("$data 1 days"));
		}
		
		$dia_semana = date("w", strtotime($data));
		
		if ($dia_semana == 6) {
			return date("Y-m-d", strtotime("$data 2 days"));
		} elseif ($dia_semana == 0) {
			return date("Y-m-d", strtotime("$data 1 days"));
		} else {
			return $data;
		}
		
	}
	
	function ehFeriado($data) {
		
		return funcoes::ehFeriado(substr($data, 6, 2), substr($data, 4, 2), substr($data, 0, 4));
		
	}
	
	#######################################
	### Retorno Para Gerente Financeiro ###
	#######################################
	
	function buscaGerentesFinanceiros() {
		
		$gerentes = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_usuarios.usu_id AS id
			FROM
				tab_usuarios
				INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
				INNER JOIN tab_grupos ON (rel_usu_gru_fazparte.gru_id = tab_grupos.gru_id)
			WHERE
				tab_usuarios.uss_id = 1 AND
				tab_grupos.gru_id = 8
			ORDER BY
				usu_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$gerentes[] = $dados["id"];
		}
		
		return $gerentes;
		
	}
	
	function registraRetorno($cliente_id, $usuario_id, $comentario) {
		
		$this->bancodedados->query("
			INSERT INTO
				tab_clientes_retornos
			SET
				cli_id = $cliente_id,
				usu_id = $usuario_id,
				clr_abertura = NOW(),
				clr_data = NOW(),
				clr_comentario = '$comentario',
				cet_id = 2,
				ces_id = 1");
		
	}
	
	###################################
	### Solicita Finalização Ajuste ###
	###################################
	
	function solicitaFinalizacaoAjuste($ajuste_id) {
		
		$this->bancodedados->query("
			UPDATE
				tab_servicos_ajustes
			SET
				sas_id = 9
			WHERE
				saj_id = $ajuste_id");
		
	}
	
	##############
	### Listar ###
	##############
	
	function listar() {
		
		if (!$_GET["f"]) {
			$_GET["f"]["data_publicacao_inicio"] = date("01/m/Y");
			$_GET["f"]["data_publicacao_termino"] = date("t/m/Y");
		}
		
		$filtro = $this->inicializaFiltro($_GET["f"]);
		$pagina = $_GET["p"] ? $_GET["p"] : 1;
		
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="3" width="100%"><tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td></tr></table>';
		$this->conteudo .= '<table border="0" cellspacing="1" cellpadding="5" width="100%"><tr><td bgcolor="#E8E8E8" align="center" class="txtBranco11">';
		if ($this->permissoes[$this->dados_modulo["nome"]]["publicar"]) $this->conteudo .= '<input type="button" class="buttonAzulEscuroBranco11" name="publicar" value="Publicar" onClick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=publicar\'">';
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
		
		$itens = array();
		
		// Geração Inicio
		$itens[] = array ("tipo" => "input", "nome" => "data_geracao_inicio", "descricao" => "Data de Geração &raquo; Início", "valor" => "", "where" => "(tab_cobrancas_retornos.coe_data_geracao >= '".substr($valores["data_geracao_inicio"], 6, 4)."-".substr($valores["data_geracao_inicio"], 3, 2)."-".substr($valores["data_geracao_inicio"], 0, 2)."')");
		
		// Geração Termino
		$itens[] = array ("tipo" => "input", "nome" => "data_geracao_termino", "descricao" => "Data de Geração &raquo; Término", "valor" => "", "where" => "(tab_cobrancas_retornos.coe_data_geracao <= '".substr($valores["data_geracao_termino"], 6, 4)."-".substr($valores["data_geracao_termino"], 3, 2)."-".substr($valores["data_geracao_termino"], 0, 2)."')");
		
		// Formatos
		$formatos = $this->buscaFormatos();
		$select_formatos = array();
		foreach ($formatos as $id => $nome) {
			$select_formatos[] = array ("id" => $id, "nome" => $nome);
		}
		$itens[] = array ("tipo" => "select", "nome" => "formato", "descricao" => "Formato", "valor" => $select_formatos, "where" => "(tab_cobrancas_retornos.cof_id = [valor])");
		
		// Publicação Inicio
		$itens[] = array ("tipo" => "input", "nome" => "data_publicacao_inicio", "descricao" => "Data de Publicação &raquo; Início", "valor" => "", "where" => "(tab_cobrancas_retornos.coe_data_publicacao >= '".substr($valores["data_publicacao_inicio"], 6, 4)."-".substr($valores["data_publicacao_inicio"], 3, 2)."-".substr($valores["data_publicacao_inicio"], 0, 2)."')");
		
		// Publicação Termino
		$itens[] = array ("tipo" => "input", "nome" => "data_publicacao_termino", "descricao" => "Data de Publicação &raquo; Término", "valor" => "", "where" => "(tab_cobrancas_retornos.coe_data_publicacao <= '".substr($valores["data_publicacao_termino"], 6, 4)."-".substr($valores["data_publicacao_termino"], 3, 2)."-".substr($valores["data_publicacao_termino"], 0, 2)."')");
		
		return controle::instanciaObjeto("filtro", $itens, $valores, "?m={$this->dados_modulo["nome"]}&a={$this->acao}");
		
	}
	
	function geraPaginacao($filtro, $total, $pagina) {
		
		return controle::instanciaObjeto("paginacao", "?m={$this->dados_modulo["nome"]}&a={$this->acao}{$filtro->parametros_url}", $total, $pagina);
		
	}
	
	function buscaItens($filtro, $pagina) {
		
		$limit_inicio = ($pagina - 1) * PAG_ITENS;
		$limit_fim = PAG_ITENS;
		
		$query = $this->bancodedados->query("
			SELECT SQL_CALC_FOUND_ROWS
				tab_cobrancas_retornos.coe_id AS id,
				tab_cobrancas_formatos.cof_nome AS formato,
				DATE_FORMAT(tab_cobrancas_retornos.coe_data_geracao, '%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(tab_cobrancas_retornos.coe_data_publicacao, '%d/%m/%Y') AS data_publicacao,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'REGISTRO') AS registros,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'REJEICAO') AS rejeicoes,
				(SELECT COUNT(*) FROM tab_cobrancas_retornos_registros WHERE tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'BAIXA') AS baixas,
				COUNT(tab_cobrancas.coa_id) AS liquidacoes,
				SUM(tab_cobrancas.coa_valor_total - tab_cobrancas.coa_valor_iof) AS valor_total
			FROM
				tab_cobrancas_retornos
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_retornos.cof_id = tab_cobrancas_formatos.cof_id)
				LEFT OUTER JOIN tab_cobrancas_retornos_registros ON (tab_cobrancas_retornos.coe_id = tab_cobrancas_retornos_registros.coe_id AND tab_cobrancas_retornos_registros.cog_tipo = 'LIQUIDACAO')
				LEFT OUTER JOIN tab_cobrancas ON (tab_cobrancas_retornos_registros.coa_id = tab_cobrancas.coa_id AND tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE cot_recebido = 1 AND cot_manual = 0))
			WHERE
				$filtro->parametros_sql
			GROUP BY
				id
			ORDER BY
				tab_cobrancas_retornos.coe_data_publicacao DESC,
				tab_cobrancas_retornos.coe_id DESC
			LIMIT
				$limit_inicio,
				$limit_fim");
		
		$linhas = $this->bancodedados->linhasTotal($query);
		
		$itens = array();
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($this->permissoes[$this->dados_modulo["nome"]]["ver"]) {
				$dados["link"] = "?m={$this->dados_modulo["nome"]}&a=ver&i={$dados["id"]}";
			}
			$dados["registros"] = number_format($dados["registros"], null, null, ".");
			$dados["rejeicoes"] = number_format($dados["rejeicoes"], null, null, ".");
			$dados["liquidacoes"] = number_format($dados["liquidacoes"], null, null, ".");
			$dados["valor_total"] = $this->formataDinheiro($dados["valor_total"]);
			$dados["contador"] = number_format($limit_inicio + count($itens) + 1, null, null, ".");
			
			$itens[] = $dados;
			
		}
		
		return array("linhas" => $linhas, "itens" => $itens);
		
	}
	
	function geraListagem($itens) {
		
		$cabecalho = array ("contador" => "#", "formato" => "Formato", "data_geracao" => "Geração", "data_publicacao" => "Publicação", "registros" => "Registros", "baixas" => "Baixas", "rejeicoes" => "Rejeições", "liquidacoes" => "Liquid.", "valor_total" => "Valor Liq.");
		
		return controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, array("registros" => "formataNumero", "baixas" => "formataNumero", "rejeicoes" => "formataNumero", "liquidacoes" => "formataNumero", "valor_total" => "formataDinheiro"), array(20, 0, 60, 60, 60, 60, 60, 60, 80), null, array("right", "left", "center", "center", "right", "right", "right", "right", "right"));
		
	}
	
}

?>