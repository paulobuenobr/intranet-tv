<?

set_time_limit(0);
require_once '_modulos/cobrancas/cobrancas.boletos_pdf.class.inc.php';

include_once DIR_GLOBAL."phpqrcode/qrlib.php";

class cobrancas_boletos_pdf_api extends cobrancas_boletos_pdf {

	const PAGINA_LARGURA = 210;
	const PAGINA_ALTURA = 297;
	const MARGEM_SUPERIOR = 5;
	const MARGEM_LATERAL = 15;
	const BARRA_PRETA = 1;
	const BARRA_BRANCA = 0;
	const BARRA_FINA = 0;
	const BARRA_LARGA = 1;
	const INICIO_ENDERECO = 33;
	const INICIO_BOLETO = 201;
	const BARRA_CEP_LARGURA = 0.5;
	const BARRA_CEP_ESPACAMENTO = 0.6;
	const BARRA_CEP_CURTA = 1.4;
	const BARRA_CEP_ALTA = 3.0;
	const LINHA_GROSSA = 0.30;
	const LINHA_FINA = 0.15;

	
	private $bancodedados;
	private $pdf;
	private $dados = array();
	private $fontes = array();
	private $endereco_altura = 26;
	private $posicao_barra_cep = 50;
	private $posicao_barra_cep_atual = 0;
	private $codigo_barras_posicao = 0;
	private $largura_linha = 0;
	private $largura_colunas = array(
		0 => 23,
		1 => 17,
		2 => 17,
		3 => 17,
		4 => 23,
		5 => 46
	);
	
	private $boletos_pdf;
	
	
	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;
		
		$this->boletos_pdf = new cobrancas_boletos_pdf($bancodedados);

	}
	
	function gerarLote($cobranca_lote_id) {
		
		$this->pdf = PDF_new();
		
		$this->iniciaDocumento();
		$this->carregaFontes();
		
		$cobrancas_lote = $this->buscaCobrancasLote($cobranca_lote_id);
		
		for ($i = 0; $i < count($cobrancas_lote); $i++) {
			
			$this->carregaDados($cobrancas_lote[$i]);
			
			$this->desenhaCobranca(false);
			
		}
		
		$this->finalizaDocumento();
		
	}

	function gerarBoleto($cobranca_id) {
		$this->pdf = PDF_new();

		$this->iniciaDocumento();
		$this->carregaFontes();
		
		$this->boletos_pdf->carregaDados($cobranca_id);
		$this->dados = $this->boletos_pdf->get_dados();
		
		$this->desenhaCobranca();
		
		$this->finalizaDocumento();

	}
	
	function exportar($attachment = true) {

		$conteudo = $this->conteudo();

		header("Content-type: application/pdf;");

		if ($attachment) {
			header("Content-disposition: attachment; filename=Boleto-{$this->dados["codigo"]}.pdf;");
		} else {
			header("Content-disposition: inline; filename=Boleto-{$this->dados["codigo"]}.pdf;");
		}

		header("Content-length: ".strlen($conteudo));

		print $conteudo;

		die();

	}

	function conteudo() {

		return PDF_get_buffer($this->pdf);

	}
	
	function formataDinheiro($valor, $moeda = "R$", $exibir_nulo = false) {

		$valor_formatado = preg_replace("/[^0-9]/", "", intval($valor, 10));
		if (!$valor_formatado && !$exibir_nulo) return false;
		$valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
		if ($moeda) $valor_formatado = "$moeda $valor_formatado";
		if ($valor < 0) $valor_formatado = "($valor_formatado)";

		return $valor_formatado;

	}

	function corrigeComplementoCliente($complemento) {

		$complementos_tipos = array(
			"a" => "Apto ",
			"l" => "Loja ",
			"s" => "Sala ",
			"c" => "Casa ",
			"o" => ""
		);

		if ($complemento && array_key_exists($complemento[0], $complementos_tipos)) {
			return $complementos_tipos[$complemento[0]].substr($complemento, 1, strlen($complemento));
		}

	}

	function mm2Point($value) {

		return $value / 25.4 * 72;

	}

	function point2MM($value) {

		return $value * 25.4 / 72;

	}
	
	function desenhaCapa() {
		
		$this->iniciaPagina();
		
		$this->escreveRemetente();
		$this->desenhaParaUsoCorreios();
		$this->desenhaLinhasCapa();
		
		$this->escreveEnderecoCliente(162, true);
		
		
		$this->finalizaPagina();
		
	}
	
	function desenhaCobranca($desenhar_cerrilhado = true) {

		if ($this->dados["qrcode"]) {
			$this->endereco_altura = 45;
		} else {
			$this->endereco_altura = 26;
		}
		
		$this->iniciaPagina();
		
		$this->desenhaDestaques();
		$this->escreveDadosEmpresa();
		$this->desenhaLinhas($desenhar_cerrilhado);
		$this->escreveLabels();
		
		$this->escreveDetalhamento();
		$this->escreveEnderecoCliente($this->boletos_pdf::INICIO_ENDERECO);
		$this->desenhaQrCode();
		$this->escrveDadosCabecalho();
		$this->escrveDadosBoleto();
		$this->escreveDadosBancarios();
		$this->desenhaCodigoBarras();
		
		$this->finalizaPagina();
		
	}

	function desenhaQrCode() {

		if ($this->dados["qrcode"]) {
			
			$file = "/tmp/qrcode-{$this->dados["codigo"]}.png";
			
			$qrcode = QRcode::png($this->dados["qrcode"], $file, QR_ECLEVEL_M);
			
			$this->insereImagem("png", $file, self::PAGINA_LARGURA - self::MARGEM_LATERAL - 36, self::ENDERECO_INICIO + 1, 35, 35);

			$this->insereImagem("png", DIR_RAIZ."imagens".DIRECTORY_SEPARATOR."pix.png", self::PAGINA_LARGURA - self::MARGEM_LATERAL - 37 - 25, self::ENDERECO_INICIO + 27.5, 25, 8.15);
			
			$this->setFonte("Helvetica-Bold", 9);

			$this->escreveLinha("Pagamento PIX", self::MARGEM_LATERAL + 2, self::ENDERECO_INICIO + $this->endereco_altura - 17.5 + 3);

			$this->setFonte("Helvetica", 9);
			
			$this->escreveLinha("Scaneie o QR Code ao lado ou copie e cole o código abaixo:", self::MARGEM_LATERAL + 2, self::ENDERECO_INICIO + $this->endereco_altura - 17.5 + 4 + 3);

			$this->setFonte("Courier", 5);

			$this->escreveTexto($this->dados["qrcode"], self::MARGEM_LATERAL + 2, self::ENDERECO_INICIO + $this->endereco_altura - 5, self::PAGINA_LARGURA - (self::MARGEM_LATERAL * 2) - 4, 8, "center");

			unlink($file);

		}
		
	}

	function carregaFontes() {
	    
		$this->fontes["Courier"] = PDF_findfont($this->pdf, "Courier", "host", 0);
		$this->fontes["Courier-Bold"] = PDF_findfont($this->pdf, "Courier-Bold", "host", 0);
		$this->fontes["Helvetica"] = PDF_findfont($this->pdf, "Helvetica", "host", 0);
		$this->fontes["Helvetica-Bold"] = PDF_findfont($this->pdf, "Helvetica-Bold", "host", 0);

	}

	function iniciaDocumento() {

		PDF_begin_document($this->pdf, "", "destination={type=fitwindow} openmode=none pagelayout=singlepage viewerpreferences={printscaling=none}");
		
		PDF_set_info($this->pdf, "Author", "Olé Internet");
		PDF_set_info($this->pdf, "Title", "Boleto {$this->dados["codigo"]}");
	}

	function finalizaDocumento() {

		PDF_end_document($this->pdf, "");

	}

	function iniciaPagina() {

		PDF_begin_page_ext($this->pdf, $this->mm2Point($this->boletos_pdf::PAGINA_LARGURA), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA), "");

	}

	function finalizaPagina() {

		PDF_end_page($this->pdf);

	}

	function desenhaParaUsoCorreios() {
		
		$this->setFonte("Helvetica-Bold", 10);
		
		$this->escreveLinhaCentro("PARA USO DOS CORREIOS", $this->boletos_pdf::PAGINA_LARGURA / 2, $this->boletos_pdf::PAGINA_ALTURA - 36.5);
		
		$this->setFonte("Helvetica", 10);
		
		$altura_quadrado = 3.875;
		
		$motivos = array(
			array(
				"Mudou-se",
				"EndereÃ§o insuficiente",
				"NÃ£o existe nÂº indicado",
				"Falecido"
			),
			array(
				"Desconhecido",
				"Recusado",
				"Ausente",
				"NÃ£o procurado"
			),
		);
		
		for ($i = 0; $i < 4; $i++) {
		    $this->escreveLinha($motivos[0][$i], $this->boletos_pdf::MARGEM_LATERAL + 5 + 2 + 4 + 2, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + 3 + (2 + $altura_quadrado) * $i);
		}
		
		for ($i = 0; $i < 4; $i++) {
		    $this->escreveLinha($motivos[1][$i], $this->boletos_pdf::MARGEM_LATERAL + 5 + 50 + 4 + 2, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + 3 + (2 + $altura_quadrado) * $i);
		}

		$this->setFonte("Helvetica", 7);
		
		$this->escreveLinha("Data:", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 85 - 5 + 1, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 3);
		$this->escreveLinha("Reintegrado ao ServiÃ§o Postal em:", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 85 - 5 + 1, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 12.75 + 3);
		
		$this->escreveLinha("Rubrica do responsÃ¡vel:", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 40 - 5 + 1, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 3);
		$this->escreveLinha("MatrÃ­cula:", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 40 - 5 + 1, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 12.75 + 3);
		
	}
	
	function escreveRemetente() {
		
		$this->setFonte("Helvetica-Bold", 16);
		
		$this->escreveLinhaCentro("OLÉ‰ TELECOM", $this->boletos_pdf::PAGINA_LARGURA / 2, $this->boletos_pdf::PAGINA_ALTURA - 80);
		
		$this->setFonte("Helvetica", 10);
		
		$this->escreveLinhaCentro("Fone: (48) 3028-0000", $this->boletos_pdf::PAGINA_LARGURA / 2, $this->boletos_pdf::PAGINA_ALTURA - 73);
		
		$this->setFonte("Helvetica-Bold", 10);
		
		$this->escreveLinha("ENDEREÇO FÍSICO:", 30, $this->boletos_pdf::PAGINA_ALTURA - 60);
		$this->escreveLinha("ENDEREÇO PARA CORRESPONDÊNCIA:", 115, $this->boletos_pdf::PAGINA_ALTURA - 60);
		
		$this->setFonte("Helvetica", 10);
		
		$this->escreveLinha("Av. Acioni de Souza Filho, 925", 30, $this->boletos_pdf::PAGINA_ALTURA - 60 + 5);
		$this->escreveLinha("88103-790 - Praia Comprida - SÃ£o JosÃ© - SC", 30, $this->boletos_pdf::PAGINA_ALTURA - 60 + 10);
		
		$this->escreveLinha("Caixa Postal 20425", 115, $this->boletos_pdf::PAGINA_ALTURA - 60 + 5);
		$this->escreveLinha("88102-971 - São José - SC", 115, $this->boletos_pdf::PAGINA_ALTURA - 60 + 10);
		
	}
	
	function escreveDadosEmpresa() {

	    //print_r($_SERVER);".DIRECTORY_SEPARATOR."D:/xampp/htdocs/intranet/
	    
	    $this->insereImagem("png",  DIR_RAIZ. "/imagens".DIRECTORY_SEPARATOR."logo_ole_tv_retangular.png", $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::MARGEM_SUPERIOR, 56.86, 12);
		
		$this->setFonte("Helvetica-Bold", 7);
		
		$this->escreveTexto("OTT AND IPTV LTDA EPP", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 120, $this->boletos_pdf::MARGEM_SUPERIOR, 120, 5, "right");
		
		$this->setFonte("Helvetica", 7);

		$this->escreveTexto( "CNPJ: 23.911.953/0001-07 - Inscrição Estadual: 25.785.542-4", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 120, $this->boletos_pdf::MARGEM_SUPERIOR + 3, 120, 30, "right");
		$this->escreveTexto("Rua Lídio Antônio de Matos, 358, Sala 02A, Pav 02, Kobrasol - São José / SC - 88102-460", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 120, $this->boletos_pdf::MARGEM_SUPERIOR + 6, 120, 30, "right");
		$this->escreveTexto("Telefone: (48) 4042-6262 - E-mail: financeiro@oletv.net.br", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 120, $this->boletos_pdf::MARGEM_SUPERIOR + 9, 120, 30, "right");
		
	}

	function escreveDetalhamento() {
		
		$this->setFonte("Helvetica-Bold", 7);
		
		$posicao = $this->boletos_pdf::INICIO_ENDERECO + 28 + 6 + 4;
		
		$this->escreveLinha("Descrição", $this->boletos_pdf::MARGEM_LATERAL + 2, $posicao);
		$this->escreveLinha("Período de Utilização", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 76, $posicao);
		$this->escreveLinhaDireita("Valor Original", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 22, $posicao);
		$this->escreveLinhaDireita("Valor Final", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $posicao);
		
		$posicao += 4;

		$linhas = 1;
		$i = 1;
		
		$this->setFonte("Helvetica", 7);
		//$this->dados = $this->boletos_pdf
		
		foreach ($this->dados["detalhamento"] as $detalhamento) {
			
			if ($this->dados["detalhamento"] <= 31 || $i <= 30 || $i == count($this->dados["detalhamento"])) {

				if ($i == count($this->dados["detalhamento"])) {
					$this->setFonte("Helvetica-Bold", 7);
				}
				
				if ($linhas % 2 == 1) {
					PDF_setcolor($this->pdf, "fill", "cmyk", 0.04, 0.02, 0.00, 0.00);
					$this->desenhaQuadrado($this->boletos_pdf::MARGEM_LATERAL + 1, $posicao - 3, $this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2) - 2, 4);
				}
				
				PDF_setcolor($this->pdf, "fill", "cmyk", 1.00, 1.00, 1.00, 1.00);
				
				if ($i == 30) {
					$this->escreveLinha("...", $this->boletos_pdf::MARGEM_LATERAL + 2 + ($detalhamento["filho"] ? 5 : 0), $posicao);
					$this->escreveLinha("...", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 76, $posicao);
					$this->escreveLinhaDireita("...", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 22, $posicao);
					$this->escreveLinhaDireita("...", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $posicao);
				} else {
					$this->escreveLinha($detalhamento["descricao"], $this->boletos_pdf::MARGEM_LATERAL + 2 + ($detalhamento["filho"] ? 5 : 0), $posicao);
					$this->escreveLinha($detalhamento["periodo"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 76, $posicao);
					$this->escreveLinhaDireita($detalhamento["valor_original"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 22, $posicao);
					$this->escreveLinhaDireita($detalhamento["valor_final"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $posicao);
				}
				
				$posicao += 4;
				$linhas++;
			
			}
			
			$i++;

		}

	}
	
	function escreveLabels() {
		
		$this->setFonte("Helvetica-Bold", 13);
		
		$this->escreveTexto("Detalhamento", $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 2, $this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2), 6, "center");
		
		$this->setFonte("Helvetica", 5);
		
		// Cabeï¿½alho

		$this->setFonte("Helvetica", 7);
		
		$labels_cabecalho = array("Nº da Fatura", "Competência", "Vencimento", "Valor");
		$largura = ($this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {
			$this->escreveLinha($labels_cabecalho[$i], $this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i) + 1, $this->boletos_pdf::MARGEM_SUPERIOR + 15 + 3);
		}
		
		$this->setFonte("Helvetica", 5);
		
		// Boleto
		
		$this->escreveLinha("Local de Pagamento", $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 10);
		$this->escreveLinha("Beneficiário", $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 16);
		$this->escreveLinha("CNPJ", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 16);
		$this->escreveLinha("Data de Emissão", $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 22);
		$this->escreveLinha("Número do Documento", $this->boletos_pdf::MARGEM_LATERAL + $this->largura_colunas[0] + 1, $this->boletos_pdf::INICIO_BOLETO + 22);
		$this->escreveLinha("Espécie do Documento", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->largura_colunas[1] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 22);
		$this->escreveLinha("Aceite", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 22);
		$this->escreveLinha("Data do Processamento", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 22);
		$this->escreveLinha("Uso do Banco", $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 28);
		$this->escreveLinha("Carteira", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->largura_colunas[1] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 28);
		$this->escreveLinha("Espécie Moeda", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 28);
		$this->escreveLinha("Quantidade Moeda", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 28);
		$this->escreveLinha("Valor Moeda", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 28);
		$this->escreveLinha("Instruções", $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 34);

		$this->escreveLinha("Competência", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 10);
		$this->escreveLinha("Vencimento", $this->boletos_pdf::PAGINA_LARGURA - ($this->largura_colunas[5] / 2) - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 10);
		$this->escreveLinha("Agência / Código do Beneficiário", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 16);
		$this->escreveLinha("Nosso Número", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 22);
		$this->escreveLinha("(=) Valor do Documento / Parcela", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 28);
		$this->escreveLinha("(-) Desconto / Abatimento", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 34);
		$this->escreveLinha("(-) Outras Deduções", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 40);
		$this->escreveLinha("(+) Mora / Multa", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 46);
		$this->escreveLinha("(+) Outros Acréscimos", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 52);
		$this->escreveLinha("(=) Valor Cobrado", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 58);
		
		$this->escreveLinha("Pagador", $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 64);
		$this->escreveLinha("Sacador / Avalista", (($this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2)) / 2) + $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 64);
		
		$this->escreveLinhaDireita("Autenticação Mecânica / Ficha de Compensação", $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 1, $this->boletos_pdf::INICIO_BOLETO + 76.5);
		
	}
	
	function escreveEnderecoCliente($inicio, $insere_destinatario = false) {

		if ($insere_destinatario) {
			
			$this->setFonte("Helvetica-Bold", 10);
			
			$this->escreveLinha("DESTINATÁRIO:", 50, $inicio + 1);
			
		}
		
		$this->desenhaCodigoBarrasCEP($inicio);
		
		$this->setFonte("Courier-Bold", 9);
		
		$this->escreveLinha(sprintf("%-70.70s", mb_strtoupper($this->dados["cliente_nome"], "iso-8859-1")), 50, $inicio + 12);
		
		$this->setFonte("Courier", 9);
		
		$this->escreveTexto(mb_strtoupper($this->dados["endereco"], "iso-8859-1"), 50, $inicio + 12, 110, 20, "left");

	}
	
	function desenhaCodigoBarrasCEP($inicio) {
		
		$tabela = array(
			0 => "11000",
			1 => "00011",
			2 => "00101",
			3 => "00110",
			4 => "01001",
			5 => "01010",
			6 => "01100",
			7 => "10001",
			8 => "10010",
			9 => "10100"
		);
		
		$cep = sprintf("%02s", preg_replace("/[^0-9]/", "", $this->dados["cep"]));
		$soma = 0;
		
		for ($i = 0; $i < 8; $i++) {
			$soma += $cep[$i];
		}
		
		$dv = $soma % 10;
		$dv = $dv == 0 ? 0 : 10 - $dv;
		$cep = "{$cep}{$dv}";
		
		$this->posicao_barra_cep_atual = 0;
		
		$this->desenhaBarraCEP($inicio, "1");
		
		for ($i = 0; $i < 9; $i++) {
			for ($j = 0; $j < 5; $j++) {
				$this->desenhaBarraCEP($inicio, $tabela[$i][$j]);
			}
		}
		
		$this->desenhaBarraCEP($inicio, "1");
		
	}
	
	function desenhaBarraCEP($inicio, $tipo) {
		
		if ($tipo == "0") {
			$offset_y = $this->boletos_pdf::BARRA_CEP_ALTA - $this->boletos_pdf::BARRA_CEP_CURTA;
			$altura = $this->boletos_pdf::BARRA_CEP_CURTA;
		} else {
			$offset_y = 0;
			$altura = $this->boletos_pdf::BARRA_CEP_ALTA;
		}
		
		$this->desenhaQuadrado($this->posicao_barra_cep + $this->posicao_barra_cep_atual, $inicio + 4 + $offset_y, $this->boletos_pdf::BARRA_CEP_LARGURA, $altura);
		
		$this->posicao_barra_cep_atual += $this->boletos_pdf::BARRA_CEP_LARGURA + $this->boletos_pdf::BARRA_CEP_ESPACAMENTO;
		
	}
	
	function escrveDadosCabecalho() {

		$this->setFonte("Helvetica-Bold", 13);
		
		$dados_cabecalho = array($this->dados["codigo"], $this->dados["competencia"], $this->dados["data_vencimento"], $this->dados["valor_original"]);
		$largura = ($this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {
			$this->escreveLinhaCentro($dados_cabecalho[$i], $this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i) + ($largura / 2), $this->boletos_pdf::MARGEM_SUPERIOR + 14.5 + 3 + 6);
		}

	}
	
	function escrveDadosBoleto() {

		$this->setFonte("Courier", 9);
		
		$this->escreveLinha("PAGÁVEL EM QUALQUER AGÊNCIA BANCÁRIA", $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 13);
		$this->escreveLinhaDireita($this->dados["competencia"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - ($this->largura_colunas[5] / 2) - 2, $this->boletos_pdf::INICIO_BOLETO + 13);
		
		$this->setFonte("Courier-Bold", 9);
		
		$this->escreveLinhaDireita($this->dados["data_vencimento"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $this->boletos_pdf::INICIO_BOLETO + 13);
		
		$this->setFonte("Courier", 9);

		$this->escreveLinha($this->dados["empresa_nome"], $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 19);
		$this->escreveLinha($this->dados["empresa_cnpj"], $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 19);
		$this->escreveLinhaDireita($this->dados["codigo_cedente"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $this->boletos_pdf::INICIO_BOLETO + 19);
		$this->escreveLinha($this->dados["data_emissao"], $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 25);
		$this->escreveLinha($this->dados["codigo"], $this->boletos_pdf::MARGEM_LATERAL + $this->largura_colunas[0] + 2, $this->boletos_pdf::INICIO_BOLETO + 25);
		$this->escreveLinha(array_key_exists("especie_documento", $this->dados) ? $this->dados["especie_documento"] : "OUTRO", $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->largura_colunas[1] - $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 25);
		$this->escreveLinha($this->dados["aceite"], $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 25);
		$this->escreveLinha($this->dados["data_emissao"], $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 25);
		$this->escreveLinhaDireita($this->dados["nosso_numero"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $this->boletos_pdf::INICIO_BOLETO + 25);
		$this->escreveLinha($this->dados["modalidade_carteira"], $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->largura_colunas[1] - $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 31);
		$this->escreveLinha($this->dados["especie_moeda"], $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 31);
		
		$this->setFonte("Courier-Bold", 9);
		
		$this->escreveLinhaDireita($this->dados["valor_original"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $this->boletos_pdf::INICIO_BOLETO + 31);
		
		$this->setFonte("Courier", 9);
		
		$this->escreveTexto(str_replace("<br>", " ", $this->dados["instrucoes"]), $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 35, $this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2) - $this->largura_colunas[5] - 4, 25);
		
		$this->escreveLinha(sprintf("%-45.45s", mb_strtoupper($this->dados["cliente_nome"], "iso-8859-1")), $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 67.5);
		$this->escreveLinha($this->dados["cpf_cnpj"], $this->boletos_pdf::MARGEM_LATERAL + 2, $this->boletos_pdf::INICIO_BOLETO + 71);
		
	}
	
	function escreveDadosBancarios() {
		
		$filename =  "D:/xampp/htdocs/intranet/imagens".DIRECTORY_SEPARATOR."boletos_{$this->dados["codigo_banco"]}.png";
		
		if (file_exists($filename)) {
			$this->insereImagem("png", $filename, $this->boletos_pdf::MARGEM_LATERAL + 1, $this->boletos_pdf::INICIO_BOLETO + 1, 40, 6);
		}
		
		$this->setFonte("Helvetica-Bold", 12);
		
		$this->escreveLinhaCentro($this->dados["codigo_banco"], $this->boletos_pdf::MARGEM_LATERAL + 37.5, $this->boletos_pdf::INICIO_BOLETO + 6.5);
		$this->escreveLinhaDireita($this->dados["linha_digitavel"], $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 2, $this->boletos_pdf::INICIO_BOLETO + 6.5, 0.6);
		
	}
	
	function desenhaDestaques() {

		PDF_setcolor($this->pdf, "fill", "cmyk", 0.04, 0.02, 0.00, 0.00);

		$largura = ($this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {
			$this->desenhaQuadrado($this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i), $this->boletos_pdf::MARGEM_SUPERIOR + 15, $largura, 11);
		}
		
		$this->desenhaQuadrado($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 28, $this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2), 6);
		$this->desenhaQuadrado($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 8, $this->largura_colunas[5], 54);

		PDF_setcolor($this->pdf, "fill", "cmyk", 1.00, 1.00, 1.00, 1.00);

	}

	function desenhaLinhasCapa() {
		
		$this->setLarguraLinha($this->boletos_pdf::LINHA_GROSSA);
		
		// Remetente
		
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5, 157, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, 157);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5, 187, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, 187);
		
		// Para uso dos Correios
		
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5, $this->boletos_pdf::PAGINA_ALTURA - 40, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, $this->boletos_pdf::PAGINA_ALTURA - 40);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5, $this->boletos_pdf::PAGINA_ALTURA - 35.5, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, $this->boletos_pdf::PAGINA_ALTURA - 35.5);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5, $this->boletos_pdf::PAGINA_ALTURA - 10, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, $this->boletos_pdf::PAGINA_ALTURA - 10);
		
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5, $this->boletos_pdf::PAGINA_ALTURA - 40, $this->boletos_pdf::MARGEM_LATERAL + 5, $this->boletos_pdf::PAGINA_ALTURA - 10);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, $this->boletos_pdf::PAGINA_ALTURA - 40, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, $this->boletos_pdf::PAGINA_ALTURA - 10);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5 - 40, $this->boletos_pdf::PAGINA_ALTURA - 35.5, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5 - 40, $this->boletos_pdf::PAGINA_ALTURA - 10);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5 - 85, $this->boletos_pdf::PAGINA_ALTURA - 35.5, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5 - 85, $this->boletos_pdf::PAGINA_ALTURA - 10);

		$this->setLarguraLinha($this->boletos_pdf::LINHA_FINA);
		
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5 - 85, $this->boletos_pdf::PAGINA_ALTURA - 22.75, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - 5, $this->boletos_pdf::PAGINA_ALTURA - 22.75);
		
		
		$altura_quadrado = 3.875;
		
		for ($i = 0; $i < 4; $i++) {
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 2, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 2 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 2, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 2 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 2, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 2, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 2 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 2 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i);
		}
		
		for ($i = 0; $i < 4; $i++) {
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 50, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 50 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 50, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 50 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 50, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 50, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 5 + 50 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + (2 + $altura_quadrado) * $i, $this->boletos_pdf::MARGEM_LATERAL + 5 + 50 + 4, $this->boletos_pdf::PAGINA_ALTURA - 35.5 + 2 + $altura_quadrado + (2 + $altura_quadrado) * $i);
		}
		
	}
	
	function desenhaLinhas($desenhar_cerrilhado) {
		
		// Linhas Tracejadas
		
		if ($desenhar_cerrilhado && $this->boletos_pdf::MARGEM_LATERAL > 10) {
			
			$this->setLarguraLinha(0.1, true);
			
			$this->desenhaLinha(10, 0, 10, $this->boletos_pdf::PAGINA_ALTURA);
			$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - 10, 0, $this->boletos_pdf::PAGINA_LARGURA - 10, $this->boletos_pdf::PAGINA_ALTURA);
			
			$this->desenhaLinha(0, 79, 10, 79);
			$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA, 79, $this->boletos_pdf::PAGINA_LARGURA - 10, 79);
			
			$this->desenhaLinha(0, 190, 10, 190);
			$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA, 190, $this->boletos_pdf::PAGINA_LARGURA - 10, 190);
			
		}
		
		// Cabeï¿½alho
		
		$this->setLarguraLinha($this->boletos_pdf::LINHA_GROSSA);
		
		$largura = ($this->boletos_pdf::PAGINA_LARGURA - ($this->boletos_pdf::MARGEM_LATERAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {

			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i), $this->boletos_pdf::MARGEM_SUPERIOR + 15, $this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i) + $largura, $this->boletos_pdf::MARGEM_SUPERIOR + 15);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i), $this->boletos_pdf::MARGEM_SUPERIOR + 26, $this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i) + $largura, $this->boletos_pdf::MARGEM_SUPERIOR + 26);
			
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i), $this->boletos_pdf::MARGEM_SUPERIOR + 15, $this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i), $this->boletos_pdf::MARGEM_SUPERIOR + 26);
			$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i) + $largura, $this->boletos_pdf::MARGEM_SUPERIOR + 15, $this->boletos_pdf::MARGEM_LATERAL + ($largura * $i) + (2 * $i) + $largura, $this->boletos_pdf::MARGEM_SUPERIOR + 26);
			
		}

		// Endereï¿½o
		
		$this->setLarguraLinha($this->boletos_pdf::LINHA_GROSSA);
		
		// Horizontais Grossas
		
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26);
		
		// Verticais Grossas
		
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO, $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26);
		
		// Quadro detalhamento

		$this->setLarguraLinha($this->boletos_pdf::LINHA_GROSSA);

		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 2, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 2);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 6 + 2, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 6 + 2);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO - 2, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO - 2);
		
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 2, $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO - 2);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_ENDERECO + 26 + 2, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO - 2);

		// Boleto

		$this->setLarguraLinha($this->boletos_pdf::LINHA_GROSSA);

		// Horizontais Grossas

		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 8, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 8);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 62, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 62);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 74, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 74);
		
		// Verticais Grossas

		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO, $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 74);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 8, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 62);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 74);

		$this->setLarguraLinha($this->boletos_pdf::LINHA_FINA);

		// Horizontais Finas

		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 14, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 14);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 20, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 20);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 26, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 26);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 32, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 32);

		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 38, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 38);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 44, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 44);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 50, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 50);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL - $this->largura_colunas[5], $this->boletos_pdf::INICIO_BOLETO + 56, $this->boletos_pdf::PAGINA_LARGURA - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 56);

		// Verticais Finas

		// Separador Dados Bancï¿½rios
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 30, $this->boletos_pdf::INICIO_BOLETO, $this->boletos_pdf::MARGEM_LATERAL + 30, $this->boletos_pdf::INICIO_BOLETO + 8);
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + 45, $this->boletos_pdf::INICIO_BOLETO, $this->boletos_pdf::MARGEM_LATERAL + 45, $this->boletos_pdf::INICIO_BOLETO + 8);
		
		// Separador Dados Boleto
		$this->desenhaLinha($this->boletos_pdf::MARGEM_LATERAL + $this->largura_colunas[0], $this->boletos_pdf::INICIO_BOLETO + 20, $this->boletos_pdf::MARGEM_LATERAL + $this->largura_colunas[0], $this->boletos_pdf::INICIO_BOLETO + 26);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->largura_colunas[1] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 20, $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->largura_colunas[1] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 32);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 26, $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->largura_colunas[2] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 32);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 14, $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->largura_colunas[3] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 32);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 20, $this->boletos_pdf::PAGINA_LARGURA - $this->largura_colunas[5] - $this->largura_colunas[4] - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 32);
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA - ($this->largura_colunas[5] / 2) - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 8, $this->boletos_pdf::PAGINA_LARGURA - ($this->largura_colunas[5] / 2) - $this->boletos_pdf::MARGEM_LATERAL, $this->boletos_pdf::INICIO_BOLETO + 14);

		// Separaï¿½ï¿½o Pagador / Sacador / Avalista
		$this->desenhaLinha($this->boletos_pdf::PAGINA_LARGURA / 2, $this->boletos_pdf::INICIO_BOLETO + 62, $this->boletos_pdf::PAGINA_LARGURA / 2, $this->boletos_pdf::INICIO_BOLETO + 74);

	}

	function desenhaCodigoBarras() {

		$tabela = array(
			0 => "00110",
			1 => "10001",
			2 => "01001",
			3 => "11000",
			4 => "00101",
			5 => "10100",
			6 => "01100",
			7 => "00011",
			8 => "10010",
			9 => "01010"
		);

		if ((strlen($this->dados["codigo_barras"]) % 2) <> 0) {
			$this->dados["codigo_barras"] = "0{$this->dados["codigo_barras"]}";
		}
		
		$this->codigo_barras_posicao = $this->boletos_pdf::MARGEM_LATERAL;

		$this->desenhaBarra($this->boletos_pdf::BARRA_PRETA, $this->boletos_pdf::BARRA_FINA);
		$this->desenhaBarra($this->boletos_pdf::BARRA_BRANCA, $this->boletos_pdf::BARRA_FINA);
		$this->desenhaBarra($this->boletos_pdf::BARRA_PRETA, $this->boletos_pdf::BARRA_FINA);
		$this->desenhaBarra($this->boletos_pdf::BARRA_BRANCA, $this->boletos_pdf::BARRA_FINA);

		for ($i = 0; $i < strlen($this->dados["codigo_barras"]); $i = $i + 2) {
			for ($j = 0; $j < 5; $j++) {
				$this->desenhaBarra($this->boletos_pdf::BARRA_PRETA, $tabela[$this->dados["codigo_barras"][$i]][$j]);
				$this->desenhaBarra($this->boletos_pdf::BARRA_BRANCA, $tabela[$this->dados["codigo_barras"][$i + 1]][$j]);
			}
		}

		$this->desenhaBarra($this->boletos_pdf::BARRA_PRETA, $this->boletos_pdf::BARRA_LARGA);
		$this->desenhaBarra($this->boletos_pdf::BARRA_BRANCA, $this->boletos_pdf::BARRA_FINA);
		$this->desenhaBarra($this->boletos_pdf::BARRA_PRETA, $this->boletos_pdf::BARRA_FINA);

	}

	function desenhaBarra($cor, $tipo) {

		$largura = $tipo == $this->boletos_pdf::BARRA_FINA ? 0.3 : 0.8;

		if ($cor == $this->boletos_pdf::BARRA_PRETA) {
			$this->desenhaQuadrado($this->codigo_barras_posicao, 277, $largura, 15);
		}

		$this->codigo_barras_posicao += $largura;

	}

	private $chache = array();
	
	function insereImagem($tipo, $url, $x, $y, $largura, $altura) {
		
		if (!array_key_exists($url, $this->cache)) {
			$this->cache[$url] = PDF_load_image($this->pdf, $tipo, $url, "");
		}

		PDF_fit_image($this->pdf, $this->cache[$url], $this->mm2Point($x), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA - ($altura + $y)), "boxsize {{$this->mm2Point($largura)} {$this->mm2Point($altura)}} fitmethod meet");

	}

	function desenhaQuadrado($x, $y, $largura, $altura) {

		PDF_rect($this->pdf, $this->mm2Point($x), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA - ($altura + $y)), $this->mm2Point($largura), $this->mm2Point($altura));
		PDF_fill($this->pdf);

	}

	function setLarguraLinha($largura, $tracejado = false) {

		$this->largura_linha = $largura;

		PDF_setlinewidth($this->pdf, $this->mm2Point($largura));

		if ($tracejado) {
			PDF_setdash($this->pdf, $this->mm2Point(1), $this->mm2Point(1));
		} else {
			PDF_setdash($this->pdf, 0, 0);
		}

	}

	function desenhaLinha($x_inicial, $y_inicial, $x_final, $y_final) {

		$offset_y = $y_inicial == $y_final ? 0 : $this->largura_linha / 2;
		$offset_x = $x_inicial == $x_final ? 0 : $this->largura_linha / 2;

		PDF_moveto($this->pdf, $this->mm2Point($x_inicial - $offset_x), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA - $y_inicial - $offset_y));
		PDF_lineto($this->pdf, $this->mm2Point($x_final + $offset_x), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA - $y_final + $offset_y));
		PDF_stroke($this->pdf);

	}

	function setFonte($fonte, $tamanho) {

		PDF_setfont($this->pdf, $this->fontes[$fonte], $tamanho);

	}

	function escreveTexto($texto, $x, $y, $largura, $altura, $alinhamento = "left") {

		PDF_show_boxed($this->pdf, $texto,  $this->mm2Point($x), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA - ($altura + $y)), $this->mm2Point($largura), $this->mm2Point($altura), "$alinhamento", "");

	}

	function escreveLinha($texto, $x, $y, $espacamento = 0) {

		PDF_set_value($this->pdf, "charspacing", $espacamento);
		PDF_show_xy($this->pdf, $texto, $this->mm2Point($x), $this->mm2Point($this->boletos_pdf::PAGINA_ALTURA - $y));
		PDF_set_value($this->pdf, "charspacing", $espacamento);

	}

	function escreveLinhaDireita($texto, $x, $y, $espacamento = 0) {

		PDF_set_value($this->pdf, "charspacing", $espacamento);

		$fontname = PDF_get_parameter($this->pdf, "fontname", 0);
		$size = PDF_get_value($this->pdf, "fontsize", 0);
		$largura = PDF_stringwidth($this->pdf, $texto, $this->fontes[$fontname], $size);

		$this->escreveLinha($texto, $x - $this->point2MM($largura), $y, $espacamento);

	}
	
	function escreveLinhaCentro($texto, $x, $y, $espacamento = 0) {
		
		PDF_set_value($this->pdf, "charspacing", $espacamento);
		
		$fontname = PDF_get_parameter($this->pdf, "fontname", 0);
		$size = PDF_get_value($this->pdf, "fontsize", 0);
		$largura = PDF_stringwidth($this->pdf, $texto, $this->fontes[$fontname], $size);
		
		$this->escreveLinha($texto, $x - ($this->point2MM($largura) / 2), $y, $espacamento);
		
	}
	
}

?>