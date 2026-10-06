<?

class cobrancas_parametros_boletos_hsbc {

  var $codigo_banco;
  var $moeda;
  var $data_vencimento;
  var $valor;
  var $codigo_cedente;
  var $nosso_numero;
  var $modalidade_carteira;
  var $digito_verificador;

  function cobrancas_parametros_boletos_hsbc($dados) {
    $this->codigo_banco = $dados["codigo_banco"];
    $this->moeda = $dados["moeda"];
    $this->data_vencimento = $dados["data_vencimento"];
    $this->valor = $dados["valor"];
    $this->codigo_cedente = $dados["codigo_cedente"];
    $this->nosso_numero = $dados["nosso_numero"];
    $this->modalidade_carteira = $dados["modalidade_carteira"];
  }

  function geraBoletoParametros() {
    $dados["codigo_banco"] = $this->codigo_banco.'-'.$this->modulo11($this->codigo_banco);
    $dados["codigo_cedente"] = $this->codigo_cedente.$this->modulo11($this->codigo_cedente);
    $dados["nosso_numero"] = $this->nosso_numero.'-'.$this->geraVerificadorNossoNumero();
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
    // 20-26  07  Código do Cedente
    $codigo_barras .= sprintf("%07s", $this->codigo_cedente.$this->modulo11($this->codigo_cedente));
    // 27-39  13  Nosso Número
    $codigo_barras .= sprintf("%013s", $this->nosso_numero);
    // 40-43  04  Data Vencimento Formato Juliano
    $codigo_barras .= sprintf("%04s", $this->geraVencimentoJuliano($this->data_vencimento));
    // 44-44  01  Código do aplicativo CNR = 2
    $codigo_barras .= $this->modalidade_carteira;
    // Dígito Verificador
    $this->digito_verificador = $this->modulo11($codigo_barras);
    return substr($codigo_barras, 0, 4).$this->digito_verificador.substr($codigo_barras, 4);
  }

  function geraLinhaDigitavel() {
    // 1º GRUPO
    // 01-03  03  Número do banco
    // 04-04  01  Código da Moeda - 9 para Real
    // 05-09  05  1ª Parte do Código Cedente
    // 10-10  01  Dígito Verificador
    $grupo1  = $this->codigo_banco;
    $grupo1 .= $this->moeda;
    $grupo1 .= substr($this->codigo_cedente, 0, 5);
    $grupo1 .= $this->modulo10($grupo1);
    // 2º GRUPO
    // 11-12  02  Restante do Código Cedente
    // 13-20  08  1ª Parte do Nosso Número
    // 21-21  01  Dígito Verificador
    $grupo2  = substr($this->codigo_cedente.$this->modulo11($this->codigo_cedente), -2);
    $grupo2 .= substr($this->nosso_numero, 0, 8);
    $grupo2 .= $this->modulo10($grupo2);
    // 3º GRUPO
    // 22-26  05  Restante do Nosso Número
    // 27-30  04  Vencimento no formato juliano
    // 31-31  01  Código do Aplicativo -> 2
    // 32-32  01  Dígito Verificador
    $grupo3  = substr($this->nosso_numero, -5);
    $grupo3 .= $this->geraVencimentoJuliano($this->data_vencimento);
    $grupo3 .= $this->modalidade_carteira;
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

  function geraVerificadorNossoNumero($tipo_identificador = "4") {
    $data_vencimento = explode("/", $this->data_vencimento);
    $digito_1 = $this->modulo11NN($this->nosso_numero);
    $digito_2 = $tipo_identificador;
    $digito_3 = $this->modulo11NN(($this->nosso_numero.$digito_1.$digito_2 + 0) + ($this->codigo_cedente + 0) + ($data_vencimento[0].$data_vencimento[1].substr($data_vencimento[2], 2) + 0));
    return $digito_1.$digito_2.$digito_3;
  }

  function geraFatorVencimento($data_vencimento) {
    $data_vencimento = explode("/", $data_vencimento);
    return intval(strtotime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}") / 86400, 10) - intval(strtotime("1997-10-07") / 86400, 10);
  }

  function geraVencimentoJuliano($data_vencimento) {
    if ($this->modalidade_carteira == "4") {
      $data_vencimento = explode("/", $data_vencimento);
      return date("z", strtotime("{$data_vencimento[2]}-{$data_vencimento[1]}-{$data_vencimento[0]}")).$data_vencimento[2][3];
    } else {
      return "0000";
    }
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
    $peso = 9;
    $soma = 0;
    for ($i = strlen($valor) - 1; $i >= 0; $i--) {
      $soma += strval($valor[$i] * $peso);
      $peso = ($peso > 2) ? $peso - 1 : 9;
    }
    $resto = $soma % 11;
    return in_array($resto, array(0, 10)) ? 0 : $resto;
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
