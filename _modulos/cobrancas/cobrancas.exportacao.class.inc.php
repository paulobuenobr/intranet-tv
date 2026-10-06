<?

set_time_limit(0);

class cobrancas_exportacao {

	var $bancodedados;

	##################
	### Construtor ###
	##################

	function __construct($bancodedados) {
		
		$this->bancodedados = $bancodedados;
	
	}

	##############################
	### Exporta Cobrancas Lote ###
	##############################

	function exportaLote($lote_id, $salvar_para_envio = false) {
		
		$dados_lote = $this->buscaDadosLote($lote_id);
		
		switch ($dados_lote["formato_id"]) {
			case  1 : return $this->exportaBoletoHSBC($dados_lote);
			case  7 : return $this->exportaBoletoSantander($dados_lote);
			case 10 : return $this->exportaBoletoCaixa($dados_lote);
			case 12 : return $this->exportaBoletoBancoReal($dados_lote);
			case 14 : return $this->exportaBoletoSantander($dados_lote);
			case 14 : return $this->exportaBoletoSantander($dados_lote);
			case 21 : return $this->exportaBoletoSantander($dados_lote);
			case 27 : return $this->exportaBoletoSantander($dados_lote);
			case  3 : return $this->exportaDebitoContaBB($dados_lote);
			case 20 : return $this->exportaDebitoContaBB($dados_lote);
			case 23 : return $this->exportaDebitoContaCEF($dados_lote);
			case 24 : return $this->exportaDebitoContaBESC240($dados_lote);
			case 25 : return $this->exportaDebitoContaBB($dados_lote);
			case 32 : return $this->exportaBoletoSantander($dados_lote);
			case 28 : return $this->exportaItau400($dados_lote, null, $salvar_para_envio);
			case 33 : return $this->exportaItau400($dados_lote, null, $salvar_para_envio);
			case 35 : return $this->exportaBoletoSantander($dados_lote);
		}
		
	}

	####################################
	### Exporta Cobrancas Individual ###
	####################################

	function exportaCobranca($cobranca_id, $salvar_para_envio = false) {
		
		$dados_cobranca = $this->buscaDadosCobranca($cobranca_id);
		
		switch ($dados_cobranca["formato_id"]) {
			case  1 : return $this->exportaBoletoHSBC(null, $dados_cobranca);
			case  7 : return $this->exportaBoletoSantander(null, $dados_cobranca);
			case  3 : return $this->exportaDebitoContaBB(null, $dados_cobranca);
			case 28 : return $this->exportaItau400(null, $dados_cobranca, $salvar_para_envio);
			case 33 : return $this->exportaItau400(null, $dados_cobranca, $salvar_para_envio);
		}
	
	}

	#############
	### Geral ###
	#############

	function carregaArquivo($arquivo) {
		
		$fp = fopen($arquivo, "r");
		$conteudo = fread($fp, filesize($arquivo));
		fclose($fp);
		
		return $conteudo;
	
	}

	#############################
	### Funções de Exportação ###
	#############################

	function exportaBoletoHSBC($dados_lote) {
		
		// Cabeçalho
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=boletos_hsbc_{$dados_lote["vencimento_mes"]}_{$dados_lote["vencimento_ano"]}.xls;");
		
		// Carrega Template
		$cobranca_conteudo = $this->carregaArquivo("modelos/cobranca_boleto.htm");
		
		// Busca Cobrança
		$cobranca_registros = $this->buscaCobrancasBoletos($dados_lote);
		$cobranca_conteudo = str_replace("[cobranca_registros]", $cobranca_registros, $cobranca_conteudo);
		$cobranca_conteudo = str_replace("[lote_nome]", str_replace(array("/", "?", ":", "[", "]"), " ", $dados_lote["nome"]), $cobranca_conteudo);
		
		// Imprime Conteudo
		print $cobranca_conteudo;
		exit;
	
	}

	function exportaBoletoBancoReal($dados_lote) {
		
		// Cabeçalho
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=boletos_banco_real_{$dados_lote["vencimento_mes"]}_{$dados_lote["vencimento_ano"]}.xls;");
		
		// Carrega Template
		$cobranca_conteudo = $this->carregaArquivo("modelos/cobranca_boleto.htm");
		
		// Busca Cobrança
		$cobranca_registros = $this->buscaCobrancasBoletos($dados_lote);
		$cobranca_conteudo = str_replace("[cobranca_registros]", $cobranca_registros, $cobranca_conteudo);
		$cobranca_conteudo = str_replace("[lote_nome]", str_replace(array("/", "?", ":", "[", "]"), " ", $dados_lote["nome"]), $cobranca_conteudo);
		
		// Imprime Conteudo
		print $cobranca_conteudo;
		exit;
	
	}

	function exportaBoletoCaixa($dados_lote) {
	
		// Cabeçalho
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=boletos_caixa_{$dados_lote["vencimento_mes"]}_{$dados_lote["vencimento_ano"]}.xls;");
		
		// Carrega Template
		$cobranca_conteudo = $this->carregaArquivo("modelos/cobranca_boleto.htm");
		
		// Busca Cobrança
		$cobranca_registros = $this->buscaCobrancasBoletos($dados_lote);
		$cobranca_conteudo = str_replace("[cobranca_registros]", $cobranca_registros, $cobranca_conteudo);
		$cobranca_conteudo = str_replace("[lote_nome]", str_replace(array("/", "?", ":", "[", "]"), " ", $dados_lote["nome"]), $cobranca_conteudo);
		
		// Imprime Conteudo
		print $cobranca_conteudo;
		exit;
	
	}

	function exportaBoletoSantander($dados_lote) {
		
		// Cabeçalho
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=boletos_santander_{$dados_lote["vencimento_mes"]}_{$dados_lote["vencimento_ano"]}.xls;");
		
		// Carrega Template
		$cobranca_conteudo = $this->carregaArquivo("modelos/cobranca_boleto.htm");
		
		// Busca Cobrança
		$cobranca_registros = $this->buscaCobrancasBoletos($dados_lote);
		$cobranca_conteudo = str_replace("[cobranca_registros]", $cobranca_registros, $cobranca_conteudo);
		$cobranca_conteudo = str_replace("[lote_nome]", str_replace(array("/", "?", ":", "[", "]"), " ", $dados_lote["nome"]), $cobranca_conteudo);
		
		// Imprime Conteudo
		print $cobranca_conteudo;
		exit;
		
	}

	function exportaDebitoContaBB($dados_lote = null, $dados_cobranca = null) {
		
		$cobranca_conteudo = array();
		$parametros_formato = ($dados_lote) ? $dados_lote["parametros"] : $dados_cobranca["parametros"];
		$arquivo_nome = ($dados_lote) ? "remessa_bb_{$dados_lote["vencimento_mes"]}_{$dados_lote["vencimento_ano"]}.txt" : "remessa_bb_{$dados_cobranca["codigo"]}.txt";
		
		// Cabeçalho
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=$arquivo_nome;");
		
		// Cabeçalho
		$cabecalho = "A"; // Fixo "A" para identificar este registro
		$cabecalho .= "1"; // Fixo "1" para indicar que remessa da empresa para o Banco
		$cabecalho .= sprintf("% -20s", substr(sprintf("%05s", $parametros_formato["cedente"]["valor"]), 0, 20));
		$cabecalho .= sprintf("% -20s", substr($parametros_formato["empresa"]["valor"], 0, 20));
		$cabecalho .= sprintf("%03s", substr($parametros_formato["banco_codigo"]["valor"], 0, 3));
		$cabecalho .= sprintf("% -20s", substr($parametros_formato["banco_nome"]["valor"], 0, 20));
		$cabecalho .= date("Ymd"); // Data de processamento
		$cabecalho .= sprintf("%06s", $parametros_formato["sequencial"]["valor"]); // Sequencial
		$cabecalho .= sprintf("%02s", "04"); // Versão do layout
		$cabecalho .= sprintf("% -17s", "DEBITO AUTOMATICO");
		$cabecalho .= sprintf("% -52s", " ");
		
		$cobranca_conteudo[] = $cabecalho;
		
		// Busca Cobranças Lote
		$cobrancas = $this->buscaCobrancasDebitoBB($dados_lote["id"], $dados_cobranca["id"]);
		
		$valor_total = 0;
		
		foreach ($cobrancas as $cobranca) {
			
			// Trata Dados
			$registro = "E"; // Identificação do Registro
			
			if ($dados_lote["formato_id"] == 3) {
				$agencia = preg_replace("/[^0-9]/", "", substr($cobranca["agencia"], 0, -2));
				$conta_corrente = preg_replace("/[^0-9]/", "", substr($cobranca["conta"], 0, -2));
				$registro .= sprintf("%010s", $cobranca["cliente_id"]).sprintf("%05s", $agencia).sprintf("%010s", $conta_corrente); // Código
			} else {
				/*
				$operacao = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["operacao"]);
				$conta_corrente = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["conta_corrente"]);
				$agencia = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["agencia"]);
				$registro .= "1".sprintf("%08s", $cobranca["cliente_id"]).sprintf("%03s", $operacao).sprintf("%05s", $agencia).sprintf("%08s", $conta_corrente); // Código
				*/
				$registro .= $cobranca["codigo_debito"]; // Código
			}
			
			$registro .= sprintf("%04s", preg_replace("/[^0-9]/", "", substr($cobranca["agencia"], 0, -2))); // Agência do cliente
			$registro .= sprintf("%014s", preg_replace("/[^0-9]/", "", substr($cobranca["conta"], 0, -2))); // Conta do cliente
			$registro .= $cobranca["data_vencimento"]; // Data de vencimento
			$registro .= sprintf("%015s", $cobranca["valor_original"] - $cobranca["valor_bonificacao"]); // Valor da cobrança
			$registro .= sprintf("%02s", "03"); // Moeda: 03 para Real, e 01 para UFIR
			$registro .= sprintf("% -60s", substr($this->corrigeNomeCliente($cobranca["nome"]), 0, 60)); // Campo livre
			$registro .= sprintf("% -20s", " ");
			$registro .= "0";
			
			$cobranca_conteudo[] = $registro;
			
			$valor_total += $cobranca["valor_original"] - $cobranca["valor_bonificacao"];
		
		}
		
		// Rodapé
		$rodape = "Z"; //fixo "Z" para identificar este registro
		$rodape .= sprintf("%06s", count($cobranca_conteudo) + 1); // Quantidade de linhas do arquivo
		$rodape .= sprintf("%017s", $valor_total); // Valor total das cobranças
		$rodape .= sprintf("% 126s", " ");
		
		$cobranca_conteudo[] = $rodape;
		
		// Imprime Conteudo
		print @implode("\n", $cobranca_conteudo);
		exit;
		
	}

	function exportaDebitoContaCEF($dados_lote = null, $dados_cobranca = null) {
		
		$cobranca_conteudo = array();
		$parametros_formato = ($dados_lote) ? $dados_lote["parametros"] : $dados_cobranca["parametros"];
		$arquivo_nome = ($dados_lote) ? "remessa_cef_{$dados_lote["vencimento_mes"]}_{$dados_lote["vencimento_ano"]}.txt" : "remessa_bb_{$dados_cobranca["codigo"]}.txt";
		
		// Cabeçalho
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=$arquivo_nome;");

		// 001 - 001 (001) Código do Registro = "A"
		$cabecalho = "A";
		// 002 - 002 (001) Código da Remessa (1 = Remessa, 2 = Retorno)
		$cabecalho .= "1";
		// 003 - 022 (020) Código do Convênio
		$cabecalho .= sprintf("% -20s", substr(sprintf("%06s", $parametros_formato["cedente"]["valor"]), 0, 20));
		// 023 - 042 (020) Nome da Empresa
		$cabecalho .= sprintf("% -20s", strtoupper(substr($parametros_formato["empresa"]["valor"], 0, 20)));
		// 043 - 045 (003) Código do Banco =  "104"
		$cabecalho .= sprintf("%03s", substr($parametros_formato["banco_codigo"]["valor"], 0, 3));
		// 046 - 065 (020) Nome do Banco
		$cabecalho .= sprintf("% -20s", substr($parametros_formato["banco_nome"]["valor"], 0, 20));
		// 066 - 073 (008) Data do Movimento
		$cabecalho .= date("Ymd");
		// 074 - 079 (006) Número Sequencial do Arquivo
		$cabecalho .= sprintf("%06s", $parametros_formato["sequencial"]["valor"]); // Sequencial
		// 080 - 081 (002) Versão do Layout = 04
		$cabecalho .= "04";
		// 082 - 098 (017) Identificação do Serviço
		$cabecalho .= sprintf("% -17s", "DEB/CRED AUTOMAT");
		// 099 - 150 (052) Reservado para o Futuro (Filler)
		$cabecalho .= sprintf("% -52s", "");

		$cobranca_conteudo[] = $cabecalho;
		
		// Busca Cobranças Lote
		$cobrancas = $this->buscaCobrancas($dados_lote["id"], $dados_cobranca["id"]);
		
		$valor_total = 0;
		
		foreach ($cobrancas as $cobranca) {
			
			// Trata Dados
			$operacao = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["operacao"]);
			$agencia = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["agencia"]);
			$conta = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["conta_corrente"]);
			// Dados Registro E - Débito
			$registro = "E"; // Identificação do Registro
			$registro .= $this->geraCodigoCEF($cobranca); // Código
			$registro .= sprintf("%04s", $agencia); // Agência do cliente
			$registro .= sprintf("%03s", $operacao).sprintf("%09s", $conta).sprintf("% 2s", ""); // Conta do cliente
			$registro .= $cobranca["data_vencimento"]; // Data de vencimento
			$registro .= sprintf("%015s", $cobranca["valor_original"] - $cobranca["valor_bonificacao"]); // Valor da cobrança
			$registro .= sprintf("%02s", "03"); // Moeda: 03 para Real, e 01 para UFIR
			$registro .= sprintf("% -60s", substr($this->corrigeNomeCliente($cobranca["parametros"]["titular"]["nome"]), 0, 60)); // Campo livre
			$registro .= sprintf("% -20s", " ");
			$registro .= "0";
			
			$cobranca_conteudo[] = $registro;
			
			$valor_total += $cobranca["valor_original"] - $cobranca["valor_bonificacao"];
		
		}
		// Rodapé
		$rodape = "Z"; //fixo "Z" para identificar este registro
		$rodape .= sprintf("%06s", count($cobranca_conteudo) + 1); // Quantidade de linhas do arquivo
		$rodape .= sprintf("%017s", $valor_total); // Valor total das cobranças - Débito
		$rodape .= sprintf("%017s", 0); // Valor total das cobranças - Crédito
		$rodape .= sprintf("% 109s", "");
		
		$cobranca_conteudo[] = $rodape;
		
		// Imprime Conteudo
		print implode("\r\n", $cobranca_conteudo);
		exit;
	
	}

	function geraCodigoCEF($cobranca) {
		
		$operacao = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["operacao"]);
		$agencia = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["agencia"]);
		$conta = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["conta_corrente"]);
		
		return "1".sprintf("%08s", $cobranca["cliente_id"]).sprintf("%03s", $operacao).sprintf("%04s", $agencia).sprintf("%09s", $conta);
		
	}

	function exportaDebitoContaBESC240($dados_lote = null, $dados_cobranca = null) {
		
		$cobranca_conteudo = array();
		$parametros_formato = ($dados_lote) ? $dados_lote["parametros"] : $dados_cobranca["parametros"];
		$arquivo_nome = ($dados_lote) ? "AE".sprintf("%06s", $parametros_formato["cedente"]["valor"]).".AGL" : "remessa_bb_{$dados_cobranca["codigo"]}.txt";
		
		header("Content-type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=$arquivo_nome;");

		#########################
		### HEADER DE ARQUIVO ###
		#########################

		// Código do Banco na Compensação
		$cabecalho = substr(sprintf("%03s", $parametros_formato["banco_codigo"]["valor"]), 0, 3);
		// Lote de Serviço
		$cabecalho .= "0000";
		// Tipo de Registro do Arquivo
		$cabecalho .= "0";
		// Uso Exclusivo FEBRABAN/CNAB
		$cabecalho .= sprintf("% 9s", "");
		// Tipo de Inscrição da Empresa
		$cabecalho .= "2";
		// Número de Inscrição da Empresa
		$cabecalho .= substr(sprintf("%014s", $parametros_formato["cnpj"]["valor"]), 0, 14);
		// Código do Convênio no Banco
		$cabecalho .= substr(sprintf("%020s", $parametros_formato["cedente"]["valor"]), 0, 20);
		// Número da Agência Mantenedora da Conta
		$cabecalho .= substr(sprintf("%06s", $parametros_formato["agencia"]["valor"]), 0, 6);
		// Número da Conta Corrente
		$cabecalho .= substr(sprintf("%013s", $parametros_formato["conta_corrente"]["valor"]), 0, 13);
		// Dígito Verificador da Agência / Conta
		$cabecalho .= "0";
		// Nome da Empresa
		$cabecalho .= substr(sprintf("% -30s", $parametros_formato["empresa"]["valor"]), 0, 30);
		// Nome do Banco
		$cabecalho .= substr(sprintf("% -30s", $parametros_formato["banco_nome"]["valor"]), 0, 30);
		// Uso Exclusivo FEBRABAN/CNAB
		$cabecalho .= sprintf("% 10s", "");
		// Código de Transmissão do Arquivo
		$cabecalho .= "1";
		// Data de geração do Arquivo
		$cabecalho .= date("dmY");
		// Hora de geração do Arquivo
		$cabecalho .= date("His");
		// Número Seqüencial do Arquivo
		$cabecalho .= substr(sprintf("%06s", $parametros_formato["sequencial"]["valor"]), 0, 6); // Sequencial
		// Número da Versão do LAY-OUT
		$cabecalho .= "020";
		// Densidade de gravação do Arquivo em BPI
		$cabecalho .= "00000";
		// Identificação do Sistema
		$cabecalho .= "AGL";
		// Tipo de movimento do Arquivo
		$cabecalho .= "0";
		// Uso Exclusivo do BESC
		$cabecalho .= sprintf("% 16s", "");
		// Uso Exclusivo da Empresa
		$cabecalho .= sprintf("% 20s", "");
		// Uso Exclusivo FEBRABAN/CNAB
		$cabecalho .= sprintf("% 29s", "");

		$cobranca_conteudo[] = $cabecalho;

		######################
		### HEADER DE LOTE ###
		######################

		// Código do Banco na Compensação
		$cabecalho = substr(sprintf("%03s", $parametros_formato["banco_codigo"]["valor"]), 0, 3);
		// Lote de Serviço
		$cabecalho .= "0001";
		// Código do registro
		$cabecalho .= "1";
		// Tipo de operação
		$cabecalho .= "D";
		// Tipo de Serviço
		$cabecalho .= "00";
		// Forma de Lançamento
		$cabecalho .= "50";
		// Número da Versão do LAY-OUT
		$cabecalho .= "020";
		// Uso Exclusivo FEBRABAN/CNAB
		$cabecalho .= " ";
		// Tipo de Inscrição no CIC
		$cabecalho .= "2";
		// Número de Inscrição da Empresa no CIC
		$cabecalho .= substr(sprintf("%014s", $parametros_formato["cnpj"]["valor"]), 0, 14);
		// Código do Convênio no Banco
		$cabecalho .= substr(sprintf("%020s", $parametros_formato["cedente"]["valor"]), 0, 20);
		// Agência Mantenedora da Conta
		$cabecalho .= substr(sprintf("%06s", $parametros_formato["agencia"]["valor"]), 0, 6);
		// Número da Conta Corrente
		$cabecalho .= substr(sprintf("%013s", $parametros_formato["conta_corrente"]["valor"]), 0, 13);
		// Dígito Verificador da Agência / Conta
		$cabecalho .= "0";
		// Nome da Empresa
		$cabecalho .= substr(sprintf("% -30s", $parametros_formato["empresa"]["valor"]), 0, 30);
		// Mensagem
		$cabecalho .= sprintf("% 40s", "");
		// Nome do Logradouro
		$cabecalho .= sprintf("% -30s", "Rua Altamiro Guimaraes");
		// Número do Local
		$cabecalho .= sprintf("%05s", "265");
		// Complemento do endereço
		$cabecalho .= sprintf("% -15s", "Sala 101");
		// Nome da Cidade
		$cabecalho .= sprintf("% -20s", "Florianopolis");
		// Código de Endereçamento Postal
		$cabecalho .= sprintf("%05s", "88015");
		// Complemento do CEP
		$cabecalho .= sprintf("%03s", "510");
		// Sigla da Unidade da Federação
		$cabecalho .= "SC";
		// Uso Exclusivo FEBRABAN/CNAB
		$cabecalho .= sprintf("% 8s", "");
		// Código das ocorrências para Retorno
		$cabecalho .= sprintf("% 10s", "");

		$cobranca_conteudo[] = $cabecalho;

		// Busca Cobranças Lote
		$cobrancas = $this->buscaCobrancas($dados_lote["id"], $dados_cobranca["id"]);
		$valor_total = 0;
		
		foreach ($cobrancas as $cobranca) {

			###############
			### DETALHE ###
			###############

			// Código do Banco na Compensação
			$detalhe = substr(sprintf("%03s", $parametros_formato["banco_codigo"]["valor"]), 0, 3);
			// Lote de Serviço
			$detalhe .= "0001";
			// Tipo de registro
			$detalhe .= "3";
			// Seqüencial do Registro no Lote
			$detalhe .= sprintf("%05s", count($cobranca_conteudo) - 1);
			// Código do Segmento do Registro Detalhe
			$detalhe .= "A";
			// Tipo de Movimento
			$detalhe .= "0";
			// Código da Instrução para Movimento
			$detalhe .= "00";
			// Código da Câmara de Compensação
			$detalhe .= "000";
			// Código do Banco do Favorecido
			$detalhe .= substr(sprintf("%03s", $parametros_formato["banco_codigo"]["valor"]), 0, 3);
			// Agência mantenedora da Conta
			$detalhe .= substr(sprintf("%06s", preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["agencia"])), 0, 6);
			// Número da Conta Corrente
			$detalhe .= substr(sprintf("%013s", preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["conta_corrente"])), 0, 13);
			// Dígito Verificador da Agência/Conta
			$detalhe .= "0";
			// Nome do Favorecido
			$detalhe .= substr(sprintf("% -30s", $this->corrigeNomeCliente($cobranca["parametros"]["titular"]["nome"])), 0, 30);
			// N.º do Documento atribuído pela Empresa
			$detalhe .= sprintf("% -20s", $cobranca["codigo"]);
			// Data para Lançamento
			$detalhe .= substr($cobranca["data_vencimento"], 6, 2).substr($cobranca["data_vencimento"], 4, 2).substr($cobranca["data_vencimento"], 0, 4);
			// Tipo da Moeda
			$detalhe .= "BRL";
			// Quantidade de Moeda
			$detalhe .= sprintf("%015s", "0");
			// Valor do Lançamento
			$detalhe .= sprintf("%015s", $cobranca["valor_original"] - $cobranca["valor_bonificacao"]);
			// Data Fim de Validade do Lançamento
			$detalhe .= date("dmY", strtotime("{$cobranca["data_vencimento"]} 5 days"));
			// Indicador de Provisionamento
			$detalhe .= "0";
			// Código Finalidade para DOC ou TED
			$detalhe .= "00";
			// Uso Exclusivo do BESC
			$detalhe .= sprintf("% -9s", "");
			// Data da Efetivação do lançamento
			$detalhe .= sprintf("%08s", "0");
			// Valor do Lançamento efetivado
			$detalhe .= sprintf("%015s", "0");
			// Mensagem
			$detalhe .= sprintf("% -40s", "");
			// Uso Exclusivo FEBRABAN/CNAB
			$detalhe .= sprintf("% -12s", "");
			// Flag de Aviso ao Favorecido
			$detalhe .= "0";
			// Código das Ocorrências para Retorno
			$detalhe .= sprintf("% -10s", "");

			$cobranca_conteudo[] = $detalhe;

			$valor_total += $cobranca["valor_original"] - $cobranca["valor_bonificacao"];
		
		}

		#######################
		### TRAILER DE LOTE ###
		#######################

		// Código do Banco na Compensação
		$rodape = substr(sprintf("%03s", $parametros_formato["banco_codigo"]["valor"]), 0, 3);
		// Lote de Serviço
		$rodape .= "0001";
		// Tipo de Registro
		$rodape .= "5";
		// Uso Exclusivo FEBRABAN/CNAB
		$rodape .= sprintf("% -9s", "");
		// Quantidade de Registros no Lote
		$rodape .= sprintf("%06s", count($cobranca_conteudo) - 2);
		// Somatória dos Valores
		$rodape .= sprintf("%018s", $valor_total);
		// Somatória das Quantidades de Moedas
		$rodape .= sprintf("%018s", "0");
		// Uso Exclusivo FEBRABAN/CNAB
		$rodape .= sprintf("% -171s", "");
		// Códigos de Ocorrências para Retorno
		$rodape .= sprintf("% -10s", "");

		$cobranca_conteudo[] = $rodape;

		##########################
		### TRAILER DE ARQUIVO ###
		##########################

		// Código do Banco na Compensação
		$rodape = substr(sprintf("%03s", $parametros_formato["banco_codigo"]["valor"]), 0, 3);
		// Lote de Serviço
		$rodape .= "9999";
		// Tipo de Registro
		$rodape .= "9";
		// Uso Exclusivo FEBRABAN/CNAB
		$rodape .= sprintf("% -9s", "");
		// Quantidade de Registros Lote
		$rodape .= sprintf("%06s", 1);
		// Quantidade de Registros no Arquivo
		$rodape .= sprintf("%06s", count($cobranca_conteudo) + 1);
		// Quantidade de Contas para Conciliação
		$rodape .= sprintf("%06s", "0");
		// Uso Exclusivo FEBRABAN/CNAB
		$rodape .= sprintf("% -205s", "");

		$cobranca_conteudo[] = $rodape;

		// Imprime Conteudo
		print implode("\r\n", $cobranca_conteudo);
		exit;
	
	}

	function modulo11NN($valor) {
		
		$peso = 2;
		$soma = 0;
		
		for ($i = strlen($valor) - 1; $i >= 0; $i--) {
			$soma += strval($valor[$i] * $peso);
			$peso = ($peso < 9) ? $peso + 1 : 2;
		}
		
		$resto = $soma % 11;
		
		return in_array($resto, array(0, 1)) ? 0 : 11 - $resto;
	
	}

	function geraCodigoBESC($cobranca) {
		
		$agencia = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["agencia"]);
		$conta = preg_replace("/[^0-9]/", "", $cobranca["parametros"]["dados_cobranca"]["conta_corrente"]);
		
		return "1".sprintf("%09s", $cobranca["cliente_id"]).sprintf("%05s", $agencia).sprintf("%010s", $conta);
	
	}

	########################
	### Funções de Busca ###
	########################

	function buscaDadosLote($lote_id) {
		
		$query = "
			SELECT
				tab_cobrancas_lotes.col_id AS id,
				tab_cobrancas_lotes.col_nome AS nome,
				tab_cobrancas_lotes.col_parametros AS parametros,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%m') AS vencimento_mes,
				DATE_FORMAT(tab_cobrancas_lotes.col_vencimento, '%Y') AS vencimento_ano,
				tab_cobrancas_formatos.cof_id AS formato_id
			FROM
				tab_cobrancas_lotes
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas_lotes.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				col_id = $lote_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["parametros"] = @unserialize($dados["parametros"]);
			$dados["formato_id"] = $dados["formato_id"] == 5 ? 1 : $dados["formato_id"];
			return $dados;
		}
	
	}

	function buscaDadosCobranca($cobranca_id) {
		
		$query = "
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas_formatos.cof_parametros AS parametros,
				tab_cobrancas_formatos.cof_id AS formato_id
			FROM
				tab_cobrancas
				INNER JOIN tab_cobrancas_formatos ON (tab_cobrancas.cof_id = tab_cobrancas_formatos.cof_id)
			WHERE
				coa_id = $cobranca_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["parametros"] = unserialize($dados["parametros"]);
			return $dados;
		}
	
	}

	function buscaCobrancas($lote_id = null, $cobranca_id = null) {
		
		$cobrancas = array();
		$feriado = array();
		
		$filtro = array();
		$filtro[] = $lote_id ? "col_id = $lote_id" : "coa_id = $cobranca_id";
		$filtro[] = "tab_cobrancas.cot_id = 1";
		$filtro = implode(" AND\n        ", $filtro);
		
		$query = "
			SELECT DISTINCT
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_ccd_debito_codigo AS codigo_debito,
				tab_cobrancas.coa_id AS id,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%Y%m%d') AS data_vencimento,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_cobrancas.cof_id AS formato_id
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				$filtro
			ORDER BY
				tab_clientes.cli_nome";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados_cobranca = $this->bancodedados->resultadoArray($query)) != false) {
			
			if (array_key_exists($dados_cobranca["data_vencimento"], $feriado)) {
				$dados_cobranca["data_vencimento"] = $feriado[$dados_cobranca["data_vencimento"]];
			} elseif (in_array(date("w", strtotime($dados_cobranca["data_vencimento"])), array(0, 6))) {
				$feriado[$dados_cobranca["data_vencimento"]] = date("Ymd", strtotime("{$dados_cobranca["data_vencimento"]} next monday"));
				$dados_cobranca["data_vencimento"] = $feriado[$dados_cobranca["data_vencimento"]];
			}
			$dados_cobranca["parametros"] = unserialize($dados_cobranca["parametros"]);
			
			$cobrancas[] = $dados_cobranca;
		
		}
		
		return $cobrancas;
		
	}

	function buscaCobrancasDebitoBB($lote_id = null, $cobranca_id = null) {
		
		$cobrancas = array();
		$feriado = array();
		
		$filtro = array();
		$filtro[] = $lote_id ? "tab_cobrancas.col_id = $lote_id" : "tab_cobrancas.coa_id = $cobranca_id";
		$filtro[] = "tab_cobrancas.cot_id = 1";
		$filtro = implode(" AND\n        ", $filtro);
		
		$query = "
			SELECT
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS nome,
				tab_clientes.cli_ccd_debito_codigo AS codigo_debito,
				tab_clientes.cli_ccd_debito_operacao AS operacao,
				tab_clientes.cli_ccd_debito_agencia AS agencia,
				tab_clientes.cli_ccd_debito_conta AS conta,
				DATE_FORMAT(MIN(tab_cobrancas.coa_data_vencimento), '%Y%m%d') AS data_vencimento,
				SUM(tab_cobrancas.coa_valor_bonificacao) AS valor_bonificacao,
				SUM(tab_cobrancas.coa_valor_original) AS valor_original,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas.cof_id AS formato_id
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				$filtro
			GROUP BY
				tab_clientes.cli_ccd_debito_codigo
			ORDER BY
				tab_clientes.cli_nome";
				
		$query = $this->bancodedados->query($query);
		
		while (($dados_cobranca = $this->bancodedados->resultadoArray($query)) != false) {
			
			if (array_key_exists($dados_cobranca["data_vencimento"], $feriado)) {
				$dados_cobranca["data_vencimento"] = $feriado[$dados_cobranca["data_vencimento"]];
			} elseif (in_array(date("w", strtotime($dados_cobranca["data_vencimento"])), array(0, 6))) {
				$feriado[$dados_cobranca["data_vencimento"]] = date("Ymd", strtotime("{$dados_cobranca["data_vencimento"]} next monday"));
				$dados_cobranca["data_vencimento"] = $feriado[$dados_cobranca["data_vencimento"]];
			}
			$dados_cobranca["parametros"] = unserialize($dados_cobranca["parametros"]);
			if (!$dados_cobranca["operacao"]) $dados_cobranca["operacao"] = $dados_cobranca["parametros"]["dados_cobranca"]["operacao"];
			if (!$dados_cobranca["agencia"]) $dados_cobranca["agencia"] = $dados_cobranca["parametros"]["dados_cobranca"]["agencia"];
			if (!$dados_cobranca["conta"]) $dados_cobranca["conta"] = $dados_cobranca["parametros"]["dados_cobranca"]["conta_corrente"];
			if (!$dados_cobranca["codigo_debito"]) {
				$clientes = controle::instanciaObjeto("clientes", $this->bancodedados, null, false);
				$dados_cobranca["codigo_debito"] = $clientes->geraCodigoDebitoAutomatico($dados_cobranca["cliente_id"], $dados_cobranca["formato_id"], $dados_cobranca["agencia"], $dados_cobranca["conta"], $dados_cobranca["operacao"]);
			}
			
			$cobrancas[] = $dados_cobranca;
		
		}
		
		return $cobrancas;
	
	}
	
	//TODO
	function buscaCobrancasBoletos($dados_lote) {
		
		$cobrancas = array();
		
		$cobranca_registro = "
			<tr height=17 style='height:12.75pt'>
			<td height=17 class=xl30 style='height:12.75pt'>[nome]</td>
			<td class=xl26>[cpf_cnpj]</td>
			<td class=xl26>[endereco]</td>
			<td class=xl26>[complemento]</td>
			<td class=xl26>[bairro]</td>
			<td class=xl26>[cidade_estado]</td>
			<td class=xl26>[cep]</td>
			<td class=xl26>[competencia]</td>
			<td class=xl26>[vencimento]</td>
			<td class=xl26>[nosso_numero]</td>
			<td class=xl26>[documento]</td>
			<td class=xl26>[instrucao]</td>
			<td class=xl28 x:num=\"[valor_adesao]\">[valor_adesao_formatado]</td>
			<td class=xl28 x:num=\"[valor_plano_life_line]\">[valor_plano_life_line_formatado]</td>
			<td class=xl28 x:num=\"[valor_plano_start]\">[valor_plano_start_formatado]</td>
			<td class=xl28 x:num=\"[valor_plano_top]\">[valor_plano_top_formatado]</td>
			<td class=xl28 x:num=\"[valor_plano_premium]\">[valor_plano_premium_formatado]</td>
			<td class=xl28 x:num=\"[valor_hbo]\">[valor_hbo_formatado]</td>
			<td class=xl28 x:num=\"[valor_fox]\">[valor_garantia_bonificacao_formatado]</td>
			<td class=xl28 x:num=\"[valor_adulto]\">[valor_assistencia_formatado]</td>
			<td class=xl28 x:num=\"[valor_decoder]\">[valor_decoder_formatado]</td>
			<td class=xl28 x:num=\"[valor_multa]\">[valor_multa_formatado]</td>
			<td class=xl28 x:num=\"[valor_total]\">[valor_total_formatado]</td>
			</tr>";
		
		// Busca Cobranças
		$atributos_ultima_linha = array("xl30" => "xl31", "xl26" => "xl32", "xl27" => "xl33", "xl28" => "xl34");
		
		$query = "
			SELECT
				tab_cobrancas.coa_id AS id,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_cobrancas.coa_codigo AS codigo,
				tab_cobrancas.coa_nosso_numero AS nosso_numero,
				tab_cobrancas.coa_parametros AS parametros,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_clientes.clo_id AS origem_id,
				tab_clientes.clt_id AS cliente_tipo_id,
				tab_clientes.cli_bloqueio_dias AS dias_bloqueio
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_cobrancas.col_id = {$dados_lote["id"]} AND
				tab_cobrancas.cot_id != 12";
		
		$query = $this->bancodedados->query($query);
	
		while (($dados_cobranca = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados_cobranca["dias_bloqueio"] = $dados_cobranca["dias_bloqueio"] ? $dados_cobranca["dias_bloqueio"] : 15;
			$dados_cobranca["competencia"] = $this->buscaCobrancaCompetencia($dados_cobranca["id"]);
			$dados_cobranca["parametros"] = unserialize($dados_cobranca["parametros"]);
			
			$cobranca = array();
			
			// Dados do Cliente
			$cobranca["[nome]"] = ($dados_cobranca["parametros"]["titular"]["nome"]) ? $this->corrigeNomeCliente($dados_cobranca["parametros"]["titular"]["nome"]) : $dados_cobranca["id"];
			$cobranca["[cpf_cnpj]"] = $dados_cobranca["parametros"]["titular"]["cpf_cnpj"];
			
			// Endereço
			$complemento = $this->formataComplemento($dados_cobranca["parametros"]["endereco_cobranca"]["complemento"]);
			
			$cobranca["[endereco]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["logradouro"].($dados_cobranca["parametros"]["endereco_cobranca"]["numero"] ? ", ".$dados_cobranca["parametros"]["endereco_cobranca"]["numero"] : "");
			$cobranca["[numero]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["numero"];
			$cobranca["[bloco]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["bloco"];
			$cobranca["[apartamento]"] = sprintf("%06s", intval(preg_replace("/[^0-9]/", "", $dados_cobranca["parametros"]["endereco_cobranca"]["complemento"]), 10));
			$cobranca["[complemento]"] = ($dados_cobranca["parametros"]["endereco_cobranca"]["condominio"] ? $dados_cobranca["parametros"]["endereco_cobranca"]["condominio"].($complemento ? ", " : "") : '').$complemento.($dados_cobranca["parametros"]["endereco_cobranca"]["bloco"] && $dados_cobranca["parametros"]["endereco_cobranca"]["bloco"] <> "Único" ? ($dados_cobranca["parametros"]["endereco_cobranca"]["complemento"] && $dados_cobranca["parametros"]["endereco_cobranca"]["complemento"] != "o" ? " / " : "").$dados_cobranca["parametros"]["endereco_cobranca"]["bloco"] : '');
			$cobranca["[bairro]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["bairro"];
			$cobranca["[cidade_estado]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["cidade"]." / ".$dados_cobranca["parametros"]["endereco_cobranca"]["estado"];
			$cobranca["[estado]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["estado"] == "SC" ? 0 : 1;
			$cobranca["[cep]"] = $dados_cobranca["parametros"]["endereco_cobranca"]["cep"];
			
			// Dados da Cobrança
			$cobranca["[competencia]"] = $dados_cobranca["competencia"];
			$cobranca["[vencimento]"] = $dados_cobranca["data_vencimento"];
			$cobranca["[nosso_numero]"] = $dados_cobranca["parametros"]["boleto"]["nosso_numero"];
			$cobranca["[documento]"] = $dados_cobranca["codigo"];
			$cobranca["[instrucao]"] = preg_replace("/(<br>|\r\n)+/i", " ", $dados_cobranca["parametros"]["boleto"]["instrucoes"]);
			
			for ($i = 0; $i < 4; $i++) {
				if (array_key_exists($i, $dados_cobranca["parametros"]["itens"])) {
					$cobranca["[observacao_".($i + 1)."]"] = $dados_cobranca["parametros"]["itens"][$i];
				} else {
					$cobranca["[observacao_".($i + 1)."]"] = "";
				}
			}
			
			// Valores
			$valores = $this->buscaValores($dados_cobranca["id"]);
			
			foreach ($valores as $id => $valor) {
				$cobranca["[{$id}]"] = $valor ? round($valor / 100, 2) : "";
				$cobranca["[{$id}_formatado]"] = $this->formataDinheiro($valor);
			}
			
			$cobrancas[] = $cobranca;
		
		}
		
		// Ordena
		$cobrancas = $this->ordenaMultiArray($cobrancas, array("[complemento]", "[endereco]", "[bairro]", "[cidade_estado]"));
		
		// Monta Linhas
		for ($i = 0; $i < count($cobrancas); $i++) {
			if ($i == count($cobrancas) - 1) {
				$cobranca_registro = str_replace(array_keys($atributos_ultima_linha), array_values($atributos_ultima_linha), $cobranca_registro);
			}
			$cobrancas[$i] = preg_replace("/\\[[^\\[]+\\]/", "", str_replace(array_keys($cobrancas[$i]), array_values($cobrancas[$i]), $cobranca_registro));
		}
		
		return implode("\n", $cobrancas);
			
	}

	function formataComplemento($complemento) {
		
		if ($complemento) {
			
			if (!preg_match("/^((apto)|(loja)|(sala)|(casa))/i", $complemento) && preg_match("/^[alsco]/", $complemento)) {
				$complemento_tipo_id = substr($complemento, 0, 1);
				$complemento_valor = substr($complemento, 1);
				if ($complemento_tipo_id != "o") {
					return $this->buscaComplementoTipos($complemento_tipo_id)." ".$complemento_valor;
				} else {
					return $complemento_valor;
				}
			} else {
				return $complemento;
			}
		
		} else {
			return "";
		}
	
	}

	function buscaComplementoTipos($tipo_id = null) {
		
		$tipos["a"] = "Apto";
		$tipos["l"] = "Loja";
		$tipos["s"] = "Sala";
		$tipos["c"] = "Casa";
		$tipos["o"] = "Outro";
		
		return $tipo_id ? ($tipo_id != "o" ? $tipos[$tipo_id] : "") : $tipos;
		
	}

	function buscaComplementoInstrucao($dados_cobranca) {
		
		if ($dados_cobranca["valor_bonificacao"]) {
			return "Conceder desconto de ".$this->formataDinheiro($dados_cobranca["valor_bonificacao"])." para pagamento até {$dados_cobranca["data_vencimento"]}.";
		} else {
			return "";
		}
	
	}

	function buscaInstrucao($dados_cobranca) {
		
		if ($dados_cobranca["valor_bonificacao"]) {
			$instrucao[] = "CONCEDER DESCONTO DE ".$this->formataDinheiro($dados_cobranca["valor_bonificacao"])." PARA PAGAMENTO ATÉ {$dados_cobranca["data_vencimento"]}.";
		}
		$instrucao[] = "APÓS O VENCIMENTO, COBRAR MULTA DE 2% + MORA DE 1% AO MÊS.";
		$instrucao[] = "SUJEITO A PROTESTO APÓS 10 DIAS DO VENCIMENTO.";
		$instrucao[] = "DEPÓSITO EM CONTA CORRENTE OU PAG. PARCIAL DO TÍTULO, NÃO IMPLICA NA LIQUIDAÇÃO DO MESMO.";
		$instrucao[] = "DÚVIDAS, ENTRE EM CONTATO COM O SETOR FINANCEIRO.";
		
		return implode(" ", $instrucao);
		
	}

	function buscaObservacao($dados_cobranca) {
		
		if ($dados_cobranca["origem_id"] == 3) {
			
			if ($dados_cobranca["valor_bonificacao"]) {
				$instrucao[] = "APÓS {$dados_cobranca["dias_bloqueio"]} DIAS DO VENCIMENTO SERÁ INTERROMPIDO O SINAL DE INTERNET.";
				$instrucao[] = "SERÁ COBRADO TAXA DE RELIGAMENTO DE R$ 10,00.";
				$instrucao[] = "CONFORME LEI 5474 DE 18/07/1968.";
			} else {
				$instrucao[] = "APÓS {$dados_cobranca["dias_bloqueio"]} DIAS DO VENCIMENTO SERÁ INTERROMPIDO O SINAL DE INTERNET.";
				$instrucao[] = "SERÁ COBRADO TAXA DE RELIGAMENTO DE R$10,00.";
				$instrucao[] = "EM CASO DE OPTAR PELO PAGAMENTO NO PROVEDOR A REFERIDA TAXA NÃO SERÁ COBRADA.";
			}
		
		} else {
			$instrucao[] = "";
		}
		
		return implode(" ", $instrucao);
		
	}

	function buscaCobrancaCompetencia($cobranca_id) {
		
		$competencias = array();
		
		$query = "
			SELECT DISTINCT
				DATE_FORMAT(cod_competencia_inicio, '%m/%Y') AS competencia
			FROM
				tab_cobrancas_detalhes
			WHERE
				coa_id = $cobranca_id AND
				ass_id IS NOT NULL";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$competencias[] = $dados["competencia"];
		}
		
		return ($competencias && count($competencias) == 1) ? $this->formataData($competencias[0]) : "";
	
	}

	function formataData($vencimento) {
		
		$meses = array (1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr", 5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez");
		$vencimento = explode("/", $vencimento);
		
		return $meses[intval($vencimento[0])]." / ".$vencimento[1];
	
	}

	function buscaValores($cobranca_id) {
		
		$valores = array();
		
		$query = "
			SELECT DISTINCT
				tab_cobrancas_detalhes.cod_id AS id,
				tab_cobrancas_detalhes.cod_valor_final + IFNULL((SELECT SUM(detalhes_filhos.cod_valor_final) FROM tab_cobrancas_detalhes AS detalhes_filhos WHERE detalhes_filhos.cod_pai = tab_cobrancas_detalhes.cod_id), 0) AS valor,
				tab_assinaturas.pla_id AS plano_id,
				tab_ajustes_financeiros.afc_id AS ajuste_categoria_id
			FROM
				tab_cobrancas_detalhes
				LEFT OUTER JOIN tab_ajustes_financeiros ON (tab_ajustes_financeiros.ajf_id = tab_cobrancas_detalhes.ajf_id)
				LEFT OUTER JOIN tab_assinaturas ON (tab_assinaturas.ass_id = tab_cobrancas_detalhes.ass_id)
			WHERE
				tab_cobrancas_detalhes.coa_id = $cobranca_id AND
				tab_cobrancas_detalhes.cod_pai IS NULL";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados_cobranca = $this->bancodedados->resultadoArray($query)) != false) {
			
			if ($dados_cobranca["plano_id"]) {
				
				switch ($dados_cobranca["plano_id"]) {
					case 729:
						$valores["valor_plano_life_line"] += $dados_cobranca["valor"];
						break;
					case 730:
						$valores["valor_plano_start"] += $dados_cobranca["valor"];
						break;
					case 731:
						$valores["valor_plano_top"] += $dados_cobranca["valor"];
						break;
					case 732:
						$valores["valor_plano_premium"] += $dados_cobranca["valor"];
						break;
					case 733:
						$valores["valor_hbo"] += $dados_cobranca["valor"];
						break;
					case 734:
						$valores["valor_fox"] += $dados_cobranca["valor"];
						break;
					case 735:
						$valores["valor_adulto"] += $dados_cobranca["valor"];
						break;
					case 736:
						$valores["valor_decoder"] += $dados_cobranca["valor"];
						break;
				}
			
			} elseif ($dados_cobranca["ajuste_categoria_id"]) {
				
				switch ($dados_cobranca["ajuste_categoria_id"]) {
					case 10:
						$valores["valor_multa"] += $dados_cobranca["valor"];
						break;
					case 19:
						$valores["valor_adesao"] += $dados_cobranca["valor"];
						break;
				}
				
			}
			
			$valores["valor_total"] += $dados_cobranca["valor"];

		}
		
		return $valores;
	
	}

	#############################
	### Funções de Formatação ###
	#############################

	function formataDinheiro($valor, $moeda = 'R$') {
		
		if ($valor) {
			
			$sinal = $valor < 0 ? "- " : "";
			$valor = intval(preg_replace("/[^0-9]/", "", $valor));
			$valor = number_format($valor / 100, 2, ",", ".");
			
			if ($moeda) $valor = $moeda." ".$valor;
			
			return $sinal.$valor;
			
		} else {
			return false;
		}
	
	}

	function corrigeNomeCliente($nome) {
		
		$nome = strtolower($nome);
		$nome = str_replace(array("ç", "ñ"), array("c", "n"),$nome);
		$nome = preg_replace("/[ÁÀÂÄáàâãäª]/", "a", $nome);
		$nome = preg_replace("/[ÉÈÊËéèêë]/", "e", $nome);
		$nome = preg_replace("/[ÍÌÎÏíìîï]/", "i", $nome);
		$nome = preg_replace("/[ÓÒÔÕÖóòôõö°º]/", "o", $nome);
		$nome = preg_replace("/[ÚÙÛÜúùûü]/", "u", $nome);
		$nome = preg_replace("/[^a-z0-9 ]/", " ", $nome);
		$nome = preg_replace("/[\r\n\t ]+/", " ", $nome);
		$nome = strtoupper($nome);
		$nome = trim($nome);
		
		return $nome;
	
	}

	function ordenaMultiArray($array, $campos) {
		
		if (is_array($array) && sizeof($array)) {
			
			$array_ordenacao = array();
			
			while (($array_ordenacao_atual = current($array)) != false) {
				
				for ($i=0; $i < sizeof($campos); $i++) {
					$chave[] = $array_ordenacao_atual[$campos[$i]];
				}
				
				$chave = implode(",", $chave);
				
				if (!$array_ordenacao[$chave]) {
					$array_ordenacao[$chave."_".key($array)] = key($array);
				}
				
				unset($chave);
				next($array);
				
			}
			
			ksort($array_ordenacao);
			
			while (is_int($array_id = current($array_ordenacao))) {
				$array_final[] = $array[$array_id];
				next($array_ordenacao);
			}
			
			return $array_final;
			
		}
	
	}

	######################
	### exportaItau400 ###
	######################

	function exportaItau400($dados_lote, $dados_cobranca = null, $salvar_para_envio = false) {
			
		// Parametros
		$parametros_formato = $dados_lote ? $dados_lote["parametros"] : $dados_cobranca["parametros"];
		
		// Conteudo
		$conteudo = array();
		
		// Cabeçalho
		$conteudo[] = $this->geraCabecalhoItau400($parametros_formato["empresa"]["valor"], $parametros_formato["agencia"]["valor"], $parametros_formato["conta"]["valor"], $parametros_formato["dac"]["valor"]);
		
		// Busca Cobranças Lote
		$cobrancas = $this->buscaCobrancasLote($dados_lote ? $dados_lote["id"] : null, $dados_cobranca ? $dados_cobranca["id"] : null);
		$sequencial = 2;
		for ($i = 0; $i < count($cobrancas); $i++) {
			$conteudo[] = $this->geraRegistroBoletoItau400($sequencial, $parametros_formato["agencia"]["valor"], $parametros_formato["conta"]["valor"], $parametros_formato["dac"]["valor"], $cobrancas[$i]);
			$sequencial++;
			$conteudo[] = $this->geraRegistroInstrucoesItau400($sequencial, $cobrancas[$i]);
			$sequencial++;
		}
		
		// Rodapé
		$conteudo[] = sprintf("%01.1s%-393.393s%06.6s", 9, "", $sequencial);

		$conteudo = implode("\r\n", $conteudo);

		if ($dados_lote) {
			$arquivo = sprintf("L%05s.REM", $dados_lote["id"]);
		} else {
			$arquivo = sprintf("C%07s.REM", $dados_cobranca["id"]);
		}

		if ($salvar_para_envio) {
			if (file_put_contents("/tmp/$arquivo", $conteudo)) {
				return rename("/tmp/$arquivo", "/usr/local/www/apache24/data/intranet/itau/Edi7WebCli/CATELEC-001-P/enviar/$arquivo");
			}
			return false;
		} else {
			// Header
			header("Content-type: application/octet-stream;");
			header("Content-Disposition: attachment; filename=$arquivo;");
			print $conteudo;
			print "\r\n";
			exit;
		}
		
	}

	function geraCabecalhoItau400($empresa, $agencia, $conta, $dac) {
		
		$zeros = 0;
		$brancos = "";
		$tipo_registro = 0;
		$operacao = 1;
		$remessa = "REMESSA";
		$tipo_servico = 1;
		$servico = "COBRANCA";
		$codigo_banco = 341;
		$nome_banco = "BANCO ITAU SA";
		$data_geracao = date("dmy");

		$format = "%01.1s%01.1s%-7.7s%02.2s%-15.15s%04.4s%02.2s%05.5s%01.1s%-8.8s%-30.30s%03.3s%-15.15s%06.6s%-294.294s%06.6s";
		
		return sprintf($format, $tipo_registro, $operacao, $remessa, $tipo_servico, $servico, $agencia, $zeros, $conta, $dac, $brancos, $empresa, $codigo_banco, $nome_banco, $data_geracao, $brancos, 1);
	
	}

	function geraRegistroBoletoItau400($sequencial, $agencia, $conta, $dac_conta, $dados_cobranca) {
		
		$zeros = 0;
		$brancos = "";
		$tipo_registro = 6;
		$layout = 1;
		$carteira = 173;
		$nosso_numero = $dados_cobranca["id"];
		$dac_nosso_numero = $this->calculaDACNossoNumero($agencia, $conta, $carteira, $nosso_numero);
		$codigo_moeda = 0;
		$moeda = "";
		$valor = $dados_cobranca["valor_original"];
		$seu_numero = $dados_cobranca["codigo"];
		$vencimento = $dados_cobranca["vencimento"];
		$especie = 17;
		$aceite = "N";
		$emissao = $dados_cobranca["emissao"];
		$tipo_inscricao = $dados_cobranca["tipo_pessoa"];
		$numero_inscricao = $dados_cobranca["cpf_cnpj"];
		$nome = $dados_cobranca["nome"];
		$logradouro = $dados_cobranca["endereco"]["endereco"];
		$bairro = $dados_cobranca["endereco"]["bairro"];
		$cep = $dados_cobranca["endereco"]["cep"];
		$cidade = $dados_cobranca["endereco"]["cidade"];
		$estado = $dados_cobranca["endereco"]["estado"];
		$sacador = "";
		$local_pagamento_1 = "ATE O VENCIMENTO, PAGUE PREFERENCIALMENTE NO ITAU";
		$local_pagamento_2 = "APOS O VENCIMENTO, PAGUE SOMENTE NO ITAU";
		$tipo_inscricao_sacador = "";
		$numero_inscricao_sacador = "";  	

		$format = "%01.1s%01.1s%04.4s%02.2s%05.5s%01.1s%03.3s%08.8s%01.1s%01.1s%-4.4s%013.13s%10.10s%06.6s%-2.2s%-1.1s%06.6s%02.2s%015.15s%-30.30s%-9.9s%-40.40s%-12.12s%08.8s%-15.15s%-2.2s%-30.30s%-4.4s%-55.55s%-55.55s%02.2s%015.15s%-31.31s%06.6s";

		return sprintf($format, $tipo_registro, $layout, $agencia, $zeros, $conta, $dac_conta, $carteira, $nosso_numero, $dac_nosso_numero, $codigo_moeda, $moeda, $valor, $seu_numero, $vencimento, $especie, $aceite, $emissao, $tipo_inscricao, $numero_inscricao, $nome, $brancos, $logradouro, $bairro, $cep, $cidade, $estado, $sacador, $brancos, $local_pagamento_1, $local_pagamento_2, $tipo_inscricao_sacador, $numero_inscricao_sacador, $brancos, $sequencial);
	
	}

	function geraRegistroInstrucoesItau400($sequencial, $dados_cobranca) {
		
		$brancos = "";
		$tipo_registro = 6;
		$layout = 2;
		$linhas = array();

		if ($dados_cobranca["valor_bonificacao"]) {
			$linhas[] = "ATE {$dados_cobranca["vencimento_formatado"]}, CONCEDER DESCONTO DE R$ {$dados_cobranca["valor_bonificacao"]}";
		}
		
		$linhas[] = "APOS {$dados_cobranca["vencimento_formatado"]}, COBRAR MULTA DE 2% + MORA DE 1% AO MES";
		$linhas[] = "SUJEITO A PROTESTO APOS 10 DIAS DO VENCIMENTO";
		$linhas[] = "DEPOSITO OU PAGAMENTO PARCIAL, NAO IMPLICA NA LIQUIDACAO DO TITULO";

		while (count($linhas) < 5) {
			$linhas[] = "";
		}

		$format = "%01.1s%01.1s%-69.69s%-69.69s%-69.69s%-69.69s%-69.69s%-47.47s%06.6s";

		return sprintf($format, $tipo_registro, $layout, $linhas[0], $linhas[1], $linhas[2], $linhas[3], $linhas[4], $brancos, $sequencial);
	
	}

	function calculaDACNossoNumero($agencia, $conta, $carteira, $nosso_numero) {
		
		$numero = sprintf("%04s%05s%03s%08s", $agencia, $conta, $carteira, $nosso_numero);
		$multiplicacao = "";
		
		for ($i = 0; $i < strlen($numero); $i++) {
			$multiplicacao .= $numero[$i] * (($i % 2) + 1);
		}
		
		$soma = 0;
		
		for ($i = 0; $i < strlen($multiplicacao); $i++) {
			$soma += $multiplicacao[$i];
		}
		
		$resto = $soma % 10;
		
		return $resto == 0 ? 0 : 10 - $resto;
	
	}

	function buscaCobrancasLote($lote_id, $cobranca_id = null) {
		
		$cobrancas = array();
		
		$query = "
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_clientes.cli_id AS cliente_id,
				tab_cobrancas.coa_codigo AS codigo,
				DATE_FORMAT(tab_cobrancas.coa_data_geracao, '%d%m%y') AS emissao,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d%m%y') AS vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS vencimento_formatado,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_valor_original AS valor_original,
				tab_clientes.cli_nome AS nome,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_clientes.clt_id AS tipo_pessoa
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
			WHERE
				".($cobranca_id ? "tab_cobrancas.coa_id = $cobranca_id" : "tab_cobrancas.col_id = $lote_id")." AND
				tab_cobrancas.cot_id = 1
			ORDER BY
				tab_cobrancas.coa_valor_original";
		
		$query = $this->bancodedados->query($query);
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["nome"] = $this->limpaNome($dados["nome"]);
			$dados["cpf_cnpj"] = preg_replace("/[^0-9]/", "", $dados["cpf_cnpj"]);
			$dados["endereco"] = $this->buscaEnderecoCobranca($dados["cliente_id"]);

			if ($dados["valor_bonificacao"]) {
				$dados["valor_bonificacao"] = number_format($dados["valor_bonificacao"] / 100, 2, ",", ".");
			}

			$cobrancas[] = $dados;
		
		}
		
		return $cobrancas;
		
	}

	function buscaEnderecoCobranca($cliente_id) {
		
		$query = "
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes_cobranca_dados.ccd_numero)) AS numero,
				tab_clientes_cobranca_dados.ccd_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				tab_ceps.cep_exportacao AS cep
			FROM
				tab_clientes_cobranca_dados
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_condominios_blocos.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes_cobranca_dados.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes_cobranca_dados.cli_id = $cliente_id";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
		} else {
			$dados = $this->buscaEndereco($cliente_id);
		}

		$complemento = array();

		if ($dados["numero"]) $complemento[] = $dados["numero"];
		
		if ($dados["condominio"]) {
			$apartamento = array();
			if ($dados["complemento"]) $apartamento[] = $dados["complemento"];
			if ($dados["bloco"] && $dados["bloco"] != "Único") $apartamento[] = $dados["bloco"];
			if ($apartamento) {
				$complemento[] = implode("/", $apartamento);
			}
		} elseif ($dados["complemento"]) {
			$complemento[] = $dados["complemento"];
		}

		$dados["logradouro"] = $this->limpaNome($dados["logradouro"]);
		$dados["complemento"] = $this->limpaNome(implode(", ", $complemento));

		$dados["logradouro"] = " {$dados["logradouro"]} ";

		$dados["logradouro"] = preg_replace("/ RUA /", " R ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ AVENIDA /", " AV ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ RODOVIA /", " ROD ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ SERVIDAO /", " SRV ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ ESTRADA /", " EST ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ LOTEAMENTO /", " LOT ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ BALENARIO /", " BAL ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ ALAMEDA /", " AL ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ TRAVESSA /", " TV ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ JARDIJM /", " JD ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ PARQUE /", " PQ ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ PRACA /", " PC ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ FAZENDA /", " FAZ ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ CONJUNTO /", " CONJ ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ CONDOMINIO /", " COND ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ QUADRA /", " Q ", $dados["logradouro"]);

		$dados["logradouro"] = preg_replace("/ PRESIDENTE /", " PRES ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ SENADOR /", " SEN ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ GOVERNADOR /", " GOV ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ PREFEITO /", " PREF ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ DEPUTADO /", " DEP ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ VEREADOR /", " VER ", $dados["logradouro"]);

		$dados["logradouro"] = preg_replace("/ CAPITAO /", " CAP ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ CORONOEL /", " CEL ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ MARECHAL /", " MAL ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ MAJOR /", " MAJ ", $dados["logradouro"]);

		$dados["logradouro"] = preg_replace("/ DOUTOR /", " DR ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ DOUTORA /", " DRA ", $dados["logradouro"]);

		$dados["logradouro"] = preg_replace("/ PROFESSORA? /", " PROF ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ ENGENHEIR[OA] /", " ENG ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ DELEGAD[OA] /", " DEL ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ DESEMBARGADOR /", " DES ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ REVERENDO /", " REV ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ PADRE /", " PE ", $dados["logradouro"]);
		$dados["logradouro"] = preg_replace("/ PROCURADOR /", " PROC ", $dados["logradouro"]);

		$dados["logradouro"] = trim(preg_replace("/[ ]{2,}/", " ", $dados["logradouro"]));

		$dados["complemento"] = preg_replace("/ QUADRA /", " Q ", $dados["complemento"]);
		$dados["complemento"] = preg_replace("/ LOTE? /", " L ", $dados["complemento"]);
		$dados["complemento"] = preg_replace("/ - /", ", ", $dados["complemento"]);

		if (strlen($dados["logradouro"]) + strlen($dados["complemento"]) + 2 > 40 && strlen($dados["complemento"]) > 0) {
			$comprimento_logradouro = 40 - 2 - strlen($dados["complemento"]);
			if ($comprimento_logradouro < 10) {
				$comprimento_logradouro = 10;
			}
			$dados["logradouro"] = $this->abreviaNome($dados["logradouro"], $comprimento_logradouro);
		}

		$endereco = array();
		
		$endereco[] = $dados["logradouro"];
		if ($dados["complemento"]) {
			$endereco[] = $dados["complemento"];
		}

		$dados["endereco"] = implode(", ", $endereco);
		$dados["bairro"] = $this->limpaNome($dados["bairro"]);
		$dados["cidade"] = $this->limpaNome($dados["cidade"]);

		return $dados;
	
	}

	function abreviaNome($nome, $comprimento) {
		
		$nome = explode(" ", $nome);
		for ($i = 1; $i < count($nome) - 1; $i++) {
			if (strlen($nome[$i]) > 4) {
				$nome[$i] = substr($nome[$i], 0, 1);
			}
		}
		
		$nome = implode(" ", $nome);
		if (strlen($nome) > $comprimento) {
			$nome = trim(substr($nome, 0, $comprimento));
		}
		
		return $nome;
	
	}

	function buscaEndereco($cliente_id) {
		
		$query = "
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes.cli_numero)) AS numero,
				tab_clientes.cli_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				tab_ceps.cep_exportacao AS cep
			FROM
				tab_clientes
				LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
				LEFT OUTER JOIN tab_condominios ON (tab_condominios.con_id = tab_clientes.con_id)
				INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
				INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
				INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
				INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
				INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
			WHERE
				tab_clientes.cli_id = $cliente_id";
		
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);
		
		if ($dados) {
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
			return $dados;
		}
	
	}

	function corrigeComplementoCliente($complemento) {
		
		$complementos_tipos["a"] = "Ap ";
		$complementos_tipos["l"] = "Lj ";
		$complementos_tipos["s"] = "Sl ";
		$complementos_tipos["c"] = "Casa ";
		$complementos_tipos["o"] = "";
		
		if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
			return $complementos_tipos[$complemento[0]].substr($complemento, 1, strlen($complemento));
		} else {
			return false;
	}
	
	}

	function limpaNome($nome) {
		
		$nome = trim(preg_replace("/[\r\n\t ]+/", " ", mb_strtolower($nome)));
		$nome = preg_replace("/[ç]/", "c", $nome);
		$nome = preg_replace("/[ñ]/", "n", $nome);
		$nome = preg_replace("/[áàâãäª]/", "a", $nome);
		$nome = preg_replace("/[éèêë]/", "e", $nome);
		$nome = preg_replace("/[íìîï]/", "i", $nome);
		$nome = preg_replace("/[óòôõö°º]/", "o", $nome);
		$nome = preg_replace("/[úùûü]/", "u", $nome);
		$nome = preg_replace("/[^a-zA-Z0-9,. \\-\\/]/", "", $nome);
		
		return strtoupper($nome);
	
	}

}

?>
