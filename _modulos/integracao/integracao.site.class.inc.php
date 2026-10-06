<?

class integracao_site {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"] ? $_GET["a"] : "busca_condominio";
    if (method_exists($this, $this->acao)) {
			$this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

  ########################
  ### Busca Condomínio ###
  ########################

  function busca_condominio() {
    $_GET["cep"] = preg_replace("/[^0-9]/", "", $_GET["cep"]);
    $_GET["numero"] = preg_replace("/[^0-9]/", "", $_GET["numero"]);
    $query = "
      SELECT
        CONCAT(tab_condominios.con_nome, ' (', tab_bairros.bai_nome, ')') AS condominio_nome,
        IF(tab_condominios.cos_id = 2, 1, 2) AS resultado
      FROM
        tab_condominios
        INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
        INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
        INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      WHERE
        tab_condominios.cep_id = {$_GET["cep"]} AND
        tab_condominios.con_numero = {$_GET["numero"]}";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $this->conteudo = "resultado={$dados["resultado"]}&condominio=".urlencode($dados["condominio_nome"]);
    } else {
      $this->conteudo = "resultado=2&condominio=".urlencode("Não encontrado");
    }
  }

  #################################
  ### Registra Cliente Prospect ###
  #################################

  function registra_cliente_prospect() {
  	$dados_cliente = $this->buscaClientePorEndereco();
    if ($dados_cliente) {
      if ($dados_cliente["status_id"] == 4) {
        // Status - Mailing
        $cliente_id = $dados_cliente["id"];
        $this->atualizaDadosCliente($cliente_id);
      } elseif ($dados_cliente["status_id"] == 3) {
        // Status - Cancelado
        $cliente_id = $this->cadastraNovoCliente();
      }
      $this->registraRetorno($cliente_id);
    }
  }

  function buscaClientePorEndereco() {
    $query = "
      SELECT
        cli_id AS id,
        cls_id AS status_id
      FROM
        tab_clientes
        INNER JOIN tab_condominios_blocos ON (tab_clientes.cob_id = tab_condominios_blocos.cob_id)
      WHERE
        tab_condominios_blocos.cep_id = {$_GET["cep"]} AND
        tab_condominios_blocos.cob_numero = {$_GET["numero"]} AND
        tab_condominios_blocos.cob_nome = '{$_GET["bloco"]}' AND
        tab_clientes.cli_complemento LIKE '_{$_GET["complemento"]}'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados;
    }
  }

  function atualizaDadosCliente($cliente_id) {
    $query = "
      UPDATE
        tab_clientes
      SET
        cli_nome = '".$this->corrigeNome($_GET["nome"])."'
      WHERE
        cli_id = $cliente_id";
    $this->bancodedados->query($query);
    // Cadastra Telefones
    $telefones = $this->buscaTelefones();
    if ($telefones) {
      $this->cadastraTelefones($cliente_id, $telefones);
    }
  }

  function cadastraNovoCliente() {
    if (!($bloco_id = $this->buscaBloco())) {
      $bloco_id = $this->cadastraNovoBloco();
    }
    // Insere Novo Cliente
    $query = "
      INSERT INTO
        tab_clientes (cli_nome, cli_data_cadastro, cob_id, cli_complemento, cls_id)
      VALUES ('{$_GET["nome"]}', NOW(), $bloco_id, '{$_GET["complemento"]}', 4)";
    $this->bancodedados->query($query);
    $cliente_id = $this->bancodedados->ultimoIdInserido();
    // Cadastra Telefones
    $telefones = $this->buscaTelefones();
    if ($telefones) {
      $this->cadastraTelefones($cliente_id, $telefones);
    }
    // Retorna ID do Cliente
    return $cliente_id;
  }

  function buscaBloco() {
    $query = "
      SELECT
        cob_id AS id
      FROM
        tab_condominios_blocos
      WHERE
        cep_id = {$_GET["cep"]} AND
        cob_numero = {$_GET["numero"]} AND
        cob_nome = '{$_GET["bloco"]}'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    } else {
      $query = "
        SELECT
          cob_id AS id
        FROM
          tab_condominios_blocos
          INNER JOIN tab_condominios ON (tab_condominios_blocos.con_id = tab_condominios.con_id)
        WHERE
          tab_condominios.cep_id = {$_GET["cep"]} AND
          tab_condominios.con_numero = {$_GET["numero"]} AND
          tab_condominios_blocos.cob_nome = '{$_GET["bloco"]}'";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
      if ($dados) {
        return $dados["id"];
      }
    }
  }

  function cadastraNovoBloco() {
    if (!($condominio_id = $this->buscaCondominio())) {
      $condominio_id = $this->cadastraCondominio();
    }
    $query = "
      INSERT INTO
        tab_condominios_blocos (cob_data_cadastro, cob_nome, con_id)
      VALUES (CURDATE(), '{$_GET["bloco"]}', $condominio_id)";
    $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
  }

  function buscaCondominio($somente_prospect = false) {
    $query = "
      SELECT
        con_id AS id
      FROM
        tab_condominios
      WHERE
        cep_id = {$_GET["cep"]} AND
        con_numero = {$_GET["numero"]}".($somente_prospect ? " AND cos_id = 1" : "");
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  function cadastraCondominio() {
    $query = "
      INSERT INTO
        tab_condominios (con_data_cadastro, con_nome, cep_id, con_numero, cos_id, coi_id)
      VALUES (CURDATE(), '{$_GET["condominio"]}', {$_GET["cep"]}, {$_GET["numero"]}, 1, 1)";
    $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
  }

  function buscaTelefones() {
    if ($_GET["telefone_residencial"]) {
      $telefones[] = $this->trataTelefone($_GET["telefone_residencial"], 1);
    }
    if ($_GET["telefone_comercial"]) {
      $telefones[] = $this->trataTelefone($_GET["telefone_comercial"], 2);
    }
    if ($_GET["telefone_celular"]) {
      $telefones[] = $this->trataTelefone($_GET["telefone_celular"], 3);
    }
    return $telefones;
  }

  function trataTelefone($telefone, $tipo_id) {
    $telefone_tratado["ddd"] = substr($telefone, 1, 2);
    $telefone_tratado["numero"] = substr($telefone, 5);
    $telefone_tratado["tipo_id"] = $tipo_id;
    return $telefone_tratado;
  }

  function cadastraTelefones($cliente_id, $telefones) {
    foreach ($telefones as $telefone) {
      if (!$this->buscaTelefone($cliente_id, $telefone)) {
        $query = "
          INSERT INTO
            tab_clientes_contatos_telefones (ccl_ddd, ccl_numero, cli_id, ccf_id)
          VALUES ('{$telefone["ddd"]}', '{$telefone["numero"]}', $cliente_id, {$telefone["tipo_id"]})";
        $this->bancodedados->query($query);
      }
    }
  }

  function buscaTelefone($cliente_id, $telefone) {
    $query = "
      SELECT
        ccl_id AS id
      FROM
        tab_clientes_contatos_telefones
      WHERE
        cli_id = $cliente_id AND
        ccl_ddd = {$telefone["ddd"]} AND
        ccl_numero = {$telefone["numero"]}";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["id"];
    }
  }

  function corrigeNome($nome) {
    $minusculas = array("da", "de", "do", "das", "dos", "e");
    $maiusculas = array("LTDA", "SC", "S/C", "SA", "S/A", "ME");
    $nome = str_replace(".", ". ", $nome); // Adiciona espaço depois do ponto de abreviação
    $nome = preg_replace("/[\r\n\t ]+/", " ", $nome); // Remove espaços seguidos
    if (strtoupper($nome) == $nome || strtolower($nome) == $nome) {
      $nome = strtolower($nome); // Coloca todos os caracteres em minúsculo
      $nome = ucwords($nome); // Coloca as primeiras letras de cada nome em maiúsculo
    }
    for ($i = 0; $i < sizeof($minusculas); $i++) $nome = str_ireplace(" {$minusculas[$i]} ", " {$minusculas[$i]} ", " $nome "); // Substitui termos minúsculos
    for ($i = 0; $i < sizeof($maiusculas); $i++) $nome = str_ireplace(" {$maiusculas[$i]} ", " {$maiusculas[$i]} ", " $nome "); // Substitui termos maiúsculos
    $nome = trim($nome); // Remove excessos do início e do fim
    return $nome;
  }

  function registraRetorno($cliente_id) {
    // Trata Dados
    $comentarios[] = "Cliente cadastrou-se através do site";
    if ($_GET["plano"]) $comentarios[] = "Plano: {$_GET["plano"]}";
    if ($_GET["observacoes"]) $comentarios[] = "Comentários: {$_GET["observacoes"]}";
    $comentarios = implode("\n", $comentarios);
    // Busca Supervisores Comerciais
    $query = "
	    SELECT
	      tab_usuarios.usu_id AS id
	    FROM
	      tab_usuarios
	      INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
	    WHERE
	      rel_usu_gru_fazparte.gru_id = 13 AND
	      tab_usuarios.uss_id = 1";
    $query = $this->bancodedados->query($query);
    // Insere Retornos
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $query = "INSERT INTO tab_clientes_retornos (cli_id, usu_id, clr_abertura, clr_data, clr_comentario, cet_id, ces_id) VALUES ($cliente_id, {$dados["id"]}, NOW(), CURDATE(), '$comentarios', 2, 1)";
      $this->bancodedados->query($query);
    }
  }

  ####################################
  ### Registra Condomínio Prospect ###
  ####################################

  function registra_condominio_prospect() {
  	$condominio_id = $this->buscaCondominio(true);
    if ($condominio_id) {
      $this->conteudo = 1;
    } else {
      $this->conteudo = 0;
    }
  }

}

?>
