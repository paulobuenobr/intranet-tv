<?

class integracao_painel_cadastro {

  var $bancodedados;
  var $conteudo;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"];
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }
	
	###########################
	### Busca Dados Titular ###
	###########################
	
	function buscaTitular() {
    $this->conteudo = serialize($this->buscaDadosTitular($_GET["i"]));
	}
	
	#################
	### Cadastrar ###
	#################
	
	function cadastrar() {
	  if ($_POST["email"]) {
	    $usuario = substr($_POST["email"], 0, strpos($_POST["email"], "@"));
	    $dados_email = $this->buscaClienteEmail($usuario);
	    if ($dados_email["cliente_id"] == $_GET["i"]) {
	      // Atualiza E-mail
	      $this->atualizaEmailPrincipal($_GET["i"], $dados_email["id"]);
        // Atualiza Hash
        $this->bancodedados->query("UPDATE email_painel SET data_hora = NOW() WHERE cli_id = {$_GET["i"]}");
        // Retorna sucesso
        $this->conteudo = serialize("ok");
	    } else {
	      $this->conteudo = serialize("E-mail inválido");
	    }
	  } else {
	    if ($this->buscaUsuarioEmail($_POST["usuario"])) {
        $this->conteudo = serialize("E-mail não disponível");
	    } else {
	      $tipo_id = 2;
	      $comentario = "<b>Olé Controle:</b> Adicionado pelo cliente ({$_GET["ip_cliente"]})";
	      $descricao = "E-mail Olé Controle";
	      $assinatura_id = $this->buscaAssinaturaCliente($_GET["i"]);
	      $capacidade = 52428800;
	      $dominio_id = 2062;
	      // Insere E-mail
        $emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, null, false);
        $email_id = $emails->insereEmail($_GET["i"], $assinatura_id, $_POST["usuario"], $dominio_id, $_POST["senha"], $descricao, $capacidade, $tipo_id, $comentario);
      	if ($email_id) {
      	  // Publica e-mail
      	  $emails->publicaClientesEmails(array($email_id));
      	  // Atualiza E-mail principal
  	      $this->atualizaEmailPrincipal($_GET["i"], $email_id);
          // Atualiza Hash
          $this->bancodedados->query("UPDATE email_painel SET data_hora = NOW() WHERE cli_id = {$_GET["i"]}");
          // Retorna sucesso
          $this->conteudo = serialize("ok");
      	} else {
          $this->conteudo = serialize("Erro durante cadastramento do e-mail");
      	}
	    }
	  }
	}
	
	############################
	### Verificar Disponivel ###
	############################
	
	function buscarEmailUsuario() {
	  $this->conteudo = serialize($this->buscaUsuarioEmail($_GET["i"]));
	}
	
	##############
	### Buscas ###
	##############
	
	function buscaDadosTitular($hash) {
	  $query = "
	    SELECT
	      tab_clientes.cli_id AS id,
	      tab_clientes.cli_nome AS nome,
	      tab_clientes.cli_cpf_cnpj AS cpf_cnpj,
	      DATE_FORMAT(tab_clientes.cli_nascimento, '%d/%m/%Y') AS nascimento,
	      DATE_FORMAT((SELECT data_hora FROM email_painel WHERE hash = '$hash'), '%d/%m/%Y (%H:%i)') AS cadastro
	    FROM
	      tab_clientes
	    WHERE
	      tab_clientes.cli_id = (SELECT cli_id FROM email_painel WHERE hash = '$hash')";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $dados["emails"] = $this->buscaEmails($dados["id"]);
      if ($dados["cadastro"]) {
        $dados["email_painel"] = $this->buscaEmailPainel($dados["id"]);
      }
      return $dados;
    }
	}
	
	function buscaAssinaturaCliente($cliente_id) {
	  $query = "
	    SELECT
	      ass_id AS id
	    FROM
	      tab_assinaturas
	    WHERE
	      cli_id = $cliente_id AND
	      ast_id = 2
	    ORDER BY
	      (SELECT pls_id FROM tab_planos WHERE tab_planos.pla_id = tab_assinaturas.pla_id)
	    LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
	}
	
	function buscaEmails($cliente_id) {
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id,
        tab_clientes_emails.cle_usuario AS usuario,
        tab_dominios.dom_dominio AS dominio
      FROM
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_clientes_emails.ces_id = 1 AND
        tab_clientes_emails.dom_id = 2062 AND
        tab_assinaturas.cli_id = $cliente_id
      ORDER BY
        tab_clientes_emails.cle_usuario
      LIMIT 0, 4";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $emails[] = "{$dados["usuario"]}@{$dados["dominio"]}";
    }
    return $emails;
	}
	
	function buscaEmailPainel($cliente_id) {
    $query = "
      SELECT
        tab_clientes_emails.cle_usuario AS usuario,
        tab_dominios.dom_dominio AS dominio
      FROM
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
        INNER JOIN tab_dominios ON (tab_clientes_emails.dom_id = tab_dominios.dom_id)
      WHERE
        tab_clientes_emails.ces_id = 1 AND
        tab_clientes_emails.cle_principal = 1 AND
        tab_assinaturas.cli_id = $cliente_id
      LIMIT 0, 1";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return "{$dados["usuario"]}@{$dados["dominio"]}";
    }
	}

  function buscaUsuarioEmail($usuario) {
    $query = "
      SELECT
        COUNT(cle_id) AS quantidade
      FROM
        tab_clientes_emails
      WHERE
        cle_usuario = '$usuario' AND
        ces_id != 4 AND
        dom_id = 2062";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["quantidade"];
    }
  }

  function buscaClienteEmail($usuario) {
    $query = "
      SELECT
        tab_clientes_emails.cle_id AS id,
        tab_assinaturas.cli_id AS cliente_id
      FROM
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
      WHERE
        tab_clientes_emails.cle_usuario = '$usuario' AND
        tab_clientes_emails.ces_id = 1 AND
        tab_clientes_emails.dom_id = 2062";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }
  
  function atualizaEmailPrincipal($cliente_id, $email_id) {
    $this->bancodedados->query("
      UPDATE
        tab_clientes_emails
        INNER JOIN tab_assinaturas ON (tab_clientes_emails.ass_id = tab_assinaturas.ass_id)
      SET
        tab_clientes_emails.cle_principal = IF(tab_clientes_emails.cle_id = $email_id, 1, 0)
      WHERE
        tab_assinaturas.cli_id = $cliente_id");
  }
  
}

?>
