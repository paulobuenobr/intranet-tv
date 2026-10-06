<?

set_time_limit(0);
ignore_user_abort(1);

require_once("_global/modulo.class.inc.php");

class cliente_credito_saldo extends modulo {

	/**
	 * Construtor da classe
	 * @param bancodedados $bancodedados Conexão com o banco de dados
	 * @param array $permissoes Permissões do usuário
	 * @param bool $executar Se true, executa a ação
	 */
	function __construct($bancodedados, $permissoes, $executar = true) {

		$this->bancodedados = $bancodedados;
		$this->permissoes = $permissoes;

		if ($executar) {
			$this->buscaDadosModulo($_GET["m"]);
			$this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "listar");
			$this->conteudo = $this->geraTitulo();
			$this->{$this->acao}();
		}
	}

	/**
	 * Exibe a tela de listagem de parceiros
	 */
	function listar() {
		$clo_id = $this->resolveCloId();
		header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver&i={$clo_id}");
	}

	/**
	 * Resolve o ID do cliente parceiro
	 * @return int ID do cliente parceiro
	 */
	function resolveCloId() {

		$clo_id = preg_replace("/[^0-9]/", "", $_GET["i"]);

		if ($this->temPermissao("ver_plus") && $clo_id && $_SESSION["origem"] == 0) {
			return (int) $clo_id;
		}

		return (int) $_SESSION["origem"];
	}

	/**
	 * Busca os dados do parceiro
	 * @param int $clo_id ID do cliente parceiro
	 * @return void
	 */
	function buscaDadosParceiro($clo_id) {

		$clo_id = preg_replace("/[^0-9]/", "", $clo_id);

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome,
				clo_cnpj AS cnpj
			FROM
				tab_clientes_origens
			WHERE
				clo_id = $clo_id AND
				cos_id = 2
			LIMIT 1");

		$this->dados_item = $this->bancodedados->resultadoArray($query);

		if (!$this->dados_item) {
			unset($this->dados_item);
		}
	}

	/**
	 * Busca os parceiros
	 * @return array Array de parceiros
	 */
	function buscaParceiros() {

		$parceiros = array();

		$query = $this->bancodedados->query("
			SELECT
				clo_id AS id,
				clo_nome AS nome
			FROM
				tab_clientes_origens
			WHERE
				cos_id = 2
			ORDER BY
				clo_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$parceiros[$dados["id"]] = $dados["nome"];
		}

		return $parceiros;
	}

	/**
	 * Gera o select de parceiros
	 * @param int $clo_id ID do cliente parceiro
	 * @param string $acao Ação
	 * @return string HTML do select
	 */
	function geraSelectParceiro($clo_id, $acao) {

		if (!$this->temPermissao("ver_plus")) {
			return "";
		}

		$parceiros = $this->buscaParceiros();
		$url = "?m={$this->dados_modulo["nome"]}&a={$acao}&i=";
		$onchange = "window.location='{$url}'+this.value";

		return "
			<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
				<td width=\"150\"><b>&nbsp;Cliente Parceiro:</b></td>
				<td>" . $this->geraSelect($parceiros, $clo_id, false, "clo_id", "350px", $onchange) . "</td>
			</tr>";
	}

	/**
	 * Formata um valor para o formato de moeda
	 * @param float $valor Valor a ser formatado
	 * @return string Valor formatado
	 */
	function formatValor($valor) {

		return "R$ " . number_format((float) $valor, 2, ",", ".");
	}

	/**
	 * Busca os planos proxy
	 * @param int $clo_id ID do cliente parceiro
	 * @return array Array de planos proxy
	 */
	function buscaPlanosProxy($clo_id) {

		$clo_id = preg_replace("/[^0-9]/", "", $clo_id);
		$planos = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_planos1.pla_nome AS plano_proxy,
				tab_planos1.pla_id AS id,
				tab_planos1.pla_valor_max AS valor_credito,
				SUM(tab_planos1.pla_valor_max) AS credito_total,
				(
					SELECT
						SUM(tab_planos_limitacao.valor)
					FROM
						tab_assinaturas
						INNER JOIN tab_assinaturas_planos_proxy ON (
							tab_assinaturas_planos_proxy.ass_id = tab_assinaturas.ass_id AND
							tab_assinaturas_planos_proxy.pla_id = tab_assinaturas.pla_id
						)
						INNER JOIN tab_planos_limitacao ON (
							tab_planos_limitacao.pla_id = tab_assinaturas_planos_proxy.pla_proxy_id AND
							tab_planos_limitacao.plano = tab_assinaturas.pla_id
						)
						INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
					WHERE
						tab_clientes.clo_id = tab_clientes_origens.clo_id AND
						tab_assinaturas.ast_id IN (1, 2, 5) AND
						tab_assinaturas_planos_proxy.pla_proxy_id = tab_planos1.pla_id
					GROUP BY
						tab_assinaturas_planos_proxy.pla_proxy_id
				) AS credito_em_uso,
				COUNT(*) AS planos
			FROM
				tab_assinaturas
				INNER JOIN tab_clientes ON (tab_assinaturas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_clientes_origens ON (tab_clientes.cli_cpf_cnpj = tab_clientes_origens.clo_cnpj)
				INNER JOIN tab_planos tab_planos1 ON (tab_assinaturas.pla_id = tab_planos1.pla_id)
			WHERE
				tab_assinaturas.pla_id IN (SELECT pla_proxy_id FROM tab_assinaturas_planos_proxy) AND
				tab_assinaturas.ast_id IN (1, 2, 5) AND
				tab_clientes_origens.cos_id = 2 AND
				tab_clientes_origens.clo_id = $clo_id
			GROUP BY
				tab_assinaturas.pla_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["credito_em_uso"] = $dados["credito_em_uso"] !== null ? (float) $dados["credito_em_uso"] : 0;
			$dados["credito_livre"] = (float) $dados["credito_total"] - $dados["credito_em_uso"];


			$dados["credito_livre"] = $dados["credito_livre"] / 100;
			$dados["credito_total"] = $dados["credito_total"] / 100;
			$dados["valor_credito"] = $dados["valor_credito"] / 100;
			$dados["credito_em_uso"] = $dados["credito_em_uso"] / 100;
			
			$planos[] = $dados;
		}

		return $planos;
	}

	/**
	 * Busca o detalhamento de uso de um plano proxy
	 * @param int $clo_id ID do cliente parceiro
	 * @param int $pla_proxy_id ID do plano proxy
	 * @return array Array de detalhamentos
	 */
	function buscaDetalhamentoPlanoProxy($clo_id, $pla_proxy_id) {

		$clo_id = preg_replace("/[^0-9]/", "", $clo_id);
		$pla_proxy_id = preg_replace("/[^0-9]/", "", $pla_proxy_id);
		$itens = array();

		$query = $this->bancodedados->query("
			SELECT
				tab_planos.pla_nome AS plano_nome,
				tab_planos_limitacao.valor AS valor_individual,
				SUM(tab_planos_limitacao.valor) AS valor_total_uso,
				COUNT(*) AS qtd_planos
			FROM
				tab_assinaturas_planos_proxy
				INNER JOIN tab_planos tab_planos ON (tab_planos.pla_id = tab_assinaturas_planos_proxy.pla_id)
				INNER JOIN tab_planos tab_planos2 ON (tab_planos2.pla_id = tab_assinaturas_planos_proxy.pla_proxy_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_assinaturas_planos_proxy.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				INNER JOIN tab_planos_limitacao ON (
					tab_planos_limitacao.pla_id = tab_assinaturas_planos_proxy.pla_proxy_id AND
					tab_planos_limitacao.plano = tab_assinaturas_planos_proxy.pla_id
				)
			WHERE
				tab_clientes.clo_id = $clo_id AND
				tab_assinaturas_planos_proxy.pla_proxy_id = $pla_proxy_id AND
				tab_assinaturas.ast_id IN (1, 2, 5)
			GROUP BY
				tab_assinaturas_planos_proxy.pla_id,
				tab_assinaturas_planos_proxy.pla_proxy_id");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["valor_individual"] = $dados["valor_individual"] / 100;
			$dados["valor_total_uso"] = $dados["valor_total_uso"] / 100;
			$itens[] = $dados;
		}

		return $itens;
	}

	/**
	 * Gera o detalhamento de uso de um plano proxy
	 * @param int $clo_id ID do cliente parceiro
	 * @param int $pla_proxy_id ID do plano proxy
	 * @return string HTML do detalhamento
	 */
	function geraDetalhamentoPlanoProxy($clo_id, $pla_proxy_id) {

		$itens = $this->buscaDetalhamentoPlanoProxy($clo_id, $pla_proxy_id);

		if (!$itens) {
			return "<p class=\"txtAzulEscuro11\">&nbsp;Nenhum detalhamento encontrado.</p>";
		}

		foreach ($itens as &$item) {
			$item["valor_individual"] = $this->formatValor($item["valor_individual"]);
			$item["valor_total_uso"] = $this->formatValor($item["valor_total_uso"]);
			$item["qtd_planos"] = number_format($item["qtd_planos"], 0, ",", ".");
		}
		unset($item);

		$cabecalho = array(
			"plano_nome" => "Plano",
			"valor_individual" => "Valor Individual",
			"valor_total_uso" => "Valor Total em Uso",
			"qtd_planos" => "Quantidade em Uso"
		);

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(0, 120, 120, 100), null, array("left", "right", "right", "center"));

		return $listagem->conteudo;
	}

	/**
	 * Gera a listagem de planos proxy
	 * @param int $clo_id ID do cliente parceiro
	 * @return string HTML da listagem
	 */
	function geraPlanosProxyListagem($clo_id) {

		$planos = $this->buscaPlanosProxy($clo_id);

		if (!$planos) {
			return "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"5\"><tr><td bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\" align=\"center\">Nenhum plano proxy encontrado.</td></tr></table>";
		}

		$conteudo = "";

		foreach ($planos as $plano) {
			$detalhe_id = "detalhe_plano_{$plano["id"]}";
			$conteudo .= "
				<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"5\" style=\"margin-bottom:10px;\">
					<tr bgcolor=\"#505050\" class=\"txtBranco11\">
						<td colspan=\"2\"><strong>&nbsp;{$plano["plano_proxy"]}</strong></td>
					</tr>
					<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
						<td width=\"220\"><b>&nbsp;Quantidade Contratada:</b></td>
						<td>" . number_format($plano["planos"], 0, ",", ".") . "</td>
					</tr>
					<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
						<td><b>&nbsp;Valor Cr&eacute;dito Unit&aacute;rio:</b></td>
						<td>{$this->formatValor($plano["valor_credito"])}</td>
					</tr>
					<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
						<td><b>&nbsp;Valor Total de Cr&eacute;ditos:</b></td>
						<td>{$this->formatValor($plano["credito_total"])}</td>
					</tr>
					<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
						<td><b>&nbsp;Total em Uso de Cr&eacute;dito:</b></td>
						<td>{$this->formatValor($plano["credito_em_uso"])}</td>
					</tr>
					<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
						<td><b>&nbsp;Valor Livre de Cr&eacute;dito:</b></td>
						<td>{$this->formatValor($plano["credito_livre"])}</td>
					</tr>
					<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
						<td colspan=\"2\">
							&nbsp;<a href=\"javascript:void(0);\" class=\"txtAzulEscuro11\" onclick=\"toggleDetalhe('{$detalhe_id}');\"><strong>&raquo; Detalhamento de uso</strong></a>
						</td>
					</tr>
					<tr id=\"{$detalhe_id}\" style=\"display:none;\">
						<td colspan=\"2\" bgcolor=\"#F5F5F5\">
							{$this->geraDetalhamentoPlanoProxy($clo_id, $plano["id"])}
						</td>
					</tr>
				</table>";
		}

		$conteudo .= "
			<script type=\"text/javascript\">
				function toggleDetalhe(id) {
					var el = document.getElementById(id);
					if (!el) return;
					el.style.display = el.style.display === 'none' ? '' : 'none';
				}
			</script>";

		return $conteudo;
	}

	/**
	 * Busca os clientes que estão utilizando crédito
	 * @param int $clo_id ID do cliente parceiro
	 * @return array Array de clientes
	 */
	function buscaClientesCredito($clo_id, $formatar = true) {

		$clo_id = preg_replace("/[^0-9]/", "", $clo_id);
		$itens = array();
		$contador = 0;

		$query = $this->bancodedados->query("
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				tab_planos2.pla_valor_max AS valor_max_proxy,
				tab_planos2.pla_nome AS plano_proxy,
				tab_planos.pla_nome AS plano,
				tab_planos_limitacao.valor AS valor_usado,
				tab_assinaturas.ass_data_ativacao AS data_ativacao
			FROM
				tab_assinaturas_planos_proxy
				INNER JOIN tab_planos tab_planos ON (tab_planos.pla_id = tab_assinaturas_planos_proxy.pla_id)
				INNER JOIN tab_planos tab_planos2 ON (tab_planos2.pla_id = tab_assinaturas_planos_proxy.pla_proxy_id)
				INNER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_assinaturas_planos_proxy.ass_id)
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
				INNER JOIN tab_planos_limitacao ON (
					tab_planos_limitacao.pla_id = tab_assinaturas_planos_proxy.pla_proxy_id AND
					tab_planos_limitacao.plano = tab_assinaturas_planos_proxy.pla_id
				)
			WHERE
				tab_clientes.clo_id = $clo_id AND
				tab_assinaturas.ast_id IN (1, 2, 5)
			ORDER BY
				tab_clientes.cli_nome,
				tab_planos2.pla_nome,
				tab_planos.pla_nome");

		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$contador++;

			if ($formatar) {
				$dados["contador"] = number_format($contador, 0, ",", ".");
				$dados["valor_usado"] = $this->formatValor($dados["valor_usado"] / 100);
				$dados["data_ativacao"] = $dados["data_ativacao"] ? date("d/m/Y", strtotime($dados["data_ativacao"])) : "<b style=\"color:red;\">Não ativado</b>";

				if ($this->permissoes["clientes"]["ver_dc"]) {
					$dados["link"] = "?m=clientes&amp;a=ver_dc&amp;i=" . $dados["id"];
				}
			} else {
				$dados["contador"] = $contador;
				$dados["valor_usado"] = $dados["valor_usado"] / 100;
				$dados["data_ativacao"] = $dados["data_ativacao"] ? date("d/m/Y", strtotime($dados["data_ativacao"])) : "Nao ativado";
			}

			$itens[] = $dados;
		}

		return $itens;
	}

	/**
	 * Gera a listagem de clientes que estão utilizando crédito
	 * @param int $clo_id ID do cliente parceiro
	 * @return string HTML da listagem
	 */
	function geraClientesListagem($clo_id) {

		$itens = $this->buscaClientesCredito($clo_id);

		if (!$itens) {
			return "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"5\"><tr><td bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\" align=\"center\">Nenhum cliente utilizando cr&eacute;dito encontrado.</td></tr></table>";
		}

		$cabecalho = array(
			"contador" => "#",
			"id" => "ID Cliente",
			"nome" => "Nome do Cliente",
			"plano" => "Plano",
			"plano_proxy" => "Plano Proxy",
			"data_ativacao" => "Data Ativação",
			"valor_usado" => "Valor Usado"
		);

		$listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens, 0, false, array(30, 70, 0, 0, 0, 0, 100), null, array("right", "center", "left", "left", "left", "center", "right"));

		return $listagem->conteudo;
	}

	/**
	 * Gera o cabeçalho do parceiro
	 * @param int $clo_id ID do cliente parceiro
	 * @param string $acao Ação
	 * @return string HTML do cabeçalho
	 */
	function geraCabecalhoParceiro($clo_id, $acao) {

		$cabecalho = "
			<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"5\">
				{$this->geraSelectParceiro($clo_id, $acao)}
				<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
					<td width=\"150\"><b>&nbsp;Parceiro:</b></td>
					<td>{$this->dados_item["nome"]}</td>
				</tr>
				<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">
					<td><b>&nbsp;CNPJ:</b></td>
					<td>{$this->dados_item["cnpj"]}</td>
				</tr>
			</table>";

		return $cabecalho;
	}

	/**
	 * Prepara a tela para a ação
	 * @return int ID do cliente parceiro
	 */	
	function preparaTela() {

		$clo_id = $this->resolveCloId();
		$this->buscaDadosParceiro($clo_id);

		if (!$this->dados_item) {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver");
			exit;
		}

		return $clo_id;
	}

	###########
	### Ver ###
	###########

	/**
	 * Exibe a tela de saldo de crédito
	 */
	function ver() {

		$clo_id = $this->preparaTela("ver");

		$this->conteudo .= $this->carregaArquivo("cliente_credito_saldo.ver.inc.php");
		$this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver" => "Saldo de Cr&eacute;dito", "ver_clientes" => "Clientes")), $this->conteudo);
		$this->conteudo = str_replace("[cabecalho_parceiro]", $this->geraCabecalhoParceiro($clo_id, "ver"), $this->conteudo);
		$this->conteudo = str_replace("[planos_proxy]", $this->geraPlanosProxyListagem($clo_id), $this->conteudo);
	}

	####################
	### Ver Clientes ###
	####################

	/**
	 * Exibe a tela de clientes que estão utilizando crédito
	 */
	function ver_clientes() {

		$clo_id = $this->preparaTela("ver_clientes");

		$this->conteudo .= $this->carregaArquivo("cliente_credito_saldo.ver_clientes.inc.php");
		$this->conteudo = str_replace("[acoes]", $this->geraAcoes(array("ver" => "Saldo de Cr&eacute;dito", "ver_clientes" => "Clientes")), $this->conteudo);
		$this->conteudo = str_replace("[cabecalho_parceiro]", $this->geraCabecalhoParceiro($clo_id, "ver_clientes"), $this->conteudo);
		$this->conteudo = str_replace("[clientes_listagem]", $this->geraClientesListagem($clo_id), $this->conteudo);

		$botoes = array();
		if ($this->temPermissao("exportar")) {
			$botoes[] = '<input type="button" name="exportar" class="buttonAzulEscuroBranco11" value="Exportar" onclick="window.location=\'?m=' . $this->dados_modulo["nome"] . '&a=exportar&i=' . $clo_id . '\'">';
		}
		$this->conteudo = str_replace("[botoes]", @implode(" ", $botoes), $this->conteudo);
	}

	###############
	### Exportar ###
	###############

	function exportar() {

		if (!$this->temPermissao("exportar")) {
			header("Location: ./?m={$this->dados_modulo["nome"]}&a=ver_clientes");
			exit;
		}

		$clo_id = $this->resolveCloId();
		$this->buscaDadosParceiro($clo_id);
		$itens = $this->buscaClientesCredito($clo_id, false);

		require_once "_global/phpexcel/PHPExcel.php";
		require_once "_global/phpexcel/PHPExcel/IOFactory.php";

		PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array("memoryCacheSize" => "8MB"));

		// Criar objeto Excel
		$objPHPExcel = new PHPExcel();

		// Definir propriedades
		$objPHPExcel->getProperties()
			->setCreator("Ole Telecom")
			->setLastModifiedBy("Ole Telecom")
			->setTitle("Relatorio de Clientes")
			->setSubject("Relatorio de Clientes")
			->setDescription("Relatorio de Clientes gerado pelo Ole Telecom")
			->setKeywords("excel php ole telecom")
			->setCategory("Relatorios Ole Telecom");

		// Selecionar planilha
		$objPHPExcel->setActiveSheetIndex(0);

		$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.4)->setLeft(0.2)->setRight(0.2)->setBottom(0.2)->setHeader(0.2);
		$objPHPExcel->getActiveSheet()->getHeaderFooter()->setAlignWithMargins(true)->setOddHeader(utf8_encode("&LRELATORIO CLIENTES&C" . date('d-m-Y') . "&R&P / &N"));

		$objPageSetup = new PHPExcel_Worksheet_PageSetup();
		$objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$objPageSetup->setRowsToRepeatAtTop(array(1));
		$objPageSetup->setFitToHeight(0);
		$objPageSetup->setFitToWidth(1);
		$objPHPExcel->getActiveSheet()->setPageSetup($objPageSetup);

		// Cabecalho
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '#');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'ID Cliente');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Nome do Cliente');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Plano');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Plano Proxy');
		$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Data Ativacao');
		$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Valor Usado');

		// Dados
		$linha = 2;
		foreach ($itens as $item) {
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$linha, $item["contador"]);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$linha, $item["id"]);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$linha, $item["nome"]);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$linha, $item["plano"]);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$linha, $item["plano_proxy"]);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$linha, $item["data_ativacao"]);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$linha, $item["valor_usado"]);
			$linha++;
		}

		// Formatação do cabeçalho
		$objPHPExcel->getActiveSheet()
			->getStyle('A1:G1')
			->getFont()
			->setBold(true);

		// Formatar moeda
		$objPHPExcel->getActiveSheet()
			->getStyle('G2:G'.$linha)
			->getNumberFormat()
			->setFormatCode('#,##0.00');

		// Auto ajuste das colunas
		foreach (range('A', 'G') as $coluna) {
			$objPHPExcel->getActiveSheet()
				->getColumnDimension($coluna)
				->setAutoSize(true);
		}

		// Nome da aba
		$objPHPExcel->getActiveSheet()->setTitle('Clientes');

		// Posicionar na primeira aba
		$objPHPExcel->setActiveSheetIndex(0);

		// Download XLSX
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="clientes_'.date('d-m-Y').'.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');

		exit;

	}

	
}
?>