<?

class mailing_http {

  #################
  ## Declarações ##
  #################

  private $conector;
  private $endereco;
  private $parametros_get;
  private $parametros_post;
  private $conteudo;
  private $arquivo;
  
  ###############
  ## Contrutor ##
  ###############

  public function __construct () {
    $this->setConector(null);
    $this->setEndereco(null);
    $this->setParametrosGet(null);
    $this->setParametrosPost(null);
    $this->setConteudo(null);
    $this->setArquivo(null);
  }
  
  ###############
  ## Destrutor ##
  ###############

  public function __destruct () {
    //unlink(dirname(__FILE__) . "/http/sessoes/" . $this->getArquivo() . ".txt");
  }
  
  #########
  ## Set ##
  #########
  
  public function setConector ($valor) {
    $this->conector = $valor;
    return $this;
  }

  public function setEndereco ($valor) {
    $this->endereco = $valor;
    return $this;
  }
  
  public function setParametrosGet ($valor) {
    $this->parametros_get = $valor;
    return $this;
  }
  
  public function setParametroGet ($nome, $valor) {
    $this->parametros_get[$nome] = $valor;
    return $this;
  }
  
  public function setParametrosPost ($valor) {
    $this->parametros_post = $valor;
    return $this;
  }
  
  public function setParametroPost ($nome, $valor) {
    $this->parametros_post[$nome] = $valor;
    return $this;
  }

  public function setConteudo ($valor) {
    $this->conteudo = $valor;
    return $this;
  }
  
  public function setArquivo ($valor) {
    $this->arquivo = $valor;
    return $this;
  }
  
  #########
  ## Get ##
  #########
  
  public function getConector () {
    return $this->conector;
  }

  public function getEndereco () {
    return $this->endereco;
  }
  
  public function getParametrosGet () {
    return $this->parametros_get;
  }
  
  public function getParametroGet ($nome) {
    return $this->parametros_get[$nome];
  }
  
  public function getParametrosPost () {
    return $this->parametros_post;
  }
  
  public function getParametroPost ($nome) {
    return $this->parametros_post[$nome];
  }

  public function getConteudo () {
    return $this->conteudo;
  }
  
  public function getArquivo () {
    return $this->arquivo;
  }
  
  ###########
  ## Acoes ##
  ###########
  
  public function inicializa ($arquivo = null) {
    $this->setArquivo($arquivo ? $arquivo : md5(rand()*time()));
    $this->setConector(curl_init());
    curl_setopt($this->getConector(), CURLOPT_TIMEOUT, 60);
    curl_setopt($this->getConector(), CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->getConector(), CURLOPT_COOKIEJAR, dirname(__FILE__) . "/http/sessoes/" . $this->getArquivo() . ".txt");
    curl_setopt($this->getConector(), CURLOPT_COOKIEFILE, dirname(__FILE__) . "/http/sessoes/" . $this->getArquivo() . ".txt");
    curl_setopt($this->getConector(), CURLOPT_AUTOREFERER, 1);
    curl_setopt($this->getConector(), CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1) ");
    return $this;
  }
  
  public function carrega ($utiliza_cache = false, $mantem_parametros = false, $retorna_header = true) {
    if (!$this->getConector()) $this->inicializa();
    if ($this->getEndereco()) {
      if ($this->getParametrosGet()) foreach ($this->getParametrosGet() as $nome => $valor) $campos_get[] = urlencode($nome) . "=" . urlencode($valor);
      curl_setopt($this->getConector(), CURLOPT_URL, $this->getEndereco() . ($campos_get?(strpos($this->getEndereco(),"?")!==false?(strpos($this->getEndereco(),"&")!==false?"&":""):"?").implode("&",$campos_get):""));
      if ($this->getParametrosPost()) foreach ($this->getParametrosPost() as $nome => $valor) $campos_post[] = urlencode($nome) . "=" . urlencode($valor);
      curl_setopt($this->getConector(), CURLOPT_POSTFIELDS, $campos_post?implode("&",$campos_post):null);
      $arquivo_md5 = dirname(__FILE__) . "/http/paginas/" . md5($this->getEndereco()."\n".serialize($this->getParametrosGet())."\n".serialize($this->getParametrosPost())) . ".htm";
      if (file_exists($arquivo_md5) && $utiliza_cache) {
        $this->setConteudo($this->carregaArquivo($arquivo_md5));
      } else {
        curl_setopt($this->getConector(), CURLOPT_HEADER, $retorna_header);
        $this->setConteudo(curl_exec($this->getConector()));
        if ($utiliza_cache) $this->gravaArquivo($arquivo_md5, $this->getEndereco()."\n".serialize($this->getParametrosGet())."\n".serialize($this->getParametrosPost())."\n".$this->getConteudo());
      }
      if (!$mantem_parametros) {
        $this->setParametrosGet(null);
        $this->setParametrosPost(null);
      }
    }
    return $this;
  }
  
	public function carregaArquivo ($nome, $modo = "r") {
	  $fp = fopen($nome, $modo);
  	$conteudo = fread($fp, filesize($nome));
  	fclose($fp);
		return $conteudo;
	}
  
  public function gravaArquivo ($nome, $conteudo, $modo = "w+") {
    $fp = fopen($nome, $modo);
    fwrite($fp, $conteudo);
    fclose($fp);
    clearstatcache();
  }
  
}

?>
