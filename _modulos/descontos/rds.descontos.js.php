<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$descontos = controle::instanciaObjeto("descontos", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "muda_servico" && $_GET["servico_id"]) {

    // Planos
    $planos = $descontos->buscaPlanos($_GET["servico_id"]);
    foreach ($planos as $id => $nome) {
      $array_planos[] = "new Array(\"$id\", \"$nome\")";
    }
    print "var planos = new Array(".@implode(",", $array_planos).");";
    print "if (document.getElementById(\"planos\")) atualizaSelect(document.getElementById(\"planos\"), planos, 'vazio', 0);";
    // Descontos
    $descontos = $descontos->buscaDescontos($_GET["servico_id"]);
    foreach ($descontos as $id => $nome) {
      $array_descontos[] = "new Array(\"$id\", \"$nome\")";
    }
    print "var descontos = new Array(".@implode(",", $array_descontos).");";
    print "if (document.getElementById(\"descontos\")) atualizaSelect(document.getElementById(\"descontos\"), descontos, 'vazio', 0);";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaModuloDesconto(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();
  if (!formulario.nome.value) {
    erro_vazio[erro_vazio.length] = "Nome";
  }
  if (!formulario.servico.value) {
    erro_vazio[erro_vazio.length] = "Serviço";
  }
  if (!formulario.sistema.value) {
    erro_vazio[erro_vazio.length] = "Sistema";
  }
  if (!formulario.tipo.value) {
    erro_vazio[erro_vazio.length] = "Tipo";
  }
  if (formulario.instalacao.value) {
    if (!formulario.instalacao_tipo.value) {
      erro_vazio[erro_invalido.length] = "Instalação";
    }
    if (!validaDesconto(formulario.instalacao.value)) {
      erro_invalido[erro_invalido.length] = "Instalação";
    }
  }
  if (!formulario.instalacao_fixa.value) {
    erro_vazio[erro_vazio.length] = "Instalação » Fixa / Variável";
  }
  if (formulario.mensalidade.value) {
    if (!formulario.mensalidade_tipo.value) {
      erro_vazio[erro_invalido.length] = "Mensalidade";
    }
    if (!validaDesconto(formulario.mensalidade.value)) {
      erro_invalido[erro_invalido.length] = "Mensalidade";
    }
  }
  if (!formulario.mensalidade_fixa.value) {
    erro_vazio[erro_vazio.length] = "Mensalidade » Fixa / Variável";
  }
  if (!formulario.validade_fixa.value) {
    erro_vazio[erro_vazio.length] = "Validade » Fixa / Variável";
  }
  if (!formulario.auto_aprovado.value) {
    erro_vazio[erro_vazio.length] = "Auto-Aprovado";
  }
  if (!formulario.adicionar_retorno.value) {
    erro_vazio[erro_vazio.length] = "Adicionar Retorno";
  }
  if (!formulario.status.value) {
    erro_vazio[erro_vazio.length] = "Status";
  }
  return apresentaErros(erro_vazio, erro_invalido);
}

function mudaServico(servico_id) {
	if (servico_id) {
		url = "rds.js.php?m=descontos&a=muda_servico&servico_id=" + servico_id;
    RDSCarregar("descontos_muda_servico", url);
	} else {
	  atualizaSelect(document.getElementById("planos"), null);
	  atualizaSelect(document.getElementById("descontos"), null);
	}
}

function mudaValidadeTipo(campo) {
	document.getElementById('validade').value = "";
	document.getElementById('validade').disabled = campo.value ? false : true;
}

function formataValidade(campo, evento) {
  if (document.getElementById('validade_tipo').value == 1) {
    return formataNumero(campo, evento);
  } else {
    return formataData(campo, evento);
  }
}

function mudaDescontoValorTipo (campo) {
  if (campo.name == 'instalacao_tipo') {
    document.getElementById("instalacao").value = '';
    document.getElementById("instalacao").disabled = campo.value ? false : true;
  } else if (campo.name == 'mensalidade_tipo') {
    document.getElementById("mensalidade").value = '';
    document.getElementById("mensalidade").disabled = campo.value ? false : true;
  }
}

<?

	if (false) { ?></script><? }

}

?>
