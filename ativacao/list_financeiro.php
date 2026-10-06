<form action="listagem.php" name="filtrar" method="GET">
<table width="450" border="0" cellspacing="1" cellpadding="3" align="center">
  <tr bgcolor="#777777">
    <td colspan="2" width="35%"><strong><font class="txtTitulo">Filtro</font></strong></td>
    <td align="right"><strong><font class="txtNormalBranco">(<a href="listagem.php?modo=financeiro&tipo=dia">dia</a>) (<a href="listagem.php?modo=financeiro&tipo=semana">semana</a>) (<a href="listagem.php?modo=financeiro&tipo=mes">mês</a>)</font></strong></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <td align="center" width="10%"><font class="txtNormal">De:</font></td>
    <td align="center" width="25%"><input class="formInput" name="inicio" id="inicio" type="text" style="width:100%" maxlength="10" onKeyPress="return formataData(this,event);" onChange="return formataData(this,event);" value="<?=$_GET['inicio']; ?>"></td>
    <td align="center" width="35%"><input name="edicao" type="radio" value="s" <? if ($_GET['edicao'] == 's') print "checked"; ?>><font class="txtNormal">Editado<input name="edicao" type="radio" value="n" <? if ($_GET['edicao'] == 'n') print "checked"; ?>>Não Editado</font></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <td align="center" width="10%"><font class="txtNormal">Até:</font></td>
    <td align="center" width="25%"><input class="formInput" name="termino" id="termino" type="text" style="width:100%" maxlength="10" onKeyPress="return formataData(this,event);" onChange="return formataData(this,event);" value="<?=$_GET['termino']; ?>"></td>
    <td align="center" width="35%"><input name="filtrar" type="submit" class="formButton" id="filtrar" value="Filtrar" style="width:48%"><input name="limpar" type="button" class="formButton" value="Limpar" style="width:48%" onClick="limparCampos();"></td>
  </tr>
</table>
  <input class="formInput" type="hidden" name="modo" value="financeiro">
</form>
<table width="990" cellspacing="1" cellpadding="3" align="center">
  <tr bgcolor="#777777">
    <td colspan="15"><strong><font class="txtTitulo">LISTAGEM DE CLIENTES :: MÓDULO FINANCEIRO</font></strong></td>
  </tr>
  <tr bgcolor="#999999">
    <td rowspan="2" align="center"></td>
    <td rowspan="2" align="center"><strong><font class="txtNormalBranco">Contrato</font></strong></td>
    <td rowspan="2" align="center"><strong><font class="txtNormalBranco">Cliente</font></strong></td>
    <td rowspan="2" align="center"><strong><font class="txtNormalBranco">Ativação</font></strong></td>
    <td colspan="4" align="center"><strong><font class="txtNormalBranco">Valores (Originais)</font></strong></td>
    <td colspan="4" align="center"><strong><font class="txtNormalBranco">Valores (Entradas)</font></strong></td>
<!--
    <td width="29%" colspan="3" align="center"><strong><font class="txtNormalBranco">Valores (Saídas)</font></strong></td>
-->
  </tr>
  <tr bgcolor="#999999">
    <td align="center"><strong><font class="txtNormalBranco">Instalação</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Rede</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">HUB</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Total</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Cheque</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Dinheiro</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Fatura</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Total</font></strong></td>
<!--
    <td align="center"><strong><font class="txtNormalBranco">Notas</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Combustível</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Total</font></strong></td>
-->
  </tr>

<?

global $query4;
global $modo;

while (($dados = @mysql_fetch_array($query4)) != false) {
  $dados['ativacao_data'] = substr($dados['ativacao_data'],8,2)."/".substr($dados['ativacao_data'],5,2)."/".substr($dados['ativacao_data'],0,4);
  $dados['nome'] = buscaNome($dados['codigo']);
  $dados['origem'] = buscaOrigem($dados['codigo']);

  $vo_instalacao += $dados['vo_instalacao'];
  $vo_rede += $dados['vo_rede'];
  $vo_hub += $dados['vo_hub'];
  $vo_total += $dados['vo_total'];
  $ve_cheque += $dados['ve_cheque'];
  $ve_dinheiro += $dados['ve_dinheiro'];
  $ve_fatura += $dados['ve_fatura'];
  $ve_total += $dados['ve_total'];
  $vs_notas += $dados['vs_notas'];
  $vs_combustivel += $dados['vs_combustivel'];
  $vs_total += $dados['vs_total'];

  $dados['vo_instalacao'] = formataDinheiro($dados['vo_instalacao']);
  $dados['vo_rede'] = formataDinheiro($dados['vo_rede']);
  $dados['vo_hub'] = formataDinheiro($dados['vo_hub']);
  $dados['vo_total'] = formataDinheiro($dados['vo_total']);

  $dados['ve_cheque'] = formataDinheiro($dados['ve_cheque']);
  $dados['ve_dinheiro'] = formataDinheiro($dados['ve_dinheiro']);
  $dados['ve_fatura'] = formataDinheiro($dados['ve_fatura']);
  $dados['ve_total'] = formataDinheiro($dados['ve_total']);

  $dados['vs_notas'] = formataDinheiro($dados['vs_notas']);
  $dados['vs_combustivel'] = formataDinheiro($dados['vs_combustivel']);
  $dados['vs_total'] = formataDinheiro($dados['vs_total']);
?>

  <tr bgcolor="#CCCCCC" onMouseOver="this.style.cursor='pointer';this.style.backgroundColor='DDDDDD';" onMouseOut="this.style.cursor='default';this.style.backgroundColor='CCCCCC';" onclick="<? echo "window.location='editar.php?modo=$modo&id=$dados[id]".($_GET["inicio"] ? "&inicio={$_GET["inicio"]}" : "").($_GET["termino"] ? "&termino={$_GET["termino"]}" : "").($_GET["edicao"] ? "&edicao={$_GET["edicao"]}" : "").($_GET["tipo"] ? "&tipo={$_GET["tipo"]}" : "")."';"; ?>">
    <td align="center"><font class="txtNormal10"><?=$dados['origem']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['codigo']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['nome']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ativacao_data']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_instalacao']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_rede']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_hub']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_total']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ve_cheque']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ve_dinheiro']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ve_fatura']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ve_total']; ?></font></td>
<!--
    <td align="center"><font class="txtNormal10"><?=$dados['vs_notas']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vs_combustivel']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vs_total']; ?></font></td>
-->
  </tr>

<?
}
?>
  <tr bgcolor="#999999">
    <td align="center" colspan="4"><font class="txtNormalBranco" style="font-size:10px"><b>TOTAL GERAL</b></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_instalacao); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_rede); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_hub); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_total); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($ve_cheque); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($ve_dinheiro); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($ve_fatura); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($ve_total); ?></font></td>
<!--
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vs_notas); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vs_combustivel); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vs_total); ?></font></td>
-->
  </tr>
</table>
