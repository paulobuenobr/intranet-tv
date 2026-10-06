<PRE>
<?

if ($_GET["cliente"]) {

	ignore_user_abort(1);
	set_time_limit(0);

	require_once("../global.inc.php");

	$importa_cliente = new importa_cliente($bancodedados);
	$importa_cliente->importaCliente($_GET["cliente"]);

}


class importa_cliente {

	private $bancodedados;
	
	##################
	### Construtor ###
	##################

	function importa_cliente($bancodedados) {
		
		$this->bancodedados = $bancodedados;
		
		$this->bancodedados->query("SET FOREIGN_KEY_CHECKS=0");

	}

	function importaCliente($cli_id) {
		
		print "importaCliente($cli_id)\n";
						
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes
			WHERE
				tvintranet_demo.tab_clientes.cli_id = {$cli_id}");

		if (!$this->bancodedados->resultado($query)) {
				
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes
				SELECT
					*
				FROM
					tvintranet.tab_clientes
				WHERE
					tvintranet.tab_clientes.cli_id = {$cli_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes.*
				FROM
					tvintranet.tab_clientes
				WHERE
					tvintranet.tab_clientes.cli_id = {$cli_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($dados["cli_pai"]) {
					$this->importaCliente($dados["cli_pai"]);
				}	
				if ($dados["cof_id"]) {
					$this->importaCobrancaFormato($dados["cof_id"]);
				}
				if ($dados["cob_id"]) {
					$this->importaBloco($dados["cob_id"]);
				}
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
				if ($dados["est_id"]) {
					$this->importaEstado($dados["est_id"]);
				}
				if ($dados["cid_id"]) {
					$this->importaCidade($dados["cid_id"]);
				}
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
				if ($dados["log_id"]) {
					$this->importaLogradouro($dados["log_id"]);
				}
				if ($dados["cep_id"]) {
					$this->importaCep($dados["cep_id"]);
				}
				if ($dados["clo_id"]) {
					$this->importaOrigem($dados["clo_id"]);
				}
				if ($dados["usu_id"]) {
					$this->importaUsuario($dados["usu_id"]);
				}
			}
					
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_contatos_telefones
				SELECT
					*
				FROM
					tvintranet.tab_clientes_contatos_telefones
				WHERE
					tvintranet.tab_clientes_contatos_telefones.cli_id = {$cli_id}");
					
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_contatos_emails
				SELECT
					*
				FROM
					tvintranet.tab_clientes_contatos_emails
				WHERE
					tvintranet.tab_clientes_contatos_emails.cli_id = {$cli_id}");
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_negativacoes
				SELECT
					*
				FROM
					tvintranet.tab_clientes_negativacoes
				WHERE
					tvintranet.tab_clientes_negativacoes.cli_id = {$cli_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_ajustes_financeiros.ajf_id
				FROM
					tvintranet.tab_ajustes_financeiros
				WHERE
					tvintranet.tab_ajustes_financeiros.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaAjusteFinanceiro($dados["ajf_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas.coa_id
				FROM
					tvintranet.tab_cobrancas
				WHERE
					tvintranet.tab_cobrancas.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCobranca($dados["coa_id"]);
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_consultas_credito.ccc_id
				FROM
					tvintranet.tab_clientes_consultas_credito
				WHERE
					tvintranet.tab_clientes_consultas_credito.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaConsultaCredito($dados["ccc_id"]);
			}
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_assinaturas.ass_id
				FROM
					tvintranet.tab_assinaturas
				WHERE
					tvintranet.tab_assinaturas.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaAssinatura($dados["ass_id"]);
			}
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_responsabilidades.cln_id
				FROM
					tvintranet.tab_clientes_responsabilidades
				WHERE
					tvintranet.tab_clientes_responsabilidades.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteResponsabilidade($dados["cln_id"]);
			}
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_retornos.clr_id
				FROM
					tvintranet.tab_clientes_retornos
				WHERE
					tvintranet.tab_clientes_retornos.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteRetorno($dados["clr_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_visitas.vis_id
				FROM
					tvintranet.tab_visitas
				WHERE
					tvintranet.tab_visitas.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaVisita($dados["vis_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_contratos.cor_id
				FROM
					tvintranet.tab_contratos
				WHERE
					tvintranet.tab_contratos.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaContrato($dados["cor_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servicos_ajustes.saj_id
				FROM
					tvintranet.tab_servicos_ajustes
				WHERE
					tvintranet.tab_servicos_ajustes.cli_id = {$cli_id} AND
					tvintranet.tab_servicos_ajustes.saj_pai IS NULL");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaServicoAjuste($dados["saj_id"]);
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_comodatos.clc_id
				FROM
					tvintranet.tab_clientes_comodatos
				WHERE
					tvintranet.tab_clientes_comodatos.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteComodato($dados["clc_id"]);
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cancelamentos.can_id
				FROM
					tvintranet.tab_cancelamentos
				WHERE
					tvintranet.tab_cancelamentos.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCancelamento($dados["can_id"]);
			}
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_chamados.cha_id
				FROM
					tvintranet.tab_chamados
				WHERE
					tvintranet.tab_chamados.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaChamado($dados["cha_id"]);
			}
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_pesquisas_aplicacoes.pea_id
				FROM
					tvintranet.tab_pesquisas_aplicacoes
				WHERE
					tvintranet.tab_pesquisas_aplicacoes.cli_id = {$cli_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaPesquisaAplicacao($dados["pea_id"]);
			}
			
		}

	}
	
	function importaConsultaCredito($ccc_id) {
		
		print "importaConsultaCredito($ccc_id)\n";

		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_consultas_credito
			WHERE
				tvintranet_demo.tab_clientes_consultas_credito.ccc_id = {$ccc_id}");

		if (!$this->bancodedados->resultado($query)) {
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_consultas_credito
				SELECT
					*
				FROM
					tvintranet.tab_clientes_consultas_credito
				WHERE
					tvintranet.tab_clientes_consultas_credito.ccc_id = {$ccc_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_consultas_credito.usu_id
				FROM
					tvintranet.tab_clientes_consultas_credito
				WHERE
					tvintranet.tab_clientes_consultas_credito.ccc_id = {$ccc_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaUsuario($dados["usu_id"]);
			}

		}
	
	}
	
	function importaCobrancaFormato($cof_id) {
		
		print "importaCobrancaFormato($cof_id)\n";

		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_cobrancas_formatos
			SELECT
				*
			FROM
				tvintranet.tab_cobrancas_formatos
			WHERE
				tvintranet.tab_cobrancas_formatos.cof_id = {$cof_id}");
		
	}
	
	function importaBloco($cob_id) {
		
		print "importaBloco($cob_id)\n";
	
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_condominios_blocos
			WHERE
				tvintranet_demo.tab_condominios_blocos.cob_id = {$cob_id}");

		if (!$this->bancodedados->resultado($query)) {

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_condominios_blocos
				SELECT
					*
				FROM
					tvintranet.tab_condominios_blocos
				WHERE
					tvintranet.tab_condominios_blocos.cob_id = {$cob_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_condominios_blocos.*
				FROM
					tvintranet.tab_condominios_blocos
				WHERE
					tvintranet.tab_condominios_blocos.cob_id = {$cob_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {				
				if ($dados["est_id"]) {
					$this->importaEstado($dados["est_id"]);
				}
				if ($dados["cid_id"]) {
					$this->importaCidade($dados["cid_id"]);
				}
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
				if ($dados["log_id"]) {
					$this->importaLogradouro($dados["log_id"]);
				}
				if ($dados["cep_id"]) {
					$this->importaCep($dados["cep_id"]);
				}
				if ($dados["sei_id"]) {
					$this->importaServidorInterface($dados["sei_id"]);
				}
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
			}

		}

	}
	
	function importaCondominio($con_id) {
		
		print "importaCondominio($con_id)\n";
			
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_condominios
			WHERE
				tvintranet_demo.tab_condominios.con_id = {$con_id}");

		if (!$this->bancodedados->resultado($query)) {
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_condominios
				SELECT
					*
				FROM
					tvintranet.tab_condominios
				WHERE
					tvintranet.tab_condominios.con_id = {$con_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_condominios.*
				FROM
					tvintranet.tab_condominios
				WHERE
					tvintranet.tab_condominios.con_id = {$con_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {				
				if ($dados["con_pai"]) {
					$this->importaCondominio($dados["con_pai"]);
				}
				if ($dados["est_id"]) {
					$this->importaEstado($dados["est_id"]);
				}
				if ($dados["cid_id"]) {
					$this->importaCidade($dados["cid_id"]);
				}
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
				if ($dados["log_id"]) {
					$this->importaLogradouro($dados["log_id"]);
				}
				if ($dados["cep_id"]) {
					$this->importaCep($dados["cep_id"]);
				}
				if ($dados["usu_id"]) {
					$this->importaUsuario($dados["usu_id"]);
				}
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_ajustes_financeiros.ajf_id
				FROM
					tvintranet.tab_ajustes_financeiros
				WHERE
					tvintranet.tab_ajustes_financeiros.con_id = {$con_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaAjusteFinanceiro($dados["ajf_id"]);
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_condominios_responsabilidades.cod_id
				FROM
					tvintranet.tab_condominios_responsabilidades
				WHERE
					tvintranet.tab_condominios_responsabilidades.con_id = {$con_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCondominioResponsabilidade($dados["cod_id"]);
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_descontos_concessoes.dec_id
				FROM
					tvintranet.tab_descontos_concessoes
				WHERE
					tvintranet.tab_descontos_concessoes.con_id = {$con_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaDescontoConcessao($dados["dec_id"]);
			}
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_con_pla_tempermissao.pla_id
				FROM
					tvintranet.rel_con_pla_tempermissao
				WHERE
					tvintranet.rel_con_pla_tempermissao.con_id = {$con_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaPlano($dados["pla_id"]);
			}
				
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_con_pla_tempermissao
				SELECT
					*
				FROM
					tvintranet.rel_con_pla_tempermissao
				WHERE
					tvintranet.rel_con_pla_tempermissao.con_id = {$con_id}");
			
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_chamados.cha_id
				FROM
					tvintranet.tab_chamados
				WHERE
					tvintranet.tab_chamados.con_id = {$con_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaChamado($dados["cha_id"]);
			}

		}

	}
	
	function importaCondominioResponsabilidade($cod_id) {
		
		print "importaCondominioResponsabilidade($cod_id)\n";
											
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_condominios_responsabilidades
			SELECT
				*
			FROM
				tvintranet.tab_condominios_responsabilidades
			WHERE
				tvintranet.tab_condominios_responsabilidades.cod_id = {$cod_id}");
	
		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_condominios_responsabilidades.*
			FROM
				tvintranet.tab_condominios_responsabilidades
			WHERE
				tvintranet.tab_condominios_responsabilidades.cod_id = {$cod_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["con_id"]) {
				$this->importaCondominio($dados["con_id"]);
			}
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
					
		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_condominios_responsabilidades_comentarios.crm_id
			FROM
				tvintranet.tab_condominios_responsabilidades_comentarios
			WHERE
				tvintranet.tab_condominios_responsabilidades_comentarios.cod_id = {$cod_id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->importaCondominioResponsabilidadeComentario($dados["crm_id"]);
		}

	}
	
	function importaCondominioResponsabilidadeComentario($crm_id) {
		
		print "importaCondominioResponsabilidadeComentario($crm_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_condominios_responsabilidades_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_condominios_responsabilidades_comentarios
			WHERE
				tvintranet.tab_condominios_responsabilidades_comentarios.crm_id = {$crm_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_condominios_responsabilidades_comentarios.*
			FROM
				tvintranet.tab_condominios_responsabilidades_comentarios
			WHERE
				tvintranet.tab_condominios_responsabilidades_comentarios.crm_id = {$crm_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}
	
	function importaCep($cep_id) {
		
		print "importaCep($cep_id)\n";
									
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_ceps
			WHERE
				tvintranet_demo.tab_ceps.cep_id = {$cep_id}");

		if (!$this->bancodedados->resultado($query)) {
											
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_ceps
				SELECT
					*
				FROM
					tvintranet.tab_ceps
				WHERE
					tvintranet.tab_ceps.cep_id = {$cep_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_ceps.*
				FROM
					tvintranet.tab_ceps
				WHERE
					tvintranet.tab_ceps.cep_id = {$cep_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($dados["est_id"]) {
					$this->importaEstado($dados["est_id"]);
				}
				if ($dados["cid_id"]) {
					$this->importaCidade($dados["cid_id"]);
				}
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
				if ($dados["log_id"]) {
					$this->importaLogradouro($dados["log_id"]);
				}
			}

		}

	}
	
	function importaLogradouro($log_id) {
		
		print "importaLogradouro($log_id)\n";
											
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_logradouros
			WHERE
				tvintranet_demo.tab_logradouros.log_id = {$log_id}");

		if (!$this->bancodedados->resultado($query)) {
											
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_logradouros
				SELECT
					*
				FROM
					tvintranet.tab_logradouros
				WHERE
					tvintranet.tab_logradouros.log_id = {$log_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_logradouros.*
				FROM
					tvintranet.tab_logradouros
				WHERE
					tvintranet.tab_logradouros.log_id = {$log_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
			}

		}
		
	}
	
	function importaBairro($bai_id) {

		print "importaBairro($bai_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_bairros
			WHERE
				tvintranet_demo.tab_bairros.bai_id = {$bai_id}");

		if (!$this->bancodedados->resultado($query)) {
												
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_bairros
				SELECT
					*
				FROM
					tvintranet.tab_bairros
				WHERE
					tvintranet.tab_bairros.bai_id = {$bai_id}");
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_bairros.*
				FROM
					tvintranet.tab_bairros
				WHERE
					tvintranet.tab_bairros.bai_id = {$bai_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($dados["cid_id"]) {
					$this->importaCidade($dados["cid_id"]);
				}
			}

		}
		
	}
	
	function importaCidade($cid_id) {

		print "importaCidade($cid_id)\n";
																		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_cidades
			WHERE
				tvintranet_demo.tab_cidades.cid_id = {$cid_id}");

		if (!$this->bancodedados->resultado($query)) {
										
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cidades
				SELECT
					*
				FROM
					tvintranet.tab_cidades
				WHERE
					tvintranet.tab_cidades.cid_id = {$cid_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cidades.*
				FROM
					tvintranet.tab_cidades
				WHERE
					tvintranet.tab_cidades.cid_id = {$cid_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				if ($dados["est_id"]) {
					$this->importaEstado($dados["est_id"]);
				}
				if ($dados["clo_id"]) {
					$this->importaOrigem($dados["clo_id"]);
				}
			}

		}
		
	}
	
	function importaEstado($est_id) {

		print "importaEstado($est_id)\n";
		
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_estados
			SELECT
				*
			FROM
				tvintranet.tab_estados
			WHERE
				tvintranet.tab_estados.est_id = {$est_id}");
		
	}
	
	function importaUsuario($usu_id) {
																						
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_usuarios
			WHERE
				tvintranet_demo.tab_usuarios.usu_id = {$usu_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_usuarios
				SELECT
					*
				FROM
					tvintranet.tab_usuarios
				WHERE
					tvintranet.tab_usuarios.usu_id = {$usu_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_usuarios.*
				FROM
					tvintranet.tab_usuarios
				WHERE
					tvintranet.tab_usuarios.usu_id = {$usu_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["est_id"]) {
					$this->importaEstado($dados["est_id"]);
				}
				if ($dados["cid_id"]) {
					$this->importaCidade($dados["cid_id"]);
				}
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
				if ($dados["log_id"]) {
					$this->importaLogradouro($dados["log_id"]);
				}
				if ($dados["cep_id"]) {
					$this->importaCep($dados["cep_id"]);
				}
				if ($dados["clo_id"]) {
					$this->importaOrigem($dados["clo_id"]);
				}
			}
				
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_usu_gru_fazparte
				SELECT
					*
				FROM
					tvintranet.rel_usu_gru_fazparte
				WHERE
					tvintranet.rel_usu_gru_fazparte.usu_id = {$usu_id}");
				
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_usu_orf_ors_exerce
				SELECT
					*
				FROM
					tvintranet.rel_usu_orf_ors_exerce
				WHERE
					tvintranet.rel_usu_orf_ors_exerce.usu_id = {$usu_id}");

		}
		
	}
	
	function importaServidorInterface($sei_id) {

		print "importaServidorInterface($sei_id)\n";
																								
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_servidores_interfaces
			WHERE
				tvintranet_demo.tab_servidores_interfaces.sei_id = {$sei_id}");

		if (!$this->bancodedados->resultado($query)) {

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servidores_interfaces
				SELECT
					*
				FROM
					tvintranet.tab_servidores_interfaces
				WHERE
					tvintranet.tab_servidores_interfaces.sei_id = {$sei_id}");
	
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servidores_interfaces.*
				FROM
					tvintranet.tab_servidores_interfaces
				WHERE
					tvintranet.tab_servidores_interfaces.sei_id = {$sei_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["sei_pai"]) {
					$this->importaServidorInterface($dados["sei_pai"]);
				}
				if ($dados["ser_id"]) {
					$this->importaServidor($dados["ser_id"]);
				}
				if ($dados["bai_id"]) {
					$this->importaBairro($dados["bai_id"]);
				}
				if ($dados["log_id"]) {
					$this->importaLogradouro($dados["log_id"]);
				}
				if ($dados["cep_id"]) {
					$this->importaCep($dados["cep_id"]);
				}
			}
						
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servidores_servicos
				SELECT
					*
				FROM
					tvintranet.tab_servidores_servicos
				WHERE
					tvintranet.tab_servidores_servicos.sei_id = {$sei_id}");
						
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servidores_ssids
				SELECT
					*
				FROM
					tvintranet.tab_servidores_ssids
				WHERE
					tvintranet.tab_servidores_ssids.sei_id = {$sei_id}");

		}

	}
	
	function importaServidorSSID($ssi_id) {

		print "importaServidorSSID($ssi_id)\n";
																														
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_servidores_ssids
			WHERE
				tvintranet_demo.tab_servidores_ssids.ssi_id = {$ssi_id}");

		if (!$this->bancodedados->resultado($query)) {

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servidores_ssids
				SELECT
					*
				FROM
					tvintranet.tab_servidores_ssids
				WHERE
					tvintranet.tab_servidores_ssids.ssi_id = {$ssi_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servidores_ssids.*
				FROM
					tvintranet.tab_servidores_ssids
				WHERE
					tvintranet.tab_servidores_ssids.ssi_id = {$ssi_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["sei_id"]) {
					$this->importaServidorInterface($dados["sei_id"]);
				}
			}

		}

	}
	
	function importaServidor($ser_id) {

		print "importaServidor($ser_id)\n";
																												
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_servidores
			WHERE
				tvintranet_demo.tab_servidores.ser_id = {$ser_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servidores
				SELECT
					*
				FROM
					tvintranet.tab_servidores
				WHERE
					tvintranet.tab_servidores.ser_id = {$ser_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servidores.*
				FROM
					tvintranet.tab_servidores
				WHERE
					tvintranet.tab_servidores.ser_id = {$ser_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["sei_id"]) {
					$this->importaServidorInterface($dados["sei_id"]);
				}
				if ($dados["cha_id"]) {
					$this->importaChamado($dados["cha_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servidores_interfaces.sei_id
				FROM
					tvintranet.tab_servidores_interfaces
				WHERE
					tvintranet.tab_servidores_interfaces.ser_id = {$ser_id}");
					
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				$this->importaServidorInterface($dados["sei_id"]);
			}

		}
		
	}
	
	function importaAjusteFinanceiro($ajf_id) {

		print "importaAjusteFinanceiro($ajf_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_ajustes_financeiros
			WHERE
				tvintranet_demo.tab_ajustes_financeiros.ajf_id = {$ajf_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_ajustes_financeiros
				SELECT
					*
				FROM
					tvintranet.tab_ajustes_financeiros
				WHERE
					tvintranet.tab_ajustes_financeiros.ajf_id = {$ajf_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_ajustes_financeiros.*
				FROM
					tvintranet.tab_ajustes_financeiros
				WHERE
					tvintranet.tab_ajustes_financeiros.ajf_id = {$ajf_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["ajf_pai"]) {
					$this->importaAjusteFinanceiro($dados["ajf_pai"]);
				}
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["ass_id"]) {
					$this->importaAssinatura($dados["ass_id"]);
				}
				if ($dados["cor_id"]) {
					$this->importaContrato($dados["cor_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_ajustes_financeiros_comentarios.afm_id
				FROM
					tvintranet.tab_ajustes_financeiros_comentarios
				WHERE
					tvintranet.tab_ajustes_financeiros_comentarios.ajf_id = {$ajf_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaAjusteFinanceiroComentario($dados["afm_id"]);
			}
			
		}

	}
	
	function importaAjusteFinanceiroComentario($afm_id) {

		print "importaAjusteFinanceiroComentario($afm_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_ajustes_financeiros_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_ajustes_financeiros_comentarios
			WHERE
				tvintranet.tab_ajustes_financeiros_comentarios.afm_id = {$afm_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_ajustes_financeiros_comentarios.*
			FROM
				tvintranet.tab_ajustes_financeiros_comentarios
			WHERE
				tvintranet.tab_ajustes_financeiros_comentarios.afm_id = {$afm_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}
	
	function importaPlano($pla_id) {

		print "importaPlano($pla_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_planos
			WHERE
				tvintranet_demo.tab_planos.pla_id = {$pla_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_planos
				SELECT
					*
				FROM
					tvintranet.tab_planos
				WHERE
					tvintranet.tab_planos.pla_id = {$pla_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_planos.*
				FROM
					tvintranet.tab_planos
				WHERE
					tvintranet.tab_planos.pla_id = {$pla_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
			}
								
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_formas_pagamento.fop_id
				FROM
					tvintranet.tab_formas_pagamento
				WHERE
					tvintranet.tab_formas_pagamento.pla_id = {$pla_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaFormaPagamento($dados["fop_id"]);
			}
								
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_descontos_concessoes.dec_id
				FROM
					tvintranet.tab_descontos_concessoes
				WHERE
					tvintranet.tab_descontos_concessoes.pla_id = {$pla_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaDescontoConcessao($dados["dec_id"]);
			}

		}
		
	}
	
	function importaDescontoConcessao($dec_id) {

		print "importaDescontoConcessao($dec_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_descontos_concessoes
			WHERE
				tvintranet_demo.tab_descontos_concessoes.dec_id = {$dec_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_descontos_concessoes
				SELECT
					*
				FROM
					tvintranet.tab_descontos_concessoes
				WHERE
					tvintranet.tab_descontos_concessoes.dec_id = {$dec_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_descontos_concessoes.*
				FROM
					tvintranet.tab_descontos_concessoes
				WHERE
					tvintranet.tab_descontos_concessoes.dec_id = {$dec_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["dec_pai"]) {
					$this->importaDescontoConcessao($dados["dec_pai"]);
				}
				if ($dados["ass_id"]) {
					$this->importaAssinatura($dados["ass_id"]);
				}
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
				if ($dados["des_id"]) {
					$this->importaDesconto($dados["des_id"]);
				}
			}

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_descontos_concessoes_comentarios.dcc_id
				FROM
					tvintranet.tab_descontos_concessoes_comentarios
				WHERE
					tvintranet.tab_descontos_concessoes_comentarios.dec_id = {$dec_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaDescontoConcessaoComentario($dados["dcc_id"]);
			}

		}
		
	}
	
	function importaDescontoConcessaoComentario($dcc_id) {

		print "importaDescontoConcessaoComentario($dcc_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_descontos_concessoes_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_descontos_concessoes_comentarios
			WHERE
				tvintranet.tab_descontos_concessoes_comentarios.dcc_id = {$dcc_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_descontos_concessoes_comentarios.*
			FROM
				tvintranet.tab_descontos_concessoes_comentarios
			WHERE
				tvintranet.tab_descontos_concessoes_comentarios.dcc_id = {$dcc_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}	
	
	function importaDesconto($des_id) {

		print "importaDesconto($des_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_descontos
			WHERE
				tvintranet_demo.tab_descontos.des_id = {$des_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_descontos
				SELECT
					*
				FROM
					tvintranet.tab_descontos
				WHERE
					tvintranet.tab_descontos.des_id = {$des_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_descontos.*
				FROM
					tvintranet.tab_descontos
				WHERE
					tvintranet.tab_descontos.des_id = {$des_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["pla_id"]) {
					$this->importaPlano($dados["pla_id"]);
				}
				if ($dados["clo_id"]) {
					$this->importaOrigem($dados["clo_id"]);
				}
			}

		}
		
	}
	
	function importaFormaPagamento($fop_id) {

		print "importaFormaPagamento($fop_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_formas_pagamento
			WHERE
				tvintranet_demo.tab_formas_pagamento.fop_id = {$fop_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_formas_pagamento
				SELECT
					*
				FROM
					tvintranet.tab_formas_pagamento
				WHERE
					tvintranet.tab_formas_pagamento.fop_id = {$fop_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_formas_pagamento.*
				FROM
					tvintranet.tab_formas_pagamento
				WHERE
					tvintranet.tab_formas_pagamento.fop_id = {$fop_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["pla_id"]) {
					$this->importaPlano($dados["pla_id"]);
				}
			}

		}
		
	}
	
	function importaChamado($cha_id) {

		print "importaChamado($cha_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_chamados
			WHERE
				tvintranet_demo.tab_chamados.cha_id = {$cha_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_chamados
				SELECT
					*
				FROM
					tvintranet.tab_chamados
				WHERE
					tvintranet.tab_chamados.cha_id = {$cha_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_chamados.*
				FROM
					tvintranet.tab_chamados
				WHERE
					tvintranet.tab_chamados.cha_id = {$cha_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cha_pai"]) {
					$this->importaChamado($dados["cha_pai"]);
				}
				if ($dados["usu_id"]) {
					$this->importaUsuario($dados["usu_id"]);
				}
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["vis_id"]) {
					$this->importaVisita($dados["vis_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_chamados_etapas.che_id
				FROM
					tvintranet.tab_chamados_etapas
				WHERE
					tvintranet.tab_chamados_etapas.cha_id = {$cha_id}");
					
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				$this->importaChamadoEtapa($dados["che_id"]);
			}

		}
		
	}
	
	function importaChamadoEtapa($che_id) {

		print "importaChamadoEtapa($che_id)\n";
								
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_chamados_etapas
			SELECT
				*
			FROM
				tvintranet.tab_chamados_etapas
			WHERE
				tvintranet.tab_chamados_etapas.che_id = {$che_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_chamados_etapas.*
			FROM
				tvintranet.tab_chamados_etapas
			WHERE
				tvintranet.tab_chamados_etapas.che_id = {$che_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
			if ($dados["che_pai"]) {
				$this->importaChamadoEtapa($dados["che_pai"]);
			}
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}

	}
	
	function importaCobranca($coa_id) {

		print "importaCobranca($coa_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_cobrancas
			WHERE
				tvintranet_demo.tab_cobrancas.coa_id = {$coa_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cobrancas
				SELECT
					*
				FROM
					tvintranet.tab_cobrancas
				WHERE
					tvintranet.tab_cobrancas.coa_id = {$coa_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas.*
				FROM
					tvintranet.tab_cobrancas
				WHERE
					tvintranet.tab_cobrancas.coa_id = {$coa_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["coa_pai"]) {
					$this->importaCobranca($dados["coa_pai"]);
				}
				if ($dados["coa_grupo_id"]) {
					$this->importaCobranca($dados["coa_grupo_id"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["col_id"]) {
					$this->importaCobrancaLote($dados["col_id"]);
				}
				if ($dados["cof_id"]) {
					$this->importaCobrancaFormato($dados["cof_id"]);
				}				
				if ($dados["fco_id"]) {
					$this->importaFaturaLoteCobranca($dados["fco_id"]);
				}
				if ($dados["fat_id"]) {
					$this->importaFatura($dados["fat_id"]);
				}
				if ($dados["rec_id"]) {
					$this->importaRecuperacaoContato($dados["rec_id"]);
				}
				if ($dados["coe_id"]) {
					$this->importaCobrancaRetorno($dados["coe_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_detalhes.cod_id
				FROM
					tvintranet.tab_cobrancas_detalhes
				WHERE
					tvintranet.tab_cobrancas_detalhes.coa_id = {$coa_id}");
					
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				$this->importaCobrancaDetalhe($dados["cod_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_comentarios.com_id
				FROM
					tvintranet.tab_cobrancas_comentarios
				WHERE
					tvintranet.tab_cobrancas_comentarios.coa_id = {$coa_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCobrancaComentario($dados["com_id"]);
			}
								
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_retornos_registros.coe_id
				FROM
					tvintranet.tab_cobrancas_retornos_registros
				WHERE
					tvintranet.tab_cobrancas_retornos_registros.coa_id = {$coa_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCobrancaRetorno($dados["coe_id"]);
			}
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cobrancas_retornos_registros
				SELECT
					*
				FROM
					tvintranet.tab_cobrancas_retornos_registros
				WHERE
					tvintranet.tab_cobrancas_retornos_registros.coa_id = {$coa_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_retornos_nao_encontradas.coe_id
				FROM
					tvintranet.tab_cobrancas_retornos_nao_encontradas
				WHERE
					tvintranet.tab_cobrancas_retornos_nao_encontradas.coa_id = {$coa_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCobrancaRetorno($dados["coe_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cobrancas_retornos_nao_encontradas
				SELECT
					*
				FROM
					tvintranet.tab_cobrancas_retornos_nao_encontradas
				WHERE
					tvintranet.tab_cobrancas_retornos_nao_encontradas.coa_id = {$coa_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_retornos_erros.coe_id
				FROM
					tvintranet.tab_cobrancas_retornos_erros
				WHERE
					tvintranet.tab_cobrancas_retornos_erros.coa_id = {$coa_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCobrancaRetorno($dados["coe_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cobrancas_retornos_erros
				SELECT
					*
				FROM
					tvintranet.tab_cobrancas_retornos_erros
				WHERE
					tvintranet.tab_cobrancas_retornos_erros.coa_id = {$coa_id}");

		}
		
	}
	
	function importaCobrancaComentario($com_id) {

		print "importaCobrancaComentario($com_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_cobrancas_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_cobrancas_comentarios
			WHERE
				tvintranet.tab_cobrancas_comentarios.com_id = {$com_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_cobrancas_comentarios.*
			FROM
				tvintranet.tab_cobrancas_comentarios
			WHERE
				tvintranet.tab_cobrancas_comentarios.com_id = {$com_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}	
		
	function importaCobrancaDetalhe($cod_id) {

		print "importaCobrancaDetalhe($cod_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_cobrancas_detalhes
			WHERE
				tvintranet_demo.tab_cobrancas_detalhes.cod_id = {$cod_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cobrancas_detalhes
				SELECT
					*
				FROM
					tvintranet.tab_cobrancas_detalhes
				WHERE
					tvintranet.tab_cobrancas_detalhes.cod_id = {$cod_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_detalhes.*
				FROM
					tvintranet.tab_cobrancas_detalhes
				WHERE
					tvintranet.tab_cobrancas_detalhes.cod_id = {$cod_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cod_pai"]) {
					$this->importaCobrancaDetalhe($dados["cod_pai"]);
				}
				if ($dados["coa_id"]) {
					$this->importaCobranca($dados["coa_id"]);
				}
				if ($dados["ass_id"]) {
					$this->importaAssinatura($dados["ass_id"]);
				}
				if ($dados["ajf_id"]) {
					$this->importaAjusteFinanceiro($dados["ajf_id"]);
				}
			}

		}
		
	}
	
	function importaCobrancaLote($col_id) {

		print "importaCobrancaLote($col_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_cobrancas_lotes
			WHERE
				tvintranet_demo.tab_cobrancas_lotes.col_id = {$col_id}");

		if (!$this->bancodedados->resultado($query)) {
									
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cobrancas_lotes
				SELECT
					*
				FROM
					tvintranet.tab_cobrancas_lotes
				WHERE
					tvintranet.tab_cobrancas_lotes.col_id = {$col_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cobrancas_lotes.*
				FROM
					tvintranet.tab_cobrancas_lotes
				WHERE
					tvintranet.tab_cobrancas_lotes.col_id = {$col_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cof_id"]) {
					$this->importaCobrancaFormato($dados["cof_id"]);
				}
			}

		}
		
	}

	function importaFaturaLoteCobranca($fco_id) {

		print "importaFaturaLoteCobranca($fco_id)\n";
	
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_faturas_lotes_cobrancas
			SELECT
				*
			FROM
				tvintranet.tab_faturas_lotes_cobrancas
			WHERE
				tvintranet.tab_faturas_lotes_cobrancas.fco_id = {$fco_id}");
		
	}	
	
	function importaFatura($fat_id) {

		print "importaFatura($fat_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_faturas
			WHERE
				tvintranet_demo.tab_faturas.fat_id = {$fat_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_faturas
				SELECT
					*
				FROM
					tvintranet.tab_faturas
				WHERE
					tvintranet.tab_faturas.fat_id = {$fat_id}");			

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_faturas.*
				FROM
					tvintranet.tab_faturas
				WHERE
					tvintranet.tab_faturas.fat_id = {$fat_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["fal_id"]) {
					$this->importaFaturaLote($dados["fal_id"]);
				}
				if ($dados["fcl_id"]) {
					$this->importaFaturaLoteCliente($dados["fcl_id"]);
				}
			}
			
		}
		
	}
	
	function importaFaturaLoteCliente($fcl_id) {

		print "importaFaturaLoteCliente($fcl_id)\n";

		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_faturas_lotes_clientes
			SELECT
				*
			FROM
				tvintranet.tab_faturas_lotes_clientes
			WHERE
				tvintranet.tab_faturas_lotes_clientes.fcl_id = {$fcl_id}");	
		
	}

	function importaFaturaLote($fal_id) {

		print "importaFaturaLote($fal_id)\n";
						
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_faturas_lotes
			SELECT
				*
			FROM
				tvintranet.tab_faturas_lotes
			WHERE
				tvintranet.tab_faturas_lotes.fal_id = {$fal_id}");
		
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_faturas_lotes_filtros
			SELECT
				*
			FROM
				tvintranet.tab_faturas_lotes_filtros
			WHERE
				tvintranet.tab_faturas_lotes_filtros.fal_id = {$fal_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_faturas_lotes_comentarios.flc_id
			FROM
				tvintranet.tab_faturas_lotes_comentarios
			WHERE
				tvintranet.tab_faturas_lotes_comentarios.fal_id = {$fal_id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->importaFaturaLoteComentario($dados["flc_id"]);
		}

	}
	
	function importaFaturaLoteComentario($flc_id) {

		print "importaFaturaLoteComentario($flc_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_faturas_lotes_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_faturas_lotes_comentarios
			WHERE
				tvintranet.tab_faturas_lotes_comentarios.flc_id = {$flc_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_faturas_lotes_comentarios.*
			FROM
				tvintranet.tab_faturas_lotes_comentarios
			WHERE
				tvintranet.tab_faturas_lotes_comentarios.flc_id = {$flc_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}	
	
	function importaRecuperacaoContato($rec_id) {

		print "importaRecuperacaoContato($rec_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_recuperacao_contatos
			SELECT
				*
			FROM
				tvintranet.tab_recuperacao_contatos
			WHERE
				tvintranet.tab_recuperacao_contatos.rec_id = {$rec_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_recuperacao_contatos.*
			FROM
				tvintranet.tab_recuperacao_contatos
			WHERE
				tvintranet.tab_recuperacao_contatos.rec_id = {$rec_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}
	
	function importaCobrancaRetorno($coe_id) {

		print "importaCobrancaRetorno($coe_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_cobrancas_retornos
			SELECT
				*
			FROM
				tvintranet.tab_cobrancas_retornos
			WHERE
				tvintranet.tab_cobrancas_retornos.coe_id = {$coe_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_cobrancas_retornos.*
			FROM
				tvintranet.tab_cobrancas_retornos
			WHERE
				tvintranet.tab_cobrancas_retornos.coe_id = {$coe_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["cof_id"]) {
				$this->importaCobrancaFormato($dados["cof_id"]);
			}
		}
		
	}
		
	function importaAssinatura($ass_id) {

		print "importaAssinatura($ass_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_assinaturas
			WHERE
				tvintranet_demo.tab_assinaturas.ass_id = {$ass_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_assinaturas
				SELECT
					*
				FROM
					tvintranet.tab_assinaturas
				WHERE
					tvintranet.tab_assinaturas.ass_id = {$ass_id}");			

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_assinaturas.*
				FROM
					tvintranet.tab_assinaturas
				WHERE
					tvintranet.tab_assinaturas.ass_id = {$ass_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["ass_pai"]) {
					$this->importaAssinatura($dados["ass_pai"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["pla_id"]) {
					$this->importaPlano($dados["pla_id"]);
				}
			}
											
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_descontos_concessoes.dec_id
				FROM
					tvintranet.tab_descontos_concessoes
				WHERE
					tvintranet.tab_descontos_concessoes.ass_id = {$ass_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaDescontoConcessao($dados["dec_id"]);
			}
	
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_pontos.clp_id
				FROM
					tvintranet.tab_clientes_pontos
				WHERE
					tvintranet.tab_clientes_pontos.ass_id = {$ass_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClientePonto($dados["clp_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_autenticacoes.cau_id
				FROM
					tvintranet.tab_clientes_autenticacoes
				WHERE
					tvintranet.tab_clientes_autenticacoes.ass_id = {$ass_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteAutenticacao($dados["cau_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_dominios
				SELECT
					*
				FROM
					tvintranet.tab_dominios
				WHERE
					tvintranet.tab_dominios.ass_id = {$ass_id}");			

		}

	}
	
	function importaClienteResponsabilidade($cln_id) {

		print "importaClienteResponsabilidade($cln_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_responsabilidades
			WHERE
				tvintranet_demo.tab_clientes_responsabilidades.cln_id = {$cln_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_responsabilidades
				SELECT
					*
				FROM
					tvintranet.tab_clientes_responsabilidades
				WHERE
					tvintranet.tab_clientes_responsabilidades.cln_id = {$cln_id}");			

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_responsabilidades.*
				FROM
					tvintranet.tab_clientes_responsabilidades
				WHERE
					tvintranet.tab_clientes_responsabilidades.cln_id = {$cln_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cod_id"]) {
					$this->importaCondominioResponsabilidade($dados["cod_id"]);
				}
				if ($dados["usu_id"]) {
					$this->importaUsuario($dados["usu_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_responsabilidades_comentarios.crc_id
				FROM
					tvintranet.tab_clientes_responsabilidades_comentarios
				WHERE
					tvintranet.tab_clientes_responsabilidades_comentarios.cln_id = {$cln_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteResponsabilidadeComentario($dados["crc_id"]);
			}
		
		}

	}
	
	function importaClienteResponsabilidadeComentario($crc_id) {

		print "importaClienteResponsabilidadeComentario($crc_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_clientes_responsabilidades_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_clientes_responsabilidades_comentarios
			WHERE
				tvintranet.tab_clientes_responsabilidades_comentarios.crc_id = {$crc_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_clientes_responsabilidades_comentarios.*
			FROM
				tvintranet.tab_clientes_responsabilidades_comentarios
			WHERE
				tvintranet.tab_clientes_responsabilidades_comentarios.crc_id = {$crc_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}
	
	function importaClienteRetorno($clr_id) {

		print "importaClienteRetorno($clr_id)\n";
					
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_clientes_retornos
			SELECT
				*
			FROM
				tvintranet.tab_clientes_retornos
			WHERE
				tvintranet.tab_clientes_retornos.clr_id = {$clr_id}");			

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_clientes_retornos.*
			FROM
				tvintranet.tab_clientes_retornos
			WHERE
				tvintranet.tab_clientes_retornos.clr_id = {$clr_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
			
	}		
	
	function importaClientePonto($clp_id) {

		print "importaClientePonto($clp_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_pontos
			WHERE
				tvintranet_demo.tab_clientes_pontos.clp_id = {$clp_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_pontos
				SELECT
					*
				FROM
					tvintranet.tab_clientes_pontos
				WHERE
					tvintranet.tab_clientes_pontos.clp_id = {$clp_id}");			

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_pontos.*
				FROM
					tvintranet.tab_clientes_pontos
				WHERE
					tvintranet.tab_clientes_pontos.clp_id = {$clp_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["sei_id"]) {
					$this->importaServidorInterface($dados["sei_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_pontos_comentarios.cpc_id
				FROM
					tvintranet.tab_clientes_pontos_comentarios
				WHERE
					tvintranet.tab_clientes_pontos_comentarios.clp_id = {$clp_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClientePontoComentario($dados["cpc_id"]);
			}
		
		}
		
	}
	
	function importaClientePontoComentario($cpc_id) {

		print "importaClientePontoComentario($cpc_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_clientes_pontos_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_clientes_pontos_comentarios
			WHERE
				tvintranet.tab_clientes_pontos_comentarios.cpc_id = {$cpc_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_clientes_pontos_comentarios.*
			FROM
				tvintranet.tab_clientes_pontos_comentarios
			WHERE
				tvintranet.tab_clientes_pontos_comentarios.cpc_id = {$cpc_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}
	
	function importaClienteAutenticacao($cau_id) {

		print "importaClienteAutenticacao($cau_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_autenticacoes
			WHERE
				tvintranet_demo.tab_clientes_autenticacoes.cau_id = {$cau_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_autenticacoes
				SELECT
					*
				FROM
					tvintranet.tab_clientes_autenticacoes
				WHERE
					tvintranet.tab_clientes_autenticacoes.cau_id = {$cau_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_autenticacoes.*
				FROM
					tvintranet.tab_clientes_autenticacoes
				WHERE
					tvintranet.tab_clientes_autenticacoes.cau_id = {$cau_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["ser_id"]) {
					$this->importaServidor($dados["ser_id"]);
				}
				if ($dados["sei_id"]) {
					$this->importaServidorInterface($dados["sei_id"]);
				}
				if ($dados["ssi_id"]) {
					$this->importaServidorSSID($dados["ssi_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_autenticacoes_comentarios.cac_id
				FROM
					tvintranet.tab_clientes_autenticacoes_comentarios
				WHERE
					tvintranet.tab_clientes_autenticacoes_comentarios.cau_id = {$cau_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteAutenticacaoComentario($dados["cac_id"]);
			}
		
		}
		
	}
		
	function importaClienteAutenticacaoComentario($cac_id) {

		print "importaClienteAutenticacaoComentario($cac_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_clientes_autenticacoes_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_clientes_autenticacoes_comentarios
			WHERE
				tvintranet.tab_clientes_autenticacoes_comentarios.cac_id = {$cac_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_clientes_autenticacoes_comentarios.*
			FROM
				tvintranet.tab_clientes_autenticacoes_comentarios
			WHERE
				tvintranet.tab_clientes_autenticacoes_comentarios.cac_id = {$cac_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaClienteIptv($cip_id) {

		print "importaClienteIptv($cip_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_iptv
			WHERE
				tvintranet_demo.tab_clientes_iptv.cip_id = {$cip_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_iptv
				SELECT
					*
				FROM
					tvintranet.tab_clientes_iptv
				WHERE
					tvintranet.tab_clientes_iptv.cip_id = {$cip_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_iptv.*
				FROM
					tvintranet.tab_clientes_iptv
				WHERE
					tvintranet.tab_clientes_iptv.cip_id = {$cip_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["cor_id"]) {
					$this->importaContrato($dados["cor_id"]);
				}
				if ($dados["pla_id"]) {
					$this->importaPlano($dados["pla_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_iptv_comentarios.cic_id
				FROM
					tvintranet.tab_clientes_iptv_comentarios
				WHERE
					tvintranet.tab_clientes_iptv_comentarios.cip_id = {$cip_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteIptvComentario($dados["cic_id"]);
			}
		
		}
		
	}

	function importaClienteIptvComentario($cic_id) {

		print "importaClienteIptvComentario($cic_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_clientes_iptv_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_clientes_iptv_comentarios
			WHERE
				tvintranet.tab_clientes_iptv_comentarios.cic_id = {$cic_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_clientes_iptv_comentarios.*
			FROM
				tvintranet.tab_clientes_iptv_comentarios
			WHERE
				tvintranet.tab_clientes_iptv_comentarios.cic_id = {$cic_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaVisita($vis_id) {

		print "importaVisita($vis_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_visitas
			WHERE
				tvintranet_demo.tab_visitas.vis_id = {$vis_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_visitas
				SELECT
					*
				FROM
					tvintranet.tab_visitas
				WHERE
					tvintranet.tab_visitas.vis_id = {$vis_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_visitas.*
				FROM
					tvintranet.tab_visitas
				WHERE
					tvintranet.tab_visitas.vis_id = {$vis_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["con_id"]) {
					$this->importaCondominio($dados["con_id"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["cor_id"]) {
					$this->importaContrato($dados["cor_id"]);
				}
				if ($dados["cha_id"]) {
					$this->importaChamado($dados["cha_id"]);
				}
				if ($dados["usu_id"]) {
					$this->importaUsuario($dados["usu_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_visitas_comentarios.vic_id
				FROM
					tvintranet.tab_visitas_comentarios
				WHERE
					tvintranet.tab_visitas_comentarios.vis_id = {$vis_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaVisitaComentario($dados["vic_id"]);
			}
		
		}

	}
		
	function importaVisitaComentario($vic_id) {

		print "importaVisitaComentario($vic_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_visitas_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_visitas_comentarios
			WHERE
				tvintranet.tab_visitas_comentarios.vic_id = {$vic_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_visitas_comentarios.*
			FROM
				tvintranet.tab_visitas_comentarios
			WHERE
				tvintranet.tab_visitas_comentarios.vic_id = {$vic_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaContrato($cor_id) {

		print "importaContrato($cor_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_contratos
			WHERE
				tvintranet_demo.tab_contratos.cor_id = {$cor_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_contratos
				SELECT
					*
				FROM
					tvintranet.tab_contratos
				WHERE
					tvintranet.tab_contratos.cor_id = {$cor_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_contratos.*
				FROM
					tvintranet.tab_contratos
				WHERE
					tvintranet.tab_contratos.cor_id = {$cor_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cor_pai"]) {
					$this->importaContrato($dados["cor_pai"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["fop_id"]) {
					$this->importaFormaPagamento($dados["fop_id"]);
				}
			}
								
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_ass_cor_estacontido
				SELECT
					*
				FROM
					tvintranet.rel_ass_cor_estacontido
				WHERE
					tvintranet.rel_ass_cor_estacontido.cor_id = {$cor_id}");
								
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_dec_cor_estacontido
				SELECT
					*
				FROM
					tvintranet.rel_dec_cor_estacontido
				WHERE
					tvintranet.rel_dec_cor_estacontido.cor_id = {$cor_id}");
								
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_equ_cor_comodato
				SELECT
					*
				FROM
					tvintranet.rel_equ_cor_comodato
				WHERE
					tvintranet.rel_equ_cor_comodato.cor_id = {$cor_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_fop_cor_estacontido.fop_id
				FROM
					tvintranet.rel_fop_cor_estacontido
				WHERE
					tvintranet.rel_fop_cor_estacontido.cor_id = {$cor_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaFormaPagamento($dados["fop_id"]);
			}
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_fop_cor_estacontido
				SELECT
					*
				FROM
					tvintranet.rel_fop_cor_estacontido
				WHERE
					tvintranet.rel_fop_cor_estacontido.cor_id = {$cor_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_contratos_comentarios.coo_id
				FROM
					tvintranet.tab_contratos_comentarios
				WHERE
					tvintranet.tab_contratos_comentarios.cor_id = {$cor_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaContratoComentario($dados["coo_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_vendas.ven_id
				FROM
					tvintranet.tab_vendas
				WHERE
					tvintranet.tab_vendas.cor_id = {$cor_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaVenda($dados["ven_id"]);
			}
		
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_iptv.cip_id
				FROM
					tvintranet.tab_clientes_iptv
				WHERE
					tvintranet.tab_clientes_iptv.cor_id = {$cor_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteIptv($dados["cip_id"]);
			}

		}
		
	}
	
	function importaVenda($ven_id) {
		
		print "importaVenda($ven_id)\n";
											
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_vendas
			SELECT
				*
			FROM
				tvintranet.tab_vendas
			WHERE
				tvintranet.tab_vendas.ven_id = {$ven_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_vendas.*
			FROM
				tvintranet.tab_vendas
			WHERE
				tvintranet.tab_vendas.ven_id = {$ven_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_vendas_comentarios.veo_id
			FROM
				tvintranet.tab_vendas_comentarios
			WHERE
				tvintranet.tab_vendas_comentarios.ven_id = {$ven_id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->importaVendaComentario($dados["veo_id"]);
		}

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_vendas_comissoes.vec_id
			FROM
				tvintranet.tab_vendas_comissoes
			WHERE
				tvintranet.tab_vendas_comissoes.ven_id = {$ven_id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->importaVendaComissao($dados["vec_id"]);
		}

	}
	
	function importaVendaComissao($vec_id) {
				
		print "importaVendaComissao($vec_id)\n";
											
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_vendas_comissoes
			SELECT
				*
			FROM
				tvintranet.tab_vendas_comissoes
			WHERE
				tvintranet.tab_vendas_comissoes.vec_id = {$vec_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_vendas_comissoes.*
			FROM
				tvintranet.tab_vendas_comissoes
			WHERE
				tvintranet.tab_vendas_comissoes.vec_id = {$vec_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_vendas_comissoes_comentarios.vco_id
			FROM
				tvintranet.tab_vendas_comissoes_comentarios
			WHERE
				tvintranet.tab_vendas_comissoes_comentarios.vec_id = {$vec_id}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->importaVendaComissaoComentario($dados["vco_id"]);
		}
	}
	
	function importaVendaComissaoComentario($vco_id) {

		print "importaVendaComissaoComentario($vco_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_vendas_comissoes_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_vendas_comissoes_comentarios
			WHERE
				tvintranet.tab_vendas_comissoes_comentarios.vco_id = {$vco_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_vendas_comissoes_comentarios.*
			FROM
				tvintranet.tab_vendas_comissoes_comentarios
			WHERE
				tvintranet.tab_vendas_comissoes_comentarios.vco_id = {$vco_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaVendaComentario($veo_id) {

		print "importaVendaComentario($veo_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_vendas_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_vendas_comentarios
			WHERE
				tvintranet.tab_vendas_comentarios.veo_id = {$veo_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_contratos_comentarios.*
			FROM
				tvintranet.tab_contratos_comentarios
			WHERE
				tvintranet.tab_contratos_comentarios.veo_id = {$veo_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaContratoComentario($coo_id) {

		print "importaContratoComentario($coo_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_contratos_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_contratos_comentarios
			WHERE
				tvintranet.tab_contratos_comentarios.coo_id = {$coo_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_contratos_comentarios.*
			FROM
				tvintranet.tab_contratos_comentarios
			WHERE
				tvintranet.tab_contratos_comentarios.coo_id = {$coo_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}
	
	function importaServicoAjuste($saj_id) {

		print "importaServicoAjuste($saj_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_servicos_ajustes
			WHERE
				tvintranet_demo.tab_servicos_ajustes.saj_id = {$saj_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servicos_ajustes
				SELECT
					*
				FROM
					tvintranet.tab_servicos_ajustes
				WHERE
					tvintranet.tab_servicos_ajustes.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servicos_ajustes.*
				FROM
					tvintranet.tab_servicos_ajustes
				WHERE
					tvintranet.tab_servicos_ajustes.saj_id = {$saj_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["saj_pai"]) {
					$this->importaServicoAjuste($dados["saj_pai"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["ass_id"]) {
					$this->importaAssinatura($dados["ass_id"]);
				}
				if ($dados["ajf_id"]) {
					$this->importaAjusteFinanceiro($dados["ajf_id"]);
				}
				if ($dados["usu_id"]) {
					$this->importaUsuario($dados["usu_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servicos_ajustes.saj_id
				FROM
					tvintranet.tab_servicos_ajustes
				WHERE
					tvintranet.tab_servicos_ajustes.saj_pai = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaServicoAjuste($dados["saj_id"]);
			}
					
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_autenticacoes_ajustes
				SELECT
					*
				FROM
					tvintranet.tab_clientes_autenticacoes_ajustes
				WHERE
					tvintranet.tab_clientes_autenticacoes_ajustes.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_autenticacoes_ajustes.cau_id
				FROM
					tvintranet.tab_clientes_autenticacoes_ajustes
				WHERE
					tvintranet.tab_clientes_autenticacoes_ajustes.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteAutenticacao($dados["cau_id"]);
			}
						
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_iptv_ajustes
				SELECT
					*
				FROM
					tvintranet.tab_clientes_iptv_ajustes
				WHERE
					tvintranet.tab_clientes_iptv_ajustes.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_iptv_ajustes.cip_id
				FROM
					tvintranet.tab_clientes_iptv_ajustes
				WHERE
					tvintranet.tab_clientes_iptv_ajustes.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteIptv($dados["cip_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_pontos_ajustes
				SELECT
					*
				FROM
					tvintranet.tab_clientes_pontos_ajustes
				WHERE
					tvintranet.tab_clientes_pontos_ajustes.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_pontos_ajustes.clp_id
				FROM
					tvintranet.tab_clientes_pontos_ajustes
				WHERE
					tvintranet.tab_clientes_pontos_ajustes.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClientePonto($dados["clp_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_saj_ass_contem
				SELECT
					*
				FROM
					tvintranet.rel_saj_ass_contem
				WHERE
					tvintranet.rel_saj_ass_contem.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_saj_ass_contem.ass_id
				FROM
					tvintranet.rel_saj_ass_contem
				WHERE
					tvintranet.rel_saj_ass_contem.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaAssinatura($dados["ass_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_saj_cau_contem
				SELECT
					*
				FROM
					tvintranet.rel_saj_cau_contem
				WHERE
					tvintranet.rel_saj_cau_contem.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_saj_cau_contem.cau_id
				FROM
					tvintranet.rel_saj_cau_contem
				WHERE
					tvintranet.rel_saj_cau_contem.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteAutenticacao($dados["cau_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_saj_cip_contem
				SELECT
					*
				FROM
					tvintranet.rel_saj_cip_contem
				WHERE
					tvintranet.rel_saj_cip_contem.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_saj_cip_contem.cip_id
				FROM
					tvintranet.rel_saj_cip_contem
				WHERE
					tvintranet.rel_saj_cip_contem.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteIptv($dados["cip_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_saj_clp_contem
				SELECT
					*
				FROM
					tvintranet.rel_saj_clp_contem
				WHERE
					tvintranet.rel_saj_clp_contem.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_saj_clp_contem.clp_id
				FROM
					tvintranet.rel_saj_clp_contem
				WHERE
					tvintranet.rel_saj_clp_contem.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClientePonto($dados["clp_id"]);
			}

			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_servicos_ajustes_regras
				SELECT
					*
				FROM
					tvintranet.tab_servicos_ajustes_regras
				WHERE
					tvintranet.tab_servicos_ajustes_regras.saj_id = {$saj_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_servicos_ajustes_comentarios.sam_id
				FROM
					tvintranet.tab_servicos_ajustes_comentarios
				WHERE
					tvintranet.tab_servicos_ajustes_comentarios.saj_id = {$saj_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaServicoAjusteComentario($dados["sam_id"]);
			}
		
		}
		
	}
		
	function importaServicoAjusteComentario($sam_id) {

		print "importaServicoAjusteComentario($sam_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_servicos_ajustes_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_servicos_ajustes_comentarios
			WHERE
				tvintranet.tab_servicos_ajustes_comentarios.sam_id = {$sam_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_servicos_ajustes_comentarios.*
			FROM
				tvintranet.tab_servicos_ajustes_comentarios
			WHERE
				tvintranet.tab_servicos_ajustes_comentarios.sam_id = {$sam_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaClienteComodato($clc_id) {

		print "importaClienteComodato($clc_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_comodatos
			WHERE
				tvintranet_demo.tab_clientes_comodatos.clc_id = {$clc_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_comodatos
				SELECT
					*
				FROM
					tvintranet.tab_clientes_comodatos
				WHERE
					tvintranet.tab_clientes_comodatos.clc_id = {$clc_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_comodatos.*
				FROM
					tvintranet.tab_clientes_comodatos
				WHERE
					tvintranet.tab_clientes_comodatos.clc_id = {$clc_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["clc_substituto_id"]) {
					$this->importaClienteComodato($dados["clc_substituto_id"]);
				}
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["ass_id"]) {
					$this->importaAssinatura($dados["ass_id"]);
				}
				if ($dados["cor_id"]) {
					$this->importaContrato($dados["cor_id"]);
				}
			}
					
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_comodatos_comentarios.ccc_id
				FROM
					tvintranet.tab_clientes_comodatos_comentarios
				WHERE
					tvintranet.tab_clientes_comodatos_comentarios.clc_id = {$clc_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaClienteComodatoComentario($dados["ccc_id"]);
			}
		
		}
		
	}
			
	function importaClienteComodatoComentario($ccc_id) {

		print "importaClienteComodatoComentario($ccc_id)\n";
							
		$this->bancodedados->query("
			INSERT IGNORE INTO
				tvintranet_demo.tab_clientes_comodatos_comentarios
			SELECT
				*
			FROM
				tvintranet.tab_clientes_comodatos_comentarios
			WHERE
				tvintranet.tab_clientes_comodatos_comentarios.ccc_id = {$ccc_id}");

		$query = $this->bancodedados->query("
			SELECT
				tvintranet.tab_clientes_comodatos_comentarios.*
			FROM
				tvintranet.tab_clientes_comodatos_comentarios
			WHERE
				tvintranet.tab_clientes_comodatos_comentarios.ccc_id = {$ccc_id}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ($dados["usu_id"]) {
				$this->importaUsuario($dados["usu_id"]);
			}
		}
		
	}

	function importaCancelamento($can_id) {

		print "importaCancelamento($can_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_cancelamentos
			WHERE
				tvintranet_demo.tab_cancelamentos.can_id = {$can_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cancelamentos
				SELECT
					*
				FROM
					tvintranet.tab_cancelamentos
				WHERE
					tvintranet.tab_cancelamentos.can_id = {$can_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cancelamentos.*
				FROM
					tvintranet.tab_cancelamentos
				WHERE
					tvintranet.tab_cancelamentos.can_id = {$can_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}
				if ($dados["cor_id"]) {
					$this->importaContrato($dados["cor_id"]);
				}
			}
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_cancelamentos_etapas
				SELECT
					*
				FROM
					tvintranet.tab_cancelamentos_etapas
				WHERE
					tvintranet.tab_cancelamentos_etapas.can_id = {$can_id}");
	
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_cancelamentos_etapas.usu_id
				FROM
					tvintranet.tab_cancelamentos_etapas
				WHERE
					tvintranet.tab_cancelamentos_etapas.can_id = {$can_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaUsuario($dados["usu_id"]);
			}

		}
		
	}
	
	function importaPesquisaAplicacao($pea_id) {

		print "importaPesquisaAplicacao($pea_id)\n";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_pesquisas_aplicacoes
			WHERE
				tvintranet_demo.tab_pesquisas_aplicacoes.pea_id = {$pea_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_pesquisas_aplicacoes
				SELECT
					*
				FROM
					tvintranet.tab_pesquisas_aplicacoes
				WHERE
					tvintranet.tab_pesquisas_aplicacoes.pea_id = {$pea_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_pesquisas_aplicacoes.*
				FROM
					tvintranet.tab_pesquisas_aplicacoes
				WHERE
					tvintranet.tab_pesquisas_aplicacoes.pea_id = {$pea_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cli_id"]) {
					$this->importaCliente($dados["cli_id"]);
				}		
				if ($dados["cha_id"]) {
					$this->importaChamado($dados["cha_id"]);
				}
				if ($dados["cor_id"]) {
					$this->importaContrato($dados["cor_id"]);
				}
				if ($dados["usu_id"]) {
					$this->importaContrato($dados["usu_id"]);
				}
			}
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_pesquisas_aplicacoes_respostas
				SELECT
					*
				FROM
					tvintranet.tab_pesquisas_aplicacoes_respostas
				WHERE
					tvintranet.tab_pesquisas_aplicacoes_respostas.pea_id = {$pea_id}");
	
		}
		
	}
	
	function importaOrigem($clo_id) {
		
		print "importaOrigem($clo_id)\n";

		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tvintranet_demo.tab_clientes_origens
			WHERE
				tvintranet_demo.tab_clientes_origens.clo_id = {$clo_id}");

		if (!$this->bancodedados->resultado($query)) {
			
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.tab_clientes_origens
				SELECT
					*
				FROM
					tvintranet.tab_clientes_origens
				WHERE
					tvintranet.tab_clientes_origens.clo_id = {$clo_id}");

			$query = $this->bancodedados->query("
				SELECT
					tvintranet.tab_clientes_origens.*
				FROM
					tvintranet.tab_clientes_origens
				WHERE
					tvintranet.tab_clientes_origens.clo_id = {$clo_id}");
					
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {			
				if ($dados["cep_id"]) {
					$this->importaCep($dados["cep_id"]);
				}		
			}
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_clo_cid_contem
				SELECT
					*
				FROM
					tvintranet.rel_clo_cid_contem
				WHERE
					tvintranet.rel_clo_cid_contem.clo_id = {$clo_id}");
	
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_clo_cid_contem.cid_id
				FROM
					tvintranet.rel_clo_cid_contem
				WHERE
					tvintranet.rel_clo_cid_contem.clo_id = {$clo_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCidade($dados["cid_id"]);
			}
							
			$this->bancodedados->query("
				INSERT IGNORE INTO
					tvintranet_demo.rel_cof_clo_disponivel
				SELECT
					*
				FROM
					tvintranet.rel_cof_clo_disponivel
				WHERE
					tvintranet.rel_cof_clo_disponivel.clo_id = {$clo_id}");
	
			$query = $this->bancodedados->query("
				SELECT
					tvintranet.rel_cof_clo_disponivel.cof_id
				FROM
					tvintranet.rel_cof_clo_disponivel
				WHERE
					tvintranet.rel_cof_clo_disponivel.clo_id = {$clo_id}");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$this->importaCobrancaFormato($dados["cof_id"]);
			}

		}
		
	}

}

?>
