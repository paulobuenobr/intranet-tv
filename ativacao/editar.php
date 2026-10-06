<?
  $id = $_GET['id'];
  $modo = $_GET['modo'];

  require_once('conexao.php');
  require_once('validacao.inc');

  switch($modo) {
//  case ('administrativo'):
//          administrativo($id);
//          break;
  case ('financeiro'):
          financeiro($id);
          break;
  case ('tecnico'):
          if ($_GET['existe'] == '1') {
            print "<script type=\"text/javascript\">alert('Número de contrato inválido.');
                      window.open('listagem.php?modo=tecnico', '_self');
                   </script>";
          }
          if ($_GET['existe'] == '2') {
            print "<script type=\"text/javascript\">retorno = confirm('Já existe um registro com esse número de contrato. Você deseja ver esse contrato?');
                      if (retorno == false) { window.open('listagem.php?modo=tecnico', '_self'); }
                      else { window.open('editar.php?modo=tecnico&id=$id', '_self'); }
                   </script>";
          }
          tecnico($id);
          break;
  default:
          retornar($modo);
          break;
  }

  ############################
  ##### FUNÇÕES GERAIS #######
  ############################

  function carregaArquivo($arquivo) {
    $fp = fopen($arquivo, "r");
    $retorno = fread($fp, filesize($arquivo));
    fclose($fp);
    return $retorno;
  }

  function retornar ($modo) {
    header("Location: listagem.php?modo=".urlencode($modo).($_GET["inicio"] ? "&inicio={$_GET["inicio"]}" : "").($_GET["termino"] ? "&termino={$_GET["termino"]}" : "").($_GET["edicao"] ? "&edicao={$_GET["edicao"]}" : "").($_GET["tipo"] ? "&tipo={$_GET["tipo"]}" : ""));
  }

  function formataDinheiro ($valor,$moeda = 'R$') {
    if ($valor == '0') {
      return '';
    }
    if ($valor != "") {
      $valor = preg_replace("/[^0-9]/","",intval($valor));
      while (strlen($valor) < 2) $valor = "0" . $valor;
      if (strlen($valor) > 2) {
        $valor = substr($valor,0,strlen($valor)-2) . "," . substr($valor,strlen($valor)-2,strlen($valor));
      } else {
        $valor = "0," . $valor;
      }
      if ($moeda) $valor = $moeda . " " . $valor;
    }

    return $valor;
  }

  ############################
  ####### BUSCA DADOS ########
  ############################

  function buscaDadosFinanceiros ($id) {
  	GLOBAL $conexao;
  $query = "
    SELECT
      id,
      codigo,
      DATE_FORMAT(ativacao_data,'%d/%m/%Y') AS ativacao_data,
      vo_instalacao,
      vo_rede,
      vo_hub,
      vo_total,
      ve_cheque,
      ve_dinheiro,
      ve_fatura,
      ve_total,
      vs_notas,
      vs_combustivel,
      vs_total
    FROM
      ativacao_financeiro
    WHERE
      id = '$id'";

  $query = @mysql_query($query, $conexao);
  $dados = @mysql_fetch_array($query);

  return $dados;
  }
/*
  function buscaDadosFinanceiros ($id) {
  	GLOBAL $conexao;
  $query = "
    SELECT
      id, codigo, DATE_FORMAT(ativacao_data,'%d/%m/%Y') AS ativacao_data, ve_cheque, ve_dinheiro, ve_notas, ve_combustivel, ve_total, vs_parcelado, vs_deposito, vs_notas, vs_combustivel, vs_total, DATE_FORMAT(vp_p1_data,'%d/%m/%Y') AS vp_p1_data, vp_p1_valor, DATE_FORMAT(vp_p2_data,'%d/%m/%Y') AS vp_p2_data, vp_p2_valor, DATE_FORMAT(vp_p3_data,'%d/%m/%Y') AS vp_p3_data, vp_p3_valor, vp_total
    FROM
      ativacao_financeiro
    WHERE
      id = '$id'";

  $query = @mysql_query($query, $conexao);
  $dados = @mysql_fetch_array($query);

  return $dados;
  }
*/
  function buscaDadosTecnicos ($id) {
  	GLOBAL $conexao;
  $query = "
    SELECT
      id, codigo, DATE_FORMAT(ativacao_data,'%d/%m/%Y') AS ativacao_data, ativacao_cabo, ativacao_conector, ativacao_rede,	ativacao_hub, vo_instalacao, vo_rede, vo_hub, vo_total
    FROM
      ativacao_tecnico
    WHERE
      id = '$id'";

  $query = @mysql_query($query, $conexao);
  $dados = @mysql_fetch_array($query);

  return $dados;
  }
/*
  function buscaEditadoAdministrativo ($codigo) {
  	GLOBAL $conexao;
  $query = "
    SELECT
      editado
    FROM
      ativacao_administrativo
    WHERE
      codigo = '$codigo'";

  $query = @mysql_query($query, $conexao);
  $editado = @mysql_result($query,0,0);

  return $editado;
  }
*/
  function buscaEditadoFinanceiro ($codigo) {
  	GLOBAL $conexao;
  $query = "
    SELECT
      editado
    FROM
      ativacao_financeiro
    WHERE
      codigo = '$codigo'";

  $query = @mysql_query($query, $conexao);
  $editado = @mysql_result($query,0,0);

  return $editado;
  }

  function buscaEditadoTecnico ($codigo) {
  	GLOBAL $conexao;
  $query = "
    SELECT
      editado
    FROM
      ativacao_tecnico
    WHERE
      codigo = '$codigo'";

  $query = @mysql_query($query, $conexao);
  $editado = @mysql_result($query,0,0);

  return $editado;
  }

  ############################
  ####### EDITA DADOS ########
  ############################

  function financeiro ($id) {
  if (empty($_POST["confirmar"])) {
	  $dados = buscaDadosFinanceiros($id);
	  $conteudo = carregaArquivo("financeiro.php");
	  $conteudo = str_replace("[id]", $dados['id'], $conteudo);
	  $conteudo = str_replace("[inicio]", $_GET["inicio"], $conteudo);
	  $conteudo = str_replace("[termino]", $_GET["termino"], $conteudo);
	  $conteudo = str_replace("[edicao]", $_GET["edicao"], $conteudo);
	  $conteudo = str_replace("[tipo]", $_GET["tipo"], $conteudo);
	  $conteudo = str_replace("[codigo]", $dados['codigo'], $conteudo);
	  $conteudo = str_replace("[ativacao_data]", $dados['ativacao_data'], $conteudo);
	  $conteudo = str_replace("[vo_instalacao]", formataDinheiro($dados['vo_instalacao']), $conteudo);
	  $conteudo = str_replace("[vo_rede]", formataDinheiro($dados['vo_rede']), $conteudo);
	  $conteudo = str_replace("[vo_hub]", formataDinheiro($dados['vo_hub']), $conteudo);
	  $conteudo = str_replace("[vo_total]", formataDinheiro($dados['vo_total']), $conteudo);
	  $conteudo = str_replace("[ve_cheque]", formataDinheiro($dados['ve_cheque']), $conteudo);
	  $conteudo = str_replace("[ve_dinheiro]", formataDinheiro($dados['ve_dinheiro']), $conteudo);
	  $conteudo = str_replace("[ve_fatura]", formataDinheiro($dados['ve_fatura']), $conteudo);
	  $conteudo = str_replace("[ve_total]", formataDinheiro($dados['ve_total']), $conteudo);
	  $conteudo = str_replace("[vs_notas]", formataDinheiro($dados['vs_notas']), $conteudo);
	  $conteudo = str_replace("[vs_combustivel]", formataDinheiro($dados['vs_combustivel']), $conteudo);
	  $conteudo = str_replace("[vs_total]", formataDinheiro($dados['vs_total']), $conteudo);
	  if (buscaEditadoFinanceiro($dados['codigo']) == '0' ||
	         ($_COOKIE["login"] == 'financeiro' && $_COOKIE["senha"] == 'simples')) {
      $conteudo = str_replace("disabled", '', $conteudo);
	  }
	  print "$conteudo";
  } else {
  	GLOBAL $conexao;
    $_POST["ativacao_data"] = substr($_POST["ativacao_data"],6,4).substr($_POST["ativacao_data"],3,2).substr($_POST["ativacao_data"],0,2);

    $_POST["vo_instalacao"] = preg_replace("/[^0-9]/","",$_POST["vo_instalacao"]);
    $_POST["vo_rede"] = preg_replace("/[^0-9]/","",$_POST["vo_rede"]);
    $_POST["vo_hub"] = preg_replace("/[^0-9]/","",$_POST["vo_hub"]);
    $_POST["vo_total"] = $_POST["vo_instalacao"]+$_POST["vo_rede"]+$_POST["vo_hub"];

    $_POST["ve_cheque"] = preg_replace("/[^0-9]/","",$_POST["ve_cheque"]);
    $_POST["ve_dinheiro"] = preg_replace("/[^0-9]/","",$_POST["ve_dinheiro"]);
    $_POST["ve_fatura"] = preg_replace("/[^0-9]/","",$_POST["ve_fatura"]);
    $_POST["ve_total"] = $_POST["ve_cheque"]+$_POST["ve_dinheiro"]+$_POST["ve_fatura"];

    $_POST["vs_notas"] = preg_replace("/[^0-9]/","",$_POST["vs_notas"]);
    $_POST["vs_combustivel"] = preg_replace("/[^0-9]/","",$_POST["vs_combustivel"]);
    $_POST["vs_total"] = $_POST["vs_notas"]+$_POST["vs_combustivel"];

    $query = "UPDATE ativacao_financeiro SET data_hora = NOW(), ativacao_data = '$_POST[ativacao_data]', vo_instalacao = '$_POST[vo_instalacao]', vo_rede = '$_POST[vo_rede]', vo_hub = '$_POST[vo_hub]', vo_total = '$_POST[vo_total]', ve_cheque = '$_POST[ve_cheque]', ve_dinheiro = '$_POST[ve_dinheiro]', ve_fatura = '$_POST[ve_fatura]', ve_total = '$_POST[ve_total]', vs_notas = '$_POST[vs_notas]', vs_combustivel = '$_POST[vs_combustivel]', vs_total = '$_POST[vs_total]', editado = '1' WHERE id = '$_POST[id]'";
	  $query = @mysql_query($query, $conexao);

    //header("Location: editar.php?modo=financeiro&id=$_POST[id]");
    retornar('financeiro');
    }
  }
/*
  function financeiro ($id) {
  if (empty($_POST["confirmar"])) {
	  $dados = buscaDadosFinanceiros($id);
	  $conteudo = carregaArquivo("financeiro.php");
	  $conteudo = str_replace("[id]", $dados['id'], $conteudo);
	  $conteudo = str_replace("[codigo]", $dados['codigo'], $conteudo);
	  $conteudo = str_replace("[ativacao_data]", $dados['ativacao_data'], $conteudo);
	  $conteudo = str_replace("[ve_cheque]", formataDinheiro($dados['ve_cheque']), $conteudo);
	  $conteudo = str_replace("[ve_dinheiro]", formataDinheiro($dados['ve_dinheiro']), $conteudo);
	  $conteudo = str_replace("[ve_notas]", formataDinheiro($dados['ve_notas']), $conteudo);
	  $conteudo = str_replace("[ve_combustivel]", formataDinheiro($dados['ve_combustivel']), $conteudo);
	  $conteudo = str_replace("[ve_total]", formataDinheiro($dados['ve_total']), $conteudo);
	  $conteudo = str_replace("[vs_parcelado]", formataDinheiro($dados['vs_parcelado']), $conteudo);
	  $conteudo = str_replace("[vs_deposito]", formataDinheiro($dados['vs_deposito']), $conteudo);
	  $conteudo = str_replace("[vs_notas]", formataDinheiro($dados['vs_notas']), $conteudo);
	  $conteudo = str_replace("[vs_combustivel]", formataDinheiro($dados['vs_combustivel']), $conteudo);
	  $conteudo = str_replace("[vs_total]", formataDinheiro($dados['vs_total']), $conteudo);
	  $conteudo = str_replace("[vp_p1_data]", $dados['vp_p1_data'], $conteudo);
	  $conteudo = str_replace("[vp_p1_valor]", formataDinheiro($dados['vp_p1_valor']), $conteudo);
	  $conteudo = str_replace("[vp_p2_data]", $dados['vp_p2_data'], $conteudo);
	  $conteudo = str_replace("[vp_p2_valor]", formataDinheiro($dados['vp_p2_valor']), $conteudo);
	  $conteudo = str_replace("[vp_p3_data]", $dados['vp_p3_data'], $conteudo);
	  $conteudo = str_replace("[vp_p3_valor]", formataDinheiro($dados['vp_p3_valor']), $conteudo);
	  $conteudo = str_replace("[vp_total]", formataDinheiro($dados['vp_total']), $conteudo);
	  print "$conteudo";
  } else {
  	GLOBAL $conexao;
    $_POST["ativacao_data"] = substr($_POST["ativacao_data"],6,4).substr($_POST["ativacao_data"],3,2).substr($_POST["ativacao_data"],0,2);

    $_POST["ve_cheque"] = preg_replace("/[^0-9]/","",$_POST["ve_cheque"]);
    $_POST["ve_dinheiro"] = preg_replace("/[^0-9]/","",$_POST["ve_dinheiro"]);
    $_POST["ve_notas"] = preg_replace("/[^0-9]/","",$_POST["ve_notas"]);
    $_POST["ve_combustivel"] = preg_replace("/[^0-9]/","",$_POST["ve_combustivel"]);
    $_POST["ve_total"] = $_POST["ve_cheque"]+$_POST["ve_dinheiro"]+$_POST["ve_notas"]+$_POST["ve_combustivel"];

    $_POST["vs_parcelado"] = preg_replace("/[^0-9]/","",$_POST["vs_parcelado"]);
    $_POST["vs_deposito"] = preg_replace("/[^0-9]/","",$_POST["vs_deposito"]);
    $_POST["vs_notas"] = preg_replace("/[^0-9]/","",$_POST["vs_notas"]);
    $_POST["vs_combustivel"] = preg_replace("/[^0-9]/","",$_POST["vs_combustivel"]);
    $_POST["vs_total"] = $_POST["vs_parcelado"]+$_POST["vs_deposito"]+$_POST["vs_notas"]+$_POST["vs_combustivel"];

    $_POST["vp_p1_valor"] = preg_replace("/[^0-9]/","",$_POST["vp_p1_valor"]);
    $_POST["vp_p2_valor"] = preg_replace("/[^0-9]/","",$_POST["vp_p2_valor"]);
    $_POST["vp_p3_valor"] = preg_replace("/[^0-9]/","",$_POST["vp_p3_valor"]);

    $_POST["vp_p1_data"] = substr($_POST["vp_p1_data"],6,4).substr($_POST["vp_p1_data"],3,2).substr($_POST["vp_p1_data"],0,2);
    $_POST["vp_p2_data"] = substr($_POST["vp_p2_data"],6,4).substr($_POST["vp_p2_data"],3,2).substr($_POST["vp_p2_data"],0,2);
    $_POST["vp_p3_data"] = substr($_POST["vp_p3_data"],6,4).substr($_POST["vp_p3_data"],3,2).substr($_POST["vp_p3_data"],0,2);

    $_POST["vp_total"] = $_POST["vp_p1_valor"]+$_POST["vp_p2_valor"]+$_POST["vp_p3_valor"];

    $query = "update ativacao_financeiro set data_hora = NOW(), ativacao_data = '$_POST[ativacao_data]', ve_cheque = '$_POST[ve_cheque]', ve_dinheiro = '$_POST[ve_dinheiro]', ve_notas = '$_POST[ve_notas]', ve_combustivel = '$_POST[ve_combustivel]', ve_total = '$_POST[ve_total]', vs_parcelado = '$_POST[vs_parcelado]', vs_deposito = '$_POST[vs_deposito]', vs_notas = '$_POST[vs_notas]', vs_combustivel = '$_POST[vs_combustivel]', vs_total = '$_POST[vs_total]', vp_p1_data = '$_POST[vp_p1_data]', vp_p1_valor = '$_POST[vp_p1_valor]', vp_p2_data = '$_POST[vp_p2_data]', vp_p2_valor = '$_POST[vp_p2_valor]', vp_p3_data = '$_POST[vp_p3_data]', vp_p3_valor = '$_POST[vp_p3_valor]', vp_total = '$_POST[vp_total]', editado = '1' WHERE id = '$_POST[id]'";
	  $query = @mysql_query($query, $conexao);

    retornar('financeiro');
    }
  }
*/

  function tecnico ($id) {
  if (empty($_POST["confirmar"])) {
	  $dados = buscaDadosTecnicos($id);
	  $conteudo = carregaArquivo("tecnico.php");
	  $conteudo = str_replace("[id]", $dados['id'], $conteudo);
	  $conteudo = str_replace("[inicio]", $_GET["inicio"], $conteudo);
	  $conteudo = str_replace("[termino]", $_GET["termino"], $conteudo);
	  $conteudo = str_replace("[edicao]", $_GET["edicao"], $conteudo);
	  $conteudo = str_replace("[tipo]", $_GET["tipo"], $conteudo);
	  $conteudo = str_replace("[codigo]", $dados['codigo'], $conteudo);
	  $conteudo = str_replace("[ativacao_data]", $dados['ativacao_data'], $conteudo);
	  $conteudo = str_replace("[ativacao_cabo]", $dados['ativacao_cabo'], $conteudo);
	  $conteudo = str_replace("[ativacao_conector]", $dados['ativacao_conector'], $conteudo);
	  $conteudo = str_replace("[ativacao_rede]", $dados['ativacao_rede'], $conteudo);
	  $conteudo = str_replace("[ativacao_hub]", $dados['ativacao_hub'], $conteudo);
	  $conteudo = str_replace("[vo_instalacao]", formataDinheiro($dados['vo_instalacao']), $conteudo);
	  $conteudo = str_replace("[vo_rede]", formataDinheiro($dados['vo_rede']), $conteudo);
	  $conteudo = str_replace("[vo_hub]", formataDinheiro($dados['vo_hub']), $conteudo);
	  $conteudo = str_replace("[vo_total]", formataDinheiro($dados['vo_total']), $conteudo);
	  if (buscaEditadoTecnico($dados['codigo']) == '0' ||
	         ($_COOKIE["login"] == 'tecnico' && $_COOKIE["senha"] == 'd123')) {
      $conteudo = str_replace("disabled", '', $conteudo);
	  }
	  print "$conteudo";
  } else {
	  
  	GLOBAL $conexao;
	
    $_POST["ativacao_data"] = substr($_POST["ativacao_data"],6,4).substr($_POST["ativacao_data"],3,2).substr($_POST["ativacao_data"],0,2);
    
	$_POST["ativacao_cabo"] = intval(preg_replace("/[^0-9]/", "", $_POST["ativacao_cabo"]), 10);
	$_POST["ativacao_conector"] = intval(preg_replace("/[^0-9]/", "", $_POST["ativacao_conector"]), 10);
	$_POST["ativacao_rede"] = intval(preg_replace("/[^0-9]/", "", $_POST["ativacao_rede"]), 10);
	$_POST["ativacao_hub"] = intval(preg_replace("/[^0-9]/", "", $_POST["ativacao_hub"]), 10);
	
	$_POST["vo_instalacao"] = intval(preg_replace("/[^0-9]/", "", $_POST["vo_instalacao"]), 10);
    $_POST["vo_rede"] = intval(preg_replace("/[^0-9]/", "", $_POST["vo_rede"]), 10);
    $_POST["vo_hub"] = intval(preg_replace("/[^0-9]/", "", $_POST["vo_hub"]), 10);
    $_POST["vo_total"] = $_POST["vo_instalacao"]+$_POST["vo_rede"]+$_POST["vo_hub"];

    $query = "
		UPDATE
			ativacao_tecnico
		SET 
			data_hora = NOW(), 
			ativacao_data = '$_POST[ativacao_data]', 
			ativacao_cabo = '$_POST[ativacao_cabo]', 
			ativacao_conector = '$_POST[ativacao_conector]', 
			ativacao_rede = '$_POST[ativacao_rede]', 
			ativacao_hub = '$_POST[ativacao_hub]', 
			vo_instalacao = '$_POST[vo_instalacao]', 
			vo_rede = '$_POST[vo_rede]', 
			vo_hub = '$_POST[vo_hub]', 
			vo_total = '$_POST[vo_total]', 
			editado = '1' 
		WHERE 
			id = '$_POST[id]'";
		
		$query = mysql_query($query, $conexao);
	  
		retornar('tecnico');
	
    }
	
  }

?>
