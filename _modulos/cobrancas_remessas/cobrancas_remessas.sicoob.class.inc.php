<?

class cobrancas_remessas_sicoob {

	var $bancodedados;
	var $dados;
	var $cobrancas = array();
	var $cobrancas_total = 0;
	var $conteudo = array();

	function __construct($bancodedados, $dados) {
		
		$this->bancodedados = $bancodedados;
		$this->dados = $dados;
		
		$this->buscaCobrancasRemessa();
		
	}
	
	function gerar() {
		
		$this->geraHeaderArquivo();
		
		$this->geraHeaderLote();
		
		$this->geraRegistros();
		
		$this->geraTraillerLote();
		
		$this->geraTraillerArquivo();
		
		return implode("\r\n", $this->conteudo);
		
	}
	
	function geraHeaderArquivo() {
		
		// 001-003 003 Código do Sicoob na Compensação: "756"
		$linha .= "756";
		  	
		// 004-007 004 Lote de Serviço: "0000"
		$linha .= "0000";
		
		// 008-008 001 Tipo de Registro: "0"
		$linha .= "0";
		
		// 009-017 009 Uso Exclusivo FEBRABAN / CNAB: Brancos
		$linha .= sprintf("% 9s", " ");
		
		// 018-018 001 Tipo de Inscrição da Empresa: '1'  =  CPF, '2'  =  CGC / CNPJ
		$linha .= "2";
		
		// 019-032 014 Número de Inscrição da Empresa
		$linha .= sprintf("%014.14s", $this->dados["parametros"]["cnpj"]["valor"]);
		
		// 033-052 020 Código do Convênio no Sicoob: Brancos
		$linha .= sprintf("% 20s", " ");
		
		// 053-057 005 Prefixo da Cooperativa: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%05.5s", $this->dados["parametros"]["agencia"]["valor"]);
		
		// 058-058 001 Dígito Verificador do Prefixo: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%01.1s", $this->modulo11($this->dados["parametros"]["agencia"]["valor"]));
		
		// 059-070 012 Conta Corrente: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%012.12s", $this->dados["parametros"]["conta_corrente"]["valor"]);
		
		// 071-071 001 Dígito Verificador da Conta: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%01.1s", $this->modulo11($this->dados["parametros"]["conta_corrente"]["valor"]));
		
		// 072-072 001 Dígito Verificador da Ag/Conta: Brancos
		$linha .= " ";
		
		// 073-102 030 Nome da Empresa
		$linha .= sprintf("%-30.30s", $this->dados["parametros"]["empresa"]["valor"]);
		
		// 103-132 030 Nome do Banco: SICOOB
		$linha .= sprintf("%-30.30s", "SICOOB");
		
		// 133	142	010 Uso Exclusivo FEBRABAN / CNAB: Brancos
		$linha .= sprintf("% 10s", " ");
		
		// 143	143	001 Código Remessa / Retorno: "1"
		$linha .= "1";
		
		// 144	151	008 Data de Geração do Arquivo
		$linha .= date("dmY", strtotime($this->dados["data_hora_geracao"]));
		
		// 152	157	006 Hora de Geração do Arquivo
		$linha .= date("His", strtotime($this->dados["data_hora_geracao"]));
		
		// 158	163	006 Número Seqüencial do Arquivo: Número seqüencial adotado e controlado pelo responsável pela geração do arquivo para ordenar a disposição dos arquivos encaminhados. Evoluir um número seqüencial a cada header de arquivo.
		$linha .= sprintf("%06.6s", $this->dados["nsa"]);
		
		// 164	166	003 No da Versão do Layout do Arquivo: "081"
		$linha .= "081";
		
		// 167	171	005 Densidade de Gravação do Arquivo: "00000"
		$linha .= "00000";
		
		// 172	191	020 Para Uso Reservado do Banco: Brancos
		$linha .= sprintf("% 20s", " ");
		
		// 192	211	020 Para Uso Reservado da Empresa: Brancos
		$linha .= sprintf("% 20s", " ");
		
		// 212	240	029 Uso Exclusivo FEBRABAN / CNAB: Brancos
		$linha .= sprintf("% 29s", " ");
		
		$this->conteudo[] = $linha;
		
	}
	
	function geraHeaderLote() {
		
		// 001-003 003 Código do Banco na Compensação: "756"
		$linha .= "756";
		  	
		// 004-007 004 Preencher com '0001' para o primeiro lote do arquivo. Para os demais: número do lote anterior acrescido de 1.
		$linha .= "0001";
		
		// 008-008 001 Tipo de Registro: "1"
		$linha .= "1";
		
		// 009-009 001 Tipo de Operação: "R"
		$linha .= "R";
		
		// 010-011 002 Tipo de Serviço: "01"
		$linha .= "01";
		
		// 012-013 002 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= "  ";
		
		// 014-016 003 Nº da Versão do Layout do Lote: "040"
		$linha .= "040";
		
		// 017-017 001 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= " ";
		
		// 018-018 001 "Tipo de Inscrição da Empresa: '1'  =  CPF, '2'  =  CGC / CNPJ"
		$linha .= "2";
		
		// 019-033 015 Nº de Inscrição da Empresa
		$linha .= sprintf("%015.15s", $this->dados["parametros"]["cnpj"]["valor"]);
		
		// 034-053 020 Código do Convênio no Banco: Brancos
		$linha .= sprintf("% 20s", " ");
		
		// 054-058 005 Prefixo da Cooperativa: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%05.5s", $this->dados["parametros"]["agencia"]["valor"]);
		
		// 059-059 001 Dígito Verificador do Prefixo: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%01.1s", $this->modulo11($this->dados["parametros"]["agencia"]["valor"]));
		
		// 060-071 012 Conta Corrente: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%012.12s", $this->dados["parametros"]["conta_corrente"]["valor"]);
		
		// 072-072 001 Dígito Verificador da Conta: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%01.1s", $this->modulo11($this->dados["parametros"]["conta_corrente"]["valor"]));
		
		// 073-073 001 Dígito Verificador da Ag/Conta: Brancos
		$linha .= " ";
		
		// 074-103 030 Nome da Empresa
		$linha .= sprintf("%-30.30s", $this->dados["parametros"]["empresa"]["valor"]);
		
		// 104-143 040 Mensagem 1: Texto referente a mensagens que serão impressas em todos os boletos referentes ao mesmo lote
		$linha .= sprintf("%-40.40s", "");
		  	
		// 144-183 040 Mensagem 2: Texto referente a mensagens que serão impressas em todos os boletos referentes ao mesmo lote.
		$linha .= sprintf("%-40.40s", "");
		  	
		// 184-191 008 Número Remessa/Retorno: Número adotado e controlado pelo responsável pela geração magnética dos dados contidos no arquivo para identificar a seqüência de envio ou devolução do arquivo entre o Beneficiário e o Sicoob.
		$linha .= sprintf("%08.8s", $this->dados["nsa"]);
		
		// 192-199 008 Data de Gravação Remessa/Retorno
		$linha .= date("dmY", strtotime($this->dados["data_hora_geracao"]));
		
		// 200-207 008 Data do Crédito: "00000000"
		$linha .= "00000000";
		
		// 208-40 033 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("% 33s", " ");
		
		$this->conteudo[] = $linha;
		
	}
	
	function geraRegistros() {
		
		for ($i = 0; $i < count($this->cobrancas); $i++) {
			
			$this->geraDetalheP($this->cobrancas[$i]);
			$this->geraDetalheQ($this->cobrancas[$i]);
			
			if ($this->cobrancas[$i]["remessa_tipo"] == "REGISTRO") {
				$this->geraDetalheR($this->cobrancas[$i]);
				$this->geraDetalheS($this->cobrancas[$i]);
			}
			
		}
		
	}
	
	function geraDetalheP($dados_cobranca) {
		
		// 001	003	003 Código do Banco na Compensação: "756"
		$linha  = "756";
		
		// 004	007	004 Preencher com '0001' para o primeiro lote do arquivo. Para os demais: número do lote anterior acrescido de 1.
		$linha .= "0001";
		
		// 008	008	001 Tipo de Registro: "3"
		$linha .= "3";
		
		// 009	013	005 Nº Sequencial do Registro no Lote: Número adotado e controlado pelo responsável pela geração magnética dos dados contidos no arquivo, para identificar a seqüência de registros encaminhados no lote. Deve ser inicializado sempre em '1', em cada novo lote.
		$linha .= sprintf("%05.5s", count($this->conteudo) - 1);
		
		// 014	014	001 Cód. Segmento do Registro Detalhe: "P"
		$linha .= "P";
		
		// 015	015	001 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= " ";
		
		// 016	017	002 Código de Movimento Remessa: '01'  =  Entrada de Títulos , '02' = Solicitacao de Baixa 	
		$linha .= $dados_cobranca["remessa_tipo"] == "REGISTRO" ? "01" : "02";
		
		// 018	022	005 Prefixo da Cooperativa: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%05.5s", $this->dados["parametros"]["agencia"]["valor"]);
		
		// 023	023	001 Dígito Verificador do Prefixo: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%01.1s", $this->modulo11($this->dados["parametros"]["agencia"]["valor"]));
		
		// 024	035	012 Conta Corrente: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%012.12s", $this->dados["parametros"]["conta_corrente"]["valor"]);
		
		// 036	036	001 Dígito Verificador da Conta: vide planilha "Capa" deste arquivo
		$linha .= sprintf("%01.1s", $this->modulo11($this->dados["parametros"]["conta_corrente"]["valor"]));
		
		// 037	037	001 Dígito Verificador da Ag/Conta: Brancos
		$linha .= " ";
		
		// 038	057	020 Nosso número
		
		// NumTitulo
		$linha .= sprintf("%010.10s", preg_replace("/[^0-9]/", "", $dados_cobranca["parametros"]["boleto"]["nosso_numero"]));
		
		// Parcela
		$linha .= "01";
		
		// Modalidade
		$linha .= "01";
		
		// Tipo Formulário
		$linha .= "4";
		
		// Brancos
		$linha .= "     ";
		
		// 058	058	001 Código da Carteira: vide planilha "Capa" deste arquivo
		$linha .= "1";
		
		// 059	059	001 Forma de Cadastr. do Título no Banco: "0"
		$linha .= "0";
		
		// 060	060	001 Tipo de Documento: Brancos
		$linha .= " ";
		
		// 061	061	001 Identificação da Emissão do Boleto: '1' = Sicoob Emite, '2' = Beneficiário Emite
		$linha .= "2";
		
		// 062	062	001 Identificação da Distribuição do Boleto: '1' = Sicoob Distribui, '2' = Beneficiário Distribui
		$linha .= "2";
		
		// 063	077	015 Nº do Documento
		$linha .= sprintf("%-15.15s", $dados_cobranca["documento"]);
		
		// 078	085	008 Data de Vencimento do Título
		$linha .= $dados_cobranca["data_vencimento"];
		
		// 086	100	013 002 Valor Nominal do Título
		$linha .= sprintf("%015.15s", $dados_cobranca["valor_nominal"]);
		
		// 101	105	005 Agência Encarregada da Cobrança: "00000"
		$linha .= "00000";
		
		// 106	106	001 Dígito Verificador da Agência: Brancos
		$linha .= " ";
		
		// 107	108	002 Espécie de Título '99' = Outros
		$linha .= $dados_cobranca["dias_protesto"] ? "04" : "99";
		
		// 109	109	001 Aceite: 'N'
		$linha .= "N";
		
		// 110	117	008 Data da Emissão do Título
		$linha .= $dados_cobranca["data_emissao"];
		
		// 118	118	001 Código do Juros de Mora: '0'  =  Isento, '1'  =  Valor por Dia, '2'  =  Taxa Mensal
		$linha .= "2";
		
		// 119	126	008 Data do Juros de Mora
		$linha .= sprintf("%08.8s", $dados_cobranca["data_multa"]);
		
		// 127	141	013 002 Juros de Mora por Dia/Taxa ao Mês: Valor = R$ ao dia, Taxa = % ao mês
		$linha .= sprintf("%015.15s", "100");
		
		// 142	142	001 Código do Desconto: '1' = Valor Fixo Até a Data Informada, '2'  =  Percentual Até a Data Informada
		$linha .= $dados_cobranca["valor_bonificacao"] ? "1" : "0";
		
		// 143	150	008 Data do Desconto 1
		$linha .= $dados_cobranca["valor_bonificacao"] ? $dados_cobranca["data_vencimento"] : "00000000";
		
		// 151	165	013 002 Valor/Percentual a ser Concedido
		$linha .= sprintf("%015.15s", $dados_cobranca["valor_bonificacao"]);
		
		// 166	180	013 002 Valor do IOF a ser Recolhido
		$linha .= sprintf("%015.15s", "0");
		
		// 181	195	013 002 Valor do Abatimento
		$linha .= sprintf("%015.15s", "0");
		
		// 196	220	025 Identificação do Título na Empresa: Campo destinado para uso do Beneficiário para identificação do Título.
		$linha .= sprintf("%010.10s", $dados_cobranca["cliente_id"]);
		$linha .= sprintf("%015.15s", $dados_cobranca["id"]);
		
		// 221	221	001 Código para Protesto: "1"
		$linha .= $dados_cobranca["dias_protesto"] > 0 ? "1" : "3";
		
		// 222	223	002 Número de Dias Corridos para Protesto
		$linha .= sprintf("%02.2s", $dados_cobranca["dias_protesto"]);
		
		// 224	224	001 Código para Baixa/Devolução: "0"
		$linha .= "0";
		
		// 225	227	003 Número de Dias para Baixa/Devolução: Brancos
		$linha .= "   ";
		
		// 228	229	002 Código da Moeda: '02' =  Dólar Americano Comercial (Venda), '09' = Real
		$linha .= "09";
		
		// 230	239	010 Nº do Contrato da Operação de Créd.: "0000000000"
		$linha .= "0000000000";
		
		// 240	240	001 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= " ";
		
		$this->conteudo[] = $linha;
		
	}
	
	function geraDetalheQ($dados_cobranca) {
 	
		// 001	003	003 Código do Banco na Compensação: "756"
		$linha  = "756";
		
		// 004	007	004 Preencher com '0001' para o primeiro lote do arquivo. Para os demais: número do lote anterior acrescido de 1.
		$linha .= "0001";
		
		// 008	008	001 Tipo de Registro: "3"
		$linha .= "3";
		
		// 009	013	005 Nº Sequencial do Registro no Lote: Número adotado e controlado pelo responsável pela geração magnética dos dados contidos no arquivo, para identificar a seqüência de registros encaminhados no lote. Deve ser inicializado sempre em '1', em cada novo lote.
		$linha .= sprintf("%05.5s", count($this->conteudo) - 1);
		
		// 014	014	001 Cód. Segmento do Registro Detalhe: "Q"
		$linha .= "Q";
		
		// 015	015	001 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= " ";
		
		// 016	017	002 Código de Movimento Remessa: '01'  =  Entrada de Títulos , '02' = Solicitacao de Baixa 	
		$linha .= $dados_cobranca["remessa_tipo"] == "REGISTRO" ? "01" : "02";
		
		// 018	018	001 Tipo de Inscrição Pagador: '1'  =  CPF, '2'  =  CGC / CNPJ
		$linha .= $dados_cobranca["cliente_tipo"];
		
		// 019	033	015 Número de Inscrição
		$linha .= sprintf("%015.15s", $dados_cobranca["cliente_cpf_cnpj"]);
		
		// 034	073	040 Nome
		$linha .= sprintf("%-40.40s", $dados_cobranca["cliente_nome"]);
		
		// 074	113	040 Endereço
		$linha .= sprintf("%-40.40s", $dados_cobranca["endereco"]["endereco"]);
		
		// 114	128	015 Bairro
		$linha .= sprintf("%-15.15s", $dados_cobranca["endereco"]["bairro"]);
		
		// 129	136	008 CEP
		$linha .= sprintf("%08.8s", $dados_cobranca["endereco"]["cep"]);
		
		// 137	151	015 Cidade
		$linha .= sprintf("%-15.15s", $dados_cobranca["endereco"]["cidade"]);
		
		// 152	153	002 UF
		$linha .= sprintf("%-2.2s", $dados_cobranca["endereco"]["uf"]);
		
		// 154	154	001 Tipo de Inscrição Sacador Avalista: '1'  =  CPF, '2'  =  CGC / CNPJ 
		//$linha .= sprintf("%01.1s", "2");
		$linha .= sprintf("%01.1s", "0");
		
		// 155	169	015 Número de Inscrição
		//$linha .= sprintf("%015.15s", "23911953000107");
		$linha .= sprintf("%015.15s", "0");
		
		// 170	209	040 Nome do Sacador/Avalista
		//$linha .= sprintf("%-40.40s", "OTT AND IPTV LTDA - EPP");
		$linha .= sprintf("%-40.40s", "");
		
		// 210	212	003 Cód. Bco. Corresp. na Compensação			
		$linha .= sprintf("%03.3s", "0");
		
		// 213	232	020 Nosso Nº no Banco Correspondente			
		$linha .= sprintf("%-20.20s", "");
		
		// 233	240	008 CNAB
		$linha .= sprintf("%-8.8s", "");
		
		$this->conteudo[] = $linha;
		
	}
	
	function geraDetalheR($dados_cobranca) {
 	
		// 001	003	003 Código do Banco na Compensação: "756"
		$linha  = "756";
		
		// 004	007	004 Preencher com '0001' para o primeiro lote do arquivo. Para os demais: número do lote anterior acrescido de 1.
		$linha .= "0001";
		
		// 008	008	001 Tipo de Registro: "3"
		$linha .= "3";
		
		// 009	013	005 Nº Sequencial do Registro no Lote: Número adotado e controlado pelo responsável pela geração magnética dos dados contidos no arquivo, para identificar a seqüência de registros encaminhados no lote. Deve ser inicializado sempre em '1', em cada novo lote.
		$linha .= sprintf("%05.5s", count($this->conteudo) - 1);
		
		// 014	014	001 Cód. Segmento do Registro Detalhe: "R"
		$linha .= "R";
		
		// 015	015	001 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= " ";
		
		// 016	017	002 Código de Movimento Remessa: '01'  =  Entrada de Títulos , '02' = Solicitacao de Baixa 	
		$linha .= $dados_cobranca["remessa_tipo"] == "REGISTRO" ? "01" : "02";
		
		// 018	018	001 Código do Desconto 2: '1'  =  Valor Fixo Até a Data Informada, '2'  =  Percentual Até a Data Informada
		$linha .= "0";
		
		// 019	026	008 Data do Desconto 2
		$linha .= sprintf("%08.8s", "0");
		
		// 027	041	013	002 Valor/Percentual a ser Concedido
		$linha .= sprintf("%015.15s", "0");
		
		// 042	042	001 Código do Desconto 3: '0', '1'  =  Valor Fixo Até a Data Informada, '2'  =  Percentual Até a Data Informada
		$linha .= "0";
		
		// 043	050	008 Data do Desconto 3: "00000000"
		$linha .= sprintf("%08.8s", "0");
		
		// 051	065	013	002 Valor/Percentual a ser Concedido: "000000000000000"
		$linha .= sprintf("%015.15s", "0");
		
		// 066	066	001 Código da Multa: '0'  =  Isento, '1'  =  Valor Fixo, '2'  =  Percentual
		$linha .= "2";
		
		// 067	074	008 Data da Multa
		$linha .= sprintf("%08.8s", $dados_cobranca["data_multa"]);
		  	  	
		// 075	089	013	002 Valor/Percentual a Ser Aplicado
		$linha .= sprintf("%015.15s", "200");
		
		// 090	099	010 Informação ao Pagador: Brancos
		$linha .= sprintf("%-10.10s", "");
		
		// 100	139	040 Mensagem 3: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
		$linha .= sprintf("%-40.40s", "");
		  	  	
		// 140	179	040 Mensagem 4: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
		$linha .= sprintf("%-40.40s", "");
		  	  	
		// 180	199	020 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-20.20s", "");
		
		// 200	207	008 Cód. Ocor. do Pagador: "00000000"
		$linha .= "00000000";
		
		// 208	210	003 Cód. do Banco na Conta do Débito: "000"
		$linha .= "000";
		
		// 211	215	005 Código da Agência do Débito: "00000"
		$linha .= "00000";
		
		// 216	216	001 Dígito Verificador da Agência: Brancos
		$linha .= " ";
		
		// 217	228	012 Conta Corrente para Débito: "000000000000"
		$linha .= "000000000000";
		
		// 229	229	001 Dígito Verificador da Conta: Brancos
		$linha .= " ";
		
		// 230	230	001 Dígito Verificador Ag/Conta: Brancos
		$linha .= " ";
		
		// 231	231	001 Aviso para Débito Automático: "0"
		$linha .= "0";
		
		// 232	240	009 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-9.9s", "");
		
		$this->conteudo[] = $linha;
		
	}
	
	function geraDetalheS($dados_cobranca) {
 	
		// 001	003	003 Código do Banco na Compensação: "756"
		$linha  = "756";
		
		// 004	007	004 Preencher com '0001' para o primeiro lote do arquivo. Para os demais: número do lote anterior acrescido de 1.
		$linha .= "0001";
		
		// 008	008	001 Tipo de Registro: "3"
		$linha .= "3";
		
		// 009	013	005 Nº Sequencial do Registro no Lote: Número adotado e controlado pelo responsável pela geração magnética dos dados contidos no arquivo, para identificar a seqüência de registros encaminhados no lote. Deve ser inicializado sempre em '1', em cada novo lote.
		$linha .= sprintf("%05.5s", count($this->conteudo) - 1);
		
		// 014	014	001 Cód. Segmento do Registro Detalhe: "S"
		$linha .= "S";
		
		// 015	015	001 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= " ";
		
		// 016	017	002 Código de Movimento Remessa: '01'  =  Entrada de Títulos , '02' = Solicitacao de Baixa 	
		$linha .= $dados_cobranca["remessa_tipo"] == "REGISTRO" ? "01" : "02";
		
		// 018	018	001	Tipo de Impressão	'3' = Corpo de Instruções da Ficha de Compensação do Boleto
		$linha .= "3";
		
		if ($dados_cobranca["valor_bonificacao"]) {
		
			// 019	058	040	Mensagem 5: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "DESCONTO DE R$ ".$this->formataDinheiro($dados_cobranca["valor_bonificacao"])." ATÉ {$dados_cobranca["data_vencimento_formatado"]}.");
			
			// 059	098	040	Mensagem 6: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "APÓS VENC MULTA DE 2% + MORA DE 1%/MÊS.");
			  	
			// 099	138	040	Mensagem 7: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			if ($dados_cobranca["dias_protesto"]) {
				$linha .= sprintf("%-40.40s", "SUJEITO A PROTESTO APÓS {$dados_cobranca["dias_protesto"]} DIAS DO VENC.");
			} else {
				$linha .= sprintf("%-40.40s", "SUJEITO A PROTESTO APÓS 10 DIAS DO VENC.");
			}
			  	
			// 139	178	040	Mensagem 8: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "DEPÓSITO OU PAGAMENTO PARCIAL DO TÍTULO,");
			  	
			// 179	218	040	Mensagem 9: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "NÃO IMPLICA NA LIQUIDAÇÃO DO MESMO.");
		
		} else {
		
			// 019	058	040	Mensagem 5: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "APÓS VENC MULTA DE 2% + MORA DE 1%/MÊS.");
			
			// 059	098	040	Mensagem 6: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			if ($dados_cobranca["dias_protesto"]) {
				$linha .= sprintf("%-40.40s", "SUJEITO A PROTESTO APÓS {$dados_cobranca["dias_protesto"]} DIAS DO VENC.");
			} else {
				$linha .= sprintf("%-40.40s", "SUJEITO A PROTESTO APÓS 10 DIAS DO VENC.");
			}
			  	
			// 099	138	040	Mensagem 7: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "DEPÓSITO OU PAGAMENTO PARCIAL DO TÍTULO,");
			  	
			// 139	178	040	Mensagem 8: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "NÃO IMPLICA NA LIQUIDAÇÃO DO MESMO.");
			  	
			// 179	218	040	Mensagem 9: Texto de observações destinado ao envio de mensagens livres, a serem impressas no campo de instruções da ficha de compensação do bloqueto.
			$linha .= sprintf("%-40.40s", "");
				
		}
	
		// 219	240	022	Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-22.22s", "");
		
		$this->conteudo[] = $linha;
	
	}
	
	function geraTraillerLote() {
		
		// 001	003	003 Código do Banco na Compensação: "756"
		$linha  = "756";
		
		// 004	007	004 Lote de Serviço: Número seqüencial para identificar univocamente um lote de serviço. Criado e controlado pelo responsável pela geração magnética dos dados contidos no arquivo.
		$linha .= "0001";
		
		// 008	008	001 Tipo de Registro: "5"
		$linha .= "5";
		
		// 009	017	009 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-9.9s", "");
		
		// 018	023	006 Quantidade de Registros no Lote
		$linha .= sprintf("%06.6s", count($this->conteudo));
		
		// 024	029	006 Totalização da Cobrança Simples - Quantidade de Títulos em Cobrança
		$linha .= sprintf("%06.6s", count($this->cobrancas));
		
		// 030	046	015	002 Totalização da Cobrança Simples - Valor Total dosTítulos em Carteiras
		$linha .= sprintf("%017.17s", $this->cobrancas_total);
		
		// 047	052	006 Totalização da Cobrança Vinculada - Quantidade de Títulos em Cobrança
		$linha .= sprintf("%06.6s", 0);
		
		// 053	069	015	002 Totalização da Cobrança Vinculada - Valor Total dosTítulos em Carteiras
		$linha .= sprintf("%017.17s", 0);
		
		// 070	075	006 Totalização da Cobrança Caucionada - Quantidade de Títulos em Cobrança
		$linha .= sprintf("%06.6s", 0);
		
		// 076	092	015	002 Totalização da Cobrança Caucionada - Valor Total dosTítulos em Carteiras
		$linha .= sprintf("%017.17s", 0);
		
		// 093	098	006 Totalização da Cobrança Descontada - Quantidade de Títulos em Cobrança
		$linha .= sprintf("%06.6s", 0);
		
		// 099	115	015	002 Totalização da Cobrança Descontada - Valor Total dosTítulos em Carteiras
		$linha .= sprintf("%017.17s", 0);
		
		// 116	123	008 Número do Aviso de Lançamento: Brancos
		$linha .= sprintf("%-8.8s", "");
		
		// 124	240	117 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-117.117s", "");
		
		$this->conteudo[] = $linha;
		
	}
	
	function geraTraillerArquivo() {
		
		// 001	003	003 Código do Banco na Compensação: "756"
		$linha  = "756";
		
		// 004	007	004 Preencher com '9999'.
		$linha .= "9999";
		
		// 008	008	001 Tipo de Registro: "9"
		$linha .= "9";
		
		// 009	017	009 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-9.9s", "");
		
		// 018	023	006 Quantidade de Lotes do Arquivo
		$linha .= "000001";
		
		// 024	029	006 Quantidade de Registros do Arquivo
		$linha .= sprintf("%06.6s", count($this->conteudo) + 1);
		
		// 030	035	006 Qtde de Contas p/ Conc. (Lotes): "000000"
		$linha .= "000000";
		
		// 036	240	205 Uso Exclusivo FEBRABAN/CNAB: Brancos
		$linha .= sprintf("%-205.205s", "");
		
		$this->conteudo[] = $linha;
		
	}

	function modulo11($valor) {
		
		$valor = "{$valor}";
		
		$peso = 2;
		$soma = 0;
		
		for ($i = strlen($valor) - 1; $i >= 0; $i--) {
			$soma += $valor[$i] * $peso;
			$peso = ($peso < 9) ? $peso + 1 : 2;
		}
		
		$resto = $soma % 11;
		
		return $resto <= 1 ? 0 : 11 - $resto;
	
	}

	function buscaCobrancasRemessa() {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_cobrancas.coa_id AS id,
				tab_cobrancas.coa_codigo AS documento,
				DATE_FORMAT(tab_cobrancas.coa_data_geracao, '%d%m%Y') AS data_emissao,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d%m%Y') AS data_vencimento,
				DATE_FORMAT(tab_cobrancas.coa_data_vencimento, '%d/%m/%Y') AS data_vencimento_formatado,
				DATE_FORMAT(DATE_ADD(tab_cobrancas.coa_data_vencimento, INTERVAL 1 DAY), '%d%m%Y') AS data_multa,
				tab_cobrancas.coa_valor_original AS valor_nominal,
				tab_cobrancas.coa_valor_bonificacao AS valor_bonificacao,
				tab_cobrancas.coa_dias_protesto AS dias_protesto,
				tab_cobrancas.coa_parametros AS parametros,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.clt_id AS cliente_tipo,
				tab_clientes.cli_cpf_cnpj AS cliente_cpf_cnpj,
				tab_clientes.cli_nome AS cliente_nome,
				tab_cobrancas_remessas_itens.cri_tipo AS remessa_tipo
			FROM
				tab_cobrancas
				INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
				INNER JOIN tab_cobrancas_remessas_itens ON (tab_cobrancas.coa_id = tab_cobrancas_remessas_itens.coa_id)
			WHERE
				tab_cobrancas_remessas_itens.cbr_id = {$this->dados["id"]}
			ORDER BY
				tab_cobrancas_remessas_itens.cri_tipo");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			
			$dados["parametros"] = unserialize($dados["parametros"]);
			$dados["cliente_cpf_cnpj"] = preg_replace("/[^0-9]/", "", $dados["cliente_cpf_cnpj"]);
			$dados["endereco"] = $this->buscaEnderecoInstalacao($dados["cliente_id"]);
			$dados["cliente_nome"] = $this->limpaNome($dados["cliente_nome"]);
			$this->cobrancas_total += $dados["valor_nominal"];
			
			$this->cobrancas[] = $dados;
			
		}
	
	}  
		
	function buscaEnderecoInstalacao($cliente_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				tab_logradouros.log_nome AS endereco,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes.cli_numero)) AS numero,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS uf,
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
				tab_clientes.cli_id = $cliente_id");
		
		$endereco = $this->bancodedados->resultadoArray($query);
		
		$endereco["endereco"] = $this->abreviaLogradouro($this->limpaNome($endereco["endereco"]));
		$endereco["bairro"] = $this->limpaNome($endereco["bairro"]);
		$endereco["cidade"] = $this->limpaNome($endereco["cidade"]);
	
		if ($endereco["numero"]) {
			
			if (strlen($endereco["endereco"]) + strlen($endereco["numero"]) + 1 > 40) {
				$endereco["endereco"] = substr($endereco["endereco"], 0, 40 - 1 - strlen($endereco["numero"]));
			}
			
			$endereco["endereco"] = "{$endereco["endereco"]} {$endereco["numero"]}";
			
		}
	
		return $endereco;
		
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
	
	function abreviaLogradouro($logradouro) {
		
		$logradouro = " $logradouro ";
	
		$logradouro = preg_replace("/ RUA /", " R ", $logradouro);
		$logradouro = preg_replace("/ AVENIDA /", " AV ", $logradouro);
		$logradouro = preg_replace("/ RODOVIA /", " ROD ", $logradouro);
		$logradouro = preg_replace("/ SERVIDAO /", " SRV ", $logradouro);
		$logradouro = preg_replace("/ ESTRADA /", " EST ", $logradouro);
		$logradouro = preg_replace("/ LOTEAMENTO /", " LOT ", $logradouro);
		$logradouro = preg_replace("/ BALENARIO /", " BAL ", $logradouro);
		$logradouro = preg_replace("/ ALAMEDA /", " AL ", $logradouro);
		$logradouro = preg_replace("/ TRAVESSA /", " TV ", $logradouro);
		$logradouro = preg_replace("/ JARDIJM /", " JD ", $logradouro);
		$logradouro = preg_replace("/ PARQUE /", " PQ ", $logradouro);
		$logradouro = preg_replace("/ PRACA /", " PC ", $logradouro);
		$logradouro = preg_replace("/ FAZENDA /", " FAZ ", $logradouro);
		$logradouro = preg_replace("/ CONJUNTO /", " CONJ ", $logradouro);
		$logradouro = preg_replace("/ CONDOMINIO /", " COND ", $logradouro);
		$logradouro = preg_replace("/ QUADRA /", " Q ", $logradouro);
		
		$logradouro = preg_replace("/ PRESIDENTE /", " PRES ", $logradouro);
		$logradouro = preg_replace("/ SENADOR /", " SEN ", $logradouro);
		$logradouro = preg_replace("/ GOVERNADOR /", " GOV ", $logradouro);
		$logradouro = preg_replace("/ PREFEITO /", " PREF ", $logradouro);
		$logradouro = preg_replace("/ DEPUTADO /", " DEP ", $logradouro);
		$logradouro = preg_replace("/ VEREADOR /", " VER ", $logradouro);
		
		$logradouro = preg_replace("/ CAPITAO /", " CAP ", $logradouro);
		$logradouro = preg_replace("/ CORONOEL /", " CEL ", $logradouro);
		$logradouro = preg_replace("/ MARECHAL /", " MAL ", $logradouro);
		$logradouro = preg_replace("/ MAJOR /", " MAJ ", $logradouro);
		
		$logradouro = preg_replace("/ DOUTOR /", " DR ", $logradouro);
		$logradouro = preg_replace("/ DOUTORA /", " DRA ", $logradouro);
		
		$logradouro = preg_replace("/ PROFESSORA? /", " PROF ", $logradouro);
		$logradouro = preg_replace("/ ENGENHEIR[OA] /", " ENG ", $logradouro);
		$logradouro = preg_replace("/ DELEGAD[OA] /", " DEL ", $logradouro);
		$logradouro = preg_replace("/ DESEMBARGADOR /", " DES ", $logradouro);
		$logradouro = preg_replace("/ REVERENDO /", " REV ", $logradouro);
		$logradouro = preg_replace("/ PADRE /", " PE ", $logradouro);
		$logradouro = preg_replace("/ PROCURADOR /", " PROC ", $logradouro);
		
		return trim(preg_replace("/[ ]{2,}/", " ", $logradouro));
		
	}

	function formataDinheiro($valor, $moeda = 'R$') {
		
		$valor = preg_replace("/[^0-9]/", "", intval($valor));
		$valor = number_format($valor / 100, 2, ",", ".");
		
		if ($moeda) $valor = $moeda." ".$valor;
		
		return $valor;
		
	}
	
}

?>
