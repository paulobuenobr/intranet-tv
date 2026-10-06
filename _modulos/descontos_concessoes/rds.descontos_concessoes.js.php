<?

if ($_GET && array_key_exists("a", $_GET)) {

	global $bancodedados;

	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$descontos_concessoes = controle::instanciaObjeto("descontos_concessoes", $bancodedados, $seguranca->permissoes, false);

	if ($_GET["a"] == "busca_descontos_servico") {

		if ($_GET["tipo"] == "ass") {
			$data_cancelamento = $descontos_concessoes->buscaDataCancelamento($_GET["id"]);
			$sistema_id = $descontos_concessoes->buscaSistemaAssinatura($_GET["id"]);
			$descontos_aplicados = $descontos_concessoes->buscaDescontosAssinatura($_GET["id"]);
			$origem_id = $descontos_concessoes->buscaOrigemAssinatura($_GET["id"]);
		} else {
			$data_cancelamento = '';
			$sistema_id = '';
			$descontos_aplicados = null;
			$origem_id = null;
		}
		
		$descontos = array();

		$query = $bancodedados->query("
			SELECT DISTINCT
				tab_descontos.des_id AS id,
				tab_descontos.des_nome AS nome,
				tab_descontos.des_parametros AS parametros
			FROM
				tab_descontos
				LEFT OUTER JOIN tab_descontos_concessoes ON (tab_descontos_concessoes.des_id = tab_descontos.des_id AND tab_descontos_concessoes.{$_GET["tipo"]}_id = {$_GET["id"]} AND tab_descontos_concessoes.dcs_id <= 5)
			WHERE".($sistema_id ? "
				(tab_descontos.pli_id = $sistema_id OR tab_descontos.pli_id IS NULL) AND" : "")."
				(tab_descontos.pls_id = {$_GET["ser"]} OR tab_descontos.pls_id IS NULL) AND
				(tab_descontos.dea_id = 1) AND
				(tab_descontos_concessoes.dec_id IS NULL || tab_descontos.des_nome LIKE 'Desconto Negociação%')
			ORDER BY
				nome");
				
		while (($desconto = $bancodedados->resultadoArray($query)) != false) {
			
			$parametros = unserialize($desconto["parametros"]);
			
			if ($_GET["tipo"] == "ass" && $parametros["carencia"]) {
				$data_carencia_minima = date("Ymd", strtotime("{$parametros["carencia"]} days"));
			}
			
			if (($_GET["tipo"] == "ass" || ($parametros["aplica_plano_principal"] && $_GET["tipo"] == "pla") ||
					($parametros["aplica_plano_adicional"] && $_GET["tipo"] == "pld") ||
					($parametros["aplica_condominio"] && $_GET["tipo"] == "con")) &&
					(!$origem_id || !$parametros["origens"] || in_array($origem_id, $parametros["origens"])) &&
					(!$_GET["plano"] || !$parametros["planos"] || in_array($_GET["plano"], $parametros["planos"])) &&
					(!$descontos_aplicados || count(array_intersect($parametros["descontos"], $descontos_aplicados)) == 0) &&
					($_GET["tipo"] != "ass" || !$data_carencia_minima || $data_carencia_minima >= $data_cancelamento)) {
				$descontos[] = "new Array(\"{$desconto["id"]}\", \"{$desconto["nome"]}\")";
				$parametros["descontos"] = implode(", ", $parametros["descontos"]);
			}
			
		}
		
		print "var descontos = new Array(".implode(",", $descontos).");";
		print "if (document.getElementById(\"desconto\")) { atualizaSelect(document.getElementById(\"desconto\"),descontos); }";

	} elseif ($_GET["a"] == "busca_dados_desconto") {

		if (($dados_desconto = $descontos_concessoes->buscaDadosDesconto($_GET["des"])) != false) {
			
			print "document.getElementById(\"instalacao_valor\").disabled = false;";
			print "document.getElementById(\"instalacao_tipo\").disabled = false;";
			print "document.getElementById(\"mensalidade_valor\").disabled = false;";
			print "document.getElementById(\"mensalidade_tipo\").disabled = false;";
			print "if (document.getElementById(\"validade_1\")) document.getElementById(\"validade_1\").disabled = false;";
			print "if (document.getElementById(\"validade_2\")) document.getElementById(\"validade_2\").disabled = false;";
			print "document.getElementById(\"instalacao_valor\").value = '{$dados_desconto["instalacao_valor"]}';";
			print "document.getElementById(\"mensalidade_valor\").value = '{$dados_desconto["mensalidade_valor"]}';";
			if ($dados_desconto["validade_tipo"] && $dados_desconto["validade_tipo"] == 2) {
				print "if (document.getElementById(\"validade_1\")) document.getElementById(\"validade_1\").style.display = 'none';";
				print "if (document.getElementById(\"validade_2\")) document.getElementById(\"validade_2\").style.display = '';";
				print "if (document.getElementById(\"validade_2\")) document.getElementById(\"validade_2\").value = '{$dados_desconto["validade"]}';";
			} else {
				print "if (document.getElementById(\"validade_1\")) document.getElementById(\"validade_1\").style.display = '';";
				print "if (document.getElementById(\"validade_2\")) document.getElementById(\"validade_2\").style.display = 'none';";
				print "if (document.getElementById(\"validade_1\")) document.getElementById(\"validade_1\").selectedIndex = '{$dados_desconto["validade"]}';";
			}
			print "document.getElementById(\"instalacao_tipo\").value = '{$dados_desconto["instalacao_tipo"]}';";
			print "document.getElementById(\"mensalidade_tipo\").value = '{$dados_desconto["mensalidade_tipo"]}';";
			if ($dados_desconto["instalacao_fixa"]) {
				print "document.getElementById(\"instalacao_valor\").disabled = true;";
				print "document.getElementById(\"instalacao_tipo\").disabled = true;";
			}
			if ($dados_desconto["mensalidade_fixa"]) {
				print "document.getElementById(\"mensalidade_valor\").disabled = true;";
				print "document.getElementById(\"mensalidade_tipo\").disabled = true;";
			}
			if ($dados_desconto["validade_fixa"]) {
				print "if (document.getElementById(\"validade_1\")) document.getElementById(\"validade_1\").disabled = true;";
				print "if (document.getElementById(\"validade_2\")) document.getElementById(\"validade_2\").disabled = true;";
			}
			print "document.getElementById(\"tipo\").innerHTML = '{$dados_desconto["tipo_nome"]}';";
		
		}

	} elseif ($_GET["a"] == "busca_desconto_servicos") {
		
		$descontos = array();

		$query = $bancodedados->query("
			SELECT DISTINCT
				tab_planos_servicos.pls_id AS id,
				tab_planos_servicos.pls_nome AS nome,
				tab_descontos.des_parametros AS parametros
			FROM
				tab_descontos
				INNER JOIN tab_planos_servicos ON (tab_descontos.pls_id = tab_planos_servicos.pls_id)
			WHERE
				tab_descontos.dea_id = 1");
				
		while (($desconto = $bancodedados->resultadoArray($query)) != false) {
			$parametros = unserialize($desconto["parametros"]);
			if ($parametros["aplica_condominio"]) {
				$descontos[] = "new Array(\"{$desconto["id"]}\", \"{$desconto["nome"]}\")";
			}
		}
		
		print "var servicos = new Array(".implode(",", array_unique($descontos)).");";
		print "if (document.getElementById(\"servico\")) atualizaSelect(document.getElementById(\"servico\"), servicos);";

	} elseif ($_GET["a"] == "busca_descontos_condominio") {

		$descontos_condominio = $descontos_concessoes->geraListagemDescontosCondominios($descontos_concessoes->buscaDescontosCondominios($_GET["con"]));
		
		print "if (document.getElementById(\"descontos_condominio\")) document.getElementById(\"descontos_condominio\").innerHTML = '".str_replace("'", '\\\'', $descontos_condominio)."';";

	} elseif ($_GET["a"] == "busca_desconto_concessoes_planos") {

		$descontos = $descontos_concessoes->geraListagemDescontosPlanos($descontos_concessoes->buscaDescontosPlanos($_GET["plano_id"]));
		
		print "document.getElementById(\"descontos_plano\").innerHTML = \"".addslashes($descontos)."\";";

	}

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function buscaDescontosServico(servico, tipo, id, plano) {
	if (servico && tipo) {
		plano = plano ? plano : "";
		id = id ? id : "";
		url = "rds.js.php?m=descontos_concessoes&a=busca_descontos_servico&ser=" + servico + "&tipo=" + tipo + "&id=" + id + "&plano=" + plano;
		RDSCarregar("descontos_concessoes_busca_descontos_servico", url);
	}
}

function buscaDescontosConcessoesPlanos(plano_id) {
	if (plano_id) {
		url = "rds.js.php?m=descontos_concessoes&a=busca_desconto_concessoes_planos&plano_id=" + plano_id;
		RDSCarregar("descontos_concessoes_busca_desconto_concessoes_planos", url);
	}
}

function buscaDadosDesconto(desconto) {
	if (desconto) {
		url = "rds.js.php?m=descontos_concessoes&a=busca_dados_desconto&des=" + desconto;
		RDSCarregar("descontos_concessoes_busca_dados_desconto", url);
	}
}

function adicionarDescontoConcessaoAssinatura(assinatura) {
	if (assinatura) {
		abrir("assinaturas_descontos", "?m=descontos_concessoes&a=adicionar&ass=" + assinatura, 450, 455, 1);
	}
}

function adicionarDescontoConcessaoPlano(plano) {
	if (plano) {
		abrir("planos_descontos", "?m=descontos_concessoes&a=adicionar&pla=" + plano, 450, 455, 1);
	}
}

function adicionarDescontoConcessaoCondominio(condominio) {
	if (condominio) {
		abrir("assinaturas_descontos", "?m=descontos_concessoes&a=adicionar&con=" + condominio, 450, 455, 1);
	}
}

function verDescontoConcessao(desconto) {
	if (desconto) {
		abrir("assinaturas_descontos", "?m=descontos_concessoes&a=ver&i=" + desconto, 450, 455, 1);
	}
}

function buscaDescontoServicos(tipo) {
	if (tipo) {
		url = "rds.js.php?m=descontos_concessoes&a=busca_desconto_servicos&tipo=" + tipo;
		RDSCarregar("descontos_concessoes_busca_desconto_servicos", url);
	}
}

function buscaDescontosCondominio(condominio, ver) {
	if (condominio) {
		url = "rds.js.php?m=descontos_concessoes&a=busca_descontos_condominio&con=" + condominio + "&ver=" + ver;
		RDSCarregar("descontos_concessoes_busca_descontos_condominio", url);
	}
}

function mudaDescontoConcessaoDesconto(campo) {
	desconto = campo.value;
	if (desconto) {
		buscaDadosDesconto(desconto);
	} else {
		campo.form.instalacao_valor.value = "";
		campo.form.mensalidade_valor.value = "";
		campo.form.instalacao_tipo.selectedIndex = "";
		campo.form.mensalidade_tipo.selectedIndex = "";
		campo.form.validade.selectedIndex = "";
		campo.form.instalacao_valor.disabled = true;
		campo.form.mensalidade_valor.disabled = true;
		campo.form.instalacao_tipo.disabled = true;
		campo.form.mensalidade_tipo.disabled = true;
		campo.form.validade.disabled = true;
		document.getElementById('tipo').innerHTML = '';
	}
}

function mudaDescontoConcessaoServico(campo, tipo,ass) {
	servico = campo.value;
	if (servico) {
		buscaDescontosServico(servico, tipo, ass);
	} else {
		atualizaSelect(campo.form.desconto);
		campo.form.instalacao_valor.value = "";
		campo.form.mensalidade_valor.value = "";
		campo.form.instalacao_tipo.selectedIndex = "";
		campo.form.mensalidade_tipo.selectedIndex = "";
		campo.form.validade.selectedIndex = "";
		campo.form.instalacao_valor.disabled = true;
		campo.form.mensalidade_valor.disabled = true;
		campo.form.instalacao_tipo.disabled = true;
		campo.form.mensalidade_tipo.disabled = true;
		campo.form.validade.disabled = true;
		document.getElementById('tipo').innerHTML = '';
	}
}

function mudaDescontoConcessaoValorTipo(campo) {
	if (campo.name == 'instalacao_tipo') {
		document.getElementById("instalacao_valor").value = '';
	} else {
		document.getElementById("mensalidade_valor").value = '';
	}
}

<?

	if (false) { ?></script><? }

}

?>
