<?

set_time_limit(0);

class clientes_emails_ldap {

  var $usuario;
  var $senha;
  var $dominio;
  var $tamanho;
  var $comentario;
  var $status;

  function clientes_emails_ldap() {
    $this->usuario = "";
    $this->senha = "";
    $this->dominio = "";
    $this->tamanho = "";
    $this->comentario = "";
    $this->status = "";
  }

  function verificarEmail($usuario, $dominio) {
    $this->acao = "verificar";
    $this->usuario = $usuario;
    $this->dominio = $dominio;
    return $this->executar();
  }

  function adicionarEmail($usuario, $dominio, $senha, $tamanho, $comentario, $status) {
  	$comentario = str_replace(array("^", "'", "`", "´"), "", $comentario);
    $this->acao = "adicionar";
    $this->usuario = $usuario;
    $this->senha = $senha ? crypt($senha) : "";
    $this->dominio = $dominio;
    $this->tamanho = $tamanho;
    $this->comentario = urlencode($comentario ? $comentario : $usuario);
    $this->status = $status;
    return $this->executar();
  }

  function editarEmail($usuario, $dominio, $senha, $tamanho, $comentario, $status) {
  	$comentario = str_replace(array("^", "'", "`", "´"), "", $comentario);
  	$this->acao = "editar";
    $this->usuario = $usuario;
    $this->senha = $senha ? crypt($senha) : "";
    $this->dominio = $dominio;
    $this->tamanho = $tamanho;
    $this->comentario = urlencode($comentario ? $comentario : $usuario);
    $this->status = $status;
    return $this->executar();
  }

  function removerEmail($usuario, $dominio) {
    $this->acao = "remover";
    $this->usuario = $usuario;
    $this->dominio = $dominio;
    return $this->executar();
  }

  function buscarDominios() {
    $this->acao = "buscar_dominios";
    return unserialize($this->executar());
  }

  function buscarUsuariosPorDominio($dominio) {
    $this->acao = "buscar_usuarios_por_dominio";
    $this->dominio = $dominio;
    return unserialize($this->executar());
  }

  function executar() {
    if ($this->acao) {
      $sessao = curl_init();
      curl_setopt($sessao, CURLOPT_TIMEOUT, 10);
      curl_setopt($sessao, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($sessao, CURLOPT_URL, "http://intranet:cvtqa0spep@intranet.mailcorp.wavesystem.com.br/");
      curl_setopt($sessao, CURLOPT_POST, TRUE);
      curl_setopt($sessao, CURLOPT_POSTFIELDS, "acao={$this->acao}&usuario={$this->usuario}&senha={$this->senha}&dominio={$this->dominio}&tamanho={$this->tamanho}&comentario={$this->comentario}&status={$this->status}");
      $retorno = curl_exec($sessao);
      curl_close($sessao);
      return preg_match("/R,\\-?[0-9]+/", $retorno) ? intval(substr($retorno, 2)) : 0;
    }
  }

}

?>
