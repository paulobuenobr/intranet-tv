<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios_atividades = controle::instanciaObjeto("condominios_atividades", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "busca_atividades" && $_GET["con"]) {

    $atividades = $condominios_atividades->buscaCondominioAtividades($_GET["con"]);
    print "if (document.getElementById(\"atividades\")) document.getElementById(\"atividades\").innerHTML = '".addslashes($atividades)."';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaAtividade(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();
  if (!formulario.responsavel.value) {
    erro_vazio[erro_vazio.length] = "Responsavel";
  }
  if (!formulario.inicio.value) {
    erro_vazio[erro_vazio.length] = "Início";
  } else if (!validaData(formulario.inicio.value)) {
    erro_invalido[erro_invalido.length] = "Início";
  }
  if (!formulario.termino.value) {
    erro_vazio[erro_vazio.length] = "Término";
  } else if (!validaData(formulario.termino.value)) {
    erro_invalido[erro_invalido.length] = "Término";
  }
  if (formulario.inicio.value && validaData(formulario.inicio.value) && formulario.termino.value && validaData(formulario.termino.value) && subtraiDatas(formulario.termino.value, formulario.inicio.value) < 0) {
    erro_invalido[erro_invalido.length] = "Início maior que Término";
  }
  if (buscaAtividadesSelecionadas() == 0) {
    erro_vazio[erro_vazio.length] = "Atividades (Pelo menos uma)";
  }
  return apresentaErros(erro_vazio, erro_invalido);
}

function buscaAtividadesSelecionadas() {
  var i = 1;
  var count = 0;
  while (atividade = document.getElementById('atividade_' + i)) {
    if (atividade.checked) count++;
    i++;
  }
  return count;
}

function buscaAtividades(condominio) {
  if (condominio) {
	  url = "rds.js.php?m=condominios_atividades&a=busca_atividades&con=" + condominio;
    RDSCarregar("condominios_atividades", url);
	}
}

function verAtividade(atividade) {
	if (atividade) {
    abrir("atividade", "?m=condominios_atividades&a=ver&i=" + atividade, 450, 370, 1);
	}
}

function adicionarAtividade(condominio) {
	if (condominio) {
    abrir("atividade", "?m=condominios_atividades&a=adicionar&c=" + condominio, 450, 370, 1);
	}
}

<?

	if (false) { ?></script><? }

}

?>
