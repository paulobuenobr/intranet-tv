<?

class mailing_cache {

  #################
  ## Declarações ##
  #################

  private $arquivo;
  private $dados;
  
  ###############
  ## Contrutor ##
  ###############

  public function __construct ($arquivo) {
    $this->setArquivo(dirname(__FILE__) . "/cache/" . $arquivo);
    $this->carregaDadosArquivo();
  }
  
  ###############
  ## Destrutor ##
  ###############

  public function __destruct () {
    $this->gravaDadosArquivo();
  }  
  
  #########
  ## Set ##
  #########
  
  public function setArquivo ($valor) {
    $this->arquivo = $valor;
    return $this;
  }

  public function setDados ($valor) {
    $this->dados = $valor;
    return $this;
  }
  
  public function setDado ($nome, $valor) {
    $this->dados[$nome] = $valor;
    return $this;
  }
  
  #########
  ## Get ##
  #########
  
  public function getArquivo () {
    return $this->arquivo;
  }

  public function getDados () {
    return $this->dados;
  }

  public function getDado ($nome) {
    return $this->dados[$nome];
  }
  
  ###########
  ## Acoes ##
  ###########

  public function carregaDadosArquivo () {
    $dados = $this->carregaArquivo($this->getArquivo());
    $dados = explode("\n", $dados);
    for ($i=0;$i<sizeof($dados);$i++) {
      $dados[$i] = explode(";", $dados[$i]);
      if (trim($dados[$i][0])) $this->setDado(trim($dados[$i][0]), trim($dados[$i][1]));
    }
  }
  
	public function carregaArquivo ($nome, $modo = "r") {
	  if (file_exists($nome)) {
  	  $fp = @fopen($nome, $modo);
    	$conteudo = fread($fp, filesize($nome));
    	fclose($fp);
  		return $conteudo;
	  }
	}
  
  public function gravaDadosArquivo () {
    if ($this->getDados()) {
      foreach ($this->getDados() as $nome => $valor) if (trim($nome)) $conteudo[] = trim($nome).";".trim($valor);
    }
    if ($conteudo) {
      sort($conteudo);
      $this->gravaArquivo($this->getArquivo(), implode("\n", $conteudo));
    }
  }

  public function gravaArquivo ($nome, $conteudo, $modo = "w+") {
    $fp = fopen($nome, $modo);
    fwrite($fp, $conteudo);
    fclose($fp);
    clearstatcache();
  }  
  
}

?>
