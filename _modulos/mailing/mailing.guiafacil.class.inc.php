<?

class mailing_guiafacil {

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
    $this->setCache(new mailing_cache("guiafacil.csv"));
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
    } else {
      return false;
    }
  }
  
  public function organizaFiltro () {
    # Estado
    $this->getFiltro()->setEnderecoEstado($this->limpaEnderecoEstado($this->getFiltro()->getEnderecoEstado()));
    # Cidade
    $this->getFiltro()->setEnderecoCidade($this->limpaEnderecoCidade($this->getFiltro()->getEnderecoCidade()));
    # Logradouro
    $this->getFiltro()->setEnderecoLogradouro($this->limpaEnderecoLogradouro($this->getFiltro()->getEnderecoLogradouro()));
    # CEP
    $this->getFiltro()->setEnderecoCep($this->limpaEnderecoCep($this->getFiltro()->getEnderecoCep()));
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
    $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
    $valor = trim($valor);
    return $valor;
  }
  
  public function limpaEnderecoLogradouro ($valor) {
    $removiveis = array (
                          "alameda", "avenida", "beco", "bosque", "condominio", "conjunto", "estrada", "jardim", "largo", "loteamento", "parque", "passagem", "passeio", "praca", "praia", "recanto", "rodovia", "rua", "servidao", "travessa", "via", "vila"/*, "das", "dos"*/,
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
    $valor = str_replace($removiveis, "", $valor);
    $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
    $valor = trim($valor);
    return $valor;
  }
  
  public function limpaEnderecoCep ($valores) {
    if (!is_array($valores)) $valores = array($valores);
    foreach ($valores as $valor) {
    	$valor = preg_replace("/[^0-9]/", "", $valor);
      if ($valor) {
        $valor = sprintf("%08s", $valor);
        $valor = substr($valor, 0, 5) . "-" . substr($valor, 5, 3);
        $valores_tratados[] = $valor;
      }
    }
    return $valores_tratados;
  }
    
  public function limpaEnderecoNumero ($valor) {
    $valor = preg_replace("/[^0-9]/", "", $valor);
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
      $valor = preg_replace("/[^0-9]/", "", $valor);
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
    $valor = preg_replace("/(bl|ap|qd|lt|sl|px|bx)/", " \\1 ", $valor);
    $valor = preg_replace("/[\r\n\t ]+/", " ", $valor);
    $valor = trim($valor);
    return $valor;
  }
  
  public function validaFiltro () {
    $endereco_estado = $this->buscaCodigoEnderecoEstado($this->getFiltro()->getEnderecoEstado());
    $endereco_cidade = $this->buscaCodigoEnderecoCidade($this->getFiltro()->getEnderecoEstado(), $this->getFiltro()->getEnderecoCidade());
    $endereco_logradouro = $this->buscaCodigoEnderecoLogradouro($this->getFiltro()->getEnderecoEstado(), $this->getFiltro()->getEnderecoCidade(), $this->getFiltro()->getEnderecoLogradouro());
    if ($endereco_estado && $endereco_cidade && $endereco_logradouro) return true;
    return false;    
  }  
  
  public function buscaCodigoEnderecoEstado ($endereco_estado) {
    if (!$this->getCache()->getDado("endereco_estado,{$endereco_estado}" && $endereco_estado == "SC")) {
      $this->getCache()->setDado("endereco_estado,{$endereco_estado}", $endereco_estado);
    }
    return $this->getCache()->getDado("endereco_estado,{$endereco_estado}");
  }
  
  public function buscaCodigoEnderecoCidade ($endereco_estado, $endereco_cidade) {
    if (!$this->getCache()->getDado("endereco_cidade,{$endereco_estado},{$endereco_cidade}")) {
      $conteudo = $this->getHttp()->setEndereco("http://www.guiafacilnet.com.br/")->carrega()->getConteudo();
      if (preg_match_all('/<option value=([^>]*)>([^<]*)<\/option>/', $conteudo, $dados)) {
        for ($i=0;$i<sizeof($dados[0]);$i++) {
          if ($endereco_cidade == $this->limpaEnderecoCidade($dados[2][$i])) {
            $this->getCache()->setDado("endereco_cidade,{$endereco_estado},{$endereco_cidade}", $dados[1][$i]);
            break;
          }
        }
      }
    }
    return $this->getCache()->getDado("endereco_cidade,{$endereco_estado},{$endereco_cidade}");
  }
  
  public function buscaCodigoEnderecoLogradouro ($endereco_estado, $endereco_cidade, $endereco_logradouro) {
    if (!$this->getCache()->getDado("endereco_logradouro,{$endereco_estado},{$endereco_cidade},{$endereco_logradouro}")) {
      $conteudo = $this->getHttp()->setEndereco("http://www.guiafacilnet.com.br/busca/endereco/iframe.asp?tipo_=guia&municipio=".$this->buscaCodigoEnderecoCidade($endereco_estado, $endereco_cidade)."&busca=".urlencode($endereco_logradouro))->carrega()->getConteudo();
      if (preg_match_all('/<td height=25 background=..\/..\/img\/fativ.gif><b><a href="default2.asp\?cdLogradouro=([0-9]*)[^>]*>[^ ]+ ([^<]*)<\/td><td width=150 class=lk background=..\/..\/img\/fativ.gif>[^<]*<\/a><\/b> <\/td><td width=100 class=lk background=..\/..\/img\/fativ.gif>([^<]*)<\/a><\/b> <\/td>/', preg_replace("/(\r|\n|\t| |&nbsp;|<br>)+/", " ", $conteudo), $dados)) {
        $this->getCache()->setDado("endereco_logradouro,{$endereco_estado},{$endereco_cidade},{$endereco_logradouro}", $dados[1][0]);
        for ($i=0;$i<sizeof($dados[0]);$i++) {
          if ($endereco_logradouro == $this->limpaEnderecoLogradouro($dados[2][$i]) || ($this->getFiltro()->getEnderecoCep() && in_array($dados[3][$i], $this->getFiltro()->getEnderecoCep()))) {
            $this->getCache()->setDado("endereco_logradouro,{$endereco_estado},{$endereco_cidade},{$endereco_logradouro}", $dados[1][$i]);
            break;
          }
        }
      }
    }
    return $this->getCache()->getDado("endereco_logradouro,{$endereco_estado},{$endereco_cidade},{$endereco_logradouro}");
  }  
  
  public function carregaRegistros () {
    // Realiza Busca
    $this->getHttp()->setParametroGet("tipo_", "endereco");
    $this->getHttp()->setParametroGet("link", "sim");
    $this->getHttp()->setParametroGet("municipio", $this->buscaCodigoEnderecoCidade($this->getFiltro()->getEnderecoEstado(), $this->getFiltro()->getEnderecoCidade()));
    $this->getHttp()->setParametroGet("cdLogradouro", $this->buscaCodigoEnderecoLogradouro($this->getFiltro()->getEnderecoEstado(), $this->getFiltro()->getEnderecoCidade(), $this->getFiltro()->getEnderecoLogradouro()));
    $this->getHttp()->setParametroGet("busca", $this->getFiltro()->getEnderecoLogradouro());
    $this->getHttp()->setEndereco("http://www.guiafacilnet.com.br/busca/endereco/iframe.asp?");
    $pagina = 1;
    $paginas = 0;
    do {
      $this->getHttp()->setParametroGet("page_registro", $pagina);
      $this->getHttp()->carrega(true, true);
      if ($this->getHttp()->getConteudo()) {
        // Extrai Registros Página
        $registros = is_array($registros) ? array_merge($registros, $this->extraiRegistrosPagina($this->getHttp()->getConteudo())) : $this->extraiRegistrosPagina($this->getHttp()->getConteudo());
        // Define Páginas
        if (!$paginas) {
          preg_match_all("/page_registro=([0-9]+)/", $this->getHttp()->getConteudo(), $dados);
          $paginas = (sizeof($dados[0])/2)+1;
        }
        // Incrementa Página
        $pagina++;
      }
    } while ($pagina <= $paginas);
    return $registros;

  }

  public function extraiRegistrosPagina($pagina) {
    $pagina = preg_replace("/<([\\/]?)(font|b)([^>]*)>/", " ", $pagina);
    $pagina = preg_replace("/(\r|\n|\t| |&nbsp;|<br>)+/", " ", $pagina);
    preg_match_all('/<table width=100% cellpadding=0 cellspacing=0 border=0><tr><td>([^,]*),([^<]*)<\/td> <td width=20 valign=top>([^<]*)<\/td> <td width=170 valign=top>([^<]*)<\/td> <td> <\/td> <\/tr> <\/table>/', $pagina, $dados);
    $registros = array();
    for ($i=0;$i<sizeof($dados[0]);$i++) {
      $registro = array();
      $registro["nome"] = trim($dados[2][$i]);
      $registro["endereco"] = trim($dados[1][$i]);
      $registro["telefone"] = "(" . trim($dados[3][$i]) . ") " . trim($dados[4][$i]);
      $registro["telefone"] = str_replace("/", "/ (" . trim($dados[3][$i]) . ")", $registro["telefone"]);
      $registro["origem"] = "guiafacil";
      // filtrar cep
      if ($registro && $ceps = $this->getFiltro()->getEnderecoCep()) {
        $cep_correto = false;
        for ($j=0;$j<sizeof($ceps);$j++) if (strpos($pagina, $ceps[$j]) !== false) $cep_correto = true;
        if (!$cep_correto) unset($registro);
      }
      // filtrar numero
      if ($registro && $numero = $this->getFiltro()->getEnderecoNumero()) {
        $numero_correto = false;
        if (strpos(" " . $this->limpaEndereco($registro["endereco"]) . " ", " {$numero} ") !== false) $numero_correto = true;
        if (!$numero_correto) unset($registro);
      }
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
    return $registros;
    
  }
  
}

?>
