<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios_responsabilidades = controle::instanciaObjeto("condominios_responsabilidades", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "busca_condominios_responsabilidades" && $_GET["con"]) {

    $responsabilidades = $condominios_responsabilidades->geraListagemCondominiosResponsabilidades($condominios_responsabilidades->buscaCondominiosResponsabilidades($_GET["con"]));
    $responsavel = $condominios_responsabilidades->buscaCondominioResponsavel($_GET["con"]);
    print "if (document.getElementById(\"responsabilidades\")) document.getElementById(\"responsabilidades\").innerHTML = '".str_replace("'", '\\\'', $responsabilidades)."';";
    print "if (document.getElementById(\"responsavel\")) document.getElementById(\"responsavel\").innerHTML = '$responsavel';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscarCondominioResponsabilidades(condominio) {
  if (condominio) {
	  url = "rds.js.php?m=condominios_responsabilidades&a=busca_condominios_responsabilidades&con=" + condominio;
    RDSCarregar("condominios_responsabilidades_buscar_condominio_responsabilidades",url);
	}
}

function verCondominioResponsabilidade(responsabilidade) {
	if (responsabilidade) {
    abrir("condominio_responsabilidade", "?m=condominios_responsabilidades&a=ver&i=" + responsabilidade, 520, 454, 1);
	}
}

function adicionarCondominioResponsabilidade(condominio) {
	if (condominio) {
    abrir("condominio_responsabilidade", "?m=condominios_responsabilidades&a=adicionar&i=" + condominio, 520, 454, 1);
	}
}

function validaCondominioResponsabilidadeAprovar(formulario) {
  if (formulario.conflito.value == 1) {
    if (!confirm('ATENÇÃO!\n\nEste condomínio possui outra(s) responsabilidade(s) vigente(s), a aprovação desta responsabilidade implicará na finalização da(s) responsabilidade(s) ativa(s). Deseja aprovar assim mesmo?')) {
    	return false;
    }
  }
  if (formulario.limite.value == 1) {
    if (!confirm('ATENÇÃO!\n\nEste funcionário já atingiu seu limite de responsabilidades para o condomínio neste período.\nDeseja continuar e finalizar a responsabilidade mais antiga?')) {
    	return false;
    }
  }
  return true;
}

function validaCondominioResponsabilidade(formulario) {

  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.categoria.value) erro_vazio[erro_vazio.length] = "Categoria";
  if (!formulario.responsavel.value) erro_vazio[erro_vazio.length] = "Responsável";

  return apresentaErros(erro_vazio, erro_invalido);

}

<?

	if (false) { ?></script><? }

}

?>
