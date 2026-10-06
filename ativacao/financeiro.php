<html>
 <head>
  <title>Wave System Internet - Ativação</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
  <meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
	<link href="wavesystem.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="wavesystem.js"></script>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 </head>
<body>

<!-- <center><img src="logo.jpg"></center><br> -->
<form action="editar.php?modo=financeiro&id=[id]&inicio=[inicio]&termino=[termino]&edicao=[edicao]&tipo=[tipo]" name="ativacao" method="POST" onSubmit="return validaFormularioFinanceiro(this)">
<table width="480" border="0" cellspacing="1" cellpadding="3" align="center">
 <tr bgcolor="#777777">
  <td colspan="2"><strong><font class="txtTitulo">CADASTRO :: MÓDULO ADMINISTRATIVO</font></strong></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td width="30%"><font class="txtNormal"><b>Código:</b></font></td>
  <td width="70%"><font class="txtNormal">[codigo]</font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal"><b>Data de Ativação:</b></font></td>
  <td><font class="txtNormal"><input class="formInput" name="ativacao_data" type="text" id="ativacao_data"  style="width:100%" maxlength="10" value="[ativacao_data]" onKeyPress="return formataData(this,event);" onblur="return formataData(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td colspan="2"><font class="txtNormal"><b>Valores Originais:</b></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Instalação:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_instalacao" type="text" id="vo_instalacao"  style="width:100%" maxlength="20" value="[vo_instalacao]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Rede:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_rede" type="text" id="vo_rede"  style="width:100%" maxlength="20" value="[vo_rede]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; HUB:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_hub" type="text" id="vo_hub"  style="width:100%" maxlength="20" value="[vo_hub]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
<!--
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Total:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_total" type="text" id="vo_total"  style="width:100%" maxlength="20" value="[vo_total]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
-->
 <tr bgcolor="#CCCCCC">
  <td colspan="2"><font class="txtNormal"><b>Valores de Entrada:</b></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Cheque:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ve_cheque" type="text" id="ve_cheque"  style="width:100%" maxlength="20" value="[ve_cheque]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Dinheiro:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ve_dinheiro" type="text" id="ve_dinheiro"  style="width:100%" maxlength="20" value="[ve_dinheiro]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Fatura:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ve_fatura" type="text" id="ve_fatura"  style="width:100%" maxlength="20" value="[ve_fatura]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
<!--
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Total:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ve_total" type="text" id="ve_total"  style="width:100%" maxlength="20" value="[ve_total]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td colspan="2"><font class="txtNormal"><b>Valores de Saída:</b></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Notas:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vs_notas" type="text" id="vs_notas"  style="width:100%" maxlength="20" value="[vs_notas]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Combustível:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vs_combustivel" type="text" id="vs_combustivel"  style="width:100%" maxlength="20" value="[vs_combustivel]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Total:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vs_total" type="text" id="vs_total" style="width:100%" maxlength="20" value="[vs_total]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
-->
 <tr bgcolor="#CCCCCC">
  <td colspan="2" align="center">
	<input class="formInput" type="hidden" name="id" value="[id]">
  <input name="confirmar" type="submit" class="formButton" id="confirmar" value="Salvar" disabled>
  <input name="limpar" type="reset" class="formButton" id="limpar" value="Limpar" disabled>
  <input name="voltar" type="button" class="formButton" value="Voltar" onClick="history.back()">
  </td>
</table>
</form>

</body>
</html>
