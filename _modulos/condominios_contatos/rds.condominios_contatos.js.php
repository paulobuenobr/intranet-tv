<?

if ($_GET["a"]) {
	
	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios_contatos = controle::instanciaObjeto("condominios_contatos", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "buscar_condominios_contatos" && $_GET["con"]) {

  	$contatos = $condominios_contatos->buscaCondominioContatos($_GET["con"]);
  	print "document.getElementById(\"condominios_contatos\").innerHTML = '".str_replace("'", '\\\'', $condominios_contatos->geraListagemCondominioContatos($contatos))."';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscaCondominioContatos(condominio) {
  if (condominio) {
	  url = "rds.js.php?m=condominios_contatos&a=buscar_condominios_contatos&con=" + condominio;
    RDSCarregar("condominio_contatos_buscar_condominios_contatos",url);
	}
}

function verContato(contato) {
	if (contato) {
    abrir("contato", "?m=condominios_contatos&a=ver&i=" + contato, 450, 500, 0);
	}
}

function adicionarContato(condominio) {
	if (condominio) {
    abrir("contato", "?m=condominios_contatos&a=adicionar&c=" + condominio, 450, 500, 0);
	}
}

function validaContato(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();
  if (!formulario.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }
  var telefones = 0;
  if (formulario.tel_residencial.value) {
    if (!validaTelefone(formulario.tel_residencial.value)) {
      erro_invalido[erro_invalido.length] = "Telefone » Residencial";
    }
    telefones = telefones + 1;
  }
  if (formulario.tel_comercial.value) {
    if (!validaTelefone(formulario.tel_comercial.value)) {
      erro_invalido[erro_invalido.length] = "Telefone » Comercial";
    }
    telefones = telefones + 1;
  }
  if (formulario.tel_celular.value) {
    if (!validaTelefone(formulario.tel_celular.value)) {
      erro_invalido[erro_invalido.length] = "Telefone » Celular";
    }
    telefones = telefones + 1;
  }
  if (telefones == 0) {
    erro_vazio[erro_vazio.length] = "Telefone (qualquer)";
  }
  if (formulario.email.value) {
    if (!validaEmail(formulario.email.value)) {
      erro_invalido[erro_invalido.length] = "E-mail";
    }
  }
  if (!formulario.tipo.value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }
  if (formulario.nascimento.value) {
    if (!validaData(formulario.nascimento.value)) {
      erro_invalido[erro_invalido.length] = "Nascimento";
    }
  }
  if (formulario.validade.value) {
    if (!validaData(formulario.validade.value)) {
      erro_invalido[erro_invalido.length] = "Validade";
    }
  }
  return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
