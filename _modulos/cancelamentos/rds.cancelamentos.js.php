<?

if ($_GET["a"]) {
	
	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$cancelamentos = controle::instanciaObjeto("cancelamentos", $bancodedados, $seguranca->permissoes, false);
  
  if ($_GET["a"] == "buscar" && $_GET["cli"]) {
  	
    $listagem = $cancelamentos->geraListagemCancelamentos($cancelamentos->buscaClientesCancelamentos($_GET["cli"]));
    $botao_adicionar = $cancelamentos->buscaBotaoAdicionar($_GET["cli"]);
    print "if (document.getElementById(\"cancelamentos\")) document.getElementById(\"cancelamentos\").innerHTML = '".addslashes($listagem)."';";
    print "if (document.getElementById(\"cancelamentos_adicionar\")) document.getElementById(\"cancelamentos_adicionar\").innerHTML = '".addslashes($botao_adicionar)."';";
    
  }
  
} else {

	if (false) { ?><script type="text/javascript"><? }

?>

var WVisualizarRequerimento;

function buscaCancelamentosCliente(cliente) {
  if (cliente) {
	  url = "rds.js.php?m=cancelamentos&a=buscar&cli=" + cliente;
    RDSCarregar("cancelamentos_buscar", url);
	}
}

function adicionarCancelamento(cliente_id) {
	if (cliente_id) {
    abrir("cancelamento", "?m=cancelamentos&a=adicionar&cliente_id=" + cliente_id, 620, 520, 1);
	}
}

function verCancelamento(cancelamento) {
	if (cancelamento) {
    abrir("cancelamento", "?m=cancelamentos&a=ver&i=" + cancelamento, 620, 520, 1);
	}
}

function visualizarRequerimento(cancelamento, contrato) {
	if (cancelamento) {
		if (WVisualizarRequerimento != null) WVisualizarRequerimento.close();
    parametros = "top,left,width=" + ((screen.availWidth > 760) ? 760 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 95) + "statusbar=no,toolbar=yes,scrollbars=yes,resizable=1";
		WVisualizarRequerimento = window.open("?m=cancelamentos&a=visualizar_requerimento&i=" + cancelamento + '&contrato_id=' + contrato, "visualizar_requerimento", parametros);
	}
}

function mudaCancelamentoEtapaTipo(etapa_tipo) {
  if (etapa_tipo && document.getElementById("etapa_conteudo_" + etapa_tipo) != null) {
    geraAlertaCancelamentoEtapasPendentes(etapa_tipo);
    if (etapa_tipo == 4 && !estaCumpridaCancelamentoEtapaTipo(3)) document.getElementById("etapa_conteudo_4_descarte_etapa_3").innerHTML = document.getElementById("etapa_conteudo_4_conteudo_descarte_etapa_3").innerHTML;
    if (etapa_tipo == 6 && !estaCumpridaCancelamentoEtapaTipo(3)) document.getElementById("etapa_conteudo_6_descarte_etapa_3").innerHTML = document.getElementById("etapa_conteudo_6_conteudo_descarte_etapa_3").innerHTML;
    if (etapa_tipo == 6 && !estaCumpridaCancelamentoEtapaTipo(4)) document.getElementById("etapa_conteudo_6_descarte_etapa_4").innerHTML = document.getElementById("etapa_conteudo_6_conteudo_descarte_etapa_4").innerHTML;
    document.getElementById("etapa_conteudo").innerHTML = document.getElementById("etapa_conteudo_"+etapa_tipo).innerHTML;
  } else {
    document.getElementById("etapa_conteudo").innerHTML = '';
  }
}

function geraAlertaCancelamentoEtapasPendentes(etapa_tipo) {
  var etapa_pendente = "";
  if (etapa_tipo == 2 || etapa_tipo == 7) {
    if (!estaCumpridaCancelamentoEtapaTipo(1)) etapa_pendente += "\n » Registro de Origem";
  }
  if (etapa_tipo == 3 || etapa_tipo == 5 || etapa_tipo == 9) {
    if (!estaCumpridaCancelamentoEtapaTipo(1)) etapa_pendente += "\n » Registro de Origem";
    if (!estaCumpridaCancelamentoEtapaTipo(2)) etapa_pendente += "\n » Registro de Tipo";
  }
  if (etapa_tipo == 4) {
    if (!estaCumpridaCancelamentoEtapaTipo(1)) etapa_pendente += "\n » Registro de Origem";
    if (!estaCumpridaCancelamentoEtapaTipo(2)) etapa_pendente += "\n » Registro de Tipo";
    if (!estaCumpridaCancelamentoEtapaTipo(3)) etapa_pendente += "\n » Registro de Negociação";
  }
  if (etapa_tipo == 6) {
    if (!estaCumpridaCancelamentoEtapaTipo(1)) etapa_pendente += "\n » Registro de Origem";
    if (!estaCumpridaCancelamentoEtapaTipo(2)) etapa_pendente += "\n » Registro de Tipo";
    if (!estaCumpridaCancelamentoEtapaTipo(3)) etapa_pendente += "\n » Registro de Negociação";
    if (!estaCumpridaCancelamentoEtapaTipo(4)) etapa_pendente += "\n » Registro de Auxilio";
  }
  if (etapa_tipo == 8) {
    if (!estaCumpridaCancelamentoEtapaTipo(1)) etapa_pendente += "\n » Registro de Origem";
    if (!estaCumpridaCancelamentoEtapaTipo(7)) etapa_pendente += "\n » Registro de Recebimento de Confirmação Formal";
  }
  if (etapa_pendente) {
    etapa_pendente = "ATENÇÃO:\n\nO registro desta Nova Etapa depende da(s) seguinte(s) etapa(s):\n" + etapa_pendente + "\n\nCaso você continue, a(s) mesma(s) será(ão) descartadas!";
    alert (etapa_pendente);
  }
}

function estaCumpridaCancelamentoEtapaTipo(etapa_tipo) {
  var etapa_cumprida = false;
  for (i = 0; i < etapas_cumpridas.length; i++) {
    if (etapas_cumpridas[i] == etapa_tipo) etapa_cumprida = true;
  }
  return etapa_cumprida;
}

function validaCancelamentoAdicionar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.etapa_conteudo_1_origem.value) erro_vazio[erro_vazio.length] = "Origem";
  if (!formulario.etapa_nivel.value) erro_vazio[erro_vazio.length] = "Nível de Stress";

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaCancelamentoEditar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.etapa_tipo.value) {
    erro_vazio[erro_vazio.length] = "Nova Etapa » Tipo";
  }
  if (!formulario.etapa_nivel.value) {
    erro_vazio[erro_vazio.length] = "Nova Etapa » Nível de Stress";
  }
  if (formulario.etapa_tipo.value) {
    if (formulario.etapa_tipo.value == 1) {
      if (!formulario.etapa_conteudo_1_origem.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Origem";
    }
    if (formulario.etapa_tipo.value == 2) {
      if (!formulario.etapa_conteudo_2_motivo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Motivo";
    }
    if (formulario.etapa_tipo.value == 3) {
      if (!formulario.etapa_conteudo_3_descricao.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Descrição";
    }
    if (formulario.etapa_tipo.value == 4) {
      if (!estaCumpridaCancelamentoEtapaTipo(3) && !formulario.etapa_conteudo_4_conteudo_descarte_etapa_3_motivo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Registro de Negociação (Motivo do Descarte)";
      if (!formulario.etapa_conteudo_4_usuario.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Usuário";
      if (!formulario.etapa_conteudo_4_descricao.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Descrição";
    }
    if (formulario.etapa_tipo.value == 5) {
      if (formulario.etapa_conteudo_5_data.value) {
        if (!validaData(formulario.etapa_conteudo_5_data.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Data";
      } else {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Data";
      }
      if (!formulario.etapa_conteudo_5_periodo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Período";
      if (!formulario.etapa_conteudo_5_local.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Local";
    }
    if (formulario.etapa_tipo.value == 6) {
      if (!estaCumpridaCancelamentoEtapaTipo(3) && !formulario.etapa_conteudo_6_conteudo_descarte_etapa_3_motivo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Registro de Negociação (Motivo do Descarte)";
      if (!estaCumpridaCancelamentoEtapaTipo(4) && !formulario.etapa_conteudo_6_conteudo_descarte_etapa_4_motivo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Registro de Auxilio (Motivo do Descarte)";
      if (!formulario.etapa_conteudo_6_motivo.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Motivo";
      if (!formulario.etapa_conteudo_6_duracao.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Duração";
    }
    if (formulario.etapa_tipo.value == 7) {
      if (formulario.etapa_conteudo_7_data.value) {
        if (!validaData(formulario.etapa_conteudo_7_data.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Data";
      } else {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Data";
      }
      if (!formulario.etapa_conteudo_7_formato.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Formato";
    }
    if (formulario.etapa_tipo.value == 8) {
      if (formulario.etapa_conteudo_8_data.value) {
        if (!validaData(formulario.etapa_conteudo_8_data.value)) erro_invalido[erro_invalido.length] = "Nova Etapa » Conteúdo » Data";
      } else {
        erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Data";
      }
    }
    if (formulario.etapa_tipo.value == 9) {
      if (!formulario.etapa_conteudo_9_novo_endereco.value) erro_vazio[erro_vazio.length] = "Nova Etapa » Conteúdo » Novo Endereço";
    }
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

function validaCancelamentoFinalizar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (formulario.status_4.checked) {
    if (!formulario.data_cancelamento.value) {
      erro_vazio[erro_vazio.length] = "Data » Cancelamento";
    } else if (!validaData(formulario.data_cancelamento.value)) {
      erro_invalido[erro_invalido.length] = "Data » Cancelamento";
    }
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

function mudaStatus() {
  if (document.getElementById('status_3').checked) {
    document.getElementById('cancelamento_contrato').style.display = 'none';
  } else {
    document.getElementById('cancelamento_contrato').style.display = (ehNetscape() ? 'table-row-group' : 'block');
  }
}

function validaCancelamentoEnviar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.emails.value) {
    erro_vazio[erro_vazio.length] = "E-mail(s)";
  } else if (!validaEmails(formulario.emails.value)) {
    erro_invalido[erro_invalido.length] = "E-mail(s)";
  }
  if (!formulario.data_cancelamento.value) {
    erro_vazio[erro_vazio.length] = "Data » Cancelamento";
  } else if (!validaData(formulario.data_cancelamento.value)) {
    erro_invalido[erro_invalido.length] = "Data » Cancelamento";
  }

  return apresentaErros(erro_vazio, erro_invalido);
}

<?

	if (false) { ?></script><? }

}

?>
