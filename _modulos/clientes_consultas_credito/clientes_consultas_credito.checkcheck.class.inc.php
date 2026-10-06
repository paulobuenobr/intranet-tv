<?

set_time_limit(0);
ignore_user_abort(1);
ini_set("default_socket_timeout", 3600);

/*

$checkcheck = new clientes_consultas_credito_checkcheck();

$checkcheck->setCpfCnpj("003.389.059-52");
$checkcheck->setValor(10000);

if ($checkcheck->consultar()) {

	print_r($checkcheck->getConteudo());
	
} else {
	
	print_r($checkcheck->getErro());
	
}

//*/

class clientes_consultas_credito_checkcheck {

	private $bancodedados;
	
	private $webservice = "https://ws.checkcheck.com.br/checkws2.0.5/CheckMasterLiteWS?wsdl";
	
	private $id_aplicacao = "228";
	private $login = "230673";
	private $senha = "5820";
	private $teste = "false";
	
	private $tipo_pessoa = null;
	private $cpf_cnpj = null;
	private $valor = "100.00";
	private $ddd1 = "";
	private $telefone1 = "";
	private $ddd2 = "";
	private $telefone2 = "";
	
	private $erro = null;
	private $conteudo = array();
	private $restricoes = 0;

	public function setCpfCnpj($cpf_cnpj) {
		
		$this->cpf_cnpj = strtoupper(preg_replace("/[^0-9A-Z]/", "", $cpf_cnpj));
		
	}
	
	public function setValor($valor) {
		
		$valor = intval(preg_replace("/[^0-9]/", "", (string) $valor), 10);
		
		if ($valor > 10000) {
			
			$this->valor = number_format($valor / 100, 2, ".", "");
			
		}
		
	}
	
	public function addTelefone($ddd, $telefone) {
		
		$ddd = intval(preg_replace("/[^0-9]/", "", (string) $ddd, 10));
		$telefone = intval(preg_replace("/[^0-9]/", "", (string) $telefone, 10));
		
		if ($ddd && $telefone && (!$this->ddd1 || !$this->ddd2)) {
			
			if (!$this->ddd1) {
				
				$this->ddd1 = (string) $ddd;
				$this->telefone1 = (string) $telefone;
				
			} else {
				
				$this->ddd2 = (string) $ddd;
				$this->telefone2 = (string) $telefone;
				
			}
			
		}
		
	}
	
	public function getErro() {
		
		return $this->erro;
		
	}
	
	public function getConteudo() {
		
		return $this->conteudo;
		
	}
	
	public function getRestricoes() {
		
		return $this->restricoes;
		
	}
  
  public function consultar() {
  	
  	if ($this->validaCPF($this->cpf_cnpj)) {
  		
  		$this->tipo_pessoa = "F";
  		
  	} elseif ($this->validaCNPJ($this->cpf_cnpj)) {
  		
  		$this->tipo_pessoa = "J";
  		
  	} else {
  		
  		$this->erro = "CPF/CNPJ inválido";
  		
  		return false;
  		
  	}
  	
  	$options = array("location" => $this->webservice, "trace" => 1, "exceptions" => 1, "encoding" => "UTF-8");
  	
  	try {
  		
  		$webservice = @new SoapClient($this->webservice, $options);
  		
  	} catch (Exception $e) {
    	
  		$this->erro = $e->getMessage();
  		
  		return false;
  		
  	}
  		
  	$parametros = array();
  	
  	$parametros["idAplicacao"] = new SoapVar($this->id_aplicacao, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "idAplicacao", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["login"] = new SoapVar($this->login, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "login", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["senha"] = new SoapVar($this->senha, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "senha", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	
  	$parametros["cpfCNPJ"] = new SoapVar($this->cpf_cnpj, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "cpfCNPJ", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["tipoPessoa"] = new SoapVar($this->tipo_pessoa, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "tipoPessoa", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["valor"] = new SoapVar($this->valor, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "valor", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	
  	$parametros["uf"] = new SoapVar("", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "uf", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	
  	$parametros["quadroSocietario"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "quadroSocietario", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["participacaoSocietaria"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "participacaoSocietaria", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["referenciaProfissional"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "referenciaProfissional", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["enderecoAlternativo"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "enderecoAlternativo", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["alertaIdentidade"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "alertaIdentidade", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["rendaPresumida"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "rendaPresumida", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["gastoEstimado"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "gastoEstimado", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["alertaObito"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "alertaObito", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["anotacoesCompletas"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "anotacoesCompletas", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["indiceRelacionamentoMercado"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "indiceRelacionamentoMercado", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["localizadorEndereco"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "localizadorEndereco", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["scoring"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "scoring", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["alertaScoring"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "alertaScoring", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["faturamentoResumido"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "faturamentoResumido", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["indicadorOperacionalidade"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "indicadorOperacionalidade", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["limiteCredito"] = new SoapVar("false", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "limiteCredito", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	
  	$parametros["ddd1"] = new SoapVar($this->ddd1, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "ddd1", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["telefone1"] = new SoapVar($this->telefone1, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "telefone1", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["ddd2"] = new SoapVar($this->ddd2, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "ddd2", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	$parametros["telefone2"] = new SoapVar($this->telefone2, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "telefone2", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	
  	$parametros["numeroSerie"] = new SoapVar("", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "numeroSerie", "http://checkmasterlite.wsjar.checkcheck.com.br/");  	
  	$parametros["nsuPai"] = new SoapVar("", XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "nsuPai", "http://checkmasterlite.wsjar.checkcheck.com.br/");
  	
  	$parametros["teste"] = new SoapVar($this->teste, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema", "teste", "http://checkmasterlite.wsjar.checkcheck.com.br/");

  	try {
  		
			$retorno = @$webservice->consultar($parametros);
			
  	} catch (Exception $e) {
    	
  		$this->erro = utf8_decode($e->getMessage());
  			
  		return false;
  		
  	}
  	
  	if ($retorno && $retorno->CheckMasterLite && $retorno->CheckMasterLite->status) {
  	
	  	$retorno = (array) $retorno->CheckMasterLite;
	  	
	  	foreach ($retorno as $grupo => $dados_grupo) {
	  		if (array_key_exists($grupo, $retorno)) {
		  		if (array_key_exists("{$grupo}Analitico", $retorno)) {
		  			if ($dados_grupo->total > 0) {
		  				$retorno["{$grupo}Analitico"] = (array) $retorno["{$grupo}Analitico"];
		  				if (array_key_exists(0, (array) $retorno["{$grupo}Analitico"])) {
				  			$retorno[$grupo] = $retorno["{$grupo}Analitico"];
		  				} else {
		  					$retorno[$grupo] = array($retorno["{$grupo}Analitico"]);
		  				}
			  			unset($retorno["{$grupo}Analitico"]);
		  			} else {
		  				unset($retorno[$grupo]);
			  			unset($retorno["{$grupo}Analitico"]);
		  			}
		  		} else {
		  			$retorno[$grupo] = (array) $retorno[$grupo];
		  		}
	  		}
	  	}
	  	
	  	foreach ($retorno as $grupo => $dados_grupo) {
	  		foreach ($dados_grupo as $campo => $dados_campo) {
	  			if ($dados_campo) {
	  				if (is_string($dados_campo)) {
	  					$retorno[$grupo][$campo] = utf8_decode(utf8_decode($dados_campo));
	  				} elseif (is_object($dados_campo)) {
	  					$retorno[$grupo][$campo] = (array) $dados_campo;
	  					foreach ($dados_campo as $dados_chave_chave => $dados_dados_dados) {
	  						$retorno[$grupo][$campo][$dados_chave_chave] = utf8_decode(utf8_decode($dados_dados_dados));
	  					}
	  				}
	  			}
	  		}
	  	}
	  	
	  	unset($retorno["status"]);
	  	unset($retorno["composicaoServico"]);
	  	
	  	foreach (array("chequeSemFundoVarejo", "pendenciasInternasVarejo", "pendenciasFinanceirasComposto", "protestoEstadual") as $restricoes) {
	  		
		  	if (array_key_exists($restricoes, $retorno)) {
		  		
		  		if (!array_key_exists(0, $retorno[$restricoes])) {
		  			
		  			$retorno[$restricoes] = array($retorno[$restricoes]);
		  			
		  		}
		  		
		  		$this->restricoes += count($retorno[$restricoes]);
		  		
		  	}
		  	
		  }
		  
	  	$this->conteudo = $retorno;
	  	
	  	return true;
	  	
  	}
  	
  	$this->erro = "Retorno inválido";
  	
  	return false;
  	
  }
  
  function validaCPF($cpf) {
  	
		$cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf));
		
  	if (strlen($cpf) != 11 || preg_match("/0{11}|1{11}|2{11}|3{11}|4{11}|5{11}|6{11}|7{11}|8{11}|9{11}/", $cpf)) {
  		
  		return false;
  		
  	}
  	
    $dv1 = $dv2 = 0;

    for ($i = 0; $i < 9; $i++) {
      $dv1 += $cpf[$i] * (10 - $i);
      $dv2 += $cpf[$i] * (11 - $i);
    }
    
    $dv1 = $dv1 % 11 > 1 ? 11 - ($dv1 % 11) : 0;
    $dv2 = ($dv2 + ($dv1 * 2)) % 11 > 1 ? 11 - (($dv2 + ($dv1 * 2)) % 11) : 0;
    
    return $cpf[9] == $dv1 && $cpf[10] == $dv2;
    
  }
	  
  function validaCNPJ($cnpj) {

		$cnpj = strtoupper(preg_replace("/[^0-9A-Z]/", "", $cnpj));
		$base = "";
		$dv = "";
		$len = strlen($cnpj);

		for ($i = 0; $i < $len && strlen($base) < 12; $i++) {
			if (preg_match("/^[0-9A-Z]$/", $cnpj[$i])) {
				$base .= $cnpj[$i];
			}
		}

		for (; $i < $len && strlen($dv) < 2; $i++) {
			if (preg_match("/^[0-9]$/", $cnpj[$i])) {
				$dv .= $cnpj[$i];
			}
		}

		$cnpj = $base . $dv;

		if (strlen($cnpj) != 14 || preg_match("/^(.)\1{13}$/", $cnpj)) {
			return false;
		}

		$mult1 = array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
		$mult2 = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);

		$d1 = 0;
		for ($i = 0; $i < 12; $i++) {
			$d1 += (ord($cnpj[$i]) - 48) * $mult1[$i];
		}

		$r1 = $d1 % 11;
		$dv1 = $r1 > 1 ? 11 - $r1 : 0;

		if ((int) $cnpj[12] !== $dv1) {
			return false;
		}

		$d2 = 0;
		for ($i = 0; $i < 13; $i++) {
			$d2 += (ord($cnpj[$i]) - 48) * $mult2[$i];
		}

		$r2 = $d2 % 11;
		$dv2 = $r2 > 1 ? 11 - $r2 : 0;

		return (int) $cnpj[13] === $dv2;
  }

}

?>
