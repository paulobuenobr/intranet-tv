<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$condominios = controle::instanciaObjeto("condominios", $bancodedados, $seguranca->permissoes, '%', false);
  
  if ($_GET["a"] == "muda_tecnologia") {
    
  	$equipamentos = $condominios->buscaEquipamentos($_GET["tecnologia"]);
    if ($equipamentos) {
      foreach ($equipamentos as $id => $nome) {
        $script_itens[] = "new Array(\"$id\", \"$nome\")";
      }
      print 'var itens_equipamentos = new Array ('.@implode(',', $script_itens).');';
      print 'if (document.getElementById("equipamento")) atualizaSelect(document.getElementById("equipamento"), itens_equipamentos);';
    }
  }

} else {

	if (false) { ?><script type="text/javascript"><? }
	
?>

function mudaTecnologia(tecnologia) {
  if (tecnologia) {
    url = "rds.js.php?m=condominios&a=muda_tecnologia&tecnologia=" + tecnologia;
    RDSCarregar("condominios_muda_tecnologia", url);
  } else {
    document.getElementById('equipamento').value = '';
    document.getElementById('equipamento').disabled = true;
  }
}

<?

	if (false) { ?></script><? }

}

?>
