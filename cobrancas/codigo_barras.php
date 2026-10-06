<?

new codigo_barras($_GET["codigo"]);

class codigo_barras {

  var $altura;
  var $barra_fina;
  var $barra_larga;
  var $margem;
  var $barras;
  var $posicao;

  function codigo_barras($codigo = "") {
    $this->altura = 50;
    $this->barra_fina = 1;
    $this->barra_larga = 3;
    $this->margem = 0;
    $this->barras = array();
    $this->posicao = $this->margem;
    if ($codigo) print $this->geraCodigoBarras($_GET["codigo"]);
  }

  function geraCodigoBarras($codigo) {
    $tabela_codigo_barras = $this->buscaTabelaCodigoBarras();
    // Abertura do código de barras
    $this->registraBarra($this->barra_fina, "preto");
    $this->registraBarra($this->barra_fina, "branco");
    $this->registraBarra($this->barra_fina, "preto");
    $this->registraBarra($this->barra_fina, "branco");
    // Corrige comprimento do codigo
    if ((strlen($codigo) % 2) <> 0) {
      $codigo = "0".$codigo;
    }
    // Aqui começa a varredura no valor enviado para confecção do código de barras
    while (strlen($codigo) > 0) {
      $i = round(substr($codigo, 0, 2));
      $codigo = substr($codigo, 2, strlen($codigo) - 2);
      $f = $tabela_codigo_barras[$i];
      for ($i = 1; $i < 11; $i += 2){
        $this->registraBarra((substr($f, ($i - 1), 1) == "0") ? $this->barra_fina : $this->barra_larga, "preto");
        $this->registraBarra((substr($f, $i, 1) == "0") ? $this->barra_fina : $this->barra_larga, "branco");
      }
    }
    // Fechamento do código de barras
    $this->registraBarra($this->barra_larga, "preto");
    $this->registraBarra($this->barra_fina, "branco");
    $this->registraBarra($this->barra_fina, "preto");
    // Gera imagem
    return $this->geraBarras();
  }

  function buscaTabelaCodigoBarras() {
    // Padrão de Código de Barras Brasileiro - I25
    $tabela_codigo_barras[0] = "00110";
    $tabela_codigo_barras[1] = "10001";
    $tabela_codigo_barras[2] = "01001";
    $tabela_codigo_barras[3] = "11000";
    $tabela_codigo_barras[4] = "00101";
    $tabela_codigo_barras[5] = "10100";
    $tabela_codigo_barras[6] = "01100";
    $tabela_codigo_barras[7] = "00011";
    $tabela_codigo_barras[8] = "10010";
    $tabela_codigo_barras[9] = "01010";

    for ($f1 = 9; $f1 >= 0; $f1--) {
      for ($f2 = 9; $f2 >= 0; $f2--) {
        $f = ($f1 * 10) + $f2;
        $texto = "";
        for($i = 1; $i < 6; $i++) {
          $texto .= substr($tabela_codigo_barras[$f1], ($i - 1), 1).substr($tabela_codigo_barras[$f2], ($i - 1), 1);
        }
        $tabela_codigo_barras[$f] = $texto;
      }
    }

    return $tabela_codigo_barras;
  }

  function registraBarra($largura, $cor) {
    $barra["x1"] = $this->posicao;
    $barra["y1"] = $this->margem;
    $barra["x2"] = $this->posicao - 1 + $largura;
    $barra["y2"] = $this->altura - ($this->margem * 2) + 1;
    $barra["cor"] = $cor;
    $this->posicao += $largura;
    $this->barras[] = $barra;
  }

  function geraBarras() {
    // Calcula largura da imagem
    $largura = $this->posicao + $this->margem;
    // Cria a imagem
    $img = imagecreate($largura, $this->altura);
    // Aloca as cores preto e branco
    $cores["preto"]  = imagecolorallocate($img, 0, 0, 0);
    $cores["branco"] = imagecolorallocate($img, 255, 255, 255);
    // Cria o retángulo principal onde a imagem será aplicada
    imagefilledrectangle($img, 0, 0, $largura, $this->altura, $cores["branco"]);
    foreach ($this->barras as $barra) {
      imagefilledrectangle($img, $barra["x1"], $barra["y1"], $barra["x2"], $barra["y2"], $cores[$barra["cor"]]);
    }
    return imagepng($img);
  }

}

?>
