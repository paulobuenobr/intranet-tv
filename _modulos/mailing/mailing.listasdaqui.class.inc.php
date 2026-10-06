<?

class mailing_listasdaqui {

  #################
  ## Declarações ##
  #################

  private $cache;
  private $filtro;
  private $http;
  private $registros;
  
  ###############
  ## Contrutor ##
  ###############

  public function __construct () {
    $this->setCache(new mailing_cache("listasdaqui.csv"));
    $this->setFiltro(new mailing_filtro());
    $this->setHttp(new mailing_http());
    $this->setRegistros(array());    
  }
  
  #########
  ## Set ##
  #########
  
  public function setCache ($valor) {
    $this->cache = $valor instanceof mailing_cache ? $valor : new mailing_cache($valor);
    return $this;
  }

  public function setFiltro ($valor) {
    $this->filtro = $valor instanceof mailing_filtro ? $valor : new mailing_filtro($valor);
    return $this;
  }

  public function setHttp ($valor) {
    $this->http = $valor instanceof mailing_http ? $valor : new mailing_http($valor);
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

  #########
  ## Get ##
  #########
  
  public function getCache () {
    return $this->cache;
  }

  public function getFiltro () {
    return $this->filtro;
  }

  public function getHttp () {
    return $this->http;
  }

  public function getRegistros () {
    return $this->registros;
  }
  
  public function getRegistro ($nome) {
    return $this->registros[$nome];
  }
  
  ###########
  ## Acoes ##
  ###########

  public function buscaRegistros () {
    $this->organizaFiltro();
    if ($this->validaFiltro()) {
      $this->setRegistros($this->carregaRegistros());
      return $this->getRegistros();
    } else return false;
  }
  
  public function organizaFiltro () {
    # Estado
    $this->getFiltro()->setEnderecoEstado($this->limpaEnderecoEstado($this->getFiltro()->getEnderecoEstado()));
    # Cidade
    $this->getFiltro()->setEnderecoCidade($this->limpaEnderecoCidade($this->getFiltro()->getEnderecoCidade()));
    # Logradouro
    $this->getFiltro()->setEnderecoLogradouro($this->limpaEnderecoLogradouro($this->getFiltro()->getEnderecoLogradouro()));
    # Numero
    $this->getFiltro()->setEnderecoNumero($this->limpaEnderecoNumero($this->getFiltro()->getEnderecoNumero()));
    # Bloco
    $this->getFiltro()->setEnderecoBloco($this->limpaEnderecoBloco($this->getFiltro()->getEnderecoBloco()));
    # Complemento
    $this->getFiltro()->setEnderecoComplemento($this->limpaEnderecoComplemento($this->getFiltro()->getEnderecoComplemento()));
  }
  
  public function limpaEnderecoEstado ($valor) {
    $valor = strtolower($valor);
    $valor = str_replace(array("ç", "ñ"), array("c", "n"), $valor);
    $valor = preg_replace("/[áàâãäª]/", "a", $valor);
    $valor = preg_replace("/[éèêë]/", "e", $valor);
    $valor = preg_replace("/[íìîï]/", "i", $valor);
    $valor = preg_replace("/[óòôõö°º]/", "o", $valor);
    $valor = preg_replace("/[úùûü]/", "u", $valor);
    $valor = preg_replace("/[^a-z ]/", "", $valor);
    $valor = trim($valor);
    return preg_match("/[a-z]{2}/", $valor) ? strtoupper($valor) : "";
  }    
  
  public function limpaEnderecoCidade ($valor) {
    $valor = strtolower($valor);
    $valor = str_replace(array("ç", "ñ"),array("c", "n"), $valor);
    $valor = preg_replace("/[áàâãäª]/", "a", $valor);
    $valor = preg_replace("/[éèêë]/", "e", $valor);
    $valor = preg_replace("/[íìîï]/", "i", $valor);
    $valor = preg_replace("/[óòôõö°º]/", "o", $valor);
    $valor = preg_replace("/[úùûü]/", "u", $valor);
    $valor = preg_replace("/[^a-z0-9 ]/", "", $valor);
    $valor = preg_replace("/ +[^ ]{1,2} +/", " ", $valor);
    $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
    $valor = trim($valor);
    return $valor;
  }
  
  public function limpaEnderecoLogradouro ($valor) {
    $removiveis = array (
                          "alameda", "avenida", "beco", "bosque", "condominio", "conjunto", "estrada", "jardim", "largo", "loteamento", "parque", "passagem", "passeio", "praca", "praia", "recanto", "rodovia", "rua", "servidao", "travessa", "via", "vila", "das", "dos",
                          "almirante", "barao", "brigadeiro", "capitao", "cardeal", "comandante", "comendador", "conde", "conselheiro", "coronel", "deputado", "desembargador", "dom", "doutor", "duque", "engenheiro", "expedicionario", "farmaceutico", "frei", "general", "governador", "imigrante", "indio", "irma", "irmao", "jornalista", "madre", "major", "marechal", "medico", "ministro", "padre", "papa", "pastor", "poeta", "prefeito", "presidente", "princesa", "professor", "professora", "radialista", "santo", "sao", "sargento", "senador", "soldado", "tenente", "vereador", "maestro"
                        );
    $valor = strtolower($valor);
    $valor = str_replace(array("ç", "ñ"),array("c", "n"), $valor);
    $valor = preg_replace("/[áàâãäª]/", "a", $valor);
    $valor = preg_replace("/[éèêë]/", "e", $valor);
    $valor = preg_replace("/[íìîï]/", "i", $valor);
    $valor = preg_replace("/[óòôõö°º]/", "o", $valor);
    $valor = preg_replace("/[úùûü]/", "u", $valor);
    $valor = preg_replace("/[^a-z0-9 ]/", "", $valor);
    $valor = preg_replace("/ +[^ ]{1,2} +/", " ", $valor);
    $valor = str_replace($removiveis, "", $valor);
    $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
    $valor = trim($valor);
    return $valor;
  }
  
  public function limpaEnderecoNumero ($valor) {
    $valor = preg_replace("/[^0-9 ]/", "", $valor);
    return preg_match("/[0-9]+/", $valor) ? $valor : "";
  }
  
  public function limpaEnderecoBloco ($valores) {
    if (!is_array($valores)) $valores = array($valores);
    foreach ($valores as $valor) {
      $valor = strtolower($valor);
      $valor = str_replace(array("ç", "ñ"),array("c", "n"), $valor);
      $valor = preg_replace("/[áàâãäª]/", "a", $valor);
      $valor = preg_replace("/[éèêë]/", "e", $valor);
      $valor = preg_replace("/[íìîï]/", "i", $valor);
      $valor = preg_replace("/[óòôõö°º]/", "o", $valor);
      $valor = preg_replace("/[úùûü]/", "u", $valor);
      $valor = preg_replace("/[^a-z0-9 ]/", "", $valor);
      $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
      $valor = trim($valor);
      if ($valor) $valores_tratados[] = $valor;
    }
    return $valores_tratados;
  }
  
  public function limpaEnderecoComplemento ($valores) {
    if (!is_array($valores)) $valores = array($valores);
    foreach ($valores as $valor) {
      $valor = preg_replace("/[^0-9 ]/", "", $valor);
      if ($valor) $valores_tratados[] = $valor;
    }
    return $valores_tratados;
  }

  public function limpaEndereco ($valor) {
    $valor = strtolower($valor);
    $valor = str_replace(array("ç", "ñ"),array("c", "n"), $valor);
    $valor = preg_replace("/[áàâãäª]/", "a", $valor);
    $valor = preg_replace("/[éèêë]/", "e", $valor);
    $valor = preg_replace("/[íìîï]/", "i", $valor);
    $valor = preg_replace("/[óòôõö°º]/", "o", $valor);
    $valor = preg_replace("/[úùûü]/", "u", $valor);
    $valor = preg_replace("/[^a-z0-9 ]/", "", $valor);
    $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
    $valor = trim($valor);
    return $valor;
  }
  
  public function validaFiltro () {
    $endereco_estado = $this->getFiltro()->getEnderecoEstado();
    $endereco_cidade = $this->getFiltro()->getEnderecoCidade();
    $endereco_logradouro = $this->getFiltro()->getEnderecoLogradouro();
    if ($endereco_estado && $endereco_cidade && $endereco_logradouro) return true;
    return false;    
  }  

  public function carregaRegistros () {
  	$imagem = $this->carregaImagemSeguranca();
    if ($imagem) {
      $this->getHttp()->setEndereco("http://www.brasiltelecom.com.br/site/102/resposta.jsp");
      //$this->getHttp()->setEndereco("http://www.brasiltelecom.com.br/site/102/consulta/consulta_102.jsp");
      $this->getHttp()->setParametroGet("nome", $this->codificar(""));
      $this->getHttp()->setParametroGet("endr", $this->codificar(trim($this->getFiltro()->getEnderecoLogradouro() . " " . $this->getFiltro()->getEnderecoNumero() . " " . (sizeof($this->getFiltro()->getEnderecoBloco())==1?implode($this->getFiltro()->getEnderecoBloco()):"") . " " . (sizeof($this->getFiltro()->getEnderecoComplemento())==1?implode($this->getFiltro()->getEnderecoComplemento()):""))));
      $this->getHttp()->setParametroGet("bair", $this->codificar(""));
      $this->getHttp()->setParametroGet("locl", $this->codificar(trim($this->getFiltro()->getEnderecoCidade() . " " . $this->getFiltro()->getEnderecoEstado())));
      $this->getHttp()->setParametroGet("secure", $imagem[1]);
      $this->getHttp()->setParametroGet("tipo", 1);
      $this->getHttp()->inicializa($imagem[0]);
      $this->getHttp()->carrega(true, true, false);
    }
    $registros = $this->extraiRegistrosPagina($this->getHttp()->getConteudo());
    return $registros;
  }
  
  public function extraiRegistrosPagina ($pagina) {
    var_dump($pagina);die();
    $pagina = explode("\n", trim($pagina));
    $registros = array();
    for ($i=2;$i<sizeof($pagina);$i++) {
    	$pagina[$i] = $this->decodificar($pagina[$i]);
      if ($pagina[$i]) {
        $registro["telefone"] = "(" . substr($pagina[$i],0,2) . ") " . substr($pagina[$i],4,4) . "-" . substr($pagina[$i],9,4); 
        $registro["nome"] = trim(substr($pagina[$i],13,55));
        $registro["endereco"] = trim(preg_replace("/[\r\n\t ]+/", " ", substr($pagina[$i],68,65)));
        $registro["origem"] = "listasdaqui";
        // filtrar bloco
        if ($registro && $blocos = $this->getFiltro()->getEnderecoBloco()) {
          $bloco_correto = false;
          for ($j=0;$j<sizeof($blocos);$j++) if (strpos(" " . $this->limpaEndereco($registro["endereco"]) . " ", " {$blocos[$j]} ") !== false || $blocos[$i] == "Unico") $bloco_correto = true;
          if (!$bloco_correto) unset($registro);
        }
        // filtrar complementos
        if ($registro && $complementos = $this->getFiltro()->getEnderecoComplemento()) {
          $complemento_correto = false;
          for ($j=0;$j<sizeof($complementos);$j++) if (strpos(" " . $this->limpaEndereco($registro["endereco"]) . " ", " {$complementos[$j]} ") !== false) $complemento_correto = true;
          if (!$complemento_correto) unset($registro);
        }
        if ($registro) $registros[] = $registro;
      }
    }
    return $registros;
  }

  // Carrega imagem do site e salva em jpg na pasta ./listasdaqui/
  public function imagemSegurancaEtapa1 () {
    $this->getHttp()->inicializa();
    $this->getHttp()->setEndereco("http://www.brasiltelecom.com.br/site/secureImage");
    $this->getHttp()->carrega(false, false, false);
    $this->gravaArquivo(dirname(__FILE__)."/listasdaqui/".$this->getHttp()->getArquivo().".jpg", $this->getHttp()->getConteudo(), "wb+");
    return $this->getHttp()->getArquivo();
  }
  
  // Informa o texto da imagem e salva em txt e apaga a imagem em jpg na pasta ./listasdaqui/
  public function imagemSegurancaEpata2 ($arquivo, $valor) {
    $this->gravaArquivo(dirname(__FILE__)."/listasdaqui/".$arquivo.".txt", $arquivo."=".$valor);
    $this->apagaArquivo(dirname(__FILE__)."/listasdaqui/".$arquivo.".jpg");
  }
  
  // Carrega um arquivo de texto disponível da imagem de segurança
  public function carregaImagemSeguranca () {
  	$dp = opendir(dirname(__FILE__)."/listasdaqui/");
    if ($dp) {
      while (($arquivo = readdir($dp)) != false) {
        if (strpos($arquivo, ".txt") !== false) {
          $conteudo = $this->carregaArquivo(dirname(__FILE__)."/listasdaqui/".$arquivo);
          $this->apagaArquivo(dirname(__FILE__)."/listasdaqui/".$arquivo);
          return explode("=", $conteudo);
        }
      }
    }
    return false;
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
  
  public function apagaArquivo ($nome) {
    unlink($nome);
    clearstatcache();
  }
  
  public function codificar ($texto) {
    if (trim($texto)) {
      ob_start();
      system('java -cp '.dirname(__FILE__).' listasdaqui codificar "'.trim($texto).'"');
    	$texto = ob_get_contents();
    	ob_clean();
    } else {
      $texto = "B48A41A90FCA933A";
    }
    return trim($texto);
  }
  
  public function decodificar ($texto) {
    if (trim($texto)) {
      ob_start();
      system('java -cp '.dirname(__FILE__).' listasdaqui decodificar "'.trim($texto).'"');
    	$texto = ob_get_contents();
    	ob_clean();
    } else {
      $texto = "";
    }
    return trim($texto);
  }
  
}

?>
