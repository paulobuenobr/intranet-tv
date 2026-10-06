<?

global $bancodedados;

if ($_GET["a"]) {

  if ($_GET["a"] == "busca_planos") {
  	
  	global $bancodedados;
  	
  	/**
  	 * @var seguranca $seguranca
  	 */
  	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
  	$relatorios_contratos = controle::instanciaObjeto("relatorios.contratos", $bancodedados, $seguranca->permissoes);
	  if ($_GET["servico"]) {
		  $planos = $relatorios_contratos->buscaPlanos($_GET["servico"]);
		  atualizaSelect("plano", $planos);
	  } else {
		  atualizaSelect("plano", array());
	  }
  } elseif ($_GET["a"] == "busca_cidade") {
        global $bancodedados;

  	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
  	$relatorios_contratos = controle::instanciaObjeto("relatorios.contratos", $bancodedados, $seguranca->permissoes);
        if ($_GET["estado"]) {
            $planos = $relatorios_contratos->buscaCidade($_GET["estado"]);
            atualizaSelect("cidade", $planos);
        } else {
            atualizaSelect("cidade", array());
        }
  }

        } else {
	
    if (false) { ?><script type="text/javascript"><? }
	
?>

function buscaPlanos(servico) {
  url = "rds.js.php?m=relatorios&a=busca_planos&servico=" + servico;
  RDSCarregar("relatorios_busca_planos", url);
}

function buscaCidade(estado) {
  url = "rds.js.php?m=relatorios&a=busca_cidade&estado=" + estado;
  RDSCarregar("relatorios_busca_cidade", url);
}

<?

	if (false) { ?></script><? }

}

?>
