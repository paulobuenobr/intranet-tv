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

<center><img src="logo_ole.jpg"></center><br>
<form action="editar.php?modo=tecnico&id=[id]&inicio=[inicio]&termino=[termino]&edicao=[edicao]&tipo=[tipo]" name="ativacao" method="POST" onSubmit="return validaFormularioTecnico(this)">
<table width="480" border="0" cellspacing="1" cellpadding="3" align="center">
 <tr bgcolor="#777777">
  <td colspan="2"><strong><font class="txtTitulo">CADASTRO :: MÓDULO TÉCNICO</font></strong></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td width="30%"><font class="txtNormal"><b>Código:</b></font></td>
  <td width="70%"><font class="txtNormal">[codigo]</font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td colspan="2"><font class="txtNormal"><b>Dados de Ativação:</b></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Data:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ativacao_data" type="text" id="ativacao_data"  style="width:100%" maxlength="10" value="[ativacao_data]" onKeyPress="return formataData(this,event);" onblur="return formataData(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Cabo:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ativacao_cabo" type="text" id="ativacao_cabo"  style="width:100%" maxlength="20" value="[ativacao_cabo]" onKeyPress="return formataNumero(this,event);" onblur="return formataNumero(this,event);" onBlur="return formataNumero(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Conector:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ativacao_conector" type="text" id="ativacao_conector"  style="width:100%" maxlength="20" value="[ativacao_conector]" onKeyPress="return formataNumero(this,event);" onblur="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"  disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Rede:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ativacao_rede" type="text" id="ativacao_rede"  style="width:100%" maxlength="20" value="[ativacao_rede]" onKeyPress="return formataNumero(this,event);" onblur="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"  disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; HUB:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="ativacao_hub" type="text" id="ativacao_hub"  style="width:100%" maxlength="20" value="[ativacao_hub]" onKeyPress="return formataNumero(this,event);" onblur="return formataNumero(this,event);" onBlur="return formataNumero(this,event);"  disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td colspan="2"><font class="txtNormal"><b>Valores Originais:</b></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Instalação:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_instalacao" type="text" id="vo_instalacao" style="width:100%" maxlength="20" value="[vo_instalacao]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Rede:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_rede" type="text" id="vo_rede" style="width:100%" maxlength="20" value="[vo_rede]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; HUB:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_hub" type="text" id="vo_hub" style="width:100%" maxlength="20" value="[vo_hub]" onKeyPress="return formataDinheiro(this,event);" onblur="return formataDinheiro(this,event);" disabled></font></td>
 </tr>
 <!--
 <tr bgcolor="#CCCCCC">
  <td><font class="txtNormal">&raquo; Total:</font></td>
  <td><font class="txtNormal"><input class="formInput" name="vo_total" type="text" id="vo_total" style="width:100%" maxlength="20" value="[vo_total]" readonly disabled></font></td>
 </tr>
 -->
 <tr bgcolor="#CCCCCC">
  <td colspan="2" align="center">
  <input class="formInput" type="hidden" name="id" value="[id]">
  <input name="confirmar" type="submit" class="formButton" id="confirmar" value="Salvar" disabled>
  <input name="limpar" type="reset" class="formButton" id="limpar" value="Limpar" disabled>
  <input name="voltar" type="button" class="formButton" value="Voltar" onClick="history.back()">
  </td>
 </tr>
</table>
</form>

</body>
</html>
