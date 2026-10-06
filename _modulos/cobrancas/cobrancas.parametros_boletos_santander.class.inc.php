<?

class cobrancas_parametros_boletos_santander {

  var $codigo_banco;
  var $moeda;
  var $data_vencimento;
  var $valor;
  var $agencia;
  var $codigo_cedente;
  var $nosso_numero;
  var $modalidade_carteira;
  var $digito_verificador;

  function cobrancas_parametros_boletos_santander($dados) {
    $this->codigo_banco = $dados["codigo_banco"];
    $this->moeda = $dados["moeda"];
    $this->data_vencimento = $dados["data_vencimento"];
    $this->valor = $dados["valor"];
    $this->codigo_cedente = $dados["codigo_cedente"];
    $this->agencia = $dados["agencia"];
    $this->nosso_numero = substr($dados["nosso_numero"], -12);
    $this->modalidade_carteira = $dados["modalidade_carteira"];
  }

  function geraBoletoParametros() {
    $dados["codigo_banco"] = $this->codigo_banco.'-'.$this->modulo11($this->codigo_banco);
    $dados["codigo_cedente"] = $this->agencia.' / '.$this->codigo_cedente.$this->modulo11($this->codigo_cedente);
    $dados["nosso_numero"] = $this->nosso_numero.'-'.$this->modulo11($this->nosso_numero);
    $dados["data_vencimento"] = $this->data_vencimento;
    $dados["valor"] = number_format(($this->valor / 100), 2, ',', '.');
    $dados["codigo_barras"] = $this->geraNumeroCodigoBarras();
    $dados["linha_digitavel"] = $this->geraLinhaDigitavel();
    return $dados;
  }

  function geraNumeroCodigoBarras() {
    // 01-03  3  Número do banco
  	$codigo_barras  = $this->codigo_banco;
    // 04-04  1  Código da Moeda - 9 para Real
  	$codigo_barras .= $this->moeda;
    // 05-05  1  DV do código de barras (cálculo posterior)
    // 06-09  4  Fator de vencimento
  	$codigo_barras .= $this->geraFatorVencimento($this->data_vencimento);
    // 10-19 10  Valor nominal
  	$codigo_barras .= sprintf("%010s", $this->valor);
    // 20-20  1  Fixo "9"
  	$codigo_barras .= "9";
    // 21-27  7  Código do cedente padrão Santander
  	$codigo_barras .= sprintf("%07s", $this->codigo_cedente.$this->modulo11($this->codigo_cedente));
    // 28-40 13  Nosso Número
  	$codigo_barras .= sprintf("%013s", $this->nosso_numero.$this->modulo11($this->nosso_numero));
    // 41-41  1  IOS – Seguradoras - Demais clientes usar 0 (zero)
  	$codigo_barras .= "0";
    // 42-44  3  Tipo de Modalidade Carteira
  	$codigo_barras .= $this->modalidade_carteira;
  	
  	$this->digito_verificador = $this->modulo11DV($codigo_barras);
    return substr($codigo_barras, 0, 4).$this->digito_verificador.substr($codigo_barras, 4);
  }

  function geraLinhaDigitavel() {
    // 1º GRUPO
    // 01-03  3  Número do banco
    $grupo1  = $this->codigo_banco;
    // 04-04  1  Código da Moeda - 9 para Real
    $grupo1 .= $this->moeda;
    // 05-05  1  Fixo "9"
    $grupo1 .= "9";
    // 06-09  4  PSK (4 primeiros dígitos)
    $grupo1 .= substr($this->codigo_cedente, 0, 4);
    $grupo1 .= $this->modulo10($grupo1);
    // 2º GRUPO
    // 10-12  3  Restante do PSK
    // 13-19  7  7 primeiros campos do NN
    $grupo2  = substr($this->codigo_cedente.$this->modulo11($this->codigo_cedente), -3);
    $grupo2 .= substr($this->nosso_numero, 0, 7);
    $grupo2 .= $this->modulo10($grupo2);
    // 3º GRUPO
    // 20-25  6  Restante do Nosso Número
    // 26-26  1  Somente p/ Seguradoras, demais usar "0"
    // 27-29  3  Tipo Carteira 102 - Simples S/ Registro
    $grupo3  = substr($this->nosso_numero.$this->modulo11($this->nosso_numero), -6);
    $grupo3 .= "0";
    $grupo3 .= $this->modalidade_carteira;
    $grupo3 .= $this->modulo10($grupo3);
    // 4º GRUPO
    // 30-30  1  Dígito Verificador do Código de Barras
    $grupo4  = $this->digito_verificador;
    // 5º GRUPO
    // 31-34  4  Fator de Vencimento
    // 35-44 10  Valor do Título
    $grupo5  = $this->geraFatorVencimento($this->data_vencimento);
    $grupo5 .= sprintf("%010s", $this->valor);
    return substr($grupo1, 0, 5).".".substr($grupo1, 5)." ".substr($grupo2, 0, 5).".".substr($grupo2, 5)." ".substr($grupo3, 0, 5).".".substr($grupo3, 5)." "."$grupo4 $grupo5";
  }

  function geraFatorVencimento($data_vencimento) {
    $data_vencimento = explode("/", $data_vencimento);
    $data1 = new DateTime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}");
    $data2 = new DateTime("1997-10-07");
    $intervalo = $data2->diff($data1);
    
	return $intervalo->format("%a");

	$data_vencimento = explode("/", $data_vencimento);
    return (int) ((strtotime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}") / 86400) - (strtotime("1997-10-07") / 86400));
  }

  function modulo11($valor) {
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
    $peso = 2;
    $soma = 0;
    for ($i = strlen($valor) - 1; $i >= 0; $i--) {
      $produto = strval($valor[$i] * $peso);
      if (strlen($produto) > 1) $produto = $produto[0] + $produto[1];
      $soma += $produto;
      $peso = ($peso == 2) ? 1 : 2;
    }
    $resto = $soma % 10;
    return $resto ? 10 - $resto : 0;
  }

}

?>
