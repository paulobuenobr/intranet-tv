<?

set_time_limit(0);

/*

date_default_timezone_set('America/Sao_paulo');

require_once("../../global.inc.php");

$cron = controle::instanciaObjeto("cron_clientes_emails", $bancodedados);
$cron->publicaClientesEmails();

*/

class cron_clientes_emails {

	var $bancodedados;
	var $clientes_emails;

	function __construct($bancodedados) {
		$this->bancodedados = $bancodedados;
		$this->clientes_emails = controle::instanciaObjeto("clientes_emails", $this->bancodedados, null, false);
	}

	###############################
	### Publica Clientes Emails ###
	###############################

	function publicaClientesEmails() {
	  // Agenda Remoção de Cancelados Há Mais de 1 Mês
	  $this->atualizaStatusCancelados();
	  // Publica E-mails Não-Publicados
	  $clientes_emails = $this->clientes_emails->buscaClientesEmailsNaoPublicados(100);
    if ($clientes_emails) {
      $this->clientes_emails->publicaClientesEmails(array_keys($clientes_emails));
    }
	}

	function atualizaStatusCancelados() {
	  $query = "
	    SELECT
	      cle_id AS id,
	      dom_id AS dominio_id,
	      cle_usuario AS usuario
	    FROM
	      tab_clientes_emails
	    WHERE
	      ces_id = 3 AND
	      DATE_ADD(cle_cancelamento, INTERVAL 1 MONTH) <= NOW()";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      if ($this->buscaEmailAtivo($dados["dominio_id"], $dados["usuario"])) {
        $query_update = "UPDATE tab_clientes_emails SET ces_id = 4, cle_publicado = 1 WHERE cle_id = {$dados["id"]}";
        $this->bancodedados->query($query_update);
      } else {
        $query_update = "UPDATE tab_clientes_emails SET ces_id = 4, cle_publicado = 0 WHERE cle_id = {$dados["id"]}";
        $this->bancodedados->query($query_update);
      }
    }
	}

	function buscaEmailAtivo($dominio_id, $usuario) {
	  $query = "
	    SELECT
	      COUNT(cle_id) AS emails
	    FROM
	      tab_clientes_emails
	    WHERE
	      dom_id = $dominio_id AND
	      cle_usuario = '$usuario' AND
	      ces_id IN (1, 2)";
    $query = $this->bancodedados->query($query);
    $dados = $this->bancodedados->resultadoArray($query);
    if ($dados) {
      return $dados["emails"];
    }
	}

	##############################
	### Remove Clientes Emails ###
	##############################

	function sincronizaClientesEmails() {
	  if (!$this->clientes_emails_ldap) {
	    $this->clientes_emails_ldap = controle::instanciaObjeto("clientes_emails.ldap", $this->bancodedados);
	  }
	  $dominios = $this->clientes_emails_ldap->buscarDominios();
	  if ($dominios) {
  	  foreach ($dominios as $dominio) {
  	  	$usuarios = $this->clientes_emails_ldap->buscarUsuariosPorDominio($dominio);
  	    if ($usuarios) {
    	    foreach ($usuarios as $usuario) {
    	    	$dados_email = $this->clientes_emails->buscaDadosClienteEmailPorUsuarioPorDominioNome($usuario, $dominio);
    	      if ($dados_email) {
    	        if ($dados_email["status_id"] == 3) {
                if (strtotime("{$dados_email["atualizacao"]} 1 MONTH") < time()) {
    	            $this->clientes_emails_ldap->removeUsuario($usuario, $dominio);
                  $this->clientes_emails->registraComentario($dados_email["id"], "<b>Ação:</b> Removido do Servidor<br>", $_SESSION["usuario"]);
                } else {
    	            $this->clientes_emails_ldap->editarUsuario($usuario, $dominio, null, null, null, 3);
                  $this->clientes_emails->registraComentario($dados_email["id"], "<b>Ação:</b> Suspenso no Servidor<br>", $_SESSION["usuario"]);
                }
                $this->clientes_emails->registraClienteEmailPublicacao($dados_email["id"]);
    	        }
    	      } else {
    	        //$this->clientes_emails_ldap->removeUsuario($usuario, $dominio);
              $this->clientes_emails_ldap->editarUsuario($usuario, $dominio, null, null, null, 3);
    	      }
    	    }
  	    }
  	  }
	  }
	}

}

?>
