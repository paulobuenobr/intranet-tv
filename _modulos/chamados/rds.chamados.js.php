<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$chamados = controle::instanciaObjeto("chamados", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "buscar_chamados" && $_GET["id"]) {

    $dados = $chamados->geraChamadosListagem($chamados->buscaChamados($_GET["id"], $_GET["tipo"]));
    print "if (document.getElementById(\"chamados\")) document.getElementById(\"chamados\").innerHTML = '".str_replace("'", '\\\'', $dados)."';";

  } elseif ($_GET["a"] == "buscar_etapas") {

    $dados = $chamados->geraListagemEtapas($chamados->buscaEtapas($_GET["chamado_id"], $_GET["detalhada"]));
    print "if (document.getElementById(\"etapas\")) document.getElementById(\"etapas\").innerHTML = '".str_replace("'", '\\\'', $dados)."';";

  } elseif ($_GET["a"] == "busca_responsaveis") {

    if ($_GET["categoria_id"]) {
      $responsaveis = $chamados->buscaResponsaveis($_GET["categoria_id"]);
      foreach ($responsaveis as $id => $responsavel) {
        $lista_responsaveis[] = "new Array(\"$id\", \"$responsavel\")";
      }
    }
    print "var responsaveis = new Array(".@implode(",", $lista_responsaveis).");";
    print "if (document.getElementById(\"responsavel\")) atualizaSelect(document.getElementById(\"responsavel\"), responsaveis);";

  } elseif ($_GET["a"] == "favoritos_adicionar") {

    $chamados->favoritosAdicionar($_GET["chamado"], $_SESSION["usuario"]);

  } elseif ($_GET["a"] == "favoritos_remover") {

    $chamados->favoritosRemover($_GET["chamado"], $_SESSION["usuario"]);

  } elseif ($_GET["a"] == "remove_etapa") {

    if ($_GET["etapa"]) {
      $chamados->removerEtapa($_GET["etapa"]);
    }

  } elseif ($_GET["a"] == "finalizar_visualizacao") {

    $chamados->registraChamadoVisualizacao($_GET["instancia"]);

  } elseif ($_GET["a"] == "atualiza_visualizacao") {

    $chamados->registraChamadoVisualizacao($_GET["instancia"], $_GET["chamado"]);

  } elseif ($_GET["a"] == "busca_etapas_impressao") {

    $_GET["chamados"] = explode(",", $_GET["chamados"]);
    for ($i = 0; $i < count($_GET["chamados"]); $i++) {
      $chamados->buscaEtapasImpressao($_GET["chamados"][$i], $_GET["exibir_internos"], $_GET["exibir_vazios"]);
    }

  }

} else {

	if (false) { ?><script type="text/javascript"><? }
	
?>

function finalizaVisualizacao() {
  url = "rds.js.php?m=chamados&a=finalizar_visualizacao&instancia=" + instancia;
  if (window.opener && window.opener.RDSCarregar) {
    window.opener.RDSCarregar("chamados_finalizar_visualizacao", url);
  } else {
    RDSCarregar("chamados_finalizar_visualizacao", url);
  }
}

function verContrato(contrato) {
	if (contrato) {
    abrir("contrato", "?m=contratos&a=ver&i=" + contrato, 625, 520, 1);
	}
}

function atualizaVisualizacao() {
  url = "rds.js.php?m=chamados&a=atualiza_visualizacao&chamado=" + chamado_atual + "&instancia=" + instancia;
  RDSCarregar("chamados_atualiza_visualizacao", url);
}

function adicionarChamado(id, tipo) {
	if (id) {
	  tipo = tipo ? tipo : "cliente_id";
    abrir("chamados", "?m=chamados&a=adicionar&" + tipo + "=" + id, 620, 520, 1);
	}
}

function verChamado(chamado_id) {
	if (chamado_id) {
    abrir("chamados", "?m=chamados&a=ver&i=" + chamado_id, 620, 520, 1);
	}
}

function buscaChamados(id, tipo) {
	if (id) {
	  tipo = tipo ? tipo : "cli_id";
	  url = "rds.js.php?m=chamados&a=buscar_chamados&id=" + id + "&tipo=" + tipo;
    RDSCarregar("chamados_buscar_chamados", url);
	}
}

function buscaValorCheckBox(nome) {
  i = 1;
  while (checkbox = document.getElementById(nome + "_" + i)) {
    if (checkbox.checked) return checkbox.value;
    i++;
  }
}

function validaChamadosAdicionar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.solicitante.value) erro_vazio[erro_vazio.length] = "Solicitante";
  if (!formulario.nivel_stress.value) erro_vazio[erro_vazio.length] = "Nível de Stress";
  if (!formulario.categoria.value) erro_vazio[erro_vazio.length] = "Categoria";
  if (!formulario.prioridade.value) erro_vazio[erro_vazio.length] = "Prioridade";

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaChamadosEditar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.categoria.value) erro_vazio[erro_vazio.length] = "Categoria";
  if (!formulario.prioridade.value) erro_vazio[erro_vazio.length] = "Prioridade";

  return apresentaErros(erro_vazio, erro_invalido);
}

function buscarEtapas(chamado_id, detalhada) {
  if (chamado_id) {
	  url = "rds.js.php?m=chamados&a=buscar_etapas&chamado_id=" + chamado_id + "&detalhada=" + detalhada;
    RDSCarregar("chamados_buscar_etapas", url);
	}
}

function mudaChamadoEtapaTipo(tipo) {
  tipos = new Array(5, 12, 13, 14, 17, 18, 19);
  for (i = 0; i < tipos.length; i++) {
    if (document.getElementById("etapa_conteudo_" + tipos[i])) {
      document.getElementById("etapa_conteudo_" + tipos[i]).style.display = (bucaTipoEtapa(tipos[i]) == bucaTipoEtapa(tipo) ? (ehNetscape() ? 'table-row-group' : 'block') : 'none');
      chamado_alterado = true;
    }
  }
}

function bucaTipoEtapa(tipo_id) {
  if (tipo_id == 5 || tipo_id == 12 || tipo_id == 13 || tipo_id == 14 || tipo_id == 17 || tipo_id == 18 || tipo_id == 19) {
    return 5;
  } else {
    return tipo_id;
  }
}

function mudaCategoria(campo) {
	url = "rds.js.php?m=chamados&a=busca_responsaveis&categoria_id=" + campo.value;
  RDSCarregar("chamados_buscar_responsaveis", url);
}

function favoritosAdicionar(chamado) {
  if (chamado) {
    url = "rds.js.php?m=chamados&a=favoritos_adicionar&chamado=" + chamado;
    RDSCarregar("chamados_favoritos_adicionar", url);
  }
}

function favoritosRemover(chamado) {
  if (chamado) {
    url = "rds.js.php?m=chamados&a=favoritos_remover&chamado=" + chamado;
    RDSCarregar("chamados_favoritos_remover", url);
  }
}

function removerEtapa(etapa) {
  if (confirm('Tem certeza que deseja remover esta etapa?')) {
    if (etapa) {
      exibePainelCarregando(true);
  	  url = "rds.js.php?m=chamados&a=remove_etapa&etapa=" + etapa;
      RDSCarregar("chamados_remove_etapa", url);
  	}
  }
}

function editarEtapa(etapa) {
  window.location = "./?m=chamados&a=editar_etapa&i=" + chamado_atual + "&etapa=" + etapa;
}

function validaEditarEtapa(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.conteudo.value) erro_vazio[erro_vazio.length] = "Conteúdo";

  return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}
