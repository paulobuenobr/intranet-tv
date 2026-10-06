<?

function geraScriptAtualizacaoSelect ($nome, $itens = '', $ativo = '') {
  if ($nome) {
    while (($item = @current($itens)) != false) {
      $script_itens[] = 'new Array("' . @key($itens) . '","' . $item . '")';
      @next($itens);
    }
    $script_conteudo  = 'var itens_' . $nome . ' = new Array (' . @implode(',',$script_itens) . ');';
    $script_conteudo .= 'if (document.getElementById("' . $nome . '")) atualizaSelect(document.getElementById("' . $nome . '"),itens_' . $nome . ',"' . $ativo . '");';
  }
  return $script_conteudo;
}

function buscaBairros ($cidade = '%') {
  global $bancodedados;
  $query = "
    SELECT DISTINCT
      tab_bairros.bai_id AS id,
      tab_bairros.bai_nome AS nome
    FROM
      tab_condominios
      INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
    WHERE
      tab_bairros.cid_id LIKE '$cidade'
    ORDER BY
      nome";
  $query = $bancodedados->query($query);
  while (($dados = $bancodedados->resultadoArray($query)) != false) {
    $bairros[$dados["id"]] = $dados["nome"];
  }
  return $bairros;
}

function buscaBairro ($bairro) {
  global $bancodedados;
  $query = "
    SELECT
      tab_bairros.bai_nome AS nome
    FROM
      tab_bairros
    WHERE
      tab_bairros.bai_id = '$bairro'";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query,0,0);
}

function buscaCidade ($cidade) {
  global $bancodedados;
  $query = "
    SELECT
      tab_cidades.cid_nome AS nome
    FROM
      tab_cidades
    WHERE
      tab_cidades.cid_id = '$cidade'";
  $query = $bancodedados->query($query);
  return $bancodedados->resultado($query,0,0);
}

if ($_GET["a"]) {

  if ($_GET["a"] == "atualiza_cidade") {

  	$bairros = buscaBairros($_GET["cidade"]);
    if ($bairros) {
      $bairros_script = geraScriptAtualizacaoSelect("bairro2", $bairros);
      print $bairros_script;
    } else {
      $bairros_script = geraScriptAtualizacaoSelect("bairro2");
      print $bairros_script;
    }

  } elseif ($_GET["a"] == "cidade_campo") {

  	$cidade = buscaCidade($_GET["cidade"]);
    if ($cidade) {
      print "document.getElementById(\"cidade1\").value = '$cidade';";
    }

  } elseif ($_GET["a"] == "bairro_campo") {

  	$bairro = buscaBairro($_GET["bairro"]);
    if ($bairro) {
      print "document.getElementById(\"bairro1\").value = '$bairro';";
    }

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function atualizaBairros(cidade) {
  if (cidade) {
    url = "rds.js.php?m=condominios.buscar&a=cidade_campo&cidade=" + cidade;
    RDSCarregar("condominios_buscar_cidade_campo", url);
    document.getElementById('bairro1').value = '';
    document.getElementById('cidade1').disabled = true;
  } else {
    document.getElementById('cidade1').value = '';
    document.getElementById('cidade1').disabled = false;
    document.getElementById('bairro1').value = '';
    document.getElementById('bairro1').disabled = false;
  }
  url = "rds.js.php?m=condominios.buscar&a=atualiza_cidade&cidade=" + cidade;
  RDSCarregar("condominios_buscar_atualiza_cidade",url);
}

function atualizaBairrosCampo(bairro) {
  if (bairro) {
    url = "rds.js.php?m=condominios.buscar&a=bairro_campo&bairro=" + bairro;
    RDSCarregar("condominios_buscar_bairro_campo", url);
    document.getElementById('bairro1').disabled = true;
  } else {
    document.getElementById('bairro1').value = '';
    document.getElementById('bairro1').disabled = false;
  }
}

<?

	if (false) { ?></script><? }

}

?>
