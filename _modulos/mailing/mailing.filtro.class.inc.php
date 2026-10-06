<?

class mailing_filtro {

  #################
  ## Declarações ##
  #################

  private $endereco_estado;
  private $endereco_cidade;
  private $endereco_bairro;
  private $endereco_logradouro;
  private $endereco_cep;
  private $endereco_numero;
  private $endereco_bloco;
  private $endereco_complemento;

  ###############
  ## Contrutor ##
  ###############
  
  public function __construct () {
    $this->setEnderecoEstado(null);
    $this->setEnderecoCidade(null);
    $this->setEnderecoBairro(null);
    $this->setEnderecoLogradouro(null);
    $this->setEnderecoCep(null);
    $this->setEnderecoNumero(null);
    $this->setEnderecoBloco(null);
    $this->setEnderecoComplemento(null);
  }
  
  #########
  ## Set ##
  #########
  
  public function setEnderecoEstado ($valor) {
    $this->endereco_estado = $valor;
    return $this;
  }

  public function setEnderecoCidade ($valor) {
    $this->endereco_cidade = $valor;
    return $this;
  }

  public function setEnderecoBairro ($valor) {
    $this->endereco_bairro = $valor;
    return $this;
  }

  public function setEnderecoLogradouro ($valor) {
    $this->endereco_logradouro = $valor;
    return $this;
  }

  public function setEnderecoCep ($valor) {
    $this->endereco_cep = $valor;
    return $this;
  }

  public function setEnderecoNumero ($valor) {
    $this->endereco_numero = $valor;
    return $this;
  }

  public function setEnderecoBloco ($valor) {
    $this->endereco_bloco = $valor;
    return $this;
  }

  public function setEnderecoComplemento ($valor) {
    $this->endereco_complemento = $valor;
    return $this;
  }

  #########
  ## Get ##
  #########
  
  public function getEnderecoEstado () {
    return $this->endereco_estado;
  }

  public function getEnderecoCidade () {
    return $this->endereco_cidade;
  }

  public function getEnderecoBairro () {
    return $this->endereco_bairro;
  }

  public function getEnderecoLogradouro () {
    return $this->endereco_logradouro;
  }

  public function getEnderecoCep () {
    return $this->endereco_cep;
  }

  public function getEnderecoNumero () {
    return $this->endereco_numero;
  }

  public function getEnderecoBloco () {
    return $this->endereco_bloco;
  }

  public function getEnderecoComplemento () {
    return $this->endereco_complemento;
  }

}

?>
