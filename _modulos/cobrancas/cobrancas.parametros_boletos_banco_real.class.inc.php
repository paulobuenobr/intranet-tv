<?

class cobrancas_parametros_boletos_banco_real {

  var $codigo_banco;
  var $agencia;
  var $codigo_cedente;
  var $nosso_numero;
  var $data_vencimento;
  var $valor;
  var $modalidade_carteira;
  var $moeda;
  var $digito_verificador;

  function cobrancas_parametros_boletos_banco_real($dados) {
    $this->codigo_banco = $dados["codigo_banco"];
    $this->agencia = $dados["agencia"];
    $this->codigo_cedente = $dados["codigo_cedente"];
    $this->nosso_numero = $dados["nosso_numero"];
    $this->data_vencimento = $dados["data_vencimento"];
    $this->valor = $dados["valor"];
    $this->modalidade_carteira = $dados["modalidade_carteira"];
    $this->moeda = $dados["moeda"];
  }

  function geraBoletoParametros() {
    $dados["codigo_banco"] = $this->codigo_banco.'-'.$this->modulo11($this->codigo_banco);
    $dados["codigo_cedente"] = $this->agencia."/".$this->codigo_cedente."/".$this->geraDitigaoCobranca();
    $dados["nosso_numero"] = $this->nosso_numero;
    $dados["data_vencimento"] = $this->data_vencimento;
    $dados["valor"] = number_format($this->valor / 100, 2, ',', '.');
    $dados["codigo_barras"] = $this->geraNumeroCodigoBarras();
    $dados["linha_digitavel"] = $this->geraLinhaDigitavel();
    return $dados;
  }

  function geraDitigaoCobranca() {
    $valor = $this->nosso_numero.$this->agencia.$this->codigo_cedente;
    return $this->modulo10($valor);
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
    // 20-23  04  Agência
    $codigo_barras .= sprintf("%04s", $this->agencia);
    // 24-30  07  Código do Cedente
    $codigo_barras .= sprintf("%07s", $this->codigo_cedente);
    // 31-31  01  Digitão
    $codigo_barras .= $this->geraDitigaoCobranca();
    // 32-44  13  Nosso Número
    $codigo_barras .= sprintf("%013s", $this->nosso_numero);
    // Dígito Verificador
    $this->digito_verificador = $this->modulo11($codigo_barras);
    return substr($codigo_barras, 0, 4).$this->digito_verificador.substr($codigo_barras, 4);
  }

  function geraLinhaDigitavel() {
    // 1º GRUPO
    // 01-03  03  Número do banco
    // 04-04  01  Código da Moeda - 9 para Real
    // 05-08  05  Agência
    // 09-08  09  Código Cedente - Primeiro Dígito
    // 10-10  01  Dígito Verificador
    $grupo1  = $this->codigo_banco;
    $grupo1 .= $this->moeda;
    $grupo1 .= $this->agencia;
    $grupo1 .= substr($this->codigo_cedente, 0, 1);
    $grupo1 .= $this->modulo10($grupo1);
    // 2º GRUPO
    // 11-16  06  Código Cedente - Segundo Dígito Em Diante
    // 17-17  01  Digitão da Cobrança
    // 18-20  03  Nosso Número - 3 Primeiros Dígitos
    // 21-21  01  Dígito Verificador
    $grupo2  = substr($this->codigo_cedente, 1);
    $grupo2 .= $this->geraDitigaoCobranca();
    $grupo2 .= substr(sprintf("%013s", $this->nosso_numero), 0, 3);
    $grupo2 .= $this->modulo10($grupo2);
    // 3º GRUPO
    // 22-31  10  Restante do Nosso Número
    // 32-32  01  Dígito Verificador
    $grupo3  = substr(sprintf("%013s", $this->nosso_numero), -10);
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
