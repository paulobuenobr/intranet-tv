<?

class integracao {

  var $bancodedados;
  var $conteudo;
  var $modulo;
  var $acao;
  var $id;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->extraiParametrosGET();
    if (!INTEGRACAO_DISPONIVEL) {
      $this->erro(0);
    } elseif ($this->modulo != "recadastramento" && INTEGRACAO_ENDERECO_ORIGEM != $_SERVER["REMOTE_ADDR"]) {
      $this->erro(1);
    } elseif (!method_exists($this, $this->modulo)) {
      $this->erro(2);
    }
    $this->executa();
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

  function extraiParametrosGET() {
    if ($_GET["link"] && $parametros = $this->buscaParamentrosMensagensLinks($_GET["link"])) {
      $this->modulo = $_GET["m"] = $parametros["modulo"];
      $this->acao = $_GET["a"] = $parametros["acao"];
      $this->id = $_GET["i"] = $parametros["id"];
    } else {
      $this->modulo = $_GET["m"];
      $this->acao = $_GET["a"];
      $this->id = $_GET["i"];
    }
    $this->registraAcessoMensagemLink();
  }

  function buscaParamentrosMensagensLinks($link) {
    $query = "
      SELECT
        mei_url_modulo AS modulo,
        mei_url_acao AS acao,
        mei_url_id AS id
      FROM
        tab_mensagens_links
      WHERE
        mei_md5 = '$link'";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function registraAcessoMensagemLink() {
    $query = "
      SELECT
        mei_id AS id,
        men_id AS mensagem_id
      FROM
        tab_mensagens_links
      WHERE
        mei_url_modulo = '{$this->modulo}' AND
        mei_url_acao = '{$this->acao}' AND
        mei_url_id = '{$this->id}'";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      // Registra Data/Hora Visualização
      $query = "UPDATE tab_mensagens_links SET mei_datahora_visualizacao = NOW() WHERE mei_id = {$dados["id"]} AND mei_datahora_visualizacao IS NULL";
      $this->bancodedados->query($query);
      // Insere Comentário na Mensagem
      $query = "INSERT INTO tab_mensagens_comentarios (mec_datahora, mec_conteudo, men_id, mcc_id, usu_id) VALUES (NOW(), 'Link /?m={$this->modulo}&a={$this->acao}&i={$this->id} (IP {$this->ip_cliente})', '{$dados["id"]}', '3', NULL)";
      $this->bancodedados->query($query);
    }
  }

  ############
  ### Erro ###
  ############

  function erro($id = null, $mensagem = null) {
    $mensagem_erro = $mensagem ? $mensagem : $this->buscaMensagemErro($id);
    $conteudo = $this->carregaArquivo("integracao.erro.inc.php");
    $conteudo = str_replace("[mensagem]", $mensagem_erro, $conteudo);
    return $conteudo;
  }

  function buscaMensagemErro($id) {
    // Integração
    $mensagens[-1] = "Erro desconhecido!";
    $mensagens[0] = "Sistema não disponível no momento!";
    $mensagens[1] = "Endereço de origem não autorizado!";
    $mensagens[2] = "Módulo não disponível!";
    $mensagens[3] = "Ação não disponível!";
    // Contratos
    $mensagens[11] = "Contrato inexistente ou não disponível para visualização!";
    $mensagens[12] = "Contrato não disponível para análise!";
    // Cobranças
    $mensagens[21] = "Cobrança inexistente ou não disponível para visualização!";
    // Migração
    $mensagens[31] = "Código do cliente inválido ou inexistente!";
    // Trata ID
    $id = array_key_exists($id, $mensagens) ? $id : -1;
    // Retorna Mensagem
    return $mensagens[$id];
  }

  ###############
  ### Executa ###
  ###############

  function executa() {
    ob_start();
    $modulo = controle::instanciaObjeto("integracao.{$this->modulo}", $this->bancodedados);
    $conteudo = ob_get_contents();
    ob_end_clean();
  	$this->registraLog($this->modulo, $this->acao, $this->id, $_GET["usuario"]);
    if ($conteudo) {
      $this->conteudo = $this->erro(null, $conteudo);
    } elseif ($modulo->erro) {
      $this->conteudo = $this->erro($modulo->erro);
    } else {
      $this->conteudo = $modulo->conteudo;
    }
    if ($this->acao == "imprimir" || $this->acao == "imprimir_vistoria" || $this->modulo == "site" || $this->modulo == "recadastramento" || substr($this->modulo, 0, 7) == "painel_") {
      die($this->conteudo);
    }
  }
  
  function registraLog($modulo, $acao, $id, $usuario) {
  	$modulo = $modulo ? "'$modulo'" : "NULL";
  	$acao = $acao ? "'$acao'" : "NULL";
  	$id = $id ? "'$id'" : "NULL";
  	$usuario = $usuario ? "'$usuario'" : "NULL";
  	$query = "INSERT INTO log_integracao (modulo, acao, id, usuario, datahora) VALUES ($modulo, $acao, $id, $usuario, NOW())";
    $this->bancodedados->query($query);
  }

}

?>
