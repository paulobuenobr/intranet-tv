<?

function buscaVendas($venda_id) {
  global $bancodedados;
  $query = "
    SELECT
      tab_vendas.ven_id AS id,
      tab_vendas.ven_pontuacao AS pontuacao,
      tab_usuarios.usu_nome AS nome
    FROM
      tab_vendas
      INNER JOIN tab_usuarios ON (tab_vendas.usu_id = tab_usuarios.usu_id)
    WHERE
      ven_id = $venda_id";
  $query = $bancodedados->query($query);
	while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $dados["link"] = "javascript:verVendas('{$dados["id"]}');";
    $vendas[] = $dados;
	}
	return $vendas;
}

function geraListagemVendasDetalhes($itens) {
  $cabecalho = array("nome" => "Nome", "pontuacao" => "Pontuação");
  $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
  return $listagem->conteudo;
}

if ($_GET["a"]) {
	
  if ($_GET["a"] == "buscar_vendas") {
  	
    $vendas = addslashes(geraListagemVendasDetalhes(buscaVendas($_GET["venda_id"])));
    print "if (document.getElementById('vendas_listagem')) document.getElementById('vendas_listagem').innerHTML = '$vendas';";
    
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var WVendas;

function verVendas(venda) {
	if (venda) {
    abrir("vendas", "?m=vendas&a=ver&i=" + venda, 450, 450, 1);
	}
}

function buscarVendas(venda_id) {
	if (venda_id) {
		url = "rds.js.php?m=vendas&a=buscar_vendas&venda_id=" + venda_id;
		RDSCarregar("vendas_buscar_vendas", url);
	}
}

<?

	if (false) { ?></script><? }

}

?>
