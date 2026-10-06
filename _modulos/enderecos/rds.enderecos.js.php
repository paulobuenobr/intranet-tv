<?

function buscaEnderecoCEP($cep) {
  global $bancodedados;
  $cep = preg_replace("/[^0-9]/", "", $cep);
  $query = "
    SELECT
      tab_estados.est_nome AS estado,
      tab_cidades.cid_nome AS cidade,
      tab_bairros.bai_nome AS bairro,
      tab_logradouros.log_nome AS logradouro
    FROM
      tab_ceps
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
      INNER JOIN tab_estados ON (tab_cidades.est_id = tab_estados.est_id)
    WHERE
      tab_ceps.cep_id = '$cep'".($_SESSION["origem"] ? " AND
				tab_cidades.cid_id IN (
					SELECT
						cid_id
					FROM
						rel_clo_cid_contem
					WHERE
						clo_id = {$_SESSION["origem"]}
				)" : "");
  $query = $bancodedados->query($query);
  return $bancodedados->resultadoArray($query);
}

function buscaEnderecoBloco($bloco) {
  global $bancodedados;
  $query = "
    SELECT
      cep_id AS cep,
      cob_numero AS numero,
      con_id AS condominio
    FROM
      tab_condominios_blocos
    WHERE
      cob_id = '$bloco'";
  $query = $bancodedados->query($query);
  $endereco_bloco = $bancodedados->resultadoArray($query);
  if ($endereco_bloco["cep"]) {
    $endereco = buscaEnderecoCEP($endereco_bloco["cep"]);
    $endereco["cep"] = substr($endereco_bloco["cep"], 0, 5)."-".substr($endereco_bloco["cep"], 5, 3);
    $endereco["numero"] = $endereco_bloco["numero"] ? $endereco_bloco["numero"] : "";
  } else {
    $endereco = buscaEnderecoCondominio($endereco_bloco["condominio"]);
  }
  return $endereco;
}

function buscaEnderecoCondominio($condominio) {
  global $bancodedados;
  $query = "
    SELECT
      cep_id AS cep,
      con_numero AS numero
    FROM
      tab_condominios
    WHERE
      con_id = '$condominio'";
  $query = $bancodedados->query($query);
  $endereco_condominio = $bancodedados->resultadoArray($query);
  $endereco = buscaEnderecoCEP($endereco_condominio["cep"]);
  $endereco["cep"] = substr($endereco_condominio["cep"],0,5) . "-" . substr($endereco_condominio["cep"],5,3);
  $endereco["numero"] = $endereco_condominio["numero"] ? $endereco_condominio["numero"] : "";
  return $endereco;
}

if ($_GET["cep"]) {

	$endereco = buscaEnderecoCEP($_GET["cep"]);
  if ($endereco) {
    print "{$_GET["sufixo"]}estado = '" . addslashes($endereco["estado"]) . "';";
    print "{$_GET["sufixo"]}cidade = '" . addslashes($endereco["cidade"]) . "';";
    print "{$_GET["sufixo"]}bairro = '" . addslashes($endereco["bairro"]) . "';";
    print "{$_GET["sufixo"]}logradouro = '" . addslashes($endereco["logradouro"]) . "';";
    print "{$_GET["sufixo"]}numero = '" . addslashes($endereco["numero"]) . "';";
    print "{$_GET["sufixo"]}cep_valido = true;";
    print "atualizaEndereco(0, '{$_GET["sufixo"]}');";
    if (!$_GET["sufixo"]) {
      print "if (document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {";
      print "cobranca_estado = '" . addslashes($endereco["estado"]) . "';";
      print "cobranca_cidade = '" . addslashes($endereco["cidade"]) . "';";
      print "cobranca_bairro = '" . addslashes($endereco["bairro"]) . "';";
      print "cobranca_logradouro = '" . addslashes($endereco["logradouro"]) . "';";
      print "cobranca_numero = '" . addslashes($endereco["numero"]) . "';";
      print "cobranca_cep_valido = true;";
      print "atualizaEndereco(0, 'cobranca_');";
      print "}";
    }
  }	else {
    print "{$_GET["sufixo"]}estado = 'CEP Incorreto';";
    print "{$_GET["sufixo"]}cidade = 'CEP Incorreto';";
    print "{$_GET["sufixo"]}bairro = 'CEP Incorreto';";
    print "{$_GET["sufixo"]}logradouro = 'CEP Incorreto';";
    print "{$_GET["sufixo"]}cep_valido = false;";
    print "atualizaEndereco(0, '{$_GET["sufixo"]}');";
    if (!$_GET["sufixo"]) {
      print "if (document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {";
      print "cobranca_estado = 'CEP Incorreto';";
      print "cobranca_cidade = 'CEP Incorreto';";
      print "cobranca_bairro = 'CEP Incorreto';";
      print "cobranca_logradouro = 'CEP Incorreto';";
      print "cobranca_cep_valido = false;";
      print "atualizaEndereco(0, 'cobranca_');";
      print "}";
    }
  }

} elseif (!empty($_GET["bloco"])) {

    $endereco = buscaEnderecoBloco($_GET["bloco"]);
    print "{$_GET["sufixo"]}cep = '" . addslashes($endereco["cep"]) . "';";
    print "{$_GET["sufixo"]}estado = '" . addslashes($endereco["estado"]) . "';";
    print "{$_GET["sufixo"]}cidade = '" . addslashes($endereco["cidade"]) . "';";
    print "{$_GET["sufixo"]}bairro = '" . addslashes($endereco["bairro"]) . "';";
    print "{$_GET["sufixo"]}logradouro = '" . addslashes($endereco["logradouro"]) . "';";
    print "{$_GET["sufixo"]}numero = '" . addslashes($endereco["numero"]) . "';";
    print "{$_GET["sufixo"]}cep_valido = true;";
    print "atualizaEndereco(1, '{$_GET["sufixo"]}');";

} elseif (!empty($_GET["condominio"])) {

	$endereco = buscaEnderecoCondominio($_GET["condominio"]);
  if ($endereco) {
    print "{$_GET["sufixo"]}cep = '" . addslashes($endereco["cep"]) . "';";
    print "{$_GET["sufixo"]}estado = '" . addslashes($endereco["estado"]) . "';";
    print "{$_GET["sufixo"]}cidade = '" . addslashes($endereco["cidade"]) . "';";
    print "{$_GET["sufixo"]}bairro = '" . addslashes($endereco["bairro"]) . "';";
    print "{$_GET["sufixo"]}logradouro = '" . addslashes($endereco["logradouro"]) . "';";
    print "{$_GET["sufixo"]}numero = '" . addslashes($endereco["numero"]) . "';";
    print "{$_GET["sufixo"]}cep_valido = true;";
    print "atualizaEndereco(1, '{$_GET["sufixo"]}');";
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var cep;
var estado;
var cidade;
var bairro;
var logradouro;
var numero;
var cobranca_cep;
var cobranca_estado;
var cobranca_cidade;
var cobranca_bairro;
var cobranca_logradouro;
var cobranca_numero;

function buscaEnderecoCEP(busca_cep, sufixo) {
  if (!sufixo) sufixo = '';
  if (busca_cep && busca_cep.length == 9) {
		busca_cep = limpaStringNumerico(busca_cep);
		url = "rds.js.php?m=enderecos&cep=" + busca_cep + "&sufixo=" + sufixo;
    RDSCarregar("enderecos" + sufixo, url);
	} else {
    if (sufixo == 'cobranca_') {
  		cobranca_estado = "Preencha o campo CEP";
  		cobranca_cidade = "Preencha o campo CEP";
  		cobranca_bairro = "Preencha o campo CEP";
  		cobranca_logradouro = "Preencha o campo CEP";
  		cobranca_numero = "";
  		cobranca_cep_valido = false;
    } else {
  		estado = "Preencha o campo CEP";
  		cidade = "Preencha o campo CEP";
  		bairro = "Preencha o campo CEP";
  		logradouro = "Preencha o campo CEP";
  		numero = "";
  		cep_valido = false;
  		if (document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
    		cobranca_estado = "Preencha o campo CEP";
    		cobranca_cidade = "Preencha o campo CEP";
    		cobranca_bairro = "Preencha o campo CEP";
    		cobranca_logradouro = "Preencha o campo CEP";
  		  cobranca_numero = "";
    		cobranca_cep_valido = false;
  		}
    }
		atualizaEndereco(0, sufixo);
	}
}

function buscaEnderecoBloco(bloco, sufixo) {
  if (!sufixo) sufixo = '';
	if (bloco) {
		url = "rds.js.php?m=enderecos&bloco=" + bloco + "&sufixo=" + sufixo;
    RDSCarregar("enderecos", url);
	}
}

function buscaEnderecoCondominio(condominio, sufixo) {
  if (!sufixo) sufixo = '';
	if (condominio) {
		url = "rds.js.php?m=enderecos&condominio=" + condominio + "&sufixo=" + sufixo;
    RDSCarregar("enderecos", url);
	}
}

function atualizaEndereco(atualiza_numero, sufixo) {
  if (!sufixo) sufixo = '';
	if (document.getElementById(sufixo + 'cep').disabled && eval(sufixo + 'cep')) {
		document.getElementById(sufixo + 'cep').value = eval(sufixo + 'cep');
	}
	document.getElementById(sufixo + 'estado').value = eval(sufixo + 'estado');
	document.getElementById(sufixo + 'cidade').value = eval(sufixo + 'cidade');
	document.getElementById(sufixo + 'bairro').value = eval(sufixo + 'bairro');
	document.getElementById(sufixo + 'logradouro').value = eval(sufixo + 'logradouro');
	if (document.getElementById(sufixo + 'numero') && (!document.getElementById(sufixo + 'numero').value || atualiza_numero)) {
	  document.getElementById(sufixo + 'numero').value = eval(sufixo + 'numero');
	}
	if (!sufixo && document.getElementById('mesmo_endereco') && document.getElementById('mesmo_endereco').checked) {
  	if (document.getElementById('cobranca_cep').disabled && eval('cep')) {
  		document.getElementById('cobranca_cep').value = eval('cep');
  	}
  	document.getElementById('cobranca_estado').value = eval('estado');
  	document.getElementById('cobranca_cidade').value = eval('cidade');
  	document.getElementById('cobranca_bairro').value = eval('bairro');
  	document.getElementById('cobranca_logradouro').value = eval('logradouro');
  	if (document.getElementById('cobranca_numero') && (!document.getElementById('cobranca_numero').value || atualiza_numero)) {
  	  document.getElementById('cobranca_numero').value = eval('numero');
  	}
	}
}

<?

	if (false) { ?></script><? }

}

?>
