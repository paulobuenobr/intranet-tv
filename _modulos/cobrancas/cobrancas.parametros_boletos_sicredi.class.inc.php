<?php

class cobrancas_parametros_boletos_sicredi {
	
	private $codigo_banco;
	private $moeda;
	private $data_vencimento;
	private $valor;
	private $agencia;
	private $posto;
	private $codigo_cedente;
	private $nosso_numero;
	private $modalidade_carteira;
	private $digito_verificador;
	private $campo_livre;
	
	function __construct($dados) {
		
		$this->codigo_banco = $dados["codigo_banco"];
		$this->moeda = $dados["moeda"];
		$this->data_vencimento = $dados["data_vencimento"];
		$this->valor = $dados["valor"];
		$this->codigo_cedente = $dados["codigo_cedente"];
		$this->agencia = $dados["agencia"];
		$this->posto = $dados["posto"];
		$this->nosso_numero = $dados["nosso_numero"];
		$this->modalidade_carteira = $dados["modalidade_carteira"];
		$this->nosso_numero_dv = $this->geraNossoNumeroDV();
		
	}
	
	function geraBoletoParametros() {
		
		$dados = array ();
		
		$dados["codigo_banco"] = "748-X";
		$dados["codigo_cedente"] = "{$this->agencia}.{$this->posto}.{$this->codigo_cedente}";
		$dados["nosso_numero"] = substr($this->nosso_numero, 0, 2)."/".substr($this->nosso_numero, 2)."-".$this->nosso_numero_dv;
		$dados["data_vencimento"] = $this->data_vencimento;
		$dados["valor"] = number_format($this->valor / 100, 2, ',', '.');
		$dados["codigo_barras"] = $this->geraNumeroCodigoBarras();
		$dados["linha_digitavel"] = $this->geraLinhaDigitavel();
		
		return $dados;
		
	}
	
	function geraNossoNumeroDV() {
		
		return $this->modulo11($this->agencia.$this->posto.$this->codigo_cedente.$this->nosso_numero);
		
	}
	
	function geraNumeroCodigoBarras() {
		
		$this->campo_livre = "11".$this->nosso_numero.$this->nosso_numero_dv.$this->agencia.$this->posto.$this->codigo_cedente."10";
		
		// 01-03 03 Número do banco
		$codigo_barras = sprintf("%03.3s", $this->codigo_banco);
		
		// 04-04 01 Código da Moeda - 9 para Real
		$codigo_barras .= sprintf("%01.1s", $this->moeda);
		
		// 05-05 01 DV do código de barras (cálculo posterior)
		
		// 06-09 04 Fator de vencimento
		$codigo_barras .= sprintf("%04.4s", $this->geraFatorVencimento($this->data_vencimento));
		
		// 10-19 10 Valor nominal
		$codigo_barras .= sprintf("%010.10s", $this->valor);
		
		// 20-20 01 Código numérico correspondente ao tipo de cobrança: “1” – Com Registro
		$codigo_barras .= "1";
		
		// 21-21 01 Código numérico correspondente ao tipo de carteira: “1” - carteira simples
		$codigo_barras .= "1";
		
		// 22-30 09 Nosso número
		$codigo_barras .= sprintf("%09.9s", $this->nosso_numero.$this->nosso_numero_dv);
		
		// 31-34 04 Código da agência/cooperativa
		$codigo_barras .= sprintf("%04.4s", $this->agencia);
		
		// 35-36 02 Posto
		$codigo_barras .= sprintf("%02.2s", $this->posto);
		
		// 37-41 05 Código do beneficiário/cedente
		$codigo_barras .= sprintf("%05.5s", $this->codigo_cedente);

		// 42-42 01 “1” (um) quando houver valor expresso no campo “valor do documento”
		$codigo_barras .= "1";

		// 43-43 01 Filler – “0” zeros
		$codigo_barras .= "0";
		
		// 43-43 01 DV do campo livre calculado por módulo 11 com aproveitamento total (resto igual a 0 ou 1 DV cai para 0)
		$codigo_barras .= $this->modulo11($this->campo_livre);
		
		$this->digito_verificador = $this->modulo11DV($codigo_barras);
		
		return substr($codigo_barras, 0, 4) . $this->digito_verificador . substr($codigo_barras, 4);
		
	}
	
	function geraLinhaDigitavel() {
		
		// 1º GRUPO
		
		// 01-03 03 Número do banco
		$grupo1 = sprintf("%03.3s", $this->codigo_banco);
		
		// 04-04 01 Código da moeda - 9 para Real
		$grupo1 .= sprintf("%01.1s", $this->moeda);
		
		// 05-09 05 Cinco primeiras posições do campo livre
		$grupo1 .= substr($this->campo_livre, 0, 5);
		
		// DV 1º GRUPO
		$grupo1 .= $this->modulo10($grupo1);
		
		// 2º GRUPO
		
		// 10-19 10 posições 06 a 15 do campo livre
		$grupo2 .= substr($this->campo_livre, 5, 10);
		
		// DV 2º GRUPO
		$grupo2 .= $this->modulo10($grupo2);
		
		// 3º GRUPO
		
		// 20-29 10 posições 16 a 25 do campo livre
		$grupo3 .= substr($this->campo_livre, 15).$this->modulo11($this->campo_livre);
		
		// DV 3º GRUPO
		$grupo3 .= $this->modulo10($grupo3);
		
		// 4º GRUPO
		
		// 30-30 01 Dígito Verificador do Código de Barras
		$grupo4 = $this->digito_verificador;
		
		// 5º GRUPO
		
		// 31-34 04 Fator de Vencimento
		$grupo5 = sprintf("%04.4s", $this->geraFatorVencimento($this->data_vencimento));
		
		// 35-44 10 Valor do Título
		$grupo5 .= sprintf("%010.10s", $this->valor);
		
		return substr($grupo1, 0, 5) . "." . substr($grupo1, 5) . " " . substr($grupo2, 0, 5) . "." . substr($grupo2, 5) . " " . substr($grupo3, 0, 5) . "." . substr($grupo3, 5) . " " . "$grupo4 $grupo5";
		
	}
	
	function geraFatorVencimento($data_vencimento) {
		
		$data_vencimento = explode("/", $data_vencimento);
		$data1 = new DateTime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}");
		$data2 = new DateTime("1997-10-07");
		$intervalo = $data2->diff($data1);
		
		return $intervalo->format("%a");
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
	
	function modulo11DV($valor) {
		
		$valor = "{$valor}";
		
		$peso = 2;
		$soma = 0;
		
		for ($i = strlen($valor) - 1; $i >= 0; $i--) {
			
			$soma += $valor[$i] * $peso;
			$peso = ($peso < 9) ? $peso + 1 : 2;
		}
		
		$resto = $soma % 11;
		
		return $resto <= 1 ? 1 : 11 - $resto;
		
	}
	
	function modulo10($valor) {
		
		$valor = "{$valor}";
		
		$peso = 2;
		$soma = 0;
		
		for ($i = strlen($valor) - 1; $i >= 0; $i--) {
			
			$produto = strval($valor[$i] * $peso);
			
			if (strlen($produto) > 1) {
				
				$produto = $produto[0] + $produto[1];
			}
			
			$soma += $produto;
			$peso = ($peso == 2) ? 1 : 2;
		}
		
		$resto = $soma % 10;
		
		return $resto ? 10 - $resto : 0;
		
	}
	
}

?>