<?

class cobrancas_parametros_boletos_caixa {

  var $codigo_banco;
  var $moeda;
  var $data_vencimento;
  var $valor;
  var $codigo_cedente;
  var $nosso_numero;
  var $modalidade_carteira;
  var $digito_verificador;

  function cobrancas_parametros_boletos_caixa($dados) {
    $this->codigo_banco = $dados["codigo_banco"];
    $this->moeda = $dados["moeda"];
    $this->data_vencimento = $dados["data_vencimento"];
    $this->valor = $dados["valor"];
    $this->codigo_cedente_original = $dados["codigo_cedente"];
    $this->codigo_cedente = substr($dados["codigo_cedente"], 12, 5);
    $this->agencia = substr($dados["codigo_cedente"], 0, 4);
    $this->nosso_numero = $dados["nosso_numero"];
    $this->modalidade_carteira = $dados["modalidade_carteira"];
  }

  function geraBoletoParametros() {
    $dados["codigo_banco"] = $this->codigo_banco.'-'.'0';
    $dados["codigo_cedente"] = $this->codigo_cedente_original;
    $dados["nosso_numero"] = "8".sprintf("%014s", $this->nosso_numero).'-'.$this->modulo11NN("8".sprintf("%014s", $this->nosso_numero));
    $dados["data_vencimento"] = $this->data_vencimento;
    $dados["valor"] = number_format($this->valor / 100, 2, ',', '.');
    $dados["codigo_barras"] = $this->geraNumeroCodigoBarras();
    $dados["linha_digitavel"] = $this->geraLinhaDigitavel();
    return $dados;
  }

  function geraNumeroCodigoBarras() {
    // 01-03  03  Número do banco
    $codigo_barras  = $this->codigo_banco;
    // 04-04  01  Código da Moeda - 9 para Real
    $codigo_barras .= $this->moeda;
    // 06-09  04  Fator de Vencimento
    $codigo_barras .= $this->geraFatorVencimento($this->data_vencimento);
    // 10-19  10  Valor do Título
    $codigo_barras .= sprintf("%010s", $this->valor);
    // 20-24  05  Código do Cedente
    $codigo_barras .= sprintf("%05s", $this->codigo_cedente);
    // 25-28  04  Agência
    $codigo_barras .= sprintf("%04s", $this->agencia);
    // 29-29  01  Modalidade Carteira
    $codigo_barras .= $this->modalidade_carteira;
    // 30-30  01  Constante - 7
    $codigo_barras .= "7";
    // 31-44  14  Nosso Número
    $codigo_barras .= sprintf("%014s", $this->nosso_numero);
    // Dígito Verificador
    $this->digito_verificador = $this->modulo11($codigo_barras);
    return substr($codigo_barras, 0, 4).$this->digito_verificador.substr($codigo_barras, 4);
  }

  function geraLinhaDigitavel() {
    // 1º GRUPO
    // 01-03  03  Número do banco
    // 04-04  01  Código da Moeda - 9 para Real
    // 05-09  05  Código Cedente
    // 10-10  01  Dígito Verificador
    $grupo1  = $this->codigo_banco;
    $grupo1 .= $this->moeda;
    $grupo1 .= sprintf("%05s", $this->codigo_cedente);
    $grupo1 .= $this->modulo10($grupo1);
    // 2º GRUPO
    // 11-14  04  Agência
    // 15-15  01  Modalidade Carteira
    // 16-16  01  Constante - 7
    // 17-20  04  4 Primeiros Dígitos Nosso Número
    // 21-21  01  Dígito Verificador
    $grupo2  = sprintf("%04s", $this->agencia);
    $grupo2 .= $this->modalidade_carteira;
    $grupo2 .= "7";
    $grupo2 .= substr(sprintf("%014s", $this->nosso_numero), 0, 4);
    $grupo2 .= $this->modulo10($grupo2);
    // 3º GRUPO
    // 22-31  10  Restante do Nosso Número
    // 32-32  01  Dígito Verificador
    $grupo3  = substr(sprintf("%014s", $this->nosso_numero), -10);
    $grupo3 .= $this->modulo10($grupo3);
    // 4º GRUPO
    // 33-33  01  Dígito Verificador do Código de Barras
    $grupo4  = $this->digito_verificador;
    // 5º GRUPO
    // 34-37  04  Fator de Vencimento
    // 38-47  10  Valor do Título
    $grupo5  = $this->geraFatorVencimento($this->data_vencimento);
    $grupo5 .= sprintf("%010s", $this->valor);
    return substr($grupo1, 0, 5).".".substr($grupo1, 5)." ".substr($grupo2, 0, 5).".".substr($grupo2, 5)." ".substr($grupo3, 0, 5).".".substr($grupo3, 5)." "."$grupo4 $grupo5";
  }

  function geraFatorVencimento($data_vencimento) {
    $data_vencimento = explode("/", $data_vencimento);
    return intval(strtotime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}") / 86400, 10) - intval(strtotime("1997-10-07") / 86400, 10);
  }

  function modulo11($valor) {
    $peso = 2;
    $soma = 0;
    for ($i = strlen($valor) - 1; $i >= 0; $i--) {
      $soma += strval($valor[$i] * $peso);
      $peso = ($peso < 9) ? $peso + 1 : 2;
    }
    $resto = $soma % 11;
    return in_array($resto, array(0, 1, 10)) ? 1 : 11 - $resto;
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
