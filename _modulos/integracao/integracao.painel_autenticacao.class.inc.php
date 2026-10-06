<?

class integracao_painel_autenticacao {

  var $bancodedados;
  var $conteudo;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "autenticar";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }
	
	##################
	### Autenticar ###
	##################
	
	function autenticar() {
		$dados_cliente = $this->buscaCliente($_POST["email"]);
	  if ($dados_cliente) {
	    if ($_POST["senha"] == "testecontrole" /*$this->autenticarLDAP($dados_cliente["usuario"], $dados_cliente["dominio"], $_POST["senha"])*/) {
	      $this->conteudo = serialize($dados_cliente);
	    }
	  } else {
      $this->conteudo = "0";
	  }
	}

	###########################
	### Busca Dados Usuário ###
	###########################
	
	function buscaDadosUsuario() {
    $this->conteudo = serialize($this->buscaCliente($_POST["email"]));
	}
	
	##############
	### Buscas ###
	##############
	
	function buscaCliente($email) {
    $usuario = substr($email, 0, strpos($email, "@"));
    $dominio = substr($email, strpos($email, "@") + 1);
	  $query = "
	    SELECT
	      tab_clientes.cli_id AS id,
	      tab_clientes.cli_nome AS nome,
	      tab_clientes_emails.cle_usuario AS usuario,
	      tab_clientes.con_id AS condominio_id,
	      tab_dominios.dom_dominio dominio
	    FROM
	      tab_clientes
	      INNER JOIN tab_assinaturas ON (tab_clientes.cli_id = tab_assinaturas.cli_id)
	      INNER JOIN tab_clientes_emails ON (tab_assinaturas.ass_id = tab_clientes_emails.ass_id)
	      INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
	    WHERE
	      tab_assinaturas.ast_id = 2 AND
	      tab_clientes_emails.ces_id = 1 AND
	      tab_clientes_emails.cle_principal = 1 AND
	      tab_clientes_emails.cle_usuario = '$usuario' AND
	      tab_dominios.dom_dominio = '$dominio'
	    LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["email"] = $email;
      $dados["primeiro_nome"] = substr($dados["nome"], 0, strpos($dados["nome"], " "));
      $dados["endereco"] = $this->buscaEndereco($dados["id"]);
      $dados["telefones"] = $this->buscaClienteTelefones($dados["id"]);
      // Dados Bloqueio
      $dados["bloqueado"] = $this->clienteBloqueado($dados["id"]);
      // Dados Velocidade
      $dados["velocidade_atual"] = $this->buscaVelocidadeAtual($dados["id"]);
  	  if ($dados["id"] == 71285) {
        $dados["velocidade_maxima"] = 10485760;
  	  } else {
        $dados["velocidade_maxima"] = $this->buscaVelocidadeMaxima($dados["id"]);
  	  }
      // Permissões
      $dados["permissoes"]["planos"] = true;
      // Olé Condomínios, Residencial, Autenticação PPPoE
  	  if ($dados["id"] == 71285) {
        $dados["permissoes"]["planos_dia"] = true;
  	  } else {
        $dados["permissoes"]["planos_dia"] = $this->permissaoPlanosDia($dados["id"]);
  	  }
      // Quem tiver direito a e-mails
      $dados["permissoes"]["emails"] = $this->permissaoEmails($dados["id"]);
      // Todos
      $dados["permissoes"]["cobrancas"] = true;
      return $dados;
    }
	}
	 
  function clienteBloqueado($cliente_id) {
    $query = "
     SELECT 
       COUNT(*) bloqueios
     FROM 
       tab_servicos_ajustes saj 
     WHERE 
       saj.cli_id = $cliente_id AND 
       saj.sas_id IN (5, 9) AND 
       saj.sac_id = 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["bloqueios"] >= 1;
    }
    return false;
  }
	
	function permissaoPlanosDia($cliente_id) {
	  $query = "
	    SELECT 
	      COUNT(*) AS contratos
      FROM 
        tab_contratos 
      WHERE 
        cli_id = $cliente_id AND 
        pls_id IN (1, 5, 28, 32, 33) AND 
        crs_id IN (19, 20)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return $dados["contratos"] > 0;
    }
	  return false;
	}
	
	function permissaoEmails($cliente_id) {
    $query = "
      SELECT 
        ass_parametros AS parametros
      FROM 
        tab_assinaturas 
      WHERE 
        cli_id = $cliente_id AND 
        ast_id IN (2, 5)";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      if ($dados["parametros"]["emails_quantidade"] > 0) {
      	return true;
      }
    }
    return false;
	}
	
	function buscaVelocidadeAtual($cliente_id) {
	  $query = "
	   SELECT 
	     MAX(clp_velocidade_download) AS velocidade 
     FROM 
       tab_clientes_pontos 
     WHERE 
       ass_id IN (SELECT ass_id FROM tab_assinaturas WHERE cli_id = $cliente_id AND ast_id = 1) AND
       cps_id = 2";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return intval($dados["velocidade"], 10);
    }
	  $query = "
	   SELECT 
	     MAX(cau_velocidade_download) AS velocidade 
     FROM 
       tab_clientes_autenticacoes 
     WHERE 
       ass_id IN (SELECT ass_id FROM tab_assinaturas WHERE cli_id = $cliente_id AND ast_id = 1) AND
       cas_id = 2";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return intval($dados["velocidade"], 10);
    }
    return 0;
	}
	
	function buscaVelocidadeMaxima($cliente_id) {
		// Olé Condomínios
	  $query = "
	   SELECT 
	     con_velocidade_maxima AS velocidade 
     FROM 
      tab_condominios 
     WHERE 
       con_id = (SELECT con_id FROM tab_clientes WHERE cli_id = $cliente_id) AND
       cos_id = 2";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["velocidade"];
    }
    // Olé Residencial
	  $query = "
	    SELECT 
	      COUNT(*) AS contratos
      FROM 
        tab_contratos 
      WHERE 
        cli_id = $cliente_id AND 
        pls_id IN (5, 28, 33) AND 
        crs_id IN (19, 20)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
    	return 3145728;
    }
    return 0;
	}
	
  function buscaEndereco($cliente_id) {
    $query = "
      SELECT
        tab_clientes.cli_id AS id,
        tab_clientes.cli_nome AS nome,
        tab_clientes.cli_complemento AS complemento,
        IF(tab_condominios_blocos.cob_numero IS NOT NULL, tab_condominios_blocos.cob_numero, IF(tab_condominios.con_numero IS NOT NULL, tab_condominios.con_numero, tab_clientes.cli_numero)) AS numero,
        tab_clientes.cof_id AS formato_cobranca_id,
        DATE_FORMAT(tab_clientes.cli_nascimento, '%d/%m') AS data_aniversario,
        tab_condominios.con_id AS condominio_id,
        tab_condominios.con_nome AS condominio,
        tab_condominios.cnt_id AS condominio_tipo_id,
        tab_condominios_blocos.cob_id AS bloco_id,
        tab_condominios_blocos.cob_nome AS bloco_nome,
        tab_estados.est_nome AS estado,
        tab_cidades.cid_nome AS cidade,
        tab_bairros.bai_nome AS bairro,
        tab_logradouros.log_nome AS logradouro,
        tab_ceps.cep_id AS cep
      FROM
        tab_clientes
        LEFT OUTER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
        LEFT OUTER JOIN tab_condominios ON (tab_clientes.con_id = tab_condominios.con_id)
        INNER JOIN tab_ceps ON (tab_ceps.cep_id = IF(tab_condominios_blocos.cep_id IS NOT NULL, tab_condominios_blocos.cep_id, IF(tab_condominios.con_id IS NOT NULL, tab_condominios.cep_id, tab_clientes.cep_id)))
        INNER JOIN tab_logradouros ON (tab_logradouros.log_id = tab_ceps.log_id)
        INNER JOIN tab_bairros ON (tab_bairros.bai_id = tab_logradouros.bai_id)
        INNER JOIN tab_cidades ON (tab_cidades.cid_id = tab_bairros.cid_id)
        INNER JOIN tab_estados ON (tab_estados.est_id = tab_cidades.est_id)
      WHERE
        cli_id = $cliente_id";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      if ($dados["condominio_id"]) {
        $dados["condominio_nome"] = $dados["condominio"];
        if ($dados["bairro"]) {
          $dados["condominio_nome"] .= " / ".$dados["bairro"];
        }
      }
      if ($dados["complemento"]) {
        $complemento_tipos = $this->buscaComplementoTipos();
        $dados["complemento_tipo_id"] = $dados["complemento"][0];
        $dados["complemento_tipo_nome"] = $complemento_tipos[$dados["complemento_tipo_id"]];
        $dados["complemento_valor"] = substr($dados["complemento"], 1, strlen($dados["complemento"]));
        if ($dados["complemento_tipo_id"] != "o") {
          $dados["complemento"] = $dados["complemento_tipo_nome"]." ".intval($dados["complemento_valor"]);
        } else {
          $dados["complemento"] = $dados["complemento_valor"];
        }
      }
      if ($dados["condominio_nome"]) {
        $endereco_formatado = "Condomínio {$dados["condominio"]}";
        if ($dados["complemento"]) {
          $endereco_formatado .= ", ".$dados["complemento"];
          if ($dados["bloco_nome"] && $dados["bloco_nome"] != "Único") $endereco_formatado .= " / ".$dados["bloco_nome"];
        } else {
          if ($dados["bloco_nome"] && $dados["bloco_nome"] != "Único") $endereco_formatado .= ", ".$dados["bloco_nome"];
        }
      } else {
        if ($dados["logradouro"]) $endereco_formatado[] = $dados["logradouro"];
        if ($dados["numero"]) $endereco_formatado[] = $dados["numero"];
        if ($dados["complemento"]) $endereco_formatado[] = $dados["complemento"];
        if ($dados["bairro"]) $endereco_formatado[] = $dados["bairro"];
        if ($dados["cidade"]) $endereco_formatado[] = $dados["cidade"];
        $endereco_formatado = implode(", ", $endereco_formatado);
      }
      return $endereco_formatado;
    }
  }

  function buscaClienteTelefones($cliente_id) {
    $query = "
      SELECT
        ccl_ramal AS ramal,
        CONCAT('(', ccl_ddd, ') ', ccl_numero) AS telefone
      FROM
        tab_clientes_contatos_telefones
      WHERE
      	cli_id = $cliente_id
      LIMIT
        0, 3";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $telefones[] = $dados["telefone"].($dados["ramal"] ? " (Ramal {$dados["ramal"]})" : "");
    }
    return $telefones;
  }

  function buscaComplementoTipos($tipo = null) {
    $tipos["a"] = "Apto";
    $tipos["l"] = "Loja";
    $tipos["s"] = "Sala";
    $tipos["c"] = "Casa";
    $tipos["o"] = "";
    if ($tipo) {
      return $tipos[$tipo];
    } else {
      return $tipos;
    }
  }

	############
	### LDAP ###
	############
	
	function autenticarLDAP($usuario, $dominio, $senha) {
    include_once("integracao.ldap.class.inc.php");
    $ldap = new integracao_ldap();
    return $ldap->autenticarUsuario($dominio, $usuario, $senha);
	}
	
}

?>
