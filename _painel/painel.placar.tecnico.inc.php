<?

function buscaMetasTecnico ($dia,$mes,$ano) {
  $dias_uteis_mes = buscaDiasUteisMes(date("t", strtotime("{$ano}-{$mes}-{$dia}")), $mes, $ano);
  $dias_uteis_semana = buscaDiasUteisSemana($dia,$mes,$ano);
  $metas_mensais = buscaMetasMensais ("tecnico",$mes,$ano);
  $metas["os_internas"]["mes"] = $metas_mensais["os_internas"];
  $metas["os_internas"]["dia"] = $metas["os_internas"]["mes"]/$dias_uteis_mes;
  $metas["os_internas"]["semana"] = $metas["os_internas"]["dia"]*$dias_uteis_semana;
  $metas["os_internas"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["os_internas"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["os_internas"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);
  $metas["os_externas"]["mes"] = $metas_mensais["os_externas"];
  $metas["os_externas"]["dia"] = $metas["os_externas"]["mes"]/$dias_uteis_mes;
  $metas["os_externas"]["semana"] = $metas["os_externas"]["dia"]*$dias_uteis_semana;
  $metas["os_externas"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["os_externas"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["os_externas"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);
  $metas["os_sistemas"]["mes"] = $metas_mensais["os_sistemas"];
  $metas["os_sistemas"]["dia"] = $metas["os_sistemas"]["mes"]/$dias_uteis_mes;
  $metas["os_sistemas"]["semana"] = $metas["os_sistemas"]["dia"]*$dias_uteis_semana;
  $metas["os_sistemas"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["os_sistemas"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["os_sistemas"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);
  $metas["os_estrutural"]["mes"] = $metas_mensais["os_estrutural"];
  $metas["os_estrutural"]["dia"] = $metas["os_estrutural"]["mes"]/$dias_uteis_mes;
  $metas["os_estrutural"]["semana"] = $metas["os_estrutural"]["dia"]*$dias_uteis_semana;
  $metas["os_estrutural"]["niveis"][] = array("cor" => PAINEL_COR_BOM, "minimo" => 0, "maximo" => 0.25);
  $metas["os_estrutural"]["niveis"][] = array("cor" => PAINEL_COR_MEDIO, "minimo" => 0.25, "maximo" => 1);
  $metas["os_estrutural"]["niveis"][] = array("cor" => PAINEL_COR_RUIM, "minimo" => 1, "maximo" => 1000);
  return $metas;
}

function geraPainelPlacarConteudoTecnico($dia,$mes,$ano) {
  $metas = buscaMetasTecnico($dia,$mes,$ano);
  // OS Internas Dia -> Geradas naquele dia
  $itens["os_internas"]["dia"] = buscaOS($dia,$mes,$ano,"dia","8"); // Suporte Interno
  $itens["os_internas"]["dia"] = geraLinkPorCondominio($itens["os_internas"]["dia"],$metas["os_internas"],"dia");
  // OS Internas Semana -> Geradas naquele semana
  $itens["os_internas"]["semana"] = buscaOS($dia,$mes,$ano,"semana","8"); // Suporte Interno
  $itens["os_internas"]["semana"] = geraLinkPorCondominio($itens["os_internas"]["semana"],$metas["os_internas"],"semana");
  // OS Internas Mês -> Geradas naquele mês
  $itens["os_internas"]["mes"] = buscaOS($dia,$mes,$ano,"mes","8"); // Suporte Interno
  $itens["os_internas"]["mes"] = geraLinkPorCondominio($itens["os_internas"]["mes"],$metas["os_internas"],"mes");
  // OS Externas Dia -> Geradas naquele dia
  $itens["os_externas"]["dia"] = buscaOS($dia,$mes,$ano,"dia","9"); // Suporte Externo
  $itens["os_externas"]["dia"] = geraLinkPorCondominio($itens["os_externas"]["dia"],$metas["os_externas"],"dia");
  // OS Externas Semana -> Geradas naquele semana
  $itens["os_externas"]["semana"] = buscaOS($dia,$mes,$ano,"semana","9"); // Suporte Externo
  $itens["os_externas"]["semana"] = geraLinkPorCondominio($itens["os_externas"]["semana"],$metas["os_externas"],"semana");
  // OS Externas Mês -> Geradas naquele mês
  $itens["os_externas"]["mes"] = buscaOS($dia,$mes,$ano,"mes","9"); // Suporte Externo
  $itens["os_externas"]["mes"] = geraLinkPorCondominio($itens["os_externas"]["mes"],$metas["os_externas"],"mes");
  // OS Sistemas Dia -> Geradas naquele dia
  $itens["os_sistemas"]["dia"] = buscaOS($dia,$mes,$ano,"dia","11"); // Sistema (ADM)
  $itens["os_sistemas"]["dia"] = geraLinkPorCondominio($itens["os_sistemas"]["dia"],$metas["os_sistemas"],"dia");
  // OS Sistemas Semana -> Geradas naquele semana
  $itens["os_sistemas"]["semana"] = buscaOS($dia,$mes,$ano,"semana","11"); // Sistema (ADM)
  $itens["os_sistemas"]["semana"] = geraLinkPorCondominio($itens["os_sistemas"]["semana"],$metas["os_sistemas"],"semana");
  // OS Sistemas Mês -> Geradas buscaOS mês
  $itens["os_sistemas"]["mes"] = buscaOS($dia,$mes,$ano,"mes","11"); // Sistema (ADM)
  $itens["os_sistemas"]["mes"] = geraLinkPorCondominio($itens["os_sistemas"]["mes"],$metas["os_sistemas"],"mes");
  // OS Estrutural Dia -> Geradas naquele dia
  $itens["os_estrutural"]["dia"] = buscaOS($dia,$mes,$ano,"dia","12"); // Estrutural
  $itens["os_estrutural"]["dia"] = geraLinkPorCondominio($itens["os_estrutural"]["dia"],$metas["os_estrutural"],"dia");
  // OS Estrutural Semana -> Geradas naquele semana
  $itens["os_estrutural"]["semana"] = buscaOS($dia,$mes,$ano,"semana","12"); // Estrutural
  $itens["os_estrutural"]["semana"] = geraLinkPorCondominio($itens["os_estrutural"]["semana"],$metas["os_estrutural"],"semana");
  // OS Estrutural Mês -> Geradas naquele mês
  $itens["os_estrutural"]["mes"] = buscaOS($dia,$mes,$ano,"mes","12"); // Estrutural
  $itens["os_estrutural"]["mes"] = geraLinkPorCondominio($itens["os_estrutural"]["mes"],$metas["os_estrutural"],"mes");
  return geraPainelPlacarConteudoTabelaTecnico($itens);
}

function geraLinkPorCondominio ($registros,$metas,$periodo) {
  if (sizeof($registros) > 0 ) {
  	$condominios = array();
    for ($i=0;$i<sizeof($registros);$i++) {
    	$condominios[$registros[$i]]++;
    }
  	$limite = 0;
    while (sizeof($condominios) > 25) {
      $limite++;
      while (($condominio_atual = current($condominios)) != false) {
        if ($condominio_atual < $limite) {
          $condominios["#Outros"] += $condominio_atual;
          unset($condominios[key($condominios)]);
        }
        next($condominios);
      }
      reset($condominios);
    }
    arsort($condominios);
    while (($condominio_atual = current($condominios)) != false) {
      $detalhes[] = key($condominios) . ": " . formataNumero($condominio_atual);
      next($condominios);
    }
    $detalhes = @implode("<br>",$detalhes);
  }
  $media = sizeof($registros)/$metas[$periodo];
  for ($i=0;$i<sizeof($metas["niveis"]);$i++) {
    if ($media >= $metas["niveis"][$i]["minimo"] && $media < $metas["niveis"][$i]["maximo"]) $cor = $metas["niveis"][$i]["cor"];
  }
  $link = '<a onMouseOver="hintShow(\''.$detalhes.'\');" onMouseOut="hintHide();" href="#" class="txtBranco10" style="color: ' . $cor . '">' . formataNumero(sizeof($registros)) . ' / ' . formataNumero($metas[$periodo]) . '</a>';
  return $link;
}

function buscaOS ($dia,$mes,$ano,$periodo,$status) {
	return array();
}

function buscaCondominios () {
  global $bancodedados;
  $query = "
    SELECT
      con_id AS id,
      CONCAT(tab_condominios.con_nome,' (',tab_cidades.cid_nome,'/',tab_bairros.bai_nome,')') AS nome
    FROM
      tab_condominios
      INNER JOIN tab_ceps ON (tab_condominios.cep_id = tab_ceps.cep_id)
      INNER JOIN tab_logradouros ON (tab_ceps.log_id = tab_logradouros.log_id)
      INNER JOIN tab_bairros ON (tab_logradouros.bai_id = tab_bairros.bai_id)
      INNER JOIN tab_cidades ON (tab_bairros.cid_id = tab_cidades.cid_id)
    WHERE
      cos_id IS NOT NULL
    ORDER BY
      nome";
  $query = $bancodedados->query($query);
  while (($condominio = $bancodedados->resultadoArray($query)) != false) {
    $condominios[$condominio["id"]] = $condominio["nome"];
  }
  $condominios[0] = "#Sem Condomínio";
  return $condominios;
}

function geraPainelPlacarConteudoTabelaTecnico ($itens) {
  $tabela = '
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="3A5977">
  <tr align="center">
    <td bgcolor="#505050" class="txtBranco10" width="30%">&nbsp;</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#505050" class="txtBranco10" width="23%">Dia</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#505050" class="txtBranco10" width="23%">Semana</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#505050" class="txtBranco10" width="23%">Mês</td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
  </tr>
  <tr align="center">
    <td bgcolor="#505050" class="txtBranco10">&nbsp;OS Internas</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_internas"]["dia"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_internas"]["semana"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_internas"]["mes"].'</td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
  </tr>
  <tr align="center">
    <td bgcolor="#505050" class="txtBranco10">&nbsp;OS Externas</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_externas"]["dia"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_externas"]["semana"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_externas"]["mes"].'</td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
  </tr>
  <tr align="center">
    <td bgcolor="#505050" class="txtBranco10">&nbsp;OS Sistemas</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_sistemas"]["dia"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_sistemas"]["semana"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_sistemas"]["mes"].'</td>
  </tr>
  <tr>
    <td bgcolor="#FFFFFF" height="1" colspan="7"></td>
  </tr>
  <tr align="center">
    <td bgcolor="#505050" class="txtBranco10">&nbsp;OS Estrutural</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_estrutural"]["dia"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_estrutural"]["semana"].'</td>
    <td bgcolor="#FFFFFF" width="1"></td>
    <td bgcolor="#828282" class="txtBranco10">'.$itens["os_estrutural"]["mes"].'</td>
  </tr></table>
';
  return $tabela;
}

?>
