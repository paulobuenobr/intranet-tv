<form action="listagem.php" name="filtrar" method="GET">
<table width="450" border="0" cellspacing="1" cellpadding="3" align="center">
  <tr bgcolor="#777777">
    <td colspan="2" width="35%"><strong><font class="txtTitulo">Filtro</font></strong></td>
    <td align="right"><strong><font class="txtNormalBranco">(<a href="listagem.php?modo=tecnico&tipo=dia">dia</a>) (<a href="listagem.php?modo=tecnico&tipo=semana">semana</a>) (<a href="listagem.php?modo=tecnico&tipo=mes">mês</a>)</font></strong></td>
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
<input class="formInput" type="hidden" name="modo" value="tecnico">
</form>
<table width="750" border="0" cellspacing="1" cellpadding="3" align="center">
  <tr bgcolor="#777777">
    <td colspan="11"><strong><font class="txtTitulo">LISTAGEM DE CLIENTES :: MÓDULO TÉCNICO</font></strong></td>
  </tr>
  <tr bgcolor="#999999">
    <td width="20%" align="center"><strong><font class="txtNormalBranco">Contrato</font></strong></td>
    <td width="37%" colspan="5" align="center"><strong><font class="txtNormalBranco">Dados de Ativação</font></strong></td>
    <td width="43%" colspan="4" align="center"><strong><font class="txtNormalBranco">Valores (Originais)</font></strong></td>
  </tr>
  <tr bgcolor="#999999">
    <td align="center"><strong><font class="txtNormalBranco">Código</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Data</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Cabo</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Conector</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Rede</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">HUB</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Instalação</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Rede</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">HUB</font></strong></td>
    <td align="center"><strong><font class="txtNormalBranco">Total</font></strong></td>
  </tr>
<?

global $query4;
global $modo;

while (($dados = @mysql_fetch_array($query4)) != false) {
  $dados['ativacao_data'] = substr($dados['ativacao_data'],8,2)."/".substr($dados['ativacao_data'],5,2)."/".substr($dados['ativacao_data'],0,4);

  $ativacao_cabo += $dados['ativacao_cabo'];
  $ativacao_conector += $dados['ativacao_conector'];
  $ativacao_rede += $dados['ativacao_rede'];
  $ativacao_hub += $dados['ativacao_hub'];
  $vo_instalacao += $dados['vo_instalacao'];
  $vo_rede += $dados['vo_rede'];
  $vo_hub += $dados['vo_hub'];
  $vo_total += $dados['vo_total'];

  $dados['vo_instalacao'] = formataDinheiro($dados['vo_instalacao']);
  $dados['vo_rede'] = formataDinheiro($dados['vo_rede']);
  $dados['vo_hub'] = formataDinheiro($dados['vo_hub']);
  $dados['vo_total'] = formataDinheiro($dados['vo_total']);
?>
  <tr bgcolor="#CCCCCC" onMouseOver="this.style.cursor='pointer';this.style.backgroundColor='DDDDDD';" onMouseOut="this.style.cursor='default';this.style.backgroundColor='CCCCCC';" onclick="<? echo "window.location='editar.php?modo=$modo&id=$dados[id]".($_GET["inicio"] ? "&inicio={$_GET["inicio"]}" : "").($_GET["termino"] ? "&termino={$_GET["termino"]}" : "").($_GET["edicao"] ? "&edicao={$_GET["edicao"]}" : "").($_GET["tipo"] ? "&tipo={$_GET["tipo"]}" : "")."';"; ?>">
    <td align="center"><font class="txtNormal10"><?=$dados['codigo']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ativacao_data']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ativacao_cabo']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ativacao_conector']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ativacao_rede']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['ativacao_hub']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_instalacao']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_rede']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_hub']; ?></font></td>
    <td align="center"><font class="txtNormal10"><?=$dados['vo_total']; ?></font></td>
  </tr>
<?
}
?>
  <tr bgcolor="#999999">
    <td align="center" colspan="2"><font class="txtNormalBranco"><b>TOTAL GERAL</b></font></td>
    <td align="center"><font class="txtNormalBranco"><?=$ativacao_cabo; ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=$ativacao_conector; ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=$ativacao_rede; ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=$ativacao_hub; ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_instalacao); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_rede); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_hub); ?></font></td>
    <td align="center"><font class="txtNormalBranco"><?=formataDinheiro($vo_total); ?></font></td>
  </tr>
  <tr bgcolor="#999999">
    <td colspan="2" align="center"><font class="txtNormal"><? echo "<b><a href=\"adicionar.php?modo=$modo\">Adicionar Contrato</a></b>"; ?></font></td>
    <td colspan="8" align="center"></td>
  </tr>
</table>
