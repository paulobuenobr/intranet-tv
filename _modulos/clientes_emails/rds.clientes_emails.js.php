<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$clientes_emails = controle::instanciaObjeto("clientes_emails", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "busca_clientes_emails_cliente" && $_GET["cliente"]) {

    $listagem = $clientes_emails->geraClientesEmailsClienteListagem($_GET["cliente"]);
    print "if (document.getElementById(\"clientes_emails_listagem\")) { document.getElementById(\"clientes_emails_listagem\").innerHTML = '".addslashes($listagem->conteudo)."'; }";
    $adicionar = $clientes_emails->geraClientesEmailsClienteAdicionar($_GET["cliente"]);
    print "if (document.getElementById(\"clientes_emails_adicionar\")) { document.getElementById(\"clientes_emails_adicionar\").innerHTML = '".addslashes($adicionar)."'; }";

  } elseif ($_GET["a"] == "atualiza_cliente_email_assinatura") {

    if ($_GET["assinatura"] && ($dominios = $clientes_emails->buscaDominiosPorAssinatura($_GET["assinatura"]))) {
      foreach ($dominios AS $id => $nome) {
        $lista_dominios[] = "new Array(\"$id\", \"$nome\")";
      }
      print "var dominios = new Array(".@implode(",", $lista_dominios).");";
      print "atualizaSelect(document.getElementById(\"dominio\"), dominios);";
      $capacidades = $clientes_emails->buscaCapacidades($clientes_emails->buscaCapacidadeMaxima($_GET["assinatura"]));
      if ($capacidades) {
        foreach ($capacidades AS $id => $nome) {
          $lista_capacidades[] = "new Array(\"$id\", \"$nome\")";
        }
      }
      print "var capacidades = new Array(".@implode(",", $lista_capacidades).");";
      print "atualizaSelect(document.getElementById(\"capacidade\"), capacidades, ".$clientes_emails->buscaCapacidadePadrao($_GET["assinatura"]).");";
      print "document.getElementById(\"usuario\").disabled = false;";
      print "document.getElementById(\"dominio\").disabled = false;";
    } else {
      print "document.getElementById(\"usuario\").disabled = true;";
      print "document.getElementById(\"dominio\").disabled = true;";
      print "document.getElementById(\"capacidade\").disabled = true;";
      print "document.getElementById(\"usuario\").value = '';";
      print "document.getElementById(\"dominio\").value = '';";
      print "document.getElementById(\"capacidade\").value = '';";
    }

  } elseif ($_GET["a"] == "verifica_cliente_email_usuario" && $_GET["usuario"] && $_GET["dominio"] && $_GET["cliente"]) {

    $mensagem = $clientes_emails->geraClientesEmailsMensagemVerificacaoUsuario($_GET["usuario"], $_GET["dominio"], $_GET["cliente"], $_GET["cliente_email"]);
    print "var mensagem = '".addslashes($mensagem)."';";
    print "if (mensagem) { alert(mensagem); document.getElementById(\"usuario\").value = ''; document.getElementById(\"usuario\").focus(); }";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaClienteEmail(formulario) {
  var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.senha.value && (cliente_id == 62291 || cliente_id == 3000)) {
	  erro_invalido[erro_invalido.length] = "Senha - Para alterar a senha migre o e-mail para o cadastro do cliente";
	}
	if (formulario.assinatura && !formulario.assinatura.value) erro_vazio[erro_vazio.length] = "Assinatura";
  if (formulario.usuario) {
  	if (!formulario.usuario.value) {
      erro_vazio[erro_vazio.length] = "Usuário";
    } else if (!validaLogin(formulario.usuario.value)) {
      erro_invalido[erro_invalido.length] = "Usuário";
    }
  }
  if (formulario.name == "adicionar") if (!formulario.senha.value) erro_vazio[erro_vazio.length] = "Senha";
	if (!formulario.capacidade.value) erro_vazio[erro_vazio.length] = "Capacidade";
	if (!formulario.tipo.value) erro_vazio[erro_vazio.length] = "Tipo";
	return apresentaErros(erro_vazio, erro_invalido);
}

function adicionarClienteEmail(cliente) {
  if (cliente) {
  	abrir("clientes_emails", "?m=clientes_emails&a=adicionar&i=" + cliente, 525, 450, 1);
  }
}

function verClienteEmail(cliente_email) {
  if (cliente_email) {
  	abrir("clientes_emails", "?m=clientes_emails&a=ver&i=" + cliente_email, 525, 450, 1);
  }
}

function buscaClientesEmailsCliente(cliente) {
  if (cliente) {
		url = "rds.js.php?m=clientes_emails&a=busca_clientes_emails_cliente&cliente=" + cliente;
		RDSCarregar("clientes_emails_busca_clientes_emails", url);
  }
}

function atualizaClienteEmailAssinatura(campo) {
  url = "rds.js.php?m=clientes_emails&a=atualiza_cliente_email_assinatura&assinatura=" + campo.form.assinatura.value;
  RDSCarregar("clientes_emails_atualiza_cliente_email_assinatura", url);
}

function verificaClienteEmailUsuario(campo) {
  url = "rds.js.php?m=clientes_emails&a=verifica_cliente_email_usuario&usuario=" + campo.form.usuario.value + "&dominio=" + campo.form.dominio.value + "&cliente=" + cliente_id + "&cliente_email=" + cliente_email_id;
  RDSCarregar("clientes_emails_verifica_cliente_email_usuario", url);
}

function gerarClienteEmailSenha(campo) {
  if (campo) {
    var caracteres = "abcdefghijklmnopqrstuvwxyz0123456789";
    var senha_aleatoria = "";
    while (senha_aleatoria.length < 8) {
      senha_aleatoria += caracteres.charAt(Math.floor(Math.random()*caracteres.length));
    }
    campo.value = senha_aleatoria;
  }
}

<?

	if (false) { ?></script><? }

}

?>
