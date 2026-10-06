<?

print "<PRE>";

/*

set_time_limit(0);

date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$cron = controle::instanciaObjeto("cron.sincroniza_telecom", $bancodedados);
$cron->sincronizar();
*/

class cron_sincroniza_telecom {

	var $bancodedados;
	
	var $bancodedados_telecom;
	
	var $contratos;
	
	var $servicos_ajustes;

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		$this->bancodedados_telecom = new bancodedados(true, "10.0.100.250", "intranet", "RVexdRsNjfaaLepeYmEYEFam", "intranet");
		$this->contratos = controle::instanciaObjeto("contratos", $this->bancodedados, null, false);
		$this->servicos_ajustes = controle::instanciaObjeto("servicos_ajustes", $this->bancodedados, null, false);

	}

	###################
	### Sincronizar ###
	###################

	function sincronizar() {

		$clientes_ativos = $this->buscaClientesAtivos();

		foreach ($clientes_ativos as $cliente) {
			
			$telecom = $this->buscaClienteTelecom($cliente["cpf_cnpj"], $cliente["cep"], $cliente["numero"]);
			
			if (!$telecom || $telecom["status_id"] == 4) {
				
				// Cliente Telecom Não Encontrato

				print "NÃO ENCONTRATO {$cliente["id"]}\t{$cliente["cpf_cnpj"]}\t{$cliente["nome"]}\n";
				
			} elseif ($telecom["status_id"] == 3) {
				
				// Cliente Telecom Cancelado
				
				if ($cliente["mensalidade"] <= 0) {
				
					print "CANCELADO {$cliente["id"]}\t{$cliente["cpf_cnpj"]}\t{$cliente["mensalidade"]}\t{$telecom["status_id"]}\t{$telecom["cancelamento"]}\n";
					
					$this->cancelaCliente($cliente["id"], $telecom["cancelamento"]);
					
				}
				
			} elseif ($telecom["status_id"] == 2) {
				
				// Cliente Telecom Ativo
				
				if ($telecom["bloqueado"]) {
					
					if ($cliente["mensalidade"] <= 0) {
					
						if (!$cliente["bloqueado"]) {

							print "BLOQUEADO {$cliente["id"]}\t{$cliente["cpf_cnpj"]}\t{$telecom["status_id"]}\n";
							
							$this->bloqueiaCliente($cliente["id"]);
							
						}

						if ($telecom["suspensao_temporaria"] && !$cliente["suspensao_temporaria"]) {
							
							print "SUSPENSÃO TEMPORÁRIA {$cliente["id"]}\t{$cliente["cpf_cnpj"]}\t{$telecom["status_id"]}\n";

							$this->aplicaSuspensaoTemporaria($cliente["id"]);

						}
						
					}

				} else {
					
					if ($cliente["bloqueado"] && !$cliente["inadimplente"]) {
					
						print "DESBLOQUEADO {$cliente["id"]}\t{$cliente["cpf_cnpj"]}\t{$cliente["inadimplente"]}\t{$telecom["status_id"]}\n";
						
						$this->desbloqueiaCliente($cliente["id"]);
						
					}
					
				}
				
			}
			
		}

	}
	
	function cancelaCliente($cliente_id, $data_cancelamento) {
		
		$contratos = $this->buscaContratosAtivos($cliente_id);
		
		foreach ($contratos as $contrato_id) {
			$this->contratos->acaoCancelarContrato($this->contratos->buscaDadosContrato($contrato_id), $data_cancelamento, "Cliente cancelou contrato de Internet");
		}
		
	}
	
	function bloqueiaCliente($cliente_id) {
	
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

	function desbloqueiaCliente($cliente_id) {
		
		$ajustes = $this->buscaAjustesInadimplencia($cliente_id);

		foreach ($ajustes as $ajuste_id) {
			$this->servicos_ajustes->removeAjuste($ajuste_id);
			$this->registraComentarioAjuste($ajuste_id, "<b>Ação</b>: Finalizado");
		}

	}
	
	function aplicaSuspensaoTemporaria($cliente_id) {
		
		$ajustes = $this->buscaAjustesInadimplencia($cliente_id);

		foreach ($ajustes as $ajuste_id) {
			$this->servicos_ajustes->buscaDadosItem($ajuste_id);
			$this->servicos_ajustes->insereSuspensaoTemporaria();
		}

	}

	function buscaAjustesInadimplencia($cliente_id) {

		$ajustes = array();

		$query = "
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				cli_id = $cliente_id AND
				sas_id IN (5, 9) AND
				sac_id = 1";
		$query = $this->bancodedados->query($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}

		return $ajustes;

	}

	########################
	### Funções de Busca ###
	########################

	function buscaClientesAtivos() {
		
		$clientes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_ceps.cep_exportacao AS cep,
				tab_clientes.cli_numero AS numero,
				tab_clientes.cli_nome AS nome,
				IF((
					SELECT
						COUNT(*)
					FROM 
						tab_servicos_ajustes 
					WHERE 
						tab_servicos_ajustes.cli_id = tab_clientes.cli_id AND
						tab_servicos_ajustes.sac_id IN (1, 3, 6) AND
						tab_servicos_ajustes.sas_id = 5
				) > 0, 1, 0) AS bloqueado,
				IF((
					SELECT
						COUNT(*)
					FROM 
						tab_servicos_ajustes 
					WHERE 
						tab_servicos_ajustes.cli_id = tab_clientes.cli_id AND
						tab_servicos_ajustes.sac_id IN (2) AND
						tab_servicos_ajustes.sas_id = 5
				) > 0, 1, 0) AS suspensao_temporaria,
				IF((
					SELECT
						COUNT(coa_id)
					FROM
						tab_cobrancas
					WHERE
						tab_cobrancas.cli_id = tab_clientes.cli_id AND
						tab_cobrancas.cot_id = 1 AND
						tab_cobrancas.coa_pai IS NULL AND
						tab_cobrancas.coa_data_bloqueio <= CURDATE()
				) > 0, 1, 0) AS inadimplente,
				(
					SELECT
						SUM(tab_assinaturas.ass_mensalidade - IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id AND tab_descontos_concessoes.dcs_id = 5), 0) - IFNULL((SELECT SUM(IF(tab_ajustes_financeiros.ajf_valor_tipo = 1, tab_ajustes_financeiros.ajf_valor, tab_ajustes_financeiros.ajf_valor * tab_assinaturas.ass_mensalidade)) FROM tab_ajustes_financeiros WHERE tab_ajustes_financeiros.ass_id = tab_assinaturas.ass_id AND tab_ajustes_financeiros.ajf_competencia_termino IS NULL AND tab_ajustes_financeiros.ajf_valor_tipo IN (1, 2) AND tab_ajustes_financeiros.afs_id = 5), 0))
					FROM 
						tab_assinaturas 
					WHERE
						tab_assinaturas.cli_id = tab_clientes.cli_id AND
						tab_assinaturas.ast_id = 2
				) AS mensalidade
			FROM
				tab_clientes
				LEFT JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				LEFT JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
			WHERE
				tab_clientes.clo_id = 1 AND
				tab_clientes.cls_id = 2");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$clientes[] = $dados;
		}
		
		return $clientes;
		
	}
	
	function buscaClienteTelecom($cpf_cnpj, $cep, $numero) {
		
		$query = $this->bancodedados_telecom->query("
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cls_id AS status_id,
				tab_clientes.cli_nome AS nome,
				IF((
					SELECT
						COUNT(*)
					FROM 
						tab_servicos_ajustes 
					WHERE 
						tab_servicos_ajustes.cli_id = tab_clientes.cli_id AND
						tab_servicos_ajustes.sac_id IN (1, 3, 6) AND
						tab_servicos_ajustes.sas_id = 5
				) > 0, 1, 0) AS bloqueado,
				IF((
					SELECT
						COUNT(*)
					FROM 
						tab_servicos_ajustes 
					WHERE 
						tab_servicos_ajustes.cli_id = tab_clientes.cli_id AND
						tab_servicos_ajustes.sac_id IN (2) AND
						tab_servicos_ajustes.sas_id = 5
				) > 0, 1, 0) AS suspensao_temporaria,
				DATE_FORMAT(IF(cls_id = 3, (
						SELECT
							MAX(cor_data_cancelamento)
						FROM
							tab_contratos
						WHERE
							tab_contratos.cli_id = tab_clientes.cli_id AND
							tab_contratos.crt_id = 11 AND
							tab_contratos.crs_id = 22
					), NULL), '%d/%m/%Y') AS cancelamento
			FROM
				tab_clientes
				LEFT JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				LEFT JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
			WHERE
				tab_clientes.cli_cpf_cnpj = '{$cpf_cnpj}' AND
				tab_ceps.cep_exportacao = {$cep} AND ".($numero ? "
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) = {$numero}" : "
				(IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) IS NULL OR
				 IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) = 0)")."
			ORDER BY
				IF(cls_id = 2, 0, cls_id)
			LIMIT 1");
		
		return $this->bancodedados_telecom->resultadoArray($query);
		
	}
	
	function buscaContratosAtivos($cliente_id) {
		
		$contratos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS id
			FROM
				tab_contratos
			WHERE
				tab_contratos.cli_id = {$cliente_id} AND
				tab_contratos.crs_id IN (19, 20)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}
		
		return $contratos;
		
	}

}

?>
