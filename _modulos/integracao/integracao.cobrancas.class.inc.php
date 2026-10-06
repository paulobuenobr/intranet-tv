<?

class integracao_cobrancas {

  var $bancodedados;

  function integracao_cobrancas($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "buscar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

  #############
  ### Geral ###
  #############

	function carregaArquivo($arquivo) {
		if (file_exists(dirname(__FILE__)."/".$arquivo)) $arquivo = dirname(__FILE__)."/".$arquivo;
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		fclose($fp);
		return $retorno;
	}

	function transicao($url) {
		die("<script type=\"text/javascript\">window.location = '$url';</script>");
	}

  ##############
  ### Buscar ###
  ##############

  function buscar() {
  	if (!$_POST) {
      $this->conteudo = $this->carregaArquivo("integracao.cobrancas.buscar.inc.php");
    	$this->conteudo = str_replace('[origem]', $_GET["origem"], $this->conteudo);
    	$this->conteudo = str_replace('[origem_nome]', $this->buscaOrigemNome($_GET["origem"]), $this->conteudo);
    	$this->conteudo = str_replace('[telefone]', $this->buscaOrigemTelefone($_GET["origem"]), $this->conteudo);
    	$this->conteudo = str_replace('[codigo]', '', $this->conteudo);
    	$this->conteudo = str_replace('[cpf_cnpj]', '', $this->conteudo);
  	} else {
  		$dados_cobranca = $this->buscaCobranca($_POST["codigo"], $_POST["cpf_cnpj"]);
  		if ($dados_cobranca) {
  			$this->transicao("?m=cobrancas&a=imprimir&i={$dados_cobranca["id"]}");
  		} else {
  			$this->erro = 21;
  		}
  	}
  }

  function buscaOrigemTelefone($origem_id) {
    $origens[1] = "3028-0000";
    $origens[2] = "3028-0000";
    $origens[3] = "3028-0000";
    $origens[4] = "3028-0000";
    return $origens[$origem_id];
  }

  function buscaOrigemNome($origem_id) {
    $origens[1] = "Wave System Internet";
    $origens[2] = "CSM Telecom";
    $origens[3] = "SodisaNET";
    $origens[4] = "Olé Telecom";
    return $origens[$origem_id];
  }

  function buscaCobranca($codigo, $cpf_cnpj) {
    $query = "
      SELECT
        coa_id AS id
      FROM
        tab_cobrancas
        INNER JOIN tab_clientes ON (tab_cobrancas.cli_id = tab_clientes.cli_id)
      WHERE
        coa_codigo = '$codigo' AND
        cli_cpf_cnpj = '$cpf_cnpj'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  ################
  ### Imprimir ###
  ################

  function imprimir() {
  	$dados_cobranca = $this->buscaDadosCobranca($_GET["i"]);
  	if ($dados_cobranca) {
      $this->conteudo = $this->carregaArquivo(DIR_RAIZ."modelos/cobrancas_boletos_{$dados_cobranca["parametros"]["boleto"]["versao"]}.html");
      foreach ($dados_cobranca["parametros"]["boleto"] as $chave => $valor) {
        $this->conteudo = str_replace("[$chave]", $valor, $this->conteudo);
      }
      $this->conteudo = str_replace("[titulo]", $this->buscaOrigemNome($_GET["origem"]), $this->conteudo);
      $this->conteudo = preg_replace("/\\[[^\\]]*\\]/", "", $this->conteudo);
  	} else {
			$this->erro = 21;
  	}
  }

  function buscaDadosCobranca($cobranca_id) {
    $query = "
      SELECT
        coa_id AS id,
        cof_id AS formato_id,
        coa_parametros AS parametros
      FROM
        tab_cobrancas
      WHERE
        coa_id = $cobranca_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      return $dados;
    }
  }

}

?>
