<?

set_time_limit(0);

class cobrancas_fatura {

	const PAGINA_LARGURA = 210;
	const PAGINA_ALTURA = 297;
	
	const MARGEM_SUPERIOR = 10;
	const MARGEM_INFERIOR = 14;
	const MARGEM_HORIZONTAL = 10;
		
	const LINHA_ALTURA = 8;

	const TITULO_ALTURA = 11;
	const CLIENTE_LINHAS = 3;
	const CLIENTE_ALTURA = self::CLIENTE_LINHAS * self::LINHA_ALTURA;
	
	const BARRA_CEP_LARGURA = 0.5;
	const BARRA_CEP_ESPACAMENTO = 0.6;
	const BARRA_CEP_CURTA = 1.4;
	const BARRA_CEP_ALTA = 3.0;

	const FATURA_INICIO = 25;
	const CABECALHO_INICIO = self::FATURA_INICIO + self::TITULO_ALTURA + 2;
	const CLIENTE_INICIO = self::CABECALHO_INICIO + self::TITULO_ALTURA + 2;
	const TITULO_DETALHAMENTO_INICIO = self::CLIENTE_INICIO + self::CLIENTE_ALTURA + 2;
	const DETALHAMENTO_INICIO = self::TITULO_DETALHAMENTO_INICIO + self::TITULO_ALTURA + 2;
	const DETALHAMENTO_LINHA_ALTURA = 6;

	const LINHA_GROSSA = 0.30;
	const LINHA_FINA = 0.15;

	private $cliente_campos = array(
		array(
			array("campo" => "cliente_nome", "label" => "Cliente", "largura" => 150),
			array("campo" => "cpf_cnpj", "label" => "CPF/CNPJ", "largura" => 0)
		),
		array(
			array("campo" => "endereco", "label" => "Endereço", "largura" => 0)
		),
		array(
			array("campo" => "bairro", "label" => "Bairro", "largura" => 60),
			array("campo" => "cidade", "label" => "Cidade", "largura" => 60),
			array("campo" => "estado", "label" => "Estado", "largura" => 30),
			array("campo" => "cep", "label" => "CEP", "largura" => 0)
		)
	);
	
	private $detalhamento_campos = array(
		array("campo" => "descricao", "label" => "Descrição", "alinhamento" => "esquerda", "largura" => 155),
		//array("campo" => "periodo", "label" => "Período", "alinhamento" => "esquerda", "largura" => 55),
		array("campo" => "valor_final", "label" => "Valor (R$)", "alinhamento" => "direita", "largura" => 0)
	);

	private $bancodedados;
	private $pdf;
	private $dados = array();
	private $fontes = array();
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
	
	function __construct($bancodedados) {

		$this->bancodedados = $bancodedados;

	}
	
	function gerarFatura($fatura_id) {
		
		$this->carregaDados($fatura_id);

		$this->pdf = PDF_new();

		$this->iniciaDocumento();
		
		$this->carregaFontes();
		
		$this->desenhaFatura();
		
		$this->finalizaDocumento();

	}
	
	function exportar($attachment = true) {

		$conteudo = $this->conteudo();

		header("Content-type: application/pdf;");

		if ($attachment) {
			header("Content-disposition: attachment; filename=nota-fatura-{$this->dados["codigo"]}.pdf;");
		} else {
			header("Content-disposition: inline; filename=nota-fatura-{$this->dados["codigo"]}.pdf;");
		}

		header("Content-length: ".strlen($conteudo));

		print $conteudo;

		die();

	}

	function conteudo() {

		return PDF_get_buffer($this->pdf);

	}
		
	function desenhaFatura() {
		
		$this->iniciaPagina();
		
		$this->desenhaDestaques();
		$this->escreveDadosEmpresa();
		$this->desenhaLinhas();
		
		$this->escreveLabels();
		
		$this->escrveDadosCabecalho();
		$this->escreveCliente();
		$this->escreveDetalhamento();
		
		$this->finalizaPagina();
		
	}

	function carregaFontes() {

		$this->fontes["Courier"] = PDF_findfont($this->pdf, "Courier", "host", 0);
		$this->fontes["Courier-Bold"] = PDF_findfont($this->pdf, "Courier-Bold", "host", 0);
		$this->fontes["Helvetica"] = PDF_findfont($this->pdf, "Helvetica", "host", 0);
		$this->fontes["Helvetica-Bold"] = PDF_findfont($this->pdf, "Helvetica-Bold", "host", 0);

	}

	function iniciaDocumento() {

		PDF_begin_document($this->pdf, "", "destination={type=fitwindow} openmode=none pagelayout=singlepage viewerpreferences={printscaling=none}");
		
		PDF_set_info($this->pdf, "Author", "Olé TV");
		PDF_set_info($this->pdf, "Title", "Fatura {$this->dados["codigo"]}");

	}

	function finalizaDocumento() {

		PDF_end_document($this->pdf, "");

	}

	function iniciaPagina() {

		PDF_begin_page_ext($this->pdf, $this->mm2Point(self::PAGINA_LARGURA), $this->mm2Point(self::PAGINA_ALTURA), "");

	}

	function finalizaPagina() {

		PDF_end_page($this->pdf);

	}
		
	function escreveDadosEmpresa() {

		$this->insereImagem("png", DIR_RAIZ."imagens".DIRECTORY_SEPARATOR."logo_ole_tv_retangular.png", self::MARGEM_HORIZONTAL, self::MARGEM_SUPERIOR - 2, 60, 15);
		
		$this->setFonte("Helvetica-Bold", 7);
		
		$this->escreveLinhaDireita("OTT AND IPTV LTDA EPP", self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::MARGEM_SUPERIOR + 2);
		
		$this->setFonte("Helvetica", 7);

		$this->escreveLinhaDireita("CNPJ: 23.911.953/0001-07 - Inscrição Estadual: 25.785.542-4", self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::MARGEM_SUPERIOR + 5.5);
		$this->escreveLinhaDireita("Rua Lídio Antônio de Matos, 358, Sala 02A, Pav 02, Kobrasol - São José / SC - 88102-460", self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::MARGEM_SUPERIOR + 9);
		$this->escreveLinhaDireita("Telefone: (48) 4042-6262 - E-mail: financeiro@oletv.net.br", self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::MARGEM_SUPERIOR + 12.5);
		
	}
	
	function escreveLabels() {
		
		$this->setFonte("Helvetica-Bold", 16);
		
 		$this->escreveLinhaCentro("Fatura Nº. {$this->dados["codigo"]}" , self::PAGINA_LARGURA / 2, self::FATURA_INICIO + 7.5);
		$this->escreveLinhaCentro("Detalhamento", self::PAGINA_LARGURA / 2, self::TITULO_DETALHAMENTO_INICIO + 7.5);
		
		// Cabeçalho

		$this->setFonte("Helvetica", 7);
		
		$labels_cabecalho = array("Competência", "Emissão", "Vencimento", "Valor");
		$largura = (self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {
			$this->escreveLinha($labels_cabecalho[$i], self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i) + 1, self::CABECALHO_INICIO + 3);
		}
		
		// Cliente
		
		foreach ($this->cliente_campos as $linha => $campos) {
			$largura = 0;
			foreach ($campos as $campo) {
				$this->escreveLinha($campo["label"], self::MARGEM_HORIZONTAL + $largura + 1, self::CLIENTE_INICIO + ($linha * self::LINHA_ALTURA) + 3);
				$largura += $campo["largura"];
			}
		}
		
		// Detalhamento
		
		$this->setFonte("Helvetica-Bold", 10);

		$largura = 0;
		foreach ($this->detalhamento_campos as $campo) {
			$this->escreveLinha($campo["label"], self::MARGEM_HORIZONTAL + $largura + 1, self::DETALHAMENTO_INICIO + 4.5);
			$largura += $campo["largura"];
		}
		
		$this->escreveLinha("Total", self::MARGEM_HORIZONTAL + 1, self::PAGINA_ALTURA - self::MARGEM_INFERIOR - self::DETALHAMENTO_LINHA_ALTURA + 4.5);
		
		// Roda-pé
		
		$this->setFonte("Helvetica", 7);

		$this->escreveLinhaDireita("Emissão de fatura conforme Lei N. 8.846/1994 e LC N. 116/2003.", self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_SUPERIOR);
		
	}
		
	function escrveDadosCabecalho() {

		$this->setFonte("Helvetica-Bold", 13);
		
		$dados_cabecalho = array($this->dados["competencia"], $this->dados["data_emissao"], $this->dados["data_vencimento"], "R$ {$this->dados["valor_total"]}");
		
		$largura = (self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {
			$this->escreveLinhaCentro($dados_cabecalho[$i], self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i) + ($largura / 2), self::CABECALHO_INICIO + 9);
		}

	}

	function escreveCliente() {
		
		$this->setFonte("Helvetica-Bold", 9);

		foreach ($this->cliente_campos as $linha => $campos) {
			$largura = 0;
			foreach ($campos as $campo) {
				$this->escreveLinha($this->dados[$campo["campo"]], self::MARGEM_HORIZONTAL + $largura + 1, self::CLIENTE_INICIO + ($linha * self::LINHA_ALTURA) + 6.5);
				$largura += $campo["largura"];
			}
		}
	
	}
	
	function escreveDetalhamento() {
		
		$this->setFonte("Helvetica", 10);

		$linha = 1;
		foreach ($this->dados["detalhamento"] as $detalhamento) {
			$largura = 0;
			foreach ($this->detalhamento_campos as $campo) {
				if ($campo["campo"] == "valor_final") {
					$this->escreveLinhaDireita($detalhamento[$campo["campo"]], self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL - 1, self::DETALHAMENTO_INICIO + ($linha * self::DETALHAMENTO_LINHA_ALTURA) + 4.5);
				} else {
					$this->escreveLinha($detalhamento[$campo["campo"]], self::MARGEM_HORIZONTAL + $largura + 1, self::DETALHAMENTO_INICIO + ($linha * self::DETALHAMENTO_LINHA_ALTURA) + 4.5);
				}
				$largura += $campo["largura"];
			}
			$linha++;
		}
		
		$this->setFonte("Helvetica-Bold", 10);

		$this->escreveLinhaDireita($this->dados["valor_total"], self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL - 1, self::PAGINA_ALTURA - self::MARGEM_INFERIOR - self::DETALHAMENTO_LINHA_ALTURA + 4.5);

	}
			
	function desenhaDestaques() {

		PDF_setcolor($this->pdf, "fill", "cmyk", 0.04, 0.02, 0.00, 0.00);

		$this->desenhaQuadrado(self::MARGEM_HORIZONTAL, self::FATURA_INICIO, self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2), self::TITULO_ALTURA);

		$largura = (self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {
			$this->desenhaQuadrado(self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i), self::CABECALHO_INICIO, $largura, self::TITULO_ALTURA);
		}
				
		$this->desenhaQuadrado(self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO, self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2), self::TITULO_ALTURA);

		$this->desenhaQuadrado(self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO, self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2), self::DETALHAMENTO_LINHA_ALTURA);

		$linha = 1;
		foreach ($this->dados["detalhamento"] as $detalhamento) {
			if ($linha % 2 == 0) {
				$this->desenhaQuadrado(self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO + ($linha * self::DETALHAMENTO_LINHA_ALTURA), self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2), self::DETALHAMENTO_LINHA_ALTURA);
			}
			$linha++;
		}
		
		$this->desenhaQuadrado(self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR - self::DETALHAMENTO_LINHA_ALTURA, self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2), self::DETALHAMENTO_LINHA_ALTURA);
		
		PDF_setcolor($this->pdf, "fill", "cmyk", 1.00, 1.00, 1.00, 1.00);

	}
	
	function desenhaLinhas() {
		
		$this->setLarguraLinha(self::LINHA_GROSSA);

		// Título
		
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::FATURA_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::FATURA_INICIO);
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::FATURA_INICIO + self::TITULO_ALTURA, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::FATURA_INICIO + self::TITULO_ALTURA);
				
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::FATURA_INICIO, self::MARGEM_HORIZONTAL, self::FATURA_INICIO + self::TITULO_ALTURA);
		$this->desenhaLinha(self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::FATURA_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::FATURA_INICIO + self::TITULO_ALTURA);

		// Cabeçalho
			
		$largura = (self::PAGINA_LARGURA - (self::MARGEM_HORIZONTAL * 2) - (2 * 3)) / 4;
		
		for ($i = 0; $i < 4; $i++) {

			$this->desenhaLinha(self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i), self::CABECALHO_INICIO, self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i) + $largura, self::CABECALHO_INICIO);
			$this->desenhaLinha(self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i), self::CABECALHO_INICIO + self::TITULO_ALTURA, self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i) + $largura, self::CABECALHO_INICIO + self::TITULO_ALTURA);
			
			$this->desenhaLinha(self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i), self::CABECALHO_INICIO, self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i), self::CABECALHO_INICIO + self::TITULO_ALTURA);
			$this->desenhaLinha(self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i) + $largura, self::CABECALHO_INICIO, self::MARGEM_HORIZONTAL + ($largura * $i) + (2 * $i) + $largura, self::CABECALHO_INICIO + self::TITULO_ALTURA);
			
		}

		// Cliente
			
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO);
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO + self::CLIENTE_ALTURA, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO + self::CLIENTE_ALTURA);
		
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO, self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO + self::CLIENTE_ALTURA);
		$this->desenhaLinha(self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO + self::CLIENTE_ALTURA);

		for ($i = 1; $i < self::CLIENTE_LINHAS; $i++) {
			$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO + ($i * self::LINHA_ALTURA), self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::CLIENTE_INICIO + ($i * self::LINHA_ALTURA));
		}

		foreach ($this->cliente_campos as $linha => $campos) {
			$largura = 0;
			foreach ($campos as $campo) {
				if ($campo["largura"]) {
					$largura += $campo["largura"];
					$this->desenhaLinha(self::MARGEM_HORIZONTAL + $largura, self::CLIENTE_INICIO + ($linha * self::LINHA_ALTURA), self::MARGEM_HORIZONTAL + $largura, self::CLIENTE_INICIO + (($linha + 1) * self::LINHA_ALTURA));
				}
			}
		}			

		// Título detalhamento
		
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO);
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO + self::TITULO_ALTURA, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO + self::TITULO_ALTURA);
				
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO, self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO + self::TITULO_ALTURA);
		$this->desenhaLinha(self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::TITULO_DETALHAMENTO_INICIO + self::TITULO_ALTURA);

		// Detalhamento

		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO);
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR);
		
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO, self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR);
		$this->desenhaLinha(self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR);

		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO + self::DETALHAMENTO_LINHA_ALTURA, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::DETALHAMENTO_INICIO + self::DETALHAMENTO_LINHA_ALTURA);
		$this->desenhaLinha(self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR - self::DETALHAMENTO_LINHA_ALTURA, self::PAGINA_LARGURA - self::MARGEM_HORIZONTAL, self::PAGINA_ALTURA - self::MARGEM_INFERIOR - self::DETALHAMENTO_LINHA_ALTURA);

		$largura = 0;
		foreach ($this->detalhamento_campos as $campo) {
			if ($campo["largura"] > 0) {
				$largura += $campo["largura"];
				$this->desenhaLinha(self::MARGEM_HORIZONTAL + $largura, self::DETALHAMENTO_INICIO, self::MARGEM_HORIZONTAL + $largura, self::PAGINA_ALTURA - self::MARGEM_INFERIOR);
			}
		}

	}

	private $chache = array();
	
	function insereImagem($tipo, $url, $x, $y, $largura, $altura) {
		
		if (!array_key_exists($url, $this->cache)) {
			$this->cache[$url] = PDF_load_image($this->pdf, $tipo, $url, "");
		}

		PDF_fit_image($this->pdf, $this->cache[$url], $this->mm2Point($x), $this->mm2Point(self::PAGINA_ALTURA - ($altura + $y)), "boxsize {{$this->mm2Point($largura)} {$this->mm2Point($altura)}} fitmethod meet");

	}

	function desenhaQuadrado($x, $y, $largura, $altura) {

		PDF_rect($this->pdf, $this->mm2Point($x), $this->mm2Point(self::PAGINA_ALTURA - ($altura + $y)), $this->mm2Point($largura), $this->mm2Point($altura));
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

		PDF_moveto($this->pdf, $this->mm2Point($x_inicial - $offset_x), $this->mm2Point(self::PAGINA_ALTURA - $y_inicial - $offset_y));
		PDF_lineto($this->pdf, $this->mm2Point($x_final + $offset_x), $this->mm2Point(self::PAGINA_ALTURA - $y_final + $offset_y));
		PDF_stroke($this->pdf);

	}

	function setFonte($fonte, $tamanho) {

		PDF_setfont($this->pdf, $this->fontes[$fonte], $tamanho);

	}

	function escreveTexto($texto, $x, $y, $largura, $altura, $alinhamento = "left") {

		PDF_show_boxed($this->pdf, $texto,  $this->mm2Point($x), $this->mm2Point(self::PAGINA_ALTURA - ($altura + $y)), $this->mm2Point($largura), $this->mm2Point($altura), "$alinhamento");

	}

	function escreveLinha($texto, $x, $y, $espacamento = 0) {

		PDF_set_value($this->pdf, "charspacing", $espacamento);
		PDF_show_xy($this->pdf, $texto, $this->mm2Point($x), $this->mm2Point(self::PAGINA_ALTURA - $y));
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
		
	function mm2Point($value) {

		return $value / 25.4 * 72;

	}

	function point2MM($value) {

		return $value * 25.4 / 72;

	}

	function carregaDados($fatura_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_faturas.fat_id AS id,
				tab_faturas.fat_numero AS codigo,
				DATE_FORMAT(tab_faturas.fat_data_emissao, '%d/%m/%Y') AS data_emissao,
				DATE_FORMAT(tab_faturas.fat_data_vencimento, '%d/%m/%Y') AS data_vencimento,
				tab_faturas.coa_id AS cobranca_id,
				tab_clientes.cli_id AS cliente_id,
				tab_clientes.cli_nome AS cliente_nome,
				tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
				tab_clientes.clt_id AS cliente_tipo_id
			FROM
				tab_faturas
				INNER JOIN tab_clientes ON (tab_faturas.cli_id = tab_clientes.cli_id)
			WHERE
				tab_faturas.fat_id = $fatura_id");

		if (($this->dados = $this->bancodedados->resultadoArray($query)) != false) {

			$this->dados["cliente_nome"] = preg_replace("/\\[[^\\]]+\\]/", "", $this->dados["cliente_nome"]);

			$this->dados = array_merge($this->dados, $this->buscaEnderecoCobranca($this->dados["cliente_id"]));

			$this->dados["competencia"] = $this->buscaCompetencia($this->dados["cobranca_id"]);
			
			$this->dados["detalhamento"] = $this->buscaDetalhamento($this->dados["id"]);
			
		}

	}
		
	function buscaCompetencia($cobranca_id) {
		
		$query = $this->bancodedados->query("
			SELECT
				DATE_FORMAT(MAX(cod_competencia_termino), '%m / %Y')
			FROM
				tab_cobrancas_detalhes
			WHERE
				coa_id  = {$cobranca_id}");
		
		return $this->bancodedados->resultado($query);
		
	}

	function formataDinheiro($valor, $moeda = "R$", $exibir_nulo = false) {

		$valor_formatado = preg_replace("/[^0-9]/", "", intval($valor, 10));
		if (!$valor_formatado && !$exibir_nulo) return false;
		$valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
		if ($moeda) $valor_formatado = "$moeda $valor_formatado";
		if ($valor < 0) $valor_formatado = "($valor_formatado)";

		return $valor_formatado;

	}

	function buscaEnderecoCobranca($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes_cobranca_dados.ccd_numero)) AS numero,
				tab_clientes_cobranca_dados.ccd_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep
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
				tab_clientes_cobranca_dados.cli_id = $cliente_id");

		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
		} else {
			$dados = $this->buscaEndereco($cliente_id);
		}

		$logradouro = array();
		$complemento = array();
		$apartamento = array();

		if ($dados["logradouro"]) $logradouro[] = $dados["logradouro"];
		if ($dados["numero"]) $logradouro[] = $dados["numero"];

		if ($dados["condominio"]) {
			if ($dados["complemento"]) $apartamento[] = $dados["complemento"];
			if ($dados["bloco"] && $dados["bloco"] != "Único") $apartamento[] = $dados["bloco"];
			if ($apartamento) $complemento[] = implode(" / ", $apartamento);
		} else {
			if ($dados["complemento"]) $logradouro[] = $dados["complemento"];
		}

		if ($complemento) {
			$logradouro[] = implode(", ", $complemento);
		}

		$dados["endereco"] = implode(", ", $logradouro);

		return $dados;

	}

	function buscaEndereco($cliente_id) {

		$query = $this->bancodedados->query("
			SELECT
				tab_condominios.con_nome AS condominio,
				tab_logradouros.log_nome AS logradouro,
				IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, con_numero, tab_clientes.cli_numero)) AS numero,
				tab_clientes.cli_complemento AS complemento,
				tab_condominios_blocos.cob_nome AS bloco,
				tab_bairros.bai_nome AS bairro,
				tab_cidades.cid_nome AS cidade,
				tab_estados.est_sigla AS estado,
				INSERT(LPAD(tab_ceps.cep_exportacao, 8, '0'), 6, 0, '-') AS cep
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

		if (($dados = $this->bancodedados->resultadoArray($query)) != false) {
			$dados["complemento"] = $this->corrigeComplementoCliente($dados["complemento"]);
			return $dados;
		}

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

	function buscaDetalhamento($fatura_id) {
				
		$detalhamento = array();
		$total = 0;
				
		$query = $this->bancodedados->query("
			SELECT
				cft_descricao AS descricao,
				fai_valor_total AS valor_final
			FROM
				tab_faturas_itens
				INNER JOIN tab_classes_fiscais_tipos ON (tab_faturas_itens.cft_id = tab_classes_fiscais_tipos.cft_id)
			WHERE
				tab_faturas_itens.fat_id = $fatura_id
			ORDER BY
				fai_id");
		
		while (($dados = $this->bancodedados->resultadoArray($query)) != false) {

			$total += $dados["valor_final"];
			
			$dados["descricao"] = trim(preg_replace("/\\[[^\\]]+\\]/", "", $dados["descricao"]));
			$dados["valor_final"] = number_format($dados["valor_final"] / 100, 2, ",", ".");
						
			$detalhamento[] = $dados;
			
		}
		
		$this->dados["valor_total"] = number_format($total / 100, 2, ",", ".");

		return $detalhamento;

	}
		
}

?>