<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$tarefas = controle::instanciaObjeto("tarefas", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "buscar_listagem") {

    $_SESSION["exibicao"] = $_GET["exibicao"];
    $_GET["referencia"] = $_GET["referencia"] ? $_GET["referencia"] : ",,{$_SESSION["usuario"]}";
    $_GET["formato"] = $_GET["formato"] ? $_GET["formato"] : "mes";
    $_GET["status"] = $_GET["status"] ? $_GET["status"] : "1";
    // Busca Data
    $_GET["dia"] = $_GET["dia"] ? $_GET["dia"] : date("d");
    $_GET["mes"] = $_GET["mes"] ? $_GET["mes"] : date("m");
    $_GET["ano"] = $_GET["ano"] ? $_GET["ano"] : date("Y");

    $tarefas_agenda = $tarefas->geraCalendario($_GET["referencia"], $_GET["formato"], $_GET["status"], $_GET["dia"], $_GET["mes"], $_GET["ano"]);
    print "if (document.getElementById(\"tarefas_agenda\")) document.getElementById(\"tarefas_agenda\").innerHTML = '$tarefas_agenda';";

  } elseif ($_GET["a"] == "buscar_origens") {

    $origens = $tarefas->buscaOrigens($_SESSION["usuario"]);
    for ($i = 0; $i < sizeof($origens); $i++) {
      $js_origens[] = 'new Array("'.$origens[$i]["id"].'","'.$origens[$i]["nome"].'")';
    }
    print "var origens = new Array(".@implode(", ", $js_origens).");";
  	print "if (document.getElementById(\"origem\")) atualizaSelect(document.getElementById(\"origem\"), origens);";

  } elseif ($_GET["a"] == "buscar_destinos" && $_GET["origem"]) {

    $_GET["origem"] .= ",{$_SESSION["usuario"]}";
    $destinos = $tarefas->buscaDestinos ($_GET["origem"]);
    for ($i = 0; $i < sizeof($destinos); $i++) {
    	$js_destinos[] = 'new Array("'.$destinos[$i]["id"].'", "'.$destinos[$i]["nome"].'", "'.$destinos[$i]["grupo"].'")';
    }
    print "var destinos = new Array(".@implode(", ", $js_destinos).");";
  	print "if (document.getElementById(\"destino\")) atualizaSelect(document.getElementById(\"destino\"), destinos);";

  } elseif ($_GET["a"] == "buscar_tipos" && $_GET["origem"] && $_GET["destino"]) {

    $tipos = $tarefas->buscaTipos(true);
    $tipos = $tarefas->filtraTiposDisponiveis($tipos, $_GET["origem"], $_GET["destino"]);
    foreach ($tipos as $tipo) {
      $js_tipos[] = "new Array(\"{$tipo["id"]}\", \"{$tipo["nome"]}\")";
    }
    print "var tipos = new Array(".@implode(", ", $js_tipos).");";
  	print "if (document.getElementById(\"tipo\")) atualizaSelect(document.getElementById(\"tipo\"), tipos);";

  } elseif ($_GET["a"] == "buscar_etapa_origens" && !empty($_GET["tarefa"])) {

    $etapa_origens = $tarefas->buscaEtapaOrigens($_GET["tarefa"], $_SESSION["usuario"]);
    for ($i = 0; $i < sizeof($etapa_origens); $i++) {
      $js_etapa_origens[] = 'new Array("'.$etapa_origens[$i]["id"].'", "'.$etapa_origens[$i]["nome"].'")';
    }
    print "var etapa_origens = new Array(".@implode(", ", $js_etapa_origens).");";
  	print "if (document.getElementById(\"etapa_origem\")) atualizaSelect(document.getElementById(\"etapa_origem\"),etapa_origens);";

  } elseif ($_GET["a"] == "buscar_etapa_tipos" && !empty($_GET["tarefa"]) && !empty($_GET["origem"])) {

    $etapa_tipos = $tarefas->buscaEtapaTipos();
    $etapa_tipos = $tarefas->filtraEtapaTiposDisponiveis($etapa_tipos, $_GET["tarefa"], $_GET["origem"].",".$_SESSION["usuario"]);
    for ($i = 0; $i < sizeof($etapa_tipos); $i++) {
      $js_etapa_tipos[] = 'new Array("'.$etapa_tipos[$i]["id"].'", "'.$etapa_tipos[$i]["nome"].'")';
    }
    print "var etapa_tipos = new Array(".@implode(", ", $js_etapa_tipos).");";
  	print "if (document.getElementById(\"etapa_tipo\")) atualizaSelect(document.getElementById(\"etapa_tipo\"),etapa_tipos);";

  } elseif ($_GET["a"] == "buscar_calendario_navegacao" && $_GET["referencia"] && $_GET["campo"]) {

    $referencia = @explode(",", $_GET["referencia"]);
    $referencia["setor"] = $tarefas->buscaDadosSetor($referencia[0]);
    $referencia["funcao"] = $tarefas->buscaDadosFuncao($referencia[1]);
    $referencia["usuario"] = ($referencia[2]) ? $tarefas->buscaDadosUsuario($referencia[2]) : $tarefas->buscaDadosUsuario($_SESSION["usuario"]);

    $minuto = date("i");
    if ($minuto % 10) {
    	$minuto = sprintf("%02s", $minuto - ($minuto % 10) + 10);
    	if ($minuto == 60 && date("H") < 23) {
    		$hora = sprintf("%02s", date("H") + 1);
    		$minuto = "00";
    	} else {
    		$hora = date("H");
    	}
    } else {
    	$minuto =  sprintf("%02s", $minuto - ($minuto % 10));
    	$hora = date("H");
    }

    if (!$_GET["valor"]) $_GET["valor"] = date("d/m/Y ($hora:$minuto)");
    $dia = substr($_GET["valor"], 0, 2);
    $mes = substr($_GET["valor"], 3, 2);
    $ano = substr($_GET["valor"], 6, 4);
    $horario = substr($_GET["valor"], 12, 5);

    $calendario = controle::instanciaObjeto("tarefas.calendario", $dia, $mes, $ano);
    $calendario->setLinkOnClick("window.opener.mudaTarefaDataHorario('[dia_atual]', '[mes]', '[ano]', document.getElementById('calendario_tarefa_horario').value);window.close();");
    $calendario->setLinkMesAnterior("window.location='rds.js.php?m=tarefas&a=buscar_calendario_navegacao&referencia={$_GET["referencia"]}&campo={$_GET["campo"]}&valor=[mes_anterior_dia]/[mes_anterior_mes]/[mes_anterior_ano]%20%28$horario%29';");
    $calendario->setLinkMesSeguinte("window.location='rds.js.php?m=tarefas&a=buscar_calendario_navegacao&referencia={$_GET["referencia"]}&campo={$_GET["campo"]}&valor=[mes_seguinte_dia]/[mes_seguinte_mes]/[mes_seguinte_ano]%20%28$horario%29';");
    $tarefas_calendario = $tarefas->buscaTarefasCalendario($_GET["dia"], $_GET["mes"], $_GET["ano"], $_GET["referencia"], "mes", "1");
    $eventos = $tarefas->geraTarefasCalendarioEventos($tarefas_calendario, "mes");
    $calendario->setEventosEmLote($eventos);

    $conteudo .= "<script type=\"text/javascript\">";
  	$conteudo .= " window.opener.tarefa_data_horario_campo = '{$_GET["campo"]}';";
  	$conteudo .= " window.opener.tarefa_data_horario_dia = '$dia';";
  	$conteudo .= " window.opener.tarefa_data_horario_mes = '$mes';";
  	$conteudo .= " window.opener.tarefa_data_horario_ano = '$ano';";
  	$conteudo .= " window.opener.tarefa_data_horario_horario = '$horario';";
    $conteudo .= "</script>";

    $conteudo .= stripslashes($calendario->geraCalendarioNavegacao());
  	$conteudo .= $tarefas->geraSelectHorarios($horario, $dia == date("d"));

    $interface = controle::instanciaObjeto("interfaces");
    $interface->geraTelaJanela($conteudo);
    print $interface->conteudo;

  } elseif ($_GET["a"] == "verifica_prazo") {

    $erro = array();

  	if ($_GET["prazo_inicio"]) {
    	$dia = substr($_GET["prazo_inicio"], 0, 2);
    	$mes = substr($_GET["prazo_inicio"], 3, 2);
    	$ano = substr($_GET["prazo_inicio"], 6, 4);
    	$hora = substr($_GET["prazo_inicio"], 12, 2);
    	$minuto = substr($_GET["prazo_inicio"], 15 , 2);
    	$prazo_inicio = strtotime("{$ano}-{$mes}-{$dia} {$hora}:{$minuto}:00");
    	if ($prazo_inicio < time()) {
    		$erro[] = "Início menor que a data atual";
    	}
  	}
  	if ($_GET["prazo_termino"]) {
    	$dia = substr($_GET["prazo_termino"], 0, 2);
    	$mes = substr($_GET["prazo_termino"], 3, 2);
    	$ano = substr($_GET["prazo_termino"], 6, 4);
    	$hora = substr($_GET["prazo_termino"], 12, 2);
    	$minuto = substr($_GET["prazo_termino"], 15 , 2);
    	$prazo_termino = strtotime("{$ano}-{$mes}-{$dia} {$hora}:{$minuto}:00");
    	if ($prazo_termino < time()) {
    		$erro[] = "Término menor que a data atual";
    	}
  	}
  	if ($_GET["prazo_termino"] && $_GET["prazo_termino"]) {
    	if ($prazo_termino < $prazo_inicio) {
    		$erro[] = "Término menor que o Início";
    	}
  	}
  	if ($erro) {
  		foreach($erro as $valor) {
  			$erro_invalido .= "* $valor\\n";
  		}
  		$erro_invalido = "O".(count($erro) > 1 ? "s" : "")." seguinte".(count($erro) > 1 ? "s" : "")." erro".(count($erro) > 1 ? "s" : "")." fo".(count($erro) > 1 ? "ram" : "i")." encontrado".(count($erro) > 1 ? "s" : "").":\\n\\n".$erro_invalido;
      print "alert('$erro_invalido');";
  	} else {
  		print "if (document.getElementById(\"{$_GET["formulario"]}\")) document.getElementById(\"{$_GET["formulario"]}\").submit();";
  	}

  } elseif ($_GET["a"] == "buscar_etapas") {

    $etapas = $tarefas->geraEtapasListagem($tarefas->buscaEtapas($_GET["tarefa_id"], $_GET["detalhada"]));
    print "if (document.getElementById(\"etapas\")) document.getElementById(\"etapas\").innerHTML = '".addslashes($etapas)."';";

  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var tarefas;

function buscarTarefaOrigens() {
  url = "rds.js.php?m=tarefas&a=buscar_origens";
  RDSCarregar("tarefas_buscar_origens", url);
}

function buscarTarefaDestinos(origem) {
  if (origem) {
	  url = "rds.js.php?m=tarefas&a=buscar_destinos&origem=" + origem;
    RDSCarregar("tarefas_buscar_destinos", url);
	}
}

function buscarTarefaTipos(origem, destino) {
  if (origem && destino) {
	  url = "rds.js.php?m=tarefas&a=buscar_tipos&origem=" + origem + "&destino=" + destino;
    RDSCarregar("tarefas_buscar_tipos", url);
	}
}

function buscarTarefaEtapaOrigens(tarefa, exibicao) {
  if (tarefa) {
    url = "rds.js.php?m=tarefas&a=buscar_etapa_origens&tarefa=" + tarefa + '&exibicao=' + exibicao;
    RDSCarregar("tarefas_buscar_etapa_origens", url);
	}
}

function buscarTarefaEtapaTipos(tarefa, origem) {
  if (tarefa && origem) {
    url = "rds.js.php?m=tarefas&a=buscar_etapa_tipos&tarefa=" + tarefa + "&origem=" + origem;
    RDSCarregar("tarefas_buscar_etapa_tipos", url);
	}
}

function carregaTarefaCalendarioNavegacao(campo, referencia, valor) {
	if (campo && referencia) {
    abrir("tarefa_calendario_navegacao", "rds.js.php?m=tarefas&a=buscar_calendario_navegacao&referencia=" + referencia + "&campo=" + campo + "&valor=" + valor, 250, 250, 0);
	}
}

var tarefa_data_horario_campo;
var tarefa_data_horario_dia;
var tarefa_data_horario_mes;
var tarefa_data_horario_ano;
var tarefa_data_horario_horario;

function mudaTarefaDataHorario(dia, mes, ano, horario) {
	if (dia) tarefa_data_horario_dia = dia;
	if (mes) tarefa_data_horario_mes = mes;
	if (ano) tarefa_data_horario_ano = ano;
	if (horario) tarefa_data_horario_horario = horario;
	if (tarefa_data_horario_campo && tarefa_data_horario_dia && tarefa_data_horario_mes && tarefa_data_horario_ano && tarefa_data_horario_horario) document.getElementById(tarefa_data_horario_campo).value = tarefa_data_horario_dia + '/' + tarefa_data_horario_mes + '/' + tarefa_data_horario_ano + ' (' + tarefa_data_horario_horario + ')';
}

function buscarTarefaListagem(dia, mes, ano, referencia, formato, status, evento) {
  if (navigator.appName.indexOf("Netscape") != -1) {
    if (evento && (evento.target.tagName == "IMG" || evento.target.tagName == "A")) return false;
  } else {
    if (evento && (evento.srcElement.tagName == "IMG" || evento.srcElement.tagName == "A")) return false;
  }
  if (dia) tarefa_listagem_dia = dia;
  if (mes) tarefa_listagem_mes = mes;
  if (ano) tarefa_listagem_ano = ano;
  if (referencia) tarefa_listagem_referencia = referencia;
  if (formato) tarefa_listagem_formato = formato;
  if (status) tarefa_listagem_status = status;
  // Monta URL
  url = "?m=tarefas&a=agenda";
  if (formato && formato != "mes") url += "&formato=" + formato;
  if (referencia) url += "&referencia=" + referencia;
  if (dia && formato != "mes") url += "&dia=" + dia;
  if (mes) url += "&mes=" + mes;
  if (ano) url += "&ano=" + ano;
  if (status) url += "&status=" + status;
  window.open(url, "_self");
}

function validaTarefaAdicionar(formulario) {
	var erro_vazio = new Array();
	var erro_invalido = new Array();
	if (!formulario.titulo.value) erro_vazio[erro_vazio.length] = "Título";
	if (!formulario.origem.value) erro_vazio[erro_vazio.length] = "Origem";
	if (!formulario.destino.value || formulario.destino.value == 'separador') erro_vazio[erro_vazio.length] = "Destino";
	if (!formulario.tipo.value && !formulario.tipo.disabled) erro_vazio[erro_vazio.length] = "Tipo";
  if (formulario.prazo_inicio.value && !validaDataHora(formulario.prazo_inicio.value)) erro_invalido[erro_invalido.length] = "Prazo » Início";
  if (formulario.prazo_termino.value && !validaDataHora(formulario.prazo_termino.value)) erro_invalido[erro_invalido.length] = "Prazo » Término";
	if (!formulario.descricao.value) erro_vazio[erro_vazio.length] = "Descrição";
  if (apresentaErros(erro_vazio, erro_invalido)) {
  	if (formulario.prazo_inicio.value || formulario.prazo_termino.value) {
  		url = "rds.js.php?m=tarefas&a=verifica_prazo&formulario=" + formulario.name + "&prazo_inicio=" + formulario.prazo_inicio.value + "&prazo_termino=" + formulario.prazo_termino.value;
  		RDSCarregar("tarefas_verifica_prazo", url);
	 		return false;
  	} else {
	 		return true;
  	}
  } else {
  	return false;
  }
}

function buscarEtapas(tarefa_id) {
  if (tarefa_id) {
    if (document.getElementById('tipo_1') && document.getElementById('tipo_1').checked) {
      detalhada = 0;
    }
    if (document.getElementById('tipo_2') && document.getElementById('tipo_2').checked) {
      detalhada = 1;
    }
	  url = "rds.js.php?m=tarefas&a=buscar_etapas&tarefa_id=" + tarefa_id + "&detalhada=" + detalhada;
    RDSCarregar("tarefas_buscar_etapas", url);
	}
}

function mudaTarefaOrigem(campo) {
  origem = campo.value;
  atualizaSelect(campo.form.destino);
  atualizaSelect(campo.form.tipo);
  if (origem) {
    buscarTarefaDestinos (origem);
  }
}

function mudaTarefaDestino(campo) {
  origem = campo.form.origem.value;
  destino = campo.value;
  atualizaSelect(campo.form.tipo);
  if (origem && destino) {
    buscarTarefaTipos (origem,destino);
  }
}

function mudaTarefaEtapaOrigem(campo) {
  etapa_origem = campo.value;
  atualizaSelect(campo.form.etapa_tipo);
  if (etapa_origem) {
    buscarTarefaEtapaTipos (tarefa_id, etapa_origem);
  }
}

function mudaTarefaEtapaTipo(campo) {
  tipos = new Array(0, 2, 4, 5, 7, 8, 9, 11);
  tipo = (campo.value) ? campo.value : '0';
  for (i = 0; i < tipos.length; i++) {
    document.getElementById("etapa_conteudo_" + tipos[i]).style.display = (tipos[i] == tipo ? (ehNetscape() ? 'table-row-group' : 'block') : 'none');
  }
  if (tipo == 2) {
    buscarTarefaTipos(campo.form.etapa_origem.value, campo.form.etapa_origem.value);
  }
}

function mudaAgendaExibicao(exibicao) {
  tarefa_exibicao = exibicao;
  url = "rds.js.php?m=tarefas&a=buscar_listagem&dia=" + tarefa_listagem_dia + "&mes=" + tarefa_listagem_mes + "&ano=" + tarefa_listagem_ano + "&referencia=" + tarefa_listagem_referencia + "&formato=" + tarefa_listagem_formato + "&status=" + tarefa_listagem_status + "&exibicao=" + tarefa_exibicao;
  RDSCarregar("tarefas_buscar_listagem", url);
}

<?

	if (false) { ?></script><? }

}

?>
