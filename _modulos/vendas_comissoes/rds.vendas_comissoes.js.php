<?

function formataDinheiro($valor, $moeda = 'R$') {
  if ($valor) {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  }
}

function buscaComissoes($venda_id) {
  global $bancodedados;
  $query = "
    SELECT
      tab_vendas_comissoes.vec_id AS id,
      tab_vendas_comissoes.vec_valor AS valor,
      tab_vendas_comissoes_tipos.vct_nome AS tipo,
      tab_usuarios.usu_nome AS nome
    FROM
      tab_vendas_comissoes
      INNER JOIN tab_vendas_comissoes_tipos ON (tab_vendas_comissoes_tipos.vct_id = tab_vendas_comissoes.vct_id)
      INNER JOIN tab_usuarios ON (tab_vendas_comissoes.usu_id = tab_usuarios.usu_id)
    WHERE
      ven_id = $venda_id
    ORDER BY
      vec_valor DESC";
  $query = $bancodedados->query($query);
  $total = 0;
  while (($item = $bancodedados->resultadoArray($query)) != false) {
    $total += $item["valor"];
    $item["valor"] = formataDinheiro($item["valor"]);
    $item["link"] = "javascript:verVendasComissoes('".$item["id"]."');";
    $itens[] = $item;
  }
  if ($total) $itens[] = array("nome" => "<b>Total</b>", "valor" => "<b>".formataDinheiro($total)."</b>");
  return $itens;
}

function geraListagemComissoesDetalhes($itens) {
  $cabecalho = array("nome" => "Nome", "tipo" => "Tipo", "valor" => "Valor");
  $listagem = controle::instanciaObjeto("listagem", $cabecalho, $itens);
  return $listagem->conteudo;
}

if ($_GET["a"]) {
	
  if ($_GET["a"] == "buscar_vendas_comissoes") {
  	
    $vendas_comissoes = addslashes(geraListagemComissoesDetalhes(buscaComissoes($_GET["venda_id"])));
    print "if (document.getElementById('comissoes_listagem')) document.getElementById('comissoes_listagem').innerHTML = '$vendas_comissoes';";
    
  }  
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var WVendasComissoes;

function verVendasComissoes(venda_comissao) {
	if (venda_comissao) {
    abrir("vendas_comissoes", "?m=vendas_comissoes&a=ver&i=" + venda_comissao, 450, 450, 1);
	}
}

function buscarVendasComissoes(venda_id) {
	if (venda_id) {
		url = "rds.js.php?m=vendas_comissoes&a=buscar_vendas_comissoes&venda_id=" + venda_id;
		RDSCarregar("vendas_comissoes_buscar_vendas_comissoes", url);
	}
}

<?

	if (false) { ?></script><? }

}

?>
