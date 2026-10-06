<?

class cron_bloqueio_inadimplencia {

	var $bancodedados;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
	}

	############################
	### Ajusta Inadimplentes ###
	############################

	function bloqueiaInadimplentes() {
		
		if (in_array(date("w"), array(1, 2, 3, 4, 5)) && !in_array(date("d/m"), array("24/12", "25/12", "31/12", "01/01", "02/01"))) {
			$inadimplentes = $this->buscaInadimplentes();
			
			foreach ($inadimplentes as $cliente_id) {
				$ajuste_id = $this->buscaBloqueioAguardandoFinalizacao($cliente_id);
				if ($ajuste_id) {
					$this->reativaBloqueio($ajuste_id);
				} else {
					$this->geraBloqueio($cliente_id);
				}
			}
			
		}
	
	}

	########################
	### Funções de Busca ###
	########################

	function buscaInadimplentes() {
		
		$clientes = array();
		
		$query = "
			SELECT
				tab_clientes.cli_id AS cliente_id
			FROM
				tab_clientes
			WHERE
				tab_clientes.cls_id = 2 AND
				(
					SELECT
						COUNT(coa_id)
					FROM
						tab_cobrancas
					WHERE
						tab_cobrancas.cli_id = tab_clientes.cli_id AND
						tab_cobrancas.cot_id = 1 AND
						tab_cobrancas.coa_pai IS NULL AND
						tab_cobrancas.coa_data_bloqueio <= CURDATE()
				) > 0 AND
				(
					SELECT
						COUNT(*) 
					FROM
						tab_servicos_ajustes 
					WHERE 
						tab_servicos_ajustes.cli_id = tab_clientes.cli_id AND 
						tab_servicos_ajustes.sac_id = 1 AND 
						tab_servicos_ajustes.sas_id = 5
				) = 0";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes[] = $dados["cliente_id"];
		}
		
		return $clientes;
	
	}

	function buscaBloqueioAguardandoFinalizacao($cliente_id) {
		
		$query = "
			SELECT
				saj_id 
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sac_id = 1 AND
				sas_id = 9";
			
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
	
	}

	function reativaBloqueio($ajuste_id) {
		
		$query = "UPDATE tab_servicos_ajustes SET sas_id = 5 WHERE saj_id = $ajuste_id";
		$this->bancodedados->query($query);
		
		$this->registraComentarioAjuste($ajuste_id, "<b>Ação</b>: Reativado");
	
	}

	function buscaClientesAssinaturas($cliente_id) {
		
		$lista = array();
		
		$query = "
			SELECT
				ass_id AS assinatura_id
			FROM
				tab_assinaturas
			WHERE
				cli_id = $cliente_id AND
				ass_pai IS NULL AND
				ast_id IN (2, 5)";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados["assinatura_id"];
		}
		
		return $lista;
	
	}

	function assinaturaComBloqueioAtivo($assinatura_id) {
		
		$query = "
			SELECT
				COUNT(rel_saj_ass_contem.saj_id)
			FROM
				rel_saj_ass_contem
				INNER JOIN tab_servicos_ajustes ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id)
			WHERE
				tab_servicos_ajustes.sac_id = 1 AND
				tab_servicos_ajustes.sas_id IN (5, 9) AND
				rel_saj_ass_contem.ass_id = $assinatura_id";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query);
	
	}

	function geraBloqueio($cliente_id) {
	
		$assinaturas = $this->buscaClientesAssinaturas($cliente_id);
		$ajuste = null;

		foreach ($assinaturas as $assinatura) {
			
			if (!$this->assinaturaComBloqueioAtivo($assinatura)) {
				
				if (!$ajuste) {
					$ajuste = $this->adicionaBloqueio($cliente_id);
				}
				
				$this->adicionaBloqueioAssinatura($ajuste, $assinatura);
				
				$pontos_iptv = $this->buscaPontosIptv($assinatura);
				foreach ($pontos_iptv as $ponto_iptv) {
					$this->adicionaBloqueioPontoIptv($ajuste, $ponto_iptv);
				}
				
			}
			
		}
	
	}

	function adicionaBloqueio($cliente_id) {
		
		$status_id = 5;
		$inicio = "NOW()";
		
		$query = "
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				sac_id = 1 AND
				cli_id = $cliente_id AND
				sas_id IN ($status_id, 5, 9)";
		
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			
			return $dados["id"];
		
		} else {
		
			// Insere Ajuste
			$query = "
				INSERT INTO
					tab_servicos_ajustes (cli_id, sac_id, saj_inicio, sas_id)
				VALUES
					($cliente_id, 1, $inicio, $status_id)";
			$this->bancodedados->query($query);
			
			$id = $this->bancodedados->ultimoIdInserido();
			
			// Insere Regra
			$dados_categoria = $this->buscaDadosCategoria(1);
			$this->registraRegras($id, $dados_categoria["regras"]);
			
			// Registra Comentário
			$this->registraComentarioAjuste($id, "<b>Ação</b>: Adicionado");
			
			return $id;
		
		}
	
	}

	function registraRegras($ajuste_id, $regras) {
		
		// Remove Regras Antigas
		$query = "DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = $ajuste_id";
		$this->bancodedados->query($query);
		
		// Insere Regras Novas
		foreach ($regras as $regra) {
			
			$regra["servico"] = $regra["servico"] ? $regra["servico"] : "NULL";
			$regra["ajuste_unidade"] = $regra["ajuste_unidade"] ? $regra["ajuste_unidade"] : 0;
			$regra["ajuste"] = $regra["ajuste_tipo"] == 1 ? $regra["ajuste_valor"] / 100 : $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
			$regra["dia_semana"] = $regra["dia_semana"] ? $regra["dia_semana"] : "NULL";
			$regra["hora_inicio"] = $regra["hora_inicio"] && $regra["hora_inicio"] != '00:00' ? "'{$regra["hora_inicio"]}'" : "NULL";
			$regra["hora_termino"] = $regra["hora_termino"] && $regra["hora_termino"] != '24:00' ? "'{$regra["hora_termino"]}'" : "NULL";
			
			$query = "
				INSERT INTO
					tab_servicos_ajustes_regras (saj_id, sat_id, sar_ajuste, sar_dia_semana, sar_hora_inicio, sar_hora_termino)
				VALUES
					($ajuste_id, {$regra["servico"]}, {$regra["ajuste"]}, {$regra["dia_semana"]}, {$regra["hora_inicio"]}, {$regra["hora_termino"]})";
			$this->bancodedados->query($query);
			
		}
	
	}

	function buscaDadosCategoria($categoria_id) {
		
		$query = "
			SELECT
				sac_regras AS regras,
				sac_editavel AS editavel
			FROM
				tab_servicos_ajustes_categorias
			WHERE
				sac_id = $categoria_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["regras"] = unserialize($dados["regras"]);
			return $dados;
		}
	
	}

	function buscaPontosIptv($assinatura_id) {
				
		$lista = array();

		$query = "
			SELECT
				cip_id AS id
			FROM
				tab_clientes_iptv
			WHERE
				tab_clientes_iptv.cor_id IN (SELECT cor_id FROM rel_ass_cor_estacontido WHERE ass_id = $assinatura_id) AND
				tab_clientes_iptv.cis_id IN (1, 3)";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados["id"];
		}
		
		return $lista;
	
	}

	function adicionaBloqueioAssinatura($ajuste_id, $assinatura_id) {
		
		$query = "
			INSERT INTO
				rel_saj_ass_contem (saj_id, ass_id)
			VALUES
				($ajuste_id, $assinatura_id)";
		$this->bancodedados->query($query);
	
	}

	function adicionaBloqueioPontoIptv($ajuste_id, $iptv_id) {
		
		$query = "
			INSERT INTO
				rel_saj_cip_contem (saj_id, cip_id)
			VALUES
				($ajuste_id, $iptv_id)";
		$this->bancodedados->query($query);
	
	}

	function registraComentarioAjuste($ajuste_id, $conteudo, $responsavel_id = null) {
		
		$ajuste_id = preg_replace("/[^0-9]/", "", $ajuste_id);
		$responsavel_id = $responsavel_id ? $responsavel_id : "NULL";
		$conteudo = addslashes(trim($conteudo));
		
		if ($ajuste_id) {
			$query = "INSERT INTO tab_servicos_ajustes_comentarios (saj_id, sam_datahora, sam_conteudo, usu_id) VALUES ($ajuste_id, NOW(), '$conteudo', $responsavel_id)";
			$this->bancodedados->query($query);
		}
		
	}

}

?>
