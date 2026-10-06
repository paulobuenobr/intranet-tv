<?

if ($_GET["a"]) {

	global $bancodedados;
	
	/**
	 * @var seguranca $seguranca
	 */
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
	$visitas = controle::instanciaObjeto("visitas", $bancodedados, $seguranca->permissoes, false);

  if ($_GET["a"] == "visita_tecnica_alocar") {
  	
	$visita_id = $visitas->getVisitaChamado($_GET["chamado_id"]);
	
	$visitas->visitasAlocar($visita_id, $_GET["data"], $_GET["periodo"]);
	
    $data = date("d/m/Y", strtotime($_GET["data"]));
    $periodos = array("1" => "Manhã", "2" => "Tarde", "3" => "Noite");
    $periodo = $periodos[$_GET["periodo"]];
	
  	$chamados = controle::instanciaObjeto("chamados", $bancodedados, $seguranca->permissoes, false);
	
  	$dados_chamado = $chamados->buscaDadosChamado($_GET["chamado_id"]);
	
  	if ($dados_chamado["categoria_id"] != 1052447 && $dados_chamado["categoria_id"] != 1052455) {
  		// Registra Categoria
  		$chamados->registraEtapa($dados_chamado, 2, array("categoria" => 1052447));
  		// Registra Responsável
		$chamados->registraEtapa($dados_chamado, 3, array("responsavel" => "21,0,0"));
  	}
  	$chamados->registraEtapa($dados_chamado, 24, array("data" => $data, "turno" => $periodo));
	
    // Atualiza chamado e fecha calendario
    print "if (window.opener && window.opener.atualizaChamado) window.opener.atualizaChamado();";
  	print("window.close();");    
  
  } elseif ($_GET["a"] == "visita_tecnica_realocar") {
  	
  	print "visita_ativa = '';";
	
	$visita = $visitas->buscaDadosVisita($_GET["visita_id"]);
	
    $visitas->visitasAlocar($_GET["visita_id"], $_GET["data"], $_GET["periodo"]);
	
    $dados_visita = $visitas->buscaDadosVisita($_GET["visita_id"]);
	
    $data = date("d/m/Y", strtotime($_GET["data"]));
    $periodos = array("1" => "Manhã", "2" => "Tarde", "3" => "Noite");
    $periodo = $periodos[$_GET["periodo"]];
	
    $chamados = controle::instanciaObjeto("chamados", $bancodedados, $seguranca->permissoes, false);
	
  	$dados_chamado = $chamados->buscaDadosChamado($dados_visita["chamado_id"]);
	
    $chamados->registraEtapa($dados_chamado, 24, array("data" => $data, "turno" => $periodo));

    $visitas_agenda = $visitas->geraAgendaVisitaTecnica($visita["tipo_id"], $_GET["mes"], $_GET["ano"], true);
    print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
    $visitas_hoje = $visitas->buscaVisitasTecnicaHoje($visita["tipo_id"]);
    print "if (document.getElementById(\"visitas_hoje_condominios\")) document.getElementById(\"visitas_hoje_condominios\").innerHTML = '".$visitas_hoje["condominios"]."';";
    print "if (document.getElementById(\"visitas_hoje_casas\")) document.getElementById(\"visitas_hoje_casas\").innerHTML = '".$visitas_hoje["casas"]."';";
    print "if (document.getElementById(\"visitas_hoje\")) document.getElementById(\"visitas_hoje\").innerHTML = '".addslashes($visitas_hoje["listagem"])."';";
    $visitas_proximos_dias = $visitas->buscaVisitasTecnicaProximosDias($visita["tipo_id"]);
    print "if (document.getElementById(\"visitas_proximos_dias_condominios\")) document.getElementById(\"visitas_proximos_dias_condominios\").innerHTML = '".$visitas_proximos_dias["condominios"]."';";
    print "if (document.getElementById(\"visitas_proximos_dias_casas\")) document.getElementById(\"visitas_proximos_dias_casas\").innerHTML = '".$visitas_proximos_dias["casas"]."';";
    print "if (document.getElementById(\"visitas_proximos_dias\")) document.getElementById(\"visitas_proximos_dias\").innerHTML = '".addslashes($visitas_proximos_dias["listagem"])."';";
    $visitas_pendentes = $visitas->buscaVisitasTecnicaPendentes($visita["tipo_id"]);
    print "if (document.getElementById(\"visitas_pendentes_condominios\")) document.getElementById(\"visitas_pendentes_condominios\").innerHTML = '".$visitas_pendentes["condominios"]."';";
    print "if (document.getElementById(\"visitas_pendentes_casas\")) document.getElementById(\"visitas_pendentes_casas\").innerHTML = '".$visitas_pendentes["casas"]."';";
    print "if (document.getElementById(\"visitas_pendentes\")) document.getElementById(\"visitas_pendentes\").innerHTML = '".addslashes($visitas_pendentes["listagem"])."';";  	
    $visitas_desagendadas = $visitas->buscaVisitasTecnicaDesagendadas($visita["tipo_id"]);
    print "if (document.getElementById(\"visitas_desagendadas_condominios\")) document.getElementById(\"visitas_desagendadas_condominios\").innerHTML = '".$visitas_desagendadas["condominios"]."';";
    print "if (document.getElementById(\"visitas_desagendadas_casas\")) document.getElementById(\"visitas_desagendadas_casas\").innerHTML = '".$visitas_desagendadas["casas"]."';";
    print "if (document.getElementById(\"visitas_desagendadas\")) document.getElementById(\"visitas_desagendadas\").innerHTML = '".addslashes($visitas_desagendadas["listagem"])."';";  	
    print "document.getElementById('painel_visita').style.display = 'none';";
  	
  } elseif (($_GET["a"] == "visitas_alocar") && $_GET["data"] && $_GET["periodo"]) {

  	if(!$_GET["visita"]){
		
  		$visitas->visitasReservar($_GET["data"], $_GET["periodo"]);
		$visitas_agenda = $visitas->buscaVisitasAgenda($_GET["mes"], $_GET["ano"]);
	    
		print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
  	
	} else {
		
	    $visitas->visitasAlocar($_GET["visita"], $_GET["data"], $_GET["periodo"]);
		
	    print "visita_ativa = '';";
	    $visitas_agenda = $visitas->buscaVisitasAgenda($_GET["mes"], $_GET["ano"]);
	    print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
	    $visitas_hoje = $visitas->buscaVisitasHoje();
	    print "if (document.getElementById(\"visitas_hoje_condominios\")) document.getElementById(\"visitas_hoje_condominios\").innerHTML = '".addslashes($visitas_hoje["condominios"])."';";
	    print "if (document.getElementById(\"visitas_hoje_casas\")) document.getElementById(\"visitas_hoje_casas\").innerHTML = '".addslashes($visitas_hoje["casas"])."';";
	    print "if (document.getElementById(\"visitas_hoje\")) document.getElementById(\"visitas_hoje\").innerHTML = '".addslashes($visitas_hoje["listagem"])."';";
	    $visitas_proximos_dias = $visitas->buscaVisitasProximosDias();
	    print "if (document.getElementById(\"visitas_proximos_dias_condominios\")) document.getElementById(\"visitas_proximos_dias_condominios\").innerHTML = '".addslashes($visitas_proximos_dias["condominios"])."';";
	    print "if (document.getElementById(\"visitas_proximos_dias_casas\")) document.getElementById(\"visitas_proximos_dias_casas\").innerHTML = '".addslashes($visitas_proximos_dias["casas"])."';";
	    print "if (document.getElementById(\"visitas_proximos_dias\")) document.getElementById(\"visitas_proximos_dias\").innerHTML = '".addslashes($visitas_proximos_dias["listagem"])."';";
	    $visitas_pendentes = $visitas->buscaVisitasPendentes();
	    print "if (document.getElementById(\"visitas_pendentes_condominios\")) document.getElementById(\"visitas_pendentes_condominios\").innerHTML = '".addslashes($visitas_pendentes["condominios"])."';";
	    print "if (document.getElementById(\"visitas_pendentes_casas\")) document.getElementById(\"visitas_pendentes_casas\").innerHTML = '".addslashes($visitas_pendentes["casas"])."';";
	    print "if (document.getElementById(\"visitas_pendentes\")) document.getElementById(\"visitas_pendentes\").innerHTML = '".addslashes($visitas_pendentes["listagem"])."';";
	    $visitas_aguardando_sem_contato = $visitas->buscaVisitasAguardandoSemContato();
	    print "if (document.getElementById(\"visitas_aguardando_sem_contato_condominios\")) document.getElementById(\"visitas_aguardando_sem_contato_condominios\").innerHTML = '".addslashes($visitas_aguardando_sem_contato["condominios"])."';";
	    print "if (document.getElementById(\"visitas_aguardando_sem_contato_casas\")) document.getElementById(\"visitas_aguardando_sem_contato_casas\").innerHTML = '".addslashes($visitas_aguardando_sem_contato["casas"])."';";
	    print "if (document.getElementById(\"visitas_aguardando_sem_contato\")) document.getElementById(\"visitas_aguardando_sem_contato\").innerHTML = '".addslashes($visitas_aguardando_sem_contato["listagem"])."';";
	    $visitas_aguardando_com_contato = $visitas->buscaVisitasAguardandoComContato();
	    print "if (document.getElementById(\"visitas_aguardando_com_contato_condominios\")) document.getElementById(\"visitas_aguardando_com_contato_condominios\").innerHTML = '".addslashes($visitas_aguardando_com_contato["condominios"])."';";
	    print "if (document.getElementById(\"visitas_aguardando_com_contato_casas\")) document.getElementById(\"visitas_aguardando_com_contato_casas\").innerHTML = '".addslashes($visitas_aguardando_com_contato["casas"])."';";
	    print "if (document.getElementById(\"visitas_aguardando_com_contato\")) document.getElementById(\"visitas_aguardando_com_contato\").innerHTML = '".addslashes($visitas_aguardando_com_contato["listagem"])."';";
	    print "document.getElementById('painel_visita').style.display = 'none';";
		
  	}
  	
  } elseif (($_GET["a"] == "visitas_desreservar") && $_GET["visita_id"]) {
  	
    $visitas->visitasDeseservar($_GET["visita_id"]);
  	$visitas_agenda = $visitas->buscaVisitasAgenda($_GET["mes"], $_GET["ano"]);
  	print "if (visita_ativa) definirVisita(visita_ativa, null, 1);";
  	print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
  
  } elseif (($_GET["a"] == "visitas_alocar_migracao") && $_GET["visita"] && $_GET["data"] && $_GET["periodo"]) {
  	
    $visitas->visitasAlocarMigracao($_GET["visita"], $_GET["data"], $_GET["periodo"]);
	
    print "visita_ativa = '';";
    $visitas_agenda = $visitas->buscaVisitasAgendaMigracao($_GET["mes"], $_GET["ano"]);
    print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
    $visitas_hoje = $visitas->buscaVisitasMigracaoHoje();
    print "if (document.getElementById(\"visitas_hoje_condominios\")) document.getElementById(\"visitas_hoje_condominios\").innerHTML = '".addslashes($visitas_hoje["condominios"])."';";
    print "if (document.getElementById(\"visitas_hoje_casas\")) document.getElementById(\"visitas_hoje_casas\").innerHTML = '".addslashes($visitas_hoje["casas"])."';";
    print "if (document.getElementById(\"visitas_hoje\")) document.getElementById(\"visitas_hoje\").innerHTML = '".addslashes($visitas_hoje["listagem"])."';";
    $visitas_proximos_dias = $visitas->buscaVisitasMigracaoProximosDias();
    print "if (document.getElementById(\"visitas_proximos_dias_condominios\")) document.getElementById(\"visitas_proximos_dias_condominios\").innerHTML = '".addslashes($visitas_proximos_dias["condominios"])."';";
    print "if (document.getElementById(\"visitas_proximos_dias_casas\")) document.getElementById(\"visitas_proximos_dias_casas\").innerHTML = '".addslashes($visitas_proximos_dias["casas"])."';";
    print "if (document.getElementById(\"visitas_proximos_dias\")) document.getElementById(\"visitas_proximos_dias\").innerHTML = '".addslashes($visitas_proximos_dias["listagem"])."';";
    $visitas_pendentes = $visitas->buscaVisitasMigracaoPendentes();
    print "if (document.getElementById(\"visitas_pendentes_condominios\")) document.getElementById(\"visitas_pendentes_condominios\").innerHTML = '".addslashes($visitas_pendentes["condominios"])."';";
    print "if (document.getElementById(\"visitas_pendentes_casas\")) document.getElementById(\"visitas_pendentes_casas\").innerHTML = '".addslashes($visitas_pendentes["casas"])."';";
    print "if (document.getElementById(\"visitas_pendentes\")) document.getElementById(\"visitas_pendentes\").innerHTML = '".addslashes($visitas_pendentes["listagem"])."';";
    $visitas_desagendadas = $visitas->buscaVisitasMigracaoDesagendadas();
    print "if (document.getElementById(\"visitas_desagendadas_condominios\")) document.getElementById(\"visitas_desagendadas_condominios\").innerHTML = '".$visitas_desagendadas["condominios"]."';";
    print "if (document.getElementById(\"visitas_desagendadas_casas\")) document.getElementById(\"visitas_desagendadas_casas\").innerHTML = '".$visitas_desagendadas["casas"]."';";
    print "if (document.getElementById(\"visitas_desagendadas\")) document.getElementById(\"visitas_desagendadas\").innerHTML = '".addslashes($visitas_desagendadas["listagem"])."';";  	
    print "document.getElementById('painel_visita').style.display = 'none';";

  } elseif (($_GET["a"] == "atualiza_visitas") && $_GET["mes"] && $_GET["ano"]) {

    print "visita_ativa = '';";
    print "document.getElementById('painel_visita').style.display = 'none';";
    $visitas_agenda = $visitas->buscaVisitasAgenda($_GET["mes"], $_GET["ano"]);
    print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
    $visitas_hoje = $visitas->buscaVisitasHoje();
    print "if (document.getElementById(\"visitas_hoje_condominios\")) document.getElementById(\"visitas_hoje_condominios\").innerHTML = '".addslashes($visitas_hoje["condominios"])."';";
    print "if (document.getElementById(\"visitas_hoje_casas\")) document.getElementById(\"visitas_hoje_casas\").innerHTML = '".addslashes($visitas_hoje["casas"])."';";
    print "if (document.getElementById(\"visitas_hoje\")) document.getElementById(\"visitas_hoje\").innerHTML = '".addslashes($visitas_hoje["listagem"])."';";
    $visitas_proximos_dias = $visitas->buscaVisitasProximosDias();
    print "if (document.getElementById(\"visitas_proximos_dias_condominios\")) document.getElementById(\"visitas_proximos_dias_condominios\").innerHTML = '".addslashes($visitas_proximos_dias["condominios"])."';";
    print "if (document.getElementById(\"visitas_proximos_dias_casas\")) document.getElementById(\"visitas_proximos_dias_casas\").innerHTML = '".addslashes($visitas_proximos_dias["casas"])."';";
    print "if (document.getElementById(\"visitas_proximos_dias\")) document.getElementById(\"visitas_proximos_dias\").innerHTML = '".addslashes($visitas_proximos_dias["listagem"])."';";
    $visitas_pendentes = $visitas->buscaVisitasPendentes();
    print "if (document.getElementById(\"visitas_pendentes_condominios\")) document.getElementById(\"visitas_pendentes_condominios\").innerHTML = '".addslashes($visitas_pendentes["condominios"])."';";
    print "if (document.getElementById(\"visitas_pendentes_casas\")) document.getElementById(\"visitas_pendentes_casas\").innerHTML = '".addslashes($visitas_pendentes["casas"])."';";
    print "if (document.getElementById(\"visitas_pendentes\")) document.getElementById(\"visitas_pendentes\").innerHTML = '".addslashes($visitas_pendentes["listagem"])."';";
    $visitas_aguardando_sem_contato = $visitas->buscaVisitasAguardandoSemContato();
    print "if (document.getElementById(\"visitas_aguardando_sem_contato_condominios\")) document.getElementById(\"visitas_aguardando_sem_contato_condominios\").innerHTML = '".addslashes($visitas_aguardando_sem_contato["condominios"])."';";
    print "if (document.getElementById(\"visitas_aguardando_sem_contato_casas\")) document.getElementById(\"visitas_aguardando_sem_contato_casas\").innerHTML = '".addslashes($visitas_aguardando_sem_contato["casas"])."';";
    print "if (document.getElementById(\"visitas_aguardando_sem_contato\")) document.getElementById(\"visitas_aguardando_sem_contato\").innerHTML = '".addslashes($visitas_aguardando_sem_contato["listagem"])."';";
    $visitas_aguardando_com_contato = $visitas->buscaVisitasAguardandoComContato();
    print "if (document.getElementById(\"visitas_aguardando_com_contato_condominios\")) document.getElementById(\"visitas_aguardando_com_contato_condominios\").innerHTML = '".addslashes($visitas_aguardando_com_contato["condominios"])."';";
    print "if (document.getElementById(\"visitas_aguardando_com_contato_casas\")) document.getElementById(\"visitas_aguardando_com_contato_casas\").innerHTML = '".addslashes($visitas_aguardando_com_contato["casas"])."';";
    print "if (document.getElementById(\"visitas_aguardando_com_contato\")) document.getElementById(\"visitas_aguardando_com_contato\").innerHTML = '".addslashes($visitas_aguardando_com_contato["listagem"])."';";

  } elseif (($_GET["a"] == "atualiza_visitas_tecnicas") && $_GET["mes"] && $_GET["ano"]) {
  	
    print "visita_ativa = '';";
  	print "document.getElementById('painel_visita').style.display = 'none';";
  	$visitas_agenda = $visitas->geraAgendaVisitaTecnica($_GET["visita_tipo"], $_GET["mes"], $_GET["ano"], true);
    print "if (document.getElementById(\"visitas_agenda\")) document.getElementById(\"visitas_agenda\").innerHTML = '".addslashes($visitas_agenda)."';";
    $visitas_hoje = $visitas->buscaVisitasTecnicaHoje($_GET["visita_tipo"]);
    print "if (document.getElementById(\"visitas_hoje_condominios\")) document.getElementById(\"visitas_hoje_condominios\").innerHTML = '".$visitas_hoje["condominios"]."';";
    print "if (document.getElementById(\"visitas_hoje_casas\")) document.getElementById(\"visitas_hoje_casas\").innerHTML = '".$visitas_hoje["casas"]."';";
    print "if (document.getElementById(\"visitas_hoje\")) document.getElementById(\"visitas_hoje\").innerHTML = '".addslashes($visitas_hoje["listagem"])."';";
    $visitas_proximos_dias = $visitas->buscaVisitasTecnicaProximosDias($_GET["visita_tipo"]);
    print "if (document.getElementById(\"visitas_proximos_dias_condominios\")) document.getElementById(\"visitas_proximos_dias_condominios\").innerHTML = '".$visitas_proximos_dias["condominios"]."';";
    print "if (document.getElementById(\"visitas_proximos_dias_casas\")) document.getElementById(\"visitas_proximos_dias_casas\").innerHTML = '".$visitas_proximos_dias["casas"]."';";
    print "if (document.getElementById(\"visitas_proximos_dias\")) document.getElementById(\"visitas_proximos_dias\").innerHTML = '".addslashes($visitas_proximos_dias["listagem"])."';";
    $visitas_pendentes = $visitas->buscaVisitasTecnicaPendentes($_GET["visita_tipo"]);
    print "if (document.getElementById(\"visitas_pendentes_condominios\")) document.getElementById(\"visitas_pendentes_condominios\").innerHTML = '".$visitas_pendentes["condominios"]."';";
    print "if (document.getElementById(\"visitas_pendentes_casas\")) document.getElementById(\"visitas_pendentes_casas\").innerHTML = '".$visitas_pendentes["casas"]."';";
    print "if (document.getElementById(\"visitas_pendentes\")) document.getElementById(\"visitas_pendentes\").innerHTML = '".addslashes($visitas_pendentes["listagem"])."';";  	
    $visitas_desagendadas = $visitas->buscaVisitasTecnicaDesagendadas($_GET["visita_tipo"]);
    print "if (document.getElementById(\"visitas_desagendadas_condominios\")) document.getElementById(\"visitas_desagendadas_condominios\").innerHTML = '".$visitas_desagendadas["condominios"]."';";
    print "if (document.getElementById(\"visitas_desagendadas_casas\")) document.getElementById(\"visitas_desagendadas_casas\").innerHTML = '".$visitas_desagendadas["casas"]."';";
    print "if (document.getElementById(\"visitas_desagendadas\")) document.getElementById(\"visitas_desagendadas\").innerHTML = '".addslashes($visitas_desagendadas["listagem"])."';";  	
 
  }

} else {
	
	if (false) { ?><script type="text/javascript"><? }
	
?>

var visita_ativa_original_fundo;
var visita_ativa_original_borda;
var vTimerDblClick = null;
var vTimerDlbClickDisparado = false;

function definirVisita(visita, cliente_tipo, gatilho, redefinicao) {
	
  if (!redefinicao) {
    if (vTimerDlbClickDisparado && !gatilho) {
      return false;
    }
    if (!gatilho) {
      vTimerDblClick = setTimeout('definirVisita(' + visita + ', "' + cliente_tipo + '", 1)', 250);
      vTimerDlbClickDisparado = true;
      return false;
    }
    vTimerDlbClickDisparado = false;
    vTimerDblClick = null;
  }
  if (visita == visita_ativa) {
    document.getElementById('painel_visita').style.display = 'none';
    if (ehNetscape()) {
      document.getElementById('tabela_painel_visita').innerHTML = '';
    } else {
      while (document.getElementById("tabela_painel_visita").cells.length > 0) {
        document.getElementById("tabela_painel_visita").deleteCell();
      }
    }
    document.getElementById("visitas_" + visita).style.backgroundColor = '';
    //document.getElementById("visitas_tipo_" + visita).style.backgroundColor = '#828282';
    //document.getElementById("visitas_contrato_" + visita).style.backgroundColor = '#828282';
    //document.getElementById("visitas_visita_" + visita).style.backgroundColor = '#828282';
   // if(document.getElementById("visitas_vendedor_" + visita)) document.getElementById("visitas_vendedor_" + visita).style.backgroundColor = '#828282';
    
    if (document.getElementById("agenda_" + visita) != null) {
      $("#agenda_" + visita).removeClass("ativa");
      if (visita_ativa_cliente_tipo == "residencial") {
 	      $(".lacunaVisita.condominial").removeClass("inativa");
       } else if (visita_ativa_cliente_tipo == "condominial") {
 	      $(".lacunaVisita.residencial").removeClass("inativa");
       }
      console.log(cliente_tipo);
      console.log(visita);
      if (cliente_tipo == undefined || cliente_tipo == "undefined" || !cliente_tipo || cliente_tipo == "") {
       document.getElementById("agenda_" + visita).style.backgroundColor = visita_ativa_original_fundo;
       document.getElementById("agenda_" + visita).parentNode.style.border = visita_ativa_original_borda;
      }
      
    }
    visita_ativa = "";
    visita_ativa_cliente_tipo = "";
  } else {
    if (visita_ativa) definirVisita(visita_ativa, null, 1);
    document.getElementById("visitas_" + visita).style.backgroundColor = '#ffcc66';
    //document.getElementById("visitas_tipo_" + visita).style.backgroundColor = '#cf9c36';
    //document.getElementById("visitas_contrato_" + visita).style.backgroundColor = '#cf9c36';
    //document.getElementById("visitas_visita_" + visita).style.backgroundColor = '#cf9c36';
   // if(document.getElementById("visitas_vendedor_" + visita)) document.getElementById("visitas_vendedor_" + visita).style.backgroundColor = '#cf9c36';
    if (document.getElementById("agenda_" + visita) != null) {
      $("#agenda_" + visita).addClass("ativa");
       if (cliente_tipo == "residencial") {
 	      $(".lacunaVisita.condominial").addClass("inativa");
       } else if (cliente_tipo == "condominial") {
 	      $(".lacunaVisita.residencial").addClass("inativa");
       } else {
	      visita_ativa_original_fundo = document.getElementById("agenda_" + visita).style.backgroundColor;
	      visita_ativa_original_borda = document.getElementById("agenda_" + visita).parentNode.style.border;
	      document.getElementById("agenda_" + visita).style.backgroundColor = "#FFBA00";
	      document.getElementById("agenda_" + visita).parentNode.style.border = "1px solid #916A00";
       }
    }
    if (ehNetscape()) {
      document.getElementById('tabela_painel_visita_cabecalho').innerHTML = document.getElementById('visitas_' + visita).parentNode.rows[0].innerHTML;
      document.getElementById('tabela_painel_visita').innerHTML = document.getElementById("visitas_" + visita).innerHTML;
    } else {
      while (document.getElementById("tabela_painel_visita_cabecalho").cells.length > 0) {
        document.getElementById("tabela_painel_visita_cabecalho").deleteCell();
      }
      while (document.getElementById("tabela_painel_visita").cells.length > 0) {
        document.getElementById("tabela_painel_visita").deleteCell();
      }
      for (i = 0; i < document.getElementById("visitas_" + visita).cells.length; i++) {
        document.getElementById('tabela_painel_visita').appendChild(document.getElementById("visitas_" + visita).cells[i].cloneNode(true));
      }
      for (i = 0; i < document.getElementById("visitas_" + visita).cells.length; i++) {
        document.getElementById('tabela_painel_visita_cabecalho').appendChild(document.getElementById('visitas_' + visita).parentNode.rows[0].cells[i].cloneNode(true));
      }
    }
    var posicao = findPos(document.getElementById("visitas_" + visita));
    document.getElementById('painel_visita').style.left = posicao[0];
    document.getElementById('painel_visita').style.top = posicao[1] - 15;
    document.getElementById('painel_visita').style.display = 'block';
    JSFX_FloatDiv("painel_visita").flt();
    visita_ativa = visita;
    visita_ativa_cliente_tipo = cliente_tipo;
  }
}

function resetaGatilho() {
  clearTimeout(vTimerDblClick);
  vTimerDlbClickDisparado = false;
}

function alocarVisita(data, periodo) {
    url = "rds.js.php?m=visitas&a=visitas_alocar&visita=" + visita_ativa + "&data=" + data + "&periodo=" + periodo + "&mes=" + agenda_mes + "&ano=" + agenda_ano;
    RDSCarregar("visitas_visitas_alocar", url);
}

function desreservarVisita(visita_id, gatilho) {
	if (visita_id) {
		
    if (vTimerDlbClickDisparado && !gatilho) {
      return false;
    }
    if (!gatilho) {
      vTimerDblClick = setTimeout('desreservarVisita(' + visita_id + ', 1)', 250);
      vTimerDlbClickDisparado = true;
      return false;
    }
    vTimerDlbClickDisparado = false;
    vTimerDblClick = null;
		
		url = "rds.js.php?m=visitas&a=visitas_desreservar&visita_id=" + visita_id + "&mes=" + agenda_mes + "&ano=" + agenda_ano;
	  RDSCarregar("visitas_visitas_desreservar", url);
	}
}

function alocarVisitaMigracao(data, periodo) {
  if (visita_ativa) {
    url = "rds.js.php?m=visitas&a=visitas_alocar_migracao&visita=" + visita_ativa + "&data=" + data + "&periodo=" + periodo + "&mes=" + agenda_mes + "&ano=" + agenda_ano;
    RDSCarregar("visitas_visitas_alocar_migracao", url);
  }
}

function alterarTurno(data, periodo) {
	if (data && periodo) {
    abrir("turno", "?m=visitas&a=alterar_turno&visita_tipo=" + visita_tipo + "&data=" + data + "&periodo=" + periodo, 400 , 250, 1);
	}
}


function verChamado(chamado) {
	if (chamado) {
    abrir("chamado", "?m=chamados&a=ver&i=" + chamado, 625, 520, 1);
	}
}

function alocarVisitaTecnica(data, periodo, chamado_id) {
  if (chamado_id == undefined) {
	url = "rds.js.php?m=visitas&a=visita_tecnica_realocar&visita_tipo=" + visita_tipo + "&visita_id=" + visita_ativa + "&data=" + data + "&periodo=" + periodo + "&mes=" + agenda_mes + "&ano=" + agenda_ano;
  } else {
    url = "rds.js.php?m=visitas&a=visita_tecnica_alocar&visita_tipo=" + visita_tipo + "&chamado_id=" + chamado_id + "&data=" + data + "&periodo=" + periodo + "&mes=" + agenda_mes + "&ano=" + agenda_ano;
  }
  RDSCarregar("visitas_visitas_tecnica_alocar", url);
}

function atualizaVisitas(visita_tipo) {
  if (agenda_mes && agenda_ano) {
	  if(visita_tipo == 2 || visita_tipo == 4){
		url = "rds.js.php?m=visitas&a=atualiza_visitas_tecnicas&visita_tipo=" + visita_tipo + "&mes=" + agenda_mes + "&ano=" + agenda_ano;
		RDSCarregar("visitas_atualiza_visitas_tecnicas", url);
	  }else{
    	url = "rds.js.php?m=visitas&a=atualiza_visitas&mes=" + agenda_mes + "&ano=" + agenda_ano;
    	RDSCarregar("visitas_atualiza_visitas", url);
	  }
  }
}

function validaVisitasAtivar(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.data_ativacao.value) {
    erro_vazio[erro_vazio.length] = "Data » Ativação";
  } else if (!validaData(formulario.data_ativacao.value)) {
    erro_invalido[erro_invalido.length] = "Data » Ativação";
  }

  if (formulario.instalador && !formulario.instalador.value && !formulario.instalador.disabled) {
    erro_vazio[erro_vazio.length] = "Instalador";
  }

  if (formulario.com_pendencias.checked && !formulario.comentario.value) {
    erro_vazio[erro_vazio.length] = "Comentário";
  }

  if (retorno = apresentaErros(erro_vazio, erro_invalido)) {
    formulario.confirmar.disabled = true;
  }

  return retorno;
}

function validaVisita(formulario) {
  var erro_vazio = new Array();
  var erro_invalido = new Array();

  if (!formulario.comentario_categoria.value) {
    erro_vazio[erro_vazio.length] = "Novo Comentário » Categoria";
  }

  return apresentaErros(erro_vazio,erro_invalido);
}

var chamado_imprimir;

function imprimeChamados(chamados) {
    parametros = "top,left,width=" + ((screen.availWidth > 760) ? 760 : screen.availWidth - 10) + ",height=" + (screen.availHeight - 50) + "statusbar=no,toolbar=no,scrollbars=yes,resizable=1";
  	chamado_imprimir = window.open("?m=chamados&a=imprimir&i=" + chamados, "chamado_imprimir", parametros);
    chamado_imprimir.focus();
}

<?

	if (false) { ?></script><? }

}

?>
