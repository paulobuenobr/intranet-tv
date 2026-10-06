<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$cobrancas_lotes = controle::instanciaObjeto("cobrancas_lotes", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "busca_dados_formato") {
  	
    // Gera Listagem Parametros
    $dados_formato = $cobrancas_lotes->buscaDadosCobrancaFormato($_GET["formato_id"]);
    $parametros = $cobrancas_lotes->geraParametrosListagem($dados_formato["parametros"]);
    print "if (document.getElementById(\"parametros\") != null) document.getElementById(\"parametros\").innerHTML = '".str_replace("'", '\\\'',$parametros)."';";
    // Busca Lote Existente
    if ($_GET["vencimento"]) {
      $cobranca_lote = $cobrancas_lotes->buscaCobrancaLoteExistente($_GET["formato_id"], $_GET["vencimento"]);
      if ($cobranca_lote) {
        print "if (confirm('Já existe outro lote com o mesmo formato e a mesma competência! Deseja vê-lo?')) { window.location = '?m=cobrancas_lotes&a=ver&i=".$cobranca_lote."'; }";
      }
    }
    
  }
  
} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

function validaCobrancaLote(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.nome.value) erro_vazio[erro_vazio.length] = "Nome";

  if (!formulario.formato.value) erro_vazio[erro_vazio.length] = "Formato";	

  if (!formulario.vencimento.value) {
    erro_vazio[erro_vazio.length] = "Vencimento";
  } else {
    if (!validaCompetencia(formulario.vencimento.value)) {
      erro_invalido[erro_invalido.length] = "Vencimento";
    }
  }

  return apresentaErros(erro_vazio,erro_invalido);
}

function validaGeraCobrancaLote(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.vencimento.value) {
    erro_vazio[erro_vazio.length] = "Vencimento";
  } else {
    if (!validaCompetencia(formulario.vencimento.value)) {
      erro_invalido[erro_invalido.length] = "Vencimento";
    }
  }

  if (nenhumFormatoSelecionado()) {
    erro_vazio[erro_vazio.length] = "Formatos (No mínimo um)";
  }

  retorno = apresentaErros(erro_vazio, erro_invalido);

  if (retorno) {
    if (retorno = confirm('Tem certeza que deseja continuar com a geração?\nEsta poderação poderá levar alguns minutos.')) {
      document.getElementById('salvar').disabled = true;
      document.getElementById('limpar').disabled = true;
      document.getElementById('voltar').disabled = true;
    }
  }

  return retorno;
}

function nenhumFormatoSelecionado() {
  var i = 0;
  var selecionado = false;
  while (formato = document.getElementById('formatos_' + i)) {
    selecionado = selecionado || formato.checked;
    i++;
  }
  return (!selecionado);
}

function mudaCobrancaFormato(formato, vencimento) {
  if (!formato) {
    if (document.getElementById("parametros")) document.getElementById("parametros").innerHTML = "";
  } else {
	  url = "rds.js.php?m=cobrancas_lotes&a=busca_dados_formato&formato_id=" + formato + "&vencimento=" + vencimento;
    RDSCarregar("cobrancas_lotes_busca_dados_formato", url);
  }
}

<?

	if (false) { ?></script><? }

}

?>
