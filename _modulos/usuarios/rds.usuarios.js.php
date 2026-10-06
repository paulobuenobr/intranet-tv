<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
  if ($_GET["a"] == "verifica_usuario_login" && !empty($_GET["login"])) {
  	
    $query = "SELECT usu_id AS id, usu_nome AS nome FROM tab_usuarios WHERE usu_login = '{$_GET["login"]}' AND usu_id <> '{$_GET["usuario"]}'";
    $query = $bancodedados->query($query);
    $usuario = $bancodedados->resultadoArray($query);
    if ($usuario) {
      $mensagem = "O login \"{$_GET["login"]}\" já está sendo utilizado pelo usuário \"" . addslashes($usuario["nome"]) . "\".";
      print "if (document.getElementById(\"login\") != null) { document.getElementById(\"login\").value = ''; document.getElementById(\"login\").focus(); }";
      print "alert('$mensagem');";
    }
    
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function verificaUsuarioMesmoLogin(campo) {
  login = campo.value;
  if (login) {
    url = "rds.js.php?m=usuarios&a=verifica_usuario_login&login=" + login + "&usuario=" + usuario_id;
    RDSCarregar("usuarios_verifica_usuario_login", url);
  }
}

function validaUsuarioDadosCadastrais(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.origem && !formulario.origem.value) erro_vazio[erro_vazio.length] = "Origem";
  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";
  if (formulario.cpf.value && !validaCPF(formulario.cpf.value)) erro_invalido[erro_invalido.length] = "CPF";
  if (formulario.nascimento.value && !validaData(formulario.nascimento.value)) erro_invalido[erro_invalido.length] = "Nascimento";
  if (!formulario.cep.value) {
    //erro_vazio[erro_vazio.length] = "Endereço » CEP";
  } else {
    if (formulario.cep.value && !cep_valido) erro_invalido[erro_invalido.length] = "Endereço » CEP";
  }
  if (formulario.numero.value && isNaN(formulario.numero.value)) erro_invalido[erro_invalido.length] = "Endereço » Número";

  var telefones = 0;
  if (formulario.tel_residencial.value) {
    if (!validaTelefone(formulario.tel_residencial.value)) erro_invalido[erro_invalido.length] = "Telefone » Residencial";
    telefones++;
  }
  if (formulario.tel_celular.value) {
    if (!validaTelefone(formulario.tel_celular.value)) erro_invalido[erro_invalido.length] = "Telefone » Celular";
    telefones++;
  }
  if (telefones == 0) erro_vazio[erro_vazio.length] = "Telefone (qualquer)";

  if (formulario.email.value && !validaEmail(formulario.email.value)) erro_invalido[erro_invalido.length] = "E-mail";
  if (!formulario.login.value) erro_vazio[erro_vazio.length] = "Login";
  if (formulario.name == "adicionar" && !formulario.senha.value) erro_vazio[erro_vazio.length] = "Senha";
  if (formulario.senha.value != formulario.senha_confirmacao.value) erro_invalido[erro_invalido.length] = "Senha";

  return apresentaErros(erro_vazio,erro_invalido);

}

function validaUsuarioDadosPessoais (formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";
  if (formulario.cpf.value && !validaCPF(formulario.cpf.value)) erro_invalido[erro_invalido.length] = "CPF";
  if (formulario.nascimento.value && !validaData(formulario.nascimento.value)) erro_invalido[erro_invalido.length] = "Nascimento";
  if (!formulario.cep.value) {
    erro_vazio[erro_vazio.length] = "Endereço » CEP";
  } else {
    if (formulario.cep.value && !cep_valido) erro_invalido[erro_invalido.length] = "Endereço » CEP";
  }
  if (formulario.numero.value && isNaN(formulario.numero.value)) erro_invalido[erro_invalido.length] = "Endereço » Número";

  var telefones = 0;
  if (formulario.tel_residencial.value) {
    if (!validaTelefone(formulario.tel_residencial.value)) erro_invalido[erro_invalido.length] = "Telefone » Residencial";
    telefones++;
  }
  if (formulario.tel_celular.value) {
    if (!validaTelefone(formulario.tel_celular.value)) erro_invalido[erro_invalido.length] = "Telefone » Celular";
    telefones++;
  }
  if (telefones == 0) erro_vazio[erro_vazio.length] = "Telefone (qualquer)";

  if (formulario.email.value && !validaEmail(formulario.email.value)) erro_invalido[erro_invalido.length] = "E-mail";
  if (formulario.name == "adicionar" && !formulario.senha.value) erro_vazio[erro_vazio.length] = "Senha";
  if (formulario.senha.value != formulario.senha_confirmacao.value) erro_invalido[erro_invalido.length] = "Senha";

  return apresentaErros(erro_vazio,erro_invalido);

}

<?

if (false) { ?></script><? }

}

?>
