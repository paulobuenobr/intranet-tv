<?

function buscaAssinaturas($id) {
	global $bancodedados;
  $query = "
    SELECT
      tab_assinaturas.ass_id AS ass_id
    FROM
      rel_ass_cor_estacontido,
      tab_assinaturas,
      tab_contratos,
      tab_clientes
    WHERE
      (tab_assinaturas.ass_id = rel_ass_cor_estacontido.ass_id) AND
      (tab_contratos.cor_id = rel_ass_cor_estacontido.cor_id) AND
      (tab_clientes.cli_id = tab_assinaturas.cli_id) AND
      (tab_assinaturas.ast_id IN (2, 3)) AND
      (tab_clientes.cli_id = '$id')";
  $query = $bancodedados->query($query);
	while (($dados = $bancodedados->resultadoArray($query)) != false) {
	  $dados[] = $dados["ass_id"];
	}
  return $dados;
}

function buscaParametros($ass_id) {
	global $bancodedados;
  $query = "
    SELECT
      tab_planos.pla_parametros AS parametros
    FROM
      tab_assinaturas
			LEFT OUTER JOIN `tab_assinaturas` `assinatura_pai` ON ( `tab_assinaturas`.`ass_pai` = `assinatura_pai`.`ass_id` )
			LEFT OUTER JOIN `tab_planos` ON (IF(`tab_assinaturas`.`ass_pai` IS NULL , `tab_assinaturas`.`pla_id` = `tab_planos`.`pla_id` , `assinatura_pai`.`pla_id` = `tab_planos`.`pla_id`))
    WHERE
      (tab_assinaturas.ass_id = '$ass_id')";
  $query = $bancodedados->query($query);
  $dados = $bancodedados->resultadoArray($query);
  if ($dados) {
  	$dados = unserialize($dados["parametros"]);
    return $dados;
  }
}

function buscaQuantidade($ass_id, $servico) {
	global $bancodedados;
  $query = "
    SELECT
      tab_assinaturas.ass_id AS ass_id
    FROM
      tab_assinaturas,
      tab_clientes_$servico
    WHERE
      (tab_assinaturas.ass_id = tab_clientes_$servico.ass_id) AND
      (tab_assinaturas.ass_id = '$ass_id')";
  $query = $bancodedados->query($query);
  return $bancodedados->linhas($query);
}

if ($_GET["a"]) {
	
	global $bancodedados;

  $lista_unidades = array ("1" => "KB", "2" => "MB", "3" => "GB", "4" => "TB");

  if ($_GET["a"] == "busca_basesdedados_cliente" && $_GET["cliente"]) {

   	$assinaturas = buscaAssinaturas($_GET["cliente"]);
    foreach ($assinaturas as $assinatura) {
    	$parametros = buscaParametros($assinatura["ass_id"]);
    	$basesdedados_disponiveis += $parametros["basesdedados_quantidade"] - buscaQuantidade($assinatura["ass_id"], "basesdedados");
    }
    print "if (document.getElementById(\"basesdedados_adicionar\")) document.getElementById(\"basesdedados_adicionar\").disabled=".((!$basesdedados_disponiveis) ? "true;" : "false;");
  	$basesdedados_disponiveis = ($basesdedados_disponiveis) ? "$basesdedados_disponiveis conta(s) disponível(is)" : "Nenhuma conta disponível";
    print "document.getElementById(\"basesdedados_disponiveis\").innerHTML = '$basesdedados_disponiveis';";

  	$query = "
  		SELECT
    		tab_clientes_basesdedados.clb_id AS id,
    		tab_clientes_basesdedados.clb_basededados AS nome,
    		tab_clientes_basesdedados.clb_login AS login,
    		tab_clientes_basesdedados.clb_capacidade AS capacidade,
    		tab_clientes_basesdedados_status.cbs_id AS status_id,
    		tab_clientes_basesdedados_status.cbs_nome AS status_nome,
        tab_clientes_basesdedados_tipos.cbt_id AS tipo_id,
        tab_clientes_basesdedados_tipos.cbt_nome AS tipo_nome
  		FROM
    		tab_assinaturas,
  			tab_clientes_basesdedados,
  	    tab_clientes_basesdedados_tipos,
  			tab_clientes_basesdedados_status
  		WHERE
    		(tab_assinaturas.ass_id = tab_clientes_basesdedados.ass_id) AND
  			(tab_clientes_basesdedados.cbs_id = tab_clientes_basesdedados_status.cbs_id) AND
  			(tab_clientes_basesdedados.cbt_id = tab_clientes_basesdedados_tipos.cbt_id) AND
    		(tab_assinaturas.cli_id = '{$_GET["cliente"]}')
  		ORDER BY
  			nome";
    $query = $bancodedados->query($query);
    while (($basededados = $bancodedados->resultadoArray($query)) != false) {
      $unidade_capacidade = floor(log($basededados["capacidade"], 1024));
      if ($_GET["ver"]) $basededados["link"] = "javascript:verBasededados({$basededados["id"]})";
      $basededados["tipo"] = $basededados["tipo_nome"];
      $basededados["nome"] = $basededados["nome"];
      $basededados["login"] = $basededados["login"];
      $basededados["capacidade"] = round($basededados["capacidade"] / pow(1024, $unidade_capacidade), 2)." ".$lista_unidades[$unidade_capacidade];
      $basededados["status"] = $basededados["status_nome"];
      $basesdedados[] = $basededados;
    }
    $cabecalho = array ("tipo" => "Tipo", "nome" => "Nome", "login" => "Login", "capacidade" => "Capacidade", "status_nome" => "Status");
    $listagem = controle::instanciaObjeto("listagem", $cabecalho, $basesdedados);
    print "document.getElementById(\"basesdedados\").innerHTML = '".str_replace("'", '\\\'', $listagem->conteudo)."';";

  } elseif ($_GET["a"] == "muda_assinatura" && $_GET["ass_id"]) {

   	$parametros = buscaParametros($_GET["ass_id"]);
   	$valor_capacidade = $parametros["basesdedados_capacidade"];
    $unidade_capacidade = floor(log($valor_capacidade, 1024));
    $hospedagens_capacidade = round($valor_capacidade / pow(1024, $unidade_capacidade), 2)." ".$lista_unidades[$unidade_capacidade];
    print "document.getElementById(\"capacidade\").innerHTML = '$hospedagens_capacidade';";

  } elseif ($_GET["a"] == "verifica_login_existe") {

  	$query = "
  		SELECT
    		tab_clientes.cli_nome AS nome
  		FROM
  			tab_clientes_basesdedados,
  			tab_assinaturas,
  			tab_clientes
  		WHERE
  			(tab_clientes_basesdedados.ass_id = tab_assinaturas.ass_id) AND
  			(tab_clientes.cli_id = tab_assinaturas.cli_id) AND
  			(tab_clientes_basesdedados.clb_id != {$_GET["clb_id"]}) AND
  			(tab_clientes_basesdedados.clb_login = '{$_GET["login"]}')";
   	$query = $bancodedados->query($query);
   	$dados = $bancodedados->resultadoArray($query);
    if ($dados) {
      print "alert('Este Login já está sendo utilizado pelo cliente \"{$dados["nome"]}\"');";
      print "document.getElementById(\"login\").value = '';";
      print "document.getElementById(\"login\").focus();";
    }

  } elseif ($_GET["a"] == "verifica_basededados_existe") {

    $query = "
  		SELECT
    		tab_clientes.cli_nome AS nome
  		FROM
  			tab_clientes_basesdedados,
  			tab_assinaturas,
  			tab_clientes
  		WHERE
  			(tab_clientes_basesdedados.ass_id = tab_assinaturas.ass_id) AND
  			(tab_clientes.cli_id = tab_assinaturas.cli_id) AND
  			(tab_clientes_basesdedados.clb_id != {$_GET["clb_id"]}) AND
  			(tab_clientes_basesdedados.clb_basededados = '{$_GET["basededados"]}')";
   	$query = $bancodedados->query($query);
   	$dados = $bancodedados->resultadoArray($query);
    if ($dados) {
      print "alert('Esta Base de Dados já está sendo utilizado pelo cliente \"{$dados["nome"]}\"');";
      print "document.getElementById(\"basededados\").value = '';";
      print "document.getElementById(\"basededados\").focus();";
    }
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var clientes_basesdedados;

function buscaBasesdedados(cliente, ver) {
  if (cliente) {
  	url = "rds.js.php?m=clientes_basesdedados&a=busca_basesdedados_cliente&cliente=" + cliente + "&ver=" + ver;
		RDSCarregar("clientes_basesdedados_busca_basesdedados_cliente", url);
  }
}

function verBasededados(basededados) {
	if (basededados) {
    abrir("clientes_basesdedados", "?m=clientes_basesdedados&a=ver&i=" + basededados, 450, 430, 0);
	}
}

function adicionarBasededados(cliente) {
	if (cliente) {
    abrir("clientes_basesdedados", "?m=clientes_basesdedados&a=adicionar&i=" + cliente, 450, 430, 0);
	}
}

function mudaAssinatura(campo) {
  document.getElementById("capacidade").innerHTML = '';
  if (campo.value) {
  	url = "rds.js.php?m=clientes_basesdedados&a=muda_assinatura&ass_id=" + campo.value;
		RDSCarregar("clientes_basesdedados_muda_assinatura", url);
  }
}

function verificaBasededadosExistente(campo, clb_id) {
  basededados = campo.form.basededados.value;
  if (basededados) {
  	url = "rds.js.php?m=clientes.basesdedados&a=verifica_basededados_existe&basededados=" + basededados + '&clb_id=' + clb_id;
		RDSCarregar("clientes_basesdedados_verifica_basededados_existe", url);
  }
}

function verificaLoginExistente(campo, clb_id) {
  login = campo.form.login.value;
  if (login) {
  	url = "rds.js.php?m=clientes.basesdedados&a=verifica_login_existe&login=" + login + '&clb_id=' + clb_id;
		RDSCarregar("clientes_basesdedados_verifica_login_existe", url);
  }
}

function validaClienteBasededados(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (formulario.assinatura)
	if (!formulario.assinatura.value) {
		erro_vazio[erro_vazio.length] = "Contrato";
	}
	if (!formulario.tipo.value) {
		erro_vazio[erro_vazio.length] = "Tipo";
	}
	if (!formulario.basededados.value) {
		erro_vazio[erro_vazio.length] = "Nome";
	} else
	if (!validaLogin(formulario.basededados.value)) {
		erro_invalido[erro_invalido] = "Nome";
	}
	if (!formulario.login.value) {
		erro_vazio[erro_vazio.length] = "Login";
	} else
	if (!validaLogin(formulario.login.value)) {
		erro_invalido[erro_invalido] = "Login";
	}
	if (formulario.senha.value && formulario.senha.value != formulario.senha_confirmacao.value) {
		erro_invalido[erro_invalido.length] = "Confirmação de senha";
	}
	return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
