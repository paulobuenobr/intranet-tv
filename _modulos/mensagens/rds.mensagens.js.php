<?

function buscaCondominio ($condominio) {
  global $bancodedados;
  $query = "
    SELECT
      con_nome AS nome
    FROM
      tab_condominios
    WHERE
      con_id = '$condominio'";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query, 0, 0);
}

if ($_GET["a"]) {
  if ($_GET["a"] == "condominio_campo") {
  	$condominio = buscaCondominio($_GET["condominio"]);
    if ($condominio) {
      print "document.getElementById(\"condominio1\").value = '$condominio';";
    }
  }  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function atualizaCondominiosCampo(condominio) {
  if (condominio) {
    url = "rds.js.php?mensagens&a=condominio_campo&condominio=" + condominio;
    RDSCarregar("mensagens_condominio_campo", url);
    document.getElementById('condominio1').disabled = true;
  } else {
    document.getElementById('condominio1').disabled = false;
  }
  document.getElementById('condominio1').value = '';
}

function verModelo(modelo_id) {
	if (modelo_id) {
    abrir("mensagens", "?m=mensagens&a=visualizar&i=" + modelo_id, 715, 520, 1);
	}
}

<?

	if (false) { ?></script><? }

}

?>
