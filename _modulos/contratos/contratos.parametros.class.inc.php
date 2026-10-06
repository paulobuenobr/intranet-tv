<?

class contratos_parametros {

	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	
	public $dados;

	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;

	}

	function buscaDadosCliente($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				DATE_FORMAT(tab_clientes.cli_data_cadastro,'%d/%m/%Y') AS data_cadastro,
				tab_cobrancas_formatos.cof_id AS cobranca_formato_id,
				tab_cobrancas_formatos.cof_nome AS cobranca_formato_nome,
				tab_clientes.cli_ccd_debito_agencia AS cobranca_debito_agencia,
				tab_clientes.cli_ccd_debito_conta AS cobranca_debito_conta,
				tab_clientes.cli_ccd_vencimento AS cobranca_vencimento,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_clientes.clo_id AS origem_id,
				DATE_FORMAT(tab_clientes.cli_nascimento, '%d/%m/%Y') AS nascimento,
				tab_clientes.cli_contato AS contato,
				tab_clientes_tipos.clt_id AS tipo_id,
				tab_clientes_tipos.clt_nome AS tipo_nome,
				tab_clientes_status.cls_id AS status_id,
				tab_clientes_status.cls_nome AS status_nome,
				tab_usuarios.usu_id AS responsavel_id,
				tab_usuarios.usu_nome AS responsavel_nome
			FROM
				tab_clientes
				INNER JOIN tab_clientes_tipos ON (tab_clientes.clt_id = tab_clientes_tipos.clt_id)
				INNER JOIN tab_clientes_status ON (tab_clientes.cls_id = tab_clientes_status.cls_id)
				LEFT OUTER JOIN tab_cobrancas_formatos ON (tab_clientes.cof_id = tab_cobrancas_formatos.cof_id)
				LEFT OUTER JOIN tab_usuarios ON (tab_clientes.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_clientes.cli_id = $cliente_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["endereco"] = $this->buscaDadosClienteEndereco($dados["id"]);
			$dados["endereco_cobranca"] = $this->buscaDadosClienteEnderecoCobranca($dados["id"]);
			$dados["emails"] = $this->buscaContatosEmails($dados["id"]);
			$dados["telefones"] = $this->buscaContatosTelefones($dados["id"]);
			
			return $dados;
			
		}
		
	}

	function buscaDadosInstalador($origem_id) {
		
		$dados = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_origens.clo_id AS id,
				tab_clientes_origens.clo_nome AS nome,
				tab_clientes_origens.clo_razao_social AS razao_social,
				tab_clientes_origens.clo_cnpj AS cnpj,
				tab_clientes_origens.clo_numero AS numero,
				tab_clientes_origens.clo_complemento AS complemento,
				tab_clientes_origens.clo_telefone AS telefone,
				tab_clientes_origens.clo_email AS email,
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado
			FROM
				tab_clientes_origens
				LEFT JOIN tab_ceps ON (tab_clientes_origens.cep_id = tab_ceps.cep_id)
				LEFT JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				LEFT JOIN tab_bairros ON (tab_ceps.bai_id = tab_bairros.bai_id)
				LEFT JOIN tab_cidades ON (tab_ceps.cid_id = tab_cidades.cid_id)
				LEFT JOIN tab_estados ON (tab_ceps.est_id = tab_estados.est_id)
			WHERE
				tab_clientes_origens.clo_id = $origem_id");
		
		if (($origem = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parceiro_nome"] = $origem["razao_social"];
			$dados["parceiro_cnpj"] = $origem["cnpj"];
			
			$dados["parceiro_endereco"] = array();
			
			$dados["parceiro_endereco"][] = $origem["logradouro"];
			if ($origem["numero"]) $dados["parceiro_endereco"][] = $origem["numero"];
			if ($origem["complemento"]) $dados["parceiro_endereco"][] = $origem["complemento"];
			
			$dados["parceiro_endereco"] = implode(", ", $dados["parceiro_endereco"]);

			$dados["parceiro_bairro"] = $origem["bairro"];
			$dados["parceiro_cidade"] = $origem["cidade"];
			$dados["parceiro_estado"] = $origem["estado"];
			$dados["parceiro_cep"] = $origem["cep"];
			$dados["parceiro_telefone"] = $origem["telefone"];
			
		}
		
		return $dados;
		
	}

	function buscaDadosProvedor() {
		
		$dados = array();
		
		$dados["provedor_nome"] = "Empresa Catarinense de Tecnologia em Telecomunicações Ltda";
		$dados["provedor_cnpj"] = "08.519.692/0001-90";
		$dados["provedor_telefone"] = "3028-0000";
		$dados["provedor_site"] = "www.oletelecom.com.br";
		
		return $dados;
		
	}

	function buscaContatosTelefones($cliente_id) {
		
		$lista = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ccl_id AS id,
				ccl_ddd AS contato_ddd,
				ccl_numero AS contato_numero,
				ccl_ramal AS contato_ramal,
				tab_clientes_contatos_telefones_tipos.ccf_id AS contato_telefone_tipo_id,
				tab_clientes_contatos_telefones_tipos.ccf_nome AS contato_telefone_tipo
			FROM
				tab_clientes_contatos_telefones
				INNER JOIN tab_clientes_contatos_telefones_tipos ON (tab_clientes_contatos_telefones.ccf_id = tab_clientes_contatos_telefones_tipos.ccf_id)
			WHERE
		        tab_clientes_contatos_telefones.cli_id = $cliente_id AND
				tab_clientes_contatos_telefones.ccf_id > 0
			ORDER BY
				contato_telefone_tipo,
				contato_ddd,
				contato_numero");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}
		
		return $lista;
		
	}

	function buscaContatosEmails($cliente_id) {
		
		$lista = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ccm_id AS id,
				CONCAT(ccm_usuario, '@', ccm_dominio) AS contato_email,
				tab_clientes_contatos_emails_tipos.cca_id AS contato_email_tipo_id,
				tab_clientes_contatos_emails_tipos.cca_nome AS contato_email_tipo
			FROM
				tab_clientes_contatos_emails
				INNER JOIN tab_clientes_contatos_emails_tipos ON (tab_clientes_contatos_emails.cca_id = tab_clientes_contatos_emails_tipos.cca_id)
			WHERE
				cli_id = $cliente_id
			ORDER BY
				contato_email_tipo,
				contato_email");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[] = $dados;
		}
		
		return $lista;
		
	}

	function buscaDadosClienteEndereco($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_condominios_blocos.cob_id AS bloco_id,
				tab_condominios_blocos.cob_nome AS bloco_nome,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_id,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento,
				IF(tab_clientes.cep_id IS NULL, IF(tab_condominios_blocos.cep_id IS NULL, tab_condominios.con_numero, tab_condominios_blocos.cob_numero), tab_clientes.cli_numero) AS numero,
				tab_clientes.cli_ponto_referencia AS ponto_referencia,
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_nome AS estado
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes.cli_id = $cliente_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["numero"] = $dados["numero"] ? $dados["numero"] : "";
			return $dados;
		}
		
	}

	function buscaDadosClienteEnderecoCobranca($cliente_id) {
		
		$query = "
			SELECT
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_condominios_blocos.cob_id AS bloco_id,
				tab_condominios_blocos.cob_nome AS bloco_nome,
				SUBSTRING(tab_clientes_cobranca_dados.ccd_complemento,1,1) AS complemento_id,
				SUBSTRING(tab_clientes_cobranca_dados.ccd_complemento,2) AS complemento,
				IF(tab_clientes_cobranca_dados.cep_id IS NULL, IF(tab_condominios_blocos.cep_id IS NULL, tab_condominios.con_numero, tab_condominios_blocos.cob_numero), tab_clientes_cobranca_dados.ccd_numero) AS numero,
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_nome AS estado
			FROM
				tab_clientes_cobranca_dados
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes_cobranca_dados.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes_cobranca_dados.cli_id = $cliente_id";
		
		$query = $this->bancodedados->query($query);
		
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["numero"] = $dados["numero"] ? $dados["numero"] : "";
			return $dados;
		} else {
			return $this->buscaDadosClienteEndereco($cliente_id);
		}
		
	}

	function buscaDadosCEP($cep) {
		
		$cep = preg_replace("/[^0-9]/", "", $cep);
		
		$query = "
			SELECT
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_nome AS estado
			FROM
				tab_ceps
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_ceps.cep_id = $cep";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function geraContratoPrincipal($cliente_id, $assinaturas, $descontos, $vigencia = 0, $versao = null, $parcelamento = 1, $parcelamento_forma = 3, $mudanca_endereco = false, $mudanca_titularidade = false) {

		$cliente = $this->buscaDadosCliente($cliente_id);

		$this->dados["contrato_versao"] = $versao ? $versao : CONTRATO_VERSAO;
		
		$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosProvedor());
		$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosInstalador($cliente["origem_id"]));
		
		$this->dados["documento_cobranca"] = "Boleto Bancário";
		$this->dados["forma_entrega"] = "Via e-mail";
				
		$dados_cliente = array();
		
		$dados_cliente["cliente_nome"] = $cliente["nome"];
		$dados_cliente["cliente_cpf_cnpj"] = $cliente["cpf_cnpj"];
		$dados_cliente["cliente_tipo"] = $cliente["tipo_id"];
		$dados_cliente["cliente_nascimento"] = $cliente["nascimento"];

		foreach ($cliente["telefones"] as $telefone) {
			$dados_cliente["cliente_telefone"][] = "({$telefone["contato_ddd"]}) {$telefone["contato_numero"]}".($telefone["contato_ramal"] ? " (Ramal {$telefone["contato_ramal"]}" : "");
			$dados_cliente["cliente_telefone_{$telefone["contato_telefone_tipo"]}"][] = "({$telefone["contato_ddd"]}) {$telefone["contato_numero"]}".($telefone["contato_ramal"] ? " (Ramal {$telefone["contato_ramal"]}" : "");

		}
		
		$dados_cliente["cliente_telefone_Residencial"] = implode(", ", $dados_cliente["cliente_telefone_Residencial"]);
		$dados_cliente["cliente_telefone_Comercial"] = implode(", ", $dados_cliente["cliente_telefone_Comercial"]);
		$dados_cliente["cliente_telefone_Celular"] = implode(", ", $dados_cliente["cliente_telefone_Celular"]);
		$dados_cliente["cliente_telefone_Fax"] = implode(", ", $dados_cliente["cliente_telefone_Fax"]);
		$dados_cliente["cliente_telefone_Recados"] = implode(", ", $dados_cliente["cliente_telefone_Recados"]);
		
		$dados_cliente["cliente_telefone"] = implode(" / ", $dados_cliente["cliente_telefone"]);
		
		foreach ($cliente["emails"] as $email) {
			$dados_cliente["cliente_emails"][] = $email["contato_email"];
		}
		
		$dados_cliente["cliente_emails"] = implode(", ", $dados_cliente["cliente_emails"]);

		$dados_cliente["cliente_cobranca_vencimento"] = $cliente["cobranca_vencimento"];
		$dados_cliente["cliente_cobranca_formato"] = $cliente["cobranca_formato_id"];
		
		if ($mudanca_titularidade) {
			$this->dados["cliente_antigo"] = $dados_cliente;
		} else {
			$this->dados = funcoes::mescla_arrays($this->dados, $dados_cliente);
		}
		
		$endereco = array();
		
		$endereco["cliente_endereco"] = array();
		$endereco["cliente_endereco"][] = $cliente["endereco"]["logradouro"];
		
		if ($cliente["endereco"]["numero"]) {
			$endereco["cliente_endereco"][] = $cliente["endereco"]["numero"];
		}
		
		$complemento = $this->formataComplemento($cliente["endereco"]["complemento"], $cliente["endereco"]["complemento_id"], $cliente["endereco"]["bloco_nome"]);
		
		if ($complemento) {
			$endereco["cliente_endereco"][] = $complemento;
		}
		
		$endereco["cliente_endereco"] = implode(", ", $endereco["cliente_endereco"]);
		
		$endereco["cliente_endereco_condominio_id"] = $cliente["endereco"]["condominio_id"];
		$endereco["cliente_endereco_condominio"] = $cliente["endereco"]["condominio_nome"];
		$endereco["cliente_endereco_bloco_id"] = $cliente["endereco"]["bloco_id"];
		$endereco["cliente_endereco_bloco"] = $cliente["endereco"]["bloco_nome"];
		$endereco["cliente_endereco_complemento_valor"] = $cliente["endereco"]["complemento"];
		$endereco["cliente_endereco_complemento_tipo"] = $cliente["endereco"]["complemento_id"];
		$endereco["cliente_endereco_complemento"] = $cliente["endereco"]["complemento"];
		$endereco["cliente_endereco_estado"] = $cliente["endereco"]["estado"];
		$endereco["cliente_endereco_cidade"] = $cliente["endereco"]["cidade"];
		$endereco["cliente_endereco_bairro"] = $cliente["endereco"]["bairro"];
		$endereco["cliente_endereco_logradouro"] = $cliente["endereco"]["logradouro"];
		$endereco["cliente_endereco_numero"] = $cliente["endereco"]["numero"];
		$endereco["cliente_endereco_ponto_referencia"] = $cliente["endereco"]["ponto_referencia"];
		$endereco["cliente_endereco_cep"] = $cliente["endereco"]["cep"];
		
		$endereco["cliente_endereco_cobranca"] = array();
		
		$endereco["cliente_endereco_cobranca"][] = $cliente["endereco_cobranca"]["logradouro"];
		if ($cliente["endereco"]["numero"]) {
			$endereco["cliente_endereco_cobranca"][] = $cliente["endereco_cobranca"]["numero"];
		}
		$complemento = $this->formataComplemento($cliente["endereco_cobranca"]["complemento"], $cliente["endereco_cobranca"]["complemento_id"], $cliente["endereco_cobranca"]["bloco_nome"]);
		if ($complemento) {
			$endereco["cliente_endereco_cobranca"][] = $complemento;
		}
		$endereco["cliente_endereco_cobranca"] = implode(", ", $endereco["cliente_endereco_cobranca"]);
		
		$endereco["cliente_endereco_cobranca_condominio_id"] = $cliente["endereco_cobranca"]["condominio_id"];
		$endereco["cliente_endereco_cobranca_condominio"] = $cliente["endereco_cobranca"]["condominio_nome"];
		$endereco["cliente_endereco_cobranca_bloco_id"] = $cliente["endereco_cobranca"]["bloco_id"];
		$endereco["cliente_endereco_cobranca_bloco"] = $cliente["endereco_cobranca"]["bloco_nome"];
		$endereco["cliente_endereco_cobranca_complemento_valor"] = $cliente["endereco_cobranca"]["complemento"];
		$endereco["cliente_endereco_cobranca_complemento_tipo"] = $cliente["endereco_cobranca"]["complemento_id"];
		$endereco["cliente_endereco_cobranca_complemento"] = $cliente["endereco_cobranca"]["complemento"];
		$endereco["cliente_endereco_cobranca_estado"] = $cliente["endereco_cobranca"]["estado"];
		$endereco["cliente_endereco_cobranca_cidade"] = $cliente["endereco_cobranca"]["cidade"];
		$endereco["cliente_endereco_cobranca_bairro"] = $cliente["endereco_cobranca"]["bairro"];
		$endereco["cliente_endereco_cobranca_logradouro"] = $cliente["endereco_cobranca"]["logradouro"];
		$endereco["cliente_endereco_cobranca_numero"] = $cliente["endereco_cobranca"]["numero"];
		$endereco["cliente_endereco_cobranca_cep"] = $cliente["endereco_cobranca"]["cep"];
		
		if ($mudanca_endereco) {
			$this->dados["endereco_antigo"] = $endereco;
		} else {
			$this->dados = funcoes::mescla_arrays($this->dados, $endereco);
		}
		
		$this->dados["contrato_tv_pacote_principal"] = "";
		$this->dados["contrato_tv_pacotes_adicionais"] = array();
		$this->dados["contrato_tv_pontos"] = 0;
		$this->dados["contrato_tv_dispositivos"] = 0;
		$this->dados["contrato_tv_gravacao"] = 0;
		$this->dados["contrato_tv_telas_vod"] = 0;
		
		foreach ($assinaturas as $assinatura) {
			
			$this->dados["sistema"] = $assinatura["sistema_id"] > $this->dados["sistema"] ? $assinatura["sistema_id"] : $this->dados["sistema"];
			
			$this->dados["contrato_tv_pontos"] += intval($assinatura["parametros"]["iptv_stb_quantidade"]);
			$this->dados["contrato_tv_dispositivos"] += intval($assinatura["parametros"]["iptv_aplicativos_quantidade"]);
			$this->dados["contrato_tv_gravacao"] += intval($assinatura["parametros"]["iptv_gravacao_horas"]);
			$this->dados["contrato_tv_telas_vod"] += intval($assinatura["parametros"]["vod_telas"]);
			
			if (!$assinatura["pai"]) {
				$this->dados["contrato_tv_pacote_principal"] = $assinatura["plano_nome"];
			} else if (count($assinatura["parametros"]["iptv_pacotes"]) > 0) {
				$this->dados["contrato_tv_pacotes_adicionais"][] = $assinatura["plano_nome"];
			}
			
		}

		$this->dados["contrato_tv_pacotes_adicionais"] = count($this->dados["contrato_tv_pacotes_adicionais"]) ? implode(", ", $this->dados["contrato_tv_pacotes_adicionais"]) : "--";

		if ($cliente["tipo_id"] == 1) {
			$this->dados["contrato_multa_atraso"] = CONTRATO_MULTA_ATRASO_PF;
			$this->dados["contrato_indice_reajuste"] = CONTRATO_INDICE_REAJUSTE_PF;
		} else {
			$this->dados["contrato_multa_atraso"] = CONTRATO_MULTA_ATRASO_PJ;
			$this->dados["contrato_indice_reajuste"] = CONTRATO_INDICE_REAJUSTE_PJ;
		}
		
		$this->dados["contrato_prazo_carencia"] = CONTRATO_PRAZO_CARENCIA;
		$this->dados["contrato_mes_reajuste"] = CONTRATO_MES_REAJUSTE;
		$this->dados["contrato_prazo_religamento"] = CONTRATO_PRAZO_RELIGAMENTO;
		$this->dados["contrato_taxa_visita_tecnica"] = CONTRATO_TAXA_VISITA_TECNICA;
		$this->dados["contrato_taxa_religamento"] = CONTRATO_TAXA_RELIGAMENTO;
		$this->dados["contrato_custo_hub"] = CONTRATO_CUSTO_HUB;
		$this->dados["contrato_custo_placa_de_rede"] = CONTRATO_CUSTO_PLACA_DE_REDE;
		
		$this->dados = funcoes::mescla_arrays($this->dados, $this->geraParametrosValores($assinaturas, $descontos, "", $vigencia, $parcelamento));
		
		$this->dados["parcelamento"] = $parcelamento && $this->dados["tabela_valor_final_instalacao"] ? $parcelamento : 0;
		$this->dados["parcelamento_forma"] = $parcelamento_forma ? $parcelamento_forma : 3;
		
		ksort($this->dados);
	
	}

	function geraContratoAditivoAlterarAssinaturas($contrato, $assinaturas, $descontos, $versao = null) {
		
		$this->dados["contrato_versao"] = $versao ? $versao : CONTRATO_VERSAO;
		
		if ($this->dados["contrato_versao"] >= 9) {
			
			$this->geraContratoPrincipal($contrato["cliente"], $assinaturas, $descontos, 0, $this->dados["contrato_versao"]);
			
		} else {
			
			$cliente = $this->buscaDadosCliente($contrato["cliente"]);
			
			$this->dados["cliente_nome"] = $cliente["nome"];
			$this->dados["cliente_cpf_cnpj"] = $cliente["cpf_cnpj"];
			$this->dados["cliente_tipo"] = $cliente["tipo_id"];
			
			if ($cliente["tipo_id"] == 1) {
				$this->dados["contrato_multa_atraso"] = CONTRATO_MULTA_ATRASO_PF;
				$this->dados["contrato_indice_reajuste"] = CONTRATO_INDICE_REAJUSTE_PF;
			} else {
				$this->dados["contrato_multa_atraso"] = CONTRATO_MULTA_ATRASO_PJ;
				$this->dados["contrato_indice_reajuste"] = CONTRATO_INDICE_REAJUSTE_PJ;
			}
			
			foreach ($cliente["telefones"] as $telefone) {
				$this->dados["cliente_telefone"][] = "({$telefone["contato_ddd"]}) {$telefone["contato_numero"]}".($telefone["contato_ramal"] ? " (Ramal {$telefone["contato_ramal"]}" : "");
			}

			$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosProvedor($cliente["origem_id"]));
			$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosInstalador($cliente["origem_id"]));

			$this->dados["cliente_telefone"] = implode(" / ", $this->dados["cliente_telefone"]);
			$this->dados["cliente_endereco_condominio"] = $cliente["endereco"]["condominio_nome"];
			$this->dados["cliente_endereco_bloco"] = $cliente["endereco"]["bloco_nome"];
			$this->dados["cliente_endereco_complemento"] = $cliente["endereco"]["complemento"];
			$this->dados["cliente_endereco_estado"] = $cliente["endereco"]["estado"];
			$this->dados["cliente_endereco_cidade"] = $cliente["endereco"]["cidade"];
			$this->dados["cliente_endereco_bairro"] = $cliente["endereco"]["bairro"];
			$this->dados["cliente_endereco_logradouro"] = $cliente["endereco"]["logradouro"];
			$this->dados["cliente_endereco_numero"] = $cliente["endereco"]["numero"];
			$this->dados["cliente_endereco_ponto_referencia"] = $cliente["endereco"]["ponto_referencia"];
			$this->dados["cliente_endereco_cep"] = $cliente["endereco"]["cep"];
			$this->dados["cliente_endereco_cobranca_condominio"] = $cliente["endereco_cobranca"]["condominio_nome"];
			$this->dados["cliente_endereco_cobranca_bloco"] = $cliente["endereco_cobranca"]["bloco_nome"];
			$this->dados["cliente_endereco_cobranca_complemento"] = $cliente["endereco_cobranca"]["complemento"];
			$this->dados["cliente_endereco_cobranca_estado"] = $cliente["endereco_cobranca"]["estado"];
			$this->dados["cliente_endereco_cobranca_cidade"] = $cliente["endereco_cobranca"]["cidade"];
			$this->dados["cliente_endereco_cobranca_bairro"] = $cliente["endereco_cobranca"]["bairro"];
			$this->dados["cliente_endereco_cobranca_logradouro"] = $cliente["endereco_cobranca"]["logradouro"];
			$this->dados["cliente_endereco_cobranca_numero"] = $cliente["endereco_cobranca"]["numero"];
			$this->dados["cliente_endereco_cobranca_cep"] = $cliente["endereco_cobranca"]["cep"];
			$this->dados["cliente_cobranca_vencimento"] = $cliente["cobranca_vencimento"];
			$this->dados["cliente_cobranca_formato"] = $cliente["cobranca_formato_id"];

			foreach ($assinaturas as $assinatura) {
				$this->dados["sistema"] = $assinatura["sistema_id"] > $this->dados["sistema"] ? $assinatura["sistema_id"] : $this->dados["sistema"];
			}
			
			$this->dados["contrato_prazo_carencia"] = CONTRATO_PRAZO_CARENCIA;
			$this->dados["contrato_mes_reajuste"] = CONTRATO_MES_REAJUSTE;
			$this->dados["contrato_prazo_religamento"] = CONTRATO_PRAZO_RELIGAMENTO;
			$this->dados["contrato_taxa_visita_tecnica"] = CONTRATO_TAXA_VISITA_TECNICA;
			$this->dados["contrato_taxa_religamento"] = CONTRATO_TAXA_RELIGAMENTO;
			$this->dados["contrato_custo_hub"] = CONTRATO_CUSTO_HUB;
			$this->dados["contrato_custo_placa_de_rede"] = CONTRATO_CUSTO_PLACA_DE_REDE;
			
			$this->dados = funcoes::mescla_arrays($this->dados, $this->geraParametrosValores($contrato["parametros"]["contrato_assinaturas_final"] ? $contrato["parametros"]["contrato_assinaturas_final"] : $contrato["parametros"]["contrato_assinaturas"], $contrato["parametros"]["contrato_descontos_final"] ? $contrato["parametros"]["contrato_descontos_final"] : $contrato["parametros"]["contrato_descontos"], "_inicial"));
			$this->dados = funcoes::mescla_arrays($this->dados, $this->geraParametrosValores($assinaturas, $descontos, "_final"));
			
			$parametros = array();
			
			if ($this->dados["contrato_assinaturas_final"]) {
				foreach ($this->dados["contrato_assinaturas_final"] as $id => $assinatura) {
					if (!$this->dados["contrato_assinaturas_inicial"][$id]) {
						$parametros["tabela_valor_original_instalacao"] += $assinatura["instalacao"];
						$parametros["tabela_valor_original_mensalidade"] += $assinatura["mensalidade"];
						$parametros["contrato_servico_pontos_quantidade"] += $assinatura["parametros"]["pontos_quantidade"];
						$parametros["contrato_servico_emails_quantidade"] += $assinatura["parametros"]["emails_quantidade"];
						$parametros["contrato_servico_hospedagens_quantidade"] += $assinatura["parametros"]["hospedagens_quantidade"];
						$parametros["contrato_servico_basesdedados_quantidade"] += $assinatura["parametros"]["basesdedados_quantidade"];
						$parametros["contrato_assinaturas"][$assinatura["id"]] = $assinatura;
					}
				}
			}

			if ($this->dados["contrato_descontos_final"]) {
				foreach ($this->dados["contrato_descontos_final"] as $id => $desconto) {
					if (!$this->dados["contrato_descontos_inicial"][$id]) {
						if (in_array($desconto["desconto_tipo_id"], array(1, 3))) {
							$parametros["tabela_valor_desconto_beneficio_instalacao"] += ($desconto["instalacao"] > 1) ? $desconto["instalacao"] : $desconto["instalacao"] * $parametros["contrato_assinaturas"][$desconto["assinatura"]]["instalacao"];
							$parametros["tabela_valor_desconto_beneficio_mensalidade"] += ($desconto["mensalidade"] > 1) ? $desconto["mensalidade"] : $desconto["mensalidade"] * $parametros["contrato_assinaturas"][$desconto["assinatura"]]["mensalidade"];
						} elseif ($desconto["desconto_tipo_id"] == 2) {
							$parametros["tabela_valor_desconto_fidelidade_instalacao"] += ($desconto["instalacao"] > 1) ? $desconto["instalacao"] : $desconto["instalacao"] * $parametros["contrato_assinaturas"][$desconto["assinatura"]]["instalacao"];
							$parametros["tabela_valor_desconto_fidelidade_mensalidade"] += ($desconto["mensalidade"] > 1) ? $desconto["mensalidade"] : $desconto["mensalidade"] * $parametros["contrato_assinaturas"][$desconto["assinatura"]]["mensalidade"];
						}
						$parametros["contrato_descontos"][$desconto["id"]] = $desconto;
					}
				}
			}

			$parametros["contrato_multa_rescisao"] = $this->dados["contrato_multa_rescisao_inicial"] + $this->calculaMultaRescisao($parametros["contrato_descontos"], $parametros);
			$parametros["contrato_prazo_vigencia"] = !$parametros["contrato_multa_rescisao"] ? 0 : round($parametros["contrato_multa_rescisao"] / 1000);

			$parametros["tabela_valor_final_instalacao"] = $parametros["tabela_valor_original_instalacao"] - $parametros["tabela_valor_desconto_beneficio_instalacao"] - $parametros["tabela_valor_desconto_fidelidade_instalacao"];
			if ($parametros["tabela_valor_final_instalacao"] < 0) $parametros["tabela_valor_final_instalacao"] = 0;
			$parametros["tabela_valor_final_mensalidade"] = $parametros["tabela_valor_original_mensalidade"] - $parametros["tabela_valor_desconto_beneficio_mensalidade"] - $parametros["tabela_valor_desconto_fidelidade_mensalidade"];
			if ($parametros["tabela_valor_final_mensalidade"] < 0) $parametros["tabela_valor_final_mensalidade"] = 0;
			
			$this->dados = funcoes::mescla_arrays($this->dados, $parametros);
			
			ksort($this->dados);
			
		}
		
	}

	function geraContratoAditivoMudancaEndereco($cliente_id, $dados, $assinaturas, $descontos, $parametros_antigos = null) {
		
		$this->dados["contrato_versao"] = $parametros_antigos ? $parametros_antigos["contrato_versao"] : CONTRATO_VERSAO;
		
		if ($this->dados["contrato_versao"] >= 9) {
			
			$this->geraContratoPrincipal($cliente_id, $assinaturas, $descontos, 0, $this->dados["contrato_versao"], 1, true);
			
			if (!$parametros_antigos) {
				
				$dados_bloco = $dados["bloco"] ? $this->buscaDadosBloco($dados["bloco"]) : null;
				$dados_bloco_cobranca = $dados["bloco"] == $dados["cobranca_bloco"] ? $dados_bloco : $this->buscaDadosBloco($dados["cobranca_bloco"]);
				
				if ($dados["bloco"]) {
					$this->dados["cliente_endereco_condominio_id"] = $dados_bloco["condominio_id"];
					$this->dados["cliente_endereco_condominio"] = $dados_bloco["condominio_nome"];
					$this->dados["cliente_endereco_bloco_id"] = $dados_bloco["bloco_id"];
					$this->dados["cliente_endereco_bloco"] = $dados_bloco["bloco_nome"];
					$this->dados["cliente_endereco_numero"] = $dados_bloco["numero"];
					$this->dados["cliente_endereco_cep"] = $dados_bloco["cep"];
					$this->dados["cliente_endereco_logradouro"] = $dados_bloco["logradouro"];
					$this->dados["cliente_endereco_bairro"] = $dados_bloco["bairro"];
					$this->dados["cliente_endereco_cidade"] = $dados_bloco["cidade"];
					$this->dados["cliente_endereco_estado"] = $dados_bloco["estado"];
				} else {
					$dados_cep = $this->buscaDadosCEP($dados["cep"]);
					$this->dados["cliente_endereco_numero"] = $dados["numero"];
					$this->dados["cliente_endereco_cep"] = $dados["cep"];
					$this->dados["cliente_endereco_logradouro"] = $dados_cep["logradouro"];
					$this->dados["cliente_endereco_bairro"] = $dados_cep["bairro"];
					$this->dados["cliente_endereco_cidade"] = $dados_cep["cidade"];
					$this->dados["cliente_endereco_estado"] = $dados_cep["estado"];
				}
				
				$this->dados["cliente_endereco_complemento_valor"] = $dados["complemento_valor"];
				$this->dados["cliente_endereco_complemento_tipo"] = $dados["complemento_tipo"];
				$this->dados["cliente_endereco_complemento"] = $dados["complemento_valor"];

				if ($dados["mesmo_endereco"]) {
					
					$this->dados["cliente_endereco_cobranca_condominio_id"] = $this->dados["cliente_endereco_condominio_id"];
					$this->dados["cliente_endereco_cobranca_condominio"] = $this->dados["cliente_endereco_condominio"];
					$this->dados["cliente_endereco_cobranca_bloco_id"] = $this->dados["cliente_endereco_bloco_id"];
					$this->dados["cliente_endereco_cobranca_bloco"] = $this->dados["cliente_endereco_bloco"];
					$this->dados["cliente_endereco_cobranca_numero"] = $this->dados["cliente_endereco_numero"];
					$this->dados["cliente_endereco_cobranca_cep"] = $this->dados["cliente_endereco_cep"];
					$this->dados["cliente_endereco_cobranca_logradouro"] = $this->dados["cliente_endereco_logradouro"];
					$this->dados["cliente_endereco_cobranca_bairro"] = $this->dados["cliente_endereco_bairro"];
					$this->dados["cliente_endereco_cobranca_cidade"] = $this->dados["cliente_endereco_cidade"];
					$this->dados["cliente_endereco_cobranca_estado"] = $this->dados["cliente_endereco_estado"];
					$this->dados["cliente_endereco_cobranca_complemento_valor"] = $this->dados["cliente_endereco_complemento_valor"];
					$this->dados["cliente_endereco_cobranca_complemento_tipo"] = $this->dados["cliente_endereco_complemento_tipo"];
					$this->dados["cliente_endereco_cobranca_complemento"] = $this->dados["cliente_endereco_complemento"];
					
				} else {
					
					$this->dados["cliente_endereco_cobranca_condominio_id"] = $dados_bloco_cobranca["condominio_id"];
					$this->dados["cliente_endereco_cobranca_condominio"] = $dados_bloco_cobranca["condominio_nome"];
					$this->dados["cliente_endereco_cobranca_bloco_id"] = $dados_bloco_cobranca["bloco_id"];
					$this->dados["cliente_endereco_cobranca_bloco"] = $dados_bloco_cobranca["bloco_nome"];
					
					if ($dados["cobranca_bloco"]) {
						$this->dados["cliente_endereco_cobranca_numero"] = $dados_bloco_cobranca["numero"];
						$this->dados["cliente_endereco_cobranca_cep"] = $dados_bloco_cobranca["cep"];
						$this->dados["cliente_endereco_cobranca_logradouro"] = $dados_bloco_cobranca["logradouro"];
						$this->dados["cliente_endereco_cobranca_bairro"] = $dados_bloco_cobranca["bairro"];
						$this->dados["cliente_endereco_cobranca_cidade"] = $dados_bloco_cobranca["cidade"];
						$this->dados["cliente_endereco_cobranca_estado"] = $dados_bloco_cobranca["estado"];
					} else {
						$dados_cep = $this->buscaDadosCEP($dados["cobranca_cep"]);
						$this->dados["cliente_endereco_cobranca_numero"] = $dados["cobranca_numero"];
						$this->dados["cliente_endereco_cobranca_cep"] = $dados["cobranca_cep"];
						$this->dados["cliente_endereco_cobranca_logradouro"] = $dados_cep["logradouro"];
						$this->dados["cliente_endereco_cobranca_bairro"] = $dados_cep["bairro"];
						$this->dados["cliente_endereco_cobranca_cidade"] = $dados_cep["cidade"];
						$this->dados["cliente_endereco_cobranca_estado"] = $dados_cep["estado"];
					}
					
					$this->dados["cliente_endereco_cobranca_complemento_valor"] = $dados["cobranca_complemento_valor"];
					$this->dados["cliente_endereco_cobranca_complemento_tipo"] = $dados["cobranca_complemento_tipo"];
					$this->dados["cliente_endereco_cobranca_complemento"] = $dados["cobranca_complemento_valor"];
					
				}
				
				$this->dados["contrato_valor_instalacao"] = intval(preg_replace("/[^0-9]/", "", $dados["valor_instalacao"]), 10);
				$this->dados["tabela_valor_original_instalacao"] = intval(preg_replace("/[^0-9]/", "", $dados["valor_instalacao"]), 10);
				$this->dados["tabela_valor_final_instalacao"] = intval(preg_replace("/[^0-9]/", "", $dados["valor_instalacao"]), 10);

				$this->dados["parcelamento"] = intval($dados["parcelamento"], 10);
			
			} else {
				
				$this->dados["cliente_endereco_condominio_id"] = $parametros_antigos["cliente_endereco_condominio_id"];
				$this->dados["cliente_endereco_condominio"] = $parametros_antigos["cliente_endereco_condominio"];
				$this->dados["cliente_endereco_bloco_id"] = $parametros_antigos["cliente_endereco_bloco_id"];
				$this->dados["cliente_endereco_bloco"] = $parametros_antigos["cliente_endereco_bloco"];
				$this->dados["cliente_endereco_numero"] = $parametros_antigos["cliente_endereco_numero"];
				$this->dados["cliente_endereco_cep"] = $parametros_antigos["cliente_endereco_cep"];
				$this->dados["cliente_endereco_logradouro"] = $parametros_antigos["cliente_endereco_logradouro"];
				$this->dados["cliente_endereco_bairro"] = $parametros_antigos["cliente_endereco_bairro"];
				$this->dados["cliente_endereco_cidade"] = $parametros_antigos["cliente_endereco_cidade"];
				$this->dados["cliente_endereco_estado"] = $parametros_antigos["cliente_endereco_estado"];
				$this->dados["cliente_endereco_complemento_valor"] = $parametros_antigos["cliente_endereco_complemento_valor"];
				$this->dados["cliente_endereco_complemento_tipo"] = $parametros_antigos["cliente_endereco_complemento_tipo"];
				$this->dados["cliente_endereco_complemento"] = $parametros_antigos["cliente_endereco_complemento"];
				$this->dados["cliente_endereco_cobranca_condominio_id"] = $parametros_antigos["cliente_endereco_cobranca_condominio_id"];
				$this->dados["cliente_endereco_cobranca_condominio"] = $parametros_antigos["cliente_endereco_cobranca_condominio"];
				$this->dados["cliente_endereco_cobranca_bloco_id"] = $parametros_antigos["cliente_endereco_cobranca_bloco_id"];
				$this->dados["cliente_endereco_cobranca_bloco"] = $parametros_antigos["cliente_endereco_cobranca_bloco"];
				$this->dados["cliente_endereco_cobranca_numero"] = $parametros_antigos["cliente_endereco_cobranca_numero"];
				$this->dados["cliente_endereco_cobranca_cep"] = $parametros_antigos["cliente_endereco_cobranca_cep"];
				$this->dados["cliente_endereco_cobranca_logradouro"] = $parametros_antigos["cliente_endereco_cobranca_logradouro"];
				$this->dados["cliente_endereco_cobranca_bairro"] = $parametros_antigos["cliente_endereco_cobranca_bairro"];
				$this->dados["cliente_endereco_cobranca_cidade"] = $parametros_antigos["cliente_endereco_cobranca_cidade"];
				$this->dados["cliente_endereco_cobranca_estado"] = $parametros_antigos["cliente_endereco_cobranca_estado"];
				$this->dados["cliente_endereco_cobranca_complemento_valor"] = $parametros_antigos["cliente_endereco_cobranca_complemento_valor"];
				$this->dados["cliente_endereco_cobranca_complemento_tipo"] = $parametros_antigos["cliente_endereco_cobranca_complemento_tipo"];
				$this->dados["cliente_endereco_cobranca_complemento"] = $parametros_antigos["cliente_endereco_cobranca_complemento"];
				$this->dados["tabela_valor_original_instalacao"] = $parametros_antigos["tabela_valor_original_instalacao"];
				$this->dados["tabela_valor_final_instalacao"] = $parametros_antigos["tabela_valor_final_instalacao"];
				$this->dados["contrato_valor_instalacao"] = $parametros_antigos["contrato_valor_instalacao"];
				$this->dados["parcelamento"] = $parametros_antigos["parcelamento"];
				
			}
			
			$this->dados["contrato_multa_rescisao"] = 0;
			$this->dados["contrato_prazo_vigencia"] = 0;
			
			ksort($this->dados);
			
		} else {
			
			$cliente = $this->buscaDadosCliente($cliente_id);
			$dados_bloco = $this->buscaDadosBloco($dados["bloco"]);
			$dados_bloco_cobranca = $this->buscaDadosBloco($dados["cobranca_bloco"]);
			
			if ($parametros_antigos) {
				
				unset($parametros_antigos["inicial"]);
				
				$this->dados = $parametros_antigos;
				$this->dados["contrato_valor_instalacao"] = $dados["instalacao"];
				
			} else {

				$this->dados["contrato_versao"] = CONTRATO_VERSAO;
				$this->dados["contrato_custo_hub"] = CONTRATO_CUSTO_HUB;
				$this->dados["contrato_custo_placa_de_rede"] = CONTRATO_CUSTO_PLACA_DE_REDE;
				$this->dados["contrato_valor_instalacao"] = $dados["instalacao"];

				$this->dados["final"]["condominio_id"] = $dados_bloco["condominio_id"];
				$this->dados["final"]["condominio_nome"] = $dados_bloco["condominio_nome"];
				$this->dados["final"]["bloco_id"] = $dados_bloco["bloco_id"];
				$this->dados["final"]["bloco_nome"] = $dados_bloco["bloco_nome"];
				
				if ($dados["bloco"]) {
					$this->dados["final"]["numero"] = $dados_bloco["numero"];
					$this->dados["final"]["cep"] = $dados_bloco["cep"];
					$this->dados["final"]["logradouro"] = $dados_bloco["logradouro"];
					$this->dados["final"]["bairro"] = $dados_bloco["bairro"];
					$this->dados["final"]["cidade"] = $dados_bloco["cidade"];
					$this->dados["final"]["estado"] = $dados_bloco["estado"];
				} else {
					$dados_cep = $this->buscaDadosCEP($dados["cep"]);
					$this->dados["final"]["numero"] = $dados["numero"];
					$this->dados["final"]["cep"] = $dados["cep"];
					$this->dados["final"]["logradouro"] = $dados_cep["logradouro"];
					$this->dados["final"]["bairro"] = $dados_cep["bairro"];
					$this->dados["final"]["cidade"] = $dados_cep["cidade"];
					$this->dados["final"]["estado"] = $dados_cep["estado"];
				}
				
				$this->dados["final"]["complemento_valor"] = $dados["complemento_valor"];
				$this->dados["final"]["complemento_tipo"] = $dados["complemento_tipo"];
				$this->dados["final"]["complemento"] = $this->corrigeComplemento($dados["complemento_valor"], $dados["complemento_tipo"]);

				if ($dados["mesmo_endereco"]) {
					
					$this->dados["final"]["cobranca_condominio_id"] = $dados_bloco["condominio_id"];
					$this->dados["final"]["cobranca_condominio_nome"] = $dados_bloco["condominio_nome"];
					$this->dados["final"]["cobranca_bloco_id"] = $dados_bloco["bloco_id"];
					$this->dados["final"]["cobranca_bloco_nome"] = $dados_bloco["bloco_nome"];
					
					if ($dados["bloco"]) {
						$this->dados["final"]["cobranca_numero"] = $dados_bloco["numero"];
						$this->dados["final"]["cobranca_cep"] = $dados_bloco["cep"];
						$this->dados["final"]["cobranca_logradouro"] = $dados_bloco["logradouro"];
						$this->dados["final"]["cobranca_bairro"] = $dados_bloco["bairro"];
						$this->dados["final"]["cobranca_cidade"] = $dados_bloco["cidade"];
						$this->dados["final"]["cobranca_estado"] = $dados_bloco["estado"];
					} else {
						$this->dados["final"]["cobranca_numero"] = $dados["numero"];
						$this->dados["final"]["cobranca_cep"] = $dados["cep"];
						$this->dados["final"]["cobranca_logradouro"] = $dados["logradouro"];
						$this->dados["final"]["cobranca_bairro"] = $dados["bairro"];
						$this->dados["final"]["cobranca_cidade"] = $dados["cidade"];
						$this->dados["final"]["cobranca_estado"] = $dados["estado"];
					}
					
					$this->dados["final"]["cobranca_complemento_valor"] = $dados["complemento_valor"];
					$this->dados["final"]["cobranca_complemento_tipo"] = $dados["complemento_tipo"];
					$this->dados["final"]["cobranca_complemento"] = $this->corrigeComplemento($dados["complemento_valor"], $dados["complemento_tipo"]);
					
				} else {
					
					$this->dados["final"]["cobranca_condominio_id"] = $dados_bloco_cobranca["condominio_id"];
					$this->dados["final"]["cobranca_condominio_nome"] = $dados_bloco_cobranca["condominio_nome"];
					$this->dados["final"]["cobranca_bloco_id"] = $dados_bloco_cobranca["bloco_id"];
					$this->dados["final"]["cobranca_bloco_nome"] = $dados_bloco_cobranca["bloco_nome"];
					
					if ($dados["cobranca_bloco"]) {
						$this->dados["final"]["cobranca_numero"] = $dados_bloco_cobranca["numero"];
						$this->dados["final"]["cobranca_cep"] = $dados_bloco_cobranca["cep"];
						$this->dados["final"]["cobranca_logradouro"] = $dados_bloco_cobranca["logradouro"];
						$this->dados["final"]["cobranca_bairro"] = $dados_bloco_cobranca["bairro"];
						$this->dados["final"]["cobranca_cidade"] = $dados_bloco_cobranca["cidade"];
						$this->dados["final"]["cobranca_estado"] = $dados_bloco_cobranca["estado"];
					} else {
						$dados_cep = $this->buscaDadosCEP($dados["cobranca_cep"]);
						$this->dados["final"]["cobranca_numero"] = $dados["cobranca_numero"];
						$this->dados["final"]["cobranca_cep"] = $dados["cobranca_cep"];
						$this->dados["final"]["cobranca_logradouro"] = $dados_cep["logradouro"];
						$this->dados["final"]["cobranca_bairro"] = $dados_cep["bairro"];
						$this->dados["final"]["cobranca_cidade"] = $dados_cep["cidade"];
						$this->dados["final"]["cobranca_estado"] = $dados_cep["estado"];
					}
					
					$this->dados["final"]["cobranca_complemento_valor"] = $dados["cobranca_complemento_valor"];
					$this->dados["final"]["cobranca_complemento_tipo"] = $dados["cobranca_complemento_tipo"];
					$this->dados["final"]["cobranca_complemento"] = $this->corrigeComplemento($dados["cobranca_complemento_valor"], $dados["cobranca_complemento_tipo"]);
					
				}
				
			}
			
			$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosProvedor($cliente["origem_id"]));			
			$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosInstalador($cliente["origem_id"]));
		
			$this->dados["cliente_nome"] = $cliente["nome"];
			$this->dados["cliente_cpf_cnpj"] = $cliente["cpf_cnpj"];
			$this->dados["cliente_tipo"] = $cliente["tipo_id"];

			$telefones = array();
			foreach ($cliente["telefones"] as $telefone) {
				$telefones[] = "({$telefone["contato_ddd"]}) {$telefone["contato_numero"]}".($telefone["contato_ramal"] ? " (Ramal {$telefone["contato_ramal"]}" : "");
			}
			$this->dados["cliente_telefone"] = @implode(" / ", $telefones);
			
			$this->dados["inicial"] = $cliente["endereco"];
			$this->dados["inicial"]["endereco_cobranca"] = $cliente["endereco_cobranca"];
			
			ksort($this->dados);
		
		}
		
	}

	function geraContratoAditivoMudancaTitularidade($cliente_id, $dados, $assinaturas, $descontos, $parametros_antigos = null) {
			
		$this->dados["contrato_versao"] = $parametros_antigos ? $parametros_antigos["contrato_versao"] : CONTRATO_VERSAO;
		
		if ($this->dados["contrato_versao"] >= 9) {
			
			$this->geraContratoPrincipal($cliente_id, $assinaturas, $descontos, 0, $this->dados["contrato_versao"], 1, false, true);
			
			$cliente = $this->buscaDadosCliente($cliente_id);
			
			if (!$parametros_antigos) {

				$this->dados["cliente_nome"] = $dados["nome"];
				$this->dados["cliente_cpf_cnpj"] = $dados["cpf_cnpj"];
				$this->dados["cliente_tipo"] = $dados["tipo"];
				$this->dados["cliente_nascimento"] = $dados["nascimento"];
				$this->dados["cliente_contato"] = $dados["contato"];
				$this->dados["cliente_emails"] = unserialize(stripslashes($dados["lista_emails"]));
				$this->dados["cliente_telefones"] = unserialize(stripslashes($dados["lista_telefones"]));
				
				foreach ($this->dados["cliente_telefones"] as $telefone) {
					$this->dados["cliente_telefone"][] = "({$telefone["contato_ddd"]}) {$telefone["contato_numero"]}".($telefone["contato_ramal"] ? " (Ramal {$telefone["contato_ramal"]}" : "");
				}
				
				$this->dados["cliente_telefone"] = implode(" / ", $this->dados["cliente_telefone"]);
				$this->dados["cliente_cobranca_vencimento"] = $cliente["cobranca_vencimento"];
				$this->dados["cliente_cobranca_formato"] = $cliente["cobranca_formato_id"];

				if ($dados["mesmo_endereco"]) {
					
					$this->dados["final"]["endereco_cobranca"] = $cliente["endereco_cobranca"];
					
				} else {
					
					$dados_bloco_cobranca = $this->buscaDadosBloco($dados["cobranca_bloco"]);
					
					$this->dados["final"]["endereco_cobranca"]["condominio_id"] = $dados_bloco_cobranca["condominio_id"];
					$this->dados["final"]["endereco_cobranca"]["condominio_nome"] = $dados_bloco_cobranca["condominio_nome"];
					$this->dados["final"]["endereco_cobranca"]["bloco_id"] = $dados_bloco_cobranca["bloco_id"];
					$this->dados["final"]["endereco_cobranca"]["bloco_nome"] = $dados_bloco_cobranca["bloco_nome"];
					$this->dados["final"]["endereco_cobranca"]["complemento_id"] = $dados["cobranca_complemento_tipo"];
					$this->dados["final"]["endereco_cobranca"]["complemento"] = $dados["cobranca_complemento_valor"];
					
					if ($dados["bloco"]) {
						$this->dados["final"]["endereco_cobranca"]["numero"] = $dados_bloco_cobranca["numero"];
						$this->dados["final"]["endereco_cobranca"]["cep"] = $dados_bloco_cobranca["cep"];
						$this->dados["final"]["endereco_cobranca"]["logradouro"] = $dados_bloco_cobranca["logradouro"];
						$this->dados["final"]["endereco_cobranca"]["bairro"] = $dados_bloco_cobranca["bairro"];
						$this->dados["final"]["endereco_cobranca"]["cidade"] = $dados_bloco_cobranca["cidade"];
						$this->dados["final"]["endereco_cobranca"]["estado"] = $dados_bloco_cobranca["estado"];
					} else {
						$dados_cep = $this->buscaDadosCEP($dados["cobranca_cep"]);
						$this->dados["final"]["endereco_cobranca"]["numero"] = $dados["cobranca_numero"];
						$this->dados["final"]["endereco_cobranca"]["cep"] = $dados["cobranca_cep"];
						$this->dados["final"]["endereco_cobranca"]["logradouro"] = $dados_cep["logradouro"];
						$this->dados["final"]["endereco_cobranca"]["bairro"] = $dados_cep["bairro"];
						$this->dados["final"]["endereco_cobranca"]["cidade"] = $dados_cep["cidade"];
						$this->dados["final"]["endereco_cobranca"]["estado"] = $dados_cep["estado"];
					}
					
				}
				
			} else {
				
				$this->dados["cliente_nome"] = $parametros_antigos["cliente_nome"];
				$this->dados["cliente_cpf_cnpj"] = $parametros_antigos["cliente_cpf_cnpj"];
				$this->dados["cliente_tipo"] = $parametros_antigos["cliente_tipo"];
				$this->dados["cliente_nascimento"] = $parametros_antigos["cliente_nascimento"];
				$this->dados["cliente_contato"] = $parametros_antigos["cliente_contato"];
				$this->dados["cliente_emails"] = $parametros_antigos["cliente_emails"];
				$this->dados["cliente_telefones"] = $parametros_antigos["cliente_telefones"];
				$this->dados["cliente_telefone"] = $parametros_antigos["cliente_telefone"];
				$this->dados["cliente_cobranca_vencimento"] = $cliente["cobranca_vencimento"];
				$this->dados["cliente_cobranca_formato"] = $cliente["cobranca_formato_id"];

				$this->dados["final"]["endereco_cobranca"] = $parametros_antigos["final"]["endereco_cobranca"];
				
			}
			
			$this->dados["inicial"]["endereco_cobranca"] = $cliente["endereco_cobranca"];

			$this->dados["contrato_valor_instalacao"] = 0;
			$this->dados["tabela_valor_original_instalacao"] = 0;
			$this->dados["tabela_valor_final_instalacao"] = 0;

			ksort($this->dados);
			
		} else {
			
			$cliente = $this->buscaDadosCliente($cliente_id);
			
			if ($parametros_antigos) {

				unset($parametros_antigos["inicial"]);
				
				$this->dados = $parametros_antigos;
				
			} else {
				
				$this->dados["contrato_versao"] = CONTRATO_VERSAO;
				$this->dados["contrato_custo_hub"] = CONTRATO_CUSTO_HUB;
				$this->dados["contrato_custo_placa_de_rede"] = CONTRATO_CUSTO_PLACA_DE_REDE;

				$this->dados["final"]["nome"] = $this->corrigeNome($dados["nome"]);
				$this->dados["final"]["tipo"] = $dados["tipo"];
				$this->dados["final"]["cpf_cnpj"] = $dados["cpf_cnpj"];
				$this->dados["final"]["nascimento"] = $dados["nascimento"];
				$this->dados["final"]["contato"] = $dados["contato"];
				$this->dados["final"]["emails"] = unserialize(stripslashes($dados["lista_emails"]));
				$this->dados["final"]["telefones"] =@unserialize(stripslashes($dados["lista_telefones"]));

				if ($dados["mesmo_endereco"]) {
					
					$this->dados["final"]["endereco_cobranca"] = $cliente["endereco_cobranca"];
					
				} else {
					
					$dados_bloco_cobranca = $this->buscaDadosBloco($dados["cobranca_bloco"]);
					
					$this->dados["final"]["endereco_cobranca"]["condominio_id"] = $dados_bloco_cobranca["condominio_id"];
					$this->dados["final"]["endereco_cobranca"]["condominio_nome"] = $dados_bloco_cobranca["condominio_nome"];
					$this->dados["final"]["endereco_cobranca"]["bloco_id"] = $dados_bloco_cobranca["bloco_id"];
					$this->dados["final"]["endereco_cobranca"]["bloco_nome"] = $dados_bloco_cobranca["bloco_nome"];
					$this->dados["final"]["endereco_cobranca"]["complemento_id"] = $dados["cobranca_complemento_tipo"];
					$this->dados["final"]["endereco_cobranca"]["complemento"] = $dados["cobranca_complemento_valor"];
					
					if ($dados["bloco"]) {
						$this->dados["final"]["endereco_cobranca"]["numero"] = $dados_bloco_cobranca["numero"];
						$this->dados["final"]["endereco_cobranca"]["cep"] = $dados_bloco_cobranca["cep"];
						$this->dados["final"]["endereco_cobranca"]["logradouro"] = $dados_bloco_cobranca["logradouro"];
						$this->dados["final"]["endereco_cobranca"]["bairro"] = $dados_bloco_cobranca["bairro"];
						$this->dados["final"]["endereco_cobranca"]["cidade"] = $dados_bloco_cobranca["cidade"];
						$this->dados["final"]["endereco_cobranca"]["estado"] = $dados_bloco_cobranca["estado"];
					} else {
						$dados_cep = $this->buscaDadosCEP($dados["cobranca_cep"]);
						$this->dados["final"]["endereco_cobranca"]["numero"] = $dados["cobranca_numero"];
						$this->dados["final"]["endereco_cobranca"]["cep"] = $dados["cobranca_cep"];
						$this->dados["final"]["endereco_cobranca"]["logradouro"] = $dados_cep["logradouro"];
						$this->dados["final"]["endereco_cobranca"]["bairro"] = $dados_cep["bairro"];
						$this->dados["final"]["endereco_cobranca"]["cidade"] = $dados_cep["cidade"];
						$this->dados["final"]["endereco_cobranca"]["estado"] = $dados_cep["estado"];
					}
					
				}
				
			}
			
			$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosProvedor($cliente["origem_id"]));			
			$this->dados = funcoes::mescla_arrays($this->dados, $this->buscaDadosInstalador($cliente["origem_id"]));

			$this->dados["endereco"] = $cliente["endereco"];
			$this->dados["inicial"]["nome"] = $cliente["nome"];
			$this->dados["inicial"]["tipo"] = $cliente["tipo_id"];
			$this->dados["inicial"]["cpf_cnpj"] = $cliente["cpf_cnpj"];
			$this->dados["inicial"]["nascimento"] = $dados["nascimento"];
			$this->dados["inicial"]["contato"] = $dados["contato"];

			$this->dados["inicial"]["emails"] = $cliente["emails"];
			$this->dados["inicial"]["telefones"] = $cliente["telefones"];

			$this->dados["inicial"]["endereco_cobranca"] = $cliente["endereco_cobranca"];

			ksort($this->dados);
			
		}
		
	}

	function corrigeComplemento($valor, $tipo) {
		
		if ($valor) {
			if ($tipo != "o") $valor = intval($valor);
			$complemento = $tipo.$valor;
		}
		
		return $complemento;
		
	}

	function buscaDadosBloco($bloco_id) {
		
		if ($bloco_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					tab_condominios_blocos.cob_id AS bloco_id,
					tab_condominios_blocos.cob_nome AS bloco_nome,
					tab_condominios.con_id AS condominio_id,
					tab_condominios.con_nome AS condominio_nome,
					INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep,
					IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cob_numero, tab_condominios.con_numero) AS numero,
					tab_logradouros.log_nome AS logradouro,
					tab_bairros.bai_nome AS bairro,
					tab_cidades.cid_nome AS cidade,
					tab_estados.est_nome AS estado
				FROM
					tab_condominios
					INNER JOIN tab_condominios_blocos ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
					INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_condominios.cep_id))
					INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
					INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
					INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
					INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
				WHERE
					tab_condominios_blocos.cob_id = $bloco_id");
			
			if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$dados["numero"] = $dados["numero"] ? $dados["numero"] : "";
				return $dados;
			}
			
		}
		
	}

	function geraParametrosValores($assinaturas, $descontos, $sufixo = "", $vigencia = 0, $parcelamento = 1) {

		$parametros = array();
		
		$valor_multa = 0;
		$valor_vigencia = 0;
		
		foreach ($assinaturas as $assinatura) {
			
			$parametros["tabela_valor_original_instalacao$sufixo"] += $assinatura["instalacao"];
			$parametros["tabela_valor_original_mensalidade$sufixo"] += $assinatura["mensalidade"];
			$parametros["contrato_servico_pontos_quantidade$sufixo"] += $assinatura["parametros"]["pontos_quantidade"];
			$parametros["contrato_servico_emails_quantidade$sufixo"] += $assinatura["parametros"]["emails_quantidade"];
			$parametros["contrato_servico_hospedagens_quantidade$sufixo"] += $assinatura["parametros"]["hospedagens_quantidade"];
			$parametros["contrato_servico_basesdedados_quantidade$sufixo"] += $assinatura["parametros"]["basesdedados_quantidade"];
			
			if ($assinatura["plano_tipo_id"] == 1) {
				$parametros["contrato_servico_pontos_velocidade_download$sufixo"] = $assinatura["parametros"]["pontos_velocidade_download"];
				$parametros["contrato_servico_pontos_velocidade_upload$sufixo"] = $assinatura["parametros"]["pontos_velocidade_upload"];
				$parametros["contrato_servico_emails_capacidade$sufixo"] = $assinatura["parametros"]["emails_capacidade"];
				$parametros["contrato_servico_hospedagens_capacidade$sufixo"] = $assinatura["parametros"]["hospedagens_capacidade"];
				$parametros["contrato_servico_hospedagens_transferencia$sufixo"] = $assinatura["parametros"]["hospedagens_transferencia"];
				$parametros["contrato_servico_basesdedados_capacidade$sufixo"] = $assinatura["parametros"]["basesdedados_capacidade"];
			}
			
			$parametros["contrato_assinaturas$sufixo"][$assinatura["id"]] = $assinatura;
			
			$valor_multa += $assinatura["multa"];
			$valor_vigencia = $assinatura["vigencia"] > $valor_vigencia ? $assinatura["vigencia"] : $valor_vigencia;
			
		}

		foreach ($descontos as $desconto) {
			if (in_array($desconto["desconto_tipo_id"], array(1, 3))) {
				//$parametros["tabela_valor_desconto_beneficio_instalacao$sufixo"] += $desconto["instalacao"] > 1 ? $desconto["instalacao"] : $desconto["instalacao"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["instalacao"];
				$parametros["tabela_valor_original_mensalidade$sufixo"] -= $desconto["mensalidade"] > 1 ? $desconto["mensalidade"] : $desconto["mensalidade"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["mensalidade"];
				$parametros["tabela_valor_original_instalacao$sufixo"] -= $desconto["instalacao"] > 1 ? $desconto["instalacao"] : $desconto["instalacao"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["instalacao"];
			} elseif ($desconto["desconto_tipo_id"] == 2) {
				$parametros["tabela_valor_desconto_fidelidade_instalacao$sufixo"] += $desconto["instalacao"] > 1 ? $desconto["instalacao"] : $desconto["instalacao"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["instalacao"];
				$parametros["tabela_valor_original_mensalidade$sufixo"] -= $desconto["mensalidade"] > 1 ? $desconto["mensalidade"] : $desconto["mensalidade"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["mensalidade"];
			} else {
				$parametros["tabela_valor_desconto_bonificacao_mensalidade$sufixo"] += $desconto["mensalidade"] > 1 ? $desconto["mensalidade"] : $desconto["mensalidade"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["mensalidade"];
			}
			$parametros["contrato_descontos$sufixo"][$desconto["id"]] = $desconto;
		}

		$parametros["tabela_valor_original_mensalidade$sufixo"] = max($parametros["tabela_valor_original_mensalidade$sufixo"], 0);
		$parametros["tabela_valor_original_instalacao$sufixo"]  = max($parametros["tabela_valor_original_instalacao$sufixo"], 0);
		
		$parametros["contrato_multa_rescisao$sufixo"] = $valor_multa;

		if ($vigencia && !$sufixo) {
			$parametros["contrato_prazo_vigencia"] = $valor_vigencia;
		} else {
			$parametros["contrato_prazo_vigencia$sufixo"] = $valor_vigencia;
		}

		$parametros["tabela_valor_final_instalacao$sufixo"] = max($parametros["tabela_valor_original_instalacao$sufixo"] - $parametros["tabela_valor_desconto_beneficio_instalacao$sufixo"] - $parametros["tabela_valor_desconto_fidelidade_instalacao$sufixo"], 0);
		$parametros["tabela_valor_final_mensalidade$sufixo"] = max($parametros["tabela_valor_original_mensalidade$sufixo"] - $parametros["tabela_valor_desconto_beneficio_mensalidade$sufixo"] - $parametros["tabela_valor_desconto_fidelidade_mensalidade$sufixo"] - $parametros["tabela_valor_desconto_bonificacao_mensalidade$sufixo"], 0);

		$parametros["tabela_valor_parcela$sufixo"] = intval($parametros["tabela_valor_final_instalacao$sufixo"] / $parcelamento);
		
		return $parametros;

	}

	function calculaMultaRescisao($descontos, $parametros, $sufixo = "") {
		
		$multa = 0;
		
		foreach ($descontos as $desconto) {
			if ($desconto["desconto_tipo_id"] == 2) {
				$fidelidade_instalacao = $desconto["instalacao"] > 1 ? $desconto["instalacao"] : $desconto["instalacao"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["instalacao"];
				$fidelidade_mensalidade = ($desconto["mensalidade"] > 1 ? $desconto["mensalidade"] : $desconto["mensalidade"] * $parametros["contrato_assinaturas$sufixo"][$desconto["assinatura"]]["mensalidade"]) * intval($desconto["validade"]);
				$multa += $fidelidade_instalacao + $fidelidade_mensalidade;
			}
		}
		
		return $multa;
		
	}

	function corrigeNome($nome) {

		$minusculas = array("da", "de", "do", "das", "dos", "e");
		$maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
		
		$nome = str_replace(".", ". ", $nome);
		$nome = preg_replace("/[\r\n\t ]+/", " ", $nome);
		
		if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
			$nome = strtolower($nome);
			$nome = ucwords($nome);
		}
		
		for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome ");
		for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome ");
		
		$nome = trim($nome);

		return $nome;

	}
	
	function formataComplemento($complemento, $complemento_tipo, $bloco) {
		
		$retorno = array();
		
		$tipos = array(
			"a" => "Apto",
			"l" => "Loja",
			"s" => "Sala",
			"c" => "Casa",
			"o" => ""
		);

		if ($complemento) {
			$retorno[] = trim("{$tipos[$complemento_tipo]} {$complemento}");
		}
		
		if ($bloco && $bloco != "Único") {
			$retorno[] = "Bloco {$bloco}";
		}
		
		return implode(" / ", $retorno);
		
	}

}

?>
