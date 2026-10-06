<?

set_time_limit(0);

require_once("mailing.filtro.class.inc.php");
require_once("mailing.cache.class.inc.php");
require_once("mailing.http.class.inc.php");
require_once("mailing.guiafacil.class.inc.php");
require_once("mailing.telelistas.class.inc.php");
require_once("mailing.listasdaqui.class.inc.php");

class mailing {

  #################
  ## Declarações ##
  #################

  private $filtro;
  private $registros;
  private $fornecedores;
  
  ###############
  ## Contrutor ##
  ###############
  
  public function __construct ($guiafacil = true, $telelistas = true, $listasdaqui = true) {
    $this->setFiltro(new mailing_filtro());
    $this->setRegistros(array());
    $this->setFornecedores(array());
    //$this->setFornecedor("guiafacil", class_exists("mailing_guiafacil") && $guiafacil ? new mailing_guiafacil() : null);
    //$this->setFornecedor("telelistas", class_exists("mailing_telelistas") && $telelistas ? new mailing_telelistas() : null);
    $this->setFornecedor("listasdaqui", class_exists("mailing_listasdaqui") && $listasdaqui ? new mailing_listasdaqui() : null);
  }
  
  #########
  ## Set ##
  #########
  
  public function setFiltro ($valor) {
    $this->filtro = $valor instanceof mailing_filtro ? $valor : new mailing_filtro($valor);
    return $this;
  }

  public function setRegistros ($valor) {
    $this->registros = $valor;
    return $this;
  }
  
  public function setRegistro ($nome, $valor = null) {
    $this->registros[$nome] = $valor;
    return $this;
  }
    
  public function setFornecedores ($valor) {
    $this->fornecedores = $valor;
    return $this;
  }
  
  public function setFornecedor ($nome, $valor = null) {
    $this->fornecedores[$nome] = $valor;
    return $this;
  }
  
  #########
  ## Get ##
  #########
  
  public function getFiltro () {
    return $this->filtro;
  }

  public function getRegistros () {
    return $this->registros;
  }
  
  public function getRegistro ($nome) {
    return $this->registros[$nome];
  }
  
  public function getFornecedores () {
    return $this->fornecedores;
  }

  public function getFornecedor ($nome) {
    return $this->fornecedores[$nome];
  }
  
  ###########
  ## Acoes ##
  ###########

  public function processaRegistros () {
    if ($this->getRegistros()) {
      foreach ($this->getRegistros() as $registro) {
        if (!$registro["nome"]) {
          $registro_final = array();
          for ($i=0;$i<sizeof($registro);$i++) {
            if (strlen($registro[$i]["nome"]) > strlen($registro[$i-1]["nome"])) $registro_final["nome"] = $this->corrigeRegistro($registro[$i]["nome"]);
            if (strlen($registro[$i]["endereco"]) > strlen($registro[$i-1]["endereco"])) $registro_final["endereco"] = $this->corrigeRegistro($registro[$i]["endereco"]);
            $registro_final["telefone"] = $registro[$i]["telefone"];
            $registro_final["origem"][] = $registro[$i]["origem"];
          }
          $this->setRegistro($registro_final["telefone"], $registro_final);
        } else {
          $registro["nome"] = $this->corrigeRegistro($registro["nome"]);
          $registro["endereco"] = $this->corrigeRegistro($registro["endereco"]);
          $this->setRegistro($registro["telefone"], $registro);
        }
      }
    }
  }
  
  function corrigeRegistro ($nome) {
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
    $nome = str_replace(".", ". ", $nome);
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome);
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome);
      $nome = ucwords($nome);
    }
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" $minusculas[$i] ", " $minusculas[$i] ", " $nome ");
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" $maiusculas[$i] ", " $maiusculas[$i] ", " $nome ");
    $nome = trim($nome);
    return $nome;
  }  
  
  public function buscaRegistrosPorEndereco ($estado = null, $cidade = null, $bairro = null, $logradouro = null, $cep = null, $numero = null, $bloco = null, $complemento = null) {
    if ($estado) $this->getFiltro()->setEnderecoEstado($estado);
    if ($cidade) $this->getFiltro()->setEnderecoCidade($cidade);
    if ($bairro) $this->getFiltro()->setEnderecoBairro($bairro);
    if ($logradouro) $this->getFiltro()->setEnderecoLogradouro($logradouro);
    if ($cep) $this->getFiltro()->setEnderecoCep($cep);
    if ($numero) $this->getFiltro()->setEnderecoNumero($numero);
    if ($bloco) $this->getFiltro()->setEnderecoBloco($bloco);
    if ($complemento) $this->getFiltro()->setEnderecoComplemento($complemento);
    $this->setRegistros(array());
    foreach ($this->getFornecedores() as $fornecedor) {
      if ($fornecedor && $registros = $fornecedor->setFiltro($this->getFiltro())->buscaRegistros()) {
        foreach ($registros as $registro) {
          $telefones = explode ("/", $registro["telefone"]);
          for ($i=0;$i<sizeof($telefones);$i++) {
            $registro["telefone"] = trim($telefones[$i]);
            $registro_anterior = $this->getRegistro($registro["telefone"]);
            if ($registro_anterior) {
              $this->setRegistro($registro["telefone"], !$registro_anterior["nome"] ? array_merge($registro_anterior, array($registro)) : array($registro_anterior, $registro));
            } else {
              $this->setRegistro($registro["telefone"], $registro);
            }
          }
        }
      }
    }
    $this->processaRegistros();
    return $this->getRegistros();
  }

}

?>
