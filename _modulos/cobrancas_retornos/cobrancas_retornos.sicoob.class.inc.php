<?php

class cobrancas_retornos_sicoob {
	
	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	/**
	 * @var cobrancas_retornos
	 */
	private $cobrancas_retornos;
	/**
	 * @var cobrancas
	 */
	private $cobrancas;
	
	private $formato;
	
	private $conteudo;
	
	private $linha = 0;
	
	private $lote = 0;
	
	private $linha_lote = 0;
	
	private $erro = null;
	
	private $nsa = 0;
	
	private $data_geracao = null;
	/**
	 * @var cobranca
	 */
	private $cobranca = null;
	/**
	 * @var cobranca[]
	 */
	private $lista_cobrancas = array();
	
	public function __construct($bancodedados, $cobrancas_retornos, $formato_id, $conteudo) {
		
		$this->bancodedados = $bancodedados;
		$this->cobrancas_retornos = $cobrancas_retornos;
		$this->cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
		$this->formato = $this->buscaDadosFormato($formato_id);
		$this->conteudo = preg_split("/\r?\n/", $conteudo);
		
	}
	
	public function importar($ignorar_sequencial = false) {
		
		if (!$this->inicioArquivo($ignorar_sequencial)) {
			return false;
		}
		
		while ($this->inicioLote()) {
			while (!$this->fimLote()) {
				if (!$this->processaRegistroT()) {
					return false;
				}
				if (!$this->processaRegistroU()) {
					return false;
				}
			}
		}
		
		if (!$this->fimArquivo()) {
			return false;
		}
		
		$retorno_id = $this->geraRetorno();
		
		for ($i = 0; $i < count($this->lista_cobrancas); $i++) {
			$this->lista_cobrancas[$i]->processar($retorno_id);
		}
		
		return $retorno_id;
		
	}
	
	public function getErro() {
		
		return $this->erro;
		
	}
	
	private function geraRetorno() {
		
		$this->bancodedados->query("
			INSERT INTO
				tab_cobrancas_retornos
			SET
				cof_id = {$this->formato["id"]},
				coe_data_geracao = '$this->data_geracao',
				coe_data_publicacao = CURDATE(),
				coe_conteudo = '".addslashes(implode("\r\n", $this->conteudo))."',
				coe_sequencial = {$this->nsa}");
		
		return $this->bancodedados->ultimoIdInserido();
		
	}
	
	private function buscaDadosFormato($formato_id) {
		
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
	
	private function buscaProximoNSA() {
		
		$query = $this->bancodedados->query("
			SELECT
				IFNULL(MAX(coe_sequencial), 0) + 1
			FROM
				tab_cobrancas_retornos
			WHERE
				cof_id = {$this->formato["id"]}");
		
		return $this->bancodedados->resultado($query);
		
	}
	
	private function inicioArquivo($ignorar_sequencial) {
		
		$registro = array();
		
		if (preg_match("/^(?P<banco>[0-9]{3})(0000)(0)( {9})(?P<pessoa>[12]{1})(?P<inscricao>[0-9]{14})(.{20})(?P<agencia>[0-9]{5})(?P<agencia_dv>[0-9])(?P<conta>[0-9]{12})(?P<conta_dv>[0-9])(.)(?P<nome_empresa>.{30})(?P<nome_banco>.{30})( {10})(2)(?P<data_geracao>[0-9]{8})(?P<hora_geracao>[0-9]{6})(?P<nsa>[0-9]{6})(?P<versao>[0-9]{3})(00000)( {20})( {20})( {29})$/", $this->conteudo[$this->linha], $registro)) {
			
			$proximo_nsa = $this->buscaProximoNSA();
			
			if ($registro["banco"] != $this->formato["parametros"]["codigo_banco"]["valor"]) {
				$this->erro = "Banco inválido (Esperado: {$this->formato["parametros"]["codigo_banco"]["valor"]}, encontrado: {$registro["banco"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["inscricao"]) != intval($this->formato["parametros"]["cnpj"]["valor"])) {
				$this->erro = "CNPJ inválido (Esperado: ".intval($this->formato["parametros"]["cnpj"]["valor"]).", encontrado: ".intval($registro["inscricao"])."): {$this->linha}";
				return false;
			} elseif (intval($registro["agencia"]) != intval($this->formato["parametros"]["agencia"]["valor"])) {
				$this->erro = "Agência inválida (Esperado: ".intval($this->formato["parametros"]["agencia"]["valor"]).", encontrado: ".intval($registro["agencia"])."): {$this->linha}";
				return false;
			} elseif (intval($registro["conta"]) != intval($this->formato["parametros"]["conta_corrente"]["valor"])) {
				$this->erro = "Agência inválida (Esperado: ".intval($this->formato["parametros"]["conta_corrente"]["valor"]).", encontrado: ".intval($registro["conta"])."): {$this->linha}";
				return false;
			} elseif ($registro["versao"] != $this->formato["parametros"]["cnab_arquivo"]) {
				$this->erro = "Versão do arquivo inválida (Esperado: {$this->formato["parametros"]["cnab_arquivo"]}, encontrado: {$registro["versao"]}): {$this->linha}";
				return false;
			} elseif ($registro["nsa"] < $proximo_nsa || ($registro["nsa"] > $proximo_nsa && !$ignorar_sequencial)) {
				$this->erro = "Sequencial inválido (Esperado: {$proximo_nsa}, encontrado: ".intval($registro["nsa"])."): {$this->linha}";
				return false;
			}
			
			$this->nsa = (int) $registro["nsa"];
			$this->data_geracao = substr($registro["data_geracao"], 4, 4)."-".substr($registro["data_geracao"], 2, 2)."-".substr($registro["data_geracao"], 0, 2);
			
			$this->linha++;
			
			return true;
			
		}
		
		$this->erro = "Arquivo inválido ou não corresponde ao formato selecionado";
		
		return false;
		
	}
	
	private function fimArquivo() {
		
		$registro = array();
		
		if (preg_match("/^(?P<banco>[0-9]{3})(9999)(9)( {9})(?P<quantidade_lotes>[0-9]{6})(?P<quantidade_registros>[0-9]{6})(000000)( {205})$/", $this->conteudo[$this->linha], $registro)) {
			
			if ($registro["banco"] != $this->formato["parametros"]["codigo_banco"]["valor"]) {
				$this->erro = "Banco inválido (Esperado: {$this->formato["parametros"]["codigo_banco"]["valor"]}, encontrado: {$registro["banco"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["quantidade_lotes"]) != $this->lote) {
				$this->erro = "Quantidade inválida de lotes (Esperado: ".intval($registro["quantidade_lotes"]).", encontrado: {$this->lote}): {$this->linha}";
				return false;
			}
			
			return true;
			
		}
		
		$this->erro = "Fim do arquivo não encontrado";
		
		return false;
		
	}
	
	private function inicioLote() {
		
		$registro = array();
		
		if (preg_match("/^(?P<banco>[0-9]{3})(?P<lote>[0-9]{4})(1)(T)(01)(  )(?P<versao>[0-9]{3})( )(?P<pessoa>[12]{1})(?P<inscricao>[0-9]{15})(.{20})(?P<agencia>[0-9]{5})(?P<agencia_dv>[0-9])(?P<conta>[0-9]{12})(?P<conta_dv>[0-9])(.)(?P<nome_empresa>.{30})( {40})( {40})([0-9]{8})(?P<data_geracao>[0-9]{8})(?P<data_credito>[0-9]{8})( {33})$/", $this->conteudo[$this->linha], $registro)) {
			
			if ($registro["banco"]!= $this->formato["parametros"]["codigo_banco"]["valor"]) {
				$this->erro = "Banco inválido (Esperado: {$this->formato["parametros"]["codigo_banco"]["valor"]}, encontrado: {$registro["banco"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["lote"]) != $this->lote + 1) {
				$this->erro = "Lote inválido (Esperado: ".($this->lote + 1).", encontrado: ".intval($registro["lote"])."): {$this->linha}";
				return false;
			} elseif ($registro["versao"] != $this->formato["parametros"]["cnab_lote"]) {
				$this->erro = "Versão do lote inválida (Esperado: {$this->formato["parametros"]["cnab_arquivo"]}, encontrado: {$registro["versao"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["inscricao"]) != intval($this->formato["parametros"]["cnpj"]["valor"])) {
				$this->erro = "CNPJ inválido (Esperado: ".intval($this->formato["parametros"]["cnpj"]["valor"]).", encontrado: ".intval($registro["inscricao"])."): {$this->linha}";
				return false;
			} elseif (intval($registro["agencia"]) != intval($this->formato["parametros"]["agencia"]["valor"])) {
				$this->erro = "Agência inválida (Esperado: ".intval($this->formato["parametros"]["agencia"]["valor"]).", encontrado: ".intval($registro["agencia"])."): {$this->linha}";
				return false;
			} elseif (intval($registro["conta"]) != intval($this->formato["parametros"]["conta_corrente"]["valor"])) {
				$this->erro = "Agência inválida (Esperado: ".intval($this->formato["parametros"]["conta_corrente"]["valor"]).", encontrado: ".intval($registro["conta"])."): {$this->linha}";
				return false;
			}
			
			$this->lote++;
			$this->linha_lote = 0;
			$this->linha++;
			
			return true;
			
		}
		
		return false;
		
	}
	
	private function fimLote() {
		
		$registro = array();
		
		if (preg_match("/^(?P<banco>[0-9]{3})(?P<lote>[0-9]{4})(5)( {9})(?P<quantidade_registros>[0-9]{6})([0-9]{6})([0-9]{17})([0-9]{6})([0-9]{17})([0-9]{6})([0-9]{17})([0-9]{6})([0-9]{17})( {8})( {117})$/", $this->conteudo[$this->linha], $registro)) {
			
			if ($registro["banco"] != $this->formato["parametros"]["codigo_banco"]["valor"]) {
				$this->erro = "Banco inválido (Esperado: {$this->formato["parametros"]["codigo_banco"]["valor"]}, encontrado: {$registro["banco"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["lote"]) != $this->lote) {
				$this->erro = "Lote inválido (Esperado: {$this->lote}, encontrado: ".intval($registro["lote"])."): {$this->linha}";
				return false;
			}
			
			$this->linha++;
			
			return true;
			
		}
		
		return false;
		
	}
	
	private function processaRegistroT() {
		
		$registro = array();
		
		if (preg_match("/^(?P<banco>[0-9]{3})(?P<lote>[0-9]{4})(3)(?P<linha>[0-9]{5})(T)( )(?P<movimento>[0-9]{2})(?P<agencia>[0-9]{5})(?P<agencia_dv>[0-9])(?P<conta>[0-9]{12})(?P<conta_dv>[0-9])(.)(?P<nosso_numero>[0-9]{9})(?P<nosso_numero_dv>[0-9])(?P<parcela>[0-9]{2})(?P<modalidade>[0-9]{2})(?P<formulario>[0-9])( {5})(?P<carteira>[0-9])(?P<documento>.{15})(?P<vencimento>[0-9]{8})(?P<valor_nominal>[0-9]{15})(?P<banco_cobranca_recebimento>[0-9]{3})(?P<agencia_cobranca_recebimento>[0-9]{5})(?P<agencia_cobranca_recebimento_dv>[0-9])(?P<cliente_id>.{10})(?P<cobranca_id>.{15})(?P<moeda>[0-9]{2})(?P<cliente_tipo>[0-9]{1})(?P<cliente_cpf_cnpj>[0-9]{15})(?P<cliente_nome>.{40})(?P<contrato>[0-9]{10})(?P<tarifas>[.0-9]{15})(?P<motivo_ocorrencia>.{10})(.{17})$/", $this->conteudo[$this->linha], $registro)) {
			
			if ($registro["banco"] != $this->formato["parametros"]["codigo_banco"]["valor"]) {
				$this->erro = "Banco inválido (Esperado: {$this->formato["parametros"]["codigo_banco"]["valor"]}, encontrado: {$registro["banco"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["lote"]) != $this->lote) {
				$this->erro = "Lote inválido (Esperado: {$this->lote}, encontrado: ".intval($registro["lote"])."): {$this->linha}";
				return false;
			} elseif (intval($registro["linha"]) != $this->linha_lote + 1) {
				$this->erro = "Linha do registro inválida (Esperado: ".($this->linha_lote + 1).", encontrado: ".intval($registro["linha"])."): {$this->linha}";
				return false;
			}
			
			$this->cobranca = new cobranca($this->bancodedados, $this->cobrancas, $this->cobrancas_retornos, $this->formato["id"], $registro["cliente_id"], $registro["cobranca_id"], $registro["nosso_numero"], $registro["documento"], $registro["vencimento"], $registro["valor_nominal"], $registro["movimento"], $registro["motivo_ocorrencia"]);
			
			$this->linha++;
			$this->linha_lote++;
			
			return true;
			
		}
		
		$this->erro = "Registro T esperado: {$this->linha}";
		
		return false;
		
	}
	
	private function processaRegistroU() {
		
		$registro = array();
		
		if (preg_match("/^(?P<banco>[0-9]{3})(?P<lote>[0-9]{4})(3)(?P<linha>[0-9]{5})(U)( )(?P<movimento>[0-9]{2})(?P<acrescimos>[0-9]{15})(?P<desconto>[0-9]{15})(?P<abatimento>[0-9]{15})(?P<iof>[0-9]{15})(?P<valor_pago>[0-9]{15})(?P<valor_creditado>[0-9]{15})(?P<despesas>[0-9]{15})(?P<creditos>[0-9]{15})(?P<data_ocorrencia>[0-9]{8})(?P<data_credito>[0-9]{8})( {4})(0{8})(0{15})(.{30})(756)(0{20})( {7})$/", $this->conteudo[$this->linha], $registro)) {
			
			if ($registro["banco"] != $this->formato["parametros"]["codigo_banco"]["valor"]) {
				$this->erro = "Banco inválido (Esperado: {$this->formato["parametros"]["codigo_banco"]["valor"]}, encontrado: {$registro["banco"]}): {$this->linha}";
				return false;
			} elseif (intval($registro["lote"]) != $this->lote) {
				$this->erro = "Lote inválido (Esperado: {$this->lote}, encontrado: ".intval($registro["lote"])."): {$this->linha}";
				return false;
			} elseif (intval($registro["linha"]) != $this->linha_lote + 1) {
				$this->erro = "Linha do registro inválida (Esperado: ".($this->linha_lote + 1).", encontrado: ".intval($registro["linha"])."): {$this->linha}";
				return false;
			}
			
			$this->cobranca->setAcrescimos($registro["acrescimos"]);
			$this->cobranca->setDesconto($registro["desconto"]);
			$this->cobranca->setAbatimento($registro["abatimento"]);
			$this->cobranca->setIOF($registro["iof"]);
			$this->cobranca->setValorPago($registro["valor_pago"]);
			$this->cobranca->setValorCreditado($registro["valor_creditado"]);
			$this->cobranca->setDespesas($registro["despesas"]);
			$this->cobranca->setCreditos($registro["creditos"]);
			$this->cobranca->setDataOcorrencia($registro["data_ocorrencia"]);
			$this->cobranca->setDataCredito($registro["data_credito"]);
			
			$this->lista_cobrancas[] = $this->cobranca;
			
			$this->linha++;
			$this->linha_lote++;
			
			return true;
			
		}
		
		$this->erro = "Registro U esperado: {$this->linha}";
		
		return false;
		
	}
	
}

class cobranca {
	/**
	 * @var bancodedados
	 */
	private $bancodedados;
	/**
	 * @var cobrancas
	 */
	private $cobrancas;
	/**
	 * @var cobrancas_retornos
	 */
	private $cobrancas_retornos;
	
	private $formato_id;
	
	private $cliente_id;
	private $cobranca_id;
	private $nosso_numero;
	private $documento;
	private $vencimento;
	private $valor_nominal;
	
	private $encontrada = false;
	
	private $movimento;
	private $motivo_ocorrencia;
	private $acrescimos;
	private $desconto;
	private $abatimento;
	private $iof;
	private $valor_pago;
	private $valor_creditado;
	private $despesas;
	private $creditos;
	private $data_ocorrencia;
	private $data_credito;
	
	public function __construct($bancodedados, $cobrancas, $cobrancas_retornos, $formato_id, $cliente_id, $cobranca_id, $nosso_numero, $documento, $vencimento, $valor_nominal, $movimento, $motivo_ocorrencia) {
		
		$this->bancodedados = $bancodedados;
		$this->cobrancas_retornos = $cobrancas_retornos;
		$this->cobrancas = $cobrancas;
		$this->formato_id = $formato_id;
		$this->cliente_id = (int) $cliente_id;
		$this->cobranca_id = (int) $cobranca_id;
		$this->nosso_numero = (int) $nosso_numero;
		$this->documento = trim($documento);
		$this->vencimento = substr($vencimento, 4, 4)."-".substr($vencimento, 2, 2)."-".substr($vencimento, 0, 2);
		$this->valor_nominal = (int) $valor_nominal;
		$this->movimento = $movimento;
		$this->motivo_ocorrencia = substr($motivo_ocorrencia, -2);
		
	}
	
	public function setAcrescimos($acrescimos) {
		
		$this->acrescimos = (int) $acrescimos;
		
	}
	
	public function setDesconto($desconto) {
		
		$this->desconto = (int) $desconto;
		
	}
	
	public function setAbatimento($abatimento) {
		
		$this->abatimento = (int) $abatimento;
		
	}
	
	public function setIOF($iof) {
		
		$this->iof = (int) $iof;
		
	}
	
	public function setValorPago($valor_pago) {
		
		$this->valor_pago = (int) $valor_pago;
		
	}
	
	public function setValorCreditado($valor_creditado) {
		
		$this->valor_creditado = (int) $valor_creditado;
		
	}
	
	public function setDespesas($despesas) {
		
		$this->despesas = (int) $despesas;
		
	}
	
	public function setCreditos($creditos) {
		
		$this->creditos = (int) $creditos;
		
	}
	
	public function setDataOcorrencia($data_ocorrencia) {
		
		$this->data_ocorrencia = substr($data_ocorrencia, 4, 4)."-".substr($data_ocorrencia, 2, 2)."-".substr($data_ocorrencia, 0, 2);
		
	}
	
	public function setDataCredito($data_credito) {
		
		$this->data_credito = substr($data_credito, 4, 4)."-".substr($data_credito, 2, 2)."-".substr($data_credito, 0, 2);
		
	}
	
	public function processar($retorno_id) {
		
		$this->buscaCobranca();
		
		if ($this->encontrada) {
			if ($this->movimento == "02") {
				if ($this->geraRegistroCobrancaRetorno($retorno_id, "REGISTRO")) {
					$this->registraConfirmacao();
				}
			} elseif ($this->movimento == "03") {
				if ($this->geraRegistroCobrancaRetorno($retorno_id, "REJEICAO")) {
					$this->registraRejeicao();
				}
			} elseif ($this->movimento == "06") {
				if ($this->geraRegistroCobrancaRetorno($retorno_id, "LIQUIDACAO")) {
					$this->registraLiquidacao($retorno_id);
				}
			} elseif ($this->movimento == "09") {
				if ($this->geraRegistroCobrancaRetorno($retorno_id, "BAIXA")) {
					$this->registraBaixa($retorno_id);
				}
			}
		} elseif ($this->movimento == "06") {
			$this->registraNaoEncontrada($retorno_id);
		}
		
	}
	
	private function geraRegistroCobrancaRetorno($retorno_id, $tipo) {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_cobrancas_retornos_registros
			WHERE
				-- coe_id != {$retorno_id} AND
				coa_id = {$this->cobranca_id} AND
				cog_tipo = '{$tipo}'");
		
		if ($this->bancodedados->resultado($query) == 0) {
			
			$this->bancodedados->query("
				INSERT INTO
					tab_cobrancas_retornos_registros
				SET
					coe_id = {$retorno_id},
					coa_id = {$this->cobranca_id},
					cog_tipo = '{$tipo}'");
			
			return true;
			
		}
		
		return false;
		
	}
	
	private function buscaCobranca() {
		
		$query = $this->bancodedados->query("
			SELECT
				COUNT(*)
			FROM
				tab_cobrancas
			WHERE
				coa_id = {$this->cobranca_id}");
		
		$this->encontrada = $this->bancodedados->resultado($query) == 1;
		
	}
	
	private function registraConfirmacao() {
		
		$this->bancodedados->query("
			UPDATE
				tab_cobrancas_remessas_itens
			SET
				cri_status = 'REGISTRADA'
			WHERE
				coa_id = {$this->cobranca_id} AND
				cri_tipo = 'REGISTRO'");
		
		$this->cobrancas_retornos->registraComentarioCobranca($this->cobranca_id, "<b>Ação:</b> Boleto registrado no banco");
		
		$this->cobrancas->buscaDadosItem($this->cobranca_id);
		
		if ($this->cobrancas->dados_item["geracao_manual"]) {
			$this->cobrancas->enviaBoletoAutomatico();
		}
		
	}
	
	private function registraRejeicao() {
		
		$this->bancodedados->query("
			UPDATE
				tab_cobrancas_remessas_itens
			SET
				cri_status = 'REJEITADA'
			WHERE
				coa_id = {$this->cobranca_id} AND
				cri_status = 'PENDENTE' AND
				cri_tipo = 'REGISTRO'");
		
		$this->cobrancas_retornos->registraComentarioCobranca($this->cobranca_id, "<b>Ação:</b> Boleto rejeitado");
		
	}
	
	private function registraLiquidacao($retorno_id) {
		
		$registro = array();
		
		$registro["id"] = $this->cobranca_id;
		$registro["data_processamento"] = $this->data_ocorrencia;
		$registro["data_credito"] = $this->data_ocorrencia;
		$registro["valor_total"] = $this->valor_pago;
		$registro["valor_iof"] = $this->iof;
		
		$this->cobrancas_retornos->darBaixa($registro, $retorno_id);
		
	}
	
	private function registraBaixa($retorno_id) {
		
		$this->bancodedados->query("
			UPDATE
				tab_cobrancas_remessas_itens
			SET
				cri_status = 'BAIXADA'
			WHERE
				coa_id = {$this->cobranca_id} AND
				cri_tipo = 'BAIXA'");
		
		$this->cobrancas_retornos->registraComentarioCobranca($this->cobranca_id, "<b>Ação:</b> Boleto cancelado no banco");
		
	}
	
	private function registraNaoEncontrada($retorno_id) {
		
		$this->cobrancas_retornos->registraNaoEncontrada($retorno_id, $this->nosso_numero, $this->vencimento, $this->valor_pago, $this->valor_pago);
		
	}
	
	private $codigos_rejeicao = array(
		"01" => "Código do Banco Inválido",
		"02" => "Código do Registro Detalhe Inválido",
		"03" => "Código do Segmento Inválido",
		"04" => "Código de Movimento Não Permitido para Carteira",
		"05" => "Código de Movimento Inválido",
		"06" => "Tipo/Número de Inscrição do Cedente Inválidos",
		"07" => "Agência/Conta/DV Inválido",
		"08" => "Nosso Número Inválido",
		"09" => "Nosso Número Duplicado",
		"10" => "Carteira Inválida",
		"11" => "Forma de Cadastramento do Título Inválido",
		"12" => "Tipo de Documento Inválido",
		"13" => "Identificação da Emissão do Bloqueto Inválida",
		"14" => "Identificação da Distribuição do Bloqueto Inválida",
		"15" => "Características da Cobrança Incompatíveis",
		"16" => "Data de Vencimento Inválida",
		"17" => "Data de Vencimento Anterior a Data de Emissão",
		"18" => "Vencimento Fora do Prazo de Operação",
		"19" => "Título a Cargo de Bancos Correspondentes com Vencimento Inferior a XX Dias",
		"20" => "Valor do Título Inválido",
		"21" => "Espécie do Título Inválida",
		"22" => "Espécie do Título Não Permitida para a Carteira",
		"23" => "Aceite Inválido",
		"24" => "Data da Emissão Inválida",
		"25" => "Data da Emissão Posterior a Data de Entrada",
		"26" => "Código de Juros de Mora Inválido",
		"27" => "Valor/Taxa de Juros de Mora Inválido",
		"28" => "Código do Desconto Inválido",
		"29" => "Valor do Desconto Maior ou Igual ao Valor do Título",
		"30" => "Desconto a Conceder Não Confere",
		"31" => "Concessão de Desconto - Já Existe Desconto Anterior",
		"32" => "Valor do IOF Inválido",
		"33" => "Valor do Abatimento Inválido",
		"34" => "Valor do Abatimento Maior ou Igual ao Valor do Título",
		"35" => "Valor a Conceder Não Confere",
		"36" => "Concessão de Abatimento - Já Existe Abatimento Anterior",
		"37" => "Código para Protesto Inválido",
		"38" => "Prazo para Protesto Inválido",
		"39" => "Pedido de Protesto Não Permitido para o Título",
		"40" => "Título com Ordem de Protesto Emitida",
		"41" => "Pedido de Cancelamento/Sustação para Títulos sem Instrução de Protesto",
		"42" => "Código para Baixa/Devolução Inválido",
		"43" => "Prazo para Baixa/Devolução Inválido",
		"44" => "Código da Moeda Inválido",
		"45" => "Nome do Sacado Não Informado",
		"46" => "Tipo/Número de Inscrição do Sacado Inválidos",
		"47" => "Endereço do Sacado Não Informado",
		"48" => "CEP Inválido",
		"49" => "CEP Sem Praça de Cobrança (Não Localizado)",
		"50" => "CEP Referente a um Banco Correspondente",
		"51" => "CEP incompatível com a Unidade da Federação",
		"52" => "Unidade da Federação Inválida",
		"53" => "Tipo/Número de Inscrição do Sacador/Avalista Inválidos",
		"54" => "Sacador/Avalista Não Informado",
		"55" => "Nosso número no Banco Correspondente Não Informado",
		"56" => "Código do Banco Correspondente Não Informado",
		"57" => "Código da Multa Inválido",
		"58" => "Data da Multa Inválida",
		"59" => "Valor/Percentual da Multa Inválido",
		"60" => "Movimento para Título Não Cadastrado",
		"61" => "Alteração da Agência Cobradora/DV Inválida",
		"62" => "Tipo de Impressão Inválido",
		"63" => "Entrada para Título já Cadastrado",
		"64" => "Número da Linha Inválido",
		"65" => "Código do Banco para Débito Inválido",
		"66" => "Agência/Conta/DV para Débito Inválido",
		"67" => "Dados para Débito incompatível com a Identificação da Emissão do Bloqueto",
		"68" => "Débito Automático Agendado",
		"69" => "Débito Não Agendado - Erro nos Dados da Remessa",
		"70" => "Débito Não Agendado - Sacado Não Consta do Cadastro de Autorizante",
		"71" => "Débito Não Agendado - Cedente Não Autorizado pelo Sacado",
		"72" => "Débito Não Agendado - Cedente Não Participa da Modalidade Débito Automático",
		"73" => "Débito Não Agendado - Código de Moeda Diferente de Real (R$)",
		"74" => "Débito Não Agendado - Data Vencimento Inválida",
		"75" => "Débito Não Agendado, Conforme seu Pedido, Título Não Registrado",
		"76" => "Débito Não Agendado, Tipo/Num. Inscrição do Debitado, Inválido",
		"77" => "Transferência para Desconto Não Permitida para a Carteira do Título",
		"78" => "Data Inferior ou Igual ao Vencimento para Débito Automático",
		"79" => "Data Juros de Mora Inválido",
		"80" => "Data do Desconto Inválida",
		"81" => "Tentativas de Débito Esgotadas - Baixado",
		"82" => "Tentativas de Débito Esgotadas - Pendente",
		"83" => "Limite Excedido",
		"84" => "Número Autorização Inexistente",
		"85" => "Título com Pagamento Vinculado ",
		"86" => "Seu Número Inválido",
		"87" => "E-mail/SMS enviado",
		"88" => "E-mail Lido",
		"89" => "E-mail/SMS devolvido - endereço de e-mail ou número do celular incorreto ",
		"90" => "E-mail devolvido - caixa postal cheia",
		"91" => "E-mail/número do celular do sacado não informado",
		"92" => "Sacado optante por Bloqueto Eletrônico - e-mail não enviado",
		"93" => "Código para emissão de bloqueto não permite envio de e-mail",
		"94" => "Código da Carteira inválido para envio e-mail.",
		"95" => "Contrato não permite o envio de e-mail",
		"96" => "Número de contrato inválido",
		"97" => "Rejeição da alteração do prazo limite de recebimento (a data deve ser informada no campo 28.3.p)",
		"98" => "Rejeição de dispensa de prazo limite de recebimento",
		"99" => "Rejeição da alteração do número do título dado pelo cedente",
		"A1" => "Rejeição da alteração do número controle do participante",
		"A2" => "Rejeição da alteração dos dados do sacado",
		"A3" => "Rejeição da alteração dos dados do sacador/avalista",
		"A4" => "Sacado DDA"
	);
	
}

?>