<?

/*

$dados = array(
	"codigo_banco" => "756",
	"moeda" => "9",
	"data_vencimento" => "05/06/2016",
	"valor" => "9999",
	"codigo_cedente" => "5019",
	"agencia" => "3258",
	"nosso_numero" => "101",
	"modalidade_carteira" => "1");

$parametros = new cobrancas_parametros_boletos_sicoob($dados);

var_dump($parametros->geraBoletoParametros());

// */

class cobrancas_parametros_boletos_sicoob {

  var $codigo_banco;
  var $moeda;
  var $data_vencimento;
  var $valor;
  var $agencia;
  var $codigo_cedente;
  var $nosso_numero;
  var $modalidade_carteira;
  var $digito_verificador;

  function cobrancas_parametros_boletos_sicoob($dados) {
  	
    $this->codigo_banco = $dados["codigo_banco"];
    $this->moeda = $dados["moeda"];
    $this->data_vencimento = $dados["data_vencimento"];
    $this->valor = $dados["valor"];
    $this->codigo_cedente = $dados["codigo_cedente"];
    $this->agencia = $dados["agencia"];
    $this->nosso_numero = sprintf("%07.7s", $dados["nosso_numero"]);
    $this->modalidade_carteira = $dados["modalidade_carteira"];
    
    $this->nosso_numero_dv = $this->geraNossoNumeroDV();
    
  }

  function geraBoletoParametros() {
  	
  	$dados = array();
  	
    $dados["codigo_banco"] = "{$this->codigo_banco}-{$this->modulo11($this->codigo_banco)}";
    $dados["codigo_cedente"] = "{$this->agencia}/{$this->codigo_cedente}-{$this->modulo11($this->codigo_cedente)}";
    $dados["nosso_numero"] = "{$this->nosso_numero}-{$this->nosso_numero_dv}";
    $dados["data_vencimento"] = $this->data_vencimento;
    $dados["valor"] = number_format($this->valor / 100, 2, ',', '.');
    $dados["codigo_barras"] = $this->geraNumeroCodigoBarras();
    $dados["linha_digitavel"] = $this->geraLinhaDigitavel();
    
    return $dados;
    
  }
  
  function geraNossoNumeroDV() {
  	
  	$constante = "3197";
  	
  	$numero = sprintf("%04.4s", $this->agencia).
  		sprintf("%010.10s", $this->codigo_cedente.$this->modulo11($this->codigo_cedente)).
  		sprintf("%07.7s", $this->nosso_numero);
  		
  	$soma = 0;
  	
  	for ($i = 0; $i < strlen($numero); $i++) {
  		$soma += $numero[$i] * $constante[$i % 4];
  	}
  	
  	$resto = $soma % 11;
  	
  	return $resto <= 1 ? 0 : 11 - $resto;
  	
  }

  function geraNumeroCodigoBarras() {
  	
    // 01-03 03 Número do banco
  	$codigo_barras  = sprintf("%03.3s", $this->codigo_banco);
  	
    // 04-04 01 Código da Moeda - 9 para Real
  	$codigo_barras .= sprintf("%01.1s", $this->moeda);
  	
    // 05-05 01 DV do código de barras (cálculo posterior)
    
    // 06-09 04 Fator de vencimento
  	$codigo_barras .= sprintf("%04.4s", $this->geraFatorVencimento($this->data_vencimento));
  	
    // 10-19 10  Valor nominal
  	$codigo_barras .= sprintf("%010.10s", $this->valor);
  	
    // 20-20 01 Tipo de modalidade carteira
  	$codigo_barras .= sprintf("%01.1s", $this->modalidade_carteira);
  	
  	// 21-24 04 Código da agência/cooperativa
  	$codigo_barras .= sprintf("%04.4s", $this->agencia);
  	
  	// 25-26 02 Código da modalidade
  	$codigo_barras .= sprintf("%02.2s", $this->modalidade_carteira);
  	
  	// 27-33 07 Código do associado/cliente
  	$codigo_barras .= sprintf("%07.7s", $this->codigo_cedente.$this->modulo11($this->codigo_cedente));
  	
  	// 34-41 08 Nosso número do boleto
  	$codigo_barras .= sprintf("%08.8s", $this->nosso_numero.$this->nosso_numero_dv);
  	
  	// 41-44 03 Número da parcela a que o boleto se refere - "001" se parcela única
  	$codigo_barras .= sprintf("%03.3s", 1);
  	
  	$this->digito_verificador = $this->modulo11DV($codigo_barras);
  	
    return substr($codigo_barras, 0, 4).$this->digito_verificador.substr($codigo_barras, 4);
    
  }

  function geraLinhaDigitavel() {
  	
    // 1º GRUPO
    
    // 01-03 03 Número do banco
    $grupo1  = sprintf("%03.3s", $this->codigo_banco);
    
    // 04-04 01 Código da moeda - 9 para Real
    $grupo1 .= sprintf("%01.1s", $this->moeda);
    
    // 05-05 01 Código da carteira
    $grupo1 .= sprintf("%01.1s", $this->modalidade_carteira);
    
    // 06-09 04 Código da agência/cooperativa				
    $grupo1 .= sprintf("%04.4s", $this->agencia);
    
    // DV 1º GRUPO
    $grupo1 .= $this->modulo10($grupo1);
    
    // 2º GRUPO
    
    // 10-11 02 Código da modalidade				
    $grupo2  = sprintf("%02.2s", $this->modalidade_carteira);
    
    // 12-18 07 Código do beneficiário/cliente
    $grupo2 .= sprintf("%07.7s", $this->codigo_cedente.$this->modulo11($this->codigo_cedente));
    
    // 19-19 01 Primeiro dígito Nosso número do boleto
    $grupo2 .= substr($this->nosso_numero, 0, 1);
    
    // DV 2º GRUPO
    $grupo2 .= $this->modulo10($grupo2);
    
    // 3º GRUPO
    
    // 20-26 07 Restante do Nosso Número
    $grupo3  = substr($this->nosso_numero, -6).$this->nosso_numero_dv;
    
    // 27-29 03 Número da parcela a que o boleto se refere - "001" se parcela única				
  	$grupo3 .= sprintf("%03.3s", 1);
    
    // DV 3º GRUPO
    $grupo3 .= $this->modulo10($grupo3);
    
    // 4º GRUPO
    
    // 30-30 01 Dígito Verificador do Código de Barras
    $grupo4  = $this->digito_verificador;
    
    // 5º GRUPO
    
    // 31-34 04 Fator de Vencimento
    $grupo5  = sprintf("%04.4s", $this->geraFatorVencimento($this->data_vencimento));

    // 35-44 10 Valor do Título
    $grupo5 .= sprintf("%010.10s", $this->valor);
    
    return substr($grupo1, 0, 5).".".substr($grupo1, 5)." ".substr($grupo2, 0, 5).".".substr($grupo2, 5)." ".substr($grupo3, 0, 5).".".substr($grupo3, 5)." "."$grupo4 $grupo5";
    
  }

  function geraFatorVencimento($data_vencimento) {
  
  	$data_vencimento = explode("/", $data_vencimento);
    $data1 = new DateTime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}");
    $data2 = new DateTime("1997-10-07");
    $intervalo = $data2->diff($data1);

	return $intervalo->format("%a");

    //$data_vencimento = explode("/", $data_vencimento);
    
    //return (int) ((strtotime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}") / 86400) - (strtotime("1997-10-07") / 86400));
    
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
