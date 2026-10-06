<?

require_once(DIR_PAINEL."painel.placar.geral.inc.php");
require_once(DIR_PAINEL."painel.placar.comercial.inc.php");
require_once(DIR_PAINEL."painel.placar.administrativo.inc.php");
require_once(DIR_PAINEL."painel.placar.financeiro.inc.php");
require_once(DIR_PAINEL."painel.mensagem.inc.php");

global $seguranca;
global $bancodedados;

if (!$seguranca) {
	$seguranca = controle::instanciaObjeto("seguranca", $bancodedados, $_SESSION["usuario"]);
}

if ($_GET && array_key_exists("ps", $_GET) && $_GET["ps"]) {
	$_SESSION["placar_setor"] = $_GET["ps"];
}
if ($_GET && array_key_exists("pm", $_GET) && $_GET["pm"]) {
	$_SESSION["placar_mes"] = $_GET["pm"];
} elseif (!$_SESSION["placar_mes"]) {
	$_SESSION["placar_mes"] = date("m");
}
if ($_GET && array_key_exists("pa", $_GET) && $_GET["pa"]) {
	$_SESSION["placar_ano"] = $_GET["pa"];
} elseif (!$_SESSION["placar_ano"]) {
	$_SESSION["placar_ano"] = date("Y");
}

if (array_key_exists("painel", $seguranca->permissoes)) {
	if (!$_SESSION["placar_setor"] || !array_key_exists($_SESSION["placar_setor"], $seguranca->permissoes["painel"])) {
	  if (array_key_exists("comercial", $seguranca->permissoes["painel"])) $_SESSION["placar_setor"] = "comercial";
	  elseif (array_key_exists("administrativo", $seguranca->permissoes["painel"])) $_SESSION["placar_setor"] = "administrativo";
	  elseif (array_key_exists("financeiro", $seguranca->permissoes["painel"])) $_SESSION["placar_setor"] = "financeiro";
	}
}

$_SESSION["placar_dia"] = $_SESSION["placar_ano"].$_SESSION["placar_mes"] != date("Ym") ? date("t", strtotime("{$_SESSION["placar_ano"]}-{$_SESSION["placar_mes"]}-01")) : date("d");

$painel_mensagem = geraPainelMensagem($_SESSION["usuario"]);
$painel_placar = geraPainelPlacar($_SESSION["placar_setor"], $_SESSION["placar_dia"], $_SESSION["placar_mes"], $_SESSION["placar_ano"]);

function geraPainelPlacar($setor, $dia, $mes, $ano) {
	global $seguranca;
	$setores = array();
	if (array_key_exists("painel", $seguranca->permissoes)) {
	  if (array_key_exists("comercial", $seguranca->permissoes["painel"])) {
	  	$setores[] = '<td bgcolor="' . ($setor == "comercial" ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\'comercial\',\''.$mes.'\',\''.$ano.'\')">Comercial</a></td>';
	  }
	  if (array_key_exists("administrativo", $seguranca->permissoes["painel"])) {
	  	$setores[] = '<td bgcolor="' . ($setor == "administrativo" ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\'administrativo\',\''.$mes.'\',\''.$ano.'\')">Administrativo</a></td>';
	  }
	  if (array_key_exists("financeiro", $seguranca->permissoes["painel"])) {
	  	$setores[] = '<td bgcolor="' . ($setor == "financeiro" ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\'financeiro\',\''.$mes.'\',\''.$ano.'\')">Financeiro</a></td>';
	  }
	}
  if (count($setores)) {
    $setores = implode('<td bgcolor="#FFFFFF" width="1"></td>', $setores);
    $conteudo = array_key_exists("painel", $seguranca->permissoes) && array_key_exists($setor, $seguranca->permissoes["painel"]) ? geraPainelPlacarConteudo($setor, $dia, $mes, $ano) : "Conteúdo Indisponível!";
    $estrutura = '
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#252525">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr height="15" align="center">' . $setores . '</tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" height="1"></td>
  </tr>
  <tr>
    <td align="center" valign="middle" height="91" class="txtBranco10">' . $conteudo . '</td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" height="1"></td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr height="15" align="center">
          <td bgcolor="' . (($mes=="01") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'01\',\''.$ano.'\')">Jan</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="02") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'02\',\''.$ano.'\')">Fev</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="03") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'03\',\''.$ano.'\')">Mar</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="04") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'04\',\''.$ano.'\')">Abr</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="05") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'05\',\''.$ano.'\')">Mai</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="06") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'06\',\''.$ano.'\')">Jun</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="07") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'07\',\''.$ano.'\')">Jul</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="08") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'08\',\''.$ano.'\')">Ago</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="09") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'09\',\''.$ano.'\')">Set</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="10") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'10\',\''.$ano.'\')">Out</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="11") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'11\',\''.$ano.'\')">Nov</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="' . (($mes=="12") ? '#505050' : '#828282') . '"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\'12\',\''.$ano.'\')">Dez</a></td>
          <td bgcolor="#FFFFFF" width="2"></td>
          <td bgcolor="#828282"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\''.$mes.'\',\''.($ano-1).'\')">&nbsp;&laquo;&nbsp;</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="#505050"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\''.$mes.'\',\''.$ano.'\')">'.$ano.'</a></td>
          <td bgcolor="#FFFFFF" width="1"></td>
          <td bgcolor="#828282"><a class="txtBranco10" href="javascript:carregaPainel(\''.$setor.'\',\''.$mes.'\',\''.($ano+1).'\')">&nbsp;&raquo;&nbsp;</a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
';
  } else {
    $estrutura = '
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#252525">
  <tr>
    <td align="center" valign="middle" height="91" class="txtBranco10">Painel Indisponível!</td>
  </tr>
</table>
';
  }
  $estrutura = preg_replace("/[\r\n\t ]+/", " ", $estrutura);
  $estrutura = str_replace("'", "\\'", $estrutura);
  return $estrutura;
}

function geraPainelPlacarConteudo($setor, $dia, $mes, $ano) {
	if ($setor == "administrativo") {
		$conteudo = verificaPainel(1, $dia, $mes, $ano);
	} elseif ($setor == "comercial") {
		$conteudo = verificaPainel(2, $dia, $mes, $ano);
	} elseif ($setor == "financeiro") {
		$conteudo = verificaPainel(3, $dia, $mes, $ano);
	} elseif ($setor == "tecnico") {
		$conteudo = verificaPainel(4, $dia, $mes, $ano);
	}
  return stripslashes($conteudo);
}

function verificaPainel($setor, $dia, $mes, $ano) {
	if (buscaPlacaresNoDia($setor, $dia, $mes, $ano) > 0) {
		$conteudo = carregaPainel($setor, $dia, $mes, $ano);
	} else {
		switch ($setor) {
		case 1:
		  $conteudo = addslashes(geraPainelPlacarConteudoAdministrativo($dia, $mes, $ano));
			break;
		case 2:
	    $conteudo = addslashes(geraPainelPlacarConteudoComercial($dia, $mes, $ano));
			break;
		case 3:
		  $conteudo = addslashes(geraPainelPlacarConteudoFinanceiro($mes, $ano));
			break;
		}
		armazenaPainel($setor, $dia, $mes, $ano, trim($conteudo));
	}
	return $conteudo;
}

function armazenaPainel($setor, $dia, $mes, $ano, $conteudo) {
  global $bancodedados;
	if (buscaPlacaresNoDia($setor, $dia, $mes, $ano) == 0) {
		$query = "INSERT INTO tab_placares (plb_id, plc_data, plc_geracao, plc_conteudo) VALUES ('$setor', '$ano$mes$dia', NOW(), '$conteudo')";
		$query = $bancodedados->query($query);
 	} else {
		$query = "UPDATE tab_placares SET plc_conteudo = '$conteudo', plc_geracao = NOW() WHERE (plb_id = '$setor' AND plc_data = '$ano$mes$dia') LIMIT 1";
		$query = $bancodedados->query($query);
 	}
}

function carregaPainel($setor_id, $dia, $mes, $ano) {
	global $bancodedados;
	$query = "
    SELECT
      plc_conteudo AS conteudo
    FROM
      tab_placares
    WHERE
      plb_id = $setor_id AND
      plc_data = '$ano-$mes-$dia'";
	$query = $bancodedados->query($query);
	return $bancodedados->resultado($query,0,0);
}

function buscaPlacaresNoDia($setor_id, $dia, $mes, $ano) {
	global $bancodedados;
	$query = "
    SELECT
      COUNT(plc_id)
    FROM
      tab_placares
    WHERE
      plb_id = $setor_id AND
      plc_data = '$ano-$mes-$dia'";
	$query = $bancodedados->query($query);
	return $bancodedados->resultado($query);
}

function carregaArquivo($arquivo) {
  $fp = fopen($arquivo, "r");
  $conteudo = fread($fp, filesize($arquivo));
  fclose($fp);
  return $conteudo;
}

function gravaArquivo($arquivo, $conteudo) {
  $fp = fopen($arquivo, "w+");
  fwrite($fp, trim($conteudo));
  fclose($fp);
}

if ($_GET && array_key_exists("m", $_GET) && $_GET["m"] == "painel") {
	$painel_datahora = date("d/m H:i");
	print "if (document.getElementById('painel_mensagem')) document.getElementById('painel_mensagem').innerHTML = '$painel_mensagem';";
  print "if (document.getElementById('painel_datahora')) document.getElementById('painel_datahora').innerHTML = '$painel_datahora';";
  print "if (document.getElementById('painel_placar')) document.getElementById('painel_placar').innerHTML = '$painel_placar';";
}

?>
