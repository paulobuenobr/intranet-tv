<?

class integracao_fatura_digital {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "ver";
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
	### Buscas ###
	##############

	function buscaDadosCliente($hash) {
		$query = "
			SELECT
				tab_clientes.cli_id AS id,
				tab_clientes.cli_nome AS nome,
				IF(tab_clientes.cof_id = 28, 1, 0) AS fatura_digital 
			FROM
				fatura_digital
				INNER JOIN tab_clientes ON (tab_clientes.cli_id = fatura_digital.cli_id)
			WHERE
				fatura_digital.hash = '$hash'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
	}
	
	function buscaEnderecoCobranca($cliente_id) {
		$query = "
		  SELECT
		  	logradouro,
		  	numero,
		  	complemento,
		  	condominio,
		  	bairro,
		  	cidade,
		  	estado,
		  	cep
		  FROM
		  	fatura_digital
		  WHERE
		  	cli_id = $cliente_id AND
		  	atualizacao_endereco IS NOT NULL";
    $query = $this->bancodedados->query($query);
    $endereco = $this->bancodedados->resultadoArray($query);
    if ($endereco) {
    	return $endereco;
    }
		$query = "
      SELECT
        tab_condominios.con_nome AS condominio,
      	tab_condominios_blocos.cob_nome AS bloco,
        INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
        IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes_cobranca_dados.ccd_numero)) AS numero,
        tab_clientes_cobranca_dados.ccd_complemento AS complemento,
        tab_logradouros.log_nome AS logradouro,
        tab_bairros.bai_nome AS bairro,
        tab_cidades.cid_nome AS cidade,
        tab_estados.est_sigla AS estado
      FROM
        tab_clientes_cobranca_dados
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes_cobranca_dados.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (IF(tab_clientes_cobranca_dados.con_id IS NOT NULL, tab_clientes_cobranca_dados.con_id, tab_condominios_blocos.con_id) = tab_condominios.con_id)
        INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes_cobranca_dados.cep_id)) = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_cidades.cid_id = tab_bairros.cid_id)
        INNER JOIN tab_estados ON (tab_estados.est_id = tab_cidades.est_id) 
      WHERE
        tab_clientes_cobranca_dados.cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
    $endereco = $this->bancodedados->resultadoArray($query);
    if ($endereco) {
    	$endereco["complemento"] = $this->formataComplemento($endereco["complemento"], $endereco["bloco"]);
    	return $endereco;
    }
		$query = "
      SELECT
        tab_condominios.con_nome AS condominio,
      	tab_condominios_blocos.cob_nome AS bloco,
        INSERT(LPAD(tab_ceps.cep_id, 8, '0'), 6, 0, '-') AS cep,
        IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
        tab_clientes.cli_complemento AS complemento,
        tab_logradouros.log_nome AS logradouro,
        tab_bairros.bai_nome AS bairro,
        tab_cidades.cid_nome AS cidade,
        tab_estados.est_sigla AS estado
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        INNER JOIN tab_ceps ON (IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.cep_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)) = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
        INNER JOIN tab_cidades ON (tab_cidades.cid_id = tab_bairros.cid_id)
        INNER JOIN tab_estados ON (tab_estados.est_id = tab_cidades.est_id) 
      WHERE
        tab_clientes.cli_id = $cliente_id";
		$query = $this->bancodedados->query($query);
    $endereco = $this->bancodedados->resultadoArray($query);
    if ($endereco) {
    	$endereco["complemento"] = $this->formataComplemento($endereco["complemento"], $endereco["bloco"]);
    	return $endereco;
    }
	}
	
	function formataComplemento($complemento, $bloco) {
		$complemento_tipo = substr($complemento, 0, 1);
		$complemento_valor = substr($complemento, 1);
		if ($complemento_tipo == "a") {
			$complemento = "Apto $complemento_valor";
		} elseif ($complemento_tipo == "s") {
			$complemento = "Sala $complemento_valor";
		} elseif ($complemento_tipo == "l") {
			$complemento = "Loja $complemento_valor";
		} elseif ($complemento_tipo == "c") {
			$complemento = "Casa $complemento_valor";
		} else {
			$complemento = $complemento_valor;
		}
		if ($bloco && !preg_match("/[UÚuú]nico/i", $bloco)) {
			$complemento = "$complemento / $bloco";
		}
		return $complemento;
	}
	
  ###########
  ### Ver ###
  ###########

  function ver() {
  	if ($_GET["i"] && ($dados_cliente = $this->buscaDadosCliente($_GET["i"]))) {
	  	if (!$_POST) {
	  		$conteudo = $this->carregaArquivo("integracao.fatura_digital.ver.inc.php");
	  		if ($dados_cliente["fatura_digital"]) {
		  		$conteudo = str_replace("[conteudo]", "
	  			<p style=\"text-align:left\">Olá {$dados_cliente["nome"]},</p>
	  			<p style=\"text-align:left\">Você optou por receber suas próximas faturas por e-mail. Se tiver alguma dúvida, por favor, entre em contato conosco pelo telefone (48) 3028-0000.</p>
	  			<p style=\"text-align:center\">Equipe Olé Telecom.</p>", $conteudo);
	  		} else {
		  		$conteudo = str_replace("[conteudo]", "
	  		<form method=\"post\">
	  			<p style=\"text-align:left\">Olá {$dados_cliente["nome"]},</p>
	  			<p style=\"text-align:left\">Clique no botão abaixo e passe a receber suas faturas por e-mail. Além de tornar a sua vida mais prática, você estará ajudando a preservar o meio-ambiente.</p>
	  			<p><input type=\"submit\" value=\"Sim, eu quero receber minhas faturas por e-mail!\" class=\"adesao\" /></p>
					<input type=\"hidden\" name=\"fatura_digital\" value=\"1\" />
				</form>", $conteudo);
	  		}
	  		// Registra Acesso
	 			$this->bancodedados->query("UPDATE fatura_digital SET ultimo_acesso = NOW() WHERE hash = '{$_GET["i"]}'");
	  		die($conteudo);
	 		} else {
	 			$this->bancodedados->query("UPDATE tab_clientes SET cof_id = 28 WHERE cli_id = {$dados_cliente["id"]}");
	 			$this->bancodedados->query("UPDATE fatura_digital SET adesao = NOW() WHERE hash = '{$_GET["i"]}'");
	 			die("<script type=\"text/javascript\">window.location = './?i={$_GET["i"]}';</script>");
	 		}
  	} else {
  		$this->erro();
  	}
  }
  
  ##########################
  ### Atualizar Cadastro ###
  ##########################
  
  function atualizar_cadastro() {
  	if ($_GET["i"] && ($dados_cliente = $this->buscaDadosCliente($_GET["i"]))) {
	  	if (!$_POST) {
	  		if ($_GET["atualizado"]) {
	  			$conteudo = $this->carregaArquivo("integracao.fatura_digital.mensagem.inc.php");
		  		$conteudo = str_replace("[conteudo]", "<p>{$dados_cliente["nome"]},</p><p>O seu endereço de cobrança foi atualizado com sucesso.</p><p>Obrigado.</p><p>Equipe Olé Telecom</p>", $conteudo);
					die($conteudo);
	  		} else {
	  			$endereco = $this->buscaEnderecoCobranca($dados_cliente["id"]);
	  			$conteudo = $this->carregaArquivo("integracao.fatura_digital.atualizar_cadastro.inc.php");
		  		$conteudo = str_replace("[nome]", $dados_cliente["nome"], $conteudo);
		  		$conteudo = str_replace("[logradouro]", $endereco["logradouro"], $conteudo);
		  		$conteudo = str_replace("[numero]", $endereco["numero"], $conteudo);
  				$conteudo = str_replace("[complemento]", $endereco["complemento"], $conteudo);
  				$conteudo = str_replace("[condominio]", $endereco["condominio"], $conteudo);
  				$conteudo = str_replace("[bairro]", $endereco["bairro"], $conteudo);
		  		$conteudo = str_replace("[cidade]", $endereco["cidade"], $conteudo);
		  		$conteudo = str_replace("[estado]", $endereco["estado"], $conteudo);
  				$conteudo = str_replace("[cep]", $endereco["cep"], $conteudo);
					die($conteudo);
	  		}
	  	} else {
	  		$this->bancodedados->query("UPDATE fatura_digital SET atualizacao_endereco = NOW(), logradouro = '{$_POST["logradouro"]}', numero = '{$_POST["numero"]}', complemento = '{$_POST["complemento"]}', condominio = '{$_POST["condominio"]}', bairro = '{$_POST["bairro"]}', cidade = '{$_POST["cidade"]}', estado = '{$_POST["estado"]}', cep = '{$_POST["cep"]}' WHERE hash = '{$_GET["i"]}'");
	 			die("<script type=\"text/javascript\">window.location = './?a=atualizar_cadastro&atualizado=1&i={$_GET["i"]}';</script>");
	  	}
  	} else {
  		$this->erro();
  	}
  }
  
  ############
  ### Erro ###
  ############
  
  function erro() {
  	die("<html><header><title>Erro</title></header><body><h1>Página não encontrada</h1><hr /><p>Se tiver problemas para acessar a página que necessita, por favor, entre em contato com o nosso suporte no telefone (48) 3028-0000.</p><p>Olé Telecom.</p></body></html>");
  }

}

?>
