<?

class integracao_recadastramento {

  var $bancodedados;
  var $conteudo;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->acao = $_GET["a"];
    if (method_exists($this, $this->acao)) {
			$this->conteudo = $this->{$this->acao}();
    } else {
			$this->erro = 3;
    }
  }

  ########################
  ### Funções de Busca ###
  ########################

  function registraMac() {
    $query = "
      SELECT
        id
      FROM
        recadastramento_macs
      WHERE
       cliente_mac = '{$_GET["cliente_mac"]}'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $query = "UPDATE recadastramento_macs SET cliente_ip = '{$_GET["cliente_ip"]}', quantidade = quantidade + 1, data_hora = NOW() WHERE id = {$dados["id"]}";
    } else {
      $query = "INSERT INTO recadastramento_macs (cliente_mac, cliente_ip, quantidade, data_hora) VALUES ('{$_GET["cliente_mac"]}', '{$_GET["cliente_ip"]}', 1, NOW())";
    }
    $this->bancodedados->query($query);
  }

  function registraOpcao1() {
  	$cliente_id = $this->buscaClienteNossoNumero($_GET["opcao_1_numero_boleto"]);
    if ($cliente_id) {
      // Trata Dados
      $_GET["opcao_1_data_nascimento"] = substr($_GET["opcao_1_data_nascimento"], 6, 4)."-".substr($_GET["opcao_1_data_nascimento"], 3, 2)."-".substr($_GET["opcao_1_data_nascimento"], 0, 2);
      // Insere Dados
      $query = "
        SELECT
          rec_id AS id
        FROM
          recadastramento
        WHERE
          cliente_id = '$cliente_id' AND
          rec_cliente_ip = '{$_GET["cliente_ip"]}' AND
          rec_cliente_mac = '{$_GET["cliente_mac"]}'";
      $query = $this->bancodedados->query($query);
      $dados = $this->bancodedados->resultadoArray($query);
      if ($dados) {
        $query = "
          UPDATE
            recadastramento
          SET
            cliente_id = $cliente_id,
            rec_nosso_numero = '{$_GET["opcao_1_numero_boleto"]}',
            rec_nome = NULL,
            rec_nascimento = '{$_GET["opcao_1_data_nascimento"]}',
            rec_condominio = NULL,
            rec_bloco = NULL,
            rec_complemento = NULL,
            rec_telefones = '{$_GET["opcao_1_telefones"]}',
            rec_emails = '{$_GET["opcao_1_emails"]}',
            rec_comentario = '{$_GET["opcao_1_comentario"]}',
            rec_cliente_mac = '{$_GET["cliente_mac"]}',
            rec_cliente_ip = '{$_GET["cliente_ip"]}',
            rec_data_hora = NOW()
          WHERE
            rec_id = {$dados["id"]}";
        $this->bancodedados->query($query);
      } else {
        $query = "
          INSERT INTO
            recadastramento (cliente_id, rec_nosso_numero, rec_nascimento, rec_telefones, rec_emails, rec_comentario, rec_cliente_mac, rec_cliente_ip, rec_data_hora)
          VALUES
            ($cliente_id, '{$_GET["opcao_1_numero_boleto"]}', '{$_GET["opcao_1_data_nascimento"]}', '{$_GET["opcao_1_telefones"]}', '{$_GET["opcao_1_emails"]}', '{$_GET["opcao_1_comentario"]}', '{$_GET["cliente_mac"]}', '{$_GET["cliente_ip"]}', NOW())";
        $this->bancodedados->query($query);
      }
      // Retorna OK
      return "[ok]";
    } else {
      // Retorna Erro
      return "Valor inválido para o campo:<br>&laquo;Últimos 7 dígitos do Nosso Número&raquo;";
    }
  }

  function registraOpcao2() {
    // Trata Dados
    $_GET["opcao_2_data_nascimento"] = substr($_GET["opcao_2_data_nascimento"], 6, 4)."-".substr($_GET["opcao_2_data_nascimento"], 3, 2)."-".substr($_GET["opcao_2_data_nascimento"], 0, 2);
    // Insere Dados
    $query = "
      SELECT
        rec_id AS id
      FROM
        recadastramento
      WHERE
        cliente_id IS NULL AND
        rec_cliente_ip = '{$_GET["cliente_ip"]}' AND
        rec_cliente_mac = '{$_GET["cliente_mac"]}'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      $query = "
        UPDATE
          recadastramento
        SET
          cliente_id = NULL,
          rec_nosso_numero = NULL,
          rec_nome = '{$_GET["opcao_2_nome"]}',
          rec_nascimento = '{$_GET["opcao_2_data_nascimento"]}',
          rec_condominio = '{$_GET["opcao_2_condominio"]}',
          rec_bloco = '{$_GET["opcao_2_bloco"]}',
          rec_complemento = '{$_GET["opcao_2_apto"]}',
          rec_telefones = '{$_GET["opcao_2_telefones"]}',
          rec_emails = '{$_GET["opcao_2_emails"]}',
          rec_comentario = '{$_GET["opcao_2_comentario"]}',
          rec_cliente_mac = '{$_GET["cliente_mac"]}',
          rec_cliente_ip = '{$_GET["cliente_ip"]}',
          rec_data_hora = NOW()
        WHERE
          rec_id = {$dados["id"]}";
      $this->bancodedados->query($query);
    } else {
      $query = "
        INSERT INTO
          recadastramento (rec_nome, rec_nascimento, rec_condominio, rec_bloco, rec_complemento, rec_telefones, rec_emails, rec_comentario, rec_cliente_mac, rec_cliente_ip, rec_data_hora)
        VALUES
          ('{$_GET["opcao_2_nome"]}', '{$_GET["opcao_2_data_nascimento"]}', '{$_GET["opcao_2_condominio"]}', '{$_GET["opcao_2_bloco"]}', '{$_GET["opcao_2_apto"]}', '{$_GET["opcao_2_telefones"]}', '{$_GET["opcao_2_emails"]}', '{$_GET["opcao_2_comentario"]}', '{$_GET["cliente_mac"]}', '{$_GET["cliente_ip"]}', NOW())";
      $this->bancodedados->query($query);
    }
    // Retorna OK
    return "[ok]";
  }

  ######################
  ### Acesso a Dados ###
  ######################

  function buscaClienteNossoNumero($nosso_numero) {
    $query = "
      SELECT
        cli_id AS cliente_id,
        coa_parametros AS parametros
      FROM
        tab_cobrancas
      WHERE
        cli_id = ".(intval(substr($nosso_numero, 0, 5), 10))." AND
        coa_codigo LIKE '070_000".substr($nosso_numero, 0, 6)."'";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $dados["parametros"] = unserialize($dados["parametros"]);
      if (substr(preg_replace("/[^0-9]/", "", $dados["parametros"]["boleto"]["nosso_numero"]), -7) == $nosso_numero) {
        return $dados["cliente_id"];
      }
    }
  }

}

?>
