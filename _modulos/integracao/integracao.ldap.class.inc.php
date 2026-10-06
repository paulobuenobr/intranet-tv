<?php

class integracao_ldap {

  var $ldap_servidor;
  var $ldap_porta;
  var $ldap_usuario;
  var $ldap_senha;
  var $ldap_conexao;
  var $ldap_raiz;

  function __construct() {
    $this->ldap_servidor = "ldap.wavesystem.com.br";
    $this->ldap_usuario = "uid=intranet,ou=admins,ou=ISP";
    $this->ldap_senha = "cvtqa0spep";
    $this->ldap_porta = 389;
    $this->ldap_raiz = "ISP";
  }

  function conectar() {
    if (!$this->ldap_conexao = ldap_connect($this->ldap_servidor)) {
      return "Impossível conectar no servidor";
    }
    if (!ldap_set_option($this->ldap_conexao, LDAP_OPT_PROTOCOL_VERSION, 3)) {
      return "Impossível utilizar versão 3";
    }
    if (!ldap_bind($this->ldap_conexao, $this->ldap_usuario, $this->ldap_senha)) {
      return "Impossível autenticar no servidor";
    }
    return 0;
  }

  function desconectar() {
    ldap_close($this->ldap_conexao);
  }
  
  function autenticarUsuario($dominio, $usuario, $senha) {
  	$dados = $this->buscaDadosEmail($dominio, $usuario);
    if ($dados) {
      return $this->validarSenha($dados["userpassword"], $senha);
    } else {
      return false;
    }
  }

  function buscaDadosEmail($dominio, $usuario) {
    if (!$this->ldap_conexao) {
      $this->conectar();
    }
    $retorno = ldap_get_entries($this->ldap_conexao, ldap_read($this->ldap_conexao, "uid={$usuario},ou=contas,ou={$dominio},ou={$this->ldap_raiz}", "(|(objectclass=CourierMailAccount)(objectclass=CourierMailAlias))"));
    if ($retorno["count"]) {
      if (in_array("CourierMailAccount", $retorno[0]["objectclass"])) {
        $dados["conta"] = true;
        $dados["quota"] = intval(substr($retorno[0]["quota"][0], 0, -1), 10);
        $dados["accountstatus"] = intval($retorno[0]["accountstatus"][0], 10);
        $dados["userpassword"] = $retorno[0]["userpassword"][0];
        $dados["homedirectory"] = $retorno[0]["homedirectory"][0];
        $dados["homedefaultmail"] = $retorno[0]["homedefaultmail"][0];
        $dados["mailbox"] = $retorno[0]["mailbox"][0];
        $dados["admin"] = $retorno[0]["admin"][0];
      }
      if (in_array("CourierMailAlias", $retorno[0]["objectclass"])) {
        $dados["alias"] = true;
        for ($i = 0; $i < count($retorno[0]["maildrop"]) - 1; $i++) {
          if (trim($retorno[0]["maildrop"][$i]) != "{$usuario}@{$dominio}") {
            $dados["maildrop"][] = trim($retorno[0]["maildrop"][$i]);
          }
        }
        if ($dados["maildrop"]) {
          sort($dados["maildrop"]);
        }
      }
      $dados["description"] = $retorno[0]["description"][0];
    }
    if ($dados) {
      if ($dados["conta"] && $dados["alias"]) {
        $dados["tipo"] = 3;
      } elseif ($dados["conta"]) {
        $dados["tipo"] = 1;
      } else {
        $dados["tipo"] = 2;
      }
      return $dados;
    }
  }
  
  function validarSenha($senha, $teste) {
      if (strtoupper(substr($senha, 1, 5)) == "CRYPT") {
        return crypt($teste, substr($senha, 7)) == substr($senha, 7);
      } else {
        return substr($senha, 5) == base64_encode(md5($teste, true));
      }
  }
  
}

?>
