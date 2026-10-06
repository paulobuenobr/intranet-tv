<?php

require_once "_global/dompdf/autoload.inc.php";
require_once "_global/PHPMailer/PHPMailerAutoload.php";

use Dompdf\Dompdf;

//TODO formas_pagamento não existe em Olé TV

class contratos {

	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	private $permissoes;
	private $dados_modulo;
	
	public $dados_item;
	public $dados_acao;
	public $conteudo;
	
	private $complemento_tipos = array(
		"a" => "Apartamento",
		"l" => "Loja",
		"s" => "Sala",
		"c" => "Casa",
		"o" => "Outro"
	);

	function __construct($bancodedados, $permissoes = null, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		if ($executar) {
			$this->dados_modulo = $this->buscaDadosModulo($_GET["m"]);
			$this->dados_acao = $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->dados_acao["nome"]}();
		}

	}

	#########################
	### Controle de Ações ###
	#########################

	function ehSincronizavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["sincronizar"]) return false;
		if ($this->dados_item["status_id"] == 24) return false;
		if ($this->dados_item["sincronizado"] && !$this->permissoes[$this->dados_modulo["nome"]]["sincronizar_plus"]) return false;

		return true;

	}

	function ehAdicionavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["adicionar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if ($this->possuiAditivoPendente($this->dados_item["id"])) return false;
		if (!in_array($this->dados_item["status_id"], array(19, 20))) return false;
		if (in_array($this->dados_item["tipo_id"], array(13)) && empty($this->dados_item["pai"])) return true;
		if (!in_array($this->dados_item["tipo_id"], array(11))) return false;

		return true;

	}

	function ehEnviavelContrato() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["enviar_contrato"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if ($this->dados_item["status_id"] < 13) return false;
		if ($this->dados_item["status_id"] == 24) return false;
		if (!$this->existeContrato($this->dados_item, "contrato")) return false;

		return true;

	}

	function ehEnviavelCobranca($data_ativacao = null) {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["enviar_cobranca"]) return false;
		if ($data_ativacao) {
			if ($this->buscaCobranca($this->dados_item["cliente"], $data_ativacao)) return true;
		} else {
			if (!in_array($this->dados_item["status_id"], array(19, 20))) return false;
			if ($this->buscaCobranca($this->dados_item["cliente"], $this->dados_item["data_ativacao"])) return true;
		}

		return false;

	}

	function ehAprovavelFinanceiro() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["financeiro_aprovar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!in_array($this->dados_item["status_id"], array(11, 12))) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;
		if (count($this->dados_item["formas_pagamento"]) && $this->dados_item["formas_pagamento"][0]["entrada"] > 0 && !$this->permissoes[$this->dados_modulo["nome"]]["financeiro_aprovar_plus"]) return false;

		return true;

	}

	function ehGeravelCobrancaPrePago() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["gerar_cobranca_pre_pago"]) return false;
		if ($this->dados_item["tipo_id"] != 11) return false;
		if ($this->dados_item["status_id"] != 11) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!$this->permissoes[$this->dados_modulo["nome"]]["gerar_cobranca_pre_pago_plus"] && $this->buscaQuantidadeRestricoes($this->dados_item["cliente"]) > FINANCEIRO_LIMITE_RESTRICOES) return false;
		if ($this->cobrancaInstalacaoGerada($this->dados_item["id"])) return false;
		if ($this->ehClienteConsultavelCredito($this->dados_item["cliente"])) return false;
		if ($this->dados_item["parametros"]["parcelamento"] !== 0 || $this->dados_item["parametros"]["tabela_valor_final_instalacao"] == 0) return false;
		if ($this->dados_item["parametros"]["parcelamento_forma"] == 1) return false;

		return true;

	}

	function ehMigravelPrePago() {

		$pos_pagos = 0;

		if (!$this->permissoes[$this->dados_modulo["nome"]]["migrar_sistema"]) return false;
		if ($this->dados_item["tipo_id"] != 11) return false;
		if ($this->dados_item["status_id"] != 12) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		foreach ($this->dados_item["assinaturas"] as $assinatura) {
			if ($assinatura["plano_sistema_id"] == 1) return false;
			if ($assinatura["sistema_id"] == 1) $pos_pagos++;
		}
		if (!$pos_pagos) return false;
		if ($this->buscaQuantidadeRestricoes($this->dados_item["cliente"]) > FINANCEIRO_LIMITE_RESTRICOES) return false;

		return true;

	}

	function ehConsultavelCredito() {

		return false;

		if (!$this->permissoes[$this->dados_modulo["nome"]]["consultar_credito"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;
		if (!in_array($this->dados_item["status_id"], array(11, 12)) && !$this->permissoes[$this->dados_modulo["nome"]]["consultar_credito_plus_plus"]) return true;
		if (!$this->ehClienteConsultavelCredito($this->dados_item["cliente"]) && !$this->permissoes[$this->dados_modulo["nome"]]["consultar_credito_plus"]) return false;

		return true;

	}

	function ehReprovavelFinanceiro() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["financeiro_aprovar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!in_array($this->dados_item["status_id"], array(11))) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;

		return true;

	}

	function ehAprovavelComercial() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["comercial_aprovar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!in_array($this->dados_item["status_id"], array(13, 14))) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;

		return true;

	}

	function ehReprovavelComercial() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["comercial_reprovar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!in_array($this->dados_item["status_id"], array(13))) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;

		return true;

	}

	function ehAprovavelTecnico() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["tecnico_aprovar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if ($this->dados_item["servico_id"] == 3 && !$this->permissoes[$this->dados_modulo["nome"]]["tecnico_aprovar_empresarial"]) return false;
		if (!in_array($this->dados_item["status_id"], array(15, 16))) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;

		return true;

	}

	function ehReprovavelTecnico() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["tecnico_reprovar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if ($this->dados_item["servico_id"] == 3 && !$this->permissoes[$this->dados_modulo["nome"]]["tecnico_aprovar_empresarial"]) return false;
		if (!in_array($this->dados_item["status_id"], array(15))) return false;

		return true;

	}

	function ehVisualizavelContrato() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["visualizar_contrato"]) return false;
		if (!$this->dados_item["parametros"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!$this->existeContrato($this->dados_item, "contrato")) return false;

		return true;

	}

	function ehVisualizavelVistoria() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["visualizar_vistoria"]) return false;
		if (!$this->dados_item["parametros"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if (!$this->existeContrato($this->dados_item, "vistoria")) return false;

		return true;

	}

	function ehEditavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["editar"]) return false;
		if ($this->dados_item["status_id"] == 24) return false;
		if (!$this->dados_item["sincronizado"]) return false;

		return true;

	}

	function ehEditavelDatas() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["editar_datas"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;

		return true;

	}

	function ehEditavelVigencia() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["editar_vigencia"]) return false;
		if ($this->dados_item["status_id"] >= 19) return false;
		if (!$this->dados_item["sincronizado"]) return false;

		return true;

	}

	function ehConfirmavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["confirmar"]) return false;
		if (!in_array($this->dados_item["status_id"], array(13, 15))) return false;
		if (!in_array($this->dados_item["tipo_id"], array(11, 12))) return false;
		if (!$this->possuiVisita($this->dados_item["id"])) return false;
		if ($this->dados_item["data_confirmacao"]) return false;

		return true;

	}

	function ehAtivavel() {

		if (!$this->permissoes["contratos"]["ativar"]) return false;
		if (!$this->dados_item["sincronizado"]) return false;
		if ($this->dados_item["status_id"] != 17) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;
		if ($this->dados_item["tipo_id"] == 12 && $this->permissoes["contratos"]["ativar_aa"]) return true;
		if ($this->dados_item["tipo_id"] == 13 && $this->permissoes["contratos"]["ativar_ma"]) return true;
		if ($this->dados_item["tipo_id"] == 14 && $this->permissoes["contratos"]["ativar_me"]) return true;
		if ($this->dados_item["tipo_id"] == 15 && $this->permissoes["contratos"]["ativar_mt"]) return true;
		if ($this->dados_item["servico_id"] == 11 && $this->permissoes["contratos"]["ativar_dominio"]) return true;
		if ($this->dados_item["servico_id"] == 20 && $this->permissoes["contratos"]["ativar_email"]) return true;
		if ($this->permissoes["contratos"]["ativar_plus"]) return true;

		return false;

	}

	function ehAtivavelSemPendencia() {

		if (!$this->dados_item["sincronizado"]) return false;
		if (!$this->permissoes[$this->dados_modulo["nome"]]["ativar"]) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;
		if ($this->dados_item["status_id"] != 19) return false;
		if (!$this->possuiContrato($this->dados_item)) return false;

		return true;

	}

	function ehDesativavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["desativar"]) return false;
		if ($this->dados_item["tipo_id"] != 11) return false;
		if ($this->dados_item["status_id"] < 19) return false;
		if ($this->dados_item["status_id"] > 20) return false;

		return true;

	}

	function ehRemovivel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["remover"]) return false;
		if ($this->dados_item["status_id"] >= 18) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;
		if ($this->existeAprovacaoCliente() && !$this->permissoes[$this->dados_modulo["nome"]]["remover_plus"]) return false;
		if ($this->cobrancaInstalacaoGerada($this->dados_item["id"]) && !$this->permissoes[$this->dados_modulo["nome"]]["remover_plus"]) return false;

		return true;

	}

	function ehCancelavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["cancelar"]) return false;
		if (!in_array($this->dados_item["status_id"], array(19, 20))) return false;
		if ($this->dados_item["pai"]) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;
		if ($this->dados_item["servico_id"] != 21 && !$this->permissoes[$this->dados_modulo["nome"]]["cancelar_plus"]) return false;

		return true;

	}

	function ehAnulavelContrato() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["anular"]) return false;
		if (!in_array($this->dados_item["status_id"], array(11, 12, 13, 14, 15, 16, 17, 18))) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;

		return true;

	}

	function ehSimulavelCancelamento() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["simular_cancelamento"]) return false;
		if (!in_array($this->dados_item["status_id"], array(19, 20))) return false;
		if (!$this->dados_item["vigencia_ativa"]) return false;

		return true;

	}

	function ehDescancelavel() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["descancelar"]) return false;
		if ($this->dados_item["status_id"] != 22) return false;
		if ($this->dados_item["pai"]) return false;
		if ($this->dados_item["dias_apos_cancelamento"] > 90 && !$this->permissoes[$this->dados_modulo["nome"]]["descancelar_plus"]) return false;

		return true;

	}

	function ehAnulavelAtivacao() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["anular_ativacao"]) return false;
		if ($this->dados_item["status_id"] != 18) return false;

		return true;

	}

	function ehAnulavelCancelamento() {

		if (!$this->permissoes[$this->dados_modulo["nome"]]["anular_cancelamento"]) return false;
		if ($this->dados_item["status_id"] != 21) return false;
		if ($this->dados_item["pai"]) return false;
		if ($this->dados_item["vinculo_pai_obrigatorio"]) return false;

		return true;

	}

	function ehBaixavelContrato() {

		if (!$this->permissoes["contratos"]["baixar_contrato"]) return false;
		if (!file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$this->dados_item["codigo"]} Telecom")))
				&& !file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$this->dados_item["codigo"]} Telecom"))))
				&& !file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$this->dados_item["codigo"]} Telecom"))))) return false;

		return true;

	}
	
	function ehRemovivelContrato() {
		
		if (!$this->permissoes["contratos"]["remover_contrato"]) return false;
		if (!$this->possuiContrato($this->dados_item)) return false;
		
		return true;
		
	}
	
	function possuiContrato($dados_contrato) {
		
		return file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$dados_contrato["codigo"]} Telecom"))) ||
			file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$dados_contrato["codigo"]} Telecom")))) ||
			file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$dados_contrato["codigo"]} Telecom"))));
		
	}

	function ehAutoAprovavelFinanceiro($dados_contrato) {

		if ($dados_contrato["tipo_id"] == 11 && 
			$this->dados_item["parametros"]["tabela_valor_final_instalacao"] > 0 && 
			$dados_contrato["parametros"]["parcelamento"] == 0 && 
			!$this->cobrancaInstalacaoRecebida($dados_contrato["id"])) return false;
		
		return true;

	}

	function ehAutoReprovavelFinanceiro($dados_cliente) {

		if ($this->buscaQuantidadeRestricoes($dados_cliente["id"]) <= FINANCEIRO_LIMITE_RESTRICOES) return false;

		return true;

	}

	function ehAutoAprovavelComercial($tipo_parametros, $servico_parametros) {

		if ($tipo_parametros["auto_aprovacao_comercial"] || ($tipo_parametros["auto_aprovacao_comercial"] === null && $servico_parametros["auto_aprovacao_comercial"])) return true;

		return false;

	}

	function ehAutoAprovavelTecnico($dados_cliente, $tipo_parametros, $servico_id, $servico_parametros, $contrato_parametros, $contrato_tipo_id, $contrato_pai_parametros = null) {

		if ($contrato_tipo_id == 11 && $this->existeAprovacaoTecnicaAnulada($dados_cliente["id"], $servico_id)) return true;
		if ($tipo_parametros["auto_aprovacao_tecnico"] || ($tipo_parametros["auto_aprovacao_tecnico"] === null && $servico_parametros["auto_aprovacao_tecnico"])) return true;
		if ($contrato_tipo_id == 14) return false;
		if ($contrato_parametros["contrato_servico_pontos_quantidade"] > $contrato_pai_parametros["contrato_servico_pontos_quantidade"]) return false;

		return true;

	}

	#################
	### Adicionar ###
	#################

	function adicionar() {

		if ($_POST && $_POST["post"]) {
			$_POST = unserialize($_POST["post"]);
		} else if ($_POST) {
			$_POST["assinaturas"] = explode(",", $_POST["assinaturas"]);
		}

		if (($cobrancas = $this->buscaCobrancasAtrasadas($_GET["cli"])) && (!$this->permissoes["contratos"]["gerenciar_inadimplentes"] || !$_POST)) {

		    $botoes = array();
			if ($this->permissoes["contratos"]["gerenciar_inadimplentes"]) $botoes[] = '<input type="submit" name="sim" class="buttonAzulEscuroBranco11" value="Sim">';
			if ($_GET["cor"]) $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
		    
			$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");

			$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td>', $this->conteudo);
			$this->conteudo = str_replace("[erro]", "Este cliente possui " . count($cobrancas) . " cobrança(s) em atraso.<br>", $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&cli={$_GET["cli"]}&cor={$_GET["cor"]}", $this->conteudo);
			$this->conteudo = str_replace("[mensagem]", $this->permissoes["contratos"]["gerenciar_inadimplentes"] ? "Você deseja continuar com a geração deste contrato?" : "Não é possível continuar com a geração deste contrato!<br>Entre em contato com o Setor Financeiro!", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		} else {

			$dados_cliente = $this->buscaDadosCliente($_GET["cli"]);
			$dados_contrato = $this->buscaDadosContrato($_GET["cor"]);

			if ($dados_cliente) {

				if (!$_POST["assinaturas"]) {

					if (!$dados_contrato) {

						// Contrato Principal

						$servicos = $this->buscaServicosAssinaturasSemContrato($dados_cliente["id"]);

						$botoes = array();
						$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
						$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

						$this->conteudo .= $this->carregaArquivo("contratos.principal.adicionar.inc.php");

						$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td>', $this->conteudo);
						$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
						$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
						$this->conteudo = str_replace("[servicos]", $this->geraSelect($servicos, count($servicos) == 1 ? key($servicos) : null, true), $this->conteudo);
						
						if (count($servicos) == 1) {
							
							$dados_assinaturas = $this->buscaDadosAssinaturasProspect($dados_cliente["id"], key($servicos));
							$assinaturas = $this->buscaAssinaturasEditavel($dados_cliente["id"], 0, key($servicos));

							$this->conteudo = str_replace("[exibe_assinaturas]", "table-row-group", $this->conteudo);
							$this->conteudo = str_replace("[assinaturas]", $this->geraListagemAssinaturasEditavel($assinaturas), $this->conteudo);
							
							if (count($assinaturas) == 1) {
								
								$descontos = $this->buscaDescontosProspect($dados_cliente["id"], $assinaturas[0]["id"]);
								$dados_assinaturas = $this->buscaDadosAssinaturasProspect($dados_cliente["id"], $assinaturas[0]["servico_id"], $assinaturas[0]["id"]);
								
								$this->conteudo = str_replace("[descontos]", $this->geraListagemDescontos($descontos), $this->conteudo);
								$this->conteudo = str_replace("[mensalidade_valor]", $dados_assinaturas["mensalidade"], $this->conteudo);
								$this->conteudo = str_replace("[instalacao_valor]", $dados_assinaturas["instalacao"], $this->conteudo);
								$this->conteudo = str_replace("[parcelamento]", $dados_assinaturas["parcelamento"], $this->conteudo);
								$this->conteudo = str_replace("[multa]", $dados_assinaturas["multa"], $this->conteudo);
								$this->conteudo = str_replace("[vigencia]", $dados_assinaturas["vigencia"], $this->conteudo);
								
							} else {
								
								$this->conteudo = str_replace("[descontos]", $this->geraListagemDescontos($this->buscaDescontosProspect($dados_cliente["id"])), $this->conteudo);
								$this->conteudo = str_replace("[mensalidade_valor]", "", $this->conteudo);
								$this->conteudo = str_replace("[instalacao_valor]", "", $this->conteudo);
								$this->conteudo = str_replace("[parcelamento]", "", $this->conteudo);
								$this->conteudo = str_replace("[multa]", "", $this->conteudo);
								$this->conteudo = str_replace("[vigencia]", "", $this->conteudo);
								
							}
							
						} else {
							$this->conteudo = str_replace("[exibe_assinaturas]", "none", $this->conteudo);
						}
						
						$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&cli={$dados_cliente["id"]}", $this->conteudo);
						$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

					} else {

						// Contrato Aditivo

						$botoes = array();
						$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
						$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
						$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

						$this->conteudo .= $this->carregaArquivo("contratos.aditivo.adicionar.inc.php");

						$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
						$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
						$this->conteudo = str_replace("[servico]", $dados_contrato["servico_nome"], $this->conteudo);
						$this->conteudo = str_replace("[servico_id]", $dados_contrato["servico_id"], $this->conteudo);
						$this->conteudo = str_replace("[contrato_id]", $dados_contrato["id"], $this->conteudo);
						$this->conteudo = str_replace("[codigo]", $dados_contrato["codigo"], $this->conteudo);
						$this->conteudo = str_replace("[tipos]", $this->geraSelect($this->buscaContratosTipos()), $this->conteudo);
						$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&cor={$_GET["cor"]}&cli={$dados_cliente["id"]}", $this->conteudo);
						$this->conteudo = str_replace("[prazo_vigencia]", $dados_contrato["prazo_vigencia"], $this->conteudo);

						if($dados_contrato["vigencia_ativa"]){
							$multa = $this->vigenciaAtivaCancelamento($dados_contrato, date("d/m/Y"));
							$data_atual_meses = (intval(date("Y"))*12) + intval(date("m"));
							$termino_fidelidade = strtotime($dados_contrato["termino_fidelidade"]);
							$termino_fidelidade = date("d/m/Y", $termino_fidelidade);
							$termino_fidelidade_meses = (intval(substr($termino_fidelidade, 6, 4)) * 12) + intval(substr($termino_fidelidade, 3, 2));
							$termino_fidelidade_meses = substr($termino_fidelidade, 0, 2) > date("d") ? $termino_fidelidade_meses + 1 : $termino_fidelidade_meses;
							$this->conteudo = str_replace("[vigencia_minima]", $termino_fidelidade_meses - $data_atual_meses, $this->conteudo);
							$this->conteudo = str_replace("[valor_minimo_multa]", $multa, $this->conteudo);
						}else{
							$this->conteudo = str_replace("[vigencia_minima]", 0, $this->conteudo);
							$this->conteudo = str_replace("[valor_minimo_multa]", 0, $this->conteudo);
						}

						$this->conteudo = str_replace("[exibir_mensagem_vigencia]", $dados_contrato["vigencia_ativa"] ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
						$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

					}

				} else {

					$servico_id = $dados_contrato ? $dados_contrato["servico_id"] : $_POST["servico"];
					$contrato_tipo = $this->buscaDadosTipo(!$dados_contrato ? 11 : $_POST["tipo"]);
					$erros_contrato = $this->buscaErrosContratoAdicionar($dados_cliente, $contrato_tipo["id"], $servico_id, $_POST["assinaturas"], true, $_POST["novo_plano"]);

					if (!$erros_contrato || ($_GET["continuar"] && $erros_contrato["adicionar_plus"] && $this->permissoes["contratos"]["adicionar_plus"])) {

						$ass_id = $_POST["assinaturas"];
						$ass_ids = is_array($ass_id) ? implode(',', array_map('intval', $ass_id)) : intval($ass_id);
						$query = "
							SELECT tab_assinaturas.*
							FROM rel_ass_cor_estacontido INNER JOIN tab_assinaturas ON tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id
							WHERE rel_ass_cor_estacontido.ass_id IN ({$ass_ids})  AND tab_assinaturas.ast_id != 1;";
							$temAssinatura = $this->bancodedados->resultado($query);
						if (empty($temAssinatura)) {
						
							$contrato_id = $this->geraContrato($dados_cliente, $contrato_tipo, $servico_id, $_POST["assinaturas"], $dados_contrato, $_POST["novo_plano"], $_POST["migrar_descontos"], null, $_POST["parcelamento"], $_POST["formas_pagamento"]);

							if($contrato_id){

								$youCastDados = $this->buscarProdutoYoucast($contrato_id);
								$codigoServico = addslashes($youCastDados["codigoServico"]);
								$codigoPlayer = $youCastDados["codigoPlayer"];
								$planoId = $youCastDados["planoId"];

								if($codigoPlayer == 'youcast'){
									$emails = $dados_cliente["emails"][0]["contato_email"] ? $dados_cliente["emails"][0]["contato_email"] : "null";
									$cpf_cnpj = preg_replace('/[^0-9a-zA-Z]/', '', $dados_cliente["cpf_cnpj"]);

									$insertSql = "
											INSERT INTO tab_youcast_clientes (cliente, contrato, documento, email, origem, produto, status) 
											VALUES ({$dados_cliente["id"]}, {$contrato_id}, '{$cpf_cnpj}', '{$emails}', {$dados_cliente["origem_id"]}, '{$codigoServico}', 2);";
									$this->bancodedados->query($insertSql);
								}
							}
							else{
								$contrato_id = $dados_contrato["id"];
							}
							$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
							$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $contrato_id);
						}

						header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$contrato_id");

					} else {

						$erros_contrato_detalhamento = array();
						for ($i = 0; $i < sizeof($erros_contrato["itens"]); $i++) {
							$erros_contrato_detalhamento[] = " » {$erros_contrato["itens"][$i]};";
						}

						$botoes = array();
						if ($erros_contrato["adicionar_plus"]) $botoes[] = '<input type="submit" name="sim" class="buttonAzulEscuroBranco11" value="Sim">';
						$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
						$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

						$this->conteudo .= $this->carregaArquivo("contratos.adicionar.erros.inc.php");

						$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=adicionar&cli={$dados_cliente["id"]}&cor={$dados_contrato["id"]}&continuar=1", $this->conteudo);
						$this->conteudo = str_replace("[detalhamento]", implode("<br>", $erros_contrato_detalhamento), $this->conteudo);
						$this->conteudo = str_replace("[post]", serialize($_POST), $this->conteudo);
						$this->conteudo = str_replace("[mensagem]", $erros_contrato["adicionar_plus"] ? "Você deseja continuar a geração deste contrato?" : "Corrija o(s) erro(s) e tente novamente!", $this->conteudo);
						$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

					}

				}

			} else {
				$this->fechar();
			}

		}

	}

	/**
	 * Busca o produto Youcast do contrato.
	 * @param string $contrato_id - ID do contrato.
	 * @return array - Dados do produto Youcast.
	 */
	function buscarProdutoYoucast($contrato_id) {
		$youCastSql = "SELECT tab_multiservice_servicos.codigo codigoServico, tab_multiservice_players.codigo codigoPlayer, tab_assinaturas.pla_id planoId
						FROM tab_planos_multiservice LEFT OUTER JOIN tab_assinaturas ON tab_assinaturas.pla_id = tab_planos_multiservice.pla_id
						LEFT OUTER JOIN rel_ass_cor_estacontido ON rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id
						LEFT OUTER JOIN tab_multiservice_players ON tab_multiservice_players.id = tab_planos_multiservice.player
						LEFT OUTER JOIN tab_multiservice_servicos ON tab_multiservice_servicos.id = tab_planos_multiservice.servico
						WHERE rel_ass_cor_estacontido.cor_id = {$contrato_id} 
						GROUP BY tab_multiservice_servicos.id, tab_multiservice_players.id";

		$youCastQuery = $this->bancodedados->query($youCastSql);
		$youCastDados = $this->bancodedados->resultadoArray($youCastQuery);
		
		if($youCastDados){
			return $youCastDados;
		} else {
			return false;
		}
	}

	#############################################
	### Adicionar Aditivo Mudança de Endereço ###
	#############################################

	function adicionar_aditivo_me() {

		$dados_contrato = $this->buscaDadosContrato($_GET["i"]);

		if ($dados_contrato) {

		    $dados_cliente = $this->buscaDadosCadastraisCliente($dados_contrato["cliente"]);
			$endereco_cobranca = $this->buscaDadosClienteEnderecoCobranca($dados_contrato["cliente"]);

			if (!$_POST) {

			    $campos_status = array();
				$desabilitados = array();
				$habilitados = array("condominio", "bloco", "cep", "numero", "complemento_tipo", "complemento_valor", "cobranca_condominio", "cobranca_bloco", "cobranca_cep", "cobranca_numero", "cobranca_complemento_tipo", "cobranca_complemento_valor");

				$botoes = array();
				$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				for ($i = 0; $i < sizeof($habilitados); $i++) {
					$campos_status[$habilitados[$i]] = (in_array($habilitados[$i], $desabilitados)) ? true : false;
				}

				if ($dados_cliente["condominio_id"]) {
					$campos_status["cep"] = true;
					$campos_status["numero"] = true;
				} else {
					$campos_status["bloco"] = true;
				}

				if ($endereco_cobranca["condominio_id"]) {
					$campos_status["cobranca_cep"] = true;
					$campos_status["cobranca_numero"] = true;
				} else {
					$campos_status["cobranca_bloco"] = true;
				}

				if ($endereco_cobranca["mesmo_endereco"]) {
					$campos_status["cobranca_condominio"] = true;
					$campos_status["cobranca_bloco"] = true;
					$campos_status["cobranca_cep"] = true;
					$campos_status["cobranca_numero"] = true;
					$campos_status["cobranca_complemento_tipo"] = true;
					$campos_status["cobranca_complemento_valor"] = true;
				}

				$this->conteudo .= $this->carregaArquivo("contratos.aditivo_me.adicionar.inc.php");

				// Campos
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp; Adicionar Aditivo (Mudança de Endereço)&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $dados_contrato["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[formas_pagamento]", $this->geraListagemFormasPagamentoServico($this->buscaFormasPagamentoServico($dados_contrato["servico_id"])) , $this->conteudo);

				// Endereço Instalação
				if ($dados_cliente["condominio_id"]) {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $dados_cliente["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[panorama_condominio]", $this->buscaPanoramaCondominio($dados_cliente["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", $this->geraSelect($this->buscaBlocos($dados_cliente["condominio_id"]), $dados_cliente["bloco_id"]), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[panorama_condominio]", "", $this->conteudo);
					$this->conteudo = str_replace("[bloco]", "", $this->conteudo);
				}
				$this->conteudo = str_replace("[cep]", $dados_cliente["cep"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $dados_cliente["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->complemento_tipos,$dados_cliente["complemento_tipo_id"]),$this->conteudo);
				$this->conteudo = str_replace("[complemento_valor]", $dados_cliente["complemento_valor"], $this->conteudo);

				// Endereço Cobrança
				if ($endereco_cobranca["condominio_id"]) {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios(), $endereco_cobranca["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_panorama_condominio]", $this->buscaPanoramaCondominio($endereco_cobranca["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", $this->geraSelect($this->buscaBlocos($endereco_cobranca["condominio_id"]), $endereco_cobranca["bloco_id"]), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_panorama_condominio]", "", $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", "", $this->conteudo);
				}
				$this->conteudo = str_replace("[cobranca_cep]", $endereco_cobranca["cep"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_numero]", $endereco_cobranca["numero"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_tipo]", $this->geraSelect($this->complemento_tipos, $endereco_cobranca["complemento_id"]), $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_valor]", $endereco_cobranca["complemento"], $this->conteudo);
				$this->conteudo = str_replace("[mesmo_endereco_checked]", $endereco_cobranca["mesmo_endereco"], $this->conteudo);

				// Desabilitar Campos
				foreach ($campos_status as $campo => $valor) {
					$this->conteudo = str_replace("[{$campo}_disabled]", ($valor) ? "disabled" : "", $this->conteudo);
				}

				// Permissão Dados Duplicados
				$this->conteudo = str_replace("[duplicar_endereco]", $this->permissoes["clientes"]["duplicar_endereco"] ? 1 : 0, $this->conteudo);
				$this->conteudo = str_replace("[duplicar_cpf_cnpj]", $this->permissoes["clientes"]["duplicar_cpf_cnpj"] ? 1 : 0, $this->conteudo);
				$this->conteudo = str_replace("[duplicado_endereco]", $this->existeDuplicidadeEndereco($dados_cliente["id"], $dados_cliente["bloco_id"], $dados_cliente["cep_id"], $dados_cliente["numero"], $dados_cliente["complemento_tipo_id"], $dados_cliente["complemento_valor"]) ? 1 : 0, $this->conteudo);
				$this->conteudo = str_replace("[duplicado_cpf_cnpj]", $this->existeDuplicidadeCPFCNPJ($dados_cliente["id"], $dados_cliente["cpf_cnpj"]) ? 1 : 0, $this->conteudo);

				// Instalação / Fidelização
				$this->conteudo = str_replace("[vigencia]", $this->geraSelect($this->buscaListaVigencia(36), 0), $this->conteudo);
				$this->conteudo = str_replace("[valor_instalacao]", $this->formataDinheiro(CONTRATO_INSTALACAO_MUDANCA_ENDERECO), $this->conteudo);
				$this->conteudo = str_replace("[parcelamento]", $this->geraSelect($this->buscaParcelamentos(null, 1, 12)), $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			} else {

				$dados_tipo = $this->buscaDadosTipo(14);
				$contrato_codigo = $this->geraContratoAditivoCodigo($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["codigo"]);
				$contrato_parametros = $this->geraContratoAditivoMudancaEnderecoParametros($dados_cliente["id"], $dados_contrato["assinaturas"], $dados_contrato["descontos"]);

				if (!$this->ehAutoAprovavelComercial($dados_tipo["parametros"], $dados_contrato["servico_parametros"])) {
					$status_id = 13;
				} elseif (!$this->ehAutoAprovavelTecnico($dados_cliente, $dados_tipo["parametros"], $dados_contrato["servico_id"], $dados_contrato["servico_parametros"], $contrato_parametros, $dados_tipo["id"], $dados_contrato["parametros"])) {
					$status_id = 15;
				} else {
					$status_id = 17;
				}

				$parametros = addslashes(serialize($contrato_parametros));

				$_POST["vigencia"] = intval(preg_replace("/[^0-9]/", "", $_POST["vigencia"]), 10);
				$_POST["valor_multa"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_multa"]), 10);

				$prazo_fidelidade = $_POST["vigencia"] && $_POST["valor_multa"] ? $_POST["vigencia"] : "NULL";
				$multa_fidelidade = $_POST["vigencia"] && $_POST["valor_multa"] ? $_POST["valor_multa"] :	"NULL";

				// Registra Contrato
				$this->bancodedados->query("
					INSERT INTO
						tab_contratos
					SET
						cli_id = {$dados_cliente["id"]},
						cor_codigo = '$contrato_codigo',
						cor_data_geracao = CURDATE(),
						cor_parametros = '$parametros',
						cor_vigencia = $prazo_fidelidade,
						cor_multa = $multa_fidelidade,
						cor_sincronizado = 1,
						pls_id = {$dados_contrato["servico_id"]},
						crt_id = {$dados_tipo["id"]},
						crs_id = $status_id,
						cor_pai = {$dados_contrato["id"]}");

				// Busca ID Contrato
				$id = $this->bancodedados->ultimoIdInserido();

				// Registra Comentário
				$this->registraComentario($id, 1, $_POST["comentario"], $_SESSION["usuario"]);

				// Vincula Formas Pagamento
				if ($_POST["formas_pagamento"]) {
					foreach ($_POST["formas_pagamento"] as $forma_pagamento) {
						$this->bancodedados->query("
							INSERT INTO
								rel_fop_cor_estacontido
							SET
								fop_id = $forma_pagamento,
								cor_id = $id");
					}
				}

				// Comentário Auto Aprovação Financeiro
				if ($status_id > 11) {
					$this->registraComentario($id, 4);
				}

				// Comentário Auto Aprovação Comercial
				if ($status_id > 13) {
					$this->registraComentario($id, 12);
				}

				// Comentário Auto Aprovação Técnico
				if ($status_id > 15) {
					$this->registraComentario($id, 15);
					// Gera Visita
					if ($dados_cliente["origem_id"] == 1 && !$dados_contrato["vinculos_pai"] && $dados_contrato["servico_parametros"]["gera_visita"]) {
						$visita_id = $this->geraVisita($dados_cliente["id"], $id);
						$this->registraComentarioVisita($visita_id, "<b>Contrato Aprovado</b>: {$contrato_codigo}", 16, $_SESSION["usuario"]);
					}
				}

				// Insere Chamado Residencial (Vistoria)
				if (in_array($dados_contrato["servico_id"], array(3, 28, 33, 34))) {

					$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
					$chamado_categoria_id = $this->buscaCategoriaViabilidadeResidencial($dados_cliente["id"]);
					$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_cliente["id"], $chamado_categoria_id);

					$dados = array();

					$dados["conteudo"] = "[n]Verificar viabilidade de instalação.[/n]";
					$dados["conteudo"] .= "[d]";
					$dados["conteudo"] .= "[n]Contrato:[/n] [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato({$dados_contrato["id"]})\">{$dados_contrato["codigo"]}</a>]\n";
					$dados["conteudo"] .= "[n]Serviço:[/n] {$dados_contrato["servico_nome"]}\n";
					$dados["conteudo"] .= "[n]Plano(s):[/n] ".$this->buscaPlanosContrato($dados_contrato["id"])."\n";
					$dados["conteudo"] .= "[/d]";
					$dados["conteudo"] .= "[n]Mudança para o endereço:[/n]";
					$dados["conteudo"] .= "[d]";
					if ($contrato_parametros["cliente_endereco_condominio"]) {
						$dados["conteudo"] .= "[n]Condomínio:[/n] {$contrato_parametros["cliente_endereco_condominio"]}\n";
					}
					$dados["conteudo"] .= "[n]Logradouro:[/n] {$contrato_parametros["cliente_endereco_logradouro"]}".($contrato_parametros["cliente_endereco_numero"] ? ", {$contrato_parametros["cliente_endereco_numero"]}" : "")."\n";
					$dados["conteudo"] .= "[n]Complemento:[/n] ".($contrato_parametros["cliente_endereco_complemento_tipo"] != "o" ? $this->complemento_tipos[$contrato_parametros["cliente_endereco_complemento_tipo"]]." " : "").$contrato_parametros["cliente_endereco_complemento_valor"]."\n";
					$dados["conteudo"] .= "[n]Bairro:[/n] {$contrato_parametros["cliente_endereco_bairro"]}\n";
					$dados["conteudo"] .= "[n]Cidade:[/n] {$contrato_parametros["cliente_endereco_cidade"]}\n";
					$dados["conteudo"] .= "[n]CEP:[/n] {$contrato_parametros["cliente_endereco_cep"]}\n";
					$dados["conteudo"] .= "[/d]";
					$dados["sistema"] = true;

					if ($chamado_id) {
						// Registra Etapa
						$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
						$chamados->registraEtapa($dados_chamado, 5, $dados);
					} else {
						// Gera Dados Chamado
						$dados["solicitante"] = "Sistema (Intranet)";
						$dados["categoria"] = $chamado_categoria_id;
						$dados["prioridade"] = 3;
						$dados["tipo"] = 5;
						// Adiciona Chamado
						$chamado_id = $chamados->adicionarChamado($dados_cliente["id"], null, $dados);
						$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
					}

					$chamados->registraEtapa($dados_chamado, 16, array("responsavel_fechamento" => "4,4,0"));

				}

				// Registro da Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");

			}

		} else {
			$this->fechar();
		}

	}

	#################################################
	### Adicionar Aditivo Mudança de Titularidade ###
	#################################################

	function adicionar_aditivo_mt() {

	    $dados_contrato = $this->buscaDadosContrato($_GET["i"]);

		if ($dados_contrato) {

			$dados_cliente = $this->buscaDadosCadastraisCliente($dados_contrato["cliente"]);
			$endereco_cobranca = $this->buscaDadosClienteEnderecoCobranca($dados_contrato["cliente"]);

			if (!$_POST) {

			    $campos_status = array();
				$desabilitados = array();
				$habilitados = array("nome", "tipo", "cpf_cnpj", "nascimento", "contato", "email", "nao_possui_email", "comentarios", "cobranca_condominio", "cobranca_bloco", "cobranca_cep", "cobranca_numero", "cobranca_complemento_tipo", "cobranca_complemento_valor");

			    $botoes = array();
				$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				for ($i = 0; $i < sizeof($desabilitados); $i++) {
					$campos_status[$desabilitados[$i]] = in_array($desabilitados[$i], $habilitados) ? false : true;
				}

				if ($dados_cliente["condominio_id"]) {
					$campos_status["cep"] = true;
					$campos_status["numero"] = true;
				} else {
					$campos_status["bloco"] = true;
				}

				if ($dados_cliente["condominio_id"]) {
					$campos_status["cep"] = true;
					$campos_status["numero"] = true;
				} else {
					$campos_status["bloco"] = true;
				}

				if ($endereco_cobranca["condominio_id"]) {
					$campos_status["cobranca_cep"] = true;
					$campos_status["cobranca_numero"] = true;
				} else {
					$campos_status["cobranca_bloco"] = true;
				}

				if ($endereco_cobranca["mesmo_endereco"]) {
					$campos_status["cobranca_condominio"] = true;
					$campos_status["cobranca_bloco"] = true;
					$campos_status["cobranca_cep"] = true;
					$campos_status["cobranca_numero"] = true;
					$campos_status["cobranca_complemento_tipo"] = true;
					$campos_status["cobranca_complemento_valor"] = true;
				}

				$contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);

				$this->conteudo .= $this->carregaArquivo("contratos.aditivo_mt.adicionar.inc.php");

				// Campos
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp; Adicionar Aditivo (Mudança de Titularidade)&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $dados_contrato["codigo"], $this->conteudo);

				// Endereço Instalação
				if ($dados_cliente["condominio_id"]) {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $dados_cliente["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", $this->geraSelect($this->buscaBlocos($dados_cliente["condominio_id"]), $dados_cliente["bloco_id"]), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", "", $this->conteudo);
				}
				$this->conteudo = str_replace("[cep]", $dados_cliente["cep"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $dados_cliente["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->complemento_tipos,$dados_cliente["complemento_tipo_id"]),$this->conteudo);
				$this->conteudo = str_replace("[complemento_valor]", $dados_cliente["complemento_valor"], $this->conteudo);

				// Dados Cadastrais
				$this->conteudo = str_replace("[id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTiposCliente(), $dados_cliente["tipo"]), $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj]", $dados_cliente["cpf_cnpj"], $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_maxlength]", ($dados_cliente["tipo"] == 2) ? "18" : "14", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_label]", ($dados_cliente["tipo"] == 2) ? "CNPJ" : "CPF", $this->conteudo);
				$this->conteudo = str_replace("[nascimento]", $dados_cliente["nascimento"], $this->conteudo);
				if ($dados_cliente["condominio_id"]) {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $dados_cliente["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", $this->geraSelect($this->buscaBlocos($dados_cliente["condominio_id"]), $dados_cliente["bloco_id"]), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", "", $this->conteudo);
				}
				$this->conteudo = str_replace("[cep]", $dados_cliente["cep"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $dados_cliente["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->complemento_tipos,$dados_cliente["complemento_tipo_id"]),$this->conteudo);
				$this->conteudo = str_replace("[complemento_valor]", $dados_cliente["complemento_valor"], $this->conteudo);
				$this->conteudo = str_replace("[contato]", $dados_cliente["contato"], $this->conteudo);

				// Contatos
				$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($this->buscaContatosTelefones($dados_cliente["id"], true), true), $this->conteudo);
				$this->conteudo = str_replace("[lista_telefones]", serialize($contatos->buscaContatosTelefones($dados_cliente["id"])), $this->conteudo);
				$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($this->buscaContatosEmails($dados_cliente["id"], true), true), $this->conteudo);
				$this->conteudo = str_replace("[lista_emails]", serialize($contatos->buscaContatosEmails($dados_cliente["id"])), $this->conteudo);

				// Endereço Cobrança
				if ($endereco_cobranca["condominio_id"]) {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios(), $endereco_cobranca["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_panorama_condominio]", $this->buscaPanoramaCondominio($endereco_cobranca["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", $this->geraSelect($this->buscaBlocos($endereco_cobranca["condominio_id"]), $endereco_cobranca["bloco_id"]), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_panorama_condominio]", "", $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", "", $this->conteudo);
				}
				$this->conteudo = str_replace("[cobranca_cep]", $endereco_cobranca["cep"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_numero]", $endereco_cobranca["numero"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_tipo]", $this->geraSelect($this->complemento_tipos, $endereco_cobranca["complemento_id"]), $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_valor]", $endereco_cobranca["complemento"], $this->conteudo);
				$this->conteudo = str_replace("[mesmo_endereco_checked]", $endereco_cobranca["mesmo_endereco"], $this->conteudo);

				// Permissão dados duplicados
				$this->conteudo = str_replace("[duplicar_endereco]", $this->permissoes["clientes"]["duplicar_endereco"] ? 1 : 0, $this->conteudo);
				$this->conteudo = str_replace("[duplicar_cpf_cnpj]", $this->permissoes["clientes"]["duplicar_cpf_cnpj"] ? 1 : 0, $this->conteudo);
				$this->conteudo = str_replace("[duplicado_endereco]", $this->existeDuplicidadeEndereco($dados_cliente["id"], $dados_cliente["bloco_id"], $dados_cliente["cep_id"], $dados_cliente["numero"], $dados_cliente["complemento_tipo_id"], $dados_cliente["complemento_valor"]) ? 1 : 0, $this->conteudo);
				$this->conteudo = str_replace("[duplicado_cpf_cnpj]", $this->existeDuplicidadeCPFCNPJ($dados_cliente["id"], $dados_cliente["cpf_cnpj"]) ? 1 : 0, $this->conteudo);

				// Desabilitar Campos
				foreach ($campos_status as $campo => $valor) {
					$this->conteudo = str_replace("[{$campo}_disabled]", ($valor)?"disabled":"", $this->conteudo);
				}

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			} else {

				$dados_tipo = $this->buscaDadosTipo(15);
				$contrato_codigo = $this->geraContratoAditivoCodigo($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["codigo"]);
				$contrato_parametros = addslashes(serialize($this->geraContratoAditivoMudancaTitularidadeParametros($dados_cliente["id"], $dados_contrato["assinaturas"], $dados_contrato["descontos"])));

				if (!$this->ehAutoAprovavelFinanceiro($dados_contrato)) {
					$status_id = 11;
				} elseif (!$this->ehAutoAprovavelComercial($dados_tipo["parametros"], $dados_contrato["servico_parametros"])) {
					$status_id = 13;
				} else {
					$status_id = 17;
				}

				// Fidelidade
				if ($dados_contrato["vigencia_ativa"]) {
					$cor_vigencia = "NULL";
					$cor_multa = intval(preg_replace("/[^0-9]/", "", $dados_contrato["multa"]), 10);
					$cor_termino_fidelidade = "'{$dados_contrato["termino_fidelidade"]}'";
				} else {
					$cor_vigencia = "NULL";
					$cor_multa = "NULL";
					$cor_termino_fidelidade = "NULL";
				}

				// Registra contrato
				$this->bancodedados->query("
					INSERT INTO
						tab_contratos
					SET
						cli_id = {$dados_cliente["id"]},
						cor_codigo = '$contrato_codigo',
						cor_data_geracao = CURDATE(),
						cor_vigencia = $cor_vigencia,
						cor_multa = $cor_multa,
						cor_termino_fidelidade = $cor_termino_fidelidade,
						cor_parametros = '$contrato_parametros',
						cor_sincronizado = 1,
						pls_id = {$dados_contrato["servico_id"]},
						crt_id = {$dados_tipo["id"]},
						crs_id = $status_id,
						cor_pai = {$dados_contrato["id"]}");

				// Busca id contrato
				$id = $this->bancodedados->ultimoIdInserido();

				// Registra Comentário
				$this->registraComentario($id, 1, $_POST["comentario"], $_SESSION["usuario"]);

				// Comentário Auto Aprovação Financeiro
				if ($status_id > 11) {
					$this->registraComentario($id, 4);
				}

				// Comentário Auto Aprovação Comercial
				if ($status_id > 13) {
					$this->registraComentario($id, 12);
				}

				// Comentário Auto Aprovação Técnico
				if ($status_id > 15) {
					$this->registraComentario($id, 15);
				}

				// Registro da Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $id);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i=$id");

			}

		} else {
			$this->fechar();
		}
		
	}

	#####################
	### Gera Contrato ###
	#####################

	function geraContrato($dados_cliente, $dados_contrato_tipo, $servico_id, $assinaturas, $dados_contrato = null, $assinaturas_migradas = null, $migrar_descontos = null, $vinculo = null, $parcelamento = 1, $formas_pagamento = null) {

		$dados_servico = $this->buscaDadosServico($servico_id);
		$formas_pagamento_migracao = array();
		$contrato_assinaturas = array();
		$contrato_descontos = array();
		$planos_migrados = array();

		if (!$dados_contrato) { // Contrato Principal

			$contrato_codigo = $this->geraContratoPrincipalCodigo($dados_cliente["id"]);
			for ($i = 0; $i < sizeof($assinaturas); $i++) {
				$contrato_assinaturas[] = $this->buscaDadosAssinatura($assinaturas[$i]);
				for ($j = 0; $j < sizeof($contrato_assinaturas[$i]["descontos"]); $j++) {
					$contrato_descontos[] = $contrato_assinaturas[$i]["descontos"][$j];
				}
			}
			$contrato_parametros = $this->geraContratoPrincipalParametros($dados_cliente["id"], $contrato_assinaturas, $contrato_descontos, 0, null, $parcelamento, 1);
			$contrato_pai = "NULL";

		} else { // Contrato Aditivo

			if ($dados_contrato_tipo["id"] == 13) { // Migraçao de Assinaturas
				foreach ($assinaturas_migradas as $assinatura => $plano) {
					$plano = explode(",", $plano);
					$planos_migrados[$assinatura] = $plano[0];
				}
				$assinaturas_novas = array();
				foreach ($dados_contrato["assinaturas"] as $assinatura) {
					if ($assinaturas[$assinatura["id"]]) {
						$forma_pagamento_migracao = $this->buscaFormaPagamentoMigracao($planos_migrados[$assinatura["id"]]);
						if ($forma_pagamento_migracao) {
							$formas_pagamento_migracao[] = $forma_pagamento_migracao;
						}
						$assinaturas_novas[] = $this->geraAssinatura($assinatura["id"], $dados_cliente["id"], $planos_migrados[$assinatura["id"]], $assinatura["sistema_id"], $migrar_descontos[$assinatura["id"]]);
					} else {
						$assinaturas_novas[] = $assinatura["id"];
					}
				}
				$assinaturas = $assinaturas_novas;
			}
			$contrato_codigo = $this->geraContratoAditivoCodigo($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["codigo"]);
			for ($i = 0; $i < sizeof($assinaturas); $i++) {
				$contrato_assinaturas[] = $this->buscaDadosAssinatura($assinaturas[$i]);
				for ($j = 0; $j < sizeof($contrato_assinaturas[$i]["descontos"]); $j++) {
					$contrato_descontos[] = $contrato_assinaturas[$i]["descontos"][$j];
				}
			}
			$contrato_parametros = $this->geraContratoAditivoAlterarAssinaturasParametros($this->buscaDadosUltimoContrato($dados_contrato["cliente"], $dados_contrato["id"]), $contrato_assinaturas, $contrato_descontos);
			$contrato_pai = $dados_contrato["id"];

		}

		if ($dados_contrato) {
		    
			if ($_POST["contrato_fidelidade"] == 2) {
				$cor_vigencia = "NULL";
				$cor_multa = "NULL";
				$cor_termino_fidelidade = "NULL";
			} elseif (count($formas_pagamento_migracao)) {
				$cor_vigencia = "NULL";
				$cor_multa = "NULL";
				$cor_termino_fidelidade = "NULL";
			} elseif ($_POST["vigencia"] && $_POST["valor_multa"]) {
				$cor_vigencia = intval(preg_replace("/[^0-9]/", "", $_POST["vigencia"]), 10);
				$cor_multa = intval(preg_replace("/[^0-9]/", "", $_POST["valor_multa"]), 10);
				$cor_termino_fidelidade = "NULL";
			} elseif ($dados_contrato["vigencia_ativa"]) {
				$cor_vigencia = "NULL";
				$cor_multa = intval(preg_replace("/[^0-9]/", "", $dados_contrato["multa"]), 10);
				$cor_termino_fidelidade = "'{$dados_contrato["termino_fidelidade"]}'";
			} else {
				$cor_vigencia = "NULL";
				$cor_multa = "NULL";
				$cor_termino_fidelidade = "NULL";
			}
			
		} else {
		    
			$cor_vigencia = $contrato_parametros["contrato_multa_rescisao"] && $contrato_parametros["contrato_prazo_vigencia"] ? $contrato_parametros["contrato_prazo_vigencia"] : "NULL";
			$cor_multa = $contrato_parametros["contrato_multa_rescisao"] && $contrato_parametros["contrato_prazo_vigencia"] ? preg_replace("/[^0-9]/", "", $contrato_parametros["contrato_multa_rescisao"]) : "NULL";
			$cor_termino_fidelidade = "NULL";
			
		}

		$contrato_parametros = addslashes(serialize($contrato_parametros));

		// Data Confirmaçao
		$cor_confirmacao = date("'Y-m-d'");

		// Registra contrato
		$this->bancodedados->query("
			INSERT INTO
				tab_contratos
			SET
				cli_id = {$dados_cliente["id"]},
				cor_codigo = '$contrato_codigo',
				cor_data_geracao = CURDATE(),
				cor_data_confirmacao = $cor_confirmacao,
				cor_vigencia = $cor_vigencia,
				cor_multa = $cor_multa,
				cor_termino_fidelidade = $cor_termino_fidelidade,
				cor_parametros = '$contrato_parametros',
				cor_sincronizado = 1,
				pls_id = $servico_id,
				crt_id = {$dados_contrato_tipo["id"]},
				crs_id = 11,
				cor_pai = $contrato_pai");

		// Busca id contrato
		$id = $this->bancodedados->ultimoIdInserido();

		// Registra Comentário
		$this->registraComentario($id, 1, null, $_SESSION["usuario"]);

		// Insere Formas de Pagto Migração
		foreach ($formas_pagamento_migracao as $forma_pagamento_id) {
			$this->bancodedados->query("
				INSERT INTO
					rel_fop_cor_estacontido
				SET
					fop_id = $forma_pagamento_id,
					cor_id = $id");
		}

		// Associa Assinaturas
		for ($i = 0; $i < sizeof($contrato_assinaturas); $i++) {
			$this->bancodedados->query("
				INSERT INTO
					rel_ass_cor_estacontido
				SET
					ass_id = {$contrato_assinaturas[$i]["id"]},
					cor_id = $id");
		}

		// Associa Descontos
		for ($i = 0; $i < sizeof($contrato_descontos); $i++) {
			$this->bancodedados->query("
				INSERT INTO
					rel_dec_cor_estacontido
				SET
					dec_id = {$contrato_descontos[$i]["id"]},
					cor_id = $id");
		}

		// Atualiza Situação Cliente
		$this->atualizaSituacaoCliente($dados_cliente["id"], 5); // Fechado (Com Contrato)

		if ($dados_contrato_tipo["id"] == 11) {
			$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);
			$clientes_iptv->insereIptv($id);
		}
		
		$assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($id);

		$comodatos = 0;
		
		foreach ($assinaturas_adicionadas as $assinatura_adicionada) {
			
			// Atualiza Data Fechamento
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
				SET
					ass_data_fechamento = CURDATE(),
					ass_data_confirmacao = $cor_confirmacao
				WHERE
					ass_id = {$assinatura_adicionada["ass_id"]}");

			// Migração só gera venda se for pós-venda, se não, só se não for pós-venda
			if ($dados_contrato_tipo["id"] != 14) {
				$this->geraVenda($dados_cliente, $this->buscaDadosAssinatura($assinatura_adicionada["ass_id"]), $id);
			}
			
			$equipamentos = $this->buscaAssinaturasEquipamentos($assinatura_adicionada["ass_id"]);
			
			foreach ($equipamentos as $equipamento){
				if (!$assinatura_adicionada["ass_migrada"] || !$this->buscaComodatoAssinatura($assinatura_adicionada["ass_migrada"], $equipamento["equipamento"])) {

					$comodatos++;

					$this->bancodedados->query("
						INSERT INTO
							tab_clientes_comodatos
						SET
							equ_id = {$equipamento["equipamento"]},
							cor_id = $id,
							cli_id = {$dados_cliente["id"]},
							ccs_id = 1,
							ass_id = {$assinatura_adicionada["ass_id"]}");
					
					$comodato_id = $this->bancodedados->ultimoIdInserido();
					
					$this->registraComentarioClienteComodato($comodato_id, "<b>Ação:</b> Adicionado");

				}
			}
			
		}

		// Gera Vínculo
		if ($vinculo) {
			$this->bancodedados->query("
				INSERT INTO
					tab_contratos_vinculos
				SET
					cor_id = {$vinculo["contrato_id"]},
					cor_filho = $id,
					pvt_id = {$vinculo["vinculo_tipo_id"]}");
		}

		// Gera Contratos Vinculados
		$assinaturas = $this->buscaAssinaturasVinculadas($assinaturas_adicionadas, $servico_id);
		foreach ($assinaturas as $vinculo_servico_id => $assinaturas_vinculadas) {
			$this->geraContrato($dados_cliente, $this->buscaDadosTipo(11), $vinculo_servico_id, $assinaturas_vinculadas["assinaturas"], null, null, null, array("contrato_id" => $id, "vinculo_tipo_id" => $assinaturas_vinculadas["tipo_id"]), 1, $formas_pagamento);
		}

		// Vincula Formas Pagamento
		if ($formas_pagamento[$servico_id]) {
			foreach ($formas_pagamento[$servico_id] as $forma_pagamento) {
				$this->bancodedados->query("INSERT INTO rel_fop_cor_estacontido (fop_id, cor_id) VALUES ($forma_pagamento, $id)");
			}
		}

		$dados_contrato = $this->buscaDadosContrato($id);

		if ($this->ehAutoReprovavelFinanceiro($dados_cliente)) {
			// Em Aberto (Financeiro, Reprovado)
			$status_id = 12;
		} elseif ($dados_contrato_tipo["id"] != 13 && !$this->ehAutoAprovavelFinanceiro($dados_contrato)) {
			// Em Aberto (Financeiro, Aguardando Aprovação)
			$status_id = 11;
		} elseif (!$this->ehAutoAprovavelComercial($dados_contrato_tipo["parametros"], $dados_servico["parametros"])) {
			// Em Aberto (Comercial, Aguardando Aprovação)
			$status_id = 13;
		} elseif (!$this->ehAutoAprovavelTecnico($dados_cliente, $dados_contrato_tipo["parametros"], $servico_id, $dados_servico["parametros"], $contrato_parametros, $dados_contrato_tipo["id"])) {
			// Em Aberto (Técnico, Aguardando Aprovação)
			$status_id = 15;
		} else {
			// Em Aberto (Aguardando Ativação)
			$status_id = 17;
		}

		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				crs_id = {$status_id}
			WHERE
				cor_id = {$id}");

		// Comentário Auto Reprovação Financeiro
		if ($status_id == 12) {
			$this->registraComentario($id, 5);
		}

		// Comentário Auto Aprovação Financeiro
		if ($status_id > 12) {
			$this->registraComentario($id, 4);
		}

		// Comentário Auto Aprovação Comercial
		if ($status_id > 13) {
			$this->registraComentario($id, 12);
		}

		// Comentário Auto Aprovação Técnico
		if ($status_id > 15) {
			$this->registraComentario($id, 15);
			// Gera Visita
			if (!$vinculo && ($dados_contrato_tipo["id"] != 13 || $comodatos > 0) && $dados_servico["parametros"]["gera_visita"] && $assinaturas_adicionadas) {
				if ($dados_cliente["origem_id"] == 1) {
					$visita_id = $this->geraVisita($dados_cliente["id"], $id, $dados_contrato_tipo["id"] != 13 ? 1 : 3 );
					$this->registraComentarioVisita($visita_id, "<b>Contrato Aprovado</b>: {$contrato_codigo}", 16, $_SESSION["usuario"]);
					if($dados_contrato_tipo["id"] == 13){
						$this->abreChamadoVisitaMigracao($this->buscaDadosContrato($id), $equipamentos);
					}
				}
			}
		}

		// Insere Chamado Residencial (Vistoria)
		if ($dados_contrato_tipo["id"] == 11 && $status_id == 15 && in_array($servico_id, array(3, 28, 33, 34))) {
			$this->insereChamadoVistoriaResidencial($dados_cliente["id"], $id, $contrato_codigo);
		}

		return $id;

	}

	#######################
	### Gera Assinatura ###
	#######################

	function geraAssinatura($assinatura_migrada_id, $cliente_id, $plano_id, $sistema_id, $migrar_descontos = null) {
	    
		// Busca Dados
		$dados_plano = $this->buscaDadosPlano($plano_id);
		$dados_migrada = $this->buscaDadosAssinatura($assinatura_migrada_id);

		$dados_migrada["vigencia"] = "NULL";
		$dados_migrada["multa"] = "NULL";
		
		// Insere Assinatura
		$this->bancodedados->query("
			INSERT INTO
				tab_assinaturas
			SET
				cli_id = $cliente_id,
				pla_id = $plano_id,
				pli_id = $sistema_id,
				ass_parametros = '{$dados_plano["parametros"]}',
				ass_instalacao = 0,
				ass_mensalidade = {$dados_plano["mensalidade"]},
				ass_vigencia = {$dados_migrada["vigencia"]},
				ass_multa = {$dados_migrada["multa"]},
				ass_data_cadastro = CURDATE(),
				ass_data_fechamento = CURDATE(),
				ass_data_confirmacao = CURDATE(),
				ast_id = 1,
				ass_pai = NULL,
				ass_migrada = $assinatura_migrada_id");
		
		$assinatura_id = $this->bancodedados->ultimoIdInserido();
		
		// Adiciona Assinaturas Vinculadas
		foreach ($dados_plano["vinculos"] as $vinculo) {

			$dados_assinatura = $this->buscaAssinatura($cliente_id, $vinculo["plano_id"], "1, 2");

			if ($vinculo["aplicacao_id"] == 2 && $dados_assinatura) {

				// Cria Vínculo: Aplicável 1 Por Cliente e Assinatura Existente
				$this->bancodedados->query("
					INSERT INTO
						tab_assinaturas_vinculos
					SET
						ass_id = $assinatura_id,
						ass_filha = {$dados_assinatura["id"]},
						pvt_id = {$vinculo["tipo_id"]}");

			} else {

				$assinatura_pai = $dados_plano["servico_id"] == $vinculo["servico_id"] ? $assinatura_id : null;

				$vinculo["assinatura_id"] = $assinatura_id;

				$this->adicionarAssinatura($cliente_id, $vinculo["plano_id"], $sistema_id, $assinatura_pai, $vinculo);

			}
		}
		
		// Data Cancelamento
		$data_cancelamento = $this->buscaDataCancelamento($cliente_id, $dados_plano["servico_id"]);
		
		// Inicializa array descontos
		$descontos_assinatura = array();
		$descontos_aplicados = array();
		
		// Herda descontos do condomínio
		$condominio_id = $this->buscaClienteCondominio($cliente_id);
		
		if ($condominio_id) {

			$query = $this->bancodedados->query("
				SELECT
					tab_descontos_concessoes.des_id AS desconto_id,
					tab_descontos_concessoes.det_id AS tipo_id,
					tab_descontos_concessoes.dec_instalacao AS instalacao,
					tab_descontos_concessoes.dec_mensalidade AS mensalidade,
					tab_descontos_concessoes.dec_data_termino AS data_termino,
					tab_descontos_concessoes.dec_pai AS desconto_pai_id,
					tab_descontos.des_parametros AS parametros
				FROM
					tab_descontos_concessoes
					INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				WHERE
					tab_descontos_concessoes.con_id = $condominio_id AND".($descontos_assinatura ? "
					tab_descontos_concessoes.des_id NOT IN (".implode(",", $descontos_assinatura).") AND" : "")."
					tab_descontos_concessoes.dec_mensalidade > 0 AND
					tab_descontos_concessoes.dcs_id = 5");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				$parametros = unserialize($dados["parametros"]);
				$data_carencia_minima = date("Ymd", strtotime("{$parametros["carencia"]} days"));

				if ((!$descontos_assinatura || count(array_intersect($parametros["descontos"], $descontos_assinatura)) == 0) &&
						(!$parametros["planos"] || in_array($plano_id, $parametros["planos"])) &&
						(!$descontos_aplicados || count(array_intersect($parametros["descontos"], $descontos_aplicados)) == 0) &&
						(!$parametros["carencia"] || !$data_cancelamento || $data_carencia_minima >= $data_cancelamento)) {

					$dados["instalacao"] = $dados["instalacao"] ? $dados["instalacao"] : "NULL";
					$dados["mensalidade"] = $dados["mensalidade"] ? $dados["mensalidade"] : "NULL";
					$dados["data_termino"] = $dados["dec_data_termino"] ? "'{$dados["data_termino"]}'" : "NULL";
					$dados["desconto_pai_id"] = $dados["desconto_pai_id"] ? $dados["desconto_pai_id"] : "NULL";

					$this->bancodedados->query("
						INSERT INTO
							tab_descontos_concessoes
						SET
							dec_data_cadastro = CURDATE(),
							des_id = {$dados["desconto_id"]},
							det_id = {$dados["tipo_id"]},
							ass_id = $assinatura_id,
							dec_instalacao = {$dados["instalacao"]},
							dec_mensalidade = {$dados["mensalidade"]},
							dec_data_termino = {$dados["data_termino"]},
							dec_pai = {$dados["desconto_pai_id"]},
							dcs_id = 2");

					$descontos_aplicados[] = $dados["desconto_id"];

				}

			}

		}
		
		// Herda Descontos do Plano
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.des_id AS desconto_id,
				tab_descontos_concessoes.det_id AS tipo_id,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes.dec_data_termino AS data_termino,
				tab_descontos_concessoes.dec_pai AS desconto_pai_id,
				tab_descontos.des_parametros AS parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
				tab_descontos_concessoes.pla_id = $plano_id AND
				tab_descontos_concessoes.dcs_id = 5".($dados_plano["tipo_migracao"] && !$dados_plano["tipo_principal"] ? "" : " AND
				tab_descontos_concessoes.det_id = 4"));
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);

			$descontos_assinatura[$dados["desconto_id"]] = $dados["desconto_id"];

			if (!$descontos_aplicados || count(array_intersect($dados["parametros"]["descontos"], $descontos_aplicados)) == 0) {

				$dados["instalacao"] = $dados["instalacao"] ? $dados["instalacao"] : "NULL";
				$dados["mensalidade"] = $dados["mensalidade"] ? $dados["mensalidade"] : "NULL";
				if ($dados["parametros"]["validade_tipo"] && $dados["parametros"]["validade_tipo"] == 2) {
					$dados["validade"] = "NULL";
					$dados["data_termino"] = $dados["parametros"]["validade"] ? $this->formataData($dados["parametros"]["validade"]) : "NULL";
				} else {
					$dados["validade"] = $dados["parametros"]["validade"] ? $dados["parametros"]["validade"] : "NULL";
					$dados["data_termino"] = "NULL";
				}
				$dados["desconto_pai_id"] = $dados["desconto_pai_id"] ? $dados["desconto_pai_id"] : "NULL";
				
				$this->bancodedados->query("
					INSERT INTO
						tab_descontos_concessoes
					SET
						dec_data_cadastro = CURDATE(),
						des_id = {$dados["desconto_id"]},
						det_id = {$dados["tipo_id"]},
						ass_id = $assinatura_id,
						dec_instalacao = {$dados["instalacao"]},
						dec_mensalidade = {$dados["mensalidade"]},
						dec_data_termino = {$dados["data_termino"]},
						dec_validade = {$dados["validade"]},
						dec_pai = {$dados["desconto_pai_id"]},
						dcs_id = 2");

				$descontos_aplicados[] = $dados["desconto_id"];

			}

		}
		
		$this->adicionaAjusteProgramado($assinatura_id, $cliente_id, $plano_id);
		
		return $assinatura_id;
		
	}

	##################
	### Gera Venda ###
	##################

	function geraVenda($dados_cliente, $dados_assinatura, $contrato_id, $responsavel_id = null) {
	    
		// Verifica se é Migrada
		if ($dados_assinatura["migrada"]) {
			$assinatura_migrada = $this->buscaDadosAssinatura($dados_assinatura["migrada"]);
		}
		
		// Busca Venda
		$query = $this->bancodedados->query("
			SELECT
				ven_id AS id,
				usu_id AS responsavel
			FROM
				tab_vendas
			WHERE
				ass_id = {$dados_assinatura["id"]}");
		
		if (($venda = $this->bancodedados->resultadoArray($query)) != false) {
			$id = $venda["id"];
			$responsavel = $venda["responsavel"];
		} elseif ($responsavel_id) {
			$responsavel = $responsavel_id;
		} else {
			$responsavel = $this->buscaResponsavel($dados_cliente["id"], $dados_assinatura["data_fechamento"]);
		}
		
		if ($assinatura_migrada && !in_array($dados_assinatura["plano_servico_id"], array(10, 11, 12, 13, 14, 15, 16, 30))) {
			if (!$responsavel || !$this->usuarioPosVenda($responsavel)) {
				if ($this->ehUsuarioPosVenda()) {
					$responsavel = $_SESSION["usuario"];
				}
			}
		} elseif ($dados_assinatura["plano_servico_id"] == 21) {
			if ($this->ehUsuarioPosVenda()) {
				if (!$responsavel || !$this->usuarioPosVenda($responsavel)) {
					$responsavel = $_SESSION["usuario"];
				}
			}
		}
		
		if (!$responsavel) {
			$responsavel = $_SESSION["usuario"];
		}
		
		// Atualiza responsável cliente
		if ($responsavel) {
			$this->bancodedados->query("
				UPDATE
					tab_clientes
				SET
					usu_id = $responsavel
				WHERE
					cli_id = '{$dados_cliente["id"]}'");
		}
		
		$indicador = $this->buscaClienteIndicador($dados_cliente["id"]);
		$comissoes = $this->calculaComissoes($dados_assinatura, $assinatura_migrada, $responsavel, $dados_cliente["condominio_id"], $indicador, $contrato_id);
		
		if ($responsavel) {
		// Gera venda se não existir e se há comissão
		if (!$venda) {
			$this->bancodedados->query("
				INSERT INTO
					tab_vendas
				SET
					ven_datahora = NOW(),
					usu_id = $responsavel,
					ass_id = {$dados_assinatura["id"]},
					cor_id = $contrato_id,
					ven_pontuacao = 0");
			$id = $this->bancodedados->ultimoIdInserido();
		}
		
		// Gera Comissões
		if ($comissoes) {

			// Apaga comissões antigas
			$this->bancodedados->query("
				DELETE
					tab_vendas_comissoes_comentarios
				FROM
					tab_vendas_comissoes
					INNER JOIN tab_vendas_comissoes_comentarios ON (tab_vendas_comissoes.vec_id = tab_vendas_comissoes_comentarios.vec_id)
				WHERE
					ven_id = $id");

			$this->bancodedados->query("
				DELETE FROM
					tab_vendas_comissoes
				WHERE
					ven_id = $id");

			// Insere Comissões no Banco
			foreach ($comissoes as $tipo => $valor) {
				if ($valor > 0) {

					$beneficiado = (in_array($tipo, array(1, 3, 4, 6))) ? $responsavel : $indicador;

					$this->bancodedados->query("
						INSERT INTO
							tab_vendas_comissoes
						SET
							ven_id = $id,
							vct_id = $tipo,
							usu_id = $beneficiado,
							vec_valor = $valor");

				}
			}

		}
		
	}
	}

	################################
	### Gera Parâmetros Contrato ###
	################################

	function geraContratoPrincipalParametros($cliente_id, $assinaturas, $descontos, $vigencia = 0, $versao = null, $parcelamento = 1, $parcelamento_forma = 3) {
	    
		/**
		 * @var contratos_parametros $contrato_parametros
		 */
		$contrato_parametros = controle::instanciaObjeto("contratos.parametros", $this->bancodedados);
		
		$contrato_parametros->geraContratoPrincipal($cliente_id, $assinaturas, $descontos, $vigencia, $versao, $parcelamento, $parcelamento_forma);
		
		return $contrato_parametros->dados;
		
	}

	function geraContratoAditivoAlterarAssinaturasParametros($contrato, $assinaturas, $descontos, $versao = null) {
	    
		/**
		 * @var contratos_parametros $contrato_parametros
		 */
		$contrato_parametros = controle::instanciaObjeto("contratos.parametros", $this->bancodedados);
		
		$contrato_parametros->geraContratoAditivoAlterarAssinaturas($contrato, $assinaturas, $descontos, $versao);
		
		return $contrato_parametros->dados;
		
	}

	function geraContratoAditivoMudancaEnderecoParametros($cliente_id, $assinaturas, $descontos, $parametros_antigos = null) {
	    
		/**
		 * @var contratos_parametros $contrato_parametros
		 */
		$contrato_parametros = controle::instanciaObjeto("contratos.parametros", $this->bancodedados);

		$contrato_parametros->geraContratoAditivoMudancaEndereco($cliente_id, $_POST, $assinaturas, $descontos, $parametros_antigos);
		
		return $contrato_parametros->dados;
		
	}

	function geraContratoAditivoMudancaTitularidadeParametros($cliente_id, $assinaturas, $descontos, $parametros_antigos = null) {
	    
		/**
		 * @var contratos_parametros $contrato_parametros
		 */
		$contrato_parametros = controle::instanciaObjeto("contratos.parametros", $this->bancodedados);
		
		$contrato_parametros->geraContratoAditivoMudancaTitularidade($cliente_id, $_POST, $assinaturas, $descontos, $parametros_antigos);
		
		return $contrato_parametros->dados;
		
	}

	##############################
	### Migra Endereço Cliente ###
	##############################

	function migraEnderecoCliente($contrato_id) {
	    
		$dados_contrato = $this->buscaDadosContrato($contrato_id);
		
		// Atualiza Endereço Cliente
		if ($dados_contrato["parametros"]["contrato_versao"] >= 9) {

			// Novo Endereço
			$bloco = $dados_contrato["parametros"]["cliente_endereco_bloco_id"] ? $dados_contrato["parametros"]["cliente_endereco_bloco_id"] : "NULL";
			$bloco_nome = $dados_contrato["parametros"]["cliente_endereco_bloco_nome"];
			$condominio = $dados_contrato["parametros"]["cliente_endereco_condominio_id"] ? $dados_contrato["parametros"]["cliente_endereco_condominio_id"] : "NULL";
			$condominio_nome = $dados_contrato["parametros"]["cliente_endereco_condominio_nome"];
			$cep = $dados_contrato["parametros"]["cliente_endereco_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["cliente_endereco_cep"]);
			$numero = $dados_contrato["parametros"]["cliente_endereco_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["cliente_endereco_numero"]);
			$complemento = "{$dados_contrato["parametros"]["cliente_endereco_complemento_tipo"]}{$dados_contrato["parametros"]["cliente_endereco_complemento_valor"]}";
			$complemento_valor = $dados_contrato["parametros"]["cliente_endereco_complemento_valor"];

			// Endereço Antigo
			$antigo_bloco_nome = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_bloco_nome"];
			$antigo_condominio_nome = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_condominio_nome"];
			$antigo_complemento_valor = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_complemento_valor"];

		} else {

			// Novo Endereço
			$bloco = $dados_contrato["parametros"]["final"]["bloco_id"] ? $dados_contrato["parametros"]["final"]["bloco_id"] : "NULL";
			$bloco_nome = $dados_contrato["parametros"]["final"]["bloco_nome"];
			$condominio = $dados_contrato["parametros"]["final"]["condominio_id"] ? $dados_contrato["parametros"]["final"]["condominio_id"] : "NULL";
			$condominio_nome = $dados_contrato["parametros"]["final"]["condominio_nome"];
			$cep = $dados_contrato["parametros"]["final"]["condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["final"]["cep"]);
			$numero = $dados_contrato["parametros"]["final"]["condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["final"]["numero"]);
			$complemento = $dados_contrato["parametros"]["final"]["complemento"];
			$complemento_valor = $dados_contrato["parametros"]["final"]["complemento_valor"];

			// Endereço Antigo
			$antigo_bloco_nome = $dados_contrato["parametros"]["inicial"]["bloco_nome"];
			$antigo_condominio_nome = $dados_contrato["parametros"]["inicial"]["condominio_nome"];
			$antigo_complemento_valor = $dados_contrato["parametros"]["inicial"]["complemento_valor"];

		}
		
		$numero = $numero ? $numero : "NULL";
		
		$this->bancodedados->query("
			UPDATE
				tab_clientes
			SET
				cob_id = $bloco,
				con_id = $condominio,
				cep_id = $cep,
				cli_numero = $numero,
				cli_complemento = '$complemento'
			WHERE
				cli_id = {$dados_contrato["cliente"]}");
		
		// Atualiza Endereço Cobrança
		$this->bancodedados->query("
			DELETE FROM
				tab_clientes_cobranca_dados
			WHERE
				cli_id = {$dados_contrato["cliente"]}");

		if ($dados_contrato["parametros"]["contrato_versao"] >= 9) {
			$cobranca_bloco = $dados_contrato["parametros"]["cliente_endereco_cobranca_bloco_id"] ? $dados_contrato["parametros"]["cliente_endereco_cobranca_bloco_id"] : "NULL";
			$cobranca_condominio = $dados_contrato["parametros"]["cliente_endereco_cobranca_condominio_id"] ? $dados_contrato["parametros"]["cliente_endereco_cobranca_condominio_id"] : "NULL";
			$cobranca_cep = $dados_contrato["parametros"]["cliente_endereco_cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["cliente_endereco_cobranca_cep"]);
			$cobranca_numero = $dados_contrato["parametros"]["cliente_endereco_cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["cliente_endereco_cobranca_numero"]);
			$cobranca_complemento = "{$dados_contrato["parametros"]["cliente_endereco_cobranca_complemento_tipo"]}{$dados_contrato["parametros"]["cliente_endereco_cobranca_complemento_valor"]}";
		} else {
			$cobranca_bloco = $dados_contrato["parametros"]["final"]["cobranca_bloco_id"] ? $dados_contrato["parametros"]["final"]["cobranca_bloco_id"] : "NULL";
			$cobranca_condominio = $dados_contrato["parametros"]["final"]["cobranca_condominio_id"] ? $dados_contrato["parametros"]["final"]["cobranca_condominio_id"] : "NULL";
			$cobranca_cep = $dados_contrato["parametros"]["final"]["cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["final"]["cobranca_cep"]);
			$cobranca_numero = $dados_contrato["parametros"]["final"]["cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["final"]["cobranca_numero"]);
			$cobranca_complemento = $dados_contrato["parametros"]["final"]["cobranca_complemento"];
		}

		$cobranca_numero = $cobranca_numero ? $cobranca_numero : "NULL";
		
		if ($cobranca_bloco != $bloco || $cobranca_condominio != $condominio || $cobranca_cep != $cep || $cobranca_numero != $numero || $cobranca_complemento != $complemento) {
			$this->bancodedados->query("
				INSERT INTO
					tab_clientes_cobranca_dados
				SET
					cli_id = '{$dados_contrato["cliente"]}',
					cob_id = $cobranca_bloco,
					cep_id = $cobranca_cep,
					ccd_numero = $cobranca_numero,
					ccd_complemento = '$cobranca_complemento'");
		}
		
		// Exporta Pontos
		if (($bloco != "NULL") && ($dados_interface = $this->buscaInterfaceBloco($bloco)) != false) {

			// Instancia ClientesPontos
			$clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);

			// Busca pontos do cliente
			$query = $this->bancodedados->query("
				SELECT
					tab_clientes_pontos.clp_id AS id,
					tab_clientes_pontos.clp_regra AS regra,
					tab_servidores_interfaces.sei_id AS interface_id,
					tab_servidores_interfaces.ser_id AS servidor_id,
					tab_assinaturas.ass_id AS assinatura_id
				FROM
					tab_assinaturas
					INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
					LEFT OUTER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
				WHERE
					tab_assinaturas.cli_id = {$dados_contrato["cliente"]} AND
					tab_clientes_pontos.cps_id IN (1, 2)");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				if ($dados["interface_id"] != $dados_interface["id"]) {
					
					// Atualiza interface/ip do ponto
					$regra = $dados["servidor_id"] == $dados_interface["servidor_id"] ? $dados["regra"] : $this->buscaProximaRegra($dados_interface["servidor_id"]);
					$ip = $clientes_pontos->buscaIPLivre($dados_interface["id"], $dados["assinatura_id"], $dados["id"]);
					$ip = $ip ? "'$ip'" : "NULL";
					
					$this->bancodedados->query("
						UPDATE
							tab_clientes_pontos
						SET
							clp_regra = $regra,
							sei_id = {$dados_interface["id"]},
							clp_ip = $ip,
							clp_publicado = 0
						WHERE
							clp_id = {$dados["id"]}");
					
					// Registra Comentário Ponto
					$this->registraComentarioClientePonto($dados["id"], "<b>Mudança de endereço:</b> <b>{$antigo_condominio_nome}, ".(($antigo_bloco_nome && $antigo_bloco_nome <> 'Único') ? "$antigo_bloco_nome / " : "")."{$antigo_complemento_valor}</b> para <b>{$condominio_nome}, ".(($bloco_nome && $bloco_nome <> 'Único') ? "$bloco_nome / " : "")."{$complemento_valor}</b>", $_SESSION["usuario"]);
					
				}

			}

		} else {

			$this->bancodedados->query("
				UPDATE
					tab_clientes_pontos
					INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				SET
					sei_id = NULL,
					clp_ip = NULL,
					clp_publicado = 0
				WHERE
					tab_assinaturas.cli_id = {$dados_contrato["cliente"]} AND
					tab_clientes_pontos.cps_id IN (1, 2) AND
					tab_planos.pls_id NOT IN (3, 5, 33, 34, 35, 36)");

		}
		
	}

	#################################
	### Desmigra Endereço Cliente ###
	#################################

	function desmigraEnderecoCliente($contrato_id) {
	    
		$dados_contrato = $this->buscaDadosContrato($contrato_id);
		$dados_cliente = $this->buscaDadosCliente($dados_contrato["cliente"]);
		
		// Atualiza Endereço Cliente
		if ($dados_contrato["parametros"]["contrato_versao"] >= 9) {

			// Novo Endereço
			$bloco_nome = $dados_contrato["parametros"]["cliente_endereco_bloco_nome"];
			$condominio_nome = $dados_contrato["parametros"]["cliente_endereco_condominio_nome"];
			$complemento_valor = $dados_contrato["parametros"]["cliente_endereco_complemento_valor"];

			// Endereço Antigo
			$antigo_bloco = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_bloco_id"] ? $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_bloco_id"] : "NULL";
			$antigo_bloco_nome = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_bloco_nome"];
			$antigo_condominio = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_condominio_id"] ? $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_condominio_id"] : "NULL";
			$antigo_condominio_nome = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_condominio_nome"];
			$antigo_cep = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cep"]);
			$antigo_numero = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_numero"]);
			$antigo_complemento = "{$dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_complemento_tipo"]}{$dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_complemento_valor"]}";
			$antigo_complemento_valor = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_complemento_valor"];

		} else {

			// Novo Endereço
			$bloco_nome = $dados_contrato["parametros"]["final"]["bloco_nome"];
			$condominio_nome = $dados_contrato["parametros"]["final"]["condominio_nome"];
			$complemento_valor = $dados_contrato["parametros"]["final"]["complemento_valor"];

			// Endereço Antigo
			$antigo_bloco = $dados_contrato["parametros"]["inicial"]["bloco_id"] ? $dados_contrato["parametros"]["inicial"]["bloco_id"] : "NULL";
			$antigo_bloco_nome = $dados_contrato["parametros"]["inicial"]["bloco_nome"];
			$antigo_condominio = $dados_contrato["parametros"]["inicial"]["condominio_id"] ? $dados_contrato["parametros"]["inicial"]["condominio_id"] : "NULL";
			$antigo_condominio_nome = $dados_contrato["parametros"]["inicial"]["condominio_nome"];
			$antigo_cep = $dados_contrato["parametros"]["inicial"]["condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["inicial"]["cep"]);
			$antigo_numero = $dados_contrato["parametros"]["inicial"]["condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["inicial"]["numero"]);
			$antigo_complemento = $dados_contrato["parametros"]["inicial"]["complemento"];
			$antigo_complemento_valor = $dados_contrato["parametros"]["inicial"]["complemento_valor"];

		}
		
		$antigo_numero = $antigo_numero ? $antigo_numero : "NULL";
		
		$this->bancodedados->query("
			UPDATE
				tab_clientes
			SET
				cob_id = $antigo_bloco,
				con_id = $antigo_condominio,
				cep_id = $antigo_cep,
				cli_numero = $antigo_numero,
				cli_complemento = '$antigo_complemento'
			WHERE
				cli_id = {$dados_cliente["id"]}");
		
		// Atualiza Endereço Cobrança
		$this->bancodedados->query("
			DELETE FROM
				tab_clientes_cobranca_dados
			WHERE
				cli_id = {$dados_cliente["id"]}");
		
		if ($dados_contrato["parametros"]["contrato_versao"] >= 9) {
			$cobranca_bloco = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_bloco_id"] ? $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_bloco_id"] : "NULL";
			$cobranca_condominio = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_condominio_id"] ? $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_condominio_id"] : "NULL";
			$cobranca_cep = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_cep"]);
			$cobranca_numero = $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_numero"]);
			$cobranca_complemento = "{$dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_complemento_tipo"]}{$dados_contrato["parametros"]["endereco_antigo"]["cliente_endereco_cobranca_complemento_valor"]}";
		} else {
			$cobranca_bloco = $dados_contrato["parametros"]["inicial"]["cobranca_bloco_id"] ? $dados_contrato["parametros"]["inicial"]["cobranca_bloco_id"] : "NULL";
			$cobranca_condominio = $dados_contrato["parametros"]["inicial"]["cobranca_condominio_id"] ? $dados_contrato["parametros"]["inicial"]["cobranca_condominio_id"] : "NULL";
			$cobranca_cep = $dados_contrato["parametros"]["inicial"]["cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["inicial"]["cobranca_cep"]);
			$cobranca_numero = $dados_contrato["parametros"]["inicial"]["cobranca_condominio_id"] ? "NULL" : preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["inicial"]["cobranca_numero"]);
			$cobranca_complemento = $dados_contrato["parametros"]["inicial"]["cobranca_complemento"];
		}
		
		$cobranca_numero = $cobranca_numero ? $cobranca_numero : "NULL";

		if ($cobranca_bloco != $antigo_bloco || $cobranca_condominio != $antigo_condominio || $cobranca_cep != $antigo_cep || $cobranca_numero != $antigo_numero || $cobranca_complemento != $antigo_complemento) {
			$this->bancodedados->query("
				INSERT INTO
					tab_clientes_cobranca_dados
				SET
					cli_id = '{$dados_cliente["id"]}',
					cob_id = $cobranca_bloco,
					cep_id = $cobranca_cep,
					ccd_numero = $cobranca_numero,
					ccd_complemento = '$cobranca_complemento'");
		}
		
		// Exporta Pontos
		if ($antigo_bloco && ($dados_interface = $this->buscaInterfaceBloco($antigo_bloco))) {

			// Instancia ClientesPontos
			$clientes_pontos = controle::instanciaObjeto("clientes_pontos", $this->bancodedados, $this->permissoes, false);

			// Busca pontos do cliente
			$query = $this->bancodedados->query("
				SELECT
					tab_clientes_pontos.clp_id AS id,
					tab_clientes_pontos.clp_regra AS regra,
					tab_servidores_interfaces.sei_id AS interface_id,
					tab_servidores_interfaces.ser_id AS servidor_id
				FROM
					tab_assinaturas
					INNER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
					LEFT OUTER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
				WHERE
					tab_assinaturas.cli_id = {$dados_cliente["id"]} AND
					tab_clientes_pontos.cps_id IN (1, 2)");

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				if ($dados["interface_id"] == $dados_interface["id"]) {
					continue;
				}

				// atualiza interface/ip do ponto
				$regra = $dados["servidor_id"] == $dados_interface["servidor_id"] ? $dados["regra"] : $this->buscaProximaRegra($dados_interface["servidor_id"]);
				$ip = $clientes_pontos->buscaIPLivre($dados_interface["id"], $dados["assinatura_id"], $dados["id"]);
				$ip = $ip ? "'$ip'" : "NULL";

				$this->bancodedados->query("
					UPDATE
						tab_clientes_pontos
					SET
						clp_regra = $regra,
						sei_id = {$dados_interface["id"]},
						clp_ip = $ip,
						clp_publicado = 0
					WHERE
						clp_id = {$dados["id"]}");

				// Registra Comentário Ponto
				$this->registraComentarioClientePonto($dados["id"], "<b>Mudança de endereço:</b> <b>{$condominio_nome}, ".(($bloco_nome && $bloco_nome <> 'Único') ? "$bloco_nome / " : "")."{$complemento_valor}</b> para <b>{$antigo_condominio_nome}, ".(($antigo_bloco_nome && $antigo_bloco_nome <> 'Único') ? "$antigo_bloco_nome / " : "")."{$antigo_complemento_valor}</b>", $_SESSION["usuario"]);

			}

		} else {

			$this->bancodedados->query("
				UPDATE
					tab_clientes_pontos
					INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				SET
					sei_id = NULL,
					clp_ip = NULL,
					clp_publicado = 0
				WHERE
					tab_assinaturas.cli_id = {$dados_cliente["id"]} AND
					tab_clientes_pontos.cps_id IN (1, 2) AND
					tab_planos.pls_id NOT IN (3, 5, 33, 34, 35, 36)");

		}
		
	}

	######################
	### Migrar Sistema ###
	######################

	function migrar_sistema() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item && $this->ehMigravelPrePago()) {

			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Migrar Sistema Pré-Pago&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente migrar as assinaturas deste contrato para o <b>Sistema Pré-Pago</b>?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->migraSistemaPagamento($this->dados_item, $_POST["comentario"]);

				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {

			$this->fechar();

		}

	}

	###############################
	### Migrar Sistema Contrato ###
	###############################

	function migraSistemaPagamento($dados_contrato, $comentario) {

		foreach ($dados_contrato["assinaturas"] as $chave => $assinatura) {

			// Atualiza Parâmetro Contrato
			$dados_contrato["assinaturas"][$chave]["sistema_id"] = "2";
			$dados_contrato["assinaturas"][$chave]["sistema_nome"] = "Pré-Pago";

			// Atualiza Assinaturas
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
				SET
					pli_id = 2
				WHERE
					ass_id = {$assinatura["id"]}");

		}

		// Registra Comentário
		$this->registraComentario($dados_contrato["id"], 29, $comentario, $_SESSION["usuario"]);

		// Sincroniza Contrato
		$this->sincronizarContrato($dados_contrato);

		// Migra Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			$migravel = true;
			$pos_pagos = 0;
			$vinculo["assinaturas"] = $this->buscaAssinaturasContrato($vinculo["contrato_id"], true);
			foreach ($vinculo["assinaturas"] as $assinatura) {
				if ($assinatura["plano_sistema_id"] == 1) $migravel = false;
				if ($assinatura["sistema_id"] == 1) $pos_pagos++;
			}
			if ($migravel && $pos_pagos) {
				$this->migraSistemaPagamento($this->buscaDadosContrato($vinculo["contrato_id"]), $comentario);
			}
		}

	}

	###############################
	### Gerar Cobrança Pré-Pago ###
	###############################

	function gerar_cobranca_pre_pago() {
	    
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehGeravelCobrancaPrePago()) {
		    
			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Gerar Cobrança Pré-Pago&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente gerar cobrança da adesão para aprovação financeira deste contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
				
			} else {
				
				// Gera Ajuste Financeiro
				$competencia = date("Y-m-01");
				$this->geraAjusteInstalacao($this->dados_item["cliente"], $this->dados_item["id"], $this->dados_item["codigo"], 1, $this->dados_item["parametros"]["tabela_valor_final_instalacao"], $competencia, $competencia);
				
				// Gera Cobrança
				$geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
				$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
				$vencimento_referencia = date("Y-m-01", strtotime("$competencia + 1 month"));
				$vencimento_cobranca = date("Y-m-d", strtotime("+ 7 day"));

				if (($formato_id = $this->buscaFormatoBoleto($dados_cliente["origem_id"])) != false) {
					if (($cobranca_id = $geracao->geraCobrancaAvulsa($this->dados_item["cliente"], $formato_id, date("m/Y", strtotime($vencimento_referencia)), date("m/Y", strtotime($vencimento_referencia)), $vencimento_cobranca, null, false, 0, 0, false, true)) != false) {
						$this->enviaMensagemCobranca($cobranca_id);
					}
				}
				
				// Registra Comentário
				$this->registraComentario($this->dados_item["id"], 36, $_POST["comentario"], $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);
				
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}

	###########
	### Ver ###
	###########

	function ver() {
	    
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item["tipo_id"] == 14) { // Mudança de Endereço
		    
			$this->ver_aditivo_me();
			
		} elseif ($this->dados_item["tipo_id"] == 15) { // Mudança de Titularidade
		    
			$this->ver_aditivo_mt();
			
		} elseif ($this->dados_item) {
		    
			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);

			$botoes = array();
			if ($this->ehEnviavelCobranca()) $botoes[]	= '<input type="button" name="enviar_cobranca" class="buttonAzulEscuroBranco11" value="Enviar Cobrança" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_cobranca&i='.$this->dados_item["id"].'\';">';
			if ($this->ehSincronizavel()) $botoes[]	= '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=sincronizar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAdicionavel()) $botoes[]	= '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar Aditivo" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar&cor='.$this->dados_item["id"].'&cli='.$dados_cliente["id"].'\';">';
			if ($this->ehEnviavelContrato()) $botoes[] = '<input type="button" name="enviar_contrato" class="buttonAzulEscuroBranco11" value="Enviar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehConfirmavel()) $botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=confirmar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAtivavelSemPendencia()) $botoes[]	= '<input type="button" name="ativar_sem_pendencias" class="buttonAzulEscuroBranco11" value="Ativar Sem Pendências" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehConsultavelCredito()) $botoes[] = '<input type="button" name="consultar_credito" class="buttonAzulEscuroBranco11" style="width:120px" value="Consultar Crédito" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=consultar_credito&i='.$this->dados_item["id"].'\';">';
			if ($this->ehMigravelPrePago()) $botoes[] = '<input type="button" name="migrar_sistema" class="buttonAzulEscuroBranco11" style="width:160px" value="Migrar Sistema Pré-Pago" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=migrar_sistema&i='.$this->dados_item["id"].'\'">';
			if ($this->ehAprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_aprovar" class="buttonAzulEscuroBranco11" style="width:140px" value="Aprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehGeravelCobrancaPrePago()) $botoes[] = '<input type="button" name="gerar_cobranca_pre_pago" class="buttonAzulEscuroBranco11" style="width:140px" value="Gerar Cobrança" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_cobranca_pre_pago&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_reprovar" class="buttonAzulEscuroBranco11" style="width:150px" value="Reprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelComercial()) $botoes[] = '<input type="button" name="comercial_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelComercial()) $botoes[] = '<input type="button" name="comercial_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehVisualizavelContrato()) $botoes[] = '<input type="button" name="visualizar_contrato" class="buttonAzulEscuroBranco11" style="width:130px" value="Visualizar (Contrato)" onClick="visualizarContrato('.$this->dados_item["id"].', \'contrato\', 0);">';
			if ($this->ehVisualizavelVistoria()) $botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" style="width:120px" value="Visualizar (Vistoria)" onClick="visualizarContrato('.$this->dados_item["id"].', \'vistoria\', 0);">';
			
			if ($this->ehVisualizavelVistoria()) $botoes[] = '<input type="button" name="enviar_vistoria" class="buttonAzulEscuroBranco11" value="Enviar Vistoria" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&e=enviar&a=visualizar_vistoria&i='.$this->dados_item["id"].'\';">';
                        
                        if ($this->ehBaixavelContrato()) $botoes[] = '<input type="button" name="baixar_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Baixar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=baixar_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivelContrato()) $botoes[] = '<input type="button" name="remover_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Remover Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehSimulavelCancelamento()) $botoes[] = '<input type="button" name="simular_cancelamento" class="buttonAzulEscuroBranco11" value="Simular Cancelamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=simular_cancelamento&i='.$this->dados_item["id"].'\';">';
			if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=descancelar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelContrato()) $botoes[] = '<input type="button" name="anular" class="buttonAzulEscuroBranco11" value="Anular" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelCancelamento()) $botoes[] = '<input type="button" name="anular_cancelamento" class="buttonAzulEscuroBranco11" value="Anular Cancelamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_cancelamento&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelAtivacao()) $botoes[] = '<input type="button" name="anular_ativacao" class="buttonAzulEscuroBranco11" value="Anular Ativação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_ativacao&i='.$this->dados_item["id"].'\';">';
			if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" style="width:50px" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\';">';
			if ($this->dados_item["pai"]) $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ver&i='.$this->dados_item["pai"].'\';">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			if (in_array($this->dados_item["tipo_id"], array(1, 11))) {
				$this->conteudo .= $this->carregaArquivo("contratos.principal.ver.inc.php");
				$this->conteudo = str_replace("[aditivos]", $this->geraListagemAditivos($this->dados_item["aditivos"]), $this->conteudo);
			} else {
				$this->conteudo .= $this->carregaArquivo("contratos.aditivo.ver.inc.php");
			}
			
			$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Ver [{$this->dados_item["tipo_nome"]}]&nbsp;</b></td>", $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
			$this->conteudo = str_replace("[telefones]", $dados_cliente["lista_telefones"], $this->conteudo);
			$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			$this->conteudo = str_replace("[assinaturas]", $this->geraListagemAssinaturas($this->dados_item["assinaturas"]), $this->conteudo);
			$this->conteudo = str_replace("[descontos]", $this->geraListagemDescontos($this->dados_item["descontos"]), $this->conteudo);
			$this->conteudo = str_replace("[instalacao]", $this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_original_instalacao"])." (".$this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_final_instalacao"]).")", $this->conteudo);
			$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
			$this->conteudo = str_replace("[forma_pagamento]", $this->dados_item["forma_pagamento"], $this->conteudo);
			$this->conteudo = str_replace("[mensalidade]", $this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_original_mensalidade"])." (".$this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_final_mensalidade"]).")", $this->conteudo);
			$this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
			$this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace("[data_anulacao]", $this->dados_item["data_anulacao"], $this->conteudo);
			$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
			$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[instalador]", $this->buscaInstalador($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[motivos_anulacao]", $this->dados_item["motivos_anulacao_nome"], $this->conteudo);
			$this->conteudo = str_replace("[exibir_mensagem_vigencia]", $this->dados_item["vigencia_ativa"] && in_array($this->dados_item["status_id"], array(19, 20, 21)) ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
			$this->conteudo = str_replace("[exibir_mensagem_financeiro]", $this->dados_item["status_id"] == 12 ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
			$this->conteudo = str_replace("[exibir_mensagem_wave_garantia]", "none", $this->conteudo);
			$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
			$this->conteudo = str_replace("[equipamentos]", implode(", ", $this->dados_item["equipamentos_nomes"]), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			
			// Registra Atividade
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);
		
		} else {
			$this->fechar();
		}
		
	}

	####################################
	### Ver Aditivo Mudança Endereço ###
	####################################

	function ver_aditivo_me() {
	    
		$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
		
	 	$botoes = array();
		if ($this->ehSincronizavel()) $botoes[] = '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=sincronizar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehEnviavelContrato()) $botoes[] = '<input type="button" name="enviar_contrato" class="buttonAzulEscuroBranco11" value="Enviar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_contrato&i='.$this->dados_item["id"].'\';">';
		if ($this->ehConfirmavel()) $botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=confirmar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAtivavelSemPendencia()) $botoes[] = '<input type="button" name="ativar_sem_pendencias" class="buttonAzulEscuroBranco11" value="Ativar Sem Pendências" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehConsultavelCredito()) $botoes[] = '<input type="button" name="consultar_credito" class="buttonAzulEscuroBranco11" style="width:120px" value="Consultar Crédito" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=consultar_credito&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_aprovar" class="buttonAzulEscuroBranco11" style="width:140px" value="Aprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_aprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehReprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_reprovar" class="buttonAzulEscuroBranco11" style="width:150px" value="Reprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAprovavelComercial()) $botoes[] = '<input type="button" name="comercial_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_aprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehReprovavelComercial()) $botoes[] = '<input type="button" name="comercial_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_aprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehReprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehVisualizavelContrato()) $botoes[] = '<input type="button" name="visualizar_contrato" class="buttonAzulEscuroBranco11" style="width:130px" value="Visualizar (Contrato)" onClick="visualizarContrato('.$this->dados_item["id"].', \'contrato\', 0);">';
		if ($this->ehVisualizavelVistoria()) $botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" style="width:120px" value="Visualizar (Vistoria)" onClick="visualizarContrato('.$this->dados_item["id"].', \'vistoria\', 0);">';
		if ($this->ehBaixavelContrato()) $botoes[] = '<input type="button" name="baixar_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Baixar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=baixar_contrato&i='.$this->dados_item["id"].'\';">';
		if ($this->ehRemovivelContrato()) $botoes[] = '<input type="button" name="remover_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Remover Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_contrato&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAnulavelContrato()) $botoes[] = '<input type="button" name="anular" class="buttonAzulEscuroBranco11" value="Anular" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular&i='.$this->dados_item["id"].'\';">';
		if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAnulavelAtivacao()) $botoes[] = '<input type="button" name="anular_ativacao" class="buttonAzulEscuroBranco11" value="Anular Ativação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_ativacao&i='.$this->dados_item["id"].'\';">';
		if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\';">';
		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ver&i='.$this->dados_item["pai"].'\';">';
		$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
		
		$this->conteudo .= $this->carregaArquivo("contratos.aditivo_me.ver.inc.php");
		
		$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Ver [{$this->dados_item["tipo_nome"]}]&nbsp;</b></td>", $this->conteudo);
		$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
		$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
		$this->conteudo = str_replace("[telefones]", $dados_cliente["lista_telefones"], $this->conteudo);
		$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
		$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
		$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
		$this->conteudo = str_replace("[instalador]", $this->buscaInstalador($this->dados_item["id"]), $this->conteudo);
		
		if ($this->dados_item["parametros"]["contrato_versao"] >= 9) {
			// Endereço
			$this->conteudo = str_replace("[condominio]", $this->dados_item["parametros"]["cliente_endereco_condominio"], $this->conteudo);
			$this->conteudo = str_replace("[bloco]", $this->dados_item["parametros"]["cliente_endereco_bloco"], $this->conteudo);
			$this->conteudo = str_replace("[cep]", $this->dados_item["parametros"]["cliente_endereco_cep"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["parametros"]["cliente_endereco_estado"], $this->conteudo);
			$this->conteudo = str_replace("[cidade]", $this->dados_item["parametros"]["cliente_endereco_cidade"], $this->conteudo);
			$this->conteudo = str_replace("[bairro]", $this->dados_item["parametros"]["cliente_endereco_bairro"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["parametros"]["cliente_endereco_estado"], $this->conteudo);
			$this->conteudo = str_replace("[logradouro]", $this->dados_item["parametros"]["cliente_endereco_logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["parametros"]["cliente_endereco_numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento]", ($this->dados_item["parametros"]["cliente_endereco_complemento_tipo"] != "o" ? $this->complemento_tipos[$this->dados_item["parametros"]["cliente_endereco_complemento_tipo"]]." " : "").$this->dados_item["parametros"]["cliente_endereco_complemento_valor"], $this->conteudo);
			// Endereço Cobrança / Correspondência
			$this->conteudo = str_replace("[cobranca_condominio]", $this->dados_item["parametros"]["cliente_endereco_cobranca_condominio"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_bloco]", $this->dados_item["parametros"]["cliente_endereco_cobranca_bloco"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_cep]", $this->dados_item["parametros"]["cliente_endereco_cobranca_cep"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_estado]", $this->dados_item["parametros"]["cliente_endereco_cobranca_estado"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_cidade]", $this->dados_item["parametros"]["cliente_endereco_cobranca_cidade"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_bairro]", $this->dados_item["parametros"]["cliente_endereco_cobranca_bairro"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_estado]", $this->dados_item["parametros"]["cliente_endereco_cobranca_estado"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_logradouro]", $this->dados_item["parametros"]["cliente_endereco_cobranca_logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_numero]", $this->dados_item["parametros"]["cliente_endereco_cobranca_numero"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_complemento]", ($this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_tipo"] != "o" ? $this->complemento_tipos[$this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_tipo"]]." " : "").$this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_valor"], $this->conteudo);
			$this->conteudo = str_replace("[formas_pagamento]", $this->geraListagemFormasPagamentoServico($this->dados_item["formas_pagamento"], $this->dados_item["forma_pagamento_id"], false), $this->conteudo);
		} else {
			// Endereço
			$this->conteudo = str_replace("[condominio]", $this->dados_item["parametros"]["final"]["condominio_nome"], $this->conteudo);
			$this->conteudo = str_replace("[bloco]", $this->dados_item["parametros"]["final"]["bloco_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cep]", $this->dados_item["parametros"]["final"]["cep"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["parametros"]["final"]["estado"], $this->conteudo);
			$this->conteudo = str_replace("[cidade]", $this->dados_item["parametros"]["final"]["cidade"], $this->conteudo);
			$this->conteudo = str_replace("[bairro]", $this->dados_item["parametros"]["final"]["bairro"], $this->conteudo);
			$this->conteudo = str_replace("[estado]", $this->dados_item["parametros"]["final"]["estado"], $this->conteudo);
			$this->conteudo = str_replace("[logradouro]", $this->dados_item["parametros"]["final"]["logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $this->dados_item["parametros"]["final"]["numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento]", ($this->dados_item["parametros"]["final"]["complemento_tipo"] != "o" ? $this->complemento_tipos[$this->dados_item["parametros"]["final"]["complemento_tipo"]]." " : "").$this->dados_item["parametros"]["final"]["complemento_valor"], $this->conteudo);
			// Endereço Cobrança / Correspondência
			$this->conteudo = str_replace("[cobranca_condominio]", $this->dados_item["parametros"]["final"]["cobranca_condominio_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_bloco]", $this->dados_item["parametros"]["final"]["cobranca_bloco_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_cep]", $this->dados_item["parametros"]["final"]["cobranca_cep"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_estado]", $this->dados_item["parametros"]["final"]["cobranca_estado"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_cidade]", $this->dados_item["parametros"]["final"]["cobranca_cidade"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_bairro]", $this->dados_item["parametros"]["final"]["cobranca_bairro"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_estado]", $this->dados_item["parametros"]["final"]["cobranca_estado"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_logradouro]", $this->dados_item["parametros"]["final"]["cobranca_logradouro"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_numero]", $this->dados_item["parametros"]["final"]["cobranca_numero"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_complemento]", ($this->dados_item["parametros"]["final"]["cobranca_complemento_tipo"] != "o" ? $this->complemento_tipos[$this->dados_item["parametros"]["final"]["cobranca_complemento_tipo"]]." " : "").$this->dados_item["parametros"]["final"]["cobranca_complemento_valor"], $this->conteudo);
		}
		
		// Comentários
		$this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
		
		// Datas
		$this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
		$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
		$this->conteudo = str_replace("[data_anulacao]", $this->dados_item["data_anulacao"], $this->conteudo);
		$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
		if(($this->dados_item["status_id"]) >= 18){
			$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
		} else {
			$this->conteudo = str_replace("[vigencia]", "", $this->conteudo);
		}
		
		// Valores
		$this->conteudo = str_replace("[instalacao]", $this->formataDinheiro($this->dados_item["parametros"]["contrato_valor_instalacao"]).($this->dados_item["parametros"]["parcelamento"] && $this->dados_item["parametros"]["tabela_valor_final_instalacao"] ? " - Parcelamento: ".$this->buscaParcelamentos($this->dados_item["parametros"]["parcelamento"], $this->dados_item["parametros"]["parcelamento_forma"]) : ""), $this->conteudo);
		
		// Situação e Status
		$this->conteudo = str_replace("[instalador]", $this->buscaInstalador($this->dados_item["id"]), $this->conteudo);
		$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
		$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
	 	$this->conteudo = str_replace("[motivos_anulacao]", $this->dados_item["motivos_anulacao_nome"], $this->conteudo);
		$this->conteudo = str_replace("[botoes]",implode(" ", $botoes),$this->conteudo);
		
	}

	########################################
	### Ver Aditivo Mudança Titularidade ###
	########################################

	function ver_aditivo_mt() {
	    
		$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
		$contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);

		$botoes = array();
		if ($this->ehSincronizavel()) $botoes[]	= '<input type="button" name="sincronizar" class="buttonAzulEscuroBranco11" value="Sincronizar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=sincronizar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehEnviavelContrato()) $botoes[] = '<input type="button" name="enviar_contrato" class="buttonAzulEscuroBranco11" value="Enviar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_contrato&i='.$this->dados_item["id"].'\';">';
		if ($this->ehConfirmavel()) $botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=confirmar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAtivavel()) $botoes[] = '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAtivavelSemPendencia()) $botoes[] = '<input type="button" name="ativar_sem_pendencias" class="buttonAzulEscuroBranco11" value="Ativar Sem Pendências" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehConsultavelCredito()) $botoes[] = '<input type="button" name="consultar_credito" class="buttonAzulEscuroBranco11" style="width:120px" value="Consultar Crédito" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=consultar_credito&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_aprovar" class="buttonAzulEscuroBranco11" style="width:140px" value="Aprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_aprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehReprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_reprovar" class="buttonAzulEscuroBranco11" style="width:150px" value="Reprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAprovavelComercial()) $botoes[] = '<input type="button" name="comercial_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_aprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehReprovavelComercial()) $botoes[] = '<input type="button" name="comercial_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_aprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehReprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_reprovar&i='.$this->dados_item["id"].'\';">';
		if ($this->ehVisualizavelContrato()) $botoes[] = '<input type="button" name="visualizar_contrato" class="buttonAzulEscuroBranco11" style="width:130px" value="Visualizar (Contrato)" onClick="visualizarContrato('.$this->dados_item["id"].', \'contrato\', 0);">';
		if ($this->ehVisualizavelVistoria()) $botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" style="width:120px" value="Visualizar (Vistoria)" onClick="visualizarContrato('.$this->dados_item["id"].', \'vistoria\', 0);">';
		if ($this->ehBaixavelContrato()) $botoes[] = '<input type="button" name="baixar_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Baixar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=baixar_contrato&i='.$this->dados_item["id"].'\';">';
		if ($this->ehRemovivelContrato()) $botoes[] = '<input type="button" name="remover_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Remover Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_contrato&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAnulavelContrato()) $botoes[] = '<input type="button" name="anular" class="buttonAzulEscuroBranco11" value="Anular" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular&i='.$this->dados_item["id"].'\';">';
		if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
		if ($this->ehAnulavelAtivacao()) $botoes[] = '<input type="button" name="anular_ativacao" class="buttonAzulEscuroBranco11" value="Anular Ativação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_ativacao&i='.$this->dados_item["id"].'\';">';
		if ($this->ehEditavel()) $botoes[] = '<input type="button" name="editar" class="buttonAzulEscuroBranco11" value="Editar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=editar&i='.$this->dados_item["id"].'\';">';
		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ver&i='.$this->dados_item["pai"].'\';">';
		$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

		$this->conteudo .= $this->carregaArquivo("contratos.aditivo_mt.ver.inc.php");
		
		$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Ver [{$this->dados_item["tipo_nome"]}]&nbsp;</b></td>", $this->conteudo);
		$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
		$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
		$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
		$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
		
		if ($this->dados_item["parametros"]["contrato_versao"] >= 9) {
			// Cliente Antigo
			$this->conteudo = str_replace("[nome_antigo]", $this->dados_item["parametros"]["cliente_antigo"]["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[pessoa_tipo_antigo]", ($this->dados_item["parametros"]["cliente_antigo"]["cliente_tipo"] == 1) ? "Pessoa Física" : "Pessoa Jurídica", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_label_antigo]", ($this->dados_item["parametros"]["cliente_antigo"]["cliente_tipo"] == 1) ? "CPF" : "CNPJ", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_antigo]", $this->dados_item["parametros"]["cliente_antigo"]["cliente_cpf_cnpj"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento_antigo]", $this->dados_item["parametros"]["cliente_antigo"]["cliente_nascimento"], $this->conteudo);
			// Endereço
			$this->conteudo = str_replace("[nome]", $this->dados_item["parametros"]["cliente_nome"], $this->conteudo);
			$this->conteudo = str_replace("[pessoa_tipo]", ($this->dados_item["parametros"]["cliente_tipo"] == 1) ? "Pessoa Física" : "Pessoa Jurídica", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_label]", ($this->dados_item["parametros"]["cliente_tipo"] == 1) ? "CPF" : "CNPJ", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["parametros"]["cliente_cpf_cnpj"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento]", $this->dados_item["parametros"]["cliente_nascimento"], $this->conteudo);
			$this->conteudo = str_replace("[contato]", $this->dados_item["parametros"]["cliente_contato"], $this->conteudo);
			// Contatos
			$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($this->dados_item["parametros"]["cliente_telefones"]), $this->conteudo);
			$this->conteudo = str_replace("[lista_telefones]", serialize($contatos->dados_item["parametros"]["cliente_telefones"]), $this->conteudo);
			$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($this->dados_item["parametros"]["cliente_emails"]), $this->conteudo);
			$this->conteudo = str_replace("[lista_emails]", serialize($contatos->dados_item["parametros"]["cliente_emails"]), $this->conteudo);
		} else {
			// Cliente Antigo
			$this->conteudo = str_replace("[nome_antigo]", $this->dados_item["parametros"]["inicial"]["nome"], $this->conteudo);
			$this->conteudo = str_replace("[pessoa_tipo_antigo]", ($this->dados_item["parametros"]["inicial"]["tipo"] == 1) ? "Pessoa Física" : "Pessoa Jurídica", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_label_antigo]", ($this->dados_item["parametros"]["inicial"]["tipo"] == 1) ? "CPF" : "CNPJ", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_antigo]", $this->dados_item["parametros"]["inicial"]["cpf_cnpj"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento_antigo]", $this->dados_item["parametros"]["inicial"]["nascimento"], $this->conteudo);
			// Endereço
			$this->conteudo = str_replace("[nome]", $this->dados_item["parametros"]["final"]["nome"], $this->conteudo);
			$this->conteudo = str_replace("[pessoa_tipo]", ($this->dados_item["parametros"]["final"]["tipo"] == 1) ? "Pessoa Física" : "Pessoa Jurídica", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj_label]", ($this->dados_item["parametros"]["final"]["tipo"] == 1) ? "CPF" : "CNPJ", $this->conteudo);
			$this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["parametros"]["final"]["cpf_cnpj"], $this->conteudo);
			$this->conteudo = str_replace("[nascimento]", $this->dados_item["parametros"]["final"]["nascimento"], $this->conteudo);
			$this->conteudo = str_replace("[contato]", $this->dados_item["parametros"]["final"]["contato"], $this->conteudo);
			// Contatos
			$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($this->dados_item["parametros"]["final"]["telefones"]), $this->conteudo);
			$this->conteudo = str_replace("[lista_telefones]", serialize($contatos->dados_item["parametros"]["final"]["telefones"]), $this->conteudo);
			$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($this->dados_item["parametros"]["final"]["emails"]), $this->conteudo);
			$this->conteudo = str_replace("[lista_emails]", serialize($contatos->dados_item["parametros"]["final"]["emails"]), $this->conteudo);
		}
		
		// Endereço Cobrança / Correspondência
		$this->conteudo = str_replace("[cobranca_condominio]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["condominio_nome"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_bloco]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["bloco_nome"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_cep]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["cep"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_estado]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["estado"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_cidade]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["cidade"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_bairro]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["bairro"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_estado]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["estado"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_logradouro]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["logradouro"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_numero]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["numero"], $this->conteudo);
		$this->conteudo = str_replace("[cobranca_complemento]", ($this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento_id"] != "o" ? $this->complemento_tipos[$this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento_id"]]." " : "").$this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento"], $this->conteudo);
		
		// Comentários
		$this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
		
		// Datas
		$this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
		$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
		$this->conteudo = str_replace("[data_anulacao]", $this->dados_item["data_anulacao"], $this->conteudo);
		$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
		
		// Situação e Status
		$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
		$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
		$this->conteudo = str_replace("[motivos_anulacao]", $this->dados_item["motivos_anulacao_nome"], $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
	}

	#######################
	### Enviar Contrato ###
	#######################

	function enviar_contrato() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';				

				$lista_emails = array();				
				foreach ($dados_cliente["emails"] as $email) {
					$lista_emails[] = $email["contato_email"];
				}
				$lista_emails = implode("; ", $lista_emails);
				
				$mensagem = "<br>Será enviada uma mensagem para o(s) e-mail(s) abaixo para visualização / aprovação dos seguintes contratos:<br><br>";
				$mensagem .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\">";
				$mensagem .= "<tr class=\"txtAzulEscuro11\">";
				$mensagem .= "<td><b>&raquo; &nbsp;{$this->dados_item["codigo"]} - {$this->dados_item["servico_nome"]}</b></td>";
				$mensagem .= "</tr>";

				foreach ($this->dados_item["vinculos"] as $vinculo) {
					if ($vinculo["servico_parametros"]["com_contrato"]) {
						$mensagem .= "<tr class=\"txtAzulEscuro11\">";
						$mensagem .= "<td><b>&raquo; &nbsp;{$vinculo["codigo"]} - {$vinculo["servico_nome"]}</b></td>";
						$mensagem .= "</tr>";
					}
				}

				$mensagem .= "</table><br>";

				$this->conteudo .= $this->carregaArquivo("contratos.enviar_contrato.inc.php");
				
				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Enviar Contrato&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[emails]", $lista_emails, $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", $mensagem, $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=enviar_contrato&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$emails = array_unique(preg_split("/ *[ ,;] */", strtolower($_POST["emails"])));
				
				if (array_key_exists("adicionar_emails", $_POST) && $_POST["adicionar_emails"]) {
					$this->cadastrarEmailsCliente($this->dados_item["cliente_id"], $emails);
				}

				$this->enviaMensagemAprovacao($this->dados_item, $dados_cliente, $emails);
				
				// Atualiza Status do Contrato
				$status_id = $this->dados_item["status_id"] == 14 ? 13 : $this->dados_item["status_id"];

				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						cor_enviado = 1,
						crs_id = $status_id
					WHERE
						tab_contratos.cor_id = {$this->dados_item["id"]}");
				
				// Registra Comentário
				$comentario = $_POST["comentario"] ? "<br><b>Comentário</b>: ".trim($_POST["comentario"]) : "";

				$this->registraComentario($this->dados_item["id"], 27, "<b>E-mail(s)</b>: ".implode(", ", $emails).$comentario, $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}

	##########################
	### Financeiro Aprovar ###
	##########################

	function financeiro_aprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Aprovar (Financeiro)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Aprovar (Financeiro)</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$this->financeiroAprovarContrato($this->dados_item, $_POST["comentario"], $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}

	###################################
	### Financeiro Aprovar Contrato ###
	###################################

	function financeiroAprovarContrato($dados_contrato, $comentario = null, $usuario_id = null, $boleto_pago = false) {

		if ($_SESSION["usuario"] != 39 && !$boleto_pago && $dados_contrato && $dados_contrato["formas_pagamento"] && count($dados_contrato["formas_pagamento"]) == 1 && $dados_contrato["formas_pagamento"][0]["entrada"] > 0) {
			return;
		}

		$dados_cliente = $this->buscaDadosCliente($dados_contrato["cliente"]);

		if (!$this->ehAutoAprovavelComercial($dados_contrato["tipo_parametros"], $dados_contrato["servico_parametros"])) {
			$status_id = 13;
		} elseif (!$this->ehAutoAprovavelTecnico($dados_cliente, $dados_contrato["tipo_parametros"], $dados_contrato["servico_id"], $dados_contrato["servico_parametros"], $dados_contrato["parametros"], $dados_contrato["tipo_id"])) {
			$status_id = 15;
		} else {
			$status_id = 17;
		}

		// Atualiza Status Contrato
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				crs_id = $status_id
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");

		// Registra Comentário Aprovação Financeira
		$this->registraComentario($dados_contrato["id"], 2, $comentario, $usuario_id);

		// Comentário Auto Aprovação Comercial
		if ($status_id > 13) {
			$this->registraComentario($dados_contrato["id"], 12);
		}

		// Comentário Auto Aprovação Técnico
		if ($status_id > 15) {
			$this->registraComentario($dados_contrato["id"], 15);
			// Gera Visita
			if (!$dados_contrato["vinculos_pai"] && $dados_contrato["servico_parametros"]["gera_visita"] && ($dados_contrato["tipo_id"] != 13 || count($dados_contrato["comodatos"]) > 0) && $dados_contrato["tipo_id"] != 15 && !$this->possuiVisita($dados_contrato["id"])) {
				if ($dados_cliente["origem_id"] == 1) {
					$visita_id = $this->geraVisita($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["tipo_id"] != 13 ? 1 : 3 );
					$this->registraComentarioVisita($visita_id, "<b>Contrato Aprovado</b>: {$dados_contrato["codigo"]}", 16, $usuario_id);
					if($dados_contrato["tipo_id"] == 13){
						$this->abreChamadoVisitaMigracao($dados_contrato);
					}
				}
			}
		}

		// Insere Chamado Residencial (Vistoria)
		if ($dados_contrato["tipo_id"] == 11 && $status_id == 15 && in_array($dados_contrato["servico_id"], array(3, 28, 33, 34))) {
			$this->insereChamadoVistoriaResidencial($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["codigo"]);
		}

		// Atualiza Situação Cliente - Fechado (Com Contrato)
		$this->atualizaSituacaoCliente($dados_contrato["cliente"], 5);

		// Aprova Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			if ($vinculo["status_id"] == 11 || $vinculo["status_id"] == 12) {
				$this->financeiroAprovarContrato($this->buscaDadosContrato($vinculo["contrato_id"]), $comentario, $usuario_id);
			}
		}

	}

	###########################
	### Financeiro Reprovar ###
	###########################

	function financeiro_reprovar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Reprovar (Financeiro)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Reprovar (Financeiro)</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->financeiroReprovarContrato($this->dados_item, $_POST["comentario"], $_SESSION["usuario"]);

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	####################################
	### Financeiro Reprovar Contrato ###
	####################################

	function financeiroReprovarContrato($dados_contrato, $comentario = null, $usuario_id = null) {
		
		// Atualiza Status Contrato
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				crs_id = 12
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
		
		// Registra Comentário
		$this->registraComentario($dados_contrato["id"], 3, $comentario, $usuario_id);
		
		// Atualiza Situação Cliente - Fechado (Com Contrato)
		$this->atualizaSituacaoCliente($dados_contrato["cliente"], 5);
		
		// Reprova Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			if ($vinculo["status_id"] == 11) {
				$this->financeiroReprovarContrato($this->buscaDadosContrato($vinculo["contrato_id"]), $comentario, $usuario_id);
			}
		}
		
	}

	#########################
	### Consultar Credito ###
	#########################

	function consultar_credito() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if ($_GET["consulta_id"]) {

				$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);

				$clientes_consultas_credito = controle::instanciaObjeto("clientes_consultas_credito", $this->bancodedados, $this->permissoes, false);
				$clientes_consultas_credito->buscaDadosItem($_GET["consulta_id"]);

			    $botoes = array();
				$botoes[] = '<input type="button" name="continuar" class="buttonAzulEscuroBranco11" value="Continuar" onClick="window.location = \'?m='.$this->dados_modulo["nome"].'&a=ver&i='.$this->dados_item["id"].'\'">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.ver_retorno_consulta.inc.php");

				$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
				$this->conteudo = str_replace("[consulta_retorno]", $clientes_consultas_credito->dados_item["conteudo_formatado"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

			} elseif (!$_POST && $this->ehConsultavelCredito()) {

			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.consultar_credito.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Consultar Crédito&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", $_GET["erro"] ? "<b><span style=\"color:#FF0000\">{$_GET["erro"]}</span></b><br><br>Para tentar novamente, clique no botão Confirmar.<br>Esta operação pode levar alguns segundos." : "Você deseja realmente efetuar a <b>Consulta de Crédito</b> deste cliente?<br>Esta operação pode levar alguns segundos.", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);

				if ($this->dados_item["tipo_id"] == 15) {
					$dados_cliente["nome"] = $this->dados_item["parametros"]["cliente_nome"];
					$dados_cliente["cpf_cnpj"] = $this->dados_item["parametros"]["cliente_cpf_cnpj"];
					$dados_cliente["tipo"] = $this->dados_item["parametros"]["cliente_tipo"];
					$dados_cliente["telefones"] = $this->dados_item["parametros"]["cliente_telefones"];
				}

				if ($this->dados_item["tipo_id"] == 11) {
					$dados_cliente["valor"] = $this->dados_item["parametros"]["tabela_valor_final_mensalidade"];
				} else {
					$dados_cliente["valor"] = $this->dados_item["parametros"]["tabela_valor_final_mensalidade_final"];
				}

				// Instancia API CheckCheck
				$check_check = controle::instanciaObjeto("clientes_consultas_credito.checkcheck");

				$check_check->setCpfCnpj($dados_cliente["cpf_cnpj"]);
				$check_check->setValor($dados_cliente["valor"]);

				for ($i = 0; $i < count($dados_cliente["telefones"]) && $i < 2; $i++) {
					$check_check->addTelefone($dados_cliente["telefones"][$i]["contato_ddd"], $dados_cliente["telefones"][$i]["contato_numero"]);
				}

				if ($check_check->consultar()) {

					$conteudo = $check_check->getConteudo();

					$conteudo_serializado = serialize($conteudo);
					$restricoes = (int) $check_check->getRestricoes();
					$situacao_id = $restricoes == 0 ? 1 : 2;

					$this->bancodedados->query("
						INSERT INTO
							tab_clientes_consultas_credito
						SET
							ccc_cpf_cnpj = '{$dados_cliente["cpf_cnpj"]}',
							ccc_data_hora = NOW(),
							ccc_conteudo = '{$conteudo_serializado}',
							ccc_restricoes = {$restricoes},
							cli_id = {$dados_cliente["id"]},
							usu_id = {$_SESSION["usuario"]},
							ccs_id = {$situacao_id},
							cct_id = 1");

					$id = $this->bancodedados->ultimoIdInserido();

					$this->registraComentario($this->dados_item["id"], 6, null, $_SESSION["usuario"]);

					// Aprova / Reprova Contratos
					if (in_array($this->dados_item["status_id"], array(11, 12))) {

						if ($restricoes > 5) {
							
							if ($this->dados_item["tipo_id"] == 11) {
								$contratos = $this->buscaClientesContratos($dados_cliente["id"], "11", $this->dados_item["id"]);
								foreach ($contratos as $contrato) {
									$this->financeiroReprovarContrato($this->buscaDadosContrato($contrato["id"]));
								}
							} else {
								$this->financeiroReprovarContrato($this->dados_item);
							}
							
						} elseif (count($this->dados_item["formas_pagamento"]) == 0 || $this->dados_item["formas_pagamento"][0]["entrada"] == 0) {

							if ($this->dados_item["tipo_id"] == 11) {
								$contratos = $this->buscaClientesContratos($dados_cliente["id"], "11, 12", $this->dados_item["id"]);
								foreach ($contratos as $contrato) {
									$this->financeiroAprovarContrato($this->buscaDadosContrato($contrato["id"]), null, $_SESSION["usuario"]);
								}
							} else {
								$this->financeiroAprovarContrato($this->dados_item, null, $_SESSION["usuario"]);
							}
							
						}

						/*
						if ($restricoes == 0) {

							if ($this->dados_item["tipo_id"] == 11) {
								$contratos = $this->buscaClientesContratos($dados_cliente["id"], "11, 12", $this->dados_item["id"]);
								foreach ($contratos as $contrato) {
									$this->financeiroAprovarContrato($this->buscaDadosContrato($contrato["id"]), null, $_SESSION["usuario"]);
								}
							} else {
								$this->financeiroAprovarContrato($this->dados_item, null, $_SESSION["usuario"]);
							}

						} else {

							if ($this->dados_item["tipo_id"] == 11) {
								$contratos = $this->buscaClientesContratos($dados_cliente["id"], "11", $this->dados_item["id"]);
								foreach ($contratos as $contrato) {
									$this->financeiroReprovarContrato($this->buscaDadosContrato($contrato["id"]));
								}
							} else {
								$this->financeiroReprovarContrato($this->dados_item);
							}

						} */

						// Atualiza Nome do Cliente

						$grafia_nome = array_key_exists("grafia", $conteudo) && count($conteudo["grafia"]) > 0 && array_key_exists("nome", $conteudo["grafia"][0]) ? trim($conteudo["grafia"][0]["nome"]) : "";

						if ($grafia_nome && !in_array(strtoupper($grafia_nome), array("NOME NÃO CONFIRMADO", "DOCUMENTO NAO CADASTRADO ATE A DATA")) && !$this->comparaNomes($grafia_nome, $dados_cliente["nome"])) {

							$grafia_nome = $this->corrigeNome($grafia_nome);

							// Atualiza tab_clientes
							if ($this->dados_item["tipo_id"] == 11) {
								$this->bancodedados->query("
									UPDATE
										tab_clientes
									SET
										cli_nome = '{$grafia_nome}'
									WHERE
										cli_id = {$dados_cliente["id"]}");
							}

							// Atualiza Parametros Contrato
							$this->dados_item["parametros"]["cliente_nome"] = $grafia_nome;
							$this->dados_item["parametros"] = addslashes(serialize($this->dados_item["parametros"]));

							$this->bancodedados->query("
								UPDATE
									tab_contratos
								SET
									cor_parametros = '{$this->dados_item["parametros"]}'
								WHERE
									cor_id = {$this->dados_item["id"]}");

							// Registra Comentário
							$this->registraComentario($this->dados_item["id"], 33, "<b>Nome</b>: {$dados_cliente["nome"]} <b>Para</b>: {$grafia_nome}");

						}

					}

					header("Location: ./?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$_GET["i"]}&consulta_id=$id");

				} else {

					header("Location: ./?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$_GET["i"]}&erro={$check_check->getErro()}");

				}

			}

		} else {
			$this->fechar();
		}

	}

	#################
	### Confirmar ###
	#################

	function confirmar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Aprovar (Comercial)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Confirmar</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				// Define Data Confirmação Contrato
				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						cor_data_confirmacao = CURDATE()
					WHERE
						tab_contratos.cor_id = {$this->dados_item["id"]}");

				// Define Data Confirmação Assinaturas
				$this->bancodedados->query("
					UPDATE
						tab_assinaturas
						INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
					SET
						ass_data_confirmacao = CURDATE()
					WHERE
						rel_ass_cor_estacontido.cor_id = {$this->dados_item["id"]} AND
						tab_assinaturas.ast_id = 1");

				// Registra Comentário
				$this->registraComentario($this->dados_item["id"], 31, $_POST["comentario"], $_SESSION["usuario"]);

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	#########################
	### Comercial Aprovar ###
	#########################

	function comercial_aprovar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Aprovar (Comercial)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Aprovar (Comercial)</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->comercialAprovar($_POST["comentario"], $_SESSION["usuario"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}
	
	function comercialAprovar($comentario, $usuario_id = null) {
		
		$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
		
		if (!$this->ehAutoAprovavelTecnico($dados_cliente, $this->dados_item["tipo_parametros"], $this->dados_item["servico_id"], $this->dados_item["servico_parametros"], $this->dados_item["parametros"], $this->dados_item["tipo_id"])) {
			$status_id = 15;
		} else {
			$status_id = 17;
		}
		
		// Atualiza Status Contrato
		$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						crs_id = $status_id
					WHERE
						tab_contratos.cor_id = {$this->dados_item["id"]}");
		
		// Registra Comentário
		$this->registraComentario($this->dados_item["id"], 10, $comentario, $usuario_id);
		
		// Comentário Auto Aprovação Técnico
		if ($status_id > 15) {
			$this->registraComentario($this->dados_item["id"], 15);
			// Gera Visita
			if (!$this->dados_item["vinculos_pai"] && $this->dados_item["servico_parametros"]["gera_visita"] && ($this->dados_item["tipo_id"] != 13 || count($this->dados_item["comodatos"]) > 0) && $this->dados_item["tipo_id"] != 15 && !$this->possuiVisita($this->dados_item["id"])) {
				if ($dados_cliente["origem_id"] == 1) {
					if($this->dados_item["tipo_id"] == 13){
						$this->abreChamadoVisitaMigracao($this->dados_item);
					} else {
						$visita_id = $this->geraVisita($this->dados_item["cliente"], $this->dados_item["id"], $this->dados_item["tipo_id"] != 13 ? 1 : 3);
						$this->registraComentarioVisita($visita_id, "<b>Contrato Aprovado</b>: {$this->dados_item["codigo"]}", 16, $_SESSION["usuario"]);
					}
				}
			}
		}
		
		// Insere Chamado Residencial (Vistoria)
		if ($this->dados_item["tipo_id"] == 11 && $status_id == 15 && in_array($this->dados_item["servico_id"], array(3, 28, 33, 34))) {
			$this->insereChamadoVistoriaResidencial($this->dados_item["cliente"], $this->dados_item["id"], $this->dados_item["codigo"]);
		}
		
		// Atualiza Situação Cliente
		$this->atualizaSituacaoCliente($this->dados_item["cliente"], 5); // Fechado (Com Contrato)
		
		// Registra Atividade
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);
		
	}

	##########################
	### Comercial Reprovar ###
	##########################

	function comercial_reprovar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");
				
				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Reprovar (Comercial)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Reprovar (Comercial)</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {

				$this->comercialReprovar($_POST["comentario"], $_SESSION["usuario"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	function comercialReprovar($comentario, $usuario_id = null) {
		
		$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
		
		// Atualiza Situacao/Status Contrato
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				crs_id = 14,
				cor_renegociacao = 1
			WHERE
				tab_contratos.cor_id = {$this->dados_item["id"]}");
		
		// Registra Comentário
		$this->registraComentario($this->dados_item["id"], 11, $comentario, $usuario_id);
		
		// Manda cliente para renegociação
		$this->atualizaSituacaoCliente($this->dados_item["cliente"], 4); // Fechado (Renegociação)
		
		// Finaliza Visita, se houver
		foreach ($this->dados_item["visitas"] as $visita) {
			
			$this->bancodedados->query("
				UPDATE
					tab_visitas
				SET
					via_id = 4
				WHERE
					vis_id = {$visita["id"]}");
			
			$this->registraComentarioVisita($visita["id"], "<b>Cancelada</b>: Contrato enviado para <b>Re-negociação</b> ({$this->dados_item["codigo"]})", 18, $_SESSION["usuario"]);
			
		}
		
		if ($this->dados_item["origem_id"] == 1 && ($dados_usuario = $this->buscaDadosUsuario($usuario_id)) != false) {
			
			$comentario = "Enviado para <b>Re-negociação</b> (Gerado em {$this->dados_item["data_geracao"]}) por {$dados_usuario["nome"]}:";
			if (trim($_POST["comentario"])) $comentario .= "\n » <b>Comentário</b>:\n".preg_replace("/[\r\n\t ]+/", " ", trim($_POST["comentario"]));
			
			$this->bancodedados->query("
				INSERT INTO
					tab_clientes_retornos
				SET
					cli_id = {$this->dados_item["cliente"]},
					usu_id = {$dados_cliente["responsavel"]},
					clr_abertura = NOW(),
					clr_data = CURDATE(),
					clr_comentario = '$comentario',
					cet_id = 2,
					ces_id = 1");
			
		}
		
		// Registra Atividade
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);
		
	}

	#######################
	### Técnico Aprovar ###
	#######################

	function tecnico_aprovar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.aprovar_tecnico.inc.php");

				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);

				// Atualiza Status Contrato
				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						crs_id = 17
					WHERE
						tab_contratos.cor_id = {$this->dados_item["id"]}");

				// Registra Comentário
				$this->registraComentario($this->dados_item["id"], 13, $_POST["comentario"], $_SESSION["usuario"]);

				// Gera Visita
				if (!$this->dados_item["vinculos_pai"] && $this->dados_item["servico_parametros"]["gera_visita"] && ($this->dados_item["tipo_id"] != 13 || count($this->dados_item["comodatos"]) > 0) && $this->dados_item["tipo_id"] != 15 && !$this->possuiVisita($this->dados_item["id"])) {
					if ($dados_cliente["origem_id"] == 1) {
						if($this->dados_item["tipo_id"] == 13) {
							$this->abreChamadoVisitaMigracao($this->dados_item);
						} else {
							$visita_id = $this->geraVisita($this->dados_item["cliente"], $this->dados_item["id"], $this->dados_item["tipo_id"] != 13 ? 1 : 3);
							$this->registraComentarioVisita($visita_id, "<b>Contrato Aprovado</b>: {$this->dados_item["codigo"]}", 16, $_SESSION["usuario"]);
						}
					}
				}

				// Atualiza Situação Cliente
				$this->atualizaSituacaoCliente($this->dados_item["cliente"], 5);

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	########################
	### Técnico Reprovar ###
	########################

	function tecnico_reprovar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Reprovar (Técnico)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Reprovar (Técnico)</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {
				
				$this->anularContrato($this->dados_item, 7);
				$this->abreChamadoAnulaContratoReprovadoTecnico($this->dados_item);

				// Registra Comentário
				$this->registraComentario($this->dados_item["id"], 14, $_POST["comentario"], $_SESSION["usuario"]);

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}

		} else {
			$this->fechar();
		}

	}

	#################
	### Desativar ###
	#################

	function desativar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {

			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");

				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Desativar&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente <b>Desativar</b> este contrato?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->bancodedados->query("
					UPDATE
						tab_contratos
						INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
						INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
						INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
						LEFT OUTER JOIN tab_dominios ON (tab_dominios.ass_id = tab_assinaturas.ass_id)
						LEFT OUTER JOIN tab_visitas ON (tab_visitas.cor_id = tab_contratos.cor_id)
						LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.ass_id = tab_assinaturas.ass_id)
					SET
						tab_assinaturas.ast_id = 1,
						tab_assinaturas.ass_data_ativacao = NULL,
						tab_assinaturas.ass_instalacao = tab_planos.pla_instalacao,
						tab_servicos_ajustes.saj_inicio = NULL,
						tab_servicos_ajustes.sas_id = 2,
						tab_dominios.dos_id = 2,
						tab_visitas.via_id = IF(tab_visitas.via_id = 3, IF(tab_visitas.vis_data IS NULL, 1, 2), tab_visitas.via_id),
						tab_contratos.crs_id = 17,
						tab_contratos.cor_data_ativacao = NULL,
						tab_contratos.cor_termino_fidelidade = NULL,
						tab_contratos.fop_id = NULL
					WHERE
						tab_contratos.cor_id = {$this->dados_item["id"]}");

				$youCastDados = $this->buscarProdutoYoucast($this->dados_item["id"]);
				$codigoServico = addslashes($youCastDados["codigoServico"]);
				$codigoPlayer = $youCastDados["codigoPlayer"];
				if($youCastDados){
					if($codigoPlayer == 'youcast'){
						$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
						$sva_youcast->remover($this->dados_item["cliente"], $codigoServico);

						$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 2, envido_email = 0 WHERE contrato = {$this->dados_item["id"]}");
					}
				}

				// Registra comentário no contrato
				$this->registraComentario($this->dados_item["id"], 32, null, $_SESSION["usuario"]);

				// Registra comentário nos descontos
				for ($i = 0; $i < sizeof($this->dados_item["descontos"]); $i++) {

					$this->registraComentarioDesconto($this->dados_item["descontos"][$i]["id"], "<b>Ação:</b> Contrato Desativado ({$this->dados_item["codigo"]})", $_SESSION["usuario"]);

					// Atualiza Status, Data Início, Data Término
					if ($this->dados_item["descontos"][$i]["parametros"]["validade_tipo"] && $this->dados_item["descontos"][$i]["parametros"]["validade_tipo"] == 2) {
						$data_termino = $this->dados_item["descontos"][$i]["parametros"]["validade"] ? $this->formataData($this->dados_item["descontos"][$i]["parametros"]["validade"]) : "NULL";
					} else {
						$data_termino = "NULL";
					}

					$this->bancodedados->query("
						UPDATE
							tab_descontos_concessoes
						SET
							dec_data_inicio = NULL,
							dec_data_termino = $data_termino,
							dcs_id = 2
						WHERE
							dec_id = {$this->dados_item["descontos"][$i]["id"]}");

				}

				//Retira data Equipamento Comodato
				$this->bancodedados->query("
					UPDATE
						tab_clientes_comodatos
					SET
						clc_data_inicio = NULL,
						clc_data_termino = NULL,
						clc_data_retirada = NULL,
						ccs_id = 1
					WHERE
						cor_id = {$this->dados_item["id"]} AND
						clc_substituto_id IS NULL");

				// Registra comentário nas visitas
				for ($i = 0; $i < sizeof($this->dados_item["visitas"]); $i++) {
					$this->registraComentarioVisita($this->dados_item["visitas"][$i]["id"], "<b>Ação</b>: Contrato Desativado ({$this->dados_item["codigo"]})", 18, $_SESSION["usuario"]);
				}

				// Atualiza Status Cliente
				$this->atualizaStatusCliente($this->dados_item["cliente"]);

				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}

	}

	##############
	### Ativar ###
	##############

	function ativar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->ehAtivavel()) {
	
			$vinculos_nao_ativaveis = array();
	
			foreach ($this->dados_item["vinculos"] as $vinculo) {
				if ($vinculo["vinculo_tipo_id"] == 3 && (!$vinculo["sincronizado"] || $vinculo["status_id"] != 17)) {
					$vinculos_nao_ativaveis[] = "<b>&raquo;&nbsp; {$vinculo["codigo"]} - {$vinculo["servico_nome"]}</b>: {$vinculo["status_nome"]}";
				}
			}
	
			if (($cobrancas = $this->buscaCobrancasAtrasadas($this->dados_item["cliente"])) && (!$this->permissoes["contratos"]["gerenciar_inadimplentes"] || !$_POST)) {
	
				$botoes = array();
				if ($this->permissoes["contratos"]["gerenciar_inadimplentes"]) $botoes[] = '<input type="submit" name="sim" class="buttonAzulEscuroBranco11" value="Sim">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	
				$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");
	
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[erro]", "Este cliente possui $cobrancas cobrança(s) em atraso.<br>", $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", $this->permissoes["contratos"]["gerenciar_inadimplentes"] ? "Você deseja continuar com ativação deste contrato?" : "Não é possível continuar com a ativação deste contrato!<br>Entre em contato com o Setor Financeiro!", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
	
			} elseif ($vinculos_nao_ativaveis) {
	
				$vinculos_nao_ativaveis = implode("<br>", $vinculos_nao_ativaveis);
	
				$botoes = array();
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	
				$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");
	
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[erro]", "O(s) seguinte(s) contrato(s) vinculado(s) não pode(m) ser ativado(s):<br><br><table><tr><td class=\"txtAzulEscuro11\">$vinculos_nao_ativaveis</td></tr></table>", $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Não é possível continuar com a ativação deste contrato!", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
	
			} elseif (($erros = $this->buscaErrosAtivacao()) != false) {
	
				$erros = implode("<br>", $erros);
	
				$botoes = array();
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	
				$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");
	
				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Ativar&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[erro]", "O(s) seguinte(s) erro(s) foi(ram) encontrado(s):<br><br><b>$erros</b><br>", $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Não é possível continuar com a ativação deste contrato!", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
	
			} else {
	
				if ($this->dados_item) {
	
					if ($this->dados_item["tipo_id"] == 14) {
	
						$this->ativar_aditivo_me();
	
					} elseif ($this->dados_item["tipo_id"] == 15) {
	
						$this->ativar_aditivo_mt();
	
					} else {
	
						$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
	
						if (!$_POST) {
	
							$data_agendamento = $this->buscaDataAgendamento($this->dados_item["id"]);
	
							// 11 - Principal
							// 12 - Alteração Assinaturas
							// 13 - Migração
	
							$nf_ativa = (count($this->dados_item["formas_pagamento"]) == 1 && $this->dados_item["formas_pagamento"][0]["valor_parcela"] > 0) || (count($this->dados_item["formas_pagamento"]) == 0 && $this->dados_item["parametros"]["tabela_valor_final_instalacao"] > 0);
	
							$botoes = array();
							$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
							$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
							$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
	
							$opcoes_instalacao = $this->buscaOpcoesInstalacao($this->dados_item["parametros"], $this->dados_item["servico_id"], $this->dados_item["tipo_id"]);
	
							if ($this->dados_item["tipo_id"] == 11 || $this->dados_item["tipo_id"] == 12) {
	
								$this->conteudo .= $this->carregaArquivo("contratos.ativar_principal.inc.php");
	
								if ($this->dados_item["servico_id"] == 28 || ($this->dados_item["servico_id"] == 5 && $this->dados_item["parametros"]["tabela_valor_final_instalacao"] == 27000)) {
									$parcelamento = $this->buscaParcelamentos($this->dados_item["parametros"]["parcelamento"], $this->dados_item["parametros"]["parcelamento_forma"], $this->dados_item["parametros"]["tabela_valor_final_instalacao"] > 0 ? $this->buscaParcelamentoMaximo($this->dados_item["servico_id"]) : 1, $this->dados_item["parametros"]["tabela_valor_final_instalacao"], $this->dados_item["servico_id"], $this->dados_item["assinaturas"][0]["id"]);
									$this->conteudo = str_replace("[parcelamento]", $this->geraSelect(array($this->dados_item["parametros"]["parcelamento"] => $parcelamento), $this->dados_item["parametros"]["parcelamento"], true), $this->conteudo);
									$this->conteudo = str_replace("[parcelamento_disabled]", "", $this->conteudo);
								} else {
									$this->conteudo = str_replace("[parcelamento]", $this->geraSelect($this->buscaParcelamentos(null, $this->dados_item["parametros"]["parcelamento_forma"], $this->dados_item["parametros"]["tabela_valor_final_instalacao"] > 0 ? $this->buscaParcelamentoMaximo($this->dados_item["servico_id"]) : 1), $this->dados_item["parametros"]["tabela_valor_final_instalacao"] > 0 ? $this->dados_item["parametros"]["parcelamento"] : 1, true), $this->conteudo);
									$this->conteudo = str_replace("[parcelamento_disabled]", count($opcoes_instalacao) == 1 && key($opcoes_instalacao) > 0 ? "" : "disabled", $this->conteudo);
								}
	
								$this->conteudo = str_replace("[sem_visita_disabled]", count($opcoes_instalacao) == 1 && key($opcoes_instalacao) > 0 ? "disabled" : "", $this->conteudo);
								$this->conteudo = str_replace("[mensalidade_valor]", $this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_original_mensalidade"])." (".$this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_final_mensalidade"]).")", $this->conteudo);
	
							} else {
	
								$this->conteudo .= $this->carregaArquivo("contratos.ativar_com_visita.inc.php");
	
							}
	
							$this->conteudo = str_replace("[data_ativacao]", $data_agendamento ? $data_agendamento : date("d/m/Y"), $this->conteudo);
							$this->conteudo = str_replace("[instalador]", $this->geraSelect($this->buscaInstaladores()), $this->conteudo);
							$this->conteudo = str_replace("[situacao]", $this->geraListagemSituacao($this->dados_item["formas_pagamento"]), $this->conteudo);
							$this->conteudo = str_replace("[nf_numero]", "<input type=\"text\" name=\"nf_numero\" id=\"nf_numero\" class=\"inputBrancoAzulEscuro11\" style=\"width:150px\" style=\"width:150px\" maxlength=\"15\" onkeypress=\"return formataNumero(this, event)\" onblur=\"return formataNumero(this, event)\" ".($nf_ativa ? "" : "disabled ")."/>", $this->conteudo);
							$this->conteudo = str_replace("[nf_emissao]", "<input type=\"text\" name=\"nf_emissao\" id=\"nf_emissao\" class=\"inputBrancoAzulEscuro11\" style=\"width:150px\" maxlength=\"10\" onkeypress=\"return formataData(this, event)\" onblur=\"return formataData(this, event)\" ".($nf_ativa ? "" : "disabled ")."/>", $this->conteudo);
							$this->conteudo = str_replace("[nf_valor]", "<input type=\"text\" name=\"nf_valor\" id=\"nf_valor\" class=\"inputBrancoAzulEscuro11\" style=\"width:150px\" maxlength=\"13\" onkeypress=\"return formataDinheiro(this, event)\" onblur=\"return formataDinheiro(this, event)\" ".($nf_ativa ? "" : "disabled ")."/>", $this->conteudo);
							$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td>', $this->conteudo);
							$this->conteudo = str_replace("[cliente]", $dados_cliente["nome"], $this->conteudo);
							$this->conteudo = str_replace("[contrato]", $this->dados_item["codigo"], $this->conteudo);
							$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
							$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
							$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
							$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
	
						} else {
							
							$this->ativarContrato($_POST["data_ativacao"], $_POST["instalador"], $_POST["nf_numero"], $_POST["nf_emissao"], $_POST["nf_valor"], $_POST["comentario"], false);
							
							header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
							
						}
	
					}
	
				} else {
					$this->fechar();
				}
	
			}

		} elseif ($this->ehAtivavelSemPendencia()) {
			
			$this->ativarSemPendencia();
			
		}else {
			$this->fechar();
		}

	}
	
	##########################################
	### Ativar Aditivo Mudança de Endereço ###
	##########################################
	
	function ativar_aditivo_me() {
		
		$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
		
		if (!$_POST) {
			
			$nf_ativa = count($this->dados_item["formas_pagamento"]) == 1 && $this->dados_item["formas_pagamento"][0]["valor_parcela"] > 0;
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo .= $this->carregaArquivo("contratos.ativar_com_visita.inc.php");
			
			$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td>', $this->conteudo);
			$this->conteudo = str_replace("[contrato]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			$this->conteudo = str_replace("[cliente]", $dados_cliente["nome"], $this->conteudo);
			$this->conteudo = str_replace("[data_ativacao]", $this->buscaDataAgendamento($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace("[instalador]", $this->geraSelect($this->buscaInstaladores()), $this->conteudo);
			$this->conteudo = str_replace("[situacao]", $this->geraListagemSituacao($this->dados_item["formas_pagamento"]), $this->conteudo);
			$this->conteudo = str_replace("[nf_numero]", "<input type=\"text\" name=\"nf_numero\" id=\"nf_numero\" class=\"inputBrancoAzulEscuro11\" style=\"width:150px\" style=\"width:150px\" maxlength=\"15\" onkeypress=\"return formataNumero(this, event)\" onblur=\"return formataNumero(this, event)\" ".($nf_ativa ? "" : "disabled ")."/>", $this->conteudo);
			$this->conteudo = str_replace("[nf_emissao]", "<input type=\"text\" name=\"nf_emissao\" id=\"nf_emissao\" class=\"inputBrancoAzulEscuro11\" style=\"width:150px\" maxlength=\"10\" onkeypress=\"return formataData(this, event)\" onblur=\"return formataData(this, event)\" ".($nf_ativa ? "" : "disabled ")."/>", $this->conteudo);
			$this->conteudo = str_replace("[nf_valor]", "<input type=\"text\" name=\"nf_valor\" id=\"nf_valor\" class=\"inputBrancoAzulEscuro11\" style=\"width:150px\" maxlength=\"13\" onkeypress=\"return formataDinheiro(this, event)\" onblur=\"return formataDinheiro(this, event)\" ".($nf_ativa ? "" : "disabled ")."/>", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
		} else {
			
			$_POST["data_ativacao"] = ($_POST["data_ativacao"]) ? substr($_POST["data_ativacao"], 6, 4)."-".substr($_POST["data_ativacao"], 3, 2)."-".substr($_POST["data_ativacao"], 0, 2) : date("Y-m-d");
			
			$_POST["nf_numero"] = (int) preg_replace("/[^0-9]/", "", $_POST["nf_numero"]);
			$_POST["nf_emissao"] = $_POST["nf_emissao"] ? substr($_POST["nf_emissao"], 6, 4)."-".substr($_POST["nf_emissao"], 3, 2)."-".substr($_POST["nf_emissao"], 0, 2) : null;
			$_POST["nf_valor"] = (int) preg_replace("/[^0-9]/", "", $_POST["nf_valor"]);
			
			$this->geraCobrancaInstalacao($this->dados_item, $_POST["data_ativacao"], false, $_POST["nf_numero"], $_POST["nf_emissao"], $_POST["nf_valor"]);
			
			if ($_POST["instalador"]) {
				$this->bancodedados->query("
					UPDATE
						tab_visitas
					SET
						usu_id = {$_POST["instalador"]}
					WHERE
						cor_id = {$this->dados_item["id"]} AND
						via_id != 4");
			}
			
			if (strtotime($_POST["data_ativacao"]) <= time()) {
				$this->ativar_contrato_aditivo_me($this->dados_item, $_POST["data_ativacao"], 20, $_POST["comentario"]);
			} else {
				$this->agendarAtivacao($this->dados_item["id"], $_POST["data_ativacao"], $_POST["comentario"], $_SESSION["usuario"]);
			}
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
		}
		
	}
	
	##############################################
	### Ativar Aditivo Mudança de Titularidade ###
	##############################################
	
	function ativar_aditivo_mt() {
		
		$dados_cliente = $this->buscaDadosCadastraisCliente($this->dados_item["cliente"]);
		
		if (!$_POST["data_ativacao"]) {
			
			$botoes = array();
			$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo .= $this->carregaArquivo("contratos.ativar_mt.inc.php");
			
			$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Ativar&nbsp;</b></td>', $this->conteudo);
			$this->conteudo = str_replace("[contrato]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[cliente]", $dados_cliente["nome"], $this->conteudo);
			$this->conteudo = str_replace("[data_ativacao]", date("d/m/Y"), $this->conteudo);
			$this->conteudo = str_replace("[exibir_mensagem_impressao]", (($this->dados_item["tipo_parametros"]["impressao_obrigatoria"]) ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none"), $this->conteudo);
			$this->conteudo = str_replace("[contrato_tipo]", $this->dados_item["tipo_id"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
		} else {
			
			$_POST["data_ativacao"] = ($_POST["data_ativacao"]) ? substr($_POST["data_ativacao"], 6, 4)."-".substr($_POST["data_ativacao"], 3, 2)."-".substr($_POST["data_ativacao"], 0, 2) : date("Y-m-d");
			
			if (strtotime($_POST["data_ativacao"]) <= time()) {
				$this->ativar_contrato_aditivo_mt($this->dados_item, $_POST["data_ativacao"], 20, $_POST["comentario"]);
			} else {
				$this->agendarAtivacao($this->dados_item["id"], $_POST["data_ativacao"], $_POST["comentario"], $_SESSION["usuario"]);
			}
			
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);
			
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
		}
		
	}
	
	############################
	### Ativar Sem Pendência ###
	############################
	
	function ativarSemPendencia() {
		
		//TODO Permitir inserção dos dados que não foram inseridos previamente (NF, Instalador)
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.alterar_status.inc.php");
				
				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Ativo (Sem Pendências)&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[mensagem]", "Você deseja realmente definir este contrato como <b>Ativo (Sem Pendências)</b>?", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
				
			} else {
				
				// Atualiza Status Contrato
				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						crs_id = 20
					WHERE
						tab_contratos.cor_id = {$this->dados_item["id"]}");
				
				// Registra Comentário
				$this->registraComentario($this->dados_item["id"], 19, $_POST["comentario"], $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);
				
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	#######################
	### Ativar Contrato ###
	#######################

	function ativarContrato($data_ativacao = null, $instalador_id = null, $nf_numero = 0, $nf_emissao = null, $nf_valor = 0, $comentario = null, $com_pendencia = true) {
		
		$this->bancodedados->transacaoIniciar();
		
		if ($this->permissoes && !$this->ehAtivavel()) {
			return;
		}		
		
		$data_ativacao = $data_ativacao ? substr($data_ativacao, 6, 4)."-".substr($data_ativacao, 3, 2)."-".substr($data_ativacao, 0, 2) : date("Y-m-d");

		// Equipamentos

		$this->bancodedados->query("
			UPDATE
				tab_clientes_comodatos
			SET
				tab_clientes_comodatos.clc_data_inicio = IF(tab_clientes_comodatos.clc_data_inicio IS NULL, '{$data_ativacao}', tab_clientes_comodatos.clc_data_inicio),
				tab_clientes_comodatos.clc_data_termino = NULL,
				tab_clientes_comodatos.clc_data_retirada = NULL,
				tab_clientes_comodatos.ccs_id = 2
			WHERE
				tab_clientes_comodatos.cor_id = {$this->dados_item["id"]} AND
				tab_clientes_comodatos.clc_substituto_id IS NULL");

		// Ativação

		if ($this->dados_item["tipo_id"] == 13 || strtotime($data_ativacao) <= strtotime(date("Y-m-d"))) {
			$contrato_status = $this->possuiContrato($this->dados_item) ? 20 : 19;
			if ($this->dados_item["tipo_id"] == 11) { // Principal
				$this->ativar_contrato_principal($this->dados_item, $data_ativacao, $contrato_status);
			} elseif ($this->dados_item["tipo_id"] == 12) { // Aditivo Alteração Assinaturas
				$this->ativar_contrato_aditivo_alteracao($this->dados_item, $data_ativacao, $contrato_status);
			} elseif ($this->dados_item["tipo_id"] == 13) { // Aditivo Migração Assinaturas
				$this->ativar_contrato_aditivo_migracao($this->dados_item, $data_ativacao, $contrato_status);
			}
		} else {
			$this->agendarAtivacao($this->dados_item["id"], $data_ativacao, $comentario, $_SESSION["usuario"]);
		}

		// Instalador

		$instalador_id = $instalador_id ? $instalador_id : "NULL";

		$this->bancodedados->query("
			UPDATE
				tab_visitas
			SET
				usu_id = {$instalador_id}
			WHERE
				cor_id = {$this->dados_item["id"]} AND
				via_id != 4");
		
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				cor_multiservice_enviado = 0
			WHERE
				cor_id = {$this->dados_item["id"]}");

		$youCastDados = $this->buscarProdutoYoucast($this->dados_item["id"]);
		$codigoServico = addslashes($youCastDados["codigoServico"]);
		$codigoPlayer = $youCastDados["codigoPlayer"];
		if($codigoPlayer == 'youcast'){
			$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
			$sva_youcast->adicionar($this->dados_item["cliente"], $codigoServico, $this->dados_item["origem"]);
			
			$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 1 WHERE contrato = {$this->dados_item["id"]}");
		}

		/*
		
		// Cobrança Instalação

		$nf_numero = (int) preg_replace("/[^0-9]/", "", $nf_numero);
		$nf_emissao = $nf_emissao ? substr($nf_emissao, 6, 4)."-".substr($nf_emissao, 3, 2)."-".substr($nf_emissao, 0, 2) : null;
		$nf_valor = (int) preg_replace("/[^0-9]/", "", $nf_valor);

		//TODO E se ativação tiver sido agendada?
		$this->geraCobrancaInstalacao($this->dados_item, $data_ativacao, true, $nf_numero, $nf_emissao, $nf_valor);

		*/

		// Registra Atividade

		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);

		$this->bancodedados->transacaoTerminar();

	}

	################################
	### Gera Cobrança Instalação ###
	################################

	function geraCobrancaInstalacao($dados_contrato, $data_ativacao, $gerar_cobranca = true, $nf_numero = null, $nf_emissao = null, $nf_valor = null) {

		$cobranca_gerada = false;

		/**
		 * @var cobrancas_geracao $geracao
		 */
		$geracao = controle::instanciaObjeto("cobrancas.geracao", $this->bancodedados);
		
		$prepago = $this->possuiAssinaturaPrePagaPrincipal($dados_contrato["id"]);
		$dados_cliente = $this->buscaDadosCliente($dados_contrato["cliente"]);

		// Parcelas
		
		if ($dados_contrato["parametros"]["parcelamento"] > 0 && $dados_contrato["parametros"]["tabela_valor_parcela"] > 0) {

			if ($dados_contrato["parametros"]["parcelamento_forma"] == 1) {
				
				$mes = substr($data_ativacao, 5, 2);
				$ano = substr($data_ativacao, 0, 4);

				$parcelamento = $dados_contrato["parametros"]["parcelamento"];
				$meses = $parcelamento - 1;
				$competencia_inicial = date("Y-m-01", strtotime("{$ano}{$mes}-01 1 month ago"));
				$competencia_final = date("Y-m-01", strtotime("{$competencia_inicial} + {$meses} month"));
				$valor = $dados_contrato["parametros"]["tabela_valor_parcela"];
				
				$this->geraAjusteInstalacao($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["codigo"], $parcelamento, $valor, $competencia_inicial, $competencia_final, $nf_numero, $nf_emissao, $nf_valor);
			
			} else {

				$mes = substr($data_ativacao, 5, 2);
				$ano = substr($data_ativacao, 0, 4);

				$competencia_inicial = date("Y-m-01", strtotime("{$ano}{$mes}-01 1 month ago"));
				$competencia_final = $competencia_inicial;
				$parcelamento = 1;
				$valor = $dados_contrato["parametros"]["parcelamento"] * $dados_contrato["parametros"]["tabela_valor_parcela"];
				
				$this->geraAjusteInstalacao($dados_contrato["cliente"], $dados_contrato["id"], $dados_contrato["codigo"], $parcelamento, $valor, $competencia_inicial, $competencia_final, $nf_numero, $nf_emissao, $nf_valor);
				
				$formato_id = 1;
				$vencimento = date("m/Y", strtotime($data_ativacao));
				$vencimento_cobranca = $data_ativacao;
				
				if (($cobranca_id = $geracao->geraCobrancaAvulsa($dados_contrato["cliente"], $formato_id, $vencimento, $vencimento, $vencimento_cobranca, date("d", strtotime($vencimento_cobranca)), false, 0, 0, true, true)) != false) {
					$cobranca_gerada = true;
				}
				
			}
			
		}

		// Cobrança Mensalidade

		$formato_id = $this->buscaFormatoBoleto($dados_cliente["origem_id"]);

		if ($gerar_cobranca && !$dados_cliente["cliente_pai_id"] && $dados_contrato["tipo_id"] == 11) {

			$lote_gerado = $this->loteGerado($data_ativacao);
			$mes_ativacao = date("Y-m-01", strtotime($data_ativacao));

			// Mês Atual

			if ($prepago || $lote_gerado) {

				$vencimento = $prepago ? date("m/Y", strtotime($mes_ativacao)) : date("m/Y", strtotime("{$mes_ativacao} next month"));

				if ($prepago) {
				    $vencimento_cobranca = date("Y-m-{$dados_cliente["vencimento"]}", strtotime($data_ativacao));
				    if (strtotime($vencimento_cobranca) < strtotime("+ 5 days")) {
				        $vencimento_cobranca = date("Y-m-d", strtotime("+ 5 days"));
				    }
				} else {
					$vencimento_cobranca = date("Y-m-{$dados_cliente["vencimento"]}", strtotime("{$mes_ativacao} next month"));
				}

				if (strtotime($vencimento_cobranca) < strtotime("+ 5 days")) {
					$vencimento_cobranca = date("Y-m-d", strtotime("+ 5 days"));
				}

				if (($cobranca_id = $geracao->geraCobrancaAvulsa($dados_contrato["cliente"], $formato_id, $vencimento, $vencimento, $vencimento_cobranca, date("d", strtotime("5 days")), false)) != false) {
					$cobranca_gerada = true;
					$this->enviaMensagemCobranca($cobranca_id);
				}

			}

			// Mês Seguinte

			if ($prepago && $lote_gerado) {

				$vencimento = date("m/Y", strtotime("{$mes_ativacao} next month"));
				$vencimento_cobranca = date("Y-m-{$dados_cliente["vencimento"]}", strtotime("{$mes_ativacao} next month"));

				if (strtotime($vencimento_cobranca) < strtotime("+ 5 days")) {
					$vencimento_cobranca = date("Y-m-d", strtotime("+ 5 days"));
				}

				if (($cobranca_id = $geracao->geraCobrancaAvulsa($dados_contrato["cliente"], $formato_id, $vencimento, $vencimento, $vencimento_cobranca, $dados_cliente["vencimento"], false)) != false) {
					$cobranca_gerada = true;
					$this->enviaMensagemCobranca($cobranca_id);
				}

			}

		}

		if ($cobranca_gerada && $dados_contrato["origem_id"] == 1) {

			$auxiliares_financeiros = $this->buscaAxuliaresFinanceiros();

			foreach ($auxiliares_financeiros as $auxiliar_financeiro) {
				$this->bancodedados->query("
					INSERT INTO
						tab_clientes_retornos
					SET
						cli_id = {$this->dados_item["cliente"]},
						usu_id = {$auxiliar_financeiro},
						clr_abertura = NOW(),
						clr_data = CURDATE(),
						clr_comentario = 'Contrato ativo! Aguardando envio de cobrança</b>.',
						cet_id = 2,
						ces_id = 1");
			}

		}

	}

	#######################
	### Enviar Cobrança ###
	#######################

	function enviar_cobranca() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		$cobranca_id = $this->buscaCobranca($this->dados_item["cliente"], $this->dados_item["data_ativacao"]);
		
		if ($this->dados_item && $cobranca_id) {
			
			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
			$dados_cobranca = $this->buscaDadosCobranca($cobranca_id);
			
			if (!$_POST) {
			    
			    $lista_emails = array();
				
				foreach ($dados_cliente["emails"] as $email) {
					$lista_emails[] = $email["contato_email"];
				}
				
				$lista_emails = implode("; ", $lista_emails);
				
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				$this->conteudo .= $this->carregaArquivo("contratos.enviar_cobranca.inc.php");
				
				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Enviar Cobrança&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[emails]", $lista_emails, $this->conteudo);
				$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_codigo]", $dados_cobranca["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_formato]", $dados_cobranca["formato"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_vencimento]", $dados_cobranca["data_vencimento"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_valor]", $this->formataDinheiro($dados_cobranca["valor_original"]), $this->conteudo);				
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=enviar_cobranca&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				sort($emails = array_unique(preg_split("/ *[ ,;] */", strtolower($_POST["emails"]))));
				
				$this->enviaMensagemCobranca($cobranca_id, $emails);
				
				// Registra Comentário Contrato
				$comentario = $_POST["comentario"] ? trim($_POST["comentario"])."<br>" : "";
				$this->registraComentario($this->dados_item["id"], 28, $comentario."<b>Código da Cobrança</b>: {$dados_cobranca["codigo"]}<br><b>E-mail(s)</b>: ".implode(", ", $emails), $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}

	#################################
	### Ativar Contrato Principal ###
	#################################

	function ativar_contrato_principal($dados_contrato, $data_ativacao, $contrato_status, $vinculado = false) {
		
		//TODO Abrir chamado para Administrativo se ativo com pendências
		
		// Ajusta Contrato Vinculado
		if ($vinculado && count($dados_contrato["formas_pagamento"]) == 1) {

			$situacao = $dados_contrato["formas_pagamento"][0];
			$dados_contrato["vigencia"] = $situacao["fidelidade"];
			$dados_contrato["multa"] = $situacao["multa"];

			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_vigencia = {$dados_contrato["vigencia"]},
					cor_multa = {$dados_contrato["multa"]},
					fop_id = {$situacao["id"]}
				WHERE
					cor_id = {$dados_contrato["id"]}");

		}
		
		// Atualiza Contrato / Assinatura / Descontos / Dominios / Visitas
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				INNER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
				INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN rel_dec_cor_estacontido ON (tab_contratos.cor_id = rel_dec_cor_estacontido.cor_id)
				LEFT OUTER JOIN tab_descontos_concessoes ON (rel_dec_cor_estacontido.dec_id = tab_descontos_concessoes.dec_id)
				LEFT OUTER JOIN tab_dominios ON (tab_dominios.ass_id = tab_assinaturas.ass_id)
				LEFT OUTER JOIN tab_visitas ON (tab_visitas.cor_id = tab_contratos.cor_id)
				LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.ass_id = tab_assinaturas.ass_id)
			SET
				tab_assinaturas.ast_id = 2,
				tab_assinaturas.ass_data_ativacao = '$data_ativacao',
				tab_assinaturas.ass_data_confirmacao = IF(tab_assinaturas.ass_data_confirmacao IS NULL, CURDATE(), tab_assinaturas.ass_data_confirmacao),
				tab_servicos_ajustes.saj_inicio = NOW(),
				tab_servicos_ajustes.sas_id = 5,
				tab_descontos_concessoes.dcs_id = IF(dcs_id IN (2, 5), 5, 4),
				tab_descontos_concessoes.dec_data_inicio = IF(dcs_id IN (2, 5), '$data_ativacao', NULL),
				tab_descontos_concessoes.dec_data_termino = IF(dcs_id IN (2, 5), IF(tab_descontos_concessoes.dec_validade IS NOT NULL, DATE_SUB(DATE_ADD('$data_ativacao', INTERVAL tab_descontos_concessoes.dec_validade MONTH), INTERVAL 1 DAY), tab_descontos_concessoes.dec_data_termino), tab_descontos_concessoes.dec_data_termino),
				tab_dominios.dos_id = 3,
				tab_visitas.via_id = 3,
				tab_visitas.vis_data = '$data_ativacao',
				tab_contratos.crs_id = $contrato_status,
				tab_contratos.cor_data_ativacao = '$data_ativacao',
				tab_contratos.cor_termino_fidelidade = IF(tab_contratos.cor_multa > 0 AND tab_contratos.cor_vigencia > 0, DATE_SUB(DATE_ADD('$data_ativacao', INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), tab_contratos.cor_termino_fidelidade),
				tab_contratos.cor_data_confirmacao = IF(tab_contratos.cor_data_confirmacao IS NULL, CURDATE(), tab_contratos.cor_data_confirmacao)
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
		
		// Finaliza Ajuste Contrato Inativo
		for ($i = 0; $i < sizeof($dados_contrato["assinaturas"]); $i++) {
			$this->bancodedados->query("
				UPDATE
					tab_servicos_ajustes
					INNER JOIN rel_saj_ass_contem ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id)
					LEFT OUTER JOIN tab_clientes_pontos ON (rel_saj_ass_contem.ass_id = tab_clientes_pontos.ass_id)
					LEFT OUTER JOIN tab_clientes_autenticacoes ON (rel_saj_ass_contem.ass_id = tab_clientes_autenticacoes.ass_id)
					LEFT OUTER JOIN tab_clientes_emails ON (rel_saj_ass_contem.ass_id = tab_clientes_emails.ass_id)
				SET
					tab_clientes_pontos.clp_publicado = 0,
					tab_clientes_pontos.clp_ajustado = 0,
					tab_clientes_pontos.clp_parametros_ajuste = '',
					tab_clientes_autenticacoes.cau_publicado = 0,
					tab_clientes_autenticacoes.cau_ajustado = 0,
					tab_clientes_autenticacoes.cau_ajuste_download = NULL,
					tab_clientes_autenticacoes.cau_ajuste_upload = NULL,
					tab_clientes_emails.cle_publicado = 0,
					tab_clientes_emails.cle_bloqueado = 0,
					tab_servicos_ajustes.saj_termino = NOW(),
					tab_servicos_ajustes.sas_id = 8
				WHERE
					rel_saj_ass_contem.ass_id = {$dados_contrato["assinaturas"][$i]["id"]} AND
					tab_servicos_ajustes.sas_id <= 5 AND
					tab_servicos_ajustes.sac_id = 3");
		}
		
		// Altera Cliente Pai (Condominial)
		$dados_cliente = $this->buscaDadosCliente($dados_contrato["cliente"]);

		if ($dados_cliente["status"] == 1 && $dados_cliente["condominio_id"] && ($condominial_id = $this->buscaCondominial($dados_cliente["condominio_id"], $dados_cliente["id"]))) {

			$this->bancodedados->query("
				UPDATE
					tab_clientes
				SET
					cli_pai = $condominial_id,
					cof_id = NULL,
					cli_ccd_debito_operacao = NULL,
					cli_ccd_debito_agencia = NULL,
					cli_ccd_debito_conta = NULL,
					cli_ccd_debito_codigo = NULL,
					cli_ccd_vencimento = NULL
				WHERE
					cli_id = {$dados_contrato["cliente"]}");

		} else {

			$dados_cliente["vencimento"] = $dados_cliente["vencimento"] ? $dados_cliente["vencimento"] : "5";
			$dados_cliente["formato_id"] = $dados_cliente["formato_id"] > 1 ? $dados_cliente["formato_id"] : $this->buscaFormatoBoletoPadrao($dados_cliente["origem_id"]);
			$dados_cliente["formato_id"] = $dados_cliente["formato_id"] ? $dados_cliente["formato_id"] : 1;

			$this->bancodedados->query("
				UPDATE
					tab_clientes
				SET
					cli_pai = NULL,
					cof_id = {$dados_cliente["formato_id"]},
					cli_ccd_vencimento = {$dados_cliente["vencimento"]}
				WHERE
					cli_id = {$dados_contrato["cliente"]}");

		}
		
		// Registra comentário no contrato
		$this->registraComentario($dados_contrato["id"], $contrato_status == 19 ? 18 : 19, $_POST["comentario"], $_SESSION["usuario"]);
		
		// Registra comentário nos descontos
		for ($i = 0; $i < sizeof($dados_contrato["descontos"]); $i++) {
			$this->registraComentarioDesconto($dados_contrato["descontos"][$i]["id"],"<b>Ação:</b> Contrato Ativo ({$dados_contrato["codigo"]})", $_SESSION["usuario"]);
		}
		
		// Registra comentário nas visitas
		for ($i = 0; $i < sizeof($dados_contrato["visitas"]); $i++) {
			if (in_array($dados_contrato["visitas"][$i]["status_id"], array(1, 2))) {
				$this->registraComentarioVisita($dados_contrato["visitas"][$i]["id"],"<b>Ação</b>: Contrato Ativo ({$dados_contrato["codigo"]})", 18, $_SESSION["usuario"]);
			}
		}
		
		// Gera Vinculos Pais/Filhos
		for ($i = 0; $i < sizeof($dados_contrato["assinaturas"]); $i++) {
			// Gera Vinculos Pai
			$vinculos_pai = $this->buscaPlanosVinculosPai($dados_contrato["assinaturas"][$i]["id"]);
			foreach ($vinculos_pai as $vinculo_pai) {
				$this->criaVinculoAssinaturas($vinculo_pai["ass_id"], $dados_contrato["assinaturas"][$i]["id"], $vinculo_pai["tipo_id"]);
				$this->criaVinculoContratos($vinculo_pai["cor_id"], $dados_contrato["id"], $vinculo_pai["tipo_id"]);
			}
			// Gera Vinculos Filho
			$vinculos_filho = $this->buscaPlanosVinculosFilho($dados_contrato["assinaturas"][$i]["id"]);
			foreach ($vinculos_filho as $vinculo_filho) {
				$this->criaVinculoAssinaturas($dados_contrato["assinaturas"][$i]["id"], $vinculo_filho["ass_id"], $vinculo_filho["tipo_id"]);
				$this->criaVinculoContratos($dados_contrato["id"], $vinculo_filho["cor_id"], $vinculo_filho["tipo_id"]);
			}
		}
		
		// Atualiza Situacao Cliente
		$this->atualizaSituacaoCliente($dados_contrato["cliente"], 5); // Fechado (Com Contrato)
		
		// Ativa Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			if ($vinculo["sincronizado"] && $vinculo["status_id"] == 17) {
				$this->ativar_contrato_principal($this->buscaDadosContrato($vinculo["contrato_id"]), $data_ativacao, $contrato_status, true);
			}
		}
		
		// Ativa Contratos Auto-Ativáveis
		$contratos_auto_ativaveis = $this->buscaContratosAutoAtivaveis($dados_contrato["cliente"]);
		foreach ($contratos_auto_ativaveis as $contrato_auto_ativavel_id) {
			$this->ativar_contrato_principal($this->buscaDadosContrato($contrato_auto_ativavel_id), $data_ativacao, $contrato_status);
		}
		
		// Atualiza Status Cliente
		$this->atualizaStatusCliente($dados_contrato["cliente"]);
		
		// Atualiza Dias Bloqueio Cliente
		$this->atualizaDiasBloqueioCliente($dados_contrato["cliente"]);
		
		// Remove Responsável Cliente
		$this->bancodedados->query("
			UPDATE
				tab_clientes
			SET
				usu_id = NULL
			WHERE
				cli_id = '{$dados_contrato["cliente"]}'");
		
		// Gera Cobrança Instalação
		$this->geraCobrancaInstalacao($dados_contrato, $data_ativacao, !$vinculado);
		
	}

	########################################################
	### Ativar Contrato Aditivo Alteração de Assinaturas ###
	########################################################

	function ativar_contrato_aditivo_alteracao($dados_contrato, $data_ativacao, $contrato_status) {
		
		//TODO Abrir chamado para Administrativo se ativo com pendências e houve cobrança instalação
		
		// Atualização Contrato / Visitas
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
			SET
				tab_visitas.via_id = 3,
				tab_visitas.vis_data = '$data_ativacao',
				tab_contratos.crs_id = $contrato_status,
				tab_contratos.cor_data_ativacao = '$data_ativacao',
				tab_contratos.cor_termino_fidelidade = IF(tab_contratos.cor_multa > 0 AND tab_contratos.cor_vigencia > 0, DATE_SUB(DATE_ADD('$data_ativacao', INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), tab_contratos.cor_termino_fidelidade),
				tab_contratos.cor_data_confirmacao = IF(tab_contratos.cor_data_confirmacao IS NULL, CURDATE(), tab_contratos.cor_data_confirmacao)
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
		
		// Assinaturas Adicionadas
		$assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($dados_contrato["id"]);

		if ($assinaturas_adicionadas) {

			$this->geraAjusteInstalacaoAditivo($dados_contrato, $data_ativacao);

			foreach ($assinaturas_adicionadas as $assinatura) {

				// Insere Link Assinaturas Adicionadas
				$this->bancodedados->query("
					INSERT INTO
						rel_ass_cor_estacontido
					SET
						ass_id = {$assinatura["ass_id"]},
						cor_id = {$assinatura["cor_id"]}");

				// Ativa Assinaturas Adicionadas
				$this->bancodedados->query("
					UPDATE
						tab_assinaturas
						LEFT OUTER JOIN tab_dominios ON (tab_assinaturas.ass_id = tab_dominios.ass_id)
						LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.ass_id = tab_assinaturas.ass_id)
					SET
						tab_assinaturas.ast_id = 2,
						tab_assinaturas.ass_data_ativacao = '$data_ativacao',
						tab_assinaturas.ass_data_confirmacao = IF(tab_assinaturas.ass_data_confirmacao IS NULL, CURDATE(), tab_assinaturas.ass_data_confirmacao),
						tab_servicos_ajustes.saj_inicio = NOW(),
						tab_servicos_ajustes.sas_id = 5,
						tab_dominios.dos_id = 3
					WHERE
						tab_assinaturas.ass_id = {$assinatura["ass_id"]}");

			}

		}
		
		// Assinaturas Removidas
		$assinaturas_removidas = $this->buscaAssinaturasRemovidas($dados_contrato["id"]);
		
		foreach ($assinaturas_removidas as $assinatura) {

			// Remove Link Assinaturas Removidas
			$this->bancodedados->query("
				DELETE FROM
					rel_ass_cor_estacontido
				WHERE
					ass_id = {$assinatura["ass_id"]} AND
					cor_id = {$assinatura["cor_id"]}");
			
			// Desativa Assinaturas Removidas
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
					LEFT OUTER JOIN tab_dominios ON (tab_dominios.ass_id = tab_assinaturas.ass_id)
					LEFT OUTER JOIN tab_clientes_pontos ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
					LEFT OUTER JOIN tab_servidores_servicos ON (tab_servidores_servicos.sei_id = tab_clientes_pontos.sei_id)
					LEFT OUTER JOIN rel_saj_clp_contem ON (rel_saj_clp_contem.clp_id = tab_clientes_pontos.clp_id)
					LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.saj_id = rel_saj_clp_contem.saj_id OR tab_servicos_ajustes.ass_id = tab_assinaturas.ass_id)
				SET
					tab_assinaturas.ast_id = 3,
					tab_assinaturas.ass_data_cancelamento = '$data_ativacao',
					tab_dominios.dos_id = 5,
					tab_clientes_pontos.cps_id = 3,
					tab_clientes_pontos.clp_ajustado = 0,
					tab_clientes_pontos.clp_parametros_ajuste = '',
					tab_servidores_servicos.ses_data_alteracao = NOW(),
					tab_servicos_ajustes.saj_termino = IF(tab_servicos_ajustes.sas_id <= 5, NOW(), tab_servicos_ajustes.saj_termino),
					tab_servicos_ajustes.sas_id = IF(tab_servicos_ajustes.sas_id <= 5, 6, tab_servicos_ajustes.sas_id)
				WHERE
					tab_assinaturas.ass_id = {$assinatura["ass_id"]}");
			
			// Registra Comentário Pontos
			$pontos = $this->buscaClientePontosAssinatura($assinatura["ass_id"]);
			foreach ($pontos as $ponto_id) {
				$this->registraComentarioClientePonto($ponto_id, "<b>Ação:</b> Assinatura Cancelada", $_SESSION["usuario"]);
			}
			
			// Desativa Autenticação PPPoe
			$this->bancodedados->query("
				UPDATE
					tab_clientes_autenticacoes
					LEFT OUTER JOIN rel_saj_cau_contem ON (rel_saj_clp_contem.cau_id = tab_clientes_autenticacoes.cau_id)
					LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.saj_id = rel_saj_cau_contem.saj_id)
				SET
					tab_clientes_autenticacoes.cas_id = 3,
					tab_clientes_autenticacoes.cau_ajustado = 0,
					tab_clientes_autenticacoes.cau_publicado = 0,
					tab_clientes_autenticacoes.cau_ajuste_download = NULL,
					tab_clientes_autenticacoes.cau_ajuste_upload = NULL,
					tab_servicos_ajustes.saj_termino = IF(tab_servicos_ajustes.sas_id <= 5, NOW(), tab_servicos_ajustes.saj_termino),
					tab_servicos_ajustes.sas_id = IF(tab_servicos_ajustes.sas_id <= 5, 6, tab_servicos_ajustes.sas_id)
				WHERE
					tab_clientes_autenticacoes.ass_id = {$assinatura["ass_id"]}");
					
			// Registra Comentário Autenticação
			$autenticacoes = $this->buscaClienteAutenticacoesAssinatura($assinatura["ass_id"]);
			foreach ($autenticacoes as $autenticacao_id) {
				$this->registraComentarioClienteAutenticacao($autenticacao_id, "<b>Ação:</b> Assinatura Cancelada", $_SESSION["usuario"]);
			}
			
			// Migra e-mails
			$this->migraEmails($assinatura["ass_id"], $assinaturas_removidas, $_SESSION["usuario"]);
			
		}
		
		// Descontos Adicionados
		$descontos_adicionados = $this->buscaDescontosAdicionados($dados_contrato["id"]);
		foreach ($descontos_adicionados as $desconto) {

			// Insere Link Descontos Adicionados
			$this->bancodedados->query("
				INSERT INTO
					rel_dec_cor_estacontido
				SET
					dec_id = {$desconto["dec_id"]},
					cor_id = {$desconto["cor_id"]}");

			// Ativa Descontos Adicionados
			$this->bancodedados->query("
				UPDATE
					tab_descontos_concessoes
				SET
					dcs_id = IF(dcs_id IN (2, 5), 5, 4),
					dec_data_termino = IF(dec_data_termino IS NULL, IF(dec_validade IS NOT NULL, DATE_SUB(DATE_ADD('$data_ativacao', INTERVAL dec_validade MONTH), INTERVAL 1 DAY), NULL), dec_data_termino),
					dec_data_inicio = '$data_ativacao'
				WHERE
					dec_id = {$desconto["dec_id"]}");

		}
		
		// Descontos Removidos
		$descontos_removidos = $this->buscaDescontosRemovidos($dados_contrato["id"]);
		foreach ($descontos_removidos as $desconto) {

			// Remove Link Descontos Removidos
			$this->bancodedados->query("
				DELETE FROM
					rel_dec_cor_estacontido
				WHERE
					dec_id = {$desconto["dec_id"]} AND
					cor_id = {$desconto["cor_id"]}");

			// Desativa Descontos Removidos
			$this->bancodedados->query("
				UPDATE
					tab_descontos_concessoes
				SET
					dcs_id = 6
				WHERE
					dec_id = {$desconto["dec_id"]}");

			// Registra Comentário Desconto
			$this->registraComentarioDesconto($desconto["dec_id"], "<b>Ação:</b> Assinatura Cancelada", $_SESSION["usuario"]);

		}
		
		// Finaliza Ajuste Contrato Inativo
		if ($dados_contrato["tipo_id"] == 11 || $dados_contrato["tipo_id"] == 12) { // Aditivo de Adição
			for ($i = 0; $i < sizeof($dados_contrato["assinaturas"]); $i++) {

				// Cancela Pontos/Ajustes
				$this->bancodedados->query("
					UPDATE
						tab_clientes_pontos
						LEFT OUTER JOIN tab_servidores_servicos ON (tab_servidores_servicos.sei_id = tab_clientes_pontos.sei_id)
						LEFT OUTER JOIN rel_saj_clp_contem ON (rel_saj_clp_contem.clp_id = tab_clientes_pontos.clp_id)
						LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.saj_id = rel_saj_clp_contem.saj_id)
					SET
						tab_clientes_pontos.clp_publicado = 0,
						tab_clientes_pontos.clp_ajustado = 0,
						tab_clientes_pontos.clp_parametros_ajuste = '',
						tab_servidores_servicos.ses_data_alteracao = NOW(),
						tab_servicos_ajustes.saj_termino = IF(tab_servicos_ajustes.sas_id <= 5, NOW(), tab_servicos_ajustes.saj_termino),
						tab_servicos_ajustes.sas_id = IF(tab_servicos_ajustes.sas_id <= 5, 6, tab_servicos_ajustes.sas_id)
					WHERE
						tab_clientes_pontos.ass_id = {$dados_contrato["assinaturas"][$i]["id"]}");

				// Cancela Autenticações/Ajustes
				$this->bancodedados->query("
					UPDATE
						tab_clientes_autenticacoes
						LEFT OUTER JOIN rel_saj_cau_contem ON (rel_saj_cau_contem.cau_id = tab_clientes_autenticacoes.cau_id)
						LEFT OUTER JOIN tab_servicos_ajustes ON (tab_servicos_ajustes.saj_id = rel_saj_cau_contem.saj_id)
					SET
						tab_clientes_autenticacoes.cau_ajustado = 0,
						tab_clientes_autenticacoes.cau_publicado = 0,
						tab_clientes_autenticacoes.cau_ajuste_download = NULL,
						tab_clientes_autenticacoes.cau_ajuste_upload = NULL,
						tab_servicos_ajustes.saj_termino = IF(tab_servicos_ajustes.sas_id <= 5, NOW(), tab_servicos_ajustes.saj_termino),
						tab_servicos_ajustes.sas_id = IF(tab_servicos_ajustes.sas_id <= 5, 6, tab_servicos_ajustes.sas_id)
					WHERE
						tab_clientes_autenticacoes.ass_id = {$dados_contrato["assinaturas"][$i]["id"]}");

			}
		}
		
		// Registra comentário no contrato
		$this->registraComentario($dados_contrato["id"], ($contrato_status == 19) ? 18 : 19, $_POST["comentario"], $_SESSION["usuario"]);
		
		// Registra comentário nos descontos
		for ($i = 0; $i < sizeof($dados_contrato["descontos"]); $i++) {
			$this->registraComentarioDesconto($dados_contrato["descontos"][$i]["id"],"<b>Ação:</b> Contrato Ativo ({$dados_contrato["codigo"]})",$_SESSION["usuario"]);
		}
		
		// Registra comentário nas visitas
		for ($i = 0; $i < sizeof($dados_contrato["visitas"]); $i++) {
			if (in_array($dados_contrato["visitas"][$i]["status_id"], array(1, 2))) {
				$this->registraComentarioVisita($dados_contrato["visitas"][$i]["id"], "<b>Ação</b>: Contrato Ativo ({$dados_contrato["codigo"]})", 18, $_SESSION["usuario"]);
			}
		}
		
		// Atualiza Situacao Cliente
		$this->atualizaSituacaoCliente($dados_contrato["cliente"], 5); // Fechado (Com Contrato)
		
		// Remove Responsável Cliente
		$this->bancodedados->query("UPDATE tab_clientes SET usu_id = NULL WHERE cli_id = '{$dados_contrato["cliente"]}'");
		
	}

	########################################
	### Ativar Contrato Aditivo Migração ###
	########################################

	function ativar_contrato_aditivo_migracao($dados_contrato, $data_ativacao, $contrato_status) {
		
		//TODO Não precisa ativar com pendências
		
		// Atualiza Contrato/Visitas
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
			SET
				tab_visitas.via_id = 3,
				tab_visitas.vis_data = IF(vis_data IS NOT NULL, vis_data, '$data_ativacao'),
				tab_contratos.crs_id = $contrato_status,
				tab_contratos.cor_data_ativacao = '$data_ativacao',
				tab_contratos.cor_termino_fidelidade = IF(tab_contratos.cor_multa > 0 AND tab_contratos.cor_vigencia > 0, DATE_SUB(DATE_ADD('$data_ativacao', INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), tab_contratos.cor_termino_fidelidade),
				tab_contratos.cor_data_confirmacao = IF(tab_contratos.cor_data_confirmacao IS NULL, CURDATE(), tab_contratos.cor_data_confirmacao)
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
		
		// Busca Assinaturas Adicionadas/Removidas
		$assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($dados_contrato["id"]);
		$assinaturas_removidas = $this->buscaAssinaturasRemovidas($dados_contrato["id"]);
		
		// Assinaturas Adicionadas
		foreach ($assinaturas_adicionadas as $assinatura) {
			
			// Migra Pontos
			$this->bancodedados->query("
				UPDATE
					tab_clientes_pontos
					LEFT OUTER JOIN tab_servidores_servicos ON (tab_clientes_pontos.sei_id = tab_servidores_servicos.sei_id)
				SET
					tab_clientes_pontos.ass_id = {$assinatura["ass_id"]},
					tab_clientes_pontos.clp_velocidade_download = {$assinatura["parametros"]["pontos_velocidade_download"]},
					tab_clientes_pontos.clp_velocidade_upload = {$assinatura["parametros"]["pontos_velocidade_upload"]},
					tab_clientes_pontos.clp_publicado = 0,
					tab_servidores_servicos.ses_data_alteracao = NOW()
				WHERE
					tab_clientes_pontos.ass_id = {$assinatura["ass_migrada"]} AND
					tab_clientes_pontos.cps_id IN (1, 2)");
			
			// Migra Autenticações PPPoe
			$this->bancodedados->query("
				UPDATE
					tab_clientes_autenticacoes
				SET
					ass_id = {$assinatura["ass_id"]},
					cau_velocidade_download = {$assinatura["parametros"]["pontos_velocidade_download"]},
					cau_velocidade_upload = {$assinatura["parametros"]["pontos_velocidade_upload"]},
					cau_publicado = 0
				WHERE
					ass_id = {$assinatura["ass_migrada"]} AND
					cas_id IN (1, 2)");
			
			// Migra E-mails
			$this->bancodedados->query("
				UPDATE
					tab_clientes_emails
				SET
					ass_id = {$assinatura["ass_id"]}
				WHERE
					ass_id = {$assinatura["ass_migrada"]} AND
					ces_id IN (1, 2)");
			
			// Migra Domínios
			$this->bancodedados->query("
				UPDATE
					tab_dominios
				SET
					ass_id = {$assinatura["ass_id"]}
				WHERE
					ass_id = {$assinatura["ass_migrada"]}");
			
			// Busca Ajustes Programados Assinatura Migrada
			$ajustes_programados = $this->buscaServicoAjustesAssinatura($assinatura["ass_migrada"], true);
			foreach ($ajustes_programados as $ajuste_programado) {
				if ($ajuste_programado["assinatura_id"] && in_array($ajuste_programado["status_id"], array(1, 2, 5))) {

					// Finaliza Ajustes Assinatura
					$this->bancodedados->query("
						UPDATE
							tab_servicos_ajustes
						SET
							sas_id = IF(sas_id <= 2, 4, 8),
							saj_termino = NOW()
						WHERE
							saj_id = {$ajuste_programado["id"]}");

					// Finaliza Ajuste Pontos
					$this->bancodedados->query("
						UPDATE
							tab_clientes_pontos_ajustes
						SET
							cpa_termino = NOW()
						WHERE
							saj_id = {$ajuste_programado["id"]} AND
							cpa_termino IS NOT NULL");

					// Finaliza Ajuste Autenticacoes
					$this->bancodedados->query("
						UPDATE
							tab_clientes_autenticacoes_ajustes
						SET
							caa_termino = NOW()
						WHERE
							saj_id = {$ajuste_programado["id"]} AND
							caa_termino IS NOT NULL");

					// Finaliza Ajustes Emails
					$this->bancodedados->query("
						UPDATE
							tab_clientes_emails_ajustes
						SET
							cea_termino = NOW()
						WHERE
							saj_id = {$ajuste_programado["id"]} AND
							cea_termino IS NOT NULL");

					// Registra Comentário
					$this->registraComentarioServicoAjuste($ajuste_programado["id"], "<b>Ação:</b> Finalizado (Assinatura Migrada)");

				} elseif (in_array($ajuste_programado["status_id"], array(1, 2, 5))) {

					// Migra Ajustes Não Assinatura
					$this->bancodedados->query("
						UPDATE
							rel_saj_ass_contem
						SET
							ass_id = {$assinatura["ass_id"]}
						WHERE
							saj_id = {$ajuste_programado["id"]} AND
							ass_id = {$assinatura["ass_migrada"]}");

					// Registra Comentário
					$this->registraComentarioServicoAjuste($ajuste_programado["id"], "<b>Ação:</b> Assinatura Migrada");

				}
			}
			
			// Ativa Ajustes Assinatura Nova
			$this->bancodedados->query("
				UPDATE
					tab_servicos_ajustes
				SET
					sas_id = 5,
					saj_inicio = NOW()
				WHERE
					ass_id = {$assinatura["ass_id"]}");
			
			// Registra Comentário Pontos / Insere Link Ajuste
			$pontos = $this->buscaClientePontosAssinatura($assinatura["ass_id"]);
			foreach ($pontos as $ponto_id) {
				$this->registraComentarioClientePonto($ponto_id, "<b>Ação:</b> Migração de Plano: <b>{$assinatura["plano_nome_migrada"]}</b> para <b>{$assinatura["plano_nome"]}</b>", $_SESSION["usuario"]);
				// Insere Link Ajuste Assinatura Novo
				if (($ajuste_programado = $this->buscaAjusteAssinatura($assinatura["ass_id"])) != false) {
					$this->bancodedados->query("
						INSERT INTO
							rel_saj_clp_contem
						SET
							saj_id = {$ajuste_programado["id"]},
							clp_id = $ponto_id");
				}
			}
			
			// Registra Comentário Autenticacoes / Insere Link Ajuste
			$autenticacoes = $this->buscaClienteAutenticacoesAssinatura($assinatura["ass_id"]);
			foreach ($autenticacoes as $autenticacao_id) {
				$this->registraComentarioClienteAutenticacao($autenticacao_id, "<b>Ação:</b> Migração de Plano: <b>{$assinatura["plano_nome_migrada"]}</b> para <b>{$assinatura["plano_nome"]}</b>", $_SESSION["usuario"]);
				// Insere Link Ajuste Assinatura Novo
				if (($ajuste_programado = $this->buscaAjusteAssinatura($assinatura["ass_id"])) != false) {
					$this->bancodedados->query("
						INSERT INTO
							rel_saj_cau_contem
						SET
							saj_id = {$ajuste_programado["id"]},
							cau_id = $autenticacao_id");
				}
			}
			
			// Insere Link Assinaturas Adicionadas
			$this->bancodedados->query("
				INSERT INTO
					rel_ass_cor_estacontido
				SET
					ass_id = {$assinatura["ass_id"]},
					cor_id = {$assinatura["cor_id"]}");
			
			// Ativa Assinaturas Adicionadas
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
				SET
					tab_assinaturas.ast_id = 2,
					tab_assinaturas.ass_data_ativacao = '$data_ativacao',
					tab_assinaturas.ass_data_confirmacao = IF(tab_assinaturas.ass_data_confirmacao IS NULL, CURDATE(), tab_assinaturas.ass_data_confirmacao)
				WHERE
					tab_assinaturas.ass_id = {$assinatura["ass_id"]}");
			
			// Migra Ajustes Financeiros
			$this->bancodedados->query("
				UPDATE
					tab_ajustes_financeiros
				SET
					ass_id = {$assinatura["ass_id"]}
				WHERE
					ass_id = {$assinatura["ass_migrada"]}");
			
			// Migra Comodatos
			$this->bancodedados->query("
				UPDATE
					tab_clientes_comodatos
				SET
					ass_id = {$assinatura["ass_id"]}
				WHERE
					ass_id = {$assinatura["ass_migrada"]} AND
					ccs_id < 5");
			
		}
		
		// Assinaturas Removidas
		$data_migracao = $data_ativacao;
		$data_migracao = explode("-", $data_migracao);
		$data_migracao = date("Y-m-d", strtotime("{$data_migracao[0]}-{$data_migracao[1]}-{$data_migracao[2]} -1 day"));
		
		foreach ($assinaturas_removidas as $assinatura) {
			
			// Remove Link Assinaturas Removidas
			$this->bancodedados->query("
				DELETE FROM
					rel_ass_cor_estacontido
				WHERE
					ass_id = {$assinatura["ass_id"]} AND
					cor_id = {$assinatura["cor_id"]}");
			
			// Desativa Assinaturas Removidas
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
				SET
					tab_assinaturas.ast_id = 4,
					tab_assinaturas.ass_data_cancelamento = '$data_migracao'
				WHERE
					tab_assinaturas.ass_id = {$assinatura["ass_id"]}");
			
			// Gera Estorno se Cobrança gerada p/ período
			if ($assinatura["sistema"] == 1) {
				// Pós-Pago
				if ($this->possuiCobrancaPeriodo($assinatura["ass_id"], $data_ativacao)) {
					// Ajuste Proporcional
					$dias = intval(date("t", strtotime($data_migracao)), 10) - intval(date("d", strtotime($data_migracao)), 10);
					$dias = $dias > 0 ? $dias : intval(date("t", strtotime($data_ativacao)), 10);
					$this->geraAjusteEstorno($dados_contrato["cliente"], $assinatura["ass_id"], $dias, date("Y-m-01", strtotime($data_ativacao)));
				}
			} else {
				// Pré-Pago
				if ($this->possuiCobrancaPeriodo($assinatura["ass_id"], $data_ativacao)) {
					// Ajuste Proporcional
					$dias = intval(date("t", strtotime($data_migracao)), 10) - intval(date("d", strtotime($data_migracao)), 10);
					$dias = $dias > 0 ? $dias : intval(date("t", strtotime($data_ativacao)), 10);
					$this->geraAjusteEstorno($dados_contrato["cliente"], $assinatura["ass_id"], $dias, date("Y-m-01", strtotime($data_ativacao)));
				}
				if ($this->possuiCobrancaPeriodo($assinatura["ass_id"], date("Y-m-d", strtotime("$data_ativacao next month")))) {
					// Ajuste 100%
					$this->geraAjusteEstorno($dados_contrato["cliente"], $assinatura["ass_id"], date("t", strtotime("$data_ativacao next month")), date("Y-m-01", strtotime("$data_ativacao next month")));
				}
			}
			
		}
		
		// Descontos Adicionados
		$descontos_adicionados = $this->buscaDescontosAdicionados($dados_contrato["id"]);
		foreach ($descontos_adicionados as $desconto) {

			// Insere Link Descontos Adicionados
			$this->bancodedados->query("
				INSERT INTO
					rel_dec_cor_estacontido
				SET
					dec_id = {$desconto["dec_id"]},
					cor_id = {$desconto["cor_id"]}");

			// Ativa Descontos Adicionados
			$this->bancodedados->query("
				UPDATE
					tab_descontos_concessoes
				SET
					dcs_id = IF(dcs_id IN (2, 5), 5, 4),
					dec_data_termino = IF(dec_data_termino IS NULL, IF(dec_validade IS NOT NULL, DATE_SUB(DATE_ADD('$data_ativacao', INTERVAL dec_validade MONTH), INTERVAL 1 DAY), NULL), dec_data_termino),
					dec_data_inicio = '$data_ativacao'
				WHERE
					dec_id = {$desconto["dec_id"]}");

		}

		// Descontos Removidos
		$descontos_removidos = $this->buscaDescontosRemovidos($dados_contrato["id"]);
		foreach ($descontos_removidos as $desconto) {

			// Remove Link Descontos Removidos
			$this->bancodedados->query("
				DELETE FROM
					rel_dec_cor_estacontido
				WHERE
					dec_id = {$desconto["dec_id"]} AND
					cor_id = {$desconto["cor_id"]}");

			// Desativa Descontos Removidos
			$this->bancodedados->query("
				UPDATE
					tab_descontos_concessoes
				SET
					dcs_id = 6
				WHERE
					dec_id = {$desconto["dec_id"]}");

			// Registra Comentário Desconto
			$this->registraComentarioDesconto($desconto["dec_id"], "<b>Ação:</b> Assinatura Migrada", $_SESSION["usuario"]);

		}
		
		// Busca Assinatura Pai
		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id
			FROM
				rel_ass_cor_estacontido
				INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = {$dados_contrato["id"]} AND
				tab_planos.plt_id = 1");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			// Atualiza Assinatura Pai
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
					INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				SET
					tab_assinaturas.ass_pai = {$dados["id"]}
				WHERE
					rel_ass_cor_estacontido.cor_id = {$dados_contrato["id"]} AND
					tab_assinaturas.ass_id <> {$dados["id"]}");
		}
		
		// Registra comentário no contrato
		$this->registraComentario($dados_contrato["id"], ($contrato_status == 19) ? 18 : 19, $_POST["comentario"], $_SESSION["usuario"]);
		
		// Registra comentário nos descontos
		for ($i = 0; $i < sizeof($dados_contrato["descontos"]); $i++) {
			$this->registraComentarioDesconto($dados_contrato["descontos"][$i]["id"], "<b>Ação:</b> Contrato Ativo (".$dados_contrato["codigo"].")", $_SESSION["usuario"]);
		}
		
		// Registra comentário nas visitas
		for ($i = 0; $i < sizeof($dados_contrato["visitas"]); $i++) {
			if (in_array($dados_contrato["visitas"][$i]["status_id"], array(1, 2))) {
				$this->registraComentarioVisita($dados_contrato["visitas"][$i]["id"], "<b>Ação</b>: Contrato Ativo (".$dados_contrato["codigo"].")", 18, $_SESSION["usuario"]);
			}
		}
		
		// Ativa Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			if ($vinculo["sincronizado"] && $vinculo["status_id"] == 17) {
				$this->ativar_contrato_principal($this->buscaDadosContrato($vinculo["contrato_id"]), $data_ativacao, $contrato_status);
			}
		}
		
		// Migra vinculo p/ contrato pai
		$this->bancodedados->query("
			UPDATE
				tab_contratos_vinculos
			SET
				cor_id = {$dados_contrato["pai"]}
			WHERE
				cor_id = {$dados_contrato["id"]}");
		
		// Finaliza Ajuste Bonificação
		$this->bancodedados->query("
			UPDATE
				tab_ajustes_financeiros
			SET
				tab_ajustes_financeiros.afs_id = 6
			WHERE
				cli_id = {$dados_contrato["cliente"]} AND
				ajf_competencia_termino IS NULL AND
				afc_id = 14 AND
				afs_id = 5");
		
		// Atualiza Situacao Cliente
		$this->atualizaSituacaoCliente($dados_contrato["cliente"], 5); // Fechado (Com Contrato)
		
		// Remove Responsável Cliente
		$this->bancodedados->query("
			UPDATE
				tab_clientes
			SET
				usu_id = NULL
			WHERE
				cli_id = '{$dados_contrato["cliente"]}'");

		// Abre chamado financeiro
		$this->abreChamadoFinanceiro($dados_contrato);
		
	}
	
	###################################################
	### Ativar Contrato Aditivo Mudança de Endereço ###
	###################################################
	
	function ativar_contrato_aditivo_me($dados_contrato, $data_ativacao, $contrato_status, $comentario = "") {
		
		//TODO Abrir chamado para Administrativo se ativo com pendências e houve cobrança instalação
		
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
			SET
				tab_contratos.crs_id = $contrato_status,
				tab_contratos.cor_data_ativacao = '{$data_ativacao}',
				tab_contratos.cor_termino_fidelidade = IF(tab_contratos.cor_multa > 0 AND tab_contratos.cor_vigencia > 0, DATE_SUB(DATE_ADD('{$data_ativacao}', INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), tab_contratos.cor_termino_fidelidade),
				tab_contratos.cor_data_confirmacao = IF(tab_contratos.cor_data_confirmacao IS NULL, CURDATE(), tab_contratos.cor_data_confirmacao),
				tab_visitas.vis_data = IF(vis_data IS NOT NULL, vis_data, '{$data_ativacao}'),
				tab_visitas.via_id = 3
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
		
		foreach ($dados_contrato["visitas"] as $visita) {
			if (in_array($visita["status_id"], array(1, 2))) {
				$this->registraComentarioVisita($visita["id"], "<b>Ação</b>: Contrato Ativo ({$dados_contrato["codigo"]})", 18, $_SESSION["usuario"]);
			}
		}
		
		$this->registraComentario($dados_contrato["id"], $contrato_status == 19 ? 18 : 19, $comentario, $_SESSION["usuario"]);
		
		$this->finalizaBloqueioContratoInativo($dados_contrato["pai"]);
		
	}
	
	#######################################################
	### Ativar Contrato Aditivo Mudança de Titularidade ###
	#######################################################
	
	function ativar_contrato_aditivo_mt($dados_contrato, $data_ativacao, $contrato_status, $comentario = "") {
		
		//TODO Não precisa ativar com pendências
		
		$dados_cliente = $this->buscaDadosCadastraisCliente($dados_contrato["cliente"]);
		$contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
		
		$dados_contrato["parametros"]["cliente_nascimento"] = $this->corrigeNascimento($dados_contrato["parametros"]["cliente_nascimento"]);
		
		$this->bancodedados->query("
			UPDATE
				tab_clientes
			SET
				cli_nome = '{$dados_contrato["parametros"]["cliente_nome"]}',
				cli_cpf_cnpj = '{$dados_contrato["parametros"]["cliente_cpf_cnpj"]}',
				cli_nascimento = {$dados_contrato["parametros"]["cliente_nascimento"]},
				clt_id = {$dados_contrato["parametros"]["cliente_tipo"]}
			WHERE
				cli_id = {$dados_cliente["id"]}");
		
		$contatos->atualizaContatoTelefones($dados_cliente["id"], $dados_contrato["parametros"]["cliente_telefones"]);
		$contatos->atualizaContatoEmails($dados_cliente["id"], $dados_contrato["parametros"]["cliente_emails"]);
		
		$this->bancodedados->query("DELETE FROM tab_clientes_cobranca_dados WHERE cli_id = {$dados_cliente["id"]}");
		
		if ($dados_contrato["parametros"]["final"]["cobranca_complemento_valor"] && ($dados_contrato["parametros"]["final"]["cobranca_cep"] != $dados_cliente["cep"] || $dados_contrato["parametros"]["final"]["cobranca_bloco_id"] != $dados_cliente["bloco_id"] || $dados_contrato["parametros"]["final"]["cobranca_complemento_tipo"] != $dados_cliente["complemento_tipo_id"] || $dados_contrato["parametros"]["final"]["cobranca_complemento_valor"] != $dados_cliente["complemento_valor"])) {
			if ($dados_contrato["parametros"]["final"]["cobranca_complemento_tipo"] != "o") $dados_contrato["parametros"]["final"]["cobranca_complemento_valor"] = intval($dados_contrato["parametros"]["final"]["cobranca_complemento_valor"]);
			$complemento = "'{$dados_contrato["parametros"]["final"]["cobranca_complemento_tipo"]}{$dados_contrato["parametros"]["final"]["cobranca_complemento_valor"]}'";
			$existe_dados_cobranca = true;
		} else {
			$complemento = "NULL";
		}
		if ($dados_contrato["parametros"]["final"]["cobranca_bloco_id"] && ($dados_contrato["parametros"]["final"]["cobranca_bloco_id"] != $dados_cliente["bloco_id"] || $dados_contrato["parametros"]["final"]["cobranca_complemento_tipo"] != $dados_cliente["complemento_tipo_id"] || $dados_contrato["parametros"]["final"]["cobranca_complemento_valor"] != $dados_cliente["complemento_valor"])) {
			$dados_contrato["parametros"]["final"]["cobranca_bloco_id"] = "'{$dados_contrato["parametros"]["final"]["cobranca_bloco_id"]}'";
			$existe_dados_cobranca = true;
		} else {
			$dados_contrato["parametros"]["final"]["cobranca_bloco_id"] = "NULL";
		}
		if ($dados_contrato["parametros"]["final"]["cobranca_cep"] && $dados_contrato["parametros"]["final"]["cobranca_cep"] != $dados_cliente["cep"]) {
			$dados_contrato["parametros"]["final"]["cobranca_cep"] = "'".preg_replace("/[^0-9]/", "", $dados_contrato["parametros"]["final"]["cobranca_cep"])."'";
			$dados_contrato["parametros"]["final"]["cobranca_numero"] = $dados_contrato["parametros"]["final"]["cobranca_numero"] ? "'{$dados_contrato["parametros"]["final"]["cobranca_numero"]}'" : "NULL";
			$existe_dados_cobranca = true;
		} else {
			$dados_contrato["parametros"]["final"]["cobranca_cep"] = "NULL";
			$dados_contrato["parametros"]["final"]["cobranca_numero"] = "NULL";
		}
		
		if ($existe_dados_cobranca) {
			$this->bancodedados->query("
				INSERT INTO
					tab_clientes_cobranca_dados
				SET
					cli_id = '{$dados_cliente["id"]}',
					cob_id = {$dados_contrato["parametros"]["final"]["cobranca_bloco_id"]},
					cep_id = {$dados_contrato["parametros"]["final"]["cobranca_cep"]},
					ccd_numero = {$dados_contrato["parametros"]["final"]["cobranca_numero"]},
					ccd_complemento = $complemento");
		}
		
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				LEFT OUTER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
			SET
				tab_contratos.crs_id = $contrato_status,
				tab_contratos.cor_data_ativacao = '{$data_ativacao}',
				tab_contratos.cor_termino_fidelidade = IF(tab_contratos.cor_multa > 0 AND tab_contratos.cor_vigencia > 0, DATE_SUB(DATE_ADD('{$data_ativacao}', INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), tab_contratos.cor_termino_fidelidade),
				tab_contratos.cor_data_confirmacao = IF(tab_contratos.cor_data_confirmacao IS NULL, CURDATE(), tab_contratos.cor_data_confirmacao),
				tab_visitas.vis_data = IF(vis_data IS NOT NULL, vis_data, '{$data_ativacao}'),
				tab_visitas.via_id = 3
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
		
		$this->registraComentario($dados_contrato["id"], $contrato_status == 19 ? 18 : 19, $comentario, $_SESSION["usuario"]);
		
	}
	
	##############
	### Editar ###
	##############

	function editar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if (!$this->ehEditavel()) {
			
			header("Location: ./?m=contratos&a=ver&i={$_GET["i"]}");
			
		} elseif ($this->dados_item["tipo_id"] == 14) { // Mudança de Endereço
		
			$this->editar_aditivo_me();
			
		} elseif ($this->dados_item["tipo_id"] == 15) { // Mudança de Titularidade
		
			$this->editar_aditivo_mt();
			
		} elseif ($this->dados_item) {
			
			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
			
			if (!$_POST) {

				$pontuacao = array();
				foreach ($this->dados_item["assinaturas"] as $assinatura) {
					$pontuacao[] = $assinatura["parametros"]["pontos_velocidade_download"];
				}

				$botoes = array();
				if ($this->ehEnviavelCobranca()) $botoes[]	= '<input type="button" name="enviar_cobranca" class="buttonAzulEscuroBranco11" value="Enviar Cobrança" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_cobranca&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAdicionavel()) $botoes[]	= '<input type="button" name="adicionar" class="buttonAzulEscuroBranco11" value="Adicionar Aditivo" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=adicionar&cor='.$this->dados_item["id"].'&cli='.$dados_cliente["id"].'\';">';
				if ($this->ehEnviavelContrato()) $botoes[] = '<input type="button" name="enviar_contrato" class="buttonAzulEscuroBranco11" value="Enviar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_contrato&i='.$this->dados_item["id"].'\';">';
				if ($this->ehConfirmavel()) $botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=confirmar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAtivavel()) $botoes[]	= '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAtivavelSemPendencia()) $botoes[]	= '<input type="button" name="ativar_sem_pendencias" class="buttonAzulEscuroBranco11" value="Ativar Sem Pendências" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehConsultavelCredito()) $botoes[] = '<input type="button" name="consultar_credito" class="buttonAzulEscuroBranco11" style="width:120px" value="Consultar Crédito" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=consultar_credito&i='.$this->dados_item["id"].'\';">';
				if ($this->ehMigravelPrePago()) $botoes[] = '<input type="button" name="migrar_sistema" class="buttonAzulEscuroBranco11" style="width:160px" value="Migrar Sistema Pré-Pago" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=migrar_sistema&i='.$this->dados_item["id"].'\'">';
				if ($this->ehAprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_aprovar" class="buttonAzulEscuroBranco11" style="width:140px" value="Aprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_aprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehGeravelCobrancaPrePago()) $botoes[] = '<input type="button" name="gerar_cobranca_pre_pago" class="buttonAzulEscuroBranco11" style="width:140px" value="Gerar Cobrança" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=gerar_cobranca_pre_pago&i='.$this->dados_item["id"].'\';">';
				if ($this->ehReprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_reprovar" class="buttonAzulEscuroBranco11" style="width:150px" value="Reprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_reprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAprovavelComercial()) $botoes[] = '<input type="button" name="comercial_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_aprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehReprovavelComercial()) $botoes[] = '<input type="button" name="comercial_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_reprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_aprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehReprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_reprovar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehVisualizavelContrato()) $botoes[] = '<input type="button" name="visualizar_contrato" class="buttonAzulEscuroBranco11" style="width:130px" value="Visualizar (Contrato)" onClick="visualizarContrato('.$this->dados_item["id"].', \'contrato\', 0);">';
				if ($this->ehVisualizavelVistoria()) $botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" style="width:120px" value="Visualizar (Vistoria)" onClick="visualizarContrato('.$this->dados_item["id"].', \'vistoria\', 0);">';
				if ($this->ehBaixavelContrato()) $botoes[] = '<input type="button" name="baixar_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Baixar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=baixar_contrato&i='.$this->dados_item["id"].'\';">';
				if ($this->ehRemovivelContrato()) $botoes[] = '<input type="button" name="remover_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Remover Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_contrato&i='.$this->dados_item["id"].'\';">';
				if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
				if ($this->ehCancelavel()) $botoes[] = '<input type="button" name="cancelar" class="buttonAzulEscuroBranco11" value="Cancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=cancelar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehDescancelavel()) $botoes[] = '<input type="button" name="descancelar" class="buttonAzulEscuroBranco11" value="Descancelar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=descancelar&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAnulavelContrato()) $botoes[] = '<input type="button" name="anular" class="buttonAzulEscuroBranco11" value="Anular" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAnulavelCancelamento()) $botoes[] = '<input type="button" name="anular_cancelamento" class="buttonAzulEscuroBranco11" value="Anular Cancelamento" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_cancelamento&i='.$this->dados_item["id"].'\';">';
				if ($this->ehAnulavelAtivacao()) $botoes[] = '<input type="button" name="anular_ativacao" class="buttonAzulEscuroBranco11" value="Anular Ativação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_ativacao&i='.$this->dados_item["id"].'\';">';
				$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

				if ($this->dados_item["tipo_id"] == 11) {
					$this->conteudo .= $this->carregaArquivo("contratos.principal.editar.inc.php");
					$this->conteudo = str_replace("[aditivos]", $this->geraListagemAditivos($this->dados_item["aditivos"]), $this->conteudo);
				} else {
					$this->conteudo .= $this->carregaArquivo("contratos.aditivo.editar.inc.php");
				}
				
				$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Editar [{$this->dados_item["tipo_nome"]}]&nbsp;</b></td>", $this->conteudo);
				$this->conteudo = str_replace("[contrato_id]", $this->dados_item["id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
				$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[telefones]", $dados_cliente["lista_telefones"], $this->conteudo);
				$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[servico_id]", $this->dados_item["servico_id"], $this->conteudo);
				$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
				$this->conteudo = str_replace("[instalacao]", $this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_original_instalacao"])." (".$this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_final_instalacao"]).")", $this->conteudo);
				$this->conteudo = str_replace("[mensalidade]", $this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_original_mensalidade"])." (".$this->formataDinheiro($this->dados_item["parametros"]["tabela_valor_final_mensalidade"]).")", $this->conteudo);
				
				if (in_array($this->dados_item["status_id"], array(11, 12, 13, 14, 15, 16, 17))) {
					if ($this->dados_item["tipo_id"] == 12) {
						$this->conteudo = str_replace("[assinaturas]", $this->geraListagemAssinaturasAditivoAlteracao($dados_cliente["id"], $this->dados_item["id"], $this->dados_item["pai"], $this->dados_item["servico_id"]), $this->conteudo);
					} elseif ($this->dados_item["tipo_id"] == 13) {
						$this->conteudo = str_replace("[assinaturas]", $this->geraListagemAssinaturasAditivoMigracaoEditavel($this->dados_item), $this->conteudo);
					} else {
						$assinaturas = $this->buscaAssinaturasEditavel($dados_cliente["id"], $this->dados_item["id"], $this->dados_item["servico_id"]);
						$this->conteudo = str_replace("[assinaturas]", $this->geraListagemAssinaturasEditavel($assinaturas), $this->conteudo);
					}
				} else {
					$this->conteudo = str_replace("[assinaturas]", $this->geraListagemAssinaturas($this->dados_item["assinaturas"]), $this->conteudo);
				}
				
				if ($this->dados_item["servico_id"] == 28) {
					$parcelamento = $this->buscaParcelamentos($this->dados_item["parametros"]["parcelamento"], $this->dados_item["parametros"]["parcelamento_forma"], $this->dados_item["parametros"]["tabela_valor_final_instalacao"] > 0 ? $this->buscaParcelamentoMaximo($this->dados_item["servico_id"]) : 1, $this->dados_item["parametros"]["tabela_valor_final_instalacao"], $this->dados_item["servico_id"], $this->dados_item["assinaturas"][0]["id"]);
					$this->conteudo = str_replace("[parcelamento]", $this->geraSelect(array($this->dados_item["parametros"]["parcelamento"] => $parcelamento)), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[parcelamento]", $this->geraSelect($this->buscaParcelamentos(null, $this->dados_item["parametros"]["parcelamento_forma"], $this->buscaParcelamentoMaximo($this->dados_item["servico_id"])), $this->dados_item["parametros"]["parcelamento"]), $this->conteudo);
				}
				
				$this->conteudo = str_replace("[pontuacao]", "[".implode(",", $pontuacao)."]", $this->conteudo);
				$this->conteudo = str_replace("[descontos]", $this->geraListagemDescontos($this->dados_item["descontos"]), $this->conteudo);
				$this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
				$this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
				$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
				$this->conteudo = str_replace("[data_anulacao]", $this->dados_item["data_anulacao"], $this->conteudo);
				$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
				$this->conteudo = str_replace("[tipo_id]", $this->dados_item["tipo_id"], $this->conteudo);
				$this->conteudo = str_replace("[instalador]", $this->buscaInstalador($this->dados_item["id"]), $this->conteudo);
				$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
				$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
				
				if ($this->ehEditavelDatas()) {
					$this->conteudo = str_replace("[data_geracao_disabled]", "", $this->conteudo);
					$this->conteudo = str_replace("[data_ativacao_disabled]", (in_array($this->dados_item["status_id"], array(18, 19, 20, 21, 22, 23)) ? "" : "disabled"), $this->conteudo);
					$this->conteudo = str_replace("[data_anulacao_disabled]", (in_array($this->dados_item["status_id"], array(24)) ? "" : "disabled"), $this->conteudo);
					$this->conteudo = str_replace("[data_cancelamento_disabled]", (in_array($this->dados_item["status_id"], array(21, 22, 23)) ? "" : "disabled"), $this->conteudo);
				} else {
					$this->conteudo = str_replace("[data_geracao_disabled]", "disabled", $this->conteudo);
					$this->conteudo = str_replace("[data_ativacao_disabled]", "disabled", $this->conteudo);
					$this->conteudo = str_replace("[data_anulacao_disabled]", "disabled", $this->conteudo);
					$this->conteudo = str_replace("[data_cancelamento_disabled]", "disabled", $this->conteudo);
				}
				
				$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
				$this->conteudo = str_replace("[vigencias]", $this->geraSelect($this->buscaListaVigencia(36), $this->dados_item["vigencia"]), $this->conteudo);
				$this->conteudo = str_replace("[valor_multa]", $this->formataDinheiro($this->dados_item["multa"]), $this->conteudo);
				$this->conteudo = str_replace("[vigencia_disabled]", $this->ehEditavelVigencia() ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace("[valor_multa_disabled]", $this->dados_item["vigencia"] && $this->ehEditavelVigencia() ? "" : "disabled", $this->conteudo);
				$this->conteudo = str_replace("[exibir_mensagem_vigencia]", $this->dados_item["vigencia_ativa"] && in_array($this->dados_item["status_id"], array(19, 20, 21)) ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=editar&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
			    $comentario = array();
			    
				if (trim($_POST["comentario"])) {
					$comentario[] = trim($_POST["comentario"]);
				}
				if ($_POST["data_geracao"] && $_POST["data_geracao"] != $this->dados_item["data_geracao"]) {
					$comentario[] = "<b>Alteraçao Data de Geração</b>: <b>{$this->dados_item["data_geracao"]}</b> para <b>{$_POST["data_geracao"]}</b>";
				}
				if ($_POST["data_ativacao"] && $_POST["data_ativacao"] != $this->dados_item["data_ativacao"]) {
					$comentario[] = "<b>Alteraçao Data de Ativação</b>: <b>{$this->dados_item["data_ativacao"]}</b> para <b>{$_POST["data_ativacao"]}</b>";
				}
				if ($_POST["data_cancelamento"] && $_POST["data_cancelamento"] != $this->dados_item["data_cancelamento"]) {
					$comentario[] = "<b>Alteraçao Data de Cancelamento</b>: <b>{$this->dados_item["data_cancelamento"]}</b> para <b>{$_POST["data_cancelamento"]}</b>";
				}

				$comentario = implode("<br>", $comentario);

				if ($comentario) {
					$this->registraComentario($this->dados_item["id"], 21, $comentario, $_SESSION["usuario"]);
				}

				if ($_POST["vigencia"] && $_POST["vigencia"] <> $this->dados_item["parametros"]["contrato_prazo_vigencia"]) {
					$this->registraComentario($this->dados_item["id"], 22, "Alteração Vigência do Contrato: ".$this->dados_item["parametros"]["contrato_prazo_vigencia"]." para ".$_POST["vigencia"]." mês(es)", $_SESSION["usuario"]);
				}
				
				if ($this->dados_item["data_geracao"] && !$_POST["data_geracao"]) $_POST["data_geracao"] = $this->dados_item["data_geracao"];
				if ($this->dados_item["data_ativacao"] && !$_POST["data_ativacao"]) $_POST["data_ativacao"] = $this->dados_item["data_ativacao"];
				if ($this->dados_item["data_anulacao"] && !$_POST["data_anulacao"]) $_POST["data_anulacao"] = $this->dados_item["data_anulacao"];
				if ($this->dados_item["data_cancelamento"] && !$_POST["data_cancelamento"]) $_POST["data_cancelamento"] = $this->dados_item["data_cancelamento"];

				$_POST["data_geracao"] = $_POST["data_geracao"] ? "'".substr($_POST["data_geracao"],6,4)."-".substr($_POST["data_geracao"],3,2)."-".substr($_POST["data_geracao"],0,2)."'" : "NULL";
				$_POST["data_ativacao"] = $_POST["data_ativacao"] ? "'".substr($_POST["data_ativacao"],6,4)."-".substr($_POST["data_ativacao"],3,2)."-".substr($_POST["data_ativacao"],0,2)."'" : "NULL";
				$_POST["data_anulacao"] = $_POST["data_anulacao"] ? "'".substr($_POST["data_anulacao"],6,4)."-".substr($_POST["data_anulacao"],3,2)."-".substr($_POST["data_anulacao"],0,2)."'" : "NULL";
				$_POST["data_cancelamento"] = $_POST["data_cancelamento"] ? "'".substr($_POST["data_cancelamento"],6,4)."-".substr($_POST["data_cancelamento"],3,2)."-".substr($_POST["data_cancelamento"],0,2)."'" : "NULL";
				
				$_POST["assinaturas"] = explode(",", $_POST["assinaturas"]);
				
				$this->dados_item["pai"] = $this->buscaDadosContrato($this->dados_item["pai"]);

				if (in_array($this->dados_item["status_id"], array(11, 12, 13, 14, 15, 16, 17))) {
				    
				    $assinaturas_migradas = array();
				    $assinaturas_removidas = array();
				    $contrato_assinaturas = array();
				    $contrato_descontos = array();

					// Desassocia Assinaturas / Descontos
					$this->bancodedados->query("DELETE FROM rel_ass_cor_estacontido WHERE cor_id = {$_GET["i"]}");
					$this->bancodedados->query("DELETE FROM rel_dec_cor_estacontido WHERE cor_id = {$_GET["i"]}");

					if ($this->dados_item["tipo_id"] == 13) { // Migraçao de Assinaturas

						// Trata Novos Planos
						for ($i = 0; $i < sizeof($_POST["novo_plano"]); $i++) {
							$novo_plano = explode(",", $_POST["novo_plano"][$i]);
							$_POST["novo_plano"][$i] = $novo_plano[0];
						}

						// Remove Atuais
						$query = $this->bancodedados->query("
							SELECT 
								ass_id 
							FROM 
								tab_assinaturas 
							WHERE 
								cli_id = {$this->dados_item["cliente"]} AND 
								ass_migrada IS NOT NULL AND 
								ast_id = 1 
							ORDER BY 
								ass_pai DESC");

						while (($assinatura = $this->bancodedados->resultadoArray($query)) != false) {
							// Remove venda
							$this->removeVenda($assinatura["ass_id"]);
							// Remove Ajuste Programado
							$this->removeAjusteProgramado($assinatura["ass_id"]);
							if (!in_array($assinatura["ass_id"], $_POST["assinaturas"])) {
								// Deleta Descontos Assinaturas Migradas
								$this->bancodedados->query("DELETE FROM tab_descontos_concessoes WHERE ass_id = {$assinatura["ass_id"]}");
								// Deleta Assinaturas Migradas
								$this->bancodedados->query("DELETE FROM tab_assinaturas WHERE ass_id = {$assinatura["ass_id"]}");
							}
						}

						// Regera Assinaturas
						foreach ($this->dados_item["pai"]["assinaturas"] as $assinatura) {
							if ($_POST["assinaturas"][$assinatura["id"]] && $_POST["novo_plano"][$assinatura["id"]]) {

								$query = $this->bancodedados->query("
									SELECT 
										ass_id 
									FROM 
										tab_assinaturas
									WHERE 
										ass_migrada = {$assinatura["id"]} AND 
										ast_id = 1");
								
								if (($assinatura_mantida_id = $this->bancodedados->resultado($query)) != false) {
									$assinaturas_migradas[] = $this->atualizaAssinatura($assinatura_mantida_id, $dados_cliente["id"], $_POST["novo_plano"][$assinatura_mantida_id]);
								} else {
									$assinaturas_migradas[] = $this->geraAssinatura($assinatura["id"], $dados_cliente["id"], $_POST["novo_plano"][$assinatura["id"]], $assinatura["sistema_id"], $_POST["migrar_descontos"][$assinatura["id"]]);
								}

							} else {
								$assinaturas_migradas[] = $assinatura["id"];
							}
						}

						$_POST["assinaturas"] = $assinaturas_migradas;

					}

					// Busca Dados Assinaturas / Descontos
					for ($i = 0; $i < sizeof($_POST["assinaturas"]); $i++) {
						$contrato_assinaturas[] = $this->buscaDadosAssinatura($_POST["assinaturas"][$i]);
						for ($j = 0; $j < sizeof($contrato_assinaturas[$i]["descontos"]); $j++) {
							$contrato_descontos[] = $contrato_assinaturas[$i]["descontos"][$j];
						}
					}

					// Associa Assinaturas / Gera Venda
					foreach ($this->dados_item["assinaturas"] as $assinatura) {
						$assinaturas_removidas[$assinatura["id"]] = $assinatura["id"];
					}
					for ($i = 0; $i < sizeof($contrato_assinaturas); $i++) {
						unset($assinaturas_removidas[$contrato_assinaturas[$i]["id"]]);
						$this->bancodedados->query("
							INSERT INTO 
								rel_ass_cor_estacontido 
							SET
								ass_id = {$contrato_assinaturas[$i]["id"]}, 
								cor_id = {$_GET["i"]}");
					}

					// Remove vendas
					foreach ($assinaturas_removidas as $assinatura_removida) {
						$this->removeVenda($assinatura_removida);
					}

					// Principal / Aditivo Alteração -> Remoção de Pontos
					if (in_array($this->dados_item["tipo_id"], array(11, 12))) {
						foreach ($this->dados_item["assinaturas"] as $assinatura) {
							if ($assinaturas_removidas[$assinatura["id"]]) {

								// Define Data Fechamento Assinatura igual a NULL
								$this->bancodedados->query("
									UPDATE 
										tab_assinaturas 
									SET 
										ass_data_fechamento = NULL, 
										ass_data_confirmacao = NULL 
									WHERE 
										ass_id = {$assinatura["id"]}");

								// Remove Assinatura Ajustes
								foreach ($assinatura["servicos_ajustes"] as $ajuste) {
									// Remove Links Ajuste/Assinatura/Base de Dados/Email/Hospedagem/Ponto
									$this->bancodedados->query("DELETE FROM rel_saj_ass_contem WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM rel_saj_cle_contem WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM rel_saj_clp_contem WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM rel_saj_cau_contem WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM rel_saj_cip_contem WHERE saj_id = {$ajuste["id"]}");
									// Remove Comentários e Regras Ajuste
									$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_comentarios WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = {$ajuste["id"]}");
									// Remove Regras Ajuste
									$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = {$ajuste["id"]}");
									// Remove Ajustes Pontos e E-Mails
									$this->bancodedados->query("DELETE FROM tab_clientes_pontos_ajustes WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM tab_clientes_emails_ajustes WHERE saj_id = {$ajuste["id"]}");
									$this->bancodedados->query("DELETE FROM tab_clientes_publicacoes_ajustes WHERE saj_id = {$ajuste["id"]}");
									// Remove Ajuste
									$this->bancodedados->query("DELETE FROM tab_servicos_ajustes WHERE saj_id = {$ajuste["id"]}");
								}

								// Remove Assinatura Pontos
								if ($assinatura["cliente_pontos"]) {
									foreach ($assinatura["cliente_pontos"] as $ponto) {
										// Remove Comentários Pontos
										$this->bancodedados->query("DELETE FROM tab_clientes_pontos_comentarios WHERE clp_id = {$ponto["id"]}");
										// Remove Pontos
										$this->bancodedados->query("DELETE FROM tab_clientes_pontos WHERE clp_id = {$ponto["id"]}");
									}
								}

								// Remove Assinatura Emails
								$this->removeEmails($assinatura["id"]);

							}
						}
					}

					// Busca Assinaturas Adicionadas
					$assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($_GET["i"]);

					// Gera Vendas
					$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);

					if ($this->dados_item["tipo_id"] != 14) {
						foreach ($assinaturas_adicionadas as $assinatura_adicionada) {
							$this->geraVenda($dados_cliente, $this->buscaDadosAssinatura($assinatura_adicionada["ass_id"]), $_GET["i"]);
						}
					}

					// Associa Descontos
					for ($i = 0; $i < sizeof($contrato_descontos); $i++) {
						$this->bancodedados->query("INSERT INTO rel_dec_cor_estacontido (dec_id, cor_id) VALUES ({$contrato_descontos[$i]["id"]}, {$_GET["i"]})");
					}

					// Parametros Contrato
					if ($this->dados_item["tipo_id"] == 11) { // Principal
						$contrato_parametros = $this->geraContratoPrincipalParametros($this->dados_item["cliente"], $contrato_assinaturas, $contrato_descontos, intval($_POST["vigencia"]), $this->dados_item["parametros"]["contrato_versao"], $_POST["parcelamento"], $this->dados_item["parametros"]["parcelamento_forma"]);
					} else { // Alteração Assinaturas
						$contrato_parametros = $this->geraContratoAditivoAlterarAssinaturasParametros($this->buscaDadosUltimoContrato($this->dados_item["cliente"], $this->dados_item["pai"]["id"], $this->dados_item["id"]), $contrato_assinaturas, $contrato_descontos, $this->dados_item["parametros"]["contrato_versao"]);
					}

					// Cancela Visitas
					if ($this->dados_item["visitas"]) {
						foreach ($this->dados_item["visitas"] as $visita) {
							$this->bancodedados->query("UPDATE tab_visitas SET via_id = 4 WHERE vis_id = {$visita["id"]}");
							$this->registraComentarioVisita($visita["id"], "<b>Cancelada</b>: Contrato alterado. Re-enviado para <b>Aprovação Comercial</b>", 18, $_SESSION["usuario"]);
						}
					}

					if ($this->dados_item["status_id"] > 13) {
						$this->registraComentario($this->dados_item["id"], 30);
					}
					
				} else {
					$contrato_parametros = $this->dados_item["parametros"];
				}
				
				// Corrige Data Cancelamento / Migração Assinatura
				if ($this->dados_item["tipo_id"] == 13 && $_POST["data_ativacao"] != "NULL") {
					$data_cancelamento = str_replace("'", "", $_POST["data_ativacao"]);
					$data_cancelamento = explode("-", $data_cancelamento);
					$data_cancelamento = date("Y-m-d", strtotime("{$data_cancelamento[0]}-{$data_cancelamento[1]}-{$data_cancelamento[2]} -1 day"));
					foreach ($this->dados_item["assinaturas"] as $assinatura) {
						if ($assinatura["migrada"]) {
							$this->bancodedados->query("
								UPDATE 
									tab_assinaturas 
								SET
									ass_data_cancelamento = '$data_cancelamento' 
								WHERE 
									ass_id = {$assinatura["migrada"]}");
						}
					}
				}
				
				if ($this->ehEditavelVigencia()) {
					if ($this->dados_item["tipo_id"] == 11) { // Principal
						$cor_vigencia = $contrato_parametros["contrato_prazo_vigencia"] ? $contrato_parametros["contrato_prazo_vigencia"] : "NULL";
						$cor_multa = $contrato_parametros["contrato_multa_rescisao"] ? preg_replace("/[^0-9]/", "", $contrato_parametros["contrato_multa_rescisao"]) : "NULL";
					} else {
						$_POST["vigencia"] = intval(preg_replace("/[^0-9]/", "", $_POST["vigencia"]), 10);
						$_POST["valor_multa"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_multa"]), 10);
						$cor_vigencia = $_POST["vigencia"] ? $_POST["vigencia"] : "NULL";
						$cor_multa = $_POST["valor_multa"] ? $_POST["valor_multa"] : "NULL";
					}
				} else {
					$cor_vigencia = 'tab_contratos.cor_vigencia';
					$cor_multa = 'tab_contratos.cor_multa';
				}
				
				foreach ($contrato_parametros["contrato_assinaturas"] AS $assinatura) {
					$this->bancodedados->query("
						UPDATE
							tab_assinaturas
							LEFT OUTER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
						SET
							tab_assinaturas.ass_data_fechamento = {$_POST["data_geracao"]},
							tab_assinaturas.ass_data_ativacao = {$_POST["data_ativacao"]},
							tab_descontos_concessoes.dec_data_inicio = {$_POST["data_ativacao"]},
							tab_descontos_concessoes.dec_data_termino = IF(tab_descontos_concessoes.dec_validade IS NOT NULL, DATE_ADD({$_POST["data_ativacao"]}, INTERVAL tab_descontos_concessoes.dec_validade MONTH), tab_descontos_concessoes.dec_data_termino)
						WHERE
							tab_assinaturas.ass_id = {$assinatura["id"]}");
				}
				
				$contrato_parametros = addslashes(serialize($contrato_parametros));
				
				// Atualiza Data Cancelamento
				if ($this->dados_item["tipo_id"] && $this->dados_item["data_cancelamento"]) {
					foreach ($this->dados_item["assinaturas"] AS $assinatura) {
						$this->bancodedados->query("
							UPDATE
								tab_assinaturas
							SET
								ass_data_cancelamento = {$_POST["data_cancelamento"]}
							WHERE
								ass_id = {$assinatura["id"]}");
					}
				}
				
				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						tab_contratos.cor_data_geracao = {$_POST["data_geracao"]},
						tab_contratos.cor_data_ativacao = {$_POST["data_ativacao"]},
						tab_contratos.cor_data_cancelamento = {$_POST["data_cancelamento"]},
						tab_contratos.cor_vigencia = $cor_vigencia,
						tab_contratos.cor_multa = $cor_multa,
						tab_contratos.cor_parametros = IF(tab_contratos.crs_id < 18, '$contrato_parametros', tab_contratos.cor_parametros),
						tab_contratos.crs_id = IF(tab_contratos.crs_id BETWEEN 13 AND 17, 13, tab_contratos.crs_id),
						tab_contratos.cor_enviado = IF(tab_contratos.crs_id BETWEEN 13 AND 17, 0, tab_contratos.cor_enviado)
					WHERE
						cor_id = {$_GET["i"]}");
				
				// Atualiza Filhos
				$this->bancodedados->query("
					UPDATE 
						tab_contratos 
					SET 
						tab_contratos.cor_data_cancelamento = {$_POST["data_cancelamento"]} 
					WHERE 
						cor_pai = {$_GET["i"]}");
				
				// Atualiza Término Fidelidade
				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						cor_termino_fidelidade = DATE_SUB(DATE_ADD(cor_data_ativacao, INTERVAL cor_vigencia MONTH), INTERVAL 1 DAY)
					WHERE
						cor_id = {$_GET["i"]} AND
						cor_vigencia > 0 AND
						cor_data_ativacao IS NOT NULL AND
						cor_termino_fidelidade IS NULL");
				
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
			}
			
		} else {
			$this->fechar();
		}
		
	}

	##########################################
	### Editar Aditivo Mudança de Endereço ###
	##########################################

	function editar_aditivo_me() {
		
		if (!$_POST) {
			
			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
			$desabilitados = array("mesmo_endereco", "condominio", "bloco", "cep", "numero", "complemento_tipo", "complemento_valor", "contato", "email", "nao_possui_email", "cobranca_condominio", "cobranca_bloco", "cobranca_cep", "cobranca_numero", "cobranca_complemento_tipo", "cobranca_complemento_valor", "valor_instalacao", "parcelamento", "valor_multa", "vigencia");
			$habilitados	 = array("mesmo_endereco", "condominio", "bloco", "cep", "numero", "complemento_tipo", "complemento_valor", "contato", "email", "nao_possui_email", "cobranca_condominio", "cobranca_bloco", "cobranca_cep", "cobranca_numero", "cobranca_complemento_tipo", "cobranca_complemento_valor", "valor_instalacao", "parcelamento", "valor_multa", "vigencia");
			$campos_status = array();
			
			for ($i = 0; $i < sizeof($desabilitados); $i++) {
				$campos_status[$desabilitados[$i]] = in_array($desabilitados[$i], $habilitados) ? false : true;
			}

			if (!$this->dados_item["parametros"]["contrato_valor_instalacao"]) {
				$campos_status["parcelamento"] = true;
			}
			if (!$this->dados_item["vigencia"]) {
				$campos_status["valor_multa"] = true;
			}

			$botoes = array();
			if ($this->ehEnviavelContrato()) $botoes[] = '<input type="button" name="enviar_contrato" class="buttonAzulEscuroBranco11" value="Enviar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehConfirmavel()) $botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=confirmar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAtivavel()) $botoes[]	= '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAtivavelSemPendencia()) $botoes[]	= '<input type="button" name="ativar_sem_pendencias" class="buttonAzulEscuroBranco11" value="Ativar Sem Pendências" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehConsultavelCredito()) $botoes[] = '<input type="button" name="consultar_credito" class="buttonAzulEscuroBranco11" style="width:120px" value="Consultar Crédito" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=consultar_credito&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_aprovar" class="buttonAzulEscuroBranco11" style="width:140px" value="Aprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_reprovar" class="buttonAzulEscuroBranco11" style="width:150px" value="Reprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelComercial()) $botoes[] = '<input type="button" name="comercial_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelComercial()) $botoes[] = '<input type="button" name="comercial_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehVisualizavelContrato()) $botoes[]	= '<input type="button" name="visualizar_contrato" class="buttonAzulEscuroBranco11" style="width:130px" value="Visualizar (Contrato)" onClick="visualizarContrato('.$this->dados_item["id"].', \'contrato\', 0);">';
			if ($this->ehVisualizavelVistoria()) $botoes[]	= '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" style="width:120px" value="Visualizar (Vistoria)" onClick="visualizarContrato('.$this->dados_item["id"].', \'vistoria\', 0);">';
			if ($this->ehBaixavelContrato()) $botoes[] = '<input type="button" name="baixar_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Baixar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=baixar_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivelContrato()) $botoes[] = '<input type="button" name="remover_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Remover Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelContrato()) $botoes[] = '<input type="button" name="anular" class="buttonAzulEscuroBranco11" value="Anular" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelAtivacao()) $botoes[] = '<input type="button" name="anular_ativacao" class="buttonAzulEscuroBranco11" value="Anular Ativação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_ativacao&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("contratos.aditivo_me.editar.inc.php");
			
			$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Editar [{$this->dados_item["tipo_nome"]}]&nbsp;</b></td>", $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
			$this->conteudo = str_replace("[nome]", $dados_cliente["nome"], $this->conteudo);
			$this->conteudo = str_replace("[telefones]", $dados_cliente["lista_telefones"], $this->conteudo);
			$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			
			if ($this->dados_item["parametros"]["contrato_versao"] >= 9) {
				
				if($this->dados_item["status_id"] >= 18){
					$this->conteudo = str_replace("[formas_pagamento]", $this->geraListagemFormasPagamentoServico($this->dados_item["formas_pagamento"], $this->dados_item["forma_pagamento_id"], false), $this->conteudo);
				}else{
					$this->conteudo = str_replace("[formas_pagamento]", $this->geraListagemFormasPagamentoServico($this->buscaFormasPagamentoServico($this->dados_item["servico_id"]), $this->dados_item["formas_pagamento"]), $this->conteudo);
				}

				// Endereço Residencial
				if ($this->dados_item["parametros"]["cliente_endereco_condominio_id"]) {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $this->dados_item["parametros"]["cliente_endereco_condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", $this->geraSelect($this->buscaBlocos($this->dados_item["parametros"]["cliente_endereco_condominio_id"]), $this->dados_item["parametros"]["cliente_endereco_bloco_id"]), $this->conteudo);
					$campos_status["cep"] = true;
					$campos_status["numero"] = true;
				} else {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", "", $this->conteudo);
					$campos_status["bloco"] = true;
				}
				$this->conteudo = str_replace("[cep]", $this->dados_item["parametros"]["cliente_endereco_cep"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $this->dados_item["parametros"]["cliente_endereco_numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->complemento_tipos, $this->dados_item["parametros"]["cliente_endereco_complemento_tipo_id"]), $this->conteudo);
				$this->conteudo = str_replace("[complemento_valor]", $this->dados_item["parametros"]["cliente_endereco_complemento_valor"], $this->conteudo);
				// Endereço Cobrança / Correspondencia
				if ($this->dados_item["parametros"]["cliente_endereco_cobranca_condominio_id"]) {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios(), $this->dados_item["parametros"]["cliente_endereco_cobranca_condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", $this->geraSelect($this->buscaBlocos($this->dados_item["parametros"]["cliente_endereco_cobranca_condominio_id"]), $this->dados_item["parametros"]["cliente_endereco_cobranca_bloco_id"]), $this->conteudo);
					$campos_status["cobranca_cep"] = true;
					$campos_status["cobranca_numero"] = true;
				} else {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", "", $this->conteudo);
					$campos_status["cobranca_bloco"] = true;
				}
				$this->conteudo = str_replace("[cobranca_cep]", $this->dados_item["parametros"]["cliente_endereco_cobranca_cep"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_numero]", $this->dados_item["parametros"]["cliente_endereco_cobranca_numero"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_tipo]", $this->geraSelect($this->complemento_tipos, $this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_tipo_id"]), $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_valor]", $this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_valor"], $this->conteudo);
				$mesmo_endereco_cobranca = ($this->dados_item["parametros"]["cliente_endereco_cobranca_condominio_id"] == $this->dados_item["parametros"]["cliente_endereco_condominio_id"] &&
				$this->dados_item["parametros"]["cliente_endereco_cobranca_bloco_id"] == $this->dados_item["parametros"]["cliente_endereco_bloco_id"] &&
				$this->dados_item["parametros"]["cliente_endereco_cobranca_cep"] == $this->dados_item["parametros"]["cliente_endereco_cep"] &&
				$this->dados_item["parametros"]["cliente_endereco_cobranca_numero"] == $this->dados_item["parametros"]["cliente_endereco_numero"] &&
				$this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_tipo"] == $this->dados_item["parametros"]["cliente_endereco_complemento_tipo"] &&
				$this->dados_item["parametros"]["cliente_endereco_cobranca_complemento_valor"] == $this->dados_item["parametros"]["cliente_endereco_complemento_valor"]) ? "checked" : "";
			
			} else {
				
				// Endereço Residencial
				if ($this->dados_item["parametros"]["final"]["condominio_id"]) {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $this->dados_item["parametros"]["final"]["condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", $this->geraSelect($this->buscaBlocos($this->dados_item["parametros"]["final"]["condominio_id"]), $this->dados_item["parametros"]["final"]["bloco_id"]), $this->conteudo);
					$campos_status["cep"] = true;
					$campos_status["numero"] = true;
				} else {
					$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[bloco]", "", $this->conteudo);
					$campos_status["bloco"] = true;
				}
				$this->conteudo = str_replace("[cep]", $this->dados_item["parametros"]["final"]["cep"], $this->conteudo);
				$this->conteudo = str_replace("[numero]", $this->dados_item["parametros"]["final"]["numero"], $this->conteudo);
				$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->complemento_tipos, $this->dados_item["parametros"]["final"]["complemento_tipo_id"]),$this->conteudo);
				$this->conteudo = str_replace("[complemento_valor]", $this->dados_item["parametros"]["final"]["complemento_valor"], $this->conteudo);
				// Endereço Cobrança / Correspondencia
				if ($this->dados_item["parametros"]["final"]["cobranca_condominio_id"]) {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios(), $this->dados_item["parametros"]["final"]["cobranca_condominio_id"]), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", $this->geraSelect($this->buscaBlocos($this->dados_item["parametros"]["final"]["cobranca_condominio_id"]), $this->dados_item["parametros"]["final"]["cobranca_bloco_id"]), $this->conteudo);
					$campos_status["cobranca_cep"] = true;
					$campos_status["cobranca_numero"] = true;
				} else {
					$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
					$this->conteudo = str_replace("[cobranca_bloco]", "", $this->conteudo);
					$campos_status["cobranca_bloco"] = true;
				}
				$this->conteudo = str_replace("[cobranca_cep]", $this->dados_item["parametros"]["final"]["cobranca_cep"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_numero]", $this->dados_item["parametros"]["final"]["cobranca_numero"], $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_tipo]", $this->geraSelect($this->complemento_tipos, $this->dados_item["parametros"]["final"]["cobranca_complemento_tipo_id"]), $this->conteudo);
				$this->conteudo = str_replace("[cobranca_complemento_valor]", $this->dados_item["parametros"]["final"]["cobranca_complemento_valor"], $this->conteudo);
				$mesmo_endereco_cobranca = ($this->dados_item["parametros"]["final"]["cobranca_condominio_id"] == $this->dados_item["parametros"]["final"]["condominio_id"] &&
				$this->dados_item["parametros"]["final"]["cobranca_bloco_id"] == $this->dados_item["parametros"]["final"]["bloco_id"] &&
				$this->dados_item["parametros"]["final"]["cobranca_cep"] == $this->dados_item["parametros"]["final"]["cep"] &&
				$this->dados_item["parametros"]["final"]["cobranca_numero"] == $this->dados_item["parametros"]["final"]["numero"] &&
				$this->dados_item["parametros"]["final"]["cobranca_complemento_tipo"] == $this->dados_item["parametros"]["final"]["complemento_tipo"] &&
				$this->dados_item["parametros"]["final"]["cobranca_complemento_valor"] == $this->dados_item["parametros"]["final"]["complemento_valor"]) ? "checked" : "";
			
			}
			
			$this->conteudo = str_replace("[mesmo_endereco_checked]", $mesmo_endereco_cobranca, $this->conteudo);
			
			if ($mesmo_endereco_cobranca) {
				$campos_status["cobranca_condominio"] = true;
				$campos_status["cobranca_bloco"] = true;
				$campos_status["cobranca_cep"] = true;
				$campos_status["cobranca_numero"] = true;
				$campos_status["cobranca_complemento_tipo"] = true;
				$campos_status["cobranca_complemento_valor"] = true;
			}
			
			// Permissão dados duplicados
			$this->conteudo = str_replace("[duplicar_endereco]", $this->permissoes["clientes"]["duplicar_endereco"] ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicar_cpf_cnpj]", $this->permissoes["clientes"]["duplicar_cpf_cnpj"] ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicado_endereco]", $this->existeDuplicidadeEndereco($dados_cliente["id"], $dados_cliente["bloco_id"], $dados_cliente["cep_id"], $dados_cliente["numero"], $dados_cliente["complemento_tipo_id"], $dados_cliente["complemento_valor"]) ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicado_cpf_cnpj]", $this->existeDuplicidadeCPFCNPJ($dados_cliente["id"], $dados_cliente["cpf_cnpj"]) ? 1 : 0, $this->conteudo);
			
			// Comentários
			$this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
			
			// Datas
			$this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
			$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
			if ($this->ehEditavelDatas()) {
				$this->conteudo = str_replace("[data_geracao_disabled]", "", $this->conteudo);
				$this->conteudo = str_replace("[data_ativacao_disabled]", (in_array($this->dados_item["status_id"], array(18, 19, 20, 21, 22, 23)) ? "" : "disabled"), $this->conteudo);
				$this->conteudo = str_replace("[data_cancelamento_disabled]", (in_array($this->dados_item["status_id"], array(21, 22, 23)) ? "" : "disabled"), $this->conteudo);
			} else {
				$this->conteudo = str_replace("[data_geracao_disabled]", "disabled", $this->conteudo);
				$this->conteudo = str_replace("[data_ativacao_disabled]", "disabled", $this->conteudo);
				$this->conteudo = str_replace("[data_cancelamento_disabled]", "disabled", $this->conteudo);
			}
			
			// Instalação / Fidelização
			$this->conteudo = str_replace("[vigencia]", $this->geraSelect($this->buscaListaVigencia(36), $this->dados_item["vigencia"]), $this->conteudo);
			$this->conteudo = str_replace("[valor_multa]", $this->formataDinheiro($this->dados_item["multa"]), $this->conteudo);
			$this->conteudo = str_replace("[valor_instalacao]", $this->formataDinheiro($this->dados_item["parametros"]["contrato_valor_instalacao"]), $this->conteudo);
			$this->conteudo = str_replace("[parcelamento]", $this->geraSelect($this->buscaParcelamentos(null, $this->dados_item["parametros"]["parcelamento_forma"], 12), $this->dados_item["parametros"]["parcelamento"]), $this->conteudo);

			// Desabilitar Campos
			foreach ($campos_status as $campo => $valor) {
				$this->conteudo = str_replace("[{$campo}_disabled]", ($valor || $this->dados_item["status_id"] >= 18) ? "disabled" : "", $this->conteudo);
			}
			
			// Situação e Status
			$this->conteudo = str_replace("[instalador]", $this->buscaInstalador($this->dados_item["id"]), $this->conteudo);
			$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		} else {
			
		    $comentario = array();
		    
			if (trim($_POST["comentario"])) {
				$comentario[] = trim($_POST["comentario"]);
			}
			if ($_POST["data_geracao"] && $_POST["data_geracao"] != $this->dados_item["data_geracao"]) {
				$comentario[] = "<b>Alteraçao Data de Geração</b>: <b>{$this->dados_item["data_geracao"]}</b> para <b>{$_POST["data_geracao"]}</b>";
			}
			if ($_POST["data_ativacao"] && $_POST["data_ativacao"] != $this->dados_item["data_ativacao"]) {
				$comentario[] = "<b>Alteraçao Data de Ativação</b>: <b>{$this->dados_item["data_ativacao"]}</b> para <b>{$_POST["data_ativacao"]}</b>";
			}
			if ($_POST["data_cancelamento"] && $_POST["data_cancelamento"] != $this->dados_item["data_cancelamento"]) {
				$comentario[] = "<b>Alteraçao Data de Cancelamento</b>: <b>{$this->dados_item["data_cancelamento"]}</b> para <b>{$_POST["data_cancelamento"]}</b>";
			}
			
			if ($comentario) {
				$comentario = implode("<br>", $comentario);
				$this->registraComentario($this->dados_item["id"], 21, $comentario, $_SESSION["usuario"]);
			}
			
			if ($this->dados_item["data_geracao"] && !$_POST["data_geracao"]) {
				$_POST["data_geracao"] = $this->dados_item["data_geracao"];
			}
			if ($this->dados_item["data_ativacao"] && !$_POST["data_ativacao"]) {
				$_POST["data_ativacao"] = $this->dados_item["data_ativacao"];
			}
			if ($this->dados_item["data_cancelamento"] && !$_POST["data_cancelamento"]) {
				$_POST["data_cancelamento"] = $this->dados_item["data_cancelamento"];
			}

			$_POST["data_geracao"] = $_POST["data_geracao"] ? "'".substr($_POST["data_geracao"], 6, 4)."-".substr($_POST["data_geracao"], 3, 2)."-".substr($_POST["data_geracao"], 0, 2)."'" : "NULL";
			$_POST["data_ativacao"] = $_POST["data_ativacao"] ? "'".substr($_POST["data_ativacao"], 6, 4)."-".substr($_POST["data_ativacao"], 3, 2)."-".substr($_POST["data_ativacao"], 0, 2)."'" : "NULL";
			$_POST["data_cancelamento"] = $_POST["data_cancelamento"] ? "'".substr($_POST["data_cancelamento"], 6, 4)."-".substr($_POST["data_cancelamento"], 3, 2)."-".substr($_POST["data_cancelamento"], 0, 2)."'" : "NULL";
			
			if ($this->dados_item["status_id"] < 18) {
				// Cancela Visita
				foreach ($this->dados_item["visitas"] as $visita) {
					$this->bancodedados->query("UPDATE tab_visitas SET via_id = 4 WHERE vis_id = {$visita["id"]}");
					$this->registraComentarioVisita($visita["id"], "<b>Cancelada</b>: Contrato editado. Re-enviado para <b>Aprovação Comercial</b>", 18, $_SESSION["usuario"]);
				}
				// Desmigra Endereço
				$this->desmigraEnderecoCliente($_GET["i"]);
				// Finaliza Ajuste Contrato Inativo
				$this->finalizaBloqueioContratoInativo($this->dados_item["pai"]);
				// Regera Parâmetros
				$contrato_parametros = $this->geraContratoAditivoMudancaEnderecoParametros($this->dados_item["cliente"], $this->dados_item["assinaturas"], $this->dados_item["descontos"]);
			} else {
				$contrato_parametros = $this->dados_item["parametros"];
			}
			
			// Vincula Formas Pagamento
			if ($this->dados_item["status_id"] <= 18) {
				$this->bancodedados->query("DELETE FROM rel_fop_cor_estacontido WHERE cor_id = {$_GET["i"]}");
				if ($_POST["formas_pagamento"]) {
					foreach ($_POST["formas_pagamento"] as $forma_pagamento) {
						$this->bancodedados->query("INSERT INTO rel_fop_cor_estacontido (fop_id, cor_id) VALUES ($forma_pagamento, {$_GET["i"]})");
					}
				}
			}
			
			$_POST["vigencia"] = intval(preg_replace("/[^0-9]/", "", $_POST["vigencia"]), 10);
			$_POST["valor_multa"] = intval(preg_replace("/[^0-9]/", "", $_POST["valor_multa"]), 10);
			
			$prazo_fidelidade = $_POST["vigencia"] && $_POST["valor_multa"] ? $_POST["vigencia"] : "NULL";
			$multa_fidelidade = $_POST["vigencia"] && $_POST["valor_multa"] ? $_POST["valor_multa"] :	"NULL";
			$contrato_parametros = addslashes(serialize($contrato_parametros));
			$termino_fidelidade = $this->dados_item["status_id"] >= 19 && $prazo_fidelidade > 0 && $multa_fidelidade > 0 ? "DATE_SUB(DATE_ADD({$_POST["data_ativacao"]}, INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY)" : "NULL";
			
			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_data_geracao = {$_POST["data_geracao"]},
					cor_data_ativacao = {$_POST["data_ativacao"]},
					cor_data_cancelamento = {$_POST["data_cancelamento"]},
					cor_termino_fidelidade = $termino_fidelidade,
					cor_vigencia = $prazo_fidelidade,
					cor_multa = $multa_fidelidade,
					cor_parametros = '$contrato_parametros',
					crs_id = IF(crs_id < 18, 13, crs_id),
					cor_enviado = 0
				WHERE
					cor_id = {$_GET["i"]}");
			
			// Registra Comentário
			$this->registraComentario($this->dados_item["id"], 21, null, $_SESSION["usuario"]);
			
			// Registra atividade
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"],$this->dados_acao["nome"],$this->dados_item["id"]);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
		}
		
	}

	##############################################
	### Editar Aditivo Mudança de Titularidade ###
	##############################################

	function editar_aditivo_mt() {
		
		if (!$_POST) {
			
			$contatos = controle::instanciaObjeto("clientes.contatos", $this->bancodedados);
			$dados_cliente = $this->buscaDadosCadastraisCliente($this->dados_item["cliente"]);
			$desabilitados = array("mesmo_endereco", "nome", "tipo", "cpf_cnpj", "nascimento", "contato", "email", "nao_possui_email", "comentarios", "cobranca_condominio", "cobranca_bloco", "cobranca_cep", "cobranca_numero", "cobranca_complemento_tipo", "cobranca_complemento_valor");
			$habilitados = array("mesmo_endereco", "nome", "tipo", "cpf_cnpj", "nascimento", "contato", "email", "nao_possui_email", "comentarios", "cobranca_condominio", "cobranca_bloco", "cobranca_cep", "cobranca_numero", "cobranca_complemento_tipo", "cobranca_complemento_valor");
			$campos_status = array();

			$mesmo_endereco_cobranca = $this->dados_item["parametros"]["final"]["endereco_cobranca"]["condominio_id"] == $dados_cliente["condominio_id"] &&
				$this->dados_item["parametros"]["final"]["endereco_cobranca"]["bloco_id"] == $dados_cliente["bloco_id"] &&
				$this->dados_item["parametros"]["final"]["endereco_cobranca"]["cep"] == $dados_cliente["cep"] &&
				$this->dados_item["parametros"]["final"]["endereco_cobranca"]["numero"] == $dados_cliente["numero"] &&
				$this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento_id"] == $dados_cliente["complemento_tipo_id"] &&
				$this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento"] == $dados_cliente["complemento_valor"];

			for ($i = 0; $i < sizeof($desabilitados); $i++) {
				$campos_status[$desabilitados[$i]] = (in_array($desabilitados[$i], $habilitados)) ? false : true;
			}

			if ($dados_cliente["condominio_id"]) {
				$campos_status["cep"] = true;
				$campos_status["numero"] = true;
			} else {
				$campos_status["bloco"] = true;
			}

			if ($this->dados_item["parametros"]["final"]["endereco_cobranca"]["condominio_id"]) {
				$campos_status["cobranca_cep"] = true;
				$campos_status["cobranca_numero"] = true;
			} else {
				$campos_status["cobranca_bloco"] = true;
			}

			if ($mesmo_endereco_cobranca) {
				$campos_status["cobranca_condominio"] = true;
				$campos_status["cobranca_bloco"] = true;
				$campos_status["cobranca_cep"] = true;
				$campos_status["cobranca_numero"] = true;
				$campos_status["cobranca_complemento_tipo"] = true;
				$campos_status["cobranca_complemento_valor"] = true;
			}
		
			$botoes = array();
			if ($this->ehEnviavelContrato()) $botoes[] = '<input type="button" name="enviar_contrato" class="buttonAzulEscuroBranco11" value="Enviar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=enviar_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehConfirmavel()) $botoes[] = '<input type="button" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=confirmar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAtivavel()) $botoes[]	= '<input type="button" name="ativar" class="buttonAzulEscuroBranco11" value="Ativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehDesativavel()) $botoes[] = '<input type="button" name="desativar" class="buttonAzulEscuroBranco11" value="Desativar" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=desativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAtivavelSemPendencia()) $botoes[]	= '<input type="button" name="ativar_sem_pendencias" class="buttonAzulEscuroBranco11" value="Ativar Sem Pendências" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=ativar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehConsultavelCredito()) $botoes[] = '<input type="button" name="consultar_credito" class="buttonAzulEscuroBranco11" style="width:120px" value="Consultar Crédito" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=consultar_credito&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_aprovar" class="buttonAzulEscuroBranco11" style="width:140px" value="Aprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelFinanceiro()) $botoes[] = '<input type="button" name="financeiro_reprovar" class="buttonAzulEscuroBranco11" style="width:150px" value="Reprovar (Financeiro)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=financeiro_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelComercial()) $botoes[] = '<input type="button" name="comercial_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelComercial()) $botoes[] = '<input type="button" name="comercial_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Comercial)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=comercial_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_aprovar" class="buttonAzulEscuroBranco11" value="Aprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_aprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehReprovavelTecnico()) $botoes[] = '<input type="button" name="tecnico_reprovar" class="buttonAzulEscuroBranco11" value="Reprovar (Técnico)" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=tecnico_reprovar&i='.$this->dados_item["id"].'\';">';
			if ($this->ehVisualizavelContrato()) $botoes[]	= '<input type="button" name="visualizar_contrato" class="buttonAzulEscuroBranco11" style="width:130px" value="Visualizar (Contrato)" onClick="visualizarContrato('.$this->dados_item["id"].', \'contrato\', 0);">';
			if ($this->ehVisualizavelVistoria()) $botoes[] = '<input type="button" name="visualizar_vistoria" class="buttonAzulEscuroBranco11" style="width:120px" value="Visualizar (Vistoria)" onClick="visualizarContrato('.$this->dados_item["id"].', \'vistoria\', 0);">';
			if ($this->ehBaixavelContrato()) $botoes[] = '<input type="button" name="baixar_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Baixar Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=baixar_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivelContrato()) $botoes[] = '<input type="button" name="remover_contrato" class="buttonAzulEscuroBranco11" style="width:100px" value="Remover Contrato" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover_contrato&i='.$this->dados_item["id"].'\';">';
			if ($this->ehRemovivel()) $botoes[] = '<input type="button" name="remover" class="buttonAzulEscuroBranco11" value="Remover" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=remover&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelContrato()) $botoes[] = '<input type="button" name="anular" class="buttonAzulEscuroBranco11" value="Anular" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular&i='.$this->dados_item["id"].'\';">';
			if ($this->ehAnulavelAtivacao()) $botoes[] = '<input type="button" name="anular_ativacao" class="buttonAzulEscuroBranco11" value="Anular Ativação" onClick="window.location=\'?m='.$this->dados_modulo["nome"].'&a=anular_ativacao&i='.$this->dados_item["id"].'\';">';
			$botoes[] = '<input type="submit" name="salvar" style="width:50px" class="buttonAzulEscuroBranco11" value="Salvar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("contratos.aditivo_mt.editar.inc.php");

			$this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Editar [{$this->dados_item["tipo_nome"]}]&nbsp;</b></td>", $this->conteudo);
			$this->conteudo = str_replace("[cliente_id]", $dados_cliente["id"], $this->conteudo);
			$this->conteudo = str_replace("[contrato_nome]", $dados_cliente["nome"], $this->conteudo);
			$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[contrato_tipo]", $this->dados_item["tipo_nome"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			
			if ($this->dados_item["parametros"]["contrato_versao"] >= 9) {
				// Cliente Antigo
				$this->conteudo = str_replace("[nome_antigo]", $this->dados_item["parametros"]["cliente_antigo"]["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[pessoa_tipo_antigo]", ($this->dados_item["parametros"]["cliente_antigo"]["cliente_tipo"] == 1) ? "Pessoa Física" : "Pessoa Jurídica", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_label_antigo]", ($this->dados_item["parametros"]["cliente_antigo"]["cliente_tipo"] == 1) ? "CPF" : "CNPJ", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_antigo]", $this->dados_item["parametros"]["cliente_antigo"]["cliente_cpf_cnpj"], $this->conteudo);
				$this->conteudo = str_replace("[nascimento_antigo]", $this->dados_item["parametros"]["cliente_antigo"]["cliente_nascimento"], $this->conteudo);
				// Novo Titular
				$this->conteudo = str_replace("[nome]", $this->dados_item["parametros"]["cliente_nome"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTiposCliente(), $this->dados_item["parametros"]["cliente_tipo"]), $this->conteudo);
				$this->conteudo = str_replace("[tipo_id]", $this->dados_item["parametros"]["cliente_tipo"], $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["parametros"]["cliente_cpf_cnpj"], $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_maxlength]", ($this->dados_item["parametros"]["cliente_tipo"] == 2) ? "18" : "14", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_label]", ($this->dados_item["parametros"]["cliente_tipo"] == 2) ? "CNPJ" : "CPF", $this->conteudo);
				$this->conteudo = str_replace("[nascimento]", $this->dados_item["parametros"]["cliente_nascimento"], $this->conteudo);
				$this->conteudo = str_replace("[contato]", $this->dados_item["parametros"]["cliente_contato"], $this->conteudo);
				// Contatos
				$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($this->dados_item["parametros"]["cliente_telefones"], true), $this->conteudo);
				$this->conteudo = str_replace("[lista_telefones]", serialize($this->dados_item["parametros"]["cliente_telefones"]), $this->conteudo);
				$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($this->dados_item["parametros"]["cliente_emails"], true), $this->conteudo);
				$this->conteudo = str_replace("[lista_emails]", serialize($this->dados_item["parametros"]["cliente_emails"]), $this->conteudo);
			} else {
				// Cliente Antigo
				$this->conteudo = str_replace("[nome_antigo]", $this->dados_item["parametros"]["inicial"]["nome"], $this->conteudo);
				$this->conteudo = str_replace("[pessoa_tipo_antigo]", ($this->dados_item["parametros"]["inicial"]["tipo"] == 1) ? "Pessoa Física" : "Pessoa Jurídica", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_label_antigo]", ($this->dados_item["parametros"]["inicial"]["tipo"] == 1) ? "CPF" : "CNPJ", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_antigo]", $this->dados_item["parametros"]["inicial"]["cpf_cnpj"], $this->conteudo);
				$this->conteudo = str_replace("[nascimento_antigo]", $this->dados_item["parametros"]["inicial"]["nascimento"], $this->conteudo);
				// Novo Titular
				$this->conteudo = str_replace("[nome]", $this->dados_item["parametros"]["final"]["nome"], $this->conteudo);
				$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTiposCliente(), $this->dados_item["parametros"]["final"]["tipo"]), $this->conteudo);
				$this->conteudo = str_replace("[tipo_id]", $this->dados_item["parametros"]["final"]["tipo"], $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj]", $this->dados_item["parametros"]["final"]["cpf_cnpj"], $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_maxlength]", ($this->dados_item["parametros"]["final"]["tipo"] == 2) ? "18" : "14", $this->conteudo);
				$this->conteudo = str_replace("[cpf_cnpj_label]", ($this->dados_item["parametros"]["final"]["tipo"] == 2) ? "CNPJ" : "CPF", $this->conteudo);
				$this->conteudo = str_replace("[nascimento]", $this->dados_item["parametros"]["final"]["nascimento"], $this->conteudo);
				$this->conteudo = str_replace("[contato]", $this->dados_item["parametros"]["final"]["contato"], $this->conteudo);
				// Contatos
				$this->conteudo = str_replace("[listagem_telefones]", $contatos->geraContatosTelefonesListagem($this->dados_item["parametros"]["final"]["telefones"], true), $this->conteudo);
				$this->conteudo = str_replace("[lista_telefones]", serialize($this->dados_item["parametros"]["final"]["telefones"]), $this->conteudo);
				$this->conteudo = str_replace("[listagem_emails]", $contatos->geraContatosEmailsListagem($this->dados_item["parametros"]["final"]["emails"], true), $this->conteudo);
				$this->conteudo = str_replace("[lista_emails]", serialize($this->dados_item["parametros"]["final"]["emails"]), $this->conteudo);
			}
			
			// Endereço Instalação
			if ($dados_cliente["condominio_id"]) {
				$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $dados_cliente["condominio_id"]), $this->conteudo);
				$this->conteudo = str_replace("[bloco]", $this->geraSelect($this->buscaBlocos($dados_cliente["condominio_id"]), $dados_cliente["bloco_id"]), $this->conteudo);
			} else {
				$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
				$this->conteudo = str_replace("[bloco]", "", $this->conteudo);
			}
			$this->conteudo = str_replace("[cep]", $dados_cliente["cep"], $this->conteudo);
			$this->conteudo = str_replace("[numero]", $dados_cliente["numero"], $this->conteudo);
			$this->conteudo = str_replace("[complemento_tipo]", $this->geraSelect($this->complemento_tipos, $dados_cliente["complemento_tipo_id"]), $this->conteudo);
			$this->conteudo = str_replace("[complemento_valor]", $dados_cliente["complemento_valor"], $this->conteudo);
			
			// Endereço Cobrança / Correspondencia
			if ($this->dados_item["parametros"]["final"]["endereco_cobranca"]["condominio_id"]) {
				$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios(), $this->dados_item["parametros"]["final"]["endereco_cobranca"]["condominio_id"]), $this->conteudo);
				$this->conteudo = str_replace("[cobranca_bloco]", $this->geraSelect($this->buscaBlocos($this->dados_item["parametros"]["final"]["endereco_cobranca"]["condominio_id"]), $this->dados_item["parametros"]["final"]["endereco_cobranca"]["bloco_id"]), $this->conteudo);
			} else {
				$this->conteudo = str_replace("[cobranca_condominio]", $this->geraSelect($this->buscaCondominios()), $this->conteudo);
				$this->conteudo = str_replace("[cobranca_bloco]", "", $this->conteudo);
			}
			$this->conteudo = str_replace("[cobranca_cep]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["cep"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_numero]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["numero"], $this->conteudo);
			$this->conteudo = str_replace("[cobranca_complemento_tipo]", $this->geraSelect($this->complemento_tipos, $this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento_id"]), $this->conteudo);
			$this->conteudo = str_replace("[cobranca_complemento_valor]", $this->dados_item["parametros"]["final"]["endereco_cobranca"]["complemento"], $this->conteudo);

			$this->conteudo = str_replace("[mesmo_endereco_checked]", $mesmo_endereco_cobranca ? "checked" : "", $this->conteudo);
			
			// Desabilitar Campos
			foreach ($campos_status as $campo => $valor) {
				$this->conteudo = str_replace("[{$campo}_disabled]", ($valor || $this->dados_item["status_id"] >= 18) ? "disabled" : "", $this->conteudo);
			}
			
			// Permissão dados duplicados
			$this->conteudo = str_replace("[duplicar_endereco]", $this->permissoes["clientes"]["duplicar_endereco"] ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicar_cpf_cnpj]", $this->permissoes["clientes"]["duplicar_cpf_cnpj"] ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicado_endereco]", $this->existeDuplicidadeEndereco($dados_cliente["id"], $dados_cliente["bloco_id"], $dados_cliente["cep_id"], $dados_cliente["numero"], $dados_cliente["complemento_tipo_id"], $dados_cliente["complemento_valor"]) ? 1 : 0, $this->conteudo);
			$this->conteudo = str_replace("[duplicado_cpf_cnpj]", $this->existeDuplicidadeCPFCNPJ($dados_cliente["id"], $dados_cliente["cpf_cnpj"]) ? 1 : 0, $this->conteudo);
			
			// Comentários
			$this->conteudo = str_replace("[comentarios]", $this->geraListagemComentarios($this->dados_item["comentarios"]), $this->conteudo);
			
			// Datas
			$this->conteudo = str_replace("[data_geracao]", $this->dados_item["data_geracao"], $this->conteudo);
			$this->conteudo = str_replace("[data_ativacao]", $this->dados_item["data_ativacao"], $this->conteudo);
			$this->conteudo = str_replace("[data_cancelamento]", $this->dados_item["data_cancelamento"], $this->conteudo);
			if ($this->ehEditavelDatas()) {
				$this->conteudo = str_replace("[data_geracao_disabled]", "", $this->conteudo);
				$this->conteudo = str_replace("[data_ativacao_disabled]", (in_array($this->dados_item["status_id"], array(19, 20, 21, 22, 23)) ? "" : "disabled"), $this->conteudo);
				$this->conteudo = str_replace("[data_cancelamento_disabled]", (in_array($this->dados_item["status_id"], array(21, 22, 23)) ? "" : "disabled"), $this->conteudo);
			} else {
				$this->conteudo = str_replace("[data_geracao_disabled]", "disabled", $this->conteudo);
				$this->conteudo = str_replace("[data_ativacao_disabled]", "disabled", $this->conteudo);
				$this->conteudo = str_replace("[data_cancelamento_disabled]", "disabled", $this->conteudo);
			}
			
			// Situação e Status
			$this->conteudo = str_replace("[situacao]", $this->dados_item["situacao"], $this->conteudo);
			$this->conteudo = str_replace("[status]", $this->dados_item["status_nome"], $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		} else {
			
		    $comentario = array();
		    
			if (trim($_POST["comentario"])) {
				$comentario[] = trim($_POST["comentario"]);
			}
			if ($_POST["data_geracao"] && $_POST["data_geracao"] != $this->dados_item["data_geracao"]) {
				$comentario[] = "<b>Alteraçao Data de Geração</b>: <b>{$this->dados_item["data_geracao"]}</b> para <b>{$_POST["data_geracao"]}</b>";
			}
			if ($_POST["data_ativacao"] && $_POST["data_ativacao"] != $this->dados_item["data_ativacao"]) {
				$comentario[] = "<b>Alteraçao Data de Ativação</b>: <b>{$this->dados_item["data_ativacao"]}</b> para <b>{$_POST["data_ativacao"]}</b>";
			}
			if ($_POST["data_cancelamento"] && $_POST["data_cancelamento"] != $this->dados_item["data_cancelamento"]) {
				$comentario[] = "<b>Alteraçao Data de Cancelamento</b>: <b>{$this->dados_item["data_cancelamento"]}</b> para <b>{$_POST["data_cancelamento"]}</b>";
			}
			
			if ($comentario) {
				$comentario = implode("<br>", $comentario);
				$this->registraComentario($this->dados_item["id"], 21, $comentario, $_SESSION["usuario"]);
			}
			
			if ($this->dados_item["data_geracao"] && !$_POST["data_geracao"]) {
				$_POST["data_geracao"] = $this->dados_item["data_geracao"];
			}
			if ($this->dados_item["data_ativacao"] && !$_POST["data_ativacao"]) {
				$_POST["data_ativacao"] = $this->dados_item["data_ativacao"];
			}
			if ($this->dados_item["data_cancelamento"] && !$_POST["data_cancelamento"]) {
				$_POST["data_cancelamento"] = $this->dados_item["data_cancelamento"];
			}

			$_POST["data_geracao"] = $_POST["data_geracao"] ? "'".substr($_POST["data_geracao"], 6, 4)."-".substr($_POST["data_geracao"], 3, 2)."-".substr($_POST["data_geracao"], 0, 2)."'" : "NULL";
			$_POST["data_ativacao"] = $_POST["data_ativacao"] ? "'".substr($_POST["data_ativacao"], 6, 4)."-".substr($_POST["data_ativacao"], 3, 2)."-".substr($_POST["data_ativacao"], 0, 2)."'" : "NULL";
			$_POST["data_cancelamento"] = $_POST["data_cancelamento"] ? "'".substr($_POST["data_cancelamento"], 6, 4)."-".substr($_POST["data_cancelamento"], 3, 2)."-".substr($_POST["data_cancelamento"], 0, 2)."'" : "NULL";
			
			if ($this->dados_item["status_id"] < 18) {
				$dados_contrato_pai = $this->buscaDadosUltimoContrato($this->dados_item["cliente"], $this->dados_item["pai"], $this->dados_item["id"]);
				$contrato_parametros = $this->geraContratoAditivoMudancaTitularidadeParametros($this->dados_item["cliente"], $dados_contrato_pai["assinaturas"], $dados_contrato_pai["descontos"], $this->dados_item["parametros"]);
			} else {
				$contrato_parametros = $this->dados_item["parametros"];
			}

			$contrato_parametros = addslashes(serialize($contrato_parametros));
			
			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_data_geracao = {$_POST["data_geracao"]},
					cor_data_ativacao = {$_POST["data_ativacao"]},
					cor_data_cancelamento = {$_POST["data_cancelamento"]},
					cor_parametros = '$contrato_parametros',
					crs_id = IF(crs_id < 18, 13, crs_id),
					cor_enviado = 0
				WHERE
					cor_id = {$_GET["i"]}");
			
			// Registra atividade
			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);

			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			
		}
		
	}

	##############
	### Anular ###
	##############

	function anular() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
				
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.anular_contrato.inc.php");
				
				$this->conteudo = str_replace("[motivos_anulacao]", $this->geraSelect($this->buscaMotivosAnulacao()), $this->conteudo);
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Anular&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				// Insere Chamado comercial
				if ($this->dados_item["status"] == 16 && !$this->permissoes[$this->dados_modulo["nome"]]["anular_plus"]) {
					$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
					// Gera Dados Chamado
					$dados_chamado = array();
					$dados_chamado["solicitante"] = "Sistema (Intranet)";
					$dados_chamado["categoria"] = 103;
					$dados_chamado["prioridade"] = 3;
					$dados_chamado["tipo"] = 5;
					$dados_chamado["sistema"] = true;
					$dados_chamado["conteudo"] = "Contrato {$this->dados_item["contrato"]} anulado.";
					// Adiciona Chamado
					$chamados->adicionarChamado($this->dados_item["cliente"], null, $dados_chamado);
				}
				
				// Anula Contrato
				$this->anularContrato($this->dados_item, $_POST["motivos_anulacao"], $_POST["comentarios"]);
				
				// Redireciona
				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}

	#######################
	### Anular Contrato ###
	#######################

	function anularContrato($dados_contrato, $motivo_anulacao_id, $comentarios = null) {
		
		$_SESSION["usuario"] = $_SESSION["usuario"] ? $_SESSION["usuario"] : "NULL";
		$dados_usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);

		// Insere Retorno Vendedor
		
		if ($dados_contrato["origem_id"] == 1) {
			
			$query = $this->bancodedados->query("
				SELECT DISTINCT
					usu_id AS usuario_id
				FROM
					tab_vendas
				WHERE
					cor_id = {$dados_contrato["id"]}");

			while (($usuarios = $this->bancodedados->resultadoArray($query)) != false) {
				if ($usuarios["usuario_id"] != $_SESSION["usuario"]) {
					$this->bancodedados->query("
						INSERT INTO
							tab_clientes_retornos
						SET
							cli_id = {$dados_contrato["cliente"]},
							usu_id = {$usuarios["usuario_id"]},
							clr_abertura = NOW(),
							clr_data = CURDATE(),
							clr_comentario = 'Contrato anulado por {$dados_usuario["nome"]}',
							cet_id = 2,
							ces_id = 1");
				}
			}
			
		}
		
		// Registra Comentário
		$this->registraComentario($dados_contrato["id"], 35, $comentarios, $_SESSION["usuario"]);
		
		// Registra Motivo Anulação
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				cma_id = $motivo_anulacao_id,
				cor_data_anulacao = NOW(),
				crs_id = 24
			WHERE
				cor_id = {$dados_contrato["id"]}");
		
		// Cancela assinatura, descontos, venda, serviços
		$assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($dados_contrato["id"]);

		foreach ($assinaturas_adicionadas as $assinatura_adicionada) {

			// Remove Venda
			$this->removeVenda($assinatura_adicionada["ass_id"]);

			// Anula Assinatura
			$this->bancodedados->query("UPDATE tab_assinaturas SET ast_id = 6 WHERE ass_id = {$assinatura_adicionada["ass_id"]}");

			// Cancela Descontos
			$descontos = $this->buscaDescontosAssinatura($assinatura_adicionada["ass_id"], false, "1,2,3,4");

			foreach ($descontos as $desconto_id) {
				$this->bancodedados->query("UPDATE tab_descontos_concessoes SET dcs_id = 4 WHERE dec_id = $desconto_id");
				$this->registraComentarioDesconto($desconto_id, "<b>Cancelado:</b> Contrato Anulado", $_SESSION["usuario"]);
			}

			$this->cancelaEmails($assinatura_adicionada["ass_id"], $dados_contrato["assinaturas"], $dados_contrato["cliente"], $dados_contrato["codigo"], $_SESSION["usuario"]);
			$this->cancelaPontos($assinatura_adicionada["ass_id"], $dados_contrato["cliente"], $dados_contrato["codigo"]);
			$this->cancelaAutenticacoes($assinatura_adicionada["ass_id"], $dados_contrato["cliente"], $dados_contrato["codigo"]);
			$this->cancelaDominios($assinatura_adicionada["ass_id"]);

		}
		
		// Cancela Serviços Ajustes
		$this->cancelaServicosAjustes($dados_contrato["cliente"]);
		
		// Cancela Visitas
		foreach ($dados_contrato["visitas"] as $visita) {
			$this->bancodedados->query("UPDATE tab_visitas SET via_id = 4 WHERE vis_id = {$visita["id"]}");
			$this->registraComentarioVisita($visita["id"], "<b>Cancelada</b>: Contrato Anulado", 18, $_SESSION["usuario"]);
		}
		
		// Aditivo Mudança Endereço -> Retorna Endereço Original
		if ($dados_contrato["tipo_id"] == 14) {
			$this->desmigraEnderecoCliente($dados_contrato["id"]);
		}
		
		// Finaliza Bloqueio Contrato Inativo
	 	$this->finalizaBloqueioContratoInativo($dados_contrato["id"]);
		
		// Insere Chamado Residencial (Vistoria)
		if ($dados_contrato["tipo_id"] == 11 && in_array($dados_contrato["servico_id"], array(28, 33, 34))) {
			// Instancia Classe Chamados
			$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
			$chamado_categoria_id = $this->buscaCategoriaViabilidadeResidencial($dados_contrato["cliente"]);
			$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_contrato["cliente"], $chamado_categoria_id);
			if ($chamado_id) {
			    $dados = array();
				$dados["conteudo"] = "Contrato anulado";
				$dados["sistema"] = true;
				// Registra Etapa
				$chamados->registraEtapa($chamados->buscaDadosChamado($chamado_id, true), 5, $dados);
			}
		}
		
		// Atualiza Status Cliente
		$this->atualizaStatusCliente($dados_contrato["cliente"]);

		if ($dados_contrato["tipo_id"] == 11) {
			$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);
			$clientes_iptv->removeIptv($dados_contrato["id"]);
		}
		
		// Remove Equipamentos comentarios
		$this->bancodedados->query("
			DELETE
				tab_clientes_comodatos_comentarios
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_clientes_comodatos_comentarios ON (tab_clientes_comodatos.clc_id = tab_clientes_comodatos_comentarios.clc_id)
			WHERE
				tab_clientes_comodatos.cor_id = {$dados_contrato["id"]}");

		// Remove equipamentos
		$this->bancodedados->query("DELETE FROM tab_clientes_comodatos WHERE cor_id = {$dados_contrato["id"]}");
		
		// Remove Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			$this->anularContrato($this->buscaDadosContrato($vinculo["contrato_id"]), $motivo_anulacao_id);
		}

		$query = $this->bancodedados->query("
			SELECT
				ajf_id AS id
			FROM
				tab_ajustes_financeiros
			WHERE
				cor_id = {$dados_contrato["id"]} AND
				afc_id = 19");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			// Cancela Ajuste Instalação
			$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 6 WHERE ajf_id = {$dados["id"]}");

			// Registra Comentário Ajuste
			$this->registraComentarioAjusteFinanceiro($dados["id"], "<b>Cancelado</b>: Contrato Anulado ({$dados_contrato["codigo"]})");

			// Cancela Cobrança Instalação
			$query_cobranca = $this->bancodedados->query("
				SELECT
					tab_cobrancas.coa_id AS id
				FROM
					tab_cobrancas
					INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				WHERE
					tab_cobrancas.cot_id = 1 AND
					tab_cobrancas_detalhes.ajf_id = {$dados["id"]}
				GROUP BY
					tab_cobrancas.coa_id
				HAVING
					COUNT(tab_cobrancas_detalhes.cod_id) = 1");

			while (($dados_cobranca = $this->bancodedados->resultadoArray($query_cobranca)) != false) {
				$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
				$cobrancas->cancelarCobranca($dados_cobranca["id"], "Contrato Anulado ({$dados_contrato["codigo"]})");

				$youCastDados = $this->buscarProdutoYoucast($dados_contrato["id"]);
				$codigoServico = addslashes($youCastDados["codigoServico"]);
				$codigoPlayer = $youCastDados["codigoPlayer"];
				if($codigoPlayer == 'youcast'){
					$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
					$sva_youcast->remover($dados_contrato["cliente"], $codigoServico);
					
					$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 2, envido_email = 0 WHERE contrato = {$dados_contrato["id"]}");
				}
			}

		}
		
	}

	###############
	### Remover ###
	###############

	function remover() {
		
		$this->buscaDadosItem($_GET["i"]);
	
		$servicos_ativos = $this->buscaServicosAtivos($_GET["i"]);
		
		if ($servicos_ativos) {
		    
		    $botoes = array();
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			
			$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");
			
			$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Remover&nbsp;</b></td>', $this->conteudo);
			$this->conteudo = str_replace("[erro]", "Este contrato possui ".count($servicos_ativos)." servicos(s) ativos:<br><br>".implode("<br>", $servicos_ativos)."<br>", $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&cli={$_GET["cli"]}&cor={$_GET["cor"]}", $this->conteudo);
			$this->conteudo = str_replace("[mensagem]", "Não é possível continuar com a remoção deste contrato!", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		
		} elseif ($this->dados_item) {
			
			if (!$_POST) {
				
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
			    
				$this->conteudo .= $this->carregaArquivo("contratos.remover.inc.php");
				
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Remover&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=remover&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				// Remove Contrato
				$this->removerContrato($this->dados_item);

				$youCastDados = $this->buscarProdutoYoucast($this->dados_item["id"]);
				$codigoServico = addslashes($youCastDados["codigoServico"]);
				$codigoPlayer = $youCastDados["codigoPlayer"];
				if($codigoPlayer == 'youcast'){
					$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
					$sva_youcast->remover($this->dados_item["cliente"], $codigoServico);
				
					$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 5, envido_email = 1 WHERE contrato = {$this->dados_item["id"]}");
				}
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				if ($this->dados_item["pai"]) {
					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$this->dados_item["pai"]}");
				} else {
					$this->fechar();
				}
				
			}
			
		} else {
			$this->fechar();
		}
		
	}

	########################
	### Remover Contrato ###
	########################

	function remover_contrato() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item && $this->ehRemovivelContrato()) {
			
			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.remover_contrato.inc.php");
				
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Remover Contrato&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {

				if (file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$this->dados_item["codigo"]} Telecom")))) {
					$caminho = DIR_RAIZ."contratos/".md5(sha1("Olé {$this->dados_item["codigo"]} Telecom"));
				} else if (file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$this->dados_item["codigo"]} Telecom"))))) {
					$caminho = DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$this->dados_item["codigo"]} Telecom")));
				} else if (file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$this->dados_item["codigo"]} Telecom"))))) {
					$caminho = DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$this->dados_item["codigo"]} Telecom")));
				}

				if (unlink($caminho)) {
								
					$this->bancodedados->query("
						UPDATE
							tab_contratos
						SET
							crs_id = 19
						WHERE
							cor_id = {$this->dados_item["id"]} AND
							crs_id = 20");
					
					$this->registraComentario($this->dados_item["id"], 37, $_POST["comentario"], $usuario_id);

				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}

	###################
	### Descancelar ###
	###################

	function descancelar() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {
			    
			    $botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.descancelar.inc.php");
				
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Descancelar&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {

				$this->acaoDescancelarContrato();

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}
	
	function acaoDescancelarContrato() {
				
		// Altera status equipamentos
		$this->bancodedados->query("
			UPDATE
				tab_clientes_comodatos
			SET
				ccs_id = 2,
				clc_data_termino = NULL,
				clc_data_retirada = NULL
			WHERE
				cor_id = {$this->dados_item["id"]} AND
				clc_substituto_id IS NULL");

		$contrato_status = $this->possuiContrato($this->dados_item) ? 20 : 19;

		// Atualiza Status Contrato
		
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				crs_id = {$contrato_status},
				cor_data_cancelamento = NULL,
				cor_multiservice_enviado = 0
			WHERE
				cor_id = {$this->dados_item["id"]} AND
				crs_id = 22");

		// Atualiza Status Contrato Aditivos

		for ($i = 0; $i < sizeof($this->dados_item["aditivos"]); $i++) {
			
			$contrato_status = $this->possuiContrato($this->dados_item["aditivos"][$i]) ? 20 : 19;

			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					crs_id = {$contrato_status},
					cor_data_cancelamento = NULL,
					cor_multiservice_enviado = 0
				WHERE
					cor_id = {$this->dados_item["aditivos"][$i]["id"]} AND
					crs_id = 22");

		}
		
		// Altera Solicitação de Cancelamento p/ Retido
		$this->bancodedados->query("
			UPDATE
				tab_cancelamentos
			SET
				cas_id = 3
			WHERE
				cor_id = {$this->dados_item["id"]} AND
				cas_id = 4");
		
		// Atualiza Status Assinaturas Vinculadas
		for ($i = 0; $i < sizeof($this->dados_item["assinaturas"]); $i++) {

			$this->bancodedados->query("
				UPDATE
					tab_assinaturas
				SET
					ast_id = 2,
					ass_data_cancelamento = NULL
				WHERE
					ass_id = {$this->dados_item["assinaturas"][$i]["id"]}");

			// Descancela Pontos
			$this->descancelaPontos($this->dados_item["assinaturas"][$i]["id"]);
			for ($j = 0; $j < sizeof($this->dados_item["assinaturas"][$i]["cliente_pontos"]); $j++) {
				$this->registraComentarioClientePonto($this->dados_item["assinaturas"][$i]["cliente_pontos"][$j]["id"], "<b>Ação:</b> Contrato Descancelado (".$this->dados_item["codigo"].")", $_SESSION["usuario"]);
			}

			// Descancela Autenticacoes
			$this->descancelaAutenticacoes($this->dados_item["assinaturas"][$i]["id"]);
			for ($j = 0; $j < sizeof($this->dados_item["assinaturas"][$i]["cliente_autenticacoes"]); $j++) {
				$this->registraComentarioClienteAutenticacao($this->dados_item["assinaturas"][$i]["cliente_autenticacoes"][$j]["id"], "<b>Ação:</b> Contrato Descancelado (".$this->dados_item["codigo"].")", $_SESSION["usuario"]);
			}

			// Descancela E-mails/Autenticacoes
			$this->descancelaEmails($this->dados_item["assinaturas"][$i]["id"], $this->dados_item["codigo"]);

			// Descancela Domínios
			$this->descancelaDominios($this->dados_item["assinaturas"][$i]["id"]);

			// Descancela Ajustes
			$this->descancelaServicosAjustes($this->dados_item["assinaturas"][$i]["id"]);

		}
		
		// Atualiza Status do Cliente
		$this->atualizaStatusCliente($this->dados_item["cliente"]);
		
		$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);
		$clientes_iptv->descancelaIptv($this->dados_item["id"]);
		
		// Atualiza Status Descontos Vinculados
		for ($i = 0; $i < sizeof($this->dados_item["descontos"]); $i++) {
			$this->bancodedados->query("
				UPDATE
					tab_descontos_concessoes
				SET
					dcs_id = 5
				WHERE
					dec_id = {$this->dados_item["descontos"][$i]["id"]} AND
					dcs_id = 6");
			// Registra Comentário
			$this->registraComentarioDesconto($this->dados_item["descontos"][$i]["id"],"<b>Ação:</b> Contrato Descancelado ({$this->dados_item["codigo"]})", $_SESSION["usuario"]);
		}
		
		// Registra comentário no contrato
		$this->registraComentario($this->dados_item["id"], 26, $_POST["comentario"], $_SESSION["usuario"]);
		
		// Registra comentário nos aditivos
		for ($i = 0; $i < sizeof($this->dados_item["aditivos"]); $i++) {
			$this->registraComentario($this->dados_item["aditivos"][$i]["id"], 26, $_POST["comentario"], $_SESSION["usuario"]);
		}
		
		// Atualiza da de bloqueio
		$this->atualizaDiasBloqueioCliente($this->dados_item["cliente"]);
		
		// Registra Atividade
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

		$youCastDados = $this->buscarProdutoYoucast($this->dados_item["id"]);
		$codigoServico = addslashes($youCastDados["codigoServico"]);
		$codigoPlayer = $youCastDados["codigoPlayer"];
		if($codigoPlayer == 'youcast'){
			$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
			$sva_youcast->adicionar($this->dados_item["cliente"], $codigoServico, $this->dados_item["origem"]);

			$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 1 , data = NOW() WHERE contrato = {$this->dados_item["id"]}");
		}

		for ($i = 0; $i < sizeof($this->dados_item["aditivos"]); $i++) {
			$youCastDados = $this->buscarProdutoYoucast($this->dados_item["aditivos"][$i]["id"]);
			$codigoServico = addslashes($youCastDados["codigoServico"]);
			$codigoPlayer = $youCastDados["codigoPlayer"];
			if($codigoPlayer == 'youcast'){
				$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
				$sva_youcast->adicionar($this->dados_item["cliente"], $codigoServico, $this->dados_item["origem"]);

				$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 1 , data = NOW() WHERE contrato = {$this->dados_item["aditivos"][$i]["id"]}");
			}
		}
		
	}

	############################
	### Simular Cancelamento ###
	############################

	function simular_cancelamento() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$_POST["data_cancelamento"] = $_POST["data_cancelamento"] ? $_POST["data_cancelamento"] : date("d/m/Y");

			$dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);
			$multa = $this->vigenciaAtivaCancelamento($this->dados_item, $_POST["data_cancelamento"]);

			$botoes = array();
			$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="window.location=\'?m=contratos&a=ver&i='.$this->dados_item["id"].'\'">';
			$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("contratos.simular_cancelamento.inc.php");

			$this->conteudo = str_replace("[codigo]", $this->dados_item["codigo"], $this->conteudo);
			$this->conteudo = str_replace("[cliente_nome]", $dados_cliente["nome"], $this->conteudo);
			$this->conteudo = str_replace("[telefones]", $dados_cliente["lista_telefones"], $this->conteudo);
			$this->conteudo = str_replace("[servico]", $this->dados_item["servico_nome"], $this->conteudo);
			$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
			$this->conteudo = str_replace("[data_cancelamento]", $_POST["data_cancelamento"], $this->conteudo);
			$this->conteudo = str_replace("[multa]", $this->formataDinheiro($multa), $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "./?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

		} else {
			$this->fechar();
		}

	}

	################
	### Cancelar ###
	################

	function cancelar() {

		$this->buscaDadosItem($_GET["i"]);

		if (($cobrancas = $this->buscaCobrancasAtrasadas($this->dados_item["cliente"])) && (!$this->permissoes["contratos"]["gerenciar_inadimplentes"] || !$_POST)) {

			$botoes = array();
		    if ($this->permissoes["contratos"]["gerenciar_inadimplentes"]) $botoes[] = '<input type="submit" name="sim" class="buttonAzulEscuroBranco11" value="Sim">';
		    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
		    $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");

			$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Cancelar&nbsp;</b></td>', $this->conteudo);
			$this->conteudo = str_replace("[erro]", "Este cliente possui $cobrancas cobrança(s) em atraso.<br>", $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
		    $this->conteudo = str_replace("[mensagem]", $this->permissoes["contratos"]["gerenciar_inadimplentes"] ? "Você deseja continuar com o cancelamento deste contrato?" : "Não é possível continuar com o cancelamento deste contrato!<br>Entre em contato com o Setor Financeiro!", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		} elseif ($this->possuiAditivoPendente($_GET["i"])) {

		    $botoes = array();
		    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
		    $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

			$this->conteudo .= $this->carregaArquivo("contratos.erros.inc.php");

			$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Cancelar&nbsp;</b></td>', $this->conteudo);
			$this->conteudo = str_replace("[erro]", "Este cliente possui um contrato aditivo em aberto.<br>", $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$_GET["a"]}&i={$_GET["i"]}", $this->conteudo);
			$this->conteudo = str_replace("[mensagem]", "Não é possível continuar com o cancelamento deste contrato!<br>Remova ou ative o contrato pendente primeiro.", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);

		} else {

			if ($this->dados_item) {

				if (!$_POST["data_cancelamento"]) {

					$botoes = array();
					$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
					$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
					$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';

					$this->conteudo .= $this->carregaArquivo("contratos.cancelar.inc.php");

					$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Cancelar&nbsp;</b></td>', $this->conteudo);
					$this->conteudo = str_replace("[data_cancelamento]", date("d/m/Y"), $this->conteudo);
					$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
					$this->conteudo = str_replace("[exibir_mensagem_vigencia]", $this->dados_item["vigencia_ativa"] ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
					$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
					$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

				} else {

					$this->acaoCancelarContrato($this->dados_item, $_POST["data_cancelamento"], $_POST["comentario"], $_SESSION["usuario"]);

					$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
					$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

					header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

				}

			} else {
				$this->fechar();
			}

		}

	}

	##############################
	### Ação Cancelar Contrato ###
	##############################

	function acaoCancelarContrato($dados_contrato, $data_cancelamento, $comentario, $usuario_id = null, $remover_venda = false) {
		
		// Gera Ajuste Financeiro Ref. Multa Rescisão

		if (($multa = $this->vigenciaAtivaCancelamento($dados_contrato, $data_cancelamento)) != false) {

			$competencia = $this->buscaCobrancasVencimentoAtual($dados_contrato["cliente"]) ? date("Y-m-01", strtotime("+1 month")) : date("Y-m-01");

			$this->bancodedados->query("
				INSERT INTO
					tab_ajustes_financeiros
				SET
					ajf_datahora = NOW(),
					cli_id = {$dados_contrato["cliente"]},
					cor_id = {$dados_contrato["id"]},
					afc_id = 10,
					aft_id = 1,
					ajf_parcelas = 1,
					ajf_valor = $multa,
					ajf_valor_total = $multa,
					ajf_valor_tipo = 1,
					ajf_competencia_inicio = '$competencia',
					ajf_competencia_termino = '$competencia',
					afs_id = 5");

			$ajuste_id = $this->bancodedados->ultimoIdInserido();

			$this->registraComentarioAjusteFinanceiro($ajuste_id, "Multa de rescisão referente ao contrato <b>".$dados_contrato["codigo"]."</b>");

		}

		$data_cancelamento = substr($data_cancelamento, 6, 4)."-".substr($data_cancelamento, 3, 2)."-".substr($data_cancelamento, 0, 2);

		if (strtotime($data_cancelamento) <= time()) {

			// Cancela Assinaturas
			foreach ($dados_contrato["assinaturas"] as $assinatura) {

				$this->bancodedados->query("
					UPDATE
						tab_assinaturas
					SET
						ass_data_cancelamento = '$data_cancelamento',
						ast_id = 3
					WHERE
						ass_id = {$assinatura["id"]}");

				while ($assinatura && $assinatura["migrada"]) {

					$assinatura = $this->buscaDadosAssinatura($assinatura["migrada"]);

					$this->bancodedados->query("
						UPDATE
							tab_assinaturas
						SET
							ass_data_cancelamento = '$data_cancelamento'
						WHERE
							ass_id = {$assinatura["id"]} AND
							ass_data_cancelamento > '$data_cancelamento'");

				}

			}

			// Cancela Contrato
			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_data_cancelamento = '$data_cancelamento',
					crs_id = 22
				WHERE
					cor_id = {$dados_contrato["id"]}");

			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_data_cancelamento = '$data_cancelamento',
					crs_id = 22
				WHERE
					cor_pai = {$dados_contrato["id"]}");

			// Efetua procedimentos de cancelamento definitivo
			$this->cancelarContrato($dados_contrato["id"], $data_cancelamento, $usuario_id, $dados_contrato, $comentario, $remover_venda);

		} else {

			// Cancela Assinaturas
			foreach ($dados_contrato["assinaturas"] as $assinatura) {

				$this->bancodedados->query("
					UPDATE
						tab_assinaturas
					SET
						ass_data_cancelamento = '$data_cancelamento',
						ast_id = 5
					WHERE
						ass_id = {$assinatura["id"]}");

				while ($assinatura && $assinatura["migrada"]) {

					$assinatura = $this->buscaDadosAssinatura($assinatura["migrada"]);

					$this->bancodedados->query("
						UPDATE
							tab_assinaturas
						SET
							ass_data_cancelamento = '$data_cancelamento'
						WHERE
							ass_id = {$assinatura["id"]} AND
							ass_data_cancelamento > '$data_cancelamento'");

				}

			}

			// Cancela Contrato
			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_data_cancelamento = '$data_cancelamento',
					crs_id = 21
				WHERE
					cor_id = {$dados_contrato["id"]}");

			$this->bancodedados->query("
				UPDATE
					tab_contratos
				SET
					cor_data_cancelamento = '$data_cancelamento',
					crs_id = 21
				WHERE
					cor_pai = {$dados_contrato["id"]}");

			// Registra Comentário Contrato Principal
			$this->registraComentario($dados_contrato["id"], 23, "<b>Data:</b> ".date("d/m/Y", strtotime($data_cancelamento)).($comentario ? "<br><b>Comentário:</b> $comentario" : ""), $usuario_id);

			// Registra Comentário Contratos Aditivos
			for ($i = 0; $i < sizeof($dados_contrato["aditivos"]); $i++) {
				$this->registraComentario($dados_contrato["aditivos"][$i]["id"], 23, $comentario, $usuario_id);
			}

		}

		// Corrige Data
		$data_cancelamento = substr($data_cancelamento, 8, 2)."/".substr($data_cancelamento, 5, 2)."/".substr($data_cancelamento, 0, 4);
		
		// Cancela Contratos Vinculados - Obrigatório (Dependente)
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			$vinculos_pai = $this->buscaContratosVinculosPai($vinculo["contrato_id"]);
			if ($vinculo["vinculo_tipo_id"] == 3 || ($vinculo["vinculo_tipo_id"] == 2 && count($vinculos_pai == 1))) {
				$this->acaoCancelarContrato($this->buscaDadosContrato($vinculo["contrato_id"]), $data_cancelamento, $comentario, $usuario_id, $remover_venda);
			}
		}
		
		// Cancela Contratos Auto-Canceláveis
		$contratos_auto_cancelaveis = $this->buscaContratosAutoCancelaveis($dados_contrato["cliente"]);
		if ($contratos_auto_cancelaveis) {
			foreach ($contratos_auto_cancelaveis as $contrato_auto_cancelavel_id) {
				$this->acaoCancelarContrato($this->buscaDadosContrato($contrato_auto_cancelavel_id), $data_cancelamento, $comentario, $usuario_id, $remover_venda);
			}
		}

		// Abre chamado Financeiro
		$this->abreChamadoFinanceiro($dados_contrato);

		$youCastDados = $this->buscarProdutoYoucast($dados_contrato["id"]);
		$codigoServico = addslashes($youCastDados["codigoServico"]);
		$codigoPlayer = $youCastDados["codigoPlayer"];
		if($codigoPlayer == 'youcast'){
			$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
			$sva_youcast->remover($dados_contrato["cliente"], $codigoServico);

			$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 2, envido_email = 0 WHERE contrato = {$dados_contrato["id"]}");
		}
	}

	function cancelarContrato($contrato_id, $data_cancelamento = null, $usuario_id = null, $dados_contrato = null, $comentario = null, $remover_venda = false) {

		if ($dados_contrato || ($dados_contrato = $this->buscaDadosContrato($contrato_id, true))) {

			$data_cancelamento = $data_cancelamento ? $data_cancelamento : substr($dados_contrato["data_cancelamento"], 6, 4)."-".substr($dados_contrato["data_cancelamento"], 3, 2)."-".substr($dados_contrato["data_cancelamento"], 0, 2);
			
			// Altera Status Contrato
			$this->bancodedados->query("UPDATE tab_contratos SET crs_id = 22 WHERE cor_id = $contrato_id");
			$this->bancodedados->query("UPDATE tab_contratos SET crs_id = 22 WHERE cor_pai = $contrato_id");
			
			// Registra Comentário no Contrato
			$this->registraComentario($contrato_id, 25, $comentario, $usuario_id);
			
			// Cancela Descontos
			$this->cancelaDescontos($contrato_id);
			
			// Cancela Assinaturas
			foreach ($dados_contrato["assinaturas"] as $assinatura) {
				if ($remover_venda) {
					$this->removeVenda($assinatura["id"]);
				}
				$this->cancelaAssinatura($assinatura["id"], $data_cancelamento);
				$this->cancelaAjustesFinanceiros($assinatura["id"], $data_cancelamento);
				$this->cancelaPontos($assinatura["id"], $dados_contrato["cliente"], $dados_contrato["codigo"]);
				$this->cancelaAutenticacoes($assinatura["id"], $dados_contrato["cliente"], $dados_contrato["codigo"]);
				$this->cancelaEmails($assinatura["id"], $dados_contrato["assinaturas"], $dados_contrato["cliente"], $dados_contrato["codigo"], $usuario_id);
				$this->cancelaDominios($assinatura["id"]);
			}
						
			// Cancela Comodatos

			foreach ($dados_contrato["assinaturas"] as $assinatura) {
				$this->cancelaComodato($assinatura["id"], $data_cancelamento, $dados_contrato);
			}
			
			/*
			$this->bancodedados->query("
				UPDATE 
					tab_clientes_comodatos 
				SET 
					clc_data_termino = '{$data_cancelamento}',
					clc_data_retirada = '{$data_cancelamento}',
					ccs_id = 5
				WHERE 
					cor_id = {$contrato_id} AND
					ccs_id IN (1, 2, 4)");
			*/
			
			// Cancela IPTV
			
			$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);
			$clientes_iptv->cancelaIptv($contrato_id);
			
			// Cancela Serviços Ajustes
			$this->cancelaServicosAjustes($dados_contrato["cliente"]);
			
			// Registra comentário nos aditivos
			for ($i = 0; $i < sizeof($dados_contrato["aditivos"]); $i++) {
				$this->registraComentario($dados_contrato["aditivos"][$i]["id"], 25, "Contrato Principal Cancelado", $usuario_id);
			}
			
			// Registra comentário nos descontos
			for ($i = 0; $i < sizeof($dados_contrato["descontos"]); $i++) {
				$this->registraComentarioDesconto($dados_contrato["descontos"][$i]["id"], "<b>Ação:</b> Contrato Cancelado (".$dados_contrato["codigo"].")", $usuario_id);
			}
			
			// Registra comentário ajuste financeiro
			for ($i = 0; $i < sizeof($dados_contrato["assinaturas"]); $i++) {
				for ($j = 0; $j < sizeof($dados_contrato["assinaturas"][$i]["ajustes_financeiros"]); $j++) {
					$this->registraComentarioAjusteFinanceiro($dados_contrato["assinaturas"][$i]["ajustes_financeiros"][$j]["id"], "<b>Ação:</b> Contrato Cancelado (".$dados_contrato["codigo"].")", $usuario_id);
				}
			}
			
			// Registra comentário nas visitas
			for ($i = 0; $i < sizeof($dados_contrato["visitas"]); $i++) {
				$this->registraComentarioVisita($dados_contrato["visitas"][$i]["id"], "<b>Ação</b>: Contrato Cancelado (".$dados_contrato["codigo"].")", 18, $usuario_id);
			}
			
			// Atualiza Status do Cliente
			if ($this->atualizaStatusCliente($dados_contrato["cliente"]) == 3) {

				// Cancela Ajustes S/ Validade Se Todos os Contratos Forem Cancelados
				$ajustes = $this->buscaAjustesSemValidade($dados_contrato["cliente"]);

				if ($ajustes) {

					$termino = substr($data_cancelamento, 0, 8)."01";

					foreach ($ajustes as $ajuste_id) {

						// Cancela Ajuste
						$this->bancodedados->query("
							UPDATE
								tab_ajustes_financeiros
							SET
								afs_id = 8,
								ajf_competencia_termino = '$termino'
							WHERE
								ajf_id = $ajuste_id");

						// Registra Comentário
						$this->registraComentarioAjusteFinanceiro($ajuste_id, "<b>Ação:</b> Cliente Cancelado", $usuario_id);

					}

				}

				// Cancela Ajustes Programados Aguardando
				$this->bancodedados->query("
					UPDATE
						tab_servicos_ajustes
					SET
						sas_id = 4
					WHERE
						cli_id = {$dados_contrato["cliente"]} AND
						sas_id IN (1, 2, 3)");

				// Atualiza Data Bloqueio
				$this->bancodedados->query("
					UPDATE
						tab_clientes
					SET
						cli_bloqueio_dias = NULL,
						cli_bloqueio_dias_customizado = 0
					WHERE
						cli_id = {$dados_contrato["cliente"]}");

			}
			
			// Remove Vinculos
			$this->bancodedados->query("DELETE FROM tab_contratos_vinculos WHERE cor_id = {$dados_contrato["id"]}");
			$this->bancodedados->query("DELETE FROM tab_contratos_vinculos WHERE cor_filho = {$dados_contrato["id"]}");
			
		}
		
	}

	###########################
	### Anular Cancelamento ###
	###########################

	function anular_cancelamento() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.anular_cancelamento.inc.php");
				
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Anular Cancelamento&nbsp;</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[vigencia]", $this->dados_item["prazo_vigencia"], $this->conteudo);
				$this->conteudo = str_replace("[exibir_mensagem_vigencia]", $this->dados_item["vigencia_ativa"] ? ((strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false) ? "block" : "table-row-group") : "none", $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$this->anularCancelamentoContrato($this->dados_item);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
				
			}
			
		} else {
			$this->fechar();
		}
		
	}

	#######################
	### Anular Ativação ###
	#######################

	function anular_ativacao() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if ($this->dados_item) {
			
			if (!$_POST) {

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.anular_ativacao.inc.php");
				
				$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><b>&nbsp;Anular Ativação</b></td>', $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);
			
			} else {
				
				$this->bancodedados->query("
					UPDATE
						tab_contratos
					SET
						cor_data_ativacao = NULL,
						crs_id = 17
					WHERE
						cor_id = {$_GET["i"]}");
				
				// Registra comentário no contrato
				$this->registraComentario($_GET["i"], 17, $_POST["comentario"], $_SESSION["usuario"]);
				
				// Registra Atividade
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $_GET["i"]);

				$youCastDados = $this->buscarProdutoYoucast($dados_contrato["id"]);
				$codigoServico = addslashes($youCastDados["codigoServico"]);
				$codigoPlayer = $youCastDados["codigoPlayer"];
				if($codigoPlayer == 'youcast'){
					$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
					$sva_youcast->remover($this->dados_item["cliente"], $codigoServico);

					$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 2, envido_email = 0 WHERE contrato = {$_GET["i"]}");
				}

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
			}
			
		} else {
			$this->fechar();
		}

	}

	###################
	### Sincronizar ###
	###################

	function sincronizar() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			if (!$_POST) {
				
			    $dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente"]);

				$botoes = array();
				$botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
				$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
				$botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
				
				$this->conteudo .= $this->carregaArquivo("contratos.sincronizar.inc.php");

				$this->conteudo = str_replace("[contrato]", $this->dados_item["codigo"], $this->conteudo);
				$this->conteudo = str_replace("[cliente]", $dados_cliente["nome"], $this->conteudo);
				$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
				$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a={$this->dados_acao["nome"]}&i={$this->dados_item["id"]}", $this->conteudo);

			} else {

				$this->sincronizarContrato($this->dados_item, $_POST["comentario"], $_SESSION["usuario"]);
				
				/**
				 * @var atividades $atividades
				 */
				$atividades = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividades->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);

				header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");

			}

		} else {
			$this->fechar();
		}
		
	}

	############################
	### Sincronizar Contrato ###
	############################

	function sincronizarContrato($dados_contrato, $comentario = null, $usuario_id = null) {

		$dados_cliente = $this->buscaDadosCliente($dados_contrato["cliente"]);
		$dados_servico = $this->buscaDadosServico($dados_contrato["servico_id"]);
		$contrato_tipo = $this->buscaDadosTipo($dados_contrato["tipo_id"]);

		if ($dados_contrato["tipo_id"] == 11) { // Principal

			$parcelamento_maximo = $this->buscaParcelamentoMaximo($dados_contrato["servico_id"]);
			$parcelamento = $dados_contrato["parametros"]["parcelamento"] > $parcelamento_maximo ? $parcelamento_maximo : $dados_contrato["parametros"]["parcelamento"];
			$parcelamento_forma = $dados_contrato["parametros"]["parcelamento_forma"];
			$vigencia = ($this->contratoVigenciaAlterada($dados_contrato["id"])) ? $dados_contrato["parametros"]["contrato_prazo_vigencia"] : 0;
			$contrato_parametros = $this->geraContratoPrincipalParametros($dados_contrato["cliente"], $dados_contrato["assinaturas"], $dados_contrato["descontos"], $vigencia, $dados_contrato["parametros"]["contrato_versao"], $parcelamento, $parcelamento_forma);

		} elseif ($dados_contrato["tipo_id"] == 12) { // Alteração Assinaturas

			// Busca Dados Assinaturas / Descontos Adicionados
			$contrato_parametros = $this->geraContratoAditivoAlterarAssinaturasParametros($this->buscaDadosUltimoContrato($dados_contrato["cliente"], $dados_contrato["pai"], $dados_contrato["id"]), $dados_contrato["assinaturas"], $dados_contrato["descontos"], $dados_contrato["parametros"]["contrato_versao"]);

		} elseif ($dados_contrato["tipo_id"] == 13) { // Migração Assinaturas

			// Busca Dados Assinaturas / Descontos Adicionados
			$contrato_parametros = $this->geraContratoAditivoAlterarAssinaturasParametros($this->buscaDadosUltimoContrato($dados_contrato["cliente"], $dados_contrato["pai"], $dados_contrato["id"]), $dados_contrato["assinaturas"], $dados_contrato["descontos"], $dados_contrato["parametros"]["contrato_versao"]);

		} elseif ($dados_contrato["tipo_id"] == 14) { // Mudança Endereço

			// Desmigra Endereço
			$this->desmigraEnderecoCliente($dados_contrato["id"]);
			// Finaliza Ajuste Contrato Inativo
			$this->finalizaBloqueioContratoInativo($dados_contrato["pai"]);
			// Atualiza Parâmetros
			$dados_contrato_pai = $this->buscaDadosUltimoContrato($dados_contrato["cliente"], $dados_contrato["pai"], $dados_contrato["id"]);
			$contrato_parametros = $this->geraContratoAditivoMudancaEnderecoParametros($dados_contrato["cliente"], $dados_contrato["assinaturas"], $dados_contrato["descontos"], $dados_contrato["parametros"]);

		} elseif ($dados_contrato["tipo_id"] == 15) { // Mudança Titularidade

			$dados_contrato_pai = $this->buscaDadosUltimoContrato($dados_contrato["cliente"], $dados_contrato["pai"], $dados_contrato["id"]);
			$contrato_parametros = $this->geraContratoAditivoMudancaTitularidadeParametros($dados_contrato["cliente"], $dados_contrato_pai["assinaturas"], $dados_contrato_pai["descontos"], $dados_contrato["parametros"]);

		}

		// Regera Venda
		if ($dados_contrato["tipo_id"] != 14) {
			foreach ($dados_contrato["assinaturas"] as $assinatura) {
				$this->geraVenda($dados_cliente, $assinatura, $dados_contrato["id"]);
			}
		}
		if ($dados_contrato["tipo_id"] == 11) { // Principal
			$cor_vigencia = $contrato_parametros["contrato_prazo_vigencia"] ? $contrato_parametros["contrato_prazo_vigencia"] : "NULL";
			$cor_multa = $contrato_parametros["contrato_multa_rescisao"] ? preg_replace("/[^0-9]/", "", $contrato_parametros["contrato_multa_rescisao"]) : "NULL";
		} else {
			$cor_vigencia = $dados_contrato["vigencia"] ? $dados_contrato["vigencia"] : "NULL";
			$cor_multa = $dados_contrato["multa"] ? $dados_contrato["multa"] : "NULL";
		}

		if ($dados_contrato["status_id"] < 19) {

			if (!$this->ehAutoAprovavelFinanceiro($dados_contrato)) {
				// Em Aberto (Financeiro, Aguardando Aprovação)
				$status_id = 11;
			} elseif ($this->ehAutoReprovavelFinanceiro($dados_cliente)) {
				// Em Aberto (Financeiro, Reprovado)
				$status_id = 12;
			} elseif (!$this->ehAutoAprovavelComercial($contrato_tipo["parametros"], $dados_servico["parametros"])) {
				// Em Aberto (Comercial, Aguardando Aprovação)
				$status_id = 13;
			} elseif (!$this->ehAutoAprovavelTecnico($dados_cliente, $contrato_tipo["parametros"], $dados_contrato["servico_id"], $dados_servico["parametros"], $contrato_parametros, $contrato_tipo["id"])) {
				// Em Aberto (Técnico, Aguardando Aprovação)
				$status_id = 15;
			} else {
				// Em Aberto (Aguardando Ativação)
				$status_id = 17;
			}

		} else {
			$status_id = $dados_contrato["status_id"];
		}

		$status_id = $dados_contrato["status_id"];

		// Atualiza Contrato
		$contrato_parametros = addslashes(serialize($contrato_parametros));

		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				cor_sincronizado = 1,
				crs_id = $status_id,
				cor_parametros = '$contrato_parametros',
				cor_vigencia = $cor_vigencia,
				cor_multa = $cor_multa
			WHERE
				cor_id = {$dados_contrato["id"]}");

		// Registra comentário no contrato
		$this->registraComentario($dados_contrato["id"], 20, $comentario, $usuario_id);

		if ($status_id != $dados_contrato["status_id"]) {
			if ($status_id == 17) {
				if ($dados_cliente["origem_id"] == 1 && !$dados_contrato["vinculos_pai"] && $dados_contrato["servico_parametros"]["gera_visita"]) {
					$visita_id = $this->geraVisita($dados_cliente["id"], $dados_contrato["id"]);
					$this->registraComentarioVisita($visita_id, "<b>Contrato Aprovado</b>: {$dados_contrato["codigo"]}", 16, $_SESSION["usuario"]);
				}
			} else {
				// Cancela Visitas
				foreach ($dados_contrato["visitas"] as $visita) {
					$this->bancodedados->query("
						UPDATE
							tab_visitas
						SET
							via_id = 4
						WHERE
							vis_id = {$visita["id"]}");
					$this->registraComentarioVisita($visita["id"], "<b>Cancelada</b>: Contrato sincronizado.</b>", 18, $_SESSION["usuario"]);
				}
			}
		}

		// Sincroniza Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			if (!$vinculo["sincronizado"]) {
				$this->sincronizarContrato($this->buscaDadosContrato($vinculo["contrato_id"]), $comentario, $usuario_id);
			}
		}

	}

	#######################
	### Baixar Contrato ###
	#######################

	function baixar_contrato() {
		
		$this->buscaDadosItem($_GET["i"]);
		
		if (file_exists(DIR_RAIZ."contratos/".md5(sha1("Olé {$this->dados_item["codigo"]} Telecom")))) {
		
			header("Content-type: application/pdf;");
			header("Content-Disposition: attachment; filename=contrato_".str_replace("/", "_", $this->dados_item["codigo"]).".pdf;");

			print $this->carregaArquivo(DIR_RAIZ."contratos/".md5(sha1("Olé {$this->dados_item["codigo"]} Telecom")));
			
		} else if (file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$this->dados_item["codigo"]} Telecom"))))) {
		
			header("Content-type: application/pdf;");
			header("Content-Disposition: attachment; filename=contrato_".str_replace("/", "_", $this->dados_item["codigo"]).".pdf;");

			print $this->carregaArquivo(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olé {$this->dados_item["codigo"]} Telecom"))));
			
		} else if (file_exists(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$this->dados_item["codigo"]} Telecom"))))) {
					
			header("Content-type: application/pdf;");
			header("Content-Disposition: attachment; filename=contrato_".str_replace("/", "_", $this->dados_item["codigo"]).".pdf;");

			print $this->carregaArquivo(DIR_RAIZ."contratos/".md5(sha1(utf8_encode("Olá {$this->dados_item["codigo"]} Telecom"))));
			
		} else {
			print "Arquivo não encontrado";
		}
		
		exit();
		
	}

	###########################
	### Visualizar Contrato ###
	###########################

	function visualizar_contrato() {

		$this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {

			$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
			$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);

			print $this->geraConteudo($this->dados_item, "contrato", false, true);

			exit();

		} else {
			$this->fechar();
		}

	}

	###########################
	### Visualizar Vistoria ###
	###########################

	function visualizar_vistoria() {

		if(!empty($_GET['e']) && $_GET['e'] == "enviar" ) {
			$this->enviar_vistoria();
		} else {
			$this->buscaDadosItem($_GET["i"]);
			if ($this->dados_item) {
				$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
				$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);
				$this->dados_item['parametros']['styleAjuste'] = "
				<style>
				td.vertical-title > div.div01 {
					margin-left: -20px;
				}
				td.vertical-title > div.div02 {
					margin-left: -24px;
				}
				td.vertical-title > div.div03 {
					margin-left: -11px;
				}
				td.vertical-title > div.div04 {
					margin-left: -37px;
				}
				td.vertical-title > div.div05 {
					margin-left: -20px;
				}
				td.vertical-subtitle > div.div06 {
					margin-left: 6px;
					margin-top: -15px;
				}
				</style>";
				$conteudo = $this->geraConteudo($this->dados_item, "vistoria", false, true);
				
				print $conteudo;
				exit();

			} else {
				$this->fechar();
			}
		}
	}
        
        function loginEmail($tipo = 'boleto') {
            
            $sql = "SELECT * FROM tab_email_smtp where ativo IS TRUE AND tipo LIKE '$tipo' LIMIT 1";
            $query = $this->bancodedados->query($sql);

            return $this->bancodedados->resultadoArray($query);
    }
        
        function enviar_vistoria() {
            
            $this->buscaDadosItem($_GET["i"]);

		if ($this->dados_item) {
                    $dados_cliente = $this->buscaDadosCliente($this->dados_item["cliente_id"]);
                    if (!$_POST) {
                    
                        $botoes = array();
                        $botoes[] = '<input type="submit" name="confirmar" class="buttonAzulEscuroBranco11" value="Confirmar">';
                        $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
                        $botoes[] = '<input type="button" name="fechar" class="buttonAzulEscuroBranco11" value="Fechar" onClick="window.close()">';
                        $lista_emails = array();
                        foreach ($dados_cliente["emails"] as $email) {
                                $lista_emails[] = $email["contato_email"];
                        }
                        $lista_emails = implode("; ", $lista_emails);

                        $this->conteudo .= $this->carregaArquivo("contratos.enviar_contrato.inc.php");
                        $this->conteudo = str_replace("[acoes]", "<td bgcolor=\"#505050\"><b>&nbsp;Enviar Contrato&nbsp;</b></td>", $this->conteudo);
                        $this->conteudo = str_replace("[emails]", $lista_emails, $this->conteudo);
                        $this->conteudo = str_replace("[mensagem]", "", $this->conteudo);
                        $this->conteudo = str_replace("[display_adicionar_emails]", "style=\"display: none\"", $this->conteudo);
                        $this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
                        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&e=enviar&a=visualizar_vistoria&i={$this->dados_item["id"]}", $this->conteudo);
                        
                    } else {
			
                        $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
						$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $this->dados_item["id"]);
                        $contratoServico = "{$this->dados_item["codigo"]} - {$this->dados_item["servico_nome"]}";
                        $this->dados_item['parametros']['styleAjuste'] = "
                        <style>
                           td.vertical-title > div.div01 {
                            margin-left: -35px;
                        }
                        td.vertical-title > div.div02 {
                            margin-left: -42px;
                        }
                        td.vertical-title > div.div03 {
                            margin-left: -27px;
                        }
                        td.vertical-title > div.div04 {
                            margin-left: -57px;
                        }
                        td.vertical-title > div.div05 {
                            margin-left: -38px;
                        }
                        td.vertical-subtitle > div.div06 {
                            margin-left: 2px;
                            margin-top: -32px;
                        }
                        body, td, th, p {
                            font-size: 11pt;
                        }
						td > div.text {
							height: 16px;
						}
                        </style>";
						$conteudo =  $this->geraConteudo($this->dados_item, "vistoria", false, true);
                         
                        
                        $dompdf = new Dompdf();
                        $dompdf->loadHtml($conteudo);
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render('S');
                        $pdf = $dompdf;

                        $mail = new PHPMailer();
                        $mail->isSMTP();
                        $mail->isHTML(true);
                        $mail->SMTPAuth = true;
                        $dadosEmail = $this->loginEmail();
                        $mail->Host =  $dadosEmail['host'];
                        $mail->Username = $dadosEmail['usuario'];
                        $mail->Password = $dadosEmail['senha'];
                        $mail->SMTPSecure = $dadosEmail['seguranca'];
                        $mail->Port = $dadosEmail['porta'];
                        $mail->From = $dadosEmail['usuario'];
                        $mail->FromName = $dadosEmail['de'];
                        $mail->Subject = "Termo de adesão ao contrato de prestação de serviços {$contratoServico} - {$dadosEmail['de']}";
                        $mail->Body = "<p>Olá! ;<br /><br />Segue em anexo o Termo de adesão ao contrato de prestação de serviços - {$contratoServico}.</p>";
                        
                        sort($emails = array_unique(preg_split("/[ ,;]+/", strtolower($_POST["emails"]))));
                        for ($i = 0; $i < count($emails); $i++) {
                            $mail->addAddress($emails[$i]);
                        }
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
                        $mail->AddStringAttachment($pdf->output(),"Termo de adesão - {$contratoServico}.pdf", "base64", "application/pdf");
                        if ($mail->send()) {
                            $_POST["comentario"] = $_POST["comentario"] ? "<br><b>Comentário:</b> ".trim($_POST["comentario"]) : "";
                            $this->registraComentario($this->dados_item["id"], 38, "<b>Ação:</b> Enviado termo de adessão (vistoria) <br><b>E-mail(s):</b> ".implode(", ", $emails).$_POST["comentario"], $_SESSION["usuario"]);
                            $atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
                            $atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->acao, $_GET["i"]);
                            header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
                        } else {
                            print "<script type=\"text/javascript\">alert(\"Erro ao enviar e-mail!\\nFavor contactar o Administrador de Sistema.\"); window.location = \"./?m={$this->dados_modulo["nome"]}&a=ver_dg&i={$_GET["i"]}\";</script>";
                            die();
                        }
                        
                        header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$_GET["i"]}");
                    }
		} else {
			$this->fechar();
		}
            
        }

	#####################
	### Gera Conteudo ###
	#####################

	function geraConteudo(&$contrato, $modelo, $via_unica = false, $anexar_vinculos = false) {

		// Carrega Modelo
		$arquivo = $this->buscaArquivoContrato($contrato, $modelo);

		if ($arquivo) {
			$conteudo = $this->carregaArquivo($arquivo);
		}

		if ($conteudo) {

			$contrato["parametros"]["contrato_codigo"] = $contrato["codigo"];

			$campos_numero = array("contrato_prazo_vigencia");
			for ($i = 0; $i < sizeof($campos_numero); $i++) {
				$contrato["parametros"][$campos_numero[$i]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_numero[$i]]);
			}

			if (!$contrato["parametros"]["tabela_valor_final_instalacao"]) {
				$contrato["parametros"]["parcelamento"] = "--";
				$contrato["parametros"]["forma_pagamento"] = "--";
			} elseif ($contrato["parametros"]["parcelamento"] === 0) {
				$contrato["parametros"]["parcelamento"] = "-";
				$contrato["parametros"]["forma_pagamento"] = "Boleto Bancário";
			} else {
				$contrato["parametros"]["parcelamento"] = $contrato["parametros"]["parcelamento"]." x ".$this->formataDinheiro($contrato["parametros"]["tabela_valor_parcela"]);
				$contrato["parametros"]["forma_pagamento"] = $contrato["parametros"]["parcelamento_forma"] == 1 ? "Boleto Bancário" : "Dinheiro/Cartão";
			}
			
			$campos_valor = array("tabela_valor_desconto_fidelidade_instalacao", "tabela_valor_original_instalacao", "tabela_valor_original_mensalidade", "tabela_valor_desconto_bonificacao_mensalidade", "tabela_valor_final_mensalidade", "tabela_valor_final_instalacao", "tabela_valor_parcela");
			for ($i = 0; $i < sizeof($campos_valor); $i++) {
				$contrato["parametros"][$campos_valor[$i]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_valor[$i]]/100, true);
				$contrato["parametros"][$campos_valor[$i]."_formatado"] = $this->formataDinheiro($contrato["parametros"][$campos_valor[$i]]);
			}

			// Campos gerais
			foreach ($contrato["parametros"] as $campo_nome => $campo_valor) {
				$conteudo = str_replace("[$campo_nome]", $campo_valor, $conteudo);
			}

			include_once DIR_GLOBAL."phpqrcode/qrlib.php";

			$conteudo = str_replace("[qr_code]", QRcode::svg($contrato["hash"]), $conteudo);

			$conteudo = preg_replace("/\\[[^\\]]*\\]/", "", $conteudo);
			
		} else {
			$conteudo = "<script type=\"text/javascript\">alert('Não existe o layout desse tipo de contrato/vistoria na Intranet.\\nFavor entrar em contato com o setor de Desenvolvimento.');window.close();</script>";
		}

		return $conteudo;

	}

	function geraConteudoParametrosAditivoMudancaEndereco(&$contrato) {

		// Trata campos contrato
		$contrato["pai"] = $this->buscaDadosContrato($contrato["pai"]);
		$contrato["parametros"]["contrato_codigo"] = $contrato["codigo"];
		$contrato["parametros"]["contrato_tipo"] = $contrato["tipo_id"];
		$contrato["parametros"]["contrato_servico"] = $contrato["pai"]["servico_id"];
		$contrato["parametros"]["contrato_data_geracao_dia"] = substr($contrato["data_geracao"], 0, 2);
		$contrato["parametros"]["contrato_data_geracao_mes"] = substr($contrato["data_geracao"], 3, 2);
		$contrato["parametros"]["contrato_data_geracao_ano"] = substr($contrato["data_geracao"], 6, 4);
		$contrato["parametros"]["contrato_pai_codigo"] = $contrato["pai"]["codigo"];
		$contrato["parametros"]["contrato_pai_data_geracao"] = $contrato["pai"]["parametros"]["data_geracao"];
		$contrato["parametros"]["contrato_multa_rescisao"] = $contrato["pai"]["parametros"]["contrato_multa_rescisao"];
		$contrato["parametros"]["contrato_prazo_vigencia"] = $contrato["pai"]["parametros"]["contrato_prazo_vigencia"];
		$contrato["parametros"]["provedor_logo"] = "logo_ole";
		$contrato["parametros"]["contrato_prazo_carencia"] = $contrato["parametros"]["contrato_prazo_carencia"] ? $contrato["parametros"]["contrato_prazo_carencia"] : CONTRATO_PRAZO_CARENCIA;
		
		// Trata campos meses
		$meses = array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
		$campos_meses = array ("contrato_data_geracao_mes");
		for ($i = 0; $i < sizeof($campos_meses); $i++) {
			$contrato["parametros"][$campos_meses[$i]."_extenso"] = $meses[intval($contrato["parametros"][$campos_meses[$i]])-1];
		}
		
		// Trata campos valor
		$campos_valor = array("contrato_custo_hub", "contrato_custo_placa_de_rede", "contrato_multa_rescisao", "contrato_valor_instalacao");
		for ($i = 0; $i < sizeof($campos_valor); $i++) {
			$contrato["parametros"][$campos_valor[$i]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_valor[$i]]/100, true);
			$contrato["parametros"][$campos_valor[$i]."_formatado"] = $this->formataDinheiro($contrato["parametros"][$campos_valor[$i]]);
		}

		// Trata campos número
		$campos_numero = array("contrato_prazo_carencia", "contrato_prazo_vigencia", "contrato_servico_pontos_quantidade");
		for ($i = 0; $i < sizeof($campos_numero); $i++) {
			$contrato["parametros"][$campos_numero[$i]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_numero[$i]]);
		}
		
		// Endereço Inicial
		foreach ($contrato["parametros"]["inicial"] as $chave => $valor) {
			$contrato["parametros"]["inicial_$chave"] = $valor;
		}
		
		// Endereço Inicial Cobrança
		if ($contrato["parametros"]["inicial"]["endereco_cobranca"]) {
			foreach ($contrato["parametros"]["inicial"]["endereco_cobranca"] as $chave => $valor) {
				$contrato["parametros"]["inicial_cobranca_$chave"] = $valor;
			}
		}
		
		// Endereço Final
		foreach ($contrato["parametros"]["final"] as $chave => $valor) {
			$contrato["parametros"]["final_$chave"] = $valor;
		}

	}

	function geraConteudoParametrosAditivoMudancaTitularidade(&$contrato) {

		// Trata campos contrato
		$contrato["pai"] = $this->buscaDadosContrato($contrato["pai"]);
		$contrato["parametros"]["contrato_codigo"] = $contrato["codigo"];
		$contrato["parametros"]["contrato_tipo"] = $contrato["tipo_id"];
		$contrato["parametros"]["contrato_servico"] = $contrato["servico_id"];
		$contrato["parametros"]["contrato_data_geracao_dia"] = substr($contrato["data_geracao"], 0, 2);
		$contrato["parametros"]["contrato_data_geracao_mes"] = substr($contrato["data_geracao"], 3, 2);
		$contrato["parametros"]["contrato_data_geracao_ano"] = substr($contrato["data_geracao"], 6, 4);
		$contrato["parametros"]["contrato_pai_codigo"] = $contrato["pai"]["codigo"];
		$contrato["parametros"]["contrato_pai_data_geracao"] = $contrato["pai"]["data_geracao"];
		$contrato["parametros"]["provedor_logo"] = "logo_ole";
		
		// Trata campos meses
		$meses = array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
		$campos_meses = array ("contrato_data_geracao_mes");
		for ($i = 0; $i < sizeof($campos_meses); $i++) {
			$contrato["parametros"][$campos_meses[$i]."_extenso"] = $meses[intval($contrato["parametros"][$campos_meses[$i]])-1];
		}
		
		// Dados Cliente Inicial
		foreach ($contrato["parametros"]["inicial"] as $chave => $valor) {
			$contrato["parametros"]["inicial_$chave"] = $valor;
		}
		
		// Endereço Cobrança Inicial
		foreach ($contrato["parametros"]["inicial"]["endereco_cobranca"] as $chave => $valor) {
			$contrato["parametros"]["inicial_cobranca_$chave"] = $valor;
		}
		$contrato["parametros"]["inicial_cobranca_complemento"] = $this->complemento_tipos[$contrato["parametros"]["inicial_cobranca_complemento_id"]]." ".$contrato["parametros"]["inicial_cobranca_complemento"];
		
		// Telefones Inicial
		$inicial_telefones = array();
		foreach ($contrato["parametros"]["inicial"]["telefones"] as $chave => $valor) {
			$inicial_telefones[] = "({$valor["contato_ddd"]}) {$valor["contato_numero"]}".($valor["contato_ramal"] ? " (Ramal {$valor["contato_ramal"]}" : "");
		}
		$contrato["parametros"]["inicial_telefones"] = implode(" / ", $inicial_telefones);
		
		// Dados Cliente Final
		foreach ($contrato["parametros"]["final"] as $chave => $valor) {
			$contrato["parametros"]["final_$chave"] = $valor;
		}
		
		// Endereço Cobrança Inicial
		foreach ($contrato["parametros"]["final"]["endereco_cobranca"] as $chave => $valor) {
			$contrato["parametros"]["final_cobranca_$chave"] = $valor;
		}
		$contrato["parametros"]["final_cobranca_complemento"] = $this->complemento_tipos[$contrato["parametros"]["final_cobranca_complemento_id"]]." ".$contrato["parametros"]["final_cobranca_complemento"];
		
		// Telefones Final
		$final_telefones = array();
		foreach ($contrato["parametros"]["final"]["telefones"] as $chave => $valor) {
			$final_telefones[] = "({$valor["contato_ddd"]}) {$valor["contato_numero"]}".($valor["contato_ramal"] ? " (Ramal {$valor["contato_ramal"]}" : "");
		}
		$contrato["parametros"]["final_telefones"] = implode(" / ", $final_telefones);
		
		// Endereço
		foreach ($contrato["parametros"]["endereco"] as $chave => $valor) {
			$contrato["parametros"]["endereco_$chave"] = $valor;
		}

		$contrato["parametros"]["endereco_complemento"] = $this->complemento_tipos[$contrato["parametros"]["endereco_complemento_id"]]." ".$contrato["parametros"]["endereco_complemento"];

	}

	function geraConteudoParametros(&$contrato) {

	    $assinatura_id = array();

		// Trata campos contrato
		$contrato["pai"] = $this->buscaDadosContrato($contrato["pai"]);
		$contrato["parametros"]["contrato_codigo"] = $contrato["codigo"];
		$contrato["parametros"]["contrato_tipo"] = $contrato["tipo_id"];
		$contrato["parametros"]["contrato_servico"] = $contrato["servico_id"];
		$contrato["parametros"]["contrato_data_geracao_dia"] = substr($contrato["data_geracao"], 0, 2);
		$contrato["parametros"]["contrato_data_geracao_mes"] = substr($contrato["data_geracao"], 3, 2);
		$contrato["parametros"]["contrato_data_geracao_ano"] = substr($contrato["data_geracao"], 6, 4);
		$contrato["parametros"]["contrato_pai_codigo"] = $contrato["pai"]["codigo"];
		$contrato["parametros"]["contrato_pai_data_geracao"] = $contrato["pai"]["data_geracao"];
		$contrato["parametros"]["provedor_logo"] = "logo_ole";
		$contrato["parametros"]["contrato_prazo_carencia"] = $contrato["parametros"]["contrato_prazo_carencia"] ? $contrato["parametros"]["contrato_prazo_carencia"] : CONTRATO_PRAZO_CARENCIA;
		
		// Endereço
		$contrato["parametros"]["cliente_endereco"][] = $contrato["parametros"]["cliente_endereco_logradouro"];
		if ($contrato["parametros"]["cliente_endereco_numero"]) {
			$contrato["parametros"]["cliente_endereco"][] = $contrato["parametros"]["cliente_endereco_numero"];
		}
		$contrato["parametros"]["cliente_endereco"] = implode(", ", $contrato["parametros"]["cliente_endereco"]);
		$contrato["parametros"]["cliente_endereco_complemento"] = $contrato["parametros"]["cliente_endereco_complemento"].($contrato["parametros"]["cliente_endereco_bloco"] && $contrato["parametros"]["cliente_endereco_bloco"] != "Único" ? " / {$contrato["parametros"]["cliente_endereco_bloco"]}" : "");
		$contrato["parametros"]["cliente_endereco_cidade"] .= " / ".$this->buscaSiglaEstado($contrato["parametros"]["cliente_endereco_estado"]);
		
		// Trata campos meses
		$meses = array("janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
		$campos_meses = array ("contrato_data_geracao_mes", "contrato_mes_reajuste");
		for ($i = 0; $i < sizeof($campos_meses); $i++) {
			$contrato["parametros"][$campos_meses[$i]."_extenso"] = $meses[intval($contrato["parametros"][$campos_meses[$i]]) - 1];
		}
		
		// Trata campos porcentagem
		$campos_numero = array("contrato_multa_atraso");
		for ($i = 0; $i < sizeof($campos_numero); $i++) {
			$contrato["parametros"][$campos_numero[$i]."_formatado"] = intval($contrato["parametros"][$campos_numero[$i]])."%";
			$contrato["parametros"][$campos_numero[$i]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_numero[$i]])." por cento";
		}

		$sufixos = array ("", "_inicial", "_final");

		for ($j = 0; $j < sizeof($sufixos); $j++) {

			// Converte para kilo/mega/giga/tera/bytes
			$campos_unidades = array ("contrato_servico_basesdedados_capacidade", "contrato_servico_emails_capacidade", "contrato_servico_hospedagens_capacidade", "contrato_servico_hospedagens_transferencia", "contrato_servico_pontos_velocidade_download", "contrato_servico_pontos_velocidade_upload", "contrato_servico_pontos_velocidade");
			for ($i = 0; $i < sizeof($campos_unidades); $i++) {
				if (array_key_exists($campos_unidades[$i].$sufixos[$j], $contrato["parametros"]) && $contrato["parametros"][$campos_unidades[$i].$sufixos[$j]]) {
					$unidade = floor(log($contrato["parametros"][$campos_unidades[$i].$sufixos[$j]], 1024));
					$contrato["parametros"][$campos_unidades[$i].$sufixos[$j]] = $contrato["parametros"][$campos_unidades[$i].$sufixos[$j]] / pow(1024, $unidade);
					$contrato["parametros"][$campos_unidades[$i]."_unidade".$sufixos[$j]] = $this->buscaUnidades($unidade);
				}
			}
			$contrato["parametros"]["contrato_servico_quantidade$sufixos[$j]"] = $contrato["parametros"]["contrato_servico_pontos_quantidade$sufixos[$j]"];

			// Trata campos número
			$campos_numero = array("cliente_cobranca_vencimento", "contrato_prazo_carencia", "contrato_prazo_reajuste", "contrato_prazo_religamento", "contrato_prazo_vigencia", "contrato_servico_basesdedados_capacidade", "contrato_servico_basesdedados_quantidade", "contrato_servico_basesdedados_quantidade_alteracao", "contrato_servico_emails_capacidade", "contrato_servico_emails_quantidade", "contrato_servico_emails_quantidade_alteracao", "contrato_servico_hospedagens_capacidade", "contrato_servico_hospedagens_quantidade", "contrato_servico_hospedagens_quantidade_alteracao", "contrato_servico_hospedagens_transferencia", "contrato_servico_quantidade", "contrato_servico_pontos_quantidade", "contrato_servico_pontos_quantidade_alteracao", "contrato_servico_pontos_velocidade_download", "contrato_servico_pontos_velocidade_upload", "contrato_servico_pontos_velocidade", "contrato_servico_pontos_quantidade_inicial", "contrato_servico_pontos_quantidade_final");
			for ($i = 0; $i < sizeof($campos_numero); $i++) {
				if (array_key_exists($campos_numero[$i].$sufixos[$j], $contrato["parametros"])) {
					$contrato["parametros"][$campos_numero[$i].$sufixos[$j]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_numero[$i].$sufixos[$j]]);
				}
			}

			// Trata campos valor
			$campos_valor = array("contrato_multa_rescisao", "contrato_taxa_religamento", "contrato_taxa_visita_tecnica", "tabela_valor_desconto_beneficio_instalacao", "tabela_valor_desconto_beneficio_mensalidade", "tabela_valor_desconto_fidelidade_instalacao", "tabela_valor_desconto_fidelidade_mensalidade", "tabela_valor_final_instalacao", "tabela_valor_final_mensalidade", "tabela_valor_original_instalacao", "tabela_valor_original_mensalidade", "contrato_custo_hub", "contrato_custo_placa_de_rede");
			for ($i = 0; $i < sizeof($campos_valor); $i++) {
				$contrato["parametros"][$campos_valor[$i].$sufixos[$j]."_extenso"] = $this->escreveValorPorExtenso($contrato["parametros"][$campos_valor[$i].$sufixos[$j]]/100, true);
				$contrato["parametros"][$campos_valor[$i].$sufixos[$j]."_formatado"] = $this->formataDinheiro($contrato["parametros"][$campos_valor[$i].$sufixos[$j]]);
			}

			if ($contrato["parametros"]["contrato_versao"] >= 8) {

				// Tabela Assinaturas
				if (sizeof($contrato["parametros"]["contrato_assinaturas$sufixos[$j]"])) {
					$k = 1;
					foreach ($contrato["parametros"]["contrato_assinaturas$sufixos[$j]"] as $assinatura) {
						$assinatura_id[$assinatura["id"]] = $assinatura["plano_nome"]." - p".$k;
						$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "<tr>";
						$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">{$assinatura_id[$assinatura["id"]]}</td>";
						if ($assinatura["parametros"]["pontos_quantidade"] > 0) {
							$unidade = floor(log($assinatura["parametros"]["pontos_velocidade_download"], 1024));
							$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">{$assinatura["parametros"]["pontos_quantidade"]} (".$this->escreveValorPorExtenso($assinatura["parametros"]["pontos_quantidade"]).") ponto(s) de até ".(number_format($assinatura["parametros"]["pontos_velocidade_download"] / pow(1024, $unidade), 1, ",", ".")).$this->buscaUnidades($unidade)."bps</td>";
						} elseif ($assinatura["parametros"]["telefonia_linhas"] > 0) {
							$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">450min. e {$assinatura["parametros"]["telefonia_linhas"]} linha(s)</td>";
						}
						$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDinheiro($assinatura["instalacao"])." (".$this->escreveValorPorExtenso($assinatura["instalacao"] / 100, true).")</td>";
						if ($contrato["parametros"]["contrato_versao"] < 11) {
							$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDinheiro($assinatura["mensalidade"])." (".$this->escreveValorPorExtenso($assinatura["mensalidade"] / 100, true).")</td>";
							// Bonificação
							if ($contrato["tipo_id"] == 11 && $contrato["parametros"]["contrato_prazo_vigencia"] && $contrato["parametros"]["contrato_multa_rescisao"]) {
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDinheiro($contrato["parametros"]["contrato_multa_rescisao"])." (".$this->escreveValorPorExtenso($contrato["parametros"]["contrato_multa_rescisao"] / 100, true).")</td>";
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_direita\">".$contrato["parametros"]["contrato_prazo_vigencia"]." (".$this->escreveValorPorExtenso($contrato["parametros"]["contrato_prazo_vigencia"]).") mês(es)</td>";
							} elseif ($contrato["tipo_id"] == 15 && $assinatura["multa"] && $assinatura["vigencia"]) {
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDinheiro($assinatura["multa"])." (".$this->escreveValorPorExtenso($assinatura["multa"] / 100, true).")</td>";
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_direita\">{$contrato["vigencia_pai_expiracao"]}</td>";
							} elseif ($contrato["multa"] && $contrato["vigencia"]) {
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDinheiro($contrato["multa"])." (".$this->escreveValorPorExtenso($contrato["multa"] / 100, true).")</td>";
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_direita\">".$contrato["vigencia"]." (".$this->escreveValorPorExtenso($contrato["vigencia"]).") mês(es)</td>";
							} else {
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">--</td>";
								$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_direita\">--</td>";
							}
						} else {
							$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "	<td class=\"celula_centro_direita\">".$this->formataDinheiro($assinatura["mensalidade"])." (".$this->escreveValorPorExtenso($assinatura["mensalidade"] / 100, true).")</td>";
						}
						$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = "</tr>";
						$k++;
					}
					$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"] = implode("\n						", $contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"]);
				} else {
					$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"] = '<tr><td class="celula_centro" colspan="6">Nenhuma assinatura disponível</td></tr>';
				}

				// Tabela Descontos
				if (sizeof($contrato["parametros"]["contrato_descontos$sufixos[$j]"])) {
					foreach ($contrato["parametros"]["contrato_descontos$sufixos[$j]"] as $desconto) {
						if ($desconto["mensalidade"] > 0 || $contrato["parametros"]["contrato_versao"] < 11) {
							$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "<tr>";
							$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">{$assinatura_id[$desconto["assinatura"]]}</td>";
							if ($contrato["parametros"]["contrato_versao"] < 11) {
								$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">{$desconto["desconto_nome"]}</td>";
								$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDescontoValor($desconto["instalacao"]).' ('.(($desconto["instalacao"] > 1 || $desconto["instalacao"] == 0) ? $this->escreveValorPorExtenso($desconto["instalacao"] / 100, true) : $this->escreveValorPorExtenso($desconto["instalacao"] * 100)." por cento").')</td>';
							}
							$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$this->formataDescontoValor($desconto["mensalidade"]).' ('.(($desconto["mensalidade"] > 1 || $desconto["mensalidade"] == 0) ? $this->escreveValorPorExtenso($desconto["mensalidade"] / 100, true) : $this->escreveValorPorExtenso($desconto["mensalidade"] * 100)." por cento").')</td>';
							$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "	<td class=\"celula_centro_esquerda\">".$desconto["validade"].'</td>';
							$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "	<td class=\"celula_centro_direita\">".$desconto["desconto_tipo_nome"].'</td>';
							$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = "</tr>";
						}
					}
					$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"] = implode("\n						", $contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"]);
				} else {
					$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"] = '<tr><td class="celula_centro_direita" style="text-align:center" colspan="6">Nenhum desconto disponível</td></tr>';
				}

			} else {

				// Tabela Assinaturas
				if (sizeof($contrato["parametros"]["contrato_assinaturas$sufixos[$j]"])) {
					$k = 1;
					foreach ($contrato["parametros"]["contrato_assinaturas$sufixos[$j]"] as $assinatura) {
						$assinatura_id[$assinatura["id"]] = $assinatura["plano_nome"].($assinatura["sistema_nome"] ? " - ".$assinatura["sistema_nome"] : "")." - p".$k;
						$unidade = floor(log($assinatura["parametros"]["pontos_velocidade_download"], 1024));
						$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"][] = '<td style="text-align:left;vertical-align:middle">'.$assinatura_id[$assinatura["id"]].'</td><td style="text-align:left;vertical-align:middle">'.$assinatura["parametros"]["pontos_quantidade"].' ('.$this->escreveValorPorExtenso($assinatura["parametros"]["pontos_quantidade"]).') ponto(s) de até '.($assinatura["parametros"]["pontos_velocidade_download"] / pow(1024, $unidade)).' ('.$this->escreveValorPorExtenso($assinatura["parametros"]["pontos_velocidade_download"] / pow(1024, $unidade)).') '.$this->buscaUnidades($unidade).'bps</td><td style="text-align:center;vertical-align:middle">'.$this->formataDinheiro($assinatura["instalacao"]).' ('.$this->escreveValorPorExtenso($assinatura["instalacao"] / 100, true).')</td><td style="text-align:center;vertical-align:middle">'.$this->formataDinheiro($assinatura["mensalidade"]).' ('.$this->escreveValorPorExtenso($assinatura["mensalidade"] / 100, true).')</td>';
						$k++;
					}
					$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"] = '<tr>'.implode('</tr><tr>', $contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"]).'</tr>';
				} else {
					$contrato["parametros"]["tabela_contrato_assinaturas$sufixos[$j]"] = '<tr><td style="text-align:center" colspan="4">Nenhuma assinatura disponível</td></tr>';
				}

				// Tabela Descontos
				if (sizeof($contrato["parametros"]["contrato_descontos$sufixos[$j]"])) {
					foreach ($contrato["parametros"]["contrato_descontos$sufixos[$j]"] as $desconto) {
						$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"][] = '<td style="text-align:left;vertical-align:middle">'.$assinatura_id[$desconto["assinatura"]].'</td><td style="text-align:left;vertical-align:middle">'.$desconto["desconto_nome"].'</td><td style="text-align:center;vertical-align:middle">'.$this->formataDescontoValor($desconto["instalacao"]).' ('.(($desconto["instalacao"] > 1 || $desconto["instalacao"] == 0) ? $this->escreveValorPorExtenso($desconto["instalacao"] / 100, true) : $this->escreveValorPorExtenso($desconto["instalacao"] * 100)." por cento").')</td><td style="text-align:center;vertical-align:middle">'.$this->formataDescontoValor($desconto["mensalidade"]).' ('.(($desconto["mensalidade"] > 1 || $desconto["mensalidade"] == 0) ? $this->escreveValorPorExtenso($desconto["mensalidade"] / 100, true) : $this->escreveValorPorExtenso($desconto["mensalidade"] * 100)." por cento").')</td><td style="text-align:center;vertical-align:middle">'.$desconto["validade"].'</td><td style="text-align:center;vertical-align:middle">'.$desconto["desconto_tipo_nome"].'</td>';
					}
					$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"] = '<tr>'.implode('</tr><tr>', $contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"]).'</tr>';
				} else {
					$contrato["parametros"]["tabela_contrato_descontos$sufixos[$j]"] = '<tr><td style="text-align:center" colspan="6">Nenhum desconto disponível</td></tr>';
				}

			}

		}

	}

	##############
	### Buscar ###
	##############

	function buscar() {
		
		if ($_POST) {
			if (!$this->permissoes["contratos"]["buscar_plus"]) {
				$_POST["responsavel"] = $_SESSION["usuario"];
			}
			$id = md5(uniqid(rand(), true));
			$_SESSION["POST"][$id] = $_POST;
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=buscar&i=$id");
			exit;
		} elseif ($_SESSION["POST"][$_GET["i"]]) {
			$_POST = $_SESSION["POST"][$_GET["i"]];
		}
		
		$botoes = array();
		$botoes[] = '<input type="submit" name="buscar" class="buttonAzulEscuroBranco11" value="Buscar">';
		$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';

		$this->conteudo .= $this->carregaArquivo("contratos.buscar.inc.php");
		
		$this->conteudo = str_replace("[acoes]", '<td bgcolor="#505050"><strong>&nbsp;Buscar&nbsp;</strong></td>', $this->conteudo);
		$this->conteudo = str_replace("[codigo]", $_POST["codigo"], $this->conteudo);
		$this->conteudo = str_replace("[cliente]", $_POST["cliente"], $this->conteudo);
		$this->conteudo = str_replace("[condominio]", $this->geraSelect($this->buscaCondominios(), $_POST["condominio"]), $this->conteudo);
		$this->conteudo = str_replace("[servico]", $this->geraSelect($this->buscaServicos(), $_POST["servico"]), $this->conteudo);
		$this->conteudo = str_replace("[tipo]", $this->geraSelect($this->buscaTipos(), $_POST["tipo"]), $this->conteudo);
		$this->conteudo = str_replace("[responsavel_disabled]", $this->permissoes["contratos"]["buscar_plus"] ? "" : "disabled", $this->conteudo);
		$this->conteudo = str_replace("[responsavel]", $this->geraSelect($this->buscaResponsaveis(), $this->permissoes["contratos"]["buscar_plus"] ? $_POST["responsavel"] : $_SESSION["usuario"]), $this->conteudo);
		$this->conteudo = str_replace("[status]", $this->geraSelect($this->buscaStatusBuscar(), $_POST["status"]), $this->conteudo);
		$this->conteudo = str_replace("[data_geracao_inicio]", $_POST["data_geracao_inicio"], $this->conteudo);
		$this->conteudo = str_replace("[data_geracao_termino]", $_POST["data_geracao_termino"], $this->conteudo);
		$this->conteudo = str_replace("[data_ativacao_inicio]", $_POST["data_ativacao_inicio"], $this->conteudo);
		$this->conteudo = str_replace("[data_ativacao_termino]", $_POST["data_ativacao_termino"], $this->conteudo);
		$this->conteudo = str_replace("[data_cancelamento_inicio]", $_POST["data_cancelamento_inicio"], $this->conteudo);
		$this->conteudo = str_replace("[data_cancelamento_termino]", $_POST["data_cancelamento_termino"], $this->conteudo);
		$this->conteudo = str_replace("[resultado]", $this->geraResultadoBusca(), $this->conteudo);
		$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
		$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=buscar", $this->conteudo);
		
	}

	function geraResultadoBusca() {
		
		if ($_POST) {
			
			$campos = array ("id" => "#", "codigo" => "Código (Tipo)", "cliente" => "Cliente (Condomínio)", "datas" => "Datas", "servico" => "Serviço", "status" => "Status");
			$itens = array();
			$where = array();
			
			if ($_POST["codigo"]) {
				$_POST["codigo"] = trim($_POST["codigo"]);
				$where[] = "tab_contratos.cor_codigo LIKE '%{$_POST["codigo"]}%'";
			}
			if ($_POST["cliente"]) {
				$_POST["cliente"] = trim($_POST["cliente"]);
				$where[] = "tab_clientes.cli_nome LIKE '%{$_POST["cliente"]}%'";
			}
			if ($_POST["condominio"]) {
				$where[] = "tab_clientes.con_id = {$_POST["condominio"]}";
			}
			if ($_POST["data_geracao_inicio"]) {
				$data = substr($_POST["data_geracao_inicio"], 6, 4)."-".substr($_POST["data_geracao_inicio"], 3, 2)."-".substr($_POST["data_geracao_inicio"], 0, 2);
				$where[] = "tab_contratos.cor_data_geracao >= '$data'";
			}
			if ($_POST["data_geracao_termino"]) {
				$data = substr($_POST["data_geracao_termino"], 6, 4)."-".substr($_POST["data_geracao_termino"], 3, 2)."-".substr($_POST["data_geracao_termino"], 0, 2);
				$where[] = "tab_contratos.cor_data_geracao <= '$data'";
			}
			if ($_POST["data_ativacao_inicio"]) {
				$data = substr($_POST["data_ativacao_inicio"], 6, 4)."-".substr($_POST["data_ativacao_inicio"], 3, 2)."-".substr($_POST["data_ativacao_inicio"], 0, 2);
				$where[] = "tab_contratos.cor_data_ativacao >= '$data'";
			}
			if ($_POST["data_ativacao_termino"]) {
				$data = substr($_POST["data_ativacao_termino"], 6, 4)."-".substr($_POST["data_ativacao_termino"], 3, 2)."-".substr($_POST["data_ativacao_termino"], 0, 2);
				$where[] = "tab_contratos.cor_data_ativacao <= '$data'";
			}
			if ($_POST["data_cancelamento_inicio"]) {
				$data = substr($_POST["data_cancelamento_inicio"], 6, 4)."-".substr($_POST["data_cancelamento_inicio"], 3, 2)."-".substr($_POST["data_cancelamento_inicio"], 0, 2);
				$where[] = "tab_contratos.cor_data_cancelamento >= '$data'";
			}
			if ($_POST["data_cancelamento_termino"]) {
				$data = substr($_POST["data_cancelamento_termino"], 6, 4)."-".substr($_POST["data_cancelamento_termino"], 3, 2)."-".substr($_POST["data_cancelamento_termino"], 0, 2);
				$where[] = "tab_contratos.cor_data_cancelamento <= '$data'";
			}
			if ($_POST["servico"]) {
				$where[] = "tab_contratos.pls_id = {$_POST["servico"]}";
			}
			if ($_POST["responsavel"]) {
				$where[] = "tab_vendas.usu_id = {$_POST["responsavel"]}";
			}
			if ($_POST["tipo"]) {
				$where[] = "tab_contratos.crt_id = {$_POST["tipo"]}";
			}
			if ($_POST["status"]) {
				if ($_POST["status"] == -1) $where[] = "tab_contratos.crs_id BETWEEN 11 AND 18";
				if ($_POST["status"] == -2) $where[] = "tab_contratos.crs_id IN (11, 12)";
				if ($_POST["status"] == -3) $where[] = "tab_contratos.crs_id IN (13, 14)";
				if ($_POST["status"] == -4) $where[] = "tab_contratos.crs_id IN (15, 16)";
				if ($_POST["status"] == -5) $where[] = "tab_contratos.crs_id IN (19, 20, 21)";
				if ($_POST["status"] == -6) $where[] = "tab_contratos.crs_id IN (22, 23, 24)";
				if ($_POST["status"] >= 10) $where[] = "tab_contratos.crs_id = {$_POST["status"]}";
			}
			
			if ($_SESSION["origem"]) {
				$where[] = "tab_clientes.clo_id = {$_SESSION["origem"]}";
			}
			
			$where = implode(" AND\n					", $where);
			
			if (!$where) {
				$where = "1";
			}
			
			$query = $this->bancodedados->query("
				SELECT DISTINCT
					tab_contratos.cor_id AS id,
					tab_contratos.cor_codigo AS codigo,
					tab_clientes.cli_id AS cliente_id,
					tab_clientes.cli_nome AS cliente,
					tab_condominios.con_nome AS condominio,
					DATE_FORMAT(tab_contratos.cor_data_geracao, '%d/%m/%Y') AS geracao,
					DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS ativacao,
					DATE_FORMAT(tab_contratos.cor_data_cancelamento, '%d/%m/%Y') AS cancelamento,
					tab_planos_servicos.pls_nome AS servico,
					tab_contratos_tipos.crt_nome AS tipo,
					tab_contratos_status.crs_nome AS status
				FROM
					tab_contratos
					LEFT OUTER JOIN tab_planos_servicos ON (tab_contratos.pls_id = tab_planos_servicos.pls_id)
					LEFT OUTER JOIN tab_vendas ON (tab_contratos.cor_id = tab_vendas.cor_id)
					LEFT OUTER JOIN tab_clientes ON (tab_contratos.cli_id = tab_clientes.cli_id)
					LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
					INNER JOIN tab_contratos_tipos ON (tab_contratos.crt_id = tab_contratos_tipos.crt_id)
					INNER JOIN tab_contratos_status ON (tab_contratos.crs_id = tab_contratos_status.crs_id)
				WHERE
					$where
				ORDER BY
					tab_contratos.cor_data_ativacao,
					tab_contratos.cor_data_geracao");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				
				if ($this->permissoes["contratos"]["ver"]) {
					$dados["link"] = "javascript:verContrato('".$dados["id"]."');";
				}
				$dados["cliente"] = strlen($dados["cliente"]) > 25 ? substr($dados["cliente"], 0, 22)."..." : $dados["cliente"];
				if ($this->permissoes["clientes"]["ver_da"]) {
					$dados["cliente"] = "<a href=\"./?m=clientes&a=ver_da&i={$dados["cliente_id"]}\" class=\"txtAzulEscuro11\">{$dados["cliente"]}</a>";
				}
				if ($dados["condominio"]) {
					$dados["cliente"] .= "<br>({$dados["condominio"]})";
				}
				$dados["tipo"] = $dados["tipo"] == "Principal" ? "({$dados["tipo"]})" : str_replace("Aditivo ", "", $dados["tipo"]);
				$dados["codigo"] = "<div nowrap>{$dados["codigo"]}<br>{$dados["tipo"]}</div>";
				if ($dados["geracao"]) $dados["datas"] .= "G: {$dados["geracao"]}<br>";
				if ($dados["ativacao"]) $dados["datas"] = "A: {$dados["ativacao"]}<br>";
				if ($dados["cancelamento"]) $dados["datas"] .= "C: {$dados["cancelamento"]}";
				$dados["datas"] = "<div nowrap>{$dados["datas"]}</div>";
				$dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
				$dados["id"] = count($itens) + 1;
				
				$itens[] = $dados;
				
			}
			
			$listagem = controle::instanciaObjeto("listagem", $campos, $itens, 0, false, array(0, 135, 0, 75, 0, 200), null, array("right"));
			
			return $listagem->conteudo;
		
		}
		
		return "Nenhum registro encontrado!";
		
	}

	######################################
	### RDS - Busca Clientes Contratos ###
	######################################

	function buscaBotaoAdicionar($cliente_id) {
		
		if (!$this->permissoes["contratos"]["adicionar"]) return "";
		if (!$this->buscaServicosAssinaturasSemContrato($cliente_id)) return "";
		
		return '<input type="button" name="contratos_adicionar" class="buttonAzulEscuroBranco11" value="Adicionar" onClick="adicionarContrato(\''.$cliente_id.'\');">';
	
	}

	function buscaClientesContratos($cliente_id, $lista_status, $contrato_pai = null) {
		
	    $contratos = array();
	    
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_contratos.cor_id AS id,
				tab_contratos.cor_codigo AS codigo,
				tab_planos_servicos.pls_nome AS servico,
				DATE_FORMAT(cor_data_geracao, '%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(cor_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				DATE_FORMAT(cor_data_anulacao, '%d/%m/%Y') AS data_anulacao,
				DATE_FORMAT(cor_data_cancelamento, '%d/%m/%Y') AS data_cancelamento,
				tab_contratos_tipos.crt_nome AS tipo,
				tab_contratos_status.crs_id AS status_id,
				tab_contratos_status.crs_nome AS status
			FROM
				tab_contratos
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
				INNER JOIN tab_contratos_tipos ON (tab_contratos.crt_id = tab_contratos_tipos.crt_id)
				INNER JOIN tab_contratos_status ON (tab_contratos.crs_id = tab_contratos_status.crs_id)".($contrato_pai ? "
				LEFT OUTER JOIN tab_contratos_vinculos ON (tab_contratos.cor_id = tab_contratos_vinculos.cor_filho AND tab_contratos_vinculos.cor_id = $contrato_pai)" : "")."
			WHERE".($contrato_pai ? "
				tab_contratos_vinculos.cor_id IS NULL AND" : "")."
				tab_contratos.cli_id = $cliente_id AND
				tab_contratos.cor_pai IS NULL AND
				tab_contratos.crs_id IN ($lista_status)
			ORDER BY
				cor_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		    
			if (in_array($dados["status_id"], array(19, 20, 21))) $dados["cor_linha"] = "#D7D7D7";
			if ($this->permissoes["contratos"]["ver"]) $dados["link"] = "javascript:verContrato({$dados["id"]});";
			$dados["codigo"] = "<div nowrap>".$dados["codigo"]."</div>";
			$dados["datas"][] = "G: ".$dados["data_geracao"];
			if ($dados["data_ativacao"]) $dados["datas"][] = "A: ".$dados["data_ativacao"];
			if ($dados["data_anulacao"]) $dados["datas"][] = "AN: ".$dados["data_anulacao"];
			if ($dados["data_cancelamento"]) $dados["datas"][] = "C: ".$dados["data_cancelamento"];
			$dados["datas"] = "<div nowrap>".implode("<br>", $dados["datas"])."</div>";
			$dados["status"] = str_replace(" (", "<br>(", $dados["status"]);
			
			$contratos[] = $dados;
			
		}
		
		return $contratos;
		
	}

	function geraListagemClientesContratos($dados) {
		
		$cabecalho = array ("codigo" => "Código", "servico" => "Serviço", "datas" => "Datas", "status" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $dados, 0, false, array(150, 0, 100, 180), null, array("center"));
		
		return $listagem->conteudo;
		
	}

	################
	### Publicar ###
	################

	function publicar() {
		
		if (!$_POST) {
		    
		    $botoes = array();
			$botoes[] = '<input type="submit" name="publicar" id="publicar" class="buttonAzulEscuroBranco11" value="Publicar" disabled>';
			$botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back();">';
			
			$this->conteudo .= $this->carregaArquivo("contratos.publicar.inc.php");
			
			$this->conteudo = str_replace("[codigo]", $_GET["status"] == "Erro" ? $_GET["codigo"] : "", $this->conteudo);
			$this->conteudo = str_replace("[mensagem]", $_GET["status"] ? ($_GET["status"] == "Erro" ? "Erro ao registrar o contrato {$_GET["codigo"]}!\nTente novamente." : "Contrato {$_GET["codigo"]} registrado com êxito!") : "", $this->conteudo);
			$this->conteudo = str_replace("[botoes]", implode(" ", $botoes), $this->conteudo);
			$this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=publicar", $this->conteudo);
			
		} else {
			
			if (!($contrato_id = $this->buscaContratoId($_POST["codigo"])) || $_FILES["arquivo"]["error"]) {
				
				header("Location: ./?m=contratos&a=publicar&status=Erro&codigo={$_POST["codigo"]}");
				
			} elseif ($_FILES["arquivo"]["name"]) {
				
				$this->registraComentario($contrato_id, 34, null, $_SESSION["usuario"]);

				$extensao = $this->pegaExtensao($_FILES["arquivo"]["name"]);
				$arquivo = md5(sha1("Olé {$_POST["codigo"]} Telecom"));

				if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], "./contratos/$arquivo")) {
					$this->bancodedados->query("UPDATE tab_contratos SET cor_arquivo_extensao = '$extensao', crs_id = IF(crs_id = 19, 20, crs_id) WHERE cor_codigo = '{$_POST["codigo"]}'");
					header("Location: ./?m=contratos&a=publicar&status=OK&codigo={$_POST["codigo"]}");
				}
				
			}
			
		}
		
	}
	
	############
	### Cron ###
	############
	
	function ativaContratosAguardandoAtivacao() {
		
		$contratos = $this->buscaContratosAguardandoAtivacao();
		
		foreach ($contratos as $contrato_id) {
			
			$this->buscaDadosItem($contrato_id);
                        
                        if($this->dados_item["status_id"] == 18) {
                            
                            $data_ativacao = substr($this->dados_item["data_ativacao"], 6, 4)."-".substr($this->dados_item["data_ativacao"], 3, 2)."-".substr($this->dados_item["data_ativacao"], 0, 2);

                            switch ($this->dados_item["tipo_id"]) {
                                    case 11:
                                            $this->ativar_contrato_principal($this->dados_item, $data_ativacao, 19);
                                            break;
                                    case 12:
                                            $this->ativar_contrato_aditivo_alteracao($this->dados_item, $data_ativacao, 19);
                                            break;
                                    case 13:
                                            $this->ativar_contrato_aditivo_migracao($this->dados_item, $data_ativacao, 19);
                                            break;
                                    case 14:
                                            $this->ativar_contrato_aditivo_me($this->dados_item, $data_ativacao, 19);
                                            break;
                                    case 15:
                                            $this->ativar_contrato_aditivo_mt($this->dados_item, $data_ativacao, 19);
                                            break;
                            }
                        }
			
		}
		
	}
	
	function migraEnderecoContratosAguardandoMudancaEndereco() {
		
		$contratos = $this->buscaContratosAguardandoMudancaEndereco();
		
		foreach ($contratos as $contrato_id) {
			$this->migraEnderecoCliente($contrato_id);
		}
		
	}
	
	######################
	### Gera Listagens ###
	######################

	function geraListagemComentarios($comentarios) {

		for ($i = 0; $i < sizeof($comentarios); $i++) {
			$comentarios[$i]["responsavel"] = ($comentarios[$i]["responsavel_id"]) ? $comentarios[$i]["responsavel_nome"] : "Sistema";
			$comentarios[$i]["detalhamento"][] = "<b>{$comentarios[$i]["responsavel"]}</b> em <b>{$comentarios[$i]["data_hora"]}</b>";
			if ($comentarios[$i]["conteudo"]) {
				$comentarios[$i]["detalhamento"][] = preg_replace("/[\r\n\t ]+/", " ", $comentarios[$i]["conteudo"]);
			}
			$comentarios[$i]["detalhamento"] = implode('<br>', $comentarios[$i]["detalhamento"]);
		}

		$cabecalho = array ("detalhamento" => "Detalhamento");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $comentarios);

		return $listagem->conteudo;

	}

	function geraListagemAssinaturas($assinaturas) {

		foreach ($assinaturas as $i => $assinatura) {
		    $assinaturas[$i]["plano_nome"] = strlen($assinatura["plano_nome"]) > 42 ? substr($assinatura["plano_nome"], 0, 39)."..." : $assinatura["plano_nome"];
			if (array_key_exists("servico", $assinatura) && $assinatura["servico"]) {
		        $assinaturas[$i]["plano_nome"] .= "<br>({$assinatura["servico"]})";
			}
		}

		$cabecalho = array ("plano_nome" => "Plano", "sistema_nome" => "Sistema", "instalacao_formatado" => "Instalação", "mensalidade_formatado" => "Mensalidade");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $assinaturas, 0, false, array(0, 60, 75, 75), null, array("left", "center", "right", "right"));

		return $listagem->conteudo;

	}

	function geraListagemAssinaturasEditavel($assinaturas) {
	    
		$cabecalho = array ("checkbox" => "", "plano_nome" => "Plano", "sistema_nome" => "Sistema", "vigencia_formatado" => "Vigência", "multa_formatado" => "Multa", "instalacao" => "Instalação", "mensalidade" => "Mensalidade");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $assinaturas, 0, false, array(15, 0, 50, 50, 60, 70, 70), null, array("center", "left", "center", "center", "right", "right", "right"));
		
		return $listagem->conteudo;
		
	}

	function geraListagemDescontos($descontos) {
	    
		foreach ($descontos as $i => $desconto) {
			$descontos[$i]["status_nome"] = str_replace(" (", "<br>(", $desconto["status_nome"]);
			$descontos[$i]["desconto_tipo_nome"] = str_replace(" [", "<br>[", $desconto["desconto_tipo_nome"]);
		}
		
		$cabecalho = array ("desconto_nome" => "Nome", "instalacao_formatado" => "Instalação", "mensalidade_formatado" => "Mensalidade", "validade" => "Validade", "desconto_tipo_nome" => "Tipo", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $descontos, 0, null, array(0, 60, 75, 70, 65, 65), null, array("left", "right", "right"));
		
		return $listagem->conteudo;
		
	}

	function geraListagemFormasPagamento($formas_pagamento, $servico_id) {
	    
		$itens = array();
	    
		if ($formas_pagamento) {
		    
			$categoria = null;
			
			foreach ($formas_pagamento as $i => $forma_pagamento) {
			    
				if ($categoria != $forma_pagamento["categoria"]) {
					$categoria = $forma_pagamento["categoria"];
					$itens[] = array("mesclado" => "<b>&raquo; $categoria</b>", "cor_linha" => "#c9d4e0");
				}
				
				$formas_pagamento[$i]["selection"] = "<input type=\"radio\" name=\"formas_pagamento[$servico_id][{$forma_pagamento["categoria"]}]\" id=\"forma_pagamento_{$servico_id}_{$i}\" value=\"{$forma_pagamento["id"]}\"".(count($formas_pagamento) == 1 ? " checked" : "")." style=\"margin: 0\" \\>";
				$formas_pagamento[$i]["entrada"] = $forma_pagamento["entrada"] > 0 ? $this->formataDinheiro($forma_pagamento["entrada"]) : "";
				$formas_pagamento[$i]["parcelas"] = $forma_pagamento["parcelas"] > 0 ? $forma_pagamento["parcelas"] : "";
				$formas_pagamento[$i]["valor_parcela"] = $forma_pagamento["valor_parcela"] > 0 ? $this->formataDinheiro($forma_pagamento["valor_parcela"]) : "";
				$formas_pagamento[$i]["valor_total"] = $forma_pagamento["valor_total"] > 0 ? $this->formataDinheiro($forma_pagamento["valor_total"]) : "";
				$formas_pagamento[$i]["multa"] = $forma_pagamento["multa"] > 0 ? $this->formataDinheiro($forma_pagamento["multa"]) : "";
				$formas_pagamento[$i]["fidelidade"] = $forma_pagamento["fidelidade"] > 0 ? "{$forma_pagamento["fidelidade"]} meses" : "";
				
				$itens[] = $formas_pagamento[$i];
				
			}
			
		}

		$cabecalho = array("selection" => "", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Valor Parcelas", "valor_total" => "Valor Total", "forma" => "Formato", "fidelidade" => "Fidelidade", "multa" => "Multa");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(16), null, array("center", "right", "right", "right", "right", "center", "center", "right"));
		
		return $listagem->conteudo;
		
	}

	function geraListagemFormasPagamentoServico($formas_pagamento, $selecionado = null, $editavel = true) {

		$itens = array();
	    
		if ($formas_pagamento) {
		    
	    	$selecionados = array();
			$categoria = null;

			if ($selecionado && is_array($selecionado)) {
				foreach ($selecionado as $forma) {
					$selecionados[] = $forma["id"];
				}
			}
			
			foreach ($formas_pagamento as $i => $forma_pagamento) {
			    
			    if ($categoria != $forma_pagamento["categoria"]) {
					$primeiro_da_categoria = true;
					$categoria = $forma_pagamento["categoria"];
					$itens[] = array("mesclado" => "<b>&raquo; $categoria</b>", "cor_linha" => "#c9d4e0");
				} else {
					$primeiro_da_categoria = false;
				}
				
				if ($editavel) {
				    $formas_pagamento[$i]["selection"] = "<input type=\"radio\" name=\"formas_pagamento[{$forma_pagamento["categoria"]}]\" id=\"forma_pagamento_{$i}\" value=\"{$forma_pagamento["id"]}\"".($selecionados && in_array($forma_pagamento["id"], $selecionados) || ($primeiro_da_categoria && $formas_pagamento[$i + 1]["categoria"] != $categoria) ? " checked" : "").">";
				} else {
				    $formas_pagamento[$i]["selection"] = $forma_pagamento["id"] == $selecionado ? "X" : "";
				}
				
				$formas_pagamento[$i]["entrada"] = $forma_pagamento["entrada"] > 0 ? $this->formataDinheiro($forma_pagamento["entrada"]) : "";
				$formas_pagamento[$i]["parcelas"] = $forma_pagamento["parcelas"] > 0 ? $forma_pagamento["parcelas"] : "";
				$formas_pagamento[$i]["valor_parcela"] = $forma_pagamento["valor_parcela"] > 0 ? $this->formataDinheiro($forma_pagamento["valor_parcela"]) : "";
				$formas_pagamento[$i]["multa"] = $forma_pagamento["multa"] > 0 ? $this->formataDinheiro($forma_pagamento["multa"]) : "";
				$formas_pagamento[$i]["fidelidade"] = $forma_pagamento["fidelidade"] > 0 ? "{$forma_pagamento["fidelidade"]} meses" : "";
				
				$itens[] = $formas_pagamento[$i];
				
			}
			
		}
		
		$cabecalho = array("selection" => "", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Valor Parcelas", "forma" => "Forma", "multa" => "Multa", "fidelidade" => "Fidelidade");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(20), null, array("center", "right", "center", "right", "center", "right", "center"));

		return $listagem->conteudo;

	}

	function geraListagemSituacao($formas_pagamento) {

		$itens = array();
		    
		foreach ($formas_pagamento as $i => $forma_pagamento) {
		    
			$formas_pagamento[$i]["selection"] = "<input type=\"radio\" name=\"situacao\" id=\"situacao_$i\" value=\"{$forma_pagamento["id"]}\"".(count($formas_pagamento) == 1 ? " checked" : "")." onchange=\"mudaFormaPagamento(this)\" style=\"margin:0\" />";
			$formas_pagamento[$i]["categoria"] = "<label for=\"situacao_$i\">{$forma_pagamento["categoria"]}</label>";
			$formas_pagamento[$i]["entrada"] = "<span id=\"entrada_{$forma_pagamento["id"]}\">".($forma_pagamento["entrada"] > 0 ? $this->formataDinheiro($forma_pagamento["entrada"]) : "-")."</span>";
			$formas_pagamento[$i]["parcelas"] = "<span id=\"parcelas_{$forma_pagamento["id"]}\">".($forma_pagamento["parcelas"] > 0 ? $$forma_pagamento["parcelas"] : "-")."</span>";
			$formas_pagamento[$i]["valor_parcela"] = "<span id=\"valor_parcela_{$forma_pagamento["id"]}\">".($forma_pagamento["valor_parcela"] > 0 ? $this->formataDinheiro($forma_pagamento["valor_parcela"]) : "-")."</span>";
			$formas_pagamento[$i]["valor_total"] = "<span id=\"valor_total_{$forma_pagamento["id"]}\">".($forma_pagamento["valor_total"] > 0 ? $this->formataDinheiro($forma_pagamento["valor_total"]) : "-")."</span>";
			$formas_pagamento[$i]["multa"] = $forma_pagamento["multa"] > 0 ? $this->formataDinheiro($forma_pagamento["multa"]) : "-";
			$formas_pagamento[$i]["fidelidade"] = $forma_pagamento["fidelidade"] > 0 ? "{$forma_pagamento["fidelidade"]} meses" : "-";
			
			$itens[] = $formas_pagamento[$i];
			
		}
		
		$cabecalho = array("selection" => "", "categoria" => "Tipo", "multa" => "Multa", "fidelidade" => "Fidelidade", "entrada" => "Entrada", "parcelas" => "Parcelas", "valor_parcela" => "Parcela", "valor_total" => "Total");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(14, 0, 60, 60, 60, 50, 60, 60), null, array("center", "left", "right", "right", "right", "right", "right", "right"));

		return $listagem->conteudo;

	}

	function geraListagemAditivos($aditivos) {

		for ($i = 0; $i < sizeof($aditivos); $i++) {
			$aditivos[$i]["tipo_nome"] = str_replace(" (", "<br>(", $aditivos[$i]["tipo_nome"]);
			$aditivos[$i]["status_nome"] = str_replace(" (", "<br>(", $aditivos[$i]["status_nome"]);
			$aditivos[$i]["link"] = "?m=contratos&a=ver&i=".$aditivos[$i]["id"];
		}
		
		$cabecalho = array ("codigo" => "Código", "tipo_nome" => "Tipo", "datas" => "Datas", "status_nome" => "Status");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $aditivos, 0, false, array(115));

		return $listagem->conteudo;

	}

	function geraListagemAssinaturasAditivoAlteracao($cliente_id, $contrato_id, $contrato_pai, $servico_id) {

		$assinaturas = $this->buscaAssinaturasAditivoAlteracao($cliente_id, $contrato_id, $contrato_pai, $servico_id);
		$cabecalho = array ("checkbox" => "", "plano_nome" => "Nome", "instalacao" => "Instalação", "mensalidade" => "Mensalidade");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $assinaturas, 0, false, array(15));

		return $listagem->conteudo;

	}

	function geraListagemAssinaturasAditivoMigracao($contrato_id) {

		$assinaturas = $this->buscaAssinaturasAditivoMigracao($contrato_id);
		$cabecalho = array ("checkbox" => "", "plano_nome" => "Plano Atual", "novo_plano" => "Plano Novo");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $assinaturas, 0, false, array(15, 0, 260));

		return $listagem->conteudo;

	}

	function geraListagemAssinaturasAditivoMigracaoEditavel($dados_item) {

		$assinaturas = $this->buscaAssinaturasAditivoMigracaoEditavel($dados_item);
		$cabecalho = array ("checkbox" => "", "plano_nome" => "Plano Atual", "novo_plano" => "Novo Plano");
		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $assinaturas, 0, false, array(15, 0, 260));

		return $listagem->conteudo;

	}

	###############
	### Funções ###
	###############

	function geraTitulo() {

		$titulo	= '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';

		return $titulo;

	}

	function carregaArquivo($arquivo) {

		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);

		return $retorno;

	}

	function atualizaStatusCliente($cliente_id) {
		
		$clientes = controle::instanciaObjeto("clientes", $this->bancodedados, $this->permissoes, false);
		
		return $clientes->atualizaStatusCliente($cliente_id);
		
	}

	function extraiConteudo($conteudo) {
	    
	    $matches = array();
		
		if (preg_match('/<body>([\s\S]*)<\/body>/', $conteudo, $matches)) {
			return "<span style=\"page-break-before:always\"></span>".$matches[1];
		}
		
	}

	function possuiCabeamentoExistente($descontos, $assinaturas) {
		
		for ($i = 0; $i < sizeof($descontos); $i++) {
			if (in_array($descontos[$i]["desconto_id"], array(38, 75, 78, 116, 137, 138, 144, 149, 157, 158, 171, 172)) && array_key_exists($descontos[$i]["assinatura"], $assinaturas)) {
				return true;
			}
		}
		
		return false;
		
	}

	function buscaAditivo($servico_id, $versao) {
		
		return $this->carregaArquivo(DIR_RAIZ."modelos/aditivo_{$versao}_{$servico_id}.html");
		
	}

	function enviaMensagemCobranca($cobranca_id, $emails = null) {

		$cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, $this->permissoes, false);
		
		$cobrancas->buscaDadosItem($cobranca_id);
		$cobrancas->enviaBoletoAutomatico($emails);

	}

	function enviaMensagemAprovacao($dados_contrato, $dados_cliente, $emails) {
		
		$dados_cliente = $this->buscaDadosCliente($dados_cliente["id"]);
		$dados_lote = $this->buscaDadosMensagensLotes(1);
		$contratos = $this->buscaContratosEnviar($dados_contrato, $dados_cliente);
		
		$parametros = $dados_lote["parametros"]["envio"];
		
		$parametros["dados_conteudo"]["cliente_nome"] = $dados_cliente["nome"];
		$parametros["dados_conteudo"]["empresa_nome"] = "Ole Telecom";
		$parametros["dados_conteudo"]["empresa_telefone"] = "3028-0000";
		$parametros["dados_conteudo"]["url"] = "http://www.oletelecom.com.br/contratos/";
		$parametros["dados_conteudo"]["cpf_cnpj"] = $dados_cliente["cpf_cnpj"];
		$parametros["dados_conteudo"]["contratos_texto"] = $contratos["texto"];
		$parametros["dados_conteudo"]["contratos_html"] = $contratos["html"];
		$parametros["dados_conteudo"]["acao"] = $contratos["acao"];
		$parametros["destinatarios"] = $emails;
		$parametros["remetente_nome"] = "Olé Telecom";
		$parametros["dados_conteudo"]["remetente_nome"] = "Olé Telecom";

		$parametros = serialize($parametros);
		
		$this->bancodedados->query("
			INSERT INTO
				tab_mensagens
			SET
				men_parametros = '$parametros',
				men_data_hora_geracao = NOW(),
				cli_id = {$dados_cliente["id"]},
				mes_id = 1,
				mlm_id = {$dados_lote["modelo_id"]},
				mel_id = 1");
		
		$mensagem_id = $this->bancodedados->ultimoIdInserido();
		
		foreach ($contratos["links"] as $link) {
			$this->bancodedados->query("
				INSERT INTO
					tab_mensagens_links
				SET
					mei_md5 = '{$link["md5"]}',
					mei_url_modulo = '{$link["modulo"]}',
					mei_url_acao = '{$link["acao"]}',
					mei_url_id = {$link["id"]},
					men_id = $mensagem_id");
		}
		
	}

	function buscaContratosEnviar($dados_contrato, $dados_cliente) {
		
		$acao = in_array($dados_contrato["servico_id"], array(1, 2, 3, 5, 28, 32, 36)) ? "vistoria" : "ver";
		$url_contratos = "http://www.oletelecom.com.br/contratos/";
		$link = array("modulo" => "contratos", "acao" => $acao, "id" => $dados_contrato["id"], "md5" => md5("contratos{$acao}{$dados_contrato["id"]}"));
		
		$contratos = array();
		
		$contratos["html"] = '<table width="100%" cellpadding="0" cellspacing="0" border="1" align="center">';
		$contratos["html"] .= '<tr><td style="text-align:center"><b>Código</b></td><td style="text-align:center"><b>Serviço</b></td><td style="text-align:center"><b>&nbsp;</b></td></tr>';
		// Contrato Pai
		$contratos["html"] .= "<tr><td style=\"text-align:center;padding-left:3px;padding-right:3px;width:155px\">{$dados_contrato["codigo"]}</td>";
		$contratos["html"] .= "<td style=\"text-align:left;padding-left:3px;padding-right:3px\">{$dados_contrato["servico_nome"]}</td>";
		$contratos["html"] .= "<td style=\"text-align:center;padding-left:3px;padding-right:3px;width:60px\"><a href=\"{$url_contratos}?link={$link["md5"]}\">Ver</a></td></tr>";
		// Texto
		$contratos["texto"][] = "Contrato: {$dados_contrato["codigo"]} - {$dados_contrato["servico_nome"]}\n{$url_contratos}?link={$link["md5"]}";
		$contratos["links"][] = $link;
		$contratos["acao"][] = $dados_contrato["status_id"] == 13 ? "sua aprovação" : "visualização";

		// Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $contrato) {
			if ($contrato["servico_parametros"]["com_contrato"]) {
				$link = array("modulo" => "contratos", "acao" => $acao, "id" => $contrato["contrato_id"], "md5" => md5("contratos{$acao}{$contrato["contrato_id"]}"));
				$contratos["html"] .= "<tr><td style=\"text-align:center;padding-left:3px;padding-right:3px\">{$contrato["codigo"]}</td>";
				$contratos["html"] .= "<td style=\"text-align:left;padding-left:3px;padding-right:3px\">{$contrato["servico_nome"]}</td>";
				$contratos["html"] .= "<td style=\"text-align:center;padding-left:3px;padding-right:3px\"><a href=\"{$url_contratos}?link={$link["md5"]}\">Ver</a></td></tr>";
				// TEXTO
				$contratos["texto"][] = "Contrato: {$contrato["codigo"]} - {$contrato["servico_nome"]}\n{$url_contratos}?link={$link["md5"]}";
				$contratos["links"][] = $link;
				$contratos["acao"][] = $contrato["status_id"] == 13 ? "sua aprovação" : "visualização";
			}
		}
		
		$contratos["html"] .= '</table>';
		
		$contratos["texto"] = implode("\n\n", $contratos["texto"]);
		$contratos["acao"] = implode("/", array_unique($contratos["acao"]));
		
		return $contratos;
		
	}

	function calculaComissoes($assinatura, $assinatura_migrada, $vendedor_id, $condominio_id, $indicador = null, $contrato_id) {
	    
	    $comissoes = array();
		$tipo_agente = $this->buscaTipoAgente($vendedor_id);
		
		// Valor Comissao
		/*if ($this->usuarioPosVenda($vendedor_id)) {
		    
			if ($assinatura_migrada) {
				$referencia_mensalidade = $this->calculaPrimeiraMensalidade($assinatura) - $this->calculaMensalidadeFinal($assinatura_migrada);
				if ($this->contratoFidelidade($contrato_id)) {
					$comissao_mensalidade = $referencia_mensalidade > 0 ? 0.5 : 150.0;
				} else {
					$comissao_mensalidade = $referencia_mensalidade > 0 ? 0.25 : 0;
				}
			} elseif ($assinatura["plano_servico_id"] == 21) {
				$referencia_mensalidade = $this->calculaPrimeiraMensalidade($assinatura);
				$comissao_mensalidade = 1;
			}
			
		} else {*/

			$comissao_mensalidade = 1.3;
			$referencia_mensalidade = $this->calculaMediaMensalidadePrimeiroAno($assinatura, $tipo_agente == 49);

		//}
		
		// Gera Comissoes Mensalidade
		if ($referencia_mensalidade > 0 || $comissao_mensalidade > 1) {
			// Mensalidade Comissionamento
			$comissoes[4] = ($comissao_mensalidade > 10) ? $comissao_mensalidade : intval($referencia_mensalidade * $comissao_mensalidade);
			// Mensalidade Sub-Comissinamento (Indicação)
			if ($indicador) {
				$comissoes[5] = ($assinatura["parametros"]["comissoes"]["mensalidade_indicacao"] > 1) ? $assinatura["parametros"]["comissoes"]["mensalidade_indicacao"] : intval($comissoes[4] * $assinatura["parametros"]["comissoes"]["mensalidade_indicacao"]);
				$comissoes[4] -= $comissoes[5];
			}
		}
		
		// Verifica se existe comissão
		foreach ($comissoes as $comissao) {
			if ($comissao > 0) return $comissoes;
		}
		
		return null;
		
	}

	function buscaAssinatura($cliente_id, $plano_id, $lista_status) {

		$assinaturas = controle::instanciaObjeto("assinaturas", $this->bancodedados, $this->permissoes, false);

		return $assinaturas->buscaAssinatura($cliente_id, $plano_id, $lista_status);

	}

	function adicionarAssinatura($cliente_id, $plano_id, $sistema_id, $assinatura_id = null, $vinculo = null) {

		$assinaturas = controle::instanciaObjeto("assinaturas", $this->bancodedados, $this->permissoes, false);

		$assinaturas->adicionarAssinatura($assinaturas->buscaDadosCliente($cliente_id), $assinaturas->buscaCondominioCliente($cliente_id), $plano_id, $sistema_id, $assinatura_id, $vinculo);

	}

	function buscaErrosContratoAdicionar($dados_cliente, $tipo_id, $servico_id, $assinaturas, $ignorar_responsavel = 0, $novo_plano = null) {
	    
	    $erros = array();
		$assinaturas = implode(", ", $assinaturas);
		
		// Contrato Residencial sem Celular, Residencial ou Ponto de Referência
		if (in_array($servico_id, array(28, 33, 34)) && $tipo_id == 11 && !($dados_cliente["origem_id"] == 9)) {
			if (count($dados_cliente["telefones"]) < 2) {
				$erros["itens"][] = "O cliente precisa possuir pelo menos dois telefones de contato";
				$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
			}
			if (!$dados_cliente["ponto_referencia"]) {
				$erros["itens"][] = "O cliente não possui ponto de referência cadastrado";
				$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
			}
		}
		
		// Dominios Indefinidos
		if ($this->possuiDominiosIndefinidos($assinaturas)) {
			$erros["itens"][] = "O cliente possui dominio(s) indefinido(s)";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Limite Cota de Descontos
		if ($this->buscaDescontosCotaSaturada($dados_cliente["responsavel"], $assinaturas)) {
			$erros["itens"][] = "Limite de descontos com cota mensal atingido";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Cliente sem Responsabilidade
		if (!$ignorar_responsavel && !$dados_cliente["responsavel"]) {
			$erros["itens"][] = "O cliente não possui um responsável";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
    	// Cliente sem Operadora Origem
		//if ($dados_cliente["status"] != 2 && !$dados_cliente["operadora_origem_id"] && $tipo_id == 11) {
		//	$erros["itens"][] = "O cliente não possui uma Operadora Origem";
		//	$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		//}
		
		// Descontos Pendentes
		if ($this->possuiDescontosPendentes($assinaturas)) {
			$erros["itens"][] = "O cliente possui desconto(s) pendente(s)";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Sem Tipo
		if (!$dados_cliente["tipo"]) {
			$erros["itens"][] = "O cliente não possui tipo (Pessoa Física ou Jurídica) cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Sem CPF/CNPJ
		if (!$dados_cliente["cpf_cnpj"]) {
			$erros["itens"][] = "O cliente não possui CPF/CNPJ cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Maior de 18 anos
		if ($dados_cliente["tipo"] == 1 && ($dados_cliente["nascimento"] && intval(preg_replace("/[^0-9]/", "", $dados_cliente["nascimento"])) > date("Ymd", strtotime("18 YEARS AGO")))) {
			$erros["itens"][] = "O cliente é menor de 18 anos";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Não possui data de nascimento
		if ($dados_cliente["tipo"] == 1 && !$dados_cliente["nascimento"]) {
			$erros["itens"][] = "O cliente não possui data de nascimento cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Sem E-mail
		if (!$dados_cliente["emails"] && !($dados_cliente["origem_id"] == 9)) {
			$erros["itens"][] = "O cliente não possui e-mail cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Sem Telefone
		if (!$dados_cliente["telefones"] && !($dados_cliente["origem_id"] == 9)) {
			$erros["itens"][] = "O cliente não possui telefone cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Bloco
		if ($dados_cliente["condominio_id"] && !$dados_cliente["bloco_id"]) {
			$erros["itens"][] = "O cliente não possui bloco cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		// Complemento
		if ($dados_cliente["condominio_id"] && !$dados_cliente["complemento"]) {
			$erros["itens"][] = "O cliente não possui complemento cadastrado";
			$erros["adicionar_plus"] = 0; // Usuário thunder não tem permissão para gerar contrato
		}
		
		if ($erros["itens"] && $_SESSION["usuario"] == 39) {
			$erros["adicionar_plus"] = 1;
		}

		return $erros;
		
	}

	function pegaExtensao($arquivo) {
		
	    $matches = array();
	    
	    if (preg_match('@^.+\.([^.]+)$@', $arquivo, $matches)) {
		  return strtolower($matches[1]);
	    }
		
	}

	function geraSelect($itens, $selecionado = null, $item_nulo = false) {
	    
		$select = "";

		if ($item_nulo) {
			$select .= "<option></option>";
		}
		foreach ($itens as $valor => $item) {
			$select .= "<option value=\"{$valor}\"".($valor == $selecionado ? " selected" : "").">{$item}</option>";
		}
		
		return $select;
		
	}

	function formataDinheiro($valor, $moeda = 'R$') {

		$valor = preg_replace("/[^0-9]/", "", intval($valor));
		$valor = number_format($valor / 100, 2, ",", ".");
		if ($moeda) $valor = $moeda." ".$valor;

		return $valor;

	}

	function formataDescontoValor($valor) {

		return $valor <= 1 && $valor > 0 ? intval($valor * 100)."%" : $this->formataDinheiro(intval($valor));

	}

	function formataDescontoValidade($validade, $data_termino) {

		if ($data_termino) {
			return $data_termino;
		} else {
			return ($validade > 0) ? (($validade == 1) ? "$validade mês" : "$validade meses") : "Indeterminada";
		}

	}

	function escreveValorPorExtenso($valor, $dinheiro = false, $maiusculas = false) {

		$rt = "";
		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

		if (strpos($valor, ",") > 0) {
			$valor = str_replace(".", "", $valor);
			$valor = str_replace(",", ".", $valor);
		}

		if (!$dinheiro) {
			$singular[0] = "";
			$plural[0] = "";
			$singular[1] = "";
			$plural[1] = "";
		}

		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
		$z=0;
		$valor = number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);

		for($i = 0; $i < count($inteiro); $i++) {
			for($ii = strlen($inteiro[$i]); $ii < 3; $ii++) {
				$inteiro[$i] = "0".$inteiro[$i];
			}
		}

		$fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);

		for ($i = 0; $i < count($inteiro); $i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt.((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ").$r;
		}

		if (!trim($rt)) $rt = "zero ".$plural[1];
		if ($maiusculas) $rt = ucwords($rt);

		return trim($rt);

	}

	function fechar() {

		if ($this->dados_item["cliente"]) {
			$this->conteudo	= '<script type="text/javascript">if (window.opener && window.opener.buscaContratosCliente) window.opener.buscaContratosCliente('.$this->dados_item["cliente"].');</script>';
			$this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscaAjustesCliente) window.opener.buscaAjustesCliente('.$this->dados_item["cliente"].');</script>';
			$this->conteudo .= '<script type="text/javascript">if (window.opener && window.opener.buscaCancelamentosCliente) window.opener.buscaCancelamentosCliente('.$this->dados_item["cliente"].');</script>';
			$this->conteudo .= '<script type="text/javascript">window.close();</script>';
		} else {
			$this->conteudo	= '<script type="text/javascript">window.close();</script>';
		}

	}

	function buscaArquivoContrato($dados_contrato, $tipo) {

		if ($tipo == "vistoria") {
			return DIR_RAIZ."modelos/{$tipo}_{$dados_contrato["parametros"]["contrato_versao"]}_{$dados_contrato["servico_id"]}.html";
		} else {
			if (file_exists(DIR_RAIZ."modelos/{$tipo}_{$dados_contrato["parametros"]["contrato_versao"]}_{$dados_contrato["servico_id"]}_{$dados_contrato["parametros"]["sistema"]}.html")) {
				return DIR_RAIZ."modelos/{$tipo}_{$dados_contrato["parametros"]["contrato_versao"]}_{$dados_contrato["servico_id"]}_{$dados_contrato["parametros"]["sistema"]}.html";
			} else {
				return DIR_RAIZ."modelos/{$tipo}_{$dados_contrato["parametros"]["contrato_versao"]}_{$dados_contrato["servico_id"]}.html";
			}
		}

		return null;

	}

	function existeContrato($dados_contrato, $tipo) {

		$arquivo = $this->buscaArquivoContrato($dados_contrato, $tipo);

		if ($arquivo) {
			return file_exists($arquivo);
		}

		return false;

	}

	function buscaParcelamentos($parcelas = null, $parcelamento_forma = 3, $maximo = 0, $valor = null, $servico_id = null, $assinatura_id = null) {

		$maximo = $maximo ? $maximo : ($parcelas ? $parcelas : 10);
	    $formas = array();	    
		
	    $formas[] = $this->formataDinheiro($valor)." (Antecipado)";
	    
    	for ($i = 1; $i <= $maximo; $i++) {
    		$formas[$i] = "$i X de ".$this->formataDinheiro(intval($valor / $i)).($parcelamento_forma == 1 ? " (Boleto Bancário)" : " (Cartão)");
    	}
    	
    	return $parcelas !== null ? $formas[$parcelas] : $formas;

	}

	function corrigePlano($plano) {

		if ($plano <= 5) {
			return $plano * 1024 * 1024;
		}

		if ($plano <= 50) {
			return ($plano / 10) * 1024 * 1024;
		}

		return $plano * 1024;

	}

	function buscaUnidades($unidade = 0) {

		$lista_unidades = array(
			"1" => "K",
			"2" => "M",
			"3" => "G",
			"4" => "T"
		);

		return $unidade ? $lista_unidades[$unidade] : $lista_unidades;

	}

	function formataFormaPagamento($forma_pagamento) {

		$retorno = array();

		if (!$forma_pagamento["entrada"] && !$forma_pagamento["parcelas"]) {
			$retorno[] = "Isento";
		} else {
			if ($forma_pagamento["entrada"]) {
				$retorno[] = $this->formataDinheiro($forma_pagamento["entrada"])." (À Vista)";
			}
			if ($forma_pagamento["parcelas"]) {
				$retorno[] = "{$forma_pagamento["parcelas"]} x ".$this->formataDinheiro($forma_pagamento["valor_parcela"]).($forma_pagamento["forma"] ? " ({$forma_pagamento["forma"]})" : "");
			}
		}

		$retorno = implode(" + ", $retorno);

		if ($forma_pagamento["parcelas"]) {
			$retorno .= " = ".$this->formataDinheiro($forma_pagamento["valor_total"]);
		}

		return "{$forma_pagamento["categoria"]}: <b>{$retorno}</b>";

	}

	function corrigeNascimento($nascimento) {
	    
		if ($nascimento) {
			$nascimento = explode("/", $nascimento);
			$nascimento = "'$nascimento[2]-$nascimento[1]-$nascimento[0]'";
		} else {
			$nascimento = "NULL";
		}
		
		return $nascimento;
		
	}

	function formataData($data, $aspas = true) {
	    
		$data = substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2);
		
		return $aspas ? "'$data'" : $data;
		
	}

	function buscaListaVigencia($vigencia_maxima = 24) {
	    
	    $lista = array(0 => "");
		
		for ($i = 6; $i <= $vigencia_maxima; $i += 6) {
			$lista[$i] = $i == 1 ? "$i mês" : "$i meses";
		}
		
		return $lista;
		
	}

	function buscaCondicoesPagamento($condicao_id = null) {

		$condicoes = array(
			1 => "A vista",
			2 => "Boleto bancário",
			3 => "Entrada (dinheiro) + Boletos bancários",
			4 => "Entrada (cheque) + Boletos bancários"
		);
		
		return $condicao_id ? $condicoes[$condicao_id] : $condicoes;
		
	}

	function buscaFormasPagamentoVinculo($cliente_id, $servico_id, $assinaturas) {
	    
		$servicos = array($servico_id);
		$assinaturas_vinculadas = $this->buscaAssinaturasVinculadas($assinaturas, $servico_id);
	    $formas = array();
		$vinculos = array();
		
		foreach ($assinaturas_vinculadas as $servico_id => $vinculos) {

			$dados_assinaturas = $this->buscaDadosAssinaturasProspect($cliente_id, $servico_id);
			$dados_servico = $this->buscaDadosServico($servico_id);

			$servicos[] = $servico_id;

			$formas[] = '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td height="30" colspan="2"><strong>&nbsp;Formas de Pagamento Instalação ('.$dados_servico["nome"].'):</strong></td></tr>';
			$formas[] = '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11"><td colspan="2" id="formas_pagamento">';
			$formas[] = $this->geraListagemFormasPagamento($this->buscaFormasPagamento($dados_assinaturas["plano_id"], $dados_assinaturas["vigencia"], $dados_assinaturas["multa"]), $servico_id);
			$formas[] = '</td></tr>';
		}
		
		$vinculos["servicos"] = implode(",", $servicos);
		$vinculos["formas"] = $formas ? implode($formas) : null;
		
		return $vinculos;
		
	}

	function insereChamadoVistoriaResidencial($cliente_id, $contrato_id, $contrato_codigo) {
	    
		$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
		$chamado_categoria_id = $this->buscaCategoriaViabilidadeResidencial($cliente_id);
		$chamado_id = $chamados->buscaChamadoClienteCategoria($cliente_id, $chamado_categoria_id);
		$dados_contrato = $this->buscaDadosContrato($contrato_id, false);
		$dados = array();
		
		$dados["conteudo"] = "Verificar viabilidade de instalação.";
		$dados["conteudo"] .= "[d]";
		$dados["conteudo"] .= "Contrato: [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato($contrato_id)\">$contrato_codigo</a>]\n";
		$dados["conteudo"] .= "Serviço: {$dados_contrato["servico_nome"]}\n";
		$dados["conteudo"] .= "Plano(s): ".$this->buscaPlanosContrato($contrato_id)."\n";
		$dados["conteudo"] .= "[/d]";
		$dados["conteudo"] .= "Questionário de viabilidade de instalação:\n";
		$dados["conteudo"] .= "[d]";
		$dados["conteudo"] .= "[n]Pergunta 1 - Tipo de edificação:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 2 - Número de andares:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 3 - Cor da edificação:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 4 - Ponto de referência:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 5 - POP's vistos:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 6 - Lugar da edificação onde foram vistos os POP's:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 7 - Tipo de visada:[/n][d]-[/d]";
		$dados["conteudo"] .= "[n]Pergunta 8 - Equipamento sugerido:[/n][d]-[/d]";
		$dados["conteudo"] .= "[/d]";
		$dados["sistema"] = true;
		
		if ($chamado_id) {
			// Registra Etapa
			$chamados->registraEtapa($chamados->buscaDadosChamado($chamado_id, true), 5, $dados);
		} else {
			// Gera Dados Chamado
			$dados["solicitante"] = "Sistema (Intranet)";
			$dados["categoria"] = $chamado_categoria_id;
			$dados["prioridade"] = 3;
			$dados["tipo"] = 5;
			// Instancia Classe Chamados
			$chamados->adicionarChamado($cliente_id, null, $dados);
		}
		
	}

	function ehContratoCancelamento($contrato, $assinaturas) {
	    
	    $mantidas = array();
	    
		foreach ($contrato["assinaturas"] as $assinatura) {
			if (!in_array($assinatura["id"], $assinaturas)) {
				$mantidas[] = $assinatura["id"];
			}
		}
		
		return count($contrato["assinaturas"]) == count($mantidas) + count($assinaturas);
		
	}

	function ehContratoAtivacao($dados_contrato) {
	    
		if ($dados_contrato["contrato_assinaturas_final"]) {
			foreach ($dados_contrato["contrato_assinaturas_final"] as $assinatura) {
				if (!$dados_contrato["contrato_assinaturas_inicial"][$assinatura["id"]]) {
					return true;
				}
			}
		}
		
		return false;
		
	}

	function formataPrazoVigencia($vigencia_ativa, $vigencia, $data_expiracao, $multa, $termino_fidelidade = null) {

		$vigencia_formatada = $this->formataVigencia($vigencia, $termino_fidelidade);

		if ($vigencia) {
			if ($data_expiracao) {
				$vigencia_formatada .= $vigencia_ativa ? " - Até $data_expiracao" : " - <font color=\"red\">Expirada em $data_expiracao</font>";
			}
			$vigencia_formatada .= " - Multa Rescisória: ".$this->formataDinheiro($multa);
		} elseif ($termino_fidelidade) {
			if ($vigencia_ativa) {
				$vigencia_formatada = "Até $vigencia_formatada";
			} else {
				$vigencia_formatada = "<font color=\"red\">Expirada em $vigencia_formatada</font>";
			}
			$vigencia_formatada .= " - Multa Rescisória: ".$this->formataDinheiro($multa);
		}

		return $vigencia_formatada;

	}

	function formataVigencia($vigencia, $termino_fidelidade) {

		if ($vigencia) {
			return $vigencia.($vigencia > 1 ? " meses" : " mês");
		} elseif ($termino_fidelidade) {
			return date("d/m/Y", strtotime($termino_fidelidade));
		}

		return "Contrato sem Plano Fidelidade";

	}

	function buscaPrazosVigencia() {

	    $itens = array();
	    
		for ($i = 1; $i <= 24; $i++) {
			$itens[$i]	= "$i ".($i > 1 ? "meses" : "mês");
		}

		return $itens;

	}

	function buscaParcelamentoMaximo($servico_id) {

		return $servico_id == 28 ? 6 : 10;

	}

	function comparaNomes($nome1, $nome2) {

		return $this->limpaNome($this->corrigeNome($nome1)) == $this->limpaNome($this->corrigeNome($nome2));

	}

	function limpaNome($nome) {

		$nome = trim(preg_replace("/[\r\n\t ]+/", " ", $nome));
		$nome = str_replace(array("ç", "ñ"), array("c", "n"),$nome);
		$nome = preg_replace("/[áàâãäª]/", "a", $nome);
		$nome = preg_replace("/[éèêë]/", "e", $nome);
		$nome = preg_replace("/[íìîï]/", "i", $nome);
		$nome = preg_replace("/[óòôõö°º]/", "o", $nome);
		$nome = preg_replace("/[úùûü]/", "u", $nome);
		$nome = preg_replace("/[']/", "`", $nome);

		return strtolower($nome);

	}

	function corrigeNome($nome) {

		$minusculas = array("da", "de", "do", "das", "dos", "e");
		$maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");

		$nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
		$nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
		$nome = preg_replace("/[']/", "`", $nome);

		if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
			$nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
			$nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
		}

		$nome = str_replace(array(" Jr "), array(" Júnior "), " $nome ");

		for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
		for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos

		$nome = trim($nome); // Remove excessos do início e do fim

		return $nome;

	}

	function buscaOpcoesInstalacao($parametros, $servico_id, $tipo_id) {

	    $opcoes = array();
	    
		if ($tipo_id <= 12) {

			// Contrato Principal e Aditivo
			$valor_padrao = $parametros["tabela_valor_final_instalacao"];
			$fidelizacao_instalacao = false;
			
			if (array_key_exists("contrato_descontos", $parametros)) {
				foreach ($parametros["contrato_descontos"] as $desconto) {
					if ($desconto["desconto_id"] == 199) {
						$fidelizacao_instalacao = true;
					}
				}
			}
			if ($servico_id == 28) {
				$opcoes[1] = $this->formataDinheiro($valor_padrao);
				return $opcoes;
			}
			if (in_array($servico_id, array(1, 2, 3, 5, 36)) && ($valor_padrao > 0 || $fidelizacao_instalacao)) {
				$opcoes[0] = "R$ 0,00 (Cabeamento Existente)".($fidelizacao_instalacao ? " [Sem Fidelização]" : "");
			} elseif ($valor_padrao == 0) {
				$opcoes[0] = "R$ 0,00 (Cabeamento Existente)";
			}
			if (!$fidelizacao_instalacao) {
				$opcoes[1] = $this->formataDinheiro($valor_padrao)." (Sem Cabeamento)";
			} else {
				$opcoes[2] = $this->formataDinheiro($valor_padrao)." (Sem Cabeamento) [Com Fidelização]";
			}

		} elseif ($tipo_id == 14) {

			// Mudança de Endereço
			$valor_padrao = $parametros["tabela_valor_final_instalacao"];
			$opcoes[0] = "R$ 0,00 (Cabeamento Existente) [Sem Fidelização]";
			$opcoes[1] = "R$ 0,00 (Sem Cabeamento) [Com Fidelização]";
			if ($valor_padrao) {
				$opcoes[2] = $this->formataDinheiro($valor_padrao)." (Sem Cabeamento) [Sem Fidelização]";
			}

		}

		return $opcoes;

	}

	function buscaProximaRegra($servidor_id) {
		
	    if (($regra = $this->buscaRegraLivre($servidor_id)) != false) {
			return $regra;
		} else {
			return $this->buscaRegraMaxima($servidor_id) + 1;
		}
		
	}

	function abreChamadoRetiradaEquipamentos($dados_contrato, $equipamentos, $data_cancelamento) {
		
		$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
		$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_contrato["cliente"], 1052455);
		$strEquipamentos = "";

		foreach ($equipamentos as $equipamento) {
			$strEquipamentos .= "[d][n]{$equipamento["equipamento_nome"]}[/n]\n";
			$strEquipamentos .= "[n] Modelo: [/n]{$equipamento["equipamento_modelo_nome"]}\n";
			$strEquipamentos .= "[n] MAC: [/n]{$equipamento["mac_address"]}[/d]";
		}
		
		$dados = array();

		$dados["conteudo"] = "Agendar retirada de equipamento(s) em comodato a partir de ".date("d/m/Y", strtotime("$data_cancelamento + 1 day")).":";
		$dados["conteudo"] .= "[d]Contrato: [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato({$dados_contrato["id"]})\">{$dados_contrato["codigo"]}</a>][/d]";
		$dados["conteudo"] .= "Equipamentos:";
		$dados["conteudo"] .= $strEquipamentos;
		$dados["sistema"] = true;

		if ($chamado_id) {
			// Registra Etapa
			$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
			$chamados->registraEtapa($dados_chamado, 5, $dados);
		} else {
			// Gera Dados Chamado
			$dados["solicitante"] = "Sistema (Intranet)";
			$dados["categoria"] = 1052455;
			$dados["prioridade"] = 3;
			$dados["tipo"] = 5;
			// Adiciona Chamado
			$chamado_id = $chamados->adicionarChamado($dados_contrato["cliente"], null, $dados);
			$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
		}

		$dados_cliente = $this->buscaDadosCliente($dados_contrato["cliente"]);

		$dados = array();

		$dados["conteudo"] = "<h1 style=\"text-align: center\">TERMO DE DEVOLU&Ccedil;&Atilde;O DE EQUIPAMENTO</h1>";
		$dados["conteudo"] .= "<div style=\"padding: 0 20px 20px 2px\">Pelo presente termo, declaro ter recebido em devolu&ccedil;&atilde;o o(s) seguinte(s) equipamento(s) entregue(s) em regime de comodato a(o) Sr(a). {$dados_cliente["nome"]}:</div>";
		$dados["conteudo"] .= "<div style=\"padding: 0 20px 20px 2px\"><table width=\"100%\" cellspacing=\"0\" border=\"0\"><tr><td><b>Equipamento</b></td><td colspan=\"3\"><b>Estado de conserva&ccedil;&atilde;o</b></td></tr>";
		foreach ($equipamentos as $equipamento) {
			$dados["conteudo"] .= "<tr>";
			$dados["conteudo"] .= "<td class=\"txtAzulEscuro11\" style=\"border: 0; padding: 0\">( &nbsp; ) {$equipamento["equipamento"]}</td>";
			$dados["conteudo"] .= "<td class=\"txtAzulEscuro11\" style=\"border: 0; padding: 0\">( &nbsp; ) Ótimo</td>";
			$dados["conteudo"] .= "<td class=\"txtAzulEscuro11\" style=\"border: 0; padding: 0\">( &nbsp; ) Bom</td>";
			$dados["conteudo"] .= "<td class=\"txtAzulEscuro11\" style=\"border: 0; padding: 0\">( &nbsp; ) Ruim</td>";
			$dados["conteudo"] .= "</tr>";
		}
		$dados["conteudo"] .= "</table></div>";
		$dados["conteudo"] .= "<div style=\"padding: 0 20px 20px 2px\">E por ser esta a express&atilde;o da verdade, firmo o presente termo.</div>";
		$dados["conteudo"] .= "<div style=\"padding: 0 20px 20px 2px\"><table width=\"100%\" cellspacing=\"0\" border=\"0\"><tr><td class=\"txtAzulEscuro11\" style=\"border: 0; padding: 0\">Data da devolu&ccedil;&atilde;o: ___ / ___ / ______.</td><td style=\"text-align: right; border: 0; padding: 0\" class=\"txtAzulEscuro11\"><div>Recebido por: __________________________________________</div><div style=\"padding-top: 10px\">CPF: __________________________________________</div></td></tr></table></div>";
		$dados["sistema"] = true;

		$chamados->registraEtapa($dados_chamado, 19, $dados);

		$chamados->registraEtapa($dados_chamado, 16, array("responsavel_fechamento" => "4,4,0"));

	}

	function abreChamadoVisitaMigracao($dados_contrato) {
			
		$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
		$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_contrato["cliente"], 1052451);
		
		$dados = array();

		$dados["conteudo"] = "[d]Contrato: [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato({$dados_contrato["id"]})\">{$dados_contrato["codigo"]}</a>][/d]";
		$dados["sistema"] = true;
		
		if ($chamado_id) {
			// Registra Etapa
			$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
			$chamados->registraEtapa($dados_chamado, 5, $dados);
		} else {
			// Gera Dados Chamado
			$dados["solicitante"] = "Sistema (Intranet)";
			$dados["categoria"] = 1052451;
			$dados["prioridade"] = 3;
			$dados["tipo"] = 5;
			// Adiciona Chamado
			$chamado_id = $chamados->adicionarChamado($dados_contrato["cliente"], null, $dados);
			$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
		}
		
	}

	function abreChamadoAnulaContratoReprovadoTecnico($dados_contrato){
		
		$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
		$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_contrato["cliente"], 103);
		
		$dados = array();
		
		$dados["conteudo"] = "Contrato reprovado por inviabilidade técnica. Favor informar ao cliente.";
		$dados["conteudo"] .= "[d]Contrato: [<a class=\"txtAzulEscuro11\" style=\"text-decoration:underline\" href=\"javascript:verContrato({$dados_contrato["id"]})\">{$dados_contrato["codigo"]}</a>][/d]";
		$dados["sistema"] = true;
			
		if ($chamado_id) {
			// Registra Etapa
			$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
			$chamados->registraEtapa($dados_chamado, 5, $dados);
		} else {
			// Gera Dados Chamado
			$dados["solicitante"] = "Sistema (Intranet)";
			$dados["categoria"] = 103;
			$dados["prioridade"] = 3;
			$dados["tipo"] = 5;
			// Adiciona Chamado
			$chamados->adicionarChamado($dados_contrato["cliente"], null, $dados);
		}
		
	}

	########################
	### Busca Dados Item ###
	########################

	function buscaDadosItem($contrato_id) {

		$this->dados_item = $this->buscaDadosContrato($contrato_id);

	}

	function buscaDadosContrato($contrato_id, $detalhamento = true) {

		if (!$contrato_id) {
			return null;
		}

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos.cor_id AS id,
				tab_contratos.cli_id AS cliente,
				tab_contratos.cor_codigo AS codigo,
				tab_contratos.cor_hash AS hash,
				tab_contratos.cli_id AS cliente_id,
				DATE_FORMAT(tab_contratos.cor_data_geracao, '%d/%m/%Y') AS data_geracao,
				DATE_FORMAT(tab_contratos.cor_data_confirmacao, '%d/%m/%Y') AS data_confirmacao,
				DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				tab_contratos.cor_data_ativacao AS data_ativacao_original,
				DATE_FORMAT(tab_contratos.cor_data_cancelamento, '%d/%m/%Y') AS data_cancelamento,
				DATE_FORMAT(tab_contratos.cor_data_anulacao, '%d/%m/%Y') AS data_anulacao,
				TO_DAYS(CURDATE()) - TO_DAYS(tab_contratos.cor_data_cancelamento) AS dias_apos_cancelamento,
				DATE_FORMAT(tab_contratos.cor_termino_fidelidade, '%d/%m/%Y') AS vigencia_expiracao,
				IF(tab_contratos.cor_termino_fidelidade >= CURDATE(), 1, 0) AS vigencia_ativa,
				tab_contratos.cor_vigencia AS vigencia,
				tab_contratos.cor_termino_fidelidade AS termino_fidelidade,
				tab_contratos.cor_multa AS multa,
				tab_contratos.fop_id AS forma_pagamento_id,
				tab_contratos.cor_parametros AS parametros,
				tab_contratos.cor_enviado AS enviado,
				tab_contratos.cor_sincronizado AS sincronizado,
				tab_contratos.cor_renegociacao AS renegociacao,
				tab_contratos.cor_pai AS pai,
				tab_contratos_status.crs_id AS status_id,
				tab_contratos_status.crs_nome AS status_nome,
				tab_contratos_motivos_anulacao.cma_id AS motivos_anulacao_id,
				tab_contratos_motivos_anulacao.cma_nome AS motivos_anulacao_nome,
				tab_contratos_tipos.crt_id AS tipo_id,
				tab_contratos_tipos.crt_nome AS tipo_nome,
				tab_contratos_tipos.crt_parametros AS tipo_parametros,
				tab_planos_servicos.pls_id AS servico_id,
				tab_planos_servicos.pls_nome AS servico_nome,
				tab_planos_servicos.pls_parametros AS servico_parametros,
				tab_clientes.clo_id AS origem_id
			FROM
				tab_contratos
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_contratos.cli_id)
				INNER JOIN tab_contratos_tipos ON (tab_contratos_tipos.crt_id = tab_contratos.crt_id)
				INNER JOIN tab_contratos_status ON (tab_contratos_status.crs_id = tab_contratos.crs_id)
				LEFT OUTER JOIN tab_contratos_motivos_anulacao ON (tab_contratos_motivos_anulacao.cma_id = tab_contratos.cma_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
			WHERE
				tab_contratos.cor_id = $contrato_id".($_SESSION["origem"] ? " AND
				tab_clientes.clo_id = {$_SESSION["origem"]}" : ""));

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["cliente_id"] = $dados["cliente"];

			// Parametros
			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["tipo_parametros"] = unserialize($dados["tipo_parametros"]);
			$dados["servico_parametros"] = unserialize($dados["servico_parametros"]);

			// Datas
			$dados["datas"][] = "G: ".$dados["data_geracao"];
			if ($dados["data_ativacao"]) $dados["datas"][] = "A: ".$dados["data_ativacao"];
			if ($dados["data_anulacao"]) $dados["datas"][] = "AN: ".$dados["data_anulacao"];
			if ($dados["data_cancelamento"]) $dados["datas"][] = "C: ".$dados["data_cancelamento"];
			$dados["datas"] = "<div nowrap style=\"width:75px\">".implode("<br>", $dados["datas"])."</div>";

			// Situação
			$dados["situacao"][] = ($dados["renegociacao"]) ? "<font color=red>Renegociação</font>" : "Fechado";
			if (!$dados["sincronizado"]) $dados["situacao"][] = "<font color=red>Aguardando sincronização</font>";
			if (!$dados["enviado"] && $dados["status_id"] == 13) $dados["situacao"][] = "<font color=red>Aguardando envio do contrato</font>";
			$dados["situacao"] = implode(" / ", $dados["situacao"]);
			if (!$dados["data_confirmacao"]) $dados["situacao"] .= " <font color=\"red\">(Aguardando confirmação)</font>";
			if ($dados["data_confirmacao"]) $dados["situacao"] .= " (Confirmado em {$dados["data_confirmacao"]})";

			// Busca Dados
			$dados["comentarios"] = $this->buscaComentariosContrato($dados["id"], $detalhamento);
			$dados["assinaturas"] = $this->buscaAssinaturasContrato($dados["id"], $detalhamento);
			$dados["descontos"] = $this->buscaDescontosContrato($dados["id"], $detalhamento);
			$dados["aditivos"] = $this->buscaAditivosContrato($dados["id"], $detalhamento);
			$dados["visitas"] = $this->buscaVisitasContrato($dados["id"], $detalhamento);
			
			// Formas Pagamento
			$dados["formas_pagamento"] = $this->buscaFormasPagamentoContrato($dados["id"], $dados["parametros"]["contrato_multa_rescisao"], $dados["parametros"]["contrato_prazo_vigencia"]);
			if ($dados["formas_pagamento"]) {
				foreach ($dados["formas_pagamento"] as $forma_pagamento) {
					if (!$dados["forma_pagamento_id"] || $forma_pagamento["id"] == $dados["forma_pagamento_id"]) {
						$dados["forma_pagamento"][] = $this->formataFormaPagamento($forma_pagamento);
					}
				}
				$dados["forma_pagamento"] = implode("<br>", $dados["forma_pagamento"]);
			} elseif ($dados["parametros"]["tabela_valor_final_instalacao"]) {
				$dados["forma_pagamento"] = $this->buscaParcelamentos($dados["parametros"]["parcelamento"], $dados["parametros"]["parcelamento_forma"], 0, $dados["parametros"]["tabela_valor_final_instalacao"], $dados["servico_id"], $dados["assinaturas"][0]["id"]);
			} else {
				$dados["forma_pagamento"] = "";
			}
			$dados["sistema_id"] = $this->buscaSistema($dados["pai"] ? $dados["pai"] : $dados["id"]);

			// Prazo Vigência
			$vigencia_filhos = $this->buscaVigenciaAtivaFilhos($dados["id"]);
			if ($vigencia_filhos) {
				$dados["vigencia_filhos"] = $vigencia_filhos;
				$dados["prazo_vigencia"] = $this->formataPrazoVigencia(1, $vigencia_filhos["vigencia"], $vigencia_filhos["vigencia_expiracao"], $vigencia_filhos["multa"], $vigencia_filhos["termino_fidelidade"]);
				$dados["vigencia_ativa"] = 1;
				$dados["multa"] = $vigencia_filhos["multa"];
				$dados["termino_fidelidade"] = $vigencia_filhos["termino_fidelidade"];
				$dados["inicio_fidelidade"] = $vigencia_filhos["data_ativacao"];

			} elseif ($dados["vigencia_ativa"] || !$dados["formas_pagamento"]) {
				$dados["prazo_vigencia"] = $this->formataPrazoVigencia($dados["vigencia_ativa"], $dados["vigencia"], $dados["vigencia_expiracao"], $dados["multa"], $dados["termino_fidelidade"]);
			} elseif ($dados["formas_pagamento"]) {
				foreach ($dados["formas_pagamento"] as $forma_pagamento) {
					if (!$dados["forma_pagamento_id"] || $forma_pagamento["id"] == $dados["forma_pagamento_id"]) {
						$dados["prazo_vigencia"][] = "{$forma_pagamento["categoria"]}: <b>".$this->formataPrazoVigencia(false, $forma_pagamento["fidelidade"], null, $forma_pagamento["multa"])."</b>";
					}
				}
				$dados["prazo_vigencia"] = implode("<br>", $dados["prazo_vigencia"]);
			}
			if(!array_key_exists("inicio_fidelidade", $dados) || !$dados["inicio_fidelidade"]){
				$dados["inicio_fidelidade"] = $dados["data_ativacao"];
			}
			if ($dados["pai"]) {
				$dados["vigencia_pai_expiracao"] = $this->buscaVigenciaExpiracao($dados["pai"]);
			}

			// Vinculos
			$dados["vinculos"] = $this->buscaContratosVinculos($dados["id"]);
			$dados["vinculos_pai"] = $this->buscaContratosVinculosPai($dados["id"]);

			$dados["vinculo_pai_obrigatorio"] = false;

			foreach ($dados["vinculos_pai"] as $vinculo) {
				if ($vinculo["vinculo_tipo_id"] != 1) {
					$dados["vinculo_pai_obrigatorio"] = true;
				}
			}

			// Comodatos
			$dados["comodatos"] = $this->buscaContratoComodatos($dados["id"]);

			return $dados;

		}

	}

	##################
	### Comentario ###
	##################

	function registraComentario($contrato_id, $categoria_id, $conteudo = "", $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$categoria_id = $categoria_id ? $categoria_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_contratos_comentarios
			SET
				coo_datahora = NOW(), 
				cor_id = $contrato_id, 
				coo_conteudo = '$conteudo', 
				ccc_id = $categoria_id, 
				usu_id = $usuario_id");

	}

	function registraComentarioClienteComodato($comodato_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_clientes_comodatos_comentarios 
			SET
				ccc_datahora = NOW(), 
				ccc_conteudo = '$conteudo', 
				clc_id = $comodato_id, 
				usu_id = $usuario_id");

	}

	function registraComentarioVisita($visita_id, $conteudo, $categoria_id, $usuario_id = null) {

		$categoria_id = $categoria_id ? $categoria_id : "NULL";
		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_visitas_comentarios 
			SET
				vic_datahora = NOW(), 
				vis_id = $visita_id, 
				vic_conteudo = '$conteudo', 
				vcc_id = $categoria_id, 
				usu_id = $usuario_id");

	}

	function registraComentarioClientePonto($ponto_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_clientes_pontos_comentarios 
			SET
				cpc_datahora = NOW(), 
				clp_id = $ponto_id, 
				cpc_conteudo = '$conteudo', 
				usu_id = $usuario_id");

	}

	function registraComentarioClienteAutenticacao($autenticacao_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_clientes_autenticacoes_comentarios 
			SET
				cac_datahora = NOW(), 
				cau_id = $autenticacao_id, 
				cac_conteudo = '$conteudo', 
				usu_id = $usuario_id");

	}

	function registraComentarioClienteEmail($email_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_clientes_emails_comentarios 
			SET
				cec_datahora = NOW(), 
				cle_id = $email_id, 
				cec_conteudo = '$conteudo', 
				usu_id = $usuario_id");

	}

	function registraComentarioServicoAjuste($ajuste_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_servicos_ajustes_comentarios 
			SET
				sam_datahora = NOW(), 
				saj_id = $ajuste_id, 
				sam_conteudo = '$conteudo', 
				usu_id = $usuario_id");

	}

	function registraComentarioAjusteFinanceiro($ajuste_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		$this->bancodedados->query("
			INSERT INTO 
				tab_ajustes_financeiros_comentarios 
			SET
				afm_datahora = NOW(), 
				ajf_id = $ajuste_id, 
				afm_conteudo = '$conteudo', 
				usu_id = $usuario_id");

	}

	function registraComentarioDesconto($desconto_id, $conteudo, $usuario_id = null) {

		$usuario_id = $usuario_id ? $usuario_id : "NULL";
		$conteudo = addslashes(trim($conteudo));

		// Descomentar a linha abaixo para registrar o comentário na tabela tab_descontos_concessoes_comentarios
		$this->bancodedados->query("
			INSERT INTO 
				tab_descontos_concessoes_comentarios
			SET
				dcc_datahora = NOW(), 
				dec_id = $desconto_id, 
				dcc_conteudo = '$conteudo', 
				usu_id = $usuario_id");

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

	#################
	### Consultas ###
	#################

	function buscaDadosModulo($nome) {

		$query = $this->bancodedados->query("
			SELECT 
				mod_id AS id, 
				mod_nome AS nome, 
				mod_descricao AS descricao 
			FROM 
				tab_modulos 
			WHERE 
				mod_nome = '$nome'");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosAcao($nome) {

		$query = $this->bancodedados->query("
			SELECT 
				aco_id AS id, 
				aco_nome AS nome, 
				aco_descricao AS descricao, 
				act_id AS tipo 
			FROM 
				tab_acoes 
			WHERE 
				aco_nome = '$nome' AND 
				mod_id = '{$this->dados_modulo["id"]}'");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaContratoId($codigo) {
			
		$query = $this->bancodedados->query("
			SELECT
				cor_id
			FROM
				tab_contratos
			WHERE
				cor_codigo = '$codigo'
			LIMIT 0, 1");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaClienteContrato($codigo) {
		
		$query = $this->bancodedados->query("
			SELECT
				cli_nome
			FROM
				tab_clientes
			WHERE
				cli_id = (SELECT cli_id FROM tab_contratos WHERE cor_codigo = '$codigo')");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaContratosCliente($cliente_id) {
		
		$cliente_id = (int) preg_replace("/[^0-9]/", "", $cliente_id);

		$contratos = array();
				
		$query = $this->bancodedados->query("
			SELECT
				cor_codigo AS codigo
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id
			ORDER BY
				cor_codigo");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["codigo"];
		}
		
		return $contratos;
		
	}

	function cobrancaInstalacaoGerada($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_cobrancas_detalhes.coa_id)
			FROM
				tab_cobrancas_detalhes
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_cobrancas_detalhes.ajf_id)
			WHERE
				tab_ajustes_financeiros.cor_id = $contrato_id AND
				tab_ajustes_financeiros.afc_id = 19 AND
				tab_ajustes_financeiros.afs_id <> 6");

		return $this->bancodedados->resultado($query) > 0;

	}

	function cobrancaInstalacaoRecebida($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_cobrancas.coa_id)
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
			WHERE
				tab_cobrancas.cot_id IN (SELECT cot_id FROM tab_cobrancas_status WHERE tab_cobrancas_status.cot_recebido = 1) AND
				tab_ajustes_financeiros.cor_id = $contrato_id AND
				tab_ajustes_financeiros.afc_id = 19 AND
				tab_ajustes_financeiros.afs_id <> 6");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiServicoAtivo($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT 
				COUNT(*) 
			FROM 
				tab_clientes_pontos 
			WHERE 
				cps_id = 1 AND 
				ass_id IN (
					SELECT 
						ass_id 
					FROM 
						rel_ass_cor_estacontido 
					WHERE 
						cor_id = $contrato_id
				)");

		if ($this->bancodedados->resultado($query) > 0) {
			return true;
		}

		$query = $this->bancodedados->query("
			SELECT 
				COUNT(*) 
			FROM 
				tab_clientes_autenticacoes 
			WHERE 
				cas_id = 1 AND 
				ass_id IN (
					SELECT 
						ass_id 
					FROM 
						rel_ass_cor_estacontido 
					WHERE 
						cor_id = $contrato_id
				)");

		if ($this->bancodedados->resultado($query)) {
			return true;
		}

		return false;

	}

	function existeAprovacaoTecnicaAnulada($cliente_id, $servico_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_contratos.cor_id)
			FROM
				tab_contratos
			WHERE
				tab_contratos.cli_id = {$cliente_id} AND
				tab_contratos.pls_id = {$servico_id} AND
				tab_contratos.crs_id = 24 AND
				tab_contratos.cor_data_anulacao = CURDATE() AND
				(
					SELECT
						tab_contratos_comentarios.ccc_id
					FROM
						tab_contratos_comentarios
					WHERE
						tab_contratos.cor_id = tab_contratos_comentarios.cor_id AND
						tab_contratos_comentarios.ccc_id IN (13, 14)
					ORDER BY
						tab_contratos_comentarios.coo_datahora
					 LIMIT 1
				) = 13");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiVisitaNaoAgendada($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(vis_id)
			FROM
				tab_visitas
			WHERE
				cor_id = {$contrato_id} AND
				via_id = 1");

		return $this->bancodedados->resultado($query) > 0;

	}

	function ehClienteConsultavelCredito($cliente_id) {
		
		return false;

		$query = $this->bancodedados->query("
			SELECT
				ccc_restricoes AS restricoes,
				DATEDIFF(NOW(), ccc_data_hora) AS dias
			FROM
				tab_clientes_consultas_credito
			WHERE
				ccc_cpf_cnpj = (
					SELECT
						cli_cpf_cnpj
					FROM
						tab_clientes
					WHERE
						cli_id = $cliente_id
				)
			ORDER BY
				ccc_data_hora DESC
			LIMIT 1");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return ($dados["restricoes"] > FINANCEIRO_LIMITE_RESTRICOES && $dados["dias"] > 0) || $dados["dias"] > 14;
		}

		return true;

	}

	function buscaQuantidadeRestricoes($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				ccc_restricoes
			FROM
				tab_clientes_consultas_credito
			WHERE
				ccc_cpf_cnpj = (
					SELECT
						cli_cpf_cnpj
					FROM
						tab_clientes
					WHERE
						cli_id = $cliente_id
				)
			ORDER BY
				ccc_data_hora DESC
			LIMIT
				1");

		return (int) $this->bancodedados->resultado($query);

	}

	function possuiAssinaturaPosPagaAtiva($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(ass_id)
			FROM
				tab_assinaturas
			WHERE
				cli_id = $cliente_id AND
				ast_id = 2 AND
				pli_id = 1");

		return $this->bancodedados->resultado($query) > 0;

	}

	function existeAprovacaoCliente() {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(coo_id)
			FROM
				tab_contratos_comentarios
			WHERE
				cor_id = {$this->dados_item["id"]} AND
				ccc_id = 8");

		return $this->bancodedados->resultado($query) > 0;

	}

	function existeCancelamentoPerdido($cliente_id, $contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(can_id)
			FROM
				tab_cancelamentos
			WHERE
				cli_id = $cliente_id AND
				(cor_id IS NULL OR cor_id = $contrato_id) AND
				can_termino >= DATE_SUB(NOW(), INTERVAL 5 DAY) AND
				cas_id = 4");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiAditivoPendente($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(cor_id)
			FROM
				tab_contratos
			WHERE
				cor_pai = $contrato_id AND
				crs_id < 19");

		return $this->bancodedados->resultado($query) > 0;

	}

	function cobrancaMultaGerada($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(cor_id)
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_cobrancas_detalhes ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes.ajf_id)
			WHERE
				cor_id = $contrato_id");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaTipos() {

		$tipos = array();

		$query = $this->bancodedados->query("
			SELECT
				crt_id AS id,
				crt_nome AS nome
			FROM
				tab_contratos_tipos
			WHERE
				crt_id >= 11
			ORDER BY
				crt_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$tipos[$dados["id"]] = $dados["nome"];
		}

		return $tipos;

	}

	function buscaTiposCliente() {

		$lista = array();

		$query = $this->bancodedados->query("
			SELECT
				clt_id AS id,
				clt_nome AS nome
			FROM
				tab_clientes_tipos
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}

		return $lista;

	}

	function buscaStatus() {

		$status = array();

		$query = $this->bancodedados->query("
			SELECT
				crs_id AS id,
				crs_nome AS nome
			FROM
				tab_contratos_status
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$status[$dados["id"]] = $dados["nome"];
		}

		return $status;

	}

	function buscaMotivosAnulacao() {

		$motivos_anulacao = array();

		$query = $this->bancodedados->query("
			SELECT
				cma_id AS id,
				cma_nome AS nome
			FROM
				tab_contratos_motivos_anulacao
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			 $motivos_anulacao[$dados["id"]] = $dados["nome"];
		}

		return $motivos_anulacao;

	}

	function buscaStatusBuscar() {

		$lista = array(
			"-1" => "Em Aberto",
			"-2" => "Em Aberto (Financeiro)",
			"-3" => "Em Aberto (Comercial)",
			"-4" => "Em Aberto (Técnico)",
			"-5" => "Ativo",
			"-6" => "Inativo"
		);

		$query = $this->bancodedados->query("
			SELECT
				crs_id AS id,
				crs_nome AS nome
			FROM
				tab_contratos_status
			WHERE
				crs_id > 10
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$lista[$dados["id"]] = $dados["nome"];
		}

		asort($lista);

		return $lista;

	}

	function buscaResponsaveis() {

		$responsaveis = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_usuarios
				INNER JOIN tab_vendas ON (tab_usuarios.usu_id = tab_vendas.usu_id)
			WHERE
				tab_usuarios.uss_id = 1
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$responsaveis[$dados["id"]] = $dados["nome"];
		}

		return $responsaveis;

	}

	function buscaServicos() {

		$servicos = array();

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS id,
				pls_nome AS nome
			FROM
				tab_planos_servicos
			ORDER BY
				nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[$dados["id"]] = $dados["nome"];
		}

		return $servicos;

	}

	function buscaCondominios() {

		$condominios = array();

		$query = $this->bancodedados->query("
			SELECT
				con_id AS id,
				CONCAT(tab_condominios.con_nome, ' (', tab_cidades.cid_nome, '/', tab_bairros.bai_nome, ')') AS nome
			FROM
				tab_condominios
				INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
			WHERE
				cos_id IS NOT NULL
			ORDER BY
				tab_condominios.con_nome,
				tab_cidades.cid_nome,
				tab_bairros.bai_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$condominios[$dados["id"]] = $dados["nome"];
		}

		return $condominios;

	}

	function buscaPanoramaCondominio($condominio_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios_status.cos_nome AS status,
				SUM(IF(tab_clientes.cls_id = 2, 1, 0)) AS clientes
			FROM
				tab_condominios
				INNER JOIN tab_condominios_status ON (tab_condominios.cos_id = tab_condominios_status.cos_id)
				LEFT OUTER JOIN tab_clientes ON (tab_condominios.con_id = tab_clientes.con_id)
			WHERE
				tab_condominios.con_id = $condominio_id
			GROUP BY
				tab_condominios.con_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return "<b>{$dados["status"]}</b> ({$dados["clientes"]} cliente".($dados["clientes"] == 1 ? "" : "s").")";
		}

	}

	function buscaBlocos($condominio_id) {

		$blocos = array();

		$query = $this->bancodedados->query("
			SELECT
				cob_id AS id,
				cob_nome AS nome
			FROM
				tab_condominios_blocos
			WHERE
				con_id = $condominio_id
			ORDER BY
			nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$blocos[$dados["id"]] = $dados["nome"];
		}

		return $blocos;

	}

	/**
	 * Busca cobrancas atrasadas para um cliente
	 * @param int $cliente_id ID do cliente
	 * @return array Array de cobrancas atrasadas
	 */
	function buscaCobrancasAtrasadas($cliente_id) {

		$query = $this->bancodedados->query("
					SELECT 
						cli_id,
						coa_id,
						coa_data_vencimento,
						DATEDIFF(CURRENT_DATE, coa_data_vencimento) AS dias_atraso
					FROM tab_cobrancas
					WHERE cot_id = 1 
					AND cof_id NOT IN (1, 2, 3)
					AND cli_id IN ( $cliente_id )
					AND coa_data_vencimento < CURRENT_DATE - INTERVAL 15 DAY
					AND cli_id IN (
						-- Subquery para manter apenas quem tem mais de 1 cobrança nessa condição
						SELECT cli_id
						FROM tab_cobrancas
						WHERE cot_id = 1 
							AND cof_id NOT IN (1, 2, 3)
							AND coa_data_vencimento < CURRENT_DATE - INTERVAL 15 DAY
						GROUP BY cli_id
						HAVING COUNT(coa_id) > 1
					)
					ORDER BY cli_id, coa_data_vencimento ASC;");

		$retorno = $this->bancodedados->resultadoArrayTotal($query);

		if (count($retorno) > 0) {
			return $retorno;
		}
		else {
			return false;
		}

	}

	function buscaCobrancasVencimentoAtual($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id)
			FROM
				tab_cobrancas
			WHERE
				coa_data_vencimento BETWEEN '".date("Y-m-01")."' AND '".date("Y-m-t")."' AND
				cli_id = $cliente_id AND
				col_id IS NOT NULL");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaInstaladores() {

		$usuarios = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id,
				tab_usuarios.usu_nome AS nome
			FROM
				tab_usuarios
				INNER JOIN rel_usu_orf_ors_exerce ON (tab_usuarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
			WHERE
				tab_usuarios.uss_id = 1 AND
				rel_usu_orf_ors_exerce.ors_id = 10
			ORDER BY
				tab_usuarios.usu_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$usuarios[$dados["id"]] = $dados["nome"];
		}

		return $usuarios;

	}

	function buscaInstalador($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_nome
			FROM
				tab_usuarios
				INNER JOIN tab_visitas ON (tab_usuarios.usu_id = tab_visitas.usu_id)
			WHERE
				tab_visitas.cor_id = $contrato_id");

		return $this->bancodedados->resultado($query);

	}

	function geradoPosVenda($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos_comentarios.coo_id
			FROM
				tab_contratos_comentarios
				INNER JOIN rel_usu_orf_ors_exerce ON (tab_contratos_comentarios.usu_id = rel_usu_orf_ors_exerce.usu_id)
			WHERE
				tab_contratos_comentarios.cor_id = $contrato_id AND
				tab_contratos_comentarios.ccc_id = 1 AND
				rel_usu_orf_ors_exerce.ors_id IN (6, 15)");

		return $this->bancodedados->resultado($query);

	}

	function buscaDataAgendamento($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(vis_data, '%d/%m/%Y')
			FROM
				tab_visitas
			WHERE
				cor_id = $contrato_id AND
				via_id = 2");

		return $this->bancodedados->resultado($query);

	}

	function buscaServicoAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_pontos.clp_ip AS ip,
				tab_clientes_pontos.clp_endereco_mac AS mac
			FROM
				tab_clientes_pontos
			WHERE
				tab_clientes_pontos.ass_id = $assinatura_id AND
				tab_clientes_pontos.cps_id IN (1, 2)
			LIMIT 0, 1");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return "Ponto: {$dados["mac"]}".($dados["ip"] ? " ({$dados["ip"]})" : "");
		}

		$query = $this->bancodedados->query("
			SELECT
				tab_dominios.dom_dominio AS dominio
			FROM
				tab_dominios
			WHERE
				tab_dominios.ass_id = $assinatura_id AND
				tab_dominios.dos_id IN (1, 2, 3, 4)
			LIMIT 0, 1");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return "Domínio: {$dados["dominio"]}";
		}

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

	function buscaAssinaturasCliente($cliente_id, $detalhamento = false) {

		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id
			FROM
				tab_assinaturas
			WHERE
				cli_id = $cliente_id
			ORDER BY
				ass_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $detalhamento ? $this->buscaDadosAssinatura($dados["id"]) : $dados["id"];
		}

		return $assinaturas;

	}

	function possuiVinculoObrigatorio($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(ass_id)
			FROM
				tab_assinaturas_vinculos
			WHERE
				ass_filha = $assinatura_id AND
				pvt_id IN (2, 3)");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaAssinaturasEditavel($cliente_id, $contrato_id, $servico_id, $assinaturas_selecionadas = null) {

		$assinaturas = array();
		$i = 0;

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				(SELECT GROUP_CONCAT(ass_id SEPARATOR ',') FROM tab_assinaturas AS filhas WHERE filhas.ass_pai = tab_assinaturas.ass_id) AS filhas,
				tab_assinaturas.ass_instalacao AS instalacao,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.ass_vigencia AS vigencia,
				tab_assinaturas.ass_multa AS multa,
				tab_assinaturas.ast_id AS status_id,
				tab_assinaturas.ass_pai AS assinatura_pai,
				tab_planos.pla_nome AS plano_nome,
				tab_planos_servicos.pls_parametros as servico_parametros,
				tab_planos_sistemas.pli_nome AS sistema_nome,
				tab_planos.pls_id AS servico_id,
				IF(rel_ass_cor_estacontido.cor_id IS NULL, 0, 1) selecionado
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_assinaturas.pli_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			WHERE
				(tab_assinaturas.cli_id = $cliente_id) AND
				(tab_assinaturas.ast_id IN (1, 2)) AND
				(tab_planos.pls_id = $servico_id) AND
				(rel_ass_cor_estacontido.cor_id = $contrato_id OR rel_ass_cor_estacontido.cor_id IS NULL)
			ORDER BY
				IF(tab_assinaturas.ass_pai IS NOT NULL, tab_assinaturas.ass_pai, tab_assinaturas.ass_id) ASC,
				tab_assinaturas.ass_pai ASC,
				selecionado DESC,
				id ASC");
		
		$quantidade = $this->bancodedados->linhas($query);

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["plano_nome"] = strlen($dados["plano_nome"]) > 42 ? substr($dados["plano_nome"], 0, 39)."..." : $dados["plano_nome"];
			$dados["servico"] = $this->buscaServicoAssinatura($dados["id"]);
			if ($dados["servico"]) {
				$dados["plano_nome"] .= "<br>({$dados["servico"]})";
			}
			$dados["instalacao"] = $this->formataDinheiro($dados["instalacao"]);
			$dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"]);
			$dados["vigencia_formatado"] = $dados["vigencia"] ? $dados["vigencia"].($dados["vigencia"] > 1 ? " meses" : " mes") : "-";
			$dados["multa_formatado"] = $dados["multa"] ? $this->formataDinheiro($dados["multa"]) : "-";
			$dados["link"] = "javascript:verAssinatura({$dados["id"]})";
			$dados["servico_parametros"] = unserialize($dados["servico_parametros"]);

			if (!$dados["assinatura_pai"]) {
				if (($assinaturas || $dados["servico_parametros"]["aceita_multiplos"]) && !$this->possuiVinculoObrigatorio($dados["id"])) {
					$dados["checkbox"] = "<input type=\"radio\" style=\"height:15px;width:15px;margin:0px;padding:0px\" id=\"assinaturas_{$i}\" name=\"assinaturas\" value=\"{$dados["id"]}".($dados["filhas"] ? ",{$dados["filhas"]}" : "")."\" onclick=\"atualizaAssinaturas(1)\"".($assinaturas_selecionadas || $quantidade == 1 ? (in_array($dados["id"], $assinaturas_selecionadas) || $quantidade == 1 ? " checked" : "") : ($dados["selecionado"] ? " checked" : "")).">";
				} else {
					$dados["checkbox"] = "<input type=\"radio\" style=\"height:15px;width:15px;margin:0px;padding:0px\" id=\"assinaturas_{$i}\" name=\"assinaturas\" value=\"{$dados["id"]}".($dados["filhas"] ? ",{$dados["filhas"]}" : "")."\" checked disabled><input type=\"hidden\" name=\"assinaturas[]\" value=\"{$dados["id"]}\">";
				}
				$i++;
			}

			$assinaturas[] = $dados;

		}

		return $assinaturas;

	}

	function buscaAssinaturasServico($cliente_id, $servico_id) {

		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_assinaturas.ast_id IN (1, 2) AND
				tab_planos.pls_id = $servico_id AND
				rel_ass_cor_estacontido.cor_id IS NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $dados;
		}

		return $assinaturas;

	}

	function buscaAssinaturasAditivoAlteracao($cliente_id, $contrato_id, $contrato_pai, $servico_id) {
				
		$assinaturas = array();
				
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_instalacao AS instalacao,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_planos.pla_nome AS plano_nome,
				tab_planos.plt_id AS plano_tipo,
				tab_planos.pls_id AS servico_id
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			WHERE
				(tab_assinaturas.cli_id = $cliente_id) AND
				(tab_assinaturas.ast_id IN (1, 2)) AND
				(tab_planos.pls_id = $servico_id) AND
				(rel_ass_cor_estacontido.cor_id IS NULL OR rel_ass_cor_estacontido.cor_id IN ($contrato_id, $contrato_pai))
			ORDER BY
				tab_assinaturas.ass_pai ASC,
				plano_tipo ASC,
				id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$i = count($assinaturas);

			$dados["plano_nome"] = strlen($dados["plano_nome"]) > 42 ? substr($dados["plano_nome"], 0, 39)."..." : $dados["plano_nome"];
			$dados["servico"] = $this->buscaServicoAssinatura($dados["id"]);
			if ($dados["servico"]) {
				$dados["plano_nome"] .= "<br>({$dados["servico"]})";
			}
			$dados["instalacao"] = $this->formataDinheiro($dados["instalacao"]);
			$dados["mensalidade"] = $this->formataDinheiro($dados["mensalidade"]);
			$dados["link"] = "javascript:verAssinatura({$dados["id"]})";
			$dados["plano_nome"] = "<div align=\"left\">{$dados["plano_nome"]}</div>";
			if ($dados["plano_tipo"] == 1) {
				$dados["checkbox"] = "<input type=\"checkbox\" style=\"height:15;width:15\" id=\"assinaturas_{$i}\" name=\"assinaturas[]\" value=\"{$dados["id"]}\" checked disabled><input type=\"hidden\" name=\"assinaturas[]\" value=\"{$dados["id"]}\">";
			} else {
				$selecionado = $this->existeContratoAssinatura($contrato_id, $dados["id"]);
				$dados["checkbox"] = "<input type=\"checkbox\" style=\"height:15;width:15\" id=\"assinaturas_{$i}\" name=\"assinaturas[]\" value=\"{$dados["id"]}\"".(($selecionado) ? " checked" : "").">";
			}
			
			$assinaturas[] = $dados;
			
		}
		
		return $assinaturas;
		
	}

	function buscaAssinaturasAditivoMigracao($contrato_id, $vigencia_ativa) {

		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_pai AS pai,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.cli_id AS cliente_id,
				tab_assinaturas.pli_id AS sistema_id,
				DATE_FORMAT(tab_assinaturas.ass_data_cadastro, '%d/%m/%Y') AS data_cadastro,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				tab_assinaturas_status.ast_id AS status_id,
				tab_assinaturas_status.ast_nome AS status_nome,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_planos.pla_parametros AS parametros,
				tab_planos.plt_id AS tipo_id,
				tab_planos.pls_id AS servico_id
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
				INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_assinaturas.ast_id IN (1, 2) AND
				rel_ass_cor_estacontido.cor_id = $contrato_id
			ORDER BY
				pai ASC,
				plano_nome ASC");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$i = count($assinaturas);

			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["servico"] = $this->buscaServicoAssinatura($dados["id"]);
			$dados["checkbox"] = "<input type=\"checkbox\" style=\"height:15;width:15\" id=\"assinaturas_{$i}\" name=\"assinaturas[{$dados["id"]}]\" value=\"{$dados["id"]}\" onclick=\"selecionaAssinatura({$i})\">";
			$dados["plano_nome"] = "<div align=\"left\"><span style=\"cursor:pointer\" onmouseover=\"this.style.textDecoration = 'underline'\" onmouseout=\"this.style.textDecoration = 'none'\" onclick=\"verAssinatura({$dados["id"]})\"><b>{$dados["plano_nome"]}</b></span><br>&raquo; <b>Data de Cadastro</b>: {$dados["data_cadastro"]}<br>&raquo; <b>Data de Ativação</b>: {$dados["data_ativacao"]}".($dados["servico"] ? "<br>&raquo; <b>Serviço:</b> {$dados["servico"]}" : "")."</div>";
			$dados["novo_plano"] = "<div align=\"left\"><select class=\"inputBrancoAzulEscuro11\" id=\"novo_plano_{$i}\" name=\"novo_plano[{$dados["id"]}]\" style=\"width:260\" disabled onchange=\"mudaNovoPlano(this, {$i})\"><option></option>".$this->geraSelect($this->buscaPlanos($dados["servico_id"], $dados["tipo_id"], $dados["plano_id"], $dados["sistema_id"], 0, $this->buscaVelocidadeMaximaCondominio($dados["cliente_id"])))."</select></div>";

			$assinaturas[] = $dados;

		}

		return $assinaturas;

	}

	function buscaVelocidadeMaximaCondominio($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_velocidade_maxima
			FROM
				tab_clientes
				INNER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
			WHERE
				tab_clientes.cli_id = $cliente_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaAssinaturasAditivoMigracaoEditavel($dados_item) {
		
		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_pai AS pai,
				tab_assinaturas.pli_id AS sistema_id,
				DATE_FORMAT(tab_assinaturas.ass_data_cadastro,'%d/%m/%Y') AS data_cadastro,
				DATE_FORMAT(tab_assinaturas.ass_data_ativacao,'%d/%m/%Y') AS data_ativacao,
				tab_assinaturas_status.ast_id AS status_id,
				tab_assinaturas_status.ast_nome AS status_nome,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_planos.pla_parametros AS parametros,
				tab_planos.plt_id AS tipo_id,
				tab_planos.pls_id AS servico_id
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_asplano_nomesinaturas.ast_id = tab_assinaturas_status.ast_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_assinaturas.ast_id IN (1, 2) AND
				rel_ass_cor_estacontido.cor_id = {$dados_item["pai"]}
			ORDER BY
				pai ASC,
				plano_nome ASC");
		
		while (($assinatura = $this->bancodedados->resultadoArray($query)) != false) {
			
			$query_migrada = $this->bancodedados->query("
				SELECT DISTINCT
					tab_assinaturas.ass_id AS id,
					tab_assinaturas.pla_id AS plano_id,
					tab_planos.pla_parametros AS parametros
				FROM
					tab_planos
					INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
					INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				WHERE
					tab_assinaturas.ast_id IN (1, 2) AND
					tab_assinaturas.ass_migrada = {$assinatura["id"]} AND
					rel_ass_cor_estacontido.cor_id = {$dados_item["id"]}");
			
			if (($migrada = $this->bancodedados->resultadoArray($query_migrada)) != false) {
				$migrada["parametros"] = unserialize($migrada["parametros"]);
				$selecionado = " checked";
			} else {
				$selecionado = "";
			}
			
			$i = count($assinaturas);

			$assinatura["parametros"] = unserialize($assinatura["parametros"]);
			$assinatura["servico"] = $this->buscaServicoAssinatura($assinatura["id"]);
			$assinatura["checkbox"] = "<input type=\"checkbox\" style=\"height:15;width:15\" id=\"assinaturas_$i\" name=\"assinaturas[{$assinatura["id"]}]\" value=\"{$assinatura["id"]}\" onclick=\"this.form.novo_plano_$i.disabled = !this.checked; this.form.migrar_descontos_$i.disabled = !this.checked; this.form.novo_plano_$i.value = ''\"$selecionado>";
			$assinatura["plano_nome"] = "<div align=\"left\"><span style=\"cursor:pointer\" onmouseover=\"this.style.textDecoration = 'underline'\" onmouseout=\"this.style.textDecoration = 'none'\" onclick=\"verAssinatura({$assinatura["id"]})\"><b>{$assinatura["plano_nome"]}</b></span><br>&raquo; <b>Data de Cadastro</b>: {$assinatura["data_cadastro"]}<br>&raquo; <b>Data de Ativação</b>: {$assinatura["data_ativacao"]}".($assinatura["servico"] ? "<br>&raquo; <b>Serviço</b>: {$assinatura["servico"]}" : "")."</div>";
			$assinatura["novo_plano"] = "<div align=\"left\"><select class=\"inputBrancoAzulEscuro11\" id=\"novo_plano_$i\" name=\"novo_plano[{$assinatura["id"]}]\" style=\"width:260\"".(($selecionado) ? "" : " disabled")." onchange=\"mudaNovoPlano(this, $i)\"><option></option>".$this->geraSelect($this->buscaPlanos($assinatura["servico_id"], $assinatura["tipo_id"], $assinatura["plano_id"], $assinatura["sistema_id"]), "{$migrada["plano_id"]},{$migrada["parametros"]["pontos_velocidade_download"]}")."</select></div>";
			
			$assinaturas[] = $assinatura;
			
		}
		
		return $assinaturas;
		
	}

	function buscaPlanos($servico_id, $tipo_id, $plano_id, $sistema_id, $velocidade_minima = 0, $velocidade_maxima = 0, $mensalidade_minima = 0) {

		$lista = array();
	
		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_id AS id,
				tab_planos.pla_nome AS nome,
				tab_planos.pla_parametros AS parametros
			FROM
				tab_planos".($mensalidade_minima != 0 ? "
				INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)" : "")."
			WHERE
				(tab_planos.plu_id = 1 OR tab_planos.pla_id = $plano_id) AND
				tab_planos.pla_tipo_migracao = 1 AND
				tab_planos.pls_id = $servico_id AND
				tab_planos.plt_id = $tipo_id AND
				(tab_planos.pli_id IS NULL OR tab_planos.pli_id = $sistema_id)".($mensalidade_minima != 0 ? " AND
				tab_assinaturas.ass_mensalidade >= $mensalidade_minima" : "")."
			ORDER BY
				tab_planos.pla_nome");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);

			if (!$dados["parametros"]["pontos_quantidade"] || ((!$velocidade_minima || ($dados["parametros"]["pontos_velocidade_download"] >= $velocidade_minima)) && (!$velocidade_maxima || ($dados["parametros"]["pontos_velocidade_download"] <= $velocidade_maxima)))) {

				if ($dados["id"] == $plano_id) {
					$dados["nome"] .= " [Atual]";
				}
				if ($dados["parametros"]["pontos_velocidade_download"] > $velocidade_maxima) {
					$dados["nome"] .= " **";
				}

				$lista["{$dados["id"]},{$dados["parametros"]["pontos_velocidade_download"]}"] = $dados["nome"];

			}

		}
		
		return $lista;
		
	}

	function existeContratoAssinatura($contrato_id, $assinatura_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				COUNT(cor_id)
			FROM
				rel_ass_cor_estacontido
			WHERE
				cor_id = $contrato_id AND
				ass_id = $assinatura_id");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function buscaDescontosAssinatura($assinatura_id, $detalhamento = false) {
	    
	    $descontos = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				dec_id AS id
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id = $assinatura_id AND
				dcs_id IN (2, 5, 6, 7)
			ORDER BY
				dec_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[] = $detalhamento ? $this->buscaDadosDesconto($dados["id"]) : $dados["id"];
		}
		
		return $descontos;
		
	}

	function buscaAjustesFinanceirosAssinatura($assinatura_id, $detalhamento = false) {
	    
	    $ajustes_financeiros = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				ajf_id AS id
			FROM
				tab_ajustes_financeiros
			WHERE
				ass_id = $assinatura_id
			ORDER BY
				ajf_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes_financeiros[] = $detalhamento ? $this->buscaDadosAjusteFinanceiro($dados["id"]) : $dados["id"];
		}
		
		return $ajustes_financeiros;
		
	}

	function buscaServicoAjustesAssinatura($assinatura_id, $detalhamento = false) {
	    
	    $servico_ajustes = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				rel_saj_ass_contem
			WHERE
				ass_id = $assinatura_id
			ORDER BY
				saj_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servico_ajustes[] = $detalhamento ? $this->buscaDadosServicoAjuste($dados["id"]) : $dados["id"];
		}
		
		return $servico_ajustes;
		
	}

	function buscaClientePontosAssinatura($assinatura_id, $detalhamento = false) {
		
		$cliente_pontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				clp_id AS id
			FROM
				tab_clientes_pontos
			WHERE
				ass_id = $assinatura_id
			ORDER BY
				clp_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cliente_pontos[] = $detalhamento ? $this->buscaDadosClientePonto($dados["id"]) : $dados["id"];
		}
		
		return $cliente_pontos;
		
	}

	function buscaClienteAutenticacoesAssinatura($assinatura_id, $detalhamento = false) {
		
		$cliente_autenticacoes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cau_id AS id
			FROM
				tab_clientes_autenticacoes
			WHERE
				ass_id = $assinatura_id
			ORDER BY
				cau_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cliente_autenticacoes[] = $detalhamento ? $this->buscaDadosClienteAutenticacao($dados["id"]) : $dados["id"];
		}
		
		return $cliente_autenticacoes;
		
	}

	function buscaClienteEmailsAssinatura($assinatura_id) {
	    
	    $cliente_emails = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				cle_id AS id
			FROM
				tab_clientes_emails
			WHERE
				ass_id = $assinatura_id
			ORDER BY
				cle_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$cliente_emails[] = $dados["id"];
		}
		
		return $cliente_emails;
		
	}

	function buscaServicoAjustesClientePonto($ponto_id, $detalhamento = false) {
	    
	    $servico_ajustes = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				rel_saj_clp_contem
			WHERE
				clp_id = $ponto_id
			ORDER BY
				saj_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servico_ajustes[] = $detalhamento ? $this->buscaDadosServicoAjuste($dados["id"]) : $dados["id"];
		}
		
		return $servico_ajustes;
		
	}

	function buscaServicoAjustesClienteAutenticacao($autenticacao_id, $detalhamento = false) {
	    
	    $servico_ajustes = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				rel_saj_cau_contem
			WHERE
				cau_id = $autenticacao_id
			ORDER BY
				saj_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servico_ajustes[] = $detalhamento ? $this->buscaDadosServicoAjuste($dados["id"]) : $dados["id"];
		}
		
		return $servico_ajustes;
		
	}

	function buscaComentariosContrato($contrato_id, $detalhamento = false) {
	    
	    $comentarios = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				coo_id AS id
			FROM
				tab_contratos_comentarios
			WHERE
				cor_id = $contrato_id
			ORDER BY
				coo_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$comentarios[] = $detalhamento ? $this->buscaDadosComentario($dados["id"]) : $dados["id"];
		}
		
		return $comentarios;
		
	}

	function buscaAssinaturasContrato($contrato_id, $detalhamento = false) {
	    
	    $assinaturas = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.ass_mensalidade AS mensalidade
			FROM
				tab_assinaturas
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id
			ORDER BY
				tab_planos.plt_id,
				tab_assinaturas.ass_pai,
				tab_assinaturas.ass_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $detalhamento ? $this->buscaDadosAssinatura($dados["id"]) : $dados["id"];
		}
		
		return $assinaturas;
		
	}

	function buscaDescontosContrato($contrato_id, $detalhamento = false) {
	    
	    $descontos = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id
			FROM
				rel_dec_cor_estacontido
				INNER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.dec_id = rel_dec_cor_estacontido.dec_id)
			WHERE
				cor_id = $contrato_id AND
				dcs_id IN (2, 5, 6, 7)
			ORDER BY
				tab_descontos_concessoes.dec_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[] = $detalhamento ? $this->buscaDadosDesconto($dados["id"]) : $dados["id"];
		}
		
		return $descontos;
		
	}

	function buscaAditivosContrato($contrato_id, $detalhamento = false) {
	    
	    $aditivos = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				cor_id AS id
			FROM
				tab_contratos
			WHERE
				cor_pai = $contrato_id
			ORDER BY
				cor_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$aditivos[] = $detalhamento ? $this->buscaDadosContrato($dados["id"]) : $dados["id"];
		}
		
		return $aditivos;
		
	}

	function buscaVisitasContrato($contrato_id, $detalhamento = false) {
	    
	    $visitas = array();
	    
		$query = $this->bancodedados->query("
			SELECT
				vis_id AS id
			FROM
				tab_visitas
			WHERE
				cor_id = $contrato_id
			ORDER BY
				vis_id ASC");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$visitas[] = $detalhamento ? $this->buscaDadosVisita($dados["id"]) : $dados["id"];
		}
		
		return $visitas;
		
	}

	function buscaEmailsAssinatura($assinatura_id, $status = "1, 2, 3, 4") {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_emails.cle_id AS id,
				tab_clientes_emails.ces_id AS status_id,
				tab_clientes_emails.cle_usuario AS login,
				tab_dominios.dom_dominio AS dominio,
				tab_assinaturas.cli_id AS cliente_id,
				tab_planos.pla_id AS plano_id,
				tab_planos.pls_id AS servico_id
			FROM
				tab_clientes_emails
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
				INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				tab_clientes_emails.ass_id = $assinatura_id AND
				tab_clientes_emails.ces_id IN ($status)");
				
		return $this->bancodedados->resultadoArrayTotal($query);
		
	}

	function buscaAjusteAssinatura($assinatura_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				saj_id AS id
			FROM
				tab_servicos_ajustes
			WHERE
				ass_id = $assinatura_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaFormasPagamento($plano_id, $fidelidade, $multa) {

		$formas_pagamento = array();

		$query = $this->bancodedados->query("
			SELECT
				fop_id AS id,
				fop_entrada AS entrada,
				fop_parcelas AS parcelas,
				fop_valor_parcela AS valor_parcela,
				fop_entrada + (fop_valor_parcela * fop_parcelas) AS valor_total,
				fop_multa AS multa,
				fop_fidelidade AS fidelidade,
				fpc_nome AS categoria,
				fpf_nome AS forma
			FROM
				tab_formas_pagamento
				INNER JOIN tab_formas_pagamento_categorias ON tab_formas_pagamento.fpc_id = tab_formas_pagamento_categorias.fpc_id
				LEFT OUTER JOIN tab_formas_pagamento_formas ON tab_formas_pagamento.fpf_id = tab_formas_pagamento_formas.fpf_id
			WHERE
				tab_formas_pagamento.pla_id = $plano_id AND
				tab_formas_pagamento.fpc_id != 5
			ORDER BY
				tab_formas_pagamento.fpc_id,
				tab_formas_pagamento.fop_parcelas,
				tab_formas_pagamento.fop_entrada");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["fidelidade"] = $dados["fidelidade"] < $fidelidade ? $fidelidade : $dados["fidelidade"];
			$dados["multa"] += $multa;

			$formas_pagamento[] = $dados;

		}

		return $formas_pagamento;

	}

	function buscaFormasPagamentoServico($servico_id) {

		$formas_pagamento = array();

		$query = $this->bancodedados->query("
			SELECT
				fop_id AS id,
				fop_entrada AS entrada,
				fop_parcelas AS parcelas,
				fop_valor_parcela AS valor_parcela,
				fop_multa AS multa,
				fop_fidelidade AS fidelidade,
				fpc_nome AS categoria,
				fpf_nome AS forma
			FROM
				tab_formas_pagamento
				INNER JOIN tab_formas_pagamento_categorias ON tab_formas_pagamento.fpc_id = tab_formas_pagamento_categorias.fpc_id
				LEFT OUTER JOIN tab_formas_pagamento_formas ON tab_formas_pagamento.fpf_id = tab_formas_pagamento_formas.fpf_id
			WHERE
				pls_id = $servico_id
			ORDER BY
				tab_formas_pagamento.fpc_id,
				tab_formas_pagamento.fop_parcelas,
				tab_formas_pagamento.fop_entrada");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$formas_pagamento[] = $dados;
		}

		return $formas_pagamento;

	}

	function possuiDesconto($assinatura_id, $desconto_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_descontos_concessoes.dec_id)
			FROM
				tab_assinaturas
				INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id AND
				tab_descontos_concessoes.des_id = $desconto_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaVelocidadePlano($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				pla_nome AS plano
			FROM
				tab_planos
				INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["plano"] = trim(preg_replace("/\\([^\\)]*\\)/", "", $dados["plano"]));
			$dados["plano"] = trim(str_replace(array("Acesso Internet", "Pós Venda", "Pos Venda", "Ponto Principal", "Corujão", "Olé Banda Larga", "Olé Total", "Ponto Adicional", "Super Condomínio"), "", $dados["plano"]));
			$dados["plano"] = $this->corrigePlano($dados["plano"]);

			return $dados["plano"];

		}

	}

	function buscaEquipamentosContratoIds($contrato_id) {

	    $equipamentos = array();

		$query = $this->bancodedados->query("
			SELECT
				equ_id AS id
			FROM
				tab_clientes_comodatos
			WHERE
				cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$equipamentos[] = $dados["id"];
		}

		return $equipamentos;

	}

	function buscaEquipamentosContratoNomes($contrato_id) {

	    $equipamentos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_equipamentos.equ_nome AS nome
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
			WHERE
				tab_clientes_comodatos.cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$equipamentos[] = $dados["nome"];
		}

		return $equipamentos;

	}
	function buscaPlanosContrato($contrato_id) {

	    $planos = array();

		$query = $this->bancodedados->query("
			SELECT
				pla_nome AS plano
			FROM
				rel_ass_cor_estacontido
				INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
			WHERE
				cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$planos[] = $dados["plano"];
		}

		return implode(", ", $planos);

	}

	function buscaSistema($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				pli_id
			FROM
				rel_ass_cor_estacontido
				INNER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			WHERE
			 	rel_ass_cor_estacontido.cor_id = $contrato_id
			LIMIT
				1");

		return $this->bancodedados->resultado($query);

	}

	function buscaFormasPagamentoContrato($contrato_id, $multa, $fidelidade) {

		$formas_pagamento = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_formas_pagamento.fop_id AS id,
				tab_formas_pagamento.fop_entrada AS entrada,
				tab_formas_pagamento.fop_parcelas AS parcelas,
				tab_formas_pagamento.fop_valor_parcela AS valor_parcela,
				(tab_formas_pagamento.fop_entrada + (tab_formas_pagamento.fop_valor_parcela * tab_formas_pagamento.fop_parcelas)) AS valor_total,
				tab_formas_pagamento.fop_multa AS multa_original,
				tab_formas_pagamento.fop_fidelidade AS fidelidade_original,
				tab_formas_pagamento.pla_id AS plano_id,
				tab_formas_pagamento.pls_id AS servico_id,
				tab_formas_pagamento_categorias.fpc_id AS categoria_id,
				tab_formas_pagamento_categorias.fpc_nome AS categoria,
				tab_formas_pagamento_formas.fpf_id AS forma_id,
				tab_formas_pagamento_formas.fpf_nome AS forma
			FROM
				tab_formas_pagamento
				INNER JOIN tab_formas_pagamento_categorias ON tab_formas_pagamento.fpc_id = tab_formas_pagamento_categorias.fpc_id
				LEFT OUTER JOIN tab_formas_pagamento_formas ON tab_formas_pagamento.fpf_id = tab_formas_pagamento_formas.fpf_id
			WHERE
				fop_id IN (SELECT fop_id FROM rel_fop_cor_estacontido WHERE cor_id = $contrato_id)
			ORDER BY
				tab_formas_pagamento.fpc_id,
				tab_formas_pagamento.fop_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["entrada"] = (int) $dados["entrada"];
			$dados["parcelas"] = (int) $dados["parcelas"];
			$dados["valor_parcela"] = (int) $dados["valor_parcela"];
			$dados["valor_total"] = (int) $dados["valor_total"];
			$dados["fidelidade"] = $dados["fidelidade_original"] < $fidelidade ? $fidelidade : $dados["fidelidade_original"];
			$dados["multa"] = $dados["multa_original"] + $multa;

			$formas_pagamento[] = $dados;

		}

		return $formas_pagamento;

	}

	function buscaVigenciaAtivaFilhos($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(tab_contratos.cor_data_ativacao, '%d/%m/%Y') AS data_ativacao,
				DATE_FORMAT(DATE_SUB(DATE_ADD(tab_contratos.cor_data_ativacao, INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), '%d/%m/%Y') AS vigencia_expiracao,
				IF(tab_contratos.cor_termino_fidelidade >= CURDATE(), 1, 0) AS vigencia_ativa,
				tab_contratos.cor_vigencia AS vigencia,
				tab_contratos.cor_multa AS multa,
				tab_contratos.cor_termino_fidelidade AS termino_fidelidade
			FROM
				tab_contratos
			WHERE
				tab_contratos.cor_pai = $contrato_id AND
				tab_contratos.crs_id != 24
			ORDER BY
				tab_contratos.cor_id DESC
			LIMIT
				1");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaVigenciaExpiracao($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(DATE_SUB(DATE_ADD(tab_contratos.cor_data_ativacao, INTERVAL tab_contratos.cor_vigencia MONTH), INTERVAL 1 DAY), '%d/%m/%Y')
			FROM
				tab_contratos
			WHERE
				tab_contratos.cor_id = $contrato_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaContratosVinculos($contrato_id) {

		$vinculos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos_vinculos.cov_id AS id,
				tab_contratos_vinculos.cor_filho AS contrato_id,
				tab_contratos_vinculos.pvt_id AS vinculo_tipo_id,
				tab_contratos.cor_codigo AS codigo,
				tab_contratos.cor_sincronizado AS sincronizado,
				tab_contratos_status.crs_id AS status_id,
				tab_contratos_status.crs_nome AS status_nome,
				tab_planos_servicos.pls_nome AS servico_nome,
				tab_planos_servicos.pls_parametros AS servico_parametros,
				tab_contratos_tipos.crt_id AS tipo_id,
				tab_contratos_tipos.crt_nome AS tipo_nome,
				tab_contratos_tipos.crt_parametros AS tipo_parametros
			FROM
				tab_contratos_vinculos
				INNER JOIN tab_contratos ON (tab_contratos.cor_id = tab_contratos_vinculos.cor_filho)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
				INNER JOIN tab_contratos_tipos ON (tab_contratos_tipos.crt_id = tab_contratos.crt_id)
				INNER JOIN tab_contratos_status ON (tab_contratos_status.crs_id = tab_contratos.crs_id)
			WHERE
				tab_contratos_vinculos.cor_id = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["servico_parametros"] = unserialize($dados["servico_parametros"]);
			$dados["tipo_parametros"] = unserialize($dados["tipo_parametros"]);

			$vinculos[] = $dados;

		}

		return $vinculos;

	}

	function ehSincronizado($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				cor_sincronizado
			FROM
				tab_contratos
			WHERE
				cor_id = $contrato_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaContratosVinculosPai($contrato_id) {

		$vinculos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_contratos_vinculos.cov_id AS id,
				tab_contratos_vinculos.cor_id AS contrato_id,
				tab_contratos_vinculos.pvt_id AS vinculo_tipo_id,
				tab_contratos.cor_codigo AS codigo,
				tab_contratos.cor_sincronizado AS sincronizado,
				tab_contratos_status.crs_id AS status_id,
				tab_contratos_status.crs_nome AS status_nome,
				tab_planos_servicos.pls_nome AS servico_nome,
				tab_planos_servicos.pls_parametros AS servico_parametros,
				tab_contratos_tipos.crt_id AS tipo_id,
				tab_contratos_tipos.crt_nome AS tipo_nome,
				tab_contratos_tipos.crt_parametros AS tipo_parametros
			FROM
				tab_contratos_vinculos
				INNER JOIN tab_contratos ON (tab_contratos.cor_id = tab_contratos_vinculos.cor_id)
				INNER JOIN tab_planos_servicos ON (tab_planos_servicos.pls_id = tab_contratos.pls_id)
				INNER JOIN tab_contratos_tipos ON (tab_contratos_tipos.crt_id = tab_contratos.crt_id)
				INNER JOIN tab_contratos_status ON (tab_contratos_status.crs_id = tab_contratos.crs_id)
			WHERE
				cor_filho = $contrato_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		    
			$dados["servico_parametros"] = unserialize($dados["servico_parametros"]);
			$dados["tipo_parametros"] = unserialize($dados["tipo_parametros"]);
			
			$vinculos[] = $dados;
			
		}

		return $vinculos;

	}

	function buscaContratosAutoAtivaveis($cliente_id) {

		$contratos = array();

		$query = $this->bancodedados->query("
			SELECT
				cor_id AS id
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				crs_id IN (17) AND
				cor_pai IS NULL AND
				pls_id = 21");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}

		return $contratos;

	}

	function buscaContratosAutoCancelaveis($cliente_id) {

		$contratos = array();

		$query = $this->bancodedados->query("
			SELECT
				cor_id AS id
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				crs_id IN (19, 20) AND
				cor_pai IS NULL AND
				pls_id = 21");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}

		return $contratos;

	}

	function buscaDadosUltimoContrato($cliente_id, $contrato_id, $contrato_filho_id = null) {

		$where = array();

		$where[] = "cli_id = $cliente_id";
		$where[] = "(cor_id = $contrato_id OR cor_pai = $contrato_id)";
		if ($contrato_filho_id) $where[] = "cor_id != $contrato_filho_id";
		$where[] = "crt_id IN (11, 12, 13)";

		$where = implode(" AND\n				", $where);

		$query = $this->bancodedados->query("
			SELECT
				MAX(cor_id)
			FROM
				tab_contratos
			WHERE
				$where");

		if (($contrato_id = $this->bancodedados->resultado($query)) != false) {
			return $this->buscaDadosContrato($contrato_id);
		}

	}

	function buscaDadosCliente($cliente_id) {
		
		$cliente_id = (int) preg_replace("/[^0-9]/", "", $cliente_id);

	    $lista_telefones = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_clientes.cli_nascimento AS nascimento,
				tab_clientes.cli_complemento AS complemento,
				tab_clientes.cli_ponto_referencia AS ponto_referencia,
				tab_clientes.cli_numero AS numero,
				tab_clientes.cep_id AS cep_id,
				tab_clientes.clt_id AS tipo,
				tab_clientes.coo_id AS operadora_origem_id,
				tab_clientes.cls_id AS status,
				tab_clientes.usu_id AS responsavel,
				tab_clientes.clo_id AS origem_id,
				tab_clientes.con_id AS condominio_id,
				tab_clientes.cob_id AS bloco_id,
				tab_clientes.cli_ccd_vencimento AS vencimento,
				tab_clientes.cof_id AS formato_id,
				tab_clientes.cli_pai AS cliente_pai_id
			FROM
				tab_clientes
			WHERE
				tab_clientes.cli_id = '{$cliente_id}'");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["emails"] = $this->buscaContatosEmails($dados["id"]);
			$dados["telefones"] = $this->buscaContatosTelefones($dados["id"]);

			if ($dados["telefones"]) {
				foreach ($dados["telefones"] as $dados_telefone) {
					$telefone = "({$dados_telefone["contato_ddd"]}) {$dados_telefone["contato_numero"]}";
					if ($dados_telefone["contato_ramal"]) {
						$telefone .= " (Ramal {$dados_telefone["contato_ramal"]})";
					}
					$lista_telefones[] = $telefone;
				}
				$dados["lista_telefones"] = implode(" / ", $lista_telefones);
			}

			$dados = funcoes::mescla_arrays($dados, $this->buscaDadosAprovacaoFinanceira($cliente_id));

			return $dados;

		}

	}

	function buscaDadosAprovacaoFinanceira($cliente_id) {

		$dados_aprovacao = array();

		$query = $this->bancodedados->query("
			SELECT
				ccc_restricoes AS restricoes
			FROM
				tab_clientes_consultas_credito
			WHERE
				ccc_data_hora >= '".date("Y-m-d", strtotime(FINANCEIRO_VERIFICACAO_VALIDADE." DAYS AGO"))."' AND
				ccc_cpf_cnpj = (
					SELECT
						cli_cpf_cnpj
					FROM
						tab_clientes
					WHERE
						cli_id = $cliente_id
				)
			ORDER BY
				ccc_data_hora DESC
			LIMIT
				1");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			if ($dados["restricoes"] > 0) {
				$dados_aprovacao["auto_aprovado_financeiro"] = false;
				$dados_aprovacao["auto_reprovado_financeiro"] = true;
				$dados_aprovacao["auto_reprovado_cadastro"] = $dados["restricoes"] > FINANCEIRO_LIMITE_RESTRICOES;
			} else {
				$dados_aprovacao["auto_aprovado_financeiro"] = true;
				$dados_aprovacao["auto_reprovado_financeiro"] = false;
				$dados_aprovacao["auto_reprovado_cadastro"] = false;
			}

			return $dados_aprovacao;

		}

		$dados_aprovacao["auto_aprovado_financeiro"] = false;
		$dados_aprovacao["auto_reprovado_financeiro"] = false;

		return $dados_aprovacao;

	}

	function buscaDadosMensagensLotes($lote_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_mensagens_lotes.mlm_id AS modelo_id,
				tab_mensagens_lotes.mel_parametros AS parametros
			FROM
				tab_mensagens_lotes
			WHERE
				tab_mensagens_lotes.mel_id = $lote_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

	}

	function buscaDadosCadastraisCliente($cliente_id) {

		$query = $this->bancodedados->query("
 			SELECT
 				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				DATE_FORMAT(tab_clientes.cli_data_cadastro, '%d/%m/%Y') AS data_cadastro,
				tab_clientes.cli_ccd_vencimento AS cobranca_vencimento,
				tab_clientes.cof_id AS cobranca_formato,
				tab_clientes.cli_ccd_debito_agencia AS cobranca_debito_agencia,
				tab_clientes.cli_ccd_debito_conta AS cobranca_debito_conta,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_clientes.cep_id AS cep_id,
				DATE_FORMAT(tab_clientes.cli_nascimento, '%d/%m/%Y') AS nascimento,
				INSERT(LPAD(IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)), 8, '0'), 6, 0, '-') AS cep,
				IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
				SUBSTRING(tab_clientes.cli_complemento, 1, 1) AS complemento_tipo_id,
				SUBSTRING(tab_clientes.cli_complemento, 2) AS complemento_valor,
				tab_clientes.cli_contato AS contato,
				tab_clientes.clt_id AS tipo,
				tab_condominios_blocos.cob_id AS bloco_id,
				tab_condominios_blocos.cob_nome AS bloco_nome,
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
			WHERE
				tab_clientes.cli_id = $cliente_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		    
			$dados["emails"] = $this->buscaContatosEmails($dados["id"]);
			$dados["telefones"] = $this->buscaContatosTelefones($dados["id"]);
			$dados["numero"] = $dados["numero"] ? $dados["numero"] : "";
			
			$dados = funcoes::mescla_arrays($dados, $this->buscaDadosAprovacaoFinanceira($dados["id"]));

			return $dados;
			
		}

	}

	function buscaDadosClienteEnderecoCobranca($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_id AS condominio_id,
				tab_condominios.con_nome AS condominio_nome,
				tab_condominios_blocos.cob_id AS bloco_id,
				tab_condominios_blocos.cob_nome AS bloco_nome,
				SUBSTRING(tab_clientes_cobranca_dados.ccd_complemento, 1, 1) AS complemento_id,
				SUBSTRING(tab_clientes_cobranca_dados.ccd_complemento, 2) AS complemento,
				IF(tab_clientes_cobranca_dados.cep_id IS NULL, IF(tab_condominios_blocos.cep_id IS NULL, tab_condominios.con_numero, tab_condominios_blocos.cob_numero), tab_clientes_cobranca_dados.ccd_numero) AS numero,
				CONCAT(SUBSTRING(tab_ceps.cep_id, 1, 5), '-', SUBSTRING(tab_ceps.cep_id, 6, 3)) AS cep,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_nome AS estado
			FROM
				tab_clientes_cobranca_dados
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_clientes_cobranca_dados.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes_cobranca_dados.cli_id = $cliente_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["numero"] = $dados["numero"] ? $dados["numero"] : "";
			$dados["mesmo_endereco"] = "";
			return $dados;
		} elseif (($dados = $this->buscaDadosClienteEndereco($cliente_id)) != false) {
			$dados["mesmo_endereco"] = "checked";
			return $dados;
		}

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
				CONCAT(SUBSTRING(tab_ceps.cep_id, 1, 5), '-', SUBSTRING(tab_ceps.cep_id, 6, 3)) AS cep,
				tab_logradouros.log_nome AS logradouro,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_nome AS estado
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
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

	function buscaDadosServico($servico_id) {

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS id,
				pls_nome AS nome,
				pls_parametros AS parametros
			FROM
				tab_planos_servicos
			WHERE
				pls_id = $servico_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

	}

	function buscaDadosTipo($contrato_tipo_id) {

		$query = $this->bancodedados->query("
			SELECT
				crt_id AS id,
				crt_nome AS nome,
				crt_parametros AS parametros
			FROM
				tab_contratos_tipos
			WHERE
				crt_id = $contrato_tipo_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}

	}

	function buscaDadosUsuario($usuario_id) {

		if (!$usuario_id) {
			return array(
				"id" => 0,
				"nome" => "Sistema"
			);
		}

		$query = $this->bancodedados->query("
			SELECT
				usu_id AS id,
				usu_nome AS nome
			FROM
				tab_usuarios
			WHERE
				usu_id = '$usuario_id'");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas.ass_id AS id,
				tab_assinaturas.cli_id AS cliente,
				tab_assinaturas.ass_instalacao AS instalacao,
				tab_assinaturas.ass_mensalidade AS mensalidade,
				tab_assinaturas.ass_vigencia AS vigencia,
				tab_assinaturas.ass_multa AS multa,
				tab_assinaturas.ass_pai AS pai,
				tab_assinaturas.ass_migrada AS migrada,
				tab_assinaturas.ass_data_ativacao AS data_ativacao,
				tab_assinaturas.ass_data_fechamento AS data_fechamento,
				tab_planos.pla_parametros AS parametros,
				tab_planos.pla_id AS plano_id,
				tab_planos.pla_nome AS plano_nome,
				tab_planos.pli_id AS plano_sistema_id,
				tab_planos.sac_id AS plano_categoria_ajuste_id,
				tab_planos_tipos.plt_id AS plano_tipo_id,
				tab_planos_tipos.plt_id AS plano_tipo_nome,
				tab_planos_servicos.pls_id AS plano_servico_id,
				tab_planos_servicos.pls_nome AS plano_servico_nome,
				tab_planos_servicos.pls_parametros AS plano_servico_parametros,
				tab_assinaturas_status.ast_id AS status_id,
				tab_assinaturas_status.ast_nome AS status_nome,
				tab_planos_sistemas.pli_id AS sistema_id,
				tab_planos_sistemas.pli_nome AS sistema_nome
			FROM
				tab_assinaturas
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_planos_tipos ON (tab_planos.plt_id = tab_planos_tipos.plt_id)
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_planos_sistemas ON (tab_planos_sistemas.pli_id = tab_assinaturas.pli_id)
			WHERE
				tab_assinaturas.ass_id = $assinatura_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["instalacao_formatado"] = $this->formataDinheiro($dados["instalacao"]);
			$dados["mensalidade_formatado"] = $this->formataDinheiro($dados["mensalidade"]);
			$dados["vigencia_formatado"] = $dados["vigencia"] ? $dados["vigencia"].($dados["vigencia"] > 1 ? " meses" : " mês") : "-";
			$dados["multa_formatado"] = $dados["multa"] ? $this->formataDinheiro($dados["multa"]) : "-";
			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["plano_servico_parametros"] = unserialize($dados["plano_servico_parametros"]);
			$dados["descontos"] = $this->buscaDescontosAssinatura($dados["id"], true);
			$dados["cliente_pontos"] = $this->buscaClientePontosAssinatura($dados["id"], true);
			$dados["cliente_autenticacoes"] = $this->buscaClienteAutenticacoesAssinatura($dados["id"], true);
			$dados["cliente_emails"] = $this->buscaClienteEmailsAssinatura($dados["id"]);
			$dados["servico_ajustes"] = $this->buscaServicoAjustesAssinatura($dados["id"], true);
			$dados["ajustes_financeiros"] = $this->buscaAjustesFinanceirosAssinatura($dados["id"], true);

			if (in_array($dados["status_id"], array(2, 5))) {
				$dados["servico"] = $this->buscaServicoAssinatura($dados["id"]);
			} elseif ($dados["migrada"]) {
				$dados["servico"] = $this->buscaServicoAssinatura($dados["migrada"]);
			}

			return $dados;

		}

	}

	function buscaPlanoAssinatura($assinatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				pla_nome
			FROM
				tab_assinaturas
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				ass_id = $assinatura_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaDadosPlano($plano_id) {

		$query = $this->bancodedados->query("
			SELECT
				pla_instalacao AS instalacao,
				pla_mensalidade AS mensalidade,
				sac_id AS categoria_ajuste_id,
				pla_parametros AS parametros,
				pls_id AS servico_id,
				pli_id AS sistema_id,
				pla_tipo_migracao AS tipo_migracao,
				pla_tipo_contrato_principal AS tipo_principal
			FROM
				tab_planos
			WHERE
				pla_id = $plano_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["vinculos"] = $this->buscaPlanosVinculos($plano_id);
			return $dados;
		}

	}

	function buscaPlanosVinculos($plano_id) {

		$vinculos = array();

		$query = $this->bancodedados->query("
			SELECT
				pls_id AS servico_id,
				pla_filho AS plano_id,
				pva_id AS aplicacao_id,
				pvt_id AS tipo_id
			FROM
				tab_planos_vinculos
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_planos_vinculos.pla_filho)
			WHERE
				tab_planos_vinculos.pla_id = $plano_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$vinculos[] = $dados;
		}

		return $vinculos;

	}

	function buscaDadosDesconto($desconto_concessao_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_descontos_concessoes.dec_id AS id,
				tab_descontos_concessoes.ass_id AS assinatura,
				tab_descontos_concessoes.con_id AS condominio,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_cadastro, '%d/%m/%Y') AS data_cadastro,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_inicio, '%d/%m/%Y') AS data_inicio,
				DATE_FORMAT(tab_descontos_concessoes.dec_data_termino, '%d/%m/%Y') AS data_termino,
				tab_descontos_concessoes.dec_instalacao AS instalacao,
				tab_descontos_concessoes.dec_mensalidade AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos.des_id AS desconto_id,
				tab_descontos.des_nome AS desconto_nome,
				tab_descontos.des_parametros AS parametros,
				tab_descontos_tipos.det_id AS desconto_tipo_id,
				tab_descontos_tipos.det_nome AS desconto_tipo_nome,
				tab_descontos_concessoes_status.dcs_id AS status_id,
				tab_descontos_concessoes_status.dcs_nome AS status_nome
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN tab_descontos_tipos ON (tab_descontos_concessoes.det_id = tab_descontos_tipos.det_id)
				INNER JOIN tab_descontos_concessoes_status ON (tab_descontos_concessoes.dcs_id = tab_descontos_concessoes_status.dcs_id)
			WHERE
				tab_descontos_concessoes.dec_id = $desconto_concessao_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["instalacao_formatado"] = $this->formataDescontoValor($dados["instalacao"]);
			$dados["mensalidade_formatado"] = $this->formataDescontoValor($dados["mensalidade"]);

			if ($dados["status_id"] <= 4) {
				$dados["validade"] = $this->formataDescontoValidade($dados["validade"], $dados["data_termino"]);
			} else {
				$dados["validade"] = $dados["data_termino"] ? $dados["data_termino"] : "Indeterminada";
			}

			return $dados;

		}

	}

	function buscaDadosAjusteFinanceiro($ajuste_financeiro_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_ajustes_financeiros.ajf_id AS id,
				tab_ajustes_financeiros.ajf_datahora AS datahora,
				tab_ajustes_financeiros.cli_id AS cliente,
				tab_ajustes_financeiros.ass_id AS assinatura,
				tab_ajustes_financeiros_categorias.afc_id AS categoria_id,
				tab_ajustes_financeiros_categorias.afc_nome AS categoria_nome,
				tab_ajustes_financeiros_tipos.aft_id AS tipo_id,
				tab_ajustes_financeiros_tipos.aft_nome AS tipo_nome,
				tab_ajustes_financeiros.ajf_valor AS valor,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_inicio,'%m/%Y') AS inicio,
				DATE_FORMAT(tab_ajustes_financeiros.ajf_competencia_termino,'%m/%Y') AS termino,
				tab_ajustes_financeiros_status.afs_id AS status_id,
				tab_ajustes_financeiros_status.afs_nome AS status_nome
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
				INNER JOIN tab_ajustes_financeiros_tipos ON (tab_ajustes_financeiros.aft_id = tab_ajustes_financeiros_tipos.aft_id)
				INNER JOIN tab_ajustes_financeiros_status ON (tab_ajustes_financeiros.afs_id = tab_ajustes_financeiros_status.afs_id)
			WHERE
				tab_ajustes_financeiros.ajf_id = $ajuste_financeiro_id");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosClientePonto($ponto_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.ass_id AS assinatura,
				tab_clientes_pontos.clp_ip AS endereco_ip,
				tab_clientes_pontos.clp_endereco_mac AS endereco_mac,
				tab_clientes_pontos.clp_velocidade_download AS velocidade_download,
				tab_clientes_pontos.clp_velocidade_upload AS velocidade_upload,
				tab_clientes_pontos.clp_publicado AS publicado,
				tab_clientes_pontos.clp_ajustado AS ajustado,
				tab_clientes_pontos_status.cps_id AS status_id,
				tab_clientes_pontos_status.cps_nome AS status_nome
			FROM
				tab_clientes_pontos
				INNER JOIN tab_clientes_pontos_status ON (tab_clientes_pontos.cps_id = tab_clientes_pontos_status.cps_id)
				LEFT OUTER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_clientes_pontos.clp_id = $ponto_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["servico_ajustes"] = $this->buscaServicoAjustesClientePonto($dados["id"], true);
			return $dados;
		}

	}

	function buscaDadosClienteAutenticacao($autenticacao_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_autenticacoes.cau_id AS id,
				tab_clientes_autenticacoes.ass_id AS assinatura,
				tab_clientes_autenticacoes.cau_endereco_ip AS endereco_ip,
				tab_clientes_autenticacoes.cau_velocidade_download AS velocidade_download,
				tab_clientes_autenticacoes.cau_velocidade_upload AS velocidade_upload,
				tab_clientes_autenticacoes.cau_publicado AS publicado,
				tab_clientes_autenticacoes.cau_ajustado AS ajustado,
				tab_clientes_autenticacoes_status.cas_id AS status_id,
				tab_clientes_autenticacoes_status.cas_nome AS status_nome
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_clientes_autenticacoes_status ON (tab_clientes_autenticacoes.cas_id = tab_clientes_autenticacoes_status.cas_id)
			WHERE
				tab_clientes_autenticacoes.cau_id = $autenticacao_id");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["servico_ajustes"] = $this->buscaServicoAjustesClienteAutenticacao($dados["id"], true);
			return $dados;
		}

	}

	function buscaDadosServicoAjuste($ajuste_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_servicos_ajustes.saj_id AS id,
				tab_servicos_ajustes.cli_id AS cliente,
				DATE_FORMAT(tab_servicos_ajustes.saj_inicio, '%d/%m/%Y (%H:%i)') AS inicio,
				DATE_FORMAT(tab_servicos_ajustes.saj_termino, '%d/%m/%Y (%H:%i)') AS termino,
				tab_servicos_ajustes_categorias.sac_id AS categoria_id,
				tab_servicos_ajustes_categorias.sac_nome AS categoria_nome,
				tab_servicos_ajustes_status.sas_id AS status_id,
				tab_servicos_ajustes_status.sas_nome AS status_nome,
				tab_servicos_ajustes.saj_pai AS pai,
				tab_servicos_ajustes.ass_id AS assinatura_id
			FROM
				tab_servicos_ajustes
				INNER JOIN tab_servicos_ajustes_categorias ON (tab_servicos_ajustes.sac_id = tab_servicos_ajustes_categorias.sac_id)
				INNER JOIN tab_servicos_ajustes_status ON (tab_servicos_ajustes.sas_id = tab_servicos_ajustes_status.sas_id)
			WHERE
				tab_servicos_ajustes.saj_id = $ajuste_id");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaDadosComentario($comentario_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_contratos_comentarios.coo_id AS id,
				tab_contratos_comentarios.cor_id AS contrato,
				DATE_FORMAT(tab_contratos_comentarios.coo_datahora, '%d/%m/%Y (%H:%i)') AS data_hora,
				tab_contratos_comentarios_categorias.ccc_nome AS categoria,
				tab_contratos_comentarios.coo_conteudo AS comentario,
				tab_usuarios.usu_id AS responsavel_id,
				IF(tab_usuarios.usu_nome IS NULL, 'Sistema', tab_usuarios.usu_nome) AS responsavel_nome
			FROM
				tab_contratos_comentarios
				INNER JOIN tab_contratos_comentarios_categorias ON (tab_contratos_comentarios.ccc_id = tab_contratos_comentarios_categorias.ccc_id)
				LEFT OUTER JOIN tab_usuarios ON (tab_contratos_comentarios.usu_id = tab_usuarios.usu_id)
			WHERE
				tab_contratos_comentarios.coo_id = $comentario_id");
		
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		    
			$dados["conteudo"] = "<b>Ação</b>: {$dados["categoria"]}";
			if ($dados["comentario"]) {
				if (substr($dados["comentario"], 0, 3) == "<b>") {
					$dados["conteudo"] .= "<br>{$dados["comentario"]}";
				} else {
					$dados["conteudo"] .= "<br><b>Comentário</b>: {$dados["comentario"]}";
				}
			}
			
			return $dados;
			
		}
		
	}	

	function buscaDadosVisita($visita_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_visitas.vis_id AS id,
				tab_visitas.cli_id AS cliente,
				DATE_FORMAT(tab_visitas.vis_data,'%d/%m/%Y') AS data,
				tab_visitas_periodos.vip_id AS periodo_id,
				tab_visitas_periodos.vip_nome AS periodo_nome,
				tab_visitas_tipos.vit_id AS tipo_id,
				tab_visitas_tipos.vit_nome AS tipo_nome,
				tab_visitas.vis_agendamentos AS agendamentos,
				tab_visitas_status.via_id AS status_id,
				tab_visitas_status.via_nome AS status_nome,
				tab_visitas.cor_id AS contrato
			FROM
				tab_visitas
				LEFT OUTER JOIN tab_visitas_periodos ON (tab_visitas.vip_id = tab_visitas_periodos.vip_id)
				LEFT OUTER JOIN tab_visitas_tipos ON (tab_visitas.vit_id = tab_visitas_tipos.vit_id)
				LEFT OUTER JOIN tab_visitas_status ON (tab_visitas.via_id = tab_visitas_status.via_id)
			WHERE
				tab_visitas.vis_id = $visita_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function buscaDadosCategoriaAjuste($categoria_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				sac_regras as regras,
				sac_editavel as editavel
			FROM
				tab_servicos_ajustes_categorias
			WHERE
				sac_id = $categoria_id");
		    
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["regras"] = unserialize($dados["regras"]);
			return $dados;
		}
		
	}

	function ehUsuarioPosVenda() {
	    
		if ($_SESSION["usuario"]) {
		    
			$query = $this->bancodedados->query("
				SELECT
					usu_id
				FROM
					rel_usu_orf_ors_exerce
				WHERE
					usu_id = {$_SESSION["usuario"]} AND
					ors_id IN (6, 15)");
			
			return $this->bancodedados->resultado($query);
			
		}
		
	}

	function geraAjusteInstalacaoAditivo($dados_contrato, $data_ativacao) {
	    
		$parcelamento = $this->dados_item["parametros"]["parcelamento"] ? $this->dados_item["parametros"]["parcelamento"] : 1;
		$competencia_inicial = date("Y-m-01", strtotime($data_ativacao));
		if ($this->dados_item["tipo_id"] == 12) {
			$valor_total = $dados_contrato["parametros"]["tabela_valor_final_instalacao"];
		} else {
			$valor_total = $dados_contrato["parametros"]["contrato_valor_instalacao"];
		}
		$competencia_final = date("Y-m-01", strtotime("$competencia_inicial + ".($parcelamento - 1)." month"));
		$valor_mensal = intval($valor_total / $parcelamento);
		
		if ($valor_mensal > 0) {

			$this->bancodedados->query("
				INSERT INTO
					tab_ajustes_financeiros
				SET
					ajf_datahora = NOW(),
					cli_id = {$dados_contrato["cliente"]},
					cor_id = {$dados_contrato["id"]},
					afc_id = 19,
					aft_id = 1,
					ajf_valor = $valor_mensal,
					ajf_valor_tipo = 1,
					ajf_valor_total = $valor_total,
					ajf_competencia_inicio = '$competencia_inicial',
					ajf_competencia_termino = '$competencia_final',
					ajf_parcelas = $parcelamento,
					afs_id = 5");

			$ajuste_id = $this->bancodedados->ultimoIdInserido();

			$this->registraComentarioAjusteFinanceiro($ajuste_id, "Instalação referente ao contrato <b>{$dados_contrato["codigo"]}</b>");

		}

	}

	function buscaCompetenciaDescontos($contrato_id) {
	    
	    $competencia = null;
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_descontos.des_parametros as parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
				INNER JOIN rel_dec_cor_estacontido ON (tab_descontos_concessoes.dec_id = rel_dec_cor_estacontido.dec_id)
			WHERE
				rel_dec_cor_estacontido.cor_id = $contrato_id AND
				tab_descontos_concessoes.dec_mensalidade = 1 AND
				tab_descontos_concessoes.dec_validade IS NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($dados["parametros"]["validade_tipo"] && $dados["parametros"]["validade_tipo"] == 2 && $dados["parametros"]["validade"]) {
				$dados["parametros"]["validade"] = $this->formataData($dados["parametros"]["validade"], false);
				if (!$competencia || strtotime($dados["parametros"]["validade"]) < strtotime($competencia)) {
					$competencia = $dados["parametros"]["validade"];
				}
			}
		}
		
		return $competencia;
		
	}

	function buscaCategoriaViabilidadeResidencial($cliente_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_bairros.cid_id
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
				INNER JOIN tab_ceps ON (IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
			WHERE
				cli_id = $cliente_id");
		
		$cidade_id = $this->bancodedados->resultado($query);
		
		return $cidade_id == 26 || $cidade_id == 13 || $cidade_id == 153 ? 1052460 : 1052456;
	
	}

	function existeDuplicidadeCPFCNPJ($cliente_id, $cpf_cnpj) {
	    
		$query = $this->bancodedados->query("
			SELECT
				COUNT(cli_id)
			FROM
				tab_clientes
			WHERE
				cli_id != $cliente_id AND
				cli_cpf_cnpj = '$cpf_cnpj'");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function existeDuplicidadeEndereco($cliente_id, $bloco_id, $cep_id, $numero, $complemento_tipo_id, $complemento_valor) {
	    
		if ($complemento_valor) {
			if ($complemento_tipo_id != "o") {
				$complemento_valor = intval($complemento_valor);
			}
			$complemento_valor = $complemento_tipo_id.$complemento_valor;
		}
		
		if ($bloco_id) {
			$query = $this->bancodedados->query("
				SELECT
					COUNT(cli_id)
				FROM
					tab_clientes
				WHERE
					cob_id = $bloco_id AND
					cli_complemento = '$complemento_valor' AND
					cli_id != $cliente_id");
		} elseif ($cep_id && $numero) {
			$query = $this->bancodedados->query("
				SELECT
					COUNT(cli_id)
				FROM
					tab_clientes
				WHERE
					cep_id = $cep_id AND
					cli_numero = $numero AND
					cli_complemento = '$complemento_valor' AND
					cli_id != $cliente_id");
		}
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function buscaServicosAssinaturasSemContrato($cliente_id) {
	    
		$servicos_disponiveis = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_planos_servicos.pls_id AS id,
				tab_planos_servicos.pls_nome AS nome
			FROM
				tab_planos_servicos
				INNER JOIN tab_planos ON (tab_planos_servicos.pls_id = tab_planos.pls_id)
				INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				LEFT OUTER JOIN tab_assinaturas_vinculos ON (tab_assinaturas_vinculos.ass_filha = tab_assinaturas.ass_id)
			WHERE
				tab_assinaturas.cli_id = $cliente_id AND
				tab_assinaturas.ast_id = 1 AND
				rel_ass_cor_estacontido.cor_id IS NULL AND
				tab_assinaturas_vinculos.asv_id IS NULL AND
				tab_planos.plt_id = 1");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos_disponiveis[$dados["id"]] = $dados["nome"];
		}
		
		return $servicos_disponiveis;

	}

	function buscaContratosTipos() {
	    
	    $tipos_permitidos = array();
	    
		// Alteração de Assinaturas
		if ($this->permissoes["contratos"]["adicionar_aditivo_aa"]) $tipos_permitidos[] = 12;
		// Migração de Assinaturas
		if ($this->permissoes["contratos"]["adicionar_aditivo_ma"]) $tipos_permitidos[] = 13;
		// Mudança de Endereço
		if ($this->permissoes["contratos"]["adicionar_aditivo_me"]) $tipos_permitidos[] = 14;
		// Mudança de Titularidade
		if ($this->permissoes["contratos"]["adicionar_aditivo_mt"]) $tipos_permitidos[] = 15;
		
		$tipos_permitidos = implode(",", $tipos_permitidos);

		if ($tipos_permitidos) {
		    
		    $tipos = array();
			
			$query = $this->bancodedados->query("
				SELECT
					crt_id AS id,
					crt_nome AS nome
				FROM
					tab_contratos_tipos
				WHERE
					crt_id in ($tipos_permitidos)
				ORDER BY
					id");
			
			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$tipos[$dados["id"]] = $dados["nome"];
			}
			
			return $tipos;
			
		}
		
	}

	function buscaCodigoContrato($contrato_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				cor_codigo
			FROM
				tab_contratos
			WHERE
				cor_id = $contrato_id");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaAssinaturasEquipamentos($assinatura_id){
	    
		$query = $this->bancodedados->query("
            SELECT
                equ_id AS equipamento
            FROM
                tab_planos_equipamentos
                INNER JOIN tab_assinaturas ON (tab_planos_equipamentos.pla_id = tab_assinaturas.pla_id)
            WHERE
				tab_assinaturas.ass_id = $assinatura_id");
		
		return $this->bancodedados->resultadoArrayTotal($query);
		
	}

	function buscaComodatoAssinatura($assinatura_id, $equipamento_id) {
	    
		$query = "
			SELECT
				COUNT(*)
			FROM
				tab_clientes_comodatos
			WHERE
				ass_id = $assinatura_id AND
				equ_id = $equipamento_id";
		
		$query = $this->bancodedados->query($query);
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function buscaContratoComodatos($contrato_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				clc_id AS id,
				equ_id AS equipamento_id,
				ccs_id AS status_id
			FROM
				tab_clientes_comodatos
			WHERE
				cor_id = $contrato_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}


	function buscaFormaPagamentoMigracao($plano_id) {

		$query = $this->bancodedados->query("
			SELECT
				fop_id
			FROM
				tab_formas_pagamento
			WHERE
				pla_id = $plano_id AND
				fpc_id = 5
			LIMIT 0, 1");

		return $this->bancodedados->resultado($query);

	}

	function buscaDadosPlanoComodato($plano_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_parametros
			FROM
				tab_planos
			WHERE
				pla_id = $plano_id AND
				(
					SELECT
						COUNT(*)
					FROM
						tab_planos_equipamentos
					WHERE
						tab_planos.pla_id = tab_planos_equipamentos.pla_id AND
						tab_planos_equipamentos.equ_id = 4
				) > 0");
		
		if (($parametros = $this->bancodedados->resultado($query)) != false) {
			return unserialize($parametros);
		}
		
		return false;
		
	}

	function buscaAssinaturasVinculadas($assinaturas_adicionadas, $servico_id) {
	    
		$assinaturas = array();
	    $assinaturas_vinculadas = array();
	    
		foreach ($assinaturas_adicionadas as $assinatura_adicionada) {
			$assinaturas[] = $assinatura_adicionada["ass_id"] ? $assinatura_adicionada["ass_id"] : $assinatura_adicionada["id"];
		}
		
		$assinaturas = implode(", ", $assinaturas);
		
		$query = $this->bancodedados->query("
			SELECT
				tab_assinaturas_vinculos.ass_filha AS assinatura_id,
				tab_assinaturas_vinculos.pvt_id AS tipo_id,
				tab_planos.pls_id AS servico_id
			FROM
				tab_assinaturas_vinculos
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_assinaturas_vinculos.ass_filha)
				INNER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
			WHERE
				tab_assinaturas_vinculos.ass_id IN ($assinaturas) AND
				tab_assinaturas.ast_id = 1 AND
				tab_planos.pls_id <> $servico_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas_vinculadas[$dados["servico_id"]]["tipo_id"] = $assinaturas_vinculadas[$dados["servico_id"]]["tipo_id"] < $dados["tipo_id"] ? $dados["tipo_id"] : $assinaturas_vinculadas[$dados["servico_id"]]["tipo_id"];
			$assinaturas_vinculadas[$dados["servico_id"]]["assinaturas"][] = $dados["assinatura_id"];
		}
		
		return $assinaturas_vinculadas;
		
	}

	function buscaClienteCondominio($cliente_id){

		$query = $this->bancodedados->query("
			SELECT
				con_id
			FROM
				tab_clientes
			WHERE
				cli_id = $cliente_id");

		return $this->bancodedados->resultado($query);

	}

	function buscaDataCancelamento($cliente_id, $servico_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(cor_data_cancelamento, '%Y%m%d')
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				pls_id = $servico_id
			ORDER BY
				cor_data_cancelamento DESC");
		
		return $this->bancodedados->resultado($query);
		
	}

	function ehDescontoPlano($desconto_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				IF(pla_id IS NOT NULL, 1, 0)
			FROM
				tab_descontos_concessoes
			WHERE
				dec_id = $desconto_id");
		
		return $this->bancodedados->resultado($query) == "1";

	}

	function atualizaAssinatura($assinatura_id, $cliente_id, $novo_plano_id) {
	    
		$dados_plano = $this->buscaDadosPlano($novo_plano_id);
		
		$this->bancodedados->query("
			UPDATE
				tab_assinaturas
			SET
				pla_id = $novo_plano_id,
				ass_parametros = '{$dados_plano["parametros"]}',
				ass_instalacao = 0,
				ass_mensalidade = {$dados_plano["mensalidade"]},
				ass_data_cadastro = CURDATE(),
				ass_data_fechamento = CURDATE()
			WHERE
				ass_id = $assinatura_id");
		
		$this->removeAjusteProgramado($assinatura_id);
		$this->adicionaAjusteProgramado($assinatura_id, $cliente_id, $novo_plano_id);
		
		return $assinatura_id;
		
	}

	function adicionaAjusteProgramado($assinatura_id, $cliente_id, $novo_plano_id) {
	    
		$dados_plano = $this->buscaDadosPlano($novo_plano_id);
		
		if ($dados_plano["categoria_ajuste_id"]) {

			// Busca Dados Categoria
			$dados_categoria = $this->buscaDadosCategoriaAjuste($dados_plano["categoria_ajuste_id"]);

			// Insere Ajuste Programado
			$this->bancodedados->query("
				INSERT INTO
					tab_servicos_ajustes
				SET
					cli_id = $cliente_id,
					ass_id = $assinatura_id,
					sac_id = {$dados_plano["categoria_ajuste_id"]},
					saj_inicio = NULL,
					saj_termino = NULL,
					sas_id = 1");

			$ajuste_id = $this->bancodedados->ultimoIdInserido();

			// Registra Regras
			$this->registraRegras($ajuste_id, $dados_categoria["regras"]);

			// Registra Comentário
			$this->registraComentarioServicoAjuste($ajuste_id, "<b>Ação</b>: Adicionado");

			// Insere Link Assinatura
			$this->bancodedados->query("
				INSERT INTO
					rel_saj_ass_contem
				SET
					saj_id = $ajuste_id,
					ass_id = $assinatura_id");

		}
		
	}

	function removeAjusteProgramado($assinatura_id) {
	    
		$this->bancodedados->query("
			DELETE FROM
				rel_saj_ass_contem
			WHERE
				saj_id IN (SELECT saj_id FROM tab_servicos_ajustes WHERE ass_id = $assinatura_id)");

		$this->bancodedados->query("
			DELETE FROM
				tab_servicos_ajustes_regras
			WHERE
				saj_id IN (SELECT saj_id FROM tab_servicos_ajustes WHERE ass_id = $assinatura_id)");

		$this->bancodedados->query("
			DELETE FROM
				tab_servicos_ajustes_comentarios
			WHERE
				saj_id IN (SELECT saj_id FROM tab_servicos_ajustes WHERE ass_id = $assinatura_id)");

		$this->bancodedados->query("
			DELETE FROM
				tab_servicos_ajustes
			WHERE
				ass_id = $assinatura_id");
		
	}

	function registraRegras($ajuste_id, $regras) {
	    
		// Remove Regras Antigas
		$this->bancodedados->query("
			DELETE FROM
				tab_servicos_ajustes_regras
			WHERE
				saj_id = $ajuste_id");
		
		// Insere Regras Novas
		foreach ($regras as $regra) {

			$regra["servico"] = $regra["servico"] ? $regra["servico"] : "NULL";
			$regra["ajuste"] = $regra["ajuste_tipo"] == 1 ? $regra["ajuste_valor"] / 100 : $regra["ajuste_valor"] * pow(1024, $regra["ajuste_unidade"]);
			$regra["dia_semana"] = $regra["dia_semana"] ? $regra["dia_semana"] : "NULL";
			$regra["hora_inicio"] = $regra["hora_inicio"] && $regra["hora_inicio"] != '00:00' ? "'{$regra["hora_inicio"]}'" : "NULL";
			$regra["hora_termino"] = $regra["hora_termino"] && $regra["hora_termino"] != '24:00' ? "'{$regra["hora_termino"]}'" : "NULL";

			$this->bancodedados->query("
				INSERT INTO
					tab_servicos_ajustes_regras
				SET
					saj_id = $ajuste_id,
					sat_id = {$regra["servico"]},
					sar_ajuste = {$regra["ajuste"]},
					sar_dia_semana = {$regra["dia_semana"]},
					sar_hora_inicio = {$regra["hora_inicio"]},
					sar_hora_termino = {$regra["hora_termino"]}");

		}
		
	}

    function calculaValorVenda($assinatura_id) {
        
        $query = $this->bancodedados->query("
	        SELECT
	            tab_assinaturas.ass_mensalidade - IFNULL(SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)), 0)
	        FROM
	            tab_assinaturas
	            LEFT OUTER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
	        WHERE
	            tab_assinaturas.ass_id = $assinatura_id AND
	            tab_descontos_concessoes.dec_mensalidade > 0 AND
	            tab_descontos_concessoes.dec_data_termino IS NULL");
        
        return $this->bancodedados->resultado($query);
        
    }

	function buscaResponsavel($cliente_id, $data_fechamento) {
	    
		$query = $this->bancodedados->query("
			SELECT
				usu_id
			FROM
				tab_clientes_responsabilidades
			WHERE
				cli_id = $cliente_id AND
				cra_id IN (4, 5, 6) AND
				'$data_fechamento' BETWEEN DATE_FORMAT(cln_inicio, '%Y-%m-%d') AND DATE_FORMAT(cln_termino, '%Y-%m-%d')
			ORDER BY
				cln_inicio DESC
			LIMIT 1");
		
		return $this->bancodedados->resultado($query);
		
	}

	function calculaMediaMensalidadePrimeiroAno($assinatura, $descontar_bonificacao = false) {
	    
		$mensalidade = (int) $assinatura["mensalidade"];
	    $descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes.dec_data_termino AS data_termino
			FROM
				tab_assinaturas
				INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
			WHERE
				tab_assinaturas.ass_id = {$assinatura["id"]} AND
				tab_descontos_concessoes.dec_mensalidade > 0".($descontar_bonificacao ? "" : " AND
				tab_descontos_concessoes.det_id != 4")."
			GROUP BY
				IF(tab_descontos_concessoes.dec_validade IS NOT NULL, tab_descontos_concessoes.dec_validade, tab_descontos_concessoes.dec_data_termino)
			ORDER BY
				validade");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		    
			if (!$dados["validade"] && $dados["data_termino"]) {
				$dados["validade"] = round((strtotime($assinatura["data_ativacao"]) - strtotime($assinatura["data_ativacao"])) / 2592000);
			}
			$dados["validade"] = $dados["validade"] > 0 && $dados["validade"] < 12 ? intval($dados["validade"], 10) : 12;
			
			$descontos[] = $dados;
			
		}
		
		$media = 0;
		
		for ($i = 1; $i <= 12; $i++) {
			$desconto = 0;
			for ($j = 0; $j < count($descontos); $j++) {
				if (!$descontos[$j]["validade"] || $descontos[$j]["validade"] >= $i) {
					$desconto += $descontos[$j]["mensalidade"];
				}
			}
			$desconto = $desconto < $mensalidade ? $desconto : $mensalidade;
			$media += $mensalidade - $desconto;
		}
		
		return intval($media / 12, 10);
		
	}

	function calculaPrimeiraMensalidade($assinatura, $ate_vencimento = false) {
	    
		$mensalidade = (int) $assinatura["mensalidade"];
		
		$query = $this->bancodedados->query("
			SELECT
				SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes.dec_data_termino AS data_termino,
				tab_descontos_concessoes.des_id AS desconto_id
			FROM
				tab_assinaturas
				INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
			WHERE
				tab_assinaturas.ass_id = {$assinatura["id"]} AND
				tab_descontos_concessoes.dec_mensalidade > 0".($ate_vencimento ? " AND
				tab_descontos_concessoes.det_id IN (4)" : "")."
			GROUP BY
				IF(tab_descontos_concessoes.dec_validade IS NOT NULL, tab_descontos_concessoes.dec_validade, tab_descontos_concessoes.dec_data_termino)
			ORDER BY
				tab_descontos_concessoes.dec_validade");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if ((!$dados["validade"] && !$dados["data_termino"]) || in_array($dados["desconto_id"], array(30))) {
				if ($mensalidade - $dados["mensalidade"] > 0) {
					$mensalidade -= $dados["mensalidade"];
				} else {
					return 0;
				}
			} else {
				if ($mensalidade - $dados["mensalidade"] > 0) {
					return $mensalidade - $dados["mensalidade"] > 0 ? $mensalidade - $dados["mensalidade"] : 0;
				} else {
					if (($dados["validade"] <= 3) || (!$dados["validade"] && ((strtotime($dados["data_termino"]) - strtotime(date("Y-m-d"))) / 60 / 60 / 24 / 30) <= 3) || $this->ehCondominioNovo($assinatura["cliente"])) {
						return $mensalidade > 0 ? $mensalidade : 0;
					} else {
						return 0;
					}
				}
			}
		}
		
		return $mensalidade > 0 ? $mensalidade : 0;
		
	}

	function calculaMensalidadeFinal($assinatura) {
	    
		$mensalidade = (int) $assinatura["mensalidade"];

		$query = $this->bancodedados->query("
			SELECT
				SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade))
			FROM
				tab_assinaturas
				LEFT OUTER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
			WHERE
				tab_assinaturas.ass_id = {$assinatura["id"]} AND
				tab_descontos_concessoes.dec_mensalidade > 0 AND
				(tab_descontos_concessoes.dec_data_termino IS NULL OR tab_descontos_concessoes.dec_data_termino >= tab_assinaturas.ass_data_cancelamento)");
		
		if ($descontos = $this->bancodedados->resultado($query)) {
			$mensalidade -= $descontos;
		}

		return $mensalidade > 0 ? $mensalidade : 0;
		
	}

	function calculaMensalidadeBonificacao($assinatura) {
	    
		$mensalidade = (int) $assinatura["mensalidade"];
		
		$query = $this->bancodedados->query( "
			SELECT
				SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_assinaturas.ass_mensalidade)) AS mensalidade,
				tab_descontos_concessoes.dec_validade AS validade,
				tab_descontos_concessoes.det_id AS desconto_tipo_id
			FROM
				tab_assinaturas
				INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
				LEFT OUTER JOIN tab_descontos_concessoes descontos_condominio ON (tab_descontos_concessoes.dec_pai = descontos_condominio.dec_id AND descontos_condominio.det_id != 4 AND descontos_condominio.con_id IS NOT NULL)
			WHERE
				tab_assinaturas.ass_id = {$assinatura["id"]} AND
				tab_descontos_concessoes.dec_mensalidade > 0 AND
				descontos_condominio.dec_id IS NULL
			GROUP BY
				tab_descontos_concessoes.dec_validade
			ORDER BY
				tab_descontos_concessoes.dec_validade");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			if (!$dados["validade"]) {
				if ($mensalidade - $dados["mensalidade"] > 0) {
					$mensalidade -= $dados["mensalidade"];
				} else {
					return 0;
				}
			} else {
				if ($mensalidade - $dados["mensalidade"] > 0) {
					return $mensalidade - $dados["mensalidade"];
				} else {
					if ($dados["validade"] <= 3 || ($this->ehCondominioNovo($assinatura["cliente"]) && $dados["desconto_tipo_id"] == 3)) {
						return $mensalidade;
					} else {
						return 0;
					}
				}
			}
		}
		
		return $mensalidade > 0 ? $mensalidade : 0;
		
	}

	function ehCondominioNovo($cliente_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				IF(con_data_ativacao >= DATE_SUB(CURDATE(), INTERVAL 15 DAY) OR con_data_ativacao IS NULL, 1, 0)
			FROM
				tab_clientes
				INNER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
			WHERE
				cli_id = $cliente_id");
		
		return $this->bancodedados->resultado($query) == "1";
		
	}

	function contratoFidelidade($contrato_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				IF(cor_vigencia IS NOT NULL AND cor_multa > 0, 1, 0)
			FROM
				tab_contratos
			WHERE
				cor_id = $contrato_id");

		return $this->bancodedados->resultado($query) == "1";

	}

	function usuarioPosVenda($usuario_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				COUNT(ors_id)
			FROM
				rel_usu_orf_ors_exerce
			WHERE
				usu_id = '$usuario_id' AND
				ors_id IN (6, 15)");

		return $this->bancodedados->resultado($query) > 0;
		
	}

	function ehVendedorExterno($vendedor_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				IF(ors_id = 8 AND orf_id <> 0, 1, 0)
			FROM
				rel_usu_orf_ors_exerce
			WHERE
				usu_id = $vendedor_id");
		
		return $this->bancodedados->resultado($query) == "1";
		
	}

	function ehVendedorInterno($vendedor_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				IF(ors_id IN (9, 24) AND orf_id <> 0, 1, 0)
			FROM
				rel_usu_orf_ors_exerce
			WHERE
				usu_id = $vendedor_id");
		
		return $this->bancodedados->resultado($query) == "1";
		
	}

	function buscaTipoAgente($vendedor_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				gru_id
			FROM
				rel_usu_gru_fazparte
			WHERE
				usu_id = '$vendedor_id' AND
				gru_id IN (34, 49, 58)
			LIMIT 1");
		
		return (int) $this->bancodedados->resultado($query);
		
	}

	function ehVendaAtiva($vendedor_id, $condominio_id, $data_fechamento) {
	    
		$data_fechamento = $data_fechamento ? "'$data_fechamento'" : "CURDATE()";
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(cod_id)
			FROM
				tab_condominios_responsabilidades
			WHERE
				$data_fechamento BETWEEN DATE_FORMAT(cod_inicio, '%Y-%m-%d') AND DATE_FORMAT(cod_termino, '%Y-%m-%d') AND
				usu_id = $vendedor_id AND
				con_id = $condominio_id");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function removeVenda($assinatura_id = null, $contrato_id = null) {
	    
		$query = $this->bancodedados->query("
			SELECT
				ven_id
			FROM
				tab_vendas
			WHERE".($assinatura_id ? "
				ass_id = $assinatura_id" : "
				cor_id = $contrato_id"));
		
		if ($venda_id = $this->bancodedados->resultado($query)) {
		
			// Remove comentários venda
			$this->bancodedados->query("
				DELETE FROM
					tab_vendas_comentarios
				WHERE
					ven_id = $venda_id");

			// Remove comentários comissões
			$this->bancodedados->query("
				DELETE FROM
					tab_vendas_comissoes_comentarios
				WHERE
					vec_id IN (SELECT vec_id FROM tab_vendas_comissoes WHERE ven_id = $venda_id)");

			// Remove comissões
			$this->bancodedados->query("
				DELETE FROM
					tab_vendas_comissoes
				WHERE
					ven_id = $venda_id");

			// Remove responsável cliente
			$this->bancodedados->query("
				UPDATE
					tab_clientes
					INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
					INNER JOIN tab_vendas ON (tab_assinaturas.ass_id = tab_vendas.ass_id)
				SET
					tab_clientes.usu_id = NULL
				WHERE
					ven_id = $venda_id");

			// Remove venda
			$this->bancodedados->query("
				DELETE FROM
					tab_vendas
				WHERE
					ven_id = $venda_id");

		}
		
	}

	function buscaClienteIndicador($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				usu_id
			FROM
				tab_clientes_responsabilidades
			WHERE
				cli_id = $cliente_id AND
				cri_id = 1 AND
				cra_id = 4 AND
				NOW() BETWEEN cln_inicio AND cln_termino");
		
		return $this->bancodedados->resultado($query);
		
	}

	function geraContratoPrincipalCodigo($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cor_codigo
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				INSTR(cor_codigo, '/') > 0 AND
				cor_pai IS NULL
			ORDER BY
				cor_id DESC
			LIMIT 0, 1");

		if (($sufixo = $this->bancodedados->resultado($query)) != false) {
			$sufixo = explode("-", $sufixo);
			$sufixo = $sufixo[1];
		} else {
			$sufixo = 0;
		}
		
		$prefixo = date("Y")."/".sprintf("%09s", intval($cliente_id));
		$sufixo = sprintf("%02s", $sufixo + 1);
		
		return "$prefixo-$sufixo";
		
	}

	function geraContratoAditivoCodigo($cliente_id, $contrato_id, $codigo) {
		
		$query = $this->bancodedados->query("
			SELECT
				cor_codigo
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				cor_pai = $contrato_id
			ORDER BY
				cor_codigo DESC
			LIMIT 0, 1");

		if (($sufixo = $this->bancodedados->resultado($query)) != false) {
			$sufixo = explode("-", $sufixo);
			$sufixo = explode("/", $sufixo[1]);
			$sufixo = $sufixo[1];
		} else {
			$sufixo = 0;
		}
		
		$sufixo = sprintf("%02s", $sufixo + 1);
		
		return "$codigo/$sufixo";
		
	}

	function planosSemComissao($planos) {
	    
	    $novos_planos = array();
	    
		foreach ($planos as $plano) {
			$plano = explode(",", $plano);
			$novos_planos[] = $plano[0];
		}
		
		$query = $this->bancodedados->query("
			SELECT
				pla_parametros AS parametros
			FROM
				tab_planos
			WHERE
				pla_id IN (".implode(", ", $novos_planos).")");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($dados["parametros"]["comissoes"]["mensalidade_responsabilidade_interno"]) return false;
			if ($dados["parametros"]["comissoes"]["mensalidade_responsabilidade_externo"]) return false;
			if ($dados["parametros"]["comissoes"]["mensalidade_responsabilidade_ativo"]) return false;
			if ($dados["parametros"]["comissoes"]["mensalidade_responsabilidade_parceiro"]) return false;
			if ($dados["parametros"]["comissoes"]["premiacao_pontos"]) return false;
		}
		
		return true;
		
	}

	function buscaDescontosVendedor($responsavel_id, $desconto_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(DISTINCT dec_id)
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_vendas ON (tab_descontos_concessoes.ass_id = tab_vendas.ass_id)
			WHERE
				tab_vendas.usu_id = $responsavel_id AND
				tab_vendas.ven_datahora LIKE '".date("Y-m-")."%' AND
				tab_descontos_concessoes.des_id = $desconto_id");
		
		return $this->bancodedados->resultado($query);
		
	}

	function possuiDominiosIndefinidos($assinaturas) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(dom_id)
			FROM
				tab_dominios
			WHERE
				ass_id IN ($assinaturas) AND
				dos_id = 1");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiDescontosPendentes($assinaturas) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(dec_id)
			FROM
				tab_descontos_concessoes
			WHERE
				ass_id IN ($assinaturas) AND
				dcs_id = 1");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaDescontosCotaSaturada($responsavel_id, $assinaturas) {
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_descontos.des_id AS desconto_id,
				tab_descontos.des_parametros AS parametros
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_descontos ON (tab_descontos_concessoes.des_id = tab_descontos.des_id)
			WHERE
				tab_descontos_concessoes.ass_id IN ($assinaturas)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			if ($dados["parametros"]["cota_mensal"]) {
				if ($this->buscaDescontosVendedor($responsavel_id, $dados["desconto_id"]) >= $dados["parametros"]["cota_mensal"]) {
					return true;
				}
			}
		}
		
		return false;

	}

	function ehClienteCancelado($cliente_id, $servico_id, $dias) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(cor_id)
			FROM
				tab_contratos
			WHERE
				cli_id = $cliente_id AND
				pls_id = $servico_id AND
				crt_id = 11 AND
				TO_DAYS(CURDATE()) - TO_DAYS(cor_data_cancelamento) <= $dias");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function buscaInterfaceBloco($bloco_id) {
	    
		$query = $this->bancodedados->query("
			SELECT
				tab_servidores_interfaces.sei_id AS id,
				tab_servidores_interfaces.ser_id AS servidor_id
			FROM
				tab_condominios_blocos
				INNER JOIN tab_servidores_interfaces ON (tab_condominios_blocos.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				cob_id = $bloco_id");
		
		return $this->bancodedados->resultadoArray($query);
		
	}

	function vigenciaAtivaCancelamento($dados_contrato, $data_cancelamento) {

		$data_cancelamento = explode("/", $data_cancelamento);
		$data_cancelamento = "{$data_cancelamento[2]}-{$data_cancelamento[1]}-{$data_cancelamento[0]}";

		$query = $this->bancodedados->query("
			SELECT
				cor_data_ativacao AS data_ativacao,
				cor_termino_fidelidade AS termino_fidelidade,
				cor_multa AS multa
			FROM
				tab_contratos
			WHERE
				cor_pai = {$dados_contrato["id"]} AND
				cor_termino_fidelidade > '$data_cancelamento' AND
				crs_id IN (19, 20, 21)
			ORDER BY
				cor_id DESC
			LIMIT 1");

		if (($dados = $this->bancodedados->resultadoArray($query)) == false) {
			
			$query = $this->bancodedados->query("
				SELECT
					cor_data_ativacao AS data_ativacao,
					cor_termino_fidelidade AS termino_fidelidade,
					cor_multa AS multa
				FROM
					tab_contratos
				WHERE
					cor_id = {$dados_contrato["id"]} AND
					cor_termino_fidelidade > '$data_cancelamento'
				ORDER BY
					cor_id DESC
				LIMIT 1");

			$dados = $this->bancodedados->resultadoArray($query);

		}

		$data_ativacao = $dados["data_ativacao"];
		$termino_fidelidade = $dados["termino_fidelidade"];
		$multa = $dados["multa"];

		$data_ativacao = strtotime($data_ativacao);
		$data_cancelamento = strtotime($data_cancelamento);
		$termino_fidelidade = strtotime($termino_fidelidade);

		if ($termino_fidelidade && ($termino_fidelidade > $data_cancelamento)) {

			$tempo_total = $termino_fidelidade - $data_ativacao;
			$tempo_termino = $termino_fidelidade - $data_cancelamento;

			return intval($multa / $tempo_total * $tempo_termino, 10);

		}

		return 0;

	}

	function buscaDadosAssinaturasProspect($cliente_id, $servico_id, $assinaturas = null) {

		$dados_assinatura = array(
			"instalacao" => 0,
			"instalacao_desconto" => 0,
			"mensalidade" => 0,
			"mensalidade_desconto" => 0,
			"descontos_fidelidade" => 0,
			"parcelamento_maximo" => 0,
			"multa" => 0
		);

		if ($assinaturas) {

			$query = "
				SELECT
					tab_assinaturas.ass_id AS assinatura_id,
					tab_assinaturas.ass_instalacao AS instalacao,
					tab_assinaturas.ass_mensalidade AS mensalidade,
					tab_assinaturas.ass_vigencia AS vigencia,
					tab_assinaturas.ass_multa AS multa,
					tab_assinaturas.pla_id AS plano_id,
					tab_planos.pls_id AS servico_id,
					tab_planos.pla_parcelamento_maximo AS parcelamento_maximo
				FROM
					tab_assinaturas
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				WHERE
					tab_assinaturas.cli_id = $cliente_id AND
					(SELECT COUNT(*) FROM rel_ass_cor_estacontido WHERE rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id) = 0 AND
					tab_assinaturas.ast_id = 1 AND".($assinaturas !== null ? "
					(tab_assinaturas.ass_id IN ($assinaturas) OR tab_assinaturas.ass_pai IN ($assinaturas)) AND" : "")."
					tab_planos.pls_id = $servico_id
				GROUP BY
					tab_assinaturas.ass_id";

			$query = $this->bancodedados->query($query);

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

				$dados_assinatura["assinatura_id"] = $dados["assinatura_id"];
				$dados_assinatura["plano_id"] = $dados["plano_id"];
				$dados_assinatura["servico_id"] = $dados["servico_id"];
				$dados_assinatura["instalacao"] += $dados["instalacao"];
				$dados_assinatura["mensalidade"] += $dados["mensalidade"];
				$dados_assinatura["multa"] += $dados["multa"];
				$dados_assinatura["vigencia"] = $dados_assinatura["vigencia"] < $dados["vigencia"] ? $dados["vigencia"] : $dados_assinatura["vigencia"];

				$descontos = $this->buscaDescontosAssinaturaProspect($dados["assinatura_id"], $dados["instalacao"], $dados["mensalidade"]);

				$dados_assinatura["instalacao_desconto"] += $descontos["instalacao"];
				$dados_assinatura["mensalidade_desconto"] += $descontos["mensalidade"];
				$dados_assinatura["descontos_fidelidade"] += $descontos["descontos_fidelidade"];

				if ($dados["instalacao"] - $descontos["instalacao"] > 0) {
					if (!$dados_assinatura["parcelamento_maximo"]) {
						$dados_assinatura["parcelamento_maximo"] = $dados["parcelamento_maximo"];
					} elseif ($dados["parcelamento_maximo"] && $dados_assinatura["parcelamento_maximo"] > $dados["parcelamento_maximo"]) {
						$dados_assinatura["parcelamento_maximo"] = $dados["parcelamento_maximo"];
					}
					if ($descontos["parcelamento_maximo"] && $dados_assinatura["parcelamento_maximo"] > $descontos["parcelamento_maximo"]) {
						$dados_assinatura["parcelamento_maximo"] = $descontos["parcelamento_maximo"];
					}
				}

			}

		}

		$dados_assinatura["instalacao_final"] = max($dados_assinatura["instalacao"] - $dados_assinatura["instalacao_desconto"], 0);
		$dados_assinatura["mensalidade_final"] = max($dados_assinatura["mensalidade"] - $dados_assinatura["mensalidade_desconto"], 0);
		
		$dados_assinatura["instalacao"] = $this->formataDinheiro($dados_assinatura["instalacao"]).($dados_assinatura["instalacao_desconto"] > 0 ? " (".$this->formataDinheiro($dados_assinatura["instalacao_final"]).")" : "");
		$dados_assinatura["mensalidade"] = $this->formataDinheiro($dados_assinatura["mensalidade"]).($dados_assinatura["mensalidade_desconto"] > 0 ? " (".$this->formataDinheiro($dados_assinatura["mensalidade_final"]).")" : "");
		
		$dados_assinatura["multa"] = $dados_assinatura["multa"] ? $this->formataDinheiro($dados_assinatura["multa"]) : "";
		$dados_assinatura["vigencia"] = $dados_assinatura["vigencia"] ? "{$dados_assinatura["vigencia"]} meses" : "";

		$dados_assinatura["parcelamento_maximo"] = min(array($dados_assinatura["parcelamento_maximo"], (int) ($dados_assinatura["instalacao_final"] / 3000)));
		$dados_assinatura["parcelamento_maximo"] = max($dados_assinatura["parcelamento_maximo"], 1);
		
		$parcelas = array();
		
		if ($dados_assinatura["instalacao_final"]) {
			
			$parcelas[] = "<option></option>";
			
			//$parcelas[] = "<option value='0'>".$this->formataDinheiro($dados_assinatura["instalacao_final"])." (Antecipado)</option>";
			
			for ($parcela = 1; $parcela <= $dados_assinatura["parcelamento_maximo"]; $parcela++) {
				//$parcelas[] = "<option value='$parcela'>$parcela x de ".$this->formataDinheiro($dados_assinatura["instalacao_final"] / $parcela)." (Cartão)</option>";
				$parcelas[] = "<option value='$parcela'>$parcela x de ".$this->formataDinheiro($dados_assinatura["instalacao_final"] / $parcela)."</option>";
			}
			
		}

		if (count($parcelas)) {
			$dados_assinatura["parcelamento"] = "<select name='parcelamento' class='inputBrancoAzulEscuro11'>".implode("", $parcelas)."</select>";
		} else {
			$dados_assinatura["parcelamento"] = "";
		}

		return $dados_assinatura;

	}

	function buscaDescontosAssinaturaProspect($assinatura_id, $instalacao, $mensalidade) {

		$descontos = array(
			"instalacao" => 0,
			"mensalidade" => 0,
			"descontos_fidelidade" => 0,
			"parcelamento_maximo" => 0
		);

		$query = $this->bancodedados->query("
			SELECT
				IF(tab_descontos_concessoes.dec_instalacao <= 1, tab_descontos_concessoes.dec_instalacao * $instalacao, tab_descontos_concessoes.dec_instalacao) AS instalacao,
				IF(tab_descontos_concessoes.dec_mensalidade <= 1, tab_descontos_concessoes.dec_mensalidade * $mensalidade, tab_descontos_concessoes.dec_mensalidade) AS mensalidade,
				IF(tab_descontos_concessoes.det_id = 2, 1, 0) AS descontos_fidelidade,
				(SELECT tab_descontos.des_parametros FROM tab_descontos WHERE tab_descontos_concessoes.des_id = tab_descontos.des_id) AS parametros
			FROM
				tab_descontos_concessoes
			WHERE
				tab_descontos_concessoes.ass_id = $assinatura_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$dados["parametros"] = unserialize($dados["parametros"]);

			$descontos["instalacao"] += $dados["instalacao"];
			$descontos["mensalidade"] += $dados["mensalidade"];
			$descontos["descontos_fidelidade"] += $dados["descontos_fidelidade"];

			if (!$descontos["parcelamento_maximo"]) {
				$descontos["parcelamento_maximo"] = $dados["parametros"]["parcelamento_maximo"];
			} elseif ($descontos["parcelamento_maximo"] && $descontos["parcelamento_maximo"] > $dados["parametros"]["parcelamento_maximo"]) {
				$descontos["parcelamento_maximo"] = $dados["parametros"]["parcelamento_maximo"];
			}

		}

		return $descontos;

	}

	function buscaDescontosProspect($cliente_id, $assinaturas = null) {

		$descontos = array();

		if ($assinaturas) {

			$query = "
				SELECT
					tab_descontos_concessoes.dec_id AS id
				FROM
					tab_assinaturas
					INNER JOIN tab_descontos_concessoes ON (tab_assinaturas.ass_id = tab_descontos_concessoes.ass_id)
				WHERE
					tab_assinaturas.ast_id = 1 AND
					tab_assinaturas.cli_id = $cliente_id AND
					(SELECT COUNT(*) FROM rel_dec_cor_estacontido WHERE rel_dec_cor_estacontido.dec_id = tab_descontos_concessoes.dec_id) = 0".($assinaturas !== null ? " AND
					(tab_assinaturas.ass_id IN ($assinaturas) OR tab_assinaturas.ass_pai IN ($assinaturas))" : "");

			$query = $this->bancodedados->query($query);

			while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				$descontos[] = $this->buscaDadosDesconto($dados["id"]);
			}

		}

		return $descontos;

	}

	function cadastrarEmailsCliente($cliente_id, $emails) {
		
		foreach ($emails as $email) {
		    
			$email = explode("@", $email);
			
			$query = $this->bancodedados->query("
				SELECT
					COUNT(ccm_id)
				FROM
					tab_clientes_contatos_emails
				WHERE
					cli_id = $cliente_id AND
					ccm_usuario = '{$email[0]}' AND
					ccm_dominio = '{$email[1]}'");
			
			if (!$this->bancodedados->resultado($query)) {
				$this->bancodedados->query("
					INSERT INTO
						tab_clientes_contatos_emails
					SET
						ccm_usuario = '{$email[0]}',
						ccm_dominio = '{$email[1]}',
						cli_id = $cliente_id,
						cca_id = 1");
			}
			
		}
		
	}

	function buscaAxuliaresFinanceiros() {

		$auxiliares = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_usuarios.usu_id AS id
			FROM
				tab_usuarios
				INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
			WHERE
				tab_usuarios.uss_id = 1 AND
				rel_usu_gru_fazparte.gru_id = 9");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$auxiliares[] = $dados["id"];
		}

		return $auxiliares;

	}

	function geraAjusteInstalacao($cliente_id, $contrato_id, $contrato_codigo, $parcelas, $valor, $competencia_inicial, $competencia_final, $nf_numero = null, $nf_emissao = null, $nf_valor = null) {

		$nf_numero = $nf_numero ? "'${nf_numero}'" : "NULL";
		$nf_emissao = $nf_emissao ? "'{$nf_emissao}'" : "NULL";
		$nf_valor = $nf_valor ? $nf_valor : "NULL";

		// Atualiza NF Ajustes Pré-Pago
		$this->bancodedados->query("
			UPDATE
				tab_ajustes_financeiros
			SET
				ajf_nf_numero = $nf_numero,
				ajf_nf_emissao = $nf_emissao,
				ajf_nf_valor = $nf_valor
			WHERE
				cor_id = $contrato_id AND
				afc_id = 19");

		$valor_total = $parcelas * $valor;

		$this->bancodedados->query("
			INSERT INTO
				tab_ajustes_financeiros
			SET
				ajf_datahora = NOW(),
				cli_id = $cliente_id,
				cor_id = $contrato_id,
				afc_id = 19,
				aft_id = 1,
				ajf_parcelas = $parcelas,
				ajf_valor = $valor,
				ajf_valor_total = $valor_total,
				ajf_valor_tipo = 1,
				ajf_competencia_inicio = '$competencia_inicial',
				ajf_competencia_termino = '$competencia_final',
				afs_id = 5,
				ajf_nf_numero = $nf_numero,
				ajf_nf_emissao = $nf_emissao,
				ajf_nf_valor = $nf_valor");

		$ajuste_id = $this->bancodedados->ultimoIdInserido();

		$this->registraComentarioAjusteFinanceiro($ajuste_id, "Instalação referente ao contrato <b>$contrato_codigo</b>");

	}

	function possuiPesquisaAtiva($pesquisa_id) {

		$query = $this->bancodedados->query("
			SELECT
				pes_id AS id
			FROM
				tab_pesquisas
			WHERE
				pes_id = $pesquisa_id AND
				pet_id = 2");

		return $this->bancodedados->resultado($query) > 0;

	}

	function loteGerado($data_ativacao) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(col_id)
			FROM
				tab_cobrancas_lotes
			WHERE
				col_vencimento = DATE_ADD('".date("Y-m-01", strtotime($data_ativacao))."', INTERVAL 1 MONTH)");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiVisita($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(vis_id)
			FROM
				tab_visitas
			WHERE
				cor_id = $contrato_id AND
				via_id != 4");

		return $this->bancodedados->resultado($query) > 0;

	}

	function buscaErrosAtivacao() {
	    
	    $erros = array();
		
		// Desconto não aprovado
		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_descontos_concessoes.dec_id)
			FROM
				tab_descontos_concessoes
				INNER JOIN tab_assinaturas ON (tab_descontos_concessoes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = {$this->dados_item["id"]} AND
				tab_descontos_concessoes.dcs_id = 1");

		if ($this->bancodedados->resultado($query) > 0) {
			$erros[] = "» O cliente possui desconto(s) pendente(s)";
		}
		
		// Garantia Sem IVR
		if ($this->dados_item["servico_id"] == 21) {

			$query = $this->bancodedados->query("
				SELECT
					COUNT(tab_assinaturas.ass_id)
				FROM
					tab_assinaturas
					INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				WHERE
					tab_planos.pls_id IN (1, 2, 3, 4, 5, 6, 28, 36) AND
					tab_assinaturas.cli_id = {$this->dados_item["cliente"]} AND
					tab_assinaturas.ast_id = 2");

			if (!$this->bancodedados->resultado($query) > 0) {
				$erros[] = "» Assinatura de Garantia sem assinatura de Internet ativa";
			}

		}
		
		// Visita não agendada
		$query = $this->bancodedados->query("
			SELECT
				COUNT(tab_visitas.vis_id)
			FROM
				tab_visitas
			WHERE
				tab_visitas.cor_id = {$this->dados_item["id"]} AND
				tab_visitas.via_id = 1");

		if ($this->bancodedados->resultado($query) > 0) {
			$erros[] = "» Visita aguardando agendamento";
		}
		
		// Visita com data posterior
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(tab_visitas.vis_data, '%d/%m/%Y') AS data
			FROM
				tab_visitas
			WHERE
				tab_visitas.cor_id = {$this->dados_item["id"]} AND
				tab_visitas.vis_data > CURDATE() AND
				tab_visitas.via_id = 2");

		if (($data = $this->bancodedados->resultado($query)) != false) {
			$erros[] = "» Ativação anterior à data de agendamento ($data)";
		}
		
		/*
		// Equipamento comodato não definido
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_clientes_comodatos
			WHERE
				tab_clientes_comodatos.cor_id = {$this->dados_item["id"]} AND
				tab_clientes_comodatos.equ_id = 12 AND
				(tab_clientes_comodatos.clc_mac_address IS NULL OR tab_clientes_comodatos.eqm_id IS NULL)");

		if ($this->bancodedados->resultado($query) > 0) {
			$erros[] = "» Decoder(s) não definido(s)";
		}
		*/

		if ($_POST && $_POST["nf_numero"]) {
			$dados_nota = $this->buscaNotaInstalacao($_POST["nf_numero"]);
			if ($dados_nota) {
				$erros[] = "» NF {$_POST["nf_numero"]} cadastrada no ID {$dados_nota["cliente_id"]} (Emissão: {$dados_nota["emissao"]})";
			}
		}

		return $erros;

	}

	function buscaNotaInstalacao($nf_numero) {

		$query = $this->bancodedados->query("
			SELECT
				cli_id AS cliente_id,
				DATE_FORMAT(ajf_nf_emissao, '%d/%m/%Y') AS emissao
			FROM
				tab_ajustes_financeiros
			WHERE
				ajf_nf_numero = '$nf_numero' AND
				afc_id IN (19, 20) AND
				ajf_id > 85164");

		return $this->bancodedados->resultadoArray($query);

	}

	function buscaFormatoBoleto($cliente_origem_id) {

		$query = $this->bancodedados->query("
			SELECT
				cof_id
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id IN (SELECT cof_id FROM rel_cof_clo_disponivel WHERE clo_id = $cliente_origem_id) AND
				cft_id = 3 AND
				cfs_id = 1
			LIMIT
				0, 1");

		return $this->bancodedados->resultado($query);

	}

	function buscaFormatoBoletoPadrao($cliente_origem_id) {

		$query = $this->bancodedados->query("
			SELECT
				cof_id
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id IN (SELECT cof_id FROM rel_cof_clo_disponivel WHERE clo_id = $cliente_origem_id) AND
				cft_id IN (2, 3) AND
				cfs_id = 1
			ORDER BY
				cft_id
			LIMIT
				0, 1");

		return $this->bancodedados->resultado($query);

	}

	function possuiAssinaturaPrePagaPrincipal($contrato_id) {

		$query = $this->bancodedados->query("
			SELECT
				COUNT(cor_id)
			FROM
				tab_assinaturas
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id AND
				tab_assinaturas.pli_id = 2");

		return $this->bancodedados->resultado($query) > 0;

	}

	function possuiAssinaturaPrePagaAdicional($contrato_id) {

		if (($assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($contrato_id)) != false) {

			$assinaturas = array();

			foreach ($assinaturas_adicionadas as $assinatura_adicionada) {
				$assinaturas[] = $assinatura_adicionada["ass_id"];
			}

			$assinaturas = implode(", ", $assinaturas);

			$query = $this->bancodedados->query("
				SELECT
					COUNT(ass_id)
				FROM
					tab_assinaturas
				WHERE
					ass_id IN ($assinaturas) AND
					pli_id = 2");

			return $this->bancodedados->resultado($query) > 0;

		}

		return false;

	}

	function agendarAtivacao($contrato_id, $data_cancelamento, $comentario, $usuario_id) {

		// Agenda Cancelamento
		$this->bancodedados->query("
			UPDATE
				tab_contratos
			SET
				cor_data_ativacao = '$data_cancelamento',
				crs_id = 18
			WHERE
				cor_id = $contrato_id");

		// Registra comentário no contrato
		$this->registraComentario($contrato_id, 16, $comentario, $usuario_id);

	}

	function buscaSiglaEstado($estado) {

		$query = $this->bancodedados->query("
			SELECT
				est_sigla
			FROM
				tab_estados
			WHERE
				est_nome = '$estado'");

		return $this->bancodedados->resultado($query);

	}

	function buscaDadosRegistro($dados_contrato) {
		
		$query = $this->bancodedados->query("
			SELECT
				cov_numero AS contrato_registro_versao,
				cov_registro_numero AS contrato_registro_numero,
				cov_registro_livro AS contrato_registro_livro,
				cov_registro_folha AS contrato_registro_folha,
				DATE_FORMAT(cov_registro_data, '%d/%m/%Y') AS contrato_registro_data
			FROM
				tab_contratos_versoes
			WHERE
				cov_id = {$dados_contrato["parametros"]["contrato_versao"]} AND
				pls_id = {$dados_contrato["servico_id"]} AND
				pli_id = {$dados_contrato["sistema_id"]}");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		
			if ($dados_contrato["parametros"]["sistema"] == 1) {
				$dados["cliente_periodo_pagamento"] = "no mês subseqüente ao da utilização";
			} else {
				$dados["cliente_periodo_pagamento"] = "mensalmente, sempre referente ao mês corrente";
			}
		
			return $dados;
		
		}

	}

	function buscaCobrancaFormatoTipo($formato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cft_id
			FROM
				tab_cobrancas_formatos
			WHERE
				cof_id = $formato_id");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaCobracaInstalacao($contrato_id, $detalhes = false) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.cot_id AS status_id
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				INNER JOIN tab_ajustes_financeiros ON (tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id)
			WHERE
				tab_ajustes_financeiros.afc_id = 19 AND
				tab_ajustes_financeiros.cor_id = $contrato_id AND
				tab_cobrancas.cot_id = 1");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			return $detalhes ? $dados : $dados["id"];
		}
		
	}

	function possuiComodatoRoteadorVigente($cliente_id, $data){

		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_clientes_comodatos
			WHERE
				tab_clientes_comodatos.clc_data_termino >= '".(substr($data, 6, 4)."-".substr($data, 3, 2)."-".substr($data, 0, 2))."' AND
				tab_clientes_comodatos.cli_id = $cliente_id AND
				tab_clientes_comodatos.equ_id = 4");

		return $this->bancodedados->resultado($query) > 0;

	}

	function contratoVigenciaAlterada($contrato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(coo_id)
			FROM
				tab_contratos_comentarios
			WHERE
				cor_id = $contrato_id AND
				ccc_id = 21");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function buscaRegraLivre($servidor_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.clp_regra AS regra
			FROM
				tab_servidores_interfaces
				INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
			WHERE
				tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_regra IS NOT NULL AND
				tab_clientes_pontos.cps_id IN (3)
			ORDER BY
				tab_clientes_pontos.clp_regra
			LIMIT
				0, 1");

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
				
			$this->bancodedados->query("
				UPDATE
					tab_clientes_pontos
				SET
					clp_regra = NULL
				WHERE
					clp_id = {$dados["id"]}");
		
			return $dados["regra"];

		}
		
	}

	function buscaRegraMaxima($servidor_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				MAX(tab_clientes_pontos.clp_regra)
			FROM
				tab_servidores_interfaces
				INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
			WHERE
				tab_servidores_interfaces.ser_id = $servidor_id AND
				tab_clientes_pontos.clp_regra IS NOT NULL AND
				tab_clientes_pontos.cps_id IN (1, 2)");
				
		return (int) $this->bancodedados->resultado($query);
		
	}

	function cancelaPontos($assinatura_id, $cliente_id, $codigo_contrato) {
		
		if (($pontos = $this->buscaPontos($assinatura_id)) != false) {
			
			$assinaturas = $this->buscaAssinaturasAtivas($cliente_id);
			
			foreach ($assinaturas as $assinatura) {

				$pontos_disponiveis = $this->buscaPontosDisponiveis($assinatura["id"]);

				while (count($pontos) > 0 && $pontos_disponiveis > 0) {

					$ponto_id = array_pop($pontos);

					$this->bancodedados->query("
						UPDATE
							tab_clientes_pontos
						SET
							ass_id = {$assinatura["id"]},
							clp_velocidade_download = {$assinatura["parametros"]["pontos_velocidade_download"]},
							clp_velocidade_upload = {$assinatura["parametros"]["pontos_velocidade_upload"]},
							clp_publicado = 0
						WHERE
							clp_id = $ponto_id");

					$this->registraComentarioClientePonto($ponto_id, "<b>Ação:</b> Migrado do Contrato Cancelado ($codigo_contrato)");

					$pontos_disponiveis--;

				}

			}
			
			while (count($pontos) > 0) {

				$ponto_id = array_pop($pontos);

				$this->bancodedados->query("
					UPDATE
						tab_clientes_pontos
					SET
						cps_id = 3,
						clp_ajustado = 0,
						clp_parametros_ajuste = '',
						clp_publicado = 0
					WHERE
						clp_id = $ponto_id");

				$this->registraComentarioClientePonto($ponto_id, "<b>Ação:</b> Cancelado - Contrato Cancelado ($codigo_contrato)");

			}
			
		}
		
	}

	function buscaAssinaturasAtivas($cliente_id) {
		
		$assinaturas = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ass_id AS id,
				ass_parametros AS parametros
			FROM
				tab_assinaturas
			WHERE
				cli_id = $cliente_id AND
				ast_id IN (1, 2, 5)");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$assinaturas[] = $dados;
		}
		
		return $assinaturas;
		
	}

	function cancelaAutenticacoes($assinatura_id, $cliente_id, $codigo_contrato) {
		
		$pontos = $this->buscaAutenticacoes($assinatura_id);
		
		if ($pontos) {
			
			$assinaturas = $this->buscaAssinaturasAtivas($cliente_id);
			
			foreach ($assinaturas as $assinatura) {

				$pontos_disponiveis = $this->buscaPontosDisponiveis($assinatura["id"]);

				while (count($pontos) > 0 && $pontos_disponiveis > 0) {

					$ponto_id = array_pop($pontos);

					$this->bancodedados->query("
						UPDATE
							tab_clientes_autenticacoes
						SET
							ass_id = {$assinatura["id"]},
							cau_velocidade_download = {$assinatura["parametros"]["pontos_velocidade_download"]},
							cau_velocidade_upload = {$assinatura["parametros"]["pontos_velocidade_upload"]},
							cau_publicado = 0
						WHERE
							cau_id = $ponto_id");

					$this->registraComentarioClienteAutenticacao($ponto_id, "<b>Ação:</b> Migrado do Contrato Cancelado ($codigo_contrato)");

					$pontos_disponiveis--;

				}

			}
			
			while (count($pontos) > 0) {

				$ponto_id = array_pop($pontos);

				$this->bancodedados->query("
					UPDATE
						tab_clientes_autenticacoes
					SET
						cas_id = 3,
						cau_ajustado = 0,
						cau_ajuste_download = 0,
						cau_ajuste_upload = 0,
						cau_publicado = 0
					WHERE
						cau_id = $ponto_id");

				$this->registraComentarioClienteAutenticacao($ponto_id, "<b>Ação:</b> Cancelado - Contrato Cancelado ($codigo_contrato)");

			}
			
		}
		
	}

	function cancelaEmails($assinatura_id, $assinaturas_canceladas, $cliente_id, $codigo, $usuario_id) {
		
		$assinaturas = array();
		
		foreach ($assinaturas_canceladas as $assinatura) {
			$assinaturas[] = $assinatura["id"];
		}
		
		$emails = $this->buscaEmailsAssinatura($assinatura_id, "1, 2");

		foreach ($emails as $email) {

			$assinatura_ativa = $this->buscaAssinaturaDisponivel($cliente_id, $assinaturas);

			if ($assinatura_ativa) {

				$this->bancodedados->query("
					UPDATE
						tab_clientes_emails
					SET
						ass_id = {$assinatura_ativa["id"]}
					WHERE
						cle_id = {$email["id"]}");

				$this->registraComentarioClienteEmail($email["id"], "<b>Ação:</b> Migrado do Contrato Cancelado ($codigo)", $usuario_id);

			} else {

				$this->bancodedados->query("
					UPDATE
						tab_clientes_emails
					SET
						cle_cancelamento = NOW(),
						ces_id = 3,
						cle_bloqueado = 0,
						cle_publicado = 0
					WHERE
						cle_id = {$email["id"]}");

				$this->registraComentarioClienteEmail($email["id"], "<b>Ação:</b> Cancelado - Contrato Cancelado ($codigo)", $usuario_id);

			}

		}
		
	}

	function buscaPontosDisponiveis($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
 				ass_parametros AS parametros
			FROM
				tab_assinaturas
			WHERE
				ass_id = $assinatura_id");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$pontos = intval($dados["parametros"]["pontos_quantidade"], 10) - $this->buscaPontosQuantidade($assinatura_id);
			if ($pontos > 0) {
				return $pontos;
			}
		}
		
		return 0;
		
	}

	function buscaPontosQuantidade($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_clientes_pontos
			WHERE
				ass_id = $assinatura_id AND
				cps_id IN (1, 2)");
				
		$pontos = (int) $this->bancodedados->resultado($query);
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_clientes_autenticacoes
			WHERE
				ass_id = $assinatura_id AND
				cas_id IN (1, 2)");
		
		$autenticacoes = (int) $this->bancodedados->resultado($query);
		
		return $pontos + $autenticacoes;
		
	}

	function descancelaEmails($assinatura_id, $codigo) {
		
		$emails = $this->buscaEmailsAssinatura($assinatura_id, "3, 4");
		
		foreach ($emails as $email) {

			$this->bancodedados->query("
				UPDATE
					tab_clientes_emails
				SET
					cle_cancelamento = NULL,
					ces_id = 1,
					cle_bloqueado = 0,
					cle_publicado = 0
				WHERE
					cle_id = {$email["id"]}");

			$this->registraComentarioClienteEmail($email["id"], "<b>Ação:</b> Ativado - Contrato Descancelado ($codigo)", $_SESSION["usuario"]);

			if (!$this->clientes_emails) {
				$this->clientes_emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, $this->permissoes, false);
			}

			$this->clientes_emails->solicitaClienteEmailPublicacao($email["id"]);

		}
		
	}

	function migraEmails($assinatura_id, $assinaturas_removidas, $usuario) {
		
		$assinaturas = array();
		
		foreach ($assinaturas_removidas as $assinatura) {
			$assinaturas[] = $assinatura["ass_id"];
		}
		
		$emails = $this->buscaEmailsAssinatura($assinatura_id, "1, 2");
		
		foreach ($emails as $email) {
			if ($email["servico_id"] != 11 && ($assinatura = $this->buscaAssinaturaDisponivel($email["cliente_id"], $assinaturas)) != false) {

				$this->bancodedados->query("
					UPDATE
						tab_clientes_emails
					SET
						ass_id = {$assinatura["id"]}
					WHERE
						cle_id = {$email["id"]}");

				$this->registraComentarioClienteEmail($email["id"], "<b>Ação:</b> Assinatura Migrada (E-mail Migrado)", $usuario);

			} else {

				$this->bancodedados->query("
					UPDATE
						tab_clientes_emails
					SET
						cle_cancelamento = NOW(),
						ces_id = 3,
						cle_bloqueado = 0,
						cle_publicado = 0
					WHERE
						cle_id = {$email["id"]}");

				$this->registraComentarioClienteEmail($email["id"], "<b>Ação:</b> Assinatura Migrada (E-mail Cancelado)", $usuario);

			}
		}
		
	}

	function buscaAssinaturaDisponivel($cliente_id, $assinaturas_removidas) {
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				tab_assinaturas.ass_id AS id,
 				tab_assinaturas.ass_parametros AS parametros
			FROM
				tab_planos
				INNER JOIN tab_assinaturas ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE".($assinaturas_removidas ? "
				tab_assinaturas.ass_id NOT IN (".implode(", ", $assinaturas_removidas).") AND" : "")."
				tab_assinaturas.ast_id IN (1, 2) AND
				tab_assinaturas.cli_id = $cliente_id
			ORDER BY
				id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			if (($dados["parametros"]["emails_quantidade"] - $this->buscaEmailsQuantidade($dados["id"])) > 0) {
				return $dados;
			}
		}
		
	}

	function buscaEmailsQuantidade($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(cle_id)
			FROM
				tab_clientes_emails
			WHERE
				ass_id = $assinatura_id AND
				ces_id IN (1, 2)");
		
		return $this->bancodedados->resultado($query);
		
	}

	function cancelaDominios($assinatura_id) {
		
		$this->bancodedados->query("
			UPDATE 
				tab_dominios 
			SET 
				dos_id = 5 
			WHERE 
				ass_id = $assinatura_id");
		
	}

	function descancelaDominios($assinatura_id) {
		
		$this->bancodedados->query("
			UPDATE 
				tab_dominios 
			SET 
				dos_id = 3 
			WHERE 
				ass_id = $assinatura_id");
		
	}

	function cancelaServicosAjustes($cliente_id) {
		
		$ajustes = $this->buscaAjustesCancelaveis($cliente_id);
		
		foreach ($ajustes as $ajuste_id) {
			$this->bancodedados->query("
				UPDATE 
					tab_servicos_ajustes 
				SET 
					saj_termino = NOW(), 
					sas_id = 6 
				WHERE 
					saj_id = $ajuste_id");
			// Registra Comentário
			$this->registraComentarioServicoAjuste($ajuste_id, "<b>Ação:</b> Contrato Cancelado");
		}
		
	}

	function buscaAjustesCancelaveis($cliente_id) {
		
		$ajustes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				tab_servicos_ajustes.saj_id AS id
			FROM
				tab_servicos_ajustes
				LEFT OUTER JOIN rel_saj_ass_contem ON (tab_servicos_ajustes.saj_id = rel_saj_ass_contem.saj_id)
				LEFT OUTER JOIN tab_assinaturas ON (rel_saj_ass_contem.ass_id = tab_assinaturas.ass_id)
			WHERE
				tab_servicos_ajustes.cli_id = $cliente_id AND
				tab_servicos_ajustes.sas_id IN (5, 9)
			GROUP BY
				id
			HAVING
				SUM(IF(tab_assinaturas.ast_id IN (2, 5), 1, 0)) = 0");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}
		
		return $ajustes;
		
	}

	function descancelaServicosAjustes($assinatura_id) {
		
		$this->bancodedados->query("
			UPDATE
				tab_servicos_ajustes
			SET
				saj_termino = NULL,
				sas_id = 5
			WHERE
				ass_id = $assinatura_id");
		
	}

	function abreChamadoFinanceiro($dados_contrato) {
		
		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
			  tab_planos.pla_nome AS plano,
			  tab_assinaturas_status.ast_nome AS status
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_detalhes ON (tab_cobrancas.coa_id = tab_cobrancas_detalhes.coa_id)
				INNER JOIN tab_assinaturas ON (tab_cobrancas_detalhes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN tab_planos ON (tab_assinaturas.pla_id = tab_planos.pla_id)
				INNER JOIN tab_assinaturas_status ON (tab_assinaturas.ast_id = tab_assinaturas_status.ast_id)
			WHERE
				tab_assinaturas.cli_id = {$dados_contrato["cliente"]} AND
				tab_assinaturas.ast_id IN (3, 4, 5) AND
				tab_cobrancas.cot_id = 1 AND
				tab_cobrancas_detalhes.cod_competencia_termino > tab_assinaturas.ass_data_cancelamento");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = "{$dados["plano"]} - {$dados["status"]}";
		}

		$query = $this->bancodedados->query("
			SELECT DISTINCT
			  tab_ajustes_financeiros_categorias.afc_nome AS categoria
			FROM
				tab_ajustes_financeiros
				INNER JOIN tab_ajustes_financeiros_categorias ON (tab_ajustes_financeiros.afc_id = tab_ajustes_financeiros_categorias.afc_id)
			WHERE
				tab_ajustes_financeiros.cor_id = {$dados_contrato["id"]} AND
				tab_ajustes_financeiros.afs_id = 5 AND
				(SELECT COUNT(*) FROM tab_cobrancas_detalhes WHERE tab_cobrancas_detalhes.ajf_id = tab_ajustes_financeiros.ajf_id) = 0");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $dados["categoria"];
		}

		if (count($assinaturas)) {
			
			$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
			
			$dados = array();
			
			$dados["conteudo"] = "Verificar cobranças do contrato {$dados_contrato["codigo"]}:";
			$dados["conteudo"] .= "[d]".implode("\n", $assinaturas)."[/d]";
			$dados["sistema"] = true;

			$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_contrato["cliente"], 104);

			if ($chamado_id) {
				// Registra Etapa
				$dados_chamado = $chamados->buscaDadosChamado($chamado_id, true);
				$chamados->registraEtapa($dados_chamado, 5, $dados);
			} else {
				$dados["solicitante"] = "Sistema (Intranet)";
				$dados["categoria"] = 104;
				$dados["prioridade"] = 3;
				$dados["tipo"] = 5;
				// Adiciona Chamado
				$chamados->adicionarChamado($dados_contrato["cliente"], null, $dados);
			}
			
		}
		
	}

	function cancelaAjustesFinanceiros($assinatura_id, $data_cancelamento) {
		
		$this->bancodedados->query("
			UPDATE
				tab_ajustes_financeiros
			SET
				afs_id = IF(afs_id < 5, 4, IF(afs_id = 5, 6, afs_id))
			WHERE
				ass_id = $assinatura_id");
		
	}

	function descancelaPontos($assinatura_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_pontos.clp_id AS id,
				tab_clientes_pontos.clp_regra AS regra,
				tab_servidores_interfaces.ser_id AS servidor_id
			FROM
				tab_clientes_pontos
				INNER JOIN tab_servidores_interfaces ON (tab_clientes_pontos.sei_id = tab_servidores_interfaces.sei_id)
			WHERE
				tab_clientes_pontos.ass_id = $assinatura_id");
				
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
		
			$regra = $dados["regra"] ? $dados["regra"] : $this->buscaProximaRegra($dados["servidor_id"]);

			$this->bancodedados->query("
				UPDATE
					tab_clientes_pontos
				SET
					cps_id = 1,
					clp_publicado = 0,
					clp_regra = $regra
				WHERE
					clp_id = {$dados["id"]}");

		}
		
	}

	function descancelaAutenticacoes($assinatura_id) {
		
		$this->bancodedados->query("
			UPDATE
				tab_clientes_autenticacoes
			SET
				cas_id = 1,
				cau_publicado = 0
			WHERE
				ass_id = $assinatura_id");
		
	}

	function buscaUltimaCompetenciaAjustes($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				MAX(ajf_competencia_termino)
			FROM
				tab_ajustes_financeiros
			WHERE
				cli_id = $cliente_id AND
				ajf_competencia_termino IS NOT NULL AND
				afs_id = 5");
		
		return $this->bancodedados->resultado($query);
		
	}

	function buscaAjustesSemValidade($cliente_id) {
		
		$ajustes = array();
		
		$query = $this->bancodedados->query("
			SELECT
				ajf_id AS id
			FROM
				tab_ajustes_financeiros
			WHERE
				cli_id = $cliente_id AND
				ass_id IS NULL AND
				cor_id IS NULL AND
				afs_id = 5 AND
				ajf_competencia_termino IS NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$ajustes[] = $dados["id"];
		}
		
		return $ajustes;
		
	}

	function cancelaDescontos($contrato_id) {
		
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				INNER JOIN rel_dec_cor_estacontido ON (tab_contratos.cor_id = rel_dec_cor_estacontido.cor_id)
				INNER JOIN tab_descontos_concessoes ON (rel_dec_cor_estacontido.dec_id = tab_descontos_concessoes.dec_id)
			SET
				dcs_id = IF(dcs_id IN (1, 2, 5), 6, dcs_id)
			WHERE
				tab_contratos.cor_id = $contrato_id");
		
	}

	function cancelaAssinatura($assinatura_id, $data_cancelamento) {
		
		// Cancela Assinatura
		$this->bancodedados->query("
			UPDATE
				tab_assinaturas
			SET
				ass_data_cancelamento = '$data_cancelamento',
				ast_id = 3
			WHERE
				ass_id = $assinatura_id AND
				ast_id IN (2, 5)");

		$this->bancodedados->query("
			UPDATE
				tab_assinaturas
			SET
				ass_data_cancelamento = '$data_cancelamento',
				ast_id = 3
			WHERE
				ass_pai = $assinatura_id AND
				ast_id IN (2, 5)");
		
		// Remove Vínculos
		$this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_id = $assinatura_id");
		$this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_filha = $assinatura_id");
		
	}

	function cancelaComodato($assinatura_id, $data_cancelamento, $dados_contrato){
				
		$equipamentos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_clientes_comodatos.clc_mac_address AS mac_address,
				tab_equipamentos.equ_nome AS equipamento_nome,
				tab_equipamentos.equ_id AS equipamento_id,
				tab_equipamentos_modelos.eqm_id AS equipamento_modelo_id,
				tab_equipamentos_modelos.eqm_nome AS equipamento_modelo_nome
			FROM
				tab_clientes_comodatos				
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
				LEFT JOIN tab_equipamentos_modelos ON (tab_clientes_comodatos.eqm_id = tab_equipamentos_modelos.eqm_id)
			WHERE
				tab_clientes_comodatos.ass_id = $assinatura_id AND
				tab_clientes_comodatos.clc_substituto_id IS NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$this->bancodedados->query("UPDATE tab_clientes_comodatos SET ccs_id = 5, clc_data_termino = '$data_cancelamento', clc_data_retirada = '$data_cancelamento' WHERE clc_id = {$dados["id"]}");

			if ($dados["equipamento_modelo_id"] || $dados["mac_address"]) {
				$equipamentos[] = $dados;
			}
			
		}
		
		if ($dados_contrato["origem_id"] == 1 && count($equipamentos) > 0) {
			$this->abreChamadoRetiradaEquipamentos($dados_contrato, $equipamentos, $data_cancelamento);
		}
		
	}

	function buscaRadio($contrato_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cau_equipamento_modelo
			FROM
				tab_clientes_autenticacoes
				INNER JOIN rel_ass_cor_estacontido ON (tab_clientes_autenticacoes.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id AND
				cau_equipamento_modelo IS NOT NULL AND
				cau_equipamento_modelo != ''
			LIMIT 1");
		
		return $this->bancodedados->resultado($query);
	
	}

	function anularCancelamentoContrato($dados_contrato) {
		
		// Cancela Ajuste Multa
		$query = $this->bancodedados->query("
			SELECT
				ajf_id AS id
			FROM
				tab_ajustes_financeiros
			WHERE
				cor_id = {$dados_contrato["id"]} AND
				afs_id = 5");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET afs_id = 6 WHERE ajf_id = {$dados["id"]}");
			$this->registraComentarioAjusteFinanceiro($dados["id"], "Cancelamento anulado");
		}
		
		$contrato_status = $this->possuiContrato($dados_contrato) ? 20 : 19;

		// Atualiza Dados Contratos
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
				LEFT OUTER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			SET
				cor_data_cancelamento = IF(crs_id = 21, NULL, cor_data_cancelamento),
				ass_data_cancelamento = IF(ast_id = 5, NULL, ass_data_cancelamento),
				crs_id = IF(crs_id = 21, {$contrato_status}, crs_id),
				ast_id = IF(ast_id = 5, 2, ast_id)
			WHERE
				tab_contratos.cor_id = {$dados_contrato["id"]}");
				
				
		$this->bancodedados->query("
			UPDATE
				tab_contratos
				LEFT OUTER JOIN rel_ass_cor_estacontido ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
				LEFT OUTER JOIN tab_assinaturas ON (rel_ass_cor_estacontido.ass_id = tab_assinaturas.ass_id)
			SET
				cor_data_cancelamento = IF(crs_id = 21, NULL, cor_data_cancelamento),
				ass_data_cancelamento = IF(ast_id = 5, NULL, ass_data_cancelamento),
				crs_id = IF(crs_id = 21, {$contrato_status}, crs_id),
				ast_id = IF(ast_id = 5, 2, ast_id)
			WHERE
				tab_contratos.cor_pai = {$dados_contrato["id"]}");
		
		// Altera Solicitação de Cancelamento p/ Retido
		$this->bancodedados->query("
			UPDATE
				tab_cancelamentos
			SET
				cas_id = 3
			WHERE
				cor_id = {$dados_contrato["id"]} AND
				cas_id = 4");
		
		// Registra comentário no contrato
		$this->registraComentario($dados_contrato["id"], 24, $_POST["comentario"], $_SESSION["usuario"]);
		
		// Registra comentário nos aditivos
		for ($i = 0; $i < sizeof($dados_contrato["aditivos"]); $i++) {
			if (in_array($dados_contrato["aditivos"][$i]["status_id"], array(19, 20))) {
				$this->registraComentario($dados_contrato["aditivos"][$i]["id"], 24, $_POST["comentario"], $_SESSION["usuario"]);
			}
		}
		
		// Anula Cancelamento Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			if ($vinculo["status_id"] == 21) {
				$this->anularCancelamentoContrato($this->buscaDadosContrato($vinculo["contrato_id"]));
			}
		}

		//Retira data Equipamento Comodato
		$this->bancodedados->query("
			UPDATE
				tab_clientes_comodatos
			SET
				clc_data_termino = NULL, 
				clc_data_retirada = NULL,
				ccs_id = 2
			WHERE
				cor_id = {$this->dados_item["id"]} AND
				clc_substituto_id IS NULL");

		// Registra Atividade
		$atividade = controle::instanciaObjeto("atividades", $this->bancodedados);
		$atividade->registraAtividade($_SESSION["usuario"], $this->dados_modulo["id"], $this->dados_acao["nome"], $dados_contrato["id"]);
		
	}

	function buscaServicosAtivos($contrato_id) {
		
		$servicos = array();
		
		// Pontos
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_pontos.clp_endereco_mac AS mac
			FROM
				tab_clientes_pontos
				INNER JOIN tab_assinaturas ON (tab_clientes_pontos.ass_id = tab_assinaturas.ass_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				tab_assinaturas.ast_id = 1 AND
				rel_ass_cor_estacontido.cor_id = $contrato_id AND
				((cps_id = 1 AND !(clp_parametros_ajuste IS NOT NULL AND clp_parametros_ajuste = 'a:2:{s:8:\"download\";i:0;s:6:\"upload\";i:0;}' AND clp_publicado = 1)) OR (cps_id != 1 AND clp_publicado = 0))");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[] = "» Ponto de Acesso ({$dados["mac"]})";
		}
		
		// Autenticações
		$query = $this->bancodedados->query("
			SELECT
				CONCAT(cle_usuario, '@', tab_dominios.dom_dominio) AS email
			FROM
				tab_clientes_autenticacoes
				INNER JOIN tab_assinaturas ON (tab_clientes_autenticacoes.ass_id = tab_assinaturas.ass_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
				INNER JOIN tab_clientes_emails ON (tab_clientes_autenticacoes.cle_id = tab_clientes_emails.cle_id)
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
			WHERE
				tab_assinaturas.ast_id = 1 AND
				rel_ass_cor_estacontido.cor_id = $contrato_id AND
				((cas_id = 1 AND !(cau_ajuste_download IS NOT NULL AND cau_ajuste_download = 0 AND cau_publicado = 1)) OR (cas_id != 1 AND cau_publicado = 0))");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[] = "» Autenticação PPPoE ({$dados["email"]})";
		}
		
		// E-mails
		$query = $this->bancodedados->query("
			SELECT
				CONCAT(cle_usuario, '@', dom_dominio) AS email
			FROM
				tab_clientes_emails
				INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
				INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
				INNER JOIN rel_ass_cor_estacontido ON (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id)
			WHERE
				ast_id = 1 AND
				cor_id = $contrato_id AND
				((ces_id = 1 AND !(cle_bloqueado = 1 AND cle_publicado = 1)) OR (ces_id != 1 AND cle_publicado = 0))");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$servicos[] = "» E-mail ({$dados["email"]})";
		}
		
		return $servicos;
		
	}

	function removerContrato($dados_contrato, $remover_assinaturas = false) {
		
		$dados_usuario = $this->buscaDadosUsuario($_SESSION["usuario"]);

		// Insere Retorno Vendedor
		
		if ($dados_contrato["origem_id"] == 1) {
		
			$query = $this->bancodedados->query("
				SELECT DISTINCT
					usu_id AS usuario_id
				FROM
					tab_vendas
				WHERE
					cor_id = {$dados_contrato["id"]}");

			while (($usuarios = $this->bancodedados->resultadoArray($query)) != false) {
				$this->bancodedados->query("
					INSERT INTO
						tab_clientes_retornos
					SET
						cli_id = {$dados_contrato["cliente"]},
						usu_id = {$usuarios["usuario_id"]},
						clr_abertura = NOW(),
						clr_data = CURDATE(),
						clr_comentario = 'Contrato removido por {$dados_usuario["nome"]}',
						cet_id = 2,
						ces_id = 1");
			}
			
		}
		
		// Remove Venda
		if (($assinaturas_adicionadas = $this->buscaAssinaturasAdicionadas($dados_contrato["id"])) != false) {
			foreach ($assinaturas_adicionadas as $assinatura_adicionada) {
				$this->removeVenda($assinatura_adicionada["ass_id"]);
			}
		} else {
			$this->removeVenda(null, $dados_contrato["id"]);
		}
		
		// Aditivo Mudança Endereço -> Retorna Endereço Original
		if ($dados_contrato["tipo_id"] == 14 && !in_array($dados_contrato["status_id"], array(11, 12, 13, 14))) {
			$this->desmigraEnderecoCliente($dados_contrato["id"]);
			// Finaliza Ajuste Contrato Inativo
			$this->finalizaBloqueioContratoInativo($dados_contrato["pai"]);
		}
		
		// Remove Equipamentos comentarios
		$this->bancodedados->query("
			DELETE
				tab_clientes_comodatos_comentarios FROM tab_clientes_comodatos
				INNER JOIN tab_clientes_comodatos_comentarios ON (tab_clientes_comodatos.clc_id = tab_clientes_comodatos_comentarios.clc_id)
			WHERE
				tab_clientes_comodatos.cor_id = {$dados_contrato["id"]}");

		// Remove equipamentos
		$this->bancodedados->query("
			DELETE FROM
				tab_clientes_comodatos
			WHERE
				cor_id = {$dados_contrato["id"]}");
		
		// Principal / Aditivo Alteração -> Remoção de pontos
		if (in_array($dados_contrato["tipo_id"], array(11, 12))) {
			foreach ($dados_contrato["assinaturas"] as $assinatura) {
				if ($dados_contrato["tipo_id"] == 11 || !$this->ehAssinaturaExistente($assinatura["id"], $dados_contrato["pai"])) {
					// Define Data Fechamento Assinatura igual a NULL
					$this->bancodedados->query("
						UPDATE
							tab_assinaturas
						SET
							ass_data_fechamento = NULL,
							ass_data_confirmacao = NULL
						WHERE
							ass_id = {$assinatura["id"]}");
					// Remove Pontos
					$this->removePontos($assinatura["id"]);
					// Remove Autenticacoes
					$this->removeAutenticacoes($assinatura["id"]);
					// Remove Assinatura Emails
					$this->removeEmails($assinatura["id"]);
					// Remove Assinatura Ajustes
						foreach ($assinatura["servicos_ajustes"] as $ajuste) {
							// Remove Links Ajuste/Assinatura/Base de Dados/Email/Hospedagem/Ponto
						$this->bancodedados->query("DELETE FROM rel_saj_ass_contem WHERE saj_id = {$ajuste["id"]}");
							// Remove Comentários e Regras Ajuste
						$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_comentarios WHERE saj_id = {$ajuste["id"]}");
							// Remove Regras Ajuste
						$this->bancodedados->query("DELETE FROM tab_servicos_ajustes_regras WHERE saj_id = {$ajuste["id"]}");
							// Remove Ajuste
						$this->bancodedados->query("DELETE FROM tab_servicos_ajustes WHERE saj_id = {$ajuste["id"]}");
					}
				}
			}
		}
		
		if ($dados_contrato["tipo_id"] == 11) {
			$clientes_iptv = controle::instanciaObjeto("clientes_iptv", $this->bancodedados, $this->permissoes, false);
			$clientes_iptv->removeIptv($dados_contrato["id"]);
		}
		
		// Finaliza Bloqueio Contrato Principal Inativo
		if (!$dados_contrato["pai"]) {
			$this->finalizaBloqueioContratoInativo($dados_contrato["id"]);
		}
		
		// Remove Links Contratos/Descontos/Assinaturas / Descontos
		$this->bancodedados->query("DELETE FROM rel_ass_cor_estacontido WHERE cor_id = {$dados_contrato["id"]}");
		$this->bancodedados->query("DELETE FROM rel_dec_cor_estacontido WHERE cor_id = {$dados_contrato["id"]}");
		$this->bancodedados->query("DELETE FROM tab_contratos_comentarios WHERE cor_id = {$dados_contrato["id"]}");
		
		// Cancela Visita e Adiciona Comentário
		$query = $this->bancodedados->query("
			SELECT
				vis_id AS id
			FROM
				tab_visitas
			WHERE
				cor_id = {$dados_contrato["id"]}");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("UPDATE tab_visitas SET via_id = 4, cor_id = NULL WHERE vis_id = {$dados["id"]}");
			$this->registraComentarioVisita($dados["id"], "<b>Cancelada</b>: Contrato Removido (".$dados_contrato["codigo"].")", 18, $_SESSION["usuario"]);
		}
		
		// Remoção Contrato de Migração de Assinaturas
		if (($dados_contrato["tipo_id"] == 13) || $remover_assinaturas) {
			foreach ($dados_contrato["assinaturas"] as $assinatura) {
				// Delete descontos assinaturas migradas
				$this->bancodedados->query("DELETE FROM tab_descontos_concessoes WHERE ass_id = {$assinatura["id"]}");
				// Remove Ajuste Programado
				$this->removeAjusteProgramado($assinatura["id"]);
				// Deleta vinculos assinaturas migradas
				$this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_id = {$assinatura["id"]}");
				// Deleta vinculos assinaturas migradas
				$this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_id = {$assinatura["id"]}");
				$this->bancodedados->query("DELETE FROM tab_assinaturas_vinculos WHERE ass_filha = {$assinatura["id"]}");
				// Deleta assinaturas migradas
				$this->bancodedados->query("DELETE FROM tab_assinaturas WHERE ass_id = {$assinatura["id"]}");
			}
		}
		
		// Cancelar Ajuste e Cobrança de Instalação
		if (($cobranca = $this->buscaCobracaInstalacao($dados_contrato["id"], true)) != false) {
			if ($cobranca["status_id"] == 1) {
				$this->bancodedados->query("UPDATE tab_cobrancas SET cot_id = 12 WHERE coa_id = {$cobranca["id"]}");
				$this->bancodedados->query("UPDATE tab_cobrancas_detalhes SET cod_cancelado = 1 WHERE coa_id = {$cobranca["id"]}");
				$this->registraComentarioCobranca($cobranca["id"], "<b>Ação:</b> Cobrança Cancelada (Contrato Removido)");
			}
		}
		
		// Desvincula Ajustes de Instalação
		$this->bancodedados->query("UPDATE tab_ajustes_financeiros SET cor_id = NULL, afs_id = 6 WHERE cor_id = {$dados_contrato["id"]}");
		
		// Remove Vinculos
		$this->bancodedados->query("DELETE FROM tab_contratos_vinculos WHERE cor_id = {$dados_contrato["id"]}");
		$this->bancodedados->query("DELETE FROM tab_contratos_vinculos WHERE cor_filho = {$dados_contrato["id"]}");
		
		// Remove Contratos Vinculados
		foreach ($dados_contrato["vinculos"] as $vinculo) {
			$this->removerContrato($this->buscaDadosContrato($vinculo["contrato_id"]), $dados_contrato["tipo_id"] == 13);
		}
		
		// Remove Formas Pagamento
		$this->bancodedados->query("DELETE FROM rel_fop_cor_estacontido WHERE cor_id = {$dados_contrato["id"]}");
		
		// Remove Youcast Clientes
		$youCastDados = $this->buscarProdutoYoucast($dados_contrato["id"]);
		$codigoServico = addslashes($youCastDados["codigoServico"]);
		$codigoPlayer = $youCastDados["codigoPlayer"];
		if($codigoPlayer == 'youcast'){
			$sva_youcast = controle::instanciaObjeto("sva_youcast", $this->bancodedados);
			$sva_youcast->remover($dados_contrato["cliente"], $codigoServico);

			$this->bancodedados->query("UPDATE tab_youcast_clientes set status = 5, envido_email = 1 WHERE contrato = {$dados_contrato["id"]}");
		}

		// Remove Contrato
		$this->bancodedados->query("DELETE FROM tab_contratos WHERE cor_id = {$dados_contrato["id"]}");
		
		// Insere Chamado Residencial (Vistoria)
		if ($dados_contrato["tipo_id"] == 11 && in_array($dados_contrato["servico_id"], array(28, 33, 34))) {
			// Instancia Classe Chamados
			$chamados = controle::instanciaObjeto("chamados", $this->bancodedados, $this->permissoes, false);
			$chamado_categoria_id = $this->buscaCategoriaViabilidadeResidencial($dados_contrato["cliente"]);
			$chamado_id = $chamados->buscaChamadoClienteCategoria($dados_contrato["cliente"], $chamado_categoria_id);
			if ($chamado_id) {
			    $dados = array();
				$dados["conteudo"] = "Contrato removido";
				$dados["sistema"] = true;
				// Registra Etapa
				$chamados->registraEtapa($chamados->buscaDadosChamado($chamado_id, true), 5, $dados);
			}
		}
		
	}

	function ehAssinaturaExistente($assinatura_id, $contrato_id) {
		
		$query = $this->bancodedados->query("
			SELECT 
				COUNT(ass_id)
			FROM 
				rel_ass_cor_estacontido 
			WHERE 
				cor_id = $contrato_id AND 
				ass_id = $assinatura_id");
		
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function removeEmails($assinatura_id) {
		
		$emails = $this->buscaEmailsAssinatura($assinatura_id);
		
		foreach ($emails as $email) {
			$this->bancodedados->query("DELETE FROM rel_saj_cle_contem WHERE cle_id = {$email["id"]}");
			$this->bancodedados->query("DELETE FROM tab_clientes_emails_ajustes WHERE cle_id = {$email["id"]}");
			$this->bancodedados->query("DELETE FROM tab_clientes_emails_comentarios WHERE cle_id = {$email["id"]}");
			$this->bancodedados->query("DELETE FROM tab_clientes_emails WHERE cle_id = {$email["id"]}");
		}
		
	}

	function removeAutenticacoes($assinatura_id) {
		
		$autenticacoes = $this->buscaClienteAutenticacoesAssinatura($assinatura_id);
		
		foreach ($autenticacoes as $autenticacao_id) {
			$this->bancodedados->query("DELETE FROM rel_saj_cau_contem WHERE cau_id = $autenticacao_id");
			$this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes_ajustes WHERE cau_id = $autenticacao_id");
			$this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes_comentarios WHERE cau_id = $autenticacao_id");
			$this->bancodedados->query("DELETE FROM tab_clientes_autenticacoes WHERE cau_id = $autenticacao_id");
		}
		
	}

	function removePontos($assinatura_id) {
		
		$pontos = $this->buscaClientePontosAssinatura($assinatura_id);
		
		foreach ($pontos as $ponto_id) {
			$this->bancodedados->query("DELETE FROM rel_saj_clp_contem WHERE clp_id = $ponto_id");
			$this->bancodedados->query("DELETE FROM tab_clientes_pontos_ajustes WHERE clp_id = $ponto_id");
			$this->bancodedados->query("DELETE FROM tab_clientes_pontos_comentarios WHERE clp_id = $ponto_id");
			$this->bancodedados->query("DELETE FROM tab_clientes_pontos WHERE clp_id = $ponto_id");
		}
		
	}

	function buscaTiposEquipamentos($contrato_id, $vinculos){

		$contratos = array($contrato_id);

		if ($vinculos) {
			$contratos = array_merge($contratos, $vinculos);
		}

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes_comodatos.clc_id AS id,
				tab_equipamentos.equ_nome AS equipamento
			FROM
				tab_clientes_comodatos
				INNER JOIN tab_equipamentos ON (tab_clientes_comodatos.equ_id = tab_equipamentos.equ_id)
			WHERE
				tab_clientes_comodatos.cor_id IN (".implode(",", $contratos).") AND
				tab_clientes_comodatos.ccs_id = 1");

		return $this->bancodedados->resultadoArrayTotal($query);

	}

	function buscaCobranca($cliente_id, $data_ativacao) {
		
		$data_ativacao = substr($data_ativacao, 6, 4)."-".substr($data_ativacao, 3, 2)."-".substr($data_ativacao, 0, 2);
		
		$query = $this->bancodedados->query("
			SELECT
				coa_id AS id,
				coa_parametros AS parametros
			FROM
				tab_cobrancas
			WHERE
				cli_id = $cliente_id AND
				coa_data_geracao = '$data_ativacao' AND
				coa_enviada = 0 AND
				col_id IS NULL AND
				cot_id = 1");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$parametros = unserialize($dados["parametros"]);
			if ($parametros["boleto"]) {
				return $dados["id"];
			}
		}
		
	}

	function buscaDadosCobranca($cobranca_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.cli_id AS cliente_id,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas_formatos.cof_id AS formato_id,
				tab_cobrancas_formatos.cof_nome AS formato,
				tab_clientes.clo_id AS origem_id
			FROM
				tab_clientes
				INNER JOIN tab_cobrancas ON (tab_clientes.cli_id = tab_cobrancas.cli_id)
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
			  tab_cobrancas.coa_id = $cobranca_id");
			  
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
		
	}

	function buscaCondominial($condominio_id, $cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id
			FROM
				tab_clientes
				INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
			WHERE
				tab_clientes.cli_id != $cliente_id AND
				tab_clientes.con_id = $condominio_id AND
				tab_assinaturas.ast_id = 2 AND
				tab_assinaturas.pla_id = 244
			LIMIT
				0, 1");
		
		return $this->bancodedados->resultado($query);
		
	}

	function atualizaDiasBloqueioCliente($cliente_id) {
				
		$query = $this->bancodedados->query("
			SELECT
				clo_bloqueio_dias
			FROM
				tab_clientes_origens
			WHERE
				clo_id = (
					SELECT
						clo_id
					FROM
						tab_clientes
					WHERE
						cli_id = $cliente_id
				)");
		
		$bloqueio_dias = $this->bancodedados->resultado($query);
		
		// Atualiza Data Bloqueio Cliente
		$this->bancodedados->query("
            UPDATE
                tab_clientes
            SET
                cli_bloqueio_dias = ".($bloqueio_dias ? $bloqueio_dias : "NULL")."
            WHERE
                cli_id = $cliente_id AND
                cli_bloqueio_dias_customizado = 0");
		
		// Atualiza Data Bloqueio Cobranças
		$this->bancodedados->query("
            UPDATE
                tab_cobrancas
            SET
                coa_data_bloqueio = ".($bloqueio_dias ? "DATE_ADD(coa_data_vencimento, INTERVAL $bloqueio_dias DAY)" : "NULL")."
            WHERE cli_id = $cliente_id");
		
		return $bloqueio_dias;
		
	}
	
	function geraVisita($cliente_id, $contrato_id, $tipo_visita_id = 1) {

		$query = $this->bancodedados->query("
			SELECT
				vis_id
			FROM
				tab_visitas
			WHERE
				cor_id = $contrato_id AND
				vit_id = $tipo_visita_id");
		
		if (($visita_id = $this->bancodedados->resultado($query)) != false) {
			
			$this->bancodedados->query("
				UPDATE
					tab_visitas
				SET
					via_id = IF(vis_data IS NULL, 1, 2)
				WHERE
					vis_id = {$visita_id}");
			
			return $visita_id;
			
		}
			
		$this->bancodedados->query("
			INSERT INTO
				tab_visitas
			SET
				cli_id = $cliente_id,
				cor_id = $contrato_id,
				via_id = 1,
				vit_id = $tipo_visita_id,
				vis_data_geracao = CURDATE()");
		
		return $this->bancodedados->ultimoIdInserido();
		
	}

	function atualizaSituacaoCliente($cliente_id, $situacao_id) {

		$this->bancodedados->query("
			UPDATE
				tab_clientes
			SET
				clu_id = $situacao_id
			WHERE
				cli_id = $cliente_id");

	}

	function criaVinculoAssinaturas($assinatura_id, $assinatura_filha, $tipo_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				asv_id AS id
			FROM
				tab_assinaturas_vinculos
			WHERE
				ass_id = $assinatura_id AND
				ass_filha = $assinatura_filha");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("
				UPDATE
					tab_assinaturas_vinculos
				SET
					pvt_id = $tipo_id
				WHERE
					asv_id = {$dados["id"]}");
		} else {
			$this->bancodedados->query("
				INSERT INTO
					tab_assinaturas_vinculos
				SET
					ass_id = $assinatura_id,
					ass_filha = $assinatura_filha,
					pvt_id = $tipo_id");
		}
		
	}

	function criaVinculoContratos($contrato_id, $contrato_filho, $tipo_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				cov_id AS id
			FROM
				tab_contratos_vinculos
			WHERE
				cor_id = $contrato_id AND
				cor_filho = $contrato_filho");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$this->bancodedados->query("
				UPDATE
					tab_contratos_vinculos
				SET
					pvt_id = $tipo_id
				WHERE
					cov_id = {$dados["id"]}");
		} else {
			$this->bancodedados->query("
				INSERT INTO
					tab_contratos_vinculos
				SET
					cor_id = $contrato_id,
					cor_filho = $contrato_filho,
					pvt_id = $tipo_id");
		}
		
	}

	function buscaPlanosVinculosPai($assinatura_id) {
		
		$vinculos = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				assinaturas_pai.ass_id AS ass_id,
				tab_contratos.cor_id AS cor_id,
				tab_planos_vinculos.pvt_id AS tipo_id
			FROM
				tab_planos_vinculos
				INNER JOIN tab_assinaturas assinaturas_filho ON (assinaturas_filho.pla_id = tab_planos_vinculos.pla_filho)
				INNER JOIN tab_assinaturas assinaturas_pai ON (assinaturas_pai.pla_id = tab_planos_vinculos.pla_id AND assinaturas_pai.cli_id = assinaturas_filho.cli_id)
				LEFT OUTER JOIN tab_assinaturas_vinculos ON (tab_assinaturas_vinculos.ass_id = assinaturas_pai.ass_id AND tab_assinaturas_vinculos.ass_filha = assinaturas_filho.ass_id)
				INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = assinaturas_pai.ass_id)
				INNER JOIN tab_contratos ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
			WHERE
				assinaturas_filho.ass_id = $assinatura_id AND
				tab_assinaturas_vinculos.asv_id IS NULL AND
				tab_contratos.cor_pai IS NULL AND
				assinaturas_pai.ast_id = 2");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$vinculos[] = $dados;
		}
		
		return $vinculos;
		
	}

	function buscaPlanosVinculosFilho($assinatura_id) {
		
		$vinculos = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				assinaturas_filho.ass_id AS ass_id,
				tab_contratos.cor_id AS cor_id,
				tab_planos_vinculos.pvt_id AS tipo_id
			FROM
				tab_planos_vinculos
				INNER JOIN tab_assinaturas assinaturas_filho ON (assinaturas_filho.pla_id = tab_planos_vinculos.pla_filho)
				INNER JOIN tab_assinaturas assinaturas_pai ON (assinaturas_pai.pla_id = tab_planos_vinculos.pla_id AND assinaturas_pai.cli_id = assinaturas_filho.cli_id)
				INNER JOIN rel_ass_cor_estacontido ON (rel_ass_cor_estacontido.ass_id = assinaturas_filho.ass_id)
				INNER JOIN tab_contratos ON (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id)
			WHERE
				assinaturas_pai.ass_id = $assinatura_id AND
				tab_contratos.cor_pai IS NULL AND
				assinaturas_filho.ast_id = 2");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$vinculos[] = $dados;
		}
		
		return $vinculos;
		
	}

	function geraAjusteEstorno($cliente_id, $assinatura_id, $dias, $competencia) {
		
		if ($dias) {
			$this->bancodedados->query("
				INSERT INTO
					tab_ajustes_financeiros
				SET
					ajf_datahora = NOW(),
					cli_id = $cliente_id,
					ass_id = $assinatura_id,
					afc_id = 13,
					aft_id = 2,
					ajf_valor = $dias,
					ajf_valor_tipo = 3,
					ajf_competencia_inicio = '$competencia',
					ajf_competencia_termino = '$competencia',
					afs_id = 5");
		}
		
	}

	function possuiCobrancaPeriodo($assinatura_id, $competencia) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(coa_id)
			FROM
				tab_cobrancas_detalhes
			WHERE
				cod_cancelado = 0 AND
				ass_id = $assinatura_id AND
				'$competencia' BETWEEN cod_competencia_inicio AND cod_competencia_termino");
				
		return $this->bancodedados->resultado($query) > 0;
		
	}

	function buscaProximaCompetencia() {
		
		$query = $this->bancodedados->query("
			SELECT
				MAX(col_vencimento)
			FROM
				tab_cobrancas_lotes");
		
		return $this->bancodedados->resultado($query);
		
	}

	function finalizaBloqueioContratoInativo($contrato_id) {
		
		$this->bancodedados->query("
			UPDATE
				rel_ass_cor_estacontido
				INNER JOIN rel_saj_ass_contem ON (rel_saj_ass_contem.ass_id = rel_ass_cor_estacontido.ass_id)
				INNER JOIN tab_servicos_ajustes ON (rel_saj_ass_contem.saj_id = tab_servicos_ajustes.saj_id)
				LEFT OUTER JOIN tab_clientes_autenticacoes ON (rel_saj_ass_contem.ass_id = tab_clientes_autenticacoes.ass_id)
				LEFT OUTER JOIN tab_clientes_pontos ON (rel_saj_ass_contem.ass_id = tab_clientes_pontos.ass_id)
				LEFT OUTER JOIN tab_clientes_emails ON (rel_saj_ass_contem.ass_id = tab_clientes_emails.ass_id)
			SET
				tab_servicos_ajustes.saj_termino = NOW(),
				tab_servicos_ajustes.sas_id = 8,
				tab_clientes_autenticacoes.cau_ajuste_download = NULL,
				tab_clientes_autenticacoes.cau_ajuste_upload = NULL,
				tab_clientes_autenticacoes.cau_ajustado = 0,
				tab_clientes_autenticacoes.cau_publicado = 0,
				tab_clientes_pontos.clp_publicado = 0,
				tab_clientes_pontos.clp_ajustado = 0,
				tab_clientes_pontos.clp_parametros_ajuste = '',
				tab_clientes_emails.cle_publicado = 0,
				tab_clientes_emails.cle_bloqueado = 0
			WHERE
				rel_ass_cor_estacontido.cor_id = $contrato_id AND
				tab_servicos_ajustes.sas_id <= 5 AND
				tab_servicos_ajustes.sac_id = 3");
		
	}

	function buscaPontos($assinatura_id) {
		
		$pontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				clp_id AS id
			FROM
				tab_clientes_pontos
			WHERE
				ass_id = $assinatura_id AND
				cps_id <> 3");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$pontos[] = $dados["id"];
		}
		
		return $pontos;
		
	}

	function buscaAutenticacoes($assinatura_id) {
		
		$pontos = array();
		
		$query = $this->bancodedados->query("
			SELECT
				cau_id AS id
			FROM
				tab_clientes_autenticacoes
			WHERE
				ass_id = $assinatura_id AND
				cas_id <> 3");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$pontos[] = $dados["id"];
		}
		
		return $pontos;
		
	}

	function adicionaBloqueioAssinatura($ajuste_id, $assinatura_id) {
		
		$this->bancodedados->query("
	 		INSERT INTO
	 			rel_saj_ass_contem
			SET
				saj_id = $ajuste_id,
				ass_id = $assinatura_id");
		
	}

	function adicionaBloqueioPonto($ajuste_id, $ponto_id) {
		
		$this->bancodedados->query("
	 		INSERT INTO
	 			rel_saj_clp_contem
			SET
				saj_id = $ajuste_id,
				clp_id = $ponto_id");
		
	}

	function adicionaBloqueio($cliente_id) {
		
		$dados_categoria = $this->buscaDadosCategoria(3);

		$this->bancodedados->query("
			INSERT INTO
				tab_servicos_ajustes
			SET
				cli_id = $cliente_id,
				sac_id = 3,
				saj_inicio = NOW(),
				sas_id = 5");
		
		$ajuste_id = $this->bancodedados->ultimoIdInserido();
		
		$this->registraRegras($ajuste_id, $dados_categoria["regras"]);
		
		return $ajuste_id;
		
	}

	function buscaDadosCategoria($categoria_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				sac_regras as regras,
				sac_editavel as editavel
			FROM
				tab_servicos_ajustes_categorias
			WHERE
				sac_id = $categoria_id");
				
		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["regras"] = unserialize($dados["regras"]);
			return $dados;
		}
		
	}

	function buscaAssinaturasAdicionadas($contrato_id) {

		$assinaturas = array();

		$query = $this->bancodedados->query("
			SELECT DISTINCT
				contratos_pai.cor_id AS cor_id,
				rel_filho.ass_id AS ass_id,
				tab_assinaturas.ass_migrada AS ass_migrada,
				tab_assinaturas.ass_parametros AS parametros,
				tab_planos.pla_nome AS plano_nome,
				planos_migrada.pla_nome AS plano_nome_migrada,
				tab_assinaturas.pli_id AS sistema
			FROM
				tab_contratos contratos_filho
				LEFT OUTER JOIN tab_contratos contratos_pai ON (contratos_pai.cor_id = contratos_filho.cor_pai)
				LEFT OUTER JOIN rel_ass_cor_estacontido rel_filho ON (rel_filho.cor_id = contratos_filho.cor_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido rel_pai ON (rel_pai.cor_id = contratos_pai.cor_id) AND (rel_pai.ass_id = rel_filho.ass_id)
				LEFT OUTER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = rel_filho.ass_id)
				LEFT OUTER JOIN tab_assinaturas assinaturas_migrada ON (assinaturas_migrada.ass_id = tab_assinaturas.ass_migrada)
				LEFT OUTER JOIN tab_planos ON (tab_planos.pla_id = tab_assinaturas.pla_id)
				LEFT OUTER JOIN tab_planos planos_migrada ON (planos_migrada.pla_id = assinaturas_migrada.pla_id)
			WHERE
				contratos_filho.cor_id = $contrato_id AND
				rel_pai.ass_id IS NULL AND
				rel_filho.ass_id IS NOT NULL");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			$assinaturas[] = $dados;
		}

		return $assinaturas;

	}

	function buscaAssinaturasRemovidas($contrato_id) {
		
		$assinaturas = array();		
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				contratos_pai.cor_id AS cor_id,
				rel_pai.ass_id AS ass_id
			FROM
				tab_contratos contratos_filho
				LEFT OUTER JOIN tab_contratos contratos_pai ON (contratos_pai.cor_id = contratos_filho.cor_pai)
				LEFT OUTER JOIN rel_ass_cor_estacontido rel_pai ON (rel_pai.cor_id = contratos_pai.cor_id)
				LEFT OUTER JOIN rel_ass_cor_estacontido rel_filho ON (rel_filho.cor_id = contratos_filho.cor_id) AND (rel_filho.ass_id = rel_pai.ass_id)
			WHERE
				contratos_filho.cor_id = $contrato_id AND
				rel_filho.ass_id IS NULL AND
				rel_pai.ass_id IS NOT NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $dados;
		}
		
		return $assinaturas;
		
	}

	function buscaDescontosAdicionados($contrato_id) {
		
		$assinaturas = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				contratos_pai.cor_id AS cor_id,
				rel_filho.dec_id AS dec_id
			FROM
				tab_contratos contratos_filho
				LEFT OUTER JOIN tab_contratos contratos_pai ON (contratos_pai.cor_id = contratos_filho.cor_pai)
				LEFT OUTER JOIN rel_dec_cor_estacontido rel_filho ON (rel_filho.cor_id = contratos_filho.cor_id)
				LEFT OUTER JOIN rel_dec_cor_estacontido rel_pai ON (rel_pai.cor_id = contratos_pai.cor_id) AND (rel_pai.dec_id = rel_filho.dec_id)
			WHERE
				contratos_filho.cor_id = $contrato_id AND
				rel_pai.dec_id IS NULL AND
				rel_filho.dec_id IS NOT NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$assinaturas[] = $dados;
		}
		
		return $assinaturas;
		
	}

	function buscaDescontosRemovidos($contrato_id) {
		
		$descontos = array();
		
		$query = $this->bancodedados->query("
			SELECT DISTINCT
				contratos_pai.cor_id AS cor_id,
				rel_pai.dec_id AS dec_id
			FROM
				tab_contratos contratos_filho
				LEFT OUTER JOIN tab_contratos contratos_pai ON (contratos_pai.cor_id = contratos_filho.cor_pai)
				LEFT OUTER JOIN rel_dec_cor_estacontido rel_pai ON (rel_pai.cor_id = contratos_pai.cor_id)
				LEFT OUTER JOIN rel_dec_cor_estacontido rel_filho ON (rel_filho.cor_id = contratos_filho.cor_id) AND (rel_filho.dec_id = rel_pai.dec_id)
			WHERE
				contratos_filho.cor_id = $contrato_id AND
				rel_filho.dec_id IS NULL AND
				rel_pai.dec_id IS NOT NULL");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$descontos[] = $dados;
		}
		
		return $descontos;
		
	}
	
	function buscaContratosAguardandoMudancaEndereco() {
		
		$contratos = array();
		
		$query = "
			SELECT
				tab_contratos.cor_id AS id
			FROM
				tab_contratos
				INNER JOIN tab_visitas ON (tab_contratos.cor_id = tab_visitas.cor_id)
			WHERE
				tab_contratos.crt_id = 14 AND
				tab_contratos.crs_id IN (13, 15, 17, 18) AND
				tab_visitas.vis_data = CURDATE() AND
				tab_visitas.via_id = 2";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}
		
		return $contratos;
		
	}
	
	function buscaContratosAguardandoAtivacao() {
		
		$contratos = array();
		
		$query = "
			SELECT
				cor_id AS id
			FROM
				tab_contratos
			WHERE
				crs_id = 18 AND
				cor_data_ativacao <= CURDATE()";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contratos[] = $dados["id"];
		}
		
		return $contratos;
		
	}

}

?>
